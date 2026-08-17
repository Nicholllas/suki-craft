<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\PromoBannerRequest;
use App\Models\PromoBanner;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;

class PromoBannerController extends Controller
{
    public function index(): View
    {
        return view('admin.promo-banners.index', ['promoBanners' => PromoBanner::query()->ordered()->paginate(15)]);
    }

    public function create(): View
    {
        return view('admin.promo-banners.create');
    }

    public function store(PromoBannerRequest $request): RedirectResponse
    {
        $data = $request->validated();
        $data['image_path'] = $request->file('image')->store('promo-banners', 'public');
        unset($data['image']);
        PromoBanner::create($data);

        return redirect()->route('admin.promo-banners.index')->with('success', 'Banner promo berhasil ditambahkan.');
    }

    public function edit(PromoBanner $promoBanner): View
    {
        return view('admin.promo-banners.edit', ['promoBanner' => $promoBanner]);
    }

    public function update(PromoBannerRequest $request, PromoBanner $promoBanner): RedirectResponse
    {
        $data = $request->validated();
        if ($request->hasFile('image')) {
            Storage::disk('public')->delete($promoBanner->image_path);
            $data['image_path'] = $request->file('image')->store('promo-banners', 'public');
        }
        unset($data['image']);
        $promoBanner->update($data);

        return redirect()->route('admin.promo-banners.edit', $promoBanner)->with('success', 'Banner promo berhasil diperbarui.');
    }

    public function toggle(PromoBanner $promoBanner): RedirectResponse
    {
        $promoBanner->update(['is_active' => ! $promoBanner->is_active]);

        return back()->with('success', 'Status banner promo berhasil diperbarui.');
    }
}
