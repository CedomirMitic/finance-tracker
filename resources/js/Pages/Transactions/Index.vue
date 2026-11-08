<script setup lang="ts">
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, usePage, router } from '@inertiajs/vue3';
import { computed, ref, onMounted, onUnmounted } from 'vue';
import { formatCurrency } from '@/Utils/formatters';
import { Transaction, PaginatedResponse, PageProps } from '@/types';
import { BankImportModal, DeleteTransactionModal, EditTransactionModal, CreateTransactionModal } from '@/Pages/Transactions/Modals';
import { TransactionItem, Pagination } from '@/Pages/Transactions/Components';
import ApplicationLogo from '@/Components/ApplicationLogo.vue';

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

//Create Transaction Modal state
const isCreateModalOpen = ref(false);

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

// State for loader
const isConverting = ref(false);
let pollInterval: number | null = null;

// Helper function for poolling
const startPolling = () => {
    if (pollInterval) return;
    pollInterval = window.setInterval(checkStatus, 3000);
};

// Function to check status in database
const checkStatus = async () => {
    try {
        const response = await fetch('/user/background-status');
        const data = await response.json();

        isConverting.value = data.background_status;


        if (!data.background_status) {
            if (pollInterval !== null) {
                clearInterval(pollInterval);
                pollInterval = null;
            }
            router.reload();
        }
    } catch (e) {
    }
};

// Check on page loading
onMounted(async () => {
    try {
        const response = await fetch('/user/background-status');
        const data = await response.json();

        if (data.background_status) {
            isConverting.value = true;
            startPolling();
        } else {
            isConverting.value = false;
        }
    } catch (e) {
    }
});

