<?php
namespace App\Http\Controllers;

use App\Http\Requests\ProfileUpdateRequest;
use App\Jobs\ProcessCurrencyConversion;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Validation\Rule;
use Inertia\Inertia;
use Inertia\Response;

class ProfileController extends Controller
{
    /**
     * Display the user's profile form.
     */
    public function edit(Request $request): Response
    {
        $currencies = [];

        try {
            $response = Http::timeout(3)->get("https://api.frankfurter.dev/v1/currencies");
            if ($response->successful()) {
                foreach ($response->json() as $code => $name) {
                    $currencies[] = [
                        'value' => $code,
                        'label' => "{$code} - {$name}"
                    ];
                }
            }
        } catch (\Exception $e) {
            \Log::error("Currency API error in profile edit: " . $e->getMessage());
        }

        usort($currencies, function ($a, $b) {
            return strcmp($a['value'], $b['value']);
        });

        return Inertia::render('Profile/Edit', [
            'mustVerifyEmail' => $request->user() instanceof MustVerifyEmail,
            'status' => session('status'),
            'currencies' => $currencies,
        ]);
    }

    public function updateCurrency(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'preferred_currency' => ['required', 'string', Rule::in(array_column($this->getSupportedCurrencies(), 'value'))],
        ]);

        $user = $request->user();
        $oldCurrency = $user->preferred_currency ?? 'EUR';
        $newCurrency = strtoupper($validated['preferred_currency']);

        if ($oldCurrency === $newCurrency) {
            return Redirect::route('profile.edit')->with('success', 'Currency updated successfully.');
        }

        // Update preffered currency on profile page
        $user->update(['preferred_currency' => $newCurrency]);

        // Dispatch job for queue
        ProcessCurrencyConversion::dispatch($user, $newCurrency);

        return Redirect::route('profile.edit')->with('success', 'Currency updated. Transactions are being converted in the background.');
    }

    private function getSupportedCurrencies(): array
    {
        $currencies = [];
        try {
            $response = Http::timeout(3)->get("https://api.frankfurter.dev/v1/currencies");
            if ($response->successful()) {
                foreach ($response->json() as $code => $name) {
                    $currencies[] = ['value' => $code, 'label' => "{$code} - {$name}"];
                }
            }
        } catch (\Exception $e) {
            // Fallback or empty
        }

        return $currencies;
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

        return Redirect::route('profile.edit')->with('success', 'Email Adress successfully changed!');
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

        Auth::logout();

        $user->delete();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return Redirect::to('/');
    }

}
