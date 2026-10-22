<script setup lang="ts">
import { Link } from '@inertiajs/vue3';

// One row in the trainee detail page's Skills section. Prints only what the source states.
// `url` is null for a skill the Global client has not shipped, so the name prints with no
// link that would 404 on it (ADR-0011 §2). Nothing here ranks or recommends a skill.
defineProps<{
    skill: { name: string; sp_cost: number | null; is_unique: boolean; url: string | null };
}>();
</script>

<template>
    <li class="flex flex-wrap items-baseline gap-2 border-b border-rule py-1.5 last:border-b-0">
        <Link v-if="skill.url" :href="skill.url" class="text-ink hover:underline transition-colors duration-150 ease-out">{{ skill.name }}</Link>
        <span v-else class="text-ink">{{ skill.name }}</span>

        <span
            v-if="skill.is_unique"
            class="pill pill-neutral"
            title="Marked from the source's skill class code, not from a card's own skill list."
        >✦ Unique</span>

        <span class="ml-auto font-mono text-xs tabular-nums text-ink-muted">
            <span v-if="skill.sp_cost === null" title="The source publishes no SP cost for this skill.">N/A SP</span>
            <template v-else>{{ skill.sp_cost }} SP</template>
        </span>
    </li>
</template>
