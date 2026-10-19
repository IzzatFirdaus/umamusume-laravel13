<script setup lang="ts">
/*
 * The career section nav (plan slice 4.1): the run's identity strip and the nine career sections as
 * one tab row, so a Trainer moves between career screens without each page growing its own
 * "back to the Cockpit" pair.
 *
 * **The name is not the plan's.** 4.1 named `CareerHeader.vue`, but that path is already a live,
 * committed component with a different contract — the Cockpit's own identity and position card
 * (`<dl>` of Scenario / Year / Month / Turn / Energy, per SCREEN-009 §12), imported by
 * `Cockpit.vue`. This is the layout-level strip instead, so it takes the name its job describes.
 * The ruling and the evidence are recorded in the ledger.
 *
 * **An unreachable section is not a link.** DESIGN.md §5's 2026-10-17 amendment: `aria-disabled`
 * announces a state but does not cancel activation, and a `<Link>` navigates regardless of the
 * attribute. So an unreachable section renders as a focusable
 * `<span role="link" aria-disabled="true" tabindex="0">` carrying a `title` that names when it
 * arrives, and — this is the load-bearing half — no click handler and no href at all. Tab reaches it,
 * the state is announced, and Enter/Space do nothing.
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

/** Result is the one section a career does not have until it ends. */
const reachable = (key: string): boolean => (key === 'result' ? props.status !== 'active' : true);

const UNAVAILABLE_TITLE = 'Available when the career is completed';

const TAB =
    'inline-flex min-h-11 items-center rounded-md px-3 text-sm font-medium text-ink hover:bg-raised hover:text-ink-strong';
const TAB_CURRENT = 'bg-raised font-bold text-ink-strong';
const TAB_DISABLED = 'inline-flex min-h-11 items-center rounded-md px-3 text-sm font-medium text-ink-muted';

const pillVariant = computed<string>(() => (props.status === 'active' ? 'positive' : 'neutral'));
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
                    <span
                        v-else
                        role="link"
                        aria-disabled="true"
                        tabindex="0"
                        :title="UNAVAILABLE_TITLE"
                        :class="TAB_DISABLED"
                    >
                        {{ tab.label }}
                    </span>
                </li>
            </ul>
        </nav>
    </section>
</template>
