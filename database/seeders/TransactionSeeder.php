<?php

namespace Database\Seeders;

use App\Models\Transaction;
use App\Models\User;
use Illuminate\Database\Seeder;
use Carbon\Carbon;

class TransactionSeeder extends Seeder
{
    public function run(): void
    {
        // 1. Find your user (the one you registered manually)
        $user = User::first();

        if (!$user) {
            $this->command->error("No user found! Please register an account first.");
            return;
        }

        $this->command->info("Attaching transactions to: " . $user->email);

        // 2. Clear old transactions for this user so we start fresh
        Transaction::where('user_id', $user->id)->delete();

        $categories = [
            ['name' => 'Salary', 'type' => 'income', 'min' => 3000, 'max' => 5000],
            ['name' => 'Freelance', 'type' => 'income', 'min' => 400, 'max' => 1200],
            ['name' => 'Rent', 'type' => 'expense', 'min' => 900, 'max' => 1100],
            ['name' => 'Food', 'type' => 'expense', 'min' => 50, 'max' => 150],
            ['name' => 'Shopping', 'type' => 'expense', 'min' => 100, 'max' => 400],
            ['name' => 'Entertainment', 'type' => 'expense', 'min' => 50, 'max' => 200],
        ];

        // 3. Generate 4 months of history
        for ($i = 0; $i < 4; $i++) {
            $month = Carbon::now()->subMonths($i);

            foreach ($categories as $item) {
                // Determine the date: If it's the current month (i=0), 
                // we'll make one transaction "today" to fill your "Spent Today" stat.
                $isCurrentMonth = ($i === 0);

                Transaction::create([
                    'user_id' => $user->id,
                    'description' => "Monthly " . $item['name'],
                    'amount' => rand($item['min'], $item['max']),
                    'type' => $item['type'],
                    'category' => $item['name'],
                    'payment_type' => 'manual', 
                    'billing_day' => null,     
                    'created_at' => $isCurrentMonth ? Carbon::now() : $month->copy()->subDays(rand(1, 28)),
                ]);
            }
        }

        $this->command->info("Success! 4 months of data generated.");
    }
}