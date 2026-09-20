<script setup>
import { useForm } from '@inertiajs/vue3';

// Form initialization for creating new transaction
const form = useForm({
    description: '',
    amount: null,
    type: 'expense',
    payment_type: 'manual',
    billing_day: null,
    category: 'Food'
});

// Submit new transaction
const submit = () => {
    form.post(route('transactions.store'), {
        onSuccess: () => form.reset('description', 'amount'),
        onError: () => console.log("Validation failed"),
    });
};
</script>

<template>
    <div>
        <!-- Payment Type Switcher (One-time / Subscription) -->
        <div class="flex bg-gray-100 p-1 rounded-xl mb-6 max-w-md mx-auto">
            <button type="button" @click="form.payment_type = 'manual'; form.billing_day=null;"
                :class="form.payment_type === 'manual' ? 'bg-white shadow-sm text-indigo-600' : 'text-gray-500'"
                class="flex-1 py-2 text-sm font-bold rounded-lg transition-all">
                One-time
            </button>

            <button type="button" @click="form.payment_type = 'recurring'; form.billing_day=1;"
                :class="form.payment_type === 'recurring' ? 'bg-white shadow-sm text-indigo-600' : 'text-gray-500'"
                class="flex-1 py-2 text-sm font-bold rounded-lg transition-all">
                Subscription 🔄
            </button>
        </div>

        <!-- Billing Day (Conditional for subscriptions) -->
        <div v-if="form.payment_type === 'recurring'"
            class="mb-6 p-4 bg-indigo-50 border border-indigo-100 rounded-xl animate-in fade-in slide-in-from-top-4 duration-300">
            <label class="block text-xs font-black uppercase text-indigo-400 mb-2">
                Billing Day (Every month)
            </label>
            <div class="flex items-center gap-3">
                <select v-model="form.billing_day"
                    class="w-full border-gray-200 rounded-lg focus:ring-indigo-500">
                    <option v-for="day in 28" :key="day" :value="day">Every {{ day }}. of the month</option>
                </select>
                <span class="text-xs text-indigo-400 italic font-medium">Auto-adds every month</span>
            </div>
        </div>

        <!-- Main Form -->
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
    </div>
</template>