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
    <div v-if="show" class="fixed inset-0 z-50 flex items-center justify-center p-4 sm:p-0">
        <div class="fixed inset-0 bg-gray-900/60 backdrop-blur-sm transition-opacity"
            @click="!editForm.processing && emit('close')">
        </div>

        <div
            class="relative bg-white rounded-xl shadow-xl max-w-lg w-full p-6 overflow-hidden transform transition-all">
            <div class="flex justify-between items-center mb-4">
                <h3 class="text-xl font-bold text-gray-900">Edit Transaction</h3>
                <button @click="emit('close')" class="text-gray-400 hover:text-gray-600 font-bold">✕</button>
            </div>

            <form @submit.prevent="updateTransaction" class="space-y-4">
                <div>
                    <label class="block text-xs font-bold text-gray-600 uppercase mb-1">Description</label>
                    <input v-model="editForm.description" type="text"
                        class="w-full border-gray-300 rounded-lg focus:ring-indigo-500">
                    <span v-if="editForm.errors.description" class="text-red-500 text-xs mt-1 font-semibold">
                        {{ editForm.errors.description }}
                    </span>
                </div>

                <div>
                    <label class="block text-xs font-bold text-gray-600 uppercase mb-1">Amount</label>
                    <input v-model="editForm.amount" type="number" step="0.01"
                        class="w-full border-gray-300 rounded-lg focus:ring-indigo-500">
                    <span v-if="editForm.errors.amount" class="text-red-500 text-xs mt-1 font-semibold">
                        {{ editForm.errors.amount }}
                    </span>
                </div>

                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-xs font-bold text-gray-600 uppercase mb-1">Type</label>
                        <select v-model="editForm.type" class="w-full border-gray-300 rounded-lg">
                            <option value="expense">Expense (-)</option>
                            <option value="income">Income (+)</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-gray-600 uppercase mb-1">Category</label>
                        <select v-model="editForm.category" class="w-full border-gray-300 rounded-lg">
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
                        <label class="block text-xs font-bold text-gray-600 uppercase mb-1">Payment Type</label>
                        <select v-model="editForm.payment_type" class="w-full border-gray-300 rounded-lg">
                            <option value="manual">Manual</option>
                            <option value="recurring">Recurring</option>
                        </select>
                    </div>
                    <div v-if="editForm.payment_type === 'recurring'">
                        <label class="block text-xs font-bold text-indigo-500 uppercase mb-1">Billing Day</label>
                        <select v-model="editForm.billing_day" class="w-full border-gray-300 rounded-lg">
                            <option v-for="day in 28" :key="day" :value="day">Every {{ day }}. of the month</option>
                        </select>
                    </div>
                </div>

                <div class="flex justify-end gap-3 mt-6">
                    <button type="button" @click="emit('close')" :disabled="editForm.processing"
                        class="px-4 py-2 bg-gray-100 hover:bg-gray-200 text-gray-700 font-semibold rounded-xl transition-colors">
                        Cancel
                    </button>
                    <button type="submit" :disabled="editForm.processing"
                        class="px-4 py-2 bg-indigo-600 hover:bg-indigo-700 text-white font-semibold rounded-xl shadow-lg transition-colors flex items-center gap-2">
                        <span v-if="editForm.processing">Saving...</span>
                        <span v-else>Save Changes</span>
                    </button>
                </div>
            </form>
        </div>
    </div>
</template>