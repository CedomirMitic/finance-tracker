<?php

namespace App\Console\Commands;

use Database\Seeders\DemoSeeder;
use Illuminate\Console\Command;

class ResetDemoData extends Command
{
    protected $signature = 'demo:reset';

    protected $description = 'Reset the demo account data (only when DEMO_MODE=true)';

    public function handle(): int
    {
        if (! config('app.demo_mode')) {
            $this->error('DEMO_MODE is not enabled. Nothing was changed.');

            return self::FAILURE;
        }

        $this->call('db:seed', [
            '--class' => DemoSeeder::class,
            '--force' => true,
        ]);

        return self::SUCCESS;
    }
}