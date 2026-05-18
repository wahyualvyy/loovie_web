<script setup lang="ts">
import { Head, Link, router } from '@inertiajs/vue3';
import { ref, computed } from 'vue';
import { Edit2, Trash2, Plus, Search, Tag } from 'lucide-vue-next';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';

defineOptions({
    layout: {
        breadcrumbs: [
            { title: 'Dashboard', href: '/dashboard' },
            { title: 'Categories', href: '/categories' },
        ],
    },
});

interface Category {
    id: number;
    name: string;
    type: 'income' | 'expense';
    color: string;
    icon: string;
    created_at: string;
}

interface Props {
    categories: {
        data: Category[];
        total: number;
        per_page: number;
        current_page: number;
        last_page: number;
    };
}

const props = defineProps<Props>();
const searchQuery = ref('');
const filterType = ref<'all' | 'income' | 'expense'>('all');
const showDeleteConfirm = ref(false);
const categoryToDelete = ref<Category | null>(null);

const filteredCategories = computed(() => {
    let filtered = props.categories.data;

    if (searchQuery.value) {
        filtered = filtered.filter((cat) =>
            cat.name.toLowerCase().includes(searchQuery.value.toLowerCase()),
        );
    }

    if (filterType.value !== 'all') {
        filtered = filtered.filter((cat) => cat.type === filterType.value);
    }

    return filtered;
});

const incomeCount = computed(
    () => props.categories.data.filter((c) => c.type === 'income').length,
);
const expenseCount = computed(
    () => props.categories.data.filter((c) => c.type === 'expense').length,
);

const confirmDelete = (category: Category) => {
    categoryToDelete.value = category;
    showDeleteConfirm.value = true;
};

const deleteCategory = () => {
    if (categoryToDelete.value) {
        router.delete(route('categories.destroy', categoryToDelete.value.id), {
            onSuccess: () => {
                showDeleteConfirm.value = false;
                categoryToDelete.value = null;
            },
        });
    }
};

const getTypeLabel = (type: string) => {
    return type === 'income' ? 'Income' : 'Expense';
};

const getTypeColor = (type: string) => {
    return type === 'income'
        ? 'bg-green-100 text-green-800 dark:bg-green-900/30 dark:text-green-400'
        : 'bg-red-100 text-red-800 dark:bg-red-900/30 dark:text-red-400';
};
</script>

