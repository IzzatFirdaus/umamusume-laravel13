<script setup lang="ts">
/*
 * The scenario panel shell, `SCR-CAR-019` (SCREEN-014 / 015 / 016, plan Â§9 E1).
 *
 * **The UI does not know how a scenario works.** The shell draws the Â§49 shape it is handed and
 * switches on `panels` flags and `widgets` keys only. It reads no scenario name, holds no
 * per-scenario branch and registers nothing per scenario: a fifth scenario is one
 * `config/scenarios.php` entry and zero edits here (gate G-33, `D-240`). The section it receives
 * carries no scenario identity field at all (`ScenarioPanelTest` pins that), so there is nothing
 * here to branch on even by accident.
 *
 * **The fallback chain lives in one place.** For a widget key:
 * `resolve(key) ?? ResourceMeter ?? WidgetFallback` â€” E3 and E4 add their own renderers to the
 * registration block below, any widget the matrix labels falls to the meter, and anything else is
 * drawn as a labelled notice naming the key. For a panel flag: `resolve(flag) ?? WidgetFallback`.
 * Keeping the chain here is what makes it one behaviour to test rather than one per key.
 *
 * **The renderer contract, which E2 to E4 write against:**
 *  - a **widget** renderer receives `{ label, value, max, absence, kind: 'widget', name }`. One
 *    widget is one value, so the props are narrow.
 *  - a **panel** renderer receives `{ scenario, label, kind: 'panel', name }` â€” the whole section,
 *    because a panel composes several widgets and the Â§49 sections rather than one reading.
 *  - a renderer that cannot draw its key should not register for it; returning nothing would leave
 *    a blank in a region the reader is owed a sentence about.
 *
 * **The baseline strip is the panel region's own empty branch** (plan Â§9 E6, `D-241`, gate G-41). A
 * scenario that leaves every flag off mounts no renderer, and what it draws instead is the strip: its
 * name and documentation state, the published caps as base plus bonus, its own one-line reason its
 * panels are off, and what the advisor does and does not do here. None of that is scenario-specific
 * code: the caps come from the matrix, the reason is a config string, and the advisor line is a
 * payload field, so a fifth scenario reaches the same strip for one config entry (gate G-33).
 *
 * Props are declared locally: TypeScript 7 ships no `lib/typescript.js` for `@vue/compiler-sfc` to
 * load, so an imported type in `defineProps` fails the build (plan Â§11). The same shape is exported
 * from `resources/js/types.ts` for the slices that reason about it without declaring props.
 */
import type { Component } from 'vue';
import { computed } from 'vue';
import ProvenanceBadge from '../ProvenanceBadge.vue';
import AlertRow from './AlertRow.vue';
import RaceCalendarNote from './RaceCalendarNote.vue';
import ResourceMeter from './ResourceMeter.vue';
import ScenarioStatusBadge from './ScenarioStatusBadge.vue';
import TrackblazerPanel from './TrackblazerPanel.vue';
import UnityCupPanel from './UnityCupPanel.vue';
import WidgetFallback from './WidgetFallback.vue';
import { registerPanel, resolvePanel, resolveWidget } from './registry';

// Phase 7A.1: Cockpit panel components â€” registered against matrix flag keys
import UraMandatoryLadder from '@/Pages/Career/Cockpit/Panels/UraMandatoryLadder.vue';
import HappyMeekPanel from '@/Pages/Career/Cockpit/Panels/HappyMeekPanel.vue';
import CareerMilestonesPanel from '@/Pages/Career/Cockpit/Panels/CareerMilestonesPanel.vue';
import PreFinalReviewPanel from '@/Pages/Career/Cockpit/Panels/PreFinalReviewPanel.vue';
import TeamRaceFacilityPanel from '@/Pages/Career/Cockpit/Panels/TeamRaceFacilityPanel.vue';
import CareerGoalsPanel from '@/Pages/Career/Cockpit/Panels/CareerGoalsPanel.vue';

/**
 * The renderers this build carries, registered against the matrix's own flag keys. A panel is
 * reached by a flag, never by a scenario name, so a scenario that leaves `career_goals` off cannot
 * draw URA's rows and a fifth scenario needs one config entry and no edit here (gate G-33).
 */
// E3 (plan Â§9.1): the calendar note states where the Cockpit already draws the calendar, and the
// two Unity-Cup flags share one panel component that draws the region each registration is for.
registerPanel('race_calendar', RaceCalendarNote);
registerPanel('team_race', UnityCupPanel);
registerPanel('team_rank_ladder', UnityCupPanel);
// E4 (plan Â§9.4): the Trackblazer renderer draws grade_points, the shop, and the epithet
// checklist from the same component, branched on `props.name`.
registerPanel('grade_objectives', TrackblazerPanel);
registerPanel('shop', TrackblazerPanel);
registerPanel('epithet_routes', TrackblazerPanel);

