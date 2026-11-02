<script setup lang="ts">
import { watch } from 'vue';
import { useForm } from '@inertiajs/vue3';
import { Transaction } from '@/types';

const props = defineProps<{
    show: boolean;
    transactionToEdit: Transaction | null;
}>();

const emit = defineEmits(['close']);

const editForm = useForm({
    description: '',
    amount: 0 as number | null,
    type: 'expense' as 'income' | 'expense',
    payment_type: 'manual' as 'manual' | 'recurring' | undefined,
    billing_day: 1 as number | null,
    category: 'Food'
});

// Watch when the modal opens to fill out the form
watch(() => props.transactionToEdit, (transaction) => {
    if (transaction) {
        editForm.description = transaction.description;
        editForm.amount = Number(transaction.amount);
        editForm.type = transaction.type;
        editForm.payment_type = transaction.payment_type;
        editForm.billing_day = transaction.billing_day || 1;
        editForm.category = transaction.category;
    }
}, { immediate: true });

watch(() => editForm.payment_type, (newType) => {
    if (newType === 'manual') {
        editForm.billing_day = null;
    }
});

const updateTransaction = () => {
    if (!props.transactionToEdit) return;

    editForm.put(route('transactions.update', props.transactionToEdit.id), {
        onSuccess: () => {
            emit('close');
            editForm.reset();
        },
    });
};
</script>

<template>
    <div v-if="show" class="fixed inset-0 z-50 flex items-center justify-center p-4 sm:p-0 font-sans">
        <div class="fixed inset-0 bg-slate-900/60 backdrop-blur-sm transition-opacity"
            @click="!editForm.processing && emit('close')">
        </div>

        <div
            class="relative bg-white rounded-3xl shadow-2xl max-w-lg w-full p-8 overflow-hidden transform transition-all border border-slate-100">
            <div class="flex justify-between items-center mb-6">
                <h3 class="text-xl font-black text-slate-900 tracking-tight">Edit Transaction</h3>
                <button @click="emit('close')" class="text-slate-400 hover:text-slate-600 font-bold p-1 rounded-xl hover:bg-slate-100 transition-colors">✕</button>
            </div>

            <form @submit.prevent="updateTransaction" class="space-y-4">
                <div>
                    <label class="block text-xs font-black text-slate-400 uppercase tracking-wider mb-1.5">Description</label>
                    <input v-model="editForm.description" type="text"
                        class="w-full border-slate-200 rounded-2xl focus:border-emerald-500 focus:ring-emerald-500 text-slate-800 font-medium py-3">
                    <span v-if="editForm.errors.description" class="text-red-500 text-xs mt-1 font-semibold">
                        {{ editForm.errors.description }}
                    </span>
                </div>

                <div>
                    <label class="block text-xs font-black text-slate-400 uppercase tracking-wider mb-1.5">Amount</label>
                    <input v-model="editForm.amount" type="number" step="0.01"
                        class="w-full border-slate-200 rounded-2xl focus:border-emerald-500 focus:ring-emerald-500 text-slate-800 font-medium py-3">
                    <span v-if="editForm.errors.amount" class="text-red-500 text-xs mt-1 font-semibold">
                        {{ editForm.errors.amount }}
                    </span>
                </div>

                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-xs font-black text-slate-400 uppercase tracking-wider mb-1.5">Type</label>
                        <select v-model="editForm.type" class="w-full border-slate-200 rounded-2xl focus:border-emerald-500 focus:ring-emerald-500 text-slate-800 font-medium py-3">
                            <option value="expense">Expense (-)</option>
                            <option value="income">Income (+)</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-xs font-black text-slate-400 uppercase tracking-wider mb-1.5">Category</label>
                        <select v-model="editForm.category" class="w-full border-slate-200 rounded-2xl focus:border-emerald-500 focus:ring-emerald-500 text-slate-800 font-medium py-3">
                            <option value="Salary">Salary</option>
                            <option value="Food">Food</option>
                            <option value="Transport">Transport</option>
                            <option value="Bills">Bills</option>
                            <option value="Entertainment">Entertainment</option>
                            <option value="Other">Other</option>
                        </select>
                    </div>
                </div>

                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-xs font-black text-slate-400 uppercase tracking-wider mb-1.5">Payment Type</label>
                        <select v-model="editForm.payment_type" class="w-full border-slate-200 rounded-2xl focus:border-emerald-500 focus:ring-emerald-500 text-slate-800 font-medium py-3">
                            <option value="manual">Manual</option>
                            <option value="recurring">Recurring</option>
                        </select>
                    </div>
                    <div v-if="editForm.payment_type === 'recurring'">
                        <label class="block text-xs font-black text-emerald-600 uppercase tracking-wider mb-1.5">Billing Day</label>
                        <select v-model="editForm.billing_day" class="w-full border-slate-200 rounded-2xl focus:border-emerald-500 focus:ring-emerald-500 text-slate-800 font-medium py-3">
                            <option v-for="day in 28" :key="day" :value="day">Every {{ day }}. of the month</option>
                        </select>
                    </div>
                </div>

                <div class="flex justify-end gap-3 mt-8">
                    <button type="button" @click="emit('close')" :disabled="editForm.processing"
                        class="px-5 py-3 bg-slate-100 hover:bg-slate-200 text-slate-700 font-bold rounded-2xl transition-colors">
                        Cancel
                    </button>
                    <button type="submit" :disabled="editForm.processing"
                        class="px-6 py-3 bg-emerald-600 hover:bg-emerald-700 text-white font-bold rounded-2xl shadow-lg shadow-emerald-600/20 transition-all flex items-center gap-2">
                        <span v-if="editForm.processing">Saving...</span>
                        <span v-else>Save Changes</span>
                    </button>
                </div>
            </form>
        </div>
    </div>
</template>