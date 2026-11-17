<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

class RunNightlyTasks extends Command
{
    protected $signature = 'app:nightly';

    protected $description = 'Runs all nightly tasks: subscription processing and, in demo mode, the demo reset';

    public function handle(): int
    {
        $tasks = ['subscriptions:process'];

        if (config('app.demo_mode')) {
            $tasks[] = 'demo:reset';
        }

        $failed = false;

        // One failing task must not stop the others
        foreach ($tasks as $task) {
            $this->info("Running {$task}...");

            try {
                if ($this->call($task) !== self::SUCCESS) {
                    $failed = true;
                }
            } catch (\Throwable $e) {
                report($e);
                $this->error("{$task} failed: {$e->getMessage()}");
                $failed = true;
            }
        }

        return $failed ? self::FAILURE : self::SUCCESS;
    }
}