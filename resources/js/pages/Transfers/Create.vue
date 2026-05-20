<script setup lang="ts">
import { Head, Link, useForm } from '@inertiajs/vue3';
import { computed } from 'vue';
import {
    ArrowLeft,
    ArrowRightLeft,
    Save,
    Wallet,
    ArrowRight,
    AlertCircle,
    Info,
} from 'lucide-vue-next';

import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';

defineOptions({
    layout: {
        breadcrumbs: [
            { title: 'Dashboard', href: '/dashboard' },
            { title: 'Transfer', href: '/transfers' },
            { title: 'Tambah Transfer', href: '/transfers/create' },
        ],
    },
});

interface Account {
    id: number;
    name: string;
    type: string;
    current_balance: number;
}

interface Props {
    accounts: Account[];
}

const props = defineProps<Props>();

const form = useForm({
    from_account_id: '',
    to_account_id: '',
    amount: '',
    transfer_date: new Date().toISOString().split('T')[0],
    description: '',
});

const fromAccount = computed(() => {
    return props.accounts.find((account) => account.id === Number(form.from_account_id || 0));
});

const toAccount = computed(() => {
    return props.accounts.find((account) => account.id === Number(form.to_account_id || 0));
});

const toAccountOptions = computed(() => {
    return props.accounts.filter((account) => account.id !== Number(form.from_account_id || 0));
});

const hasEnoughBalance = computed(() => {
    if (!fromAccount.value || !form.amount) return true;

    return Number(fromAccount.value.current_balance || 0) >= Number(form.amount || 0);
});

const balanceAfterTransfer = computed(() => {
    if (!fromAccount.value) return 0;

    return Number(fromAccount.value.current_balance || 0) - Number(form.amount || 0);
});

const receivedBalanceAfterTransfer = computed(() => {
    if (!toAccount.value) return 0;

    return Number(toAccount.value.current_balance || 0) + Number(form.amount || 0);
});

