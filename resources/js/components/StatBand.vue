<script setup lang="ts">
import { computed } from 'vue';
import GradeBadge from './GradeBadge.vue';

// KI-47 / ADR-0015: the ceilings arrive pre-computed from ScenarioCaps::forRun(), the same
// call the turn validator makes, so the band can never rate a trainee against a bonus the
// form would reject. The scenario's name is not passed at all (G-33): capBonus is its
// display breakdown, or null when the run names no scenario.
const props = defineProps<{
    caps: Record<string, number>;
    values: Record<string, number>;
    skillPoints: number | null;
    capBonus: Record<string, number> | null;
    baseCap: number;
    hardCap: number;
    gradeBanding: { step: number; labels: string[] };
    /**
     * The ceiling a stat could still reach, per stat, when the run's breakthrough position is known.
     * Null where it is not, and null as a whole where no caller can name one yet: an absent potential
     * leaves the band drawing exactly what it drew before this prop existed.
     */
    potentialCaps?: Record<string, number | null> | null;
}>();

/*
 * Full literal class strings, keyed rather than interpolated: Tailwind v4 scans sources
 * for complete class names, so "bg-tint-{$stat}" would compile to nothing at all.
 */
const tintClass: Record<string, string> = {
    Speed: 'bg-tint-speed border-line-speed',
    Stamina: 'bg-tint-stamina border-line-stamina',
    Power: 'bg-tint-power border-line-power',
    Guts: 'bg-tint-guts border-line-guts',
    Wit: 'bg-tint-wit border-line-wit',
};

const glyphPath: Record<string, string> = {
    Speed: 'M2 13h11l1 2H1v-2zm1-3 3-4 3 3 3-2 1 3H3z',
    Stamina: 'M8 15S2 11 2 6.6A3.6 3.6 0 0 1 8 4a3.6 3.6 0 0 1 6 2.6C14 11 8 15 8 15z',
    Power: 'M1 5h2v6H1zm3 1h2v4H4zm8-1h2v6h-2zM10 6h2v4h-2zM6 7h4v2H6z',
    Guts: 'M8 1c2 3 5 4 5 8a5 5 0 0 1-10 0c0-2 1-3 2-4 0 2 1 3 2 3 1-2-1-4-1-7z',
    Wit: 'M8 2 15 6l-7 4-7-4 7-4zm-5 7v3c0 1.7 10 1.7 10 0V9L8 11 3 9z',
};

const fmt = (n: number): string => n.toLocaleString('en-US');

// Grade is derived from the entered value, never stored. The banding is ours (no source
// defines a client stat grade), printed below the band and labelled provisional (D-256, G-46).
const gradeOf = (value: number): string => {
    const index = Math.min(
        props.gradeBanding.labels.length - 1,
        Math.floor(Math.max(0, value) / props.gradeBanding.step),
    );
    return props.gradeBanding.labels[index];
};

// Blade threw here on a missing cap or an unknown scenario key; both are unreachable now
// that the caps map itself drives which columns render.
const rows = computed(() =>
    Object.keys(props.caps).map((stat) => {
        const value = Math.trunc(props.values[stat] ?? 0);
        const cap = Math.trunc(props.caps[stat]);
        const potential = Math.trunc(props.potentialCaps?.[stat] ?? 0);

        // A known potential is the scale the bar is drawn against, so the scenario ceiling becomes
        // an inner marker rather than the end of the ruler: the audit's finding was that the tool
        // showed one level where the client shows three. Without one the band is unchanged.
        const scale = potential > cap ? potential : cap;
        const pctOf = (n: number): number => Math.min(100, scale > 0 ? (n / scale) * 100 : 0);

        return {
            stat,
            value,
            cap,
            potential: potential > cap ? potential : null,
            pct: pctOf(value),
            softPct: pctOf(props.baseCap),
            capPct: pctOf(cap),
            atCeiling: cap <= props.baseCap,
            grade: gradeOf(value),
        };
    }),
);

const hasPotential = computed(() =>
    Object.values(props.potentialCaps ?? {}).some((cap) => cap !== null && cap !== undefined),
);

const bonusLine = computed(() =>
    Object.keys(props.caps)
        .map((stat) => `${stat} +${props.capBonus?.[stat] ?? 0}`)
        .join(', '),
);

const r2 = (n: number): number => Math.round(n * 100) / 100;
</script>

