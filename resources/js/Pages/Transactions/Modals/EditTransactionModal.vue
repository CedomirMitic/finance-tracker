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

const fillForm = () => {
    const t = props.transactionToEdit;
    if (!t) return;

    editForm.description = t.description;
    editForm.amount = Number(t.amount);
    editForm.type = t.type;
    editForm.payment_type = t.payment_type;
    editForm.billing_day = t.billing_day || 1;
    editForm.category = t.category;
    editForm.clearErrors();
};

// Watch when the modal opens to fill out the form
watch(() => [props.show, props.transactionToEdit], () => {
    if (props.show) fillForm();
}, { immediate: true });


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
                <button @click="emit('close')"
                    class="text-slate-400 hover:text-slate-600 font-bold p-1 rounded-xl hover:bg-slate-100 transition-colors">✕</button>
            </div>

            <form @submit.prevent="updateTransaction" class="space-y-4">
                <div>
                    <label
                        class="block text-xs font-black text-slate-400 uppercase tracking-wider mb-1.5">Description</label>
                    <input v-model="editForm.description" type="text"
                        class="w-full border-slate-200 rounded-2xl focus:border-emerald-500 focus:ring-emerald-500 text-slate-800 font-medium py-3">
                    <span v-if="editForm.errors.description" class="text-red-500 text-xs mt-1 font-semibold">
                        {{ editForm.errors.description }}
                    </span>
                </div>

                <div>
                    <label
                        class="block text-xs font-black text-slate-400 uppercase tracking-wider mb-1.5">Amount</label>
                    <input v-model="editForm.amount" type="number" step="0.01"
                        class="w-full border-slate-200 rounded-2xl focus:border-emerald-500 focus:ring-emerald-500 text-slate-800 font-medium py-3">
                    <span v-if="editForm.errors.amount" class="text-red-500 text-xs mt-1 font-semibold">
                        {{ editForm.errors.amount }}
                    </span>
                </div>

                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label
                            class="block text-xs font-black text-slate-400 uppercase tracking-wider mb-1.5">Type</label>
                        <select v-model="editForm.type"
                            class="w-full border-slate-200 rounded-2xl focus:border-emerald-500 focus:ring-emerald-500 text-slate-800 font-medium py-3">
                            <option value="expense">Expense (-)</option>
                            <option value="income">Income (+)</option>
                        </select>
                    </div>
                    <div>
                        <label
                            class="block text-xs font-black text-slate-400 uppercase tracking-wider mb-1.5">Category</label>
                        <select v-model="editForm.category"
                            class="w-full border-slate-200 rounded-2xl focus:border-emerald-500 focus:ring-emerald-500 text-slate-800 font-medium py-3">
                            <option value="Salary">Salary</option>
                            <option value="Food">Food</option>
                            <option value="Transport">Transport</option>
                            <option value="Bills">Bills</option>
                            <option value="Entertainment">Entertainment</option>
                            <option value="Other">Other</option>
                        </select>
                    </div>
                </div>

                <p class="text-xs text-slate-400 font-medium">
                    {{ editForm.payment_type === 'recurring'
                        ? '🔄 Recurring: changes apply to future charges only.'
                        : 'One-time transaction.' }}
                </p>

                <div v-if="editForm.payment_type === 'recurring'">
                    <label class="block text-xs font-black text-slate-400 uppercase tracking-wider mb-1.5">Billing
                        Day</label>
                    <select v-model="editForm.billing_day"
                        class="w-full border-slate-200 rounded-2xl focus:border-emerald-500 focus:ring-emerald-500 text-slate-800 font-medium py-3">
                        <option v-for="day in 28" :key="day" :value="day">Day {{ day }}</option>
                    </select>
                    <span v-if="editForm.errors.billing_day" class="text-red-500 text-xs mt-1 font-semibold">
                        {{ editForm.errors.billing_day }}
                    </span>
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