<script setup lang="ts">
import { Head, Link, useForm } from '@inertiajs/vue3';
import { ref, computed } from 'vue';
import { ArrowLeft, AlertCircle } from 'lucide-vue-next';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';

defineOptions({
    layout: {
        breadcrumbs: [
            { title: 'Dashboard', href: '/dashboard' },
            { title: 'Categories', href: '/categories' },
            { title: 'Edit Category', href: '/categories/1/edit' },
        ],
    },
});

interface Category {
    id: number;
    name: string;
    type: 'income' | 'expense';
    color: string;
    icon: string;
}

interface Props {
    category: Category;
}

const props = defineProps<Props>();

const colorPresets = [
    '#FF6B6B',
    '#4ECDC4',
    '#45B7D1',
    '#FFA07A',
    '#98D8C8',
    '#F7DC6F',
    '#BB8FCE',
    '#85C1E2',
    '#F8B739',
    '#52C41A',
    '#FF7A45',
    '#1890FF',
    '#722ED1',
    '#EB2F96',
    '#13C2C2',
];

const form = useForm({
    name: props.category.name,
    type: props.category.type,
    color: props.category.color,
    icon: props.category.icon,
});

const isSubmitting = ref(false);

const previewColor = computed(() => form.color);

const selectColor = (color: string) => {
    form.color = color;
};

const submit = () => {
    isSubmitting.value = true;
    form.put(`/categories/${props.category.id}`, {
        onFinish: () => {
            isSubmitting.value = false;
        },
    });
};
</script>

