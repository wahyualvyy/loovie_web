<script setup lang="ts">
import { useForm } from '@inertiajs/vue3';
import { computed, watch } from 'vue';
import { AlertCircle, PiggyBank, Save, Wallet, X } from 'lucide-vue-next';

import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';

interface Account {
    id: number;
    name: string;
    type: string;
    current_balance: number;
}

interface Goal {
    id: number;
    title: string;
    target_amount: number;
    current_amount: number;
    remaining_amount: number;
    progress_percentage: number;
    status: string;
}

const props = defineProps<{
    show: boolean;
    goal: Goal | null;
    accounts: Account[];
}>();

const emit = defineEmits<{
    close: [];
}>();

const form = useForm({
    financial_account_id: '',
    amount: '',
    deposit_date: new Date().toISOString().split('T')[0],
    description: '',
});

watch(
    () => props.show,
    (value) => {
        if (value) {
            form.reset();
            form.clearErrors();
            form.deposit_date = new Date().toISOString().split('T')[0];
        }
    },
);

const selectedAccount = computed(() => {
    return props.accounts.find(
        (account) => account.id === Number(form.financial_account_id || 0),
    );
});

const amountNumber = computed(() => Number(form.amount || 0));

const hasEnoughBalance = computed(() => {
    if (!selectedAccount.value || !form.amount) return true;

    return Number(selectedAccount.value.current_balance || 0) >= amountNumber.value;
});

const goalProgressAfterDeposit = computed(() => {
    if (!props.goal) return 0;

    const target = Number(props.goal.target_amount || 0);
    const current = Number(props.goal.current_amount || 0) + amountNumber.value;

    if (target <= 0) return 0;

    return Math.min(Math.round((current / target) * 100), 100);
});

const remainingAfterDeposit = computed(() => {
    if (!props.goal) return 0;

    return Math.max(Number(props.goal.remaining_amount || 0) - amountNumber.value, 0);
});

const accountBalanceAfterDeposit = computed(() => {
    if (!selectedAccount.value) return 0;

    return Number(selectedAccount.value.current_balance || 0) - amountNumber.value;
});

const formatCurrency = (value: number | string | null | undefined) => {
    return new Intl.NumberFormat('id-ID', {
        style: 'currency',
        currency: 'IDR',
        minimumFractionDigits: 0,
        maximumFractionDigits: 0,
    }).format(Number(value || 0));
};

const closeModal = () => {
    if (form.processing) return;

    emit('close');
};

const submit = () => {
    if (!props.goal) return;

    form.post(`/saving-goals/${props.goal.id}/deposits`, {
        preserveScroll: true,
        onSuccess: () => {
            emit('close');
            form.reset();
        },
    });
};
</script>

