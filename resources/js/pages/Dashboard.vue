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
    PiggyBank,
    AlertTriangle,
    Plus,
    CircleDollarSign,
    BarChart3,
} from 'lucide-vue-next';
import { computed, ref } from 'vue';

import EmptyState from '@/components/EmptyState.vue';

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

interface BudgetItem {
    id: number;
    category_id?: number;
    category_name: string;
    category_color: string;
    month?: string;
    amount: number;
    used_amount: number;
    remaining_amount: number;
    percentage: number;
    status: 'safe' | 'warning' | 'over';
    description?: string | null;
}

interface BudgetSummary {
    month: string;
    totalAccountBalance: number;
    totalBudget: number;
    totalUsed: number;
    totalRemaining: number;
    unallocatedBalance: number;
    totalPercentage: number;
    warnings: BudgetItem[];
    items: BudgetItem[];
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
    budgetSummary: BudgetSummary;
}

const props = defineProps<Props>();

const selectedYear = ref(props.selectedYear);

const safeAvailableYears = computed(() => {
    if (props.availableYears && props.availableYears.length > 0) {
        return props.availableYears;
    }

    return [new Date().getFullYear()];
});

const safeBudgetSummary = computed<BudgetSummary>(() => {
    return (
        props.budgetSummary || {
            month: new Date().toISOString().slice(0, 7),
            totalAccountBalance: 0,
            totalBudget: 0,
            totalUsed: 0,
            totalRemaining: 0,
            unallocatedBalance: 0,
            totalPercentage: 0,
            warnings: [],
            items: [],
        }
    );
});

