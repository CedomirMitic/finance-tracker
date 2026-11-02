<script setup>
import { router } from '@inertiajs/vue3';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { usePage } from '@inertiajs/vue3';

defineProps({
    subscribed: Boolean,
});

const page = usePage();
</script>

<template>
    <Head title="Subscription & Billing" />
    <AuthenticatedLayout>
        <template #header>
            <div class="font-sans">
                <h2 class="font-black text-xl sm:text-2xl text-slate-900 tracking-tight">Subscription & Billing</h2>
                <p class="text-xs text-slate-400 mt-0.5 font-medium">Manage your plan, billing details, and PRO features.</p>
            </div>
        </template>

        <div class="py-12 max-w-5xl mx-auto sm:px-6 lg:px-8 font-sans">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-3xl p-8 border border-slate-100">

                <div class="text-center max-w-2xl mx-auto mb-12">
                    <h1 class="text-3xl font-black text-slate-900 tracking-tight">Choose Your Plan</h1>
                    <p class="text-slate-500 mt-2 text-sm">Upgrade to Pro to remove transaction limits and unlock advanced PDF reports.</p>
                </div>

                <!-- Active Pro Banner -->
                <div v-if="subscribed"
                    class="bg-emerald-50 border border-emerald-200/80 p-6 rounded-2xl mb-8 flex flex-col sm:flex-row justify-between items-center gap-4">
                    <div class="flex items-center gap-4">
                        <div class="p-3 bg-emerald-600 text-white rounded-2xl shadow-md shadow-emerald-600/20">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5"
                                    d="M5 13l4 4L19 7" />
                            </svg>
                        </div>
                        <div>
                            <h3 class="text-lg font-black text-emerald-950">You are an active Pro subscriber!</h3>
                            <p class="text-sm text-emerald-700 mt-0.5">Manage your payment methods, billing history, or cancel anytime via the Stripe portal.</p>
                        </div>
                    </div>
                    <a :href="route('billing.portal')" target="_blank" rel="noopener noreferrer"
                        class="whitespace-nowrap bg-emerald-600 text-white px-6 py-3 rounded-2xl font-bold hover:bg-emerald-700 transition-all shadow-lg shadow-emerald-600/25 text-center inline-block">
                        Manage Subscription
                    </a>
                </div>

                <!-- Pricing Cards Grid -->
                <div v-else class="grid grid-cols-1 md:grid-cols-2 gap-8 items-stretch">

                    <!-- Free Package -->
                    <div class="border border-slate-200/80 rounded-3xl p-8 flex flex-col justify-between bg-slate-50/50">
                        <div>
                            <div class="flex justify-between items-center">
                                <h3 class="text-xl font-bold text-slate-900">Free Tier</h3>
                            </div>
                            <p class="text-slate-500 text-sm mt-2">Essential features for casual personal expense tracking.</p>
                            <p class="text-4xl font-black text-slate-900 mt-6">$0 <span class="text-sm font-normal text-slate-500">/ forever</span></p>

                            <ul class="mt-8 space-y-3.5 text-sm text-slate-600">
                                <li class="flex items-center gap-3">
                                    <span class="text-emerald-600 font-bold">✓</span> Up to 15 transactions limit
                                </li>
                                <li class="flex items-center gap-3">
                                    <span class="text-emerald-600 font-bold">✓</span> Recurring & manual payments
                                </li>
                                <li class="flex items-center gap-3">
                                    <span class="text-emerald-600 font-bold">✓</span> CSV Export reports
                                </li>
                                <li class="flex items-center gap-3 text-slate-300">
                                    <span>✕</span> Unlimited transactions
                                </li>
                            </ul>
                        </div>
                        <button disabled
                            class="mt-8 w-full bg-slate-200 text-slate-500 font-bold py-3.5 rounded-2xl cursor-not-allowed">
                            Current Plan
                        </button>
                    </div>

                    <!-- Pro Package -->
                    <div
                        class="border-2 border-emerald-600 rounded-3xl p-8 flex flex-col justify-between shadow-xl shadow-emerald-50 relative bg-white">
                        <div class="absolute -top-3.5 right-8">
                            <span class="bg-emerald-600 text-white text-xs font-black uppercase tracking-wider px-3.5 py-1 rounded-full shadow-sm">
                                Recommended
                            </span>
                        </div>
                        <div>
                            <h3 class="text-xl font-bold text-slate-900">Pro SaaS</h3>
                            <p class="text-slate-500 text-sm mt-2">Full power for users who want complete financial flexibility.</p>
                            <p class="text-4xl font-black text-emerald-600 mt-6">$7.99 <span class="text-sm font-normal text-slate-500">/ month</span></p>

                            <ul class="mt-8 space-y-3.5 text-sm text-slate-700">
                                <li class="flex items-center gap-3">
                                    <span class="text-emerald-600 font-bold">✓</span> <strong class="text-slate-900">Unlimited</strong> transactions
                                </li>
                                <li class="flex items-center gap-3">
                                    <span class="text-emerald-600 font-bold">✓</span> Recurring & manual payments
                                </li>
                                <li class="flex items-center gap-3">
                                    <span class="text-emerald-600 font-bold">✓</span> Advanced PDF & CSV reports
                                </li>
                                <li class="flex items-center gap-3">
                                    <span class="text-emerald-600 font-bold">✓</span> Category Budgets & tracking limits
                                </li>
                            </ul>
                        </div>

                        <form :action="route('billing.checkout')" method="GET" target="_blank" class="mt-8">
                            <input type="hidden" name="_token" :value="page.props.csrf_token">
                            <button type="submit"
                                class="w-full bg-emerald-600 text-white py-3.5 rounded-2xl font-bold hover:bg-emerald-700 transition-all shadow-lg shadow-emerald-600/25 text-center block">
                                Upgrade to Pro Now
                            </button>
                        </form>
                    </div>
                </div>

            </div>
        </div>
    </AuthenticatedLayout>
</template>