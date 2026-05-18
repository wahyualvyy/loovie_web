<script setup lang="ts">
import { Head } from '@inertiajs/vue3';
import { CreditCard, TrendingUp, TrendingDown, Wallet } from 'lucide-vue-next';
import { ref, computed } from 'vue';

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

// Demo data - akan diganti dengan API call
const accounts = ref([
    {
        id: 1,
        name: 'Bank BCA',
        type: 'Bank',
        balance: 5000000,
        color: 'bg-blue-500',
    },
    {
        id: 2,
        name: 'Cash',
        type: 'Cash',
        balance: 1200000,
        color: 'bg-green-500',
    },
    {
        id: 3,
        name: 'Dana',
        type: 'E-Wallet',
        balance: 500000,
        color: 'bg-purple-500',
    },
]);

const totalBalance = computed(() => {
    return accounts.value.reduce((sum, acc) => sum + acc.balance, 0);
});

const monthlyIncome = ref(15000000);
const monthlyExpense = ref(8500000);
const netBalance = computed(() => monthlyIncome.value - monthlyExpense.value);
</script>

<template>
    <Head title="Dashboard - Loovie Apps" />

    <div class="space-y-6 p-4 sm:p-6 lg:p-8">
        <!-- Page Header -->
        <div>
            <h1
                class="text-3xl font-bold text-gray-900 sm:text-4xl dark:text-white"
            >
                Dashboard
            </h1>
            <p class="mt-1 text-gray-600 dark:text-gray-400">
                Welcome back! Here's your financial overview.
            </p>
        </div>

        <!-- Summary Cards - Responsive Grid -->
        <div class="grid grid-cols-1 gap-4 md:grid-cols-2 lg:grid-cols-4">
            <!-- Total Balance Card -->
            <div
                class="rounded-lg border border-gray-200 bg-white p-6 shadow-sm transition-shadow hover:shadow-md dark:border-gray-800 dark:bg-gray-900"
            >
                <div class="flex items-center justify-between">
                    <div>
                        <p
                            class="text-sm font-medium text-gray-600 dark:text-gray-400"
                        >
                            Total Balance
                        </p>
                        <p
                            class="mt-2 text-2xl font-bold text-gray-900 sm:text-3xl dark:text-white"
                        >
                            Rp {{ (totalBalance / 1000000).toFixed(2) }}M
                        </p>
                    </div>
                    <div class="rounded-lg bg-blue-100 p-3 dark:bg-blue-900/30">
                        <Wallet
                            class="h-6 w-6 text-blue-600 dark:text-blue-400"
                        />
                    </div>
                </div>
            </div>

            <!-- Monthly Income Card -->
            <div
                class="rounded-lg border border-gray-200 bg-white p-6 shadow-sm transition-shadow hover:shadow-md dark:border-gray-800 dark:bg-gray-900"
            >
                <div class="flex items-center justify-between">
                    <div>
                        <p
                            class="text-sm font-medium text-gray-600 dark:text-gray-400"
                        >
                            Income This Month
                        </p>
                        <p
                            class="mt-2 text-2xl font-bold text-green-600 sm:text-3xl dark:text-green-400"
                        >
                            +Rp {{ (monthlyIncome / 1000000).toFixed(2) }}M
                        </p>
                    </div>
                    <div
                        class="rounded-lg bg-green-100 p-3 dark:bg-green-900/30"
                    >
                        <TrendingUp
                            class="h-6 w-6 text-green-600 dark:text-green-400"
                        />
                    </div>
                </div>
            </div>

            <!-- Monthly Expense Card -->
            <div
                class="rounded-lg border border-gray-200 bg-white p-6 shadow-sm transition-shadow hover:shadow-md dark:border-gray-800 dark:bg-gray-900"
            >
                <div class="flex items-center justify-between">
                    <div>
                        <p
                            class="text-sm font-medium text-gray-600 dark:text-gray-400"
                        >
                            Expense This Month
                        </p>
                        <p
                            class="mt-2 text-2xl font-bold text-red-600 sm:text-3xl dark:text-red-400"
                        >
                            -Rp {{ (monthlyExpense / 1000000).toFixed(2) }}M
                        </p>
                    </div>
                    <div class="rounded-lg bg-red-100 p-3 dark:bg-red-900/30">
                        <TrendingDown
                            class="h-6 w-6 text-red-600 dark:text-red-400"
                        />
                    </div>
                </div>
            </div>

            <!-- Net Balance Card -->
            <div
                class="rounded-lg border border-gray-200 bg-white p-6 shadow-sm transition-shadow hover:shadow-md dark:border-gray-800 dark:bg-gray-900"
            >
                <div class="flex items-center justify-between">
                    <div>
                        <p
                            class="text-sm font-medium text-gray-600 dark:text-gray-400"
                        >
                            Net Balance
                        </p>
                        <p
                            class="mt-2 text-2xl font-bold text-indigo-600 sm:text-3xl dark:text-indigo-400"
                        >
                            Rp {{ (netBalance / 1000000).toFixed(2) }}M
                        </p>
                    </div>
                    <div
                        class="rounded-lg bg-indigo-100 p-3 dark:bg-indigo-900/30"
                    >
                        <CreditCard
                            class="h-6 w-6 text-indigo-600 dark:text-indigo-400"
                        />
                    </div>
                </div>
            </div>
        </div>

        <!-- Accounts Summary -->
        <div
            class="rounded-lg border border-gray-200 bg-white p-6 shadow-sm dark:border-gray-800 dark:bg-gray-900"
        >
            <h2
                class="mb-4 text-lg font-semibold text-gray-900 dark:text-white"
            >
                Account Summary
            </h2>
            <div class="grid grid-cols-1 gap-4 md:grid-cols-2 lg:grid-cols-3">
                <div
                    v-for="account in accounts"
                    :key="account.id"
                    class="rounded-lg bg-gray-50 p-4 dark:bg-gray-800"
                >
                    <div class="mb-3 flex items-center space-x-3">
                        <div
                            :class="[
                                account.color,
                                'flex h-10 w-10 items-center justify-center rounded-lg',
                            ]"
                        >
                            <CreditCard class="h-5 w-5 text-white" />
                        </div>
                        <div>
                            <p
                                class="text-sm font-medium text-gray-900 dark:text-white"
                            >
                                {{ account.name }}
                            </p>
                            <p class="text-xs text-gray-500 dark:text-gray-400">
                                {{ account.type }}
                            </p>
                        </div>
                    </div>
                    <p class="text-lg font-bold text-gray-900 dark:text-white">
                        Rp {{ (account.balance / 1000000).toFixed(2) }}M
                    </p>
                </div>
            </div>
        </div>

        <!-- Coming Soon Sections -->
        <div class="grid grid-cols-1 gap-6 lg:grid-cols-3">
            <!-- Chart Section -->
            <div
                class="rounded-lg border border-gray-200 bg-white p-6 shadow-sm lg:col-span-2 dark:border-gray-800 dark:bg-gray-900"
            >
                <h2
                    class="mb-4 text-lg font-semibold text-gray-900 dark:text-white"
                >
                    Monthly Trends
                </h2>
                <div
                    class="flex h-64 items-center justify-center rounded-lg bg-gradient-to-br from-gray-100 to-gray-50 dark:from-gray-800 dark:to-gray-700"
                >
                    <p class="text-gray-500 dark:text-gray-400">
                        Chart will be added in next phase
                    </p>
                </div>
            </div>

            <!-- Recent Transactions -->
            <div
                class="rounded-lg border border-gray-200 bg-white p-6 shadow-sm dark:border-gray-800 dark:bg-gray-900"
            >
                <h2
                    class="mb-4 text-lg font-semibold text-gray-900 dark:text-white"
                >
                    Recent Transactions
                </h2>
                <div class="space-y-3">
                    <div
                        class="flex items-center justify-between rounded-lg bg-gray-50 p-3 dark:bg-gray-800"
                    >
                        <div class="text-sm">
                            <p
                                class="font-medium text-gray-900 dark:text-white"
                            >
                                Salary
                            </p>
                            <p class="text-xs text-gray-500 dark:text-gray-400">
                                Today
                            </p>
                        </div>
                        <p
                            class="font-semibold text-green-600 dark:text-green-400"
                        >
                            +Rp 5M
                        </p>
                    </div>
                    <div
                        class="flex items-center justify-between rounded-lg bg-gray-50 p-3 dark:bg-gray-800"
                    >
                        <div class="text-sm">
                            <p
                                class="font-medium text-gray-900 dark:text-white"
                            >
                                Groceries
                            </p>
                            <p class="text-xs text-gray-500 dark:text-gray-400">
                                Yesterday
                            </p>
                        </div>
                        <p class="font-semibold text-red-600 dark:text-red-400">
                            -Rp 500K
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>
