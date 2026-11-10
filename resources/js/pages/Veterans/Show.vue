<script setup lang="ts">
/*
 * SCREEN-021's detail surface (`veteran-detail.html`). One banked career, read on its own.
 *
 * **The shape is the wireframe's: a two-column band of cards, the stats as `srow` rows, and the
 * explanatory sentences as `note` blocks.** It was a single stacked panel of definition lists before,
 * which printed the same facts in a shape the parity harness could not resolve to any wireframe
 * marker. Nothing was added by the change and nothing was dropped: the record facts, the five final
 * totals, the tags, the notes and the two parents are all still here, in the same order.
 *
 * **There is no Factors card, and that is a rule rather than a gap.** The wireframe leads its left
 * column with a Family / Spark / Grade / Carries table. A per-spark grade is derived from the stored
 * sparks, and `ADR-0020` §3 with `PRD` FR-G-4 keeps derivation out of this library: it records and
 * searches what the Trainer entered. `VeteranRow` says the same in its own docblock and carries counts
 * only. The absence is stated on the page, under "Not in this build", which is where the register
 * already puts it.
 */
import AppLayout from '../../layouts/AppLayout.vue';
import ArtworkSlot from '../../components/ArtworkSlot.vue';
import PageHeader from '../../components/ui/PageHeader.vue';
import { Head } from '@inertiajs/vue3';
import { computed } from 'vue';

interface Veteran {
    id: number;
    run_id: number;
    trainee: string;
    trainee_ja: string | null;
    scenario_label: string;
    status_label: string;
    saved_at: string | null;
    tags: string[];
    notes: string | null;
    hasSelection: boolean;
    builder_url: string | null;
    veteran_url: string;
}

interface Absence {
    label: string;
    reason: string;
}

const props = defineProps<{
    veteran: Veteran;
    career: { turns: number; energy: number | null; fans: number | null };
    /** The five final stats from the run's last logged turn; null where no turn recorded one. */
    stats: Record<string, number | null>;
    counts: { skills: number; races: number };
    parents: { a: string | null; b: string | null };
    runUrl: string;
    /** `route('veterans.index')`, the back door. */
    libraryUrl: string;
    /** `route('database.sources')`, the ledger the filing timestamp came from. */
    sourcesUrl: string;
    traineeUrl: string;
    artworkURL: string | null;
    notice: string;
    absences: Absence[];
}>();

const doorClass =
    'inline-flex min-h-11 items-center rounded-md border border-rule px-3 text-sm font-medium text-ink hover:bg-raised hover:text-ink-strong';

/**
 * The head band's sentence. It carries the saved date the wireframe prints because the library row's
 * own timestamp holds it (`VeteranRow::saved_at`); an earlier note here claimed no column carried that
 * date, which was true of the run and false of the filing.
 */
const lede = computed((): string => {
    const saved = props.veteran.saved_at === null ? '' : ` · saved ${props.veteran.saved_at}`;

    return `${props.veteran.scenario_label}${saved} · from run ${props.veteran.run_id}. `
        + 'The record as it was banked, and the two parents it was built from.';
});

/** Every final total is null when no turn was logged, which is one sentence rather than five. */
const noStatsRecorded = computed((): boolean =>
    Object.values(props.stats).every((value) => value === null),
);
</script>

