<script setup lang="ts">
import { Head, Link, useForm } from '@inertiajs/vue3';
import { computed, ref } from 'vue';
import {
    ArrowLeft,
    AlertCircle,
    Save,
    Tag,
    TrendingUp,
    TrendingDown,
    Palette,
    Info,
    CheckCircle2,
    Clock,
} from 'lucide-vue-next';

import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';

defineOptions({
    layout: {
        breadcrumbs: [
            { title: 'Dashboard', href: '/dashboard' },
            { title: 'Kategori', href: '/categories' },
            { title: 'Edit Kategori', href: '/categories/edit' },
        ],
    },
});

interface Category {
    id: number;
    name: string;
    type: 'income' | 'expense';
    color: string;
    icon: string;
}

interface Props {
    category: Category;
}

const props = defineProps<Props>();

const colorPresets = [
    '#FF6B6B',
    '#4ECDC4',
    '#45B7D1',
    '#FFA07A',
    '#98D8C8',
    '#F7DC6F',
    '#BB8FCE',
    '#85C1E2',
    '#F8B739',
    '#52C41A',
    '#FF7A45',
    '#1890FF',
    '#722ED1',
    '#EB2F96',
    '#13C2C2',
    '#6366F1',
];

const iconPresets = [
    'salary',
    'bonus',
    'food',
    'shopping',
    'transport',
    'home',
    'travel',
    'education',
    'health',
    'phone',
    'electricity',
    'entertainment',
];

const form = useForm({
    name: props.category.name,
    type: props.category.type,
    color: props.category.color,
    icon: props.category.icon,
});

const isSubmitting = ref(false);

const previewInitial = computed(() => {
    if (form.icon) return form.icon.charAt(0).toUpperCase();
    if (form.name) return form.name.charAt(0).toUpperCase();

    return '?';
});

const typeLabel = computed(() => {
    return form.type === 'income' ? 'Pemasukan' : 'Pengeluaran';
});

const typeDescription = computed(() => {
    return form.type === 'income'
        ? 'Kategori untuk uang masuk seperti gaji, bonus, atau pemasukan lainnya.'
        : 'Kategori untuk uang keluar seperti makan, belanja, transportasi, atau tagihan.';
});

const selectColor = (color: string) => {
    form.color = color;
};

const selectIcon = (icon: string) => {
    form.icon = icon;
};

const submit = () => {
    isSubmitting.value = true;

    form.put(`/categories/${props.category.id}`, {
        preserveScroll: true,
        onFinish: () => {
            isSubmitting.value = false;
        },
    });
};
</script>

