<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Product;
use App\Models\PromoBanner;
use Illuminate\View\View;

class StorefrontController extends Controller
{
    public function index(): View
    {
        return view('store.home', [
            'categories' => Category::query()->where('is_active', true)->orderBy('name')->get(),
            'featuredProducts' => Product::query()
                ->select(['id', 'category_id', 'name', 'slug', 'description', 'base_price', 'is_featured', 'created_at'])
                ->with([
                    'category:id,name,slug',
                    'images:id,product_id,path,is_primary,sort_order',
                    'variants:id,product_id,price_adjustment,is_active',
                ])
                ->where('is_active', true)
                ->whereHas('category', fn ($query) => $query->where('is_active', true))
                ->orderByDesc('is_featured')
                ->latest()
                ->limit(8)
                ->get(),
            'promoBanners' => PromoBanner::query()->active()->ordered()->get(),
        ]);
    }
}
