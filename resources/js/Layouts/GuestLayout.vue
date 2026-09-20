<script setup>
import { Link } from '@inertiajs/vue3';
</script>

<template>
    <div class="min-h-screen bg-gray-50 flex flex-col justify-center items-center relative overflow-hidden px-4 py-12">
        <!-- Decorative background dots -->
        <div class="absolute top-0 left-0 w-full h-full overflow-hidden z-0 pointer-events-none">
            <div class="absolute -top-[10%] -left-[10%] w-[40%] h-[40%] bg-indigo-100 rounded-full blur-3xl opacity-50"></div>
            <div class="absolute -bottom-[10%] -right-[10%] w-[40%] h-[40%] bg-green-100 rounded-full blur-3xl opacity-50"></div>
        </div>

        <!-- Back button in left corner -->
        <div class="absolute top-6 left-6 z-20">
            <Link
                href="/"
                class="inline-flex items-center gap-2 px-4 py-2 bg-white text-gray-700 hover:text-indigo-600 font-semibold text-sm rounded-xl shadow-md border border-gray-100 hover:bg-gray-50 transition-all"
            >
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round5" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path>
                </svg>
                Back to Home
            </Link>
        </div>

        <!-- Main Logo/Headline above the form -->
        <div class="sm:mx-auto sm:w-full sm:max-w-md relative z-10 text-center mb-6">
            <Link href="/" class="inline-flex items-center justify-center p-3 bg-white rounded-2xl shadow-xl shadow-indigo-100 border border-indigo-50 mb-3">
                <svg class="w-8 h-8 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z">
                    </path>
                </svg>
            </Link>
            <h2 class="text-2xl font-black text-gray-900 tracking-tight">FinanceTracker</h2>
        </div>

        <!-- Card with Form -->
        <div class="sm:mx-auto sm:w-full sm:max-w-md relative z-10">
            <div class="bg-white py-8 px-6 shadow-2xl shadow-gray-100 border border-gray-100 rounded-3xl sm:px-10">
                <slot />
            </div>
        </div>
    </div>
</template>