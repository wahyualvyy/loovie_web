<script setup lang="ts">
import { Head, Link, router } from '@inertiajs/vue3';
import { ref, computed } from 'vue';
import {
    CreditCard,
    Edit2,
    Trash2,
    Plus,
    Search,
    Download,
    Printer,
} from 'lucide-vue-next';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';

defineOptions({
    layout: {
        breadcrumbs: [
            { title: 'Dashboard', href: '/dashboard' },
            { title: 'Financial Accounts', href: '/financial-accounts' },
        ],
    },
});

interface Account {
    id: number;
    name: string;
    type: string;
    initial_balance: number;
    current_balance: number;
    description: string | null;
    is_active: boolean;
    created_at: string;
}

interface Props {
    accounts: {
        data: Account[];
        total: number;
        per_page: number;
        current_page: number;
        last_page: number;
    };
    totalBalance: number;
}

const props = defineProps<Props>();
const searchQuery = ref('');
const showDeleteConfirm = ref(false);
const accountToDelete = ref<Account | null>(null);

const accountTypes = {
    cash: 'Cash',
    bank: 'Bank',
    digital_wallet: 'E-Wallet',
    investment: 'Investment',
    credit_card: 'Credit Card',
};

const getTypeColor = (type: string) => {
    const colors: Record<string, string> = {
        cash: 'bg-green-100 text-green-800 dark:bg-green-900/30 dark:text-green-400',
        bank: 'bg-blue-100 text-blue-800 dark:bg-blue-900/30 dark:text-blue-400',
        digital_wallet:
            'bg-purple-100 text-purple-800 dark:bg-purple-900/30 dark:text-purple-400',
        investment:
            'bg-yellow-100 text-yellow-800 dark:bg-yellow-900/30 dark:text-yellow-400',
        credit_card:
            'bg-red-100 text-red-800 dark:bg-red-900/30 dark:text-red-400',
    };
    return colors[type] || 'bg-gray-100 text-gray-800';
};

const filteredAccounts = computed(() => {
    if (!searchQuery.value) return props.accounts.data;
    return props.accounts.data.filter(
        (account) =>
            account.name
                .toLowerCase()
                .includes(searchQuery.value.toLowerCase()) ||
            accountTypes[account.type as keyof typeof accountTypes]
                ?.toLowerCase()
                .includes(searchQuery.value.toLowerCase()),
    );
});

const confirmDelete = (account: Account) => {
    accountToDelete.value = account;
    showDeleteConfirm.value = true;
};

const deleteAccount = () => {
    if (accountToDelete.value) {
        router.delete(`/financial-accounts/${accountToDelete.value.id}`, {
            onSuccess: () => {
                showDeleteConfirm.value = false;
                accountToDelete.value = null;
            },
        });
    }
};

const formatCurrency = (value: number) => {
    return new Intl.NumberFormat('id-ID', {
        style: 'currency',
        currency: 'IDR',
        minimumFractionDigits: 0,
        maximumFractionDigits: 0,
    }).format(value);
};
</script>

