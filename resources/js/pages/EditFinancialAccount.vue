<script setup lang="ts">
import { Head, Link, useForm } from '@inertiajs/vue3';
import { computed, ref } from 'vue';
import {
    ArrowLeft,
    AlertCircle,
    Save,
    Wallet,
    Landmark,
    Smartphone,
    TrendingUp,
    CreditCard,
    Banknote,
    CheckCircle2,
    Info,
    RefreshCcw,
} from 'lucide-vue-next';

import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';

defineOptions({
    layout: {
        breadcrumbs: [
            { title: 'Dashboard', href: '/dashboard' },
            { title: 'Akun Keuangan', href: '/financial-accounts' },
            { title: 'Edit Akun', href: '/financial-accounts/edit' },
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
}

interface Props {
    account: Account;
}

const props = defineProps<Props>();

const accountTypes = [
    {
        value: 'cash',
        label: 'Cash / Tunai',
        description: 'Uang tunai yang kamu pegang langsung.',
        icon: Banknote,
    },
    {
        value: 'bank',
        label: 'Bank',
        description: 'Rekening bank seperti BCA, BRI, Mandiri, dan lainnya.',
        icon: Landmark,
    },
    {
        value: 'digital_wallet',
        label: 'E-Wallet',
        description: 'Dompet digital seperti Dana, GoPay, OVO, ShopeePay.',
        icon: Smartphone,
    },
    {
        value: 'investment',
        label: 'Investasi',
        description: 'Akun investasi, reksadana, saham, atau aset lainnya.',
        icon: TrendingUp,
    },
    {
        value: 'credit_card',
        label: 'Kartu Kredit',
        description: 'Akun kartu kredit atau limit pembayaran.',
        icon: CreditCard,
    },
];

const form = useForm({
    name: props.account.name,
    type: props.account.type,
    initial_balance: String(props.account.initial_balance),
    description: props.account.description || '',
    is_active: props.account.is_active,
});

const isSubmitting = ref(false);

const selectedType = computed(() => {
    return accountTypes.find((type) => type.value === form.type);
});

const hasPreview = computed(() => {
    return Boolean(form.name || form.type || form.initial_balance || form.description);
});

const formatCurrency = (value: number | string | null | undefined) => {
    return new Intl.NumberFormat('id-ID', {
        style: 'currency',
        currency: 'IDR',
        minimumFractionDigits: 0,
        maximumFractionDigits: 0,
    }).format(Number(value || 0));
};

const setAccountType = (type: string) => {
    form.type = type;
};

const submit = () => {
    isSubmitting.value = true;

    form.put(`/financial-accounts/${props.account.id}`, {
        preserveScroll: true,
        onFinish: () => {
            isSubmitting.value = false;
        },
    });
};
</script>

<template>
    <Head title="Edit Akun Keuangan - Loovie Apps" />

    <div class="space-y-6 p-4 sm:p-6 lg:p-8">
        <!-- Header -->
        <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
            <div>
                <Link
                    href="/financial-accounts"
                    class="mb-3 inline-flex items-center text-sm font-medium text-indigo-600 hover:text-indigo-700"
                >
                    <ArrowLeft class="mr-2 h-4 w-4" />
                    Kembali ke Akun Keuangan
                </Link>

                <h1 class="text-2xl font-bold text-gray-900 sm:text-3xl dark:text-white">
                    Edit Akun Keuangan
                </h1>

                <p class="mt-1 text-sm text-gray-600 dark:text-gray-400">
                    Perbarui informasi akun keuangan yang sudah dibuat.
                </p>
            </div>
        </div>

        <div class="grid grid-cols-1 gap-6 lg:grid-cols-3">
            <!-- Form -->
            <div class="lg:col-span-2">
                <div
                    class="space-y-6 rounded-2xl border border-gray-200 bg-white p-6 shadow-sm dark:border-gray-800 dark:bg-gray-900"
                >
                    <!-- Name Field -->
                    <div>
                        <Label
                            for="name"
                            class="mb-2 block text-sm font-semibold text-gray-900 dark:text-white"
                        >
                            Nama Akun
                            <span class="text-red-500">*</span>
                        </Label>

                        <Input
                            id="name"
                            v-model="form.name"
                            type="text"
                            placeholder="Contoh: Bank BCA, Cash Wallet, Dana"
                            class="w-full rounded-lg border-gray-200 bg-gray-50 dark:border-gray-700 dark:bg-gray-800"
                        />

                        <p
                            v-if="form.errors.name"
                            class="mt-2 flex items-center gap-2 text-sm text-red-600 dark:text-red-400"
                        >
                            <AlertCircle class="h-4 w-4" />
                            <span>{{ form.errors.name }}</span>
                        </p>
                    </div>

                    <!-- Type Field -->
                    <div>
                        <Label class="mb-3 block text-sm font-semibold text-gray-900 dark:text-white">
                            Tipe Akun
                            <span class="text-red-500">*</span>
                        </Label>

                        <div class="grid grid-cols-1 gap-3 md:grid-cols-2">
                            <button
                                v-for="type in accountTypes"
                                :key="type.value"
                                type="button"
                                :class="[
                                    'rounded-xl border p-4 text-left transition hover:border-indigo-300 hover:bg-indigo-50 dark:hover:border-indigo-800 dark:hover:bg-indigo-950/30',
                                    form.type === type.value
                                        ? 'border-2 border-indigo-500 bg-indigo-50 dark:bg-indigo-950/40'
                                        : 'border-gray-200 bg-gray-50 dark:border-gray-700 dark:bg-gray-800',
                                ]"
                                @click="setAccountType(type.value)"
                            >
                                <div class="flex items-start gap-3">
                                    <div
                                        :class="[
                                            'flex h-10 w-10 shrink-0 items-center justify-center rounded-xl',
                                            form.type === type.value
                                                ? 'bg-indigo-600 text-white'
                                                : 'bg-white text-gray-600 dark:bg-gray-900 dark:text-gray-300',
                                        ]"
                                    >
                                        <component :is="type.icon" class="h-5 w-5" />
                                    </div>

                                    <div class="min-w-0">
                                        <p class="font-semibold text-gray-900 dark:text-white">
                                            {{ type.label }}
                                        </p>

                                        <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">
                                            {{ type.description }}
                                        </p>
                                    </div>
                                </div>
                            </button>
                        </div>

                        <p
                            v-if="form.errors.type"
                            class="mt-2 flex items-center gap-2 text-sm text-red-600 dark:text-red-400"
                        >
                            <AlertCircle class="h-4 w-4" />
                            <span>{{ form.errors.type }}</span>
                        </p>
                    </div>

                    <!-- Initial Balance Field -->
                    <div>
                        <Label
                            for="initial_balance"
                            class="mb-2 block text-sm font-semibold text-gray-900 dark:text-white"
                        >
                            Saldo Awal
                            <span class="text-red-500">*</span>
                        </Label>

                        <div class="relative">
                            <span class="absolute left-4 top-3 text-gray-500 dark:text-gray-400">
                                Rp
                            </span>

                            <Input
                                id="initial_balance"
                                v-model="form.initial_balance"
                                type="number"
                                placeholder="0"
                                min="0"
                                step="1000"
                                class="w-full rounded-lg border-gray-200 bg-gray-50 pl-10 dark:border-gray-700 dark:bg-gray-800"
                            />
                        </div>

                        <div
                            class="mt-2 rounded-lg border border-blue-200 bg-blue-50 px-3 py-2 text-sm text-blue-700 dark:border-blue-900 dark:bg-blue-950/40 dark:text-blue-200"
                        >
                            Saldo sekarang:
                            <strong>{{ formatCurrency(account.current_balance) }}</strong>
                        </div>

                        <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">
                            Mengubah saldo awal dapat memengaruhi perhitungan saldo akun.
                        </p>

                        <p
                            v-if="form.errors.initial_balance"
                            class="mt-2 flex items-center gap-2 text-sm text-red-600 dark:text-red-400"
                        >
                            <AlertCircle class="h-4 w-4" />
                            <span>{{ form.errors.initial_balance }}</span>
                        </p>
                    </div>

                    <!-- Description Field -->
                    <div>
                        <Label
                            for="description"
                            class="mb-2 block text-sm font-semibold text-gray-900 dark:text-white"
                        >
                            Deskripsi
                        </Label>

                        <textarea
                            id="description"
                            v-model="form.description"
                            placeholder="Contoh: rekening utama untuk gaji, dompet harian, akun tabungan..."
                            rows="3"
                            class="w-full resize-none rounded-lg border border-gray-200 bg-gray-50 px-4 py-2 text-gray-900 transition-all focus:border-transparent focus:ring-2 focus:ring-indigo-500 dark:border-gray-700 dark:bg-gray-800 dark:text-white"
                        ></textarea>

                        <p
                            v-if="form.errors.description"
                            class="mt-2 flex items-center gap-2 text-sm text-red-600 dark:text-red-400"
                        >
                            <AlertCircle class="h-4 w-4" />
                            <span>{{ form.errors.description }}</span>
                        </p>
                    </div>

                    <!-- Active Status -->
                    <div
                        class="rounded-xl border border-gray-200 bg-gray-50 p-4 dark:border-gray-700 dark:bg-gray-800"
                    >
                        <label
                            for="is_active"
                            class="flex cursor-pointer items-start gap-3"
                        >
                            <input
                                id="is_active"
                                v-model="form.is_active"
                                type="checkbox"
                                class="mt-1 h-4 w-4 cursor-pointer rounded border-gray-300 text-indigo-600 focus:ring-indigo-500"
                            />

                            <div>
                                <p class="text-sm font-semibold text-gray-900 dark:text-white">
                                    Akun aktif
                                </p>
                                <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">
                                    Akun aktif akan dihitung dalam total saldo dan bisa dipilih saat membuat transaksi.
                                </p>
                            </div>
                        </label>
                    </div>

                    <!-- Buttons -->
                    <div class="flex flex-col gap-3 border-t border-gray-200 pt-6 sm:flex-row dark:border-gray-700">
                        <Link href="/financial-accounts" class="flex-1">
                            <Button
                                variant="outline"
                                class="w-full"
                                type="button"
                            >
                                Batal
                            </Button>
                        </Link>

                        <Button
                            type="button"
                            :disabled="isSubmitting || form.processing"
                            class="flex-1 bg-indigo-600 text-white hover:bg-indigo-700 disabled:cursor-not-allowed disabled:bg-slate-400"
                            @click="submit"
                        >
                            <Save class="mr-2 h-4 w-4" />
                            {{ isSubmitting || form.processing ? 'Menyimpan...' : 'Simpan Perubahan' }}
                        </Button>
                    </div>
                </div>
            </div>

            <!-- Sidebar -->
            <div class="space-y-6 lg:col-span-1">
                <!-- Preview -->
                <div
                    v-if="hasPreview"
                    class="rounded-2xl border border-gray-200 bg-white p-6 shadow-sm dark:border-gray-800 dark:bg-gray-900"
                >
                    <div class="mb-4 flex items-center gap-2">
                        <Wallet class="h-5 w-5 text-indigo-500" />
                        <p class="text-sm font-semibold text-gray-900 dark:text-white">
                            Preview Akun
                        </p>
                    </div>

                    <div class="flex items-start gap-3">
                        <div
                            class="flex h-12 w-12 shrink-0 items-center justify-center rounded-xl bg-indigo-100 text-indigo-600 dark:bg-indigo-950 dark:text-indigo-300"
                        >
                            <component
                                :is="selectedType?.icon || Wallet"
                                class="h-6 w-6"
                            />
                        </div>

                        <div class="min-w-0">
                            <p class="truncate font-semibold text-gray-900 dark:text-white">
                                {{ form.name || 'Nama Akun' }}
                            </p>

                            <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">
                                {{ selectedType?.label || 'Tipe belum dipilih' }}
                            </p>
                        </div>
                    </div>

                    <div class="mt-5 grid grid-cols-1 gap-3">
                        <div class="rounded-xl bg-gray-50 p-4 dark:bg-gray-800">
                            <p class="text-xs text-gray-500 dark:text-gray-400">
                                Saldo Awal
                            </p>

                            <p class="mt-1 text-2xl font-bold text-gray-900 dark:text-white">
                                {{ formatCurrency(form.initial_balance) }}
                            </p>
                        </div>

                        <div class="rounded-xl bg-gray-50 p-4 dark:bg-gray-800">
                            <p class="text-xs text-gray-500 dark:text-gray-400">
                                Saldo Sekarang
                            </p>

                            <p class="mt-1 text-2xl font-bold text-indigo-600 dark:text-indigo-400">
                                {{ formatCurrency(account.current_balance) }}
                            </p>
                        </div>
                    </div>

                    <div class="mt-4 flex items-center gap-2">
                        <span
                            :class="[
                                'inline-flex items-center gap-1 rounded-full px-3 py-1 text-xs font-medium',
                                form.is_active
                                    ? 'bg-emerald-100 text-emerald-700 dark:bg-emerald-950 dark:text-emerald-300'
                                    : 'bg-gray-100 text-gray-700 dark:bg-gray-800 dark:text-gray-300',
                            ]"
                        >
                            <CheckCircle2 v-if="form.is_active" class="h-3 w-3" />
                            {{ form.is_active ? 'Aktif' : 'Nonaktif' }}
                        </span>
                    </div>

                    <p
                        v-if="form.description"
                        class="mt-4 text-sm text-gray-600 dark:text-gray-400"
                    >
                        {{ form.description }}
                    </p>
                </div>

                <!-- Info -->
                <div
                    class="rounded-2xl border border-blue-200 bg-blue-50 p-4 shadow-sm dark:border-blue-800 dark:bg-blue-900/20"
                >
                    <div class="flex gap-3">
                        <Info class="mt-0.5 h-5 w-5 shrink-0 text-blue-600 dark:text-blue-400" />

                        <div>
                            <p class="text-sm font-medium text-blue-900 dark:text-blue-400">
                                Tips Edit Akun
                            </p>

                            <p class="mt-1 text-xs text-blue-800 dark:text-blue-300">
                                Perubahan pada akun dapat memengaruhi laporan, transaksi, dan total saldo.
                            </p>
                        </div>
                    </div>
                </div>

                <!-- Current Balance Info -->
                <div
                    class="rounded-2xl border border-indigo-200 bg-gradient-to-br from-indigo-50 to-indigo-100 p-6 shadow-sm dark:border-indigo-700 dark:from-indigo-900/20 dark:to-indigo-800/20"
                >
                    <div class="mb-4 flex items-center gap-2">
                        <RefreshCcw class="h-5 w-5 text-indigo-600 dark:text-indigo-400" />
                        <p class="text-sm font-semibold text-indigo-700 dark:text-indigo-400">
                            Informasi Saldo
                        </p>
                    </div>

                    <div class="space-y-3">
                        <div class="flex justify-between gap-3 text-sm">
                            <span class="text-gray-700 dark:text-gray-300">
                                Saldo Awal:
                            </span>

                            <span class="font-medium text-gray-900 dark:text-white">
                                {{ formatCurrency(account.initial_balance) }}
                            </span>
                        </div>

                        <div class="flex justify-between gap-3 text-sm">
                            <span class="text-gray-700 dark:text-gray-300">
                                Saldo Sekarang:
                            </span>

                            <span class="font-medium text-indigo-600 dark:text-indigo-400">
                                {{ formatCurrency(account.current_balance) }}
                            </span>
                        </div>

                        <div class="flex justify-between gap-3 border-t border-indigo-200 pt-3 text-sm dark:border-indigo-700">
                            <span class="text-gray-700 dark:text-gray-300">
                                Selisih:
                            </span>

                            <span
                                :class="[
                                    'font-medium',
                                    account.current_balance - account.initial_balance >= 0
                                        ? 'text-emerald-600 dark:text-emerald-400'
                                        : 'text-red-600 dark:text-red-400',
                                ]"
                            >
                                {{
                                    formatCurrency(
                                        account.current_balance - account.initial_balance,
                                    )
                                }}
                            </span>
                        </div>
                    </div>
                </div>

                <!-- Type Info -->
                <div
                    class="rounded-2xl border border-gray-200 bg-white p-6 shadow-sm dark:border-gray-800 dark:bg-gray-900"
                >
                    <h3 class="font-semibold text-gray-900 dark:text-white">
                        Tipe Akun
                    </h3>

                    <div class="mt-4 space-y-3">
                        <div
                            v-for="type in accountTypes"
                            :key="type.value"
                            class="flex items-start gap-3"
                        >
                            <div
                                class="flex h-8 w-8 shrink-0 items-center justify-center rounded-lg bg-gray-100 text-gray-600 dark:bg-gray-800 dark:text-gray-300"
                            >
                                <component :is="type.icon" class="h-4 w-4" />
                            </div>

                            <div>
                                <p class="text-sm font-medium text-gray-900 dark:text-white">
                                    {{ type.label }}
                                </p>
                                <p class="text-xs text-gray-500 dark:text-gray-400">
                                    {{ type.description }}
                                </p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>
