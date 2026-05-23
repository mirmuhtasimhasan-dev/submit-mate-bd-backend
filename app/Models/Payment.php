<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Payment extends Model
{
    use HasFactory;

    protected $fillable = [
        'order_id',
        'method',
        'provider',
        'amount',
        'sender_number',
        'transaction_id',
        'screenshot_path',
        'gateway_payment_id',
        'status',
        'verified_at',
        'admin_note',
    ];

    public function order()
    {
        return $this->belongsTo(Order::class);
    }
}