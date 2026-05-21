<script setup lang="ts">
import { Head, Link, useForm } from '@inertiajs/vue3';
import { computed, watch } from 'vue';
import {
    ArrowLeft,
    Save,
    Repeat2,
    AlertCircle,
    TrendingUp,
    TrendingDown,
    Wallet,
    Tag,
    Calendar,
    Info,
} from 'lucide-vue-next';

import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';

defineOptions({
    layout: {
        breadcrumbs: [
            { title: 'Dashboard', href: '/dashboard' },
            { title: 'Transaksi Berulang', href: '/recurring-transactions' },
            { title: 'Tambah Recurring', href: '/recurring-transactions/create' },
        ],
    },
});

interface Account {
    id: number;
    name: string;
    type: string;
    current_balance: number;
}

interface Category {
    id: number;
    name: string;
    type: 'income' | 'expense';
    color: string;
    icon: string;
}

interface Props {
    accounts: Account[];
    categories: Category[];
}

const props = defineProps<Props>();

const form = useForm({
    financial_account_id: '',
    category_id: '',
    title: '',
    type: 'expense',
    amount: '',
    frequency: 'monthly',
    start_date: new Date().toISOString().split('T')[0],
    next_date: new Date().toISOString().split('T')[0],
    end_date: '',
    description: '',
    is_active: true,
});

const filteredCategories = computed(() => {
    return props.categories.filter((category) => category.type === form.type);
});

const selectedAccount = computed(() => {
    return props.accounts.find((account) => account.id === Number(form.financial_account_id || 0));
});

const selectedCategory = computed(() => {
    return props.categories.find((category) => category.id === Number(form.category_id || 0));
});

watch(
    () => form.type,
    () => {
        form.category_id = '';
    },
);

watch(
    () => form.start_date,
    (value) => {
        if (!form.next_date || form.next_date < value) {
            form.next_date = value;
        }
    },
);

const formatCurrency = (value: number | string | null | undefined) => {
    return new Intl.NumberFormat('id-ID', {
        style: 'currency',
        currency: 'IDR',
        minimumFractionDigits: 0,
        maximumFractionDigits: 0,
    }).format(Number(value || 0));
};

const getFrequencyLabel = (frequency: string) => {
    const labels: Record<string, string> = {
        daily: 'Harian',
        weekly: 'Mingguan',
        monthly: 'Bulanan',
        yearly: 'Tahunan',
    };

    return labels[frequency] || frequency;
};

const submit = () => {
    form.post('/recurring-transactions', {
        preserveScroll: true,
    });
};
</script>

