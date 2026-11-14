<script setup lang="ts">
/*
 * The shared stat badge: one component for the seven stat/type entities' label-and-value chips,
 * so a new surface is one component reuse rather than a copied colour map (the same rule
 * `SupportTypeMark.vue` records for the type tiles).
 *
 * **Colour comes from tokens minted in stat Slice A (2026-11-14), never literals.** The tint
 * variant layers the three families the stylesheet ships per stat: the `--color-tint-*` fill,
 * the `--color-stat-*-ink` text tier and the `--color-line-*` boundary. The plain variant is the
 * text tier on the page's own surface, for rows that sit inside an already-tinted parent.
 *
 * **Literal class strings, keyed per stat.** Tailwind v4 scans sources for complete class names,
 * so an interpolated `bg-tint-${stat}` compiles to nothing (the same rule `TrainingCard.vue`'s
 * `groupBorder` map records at :111-112). Every class this component can render appears here as a
 * whole string, and the maps are typed so a misspelled stat key is a compile error, not a missing
 * colour at runtime.
 *
 * **The word carries the meaning; the colour never does alone** (`design-2.0` §17, the rule
 * `SupportTypeMark.vue` implements): the stat name is always printed inside the badge, so a
 * reader who cannot separate the seven fills still reads the stat.
 */
import { computed } from 'vue';

const STAT_KEYS = ['speed', 'stamina', 'power', 'guts', 'wit', 'pal', 'group'] as const;

type Stat = (typeof STAT_KEYS)[number];
type Variant = 'tint' | 'plain';

const props = withDefaults(
    defineProps<{
        /** One of the seven stat/type keys; anything else is a compile error. */
        stat: Stat;
        /** `tint` = filled chip; `plain` = text tier only, for use inside tinted parents. */
        variant?: Variant;
        /** The stat's value; omitted for a label-only badge. */
        value?: string | number | null;
    }>(),
    { variant: 'tint', value: null },
);

/*
 * Whole-string class maps (Tailwind v4 source-scan rule). Speed through Wit own tint and line
 * tokens (stat Slice A's families); `pal` and `group` minted no tint/line tokens (stat Slice A's
 * plan records why: their in-game chips have no pastel fill of their own), so their tint variant
 * is the neutral chip -- `bg-sunken` + `border-rule` -- carrying their own ink tier. That is a
 * visible-difference-by-design, not a borrowed identity: reusing another stat's fill would lie
 * about which stat the chip names.
 */
const TINT_CLASSES: Record<Stat, string> = {
    speed: 'bg-tint-speed border-line-speed',
    stamina: 'bg-tint-stamina border-line-stamina',
    power: 'bg-tint-power border-line-power',
    guts: 'bg-tint-guts border-line-guts',
    wit: 'bg-tint-wit border-line-wit',
    pal: 'bg-sunken border-rule',
    group: 'bg-sunken border-rule',
};
const INK_TEXT: Record<Stat, string> = {
    speed: 'text-stat-speed-ink',
    stamina: 'text-stat-stamina-ink',
    power: 'text-stat-power-ink',
    guts: 'text-stat-guts-ink',
    wit: 'text-stat-wit-ink',
    pal: 'text-stat-pal-ink',
    group: 'text-stat-group-ink',
};

const LABELS: Record<Stat, string> = {
    speed: 'Speed',
    stamina: 'Stamina',
    power: 'Power',
    guts: 'Guts',
    wit: 'Wit',
    pal: 'Pal',
    group: 'Group',
};

const classes = computed((): string[] => {
    const base = ['inline-flex', 'items-center', 'gap-1', 'rounded-md', 'px-2', 'py-1', 'text-xs', 'font-medium', 'border'];
    if (props.variant === 'plain') {
        return [...base, INK_TEXT[props.stat]];
    }
    return [...base, TINT_CLASSES[props.stat], INK_TEXT[props.stat]];
});

const label = computed((): string => LABELS[props.stat]);
const hasValue = computed((): boolean => props.value !== null && props.value !== undefined && props.value !== '');
</script>

<template>
    <span :class="classes" :data-stat="props.stat">
        <span class="font-semibold">{{ label }}</span>
        <span v-if="hasValue" class="font-mono tabular-nums">{{ props.value }}</span>
    </span>
</template>
