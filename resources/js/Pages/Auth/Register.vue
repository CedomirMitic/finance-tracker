<script setup>
import GuestLayout from '@/Layouts/GuestLayout.vue';
import InputError from '@/Components/InputError.vue';
import InputLabel from '@/Components/InputLabel.vue';
import TextInput from '@/Components/TextInput.vue';
import { Head, Link, useForm } from '@inertiajs/vue3';
import { ref } from 'vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';

import TermsOfService from '@/Pages/TermsOfService.vue';
import PrivacyPolicy from '@/Pages/PrivacyPolicy.vue';

const form = useForm({
    name: '',
    email: '',
    password: '',
    password_confirmation: '',
});

const submit = () => {
    form.post(route('register'), {
        onFinish: () => form.reset('password', 'password_confirmation'),
    });
};

const activeModal = ref(null); // 'terms' | 'privacy' | null
</script>

<template>
    <GuestLayout>
        <Head title="Register" />

        <div class="mb-6">
            <h3 class="text-xl font-black text-slate-900">Create an account</h3>
            <p class="text-sm text-slate-500 mt-1">Start tracking your finances today.</p>
        </div>

        <form @submit.prevent="submit" class="space-y-4">
            <div>
                <InputLabel for="name" value="Name" class="font-bold text-slate-700" />
                <TextInput
                    id="name"
                    type="text"
                    class="mt-1 block w-full rounded-xl border-slate-200 focus:border-emerald-500 focus:ring-emerald-500 shadow-sm"
                    v-model="form.name"
                    required
                    autofocus
                    autocomplete="name"
                />
                <InputError class="mt-2" :message="form.errors.name" />
            </div>

            <div>
                <InputLabel for="email" value="Email" class="font-bold text-slate-700" />
                <TextInput
                    id="email"
                    type="email"
                    class="mt-1 block w-full rounded-xl border-slate-200 focus:border-emerald-500 focus:ring-emerald-500 shadow-sm"
                    v-model="form.email"
                    required
                    autocomplete="username"
                />
                <InputError class="mt-2" :message="form.errors.email" />
            </div>

            <div>
                <InputLabel for="password" value="Password" class="font-bold text-slate-700" />
                <TextInput
                    id="password"
                    type="password"
                    class="mt-1 block w-full rounded-xl border-slate-200 focus:border-emerald-500 focus:ring-emerald-500 shadow-sm"
                    v-model="form.password"
                    required
                    autocomplete="new-password"
                />
                <InputError class="mt-2" :message="form.errors.password" />
            </div>

            <div>
                <InputLabel for="password_confirmation" value="Confirm Password" class="font-bold text-slate-700" />
                <TextInput
                    id="password_confirmation"
                    type="password"
                    class="mt-1 block w-full rounded-xl border-slate-200 focus:border-emerald-500 focus:ring-emerald-500 shadow-sm"
                    v-model="form.password_confirmation"
                    required
                    autocomplete="new-password"
                />
                <InputError class="mt-2" :message="form.errors.password_confirmation" />
            </div>

            <div class="text-xs text-slate-500 leading-relaxed">
                By registering, you agree to our 
                <button type="button" @click="activeModal = 'terms'" class="text-emerald-600 font-semibold hover:underline">Terms of Service</button> 
                and 
                <button type="button" @click="activeModal = 'privacy'" class="text-emerald-600 font-semibold hover:underline">Privacy Policy</button>.
            </div>

            <div>
                <PrimaryButton color="emerald" class="w-full py-3.5" :class="{ 'opacity-25': form.processing }" :disabled="form.processing">
                    Create Account
                </PrimaryButton>
            </div>

            <div class="text-center mt-6 text-sm text-slate-500">
                Already have an account? 
                <Link :href="route('login')" class="text-emerald-600 font-bold hover:underline">Sign in</Link>
            </div>
        </form>

        <Teleport to="body">
            <div v-if="activeModal" class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/50 backdrop-blur-sm">
                <div class="bg-white rounded-3xl max-w-2xl w-full p-6 shadow-2xl border border-slate-100 max-h-[85vh] flex flex-col relative">
                    <div class="flex justify-between items-center pb-4 border-b border-slate-100">
                        <h3 class="text-xl font-black text-slate-900">
                            {{ activeModal === 'terms' ? 'Terms of Service' : 'Privacy Policy' }}
                        </h3>
                        <button @click="activeModal = null" class="p-2 text-slate-400 hover:text-slate-600 rounded-xl hover:bg-slate-100">
                            ✕
                        </button>
                    </div>

                    <div class="flex-grow mt-4 overflow-y-auto pr-2 text-left">
                        <TermsOfService v-if="activeModal === 'terms'" />
                        <PrivacyPolicy v-else-if="activeModal === 'privacy'" />
                    </div>

                    <div class="mt-4 pt-4 border-t border-slate-100 flex justify-end">
                        <button @click="activeModal = null" class="px-5 py-2.5 bg-slate-900 text-white font-bold rounded-xl text-sm hover:bg-slate-800 transition-all">Close</button>
                    </div>
                </div>
            </div>
        </Teleport>
    </GuestLayout>
</template>