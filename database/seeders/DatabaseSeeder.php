<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // This is the "Main Menu". It calls your specific seeder.
        $this->call([
            TransactionSeeder::class,
        ]);
    }
}