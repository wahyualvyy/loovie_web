<script setup lang="ts">
import { Head, Link, router, useForm } from '@inertiajs/vue3';
import { ref, computed } from 'vue';
import { Edit2, Trash2, Plus, Search, Download } from 'lucide-vue-next';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';

defineOptions({
    layout: {
        breadcrumbs: [
            { title: 'Dashboard', href: '/dashboard' },
            { title: 'Transactions', href: '/transactions' },
        ],
    },
});

interface Account {
    id: number;
    name: string;
}

interface Category {
    id: number;
    name: string;
    type: 'income' | 'expense';
    color: string;
}

interface TransactionItem {
    id: number;
    financial_account_id: number;
    category_id: number;
    type: 'income' | 'expense';
    amount: number;
    transaction_date: string;
    description: string | null;
    attachment: string | null;
    created_at: string;
    account: Account;
    category: Category;
}

interface Props {
    transactions: {
        data: TransactionItem[];
        total: number;
        per_page: number;
        current_page: number;
        last_page: number;
        from: number;
        to: number;
    };
    accounts: Account[];
    categories: Category[];
    filters: {
        search: string | null;
        account_id: string | null;
        category_id: string | null;
        type: string | null;
        date_from: string | null;
        date_to: string | null;
        month: string | null;
    };
}

const props = defineProps<Props>();
const showDeleteConfirm = ref(false);
const transactionToDelete = ref<TransactionItem | null>(null);

const form = useForm({
    search: props.filters.search || '',
    account_id: props.filters.account_id || '',
    category_id: props.filters.category_id || '',
    type: props.filters.type || '',
    date_from: props.filters.date_from || '',
    date_to: props.filters.date_to || '',
    month: props.filters.month || '',
});

const totalIncome = computed(() => {
    return props.transactions.data
        .filter((t) => t.type === 'income')
        .reduce((sum, t) => sum + t.amount, 0);
});

const totalExpense = computed(() => {
    return props.transactions.data
        .filter((t) => t.type === 'expense')
        .reduce((sum, t) => sum + t.amount, 0);
});

const confirmDelete = (transaction: TransactionItem) => {
    transactionToDelete.value = transaction;
    showDeleteConfirm.value = true;
};

const deleteTransaction = () => {
    if (transactionToDelete.value) {
        router.delete(
            route('transactions.destroy', transactionToDelete.value.id),
            {
                onSuccess: () => {
                    showDeleteConfirm.value = false;
                    transactionToDelete.value = null;
                },
            },
        );
    }
};

const applyFilters = () => {
    form.get(route('transactions.index'));
};