<template>
    <Head title="Tambah Transaksi Berulang - Loovie Apps" />

    <div class="space-y-6 p-4 sm:p-6 lg:p-8">
        <div>
            <Link
                href="/recurring-transactions"
                class="mb-3 inline-flex items-center text-sm font-medium text-indigo-600 hover:text-indigo-700"
            >
                <ArrowLeft class="mr-2 h-4 w-4" />
                Kembali ke Transaksi Berulang
            </Link>

            <h1 class="text-2xl font-bold text-gray-900 sm:text-3xl dark:text-white">
                Tambah Transaksi Berulang
            </h1>

            <p class="mt-1 text-sm text-gray-600 dark:text-gray-400">
                Buat transaksi rutin seperti gaji, kos, listrik, cicilan, atau langganan.
            </p>
        </div>

        <div class="grid grid-cols-1 gap-6 lg:grid-cols-3">
            <!-- Form -->
            <div class="lg:col-span-2">
                <div class="space-y-6 rounded-2xl border border-gray-200 bg-white p-6 shadow-sm dark:border-gray-800 dark:bg-gray-900">
                    <!-- Title -->
                    <div>
                        <label class="mb-2 block text-sm font-semibold">
                            Judul
                            <span class="text-red-500">*</span>
                        </label>

                        <Input
                            v-model="form.title"
                            type="text"
                            placeholder="Contoh: Gaji Bulanan, Bayar Kos, Internet"
                            class="rounded-lg border-gray-200 bg-gray-50 dark:border-gray-700 dark:bg-gray-800"
                        />

                        <p v-if="form.errors.title" class="mt-2 flex items-center gap-2 text-sm text-red-600">
                            <AlertCircle class="h-4 w-4" />
                            {{ form.errors.title }}
                        </p>
                    </div>

                    <!-- Type -->
                    <div>
                        <label class="mb-3 block text-sm font-semibold">
                            Tipe
                            <span class="text-red-500">*</span>
                        </label>

                        <div class="grid grid-cols-1 gap-3 sm:grid-cols-2">
                            <label class="relative">
                                <input
                                    v-model="form.type"
                                    type="radio"
                                    value="income"
                                    class="peer sr-only"
                                />

                                <div class="cursor-pointer rounded-xl border-2 border-gray-200 bg-gray-50 p-4 transition-all peer-checked:border-emerald-500 peer-checked:bg-emerald-50 dark:border-gray-700 dark:bg-gray-800 dark:peer-checked:bg-emerald-900/20">
                                    <div class="flex items-start gap-3">
                                        <div class="flex h-10 w-10 items-center justify-center rounded-xl bg-emerald-100 text-emerald-600 dark:bg-emerald-950 dark:text-emerald-300">
                                            <TrendingUp class="h-5 w-5" />
                                        </div>

                                        <div>
                                            <p class="font-semibold text-emerald-700 dark:text-emerald-400">
                                                Pemasukan
                                            </p>
                                            <p class="mt-1 text-sm text-muted-foreground">
                                                Uang masuk berulang.
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

                                <div class="cursor-pointer rounded-xl border-2 border-gray-200 bg-gray-50 p-4 transition-all peer-checked:border-red-500 peer-checked:bg-red-50 dark:border-gray-700 dark:bg-gray-800 dark:peer-checked:bg-red-900/20">
                                    <div class="flex items-start gap-3">
                                        <div class="flex h-10 w-10 items-center justify-center rounded-xl bg-red-100 text-red-600 dark:bg-red-950 dark:text-red-300">
                                            <TrendingDown class="h-5 w-5" />
                                        </div>

                                        <div>
                                            <p class="font-semibold text-red-700 dark:text-red-400">
                                                Pengeluaran
                                            </p>
                                            <p class="mt-1 text-sm text-muted-foreground">
                                                Uang keluar berulang.
                                            </p>
                                        </div>
                                    </div>
                                </div>
                            </label>
                        </div>

                        <p v-if="form.errors.type" class="mt-2 flex items-center gap-2 text-sm text-red-600">
                            <AlertCircle class="h-4 w-4" />
                            {{ form.errors.type }}
                        </p>
                    </div>

                    <!-- Account + Category -->
                    <div class="grid grid-cols-1 gap-4 md:grid-cols-2">
                        <div>
                            <label class="mb-2 block text-sm font-semibold">
                                Akun
                                <span class="text-red-500">*</span>
                            </label>

                            <select
                                v-model="form.financial_account_id"
                                class="w-full rounded-lg border border-gray-200 bg-gray-50 px-4 py-2 text-gray-900 focus:ring-2 focus:ring-indigo-500 dark:border-gray-700 dark:bg-gray-800 dark:text-white"
                            >
                                <option value="">Pilih akun</option>
                                <option
                                    v-for="account in accounts"
                                    :key="account.id"
                                    :value="String(account.id)"
                                >
                                    {{ account.name }} - {{ formatCurrency(account.current_balance) }}
                                </option>
                            </select>

                            <p v-if="form.errors.financial_account_id" class="mt-2 flex items-center gap-2 text-sm text-red-600">
                                <AlertCircle class="h-4 w-4" />
                                {{ form.errors.financial_account_id }}
                            </p>
                        </div>

                        <div>
                            <label class="mb-2 block text-sm font-semibold">
                                Kategori
                                <span class="text-red-500">*</span>
                            </label>

                            <select
                                v-model="form.category_id"
                                class="w-full rounded-lg border border-gray-200 bg-gray-50 px-4 py-2 text-gray-900 focus:ring-2 focus:ring-indigo-500 dark:border-gray-700 dark:bg-gray-800 dark:text-white"
                            >
                                <option value="">Pilih kategori</option>
                                <option
                                    v-for="category in filteredCategories"
                                    :key="category.id"
                                    :value="String(category.id)"
                                >
                                    {{ category.name }}
                                </option>
                            </select>

                            <p v-if="form.errors.category_id" class="mt-2 flex items-center gap-2 text-sm text-red-600">
                                <AlertCircle class="h-4 w-4" />
                                {{ form.errors.category_id }}
                            </p>
                        </div>
                    </div>

                    <!-- Amount + Frequency -->
                    <div class="grid grid-cols-1 gap-4 md:grid-cols-2">
                        <div>
                            <label class="mb-2 block text-sm font-semibold">
                                Nominal
                                <span class="text-red-500">*</span>
                            </label>

                            <div class="relative">
                                <span class="absolute left-4 top-3 text-gray-500">Rp</span>

                                <Input
                                    v-model="form.amount"
                                    type="number"
                                    min="1"
                                    step="1000"
                                    placeholder="0"
                                    class="rounded-lg border-gray-200 bg-gray-50 pl-10 dark:border-gray-700 dark:bg-gray-800"
                                />
                            </div>

                            <p v-if="form.errors.amount" class="mt-2 flex items-center gap-2 text-sm text-red-600">
                                <AlertCircle class="h-4 w-4" />
                                {{ form.errors.amount }}
                            </p>
                        </div>

                        <div>
                            <label class="mb-2 block text-sm font-semibold">
                                Frekuensi
                                <span class="text-red-500">*</span>
                            </label>

                            <select
                                v-model="form.frequency"
                                class="w-full rounded-lg border border-gray-200 bg-gray-50 px-4 py-2 text-gray-900 focus:ring-2 focus:ring-indigo-500 dark:border-gray-700 dark:bg-gray-800 dark:text-white"
                            >
                                <option value="daily">Harian</option>
                                <option value="weekly">Mingguan</option>
                                <option value="monthly">Bulanan</option>
                                <option value="yearly">Tahunan</option>
                            </select>

                            <p v-if="form.errors.frequency" class="mt-2 flex items-center gap-2 text-sm text-red-600">
                                <AlertCircle class="h-4 w-4" />
                                {{ form.errors.frequency }}
                            </p>
                        </div>
                    </div>

                    <!-- Dates -->
                    <div class="grid grid-cols-1 gap-4 md:grid-cols-3">
                        <div>
                            <label class="mb-2 block text-sm font-semibold">
                                Tanggal Mulai
                                <span class="text-red-500">*</span>
                            </label>

                            <Input
                                v-model="form.start_date"
                                type="date"
                                class="rounded-lg border-gray-200 bg-gray-50 dark:border-gray-700 dark:bg-gray-800"
                            />

                            <p v-if="form.errors.start_date" class="mt-2 flex items-center gap-2 text-sm text-red-600">
                                <AlertCircle class="h-4 w-4" />
                                {{ form.errors.start_date }}
                            </p>
                        </div>

                        <div>
                            <label class="mb-2 block text-sm font-semibold">
                                Tanggal Generate Berikutnya
                                <span class="text-red-500">*</span>
                            </label>

                            <Input
                                v-model="form.next_date"
                                type="date"
                                class="rounded-lg border-gray-200 bg-gray-50 dark:border-gray-700 dark:bg-gray-800"
                            />

                            <p v-if="form.errors.next_date" class="mt-2 flex items-center gap-2 text-sm text-red-600">
                                <AlertCircle class="h-4 w-4" />
                                {{ form.errors.next_date }}
                            </p>
                        </div>

                        <div>
                            <label class="mb-2 block text-sm font-semibold">
                                Tanggal Akhir
                            </label>

                            <Input
                                v-model="form.end_date"
                                type="date"
                                class="rounded-lg border-gray-200 bg-gray-50 dark:border-gray-700 dark:bg-gray-800"
                            />

                            <p class="mt-1 text-xs text-muted-foreground">
                                Kosongkan jika tidak ada batas akhir.
                            </p>

                            <p v-if="form.errors.end_date" class="mt-2 flex items-center gap-2 text-sm text-red-600">
                                <AlertCircle class="h-4 w-4" />
                                {{ form.errors.end_date }}
                            </p>
                        </div>
                    </div>

                    <!-- Active -->
                    <div class="rounded-xl border bg-muted/40 p-4">
                        <label class="flex cursor-pointer items-start gap-3">
                            <input
                                v-model="form.is_active"
                                type="checkbox"
                                class="mt-1 h-4 w-4 rounded border-gray-300 text-indigo-600 focus:ring-indigo-500"
                            />

                            <div>
                                <p class="text-sm font-semibold">
                                    Aktifkan transaksi berulang
                                </p>
                                <p class="mt-1 text-xs text-muted-foreground">
                                    Jika aktif, transaksi ini bisa digenerate otomatis/manual sesuai tanggal berikutnya.
                                </p>
                            </div>
                        </label>
                    </div>

                    <!-- Description -->
                    <div>
                        <label class="mb-2 block text-sm font-semibold">
                            Deskripsi
                        </label>

                        <textarea
                            v-model="form.description"
                            rows="3"
                            placeholder="Contoh: pembayaran internet rumah..."
                            class="w-full rounded-lg border border-gray-200 bg-gray-50 px-4 py-2 text-gray-900 focus:ring-2 focus:ring-indigo-500 dark:border-gray-700 dark:bg-gray-800 dark:text-white"
                        ></textarea>

                        <p v-if="form.errors.description" class="mt-2 flex items-center gap-2 text-sm text-red-600">
                            <AlertCircle class="h-4 w-4" />
                            {{ form.errors.description }}
                        </p>
                    </div>

                    <!-- Actions -->
                    <div class="flex flex-col gap-3 border-t border-gray-200 pt-6 sm:flex-row dark:border-gray-700">
                        <Link href="/recurring-transactions" class="flex-1">
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
                            {{ form.processing ? 'Menyimpan...' : 'Simpan Recurring' }}
                        </Button>
                    </div>
                </div>
            </div>

            <!-- Sidebar -->
            <div class="space-y-6 lg:col-span-1">
                <div class="rounded-2xl border border-gray-200 bg-white p-6 shadow-sm dark:border-gray-800 dark:bg-gray-900">
                    <div class="mb-4 flex items-center gap-2">
                        <Repeat2 class="h-5 w-5 text-indigo-500" />
                        <p class="text-sm font-semibold">Preview Recurring</p>
                    </div>

                    <div class="space-y-4">
                        <div>
                            <p class="text-xs text-muted-foreground">Judul</p>
                            <p class="mt-1 font-semibold">{{ form.title || 'Judul Recurring' }}</p>
                        </div>

                        <div class="rounded-xl bg-muted/40 p-4">
                            <p class="text-xs text-muted-foreground">Nominal</p>
                            <p
                                :class="[
                                    'mt-1 text-2xl font-bold',
                                    form.type === 'income' ? 'text-emerald-500' : 'text-red-500',
                                ]"
                            >
                                {{ form.type === 'income' ? '+' : '-' }}
                                {{ formatCurrency(form.amount) }}
                            </p>
                        </div>

                        <div class="grid grid-cols-1 gap-3">
                            <div class="flex items-center gap-2 text-sm text-muted-foreground">
                                <Wallet class="h-4 w-4" />
                                {{ selectedAccount?.name || 'Akun belum dipilih' }}
                            </div>

                            <div class="flex items-center gap-2 text-sm text-muted-foreground">
                                <Tag class="h-4 w-4" />
                                {{ selectedCategory?.name || 'Kategori belum dipilih' }}
                            </div>

                            <div class="flex items-center gap-2 text-sm text-muted-foreground">
                                <Repeat2 class="h-4 w-4" />
                                {{ getFrequencyLabel(form.frequency) }}
                            </div>

                            <div class="flex items-center gap-2 text-sm text-muted-foreground">
                                <Calendar class="h-4 w-4" />
                                Next: {{ form.next_date || '-' }}
                            </div>
                        </div>

                        <div
                            :class="[
                                'rounded-xl p-4 text-sm',
                                form.type === 'income'
                                    ? 'bg-emerald-50 text-emerald-700 dark:bg-emerald-950/30 dark:text-emerald-300'
                                    : 'bg-red-50 text-red-700 dark:bg-red-950/30 dark:text-red-300',
                            ]"
                        >
                            Transaksi ini akan menjadi
                            <strong>{{ form.type === 'income' ? 'pemasukan' : 'pengeluaran' }}</strong>
                            berulang secara
                            <strong>{{ getFrequencyLabel(form.frequency).toLowerCase() }}</strong>.
                        </div>
                    </div>
                </div>

                <div class="rounded-2xl border border-blue-200 bg-blue-50 p-4 shadow-sm dark:border-blue-800 dark:bg-blue-900/20">
                    <div class="flex gap-3">
                        <Info class="mt-0.5 h-5 w-5 shrink-0 text-blue-600 dark:text-blue-400" />
                        <div>
                            <p class="text-sm font-medium text-blue-900 dark:text-blue-400">
                                Info Recurring
                            </p>
                            <p class="mt-1 text-xs text-blue-800 dark:text-blue-300">
                                Tombol generate akan membuat transaksi baru, mengubah saldo akun, lalu memajukan tanggal berikutnya.
                            </p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>