// Clear interval if user exists page
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
            <div class="font-sans">
                <h2 class="font-black text-xl sm:text-2xl text-slate-900 tracking-tight">Transactions</h2>
                <p class="text-xs text-slate-400 mt-0.5 font-medium">View, edit, and manage all your income and expense records.</p>
            </div>
        </template>

        <!-- Header Section -->
        <div>
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 bg-white p-8 shadow-sm rounded-3xl border border-slate-100">
                <!-- Quick Actions Block -->
                <div class="mb-8 bg-slate-50/50 p-6 rounded-3xl border border-slate-100">
                    <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4 mb-6">
                        <div>
                            <h3 class="text-xs font-black uppercase tracking-widest text-slate-400">Quick Actions</h3>
                        </div>
                    </div>

                    <!-- Dugmići: na mobilnom ide jedan ispod drugog preko cele širine, na desktopu jedno pored drugog -->
                    <div class="flex flex-col sm:flex-row items-center gap-3">
                        <button @click="isCreateModalOpen = true"
                            class="w-full sm:w-auto inline-flex items-center justify-center gap-2 px-6 py-3.5 bg-emerald-600 hover:bg-emerald-700 text-white text-sm font-bold rounded-2xl shadow-lg shadow-emerald-600/25 transition-all">
                            + New Transaction
                        </button>

                        <button @click="isImportModalOpen = true"
                            class="w-full sm:w-auto inline-flex items-center justify-center gap-2 px-5 py-3.5 bg-white border border-slate-200 hover:bg-slate-50 text-slate-700 text-sm font-bold rounded-2xl shadow-sm transition-all">
                            📁 Bank Import
                        </button>
                    </div>
                </div>

                <!-- Create Transaction Modal -->
                <CreateTransactionModal :show="isCreateModalOpen" @close="isCreateModalOpen = false" />

                <!-- Total Balance Card (Emerald Theme) -->
                <div
                    class="bg-gradient-to-br from-emerald-600 to-teal-700 p-8 rounded-3xl shadow-xl shadow-emerald-600/20 text-white mb-8 relative overflow-hidden">
                    <div
                        class="absolute -right-6 -bottom-6 w-32 h-32 bg-white/10 rounded-full blur-2xl pointer-events-none">
                    </div>
                    <div class="flex justify-between items-start relative z-10">
                        <div>
                            <p class="text-emerald-100 text-xs font-black uppercase tracking-widest">Total Balance ({{
                                userCurrency }})</p>

                            <div v-if="isConverting" class="flex items-center gap-2 mt-3 text-emerald-100">
                                <svg class="animate-spin h-6 w-6 text-white" viewBox="0 0 24 24" fill="none">
                                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor"
                                        stroke-width="4">
                                    </circle>
                                    <path class="opacity-75" fill="currentColor"
                                        d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z">
                                    </path>
                                </svg>
                                <span class="text-sm font-semibold tracking-wide">Processing...</span>
                            </div>

                            <h2 v-else class="text-4xl font-black mt-2 tracking-tight">
                                {{ formatCurrency(totalBalance, userCurrency) }}
                            </h2>
                        </div>
                        <ApplicationLogo class="hidden sm:flex brightness-0 invert opacity-90 scale-110" />
                    </div>
                    <div class="mt-6 flex gap-4 text-sm relative z-10">
                        <div class="flex items-center bg-white/10 px-3 py-1 rounded-xl backdrop-blur-sm">
                            <span class="w-2 h-2 bg-emerald-300 rounded-full mr-2"></span>
                            <span class="font-semibold text-xs">Active Account</span>
                        </div>
                    </div>
                </div>

                <!-- Monthly Recurring Overview -->
                <div v-if="activeSubsCount > 0"
                    class="mb-8 overflow-hidden bg-white border text-center border-slate-100 rounded-3xl shadow-sm">
                    <div class="bg-slate-50/80 px-5 py-4 border-b border-slate-100">
                        <h3 class="text-[10px] font-black text-slate-400 uppercase tracking-[0.2em]">
                            Monthly Recurring Overview
                        </h3>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-3 divide-y md:divide-y-0 md:divide-x divide-slate-100">
                        <div class="p-6">
                            <p class="text-[10px] font-black text-emerald-600 uppercase tracking-wider mb-1">Fixed
                                Income</p>
                            <p class="text-xl font-black text-slate-800">{{ formatCurrency(recurringIncome,
                                userCurrency) }}</p>
                            <p class="text-xs text-slate-400 mt-1">Guaranteed monthly inflow</p>
                        </div>

                        <div class="p-6">
                            <p class="text-[10px] font-black text-red-500 uppercase tracking-wider mb-1">Fixed Expenses
                            </p>
                            <p class="text-xl font-black text-slate-800">{{ formatCurrency(recurringExpenses,
                                userCurrency) }}
                            </p>
                            <p class="text-xs text-slate-400 mt-1">Subscriptions & commitments</p>
                        </div>

                        <div class="p-6 bg-slate-50/50">
                            <p class="text-[10px] font-black text-emerald-600 uppercase tracking-wider mb-1">Predictable
                                Net</p>
                            <p class="text-xl font-black"
                                :class="recurringNet >= 0 ? 'text-emerald-700' : 'text-red-600'">
                                {{ recurringNet >= 0 ? '+' : '' }}{{ formatCurrency(recurringNet, userCurrency) }}
                            </p>
                            <p class="text-xs text-slate-400 mt-1">Left after all automated items</p>
                        </div>
                    </div>
                </div>

                <!-- Transactions List / SKELETON LOADER -->
                <div class="space-y-3">
                    <div v-if="isConverting" class="space-y-3">
                        <div class="animate-pulse bg-slate-100 h-16 rounded-2xl w-full"></div>
                        <div class="animate-pulse bg-slate-100 h-16 rounded-2xl w-full opacity-75"></div>
                        <div class="animate-pulse bg-slate-100 h-16 rounded-2xl w-full opacity-50"></div>
                    </div>

                    <template v-else>
                        <TransactionItem v-for="t in transactions.data" :key="t.id" :transaction="t"
                            @edit="openEditModal" @delete="confirmDelete" />
                    </template>
                </div>

                <!-- Pagination Links -->
                <Pagination v-if="!isConverting" :links="transactions.links" />

                <!-- Empty State -->
                <div v-if="!isConverting && transactions.data.length === 0"
                    class="text-center bg-emerald-50/50 border-2 border-dashed border-emerald-200 p-12 rounded-3xl mt-6">
                    <h3 class="text-xl font-black text-slate-800">No Transactions found.</h3>
                    <h2 class="text-md text-emerald-600 font-bold mt-1">💸 Welcome to your Wallet!</h2>
                    <p class="text-xs text-slate-500 mt-1">Click 'Add' above to track your first expense.</p>
                </div>
            </div>
        </div>

        <!-- Modals -->

        <!-- Create Transaction Modal Component -->
        <CreateTransactionModal :show="isCreateModalOpen" @close="isCreateModalOpen = false" />

        <DeleteTransactionModal :show="isDeleteModalOpen" :transactionToDelete="transactionToDelete"
            @close="isDeleteModalOpen = false" />

        <BankImportModal :show="isImportModalOpen" :isPro="isPro" @close="isImportModalOpen = false"
            @import_started="() => { isConverting = true; startPolling(); }" />

        <EditTransactionModal :show="isEditModalOpen" :transactionToEdit="transactionToEdit"
            @close="isEditModalOpen = false" />

    </AuthenticatedLayout>
</template>