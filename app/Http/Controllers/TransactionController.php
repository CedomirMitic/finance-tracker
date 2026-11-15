<?php

namespace App\Http\Controllers;

use App\Models\Transaction;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Inertia\Inertia;

class TransactionController extends Controller
{
    public function index(Request $request)
    {
        $user = $request->user();
        $preferredCurrency = $user->preferred_currency ?? 'EUR';

        // Paginate 10 transactions per page
        $transactions = $user->manualTransactions()->latest('created_at')->paginate(10);

        $totalBalance = $user->manualTransactions()
            ->selectRaw("sum(case when type = 'income' then amount else -amount end) as total")
            ->value('total') ?? 0;

        $recurringTransactions = $user->recurringTransactions()->orderBy('billing_day')->get();

        $recurringIncome = $recurringTransactions->where('type', 'income')->sum('amount');
        $recurringExpenses = $recurringTransactions->where('type', 'expense')->sum('amount');
        $activeSubsCount = $recurringTransactions->count();

        return Inertia::render('Transactions/Index', [
            'transactions' => $transactions,
            'recurringTransactions' => $recurringTransactions,
            'totalBalance' => (float) $totalBalance,
            'recurringIncome' => (float) ($recurringIncome ?? 0),
            'recurringExpenses' => (float) ($recurringExpenses ?? 0),
            'activeSubsCount' => $activeSubsCount,
            'userCurrency' => $preferredCurrency,
        ]);
    }

    public function store(Request $request)
    {
        if (!Gate::allows('create-transaction')) {
            return redirect()->route('billing.index')
                ->with('error', 'You reached a limit of your 15 free package transactions. Please Upgrade to Pro to continue adding transactions.');
        }

        $validated = $request->validate([
            'description' => 'required|string|min:3|max:255',
            'amount' => 'required|numeric|gt:0|max:99999999',
            'type' => 'required|in:income,expense',
            'category' => 'required|string|max:255',
            'payment_type' => 'required|in:manual,recurring',
            'billing_day' => 'required_if:payment_type,recurring|nullable|integer|min:1|max:28',
        ]);

        $user = $request->user();
        $validated['original_amount'] = $validated['amount'];
        $validated['original_currency'] = $user->preferred_currency ?? 'EUR';
        $validated['currency'] = $user->preferred_currency ?? 'EUR';

        $user->transactions()->create($validated);

        $message = $validated['payment_type'] === 'recurring'
            ? 'Recurring transaction saved. It will be recorded automatically on the billing day.'
            : 'Transaction was added successfully!';

        return redirect()->back()->with('success', $message);
    }

    public function update(Request $request, Transaction $transaction)
    {
        if ($transaction->user_id !== auth()->id()) {
            abort(403);
        }

        $rules = [
            'description' => 'required|string|min:3|max:255',
            'amount' => 'required|numeric|gt:0|max:99999999',
            'type' => 'required|in:income,expense',
            'category' => 'required|string|max:255',
        ];

        if ($transaction->payment_type === 'recurring') {
            $rules['billing_day'] = 'required|integer|min:1|max:28';
        }

        $validated = $request->validate($rules);

        $user = $request->user();
        if ((float) $validated['amount'] !== (float) $transaction->amount) {
            $validated['original_amount'] = $validated['amount'];
            $validated['original_currency'] = $user->preferred_currency ?? 'EUR';
            $validated['currency'] = $user->preferred_currency ?? 'EUR';
        }

        $transaction->update($validated);

        return back()->with('success', 'Transaction was successfully updated!');
    }

    public function destroy(Transaction $transaction)
    {
        if ($transaction->user_id !== auth()->id()) {
            abort(403);
        }

        $transaction->delete();

        return redirect()->back()->with('success', 'Transaction was deleted successfully!');
    }

    public function cancelSubscription(Transaction $transaction)
    {
        if ($transaction->user_id !== auth()->id()) {
            abort(403);
        }

        abort_unless($transaction->payment_type === 'recurring', 422);

        $transaction->delete();

        return redirect()->back()->with('success', 'Recurring transaction was cancelled. Past charges stay in your history.');
    }
}