<script setup lang="ts">
import { formatCurrency } from '@/Utils/formatters';
import { usePage } from '@inertiajs/vue3';
import { computed } from 'vue';
import { PageProps } from '@/types';

defineProps({
    transaction: {
        type: Object,
        required: true
    }
})

defineEmits(['edit', 'delete'])

const page = usePage<PageProps>();

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

const formatDate = (dateString: string) => {
    if (!dateString) return '';
    const date = new Date(dateString);
    return date.toLocaleDateString(undefined, {
        day: 'numeric',
        month: 'short',
        year: 'numeric',
    });
};
</script>

<template>
    <div class="flex items-center justify-between p-3 md:p-5 bg-white rounded-xl shadow-sm border border-gray-100 hover:shadow-md transition-shadow">
        <div class="flex items-center gap-3 md:gap-6 min-w-0">
            <div class="p-2 bg-gray-50 rounded-full w-10 h-10 md:w-16 md:h-16 flex-shrink-0 flex items-center justify-center text-md md:text-xl">
                {{ getCategoryIcon(transaction.category) }}
            </div>

            <div class="min-w-0">
                <p class="font-bold text-gray-800 text-sm md:text-xl truncate leading-tight">
                    {{ transaction.description }}
                </p>
                <div class="flex items-center gap-2">
                    <p class="text-gray-400 font-medium uppercase tracking-wider text-[10px] md:text-xs">
                        {{ transaction.category }}
                    </p>
                    <span v-if="transaction.payment_type === 'recurring'"
                        class="text-[8px] md:text-xs text-indigo-500 bg-indigo-50 px-2 py-0.5 rounded-full whitespace-nowrap">
                        🔄 <span class="hidden sm:inline">Every</span> {{ transaction.billing_day }}.
                    </span>
                </div>
            </div>
        </div>

        <div class="flex items-center gap-2 md:gap-4 flex-shrink-0 ml-4">
            <div class="text-right">
                <p :class="transaction.type === 'income' ? 'text-green-600' : 'text-gray-900'"
                    class="font-extrabold text-sm md:text-xl whitespace-nowrap">
                    {{ transaction.type === 'income' ? '+' : '-' }}{{ formatCurrency(Number(transaction.amount), transaction.currency) }}
                </p>
                <p class="text-gray-400 text-[10px] md:text-sm">{{ formatDate(transaction.created_at) }}</p>
            </div>

            <!-- EDIT BUTTON -->
            <button @click="$emit('edit', transaction)"
                class="flex items-center justify-center p-2 md:px-3 md:py-2 rounded-lg bg-gray-50 text-gray-500 hover:bg-indigo-50 hover:text-indigo-600 transition-all"
                title="Edit Transaction">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 md:h-5 md:w-5" fill="none"
                    viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                </svg>
            </button>

            <!-- DELETE / CANCEL BUTTON -->
            <button @click="$emit('delete', transaction)"
                class="flex items-center justify-center p-2 md:px-4 md:py-2 rounded-lg transition-all"
                :class="transaction.payment_type === 'recurring'
                    ? 'bg-amber-50 text-amber-600 hover:bg-amber-100'
                    : 'bg-gray-50 text-gray-400 hover:bg-red-50 hover:text-red-600'">

                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 md:h-6 md:w-6" fill="none"
                    viewBox="0 0 24 24" stroke="currentColor">
                    <path v-if="transaction.payment_type !== 'recurring'" stroke-linecap="round"
                        stroke-linejoin="round" stroke-width="2"
                        d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                    <path v-else stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M10 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2m7-2a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>

                <span class="hidden lg:block ml-2 text-xs md:text-sm font-black uppercase tracking-tighter">
                    {{ transaction.payment_type === 'recurring' ? 'Cancel' : 'Delete' }}
                </span>
            </button>
        </div>
    </div>
</template>