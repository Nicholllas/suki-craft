<?php

namespace App\Services;

use App\Models\Admin;
use App\Models\Ingredient;
use App\Models\Order;
use App\Models\ProductIngredient;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class InventoryService
{
    public function recordStockIn(Ingredient $ingredient, float $quantity, string $reason, Admin $admin): void
    {
        DB::transaction(function () use ($admin, $ingredient, $quantity, $reason) {
            $lockedIngredient = $this->lockedIngredient($ingredient->id);
            $lockedIngredient->update(['current_stock' => (float) $lockedIngredient->current_stock + $quantity]);
            $lockedIngredient->stockMovements()->create(['created_by' => $admin->id, 'quantity' => $quantity, 'reason' => $reason, 'type' => 'in']);
        });
    }

    public function recordAdjustment(Ingredient $ingredient, float $newQuantity, string $reason, Admin $admin): void
    {
        DB::transaction(function () use ($admin, $ingredient, $newQuantity, $reason) {
            $lockedIngredient = $this->lockedIngredient($ingredient->id);
            $difference = $newQuantity - (float) $lockedIngredient->current_stock;
            $lockedIngredient->update(['current_stock' => $newQuantity]);
            $lockedIngredient->stockMovements()->create(['created_by' => $admin->id, 'quantity' => $difference, 'reason' => $reason, 'type' => 'adjustment']);
        });
    }

    public function deductForOrder(Order $order): void
    {
        if ($order->ingredientStockMovements()->where('type', 'out')->exists()) {
            return;
        }

        $requirements = $this->requirementsForOrder($order);

        if ($requirements->isEmpty()) {
            return;
        }

        $ingredients = Ingredient::query()->whereKey($requirements->keys())->lockForUpdate()->get()->keyBy('id');

        foreach ($requirements as $ingredientId => $quantity) {
            $ingredient = $ingredients->get($ingredientId);

            if (! $ingredient) {
                continue;
            }

            $newStock = (float) $ingredient->current_stock - $quantity;
            $ingredient->update(['current_stock' => $newStock]);
            $ingredient->stockMovements()->create(['quantity' => -$quantity, 'related_order_id' => $order->id, 'reason' => 'Pemakaian untuk pesanan '.$order->order_number, 'type' => 'out']);

            if ($newStock <= (float) $ingredient->minimum_stock) {
                Log::warning('Stok bahan berada di bawah ambang minimum setelah pesanan diproses.', [
                    'current_stock' => $newStock,
                    'ingredient_id' => $ingredient->id,
                    'minimum_stock' => (float) $ingredient->minimum_stock,
                    'order_id' => $order->id,
                ]);
            }
        }
    }

    public function getLowStockIngredients(): Collection
    {
        return Ingredient::query()->where('is_active', true)->whereColumn('current_stock', '<=', 'minimum_stock')->orderBy('current_stock')->get();
    }

    private function lockedIngredient(int $ingredientId): Ingredient
    {
        return Ingredient::query()->whereKey($ingredientId)->lockForUpdate()->firstOrFail();
    }

    private function requirementsForOrder(Order $order): Collection
    {
        $requirements = collect();
        $order->loadMissing('itemGroups.variants:id,order_item_group_id,product_variant_id,quantity_in_bundle');

        foreach ($order->itemGroups as $itemGroup) {
            if ($itemGroup->variants->isEmpty()) {
                foreach ($this->recipesForItem($itemGroup->product_id, null) as $recipe) {
                    $requirements[$recipe->ingredient_id] = ($requirements[$recipe->ingredient_id] ?? 0) + ((float) $recipe->quantity_needed * $itemGroup->bundle_quantity);
                }

                continue;
            }

            foreach ($itemGroup->variants as $variant) {
                foreach ($this->recipesForItem($itemGroup->product_id, $variant->product_variant_id) as $recipe) {
                    $requirements[$recipe->ingredient_id] = ($requirements[$recipe->ingredient_id] ?? 0) + ((float) $recipe->quantity_needed * $variant->quantity_in_bundle * $itemGroup->bundle_quantity);
                }
            }
        }

        return $requirements;
    }

    private function recipesForItem(int $productId, ?int $variantId): Collection
    {
        if ($variantId) {
            $variantRecipes = ProductIngredient::query()->where('product_id', $productId)->where('product_variant_id', $variantId)->get();

            if ($variantRecipes->isNotEmpty()) {
                return $variantRecipes;
            }
        }

        return ProductIngredient::query()->where('product_id', $productId)->whereNull('product_variant_id')->get();
    }
}