// Phase 7A.1: Cockpit panel components â€” each flag maps to its own component
registerPanel('ura_mandatory_ladder', UraMandatoryLadder);
registerPanel('happy_meek', HappyMeekPanel);
registerPanel('career_milestones', CareerMilestonesPanel);
registerPanel('pre_final_review', PreFinalReviewPanel);
registerPanel('team_race_facility', TeamRaceFacilityPanel);
// The Phase 7A.1 registration of SpiritBurstsPanel against `spirit_bursts` is removed: that key
// is a WIDGET, not a panel flag, so the panel-shaped component received the widget shape and its
// template read `props.scenario.alerts` off undefined, crashing every Unity Cup cockpit render
// (measured in the 2026-10-16 browser run, TypeError at render). A renderer that cannot draw its
// key must not register for it, which is this shell's own contract; the widget falls back to the
// labelled meter, the shape it rendered before the extraction. The component stays on disk for
// the slice that gives the burst machine a panel flag or a widget-shaped renderer.
// `team_rank_ladder` is registered by E3's UnityCupPanel above, and that registration is the one
// that stays: the Phase 7A.1 TeamRankLadderPanel re-registration here shadowed it (a Map's last
// set wins), and the shadowed renderer draws the team facts card â€” the run's recorded team name,
// motto, counters and the rank gauge â€” while TeamRankLadderPanel itself reads a `scenario.ladder`
// key the section does not carry, so it rendered alerts and no ladder at all. Measured 2026-10-16:
// with the shadow in place a saved team name printed nowhere on the Cockpit, which is what the
// team-editor browser cases read back. UnityCupPanel is the live surface the Phase G ruling
// records ("already print in UnityCupPanel.vue:199-228"); the one-fact-once rule is honoured by
// drawing it once, not by retiring it.
// The Phase 7A.1 re-registrations of `grade_objectives`, `shop` and `epithet_routes` are removed:
// they shadowed E4's TrackblazerPanel above (a Map's last set wins), and none of the three reads the
// section the payload carries. `gradeSection()` nests the objectives under `scenario.grade`, the items
// under `scenario.shop.catalogue` and the checklist under `scenario.epithet.rows`, while the three
// components declare `scenario.objectives`, `scenario.shop` as a group list and `scenario.epithets`
// (plural, a key the section does not hold). The first of those is the career-goals ladder, so the
// shadow also drew one fact under two headings. TrackblazerPanel is the renderer whose prop reads
// exist; its three arms are what the Trackblazer cockpit draws. The components stay on disk for the
// slice that gives them a section of their own.
registerPanel('career_goals', CareerGoalsPanel);

interface PanelFlag {
    label: string;
    on: boolean;
}

const props = defineProps<{
    scenario: {
        label: string;
        declared: boolean;
        documented: boolean;
        version: string | null;
        version_title: string;
        /**
         * The scenario's own Global ruleset date â€” its launch or rework instant, sourced per
         * scenario â€” or null when no scenario is set. A different fact from `version` above,
         * which stays the null-by-ruling `app.ruleset`; the two render as separate lines and
         * must not merge (the R2-05 split, and the 2026-10-15 wiring's Caveat 1).
         */
        ruleset_date: string | null;
        ruleset_date_title: string;
        widgets: string[];
        widget_labels: Record<string, string>;
        widget_values: Record<string, number | string | null>;
        widgets_absence: string | null;
        panels: Record<string, PanelFlag>;
        objectives: unknown[];
        actions: unknown[];
        alerts: unknown[];
        recommendations: unknown[];
        recommendations_absence: string;
        /** What the scenario's config entry declares about its finale, or null. Not this run's state. */
        finale_structure: unknown;
        /**
         * This run's own position against its scenario's finale, read server-side by `FinaleReader`,
         * or null when the catalogue holds no such row. The Career Result prints the same shape under
         * the same key; the key above it, `finale_structure`, is a different question and is what the
         * config declares rather than what the run has done.
         */
        finale_state: {
            reached: boolean;
            outcome: string | null;
            placement: string | null;
            turn: number | null;
            label: string;
        } | null;
        finale_absence: string | null;
        /**
         * The published stat caps the baseline strip draws (plan Â§9 E6, D-241). `base` and `bonus`
         * travel separately because the matrix forbids collapsing them; `cap` is their sum through
         * `ScenarioCaps`.
         */
        caps: {
            verified_at: string;
            base: number;
            hard_cap: number;
            source_title: string;
            rows: { key: string; label: string; base: number; bonus: number; cap: number }[];
        };
        /** The scenario's own sentence for why its panels are off, or null where its entry has none. */
        panel_absence: string | null;
        /** Where that reason is recorded, for the `title`. */
        panel_absence_title: string | null;
    };
}>();

