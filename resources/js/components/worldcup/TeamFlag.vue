<script setup lang="ts">
import { Flag } from '@lucide/vue';
import { computed, ref, watch } from 'vue';
import {
    isPlaceholderTeam,
    teamFlagUrl,
    teamInitials,
} from '@/lib/teamFlags';
import { cn } from '@/lib/utils';

const props = withDefaults(
    defineProps<{
        team: string;
        size?: 'sm' | 'md' | 'lg';
        class?: string;
    }>(),
    {
        size: 'md',
    },
);

const imageFailed = ref(false);

const dimension = computed(() => {
    switch (props.size) {
        case 'sm':
            return 40;
        case 'lg':
            return 64;
        default:
            return 56;
    }
});

const flagUrl = computed(() =>
    teamFlagUrl(props.team, dimension.value * 2),
);
const showFlag = computed(
    () => flagUrl.value !== null && !imageFailed.value,
);
const placeholder = computed(() => isPlaceholderTeam(props.team));

watch(
    () => props.team,
    () => {
        imageFailed.value = false;
    },
);

function onError(): void {
    imageFailed.value = true;
}
</script>

<template>
    <div
        :class="cn('team-flag', props.class)"
        :style="{
            width: `${dimension}px`,
            height: `${dimension}px`,
        }"
    >
        <img
            v-if="showFlag"
            :src="flagUrl!"
            :alt="`${team} flag`"
            class="team-flag__image"
            loading="lazy"
            @error="onError"
        />
        <div
            v-else
            class="team-flag__fallback"
            :class="placeholder ? 'team-flag__fallback--placeholder' : ''"
        >
            <Flag v-if="placeholder" class="size-1/2 opacity-60" />
            <span v-else class="text-sm font-black">{{
                teamInitials(team)
            }}</span>
        </div>
    </div>
</template>

<style scoped>
.team-flag {
    flex-shrink: 0;
    overflow: hidden;
    border-radius: 50%;
    box-shadow: 0 0 0 1px rgb(255 255 255 / 0.12);
}

.team-flag__image {
    display: block;
    width: 100%;
    height: 100%;
    border-radius: 50%;
    object-fit: cover;
    object-position: center;
}

.team-flag__fallback {
    display: flex;
    height: 100%;
    width: 100%;
    align-items: center;
    justify-content: center;
    border-radius: 50%;
    background: rgb(255 255 255 / 0.05);
    color: rgb(148 163 184);
}

.team-flag__fallback--placeholder {
    background: rgb(255 255 255 / 0.05);
}
</style>
