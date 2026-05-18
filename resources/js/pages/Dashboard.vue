<script setup lang="ts">
import { Head, Link, router } from '@inertiajs/vue3';
import { CreditCard, TrendingUp, TrendingDown, Wallet, Calendar } from 'lucide-vue-next';
import { ref, onMounted } from 'vue';
import { Chart as ChartJS, CategoryScale, LinearScale, PointElement, LineElement, BarElement, Title, Tooltip, Legend } from 'chart.js';
import { Line, Bar } from 'vue-chartjs';

ChartJS.register(CategoryScale, LinearScale, PointElement, LineElement, BarElement, Title, Tooltip, Legend);

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
    router.get(route('dashboard'), { year }, { preserveScroll: true });
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

    <div class="p-4 sm:p-6 lg:p-8 space-y-6">
        <!-- Header -->
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
            <div>
                <h1 class="text-3xl sm:text-4xl font-bold text-gray-900 dark:text-white">Dashboard</h1>
                <p class="text-gray-600 dark:text-gray-400 mt-1">Welcome back! Here's your financial overview</p>
            </div>
        </div>

        <!-- Summary Cards -->
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
            <!-- Total Balance -->
            <div class="bg-gradient-to-br from-blue-500 to-blue-600 dark:from-blue-600 dark:to-blue-700 rounded-lg shadow-sm border border-blue-400 dark:border-blue-700 p-6 text-white">
                <div class="flex items-center justify-between mb-2">
                    <p class="text-sm font-medium opacity-90">Total Balance</p>
                    <Wallet class="w-5 h-5 opacity-75" />
                </div>
                <p class="text-2xl sm:text-3xl font-bold">{{ formatCurrency(totalBalance) }}</p>
                <p class="text-xs opacity-75 mt-2">All accounts combined</p>
            </div>

            <!-- Monthly Income -->
            <div class="bg-gradient-to-br from-green-500 to-green-600 dark:from-green-600 dark:to-green-700 rounded-lg shadow-sm border border-green-400 dark:border-green-700 p-6 text-white">
                <div class="flex items-center justify-between mb-2">
                    <p class="text-sm font-medium opacity-90">This Month Income</p>
                    <TrendingUp class="w-5 h-5 opacity-75" />
                </div>
                <p class="text-2xl sm:text-3xl font-bold">{{ formatCurrency(monthlyIncome) }}</p>
                <p class="text-xs opacity-75 mt-2">Income transactions</p>
            </div>

            <!-- Monthly Expense -->
            <div class="bg-gradient-to-br from-red-500 to-red-600 dark:from-red-600 dark:to-red-700 rounded-lg shadow-sm border border-red-400 dark:border-red-700 p-6 text-white">
                <div class="flex items-center justify-between mb-2">
                    <p class="text-sm font-medium opacity-90">This Month Expense</p>
                    <TrendingDown class="w-5 h-5 opacity-75" />
                </div>
                <p class="text-2xl sm:text-3xl font-bold">{{ formatCurrency(monthlyExpense) }}</p>
                <p class="text-xs opacity-75 mt-2">Expense transactions</p>
            </div>

            <!-- Net Balance -->
            <div :class="['rounded-lg shadow-sm border p-6 text-white', netBalance >= 0 ? 'bg-gradient-to-br from-purple-500 to-purple-600 dark:from-purple-600 dark:to-purple-700 border-purple-400 dark:border-purple-700' : 'bg-gradient-to-br from-orange-500 to-orange-600 dark:from-orange-600 dark:to-orange-700 border-orange-400 dark:border-orange-700']">
                <div class="flex items-center justify-between mb-2">
                    <p class="text-sm font-medium opacity-90">Net Balance</p>
                    <CreditCard class="w-5 h-5 opacity-75" />
                </div>
                <p class="text-2xl sm:text-3xl font-bold">{{ formatCurrency(netBalance) }}</p>
                <p class="text-xs opacity-75 mt-2">{{ netBalance >= 0 ? 'Surplus' : 'Deficit' }}</p>
            </div>
        </div>

        <!-- Charts Section -->
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
            <!-- Monthly Income Chart -->
            <div class="bg-white dark:bg-gray-900 rounded-lg shadow-sm border border-gray-200 dark:border-gray-800 p-6">
                <div class="flex items-center justify-between mb-4">
                    <h2 class="text-lg font-semibold text-gray-900 dark:text-white">Monthly Income</h2>
                    <div class="flex items-center gap-2">
                        <Calendar class="w-4 h-4 text-gray-500" />
                        <select
                            :value="selectedYear"
                            @change="handleYearChange(Number($event.target.value))"
                            class="px-3 py-1 rounded text-sm bg-gray-50 dark:bg-gray-800 border border-gray-200 dark:border-gray-700 text-gray-900 dark:text-white focus:ring-2 focus:ring-indigo-500"
                        >
                            <option v-for="year in availableYears" :key="year" :value="year">
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
            <div class="bg-white dark:bg-gray-900 rounded-lg shadow-sm border border-gray-200 dark:border-gray-800 p-6">
                <div class="flex items-center justify-between mb-4">
                    <h2 class="text-lg font-semibold text-gray-900 dark:text-white">Monthly Expense</h2>
                    <div class="flex items-center gap-2">
                        <Calendar class="w-4 h-4 text-gray-500" />
                        <select
                            :value="selectedYear"
                            @change="handleYearChange(Number($event.target.value))"
                            class="px-3 py-1 rounded text-sm bg-gray-50 dark:bg-gray-800 border border-gray-200 dark:border-gray-700 text-gray-900 dark:text-white focus:ring-2 focus:ring-indigo-500"
                        >
                            <option v-for="year in availableYears" :key="year" :value="year">
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
        <div class="bg-white dark:bg-gray-900 rounded-lg shadow-sm border border-gray-200 dark:border-gray-800 p-6">
            <div class="flex items-center justify-between mb-4">
                <h2 class="text-lg font-semibold text-gray-900 dark:text-white">Income vs Expense</h2>
                <div class="flex items-center gap-2">
                    <Calendar class="w-4 h-4 text-gray-500" />
                    <select
                        :value="selectedYear"
                        @change="handleYearChange(Number($event.target.value))"
                        class="px-3 py-1 rounded text-sm bg-gray-50 dark:bg-gray-800 border border-gray-200 dark:border-gray-700 text-gray-900 dark:text-white focus:ring-2 focus:ring-indigo-500"
                    >
                        <option v-for="year in availableYears" :key="year" :value="year">
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
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
            <!-- Recent Transactions -->
            <div class="lg:col-span-2">
                <div class="bg-white dark:bg-gray-900 rounded-lg shadow-sm border border-gray-200 dark:border-gray-800 p-6">
                    <div class="flex items-center justify-between mb-4">
                        <h2 class="text-lg font-semibold text-gray-900 dark:text-white">Recent Transactions</h2>
                        <Link :href="route('transactions.index')" class="text-indigo-600 hover:text-indigo-700 text-sm font-medium">
                            View All
                        </Link>
                    </div>
                    <div v-if="recentTransactions.length === 0" class="text-center py-8 text-gray-500">
                        No transactions yet
                    </div>
                    <div v-else class="space-y-3">
                        <div
                            v-for="transaction in recentTransactions"
                            :key="transaction.id"
                            class="flex items-center justify-between p-3 bg-gray-50 dark:bg-gray-800 rounded-lg hover:bg-gray-100 dark:hover:bg-gray-700 transition-colors"
                        >
                            <div class="flex items-center gap-3 flex-1 min-w-0">
                                <div
                                    :style="{ backgroundColor: transaction.category_color }"
                                    class="w-10 h-10 rounded-full flex items-center justify-center text-white font-semibold text-sm flex-shrink-0"
                                >
                                    {{ transaction.category_name.charAt(0) }}
                                </div>
                                <div class="min-w-0">
                                    <p class="text-sm font-medium text-gray-900 dark:text-white truncate">
                                        {{ transaction.category_name }}
                                    </p>
                                    <p class="text-xs text-gray-500 dark:text-gray-400">
                                        {{ transaction.account_name }} • {{ formatDate(transaction.transaction_date) }}
                                    </p>
                                </div>
                            </div>
                            <p :class="['text-sm font-semibold whitespace-nowrap ml-2', transaction.type === 'income' ? 'text-green-600 dark:text-green-400' : 'text-red-600 dark:text-red-400']">
                                {{ transaction.type === 'income' ? '+' : '-' }}{{ formatCurrency(transaction.amount) }}
                            </p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Account Summary -->
            <div class="bg-white dark:bg-gray-900 rounded-lg shadow-sm border border-gray-200 dark:border-gray-800 p-6">
                <div class="flex items-center justify-between mb-4">
                    <h2 class="text-lg font-semibold text-gray-900 dark:text-white">Accounts</h2>
                    <Link :href="route('financial-accounts.index')" class="text-indigo-600 hover:text-indigo-700 text-sm font-medium">
                        Manage
                    </Link>
                </div>
                <div v-if="accountSummary.length === 0" class="text-center py-8 text-gray-500">
                    No accounts yet
                </div>
                <div v-else class="space-y-3">
                    <div
                        v-for="account in accountSummary"
                        :key="account.id"
                        class="p-3 bg-gray-50 dark:bg-gray-800 rounded-lg"
                    >
                        <div class="flex items-center justify-between mb-1">
                            <p class="text-sm font-medium text-gray-900 dark:text-white">{{ account.name }}</p>
                            <span class="text-xs px-2 py-1 bg-gray-200 dark:bg-gray-700 text-gray-700 dark:text-gray-300 rounded">
                                {{ account.type }}
                            </span>
                        </div>
                        <p class="text-lg font-bold text-gray-900 dark:text-white">
                            {{ formatCurrency(account.current_balance) }}
                        </p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Recent Notes -->
        <div class="bg-white dark:bg-gray-900 rounded-lg shadow-sm border border-gray-200 dark:border-gray-800 p-6">
            <div class="flex items-center justify-between mb-4">
                <h2 class="text-lg font-semibold text-gray-900 dark:text-white">Recent Notes</h2>
                <Link :href="route('notes.index')" class="text-indigo-600 hover:text-indigo-700 text-sm font-medium">
                    View All
                </Link>
            </div>
            <div v-if="recentNotes.length === 0" class="text-center py-8 text-gray-500">
                No notes yet
            </div>
            <div v-else class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                <div
                    v-for="note in recentNotes"
                    :key="note.id"
                    class="p-4 bg-gradient-to-br from-yellow-50 to-yellow-100 dark:from-yellow-900/20 dark:to-yellow-800/20 border border-yellow-200 dark:border-yellow-800 rounded-lg hover:shadow-md transition-shadow"
                >
                    <div class="flex items-start justify-between mb-2">
                        <h3 class="text-sm font-semibold text-gray-900 dark:text-white line-clamp-1">
                            {{ note.title }}
                        </h3>
                    </div>
                    <p class="text-xs text-gray-700 dark:text-gray-300 line-clamp-2 mb-3">
                        {{ truncateContent(note.content, 80) }}
                    </p>
                    <div class="flex items-center justify-between">
                        <span v-if="note.label" class="text-xs px-2 py-1 bg-gray-200 dark:bg-gray-700 text-gray-700 dark:text-gray-300 rounded">
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
