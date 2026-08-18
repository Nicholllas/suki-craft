<?php

use App\Enums\AdminRole;
use App\Models\Admin;
use App\Models\Category;
use App\Models\Ingredient;
use App\Models\Product;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

test('an administrator can create a product', function () {
    Storage::fake('public');
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
        ->assertSee('name="weight_grams"', false)
        ->assertSee('Resep / Bahan');

    $response = $this->actingAs($admin, 'admin')->post(route('admin.products.store'), [
        'category_id' => $category->id,
        'name' => 'Buket Mawar',
        'base_price' => 150000,
        'images' => [UploadedFile::fake()->image('buket.jpg')],
        'is_active' => true,
        'is_featured' => false,
        'weight_grams' => 750,
    ]);

    $response->assertRedirect(route('admin.products.index'));
    $this->assertDatabaseHas('products', [
        'category_id' => $category->id,
        'name' => 'Buket Mawar',
        'slug' => 'buket-mawar',
        'base_price' => 150000,
        'is_active' => true,
        'weight_grams' => 750,
    ]);
    $this->assertDatabaseCount('product_images', 1);

    $product = Product::query()->firstOrFail();
    $oldImagePath = $product->images()->value('path');

    Storage::disk('public')->assertExists($oldImagePath);

    $this->actingAs($admin, 'admin')
        ->put(route('admin.products.update', $product), [
            'base_price' => 175000,
            'category_id' => $category->id,
            'images' => [UploadedFile::fake()->image('buket-baru-1.jpg'), UploadedFile::fake()->image('buket-baru-2.jpg')],
            'is_active' => true,
            'is_featured' => false,
            'ingredients' => [['ingredient_id' => $ingredient->id, 'quantity_needed' => 6]],
            'name' => 'Buket Mawar',
            'slug' => 'buket-mawar',
            'weight_grams' => 900,
        ])
        ->assertRedirect(route('admin.products.edit', $product));

    $newImagePaths = $product->fresh()->images()->pluck('path')->all();

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
        ->assertSee('Buket Mawar');
});
