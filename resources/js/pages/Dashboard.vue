<script setup lang="ts">
import { Head, Link, router } from '@inertiajs/vue3';
import {
    Wallet,
    TrendingUp,
    TrendingDown,
    CreditCard,
    Calendar,
    ArrowUpRight,
    ArrowDownRight,
    FileText,
    Landmark,
    NotebookText,
} from 'lucide-vue-next';
import { computed, ref } from 'vue';

import {
    Chart as ChartJS,
    CategoryScale,
    LinearScale,
    BarElement,
    Title,
    Tooltip,
    Legend,
} from 'chart.js';

import { Bar } from 'vue-chartjs';

ChartJS.register(
    CategoryScale,
    LinearScale,
    BarElement,
    Title,
    Tooltip,
    Legend,
);

defineOptions({
    layout: {
        breadcrumbs: [
            {
                title: 'Dashboard',
                href: '/dashboard',
            },
        ],
    },
});

interface Transaction {
    id: number;
    category_name: string;
    category_color: string;
    type: 'income' | 'expense';
    amount: number;
    account_name: string;
    transaction_date: string;
    description: string | null;
}

interface Note {
    id: number;
    title: string;
    content: string;
    label: string | null;
    note_date: string;
}

interface Account {
    id: number;
    name: string;
    type: string;
    current_balance: number;
    initial_balance: number;
}

interface ChartData {
    months: string[];
    income: number[];
    expense: number[];
}

interface Props {
    totalBalance: number;
    monthlyIncome: number;
    monthlyExpense: number;
    netBalance: number;
    chartData: ChartData;
    recentTransactions: Transaction[];
    recentNotes: Note[];
    accountSummary: Account[];
    selectedYear: number;
    availableYears: number[];
}

const props = defineProps<Props>();

const selectedYear = ref(props.selectedYear);

const safeAvailableYears = computed(() => {
    if (props.availableYears && props.availableYears.length > 0) {
        return props.availableYears;
    }

    return [new Date().getFullYear()];
});

const combinedChartData = computed(() => {
    return {
        labels: props.chartData?.months ?? [],
        datasets: [
            {
                label: 'Pemasukan',
                data: props.chartData?.income ?? [],
                backgroundColor: 'rgba(16, 185, 129, 0.8)',
                borderColor: '#10b981',
                borderWidth: 1,
                borderRadius: 6,
            },
            {
                label: 'Pengeluaran',
                data: props.chartData?.expense ?? [],
                backgroundColor: 'rgba(239, 68, 68, 0.8)',
                borderColor: '#ef4444',
                borderWidth: 1,
                borderRadius: 6,
            },
        ],
    };
});

const chartOptions = {
    responsive: true,
    maintainAspectRatio: false,
    plugins: {
        legend: {
            position: 'top' as const,
            labels: {
                color: '#6b7280',
                usePointStyle: true,
                boxWidth: 8,
                boxHeight: 8,
            },
        },
        tooltip: {
            callbacks: {
                label: function (context: any) {
                    const label = context.dataset.label || '';
                    const value = context.parsed.y || 0;

                    return `${label}: ${formatCurrency(value)}`;
                },
            },
        },
    },
    scales: {
        y: {
            beginAtZero: true,
            ticks: {
                color: '#6b7280',
                callback: function (value: any) {
                    return formatCompactCurrency(Number(value));
                },
            },
            grid: {
                color: '#e5e7eb',
            },
        },
        x: {
            ticks: {
                color: '#6b7280',
            },
            grid: {
                display: false,
            },
        },
    },
};

const handleYearChange = (year: number) => {
    selectedYear.value = year;

    router.get(
        '/dashboard',
        { year },
        {
            preserveScroll: true,
            preserveState: true,
        },
    );
};

const formatCurrency = (value: number | string | null | undefined) => {
    const numericValue = Number(value ?? 0);

    return new Intl.NumberFormat('id-ID', {
        style: 'currency',
        currency: 'IDR',
        minimumFractionDigits: 0,
        maximumFractionDigits: 0,
    }).format(numericValue);
};

const formatCompactCurrency = (value: number) => {
    if (value >= 1_000_000_000) {
        return `Rp${Math.round(value / 1_000_000_000)}M`;
    }

    if (value >= 1_000_000) {
        return `Rp${Math.round(value / 1_000_000)}Jt`;
    }

    if (value >= 1_000) {
        return `Rp${Math.round(value / 1_000)}Rb`;
    }

    return `Rp${value}`;
};

const formatDate = (date: string | null | undefined) => {
    if (!date) return '-';

    return new Date(date).toLocaleDateString('id-ID', {
        day: '2-digit',
        month: 'short',
        year: 'numeric',
    });
};

