<script setup lang="ts">
/*
 * The career section nav (plan slice 4.1): the run's identity strip and the nine career sections as
 * one tab row, so a Trainer moves between career screens without each page growing its own
 * "back to the Cockpit" pair.
 *
 * **The name is not the plan's.** 4.1 was specified to add a NEW component under the name the plan
 * used, but that path was already a live, committed component with a different contract — the
 * Cockpit's own identity and position card (`<dl>` of Scenario / Year / Month / Turn / Energy, per
 * SCREEN-009 §12), imported by `Cockpit.vue`. This is the layout-level strip instead, so it takes
 * the name its job describes. Slice 4.2 then renamed that other component to
 * `CockpitIdentityCard.vue`, which is what its job always described, so the plan's original name no
 * longer names anything in the tree — deliberately, which is why this prose does not print it either:
 * a source scan for it is an assertion the tree keeps, and one occurrence would silently pass a
 * reader while failing the grep. The ruling and the evidence are recorded in the ledger.
 *
 * **An unreachable section is not a link.** DESIGN.md §5's 2026-10-17 amendment: `aria-disabled`
 * announces a state but does not cancel activation, and a `<Link>` navigates regardless of the
 * attribute.
 *
 * *Dated correction 2026-10-22 (UX audit F-04): the paragraph above stood, and the shape the next
 * sentence prescribed did not.* It read: an unreachable section renders as a focusable
 * `<span role="link" aria-disabled="true" tabindex="0">` carrying a `title`. §5 names that exactly as
 * the thing not to do — "`aria-disabled` alone is not that shape and never may be used as one" — and it
 * put the reason behind a `title`, which is the hover-only channel §5 (a) supersedes. The ruled shape
 * is non-focusable text naming the absence, so the unavailable tab is a plain `<span>` with no role,
 * no `tabindex`, no `title`, and its reason printed as visible muted prose beside it.
 *
 * **The status pill is `StatusPill`, not `StatusBadge`, and that is measured.** `StatusBadge`'s
 * runtime `KNOWN` set is `estimated | unknown | unconfirmed` (provenance states) and its own docblock
 * says an unrecognised state renders NOTHING. Handing it a run status like `active` therefore renders
 * no pill at all. `StatusPill` takes an arbitrary label plus a variant, which is the shape a run
 * status needs.
 *
 * **The hrefs are built from the server's own Cockpit URL.** Every career screen is
 * `${base}/${section}` for the same `base`, so the run id and the `/training-runs` prefix come from
 * the server rather than being restated here. The one exception is the run-scoped Legacy Lab, whose
 * path is `/legacy/{run}` by its own route and is therefore built from the id that base ends in.
 */
import { Link } from '@inertiajs/vue3';
import { computed } from 'vue';
// `components/StatusPill.vue`, not `components/ui/StatusPill.vue`: the import was wrong since this
// component landed and `npm run typecheck` did not catch it (a `.vue` specifier resolves for vue-tsc
// without the file existing), so it took a real bundle build to surface. Verified by the build:
// `Could not resolve "../ui/StatusPill.vue"`.
import StatusPill from '../StatusPill.vue';

const props = defineProps<{
    trainee: string;
    /** The Japanese name, shown beside the romanised one when it differs. */
    traineeJa?: string | null;
    scenarioLabel: string;
    statusLabel: string;
    /**
     * The raw `RunStatus` value. Optional on purpose: absent means the caller did not say, and the
     * honest answer then is that every section is reachable, because a tab wrongly disabled hides a
     * live screen.
     */
    status?: string;
    /** Where the career stands, when the screen knows it (for example "Turn 3"). */
    position?: string | null;
    /** The Cockpit URL. The other sections are built from its base. */
    runUrl: string;
    /** The live section's key, so exactly one tab carries `aria-current="page"`. */
    current?: string;
}>();

const base = computed<string>(() => props.runUrl.replace(/\/cockpit$/, ''));
const runId = computed<string>(() => base.value.split('/').pop() ?? '');

const TABS: { key: string; label: string; href: (base: string) => string }[] = [
    { key: 'cockpit', label: 'Cockpit', href: (b: string) => `${b}/cockpit` },
    { key: 'training', label: 'Training', href: (b: string) => `${b}/training` },
    { key: 'races', label: 'Races', href: (b: string) => `${b}/races` },
    { key: 'skills', label: 'Skills', href: (b: string) => `${b}/skills` },
    { key: 'events', label: 'Events', href: (b: string) => `${b}/events` },
    { key: 'legacy', label: 'Legacy', href: () => `/legacy/${runId.value}` },
    { key: 'deck', label: 'Deck', href: (b: string) => `${b}/deck` },
    { key: 'timeline', label: 'Timeline', href: (b: string) => `${b}/timeline` },
    { key: 'result', label: 'Result', href: (b: string) => `${b}/result` },
];

