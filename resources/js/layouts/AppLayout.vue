<script setup lang="ts">
import { Link, usePage } from '@inertiajs/vue3';
import { computed, nextTick, ref, watch } from 'vue';
import NavGlyph from '../components/NavGlyph.vue';

const page = usePage();
const appVersion = computed(() => page.props.app?.version ?? null);
const flashStatus = computed(() => page.props.flash?.status ?? null);

// The mobile bar's More disclosure. `<details>` is the native widget, so the expanded state, the
// `aria-expanded` announcement and the keyboard path come from the platform rather than from focus code
// written here. The state is mirrored in a ref because the layout instance survives a client-side visit:
// without closing on navigation the panel would stay open across the page change, and a Trainer who
// opened it once would meet it again on every screen.
const moreOpen = ref(false);
const moreSummary = ref<HTMLElement | null>(null);

watch(
    () => page.url,
    () => {
        moreOpen.value = false;
    },
);

function closeMore(): void {
    if (!moreOpen.value) {
        return;
    }

    moreOpen.value = false;

    void nextTick(() => moreSummary.value?.focus());
}

function onMoreToggle(event: Event): void {
    const node = event.target as HTMLDetailsElement | null;

    moreOpen.value = node?.open ?? false;
}

// "Am I on this destination?" answered as a prefix, not as equality, because a section's own
// sub-screens carry it in the URL: `/legacy/12` and `/legacy/compare` are both the Legacy Lab.
// `page.url` also carries the query string on a filtered list, so the comparison strips it —
// otherwise `aria-current="page"` silently falls off the moment a filter is applied. Exact
// equality had this working only on the bare index URLs, which is why it is stated here rather
// than left to look correct on the screen it was written for.
function isCurrent(to: string): boolean {
    const [path] = page.url.split('?');

    return path === to || path.startsWith(`${to}/`);
}

// The 2.0 navigation. The desktop sidebar has no numbered section of its own in
// `docs/proposals/design-2.0.md`: §41 is the only Navigation section the brief holds, and it owns the
// mobile bar below, so the sidebar's destinations are the shipped list rather than a cited one. An
// earlier comment here cited §28, which is Trackblazer Visual Language.
//
// `spa` marks the Inertia routes that client-navigate; the rest full-reload to the existing screens.
// `to: null` is the named-absence rule (`to: null` renders "not built" rather than a dead link) and no
// destination uses it now that the Veteran library landed as D16's read half. The branch stays because
// it is the mechanism `SetupLayout.vue` uses for the same case, and a destination that lands ahead of
// its own screen needs it rather than needing a re-write.
//
// "New Career" points at the wizard's step 1 (`career.scenario`). It used to point at `runs.create`, the
// pre-2.0 one-combobox form, while `SCREEN_SPEC.md` SCR-CAR-002 states the scenario step is "Reached
// from the Dashboard's New Career flow", so the D2-D7 wizard had no inbound click path at all. The old
// form keeps its own route and is still reached from a catalog page that carries a chosen trainee.
//
// "Careers" is the list (`runs.index`), which had no clickable inbound link either: the only reference
// was the redirect after a delete, so a Trainer who deleted a run arrived on a screen they could not
// get back to.
interface NavItem {
    label: string;
    /** The brief's shorter word for the mobile slot, or null to print `label`. */
    mobileLabel: string | null;
    /** null is the named-absence case: the destination exists in the plan and has no screen yet. */
    to: string | null;
    spa: boolean;
    /** Whether the destination takes one of design-2.0 §41's four slots beside "More". */
    inBar: boolean;
    /**
     * Hover-prefetch this destination's props and its lazy page chunk.
     *
     * `hover` rather than `mount`: prefetching all ten on every page load would put ten server visits
     * back onto the one-process dev server, which is the serialization this slice is trying to reduce.
     * Pointer intent is the screen the Trainer is probably about to open, and it is the one already
     * downloaded chunk for free.
     *
     * False on "New Career", and that is the whole exception. Inertia caches a prefetched response for
     * 30 seconds by default, and the wizard's steps render session-draft state: a reused prefetch would
     * show the scenario or trainee the Trainer had chosen *before* the save that just changed it. A
     * read-only catalog, library or settings screen cannot go stale in a way the Trainer caused, so those
     * take the prefetch and the draft flow does not.
     */
    prefetches: boolean;
    /**
     * The key into `NavGlyph`'s mark list, for the collapsed rail. Conceptual mapping from
     * `design-2.0` §44, filtered through the `DESIGN.md` §6 motif gate; `list` (Careers) and `spark`
     * (Skills) are the two §44 does not name a destination for, so they take its `Play / Flag` sibling
     * and its `Skill Points` mark. Never the whole name of a destination: the word stays in the DOM.
     */
    icon: string;
}

