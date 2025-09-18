<?php

namespace App\Services;

use Carbon\Carbon;
use Illuminate\Support\Arr;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;

class PriceService
{
    public function __construct(private RsdApiService $api) {}
    public function priceAt(string $medicineId, Carbon $at): ?int
    {
        $dateStr = $at->toDateString();
        $cacheKey = "priceAt:{$medicineId}:{$dateStr}";

        return Cache::remember($cacheKey, 300, function () use ($medicineId, $dateStr) {
            $prices = collect($this->api->listPrices($medicineId));
            $norm = $prices->map(function ($p) {
                return [
                    'unit_price' => (int) Arr::get($p, 'unit_price'),
                    'start'      => Arr::get($p, 'start_date.value'),
                    'end'        => Arr::get($p, 'end_date.value'),
                ];
            });

            $matches = $norm->filter(function ($row) use ($dateStr) {
                $startOk = $row['start'] && $row['start'] <= $dateStr;
                $endVal  = $row['end'];
                $endOk   = is_null($endVal) || $dateStr <= $endVal;
                return $startOk && $endOk;
            });

            if ($matches->isEmpty()) {
                return null;
            }

            $picked = $matches->sortByDesc('start')->first();

            return $picked['unit_price'] ?? null;
        });
    }

    public function priceRowAt(string $medicineId, Carbon $at): ?array
    {
        $dateStr = $at->toDateString();
        $prices  = collect($this->api->listPrices($medicineId))->map(function ($p) {
            return [
                'id'         => Arr::get($p, 'id'),
                'unit_price' => (int) Arr::get($p, 'unit_price'),
                'start'      => Arr::get($p, 'start_date.value'),
                'end'        => Arr::get($p, 'end_date.value'),
            ];
        });

        $picked = $prices->filter(
            fn($r) =>
            $r['start'] && $r['start'] <= $dateStr && (is_null($r['end']) || $dateStr <= $r['end'])
        )->sortByDesc('start')->first();

        return $picked ?: null;
    }
}
