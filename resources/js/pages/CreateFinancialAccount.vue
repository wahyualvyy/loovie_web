<script setup lang="ts">
import { Head, Link, useForm } from '@inertiajs/vue3';
import { ref } from 'vue';
import { ArrowLeft, AlertCircle } from 'lucide-vue-next';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';

defineOptions({
    layout: {
        breadcrumbs: [
            { title: 'Dashboard', href: '/dashboard' },
            { title: 'Financial Accounts', href: '/financial-accounts' },
            { title: 'Create Account', href: '/financial-accounts/create' },
        ],
    },
});

const accountTypes = [
    { value: 'cash', label: 'Cash / Tunai' },
    { value: 'bank', label: 'Bank' },
    { value: 'digital_wallet', label: 'E-Wallet (Dana, GoPay, dll)' },
    { value: 'investment', label: 'Investment' },
    { value: 'credit_card', label: 'Credit Card' },
];

const form = useForm({
    name: '',
    type: '',
    initial_balance: '',
    description: '',
    is_active: true,
});

const isSubmitting = ref(false);

const submit = () => {
    isSubmitting.value = true;
    form.post(route('financial-accounts.store'), {
        onFinish: () => {
            isSubmitting.value = false;
        },
    });
};
</script>

<template>
    <Head title="Create Financial Account - Loovie Apps" />

    <div class="max-w-2xl p-4 sm:p-6 lg:p-8">
        <!-- Header -->
        <div class="mb-6 flex items-center space-x-3">
            <Link
                :href="route('financial-accounts.index')"
                class="text-gray-600 hover:text-gray-900 dark:text-gray-400 dark:hover:text-white"
            >
                <ArrowLeft class="h-6 w-6" />
            </Link>
            <div>
                <h1 class="text-3xl font-bold text-gray-900 dark:text-white">
                    Add New Account
                </h1>
                <p class="mt-1 text-gray-600 dark:text-gray-400">
                    Create a new financial account
                </p>
            </div>
        </div>

        <!-- Form Card -->
        <div
            class="space-y-6 rounded-lg border border-gray-200 bg-white p-6 shadow-sm dark:border-gray-800 dark:bg-gray-900"
        >
            <!-- Name Field -->
            <div>
                <Label
                    for="name"
                    class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-300"
                >
                    Account Name <span class="text-red-500">*</span>
                </Label>
                <Input
                    id="name"
                    v-model="form.name"
                    type="text"
                    placeholder="e.g., Bank BCA, Cash Wallet"
                    class="w-full rounded-lg border-gray-200 bg-gray-50 dark:border-gray-700 dark:bg-gray-800"
                />
                <p
                    v-if="form.errors.name"
                    class="mt-2 flex items-center space-x-2 text-sm text-red-600 dark:text-red-400"
                >
                    <AlertCircle class="h-4 w-4" />
                    <span>{{ form.errors.name }}</span>
                </p>
            </div>

            <!-- Type Field -->
            <div>
                <Label
                    for="type"
                    class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-300"
                >
                    Account Type <span class="text-red-500">*</span>
                </Label>
                <select
                    id="type"
                    v-model="form.type"
                    class="w-full rounded-lg border border-gray-200 bg-gray-50 px-4 py-2 text-gray-900 transition-all focus:border-transparent focus:ring-2 focus:ring-indigo-500 dark:border-gray-700 dark:bg-gray-800 dark:text-white"
                >
                    <option value="">-- Select Account Type --</option>
                    <option
                        v-for="type in accountTypes"
                        :key="type.value"
                        :value="type.value"
                    >
                        {{ type.label }}
                    </option>
                </select>
                <p
                    v-if="form.errors.type"
                    class="mt-2 flex items-center space-x-2 text-sm text-red-600 dark:text-red-400"
                >
                    <AlertCircle class="h-4 w-4" />
                    <span>{{ form.errors.type }}</span>
                </p>
            </div>

            <!-- Initial Balance Field -->
            <div>
                <Label
                    for="initial_balance"
                    class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-300"
                >
                    Initial Balance (Rp) <span class="text-red-500">*</span>
                </Label>
                <Input
                    id="initial_balance"
                    v-model="form.initial_balance"
                    type="number"
                    placeholder="0"
                    min="0"
                    class="w-full rounded-lg border-gray-200 bg-gray-50 dark:border-gray-700 dark:bg-gray-800"
                />
                <p
                    v-if="form.errors.initial_balance"
                    class="mt-2 flex items-center space-x-2 text-sm text-red-600 dark:text-red-400"
                >
                    <AlertCircle class="h-4 w-4" />
                    <span>{{ form.errors.initial_balance }}</span>
                </p>
            </div>

            <!-- Description Field -->
            <div>
                <Label
                    for="description"
                    class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-300"
                >
                    Description (Optional)
                </Label>
                <textarea
                    id="description"
                    v-model="form.description"
                    placeholder="Add notes about this account..."
                    rows="3"
                    class="w-full resize-none rounded-lg border border-gray-200 bg-gray-50 px-4 py-2 text-gray-900 transition-all focus:border-transparent focus:ring-2 focus:ring-indigo-500 dark:border-gray-700 dark:bg-gray-800 dark:text-white"
                ></textarea>
                <p
                    v-if="form.errors.description"
                    class="mt-2 flex items-center space-x-2 text-sm text-red-600 dark:text-red-400"
                >
                    <AlertCircle class="h-4 w-4" />
                    <span>{{ form.errors.description }}</span>
                </p>
            </div>

            <!-- Active Status -->
            <div class="flex items-center space-x-3">
                <input
                    id="is_active"
                    v-model="form.is_active"
                    type="checkbox"
                    class="h-4 w-4 cursor-pointer rounded border-gray-300 text-indigo-600 focus:ring-indigo-500"
                />
                <Label
                    for="is_active"
                    class="cursor-pointer text-sm font-medium text-gray-700 dark:text-gray-300"
                >
                    Account is active
                </Label>
            </div>

            <!-- Buttons -->
            <div
                class="flex items-center justify-end space-x-3 border-t border-gray-200 pt-4 dark:border-gray-700"
            >
                <Link :href="route('financial-accounts.index')">
                    <Button variant="outline">Cancel</Button>
                </Link>
                <Button
                    @click="submit"
                    :disabled="
                        isSubmitting ||
                        !form.name ||
                        !form.type ||
                        !form.initial_balance
                    "
                    class="bg-indigo-600 text-white hover:bg-indigo-700 disabled:cursor-not-allowed disabled:opacity-50"
                >
                    {{ isSubmitting ? 'Creating...' : 'Create Account' }}
                </Button>
            </div>
        </div>
    </div>
</template>
