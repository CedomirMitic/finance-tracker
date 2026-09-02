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
        $currentMonth = Carbon::now()->month;
        $currentYear = Carbon::now()->year;
        
        // 1. Find all "recurring" templates scheduled for billing today or handle edge cases 
        // (e.g. if today is the 31st and this month has 30 or 28 days, catch the last day of the month)
        $isLastDayOfMonth = Carbon::now()->isLastOfMonth();

        $subscriptions = Transaction::where('payment_type', 'recurring')
            ->where(function ($query) use ($today, $isLastDayOfMonth) {
                $query->where('billing_day', $today);
                
                if ($isLastDayOfMonth) {
                    // If today is the last day of a shorter month (e.g., Feb 28 or Apr 30),
                    // also process subscriptions whose billing day exceeds today's date (e.g., day 29, 30, 31).
                    $query->orWhere('billing_day', '>', $today);
                }
            })
            ->get();

        foreach ($subscriptions as $sub) {
            // 2. Prevent duplication: check if a transaction for this exact subscription template 
            // has already been generated for the current user, description, and month/year.
            // Using template's ID or unique matching criteria.
            $exists = Transaction::where('user_id', $sub->user_id)
                ->where('description', $sub->description)
                ->where('amount', $sub->amount)
                ->where('category', $sub->category)
                ->whereMonth('created_at', $currentMonth)
                ->whereYear('created_at', $currentYear)
                ->where('payment_type', 'manual')
                ->exists();

            if (!$exists) {
                // 3. Create a new "manual" transaction copy for the current month's billing
                Transaction::create([
                    'user_id'      => $sub->user_id,
                    'description'  => $sub->description,
                    'amount'       => $sub->amount,
                    'type'         => $sub->type,
                    'category'     => $sub->category,
                    'payment_type' => 'manual', 
                ]);

                $this->info("Subscription added: {$sub->description} for user ID: {$sub->user_id}");
            }
        }

        $this->info('All subscriptions for today have been processed.');
    }
}