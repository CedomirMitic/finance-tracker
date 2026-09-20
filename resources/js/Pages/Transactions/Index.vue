<script setup lang="ts">
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, usePage, router } from '@inertiajs/vue3';
import { computed, ref, onMounted, onUnmounted } from 'vue';
import { formatCurrency } from '@/Utils/formatters';
import { Transaction, PaginatedResponse, PageProps } from '@/types';
import { BankImportModal, DeleteTransactionModal, EditTransactionModal } from '@/Pages/Transactions/Modals';
import { TransactionItem, Pagination, CreateTransactionForm } from '@/Pages/Transactions/Components';

const props = defineProps<{
    transactions: PaginatedResponse<Transaction>;
    totalBalance: number;
    recurringIncome: number;
    recurringExpenses: number;
    activeSubsCount: number;
}>();

const page = usePage<PageProps>();
const isPro = computed(() => page.props.auth.user?.subscribed ?? false);
const userCurrency = computed(() => page.props.auth.user?.preferred_currency ?? 'EUR');
const recurringNet = computed(() => props.recurringIncome - props.recurringExpenses);

// Delete Modal state
const isDeleteModalOpen = ref(false);
const transactionToDelete = ref<Transaction | null>(null);

const confirmDelete = (transaction: Transaction) => {
    transactionToDelete.value = transaction;
    isDeleteModalOpen.value = true;
};

// Bank Imports Modal state
const isImportModalOpen = ref(false);

// Edit Modal state
const isEditModalOpen = ref(false);
const transactionToEdit = ref<Transaction | null>(null);

const openEditModal = (transaction: Transaction) => {
    transactionToEdit.value = transaction;
    isEditModalOpen.value = true;
};

// 1. State za loader
const isConverting = ref(false);
let pollInterval: number | null = null;

// Pomoćna funkcija za pokretanje polling-a
const startPolling = () => {
    if (pollInterval) return; // Ako već radi, ne diraj
    pollInterval = window.setInterval(checkStatus, 3000);
};

// 2. Funkcija za proveru statusa
const checkStatus = async () => {
    try {
        const response = await fetch('/user/background-status');
        const data = await response.json();

        isConverting.value = data.background_status;

        // Kada se job završi (background_status postane false, a bio je aktivan)
        if (!data.background_status) {
            if (pollInterval !== null) {
                clearInterval(pollInterval);
                pollInterval = null;
            }
            router.reload();
        }
    } catch (e) {
        console.error(e);
    }
};

// 3. Provera pri učitavanju stranice (da li je import već u toku od ranije)
onMounted(async () => {
    try {
        const response = await fetch('/user/background-status');
        const data = await response.json();

        if (data.background_status) {
            isConverting.value = true;
            startPolling(); // Pokreni polling samo ako je uvoz u toku
        } else {
            isConverting.value = false;
        }
    } catch (e) {
        console.error(e);
    }
});

// 4. Čišćenje intervala kada korisnik napusti stranicu
onUnmounted(() => {
    if (pollInterval !== null) {
        clearInterval(pollInterval);
    }
});
</script>

