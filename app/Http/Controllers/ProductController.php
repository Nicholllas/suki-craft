<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Product;
use Illuminate\Http\Request;

class ProductController extends Controller
{
    public function index(Request $request)
    {
        $categories = Category::query()
            ->where('is_active', true)
            ->orderBy('name')
            ->get();

        $products = Product::query()
            ->with('category')
            ->where('is_active', true)
            ->whereHas('category', function ($query) {
                $query->where('is_active', true);
            })
            ->when(
                $request->filled('search'),
                function ($query) use ($request) {
                    $query->where(
                        'name',
                        'like',
                        '%' . $request->search . '%'
                    );
                }
            )
            ->when(
                $request->filled('category'),
                function ($query) use ($request) {
                    $query->whereHas('category', function ($query) use ($request) {
                        $query->where('slug', $request->category);
                    });
                }
            )
            ->latest()
            ->paginate(12)
            ->withQueryString();

        return view(
            'products.index',
            compact('products', 'categories')
        );
    }

    public function show(Product $product)
    {
        abort_unless(
            $product->is_active &&
            $product->category?->is_active,
            404
        );

        return view(
            'products.show',
            compact('product')
        );
    }
}