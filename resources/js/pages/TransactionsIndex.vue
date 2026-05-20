<script setup lang="ts">
import { Head, Link, router, useForm } from '@inertiajs/vue3';
import { ref, computed } from 'vue';
import {
    Edit2,
    Trash2,
    Plus,
    Search,
    Download,
    Printer,
    Tag,
    ReceiptText,
    TrendingUp,
    TrendingDown,
} from 'lucide-vue-next';

import DeleteConfirmModal from '@/components/DeleteConfirmModal.vue';
import EmptyState from '@/components/EmptyState.vue';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';

defineOptions({
    layout: {
        breadcrumbs: [
            { title: 'Dashboard', href: '/dashboard' },
            { title: 'Transaksi', href: '/transactions' },
        ],
    },
});

interface Account {
    id: number;
    name: string;
}

interface Category {
    id: number;
    name: string;
    type: 'income' | 'expense';
    color: string;
}

interface TransactionItem {
    id: number;
    financial_account_id: number;
    category_id: number;
    type: 'income' | 'expense';
    amount: number;
    transaction_date: string;
    description: string | null;
    attachment: string | null;
    created_at: string;
    account: Account;
    category: Category;
}

interface Props {
    transactions: {
        data: TransactionItem[];
        total: number;
        per_page: number;
        current_page: number;
        last_page: number;
        from: number;
        to: number;
    };
    accounts: Account[];
    categories: Category[];
    filters: {
        search: string | null;
        account_id: string | null;
        category_id: string | null;
        type: string | null;
        date_from: string | null;
        date_to: string | null;
        month: string | null;
    };
}

const props = defineProps<Props>();

const showDeleteModal = ref(false);
const transactionToDelete = ref<TransactionItem | null>(null);
const deleteProcessing = ref(false);

const form = useForm({
    search: props.filters.search || '',
    account_id: props.filters.account_id || '',
    category_id: props.filters.category_id || '',
    type: props.filters.type || '',
    date_from: props.filters.date_from || '',
    date_to: props.filters.date_to || '',
    month: props.filters.month || '',
});

const totalIncome = computed(() => {
    return props.transactions.data
        .filter((transaction) => transaction.type === 'income')
        .reduce((sum, transaction) => sum + Number(transaction.amount), 0);
});

const totalExpense = computed(() => {
    return props.transactions.data
        .filter((transaction) => transaction.type === 'expense')
        .reduce((sum, transaction) => sum + Number(transaction.amount), 0);
});

const isFiltered = computed(() => {
    return Boolean(
        form.search ||
            form.account_id ||
            form.category_id ||
            form.type ||
            form.date_from ||
            form.date_to ||
            form.month,
    );
});

const openDeleteModal = (transaction: TransactionItem) => {
    transactionToDelete.value = transaction;
    showDeleteModal.value = true;
};

const closeDeleteModal = () => {
    if (deleteProcessing.value) return;

    transactionToDelete.value = null;
    showDeleteModal.value = false;
};

const deleteTransaction = () => {
    if (!transactionToDelete.value) return;

    deleteProcessing.value = true;

    router.delete(`/transactions/${transactionToDelete.value.id}`, {
        preserveScroll: true,
        onSuccess: () => closeDeleteModal(),
        onFinish: () => {
            deleteProcessing.value = false;
        },
    });
};

const applyFilters = () => {
    form.get('/transactions', {
        preserveScroll: true,
        preserveState: true,
    });
};

