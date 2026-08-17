<?php

namespace App\Http\Controllers;

use App\Models\PromoBanner;
use Illuminate\View\View;

class StorefrontController extends Controller
{
    public function index(): View
    {
        return view('store.home', ['promoBanners' => PromoBanner::query()->active()->ordered()->get()]);
    }
}
