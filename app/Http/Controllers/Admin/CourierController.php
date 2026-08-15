<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\CourierRequest;
use App\Models\Courier;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class CourierController extends Controller
{
    public function index(): View
    {
        return view('admin.couriers.index', ['couriers' => Courier::query()->latest()->paginate(15)]);
    }

    public function store(CourierRequest $request): RedirectResponse
    {
        Courier::create($request->validated());

        return back()->with('success', 'Kurir berhasil ditambahkan.');
    }

    public function update(CourierRequest $request, Courier $courier): RedirectResponse
    {
        $courier->update($request->validated());

        return back()->with('success', 'Data kurir berhasil diperbarui.');
    }

    public function toggle(Courier $courier): RedirectResponse
    {
        $courier->update(['is_active' => ! $courier->is_active]);

        return back()->with('success', 'Status kurir berhasil diperbarui.');
    }
}
