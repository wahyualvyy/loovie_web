<script setup lang="ts">
import { Head, Link, router, useForm } from '@inertiajs/vue3';
import { computed, ref } from 'vue';
import {
    Edit2,
    Trash2,
    Plus,
    Search,
    Tag,
    Download,
    Printer,
    NotebookText,
} from 'lucide-vue-next';

import DeleteConfirmModal from '@/components/DeleteConfirmModal.vue';
import EmptyState from '@/components/EmptyState.vue';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';

defineOptions({
    layout: {
        breadcrumbs: [
            { title: 'Dashboard', href: '/dashboard' },
            { title: 'Catatan', href: '/notes' },
        ],
    },
});

interface NoteItem {
    id: number;
    title: string;
    content: string;
    note_date: string;
    label: string | null;
    created_at: string;
    updated_at: string;
}

interface Props {
    notes: {
        data: NoteItem[];
        total: number;
        per_page: number;
        current_page: number;
        last_page: number;
        from: number;
        to: number;
    };
    labels: string[];
    filters: {
        search: string | null;
        label: string | null;
        date_from: string | null;
        date_to: string | null;
        month: string | null;
    };
}

const props = defineProps<Props>();

const showDeleteModal = ref(false);
const noteToDelete = ref<NoteItem | null>(null);
const deleteProcessing = ref(false);

const form = useForm({
    search: props.filters.search || '',
    label: props.filters.label || '',
    date_from: props.filters.date_from || '',
    date_to: props.filters.date_to || '',
    month: props.filters.month || '',
});

const isFiltered = computed(() => {
    return Boolean(
        form.search ||
            form.label ||
            form.date_from ||
            form.date_to ||
            form.month,
    );
});

const openDeleteModal = (note: NoteItem) => {
    noteToDelete.value = note;
    showDeleteModal.value = true;
};

const closeDeleteModal = () => {
    if (deleteProcessing.value) return;

    noteToDelete.value = null;
    showDeleteModal.value = false;
};

const deleteNote = () => {
    if (!noteToDelete.value) return;

    deleteProcessing.value = true;

    router.delete(`/notes/${noteToDelete.value.id}`, {
        preserveScroll: true,
        onSuccess: () => closeDeleteModal(),
        onFinish: () => {
            deleteProcessing.value = false;
        },
    });
};

const applyFilters = () => {
    form.get('/notes', {
        preserveScroll: true,
        preserveState: true,
    });
};

const resetFilters = () => {
    form.reset();

    router.get(
        '/notes',
        {},
        {
            preserveScroll: true,
            preserveState: true,
        },
    );
};

const formatDate = (date: string) => {
    if (!date) return '-';

    return new Date(date).toLocaleDateString('id-ID', {
        day: 'numeric',
        month: 'long',
        year: 'numeric',
    });
};

const truncateContent = (content: string | null | undefined, limit = 100) => {
    if (!content) return '-';

    if (content.length <= limit) return content;

    return `${content.substring(0, limit)}...`;
};

const getLabelColor = (index: number) => {
    const colors = [
        'bg-blue-100 text-blue-800 dark:bg-blue-950 dark:text-blue-300',
        'bg-purple-100 text-purple-800 dark:bg-purple-950 dark:text-purple-300',
        'bg-pink-100 text-pink-800 dark:bg-pink-950 dark:text-pink-300',
        'bg-green-100 text-green-800 dark:bg-green-950 dark:text-green-300',
        'bg-yellow-100 text-yellow-800 dark:bg-yellow-950 dark:text-yellow-300',
        'bg-red-100 text-red-800 dark:bg-red-950 dark:text-red-300',
        'bg-indigo-100 text-indigo-800 dark:bg-indigo-950 dark:text-indigo-300',
        'bg-orange-100 text-orange-800 dark:bg-orange-950 dark:text-orange-300',
    ];

    return colors[index % colors.length];
};

const getDeleteItemName = computed(() => {
    if (!noteToDelete.value) return '';

    return noteToDelete.value.title || 'Catatan';
});
</script>

