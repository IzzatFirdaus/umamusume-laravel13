<script setup lang="ts">
/*
 * HFW-OVERRIDE (computed/rect: the wizard-wide class) - `target-diff-{1280,390}.json` measures the
 * app's DESIGN.md tokens against the wireframe's mock CSS and the shell wrapper's offset. Tokens
 * outrank the mock palette (`AGENTS.md` §2) and the wrapper moves every box, so both buckets carry
 * entries by construction; the page is not restyled to chase the mock's values.
 */
/*
 * SCREEN-005, Build Target (`ADR-0020` §1; `docs/research-scratch/PLANS-AND-BRIEFS.md` section `## screen-spec-2.0.md` SCREEN-005). Step 3 of
 * the setup wizard, headed "Your target".
 *
 * **The page prints props and assembles nothing the server did not send.** The purpose options arrive
 * labelled through `uma.build_purpose`, the option vocabularies arrive as `BuildTargetPayload`'s own
 * constants, the stat matrix arrives as `statOrder`, and the caps arrive pre-computed by
 * `ScenarioCaps::forRun()` - the same call the write validates against (`ADR-0015`). That is why this
 * file names no stat, no band, no surface, no style and no scenario: `CareerBuildTargetTest` greps for
 * exactly that.
 *
 * **The server is the authority on the clamp.** The `max` attribute mirrors the cap as a convenience
 * for the browser; the write is refused server-side with the bound named when a number is above it,
 * and the refusal renders beside the input that caused it with focus moved to it.
 *
 * **The bar is not a readiness verdict.** Whether a target is reachable in the turns left is held
 * computation in this repository, so the bar is the entered number against the config cap and nothing
 * more, and it never renders without its number printed beside it.
 *
 * **The summary is the only derived text on the page** and it is built from the entered values alone,
 * which is what the badge beside it states. An empty field is named as an absence in the sentence
 * rather than skipped, so a reader is never told less than the form knows.
 */
import SetupLayout from '../../layouts/SetupLayout.vue';
import AbsenceValue from '../../components/AbsenceValue.vue';
import SubCell from '../../components/SubCell.vue';
import { BaseSelect, BaseInput, BaseButton } from '../../components/Form';
import { Head, Link, useForm } from '@inertiajs/vue3';
import { computed, nextTick, ref } from 'vue';

interface SelectOption {
    value: string;
    label: string;
}

interface StoredTarget {
    purpose: string;
    distance: string;
    surface: string;
    style: string;
    targets: Record<string, number>;
    skill_priorities: string[];
}

const props = defineProps<{
    /** The steps this session has opened, the step bar's completed marking reads them. */
    visited: number[];
    /** The steps that have data stored in the draft (`SetupDraft::saved()`), read apart from `visited`. */
    saved: number[];
    choices: { label: string; value: string | null; url: string | null }[];
    /** The stored payload as entered, or null before one exists. */
    target: StoredTarget | null;
    /** The Trainer's stored stat-target defaults (SCREEN-024), applied only where no target is stored. */
    statDefaults: Record<string, number> | null;
    /**
     * One ceiling per stat, from `ScenarioCaps::forRun` - the entered ceiling where the draft carries
     * one, the base cap alone when no scenario is chosen yet. This is the bound the write enforces.
     */
    caps: Record<string, number>;
    /** The same stats' published ceiling from the matrix, entered figures aside (`ADR-0025`). */
    scenarioCaps: Record<string, number>;
    /** The ceilings already entered, keyed by the columns the run holds them in; absent where none was. */
    enteredCaps: Record<string, number | null>;
    /** The stats that may carry an entered ceiling. Four, and the server's list decides which. */
    capStats: string[];
    /** The engine ceiling no entered figure may pass; the request enforces it, not this attribute. */
    hardCap: number;
    statOrder: string[];
    purposeOptions: SelectOption[];
    distanceBands: string[];
    /**
     * Each band's own sentence, printed beside its radio. Composed by the controller because the page
     * may not name a band or a stat (`CareerBuildTargetTest` enforces that), and the sentence does both.
     */
    distanceBandNotes: Record<string, string>;
    surfaces: string[];
    styles: string[];
    scenarioLabel: string | null;
    scenarioPending: boolean;
    /** `route('career.target.store')`, supplied by the server so the page never hard-codes the URL. */
    action: string;
    /**
     * The scenario stanza's own stamina floors, from `config/scenarios.php` via the controller. Read
     * rather than restated, so a published figure has one home.
     */
    finaleTargets: { staminaByDistance: Record<string, number> };
}>();

