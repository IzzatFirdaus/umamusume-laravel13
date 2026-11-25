<script setup lang="ts">
/*
 * The Unity Cup roster, as a task-shaped table (`UC2.4` + `UC3`).
 *
 * **Why a table and not a column of cards.** §1.8's roster is twenty members and the client lays it
 * out in five columns with an ACE each. The previous surface printed one card per member, so
 * nineteen members stacked into a page a Trainer had to scroll rather than scan, and the only way to
 * compare two members' Speed was to look at them one at a time. A table answers the comparison in one
 * pass. `DataTable`'s own rule is the reason it is `<table>` and not a grid of divs: a screen reader
 * announces a table as a table, with its row and column count and each cell's headers.
 *
 * **The editor is contextual and opens over the row it edits** (`UC3`). It used to replace the row
 * inline, in the middle of the list, which pushed every row below it down by a form's height. A
 * native `<dialog>` opened with `showModal()` owns the focus trap, the Escape key and the inert
 * background from the platform, so none of that is re-implemented here — the same pattern
 * `CardPicker.vue` and `RaceCard.vue` already use on this codebase.
 *
 * **Remove lives in the editor, not on the row.** It was a red button on every row, so the roster
 * read as a list of destructive affordances. The confirmation is unchanged and the server's own
 * restrictions (`RosterMember::booted()`) are untouched: this component adds no write path.
 *
 * **Null is not zero** (`D-220`). A stat the Trainer has not read renders a dash with an `sr-only`
 * "Not recorded", never `0`, and a member added by name alone says so rather than printing five
 * zeroes. Submit transforms post `null` for every blank field.
 *
 * **The counts are kept apart.** `members.length` is the number of identities this run recorded.
 * `max_members` is the client's ceiling. The client's own `Members: 20 / 20` reading is a third
 * number, and no column stores it, so this surface does not print one — a difference between the two
 * is stated rather than explained away.
 */
import { computed, nextTick, ref, watch } from 'vue';
import { router, useForm } from '@inertiajs/vue3';
import { BaseButton, BaseCheckbox, BaseInput, BaseSelect } from '../Form';
import type { RosterMemberRow, RosterPayload } from './RosterTableRows';

const props = defineProps<{
    /** `rosterSection()` plus the write URLs, or null when the page has not wired the roster in. */
    roster: RosterPayload | null;
    /** Number of identities the client showed training alongside the trainee (UC3.3). Null when the run has not recorded an observation. */
    observed_member_count: number | null;
}>();

const GRADE_OPTIONS = [
    { value: '', label: 'Not recorded' },
    { value: 'G', label: 'G' },
    { value: 'F', label: 'F' },
    { value: 'E', label: 'E' },
    { value: 'D', label: 'D' },
    { value: 'C', label: 'C' },
    { value: 'B', label: 'B' },
    { value: 'A', label: 'A' },
    { value: 'S', label: 'S' },
];

/* `RosterDistance`'s five columns plus the unassigned blank; Dirt is a team column here and
 * deliberately not one of `BuildTargetPayload::DISTANCE_BANDS` (four, no Dirt). */
const CATEGORIES = ['Sprint', 'Mile', 'Medium', 'Long', 'Dirt'];

const DISTANCE_OPTIONS = [
    /* "No category", not "Unassigned": the write side stores the same null for "she stands outside
     * every column" and "I have not read her column yet", so the option must not claim the first
     * (`UC3.5`). */
    { value: '', label: 'No category' },
    ...CATEGORIES.map((category) => ({ value: category, label: category })),
];

const MAX_MEMBERS = computed((): number => props.roster?.max_members ?? 20);

const text = (value: string | null): string => value ?? '';
const figure = (value: string): number | null => (value === '' ? null : Number(value));
const numberText = (value: number | null): string => (value === null ? '' : String(value));

/* ---- Reading controls ------------------------------------------------------------------- */

const query = ref('');
const categoryFilter = ref('');
const originFilter = ref('');
const sortKey = ref<'position' | 'name' | 'grade' | 'category'>('position');
const sortAsc = ref(true);

const RANK_OF_GRADE = (grade: string | null): number => {
    if (grade === null) {
        return -1;
    }
    const ladder = ['G', 'F', 'E', 'D', 'C', 'B', 'A', 'S'];
    return ladder.indexOf(grade);
};

const rows = computed((): RosterMemberRow[] => {
    const members = props.roster?.members ?? [];
    const needle = query.value.trim().toLowerCase();

    const filtered = members.filter((member) => {
        if (needle !== '' && !member.name.toLowerCase().includes(needle)) {
            return false;
        }
        if (categoryFilter.value !== '' && member.distance_assignment !== categoryFilter.value) {
            return false;
        }
        if (originFilter.value === 'catalogue' && member.umamusume_id === null) {
            return false;
        }
        if (originFilter.value === 'name' && member.umamusume_id !== null) {
            return false;
        }

        return true;
    });

    const sorted = [...filtered].sort((a, b) => {
        switch (sortKey.value) {
            case 'name':
                return a.name.localeCompare(b.name);
            case 'grade':
                return RANK_OF_GRADE(a.grade) - RANK_OF_GRADE(b.grade);
            case 'category':
                return (a.distance_assignment ?? '~').localeCompare(b.distance_assignment ?? '~');
            default:
                return a.position - b.position;
        }
    });

    return sortAsc.value ? sorted : sorted.reverse();
});