<template>
    <Head title="Edit Category - Loovie Apps" />

    <div class="max-w-2xl p-4 sm:p-6 lg:p-8">
        <!-- Header -->
        <div class="mb-6 flex items-center space-x-3">
            <Link
                href="/categories"
                class="text-gray-600 hover:text-gray-900 dark:text-gray-400 dark:hover:text-white"
            >
                <ArrowLeft class="h-6 w-6" />
            </Link>
            <div>
                <h1 class="text-3xl font-bold text-gray-900 dark:text-white">
                    Edit Category
                </h1>
                <p class="mt-1 text-gray-600 dark:text-gray-400">
                    Update category information
                </p>
            </div>
        </div>

        <!-- Form Card -->
        <div
            class="space-y-6 rounded-lg border border-gray-200 bg-white p-6 shadow-sm dark:border-gray-800 dark:bg-gray-900"
        >
            <!-- Name Field -->
            <div>
                <Label
                    for="name"
                    class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-300"
                >
                    Category Name <span class="text-red-500">*</span>
                </Label>
                <Input
                    id="name"
                    v-model="form.name"
                    type="text"
                    placeholder="e.g., Salary, Groceries, Rent"
                    class="w-full rounded-lg border-gray-200 bg-gray-50 dark:border-gray-700 dark:bg-gray-800"
                />
                <p
                    v-if="form.errors.name"
                    class="mt-2 flex items-center space-x-2 text-sm text-red-600 dark:text-red-400"
                >
                    <AlertCircle class="h-4 w-4" />
                    <span>{{ form.errors.name }}</span>
                </p>
            </div>

            <!-- Type Field -->
            <div>
                <Label
                    class="mb-3 block text-sm font-medium text-gray-700 dark:text-gray-300"
                >
                    Category Type <span class="text-red-500">*</span>
                </Label>
                <div class="grid grid-cols-2 gap-3">
                    <label class="relative">
                        <input
                            v-model="form.type"
                            type="radio"
                            value="income"
                            class="peer sr-only"
                        />
                        <div
                            class="cursor-pointer rounded-lg border-2 border-gray-200 p-4 transition-all peer-checked:border-green-500 peer-checked:bg-green-50 dark:border-gray-700 dark:peer-checked:bg-green-900/20"
                        >
                            <p
                                class="font-medium text-green-700 dark:text-green-400"
                            >
                                Income
                            </p>
                            <p class="text-sm text-gray-600 dark:text-gray-400">
                                Money coming in
                            </p>
                        </div>
                    </label>
                    <label class="relative">
                        <input
                            v-model="form.type"
                            type="radio"
                            value="expense"
                            class="peer sr-only"
                        />
                        <div
                            class="cursor-pointer rounded-lg border-2 border-gray-200 p-4 transition-all peer-checked:border-red-500 peer-checked:bg-red-50 dark:border-gray-700 dark:peer-checked:bg-red-900/20"
                        >
                            <p
                                class="font-medium text-red-700 dark:text-red-400"
                            >
                                Expense
                            </p>
                            <p class="text-sm text-gray-600 dark:text-gray-400">
                                Money going out
                            </p>
                        </div>
                    </label>
                </div>
                <p
                    v-if="form.errors.type"
                    class="mt-2 flex items-center space-x-2 text-sm text-red-600 dark:text-red-400"
                >
                    <AlertCircle class="h-4 w-4" />
                    <span>{{ form.errors.type }}</span>
                </p>
            </div>

            <!-- Color Field -->
            <div>
                <Label
                    class="mb-3 block text-sm font-medium text-gray-700 dark:text-gray-300"
                >
                    Color <span class="text-red-500">*</span>
                </Label>
                <div class="space-y-3">
                    <!-- Preview -->
                    <div class="flex items-center space-x-3">
                        <div
                            :style="{ backgroundColor: previewColor }"
                            class="h-16 w-16 rounded-lg border border-gray-200 shadow-sm dark:border-gray-700"
                        ></div>
                        <div>
                            <p class="text-sm text-gray-600 dark:text-gray-400">
                                Selected Color
                            </p>
                            <p
                                class="font-mono text-lg font-semibold text-gray-900 dark:text-white"
                            >
                                {{ previewColor }}
                            </p>
                        </div>
                    </div>

                    <!-- Color Presets -->
                    <div>
                        <p
                            class="mb-2 text-xs text-gray-600 dark:text-gray-400"
                        >
                            Quick Select
                        </p>
                        <div class="grid grid-cols-8 gap-2">
                            <button
                                v-for="color in colorPresets"
                                :key="color"
                                @click="selectColor(color)"
                                :style="{ backgroundColor: color }"
                                :class="[
                                    'h-10 w-10 rounded-lg border-2 transition-all',
                                    form.color === color
                                        ? 'border-gray-900 shadow-lg dark:border-white'
                                        : 'border-transparent hover:shadow-md',
                                ]"
                            />
                        </div>
                    </div>

                    <!-- Custom Color Input -->
                    <div>
                        <label class="text-xs text-gray-600 dark:text-gray-400"
                            >Custom Hex Color</label
                        >
                        <Input
                            v-model="form.color"
                            type="text"
                            placeholder="#4ECDC4"
                            class="mt-1 rounded-lg border-gray-200 bg-gray-50 font-mono dark:border-gray-700 dark:bg-gray-800"
                        />
                    </div>
                </div>
                <p
                    v-if="form.errors.color"
                    class="mt-2 flex items-center space-x-2 text-sm text-red-600 dark:text-red-400"
                >
                    <AlertCircle class="h-4 w-4" />
                    <span>{{ form.errors.color }}</span>
                </p>
            </div>

            <!-- Icon Field -->
            <div>
                <Label
                    for="icon"
                    class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-300"
                >
                    Icon (or text like "salary", "food")
                    <span class="text-red-500">*</span>
                </Label>
                <Input
                    id="icon"
                    v-model="form.icon"
                    type="text"
                    placeholder="e.g., payment, shopping, food"
                    class="w-full rounded-lg border-gray-200 bg-gray-50 dark:border-gray-700 dark:bg-gray-800"
                />
                <p
                    v-if="form.errors.icon"
                    class="mt-2 flex items-center space-x-2 text-sm text-red-600 dark:text-red-400"
                >
                    <AlertCircle class="h-4 w-4" />
                    <span>{{ form.errors.icon }}</span>
                </p>
            </div>

            <!-- Buttons -->
            <div
                class="flex items-center justify-end space-x-3 border-t border-gray-200 pt-4 dark:border-gray-700"
            >
                <Link href="/categories">
                    <Button variant="outline">Cancel</Button>
                </Link>
                <Button
                    @click="submit"
                    :disabled="
                        isSubmitting ||
                        !form.name ||
                        !form.type ||
                        !form.color ||
                        !form.icon
                    "
                    class="bg-indigo-600 text-white hover:bg-indigo-700 disabled:cursor-not-allowed disabled:opacity-50"
                >
                    {{ isSubmitting ? 'Saving...' : 'Save Changes' }}
                </Button>
            </div>
        </div>
    </div>
</template>
