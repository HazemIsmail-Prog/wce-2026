<script setup lang="ts">
import { Link, usePage } from '@inertiajs/vue3';
import { ClipboardPen, LayoutGrid, Target, Trophy } from '@lucide/vue';
import { computed } from 'vue';
import AppLogo from '@/components/AppLogo.vue';
import Breadcrumbs from '@/components/Breadcrumbs.vue';
import { Avatar, AvatarFallback, AvatarImage } from '@/components/ui/avatar';
import { Button } from '@/components/ui/button';
import {
    DropdownMenu,
    DropdownMenuContent,
    DropdownMenuTrigger,
} from '@/components/ui/dropdown-menu';
import UserMenuContent from '@/components/UserMenuContent.vue';
import { useCanManageScores } from '@/composables/useCanManageScores';
import { useCurrentUrl } from '@/composables/useCurrentUrl';
import { getInitials } from '@/composables/useInitials';
import { cn } from '@/lib/utils';
import { dashboard } from '@/routes';
import { index as predictionsIndex } from '@/routes/predictions';
import { index as resultsIndex } from '@/routes/results';
import { index as scoresIndex } from '@/routes/scores';
import type { BreadcrumbItem, NavItem } from '@/types';

type Props = {
    breadcrumbs?: BreadcrumbItem[];
};

const props = withDefaults(defineProps<Props>(), {
    breadcrumbs: () => [],
});

const page = usePage();
const auth = computed(() => page.props.auth);
const canManageScores = useCanManageScores();
const { whenCurrentUrl } = useCurrentUrl();

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

const navLinkClass = (href: NavItem['href']) =>
    cn(
        'inline-flex shrink-0 items-center gap-1.5 rounded-full px-3 py-2 text-xs font-semibold transition sm:gap-2 sm:px-4 sm:text-sm',
        'text-slate-300 hover:bg-emerald-500/10 hover:text-emerald-200',
        whenCurrentUrl(
            href,
            'bg-emerald-500/15 text-emerald-300 ring-1 ring-emerald-500/30',
        ),
    );
</script>

<template>
    <header class="sticky top-0 z-50 border-b border-white/10 bg-background/95 backdrop-blur-md">
        <div class="mx-auto max-w-7xl px-3 sm:px-4">
            <div class="flex h-14 items-center justify-between gap-3">
                <Link
                    :href="dashboard()"
                    class="flex min-w-0 items-center gap-x-2"
                >
                    <AppLogo compact />
                </Link>

                <DropdownMenu>
                    <DropdownMenuTrigger as-child>
                        <Button
                            variant="ghost"
                            size="icon"
                            class="relative size-10 shrink-0 rounded-full p-1 focus-within:ring-2 focus-within:ring-emerald-500/40"
                        >
                            <Avatar
                                class="size-8 overflow-hidden rounded-full ring-1 ring-emerald-500/30"
                            >
                                <AvatarImage
                                    v-if="auth.user.avatar"
                                    :src="auth.user.avatar"
                                    :alt="auth.user.name"
                                />
                                <AvatarFallback
                                    class="rounded-full bg-emerald-500/20 text-sm font-semibold text-emerald-200"
                                >
                                    {{ getInitials(auth.user?.name) }}
                                </AvatarFallback>
                            </Avatar>
                        </Button>
                    </DropdownMenuTrigger>
                    <DropdownMenuContent align="end" class="w-56">
                        <UserMenuContent :user="auth.user" />
                    </DropdownMenuContent>
                </DropdownMenu>
            </div>

            <nav
                class="scrollbar-none -mx-3 flex gap-1.5 overflow-x-auto px-3 pb-3 sm:mx-0 sm:gap-2 sm:px-0"
                aria-label="Main navigation"
            >
                <Link
                    v-for="item in mainNavItems"
                    :key="item.title"
                    :href="item.href"
                    :class="navLinkClass(item.href)"
                >
                    <component :is="item.icon" class="size-4 shrink-0" />
                    <span>{{ item.title }}</span>
                </Link>
            </nav>
        </div>

        <div
            v-if="props.breadcrumbs.length > 1"
            class="border-t border-white/10 bg-background/50"
        >
            <div
                class="mx-auto flex h-10 w-full items-center overflow-x-auto px-3 text-muted-foreground sm:px-4 md:max-w-7xl"
            >
                <Breadcrumbs :breadcrumbs="breadcrumbs" />
            </div>
        </div>
    </header>
</template>

<style scoped>
.scrollbar-none {
    -ms-overflow-style: none;
    scrollbar-width: none;
}

.scrollbar-none::-webkit-scrollbar {
    display: none;
}
</style>