const sortBy = (key: 'position' | 'name' | 'grade' | 'category'): void => {
    if (sortKey.value === key) {
        sortAsc.value = !sortAsc.value;
        return;
    }
    sortKey.value = key;
    sortAsc.value = true;
};

const ariaSort = (key: string): 'ascending' | 'descending' | 'none' | undefined => {
    if (['position', 'name', 'grade', 'category'].indexOf(key) === -1) {
        return undefined;
    }

    return sortKey.value === key ? (sortAsc.value ? 'ascending' : 'descending') : 'none';
};

const filtering = computed((): boolean =>
    query.value.trim() !== '' || categoryFilter.value !== '' || originFilter.value !== '');

const clearFilters = (): void => {
    query.value = '';
    categoryFilter.value = '';
    originFilter.value = '';
};

const stat = (value: number | null): string => (value === null ? '—' : String(value));

/*
 * The ceiling, printed only beside a reading that exists.
 *
 * A member with `speed: null, speed_cap: 1600` has a cap and no reading, so drawing `/1600` next to a
 * dash would invent a comparison the run never made. The pair is only informative when both halves
 * are recorded, which is why this returns null rather than a partial string.
 */
const cap = (value: number | null, ceiling: number | null): string | null =>
    value === null || ceiling === null ? null : `/${ceiling}`;

/* ---- Add form --------------------------------------------------------------------------- */

const adding = ref(false);

const newMember = useForm({
    name: '',
    grade: '',
    speed: '',
    stamina: '',
    power: '',
    guts: '',
    wit: '',
    speed_cap: '',
    stamina_cap: '',
    power_cap: '',
    guts_cap: '',
    wit_cap: '',
    distance_assignment: '',
    is_ace: false,
    is_trainee: false,
    // UC3.4's ten observed aptitudes. `submitAdd` posts all ten, so the form has to carry them or
    // the request sends `undefined` for every axis — and a blank here is an unobserved axis, not a
    // missing key (D-220). The editor's `draft` declares the same set; both must stay in step.
    aptitude_turf: '',
    aptitude_dirt: '',
    aptitude_sprint: '',
    aptitude_mile: '',
    aptitude_medium: '',
    aptitude_long: '',
    aptitude_front_runner: '',
    aptitude_pace_chaser: '',
    aptitude_late_surger: '',
    aptitude_end_closer: '',
});

const rosterFull = computed((): boolean => (props.roster?.members.length ?? 0) >= MAX_MEMBERS.value);

const openAdd = (): void => {
    if (props.roster === null || rosterFull.value) {
        return;
    }
    adding.value = true;
    newMember.clearErrors();
    newMember.reset();
};

const submitAdd = (): void => {
    if (props.roster === null) {
        return;
    }

    newMember
        .transform((data) => ({
            name: data.name.trim(),
            grade: data.grade === '' ? null : data.grade,
            speed: figure(data.speed),
            stamina: figure(data.stamina),
            power: figure(data.power),
            guts: figure(data.guts),
            wit: figure(data.wit),
            speed_cap: figure(data.speed_cap),
            stamina_cap: figure(data.stamina_cap),
            power_cap: figure(data.power_cap),
            guts_cap: figure(data.guts_cap),
            wit_cap: figure(data.wit_cap),
            distance_assignment: data.distance_assignment === '' ? null : data.distance_assignment,
            is_ace: data.is_ace,
            is_trainee: data.is_trainee,
            aptitude_turf: data.aptitude_turf,
            aptitude_dirt: data.aptitude_dirt,
            aptitude_sprint: data.aptitude_sprint,
            aptitude_mile: data.aptitude_mile,
            aptitude_medium: data.aptitude_medium,
            aptitude_long: data.aptitude_long,
            aptitude_front_runner: data.aptitude_front_runner,
            aptitude_pace_chaser: data.aptitude_pace_chaser,
            aptitude_late_surger: data.aptitude_late_surger,
            aptitude_end_closer: data.aptitude_end_closer,
        }))
        .post(props.roster.store_url, {
            preserveScroll: true,
            onSuccess: () => {
                adding.value = false;
                newMember.reset();
            },
        });
};

/* ---- The contextual editor -------------------------------------------------------------- */

