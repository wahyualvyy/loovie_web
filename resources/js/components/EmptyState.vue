<script setup lang="ts">
import { Link } from '@inertiajs/vue3';
import type { Component } from 'vue';

type Props = {
    icon?: Component;
    title: string;
    description?: string;
    actionLabel?: string;
    actionHref?: string;
    buttonType?: 'link' | 'button';
};

withDefaults(defineProps<Props>(), {
    description: '',
    actionLabel: '',
    actionHref: '',
    buttonType: 'link',
});

const emit = defineEmits<{
    action: [];
}>();
</script>

<template>
    <div class="rounded-2xl border border-dashed bg-card px-6 py-12 text-center">
        <div
            v-if="icon"
            class="mx-auto flex h-14 w-14 items-center justify-center rounded-2xl bg-muted text-muted-foreground"
        >
            <component :is="icon" class="h-7 w-7" />
        </div>

        <h3 class="mt-4 text-lg font-semibold">
            {{ title }}
        </h3>

        <p
            v-if="description"
            class="mx-auto mt-2 max-w-md text-sm text-muted-foreground"
        >
            {{ description }}
        </p>

        <Link
            v-if="buttonType === 'link' && actionLabel && actionHref"
            :href="actionHref"
            class="mt-5 inline-flex items-center justify-center rounded-lg bg-blue-600 px-4 py-2 text-sm font-medium text-white transition hover:bg-blue-700"
        >
            {{ actionLabel }}
        </Link>

        <button
            v-if="buttonType === 'button' && actionLabel"
            type="button"
            class="mt-5 inline-flex items-center justify-center rounded-lg bg-blue-600 px-4 py-2 text-sm font-medium text-white transition hover:bg-blue-700"
            @click="emit('action')"
        >
            {{ actionLabel }}
        </button>
    </div>
</template>
