<script setup lang="ts">
/*
 * The Unity Cup roster's write surface (D01).
 *
 * §1.8 of `docs/[Rosy_Dreams]Rice_Shower_Unity-Cup.md` is the client's own Team Info panel: `Members:
 * 20 / 20`, laid out as five distance columns with an ACE plus two more in each. This component is
 * the backend behind that panel — add, edit, remove — posting the `runs.roster.*` routes the
 * `RosterMemberController` owns. It never derives a member from the six-card deck: the deck and the
 * roster are different facts (see `RosterMember`'s docblock), and this reads only `roster_members`.
 *
 * **The prop is the contract.** `rosterSection()` sends `{members, empty, unrecorded}` and the write
 * URLs ride beside each row (`store_url`, per-member `update_url`/`destroy_url`). A run with no roster
 * rows is `unrecorded: true`, and the panel says so rather than fabricating a "0 / 20" roster. The
 * component is inert when no `roster` prop arrives at all (the page has not wired it yet), because a
 * write form with no submit target is a lie about what the server will accept.
 *
 * **Null-not-zero (D-220).** Every stat is blank until the Trainer states it; a blank posts `null`,
 * never `0`, because a member added by name has no reading yet and a fabricated stat is a claim
 * about a record that does not exist. Only `name` is required (`StoreRosterMemberRequest`).
 *
 * **One ACE per distance column** is the model's own rule (`RosterMember::booted()`); the request
 * catches it at the boundary with a message the form prints beside the field, so a rejected ACE does
 * not become a 500.
 */
import { reactive, ref } from 'vue';
import { router, useForm } from '@inertiajs/vue3';
import { BaseButton, BaseCheckbox, BaseInput, BaseSelect } from '../Form';

/** One roster row as `CockpitController::rosterSection()` sends it, plus its write URLs. */
interface RosterMemberRow {
    id: number;
    name: string;
    grade: string | null;
    speed: number | null;
    stamina: number | null;
    power: number | null;
    guts: number | null;
    wit: number | null;
    distance_assignment: string | null;
    is_ace: boolean;
    is_trainee: boolean;
    umamusume_id: number | null;
    position: number;
    update_url: string;
    destroy_url: string;
}

interface RosterPayload {
    members: RosterMemberRow[];
    empty: string | null;
    unrecorded: boolean;
    store_url: string;
    /** `RosterMember::MAX_MEMBERS` (20); the client's own ceiling (§1.8), not this tool's. */
    max_members: number;
}

const props = defineProps<{
    /** `rosterSection()` + write URLs, or null when the page has not wired the roster in yet. */
    roster: RosterPayload | null;
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

/* RosterDistance's five columns plus the unassigned blank; Dirt is a team column here and deliberately
 * not one of `BuildTargetPayload::DISTANCE_BANDS` (four, no Dirt) — see the enum's own note. */
const DISTANCE_OPTIONS = [
    { value: '', label: 'Unassigned' },
    { value: 'Sprint', label: 'Sprint' },
    { value: 'Mile', label: 'Mile' },
    { value: 'Medium', label: 'Medium' },
    { value: 'Long', label: 'Long' },
    { value: 'Dirt', label: 'Dirt' },
];

const MAX_MEMBERS = props.roster?.max_members ?? 20;

const text = (value: string | null): string => value ?? '';
const figure = (value: string): number | null => (value === '' ? null : Number(value));
const numberText = (value: number | null): string => (value === null ? '' : String(value));

/* ---- Add form -------------------------------------------------------------------------- */

const adding = ref(false);

const newMember = useForm({
    name: '',
    grade: '',
    speed: '',
    stamina: '',
    power: '',
    guts: '',
    wit: '',
    distance_assignment: '',
    is_ace: false,
    is_trainee: false,
});

function openAdd(): void {
    if (!props.roster || props.roster.members.length >= MAX_MEMBERS) {
        return;
    }
    adding.value = true;
    newMember.clearErrors();
    newMember.reset();
}

function submitAdd(): void {
    if (!props.roster) {
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
            distance_assignment: data.distance_assignment === '' ? null : data.distance_assignment,
            is_ace: data.is_ace,
            is_trainee: data.is_trainee,
        }))
        .post(props.roster.store_url, {
            preserveScroll: true,
            onSuccess: () => {
                adding.value = false;
                newMember.reset();
            },
        });
}

