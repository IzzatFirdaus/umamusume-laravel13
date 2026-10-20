<script setup lang="ts">
import { Link, usePage } from '@inertiajs/vue3';
import { computed, nextTick, ref, watch } from 'vue';
import NavGlyph from '../components/NavGlyph.vue';
import VisitProgressBar from '../components/VisitProgressBar.vue';
import { useLayout } from '../composables/useLayout';

const page = usePage();

/*
 * The bar carries the product name and the collapse control, and nothing else. The build's version
 * string used to sit on the right, which made an implementation detail the last thing a Trainer read
 * on every screen; the footer's "Local data · Trainer Desk" line names the build's nature without
 * printing a number. `page.props.app` still arrives from `HandleInertiaRequests` for any screen that
 * wants it (the Settings screen reports it among its own facts); the shell just stops spending the
 * header on it.
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
const flashStatus = computed(() => page.props.flash?.status ?? null);

// The banner dismisses itself after five seconds and can be dismissed by hand (plan 6B.6).
// The watch keys on `page.props` identity, not on the message text: two saves in a row flash
// the same sentence, and a watch on the text alone would miss the second one after a dismiss.
const FLASH_DISMISS_MS = 5000;
const visibleFlash = ref<string | null>(flashStatus.value);
let flashTimer: ReturnType<typeof setTimeout> | undefined;

function scheduleFlashDismiss(): void {
    if (flashTimer !== undefined) {
        clearTimeout(flashTimer);
    }

    if (visibleFlash.value === null) {
        return;
    }

    flashTimer = setTimeout(() => {
        visibleFlash.value = null;
    }, FLASH_DISMISS_MS);
}

if (visibleFlash.value !== null) {
    scheduleFlashDismiss();
}

watch(
    () => page.props,
    () => {
        visibleFlash.value = flashStatus.value;
        scheduleFlashDismiss();
    },
);

function dismissFlash(): void {
    visibleFlash.value = null;

    if (flashTimer !== undefined) {
        clearTimeout(flashTimer);
    }
}

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
 * The control that collapses it lives in the bar, not in this column (plan 1.2): the bar is the one
 * row present at every width, so the control that resizes the rail belongs beside the product name
 * rather than inside the thing it resizes. It is hidden below `md`, where the column is hidden too.
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
            <!-- The product name left this column when it moved to the bar. What stays is the
                 collapsed rail's own label rule, so the rail keeps its top edge and does not float. -->
            <div v-if="railCollapsed" class="flex items-center justify-center border-b border-rule px-2 py-3 text-xs font-bold tracking-wide text-ink-strong">
                TD
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
            <!-- The bar is chrome, not a title block: one 48px row, the product name on the left and
                 the rail control on the right, closed by a 2px accent rule as its only ornament. It
                 must not carry the page `h1`: DESIGN.md §2.2's owner-signed 2026-10-17 amendment puts
                 the `h1` in the shell's `#title`, and one bar cannot be both 48px and hold an eyebrow
                 over a 30px heading. The heading therefore sits immediately below, as its own band on
                 the page field, which keeps its Critical treatment and keeps the bar's measured height.

                 The build's version string used to sit opposite the heading in the old single-row bar;
                 it is gone. The footer's "Local data · Trainer Desk" line names the build's nature, and
                 a number a Trainer cannot act on is not worth a permanent slot in the chrome. -->
            <header role="banner" class="flex flex-col">
                <div class="flex h-12 shrink-0 items-center justify-between gap-4 border-b-2 border-brand-chrome bg-panel px-4 text-ink md:px-8">
                    <span class="truncate text-sm font-semibold tracking-wide text-ink-strong">TRAINER DESK</span>
                    <!-- Rail control, desktop only. The sidebar is hidden below `md`, where the bottom
                         bar carries navigation, so a control that only resizes the sidebar has nothing
                         to act on at that width. The name is always the action and the visible word is
                         a substring of it (WCAG 2.5.3), exactly as the sidebar's own button was. -->
                    <!-- Text only, no chevron. The word is the action and the accessible name is
                         always the action, so the glyph added nothing a screen reader could use and
                         read as a stray "<" beside the word (1.3 screenshot inspection). The
                         5.5rem-ish width is stable because the two words are near enough in length. -->
                    <button
                        type="button"
                        class="hidden min-h-11 shrink-0 items-center rounded-md px-2 text-xs font-medium text-ink hover:bg-raised hover:text-ink-strong transition-colors duration-150 ease-out md:inline-flex"
                        :aria-expanded="!railCollapsed"
                        aria-controls="primary-nav"
                        :title="railCollapsed ? 'Expand navigation' : undefined"
                        @click="toggleRail"
                    >
                        <span class="sr-only">{{ railCollapsed ? 'Expand navigation' : 'Collapse navigation' }}</span>
                        <span aria-hidden="true">{{ railCollapsed ? 'Expand' : 'Collapse' }}</span>
                    </button>
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
            <main id="main" data-layout-container class="@container" :class="['mx-auto min-w-0 w-full max-w-6xl flex-1 px-4 py-6 pb-24 md:px-8 md:pb-6', layoutWidthClass]">
                <!-- The flash region is always mounted and carries only its text (WCAG 4.1.3): a live
                     region announces what changes inside it, so a region created in the same tick as its
                     message may never be announced. Empty, it is sr-only and says nothing; the classes
                     that paint the banner arrive with the message. The dismiss button is a sibling of
                     the region, never inside it: `role="status"` is read as its own text, and a control
                     in the region would join the announcement. -->
                <div
                    class="flex items-center justify-between gap-2"
                    :class="visibleFlash ? 'mb-4 rounded-md border border-rule bg-raised px-3 py-2' : ''"
                >
                    <p
                         role="status"
                         :class="visibleFlash ? 'text-sm text-ink' : 'sr-only'"
                     >
                         {{ visibleFlash ?? '' }}
                     </p>
                    <button
                        v-if="visibleFlash"
                        type="button"
                        aria-label="Dismiss"
                        class="inline-flex min-h-11 min-w-11 shrink-0 items-center justify-center text-ink-muted hover:text-ink-strong transition-colors duration-150 ease-out"
                        @click="dismissFlash"
                    >
                        ✕
                    </button>
                </div>
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
