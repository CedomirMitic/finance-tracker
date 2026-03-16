<script setup lang="ts">
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import Modal from '@/Components/Modal.vue';
import { Head, router, useForm } from '@inertiajs/vue3';
import { formatCurrency } from '@/Utils/formatters';
import { computed, ref } from 'vue';
import type { ApexOptions } from 'apexcharts';
import VueApexCharts from "vue3-apexcharts";

const props = defineProps<{
    stats: Array<{ category: string, total: string }>,
    budgets: Record<string, number>,
    transactions: Array<{ id: number, category: string, description: string, amount: string, type: string, created_at: string }>,
    totalIncome: number,
    totalExpenses: number,
    availableYears: number[],
    months: Array<{ value: string, label: string }>,
    selectedYear: number,
    selectedMonth: string
}>();

const selectedYear = ref(props.selectedYear);
const selectedMonth = ref(props.selectedMonth);
const showingModal = ref(false);
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
    budgetForm.category = category;
    budgetForm.amount = props.budgets[category] || 0;
    showingModal.value = true;
};

const submitBudget = () => {
    budgetForm.post(route('statistics.budget.update'), {
        onSuccess: () => {
            showingModal.value = false;
        },
    });
};

const chartOptions = computed<ApexOptions>(() => ({
    labels: props.stats.map(item => item.category),
    chart: { 
        type: 'donut', 
        animations: { enabled: true, speed: 800 },
        sparkline: { enabled: false } 
    },
    // Isključujemo legendu unutar grafikona da bi krug bio veći
    legend: { show: false }, 
    dataLabels: { enabled: false },
    tooltip: { y: { formatter: (value: number) => formatCurrency(value) } },
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
                        formatter: (w) => formatCurrency(w.globals.seriesTotals.reduce((a: number, b: number) => a + b, 0))
                    },
                    value: { 
                        show: true, 
                        fontSize: '20px', 
                        fontWeight: '900',
                        formatter: (val: string) => formatCurrency(Number(val)) 
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
            <div class="flex justify-between items-center">
                <h2 class="font-semibold text-md sm:text-xl text-gray-800 leading-tight">Financial Statistics</h2>
                <div class="flex items-center gap-2 sm:gap-4">
                    <select v-model="selectedMonth" @change="updateStats" class="rounded-md border-gray-300 shadow-sm py-1 text-sm">
                        <option v-for="m in months" :key="m.value" :value="m.value">{{ m.label }}</option>
                    </select>
                    <select v-model="selectedYear" @change="updateStats" class="rounded-md border-gray-300 shadow-sm py-1 text-sm">
                        <option v-for="y in availableYears" :key="y" :value="y">{{ y }}</option>
                    </select>
                </div>
            </div>
        </template>

        <div class="py-12">
            <div class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8">
                <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
                    <div class="bg-white p-6 rounded-xl shadow-sm border-l-4 border-green-500 text-center md:text-left">
                        <p class="text-xs text-gray-500 uppercase font-bold tracking-wider">Total Income</p>
                        <p class="text-2xl font-black text-green-600">{{ formatCurrency(totalIncome) }}</p>
                    </div>
                    <div class="bg-white p-6 rounded-xl shadow-sm border-l-4 border-red-500 text-center md:text-left">
                        <p class="text-xs text-gray-500 uppercase font-bold tracking-wider">Total Expenses</p>
                        <p class="text-2xl font-black text-red-600">{{ formatCurrency(totalExpenses) }}</p>
                    </div>
                    <div class="bg-white p-6 rounded-xl shadow-sm border-l-4 text-center md:text-left" :class="netBalance >= 0 ? 'border-indigo-500' : 'border-orange-500'">
                        <p class="text-xs text-gray-500 uppercase font-bold tracking-wider">Net Balance</p>
                        <p class="text-2xl font-black" :class="netBalance >= 0 ? 'text-indigo-600' : 'text-orange-600'">{{ formatCurrency(netBalance) }}</p>
                    </div>
                </div>

                <div class="bg-white shadow-sm rounded-xl p-4 sm:p-8 mb-8 overflow-hidden">
                    <div v-if="series.length > 0">
                        <div class="flex flex-col lg:flex-row items-center justify-between gap-10">
                            
                            <div class="w-full lg:w-1/2 flex justify-center">
                                <div class="w-full relative mx-auto" style="max-width: 400px;">
                                    <VueApexCharts 
                                        type="donut" 
                                        width="100%" 
                                        :options="chartOptions" 
                                        :series="series" 
                                    />
                                </div>
                            </div>

                            <div class="flex-1 w-full max-w-md lg:max-w-none">
                                <h4 class="text-xs font-bold text-gray-400 uppercase mb-6 tracking-widest text-center lg:text-left underline decoration-indigo-200 underline-offset-8">
                                    Expense Breakdown
                                </h4>
                                <div class="space-y-6">
                                    <div v-for="(item, index) in stats" :key="item.category"
                                        @click="toggleCategory(item.category)"
                                        class="group cursor-pointer p-3 -mx-2 rounded-xl transition hover:bg-gray-50"
                                        :class="{ 'bg-indigo-50 ring-1 ring-indigo-100': activeCategory === item.category }">
                                        
                                        <div class="flex justify-between items-center text-sm mb-2">
                                            <span class="font-bold text-gray-700 capitalize flex items-center min-w-0 flex-1 mr-2">
                                                <span class="truncate">{{ item.category }}</span>
                                                <button @click.stop="openBudgetModal(item.category)" type="button"
                                                    class="ml-2 flex-shrink-0 text-[9px] bg-gray-100 px-1.5 py-0.5 rounded text-gray-400 hover:bg-indigo-600 hover:text-white transition uppercase font-bold">Budget</button>
                                            </span>
                                            <span class="text-gray-900 font-black flex-shrink-0">{{ formatCurrency(Number(item.total)) }}</span>
                                        </div>

                                        <div class="w-full bg-gray-100 h-2 rounded-full overflow-hidden">
                                            <div class="h-full transition-all duration-1000"
                                                :class="budgets[item.category] > 0 && Number(item.total) > budgets[item.category] ? 'bg-red-500' : 'bg-indigo-500'"
                                                :style="{ width: Math.min((Number(item.total) / (budgets[item.category] > 0 ? budgets[item.category] : grandTotal || 1) * 100), 100) + '%' }">
                                            </div>
                                        </div>

                                        <div class="flex justify-between mt-1 min-h-[14px]">
                                            <div v-if="budgets[item.category] > 0" class="text-[10px] italic">
                                                <span class="text-gray-400">Limit: {{ formatCurrency(budgets[item.category]) }}</span>
                                                <span v-if="Number(item.total) > budgets[item.category]" class="text-red-500 font-bold ml-1">
                                                    (Over by {{ formatCurrency(Number(item.total) - budgets[item.category]) }})
                                                </span>
                                            </div>
                                            <p v-else class="text-[10px] text-gray-300 italic">No limit set</p>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div v-else class="py-20 text-center text-gray-400">No data found.</div>
                </div>

                <transition enter-active-class="transition duration-300 ease-out"
                    enter-from-class="transform scale-95 opacity-0" enter-to-class="transform scale-100 opacity-100">
                    <div v-if="activeCategory" class="bg-white shadow-sm rounded-xl p-6 border-t-4 border-indigo-500">
                        <div class="flex justify-between items-center mb-6">
                            <h3 class="text-lg font-bold text-gray-800">
                                Details for <span class="capitalize text-indigo-600">{{ activeCategory }}</span>
                            </h3>
                            <button @click="activeCategory = null" class="text-gray-400 hover:text-gray-600 text-sm font-bold">&times; Close</button>
                        </div>
                        <div class="overflow-x-auto">
                            <table class="w-full text-left text-sm">
                                <thead>
                                    <tr class="text-gray-400 border-b uppercase text-[10px] tracking-widest">
                                        <th class="pb-3">Description</th>
                                        <th class="pb-3 text-right">Amount</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y">
                                    <tr v-for="t in filteredTransactions" :key="t.id" class="hover:bg-gray-50 transition">
                                        <td class="py-3 font-medium text-gray-700">{{ t.description }}</td>
                                        <td class="py-3 text-right font-black text-red-500">{{ formatCurrency(Number(t.amount)) }}</td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </transition>
            </div>
        </div>

        <Modal :show="showingModal" @close="showingModal = false">
            <div class="p-6">
                <h2 class="text-lg font-black text-gray-900 capitalize text-center mb-6 border-b pb-4">
                    Budget for {{ budgetForm.category }}
                </h2>
                <div class="mb-6">
                    <label class="block text-xs font-bold text-gray-400 uppercase mb-2">Monthly Limit (€)</label>
                    <input v-model="budgetForm.amount" type="number" step="0.01" class="w-full border-gray-200 rounded-lg focus:ring-indigo-500 p-3" />
                </div>
                <div class="flex gap-3">
                    <button @click="showingModal = false" class="flex-1 py-3 text-sm font-bold text-gray-500 bg-gray-50 rounded-lg hover:bg-gray-100 transition">Cancel</button>
                    <button @click="submitBudget" class="flex-1 py-3 bg-indigo-600 text-white rounded-lg text-sm font-bold hover:bg-indigo-700 shadow-lg shadow-indigo-200 transition">Save</button>
                </div>
            </div>
        </Modal>
    </AuthenticatedLayout>
</template>