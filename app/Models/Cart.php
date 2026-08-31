<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Cart extends Model
{
    use HasFactory;

    protected $fillable = ['checkout_processed_at', 'customer_id', 'session_id'];

    protected function casts(): array
    {
        return ['checkout_processed_at' => 'datetime'];
    }

    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }

    public function itemGroups(): HasMany
    {
        return $this->hasMany(CartItemGroup::class);
    }
}
