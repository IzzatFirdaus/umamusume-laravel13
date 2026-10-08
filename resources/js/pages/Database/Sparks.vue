<script setup lang="ts">
/*
 * The Sparks reference view (SCREEN-023, `SCR-SYS-010`). The Spark categories from
 * `docs/UMAMUSUME_REFERENCE.md` §1.5.2 and the star-roll odds from §1.5.3.
 *
 * **The category colour is §18's spark identity, paired with the word.** Blue, Pink, Green and White
 * are carried as an accent chip beside the category name, never as the only difference between two
 * rows: the category word and the global name both print.
 *
 * **The record counts are cited, and one of them is the guide's own reading.** The Spark export does
 * exist on a developed machine (`research-scratch/data/json/factors.json`) but it is gitignored and no
 * runtime path reads it, so the six counts are transcribed from the guide rather than re-derived. Five
 * of the six are cells in that export (5, 10, 452, 37, 34). The sixth, 268, is the guide reading part
 * of its 336-record bucket, which is why the 806 these rows sum to is the described total while the
 * export holds 874. The view names that difference instead of leaving the reader to find it.
 *
 * **The odds table renders on the page.** It is three rows and it answers a question a Trainer asks
 * directly, so it is not hidden in a drawer (§35's progressive disclosure is for the row detail, not
 * for a table this small).
 */
import AppLayout from '../../layouts/AppLayout.vue';
import ProvenanceBadge from '../../components/ProvenanceBadge.vue';
import ReferenceDrawer from '../../components/database/ReferenceDrawer.vue';
import SourceNote from '../../components/database/SourceNote.vue';
import { Head } from '@inertiajs/vue3';
import { nextTick, ref } from 'vue';

interface Category {
    category: string;
    jp_name: string;
    global_name: string;
    effect: string;
    records: number;
    state: 'confirmed' | 'calculated' | 'estimated' | 'unknown';
    source_title: string;
}

interface OddsRow {
    band: string;
    one_star: string;
    two_stars: string;
    three_stars: string;
}

const props = defineProps<{
    categories: Category[];
    rollOdds: OddsRow[];
    rollOddsNote: string;
    recheck: { text: string; url: string };
    source: { section: string; anchor: string; doc: string };
    totalCount: number;
}>();

/** §18's four spark families have no committed token set in this build, so the chip is neutral and
 * the category word carries the identity. Inventing a colour mapping would state a distinction the
 * design system does not own; the word is the distinction, and it always prints. */
const chip = 'bg-sunken text-ink-strong';

const selected = ref<Category | null>(null);
const trigger = ref<HTMLElement | null>(null);

const open = (row: Category, event: MouseEvent): void => {
    trigger.value = event.currentTarget as HTMLElement;
    selected.value = row;
};

const close = async (): Promise<void> => {
    selected.value = null;
    await nextTick();
    trigger.value?.focus();
};
</script>

