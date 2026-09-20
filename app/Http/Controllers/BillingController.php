<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Inertia\Inertia;

class BillingController extends Controller
{
    public function index(Request $request)
    {
        return Inertia::render('Billing/Index', [
            'subscribed' => $request->user()->subscribed('default'),
            'subscription' => $request->user()->subscription('default')?->asStripeSubscription(),
        ]);
    }

    public function checkout(Request $request)
    {
        $priceId = 'price_1UByD3GqvZUa1rtkwNKTUUCP'; 

        $checkout = $request->user()->newSubscription('default', $priceId)
            ->checkout([
                'success_url' => route('dashboard') . '?success=true',
                'cancel_url' => route('billing.index') . '?canceled=true',
            ]);
        
        return $checkout;
    }

    // Preusmeravanje korisnika na Stripe Billing Portal
    public function portal(Request $request)
    {
        return $request->user()->redirectToBillingPortal(
            route('billing.index')
        );
    }
}
