<script setup>
import { Link } from '@inertiajs/vue3';
</script>

<template>
    <div class="min-h-screen bg-slate-50 flex flex-col justify-center items-center relative overflow-hidden px-4 py-12 font-sans">
        <!-- Decorative background gradients -->
        <div class="absolute top-0 left-0 w-full h-full overflow-hidden z-0 pointer-events-none">
            <div class="absolute -top-[10%] -left-[10%] w-[40%] h-[40%] bg-emerald-100 rounded-full blur-3xl opacity-50"></div>
            <div class="absolute -bottom-[10%] -right-[10%] w-[40%] h-[40%] bg-teal-100 rounded-full blur-3xl opacity-50"></div>
        </div>

        <!-- Back button-->
        <div class="absolute top-6 left-6 z-20 hidden sm:block">
            <Link
                href="/"
                class="inline-flex items-center gap-2 px-4 py-2 bg-white text-slate-700 hover:text-emerald-600 font-semibold text-sm rounded-xl shadow-md border border-slate-100 hover:bg-slate-50 transition-all"
            >
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path>
                </svg>
                Back to Home
            </Link>
        </div>

        <!-- Main Logo/Headline above the form -->
        <div class="sm:mx-auto sm:w-full sm:max-w-md relative z-10 text-center mb-6">
            <Link href="/" class="inline-flex items-center gap-3 p-3 bg-white rounded-3xl shadow-xl shadow-emerald-50 border border-emerald-100/50 mb-3 group hover:border-emerald-200 transition-all">
                <!-- SVG Logo -->
                <svg class="h-10 w-auto text-emerald-500" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 48 48" xmlns="http://www.w3.org/2000/svg">
                    <path class="text-emerald-700 opacity-80" d="M24 4L6 10V22C6 33.04 13.68 43.12 24 46C34.32 43.12 42 33.04 42 22V10L24 4Z" />
                    <path class="text-emerald-500" stroke-linecap="round" stroke-linejoin="round" d="M14 28L18 24M18 24L24 29M24 29L34 18M34 18V30 18M34 18V22" />
                    <path class="text-emerald-400" stroke-linecap="round" stroke-linejoin="round" d="M23 15C21.34 15 20 16.34 20 18C20 19.66 21.34 21 23 21S26 22.34 26 24C26 25.66 24.66 27 23 27M23 15V13M23 27V29" />
                </svg>
                <div class="text-left pr-2">
                    <div class="text-xl font-black text-slate-950 tracking-tighter leading-none">Finance</div>
                    <div class="text-xs font-bold text-emerald-600 tracking-tight leading-none mt-0.5">Tracker</div>
                </div>
            </Link>
        </div>

        <!-- Card with Form -->
        <div class="sm:mx-auto sm:w-full sm:max-w-md relative z-10">
            <div class="bg-white py-8 px-6 shadow-2xl shadow-slate-100 border border-slate-100 rounded-3xl sm:px-10">
                <slot />
            </div>
            
            <!--   Mobile back button -->
            <div class="mt-6 text-center sm:hidden">
                <Link href="/" class="text-sm font-semibold text-slate-500 hover:text-emerald-600 transition-colors">
                    ← Back to Home
                </Link>
            </div>
        </div>
    </div>
</template>