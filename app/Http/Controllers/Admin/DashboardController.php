<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Product;

class DashboardController extends Controller
{
    public function __invoke()
    {
        $activeProducts = Product::query()->where('is_active', true)->count();
        $lowStockProducts = Product::query()->with('category')->where('stock', '<=', 5)->orderBy('stock')->limit(5)->get();

        return view('admin.dashboard', [
            'activeProducts' => $activeProducts,
            'lowStockProducts' => $lowStockProducts,
            'productCount' => Product::query()->count(),
        ]);
    }
}
