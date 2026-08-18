<?php

use App\Enums\AdminRole;
use App\Models\Admin;
use App\Models\Category;
use App\Models\Ingredient;
use App\Models\Product;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

test('an administrator can create a product', function () {
    fakeStorageDisk('public');
    $admin = Admin::create([
        'email' => 'admin@example.com',
        'is_active' => true,
        'name' => 'Admin Produk',
        'password' => 'password',
        'role' => AdminRole::ADMIN,
    ]);

    $category = Category::create([
        'name' => 'Buket Bunga',
        'slug' => 'buket-bunga',
        'is_active' => true,
    ]);
    $ingredient = Ingredient::create([
        'current_stock' => 30,
        'is_active' => true,
        'minimum_stock' => 5,
        'name' => 'Mawar putih',
        'unit' => 'tangkai',
    ]);

    $this->actingAs($admin, 'admin')
        ->get(route('admin.products.create'))
        ->assertOk()
        ->assertSee('name="base_price"', false)
        ->assertDontSee('name="slug"', false)
        ->assertDontSee('[sku]', false)
        ->assertSee('Slug dibuat otomatis dari nama produk.')
        ->assertSee('SKU dibuat otomatis dari nama produk dan label varian.')
        ->assertSee('Resep / Bahan');

    $response = $this->actingAs($admin, 'admin')->post(route('admin.products.store'), [
        'category_id' => $category->id,
        'name' => 'Buket Mawar',
        'base_price' => 150000,
        'allow_multiple_variants' => true,
        'images' => [UploadedFile::fake()->image('buket.jpg')],
        'is_active' => true,
        'is_featured' => false,
        'slug' => 'slug-manual-yang-diabaikan',
        'variants' => [[
            'is_active' => true,
            'is_quantity_based' => true,
            'label' => 'Pecahan Rp50.000',
            'price_adjustment' => 50000,
            'sku' => 'SKU-MANUAL-YANG-DIABAIKAN',
        ]],
    ]);

    $response->assertRedirect(route('admin.products.index'));
    $this->assertDatabaseHas('products', [
        'category_id' => $category->id,
        'name' => 'Buket Mawar',
        'slug' => 'buket-mawar',
        'base_price' => 150000,
        'allow_multiple_variants' => true,
        'is_active' => true,
    ]);
    $this->assertDatabaseCount('product_images', 1);
    $this->assertDatabaseHas('product_variants', ['is_quantity_based' => true, 'price_adjustment' => 50000, 'sku' => 'BUKET-MAWAR-PECAHAN-RP50000']);

    $product = Product::query()->firstOrFail();
    $variant = $product->variants()->firstOrFail();
    $oldImagePath = $product->images()->value('path');

    Storage::disk('public')->assertExists($oldImagePath);

    $this->actingAs($admin, 'admin')
        ->put(route('admin.products.update', $product), [
            'base_price' => 175000,
            'allow_multiple_variants' => true,
            'category_id' => $category->id,
            'images' => [UploadedFile::fake()->image('buket-baru-1.jpg'), UploadedFile::fake()->image('buket-baru-2.jpg')],
            'is_active' => true,
            'is_featured' => false,
            'ingredients' => [['ingredient_id' => $ingredient->id, 'quantity_needed' => 6]],
            'name' => 'Buket Mawar Premium',
            'slug' => 'slug-manual-update-yang-diabaikan',
            'variants' => [[
                'id' => $variant->id,
                'is_active' => true,
                'is_quantity_based' => true,
                'label' => 'Pecahan Rp100.000',
                'price_adjustment' => 100000,
                'sku' => 'SKU-MANUAL-UPDATE-YANG-DIABAIKAN',
            ]],
        ])
        ->assertRedirect(route('admin.products.edit', $product));

    $newImagePaths = $product->fresh()->images()->pluck('path')->all();

    expect($product->fresh()->slug)->toBe('buket-mawar-premium')
        ->and($variant->refresh()->sku)->toBe('BUKET-MAWAR-PREMIUM-PECAHAN-RP100000');

    Storage::disk('public')->assertMissing($oldImagePath);
    Storage::disk('public')->assertExists($newImagePaths);
    $this->assertDatabaseCount('product_images', 2);

    $image = $product->fresh()->images()->firstOrFail();

    $this->actingAs($admin, 'admin')
        ->delete(route('admin.products.images.destroy', [$product, $image]))
        ->assertRedirect();

    Storage::disk('public')->assertMissing($image->path);
    $this->assertDatabaseCount('product_images', 1);
    $this->assertDatabaseHas('product_ingredients', ['ingredient_id' => $ingredient->id, 'quantity_needed' => 6]);

    $category->update(['is_active' => false]);

    $this->actingAs($admin, 'admin')
        ->get(route('admin.products.edit', $product))
        ->assertOk()
        ->assertSee('Buket Mawar Premium');
});

