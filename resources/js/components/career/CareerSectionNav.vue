<script setup lang="ts">
import { computed } from 'vue';
import { Link } from '@inertiajs/vue3';

/*
 * The career stepper: the nine sections a run walks, in the wireframe's own shape
 * (`cockpit-unity-cup.html:71-82` — `<nav aria-label="Career" class="stepper">` with one `.step`
 * per section, the live one carrying `aria-current="step"` and rendered as a span).
 *
 * It was a card carrying the run's identity strip above a tab row. Both parts went, for the same
 * reason: the HFW draws the identity in the page head and the topbar (`.crumb`, `.who`) and draws no
 * second identity band anywhere in the set, so the strip was a second name for facts already on
 * screen. The band's facts survive — trainee and scenario in the crumb, the state in the `who` pill,
 * the calendar, rarity and potential in the page's `lede`.
 *
 * The props the identity band used (`trainee`, `traineeJa`, `scenarioLabel`, `statusLabel`,
 * `position`) are kept because eight career screens pass them, and churning eight call sites to
 * delete five arguments that cost nothing would be a bigger diff than the shape change that
 * motivated it. They are no longer read here.
 *
 * HFW-OVERRIDE (stepper-consistency): only `cockpit-unity-cup.html` draws a stepper; the other three
 * cockpit HFWs omit it. The app draws it on every career screen, active and completed, because a
 * Trainer who sees it on one active run and not the next reads its absence as a bug. Ruling 2 (b).
 */
const props = defineProps<{
    trainee?: string;
    traineeJa?: string | null;
    scenarioLabel?: string;
    statusLabel?: string;
    /**
     * The raw `RunStatus` value. Optional on purpose: absent means the caller did not say, and the
     * honest answer then is that every section is reachable, because a step wrongly disabled hides a
     * live screen.
     */
    status?: string;
    position?: string | null;
    /** The Cockpit URL. The other sections are built from its base. */
    runUrl: string;
    /** The live section's key, so exactly one step carries `aria-current="step"`. */
    current?: string;
}>();

const base = computed<string>(() => props.runUrl.replace(/\/cockpit$/, ''));

const STEPS: { key: string; label: string; href: (base: string) => string }[] = [
    { key: 'cockpit', label: 'Cockpit', href: (b: string) => `${b}/cockpit` },
    { key: 'training', label: 'Training', href: (b: string) => `${b}/training` },
    { key: 'races', label: 'Races', href: (b: string) => `${b}/races` },
    { key: 'skills', label: 'Skills', href: (b: string) => `${b}/skills` },
    { key: 'events', label: 'Events', href: (b: string) => `${b}/events` },
    /* `Inheritance`, not `Legacy`: the HFW stepper names this step after the career's inheritance
       event, which is `/training-runs/{run}/inheritance`. The run-scoped Legacy Lab is a different
       screen and keeps its own door on the global rail. */
    { key: 'inheritance', label: 'Inheritance', href: (b: string) => `${b}/inheritance` },
    { key: 'deck', label: 'Deck', href: (b: string) => `${b}/deck` },
    { key: 'timeline', label: 'Timeline', href: (b: string) => `${b}/timeline` },
    { key: 'result', label: 'Result', href: (b: string) => `${b}/result` },
];

/**
 * Result is the one section a career does not have until it ends.
 *
 * The values are the MEASURED backing cases of `App\Enums\RunStatus` — `Active`, `Completed`,
 * `Retired`, capitalised. Absent `status` keeps the safe default the prop documents.
 *
 * When the status IS known and terminal the default no longer applies: `result` becomes live and
 * `training` does not, because the decision screen is the one a finished career has no turns left to
 * reach. Everything else stays reachable.
 */
const hasEnded = (status: string): boolean => status === 'Completed' || status === 'Retired';

const reachable = (key: string): boolean => {
    if (props.status === undefined || props.status === '') {
        return true;
    }

    const ended = hasEnded(props.status);

    if (key === 'result') {
        return ended;
    }

    if (key === 'training') {
        return !ended;
    }

    return true;
};

const UNAVAILABLE_REASON = 'Available when the career is completed';

/** The reason an unavailable step is unavailable. It differs by which way the step is unavailable. */
const unavailableReason = (key: string): string =>
    key === 'training' ? 'The career has ended, so no turns are left to decide' : UNAVAILABLE_REASON;

/** The steps this career cannot reach, named in prose rather than by a `title` on a control. */
const unreachableSteps = computed<{ key: string; label: string }[]>(() =>
    STEPS.filter((step) => !reachable(step.key)).map((step) => ({ key: step.key, label: step.label })),
);
</script>

<template>
    <nav aria-label="Career" class="stepper">
        <template v-for="step in STEPS" :key="step.key">
            <Link
                v-if="reachable(step.key)"
                :href="step.href(base)"
                class="step"
                :aria-current="props.current === step.key ? 'step' : undefined"
            >{{ step.label }}</Link>
            <!-- DESIGN.md §5 (a): an unreachable destination is non-focusable text, never an anchor
                 with `aria-disabled`, a `role="link"` or a `title`. `.step.is-empty` is the ported
                 rule that draws it as the dimmed member of the same row, so the absence is visible
                 without a sentence the HFW does not draw. -->
            <span v-else class="step is-empty" :aria-current="props.current === step.key ? 'step' : undefined">
                {{ step.label }}
            </span>
        </template>
    </nav>

    <!--
      DESIGN.md: "An absence states its reason where a keyboard and a screen reader can reach it."
      A dimmed step names the absence but not its cause, so the cause is one visible sentence per
      unreachable step, in the same voice as the row.

      HFW-OVERRIDE (text: absence-reason) — the wireframes draw a stepper with no reachability model
      at all, so they cannot draw this sentence. It ships on every career screen because a Trainer who
      sees `Result` dimmed needs to know whether it is a bug or a wait. One line per unreachable step,
      and none on the safe default where the caller did not report a status.
    -->
    <p
        v-for="step in unreachableSteps"
        :key="step.key"
        class="mt-1 text-xs text-ink-muted"
        data-absence
    >
        {{ step.label }}: {{ unavailableReason(step.key) }}.
    </p>
</template>
