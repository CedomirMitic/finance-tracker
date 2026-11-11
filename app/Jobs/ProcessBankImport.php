<?php

namespace App\Jobs;

use App\Models\Transaction;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class ProcessBankImport implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $userId;
    protected $rows;
    protected $mapping;
    protected $importCurrency;

    public function __construct($userId, array $rows, array $mapping, string $importCurrency)
    {
        $this->userId = $userId;
        $this->rows = $rows;
        $this->mapping = $mapping;
        $this->importCurrency = strtoupper($importCurrency);
    }

    public $tries = 3;
    public $backoff = [30, 120];

    public function handle(): void
    {
        $user = User::find($this->userId);

        if (!$user) {
            return;
        }

        $user->update(['background_status' => true]);

        try {
            $targetCurrency = strtoupper($user->preferred_currency ?? 'EUR');
            $rateCache = [];
            $records = [];

            
            foreach ($this->rows as $row) {
                $rawAmount = $row[$this->mapping['amount_col']] ?? null;
                $description = $row[$this->mapping['description_col']] ?? 'Imported Transaction';

                
                $cleanAmountStr = preg_replace('/[^\d.,-]/', '', (string) $rawAmount);

              
                if (!preg_match('/\d/', $cleanAmountStr)) {
                    continue;
                }

                if (strpos($cleanAmountStr, ',') !== false && strpos($cleanAmountStr, '.') !== false) {
                    if (strrpos($cleanAmountStr, ',') > strrpos($cleanAmountStr, '.')) {
                        $cleanAmount = floatval(str_replace('.', '', str_replace(',', '.', $cleanAmountStr)));
                    } else {
                        $cleanAmount = floatval(str_replace(',', '', $cleanAmountStr));
                    }
                } else {
                    $cleanAmount = floatval(str_replace(',', '.', $cleanAmountStr));
                }

                $sourceAmount = abs($cleanAmount);

                if ($sourceAmount == 0) {
                    continue;
                }

                $rawDate = isset($this->mapping['date_col']) && isset($row[$this->mapping['date_col']])
                    ? $row[$this->mapping['date_col']] : null;
                $transactionDate = $rawDate ? date('Y-m-d', strtotime($rawDate)) : now()->toDateString();

                if (!$transactionDate || $transactionDate === '1970-01-01') {
                    $transactionDate = now()->toDateString();
                }

                $amountInTarget = $sourceAmount;

                if ($this->importCurrency !== $targetCurrency) {
                    $amountInTarget = $sourceAmount * $this->getRate($transactionDate, $targetCurrency, $rateCache);
                }

                $records[] = [
                    'user_id' => $user->id,
                    'imported_transaction_date' => $transactionDate,
                    'amount' => round($amountInTarget, 2),
                    'currency' => $targetCurrency,
                    'original_amount' => $sourceAmount,
                    'original_currency' => $this->importCurrency,
                    'type' => $cleanAmount < 0 ? 'expense' : 'income',
                    'description' => Str::limit(trim((string) $description), 255, ''),
                    'category' => 'Other',
                    'payment_type' => 'manual',
                    'billing_day' => null,
                ];
            }

            
            DB::transaction(function () use ($records) {
                foreach ($records as $record) {
                    Transaction::create($record);
                }
            });

            Log::info('Successfully imported ' . count($records) . " transactions for user ID: {$user->id}");
        } catch (\Exception $e) {
            Log::error('Error while trying to execute a job: ' . $e->getMessage() . ' on line ' . $e->getLine());
            throw $e;
        } finally {
            $user->update(['background_status' => false]);
        }
    }

    private function getRate(string $date, string $target, array &$rateCache): float
    {
        $key = "{$date}_{$this->importCurrency}_{$target}";

        if (!isset($rateCache[$key])) {
            $rate = null;

            try {
                $response = Http::timeout(5)->get("https://api.frankfurter.dev/v1/{$date}", [
                    'from' => $this->importCurrency,
                    'to' => $target,
                ]);

                if ($response->successful()) {
                    $rate = $response->json("rates.{$target}");
                }
            } catch (\Exception $e) {
                Log::error("Exchange rate API error for {$date}: " . $e->getMessage());
            }

            if (!$rate || $rate <= 0) {
                throw new \RuntimeException("No exchange rate {$this->importCurrency}->{$target} for {$date}");
            }

            $rateCache[$key] = $rate;
        }

        return $rateCache[$key];
    }
    public function failed(\Throwable $exception)
    {
        $user = User::find($this->userId);
        if ($user) {
            $user->update(['background_status' => false]);
        }
    }
}