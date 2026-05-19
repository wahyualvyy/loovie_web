<script setup lang="ts">
import { Head, Link, router } from '@inertiajs/vue3';
import {
    CreditCard,
    TrendingUp,
    TrendingDown,
    Wallet,
    Calendar,
} from 'lucide-vue-next';
import { ref, onMounted } from 'vue';
import {
    Chart as ChartJS,
    CategoryScale,
    LinearScale,
    PointElement,
    LineElement,
    BarElement,
    Title,
    Tooltip,
    Legend,
} from 'chart.js';
import { Line, Bar } from 'vue-chartjs';

ChartJS.register(
    CategoryScale,
    LinearScale,
    PointElement,
    LineElement,
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

const incomeChartData = ref<any>(null);
const expenseChartData = ref<any>(null);
const combinedChartData = ref<any>(null);

const chartOptions = {
    responsive: true,
    maintainAspectRatio: false,
    plugins: {
        legend: {
            labels: {
                color: '#6b7280',
            },
        },
    },
    scales: {
        y: {
            ticks: {
                color: '#6b7280',
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
                color: '#e5e7eb',
            },
        },
    },
};

onMounted(() => {
    updateCharts();
});

const updateCharts = () => {
    // Income Chart
    incomeChartData.value = {
        labels: props.chartData.months,
        datasets: [
            {
                label: 'Income',
                data: props.chartData.income,
                borderColor: '#10b981',
                backgroundColor: 'rgba(16, 185, 129, 0.1)',
                borderWidth: 2,
                fill: true,
                tension: 0.4,
                pointRadius: 4,
                pointBackgroundColor: '#10b981',
            },
        ],
    };

    // Expense Chart
    expenseChartData.value = {
        labels: props.chartData.months,
        datasets: [
            {
                label: 'Expense',
                data: props.chartData.expense,
                borderColor: '#ef4444',
                backgroundColor: 'rgba(239, 68, 68, 0.1)',
                borderWidth: 2,
                fill: true,
                tension: 0.4,
                pointRadius: 4,
                pointBackgroundColor: '#ef4444',
            },
        ],
    };

    // Combined Chart
    combinedChartData.value = {
        labels: props.chartData.months,
        datasets: [
            {
                label: 'Income',
                data: props.chartData.income,
                backgroundColor: 'rgba(16, 185, 129, 0.8)',
                borderColor: '#10b981',
                borderWidth: 1,
            },
            {
                label: 'Expense',
                data: props.chartData.expense,
                backgroundColor: 'rgba(239, 68, 68, 0.8)',
                borderColor: '#ef4444',
                borderWidth: 1,
            },
        ],
    };
};

const handleYearChange = (year: number) => {
    selectedYear.value = year;
    router.get('/dashboard', { year }, { preserveScroll: true });
};

const formatCurrency = (value: number) => {
    return new Intl.NumberFormat('id-ID', {
        style: 'currency',
        currency: 'IDR',
        minimumFractionDigits: 0,
        maximumFractionDigits: 0,
    }).format(value);
};

const formatDate = (date: string) => {
    return new Date(date).toLocaleDateString('id-ID', {
        day: 'numeric',
        month: 'short',
        year: 'numeric',
    });
};

const truncateContent = (content: string, limit: number = 80) => {
    if (content.length <= limit) return content;
    return content.substring(0, limit) + '...';
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
                <h1
                    class="text-3xl font-bold text-gray-900 sm:text-4xl dark:text-white"
                >
                    Dashboard
                </h1>
                <p class="mt-1 text-gray-600 dark:text-gray-400">
                    Welcome back! Here's your financial overview
                </p>
            </div>
        </div>

        <!-- Summary Cards -->
        <div class="grid grid-cols-1 gap-4 md:grid-cols-2 lg:grid-cols-4">
            <!-- Total Balance -->
            <div
                class="rounded-lg border border-blue-400 bg-gradient-to-br from-blue-500 to-blue-600 p-6 text-white shadow-sm dark:border-blue-700 dark:from-blue-600 dark:to-blue-700"
            >
                <div class="mb-2 flex items-center justify-between">
                    <p class="text-sm font-medium opacity-90">Total Balance</p>
                    <Wallet class="h-5 w-5 opacity-75" />
                </div>
                <p class="text-2xl font-bold sm:text-3xl">
                    {{ formatCurrency(totalBalance) }}
                </p>
                <p class="mt-2 text-xs opacity-75">All accounts combined</p>
            </div>

            <!-- Monthly Income -->
            <div
                class="rounded-lg border border-green-400 bg-gradient-to-br from-green-500 to-green-600 p-6 text-white shadow-sm dark:border-green-700 dark:from-green-600 dark:to-green-700"
            >
                <div class="mb-2 flex items-center justify-between">
                    <p class="text-sm font-medium opacity-90">
                        This Month Income
                    </p>
                    <TrendingUp class="h-5 w-5 opacity-75" />
                </div>
                <p class="text-2xl font-bold sm:text-3xl">
                    {{ formatCurrency(monthlyIncome) }}
                </p>
                <p class="mt-2 text-xs opacity-75">Income transactions</p>
            </div>

            <!-- Monthly Expense -->
            <div
                class="rounded-lg border border-red-400 bg-gradient-to-br from-red-500 to-red-600 p-6 text-white shadow-sm dark:border-red-700 dark:from-red-600 dark:to-red-700"
            >
                <div class="mb-2 flex items-center justify-between">
                    <p class="text-sm font-medium opacity-90">
                        This Month Expense
                    </p>
                    <TrendingDown class="h-5 w-5 opacity-75" />
                </div>
                <p class="text-2xl font-bold sm:text-3xl">
                    {{ formatCurrency(monthlyExpense) }}
                </p>
                <p class="mt-2 text-xs opacity-75">Expense transactions</p>
            </div>

            <!-- Net Balance -->
            <div
                :class="[
                    'rounded-lg border p-6 text-white shadow-sm',
                    netBalance >= 0
                        ? 'border-purple-400 bg-gradient-to-br from-purple-500 to-purple-600 dark:border-purple-700 dark:from-purple-600 dark:to-purple-700'
                        : 'border-orange-400 bg-gradient-to-br from-orange-500 to-orange-600 dark:border-orange-700 dark:from-orange-600 dark:to-orange-700',
                ]"
            >
                <div class="mb-2 flex items-center justify-between">
                    <p class="text-sm font-medium opacity-90">Net Balance</p>
                    <CreditCard class="h-5 w-5 opacity-75" />
                </div>
                <p class="text-2xl font-bold sm:text-3xl">
                    {{ formatCurrency(netBalance) }}
                </p>
                <p class="mt-2 text-xs opacity-75">
                    {{ netBalance >= 0 ? 'Surplus' : 'Deficit' }}
                </p>
            </div>
        </div>

        <!-- Charts Section -->
        <div class="grid grid-cols-1 gap-6 lg:grid-cols-2">
            <!-- Monthly Income Chart -->
            <div
                class="rounded-lg border border-gray-200 bg-white p-6 shadow-sm dark:border-gray-800 dark:bg-gray-900"
            >
                <div class="mb-4 flex items-center justify-between">
                    <h2
                        class="text-lg font-semibold text-gray-900 dark:text-white"
                    >
                        Monthly Income
                    </h2>
                    <div class="flex items-center gap-2">
                        <Calendar class="h-4 w-4 text-gray-500" />
                        <select
                            :value="selectedYear"
                            @change="
                                handleYearChange(Number($event.target.value))
                            "
                            class="rounded border border-gray-200 bg-gray-50 px-3 py-1 text-sm text-gray-900 focus:ring-2 focus:ring-indigo-500 dark:border-gray-700 dark:bg-gray-800 dark:text-white"
                        >
                            <option
                                v-for="year in availableYears"
                                :key="year"
                                :value="year"
                            >
                                {{ year }}
                            </option>
                        </select>
                    </div>
                </div>
                <div class="h-80">
                    <Line
                        v-if="incomeChartData"
                        :data="incomeChartData"
                        :options="chartOptions"
                    />
                </div>
            </div>

            <!-- Monthly Expense Chart -->
            <div
                class="rounded-lg border border-gray-200 bg-white p-6 shadow-sm dark:border-gray-800 dark:bg-gray-900"
            >
                <div class="mb-4 flex items-center justify-between">
                    <h2
                        class="text-lg font-semibold text-gray-900 dark:text-white"
                    >
                        Monthly Expense
                    </h2>
                    <div class="flex items-center gap-2">
                        <Calendar class="h-4 w-4 text-gray-500" />
                        <select
                            :value="selectedYear"
                            @change="
                                handleYearChange(Number($event.target.value))
                            "
                            class="rounded border border-gray-200 bg-gray-50 px-3 py-1 text-sm text-gray-900 focus:ring-2 focus:ring-indigo-500 dark:border-gray-700 dark:bg-gray-800 dark:text-white"
                        >
                            <option
                                v-for="year in availableYears"
                                :key="year"
                                :value="year"
                            >
                                {{ year }}
                            </option>
                        </select>
                    </div>
                </div>
                <div class="h-80">
                    <Line
                        v-if="expenseChartData"
                        :data="expenseChartData"
                        :options="chartOptions"
                    />
                </div>
            </div>
        </div>

        <!-- Combined Chart -->
        <div
            class="rounded-lg border border-gray-200 bg-white p-6 shadow-sm dark:border-gray-800 dark:bg-gray-900"
        >
            <div class="mb-4 flex items-center justify-between">
                <h2 class="text-lg font-semibold text-gray-900 dark:text-white">
                    Income vs Expense
                </h2>
                <div class="flex items-center gap-2">
                    <Calendar class="h-4 w-4 text-gray-500" />
                    <select
                        :value="selectedYear"
                        @change="handleYearChange(Number($event.target.value))"
                        class="rounded border border-gray-200 bg-gray-50 px-3 py-1 text-sm text-gray-900 focus:ring-2 focus:ring-indigo-500 dark:border-gray-700 dark:bg-gray-800 dark:text-white"
                    >
                        <option
                            v-for="year in availableYears"
                            :key="year"
                            :value="year"
                        >
                            {{ year }}
                        </option>
                    </select>
                </div>
            </div>
            <div class="h-80">
                <Bar
                    v-if="combinedChartData"
                    :data="combinedChartData"
                    :options="chartOptions"
                />
            </div>
        </div>

        <!-- Recent Transactions & Notes & Accounts -->
        <div class="grid grid-cols-1 gap-6 lg:grid-cols-3">
            <!-- Recent Transactions -->
            <div class="lg:col-span-2">
                <div
                    class="rounded-lg border border-gray-200 bg-white p-6 shadow-sm dark:border-gray-800 dark:bg-gray-900"
                >
                    <div class="mb-4 flex items-center justify-between">
                        <h2
                            class="text-lg font-semibold text-gray-900 dark:text-white"
                        >
                            Recent Transactions
                        </h2>
                        <Link
                            href="/transactions"
                            class="text-sm font-medium text-indigo-600 hover:text-indigo-700"
                        >
                            View All
                        </Link>
                    </div>
                    <div
                        v-if="recentTransactions.length === 0"
                        class="py-8 text-center text-gray-500"
                    >
                        No transactions yet
                    </div>
                    <div v-else class="space-y-3">
                        <div
                            v-for="transaction in recentTransactions"
                            :key="transaction.id"
                            class="flex items-center justify-between rounded-lg bg-gray-50 p-3 transition-colors hover:bg-gray-100 dark:bg-gray-800 dark:hover:bg-gray-700"
                        >
                            <div class="flex min-w-0 flex-1 items-center gap-3">
                                <div
                                    :style="{
                                        backgroundColor:
                                            transaction.category_color,
                                    }"
                                    class="flex h-10 w-10 flex-shrink-0 items-center justify-center rounded-full text-sm font-semibold text-white"
                                >
                                    {{ transaction.category_name.charAt(0) }}
                                </div>
                                <div class="min-w-0">
                                    <p
                                        class="truncate text-sm font-medium text-gray-900 dark:text-white"
                                    >
                                        {{ transaction.category_name }}
                                    </p>
                                    <p
                                        class="text-xs text-gray-500 dark:text-gray-400"
                                    >
                                        {{ transaction.account_name }} •
                                        {{
                                            formatDate(
                                                transaction.transaction_date,
                                            )
                                        }}
                                    </p>
                                </div>
                            </div>
                            <p
                                :class="[
                                    'ml-2 text-sm font-semibold whitespace-nowrap',
                                    transaction.type === 'income'
                                        ? 'text-green-600 dark:text-green-400'
                                        : 'text-red-600 dark:text-red-400',
                                ]"
                            >
                                {{ transaction.type === 'income' ? '+' : '-'
                                }}{{ formatCurrency(transaction.amount) }}
                            </p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Account Summary -->
            <div
                class="rounded-lg border border-gray-200 bg-white p-6 shadow-sm dark:border-gray-800 dark:bg-gray-900"
            >
                <div class="mb-4 flex items-center justify-between">
                    <h2
                        class="text-lg font-semibold text-gray-900 dark:text-white"
                    >
                        Accounts
                    </h2>
                    <Link
                        href="/financial-accounts"
                        class="text-sm font-medium text-indigo-600 hover:text-indigo-700"
                    >
                        Manage
                    </Link>
                </div>
                <div
                    v-if="accountSummary.length === 0"
                    class="py-8 text-center text-gray-500"
                >
                    No accounts yet
                </div>
                <div v-else class="space-y-3">
                    <div
                        v-for="account in accountSummary"
                        :key="account.id"
                        class="rounded-lg bg-gray-50 p-3 dark:bg-gray-800"
                    >
                        <div class="mb-1 flex items-center justify-between">
                            <p
                                class="text-sm font-medium text-gray-900 dark:text-white"
                            >
                                {{ account.name }}
                            </p>
                            <span
                                class="rounded bg-gray-200 px-2 py-1 text-xs text-gray-700 dark:bg-gray-700 dark:text-gray-300"
                            >
                                {{ account.type }}
                            </span>
                        </div>
                        <p
                            class="text-lg font-bold text-gray-900 dark:text-white"
                        >
                            {{ formatCurrency(account.current_balance) }}
                        </p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Recent Notes -->
        <div
            class="rounded-lg border border-gray-200 bg-white p-6 shadow-sm dark:border-gray-800 dark:bg-gray-900"
        >
            <div class="mb-4 flex items-center justify-between">
                <h2 class="text-lg font-semibold text-gray-900 dark:text-white">
                    Recent Notes
                </h2>
                        <Link
                            href="/notes"
                            class="text-sm font-medium text-indigo-600 hover:text-indigo-700"
                        >
                    View All
                </Link>
            </div>
            <div
                v-if="recentNotes.length === 0"
                class="py-8 text-center text-gray-500"
            >
                No notes yet
            </div>
            <div
                v-else
                class="grid grid-cols-1 gap-4 md:grid-cols-2 lg:grid-cols-3"
            >
                <div
                    v-for="note in recentNotes"
                    :key="note.id"
                    class="rounded-lg border border-yellow-200 bg-gradient-to-br from-yellow-50 to-yellow-100 p-4 transition-shadow hover:shadow-md dark:border-yellow-800 dark:from-yellow-900/20 dark:to-yellow-800/20"
                >
                    <div class="mb-2 flex items-start justify-between">
                        <h3
                            class="line-clamp-1 text-sm font-semibold text-gray-900 dark:text-white"
                        >
                            {{ note.title }}
                        </h3>
                    </div>
                    <p
                        class="mb-3 line-clamp-2 text-xs text-gray-700 dark:text-gray-300"
                    >
                        {{ truncateContent(note.content, 80) }}
                    </p>
                    <div class="flex items-center justify-between">
                        <span
                            v-if="note.label"
                            class="rounded bg-gray-200 px-2 py-1 text-xs text-gray-700 dark:bg-gray-700 dark:text-gray-300"
                        >
                            {{ note.label }}
                        </span>
                        <span class="text-xs text-gray-600 dark:text-gray-400">
                            {{ formatDate(note.note_date) }}
                        </span>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>
