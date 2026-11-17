<?php

namespace App\Services;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class CurrencyService
{
    public function getSupportedCurrencies(): array
    {

        $cached = Cache::get('frankfurter.currencies');
        if ($cached) {
            return $cached;
        }
        $currencies = [];

        try {
            $response = Http::timeout(3)->get("https://api.frankfurter.dev/v1/currencies");

            if ($response->successful()) {
                foreach ($response->json() as $code => $name) {
                    $currencies[] = [
                        'value' => $code,
                        'label' => "{$code} - {$name}"
                    ];
                }
            }
        } catch (\Exception $e) {
            Log::error("Currency API error: " . $e->getMessage());
        }

        usort($currencies, fn($a, $b) => strcmp($a['value'], $b['value']));

        if (!empty($currencies)) {
            Cache::put('frankfurter.currencies', $currencies, now()->addDay());
        }

        return $currencies;
    }


    public function isRateServiceAvailable(string $from, string $to): bool
{
    try {
        $response = Http::timeout(3)->get('https://api.frankfurter.dev/v1/latest', [
            'from' => $from,
            'to' => $to,
        ]);

        return $response->successful() && $response->json("rates.{$to}") !== null;
    } catch (\Exception $e) {
        Log::warning('Exchange rate service check failed: ' . $e->getMessage());

        return false;
    }
}
}