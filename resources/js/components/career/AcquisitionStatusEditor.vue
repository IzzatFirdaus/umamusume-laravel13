<script setup lang="ts">
/*
 * The acquired/skipped status editor (F2, plan §9.6 ruling 5). A table of every skill the run
 * already has a state for, with the status picker inline. Submits through `runs.skills.sync`, the
 * same Form Request the 0.1.0 record screen used: a move of ownership, not a new route.
 *
 * A skill's acquisition status is distinct from the build-target priority list the rest of the
 * planner edits. The two writes POST to different routes and touch different columns, so neither
 * overwrites the other (ruling 5). The page passes the same four state groups it renders above; a
 * row with no catalogue `id` is a priority the catalogue has no row for and cannot be posted, so it
 * is not offered here.
 */
import { useForm } from '@inertiajs/vue3';
import { computed, nextTick, ref, watch } from 'vue';

interface PlanRow {
    id: number | null;
    name: string;
    is_unique: boolean;
    recorded: { status: string; turn: number | null } | null;
}

interface Group {
    key: string;
    label: string;
    glyph: string;
    rows: PlanRow[];
    absent: string;
}

const props = withDefaults(
    defineProps<{
        action: string;
        groups: Group[];
        options: { value: string; label: string }[];
    }>(),
    { options: () => [] },
);

// Only a catalogue-backed skill can be posted: the Form Request scopes `skill_id` to the Global
// catalogue, and a priority the catalogue cannot name carries a null id.
const rows = computed(() => props.groups.flatMap((group) => group.rows).filter((row): row is PlanRow & { id: number } => row.id !== null));

// The edit state is local until the save lands. Each row maps to its pending status and turn; the
// form posts only rows the Trainer actually changed, so revalidation cannot blast an untouched row.
const status = ref<Record<number, string>>({});
const turn = ref<Record<number, string>>({});

const touched = computed(() => Object.keys(status.value).map(Number));

const form = useForm<{ skills: Array<{ skill_id: string; status: string; turn_acquired: string }> }>({
    skills: [],
});

const alert = ref<HTMLElement | null>(null);

watch(
    () => form.hasErrors,
    async (hasErrors: boolean) => {
        if (!hasErrors) {
            return;
        }

        await nextTick();
        alert.value?.focus();
    },
);

function currentStatus(row: PlanRow & { id: number }): string {
    return status.value[row.id] ?? row.recorded?.status ?? props.options[0]?.value ?? '';
}

function currentTurn(row: PlanRow & { id: number }): string {
    return turn.value[row.id] ?? (row.recorded?.turn !== null && row.recorded?.turn !== undefined ? String(row.recorded.turn) : '');
}

function changeStatus(row: PlanRow & { id: number }, value: string): void {
    status.value[row.id] = value;
}

function changeTurn(row: PlanRow & { id: number }, value: string): void {
    turn.value[row.id] = value;
}

function save(): void {
    form.skills = rows.value
        .filter((row) => status.value[row.id] !== undefined)
        .map((row) => ({
            skill_id: String(row.id),
            status: currentStatus(row),
            turn_acquired: currentTurn(row),
        }));

    form.post(props.action, { preserveScroll: true });
}
</script>

<template>
    <section aria-labelledby="acquisition-status-heading" class="rounded-md border border-rule bg-panel p-4">
        <h2 id="acquisition-status-heading" class="text-base font-semibold text-ink-strong">Acquired / skipped</h2>
        <p class="mt-1 text-sm text-ink-muted">
            Mark whether this run learned each skill, skipped it, or is still suggesting it.
            This is separate from the priority order above; it records what the run has, not what
            the plan wants.
        </p>

        <p v-if="rows.length === 0" class="mt-3 rounded-md border border-dashed border-rule bg-raised p-3 text-sm text-ink">
            No skill with a catalogue row is recorded on this run yet, so there is no status to change.
        </p>

        <template v-else>
            <table class="mt-3 w-full table-auto text-sm">
                <thead>
                    <tr class="text-left text-ink-muted">
                        <th class="pb-2">Skill</th>
                        <th class="pb-2">Current status</th>
                        <th class="pb-2">Change to</th>
                        <th class="pb-2">Turn acquired</th>
                    </tr>
                </thead>
                <tbody>
                    <tr v-for="row in rows" :key="row.id" class="border-t border-rule">
                        <td class="py-2">
                            <span class="font-semibold text-ink-strong">{{ row.name }}</span>
                            <span v-if="row.is_unique" class="ml-1.5 text-xs text-ink-muted">Unique</span>
                        </td>
                        <td class="py-2">{{ row.recorded?.status ?? 'Not set' }}</td>
                        <td class="py-2">
                            <label :for="`skill-${row.id}-status`" class="sr-only">Change status for {{ row.name }}</label>
                            <select
                                :id="`skill-${row.id}-status`"
                                :value="currentStatus(row)"
                                class="min-h-11 w-full rounded-md border border-rule bg-raised px-2 text-ink"
                                @change="changeStatus(row, ($event.target as HTMLSelectElement).value)"
                            >
                                <option v-for="option in props.options" :key="option.value" :value="option.value">{{ option.label }}</option>
                            </select>
                        </td>
                        <td class="py-2">
                            <label :for="`skill-${row.id}-turn`" class="sr-only">Turn acquired for {{ row.name }}</label>
                            <input
                                :id="`skill-${row.id}-turn`"
                                type="number"
                                min="1"
                                :value="currentTurn(row)"
                                class="h-11 w-16 rounded-md border border-rule bg-raised px-2 text-ink"
                                @input="changeTurn(row, ($event.target as HTMLInputElement).value)"
                            />
                        </td>
                    </tr>
                </tbody>
            </table>

            <div
                v-if="form.hasErrors"
                ref="alert"
                role="alert"
                tabindex="-1"
                class="mt-3 rounded-md border border-risk bg-raised p-3 text-sm text-risk"
            >
                <p class="font-semibold">Some skill statuses were not saved.</p>
                <ul class="mt-1 list-disc pl-5">
                    <li v-for="(message, key) in form.errors" :key="key">{{ message }}</li>
                </ul>
            </div>

            <button
                type="button"
                class="enamel mt-4 inline-flex min-h-11 items-center rounded-full bg-chrome px-5 font-bold text-on-chrome disabled:opacity-60"
                :disabled="touched.length === 0 || form.processing"
                @click="save"
            >
                {{ form.processing ? 'Saving…' : 'Save acquisition status' }}
            </button>
        </template>
    </section>
</template>
