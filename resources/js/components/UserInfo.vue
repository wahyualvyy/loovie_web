<script setup lang="ts">
import { computed, ref, watch } from 'vue';
import { useInitials } from '@/composables/useInitials';
import type { User } from '@/types';

type UserWithPhoto = User & {
    avatar?: string | null;
    photo_url?: string | null;
    photo_path?: string | null;
};

type Props = {
    user: UserWithPhoto;
    showEmail?: boolean;
};

const props = withDefaults(defineProps<Props>(), {
    showEmail: false,
});

const { getInitials } = useInitials();

const imageError = ref(false);

const avatarUrl = computed(() => {
    return props.user?.photo_url || props.user?.avatar || null;
});

const shouldShowImage = computed(() => {
    return !!avatarUrl.value && !imageError.value;
});

watch(
    () => avatarUrl.value,
    () => {
        imageError.value = false;
    },
);
</script>

<template>
    <div
        class="flex h-8 w-8 shrink-0 items-center justify-center overflow-hidden rounded-lg bg-muted"
    >
        <img
            v-if="shouldShowImage"
            :src="avatarUrl"
            :alt="user.name"
            class="h-full w-full object-cover"
            @error="imageError = true"
        />

        <span
            v-else
            class="text-sm font-semibold text-foreground"
        >
            {{ getInitials(user.name) }}
        </span>
    </div>

    <div class="grid flex-1 text-left text-sm leading-tight">
        <span class="truncate font-medium">
            {{ user.name }}
        </span>

        <span
            v-if="showEmail"
            class="truncate text-xs text-muted-foreground"
        >
            {{ user.email }}
        </span>
    </div>
</template>
