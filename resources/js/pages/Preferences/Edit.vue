<script setup lang="ts">
import AppLayout from '../../layouts/AppLayout.vue';
import { BaseButton, BaseCheckbox, BaseInput, BaseRadio, BaseSelect } from '../../components/Form';
import { buttonClasses } from '../../components/Form/buttonStyles';
import { Head, useForm, usePage } from '@inertiajs/vue3';
import { computed, nextTick, onUnmounted, ref } from 'vue';

const props = defineProps<{
    theme: 'light' | 'dark' | 'eva01' | null;
    failureEstimate: 'on' | 'off';
    verifiedAt: string | null;
    importUrl: string;
    settings: {
        default_scenario: string | null;
        recommendation_aggressiveness: string | null;
        stat_target_defaults: Record<string, number> | null;
        language: string | null;
        dashboard_layout: string | null;
        dashboard_hidden_cards: string[] | null;
    };
    scenarioOptions: { value: string; label: string }[];
    aggressivenessOptions: { value: string; label: string }[];
    statOrder: string[];
    hardCap: number;
    exportUrl: string;
    backupUrl: string;
    /** The `dashboard.wide_bento_enabled` flag (DL.14): when false, Wide Bento stays disabled. */
    wideBentoEnabled: boolean;
}>();

const page = usePage();
const rulesetVersion = computed(() => page.props.app?.ruleset ?? null);

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

// The "Show on Dashboard" checkboxes invert the stored `dashboard_hidden_cards`: a card is
// checked when it is NOT in the hidden list. The hidden list is recomputed from `shownCards`
// on submit so the store never receives "show" semantics it does not authorise.
const DASHBOARD_CARDS: ReadonlyArray<{ id: string; label: string }> = [
    { id: 'active_career', label: 'Pick up where you left off' },
    { id: 'quick_actions', label: 'Quick actions' },
    { id: 'recent_veterans', label: 'Recent veterans' },
    { id: 'data_status', label: 'Needs you' },
];