test('an administrator can save fixed and ratio based product recipes', function () {
    $admin = Admin::create(['email' => 'recipe@example.com', 'is_active' => true, 'name' => 'Admin Resep', 'password' => 'password', 'role' => AdminRole::ADMIN]);
    $product = Product::factory()->create();
    $product->images()->create(['is_primary' => true, 'path' => 'products/resep.jpg', 'sort_order' => 1]);
    $defaultIngredient = Ingredient::create(['current_stock' => 100, 'is_active' => true, 'minimum_stock' => 5, 'name' => 'Kertas pembungkus', 'unit' => 'lembar']);
    $fixedVariantIngredient = Ingredient::create(['current_stock' => 100, 'is_active' => true, 'minimum_stock' => 5, 'name' => 'Pita besar', 'unit' => 'meter']);
    $ratioIngredient = Ingredient::create(['current_stock' => 100, 'is_active' => true, 'minimum_stock' => 5, 'name' => 'Lembar uang', 'unit' => 'lembar']);
    $fixedVariant = $product->variants()->create(['is_active' => true, 'is_quantity_based' => false, 'label' => 'Ukuran L', 'price_adjustment' => 50000]);
    $quantityBasedVariant = $product->variants()->create(['is_active' => true, 'is_quantity_based' => true, 'label' => 'Pecahan Rp1.000', 'price_adjustment' => 1000]);

    $this->actingAs($admin, 'admin')
        ->put(route('admin.products.update', $product), [
            'allow_multiple_variants' => true,
            'base_price' => $product->base_price,
            'category_id' => $product->category_id,
            'ingredients' => [
                ['ingredient_id' => $defaultIngredient->id, 'quantity_needed' => 2],
                ['ingredient_id' => $fixedVariantIngredient->id, 'product_variant_id' => $fixedVariant->id, 'quantity_needed' => 3],
                ['ingredient_id' => $ratioIngredient->id, 'product_variant_id' => $quantityBasedVariant->id, 'ratio_per_unit' => 1.5],
            ],
            'is_active' => true,
            'is_featured' => false,
            'name' => $product->name,
            'slug' => $product->slug,
            'variants' => [
                ['id' => $fixedVariant->id, 'is_active' => true, 'is_quantity_based' => false, 'label' => $fixedVariant->label, 'price_adjustment' => $fixedVariant->price_adjustment, 'sku' => null],
                ['id' => $quantityBasedVariant->id, 'is_active' => true, 'is_quantity_based' => true, 'label' => $quantityBasedVariant->label, 'price_adjustment' => $quantityBasedVariant->price_adjustment, 'sku' => null],
            ],
        ])
        ->assertRedirect(route('admin.products.edit', $product));

    $recipes = $product->ingredients()->get()->keyBy('ingredient_id');

    expect($recipes->get($defaultIngredient->id)->quantity_needed)->toBe('2.000')
        ->and($recipes->get($defaultIngredient->id)->ratio_per_unit)->toBeNull()
        ->and($recipes->get($fixedVariantIngredient->id)->quantity_needed)->toBe('3.000')
        ->and($recipes->get($fixedVariantIngredient->id)->ratio_per_unit)->toBeNull()
        ->and($recipes->get($ratioIngredient->id)->quantity_needed)->toBeNull()
        ->and($recipes->get($ratioIngredient->id)->ratio_per_unit)->toBe('1.500');

    $this->actingAs($admin, 'admin')
        ->get(route('admin.products.edit', $product))
        ->assertOk()
        ->assertSee('Qty per buket')
        ->assertSee('Rasio')
        ->assertSee('Jumlah aktual dihitung dari qty yang dipilih customer saat checkout × rasio ini');
});

test('product recipe validation requires the value matching the selected variant mode', function () {
    $admin = Admin::create(['email' => 'recipe-validation@example.com', 'is_active' => true, 'name' => 'Admin Validasi Resep', 'password' => 'password', 'role' => AdminRole::ADMIN]);
    $product = Product::factory()->create();
    $product->images()->create(['is_primary' => true, 'path' => 'products/validation.jpg', 'sort_order' => 1]);
    $ingredient = Ingredient::create(['current_stock' => 100, 'is_active' => true, 'minimum_stock' => 5, 'name' => 'Lembar uang validasi', 'unit' => 'lembar']);
    $variant = $product->variants()->create(['is_active' => true, 'is_quantity_based' => true, 'label' => 'Pecahan Rp5.000', 'price_adjustment' => 5000]);
    $basePayload = [
        'allow_multiple_variants' => true,
        'base_price' => $product->base_price,
        'category_id' => $product->category_id,
        'is_active' => true,
        'is_featured' => false,
        'name' => $product->name,
        'slug' => $product->slug,
    ];

    $this->actingAs($admin, 'admin')
        ->put(route('admin.products.update', $product), [
            ...$basePayload,
            'ingredients' => [['ingredient_id' => $ingredient->id, 'product_variant_id' => $variant->id, 'quantity_needed' => 5]],
            'variants' => [['id' => $variant->id, 'is_active' => true, 'is_quantity_based' => true, 'label' => $variant->label, 'price_adjustment' => $variant->price_adjustment, 'sku' => null]],
        ])
        ->assertSessionHasErrors('ingredients.0.ratio_per_unit');

    $this->actingAs($admin, 'admin')
        ->put(route('admin.products.update', $product), [
            ...$basePayload,
            'ingredients' => [['ingredient_id' => $ingredient->id, 'product_variant_id' => $variant->id, 'ratio_per_unit' => 1]],
            'variants' => [['id' => $variant->id, 'is_active' => true, 'is_quantity_based' => false, 'label' => $variant->label, 'price_adjustment' => $variant->price_adjustment, 'sku' => null]],
        ])
        ->assertSessionHasErrors('ingredients.0.quantity_needed');
});
