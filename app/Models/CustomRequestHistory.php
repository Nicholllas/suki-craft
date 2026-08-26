<?php

namespace App\Models;

use App\Enums\CustomRequestStatus;
use Database\Factories\CustomRequestHistoryFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CustomRequestHistory extends Model
{
    /** @use HasFactory<CustomRequestHistoryFactory> */
    use HasFactory;

    protected $fillable = ['actor_type', 'admin_id', 'customer_id', 'note', 'status'];

    protected function casts(): array
    {
        return ['status' => CustomRequestStatus::class];
    }

    public function customRequest(): BelongsTo
    {
        return $this->belongsTo(CustomRequest::class);
    }

    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }

    public function admin(): BelongsTo
    {
        return $this->belongsTo(Admin::class);
    }
}
