<script setup>
import Checkbox from '@/Components/Checkbox.vue';
import GuestLayout from '@/Layouts/GuestLayout.vue';
import InputError from '@/Components/InputError.vue';
import InputLabel from '@/Components/InputLabel.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import TextInput from '@/Components/TextInput.vue';
import { Head, Link, useForm } from '@inertiajs/vue3';

defineProps({
    canResetPassword: {
        type: Boolean,
    },
    status: {
        type: String,
    },
});

const form = useForm({
    email: '',
    password: '',
    remember: false,
});

const submit = () => {
    form.post(route('login'), {
        onFinish: () => form.reset('password'),
    });
};
</script>

<template>
    <GuestLayout>
        <Head title="Log in" />

        <div class="mb-6">
            <h3 class="text-xl font-black text-gray-900">Welcome back</h3>
            <p class="text-sm text-gray-500 mt-1">Please enter your details to sign in.</p>
        </div>

        <div v-if="status" class="mb-4 text-sm font-medium text-green-600">
            {{ status }}
        </div>

        <form @submit.prevent="submit" class="space-y-4">
            <div>
                <InputLabel for="email" value="Email" class="font-bold text-gray-700" />

                <TextInput
                    id="email"
                    type="email"
                    class="mt-1 block w-full rounded-xl border-gray-200 focus:border-indigo-500 focus:ring-indigo-500 shadow-sm"
                    v-model="form.email"
                    required
                    autofocus
                    autocomplete="username"
                />

                <InputError class="mt-2" :message="form.errors.email" />
            </div>

            <div>
                <InputLabel for="password" value="Password" class="font-bold text-gray-700" />

                <TextInput
                    id="password"
                    type="password"
                    class="mt-1 block w-full rounded-xl border-gray-200 focus:border-indigo-500 focus:ring-indigo-500 shadow-sm"
                    v-model="form.password"
                    required
                    autocomplete="current-password"
                />

                <InputError class="mt-2" :message="form.errors.password" />
            </div>

            <div class="flex items-center justify-between text-sm">
                <label class="flex items-center">
                    <Checkbox name="remember" v-model:checked="form.remember" class="rounded border-gray-300 text-indigo-600 focus:ring-indigo-500" />
                    <span class="ms-2 text-gray-600">Remember me</span>
                </label>

                <Link
                    v-if="canResetPassword"
                    :href="route('password.request')"
                    class="text-indigo-600 hover:text-indigo-700 font-semibold"
                >
                    Forgot password?
                </Link>
            </div>

            <div>
                <button
                    type="submit"
                    class="w-full py-3.5 px-4 bg-indigo-600 text-white font-bold rounded-2xl shadow-xl shadow-indigo-200 hover:bg-indigo-700 transition-all text-center block"
                    :class="{ 'opacity-25': form.processing }"
                    :disabled="form.processing"
                >
                    Sign in
                </button>
            </div>

            <div class="text-center mt-6 text-sm text-gray-500">
                Don't have an account? 
                <Link :href="route('register')" class="text-indigo-600 font-bold hover:underline">Sign up</Link>
            </div>
        </form>
    </GuestLayout>
</template>