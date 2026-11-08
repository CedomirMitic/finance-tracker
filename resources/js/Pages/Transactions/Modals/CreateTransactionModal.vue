<script setup lang="ts">
import { computed } from 'vue';
import { useForm, usePage } from '@inertiajs/vue3';

const props = defineProps<{
    show: boolean;
}>();

const emit = defineEmits(['close']);

const form = useForm({
    description: '',
    amount: null as number | null,
    type: 'expense' as 'income' | 'expense',
    payment_type: 'manual' as 'manual' | 'recurring',
    billing_day: 1 as number | null,
    category: 'Food'
});

const page = usePage();
const userCurrency = computed(() => (page.props as any).userCurrency || 'EUR');

const submit = () => {
    form.post(route('transactions.store'), {
        onSuccess: () => {
            form.reset();
            emit('close');
        },
    });
};
</script>

<template>
    <div v-if="show" class="fixed inset-0 z-50 flex items-center justify-center p-4 sm:p-0 font-sans">
        <!-- Backdrop -->
        <div class="fixed inset-0 bg-slate-900/60 backdrop-blur-sm transition-opacity"
            @click="!form.processing && emit('close')">
        </div>

        <!-- Modal Box -->
        <div class="relative bg-white rounded-3xl shadow-2xl max-w-lg w-full p-8 overflow-hidden transform transition-all border border-slate-100 z-10">
            <div class="flex justify-between items-center mb-6">
                <div>
                    <h3 class="text-xl font-black text-slate-900 tracking-tight">New Transaction</h3>
                    <p class="text-xs text-slate-400 mt-0.5 font-medium">Add a new income or expense item.</p>
                </div>
                <button @click="emit('close')" class="text-slate-400 hover:text-slate-600 font-bold p-1.5 rounded-xl hover:bg-slate-100 transition-colors">✕</button>
            </div>

            <!-- Payment Type Switcher (One-time / Subscription) -->
            <div class="flex bg-slate-100 p-1.5 rounded-2xl mb-6">
                <button type="button" @click="form.payment_type = 'manual'; form.billing_day = null;"
                    :class="form.payment_type === 'manual' ? 'bg-white shadow-sm text-slate-900 font-bold' : 'text-slate-500 font-medium'"
                    class="flex-1 py-2.5 text-xs rounded-xl transition-all">
                    One-time
                </button>

                <button type="button" @click="form.payment_type = 'recurring'; form.billing_day = 1;"
                    :class="form.payment_type === 'recurring' ? 'bg-white shadow-sm text-slate-900 font-bold' : 'text-slate-500 font-medium'"
                    class="flex-1 py-2.5 text-xs rounded-xl transition-all flex items-center justify-center gap-1.5">
                    <span>Subscription</span> <span>🔄</span>
                </button>
            </div>

            <!-- Billing Day (Conditional for subscriptions) -->
            <div v-if="form.payment_type === 'recurring'"
                class="mb-6 p-4 bg-emerald-50/60 border border-emerald-100 rounded-2xl">
                <label class="block text-xs font-black uppercase text-emerald-800 mb-2">
                    Billing Day (Every month)
                </label>
                <div class="flex items-center gap-3">
                    <select v-model="form.billing_day"
                        class="w-full border-slate-200 rounded-2xl focus:border-emerald-500 focus:ring-emerald-500 text-sm py-2.5 text-slate-800">
                        <option v-for="day in 28" :key="day" :value="day">Every {{ day }}. of the month</option>
                    </select>
                </div>
                <span class="text-[11px] text-emerald-600 font-semibold block mt-1.5">Auto-adds to your balance every month</span>
            </div>

            <!-- Main Form Fields -->
            <form @submit.prevent="submit" class="space-y-4">
                <div>
                    <label class="block text-xs font-black text-slate-400 uppercase tracking-wider mb-1.5">Description</label>
                    <input v-model="form.description" type="text" placeholder="e.g. Grocery store, Salary..."
                        class="w-full border-slate-200 rounded-2xl focus:border-emerald-500 focus:ring-emerald-500 text-slate-800 font-medium py-3">
                    <span v-if="form.errors.description" class="text-red-500 text-xs mt-1 font-semibold block">
                        {{ form.errors.description }}
                    </span>
                </div>

                <div>
                    <label class="block text-xs font-black text-slate-400 uppercase tracking-wider mb-1.5">Amount ({{ userCurrency }})</label>
                    <input v-model="form.amount" type="number" step="0.01" placeholder="0.00"
                        class="w-full border-slate-200 rounded-2xl focus:border-emerald-500 focus:ring-emerald-500 text-slate-800 font-medium py-3">
                    <span v-if="form.errors.amount" class="text-red-500 text-xs mt-1 font-semibold block">
                        {{ form.errors.amount }}
                    </span>
                </div>

                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-xs font-black text-slate-400 uppercase tracking-wider mb-1.5">Type</label>
                        <select v-model="form.type" class="w-full border-slate-200 rounded-2xl focus:border-emerald-500 focus:ring-emerald-500 text-slate-800 font-medium py-3 text-sm">
                            <option value="expense">Expense (-)</option>
                            <option value="income">Income (+)</option>
                        </select>
                    </div>

                    <div>
                        <label class="block text-xs font-black text-slate-400 uppercase tracking-wider mb-1.5">Category</label>
                        <select v-model="form.category" class="w-full border-slate-200 rounded-2xl focus:border-emerald-500 focus:ring-emerald-500 text-slate-800 font-medium py-3 text-sm">
                            <option value="Salary">Salary</option>
                            <option value="Food">Food</option>
                            <option value="Transport">Transport</option>
                            <option value="Bills">Bills</option>
                            <option value="Entertainment">Entertainment</option>
                            <option value="Other">Other</option>
                        </select>
                    </div>
                </div>

                <div class="flex justify-end gap-3 mt-8">
                    <button type="button" @click="emit('close')" :disabled="form.processing"
                        class="px-5 py-3 bg-slate-100 hover:bg-slate-200 text-slate-700 font-bold rounded-2xl text-sm transition-colors">
                        Cancel
                    </button>
                    <button type="submit" :disabled="form.processing"
                        class="px-6 py-3 text-white font-bold rounded-2xl text-sm shadow-lg transition-all flex items-center gap-2"
                        :class="form.type === 'income' ? 'bg-emerald-600 hover:bg-emerald-700 shadow-emerald-600/25' : 'bg-slate-900 hover:bg-slate-800 shadow-slate-900/20'">
                        <span v-if="form.processing">Saving...</span>
                        <span v-else>{{ form.type === 'income' ? 'Save Income' : 'Save Expense' }}</span>
                    </button>
                </div>
            </form>
        </div>
    </div>
</template>