const items: NavItem[] = [
    { label: 'Dashboard', mobileLabel: 'Home', to: '/', spa: true, inBar: true, prefetches: true, icon: 'home' },
    { label: 'New Career', mobileLabel: null, to: '/career/setup/scenario', spa: true, inBar: false, prefetches: false, icon: 'flag' },
    { label: 'Careers', mobileLabel: 'Career', to: '/training-runs', spa: true, inBar: true, prefetches: true, icon: 'list' },
    { label: 'Legacy Lab', mobileLabel: 'Legacy', to: '/legacy', spa: true, inBar: true, prefetches: true, icon: 'network' },
    { label: 'Veterans', mobileLabel: null, to: '/veterans', spa: true, inBar: false, prefetches: true, icon: 'trophy' },
    { label: 'Support Cards', mobileLabel: 'Deck', to: '/support-cards', spa: true, inBar: true, prefetches: true, icon: 'cards' },
    { label: 'Skills', mobileLabel: null, to: '/skills', spa: true, inBar: false, prefetches: true, icon: 'spark' },
    { label: 'Review', mobileLabel: null, to: '/review', spa: true, inBar: false, prefetches: true, icon: 'triangle' },
    // /database, not /umamusume: the Database hub (SCREEN-023, plan §8 D17) is the label's
    // destination, and /umamusume is one of its five areas. Repointed rather than added, so this slice
    // contributes no destination. The count is ten live items with zero named absences, which is above
    // `frontend-development-plan.md` §13's "at or under eight"; the breach predates D17 and its repair is
    // the owner's ruling, not a quiet merge of two entries.
    { label: 'Database', mobileLabel: null, to: '/database', spa: true, inBar: false, prefetches: true, icon: 'book' },
    { label: 'Settings', mobileLabel: null, to: '/preferences', spa: true, inBar: false, prefetches: true, icon: 'gear' },
];

// design-2.0 §41: "Use a bottom navigation bar: Home, Career, Legacy, Deck, More" and "Do not attempt to
// shrink the desktop sidebar onto mobile." The ten-row sidebar used to render whole, which is the shape
// the brief forbids and the reason nothing looked scrollable at 320px. `mobileLabel` is the brief's word
// for the slot; the sidebar keeps the full name.
const barItems = items.filter((item) => item.inBar);
const moreItems = items.filter((item) => !item.inBar);

/*
 * The collapsible rail. Collapsed means marks instead of words, and expanded means words only: the owner
 * asked for the marks to appear at one width, not beside the labels at both, so a mark never competes
 * with the word it stands for.
 *
 * Collapsing is still safe only because the word never leaves the DOM: the label moves to `sr-only`
 * rather than being removed, so every destination keeps its accessible name and gains a `title` carrying
 * it (design-2.0 §42, "text alternatives for icons"), and the active destination keeps
 * `aria-current="page"` plus a left rule on top of its fill because state may never ride on colour alone.
 *
 * The choice lives in `localStorage`, not in a Preference row. `App\Models\Preference::KEYS` is exactly
 * `theme` and `failure_estimate` because PRD US-11 authorises those two and no more, so a third stored
 * key is a product-scope widening plus a Settings change; a display preference of the shell is not worth
 * that. The cost, stated rather than hidden: the rail follows this browser, not this Trainer, and it
 * does not appear on the Settings page.
 */
const RAIL_KEY = 'trainer-desk.nav-rail';

function readStoredRail(): boolean {
    try {
        return localStorage.getItem(RAIL_KEY) === 'compact';
    } catch {
        // A blocked storage area is a rail that opens wide, which is the state the app shipped in.
        return false;
    }
}

const railCollapsed = ref(readStoredRail());

function toggleRail(): void {
    railCollapsed.value = !railCollapsed.value;

    try {
        localStorage.setItem(RAIL_KEY, railCollapsed.value ? 'compact' : 'wide');
    } catch {
        // The toggle still works for this visit; only the memory of it is lost.
    }
}

const linkClass =
    'flex min-h-11 items-center rounded-md px-3 text-sm font-medium text-ink hover:bg-raised hover:text-ink-strong';
const activeClass = 'bg-raised text-ink-strong';
const disabledClass =
    'flex min-h-11 cursor-not-allowed items-center rounded-md px-3 text-sm font-medium text-ink-muted';
