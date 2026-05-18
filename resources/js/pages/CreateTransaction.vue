<script setup lang="ts">
import { Head, Link, useForm } from '@inertiajs/vue3';
import { ref, computed } from 'vue';
import { ArrowLeft, Upload } from 'lucide-vue-next';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';

defineOptions({
    layout: {
        breadcrumbs: [
            { title: 'Dashboard', href: '/dashboard' },
            { title: 'Transactions', href: '/transactions' },
            { title: 'Add Transaction', href: '/transactions/create' },
        ],
    },
});

interface Account {
    id: number;
    name: string;
    current_balance: number;
}

interface Category {
    id: number;
    name: string;
    type: 'income' | 'expense';
    color: string;
}

interface Props {
    accounts: Account[];
    categories: Category[];
}

const props = defineProps<Props>();
const attachmentFile = ref<File | null>(null);
const attachmentPreview = ref<string | null>(null);

const form = useForm({
    financial_account_id: '',
    category_id: '',
    type: '',
    amount: '',
    transaction_date: new Date().toISOString().split('T')[0],
    description: '',
    attachment: null as File | null,
});

const selectedCategory = computed(() => {
    return props.categories.find(
        (c) => c.id === parseInt(form.category_id || '0'),
    );
});

const selectedAccount = computed(() => {
    return props.accounts.find(
        (a) => a.id === parseInt(form.financial_account_id || '0'),
    );
});

const incomeCategories = computed(() => {
    return props.categories.filter((c) => c.type === 'income');
});

const expenseCategories = computed(() => {
    return props.categories.filter((c) => c.type === 'expense');
});

const handleCategoryChange = (e: Event) => {
    const categoryId = parseInt((e.target as HTMLSelectElement).value || '0');
    const category = props.categories.find((c) => c.id === categoryId);
    if (category) {
        form.type = category.type;
        form.category_id = String(categoryId);
    }
};

const handleFileChange = (e: Event) => {
    const target = e.target as HTMLInputElement;
    const file = target.files?.[0];

    if (file) {
        attachmentFile.value = file;
        form.attachment = file;

        if (file.type.startsWith('image/')) {
            const reader = new FileReader();
            reader.onload = (e) => {
                attachmentPreview.value = e.target?.result as string;
            };
            reader.readAsDataURL(file);
        }
    }
};

const removeAttachment = () => {
    attachmentFile.value = null;
    attachmentPreview.value = null;
    form.attachment = null;
};

