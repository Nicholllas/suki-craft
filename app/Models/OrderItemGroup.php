<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class OrderItemGroup extends Model
{
    use HasFactory;

    protected $fillable = ['bouquet_size_id', 'bouquet_size_label', 'bundle_quantity', 'card_message', 'custom_request_id', 'order_id', 'product_id', 'product_name', 'requires_quote', 'service_price', 'special_note', 'subtotal'];

    protected function casts(): array
    {
        return ['bundle_quantity' => 'integer', 'requires_quote' => 'boolean', 'service_price' => 'decimal:2', 'subtotal' => 'decimal:2'];
    }

    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
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

    public function review(): HasOne
    {
        return $this->hasOne(Review::class);
    }

    public function variants(): HasMany
    {
        return $this->hasMany(OrderItemVariant::class);
    }
}
