<?php

namespace App\Jobs;

use App\Exceptions\ExchangeRateUnavailableException;
use App\Models\Transaction;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Cache;

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

    public $tries = 1;

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
            $skipped = 0;
            $badDates = 0;
            $dateMapped = isset($this->mapping['date_col']);


            foreach ($this->rows as $row) {
                $rawAmount = $row[$this->mapping['amount_col']] ?? null;
                $description = trim((string) ($row[$this->mapping['description_col']] ?? '')) ?: 'Imported Transaction';


                $cleanAmount = $this->parseAmount($rawAmount);

                if ($cleanAmount === null || $cleanAmount == 0) {
                    $skipped++;
                    continue;
                }

                $sourceAmount = abs($cleanAmount);

                $transactionDate = $dateMapped && isset($row[$this->mapping['date_col']])
                    ? $this->parseDate($row[$this->mapping['date_col']])
                    : null;

                if ($transactionDate === null) {
                    if ($dateMapped) {
                        $badDates++;
                    }
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
                    'description' => Str::limit($description, 255, ''),
                    'category' => 'Other',
                    'payment_type' => 'manual',
                    'billing_day' => null,
                ];
            }


            if (empty($records) || $skipped > count($this->rows) / 2) {
                throw new \RuntimeException('Most rows do not contain a valid amount.');
            }

            if ($dateMapped && $badDates > count($records) / 2) {
                throw new \RuntimeException('Most rows do not contain a valid date.');
            }

            DB::transaction(function () use ($records) {
                foreach ($records as $record) {
                    Transaction::create($record);
                }
            });

            Log::info('Successfully imported ' . count($records) . " transactions for user ID: {$user->id}");
        } catch (\Exception $e) {
            Log::error('Error while trying to execute a job: ' . $e->getMessage() . ' on line ' . $e->getLine());
            $this->reportFailure($e);
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
                throw new ExchangeRateUnavailableException("No exchange rate {$this->importCurrency}->{$target} for {$date}");
            }

            $rateCache[$key] = $rate;
        }

        return $rateCache[$key];
    }
    private function reportFailure(\Throwable $e): void
    {
        $message = $e instanceof ExchangeRateUnavailableException
            ? 'Import failed because the exchange rate service is not available. Nothing was imported. Please try again later.'
            : 'Import failed. Nothing was imported. Please check your file and column mapping and try again.';

        Cache::put("background_error.{$this->userId}", $message, now()->addHour());
    }

    public function failed(\Throwable $exception)
    {
        $user = User::find($this->userId);
        if ($user) {
            $user->update(['background_status' => false]);
        }

        $this->reportFailure($exception);
    }



    private function parseAmount(mixed $raw): ?float
    {

        if (is_int($raw) || is_float($raw)) {
            return (float) $raw;
        }


        $value = trim(str_ireplace([$this->importCurrency, '€', '$', '£', '¥'], '', (string) $raw));


        if (!preg_match('/^\(?\s*([\-+]?)\s*(\d[\d\s.,\']*)\)?$/', $value, $m)) {
            return null;
        }

        $negative = $m[1] === '-' || str_starts_with($value, '(');
        $num = preg_replace('/[\s\']/', '', $m[2]);

        $lastComma = strrpos($num, ',');
        $lastDot = strrpos($num, '.');

        if ($lastComma !== false && $lastDot !== false) {

            $decimal = $lastComma > $lastDot ? ',' : '.';
            $thousands = $decimal === ',' ? '.' : ',';
            $num = str_replace($decimal, '.', str_replace($thousands, '', $num));
        } elseif ($lastComma !== false || $lastDot !== false) {
            $sep = $lastComma !== false ? ',' : '.';
            $parts = explode($sep, $num);
            $last = end($parts);

            if (count($parts) > 2 || strlen($last) === 3) {

                $validGroups = strlen($parts[0]) >= 1 && strlen($parts[0]) <= 3
                    && collect(array_slice($parts, 1))->every(fn($p) => strlen($p) === 3);

                if (!$validGroups) {
                    return null;
                }

                $num = implode('', $parts);
            } else {
                $num = $parts[0] . '.' . $last;
            }
        }

        if (!preg_match('/^\d+(\.\d{1,3})?$/', $num)) {
            return null;
        }

        return $negative ? -(float) $num : (float) $num;
    }


    private function parseDate(mixed $raw): ?string
    {
        if ($raw === null || $raw === '') {
            return null;
        }

        if (is_numeric($raw) && $raw > 20000 && $raw < 80000) {
            return \PhpOffice\PhpSpreadsheet\Shared\Date::excelToDateTimeObject($raw)->format('Y-m-d');
        }

        $timestamp = strtotime((string) $raw);

        return $timestamp ? date('Y-m-d', $timestamp) : null;
    }
}