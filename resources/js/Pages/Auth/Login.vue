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
            <h3 class="text-xl font-black text-slate-900">Welcome back</h3>
            <p class="text-sm text-slate-500 mt-1">Please enter your details to sign in.</p>
        </div>

        <div v-if="status" class="mb-4 text-sm font-medium text-emerald-600">
            {{ status }}
        </div>

        <form @submit.prevent="submit" class="space-y-4">
            <div>
                <InputLabel for="email" value="Email" class="font-bold text-slate-700" />

                <TextInput id="email" type="email"
                    class="mt-1 block w-full rounded-xl border-slate-200 focus:border-emerald-500 focus:ring-emerald-500 shadow-sm"
                    v-model="form.email" required autofocus autocomplete="username" />

                <InputError class="mt-2" :message="form.errors.email" />
            </div>

            <div>
                <InputLabel for="password" value="Password" class="font-bold text-slate-700" />

                <TextInput id="password" type="password"
                    class="mt-1 block w-full rounded-xl border-slate-200 focus:border-emerald-500 focus:ring-emerald-500 shadow-sm"
                    v-model="form.password" required autocomplete="current-password" />

                <InputError class="mt-2" :message="form.errors.password" />
            </div>

            <div class="flex items-center justify-between text-sm">
                <label class="flex items-center cursor-pointer">
                    <Checkbox name="remember" v-model:checked="form.remember"
                        class="rounded border-slate-300 text-emerald-600 focus:ring-emerald-500" />
                    <span class="ms-2 text-slate-600">Remember me</span>
                </label>

                <Link v-if="canResetPassword" :href="route('password.request')"
                    class="text-emerald-600 hover:text-emerald-700 font-semibold transition-colors">
                    Forgot password?
                </Link>
            </div>

            <div>
                <PrimaryButton color="emerald" class="w-full py-3.5" :class="{ 'opacity-25': form.processing }"
                    :disabled="form.processing">
                    Sign in
                </PrimaryButton>
            </div>

            <div class="text-center mt-6 text-sm text-slate-500">
                Don't have an account?
                <Link :href="route('register')" class="text-emerald-600 font-bold hover:underline">Sign up</Link>
            </div>
        </form>
    </GuestLayout>
</template>