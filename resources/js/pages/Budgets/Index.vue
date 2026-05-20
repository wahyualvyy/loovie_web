<script setup>
import { Head, router } from '@inertiajs/vue3';
import { computed, ref } from 'vue';
import AppLayout from '@/layouts/AppLayout.vue';
import DeleteConfirmModal from '@/components/DeleteConfirmModal.vue';
import EmptyState from '@/components/EmptyState.vue';
import {
    PiggyBank,
    Plus,
    Pencil,
    Trash2,
    Wallet,
    TrendingDown,
    AlertTriangle,
    CheckCircle2,
    X,
    Save,
} from 'lucide-vue-next';

defineOptions({
    layout: AppLayout,
});

const props = defineProps({
    budgets: {
        type: Array,
        default: () => [],
    },
    categories: {
        type: Array,
        default: () => [],
    },
    selectedMonth: {
        type: String,
        default: '',
    },
    summary: {
        type: Object,
        default: () => ({
            totalAccountBalance: 0,
            totalBudget: 0,
            totalUsed: 0,
            totalRemaining: 0,
            unallocatedBalance: 0,
            totalPercentage: 0,
        }),
    },
});

const showForm = ref(false);
const editMode = ref(false);
const selectedBudgetId = ref(null);
const errors = ref({});
const processing = ref(false);

const showDeleteModal = ref(false);
const selectedDeleteBudget = ref(null);
const deleteProcessing = ref(false);

const monthFilter = ref(
    props.selectedMonth || new Date().toISOString().slice(0, 7),
);

const form = ref({
    category_id: '',
    month: monthFilter.value,
    amount: '',
    description: '',
});

const budgetStatus = computed(() => {
    const percentage = props.summary.totalPercentage || 0;

    if (percentage >= 100) return 'over';
    if (percentage >= 80) return 'warning';

    return 'safe';
});

const hasCategories = computed(() => props.categories.length > 0);

const formatCurrency = (value) => {
    return new Intl.NumberFormat('id-ID', {
        style: 'currency',
        currency: 'IDR',
        minimumFractionDigits: 0,
        maximumFractionDigits: 0,
    }).format(Number(value || 0));
};

const getStatusLabel = (status) => {
    if (status === 'over') return 'Melebihi Budget';
    if (status === 'warning') return 'Hampir Limit';

    return 'Aman';
};

const getStatusClass = (status) => {
    if (status === 'over') {
        return 'bg-red-100 text-red-700 dark:bg-red-950 dark:text-red-300';
    }

    if (status === 'warning') {
        return 'bg-yellow-100 text-yellow-700 dark:bg-yellow-950 dark:text-yellow-300';
    }

    return 'bg-emerald-100 text-emerald-700 dark:bg-emerald-950 dark:text-emerald-300';
};

const getProgressClass = (status) => {
    if (status === 'over') return 'bg-red-500';
    if (status === 'warning') return 'bg-yellow-500';

    return 'bg-emerald-500';
};

const getSummaryStatusText = computed(() => {
    if (props.summary.totalBudget <= 0) {
        return 'Belum Ada Budget';
    }

    if (budgetStatus.value === 'over') {
        return 'Melebihi Budget';
    }

    if (budgetStatus.value === 'warning') {
        return 'Hampir Limit';
    }

    return 'Aman';
});

const changeMonth = () => {
    router.get(
        '/budgets',
        {
            month: monthFilter.value,
        },
        {
            preserveScroll: true,
            preserveState: true,
        },
    );
};

const resetForm = () => {
    form.value = {
        category_id: '',
        month: monthFilter.value,
        amount: '',
        description: '',
    };

    selectedBudgetId.value = null;
    editMode.value = false;
    errors.value = {};
    processing.value = false;
};

const openCreate = () => {
    resetForm();
    showForm.value = true;
};

const openEdit = (budget) => {
    editMode.value = true;
    selectedBudgetId.value = budget.id;
    showForm.value = true;
    errors.value = {};
    processing.value = false;

    form.value = {
        category_id: budget.category_id,
        month: budget.month,
        amount: budget.amount,
        description: budget.description || '',
    };
};

const closeForm = () => {
    showForm.value = false;
    resetForm();
};

const submitForm = () => {
    errors.value = {};
    processing.value = true;

    const payload = {
        category_id: form.value.category_id,
        month: form.value.month,
        amount: form.value.amount,
        description: form.value.description,
    };

    const options = {
        preserveScroll: true,
        onSuccess: () => {
            showForm.value = false;
            resetForm();
        },
        onError: (err) => {
            errors.value = err;
        },
        onFinish: () => {
            processing.value = false;
        },
    };

    if (editMode.value) {
        router.put(`/budgets/${selectedBudgetId.value}`, payload, options);
        return;
    }

    router.post('/budgets', payload, options);
};

