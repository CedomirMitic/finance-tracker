<script setup lang="ts">
import { ref, computed } from 'vue';
import { router, usePage } from '@inertiajs/vue3';
import axios from 'axios';
import Multiselect from '@vueform/multiselect';
import '@vueform/multiselect/themes/default.css';

const props = defineProps<{ isPro: boolean; show: boolean }>();
const emit = defineEmits(['close', 'import_started']);

const page = usePage();
const flash = computed(() => (page.props as any).flash || {});

const step = ref<'upload' | 'map'>('upload');
const file = ref<File | null>(null);
const headers = ref<string[]>([]);
const sampleData = ref<any[]>([]);
const currencies = ref<{ value: string; label: string }[]>();

const mapping = ref({
    amount_col: null as number | null,
    description_col: null as number | null,
    date_col: null as number | null,
    currency: 'EUR',
});

const handleFileUpload = async (e: any) => {
    const uploadedFile = e.target.files[0];
    if (!uploadedFile) return;
    file.value = uploadedFile;

    // Empty flash errors if they existed
    if (page.props.flash) {
        (page.props.flash as any).error = null;
    }

    const formData = new FormData();
    formData.append('file', uploadedFile);

    try {
        const response = await axios.post(route('transactions.import.preview'), formData);
        headers.value = response.data.headers;
        sampleData.value = response.data.sample;
        currencies.value = response.data.currencies;
        step.value = 'map';
    } catch (error: any) {
        // Flash error system
        if (page.props.flash) {
            (page.props.flash as any).error = error.response?.data?.message || 'Error reading file. Please check if the format is valid.';
        }
        file.value = null;
    }
};
const isLoading = ref(false);

const submitMapping = () => {
    if (page.props.flash) {
        (page.props.flash as any).error = null;
    }

    isLoading.value = true;

    router.post(route('transactions.import.store'), mapping.value, {
        onSuccess: () => {
            step.value = 'upload';
            emit('import_started');
            emit('close');
        },
        onError: (errors) => {
            if (page.props.flash) {
                (page.props.flash as any).error = Object.values(errors)[0] as string;
            }
        },
        onFinish: () => {
            isLoading.value = false;
        }
    });
};

const closeModal = async () => {
    try {
        await axios.post(route('transactions.import.cancel'));
    } catch (e) {

    }

    if (page.props.flash) {
        (page.props.flash as any).error = null;
    }

    step.value = 'upload';
    file.value = null;
    headers.value = [];
    sampleData.value = [];
    mapping.value = {
        amount_col: null,
        description_col: null,
        date_col: null,
        currency: 'EUR',
    };

    emit('close');
};
</script>

