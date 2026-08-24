<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ProductBouquetSize extends Model
{
    protected $attributes = [
        'is_active' => true,
        'is_custom' => false,
    ];

    protected $fillable = [
        'code',
        'is_active',
        'is_custom',
        'label',
        'max_sheets',
        'min_sheets',
        'service_price',
    ];

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
            'is_custom' => 'boolean',
            'max_sheets' => 'integer',
            'min_sheets' => 'integer',
            'service_price' => 'decimal:2',
        ];
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    public function appliesToSheetCount(int $sheetCount): bool
    {
        return $sheetCount >= $this->min_sheets
            && ($this->max_sheets === null || $sheetCount <= $this->max_sheets);
    }

    public function getRangeLabelAttribute(): string
    {
        return $this->max_sheets === null
            ? $this->min_sheets.' Lembar ke atas'
            : $this->min_sheets.'-'.$this->max_sheets.' Lembar';
    }
}
