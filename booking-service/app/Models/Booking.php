<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Booking extends Model
{
    protected $fillable = [
        'booking_code',
        'user_id',
        'event_id',
        'event_title',
        'quantity',
        'total_price',
        'status',
        'payment_url',
        'midtrans_order_id',
        'midtrans_transaction_id',
        'paid_at',
    ];
}
