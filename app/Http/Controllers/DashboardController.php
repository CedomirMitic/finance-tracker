<?php

namespace App\Http\Controllers;

use App\Models\Transaction;
use Inertia\Inertia;
use Carbon\Carbon;

class DashboardController extends Controller
{
    public function index()
    {
        $user = auth()->user();
        $now = Carbon::now();

        $spentToday = $user->transactions()->onDate(today())->where('type', 'expense')->sum('amount');

        $monthlyIncome = $user->transactions()
            ->inMonth($now->year, $now->month)->where('type', 'income')->sum('amount');

        $monthlyExpenses = $user->transactions()
            ->inMonth($now->year, $now->month)->where('type', 'expense')->sum('amount');

        $recentTransactions = $user->transactions()->latest()->limit(5)->get();
            
        $trend = $user->transactions()
            ->selectRaw("DATE_FORMAT(" . Transaction::EFFECTIVE_DATE . ", '%Y-%m') as month")
            ->selectRaw("SUM(CASE WHEN type = 'income' THEN amount ELSE 0 END) as income")
            ->selectRaw("SUM(CASE WHEN type = 'expense' THEN amount ELSE 0 END) as expense")
            ->betweenDates(today()->subMonths(5)->startOfMonth(), today())
            ->groupBy('month')
            ->orderBy('month')
            ->get();

        return Inertia::render('Dashboard', [
            'stats' => [
                'spentToday' => (float) $spentToday,
                'monthlyIncome' => (float) $monthlyIncome,
                'monthlyExpenses' => (float) $monthlyExpenses,
                'savings' => (float) ($monthlyIncome - $monthlyExpenses),
            ],
            'recentTransactions' => $recentTransactions,
            'trend' => $trend
        ]);
    }
}