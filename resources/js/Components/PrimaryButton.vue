<script setup>
defineProps({
    type: {
        type: String,
        default: 'submit',
    },
    color: {
        type: String,
        default: 'emerald', 
    }
});
</script>

<template>
    <button
        :type="type"
        :class="[
            'inline-flex items-center justify-center px-4 py-3 text-sm font-bold rounded-2xl shadow-lg transition-all duration-150 ease-in-out focus:outline-none focus:ring-2 focus:ring-offset-2 active:scale-[0.98] text-white',
            color === 'emerald' ? 'bg-emerald-600 shadow-emerald-600/25 hover:bg-emerald-700 focus:ring-emerald-500' : '',
            color === 'indigo' ? 'bg-indigo-600 shadow-indigo-600/25 hover:bg-indigo-700 focus:ring-indigo-500' : '',
            color === 'slate' ? 'bg-slate-900 shadow-slate-900/25 hover:bg-slate-800 focus:ring-slate-500' : '',
        ]"
    >
        <slot />
    </button>
</template>