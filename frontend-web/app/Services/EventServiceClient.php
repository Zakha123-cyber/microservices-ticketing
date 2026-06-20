<?php

namespace App\Services;

class EventServiceClient extends ApiClient
{
    public function all(array $query = [], ?string $token = null): array
    {
        return $this->get('/events', $query, $token);
    }

    public function find(int $id, ?string $token = null): array
    {
        return $this->get('/events/' . $id, [], $token);
    }
}
