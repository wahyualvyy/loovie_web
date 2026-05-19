<script setup lang="ts">
import { WalletCards } from 'lucide-vue-next';
</script>

<template>
    <div class="flex min-w-0 items-center gap-2">
        <!-- Logo Icon -->
        <div
            class="relative flex aspect-square size-10 shrink-0 items-center justify-center overflow-hidden rounded-xl shadow-md"
        >
            <div
                class="absolute inset-0 bg-gradient-to-br from-blue-500 via-indigo-500 to-purple-600"
            />

            <div
                class="absolute inset-0 bg-gradient-to-tr from-transparent via-white/10 to-white/20"
            />

            <div class="absolute inset-0 rounded-xl ring-1 ring-inset ring-white/20" />

            <WalletCards class="relative size-5 text-white drop-shadow-sm" />
        </div>

        <!-- Logo Text: otomatis hilang saat sidebar collapsed -->
        <div
            class="grid min-w-0 flex-1 text-left text-sm transition-all duration-200 group-data-[collapsible=icon]:hidden"
        >
            <div class="flex min-w-0 items-center gap-1.5">
                <span class="truncate text-base font-bold leading-tight tracking-tight">
                    Loovie
                </span>

                <span
                    class="shrink-0 text-base font-bold leading-tight tracking-tight text-indigo-500 dark:text-indigo-400"
                >
                    Apps
                </span>
            </div>

            <span class="truncate text-xs tracking-wide text-muted-foreground">
                Manajemen Keuangan
            </span>
        </div>
    </div>
</template>
