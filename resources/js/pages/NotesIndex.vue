<script setup lang="ts">
import { Head, Link, router, useForm } from '@inertiajs/vue3';
import { ref, computed } from 'vue';
import {
    Edit2,
    Trash2,
    Plus,
    Search,
    Tag,
    Download,
    Printer,
} from 'lucide-vue-next';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';

defineOptions({
    layout: {
        breadcrumbs: [
            { title: 'Dashboard', href: '/dashboard' },
            { title: 'Notes', href: '/notes' },
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
const showDeleteConfirm = ref(false);
const noteToDelete = ref<NoteItem | null>(null);

const form = useForm({
    search: props.filters.search || '',
    label: props.filters.label || '',
    date_from: props.filters.date_from || '',
    date_to: props.filters.date_to || '',
    month: props.filters.month || '',
});

const confirmDelete = (note: NoteItem) => {
    noteToDelete.value = note;
    showDeleteConfirm.value = true;
};

const deleteNote = () => {
    if (noteToDelete.value) {
        router.delete(`/notes/${noteToDelete.value.id}`, {
            onSuccess: () => {
                showDeleteConfirm.value = false;
                noteToDelete.value = null;
            },
        });
    }
};

const applyFilters = () => {
    form.get('/notes');
};

const resetFilters = () => {
    form.reset();
    router.get('/notes');
};

const formatDate = (date: string) => {
    return new Date(date).toLocaleDateString('id-ID', {
        day: 'numeric',
        month: 'long',
        year: 'numeric',
    });
};

const truncateContent = (content: string, limit: number = 100) => {
    if (content.length <= limit) return content;
    return content.substring(0, limit) + '...';
};

const getLabelColor = (index: number) => {
    const colors = [
        'bg-blue-100 text-blue-800',
        'bg-purple-100 text-purple-800',
        'bg-pink-100 text-pink-800',
        'bg-green-100 text-green-800',
        'bg-yellow-100 text-yellow-800',
        'bg-red-100 text-red-800',
        'bg-indigo-100 text-indigo-800',
        'bg-orange-100 text-orange-800',
    ];
    return colors[index % colors.length];
};
</script>

<template>
    <Head title="Notes - Loovie Apps" />

    <div class="space-y-6 p-4 sm:p-6 lg:p-8">
        <!-- Header -->
        <div
            class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between"
        >
            <div>
                <h1
                    class="text-3xl font-bold text-gray-900 sm:text-4xl dark:text-white"
                >
                    Notes
                </h1>
                <p class="mt-1 text-gray-600 dark:text-gray-400">
                    Financial reminders and notes
                </p>
            </div>
            <Link href="/notes/create">
                <Button class="bg-indigo-600 text-white hover:bg-indigo-700">
                    <Plus class="mr-2 h-4 w-4" />
                    Add Note
                </Button>
            </Link>
        </div>

        <!-- Filters -->
        <div
            class="space-y-4 rounded-lg border border-gray-200 bg-white p-4 shadow-sm dark:border-gray-800 dark:bg-gray-900"
        >
            <div class="grid grid-cols-1 gap-4 md:grid-cols-2 lg:grid-cols-3">
                <!-- Search -->
                <div class="relative">
                    <Search
                        class="absolute top-3 left-3 h-5 w-5 text-gray-400"
                    />
                    <Input
                        v-model="form.search"
                        type="text"
                        placeholder="Search title or content..."
                        class="rounded-lg border-gray-200 bg-gray-50 pl-10 dark:border-gray-700 dark:bg-gray-800"
                    />
                </div>

                <!-- Label Filter -->
                <select
                    v-model="form.label"
                    class="rounded-lg border border-gray-200 bg-gray-50 px-4 py-2 text-gray-900 focus:ring-2 focus:ring-indigo-500 dark:border-gray-700 dark:bg-gray-800 dark:text-white"
                >
                    <option value="">All Labels</option>
                    <option v-for="lbl in labels" :key="lbl" :value="lbl">
                        {{ lbl }}
                    </option>
                </select>
            </div>

            <div class="grid grid-cols-1 gap-4 md:grid-cols-3">
                <!-- Date From -->
                <div>
                    <label
                        class="mb-1 block text-sm text-gray-600 dark:text-gray-400"
                        >From Date</label
                    >
                    <Input
                        v-model="form.date_from"
                        type="date"
                        class="w-full rounded-lg border-gray-200 bg-gray-50 dark:border-gray-700 dark:bg-gray-800"
                    />
                </div>

                <!-- Date To -->
                <div>
                    <label
                        class="mb-1 block text-sm text-gray-600 dark:text-gray-400"
                        >To Date</label
                    >
                    <Input
                        v-model="form.date_to"
                        type="date"
                        class="w-full rounded-lg border-gray-200 bg-gray-50 dark:border-gray-700 dark:bg-gray-800"
                    />
                </div>

                <!-- Month Filter -->
                <div>
                    <label
                        class="mb-1 block text-sm text-gray-600 dark:text-gray-400"
                        >Or Select Month</label
                    >
                    <Input
                        v-model="form.month"
                        type="month"
                        class="w-full rounded-lg border-gray-200 bg-gray-50 dark:border-gray-700 dark:bg-gray-800"
                    />
                </div>
            </div>

            <!-- Filter Buttons -->
            <div class="flex justify-end gap-2">
                <Button
                    variant="outline"
                    @click="resetFilters"
                    class="text-gray-700 dark:text-gray-300"
                >
                    Reset
                </Button>
                <Button
                    @click="applyFilters"
                    class="bg-indigo-600 text-white hover:bg-indigo-700"
                >
                    Apply Filters
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

        <!-- Notes Grid -->
        <div
            v-if="notes.data.length === 0"
            class="rounded-lg border border-gray-200 bg-white p-12 text-center shadow-sm dark:border-gray-800 dark:bg-gray-900"
        >
            <div
                class="mb-4 inline-flex h-16 w-16 items-center justify-center rounded-full bg-gray-100 dark:bg-gray-800"
            >
                <Tag class="h-8 w-8 text-gray-400" />
            </div>
            <h3
                class="mb-2 text-xl font-semibold text-gray-900 dark:text-white"
            >
                No notes yet
            </h3>
            <p class="mb-6 text-gray-600 dark:text-gray-400">
                Create your first note to get started
            </p>
            <Link href="/notes/create">
                <Button class="bg-indigo-600 text-white hover:bg-indigo-700">
                    <Plus class="mr-2 h-4 w-4" />
                    Create Note
                </Button>
            </Link>
        </div>

        <div
            v-else
            class="grid grid-cols-1 gap-4 md:grid-cols-2 lg:grid-cols-3"
        >
            <div
                v-for="note in notes.data"
                :key="note.id"
                class="flex flex-col rounded-lg border border-gray-200 bg-white p-5 shadow-sm transition-shadow hover:shadow-md dark:border-gray-800 dark:bg-gray-900"
            >
                <!-- Header -->
                <div class="mb-3 flex items-start justify-between">
                    <div class="flex-1">
                        <h3
                            class="mb-1 line-clamp-2 font-semibold text-gray-900 dark:text-white"
                        >
                            {{ note.title }}
                        </h3>
                        <p class="text-sm text-gray-500 dark:text-gray-400">
                            {{ formatDate(note.note_date) }}
                        </p>
                    </div>
                    <div class="ml-2 flex items-center gap-1">
                        <Link :href="`/notes/${note.id}/edit`">
                            <Button
                                variant="ghost"
                                size="sm"
                                class="text-blue-600 hover:text-blue-700"
                            >
                                <Edit2 class="h-4 w-4" />
                            </Button>
                        </Link>
                        <Button
                            variant="ghost"
                            size="sm"
                            @click="confirmDelete(note)"
                            class="text-red-600 hover:text-red-700"
                        >
                            <Trash2 class="h-4 w-4" />
                        </Button>
                    </div>
                </div>

                <!-- Content -->
                <p
                    class="mb-4 line-clamp-3 flex-grow text-sm text-gray-700 dark:text-gray-300"
                >
                    {{ truncateContent(note.content, 120) }}
                </p>

                <!-- Label Badge -->
                <div
                    v-if="note.label"
                    class="border-t border-gray-200 pt-3 dark:border-gray-700"
                >
                    <span
                        :class="[
                            'inline-block rounded-full px-3 py-1 text-xs font-medium',
                            getLabelColor(notes.data.indexOf(note)),
                        ]"
                    >
                        {{ note.label }}
                    </span>
                </div>
            </div>
        </div>

        <!-- Pagination -->
        <div
            v-if="notes.last_page > 1"
            class="flex items-center justify-between"
        >
            <p class="text-sm text-gray-600 dark:text-gray-400">
                Showing {{ notes.from }} to {{ notes.to }} of
                {{ notes.total }} notes
            </p>
            <div class="flex gap-2">
                <Link
                    v-if="notes.current_page > 1"
                    :href="`/notes?page=${notes.current_page - 1}`"
                >
                    <Button variant="outline">Previous</Button>
                </Link>
                <Link
                    v-if="notes.current_page < notes.last_page"
                    :href="`/notes?page=${notes.current_page + 1}`"
                >
                    <Button variant="outline">Next</Button>
                </Link>
            </div>
        </div>
    </div>

    <!-- Delete Confirmation Modal -->
    <div
        v-if="showDeleteConfirm"
        class="fixed inset-0 z-50 flex items-center justify-center bg-black/50 p-4 dark:bg-black/70"
    >
        <div
            class="w-full max-w-sm rounded-lg bg-white p-6 shadow-xl dark:bg-gray-900"
        >
            <h3
                class="mb-4 text-lg font-semibold text-gray-900 dark:text-white"
            >
                Delete Note
            </h3>
            <p class="mb-6 text-gray-600 dark:text-gray-400">
                Are you sure you want to delete this note? This action cannot be
                undone.
            </p>
            <div class="flex items-center justify-end space-x-3">
                <Button
                    variant="outline"
                    @click="showDeleteConfirm = false"
                    class="px-4 py-2"
                >
                    Cancel
                </Button>
                <Button
                    @click="deleteNote"
                    class="bg-red-600 px-4 py-2 text-white hover:bg-red-700"
                >
                    Delete
                </Button>
            </div>
        </div>
    </div>
</template>
