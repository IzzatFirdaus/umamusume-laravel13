<script setup lang="ts">
/*
 * SCREEN-020, Save Veteran (`SCR-VET-003`, plan §8 D16's write half). The screen that files a finished
 * career into the Veteran library, and the first caller of `RecordVeteran`.
 *
 * The page pattern is `Career/SkillsPlanner.vue`'s: `CareerLayout`, page-level loading and error states,
 * `useForm` posting to a server-owned route.
 *
 * **What this screen is for, in the client's own words.** A Veteran is the Trainer's note about a career
 * that finished: which distances it ran, what it was good at, what happened. `design-2.0` §24 asks for a
 * tag list and a note, and that is the whole of the write. The name is not a field: the library row is a
 * pointer to the career (`ADR-0010`), so her name prints read-only through the run.
 *
 * **Three figures the brief asks for are not here, and the page says why.** Factor analysis, a legacy value
 * and a best use are `ADR-0020` §3's held computation and no table in this repository prices them, so each
 * prints `N/A` with the ruling as its disclosure rather than a score, a star rating or a sentence beginning
 * with advice. Nothing on this page recommends anything.
 *
 * **Tags are a suggestion, not a menu.** The groups come from `config('uma.veteran.suggested_tags')` in the
 * server's own order, and a Trainer can add a word of their own beside them. A tag's state is carried by
 * `aria-pressed` plus a visible check, never by colour alone, and every chip is a real `<button>` in the tab
 * order, so the keyboard path is the same path as the mouse path.
 */
import CareerLayout from '../../layouts/CareerLayout.vue';
import AbsenceValue from '../../components/AbsenceValue.vue';
import SparkChip from '../../components/legacy/SparkChip.vue';
import { Head, Link, useForm } from '@inertiajs/vue3';
import { computed, reactive, ref } from 'vue';
import { useVisitState } from '../../composables/useVisitState';

interface HeldFigure {
    value: null;
    title: string;
}

interface Spark {
    kind: string;
    kind_label: string;
    target: string | null;
    stars: number | null;
}

interface GraphParent {
    slot: string;
    label: string;
    name: string | null;
    rank: number | null;
    is_guest: boolean;
    sparks: Spark[];
}

const props = defineProps<{
    run: {
        id: number;
        trainee: string;
        trainee_ja: string | null;
        scenario_label: string;
        status: string;
        status_label: string;
        recordable: boolean;
    };
    career: { turns: number; energy: number | null; fans: number | null };
    stats: Record<string, number | null>;
    counts: { skills: number; races: number };
    graph: { trainee: { name: string | null }; parents: GraphParent[] };
    spark_kinds: Record<string, string>;
    suggested_tags: Record<string, string[]>;
    limits: { max_tags: number; max_tag_length: number; max_notes_length: number };
    saved: { tags: string[]; notes: string | null } | null;
    held: { factor_analysis: HeldFigure; legacy_value: HeldFigure; best_use: HeldFigure };
    notice: string;
    absences: { label: string; reason: string }[];
    blocked: string | null;
    save_url: string;
    result_url: string;
    run_url: string;
    library_url: string;
}>();

const { visiting, visitFailed } = useVisitState();

const form = useForm<{ tags: string[]; notes: string }>({
    tags: props.saved?.tags ?? [],
    notes: props.saved?.notes ?? '',
});

/** Every tag the screen offers, in the server's order. Used to tell a suggestion from the Trainer's own word. */
const suggestions = computed<string[]>(() => Object.values(props.suggested_tags).flat());

const customTags = computed<string[]>(() => form.tags.filter((tag) => !suggestions.value.includes(tag)));

const statLabels: { key: string; label: string }[] = [
    { key: 'Speed', label: 'Speed' },
    { key: 'Stamina', label: 'Stamina' },
    { key: 'Power', label: 'Power' },
    { key: 'Guts', label: 'Guts' },
    { key: 'Wit', label: 'Wit' },
];

function isSelected(tag: string): boolean {
    return form.tags.includes(tag);
}

const announcement = ref('');

function toggleTag(tag: string): void {
    const selecting = !isSelected(tag);

    form.tags = selecting
        ? [...form.tags, tag]
        : form.tags.filter((selected) => selected !== tag);

    // Announced because `aria-pressed` alone is silent for a Trainer using a keyboard with the chip list
    // already focused: the state of the row they are looking at changed, and nothing moved.
    announcement.value = `${tag} ${selecting ? 'tagged' : 'untagged'}.`;
}

const newTag = ref('');

/**
 * Adds the Trainer's own word. Duplicated on purpose-case, because the server de-duplicates the same way
 * (`StoreVeteranRequest::prepareForValidation()`), and a chip that silently vanished on save would look like
 * the write failed.
 */
