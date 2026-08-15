<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\ProductStoreRequest;
use App\Http\Requests\ProductUpdateRequest;
use App\Models\Category;
use App\Models\Product;
use App\Models\ProductImage;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\View\View;

class ProductController extends Controller
{
    public function index(): View
    {
        $filters = request()->only(['category', 'status', 'search']);
        $products = Product::query()
            ->with(['category', 'images', 'variants'])
            ->withCount('variants')
            ->when($filters['category'] ?? null, fn ($query, $category) => $query->where('category_id', $category))
            ->when($filters['status'] ?? null, fn ($query, $status) => $query->where('is_active', $status === 'active'))
            ->when($filters['search'] ?? null, fn ($query, $search) => $query->where('name', 'like', "%{$search}%"))
            ->latest()
            ->paginate(15)
            ->withQueryString();

        return view('admin.products.index', ['categories' => Category::query()->orderBy('name')->get(), 'filters' => $filters, 'products' => $products]);
    }

    public function create(): View
    {
        return view('admin.products.create', ['categories' => $this->categories()]);
    }

    public function store(ProductStoreRequest $request): RedirectResponse
    {
        DB::transaction(function () use ($request) {
            $data = $request->validated();
            $product = Product::create($this->productData($data));

            $this->syncVariants($product, $data['variants'] ?? []);
            $this->storeImages($product, $request->file('images', []));
        });

        return redirect()->route('admin.products.index')->with('success', 'Produk buket berhasil ditambahkan.');
    }

    public function edit(Product $product): View
    {
        $product->load(['images', 'variants']);

        return view('admin.products.edit', ['categories' => $this->categories($product), 'product' => $product]);
    }

    public function update(ProductUpdateRequest $request, Product $product): RedirectResponse
    {
        $deletedImagePaths = DB::transaction(function () use ($product, $request) {
            $data = $request->validated();
            $product->update($this->productData($data, $product));

            $this->syncVariants($product, $data['variants'] ?? []);

            if ($images = $request->file('images', [])) {
                $deletedImagePaths = $this->replaceImages($product, $images);

                return $deletedImagePaths;
            }

            $this->syncImageOrder($product, $data['image_order'] ?? []);
            $this->syncPrimaryImage($product, $data['primary_image_id'] ?? null);

            return [];
        });
        Storage::disk('public')->delete($deletedImagePaths);

        return redirect()->route('admin.products.edit', $product)->with('success', 'Produk buket berhasil diperbarui.');
    }

    public function toggle(Product $product): RedirectResponse
    {
        $product->update(['is_active' => ! $product->is_active]);

        return back()->with('success', 'Status produk berhasil diperbarui.');
    }

    public function destroyImage(Product $product, ProductImage $image): RedirectResponse
    {
        abort_unless($image->product_id === $product->id, 404);

        if ($product->images()->count() <= 1) {
            return back()->with('error', 'Produk harus memiliki minimal satu foto.');
        }

        $imagePath = $image->path;
        DB::transaction(function () use ($product, $image) {
            $wasPrimary = $image->is_primary;
            $image->delete();

            if ($wasPrimary) {
                $this->syncPrimaryImage($product, $product->images()->value('id'));
            }
        });
        Storage::disk('public')->delete($imagePath);

        return back()->with('success', 'Foto produk berhasil dihapus.');
    }

    private function categories(?Product $product = null)
    {
        return Category::query()->where('is_active', true)->when($product, fn ($query) => $query->orWhere('id', $product->category_id))->orderBy('name')->get();
    }

    private function productData(array $data, ?Product $product = null): array
    {
        $productData = Arr::only($data, ['base_price', 'category_id', 'description', 'is_active', 'is_featured', 'name']);
        $productData['price'] = $data['base_price'];

        if (! $product) {
            $productData['cost_price'] = 0;
            $productData['stock'] = 0;
        }
        $productData['slug'] = $this->uniqueSlug($data['slug'] ?: $data['name'], $product);

        return $productData;
    }

    private function storeImages(Product $product, array $images): void
    {
        $startOrder = (int) $product->images()->max('sort_order') + 1;

        foreach ($images as $index => $image) {
            $product->images()->create([
                'is_primary' => ! $product->images()->exists() && $index === 0,
                'path' => $image->store('products', 'public'),
                'sort_order' => $startOrder + $index,
            ]);
        }
    }

    private function replaceImages(Product $product, array $images): array
    {
        $paths = $product->images()->pluck('path')->all();
        $product->images()->delete();
        $this->storeImages($product, $images);

        return $paths;
    }

    private function syncImageOrder(Product $product, array $imageIds): void
    {
        foreach ($imageIds as $position => $imageId) {
            $product->images()->whereKey($imageId)->update(['sort_order' => $position]);
        }
    }

    private function syncPrimaryImage(Product $product, ?int $imageId): void
    {
        $imageId ??= $product->images()->where('is_primary', true)->value('id') ?? $product->images()->value('id');

        $product->images()->update(['is_primary' => false]);
        $product->images()->whereKey($imageId)->update(['is_primary' => true]);
    }

    private function syncVariants(Product $product, array $variants): void
    {
        $variantIds = [];

        foreach ($variants as $variant) {
            $data = Arr::only($variant, ['is_active', 'label', 'price_adjustment', 'sku']);
            $record = filled($variant['id'] ?? null) ? $product->variants()->find($variant['id']) : null;
            $record ? $record->update($data) : $record = $product->variants()->create($data);
            $variantIds[] = $record->id;
        }

        $product->variants()->when($variantIds, fn ($query) => $query->whereNotIn('id', $variantIds), fn ($query) => $query)->delete();
    }

    private function uniqueSlug(string $value, ?Product $product = null): string
    {
        $baseSlug = Str::slug($value) ?: 'produk';
        $slug = $baseSlug;
        $number = 2;

        while (Product::query()->where('slug', $slug)->when($product, fn ($query) => $query->whereKeyNot($product))->exists()) {
            $slug = "{$baseSlug}-{$number}";
            $number++;
        }

        return $slug;
    }
}