const shownCards = ref<string[]>(
    DASHBOARD_CARDS.filter((card) => !(props.settings.dashboard_hidden_cards ?? []).includes(card.id)).map((card) => card.id),
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

// Reset layout to defaults (DL.6): a two-click inline confirm, no modal. The first click arms the
// button and flips its label to `Reset?`; a second click within five seconds restores `bento` and an
// empty hidden list and submits. The label reverts on its own, so an abandoned arm leaves no state.
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

// The Data panel's Backup is a second action, not a field of the preferences form: it writes a
// server-side snapshot and changes nothing about what the form holds, so it posts on its own.
const backupForm = useForm({});

interface Category {
    id: string;
    label: string;
}

const categories: ReadonlyArray<Category> = [
    { id: 'general', label: 'General' },
    { id: 'recommendation', label: 'Recommendation' },
    { id: 'data', label: 'Data' },
    { id: 'game-version', label: 'Game version' },
];

const tabParam = new URLSearchParams(window.location.search).get('tab');
const activeCategory = ref(
    tabParam !== null && categories.some((category) => category.id === tabParam) ? tabParam : 'general',
);

// The tab lives in the URL so a reload lands on the tab the Trainer was reading (plan 6B.10);
// replaceState changes only the address, keeping Inertia's own history state intact.
function selectCategory(id: string): void {
    activeCategory.value = id;

    const url = new URL(window.location.href);

    if (id === 'general') {
        url.searchParams.delete('tab');
    } else {
        url.searchParams.set('tab', id);
    }

    window.history.replaceState(window.history.state, '', `${url.pathname}${url.search}`);
}

function onTabKeydown(event: KeyboardEvent): void {
    const target = event.target as HTMLElement | null;

    if (target?.getAttribute('role') !== 'tab') {
        return;
    }

    const container = event.currentTarget as HTMLElement;
    const tabs = Array.from(container.querySelectorAll<HTMLElement>('[role="tab"]'));
    const currentIndex = tabs.indexOf(target);
    const count = tabs.length;

    switch (event.key) {
        case 'ArrowLeft':
            event.preventDefault();
            focusTab((currentIndex - 1 + count) % count);
            break;
        case 'ArrowRight':
            event.preventDefault();
            focusTab((currentIndex + 1) % count);
            break;
        case 'Home':
            event.preventDefault();
            focusTab(0);
            break;
        case 'End':
            event.preventDefault();
            focusTab(count - 1);
            break;
    }
}

function focusTab(index: number): void {
    selectCategory(categories[index].id);

    void nextTick(() => {
        const tabs = document.querySelectorAll<HTMLElement>('[role="tab"]');
        tabs[index]?.focus();
    });
}

// Each control carries its own error key, so the lookup is exact: the map-level refusal arrives as
// `settings.stat_target_defaults` and a single stat's bound as `settings.stat_target_defaults.Speed`.
const errorOf = (key: string): string | undefined => form.errors[key];

const controlId = (key: string): string => `field-${key.replace(/\./g, '_')}`;
const errorId = (key: string): string => `error-${key.replace(/\./g, '_')}`;
const describedBy = (key: string): string | undefined => (errorOf(key) === undefined ? undefined : errorId(key));

// The keys that can carry a refusal, in the order they render. Each element exists only while it has
// a message, and `aria-describedby` is set under the same condition, so the reference is never
// dangling. One block below the panels rather than a span inside each label, because an error inside
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

/** The categories a refused key belongs to, so the failing control is in the DOM with its message. */
const errorCategory = (key: string): string =>
    key.startsWith('settings.stat_target_defaults') || key.startsWith('settings.recommendation_aggressiveness')
        ? 'recommendation'
        : 'general';

function submit(): void {
    // Sync the inverted checkbox state back into the stored blob: hide = the cards NOT shown.
    form.settings.dashboard_hidden_cards = DASHBOARD_CARDS
        .filter((card) => !shownCards.value.includes(card.id))
        .map((card) => card.id);

    form.put('/preferences', {
        onError: () => {
            const firstKey = Object.keys(form.errors)[0];

            selectCategory(errorCategory(firstKey));

            // The panel must swap before the control can take focus, and every control's id is
            // `controlId(its error key)`, which is the idiom `BuildTarget.vue:140` already uses. A
            // whole-blob refusal (`settings`, `preferences`) names no control, so nothing is focused
            // and the message block below the panels is the answer.
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
    <AppLayout>
        <Head title="Settings" />
        <template #eyebrow>Preferences</template>
        <template #title>Settings</template>

        <h2 class="text-lg font-semibold text-ink-strong">Preferences</h2>
        <p class="mt-1 text-sm text-ink-muted">
            Settings are stored in this app's database for a single Trainer and read back on the next
            full page load. A preference this tool cannot store is named rather than offered.
        </p>

        <form
            class="mt-6 max-w-3xl space-y-6"
            :aria-busy="form.processing"
            @submit.prevent="submit"
        >
            <div
                role="tablist"
                aria-label="Settings categories"
                class="border-b border-rule"
                @keydown="onTabKeydown"
            >
                <button
                    v-for="category in categories"
                    :key="category.id"
                    :id="`settings-tab-${category.id}`"
                    role="tab"
                    :aria-selected="activeCategory === category.id"
                    :aria-controls="`settings-panel-${category.id}`"
                    :tabindex="activeCategory === category.id ? 0 : -1"
                    @click="selectCategory(category.id)"
                    class="inline-flex min-h-11 items-center border-b-2 px-4 py-2 text-sm font-medium transition-colors duration-150 ease-out"
                    :class="
                        activeCategory === category.id
                            ? 'border-pick-line text-ink-strong'
                            : 'border-transparent text-ink-muted hover:text-ink-strong'
                    "
                >
                    {{ category.label }}
                </button>
            </div>

            <!-- General -->
            <section
                v-if="activeCategory === 'general'"
                id="settings-panel-general"
                role="tabpanel"
                aria-labelledby="settings-tab-general"
                class="space-y-4"
            >
                <p class="text-sm text-ink-muted">Appearance, language and default scenario.</p>

                <!-- The third authorized value is the absence of a row, so "follow the system"
                     writes nothing rather than storing the word `system`. `eva01` is a standalone
                     third palette (owner request 2026-10-24). -->
                <BaseSelect
                    :model-value="form.theme"
                    :id="controlId('theme')"
                    name="theme"
                    label="Theme"
                    :options="[
                        { value: '', label: 'Follow the system' },
                        { value: 'light', label: 'Light' },
                        { value: 'dark', label: 'Dark' },
                        { value: 'eva01', label: 'Evangelion Unit-01' },
                    ]"
                    :aria-describedby="describedBy('theme')"
                    @update:model-value="(v: string | number | null) => (form.theme = String(v ?? ''))"
                />

                <BaseCheckbox
                    :model-value="form.failure_estimate === 'on'"
                    :id="controlId('failure_estimate')"
                    name="failure_estimate"
                    label="Numeric failure estimate"
                    :aria-describedby="describedBy('failure_estimate')"
                    @update:model-value="(v: boolean | string | number) => (form.failure_estimate = v === true ? 'on' : 'off')"
                />

                <p class="text-xs text-ink-muted">
                    Numeric failure estimate is {{ form.failure_estimate === 'on' ? 'on' : 'off' }}.
                    No source publishes a failure curve; the stored choice is kept for a future model.
                </p>

                <!-- One option, disabled, because the corpus is Global-labelled: offering a second
                     language would be a false choice, and an absence would hide the one this build
                     has. The value travels with the form even though the control cannot change it,
                     so what the screen states is what the store holds. The explanation rides as a
                     visible hint, not a `title`: a disabled control does not surface tooltips
                     reliably, and a stated absence should be read, not hovered for. -->
                <BaseSelect
                    :model-value="form.settings.language"
                    :id="controlId('settings.language')"
                    name="settings[language]"
                    label="Language"
                    :options="[{ value: 'en', label: 'English (Global)' }]"
                    disabled
                    hint="This build is Global-labelled only: the corpus holds no other language's strings, so there is nothing to switch to."
                />

                <BaseSelect
                    v-model="form.settings.default_scenario"
                    :id="controlId('settings.default_scenario')"
                    name="settings[default_scenario]"
                    label="Default scenario"
                    :options="[{ value: '', label: 'No default' }, ...scenarioOptions.map(o => ({ value: o.value, label: o.label }))]"
                    :aria-describedby="describedBy('settings.default_scenario')"
                />
                <!-- Outside the label: a label's text becomes the control's accessible name, and a
                     hint is not part of that name. The errors render in one block below the panels. -->
                <p class="-mt-2 text-xs text-ink-muted">
                    Pre-selects the scenario on the new-career screen, until a career is chosen.
                </p>

                <!-- Layout width (PLAN 6B.14): three radios, one per shell measure. The labels
                     use 'Compact / Standard / Wide' (the shell's three widths in grow order);
                     a third card on this screen, "Show on Dashboard", was the form's choice but
                     is no longer the only thing the radios control. -->
                <fieldset class="space-y-2">
                    <legend class="text-lg font-semibold text-ink-strong">Layout width</legend>
                    <p class="text-xs text-ink-muted">
                        How much horizontal space pages use. Compact is best for forms; Wide is best for tables and grids.
                    </p>

                    <BaseRadio
                        v-model="form.settings.dashboard_layout"
                        :id="controlId('settings.dashboard_layout')"
                        name="settings[dashboard_layout]"
                        label="Standard"
                        value="standard"
                        hint="Bento composition. Grouped cards at the default shell width (1152px)."
                        :aria-describedby="describedBy('settings.dashboard_layout')"
                    />

                    <BaseRadio
                        v-model="form.settings.dashboard_layout"
                        :id="controlId('settings.dashboard_layout_wide')"
                        name="settings[dashboard_layout]"
                        label="Wide"
                        value="wide"
                        hint="Wide bento composition. The same grouping at the wider cap (1536px)."
                    />

                    <BaseRadio
                        v-model="form.settings.dashboard_layout"
                        :id="controlId('settings.dashboard_layout_focused')"
                        name="settings[dashboard_layout]"
                        label="Compact"
                        value="compact"
                        hint="Focused composition. A single reading column, best for forms and long copy."
                        :aria-describedby="describedBy('settings.dashboard_layout')"
                    />
                </fieldset>

                <!-- Show on Dashboard (PLAN 6B.14): the stored value is `dashboard_hidden_cards`
                     (the cards to hide), so the checkboxes invert it: checked = shown, unchecked = hidden.
                     The submit handler recomputes the hidden list from the shown set. -->
                <fieldset class="space-y-2">
                    <legend class="text-lg font-semibold text-ink-strong">Show on Dashboard</legend>
                    <p class="text-xs text-ink-muted">
                        Toggle which dashboard cards appear when you open the Dashboard.
                    </p>

                    <BaseCheckbox
                        v-for="card in DASHBOARD_CARDS"
                        :key="card.id"
                        :model-value="isCardShown(card.id)"
                        :id="`dashboard_card_${card.id}`"
                        :name="`settings[dashboard_hidden_cards][${card.id}]`"
                        :label="card.label"
                        :aria-describedby="describedBy('settings.dashboard_hidden_cards')"
                        @update:model-value="(v: boolean | string | number) => toggleCard(card.id, v === true)"
                    />
                </fieldset>

                <!-- Reset layout to defaults (DL.6): secondary, below the checkbox group, inline
                     two-click confirm. Restores `bento` with every card shown and submits. -->
                <div class="flex flex-wrap items-center gap-3">
                    <BaseButton type="button" variant="secondary" @click="armReset">
                        {{ resetArmed ? 'Reset?' : 'Reset layout to defaults' }}
                    </BaseButton>
                    <p v-if="resetStatus" role="status" class="sr-only">{{ resetStatus }}</p>
                </div>

                <dl class="border-t border-rule pt-3 space-y-2">
                    <div class="flex justify-between">
                        <dt class="text-lg font-semibold text-ink-strong">Units</dt>
                        <dd
                            class="text-ink-muted"
                            title="Sourcing absence: the only unit this tool prints is race distance, and it prints metres because the client does. No source in the corpus publishes an imperial rendering, so there is nothing to convert."
                        >
                            N/A
                        </dd>
                    </div>
                </dl>
            </section>

            <!-- Recommendation -->
            <section
                v-else-if="activeCategory === 'recommendation'"
                id="settings-panel-recommendation"
                role="tabpanel"
                aria-labelledby="settings-tab-recommendation"
                class="space-y-4"
            >
                <p class="text-sm text-ink-muted">Advisor defaults and stat-target pre-fills.</p>

                <BaseSelect
                    v-model="form.settings.recommendation_aggressiveness"
                    :id="controlId('settings.recommendation_aggressiveness')"
                    name="settings[recommendation_aggressiveness]"
                    label="Recommendation aggressiveness"
                    :options="[{ value: '', label: 'Not set' }, ...aggressivenessOptions.map(o => ({ value: o.value, label: o.label }))]"
                    :aria-describedby="describedBy('settings.recommendation_aggressiveness')"
                    title="Stored now; the advisor reads it in a follow-up slice."
                />

                <fieldset class="space-y-2">
                    <legend class="text-lg font-semibold text-ink-strong">Stat-target defaults</legend>
                    <p class="text-xs text-ink-muted">
                        Pre-fills the Build Target screen's five stat inputs when no target has been
                        entered. The engine ceiling is {{ hardCap }}, the number no stat may pass
                        whatever the scenario's own bonus says.
                    </p>
                    <template v-for="stat in statOrder" :key="stat">
                        <BaseInput
                            v-model="form.settings.stat_target_defaults[stat]"
                            type="number"
                            :name="`settings[stat_target_defaults][${stat}]`"
                            :id="controlId(`settings.stat_target_defaults.${stat}`)"
                            :label="stat"
                            :hint="`0-${hardCap}`"
                            :error="form.errors[`settings.stat_target_defaults.${stat}`]"
                        />
                    </template>
                </fieldset>

                <dl class="border-t border-rule pt-3 space-y-2">
                    <div class="flex justify-between">
                        <dt class="text-lg font-semibold text-ink-strong">Risk tolerance</dt>
                        <dd
                            class="text-ink-muted"
                            title="Whether a risk tolerance enters the stored payload is the Build Target screen's own open question, not this one's to settle."
                        >
                            N/A
                        </dd>
                    </div>
                </dl>
            </section>

            <!-- Data -->
            <section
                v-else-if="activeCategory === 'data'"
                id="settings-panel-data"
                role="tabpanel"
                aria-labelledby="settings-tab-data"
                class="space-y-4"
            >
                <p class="text-sm text-ink-muted">Import, export and backup.</p>

                <dl class="border-t border-rule pt-3 space-y-2">
                    <div class="flex justify-between">
                        <dt class="text-lg font-semibold text-ink-strong">Import</dt>
                        <dd>
                            <a
                                :href="importUrl"
                                :class="buttonClasses('secondary')"
                                >Open import</a
                            >
                        </dd>
                    </div>
                    <div class="flex justify-between">
                        <dt class="text-lg font-semibold text-ink-strong">Export</dt>
                        <dd>
                            <a
                                :href="exportUrl"
                                :class="buttonClasses('secondary')"
                            >Download export</a>
                        </dd>
                    </div>
                </dl>

                <p class="text-sm text-ink">
                    Backup is a server-side action: it writes a snapshot of this app's database file
                    under <span class="font-mono text-xs">storage/app/backups/</span> and reports the
                    path in the message above. Nothing is downloaded, and restoring one is a
                    deliberate owner action rather than a screen button.
                </p>
                <BaseButton
                    type="button"
                    variant="secondary"
                    :disabled="backupForm.processing"
                    @click="runBackup"
                >
                    {{ backupForm.processing ? 'Backing up…' : 'Back up now' }}
                </BaseButton>

                <dl class="border-t border-rule pt-3 space-y-2">
                    <div class="flex justify-between">
                        <dt class="text-lg font-semibold text-ink-strong">Restore</dt>
                        <dd
                            class="text-ink-muted"
                            title="Destructive: restoring replaces the database file, which is an owner's action to take. Absent rather than a button without a backend."
                        >
                            N/A
                        </dd>
                    </div>
                    <div class="flex justify-between">
                        <dt class="text-lg font-semibold text-ink-strong">Reset</dt>
                        <dd
                            class="text-ink-muted"
                            title="Destructive; adding it needs the owner's approval"
                        >
                            N/A
                        </dd>
                    </div>
                </dl>
            </section>

            <!-- Game version: ruleset is N/A (no source), verified date from config -->
            <section
                v-else
                id="settings-panel-game-version"
                role="tabpanel"
                aria-labelledby="settings-tab-game-version"
                class="space-y-4"
            >
                <p class="text-sm text-ink-muted">Ruleset version and last-verified date.</p>

                <dl class="border-t border-rule pt-3 space-y-2">
                    <div class="flex justify-between">
                        <dt class="text-lg font-semibold text-ink-strong">Global Ruleset Version</dt>
                        <dd
                            :class="
                                rulesetVersion
                                    ? 'text-ink'
                                    : 'text-ink-muted'
                            "
                            :title="
                                rulesetVersion
                                    ? undefined
                                    : 'No source defines a Global ruleset version'
                            "
                        >
                            {{ rulesetVersion ?? 'N/A' }}
                        </dd>
                    </div>
                    <div class="flex justify-between">
                        <dt class="text-lg font-semibold text-ink-strong">Verified</dt>
                        <dd
                            class="text-ink"
                            :title="verifiedAt ? undefined : 'No verification date is recorded for this build.'"
                        >
                            {{ verifiedAt ?? 'N/A' }}
                        </dd>
                    </div>
                </dl>
            </section>

            <p
                v-for="key in visibleErrors"
                :key="key"
                :id="`error-block-${key}`"
                class="text-risk"
            >
                {{ errorOf(key) }}
            </p>

            <!-- The one accented primary on the screen (plan 6B.12); the tab strip's gold underline
                 is the selection state, not a second action accent. Rendered through BaseButton so
                 the browser gate can read `data-variant="primary"` off the committed element. -->
            <BaseButton type="submit" variant="primary" :disabled="form.processing">
                {{ form.processing ? 'Saving…' : 'Save preferences' }}
            </BaseButton>
            <p class="text-xs text-ink-muted">Saves every tab in one write.</p>
        </form>
    </AppLayout>
</template>

