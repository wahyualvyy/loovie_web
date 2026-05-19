<script setup lang="ts">
import { Head, Link, useForm } from '@inertiajs/vue3';
import { ref, computed } from 'vue';
import { ArrowLeft, FileText } from 'lucide-vue-next';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';

defineOptions({
    layout: {
        breadcrumbs: [
            { title: 'Dashboard', href: '/dashboard' },
            { title: 'Notes', href: '/notes' },
            { title: 'Add Note', href: '/notes/create' },
        ],
    },
});

const form = useForm({
    title: '',
    content: '',
    note_date: new Date().toISOString().split('T')[0],
    label: '',
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

const submit = () => {
    form.post('/notes', {
        onError: (errors) => {
            console.error('Validation errors:', errors);
        },
    });
};
</script>

<template>
    <Head title="Add Note - Loovie Apps" />

    <div class="p-4 sm:p-6 lg:p-8">
        <!-- Header -->
        <div class="mb-6">
            <Link
                href="/notes"
                class="mb-4 inline-flex items-center text-indigo-600 hover:text-indigo-700"
            >
                <ArrowLeft class="mr-2 h-4 w-4" />
                Back to Notes
            </Link>
            <h1
                class="text-3xl font-bold text-gray-900 sm:text-4xl dark:text-white"
            >
                Add New Note
            </h1>
        </div>

        <div class="grid grid-cols-1 gap-6 lg:grid-cols-3">
            <!-- Form -->
            <div class="lg:col-span-2">
                <div
                    class="space-y-6 rounded-lg border border-gray-200 bg-white p-6 shadow-sm dark:border-gray-800 dark:bg-gray-900"
                >
                    <!-- Title -->
                    <div>
                        <label
                            class="mb-2 block text-sm font-semibold text-gray-900 dark:text-white"
                        >
                            Title <span class="text-red-500">*</span>
                        </label>
                        <Input
                            v-model="form.title"
                            type="text"
                            placeholder="Enter note title..."
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
                        <div class="mb-2 flex items-center justify-between">
                            <label
                                class="block text-sm font-semibold text-gray-900 dark:text-white"
                            >
                                Content <span class="text-red-500">*</span>
                            </label>
                            <span
                                class="text-xs text-gray-500 dark:text-gray-400"
                            >
                                {{ charCount }} characters,
                                {{ wordCount }} words
                            </span>
                        </div>
                        <textarea
                            v-model="form.content"
                            placeholder="Write your note here..."
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
                        <label
                            class="mb-2 block text-sm font-semibold text-gray-900 dark:text-white"
                        >
                            Date <span class="text-red-500">*</span>
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
                        <label
                            class="mb-2 block text-sm font-semibold text-gray-900 dark:text-white"
                        >
                            Label (Optional)
                        </label>
                        <Input
                            v-model="form.label"
                            type="text"
                            placeholder="e.g., Reminder, Savings Goal, Tax Planning..."
                            class="w-full rounded-lg border-gray-200 bg-gray-50 dark:border-gray-700 dark:bg-gray-800"
                        />
                        <p
                            class="mt-1 text-xs text-gray-500 dark:text-gray-400"
                        >
                            Add a label to organize and filter your notes
                        </p>
                    </div>

                    <!-- Form Actions -->
                    <div
                        class="flex gap-3 border-t border-gray-200 pt-6 dark:border-gray-700"
                    >
                        <Link href="/notes" class="flex-1">
                            <Button variant="outline" class="w-full"
                                >Cancel</Button
                            >
                        </Link>
                        <Button
                            @click="submit"
                            :disabled="form.processing"
                            class="flex-1 bg-indigo-600 text-white hover:bg-indigo-700"
                        >
                            {{
                                form.processing ? 'Creating...' : 'Create Note'
                            }}
                        </Button>
                    </div>
                </div>
            </div>

            <!-- Sidebar -->
            <div class="lg:col-span-1">
                <!-- Preview Card -->
                <div
                    v-if="form.title || form.content"
                    class="mb-6 rounded-lg border border-gray-200 bg-white p-6 shadow-sm dark:border-gray-800 dark:bg-gray-900"
                >
                    <p
                        class="mb-4 text-xs font-medium tracking-wide text-gray-600 uppercase dark:text-gray-400"
                    >
                        Preview
                    </p>
                    <div class="space-y-4">
                        <div v-if="form.title">
                            <p
                                class="mb-1 text-xs font-medium text-gray-500 dark:text-gray-400"
                            >
                                Title
                            </p>
                            <p
                                class="line-clamp-2 font-semibold text-gray-900 dark:text-white"
                            >
                                {{ form.title }}
                            </p>
                        </div>
                        <div v-if="form.content">
                            <p
                                class="mb-1 text-xs font-medium text-gray-500 dark:text-gray-400"
                            >
                                Content Preview
                            </p>
                            <p
                                class="line-clamp-3 text-sm text-gray-700 dark:text-gray-300"
                            >
                                {{ form.content }}
                            </p>
                        </div>
                        <div v-if="form.note_date">
                            <p
                                class="mb-1 text-xs font-medium text-gray-500 dark:text-gray-400"
                            >
                                Date
                            </p>
                            <p class="text-sm text-gray-900 dark:text-white">
                                {{
                                    new Date(form.note_date).toLocaleDateString(
                                        'id-ID',
                                        {
                                            day: 'numeric',
                                            month: 'long',
                                            year: 'numeric',
                                        },
                                    )
                                }}
                            </p>
                        </div>
                        <div v-if="form.label">
                            <p
                                class="mb-1 text-xs font-medium text-gray-500 dark:text-gray-400"
                            >
                                Label
                            </p>
                            <span
                                class="inline-block rounded-full bg-indigo-100 px-3 py-1 text-xs font-medium text-indigo-700 dark:bg-indigo-900/30 dark:text-indigo-400"
                            >
                                {{ form.label }}
                            </span>
                        </div>
                    </div>
                </div>

                <!-- Stats Card -->
                <div
                    class="rounded-lg border border-indigo-200 bg-gradient-to-br from-indigo-50 to-indigo-100 p-6 shadow-sm dark:border-indigo-700 dark:from-indigo-900/20 dark:to-indigo-800/20"
                >
                    <p
                        class="mb-4 text-xs font-medium tracking-wide text-indigo-700 uppercase dark:text-indigo-400"
                    >
                        Statistics
                    </p>
                    <div class="space-y-3">
                        <div class="flex items-center justify-between">
                            <span
                                class="text-sm text-gray-700 dark:text-gray-300"
                                >Characters</span
                            >
                            <span
                                class="font-bold text-gray-900 dark:text-white"
                                >{{ charCount }}</span
                            >
                        </div>
                        <div class="flex items-center justify-between">
                            <span
                                class="text-sm text-gray-700 dark:text-gray-300"
                                >Words</span
                            >
                            <span
                                class="font-bold text-gray-900 dark:text-white"
                                >{{ wordCount }}</span
                            >
                        </div>
                        <div
                            class="flex items-center justify-between border-t border-indigo-200 pt-2 dark:border-indigo-700"
                        >
                            <span
                                class="text-sm text-gray-700 dark:text-gray-300"
                                >Note Date</span
                            >
                            <span
                                class="text-sm font-medium text-gray-900 dark:text-white"
                            >
                                {{
                                    new Date(form.note_date).toLocaleDateString(
                                        'id-ID',
                                        { day: 'numeric', month: 'short' },
                                    )
                                }}
                            </span>
                        </div>
                    </div>
                </div>

                <!-- Info Card -->
                <div
                    class="mt-6 rounded-lg border border-blue-200 bg-blue-50 p-4 shadow-sm dark:border-blue-800 dark:bg-blue-900/20"
                >
                    <div class="flex gap-3">
                        <FileText
                            class="mt-0.5 h-5 w-5 flex-shrink-0 text-blue-600 dark:text-blue-400"
                        />
                        <div>
                            <p
                                class="text-sm font-medium text-blue-900 dark:text-blue-400"
                            >
                                Tip
                            </p>
                            <p
                                class="mt-1 text-xs text-blue-800 dark:text-blue-300"
                            >
                                Use labels to organize your notes by category.
                                You can filter notes by label later.
                            </p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>
