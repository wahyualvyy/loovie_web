<script setup>
import { Head, router } from '@inertiajs/vue3';
import AppLayout from '@/layouts/AppLayout.vue';
import {
    ArchiveRestore,
    Download,
    Upload,
    AlertTriangle,
    FileJson,
    ShieldCheck,
    DatabaseBackup,
    Wallet,
    Tags,
    ReceiptText,
    NotebookText,
    PiggyBank,
    X,
} from 'lucide-vue-next';
import { computed, ref } from 'vue';

defineOptions({
    layout: AppLayout,
});

const fileInput = ref(null);
const selectedFile = ref(null);
const errors = ref({});
const processing = ref(false);
const showConfirmModal = ref(false);

const selectedFileSize = computed(() => {
    if (!selectedFile.value) return '';

    const size = selectedFile.value.size;

    if (size >= 1024 * 1024) {
        return `${(size / 1024 / 1024).toFixed(2)} MB`;
    }

    return `${(size / 1024).toFixed(2)} KB`;
});

const backupItems = [
    {
        title: 'Akun Keuangan',
        description: 'Cash, bank, e-wallet, investasi, dan saldo akun.',
        icon: Wallet,
    },
    {
        title: 'Kategori',
        description: 'Kategori pemasukan dan pengeluaran.',
        icon: Tags,
    },
    {
        title: 'Transaksi',
        description: 'Semua pemasukan dan pengeluaran.',
        icon: ReceiptText,
    },
    {
        title: 'Catatan',
        description: 'Catatan keuangan dan pengingat.',
        icon: NotebookText,
    },
    {
        title: 'Budget',
        description: 'Budget bulanan per kategori.',
        icon: PiggyBank,
    },
];

const handleFileChange = (event) => {
    const file = event.target.files?.[0];

    selectedFile.value = file || null;
    errors.value = {};

    if (!file) return;

    const isJson =
        file.type === 'application/json' ||
        file.name.toLowerCase().endsWith('.json');

    if (!isJson) {
        selectedFile.value = null;
        errors.value.backup_file = 'File harus berformat JSON.';

        if (fileInput.value) {
            fileInput.value.value = '';
        }
    }
};

const openFilePicker = () => {
    fileInput.value?.click();
};

const clearSelectedFile = () => {
    selectedFile.value = null;
    errors.value = {};

    if (fileInput.value) {
        fileInput.value.value = '';
    }
};

const openConfirmModal = () => {
    errors.value = {};

    if (!selectedFile.value) {
        errors.value.backup_file = 'Pilih file backup terlebih dahulu.';
        return;
    }

    showConfirmModal.value = true;
};

const closeConfirmModal = () => {
    if (processing.value) return;

    showConfirmModal.value = false;
};

const importBackup = () => {
    if (!selectedFile.value) return;

    const formData = new FormData();
    formData.append('backup_file', selectedFile.value);

    processing.value = true;

    router.post('/backup/import', formData, {
        forceFormData: true,
        preserveScroll: true,
        onSuccess: () => {
            showConfirmModal.value = false;
            selectedFile.value = null;

            if (fileInput.value) {
                fileInput.value.value = '';
            }
        },
        onError: (err) => {
            errors.value = err;
            showConfirmModal.value = false;
        },
        onFinish: () => {
            processing.value = false;
        },
    });
};
</script>

