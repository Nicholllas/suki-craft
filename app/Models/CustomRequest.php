<?php

namespace App\Models;

use App\Enums\CustomRequestStatus;
use Database\Factories\CustomRequestFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class CustomRequest extends Model
{
    /** @use HasFactory<CustomRequestFactory> */
    use HasFactory;

    protected $attributes = [
        'request_kind' => 'full_bouquet',
        'status' => CustomRequestStatus::WAITING_REVIEW->value,
    ];

    protected $fillable = [
        'customer_id',
        'product_id',
        'custom_bouquet_category_id',
        'custom_category_name',
        'request_number',
        'request_kind',
        'status',
        'item_source',
        'budget_min',
        'budget_max',
        'wrapping_preference',
        'additional_notes',
        'reference_image_path',
        'base_configuration',
        'needed_date',
        'quoted_price',
        'quote_note',
        'quoted_at',
        'quote_expires_at',
        'approved_at',
        'converted_to_cart_at',
    ];

    protected function casts(): array
    {
        return [
            'approved_at' => 'datetime',
            'base_configuration' => 'array',
            'budget_max' => 'decimal:2',
            'budget_min' => 'decimal:2',
            'converted_to_cart_at' => 'datetime',
            'needed_date' => 'date',
            'quote_expires_at' => 'datetime',
            'quoted_at' => 'datetime',
            'quoted_price' => 'decimal:2',
            'status' => CustomRequestStatus::class,
        ];
    }

    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    public function customBouquetCategory(): BelongsTo
    {
        return $this->belongsTo(CustomBouquetCategory::class);
    }

    public function items(): HasMany
    {
        return $this->hasMany(CustomRequestItem::class)->orderBy('id');
    }

    public function histories(): HasMany
    {
        return $this->hasMany(CustomRequestHistory::class)->orderBy('created_at')->orderBy('id');
    }

    public function cartItemGroup(): HasOne
    {
        return $this->hasOne(CartItemGroup::class);
    }

    public function orderItemGroup(): HasOne
    {
        return $this->hasOne(OrderItemGroup::class);
    }
}
