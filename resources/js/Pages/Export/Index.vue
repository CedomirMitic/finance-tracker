<script setup lang="ts">
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, usePage } from '@inertiajs/vue3';
import { ref, computed } from 'vue';
import { User } from '@/types';

interface PageProps {
    auth: {
        user: User;
    };
    [key: string]: any;
}


const successMessage = ref<string | null>(null);

const triggerSuccess = (text: string) => {
    successMessage.value = text;
    setTimeout(() => {
        successMessage.value = null;
    }, 3000);
};
const page = usePage<PageProps>();
const isPro = computed(() => page.props.auth.user?.subscribed ?? false);

const years = [2024, 2025, 2026];
const months = [
    { v: '01', l: 'January' }, { v: '02', l: 'February' }, { v: '03', l: 'March' },
    { v: '04', l: 'April' }, { v: '05', l: 'May' }, { v: '06', l: 'June' },
    { v: '07', l: 'July' }, { v: '08', l: 'August' }, { v: '09', l: 'September' },
    { v: '10', l: 'October' }, { v: '11', l: 'November' }, { v: '12', l: 'December' }
];

const selectedYear = ref(new Date().getFullYear());
const selectedMonth = ref(('0' + (new Date().getMonth() + 1)).slice(-2));

const downloadCsv = () => {
    const url = route('export.download', {
        year: selectedYear.value,
        month: selectedMonth.value,
        format: 'csv'
    });

    const link = document.createElement('a');
    link.href = url;
    link.setAttribute('download', `report-${selectedYear.value}-${selectedMonth.value}.csv`);
    document.body.appendChild(link);
    link.click();
    document.body.removeChild(link);

    triggerSuccess('CSV report was successfully downloaded!');
};

const downloadPdf = () => {
    if (!isPro.value) {
        window.location.href = route('billing.index');
        return;
    }

    const url = route('export.pdf', {
        year: selectedYear.value,
        month: selectedMonth.value
    });

    window.open(url, '_blank');
    triggerSuccess('PDF report was successfully downloaded!');
};
</script>

<template>

    <Head title="Export Data" />
    <AuthenticatedLayout>
        <template #header>
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">Export Reports</h2>
        </template>

        <div class="py-12">
            <div class="max-w-3xl mx-auto sm:px-6 lg:px-8">

                <!-- Flash Messages Alert Box -->
                <transition enter-active-class="transition duration-300 ease-out"
                    enter-from-class="transform -translate-y-2 opacity-0"
                    enter-to-class="transform translate-y-0 opacity-100"
                    leave-active-class="transition duration-200 ease-in"
                    leave-from-class="transform translate-y-0 opacity-100"
                    leave-to-class="transform -translate-y-2 opacity-0">
                    <div v-if="successMessage"
                        class="mb-6 p-4 bg-emerald-50 border border-emerald-200 text-emerald-800 rounded-xl shadow-sm flex items-center justify-between">
                        <div class="flex items-center gap-3">
                            <span class="text-xl">✅</span>
                            <span class="text-sm font-semibold">{{ successMessage }}</span>
                        </div>
                        <button @click="successMessage = null"
                            class="text-emerald-400 hover:text-emerald-600 text-sm font-bold">✕</button>
                    </div>
                </transition>

                <!-- Flash Message END-->

                <div class="bg-white overflow-hidden shadow-sm sm:rounded-xl p-8 border border-gray-100">
                    <div class="flex items-center gap-4 mb-8">
                        <div class="p-3 bg-indigo-50 rounded-lg text-indigo-600">
                            <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z">
                                </path>
                            </svg>
                        </div>
                        <div>
                            <h3 class="text-lg font-black text-gray-900">Download CSV Report</h3>
                            <p class="text-sm text-gray-500">Select a period to generate your financial spreadsheet.</p>
                        </div>
                    </div>

                    <div class="grid grid-cols-2 gap-4 mb-8">
                        <div>
                            <label
                                class="block text-xs font-bold text-gray-400 uppercase mb-2 tracking-widest">Year</label>
                            <select v-model="selectedYear"
                                class="w-full border-gray-200 rounded-lg focus:ring-indigo-500">
                                <option v-for="y in years" :key="y" :value="y">{{ y }}</option>
                            </select>
                        </div>
                        <div>
                            <label
                                class="block text-xs font-bold text-gray-400 uppercase mb-2 tracking-widest">Month</label>
                            <select v-model="selectedMonth"
                                class="w-full border-gray-200 rounded-lg focus:ring-indigo-500">
                                <option v-for="m in months" :key="m.v" :value="m.v">{{ m.l }}</option>
                            </select>
                        </div>
                    </div>

                    <div class="flex flex-col sm:flex-row gap-4">
                        <button @click="downloadPdf"
                            :class="isPro ? 'bg-red-600 hover:bg-red-700 shadow-red-100' : 'bg-gray-800 hover:bg-gray-900 shadow-gray-200'"
                            class="flex-1 text-white font-bold py-4 rounded-xl shadow-lg transition flex items-center justify-center gap-2 relative">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z">
                                </path>
                            </svg>
                            <span v-if="isPro">Download PDF</span>
                            <span v-else class="flex items-center gap-1.5">
                                Upgrade to Pro (PDF)
                                <svg class="w-4 h-4 text-yellow-400" fill="currentColor" viewBox="0 0 20 20">
                                    <path
                                        d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.690h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.690l1.07-3.292z" />
                                </svg>
                            </span>
                        </button>

                        <button @click="downloadCsv"
                            class="flex-1 bg-green-600 hover:bg-green-700 text-white font-bold py-4 rounded-xl shadow-lg shadow-green-100 transition flex items-center justify-center gap-2">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z">
                                </path>
                            </svg>
                            Download CSV
                        </button>
                    </div>

                    <p class="mt-4 text-center text-[11px] text-gray-400 italic">
                        The file will include all income and expense transactions for the selected period.
                    </p>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>