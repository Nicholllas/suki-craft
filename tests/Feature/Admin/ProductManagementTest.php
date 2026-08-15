<?php

use App\Enums\AdminRole;
use App\Models\Admin;
use App\Models\Category;
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

    $this->actingAs($admin, 'admin')
        ->get(route('admin.products.create'))
        ->assertOk()
        ->assertSee('name="base_price"', false);

    $response = $this->actingAs($admin, 'admin')->post(route('admin.products.store'), [
        'category_id' => $category->id,
        'name' => 'Buket Mawar',
        'base_price' => 150000,
        'images' => [UploadedFile::fake()->image('buket.jpg')],
        'is_active' => true,
        'is_featured' => false,
    ]);

    $response->assertRedirect(route('admin.products.index'));
    $this->assertDatabaseHas('products', [
        'category_id' => $category->id,
        'name' => 'Buket Mawar',
        'slug' => 'buket-mawar',
        'base_price' => 150000,
        'is_active' => true,
    ]);
    $this->assertDatabaseCount('product_images', 1);
});