<template>
    <!--
        One band, six columns. Two markers, two meanings: the dashed tick is the 1200
        halved-gains line, the bar end is the scenario ceiling. Where a ceiling equals 1200
        the bar end itself takes the dashes so the coincidence stays visible (ADR-0002, D-211, D-212).
    -->
    <div class="rounded-md border border-rule bg-raised">
        <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-6">
            <div v-for="row in rows" :key="row.stat" class="border-b border-r border-rule p-3 last:border-r-0">
                <div
                    class="-mx-3 -mt-3 mb-2 flex items-center gap-1.5 border-b px-3 py-1.5 text-xs font-semibold uppercase tracking-wide text-ink"
                    :class="tintClass[row.stat]"
                >
                    <svg class="size-3.5 shrink-0 fill-current" viewBox="0 0 16 16" aria-hidden="true">
                        <path :d="glyphPath[row.stat]" />
                    </svg>
                    <span>{{ row.stat }}</span>
                </div>

                <div class="flex items-end gap-2">
                    <!-- R13/KI-8: one component owns the letter treatment; x-grade-badge's twin is the only one now. -->
                    <GradeBadge :grade="row.grade" title="Derived from the entered value, not read from the client" />
                    <span class="font-mono text-2xl leading-none font-extrabold tabular-nums text-ink-strong">
                        {{ fmt(row.value) }}
                    </span>
                </div>

                <div class="mt-0.5 font-mono text-xs tabular-nums text-ink-muted">
                    / {{ fmt(row.cap) }}
                </div>

                <div
                    class="relative mt-2 h-1.5 overflow-hidden rounded bg-sunken"
                    :class="row.atCeiling || row.potential !== null ? 'border-r-2 border-dashed border-r-ink-faint rounded-l' : ''"
                >
                    <div class="absolute inset-y-0 left-0 bg-green-deep" :style="{ width: `${r2(row.pct)}%` }"></div>
                    <div
                        v-if="row.value > baseCap"
                        class="absolute inset-y-0 bg-up opacity-50"
                        :style="{
                            left: `${r2(row.softPct)}%`,
                            width: `${r2(Math.min(100 - row.softPct, ((row.value - baseCap) / (row.potential ?? row.cap)) * 100))}%`,
                        }"
                    ></div>
                    <div
                        v-if="!row.atCeiling"
                        class="absolute -inset-y-1 w-0 border-l-2 border-dashed border-ink-faint"
                        :style="{ left: `${r2(row.softPct)}%` }"
                    ></div>
                    <!-- The third level, present only when a potential is known: without one the bar
                         ends at the scenario ceiling exactly as it always did. -->
                    <div
                        v-if="row.potential !== null"
                        class="potential-cap-marker absolute -inset-y-1.5 w-0 border-l-2 border-dashed border-ink-muted"
                        :style="{ left: `${r2(row.capPct)}%` }"
                        :title="`Scenario ceiling ${fmt(row.cap)}; the bar ends at the potential ${fmt(row.potential)}`"
                    ></div>
                </div>
            </div>

            <div class="border-b border-rule p-3">
                <div
                    class="-mx-3 -mt-3 mb-2 flex items-center gap-1.5 border-b border-line-sp px-3 py-1.5 text-xs font-semibold uppercase tracking-wide text-ink bg-tint-sp"
                >
                    <svg class="size-3.5 shrink-0 fill-current" viewBox="0 0 16 16" aria-hidden="true">
                        <path d="M3 2h9a1 1 0 0 1 1 1v10H4a1 1 0 0 1-1-1V2zm2 1v8h7V3H5z" />
                    </svg>
                    <span>Skill Points</span>
                </div>
                <div class="font-mono text-2xl leading-none font-extrabold tabular-nums text-ink-strong">
                    {{ skillPoints === null ? '0' : fmt(Math.trunc(skillPoints)) }}
                </div>
                <div class="mt-0.5 text-xs text-ink-muted">no cap, no grade</div>
            </div>
        </div>

        <div class="flex flex-wrap items-center gap-x-4 gap-y-1 px-3 py-2 text-xs text-ink-muted">
            <span class="inline-block h-3 w-0 border-l-2 border-dashed border-ink-faint" aria-hidden="true"></span>
            <span>
                <span class="font-semibold text-ink-strong">1,200</span> is where training gains halve.
                <template v-if="hasPotential">
                    The inner dashed line is the scenario ceiling; the bar ends at the potential ceiling.
                </template>
                <template v-else>
                    The bar end is the scenario ceiling. Where the two coincide the bar end is dashed.
                </template>
            </span>
        </div>

        <div class="px-3 pb-3 font-mono text-xs tabular-nums text-ink-muted">
            <template v-if="capBonus === null">
                {{ baseCap }} base, no scenario set: every ceiling here is the base cap and no bonus applies.
            </template>
            <template v-else>{{ baseCap }} base + {{ bonusLine }} </template>
            <template v-if="hasPotential">
                The outer dashed line is the potential ceiling, which is the hard cap the scenario's own
                row publishes: a level the run could still earn, not one it has reached.
            </template>
            <template v-else>breakthrough not tracked. Hard cap {{ fmt(hardCap) }}.</template>
            <!-- The five 「限界値アップ」 effects are carried by zero of the 559 catalogue records (§1.4.8). -->
            Deck recorded under Support deck; no card in the catalogue raises these ceilings.
        </div>

        <div class="border-t border-rule px-3 py-2">
            <p class="text-xs text-ink-muted">
                <span class="rounded border border-dashed border-down px-1 font-semibold text-down">Provisional</span>
                Our grade scale is provisional; validated below 450 only.
                No source in this repository defines a client stat grade, so these letters are ours.
            </p>
        </div>
    </div>
</template>
