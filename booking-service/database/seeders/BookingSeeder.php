<?php

namespace Database\Seeders;

use App\Models\Booking;
use Illuminate\Database\Seeder;

class BookingSeeder extends Seeder
{
    public function run(): void
    {
        Booking::firstOrCreate(['booking_code' => 'BKG-SEED-001'], [
            'user_id' => 2,
            'event_id' => 1,
            'event_title' => 'Rock Concert 2026',
            'quantity' => 2,
            'total_price' => 1000000,
            'status' => 'paid',
            'midtrans_order_id' => 'ORDER-SEED-001',
            'paid_at' => now(),
        ]);
    }
}
