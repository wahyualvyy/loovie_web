<script setup lang="ts">
import { Head, Link, useForm } from '@inertiajs/vue3';
import { computed, ref } from 'vue';
import {
    ArrowLeft,
    Upload,
    X,
    Save,
    Wallet,
    Tags,
    ReceiptText,
    TrendingUp,
    TrendingDown,
    FileText,
    Image as ImageIcon,
} from 'lucide-vue-next';

import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';

defineOptions({
    layout: {
        breadcrumbs: [
            { title: 'Dashboard', href: '/dashboard' },
            { title: 'Transaksi', href: '/transactions' },
            { title: 'Tambah Transaksi', href: '/transactions/create' },
        ],
    },
});

interface Account {
    id: number;
    name: string;
    current_balance: number;
}

interface Category {
    id: number;
    name: string;
    type: 'income' | 'expense';
    color: string;
}

interface Props {
    accounts: Account[];
    categories: Category[];
}

const props = defineProps<Props>();

const fileInput = ref<HTMLInputElement | null>(null);
const attachmentFile = ref<File | null>(null);
const attachmentPreview = ref<string | null>(null);

const form = useForm({
    financial_account_id: '',
    category_id: '',
    type: '',
    amount: '',
    transaction_date: new Date().toISOString().split('T')[0],
    description: '',
    attachment: null as File | null,
});

const selectedCategory = computed(() => {
    return props.categories.find(
        (category) => category.id === Number(form.category_id || 0),
    );
});

const selectedAccount = computed(() => {
    return props.accounts.find(
        (account) => account.id === Number(form.financial_account_id || 0),
    );
});

const incomeCategories = computed(() => {
    return props.categories.filter((category) => category.type === 'income');
});

const expenseCategories = computed(() => {
    return props.categories.filter((category) => category.type === 'expense');
});

const visibleCategories = computed(() => {
    if (form.type === 'income') return incomeCategories.value;
    if (form.type === 'expense') return expenseCategories.value;

    return props.categories;
});

const hasAccounts = computed(() => props.accounts.length > 0);
const hasCategories = computed(() => props.categories.length > 0);

const setTransactionType = (type: 'income' | 'expense') => {
    form.type = type;

    if (selectedCategory.value && selectedCategory.value.type !== type) {
        form.category_id = '';
    }
};

const handleCategoryChange = (event: Event) => {
    const categoryId = Number((event.target as HTMLSelectElement).value || 0);
    const category = props.categories.find((item) => item.id === categoryId);

    form.category_id = categoryId ? String(categoryId) : '';

    if (category) {
        form.type = category.type;
    }
};

const openFilePicker = () => {
    fileInput.value?.click();
};

const handleFileChange = (event: Event) => {
    const target = event.target as HTMLInputElement;
    const file = target.files?.[0];

    if (!file) return;

    attachmentFile.value = file;
    form.attachment = file;
    attachmentPreview.value = null;

    if (file.type.startsWith('image/')) {
        const reader = new FileReader();

        reader.onload = (readerEvent) => {
            attachmentPreview.value = readerEvent.target?.result as string;
        };

        reader.readAsDataURL(file);
    }
};

const removeAttachment = () => {
    attachmentFile.value = null;
    attachmentPreview.value = null;
    form.attachment = null;

    if (fileInput.value) {
        fileInput.value.value = '';
    }
};

const submit = () => {
    form.post('/transactions', {
        forceFormData: true,
        preserveScroll: true,
    });
};

const formatCurrency = (value: number | string | null | undefined) => {
    return new Intl.NumberFormat('id-ID', {
        style: 'currency',
        currency: 'IDR',
        minimumFractionDigits: 0,
        maximumFractionDigits: 0,
    }).format(Number(value || 0));
};

const getTypeLabel = (type: string) => {
    if (type === 'income') return 'Pemasukan';
    if (type === 'expense') return 'Pengeluaran';

    return '-';
};

const getAttachmentSize = computed(() => {
    if (!attachmentFile.value) return '';

    const size = attachmentFile.value.size;

    if (size >= 1024 * 1024) {
        return `${(size / 1024 / 1024).toFixed(2)} MB`;
    }

    return `${(size / 1024).toFixed(2)} KB`;
});
</script>

