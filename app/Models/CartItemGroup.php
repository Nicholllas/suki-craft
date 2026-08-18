<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class CartItemGroup extends Model
{
    use HasFactory;

    protected $attributes = ['bundle_quantity' => 1];

    protected $fillable = ['product_id', 'bundle_quantity', 'card_message', 'special_note'];

    protected function casts(): array
    {
        return ['bundle_quantity' => 'integer'];
    }

    public function cart(): BelongsTo
    {
        return $this->belongsTo(Cart::class);
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    public function variants(): HasMany
    {
        return $this->hasMany(CartItemVariant::class);
    }

    public function getBundleSubtotalAttribute(): float
    {
        return (float) $this->product->base_price + $this->variants->sum(fn (CartItemVariant $variant): float => $variant->lineSubtotal);
    }

    public function getSubtotalAttribute(): float
    {
        return $this->bundleSubtotal * $this->bundle_quantity;
    }
}
