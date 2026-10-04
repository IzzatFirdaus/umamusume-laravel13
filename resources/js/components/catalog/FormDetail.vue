<script setup lang="ts">
import RarityChip from '../RarityChip.vue';

// One costume form's body. The title is verbatim client copy, brackets included: the lore guard
// sits on the display path, not on the data, so nothing here re-cases or trims it. This form's
// own fetch is named separately from the trainee's fetch history (D-33).
defineProps<{
    card: {
        title: string;
        rarity_label: string;
        rarity_stars: string;
        is_debut_form: boolean;
        unconfirmed: boolean;
        global_release_date: string;
        global_release_date_display: string;
        snapshot_path: string | null;
        source_url: string;
        fetched_at_display: string | null;
    };
}>();
</script>

<template>
    <div class="rounded-md border border-rule bg-raised p-4">
        <div class="flex flex-wrap items-baseline justify-between gap-x-4 gap-y-1">
            <h3 class="text-base font-semibold text-ink-strong">{{ card.title }}</h3>
            <span class="flex flex-wrap items-baseline gap-3 text-xs text-ink-muted">
                <RarityChip :label="card.rarity_label" :stars="card.rarity_stars" />
                <span v-if="card.is_debut_form">debut form</span>
                <span v-if="card.unconfirmed" class="font-semibold text-risk">Not confirmed by two sources</span>
                <time :datetime="card.global_release_date">
                    Released (Global) {{ card.global_release_date_display }}
                </time>
            </span>
        </div>

        <p class="mt-2 text-xs text-ink-muted">
            from
            <template v-if="card.snapshot_path">{{ card.snapshot_path }} ·</template>
            <a :href="card.source_url" class="text-ink-strong underline">{{ card.source_url }}</a>
            <template v-if="card.fetched_at_display">· read {{ card.fetched_at_display }}</template>
        </p>
    </div>
</template>
