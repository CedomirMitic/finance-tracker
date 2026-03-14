<?php

namespace App\Http\Controllers;

use App\Models\Transaction;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;
use Inertia\Inertia;

class TransactionController extends Controller
{
    public function index()
    {
        return Inertia::render('Transactions/Index', [
            'transactions' => Transaction::where('user_id', Auth::id())
                ->latest()
                ->get()
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'description'  => 'required|string|min:3|max:255',
            'amount'       => 'required|numeric|gt:0',
            'type'         => 'required|in:income,expense',
            'category'     => 'required|string',
            'payment_type' => 'required|in:manual,recurring',
            'billing_day'  => 'required_if:payment_type,recurring|nullable|integer|min:1|max:28',
        ]);

        $request->user()->transactions()->create($validated);

        return back();
    }

    public function destroy(Transaction $transaction)
    {
        if ($transaction->user_id !== auth()->id()) {
            abort(403);
        }

        $transaction->delete();

        return back();
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

    return back();
}
}