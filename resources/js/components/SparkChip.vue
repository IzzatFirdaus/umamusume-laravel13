<script setup lang="ts">
interface Props {
    label: string;
    variant?: 'stat' | 'aptitude' | 'skill' | 'competition' | 'scenario' | 'consumable' | 'currency';
    size?: 'sm' | 'md';
}

const props = defineProps<Props>();

// The seven spark categories are labels, not a palette. The variant colours this component
// originally shipped with referenced six token families that do not exist in `app.css`
// (blue-, purple-, orange-, pink-, yellow-, gray- tint/deep/line): Tailwind v4 emits nothing
// for unknown utilities, so those chips rendered as plain text in a default-colour border, and
// the "palette" was a gated proposal anyway — a wider semantic colour vocabulary is Phase 11
// (Track 2-D), which may not ship before its owner ruling on `DESIGN.md` §2 is recorded.
// Until then every variant carries the chip treatment the contract can actually prove:
// `ink-muted on sunken` is a measured G-18 pair, and `aptitude` keeps the green-tint pair the
// registry already carries. The `variant` prop stays in the API so the ruling can recolour by
// name without touching call sites.
const variantClasses: Record<string, string> = {
    stat: 'bg-sunken text-ink-muted border-rule',
    aptitude: 'bg-green-tint text-ink border-green-line',
    skill: 'bg-sunken text-ink-muted border-rule',
    competition: 'bg-sunken text-ink-muted border-rule',
    scenario: 'bg-sunken text-ink-muted border-rule',
    consumable: 'bg-sunken text-ink-muted border-rule',
    currency: 'bg-sunken text-ink-muted border-rule',
    default: 'bg-sunken text-ink-muted border-rule',
};

const sizeClasses = {
    sm: 'px-1.5 py-0.5 text-xs',
    md: 'px-2 py-0.5 text-sm',
};
</script>

<template>
    <span
        class="inline-flex items-center rounded-full border font-medium"
        :class="[variantClasses[props.variant ?? 'default'], sizeClasses[props.size ?? 'md']]"
    >
        {{ props.label }}
    </span>
</template>