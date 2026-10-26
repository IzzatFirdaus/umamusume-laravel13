<script setup lang="ts">
import AppLayout from '../../layouts/AppLayout.vue';
import { BaseButton, BaseCheckbox, BaseInput, BaseRadio, BaseSelect } from '../../components/Form';
import { buttonClasses } from '../../components/Form/buttonStyles';
import PageHeader from '../../components/ui/PageHeader.vue';
import Note from '../../components/ui/Note.vue';
import { Head, useForm } from '@inertiajs/vue3';
import { computed, nextTick, onUnmounted, ref } from 'vue';

/*
 * The Preferences screen, ported from the wireframe
 * (`docs/design-research/frontend-v2-wireframes/preferences.html`): one page head carrying one save
 * verb, then the HFW's `.grid g-side` — Layout width / Target defaults / Data on the wide side,
 * Theme / Language / Dashboard on the narrow one.
 *
 * The wireframe is the authority on shape, and the app is the authority on state. Where the two
 * disagree, the difference is recorded here and at the point it is made, rather than papered over:
 *
 *   - The wireframe's Theme row is three options. This build stores a fourth resolved palette
 *     (`eva01`, owner request 2026-10-24), and a stored value a Trainer may already have chosen
 *     cannot be dropped from the only control that sets it, so the fourth option renders too.
 *   - The wireframe's Language select offers Japanese. This build's corpus is Global-labelled only
 *     and the writer refuses a second language, so the control carries the one option that exists
 *     and says why in its own hint.
 *   - The wireframe's Dashboard table is a picture of four "Yes" pills. This build stores which
 *     dashboard regions are hidden, so the table keeps the wireframe's shape and its "Shown" column
 *     carries the live control.
 *   - The wireframe's Target defaults grid ends with a "Distance" select. This build's sixth cell
 *     is the default scenario, which is the preference that key actually stores.
 *
 * The wireframe's `.keybox` (the annotation key) is a document artifact and is not rendered, like
 * the `.proto` chip and the dashboard's table of contents in the other files of the set.
 */
const props = defineProps<{
    theme: 'light' | 'dark' | 'eva01' | null;
    failureEstimate: 'on' | 'off';
    settings: {
        default_scenario: string | null;
        recommendation_aggressiveness: string | null;
        /** The matrix's five stat names, each with its stored default or null. */
        stat_target_defaults: Record<string, number | null>;
        language: string | null;
        dashboard_layout: string | null;
        dashboard_hidden_cards: string[] | null;
    };
    scenarioOptions: { value: string; label: string }[];
    statOrder: string[];
    hardCap: number;
    exportUrl: string;
    backupUrl: string;
    /** The `dashboard.wide_bento_enabled` flag (DL.14): when false, Wide Bento stays disabled. */
    wideBentoEnabled: boolean;
}>();

const form = useForm({
    theme: props.theme ?? '',
    failure_estimate: props.failureEstimate,
    settings: {
        default_scenario: props.settings.default_scenario ?? '',
        recommendation_aggressiveness: props.settings.recommendation_aggressiveness ?? '',
        // An unset stat is an empty field, never a pre-filled zero: a zero is a number the
        // Trainer did not enter, and the blob stores only what was actually set.
        stat_target_defaults: Object.fromEntries(
            props.statOrder.map((stat) => [stat, props.settings.stat_target_defaults?.[stat] ?? '']),
        ),
        language: props.settings.language ?? 'en',
        // The form seeds from whatever the read map normalised: canonical values pass through, and
        // an unset row falls back to `standard`, the default the radios now carry.
        dashboard_layout: props.settings.dashboard_layout ?? 'standard',
        dashboard_hidden_cards: props.settings.dashboard_hidden_cards ?? [],
    },
});

/*
 * preferences.html:78-98. Each width states its own number, because "Compact / Standard / Wide"
 * means nothing without 768 / 1152 / 1536 (annotation 2).
 */
const LAYOUT_OPTIONS: ReadonlyArray<{ value: string; label: string; hint: string }> = [
    {
        value: 'standard',
        label: 'Standard',
        hint: 'Bento composition. Grouped cards at the default shell width (1152px).',
    },
    {
        value: 'wide',
        label: 'Wide',
        hint: 'Wide bento composition. The same grouping at the wider cap (1536px).',
    },
    {
        value: 'compact',
        label: 'Compact',
        hint: 'Focused composition. A single reading column, best for forms and long copy.',
    },
];

/*
 * preferences.html:137-159. A choice of options, not a two-way switch: the empty value is the
 * absence of a stored row, so "Follow the system" writes nothing. `eva01` is a standalone fourth
 * palette this build stores.
 */