const editing = ref<RosterMemberRow | null>(null);
const dialog = ref<HTMLDialogElement | null>(null);
const draft = ref({
    name: '',
    grade: '',
    speed: '',
    stamina: '',
    power: '',
    guts: '',
    wit: '',
    speed_cap: '',
    stamina_cap: '',
    power_cap: '',
    guts_cap: '',
    wit_cap: '',
    distance_assignment: '',
    is_ace: false,
    is_trainee: false,
    aptitude_turf: '',
    aptitude_dirt: '',
    aptitude_sprint: '',
    aptitude_mile: '',
    aptitude_medium: '',
    aptitude_long: '',
    aptitude_front_runner: '',
    aptitude_pace_chaser: '',
    aptitude_late_surger: '',
    aptitude_end_closer: '',
});
const rowError = ref<string | null>(null);
const rowBusy = ref(false);
const removing = ref(false);

const openEditor = async (member: RosterMemberRow): Promise<void> => {
    editing.value = member;
    rowError.value = null;
    removing.value = false;
    draft.value = {
        name: member.name,
        grade: member.grade ?? '',
        speed: numberText(member.speed),
        stamina: numberText(member.stamina),
        power: numberText(member.power),
        guts: numberText(member.guts),
        wit: numberText(member.wit),
        speed_cap: numberText(member.speed_cap),
        stamina_cap: numberText(member.stamina_cap),
        power_cap: numberText(member.power_cap),
        guts_cap: numberText(member.guts_cap),
        wit_cap: numberText(member.wit_cap),
        distance_assignment: member.distance_assignment ?? '',
        is_ace: member.is_ace,
        is_trainee: member.is_trainee,
        // UC3.4: seed all ten from the stored row. Without these the editor opens with every axis
        // blank and writes the blanks back, erasing observations the run recorded.
        aptitude_turf: member.aptitude_turf ?? '',
        aptitude_dirt: member.aptitude_dirt ?? '',
        aptitude_sprint: member.aptitude_sprint ?? '',
        aptitude_mile: member.aptitude_mile ?? '',
        aptitude_medium: member.aptitude_medium ?? '',
        aptitude_long: member.aptitude_long ?? '',
        aptitude_front_runner: member.aptitude_front_runner ?? '',
        aptitude_pace_chaser: member.aptitude_pace_chaser ?? '',
        aptitude_late_surger: member.aptitude_late_surger ?? '',
        aptitude_end_closer: member.aptitude_end_closer ?? '',
    };

    await nextTick();
    dialog.value?.showModal();
    await nextTick();
    // Focus the name field through the DOM, not through a template ref: `BaseInput` is a component,
    // and a `<script setup>` component without `defineExpose` hands back its public proxy, which has
    // no `focus()`. Querying the rendered control is the platform's own behaviour — `showModal()`
    // would otherwise leave focus on the dialog's first focusable, the Close button, which puts
    // Escape one keystroke away from "close" and every field two stops from the keyboard.
    dialog.value?.querySelector<HTMLInputElement>('#roster_edit_name')?.focus();
};

const closeEditor = (): void => {
    dialog.value?.close();
};

/** Fires on `close`, whatever closed it: the Close button, Escape, or a successful save. */
const editorClosed = (): void => {
    editing.value = null;
    rowError.value = null;
    removing.value = false;
};

const saveEditor = (): void => {
    const member = editing.value;
    if (member === null) {
        return;
    }

    rowBusy.value = true;
    rowError.value = null;

    router.put(
        member.update_url,
        {
            name: draft.value.name.trim(),
            grade: draft.value.grade === '' ? null : draft.value.grade,
            speed: figure(draft.value.speed),
            stamina: figure(draft.value.stamina),
            power: figure(draft.value.power),
            guts: figure(draft.value.guts),
            wit: figure(draft.value.wit),
            speed_cap: figure(draft.value.speed_cap),
            stamina_cap: figure(draft.value.stamina_cap),
            power_cap: figure(draft.value.power_cap),
            guts_cap: figure(draft.value.guts_cap),
            wit_cap: figure(draft.value.wit_cap),
            distance_assignment: draft.value.distance_assignment === '' ? null : draft.value.distance_assignment,
            is_ace: draft.value.is_ace,
            is_trainee: draft.value.is_trainee,
            aptitude_turf: draft.value.aptitude_turf,
            aptitude_dirt: draft.value.aptitude_dirt,
            aptitude_sprint: draft.value.aptitude_sprint,
            aptitude_mile: draft.value.aptitude_mile,
            aptitude_medium: draft.value.aptitude_medium,
            aptitude_long: draft.value.aptitude_long,
            aptitude_front_runner: draft.value.aptitude_front_runner,
            aptitude_pace_chaser: draft.value.aptitude_pace_chaser,
            aptitude_late_surger: draft.value.aptitude_late_surger,
            aptitude_end_closer: draft.value.aptitude_end_closer,
        },
        {
            preserveScroll: true,
            onError: (errors) => {
                rowError.value = Object.values(errors)[0] ?? 'That member could not be saved.';
            },
            onFinish: () => {
                rowBusy.value = false;
            },
            onSuccess: () => {
                closeEditor();
            },
        },
    );
};

