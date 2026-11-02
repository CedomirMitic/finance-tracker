<script setup lang="ts">
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import Modal from '@/Components/Modal.vue';
import { Head, router, useForm, usePage } from '@inertiajs/vue3';
import { formatCurrency } from '@/Utils/formatters';
import { computed, ref } from 'vue';
import type { ApexOptions } from 'apexcharts';
import VueApexCharts from "vue3-apexcharts";
import { PageProps } from '@/types';

const props = defineProps<{
    stats: Array<{ category: string, total: string }>,
    budgets: Record<string, number>,
    transactions: Array<{ id: number, category: string, description: string, amount: string, type: string, created_at: string, display_amount: number }>,
    totalIncome: number,
    totalExpenses: number,
    availableYears: number[],
    months: Array<{ value: string, label: string }>,
    selectedYear: number,
    selectedMonth: string
}>();

const page = usePage<PageProps>();
const userCurrency = computed(() => page.props.auth.user?.preferred_currency ?? 'EUR');
const user = computed(() => page.props.auth.user);

const selectedYear = ref(props.selectedYear);
const selectedMonth = ref(props.selectedMonth);
const showingModal = ref(false);
const showProModal = ref(false);
const activeCategory = ref<string | null>(null);

const budgetForm = useForm({
    category: '',
    amount: 0,
});

const grandTotal = computed(() => props.stats.reduce((acc, item) => acc + Number(item.total), 0));
const netBalance = computed(() => props.totalIncome - props.totalExpenses);

const filteredTransactions = computed(() => {
    if (!activeCategory.value) return [];
    return props.transactions.filter(t => t.category === activeCategory.value && t.type === 'expense');
});

const updateStats = () => {
    activeCategory.value = null;
    router.get(route('statistics'), {
        year: selectedYear.value,
        month: selectedMonth.value
    }, { preserveState: true, replace: true });
};

const toggleCategory = (category: string) => {
    activeCategory.value = activeCategory.value === category ? null : category;
};

const openBudgetModal = (category: string) => {
    if (!user.value?.subscribed) {
        showProModal.value = true;
        return;
    }

    budgetForm.category = category;
    budgetForm.amount = props.budgets[category] || 0;
    showingModal.value = true;
};

const submitBudget = () => {
    budgetForm.post(route('statistics.budget.update'), {
        onSuccess: () => {
            showingModal.value = false;
            budgetForm.reset();
        },
    });
};

const redirectToUpgrade = () => {
    showProModal.value = false;
    router.visit(route('billing.index'));
};
const chartOptions = computed<ApexOptions>(() => ({
    labels: props.stats.map(item => item.category),
    chart: {
        type: 'donut',
        animations: { enabled: true, speed: 800 },
        sparkline: { enabled: false }
    },
    legend: { show: false },
    dataLabels: { enabled: false },
    tooltip: { y: { formatter: (value: number) => formatCurrency(value, userCurrency.value) } },
    plotOptions: {
        pie: {
            donut: {
                size: '75%',
                labels: {
                    show: true,
                    total: {
                        show: true,
                        label: 'Expenses',
                        color: '#9ca3af',
                        fontSize: '12px',
                        formatter: (w) => formatCurrency(w.globals.seriesTotals.reduce((a: number, b: number) => a + b, 0), userCurrency.value)
                    },
                    value: {
                        show: true,
                        fontSize: '20px',
                        fontWeight: '900',
                        formatter: (val: string) => formatCurrency(Number(val), userCurrency.value)
                    }
                }
            }
        }
    },
    grid: { padding: { top: 0, bottom: 0, left: 0, right: 0 } },
    colors: ['#6366f1', '#10b981', '#f59e0b', '#ef4444', '#8b5cf6', '#ec4899'],
}));

const series = computed(() => props.stats.map(item => Number(item.total)));
</script>

<template>

    <Head title="Statistics" />
    <AuthenticatedLayout>
