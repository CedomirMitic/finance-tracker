<?php

namespace App\Http\Controllers;

use App\Models\Budget;
use App\Models\Transaction;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;
use Illuminate\Validation\Rule;

class StatisticsController extends Controller
{
    public function index(Request $request): Response
    {
        $user = $request->user();
        $request->validate([
            'year' => 'nullable|numeric|between:2000,2100',
            'month' => 'nullable|numeric|between:1,12',
        ]);
        $year = (int) $request->input('year', date('Y'));
        $month = (int) $request->input('month', date('m'));

        $budgets = $user->budgets()->pluck('amount', 'category');

        $transactions = $user->transactions()
            ->inMonth($year, $month)
            ->orderByRaw(Transaction::EFFECTIVE_DATE . ' desc')
            ->get();

        $byCategory = fn(string $type) => $transactions->where('type', $type)
            ->groupBy('category')
            ->map(fn($group, $category) => [
                'category' => $category,
                'total' => $group->sum('amount'),
            ])
            ->values();

        // Group expenses by category efficiently from the retrieved transactions collection
        $expenseStats = $byCategory('expense');
        $incomeStats = $byCategory('income');

        $totalIncome = $incomeStats->sum('total');
        $totalExpenses = $expenseStats->sum('total');

        $availableYears = $user->transactions()
            ->selectRaw('YEAR(' . Transaction::EFFECTIVE_DATE . ') as year')
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
            'income_stats' => $incomeStats,
            'expense_stats' => $expenseStats,
            'budgets' => $budgets,
            'totalIncome' => (float) $totalIncome,
            'totalExpenses' => (float) $totalExpenses,
            'availableYears' => $availableYears->values(),
            'months' => $months,
            'selectedYear' => $year,
            'selectedMonth' => sprintf('%02d', $month),
            'transactions' => $transactions,
        ]);
    }

    public function updateBudget(Request $request)
    {
        $validated = $request->validate([
            'category' => [
                'required',
                'string',
                Rule::exists('transactions', 'category')->where(fn($q) => $q
                    ->where('user_id', auth()->id())
                    ->where('type', 'expense')),
            ],
            'required|numeric|gt:0|max:99999999',
        ]);

        Budget::updateOrCreate(
            ['user_id' => auth()->id(), 'category' => $validated['category']],
            ['amount' => $validated['amount']]
        );

        return back()->with('message', 'Budget updated successfully!');
    }
}