<template>

    <Head title="Transactions" />

    <AuthenticatedLayout>
        <template #header>
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">My Finances</h2>
        </template>

        <!-- Header Section -->
        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 bg-white p-6 shadow rounded-lg">
                <div class="flex justify-between items-center mb-6">
                    <h2 class="text-2xl font-bold text-gray-900">Transactions</h2>

                    <button @click="isImportModalOpen = true"
                        class="inline-flex items-center gap-2 px-4 py-2 bg-white border border-gray-300 hover:bg-gray-50 text-gray-700 text-sm font-semibold rounded-xl shadow-sm transition-all">
                        📁 Bank Import
                    </button>
                </div>

                <!-- CreateTransactionForm  -->
                <CreateTransactionForm />

                <!-- Total Balance Card -->
                <div class="bg-gradient-to-br from-indigo-600 to-blue-700 p-6 rounded-xl shadow-xl text-white mb-8">
                    <div class="flex justify-between items-start">
                        <div>
                            <p class="text-indigo-100 text-sm font-medium uppercase tracking-wider">Total Balance ({{
                                userCurrency }})</p>

                            <!-- LOADER ZA SALDO DOK TRAJE IMPORT -->
                            <div v-if="isConverting" class="flex items-center gap-2 mt-3 text-indigo-200">
                                <svg class="animate-spin h-6 w-6 text-white" viewBox="0 0 24 24" fill="none">
                                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor"
                                        stroke-width="4">
                                    </circle>
                                    <path class="opacity-75" fill="currentColor"
                                        d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z">
                                    </path>
                                </svg>
                                <span class="text-sm font-semibold tracking-wide">Preračunavanje u toku...</span>
                            </div>

                            <h2 v-else class="text-4xl font-bold mt-1 tracking-tight">
                                {{ formatCurrency(totalBalance, userCurrency) }}
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

                <!-- Monthly Recurring Overview -->
                <div v-if="activeSubsCount > 0"
                    class="mb-8 overflow-hidden bg-white border text-center border-slate-200 rounded-xl shadow-sm">
                    <div class="bg-slate-50 px-5 py-3 border-b border-slate-200">
                        <h3 class="text-[10px] font-black text-slate-400 uppercase tracking-[0.2em]">
                            Monthly Recurring Overview
                        </h3>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-3 divide-y md:divide-y-0 md:divide-x divide-slate-200">
                        <div class="p-5">
                            <p class="text-[10px] font-bold text-green-500 uppercase mb-1">Fixed Income</p>
                            <p class="text-xl font-black text-slate-800">{{ formatCurrency(recurringIncome,
                                userCurrency) }}</p>
                            <p class="text-xs text-slate-400 mt-1">Guaranteed monthly inflow</p>
                        </div>

                        <div class="p-5">
                            <p class="text-[10px] font-bold text-red-500 uppercase mb-1">Fixed Expenses</p>
                            <p class="text-xl font-black text-slate-800">{{ formatCurrency(recurringExpenses,
                                userCurrency) }}
                            </p>
                            <p class="text-xs text-slate-400 mt-1">Subscriptions & commitments</p>
                        </div>

                        <div class="p-5 bg-slate-50/50">
                            <p class="text-[10px] font-bold text-indigo-500 uppercase mb-1">Predictable Net</p>
                            <p class="text-xl font-black"
                                :class="recurringNet >= 0 ? 'text-indigo-600' : 'text-red-600'">
                                {{ recurringNet >= 0 ? '+' : '' }}{{ formatCurrency(recurringNet, userCurrency) }}
                            </p>
                            <p class="text-xs text-slate-400 mt-1">Left after all automated items</p>
                        </div>
                    </div>
                </div>

                <!-- Transactions List / SKELETON LOADER -->
                <div class="space-y-3">
                    <div v-if="isConverting" class="space-y-3">
                        <div class="animate-pulse bg-gray-100 h-16 rounded-xl w-full"></div>
                        <div class="animate-pulse bg-gray-100 h-16 rounded-xl w-full opacity-75"></div>
                        <div class="animate-pulse bg-gray-100 h-16 rounded-xl w-full opacity-50"></div>
                    </div>

                    <template v-else>
                        <TransactionItem v-for="t in transactions.data" :key="t.id" :transaction="t"
                            @edit="openEditModal" @delete="confirmDelete" />
                    </template>
                </div>

                <!-- Pagination Links  -->
                <Pagination v-if="!isConverting" :links="transactions.links" />

                <!-- Empty State -->
                <div v-if="!isConverting && transactions.data.length === 0"
                    class="text-center bg-indigo-50 border-2 border-dashed border-indigo-200 p-12 rounded-xl mt-4">
                    <h3 class="text-xl font-bold text-gray-800">No Transactions found.</h3>
                    <h2 class="text-md text-indigo-600 font-semibold mt-1">💸 Welcome to your Wallet!</h2>
                    <p class="text-xs text-gray-500 mt-1">Click 'Add' above to track your first expense.</p>
                </div>
            </div>
        </div>

        <!-- Modals -->
        <DeleteTransactionModal :show="isDeleteModalOpen" :transactionToDelete="transactionToDelete"
            @close="isDeleteModalOpen = false" />

        <BankImportModal :show="isImportModalOpen" :isPro="isPro" @close="isImportModalOpen = false"
            @import_started="() => { isConverting = true; startPolling(); }" />

        <EditTransactionModal :show="isEditModalOpen" :transactionToEdit="transactionToEdit"
            @close="isEditModalOpen = false" />

    </AuthenticatedLayout>
</template>