<template>
    <Head title="Tambah Transaksi - Loovie Apps" />

    <div class="space-y-6 p-4 sm:p-6 lg:p-8">
        <!-- Header -->
        <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
            <div>
                <Link
                    href="/transactions"
                    class="mb-3 inline-flex items-center text-sm font-medium text-indigo-600 hover:text-indigo-700"
                >
                    <ArrowLeft class="mr-2 h-4 w-4" />
                    Kembali ke Transaksi
                </Link>

                <h1 class="text-2xl font-bold text-gray-900 sm:text-3xl dark:text-white">
                    Tambah Transaksi
                </h1>

                <p class="mt-1 text-sm text-gray-600 dark:text-gray-400">
                    Catat pemasukan atau pengeluaran baru ke akun keuanganmu.
                </p>
            </div>
        </div>

        <div
            v-if="!hasAccounts || !hasCategories"
            class="rounded-xl border border-yellow-200 bg-yellow-50 p-4 text-sm text-yellow-800 dark:border-yellow-900 dark:bg-yellow-950/40 dark:text-yellow-200"
        >
            <p class="font-semibold">
                Data awal belum lengkap
            </p>

            <p class="mt-1">
                Kamu perlu memiliki akun keuangan dan kategori sebelum membuat transaksi.
            </p>

            <div class="mt-3 flex flex-wrap gap-2">
                <Link
                    v-if="!hasAccounts"
                    href="/financial-accounts/create"
                    class="rounded-lg bg-blue-600 px-3 py-2 text-xs font-medium text-white hover:bg-blue-700"
                >
                    Tambah Akun
                </Link>

                <Link
                    v-if="!hasCategories"
                    href="/categories/create"
                    class="rounded-lg bg-indigo-600 px-3 py-2 text-xs font-medium text-white hover:bg-indigo-700"
                >
                    Tambah Kategori
                </Link>
            </div>
        </div>

        <div class="grid grid-cols-1 gap-6 lg:grid-cols-3">
            <!-- Form -->
            <div class="lg:col-span-2">
                <div
                    class="space-y-6 rounded-2xl border border-gray-200 bg-white p-6 shadow-sm dark:border-gray-800 dark:bg-gray-900"
                >
                    <!-- Account -->
                    <div>
                        <label class="mb-2 block text-sm font-semibold text-gray-900 dark:text-white">
                            Akun Keuangan
                            <span class="text-red-500">*</span>
                        </label>

                        <select
                            v-model="form.financial_account_id"
                            class="w-full rounded-lg border border-gray-200 bg-gray-50 px-4 py-2 text-gray-900 focus:border-transparent focus:ring-2 focus:ring-indigo-500 dark:border-gray-700 dark:bg-gray-800 dark:text-white"
                        >
                            <option value="">
                                Pilih akun keuangan
                            </option>

                            <option
                                v-for="account in accounts"
                                :key="account.id"
                                :value="String(account.id)"
                            >
                                {{ account.name }} - {{ formatCurrency(account.current_balance) }}
                            </option>
                        </select>

                        <p
                            v-if="form.errors.financial_account_id"
                            class="mt-2 text-sm text-red-600"
                        >
                            {{ form.errors.financial_account_id }}
                        </p>
                    </div>

                    <!-- Type -->
                    <div>
                        <label class="mb-3 block text-sm font-semibold text-gray-900 dark:text-white">
                            Tipe Transaksi
                            <span class="text-red-500">*</span>
                        </label>

                        <div class="grid grid-cols-1 gap-3 sm:grid-cols-2">
                            <button
                                type="button"
                                :class="[
                                    'flex items-center justify-center gap-2 rounded-lg px-4 py-3 font-medium transition-all',
                                    form.type === 'income'
                                        ? 'border-2 border-green-500 bg-green-100 text-green-700 dark:bg-green-900/30 dark:text-green-400'
                                        : 'border border-gray-200 bg-gray-50 text-gray-700 hover:border-green-300 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-300',
                                ]"
                                @click="setTransactionType('income')"
                            >
                                <TrendingUp class="h-4 w-4" />
                                Pemasukan
                            </button>

                            <button
                                type="button"
                                :class="[
                                    'flex items-center justify-center gap-2 rounded-lg px-4 py-3 font-medium transition-all',
                                    form.type === 'expense'
                                        ? 'border-2 border-red-500 bg-red-100 text-red-700 dark:bg-red-900/30 dark:text-red-400'
                                        : 'border border-gray-200 bg-gray-50 text-gray-700 hover:border-red-300 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-300',
                                ]"
                                @click="setTransactionType('expense')"
                            >
                                <TrendingDown class="h-4 w-4" />
                                Pengeluaran
                            </button>
                        </div>

                        <p v-if="form.errors.type" class="mt-2 text-sm text-red-600">
                            {{ form.errors.type }}
                        </p>
                    </div>

                    <!-- Category -->
                    <div>
                        <label class="mb-2 block text-sm font-semibold text-gray-900 dark:text-white">
                            Kategori
                            <span class="text-red-500">*</span>
                        </label>

                        <div
                            v-if="categories.length === 0"
                            class="rounded-lg border border-yellow-200 bg-yellow-50 p-4 dark:border-yellow-800 dark:bg-yellow-900/20"
                        >
                            <p class="text-sm text-yellow-700 dark:text-yellow-400">
                                Belum ada kategori.
                                <Link
                                    href="/categories/create"
                                    class="font-semibold underline hover:no-underline"
                                >
                                    Buat kategori dulu
                                </Link>
                            </p>
                        </div>

                        <select
                            v-else
                            v-model="form.category_id"
                            class="w-full rounded-lg border border-gray-200 bg-gray-50 px-4 py-2 text-gray-900 focus:border-transparent focus:ring-2 focus:ring-indigo-500 dark:border-gray-700 dark:bg-gray-800 dark:text-white"
                            @change="handleCategoryChange"
                        >
                            <option value="">
                                Pilih kategori
                            </option>

                            <option
                                v-for="category in visibleCategories"
                                :key="category.id"
                                :value="String(category.id)"
                            >
                                {{ category.name }} - {{ getTypeLabel(category.type) }}
                            </option>
                        </select>

                        <p
                            v-if="form.errors.category_id"
                            class="mt-2 text-sm text-red-600"
                        >
                            {{ form.errors.category_id }}
                        </p>

                        <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">
                            Jika tipe transaksi dipilih, daftar kategori akan menyesuaikan tipe tersebut.
                        </p>
                    </div>

                    <!-- Amount -->
                    <div>
                        <label class="mb-2 block text-sm font-semibold text-gray-900 dark:text-white">
                            Nominal
                            <span class="text-red-500">*</span>
                        </label>

                        <div class="relative">
                            <span class="absolute left-4 top-3 text-gray-500 dark:text-gray-400">
                                Rp
                            </span>

                            <Input
                                v-model="form.amount"
                                type="number"
                                placeholder="0"
                                min="1"
                                step="1000"
                                class="rounded-lg border-gray-200 bg-gray-50 pl-10 dark:border-gray-700 dark:bg-gray-800"
                            />
                        </div>

                        <p v-if="form.errors.amount" class="mt-2 text-sm text-red-600">
                            {{ form.errors.amount }}
                        </p>
                    </div>

                    <!-- Date -->
                    <div>
                        <label class="mb-2 block text-sm font-semibold text-gray-900 dark:text-white">
                            Tanggal Transaksi
                            <span class="text-red-500">*</span>
                        </label>

                        <Input
                            v-model="form.transaction_date"
                            type="date"
                            class="w-full rounded-lg border-gray-200 bg-gray-50 dark:border-gray-700 dark:bg-gray-800"
                        />

                        <p
                            v-if="form.errors.transaction_date"
                            class="mt-2 text-sm text-red-600"
                        >
                            {{ form.errors.transaction_date }}
                        </p>
                    </div>

                    <!-- Description -->
                    <div>
                        <label class="mb-2 block text-sm font-semibold text-gray-900 dark:text-white">
                            Deskripsi
                        </label>

                        <textarea
                            v-model="form.description"
                            placeholder="Contoh: makan siang, gaji bulan ini, bayar internet..."
                            rows="3"
                            class="w-full rounded-lg border border-gray-200 bg-gray-50 px-4 py-2 text-gray-900 focus:border-transparent focus:ring-2 focus:ring-indigo-500 dark:border-gray-700 dark:bg-gray-800 dark:text-white"
                        ></textarea>

                        <p
                            v-if="form.errors.description"
                            class="mt-2 text-sm text-red-600"
                        >
                            {{ form.errors.description }}
                        </p>
                    </div>

                    <!-- Attachment -->
                    <div>
                        <label class="mb-2 block text-sm font-semibold text-gray-900 dark:text-white">
                            Lampiran
                        </label>

                        <input
                            ref="fileInput"
                            type="file"
                            accept="image/jpg,image/jpeg,image/png,application/pdf"
                            class="hidden"
                            @change="handleFileChange"
                        />

                        <button
                            type="button"
                            class="flex w-full flex-col items-center justify-center rounded-xl border-2 border-dashed border-gray-300 p-6 text-center transition-colors hover:border-indigo-500 dark:border-gray-600"
                            @click="openFilePicker"
                        >
                            <Upload class="mb-2 h-8 w-8 text-gray-400" />

                            <p class="text-sm font-medium text-gray-700 dark:text-gray-300">
                                Klik untuk upload bukti transaksi
                            </p>

                            <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">
                                JPG, PNG, atau PDF maksimal 5MB
                            </p>
                        </button>

                        <p
                            v-if="form.errors.attachment"
                            class="mt-2 text-sm text-red-600"
                        >
                            {{ form.errors.attachment }}
                        </p>

                        <div
                            v-if="attachmentFile"
                            class="mt-4 rounded-xl border bg-gray-50 p-4 dark:border-gray-800 dark:bg-gray-800"
                        >
                            <div class="flex items-start justify-between gap-3">
                                <div class="flex min-w-0 items-start gap-3">
                                    <ImageIcon
                                        v-if="attachmentPreview"
                                        class="mt-0.5 h-5 w-5 shrink-0 text-blue-500"
                                    />

                                    <FileText
                                        v-else
                                        class="mt-0.5 h-5 w-5 shrink-0 text-red-500"
                                    />

                                    <div class="min-w-0">
                                        <p class="truncate text-sm font-medium text-gray-900 dark:text-white">
                                            {{ attachmentFile.name }}
                                        </p>
                                        <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">
                                            Ukuran: {{ getAttachmentSize }}
                                        </p>
                                    </div>
                                </div>

                                <button
                                    type="button"
                                    class="rounded-lg p-1 text-gray-500 hover:bg-gray-200 hover:text-gray-900 dark:hover:bg-gray-700 dark:hover:text-white"
                                    @click="removeAttachment"
                                >
                                    <X class="h-4 w-4" />
                                </button>
                            </div>

                            <img
                                v-if="attachmentPreview"
                                :src="attachmentPreview"
                                alt="Preview lampiran"
                                class="mt-4 h-48 w-full rounded-lg object-cover"
                            />
                        </div>
                    </div>

                    <!-- Form Actions -->
                    <div class="flex flex-col gap-3 border-t border-gray-200 pt-6 sm:flex-row dark:border-gray-700">
                        <Link href="/transactions" class="flex-1">
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
                            :disabled="form.processing"
                            class="flex-1 bg-indigo-600 text-white hover:bg-indigo-700 disabled:cursor-not-allowed disabled:bg-slate-400"
                            @click="submit"
                        >
                            <Save class="mr-2 h-4 w-4" />
                            {{ form.processing ? 'Menyimpan...' : 'Simpan Transaksi' }}
                        </Button>
                    </div>
                </div>
            </div>

            <!-- Preview Sidebar -->
            <div class="space-y-6 lg:col-span-1">
                <!-- Category Preview -->
                <div
                    v-if="selectedCategory"
                    class="rounded-2xl border border-gray-200 bg-white p-6 shadow-sm dark:border-gray-800 dark:bg-gray-900"
                >
                    <div class="mb-3 flex items-center gap-2">
                        <Tags class="h-5 w-5 text-indigo-500" />
                        <p class="text-sm font-semibold text-gray-900 dark:text-white">
                            Preview Kategori
                        </p>
                    </div>

                    <div class="flex items-center gap-3">
                        <div
                            :style="{ backgroundColor: selectedCategory.color }"
                            class="flex h-12 w-12 items-center justify-center rounded-xl text-lg font-semibold text-white"
                        >
                            {{ selectedCategory.name.charAt(0) }}
                        </div>

                        <div>
                            <p class="font-medium text-gray-900 dark:text-white">
                                {{ selectedCategory.name }}
                            </p>

                            <p
                                :class="[
                                    'text-xs font-medium',
                                    selectedCategory.type === 'income'
                                        ? 'text-green-600'
                                        : 'text-red-600',
                                ]"
                            >
                                {{ getTypeLabel(selectedCategory.type) }}
                            </p>
                        </div>
                    </div>
                </div>

                <!-- Account Preview -->
                <div
                    v-if="selectedAccount"
                    class="rounded-2xl border border-gray-200 bg-white p-6 shadow-sm dark:border-gray-800 dark:bg-gray-900"
                >
                    <div class="mb-3 flex items-center gap-2">
                        <Wallet class="h-5 w-5 text-blue-500" />
                        <p class="text-sm font-semibold text-gray-900 dark:text-white">
                            Preview Akun
                        </p>
                    </div>

                    <p class="text-sm text-gray-600 dark:text-gray-400">
                        {{ selectedAccount.name }}
                    </p>

                    <p class="mt-2 text-2xl font-bold text-gray-900 dark:text-white">
                        {{ formatCurrency(selectedAccount.current_balance) }}
                    </p>
                </div>

                <!-- Summary -->
                <div
                    v-if="form.amount && selectedCategory"
                    class="rounded-2xl border border-indigo-200 bg-gradient-to-br from-indigo-50 to-indigo-100 p-6 shadow-sm dark:border-indigo-700 dark:from-indigo-900/20 dark:to-indigo-800/20"
                >
                    <div class="mb-4 flex items-center gap-2">
                        <ReceiptText class="h-5 w-5 text-indigo-600 dark:text-indigo-400" />
                        <p class="text-sm font-semibold text-indigo-700 dark:text-indigo-400">
                            Ringkasan Transaksi
                        </p>
                    </div>

                    <div class="space-y-3">
                        <div class="flex justify-between gap-3 text-sm">
                            <span class="text-gray-700 dark:text-gray-300">
                                Tipe:
                            </span>

                            <span
                                :class="[
                                    'font-medium',
                                    form.type === 'income'
                                        ? 'text-green-600'
                                        : 'text-red-600',
                                ]"
                            >
                                {{ getTypeLabel(form.type) }}
                            </span>
                        </div>

                        <div class="flex justify-between gap-3 text-sm">
                            <span class="text-gray-700 dark:text-gray-300">
                                Kategori:
                            </span>

                            <span class="font-medium text-gray-900 dark:text-white">
                                {{ selectedCategory.name }}
                            </span>
                        </div>

                        <div class="flex justify-between gap-3 text-sm">
                            <span class="text-gray-700 dark:text-gray-300">
                                Nominal:
                            </span>

                            <span class="font-medium text-gray-900 dark:text-white">
                                {{ formatCurrency(Number(form.amount) || 0) }}
                            </span>
                        </div>

                        <div class="flex justify-between gap-3 text-sm">
                            <span class="text-gray-700 dark:text-gray-300">
                                Akun:
                            </span>

                            <span class="font-medium text-gray-900 dark:text-white">
                                {{ selectedAccount?.name || '-' }}
                            </span>
                        </div>
                    </div>
                </div>

                <!-- Help Card -->
                <div
                    class="rounded-2xl border border-gray-200 bg-white p-6 shadow-sm dark:border-gray-800 dark:bg-gray-900"
                >
                    <h3 class="font-semibold text-gray-900 dark:text-white">
                        Tips
                    </h3>

                    <ul class="mt-3 space-y-2 text-sm text-gray-600 dark:text-gray-400">
                        <li>• Pilih akun yang saldo/transaksinya ingin dicatat.</li>
                        <li>• Pilih tipe agar kategori lebih mudah difilter.</li>
                        <li>• Lampiran bisa digunakan untuk bukti pembayaran.</li>
                    </ul>
                </div>
            </div>
        </div>
    </div>
</template>