<template>
    <Head title="Catatan - Loovie Apps" />

    <div class="space-y-6 p-4 sm:p-6 lg:p-8">
        <!-- Header -->
        <div
            class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between"
        >
            <div>
                <h1 class="text-3xl font-bold text-gray-900 sm:text-4xl dark:text-white">
                    Catatan
                </h1>
                <p class="mt-1 text-gray-600 dark:text-gray-400">
                    Simpan catatan, pengingat, dan informasi penting keuanganmu.
                </p>
            </div>

            <Link href="/notes/create">
                <Button class="bg-indigo-600 text-white hover:bg-indigo-700">
                    <Plus class="mr-2 h-4 w-4" />
                    Tambah Catatan
                </Button>
            </Link>
        </div>

        <!-- Summary -->
        <div class="grid grid-cols-1 gap-4 md:grid-cols-3">
            <div class="rounded-xl border bg-card p-5 shadow-sm">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm text-muted-foreground">
                            Total Catatan
                        </p>
                        <p class="mt-2 text-2xl font-bold">
                            {{ notes.total || 0 }}
                        </p>
                    </div>

                    <NotebookText class="h-8 w-8 text-blue-500" />
                </div>
            </div>

            <div class="rounded-xl border bg-card p-5 shadow-sm">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm text-muted-foreground">
                            Label
                        </p>
                        <p class="mt-2 text-2xl font-bold">
                            {{ labels.length || 0 }}
                        </p>
                    </div>

                    <Tag class="h-8 w-8 text-indigo-500" />
                </div>
            </div>

            <div class="rounded-xl border bg-card p-5 shadow-sm">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm text-muted-foreground">
                            Data Ditampilkan
                        </p>
                        <p class="mt-2 text-2xl font-bold text-emerald-500">
                            {{ notes.data.length || 0 }}
                        </p>
                    </div>

                    <Search class="h-8 w-8 text-emerald-500" />
                </div>
            </div>
        </div>

        <!-- Filters -->
        <div
            class="space-y-4 rounded-xl border border-gray-200 bg-white p-4 shadow-sm dark:border-gray-800 dark:bg-gray-900"
        >
            <div class="grid grid-cols-1 gap-4 md:grid-cols-2 lg:grid-cols-3">
                <div class="relative">
                    <Search class="absolute left-3 top-3 h-5 w-5 text-gray-400" />
                    <Input
                        v-model="form.search"
                        type="text"
                        placeholder="Cari judul atau isi catatan..."
                        class="rounded-lg border-gray-200 bg-gray-50 pl-10 dark:border-gray-700 dark:bg-gray-800"
                    />
                </div>

                <select
                    v-model="form.label"
                    class="rounded-lg border border-gray-200 bg-gray-50 px-4 py-2 text-gray-900 focus:ring-2 focus:ring-indigo-500 dark:border-gray-700 dark:bg-gray-800 dark:text-white"
                >
                    <option value="">Semua Label</option>
                    <option v-for="label in labels" :key="label" :value="label">
                        {{ label }}
                    </option>
                </select>
            </div>

            <div class="grid grid-cols-1 gap-4 md:grid-cols-3">
                <div>
                    <label class="mb-1 block text-sm text-gray-600 dark:text-gray-400">
                        Dari Tanggal
                    </label>
                    <Input
                        v-model="form.date_from"
                        type="date"
                        class="w-full rounded-lg border-gray-200 bg-gray-50 dark:border-gray-700 dark:bg-gray-800"
                    />
                </div>

                <div>
                    <label class="mb-1 block text-sm text-gray-600 dark:text-gray-400">
                        Sampai Tanggal
                    </label>
                    <Input
                        v-model="form.date_to"
                        type="date"
                        class="w-full rounded-lg border-gray-200 bg-gray-50 dark:border-gray-700 dark:bg-gray-800"
                    />
                </div>

                <div>
                    <label class="mb-1 block text-sm text-gray-600 dark:text-gray-400">
                        Atau Pilih Bulan
                    </label>
                    <Input
                        v-model="form.month"
                        type="month"
                        class="w-full rounded-lg border-gray-200 bg-gray-50 dark:border-gray-700 dark:bg-gray-800"
                    />
                </div>
            </div>

            <div class="flex justify-end gap-2">
                <Button
                    variant="outline"
                    class="text-gray-700 dark:text-gray-300"
                    @click="resetFilters"
                >
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

        <!-- Export/Print Buttons -->
        <div class="flex flex-wrap gap-2">
            <a
                href="/notes/export/csv"
                class="inline-flex items-center gap-2 rounded-lg bg-blue-500 px-4 py-2 text-white transition hover:bg-blue-600"
            >
                <Download class="h-4 w-4" />
                Export CSV
            </a>

            <a
                href="/notes/export/print"
                target="_blank"
                class="inline-flex items-center gap-2 rounded-lg bg-gray-500 px-4 py-2 text-white transition hover:bg-gray-600"
            >
                <Printer class="h-4 w-4" />
                Print Report
            </a>
        </div>

        <!-- Empty State -->
        <EmptyState
            v-if="notes.data.length === 0"
            :icon="NotebookText"
            :title="isFiltered ? 'Catatan tidak ditemukan' : 'Belum ada catatan'"
            :description="
                isFiltered
                    ? 'Tidak ada catatan yang cocok dengan filter yang kamu gunakan.'
                    : 'Buat catatan pertama untuk menyimpan pengingat atau informasi penting.'
            "
            :action-label="isFiltered ? 'Reset Filter' : 'Tambah Catatan'"
            :button-type="isFiltered ? 'button' : 'link'"
            :action-href="isFiltered ? '' : '/notes/create'"
            @action="resetFilters"
        />

        <!-- Notes Grid -->
        <div
            v-else
            class="grid grid-cols-1 gap-4 md:grid-cols-2 lg:grid-cols-3"
        >
            <div
                v-for="(note, index) in notes.data"
                :key="note.id"
                class="flex flex-col rounded-xl border border-gray-200 bg-white p-5 shadow-sm transition-shadow hover:shadow-md dark:border-gray-800 dark:bg-gray-900"
            >
                <div class="mb-3 flex items-start justify-between gap-3">
                    <div class="min-w-0 flex-1">
                        <h3 class="mb-1 line-clamp-2 font-semibold text-gray-900 dark:text-white">
                            {{ note.title }}
                        </h3>
                        <p class="text-sm text-gray-500 dark:text-gray-400">
                            {{ formatDate(note.note_date) }}
                        </p>
                    </div>

                    <div class="flex shrink-0 items-center gap-1">
                        <Link
                            :href="`/notes/${note.id}/edit`"
                            class="inline-flex items-center rounded-lg border px-2 py-2 text-blue-600 transition hover:bg-blue-50 dark:border-blue-900 dark:hover:bg-blue-950/40"
                        >
                            <Edit2 class="h-4 w-4" />
                        </Link>

                        <button
                            type="button"
                            class="inline-flex items-center rounded-lg border border-red-200 px-2 py-2 text-red-600 transition hover:bg-red-50 dark:border-red-900 dark:hover:bg-red-950/40"
                            @click="openDeleteModal(note)"
                        >
                            <Trash2 class="h-4 w-4" />
                        </button>
                    </div>
                </div>

                <p class="mb-4 line-clamp-3 flex-grow text-sm text-gray-700 dark:text-gray-300">
                    {{ truncateContent(note.content, 120) }}
                </p>

                <div
                    class="mt-auto border-t border-gray-200 pt-3 dark:border-gray-700"
                >
                    <span
                        v-if="note.label"
                        :class="[
                            'inline-block rounded-full px-3 py-1 text-xs font-medium',
                            getLabelColor(index),
                        ]"
                    >
                        {{ note.label }}
                    </span>

                    <span
                        v-else
                        class="inline-block rounded-full bg-gray-100 px-3 py-1 text-xs font-medium text-gray-600 dark:bg-gray-800 dark:text-gray-300"
                    >
                        Tanpa Label
                    </span>
                </div>
            </div>
        </div>

        <!-- Pagination -->
        <div
            v-if="notes.last_page > 1"
            class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between"
        >
            <p class="text-sm text-gray-600 dark:text-gray-400">
                Menampilkan {{ notes.from }} sampai {{ notes.to }}
                dari {{ notes.total }} catatan
            </p>

            <div class="flex gap-2">
                <Link
                    v-if="notes.current_page > 1"
                    :href="`/notes?page=${notes.current_page - 1}`"
                >
                    <Button variant="outline">Sebelumnya</Button>
                </Link>

                <Link
                    v-if="notes.current_page < notes.last_page"
                    :href="`/notes?page=${notes.current_page + 1}`"
                >
                    <Button variant="outline">Berikutnya</Button>
                </Link>
            </div>
        </div>

        <DeleteConfirmModal
            :show="showDeleteModal"
            title="Hapus Catatan?"
            description="Catatan ini akan dihapus permanen dan tidak bisa dikembalikan."
            :item-name="getDeleteItemName"
            :processing="deleteProcessing"
            confirm-label="Ya, Hapus"
            @close="closeDeleteModal"
            @confirm="deleteNote"
        />
    </div>
</template>
