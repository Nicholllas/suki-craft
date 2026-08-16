<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Ingredient extends Model
{
    use HasFactory;

    protected $fillable = ['name', 'unit', 'current_stock', 'minimum_stock', 'is_active'];

    protected function casts(): array
    {
        return [
            'current_stock' => 'decimal:3',
            'is_active' => 'boolean',
            'minimum_stock' => 'decimal:3',
        ];
    }

    public function productIngredients(): HasMany
    {
        return $this->hasMany(ProductIngredient::class);
    }

    public function stockMovements(): HasMany
    {
        return $this->hasMany(IngredientStockMovement::class);
    }
}
