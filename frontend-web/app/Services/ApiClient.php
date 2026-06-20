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

    public function put(string $path, array $payload = [], ?string $token = null): array
    {
        return $this->withToken($token)->put($this->baseUrl . $path, $payload)->json() ?? [];
    }

    public function delete(string $path, ?string $token = null): array
    {
        return $this->withToken($token)->delete($this->baseUrl . $path)->json() ?? [];
    }

    public function multipart(string $method, string $path, array $payload, ?string $token = null): array
    {
        $request = $this->withToken($token)->asMultipart();
        $multipart = [];

        foreach ($payload as $name => $value) {
            if ($value instanceof \Illuminate\Http\UploadedFile) {
                $multipart[] = [
                    'name' => $name,
                    'contents' => fopen($value->getRealPath(), 'r'),
                    'filename' => $value->getClientOriginalName(),
                ];
                continue;
            }

            if ($value !== null) {
                $multipart[] = [
                    'name' => $name,
                    'contents' => (string) $value,
                ];
            }
        }

        return $request->send(strtoupper($method), $this->baseUrl . $path, [
            'multipart' => $multipart,
        ])->json() ?? [];
    }

    protected function withToken(?string $token)
    {
        $request = Http::acceptJson();
        return $token ? $request->withToken($token) : $request;
    }
}