const openDeleteModal = (budget) => {
    selectedDeleteBudget.value = budget;
    showDeleteModal.value = true;
};

const closeDeleteModal = () => {
    selectedDeleteBudget.value = null;
    showDeleteModal.value = false;
};

const confirmDeleteBudget = () => {
    if (!selectedDeleteBudget.value) return;

    deleteProcessing.value = true;

    router.delete(`/budgets/${selectedDeleteBudget.value.id}`, {
        preserveScroll: true,
        onError: (err) => {
            errors.value = err;
        },
        onFinish: () => {
            deleteProcessing.value = false;
        },
    });
};
</script>

<template>
    <Head title="Budget Bulanan" />

    <div class="space-y-6 p-4 md:p-6">
        <div
            class="flex flex-col gap-4 md:flex-row md:items-center md:justify-between"
        >
            <div>
                <h1 class="text-2xl font-bold">Budget Bulanan</h1>
                <p class="text-sm text-muted-foreground">
                    Atur alokasi pengeluaran dari saldo akun keuangan per
                    kategori.
                </p>
            </div>

            <div class="flex flex-col gap-2 sm:flex-row">
                <input
                    v-model="monthFilter"
                    type="month"
                    class="rounded-lg border bg-background px-3 py-2 text-sm"
                    @change="changeMonth"
                />

                <button
                    type="button"
                    :disabled="!hasCategories"
                    class="inline-flex items-center justify-center gap-2 rounded-lg bg-blue-600 px-4 py-2 text-sm font-medium text-white transition hover:bg-blue-700 disabled:cursor-not-allowed disabled:bg-slate-400"
                    @click="openCreate"
                >
                    <Plus class="h-4 w-4" />
                    Tambah Budget
                </button>
            </div>
        </div>

        <div
            v-if="!hasCategories"
            class="rounded-xl border border-yellow-200 bg-yellow-50 p-4 text-sm text-yellow-800 dark:border-yellow-900 dark:bg-yellow-950 dark:text-yellow-200"
        >
            Kamu belum punya kategori pengeluaran. Buat kategori dengan tipe
            <strong>expense</strong> dulu agar bisa membuat budget.
        </div>

        <div class="grid grid-cols-1 gap-4 md:grid-cols-2 xl:grid-cols-5">
            <div class="rounded-xl border bg-card p-5 shadow-sm">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm text-muted-foreground">Saldo Akun</p>
                        <p class="mt-2 text-xl font-bold">
                            {{ formatCurrency(summary.totalAccountBalance) }}
                        </p>
                    </div>

                    <Wallet class="h-8 w-8 text-blue-500" />
                </div>
            </div>

            <div class="rounded-xl border bg-card p-5 shadow-sm">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm text-muted-foreground">
                            Budget Dialokasikan
                        </p>
                        <p class="mt-2 text-xl font-bold">
                            {{ formatCurrency(summary.totalBudget) }}
                        </p>
                    </div>

                    <PiggyBank class="h-8 w-8 text-indigo-500" />
                </div>
            </div>

            <div class="rounded-xl border bg-card p-5 shadow-sm">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm text-muted-foreground">
                            Belum Dialokasikan
                        </p>
                        <p
                            :class="[
                                'mt-2 text-xl font-bold',
                                summary.unallocatedBalance >= 0
                                    ? 'text-emerald-500'
                                    : 'text-red-500',
                            ]"
                        >
                            {{ formatCurrency(summary.unallocatedBalance) }}
                        </p>
                    </div>

                    <Wallet class="h-8 w-8 text-emerald-500" />
                </div>
            </div>

            <div class="rounded-xl border bg-card p-5 shadow-sm">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm text-muted-foreground">Terpakai</p>
                        <p class="mt-2 text-xl font-bold text-red-500">
                            {{ formatCurrency(summary.totalUsed) }}
                        </p>
                    </div>

                    <TrendingDown class="h-8 w-8 text-red-500" />
                </div>
            </div>

            <div class="rounded-xl border bg-card p-5 shadow-sm">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm text-muted-foreground">Sisa Budget</p>
                        <p
                            :class="[
                                'mt-2 text-xl font-bold',
                                summary.totalRemaining >= 0
                                    ? 'text-emerald-500'
                                    : 'text-red-500',
                            ]"
                        >
                            {{ formatCurrency(summary.totalRemaining) }}
                        </p>
                    </div>

                    <AlertTriangle
                        v-if="budgetStatus !== 'safe'"
                        class="h-8 w-8 text-yellow-500"
                    />

                    <CheckCircle2 v-else class="h-8 w-8 text-emerald-500" />
                </div>
            </div>
        </div>

        <div class="rounded-xl border bg-card p-5 shadow-sm">
            <div class="mb-3 flex items-center justify-between">
                <div>
                    <h2 class="font-semibold">Progress Budget Bulanan</h2>
                    <p class="text-sm text-muted-foreground">
                        Total penggunaan budget bulan {{ selectedMonth }}.
                    </p>
                </div>

                <span
                    :class="[
                        'rounded-full px-3 py-1 text-xs font-medium',
                        getStatusClass(budgetStatus),
                    ]"
                >
                    {{ getSummaryStatusText }}
                </span>
            </div>

            <div
                class="mb-1 flex justify-between text-xs text-muted-foreground"
            >
                <span>Terpakai</span>
                <span>{{ summary.totalPercentage || 0 }}%</span>
            </div>

            <div class="h-3 overflow-hidden rounded-full bg-muted">
                <div
                    class="h-full rounded-full transition-all"
                    :class="getProgressClass(budgetStatus)"
                    :style="{
                        width:
                            Math.min(summary.totalPercentage || 0, 100) + '%',
                    }"
                ></div>
            </div>
        </div>

        <div class="rounded-xl border bg-card p-5 shadow-sm">
            <div class="mb-4 flex items-center justify-between">
                <div>
                    <h2 class="font-semibold">Daftar Budget</h2>
                    <p class="text-sm text-muted-foreground">
                        Budget dihitung dari transaksi pengeluaran berdasarkan
                        kategori.
                    </p>
                </div>
            </div>

            <EmptyState
                v-if="budgets.length === 0"
                :icon="PiggyBank"
                title="Belum ada budget"
                description="Buat budget pertama untuk mengatur batas pengeluaran dari saldo akunmu."
                action-label="Tambah Budget"
                button-type="button"
                @action="openCreate"
            />

            <div v-else class="space-y-4">
                <div
                    v-for="budget in budgets"
                    :key="budget.id"
                    class="rounded-xl border p-4"
                >
                    <div
                        class="mb-3 flex flex-col gap-3 md:flex-row md:items-center md:justify-between"
                    >
                        <div class="flex items-center gap-3">
                            <div
                                class="h-10 w-10 shrink-0 rounded-full"
                                :style="{
                                    backgroundColor: budget.category_color,
                                }"
                            ></div>

                            <div>
                                <h3 class="font-semibold">
                                    {{ budget.category_name }}
                                </h3>
                                <p class="text-sm text-muted-foreground">
                                    {{ budget.month }}
                                </p>
                            </div>
                        </div>

                        <div class="flex flex-wrap items-center gap-2">
                            <span
                                :class="[
                                    'rounded-full px-3 py-1 text-xs font-medium',
                                    getStatusClass(budget.status),
                                ]"
                            >
                                {{ getStatusLabel(budget.status) }}
                            </span>

                            <button
                                type="button"
                                class="inline-flex items-center gap-1 rounded-lg border px-3 py-2 text-sm hover:bg-muted"
                                @click="openEdit(budget)"
                            >
                                <Pencil class="h-4 w-4" />
                                Edit
                            </button>

                            <button
                                type="button"
                                class="inline-flex items-center gap-1 rounded-lg border border-red-200 px-3 py-2 text-sm text-red-600 transition hover:bg-red-50 dark:border-red-900 dark:hover:bg-red-950/40"
                                @click="openDeleteModal(budget)"
                            >
                                <Trash2 class="h-4 w-4" />
                                Hapus
                            </button>
                        </div>
                    </div>

                    <div class="grid grid-cols-1 gap-3 md:grid-cols-3">
                        <div>
                            <p class="text-xs text-muted-foreground">Budget</p>
                            <p class="font-semibold">
                                {{ formatCurrency(budget.amount) }}
                            </p>
                        </div>

                        <div>
                            <p class="text-xs text-muted-foreground">
                                Terpakai
                            </p>
                            <p class="font-semibold text-red-500">
                                {{ formatCurrency(budget.used_amount) }}
                            </p>
                        </div>

                        <div>
                            <p class="text-xs text-muted-foreground">Sisa</p>
                            <p
                                :class="[
                                    'font-semibold',
                                    budget.remaining_amount >= 0
                                        ? 'text-emerald-500'
                                        : 'text-red-500',
                                ]"
                            >
                                {{ formatCurrency(budget.remaining_amount) }}
                            </p>
                        </div>
                    </div>

                    <div class="mt-4">
                        <div
                            class="mb-1 flex justify-between text-xs text-muted-foreground"
                        >
                            <span>Progress</span>
                            <span>{{ budget.percentage }}%</span>
                        </div>

                        <div class="h-2 overflow-hidden rounded-full bg-muted">
                            <div
                                class="h-full rounded-full transition-all"
                                :class="getProgressClass(budget.status)"
                                :style="{
                                    width:
                                        Math.min(budget.percentage, 100) + '%',
                                }"
                            ></div>
                        </div>
                    </div>

                    <p
                        v-if="budget.description"
                        class="mt-3 text-sm text-muted-foreground"
                    >
                        {{ budget.description }}
                    </p>
                </div>
            </div>
        </div>

        <!-- Form Modal -->
        <div
            v-if="showForm"
            class="fixed inset-0 z-50 flex items-center justify-center bg-black/50 p-4"
            @click.self="closeForm"
        >
            <div class="w-full max-w-lg rounded-xl bg-background p-6 shadow-lg">
                <div class="mb-6 flex items-start justify-between gap-4">
                    <div>
                        <h2 class="text-xl font-semibold">
                            {{ editMode ? 'Edit Budget' : 'Tambah Budget' }}
                        </h2>

                        <p class="mt-1 text-sm text-muted-foreground">
                            Tentukan alokasi budget dari saldo akun untuk
                            kategori pengeluaran.
                        </p>
                    </div>

                    <button
                        type="button"
                        class="rounded-lg p-2 hover:bg-muted disabled:cursor-not-allowed disabled:opacity-50"
                        :disabled="processing"
                        @click="closeForm"
                    >
                        <X class="h-5 w-5" />
                    </button>
                </div>

                <form class="space-y-4" @submit.prevent="submitForm">
                    <div>
                        <label class="mb-1 block text-sm font-medium">
                            Kategori Pengeluaran
                        </label>

                        <select
                            v-model="form.category_id"
                            class="w-full rounded-lg border bg-background px-3 py-2"
                            :disabled="processing"
                        >
                            <option value="">Pilih kategori</option>

                            <option
                                v-for="category in categories"
                                :key="category.id"
                                :value="category.id"
                            >
                                {{ category.name }}
                            </option>
                        </select>

                        <p
                            v-if="errors.category_id"
                            class="mt-1 text-sm text-red-500"
                        >
                            {{ errors.category_id }}
                        </p>
                    </div>

                    <div>
                        <label class="mb-1 block text-sm font-medium">
                            Bulan Budget
                        </label>

                        <input
                            v-model="form.month"
                            type="month"
                            class="w-full rounded-lg border bg-background px-3 py-2"
                            :disabled="processing"
                        />

                        <p
                            v-if="errors.month"
                            class="mt-1 text-sm text-red-500"
                        >
                            {{ errors.month }}
                        </p>
                    </div>

                    <div>
                        <label class="mb-1 block text-sm font-medium">
                            Nominal Alokasi Budget
                        </label>

                        <input
                            v-model="form.amount"
                            type="number"
                            min="1"
                            step="1000"
                            class="w-full rounded-lg border bg-background px-3 py-2"
                            placeholder="Contoh: 500000 untuk kategori Makan"
                            :disabled="processing"
                        />

                        <p class="mt-1 text-xs text-muted-foreground">
                            Nilai ini akan menjadi batas pengeluaran untuk
                            kategori tersebut.
                        </p>

                        <p
                            v-if="errors.amount"
                            class="mt-1 text-sm text-red-500"
                        >
                            {{ errors.amount }}
                        </p>
                    </div>

                    <div>
                        <label class="mb-1 block text-sm font-medium">
                            Catatan
                        </label>

                        <textarea
                            v-model="form.description"
                            rows="3"
                            class="w-full rounded-lg border bg-background px-3 py-2"
                            placeholder="Opsional"
                            :disabled="processing"
                        ></textarea>

                        <p
                            v-if="errors.description"
                            class="mt-1 text-sm text-red-500"
                        >
                            {{ errors.description }}
                        </p>
                    </div>

                    <div class="flex justify-end gap-2 pt-2">
                        <button
                            type="button"
                            class="rounded-lg border px-4 py-2 text-sm hover:bg-muted disabled:cursor-not-allowed disabled:opacity-50"
                            :disabled="processing"
                            @click="closeForm"
                        >
                            Batal
                        </button>

                        <button
                            type="submit"
                            class="inline-flex items-center gap-2 rounded-lg bg-blue-600 px-4 py-2 text-sm font-medium text-white hover:bg-blue-700 disabled:cursor-not-allowed disabled:bg-slate-400"
                            :disabled="processing"
                        >
                            <Save class="h-4 w-4" />
                            {{
                                processing
                                    ? 'Menyimpan...'
                                    : editMode
                                      ? 'Update Budget'
                                      : 'Simpan Budget'
                            }}
                        </button>
                    </div>
                </form>
            </div>
        </div>

        <DeleteConfirmModal
            :show="showDeleteModal"
            title="Hapus Budget?"
            description="Budget ini akan dihapus permanen dan tidak bisa dikembalikan."
            :item-name="selectedDeleteBudget?.category_name"
            :processing="deleteProcessing"
            confirm-label="Ya, Hapus"
            @close="closeDeleteModal"
            @confirm="confirmDeleteBudget"
        />
    </div>
</template>
