<?php

namespace App\Models;

use App\Enums\PaymentProofStatus;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Str;

class PaymentProof extends Model
{
    use HasFactory;

    protected $attributes = ['status' => PaymentProofStatus::PENDING->value];

    protected $fillable = [
        'path',
        'uploaded_at',
        'verified_at',
        'verified_by',
        'status',
        'rejection_reason',
    ];

    protected function casts(): array
    {
        return [
            'status' => PaymentProofStatus::class,
            'uploaded_at' => 'datetime',
            'verified_at' => 'datetime',
        ];
    }

    public function getIsPdfAttribute(): bool
    {
        return Str::endsWith($this->path, '.pdf');
    }

    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }

    public function verifier(): BelongsTo
    {
        return $this->belongsTo(Admin::class, 'verified_by');
    }
}