const removeMember = (): void => {
    const member = editing.value;
    if (member === null) {
        return;
    }
    if (!confirm(`Remove "${member.name}" from the team roster? This cannot be undone.`)) {
        return;
    }

    rowBusy.value = true;
    router.delete(member.destroy_url, {
        preserveScroll: true,
        onFinish: () => {
            rowBusy.value = false;
        },
        onSuccess: () => {
            closeEditor();
        },
    });
};

/* A member deleted elsewhere, or a save that re-rendered the list, leaves the editor pointing at a
 * row that no longer exists. Closing on that is the honest response; re-pointing at a different row
 * would silently swap the subject of an open form. */
watch(
    () => props.roster?.members,
    (members) => {
        if (editing.value !== null && (members ?? []).every((member) => member.id !== editing.value?.id)) {
            closeEditor();
        }
    },
);
</script>

<template>
    <section aria-labelledby="team-roster-heading" class="card">
        <div class="flex flex-wrap items-start justify-between gap-x-4 gap-y-2">
            <div class="min-w-0">
                <h2 id="team-roster-heading" class="text-lg font-semibold text-ink-strong">
                    Recorded roster
                </h2>
                <p class="sub">
                    The characters this run recorded alongside the trainee, read from the run's own
                    roster rather than from the six support cards.
                </p>
            </div>

            <BaseButton
                v-if="props.roster !== null && !adding && !rosterFull"
                variant="secondary"
                size="sm"
                @click="openAdd"
            >
                Add member
            </BaseButton>
        </div>

        <!-- The panel the page was not given: a write form with no submit target is a lie about what
             the server will accept, so this says so instead. -->
        <div v-if="props.roster === null" class="mt-3 rounded-md border border-rule bg-sunken p-3 text-sm text-ink-muted">
            <p class="font-semibold text-ink-strong">Roster unavailable on this screen</p>
            <p class="mt-1">
                The team page has not been given the roster yet. The write routes exist
                (<code>runs.roster.store/update/destroy</code>); the page must pass the roster section
                and its action URLs for this panel to function.
            </p>
        </div>

        <template v-else>
            <!-- The count, in the three facts it actually is. `Members: 20 / 20` on the client is a
                 reading off the game; `18` is how many identities this run transcribed; `20` is the
