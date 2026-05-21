<script setup lang="ts">
import { Head, Link, router, useForm } from '@inertiajs/vue3';
import { computed, ref } from 'vue';
import {
    Repeat2,
    Plus,
    Search,
    Edit2,
    Trash2,
    Play,
    RefreshCcw,
    Clock3,
    TrendingUp,
    TrendingDown,
    Calendar,
    Wallet,
    Tag,
    CheckCircle2,
    XCircle,
} from 'lucide-vue-next';

import DeleteConfirmModal from '@/components/DeleteConfirmModal.vue';
import EmptyState from '@/components/EmptyState.vue';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';

defineOptions({
    layout: {
        breadcrumbs: [
            { title: 'Dashboard', href: '/dashboard' },
            { title: 'Transaksi Berulang', href: '/recurring-transactions' },
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
    type: string;
    color: string;
    icon: string;
}

interface RecurringTransaction {
    id: number;
    financial_account_id: number;
    category_id: number;
    title: string;
    type: 'income' | 'expense';
    type_label: string;
    amount: number;
    frequency: 'daily' | 'weekly' | 'monthly' | 'yearly';
    frequency_label: string;
    start_date: string;
    next_date: string;
    end_date: string | null;
    description: string | null;
    is_active: boolean;
    status_label: string;
    last_generated_at: string | null;
    account?: Account;
    category?: Category;
}

interface Props {
    recurringTransactions: {
        data: RecurringTransaction[];
        total: number;
        per_page: number;
        current_page: number;
        last_page: number;
        from: number | null;
        to: number | null;
    };
    summary: {
        total: number;
        active: number;
        inactive: number;
        income_total: number;
        expense_total: number;
    };
    filters: {
        search: string | null;
        type: string | null;
        frequency: string | null;
        status: string | null;
    };
}

const props = defineProps<Props>();

const showDeleteModal = ref(false);
const recurringToDelete = ref<RecurringTransaction | null>(null);
const deleteProcessing = ref(false);
const generateProcessingId = ref<number | null>(null);
const generateDueProcessing = ref(false);

const form = useForm({
    search: props.filters.search || '',
    type: props.filters.type || '',
    frequency: props.filters.frequency || '',
    status: props.filters.status || '',
});

const isFiltered = computed(() => {
    return Boolean(form.search || form.type || form.frequency || form.status);
});

const netRecurring = computed(() => {
    return Number(props.summary.income_total || 0) - Number(props.summary.expense_total || 0);
});

const getDeleteItemName = computed(() => {
    if (!recurringToDelete.value) return '';

    return `${recurringToDelete.value.title} - ${formatCurrency(recurringToDelete.value.amount)}`;
});

const formatCurrency = (value: number | string | null | undefined) => {
    return new Intl.NumberFormat('id-ID', {
        style: 'currency',
        currency: 'IDR',
        minimumFractionDigits: 0,
        maximumFractionDigits: 0,
    }).format(Number(value || 0));
};

const formatDate = (date: string | null | undefined) => {
    if (!date) return '-';

    return new Date(date).toLocaleDateString('id-ID', {
        day: 'numeric',
        month: 'long',
        year: 'numeric',
    });
};

const applyFilters = () => {
    form.get('/recurring-transactions', {
        preserveScroll: true,
        preserveState: true,
    });
};

const resetFilters = () => {
    form.reset();

    router.get(
        '/recurring-transactions',
        {},
        {
            preserveScroll: true,
            preserveState: true,
        },
    );
};

const openDeleteModal = (recurring: RecurringTransaction) => {
    recurringToDelete.value = recurring;
    showDeleteModal.value = true;
};

const closeDeleteModal = () => {
    recurringToDelete.value = null;
    showDeleteModal.value = false;
};

const deleteRecurring = () => {
    if (!recurringToDelete.value) return;

    const id = recurringToDelete.value.id;

    showDeleteModal.value = false;
    recurringToDelete.value = null;
    deleteProcessing.value = true;

    router.delete(`/recurring-transactions/${id}`, {
        preserveScroll: true,
        onFinish: () => {
            deleteProcessing.value = false;
        },
    });
};

const generateOne = (recurring: RecurringTransaction) => {
    generateProcessingId.value = recurring.id;

    router.post(
        `/recurring-transactions/${recurring.id}/generate`,
        {},
        {
            preserveScroll: true,
            onFinish: () => {
                generateProcessingId.value = null;
            },
        },
    );
};

const generateDue = () => {
    generateDueProcessing.value = true;

    router.post(
        '/recurring-transactions-generate-due',
        {},
        {
            preserveScroll: true,
            onFinish: () => {
                generateDueProcessing.value = false;
            },
        },
    );
};

const getTypeClass = (type: string) => {
    if (type === 'income') {
        return 'bg-emerald-100 text-emerald-700 dark:bg-emerald-950 dark:text-emerald-300';
    }

    return 'bg-red-100 text-red-700 dark:bg-red-950 dark:text-red-300';
};

const getStatusClass = (isActive: boolean) => {
    if (isActive) {
        return 'bg-blue-100 text-blue-700 dark:bg-blue-950 dark:text-blue-300';
    }

    return 'bg-gray-100 text-gray-700 dark:bg-gray-800 dark:text-gray-300';
};

const getFrequencyClass = (frequency: string) => {
    const classes: Record<string, string> = {
        daily: 'bg-purple-100 text-purple-700 dark:bg-purple-950 dark:text-purple-300',
        weekly: 'bg-indigo-100 text-indigo-700 dark:bg-indigo-950 dark:text-indigo-300',
        monthly: 'bg-blue-100 text-blue-700 dark:bg-blue-950 dark:text-blue-300',
        yearly: 'bg-amber-100 text-amber-700 dark:bg-amber-950 dark:text-amber-300',
    };

    return classes[frequency] || 'bg-gray-100 text-gray-700';
};
</script>

<template>
    <Head title="Transaksi Berulang - Loovie Apps" />

    <div class="space-y-6 p-4 sm:p-6 lg:p-8">
        <!-- Header -->
        <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
            <div>
                <h1 class="text-3xl font-bold text-gray-900 sm:text-4xl dark:text-white">
                    Transaksi Berulang
                </h1>
                <p class="mt-1 text-gray-600 dark:text-gray-400">
                    Kelola transaksi rutin seperti gaji, kos, cicilan, listrik, dan langganan.
                </p>
            </div>

            <div class="flex flex-col gap-2 sm:flex-row">
                <Button
                    type="button"
                    variant="outline"
                    :disabled="generateDueProcessing"
                    @click="generateDue"
                >
                    <RefreshCcw class="mr-2 h-4 w-4" />
                    {{ generateDueProcessing ? 'Memproses...' : 'Generate Jatuh Tempo' }}
                </Button>

                <Link href="/recurring-transactions/create">
                    <Button class="w-full bg-indigo-600 text-white hover:bg-indigo-700 sm:w-auto">
                        <Plus class="mr-2 h-4 w-4" />
                        Tambah Recurring
                    </Button>
                </Link>
            </div>
        </div>

        <!-- Summary -->
        <div class="grid grid-cols-1 gap-4 md:grid-cols-2 xl:grid-cols-5">
            <div class="rounded-xl border bg-card p-5 shadow-sm">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm text-muted-foreground">Total</p>
                        <p class="mt-2 text-2xl font-bold">{{ summary.total }}</p>
                    </div>
                    <Repeat2 class="h-8 w-8 text-indigo-500" />
                </div>
            </div>

            <div class="rounded-xl border bg-card p-5 shadow-sm">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm text-muted-foreground">Aktif</p>
                        <p class="mt-2 text-2xl font-bold text-blue-500">{{ summary.active }}</p>
                    </div>
                    <CheckCircle2 class="h-8 w-8 text-blue-500" />
                </div>
            </div>

            <div class="rounded-xl border bg-card p-5 shadow-sm">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm text-muted-foreground">Nonaktif</p>
                        <p class="mt-2 text-2xl font-bold text-gray-500">{{ summary.inactive }}</p>
                    </div>
                    <XCircle class="h-8 w-8 text-gray-500" />
                </div>
            </div>

            <div class="rounded-xl border bg-card p-5 shadow-sm">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm text-muted-foreground">Pemasukan Aktif</p>
                        <p class="mt-2 text-xl font-bold text-emerald-500">
                            {{ formatCurrency(summary.income_total) }}
                        </p>
                    </div>
                    <TrendingUp class="h-8 w-8 text-emerald-500" />
                </div>
            </div>

            <div class="rounded-xl border bg-card p-5 shadow-sm">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm text-muted-foreground">Pengeluaran Aktif</p>
                        <p class="mt-2 text-xl font-bold text-red-500">
                            {{ formatCurrency(summary.expense_total) }}
                        </p>
                    </div>
                    <TrendingDown class="h-8 w-8 text-red-500" />
                </div>
            </div>
        </div>

        <div class="rounded-xl border bg-card p-5 shadow-sm">
            <div class="flex flex-col gap-2 sm:flex-row sm:items-center sm:justify-between">
                <div>
                    <p class="text-sm text-muted-foreground">Estimasi saldo bersih recurring aktif</p>
                    <p
                        :class="[
                            'mt-1 text-2xl font-bold',
                            netRecurring >= 0 ? 'text-emerald-500' : 'text-red-500',
                        ]"
                    >
                        {{ formatCurrency(netRecurring) }}
                    </p>
                </div>

                <p class="text-sm text-muted-foreground">
                    Pemasukan aktif - pengeluaran aktif
                </p>
            </div>
        </div>

        <!-- Filters -->
        <div class="space-y-4 rounded-xl border border-gray-200 bg-white p-4 shadow-sm dark:border-gray-800 dark:bg-gray-900">
            <div class="grid grid-cols-1 gap-4 md:grid-cols-4">
                <div class="relative">
                    <Search class="absolute left-3 top-3 h-5 w-5 text-gray-400" />
                    <Input
                        v-model="form.search"
                        type="text"
                        placeholder="Cari judul, akun, kategori..."
                        class="rounded-lg border-gray-200 bg-gray-50 pl-10 dark:border-gray-700 dark:bg-gray-800"
                    />
                </div>

                <select
                    v-model="form.type"
                    class="rounded-lg border border-gray-200 bg-gray-50 px-4 py-2 text-gray-900 focus:ring-2 focus:ring-indigo-500 dark:border-gray-700 dark:bg-gray-800 dark:text-white"
                >
                    <option value="">Semua Tipe</option>
                    <option value="income">Pemasukan</option>
                    <option value="expense">Pengeluaran</option>
                </select>

                <select
                    v-model="form.frequency"
                    class="rounded-lg border border-gray-200 bg-gray-50 px-4 py-2 text-gray-900 focus:ring-2 focus:ring-indigo-500 dark:border-gray-700 dark:bg-gray-800 dark:text-white"
                >
                    <option value="">Semua Frekuensi</option>
                    <option value="daily">Harian</option>
                    <option value="weekly">Mingguan</option>
                    <option value="monthly">Bulanan</option>
                    <option value="yearly">Tahunan</option>
                </select>

                <select
                    v-model="form.status"
                    class="rounded-lg border border-gray-200 bg-gray-50 px-4 py-2 text-gray-900 focus:ring-2 focus:ring-indigo-500 dark:border-gray-700 dark:bg-gray-800 dark:text-white"
                >
                    <option value="">Semua Status</option>
                    <option value="active">Aktif</option>
                    <option value="inactive">Nonaktif</option>
                </select>
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

        <EmptyState
            v-if="recurringTransactions.data.length === 0"
            :icon="Repeat2"
            :title="isFiltered ? 'Transaksi berulang tidak ditemukan' : 'Belum ada transaksi berulang'"
            :description="
                isFiltered
                    ? 'Tidak ada transaksi berulang yang cocok dengan filter.'
                    : 'Buat transaksi rutin seperti gaji, kos, internet, listrik, atau langganan.'
            "
            :action-label="isFiltered ? 'Reset Filter' : 'Tambah Recurring'"
            :button-type="isFiltered ? 'button' : 'link'"
            :action-href="isFiltered ? '' : '/recurring-transactions/create'"
            @action="resetFilters"
        />

        <!-- Cards -->
        <div v-else class="grid grid-cols-1 gap-4 md:grid-cols-2 xl:grid-cols-3">
            <div
                v-for="item in recurringTransactions.data"
                :key="item.id"
                class="rounded-2xl border bg-white p-5 shadow-sm transition hover:shadow-md dark:border-gray-800 dark:bg-gray-900"
            >
                <div class="mb-4 flex items-start justify-between gap-3">
                    <div class="flex min-w-0 gap-3">
                        <div
                            :class="[
                                'flex h-12 w-12 shrink-0 items-center justify-center rounded-xl',
                                item.type === 'income'
                                    ? 'bg-emerald-100 text-emerald-600 dark:bg-emerald-950 dark:text-emerald-300'
                                    : 'bg-red-100 text-red-600 dark:bg-red-950 dark:text-red-300',
                            ]"
                        >
                            <TrendingUp v-if="item.type === 'income'" class="h-6 w-6" />
                            <TrendingDown v-else class="h-6 w-6" />
                        </div>

                        <div class="min-w-0">
                            <h3 class="truncate font-semibold text-gray-900 dark:text-white">
                                {{ item.title }}
                            </h3>

                            <p class="mt-1 text-sm font-bold text-gray-900 dark:text-white">
                                {{ formatCurrency(item.amount) }}
                            </p>
                        </div>
                    </div>

                    <span
                        :class="[
                            'shrink-0 rounded-full px-2 py-1 text-xs font-medium',
                            getStatusClass(item.is_active),
                        ]"
                    >
                        {{ item.status_label }}
                    </span>
                </div>

                <div class="mb-4 flex flex-wrap gap-2">
                    <span
                        :class="[
                            'inline-flex items-center gap-1 rounded-full px-2 py-1 text-xs font-medium',
                            getTypeClass(item.type),
                        ]"
                    >
                        {{ item.type_label }}
                    </span>

                    <span
                        :class="[
                            'inline-flex items-center gap-1 rounded-full px-2 py-1 text-xs font-medium',
                            getFrequencyClass(item.frequency),
                        ]"
                    >
                        <RefreshCcw class="h-3 w-3" />
                        {{ item.frequency_label }}
                    </span>
                </div>

                <div class="space-y-3">
                    <div class="rounded-xl bg-muted/40 p-3">
                        <div class="flex items-center gap-2 text-sm">
                            <Calendar class="h-4 w-4 text-indigo-500" />
                            <span class="text-muted-foreground">Next:</span>
                            <span class="font-semibold">{{ formatDate(item.next_date) }}</span>
                        </div>

                        <div class="mt-2 flex items-center gap-2 text-sm">
                            <Clock3 class="h-4 w-4 text-gray-500" />
                            <span class="text-muted-foreground">Mulai:</span>
                            <span>{{ formatDate(item.start_date) }}</span>
                        </div>

                        <div class="mt-2 flex items-center gap-2 text-sm">
                            <Clock3 class="h-4 w-4 text-gray-500" />
                            <span class="text-muted-foreground">Akhir:</span>
                            <span>{{ formatDate(item.end_date) }}</span>
                        </div>
                    </div>

                    <div class="grid grid-cols-1 gap-2">
                        <div class="flex items-center gap-2 text-sm text-muted-foreground">
                            <Wallet class="h-4 w-4" />
                            <span class="truncate">{{ item.account?.name || '-' }}</span>
                        </div>

                        <div class="flex items-center gap-2 text-sm text-muted-foreground">
                            <Tag class="h-4 w-4" />
                            <span class="truncate">{{ item.category?.name || '-' }}</span>
                        </div>
                    </div>

                    <p
                        v-if="item.description"
                        class="line-clamp-2 text-sm text-muted-foreground"
                    >
                        {{ item.description }}
                    </p>
                </div>

                <div class="mt-5 grid grid-cols-1 gap-2 border-t pt-4 sm:grid-cols-3 dark:border-gray-800">
                    <button
                        type="button"
                        class="inline-flex items-center justify-center gap-2 rounded-lg border border-emerald-200 px-3 py-2 text-sm text-emerald-600 transition hover:bg-emerald-50 disabled:cursor-not-allowed disabled:opacity-50 dark:border-emerald-900 dark:hover:bg-emerald-950/40"
                        :disabled="!item.is_active || generateProcessingId === item.id"
                        @click="generateOne(item)"
                    >
                        <Play class="h-4 w-4" />
                        {{ generateProcessingId === item.id ? 'Proses' : 'Generate' }}
                    </button>

                    <Link
                        :href="`/recurring-transactions/${item.id}/edit`"
                        class="inline-flex items-center justify-center gap-2 rounded-lg border px-3 py-2 text-sm text-blue-600 transition hover:bg-blue-50 dark:border-blue-900 dark:hover:bg-blue-950/40"
                    >
                        <Edit2 class="h-4 w-4" />
                        Edit
                    </Link>

                    <button
                        type="button"
                        class="inline-flex items-center justify-center gap-2 rounded-lg border border-red-200 px-3 py-2 text-sm text-red-600 transition hover:bg-red-50 dark:border-red-900 dark:hover:bg-red-950/40"
                        @click="openDeleteModal(item)"
                    >
                        <Trash2 class="h-4 w-4" />
                        Hapus
                    </button>
                </div>
            </div>
        </div>

        <!-- Pagination -->
        <div
            v-if="recurringTransactions.last_page > 1"
            class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between"
        >
            <p class="text-sm text-gray-600 dark:text-gray-400">
                Menampilkan {{ recurringTransactions.from }} sampai {{ recurringTransactions.to }}
                dari {{ recurringTransactions.total }} data
            </p>

            <div class="flex gap-2">
                <Link
                    v-if="recurringTransactions.current_page > 1"
                    :href="`/recurring-transactions?page=${recurringTransactions.current_page - 1}`"
                >
                    <Button variant="outline">Sebelumnya</Button>
                </Link>

                <Link
                    v-if="recurringTransactions.current_page < recurringTransactions.last_page"
                    :href="`/recurring-transactions?page=${recurringTransactions.current_page + 1}`"
                >
                    <Button variant="outline">Berikutnya</Button>
                </Link>
            </div>
        </div>

        <DeleteConfirmModal
            :show="showDeleteModal"
            title="Hapus Transaksi Berulang?"
            description="Data transaksi berulang ini akan dihapus permanen. Transaksi yang sudah pernah dibuat tidak ikut terhapus."
            :item-name="getDeleteItemName"
            :processing="deleteProcessing"
            confirm-label="Ya, Hapus"
            @close="closeDeleteModal"
            @confirm="deleteRecurring"
        />
    </div>
</template>