<template>
    <div v-if="show" class="fixed inset-0 bg-slate-900/60 backdrop-blur-sm flex items-center justify-center p-4 z-50 font-sans">
        <div class="bg-white rounded-3xl p-8 max-w-xl w-full shadow-2xl border border-slate-100">
            <div class="flex justify-between items-center mb-6">
                <h3 class="text-xl font-black text-slate-900 tracking-tight">Smart Bank Statement Import</h3>
                <button @click="$emit('close')" class="text-slate-400 hover:text-slate-600 font-bold p-1 rounded-xl hover:bg-slate-100 transition-colors">✕</button>
            </div>

            <!-- Flash error Section -->
            <transition enter-active-class="transition duration-300 ease-out"
                enter-from-class="transform -translate-y-2 opacity-0"
                enter-to-class="transform translate-y-0 opacity-100"
                leave-active-class="transition duration-200 ease-in"
                leave-from-class="transform translate-y-0 opacity-100"
                leave-to-class="transform -translate-y-2 opacity-0">
                <div v-if="flash.error"
                    class="mb-6 p-4 bg-red-50 border border-red-200 text-red-800 rounded-2xl shadow-sm flex items-center justify-between">
                    <div class="flex items-center gap-3">
                        <span class="text-xl">❌</span>
                        <span class="text-sm font-semibold">{{ flash.error }}</span>
                    </div>
                    <button @click="(page.props.flash as any).error = null"
                        class="text-red-400 hover:text-red-600 text-sm font-bold">✕</button>
                </div>
            </transition>

            <!-- Upload File -->
            <div v-if="step === 'upload'"
                class="border-2 border-dashed border-slate-200 rounded-2xl p-8 text-center bg-slate-50/50 hover:bg-slate-50 transition-colors">
                <p class="text-sm text-slate-600 mb-4 font-medium">Drag and drop bank statement (CSV, Excel) or click below</p>
                <input type="file" @change="handleFileUpload" accept=".csv,.txt,.xlsx,.xls"
                    class="block w-full text-sm text-slate-500 file:mx-auto file:py-2.5 file:px-5 file:rounded-2xl file:border-0 file:text-sm file:font-bold file:bg-emerald-50 file:text-emerald-700 hover:file:bg-emerald-100 cursor-pointer transition-all" />
            </div>

            <!-- Map Columns & Currency -->
            <div v-if="step === 'map'">
                <p class="text-sm text-slate-500 mb-6">Select which columns match your transaction fields and choose the currency of the statement:</p>

                <div class="space-y-4 mb-8">
                    <div>
                        <label class="block text-xs font-black text-slate-400 uppercase tracking-wider mb-1.5">Currency of Statement</label>
                        <Multiselect v-model="mapping.currency" :options="currencies" :searchable="true"
                            placeholder="Search currency (e.g. RSD, USD)..." track-by="value" label="label"
                            value-format="value" />
                    </div>

                    <div>
                        <label class="block text-xs font-black text-slate-400 uppercase tracking-wider mb-1.5">Transaction Date</label>
                        <select v-model="mapping.date_col" class="w-full border-slate-200 rounded-2xl text-sm focus:border-emerald-500 focus:ring-emerald-500 text-slate-800 font-medium py-3">
                            <option :value="null">-- Select Date Column (Optional) --</option>
                            <option v-for="(header, index) in headers" :key="index" :value="index">{{ header }} (Sample: {{ sampleData[0]?.[index] }})</option>
                        </select>
                    </div>

                    <div>
                        <label class="block text-xs font-black text-slate-400 uppercase tracking-wider mb-1.5">Amount</label>
                        <select v-model="mapping.amount_col" class="w-full border-slate-200 rounded-2xl text-sm focus:border-emerald-500 focus:ring-emerald-500 text-slate-800 font-medium py-3">
                            <option v-for="(header, index) in headers" :key="index" :value="index">{{ header }} (Sample: {{ sampleData[0]?.[index] }})</option>
                        </select>
                    </div>

                    <div>
                        <label class="block text-xs font-black text-slate-400 uppercase tracking-wider mb-1.5">Description / Purpose</label>
                        <select v-model="mapping.description_col" class="w-full border-slate-200 rounded-2xl text-sm focus:border-emerald-500 focus:ring-emerald-500 text-slate-800 font-medium py-3">
                            <option v-for="(header, index) in headers" :key="index" :value="index">{{ header }} (Sample: {{ sampleData[0]?.[index] }})</option>
                        </select>
                    </div>
                </div>

                <div class="flex justify-end gap-3">
                    <button @click="$emit('close')"
                        class="px-5 py-3 bg-slate-100 hover:bg-slate-200 text-slate-700 font-bold rounded-2xl text-sm transition-colors">Cancel</button>
                    <button @click="submitMapping" :disabled="isLoading"
                        class="px-6 py-3 bg-emerald-600 text-white font-bold rounded-2xl hover:bg-emerald-700 text-sm shadow-lg shadow-emerald-600/25 disabled:opacity-50 disabled:cursor-not-allowed flex items-center justify-center gap-2 transition-all">
                        <!-- Loading Spinner SVG -->
                        <svg v-if="isLoading" class="animate-spin h-4 w-4 text-white" xmlns="http://www.w3.org/2000/svg"
                            fill="none" viewBox="0 0 24 24">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4">
                            </circle>
                            <path class="opacity-75" fill="currentColor"
                                d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z">
                            </path>
                        </svg>

                        <span>{{ isLoading ? 'Processing...' : 'Finish Import' }}</span>
                    </button>
                </div>
            </div>
        </div>
    </div>
</template>