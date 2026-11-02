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
const { mode, layoutWidthClass } = useLayout();

/*
 * The shell's measure, as the wireframe applies it: one centring band per region, `26px` of side
 * padding at desktop and `16px` below the rail's breakpoint. It is a class string rather than a
 * wrapper so each band (bar content, page head, canvas) caps itself at the same measure as `main`,
 * which is what keeps the bar's own background running the full width while its content stops at
 * the measure.
 */
const bandClass = computed(() => `mx-auto w-full ${layoutWidthClass.value} px-4 md:px-[26px]`);

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
 * The collapsible rail, rebuilt to the HFW's rail block (docs/design-research/frontend-v2-wireframes/
 * assets/shell.css:146-311). Collapsed means marks instead of words, and expanded means words only:
 * the owner asked for the marks to appear at one width, not beside the labels at both, so a mark
 * never competes with the word it stands for.
 *
 * The rail is sticky from `md` up: `h-screen` gives it a definite viewport height, `self-start` stops
 * the flex row stretching it, and the nav scrolls inside the column while the foot stays pinned. The
 * HFW reaches the same shape through a grid column (`--rail-w`) and a 180ms `grid-template-columns`
 * transition; this shell is a flex row, so the collapse animates the child's width instead. The end
 * state is the measured HFW geometry at both widths. HFW-OVERRIDE (motion): width, not grid columns.
 *
 * Collapsing is still safe only because the word never leaves the DOM: the label moves to `sr-only`
 * rather than being removed, so every destination keeps its accessible name and gains a `title`
 * carrying it (design-2.0 §42, "text alternatives for icons"), and the active destination keeps
 * `aria-current="page"` plus the HFW's 3px inset left rule on top of its fill because state may never
 * ride on colour alone. The group headings stay in the tree as `sr-only` when the rail collapses,
 * where the HFW uses `display:none`; visually identical, and a screen reader keeps the group names
 * (HFW-OVERRIDE (a11y)).
 *
 * The control that collapses it lives in the rail head beside the brand (HFW index.html annotation
 * 10): the control belongs to the pane it resizes. Collapsed, the head stacks the 22px `TD` tile
 * above the toggle, so both fit the 56px column; the wordmark moves to `sr-only` and the toggle's
 * chevron turns to point the way the rail will move.
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
// as the same family as the chrome above it instead of adding a second hue beside it. The mobile
// bar keeps this fill; the rail's current row uses the HFW's chrome tint plus an inset rule.
const activeClass = 'bg-brand-chrome/10 font-semibold text-brand-chrome';

/*
 * The rail's geometry copies the HFW's rail block (docs/design-research/frontend-v2-wireframes/
 * assets/shell.css:146-311), measured in Chromium at 1280x800: a 236px sticky column collapsing to
 * 56px, a 64px head (84px collapsed), a 22px TD tile, 13.5px rows with 11px inline padding and
 * 1px/4px margins, 10px group headings inset 31px, a 6px dot spacer, a 3px x 28px current rule at
 * the rail edge, and a 44px foot at 13px/15px. The house scale rounds those (13.5 to 14, 10 to 12,
 * 31 to 32), so the exact values live here with the HFW as their source rather than as static
 * arbitrary classes in the template. HFW-OVERRIDE (a11y): the group headings move to `sr-only`
 * rather than leaving the tree, so a collapsed rail keeps its group names.
 */
const railHeadClass = 'flex items-center gap-2 px-2.5 pb-2 pt-3';
const railHeadCollapsedClass = 'flex flex-col items-center gap-0.5 px-0 pb-1.5 pt-2.5';
const railBrandClass = 'flex min-w-0 flex-1 items-center gap-[9px]';
const railBrandCollapsedClass = 'flex flex-none items-center justify-center';
const railMarkClass =
    'grid size-5.5 shrink-0 place-items-center rounded-sm bg-brand-chrome text-[11px] font-black text-on-brand-chrome';
const railWordClass = 'overflow-hidden whitespace-nowrap text-xs font-black leading-normal tracking-widest text-ink-strong';
const railGroupClass =
    'px-[31px] pb-[5px] pt-3 text-[10px] font-extrabold uppercase tracking-[0.13em] text-ink-muted';
const railItemClass =
    'relative mx-1 my-px flex min-h-11 items-center gap-2.5 rounded-sm px-[11px] text-[13.5px]';
const railItemCollapsedClass =
    'relative mx-1 my-px flex min-h-11 items-center justify-center gap-2.5 rounded-sm px-0 text-[13.5px]';
