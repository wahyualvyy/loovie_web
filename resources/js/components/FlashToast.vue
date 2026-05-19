<script setup lang="ts">
import { usePage } from '@inertiajs/vue3';
import { watch } from 'vue';
import { toast, Toaster } from 'vue-sonner';
import 'vue-sonner/style.css';

const page = usePage();

let lastMessage = '';

watch(
    () => page.props.flash,
    (flash: any) => {
        if (!flash) return;

        const message =
            flash.success ||
            flash.error ||
            flash.warning ||
            flash.info ||
            '';

        if (!message || message === lastMessage) return;

        lastMessage = message;

        if (flash.success) toast.success(flash.success);
        else if (flash.error) toast.error(flash.error);
        else if (flash.warning) toast.warning(flash.warning);
        else if (flash.info) toast.info(flash.info);
    },
    {
        deep: true,
    },
);
</script>

<template>
    <Toaster
        position="top-right"
        rich-colors
        close-button
        expand
    />
</template>
