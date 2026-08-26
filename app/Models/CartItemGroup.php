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

    protected $fillable = ['bouquet_size_id', 'bouquet_size_label', 'bundle_quantity', 'card_message', 'custom_request_id', 'product_id', 'requires_quote', 'service_price', 'special_note'];

    protected function casts(): array
    {
        return ['bundle_quantity' => 'integer', 'requires_quote' => 'boolean', 'service_price' => 'decimal:2'];
    }

    public function cart(): BelongsTo
    {
        return $this->belongsTo(Cart::class);
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    public function bouquetSize(): BelongsTo
    {
        return $this->belongsTo(ProductBouquetSize::class);
    }

    public function customRequest(): BelongsTo
    {
        return $this->belongsTo(CustomRequest::class);
    }

    public function variants(): HasMany
    {
        return $this->hasMany(CartItemVariant::class);
    }

    public function getBundleSubtotalAttribute(): float
    {
        return (float) ($this->service_price ?? $this->product->base_price) + $this->variants->sum(fn (CartItemVariant $variant): float => $variant->lineSubtotal);
    }

    public function getSubtotalAttribute(): float
    {
        return $this->bundleSubtotal * $this->bundle_quantity;
    }
}
