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

        $query = $user->transactions();

        // Paginate 10 transactions per page
        $transactions = $query->latest('created_at')->paginate(10);

        $totalBalance = $user->transactions()
            ->selectRaw("sum(case when type = 'income' then amount else -amount end) as total")
            ->value('total') ?? 0;

        $recurringIncome = $user->transactions()
            ->where('payment_type', 'recurring')
            ->where('type', 'income')
            ->sum('amount');

        $recurringExpenses = $user->transactions()
            ->where('payment_type', 'recurring')
            ->where('type', 'expense')
            ->sum('amount');

        $activeSubsCount = $user->transactions()
            ->where('payment_type', 'recurring')
            ->count();

        return Inertia::render('Transactions/Index', [
            'transactions' => $transactions,
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
            'amount' => 'required|numeric|gt:0',
            'type' => 'required|in:income,expense',
            'category' => 'required|string',
            'payment_type' => 'required|in:manual,recurring',
            'billing_day' => 'required_if:payment_type,recurring|nullable|integer|min:1|max:28',
        ]);

        //Static writing of original amount and currency to save them for UI rendering
        $user = $request->user();
        $validated['original_amount'] = $validated['amount'];
        $validated['original_currency'] = $user->preferred_currency ?? 'EUR';
        $validated['currency'] = $user->preferred_currency ?? 'EUR';

        $request->user()->transactions()->create($validated);

        return redirect()->back()->with('success', 'Transaction was added succesfully!');
    }


    public function update(Request $request, Transaction $transaction)
    {
        if ($transaction->user_id !== auth()->id()) {
            abort(403);
        }

        $validated = $request->validate([
            'description' => 'required|string|min:3|max:255',
            'amount' => 'required|numeric|gt:0',
            'type' => 'required|in:income,expense',
            'category' => 'required|string',
            'payment_type' => 'required|in:manual,recurring',
            'billing_day' => 'required_if:payment_type,recurring|nullable|integer|min:1|max:28',
        ]);

        //Static writing of original amount and currency to save them for UI rendering
        $user = $request->user();
        $validated['original_amount'] = $validated['amount'];
        $validated['original_currency'] = $user->preferred_currency ?? 'EUR';
        $validated['currency'] = $user->preferred_currency ?? 'EUR';

        $transaction->update($validated);

        return back()->with('success', 'Transaction was succesfully updated!');
    }
    public function destroy(Transaction $transaction)
    {
        if ($transaction->user_id !== auth()->id()) {
            abort(403);
        }

        $transaction->delete();

        return redirect()->back()->with('success', 'Transaction was deleted succesfully!');
    }

    public function cancelSubscription(Transaction $transaction)
    {

        if ($transaction->user_id !== auth()->id()) {
            abort(403);
        }

        $transaction->update([
            'payment_type' => 'manual',
            'billing_day' => null
        ]);

        return redirect()->back()->with('success', 'Subscription was successfully modified');
    }
}