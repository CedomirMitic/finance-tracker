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

    public function handle(): void
    {
        $user = User::find($this->userId);
        $user->update(['background_status' => true]);

        try {
            if (!$user) {
                return;
            }


            $targetCurrency = strtoupper($user->preferred_currency ?? 'EUR');
            $rateCache = [];
            $count = 0;

            foreach ($this->rows as $row) {
                $rawAmount = $row[$this->mapping['amount_col']] ?? 0;
                $description = $row[$this->mapping['description_col']] ?? 'Imported Transaction';

                $rawDate = isset($this->mapping['date_col']) && isset($row[$this->mapping['date_col']]) ? $row[$this->mapping['date_col']] : null;
                $transactionDate = $rawDate ? date('Y-m-d', strtotime($rawDate)) : now()->toDateString();

                if (!$transactionDate || $transactionDate === '1970-01-01') {
                    $transactionDate = now()->toDateString();
                }

                if ($rawAmount === null || $rawAmount === '') {
                    continue;
                }

                // Universal amount parsing
                $cleanAmountStr = preg_replace('/[^\d.,-]/', '', str_replace([' ', 'RSD', 'EUR', 'USD', '$', '€'], '', $rawAmount));

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
                $amountInTarget = $sourceAmount;

                // Convert from import currency to user preffered one
                if ($this->importCurrency !== $targetCurrency) {
                    $cacheKey = "{$transactionDate}_{$this->importCurrency}_{$targetCurrency}";

                    if (!array_key_exists($cacheKey, $rateCache)) {
                        $historicalRate = null;
                        try {
                            $response = Http::timeout(5)->get("https://api.frankfurter.app/{$transactionDate}?from={$this->importCurrency}&to={$targetCurrency}");
                            if ($response->successful()) {
                                $historicalRate = $response->json("rates.{$targetCurrency}");
                            }
                        } catch (\Exception $e) {
                            Log::error("Historical currency API error for {$transactionDate}: " . $e->getMessage());
                        }
                        $rateCache[$cacheKey] = $historicalRate;
                    }

                    if ($rateCache[$cacheKey] && $rateCache[$cacheKey] > 0) {
                        $amountInTarget = $sourceAmount * $rateCache[$cacheKey];
                    }
                }

                Transaction::create([
                    'user_id' => $user->id,
                    'imported_transaction_date' => $transactionDate,
                    'amount' => round($amountInTarget, 2),    // Converted amount in preffered currency
                    'currency' => $targetCurrency,           // Users preffered currency
                    'original_amount' => $sourceAmount,       // Original amount from file
                    'original_currency' => $this->importCurrency, // Original currency from file
                    'type' => $cleanAmount < 0 ? 'expense' : 'income',
                    'description' => trim($description),
                    'category' => 'Other',
                    'payment_type' => 'manual',
                    'billing_day' => null,
                ]);

                $count++;
            }


            Log::info("Successfully imported {$count} transactions for user ID: {$user->id}");
        } catch (\Exception $e) {
            \Log::error("Error while trying to execute a job: " . $e->getMessage() . " on line " . $e->getLine());
            throw $e;
        } finally {
            $user->update(['background_status' => false]);
        }
    }

    // Safety net if job fails
    public function failed(\Throwable $exception)
    {
        $user = User::find($this->userId);
        if ($user) {
            $user->update(['background_status' => false]);
        }
    }
}