<?php

namespace Database\Seeders;

use App\Models\User;
use Carbon\Carbon;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class DemoSeeder extends Seeder
{
    public function run(): void
    {
        // Demo data must never land in a real production database
        if (app()->isProduction() && ! config('app.demo_mode')) {
            $this->command?->warn('DemoSeeder skipped: set DEMO_MODE=true to allow it in production.');

            return;
        }

        $user = $this->demoUser();

        // Reset ONLY the demo user's data
        $user->transactions()->delete();
        $user->budgets()->delete();
        $this->clearSubscriptions($user);

        $this->seedTransactions($user);
        $this->seedBudgets($user);
        $this->seedProSubscription($user);

        $this->command?->info("Demo data created for {$user->email}");
    }

    private function demoUser(): User
    {
        $user = User::firstOrNew(['email' => config('app.demo_email', 'test@example.com')]);

        $user->forceFill([
            'name' => 'Demo User',
            'password' => config('app.demo_password', 'test123123'), // the 'hashed' cast hashes it
            'preferred_currency' => 'EUR',
            'background_status' => false,
            'email_verified_at' => now(),
            'stripe_id' => null,
        ])->save();

        return $user;
    }

    private function clearSubscriptions(User $user): void
    {
        $ids = DB::table('subscriptions')->where('user_id', $user->id)->pluck('id');

        DB::table('subscription_items')->whereIn('subscription_id', $ids)->delete();
        DB::table('subscriptions')->where('user_id', $user->id)->delete();
    }

    private function seedTransactions(User $user): void
    {
        mt_srand(2026); // same "random" data on every reset

        $currentMonth = now()->startOfMonth();
        $templatesCreatedAt = $currentMonth->copy()->subMonths(5);
        $rows = [];

        $add = function (
            string $description,
            string $category,
            string $type,
            float $amount,
            Carbon $when,
            string $paymentType = 'manual',
            ?int $billingDay = null
        ) use (&$rows, $user): void {
            $timestamp = $when->toDateTimeString();

            $rows[] = [
                'user_id' => $user->id,
                'description' => $description,
                'amount' => $amount,
                'type' => $type,
                'category' => $category,
                'currency' => 'EUR',
                'original_amount' => $amount,
                'original_currency' => 'EUR',
                'payment_type' => $paymentType,
                'billing_day' => $billingDay,
                'imported_transaction_date' => null,
                'created_at' => $timestamp,
                'updated_at' => $timestamp,
            ];
        };

        // Recurring templates: description, category, type, amount, billing day
        $recurring = [
            ['Rent', 'Bills', 'expense', 650.00, 1],
            ['Netflix', 'Entertainment', 'expense', 12.99, 5],
            ['Gym membership', 'Other', 'expense', 29.90, 12],
        ];

        foreach ($recurring as [$description, $category, $type, $amount, $day]) {
            $add($description, $category, $type, $amount, $templatesCreatedAt, 'recurring', $day);
        }

        // Variable expenses: count, min, max, descriptions
        $variable = [
            'Food' => [[6, 9], 5, 65, ['Supermarket', 'Bakery', 'Lunch', 'Groceries', 'Pizza night']],
            'Transport' => [[3, 5], 2, 45, ['Metro ticket', 'Taxi', 'Fuel', 'Bike repair']],
            'Entertainment' => [[1, 3], 12, 70, ['Cinema', 'Concert ticket', 'Restaurant']],
            'Other' => [[1, 2], 8, 60, ['Pharmacy', 'Gift', 'Haircut']],
        ];

        // Six months of history, oldest first
        for ($offset = 5; $offset >= 0; $offset--) {
            $monthStart = $currentMonth->copy()->subMonths($offset);
            $lastDay = $offset === 0 ? now()->day : $monthStart->daysInMonth;
            $at = fn (int $day): Carbon => $this->moment($monthStart, $day);

            $add('Monthly salary', 'Salary', 'income', 3245.00, $at(1));

            // Copies of recurring items, as if the cron job had already created them
            foreach ($recurring as [$description, $category, $type, $amount, $day]) {
                if ($day <= $lastDay) {
                    $add($description, $category, $type, $amount, $at($day));
                }
            }

            foreach ($variable as $category => [$countRange, $min, $max, $descriptions]) {
                $count = mt_rand($countRange[0], $countRange[1]);

                for ($i = 0; $i < $count; $i++) {
                    $add(
                        $descriptions[mt_rand(0, count($descriptions) - 1)],
                        $category,
                        'expense',
                        round(mt_rand($min * 100, $max * 100) / 100, 2),
                        $at(mt_rand(1, $lastDay))
                    );
                }
            }

            if ($offset % 2 === 0) {
                $add(
                    'Freelance project',
                    'Other',
                    'income',
                    round(mt_rand(45000, 90000) / 100, 2),
                    $at(min(mt_rand(10, 20), $lastDay))
                );
            }
        }

        // Guarantees a value for "Spent today" on the dashboard
        $add('Morning coffee', 'Food', 'expense', 4.80, now());

        foreach (array_chunk($rows, 100) as $chunk) {
            DB::table('transactions')->insert($chunk);
        }

        mt_srand(); // restore real randomness
    }

    private function moment(Carbon $monthStart, int $day): Carbon
    {
          $when = $monthStart->copy()->addDays($day - 1)->setTime(mt_rand(8, 20), mt_rand(0, 59));

        // Never create a timestamp in the future
        return $when->gt(now()) ? now() : $when;
    }

    private function seedBudgets(User $user): void
    {
        foreach (['Food' => 450, 'Transport' => 120, 'Entertainment' => 50] as $category => $amount) {
            $user->budgets()->create(['category' => $category, 'amount' => $amount]);
        }
    }

    /**
     * Local "active" subscription so the demo user can try Pro features.
     * subscribed('default') only reads these rows, it never calls Stripe.
     */
    private function seedProSubscription(User $user): void
    {
        $price = config('services.stripe.price_id') ?: 'price_demo';
        $now = now()->toDateTimeString();

        $subscriptionId = DB::table('subscriptions')->insertGetId([
            'user_id' => $user->id,
            'type' => 'default',
            'stripe_id' => 'sub_demo_local',
            'stripe_status' => 'active',
            'stripe_price' => $price,
            'quantity' => 1,
            'trial_ends_at' => null,
            'ends_at' => null,
            'created_at' => $now,
            'updated_at' => $now,
        ]);

        DB::table('subscription_items')->insert([
            'subscription_id' => $subscriptionId,
            'stripe_id' => 'si_demo_local',
            'stripe_product' => 'prod_demo',
            'stripe_price' => $price,
            'quantity' => 1,
            'created_at' => $now,
            'updated_at' => $now,
        ]);
    }
}