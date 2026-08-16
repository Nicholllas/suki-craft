<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class IngredientStockMovement extends Model
{
    protected $fillable = ['ingredient_id', 'type', 'quantity', 'reason', 'related_order_id', 'created_by'];

    protected function casts(): array
    {
        return ['quantity' => 'decimal:3'];
    }

    public function admin(): BelongsTo
    {
        return $this->belongsTo(Admin::class, 'created_by');
    }

    public function ingredient(): BelongsTo
    {
        return $this->belongsTo(Ingredient::class);
    }

    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class, 'related_order_id');
    }
}