/** The panel flags this scenario composes, in the matrix's own order. */
const onPanels = computed<{ flag: string; label: string }[]>(() =>
    Object.entries(props.scenario.panels)
        .filter(([, panel]) => panel.on)
        .map(([flag, panel]) => ({ flag, label: panel.label })),
);

/**
 * Whether the scenario composes the career-goals ladder.
 *
 * That panel is a goal list, not a replacement for the published caps, so the baseline strip still
 * owes its caps table and its absent-panel line. The gate below used to read `onPanels.length === 0`,
 * which conflated "composes no panel at all" with "composes no cap-replacing panel" and hid the strip
 * behind the goals panel — the defect `grand-concert-panel.spec.ts:49` pins.
 */
const careerGoalsOn = computed<boolean>(() => props.scenario.panels['career_goals']?.on === true);

/**
 * The widget chain. `resolve` first, so a registered renderer always wins; then the meter, but
 * only for a key the matrix labels â€” a meter needs a name, and inventing one is the failure this
 * chain exists to avoid; then the notice that names the key instead.
 */
const resourceRenderer = (key: string): Component =>
    resolveWidget(key) ?? (props.scenario.widget_labels[key] !== undefined ? ResourceMeter : WidgetFallback);

/** The panel chain. A panel is not a resource, so the meter is not in this one. */
const panelRenderer = (flag: string): Component => resolvePanel(flag) ?? WidgetFallback;

const emptyText = computed<string>(() =>
    props.scenario.declared
        ? 'This scenario composes no panels of its own, so there is nothing to draw here. Its caps still apply to the stat bars above.'
        : 'No scenario is set for this run, so no scenario panel is composed. The run is measured against the baseline caps.',
);
</script>

