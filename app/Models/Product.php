<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Product extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'category_id',
        'name',
        'slug',
        'description',
        'base_price',
        'price',
        'cost_price',
        'stock',
        'is_active',
        'is_featured',
    ];

    protected function casts(): array
    {
        return [
            'base_price' => 'decimal:2',
            'cost_price' => 'decimal:2',
            'is_active' => 'boolean',
            'is_featured' => 'boolean',
            'price' => 'decimal:2',
            'stock' => 'integer',
        ];
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    public function images(): HasMany
    {
        return $this->hasMany(ProductImage::class)->orderBy('sort_order');
    }

    public function ingredients(): HasMany
    {
        return $this->hasMany(ProductIngredient::class);
    }

    public function variants(): HasMany
    {
        return $this->hasMany(ProductVariant::class)->orderBy('label');
    }

    public function getPrimaryImageAttribute(): ?ProductImage
    {
        $images = $this->relationLoaded('images') ? $this->images : $this->images()->get();

        return $images->firstWhere('is_primary', true) ?? $images->first();
    }

    public function getFinalPriceAttribute(): float
    {
        $adjustment = $this->relationLoaded('variants')
            ? $this->variants->where('is_active', true)->min('price_adjustment')
            : $this->variants()->where('is_active', true)->min('price_adjustment');

        return (float) $this->base_price + (float) ($adjustment ?? 0);
    }
}
