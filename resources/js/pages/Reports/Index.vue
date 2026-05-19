<script setup>
import { Head, Link, router } from '@inertiajs/vue3';
import { computed, ref } from 'vue';
import AppLayout from '@/layouts/AppLayout.vue';
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
</script>

<template>
    <Head title="Laporan Bulanan" />

    <div class="space-y-6 p-4 md:p-6">
        <div class="flex flex-col gap-4 md:flex-row md:items-center md:justify-between">
            <div>
                <h1 class="text-2xl font-bold">
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
                    class="inline-flex items-center justify-center gap-2 rounded-lg border px-4 py-2 text-sm font-medium hover:bg-muted"
                >
                    <Download class="h-4 w-4" />
                    CSV
                </a>

                <a
                    :href="`/reports/export/print?month=${selectedMonth}`"
                    target="_blank"
                    class="inline-flex items-center justify-center gap-2 rounded-lg bg-blue-600 px-4 py-2 text-sm font-medium text-white hover:bg-blue-700"
                >
                    <Printer class="h-4 w-4" />
                    Print
                </a>
            </div>
        </div>

        <div class="grid grid-cols-1 gap-4 md:grid-cols-4">
            <div class="rounded-xl border bg-card p-5 shadow-sm">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm text-muted-foreground">Pemasukan</p>
                        <p class="mt-2 text-xl font-bold text-emerald-500">
                            {{ formatCurrency(summary.totalIncome) }}
                        </p>
                    </div>

                    <TrendingUp class="h-8 w-8 text-emerald-500" />
                </div>
            </div>

            <div class="rounded-xl border bg-card p-5 shadow-sm">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm text-muted-foreground">Pengeluaran</p>
                        <p class="mt-2 text-xl font-bold text-red-500">
                            {{ formatCurrency(summary.totalExpense) }}
                        </p>
                    </div>

                    <TrendingDown class="h-8 w-8 text-red-500" />
                </div>
            </div>

            <div class="rounded-xl border bg-card p-5 shadow-sm">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm text-muted-foreground">Saldo Bersih</p>
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

                    <Wallet class="h-8 w-8 text-blue-500" />
                </div>
            </div>

            <div class="rounded-xl border bg-card p-5 shadow-sm">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm text-muted-foreground">Total Transaksi</p>
                        <p class="mt-2 text-xl font-bold">
                            {{ summary.transactionCount || 0 }}
                        </p>
                    </div>

                    <ReceiptText class="h-8 w-8 text-indigo-500" />
                </div>
            </div>
        </div>

        <div class="grid grid-cols-1 gap-6 xl:grid-cols-3">
            <div class="xl:col-span-2 rounded-xl border bg-card p-5 shadow-sm">
                <div class="mb-4 flex items-center gap-2">
                    <BarChart3 class="h-5 w-5 text-red-500" />
                    <h2 class="font-semibold">Pengeluaran per Kategori</h2>
                </div>

                <div
                    v-if="expenseByCategory.length === 0"
                    class="rounded-xl border border-dashed py-10 text-center text-sm text-muted-foreground"
                >
                    Belum ada pengeluaran pada bulan ini.
                </div>

                <div v-else class="space-y-4">
                    <div
                        v-for="category in expenseByCategory"
                        :key="category.id || category.name"
                        class="rounded-xl border p-4"
                    >
                        <div class="mb-2 flex items-center justify-between gap-3">
                            <div class="flex min-w-0 items-center gap-3">
                                <div
                                    class="h-9 w-9 shrink-0 rounded-full"
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

                            <p class="font-bold text-red-500">
                                {{ formatCurrency(category.total) }}
                            </p>
                        </div>

                        <div class="h-2 overflow-hidden rounded-full bg-muted">
                            <div
                                class="h-full rounded-full bg-red-500"
                                :style="{
                                    width:
                                        summary.totalExpense > 0
                                            ? Math.min((category.total / summary.totalExpense) * 100, 100) + '%'
                                            : '0%',
                                }"
                            ></div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="rounded-xl border bg-card p-5 shadow-sm">
                <div class="mb-4 flex items-center gap-2">
                    <PiggyBank class="h-5 w-5 text-indigo-500" />
                    <h2 class="font-semibold">Budget Bulanan</h2>
                </div>

                <div
                    v-if="!budget.totalBudget"
                    class="rounded-xl border border-dashed py-10 text-center text-sm text-muted-foreground"
                >
                    Belum ada budget bulan ini.
                    <div class="mt-4">
                        <Link
                            href="/budgets"
                            class="rounded-lg bg-blue-600 px-4 py-2 text-sm font-medium text-white hover:bg-blue-700"
                        >
                            Buat Budget
                        </Link>
                    </div>
                </div>

                <div v-else class="space-y-4">
                    <div class="grid grid-cols-1 gap-3">
                        <div class="rounded-lg bg-muted/40 p-3">
                            <p class="text-xs text-muted-foreground">Total Budget</p>
                            <p class="font-bold">
                                {{ formatCurrency(budget.totalBudget) }}
                            </p>
                        </div>

                        <div class="rounded-lg bg-muted/40 p-3">
                            <p class="text-xs text-muted-foreground">Terpakai</p>
                            <p class="font-bold text-red-500">
                                {{ formatCurrency(budget.totalUsed) }}
                            </p>
                        </div>

                        <div class="rounded-lg bg-muted/40 p-3">
                            <p class="text-xs text-muted-foreground">Sisa</p>
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
                                :class="
                                    budget.totalPercentage >= 100
                                        ? 'bg-red-500'
                                        : budget.totalPercentage >= 80
                                          ? 'bg-yellow-500'
                                          : 'bg-emerald-500'
                                "
                                :style="{ width: Math.min(budget.totalPercentage || 0, 100) + '%' }"
                            ></div>
                        </div>
                    </div>

                    <div
                        v-if="budget.warnings && budget.warnings.length > 0"
                        class="rounded-xl border border-yellow-200 bg-yellow-50 p-3 text-sm dark:border-yellow-900 dark:bg-yellow-950/40"
                    >
                        <div class="mb-2 flex items-center gap-2 font-semibold text-yellow-700 dark:text-yellow-300">
                            <AlertTriangle class="h-4 w-4" />
                            Peringatan
                        </div>

                        <div class="space-y-2">
                            <div
                                v-for="item in budget.warnings"
                                :key="item.id"
                                class="rounded-lg bg-background p-2"
                            >
                                <div class="flex items-center justify-between gap-2">
                                    <span>{{ item.category_name }}</span>
                                    <span
                                        :class="[
                                            'rounded-full px-2 py-1 text-xs',
                                            getStatusClass(item.status),
                                        ]"
                                    >
                                        {{ getStatusLabel(item.status) }}
                                    </span>
                                </div>
                            </div>
                        </div>
                    </div>

                    <Link
                        href="/budgets"
                        class="inline-flex w-full justify-center rounded-lg border px-4 py-2 text-sm font-medium hover:bg-muted"
                    >
                        Kelola Budget
                    </Link>
                </div>
            </div>
        </div>

        <div class="grid grid-cols-1 gap-6 xl:grid-cols-2">
            <div class="rounded-xl border bg-card p-5 shadow-sm">
                <div class="mb-4 flex items-center gap-2">
                    <BarChart3 class="h-5 w-5 text-emerald-500" />
                    <h2 class="font-semibold">Pemasukan per Kategori</h2>
                </div>

                <div
                    v-if="incomeByCategory.length === 0"
                    class="rounded-xl border border-dashed py-10 text-center text-sm text-muted-foreground"
                >
                    Belum ada pemasukan pada bulan ini.
                </div>

                <div v-else class="space-y-4">
                    <div
                        v-for="category in incomeByCategory"
                        :key="category.id || category.name"
                        class="rounded-xl border p-4"
                    >
                        <div class="mb-2 flex items-center justify-between gap-3">
                            <div class="flex min-w-0 items-center gap-3">
                                <div
                                    class="h-9 w-9 shrink-0 rounded-full"
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

                            <p class="font-bold text-emerald-500">
                                {{ formatCurrency(category.total) }}
                            </p>
                        </div>

                        <div class="h-2 overflow-hidden rounded-full bg-muted">
                            <div
                                class="h-full rounded-full bg-emerald-500"
                                :style="{
                                    width:
                                        summary.totalIncome > 0
                                            ? Math.min((category.total / summary.totalIncome) * 100, 100) + '%'
                                            : '0%',
                                }"
                            ></div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="rounded-xl border bg-card p-5 shadow-sm">
                <div class="mb-4 flex items-center gap-2">
                    <ReceiptText class="h-5 w-5 text-blue-500" />
                    <h2 class="font-semibold">Transaksi Terbesar</h2>
                </div>

                <div
                    v-if="largestTransactions.length === 0"
                    class="rounded-xl border border-dashed py-10 text-center text-sm text-muted-foreground"
                >
                    Belum ada transaksi pada bulan ini.
                </div>

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
    </div>
</template>