const THEME_OPTIONS: ReadonlyArray<{ value: string; label: string; hint: string }> = [
    { value: '', label: 'Follow the system', hint: 'OS decides. Nothing stored.' },
    { value: 'light', label: 'Light', hint: 'The default palette.' },
    { value: 'dark', label: 'Dark', hint: 'The measured dark ramp.' },
    { value: 'eva01', label: 'Evangelion Unit-01', hint: 'The measured third palette.' },
];

/*
 * The default scenario is one of the scenarios the matrix composes, named by the matrix's own
 * labels (D-240): the writer refuses a key the config does not carry, so the control cannot offer
 * a scenario that does not exist. "Not set" is the empty state — the card's own subtitle says an
 * empty default leaves the step blank.
 */
const SCENARIO_OPTIONS = computed<{ value: string; label: string }[]>(() => [
    { value: '', label: 'Not set' },
    ...props.scenarioOptions,
]);

/*
 * The dashboard regions a Trainer can switch off, in the wireframe's reading order
 * (preferences.html:180-183).
 *
 * `reads` are the stored ids that hide the region; `id` is the id the writer accepts for it. The
 * two differ once: the Dashboard honours a legacy `roster` id for the column it calls "Needs you",
 * and the writer refuses that id, so the row reads the state of either id and writes the one the
 * writer takes. Offering `roster` itself would be a control that cannot be saved.
 */
const DASHBOARD_CARDS: ReadonlyArray<{ id: string; label: string; reads: string[] }> = [
    { id: 'active_career', label: 'Active career', reads: ['active_career'] },
    { id: 'data_status', label: 'Needs you', reads: ['data_status', 'roster'] },
    { id: 'recent_veterans', label: 'Recent veterans', reads: ['recent_veterans'] },
    { id: 'quick_actions', label: 'Quick actions', reads: ['quick_actions'] },
];

const hiddenCards = props.settings.dashboard_hidden_cards ?? [];

/*
 * The stored value is `dashboard_hidden_cards` (the regions to hide), so the controls invert it:
 * checked = shown. The hidden list is recomputed from `shownCards` on submit, so the store never
 * receives "show" semantics it does not authorise.
 */
const shownCards = ref<string[]>(
    DASHBOARD_CARDS.filter((card) => !card.reads.some((id) => hiddenCards.includes(id))).map((card) => card.id),
);

function isCardShown(cardId: string): boolean {
    return shownCards.value.includes(cardId);
}

function toggleCard(cardId: string, checked: boolean): void {
    if (checked) {
        if (!shownCards.value.includes(cardId)) {
            shownCards.value.push(cardId);
        }
    } else {
        shownCards.value = shownCards.value.filter((id) => id !== cardId);
    }
}

/*
 * Reset layout to defaults: a two-click inline confirm, no modal. The first click arms the
 * button and flips its label to `Reset?`; a second click within five seconds restores the
 * standard width and every card and submits. The label reverts on its own, so an abandoned arm
 * leaves no state.
 */
const resetArmed = ref(false);
const resetStatus = ref('');
let resetTimer: ReturnType<typeof setTimeout> | null = null;

function armReset(): void {
    if (resetArmed.value) {
        commitReset();

        return;
    }

    resetArmed.value = true;
    resetTimer = setTimeout(() => {
        resetArmed.value = false;
        resetTimer = null;
    }, 5000);
}

function commitReset(): void {
    if (resetTimer !== null) {
        clearTimeout(resetTimer);
        resetTimer = null;
    }

    resetArmed.value = false;
    form.settings.dashboard_layout = 'standard';
    shownCards.value = DASHBOARD_CARDS.map((card) => card.id);
    resetStatus.value = 'Layout width reset to defaults.';
    submit();
}

onUnmounted(() => {
    if (resetTimer !== null) {
        clearTimeout(resetTimer);
    }
});

/*
 * The Data card's Backup is a second action, not a field of the preferences form: it writes a
 * server-side snapshot and changes nothing about what the form holds, so it posts on its own.
 */
const backupForm = useForm({});

// Each control carries its own error key, so the lookup is exact: the map-level refusal arrives as
// `settings.stat_target_defaults` and a single stat's bound as `settings.stat_target_defaults.Speed`.
const errorOf = (key: string): string | undefined => form.errors[key];

const controlId = (key: string): string => `field-${key.replace(/\./g, '_')}`;
const errorId = (key: string): string => `error-${key.replace(/\./g, '_')}`;
const describedBy = (key: string): string | undefined => (errorOf(key) === undefined ? undefined : errorId(key));

