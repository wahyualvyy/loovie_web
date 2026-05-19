<script setup lang="ts">
import { Link, usePage } from '@inertiajs/vue3';
import { computed } from 'vue';
import {
    LayoutGrid,
    TrendingUp,
    FileText,
    CreditCard,
    Database,
} from 'lucide-vue-next';

import AppLogo from '@/components/AppLogo.vue';
import NavFooter from '@/components/NavFooter.vue';
import NavMain from '@/components/NavMain.vue';
import NavUser from '@/components/NavUser.vue';

import {
    Sidebar,
    SidebarContent,
    SidebarFooter,
    SidebarHeader,
    SidebarMenu,
    SidebarMenuButton,
    SidebarMenuItem,
} from '@/components/ui/sidebar';

import type { NavItem } from '@/types';

const page = usePage();

const authUser = computed(() => page.props.auth.user);

const mainNavItems = computed<NavItem[]>(() => {
    const items: NavItem[] = [
        {
            title: 'Dashboard',
            href: '/dashboard',
            icon: LayoutGrid,
        },
        {
            title: 'Transactions',
            href: '/transactions',
            icon: TrendingUp,
        },
        {
            title: 'Notes',
            href: '/notes',
            icon: FileText,
        },
        {
            title: 'Financial Accounts',
            href: '/financial-accounts',
            icon: CreditCard,
        },
    ];

    if (authUser.value?.role === 'admin') {
        items.push({
            title: 'Data Master',
            href: '/data-master/users',
            icon: Database,
        });
    }

    return items;
});

const footerNavItems: NavItem[] = [];
</script>

<template>
    <Sidebar collapsible="icon" variant="inset">
        <SidebarHeader>
            <SidebarMenu>
                <SidebarMenuItem>
                    <SidebarMenuButton size="lg" as-child>
                        <Link href="/dashboard">
                            <AppLogo />
                        </Link>
                    </SidebarMenuButton>
                </SidebarMenuItem>
            </SidebarMenu>
        </SidebarHeader>

        <SidebarContent>
            <NavMain :items="mainNavItems" />
        </SidebarContent>

        <SidebarFooter>
            <NavFooter :items="footerNavItems" />
            <NavUser />
        </SidebarFooter>
    </Sidebar>

    <slot />
</template>
