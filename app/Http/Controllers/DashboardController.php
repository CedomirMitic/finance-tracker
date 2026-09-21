<?php 

namespace App\Http\Controllers;

use App\Models\Transaction;
use Inertia\Inertia;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    public function index()
{
    $user = auth()->user();
    $now = Carbon::now();

    $spentToday = Transaction::where('user_id', $user->id)
        ->whereDate('created_at', Carbon::today())
        ->where('type', 'expense')
        ->sum('amount');

    $monthlyIncome = Transaction::where('user_id', $user->id)
        ->whereMonth('created_at', $now->month)
        ->whereYear('created_at', $now->year)
        ->where('type', 'income')
        ->sum('amount');

    $monthlyExpenses = Transaction::where('user_id', $user->id)
        ->whereMonth('created_at', $now->month)
        ->whereYear('created_at', $now->year)
        ->where('type', 'expense')
        ->sum('amount');


    $recentTransactions = Transaction::where('user_id', $user->id)
        ->orderBy('created_at', 'desc')
        ->limit(5)
        ->get();


    $trend = Transaction::select(
            DB::raw("DATE_FORMAT(created_at, '%m') as month"),
            DB::raw("SUM(CASE WHEN type = 'income' THEN amount ELSE 0 END) as income"),
            DB::raw("SUM(CASE WHEN type = 'expense' THEN amount ELSE 0 END) as expense")
        )
        ->where('user_id', $user->id)
        ->where('created_at', '>=', Carbon::now()->subMonths(6))
        ->groupBy('month')
        ->orderBy('month', 'asc')
        ->get();

    return Inertia::render('Dashboard', [
        'stats' => [
            'spentToday' => (float)$spentToday,
            'monthlyIncome' => (float)$monthlyIncome,
            'monthlyExpenses' => (float)$monthlyExpenses,
            'savings' => (float)($monthlyIncome - $monthlyExpenses),
        ],
        'recentTransactions' => $recentTransactions,
        'trend' => $trend
    ]);
}
}