<script setup lang="ts">
import { Head, Link, router } from '@inertiajs/vue3';
import { computed, ref } from 'vue';
import {
    CreditCard,
    Edit2,
    Trash2,
    Plus,
    Search,
    Download,
    Printer,
    Wallet,
    Landmark,
} from 'lucide-vue-next';

import DeleteConfirmModal from '@/components/DeleteConfirmModal.vue';
import EmptyState from '@/components/EmptyState.vue';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';

defineOptions({
    layout: {
        breadcrumbs: [
            { title: 'Dashboard', href: '/dashboard' },
            { title: 'Akun Keuangan', href: '/financial-accounts' },
        ],
    },
});

interface Account {
    id: number;
    name: string;
    type: string;
    initial_balance: number;
    current_balance: number;
    description: string | null;
    is_active: boolean;
    created_at: string;
}

interface Props {
    accounts: {
        data: Account[];
        total: number;
        per_page: number;
        current_page: number;
        last_page: number;
    };
    totalBalance: number;
}

const props = defineProps<Props>();

const searchQuery = ref('');
const showDeleteModal = ref(false);
const accountToDelete = ref<Account | null>(null);
const deleteProcessing = ref(false);

const accountTypes: Record<string, string> = {
    cash: 'Cash',
    bank: 'Bank',
    digital_wallet: 'E-Wallet',
    investment: 'Investasi',
    credit_card: 'Kartu Kredit',
};

const filteredAccounts = computed(() => {
    if (!searchQuery.value) return props.accounts.data;

    const keyword = searchQuery.value.toLowerCase();

    return props.accounts.data.filter((account) => {
        const typeLabel = accountTypes[account.type] || account.type;

        return (
            account.name.toLowerCase().includes(keyword) ||
            typeLabel.toLowerCase().includes(keyword) ||
            (account.description || '').toLowerCase().includes(keyword)
        );
    });
});

const activeAccounts = computed(() => {
    return props.accounts.data.filter((account) => account.is_active).length;
});

const inactiveAccounts = computed(() => {
    return props.accounts.data.filter((account) => !account.is_active).length;
});

const isFiltered = computed(() => {
    return Boolean(searchQuery.value);
});

const getTypeColor = (type: string) => {
    const colors: Record<string, string> = {
        cash: 'bg-green-100 text-green-800 dark:bg-green-900/30 dark:text-green-400',
        bank: 'bg-blue-100 text-blue-800 dark:bg-blue-900/30 dark:text-blue-400',
        digital_wallet:
            'bg-purple-100 text-purple-800 dark:bg-purple-900/30 dark:text-purple-400',
        investment:
            'bg-yellow-100 text-yellow-800 dark:bg-yellow-900/30 dark:text-yellow-400',
        credit_card:
            'bg-red-100 text-red-800 dark:bg-red-900/30 dark:text-red-400',
    };

    return colors[type] || 'bg-gray-100 text-gray-800 dark:bg-gray-800 dark:text-gray-300';
};

const openDeleteModal = (account: Account) => {
    accountToDelete.value = account;
    showDeleteModal.value = true;
};

const closeDeleteModal = () => {
    if (deleteProcessing.value) return;

    accountToDelete.value = null;
    showDeleteModal.value = false;
};

const deleteAccount = () => {
    if (!accountToDelete.value) return;

    deleteProcessing.value = true;

    router.delete(`/financial-accounts/${accountToDelete.value.id}`, {
        preserveScroll: true,
        onSuccess: () => closeDeleteModal(),
        onFinish: () => {
            deleteProcessing.value = false;
        },
    });
};

const resetSearch = () => {
    searchQuery.value = '';
};

const formatCurrency = (value: number | string | null | undefined) => {
    return new Intl.NumberFormat('id-ID', {
        style: 'currency',
        currency: 'IDR',
        minimumFractionDigits: 0,
        maximumFractionDigits: 0,
    }).format(Number(value || 0));
};
</script>

