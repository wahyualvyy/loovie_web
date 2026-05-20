<script setup lang="ts">
import { Head, Link, router } from '@inertiajs/vue3';
import { computed, ref } from 'vue';
import {
    Edit2,
    Trash2,
    Plus,
    Search,
    Tag,
    TrendingUp,
    TrendingDown,
} from 'lucide-vue-next';

import DeleteConfirmModal from '@/components/DeleteConfirmModal.vue';
import EmptyState from '@/components/EmptyState.vue';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';

defineOptions({
    layout: {
        breadcrumbs: [
            { title: 'Dashboard', href: '/dashboard' },
            { title: 'Kategori', href: '/categories' },
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

const showDeleteModal = ref(false);
const categoryToDelete = ref<Category | null>(null);
const deleteProcessing = ref(false);

const filteredCategories = computed(() => {
    let filtered = props.categories.data;

    if (searchQuery.value) {
        const keyword = searchQuery.value.toLowerCase();

        filtered = filtered.filter((category) => {
            return (
                category.name.toLowerCase().includes(keyword) ||
                category.type.toLowerCase().includes(keyword) ||
                (category.icon || '').toLowerCase().includes(keyword)
            );
        });
    }

    if (filterType.value !== 'all') {
        filtered = filtered.filter((category) => category.type === filterType.value);
    }

    return filtered;
});

const incomeCount = computed(() => {
    return props.categories.data.filter((category) => category.type === 'income').length;
});

const expenseCount = computed(() => {
    return props.categories.data.filter((category) => category.type === 'expense').length;
});

const isFiltered = computed(() => {
    return Boolean(searchQuery.value || filterType.value !== 'all');
});

const resetFilters = () => {
    searchQuery.value = '';
    filterType.value = 'all';
};

const openDeleteModal = (category: Category) => {
    categoryToDelete.value = category;
    showDeleteModal.value = true;
};

const closeDeleteModal = () => {
    if (deleteProcessing.value) return;

    categoryToDelete.value = null;
    showDeleteModal.value = false;
};

const deleteCategory = () => {
    if (!categoryToDelete.value) return;

    deleteProcessing.value = true;

    router.delete(`/categories/${categoryToDelete.value.id}`, {
        preserveScroll: true,
        onSuccess: () => closeDeleteModal(),
        onFinish: () => {
            deleteProcessing.value = false;
        },
    });
};

const getTypeLabel = (type: string) => {
    return type === 'income' ? 'Pemasukan' : 'Pengeluaran';
};

const getTypeColor = (type: string) => {
    return type === 'income'
        ? 'bg-green-100 text-green-800 dark:bg-green-900/30 dark:text-green-400'
        : 'bg-red-100 text-red-800 dark:bg-red-900/30 dark:text-red-400';
};

const getTypeIcon = (type: string) => {
    return type === 'income' ? TrendingUp : TrendingDown;
};

const getInitialIcon = (category: Category) => {
    if (category.icon) {
        return category.icon.charAt(0).toUpperCase();
    }

    return category.name.charAt(0).toUpperCase();
};

const getDeleteItemName = computed(() => {
    if (!categoryToDelete.value) return '';

    return `${categoryToDelete.value.name} (${getTypeLabel(categoryToDelete.value.type)})`;
});
</script>

<template>
    <Head title="Kategori - Loovie Apps" />

    <div class="space-y-6 p-4 sm:p-6 lg:p-8">
        <!-- Header -->
        <div
            class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between"
        >
            <div>
                <h1 class="text-3xl font-bold text-gray-900 sm:text-4xl dark:text-white">
                    Kategori
                </h1>
                <p class="mt-1 text-gray-600 dark:text-gray-400">
                    Kelola kategori pemasukan dan pengeluaran.
                </p>
            </div>

            <Link href="/categories/create">
                <Button class="bg-indigo-600 text-white hover:bg-indigo-700">
                    <Plus class="mr-2 h-4 w-4" />
                    Tambah Kategori
                </Button>
            </Link>
        </div>

        <!-- Summary Stats -->
        <div class="grid grid-cols-1 gap-4 md:grid-cols-3">
            <div class="rounded-xl border bg-card p-5 shadow-sm">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm text-muted-foreground">
                            Total Kategori
                        </p>
                        <p class="mt-2 text-2xl font-bold">
                            {{ categories.data.length }}
                        </p>
                    </div>

                    <Tag class="h-8 w-8 text-blue-500" />
                </div>
            </div>

            <div class="rounded-xl border bg-card p-5 shadow-sm">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm text-muted-foreground">
                            Pemasukan
                        </p>
                        <p class="mt-2 text-2xl font-bold text-green-600 dark:text-green-400">
                            {{ incomeCount }}
                        </p>
                    </div>

                    <TrendingUp class="h-8 w-8 text-green-600 dark:text-green-400" />
                </div>
            </div>

            <div class="rounded-xl border bg-card p-5 shadow-sm">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm text-muted-foreground">
                            Pengeluaran
                        </p>
                        <p class="mt-2 text-2xl font-bold text-red-600 dark:text-red-400">
                            {{ expenseCount }}
                        </p>
                    </div>

                    <TrendingDown class="h-8 w-8 text-red-600 dark:text-red-400" />
                </div>
            </div>
        </div>

        <!-- Filters -->
        <div
            class="space-y-4 rounded-xl border border-gray-200 bg-white p-4 shadow-sm dark:border-gray-800 dark:bg-gray-900"
        >
            <div class="grid grid-cols-1 gap-4 md:grid-cols-2">
                <div class="relative">
                    <Search class="absolute left-3 top-3 h-5 w-5 text-gray-400" />

                    <Input
                        v-model="searchQuery"
                        type="text"
                        placeholder="Cari kategori atau icon..."
                        class="rounded-lg border-gray-200 bg-gray-50 pl-10 dark:border-gray-700 dark:bg-gray-800"
                    />
                </div>

                <select
                    v-model="filterType"
                    class="rounded-lg border border-gray-200 bg-gray-50 px-4 py-2 text-gray-900 focus:ring-2 focus:ring-indigo-500 dark:border-gray-700 dark:bg-gray-800 dark:text-white"
                >
                    <option value="all">Semua Tipe</option>
                    <option value="income">Pemasukan Saja</option>
                    <option value="expense">Pengeluaran Saja</option>
                </select>
            </div>
        </div>

        <!-- Empty State -->
        <EmptyState
            v-if="filteredCategories.length === 0"
            :icon="Tag"
            :title="isFiltered ? 'Kategori tidak ditemukan' : 'Belum ada kategori'"
            :description="
                isFiltered
                    ? 'Tidak ada kategori yang cocok dengan filter atau kata kunci pencarian.'
                    : 'Tambahkan kategori pemasukan dan pengeluaran untuk mulai mencatat transaksi.'
            "
            :action-label="isFiltered ? 'Reset Filter' : 'Tambah Kategori'"
            :button-type="isFiltered ? 'button' : 'link'"
            :action-href="isFiltered ? '' : '/categories/create'"
            @action="resetFilters"
        />

        <!-- Categories Grid -->
        <div
            v-else
            class="grid grid-cols-1 gap-4 md:grid-cols-2 lg:grid-cols-3"
        >
            <div
                v-for="category in filteredCategories"
                :key="category.id"
                class="rounded-xl border border-gray-200 bg-white p-5 shadow-sm transition hover:shadow-md dark:border-gray-800 dark:bg-gray-900"
            >
                <div class="mb-4 flex items-start justify-between gap-3">
                    <div class="flex min-w-0 items-center gap-3">
                        <div
                            :style="{ backgroundColor: category.color }"
                            class="flex h-12 w-12 shrink-0 items-center justify-center rounded-xl text-sm font-bold text-white shadow-sm"
                        >
                            {{ getInitialIcon(category) }}
                        </div>

                        <div class="min-w-0">
                            <p class="truncate font-semibold text-gray-900 dark:text-white">
                                {{ category.name }}
                            </p>

                            <div class="mt-1 flex flex-wrap items-center gap-2">
                                <span
                                    :class="[
                                        'inline-flex items-center gap-1 rounded-full px-2 py-1 text-xs font-medium',
                                        getTypeColor(category.type),
                                    ]"
                                >
                                    <component
                                        :is="getTypeIcon(category.type)"
                                        class="h-3 w-3"
                                    />
                                    {{ getTypeLabel(category.type) }}
                                </span>

                                <span
                                    class="rounded-full bg-gray-100 px-2 py-1 text-xs font-medium text-gray-600 dark:bg-gray-800 dark:text-gray-300"
                                >
                                    {{ category.icon || 'No Icon' }}
                                </span>
                            </div>
                        </div>
                    </div>

                    <div class="flex shrink-0 items-center gap-1">
                        <Link
                            :href="`/categories/${category.id}/edit`"
                            class="inline-flex items-center rounded-lg border px-2 py-2 text-blue-600 transition hover:bg-blue-50 dark:border-blue-900 dark:hover:bg-blue-950/40"
                        >
                            <Edit2 class="h-4 w-4" />
                        </Link>

                        <button
                            type="button"
                            class="inline-flex items-center rounded-lg border border-red-200 px-2 py-2 text-red-600 transition hover:bg-red-50 dark:border-red-900 dark:hover:bg-red-950/40"
                            @click="openDeleteModal(category)"
                        >
                            <Trash2 class="h-4 w-4" />
                        </button>
                    </div>
                </div>

                <div
                    class="grid grid-cols-2 gap-3 border-t border-gray-100 pt-4 dark:border-gray-800"
                >
                    <div>
                        <p class="text-xs text-muted-foreground">
                            Warna
                        </p>
                        <div class="mt-1 flex items-center gap-2">
                            <span
                                :style="{ backgroundColor: category.color }"
                                class="h-4 w-4 rounded-full border"
                            ></span>
                            <span class="font-mono text-sm text-gray-700 dark:text-gray-300">
                                {{ category.color }}
                            </span>
                        </div>
                    </div>

                    <div>
                        <p class="text-xs text-muted-foreground">
                            Tipe
                        </p>
                        <p class="mt-1 text-sm font-medium">
                            {{ getTypeLabel(category.type) }}
                        </p>
                    </div>
                </div>
            </div>
        </div>

        <DeleteConfirmModal
            :show="showDeleteModal"
            title="Hapus Kategori?"
            description="Kategori ini akan dihapus permanen. Jika kategori memiliki transaksi, proses hapus bisa ditolak oleh sistem."
            :item-name="getDeleteItemName"
            :processing="deleteProcessing"
            confirm-label="Ya, Hapus"
            @close="closeDeleteModal"
            @confirm="deleteCategory"
        />
    </div>
</template>
