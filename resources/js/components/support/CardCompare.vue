<script setup lang="ts">
/**
 * Side-by-side support card comparison component.
 *
 * Reuses the detail page's anchor-table rendering: same effect rows, same stated anchors,
 * same N/A rules, cap = last stated anchor. Renders only the selected cards — no empty columns.
 * Built so the picker can reuse it in a later pass.
 *
 * Props: cards with stages (same shape as detail page's effectStages).
 * Emits: close
 */
import { computed } from 'vue';
import ArtworkSlot from '../ArtworkSlot.vue';
import RarityChip from '../RarityChip.vue';
import SupportTypeMark from './SupportTypeMark.vue';

interface StageAnchor {
    level: number;
    display: string;
}

interface EffectStage {
    effect_id: number;
    name: string | null;
    anchors: StageAnchor[];
    cap: StageAnchor;
}

interface CompareCard {
    id: number;
    name: string;
    char_name: string;
    title: string | null;
    artworkURL: string | null;
    rarity_label: string;
    rarity_stars: string;
    rarity_word: string;
    type: string;
    type_label: string;
    release_status: string;
    stages: EffectStage[];
}

interface Props {
    cards: CompareCard[];
}

defineProps<Props>();
defineEmits<{ close: [] }>();

/**
 * Get all unique effect IDs across all cards for column headers.
 * Order by first appearance in the first card that has it.
 */
const allEffectIds = computed((): number[] => {
    const seen = new Set<number>();
    const order: number[] = [];

    for (const card of props.cards) {
        for (const stage of card.stages) {
            if (!seen.has(stage.effect_id)) {
                seen.add(stage.effect_id);
                order.push(stage.effect_id);
            }
        }
    }

    return order;
});

/**
 * Format the anchor pairs for display, matching the detail page's format.
 */
const statedAt = (stage: { anchors: { level: number; display: string }[] }): string => {
    const pairs = stage.anchors.map((anchor) => `${anchor.level}: ${anchor.display}`);

    if (pairs.length === 0) return 'Not recorded';

    return stage.anchors[0].level === 1
        ? pairs.join(' · ')
        : ['1: Not recorded', ...pairs].join(' · ');
};

/**
 * Format the cap display for the header row.
 */
const capDisplay = (stage: { cap: { display: string; level: number } }): string => {
    return `${stage.cap.display} · Lv ${stage.cap.level}`;
};

/**
 * Get the stage for a given card and effect ID.
 */
const getStage = (card: { stages: { effect_id: number }[] }, effectId: number): { effect_id: number; name: string | null; anchors: { level: number; display: string }[]; cap: { display: string; level: number } } | null => {
    return card.stages.find((s) => s.effect_id === effectId) ?? null;
};
</script>

<template>
    <div class="card">
        <h2 class="text-lg font-semibold text-ink-strong mb-4">
            Compare {{ props.cards.length }} cards
        </h2>

        <template v-if="props.cards.length < 2">
            <p class="text-sm text-ink-muted">
                Select at least 2 cards to compare.
            </p>
        </template>

        <template v-else>
            <!-- Card headers -->
            <div class="overflow-x-auto">
                <table class="t min-w-max">
                    <thead>
                        <tr>
                            <th class="border-b border-rule px-2 py-2 text-left text-xs font-semibold text-ink-strong w-1/3">
                                Effect
                            </th>
                            <th v-for="card in props.cards" :key="card.id" class="border-b border-rule px-2 py-2 text-center text-xs font-semibold text-ink-strong">
                                <div class="flex items-center justify-center gap-1">
                                    <ArtworkSlot :url="card.artworkURL" alt="" size="size-8" />
                                    <span class="font-semibold text-ink-strong truncate">{{ card.name }}</span>
                                    <RarityChip :label="card.rarity_label" :stars="card.rarity_stars" />
                                    <SupportTypeMark :type="card.type" :label="card.type_label" />
                                </div>
                            </th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr v-for="effectId in allEffectIds" :key="effectId">
                            <td class="border-b border-rule px-2 py-2 align-top text-sm text-ink font-mono text-xs">
                                {{ getStage(props.cards[0], effectId)?.name ?? `Effect ${effectId}` }}
                            </td>
                            <td v-for="card in props.cards" :key="card.id" class="border-b border-rule px-2 py-2 align-top text-center">
                                <template v-if="getStage(card, effectId) as stage">
                                    <div class="font-mono text-xs text-ink">{{ statedAt(stage) }}</div>
                                    <div class="mt-1 font-bold text-ink-strong">{{ capDisplay(stage) }}</div>
                                </template>
                                <template v-else>
                                    <span class="text-xs text-ink-muted">Not recorded</span>
                                </template>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </template>

        <p class="note mt-3">
            <span class="g" aria-hidden="true">○</span>
            <span>
                These are the selected cards' own figures, not interpolated values. Every figure is a
                stated anchor and the highest stated anchor is the cap, never an interpolation.
            </span>
        </p>
    </div>
</template>