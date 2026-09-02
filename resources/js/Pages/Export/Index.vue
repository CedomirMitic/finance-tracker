<script setup lang="ts">
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head } from '@inertiajs/vue3';
import { ref } from 'vue';

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
};

const downloadPdf = () => {
    const url = route('export.pdf', {
        year: selectedYear.value,
        month: selectedMonth.value
    });

    // Mobile browsers (especially iOS Safari) block simulated click events on dynamically 
    // created <a> elements if they aren't explicitly appended to the DOM or if target='_blank' 
    // opens an unhandled popup tab. Direct window assignment handles mobile routing natively.
    window.location.href = url;
};
</script>

<template>

    <Head title="Export Data" />
    <AuthenticatedLayout>
        <template #header>
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">Export Reports</h2>
        </template>

        <div class="py-12 text-english">
            <div class="max-w-3xl mx-auto sm:px-6 lg:px-8">
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
                            class="flex-1 bg-red-600 hover:bg-red-700 text-white font-bold py-4 rounded-xl shadow-lg shadow-red-100 transition flex items-center justify-center gap-2">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z">
                                </path>
                            </svg>
                            Download PDF
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