<script setup lang="ts">
import { Head, Link, useForm } from '@inertiajs/vue3';
import { computed } from 'vue';
import {
    ArrowLeft,
    Save,
    Target,
    AlertCircle,
    Calendar,
    PiggyBank,
    Info,
} from 'lucide-vue-next';

import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';

defineOptions({
    layout: {
        breadcrumbs: [
            { title: 'Dashboard', href: '/dashboard' },
            { title: 'Target Tabungan', href: '/saving-goals' },
            { title: 'Tambah Target', href: '/saving-goals/create' },
        ],
    },
});

const form = useForm({
    title: '',
    target_amount: '',
    current_amount: '0',
    target_date: '',
    status: 'active',
    description: '',
});

const progressPercentage = computed(() => {
    const target = Number(form.target_amount || 0);
    const current = Number(form.current_amount || 0);

    if (target <= 0) return 0;

    return Math.min(Math.round((current / target) * 100), 100);
});

const remainingAmount = computed(() => {
    return Math.max(Number(form.target_amount || 0) - Number(form.current_amount || 0), 0);
});

const hasPreview = computed(() => {
    return Boolean(form.title || form.target_amount || form.current_amount || form.target_date);
});

const formatCurrency = (value: number | string | null | undefined) => {
    return new Intl.NumberFormat('id-ID', {
        style: 'currency',
        currency: 'IDR',
        minimumFractionDigits: 0,
        maximumFractionDigits: 0,
    }).format(Number(value || 0));
};

const formatDate = (date: string) => {
    if (!date) return '-';

    return new Date(date).toLocaleDateString('id-ID', {
        day: 'numeric',
        month: 'long',
        year: 'numeric',
    });
};

const submit = () => {
    form.post('/saving-goals', {
        preserveScroll: true,
    });
};
</script>

