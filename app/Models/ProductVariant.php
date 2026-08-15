<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ProductVariant extends Model
{
    protected $fillable = ['label', 'price_adjustment', 'sku', 'is_active'];

    protected function casts(): array
    {
        return ['is_active' => 'boolean', 'price_adjustment' => 'decimal:2'];
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    public function getFinalPriceAttribute(): float
    {
        return (float) $this->product->base_price + (float) $this->price_adjustment;
    }
}
