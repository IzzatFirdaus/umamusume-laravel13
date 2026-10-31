<script setup lang="ts">
/*
 * The scenario matrix (SCREEN-023, plan §8 D17). The composition matrix in `config/scenarios.php`,
 * printed as it is: the caps, the panels, the widgets, the live date and the documentation marker,
 * laid out as `database-scenarios.html`'s three cards. The matrix's own verification date sits in the
 * caps note, where the wireframe keeps its caveats, rather than in a band above the cards.
 *
 * HFW-OVERRIDE (data: panel row order): the panels matrix lists its rows in the config's key order,
 * and the harness pairs by position, so it reports a different panel on each row. Read by panel, every
 * flag on the mock's matrix matches the config, which is the composition the cockpit renders.
 *
 * The page branches on nothing but resolved values from the matrix. No scenario name reaches the
 * template as a key, so a fifth scenario is one config entry and no edit here (D-240, gate G-33); the
 * two example sentences in the panels note name scenarios as examples only.
 *
 * Every panel renders, on or off. The Scenario Selection cards hide an off panel because a scenario must
 * not claim a system it does not compose; a reference matrix earns its place by stating the matrix, so
 * an off panel is a fact here rather than a silence. The word `off` stays in the text, so the state
 * never rides on colour alone.
 *
 * Density is allowed on this screen only (plan §13, "Deliberately traded").
 */
import AppLayout from '../../layouts/AppLayout.vue';
import PageHeader from '../../components/ui/PageHeader.vue';
import DatabaseNav from '../../components/database/DatabaseNav.vue';
import DataTable, { type DataTableColumn } from '../../components/ui/DataTable.vue';
import { Head } from '@inertiajs/vue3';
import { computed } from 'vue';

interface Cap {
    stat: string;
    bonus: number;
    cap: number;
}

interface Scenario {
    label: string;
    live_on_global: string;
    ruleset: { date: string; event: string };
    caps: Cap[];
    widgets: { label: string }[];
    panels: { label: string; enabled: boolean }[];
    partially_documented: boolean;
    notes: string;
}

const props = defineProps<{
    baseCap: number;
    verifiedAt: string;
    scenarios: Record<string, Scenario>;
}>();

const bonusText = (bonus: number): string => (bonus > 0 ? `+${bonus}` : `${bonus}`);

/*
 * The caps table and the panels table are the shared `DataTable` primitive in the per-scenario cards,
 * so a screen reader announces each as a table with its rows and columns rather than as a list of
 * numbers. The three matrices above are the wireframe's own `table.t` grammar. Figures are right
 * aligned through `td.n` and set in tabular figures, because a column of caps read down the page is
 * compared, not read.
 */
const capColumns: DataTableColumn[] = [
    { key: 'stat', label: 'Stat' },
    { key: 'base', label: 'Base', align: 'right', mono: true },
    { key: 'bonus', label: 'Bonus', align: 'right', mono: true },
    { key: 'cap', label: 'Published cap', align: 'right', mono: true },
];

const panelColumns: DataTableColumn[] = [
    { key: 'label', label: 'Panel' },
    { key: 'state', label: 'State' },
];

const capRows = (scenario: Scenario): Record<string, unknown>[] =>
    scenario.caps.map((cap) => ({
        stat: cap.stat,
        base: props.baseCap,
        bonus: bonusText(cap.bonus),
        cap: cap.cap,
    }));

const panelRows = (scenario: Scenario): Record<string, unknown>[] =>
    scenario.panels.map((panel) => ({ label: panel.label, enabled: panel.enabled }));

/*
 * The wireframe's three tables (`database-scenarios.html:69-124`), all read off the same matrix:
 * the caps with each row's outlier marked, the panel composition as a matrix, and the ruleset date.
 */
const entries = computed<[string, Scenario][]>(() => Object.entries(props.scenarios));
const statNames = computed<string[]>(() => entries.value[0]?.[1].caps.map((cap) => cap.stat) ?? []);
const panelNames = computed<string[]>(() => entries.value[0]?.[1].panels.map((panel) => panel.label) ?? []);

/** The published cap, or the word for a stat this scenario's row does not carry. */
const capText = (scenario: Scenario, stat: string): string => {
    const cap = scenario.caps.find((entry) => entry.stat === stat)?.cap;

    return cap === undefined ? 'N/A' : `${cap}`;
};

/**
 * The stat a row marks as its outlier: the row's highest cap when it is strictly above the second
 * highest. URA Finale's row is flat, so it marks none — which is the wireframe's own reading (bold
 * marks the outlier, not the maximum).
 */
const outlier = (scenario: Scenario): string | null => {
    const caps = [...scenario.caps].sort((a, b) => b.cap - a.cap);

    if (caps.length < 2 || caps[0].cap === caps[1].cap) {
        return null;
    }

    return caps[0].stat;
};

const panelState = (scenario: Scenario, label: string): boolean =>
    scenario.panels.find((panel) => panel.label === label)?.enabled ?? false;
