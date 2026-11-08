<script setup lang="ts">
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, usePage } from '@inertiajs/vue3';
import { ref, computed } from 'vue';
import { User } from '@/types';
import axios from 'axios';

interface PageProps {
    auth: {
        user: User;
    };
    [key: string]: any;
}
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

const downloadCsv = async () => {
    try {
        const response = await axios.get(route('export.download', {
            year: selectedYear.value,
            month: selectedMonth.value,
            format: 'csv'
        }), {
            responseType: 'blob', 
        });

        const blob = new Blob([response.data], { type: 'text/csv' });
        const link = document.createElement('a');
        link.href = window.URL.createObjectURL(blob);
        link.download = `report-${selectedYear.value}-${selectedMonth.value}.csv`;
        link.click();
        window.URL.revokeObjectURL(link.href);

        (page.props as any).flash.success = 'CSV report started downloading...';
    } catch (error: any) {
        (page.props as any).flash.error = 'There was an error while downloading CSV...';
    }
};

const downloadPdf = async () => {
    if (!isPro.value) {
        window.location.href = route('billing.index');
        return;
    }

    try {
        const response = await axios.get(route('export.pdf', {
            year: selectedYear.value,
            month: selectedMonth.value
        }), {
            responseType: 'blob',
        });

        
        const blob = new Blob([response.data], { type: 'application/pdf' });
        const link = document.createElement('a');
        link.href = window.URL.createObjectURL(blob);
        link.download = `Report-${selectedYear.value}-${selectedMonth.value}.pdf`;
        link.click();
        window.URL.revokeObjectURL(link.href);

        (page.props as any).flash.success = 'PDF report started downloading...';
    } catch (error: any) {
        (page.props as any).flash.error = 'There was an error while downloading...';
    }
};
</script>

<template>

    <Head title="Export Data" />
    <AuthenticatedLayout>
        <template #header>
            <div class="font-sans">
                <h2 class="font-black text-xl sm:text-2xl text-slate-900 tracking-tight">Export Reports</h2>
                <p class="text-xs text-slate-400 mt-0.5 font-medium">Download your financial records in spreadsheet or
                    document format.</p>
            </div>
        </template>

        <div class="py-12 font-sans">
            <div class="max-w-3xl mx-auto sm:px-6 lg:px-8">


                <!-- Main Card -->
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-3xl p-8 border border-slate-100">
                    <div class="flex items-center gap-4 mb-8">
                        <div class="p-3 bg-emerald-50 rounded-2xl text-emerald-600">
                            <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z">
                                </path>
                            </svg>
                        </div>
                        <div>
                            <h3 class="text-lg font-black text-slate-900 tracking-tight">Download Report</h3>
                            <p class="text-xs text-slate-500 font-medium">Select a period to generate your financial
                                spreadsheet
                                or PDF.</p>
                        </div>
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 mb-8">
                        <div>
                            <label
                                class="block text-xs font-black text-slate-400 uppercase tracking-wider mb-2">Year</label>
                            <select v-model="selectedYear"
                                class="w-full border-slate-200 rounded-2xl py-3 px-4 text-sm font-bold text-slate-700 shadow-sm focus:border-emerald-500 focus:ring-emerald-500">
                                <option v-for="y in years" :key="y" :value="y">{{ y }}</option>
                            </select>
                        </div>
                        <div>
                            <label
                                class="block text-xs font-black text-slate-400 uppercase tracking-wider mb-2">Month</label>
                            <select v-model="selectedMonth"
                                class="w-full border-slate-200 rounded-2xl py-3 px-4 text-sm font-bold text-slate-700 shadow-sm focus:border-emerald-500 focus:ring-emerald-500">
                                <option v-for="m in months" :key="m.v" :value="m.v">{{ m.l }}</option>
                            </select>
                        </div>
                    </div>

                    <div class="flex flex-col sm:flex-row gap-4">
                        <button @click="downloadPdf"
                            :class="isPro ? 'bg-red-600 hover:bg-red-700 shadow-red-600/25' : 'bg-slate-900 hover:bg-slate-800 shadow-slate-900/20'"
                            class="flex-1 text-white font-bold py-3.5 px-6 rounded-2xl shadow-lg transition-all flex items-center justify-center gap-2 relative">
                            <svg class="w-5 h-5 text-red-200" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z">
                                </path>
                            </svg>
                            <span v-if="isPro">Download PDF Report</span>
                            <span v-else class="flex items-center gap-2">
                                Upgrade to Pro (PDF)
                                <span
                                    class="bg-amber-100 text-amber-800 text-[10px] font-black px-2 py-0.5 rounded-lg">PRO
                                    ⭐</span>
                            </span>
                        </button>

                        <button @click="downloadCsv"
                            class="flex-1 bg-emerald-600 hover:bg-emerald-700 text-white font-bold py-3.5 px-6 rounded-2xl shadow-lg shadow-emerald-600/25 transition-all flex items-center justify-center gap-2">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z">
                                </path>
                            </svg>
                            Download CSV Spreadsheet
                        </button>
                    </div>

                    <p class="mt-6 text-center text-xs text-slate-400 font-medium italic">
                        The generated file will include all income and expense records for the selected monthly period.
                    </p>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>