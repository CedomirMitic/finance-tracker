<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Inertia\Inertia;

class BillingController extends Controller
{
    public function index(Request $request)
    {

        $user = $request->user();
        $sub = $user->subscription('default');

        return Inertia::render('Billing/Index', [
            'subscribed' => $user->subscribed('default'),
            'subscription' => $sub ? [
                'status' => $sub->stripe_status,
                'on_grace_period' => $sub->onGracePeriod(),
                'ends_at' => $sub->ends_at,
            ] : null,
        ]);
    }

    public function checkout(Request $request)
    {
        if ($request->user()->subscribed('default'))
            return redirect()->route('billing.index');

        $priceId = config('services.stripe.price_id');

        if (blank($priceId)) {
            report(new \RuntimeException('STRIPE_PRICE_ID is not configured.'));

            return redirect()->route('billing.index')
                ->with('error', 'Billing is not configured yet. Please try again later.');
        }

        return $request->user()
            ->newSubscription('default', $priceId)
            ->checkout([
                'success_url' => route('dashboard') . '?success=true',
                'cancel_url' => route('billing.index') . '?canceled=true',
            ]);
    }

    public function portal(Request $request)
    {
        return $request->user()->redirectToBillingPortal(
            route('billing.index')
        );
    }
}