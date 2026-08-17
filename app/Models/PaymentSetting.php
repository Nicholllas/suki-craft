<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PaymentSetting extends Model
{
    protected $fillable = ['bank_account_holder', 'bank_account_number', 'bank_name', 'qris_image_path', 'qris_payload'];
}
