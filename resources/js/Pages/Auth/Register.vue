<script setup>
import GuestLayout from '@/Layouts/GuestLayout.vue';
import InputError from '@/Components/InputError.vue';
import InputLabel from '@/Components/InputLabel.vue';
import TextInput from '@/Components/TextInput.vue';
import { Head, Link, useForm } from '@inertiajs/vue3';
import { ref } from 'vue';

// Direktno uvozimo postojeće stranice da bi se modal otvorio trenutno
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
            <h3 class="text-xl font-black text-gray-900">Create an account</h3>
            <p class="text-sm text-gray-500 mt-1">Start tracking your finances today.</p>
        </div>

        <form @submit.prevent="submit" class="space-y-4">
            <div>
                <InputLabel for="name" value="Name" class="font-bold text-gray-700" />
                <TextInput
                    id="name"
                    type="text"
                    class="mt-1 block w-full rounded-xl border-gray-200 focus:border-indigo-500 focus:ring-indigo-500 shadow-sm"
                    v-model="form.name"
                    required
                    autofocus
                    autocomplete="name"
                />
                <InputError class="mt-2" :message="form.errors.name" />
            </div>

            <div>
                <InputLabel for="email" value="Email" class="font-bold text-gray-700" />
                <TextInput
                    id="email"
                    type="email"
                    class="mt-1 block w-full rounded-xl border-gray-200 focus:border-indigo-500 focus:ring-indigo-500 shadow-sm"
                    v-model="form.email"
                    required
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
                    autocomplete="new-password"
                />
                <InputError class="mt-2" :message="form.errors.password" />
            </div>

            <div>
                <InputLabel for="password_confirmation" value="Confirm Password" class="font-bold text-gray-700" />
                <TextInput
                    id="password_confirmation"
                    type="password"
                    class="mt-1 block w-full rounded-xl border-gray-200 focus:border-indigo-500 focus:ring-indigo-500 shadow-sm"
                    v-model="form.password_confirmation"
                    required
                    autocomplete="new-password"
                />
                <InputError class="mt-2" :message="form.errors.password_confirmation" />
            </div>

            <div class="text-xs text-gray-500 leading-relaxed">
                By registering, you agree to our 
                <button type="button" @click="activeModal = 'terms'" class="text-indigo-600 font-semibold hover:underline">Terms of Service</button> 
                and 
                <button type="button" @click="activeModal = 'privacy'" class="text-indigo-600 font-semibold hover:underline">Privacy Policy</button>.
            </div>

            <div>
                <button
                    type="submit"
                    class="w-full py-3.5 px-4 bg-indigo-600 text-white font-bold rounded-2xl shadow-xl shadow-indigo-200 hover:bg-indigo-700 transition-all text-center block"
                    :class="{ 'opacity-25': form.processing }"
                    :disabled="form.processing"
                >
                    Create Account
                </button>
            </div>

            <div class="text-center mt-6 text-sm text-gray-500">
                Already have an account? 
                <Link :href="route('login')" class="text-indigo-600 font-bold hover:underline">Sign in</Link>
            </div>
        </form>

        <!-- MODAL ZA PRIKAZ -->
        <Teleport to="body">
            <div v-if="activeModal" class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/50 backdrop-blur-sm">
                <div class="bg-white rounded-3xl max-w-2xl w-full p-6 shadow-2xl border border-gray-100 max-h-[85vh] flex flex-col relative">
                    <div class="flex justify-between items-center pb-4 border-b border-gray-100">
                        <h3 class="text-xl font-black text-gray-900">
                            {{ activeModal === 'terms' ? 'Terms of Service' : 'Privacy Policy' }}
                        </h3>
                        <button @click="activeModal = null" class="p-2 text-gray-400 hover:text-gray-600 rounded-xl hover:bg-gray-100">
                            ✕
                        </button>
                    </div>

                    <!-- Prikazujemo direktno uvezene komponente bez kašnjenja -->
                    <div class="flex-grow mt-4 overflow-y-auto pr-2 text-left">
                        <TermsOfService v-if="activeModal === 'terms'" />
                        <PrivacyPolicy v-else-if="activeModal === 'privacy'" />
                    </div>

                    <div class="mt-4 pt-4 border-t border-gray-100 flex justify-end">
                        <button @click="activeModal = null" class="px-5 py-2.5 bg-gray-900 text-white font-bold rounded-xl text-sm hover:bg-gray-800 transition-all">Close</button>
                    </div>
                </div>
            </div>
        </Teleport>
    </GuestLayout>
</template>