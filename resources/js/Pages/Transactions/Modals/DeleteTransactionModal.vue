<script setup lang="ts">
import { ref } from 'vue';
import { router } from '@inertiajs/vue3';
import { Transaction } from '@/types';

const props = defineProps<{
    show: boolean;
    transactionToDelete: Transaction | null;
}>();

const emit = defineEmits(['close']);
const isDeleting = ref(false);

const executeAction = () => {
    if (!props.transactionToDelete) return;

    isDeleting.value = true;

    if (props.transactionToDelete.payment_type === 'recurring') {
        router.patch(route('transactions.cancel', props.transactionToDelete.id), {}, {
            onFinish: () => {
                isDeleting.value = false;
                emit('close');
            }
        });
    } else {
        router.delete(route('transactions.destroy', props.transactionToDelete.id), {
            onFinish: () => {
                isDeleting.value = false;
                emit('close');
            }
        });
    }
};
</script>

<template>
    <div v-if="show" class="fixed inset-0 z-50 flex items-center justify-center p-4 sm:p-0">
        <div class="fixed inset-0 bg-gray-900/60 backdrop-blur-sm transition-opacity"
            @click="!isDeleting && $emit('close')">
        </div>

        <div class="relative bg-white rounded-xl shadow-xl max-w-sm w-full p-6 overflow-hidden transform transition-all">
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
                <button @click="$emit('close')" :disabled="isDeleting"
                    class="flex-1 px-4 py-2.5 bg-gray-100 hover:bg-gray-200 text-gray-700 font-semibold rounded-xl transition-colors disabled:opacity-50">
                    Back
                </button>
                <button @click="executeAction" :disabled="isDeleting" :class="transactionToDelete?.payment_type === 'recurring'
                    ? 'bg-amber-500 hover:bg-amber-600 shadow-amber-200'
                    : 'bg-red-600 hover:bg-red-700 shadow-red-200'"
                    class="flex-1 px-4 py-2.5 text-white font-semibold rounded-xl shadow-lg transition-colors flex items-center justify-center gap-2 disabled:opacity-50">

                    <svg v-if="isDeleting" class="animate-spin h-5 w-5 text-white"
                        xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4">
                        </circle>
                        <path class="opacity-75" fill="currentColor"
                            d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z">
                        </path>
                    </svg>

                    <span>
                        {{ isDeleting ? 'Processing...' : (transactionToDelete?.payment_type === 'recurring' ?
                            'Yes, Stop It'
                            : 'Yes, Delete') }}
                    </span>
                </button>
            </div>
        </div>
    </div>
</template>