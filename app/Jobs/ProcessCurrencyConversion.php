<?php

namespace App\Jobs;

use App\Models\Transaction;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Http;

class ProcessCurrencyConversion implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $user;
    public $newCurrency;

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
            $newCurrency = $this->newCurrency;
            $user = $this->user;

            $user->transactions()->chunk(100, function ($transactions) use ($newCurrency, &$rateCache) {
                foreach ($transactions as $transaction) {
                    $from = $transaction->original_currency ?? $transaction->currency;
                    $sourceAmount = $transaction->original_amount ?? $transaction->amount;
                    $to = $newCurrency;
                    $date = $transaction->created_at ? $transaction->created_at->toDateString() : now()->toDateString();

                    $rate = $this->getExchangeRate($from, $to, $date, $rateCache);

                    Transaction::where('id', $transaction->id)->update([
                        'amount' => round($sourceAmount * $rate, 2),
                        'currency' => $to,
                    ]);
                }
            });

        } catch (\Exception $e) {
            \Log::error("Error while trying to execute a job: " . $e->getMessage() . " on line " . $e->getLine());
            throw $e;

        } finally {
            $this->user->update(['background_status' => false]);
        }
    }

    private function getExchangeRate($from, $to, $date, &$rateCache)
    {
        if ($from === $to)
            return 1;

        // Check if date is weekend day 
        $carbonDate = Carbon::parse($date);
        if ($carbonDate->isWeekend()) {
            // If its weekend set date to friday
            $carbonDate->previous('friday');
            $date = $carbonDate->toDateString();
        }

        $cacheKey = "{$from}_{$to}_{$date}";
        if (isset($rateCache[$cacheKey]))
            return $rateCache[$cacheKey];

        try {
            $response = Http::get("https://api.frankfurter.dev/{$date}", [
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
            \Log::warning("Exchange rate API error for {$from} to {$to} on {$date}: " . $e->getMessage());
        }

        // if it still fails take latest exchange rate 
        try {
            $response = Http::get("https://api.frankfurter.dev/latest", [
                'from' => $from,
                'to' => $to,
            ]);
            if ($response->successful()) {
                $rate = $response->json("rates.{$to}");
                if ($rate) {
                    return $rate;
                }
            }
        } catch (\Exception $e) {
        }

        // If everything fails return 1/1 
        return 1;
    }

    // Safety net if job fails
    public function failed(\Throwable $exception)
    {
        $user = $this->user;
        if ($user) {
            $user->update(['background_status' => false]);
        }
    }
}