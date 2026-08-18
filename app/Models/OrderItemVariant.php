<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class OrderItemVariant extends Model
{
    use HasFactory;

    protected $fillable = ['product_variant_id', 'variant_label', 'unit_price', 'quantity_in_bundle', 'line_subtotal'];

    protected function casts(): array
    {
        return ['quantity_in_bundle' => 'integer', 'unit_price' => 'decimal:2', 'line_subtotal' => 'decimal:2'];
    }

    public function group(): BelongsTo
    {
        return $this->belongsTo(OrderItemGroup::class, 'order_item_group_id');
    }

    public function productVariant(): BelongsTo
    {
        return $this->belongsTo(ProductVariant::class);
    }
}
