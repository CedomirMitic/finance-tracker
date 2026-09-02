<?php
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\ExportController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\StatisticsController;
use App\Http\Controllers\TransactionController;
use Illuminate\Foundation\Application;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Log;

require __DIR__ . '/auth.php';

Route::get('/', function () {
    return Inertia::render('Welcome', [
        'canLogin' => Route::has('login'),
        'canRegister' => Route::has('register'),
        'laravelVersion' => Application::VERSION,
        'phpVersion' => PHP_VERSION,
    ]);
});

Route::get('/dashboard', [DashboardController::class, 'index'])
    ->middleware(['auth', 'verified'])
    ->name('dashboard');

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});


Route::middleware(['auth', 'verified'])->group(function () {
    Route::get('/transactions', [TransactionController::class, 'index'])->name('transactions.index');
});
Route::post('/transactions', [TransactionController::class, 'store'])->name('transactions.store');
Route::delete('/transactions/{transaction}', [TransactionController::class, 'destroy'])->name('transactions.destroy');
Route::patch('/transactions/{transaction}/cancel', [TransactionController::class, 'cancelSubscription'])
    ->name('transactions.cancel');


Route::get('/statistics', [StatisticsController::class, 'index'])->name('statistics');
Route::post('/statistics/budget', [StatisticsController::class, 'updateBudget'])->name('statistics.budget.update');

Route::get('/export', [ExportController::class, 'index'])->name('export.index');
Route::get('/export/download', [ExportController::class, 'download'])->name('export.download');
Route::get('/export/pdf', [ExportController::class, 'downloadPdf'])->name('export.pdf');

// Automatic Subscription Checker

Route::get('/run-cron-secure-token-12345', function () {
    try {
        Artisan::call('subscriptions:process');
        Log::info('Subscriptions processed successfully via external cron.');
        return response()->json(['status' => 'success', 'message' => 'Subscriptions processed.']);
    } catch (\Exception $e) {
        Log::error('Cron failed: ' . $e->getMessage());
        return response()->json(['status' => 'error', 'message' => $e->getMessage()], 500);
    }
});