<script setup lang="ts">
import { Head, Link, router, useForm } from '@inertiajs/vue3';
import { computed, ref } from 'vue';
import {
    ArrowRightLeft,
    Plus,
    Search,
    Calendar,
    Edit2,
    Trash2,
    ArrowRight,
    Wallet,
} from 'lucide-vue-next';

import EmptyState from '@/components/EmptyState.vue';
import DeleteConfirmModal from '@/components/DeleteConfirmModal.vue';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';

defineOptions({
    layout: {
        breadcrumbs: [
            { title: 'Dashboard', href: '/dashboard' },
            { title: 'Transfer', href: '/transfers' },
        ],
    },
});

interface Account {
    id: number;
    name: string;
    type: string;
    current_balance: number;
}

interface TransferItem {
    id: number;
    from_account_id: number;
    to_account_id: number;
    amount: number;
    transfer_date: string;
    description: string | null;
    created_at: string;
    from_account: Account;
    to_account: Account;
}

interface Props {
    transfers: {
        data: TransferItem[];
        total: number;
        per_page: number;
        current_page: number;
        last_page: number;
        from: number;
        to: number;
    };
    filters: {
        search: string | null;
        date_from: string | null;
        date_to: string | null;
        month: string | null;
    };
}

const props = defineProps<Props>();

const showDeleteModal = ref(false);
const transferToDelete = ref<TransferItem | null>(null);
const deleteProcessing = ref(false);

const form = useForm({
    search: props.filters.search || '',
    date_from: props.filters.date_from || '',
    date_to: props.filters.date_to || '',
    month: props.filters.month || '',
});

const totalTransferAmount = computed(() => {
    return props.transfers.data.reduce((total, transfer) => {
        return total + Number(transfer.amount || 0);
    }, 0);
});

