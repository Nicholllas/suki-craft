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
        DB::transaction(function () use ($order): void {
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
        });
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
        $order->loadMissing([
            'itemGroups:id,order_id,product_id,bundle_quantity',
            'itemGroups.variants:id,order_item_group_id,product_variant_id,quantity_in_bundle',
            'itemGroups.variants.productVariant:id,is_quantity_based',
        ]);
        $recipesByProduct = ProductIngredient::query()->whereIn('product_id', $order->itemGroups->pluck('product_id')->unique())->get(['ingredient_id', 'product_id', 'product_variant_id', 'quantity_needed', 'ratio_per_unit'])->groupBy('product_id');

        foreach ($order->itemGroups as $itemGroup) {
            $recipes = $recipesByProduct->get($itemGroup->product_id, collect());

            foreach ($recipes->whereNull('product_variant_id') as $recipe) {
                $quantity = (float) $recipe->quantity_needed * $itemGroup->bundle_quantity;
                $requirements->put($recipe->ingredient_id, (float) $requirements->get($recipe->ingredient_id, 0) + $quantity);
            }

            foreach ($itemGroup->variants as $variant) {
                if (! $variant->product_variant_id || ! $variant->productVariant) {
                    continue;
                }

                foreach ($recipes->where('product_variant_id', $variant->product_variant_id) as $recipe) {
                    $quantityPerVariant = $variant->productVariant->is_quantity_based ? (float) $recipe->ratio_per_unit : (float) $recipe->quantity_needed;
                    $quantity = $quantityPerVariant * $variant->quantity_in_bundle * $itemGroup->bundle_quantity;
                    $requirements->put($recipe->ingredient_id, (float) $requirements->get($recipe->ingredient_id, 0) + $quantity);
                }
            }
        }

        return $requirements;
    }
}