const resetFilters = () => {
    form.reset();
    router.get('/transactions', {}, {
        preserveScroll: true,
        preserveState: true,
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

const formatDate = (date: string) => {
    return new Date(date).toLocaleDateString('id-ID', {
        day: 'numeric',
        month: 'short',
        year: 'numeric',
    });
};

const getDeleteItemName = computed(() => {
    if (!transactionToDelete.value) return '';

    const transaction = transactionToDelete.value;
    const category = transaction.category?.name || 'Transaksi';
    const amount = formatCurrency(transaction.amount);

    return `${category} - ${amount}`;
});
</script>

<template>
    <Head title="Transaksi - Loovie Apps" />

    <div class="space-y-6 p-4 sm:p-6 lg:p-8">
        <!-- Header -->
        <div
            class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between"
        >
            <div>
                <h1 class="text-3xl font-bold text-gray-900 sm:text-4xl dark:text-white">
                    Transaksi
                </h1>
                <p class="mt-1 text-gray-600 dark:text-gray-400">
                    Kelola semua pemasukan dan pengeluaranmu.
                </p>
            </div>

            <Link href="/transactions/create">
                <Button class="bg-indigo-600 text-white hover:bg-indigo-700">
                    <Plus class="mr-2 h-4 w-4" />
                    Tambah Transaksi
                </Button>
            </Link>
        </div>

        <!-- Summary Stats -->
        <div class="grid grid-cols-1 gap-4 md:grid-cols-2">
            <div
                class="rounded-xl border border-green-200 bg-green-50 p-5 shadow-sm dark:border-green-800 dark:bg-green-900/20"
            >
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm font-medium text-green-700 dark:text-green-400">
                            Total Pemasukan
                        </p>
                        <p class="mt-2 text-2xl font-bold text-green-600 sm:text-3xl dark:text-green-400">
                            {{ formatCurrency(totalIncome) }}
                        </p>
                    </div>

                    <TrendingUp class="h-8 w-8 text-green-600 dark:text-green-400" />
                </div>
            </div>

            <div
                class="rounded-xl border border-red-200 bg-red-50 p-5 shadow-sm dark:border-red-800 dark:bg-red-900/20"
            >
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm font-medium text-red-700 dark:text-red-400">
                            Total Pengeluaran
                        </p>
                        <p class="mt-2 text-2xl font-bold text-red-600 sm:text-3xl dark:text-red-400">
                            {{ formatCurrency(totalExpense) }}
                        </p>
                    </div>

                    <TrendingDown class="h-8 w-8 text-red-600 dark:text-red-400" />
                </div>
            </div>
        </div>

        <!-- Filters -->
        <div
            class="space-y-4 rounded-xl border border-gray-200 bg-white p-4 shadow-sm dark:border-gray-800 dark:bg-gray-900"
        >
            <div class="grid grid-cols-1 gap-4 md:grid-cols-2 lg:grid-cols-4">
                <div class="relative">
                    <Search class="absolute left-3 top-3 h-5 w-5 text-gray-400" />
                    <Input
                        v-model="form.search"
                        type="text"
                        placeholder="Cari deskripsi..."
                        class="rounded-lg border-gray-200 bg-gray-50 pl-10 dark:border-gray-700 dark:bg-gray-800"
                    />
                </div>

                <select
                    v-model="form.account_id"
                    class="rounded-lg border border-gray-200 bg-gray-50 px-4 py-2 text-gray-900 focus:ring-2 focus:ring-indigo-500 dark:border-gray-700 dark:bg-gray-800 dark:text-white"
                >
                    <option value="">Semua Akun</option>
                    <option
                        v-for="account in accounts"
                        :key="account.id"
                        :value="String(account.id)"
                    >
                        {{ account.name }}
                    </option>
                </select>

                <select
                    v-model="form.category_id"
                    class="rounded-lg border border-gray-200 bg-gray-50 px-4 py-2 text-gray-900 focus:ring-2 focus:ring-indigo-500 dark:border-gray-700 dark:bg-gray-800 dark:text-white"
                >
                    <option value="">Semua Kategori</option>
                    <option
                        v-for="category in categories"
                        :key="category.id"
                        :value="String(category.id)"
                    >
                        {{ category.name }}
                    </option>
                </select>

                <select
                    v-model="form.type"
                    class="rounded-lg border border-gray-200 bg-gray-50 px-4 py-2 text-gray-900 focus:ring-2 focus:ring-indigo-500 dark:border-gray-700 dark:bg-gray-800 dark:text-white"
                >
                    <option value="">Semua Tipe</option>
                    <option value="income">Pemasukan</option>
                    <option value="expense">Pengeluaran</option>
                </select>
            </div>

            <div class="grid grid-cols-1 gap-4 md:grid-cols-3">
                <div>
                    <label class="mb-1 block text-sm text-gray-600 dark:text-gray-400">
                        Dari Tanggal
                    </label>
                    <Input
                        v-model="form.date_from"
                        type="date"
                        class="w-full rounded-lg border-gray-200 bg-gray-50 dark:border-gray-700 dark:bg-gray-800"
                    />
                </div>

                <div>
                    <label class="mb-1 block text-sm text-gray-600 dark:text-gray-400">
                        Sampai Tanggal
                    </label>
                    <Input
                        v-model="form.date_to"
                        type="date"
                        class="w-full rounded-lg border-gray-200 bg-gray-50 dark:border-gray-700 dark:bg-gray-800"
                    />
                </div>

                <div>
                    <label class="mb-1 block text-sm text-gray-600 dark:text-gray-400">
                        Atau Pilih Bulan
                    </label>
                    <Input
                        v-model="form.month"
                        type="month"
                        class="w-full rounded-lg border-gray-200 bg-gray-50 dark:border-gray-700 dark:bg-gray-800"
                    />
                </div>
            </div>

            <div class="flex justify-end gap-2">
                <Button
                    variant="outline"
                    class="text-gray-700 dark:text-gray-300"
                    @click="resetFilters"
                >
                    Reset
                </Button>

                <Button
                    class="bg-indigo-600 text-white hover:bg-indigo-700"
                    @click="applyFilters"
                >
                    Terapkan Filter
                </Button>
            </div>
        </div>

        <!-- Export/Print Buttons -->
        <div class="flex flex-wrap gap-2">
            <a
                href="/transactions/export/csv"
                class="inline-flex items-center gap-2 rounded-lg bg-blue-500 px-4 py-2 text-white transition hover:bg-blue-600"
            >
                <Download class="h-4 w-4" />
                Export CSV
            </a>

            <a
                href="/transactions/export/print"
                target="_blank"
                class="inline-flex items-center gap-2 rounded-lg bg-gray-500 px-4 py-2 text-white transition hover:bg-gray-600"
            >
                <Printer class="h-4 w-4" />
                Print Report
            </a>

            <Link
                href="/categories"
                class="inline-flex items-center gap-2 rounded-lg bg-gray-500 px-4 py-2 text-white transition hover:bg-gray-600"
            >
                <Tag class="h-4 w-4" />
                Kategori
            </Link>
        </div>

        <!-- Empty State -->
        <EmptyState
            v-if="transactions.data.length === 0"
            :icon="ReceiptText"
            :title="isFiltered ? 'Transaksi tidak ditemukan' : 'Belum ada transaksi'"
            :description="
                isFiltered
                    ? 'Tidak ada transaksi yang cocok dengan filter yang kamu gunakan.'
                    : 'Mulai catat pemasukan atau pengeluaran pertamamu.'
            "
            :action-label="isFiltered ? 'Reset Filter' : 'Tambah Transaksi'"
            :button-type="isFiltered ? 'button' : 'link'"
            :action-href="isFiltered ? '' : '/transactions/create'"
            @action="resetFilters"
        />

        <!-- Transactions Table -->
        <div
            v-else
            class="overflow-hidden rounded-xl border border-gray-200 bg-white shadow-sm dark:border-gray-800 dark:bg-gray-900"
        >
            <!-- Desktop View -->
            <div class="hidden overflow-x-auto md:block">
                <table class="w-full">
                    <thead
                        class="border-b border-gray-200 bg-gray-50 dark:border-gray-700 dark:bg-gray-800"
                    >
                        <tr>
                            <th class="px-6 py-3 text-left text-sm font-semibold text-gray-900 dark:text-white">
                                Tanggal
                            </th>
                            <th class="px-6 py-3 text-left text-sm font-semibold text-gray-900 dark:text-white">
                                Deskripsi
                            </th>
                            <th class="px-6 py-3 text-left text-sm font-semibold text-gray-900 dark:text-white">
                                Akun
                            </th>
                            <th class="px-6 py-3 text-left text-sm font-semibold text-gray-900 dark:text-white">
                                Kategori
                            </th>
                            <th class="px-6 py-3 text-right text-sm font-semibold text-gray-900 dark:text-white">
                                Nominal
                            </th>
                            <th class="px-6 py-3 text-center text-sm font-semibold text-gray-900 dark:text-white">
                                Aksi
                            </th>
                        </tr>
                    </thead>

                    <tbody class="divide-y divide-gray-200 dark:divide-gray-700">
                        <tr
                            v-for="transaction in transactions.data"
                            :key="transaction.id"
                            class="transition-colors hover:bg-gray-50 dark:hover:bg-gray-800/50"
                        >
                            <td class="px-6 py-4 text-sm font-medium text-gray-900 dark:text-white">
                                {{ formatDate(transaction.transaction_date) }}
                            </td>

                            <td class="px-6 py-4 text-sm text-gray-600 dark:text-gray-400">
                                {{ transaction.description || '-' }}
                            </td>

                            <td class="px-6 py-4 text-sm text-gray-900 dark:text-white">
                                {{ transaction.account?.name || '-' }}
                            </td>

                            <td class="px-6 py-4 text-sm">
                                <span
                                    class="rounded-full px-3 py-1 text-xs font-medium"
                                    :style="{
                                        backgroundColor: transaction.category?.color
                                            ? transaction.category.color + '20'
                                            : '#f3f4f6',
                                        color: transaction.category?.color || '#374151',
                                    }"
                                >
                                    {{ transaction.category?.name || '-' }}
                                </span>
                            </td>

                            <td
                                :class="[
                                    'px-6 py-4 text-right text-sm font-semibold',
                                    transaction.type === 'income'
                                        ? 'text-green-600 dark:text-green-400'
                                        : 'text-red-600 dark:text-red-400',
                                ]"
                            >
                                {{ transaction.type === 'income' ? '+' : '-' }}
                                {{ formatCurrency(transaction.amount) }}
                            </td>

                            <td class="px-6 py-4">
                                <div class="flex items-center justify-center gap-2">
                                    <Link
                                        :href="`/transactions/${transaction.id}/edit`"
                                        class="inline-flex items-center gap-1 rounded-lg border px-3 py-2 text-xs text-blue-600 transition hover:bg-blue-50 dark:border-blue-900 dark:hover:bg-blue-950/40"
                                    >
                                        <Edit2 class="h-4 w-4" />
                                        Edit
                                    </Link>

                                    <button
                                        type="button"
                                        class="inline-flex items-center gap-1 rounded-lg border border-red-200 px-3 py-2 text-xs text-red-600 transition hover:bg-red-50 dark:border-red-900 dark:hover:bg-red-950/40"
                                        @click="openDeleteModal(transaction)"
                                    >
                                        <Trash2 class="h-4 w-4" />
                                        Hapus
                                    </button>
                                </div>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <!-- Mobile View -->
            <div class="md:hidden">
                <div
                    v-for="transaction in transactions.data"
                    :key="transaction.id"
                    class="border-b border-gray-200 p-4 last:border-b-0 dark:border-gray-700"
                >
                    <div class="mb-3 flex items-start justify-between gap-3">
                        <div>
                            <p class="font-medium text-gray-900 dark:text-white">
                                {{ transaction.category?.name || '-' }}
                            </p>
                            <p class="text-xs text-gray-500 dark:text-gray-400">
                                {{ formatDate(transaction.transaction_date) }}
                            </p>
                        </div>

                        <div class="flex items-center gap-2">
                            <Link
                                :href="`/transactions/${transaction.id}/edit`"
                                class="rounded-lg border px-2 py-2 text-blue-600"
                            >
                                <Edit2 class="h-4 w-4" />
                            </Link>

                            <button
                                type="button"
                                class="rounded-lg border border-red-200 px-2 py-2 text-red-600"
                                @click="openDeleteModal(transaction)"
                            >
                                <Trash2 class="h-4 w-4" />
                            </button>
                        </div>
                    </div>

                    <div class="space-y-2 text-sm">
                        <div class="flex justify-between gap-3">
                            <span class="text-gray-600 dark:text-gray-400">
                                Akun:
                            </span>
                            <span class="font-medium text-gray-900 dark:text-white">
                                {{ transaction.account?.name || '-' }}
                            </span>
                        </div>

                        <div class="flex justify-between gap-3">
                            <span class="text-gray-600 dark:text-gray-400">
                                Deskripsi:
                            </span>
                            <span class="text-right font-medium text-gray-900 dark:text-white">
                                {{ transaction.description || '-' }}
                            </span>
                        </div>

                        <div
                            class="flex justify-between gap-3 border-t border-gray-200 pt-2 dark:border-gray-700"
                        >
                            <span class="text-gray-600 dark:text-gray-400">
                                Nominal:
                            </span>
                            <span
                                :class="[
                                    'font-bold',
                                    transaction.type === 'income'
                                        ? 'text-green-600 dark:text-green-400'
                                        : 'text-red-600 dark:text-red-400',
                                ]"
                            >
                                {{ transaction.type === 'income' ? '+' : '-' }}
                                {{ formatCurrency(transaction.amount) }}
                            </span>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Pagination -->
        <div
            v-if="transactions.last_page > 1"
            class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between"
        >
            <p class="text-sm text-gray-600 dark:text-gray-400">
                Menampilkan {{ transactions.from }} sampai {{ transactions.to }}
                dari {{ transactions.total }} data
            </p>

            <div class="flex gap-2">
                <Link
                    v-if="transactions.current_page > 1"
                    :href="`/transactions?page=${transactions.current_page - 1}`"
                >
                    <Button variant="outline">Sebelumnya</Button>
                </Link>

                <Link
                    v-if="transactions.current_page < transactions.last_page"
                    :href="`/transactions?page=${transactions.current_page + 1}`"
                >
                    <Button variant="outline">Berikutnya</Button>
                </Link>
            </div>
        </div>

        <DeleteConfirmModal
            :show="showDeleteModal"
            title="Hapus Transaksi?"
            description="Transaksi ini akan dihapus permanen dan saldo akun akan disesuaikan ulang."
            :item-name="getDeleteItemName"
            :processing="deleteProcessing"
            confirm-label="Ya, Hapus"
            @close="closeDeleteModal"
            @confirm="deleteTransaction"
        />
    </div>
</template>
