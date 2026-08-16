<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\IngredientRequest;
use App\Http\Requests\Admin\StockAdjustmentRequest;
use App\Http\Requests\Admin\StockInRequest;
use App\Models\Admin;
use App\Models\Ingredient;
use App\Services\InventoryService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class IngredientController extends Controller
{
    public function __construct(private InventoryService $inventoryService) {}

    public function index(Request $request): View
    {
        $search = $request->string('search')->trim()->toString();
        $ingredients = Ingredient::query()->when($search, fn ($query) => $query->where('name', 'like', "%{$search}%"))->latest()->paginate(20)->withQueryString();

        return view('admin.ingredients.index', ['ingredients' => $ingredients, 'search' => $search]);
    }

    public function create(): View
    {
        return view('admin.ingredients.create');
    }

    public function store(IngredientRequest $request): RedirectResponse
    {
        Ingredient::create($request->validated());

        return redirect()->route('admin.ingredients.index')->with('success', 'Bahan berhasil ditambahkan.');
    }

    public function show(Ingredient $ingredient): View
    {
        $movements = $ingredient->stockMovements()->with(['admin:id,name', 'order:id,order_number'])->latest()->paginate(15);

        return view('admin.ingredients.show', ['ingredient' => $ingredient, 'movements' => $movements]);
    }

    public function edit(Ingredient $ingredient): View
    {
        return view('admin.ingredients.edit', ['ingredient' => $ingredient]);
    }

    public function update(IngredientRequest $request, Ingredient $ingredient): RedirectResponse
    {
        $ingredient->update($request->validated());

        return redirect()->route('admin.ingredients.edit', $ingredient)->with('success', 'Bahan berhasil diperbarui.');
    }

    public function stockIn(Ingredient $ingredient, StockInRequest $request): RedirectResponse
    {
        $this->inventoryService->recordStockIn($ingredient, (float) $request->validated('quantity'), $request->validated('reason'), $this->admin($request));

        return back()->with('success', 'Stok masuk berhasil dicatat.');
    }

    public function adjust(Ingredient $ingredient, StockAdjustmentRequest $request): RedirectResponse
    {
        $this->inventoryService->recordAdjustment($ingredient, (float) $request->validated('quantity'), $request->validated('reason'), $this->admin($request));

        return back()->with('success', 'Penyesuaian stok berhasil dicatat.');
    }

    public function toggle(Ingredient $ingredient): RedirectResponse
    {
        $ingredient->update(['is_active' => ! $ingredient->is_active]);

        return back()->with('success', 'Status bahan berhasil diperbarui.');
    }

    private function admin(Request $request): Admin
    {
        $admin = $request->user('admin');

        abort_unless($admin instanceof Admin, 403);

        return $admin;
    }
}
