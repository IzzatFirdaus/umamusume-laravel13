<script setup lang="ts">
/*
 * One aptitude letter, stated twice: as the letter and as the band it belongs to.
 *
 * `AptitudeGrid.vue:36` prints the letter alone in a bold mono cell, which is fine where a labelled grid
 * is the read ("Turf A"). On a roster card the letter is the only thing a Trainer skims, and `design-2.0`
 * §17 is explicit that it must not be the only signal: "Do not communicate aptitude solely through color."
 * So the badge carries the band word as text, and the fill is an *additional* signal on top of it rather
 * than the readout itself.
 *
 * The mapping is the one §17 records, in full: S and A are Strong, B and C Neutral, D and E Weak, F and G
 * Very weak. `S` is kept even though no seeded row carries one (the catalog's best letter is `A`), because
 * the parser will accept it: `GametoraCharacterParser.php:143` fixes the domain with
 * `preg_match('/^[SABCDEFG]$/')`, so a letter this table drops would be a letter the ingest can write and
 * the screen then silently mislabels.
 *
 * The `label` prop is required with no default. A badge without its own word for the axis is the failure
 * this component exists to prevent, and `config/scenarios.php`'s rule that a scenario-driven component
 * declare `scenario` with no default is the same discipline applied here (AGENTS.md §7).
 */
withDefaults(
    defineProps<{
        /** The axis the letter is on: `Turf`, `Long`, `Pace chaser`. No default, by design. */
        label: string;
        /** The stored letter. Anything outside S..G renders as an absence rather than as a guess. */
        letter: string | null;
    }>(),
    { letter: null },
);

const BANDS: Record<string, string> = {
    S: 'Strong',
    A: 'Strong',
    B: 'Neutral',
    C: 'Neutral',
    D: 'Weak',
    E: 'Weak',
    F: 'Very weak',
    G: 'Very weak',
};

const bandOf = (letter: string | null): string | null => (letter === null ? null : BANDS[letter] ?? null);

/*
 * The fill is decoration over the two words, never the meaning. Four token combinations, all from the
 * existing set: no new custom property, no `dark:` variant, and no palette class, which is what
 * `DesignTokensTest`'s sweep of both source trees checks. The strong band is the only one that carries a
 * weight change, so a Trainer who reads no colour at all still sees the tier break in the type.
 */
const classesOf = (letter: string | null): string => {
    const band = bandOf(letter);

    if (band === 'Strong') {
        return 'border-pick-line bg-sunken font-bold text-ink-strong';
    }

    if (band === 'Neutral') {
        return 'border-rule bg-panel font-semibold text-ink';
    }

    if (band === 'Weak') {
        return 'border-rule bg-raised font-normal text-ink-muted';
    }

    // The bottom band and the absent letter share a shape on purpose: an unpublished aptitude is not a
    // weak one, which is why the word differs even though the fill does not.
    return 'border-dashed border-rule bg-raised font-normal text-ink-muted';
};
</script>

<template>
    <span class="inline-flex items-baseline gap-1.5 rounded-md border px-2 py-1 text-xs" :class="classesOf(letter)">
        <span class="text-ink-muted">{{ label }}</span>
        <span class="font-mono tabular-nums">{{ letter ?? 'N/A' }}</span>
        <span
            v-if="letter !== null && bandOf(letter) === null"
            title="This letter is outside the S to G scale this tool records."
            class="text-ink-muted"
        >Unrecognised</span>
        <span v-else-if="letter === null" title="No aptitude letter has been published for this axis." class="text-ink-muted">
            Not published
        </span>
        <span v-else>{{ bandOf(letter) }}</span>
    </span>
</template>
