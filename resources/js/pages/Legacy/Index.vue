<script setup lang="ts">
/*
 * The Legacy Lab, saved legacies (`legacy.html`, owner ruling 2026-10-28: this page is the
 * wireframe's saved-legacies page; the builder stays at `/legacy/{run}` and the wizard's Legacy step).
 *
 * The unit is the pair, not the Veteran row: two parents and the sparks they pass on are one stored
 * object (`training_runs.legacy_selection`, ADR-0010), so the table's rows are runs that carry a
 * selection. The right column carries the published worth of a legacy and the live parent count; it
 * prices no specific pair (FR-G-4, ADR-0020 §3).
 *
 * HFW-OVERRIDE (data: worth rows) - `legacy.html:85-97` draws six value rows; so does this page. The
 * DM-3 correction stays in the note: Green and White are hint and skill sparks, which trigger at
 * inspiration events rather than paying a flat stat at career start.
 */
import AppLayout from '../../layouts/AppLayout.vue';
import PageHeader from '../../components/ui/PageHeader.vue';
import { LinkButton } from '../../components/Form';
import { Head } from '@inertiajs/vue3';

interface SavedPair {
    run_id: number;
    trainee: string;
    scenario_label: string;
    pair_label: string;
    parent_a: string | null;
    parent_b: string | null;
    blue_grades: string;
    builder_url: string;
}

const props = defineProps<{
    pairs: SavedPair[];
    totalCount: number;
    notice: string;
}>();

const th = 'border-b border-rule px-2 py-2 text-left text-xs font-semibold text-ink-strong';
const thNum = 'border-b border-rule px-2 py-2 text-right text-xs font-semibold text-ink-strong';
const td = 'border-b border-rule px-2 py-3 align-top text-sm text-ink';
const tdNum = 'border-b border-rule px-2 py-3 text-right align-top font-mono text-sm text-ink';
</script>