<template>
    <Head title="Edit Kategori - Loovie Apps" />

    <div class="space-y-6 p-4 sm:p-6 lg:p-8">
        <!-- Header -->
        <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
            <div>
                <Link
                    href="/categories"
                    class="mb-3 inline-flex items-center text-sm font-medium text-indigo-600 hover:text-indigo-700"
                >
                    <ArrowLeft class="mr-2 h-4 w-4" />
                    Kembali ke Kategori
                </Link>

                <h1 class="text-2xl font-bold text-gray-900 sm:text-3xl dark:text-white">
                    Edit Kategori
                </h1>

                <p class="mt-1 text-sm text-gray-600 dark:text-gray-400">
                    Perbarui kategori pemasukan atau pengeluaran yang sudah dibuat.
                </p>
            </div>
        </div>

        <div class="grid grid-cols-1 gap-6 lg:grid-cols-3">
            <!-- Form -->
            <div class="lg:col-span-2">
                <div
                    class="space-y-6 rounded-2xl border border-gray-200 bg-white p-6 shadow-sm dark:border-gray-800 dark:bg-gray-900"
                >
                    <!-- Name Field -->
                    <div>
                        <Label
                            for="name"
                            class="mb-2 block text-sm font-semibold text-gray-900 dark:text-white"
                        >
                            Nama Kategori
                            <span class="text-red-500">*</span>
                        </Label>

                        <Input
                            id="name"
                            v-model="form.name"
                            type="text"
                            placeholder="Contoh: Gaji, Makan, Belanja, Transportasi"
                            class="w-full rounded-lg border-gray-200 bg-gray-50 dark:border-gray-700 dark:bg-gray-800"
                        />

                        <p
                            v-if="form.errors.name"
                            class="mt-2 flex items-center gap-2 text-sm text-red-600 dark:text-red-400"
                        >
                            <AlertCircle class="h-4 w-4" />
                            <span>{{ form.errors.name }}</span>
                        </p>
                    </div>

                    <!-- Type Field -->
                    <div>
                        <Label class="mb-3 block text-sm font-semibold text-gray-900 dark:text-white">
                            Tipe Kategori
                            <span class="text-red-500">*</span>
                        </Label>

                        <div class="grid grid-cols-1 gap-3 sm:grid-cols-2">
                            <label class="relative">
                                <input
                                    v-model="form.type"
                                    type="radio"
                                    value="income"
                                    class="peer sr-only"
                                />

                                <div
                                    class="cursor-pointer rounded-xl border-2 border-gray-200 bg-gray-50 p-4 transition-all peer-checked:border-green-500 peer-checked:bg-green-50 dark:border-gray-700 dark:bg-gray-800 dark:peer-checked:bg-green-900/20"
                                >
                                    <div class="flex items-start gap-3">
                                        <div
                                            class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl bg-green-100 text-green-600 dark:bg-green-950 dark:text-green-300"
                                        >
                                            <TrendingUp class="h-5 w-5" />
                                        </div>

                                        <div>
                                            <p class="font-semibold text-green-700 dark:text-green-400">
                                                Pemasukan
                                            </p>
                                            <p class="mt-1 text-sm text-gray-600 dark:text-gray-400">
                                                Uang masuk
                                            </p>
                                        </div>
                                    </div>
                                </div>
                            </label>

                            <label class="relative">
                                <input
                                    v-model="form.type"
                                    type="radio"
                                    value="expense"
                                    class="peer sr-only"
                                />

                                <div
                                    class="cursor-pointer rounded-xl border-2 border-gray-200 bg-gray-50 p-4 transition-all peer-checked:border-red-500 peer-checked:bg-red-50 dark:border-gray-700 dark:bg-gray-800 dark:peer-checked:bg-red-900/20"
                                >
                                    <div class="flex items-start gap-3">
                                        <div
                                            class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl bg-red-100 text-red-600 dark:bg-red-950 dark:text-red-300"
                                        >
                                            <TrendingDown class="h-5 w-5" />
                                        </div>

                                        <div>
                                            <p class="font-semibold text-red-700 dark:text-red-400">
                                                Pengeluaran
                                            </p>
                                            <p class="mt-1 text-sm text-gray-600 dark:text-gray-400">
                                                Uang keluar
                                            </p>
                                        </div>
                                    </div>
                                </div>
                            </label>
                        </div>

                        <p
                            v-if="form.errors.type"
                            class="mt-2 flex items-center gap-2 text-sm text-red-600 dark:text-red-400"
                        >
                            <AlertCircle class="h-4 w-4" />
                            <span>{{ form.errors.type }}</span>
                        </p>
                    </div>

                    <!-- Color Field -->
                    <div>
                        <Label class="mb-3 block text-sm font-semibold text-gray-900 dark:text-white">
                            Warna Kategori
                            <span class="text-red-500">*</span>
                        </Label>

                        <div class="space-y-4">
                            <!-- Preview Color -->
                            <div class="flex items-center gap-3">
                                <div
                                    :style="{ backgroundColor: form.color }"
                                    class="h-16 w-16 rounded-xl border border-gray-200 shadow-sm dark:border-gray-700"
                                ></div>

                                <div>
                                    <p class="text-sm text-gray-600 dark:text-gray-400">
                                        Warna dipilih
                                    </p>

                                    <p class="font-mono text-lg font-semibold text-gray-900 dark:text-white">
                                        {{ form.color }}
                                    </p>
                                </div>
                            </div>

                            <!-- Color Presets -->
                            <div>
                                <p class="mb-2 text-xs text-gray-600 dark:text-gray-400">
                                    Pilih warna cepat
                                </p>

                                <div class="grid grid-cols-8 gap-2 sm:grid-cols-10 md:grid-cols-12">
                                    <button
                                        v-for="color in colorPresets"
                                        :key="color"
                                        type="button"
                                        :style="{ backgroundColor: color }"
                                        :class="[
                                            'h-10 w-10 rounded-lg border-2 transition-all hover:scale-105',
                                            form.color === color
                                                ? 'border-gray-900 shadow-lg ring-2 ring-gray-300 dark:border-white dark:ring-gray-600'
                                                : 'border-transparent hover:shadow-md',
                                        ]"
                                        @click="selectColor(color)"
                                    />
                                </div>
                            </div>

                            <!-- Custom Color Input -->
                            <div>
                                <Label
                                    for="color"
                                    class="mb-2 block text-xs font-medium text-gray-600 dark:text-gray-400"
                                >
                                    Custom Hex Color
                                </Label>

                                <div class="flex gap-2">
                                    <Input
                                        id="color"
                                        v-model="form.color"
                                        type="text"
                                        placeholder="#4ECDC4"
                                        class="rounded-lg border-gray-200 bg-gray-50 font-mono dark:border-gray-700 dark:bg-gray-800"
                                    />

                                    <input
                                        v-model="form.color"
                                        type="color"
                                        class="h-10 w-14 cursor-pointer rounded-lg border border-gray-200 bg-gray-50 p-1 dark:border-gray-700 dark:bg-gray-800"
                                    />
                                </div>
                            </div>
                        </div>

                        <p
                            v-if="form.errors.color"
                            class="mt-2 flex items-center gap-2 text-sm text-red-600 dark:text-red-400"
                        >
                            <AlertCircle class="h-4 w-4" />
                            <span>{{ form.errors.color }}</span>
                        </p>
                    </div>

                    <!-- Icon Field -->
                    <div>
                        <Label
                            for="icon"
                            class="mb-2 block text-sm font-semibold text-gray-900 dark:text-white"
                        >
                            Icon / Kode Icon
                            <span class="text-red-500">*</span>
                        </Label>

                        <Input
                            id="icon"
                            v-model="form.icon"
                            type="text"
                            placeholder="Contoh: salary, food, shopping, transport"
                            class="w-full rounded-lg border-gray-200 bg-gray-50 dark:border-gray-700 dark:bg-gray-800"
                        />

                        <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">
                            Isi dengan teks/icon code sederhana. Contoh: salary, food, shopping, transport.
                        </p>

                        <div class="mt-3 flex flex-wrap gap-2">
                            <button
                                v-for="icon in iconPresets"
                                :key="icon"
                                type="button"
                                :class="[
                                    'rounded-full border px-3 py-1 text-xs font-medium transition',
                                    form.icon === icon
                                        ? 'border-indigo-500 bg-indigo-100 text-indigo-700 dark:bg-indigo-950 dark:text-indigo-300'
                                        : 'border-gray-200 bg-gray-50 text-gray-600 hover:bg-gray-100 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-300 dark:hover:bg-gray-700',
                                ]"
                                @click="selectIcon(icon)"
                            >
                                {{ icon }}
                            </button>
                        </div>

                        <p
                            v-if="form.errors.icon"
                            class="mt-2 flex items-center gap-2 text-sm text-red-600 dark:text-red-400"
                        >
                            <AlertCircle class="h-4 w-4" />
                            <span>{{ form.errors.icon }}</span>
                        </p>
                    </div>

                    <!-- Buttons -->
                    <div class="flex flex-col gap-3 border-t border-gray-200 pt-6 sm:flex-row dark:border-gray-700">
                        <Link href="/categories" class="flex-1">
                            <Button
                                variant="outline"
                                class="w-full"
                                type="button"
                            >
                                Batal
                            </Button>
                        </Link>

                        <Button
                            type="button"
                            :disabled="isSubmitting || form.processing"
                            class="flex-1 bg-indigo-600 text-white hover:bg-indigo-700 disabled:cursor-not-allowed disabled:bg-slate-400"
                            @click="submit"
                        >
                            <Save class="mr-2 h-4 w-4" />
                            {{ isSubmitting || form.processing ? 'Menyimpan...' : 'Simpan Perubahan' }}
                        </Button>
                    </div>
                </div>
            </div>

            <!-- Sidebar -->
            <div class="space-y-6 lg:col-span-1">
                <!-- Preview -->
                <div
                    class="rounded-2xl border border-gray-200 bg-white p-6 shadow-sm dark:border-gray-800 dark:bg-gray-900"
                >
                    <div class="mb-4 flex items-center gap-2">
                        <Tag class="h-5 w-5 text-indigo-500" />
                        <p class="text-sm font-semibold text-gray-900 dark:text-white">
                            Preview Kategori
                        </p>
                    </div>

                    <div class="flex items-start gap-3">
                        <div
                            :style="{ backgroundColor: form.color }"
                            class="flex h-14 w-14 shrink-0 items-center justify-center rounded-xl text-lg font-bold text-white shadow-sm"
                        >
                            {{ previewInitial }}
                        </div>

                        <div class="min-w-0">
                            <p class="truncate font-semibold text-gray-900 dark:text-white">
                                {{ form.name || 'Nama Kategori' }}
                            </p>

                            <p
                                :class="[
                                    'mt-1 text-sm font-medium',
                                    form.type === 'income'
                                        ? 'text-green-600 dark:text-green-400'
                                        : 'text-red-600 dark:text-red-400',
                                ]"
                            >
                                {{ typeLabel }}
                            </p>

                            <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">
                                Icon: {{ form.icon || '-' }}
                            </p>
                        </div>
                    </div>

                    <div class="mt-5 rounded-xl bg-gray-50 p-4 dark:bg-gray-800">
                        <p class="text-xs text-gray-500 dark:text-gray-400">
                            Deskripsi tipe
                        </p>

                        <p class="mt-1 text-sm text-gray-700 dark:text-gray-300">
                            {{ typeDescription }}
                        </p>
                    </div>

                    <div class="mt-4 flex flex-wrap items-center gap-2">
                        <span
                            :class="[
                                'inline-flex items-center gap-1 rounded-full px-3 py-1 text-xs font-medium',
                                form.type === 'income'
                                    ? 'bg-green-100 text-green-700 dark:bg-green-950 dark:text-green-300'
                                    : 'bg-red-100 text-red-700 dark:bg-red-950 dark:text-red-300',
                            ]"
                        >
                            <CheckCircle2 class="h-3 w-3" />
                            {{ typeLabel }}
                        </span>

                        <span class="rounded-full bg-gray-100 px-3 py-1 text-xs font-medium text-gray-700 dark:bg-gray-800 dark:text-gray-300">
                            {{ form.color }}
                        </span>
                    </div>
                </div>

                <!-- Original Info -->
                <div
                    class="rounded-2xl border border-indigo-200 bg-gradient-to-br from-indigo-50 to-indigo-100 p-6 shadow-sm dark:border-indigo-700 dark:from-indigo-900/20 dark:to-indigo-800/20"
                >
                    <div class="mb-4 flex items-center gap-2">
                        <Clock class="h-5 w-5 text-indigo-600 dark:text-indigo-400" />
                        <p class="text-sm font-semibold text-indigo-700 dark:text-indigo-400">
                            Data Awal
                        </p>
                    </div>

                    <div class="space-y-3">
                        <div class="flex justify-between gap-3 text-sm">
                            <span class="text-gray-700 dark:text-gray-300">
                                Nama:
                            </span>

                            <span class="font-medium text-gray-900 dark:text-white">
                                {{ category.name }}
                            </span>
                        </div>

                        <div class="flex justify-between gap-3 text-sm">
                            <span class="text-gray-700 dark:text-gray-300">
                                Tipe:
                            </span>

                            <span class="font-medium text-gray-900 dark:text-white">
                                {{ category.type === 'income' ? 'Pemasukan' : 'Pengeluaran' }}
                            </span>
                        </div>

                        <div class="flex justify-between gap-3 text-sm">
                            <span class="text-gray-700 dark:text-gray-300">
                                Warna:
                            </span>

                            <span class="font-mono font-medium text-gray-900 dark:text-white">
                                {{ category.color }}
                            </span>
                        </div>

                        <div class="flex justify-between gap-3 text-sm">
                            <span class="text-gray-700 dark:text-gray-300">
                                Icon:
                            </span>

                            <span class="font-medium text-gray-900 dark:text-white">
                                {{ category.icon }}
                            </span>
                        </div>
                    </div>
                </div>

                <!-- Info -->
                <div
                    class="rounded-2xl border border-blue-200 bg-blue-50 p-4 shadow-sm dark:border-blue-800 dark:bg-blue-900/20"
                >
                    <div class="flex gap-3">
                        <Info class="mt-0.5 h-5 w-5 shrink-0 text-blue-600 dark:text-blue-400" />

                        <div>
                            <p class="text-sm font-medium text-blue-900 dark:text-blue-400">
                                Tips Edit Kategori
                            </p>

                            <p class="mt-1 text-xs text-blue-800 dark:text-blue-300">
                                Mengubah kategori dapat memengaruhi tampilan transaksi, budget, dan laporan.
                            </p>
                        </div>
                    </div>
                </div>

                <!-- Type Info -->
                <div
                    class="rounded-2xl border border-gray-200 bg-white p-6 shadow-sm dark:border-gray-800 dark:bg-gray-900"
                >
                    <h3 class="font-semibold text-gray-900 dark:text-white">
                        Panduan Tipe
                    </h3>

                    <div class="mt-4 space-y-4">
                        <div class="flex gap-3">
                            <div
                                class="flex h-9 w-9 shrink-0 items-center justify-center rounded-lg bg-green-100 text-green-600 dark:bg-green-950 dark:text-green-300"
                            >
                                <TrendingUp class="h-5 w-5" />
                            </div>

                            <div>
                                <p class="text-sm font-medium text-gray-900 dark:text-white">
                                    Pemasukan
                                </p>
                                <p class="text-xs text-gray-500 dark:text-gray-400">
                                    Untuk gaji, bonus, hadiah, atau uang masuk lainnya.
                                </p>
                            </div>
                        </div>

                        <div class="flex gap-3">
                            <div
                                class="flex h-9 w-9 shrink-0 items-center justify-center rounded-lg bg-red-100 text-red-600 dark:bg-red-950 dark:text-red-300"
                            >
                                <TrendingDown class="h-5 w-5" />
                            </div>

                            <div>
                                <p class="text-sm font-medium text-gray-900 dark:text-white">
                                    Pengeluaran
                                </p>
                                <p class="text-xs text-gray-500 dark:text-gray-400">
                                    Untuk makan, belanja, transportasi, tagihan, dan kebutuhan lain.
                                </p>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Color Info -->
                <div
                    class="rounded-2xl border border-gray-200 bg-white p-6 shadow-sm dark:border-gray-800 dark:bg-gray-900"
                >
                    <div class="mb-4 flex items-center gap-2">
                        <Palette class="h-5 w-5 text-purple-500" />
                        <h3 class="font-semibold text-gray-900 dark:text-white">
                            Warna & Icon
                        </h3>
                    </div>

                    <div class="space-y-3 text-sm text-gray-600 dark:text-gray-400">
                        <p>
                            Warna kategori akan muncul di daftar transaksi, budget, dan laporan.
                        </p>

                        <p>
                            Icon disimpan sebagai teks agar mudah dipakai untuk tampilan custom.
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>
