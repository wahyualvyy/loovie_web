<script setup lang="ts">
import { Head, Link, useForm } from '@inertiajs/vue3';
import { computed } from 'vue';
import {
    ArrowLeft,
    FileText,
    Save,
    NotebookText,
    Calendar,
    Tag,
    BarChart3,
    Lightbulb,
    Clock,
} from 'lucide-vue-next';

import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';

defineOptions({
    layout: {
        breadcrumbs: [
            { title: 'Dashboard', href: '/dashboard' },
            { title: 'Catatan', href: '/notes' },
            { title: 'Edit Catatan', href: '/notes/edit' },
        ],
    },
});

interface Note {
    id: number;
    title: string;
    content: string;
    note_date: string;
    label: string | null;
    created_at: string;
    updated_at: string;
}

interface Props {
    note: Note;
}

const props = defineProps<Props>();

const form = useForm({
    title: props.note.title,
    content: props.note.content,
    note_date: props.note.note_date,
    label: props.note.label || '',
    _method: 'PUT',
});

const wordCount = computed(() => {
    return form.content
        .trim()
        .split(/\s+/)
        .filter((word) => word.length > 0).length;
});

const charCount = computed(() => {
    return form.content.length;
});

const formattedDate = computed(() => {
    if (!form.note_date) return '-';

    return new Date(form.note_date).toLocaleDateString('id-ID', {
        day: 'numeric',
        month: 'long',
        year: 'numeric',
    });
});

const shortFormattedDate = computed(() => {
    if (!form.note_date) return '-';

    return new Date(form.note_date).toLocaleDateString('id-ID', {
        day: 'numeric',
        month: 'short',
    });
});

const createdDate = computed(() => {
    if (!props.note.created_at) return '-';

    return new Date(props.note.created_at).toLocaleDateString('id-ID', {
        day: 'numeric',
        month: 'short',
        year: 'numeric',
    });
});

const updatedDate = computed(() => {
    if (!props.note.updated_at) return '-';

    return new Date(props.note.updated_at).toLocaleDateString('id-ID', {
        day: 'numeric',
        month: 'short',
        year: 'numeric',
    });
});

const submit = () => {
    form.put(`/notes/${props.note.id}`, {
        preserveScroll: true,
    });
};
</script>

