<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;

class EventServiceClient
{
    public function checkAvailability(int $eventId, int $quantity): array
    {
        return Http::post(config('services.event.url') . '/api/events/internal/check-availability', [
            'event_id' => $eventId,
            'quantity' => $quantity,
        ])->throw()->json('data');
    }

    public function reduceQuota(int $eventId, int $quantity): array
    {
        return Http::post(config('services.event.url') . '/api/events/internal/reduce-quota', [
            'event_id' => $eventId,
            'quantity' => $quantity,
        ])->throw()->json('data') ?? [];
    }
}
