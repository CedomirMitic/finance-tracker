<script setup>
import { Head, Link } from '@inertiajs/vue3';
</script>

<!--
    TEMPLATE NOTE: this text describes the FinanceTracker demo site. It is not legal advice.
    Replace every  item. If you reuse this kit, rewrite it for your own business
    (prices, refund rules, governing law) and remove the "test mode" sentence in section 3.
-->

<template>

    <Head title="Terms of Service" />
    <div class="max-w-4xl mx-auto py-12 px-6 text-slate-700 leading-relaxed">
        <h1 class="text-3xl font-bold text-slate-900 mb-2">Terms of Service</h1>
        <p class="text-sm text-slate-500 mb-8">Last updated: November 2026</p>

        <p class="mb-4">
            Welcome to FinanceTracker. By creating an account or using the service you agree to these terms.
        </p>

        <h2 class="text-xl font-bold text-slate-900 mt-8 mb-2">1. The service</h2>
        <p class="mb-4">
            FinanceTracker helps you track income and expenses, set budgets, import bank statements and export
            reports. The service may change, and features may be added or removed.
        </p>

        <h2 class="text-xl font-bold text-slate-900 mt-8 mb-2">2. Accounts</h2>
        <p class="mb-4">
            You are responsible for your account, for keeping your password secret and for the information you enter.
            You must be able to enter into a binding contract to use the service.
        </p>

        <h2 class="text-xl font-bold text-slate-900 mt-8 mb-2">3. Plans and payment</h2>
        <p class="mb-2">
            There is a free plan with limits and a paid Pro plan. The current price is shown before you subscribe.
            Payments are handled by Stripe, and Pro renews every month until you cancel.
        </p>
        <p class="mb-2">
            You can cancel at any time in the billing portal. Pro stays active until the end of the period you have
            already paid for.
        </p>
        <p class="mb-4">
            On the live demo site Stripe runs in test mode, so no real money is charged.
        </p>

        <h2 class="text-xl font-bold text-slate-900 mt-8 mb-2">4. Your data</h2>
        <p class="mb-4">
            You own the data you enter. You can export your transactions as CSV and delete your account at any time.
            How we handle personal data is described in the <Link href="/privacy"
                class="text-emerald-600 hover:underline">Privacy Policy</Link>.
        </p>

        <h2 class="text-xl font-bold text-slate-900 mt-8 mb-2">5. Acceptable use</h2>
        <p class="mb-4">
            Do not misuse the service: no attempts to break or overload it, to access other users' data, or to use it
            for anything unlawful. We may limit or suspend accounts that do.
        </p>

        <h2 class="text-xl font-bold text-slate-900 mt-8 mb-2">6. No financial advice</h2>
        <p class="mb-4">
            FinanceTracker is a tracking tool, not financial, tax or legal advice. Exchange rates come from a third
            party and calculations may contain errors, so check important figures yourself.
        </p>

        <h2 class="text-xl font-bold text-slate-900 mt-8 mb-2">7. Availability and liability</h2>
        <p class="mb-4">
            The service is provided "as is" and may be unavailable from time to time. To the extent permitted by law,
            we are not liable for indirect or consequential losses, and our total liability is limited to the amount
            you paid for the service in the last 12 months. Liability that cannot be excluded by law remains unaffected.
        </p>

        <h2 class="text-xl font-bold text-slate-900 mt-8 mb-2">8. Termination</h2>
        <p class="mb-4">
            You can stop using the service and delete your account at any time. We may end or suspend accounts that
            break these terms.
        </p>

        <h2 class="text-xl font-bold text-slate-900 mt-8 mb-2">9. Governing law</h2>
        <p class="mb-4">
            These terms are governed by Austrian law, excluding its conflict-of-law rules and the UN Convention on
            Contracts for the International Sale of Goods. If you are a consumer in the EU/EEA, the mandatory consumer
            protection laws of your country of residence remain unaffected.
        </p>

        <h2 class="text-xl font-bold text-slate-900 mt-8 mb-2">10. Contact</h2>
        <p class="mb-8">Cedomir Mitic , Austria. E-mail: cedomir988@gmail.com</p>

        <Link href="/" class="text-emerald-600 hover:underline font-semibold">&larr; Back to home</Link>
    </div>
</template>