<template>
    <div class="flex flex-col gap-3">
        <!-- The panels this scenario composes, drawn first. The Cockpit's own reading order is decision,
             then what the scenario says about it, then the scenario's identity strip below; an OFF flag
             still draws nothing at all, because the region names the composition, never the absence of a
             system the scenario does not use. -->
        <ul v-if="onPanels.length > 0" data-region="scenario-panels" class="flex flex-col gap-3">
            <li v-for="panel in onPanels" :key="panel.flag">
                <component
                    :is="panelRenderer(panel.flag)"
                    :scenario="props.scenario"
                    :label="panel.label"
                    kind="panel"
                    :name="panel.flag"
                />
            </li>
        </ul>

        <!-- The baseline strip: the scenario's own name, whether a guide is held for it, and the
             ruleset version absence. Every scenario carries these three, which is what makes the strip
             the floor a fifth scenario gets for one config entry. The heading says "version" because the
             scenario's own rule family is a different fact, printed under that name on the setup
             wizard's card (R2-05); this strip holds only the version, which no source defines. -->
        <div data-region="scenario" class="flex flex-wrap items-baseline gap-2">
            <p class="text-sm font-medium text-ink-strong">{{ props.scenario.label }}</p>
            <ScenarioStatusBadge :documented="props.scenario.documented" />
        </div>

        <p class="text-xs text-ink-muted">
            Ruleset version:
            <span :title="props.scenario.version_title">N/A</span>.
            {{ props.scenario.version_title }}
        </p>

        <!-- The scenario's own ruleset date: a sourced fact per scenario (its launch or rework
             instant), carried beside â€” never inside â€” the null version line above. Hidden rather
             than drawn as an absence when no scenario is set: a date that dates nothing is not a
             fact this strip owes a row for. -->
        <p v-if="props.scenario.ruleset_date !== null" class="text-xs text-ink-muted" data-region="scenario-ruleset-date">
            Scenario ruleset date:
            <span :title="props.scenario.ruleset_date_title">{{ props.scenario.ruleset_date }}</span>.
            {{ props.scenario.ruleset_date_title }}
        </p>

        <!-- The scenario's own resources, by the same subtraction the header uses: the baseline
             three are the header's figures and are not repeated here. -->
        <ul
            v-if="props.scenario.widgets.length > 0"
            class="grid grid-cols-1 gap-3 @3xl:grid-cols-2 @5xl:grid-cols-4"
        >
            <li v-for="key in props.scenario.widgets" :key="key">
                <component
                    :is="resourceRenderer(key)"
                    :label="props.scenario.widget_labels[key] ?? key"
                    :value="props.scenario.widget_values[key] ?? null"
                    :max="null"
                    :absence="props.scenario.widgets_absence"
                    kind="widget"
                    :name="key"
                />
            </li>
        </ul>

        <!-- The baseline strip: the render for a scenario that composes no panel at all (plan Â§9 E6,
             D-241, gate G-41). It is one branch for every scenario, so a fifth one that leaves every
             flag off reaches it for one config entry and no edit here. -->
        <div v-if="careerGoalsOn || onPanels.length === 0" class="flex flex-col gap-3">
            <!-- The generic sentence, only where the scenario declares no line of its own: a specific
                 reason beats a generic one, and printing both would say the same thing twice. -->
            <AlertRow
                v-if="props.scenario.panel_absence === null"
                glyph="○"
                :text="emptyText"
                tone="note"
            />

            <!-- The published caps, as the matrix states them: base and bonus are separate terms and
                 the cap is their sum through `ScenarioCaps`, never a collapsed number. Drawn only for
                 a scenario the run declared, because lending an undeclared run the baseline entry's
                 caps would state a composition nobody chose. -->
            <section v-if="props.scenario.declared" class="flex flex-col gap-2">
                <h3 class="flex flex-wrap items-baseline gap-2 text-lg font-semibold text-ink-strong">
                    Published stat caps
                    <span
                        class="text-xs font-normal normal-case tracking-normal"
                        title="The date the scenario matrix was last checked against its sources. Nothing here was fetched on it."
                    >Verified {{ props.scenario.caps.verified_at }}</span>
                </h3>
                <!-- ADR-0001 Â§2 puts the constants behind a number wherever the number appears, so the
                     matrix this read from is named on the row rather than only in the badge's `title`. -->
                <p class="text-xs text-ink-muted">{{ props.scenario.caps.source_title }}</p>

                <table class="w-full text-xs text-ink">
                    <caption class="sr-only">Published stat caps for this scenario</caption>
                    <thead>
                        <tr class="text-ink-muted">
                            <th scope="col" class="text-left font-medium">Stat</th>
                            <th scope="col" class="text-right font-medium">Base</th>
                            <th scope="col" class="text-right font-medium">Bonus</th>
                            <th scope="col" class="text-right font-medium">Cap</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr v-for="row in props.scenario.caps.rows" :key="row.key">
                            <th scope="row" class="text-left font-normal">{{ row.label }}</th>
                            <td class="text-right font-mono tabular-nums text-ink-muted">{{ row.base }}</td>
                            <td class="text-right font-mono tabular-nums text-ink-muted">{{ row.bonus }}</td>
                            <td class="text-right font-mono tabular-nums text-ink-strong">{{ row.cap }}</td>
                        </tr>
                    </tbody>
                </table>
            </section>

            <!-- The scenario's own reason its panels are off, where its entry declares one. This is an
                 absence, not a figure, so the `AlertRow` owns the whole statement: the owner ruled on
                 2026-10-09 that `ProvenanceBadge` qualifies a displayed value and `AbsenceValue`-shaped
                 prose owns the claim that no value exists. `DESIGN.md` Â§4.2 says the same in terms. -->
            <div
                v-if="props.scenario.panel_absence !== null"
                class="flex flex-wrap items-start gap-2"
            >
                <AlertRow
                    class="flex-1"
                    glyph="○"
                    :text="props.scenario.panel_absence"
                    :detail="props.scenario.panel_absence_title"
                    tone="note"
                />
            </div>

            <!-- The finale, where the scenario's calendar carries one. A position, not a decision:
                 no link and no action, because the action grid is where decisions live. Three states,
                 the same three the Career Result prints, read from one server-side reader (the
                 `FinaleReader`) so no two screens can disagree. The label is notice 905's noun; the
                 catalogue row's own title is KI-82, which is why it does not travel here. -->
            <p v-if="props.scenario.finale_state !== null" class="mt-2 text-sm text-ink">
                <span class="font-semibold text-ink-strong">Finale</span>:
                {{ props.scenario.finale_state.label }}.
                <template v-if="!props.scenario.finale_state.reached">Not yet reached.</template>
                <template v-else-if="props.scenario.finale_state.outcome === null">Reached; outcome not recorded.</template>
                <template v-else>
                    Completed: {{ props.scenario.finale_state.outcome }}<template v-if="props.scenario.finale_state.placement !== null">, {{ props.scenario.finale_state.placement }}</template><template v-if="props.scenario.finale_state.turn !== null"> on turn {{ props.scenario.finale_state.turn }}</template>.
                </template>
            </p>

            <!-- What the advisor does and does not do here, so the Trainer is not left guessing. -->
            <AlertRow glyph="○" :text="props.scenario.recommendations_absence" tone="note" />
        </div>
    </div>
</template>