ceiling. The run stores the client's own reading (`observed_member_count`), and this
            payload now carries it. The observed figure is printed beside the recorded one, and
            reconciliation is performed by the backend. -->
            <p class="mt-2 text-sm text-ink-muted">
                <span class="font-mono font-bold tabular-nums text-ink-strong">{{ props.roster.members.length }}</span>
                recorded
                <template v-if="props.observed_member_count !== null">
                    ·
                    <span class="font-mono font-bold tabular-nums text-ink-strong">
                        {{ props.observed_member_count }} observed
                    </span>
                </template>
                <template v-else>
                    ·
                    <span class="text-ink-muted">N/A</span>
                </template>
                <span v-if="props.roster.members.length < MAX_MEMBERS" class="text-ink-muted">
                    · {{ MAX_MEMBERS - props.roster.members.length }} not yet recorded
                </span>
                <span v-else class="text-ink-muted">· the client's own ceiling</span>
            </p>

            <p v-if="props.roster.unrecorded" class="mt-1 text-sm text-ink-muted">
                No team roster has been recorded on this run yet. Add members to build it.
            </p>

            <p class="mt-1 text-xs text-ink-muted">
                The client's own team panel reports its member count separately from this list. That
                reading is stored on the run, and this payload now carries the observed member count
                alongside the recorded count. Reconciliation between the two is performed by the
                backend.
            </p>

            <!-- Add form -->
            <form
                v-if="adding"
                class="mt-3 grid grid-cols-1 gap-3 rounded-md border border-rule bg-raised p-3 @2xl:grid-cols-2"
                @submit.prevent="submitAdd"
            >
                <h3 class="text-sm font-semibold text-ink-strong @2xl:col-span-2">New member</h3>
                <label class="flex flex-col gap-1">
                    <span class="text-ink-muted">Name <span class="text-risk" aria-hidden="true">*</span></span>
                    <BaseInput
                        id="roster_new_name"
                        v-model="newMember.name"
                        name="name"
                        type="text"
                        maxlength="100"
                        required
                        :error="newMember.errors.name"
                    />
                </label>
                <label class="flex flex-col gap-1">
                    <span class="text-ink-muted">Grade</span>
                    <BaseSelect
                        id="roster_new_grade"
                        v-model="newMember.grade"
                        name="grade"
                        :options="GRADE_OPTIONS"
                        :error="newMember.errors.grade"
                    />
                </label>
                <label class="flex flex-col gap-1">
                    <span class="text-ink-muted">Race category</span>
                    <BaseSelect
                        id="roster_new_distance"
                        v-model="newMember.distance_assignment"
                        name="distance_assignment"
                        :options="DISTANCE_OPTIONS"
                        :error="newMember.errors.distance_assignment"
                    />
                </label>
                <div class="flex flex-wrap items-end gap-3">
                    <label class="flex w-24 flex-col gap-1">
                        <span class="text-ink-muted">Speed</span>
                        <BaseInput id="roster_new_speed" v-model="newMember.speed" name="speed" type="number" min="0" max="2000" :error="newMember.errors.speed" />
                    </label>
                    <label class="flex w-24 flex-col gap-1">
                        <span class="text-ink-muted">Stamina</span>
                        <BaseInput id="roster_new_stamina" v-model="newMember.stamina" name="stamina" type="number" min="0" max="2000" :error="newMember.errors.stamina" />
                    </label>
                    <label class="flex w-24 flex-col gap-1">
                        <span class="text-ink-muted">Power</span>
                        <BaseInput id="roster_new_power" v-model="newMember.power" name="power" type="number" min="0" max="2000" :error="newMember.errors.power" />
                    </label>
                    <label class="flex w-24 flex-col gap-1">
                        <span class="text-ink-muted">Guts</span>
                        <BaseInput id="roster_new_guts" v-model="newMember.guts" name="guts" type="number" min="0" max="2000" :error="newMember.errors.guts" />
                    </label>
                    <label class="flex w-24 flex-col gap-1">
                        <span class="text-ink-muted">Wit</span>
                        <BaseInput id="roster_new_wit" v-model="newMember.wit" name="wit" type="number" min="0" max="2000" :error="newMember.errors.wit" />
                    </label>
                </div>
                <!-- The ceilings. Optional at record time: a member added to the roster before her
                     caps were read is a legal row, and filling these in later is what the editor is
                     for. Kept in the same five-stat order as the stats above so the two rows read as
                     pairs rather than as two unrelated groups. -->
                <div class="flex flex-wrap items-end gap-3">
                    <label class="flex w-24 flex-col gap-1">
                        <span class="text-ink-muted">Speed cap</span>
                        <BaseInput id="roster_new_speed_cap" v-model="newMember.speed_cap" name="speed_cap" type="number" min="0" max="2000" :error="newMember.errors.speed_cap" />
                    </label>
                    <label class="flex w-24 flex-col gap-1">
                        <span class="text-ink-muted">Stamina cap</span>
                        <BaseInput id="roster_new_stamina_cap" v-model="newMember.stamina_cap" name="stamina_cap" type="number" min="0" max="2000" :error="newMember.errors.stamina_cap" />
                    </label>
                    <label class="flex w-24 flex-col gap-1">
                        <span class="text-ink-muted">Power cap</span>
                        <BaseInput id="roster_new_power_cap" v-model="newMember.power_cap" name="power_cap" type="number" min="0" max="2000" :error="newMember.errors.power_cap" />
                    </label>
                    <label class="flex w-24 flex-col gap-1">
                        <span class="text-ink-muted">Guts cap</span>
                        <BaseInput id="roster_new_guts_cap" v-model="newMember.guts_cap" name="guts_cap" type="number" min="0" max="2000" :error="newMember.errors.guts_cap" />
                    </label>
                    <label class="flex w-24 flex-col gap-1">
                        <span class="text-ink-muted">Wit cap</span>
                        <BaseInput id="roster_new_wit_cap" v-model="newMember.wit_cap" name="wit_cap" type="number" min="0" max="2000" :error="newMember.errors.wit_cap" />
                    </label>
                </div>
                <div class="flex flex-wrap items-center gap-4 @2xl:col-span-2">
                    <BaseCheckbox id="roster_new_ace" v-model="newMember.is_ace" name="is_ace" label="ACE" :error="newMember.errors.is_ace" />
                    <BaseCheckbox id="roster_new_trainee" v-model="newMember.is_trainee" name="is_trainee" label="Trainee" :error="newMember.errors.is_trainee" />
                </div>
                <div class="flex gap-2 @2xl:col-span-2">
                    <BaseButton type="submit" variant="primary" size="sm" :loading="newMember.processing">
                        {{ newMember.processing ? 'Adding…' : 'Add member' }}
                    </BaseButton>
                    <BaseButton type="button" variant="secondary" size="sm" @click="adding = false">Cancel</BaseButton>
                </div>
            </form>

            <!-- Reading controls. Search, one category filter, one origin filter: the three cuts the
                 recorded data actually supports. Sorting is local because the whole roster arrives in
                 one payload (max 20 rows), so a round trip would be slower than a sort. -->
            <div v-if="props.roster.members.length > 0" class="mt-3 flex flex-wrap items-end gap-3 text-sm">
                <label class="flex flex-col gap-1">
                    <span class="text-xs font-semibold uppercase tracking-wide text-ink-muted">Search name</span>
                    <BaseInput
                        id="roster_search"
                        v-model="query"
                        name="roster_search"
                        type="search"
                        autocorrect="off"
                        autocapitalize="none"
                        :spellcheck="false"
                        enterkeyhint="search"
                    />
                </label>
                <label class="flex flex-col gap-1">
                    <span class="text-xs font-semibold uppercase tracking-wide text-ink-muted">Race category</span>
                    <BaseSelect
                        id="roster_filter_category"
                        v-model="categoryFilter"
                        name="roster_filter_category"
                        :options="[{ value: '', label: 'All categories' }, ...CATEGORIES.map((category) => ({ value: category, label: category }))]"
                    />
                </label>
                <label class="flex flex-col gap-1">
                    <span class="text-xs font-semibold uppercase tracking-wide text-ink-muted">Catalogue link</span>
                    <BaseSelect
                        id="roster_filter_origin"
                        v-model="originFilter"
                        name="roster_filter_origin"
                        :options="[
                            { value: '', label: 'All members' },
                            { value: 'catalogue', label: 'Catalogue-linked' },
                            { value: 'name', label: 'Name only' },
                        ]"
                    />
                </label>
                <BaseButton v-if="filtering" type="button" variant="secondary" size="sm" @click="clearFilters">
                    Clear
                </BaseButton>
                <p v-if="filtering" role="status" class="pb-2 text-xs text-ink-muted">
                    {{ rows.length }} of {{ props.roster.members.length }} shown
                </p>
            </div>

            <!-- The table. `tabindex="0"` + a name make it a focusable scroll region, so the arrow keys
                 scroll it at narrow widths instead of moving the page (WCAG 2.1.1). -->
            <div
                v-if="props.roster.members.length > 0"
                class="mt-2 overflow-x-auto focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-ring"
                role="region"
                tabindex="0"
                aria-label="Recorded team roster, scrollable"
            >
                <table class="t">
                    <caption class="sr-only">
                        Recorded team roster for this run, one row per member.
                    </caption>
                    <thead>
                        <tr>
                            <th scope="col" :aria-sort="ariaSort('name')">
                                <button type="button" class="inline-flex min-h-6 items-center gap-1" @click="sortBy('name')">
                                    Member
                                    <span aria-hidden="true" class="text-ink-muted">{{ sortKey === 'name' ? (sortAsc ? '▲' : '▼') : '↕' }}</span>
                                </button>
                            </th>
                            <th scope="col" :aria-sort="ariaSort('grade')">
                                <button type="button" class="inline-flex min-h-6 items-center gap-1" @click="sortBy('grade')">
                                    Grade
                                    <span aria-hidden="true" class="text-ink-muted">{{ sortKey === 'grade' ? (sortAsc ? '▲' : '▼') : '↕' }}</span>
                                </button>
                            </th>
                            <th scope="col" class="n">Speed</th>
                            <th scope="col" class="n">Stamina</th>
                            <th scope="col" class="n">Power</th>
                            <th scope="col" class="n">Guts</th>
                            <th scope="col" class="n">Wit</th>
                            <th scope="col" :aria-sort="ariaSort('category')">
                                <button type="button" class="inline-flex min-h-6 items-center gap-1" @click="sortBy('category')">
                                    Category
                                    <span aria-hidden="true" class="text-ink-muted">{{ sortKey === 'category' ? (sortAsc ? '▲' : '▼') : '↕' }}</span>
                                </button>
                            </th>
                            <th scope="col">Flags</th>
                            <th scope="col"><span class="sr-only">Actions</span></th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr v-for="member in rows" :key="member.id">
                            <th scope="row" class="text-left font-semibold text-ink-strong">
                                {{ member.name }}
                                <span v-if="member.umamusume_id === null" class="ml-1 text-xs font-normal text-ink-muted">
                                    (name only)
                                </span>
                            </th>
                            <td>
                                <span v-if="member.grade !== null" class="pill pill-neutral">{{ member.grade }}</span>
                                <span v-else class="text-ink-muted">—<span class="sr-only">Not recorded</span></span>
                            </td>
                            <td class="n font-mono tabular-nums">
                                {{ stat(member.speed) }}<span v-if="cap(member.speed, member.speed_cap) !== null" class="text-xs font-normal text-ink-muted">{{ cap(member.speed, member.speed_cap) }}</span>
                            </td>
                            <td class="n font-mono tabular-nums">
                                {{ stat(member.stamina) }}<span v-if="cap(member.stamina, member.stamina_cap) !== null" class="text-xs font-normal text-ink-muted">{{ cap(member.stamina, member.stamina_cap) }}</span>
                            </td>
                            <td class="n font-mono tabular-nums">
                                {{ stat(member.power) }}<span v-if="cap(member.power, member.power_cap) !== null" class="text-xs font-normal text-ink-muted">{{ cap(member.power, member.power_cap) }}</span>
                            </td>
                            <td class="n font-mono tabular-nums">
                                {{ stat(member.guts) }}<span v-if="cap(member.guts, member.guts_cap) !== null" class="text-xs font-normal text-ink-muted">{{ cap(member.guts, member.guts_cap) }}</span>
                            </td>
                            <td class="n font-mono tabular-nums">
                                {{ stat(member.wit) }}<span v-if="cap(member.wit, member.wit_cap) !== null" class="text-xs font-normal text-ink-muted">{{ cap(member.wit, member.wit_cap) }}</span>
                            </td>
                            <td>
                                <span v-if="member.distance_assignment !== null" class="pill pill-outline">
                                    {{ member.distance_assignment }}
                                </span>
                                <!-- Not "Unassigned". One nullable column holds both "the Trainer
                                     recorded this member outside every category" and "no category the
                                     client shows was transcribed", and the payload cannot say which.
                                     Naming the second as the first would be a distinction the backend
                                     has nowhere to persist (`UC3.5`). -->
                                <span v-else class="text-ink-muted">No category recorded</span>
                            </td>
                            <!-- UC3.2: member origin -->
                            <td>
                                <span v-if="member.origin !== null" class="pill pill-neutral">
                                    {{ member.origin }}
                                </span>
                                <span v-else class="text-ink-muted">Unknown</span>
                            </td>
                            <!-- UC3.2: exact support-card identity -->
                            <td>
                                <span v-if="member.support_card_id !== null">
                                    <a href="#" class="text-ink underline">
                                        Support Card {{ member.support_card_id }}
                                    </a>
                                </span>
                                <span v-else class="text-ink-muted">No support card</span>
                            </td>

                            <td>
                                <span v-if="member.is_ace" class="pill pill-accent">ACE</span>
                                <span v-if="member.is_trainee" class="pill pill-outline">Trainee</span>
                                <span v-if="!member.is_ace && !member.is_trainee" class="text-ink-muted">—<span class="sr-only">No flags</span></span>
                            </td>
                            <td class="whitespace-nowrap">
                                <BaseButton variant="secondary" size="sm" @click="openEditor(member)">
                                    Edit<span class="sr-only"> {{ member.name }}</span>
                                </BaseButton>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <p v-if="props.roster.members.length > 0 && rows.length === 0" class="mt-3 text-sm text-ink-muted">
                No recorded member matches these filters.
            </p>
        </template>

        <!-- The contextual member editor. `closedby="any"` lets Escape and the backdrop close it; the
             form is `method="dialog"` so the Cancel button closes without a handler and the focus
             return is the platform's. -->
        <dialog
            ref="dialog"
            closedby="any"
            class="m-auto w-full max-w-2xl rounded-lg border-rule bg-panel p-4 text-ink backdrop:bg-scrim"
            :aria-label="editing !== null ? `Edit ${editing.name}` : 'Edit team member'"
            @close="editorClosed"
        >
            <form v-if="editing !== null" class="flex flex-col gap-4" @submit.prevent="saveEditor">
                <div class="flex items-start justify-between gap-3">
                    <div class="min-w-0">
                        <h3 class="text-lg font-semibold text-ink-strong">{{ editing.name }}</h3>
                        <p class="text-xs text-ink-muted">
                            Row {{ editing.position }} of the recorded roster. Saving writes this member
                            only, through <code>runs.roster.update</code>.
                        </p>
                    </div>
                    <BaseButton type="button" variant="secondary" size="sm" :disabled="rowBusy" @click="closeEditor">
                        Close
                    </BaseButton>
                </div>

                <fieldset class="flex flex-col gap-2">
                    <legend class="text-xs font-extrabold uppercase tracking-wide text-ink-muted">Identity</legend>
                    <div class="grid grid-cols-1 gap-3 @2xl:grid-cols-2">
                        <label class="flex flex-col gap-1">
                            <span class="text-ink-muted">Name</span>
                            <BaseInput
                                id="roster_edit_name"
                                v-model="draft.name"
                                name="name"
                                type="text"
                                maxlength="100"
                                required
                            />
                        </label>
                        <label class="flex flex-col gap-1">
                            <span class="text-ink-muted">Grade</span>
                            <BaseSelect id="roster_edit_grade" v-model="draft.grade" name="grade" :options="GRADE_OPTIONS" />
                        </label>
                    </div>
                    <p class="text-xs text-ink-muted">
                        {{ editing.umamusume_id === null
                            ? 'This member is recorded by name and is not linked to the catalogue, so no artwork or card identity is shown for her.'
                            : 'This member is linked to a catalogue entry.' }}
                    </p>
                </fieldset>

                <fieldset class="flex flex-col gap-2">
                    <legend class="text-xs font-extrabold uppercase tracking-wide text-ink-muted">Current attributes</legend>
                    <div class="grid grid-cols-2 gap-3 @2xl:grid-cols-5">
                        <label class="flex flex-col gap-1">
                            <span class="text-ink-muted">Speed</span>
                            <BaseInput id="roster_edit_speed" v-model="draft.speed" name="speed" type="number" min="0" max="2000" />
                        </label>
                        <label class="flex flex-col gap-1">
                            <span class="text-ink-muted">Stamina</span>
                            <BaseInput id="roster_edit_stamina" v-model="draft.stamina" name="stamina" type="number" min="0" max="2000" />
                        </label>
                        <label class="flex flex-col gap-1">
                            <span class="text-ink-muted">Power</span>
                            <BaseInput id="roster_edit_power" v-model="draft.power" name="power" type="number" min="0" max="2000" />
                        </label>
                        <label class="flex flex-col gap-1">
                            <span class="text-ink-muted">Guts</span>
                            <BaseInput id="roster_edit_guts" v-model="draft.guts" name="guts" type="number" min="0" max="2000" />
                        </label>
                        <label class="flex flex-col gap-1">
                            <span class="text-ink-muted">Wit</span>
                            <BaseInput id="roster_edit_wit" v-model="draft.wit" name="wit" type="number" min="0" max="2000" />
                        </label>
                    </div>
                    <p class="text-xs text-ink-muted">
                        These are the member's own stats as the client shows them. A blank field is a
                        reading this run has not recorded, and it stays blank rather than becoming zero.
                    </p>

                    <!-- The ceilings, beside the stats they bound (§1.9). Separate fields rather
                         than one combined value: a member standing on her ceiling and a member with
                         room left record the same stat number, and the cap is what tells them apart.
                         Blank means the ceiling was not read, not that there is no ceiling. -->
                    <p class="mt-1 text-ink-muted">Ceilings</p>
                    <div class="grid grid-cols-2 gap-3 @2xl:grid-cols-5">
                        <label class="flex flex-col gap-1">
                            <span class="text-ink-muted">Speed cap</span>
                            <BaseInput id="roster_edit_speed_cap" v-model="draft.speed_cap" name="speed_cap" type="number" min="0" max="2000" />
                        </label>
                        <label class="flex flex-col gap-1">
                            <span class="text-ink-muted">Stamina cap</span>
                            <BaseInput id="roster_edit_stamina_cap" v-model="draft.stamina_cap" name="stamina_cap" type="number" min="0" max="2000" />
                        </label>
                        <label class="flex flex-col gap-1">
                            <span class="text-ink-muted">Power cap</span>
                            <BaseInput id="roster_edit_power_cap" v-model="draft.power_cap" name="power_cap" type="number" min="0" max="2000" />
                        </label>
                        <label class="flex flex-col gap-1">
                            <span class="text-ink-muted">Guts cap</span>
                            <BaseInput id="roster_edit_guts_cap" v-model="draft.guts_cap" name="guts_cap" type="number" min="0" max="2000" />
                        </label>
                        <label class="flex flex-col gap-1">
                            <span class="text-ink-muted">Wit cap</span>
                            <BaseInput id="roster_edit_wit_cap" v-model="draft.wit_cap" name="wit_cap" type="number" min="0" max="2000" />
                        </label>
                    </div>
                </fieldset>

                <fieldset class="flex flex-col gap-2">
                    <legend class="text-xs font-extrabold uppercase tracking-wide text-ink-muted">Formation</legend>
                    <label class="flex flex-col gap-1">
                        <span class="text-ink-muted">Race category</span>
                        <BaseSelect
                            id="roster_edit_distance"
                            v-model="draft.distance_assignment"
                            name="distance_assignment"
                            :options="DISTANCE_OPTIONS"
                        />
                    </label>
                    <div class="flex flex-wrap items-center gap-4">
                        <BaseCheckbox id="roster_edit_ace" v-model="draft.is_ace" name="is_ace" label="ACE in this category" />
                        <BaseCheckbox id="roster_edit_trainee" v-model="draft.is_trainee" name="is_trainee" label="This member is the trainee" />
                    </div>
                    <p class="text-xs text-ink-muted">
                        One ACE per race category is the shape the client shows, and the server refuses a
                        second. Dirt is a category here even though it is a surface rather than a
                        distance band.
                    </p>
                </fieldset>

                <p v-if="rowError !== null" role="alert" class="rounded-md border border-risk bg-raised px-3 py-2 text-sm text-risk">
                    {{ rowError }}
                </p>

                <div class="flex flex-wrap items-center justify-between gap-3 border-t border-rule pt-3">
                    <div class="flex gap-2">
                        <BaseButton type="submit" variant="primary" size="sm" :loading="rowBusy">
                            {{ rowBusy ? 'Saving…' : 'Save member' }}
                        </BaseButton>
                        <BaseButton type="button" variant="secondary" size="sm" :disabled="rowBusy" @click="closeEditor">
                            Cancel
                        </BaseButton>
                    </div>
                    <!-- Remove, demoted from every row to the member it belongs to. The confirmation and
                         the server's own rules are unchanged; only its prominence moved. -->
                    <BaseButton type="button" variant="destructive" size="sm" :disabled="rowBusy" @click="removeMember">
                        Remove from roster
                    </BaseButton>
                </div>
            </form>
        </dialog>
    </section>
</template>
