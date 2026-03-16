<script setup lang="ts">
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, useForm, router } from '@inertiajs/vue3';
import { computed } from 'vue';
import { formatCurrency } from '@/Utils/formatters';
import { Link } from '@inertiajs/vue3';
import { ref } from 'vue';

// Define Props first so they are available for computed properties
const props = defineProps<{
    transactions: any[]
}>();

// Calculate total balance based on transaction type
const totalBalance = computed(() => {
    return props.transactions.reduce((acc, t) => {
        const amount = Number(t.amount);
        return t.type === 'income' ? acc + amount : acc - amount;
    }, 0);
});

// Count active subscriptions
const activeSubsCount = computed(() => {
    return props.transactions.filter(t => t.payment_type === 'recurring').length;
});

// Total recurring income (e.g., Salary, Rent you collect)
const recurringIncome = computed(() => {
    return props.transactions
        .filter(t => t.payment_type === 'recurring' && t.type === 'income')
        .reduce((acc, t) => acc + Number(t.amount), 0);
});

// Total recurring expenses (Subscriptions, Bills)
const recurringExpenses = computed(() => {
    return props.transactions
        .filter(t => t.payment_type === 'recurring' && t.type === 'expense')
        .reduce((acc, t) => acc + Number(t.amount), 0);
});

// Net monthly recurring result
const recurringNet = computed(() => recurringIncome.value - recurringExpenses.value);

// Category to Emoji mapping helper
const getCategoryIcon = (category: string) => {
    const icons: Record<string, string> = {
        Salary: '💰',
        Food: '🍔',
        Transport: '🚗',
        Entertainment: '💃',
        Bills: '📄',
        Other: '📦',
    };
    return icons[category] || '❓';
};

// Form initialization
const form = useForm({
    description: '',
    amount: null, // Bolje je null nego 0 da bi placeholder radio
    type: 'expense', // income ili expense
    payment_type: 'manual', // manual ili recurring
    billing_day: 1, // Default dan u mesecu
    category: 'Food'
});

// Submit new transaction
const submit = () => {
    // Ovo će ti u Chrome konzoli (F12) tačno reći šta ide ka serveru
    console.log("Slanje podataka:", form.data());

    form.post(route('transactions.store'), {
        onSuccess: () => form.reset(),
        onError: () => console.log("Validation failed"),
    });
};

// Modal State
const isDeleteModalOpen = ref(false);
const transactionToDelete = ref<any>(null);

// Function that opens modal
const confirmDelete = (transaction: any) => {
    transactionToDelete.value = transaction;
    isDeleteModalOpen.value = true;
};

const executeAction = () => {
    if (!transactionToDelete.value) return;

    if (transactionToDelete.value.payment_type === 'recurring') {
        // If its subscription just cancel the subscription without deleting (Patch request)
        router.patch(route('transactions.cancel', transactionToDelete.value.id), {}, {
            onSuccess: () => {
                isDeleteModalOpen.value = false;
                transactionToDelete.value = null;
            }
        });
    } else {
        // If its manuel without subscription, delete it
        router.delete(route('transactions.destroy', transactionToDelete.value.id), {
            onSuccess: () => {
                isDeleteModalOpen.value = false;
                transactionToDelete.value = null;
            }
        });
    }
};

// Display Date time of transaction
const formatDate = (dateString: string) => {
    if (!dateString) return ''; // Handle empty dates safely

    const date = new Date(dateString);

    // Passing 'undefined' tells the browser to use the user's local settings
    return date.toLocaleDateString(undefined, {
        day: 'numeric',
        month: 'short',
        year: 'numeric',
    });
};
</script>

