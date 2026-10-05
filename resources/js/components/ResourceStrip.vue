<script setup lang="ts">
/*
 * Composed entirely from props: the widget list, the scenario label and the panel flag come
 * from `config('scenarios')` on the server, and no scenario name branches here (D-240).
 * `values` is the Blade's shadowed `$run` prop: the run's numbers keyed by widget, never the model.
 */
type Widget = 'turn' | 'energy' | 'fans' | 'team_rank' | 'spirit_bursts' | 'grade_points' | 'shop_coins';
type ValueKey = 'turn' | 'energy' | 'fans' | 'team_rank' | 'bursts' | 'grade_points' | 'shop_coins';

const props = withDefaults(
    defineProps<{
        widgets: Widget[];
        // The matrix label for the resolved scenario; printed only when `declared`.
        scenarioLabel: string;
        // config `panels.grade_objectives`: decides which fans caption is honest (D-220).
        hasGradeObjectives?: boolean;
        // `scenarioKey()` falls back to the baseline; that fallback is a device, not a fact.
        declared?: boolean;
        values?: Partial<Record<ValueKey, number | string | null>>;
    }>(),
    {
        hasGradeObjectives: false,
        declared: true,
        values: () => ({}),
    },
);

const int = (raw: number | string): number => Math.trunc(Number(raw));
const group = (n: number): string => n.toLocaleString('en-US');

interface Box {
    widget: Widget;
    label: string;
    value: string;
    sub: string | null;
    valueHint: string | null;
    segments: number | null;
}

// A value the run does not have renders as `N/A` with a caption saying so: defaulting would
// assert a fact nobody entered (D-220), and a dash is banned in shipped copy (R-02).
function present(widget: Widget): Box {
    const key: ValueKey = widget === 'spirit_bursts' ? 'bursts' : widget;
    const raw = props.values[key] ?? null;
    const recorded = raw !== null && raw !== '';

    let label = '';
    let value = '';
    let sub: string | null = null;

    switch (widget) {
        case 'turn':
            label = 'Turn';
            value = group(int(props.values.turn ?? 0));
            sub = props.declared ? props.scenarioLabel : 'no scenario set';
            break;
        case 'energy':
            label = 'Energy';
            value = recorded ? `${int(raw as number | string)}/100` : 'N/A';
            sub = recorded ? null : 'not yet recorded';
            break;
        case 'fans':
            label = 'Fans';
            value = recorded ? group(int(raw as number | string)) : 'N/A';
            sub = recorded ? (props.hasGradeObjectives ? 'farmed by racing here' : 'next event gate 60,000') : 'not yet recorded';
            break;
        case 'team_rank':
            label = 'Team Rank';
            value = recorded ? String(raw) : 'N/A';
            sub = recorded ? 'drives facility level' : 'not yet recorded';
            break;
        case 'spirit_bursts':
            label = 'Spirit Bursts';
            value = recorded ? group(int(raw as number | string)) : 'N/A';
            sub = recorded ? 'normal plus Extreme' : 'not yet recorded';
            break;
        case 'grade_points':
            label = 'Grade Points';
            value = recorded ? `${int(raw as number | string)}/300` : 'N/A';
            sub = recorded ? 'surplus does not carry over' : 'not yet recorded';
            break;
        case 'shop_coins':
            label = 'Shop Coins';
            value = recorded ? group(int(raw as number | string)) : 'N/A';
            sub = recorded ? 'rotation in 2 turns' : 'not yet recorded';
            break;
        default:
            throw new Error(`Unknown widget [${widget}] in scenario config.`);
    }

    return {
        widget,
        label,
        value,
        sub,
        // "N/A" alone is ambiguous, so the tooltip names which half of the fact it is.
        valueHint: recorded ? null : 'No value recorded for this run',
        segments: widget === 'energy' && recorded ? Math.max(0, Math.min(5, Math.round(int(raw as number | string) / 20))) : null,
    };
}
</script>

<template>
    <div class="flex flex-wrap gap-2">
        <template v-for="box in widgets.map(present)" :key="box.widget">
            <!-- The turn widget is the torn-page calendar card: a tab strip with two punch
                 holes over a bordered body, drawn as an object not as another number box. -->
            <div
                v-if="box.widget === 'turn'"
                class="relative min-w-36 flex-1 overflow-hidden rounded-md border-2 border-anchor bg-raised px-3 pt-6 pb-2"
            >
                <span class="absolute inset-x-0 top-0 flex h-5 items-center gap-1.5 bg-anchor px-2" aria-hidden="true">
                    <span class="size-2 rounded-full bg-raised"></span>
                    <span class="size-2 rounded-full bg-raised"></span>
                </span>
                <span class="block font-mono text-xl leading-none font-extrabold tabular-nums text-anchor">{{ box.value }}</span>
                <span class="mt-1 block text-xs font-semibold uppercase tracking-wide text-ink-muted">{{ box.label }}</span>
                <span v-if="box.sub !== null" class="block text-xs text-ink-muted">{{ box.sub }}</span>
            </div>
            <div v-else class="min-w-36 flex-1 rounded-md border border-rule bg-raised px-3 py-2">
                <span class="block text-xs font-semibold uppercase tracking-wide text-ink-muted">{{ box.label }}</span>
                <span
                    class="block font-mono text-xl font-extrabold tabular-nums text-ink-strong"
                    :title="box.valueHint ?? undefined"
                >{{ box.value }}</span>

                <span
                    v-if="box.segments !== null"
                    class="mt-1 flex gap-1"
                    role="img"
                    :aria-label="`Energy ${values.energy ?? 0} of 100`"
                >
                    <span
                        v-for="i in 5"
                        :key="i"
                        class="h-1.5 flex-1 rounded-sm"
                        :class="i - 1 < box.segments ? 'bg-green' : 'bg-idle'"
                    ></span>
                </span>
                <span v-else-if="box.sub !== null" class="block text-xs text-ink-muted">{{ box.sub }}</span>
            </div>
        </template>
    </div>
</template>