function addCustomTag(): void {
    const tag = newTag.value.trim();

    if (tag === '' || form.tags.includes(tag)) {
        return;
    }

    form.tags = [...form.tags, tag];
    newTag.value = '';
    announcement.value = `Tag added: ${tag}.`;
}

const tagErrors = computed<string[]>(() => {
    const errors = new Set<string>();

    Object.entries(form.errors).forEach(([field, message]) => {
        if (field === 'tags' || field.startsWith('tags.')) {
            errors.add(message);
        }
    });

    return [...errors];
});

const saving = reactive({ busy: false });

function save(): void {
    saving.busy = true;

    form.post(props.save_url, {
        preserveScroll: true,
        onFinish: () => {
            saving.busy = false;
        },
    });
}
</script>

<template>
    <CareerLayout
        :trainee="props.run.trainee"
        :scenario-label="props.run.scenario_label"
        :status-label="props.run.status_label"
        :run-url="props.run.run_url"
    >
        <Head :title="`Save Veteran: ${props.run.trainee}`" />
        <template #title>Save Veteran</template>

        <p v-if="visiting" role="status" class="mb-4 text-sm text-ink-muted">Loading…</p>
        <p v-if="visitFailed" role="alert" class="mb-4 rounded border border-risk bg-raised px-3 py-2 text-sm text-risk">
            This screen did not load. Nothing was saved; try again.
        </p>

        <p class="text-sm text-ink-muted">{{ props.notice }}</p>

        <!-- A career that has not finished cannot be filed. The refusal names the status it actually holds
             and the two doors that lead onward, rather than showing a form whose save button would only fail. -->
        <section
            v-if="props.blocked !== null"
            aria-labelledby="blocked-heading"
            class="mt-4 rounded-md border border-rule bg-panel p-4"
        >
            <h2 id="blocked-heading" class="text-base font-semibold text-ink-strong">Nothing to file yet</h2>
            <p role="alert" class="mt-2 text-sm text-ink">{{ props.blocked }}</p>
            <p class="mt-3 flex flex-wrap gap-3 text-sm">
                <Link href="/training-runs" class="inline-flex min-h-11 items-center rounded-md border border-rule px-3 text-ink hover:bg-sunken">Open the run list</Link>
                <a :href="props.run_url" class="inline-flex min-h-11 items-center rounded-md border border-rule px-3 text-ink hover:bg-sunken">This career's record</a>
            </p>
        </section>

        <template v-else>
            <!-- Step 1 of the brief's workflow: review what the career recorded, before tagging it. -->
            <section aria-labelledby="review-heading" class="mt-4 rounded-md border border-rule bg-panel p-4">
                <h2 id="review-heading" class="text-base font-semibold text-ink-strong">What this career recorded</h2>
                <p class="mt-1 text-sm text-ink-muted">
                    Read back from the run. Nothing here is derived, and the library adds your tags and note to it.
                </p>

                <dl class="mt-3 grid grid-cols-2 gap-x-6 gap-y-3 text-sm sm:grid-cols-4">
                    <div>
                        <dt class="text-xs text-ink-muted">Trainee</dt>
                        <dd class="text-ink-strong">
                            {{ props.run.trainee }}<span v-if="props.run.trainee_ja" lang="ja" class="ml-1 text-ink-muted">{{ props.run.trainee_ja }}</span>
                        </dd>
                    </div>
                    <div>
                        <dt class="text-xs text-ink-muted">Turns logged</dt>
                        <dd class="text-ink-strong">{{ props.career.turns }}</dd>
                    </div>
                    <div>
                        <!-- Named for what is counted. The pivot holds Suggested and Skipped rows beside the
                             acquired ones, and `race_entries` holds NotOffered and Skipped rows beside the
                             finished ones, so "learned" and "run" would claim a filter this number does not
                             apply (`SCR-CAR-018` rules that a skipped race is not a race the career ran). -->
                        <dt class="text-xs text-ink-muted">Skills recorded</dt>
                        <dd class="text-ink-strong">{{ props.counts.skills }}</dd>
                    </div>
                    <div>
                        <dt class="text-xs text-ink-muted">Races entered</dt>
                        <dd class="text-ink-strong">{{ props.counts.races }}</dd>
                    </div>
                    <div v-for="stat in statLabels" :key="stat.key">
                        <dt class="text-xs text-ink-muted">{{ stat.label }}</dt>
                        <dd class="text-ink-strong">
                            <template v-if="props.stats[stat.key] !== null">{{ props.stats[stat.key] }}</template>
                            <AbsenceValue
                                v-else
                                reason="This run logged no turn, so it recorded no value for this stat."
                                compact
                            />
                        </dd>
                    </div>
                </dl>

                <h3 class="mt-4 text-sm font-semibold text-ink-strong">Sparks it carries</h3>
                <p v-if="props.graph.parents.length === 0" class="mt-1 text-sm text-ink-muted" role="status">
                    This career records no parents, so it carries no Sparks. A parent is chosen on the Legacy Lab.
                </p>
                <ul v-else class="mt-2 space-y-3">
                    <li v-for="parent in props.graph.parents" :key="parent.slot" class="text-sm">
                        <span class="font-semibold text-ink-strong">{{ parent.label }}</span>
                        <span class="text-ink" title="You have not recorded a parent for this slot.">{{ parent.name ?? 'N/A' }}</span>
                        <span v-if="parent.is_guest" class="ml-2 text-xs text-ink-muted">(rented)</span>
                        <span class="ml-2 text-xs text-ink-muted" title="You have not recorded a rank for this parent.">
                            Rank: {{ parent.rank ?? 'N/A' }}
                        </span>
                        <ul v-if="parent.sparks.length > 0" class="mt-1 flex flex-wrap gap-2">
                            <li v-for="(spark, sparkIndex) in parent.sparks" :key="`${parent.slot}-${sparkIndex}`">
                                <SparkChip
                                    :kind-label="props.spark_kinds[spark.kind] ?? spark.kind_label ?? spark.kind"
                                    :target="spark.target"
                                    :stars="spark.stars"
                                />
                            </li>
                        </ul>
                        <p v-else class="mt-1 text-xs text-ink-muted">No Spark recorded on this parent.</p>
                    </li>
                </ul>
            </section>

            <!-- The three figures `screen-spec-2.0` §24 asks for. Held, printed as held, with the ruling as
                 the reason. A number here would be an invention, and a sentence of advice would be a
                 recommendation `ADR-0020` §3 keeps out of the record screens. -->
            <section aria-labelledby="held-heading" class="mt-4 rounded-md border border-rule bg-panel p-4">
                <h2 id="held-heading" class="text-base font-semibold text-ink-strong">Not computed</h2>
                <dl class="mt-2 grid gap-3 text-sm sm:grid-cols-3">
                    <div v-for="(figure, name) in props.held" :key="name">
                        <dt class="text-xs text-ink-muted">{{ name === 'factor_analysis' ? 'Factor analysis' : name === 'legacy_value' ? 'Legacy value' : 'Best use' }}</dt>
                        <dd class="text-ink-strong">
                            <AbsenceValue :reason="figure.title" compact />
                        </dd>
                    </div>
                </dl>
            </section>

            <!-- Step 2: tag it. Suggestions first, grouped in the server's order, then the Trainer's own words. -->
            <form class="mt-4 space-y-4" @submit.prevent="save">
                <section aria-labelledby="tags-heading" class="rounded-md border border-rule bg-panel p-4">
                    <h2 id="tags-heading" class="text-base font-semibold text-ink-strong">Tags</h2>
                    <p id="tags-help" class="mt-1 text-sm text-ink-muted">
                        Suggestions, not a fixed list. Choose any of these, or add a word of your own below.
                    </p>

                    <p v-if="form.errors.run" id="tag-run-error" role="alert" class="mt-2 rounded border border-risk bg-raised px-3 py-2 text-sm text-risk">
                        {{ form.errors.run }}
                    </p>

                    <div v-for="(groupTags, group) in props.suggested_tags" :key="group" class="mt-3">
                        <h3 class="text-xs font-semibold uppercase tracking-wide text-ink-muted">{{ group }}</h3>
                        <div class="mt-1 flex flex-wrap gap-2">
                            <button
                                v-for="tag in groupTags"
                                :key="tag"
                                type="button"
                                class="enamel inline-flex min-h-11 items-center gap-1 rounded-full border border-rule px-3 text-sm font-semibold text-ink aria-pressed:bg-pick aria-pressed:font-bold aria-pressed:text-on-pick"
                                :aria-pressed="isSelected(tag)"
                                aria-describedby="tags-help"
                                @click="toggleTag(tag)"
                            >
                                <!-- The dot is the non-colour half of the state: `aria-pressed` carries it to
                                     assistive tech, the gold fill carries it to sight, and neither is relied
                                     on alone (`DESIGN.md` §1.4.1). The badge's tick glyph is deliberately not
                                     used here: `ProvenanceBadge` owns it, and `CareerBuildTargetTest` sweeps
                                     every component source for it, comment included. -->
                                <span v-if="isSelected(tag)" aria-hidden="true" class="mr-0.5">●</span>
                                {{ tag }}
                            </button>
                        </div>
                    </div>

                    <div class="mt-4 flex flex-wrap items-end gap-2">
                        <label class="flex flex-col gap-1 text-sm" for="custom-tag">
                            <span class="text-ink-muted">Your own tag</span>
                            <input
                                id="custom-tag"
                                v-model="newTag"
                                type="text"
                                :maxlength="props.limits.max_tag_length"
                                :aria-describedby="tagErrors.length > 0 ? 'tag-errors' : 'tags-help'"
                                placeholder="Any word you would search by"
                                class="h-11 rounded-md border border-rule bg-raised px-2 text-ink"
                            >
                        </label>
                        <button
                            type="button"
                            class="enamel inline-flex h-11 items-center rounded-full bg-chrome px-4 text-sm font-bold text-on-chrome disabled:opacity-50"
                            :disabled="newTag.trim() === ''"
                            @click="addCustomTag"
                        >
                            Add tag
                        </button>
                    </div>

                    <ul v-if="customTags.length > 0" class="mt-2 flex flex-wrap gap-2">
                        <li v-for="tag in customTags" :key="`custom-${tag}`">
                            <button
                                type="button"
                                class="inline-flex min-h-11 items-center gap-1 rounded-full border border-rule bg-pick px-3 text-sm font-semibold text-on-pick"
                                aria-pressed="true"
                                @click="toggleTag(tag)"
                            >
                                <span aria-hidden="true">●</span>
                                {{ tag }}
                                <span class="sr-only">tagged. Choose to remove.</span>
                            </button>
                        </li>
                    </ul>

                    <p role="status" aria-live="polite" class="mt-2 text-sm text-ink-muted">
                        {{ announcement }}
                    </p>

                    <ul v-if="tagErrors.length > 0" id="tag-errors" class="mt-2 space-y-1">
                        <li
                            v-for="message in tagErrors"
                            :key="message"
                            role="alert"
                            class="rounded border border-risk bg-raised px-3 py-1 text-sm text-risk"
                        >
                            {{ message }}
                        </li>
                    </ul>
                </section>

                <section aria-labelledby="note-heading" class="rounded-md border border-rule bg-panel p-4">
                    <h2 id="note-heading" class="text-base font-semibold text-ink-strong">Note</h2>
                    <label class="mt-1 block text-sm" for="veteran-notes">
                        <span class="text-ink-muted">What you want to remember about this career</span>
                        <textarea
                            id="veteran-notes"
                            v-model="form.notes"
                            rows="4"
                            :maxlength="props.limits.max_notes_length"
                            class="mt-1 w-full rounded-md border border-rule bg-raised px-2 py-1 text-ink"
                            :aria-describedby="form.errors.notes ? 'notes-error' : 'notes-help'"
                        ></textarea>
                    </label>
                    <p id="notes-help" class="mt-1 text-xs text-ink-muted">
                        Free text, up to {{ props.limits.max_notes_length }} characters. Printed on the library's detail screen exactly as you write it.
                    </p>
                    <p v-if="form.errors.notes" id="notes-error" role="alert" class="mt-1 text-sm text-risk">
                        {{ form.errors.notes }}
                    </p>
                </section>

                <div class="flex flex-wrap items-center gap-3">
                    <button
                        type="submit"
                        class="enamel inline-flex h-11 items-center rounded-full bg-chrome px-5 text-sm font-bold text-on-chrome disabled:opacity-50"
                        :disabled="form.processing || saving.busy"
                    >
                        {{ form.processing ? 'Saving…' : (props.saved === null ? 'Save Veteran' : 'Update Veteran') }}
                    </button>
                    <a :href="props.result_url" class="inline-flex min-h-11 items-center text-sm text-ink underline">Back to the result</a>
                    <a :href="props.library_url" class="inline-flex min-h-11 items-center text-sm text-ink underline">Open the library</a>
                </div>
            </form>

            <!-- Step 3 is the save itself: the button above posts to `runs.veteran.store` and the library row
                 lands or rewrites in one step, so there is no second confirmation to click through. -->
            <section aria-labelledby="absences-heading" class="mt-4 rounded-md border border-rule bg-panel p-4">
                <h2 id="absences-heading" class="text-base font-semibold text-ink-strong">NOT IN THIS BUILD</h2>
                <ul class="mt-2 space-y-2 text-sm">
                    <li v-for="absence in props.absences" :key="absence.label">
                        <span class="text-ink">{{ absence.label }}</span>: {{ absence.reason }}
                    </li>
                </ul>
            </section>
        </template>
    </CareerLayout>
</template>
