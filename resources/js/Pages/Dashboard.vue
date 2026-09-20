<script setup lang="ts">
declare function route(name?: string, params?: any): string;[]
import type { ApexOptions } from 'apexcharts';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link, usePage} from '@inertiajs/vue3';
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

const chartOptions: ApexOptions = {
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
    colors: ['#10b981', '#ef4444'],
    xaxis: {
        categories: props.trend.map(t => 'Month ' + t.month)
    },
    yaxis: {
        labels: {
            formatter: (val: number) => formatCurrency(val)
        }
    }
};

const series = [
    { name: 'Income', data: props.trend.map(t => t.income) },
    { name: 'Expenses', data: props.trend.map(t => t.expense) }
];
</script>

<template>

    <Head title="Dashboard" />

    <AuthenticatedLayout>
        <template #header>
            <h2 class="font-bold text-2xl text-gray-800 leading-tight">Welcome Back!</h2>
        </template>

        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-8">

                <div class="grid grid-cols-1 md:grid-cols-4 gap-6">
                    <div class="bg-white p-6 rounded-2xl shadow-sm border border-gray-100">
                        <p class="text-xs font-black text-gray-400 uppercase tracking-widest mb-1">Spent Today</p>
                        <p class="text-2xl font-black text-red-500">{{ formatCurrency(stats.spentToday, userCurrency) }}</p>
                    </div>
                    <div class="bg-white p-6 rounded-2xl shadow-sm border border-gray-100">
                        <p class="text-xs font-black text-gray-400 uppercase tracking-widest mb-1">Monthly Income</p>
                        <p class="text-2xl font-black text-green-500">{{ formatCurrency(stats.monthlyIncome, userCurrency) }}</p>
                    </div>
                    <div class="bg-white p-6 rounded-2xl shadow-sm border border-gray-100">
                        <p class="text-xs font-black text-gray-400 uppercase tracking-widest mb-1">Monthly Expenses</p>
                        <p class="text-2xl font-black text-gray-800">{{ formatCurrency(stats.monthlyExpenses, userCurrency) }}</p>
                    </div>
                    <div class="bg-white p-6 rounded-2xl shadow-sm border border-indigo-100 bg-indigo-50/30">
                        <p class="text-xs font-black text-indigo-400 uppercase tracking-widest mb-1">Savings</p>
                        <p class="text-2xl font-black text-indigo-600">{{ formatCurrency(stats.savings, userCurrency) }}</p>
                    </div>
                </div>

                <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
                    <div class="lg:col-span-2 bg-white p-8 rounded-2xl shadow-sm border border-gray-100">
                        <h3 class="font-bold text-gray-800 mb-6">Income vs Expenses (6 Months)</h3>
                        <VueApexCharts type="bar" height="300" :options="chartOptions" :series="series" />
                    </div>

                    <div class="bg-white p-8 rounded-2xl shadow-sm border border-gray-100">
                        <div class="flex justify-between items-center mb-6">
                            <h3 class="font-bold text-gray-800">Recent Activity</h3>
                            <Link :href="route('transactions.index')"
                                class="text-xs text-indigo-600 hover:underline font-bold">
                                View All
                            </Link>
                        </div>
                        <div class="space-y-4">
                            <div v-for="t in recentTransactions" :key="t.id"
                                class="flex justify-between items-center pb-4 border-b border-gray-50 last:border-0">
                                <div>
                                    <p class="text-sm font-bold text-gray-700 capitalize">{{ t.description || t.category
                                        }}</p>
                                    <p class="text-[10px] text-gray-400 uppercase tracking-tighter">{{ t.category }}</p>
                                </div>
                                <p :class="t.type === 'expense' ? 'text-red-500' : 'text-green-500'"
                                    class="font-black text-sm">
                                    {{ t.type === 'expense' ? '-' : '+' }}{{ formatCurrency(Number(t.display_amount ?? t.amount), userCurrency) }}
                                </p>
                            </div>
                            <div v-if="recentTransactions.length === 0"
                                class="text-center py-10 text-gray-400 text-sm italic">
                                No recent activity.
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>