const isFiltered = computed(() => {
    return Boolean(form.search || form.date_from || form.date_to || form.month);
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

const applyFilters = () => {
    form.get('/transfers', {
        preserveScroll: true,
        preserveState: true,
    });
};

const resetFilters = () => {
    form.reset();

    router.get(
        '/transfers',
        {},
        {
            preserveScroll: true,
            preserveState: true,
        },
    );
};

const openDeleteModal = (transfer: TransferItem) => {
    transferToDelete.value = transfer;
    showDeleteModal.value = true;
};

const closeDeleteModal = () => {
    transferToDelete.value = null;
    showDeleteModal.value = false;
};

const deleteTransfer = () => {
    if (!transferToDelete.value) return;

    const id = transferToDelete.value.id;

    showDeleteModal.value = false;
    transferToDelete.value = null;
    deleteProcessing.value = true;

    router.delete(`/transfers/${id}`, {
        preserveScroll: true,
        onFinish: () => {
            deleteProcessing.value = false;
        },
    });
};

const getDeleteItemName = computed(() => {
    if (!transferToDelete.value) return '';

    return `${transferToDelete.value.from_account?.name || '-'} → ${transferToDelete.value.to_account?.name || '-'} - ${formatCurrency(transferToDelete.value.amount)}`;
});
</script>

<template>
    <Head title="Transfer Antar Akun - Loovie Apps" />

    <div class="space-y-6 p-4 sm:p-6 lg:p-8">
        <!-- Header -->
        <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
            <div>
                <h1 class="text-3xl font-bold text-gray-900 sm:text-4xl dark:text-white">
                    Transfer Antar Akun
                </h1>
                <p class="mt-1 text-gray-600 dark:text-gray-400">
                    Pindahkan saldo dari satu akun ke akun lain tanpa masuk laporan pemasukan/pengeluaran.
                </p>
            </div>

            <Link href="/transfers/create">
                <Button class="bg-indigo-600 text-white hover:bg-indigo-700">
                    <Plus class="mr-2 h-4 w-4" />
                    Tambah Transfer
                </Button>
            </Link>
        </div>

        <!-- Summary -->
        <div class="grid grid-cols-1 gap-4 md:grid-cols-3">
            <div class="rounded-xl border bg-card p-5 shadow-sm">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm text-muted-foreground">
                            Total Transfer
                        </p>
                        <p class="mt-2 text-2xl font-bold">
                            {{ transfers.total || 0 }}
                        </p>
                    </div>

                    <ArrowRightLeft class="h-8 w-8 text-indigo-500" />
                </div>
            </div>

            <div class="rounded-xl border bg-card p-5 shadow-sm">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm text-muted-foreground">
                            Nominal Ditampilkan
                        </p>
                        <p class="mt-2 text-2xl font-bold text-blue-500">
                            {{ formatCurrency(totalTransferAmount) }}
                        </p>
                    </div>

                    <Wallet class="h-8 w-8 text-blue-500" />
                </div>
            </div>

            <div class="rounded-xl border bg-card p-5 shadow-sm">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm text-muted-foreground">
                            Data Ditampilkan
                        </p>
                        <p class="mt-2 text-2xl font-bold text-emerald-500">
                            {{ transfers.data.length || 0 }}
                        </p>
                    </div>

                    <Search class="h-8 w-8 text-emerald-500" />
                </div>
            </div>
        </div>

        <!-- Filters -->
        <div class="space-y-4 rounded-xl border border-gray-200 bg-white p-4 shadow-sm dark:border-gray-800 dark:bg-gray-900">
            <div class="grid grid-cols-1 gap-4 md:grid-cols-2">
                <div class="relative">
                    <Search class="absolute left-3 top-3 h-5 w-5 text-gray-400" />
                    <Input
                        v-model="form.search"
                        type="text"
                        placeholder="Cari akun atau deskripsi..."
                        class="rounded-lg border-gray-200 bg-gray-50 pl-10 dark:border-gray-700 dark:bg-gray-800"
                    />
                </div>

                <div class="relative">
                    <Calendar class="absolute left-3 top-3 h-5 w-5 text-gray-400" />
                    <Input
                        v-model="form.month"
                        type="month"
                        class="rounded-lg border-gray-200 bg-gray-50 pl-10 dark:border-gray-700 dark:bg-gray-800"
                    />
                </div>
            </div>

            <div class="grid grid-cols-1 gap-4 md:grid-cols-2">
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
            </div>

            <div class="flex justify-end gap-2">
                <Button variant="outline" @click="resetFilters">
                    Reset
                </Button>

                <Button class="bg-indigo-600 text-white hover:bg-indigo-700" @click="applyFilters">
                    Terapkan Filter
                </Button>
            </div>
        </div>

        <!-- Empty State -->
        <EmptyState
            v-if="transfers.data.length === 0"
            :icon="ArrowRightLeft"
            :title="isFiltered ? 'Transfer tidak ditemukan' : 'Belum ada transfer'"
            :description="
                isFiltered
                    ? 'Tidak ada transfer yang cocok dengan filter yang kamu gunakan.'
                    : 'Buat transfer antar akun untuk memindahkan saldo tanpa mencatat sebagai pemasukan atau pengeluaran.'
            "
            :action-label="isFiltered ? 'Reset Filter' : 'Tambah Transfer'"
            :button-type="isFiltered ? 'button' : 'link'"
            :action-href="isFiltered ? '' : '/transfers/create'"
            @action="resetFilters"
        />

        <!-- Table -->
        <div
            v-else
            class="overflow-hidden rounded-xl border border-gray-200 bg-white shadow-sm dark:border-gray-800 dark:bg-gray-900"
        >
            <!-- Desktop -->
            <div class="hidden overflow-x-auto md:block">
                <table class="w-full">
                    <thead class="border-b border-gray-200 bg-gray-50 dark:border-gray-700 dark:bg-gray-800">
                        <tr>
                            <th class="px-6 py-3 text-left text-sm font-semibold">
                                Tanggal
                            </th>
                            <th class="px-6 py-3 text-left text-sm font-semibold">
                                Dari Akun
                            </th>
                            <th class="px-6 py-3 text-left text-sm font-semibold">
                                Ke Akun
                            </th>
                            <th class="px-6 py-3 text-right text-sm font-semibold">
                                Nominal
                            </th>
                            <th class="px-6 py-3 text-left text-sm font-semibold">
                                Deskripsi
                            </th>
                            <th class="px-6 py-3 text-center text-sm font-semibold">
                                Aksi
                            </th>
                        </tr>
                    </thead>

                    <tbody class="divide-y divide-gray-200 dark:divide-gray-700">
                        <tr
                            v-for="transfer in transfers.data"
                            :key="transfer.id"
                            class="transition-colors hover:bg-gray-50 dark:hover:bg-gray-800/50"
                        >
                            <td class="px-6 py-4 text-sm">
                                {{ formatDate(transfer.transfer_date) }}
                            </td>

                            <td class="px-6 py-4 text-sm font-medium">
                                {{ transfer.from_account?.name || '-' }}
                            </td>

                            <td class="px-6 py-4 text-sm font-medium">
                                <div class="flex items-center gap-2">
                                    <ArrowRight class="h-4 w-4 text-indigo-500" />
                                    {{ transfer.to_account?.name || '-' }}
                                </div>
                            </td>

                            <td class="px-6 py-4 text-right text-sm font-bold text-indigo-600 dark:text-indigo-400">
                                {{ formatCurrency(transfer.amount) }}
                            </td>

                            <td class="px-6 py-4 text-sm text-muted-foreground">
                                {{ transfer.description || '-' }}
                            </td>

                            <td class="px-6 py-4">
                                <div class="flex items-center justify-center gap-2">
                                    <Link
                                        :href="`/transfers/${transfer.id}/edit`"
                                        class="inline-flex items-center gap-1 rounded-lg border px-3 py-2 text-xs text-blue-600 transition hover:bg-blue-50 dark:border-blue-900 dark:hover:bg-blue-950/40"
                                    >
                                        <Edit2 class="h-4 w-4" />
                                        Edit
                                    </Link>

                                    <button
                                        type="button"
                                        class="inline-flex items-center gap-1 rounded-lg border border-red-200 px-3 py-2 text-xs text-red-600 transition hover:bg-red-50 dark:border-red-900 dark:hover:bg-red-950/40"
                                        @click="openDeleteModal(transfer)"
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

            <!-- Mobile -->
            <div class="md:hidden">
                <div
                    v-for="transfer in transfers.data"
                    :key="transfer.id"
                    class="border-b border-gray-200 p-4 last:border-b-0 dark:border-gray-700"
                >
                    <div class="mb-3 flex items-start justify-between gap-3">
                        <div>
                            <p class="font-semibold">
                                {{ formatCurrency(transfer.amount) }}
                            </p>
                            <p class="text-xs text-muted-foreground">
                                {{ formatDate(transfer.transfer_date) }}
                            </p>
                        </div>

                        <div class="flex gap-2">
                            <Link
                                :href="`/transfers/${transfer.id}/edit`"
                                class="rounded-lg border px-2 py-2 text-blue-600"
                            >
                                <Edit2 class="h-4 w-4" />
                            </Link>

                            <button
                                type="button"
                                class="rounded-lg border border-red-200 px-2 py-2 text-red-600"
                                @click="openDeleteModal(transfer)"
                            >
                                <Trash2 class="h-4 w-4" />
                            </button>
                        </div>
                    </div>

                    <div class="rounded-xl bg-muted/40 p-3 text-sm">
                        <div class="flex items-center justify-between gap-2">
                            <span>{{ transfer.from_account?.name || '-' }}</span>
                            <ArrowRight class="h-4 w-4 text-indigo-500" />
                            <span>{{ transfer.to_account?.name || '-' }}</span>
                        </div>
                    </div>

                    <p class="mt-2 text-sm text-muted-foreground">
                        {{ transfer.description || '-' }}
                    </p>
                </div>
            </div>
        </div>

        <!-- Pagination -->
        <div
            v-if="transfers.last_page > 1"
            class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between"
        >
            <p class="text-sm text-gray-600 dark:text-gray-400">
                Menampilkan {{ transfers.from }} sampai {{ transfers.to }}
                dari {{ transfers.total }} transfer
            </p>

            <div class="flex gap-2">
                <Link
                    v-if="transfers.current_page > 1"
                    :href="`/transfers?page=${transfers.current_page - 1}`"
                >
                    <Button variant="outline">Sebelumnya</Button>
                </Link>

                <Link
                    v-if="transfers.current_page < transfers.last_page"
                    :href="`/transfers?page=${transfers.current_page + 1}`"
                >
                    <Button variant="outline">Berikutnya</Button>
                </Link>
            </div>
        </div>

        <DeleteConfirmModal
            :show="showDeleteModal"
            title="Hapus Transfer?"
            description="Transfer ini akan dihapus dan saldo akun akan dikembalikan seperti sebelum transfer."
            :item-name="getDeleteItemName"
            :processing="deleteProcessing"
            confirm-label="Ya, Hapus"
            @close="closeDeleteModal"
            @confirm="deleteTransfer"
        />
    </div>
</template>
