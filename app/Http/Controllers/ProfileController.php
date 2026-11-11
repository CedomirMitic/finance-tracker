<?php

namespace App\Http\Controllers;

use App\Http\Requests\ProfileUpdateRequest;
use App\Jobs\ProcessCurrencyConversion;
use App\Services\CurrencyService;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Validation\Rule;
use Inertia\Inertia;
use Inertia\Response;
use Laravel\Cashier\Cashier;

class ProfileController extends Controller
{
    /**
     * Display the user's profile form.
     */
    public function edit(Request $request, CurrencyService $currencyService): Response
    {
        $currencies = $currencyService->getSupportedCurrencies();

        return Inertia::render('Profile/Edit', [
            'mustVerifyEmail' => $request->user() instanceof MustVerifyEmail,
            'status' => session('status'),
            'currencies' => $currencies,
        ]);
    }

    public function updateCurrency(Request $request, CurrencyService $currencyService): RedirectResponse
    {
        $validated = $request->validate([
            'preferred_currency' => ['required', 'string', Rule::in(array_column($currencyService->getSupportedCurrencies(), 'value'))],
        ]);

        $user = $request->user();
        $oldCurrency = $user->preferred_currency ?? 'EUR';
        $newCurrency = strtoupper($validated['preferred_currency']);

        if ($oldCurrency === $newCurrency) {
            return Redirect::route('profile.edit')->with('success', 'Currency updated successfully.');
        }

        $user->update(['preferred_currency' => $newCurrency]);

        // Dispatch background job for currency conversion
        ProcessCurrencyConversion::dispatch($user, $newCurrency);

        return Redirect::route('profile.edit')->with('success', 'Currency updated. Transactions are being converted in the background.');
    }

    /**
     * Update the user's profile information.
     */
    public function update(ProfileUpdateRequest $request): RedirectResponse
    {
        $request->user()->fill($request->validated());

        if ($request->user()->isDirty('email')) {
            $request->user()->email_verified_at = null;
        }

        $request->user()->save();

        return Redirect::route('profile.edit')->with('success', 'Email Address successfully changed!');
    }

    /**
     * Delete the user's account.
     */
    public function destroy(Request $request): RedirectResponse
    {
        $request->validate([
            'password' => ['required', 'current_password'],
        ]);

        $user = $request->user();

        try {
            if ($user->subscribed('default')) {
                $user->subscription('default')->cancelNow();
            }

            if ($user->hasStripeId()) {
                Cashier::stripe()->customers->delete($user->stripe_id);
            }
        } catch (\Throwable $e) {
            report($e);

            return back()->withErrors(['password' => 'Could not cancel your subscription. Please try again.']);
        }

        foreach ($user->subscriptions as $subscription) {
            $subscription->items()->delete();
            $subscription->delete();
        }


        Auth::logout();

        $user->delete();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return Redirect::to('/');
    }
}