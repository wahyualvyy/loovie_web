<script setup lang="ts">
import { Trash2, X } from 'lucide-vue-next';
import { ref, watch } from 'vue';

type Props = {
    show: boolean;
    title?: string;
    description?: string;
    itemName?: string;
    processing?: boolean;
    confirmLabel?: string;
};

const props = withDefaults(defineProps<Props>(), {
    title: 'Hapus Data?',
    description: 'Data yang dihapus tidak bisa dikembalikan.',
    itemName: '',
    processing: false,
    confirmLabel: 'Ya, Hapus',
});

const emit = defineEmits<{
    close: [];
    confirm: [];
}>();

const localShow = ref(props.show);

watch(
    () => props.show,
    (value) => {
        localShow.value = value;
    },
);

const closeModal = () => {
    localShow.value = false;
    emit('close');
};

const confirmDelete = () => {
    if (props.processing) return;

    emit('confirm');
    emit('close');
    localShow.value = false;
};
</script>

<template>
    <div
        v-if="localShow"
        class="fixed inset-0 z-50 flex items-center justify-center bg-black/50 p-4"
        @click.self="closeModal"
    >
        <div class="w-full max-w-md rounded-2xl border bg-background p-6 shadow-xl">
            <div class="flex items-start justify-between gap-4">
                <div class="flex items-start gap-4">
                    <div
                        class="flex h-12 w-12 shrink-0 items-center justify-center rounded-full bg-red-100 text-red-600 dark:bg-red-950 dark:text-red-300"
                    >
                        <Trash2 class="h-6 w-6" />
                    </div>

                    <div>
                        <h2 class="text-lg font-semibold">
                            {{ title }}
                        </h2>

                        <p class="mt-1 text-sm text-muted-foreground">
                            {{ description }}
                        </p>
                    </div>
                </div>

                <button
                    type="button"
                    class="rounded-lg p-2 hover:bg-muted disabled:cursor-not-allowed disabled:opacity-50"
                    :disabled="processing"
                    @click="closeModal"
                >
                    <X class="h-4 w-4" />
                </button>
            </div>

            <div
                v-if="itemName"
                class="mt-5 rounded-xl border bg-muted/40 p-4 text-sm"
            >
                <p class="text-muted-foreground">
                    Data yang akan dihapus:
                </p>

                <p class="mt-1 font-semibold">
                    {{ itemName }}
                </p>
            </div>

            <div class="mt-6 flex justify-end gap-2">
                <button
                    type="button"
                    class="rounded-lg border px-4 py-2 text-sm transition hover:bg-muted disabled:cursor-not-allowed disabled:opacity-50"
                    :disabled="processing"
                    @click="closeModal"
                >
                    Batal
                </button>

                <button
                    type="button"
                    class="inline-flex items-center gap-2 rounded-lg bg-red-600 px-4 py-2 text-sm font-medium text-white transition hover:bg-red-700 disabled:cursor-not-allowed disabled:bg-slate-400"
                    :disabled="processing"
                    @click="confirmDelete"
                >
                    <Trash2 class="h-4 w-4" />
                    {{ processing ? 'Menghapus...' : confirmLabel }}
                </button>
            </div>
        </div>
    </div>
</template>
