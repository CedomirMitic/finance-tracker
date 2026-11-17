<?php

use Illuminate\Support\Facades\Schedule;
// Single entry point for all recurring jobs: subscriptions and, in demo mode, the demo reset.
// Traditional hosting: add ONE cron entry  * * * * * php artisan schedule:run
// Railway: run `php artisan app:nightly` from a cron service instead. Do not use both.
Schedule::command('app:nightly')->daily()->withoutOverlapping();