<template>
    <AppLayout :breadcrumb="[{ label: 'My data' }, { label: 'Legacy Lab' }]">
        <Head title="Legacy Lab" />
        <template #head>
            <PageHeader
                eyebrow="Legacy Lab"
                title="Build and compare inheritance pairs"
                description="A legacy is two parents and the sparks they pass on. Compare candidate pairs before committing one to a run."
            >
                <template #actions>
                    <!-- The HFW's one filled action: building a pair is exploration, comparing two is
                         the decision (`legacy.html:61`). -->
                    <LinkButton href="/legacy/compare" variant="primary">Compare two pairs</LinkButton>
                </template>
            </PageHeader>
        </template>

        <div class="grid items-start gap-4 g-side">
            <section class="card" aria-labelledby="legacy-saved">
                <h2 id="legacy-saved">Saved legacies</h2>
                <p class="sub">A saved pair is reusable across runs. Its value is the sparks the two parents carry together.</p>

                <p
                    v-if="props.pairs.length === 0"
                    class="mt-3 rounded-md border border-dashed border-rule bg-raised p-6 text-sm text-ink-muted"
                >
                    No saved pair yet. Open a run's Legacy builder, assign a parent, and confirm: that run
                    becomes a pair this list can compare.
                </p>

                <div v-else class="mt-3 overflow-x-auto">
                    <table class="w-full border-collapse">
                        <thead>
                            <tr>
                                <th :class="th">Legacy</th>
                                <th :class="th">Parent A</th>
                                <th :class="th">Parent B</th>
                                <th :class="thNum">Blue sparks</th>
                                <th :class="thNum"><span class="sr-only">Open the pair</span></th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr v-for="pair in props.pairs" :key="pair.run_id">
                                <td :class="td">
                                    <b class="text-ink-strong">{{ pair.pair_label }}</b>
                                    <span class="mt-0.5 block text-xs text-ink-muted">
                                        {{ pair.trainee }} · {{ pair.scenario_label }} · run #{{ pair.run_id }}
                                    </span>
                                </td>
                                <td :class="td">
                                    <template v-if="pair.parent_a">{{ pair.parent_a }}</template>
                                    <span v-else>N/A<span class="sr-only">: this parent slot is empty.</span></span>
                                </td>
                                <td :class="td">
                                    <template v-if="pair.parent_b">{{ pair.parent_b }}</template>
                                    <span v-else>N/A<span class="sr-only">: this parent slot is empty.</span></span>
                                </td>
                                <td :class="tdNum">{{ pair.blue_grades }}</td>
                                <td :class="[td, 'text-right']">
                                    <LinkButton :href="pair.builder_url" variant="secondary" size="sm">Open</LinkButton>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </section>

            <aside class="space-y-4" aria-label="Legacy reference">
                <section class="card" aria-labelledby="legacy-worth">
                    <h2 id="legacy-worth">What a legacy is worth</h2>
                    <table class="mt-2 w-full border-collapse text-sm">
                        <caption class="sr-only">Published spark reference values, by family</caption>
                        <tbody>
                            <tr class="border-b border-rule">
                                <th scope="row" class="py-2 pr-3 text-left font-medium text-ink">Blue spark, ★1</th>
                                <td class="py-2 text-right font-mono tabular-nums text-ink">+5 stat</td>
                            </tr>
                            <tr class="border-b border-rule">
                                <th scope="row" class="py-2 pr-3 text-left font-medium text-ink">Blue spark, ★2</th>
                                <td class="py-2 text-right font-mono tabular-nums text-ink">+12 stat</td>
                            </tr>
                            <tr class="border-b border-rule">
                                <th scope="row" class="py-2 pr-3 text-left font-medium text-ink">Blue spark, ★3</th>
                                <td class="py-2 text-right font-mono tabular-nums text-ink">+21 stat</td>
                            </tr>
                            <tr class="border-b border-rule">
                                <th scope="row" class="py-2 pr-3 text-left font-medium text-ink">Pink spark, ★1 · 4 · 7 · 10-18</th>
                                <td class="py-2 text-right font-mono tabular-nums text-ink">+1 / +2 / +3 / +4 grade</td>
                            </tr>
                            <tr class="border-b border-rule">
                                <th scope="row" class="py-2 pr-3 text-left font-medium text-ink">Green spark</th>
                                <td class="py-2 text-right font-mono tabular-nums text-ink">1-3 hint levels</td>
                            </tr>
                            <tr>
                                <th scope="row" class="py-2 pr-3 text-left font-medium text-ink">White spark, skill · competition</th>
                                <td class="py-2 text-right font-mono tabular-nums text-ink">hint +1…+5 · 3 / 6 / 9 stat</td>
                            </tr>
                        </tbody>
                    </table>
                    <p class="note mt-3">
                        <span class="g" aria-hidden="true">○</span>
                        <span>
                            <b>Blue is the stat spark and Pink is the aptitude spark</b>
                            (<code>UMAMUSUME_REFERENCE.md</code> §1.5.2). Blue pays a flat stat at career start;
                            Pink lifts an aptitude grade by the star count. Green and White are hint and skill
                            sparks, which trigger at inspiration events rather than at the start. The
                            +16-per-★3 cap raise is the breakthrough, a different mechanic that moves the
                            ceiling, not the stat. This screen stores and compares what you enter; it prices
                            no pair of its own.
                        </span>
                    </p>
                </section>

                <section class="card" aria-labelledby="legacy-parents">
                    <h2 id="legacy-parents">Parents available</h2>
                    <p class="mt-1 text-sm text-ink-muted">
                        {{ props.totalCount }} {{ props.totalCount === 1 ? 'veteran' : 'veterans' }} can be used
                        as parents. A veteran cannot be its own parent, and the two slots cannot hold the
                        same record.
                    </p>
                    <LinkButton href="/veterans" variant="secondary" size="sm" class="mt-3">
                        Open the veteran library
                    </LinkButton>
                </section>
            </aside>
        </div>
    </AppLayout>
</template>
