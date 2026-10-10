<script setup lang="ts">
import AppLayout from '../../layouts/AppLayout.vue';
import { Head, useForm, usePage } from '@inertiajs/vue3';
import { computed, nextTick, ref } from 'vue';

const props = defineProps<{
    theme: 'light' | 'dark' | null;
    failureEstimate: 'on' | 'off';
    verifiedAt: string | null;
    importUrl: string;
    settings: {
        default_scenario: string | null;
        recommendation_aggressiveness: string | null;
        stat_target_defaults: Record<string, number> | null;
        language: string | null;
    };
    scenarioOptions: { value: string; label: string }[];
    aggressivenessOptions: { value: string; label: string }[];
    statOrder: string[];
    hardCap: number;
    exportUrl: string;
    backupUrl: string;
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
    },
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

const activeCategory = ref('general');

function onEstimateToggle(event: Event): void {
    form.failure_estimate = (event.target as HTMLInputElement).checked ? 'on' : 'off';
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
    activeCategory.value = categories[index].id;

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
    form.put('/preferences', {
        onError: () => {
            const firstKey = Object.keys(form.errors)[0];

            activeCategory.value = errorCategory(firstKey);

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
        <template #title>Settings</template>

        <h2 class="text-2xl font-semibold text-ink-strong">Preferences</h2>
        <p class="mt-1 text-sm text-ink-muted">
            Settings are stored in this app's database for a single Trainer and read back on the next
            request (PRD US-11, PRD §6 non-goal 12). A preference this tool cannot store is named
            rather than offered.
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
                    @click="activeCategory = category.id"
                    class="inline-flex min-h-11 items-center border-b-2 px-4 py-2 text-sm font-medium focus:outline-none"
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
                <label class="block">
                    <span class="font-medium text-ink">Theme</span>
                    <select
                        v-model="form.theme"
                        name="theme"
                        :id="controlId('theme')"
                        :aria-describedby="describedBy('theme')"
                        class="mt-1 block w-full min-h-11 rounded-md border border-rule bg-raised px-2 text-ink"
                    >
                        <!-- The third authorized value is the absence of a row, so "follow the system"
                             writes nothing rather than storing the word `system`. -->
                        <option value="">Follow the system</option>
                        <option value="light">Light</option>
                        <option value="dark">Dark</option>
                    </select>
                </label>

                <label class="inline-flex min-h-11 items-center gap-2">
                    <input
                        type="checkbox"
                        name="failure_estimate"
                        :id="controlId('failure_estimate')"
                        class="h-5 w-5 accent-chrome"
                        :aria-describedby="describedBy('failure_estimate')"
                        :checked="form.failure_estimate === 'on'"
                        @change="onEstimateToggle"
                    >
                    <span class="font-medium text-ink">Numeric failure estimate</span>
                </label>

                <p class="text-xs text-ink-muted">
                    Turning the estimate on changes no screen yet. `ADR-0001` §3 records that
                    no source publishes a failure curve and requires any number this tool prints
                    to show its formula beside it, so there is nothing honest to render until a
                    model exists. The choice is stored, and the band the app can source from
                    Energy (Safe, Caution, Danger) shows either way.
                </p>

                <label class="block">
                    <span class="font-medium text-ink">Language</span>
                    <!-- One option, disabled, because the corpus is Global-labelled: offering a second
                         language would be a false choice, and an absence would hide the one this build
                         has. The value travels with the form even though the control cannot change it,
                         so what the screen states is what the store holds. -->
                    <select
                        v-model="form.settings.language"
                        name="settings[language]"
                        :id="controlId('settings.language')"
                        disabled
                        title="This build is Global-labelled only: the corpus holds no other language's strings, so there is nothing to switch to."
                        class="mt-1 block w-full min-h-11 rounded-md border border-rule bg-sunken px-2 text-ink-muted"
                    >
                        <option value="en">English (Global)</option>
                    </select>
                </label>

                <label class="block">
                    <span class="font-medium text-ink">Default scenario</span>
                    <!-- One option per entry the matrix composes, read as a whole: no branch on any
                         of them (D-240, gate G-33). -->
                    <select
                        v-model="form.settings.default_scenario"
                        name="settings[default_scenario]"
                        :id="controlId('settings.default_scenario')"
                        :aria-describedby="describedBy('settings.default_scenario')"
                        class="mt-1 block w-full min-h-11 rounded-md border border-rule bg-raised px-2 text-ink"
                    >
                        <option value="">No default</option>
                        <option v-for="option in scenarioOptions" :key="option.value" :value="option.value">
                            {{ option.label }}
                        </option>
                    </select>
                </label>
                <!-- Outside the label: a label's text becomes the control's accessible name, and a
                     hint is not part of that name. The errors render in one block below the panels. -->
                <p class="-mt-2 text-xs text-ink-muted">
                    Pre-selects the scenario on the new-career screen, until a career is chosen.
                </p>

                <dl class="border-t border-rule pt-3 space-y-2">
                    <div class="flex justify-between">
                        <dt class="text-ink">Units</dt>
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
                <label class="block">
                    <span class="font-medium text-ink">Recommendation aggressiveness</span>
                    <select
                        v-model="form.settings.recommendation_aggressiveness"
                        name="settings[recommendation_aggressiveness]"
                        :id="controlId('settings.recommendation_aggressiveness')"
                        :aria-describedby="describedBy('settings.recommendation_aggressiveness')"
                        title="Stored now; the advisor reads it in a follow-up slice."
                        class="mt-1 block w-full min-h-11 rounded-md border border-rule bg-raised px-2 text-ink"
                    >
                        <option value="">Not set</option>
                        <option v-for="option in aggressivenessOptions" :key="option.value" :value="option.value">
                            {{ option.label }}
                        </option>
                    </select>
                </label>

                <fieldset class="space-y-2">
                    <legend class="font-medium text-ink">Stat-target defaults</legend>
                    <p class="text-xs text-ink-muted">
                        Pre-fills the Build Target screen's five stat inputs when no target has been
                        entered. The engine ceiling is {{ hardCap }}, the number no stat may pass
                        whatever the scenario's own bonus says.
                    </p>
                    <label
                        v-for="stat in statOrder"
                        :key="stat"
                        class="flex items-center justify-between gap-3"
                    >
                        <span class="font-medium text-ink">{{ stat }}</span>
                        <!-- No `min`/`max` here on purpose: an HTML clamp would stop the submit before the
                         Form Request saw it, and the bound has one owner. Build Target made the same
                         choice for the same inputs. -->
                    <input
                            v-model="form.settings.stat_target_defaults[stat]"
                            type="number"
                            :name="`settings[stat_target_defaults][${stat}]`"
                            :id="controlId(`settings.stat_target_defaults.${stat}`)"
                            :aria-describedby="describedBy(`settings.stat_target_defaults.${stat}`)"
                            class="mt-1 block w-32 min-h-11 rounded-md border border-rule bg-raised px-2 text-right font-mono tabular-nums text-ink"
                        >
                    </label>
                </fieldset>

                <dl class="border-t border-rule pt-3 space-y-2">
                    <div class="flex justify-between">
                        <dt class="text-ink">Risk tolerance</dt>
                        <dd
                            class="text-ink-muted"
                            title="A preference the brief's Build Target step would carry, and whether it enters the stored payload is that screen's own open question, not this screen's to settle."
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
                <dl class="border-t border-rule pt-3 space-y-2">
                    <div class="flex justify-between">
                        <dt class="text-ink">Import</dt>
                        <dd>
                            <a
                                :href="importUrl"
                                class="inline-flex min-h-11 items-center text-pick-line hover:underline"
                                >Open import</a
                            >
                        </dd>
                    </div>
                    <div class="flex justify-between">
                        <dt class="text-ink">Export</dt>
                        <dd>
                            <a
                                :href="exportUrl"
                                class="inline-flex min-h-11 items-center text-pick-line hover:underline"
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
                <button
                    type="button"
                    :disabled="backupForm.processing"
                    class="inline-flex min-h-11 items-center rounded-md border border-rule bg-raised px-4 text-sm font-bold text-ink-strong hover:bg-panel disabled:opacity-60"
                    @click="runBackup"
                >
                    {{ backupForm.processing ? 'Backing up…' : 'Back up now' }}
                </button>

                <dl class="border-t border-rule pt-3 space-y-2">
                    <div class="flex justify-between">
                        <dt class="text-ink">Restore</dt>
                        <dd
                            class="text-ink-muted"
                            title="Destructive: restoring replaces the database file, which AGENTS.md §5 scopes to the owner. Absent rather than a button without a backend."
                        >
                            N/A
                        </dd>
                    </div>
                    <div class="flex justify-between">
                        <dt class="text-ink">Reset</dt>
                        <dd
                            class="text-ink-muted"
                            title="Destructive; requires owner approval for a route (AGENTS §5)"
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
                <dl class="border-t border-rule pt-3 space-y-2">
                    <div class="flex justify-between">
                        <dt class="text-ink">Global Ruleset Version</dt>
                        <dd
                            :class="
                                rulesetVersion
                                    ? 'text-ink'
                                    : 'text-ink-muted'
                            "
                            :title="
                                rulesetVersion
                                    ? undefined
                                    : 'No source defines a Global ruleset version (design-2.0 §48)'
                            "
                        >
                            {{ rulesetVersion ?? 'N/A' }}
                        </dd>
                    </div>
                    <div class="flex justify-between">
                        <dt class="text-ink">Verified</dt>
                        <dd class="text-ink">
                            {{ verifiedAt ?? 'N/A' }}
                        </dd>
                    </div>
                </dl>
            </section>

            <p
                v-for="key in visibleErrors"
                :key="key"
                :id="errorId(key)"
                class="text-risk"
            >
                {{ errorOf(key) }}
            </p>

            <button
                type="submit"
                :disabled="form.processing"
                class="inline-flex min-h-11 items-center rounded-md border border-rule bg-raised px-4 text-sm font-bold text-ink-strong hover:bg-panel disabled:opacity-60"
            >
                {{ form.processing ? 'Saving…' : 'Save preferences' }}
            </button>
        </form>
    </AppLayout>
</template>