const truncateContent = (content: string | null | undefined, limit = 90) => {
    if (!content) return '-';

    if (content.length <= limit) {
        return content;
    }

    return `${content.substring(0, limit)}...`;
};

const getTransactionInitial = (transaction: Transaction) => {
    return transaction.category_name?.charAt(0)?.toUpperCase() || '?';
};

const getTransactionColor = (transaction: Transaction) => {
    return transaction.category_color || '#6366f1';
};

const netStatusText = computed(() => {
    if (props.netBalance > 0) return 'Surplus bulan ini';
    if (props.netBalance < 0) return 'Defisit bulan ini';

    return 'Seimbang bulan ini';
});
</script>

<template>
    <Head title="Dashboard - Loovie Apps" />

    <div class="space-y-6 p-4 sm:p-6 lg:p-8">
        <!-- Header -->
        <div
            class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between"
        >
            <div>
                <h1 class="text-2xl font-bold text-gray-900 sm:text-3xl dark:text-white">
                    Dashboard
                </h1>
                <p class="mt-1 text-sm text-gray-600 dark:text-gray-400">
                    Ringkasan keuangan, transaksi, akun, dan catatan terbaru.
                </p>
            </div>

            <div class="flex items-center gap-2">
                <Calendar class="h-4 w-4 text-gray-500" />

                <select
                    :value="selectedYear"
                    class="rounded-lg border border-gray-200 bg-white px-3 py-2 text-sm text-gray-900 shadow-sm focus:border-indigo-500 focus:ring-2 focus:ring-indigo-500 dark:border-gray-700 dark:bg-gray-900 dark:text-white"
                    @change="handleYearChange(Number(($event.target as HTMLSelectElement).value))"
                >
                    <option
                        v-for="year in safeAvailableYears"
                        :key="year"
                        :value="year"
                    >
                        {{ year }}
                    </option>
                </select>
            </div>
        </div>

        <!-- Summary Cards -->
        <div class="grid grid-cols-1 gap-4 md:grid-cols-2 xl:grid-cols-4">
            <div
                class="rounded-2xl border border-blue-100 bg-white p-5 shadow-sm dark:border-blue-900/40 dark:bg-gray-900"
            >
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm font-medium text-gray-500 dark:text-gray-400">
                            Total Saldo
                        </p>
                        <p class="mt-2 text-2xl font-bold text-gray-900 dark:text-white">
                            {{ formatCurrency(totalBalance) }}
                        </p>
                    </div>

                    <div
                        class="flex h-12 w-12 items-center justify-center rounded-xl bg-blue-50 text-blue-600 dark:bg-blue-950 dark:text-blue-300"
                    >
                        <Wallet class="h-6 w-6" />
                    </div>
                </div>

                <p class="mt-4 text-xs text-gray-500 dark:text-gray-400">
                    Total saldo dari semua akun aktif.
                </p>
            </div>

            <div
                class="rounded-2xl border border-emerald-100 bg-white p-5 shadow-sm dark:border-emerald-900/40 dark:bg-gray-900"
            >
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm font-medium text-gray-500 dark:text-gray-400">
                            Pemasukan Bulan Ini
                        </p>
                        <p class="mt-2 text-2xl font-bold text-emerald-600 dark:text-emerald-400">
                            {{ formatCurrency(monthlyIncome) }}
                        </p>
                    </div>

                    <div
                        class="flex h-12 w-12 items-center justify-center rounded-xl bg-emerald-50 text-emerald-600 dark:bg-emerald-950 dark:text-emerald-300"
                    >
                        <TrendingUp class="h-6 w-6" />
                    </div>
                </div>

                <div class="mt-4 flex items-center gap-1 text-xs text-emerald-600 dark:text-emerald-400">
                    <ArrowUpRight class="h-3.5 w-3.5" />
                    Income transaction summary
                </div>
            </div>

            <div
                class="rounded-2xl border border-red-100 bg-white p-5 shadow-sm dark:border-red-900/40 dark:bg-gray-900"
            >
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm font-medium text-gray-500 dark:text-gray-400">
                            Pengeluaran Bulan Ini
                        </p>
                        <p class="mt-2 text-2xl font-bold text-red-600 dark:text-red-400">
                            {{ formatCurrency(monthlyExpense) }}
                        </p>
                    </div>

                    <div
                        class="flex h-12 w-12 items-center justify-center rounded-xl bg-red-50 text-red-600 dark:bg-red-950 dark:text-red-300"
                    >
                        <TrendingDown class="h-6 w-6" />
                    </div>
                </div>

                <div class="mt-4 flex items-center gap-1 text-xs text-red-600 dark:text-red-400">
                    <ArrowDownRight class="h-3.5 w-3.5" />
                    Expense transaction summary
                </div>
            </div>

            <div
                class="rounded-2xl border border-purple-100 bg-white p-5 shadow-sm dark:border-purple-900/40 dark:bg-gray-900"
            >
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm font-medium text-gray-500 dark:text-gray-400">
                            Saldo Bersih
                        </p>
                        <p
                            :class="[
                                'mt-2 text-2xl font-bold',
                                netBalance >= 0
                                    ? 'text-purple-600 dark:text-purple-400'
                                    : 'text-orange-600 dark:text-orange-400',
                            ]"
                        >
                            {{ formatCurrency(netBalance) }}
                        </p>
                    </div>

                    <div
                        class="flex h-12 w-12 items-center justify-center rounded-xl bg-purple-50 text-purple-600 dark:bg-purple-950 dark:text-purple-300"
                    >
                        <CreditCard class="h-6 w-6" />
                    </div>
                </div>

                <p class="mt-4 text-xs text-gray-500 dark:text-gray-400">
                    {{ netStatusText }}
                </p>
            </div>
        </div>

        <!-- Main Chart -->
        <div
            class="rounded-2xl border border-gray-200 bg-white p-5 shadow-sm dark:border-gray-800 dark:bg-gray-900"
        >
            <div
                class="mb-5 flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between"
            >
                <div>
                    <h2 class="text-lg font-semibold text-gray-900 dark:text-white">
                        Pemasukan vs Pengeluaran
                    </h2>
                    <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">
                        Perbandingan transaksi bulanan tahun {{ selectedYear }}.
                    </p>
                </div>

                <Link
                    href="/transactions"
                    class="inline-flex items-center justify-center rounded-lg border border-gray-200 px-3 py-2 text-sm font-medium text-gray-700 hover:bg-gray-50 dark:border-gray-700 dark:text-gray-300 dark:hover:bg-gray-800"
                >
                    Lihat Transaksi
                </Link>
            </div>

            <div class="h-80">
                <Bar
                    :data="combinedChartData"
                    :options="chartOptions"
                />
            </div>
        </div>

        <!-- Transactions + Accounts -->
        <div class="grid grid-cols-1 gap-6 xl:grid-cols-3">
            <!-- Recent Transactions -->
            <div class="xl:col-span-2">
                <div
                    class="h-full rounded-2xl border border-gray-200 bg-white p-5 shadow-sm dark:border-gray-800 dark:bg-gray-900"
                >
                    <div class="mb-4 flex items-center justify-between">
                        <div class="flex items-center gap-2">
                            <div
                                class="flex h-9 w-9 items-center justify-center rounded-lg bg-indigo-50 text-indigo-600 dark:bg-indigo-950 dark:text-indigo-300"
                            >
                                <FileText class="h-5 w-5" />
                            </div>
                            <h2 class="text-lg font-semibold text-gray-900 dark:text-white">
                                Transaksi Terbaru
                            </h2>
                        </div>

                        <Link
                            href="/transactions"
                            class="text-sm font-medium text-indigo-600 hover:text-indigo-700 dark:text-indigo-400"
                        >
                            View All
                        </Link>
                    </div>

                    <div
                        v-if="recentTransactions.length === 0"
                        class="rounded-xl border border-dashed border-gray-200 py-10 text-center text-sm text-gray-500 dark:border-gray-700 dark:text-gray-400"
                    >
                        Belum ada transaksi.
                    </div>

                    <div v-else class="space-y-3">
                        <div
                            v-for="transaction in recentTransactions"
                            :key="transaction.id"
                            class="flex items-center justify-between gap-3 rounded-xl border border-gray-100 bg-gray-50 p-3 transition hover:bg-gray-100 dark:border-gray-800 dark:bg-gray-800 dark:hover:bg-gray-700"
                        >
                            <div class="flex min-w-0 flex-1 items-center gap-3">
                                <div
                                    :style="{
                                        backgroundColor: getTransactionColor(transaction),
                                    }"
                                    class="flex h-10 w-10 shrink-0 items-center justify-center rounded-full text-sm font-semibold text-white"
                                >
                                    {{ getTransactionInitial(transaction) }}
                                </div>

                                <div class="min-w-0">
                                    <p class="truncate text-sm font-medium text-gray-900 dark:text-white">
                                        {{ transaction.category_name || '-' }}
                                    </p>
                                    <p class="truncate text-xs text-gray-500 dark:text-gray-400">
                                        {{ transaction.account_name || '-' }}
                                        •
                                        {{ formatDate(transaction.transaction_date) }}
                                    </p>
                                </div>
                            </div>

                            <p
                                :class="[
                                    'shrink-0 text-sm font-semibold',
                                    transaction.type === 'income'
                                        ? 'text-emerald-600 dark:text-emerald-400'
                                        : 'text-red-600 dark:text-red-400',
                                ]"
                            >
                                {{ transaction.type === 'income' ? '+' : '-' }}
                                {{ formatCurrency(transaction.amount) }}
                            </p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Account Summary -->
            <div>
                <div
                    class="h-full rounded-2xl border border-gray-200 bg-white p-5 shadow-sm dark:border-gray-800 dark:bg-gray-900"
                >
                    <div class="mb-4 flex items-center justify-between">
                        <div class="flex items-center gap-2">
                            <div
                                class="flex h-9 w-9 items-center justify-center rounded-lg bg-blue-50 text-blue-600 dark:bg-blue-950 dark:text-blue-300"
                            >
                                <Landmark class="h-5 w-5" />
                            </div>
                            <h2 class="text-lg font-semibold text-gray-900 dark:text-white">
                                Akun Keuangan
                            </h2>
                        </div>

                        <Link
                            href="/financial-accounts"
                            class="text-sm font-medium text-indigo-600 hover:text-indigo-700 dark:text-indigo-400"
                        >
                            Manage
                        </Link>
                    </div>

                    <div
                        v-if="accountSummary.length === 0"
                        class="rounded-xl border border-dashed border-gray-200 py-10 text-center text-sm text-gray-500 dark:border-gray-700 dark:text-gray-400"
                    >
                        Belum ada akun.
                    </div>

                    <div v-else class="space-y-3">
                        <div
                            v-for="account in accountSummary"
                            :key="account.id"
                            class="rounded-xl border border-gray-100 bg-gray-50 p-3 dark:border-gray-800 dark:bg-gray-800"
                        >
                            <div class="mb-2 flex items-center justify-between gap-2">
                                <p class="truncate text-sm font-medium text-gray-900 dark:text-white">
                                    {{ account.name }}
                                </p>

                                <span class="shrink-0 rounded-full bg-gray-200 px-2 py-1 text-xs text-gray-700 dark:bg-gray-700 dark:text-gray-300">
                                    {{ account.type }}
                                </span>
                            </div>

                            <p class="text-lg font-bold text-gray-900 dark:text-white">
                                {{ formatCurrency(account.current_balance) }}
                            </p>

                            <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">
                                Saldo awal:
                                {{ formatCurrency(account.initial_balance) }}
                            </p>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Recent Notes -->
        <div
            class="rounded-2xl border border-gray-200 bg-white p-5 shadow-sm dark:border-gray-800 dark:bg-gray-900"
        >
            <div class="mb-4 flex items-center justify-between">
                <div class="flex items-center gap-2">
                    <div
                        class="flex h-9 w-9 items-center justify-center rounded-lg bg-yellow-50 text-yellow-600 dark:bg-yellow-950 dark:text-yellow-300"
                    >
                        <NotebookText class="h-5 w-5" />
                    </div>
                    <h2 class="text-lg font-semibold text-gray-900 dark:text-white">
                        Catatan Terbaru
                    </h2>
                </div>

                <Link
                    href="/notes"
                    class="text-sm font-medium text-indigo-600 hover:text-indigo-700 dark:text-indigo-400"
                >
                    View All
                </Link>
            </div>

            <div
                v-if="recentNotes.length === 0"
                class="rounded-xl border border-dashed border-gray-200 py-10 text-center text-sm text-gray-500 dark:border-gray-700 dark:text-gray-400"
            >
                Belum ada catatan.
            </div>

            <div
                v-else
                class="grid grid-cols-1 gap-4 md:grid-cols-2 xl:grid-cols-3"
            >
                <div
                    v-for="note in recentNotes"
                    :key="note.id"
                    class="rounded-xl border border-yellow-100 bg-yellow-50 p-4 transition hover:shadow-sm dark:border-yellow-900/40 dark:bg-yellow-950/20"
                >
                    <div class="mb-2 flex items-start justify-between gap-2">
                        <h3 class="line-clamp-1 text-sm font-semibold text-gray-900 dark:text-white">
                            {{ note.title }}
                        </h3>

                        <span class="shrink-0 text-xs text-gray-500 dark:text-gray-400">
                            {{ formatDate(note.note_date) }}
                        </span>
                    </div>

                    <p class="line-clamp-2 text-sm text-gray-700 dark:text-gray-300">
                        {{ truncateContent(note.content, 90) }}
                    </p>

                    <div class="mt-3">
                        <span
                            v-if="note.label"
                            class="rounded-full bg-white px-2 py-1 text-xs font-medium text-yellow-700 ring-1 ring-yellow-200 dark:bg-gray-900 dark:text-yellow-300 dark:ring-yellow-900"
                        >
                            {{ note.label }}
                        </span>

                        <span
                            v-else
                            class="text-xs text-gray-400"
                        >
                            Tanpa label
                        </span>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>