const resetFilters = () => {
    form.reset();
    router.get(route('transactions.index'));
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

const getAccountName = (accountId: number) => {
    return props.accounts.find((a) => a.id === accountId)?.name || 'Unknown';
};

const getCategoryName = (categoryId: number) => {
    return props.categories.find((c) => c.id === categoryId)?.name || 'Unknown';
};
</script>

<template>
    <Head title="Transactions - Loovie Apps" />

    <div class="space-y-6 p-4 sm:p-6 lg:p-8">
        <!-- Header -->
        <div
            class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between"
        >
            <div>
                <h1
                    class="text-3xl font-bold text-gray-900 sm:text-4xl dark:text-white"
                >
                    Transactions
                </h1>
                <p class="mt-1 text-gray-600 dark:text-gray-400">
                    All your income and expenses
                </p>
            </div>
            <Link :href="route('transactions.create')">
                <Button class="bg-indigo-600 text-white hover:bg-indigo-700">
                    <Plus class="mr-2 h-4 w-4" />
                    Add Transaction
                </Button>
            </Link>
        </div>

        <!-- Summary Stats -->
        <div class="grid grid-cols-1 gap-4 md:grid-cols-2">
            <div
                class="rounded-lg border border-green-200 bg-green-50 p-4 dark:border-green-800 dark:bg-green-900/20"
            >
                <p
                    class="text-sm font-medium text-green-700 dark:text-green-400"
                >
                    Total Income
                </p>
                <p
                    class="mt-1 text-2xl font-bold text-green-600 sm:text-3xl dark:text-green-400"
                >
                    {{ formatCurrency(totalIncome) }}
                </p>
            </div>
            <div
                class="rounded-lg border border-red-200 bg-red-50 p-4 dark:border-red-800 dark:bg-red-900/20"
            >
                <p class="text-sm font-medium text-red-700 dark:text-red-400">
                    Total Expense
                </p>
                <p
                    class="mt-1 text-2xl font-bold text-red-600 sm:text-3xl dark:text-red-400"
                >
                    {{ formatCurrency(totalExpense) }}
                </p>
            </div>
        </div>

        <!-- Filters -->
        <div
            class="space-y-4 rounded-lg border border-gray-200 bg-white p-4 shadow-sm dark:border-gray-800 dark:bg-gray-900"
        >
            <div class="grid grid-cols-1 gap-4 md:grid-cols-2 lg:grid-cols-4">
                <!-- Search -->
                <div class="relative">
                    <Search
                        class="absolute top-3 left-3 h-5 w-5 text-gray-400"
                    />
                    <Input
                        v-model="form.search"
                        type="text"
                        placeholder="Search description..."
                        class="rounded-lg border-gray-200 bg-gray-50 pl-10 dark:border-gray-700 dark:bg-gray-800"
                    />
                </div>

                <!-- Account Filter -->
                <select
                    v-model="form.account_id"
                    class="rounded-lg border border-gray-200 bg-gray-50 px-4 py-2 text-gray-900 focus:ring-2 focus:ring-indigo-500 dark:border-gray-700 dark:bg-gray-800 dark:text-white"
                >
                    <option value="">All Accounts</option>
                    <option
                        v-for="account in accounts"
                        :key="account.id"
                        :value="String(account.id)"
                    >
                        {{ account.name }}
                    </option>
                </select>

                <!-- Category Filter -->
                <select
                    v-model="form.category_id"
                    class="rounded-lg border border-gray-200 bg-gray-50 px-4 py-2 text-gray-900 focus:ring-2 focus:ring-indigo-500 dark:border-gray-700 dark:bg-gray-800 dark:text-white"
                >
                    <option value="">All Categories</option>
                    <option
                        v-for="category in categories"
                        :key="category.id"
                        :value="String(category.id)"
                    >
                        {{ category.name }}
                    </option>
                </select>

                <!-- Type Filter -->
                <select
                    v-model="form.type"
                    class="rounded-lg border border-gray-200 bg-gray-50 px-4 py-2 text-gray-900 focus:ring-2 focus:ring-indigo-500 dark:border-gray-700 dark:bg-gray-800 dark:text-white"
                >
                    <option value="">All Types</option>
                    <option value="income">Income</option>
                    <option value="expense">Expense</option>
                </select>
            </div>

            <div class="grid grid-cols-1 gap-4 md:grid-cols-3">
                <!-- Date From -->
                <div>
                    <label
                        class="mb-1 block text-sm text-gray-600 dark:text-gray-400"
                        >From Date</label
                    >
                    <Input
                        v-model="form.date_from"
                        type="date"
                        class="w-full rounded-lg border-gray-200 bg-gray-50 dark:border-gray-700 dark:bg-gray-800"
                    />
                </div>

                <!-- Date To -->
                <div>
                    <label
                        class="mb-1 block text-sm text-gray-600 dark:text-gray-400"
                        >To Date</label
                    >
                    <Input
                        v-model="form.date_to"
                        type="date"
                        class="w-full rounded-lg border-gray-200 bg-gray-50 dark:border-gray-700 dark:bg-gray-800"
                    />
                </div>

                <!-- Month Filter -->
                <div>
                    <label
                        class="mb-1 block text-sm text-gray-600 dark:text-gray-400"
                        >Or Select Month</label
                    >
                    <Input
                        v-model="form.month"
                        type="month"
                        class="w-full rounded-lg border-gray-200 bg-gray-50 dark:border-gray-700 dark:bg-gray-800"
                    />
                </div>
            </div>

            <!-- Filter Buttons -->
            <div class="flex justify-end gap-2">
                <Button
                    variant="outline"
                    @click="resetFilters"
                    class="text-gray-700 dark:text-gray-300"
                >
                    Reset
                </Button>
                <Button
                    @click="applyFilters"
                    class="bg-indigo-600 text-white hover:bg-indigo-700"
                >
                    Apply Filters
                </Button>
            </div>
        </div>

        <!-- Transactions Table - Responsive -->
        <div
            class="overflow-hidden rounded-lg border border-gray-200 bg-white shadow-sm dark:border-gray-800 dark:bg-gray-900"
        >
            <!-- Desktop View -->
            <div class="hidden overflow-x-auto md:block">
                <table class="w-full">
                    <thead
                        class="border-b border-gray-200 bg-gray-50 dark:border-gray-700 dark:bg-gray-800"
                    >
                        <tr>
                            <th
                                class="px-6 py-3 text-left text-sm font-semibold text-gray-900 dark:text-white"
                            >
                                Date
                            </th>
                            <th
                                class="px-6 py-3 text-left text-sm font-semibold text-gray-900 dark:text-white"
                            >
                                Description
                            </th>
                            <th
                                class="px-6 py-3 text-left text-sm font-semibold text-gray-900 dark:text-white"
                            >
                                Account
                            </th>
                            <th
                                class="px-6 py-3 text-left text-sm font-semibold text-gray-900 dark:text-white"
                            >
                                Category
                            </th>
                            <th
                                class="px-6 py-3 text-right text-sm font-semibold text-gray-900 dark:text-white"
                            >
                                Amount
                            </th>
                            <th
                                class="px-6 py-3 text-center text-sm font-semibold text-gray-900 dark:text-white"
                            >
                                Actions
                            </th>
                        </tr>
                    </thead>
                    <tbody
                        class="divide-y divide-gray-200 dark:divide-gray-700"
                    >
                        <tr
                            v-if="transactions.data.length === 0"
                            class="hover:bg-gray-50 dark:hover:bg-gray-800/50"
                        >
                            <td
                                colspan="6"
                                class="px-6 py-8 text-center text-gray-500 dark:text-gray-400"
                            >
                                No transactions found
                            </td>
                        </tr>
                        <tr
                            v-for="transaction in transactions.data"
                            :key="transaction.id"
                            class="transition-colors hover:bg-gray-50 dark:hover:bg-gray-800/50"
                        >
                            <td
                                class="px-6 py-4 text-sm font-medium text-gray-900 dark:text-white"
                            >
                                {{ formatDate(transaction.transaction_date) }}
                            </td>
                            <td
                                class="px-6 py-4 text-sm text-gray-600 dark:text-gray-400"
                            >
                                {{ transaction.description || '-' }}
                            </td>
                            <td
                                class="px-6 py-4 text-sm text-gray-900 dark:text-white"
                            >
                                {{ transaction.account.name }}
                            </td>
                            <td class="px-6 py-4 text-sm">
                                <span
                                    :style="{
                                        backgroundColor:
                                            transaction.category.color + '30',
                                    }"
                                    class="rounded-full px-3 py-1 text-xs font-medium"
                                    :style="{
                                        color: transaction.category.color,
                                    }"
                                >
                                    {{ transaction.category.name }}
                                </span>
                            </td>
                            <td
                                :class="[
                                    'px-6 py-4 text-right text-sm font-semibold',
                                    transaction.type === 'income'
                                        ? 'text-green-600 dark:text-green-400'
                                        : 'text-red-600 dark:text-red-400',
                                ]"
                            >
                                {{ transaction.type === 'income' ? '+' : '-'
                                }}{{ formatCurrency(transaction.amount) }}
                            </td>
                            <td class="px-6 py-4">
                                <div
                                    class="flex items-center justify-center space-x-2"
                                >
                                    <Link
                                        :href="
                                            route(
                                                'transactions.edit',
                                                transaction.id,
                                            )
                                        "
                                        class="inline-flex"
                                    >
                                        <Button
                                            variant="ghost"
                                            size="sm"
                                            class="text-blue-600 hover:text-blue-700"
                                        >
                                            <Edit2 class="h-4 w-4" />
                                        </Button>
                                    </Link>
                                    <Button
                                        variant="ghost"
                                        size="sm"
                                        @click="confirmDelete(transaction)"
                                        class="text-red-600 hover:text-red-700"
                                    >
                                        <Trash2 class="h-4 w-4" />
                                    </Button>
                                </div>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <!-- Mobile View -->
            <div class="md:hidden">
                <div
                    v-if="transactions.data.length === 0"
                    class="p-8 text-center text-gray-500 dark:text-gray-400"
                >
                    No transactions found
                </div>
                <div
                    v-for="transaction in transactions.data"
                    :key="transaction.id"
                    class="border-b border-gray-200 p-4 last:border-b-0 dark:border-gray-700"
                >
                    <div class="mb-3 flex items-start justify-between">
                        <div>
                            <p
                                class="font-medium text-gray-900 dark:text-white"
                            >
                                {{ transaction.category.name }}
                            </p>
                            <p class="text-xs text-gray-500 dark:text-gray-400">
                                {{ formatDate(transaction.transaction_date) }}
                            </p>
                        </div>
                        <div class="flex items-center space-x-2">
                            <Link
                                :href="
                                    route('transactions.edit', transaction.id)
                                "
                            >
                                <Button
                                    variant="ghost"
                                    size="sm"
                                    class="text-blue-600"
                                >
                                    <Edit2 class="h-4 w-4" />
                                </Button>
                            </Link>
                            <Button
                                variant="ghost"
                                size="sm"
                                @click="confirmDelete(transaction)"
                                class="text-red-600"
                            >
                                <Trash2 class="h-4 w-4" />
                            </Button>
                        </div>
                    </div>
                    <div class="space-y-2 text-sm">
                        <div class="flex justify-between">
                            <span class="text-gray-600 dark:text-gray-400"
                                >Account:</span
                            >
                            <span
                                class="font-medium text-gray-900 dark:text-white"
                                >{{ transaction.account.name }}</span
                            >
                        </div>
                        <div class="flex justify-between">
                            <span class="text-gray-600 dark:text-gray-400"
                                >Description:</span
                            >
                            <span
                                class="font-medium text-gray-900 dark:text-white"
                                >{{ transaction.description || '-' }}</span
                            >
                        </div>
                        <div
                            class="flex justify-between border-t border-gray-200 pt-2 dark:border-gray-700"
                        >
                            <span class="text-gray-600 dark:text-gray-400"
                                >Amount:</span
                            >
                            <span
                                :class="[
                                    'font-bold',
                                    transaction.type === 'income'
                                        ? 'text-green-600 dark:text-green-400'
                                        : 'text-red-600 dark:text-red-400',
                                ]"
                            >
                                {{ transaction.type === 'income' ? '+' : '-'
                                }}{{ formatCurrency(transaction.amount) }}
                            </span>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Pagination -->
        <div
            v-if="transactions.last_page > 1"
            class="flex items-center justify-between"
        >
            <p class="text-sm text-gray-600 dark:text-gray-400">
                Showing {{ transactions.from }} to {{ transactions.to }} of
                {{ transactions.total }} results
            </p>
            <div class="flex gap-2">
                <Link
                    v-if="transactions.current_page > 1"
                    :href="`${route('transactions.index')}?page=${transactions.current_page - 1}`"
                >
                    <Button variant="outline">Previous</Button>
                </Link>
                <Link
                    v-if="transactions.current_page < transactions.last_page"
                    :href="`${route('transactions.index')}?page=${transactions.current_page + 1}`"
                >
                    <Button variant="outline">Next</Button>
                </Link>
            </div>
        </div>
    </div>

    <!-- Delete Confirmation Modal -->
    <div
        v-if="showDeleteConfirm"
        class="fixed inset-0 z-50 flex items-center justify-center bg-black/50 p-4 dark:bg-black/70"
    >
        <div
            class="w-full max-w-sm rounded-lg bg-white p-6 shadow-xl dark:bg-gray-900"
        >
            <h3
                class="mb-4 text-lg font-semibold text-gray-900 dark:text-white"
            >
                Delete Transaction
            </h3>
            <p class="mb-6 text-gray-600 dark:text-gray-400">
                Are you sure you want to delete this transaction? The account
                balance will be adjusted accordingly.
            </p>
            <div class="flex items-center justify-end space-x-3">
                <Button
                    variant="outline"
                    @click="showDeleteConfirm = false"
                    class="px-4 py-2"
                >
                    Cancel
                </Button>
                <Button
                    @click="deleteTransaction"
                    class="bg-red-600 px-4 py-2 text-white hover:bg-red-700"
                >
                    Delete
                </Button>
            </div>
        </div>
    </div>
</template>