const railItemIdleClass = 'font-medium text-ink hover:bg-sunken';
const railItemCurrentClass = 'bg-brand-chrome/10 font-extrabold text-ink-strong';
const railItemDisabledClass = 'cursor-not-allowed font-medium text-ink-muted';
const railDotClass = 'size-1.5 flex-none rounded-full';
const railCurrentRuleClass = 'absolute -left-1 inset-y-2 w-[3px] rounded-r-[3px] bg-brand-chrome';
const railGlyphClass = 'size-4.5';
const railFootClass =
    'mt-auto flex min-h-11 items-center border-t border-rule-soft text-xs leading-normal text-ink-muted underline';
const railFootWideClass = 'px-[15px] py-[13px]';
const railFootCollapsedClass = 'justify-center px-0 py-[13px]';
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
    <div class="flex min-h-screen bg-page text-ink" :data-layout="mode">
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
                'hidden shrink-0 flex-col border-r border-rule-soft bg-panel transition-[width] duration-[180ms] ease-out md:sticky md:top-0 md:flex md:h-screen md:self-start motion-reduce:transition-none',
                railCollapsed ? 'w-14' : 'w-[236px]',
            ]"
        >
            <!-- The rail head carries the brand and the control that resizes this pane, because the
                 control belongs to the thing it resizes (HFW index.html annotation 10). Expanded,
                 the 22px TD tile and the word share one row and the 44px chevron toggle sits at the
                 far end; collapsed, the head stacks the tile above the toggle so both fit 56px, and
                 the word moves to `sr-only` (WCAG 2.5.3 and 1.3.1). -->
            <div :class="railCollapsed ? railHeadCollapsedClass : railHeadClass">
                <span :class="railCollapsed ? railBrandCollapsedClass : railBrandClass">
                    <span aria-hidden="true" :class="railMarkClass" data-rail-mark>TD</span>
                    <span :class="[railWordClass, railCollapsed ? 'sr-only' : '']">TRAINER DESK</span>
                </span>
                <button
                    type="button"
                    class="inline-flex min-h-11 min-w-11 shrink-0 items-center justify-center rounded-sm text-ink-muted hover:bg-sunken hover:text-ink-strong"
                    :aria-expanded="!railCollapsed"
                    aria-controls="primary-nav-rail"
                    :aria-label="railCollapsed ? 'Expand navigation' : 'Collapse navigation'"
                    :title="railCollapsed ? 'Expand navigation' : 'Collapse navigation'"
                    @click="toggleRail"
                >
                    <span class="sr-only">{{ railCollapsed ? 'Expand navigation' : 'Collapse navigation' }}</span>
                    <NavGlyph
                        name="chevron"
                        :class="[
                            railGlyphClass,
                            'transition-transform',
                            'stroke-[1.8]',
                            railCollapsed ? '' : 'rotate-180',
                            'motion-reduce:transition-none',
                        ]"
                    />
                </button>
            </div>
            <nav id="primary-nav-rail" aria-label="Global navigation" class="flex-1 overflow-y-auto">
                <div v-for="group in groups" :key="group.label">
                    <p :class="railCollapsed ? 'sr-only' : railGroupClass">{{ group.label }}</p>
                    <ul>
                        <li v-for="item in group.items" :key="item.label">
                            <Link
                                v-if="item.spa"
                                :href="item.to as string"
                                :class="[
                                    railCollapsed ? railItemCollapsedClass : railItemClass,
                                    isCurrent(item.to as string) ? railItemCurrentClass : railItemIdleClass,
                                ]"
                                :aria-current="isCurrent(item.to as string) ? 'page' : undefined"
                                :title="item.label"
                                :prefetch="item.prefetches ? 'hover' : false"
                            >
                                <span
                                    v-if="!railCollapsed"
                                    aria-hidden="true"
                                    data-rail-dot
                                    :class="[railDotClass, isCurrent(item.to as string) ? 'bg-brand-chrome' : 'bg-ink-faint']"
                                />
                                <NavGlyph v-if="railCollapsed" :name="item.icon" class="size-4.5" />
                                <span :class="railCollapsed ? 'sr-only' : ''">{{ item.label }}</span>
                                <span
                                    v-if="isCurrent(item.to as string)"
                                    aria-hidden="true"
                                    data-rail-current
                                    :class="railCurrentRuleClass"
                                />
                            </Link>
                            <a
                                v-else-if="item.to"
                                :href="item.to"
                                :class="[
                                    railCollapsed ? railItemCollapsedClass : railItemClass,
                                    isCurrent(item.to) ? railItemCurrentClass : railItemIdleClass,
                                ]"
                                :title="item.label"
                            >
                                <span
                                    v-if="!railCollapsed"
                                    aria-hidden="true"
                                    data-rail-dot
                                    :class="[railDotClass, isCurrent(item.to) ? 'bg-brand-chrome' : 'bg-ink-faint']"
                                />
                                <NavGlyph v-if="railCollapsed" :name="item.icon" class="size-4.5" />
                                <span :class="railCollapsed ? 'sr-only' : ''">{{ item.label }}</span>
                                <span
                                    v-if="isCurrent(item.to)"
                                    aria-hidden="true"
                                    data-rail-current
                                    :class="railCurrentRuleClass"
                                />
                            </a>
                            <span
                                v-else
                                :class="[railCollapsed ? railItemCollapsedClass : railItemClass, railItemDisabledClass]"
                                title="Coming with Trainer Desk 2.0"
                            >
                                <span
                                    v-if="!railCollapsed"
                                    aria-hidden="true"
                                    data-rail-dot
                                    :class="[railDotClass, 'bg-ink-faint']"
                                />
                                <NavGlyph v-if="railCollapsed" :name="item.icon" class="size-4.5" />
                                <span :class="railCollapsed ? 'sr-only' : ''">{{ item.label }}</span>
                                <span v-if="!railCollapsed" class="ml-2 text-xs">not built</span>
                            </span>
                        </li>
                    </ul>
                </div>
            </nav>
            <Link
                href="/database/sources"
                title="Data sources"
                :class="[railFootClass, railCollapsed ? railFootCollapsedClass : railFootWideClass]"
            >
                <span :class="railCollapsed ? 'sr-only' : ''">Data sources</span>
            </Link>
        </aside>

