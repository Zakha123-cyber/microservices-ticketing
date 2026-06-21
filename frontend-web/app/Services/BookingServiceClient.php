<?php

namespace App\Services;

class BookingServiceClient extends ApiClient
{
    public function myBookings(?string $token = null, array $query = []): array
    {
        return $this->get('/bookings/my-bookings', $query, $token);
    }

    public function create(array $payload, ?string $token = null): array
    {
        return $this->post('/bookings', $payload, $token);
    }

    public function find(int $id, ?string $token = null): array
    {
        return $this->get('/bookings/' . $id, [], $token);
    }

    public function updatePayment(int $id, array $payload, ?string $token = null): array
    {
        return $this->post('/bookings/' . $id . '/payment-callback', $payload, $token);
    }

    public function all(?string $token = null, array $query = []): array
    {
        return $this->get('/bookings/admin/all', $query, $token);
    }

    public function verifyTicket(string $bookingCode, ?string $token = null): array
    {
        return $this->post('/bookings/verify', ['booking_code' => $bookingCode], $token);
    }
}
