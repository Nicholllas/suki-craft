<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\PromotionRequest;
use App\Models\Promotion;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class PromotionController extends Controller
{
    public function index(): View
    {
        return view('admin.promotions.index', ['promotions' => Promotion::query()->withCount('usages')->latest()->paginate(15)]);
    }

    public function create(): View
    {
        return view('admin.promotions.create');
    }

    public function store(PromotionRequest $request): RedirectResponse
    {
        Promotion::create($request->validated());

        return redirect()->route('admin.promotions.index')->with('success', 'Kode promo berhasil ditambahkan.');
    }

    public function show(Promotion $promotion): View
    {
        $usages = $promotion->usages()->with('order:id,order_number,total,discount_amount')->latest()->paginate(15);

        return view('admin.promotions.show', ['promotion' => $promotion, 'usages' => $usages, 'totalDiscount' => $promotion->usages()->join('orders', 'promotion_usages.order_id', '=', 'orders.id')->sum('orders.discount_amount')]);
    }

    public function edit(Promotion $promotion): View
    {
        return view('admin.promotions.edit', ['promotion' => $promotion]);
    }

    public function update(PromotionRequest $request, Promotion $promotion): RedirectResponse
    {
        $promotion->update($request->validated());

        return redirect()->route('admin.promotions.edit', $promotion)->with('success', 'Kode promo berhasil diperbarui.');
    }

    public function toggle(Promotion $promotion): RedirectResponse
    {
        $promotion->update(['is_active' => ! $promotion->is_active]);

        return back()->with('success', 'Status kode promo berhasil diperbarui.');
    }
}