const mobileClass =
    'flex min-h-11 shrink-0 items-center whitespace-nowrap px-3 text-xs font-medium text-ink';
const mobileDisabledClass =
    'flex min-h-11 shrink-0 cursor-not-allowed items-center whitespace-nowrap px-3 text-xs font-medium text-ink-muted';
// The bar's own slot class, deliberately not `mobileClass`: five `shrink-0` slots side by side are wider
// than 320px, which is how the old strip overflowed in the first place. `flex-1 min-w-0` divides the
// viewport five ways and lets a label give way instead of pushing the bar past the screen edge.
const mobileBarClass =
    'flex min-h-11 flex-1 min-w-0 items-center justify-center whitespace-nowrap px-1 text-xs font-medium text-ink';
// `list-none` plus the webkit marker rule because a `<summary>` brings its own disclosure triangle, and
// the bar wants the word "More" to read as a destination slot rather than as a fieldset header.
const mobileMoreClass =
    'flex min-h-11 flex-1 cursor-pointer list-none items-center justify-center px-1 text-xs font-medium text-ink hover:bg-raised hover:text-ink-strong [&::-webkit-details-marker]:hidden';
</script>

<template>
    <div class="flex min-h-screen bg-page text-ink">
        <a
            href="#main"
            class="sr-only focus:not-sr-only focus:absolute focus:left-4 focus:top-4 focus:z-50 focus:rounded-md focus:border-2 focus:border-rule focus:bg-raised focus:px-4 focus:py-2 focus:text-sm focus:font-bold focus:text-ink-strong"
        >
            Skip to content
        </a>

        <aside
            :class="[
                'hidden shrink-0 flex-col border-r border-rule bg-panel transition-[width] md:flex motion-reduce:transition-none',
                railCollapsed ? 'w-14' : 'w-64',
            ]"
        >
            <div class="flex items-center justify-between gap-2 border-b border-rule px-2 py-3">
                <span :class="[railCollapsed ? 'sr-only' : '', 'text-sm font-bold tracking-wide text-ink-strong']">
                    TRAINER DESK
                </span>
                <button
                    type="button"
                    class="inline-flex min-h-11 shrink-0 items-center gap-1.5 rounded-md px-2 text-xs font-medium text-ink hover:bg-raised hover:text-ink-strong"
                    :aria-expanded="railCollapsed ? 'false' : 'true'"
                    aria-controls="primary-nav"
                    :title="railCollapsed ? 'Expand navigation' : undefined"
                    @click="toggleRail"
                >
                    <!-- The accessible name is always the action, and the visible word is a substring of
                         it (WCAG 2.5.3 Label in Name), so a voice-control user who says "Collapse" is
                         heard. Collapsed leaves no room for the word, so the mark carries the affordance
                         and the name still comes from the label. -->
                    <span class="sr-only">{{ railCollapsed ? 'Expand navigation' : 'Collapse navigation' }}</span>
                    <span v-if="!railCollapsed" aria-hidden="true">Collapse</span>
                    <span class="inline-flex shrink-0" aria-hidden="true">
                        <NavGlyph name="chevron" :class="railCollapsed ? '' : 'rotate-180'" />
                    </span>
                </button>
            </div>
            <nav id="primary-nav" aria-label="Primary" class="flex-1 p-2">
                <ul class="space-y-1">
                    <li v-for="item in items" :key="item.label">
                        <Link
                            v-if="item.spa"
                            :href="item.to as string"
                            :class="[
                                linkClass,
                                railCollapsed ? 'justify-center px-0' : '',
                                isCurrent(item.to as string) ? activeClass : '',
                                isCurrent(item.to as string) && railCollapsed ? 'border-l-2 border-l-green' : '',
                            ]"
                            :aria-current="isCurrent(item.to as string) ? 'page' : undefined"
                            :title="railCollapsed ? item.label : undefined"
                            :prefetch="item.prefetches ? 'hover' : false"
                        >
                            <NavGlyph v-if="railCollapsed" :name="item.icon" />
                            <span :class="railCollapsed ? 'sr-only' : ''">{{ item.label }}</span>
                        </Link>
                        <a
                            v-else-if="item.to"
                            :href="item.to"
                            :class="[linkClass, railCollapsed ? 'justify-center px-0' : '']"
                            :title="railCollapsed ? item.label : undefined"
                        >
                            <NavGlyph v-if="railCollapsed" :name="item.icon" />
                            <span :class="railCollapsed ? 'sr-only' : ''">{{ item.label }}</span>
                        </a>
                        <span
                            v-else
                            :class="[disabledClass, railCollapsed ? 'justify-center px-0' : '']"
                            title="Coming with Trainer Desk 2.0"
                        >
                            <NavGlyph v-if="railCollapsed" :name="item.icon" />
                            <span :class="railCollapsed ? 'sr-only' : ''">{{ item.label }}</span>
                            <span v-if="!railCollapsed" class="ml-2 text-xs">not built</span>
                        </span>
                    </li>
                </ul>
            </nav>
            <div class="border-t border-rule px-2 py-3 text-xs text-ink-muted">
                <span :class="railCollapsed ? 'sr-only' : ''">Local data · Trainer Desk</span>
            </div>
        </aside>

        <div class="flex min-w-0 flex-1 flex-col">
            <header
                role="banner"
                class="flex items-center justify-between gap-4 border-b border-rule bg-panel px-6 py-3"
            >
                <h1 class="text-base font-semibold text-ink-strong">
                    <slot name="title" />
                </h1>
                <span
                    v-if="appVersion"
                    class="font-mono text-xs tabular-nums text-ink-muted"
                    title="Application version"
                >
                    v{{ appVersion }}
                </span>
                <span v-else class="text-xs text-ink-muted" title="Application version not recorded">
                    N/A
                </span>
            </header>
            <main id="main" class="flex-1 px-6 py-6 pb-24 md:pb-6">
                <p
                     v-if="flashStatus"
                     role="status"
                     class="mb-4 rounded border border-green-line bg-green-tint px-3 py-2 text-sm text-ink"
                 >
                     {{ flashStatus }}
                 </p>
                <slot />
            </main>
        </div>

        <!-- Mobile navigation (docs/proposals/design-2.0.md §41): four destinations plus a More
             disclosure, in the brief's own slots. This used to render the whole ten-row sidebar inside
             one `overflow-x-auto` strip, which is the shape §41 forbids ("Do not attempt to shrink the
             desktop sidebar onto mobile") and the reason nothing at 320px looked scrollable: a
             horizontal-only scroller under a vertical gesture offers no affordance and answers no wheel. -->
        <nav
            aria-label="Bottom navigation"
            class="fixed inset-x-0 bottom-0 z-40 flex border-t border-rule bg-panel md:hidden"
        >
            <template v-for="item in barItems" :key="item.label">
                <Link
                    v-if="item.spa"
                    :href="item.to as string"
                    :class="[mobileBarClass, isCurrent(item.to as string) ? activeClass : '']"
                    :aria-current="isCurrent(item.to as string) ? 'page' : undefined"
                    :prefetch="item.prefetches ? 'hover' : false"
                >
                    {{ item.mobileLabel ?? item.label }}
                </Link>
                <a v-else-if="item.to" :href="item.to" :class="mobileBarClass">
                    {{ item.mobileLabel ?? item.label }}
                </a>
                <span v-else :class="mobileDisabledClass" title="Coming with Trainer Desk 2.0">
                    {{ item.mobileLabel ?? item.label }}
                    <span class="ml-1 text-xs">not built</span>
                </span>
            </template>

            <!-- `<details>` rather than a hand-rolled popover: the native widget carries the expanded
                 state, the announcement and the Enter/Space toggle, and the panel is plain document flow
                 that stays in the tab order. Escape closes it and puts focus back on the summary. -->
            <details
                :open="moreOpen"
                class="flex-1"
                @toggle="onMoreToggle"
                @keydown.esc="closeMore"
            >
                <summary ref="moreSummary" :class="mobileMoreClass">More</summary>
                <ul
                    class="fixed inset-x-0 bottom-11 max-h-[60vh] overflow-y-auto border-t border-rule bg-panel p-2"
                >
                    <li v-for="overflow in moreItems" :key="overflow.label">
                        <Link
                            v-if="overflow.spa"
                            :href="overflow.to as string"
                            :class="[mobileClass, isCurrent(overflow.to as string) ? activeClass : '']"
                            :aria-current="isCurrent(overflow.to as string) ? 'page' : undefined"
                            :prefetch="overflow.prefetches ? 'hover' : false"
                        >
                            {{ overflow.label }}
                        </Link>
                        <a v-else-if="overflow.to" :href="overflow.to" :class="mobileClass">
                            {{ overflow.label }}
                        </a>
                        <span v-else :class="mobileDisabledClass" title="Coming with Trainer Desk 2.0">
                            {{ overflow.label }}
                            <span class="ml-1 text-xs">not built</span>
                        </span>
                    </li>
                </ul>
            </details>
        </nav>
    </div>
</template>