const hasChartData = computed(() => {
    const incomeTotal = props.chartData?.income?.reduce(
        (total, value) => total + Number(value || 0),
        0,
    );

    const expenseTotal = props.chartData?.expense?.reduce(
        (total, value) => total + Number(value || 0),
        0,
    );

    return Number(incomeTotal || 0) > 0 || Number(expenseTotal || 0) > 0;
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

    if (content.length <= limit) return content;

    return `${content.substring(0, limit)}...`;
};

const getTransactionInitial = (transaction: Transaction) => {
    return transaction.category_name?.charAt(0)?.toUpperCase() || '?';
};

const getTransactionColor = (transaction: Transaction) => {
    return transaction.category_color || '#6366f1';
};

const getAccountTypeLabel = (type: string) => {
    const labels: Record<string, string> = {
        cash: 'Cash',
        bank: 'Bank',
        digital_wallet: 'E-Wallet',
        investment: 'Investasi',
        credit_card: 'Kartu Kredit',
    };

    return labels[type] || type;
};

const netStatusText = computed(() => {
    if (props.netBalance > 0) return 'Surplus bulan ini';
    if (props.netBalance < 0) return 'Defisit bulan ini';

    return 'Seimbang bulan ini';
});

const budgetOverallStatus = computed(() => {
    const percentage = Number(safeBudgetSummary.value.totalPercentage || 0);

    if (percentage >= 100) return 'over';
    if (percentage >= 80) return 'warning';

    return 'safe';
});

const getBudgetStatusLabel = (status: string) => {
    if (status === 'over') return 'Melebihi Budget';
    if (status === 'warning') return 'Hampir Limit';

    return 'Aman';
};

const getBudgetStatusClass = (status: string) => {
    if (status === 'over') {
        return 'bg-red-100 text-red-700 dark:bg-red-950 dark:text-red-300';
    }

    if (status === 'warning') {
        return 'bg-yellow-100 text-yellow-700 dark:bg-yellow-950 dark:text-yellow-300';
    }

    return 'bg-emerald-100 text-emerald-700 dark:bg-emerald-950 dark:text-emerald-300';
};

const getBudgetProgressClass = (status: string) => {
    if (status === 'over') return 'bg-red-500';
    if (status === 'warning') return 'bg-yellow-500';

    return 'bg-emerald-500';
};
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
                    Ringkasan keuangan, budget, transaksi, akun, dan catatan terbaru.
                </p>
            </div>

            <div class="flex items-center gap-2">
                <Calendar class="h-4 w-4 text-gray-500" />

                <select
                    :value="selectedYear"
                    class="rounded-lg border border-gray-200 bg-white px-3 py-2 text-sm text-gray-900 shadow-sm focus:border-indigo-500 focus:ring-2 focus:ring-indigo-500 dark:border-gray-700 dark:bg-gray-900 dark:text-white"
                    @change="
                        handleYearChange(
                            Number(($event.target as HTMLSelectElement).value),
                        )
                    "
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

        <!-- Quick Actions -->
        <div
            class="rounded-2xl border border-gray-200 bg-white p-5 shadow-sm dark:border-gray-800 dark:bg-gray-900"
        >
            <div
                class="mb-4 flex flex-col gap-2 sm:flex-row sm:items-center sm:justify-between"
            >
                <div>
                    <h2 class="text-lg font-semibold text-gray-900 dark:text-white">
                        Aksi Cepat
                    </h2>
                    <p class="text-sm text-gray-500 dark:text-gray-400">
                        Tambahkan data penting tanpa perlu membuka menu satu per satu.
                    </p>
                </div>

                <Plus class="hidden h-5 w-5 text-gray-400 sm:block" />
            </div>

            <div class="grid grid-cols-1 gap-3 sm:grid-cols-2 xl:grid-cols-4">
                <Link
                    href="/transactions/create"
                    class="group rounded-xl border border-gray-200 bg-gray-50 p-4 transition hover:border-emerald-200 hover:bg-emerald-50 dark:border-gray-800 dark:bg-gray-800 dark:hover:border-emerald-900 dark:hover:bg-emerald-950/30"
                >
                    <div class="flex items-center gap-3">
                        <div
                            class="flex h-11 w-11 items-center justify-center rounded-xl bg-emerald-100 text-emerald-600 transition group-hover:bg-emerald-600 group-hover:text-white dark:bg-emerald-950 dark:text-emerald-300"
                        >
                            <CircleDollarSign class="h-5 w-5" />
                        </div>

                        <div class="min-w-0">
                            <p class="font-semibold text-gray-900 dark:text-white">
                                Tambah Transaksi
                            </p>
                            <p class="text-xs text-gray-500 dark:text-gray-400">
                                Catat pemasukan/pengeluaran
                            </p>
                        </div>
                    </div>
                </Link>

                <Link
                    href="/budgets"
                    class="group rounded-xl border border-gray-200 bg-gray-50 p-4 transition hover:border-indigo-200 hover:bg-indigo-50 dark:border-gray-800 dark:bg-gray-800 dark:hover:border-indigo-900 dark:hover:bg-indigo-950/30"
                >
                    <div class="flex items-center gap-3">
                        <div
                            class="flex h-11 w-11 items-center justify-center rounded-xl bg-indigo-100 text-indigo-600 transition group-hover:bg-indigo-600 group-hover:text-white dark:bg-indigo-950 dark:text-indigo-300"
                        >
                            <PiggyBank class="h-5 w-5" />
                        </div>

                        <div class="min-w-0">
                            <p class="font-semibold text-gray-900 dark:text-white">
                                Tambah Budget
                            </p>
                            <p class="text-xs text-gray-500 dark:text-gray-400">
                                Atur batas pengeluaran
                            </p>
                        </div>
                    </div>
                </Link>

                <Link
                    href="/notes/create"
                    class="group rounded-xl border border-gray-200 bg-gray-50 p-4 transition hover:border-yellow-200 hover:bg-yellow-50 dark:border-gray-800 dark:bg-gray-800 dark:hover:border-yellow-900 dark:hover:bg-yellow-950/30"
                >
                    <div class="flex items-center gap-3">
                        <div
                            class="flex h-11 w-11 items-center justify-center rounded-xl bg-yellow-100 text-yellow-600 transition group-hover:bg-yellow-500 group-hover:text-white dark:bg-yellow-950 dark:text-yellow-300"
                        >
                            <NotebookText class="h-5 w-5" />
                        </div>

                        <div class="min-w-0">
                            <p class="font-semibold text-gray-900 dark:text-white">
                                Tambah Catatan
                            </p>
                            <p class="text-xs text-gray-500 dark:text-gray-400">
                                Simpan catatan keuangan
                            </p>
                        </div>
                    </div>
                </Link>

                <Link
                    href="/financial-accounts/create"
                    class="group rounded-xl border border-gray-200 bg-gray-50 p-4 transition hover:border-blue-200 hover:bg-blue-50 dark:border-gray-800 dark:bg-gray-800 dark:hover:border-blue-900 dark:hover:bg-blue-950/30"
                >
                    <div class="flex items-center gap-3">
                        <div
                            class="flex h-11 w-11 items-center justify-center rounded-xl bg-blue-100 text-blue-600 transition group-hover:bg-blue-600 group-hover:text-white dark:bg-blue-950 dark:text-blue-300"
                        >
                            <Landmark class="h-5 w-5" />
                        </div>

                        <div class="min-w-0">
                            <p class="font-semibold text-gray-900 dark:text-white">
                                Tambah Akun
                            </p>
                            <p class="text-xs text-gray-500 dark:text-gray-400">
                                Buat akun keuangan baru
                            </p>
                        </div>
                    </div>
                </Link>
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

                <div
                    class="mt-4 flex items-center gap-1 text-xs text-emerald-600 dark:text-emerald-400"
                >
                    <ArrowUpRight class="h-3.5 w-3.5" />
                    Ringkasan pemasukan bulan ini
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

                <div
                    class="mt-4 flex items-center gap-1 text-xs text-red-600 dark:text-red-400"
                >
                    <ArrowDownRight class="h-3.5 w-3.5" />
                    Ringkasan pengeluaran bulan ini
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

        <!-- Budget Overview -->
        <div
            class="rounded-2xl border border-gray-200 bg-white p-5 shadow-sm dark:border-gray-800 dark:bg-gray-900"
        >
            <div
                class="mb-5 flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between"
            >
                <div class="flex items-center gap-3">
                    <div
                        class="flex h-10 w-10 items-center justify-center rounded-xl bg-indigo-50 text-indigo-600 dark:bg-indigo-950 dark:text-indigo-300"
                    >
                        <PiggyBank class="h-5 w-5" />
                    </div>

                    <div>
                        <h2 class="text-lg font-semibold text-gray-900 dark:text-white">
                            Budget Bulanan
                        </h2>
                        <p class="text-sm text-gray-500 dark:text-gray-400">
                            Pantau penggunaan budget bulan
                            {{ safeBudgetSummary.month }}.
                        </p>
                    </div>
                </div>

                <Link
                    href="/budgets"
                    class="inline-flex items-center justify-center rounded-lg border border-gray-200 px-3 py-2 text-sm font-medium text-gray-700 transition hover:bg-gray-50 dark:border-gray-700 dark:text-gray-300 dark:hover:bg-gray-800"
                >
                    Kelola Budget
                </Link>
            </div>

            <EmptyState
                v-if="safeBudgetSummary.totalBudget <= 0"
                :icon="PiggyBank"
                title="Belum ada budget bulan ini"
                description="Buat budget untuk mengontrol pengeluaran tiap kategori."
                action-label="Tambah Budget"
                action-href="/budgets"
            />

            <div v-else class="space-y-5">
                <div class="grid grid-cols-1 gap-4 md:grid-cols-5">
                    <div class="rounded-xl bg-gray-50 p-4 dark:bg-gray-800">
                        <p class="text-sm text-gray-500 dark:text-gray-400">
                            Saldo Akun
                        </p>
                        <p class="mt-2 text-xl font-bold text-gray-900 dark:text-white">
                            {{ formatCurrency(safeBudgetSummary.totalAccountBalance) }}
                        </p>
                    </div>

                    <div class="rounded-xl bg-gray-50 p-4 dark:bg-gray-800">
                        <p class="text-sm text-gray-500 dark:text-gray-400">
                            Budget
                        </p>
                        <p class="mt-2 text-xl font-bold text-indigo-600 dark:text-indigo-400">
                            {{ formatCurrency(safeBudgetSummary.totalBudget) }}
                        </p>
                    </div>

                    <div class="rounded-xl bg-gray-50 p-4 dark:bg-gray-800">
                        <p class="text-sm text-gray-500 dark:text-gray-400">
                            Belum Dialokasikan
                        </p>
                        <p
                            :class="[
                                'mt-2 text-xl font-bold',
                                safeBudgetSummary.unallocatedBalance >= 0
                                    ? 'text-emerald-600 dark:text-emerald-400'
                                    : 'text-red-600 dark:text-red-400',
                            ]"
                        >
                            {{ formatCurrency(safeBudgetSummary.unallocatedBalance) }}
                        </p>
                    </div>

                    <div class="rounded-xl bg-gray-50 p-4 dark:bg-gray-800">
                        <p class="text-sm text-gray-500 dark:text-gray-400">
                            Terpakai
                        </p>
                        <p class="mt-2 text-xl font-bold text-red-600 dark:text-red-400">
                            {{ formatCurrency(safeBudgetSummary.totalUsed) }}
                        </p>
                    </div>

                    <div class="rounded-xl bg-gray-50 p-4 dark:bg-gray-800">
                        <p class="text-sm text-gray-500 dark:text-gray-400">
                            Sisa Budget
                        </p>
                        <p
                            :class="[
                                'mt-2 text-xl font-bold',
                                safeBudgetSummary.totalRemaining >= 0
                                    ? 'text-emerald-600 dark:text-emerald-400'
                                    : 'text-red-600 dark:text-red-400',
                            ]"
                        >
                            {{ formatCurrency(safeBudgetSummary.totalRemaining) }}
                        </p>
                    </div>
                </div>

                <div>
                    <div
                        class="mb-1 flex justify-between text-xs text-gray-500 dark:text-gray-400"
                    >
                        <span>Total penggunaan budget</span>
                        <span>{{ safeBudgetSummary.totalPercentage }}%</span>
                    </div>

                    <div class="h-3 overflow-hidden rounded-full bg-gray-100 dark:bg-gray-800">
                        <div
                            class="h-full rounded-full transition-all"
                            :class="getBudgetProgressClass(budgetOverallStatus)"
                            :style="{
                                width: Math.min(safeBudgetSummary.totalPercentage, 100) + '%',
                            }"
                        ></div>
                    </div>
                </div>

                <div
                    v-if="safeBudgetSummary.warnings && safeBudgetSummary.warnings.length > 0"
                    class="rounded-xl border border-yellow-200 bg-yellow-50 p-4 dark:border-yellow-900 dark:bg-yellow-950/40"
                >
                    <div class="mb-3 flex items-center gap-2">
                        <AlertTriangle class="h-5 w-5 text-yellow-600 dark:text-yellow-300" />
                        <h3 class="font-semibold text-yellow-800 dark:text-yellow-200">
                            Peringatan Budget
                        </h3>
                    </div>

                    <div class="space-y-2">
                        <div
                            v-for="budget in safeBudgetSummary.warnings"
                            :key="budget.id"
                            class="flex flex-col gap-2 rounded-lg bg-white p-3 text-sm sm:flex-row sm:items-center sm:justify-between dark:bg-gray-900"
                        >
                            <div>
                                <p class="font-medium text-gray-900 dark:text-white">
                                    {{ budget.category_name }}
                                </p>
                                <p class="text-gray-500 dark:text-gray-400">
                                    Terpakai {{ formatCurrency(budget.used_amount) }}
                                    dari {{ formatCurrency(budget.amount) }}
                                </p>
                            </div>

                            <span
                                :class="[
                                    'w-fit rounded-full px-3 py-1 text-xs font-medium',
                                    getBudgetStatusClass(budget.status),
                                ]"
                            >
                                {{ getBudgetStatusLabel(budget.status) }}
                            </span>
                        </div>
                    </div>
                </div>

                <div class="grid grid-cols-1 gap-3 md:grid-cols-2 xl:grid-cols-3">
                    <div
                        v-for="budget in safeBudgetSummary.items"
                        :key="budget.id"
                        class="rounded-xl border border-gray-100 bg-gray-50 p-4 dark:border-gray-800 dark:bg-gray-800"
                    >
                        <div class="mb-3 flex items-center justify-between gap-2">
                            <div class="flex min-w-0 items-center gap-2">
                                <div
                                    class="h-8 w-8 shrink-0 rounded-full"
                                    :style="{ backgroundColor: budget.category_color }"
                                ></div>

                                <div class="min-w-0">
                                    <p class="truncate text-sm font-semibold text-gray-900 dark:text-white">
                                        {{ budget.category_name }}
                                    </p>
                                    <p class="text-xs text-gray-500 dark:text-gray-400">
                                        {{ budget.percentage }}% terpakai
                                    </p>
                                </div>
                            </div>

                            <span
                                :class="[
                                    'shrink-0 rounded-full px-2 py-1 text-xs font-medium',
                                    getBudgetStatusClass(budget.status),
                                ]"
                            >
                                {{ getBudgetStatusLabel(budget.status) }}
                            </span>
                        </div>

                        <div class="mb-1 flex justify-between text-xs text-gray-500 dark:text-gray-400">
                            <span>{{ formatCurrency(budget.used_amount) }}</span>
                            <span>{{ formatCurrency(budget.amount) }}</span>
                        </div>

                        <div class="h-2 overflow-hidden rounded-full bg-gray-200 dark:bg-gray-700">
                            <div
                                class="h-full rounded-full transition-all"
                                :class="getBudgetProgressClass(budget.status)"
                                :style="{ width: Math.min(budget.percentage, 100) + '%' }"
                            ></div>
                        </div>
                    </div>
                </div>
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
                    class="inline-flex items-center justify-center rounded-lg border border-gray-200 px-3 py-2 text-sm font-medium text-gray-700 transition hover:bg-gray-50 dark:border-gray-700 dark:text-gray-300 dark:hover:bg-gray-800"
                >
                    Lihat Transaksi
                </Link>
            </div>

            <EmptyState
                v-if="!hasChartData"
                :icon="BarChart3"
                title="Belum ada data grafik"
                description="Tambahkan transaksi pemasukan atau pengeluaran untuk melihat grafik."
                action-label="Tambah Transaksi"
                action-href="/transactions/create"
            />

            <div v-else class="h-80">
                <Bar :data="combinedChartData" :options="chartOptions" />
            </div>
        </div>

        <!-- Transactions + Accounts -->
        <div class="grid grid-cols-1 gap-6 xl:grid-cols-3">
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
                            Lihat Semua
                        </Link>
                    </div>

                    <EmptyState
                        v-if="recentTransactions.length === 0"
                        :icon="ReceiptText"
                        title="Belum ada transaksi"
                        description="Mulai catat pemasukan atau pengeluaran pertamamu."
                        action-label="Tambah Transaksi"
                        action-href="/transactions/create"
                    />

                    <div v-else class="space-y-3">
                        <div
                            v-for="transaction in recentTransactions"
                            :key="transaction.id"
                            class="flex items-center justify-between gap-3 rounded-xl border border-gray-100 bg-gray-50 p-3 transition hover:bg-gray-100 dark:border-gray-800 dark:bg-gray-800 dark:hover:bg-gray-700"
                        >
                            <div class="flex min-w-0 flex-1 items-center gap-3">
                                <div
                                    :style="{ backgroundColor: getTransactionColor(transaction) }"
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
                            Kelola
                        </Link>
                    </div>

                    <EmptyState
                        v-if="accountSummary.length === 0"
                        :icon="Landmark"
                        title="Belum ada akun"
                        description="Tambahkan akun keuangan seperti cash, bank, atau e-wallet."
                        action-label="Tambah Akun"
                        action-href="/financial-accounts/create"
                    />

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

                                <span
                                    class="shrink-0 rounded-full bg-gray-200 px-2 py-1 text-xs text-gray-700 dark:bg-gray-700 dark:text-gray-300"
                                >
                                    {{ getAccountTypeLabel(account.type) }}
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
                    Lihat Semua
                </Link>
            </div>

            <EmptyState
                v-if="recentNotes.length === 0"
                :icon="NotebookText"
                title="Belum ada catatan"
                description="Buat catatan untuk menyimpan pengingat atau informasi penting."
                action-label="Tambah Catatan"
                action-href="/notes/create"
            />

            <div v-else class="grid grid-cols-1 gap-4 md:grid-cols-2 xl:grid-cols-3">
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

                        <span v-else class="text-xs text-gray-400">
                            Tanpa label
                        </span>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>
