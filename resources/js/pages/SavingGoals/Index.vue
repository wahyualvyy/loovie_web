<script setup lang="ts">
import { Head, Link, router, useForm } from '@inertiajs/vue3';
import { computed, ref } from 'vue';
import {
    CheckCircle2,
    Clock3,
    Edit2,
    PiggyBank,
    Plus,
    Search,
    Target,
    Trash2,
    Wallet,
    XCircle,
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
        ],
    },
});

interface SavingGoal {
    id: number;
    title: string;
    target_amount: number;
    current_amount: number;
    target_date: string | null;
    status: 'active' | 'completed' | 'cancelled';
    status_label: string;
    description: string | null;
    progress_percentage: number;
    remaining_amount: number;
    created_at: string;
}

interface Account {
    id: number;
    name: string;
    type: string;
    current_balance: number;
}

interface Props {
    goals: {
        data: SavingGoal[];
        total: number;
        per_page: number;
        current_page: number;
        last_page: number;
        from: number | null;
        to: number | null;
    };
    summary: {
        total_goals: number;
        active_goals: number;
        completed_goals: number;
        cancelled_goals: number;
        total_target: number;
        total_collected: number;
    };
    filters: {
        search: string | null;
        status: string | null;
    };
    accounts: Account[];
}

const props = defineProps<Props>();

const showDepositModal = ref(false);
const goalToDeposit = ref<SavingGoal | null>(null);

const showDeleteModal = ref(false);
const goalToDelete = ref<SavingGoal | null>(null);
const deleteProcessing = ref(false);

const form = useForm({
    search: props.filters.search || '',
    status: props.filters.status || '',
});

const totalRemaining = computed(() => {
    return Math.max(
        Number(props.summary.total_target || 0) -
            Number(props.summary.total_collected || 0),
        0,
    );
});

const overallProgress = computed(() => {
    const target = Number(props.summary.total_target || 0);
    const collected = Number(props.summary.total_collected || 0);

    if (target <= 0) return 0;

    return Math.min(Math.round((collected / target) * 100), 100);
});

const isFiltered = computed(() => {
    return Boolean(form.search || form.status);
});

