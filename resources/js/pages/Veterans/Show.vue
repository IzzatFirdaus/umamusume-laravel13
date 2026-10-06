<script setup lang="ts">
import AppLayout from '../../layouts/AppLayout.vue';
import { Head } from '@inertiajs/vue3';

interface Veteran {
    id: number;
    run_id: number;
    trainee: string;
    trainee_ja: string | null;
    scenario_label: string;
    status_label: string;
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
    counts: { skills: number; races: number };
    parents: { a: string | null; b: string | null };
    runUrl: string;
    traineeUrl: string;
    notice: string;
    absences: Absence[];
}>();

const doorClass =
    'inline-flex min-h-11 items-center rounded-md border border-rule px-3 text-sm font-medium text-ink hover:bg-raised hover:text-ink-strong';
</script>

<template>
    <AppLayout>
        <Head :title="`Veteran: ${props.veteran.trainee}`" />
        <template #title>Veteran: {{ props.veteran.trainee }}</template>

        <p class="text-sm text-ink-muted" role="note">{{ props.notice }}</p>

        <section aria-labelledby="veteran-record" class="mt-4 rounded-md border border-rule bg-raised p-4">
            <h2 id="veteran-record" class="text-xs font-bold tracking-wide text-ink-muted">
                THE RECORD
            </h2>

            <dl class="mt-3 grid gap-x-6 gap-y-3 text-sm sm:grid-cols-2">
                <div>
                    <dt class="text-xs text-ink-muted">Trainee</dt>
                    <dd class="text-ink-strong">
                        <a :href="props.traineeUrl" class="font-semibold hover:underline">
                            {{ props.veteran.trainee }}
                        </a>
                        <span
                            v-if="props.veteran.trainee_ja !== null && props.veteran.trainee_ja !== props.veteran.trainee"
                            lang="ja"
                            class="ml-2 text-ink-muted"
                        >
                            {{ props.veteran.trainee_ja }}
                        </span>
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
                        <span v-else title="No turn carries an end-of-turn Energy figure on this record.">
                            N/A
                        </span>
                    </dd>
                </div>

                <div>
                    <dt class="text-xs text-ink-muted">Fans at the last logged turn</dt>
                    <dd class="text-ink-strong">
                        <span v-if="props.career.fans !== null">{{ props.career.fans }}</span>
                        <span v-else title="No turn carries an end-of-turn Fans figure on this record.">
                            N/A
                        </span>
                    </dd>
                </div>

                <div>
                    <dt class="text-xs text-ink-muted">Skills learned</dt>
                    <dd class="text-ink-strong">{{ props.counts.skills }}</dd>
                </div>

                <div>
                    <dt class="text-xs text-ink-muted">Races entered</dt>
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
                        title="A tag you typed on this record. Tags are the Trainer's own words, not client vocabulary."
                    >
                        {{ tag }}
                    </span>
                </p>
                <p v-else class="mt-1 text-sm">
                    <span title="No tag was recorded on this Veteran.">N/A</span>
                    <span class="text-ink-muted"> tagged</span>
                </p>
            </div>

            <div class="mt-3 text-sm">
                <p class="text-xs text-ink-muted">Notes</p>
                <p v-if="props.veteran.notes !== null" class="mt-1 text-ink">{{ props.veteran.notes }}</p>
                <p v-else class="mt-1 text-sm">
                    <span title="No note was recorded on this Veteran.">N/A</span>
                </p>
            </div>
        </section>

        <section aria-labelledby="veteran-parents" class="mt-4 rounded-md border border-rule bg-raised p-4">
            <h2 id="veteran-parents" class="text-xs font-bold tracking-wide text-ink-muted">
                BUILT FROM
            </h2>
            <p class="mt-1 text-xs text-ink-muted">
                The two inheritance parents the run recorded. Their own two ancestors are on the Legacy
                Lab, which prints the six-node graph; this screen names the parents and leaves the graph
                to the one surface that renders it.
            </p>
            <dl class="mt-3 grid gap-x-6 gap-y-3 text-sm sm:grid-cols-2">
                <div>
                    <dt class="text-xs text-ink-muted">Parent A</dt>
                    <dd class="text-ink-strong">
                        <span v-if="props.parents.a !== null">{{ props.parents.a }}</span>
                        <span v-else title="The run records no Parent A.">N/A, not recorded</span>
                    </dd>
                </div>
                <div>
                    <dt class="text-xs text-ink-muted">Parent B</dt>
                    <dd class="text-ink-strong">
                        <span v-if="props.parents.b !== null">{{ props.parents.b }}</span>
                        <span v-else title="The run records no Parent B.">N/A, not recorded</span>
                    </dd>
                </div>
            </dl>
        </section>

        <div class="mt-4 flex flex-wrap gap-2">
            <a :href="props.runUrl" :class="`enamel ${doorClass} bg-chrome font-semibold text-on-chrome`">
                Open the career record
            </a>
            <a v-if="props.veteran.builder_url" :href="props.veteran.builder_url" :class="doorClass">
                Legacy Lab
            </a>
            <a
                v-else
                href="/veterans"
                :class="doorClass"
                title="This run has no Legacy read-back recorded, so there is nothing to open in the builder."
            >
                Back to the library
            </a>
        </div>

        <section aria-labelledby="veteran-absences" class="mt-8">
            <h2 id="veteran-absences" class="text-xs font-bold tracking-wide text-ink-muted">
                NOT IN THIS BUILD
            </h2>
            <ul class="mt-2 space-y-2 text-sm text-ink-muted">
                <li v-for="absence in props.absences" :key="absence.label">
                    <span class="text-ink">{{ absence.label }}</span>: {{ absence.reason }}
                </li>
            </ul>
        </section>
    </AppLayout>
</template>
