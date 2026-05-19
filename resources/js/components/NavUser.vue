<script setup lang="ts">
import { computed } from 'vue';
import { Link, usePage } from '@inertiajs/vue3';
import {
    ChevronsUpDown,
    LogOut,
    Settings,
} from 'lucide-vue-next';

import {
    DropdownMenu,
    DropdownMenuContent,
    DropdownMenuItem,
    DropdownMenuLabel,
    DropdownMenuSeparator,
    DropdownMenuTrigger,
} from '@/components/ui/dropdown-menu';

import {
    SidebarMenu,
    SidebarMenuButton,
    SidebarMenuItem,
    useSidebar,
} from '@/components/ui/sidebar';

const page = usePage();
const { isMobile, state } = useSidebar();

const user = computed(() => page.props.auth?.user ?? null);

const userName = computed(() => user.value?.name || 'User');
const userEmail = computed(() => user.value?.email || '');
const userPhoto = computed(() => user.value?.photo_url || null);

const userInitial = computed(() => {
    const name = userName.value?.trim();

    if (!name) {
        return 'U';
    }

    return name.charAt(0).toUpperCase();
});
</script>

<template>
    <SidebarMenu>
        <SidebarMenuItem>
            <DropdownMenu>
                <DropdownMenuTrigger as-child>
                    <SidebarMenuButton
                        size="lg"
                        class="data-[state=open]:bg-sidebar-accent data-[state=open]:text-sidebar-accent-foreground"
                        data-test="sidebar-menu-button"
                    >
                        <div
                            class="flex h-8 w-8 shrink-0 items-center justify-center overflow-hidden rounded-lg bg-muted"
                        >
                            <img
                                v-if="userPhoto"
                                :src="userPhoto"
                                :alt="userName"
                                class="h-full w-full object-cover"
                                @error="$event.target.style.display = 'none'"
                            />

                            <span
                                v-else
                                class="text-sm font-semibold text-foreground"
                            >
                                {{ userInitial }}
                            </span>
                        </div>

                        <div class="grid flex-1 text-left text-sm leading-tight">
                            <span class="truncate font-medium">
                                {{ userName }}
                            </span>

                            <span class="truncate text-xs text-muted-foreground">
                                {{ userEmail }}
                            </span>
                        </div>

                        <ChevronsUpDown class="ml-auto size-4" />
                    </SidebarMenuButton>
                </DropdownMenuTrigger>

                <DropdownMenuContent
                    class="w-(--reka-dropdown-menu-trigger-width) min-w-56 rounded-lg"
                    :side="
                        isMobile
                            ? 'bottom'
                            : state === 'collapsed'
                              ? 'left'
                              : 'bottom'
                    "
                    align="end"
                    :side-offset="4"
                >
                    <DropdownMenuLabel class="p-0 font-normal">
                        <div class="flex items-center gap-2 px-2 py-1.5 text-left text-sm">
                            <div
                                class="flex h-10 w-10 shrink-0 items-center justify-center overflow-hidden rounded-lg bg-muted"
                            >
                                <img
                                    v-if="userPhoto"
                                    :src="userPhoto"
                                    :alt="userName"
                                    class="h-full w-full object-cover"
                                    @error="$event.target.style.display = 'none'"
                                />

                                <span
                                    v-else
                                    class="text-base font-semibold text-foreground"
                                >
                                    {{ userInitial }}
                                </span>
                            </div>

                            <div class="grid flex-1 text-left text-sm leading-tight">
                                <span class="truncate font-medium">
                                    {{ userName }}
                                </span>

                                <span class="truncate text-xs text-muted-foreground">
                                    {{ userEmail }}
                                </span>
                            </div>
                        </div>
                    </DropdownMenuLabel>

                    <DropdownMenuSeparator />

                    <DropdownMenuItem as-child>
                        <Link
                            href="/settings/profile"
                            class="flex w-full items-center"
                        >
                            <Settings class="mr-2 size-4" />
                            <span>Settings</span>
                        </Link>
                    </DropdownMenuItem>

                    <DropdownMenuSeparator />

                    <DropdownMenuItem as-child>
                        <Link
                            href="/logout"
                            method="post"
                            as="button"
                            class="flex w-full items-center"
                        >
                            <LogOut class="mr-2 size-4" />
                            <span>Log out</span>
                        </Link>
                    </DropdownMenuItem>
                </DropdownMenuContent>
            </DropdownMenu>
        </SidebarMenuItem>
    </SidebarMenu>
</template>
