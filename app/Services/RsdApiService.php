<?php

namespace App\Services;

use Carbon\Carbon;
use Illuminate\Http\Client\PendingRequest;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Throwable;

class RsdApiService
{
    protected string $base;
    protected string $email;
    protected string $password;

    protected int $tokenTtl   = 55 * 60;
    protected int $listTtl    = 60 * 60;
    protected int $pricesTtl  = 10 * 60;

    public function __construct()
    {
        $this->base     = rtrim(config('services.rsd.base', ''), '/');
        $this->email    = (string) config('services.rsd.email', '');
        $this->password = (string) config('services.rsd.password', '');
    }

    /* ==================== Auth & HTTP client ==================== */

    protected function tokenCacheKey(): string
    {
        return 'rsdapi.token.' . md5($this->email . '|' . $this->base);
    }

    protected function fetchToken(): string
    {
        $resp = Http::asJson()
            ->timeout(15)
            ->retry(1, 250)
            ->withHeaders(['Accept' => 'application/json', 'User-Agent' => 'DeltaSuryaApp/1.0'])
            ->post($this->base . '/auth', [
                'email'    => $this->email,
                'password' => $this->password,
            ])->throw()->json();

        $token = Arr::get($resp, 'access_token') ?? Arr::get($resp, 'token') ?? Arr::get($resp, 'data.token');
        if (!$token) {
            throw new \RuntimeException('Token tidak ditemukan pada response auth.');
        }
        return $token;
    }

    protected function token(): string
    {
        return Cache::remember($this->tokenCacheKey(), $this->tokenTtl, fn() => $this->fetchToken());
    }

    protected function forgetToken(): void
    {
        Cache::forget($this->tokenCacheKey());
    }

    protected function client(?string $token = null): PendingRequest
    {
        $token = $token ?: $this->token();
        return Http::baseUrl($this->base)
            ->timeout(15)
            ->retry(1, 300)
            ->acceptJson()
            ->withToken($token)
            ->withHeaders(['User-Agent' => 'DeltaSuryaApp/1.0']);
    }

    protected function request(string $method, string $url, array $options = []): array
    {
        try {
            $res = $this->client()->send($method, ltrim($url, '/'), $options);
            if ($res->status() === 401) {
                $this->forgetToken();
                $res = $this->client()->send($method, ltrim($url, '/'), $options);
            }
            return $res->throw()->json() ?? [];
        } catch (Throwable $e) {
            throw $e;
        }
    }

    /* ====================== Public API ====================== */

    public function listMedicines(): array
    {
        $key = 'rsdapi.medicines';
        return Cache::remember($key, $this->listTtl, function () {
            $json = $this->request('GET', '/medicines');
            return Arr::get($json, 'medicines', []);
        });
    }

    public function listPrices(string $medicineId): array
    {
        $key = 'rsdapi.prices.' . $medicineId;
        return Cache::remember($key, $this->pricesTtl, function () use ($medicineId) {
            $json = $this->request('GET', "/medicines/{$medicineId}/prices");
            return Arr::get($json, 'prices', []);
        });
    }
}
