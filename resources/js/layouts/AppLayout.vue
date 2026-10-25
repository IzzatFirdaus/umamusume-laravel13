<script setup lang="ts">
import { Link, usePage } from '@inertiajs/vue3';
import { computed, nextTick, ref, watch } from 'vue';
import Flash from '../components/ui/Flash.vue';
import NavGlyph from '../components/NavGlyph.vue';
import VisitProgressBar from '../components/VisitProgressBar.vue';
import { useLayout } from '../composables/useLayout';

const page = usePage();

/*
 * The bar carries the breadcrumb and the live-career pill, and nothing else: the product name and
 * the rail's collapse control moved to the rail head (HFW index.html annotation 10 — the control
 * belongs to the pane it resizes, and a global row owning a local width was plan 1.2's mistake).
 * The build's version string used to sit on the right, which made an implementation detail the last
 * thing a Trainer read on every screen; the footer's "Local data · Trainer Desk" line names the
 * build's nature without printing a number. `page.props.app` still arrives from
 * `HandleInertiaRequests` for any screen that wants it (the Settings screen reports it among its
 * own facts); the shell just stops spending the header on it.
 */

/*
 * The shell measure is read from `Layout width` (the renamed `dashboard_layout` preference). The
 * ScalaCaps-pre Rules: `compact` keeps the 768px reading measure, `standard` lands on the 1152px
 * shell, `wide` opens the 1536px shell (`--container-shell-wide`, DESIGN.md §2.3). The choice
 * reaches every AppLayout screen, not only the Dashboard, which is the workstream 1 change: the
 * shell is one renderer, every page inherits it. The class map lives in `useLayout.ts` as
 * `LAYOUT_WIDTH_CLASSES`, so the shell and the wizard share one source of truth.
 */
const { layoutWidthClass } = useLayout();

/*
 * The bar's crumb (HFW `.topbar > .crumb`). A page may pass its own trail — `Database / Sources` is
 * the shape the database area uses — and a page that passes nothing gets the destination the rail is
 * already marking current. The fallback is read from the same `isCurrent()` test the rail uses, so the
 * crumb and the `aria-current` item cannot disagree.
 */
const props = withDefaults(
    defineProps<{
        /** Optional explicit trail. The last part is the current screen and renders unlinked. */
        breadcrumb?: { label: string; href?: string }[];
    }>(),
    { breadcrumb: () => [] },
);

const breadcrumbParts = computed<{ label: string; href?: string }[]>(() => {
    if (props.breadcrumb.length > 0) {
        return props.breadcrumb;
    }

    const current = items.find((item) => item.to !== null && isCurrent(item.to));

    return current ? [{ label: current.label }] : [];
});

// The topbar pill's count, shared by `HandleInertiaRequests` so the shell does not query per page.
const activeCareerCount = computed<number>(() => {
    const count = (page.props as { activeCareerCount?: number }).activeCareerCount;

    return typeof count === 'number' ? count : 0;
});
// The banner itself lives in `ui/Flash.vue`: the region is always mounted, it arms its own
// five-second timer, and it keys on `page.props` identity rather than on the message text, so two
// saves in a row flash twice. This layout only hands it the current status.
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
// `docs/research-scratch/DESIGN-CORPUS.md` section `## design-2.0.md`: §41 is the only Navigation section the brief holds, and it owns the
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
     * `docs/research-scratch/DESIGN-CORPUS.md` section `## design-2.0.md` §44, filtered through the `DESIGN.md` §6 motif gate; `list` (Careers) and `spark`
     * (Skills) are the two §44 does not name a destination for, so they take its `Play / Flag` sibling
     * and its `Skill Points` mark. Never the whole name of a destination: the word stays in the DOM.
     */
    icon: string;
}

/*
 * The HFW's grouped rail (docs/design-research/frontend-v2-wireframes/assets/shell.js `NAV`):
 * Play / My data / Library / System. Grouping is how the ten flat items meet the "at or under
 * eight" target without deleting a destination — the HFW's own ruling for that breach, recorded as
 * annotation 4 on index.html. The group label is a heading for the eye; the collapsed rail hides it
 * because a heading over marks is noise at 56px.
 */
interface NavGroup {
    label: string;
    items: NavItem[];
}

