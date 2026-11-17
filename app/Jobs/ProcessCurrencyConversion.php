<?php

namespace App\Jobs;

use App\Exceptions\ExchangeRateUnavailableException;
use App\Models\Transaction;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class ProcessCurrencyConversion implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $user;
    public $newCurrency;
    public $tries = 1;
    public $timeout = 600;

    public function __construct(User $user, string $newCurrency)
    {
        $this->user = $user;
        $this->newCurrency = $newCurrency;
    }

    public function handle(): void
    {
        $this->user->update(['background_status' => true]);

        try {
            $rateCache = [];
            $updates = [];


            foreach ($this->user->transactions()->cursor() as $transaction) {
                $from = $transaction->original_currency ?? $transaction->currency;
                $sourceAmount = $transaction->original_amount ?? $transaction->amount;
                $date = Carbon::parse($transaction->imported_transaction_date ?? $transaction->created_at)->toDateString();

                $rate = $this->getExchangeRate($from, $this->newCurrency, $date, $rateCache);

                $updates[$transaction->id] = round($sourceAmount * $rate, 2);
            }


            DB::transaction(function () use ($updates) {
                foreach ($updates as $id => $amount) {
                    Transaction::where('id', $id)->update([
                        'amount' => $amount,
                        'currency' => $this->newCurrency,
                    ]);
                }

                User::whereKey($this->user->id)->update(['preferred_currency' => $this->newCurrency]);
            });
        } catch (\Exception $e) {
            Log::error('Error while trying to execute a job: ' . $e->getMessage() . ' on line ' . $e->getLine());
            $this->reportFailure();
            throw $e;
        } finally {
            $this->user->update(['background_status' => false]);
        }
    }

    private function getExchangeRate($from, $to, $date, &$rateCache)
    {
        if ($from === $to) {
            return 1;
        }

        $carbonDate = Carbon::parse($date);

        if ($carbonDate->isWeekend()) {
            $carbonDate->previous('friday');
        }

        $formattedDate = $carbonDate->toDateString();
        $cacheKey = "{$from}_{$to}_{$formattedDate}";

        if (isset($rateCache[$cacheKey])) {
            return $rateCache[$cacheKey];
        }

        try {
            $response = Http::timeout(5)->get("https://api.frankfurter.dev/v1/{$formattedDate}", [
                'from' => $from,
                'to' => $to,
            ]);

            if ($response->successful()) {
                $rate = $response->json("rates.{$to}");
                if ($rate) {
                    $rateCache[$cacheKey] = $rate;
                    return $rate;
                }
            }
        } catch (\Exception $e) {
            Log::warning("Historical exchange rate error for {$formattedDate}: " . $e->getMessage());
        }

        throw new ExchangeRateUnavailableException("No exchange rate {$from}->{$to} for {$date}");
    }

    public function failed(\Throwable $exception)
    {
        if ($this->user) {
            $this->user->update(['background_status' => false]);
            $this->reportFailure();
        }
    }

    private function reportFailure(): void
    {
        Cache::put(
            "background_error.{$this->user->id}",
            'Currency conversion failed because the exchange rate service is not available. Nothing was changed. Please try again later.',
            now()->addHour()
        );
    }
}