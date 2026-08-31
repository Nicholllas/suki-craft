<?php

namespace App\Models;

use Database\Factories\CustomBouquetCategoryFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class CustomBouquetCategory extends Model
{
    /** @use HasFactory<CustomBouquetCategoryFactory> */
    use HasFactory;

    protected $attributes = [
        'is_active' => true,
        'sort_order' => 0,
    ];

    protected $fillable = [
        'name',
        'slug',
        'description',
        'quantity_label',
        'quote_threshold',
        'is_active',
        'sort_order',
    ];

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
            'quote_threshold' => 'integer',
            'sort_order' => 'integer',
        ];
    }

    public function customRequests(): HasMany
    {
        return $this->hasMany(CustomRequest::class);
    }

    public function products(): HasMany
    {
        return $this->hasMany(Product::class);
    }

    public function requiresCustomRequestForQuantity(int $quantity): bool
    {
        return $this->quote_threshold !== null && $quantity >= $this->quote_threshold;
    }
}
