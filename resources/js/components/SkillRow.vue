<script setup lang="ts">
// One row in the trainee detail page's Skills section. Prints only what the source states.
// `url` is null for a skill the Global client has not shipped, so the name prints with no
// link that would 404 on it (ADR-0011 §2). Nothing here ranks or recommends a skill.
defineProps<{
    skill: { name: string; sp_cost: number | null; is_unique: boolean; url: string | null };
}>();
</script>

<template>
    <li class="flex flex-wrap items-baseline gap-2 border-b border-rule py-1.5 last:border-b-0">
        <a v-if="skill.url" :href="skill.url" class="text-ink hover:underline">{{ skill.name }}</a>
        <span v-else class="text-ink">{{ skill.name }}</span>

        <span
            v-if="skill.is_unique"
            class="rounded-full bg-sunken px-2 py-0.5 text-xs text-ink-strong"
            title="Marked from the source's skill class code, not from a card's own skill list."
        >✦ Unique</span>

        <span class="ml-auto font-mono text-xs tabular-nums text-ink-muted">
            <span v-if="skill.sp_cost === null" title="The source publishes no SP cost for this skill.">N/A SP</span>
            <template v-else>{{ skill.sp_cost }} SP</template>
        </span>
    </li>
</template>