const groups: NavGroup[] = [
    {
        label: 'Play',
        items: [
            { label: 'Dashboard', mobileLabel: 'Home', to: '/', spa: true, inBar: true, prefetches: true, icon: 'home' },
            { label: 'New Career', mobileLabel: null, to: '/career/setup/scenario', spa: true, inBar: false, prefetches: false, icon: 'flag' },
        ],
    },
    {
        label: 'My data',
        items: [
            { label: 'Careers', mobileLabel: 'Career', to: '/training-runs', spa: true, inBar: true, prefetches: true, icon: 'list' },
            { label: 'Legacy Lab', mobileLabel: 'Legacy', to: '/legacy', spa: true, inBar: true, prefetches: true, icon: 'network' },
            { label: 'Veterans', mobileLabel: null, to: '/veterans', spa: true, inBar: false, prefetches: true, icon: 'trophy' },
            { label: 'Review', mobileLabel: null, to: '/review', spa: true, inBar: false, prefetches: true, icon: 'triangle' },
        ],
    },
    {
        label: 'Library',
        items: [
            { label: 'Support Cards', mobileLabel: 'Deck', to: '/support-cards', spa: true, inBar: true, prefetches: true, icon: 'cards' },
            { label: 'Skills', mobileLabel: null, to: '/skills', spa: true, inBar: false, prefetches: true, icon: 'spark' },
        ],
    },
    {
        label: 'System',
        items: [
            // /database, not /umamusume: the Database hub (SCREEN-023, plan §8 D17) is the label's
            // destination, and /umamusume is one of its five areas.
            { label: 'Database', mobileLabel: null, to: '/database', spa: true, inBar: false, prefetches: true, icon: 'book' },
            { label: 'Settings', mobileLabel: null, to: '/preferences', spa: true, inBar: false, prefetches: true, icon: 'gear' },
        ],
    },
];

const items: NavItem[] = groups.flatMap((group) => group.items);

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
 * The control that collapses it lives in the rail head beside the brand (HFW index.html annotation
 * 10): the control belongs to the pane it resizes, and plan 1.2's placement in the 48px bar made a
 * global row own a local width. It is hidden below `md`, where the column is hidden too.
 * The collapsed column keeps a short `TD` mark so the rail does not float without a top edge.
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

// One brand accent on the surface: the header blue carries the active nav item, so the rail reads
// as the same family as the chrome above it instead of adding a second hue beside it.
const linkClass =
    'flex min-h-11 items-center rounded-md px-3 text-sm font-medium text-ink hover:bg-raised hover:text-ink-strong';
const activeClass = 'bg-brand-chrome/10 font-semibold text-brand-chrome';
// The sidebar's active rule sits on the left edge of the row at every rail width, so the current
// destination is marked by a shape as well as by a fill and a word colour (WCAG 1.4.1). Stock
// utilities only: `border-l-4` is the nearest step on the default ramp to the 3px the plan names,
// and a `[3px]` arbitrary value would trip the arbitrary-value lint that plan slice 1.12 installs.
// The mobile bar keeps a left rule out: a thick bar inside a 64px-wide bottom slot reads as
// misalignment rather than as state, and the bar carries `aria-current` and a raised fill instead.
const activeRailRule = 'border-l-4 border-l-brand-chrome pl-2';
const activeRailRuleCollapsed = 'border-l-4 border-l-brand-chrome';
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

/*
 * The bar carries five destinations and the disclosure carries the rest, so a Trainer standing on a
 * destination in the overflow saw the bar mark nothing at all. `computed`, not a const: `page.url`
 * changes under an Inertia visit, and a value taken once would freeze on the first screen.
 */
const moreIsCurrent = computed(() =>
    moreItems.some((item) => item.to !== null && isCurrent(item.to)),
);
</script>

