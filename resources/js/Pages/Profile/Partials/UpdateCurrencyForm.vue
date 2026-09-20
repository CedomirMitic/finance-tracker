<script setup>
import InputLabel from '@/Components/InputLabel.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import { useForm, usePage } from '@inertiajs/vue3';

defineProps({
    currencies: {
        type: Array,
        required: true,
    },
});

const user = usePage().props.auth.user;

const form = useForm({
    preferred_currency: user.preferred_currency || 'EUR',
});

const updateCurrency = () => {
    form.patch(route('profile.currency.update'), {
        preserveScroll: true,
    });
};
</script>

<template>
    <section>
        <header>
            <h2 class="text-lg font-medium text-gray-950">Preferred Currency</h2>
            <p class="mt-1 text-sm text-gray-600">
                Update your account's preferred base currency for tracking and dashboard display.
            </p>
        </header>

        <form @submit.prevent="updateCurrency" class="mt-6 space-y-6">
            <div>
                <InputLabel for="preferred_currency" value="Currency" />

                <select
                    id="preferred_currency"
                    v-model="form.preferred_currency"
                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
                >
                    <option v-for="curr in currencies" :key="curr.value" :value="curr.value">
                        {{ curr.label }}
                    </option>
                </select>

                <div v-if="form.errors.preferred_currency" class="text-red-600 text-sm mt-2">
                    {{ form.errors.preferred_currency }}
                </div>
            </div>

            <div class="flex items-center gap-4">
                <PrimaryButton :disabled="form.processing">Save</PrimaryButton>

                <Transition
                    enter-active-class="transition ease-in-out"
                    enter-from-class="opacity-0"
                    leave-active-class="transition ease-in-out"
                    leave-to-class="opacity-0"
                >
                    <p v-if="form.recentlySuccessful" class="text-sm text-gray-600">Saved.</p>
                </Transition>
            </div>
        </form>
    </section>
</template>