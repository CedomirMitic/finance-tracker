<script setup lang="ts">
import { Head, Link, usePage } from '@inertiajs/vue3';
import { PageProps } from '@/types';
import { computed } from 'vue';

defineProps({
    canLogin: Boolean,
    canRegister: Boolean,
});
const page = usePage<PageProps>();
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

    <div class="min-h-screen bg-slate-50 flex flex-col relative overflow-hidden font-sans">
        <!-- Ambient Background Gradients -->
        <div class="absolute top-0 left-0 w-full h-full overflow-hidden z-0 pointer-events-none">
            <div
                class="absolute -top-[10%] -left-[10%] w-[45%] h-[45%] bg-emerald-100 rounded-full blur-3xl opacity-60">
            </div>
            <div class="absolute top-[30%] -right-[10%] w-[40%] h-[40%] bg-teal-100 rounded-full blur-3xl opacity-50">
            </div>
        </div>

        <main class="relative z-10 text-center px-6 max-w-6xl mx-auto pt-16 pb-20 flex-grow">
            <div class="mb-8 inline-flex items-center gap-3">
                <div
                    class="p-3 bg-white rounded-3xl shadow-xl shadow-emerald-50 border border-emerald-100/50 flex items-center justify-center">
                    <svg class="h-14 w-auto text-emerald-500" fill="none" stroke="currentColor" stroke-width="2.5"
                        viewBox="0 0 48 48" xmlns="http://www.w3.org/2000/svg">
                        <path class="text-emerald-700 opacity-80"
                            d="M24 4L6 10V22C6 33.04 13.68 43.12 24 46C34.32 43.12 42 33.04 42 22V10L24 4Z" />
                        <path class="text-emerald-500" stroke-linecap="round" stroke-linejoin="round"
                            d="M14 28L18 24M18 24L24 29M24 29L34 18M34 18L30 18M34 18V22" />
                        <path class="text-emerald-400" stroke-linecap="round" stroke-linejoin="round"
                            d="M23 15C21.34 15 20 16.34 20 18C20 19.66 21.34 21 23 21S26 22.34 26 24C26 25.66 24.66 27 23 27M23 15V13M23 27V29" />
                    </svg>
                </div>
                <div class="text-left">
                    <div class="text-3xl font-black text-slate-950 tracking-tighter leading-none">Finance</div>
                    <div class="text-lg font-bold text-emerald-600 tracking-tight leading-none mt-1">Tracker</div>
                </div>
            </div>

            <h1 class="text-5xl md:text-7xl font-black text-slate-900 tracking-tight mb-6 leading-[1.05]">
                Master your <span
                    class="text-transparent bg-clip-text bg-gradient-to-r from-emerald-600 to-teal-500">wealth &
                    expenses.</span>
            </h1>

            <p class="text-lg md:text-xl text-slate-600 max-w-2xl mx-auto mb-10 leading-relaxed">
                The modern full-stack boilerplate to track cashflow, import bank statements from CSV or Excel, and build
                custom
                financial dashboards.
            </p>

            <!-- CTA Buttons -->
            <div class="flex flex-col sm:flex-row gap-4 justify-center items-center mb-16">
                <template v-if="!user">
                    <Link href="/register"
                        class="w-full sm:w-auto px-8 py-4 bg-emerald-600 text-white font-extrabold rounded-2xl shadow-xl shadow-emerald-600/25 hover:bg-emerald-700 hover:-translate-y-0.5 transition-all text-lg block text-center">
                        Get Started Free
                    </Link>
                    <Link href="/login"
                        class="w-full sm:w-auto px-8 py-4 bg-white text-slate-700 font-bold rounded-2xl border border-slate-200 shadow-sm hover:bg-slate-50 hover:-translate-y-0.5 transition-all text-lg block text-center">
                        Log In
                    </Link>
                </template>

                <template v-else>
                    <Link href="/dashboard"
                        class="w-full sm:w-auto px-10 py-5 bg-emerald-600 text-white font-extrabold rounded-2xl shadow-xl shadow-emerald-600/25 hover:bg-emerald-700 hover:-translate-y-0.5 transition-all text-xl block text-center">
                        Continue to Dashboard →
                    </Link>
                </template>
            </div>

            <!-- Demo Banner -->
            <div v-if="page.props.demoMode"
                class="p-4 bg-white/80 backdrop-blur border border-emerald-100 rounded-2xl shadow-sm max-w-md mx-auto mb-16 flex items-center justify-between text-left">
                <div>
                    <span class="text-xs uppercase font-extrabold text-emerald-600 tracking-wider block">Instant Live
                        Demo</span>
                    <span class="text-xs text-slate-500">test@example.com / test123123</span>
                </div>
                <Link href="/demo-login"
                    class="px-3 py-1.5 bg-emerald-50 text-emerald-700 hover:bg-emerald-100 font-bold text-xs rounded-lg transition-colors">
                    Try Demo →
                </Link>
            </div>

            <!-- Visual Showcase: Financial Dashboard Mockup Widget -->
            <div
                class="max-w-4xl mx-auto mb-20 bg-slate-900 rounded-3xl p-6 shadow-2xl shadow-slate-900/20 border border-slate-800 text-left text-white relative overflow-hidden">
                <div
                    class="flex flex-col md:flex-row justify-between items-start md:items-center pb-6 border-b border-slate-800 gap-4">
                    <div>
                        <div class="text-xs font-medium text-slate-400">Total Net Worth</div>
                        <div class="text-3xl font-black text-white">$42,850.00 <span
                                class="text-xs text-emerald-400 font-bold">+12.4% this month</span></div>
                    </div>
                    <div class="flex gap-2">
                        <span
                            class="px-3 py-1 bg-amber-500/10 border border-amber-500/20 text-amber-400 rounded-xl text-xs font-semibold">Sample
                            preview</span>
                        <span class="px-3 py-1 bg-slate-800 text-slate-300 rounded-xl text-xs font-semibold">EUR /
                            USD</span>
                    </div>
                </div>

                <!-- Mini Cards & Charts preview -->
                <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mt-6">
                    <div class="bg-slate-800/60 p-4 rounded-2xl border border-slate-700/50">
                        <div class="text-xs text-slate-400">Income</div>
                        <div class="text-xl font-bold text-emerald-400 mt-1">+$6,400.00</div>
                        <div class="w-full h-1.5 bg-slate-700 rounded-full mt-3 overflow-hidden">
                            <div class="bg-emerald-400 h-full w-[80%] rounded-full"></div>
                        </div>
                    </div>
                    <div class="bg-slate-800/60 p-4 rounded-2xl border border-slate-700/50">
                        <div class="text-xs text-slate-400">Expenses</div>
                        <div class="text-xl font-bold text-rose-400 mt-1">-$2,150.00</div>
                        <div class="w-full h-1.5 bg-slate-700 rounded-full mt-3 overflow-hidden">
                            <div class="bg-rose-400 h-full w-[40%] rounded-full"></div>
                        </div>
                    </div>
                    <div class="bg-slate-800/60 p-4 rounded-2xl border border-slate-700/50">
                        <div class="text-xs text-slate-400">Savings Target</div>
                        <div class="text-xl font-bold text-teal-300 mt-1">$1,500 / $2,000</div>
                        <div class="w-full h-1.5 bg-slate-700 rounded-full mt-3 overflow-hidden">
                            <div class="bg-teal-400 h-full w-[75%] rounded-full"></div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Features Grid -->
            <div class="grid grid-cols-1 md:grid-cols-3 gap-8 text-left border-t border-slate-200/60 pt-16">
                <!-- Feature 1 -->
                <div
                    class="p-6 bg-white rounded-3xl shadow-lg shadow-slate-100 border border-slate-100 flex flex-col hover:border-emerald-200 transition-colors">
                    <div
                        class="w-full h-32 bg-emerald-50/70 rounded-2xl flex items-center justify-center mb-6 overflow-hidden relative">
                        <div class="absolute bottom-2 left-2 w-full h-full flex gap-2 items-end px-4 pb-3">
                            <div class="w-1/4 h-[40%] bg-emerald-200 rounded-t-lg"></div>
                            <div class="w-1/4 h-[85%] bg-emerald-500 rounded-t-lg"></div>
                            <div class="w-1/4 h-[60%] bg-emerald-300 rounded-t-lg"></div>
                            <div class="w-1/4 h-[100%] bg-teal-500 rounded-t-lg"></div>
                        </div>
                    </div>
                    <div class="text-emerald-600 font-black mb-1 uppercase text-xs tracking-widest">Analytics</div>
                    <h4 class="font-bold text-lg text-slate-900 mb-1">Cashflow Analytics</h4>
                    <p class="text-sm text-slate-500 flex-grow">Analyze income vs expenses with real-time bar charts and
                        breakdown
                        metrics.</p>
                </div>

                <!-- Feature 2 -->
                <div
                    class="p-6 bg-white rounded-3xl shadow-lg shadow-slate-100 border border-slate-100 flex flex-col hover:border-emerald-200 transition-colors">
                    <div
                        class="w-full h-32 bg-teal-50/70 rounded-2xl flex items-center justify-center mb-6 overflow-hidden relative p-5 flex flex-col justify-center">
                        <div class="w-full space-y-3">
                            <div class="h-2.5 bg-slate-100 rounded-full w-full relative overflow-hidden">
                                <div class="absolute top-0 left-0 h-full w-[70%] bg-emerald-500 rounded-full"></div>
                            </div>
                            <div class="h-2.5 bg-slate-100 rounded-full w-full relative overflow-hidden">
                                <div class="absolute top-0 left-0 h-full w-[90%] bg-teal-500 rounded-full"></div>
                            </div>
                            <div class="h-2.5 bg-slate-100 rounded-full w-full relative overflow-hidden">
                                <div class="absolute top-0 left-0 h-full w-[45%] bg-emerald-300 rounded-full"></div>
                            </div>
                        </div>
                    </div>
                    <div class="text-teal-600 font-black mb-1 uppercase text-xs tracking-widest">Budgets</div>
                    <h4 class="font-bold text-lg text-slate-900 mb-1">Category Caps</h4>
                    <p class="text-sm text-slate-500 flex-grow">Set strict limits for food, entertainment, and utilities
                        to prevent
                        overspending.</p>
                </div>

                <!-- Feature 3 -->
                <div
                    class="p-6 bg-white rounded-3xl shadow-lg shadow-slate-100 border border-slate-100 flex flex-col hover:border-emerald-200 transition-colors">
                    <div
                        class="w-full h-32 bg-slate-100/70 rounded-2xl flex items-center justify-center mb-6 overflow-hidden relative">
                        <div
                            class="p-3 bg-white rounded-xl shadow-md border border-slate-200 text-slate-800 font-extrabold text-xs flex items-center gap-2">
                            <span>📄 Statement.pdf</span>
                            <span
                                class="px-2 py-0.5 bg-emerald-100 text-emerald-700 rounded text-[10px]">EXPORTED</span>
                        </div>
                    </div>
                    <div class="text-teal-600 font-black mb-1 uppercase text-xs tracking-widest">Reports</div>
                    <h4 class="font-bold text-lg text-slate-900 mb-1">Instant Reports</h4>
                    <p class="text-sm text-slate-500 flex-grow">Generate structured PDF statements and CSV data exports
                        for tax
                        season.</p>
                </div>
            </div>
        </main>

        <!-- PRICING SECTION -->
        <section class="max-w-6xl mx-auto px-6 py-20 border-t border-slate-200/60 text-left w-full relative z-10">
            <div class="text-center max-w-2xl mx-auto mb-16">
                <h2 class="text-xs font-black text-emerald-600 uppercase tracking-widest mb-3">Pricing</h2>
                <h3 class="text-4xl font-black text-slate-900 tracking-tight">Simple, transparent pricing</h3>
                <p class="text-slate-500 mt-2">Choose the plan that fits your financial goals.</p>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-8 max-w-4xl mx-auto">
                <!-- Free Plan -->
                <div
                    class="p-8 bg-white rounded-3xl shadow-lg shadow-slate-100 border border-slate-200 flex flex-col justify-between">
                    <div>
                        <div class="text-slate-900 font-black text-xl mb-1">Free</div>
                        <p class="text-sm text-slate-500 mb-6">Basic expense tracking for starters.</p>
                        <div class="text-4xl font-black text-slate-900 mb-6">$0 <span
                                class="text-sm font-normal text-slate-500">/ forever</span></div>
                        <ul class="space-y-3 text-sm text-slate-600 mb-8">
                            <li class="flex items-center gap-2">✓ Up to 15 transactions / month</li>
                            <li class="flex items-center gap-2">✓ CSV export available</li>
                            <li class="text-slate-400 flex items-center gap-2">✕ Budgets (Pro only)</li>
                            <li class="text-slate-400 flex items-center gap-2">✕ Bank statement import (Pro only)</li>
                            <li class="text-slate-400 flex items-center gap-2">✕ PDF reports (Pro only)</li>
                        </ul>
                    </div>
                    <Link href="/register"
                        class="w-full py-4 bg-slate-100 text-slate-800 font-bold rounded-2xl hover:bg-slate-200 transition-all text-center block">
                        Get Started Free
                    </Link>
                </div>

                <!-- Pro Plan -->
                <div
                    class="p-8 bg-slate-900 text-white rounded-3xl shadow-2xl shadow-slate-900/20 border border-slate-800 flex flex-col justify-between relative overflow-hidden">
                    <div
                        class="absolute top-0 right-0 bg-emerald-500 text-slate-950 text-xs font-black px-4 py-1.5 rounded-bl-2xl uppercase tracking-wider">
                        Popular</div>
                    <div>
                        <div class="font-black text-xl mb-1 text-white">Pro</div>
                        <p class="text-sm text-slate-400 mb-6">No limits and full control over your finances.</p>
                        <div class="text-4xl font-black mb-6 text-white">$7.99 <span
                                class="text-sm font-normal text-slate-400">/ month</span></div>
                        <ul class="space-y-3 text-sm text-slate-300 mb-8">
                            <li class="flex items-center gap-2">✓ Unlimited transactions</li>
                            <li class="flex items-center gap-2">✓ Advanced Budgets & Category caps</li>
                            <li class="flex items-center gap-2">✓ Professional PDF reports export</li>
                            <li class="flex items-center gap-2">✓ CSV & Excel bank statement import</li>
                            <li class="flex items-center gap-2">✓ Full CSV export available</li>
                        </ul>
                    </div>
                    <Link :href="user ? route('billing.index') : '/register'"
                        class="w-full py-4 bg-emerald-500 text-slate-950 font-black rounded-2xl hover:bg-emerald-400 transition-all text-center block shadow-lg shadow-emerald-500/20">
                        {{ user?.subscribed ? 'Manage subscription' : 'Upgrade to Pro' }}
                    </Link>
                </div>
            </div>
        </section>

        <!-- Footer -->
        <footer
            class="w-full py-8 text-center text-slate-400 text-sm flex justify-center gap-6 border-t border-slate-200/60 relative z-10 bg-white">
            <span>&copy; 2026 FinanceTracker.</span>
            <Link href="/terms" class="hover:underline">Terms of Service</Link>
            <Link href="/privacy" class="hover:underline">Privacy Policy</Link>
        </footer>
    </div>
</template>