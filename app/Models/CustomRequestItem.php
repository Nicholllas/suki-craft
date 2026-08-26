<?php

namespace App\Models;

use Database\Factories\CustomRequestItemFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CustomRequestItem extends Model
{
    /** @use HasFactory<CustomRequestItemFactory> */
    use HasFactory;

    protected $fillable = ['item_name', 'notes', 'quantity'];

    protected function casts(): array
    {
        return ['quantity' => 'integer'];
    }

    public function customRequest(): BelongsTo
    {
        return $this->belongsTo(CustomRequest::class);
    }
}
