<script setup lang="ts">
declare function route(name?: string, params?: any): string;[]
import type { ApexOptions } from 'apexcharts';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link, usePage } from '@inertiajs/vue3';
import { formatCurrency } from '@/Utils/formatters';
import { computed } from 'vue';
import VueApexCharts from "vue3-apexcharts";
import { PageProps } from '@/types';

const props = defineProps<{
    stats: { spentToday: number, monthlyIncome: number, monthlyExpenses: number, savings: number },
    recentTransactions: Array<any>,
    trend: Array<{ month: string, income: number, expense: number }>
}>();

const page = usePage<PageProps>();
const userCurrency = computed(() => page.props.auth.user?.preferred_currency ?? 'EUR');

const chartOptions = computed<ApexOptions>(() => ({
    chart: {
        type: 'bar',
        toolbar: { show: false }
    },
    plotOptions: {
        bar: {
            horizontal: false,
            columnWidth: '55%',
            borderRadius: 5
        }
    },
    dataLabels: { enabled: false },
    colors: ['#10b981', '#ef4444'], // Emerald for income , red for expenses
    xaxis: {
        categories: props.trend.map(t =>
            new Date(t.month + '-01').toLocaleString('en', { month: 'short', timeZone: 'UTC' })
        )
    },
    yaxis: {
        labels: {
            formatter: (val: number) => formatCurrency(val, userCurrency.value)
        }
    }
}));

const series = [
    { name: 'Income', data: props.trend.map(t => t.income) },
    { name: 'Expenses', data: props.trend.map(t => t.expense) }
];
</script>

<template>

    <Head title="Dashboard" />

    <AuthenticatedLayout>
        <template #header>
            <div class="font-sans">
                <h2 class="font-black text-xl sm:text-2xl text-slate-900 tracking-tight">Welcome Back!</h2>
                <p class="text-xs text-slate-400 mt-0.5 font-medium">Here is a quick overview of your current financial
                    standing.</p>
            </div>
        </template>

        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-8">

                <!-- Statistika (Kartice) -->
                <div class="grid grid-cols-1 md:grid-cols-4 gap-6">
                    <div class="bg-white p-6 rounded-2xl shadow-sm border border-slate-100">
                        <p class="text-xs font-black text-slate-400 uppercase tracking-widest mb-1">Spent Today</p>
                        <p class="text-2xl font-black text-red-500">{{ formatCurrency(stats.spentToday, userCurrency) }}
                        </p>
                    </div>
                    <div class="bg-white p-6 rounded-2xl shadow-sm border border-slate-100">
                        <p class="text-xs font-black text-slate-400 uppercase tracking-widest mb-1">Monthly Income</p>
                        <p class="text-2xl font-black text-emerald-600">{{ formatCurrency(stats.monthlyIncome,
                            userCurrency) }}
                        </p>
                    </div>
                    <div class="bg-white p-6 rounded-2xl shadow-sm border border-slate-100">
                        <p class="text-xs font-black text-slate-400 uppercase tracking-widest mb-1">Monthly Expenses</p>
                        <p class="text-2xl font-black text-slate-800">{{ formatCurrency(stats.monthlyExpenses,
                            userCurrency) }}
                        </p>
                    </div>
                    <!-- Prebačeno sa indigo na emerald temu -->
                    <div class="bg-white p-6 rounded-2xl shadow-sm border border-emerald-100 bg-emerald-50/30">
                        <p class="text-xs font-black text-emerald-500 uppercase tracking-widest mb-1">Savings</p>
                        <p class="text-2xl font-black text-emerald-700">{{ formatCurrency(stats.savings, userCurrency)
                            }}</p>
                    </div>
                </div>

                <!-- Grafikon i Aktivnosti -->
                <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
                    <div class="lg:col-span-2 bg-white p-8 rounded-2xl shadow-sm border border-slate-100">
                        <h3 class="font-bold text-slate-800 mb-6">Income vs Expenses (6 Months)</h3>
                        <VueApexCharts type="bar" height="300" :options="chartOptions" :series="series" />
                    </div>

                    <div class="bg-white p-8 rounded-2xl shadow-sm border border-slate-100">
                        <div class="flex justify-between items-center mb-6">
                            <h3 class="font-bold text-slate-800">Recent Activity</h3>
                            <Link :href="route('transactions.index')"
                                class="text-xs text-emerald-600 hover:text-emerald-700 font-bold">
                                View All
                            </Link>
                        </div>
                        <div class="space-y-4">
                            <div v-for="t in recentTransactions" :key="t.id"
                                class="flex justify-between items-center pb-4 border-b border-slate-50 last:border-0">
                                <div>
                                    <p class="text-sm font-bold text-slate-700 capitalize">{{ t.description ||
                                        t.category }}</p>
                                    <p class="text-[10px] text-slate-400 uppercase tracking-tighter">{{ t.category }}
                                    </p>
                                </div>
                                <p :class="t.type === 'expense' ? 'text-red-500' : 'text-emerald-600'"
                                    class="font-black text-sm">
                                    {{ t.type === 'expense' ? '-' : '+' }}{{ formatCurrency(Number(t.display_amount ??
                                    t.amount), userCurrency) }}
                                </p>
                            </div>
                            <div v-if="recentTransactions.length === 0"
                                class="text-center py-10 text-slate-400 text-sm italic">
                                No recent activity.
                            </div>
                        </div>
                    </div>
                </div>

            </div>
        </div>
    </AuthenticatedLayout>
</template>