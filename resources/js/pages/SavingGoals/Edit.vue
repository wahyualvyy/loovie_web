<script setup lang="ts">
import { Head, Link, router, useForm } from '@inertiajs/vue3';
import { computed, ref } from 'vue';
import {
    ArrowLeft,
    Save,
    Target,
    AlertCircle,
    Calendar,
    PiggyBank,
    Info,
    Clock,
    Trash2,
} from 'lucide-vue-next';

import DeleteConfirmModal from '@/components/DeleteConfirmModal.vue';
import EmptyState from '@/components/EmptyState.vue';
import SavingGoalDepositModal from '@/components/SavingGoalDepositModal.vue';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';

defineOptions({
    layout: {
        breadcrumbs: [
            { title: 'Dashboard', href: '/dashboard' },
            { title: 'Target Tabungan', href: '/saving-goals' },
            { title: 'Edit Target', href: '/saving-goals/edit' },
        ],
    },
});

interface Account {
    id: number;
    name: string;
    type: string;
    current_balance: number;
}

interface Deposit {
    id: number;
    amount: number;
    deposit_date: string;
    description: string | null;
    financial_account?: {
        id: number;
        name: string;
        type: string;
        current_balance: number;
    };
}

interface SavingGoal {
    id: number;
    title: string;
    target_amount: number;
    current_amount: number;
    target_date: string | null;
    status: 'active' | 'completed' | 'cancelled';
    description: string | null;
    progress_percentage: number;
    remaining_amount: number;
    status_label: string;
    created_at: string;
    deposits?: Deposit[];
}

interface Props {
    goal: SavingGoal;
    accounts: Account[];
}

const props = defineProps<Props>();

const form = useForm({
    title: props.goal.title,
    target_amount: String(props.goal.target_amount),
    current_amount: String(props.goal.current_amount),
    target_date: props.goal.target_date || '',
    status: props.goal.status,
    description: props.goal.description || '',
});

const showDepositModal = ref(false);
const depositToDelete = ref<Deposit | null>(null);
const showDeleteDepositModal = ref(false);
const deleteDepositProcessing = ref(false);

const progressPercentage = computed(() => {
    const target = Number(form.target_amount || 0);
    const current = Number(form.current_amount || 0);

    if (target <= 0) return 0;

    return Math.min(Math.round((current / target) * 100), 100);
});

const remainingAmount = computed(() => {
    return Math.max(
        Number(form.target_amount || 0) - Number(form.current_amount || 0),
        0,
    );
});

const statusLabel = computed(() => {
    if (form.status === 'completed') return 'Selesai';
    if (form.status === 'cancelled') return 'Dibatalkan';

    return 'Aktif';
});

const depositList = computed(() => {
    return props.goal.deposits || [];
});

const deleteDepositName = computed(() => {
    if (!depositToDelete.value) return '';

    return `${formatCurrency(depositToDelete.value.amount)} - ${formatDate(
        depositToDelete.value.deposit_date,
    )}`;
});

const formatCurrency = (value: number | string | null | undefined) => {
    return new Intl.NumberFormat('id-ID', {
        style: 'currency',
        currency: 'IDR',
        minimumFractionDigits: 0,
        maximumFractionDigits: 0,
    }).format(Number(value || 0));
};

const formatDate = (date: string | null) => {
    if (!date) return '-';

    return new Date(date).toLocaleDateString('id-ID', {
        day: 'numeric',
        month: 'long',
        year: 'numeric',
    });
};

const openDepositModal = () => {
    showDepositModal.value = true;
};

const closeDepositModal = () => {
    showDepositModal.value = false;
};

const openDeleteDepositModal = (deposit: Deposit) => {
    depositToDelete.value = deposit;
    showDeleteDepositModal.value = true;
};

const closeDeleteDepositModal = () => {
    depositToDelete.value = null;
    showDeleteDepositModal.value = false;
};

const deleteDeposit = () => {
    if (!depositToDelete.value) return;

    const id = depositToDelete.value.id;

    showDeleteDepositModal.value = false;
    depositToDelete.value = null;
    deleteDepositProcessing.value = true;

    router.delete(`/saving-goal-deposits/${id}`, {
        preserveScroll: true,
        onFinish: () => {
            deleteDepositProcessing.value = false;
        },
    });
};

