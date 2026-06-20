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

    public function create(array $payload, ?string $token = null): array
    {
        return $this->multipart('POST', '/events', $payload, $token);
    }

    public function update(int $id, array $payload, ?string $token = null): array
    {
        return $this->multipart('POST', '/events/' . $id . '/update', $payload, $token);
    }

    public function deleteEvent(int $id, ?string $token = null): array
    {
        return $this->delete('/events/' . $id, $token);
    }
}