<template>
    <div class="flex min-h-screen bg-page text-ink">
        <!-- The visit progress indicator (KI-63's replacement) teleports its element to <body>, so
             every shell that renders through this layout carries it and no page imports it. -->
        <VisitProgressBar />
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
            <!-- The rail head carries the brand and the control that resizes this pane, because the
                 control belongs to the thing it resizes (HFW index.html annotation 10; the earlier
                 placement in the 48px bar made a global row own a local width). Collapsed, the
                 wordmark shortens to the `TD` mark and the toggle keeps its icon; the accessible
                 name stays the action either way (WCAG 2.5.3). -->
            <div class="flex items-center gap-2 border-b border-rule px-2 py-2">
                <span
                    class="flex min-w-0 items-center gap-2 px-1 text-xs font-bold tracking-wide text-ink-strong"
                >
                    <span aria-hidden="true">TD</span>
                    <span :class="railCollapsed ? 'sr-only' : 'truncate'">TRAINER DESK</span>
                </span>
                <button
                    type="button"
                    class="ml-auto inline-flex min-h-11 shrink-0 items-center justify-center rounded-md px-2 text-xs font-medium text-ink hover:bg-raised hover:text-ink-strong transition-colors duration-150 ease-out"
                    :aria-expanded="!railCollapsed"
                    aria-controls="primary-nav-rail"
                    :title="railCollapsed ? 'Expand navigation' : undefined"
                    @click="toggleRail"
                >
                    <span class="sr-only">{{ railCollapsed ? 'Expand navigation' : 'Collapse navigation' }}</span>
                    <span aria-hidden="true">{{ railCollapsed ? '›' : '‹' }}</span>
                </button>
            </div>
            <nav id="primary-nav-rail" aria-label="Global navigation" class="flex-1 overflow-y-auto p-2">
                <div v-for="group in groups" :key="group.label" class="mb-2">
                    <p
                        :class="[
                            'px-3 pb-1 pt-2 text-[11px] font-bold uppercase tracking-wider text-ink-muted',
                            railCollapsed ? 'sr-only' : '',
                        ]"
                    >{{ group.label }}</p>
                    <ul class="space-y-1">
                        <li v-for="item in group.items" :key="item.label">
                            <Link
                                v-if="item.spa"
                                :href="item.to as string"
                                :class="[
                                    linkClass,
                                    railCollapsed ? 'justify-center px-0' : '',
                                    isCurrent(item.to as string) ? activeClass : '',
                                    isCurrent(item.to as string) && railCollapsed ? activeRailRuleCollapsed : '',
                                    isCurrent(item.to as string) && !railCollapsed ? activeRailRule : '',
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
                </div>
            </nav>
            <div class="border-t border-rule px-2 py-3 text-xs text-ink-muted">
                <Link
                    href="/database/sources"
                    class="inline-flex min-h-11 items-center underline"
                    :class="railCollapsed ? 'sr-only' : ''"
                >Data sources</Link>
            </div>
        </aside>

        <div class="flex min-w-0 flex-1 flex-col">
            <!-- The bar is chrome, not a title block: one 48px row, the breadcrumb on the left and
                 the live-career pill on the right, closed by a 2px accent rule as its only ornament. It
                 must not carry the page `h1`: DESIGN.md §2.2's owner-signed 2026-10-17 amendment puts
                 the `h1` in the shell's `#title`, and one bar cannot be both 48px and hold an eyebrow
                 over a 30px heading. The heading therefore sits immediately below, as its own band on
                 the page field, which keeps its Critical treatment and keeps the bar's measured height.

                 The build's version string used to sit opposite the heading in the old single-row bar;
                 it is gone. The footer's "Local data · Trainer Desk" line names the build's nature, and
                 a number a Trainer cannot act on is not worth a permanent slot in the chrome. -->
            <header role="banner" class="flex flex-col">
                <div class="flex h-12 shrink-0 items-center gap-4 border-b-2 border-brand-accent bg-brand-chrome px-4 text-on-brand-chrome md:px-8">
                    <!-- The crumb names where the Trainer is, not the product. The product name lives in
                         the rail head now (HFW shell), so the bar spends its width on the one fact the
                         rail cannot carry: the section path. A page passes `breadcrumb`; the fallback
                         names the layout's own destination so the bar is never empty. -->
                    <span class="min-w-0 truncate text-sm text-on-brand-chrome/80" data-shell-crumb>
                        <template v-if="breadcrumbParts.length">
                            <template v-for="(part, index) in breadcrumbParts" :key="`${part.label}-${index}`">
                                <span v-if="index > 0" aria-hidden="true" class="px-1">/</span>
                                <Link
                                    v-if="part.href"
                                    :href="part.href"
                                    class="underline hover:text-on-brand-chrome"
                                >{{ part.label }}</Link>
                                <span v-else class="font-semibold text-on-brand-chrome">{{ part.label }}</span>
                            </template>
                        </template>
                        <span v-else class="font-semibold text-on-brand-chrome">Trainer Desk</span>
                    </span>
                    <span class="flex-1" />
                    <!-- The live-career pill. Count only, shared from the middleware so every screen
                         can state it without a per-controller query. Absent at zero: no career is a
                         normal state, not a warning. -->
                    <span
                        v-if="activeCareerCount > 0"
                        class="inline-flex shrink-0 items-center rounded-full bg-panel px-2 py-0.5 text-xs font-semibold text-ink"
                        data-shell-career-pill
                    >{{ activeCareerCount }} active career{{ activeCareerCount === 1 ? '' : 's' }}</span>
                </div>
                <!-- The title band. Pages that render `PageHeader` in `#head` supply their own `h1`
                     there and leave this band empty, so exactly one `h1` reaches the document; the
                     band remains for the pages that still fill `#title` and have not adopted the
                     component yet. Both paths keep the heading in the banner region so the shell's
                     `role="banner"` landmark still names the page. -->
                <div v-if="$slots.head" class="border-b border-rule bg-page px-4 py-3 md:px-8">
                    <slot name="head" />
                </div>
                <div
                    v-else-if="$slots.title"
                    class="flex min-w-0 flex-col border-b border-rule bg-page px-4 py-3 md:px-8"
                >
                    <p
                        v-if="$slots.eyebrow"
                        class="text-xs font-semibold uppercase tracking-wider text-ink-muted"
                    >
                        <slot name="eyebrow" />
                    </p>
                    <h1 class="truncate text-3xl font-semibold text-ink-strong">
                        <slot name="title" />
                    </h1>
                </div>
            </header>
            <!-- The ratified shell measure (DESIGN.md:197-208): the error documents constrain
                 themselves to this width and the product shell now does too. The h1 above is the
                 shell's, per DESIGN.md §2.2's 2026-10-17 amendment; nothing here repeats it.

                 `@container` is `container-type: inline-size`, and it is LOAD-BEARING: twenty-one
                 files lay themselves out with the `@2xl:`/`@3xl:`/`@4xl:`/`@5xl:` variants, which
                 resolve against this element rather than the viewport. Do not remove it to make an
                 element stick.

                 Its CONSEQUENCE is that `position: sticky` is a NO-OP for every descendant of this
                 `main`: the sticky containing block is the containment ancestor, not the scrollport,
                 so a sticky child scrolls away exactly as a static one does. Every sticky claim that
                 used to live under here has been retired rather than left in place - the wizard step
                 bar (`SetupLayout.vue`), the "On this page" wayfinder (`Career/Cockpit.vue`), and the
                 summary strip's own class. `tests/browser/containment.spec.ts` pins the current,
                 deliberate behaviour, so a future containment fix updates that test instead of
                 silently restoring nothing. Making sticky work here is a design decision, not a
                 cleanup. -->
            <main id="main" data-layout-container class="@container" :class="['mx-auto min-w-0 w-full flex-1 px-4 py-6 pb-24 md:px-8 md:pb-6', layoutWidthClass]">
                <!-- Always mounted, so the live region exists before its message does (WCAG 4.1.3). -->
                <Flash :status="flashStatus" :token="page.props" />
                <slot />
            </main>
        </div>

        <!-- Mobile navigation (docs/proposals/design-2.0.md §41): four destinations plus a More
             disclosure, in the brief's own slots. This used to render the whole ten-row sidebar inside
             one `overflow-x-auto` strip, which is the shape §41 forbids ("Do not attempt to shrink the
             desktop sidebar onto mobile") and the reason nothing at 320px looked scrollable: a
             horizontal-only scroller under a vertical gesture offers no affordance and answers no wheel. -->
        <nav
            aria-label="Primary mobile"
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
                <summary
                    ref="moreSummary"
                    :class="[mobileMoreClass, moreIsCurrent ? activeClass : '']"
                    :aria-label="moreIsCurrent ? 'More, current section' : 'More'"
                >More</summary>
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