/**
 * One stored ceiling as a field value. Nothing stated is an empty field rather than a zero: the request
 * reads an empty field as "no ceiling stated, keep the scenario's own" (`ADR-0025`, D-220).
 */
const ceilingField = (column: string): string => {
    const value = props.enteredCaps[column];

    return value === null || value === undefined ? '' : String(value);
};

/**
 * The four `training_runs` columns that hold an entered ceiling. These are the request's own keys, not
 * displayed vocabulary: every stat *label* on this page still arrives through `statOrder` or `capStats`,
 * which is the claim the header above makes and `CareerBuildTargetTest` greps for.
 */
const capColumns = ['cap_speed', 'cap_stamina', 'cap_power', 'cap_guts'] as const;
type CapColumn = (typeof capColumns)[number];

/** The column that holds one stat's entered ceiling. `capStats` carries exactly these four stats. */
const capColumn = (stat: string): CapColumn => `cap_${stat.toLowerCase()}` as CapColumn;

const form = useForm<{
    purpose: string;
    distance: string;
    surface: string;
    style: string;
    targets: Record<string, number | string>;
    skill_priorities: string[];
    cap_speed: string;
    cap_stamina: string;
    cap_power: string;
    cap_guts: string;
}>({
    purpose: props.target?.purpose ?? '',
    distance: props.target?.distance ?? '',
    surface: props.target?.surface ?? '',
    style: props.target?.style ?? '',
    // No stored value is an empty field, never a pre-filled zero: a zero is a number the Trainer
    // did not enter, and the payload's required rule asks for every stat by name. The Trainer's
    // stored stat-target defaults (SCREEN-024) fill the gap only where no target is stored, so a
    // career's own target is never overwritten by a preference.
    targets: Object.fromEntries(
        props.statOrder.map((stat) => [
            stat,
            props.target?.targets?.[stat] ?? props.statDefaults?.[stat] ?? '',
        ]),
    ),
    skill_priorities: props.target?.skill_priorities ? [...props.target.skill_priorities] : [],
    // An unset ceiling is an empty field rather than a number: the request reads an absent value as "no
    // ceiling stated, keep the scenario's", and a zero here would be a reading nobody took (D-220).
    cap_speed: ceilingField('cap_speed'),
    cap_stamina: ceilingField('cap_stamina'),
    cap_power: ceilingField('cap_power'),
    cap_guts: ceilingField('cap_guts'),
});

const errors = computed<Record<string, string | undefined>>(
    () => form.errors as Record<string, string | undefined>,
);

// The DOM order of the focusable fields, so a failed submit focuses the first control the server
// refused rather than the first error key the response happens to carry.
const fieldOrder = computed<string[]>(() => [
    'purpose',
    'distance',
    'surface',
    'style',
    ...props.statOrder.map((stat) => `targets.${stat}`),
    ...capColumns,
    'skill_priorities',
]);

const controlId = (key: string): string => key.replace(/\./g, '_');
const errorId = (key: string): string => `error-${key.replace(/\./g, '_')}`;

// A key belongs to a field when it is that field or sits under it: each stat's error key lives under
// the `targets` map, and each skill-priority row key under `skill_priorities`.
const errorOf = (key: string): string | undefined => {
    const exact = errors.value[key];

    if (exact !== undefined) {
        return exact;
    }

    const nested = Object.keys(errors.value).find((candidate) => candidate.startsWith(`${key}.`));

    return nested === undefined ? undefined : errors.value[nested];
};

