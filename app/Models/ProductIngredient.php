<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ProductIngredient extends Model
{
    protected $attributes = ['ratio_per_unit' => 1];

    protected $fillable = ['ingredient_id', 'product_id', 'product_variant_id', 'quantity_needed', 'ratio_per_unit'];

    protected function casts(): array
    {
        return ['quantity_needed' => 'decimal:3', 'ratio_per_unit' => 'decimal:3'];
    }

    public function ingredient(): BelongsTo
    {
        return $this->belongsTo(Ingredient::class);
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    public function productVariant(): BelongsTo
    {
        return $this->belongsTo(ProductVariant::class);
    }
}
