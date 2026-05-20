<script setup>
import { Head, Link, router } from '@inertiajs/vue3';
import { computed, ref } from 'vue';
import AppLayout from '@/layouts/AppLayout.vue';
import EmptyState from '@/components/EmptyState.vue';
import {
    BarChart3,
    Calendar,
    Download,
    Printer,
    TrendingUp,
    TrendingDown,
    Wallet,
    ReceiptText,
    AlertTriangle,
    PiggyBank,
    FileText,
    CircleDollarSign,
} from 'lucide-vue-next';

defineOptions({
    layout: AppLayout,
});

const props = defineProps({
    month: {
        type: String,
        default: '',
    },
    report: {
        type: Object,
        required: true,
    },
});

const selectedMonth = ref(props.month || new Date().toISOString().slice(0, 7));

const summary = computed(() => props.report.summary || {});
const budget = computed(() => props.report.budget || {});
const incomeByCategory = computed(() => props.report.incomeByCategory || []);
const expenseByCategory = computed(() => props.report.expenseByCategory || []);
const largestTransactions = computed(() => props.report.largestTransactions || []);

const hasTransactions = computed(() => {
    return Number(summary.value.transactionCount || 0) > 0;
});

const hasBudget = computed(() => {
    return Number(budget.value.totalBudget || 0) > 0;
});

const formatCurrency = (value) => {
    return new Intl.NumberFormat('id-ID', {
        style: 'currency',
        currency: 'IDR',
        minimumFractionDigits: 0,
        maximumFractionDigits: 0,
    }).format(Number(value || 0));
};

const changeMonth = () => {
    router.get(
        '/reports',
        {
            month: selectedMonth.value,
        },
        {
            preserveScroll: true,
            preserveState: true,
        },
    );
};

const getStatusLabel = (status) => {
    if (status === 'over') return 'Melebihi Budget';
    if (status === 'warning') return 'Hampir Limit';

    return 'Aman';
};

const getStatusClass = (status) => {
    if (status === 'over') {
        return 'bg-red-100 text-red-700 dark:bg-red-950 dark:text-red-300';
    }

    if (status === 'warning') {
        return 'bg-yellow-100 text-yellow-700 dark:bg-yellow-950 dark:text-yellow-300';
    }

    return 'bg-emerald-100 text-emerald-700 dark:bg-emerald-950 dark:text-emerald-300';
};

const getProgressClass = (status) => {
    if (status === 'over') return 'bg-red-500';
    if (status === 'warning') return 'bg-yellow-500';

    return 'bg-emerald-500';
};

const getTotalBudgetStatus = computed(() => {
    const percentage = Number(budget.value.totalPercentage || 0);

    if (percentage >= 100) return 'over';
    if (percentage >= 80) return 'warning';

    return 'safe';
});

const getCategoryPercentage = (total, grandTotal) => {
    if (!grandTotal || grandTotal <= 0) return '0%';

    return `${Math.min((Number(total || 0) / Number(grandTotal || 0)) * 100, 100)}%`;
};
</script>