<!-- The HFW's `.main` column: bar, page head, canvas. Three bands, each capped at the shell
             measure, because the wireframe sets one measure per band and lets the rail own the rest
             (`shell.css:351-367`). The bar is 41px, not the older 48px row: `10px` padding either
             side of a `13px` line is what the wireframe paints, and the bar's height is a
             consequence of its content rather than a fixed row. -->
        <div class="flex min-w-0 flex-1 flex-col">
            <header role="banner" class="flex flex-col">
                <!-- Blue chrome, pink hairline under it (owner override 2026-10-26; the HFW's own pair,
                     #2255FF and #FF77CC, resolve through the house tokens so a theme flip keeps the
                     relationship rather than the hex). -->
                <div
                    class="w-full border-b-2 border-b-brand-accent bg-brand-chrome text-on-brand-chrome"
                    data-shell-topbar
                >
                    <div
                        :class="[bandClass, 'flex items-center gap-4 py-2.5']"
                    >
                        <!-- The crumb names where the Trainer is, not the product. The product name lives
                             in the rail head, so the bar spends its width on the one fact the rail cannot
                             carry: the section path. A page passes `breadcrumb`; the fallback names the
                             layout's own destination so the bar is never empty. Separator is the
                             interpunct every HFW crumb uses (`Rice Shower · Unity Cup · Cockpit`), not a
                             slash. HFW-OVERRIDE (computed: font-weight): the cockpit HFW bolds its
                             scenario while `Races` and `Save Veteran` in the same set bold the screen, so
                             the crumb follows the set's dominant rule and bolds its leaf. -->
                        <span
                            class="min-w-0 text-[12.5px] [&_b]:font-extrabold"
                            data-shell-crumb
                        >
                            <template v-if="breadcrumbParts.length">
                                <template v-for="(part, index) in breadcrumbParts" :key="`${part.label}-${index}`">
                                    <span v-if="index > 0" aria-hidden="true"> · </span>
                                    <Link
                                        v-if="part.href"
                                        :href="part.href"
                                        class="min-h-11 inline-flex items-center underline underline-offset-2 hover:decoration-2"
                                    >{{ part.label }}</Link>
                                    <b v-else>{{ part.label }}</b>
                                </template>
                            </template>
                            <b v-else>Trainer Desk</b>
                        </span>
                        <span class="flex-1" />
                        <!--
                          THE `#who` SLOT CONTRACT (stated once; S8).

                          The HFW topbar carries one state pill at `.who`. Its contract:

                          * A page MAY fill `#who` with exactly ONE pill describing the fact a
                            Trainer needs on that screen. A run-scoped screen supplies the run's own
                            state (`Turn 67 · Active`, or `Completed`) because the count of careers is
                            not that fact.
                          * A page that fills nothing gets the shell default: the shared active-career
                            count, which costs no per-controller query because the middleware shares it.
                          * Absent at zero. No active career is a normal state, not a warning, so the
                            pill does not render at all (`v-if="activeCareerCount > 0"`).
                          * One pill only. This is a status line, not a toolbar; a page needing two
                            facts states the second inside its own canvas.

                          OWNERSHIP OF THE THREE PAGE FILLS: the review queue's "N pending", the
                          preflight "N warning" and the veterans "N banked" pills are page-side work
                          and DO NOT land in S8. S8 is shell-only by its own gate ("do not take file
                          claims on page files during S8"), so each fill lands with the slice that
                          owns its page, carrying prop + query source + pin in one commit.
                        -->
                        <slot name="who">
                            <span
                                v-if="activeCareerCount > 0"
                                class="inline-flex shrink-0 items-center rounded-pill bg-status-positive px-2.5 py-[3px] text-[11.5px] font-extrabold leading-[1.45] text-status-positive-ink"
                                data-shell-career-pill
                            >{{ activeCareerCount }} active career{{ activeCareerCount === 1 ? '' : 's' }}</span>
                        </slot>
                        <!-- Help, reachable from every screen (HFW topbar `.toputils`). -->
                        <Link
                            href="/help"
                            class="inline-flex min-h-11 shrink-0 items-center gap-1.5 rounded-pill px-2.5 text-[12.5px] font-extrabold text-on-brand-chrome hover:bg-on-brand-chrome/10"
                            title="Help and documentation"
                        >
                            <span aria-hidden="true">?</span>
                            <span>Help</span>
                        </Link>
                    </div>
                </div>
                <!-- The page head. Pages that render `PageHeader` in `#head` supply their own `h1`
                     and actions here and leave the band empty below; the fallback band remains for the
                     pages still filling `#title`. Both paths keep the heading in the banner region, so
                     exactly one `h1` reaches the document. -->
                <div v-if="$slots.head" :class="[bandClass, 'pt-5']">
                    <slot name="head" />
                </div>
                <div
                    v-else-if="$slots.title"
                    :class="[bandClass, 'flex min-w-0 flex-col pt-5']"
                >
                    <p
                        v-if="$slots.eyebrow"
                        data-region="eyebrow"
                        class="text-[11px] font-extrabold uppercase tracking-[0.12em] text-ink-muted"
                    >
                        <slot name="eyebrow" />
                    </p>
                    <h1 class="min-w-0 text-3xl font-semibold leading-[1.12] tracking-[-0.01em] text-ink-strong focus:outline-none">
                        <slot name="title" />
                    </h1>
                    <!-- The HFW's `.lede`: the one sentence under the name, at a readable measure.
                         It sits on the fallback path only because `PageHeader` already renders the
                         same marker from its `description` prop (`PageHeader.vue:64`); a page that
                         adopts `PageHeader` stops filling this band, so the two never both print.
                         Same 5px offset and 13px size as `PageHeader`'s lede, so the band's DOM does
                         not change shape when a page migrates. -->
                    <p
                        v-if="$slots.lede"
                        data-region="lede"
                        class="mt-[5px] max-w-prose text-[13px] text-ink-muted"
                    >
                        <slot name="lede" />
                    </p>
                    <!-- The HFW's `.phead .hr`, and the marker `PageHeader` renders from its own
                         template (`PageHeader.vue:78`). The fallback band draws it too so the two
                         paths produce the SAME `.phead` DOM — eyebrow, hrow, lede, hr — and a page's
                         head does not change shape when it migrates from this band to `PageHeader`.
                         `aria-hidden` because it is a hairline, not content. -->
                    <div class="mt-4 border-t border-rule-soft" aria-hidden="true" data-page-head-rule data-region="hr" />
                </div>
            </header>
            <!-- The canvas. `@container` is `container-type: inline-size` and it is LOAD-BEARING:
                 twenty-one files lay themselves out with the `@2xl:`/`@3xl:`/`@4xl:`/`@5xl:` variants,
                 which resolve against this element rather than the viewport. Do not remove it to make
                 an element stick.

                 Its CONSEQUENCE is that `position: sticky` is a NO-OP for every descendant of this
                 `main`: the sticky containing block is the containment ancestor, not the scrollport, so a
                 sticky child scrolls away exactly as a static one does. `tests/browser/containment.spec.ts`
                 pins the current, deliberate behaviour.

                 `layoutWidthClass` still applies, so the Settings width preference keeps its effect;
                 standard is the wireframe's own measure, and compact/wide narrow or open it. -->
            <main
                id="main"
                data-layout-container
                class="@container mx-auto min-w-0 w-full flex-1 pb-24 md:pb-0"
                :class="layoutWidthClass"
            >
                <!-- Always mounted, so the live region exists before its message does (WCAG 4.1.3). -->
                <Flash :status="flashStatus" :token="page.props" />
                <div data-shell-band :class="[bandClass, 'pt-4 pb-16']">
                    <slot />
                </div>
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