<template>
    <div
        v-if="show && goal"
        class="fixed inset-0 z-50 flex items-center justify-center bg-black/50 p-4"
        @click.self="closeModal"
    >
        <div class="max-h-[90vh] w-full max-w-2xl overflow-y-auto rounded-2xl border bg-background p-6 shadow-xl">
            <!-- Header -->
            <div class="mb-6 flex items-start justify-between gap-4">
                <div class="flex items-start gap-4">
                    <div
                        class="flex h-12 w-12 shrink-0 items-center justify-center rounded-full bg-purple-100 text-purple-600 dark:bg-purple-950 dark:text-purple-300"
                    >
                        <PiggyBank class="h-6 w-6" />
                    </div>

                    <div>
                        <h2 class="text-lg font-semibold">
                            Setor ke Target Tabungan
                        </h2>

                        <p class="mt-1 text-sm text-muted-foreground">
                            Tambahkan uang ke target:
                            <strong>{{ goal.title }}</strong>
                        </p>
                    </div>
                </div>

                <button
                    type="button"
                    class="rounded-lg p-2 hover:bg-muted disabled:cursor-not-allowed disabled:opacity-50"
                    :disabled="form.processing"
                    @click="closeModal"
                >
                    <X class="h-4 w-4" />
                </button>
            </div>

            <div class="grid grid-cols-1 gap-6 lg:grid-cols-2">
                <!-- Form -->
                <div class="space-y-4">
                    <div>
                        <label class="mb-2 block text-sm font-semibold">
                            Akun Sumber
                            <span class="text-red-500">*</span>
                        </label>

                        <select
                            v-model="form.financial_account_id"
                            class="w-full rounded-lg border border-gray-200 bg-gray-50 px-4 py-2 text-gray-900 focus:ring-2 focus:ring-indigo-500 dark:border-gray-700 dark:bg-gray-800 dark:text-white"
                        >
                            <option value="">
                                Pilih akun sumber
                            </option>

                            <option
                                v-for="account in accounts"
                                :key="account.id"
                                :value="String(account.id)"
                            >
                                {{ account.name }} - {{ formatCurrency(account.current_balance) }}
                            </option>
                        </select>

                        <p
                            v-if="form.errors.financial_account_id"
                            class="mt-2 flex items-center gap-2 text-sm text-red-600"
                        >
                            <AlertCircle class="h-4 w-4" />
                            {{ form.errors.financial_account_id }}
                        </p>
                    </div>

                    <div>
                        <label class="mb-2 block text-sm font-semibold">
                            Nominal Setor
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
                            Saldo akun tidak mencukupi.
                        </p>

                        <p
                            v-if="form.errors.amount"
                            class="mt-2 flex items-center gap-2 text-sm text-red-600"
                        >
                            <AlertCircle class="h-4 w-4" />
                            {{ form.errors.amount }}
                        </p>
                    </div>

                    <div>
                        <label class="mb-2 block text-sm font-semibold">
                            Tanggal Setor
                            <span class="text-red-500">*</span>
                        </label>

                        <Input
                            v-model="form.deposit_date"
                            type="date"
                            class="rounded-lg border-gray-200 bg-gray-50 dark:border-gray-700 dark:bg-gray-800"
                        />

                        <p
                            v-if="form.errors.deposit_date"
                            class="mt-2 flex items-center gap-2 text-sm text-red-600"
                        >
                            <AlertCircle class="h-4 w-4" />
                            {{ form.errors.deposit_date }}
                        </p>
                    </div>

                    <div>
                        <label class="mb-2 block text-sm font-semibold">
                            Deskripsi
                        </label>

                        <textarea
                            v-model="form.description"
                            rows="3"
                            placeholder="Contoh: setoran dari gaji bulan ini..."
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
                </div>

                <!-- Preview -->
                <div class="space-y-4">
                    <div class="rounded-xl border bg-muted/40 p-4">
                        <div class="mb-3 flex items-center gap-2">
                            <PiggyBank class="h-5 w-5 text-purple-500" />
                            <p class="font-semibold">
                                Preview Target
                            </p>
                        </div>

                        <p class="text-sm text-muted-foreground">
                            Target
                        </p>
                        <p class="mt-1 font-semibold">
                            {{ goal.title }}
                        </p>

                        <div class="mt-4 grid grid-cols-2 gap-3">
                            <div class="rounded-lg bg-background p-3">
                                <p class="text-xs text-muted-foreground">
                                    Saat Ini
                                </p>
                                <p class="mt-1 font-bold text-emerald-500">
                                    {{ formatCurrency(goal.current_amount) }}
                                </p>
                            </div>

                            <div class="rounded-lg bg-background p-3">
                                <p class="text-xs text-muted-foreground">
                                    Setelah Setor
                                </p>
                                <p class="mt-1 font-bold text-purple-500">
                                    {{ formatCurrency(Number(goal.current_amount) + amountNumber) }}
                                </p>
                            </div>
                        </div>

                        <div class="mt-4">
                            <div class="mb-1 flex justify-between text-xs text-muted-foreground">
                                <span>Progress setelah setor</span>
                                <span>{{ goalProgressAfterDeposit }}%</span>
                            </div>

                            <div class="h-3 overflow-hidden rounded-full bg-gray-200 dark:bg-gray-700">
                                <div
                                    class="h-full rounded-full bg-purple-500"
                                    :style="{ width: goalProgressAfterDeposit + '%' }"
                                ></div>
                            </div>
                        </div>

                        <div class="mt-3 flex justify-between text-sm">
                            <span class="text-muted-foreground">
                                Sisa setelah setor
                            </span>
                            <span class="font-semibold text-red-500">
                                {{ formatCurrency(remainingAfterDeposit) }}
                            </span>
                        </div>
                    </div>

                    <div class="rounded-xl border bg-muted/40 p-4">
                        <div class="mb-3 flex items-center gap-2">
                            <Wallet class="h-5 w-5 text-blue-500" />
                            <p class="font-semibold">
                                Preview Akun
                            </p>
                        </div>

                        <p class="text-sm text-muted-foreground">
                            Akun sumber
                        </p>

                        <p class="mt-1 font-semibold">
                            {{ selectedAccount?.name || '-' }}
                        </p>

                        <div class="mt-4 grid grid-cols-2 gap-3">
                            <div class="rounded-lg bg-background p-3">
                                <p class="text-xs text-muted-foreground">
                                    Saldo Sekarang
                                </p>
                                <p class="mt-1 font-bold">
                                    {{ formatCurrency(selectedAccount?.current_balance || 0) }}
                                </p>
                            </div>

                            <div class="rounded-lg bg-background p-3">
                                <p class="text-xs text-muted-foreground">
                                    Setelah Setor
                                </p>
                                <p
                                    :class="[
                                        'mt-1 font-bold',
                                        accountBalanceAfterDeposit < 0
                                            ? 'text-red-500'
                                            : 'text-blue-500',
                                    ]"
                                >
                                    {{ formatCurrency(accountBalanceAfterDeposit) }}
                                </p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Actions -->
            <div class="mt-6 flex flex-col gap-3 border-t pt-5 sm:flex-row">
                <Button
                    type="button"
                    variant="outline"
                    class="flex-1"
                    :disabled="form.processing"
                    @click="closeModal"
                >
                    Batal
                </Button>

                <Button
                    type="button"
                    class="flex-1 bg-purple-600 text-white hover:bg-purple-700 disabled:cursor-not-allowed disabled:bg-slate-400"
                    :disabled="form.processing || !hasEnoughBalance"
                    @click="submit"
                >
                    <Save class="mr-2 h-4 w-4" />
                    {{ form.processing ? 'Menyimpan...' : 'Simpan Setoran' }}
                </Button>
            </div>
        </div>
    </div>
</template>