/**
 * Result is the one section a career does not have until it ends.
 *
 * The values are the MEASURED backing cases of `App\Enums\RunStatus` — `Active`, `Completed`,
 * `Retired`, capitalised. The first version compared against a lower-cased `active` that does not
 * exist, so the comparison never matched and Result rendered as a plain link on every career,
 * including the active one the rule is about. The first real browser run in the isolated worktree
 * caught it: the disabled tab resolved to `<a href=".../result">`, and DESIGN.md Section 5's rule
 * that an unreachable destination must not be an anchor was therefore not implemented at all.
 *
 * Absent `status` keeps the safe default the prop documents: the caller did not say, and a tab
 * wrongly disabled hides a live screen, so every section stays reachable. That default is why this
 * takes the enum string rather than a boolean — a boolean would have to be supplied by all eight
 * non-pilot callers, and each omission would silently disable Result.
 *
 * When the status IS known and terminal the default no longer applies, because the caller did say.
 * Then two tabs invert, symmetrically: `result` becomes live (there is a result to read) and
 * `training` becomes non-live, because the decision screen is the one a finished career has no turns
 * left to reach. Everything else stays reachable — cockpit, races, skills, events, legacy, deck and
 * timeline are all still viewable. The browser suite caught the omission: on a Retired career the
 * career bar still offered the Training tab as an anchor, which is the decision screen.
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

const UNAVAILABLE_TITLE = 'Available when the career is completed';

/** The tooltip an unavailable tab carries. The reason differs by which way the tab is unavailable. */
const unavailableTitle = (key: string): string =>
    key === 'training' ? 'The career has ended, so no turns are left to decide' : UNAVAILABLE_TITLE;

/**
 * The sections this career cannot reach, so the missing ones are named in prose rather than by a
 * `title` on a control. Empty on the safe default, where the caller did not say.
 */
const unreachableTabs = computed<{ key: string; label: string }[]>(() =>
    TABS.filter((tab) => !reachable(tab.key)).map((tab) => ({ key: tab.key, label: tab.label })),
);

const TAB =
    'inline-flex min-h-11 items-center rounded-md px-3 text-sm font-medium text-ink hover:bg-raised hover:text-ink-strong';
const TAB_CURRENT = 'bg-raised font-bold text-ink-strong';
const TAB_DISABLED = 'inline-flex min-h-11 items-center rounded-md px-3 text-sm font-medium text-ink-muted';

const pillVariant = computed<string>(() => (props.status === 'Active' ? 'positive' : 'neutral'));
const showTraineeJa = computed<boolean>(
    () =>
        props.traineeJa !== null &&
        props.traineeJa !== undefined &&
        props.traineeJa !== '' &&
        props.traineeJa !== props.trainee,
);
</script>

<template>
    <section aria-label="Career" class="mb-4 rounded-md border border-rule bg-panel">
        <div class="flex flex-wrap items-baseline gap-x-3 gap-y-1 px-4 py-2">
            <p class="text-sm font-semibold text-ink-strong">
                {{ props.trainee }}
                <span v-if="showTraineeJa" lang="ja" class="ml-2 text-sm font-normal text-ink-muted">
                    {{ props.traineeJa }}
                </span>
            </p>
            <p class="flex flex-wrap items-baseline gap-x-2 text-sm text-ink-muted">
                <span>{{ props.scenarioLabel }}</span>
                <StatusPill :label="props.statusLabel" :variant="pillVariant" size="sm" />
                <template v-if="props.position">
                    <span aria-hidden="true">·</span>
                    <span>{{ props.position }}</span>
                </template>
            </p>
        </div>

        <nav aria-label="Career sections" class="border-t border-rule px-2 py-1">
            <ul class="flex flex-wrap gap-1 text-sm">
                <li v-for="tab in TABS" :key="tab.key">
                    <Link
                        v-if="reachable(tab.key)"
                        :href="tab.href(base)"
                        :class="[TAB, props.current === tab.key ? TAB_CURRENT : '']"
                        :aria-current="props.current === tab.key ? 'page' : undefined"
                    >
                        {{ tab.label }}
                    </Link>
                    <!-- DESIGN.md §5 (a), 2026-10-17: an unreachable destination is non-focusable text.
                         No `role="link"`, no `tabindex`, no `title`: those were the shape §5 forbids,
                         and the reason is the visible line under the tab row. -->
                    <span v-else :class="TAB_DISABLED">
                        {{ tab.label }}
                    </span>
                </li>
            </ul>
            <!-- DESIGN.md §5 (a): the reason an unreachable section is absent, as visible prose in the
                 same sentence voice as the tabs, and reachable without a mouse. It replaces the
                 `title` the unreachable tab used to carry. Each unavailable section gets its own
                 sentence, because one line covering two tabs would attach the reason to neither. -->
            <p v-for="tab in unreachableTabs" :key="tab.key" class="px-2 pb-1 text-xs text-ink-muted" data-absence>
                {{ tab.label }}: {{ unavailableTitle(tab.key) }}.
            </p>
        </nav>
    </section>
</template>