<template>
    <Head title="Financial Accounts - Loovie Apps" />

    <div class="space-y-6 p-4 sm:p-6 lg:p-8">
        <!-- Header -->
        <div
            class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between"
        >
            <div>
                <h1
                    class="text-3xl font-bold text-gray-900 sm:text-4xl dark:text-white"
                >
                    Financial Accounts
                </h1>
                <p class="mt-1 text-gray-600 dark:text-gray-400">
                    Manage all your financial accounts
                </p>
            </div>
            <Link href="/financial-accounts/create">
                <Button class="bg-indigo-600 text-white hover:bg-indigo-700">
                    <Plus class="mr-2 h-4 w-4" />
                    Add Account
                </Button>
            </Link>
        </div>

        <!-- Total Balance Card -->
        <div
            class="rounded-lg bg-gradient-to-br from-indigo-600 to-purple-600 p-6 text-white shadow-lg"
        >
            <p class="text-sm font-medium opacity-90">Total Balance</p>
            <p class="mt-2 text-3xl font-bold sm:text-4xl">
                {{ formatCurrency(totalBalance) }}
            </p>
        </div>

        <!-- Search Bar -->
        <div
            class="rounded-lg border border-gray-200 bg-white p-4 shadow-sm dark:border-gray-800 dark:bg-gray-900"
        >
            <div class="relative">
                <Search class="absolute top-3 left-3 h-5 w-5 text-gray-400" />
                <Input
                    v-model="searchQuery"
                    type="text"
                    placeholder="Search accounts..."
                    class="rounded-lg border-gray-200 bg-gray-50 pl-10 dark:border-gray-700 dark:bg-gray-800"
                />
            </div>
        </div>

        <!-- Export/Print Buttons -->
        <div class="flex flex-wrap gap-2">
            <a
                href="/financial-accounts/export/csv"
                class="inline-flex items-center gap-2 rounded-lg bg-blue-500 px-4 py-2 text-white transition hover:bg-blue-600"
            >
                <Download class="h-4 w-4" />
                Export CSV
            </a>
            <a
                href="/financial-accounts/export/print"
                target="_blank"
                class="inline-flex items-center gap-2 rounded-lg bg-gray-500 px-4 py-2 text-white transition hover:bg-gray-600"
            >
                <Printer class="h-4 w-4" />
                Print Report
            </a>
        </div>

        <!-- Accounts Table - Responsive -->
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
                                Account Name
                            </th>
                            <th
                                class="px-6 py-3 text-left text-sm font-semibold text-gray-900 dark:text-white"
                            >
                                Type
                            </th>
                            <th
                                class="px-6 py-3 text-right text-sm font-semibold text-gray-900 dark:text-white"
                            >
                                Balance
                            </th>
                            <th
                                class="px-6 py-3 text-left text-sm font-semibold text-gray-900 dark:text-white"
                            >
                                Status
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
                            v-if="filteredAccounts.length === 0"
                            class="hover:bg-gray-50 dark:hover:bg-gray-800/50"
                        >
                            <td
                                colspan="5"
                                class="px-6 py-8 text-center text-gray-500 dark:text-gray-400"
                            >
                                No accounts found
                            </td>
                        </tr>
                        <tr
                            v-for="account in filteredAccounts"
                            :key="account.id"
                            class="transition-colors hover:bg-gray-50 dark:hover:bg-gray-800/50"
                        >
                            <td class="px-6 py-4">
                                <div class="flex items-center space-x-3">
                                    <div
                                        class="flex h-10 w-10 items-center justify-center rounded-lg bg-indigo-100 dark:bg-indigo-900/30"
                                    >
                                        <CreditCard
                                            class="h-5 w-5 text-indigo-600 dark:text-indigo-400"
                                        />
                                    </div>
                                    <div>
                                        <p
                                            class="font-medium text-gray-900 dark:text-white"
                                        >
                                            {{ account.name }}
                                        </p>
                                        <p
                                            class="text-sm text-gray-500 dark:text-gray-400"
                                        >
                                            {{ account.description }}
                                        </p>
                                    </div>
                                </div>
                            </td>
                            <td class="px-6 py-4">
                                <span
                                    :class="[
                                        'rounded-full px-3 py-1 text-sm font-medium',
                                        getTypeColor(account.type),
                                    ]"
                                >
                                    {{
                                        accountTypes[
                                            account.type as keyof typeof accountTypes
                                        ]
                                    }}
                                </span>
                            </td>
                            <td class="px-6 py-4 text-right">
                                <p
                                    class="font-semibold text-gray-900 dark:text-white"
                                >
                                    {{
                                        formatCurrency(account.current_balance)
                                    }}
                                </p>
                                <p
                                    class="text-sm text-gray-500 dark:text-gray-400"
                                >
                                    Initial:
                                    {{
                                        formatCurrency(account.initial_balance)
                                    }}
                                </p>
                            </td>
                            <td class="px-6 py-4">
                                <span
                                    v-if="account.is_active"
                                    class="rounded-full bg-green-100 px-3 py-1 text-sm font-medium text-green-800 dark:bg-green-900/30 dark:text-green-400"
                                >
                                    Active
                                </span>
                                <span
                                    v-else
                                    class="rounded-full bg-gray-100 px-3 py-1 text-sm font-medium text-gray-800 dark:bg-gray-800 dark:text-gray-400"
                                >
                                    Inactive
                                </span>
                            </td>
                            <td class="px-6 py-4">
                                <div
                                    class="flex items-center justify-center space-x-2"
                                >
                                    <Link
                                        :href="`/financial-accounts/${account.id}/edit`"
                                        class="inline-flex"
                                    >
                                        <Button
                                            variant="ghost"
                                            size="sm"
                                            class="text-blue-600 hover:bg-blue-50 hover:text-blue-700 dark:hover:bg-blue-900/20"
                                        >
                                            <Edit2 class="h-4 w-4" />
                                        </Button>
                                    </Link>
                                    <Button
                                        variant="ghost"
                                        size="sm"
                                        @click="confirmDelete(account)"
                                        class="text-red-600 hover:bg-red-50 hover:text-red-700 dark:hover:bg-red-900/20"
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
                    v-if="filteredAccounts.length === 0"
                    class="p-8 text-center text-gray-500 dark:text-gray-400"
                >
                    No accounts found
                </div>
                <div
                    v-for="account in filteredAccounts"
                    :key="account.id"
                    class="border-b border-gray-200 p-4 last:border-b-0 dark:border-gray-700"
                >
                    <div class="mb-3 flex items-start justify-between">
                        <div class="flex flex-1 items-center space-x-3">
                            <div
                                class="flex h-10 w-10 flex-shrink-0 items-center justify-center rounded-lg bg-indigo-100 dark:bg-indigo-900/30"
                            >
                                <CreditCard
                                    class="h-5 w-5 text-indigo-600 dark:text-indigo-400"
                                />
                            </div>
                            <div class="min-w-0 flex-1">
                                <p
                                    class="truncate font-medium text-gray-900 dark:text-white"
                                >
                                    {{ account.name }}
                                </p>
                                <p
                                    class="truncate text-sm text-gray-500 dark:text-gray-400"
                                >
                                    {{ account.description }}
                                </p>
                            </div>
                        </div>
                        <div class="ml-2 flex items-center space-x-2">
                            <Link
                                :href="`/financial-accounts/${account.id}/edit`"
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
                                @click="confirmDelete(account)"
                                class="text-red-600"
                            >
                                <Trash2 class="h-4 w-4" />
                            </Button>
                        </div>
                    </div>
                    <div class="grid grid-cols-3 gap-2 text-sm">
                        <div>
                            <p class="text-gray-600 dark:text-gray-400">Type</p>
                            <span
                                :class="[
                                    'mt-1 block rounded px-2 py-1 text-center text-xs font-medium',
                                    getTypeColor(account.type),
                                ]"
                            >
                                {{
                                    accountTypes[
                                        account.type as keyof typeof accountTypes
                                    ]
                                }}
                            </span>
                        </div>
                        <div>
                            <p class="text-gray-600 dark:text-gray-400">
                                Status
                            </p>
                            <span
                                v-if="account.is_active"
                                class="mt-1 block rounded bg-green-100 px-2 py-1 text-center text-xs font-medium text-green-800 dark:bg-green-900/30 dark:text-green-400"
                            >
                                Active
                            </span>
                            <span
                                v-else
                                class="mt-1 block rounded bg-gray-100 px-2 py-1 text-center text-xs font-medium text-gray-800 dark:bg-gray-800 dark:text-gray-400"
                            >
                                Inactive
                            </span>
                        </div>
                        <div>
                            <p class="text-gray-600 dark:text-gray-400">
                                Balance
                            </p>
                            <p
                                class="mt-1 text-xs font-semibold text-gray-900 dark:text-white"
                            >
                                {{ formatCurrency(account.current_balance) }}
                            </p>
                        </div>
                    </div>
                </div>
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
                Delete Account
            </h3>
            <p class="mb-6 text-gray-600 dark:text-gray-400">
                Are you sure you want to delete
                <strong>{{ accountToDelete?.name }}</strong
                >? This action cannot be undone.
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
                    @click="deleteAccount"
                    class="bg-red-600 px-4 py-2 text-white hover:bg-red-700"
                >
                    Delete
                </Button>
            </div>
        </div>
    </div>
</template>
