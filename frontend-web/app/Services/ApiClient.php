<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;

class ApiClient
{
    protected string $baseUrl;

    public function __construct()
    {
        $this->baseUrl = rtrim(env('API_GATEWAY_URL', 'http://localhost:8000/api'), '/');
    }

    public function get(string $path, array $query = [], ?string $token = null): array
    {
        return $this->withToken($token)->get($this->baseUrl . $path, $query)->json() ?? [];
    }

    public function post(string $path, array $payload = [], ?string $token = null): array
    {
        return $this->withToken($token)->post($this->baseUrl . $path, $payload)->json() ?? [];
    }

    protected function withToken(?string $token)
    {
        $request = Http::acceptJson();
        return $token ? $request->withToken($token) : $request;
    }
}