<template>
    <Head title="Laporan Bulanan - Loovie Apps" />

    <div class="space-y-6 p-4 md:p-6">
        <!-- Header -->
        <div class="flex flex-col gap-4 md:flex-row md:items-center md:justify-between">
            <div>
                <h1 class="text-2xl font-bold md:text-3xl">
                    Laporan Bulanan
                </h1>
                <p class="text-sm text-muted-foreground">
                    Ringkasan keuangan untuk {{ report.monthLabel }}.
                </p>
            </div>

            <div class="flex flex-col gap-2 sm:flex-row">
                <div class="flex items-center gap-2 rounded-lg border bg-background px-3 py-2">
                    <Calendar class="h-4 w-4 text-muted-foreground" />

                    <input
                        v-model="selectedMonth"
                        type="month"
                        class="bg-transparent text-sm outline-none"
                        @change="changeMonth"
                    />
                </div>

                <a
                    :href="`/reports/export/csv?month=${selectedMonth}`"
                    class="inline-flex items-center justify-center gap-2 rounded-lg border px-4 py-2 text-sm font-medium transition hover:bg-muted"
                >
                    <Download class="h-4 w-4" />
                    Export CSV
                </a>

                <a
                    :href="`/reports/export/print?month=${selectedMonth}`"
                    target="_blank"
                    class="inline-flex items-center justify-center gap-2 rounded-lg bg-blue-600 px-4 py-2 text-sm font-medium text-white transition hover:bg-blue-700"
                >
                    <Printer class="h-4 w-4" />
                    Print
                </a>
            </div>
        </div>

        <!-- Empty main report warning -->
        <div
            v-if="!hasTransactions"
            class="rounded-xl border border-blue-200 bg-blue-50 p-4 text-sm text-blue-800 dark:border-blue-900 dark:bg-blue-950/40 dark:text-blue-200"
        >
            <div class="flex gap-3">
                <FileText class="mt-0.5 h-5 w-5 shrink-0" />
                <div>
                    <p class="font-semibold">
                        Belum ada transaksi pada bulan ini
                    </p>
                    <p class="mt-1">
                        Laporan tetap bisa dibuka, tetapi data ringkasan akan bernilai 0
                        sampai kamu menambahkan transaksi.
                    </p>
                </div>
            </div>
        </div>

        <!-- Summary Cards -->
        <div class="grid grid-cols-1 gap-4 md:grid-cols-2 xl:grid-cols-4">
            <div class="rounded-xl border bg-card p-5 shadow-sm">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm text-muted-foreground">
                            Pemasukan
                        </p>
                        <p class="mt-2 text-xl font-bold text-emerald-500">
                            {{ formatCurrency(summary.totalIncome) }}
                        </p>
                    </div>

                    <div
                        class="flex h-11 w-11 items-center justify-center rounded-xl bg-emerald-100 text-emerald-600 dark:bg-emerald-950 dark:text-emerald-300"
                    >
                        <TrendingUp class="h-6 w-6" />
                    </div>
                </div>
            </div>

            <div class="rounded-xl border bg-card p-5 shadow-sm">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm text-muted-foreground">
                            Pengeluaran
                        </p>
                        <p class="mt-2 text-xl font-bold text-red-500">
                            {{ formatCurrency(summary.totalExpense) }}
                        </p>
                    </div>

                    <div
                        class="flex h-11 w-11 items-center justify-center rounded-xl bg-red-100 text-red-600 dark:bg-red-950 dark:text-red-300"
                    >
                        <TrendingDown class="h-6 w-6" />
                    </div>
                </div>
            </div>

            <div class="rounded-xl border bg-card p-5 shadow-sm">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm text-muted-foreground">
                            Saldo Bersih
                        </p>
                        <p
                            :class="[
                                'mt-2 text-xl font-bold',
                                summary.netBalance >= 0
                                    ? 'text-blue-500'
                                    : 'text-red-500',
                            ]"
                        >
                            {{ formatCurrency(summary.netBalance) }}
                        </p>
                    </div>

                    <div
                        class="flex h-11 w-11 items-center justify-center rounded-xl bg-blue-100 text-blue-600 dark:bg-blue-950 dark:text-blue-300"
                    >
                        <Wallet class="h-6 w-6" />
                    </div>
                </div>
            </div>

            <div class="rounded-xl border bg-card p-5 shadow-sm">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm text-muted-foreground">
                            Total Transaksi
                        </p>
                        <p class="mt-2 text-xl font-bold">
                            {{ summary.transactionCount || 0 }}
                        </p>
                    </div>

                    <div
                        class="flex h-11 w-11 items-center justify-center rounded-xl bg-indigo-100 text-indigo-600 dark:bg-indigo-950 dark:text-indigo-300"
                    >
                        <ReceiptText class="h-6 w-6" />
                    </div>
                </div>
            </div>
        </div>

        <!-- Income Expense Counts -->
        <div class="grid grid-cols-1 gap-4 md:grid-cols-2">
            <div class="rounded-xl border bg-card p-5 shadow-sm">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm text-muted-foreground">
                            Jumlah Transaksi Pemasukan
                        </p>
                        <p class="mt-2 text-2xl font-bold text-emerald-500">
                            {{ summary.incomeCount || 0 }}
                        </p>
                    </div>

                    <TrendingUp class="h-8 w-8 text-emerald-500" />
                </div>
            </div>

            <div class="rounded-xl border bg-card p-5 shadow-sm">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm text-muted-foreground">
                            Jumlah Transaksi Pengeluaran
                        </p>
                        <p class="mt-2 text-2xl font-bold text-red-500">
                            {{ summary.expenseCount || 0 }}
                        </p>
                    </div>

                    <TrendingDown class="h-8 w-8 text-red-500" />
                </div>
            </div>
        </div>

        <!-- Expense + Budget -->
        <div class="grid grid-cols-1 gap-6 xl:grid-cols-3">
            <!-- Expense By Category -->
            <div class="rounded-xl border bg-card p-5 shadow-sm xl:col-span-2">
                <div class="mb-4 flex items-center justify-between gap-3">
                    <div class="flex items-center gap-2">
                        <BarChart3 class="h-5 w-5 text-red-500" />
                        <h2 class="font-semibold">
                            Pengeluaran per Kategori
                        </h2>
                    </div>

                    <span class="rounded-full bg-red-100 px-3 py-1 text-xs font-medium text-red-700 dark:bg-red-950 dark:text-red-300">
                        {{ expenseByCategory.length }} kategori
                    </span>
                </div>

                <EmptyState
                    v-if="expenseByCategory.length === 0"
                    :icon="TrendingDown"
                    title="Belum ada pengeluaran"
                    description="Tidak ada pengeluaran pada bulan ini."
                />

                <div v-else class="space-y-4">
                    <div
                        v-for="category in expenseByCategory"
                        :key="category.id || category.name"
                        class="rounded-xl border p-4"
                    >
                        <div class="mb-2 flex items-center justify-between gap-3">
                            <div class="flex min-w-0 items-center gap-3">
                                <div
                                    class="h-10 w-10 shrink-0 rounded-full"
                                    :style="{ backgroundColor: category.color }"
                                ></div>

                                <div class="min-w-0">
                                    <p class="truncate font-semibold">
                                        {{ category.name }}
                                    </p>
                                    <p class="text-xs text-muted-foreground">
                                        {{ category.count }} transaksi
                                    </p>
                                </div>
                            </div>

                            <p class="shrink-0 font-bold text-red-500">
                                {{ formatCurrency(category.total) }}
                            </p>
                        </div>

                        <div class="mb-1 flex justify-between text-xs text-muted-foreground">
                            <span>Persentase</span>
                            <span>
                                {{
                                    summary.totalExpense > 0
                                        ? Math.round((category.total / summary.totalExpense) * 100)
                                        : 0
                                }}%
                            </span>
                        </div>

                        <div class="h-2 overflow-hidden rounded-full bg-muted">
                            <div
                                class="h-full rounded-full bg-red-500"
                                :style="{
                                    width: getCategoryPercentage(
                                        category.total,
                                        summary.totalExpense,
                                    ),
                                }"
                            ></div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Budget -->
            <div class="rounded-xl border bg-card p-5 shadow-sm">
                <div class="mb-4 flex items-center justify-between gap-3">
                    <div class="flex items-center gap-2">
                        <PiggyBank class="h-5 w-5 text-indigo-500" />
                        <h2 class="font-semibold">
                            Budget Bulanan
                        </h2>
                    </div>

                    <span
                        v-if="hasBudget"
                        :class="[
                            'rounded-full px-3 py-1 text-xs font-medium',
                            getStatusClass(getTotalBudgetStatus),
                        ]"
                    >
                        {{ getStatusLabel(getTotalBudgetStatus) }}
                    </span>
                </div>

                <EmptyState
                    v-if="!hasBudget"
                    :icon="PiggyBank"
                    title="Belum ada budget"
                    description="Buat budget bulan ini untuk memantau batas pengeluaran."
                    action-label="Buat Budget"
                    action-href="/budgets"
                />

                <div v-else class="space-y-4">
                    <div class="grid grid-cols-1 gap-3">
                        <div class="rounded-lg bg-muted/40 p-3">
                            <p class="text-xs text-muted-foreground">
                                Total Budget
                            </p>
                            <p class="font-bold">
                                {{ formatCurrency(budget.totalBudget) }}
                            </p>
                        </div>

                        <div class="rounded-lg bg-muted/40 p-3">
                            <p class="text-xs text-muted-foreground">
                                Terpakai
                            </p>
                            <p class="font-bold text-red-500">
                                {{ formatCurrency(budget.totalUsed) }}
                            </p>
                        </div>

                        <div class="rounded-lg bg-muted/40 p-3">
                            <p class="text-xs text-muted-foreground">
                                Sisa
                            </p>
                            <p
                                :class="[
                                    'font-bold',
                                    budget.totalRemaining >= 0
                                        ? 'text-emerald-500'
                                        : 'text-red-500',
                                ]"
                            >
                                {{ formatCurrency(budget.totalRemaining) }}
                            </p>
                        </div>
                    </div>

                    <div>
                        <div class="mb-1 flex justify-between text-xs text-muted-foreground">
                            <span>Progress</span>
                            <span>{{ budget.totalPercentage || 0 }}%</span>
                        </div>

                        <div class="h-3 overflow-hidden rounded-full bg-muted">
                            <div
                                class="h-full rounded-full"
                                :class="getProgressClass(getTotalBudgetStatus)"
                                :style="{
                                    width: Math.min(budget.totalPercentage || 0, 100) + '%',
                                }"
                            ></div>
                        </div>
                    </div>

                    <div
                        v-if="budget.warnings && budget.warnings.length > 0"
                        class="rounded-xl border border-yellow-200 bg-yellow-50 p-3 text-sm dark:border-yellow-900 dark:bg-yellow-950/40"
                    >
                        <div class="mb-2 flex items-center gap-2 font-semibold text-yellow-700 dark:text-yellow-300">
                            <AlertTriangle class="h-4 w-4" />
                            Peringatan Budget
                        </div>

                        <div class="space-y-2">
                            <div
                                v-for="item in budget.warnings"
                                :key="item.id"
                                class="rounded-lg bg-background p-2"
                            >
                                <div class="flex items-center justify-between gap-2">
                                    <span class="truncate">
                                        {{ item.category_name }}
                                    </span>

                                    <span
                                        :class="[
                                            'shrink-0 rounded-full px-2 py-1 text-xs',
                                            getStatusClass(item.status),
                                        ]"
                                    >
                                        {{ getStatusLabel(item.status) }}
                                    </span>
                                </div>

                                <p class="mt-1 text-xs text-muted-foreground">
                                    Terpakai
                                    {{ formatCurrency(item.used_amount) }}
                                    dari
                                    {{ formatCurrency(item.amount) }}
                                </p>
                            </div>
                        </div>
                    </div>

                    <Link
                        href="/budgets"
                        class="inline-flex w-full justify-center rounded-lg border px-4 py-2 text-sm font-medium transition hover:bg-muted"
                    >
                        Kelola Budget
                    </Link>
                </div>
            </div>
        </div>

        <!-- Income + Largest Transactions -->
        <div class="grid grid-cols-1 gap-6 xl:grid-cols-2">
            <!-- Income By Category -->
            <div class="rounded-xl border bg-card p-5 shadow-sm">
                <div class="mb-4 flex items-center justify-between gap-3">
                    <div class="flex items-center gap-2">
                        <BarChart3 class="h-5 w-5 text-emerald-500" />
                        <h2 class="font-semibold">
                            Pemasukan per Kategori
                        </h2>
                    </div>

                    <span class="rounded-full bg-emerald-100 px-3 py-1 text-xs font-medium text-emerald-700 dark:bg-emerald-950 dark:text-emerald-300">
                        {{ incomeByCategory.length }} kategori
                    </span>
                </div>

                <EmptyState
                    v-if="incomeByCategory.length === 0"
                    :icon="TrendingUp"
                    title="Belum ada pemasukan"
                    description="Tidak ada pemasukan pada bulan ini."
                />

                <div v-else class="space-y-4">
                    <div
                        v-for="category in incomeByCategory"
                        :key="category.id || category.name"
                        class="rounded-xl border p-4"
                    >
                        <div class="mb-2 flex items-center justify-between gap-3">
                            <div class="flex min-w-0 items-center gap-3">
                                <div
                                    class="h-10 w-10 shrink-0 rounded-full"
                                    :style="{ backgroundColor: category.color }"
                                ></div>

                                <div class="min-w-0">
                                    <p class="truncate font-semibold">
                                        {{ category.name }}
                                    </p>
                                    <p class="text-xs text-muted-foreground">
                                        {{ category.count }} transaksi
                                    </p>
                                </div>
                            </div>

                            <p class="shrink-0 font-bold text-emerald-500">
                                {{ formatCurrency(category.total) }}
                            </p>
                        </div>

                        <div class="mb-1 flex justify-between text-xs text-muted-foreground">
                            <span>Persentase</span>
                            <span>
                                {{
                                    summary.totalIncome > 0
                                        ? Math.round((category.total / summary.totalIncome) * 100)
                                        : 0
                                }}%
                            </span>
                        </div>

                        <div class="h-2 overflow-hidden rounded-full bg-muted">
                            <div
                                class="h-full rounded-full bg-emerald-500"
                                :style="{
                                    width: getCategoryPercentage(
                                        category.total,
                                        summary.totalIncome,
                                    ),
                                }"
                            ></div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Largest Transactions -->
            <div class="rounded-xl border bg-card p-5 shadow-sm">
                <div class="mb-4 flex items-center justify-between gap-3">
                    <div class="flex items-center gap-2">
                        <ReceiptText class="h-5 w-5 text-blue-500" />
                        <h2 class="font-semibold">
                            Transaksi Terbesar
                        </h2>
                    </div>

                    <span class="rounded-full bg-blue-100 px-3 py-1 text-xs font-medium text-blue-700 dark:bg-blue-950 dark:text-blue-300">
                        Top {{ largestTransactions.length }}
                    </span>
                </div>

                <EmptyState
                    v-if="largestTransactions.length === 0"
                    :icon="ReceiptText"
                    title="Belum ada transaksi"
                    description="Belum ada transaksi pada bulan ini."
                    action-label="Tambah Transaksi"
                    action-href="/transactions/create"
                />

                <div v-else class="space-y-3">
                    <div
                        v-for="transaction in largestTransactions"
                        :key="transaction.id"
                        class="flex items-center justify-between gap-3 rounded-xl border p-3"
                    >
                        <div class="min-w-0">
                            <p class="truncate font-medium">
                                {{ transaction.category_name }}
                            </p>
                            <p class="text-xs text-muted-foreground">
                                {{ transaction.account_name }} • {{ transaction.date }}
                            </p>
                            <p
                                v-if="transaction.description && transaction.description !== '-'"
                                class="mt-1 truncate text-xs text-muted-foreground"
                            >
                                {{ transaction.description }}
                            </p>
                        </div>

                        <p
                            :class="[
                                'shrink-0 font-bold',
                                transaction.type === 'income'
                                    ? 'text-emerald-500'
                                    : 'text-red-500',
                            ]"
                        >
                            {{ transaction.type === 'income' ? '+' : '-' }}
                            {{ formatCurrency(transaction.amount) }}
                        </p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Footer Action -->
        <div class="flex flex-col gap-2 rounded-xl border bg-card p-5 shadow-sm sm:flex-row sm:items-center sm:justify-between">
            <div>
                <h3 class="font-semibold">
                    Butuh detail transaksi?
                </h3>
                <p class="text-sm text-muted-foreground">
                    Buka halaman transaksi untuk melihat, mencari, atau mengubah data lengkap.
                </p>
            </div>

            <Link
                href="/transactions"
                class="inline-flex items-center justify-center gap-2 rounded-lg bg-indigo-600 px-4 py-2 text-sm font-medium text-white transition hover:bg-indigo-700"
            >
                <CircleDollarSign class="h-4 w-4" />
                Lihat Transaksi
            </Link>
        </div>
    </div>
</template>