<template>
    <AppLayout>
        <Head title="Sparks" />
        <template #title>Sparks</template>

        <h2 class="text-2xl font-semibold text-ink-strong">Sparks</h2>

        <p class="mt-4 max-w-3xl text-sm text-ink-muted">
            The Spark categories a finished Umamusume can carry, and the odds a Spark rolls at one,
            two or three stars. The inventory is the reference guide's own count; roll odds follow
            the final value of the stat.
        </p>

        <SourceNote
            :section="props.source.section"
            :anchor="props.source.anchor"
            :doc="props.source.doc"
            :recheck="props.recheck"
        />

        <p class="mt-6 text-sm text-ink-muted">{{ props.totalCount }} Spark categories</p>

        <section
            v-if="props.categories.length === 0"
            aria-labelledby="sparks-empty"
            class="mt-4 rounded-md border border-dashed border-rule bg-raised p-6"
        >
            <h3 id="sparks-empty" class="text-base font-semibold text-ink-strong">
                No Spark categories are sourced
            </h3>
            <p class="mt-2 text-sm text-ink-muted">
                The reference guide's §1.5.2 table is what this view prints, and it is empty here.
                Without it there is no way to tell a stat spark from a competition one, or to size the
                odds a parent faces.
            </p>
            <p class="mt-2 text-sm text-ink">
                Re-read the guide's §1.5.2 categories against the local export.
            </p>
        </section>

        <ul v-else class="mt-2 flex flex-col gap-3">
            <li
                v-for="row in props.categories"
                :key="row.category"
                class="rounded-md border border-rule bg-raised"
            >
                <button
                    type="button"
                    class="flex min-h-11 w-full flex-wrap items-baseline justify-between gap-x-4 gap-y-2 px-4 py-3 text-left hover:bg-panel"
                    :aria-expanded="selected?.category === row.category"
                    @click="open(row, $event)"
                >
                    <span class="flex flex-wrap items-center gap-2">
                        <span class="rounded-full px-2 py-0.5 text-xs font-semibold" :class="chip">
                            {{ row.category }}
                        </span>
                        <span class="text-sm font-medium text-ink-strong">{{ row.global_name }}</span>
                    </span>

                    <span class="flex flex-wrap items-center gap-2">
                        <span class="font-mono text-xs tabular-nums text-ink-muted">
                            {{ row.records }} records
                        </span>
                        <ProvenanceBadge :state="row.state" :title="row.source_title" />
                    </span>
                </button>

                <dl
                    class="grid grid-cols-1 gap-x-4 gap-y-1 border-t border-rule px-4 py-3 text-sm sm:grid-cols-[9rem_1fr]"
                >
                    <dt class="text-ink-muted">JP name</dt>
                    <dd class="text-ink" lang="ja">{{ row.jp_name }}</dd>
                    <dt class="text-ink-muted">Effect</dt>
                    <dd class="text-ink">{{ row.effect }}</dd>
                </dl>

                <div class="px-4 pb-3">
                    <ReferenceDrawer
                        v-if="selected?.category === row.category"
                        :title="row.global_name"
                        @close="close"
                    >
                        <p>{{ row.effect }}</p>
                        <p class="text-ink-muted">
                            {{ row.records }} records, as the reference guide
                            counts them.
                        </p>
                        <p class="text-xs text-ink-muted">{{ row.source_title }}</p>
                    </ReferenceDrawer>
                </div>
            </li>
        </ul>

        <p v-if="props.categories.length > 0" class="mt-3 max-w-3xl text-sm text-ink-muted">
            The guide counts 68 further records in the same export that no page in its pass explains;
            they are counted, not described, so they are absent here.
        </p>

        <!--
            The star-roll odds, §1.5.3. A real table with row headers, because the four columns are the
            same property across three bands and comparison is the point (design-2.0 §46).
        -->
        <section aria-labelledby="spark-odds" class="mt-10">
            <h3 id="spark-odds" class="text-lg font-semibold text-ink-strong">
                Star-roll odds by final stat value
            </h3>

            <p class="mt-2 max-w-3xl text-sm text-ink-muted">{{ props.rollOddsNote }}</p>

            <div
                class="mt-3 overflow-x-auto rounded-md border border-rule bg-raised"
                role="region"
                tabindex="0"
                aria-label="Star-roll odds, scrollable"
            >
                <table class="w-full min-w-[28rem] text-sm">
                    <caption class="sr-only">
                        Star-roll odds by final stat value: one, two and three stars
                    </caption>
                    <thead>
                        <tr class="border-b border-rule bg-sunken text-left text-xs text-ink-muted">
                            <th scope="col" class="px-3 py-1.5 font-semibold">Final stat value</th>
                            <th scope="col" class="px-3 py-1.5 font-semibold">1 star</th>
                            <th scope="col" class="px-3 py-1.5 font-semibold">2 stars</th>
                            <th scope="col" class="px-3 py-1.5 font-semibold">3 stars</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-rule">
                        <tr v-for="odds in props.rollOdds" :key="odds.band">
                            <th scope="row" class="px-3 py-1.5 text-left font-medium text-ink-strong">
                                {{ odds.band }}
                            </th>
                            <td class="px-3 py-1.5 tabular-nums text-ink">{{ odds.one_star }}</td>
                            <td class="px-3 py-1.5 tabular-nums text-ink">{{ odds.two_stars }}</td>
                            <td class="px-3 py-1.5 tabular-nums text-ink">{{ odds.three_stars }}</td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </section>
    </AppLayout>
</template>
