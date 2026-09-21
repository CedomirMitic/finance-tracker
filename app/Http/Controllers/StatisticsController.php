<?php

namespace App\Http\Controllers;

use App\Models\Budget;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class StatisticsController extends Controller
{
public function index(Request $request): Response
    {
        $user = $request->user();
        $year = $request->input('year', date('Y'));
        $month = $request->input('month', date('m'));
        
        $budgets = $user->budgets()->pluck('amount', 'category');

        $transactions = $user->transactions()
            ->whereYear('created_at', $year)
            ->whereMonth('created_at', $month)
            ->orderBy('created_at', 'desc')
            ->get();

        // Group expenses by category efficiently from the retrieved transactions collection
        $stats = $transactions->where('type', 'expense')
            ->groupBy('category')
            ->map(fn ($group, $category) => [
                'category' => $category,
                'total' => $group->sum('amount')
            ])
            ->values();

        $totalIncome = $transactions->where('type', 'income')->sum('amount');
        $totalExpenses = $stats->sum('total');

        $availableYears = $user->transactions()
            ->selectRaw("YEAR(created_at) as year")
            ->distinct()
            ->orderBy('year', 'desc')
            ->pluck('year')
            ->map(fn($y) => (int) $y);

        if ($availableYears->isEmpty()) {
            $availableYears = collect([(int) date('Y')]);
        }

        $months = [
            ['value' => '01', 'label' => 'January'],
            ['value' => '02', 'label' => 'February'],
            ['value' => '03', 'label' => 'March'],
            ['value' => '04', 'label' => 'April'],
            ['value' => '05', 'label' => 'May'],
            ['value' => '06', 'label' => 'June'],
            ['value' => '07', 'label' => 'July'],
            ['value' => '08', 'label' => 'August'],
            ['value' => '09', 'label' => 'September'],
            ['value' => '10', 'label' => 'October'],
            ['value' => '11', 'label' => 'November'],
            ['value' => '12', 'label' => 'December'],
        ];

        return Inertia::render('Statistics/Index', [
            'stats' => $stats,
            'budgets' => $budgets,
            'totalIncome' => (float) $totalIncome,
            'totalExpenses' => (float) $totalExpenses,
            'availableYears' => $availableYears->values(),
            'months' => $months,
            'selectedYear' => (int) $year,
            'selectedMonth' => (string) $month,
            'transactions' => $transactions,
        ]);
    }

    public function updateBudget(Request $request)
    {
        $validated = $request->validate([
            'category' => 'required|string',
            'amount' => 'required|numeric|min:0',
        ]);

        Budget::updateOrCreate(
            ['user_id' => auth()->id(), 'category' => $validated['category']],
            ['amount' => $validated['amount']]
        );

        return back()->with('message', 'Budget updated successfully!');
    }
}