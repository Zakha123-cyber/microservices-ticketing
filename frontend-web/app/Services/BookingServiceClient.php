<?php

namespace App\Services;

class BookingServiceClient extends ApiClient
{
    public function myBookings(?string $token = null): array
    {
        return $this->get('/bookings/my-bookings', [], $token);
    }
}
