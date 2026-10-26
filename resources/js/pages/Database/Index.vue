<script setup lang="ts">
/*
 * The Database hub (SCREEN-023, plan §8 D17), rebuilt on database.html.
 *
 * The HFW's shape is one `g-3` launcher: nine tiles, each a live count (or the word the area's data
 * carries instead of one) over a one-line description of what the area holds. The hub invents no
 * figure: the counts arrive from the controller's own tables, and the transcribed areas print their
 * family word rather than a number no table can corroborate.
 *
 * The subnav under the page head is the area switcher, shared with every area page
 * (`components/database/DatabaseNav.vue`). The source ledger is the page's one filled action.
 */
import AppLayout from '../../layouts/AppLayout.vue';
import PageHeader from '../../components/ui/PageHeader.vue';
import { LinkButton } from '../../components/Form';
import DatabaseNav from '../../components/database/DatabaseNav.vue';
import { Head, Link } from '@inertiajs/vue3';

const props = defineProps<{
    areas: { label: string; url: string; source: string; count: number | null; tag: string | null; description: string }[];
    matrix: { state: string; verified_at: string | null; source: string };
}>();

const group = (n: number): string => n.toLocaleString('en-US');
const tile = 'flex flex-col justify-between rounded-md border border-rule bg-panel p-4 hover:border-brand-chrome transition-colors duration-150 ease-out';
</script>

<template>
    <AppLayout :breadcrumb="[{ label: 'Database' }]">
        <Head title="Reference data" />
        <template #head>
            <PageHeader
                eyebrow="Database"
                title="Reference data"
                description="Everything the tool knows about the game, and where each figure came from. Nine areas, one source ledger."
            >
                <template #actions>
                    <LinkButton href="/database/sources" variant="primary">Source ledger</LinkButton>
                </template>
            </PageHeader>
        </template>

        <DatabaseNav />

        <p class="mt-4 rounded-md border border-rule bg-raised px-3 py-2 text-sm text-ink-muted">
            <span aria-hidden="true" class="mr-2 text-status-positive">i</span>
            <b class="text-ink-strong">Global data · {{ props.matrix.state }}.</b>
            <template v-if="props.matrix.verified_at">
                The scenario matrix was verified {{ props.matrix.verified_at }} against
                {{ props.matrix.source }}.
            </template>
            <template v-else>
                The scenario matrix carries no recorded verification date; the source ledger names the
                documents behind it.
            </template>
            Nothing on this page was fetched today; a verification date is not an update date.
        </p>

        <nav aria-label="Reference data" class="mt-4">
            <ul class="grid gap-3 md:grid-cols-2 xl:grid-cols-3">
                <li v-for="(area, index) in props.areas" :key="area.label">
                    <!-- The tile's name is its area, not its area plus its count: the count and the
                         description ride along as the description, so the door is addressable by the
                         word the rail and the area pages use. -->
                    <Link
                        :href="area.url"
                        :prefetch="'hover'"
                        :aria-label="area.label"
                        :aria-describedby="`database-area-${index}`"
                        :class="tile"
                    >
                        <span class="flex items-baseline justify-between gap-3">
                            <b class="text-sm font-bold text-ink-strong">{{ area.label }}</b>
                            <span v-if="area.count !== null" class="font-mono text-sm font-bold tabular-nums text-ink-strong">
                                {{ group(area.count) }}
                            </span>
                            <span
                                v-else
                                class="inline-flex shrink-0 items-center rounded-full border border-rule px-2 py-0.5 text-xs font-semibold text-ink-muted"
                            >{{ area.tag }}</span>
                        </span>
                        <span :id="`database-area-${index}`" class="mt-1.5 text-xs text-ink-muted">{{ area.description }}</span>
                    </Link>
                </li>
            </ul>
        </nav>
    </AppLayout>
</template>