const submit = () => {
    form.post(route('transactions.store'), {
        onError: (errors) => {
            console.error('Validation errors:', errors);
        },
    });
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
    <Head title="Add Transaction - Loovie Apps" />

    <div class="p-4 sm:p-6 lg:p-8">
        <!-- Header -->
        <div class="mb-6">
            <Link
                :href="route('transactions.index')"
                class="mb-4 inline-flex items-center text-indigo-600 hover:text-indigo-700"
            >
                <ArrowLeft class="mr-2 h-4 w-4" />
                Back to Transactions
            </Link>
            <h1
                class="text-3xl font-bold text-gray-900 sm:text-4xl dark:text-white"
            >
                Add New Transaction
            </h1>
        </div>

        <div class="grid grid-cols-1 gap-6 lg:grid-cols-3">
            <!-- Form -->
            <div class="lg:col-span-2">
                <div
                    class="space-y-6 rounded-lg border border-gray-200 bg-white p-6 shadow-sm dark:border-gray-800 dark:bg-gray-900"
                >
                    <!-- Account Selection -->
                    <div>
                        <label
                            class="mb-2 block text-sm font-semibold text-gray-900 dark:text-white"
                        >
                            Financial Account
                            <span class="text-red-500">*</span>
                        </label>
                        <select
                            v-model="form.financial_account_id"
                            class="w-full rounded-lg border border-gray-200 bg-gray-50 px-4 py-2 text-gray-900 focus:border-transparent focus:ring-2 focus:ring-indigo-500 dark:border-gray-700 dark:bg-gray-800 dark:text-white"
                        >
                            <option value="">Select an account...</option>
                            <option
                                v-for="account in accounts"
                                :key="account.id"
                                :value="String(account.id)"
                            >
                                {{ account.name }} (Balance:
                                {{ formatCurrency(account.current_balance) }})
                            </option>
                        </select>
                        <p
                            v-if="form.errors.financial_account_id"
                            class="mt-2 text-sm text-red-600"
                        >
                            {{ form.errors.financial_account_id }}
                        </p>
                    </div>

                    <!-- Category Selection with Type -->
                    <div>
                        <label
                            class="mb-3 block text-sm font-semibold text-gray-900 dark:text-white"
                        >
                            Transaction Type <span class="text-red-500">*</span>
                        </label>
                        <div class="space-y-3">
                            <div class="flex items-center space-x-4">
                                <button
                                    type="button"
                                    @click="form.type = 'income'"
                                    :class="[
                                        'flex-1 rounded-lg px-4 py-3 font-medium transition-all',
                                        form.type === 'income'
                                            ? 'border-2 border-green-500 bg-green-100 text-green-700 dark:bg-green-900/30 dark:text-green-400'
                                            : 'border border-gray-200 bg-gray-50 text-gray-700 hover:border-green-300 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-300',
                                    ]"
                                >
                                    + Income
                                </button>
                                <button
                                    type="button"
                                    @click="form.type = 'expense'"
                                    :class="[
                                        'flex-1 rounded-lg px-4 py-3 font-medium transition-all',
                                        form.type === 'expense'
                                            ? 'border-2 border-red-500 bg-red-100 text-red-700 dark:bg-red-900/30 dark:text-red-400'
                                            : 'border border-gray-200 bg-gray-50 text-gray-700 hover:border-red-300 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-300',
                                    ]"
                                >
                                    - Expense
                                </button>
                            </div>
                        </div>
                        <p
                            v-if="form.errors.type"
                            class="mt-2 text-sm text-red-600"
                        >
                            {{ form.errors.type }}
                        </p>
                    </div>

                    <!-- Category -->
                    <div>
                        <label
                            class="mb-2 block text-sm font-semibold text-gray-900 dark:text-white"
                        >
                            Category <span class="text-red-500">*</span>
                        </label>
                        <select
                            v-model="form.category_id"
                            @change="handleCategoryChange"
                            class="w-full rounded-lg border border-gray-200 bg-gray-50 px-4 py-2 text-gray-900 focus:border-transparent focus:ring-2 focus:ring-indigo-500 dark:border-gray-700 dark:bg-gray-800 dark:text-white"
                        >
                            <option value="">Select a category...</option>
                            <optgroup
                                v-if="form.type === 'income' || !form.type"
                                label="Income Categories"
                            >
                                <option
                                    v-for="category in incomeCategories"
                                    :key="category.id"
                                    :value="String(category.id)"
                                >
                                    {{ category.name }}
                                </option>
                            </optgroup>
                            <optgroup
                                v-if="form.type === 'expense' || !form.type"
                                label="Expense Categories"
                            >
                                <option
                                    v-for="category in expenseCategories"
                                    :key="category.id"
                                    :value="String(category.id)"
                                >
                                    {{ category.name }}
                                </option>
                            </optgroup>
                        </select>
                        <p
                            v-if="form.errors.category_id"
                            class="mt-2 text-sm text-red-600"
                        >
                            {{ form.errors.category_id }}
                        </p>
                    </div>

                    <!-- Amount -->
                    <div>
                        <label
                            class="mb-2 block text-sm font-semibold text-gray-900 dark:text-white"
                        >
                            Amount <span class="text-red-500">*</span>
                        </label>
                        <div class="relative">
                            <span
                                class="absolute top-3 left-4 text-gray-500 dark:text-gray-400"
                                >Rp</span
                            >
                            <Input
                                v-model="form.amount"
                                type="number"
                                placeholder="0"
                                min="1"
                                step="0.01"
                                class="rounded-lg border-gray-200 bg-gray-50 pl-10 dark:border-gray-700 dark:bg-gray-800"
                            />
                        </div>
                        <p
                            v-if="form.errors.amount"
                            class="mt-2 text-sm text-red-600"
                        >
                            {{ form.errors.amount }}
                        </p>
                    </div>

                    <!-- Date -->
                    <div>
                        <label
                            class="mb-2 block text-sm font-semibold text-gray-900 dark:text-white"
                        >
                            Transaction Date <span class="text-red-500">*</span>
                        </label>
                        <Input
                            v-model="form.transaction_date"
                            type="date"
                            class="w-full rounded-lg border-gray-200 bg-gray-50 dark:border-gray-700 dark:bg-gray-800"
                        />
                        <p
                            v-if="form.errors.transaction_date"
                            class="mt-2 text-sm text-red-600"
                        >
                            {{ form.errors.transaction_date }}
                        </p>
                    </div>

                    <!-- Description -->
                    <div>
                        <label
                            class="mb-2 block text-sm font-semibold text-gray-900 dark:text-white"
                        >
                            Description
                        </label>
                        <textarea
                            v-model="form.description"
                            placeholder="Add notes about this transaction..."
                            rows="3"
                            class="w-full rounded-lg border border-gray-200 bg-gray-50 px-4 py-2 text-gray-900 focus:border-transparent focus:ring-2 focus:ring-indigo-500 dark:border-gray-700 dark:bg-gray-800 dark:text-white"
                        ></textarea>
                    </div>

                    <!-- Attachment -->
                    <div>
                        <label
                            class="mb-2 block text-sm font-semibold text-gray-900 dark:text-white"
                        >
                            Attachment (Receipt/Proof)
                        </label>
                        <div
                            class="cursor-pointer rounded-lg border-2 border-dashed border-gray-300 p-6 text-center transition-colors hover:border-indigo-500 dark:border-gray-600"
                        >
                            <input
                                type="file"
                                accept="image/jpg,image/jpeg,image/png,application/pdf"
                                @change="handleFileChange"
                                class="hidden"
                                ref="fileInput"
                            />
                            <label class="block cursor-pointer">
                                <Upload
                                    class="mx-auto mb-2 h-8 w-8 text-gray-400"
                                />
                                <p
                                    class="text-sm font-medium text-gray-700 dark:text-gray-300"
                                >
                                    Click to upload or drag and drop
                                </p>
                                <p
                                    class="mt-1 text-xs text-gray-500 dark:text-gray-400"
                                >
                                    JPG, PNG or PDF up to 5MB
                                </p>
                            </label>
                        </div>
                    </div>

                    <!-- Form Actions -->
                    <div
                        class="flex gap-3 border-t border-gray-200 pt-6 dark:border-gray-700"
                    >
                        <Link
                            :href="route('transactions.index')"
                            class="flex-1"
                        >
                            <Button variant="outline" class="w-full"
                                >Cancel</Button
                            >
                        </Link>
                        <Button
                            @click="submit"
                            :disabled="form.processing"
                            class="flex-1 bg-indigo-600 text-white hover:bg-indigo-700"
                        >
                            {{
                                form.processing
                                    ? 'Creating...'
                                    : 'Create Transaction'
                            }}
                        </Button>
                    </div>
                </div>
            </div>

            <!-- Preview Sidebar -->
            <div class="lg:col-span-1">
                <!-- Category Preview -->
                <div
                    v-if="selectedCategory"
                    class="mb-6 rounded-lg border border-gray-200 bg-white p-6 shadow-sm dark:border-gray-800 dark:bg-gray-900"
                >
                    <p
                        class="mb-3 text-xs font-medium tracking-wide text-gray-600 uppercase dark:text-gray-400"
                    >
                        Category Preview
                    </p>
                    <div class="flex items-center space-x-3">
                        <div
                            :style="{ backgroundColor: selectedCategory.color }"
                            class="flex h-12 w-12 items-center justify-center rounded-lg text-lg font-semibold text-white"
                        >
                            {{ selectedCategory.name.charAt(0) }}
                        </div>
                        <div>
                            <p
                                class="font-medium text-gray-900 dark:text-white"
                            >
                                {{ selectedCategory.name }}
                            </p>
                            <p
                                :class="[
                                    'text-xs font-medium',
                                    selectedCategory.type === 'income'
                                        ? 'text-green-600'
                                        : 'text-red-600',
                                ]"
                            >
                                {{
                                    selectedCategory.type === 'income'
                                        ? '+ Income'
                                        : '- Expense'
                                }}
                            </p>
                        </div>
                    </div>
                </div>

                <!-- Account Preview -->
                <div
                    v-if="selectedAccount"
                    class="mb-6 rounded-lg border border-gray-200 bg-white p-6 shadow-sm dark:border-gray-800 dark:bg-gray-900"
                >
                    <p
                        class="mb-3 text-xs font-medium tracking-wide text-gray-600 uppercase dark:text-gray-400"
                    >
                        Account Preview
                    </p>
                    <p class="text-sm text-gray-600 dark:text-gray-400">
                        {{ selectedAccount.name }}
                    </p>
                    <p
                        class="mt-2 text-2xl font-bold text-gray-900 dark:text-white"
                    >
                        {{ formatCurrency(selectedAccount.current_balance) }}
                    </p>
                </div>

                <!-- Attachment Preview -->
                <div
                    v-if="attachmentPreview"
                    class="mb-6 rounded-lg border border-gray-200 bg-white p-6 shadow-sm dark:border-gray-800 dark:bg-gray-900"
                >
                    <p
                        class="mb-3 text-xs font-medium tracking-wide text-gray-600 uppercase dark:text-gray-400"
                    >
                        Attachment Preview
                    </p>
                    <img
                        :src="attachmentPreview"
                        alt="Preview"
                        class="mb-3 h-48 w-full rounded-lg object-cover"
                    />
                    <Button
                        variant="outline"
                        @click="removeAttachment"
                        class="w-full text-red-600 hover:text-red-700"
                    >
                        Remove
                    </Button>
                </div>

                <!-- Summary -->
                <div
                    v-if="form.amount && selectedCategory"
                    class="rounded-lg border border-indigo-200 bg-gradient-to-br from-indigo-50 to-indigo-100 p-6 shadow-sm dark:border-indigo-700 dark:from-indigo-900/20 dark:to-indigo-800/20"
                >
                    <p
                        class="mb-4 text-xs font-medium tracking-wide text-indigo-700 uppercase dark:text-indigo-400"
                    >
                        Transaction Summary
                    </p>
                    <div class="space-y-3">
                        <div class="flex justify-between text-sm">
                            <span class="text-gray-700 dark:text-gray-300"
                                >Type:</span
                            >
                            <span
                                :class="[
                                    'font-medium',
                                    form.type === 'income'
                                        ? 'text-green-600'
                                        : 'text-red-600',
                                ]"
                            >
                                {{ form.type === 'income' ? '+' : '-' }}
                                {{ selectedCategory.name }}
                            </span>
                        </div>
                        <div class="flex justify-between text-sm">
                            <span class="text-gray-700 dark:text-gray-300"
                                >Amount:</span
                            >
                            <span
                                class="font-medium text-gray-900 dark:text-white"
                                >{{
                                    formatCurrency(Number(form.amount) || 0)
                                }}</span
                            >
                        </div>
                        <div class="flex justify-between text-sm">
                            <span class="text-gray-700 dark:text-gray-300"
                                >Account:</span
                            >
                            <span
                                class="font-medium text-gray-900 dark:text-white"
                                >{{ selectedAccount?.name || '-' }}</span
                            >
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>