const describedBy = (key: string): string | undefined => (errorOf(key) === undefined ? undefined : errorId(key));

function submit(): void {
    // The write URL is supplied by the server (`career.target.store`) so the page never states it as
    // a literal. There is no Ziggy dependency here.
    form.put(props.action, {
        preserveScroll: true,
        onError: () => {
            const first = fieldOrder.value.find(
                (key) => errors.value[key] !== undefined || errorOf(key) !== undefined,
            );

            // A refusal against the whole `targets` map (a key outside the matrix) has no control of
            // its own, so it lands on the first stat input.
            const target = first
                ?? (errors.value.targets === undefined || props.statOrder.length === 0
                    ? undefined
                    : `targets.${props.statOrder[0]}`);

            if (target !== undefined) {
                nextTick(() => {
                    document.getElementById(controlId(target))?.focus();
                });
            }
        },
    });
}

const cap = (stat: string): number => props.caps[stat] ?? 0;

const entered = (stat: string): number | null => {
    const value = form.targets[stat];

    return typeof value === 'number' && Number.isFinite(value) ? value : null;
};

const percent = (stat: string): number => {
    const value = entered(stat);
    const ceiling = cap(stat);

    if (value === null || ceiling <= 0) {
        return 0;
    }

    return Math.min(100, Math.max(0, Math.round((value / ceiling) * 100)));
};

/** The sum of the five entered targets, or null while any one of them is still empty. */
const totalTarget = computed((): number | null => {
    const values = props.statOrder.map((stat) => entered(stat));

    return values.some((value) => value === null) ? null : (values as number[]).reduce((sum, value) => sum + value, 0);
});

/** The sum of the five caps this run is bounded by: the denominator the total is read against. */
const totalCap = computed((): number => props.statOrder.reduce((sum, stat) => sum + cap(stat), 0));


/**
 * Fills the five stat fields from the Trainer's stored defaults. Only ever called from the explicit
 * button, so a preference never overwrites a career's own target on load.
 */
function applyDefaults(): void {
    const defaults = props.statDefaults;

    if (defaults === null) {
        return;
    }

    props.statOrder.forEach((stat) => {
        const value = defaults[stat];

        if (typeof value === 'number') {
            form.targets[stat] = value;
        }
    });
}

</script>