<template>
    <Head title="Akun Keuangan - Loovie Apps" />

    <div class="space-y-6 p-4 sm:p-6 lg:p-8">
        <!-- Header -->
        <div
            class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between"
        >
            <div>
                <h1 class="text-3xl font-bold text-gray-900 sm:text-4xl dark:text-white">
                    Akun Keuangan
                </h1>
                <p class="mt-1 text-gray-600 dark:text-gray-400">
                    Kelola semua akun penyimpanan uangmu.
                </p>
            </div>

            <Link href="/financial-accounts/create">
                <Button class="bg-indigo-600 text-white hover:bg-indigo-700">
                    <Plus class="mr-2 h-4 w-4" />
                    Tambah Akun
                </Button>
            </Link>
        </div>

        <!-- Summary Cards -->
        <div class="grid grid-cols-1 gap-4 md:grid-cols-3">
            <div
                class="rounded-xl bg-gradient-to-br from-indigo-600 to-purple-600 p-6 text-white shadow-lg"
            >
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm font-medium opacity-90">
                            Total Saldo
                        </p>
                        <p class="mt-2 text-3xl font-bold sm:text-4xl">
                            {{ formatCurrency(totalBalance) }}
                        </p>
                    </div>

                    <Wallet class="h-10 w-10 opacity-90" />
                </div>
            </div>

            <div class="rounded-xl border bg-card p-6 shadow-sm">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm text-muted-foreground">
                            Akun Aktif
                        </p>
                        <p class="mt-2 text-3xl font-bold text-emerald-500">
                            {{ activeAccounts }}
                        </p>
                    </div>

                    <Landmark class="h-9 w-9 text-emerald-500" />
                </div>
            </div>

            <div class="rounded-xl border bg-card p-6 shadow-sm">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm text-muted-foreground">
                            Akun Nonaktif
                        </p>
                        <p class="mt-2 text-3xl font-bold text-slate-500">
                            {{ inactiveAccounts }}
                        </p>
                    </div>

                    <CreditCard class="h-9 w-9 text-slate-500" />
                </div>
            </div>
        </div>

        <!-- Search Bar -->
        <div
            class="rounded-xl border border-gray-200 bg-white p-4 shadow-sm dark:border-gray-800 dark:bg-gray-900"
        >
            <div class="relative">
                <Search class="absolute left-3 top-3 h-5 w-5 text-gray-400" />
                <Input
                    v-model="searchQuery"
                    type="text"
                    placeholder="Cari nama akun, tipe, atau deskripsi..."
                    class="rounded-lg border-gray-200 bg-gray-50 pl-10 dark:border-gray-700 dark:bg-gray-800"
                />
            </div>
        </div>

        <!-- Export/Print Buttons -->
        <div class="flex flex-wrap gap-2">
            <a
                href="/financial-accounts/export/csv"
                class="inline-flex items-center gap-2 rounded-lg bg-blue-500 px-4 py-2 text-white transition hover:bg-blue-600"
            >
                <Download class="h-4 w-4" />
                Export CSV
            </a>

            <a
                href="/financial-accounts/export/print"
                target="_blank"
                class="inline-flex items-center gap-2 rounded-lg bg-gray-500 px-4 py-2 text-white transition hover:bg-gray-600"
            >
                <Printer class="h-4 w-4" />
                Print Report
            </a>
        </div>

        <!-- Empty State -->
        <EmptyState
            v-if="filteredAccounts.length === 0"
            :icon="CreditCard"
            :title="isFiltered ? 'Akun tidak ditemukan' : 'Belum ada akun keuangan'"
            :description="
                isFiltered
                    ? 'Tidak ada akun yang cocok dengan kata kunci pencarian.'
                    : 'Tambahkan akun pertama seperti Cash, Bank, E-Wallet, atau Investasi.'
            "
            :action-label="isFiltered ? 'Reset Pencarian' : 'Tambah Akun'"
            :button-type="isFiltered ? 'button' : 'link'"
            :action-href="isFiltered ? '' : '/financial-accounts/create'"
            @action="resetSearch"
        />

        <!-- Accounts Table -->
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
                                Nama Akun
                            </th>
                            <th class="px-6 py-3 text-left text-sm font-semibold text-gray-900 dark:text-white">
                                Tipe
                            </th>
                            <th class="px-6 py-3 text-right text-sm font-semibold text-gray-900 dark:text-white">
                                Saldo
                            </th>
                            <th class="px-6 py-3 text-left text-sm font-semibold text-gray-900 dark:text-white">
                                Status
                            </th>
                            <th class="px-6 py-3 text-center text-sm font-semibold text-gray-900 dark:text-white">
                                Aksi
                            </th>
                        </tr>
                    </thead>

                    <tbody class="divide-y divide-gray-200 dark:divide-gray-700">
                        <tr
                            v-for="account in filteredAccounts"
                            :key="account.id"
                            class="transition-colors hover:bg-gray-50 dark:hover:bg-gray-800/50"
                        >
                            <td class="px-6 py-4">
                                <div class="flex items-center space-x-3">
                                    <div
                                        class="flex h-10 w-10 items-center justify-center rounded-lg bg-indigo-100 dark:bg-indigo-900/30"
                                    >
                                        <CreditCard class="h-5 w-5 text-indigo-600 dark:text-indigo-400" />
                                    </div>

                                    <div class="min-w-0">
                                        <p class="truncate font-medium text-gray-900 dark:text-white">
                                            {{ account.name }}
                                        </p>
                                        <p class="truncate text-sm text-gray-500 dark:text-gray-400">
                                            {{ account.description || '-' }}
                                        </p>
                                    </div>
                                </div>
                            </td>

                            <td class="px-6 py-4">
                                <span
                                    :class="[
                                        'rounded-full px-3 py-1 text-sm font-medium',
                                        getTypeColor(account.type),
                                    ]"
                                >
                                    {{ accountTypes[account.type] || account.type }}
                                </span>
                            </td>

                            <td class="px-6 py-4 text-right">
                                <p class="font-semibold text-gray-900 dark:text-white">
                                    {{ formatCurrency(account.current_balance) }}
                                </p>
                                <p class="text-sm text-gray-500 dark:text-gray-400">
                                    Awal:
                                    {{ formatCurrency(account.initial_balance) }}
                                </p>
                            </td>

                            <td class="px-6 py-4">
                                <span
                                    v-if="account.is_active"
                                    class="rounded-full bg-green-100 px-3 py-1 text-sm font-medium text-green-800 dark:bg-green-900/30 dark:text-green-400"
                                >
                                    Aktif
                                </span>

                                <span
                                    v-else
                                    class="rounded-full bg-gray-100 px-3 py-1 text-sm font-medium text-gray-800 dark:bg-gray-800 dark:text-gray-400"
                                >
                                    Nonaktif
                                </span>
                            </td>

                            <td class="px-6 py-4">
                                <div class="flex items-center justify-center gap-2">
                                    <Link
                                        :href="`/financial-accounts/${account.id}/edit`"
                                        class="inline-flex items-center gap-1 rounded-lg border px-3 py-2 text-xs text-blue-600 transition hover:bg-blue-50 dark:border-blue-900 dark:hover:bg-blue-950/40"
                                    >
                                        <Edit2 class="h-4 w-4" />
                                        Edit
                                    </Link>

                                    <button
                                        type="button"
                                        class="inline-flex items-center gap-1 rounded-lg border border-red-200 px-3 py-2 text-xs text-red-600 transition hover:bg-red-50 dark:border-red-900 dark:hover:bg-red-950/40"
                                        @click="openDeleteModal(account)"
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
                    v-for="account in filteredAccounts"
                    :key="account.id"
                    class="border-b border-gray-200 p-4 last:border-b-0 dark:border-gray-700"
                >
                    <div class="mb-3 flex items-start justify-between gap-3">
                        <div class="flex min-w-0 flex-1 items-center space-x-3">
                            <div
                                class="flex h-10 w-10 shrink-0 items-center justify-center rounded-lg bg-indigo-100 dark:bg-indigo-900/30"
                            >
                                <CreditCard class="h-5 w-5 text-indigo-600 dark:text-indigo-400" />
                            </div>

                            <div class="min-w-0 flex-1">
                                <p class="truncate font-medium text-gray-900 dark:text-white">
                                    {{ account.name }}
                                </p>
                                <p class="truncate text-sm text-gray-500 dark:text-gray-400">
                                    {{ account.description || '-' }}
                                </p>
                            </div>
                        </div>

                        <div class="ml-2 flex items-center gap-2">
                            <Link
                                :href="`/financial-accounts/${account.id}/edit`"
                                class="rounded-lg border px-2 py-2 text-blue-600"
                            >
                                <Edit2 class="h-4 w-4" />
                            </Link>

                            <button
                                type="button"
                                class="rounded-lg border border-red-200 px-2 py-2 text-red-600"
                                @click="openDeleteModal(account)"
                            >
                                <Trash2 class="h-4 w-4" />
                            </button>
                        </div>
                    </div>

                    <div class="grid grid-cols-3 gap-2 text-sm">
                        <div>
                            <p class="text-gray-600 dark:text-gray-400">
                                Tipe
                            </p>
                            <span
                                :class="[
                                    'mt-1 block rounded px-2 py-1 text-center text-xs font-medium',
                                    getTypeColor(account.type),
                                ]"
                            >
                                {{ accountTypes[account.type] || account.type }}
                            </span>
                        </div>

                        <div>
                            <p class="text-gray-600 dark:text-gray-400">
                                Status
                            </p>
                            <span
                                v-if="account.is_active"
                                class="mt-1 block rounded bg-green-100 px-2 py-1 text-center text-xs font-medium text-green-800 dark:bg-green-900/30 dark:text-green-400"
                            >
                                Aktif
                            </span>

                            <span
                                v-else
                                class="mt-1 block rounded bg-gray-100 px-2 py-1 text-center text-xs font-medium text-gray-800 dark:bg-gray-800 dark:text-gray-400"
                            >
                                Nonaktif
                            </span>
                        </div>

                        <div>
                            <p class="text-gray-600 dark:text-gray-400">
                                Saldo
                            </p>
                            <p class="mt-1 text-xs font-semibold text-gray-900 dark:text-white">
                                {{ formatCurrency(account.current_balance) }}
                            </p>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <DeleteConfirmModal
            :show="showDeleteModal"
            title="Hapus Akun?"
            description="Akun ini akan dihapus permanen. Jika akun memiliki transaksi, proses hapus bisa ditolak oleh sistem."
            :item-name="accountToDelete?.name"
            :processing="deleteProcessing"
            confirm-label="Ya, Hapus"
            @close="closeDeleteModal"
            @confirm="deleteAccount"
        />
    </div>
</template>
