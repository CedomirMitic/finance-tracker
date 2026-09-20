<script setup lang="ts">
import { Head, Link, usePage } from '@inertiajs/vue3';
import type { PageProps } from '@inertiajs/core';
import { computed } from 'vue';

defineProps({
    canLogin: Boolean,
    canRegister: Boolean,
});

interface CustomPageProps extends PageProps {
    auth: {
        user: any;
    };
}

const page = usePage<CustomPageProps>();
const user = computed(() => page.props.auth.user);
</script>

<template>
    <Head>
        <title>FinanceTracker - Master your money and track expenses</title>
        <meta name="description"
            content="The simple, powerful way to track expenses, set budgets, and generate professional reports for your personal finances." />
        <meta property="og:title" content="FinanceTracker - Master your money" />
        <meta property="og:description"
            content="Track expenses, set budgets, and generate professional reports easily." />
        <meta property="og:type" content="website" />
    </Head>

    <div class="min-h-screen bg-gray-50 flex flex-col relative overflow-hidden">
        <div class="absolute top-0 left-0 w-full h-full overflow-hidden z-0 pointer-events-none">
            <div class="absolute -top-[10%] -left-[10%] w-[40%] h-[40%] bg-indigo-100 rounded-full blur-3xl opacity-50">
            </div>
            <div
                class="absolute -bottom-[10%] -right-[10%] w-[40%] h-[40%] bg-green-100 rounded-full blur-3xl opacity-50">
            </div>
        </div>

        <main class="relative z-10 text-center px-6 max-w-5xl mx-auto py-20 flex-grow">
            <div
                class="mb-8 inline-flex items-center justify-center p-4 bg-white rounded-3xl shadow-xl shadow-indigo-100 border border-indigo-50">
                <svg class="w-10 h-10 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z">
                    </path>
                </svg>
            </div>

            <h1 class="text-6xl md:text-8xl font-black text-gray-900 tracking-tighter mb-6 leading-[0.9]">
                Master your <span class="text-indigo-600">money.</span>
            </h1>

            <p class="text-lg md:text-xl text-gray-500 max-w-xl mx-auto mb-12 leading-relaxed">
                The simple, powerful way to track expenses, set budgets, and generate professional reports.
            </p>

            <div class="flex flex-col sm:flex-row gap-4 justify-center items-center mb-24">
                <template v-if="!user">
                    <Link href="/register"
                        class="w-full sm:w-auto px-10 py-5 bg-indigo-600 text-white font-black rounded-2xl shadow-2xl shadow-indigo-200 hover:bg-indigo-700 hover:-translate-y-1 transition-all text-xl block text-center">
                        Get Started Free
                    </Link>
                    <Link href="/login"
                        class="w-full sm:w-auto px-10 py-5 bg-white text-gray-700 font-bold rounded-2xl border border-gray-200 hover:bg-gray-50 hover:-translate-y-1 transition-all text-xl block text-center">
                        Log In
                    </Link>
                </template>

                <template v-else>
                    <Link href="/dashboard"
                        class="w-full sm:w-auto px-12 py-6 bg-indigo-600 text-white font-black rounded-2xl shadow-2xl shadow-indigo-200 hover:bg-indigo-700 hover:-translate-y-1 transition-all text-2xl block text-center">
                        Continue to Dashboard →
                    </Link>
                </template>
            </div>

            <div class="mt-6 p-4 bg-gray-100 border-l-4 border-blue-500 rounded shadow-sm max-w-sm mx-auto mb-16">
                <p class="text-sm text-gray-700 font-bold mb-1">Demo Access:</p>
                <div class="text-sm text-gray-600">
                    <p><strong>Email:</strong> test@example.com</p>
                    <p><strong>Password:</strong> test123123</p>
                </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-3 gap-8 text-left border-t border-gray-100 pt-16">
                <div class="p-6 bg-white rounded-3xl shadow-lg shadow-gray-100/50 border border-gray-100 flex flex-col">
                    <div
                        class="w-full h-32 bg-indigo-50 rounded-2xl flex items-center justify-center mb-6 overflow-hidden relative">
                        <div class="absolute bottom-2 left-2 w-full h-full flex gap-1 items-end px-3 pb-3">
                            <div class="w-1/4 h-[60%] bg-indigo-200 rounded-t-lg"></div>
                            <div class="w-1/4 h-[90%] bg-indigo-400 rounded-t-lg"></div>
                            <div class="w-1/4 h-[40%] bg-indigo-300 rounded-t-lg"></div>
                            <div class="w-1/4 h-[75%] bg-indigo-500 rounded-t-lg"></div>
                        </div>
                    </div>
                    <div class="text-indigo-600 font-black mb-1 uppercase text-xs tracking-widest">Analytics</div>
                    <h4 class="font-bold text-lg text-gray-900 mb-1">Interactive Charts</h4>
                    <p class="text-sm text-gray-500 flex-grow">Analyze spending habits with bar and donut charts.</p>
                </div>

                <div class="p-6 bg-white rounded-3xl shadow-lg shadow-gray-100/50 border border-gray-100 flex flex-col">
                    <div
                        class="w-full h-32 bg-green-50 rounded-2xl flex items-center justify-center mb-6 overflow-hidden relative p-5">
                        <div class="w-full space-y-3 mt-4">
                            <div class="h-2 bg-gray-100 rounded-full w-full relative">
                                <div class="absolute top-0 left-0 h-full w-[70%] bg-green-400 rounded-full"></div>
                            </div>
                            <div class="h-2 bg-gray-100 rounded-full w-full relative">
                                <div class="absolute top-0 left-0 h-full w-[90%] bg-green-500 rounded-full"></div>
                            </div>
                            <div class="h-2 bg-gray-100 rounded-full w-full relative">
                                <div class="absolute top-0 left-0 h-full w-[45%] bg-green-300 rounded-full"></div>
                            </div>
                        </div>
                    </div>
                    <div class="text-green-600 font-black mb-1 uppercase text-xs tracking-widest">Budgets</div>
                    <h4 class="font-bold text-lg text-gray-900 mb-1">Smart Budgets</h4>
                    <p class="text-sm text-gray-500 flex-grow">Set category targets and get alerted when you overspend.</p>
                </div>

                <div class="p-6 bg-white rounded-3xl shadow-lg shadow-gray-100/50 border border-gray-100 flex flex-col">
                    <div
                        class="w-full h-32 bg-red-50 rounded-2xl flex items-center justify-center mb-6 overflow-hidden relative">
                        <svg class="w-16 h-16 text-red-200" fill="currentColor" viewBox="0 0 20 20">
                            <path d="M4 18h12V6H9L4 11v7zM4 11l5-5M9 6v5H4"></path>
                        </svg>
                        <div class="absolute -bottom-1 right-2 p-3 bg-red-500 rounded-xl text-white font-black text-xs">
                            PDF</div>
                    </div>
                    <div class="text-red-600 font-black mb-1 uppercase text-xs tracking-widest">Reports</div>
                    <h4 class="font-bold text-lg text-gray-900 mb-1">Professional Exports</h4>
                    <p class="text-sm text-gray-500 flex-grow">Generate PDF and CSV statements for records.</p>
                </div>
            </div>
        </main>

        <!-- PRICING SECTION -->
        <section class="max-w-5xl mx-auto px-6 py-20 border-t border-gray-100 text-left w-full relative z-10">
            <div class="text-center max-w-2xl mx-auto mb-16">
                <h2 class="text-xs font-black text-indigo-600 uppercase tracking-widest mb-3">Pricing</h2>
                <h3 class="text-4xl font-black text-gray-900 tracking-tight">Simple, transparent pricing</h3>
                <p class="text-gray-500 mt-2">Choose the plan that fits your financial goals.</p>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-8 max-w-4xl mx-auto">
                <!-- Free Plan -->
                <div class="p-8 bg-white rounded-3xl shadow-lg shadow-gray-100/50 border border-gray-100 flex flex-col justify-between">
                    <div>
                        <div class="text-gray-900 font-black text-xl mb-1">Free</div>
                        <p class="text-sm text-gray-500 mb-6">Basic expense tracking for starters.</p>
                        <div class="text-4xl font-black text-gray-900 mb-6">$0 <span class="text-sm font-normal text-gray-500">/ forever</span></div>
                        <ul class="space-y-3 text-sm text-gray-600 mb-8">
                            <li class="flex items-center gap-2">✓ Up to 15 transactions / month</li>
                            <li class="flex items-center gap-2">✓ CSV export available</li>
                            <li class="text-gray-300 flex items-center gap-2">✕ Budgets (Pro only)</li>
                            <li class="text-gray-300 flex items-center gap-2">✕ PDF reports (Pro only)</li>
                        </ul>
                    </div>
                    <Link href="/register" class="w-full py-4 bg-gray-100 text-gray-800 font-bold rounded-2xl hover:bg-gray-200 transition-all text-center block">
                        Get Started Free
                    </Link>
                </div>

                <!-- Pro Plan -->
                <div class="p-8 bg-indigo-900 text-white rounded-3xl shadow-2xl shadow-indigo-200 border border-indigo-800 flex flex-col justify-between relative overflow-hidden">
                    <div class="absolute top-0 right-0 bg-indigo-500 text-white text-xs font-black px-4 py-1.5 rounded-bl-2xl uppercase tracking-wider">Popular</div>
                    <div>
                        <div class="font-black text-xl mb-1 text-white">Pro</div>
                        <p class="text-sm text-indigo-200 mb-6">No limits and full control over your finances.</p>
                        <div class="text-4xl font-black mb-6">$9 <span class="text-sm font-normal text-indigo-300">/ month</span></div>
                        <ul class="space-y-3 text-sm text-indigo-100 mb-8">
                            <li class="flex items-center gap-2">✓ Unlimited transactions</li>
                            <li class="flex items-center gap-2">✓ Advanced Budgets</li>
                            <li class="flex items-center gap-2">✓ Professional PDF reports export</li>
                            <li class="flex items-center gap-2">✓ CSV export available</li>
                        </ul>
                    </div>
                    <Link href="/register" class="w-full py-4 bg-white text-indigo-900 font-black rounded-2xl hover:bg-indigo-50 transition-all text-center block shadow-lg">
                        Upgrade to Pro
                    </Link>
                </div>
            </div>
        </section>

        <footer class="w-full py-8 text-center text-gray-400 text-sm flex justify-center gap-6 border-t border-gray-100 relative z-10 bg-white">
            <span>&copy; 2026 FinanceTracker.</span>
            <Link href="/terms" class="hover:underline">Terms of Service</Link>
            <Link href="/privacy" class="hover:underline">Privacy Policy</Link>
        </footer>
    </div>
</template>