<template>
    <Head title="Tambah Target Tabungan - Loovie Apps" />

    <div class="space-y-6 p-4 sm:p-6 lg:p-8">
        <!-- Header -->
        <div>
            <Link
                href="/saving-goals"
                class="mb-3 inline-flex items-center text-sm font-medium text-indigo-600 hover:text-indigo-700"
            >
                <ArrowLeft class="mr-2 h-4 w-4" />
                Kembali ke Target Tabungan
            </Link>

            <h1 class="text-2xl font-bold text-gray-900 sm:text-3xl dark:text-white">
                Tambah Target Tabungan
            </h1>

            <p class="mt-1 text-sm text-gray-600 dark:text-gray-400">
                Buat target seperti dana darurat, beli laptop, liburan, atau modal usaha.
            </p>
        </div>

        <div class="grid grid-cols-1 gap-6 lg:grid-cols-3">
            <!-- Form -->
            <div class="lg:col-span-2">
                <div class="space-y-6 rounded-2xl border border-gray-200 bg-white p-6 shadow-sm dark:border-gray-800 dark:bg-gray-900">
                    <!-- Title -->
                    <div>
                        <label class="mb-2 block text-sm font-semibold">
                            Nama Target
                            <span class="text-red-500">*</span>
                        </label>

                        <Input
                            v-model="form.title"
                            type="text"
                            placeholder="Contoh: Beli Laptop, Dana Darurat, Liburan"
                            class="rounded-lg border-gray-200 bg-gray-50 dark:border-gray-700 dark:bg-gray-800"
                        />

                        <p v-if="form.errors.title" class="mt-2 flex items-center gap-2 text-sm text-red-600">
                            <AlertCircle class="h-4 w-4" />
                            {{ form.errors.title }}
                        </p>
                    </div>

                    <!-- Target Amount -->
                    <div>
                        <label class="mb-2 block text-sm font-semibold">
                            Nominal Target
                            <span class="text-red-500">*</span>
                        </label>

                        <div class="relative">
                            <span class="absolute left-4 top-3 text-gray-500">Rp</span>
                            <Input
                                v-model="form.target_amount"
                                type="number"
                                min="1"
                                step="1000"
                                placeholder="0"
                                class="rounded-lg border-gray-200 bg-gray-50 pl-10 dark:border-gray-700 dark:bg-gray-800"
                            />
                        </div>

                        <p v-if="form.errors.target_amount" class="mt-2 flex items-center gap-2 text-sm text-red-600">
                            <AlertCircle class="h-4 w-4" />
                            {{ form.errors.target_amount }}
                        </p>
                    </div>

                    <!-- Current Amount -->
                    <div>
                        <label class="mb-2 block text-sm font-semibold">
                            Nominal Terkumpul
                        </label>

                        <div class="relative">
                            <span class="absolute left-4 top-3 text-gray-500">Rp</span>
                            <Input
                                v-model="form.current_amount"
                                type="number"
                                min="0"
                                step="1000"
                                placeholder="0"
                                class="rounded-lg border-gray-200 bg-gray-50 pl-10 dark:border-gray-700 dark:bg-gray-800"
                            />
                        </div>

                        <p class="mt-1 text-xs text-muted-foreground">
                            Isi 0 jika belum ada uang yang terkumpul.
                        </p>

                        <p v-if="form.errors.current_amount" class="mt-2 flex items-center gap-2 text-sm text-red-600">
                            <AlertCircle class="h-4 w-4" />
                            {{ form.errors.current_amount }}
                        </p>
                    </div>

                    <!-- Target Date -->
                    <div>
                        <label class="mb-2 block text-sm font-semibold">
                            Tanggal Target
                        </label>

                        <Input
                            v-model="form.target_date"
                            type="date"
                            class="rounded-lg border-gray-200 bg-gray-50 dark:border-gray-700 dark:bg-gray-800"
                        />

                        <p v-if="form.errors.target_date" class="mt-2 flex items-center gap-2 text-sm text-red-600">
                            <AlertCircle class="h-4 w-4" />
                            {{ form.errors.target_date }}
                        </p>
                    </div>

                    <!-- Status -->
                    <div>
                        <label class="mb-2 block text-sm font-semibold">
                            Status
                            <span class="text-red-500">*</span>
                        </label>

                        <select
                            v-model="form.status"
                            class="w-full rounded-lg border border-gray-200 bg-gray-50 px-4 py-2 text-gray-900 focus:ring-2 focus:ring-indigo-500 dark:border-gray-700 dark:bg-gray-800 dark:text-white"
                        >
                            <option value="active">Aktif</option>
                            <option value="completed">Selesai</option>
                            <option value="cancelled">Dibatalkan</option>
                        </select>

                        <p v-if="form.errors.status" class="mt-2 flex items-center gap-2 text-sm text-red-600">
                            <AlertCircle class="h-4 w-4" />
                            {{ form.errors.status }}
                        </p>
                    </div>

                    <!-- Description -->
                    <div>
                        <label class="mb-2 block text-sm font-semibold">
                            Deskripsi
                        </label>

                        <textarea
                            v-model="form.description"
                            rows="3"
                            placeholder="Contoh: target untuk upgrade laptop kerja..."
                            class="w-full rounded-lg border border-gray-200 bg-gray-50 px-4 py-2 text-gray-900 focus:ring-2 focus:ring-indigo-500 dark:border-gray-700 dark:bg-gray-800 dark:text-white"
                        ></textarea>

                        <p v-if="form.errors.description" class="mt-2 flex items-center gap-2 text-sm text-red-600">
                            <AlertCircle class="h-4 w-4" />
                            {{ form.errors.description }}
                        </p>
                    </div>

                    <!-- Actions -->
                    <div class="flex flex-col gap-3 border-t border-gray-200 pt-6 sm:flex-row dark:border-gray-700">
                        <Link href="/saving-goals" class="flex-1">
                            <Button variant="outline" class="w-full" type="button">
                                Batal
                            </Button>
                        </Link>

                        <Button
                            type="button"
                            :disabled="form.processing"
                            class="flex-1 bg-indigo-600 text-white hover:bg-indigo-700 disabled:cursor-not-allowed disabled:bg-slate-400"
                            @click="submit"
                        >
                            <Save class="mr-2 h-4 w-4" />
                            {{ form.processing ? 'Menyimpan...' : 'Simpan Target' }}
                        </Button>
                    </div>
                </div>
            </div>

            <!-- Sidebar -->
            <div class="space-y-6 lg:col-span-1">
                <div class="rounded-2xl border border-gray-200 bg-white p-6 shadow-sm dark:border-gray-800 dark:bg-gray-900">
                    <div class="mb-4 flex items-center gap-2">
                        <Target class="h-5 w-5 text-indigo-500" />
                        <p class="text-sm font-semibold">Preview Target</p>
                    </div>

                    <div v-if="hasPreview" class="space-y-4">
                        <div>
                            <p class="text-xs text-muted-foreground">Nama Target</p>
                            <p class="mt-1 font-semibold">{{ form.title || 'Nama Target' }}</p>
                        </div>

                        <div class="grid grid-cols-1 gap-3">
                            <div class="rounded-xl bg-muted/40 p-4">
                                <p class="text-xs text-muted-foreground">Target</p>
                                <p class="mt-1 text-xl font-bold">
                                    {{ formatCurrency(form.target_amount) }}
                                </p>
                            </div>

                            <div class="rounded-xl bg-emerald-50 p-4 dark:bg-emerald-950/30">
                                <p class="text-xs text-emerald-700 dark:text-emerald-300">Terkumpul</p>
                                <p class="mt-1 text-xl font-bold text-emerald-500">
                                    {{ formatCurrency(form.current_amount) }}
                                </p>
                            </div>

                            <div class="rounded-xl bg-red-50 p-4 dark:bg-red-950/30">
                                <p class="text-xs text-red-700 dark:text-red-300">Sisa</p>
                                <p class="mt-1 text-xl font-bold text-red-500">
                                    {{ formatCurrency(remainingAmount) }}
                                </p>
                            </div>
                        </div>

                        <div>
                            <div class="mb-1 flex justify-between text-xs text-muted-foreground">
                                <span>Progress</span>
                                <span>{{ progressPercentage }}%</span>
                            </div>

                            <div class="h-3 overflow-hidden rounded-full bg-muted">
                                <div
                                    class="h-full rounded-full bg-indigo-500"
                                    :style="{ width: progressPercentage + '%' }"
                                ></div>
                            </div>
                        </div>

                        <div class="flex items-center gap-2 text-sm text-muted-foreground">
                            <Calendar class="h-4 w-4" />
                            {{ formatDate(form.target_date) }}
                        </div>
                    </div>

                    <div v-else class="text-center">
                        <PiggyBank class="mx-auto h-10 w-10 text-gray-400" />
                        <p class="mt-3 font-semibold">Preview masih kosong</p>
                        <p class="mt-1 text-sm text-muted-foreground">
                            Isi target untuk melihat preview.
                        </p>
                    </div>
                </div>

                <div class="rounded-2xl border border-blue-200 bg-blue-50 p-4 shadow-sm dark:border-blue-800 dark:bg-blue-900/20">
                    <div class="flex gap-3">
                        <Info class="mt-0.5 h-5 w-5 shrink-0 text-blue-600 dark:text-blue-400" />
                        <div>
                            <p class="text-sm font-medium text-blue-900 dark:text-blue-400">
                                Tips Target
                            </p>
                            <p class="mt-1 text-xs text-blue-800 dark:text-blue-300">
                                Buat target dengan nominal dan deadline yang realistis agar progress lebih mudah dicapai.
                            </p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>