<template #header>
            <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4 font-sans">
                <div>
                    <h2 class="font-black text-xl sm:text-2xl text-slate-900 tracking-tight">Financial Statistics</h2>
                    <p class="text-xs text-slate-400 mt-0.5 font-medium">Analyze your monthly breakdown and category limits.</p>
                </div>
                
                <!-- Selektori za mesec i godinu sa dovoljno prostora da tekst ne prelazi preko strelice -->
                <div class="flex items-center gap-2.5">
                    <select v-model="selectedMonth" @change="updateStats"
                        class="bg-white border border-slate-200 rounded-2xl pl-4 pr-10 py-2.5 text-sm font-bold text-slate-700 shadow-sm hover:border-slate-300 focus:border-emerald-500 focus:ring-1 focus:ring-emerald-500 outline-none transition-all cursor-pointer">
                        <option v-for="m in months" :key="m.value" :value="m.value">{{ m.label }}</option>
                    </select>
                    
                    <select v-model="selectedYear" @change="updateStats"
                        class="bg-white border border-slate-200 rounded-2xl pl-4 pr-10 py-2.5 text-sm font-bold text-slate-700 shadow-sm hover:border-slate-300 focus:border-emerald-500 focus:ring-1 focus:ring-emerald-500 outline-none transition-all cursor-pointer">
                        <option v-for="y in availableYears" :key="y" :value="y">{{ y }}</option>
                    </select>
                </div>
            </div>
        </template>

        <div class="py-12 font-sans">
            <div class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8">
                <!-- Top Summary Cards -->
                <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
                    <div
                        class="bg-white p-6 rounded-3xl shadow-sm border border-slate-100 border-l-4 border-l-emerald-500 text-center md:text-left">
                        <p class="text-xs text-slate-400 uppercase font-black tracking-wider">Total Income</p>
                        <p class="text-2xl font-black text-emerald-600 mt-1">{{ formatCurrency(totalIncome,
                            userCurrency) }}</p>
                    </div>
                    <div
                        class="bg-white p-6 rounded-3xl shadow-sm border border-slate-100 border-l-4 border-l-red-500 text-center md:text-left">
                        <p class="text-xs text-slate-400 uppercase font-black tracking-wider">Total Expenses</p>
                        <p class="text-2xl font-black text-red-600 mt-1">{{ formatCurrency(totalExpenses, userCurrency)
                        }}</p>
                    </div>
                    <div class="bg-white p-6 rounded-3xl shadow-sm border border-slate-100 border-l-4 text-center md:text-left"
                        :class="netBalance >= 0 ? 'border-l-emerald-600' : 'border-l-orange-500'">
                        <p class="text-xs text-slate-400 uppercase font-black tracking-wider">Net Balance</p>
                        <p class="text-2xl font-black mt-1"
                            :class="netBalance >= 0 ? 'text-emerald-700' : 'text-orange-600'">
                            {{ formatCurrency(netBalance, userCurrency) }}
                        </p>
                    </div>
                </div>

                <!-- Main Chart & Breakdown Section -->
                <div class="bg-white shadow-sm rounded-3xl border border-slate-100 p-6 sm:p-8 mb-8 overflow-hidden">
                    <div v-if="series.length > 0">
                        <div class="flex flex-col lg:flex-row items-center justify-between gap-10">
                            <div class="w-full lg:w-1/2 flex justify-center">
                                <div class="w-full relative mx-auto" style="max-width: 400px;">
                                    <VueApexCharts type="donut" width="100%" :options="chartOptions" :series="series" />
                                </div>
                            </div>

                            <div class="flex-1 w-full max-w-md lg:max-w-none">
                                <h4
                                    class="text-xs font-black text-slate-400 uppercase mb-6 tracking-widest text-center lg:text-left underline decoration-emerald-200 underline-offset-8">
                                    Expense Breakdown
                                </h4>
                                <div class="space-y-4">
                                    <div v-for="(item, index) in stats" :key="item.category"
                                        @click="toggleCategory(item.category)"
                                        class="group cursor-pointer p-4 -mx-2 rounded-2xl transition hover:bg-slate-50 border border-transparent"
                                        :class="{ 'bg-emerald-50/60 border-emerald-100': activeCategory === item.category }">

                                        <div class="flex justify-between items-center text-sm mb-2">
                                            <span
                                                class="font-bold text-slate-700 capitalize flex items-center min-w-0 flex-1 mr-2">
                                                <span class="truncate">{{ item.category }}</span>
                                                <button @click.stop="openBudgetModal(item.category)" type="button"
                                                    class="ml-2 flex-shrink-0 text-[10px] bg-slate-100 px-2 py-1 rounded-xl text-slate-500 hover:bg-emerald-600 hover:text-white transition uppercase font-extrabold flex items-center gap-1">
                                                    Budget
                                                    <span v-if="!user?.subscribed"
                                                        class="text-[8px] bg-amber-100 text-amber-800 px-1 rounded-lg">PRO</span>
                                                </button>
                                            </span>
                                            <span class="text-slate-900 font-black flex-shrink-0">
                                                {{ formatCurrency(Number(item.total), userCurrency) }}
                                            </span>
                                        </div>

                                        <div class="w-full bg-slate-100 h-2.5 rounded-full overflow-hidden">
                                            <div class="h-full transition-all duration-1000 rounded-full"
                                                :class="budgets[item.category] > 0 && Number(item.total) > budgets[item.category] ? 'bg-red-500' : 'bg-emerald-500'"
                                                :style="{ width: Math.min((Number(item.total) / (budgets[item.category] > 0 ? budgets[item.category] : grandTotal || 1) * 100), 100) + '%' }">
                                            </div>
                                        </div>

                                        <div class="flex justify-between mt-1.5 min-h-[14px]">
                                            <div v-if="budgets[item.category] > 0" class="text-[11px] italic">
                                                <span class="text-slate-400 font-medium">Limit: {{
                                                    formatCurrency(budgets[item.category], userCurrency) }}</span>
                                                <span v-if="Number(item.total) > budgets[item.category]"
                                                    class="text-red-500 font-bold ml-1">
                                                    (Over by {{ formatCurrency(Number(item.total) -
                                                        budgets[item.category],
                                                        userCurrency) }})
                                                </span>
                                            </div>
                                            <p v-else class="text-[11px] text-slate-300 italic font-medium">No limit set
                                            </p>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div v-else class="py-20 text-center text-slate-400 font-medium">No data found.</div>
                </div>

                <!-- Category Details Transition Table -->
                <transition enter-active-class="transition duration-300 ease-out"
                    enter-from-class="transform scale-95 opacity-0" enter-to-class="transform scale-100 opacity-100">
                    <div v-if="activeCategory"
                        class="bg-white shadow-sm rounded-3xl border border-slate-100 p-6 sm:p-8 border-t-4 border-t-emerald-500">
                        <div class="flex justify-between items-center mb-6">
                            <h3 class="text-lg font-black text-slate-900 tracking-tight">
                                Details for <span class="capitalize text-emerald-600">{{ activeCategory }}</span>
                            </h3>
                            <button @click="activeCategory = null"
                                class="text-slate-400 hover:text-slate-600 text-sm font-bold p-1 rounded-xl hover:bg-slate-100 transition-colors">&times;
                                Close</button>
                        </div>
                        <div class="overflow-x-auto">
                            <table class="w-full text-left text-sm">
                                <thead>
                                    <tr
                                        class="text-slate-400 border-b border-slate-100 uppercase text-[10px] tracking-widest font-black">
                                        <th class="pb-3">Description</th>
                                        <th class="pb-3 text-right">Amount</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-slate-100">
                                    <tr v-for="t in filteredTransactions" :key="t.id"
                                        class="hover:bg-slate-50 transition">
                                        <td class="py-3.5 font-semibold text-slate-700">{{ t.description }}</td>
                                        <td class="py-3.5 text-right font-black text-red-500">
                                            {{ formatCurrency(Number(t.amount), userCurrency) }}
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </transition>
            </div>
        </div>

        <!-- Budget Modal -->
        <Modal :show="showingModal" @close="showingModal = false" max-width="md">
            <div class="p-8 font-sans">
                <h2
                    class="text-xl font-black text-slate-900 capitalize text-center mb-6 border-b border-slate-100 pb-4">
                    Budget for {{ budgetForm.category }}
                </h2>

                <div class="mb-6">
                    <label class="block text-xs font-black text-slate-400 uppercase tracking-wider mb-2">Monthly
                        Limit</label>
                    <div class="relative flex items-center">
                        <input v-model="budgetForm.amount" type="number" step="0.01"
                            class="w-full border-slate-200 rounded-2xl focus:border-emerald-500 focus:ring-emerald-500 p-3.5 pr-16 text-slate-800 font-medium" />
                        <span class="absolute right-4 text-xs font-bold text-slate-400">
                            {{ userCurrency }}
                        </span>
                    </div>
                </div>

                <div class="flex gap-3">
                    <button @click="showingModal = false"
                        class="flex-1 py-3.5 text-sm font-bold text-slate-600 bg-slate-100 rounded-2xl hover:bg-slate-200 transition">Cancel</button>
                    <button @click="submitBudget"
                        class="flex-1 py-3.5 bg-emerald-600 text-white rounded-2xl text-sm font-bold hover:bg-emerald-700 shadow-lg shadow-emerald-600/25 transition">Save</button>
                </div>
            </div>
        </Modal>

        <!-- PRO Modal -->
        <Modal :show="showProModal" @close="showProModal = false" max-width="md">
            <div class="p-8 text-center font-sans">
                <div
                    class="w-14 h-14 bg-amber-100 text-amber-600 rounded-2xl flex items-center justify-center mx-auto mb-4 text-2xl font-bold shadow-inner">
                    ⭐
                </div>
                <h2 class="text-xl font-black text-slate-900 mb-2 tracking-tight">PRO Feature</h2>
                <p class="text-sm text-slate-500 mb-8 font-medium">
                    Setting category budgets and tracking limits is exclusively available for PRO members. Upgrade your
                    account
                    to unlock this feature.
                </p>
                <div class="flex gap-3">
                    <button @click="showProModal = false"
                        class="flex-1 py-3.5 text-sm font-bold text-slate-600 bg-slate-100 rounded-2xl hover:bg-slate-200 transition">Close</button>
                    <button @click="redirectToUpgrade"
                        class="flex-1 py-3.5 bg-emerald-600 text-white rounded-2xl text-sm font-bold hover:bg-emerald-700 shadow-lg shadow-emerald-600/25 transition">Upgrade
                        to PRO</button>
                </div>
            </div>
        </Modal>
    </AuthenticatedLayout>
</template>