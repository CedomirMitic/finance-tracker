<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Transaction;
use Carbon\Carbon;

class ProcessSubscriptions extends Command
{
    /**
     * The name and signature of the console command.
     * Use this signature to run the command via terminal: php artisan subscriptions:process
     */
    protected $signature = 'subscriptions:process';

    /**
     * The console command description.
     */
    protected $description = 'Automatically generate transaction records for monthly subscriptions';

    public function handle()
    {
        $today = Carbon::now()->day;
        
        // 1. Find all "recurring" templates scheduled for billing today
        $subscriptions = Transaction::where('payment_type', 'recurring')
            ->where('billing_day', $today)
            ->get();

        foreach ($subscriptions as $sub) {
            // Check: Has this subscription already been processed for the current month?
            // We look for a manual transaction with the same description and user_id in the current month/year.
            $exists = Transaction::where('user_id', $sub->user_id)
                ->where('description', $sub->description)
                ->whereMonth('created_at', Carbon::now()->month)
                ->whereYear('created_at', Carbon::now()->year)
                ->where('payment_type', 'manual') // We only look at executed (manual) transactions
                ->exists();

            if (!$exists) {
                // 2. Create a new "manual" transaction based on the subscription template
                Transaction::create([
                    'user_id'      => $sub->user_id,
                    'description'  => $sub->description,
                    'amount'       => $sub->amount,
                    'type'         => $sub->type,
                    'category'     => $sub->category,
                    'payment_type' => 'manual', // This marks the transaction as "executed"
                ]);

                $this->info("Subscription added: {$sub->description} for user ID: {$sub->user_id}");
            }
        }

        $this->info('All subscriptions for today have been processed.');
    }
}