<template>
    <AppLayout>
        <Head :title="`Veteran: ${props.veteran.trainee}`" />

        <template #who>
            <!-- The `#who` slot contract (AppLayout, S8): one pill, the fact the page is about. Every
                 row on this screen is a filed career, so "Banked" is never a claim about a state the
                 record might not be in. -->
            <span class="pill pill-positive" data-shell-banked-pill>Banked</span>
        </template>

        <PageHeader eyebrow="Veteran" :title="props.veteran.trainee" :title-ja="props.veteran.trainee_ja ?? undefined" :description="lede">
            <template #actions>
                <a :href="props.libraryUrl" :class="doorClass">Back to library</a>
                <a :href="props.runUrl" :class="`${doorClass} bg-primary-action font-semibold text-on-primary-action`">
                    Open the Cockpit
                </a>
            </template>
        </PageHeader>

        <div class="mt-4 grid gap-4 lg:grid-cols-2">
            <div class="min-w-0">
                <section aria-labelledby="veteran-record" class="card">
                    <h2 id="veteran-record" class="text-lg font-semibold text-ink-strong">
                        The Record
                    </h2>

                    <dl class="mt-3 grid gap-x-6 gap-y-3 text-sm @3xl:grid-cols-2">
                        <div>
                            <dt class="text-xs text-ink-muted">Trainee</dt>
                            <dd class="text-ink-strong">
                                <div class="flex items-center gap-3">
                                    <ArtworkSlot :url="props.artworkURL" alt="" size="size-12" reserve />
                                    <span>
                                        <a :href="props.traineeUrl" class="font-semibold hover:underline transition-colors duration-150 ease-out">
                                            {{ props.veteran.trainee }}
                                        </a>
                                        <span
                                            v-if="props.veteran.trainee_ja !== null && props.veteran.trainee_ja !== props.veteran.trainee"
                                            lang="ja"
                                            class="ml-2 text-ink-muted"
                                        >
                                            {{ props.veteran.trainee_ja }}
                                        </span>
                                    </span>
                                </div>
                            </dd>
                        </div>

                        <div>
                            <dt class="text-xs text-ink-muted">Scenario</dt>
                            <dd class="text-ink-strong">{{ props.veteran.scenario_label }}</dd>
                        </div>

                        <div>
                            <dt class="text-xs text-ink-muted">Status</dt>
                            <dd class="text-ink-strong">{{ props.veteran.status_label }}</dd>
                        </div>

                        <div>
                            <dt class="text-xs text-ink-muted">Turns logged</dt>
                            <dd class="text-ink-strong">{{ props.career.turns }}</dd>
                        </div>

                        <div>
                            <dt class="text-xs text-ink-muted">Energy at the last logged turn</dt>
                            <dd class="text-ink-strong">
                                <span v-if="props.career.energy !== null">{{ props.career.energy }}</span>
                                <span v-else>
                                    N/A <span class="text-xs text-ink-muted">· no turn carries an Energy figure</span>
                                </span>
                            </dd>
                        </div>

                        <div>
                            <dt class="text-xs text-ink-muted">Fans at the last logged turn</dt>
                            <dd class="text-ink-strong">
                                <span v-if="props.career.fans !== null">{{ props.career.fans }}</span>
                                <span v-else>
                                    N/A <span class="text-xs text-ink-muted">· no turn carries a Fans figure</span>
                                </span>
                            </dd>
                        </div>

                        <div>
                            <!-- Counted, not filtered, the same words the compare surface uses: the pivot holds
                                 Suggested and Skipped rows and `race_entries` holds NotOffered and Skipped rows,
                                 so these are the records a career carries rather than the skills it earned or
                                 the races it ran. "Learned" overstated the number one screen away from the
                                 surface that explains why. -->
                            <dt class="text-xs text-ink-muted">Skills recorded</dt>
                            <dd class="text-ink-strong">{{ props.counts.skills }}</dd>
                        </div>

                        <div>
                            <dt class="text-xs text-ink-muted">Races recorded</dt>
                            <dd class="text-ink-strong">{{ props.counts.races }}</dd>
                        </div>
                    </dl>

                    <div class="mt-3 text-sm">
                        <p class="text-xs text-ink-muted">Tags</p>
                        <p v-if="props.veteran.tags.length > 0" class="mt-1 flex flex-wrap gap-1 text-xs">
                            <span
                                v-for="tag in props.veteran.tags"
                                :key="tag"
                                class="rounded-full bg-sunken px-2 py-0.5 text-ink-strong"
                            >
                                {{ tag }}
                            </span>
                        </p>
                        <p v-else class="mt-1 text-sm">
                            <span title="No tag was recorded on this Veteran.">N/A</span>
                            <span class="text-ink-muted"> tagged: no tag was recorded on this Veteran</span>
                        </p>
                    </div>

                    <div class="mt-3 text-sm">
                        <p class="text-xs text-ink-muted">Notes</p>
                        <p v-if="props.veteran.notes !== null" class="mt-1 text-ink">{{ props.veteran.notes }}</p>
                        <p v-else class="mt-1 text-sm">
                            <span title="No note was recorded on this Veteran.">N/A</span> <span class="text-xs text-ink-muted">· no note was recorded</span>
                        </p>
                    </div>
                </section>

                <!-- The five final stats as the wireframe draws them: one `srow` per stat, in the
                     client's own order. A career with no logged turn prints the absence in words per
                     row rather than five zeros, because a zero reads as a finishing total nobody
                     recorded (veteran-detail annotation 2). -->
                <section aria-labelledby="veteran-stats" class="card mt-4">
                    <h2 id="veteran-stats" class="text-lg font-semibold text-ink-strong">Final stats</h2>

                    <div
                        v-for="(value, stat) in props.stats"
                        :key="stat"
                        class="srow"
                    >
                        <span class="k">{{ stat }}</span>
                        <span class="v mono">
                            <template v-if="value !== null">{{ value }}</template>
                            <span v-else class="text-xs font-normal text-ink-muted">Not recorded</span>
                        </span>
                    </div>

                    <p v-if="noStatsRecorded" class="note">
                        No turn is logged on this career, so none of the five stats has a recorded figure.
                    </p>
                </section>
            </div>

            <div class="min-w-0">
                <section aria-labelledby="veteran-parents" class="card">
                    <h2 id="veteran-parents" class="text-lg font-semibold text-ink-strong">
                        Built from
                    </h2>
                    <p class="note">
                        The two inheritance parents the run recorded. Their own two ancestors are on the
                        Legacy Lab, which prints the six-node graph; this screen names the parents and
                        leaves the graph to the one surface that renders it.
                    </p>
                    <dl class="mt-3 grid gap-x-6 gap-y-3 text-sm @3xl:grid-cols-2">
                        <div>
                            <dt class="text-xs text-ink-muted">Parent A</dt>
                            <dd class="text-ink-strong">
                                <span v-if="props.parents.a !== null">{{ props.parents.a }}</span>
                                <span v-else>N/A, not recorded</span>
                            </dd>
                        </div>
                        <div>
                            <dt class="text-xs text-ink-muted">Parent B</dt>
                            <dd class="text-ink-strong">
                                <span v-if="props.parents.b !== null">{{ props.parents.b }}</span>
                                <span v-else>N/A, not recorded</span>
                            </dd>
                        </div>
                    </dl>
                    <a v-if="props.veteran.builder_url" :href="props.veteran.builder_url" :class="doorClass" class="mt-3">
                        Legacy Lab
                    </a>
                    <p v-else class="note">
                        This run has no Legacy read-back recorded, so there is nothing to open in the builder.
                    </p>
                </section>

                <!-- Where the record came from, in the two figures the row actually carries: the run it
                     was filed from and the day it was filed. The wireframe's Provenance block also
                     states when the factors were banked; this build holds no factor inventory to date,
                     so that row is the same derivation the "Not in this build" card names. -->
                <section aria-labelledby="veteran-provenance" class="card mt-4">
                    <h2 id="veteran-provenance" class="text-lg font-semibold text-ink-strong">
                        Provenance
                    </h2>
                    <dl class="mt-3 grid gap-x-6 gap-y-3 text-sm @3xl:grid-cols-2">
                        <div>
                            <dt class="text-xs text-ink-muted">Source run</dt>
                            <dd class="text-ink-strong">
                                <a :href="props.runUrl" class="font-mono hover:underline transition-colors duration-150 ease-out">
                                    run {{ props.veteran.run_id }}
                                </a>
                            </dd>
                        </div>
                        <div>
                            <dt class="text-xs text-ink-muted">Filed</dt>
                            <dd class="text-ink-strong">
                                <span v-if="props.veteran.saved_at !== null" class="font-mono">{{ props.veteran.saved_at }}</span>
                                <span v-else>N/A, the library row carries no filing timestamp</span>
                            </dd>
                        </div>
                    </dl>
                    <a :href="props.sourcesUrl" :class="doorClass" class="mt-3">Source ledger</a>
                </section>

                <!-- UX-19: the same footnote treatment the save screen and the compare screen take. The
                     list stays honest and reachable; the first viewport belongs to the Veteran's own
                     facts. -->
                <details class="card mt-4">
                    <summary id="veteran-absences" class="flex min-h-11 cursor-pointer items-center text-lg font-semibold text-ink-strong">
                        Not in this build
                    </summary>
                    <ul class="mt-2 space-y-2 text-sm text-ink-muted">
                        <li v-for="absence in props.absences" :key="absence.label">
                            <span class="text-ink">{{ absence.label }}</span>: {{ absence.reason }}
                        </li>
                    </ul>
                </details>
            </div>
        </div>
    </AppLayout>
</template>
