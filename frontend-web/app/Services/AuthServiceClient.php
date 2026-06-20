<?php

namespace App\Services;

class AuthServiceClient extends ApiClient
{
    public function login(array $payload): array
    {
        return $this->post('/auth/login', $payload);
    }

    public function register(array $payload): array
    {
        return $this->post('/auth/register', $payload);
    }
}
