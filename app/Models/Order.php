<?php

namespace App\Models;

use App\Enums\OrderStatus;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Support\Str;

class Order extends Model
{
    use HasFactory;

    protected $attributes = ['status' => OrderStatus::PENDING_PAYMENT->value];

    protected $fillable = [
        'order_number',
        'public_token',
        'customer_id',
        'promotion_id',
        'customer_name',
        'customer_phone',
        'customer_email',
        'delivery_address',
        'delivery_date',
        'delivery_time_slot',
        'courier_id',
        'delivery_proof_path',
        'delivered_at',
        'cancellation_reason',
        'subtotal',
        'delivery_fee',
        'discount_amount',
        'total',
        'status',
        'notes',
    ];

    protected function casts(): array
    {
        return [
            'delivery_date' => 'date',
            'delivered_at' => 'datetime',
            'delivery_fee' => 'decimal:2',
            'discount_amount' => 'decimal:2',
            'status' => OrderStatus::class,
            'subtotal' => 'decimal:2',
            'total' => 'decimal:2',
        ];
    }

    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }

    public function promotion(): BelongsTo
    {
        return $this->belongsTo(Promotion::class);
    }

    public function courier(): BelongsTo
    {
        return $this->belongsTo(Courier::class);
    }

    public function items(): HasMany
    {
        return $this->hasMany(OrderItem::class);
    }

    public function ingredientStockMovements(): HasMany
    {
        return $this->hasMany(IngredientStockMovement::class, 'related_order_id');
    }

    public function latestPaymentProof(): HasOne
    {
        return $this->hasOne(PaymentProof::class)->latestOfMany('uploaded_at');
    }

    public function paymentProofs(): HasMany
    {
        return $this->hasMany(PaymentProof::class);
    }

    public function reviews(): HasMany
    {
        return $this->hasMany(Review::class);
    }

    public function statusHistories(): HasMany
    {
        return $this->hasMany(OrderStatusHistory::class);
    }

    public function paymentDeadline(): Carbon
    {
        return $this->delivery_date->copy()->setTimeFromTimeString(Str::before($this->delivery_time_slot, '-'));
    }

    public function paymentDeadlineHasPassed(): bool
    {
        return now()->greaterThanOrEqualTo($this->paymentDeadline());
    }
}
