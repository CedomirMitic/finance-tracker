<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     *
     * Demo data is opt-in: local development, or DEMO_MODE=true.
     * A real production database stays untouched.
     */
    public function run(): void
    {
        if (app()->isProduction() && !config('app.demo_mode')) {
            return;
        }

        $this->call(DemoSeeder::class);
    }
}