<template>
    <SetupLayout :step="3" :visited="props.visited" :saved="props.saved" :choices="props.choices">
        <Head title="What are you building toward?" />
        <template #eyebrow>New career</template>
        <template #title>What are you building toward?</template>
        <template #lede>
            Five stat targets and the distance you are building for. These are your own goals, not a game
            rule, and the cockpit's bars are drawn against them.
        </template>

        <!--
          The HFW's head-band pair (`career-target.html:61-65`). The forward action is this step's write, not
          a navigation: the button lives here and posts the form below through the native `form` attribute,
          which is what lets it sit in the head band while the fields stay in the canvas. The write endpoint
          is unchanged (`career.target.store`), and the label is the wireframe's.

          `Back` points at the trainee step, which is this app's previous screen. The wireframe's own back
          link targets `career-profile.html`, but profile is a per-record read page here rather than a step
          in the flow, so the honest previous step is the roster.
        -->
        <template #actions>
            <Link
                href="/career/setup/trainee"
                class="inline-flex min-h-11 items-center justify-center rounded-md border border-rule bg-raised px-4 text-sm font-semibold text-ink hover:bg-panel"
            >
                Back
            </Link>
            <BaseButton type="submit" form="build-target-form" variant="primary" :disabled="form.processing">
                Continue to legacy
            </BaseButton>
        </template>

        <!-- No breadcrumb: "← Back to target" pointed at this same URL, a control that navigates to the
             page it is on. The step bar above lists all six steps and the carried-choices strip states
             the scenario and the trainee, so nothing is lost. Steps 1 and 2 carry the same note. -->

        <!-- The wireframe's labelled-defect pattern, kept in its shape. It states the failure the step
             exists to prevent rather than describing the implementation: a career whose target was never
             entered reads as a career with no goal at the moment it is committed. -->
        <div class="warn" data-field="target-defect">
            <b>This is the step the current build skips.</b>
            A run created with no target entered commits with five empty goals, and the cockpit then has
            nothing to measure a turn against. Filling these in is what makes the run's progress answerable.
        </div>

        <!--
          The page is the wireframe's page and nothing else (`career-target.html:87-195`): the warn band,
          then `.g-side` - stat targets with their totals on the left; distance, stamina floor and the
          saved default on the right. Four blocks the app used to add around that shell are gone, each
          because the HFW does not draw it: a `Summary` card that restated the whole form as one
          sentence; a "Scenario: … Back: Trainee / Change scenario" line duplicating the step bar's
          doors; a "What the career is for" fieldset (purpose / distance / surface / style); and a
          "Skill priorities" fieldset, which belongs to the post-wizard Skills Planner.

          Nothing the write needs is dropped with them: the four values keep travelling in the form
          payload (`BuildTargetPayload::KEYS` is untouched) and distance is asked for by the radio
          group below, which is where the HFW asks for it.
        -->
        <p v-if="props.target === null" class="mt-3 text-sm text-ink-muted">
            No target is recorded yet. The fields below start empty rather than at zero.
        </p>

        <!-- `novalidate` on purpose: the `max` attribute mirrors the cap for the browser only, and
             native constraint validation would block the submit with a browser bubble instead of
             letting the server refuse with its named bound, which is the message this page renders
             with its own `aria-describedby` and focus. -->
        <form id="build-target-form" class="mt-5 mx-auto max-w-4xl" novalidate @submit.prevent="submit">
            <!--
              The wireframe's two-column canvas. `.g-side` is `minmax(0,2.1fr) minmax(0,1fr)` and it
              collapses below 821px (frontend-v2-wireframes/assets/shell.css:404,406), so both the
              breakpoint and the proportions are the wireframe's own rather than a separate measure:
              the same reason the card grid keeps `min-[821px]:`. The blocks sit at their existing
              indentation so the move reads as two wrapper lines rather than a re-indent of the form.
            -->
            <div class="grid gap-3.5 min-[821px]:grid-cols-[minmax(0,2.1fr)_minmax(0,1fr)]">
            <div class="space-y-3.5">
            <!--
              HFW-OVERRIDE (required by the write): purpose, surface and style are asked for here although
              `career-target.html` draws no such fieldset.

              `StoreBuildTargetRequest::rules()` makes all four of `purpose`, `distance`, `surface` and
              `style` `required` the moment any stat carries a number, because `payload()` stores them and
              a target with no purpose is a stat list with no reason behind it. Deleting this fieldset would
              therefore leave a Trainer able to type five numbers and no way to satisfy the refusal: the
              page would render a form the server always rejects, and career creation would stop. That is a
              domain change (relaxing the request, or giving the wizard a default for each) and not a
              layout cut, so the fields stay and the divergence is recorded rather than silently created.

              `distance` is the one of the four the HFW does ask for, as the radio group in the Distance
              card below, so it is NOT repeated here; the group owns it and posts the same field name.
            -->
            <section data-region="card" class="card" aria-labelledby="target-purpose-heading">
                <h2 id="target-purpose-heading">What the career is for</h2>

                <div class="mt-2 grid gap-4 min-[821px]:grid-cols-2">
                    <BaseSelect
                        v-model="form.purpose"
                        :id="controlId('purpose')"
                        name="purpose"
                        label="Purpose"
                        :options="[{ value: '', label: 'Not chosen yet' }, ...props.purposeOptions]"
                        :aria-describedby="describedBy('purpose')"
                    />
                    <BaseSelect
                        v-model="form.surface"
                        :id="controlId('surface')"
                        name="surface"
                        label="Surface"
                        :options="[{ value: '', label: 'Not chosen yet' }, ...props.surfaces.map(s => ({ value: s, label: s }))]"
                        :aria-describedby="describedBy('surface')"
                    />
                    <BaseSelect
                        v-model="form.style"
                        :id="controlId('style')"
                        name="style"
                        label="Style"
                        :options="[{ value: '', label: 'Not chosen yet' }, ...props.styles.map(s => ({ value: s, label: s }))]"
                        :aria-describedby="describedBy('style')"
                    />
                </div>

                <p v-if="errorOf('purpose')" role="alert" class="mt-2 text-sm text-risk">{{ errorOf('purpose') }}</p>
                <p v-if="errorOf('surface')" role="alert" class="mt-2 text-sm text-risk">{{ errorOf('surface') }}</p>
                <p v-if="errorOf('style')" role="alert" class="mt-2 text-sm text-risk">{{ errorOf('style') }}</p>
            </section>

            <section data-region="card" class="card" aria-labelledby="stat-targets-heading">
                <h2 id="stat-targets-heading">Stat targets</h2>

                <p class="sub">
                    Each target is compared against the run's scenario cap, so a target above the cap is
                    refused at preflight rather than silently clipped.
                </p>

                <div class="mt-4 space-y-4">
                    <!-- Each stat is one `.field`: the wireframe puts the value, its denominator and the share of
                             the cap in a single labelled group, so the cap is read with the number rather
                             than matched up from a column header elsewhere. `data-region` rather than the
                             corpus' `.field` class because `.field` is unlayered in app.css and would win
                             over the utilities this row already carries. -->
                        <div v-for="stat in props.statOrder" :key="stat" :data-stat="stat" data-region="field" class="grid gap-3 min-[821px]:grid-cols-2">
                        <!-- Target column -->
                        <div class="space-y-1">
                            <BaseInput
                                v-model.number="form.targets[stat]"
                                :id="controlId(`targets.${stat}`)"
                                type="number"
                                :name="`targets[${stat}]`"
                                :aria-label="stat"
                                :min="0"
                                :max="cap(stat)"
                                inputmode="numeric"
                                :aria-describedby="describedBy(`targets.${stat}`)"
                                :label="stat"
                                value-class="font-mono text-ink tabular-nums"
                                :error="errorOf(`targets.${stat}`)"
                            />
                            <!--
                              The wireframe's field is one label, one number, its denominator and its
                              share of the cap (`career-target.html:101-125`). The three lines this cell
                              used to add - a second copy of the cap, a bar restating the same figure,
                              and a named absence standing where the number goes - all said what the
                              denominator already says, so they are gone and an empty field is simply
                              empty.
                            -->
                            <span class="font-mono text-xs text-ink-muted tabular-nums" data-field="denominator">
                                {{ entered(stat) ?? '-' }} / {{ cap(stat) }} cap
                            </span>
                            <span class="text-xs text-ink-muted" data-field="percent-of-cap">
                                <template v-if="entered(stat) !== null">{{ percent(stat) }}% of cap</template>
                                <template v-else>not entered, so no share of the cap is claimed</template>
                            </span>
                        </div>

                        <!-- Ceiling column (only for stats that have an enterable ceiling) -->
                        <div v-if="props.capStats.includes(stat)" class="space-y-1">
                            <BaseInput
                                v-model="form[capColumn(stat)]"
                                :id="controlId(capColumn(stat))"
                                type="number"
                                :name="capColumn(stat)"
                                :aria-label="`${stat} ceiling`"
                                :min="1"
                                :max="props.hardCap"
                                inputmode="numeric"
                                :aria-describedby="describedBy(capColumn(stat))"
                                :label="`${stat} ceiling`"
                                value-class="font-mono text-ink tabular-nums"
                                class="w-full"
                                :error="errorOf(capColumn(stat))"
                            />
                        </div>
                        <!-- Placeholder for stats without ceiling (the fifth stat) to keep grid alignment -->
                        <div v-else class="space-y-1">
                            <span class="text-xs text-ink-muted">No enterable ceiling for this stat</span>
                        </div>
                    </div>
                </div>

                <p
                    v-if="errors.targets"
                    :id="errorId('targets')"
                    role="alert"
                    class="mt-3 rounded-md border border-risk bg-raised px-3 py-2 text-sm text-ink"
                >{{ errors.targets }}</p>
            </section>

            <!-- The totals block: what the entered figures add up to, beside the one published build the
                 page names and the ceiling the five caps sum to. Three different claims, three rows, and
                 the note says which one is a requirement (none of them is). -->
            <!--
              The totals block is the third part of the wireframe's Stat targets card, not a card of its
              own (`career-target.html:128-138` nests it inside). It keeps its own element so the rows can
              be read as a group, but it no longer claims `data-region="card"`: a card marker here made the
              page render five cards where the HFW draws four.
            -->
            <section class="card" aria-label="Total against the published sample">
                <!--
                  The wireframe labels this block "Total against the published sample" and gives it no
                  heading of its own (`career-target.html:128-138`): it is the third part of the Stat
                  targets card, not a card called "Totals". The `h2` went with the name, so the card
                  count and the heading order now match the HFW's four cards.
                -->
                <p class="text-[10px] font-extrabold uppercase tracking-[0.07em] text-ink-muted">
                    Total against the published sample
                </p>

                <div class="mt-2" data-field="target-totals">
                    <div class="srow">
                        <span class="k">Your total</span>
                        <span class="v font-mono tabular-nums">
                            <span
                                v-if="totalTarget === null"
                                title="No total is printed because at least one of the five targets is still empty."
                            >N/A</span>
                            <template v-else>{{ totalTarget }}</template>
                        </span>
                    </div>
                    <div class="srow">
                        <span class="k">Published Global sample build <em>Special Week</em></span>
                        <span class="v font-mono tabular-nums">
                            <span title="This repository stores no sample-build column, so the published sample total cannot be sourced.">N/A</span>
                        </span>
                    </div>
                    <div class="srow">
                        <span class="k">Sum of the five scenario caps</span>
                        <span class="v font-mono tabular-nums">{{ totalCap }}</span>
                    </div>
                </div>

                <p class="note mt-2">
                    The published Global sample is one publisher's build, not a requirement and not this
                    tool's recommendation: it is a figure to compare against, and a career below it is not
                    a worse career. This repository stores no sample-build column, so the row prints N/A
                    rather than a number it cannot source.
                </p>
            </section>

            </div>

            <!-- The right column of the wireframe's `.g-side`: Distance, then Stamina floor. The 1400 m
                 row is the wireframe's `.note` inside the Distance card (career-target.html:159), not a
                 warning band: the page states a source disagreement rather than refusing a submit. -->
            <div class="space-y-3.5">
            <section data-region="card" class="card" aria-labelledby="distance-bands">
                <h2 id="distance-bands">Distance</h2>
                <p class="sub">Sets the stamina floor the advisor warns about before a race.</p>

                <!--
                  The wireframe asks for the distance as a radio group with each band's own sentence
                  beside it (`career-target.html:141-158`), not as a row of pills: the choice is one of
                  four and only one can hold, which is what a radio group means to a keyboard and a
                  screen reader. The bands themselves still arrive from the server, so the descriptions
                  are looked up by band name and a fifth band renders with its name and no sentence
                  rather than with an invented one.
                -->
                <fieldset class="mt-2">
                    <legend class="sr-only">Distance</legend>
                    <div class="flex flex-col gap-1.5">
                        <label
                            v-for="band in props.distanceBands"
                            :key="band"
                            class="flex items-center gap-2 text-sm text-ink"
                        >
                            <input
                                v-model="form.distance"
                                type="radio"
                                name="distance"
                                :value="band"
                                class="accent-chrome"
                            >
                            {{ band }}
                            <span v-if="props.distanceBandNotes[band]" class="text-xs text-ink-muted">{{ props.distanceBandNotes[band] }}</span>
                        </label>
                    </div>
                </fieldset>

                <p v-if="errorOf('distance')" role="alert" class="mt-2 text-sm text-risk">{{ errorOf('distance') }}</p>

                <p class="note mt-2" data-field="distance-conflict">
                    <b>Band edges are Game8's. 1400 m is an open conflict:</b>
                    Game8 places it in {{ props.distanceBands[0] }}, the data export files every 1400 m
                    course under distance code 2, which reads as {{ props.distanceBands[1] }}, and the
                    client's own skills tagged for that band gate on <code>distance_type == 2</code>. This tool
                    states the disagreement rather than picking a side, so a 1400 m race can be typed
                    either way as long as the skill gate it is planned against uses the same reading.
                </p>
            </section>

            <!-- The stamina floor per band, read from the scenario's own stanza rather than restated. -->
            <section data-region="card" class="card" aria-labelledby="distance-floor">
                <h2 id="distance-floor">Stamina floor</h2>

                <p class="sub">What the advisor checks before recommending a Medium or Long race.</p>

                <table v-if="Object.keys(props.finaleTargets.staminaByDistance).length > 0" class="mt-2 w-full text-sm">
                    <thead>
                        <tr class="border-b border-rule text-left text-xs font-semibold uppercase tracking-wider text-ink-muted">
                            <th class="py-2 pr-3">Band</th>
                            <th>Floor</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr v-for="(floor, band) in props.finaleTargets.staminaByDistance" :key="band" class="border-b border-rule">
                            <td class="py-2 pr-3 text-ink">{{ band }}</td>
                            <td class="font-mono text-ink tabular-nums">{{ floor }}</td>
                        </tr>
                    </tbody>
                </table>
                <p v-else class="mt-2 text-sm text-ink-muted">
                    <AbsenceValue compact reason="The chosen scenario publishes no stamina floor per distance, so none is stated." />
                </p>

                <p class="note mt-2">
                    These come from the scenario's own settings, the same entry its caps come from. They are
                    the floor the finale expects, not a target to overshoot.
                </p>
            </section>

            <!-- The wireframe's fourth card: the saved default is a preference the Trainer applies, not this
                 run's target. Its three preset buttons are deliberately not built: `statDefaults` holds one
                 stored spread, and naming presets the tool does not hold would be an invented figure
                 (AGENTS.md §5), so the card carries the one control the payload supports and where to set
                 it. The submit stays outside the card because it is the step's write, not a default. -->
            <section data-region="card" class="card" aria-labelledby="target-saved-default">
                <h2 id="target-saved-default">Target from a saved default</h2>
                <p class="sub">Settings can hold a default spread so a new run does not start blank.</p>

                <div class="mt-3 flex flex-wrap items-center gap-3">
                    <BaseButton type="button" variant="secondary" :disabled="props.statDefaults === null" @click="applyDefaults">
                        Use saved defaults
                    </BaseButton>
                    <Link href="/settings" class="inline-flex min-h-11 items-center text-sm text-ink underline">
                        Set defaults in Settings
                    </Link>
                </div>

                <p class="mt-3 text-xs text-ink-muted">
                    <template v-if="props.statDefaults === null">
                        No saved stat defaults exist, so there is nothing to apply. Settings is where they are set.
                    </template>
                    <template v-else>
                        Saved defaults are your own preference. Applying them replaces the five figures above;
                        they are never applied to a run whose target is already stored.
                    </template>
                </p>
            </section>
            </div>
            </div>

            <!-- The submit moved to the head band's `#actions` (`career-target.html:61-65`), posting this
                 form by id. Only the status line stays here, beside the fields it is saving. -->
            <p v-if="form.processing" role="status" class="text-sm text-ink-muted">Saving your target…</p>
        </form>
    </SetupLayout>
</template>