const getDeleteItemName = computed(() => {
    if (!goalToDelete.value) return '';

    return `${goalToDelete.value.title} - ${formatCurrency(goalToDelete.value.target_amount)}`;
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

const applyFilters = () => {
    form.get('/saving-goals', {
        preserveScroll: true,
        preserveState: true,
    });
};

const resetFilters = () => {
    form.reset();

    router.get(
        '/saving-goals',
        {},
        {
            preserveScroll: true,
            preserveState: true,
        },
    );
};

const openDepositModal = (goal: SavingGoal) => {
    goalToDeposit.value = goal;
    showDepositModal.value = true;
};

const closeDepositModal = () => {
    goalToDeposit.value = null;
    showDepositModal.value = false;
};

const openDeleteModal = (goal: SavingGoal) => {
    goalToDelete.value = goal;
    showDeleteModal.value = true;
};

const closeDeleteModal = () => {
    goalToDelete.value = null;
    showDeleteModal.value = false;
};

const deleteGoal = () => {
    if (!goalToDelete.value) return;

    const id = goalToDelete.value.id;

    showDeleteModal.value = false;
    goalToDelete.value = null;
    deleteProcessing.value = true;

    router.delete(`/saving-goals/${id}`, {
        preserveScroll: true,
        onFinish: () => {
            deleteProcessing.value = false;
        },
    });
};

const getStatusClass = (status: string) => {
    if (status === 'completed') {
        return 'bg-emerald-100 text-emerald-700 dark:bg-emerald-950 dark:text-emerald-300';
    }

    if (status === 'cancelled') {
        return 'bg-red-100 text-red-700 dark:bg-red-950 dark:text-red-300';
    }

    return 'bg-blue-100 text-blue-700 dark:bg-blue-950 dark:text-blue-300';
};

const getStatusIcon = (status: string) => {
    if (status === 'completed') return CheckCircle2;
    if (status === 'cancelled') return XCircle;

    return Clock3;
};

const getProgressColor = (status: string, progress: number) => {
    if (status === 'completed') return 'bg-emerald-500';
    if (status === 'cancelled') return 'bg-red-500';
    if (progress >= 80) return 'bg-indigo-500';
    if (progress >= 50) return 'bg-blue-500';

    return 'bg-slate-500';
};
</script>

<template>
    <Head title="Target Tabungan - Loovie Apps" />

    <div class="space-y-6 p-4 sm:p-6 lg:p-8">
        <!-- Header -->
        <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
            <div>
                <h1 class="text-3xl font-bold text-gray-900 sm:text-4xl dark:text-white">
                    Target Tabungan
                </h1>

                <p class="mt-1 text-gray-600 dark:text-gray-400">
                    Kelola target seperti dana darurat, beli laptop, liburan,
                    atau tabungan lainnya.
                </p>
            </div>

            <Link href="/saving-goals/create">
                <Button class="bg-indigo-600 text-white hover:bg-indigo-700">
                    <Plus class="mr-2 h-4 w-4" />
                    Tambah Target
                </Button>
            </Link>
        </div>

        <!-- Summary -->
        <div class="grid grid-cols-1 gap-4 md:grid-cols-2 xl:grid-cols-4">
            <div class="rounded-xl border bg-card p-5 shadow-sm">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm text-muted-foreground">
                            Total Target
                        </p>
                        <p class="mt-2 text-2xl font-bold">
                            {{ summary.total_goals }}
                        </p>
                    </div>

                    <Target class="h-8 w-8 text-indigo-500" />
                </div>
            </div>

            <div class="rounded-xl border bg-card p-5 shadow-sm">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm text-muted-foreground">
                            Target Aktif
                        </p>
                        <p class="mt-2 text-2xl font-bold text-blue-500">
                            {{ summary.active_goals }}
                        </p>
                    </div>

                    <Clock3 class="h-8 w-8 text-blue-500" />
                </div>
            </div>

            <div class="rounded-xl border bg-card p-5 shadow-sm">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm text-muted-foreground">
                            Terkumpul
                        </p>
                        <p class="mt-2 text-2xl font-bold text-emerald-500">
                            {{ formatCurrency(summary.total_collected) }}
                        </p>
                    </div>

                    <PiggyBank class="h-8 w-8 text-emerald-500" />
                </div>
            </div>

            <div class="rounded-xl border bg-card p-5 shadow-sm">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm text-muted-foreground">
                            Sisa Target
                        </p>
                        <p class="mt-2 text-2xl font-bold text-red-500">
                            {{ formatCurrency(totalRemaining) }}
                        </p>
                    </div>

                    <Wallet class="h-8 w-8 text-red-500" />
                </div>
            </div>
        </div>

        <!-- Overall Progress -->
        <div
            v-if="summary.total_goals > 0"
            class="rounded-xl border bg-card p-5 shadow-sm"
        >
            <div class="mb-3 flex items-center justify-between">
                <div>
                    <h2 class="font-semibold">
                        Progress Semua Target
                    </h2>

                    <p class="text-sm text-muted-foreground">
                        {{ formatCurrency(summary.total_collected) }} dari
                        {{ formatCurrency(summary.total_target) }}
                    </p>
                </div>

                <span class="rounded-full bg-indigo-100 px-3 py-1 text-sm font-semibold text-indigo-700 dark:bg-indigo-950 dark:text-indigo-300">
                    {{ overallProgress }}%
                </span>
            </div>

            <div class="h-3 overflow-hidden rounded-full bg-muted">
                <div
                    class="h-full rounded-full bg-indigo-500 transition-all"
                    :style="{ width: overallProgress + '%' }"
                ></div>
            </div>
        </div>

        <!-- Filters -->
        <div class="space-y-4 rounded-xl border border-gray-200 bg-white p-4 shadow-sm dark:border-gray-800 dark:bg-gray-900">
            <div class="grid grid-cols-1 gap-4 md:grid-cols-2">
                <div class="relative">
                    <Search class="absolute left-3 top-3 h-5 w-5 text-gray-400" />

                    <Input
                        v-model="form.search"
                        type="text"
                        placeholder="Cari nama target atau deskripsi..."
                        class="rounded-lg border-gray-200 bg-gray-50 pl-10 dark:border-gray-700 dark:bg-gray-800"
                    />
                </div>

                <select
                    v-model="form.status"
                    class="rounded-lg border border-gray-200 bg-gray-50 px-4 py-2 text-gray-900 focus:ring-2 focus:ring-indigo-500 dark:border-gray-700 dark:bg-gray-800 dark:text-white"
                >
                    <option value="">Semua Status</option>
                    <option value="active">Aktif</option>
                    <option value="completed">Selesai</option>
                    <option value="cancelled">Dibatalkan</option>
                </select>
            </div>

            <div class="flex justify-end gap-2">
                <Button variant="outline" @click="resetFilters">
                    Reset
                </Button>

                <Button
                    class="bg-indigo-600 text-white hover:bg-indigo-700"
                    @click="applyFilters"
                >
                    Terapkan Filter
                </Button>
            </div>
        </div>

        <!-- Empty -->
        <EmptyState
            v-if="goals.data.length === 0"
            :icon="Target"
            :title="isFiltered ? 'Target tidak ditemukan' : 'Belum ada target tabungan'"
            :description="
                isFiltered
                    ? 'Tidak ada target tabungan yang cocok dengan filter.'
                    : 'Buat target tabungan pertama seperti dana darurat, beli laptop, atau liburan.'
            "
            :action-label="isFiltered ? 'Reset Filter' : 'Tambah Target'"
            :button-type="isFiltered ? 'button' : 'link'"
            :action-href="isFiltered ? '' : '/saving-goals/create'"
            @action="resetFilters"
        />

        <!-- Goals Grid -->
        <div
            v-else
            class="grid grid-cols-1 gap-4 md:grid-cols-2 xl:grid-cols-3"
        >
            <div
                v-for="goal in goals.data"
                :key="goal.id"
                class="rounded-2xl border bg-white p-5 shadow-sm transition hover:shadow-md dark:border-gray-800 dark:bg-gray-900"
            >
                <div class="mb-4 flex items-start justify-between gap-3">
                    <div class="flex min-w-0 items-start gap-3">
                        <div class="flex h-12 w-12 shrink-0 items-center justify-center rounded-xl bg-indigo-100 text-indigo-600 dark:bg-indigo-950 dark:text-indigo-300">
                            <Target class="h-6 w-6" />
                        </div>

                        <div class="min-w-0">
                            <h3 class="truncate font-semibold text-gray-900 dark:text-white">
                                {{ goal.title }}
                            </h3>

                            <p class="mt-1 text-xs text-muted-foreground">
                                Deadline: {{ formatDate(goal.target_date) }}
                            </p>
                        </div>
                    </div>

                    <span
                        :class="[
                            'inline-flex shrink-0 items-center gap-1 rounded-full px-2 py-1 text-xs font-medium',
                            getStatusClass(goal.status),
                        ]"
                    >
                        <component
                            :is="getStatusIcon(goal.status)"
                            class="h-3 w-3"
                        />
                        {{ goal.status_label }}
                    </span>
                </div>

                <div class="space-y-3">
                    <div class="grid grid-cols-2 gap-3">
                        <div class="rounded-xl bg-muted/40 p-3">
                            <p class="text-xs text-muted-foreground">
                                Terkumpul
                            </p>
                            <p class="mt-1 font-bold text-emerald-500">
                                {{ formatCurrency(goal.current_amount) }}
                            </p>
                        </div>

                        <div class="rounded-xl bg-muted/40 p-3">
                            <p class="text-xs text-muted-foreground">
                                Target
                            </p>
                            <p class="mt-1 font-bold">
                                {{ formatCurrency(goal.target_amount) }}
                            </p>
                        </div>
                    </div>

                    <div>
                        <div class="mb-1 flex justify-between text-xs text-muted-foreground">
                            <span>Progress</span>
                            <span>{{ goal.progress_percentage }}%</span>
                        </div>

                        <div class="h-3 overflow-hidden rounded-full bg-muted">
                            <div
                                class="h-full rounded-full transition-all"
                                :class="getProgressColor(goal.status, goal.progress_percentage)"
                                :style="{ width: Math.min(goal.progress_percentage, 100) + '%' }"
                            ></div>
                        </div>
                    </div>

                    <div class="rounded-xl bg-muted/40 p-3">
                        <p class="text-xs text-muted-foreground">
                            Sisa
                        </p>
                        <p class="mt-1 font-bold text-red-500">
                            {{ formatCurrency(goal.remaining_amount) }}
                        </p>
                    </div>

                    <p
                        v-if="goal.description"
                        class="line-clamp-2 text-sm text-muted-foreground"
                    >
                        {{ goal.description }}
                    </p>
                </div>

                <div class="mt-5 grid grid-cols-1 gap-2 border-t pt-4 sm:grid-cols-3 dark:border-gray-800">
                    <button
                        type="button"
                        class="inline-flex items-center justify-center gap-2 rounded-lg border border-purple-200 px-3 py-2 text-sm text-purple-600 transition hover:bg-purple-50 disabled:cursor-not-allowed disabled:opacity-50 dark:border-purple-900 dark:hover:bg-purple-950/40"
                        :disabled="goal.status === 'cancelled'"
                        @click="openDepositModal(goal)"
                    >
                        <PiggyBank class="h-4 w-4" />
                        Setor
                    </button>

                    <Link
                        :href="`/saving-goals/${goal.id}/edit`"
                        class="inline-flex items-center justify-center gap-2 rounded-lg border px-3 py-2 text-sm text-blue-600 transition hover:bg-blue-50 dark:border-blue-900 dark:hover:bg-blue-950/40"
                    >
                        <Edit2 class="h-4 w-4" />
                        Edit
                    </Link>

                    <button
                        type="button"
                        class="inline-flex items-center justify-center gap-2 rounded-lg border border-red-200 px-3 py-2 text-sm text-red-600 transition hover:bg-red-50 dark:border-red-900 dark:hover:bg-red-950/40"
                        @click="openDeleteModal(goal)"
                    >
                        <Trash2 class="h-4 w-4" />
                        Hapus
                    </button>
                </div>
            </div>
        </div>

        <!-- Pagination -->
        <div
            v-if="goals.last_page > 1"
            class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between"
        >
            <p class="text-sm text-gray-600 dark:text-gray-400">
                Menampilkan {{ goals.from }} sampai {{ goals.to }} dari
                {{ goals.total }} target
            </p>

            <div class="flex gap-2">
                <Link
                    v-if="goals.current_page > 1"
                    :href="`/saving-goals?page=${goals.current_page - 1}`"
                >
                    <Button variant="outline">Sebelumnya</Button>
                </Link>

                <Link
                    v-if="goals.current_page < goals.last_page"
                    :href="`/saving-goals?page=${goals.current_page + 1}`"
                >
                    <Button variant="outline">Berikutnya</Button>
                </Link>
            </div>
        </div>

        <DeleteConfirmModal
            :show="showDeleteModal"
            title="Hapus Target?"
            description="Target tabungan ini akan dihapus permanen."
            :item-name="getDeleteItemName"
            :processing="deleteProcessing"
            confirm-label="Ya, Hapus"
            @close="closeDeleteModal"
            @confirm="deleteGoal"
        />

        <SavingGoalDepositModal
            :show="showDepositModal"
            :goal="goalToDeposit"
            :accounts="accounts"
            @close="closeDepositModal"
        />
    </div>
</template>
