<script setup>
import { Head, Link } from '@inertiajs/vue3';
</script>

<!--
    TEMPLATE NOTE: this text describes the FinanceTracker demo site. It is not legal advice.
    Replace every item. If you reuse this kit, rewrite it so it matches
    your own company, providers and data flows.
-->

<template>

    <Head title="Privacy Policy" />
    <div class="max-w-4xl mx-auto py-12 px-6 text-slate-700 leading-relaxed">
        <h1 class="text-3xl font-bold text-slate-900 mb-2">Privacy Policy</h1>
        <p class="text-sm text-slate-500 mb-8">Last updated: November 2026</p>

        <p class="mb-4">
            This policy explains which personal data FinanceTracker processes, why, and what rights you have.
        </p>

        <h2 class="text-xl font-bold text-slate-900 mt-8 mb-2">1. Who is responsible</h2>
        <p class="mb-4">
            Cedomir Mitic, Austria.<br>
            Contact: cedomir988@gmail.com
        </p>

        <h2 class="text-xl font-bold text-slate-900 mt-8 mb-2">2. Data we process</h2>
        <ul class="list-disc pl-6 mb-4 space-y-1">
            <li><strong>Account data:</strong> name, e-mail address and your password (stored only as a hash).</li>
            <li><strong>Financial data you enter or import:</strong> transactions with amounts, descriptions,
                categories, dates and currencies, recurring items, budgets, and files you upload for import.</li>
            <li><strong>Subscription data:</strong> your plan and status, and the customer and subscription IDs from
                Stripe. We never see or store your card number.</li>
            <li><strong>Technical data:</strong> IP address, session information and error reports, so that the
                service works and can be kept secure.</li>
        </ul>

        <h2 class="text-xl font-bold text-slate-900 mt-8 mb-2">3. Why we process it</h2>
        <ul class="list-disc pl-6 mb-4 space-y-1">
            <li>To provide the service and your account (contract, Art. 6(1)(b) GDPR).</li>
            <li>To process payments and keep records required by law (Art. 6(1)(b) and (c) GDPR).</li>
            <li>To keep the service secure and fix errors (legitimate interest, Art. 6(1)(f) GDPR).</li>
        </ul>
        <p class="mb-4">We do not sell your data and we do not use it for advertising.</p>

        <h2 class="text-xl font-bold text-slate-900 mt-8 mb-2">4. Who receives your data</h2>
        <p class="mb-2">We use service providers that process data on our behalf:</p>
        <ul class="list-disc pl-6 mb-4 space-y-1">
            <li><strong>Stripe</strong> for payments and subscription management.</li>
            <li><strong>Resend</strong> for transactional e-mails (such as e-mail verification).</li>
            <li><strong>Sentry</strong> for error monitoring (technical information about errors).</li>
            <li><strong>Railway</strong> (application hosting) and <strong>Aiven</strong> (database hosting).</li>
            <li><strong>Frankfurter API</strong> for exchange rates. It only receives currency codes and dates, no
                personal data.</li>
        </ul>
        <p class="mb-4">
            Some providers are based outside the EU/EEA. Where that is the case, transfers rely on safeguards such as
            an EU adequacy decision or Standard Contractual Clauses.
        </p>

        <h2 class="text-xl font-bold text-slate-900 mt-8 mb-2">5. How long we keep data</h2>
        <p class="mb-4">
            We keep your data while your account exists. If you delete your account in the profile settings, we delete
            your account, transactions and budgets and cancel your subscription. Payment records may be kept by Stripe
            as required by law, and backups or logs may keep data for a limited time.
        </p>

        <h2 class="text-xl font-bold text-slate-900 mt-8 mb-2">6. Cookies</h2>
        <p class="mb-4">
            We use only technically necessary cookies: a session cookie to keep you logged in and a security token
            cookie (CSRF). We do not use advertising or tracking cookies.
        </p>

        <h2 class="text-xl font-bold text-slate-900 mt-8 mb-2">7. Your rights</h2>
        <p class="mb-4">
            You have the right to access, correct and delete your data, to restrict or object to processing, and to
            receive your data in a portable format (you can export your transactions as CSV). Contact us at the address
            above to use these rights. You can also complain to the Austrian Data Protection Authority
            (Datenschutzbehörde).
        </p>

        <h2 class="text-xl font-bold text-slate-900 mt-8 mb-2">8. Demo account</h2>
        <p class="mb-4">
            The shared demo account is reset every night and is visible to everyone who uses it. Do not enter real
            personal or financial data there.
        </p>

        <h2 class="text-xl font-bold text-slate-900 mt-8 mb-2">9. Changes</h2>
        <p class="mb-8">We may update this policy. The date at the top shows the latest version.</p>

        <Link href="/" class="text-emerald-600 hover:underline font-semibold">&larr; Back to home</Link>
    </div>
</template>