<template>
    <Head title="Edit Catatan - Loovie Apps" />

    <div class="space-y-6 p-4 sm:p-6 lg:p-8">
        <!-- Header -->
        <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
            <div>
                <Link
                    href="/notes"
                    class="mb-3 inline-flex items-center text-sm font-medium text-indigo-600 hover:text-indigo-700"
                >
                    <ArrowLeft class="mr-2 h-4 w-4" />
                    Kembali ke Catatan
                </Link>

                <h1 class="text-2xl font-bold text-gray-900 sm:text-3xl dark:text-white">
                    Edit Catatan
                </h1>

                <p class="mt-1 text-sm text-gray-600 dark:text-gray-400">
                    Perbarui catatan, pengingat, atau informasi penting yang sudah dibuat.
                </p>
            </div>
        </div>

        <div class="grid grid-cols-1 gap-6 lg:grid-cols-3">
            <!-- Form -->
            <div class="lg:col-span-2">
                <div
                    class="space-y-6 rounded-2xl border border-gray-200 bg-white p-6 shadow-sm dark:border-gray-800 dark:bg-gray-900"
                >
                    <!-- Title -->
                    <div>
                        <label class="mb-2 block text-sm font-semibold text-gray-900 dark:text-white">
                            Judul Catatan
                            <span class="text-red-500">*</span>
                        </label>

                        <Input
                            v-model="form.title"
                            type="text"
                            placeholder="Contoh: Target tabungan bulan ini"
                            class="w-full rounded-lg border-gray-200 bg-gray-50 dark:border-gray-700 dark:bg-gray-800"
                        />

                        <p
                            v-if="form.errors.title"
                            class="mt-2 text-sm text-red-600"
                        >
                            {{ form.errors.title }}
                        </p>
                    </div>

                    <!-- Content -->
                    <div>
                        <div class="mb-2 flex flex-col gap-1 sm:flex-row sm:items-center sm:justify-between">
                            <label class="block text-sm font-semibold text-gray-900 dark:text-white">
                                Isi Catatan
                                <span class="text-red-500">*</span>
                            </label>

                            <span class="text-xs text-gray-500 dark:text-gray-400">
                                {{ charCount }} karakter, {{ wordCount }} kata
                            </span>
                        </div>

                        <textarea
                            v-model="form.content"
                            placeholder="Tulis catatanmu di sini..."
                            rows="10"
                            class="w-full resize-none rounded-lg border border-gray-200 bg-gray-50 px-4 py-3 text-gray-900 focus:border-transparent focus:ring-2 focus:ring-indigo-500 dark:border-gray-700 dark:bg-gray-800 dark:text-white"
                        ></textarea>

                        <p
                            v-if="form.errors.content"
                            class="mt-2 text-sm text-red-600"
                        >
                            {{ form.errors.content }}
                        </p>
                    </div>

                    <!-- Date -->
                    <div>
                        <label class="mb-2 block text-sm font-semibold text-gray-900 dark:text-white">
                            Tanggal Catatan
                            <span class="text-red-500">*</span>
                        </label>

                        <Input
                            v-model="form.note_date"
                            type="date"
                            class="w-full rounded-lg border-gray-200 bg-gray-50 dark:border-gray-700 dark:bg-gray-800"
                        />

                        <p
                            v-if="form.errors.note_date"
                            class="mt-2 text-sm text-red-600"
                        >
                            {{ form.errors.note_date }}
                        </p>
                    </div>

                    <!-- Label -->
                    <div>
                        <label class="mb-2 block text-sm font-semibold text-gray-900 dark:text-white">
                            Label
                        </label>

                        <Input
                            v-model="form.label"
                            type="text"
                            placeholder="Contoh: Pengingat, Tabungan, Pajak, Tagihan"
                            class="w-full rounded-lg border-gray-200 bg-gray-50 dark:border-gray-700 dark:bg-gray-800"
                        />

                        <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">
                            Label membantu mengelompokkan dan memfilter catatan.
                        </p>

                        <p
                            v-if="form.errors.label"
                            class="mt-2 text-sm text-red-600"
                        >
                            {{ form.errors.label }}
                        </p>
                    </div>

                    <!-- Form Actions -->
                    <div class="flex flex-col gap-3 border-t border-gray-200 pt-6 sm:flex-row dark:border-gray-700">
                        <Link href="/notes" class="flex-1">
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
                <!-- Preview Card -->
                <div
                    class="rounded-2xl border border-gray-200 bg-white p-6 shadow-sm dark:border-gray-800 dark:bg-gray-900"
                >
                    <div class="mb-4 flex items-center gap-2">
                        <NotebookText class="h-5 w-5 text-indigo-500" />
                        <p class="text-sm font-semibold text-gray-900 dark:text-white">
                            Preview Catatan
                        </p>
                    </div>

                    <div class="space-y-4">
                        <div>
                            <p class="mb-1 text-xs font-medium text-gray-500 dark:text-gray-400">
                                Judul
                            </p>

                            <p class="line-clamp-2 font-semibold text-gray-900 dark:text-white">
                                {{ form.title || 'Tanpa Judul' }}
                            </p>
                        </div>

                        <div>
                            <p class="mb-1 text-xs font-medium text-gray-500 dark:text-gray-400">
                                Isi Catatan
                            </p>

                            <p class="line-clamp-4 text-sm text-gray-700 dark:text-gray-300">
                                {{ form.content || 'Belum ada isi catatan.' }}
                            </p>
                        </div>

                        <div>
                            <p class="mb-1 text-xs font-medium text-gray-500 dark:text-gray-400">
                                Tanggal
                            </p>

                            <div class="flex items-center gap-2 text-sm text-gray-900 dark:text-white">
                                <Calendar class="h-4 w-4 text-blue-500" />
                                {{ formattedDate }}
                            </div>
                        </div>

                        <div v-if="form.label">
                            <p class="mb-1 text-xs font-medium text-gray-500 dark:text-gray-400">
                                Label
                            </p>

                            <span
                                class="inline-flex items-center gap-1 rounded-full bg-indigo-100 px-3 py-1 text-xs font-medium text-indigo-700 dark:bg-indigo-900/30 dark:text-indigo-400"
                            >
                                <Tag class="h-3 w-3" />
                                {{ form.label }}
                            </span>
                        </div>

                        <div v-else>
                            <p class="mb-1 text-xs font-medium text-gray-500 dark:text-gray-400">
                                Label
                            </p>

                            <span class="text-xs text-gray-400">
                                Tanpa label
                            </span>
                        </div>
                    </div>
                </div>

                <!-- Stats Card -->
                <div
                    class="rounded-2xl border border-indigo-200 bg-gradient-to-br from-indigo-50 to-indigo-100 p-6 shadow-sm dark:border-indigo-700 dark:from-indigo-900/20 dark:to-indigo-800/20"
                >
                    <div class="mb-4 flex items-center gap-2">
                        <BarChart3 class="h-5 w-5 text-indigo-600 dark:text-indigo-400" />
                        <p class="text-sm font-semibold text-indigo-700 dark:text-indigo-400">
                            Statistik Catatan
                        </p>
                    </div>

                    <div class="space-y-3">
                        <div class="flex items-center justify-between">
                            <span class="text-sm text-gray-700 dark:text-gray-300">
                                Karakter
                            </span>

                            <span class="font-bold text-gray-900 dark:text-white">
                                {{ charCount }}
                            </span>
                        </div>

                        <div class="flex items-center justify-between">
                            <span class="text-sm text-gray-700 dark:text-gray-300">
                                Kata
                            </span>

                            <span class="font-bold text-gray-900 dark:text-white">
                                {{ wordCount }}
                            </span>
                        </div>

                        <div class="flex items-center justify-between border-t border-indigo-200 pt-2 dark:border-indigo-700">
                            <span class="text-sm text-gray-700 dark:text-gray-300">
                                Tanggal
                            </span>

                            <span class="text-sm font-medium text-gray-900 dark:text-white">
                                {{ shortFormattedDate }}
                            </span>
                        </div>

                        <div class="flex items-center justify-between">
                            <span class="text-sm text-gray-700 dark:text-gray-300">
                                Dibuat
                            </span>

                            <span class="text-xs font-medium text-gray-900 dark:text-white">
                                {{ createdDate }}
                            </span>
                        </div>

                        <div class="flex items-center justify-between">
                            <span class="text-sm text-gray-700 dark:text-gray-300">
                                Diperbarui
                            </span>

                            <span class="text-xs font-medium text-gray-900 dark:text-white">
                                {{ updatedDate }}
                            </span>
                        </div>
                    </div>
                </div>

                <!-- Info Card -->
                <div
                    class="rounded-2xl border border-blue-200 bg-blue-50 p-4 shadow-sm dark:border-blue-800 dark:bg-blue-900/20"
                >
                    <div class="flex gap-3">
                        <Lightbulb class="mt-0.5 h-5 w-5 shrink-0 text-blue-600 dark:text-blue-400" />

                        <div>
                            <p class="text-sm font-medium text-blue-900 dark:text-blue-400">
                                Info Catatan
                            </p>

                            <p class="mt-1 text-xs text-blue-800 dark:text-blue-300">
                                Catatan ini dibuat pada {{ createdDate }} dan terakhir diperbarui pada {{ updatedDate }}.
                            </p>
                        </div>
                    </div>
                </div>

                <!-- History Card -->
                <div
                    class="rounded-2xl border border-gray-200 bg-white p-4 shadow-sm dark:border-gray-800 dark:bg-gray-900"
                >
                    <div class="flex gap-3">
                        <Clock class="mt-0.5 h-5 w-5 shrink-0 text-gray-500" />

                        <div>
                            <p class="text-sm font-medium text-gray-900 dark:text-white">
                                Riwayat
                            </p>

                            <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">
                                Dibuat: {{ createdDate }}
                            </p>

                            <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">
                                Update terakhir: {{ updatedDate }}
                            </p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>