/* ---- Per-row edit ---------------------------------------------------------------------- */

/** The row currently open for editing, keyed by member id; null when no row is being edited. */
const editingId = ref<number | null>(null);
const draft = reactive<Record<string, string | boolean>>({});
const rowError = ref<string | null>(null);
const rowBusy = ref(false);

function startEdit(member: RosterMemberRow): void {
    editingId.value = member.id;
    rowError.value = null;
    draft.name = member.name;
    draft.grade = member.grade ?? '';
    draft.speed = numberText(member.speed);
    draft.stamina = numberText(member.stamina);
    draft.power = numberText(member.power);
    draft.guts = numberText(member.guts);
    draft.wit = numberText(member.wit);
    draft.distance_assignment = member.distance_assignment ?? '';
    draft.is_ace = member.is_ace;
    draft.is_trainee = member.is_trainee;
}

function cancelEdit(): void {
    editingId.value = null;
    rowError.value = null;
}

function saveEdit(member: RosterMemberRow): void {
    rowBusy.value = true;
    rowError.value = null;
    router.put(
        member.update_url,
        {
            name: String(draft.name).trim(),
            grade: draft.grade === '' ? null : draft.grade,
            speed: figure(String(draft.speed)),
            stamina: figure(String(draft.stamina)),
            power: figure(String(draft.power)),
            guts: figure(String(draft.guts)),
            wit: figure(String(draft.wit)),
            distance_assignment:
                draft.distance_assignment === '' ? null : draft.distance_assignment,
            is_ace: Boolean(draft.is_ace),
            is_trainee: Boolean(draft.is_trainee),
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
                editingId.value = null;
            },
        },
    );
}

function removeMember(member: RosterMemberRow): void {
    if (!confirm(`Remove "${member.name}" from the team roster? This cannot be undone.`)) {
        return;
    }
    rowBusy.value = true;
    router.delete(member.destroy_url, {
        preserveScroll: true,
        onFinish: () => {
            rowBusy.value = false;
        },
    });
}

/* ---- Presentation helpers -------------------------------------------------------------- */

function statLine(member: RosterMemberRow): string {
    const parts: string[] = [];
    const push = (label: string, value: number | null): void => {
        if (value !== null) {
            parts.push(`${label} ${value}`);
        }
    };
    push('SPD', member.speed);
    push('STA', member.stamina);
    push('PWR', member.power);
    push('GUT', member.guts);
    push('WIT', member.wit);
    return parts.length > 0 ? parts.join(' · ') : 'no stats recorded';
}

function distanceLabel(value: string | null): string {
    return value ?? 'Unassigned';
}

function flags(member: RosterMemberRow): string[] {
    const out: string[] = [];
    if (member.is_ace) {
        out.push('ACE');
    }
    if (member.is_trainee) {
        out.push('Trainee');
    }
    return out;
}
</script>

