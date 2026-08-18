<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CartItemVariant extends Model
{
    use HasFactory;

    protected $attributes = ['quantity_in_bundle' => 1];

    protected $fillable = ['product_variant_id', 'unit_price', 'quantity_in_bundle'];

    protected function casts(): array
    {
        return ['quantity_in_bundle' => 'integer', 'unit_price' => 'decimal:2'];
    }

    public function group(): BelongsTo
    {
        return $this->belongsTo(CartItemGroup::class, 'cart_item_group_id');
    }

    public function productVariant(): BelongsTo
    {
        return $this->belongsTo(ProductVariant::class);
    }

    public function getLineSubtotalAttribute(): float
    {
        return (float) $this->unit_price * $this->quantity_in_bundle;
    }
}