<template>
    <Head title="Backup & Restore - Loovie Apps" />

    <div class="space-y-6 p-4 md:p-6">
        <!-- Header -->
        <div class="flex flex-col gap-4 md:flex-row md:items-center md:justify-between">
            <div>
                <h1 class="text-2xl font-bold md:text-3xl">
                    Backup & Restore
                </h1>
                <p class="text-sm text-muted-foreground">
                    Export dan import data keuangan dalam format JSON.
                </p>
            </div>

            <a
                href="/backup/export"
                class="inline-flex items-center justify-center gap-2 rounded-lg bg-blue-600 px-4 py-2 text-sm font-medium text-white transition hover:bg-blue-700"
            >
                <Download class="h-4 w-4" />
                Download Backup
            </a>
        </div>

        <!-- Warning -->
        <div
            class="rounded-xl border border-yellow-200 bg-yellow-50 p-4 text-sm text-yellow-800 dark:border-yellow-900 dark:bg-yellow-950/40 dark:text-yellow-200"
        >
            <div class="flex gap-3">
                <AlertTriangle class="mt-0.5 h-5 w-5 shrink-0" />
                <div>
                    <p class="font-semibold">
                        Penting sebelum restore
                    </p>
                    <p class="mt-1">
                        Restore akan mengganti data akun yang sedang login dengan isi file backup.
                        Pastikan kamu sudah membuat backup terbaru sebelum melakukan restore.
                    </p>
                </div>
            </div>
        </div>

        <!-- Main Actions -->
        <div class="grid grid-cols-1 gap-6 xl:grid-cols-2">
            <!-- Export -->
            <div class="rounded-xl border bg-card p-6 shadow-sm">
                <div class="flex items-start gap-4">
                    <div
                        class="flex h-12 w-12 shrink-0 items-center justify-center rounded-xl bg-blue-100 text-blue-600 dark:bg-blue-950 dark:text-blue-300"
                    >
                        <Download class="h-6 w-6" />
                    </div>

                    <div>
                        <h2 class="text-lg font-semibold">
                            Export Backup
                        </h2>
                        <p class="mt-1 text-sm text-muted-foreground">
                            Download semua data akun, kategori, transaksi,
                            catatan, dan budget milik akunmu.
                        </p>
                    </div>
                </div>

                <div class="mt-6 rounded-xl border bg-muted/40 p-4">
                    <div class="flex items-center gap-3">
                        <FileJson class="h-5 w-5 text-blue-500" />
                        <div>
                            <p class="text-sm font-medium">
                                Format JSON
                            </p>
                            <p class="text-xs text-muted-foreground">
                                File backup bisa digunakan untuk restore data kapan saja.
                            </p>
                        </div>
                    </div>
                </div>

                <div class="mt-6 grid grid-cols-1 gap-3 sm:grid-cols-2">
                    <div class="rounded-lg bg-muted/40 p-3 text-sm">
                        <p class="font-medium">Aman untuk disimpan</p>
                        <p class="mt-1 text-xs text-muted-foreground">
                            Simpan file di drive pribadi.
                        </p>
                    </div>

                    <div class="rounded-lg bg-muted/40 p-3 text-sm">
                        <p class="font-medium">Mudah dipulihkan</p>
                        <p class="mt-1 text-xs text-muted-foreground">
                            Import ulang saat dibutuhkan.
                        </p>
                    </div>
                </div>

                <a
                    href="/backup/export"
                    class="mt-6 inline-flex w-full items-center justify-center gap-2 rounded-lg bg-blue-600 px-4 py-2 text-sm font-medium text-white transition hover:bg-blue-700"
                >
                    <Download class="h-4 w-4" />
                    Download Backup JSON
                </a>
            </div>

            <!-- Restore -->
            <div class="rounded-xl border bg-card p-6 shadow-sm">
                <div class="flex items-start gap-4">
                    <div
                        class="flex h-12 w-12 shrink-0 items-center justify-center rounded-xl bg-emerald-100 text-emerald-600 dark:bg-emerald-950 dark:text-emerald-300"
                    >
                        <Upload class="h-6 w-6" />
                    </div>

                    <div>
                        <h2 class="text-lg font-semibold">
                            Restore Backup
                        </h2>
                        <p class="mt-1 text-sm text-muted-foreground">
                            Import file backup JSON untuk memulihkan data akunmu.
                        </p>
                    </div>
                </div>

                <div class="mt-6">
                    <label class="mb-2 block text-sm font-medium">
                        File Backup JSON
                    </label>

                    <input
                        ref="fileInput"
                        type="file"
                        accept=".json,application/json"
                        class="hidden"
                        @change="handleFileChange"
                    />

                    <button
                        type="button"
                        class="flex w-full flex-col items-center justify-center rounded-xl border border-dashed bg-muted/30 px-4 py-8 text-center transition hover:bg-muted/50"
                        @click="openFilePicker"
                    >
                        <FileJson class="h-10 w-10 text-muted-foreground" />
                        <span class="mt-3 text-sm font-medium">
                            Pilih file backup
                        </span>
                        <span class="mt-1 text-xs text-muted-foreground">
                            Klik untuk memilih file .json
                        </span>
                    </button>

                    <p
                        v-if="errors.backup_file"
                        class="mt-2 text-sm text-red-500"
                    >
                        {{ errors.backup_file }}
                    </p>
                </div>

                <div
                    v-if="selectedFile"
                    class="mt-4 rounded-xl border bg-muted/40 p-4"
                >
                    <div class="flex items-start justify-between gap-3">
                        <div class="flex items-start gap-3">
                            <FileJson class="mt-0.5 h-5 w-5 shrink-0 text-blue-500" />

                            <div class="min-w-0">
                                <p class="truncate text-sm font-medium">
                                    {{ selectedFile.name }}
                                </p>
                                <p class="mt-1 text-xs text-muted-foreground">
                                    Ukuran file: {{ selectedFileSize }}
                                </p>
                            </div>
                        </div>

                        <button
                            type="button"
                            class="rounded-lg p-1 text-muted-foreground hover:bg-muted hover:text-foreground"
                            @click="clearSelectedFile"
                        >
                            <X class="h-4 w-4" />
                        </button>
                    </div>
                </div>

                <button
                    type="button"
                    class="mt-6 inline-flex w-full items-center justify-center gap-2 rounded-lg bg-emerald-600 px-4 py-2 text-sm font-medium text-white transition hover:bg-emerald-700 disabled:cursor-not-allowed disabled:bg-slate-400"
                    :disabled="processing"
                    @click="openConfirmModal"
                >
                    <ArchiveRestore class="h-4 w-4" />
                    Restore Data
                </button>
            </div>
        </div>

        <!-- Backup Content -->
        <div class="rounded-xl border bg-card p-6 shadow-sm">
            <div class="flex items-start gap-4">
                <div
                    class="flex h-12 w-12 shrink-0 items-center justify-center rounded-xl bg-indigo-100 text-indigo-600 dark:bg-indigo-950 dark:text-indigo-300"
                >
                    <ShieldCheck class="h-6 w-6" />
                </div>

                <div class="min-w-0 flex-1">
                    <h2 class="text-lg font-semibold">
                        Data yang termasuk backup
                    </h2>
                    <p class="mt-1 text-sm text-muted-foreground">
                        File backup mencakup data personal user yang sedang login.
                    </p>

                    <div class="mt-5 grid grid-cols-1 gap-3 md:grid-cols-2 xl:grid-cols-5">
                        <div
                            v-for="item in backupItems"
                            :key="item.title"
                            class="rounded-xl border bg-muted/30 p-4"
                        >
                            <component
                                :is="item.icon"
                                class="h-6 w-6 text-indigo-500"
                            />

                            <p class="mt-3 text-sm font-semibold">
                                {{ item.title }}
                            </p>

                            <p class="mt-1 text-xs text-muted-foreground">
                                {{ item.description }}
                            </p>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Info -->
        <div class="grid grid-cols-1 gap-4 md:grid-cols-3">
            <div class="rounded-xl border bg-card p-5 shadow-sm">
                <DatabaseBackup class="h-7 w-7 text-blue-500" />
                <h3 class="mt-3 font-semibold">
                    Backup rutin
                </h3>
                <p class="mt-1 text-sm text-muted-foreground">
                    Lakukan backup secara berkala agar data tetap aman.
                </p>
            </div>

            <div class="rounded-xl border bg-card p-5 shadow-sm">
                <FileJson class="h-7 w-7 text-emerald-500" />
                <h3 class="mt-3 font-semibold">
                    Format terbuka
                </h3>
                <p class="mt-1 text-sm text-muted-foreground">
                    File JSON mudah disimpan dan dipindahkan.
                </p>
            </div>

            <div class="rounded-xl border bg-card p-5 shadow-sm">
                <AlertTriangle class="h-7 w-7 text-yellow-500" />
                <h3 class="mt-3 font-semibold">
                    Restore mengganti data
                </h3>
                <p class="mt-1 text-sm text-muted-foreground">
                    Data lama akan diganti sesuai isi file backup.
                </p>
            </div>
        </div>

        <!-- Restore Confirm Modal -->
        <div
            v-if="showConfirmModal"
            class="fixed inset-0 z-50 flex items-center justify-center bg-black/50 p-4"
        >
            <div class="w-full max-w-md rounded-2xl border bg-background p-6 shadow-xl">
                <div class="flex items-start justify-between gap-4">
                    <div class="flex items-start gap-4">
                        <div
                            class="flex h-12 w-12 shrink-0 items-center justify-center rounded-full bg-red-100 text-red-600 dark:bg-red-950 dark:text-red-300"
                        >
                            <AlertTriangle class="h-6 w-6" />
                        </div>

                        <div>
                            <h2 class="text-lg font-semibold">
                                Restore Data?
                            </h2>
                            <p class="mt-1 text-sm text-muted-foreground">
                                Data lama akun ini akan diganti dengan isi file backup.
                                Tindakan ini tidak bisa dibatalkan.
                            </p>
                        </div>
                    </div>

                    <button
                        type="button"
                        class="rounded-lg p-2 hover:bg-muted disabled:cursor-not-allowed disabled:opacity-50"
                        :disabled="processing"
                        @click="closeConfirmModal"
                    >
                        <X class="h-4 w-4" />
                    </button>
                </div>

                <div class="mt-5 rounded-xl border bg-muted/40 p-4 text-sm">
                    <p class="font-medium">
                        File yang akan dipulihkan:
                    </p>
                    <p class="mt-1 break-all text-muted-foreground">
                        {{ selectedFile?.name }}
                    </p>
                    <p
                        v-if="selectedFile"
                        class="mt-1 text-xs text-muted-foreground"
                    >
                        Ukuran: {{ selectedFileSize }}
                    </p>
                </div>

                <div
                    class="mt-4 rounded-xl border border-red-200 bg-red-50 p-4 text-sm text-red-700 dark:border-red-900 dark:bg-red-950/40 dark:text-red-200"
                >
                    Pastikan file backup benar. Setelah restore berhasil, data lama tidak bisa dikembalikan kecuali kamu punya backup lain.
                </div>

                <div class="mt-6 flex justify-end gap-2">
                    <button
                        type="button"
                        class="rounded-lg border px-4 py-2 text-sm transition hover:bg-muted disabled:cursor-not-allowed disabled:opacity-50"
                        :disabled="processing"
                        @click="closeConfirmModal"
                    >
                        Batal
                    </button>

                    <button
                        type="button"
                        class="inline-flex items-center gap-2 rounded-lg bg-red-600 px-4 py-2 text-sm font-medium text-white transition hover:bg-red-700 disabled:cursor-not-allowed disabled:bg-slate-400"
                        :disabled="processing"
                        @click="importBackup"
                    >
                        <ArchiveRestore class="h-4 w-4" />
                        {{ processing ? 'Memulihkan...' : 'Ya, Restore' }}
                    </button>
                </div>
            </div>
        </div>
    </div>
</template>