const submit = () => {
    form.put(`/saving-goals/${props.goal.id}`, {
        preserveScroll: true,
    });
};
</script>

<template>
    <Head title="Edit Target Tabungan - Loovie Apps" />

    <div class="space-y-6 p-4 sm:p-6 lg:p-8">
        <!-- Header -->
        <div>
            <Link
                href="/saving-goals"
                class="mb-3 inline-flex items-center text-sm font-medium text-indigo-600 hover:text-indigo-700"
            >
                <ArrowLeft class="mr-2 h-4 w-4" />
                Kembali ke Target Tabungan
            </Link>

            <h1 class="text-2xl font-bold text-gray-900 sm:text-3xl dark:text-white">
                Edit Target Tabungan
            </h1>

            <p class="mt-1 text-sm text-gray-600 dark:text-gray-400">
                Perbarui target tabungan, lakukan setoran, dan lihat riwayat setoran.
            </p>
        </div>

        <div class="grid grid-cols-1 gap-6 lg:grid-cols-3">
            <!-- Form -->
            <div class="lg:col-span-2">
                <div
                    class="space-y-6 rounded-2xl border border-gray-200 bg-white p-6 shadow-sm dark:border-gray-800 dark:bg-gray-900"
                >
                    <!-- Title -->
                    <div>
                        <label class="mb-2 block text-sm font-semibold">
                            Nama Target
                            <span class="text-red-500">*</span>
                        </label>

                        <Input
                            v-model="form.title"
                            type="text"
                            placeholder="Contoh: Beli Laptop, Dana Darurat, Liburan"
                            class="rounded-lg border-gray-200 bg-gray-50 dark:border-gray-700 dark:bg-gray-800"
                        />

                        <p
                            v-if="form.errors.title"
                            class="mt-2 flex items-center gap-2 text-sm text-red-600"
                        >
                            <AlertCircle class="h-4 w-4" />
                            {{ form.errors.title }}
                        </p>
                    </div>

                    <!-- Target Amount -->
                    <div>
                        <label class="mb-2 block text-sm font-semibold">
                            Nominal Target
                            <span class="text-red-500">*</span>
                        </label>

                        <div class="relative">
                            <span class="absolute left-4 top-3 text-gray-500">
                                Rp
                            </span>

                            <Input
                                v-model="form.target_amount"
                                type="number"
                                min="1"
                                step="1000"
                                placeholder="0"
                                class="rounded-lg border-gray-200 bg-gray-50 pl-10 dark:border-gray-700 dark:bg-gray-800"
                            />
                        </div>

                        <p
                            v-if="form.errors.target_amount"
                            class="mt-2 flex items-center gap-2 text-sm text-red-600"
                        >
                            <AlertCircle class="h-4 w-4" />
                            {{ form.errors.target_amount }}
                        </p>
                    </div>

                    <!-- Current Amount -->
                    <div>
                        <label class="mb-2 block text-sm font-semibold">
                            Nominal Terkumpul
                            <span class="text-red-500">*</span>
                        </label>

                        <div class="relative">
                            <span class="absolute left-4 top-3 text-gray-500">
                                Rp
                            </span>

                            <Input
                                v-model="form.current_amount"
                                type="number"
                                min="0"
                                step="1000"
                                placeholder="0"
                                class="rounded-lg border-gray-200 bg-gray-50 pl-10 dark:border-gray-700 dark:bg-gray-800"
                            />
                        </div>

                        <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">
                            Nominal ini bisa berubah otomatis dari fitur setoran.
                        </p>

                        <p
                            v-if="form.errors.current_amount"
                            class="mt-2 flex items-center gap-2 text-sm text-red-600"
                        >
                            <AlertCircle class="h-4 w-4" />
                            {{ form.errors.current_amount }}
                        </p>
                    </div>

                    <!-- Target Date -->
                    <div>
                        <label class="mb-2 block text-sm font-semibold">
                            Tanggal Target
                        </label>

                        <Input
                            v-model="form.target_date"
                            type="date"
                            class="rounded-lg border-gray-200 bg-gray-50 dark:border-gray-700 dark:bg-gray-800"
                        />

                        <p
                            v-if="form.errors.target_date"
                            class="mt-2 flex items-center gap-2 text-sm text-red-600"
                        >
                            <AlertCircle class="h-4 w-4" />
                            {{ form.errors.target_date }}
                        </p>
                    </div>

                    <!-- Status -->
                    <div>
                        <label class="mb-2 block text-sm font-semibold">
                            Status
                            <span class="text-red-500">*</span>
                        </label>

                        <select
                            v-model="form.status"
                            class="w-full rounded-lg border border-gray-200 bg-gray-50 px-4 py-2 text-gray-900 focus:ring-2 focus:ring-indigo-500 dark:border-gray-700 dark:bg-gray-800 dark:text-white"
                        >
                            <option value="active">Aktif</option>
                            <option value="completed">Selesai</option>
                            <option value="cancelled">Dibatalkan</option>
                        </select>

                        <p
                            v-if="form.errors.status"
                            class="mt-2 flex items-center gap-2 text-sm text-red-600"
                        >
                            <AlertCircle class="h-4 w-4" />
                            {{ form.errors.status }}
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
                            placeholder="Contoh: target untuk upgrade laptop kerja..."
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
                        <Link href="/saving-goals" class="flex-1">
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
                            variant="outline"
                            class="flex-1 border-purple-200 text-purple-600 hover:bg-purple-50 dark:border-purple-900 dark:hover:bg-purple-950/40"
                            :disabled="goal.status === 'cancelled'"
                            @click="openDepositModal"
                        >
                            <PiggyBank class="mr-2 h-4 w-4" />
                            Setor Target
                        </Button>

                        <Button
                            type="button"
                            :disabled="form.processing"
                            class="flex-1 bg-indigo-600 text-white hover:bg-indigo-700 disabled:cursor-not-allowed disabled:bg-slate-400"
                            @click="submit"
                        >
                            <Save class="mr-2 h-4 w-4" />
                            {{ form.processing ? 'Menyimpan...' : 'Simpan Perubahan' }}
                        </Button>
                    </div>
                </div>
            </div>

            <!-- Sidebar -->
            <div class="space-y-6 lg:col-span-1">
                <!-- Preview -->
                <div
                    class="rounded-2xl border border-gray-200 bg-white p-6 shadow-sm dark:border-gray-800 dark:bg-gray-900"
                >
                    <div class="mb-4 flex items-center gap-2">
                        <Target class="h-5 w-5 text-indigo-500" />
                        <p class="text-sm font-semibold">
                            Preview Target
                        </p>
                    </div>

                    <div class="space-y-4">
                        <div>
                            <p class="text-xs text-muted-foreground">
                                Nama Target
                            </p>
                            <p class="mt-1 font-semibold">
                                {{ form.title || 'Nama Target' }}
                            </p>
                        </div>

                        <div class="grid grid-cols-1 gap-3">
                            <div class="rounded-xl bg-muted/40 p-4">
                                <p class="text-xs text-muted-foreground">
                                    Target
                                </p>
                                <p class="mt-1 text-xl font-bold">
                                    {{ formatCurrency(form.target_amount) }}
                                </p>
                            </div>

                            <div class="rounded-xl bg-emerald-50 p-4 dark:bg-emerald-950/30">
                                <p class="text-xs text-emerald-700 dark:text-emerald-300">
                                    Terkumpul
                                </p>
                                <p class="mt-1 text-xl font-bold text-emerald-500">
                                    {{ formatCurrency(form.current_amount) }}
                                </p>
                            </div>

                            <div class="rounded-xl bg-red-50 p-4 dark:bg-red-950/30">
                                <p class="text-xs text-red-700 dark:text-red-300">
                                    Sisa
                                </p>
                                <p class="mt-1 text-xl font-bold text-red-500">
                                    {{ formatCurrency(remainingAmount) }}
                                </p>
                            </div>
                        </div>

                        <div>
                            <div class="mb-1 flex justify-between text-xs text-muted-foreground">
                                <span>Progress</span>
                                <span>{{ progressPercentage }}%</span>
                            </div>

                            <div class="h-3 overflow-hidden rounded-full bg-muted">
                                <div
                                    class="h-full rounded-full bg-indigo-500"
                                    :style="{ width: progressPercentage + '%' }"
                                ></div>
                            </div>
                        </div>

                        <div class="flex items-center gap-2 text-sm text-muted-foreground">
                            <Calendar class="h-4 w-4" />
                            {{ formatDate(form.target_date) }}
                        </div>

                        <div class="rounded-xl bg-muted/40 p-4">
                            <p class="text-xs text-muted-foreground">
                                Status
                            </p>
                            <p class="mt-1 font-semibold">
                                {{ statusLabel }}
                            </p>
                        </div>
                    </div>
                </div>

                <!-- Original Data -->
                <div
                    class="rounded-2xl border border-indigo-200 bg-gradient-to-br from-indigo-50 to-indigo-100 p-6 shadow-sm dark:border-indigo-700 dark:from-indigo-900/20 dark:to-indigo-800/20"
                >
                    <div class="mb-4 flex items-center gap-2">
                        <Clock class="h-5 w-5 text-indigo-600 dark:text-indigo-400" />
                        <p class="text-sm font-semibold text-indigo-700 dark:text-indigo-400">
                            Data Awal
                        </p>
                    </div>

                    <div class="space-y-3 text-sm">
                        <div class="flex justify-between gap-3">
                            <span class="text-gray-700 dark:text-gray-300">
                                Target:
                            </span>
                            <span class="font-medium">
                                {{ formatCurrency(goal.target_amount) }}
                            </span>
                        </div>

                        <div class="flex justify-between gap-3">
                            <span class="text-gray-700 dark:text-gray-300">
                                Terkumpul:
                            </span>
                            <span class="font-medium">
                                {{ formatCurrency(goal.current_amount) }}
                            </span>
                        </div>

                        <div class="flex justify-between gap-3">
                            <span class="text-gray-700 dark:text-gray-300">
                                Progress:
                            </span>
                            <span class="font-medium">
                                {{ goal.progress_percentage }}%
                            </span>
                        </div>

                        <div class="flex justify-between gap-3">
                            <span class="text-gray-700 dark:text-gray-300">
                                Status:
                            </span>
                            <span class="font-medium">
                                {{ goal.status_label }}
                            </span>
                        </div>
                    </div>
                </div>

                <!-- Info -->
                <div
                    class="rounded-2xl border border-blue-200 bg-blue-50 p-4 shadow-sm dark:border-blue-800 dark:bg-blue-900/20"
                >
                    <div class="flex gap-3">
                        <Info class="mt-0.5 h-5 w-5 shrink-0 text-blue-600 dark:text-blue-400" />
                        <div>
                            <p class="text-sm font-medium text-blue-900 dark:text-blue-400">
                                Info Edit Target
                            </p>
                            <p class="mt-1 text-xs text-blue-800 dark:text-blue-300">
                                Jika nominal terkumpul sama atau melebihi target,
                                status otomatis menjadi selesai.
                            </p>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Deposit History -->
        <div
            class="rounded-2xl border border-gray-200 bg-white p-6 shadow-sm dark:border-gray-800 dark:bg-gray-900"
        >
            <div class="mb-4 flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
                <div>
                    <h2 class="text-lg font-semibold text-gray-900 dark:text-white">
                        Riwayat Setoran
                    </h2>
                    <p class="text-sm text-gray-500 dark:text-gray-400">
                        Daftar setoran yang pernah masuk ke target ini.
                    </p>
                </div>

                <Button
                    type="button"
                    class="bg-purple-600 text-white hover:bg-purple-700"
                    :disabled="goal.status === 'cancelled'"
                    @click="openDepositModal"
                >
                    <PiggyBank class="mr-2 h-4 w-4" />
                    Setor
                </Button>
            </div>

            <EmptyState
                v-if="depositList.length === 0"
                :icon="PiggyBank"
                title="Belum ada setoran"
                description="Tambahkan setoran pertama untuk menaikkan progress target."
                action-label="Setor Target"
                button-type="button"
                @action="openDepositModal"
            />

            <div
                v-else
                class="overflow-hidden rounded-xl border dark:border-gray-800"
            >
                <!-- Desktop Table -->
                <div class="hidden overflow-x-auto md:block">
                    <table class="w-full">
                        <thead class="border-b bg-gray-50 dark:border-gray-800 dark:bg-gray-800">
                            <tr>
                                <th class="px-4 py-3 text-left text-sm font-semibold">
                                    Tanggal
                                </th>
                                <th class="px-4 py-3 text-left text-sm font-semibold">
                                    Akun
                                </th>
                                <th class="px-4 py-3 text-right text-sm font-semibold">
                                    Nominal
                                </th>
                                <th class="px-4 py-3 text-left text-sm font-semibold">
                                    Deskripsi
                                </th>
                                <th class="px-4 py-3 text-center text-sm font-semibold">
                                    Aksi
                                </th>
                            </tr>
                        </thead>

                        <tbody class="divide-y dark:divide-gray-800">
                            <tr
                                v-for="deposit in depositList"
                                :key="deposit.id"
                                class="hover:bg-gray-50 dark:hover:bg-gray-800/50"
                            >
                                <td class="px-4 py-3 text-sm">
                                    {{ formatDate(deposit.deposit_date) }}
                                </td>

                                <td class="px-4 py-3 text-sm">
                                    {{ deposit.financial_account?.name || '-' }}
                                </td>

                                <td class="px-4 py-3 text-right text-sm font-bold text-purple-600 dark:text-purple-400">
                                    {{ formatCurrency(deposit.amount) }}
                                </td>

                                <td class="px-4 py-3 text-sm text-muted-foreground">
                                    {{ deposit.description || '-' }}
                                </td>

                                <td class="px-4 py-3 text-center">
                                    <button
                                        type="button"
                                        class="inline-flex items-center gap-2 rounded-lg border border-red-200 px-3 py-2 text-xs text-red-600 transition hover:bg-red-50 dark:border-red-900 dark:hover:bg-red-950/40"
                                        @click="openDeleteDepositModal(deposit)"
                                    >
                                        <Trash2 class="h-4 w-4" />
                                        Batalkan
                                    </button>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <!-- Mobile Cards -->
                <div class="md:hidden">
                    <div
                        v-for="deposit in depositList"
                        :key="deposit.id"
                        class="border-b p-4 last:border-b-0 dark:border-gray-800"
                    >
                        <div class="flex items-start justify-between gap-3">
                            <div>
                                <p class="font-bold text-purple-600 dark:text-purple-400">
                                    {{ formatCurrency(deposit.amount) }}
                                </p>
                                <p class="mt-1 text-xs text-muted-foreground">
                                    {{ formatDate(deposit.deposit_date) }}
                                </p>
                                <p class="mt-1 text-sm">
                                    {{ deposit.financial_account?.name || '-' }}
                                </p>
                                <p class="mt-1 text-sm text-muted-foreground">
                                    {{ deposit.description || '-' }}
                                </p>
                            </div>

                            <button
                                type="button"
                                class="rounded-lg border border-red-200 px-2 py-2 text-red-600"
                                @click="openDeleteDepositModal(deposit)"
                            >
                                <Trash2 class="h-4 w-4" />
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <SavingGoalDepositModal
            :show="showDepositModal"
            :goal="goal"
            :accounts="accounts"
            @close="closeDepositModal"
        />

        <DeleteConfirmModal
            :show="showDeleteDepositModal"
            title="Batalkan Setoran?"
            description="Setoran akan dihapus, saldo akun dikembalikan, dan progress target dikurangi."
            :item-name="deleteDepositName"
            :processing="deleteDepositProcessing"
            confirm-label="Ya, Batalkan"
            @close="closeDeleteDepositModal"
            @confirm="deleteDeposit"
        />
    </div>
</template>
