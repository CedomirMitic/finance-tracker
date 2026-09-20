<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // This is the "Main Menu". It calls your specific seeder.
        $user = User::factory()->create([
            'name' => 'Test',
            'email' => 'test@example.com',
            'password' => bcrypt('test123123'),
            'preferred_currency' => 'EUR',
        ]);
        
        $this->call([
            TransactionSeeder::class,
        ]);
    }
}