// The keys that can carry a refusal, in the order they render. Each element exists only while it has
// a message, and `aria-describedby` is set under the same condition, so the reference is never
// dangling. One block below the cards rather than a span inside each label, because an error inside
// a label becomes part of the control's accessible name and a refusal is not the control's identity.
const errorKeys: ReadonlyArray<string> = [
    'theme',
    'failure_estimate',
    'settings.default_scenario',
    'settings.recommendation_aggressiveness',
    'settings.stat_target_defaults',
    ...props.statOrder.map((stat) => `settings.stat_target_defaults.${stat}`),
    'settings.dashboard_layout',
    'settings.dashboard_hidden_cards',
    'settings',
    'preferences',
];

const visibleErrors = computed<string[]>(() => errorKeys.filter((key) => errorOf(key) !== undefined));

/*
 * The clean/dirty state drives the savebar: the wireframe disables the control while clean and
 * prints "All changes saved" (preferences.html:62-63). Inertia's own `form.isDirty` does not see
 * writes made through the nested `settings` proxy, and `shownCards` is a separate ref, so the
 * screen compares a snapshot of everything it will submit against the seeded one instead. The
 * snapshot reads the nested proxy through `JSON.stringify`, which walks every leaf, so a write to
 * one stat target is a write the comparison sees.
 */
const snapshot = () =>
    JSON.stringify({
        theme: form.theme,
        failure_estimate: form.failure_estimate,
        settings: form.settings,
        shown: shownCards.value,
    });

// Re-baselined after every successful save: the screen is clean again the moment the store holds
// what the form shows, so the comparison is against the last committed state, not the page load.
const pristine = ref<string>(snapshot());
const isDirty = computed<boolean>(() => snapshot() !== pristine.value);

function submit(): void {
    // Sync the inverted checkbox state back into the stored blob: hide = the regions NOT shown.
    form.settings.dashboard_hidden_cards = DASHBOARD_CARDS
        .filter((card) => !shownCards.value.includes(card.id))
        .map((card) => card.id);

    form.put('/preferences', {
        onSuccess: () => {
            pristine.value = snapshot();
        },
        onError: () => {
            const firstKey = Object.keys(form.errors)[0];

            // The control has to be in the DOM before it can take focus, and every control's id is
            // `controlId(its error key)`, the idiom `BuildTarget.vue:140` already uses. A whole-blob
            // refusal (`settings`, `preferences`) names no control, so nothing is focused and the
            // message block below the cards is the answer.
            void nextTick(() => {
                document.getElementById(controlId(firstKey))?.focus();
            });
        },
    });
}

function runBackup(): void {
    backupForm.post(props.backupUrl, { preserveScroll: true });
}
</script>