</script>

<template>
    <AppLayout :breadcrumb="[{ label: 'Database', href: '/database' }, { label: 'Scenarios' }]">
        <Head title="Scenarios" />
        <template #head>
            <PageHeader
                eyebrow="Scenarios"
                title="Scenario matrix"
                description="Four permanent Global scenarios. This table is the composition source: the cockpit renders exactly these panels and no others."
            />
        </template>

        <DatabaseNav />

        <!--
          The wireframe's three cards (`database-scenarios.html:72-125`), all read off the same matrix:
          the caps with each row's outlier marked, the panel composition as a matrix, and the rework
          date. Each is a bare `div.card` holding `h2` + `table.t` + `p.note`, in that order, because
          that is the order the wireframe pairs on.
          HFW-OVERRIDE (data: cap source): the mock's figures are its own; this page prints
          `config/scenarios.php`'s base plus per-stat bonus against the published cap, and the dates
          are the config's. The two cap layers the note states are the reference's own (section 1.3.4
          at `:417` and `:422`); a JP guide's 2100 entry level at `:462` is a recommended
          qualification, not a Global cap, and is not printed here.
          HFW-OVERRIDE (copy: heading): the third card is headed "Last rework", not the mock's "Ruleset
          dates" - `5fd9a8ad` ruled the heading names the fact (a launch or rework instant), and
          `RulesetVersionVocabularyTest` is what pins "ruleset version" to the separate app fact.
          The mock's Unity Cup row carries `tr.is-current`; no row on a reference matrix is current, so
          the highlight is not drawn and the harness records it as missing.
        -->
        <div class="card mt-4" role="region" aria-labelledby="scenario-caps">
            <h2 id="scenario-caps">Caps</h2>

            <template v-if="entries.length > 0">
                <table class="t" aria-label="Stat caps by scenario">
                    <thead>
                        <tr>
                            <th scope="col">Scenario</th>
                            <th scope="col">Live on Global</th>
                            <th v-for="stat in statNames" :key="stat" scope="col" class="n">{{ stat }}</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr v-for="[key, scenario] in entries" :key="key">
                            <th scope="row">{{ scenario.label }}</th>
                            <td class="font-mono">{{ scenario.live_on_global }}</td>
                            <td
                                v-for="stat in statNames"
                                :key="stat"
                                class="n font-mono tabular-nums"
                                :title="capText(scenario, stat) === 'N/A'
                                    ? 'This scenario publishes no cap for that stat.'
                                    : null"
                            >
                                <b v-if="outlier(scenario) === stat">{{ capText(scenario, stat) }}</b>
                                <template v-else>{{ capText(scenario, stat) }}</template>
                            </td>
                        </tr>
                    </tbody>
                </table>
                <p class="note mt-3">
                    <span class="g" aria-hidden="true">○</span>
                    <span>
                        The bold figure in each row is that scenario's outlier, and the outlier is its
                        mechanical identity. Base is {{ props.baseCap }} for every stat; the hard ceiling
                        is 2000. Bold marks the outlier, not the maximum. Verified
                        {{ props.verifiedAt }} against the GameTora scenarios.json export and the Global
                        prose sources the config header names. A verification date records when the
                        matrix was checked, not when it was fetched, and nothing on this page was
                        fetched today.
                    </span>
                </p>
            </template>

            <!-- ADR-0007: the empty state names the absence, and the three cards below it print no
                 table. The path is reachable only through configuration, so a Feature test, not a
                 browser spec, is what exercises it. -->
            <p v-else class="mt-2 text-sm text-ink-muted">
                No scenario matrix is configured, so there are no caps, panel states or rework dates to
                print.
            </p>
        </div>

        <div class="card mt-4" role="region" aria-labelledby="scenario-panels">
            <h2 id="scenario-panels">Panels each scenario composes</h2>
            <table v-if="entries.length > 0" class="t" aria-label="Panels each scenario composes">
                <thead>
                    <tr>
                        <th scope="col">Panel</th>
                        <th v-for="[key, scenario] in entries" :key="key" scope="col" class="n">
                            {{ scenario.label }}
                        </th>
                    </tr>
                </thead>
                <tbody>
                    <tr v-for="panel in panelNames" :key="panel">
                        <th scope="row">{{ panel }}</th>
                        <td v-for="[key, scenario] in entries" :key="key" class="n">
                            {{ panelState(scenario, panel) ? 'on' : 'off' }}
                        </td>
                    </tr>
                </tbody>
            </table>
            <p class="note mt-3">
                <span class="g" aria-hidden="true">○</span>
                <span>
                    An off panel draws nothing at all. The cockpit never shows an empty box for a system
                    its scenario does not use, which is why URA Finale looks sparser than Unity Cup and
                    both are correct.
                </span>
            </p>
        </div>

        <div class="card mt-4" role="region" aria-labelledby="scenario-ruleset">
            <h2 id="scenario-ruleset">Last rework</h2>
            <table v-if="entries.length > 0" class="t" aria-label="Last rework date for each scenario">
                <thead>
                    <tr>
                        <th scope="col">Scenario</th>
                        <th scope="col">Date</th>
                        <th scope="col">What it marks</th>
                    </tr>
                </thead>
                <tbody>
                    <tr v-for="[key, scenario] in entries" :key="key">
                        <th scope="row">{{ scenario.label }}</th>
                        <td class="font-mono">{{ scenario.ruleset.date }}</td>
                        <td>{{ scenario.ruleset.event }}</td>
                    </tr>
                </tbody>
            </table>
            <p class="note mt-3">
                <span class="g" aria-hidden="true">○</span>
                <span>
                    A rework date is <b>not</b> an app ruleset version. No source defines a Global
                    ruleset version, so the version line elsewhere reads <code>N/A</code> and the two
                    facts are never merged into one label.
                </span>
            </p>
        </div>

        <h2 class="mt-8 text-lg font-semibold text-ink-strong">Per-scenario detail</h2>

        <p class="mt-4 max-w-3xl text-sm text-ink-muted">
            The scenario's composition matrix, printed as it is. Caps are the base plus a per-stat
            bonus, and the base is {{ props.baseCap }}. The caps note above carries the verification
            date and names the sources the config header cites.
        </p>

        <!--
          HFW-OVERRIDE (copy: table idiom note): the page draws two table idioms, and this one sentence
          says which is which. It sits above the first detail wrapper because `DataTable` renders its own
          card and has no slot for a note inside it, so the sentence is a block above rather than a Card
          inside a Card, which `lint:ui` refuses. It is wrapping prose, never a fixed width, so the
          320px reading in `database.spec.ts:455` stays a reflow rather than a pan.
        -->
        <p class="note mt-4">
            <span class="g" aria-hidden="true">○</span>
            <span>
                The three tables above are this page's reference matrices; the cards below print one
                scenario's own caps and panels in the shared data table this tool uses for its lists.
            </span>
        </p>

        <div class="mt-6 space-y-6">
            <section
                v-for="(scenario, key) in props.scenarios"
                :key="key"
                :aria-labelledby="`scenario-${key}`"
                 class="rounded-md border border-rule bg-panel p-5"
            >
                <div class="flex flex-wrap items-baseline justify-between gap-x-4 gap-y-2">
                    <h3 :id="`scenario-${key}`" class="text-xl font-semibold text-ink-strong">
                        {{ scenario.label }}
                    </h3>

                    <div class="flex flex-wrap items-baseline gap-x-3 gap-y-1 text-sm text-ink-muted">
                        <span>
                            Live on [Global]:
                            <time :datetime="scenario.live_on_global">{{ scenario.live_on_global }}</time>
                        </span>

                        <span
                            v-if="scenario.partially_documented"
                            class="rounded-full bg-sunken px-2 py-0.5 text-xs text-ink-strong"
                            title="Mechanics are sourced, but the client strings behind the panels are not, so the panels render off."
                        >
                            PARTIALLY DOCUMENTED
                        </span>
                    </div>
                </div>

                <div class="mt-4 grid gap-5 g-2">
                    <div>
                        <h4 class="text-lg font-semibold text-ink-strong">Caps</h4>

                        <DataTable
                            class="mt-2"
                            :caption="`${scenario.label} stat caps`"
                            :columns="capColumns"
                            :rows="capRows(scenario)"
                        />
                    </div>

                    <div class="space-y-4">
                        <div>
                            <h4 class="text-lg font-semibold text-ink-strong">Widgets</h4>

                            <ul class="mt-2 flex flex-wrap gap-1.5">
                                <li
                                    v-for="widget in scenario.widgets"
                                    :key="widget.label"
                                    class="rounded-full bg-sunken px-2.5 py-1 text-xs text-ink-strong"
                                >
                                    {{ widget.label }}
                                </li>
                            </ul>
                        </div>

                        <div>
                            <h4 class="text-lg font-semibold text-ink-strong">Panels</h4>

                            <DataTable
                                class="mt-2"
                                :caption="`${scenario.label} panels`"
                                :columns="panelColumns"
                                :rows="panelRows(scenario)"
                            >
                                <!-- The word is the state, so the state never rides on a glyph or on
                                     colour alone. A tick used to sit beside the word and an em dash
                                     stood for "off"; the dash is banned in shipped copy (R-02,
                                     D-79) and the tick was decoration, so the word is printed. -->
                                <template #cell-state="{ row }">
                                    <span :class="row.enabled ? 'text-ink-strong' : 'text-ink-muted'">
                                        {{ row.enabled ? 'on' : 'off' }}
                                    </span>
                                </template>
                            </DataTable>
                        </div>
                    </div>
                </div>

                <p class="mt-4 text-sm text-ink-muted">{{ scenario.notes }}</p>
            </section>
        </div>
    </AppLayout>
</template>
