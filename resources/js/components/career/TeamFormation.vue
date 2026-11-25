<script setup lang="ts">
/*
 * The team's five race-category columns (`UC2.3`): who the run fields where, with each category's
 * ACE called out.
 *
 * **The columns are the client's own five**, Sprint, Mile, Medium, Long and Dirt
 * (`config('scenarios.scenarios.*.team_race.categories')`, `§1.8`). Dirt is a surface rather than a
 * distance band, which is why this does not read `BuildTargetPayload::DISTANCE_BANDS`: keying the
 * board off four bands would drop a fifth of the race it describes.
 *
 * **Four states, and they are not the same fact.** A category whose ACE the roster records says so.
 * A category with members but no ACE is "no ACE crowned". A category with no members at all is
 * "no member recorded". And the board never calls an undocumented slot empty: the client shows a
 * three-slot column, and a category this run recorded nothing for may still hold members in the
 * game that were never transcribed. The distinction is printed, not collapsed into a blank.
 *
 * **Nothing here is derived.** No ACE is chosen, no member is moved, and no readiness is scored: the
 * assignments are exactly the roster's own `distance_assignment` and `is_ace` values. A member may
 * be recorded with no category, which is the "Unassigned" row rather than a sixth column.
 *
 * **No drag and drop.** Reassigning is the member editor's job, through `runs.roster.update`, which
 * is the one write for a member's column. A drag handle here would be a second write path with no
 * validation behind it.
 */
import type { RosterMemberRow } from './RosterTableRows';

const props = defineProps<{
    /**
     * The scenario's own board, from `TeamPanelBuilder::teamCoverageSection()`: one row per published
     * category with the ask the guides state. Reading the categories from here rather than restating
     * them keeps a fifth scenario's set in its own config, and it is the same list the scenario page's
     * coverage table reads.
     */
    coverage: { rows: { category: string; asks: string }[] } | null;
    /** The recorded roster, already ordered by `position`. */
    members: RosterMemberRow[];
}>();

/** `"3 fielded"` → 3, or null when the matrix states no figure. */
const fieldedPerCategory = (asks: string): number | null => {
    const match = asks.match(/\d+/);

    return match === null ? null : Number(match[0]);
};

const categories = (coverage: { rows: { category: string; asks: string }[] } | null): string[] =>
    (coverage?.rows ?? []).map((row) => row.category);

const inCategory = (members: RosterMemberRow[], category: string): RosterMemberRow[] =>
    members.filter((member) => member.distance_assignment === category);

const aceOf = (members: RosterMemberRow[], category: string): RosterMemberRow | null =>
    members.find((member) => member.distance_assignment === category && member.is_ace) ?? null;

const others = (members: RosterMemberRow[], category: string): RosterMemberRow[] =>
    members.filter((member) => member.distance_assignment === category && !member.is_ace);

/** The members no category was recorded for. A recorded member without a column is not "unowned". */
const unassigned = (members: RosterMemberRow[]): RosterMemberRow[] =>
    members.filter((member) => member.distance_assignment === null);
</script>

<template>
    <section aria-labelledby="team-formation-heading" class="card">
        <h2 id="team-formation-heading" class="text-lg font-semibold text-ink-strong">Team formation</h2>
        <p class="sub">
            The categories a Unity Cup team race runs, and which of this run's recorded members stand
            in each. The ACE is the member the client crowns in a column; one per category.
            <template v-if="props.coverage?.rows?.[0]">
                {{ props.coverage.rows[0].asks }} in each.
            </template>
        </p>

        <!-- Five columns on a wide canvas, folding to two and then one. `@container` on the grid's own
             wrapper rather than a viewport query, because the panel's width is the thing that decides
             whether five columns fit (`main` is already the container). -->
        <div class="@container mt-3">
            <div class="grid gap-2 @2xl:grid-cols-3 @4xl:grid-cols-5">
                <div
                    v-for="category in categories(props.coverage)"
                    :key="category"
                    class="rounded-md border border-rule bg-raised p-2"
                >
                    <h3 class="flex items-baseline justify-between gap-2 text-xs font-extrabold uppercase tracking-wide text-ink-muted">
                        {{ category }}
                        <span
                            v-if="props.coverage !== null && fieldedPerCategory(props.coverage.rows.find(row => row.category === category)?.asks ?? '') !== null"
                            class="font-mono normal-case tracking-normal"
                        >{{ inCategory(props.members, category).length }}/{{ fieldedPerCategory(props.coverage.rows.find(row => row.category === category)!.asks) }}</span>
                    </h3>

                    <!-- The ACE slot. Named as a slot even when empty, so a reader sees the shape the
                         client draws rather than an absence with no context. -->
                    <div v-if="aceOf(props.members, category) !== null" class="mt-1.5">
                        <span class="pill pill-accent">ACE</span>
                        <p class="mt-0.5 text-sm font-semibold text-ink-strong">
                            {{ aceOf(props.members, category)!.name }}
                        </p>
                    </div>
                    <div v-else class="mt-1.5 rounded-sm border border-dashed border-rule px-2 py-1.5">
                        <span class="pill pill-outline">ACE</span>
                        <p class="mt-0.5 text-xs text-ink-muted">
                            {{ inCategory(props.members, category).length === 0
                                ? 'No member recorded in this category.'
                                : 'No ACE crowned in this category.' }}
                        </p>
                    </div>

                    <ul
                        v-if="others(props.members, category).length > 0"
                        class="mt-1.5 flex flex-col gap-0.5"
                        :aria-label="`Other members in ${category}`"
                    >
                        <li
                            v-for="member in others(props.members, category)"
                            :key="member.id"
                            class="flex items-baseline justify-between gap-2 text-xs"
                        >
                            <span class="min-w-0 truncate text-ink">{{ member.name }}</span>
                            <span v-if="member.grade !== null" class="font-mono text-ink-muted">{{ member.grade }}</span>
                        </li>
                    </ul>
                </div>
            </div>
        </div>

        <!-- Members the run recorded with no category. Printed rather than dropped: a member with no
             category is a real roster row, and hiding her would make the roster and the formation
             disagree about who is on the team. -->
        <div v-if="unassigned(props.members).length > 0" class="mt-3 rounded-md border border-rule bg-sunken p-2">
            <h3 class="text-xs font-extrabold uppercase tracking-wide text-ink-muted">
                No category recorded
                <span class="font-mono normal-case tracking-normal">({{ unassigned(props.members).length }})</span>
            </h3>
            <p class="mt-0.5 text-xs text-ink-muted">
                These members have no race category on the roster. One nullable column holds both
                "stands outside every category" and "the column was never transcribed", and this
                payload cannot say which, so neither is claimed here.
            </p>
            <ul class="mt-1 flex flex-wrap gap-x-3 gap-y-1 text-xs">
                <li v-for="member in unassigned(props.members)" :key="member.id" class="text-ink">
                    {{ member.name }}
                    <span v-if="member.is_trainee" class="pill pill-outline">Trainee</span>
                </li>
            </ul>
        </div>

        <p v-if="props.members.length === 0" class="mt-3 text-sm text-ink-muted">
            No team roster has been recorded on this run yet, so no category has a member or an ACE.
        </p>

        <p class="mt-2 text-xs text-ink-muted">
            A category this run recorded nothing for is not necessarily empty in the game: the roster
            is what was transcribed, and the client's own columns are not read back from it.
        </p>
    </section>
</template>