const formatCurrency = (value: number | string | null | undefined) => {
    return new Intl.NumberFormat('id-ID', {
        style: 'currency',
        currency: 'IDR',
        minimumFractionDigits: 0,
        maximumFractionDigits: 0,
    }).format(Number(value || 0));
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

const submit = () => {
    form.post('/transfers', {
        preserveScroll: true,
    });
};
</script>

<template>
    <Head title="Tambah Transfer - Loovie Apps" />

    <div class="space-y-6 p-4 sm:p-6 lg:p-8">
        <!-- Header -->
        <div>
            <Link
                href="/transfers"
                class="mb-3 inline-flex items-center text-sm font-medium text-indigo-600 hover:text-indigo-700"
            >
                <ArrowLeft class="mr-2 h-4 w-4" />
                Kembali ke Transfer
            </Link>

            <h1 class="text-2xl font-bold text-gray-900 sm:text-3xl dark:text-white">
                Tambah Transfer Antar Akun
            </h1>

            <p class="mt-1 text-sm text-gray-600 dark:text-gray-400">
                Pindahkan saldo dari satu akun ke akun lainnya.
            </p>
        </div>

        <div
            v-if="accounts.length < 2"
            class="rounded-xl border border-yellow-200 bg-yellow-50 p-4 text-sm text-yellow-800 dark:border-yellow-900 dark:bg-yellow-950/40 dark:text-yellow-200"
        >
            <p class="font-semibold">
                Minimal butuh 2 akun aktif
            </p>
            <p class="mt-1">
                Buat minimal dua akun keuangan aktif sebelum melakukan transfer.
            </p>

            <Link
                href="/financial-accounts/create"
                class="mt-3 inline-flex rounded-lg bg-indigo-600 px-3 py-2 text-xs font-medium text-white hover:bg-indigo-700"
            >
                Tambah Akun
            </Link>
        </div>

        <div class="grid grid-cols-1 gap-6 lg:grid-cols-3">
            <!-- Form -->
            <div class="lg:col-span-2">
                <div class="space-y-6 rounded-2xl border border-gray-200 bg-white p-6 shadow-sm dark:border-gray-800 dark:bg-gray-900">
                    <!-- From Account -->
                    <div>
                        <label class="mb-2 block text-sm font-semibold">
                            Akun Sumber
                            <span class="text-red-500">*</span>
                        </label>

                        <select
                            v-model="form.from_account_id"
                            class="w-full rounded-lg border border-gray-200 bg-gray-50 px-4 py-2 text-gray-900 focus:ring-2 focus:ring-indigo-500 dark:border-gray-700 dark:bg-gray-800 dark:text-white"
                        >
                            <option value="">Pilih akun sumber</option>
                            <option
                                v-for="account in accounts"
                                :key="account.id"
                                :value="String(account.id)"
                            >
                                {{ account.name }} - {{ formatCurrency(account.current_balance) }}
                            </option>
                        </select>

                        <p
                            v-if="form.errors.from_account_id"
                            class="mt-2 flex items-center gap-2 text-sm text-red-600"
                        >
                            <AlertCircle class="h-4 w-4" />
                            {{ form.errors.from_account_id }}
                        </p>
                    </div>

                    <!-- To Account -->
                    <div>
                        <label class="mb-2 block text-sm font-semibold">
                            Akun Tujuan
                            <span class="text-red-500">*</span>
                        </label>

                        <select
                            v-model="form.to_account_id"
                            class="w-full rounded-lg border border-gray-200 bg-gray-50 px-4 py-2 text-gray-900 focus:ring-2 focus:ring-indigo-500 dark:border-gray-700 dark:bg-gray-800 dark:text-white"
                        >
                            <option value="">Pilih akun tujuan</option>
                            <option
                                v-for="account in toAccountOptions"
                                :key="account.id"
                                :value="String(account.id)"
                            >
                                {{ account.name }} - {{ formatCurrency(account.current_balance) }}
                            </option>
                        </select>

                        <p
                            v-if="form.errors.to_account_id"
                            class="mt-2 flex items-center gap-2 text-sm text-red-600"
                        >
                            <AlertCircle class="h-4 w-4" />
                            {{ form.errors.to_account_id }}
                        </p>
                    </div>

                    <!-- Amount -->
                    <div>
                        <label class="mb-2 block text-sm font-semibold">
                            Nominal Transfer
                            <span class="text-red-500">*</span>
                        </label>

                        <div class="relative">
                            <span class="absolute left-4 top-3 text-gray-500">
                                Rp
                            </span>

                            <Input
                                v-model="form.amount"
                                type="number"
                                min="1"
                                step="1000"
                                placeholder="0"
                                class="rounded-lg border-gray-200 bg-gray-50 pl-10 dark:border-gray-700 dark:bg-gray-800"
                            />
                        </div>

                        <p
                            v-if="!hasEnoughBalance"
                            class="mt-2 flex items-center gap-2 text-sm text-red-600"
                        >
                            <AlertCircle class="h-4 w-4" />
                            Saldo akun sumber tidak mencukupi.
                        </p>

                        <p
                            v-if="form.errors.amount"
                            class="mt-2 flex items-center gap-2 text-sm text-red-600"
                        >
                            <AlertCircle class="h-4 w-4" />
                            {{ form.errors.amount }}
                        </p>
                    </div>

                    <!-- Date -->
                    <div>
                        <label class="mb-2 block text-sm font-semibold">
                            Tanggal Transfer
                            <span class="text-red-500">*</span>
                        </label>

                        <Input
                            v-model="form.transfer_date"
                            type="date"
                            class="w-full rounded-lg border-gray-200 bg-gray-50 dark:border-gray-700 dark:bg-gray-800"
                        />

                        <p
                            v-if="form.errors.transfer_date"
                            class="mt-2 flex items-center gap-2 text-sm text-red-600"
                        >
                            <AlertCircle class="h-4 w-4" />
                            {{ form.errors.transfer_date }}
                        </p>
                    </div>

                    <!-- Description -->
                    <div>
                        <label class="mb-2 block text-sm font-semibold">
                            Deskripsi
                        </label>

                        <textarea
                            v-model="form.description"
                            rows="3"
                            placeholder="Contoh: top up e-wallet, pindah saldo tabungan..."
                            class="w-full rounded-lg border border-gray-200 bg-gray-50 px-4 py-2 text-gray-900 focus:ring-2 focus:ring-indigo-500 dark:border-gray-700 dark:bg-gray-800 dark:text-white"
                        ></textarea>

                        <p
                            v-if="form.errors.description"
                            class="mt-2 flex items-center gap-2 text-sm text-red-600"
                        >
                            <AlertCircle class="h-4 w-4" />
                            {{ form.errors.description }}
                        </p>
                    </div>

                    <!-- Actions -->
                    <div class="flex flex-col gap-3 border-t border-gray-200 pt-6 sm:flex-row dark:border-gray-700">
                        <Link href="/transfers" class="flex-1">
                            <Button variant="outline" class="w-full" type="button">
                                Batal
                            </Button>
                        </Link>

                        <Button
                            type="button"
                            :disabled="form.processing || !hasEnoughBalance || accounts.length < 2"
                            class="flex-1 bg-indigo-600 text-white hover:bg-indigo-700 disabled:cursor-not-allowed disabled:bg-slate-400"
                            @click="submit"
                        >
                            <Save class="mr-2 h-4 w-4" />
                            {{ form.processing ? 'Menyimpan...' : 'Simpan Transfer' }}
                        </Button>
                    </div>
                </div>
            </div>

            <!-- Sidebar -->
            <div class="space-y-6 lg:col-span-1">
                <div class="rounded-2xl border border-gray-200 bg-white p-6 shadow-sm dark:border-gray-800 dark:bg-gray-900">
                    <div class="mb-4 flex items-center gap-2">
                        <ArrowRightLeft class="h-5 w-5 text-indigo-500" />
                        <p class="text-sm font-semibold">
                            Preview Transfer
                        </p>
                    </div>

                    <div v-if="fromAccount || toAccount || form.amount" class="space-y-4">
                        <div class="rounded-xl bg-red-50 p-4 dark:bg-red-950/30">
                            <p class="text-xs text-red-700 dark:text-red-300">
                                Akun Sumber
                            </p>
                            <p class="mt-1 font-semibold">
                                {{ fromAccount?.name || '-' }}
                            </p>
                            <p class="mt-1 text-xs text-muted-foreground">
                                {{ fromAccount ? getAccountTypeLabel(fromAccount.type) : '-' }}
                            </p>
                            <p class="mt-2 text-sm">
                                Saldo setelah transfer:
                                <strong>{{ formatCurrency(balanceAfterTransfer) }}</strong>
                            </p>
                        </div>

                        <div class="flex justify-center">
                            <div class="flex h-10 w-10 items-center justify-center rounded-full bg-indigo-100 text-indigo-600 dark:bg-indigo-950 dark:text-indigo-300">
                                <ArrowRight class="h-5 w-5" />
                            </div>
                        </div>

                        <div class="rounded-xl bg-emerald-50 p-4 dark:bg-emerald-950/30">
                            <p class="text-xs text-emerald-700 dark:text-emerald-300">
                                Akun Tujuan
                            </p>
                            <p class="mt-1 font-semibold">
                                {{ toAccount?.name || '-' }}
                            </p>
                            <p class="mt-1 text-xs text-muted-foreground">
                                {{ toAccount ? getAccountTypeLabel(toAccount.type) : '-' }}
                            </p>
                            <p class="mt-2 text-sm">
                                Saldo setelah transfer:
                                <strong>{{ formatCurrency(receivedBalanceAfterTransfer) }}</strong>
                            </p>
                        </div>

                        <div class="rounded-xl bg-muted/40 p-4">
                            <p class="text-xs text-muted-foreground">
                                Nominal
                            </p>
                            <p class="mt-1 text-2xl font-bold text-indigo-600 dark:text-indigo-400">
                                {{ formatCurrency(form.amount) }}
                            </p>
                        </div>
                    </div>

                    <div v-else class="text-center">
                        <Wallet class="mx-auto h-10 w-10 text-gray-400" />
                        <p class="mt-3 font-semibold">
                            Preview masih kosong
                        </p>
                        <p class="mt-1 text-sm text-muted-foreground">
                            Pilih akun dan nominal untuk melihat preview.
                        </p>
                    </div>
                </div>

                <div class="rounded-2xl border border-blue-200 bg-blue-50 p-4 shadow-sm dark:border-blue-800 dark:bg-blue-900/20">
                    <div class="flex gap-3">
                        <Info class="mt-0.5 h-5 w-5 shrink-0 text-blue-600 dark:text-blue-400" />
                        <div>
                            <p class="text-sm font-medium text-blue-900 dark:text-blue-400">
                                Info Transfer
                            </p>
                            <p class="mt-1 text-xs text-blue-800 dark:text-blue-300">
                                Transfer hanya memindahkan saldo antar akun dan tidak masuk ke laporan pemasukan/pengeluaran.
                            </p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>
