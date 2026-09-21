<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class CurrencyService
{
    public function getSupportedCurrencies(): array
    {
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

        return $currencies;
    }
}