<script setup>
import { computed } from 'vue'
import { usePage } from '@inertiajs/vue3'

const page = usePage()
const flash = computed(() => page.props.flash || {})
</script>

<template>
    <div>
        <!-- Success Flash Message -->
        <transition enter-active-class="transition duration-300 ease-out"
            enter-from-class="transform -translate-y-2 opacity-0" enter-to-class="transform translate-y-0 opacity-100"
            leave-active-class="transition duration-200 ease-in" leave-from-class="transform translate-y-0 opacity-100"
            leave-to-class="transform -translate-y-2 opacity-0">
            <div v-if="flash.success"
                class="mb-6 p-4 bg-emerald-50 border border-emerald-200 text-emerald-800 rounded-xl shadow-sm flex items-center justify-between">
                <div class="flex items-center gap-3">
                    <span class="text-xl">✅</span>
                    <span class="text-sm font-semibold">{{ flash.success }}</span>
                </div>
                <button @click="page.props.flash.success = null"
                    class="text-emerald-400 hover:text-emerald-600 text-sm font-bold">✕</button>
            </div>
        </transition>

        <!-- Error Flash Message -->
        <transition enter-active-class="transition duration-300 ease-out"
            enter-from-class="transform -translate-y-2 opacity-0" enter-to-class="transform translate-y-0 opacity-100"
            leave-active-class="transition duration-200 ease-in" leave-from-class="transform translate-y-0 opacity-100"
            leave-to-class="transform -translate-y-2 opacity-0">
            <div v-if="flash.error"
                class="mb-6 p-4 bg-red-50 border border-red-200 text-red-800 rounded-xl shadow-sm flex items-center justify-between">
                <div class="flex items-center gap-3">
                    <span class="text-xl">❌</span>
                    <span class="text-sm font-semibold">{{ flash.error }}</span>
                </div>
                <button @click="page.props.flash.error = null"
                    class="text-red-400 hover:text-red-600 text-sm font-bold">✕</button>
            </div>
        </transition>
    </div>
</template>