<template>
    <AppLayout :breadcrumb="[{ label: 'Settings' }]">
        <Head title="Preferences" />

        <!-- preferences.html:54-67: the page head carries the state line and the one save verb,
             so the screen reads as one form with one commit rather than a per-panel save. -->
        <template #head>
            <PageHeader
                eyebrow="Settings"
                title="Preferences"
                description="Layout, language, theme and the target defaults a new career starts from. Stored in the database, not the browser."
            >
                <template #actions>
                    <span role="status" class="text-xs font-semibold text-ink-muted">
                        {{ form.processing ? 'Saving' : isDirty ? 'Unsaved changes' : 'All changes saved' }}
                    </span>
                    <BaseButton type="submit" variant="primary" :disabled="form.processing || !isDirty" form="preferences-form">
                        {{ form.processing ? 'Saving' : 'Save changes' }}
                    </BaseButton>
                </template>
            </PageHeader>
        </template>

        <!-- HFW `.grid g-side` (shell.css:404): 2.1fr / 1fr, collapsing to one column at 820px. This
             is the window's width, so the breakpoints are media queries, not container queries —
             a container query resolves against the shell's content box and would lag the window. -->
        <form
            id="preferences-form"
            class="grid grid-cols-1 gap-3.5 min-[821px]:grid-cols-[minmax(0,2.1fr)_minmax(0,1fr)] [&>*]:m-0"
            :aria-busy="form.processing"
            @submit.prevent="submit"
        >
            <!-- LEFT COLUMN (preferences.html:73-129) -->
            <div class="flex flex-col gap-3.5 [&>*]:m-0">
                <section class="card" aria-labelledby="pref-layout-heading">
                    <h2 id="pref-layout-heading">Layout width</h2>
                    <p class="sub">
                        How much horizontal space pages use. Compact is best for forms; Wide is best for
                        tables and grids.
                    </p>

                    <!-- The three options are cards, not bare radios: the wireframe states each
                         width's number, because a Trainer choosing Wide on a laptop needs to know
                         it collapses (annotation 2). -->
                    <div class="flex flex-col gap-2.5" role="radiogroup" aria-labelledby="pref-layout-heading">
                        <BaseRadio
                            v-for="option in LAYOUT_OPTIONS"
                            :key="option.value"
                            v-model="form.settings.dashboard_layout"
                            :id="controlId(`settings.dashboard_layout_${option.value}`)"
                            name="settings[dashboard_layout]"
                            card
                            :label="option.label"
                            :value="option.value"
                            :hint="option.hint"
                            :disabled="option.value === 'wide' && !props.wideBentoEnabled"
                            :aria-describedby="describedBy('settings.dashboard_layout')"
                        />
                    </div>

                    <Note class="mt-2.5">
                        The widths are the ratified ones: 768 / 1152 / 1536. Below 1280px the wide shell
                        collapses to the standard breakpoints, so choosing Wide on a laptop changes
                        nothing.
                    </Note>
                </section>

                <section class="card" aria-labelledby="pref-targets-heading">
                    <h2 id="pref-targets-heading">Target defaults</h2>
                    <p class="sub">
                        What a new career's build-target step starts from. Empty means the step starts
                        blank.
                    </p>
                    <!-- HFW `.grid g-3` (shell.css:401,405-406): three columns that go to two below
                         1080px and to one below 820px, measured against the window. The sixth cell is
                         the default scenario, which is the preference the sixth cell stores. -->
                    <div class="grid grid-cols-1 gap-2.5 min-[821px]:grid-cols-2 min-[1081px]:grid-cols-3">
                        <!-- Each field is wrapped because `BaseInput` renders its label and its hint as
                             two roots: unwrapped, the hint becomes a cell of this grid on its own and
                             the field grid stops being the wireframe's three columns.

                             The field states the range in its hint rather than in a `max` attribute: the
                             engine ceiling is the server's rule (`UpdatePreferenceRequest`), and a
                             browser-side bound would refuse the value silently and never let the
                             screen show the refusal it is written to show. -->
                        <div v-for="stat in props.statOrder" :key="stat">
                            <BaseInput
                                v-model="form.settings.stat_target_defaults[stat]"
                                type="number"
                                :name="`settings[stat_target_defaults][${stat}]`"
                                :id="controlId(`settings.stat_target_defaults.${stat}`)"
                                :label="stat"
                                :hint="`0-${props.hardCap}`"
                                inputmode="numeric"
                                :error="form.errors[`settings.stat_target_defaults.${stat}`]"
                            />
                        </div>
                        <BaseSelect
                            :model-value="form.settings.default_scenario"
                            :id="controlId('settings.default_scenario')"
                            name="settings[default_scenario]"
                            label="Default scenario"
                            :options="SCENARIO_OPTIONS"
                            :aria-describedby="describedBy('settings.default_scenario')"
                            :error="form.errors['settings.default_scenario']"
                            @update:model-value="(v: string | number | null) => (form.settings.default_scenario = String(v ?? ''))"
                        />
                    </div>
                </section>

                <section class="card" aria-labelledby="pref-data-heading">
                    <h2 id="pref-data-heading">Data</h2>
                    <div class="flex flex-col gap-2">
                        <a :href="props.exportUrl" :class="buttonClasses('secondary')">Export everything as JSON</a>
                        <BaseButton type="button" variant="secondary" :disabled="backupForm.processing" @click="runBackup">
                            {{ backupForm.processing ? 'Backing up' : 'Download a backup' }}
                        </BaseButton>
                    </div>
                    <p class="mt-2.5 text-xs text-ink-muted">
                        Export is a file download. A backup is a complete copy you can restore from.
                    </p>
                </section>
            </div>

            <!-- RIGHT COLUMN (preferences.html:131-189) -->
            <div class="flex flex-col gap-3.5 [&>*]:m-0">
                <section class="card" aria-labelledby="pref-theme-heading">
                    <h2 id="pref-theme-heading">Theme</h2>
                    <p class="sub">
                        Applied to every screen. &ldquo;Follow the system&rdquo; stores nothing, so the OS
                        preference decides.
                    </p>

                    <!-- A choice of options, not a two-way switch: the empty value is the absence of
                         a stored row, so "follow the system" writes nothing rather than storing the
                         word `system`. `eva01` is a standalone third palette.

                         The tiles are stacked one per row, full width, as the wireframe draws them:
                         its markup asks for three columns through a `.grid2` class its own CSS never
                         defines, so the rendered wireframe puts one tile on each row — and the
                         narrow column is too small for three side by side to stay readable. -->
                    <div class="flex flex-col gap-2.5" role="radiogroup" aria-labelledby="pref-theme-heading">
                        <BaseRadio
                            v-for="option in THEME_OPTIONS"
                            :key="option.value"
                            :model-value="form.theme"
                            :id="controlId(`theme_${option.value || 'system'}`)"
                            name="theme"
                            card
                            :label="option.label"
                            :value="option.value"
                            :hint="option.hint"
                            @update:model-value="(v: string | number | null) => (form.theme = String(v ?? ''))"
                        />
                    </div>

                    <Note class="mt-2.5">
                        This is the app&rsquo;s only theme control &mdash; a four-option choice, not a
                        two-way switch. The options are shown as radios so they read side by side.
                        There is no per-page switching anywhere else in the product.
                    </Note>

                    <!-- A separate stored preference the wireframe predates (Preference::KEYS holds
                         `failure_estimate` alongside `theme`). Dropping it would strand the only
                         control that sets it, so it renders here rather than being lost. -->
                    <div class="mt-3 border-t border-rule-soft pt-3">
                        <BaseCheckbox
                            :model-value="form.failure_estimate === 'on'"
                            :id="controlId('failure_estimate')"
                            name="failure_estimate"
                            label="Numeric failure estimate"
                            :aria-describedby="describedBy('failure_estimate')"
                            @update:model-value="(v: boolean | string | number) => (form.failure_estimate = v === true ? 'on' : 'off')"
                        />
                        <p class="mt-1 text-xs text-ink-muted">
                            Numeric failure estimate is {{ form.failure_estimate === 'on' ? 'on' : 'off' }}.
                            No source publishes a failure curve; the stored choice is kept for a future model.
                        </p>
                    </div>
                </section>

                <section class="card" aria-labelledby="pref-language-heading">
                    <h2 id="pref-language-heading">Language</h2>
                    <BaseSelect
                        :model-value="form.settings.language"
                        :id="controlId('settings.language')"
                        name="settings[language]"
                        label="Interface language"
                        :options="[{ value: 'en', label: 'English' }]"
                        disabled
                        hint="This build is Global-labelled only: the corpus holds no other language's strings, so there is nothing to switch to."
                    />
                    <p class="mt-2.5 text-xs text-ink-muted">
                        Game vocabulary is the client's own English either way; the language setting
                        changes the interface, not the game terms.
                    </p>
                </section>

                <section class="card" aria-labelledby="pref-dashboard-heading">
                    <h2 id="pref-dashboard-heading">Dashboard</h2>

                    <!-- The stored value is `dashboard_hidden_cards` (the regions to hide), so the
                         toggles invert it: checked = shown. The submit handler recomputes the hidden
                         list from the shown set. -->
                    <table class="w-full border-collapse text-[12.5px]">
                        <thead>
                            <tr>
                                <th class="border-b border-rule-soft px-2 pb-1.5 text-left text-[10px] font-extrabold uppercase tracking-[0.06em] text-ink-muted">Card</th>
                                <th class="border-b border-rule-soft px-2 pb-1.5 text-right text-[10px] font-extrabold uppercase tracking-[0.06em] text-ink-muted">Shown</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr v-for="card in DASHBOARD_CARDS" :key="card.id">
                                <td class="border-b border-rule-soft px-2 py-1.5">{{ card.label }}</td>
                                <td class="border-b border-rule-soft px-2 py-1.5 text-right">
                                    <BaseCheckbox
                                        :model-value="isCardShown(card.id)"
                                        :id="`dashboard_card_${card.id}`"
                                        :name="`settings[dashboard_hidden_cards][${card.id}]`"
                                        :label="card.label"
                                        :sr-only-label="true"
                                        :aria-describedby="describedBy('settings.dashboard_hidden_cards')"
                                        @update:model-value="(v: boolean | string | number) => toggleCard(card.id, v === true)"
                                    />
                                </td>
                            </tr>
                        </tbody>
                    </table>

                    <p class="mt-2.5 text-xs text-ink-muted">Hidden cards are excluded, not collapsed.</p>

                    <div class="mt-2.5 flex flex-wrap items-center gap-3">
                        <BaseButton type="button" variant="secondary" @click="armReset">
                            {{ resetArmed ? 'Reset?' : 'Reset layout to defaults' }}
                        </BaseButton>
                        <p v-if="resetStatus" role="status" class="sr-only">{{ resetStatus }}</p>
                    </div>
                </section>
            </div>

            <p
                v-for="key in visibleErrors"
                :key="key"
                :id="`error-block-${key}`"
                class="text-risk"
            >
                {{ errorOf(key) }}
            </p>
        </form>
    </AppLayout>
</template>
