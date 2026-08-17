<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Promotion extends Model
{
    use HasFactory;

    protected $attributes = ['is_active' => true, 'usage_limit_per_customer' => 1];

    protected $fillable = ['code', 'type', 'value', 'min_purchase', 'max_discount', 'usage_limit', 'usage_limit_per_customer', 'starts_at', 'expires_at', 'is_active'];

    protected function casts(): array
    {
        return ['expires_at' => 'datetime', 'is_active' => 'boolean', 'max_discount' => 'decimal:2', 'min_purchase' => 'decimal:2', 'starts_at' => 'datetime', 'usage_limit' => 'integer', 'usage_limit_per_customer' => 'integer', 'value' => 'decimal:2'];
    }

    public function usages(): HasMany
    {
        return $this->hasMany(PromotionUsage::class);
    }

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_active', true);
    }
}