<template>

    <Head title="Transactions" />

    <AuthenticatedLayout>
        <template #header>
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">My Finances</h2>
        </template>

        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 bg-white p-6 shadow rounded-lg">
                <div class="flex bg-gray-100 p-1 rounded-xl mb-6 max-w-md mx-auto">
                    <button type="button" @click="form.payment_type = 'manual'"
                        :class="form.payment_type === 'manual' ? 'bg-white shadow-sm text-indigo-600' : 'text-gray-500'"
                        class="flex-1 py-2 text-sm font-bold rounded-lg transition-all">
                        One-time
                    </button>
                    <button type="button" @click="form.payment_type = 'recurring'"
                        :class="form.payment_type === 'recurring' ? 'bg-white shadow-sm text-indigo-600' : 'text-gray-500'"
                        class="flex-1 py-2 text-sm font-bold rounded-lg transition-all">
                        Subscription 🔄
                    </button>
                </div>

                <div v-if="form.payment_type === 'recurring'"
                    class="mb-6 p-4 bg-indigo-50 border border-indigo-100 rounded-xl animate-in fade-in slide-in-from-top-4 duration-300">
                    <label class="block text-xs font-black uppercase text-indigo-400 mb-2">Billing Day (Every
                        month)</label>
                    <div class="flex items-center gap-3">
                        <select v-model="form.billing_day"
                            class="w-full border-gray-200 rounded-lg focus:ring-indigo-500">
                            <option v-for="day in 28" :key="day" :value="day">Every {{ day }}. of the month</option>
                        </select>
                        <span class="text-xs text-indigo-400 italic font-medium">Auto-adds every month</span>
                    </div>
                </div>
                <form @submit.prevent="submit"
                    class="grid grid-cols-1 md:grid-cols-5 gap-4 mb-8 p-6 bg-gray-50 rounded-xl border-t-4 transition-all duration-300"
                    :class="form.type === 'income' ? 'border-green-500' : 'border-red-500'">

                    <div class="flex flex-col w-full">
                        <input v-model="form.description" type="text" placeholder="Description"
                            class="border-gray-300 rounded-lg focus:ring-2" :class="[form.type === 'income' ? 'focus:ring-green-500' : 'focus:ring-red-500',
                            form.errors.description ? 'border-red-500' : 'border-gray-300']">
                        <span v-if="form.errors.description" class="text-red-500 text-xs mt-1 font-semibold">
                            {{ form.errors.description }}
                        </span>
                    </div>

                    <div class="flex flex-col w-full">
                        <input v-model="form.amount" type="number" step="0.01" placeholder="Amount"
                            class="border-gray-300 rounded-lg focus:ring-2" :class="[form.type === 'income' ? 'focus:ring-green-500' : 'focus:ring-red-500',
                            form.errors.amount ? 'border-red-500' : 'border-gray-300']">
                        <span v-if="form.errors.amount" class="text-red-500 text-xs mt-1 font-semibold">
                            {{ form.errors.amount }}
                        </span>
                    </div>
                    <select v-model="form.type" class="border-gray-300 rounded-lg">
                        <option value="expense">Expense (-)</option>
                        <option value="income">Income (+)</option>
                    </select>

                    <select v-model="form.category" class="border-gray-300 rounded-lg">
                        <option value="Salary">Salary</option>
                        <option value="Food">Food</option>
                        <option value="Transport">Transport</option>
                        <option value="Bills">Bills</option>
                        <option value="Entertainment">Entertainment</option>
                        <option value="Other">Other</option>
                    </select>

                    <button type="submit" :disabled="form.processing"
                        class="text-white rounded-lg px-4 py-2 font-bold shadow transition-colors"
                        :class="form.type === 'income' ? 'bg-green-600 hover:bg-green-700' : 'bg-red-600 hover:bg-red-700'">
                        {{ form.type === 'income' ? 'Add Income' : 'Add Expense' }}
                    </button>
                </form>

                <div class="bg-gradient-to-br from-indigo-600 to-blue-700 p-6 rounded-xl shadow-xl text-white mb-8">
                    <div class="flex justify-between items-start">
                        <div>
                            <p class="text-indigo-100 text-sm font-medium uppercase tracking-wider">Total Balance</p>
                            <h2 class="text-4xl font-bold mt-1 tracking-tight">
                                {{ formatCurrency(totalBalance) }}
                            </h2>
                        </div>
                        <div class="bg-white/20 p-3 rounded-full">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24"
                                stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                        </div>
                    </div>
                    <div class="mt-6 flex gap-4 text-sm">
                        <div class="flex items-center">
                            <span class="w-2 h-2 bg-green-400 rounded-full mr-2"></span>
                            <span>Active Account</span>
                        </div>
                    </div>
                </div>

                <div v-if="activeSubsCount > 0"
                    class="mb-8 overflow-hidden bg-white border text-center border-slate-200 rounded-xl shadow-sm">
                    <div class="bg-slate-50 px-5 py-3 border-b border-slate-200">
                        <h3 class="text-[10px] font-black text-slate-400 uppercase tracking-[0.2em]">Monthly Recurring
                            Overview
                        </h3>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-3 divide-y md:divide-y-0 md:divide-x divide-slate-200">
                        <div class="p-5">
                            <p class="text-[10px] font-bold text-green-500 uppercase mb-1">Fixed Income</p>
                            <p class="text-xl font-black text-slate-800">{{ formatCurrency(recurringIncome) }}</p>
                            <p class="text-xs text-slate-400 mt-1">Guaranteed monthly inflow</p>
                        </div>

                        <div class="p-5">
                            <p class="text-[10px] font-bold text-red-500 uppercase mb-1">Fixed Expenses</p>
                            <p class="text-xl font-black text-slate-800">{{ formatCurrency(recurringExpenses) }}</p>
                            <p class="text-xs text-slate-400 mt-1">Subscriptions & commitments</p>
                        </div>

                        <div class="p-5 bg-slate-50/50">
                            <p class="text-[10px] font-bold text-indigo-500 uppercase mb-1">Predictable Net</p>
                            <p class="text-xl font-black"
                                :class="recurringNet >= 0 ? 'text-indigo-600' : 'text-red-600'">
                                {{ recurringNet >= 0 ? '+' : '' }}{{ formatCurrency(recurringNet) }}
                            </p>
                            <p class="text-xs text-slate-400 mt-1">Left after all automated items</p>
                        </div>
                    </div>
                </div>

                <div class="space-y-3">
                    <div v-for="t in transactions" :key="t.id"
                        class="flex items-center justify-between p-3 md:p-5 bg-white rounded-xl shadow-sm border border-gray-100 hover:shadow-md transition-shadow">

                        <div class="flex items-center gap-3 md:gap-6 min-w-0">
                            <div
                                class="p-2 bg-gray-50 rounded-full w-10 h-10 md:w-16 md:h-16 flex-shrink-0 flex items-center justify-center text-md md:text-xl">
                                {{ getCategoryIcon(t.category) }}
                            </div>

                            <div class="min-w-0">
                                <p class="font-bold text-gray-800 text-sm md:text-xl truncate leading-tight">
                                    {{ t.description }}
                                </p>
                                <div class="flex items-center gap-2">
                                    <p
                                        class="text-gray-400 font-medium uppercase tracking-wider text-[10px] md:text-xs">
                                        {{ t.category }}
                                    </p>
                                    <span v-if="t.payment_type === 'recurring'"
                                        class="text-[8px] md:text-xs text-indigo-500 bg-indigo-50 px-2 py-0.5 rounded-full whitespace-nowrap">
                                        🔄 <span class="hidden sm:inline">Every</span> {{ t.billing_day }}.
                                    </span>
                                </div>
                            </div>
                        </div>

                        <div class="flex items-center gap-3 md:gap-8 flex-shrink-0 ml-4">
                            <div class="text-right">
                                <p :class="t.type === 'income' ? 'text-green-600' : 'text-gray-900'"
                                    class="font-extrabold text-sm md:text-xl whitespace-nowrap">
                                    {{ t.type === 'income' ? '+' : '-' }}{{ formatCurrency(t.amount) }}
                                </p>
                                <p class="text-gray-400 text-[10px] md:text-sm">{{ formatDate(t.created_at) }}</p>
                            </div>

                            <button @click="confirmDelete(t)"
                                class="flex items-center justify-center p-2 md:px-4 md:py-2 rounded-lg transition-all"
                                :class="t.payment_type === 'recurring'
                                    ? 'bg-amber-50 text-amber-600'
                                    : 'bg-gray-50 text-gray-400 hover:bg-red-50 hover:text-red-600'">

                                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 md:h-6 md:w-6" fill="none"
                                    viewBox="0 0 24 24" stroke="currentColor">
                                    <path v-if="t.payment_type !== 'recurring'" stroke-linecap="round"
                                        stroke-linejoin="round" stroke-width="2"
                                        d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                    <path v-else stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M10 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2m7-2a9 9 0 11-18 0 9 9 0 0118 0z" />
                                </svg>

                                <span
                                    class="hidden lg:block ml-2 text-xs md:text-sm font-black uppercase tracking-tighter">
                                    {{ t.payment_type === 'recurring' ? 'Cancel' : 'Delete' }}
                                </span>
                            </button>
                        </div>
                    </div>
                </div>

                <div v-if="transactions.length === 0"
                    class="text-center bg-indigo-50 border-2 border-dashed border-indigo-200 p-12 rounded-xl">
                    <h3 class="text-xl">No Transactions found.</h3>
                    <h2 class="text-md">💸Welcome to your Wallet!</h2>
                    <p class="text-xs">Click 'Add' above to track your first expense.</p>
                </div>
            </div>
        </div>
        <!--DELETE MODAL SECTION-->
        <div v-if="isDeleteModalOpen" class="fixed inset-0 z-50 flex items-center justify-center p-4 sm:p-0">
            <div class="fixed inset-0 bg-gray-900/60 backdrop-blur-sm transition-opacity"
                @click="isDeleteModalOpen = false">
            </div>

            <div
                class="relative bg-white rounded-xl shadow-xl max-w-sm w-full p-6 overflow-hidden transform transition-all animate-in zoom-in duration-200">
                <div class="text-center">
                    <div :class="transactionToDelete?.payment_type === 'recurring' ? 'bg-amber-100' : 'bg-red-100'"
                        class="mx-auto flex items-center justify-center h-12 w-12 rounded-full mb-4">
                        <svg v-if="transactionToDelete?.payment_type === 'recurring'" class="h-6 w-6 text-amber-600"
                            fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M10 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2m7-2a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                        <svg v-else class="h-6 w-6 text-red-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                        </svg>
                    </div>

                    <h3 class="text-xl font-bold text-gray-900 mb-2">
                        {{ transactionToDelete?.payment_type === 'recurring' ? 'Stop Subscription' : 'Delete Transaction' }}
                    </h3>

                    <p class="text-sm text-gray-500 mb-6">
                        <span v-if="transactionToDelete?.payment_type === 'recurring'">
                            This will stop future auto-payments for <span class="font-bold text-gray-700">"{{
                                transactionToDelete?.description }}"</span>, but you'll keep the history.
                        </span>
                        <span v-else>
                            Are you sure you want to permanently delete <span class="font-bold text-gray-700">"{{
                                transactionToDelete?.description }}"</span>?
                        </span>
                    </p>
                </div>

                <div class="flex flex-col sm:flex-row gap-3">
                    <button @click="isDeleteModalOpen = false"
                        class="flex-1 px-4 py-2.5 bg-gray-100 hover:bg-gray-200 text-gray-700 font-semibold rounded-xl transition-colors">
                        Back
                    </button>
                    <button @click="executeAction" :class="transactionToDelete?.payment_type === 'recurring'
                        ? 'bg-amber-500 hover:bg-amber-600 shadow-amber-200'
                        : 'bg-red-600 hover:bg-red-700 shadow-red-200'"
                        class="flex-1 px-4 py-2.5 text-white font-semibold rounded-xl shadow-lg transition-colors">
                        {{ transactionToDelete?.payment_type === 'recurring' ? 'Yes, Stop it' : 'Yes, Delete' }}
                    </button>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>