<template>
    <Head title="Categories - Loovie Apps" />

    <div class="space-y-6 p-4 sm:p-6 lg:p-8">
        <!-- Header -->
        <div
            class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between"
        >
            <div>
                <h1
                    class="text-3xl font-bold text-gray-900 sm:text-4xl dark:text-white"
                >
                    Categories
                </h1>
                <p class="mt-1 text-gray-600 dark:text-gray-400">
                    Manage transaction categories
                </p>
            </div>
            <Link :href="route('categories.create')">
                <Button class="bg-indigo-600 text-white hover:bg-indigo-700">
                    <Plus class="mr-2 h-4 w-4" />
                    Add Category
                </Button>
            </Link>
        </div>

        <!-- Summary Stats -->
        <div class="grid grid-cols-2 gap-4 md:grid-cols-3">
            <div
                class="rounded-lg border border-gray-200 bg-white p-4 shadow-sm dark:border-gray-800 dark:bg-gray-900"
            >
                <p class="text-sm text-gray-600 dark:text-gray-400">
                    Total Categories
                </p>
                <p
                    class="mt-1 text-2xl font-bold text-gray-900 dark:text-white"
                >
                    {{ categories.data.length }}
                </p>
            </div>
            <div
                class="rounded-lg border border-gray-200 bg-white p-4 shadow-sm dark:border-gray-800 dark:bg-gray-900"
            >
                <p class="text-sm text-gray-600 dark:text-gray-400">Income</p>
                <p
                    class="mt-1 text-2xl font-bold text-green-600 dark:text-green-400"
                >
                    {{ incomeCount }}
                </p>
            </div>
            <div
                class="rounded-lg border border-gray-200 bg-white p-4 shadow-sm dark:border-gray-800 dark:bg-gray-900"
            >
                <p class="text-sm text-gray-600 dark:text-gray-400">Expense</p>
                <p
                    class="mt-1 text-2xl font-bold text-red-600 dark:text-red-400"
                >
                    {{ expenseCount }}
                </p>
            </div>
        </div>

        <!-- Filters -->
        <div
            class="space-y-4 rounded-lg border border-gray-200 bg-white p-4 shadow-sm dark:border-gray-800 dark:bg-gray-900"
        >
            <div class="grid grid-cols-1 gap-4 md:grid-cols-2">
                <div class="relative">
                    <Search
                        class="absolute top-3 left-3 h-5 w-5 text-gray-400"
                    />
                    <Input
                        v-model="searchQuery"
                        type="text"
                        placeholder="Search categories..."
                        class="rounded-lg border-gray-200 bg-gray-50 pl-10 dark:border-gray-700 dark:bg-gray-800"
                    />
                </div>
                <select
                    v-model="filterType"
                    class="rounded-lg border border-gray-200 bg-gray-50 px-4 py-2 text-gray-900 focus:ring-2 focus:ring-indigo-500 dark:border-gray-700 dark:bg-gray-800 dark:text-white"
                >
                    <option value="all">All Types</option>
                    <option value="income">Income Only</option>
                    <option value="expense">Expense Only</option>
                </select>
            </div>
        </div>

        <!-- Categories Grid -->
        <div class="grid grid-cols-1 gap-4 md:grid-cols-2 lg:grid-cols-3">
            <div
                v-if="filteredCategories.length === 0"
                class="col-span-full py-12 text-center"
            >
                <p class="text-gray-500 dark:text-gray-400">
                    No categories found
                </p>
            </div>

            <div
                v-for="category in filteredCategories"
                :key="category.id"
                class="rounded-lg border border-gray-200 bg-white p-4 shadow-sm transition-shadow hover:shadow-md dark:border-gray-800 dark:bg-gray-900"
            >
                <div class="mb-3 flex items-start justify-between">
                    <div class="flex items-center space-x-3">
                        <div
                            :style="{ backgroundColor: category.color }"
                            class="flex h-10 w-10 items-center justify-center rounded-lg text-sm font-bold text-white"
                        >
                            {{ category.icon.charAt(0).toUpperCase() }}
                        </div>
                        <div>
                            <p
                                class="font-medium text-gray-900 dark:text-white"
                            >
                                {{ category.name }}
                            </p>
                            <span
                                :class="[
                                    'rounded px-2 py-1 text-xs font-medium',
                                    getTypeColor(category.type),
                                ]"
                            >
                                {{ getTypeLabel(category.type) }}
                            </span>
                        </div>
                    </div>
                    <div class="flex items-center space-x-1">
                        <Link :href="route('categories.edit', category.id)">
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
                            @click="confirmDelete(category)"
                            class="text-red-600 hover:text-red-700"
                        >
                            <Trash2 class="h-4 w-4" />
                        </Button>
                    </div>
                </div>
                <div
                    :style="{ backgroundColor: category.color + '20' }"
                    class="rounded px-3 py-2 text-center font-mono text-sm text-gray-700 dark:text-gray-300"
                >
                    {{ category.color }}
                </div>
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
                Delete Category
            </h3>
            <p class="mb-6 text-gray-600 dark:text-gray-400">
                Are you sure you want to delete
                <strong>{{ categoryToDelete?.name }}</strong
                >? This action cannot be undone.
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
                    @click="deleteCategory"
                    class="bg-red-600 px-4 py-2 text-white hover:bg-red-700"
                >
                    Delete
                </Button>
            </div>
        </div>
    </div>
</template>