<template>
    <div v-if="props.roster === null" class="rounded-md border border-rule bg-sunken p-3 text-sm text-ink-muted">
        <p class="font-semibold text-ink-strong">Roster unavailable on this screen</p>
        <p class="mt-1">
            The team page has not been given the roster yet. The write routes exist
            (<code>runs.roster.store/update/destroy</code>); the page must pass
            <code>rosterSection()</code> and its action URLs for the panel below to function.
        </p>
    </div>

    <div v-else class="flex flex-col gap-3 text-sm">
        <div class="flex flex-wrap items-center justify-between gap-2">
            <p class="text-ink-muted">
                <span class="font-mono tabular-nums text-ink-strong">
                    {{ props.roster.members.length }} / {{ MAX_MEMBERS }}
                </span>
                members recorded.
            </p>
            <BaseButton
                v-if="!adding && props.roster.members.length < MAX_MEMBERS"
                variant="secondary"
                size="sm"
                @click="openAdd"
            >
                Add member
            </BaseButton>
            <span v-else-if="props.roster.members.length >= MAX_MEMBERS" class="text-xs text-ink-muted">
                Roster full ({{ MAX_MEMBERS }}).
            </span>
        </div>

        <p v-if="props.roster.unrecorded" class="text-ink-muted">
            No team roster has been recorded on this run yet. Add members below to build it.
        </p>

        <!-- Add form -->
        <form
            v-if="adding"
            class="grid grid-cols-1 gap-3 rounded-md border border-rule bg-raised p-3 @2xl:grid-cols-2"
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
                <span class="text-ink-muted">Distance</span>
                <BaseSelect
                    id="roster_new_distance"
                    v-model="newMember.distance_assignment"
                    name="distance_assignment"
                    :options="DISTANCE_OPTIONS"
                    :error="newMember.errors.distance_assignment"
                />
            </label>
            <label class="flex flex-col gap-1">
                <span class="text-ink-muted">Speed</span>
                <BaseInput id="roster_new_speed" v-model="newMember.speed" name="speed" type="number" min="0" max="2000" :error="newMember.errors.speed" />
            </label>
            <label class="flex flex-col gap-1">
                <span class="text-ink-muted">Stamina</span>
                <BaseInput id="roster_new_stamina" v-model="newMember.stamina" name="stamina" type="number" min="0" max="2000" :error="newMember.errors.stamina" />
            </label>
            <label class="flex flex-col gap-1">
                <span class="text-ink-muted">Power</span>
                <BaseInput id="roster_new_power" v-model="newMember.power" name="power" type="number" min="0" max="2000" :error="newMember.errors.power" />
            </label>
            <label class="flex flex-col gap-1">
                <span class="text-ink-muted">Guts</span>
                <BaseInput id="roster_new_guts" v-model="newMember.guts" name="guts" type="number" min="0" max="2000" :error="newMember.errors.guts" />
            </label>
            <label class="flex flex-col gap-1">
                <span class="text-ink-muted">Wit</span>
                <BaseInput id="roster_new_wit" v-model="newMember.wit" name="wit" type="number" min="0" max="2000" :error="newMember.errors.wit" />
            </label>
            <div class="flex flex-wrap items-center gap-4 @2xl:col-span-2">
                <BaseCheckbox id="roster_new_ace" v-model="newMember.is_ace" name="is_ace" label="ACE" :error="newMember.errors.is_ace" />
                <BaseCheckbox id="roster_new_trainee" v-model="newMember.is_trainee" name="is_trainee" label="Trainee" :error="newMember.errors.is_trainee" />
            </div>
            <p v-if="newMember.errors.name && props.roster && props.roster.members.length >= MAX_MEMBERS" class="text-xs text-risk @2xl:col-span-2">
                {{ newMember.errors.name }}
            </p>
            <div class="flex gap-2 @2xl:col-span-2">
                <BaseButton type="submit" variant="primary" size="sm" :loading="newMember.processing">
                    {{ newMember.processing ? 'Adding…' : 'Add member' }}
                </BaseButton>
                <BaseButton type="button" variant="secondary" size="sm" @click="adding = false">Cancel</BaseButton>
            </div>
        </form>

        <!-- Member list -->
        <ul v-if="props.roster.members.length > 0" class="flex flex-col gap-2">
            <li
                v-for="member in props.roster.members"
                :key="member.id"
                class="rounded-md border border-rule bg-panel p-3"
            >
                <!-- Read mode -->
                <div v-if="editingId !== member.id" class="flex flex-wrap items-start justify-between gap-2">
                    <div class="min-w-0">
                        <div class="flex flex-wrap items-center gap-2">
                            <span class="font-semibold text-ink-strong">{{ member.name }}</span>
                            <span v-if="member.grade" class="pill pill-neutral">{{ member.grade }}</span>
                            <span class="pill pill-outline">{{ distanceLabel(member.distance_assignment) }}</span>
                            <span v-for="flag in flags(member)" :key="flag" class="pill pill-accent">{{ flag }}</span>
                        </div>
                        <p class="mt-1 font-mono tabular-nums text-xs text-ink-muted">{{ statLine(member) }}</p>
                    </div>
                    <div class="flex shrink-0 gap-2">
                        <BaseButton variant="secondary" size="sm" :disabled="rowBusy" @click="startEdit(member)">Edit</BaseButton>
                        <BaseButton variant="destructive" size="sm" :disabled="rowBusy" @click="removeMember(member)">Remove</BaseButton>
                    </div>
                </div>

                <!-- Edit mode -->
                <form v-else class="grid grid-cols-1 gap-3 @2xl:grid-cols-2" @submit.prevent="saveEdit(member)">
                    <label class="flex flex-col gap-1">
                        <span class="text-ink-muted">Name</span>
                        <BaseInput id="roster_edit_name" v-model="draft.name as string" name="name" type="text" maxlength="100" required />
                    </label>
                    <label class="flex flex-col gap-1">
                        <span class="text-ink-muted">Grade</span>
                        <BaseSelect id="roster_edit_grade" v-model="draft.grade as string" name="grade" :options="GRADE_OPTIONS" />
                    </label>
                    <label class="flex flex-col gap-1">
                        <span class="text-ink-muted">Distance</span>
                        <BaseSelect id="roster_edit_distance" v-model="draft.distance_assignment as string" name="distance_assignment" :options="DISTANCE_OPTIONS" />
                    </label>
                    <label class="flex flex-col gap-1">
                        <span class="text-ink-muted">Speed</span>
                        <BaseInput id="roster_edit_speed" v-model="draft.speed as string" name="speed" type="number" min="0" max="2000" />
                    </label>
                    <label class="flex flex-col gap-1">
                        <span class="text-ink-muted">Stamina</span>
                        <BaseInput id="roster_edit_stamina" v-model="draft.stamina as string" name="stamina" type="number" min="0" max="2000" />
                    </label>
                    <label class="flex flex-col gap-1">
                        <span class="text-ink-muted">Power</span>
                        <BaseInput id="roster_edit_power" v-model="draft.power as string" name="power" type="number" min="0" max="2000" />
                    </label>
                    <label class="flex flex-col gap-1">
                        <span class="text-ink-muted">Guts</span>
                        <BaseInput id="roster_edit_guts" v-model="draft.guts as string" name="guts" type="number" min="0" max="2000" />
                    </label>
                    <label class="flex flex-col gap-1">
                        <span class="text-ink-muted">Wit</span>
                        <BaseInput id="roster_edit_wit" v-model="draft.wit as string" name="wit" type="number" min="0" max="2000" />
                    </label>
                    <div class="flex flex-wrap items-center gap-4 @2xl:col-span-2">
                        <BaseCheckbox id="roster_edit_ace" v-model="draft.is_ace as boolean" name="is_ace" label="ACE" />
                        <BaseCheckbox id="roster_edit_trainee" v-model="draft.is_trainee as boolean" name="is_trainee" label="Trainee" />
                    </div>
                    <p v-if="rowError" role="alert" class="text-xs text-risk @2xl:col-span-2">{{ rowError }}</p>
                    <div class="flex gap-2 @2xl:col-span-2">
                        <BaseButton type="submit" variant="primary" size="sm" :loading="rowBusy">
                            {{ rowBusy ? 'Saving…' : 'Save member' }}
                        </BaseButton>
                        <BaseButton type="button" variant="secondary" size="sm" :disabled="rowBusy" @click="cancelEdit">Cancel</BaseButton>
                    </div>
                </form>
            </li>
        </ul>
    </div>
</template>