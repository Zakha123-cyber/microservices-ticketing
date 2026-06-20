<?php

namespace App\Services;

use App\Models\Booking;
use Midtrans\Config;
use Midtrans\Snap;

class MidtransService
{
    public function __construct()
    {
        Config::$serverKey = config('services.midtrans.server_key');
        Config::$isProduction = (bool) config('services.midtrans.is_production');
        Config::$isSanitized = true;
        Config::$is3ds = true;
    }

    public function createPayment(Booking $booking): string
    {
        if (blank(config('services.midtrans.server_key')) || str_contains((string) config('services.midtrans.server_key'), 'your_midtrans')) {
            return url('/api/bookings/' . $booking->id . '/simulate-payment');
        }

        $finishUrl = config('services.midtrans.finish_redirect_url')
            ? config('services.midtrans.finish_redirect_url') . '/' . $booking->id
            : url('/api/bookings/' . $booking->id . '/simulate-payment');

        return Snap::createTransaction([
            'transaction_details' => [
                'order_id' => $booking->midtrans_order_id,
                'gross_amount' => (int) $booking->total_price,
            ],
            'callbacks' => [
                'finish' => $finishUrl,
            ],
            'item_details' => [[
                'id' => (string) $booking->event_id,
                'price' => (int) ($booking->total_price / $booking->quantity),
                'quantity' => $booking->quantity,
                'name' => $booking->event_title,
            ]],
        ])->redirect_url;
    }
}
