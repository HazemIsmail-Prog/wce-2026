<script setup lang="ts">
import { Link } from '@inertiajs/vue3';
import { ClipboardPen, LayoutGrid, Target, Trophy } from '@lucide/vue';
import { computed } from 'vue';
import { useCanManageScores } from '@/composables/useCanManageScores';
import AppLogo from '@/components/AppLogo.vue';
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
import { dashboard } from '@/routes';
import { index as predictionsIndex } from '@/routes/predictions';
import { index as resultsIndex } from '@/routes/results';
import { index as scoresIndex } from '@/routes/scores';
import type { NavItem } from '@/types';

const canManageScores = useCanManageScores();

const mainNavItems = computed<NavItem[]>(() => {
    const items: NavItem[] = [
        {
            title: 'Dashboard',
            href: dashboard(),
            icon: LayoutGrid,
        },
        {
            title: 'Predictions',
            href: predictionsIndex(),
            icon: Target,
        },
        {
            title: 'Results',
            href: resultsIndex(),
            icon: Trophy,
        },
    ];

    if (canManageScores.value) {
        items.push({
            title: 'Scores',
            href: scoresIndex(),
            icon: ClipboardPen,
        });
    }

    return items;
});
</script>

<template>
    <Sidebar
        collapsible="icon"
        variant="inset"
        class="border-r border-emerald-500/10 bg-sidebar"
    >
        <SidebarHeader class="border-b border-white/5 pb-4">
            <SidebarMenu>
                <SidebarMenuItem>
                    <SidebarMenuButton
                        size="lg"
                        as-child
                        class="hover:bg-emerald-500/10"
                    >
                        <Link :href="dashboard()">
                            <AppLogo />
                        </Link>
                    </SidebarMenuButton>
                </SidebarMenuItem>
            </SidebarMenu>
        </SidebarHeader>

        <SidebarContent>
            <NavMain :items="mainNavItems" />
        </SidebarContent>

        <SidebarFooter class="border-t border-white/5 pt-4">
            <NavUser />
        </SidebarFooter>
    </Sidebar>
    <slot />
</template>
