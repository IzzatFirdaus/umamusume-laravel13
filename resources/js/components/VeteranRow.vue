<script setup lang="ts">
import ArtworkSlot from './ArtworkSlot.vue';
import StatusPill from './StatusPill.vue';
import SubCell from './SubCell.vue';
import { veteranResultClause } from '../domain/veteranNote';

interface Props {
    veteran: {
        id: number;
        label: string;
        trainee: string;
        trainee_ja: string | null;
        scenario_label: string;
        status_label: string;
        tags: string[];
        notes: string | null;
        hasSelection: boolean;
        builder_url: string | null;
        veteran_url: string;
        /** The trainee's loopback portrait url, or null when the mirror holds no file. */
        portrait_url: string | null;
    };
}

const props = defineProps<Props>();

const group = (n: number): string => n.toLocaleString('en-US');

function statusVariant(status: string): 'info' | 'success' | 'warning' | 'neutral' {
    switch (status) {
        case 'Completed': return 'success';
        case 'Active': return 'info';
        case 'Retired': return 'warning';
        default: return 'neutral';
    }
}
</script>

<template>
    <div class="px-4 py-3 text-sm" data-veteran-row>
        <div class="flex flex-col gap-2 sm:flex-row sm:flex-wrap sm:items-baseline sm:justify-between sm:gap-x-4">
            <div class="flex flex-wrap items-baseline gap-3 min-w-0 flex-1">
                <!-- The trainee's identity frame, resolved server-side (`portrait_url`); the row prints her
                     name in the link beside it, so the image is decorative (`alt=""`, `design-2.0` §42) and
                     `reserve` holds the cell at one x whether or not the mirror holds the file. Absence
                     paints nothing: no broken frame, no grey box, no placeholder glyph (`DESIGN.md` §4.7). -->
                <ArtworkSlot :url="props.veteran.portrait_url" alt="" size="size-12" reserve />
                <!-- Wraps rather than truncating (`A-99`): a 53px name column clipped `Daiwa Scarlet` and
                     `Maruzensky` to one or two letters, and a career's identity is not a caption to elide.
                     Two lines at 390 cost less than an unreadable row. -->
                <a
                    :href="props.veteran.veteran_url"
                    class="font-semibold text-ink-strong hover:underline transition-colors duration-150 ease-out"
                >
                    {{ props.veteran.label }}
                </a>

                <span
                    v-if="props.veteran.trainee_ja !== null && props.veteran.trainee_ja !== props.veteran.trainee"
                    lang="ja"
                    class="text-ink-muted"
                >
                    {{ props.veteran.trainee_ja }}
                </span>
            </div>

            <div class="flex flex-wrap items-center gap-2 sm:shrink-0">
                <StatusPill
                    :label="props.veteran.status_label"
                    :variant="statusVariant(props.veteran.status_label)"
                    size="sm"
                />
                <!-- The card's primary action, in the header row beside the career it belongs to rather
                     than under the note: Phase 8.3 moved it here so the row reads as an action, not a
                     caption with a control parked below it. Kept neutral, so the filter button stays the
                     screen's single accent (Phase 8.7). -->
                <a
                    :href="`/veterans/compare?veterans[]=${props.veteran.id}`"
                    class="inline-flex min-h-11 items-center rounded-md border border-rule px-3 font-medium text-ink hover:bg-raised transition-colors duration-150 ease-out"
                >
                    Compare this career
                </a>
            </div>
        </div>

        <!-- Sub-cells for scenario and tags -->
        <div class="mt-2 flex flex-wrap items-center gap-3 text-sm">
            <SubCell
                label="Scenario"
                :value="props.veteran.scenario_label"
                value-class="font-mono text-base tabular-nums text-ink-strong"
            />
            <template v-if="props.veteran.tags.length > 0">
                <span class="text-xs text-ink-muted">Tags:</span>
                <span class="flex flex-wrap gap-1 text-xs">
                    <span
                        v-for="tag in props.veteran.tags"
                        :key="tag"
                        class="pill pill-neutral"
                        title="A tag you typed on this record. Tags are the Trainer's own words, not client vocabulary."
                    >
                        {{ tag }}
                    </span>
                </span>
            </template>
            <template v-else>
                <!-- Phase 8.2: a bare `N/A` is the unexplained-absence tell (ux-laws §1.4), so the line
                     states what is missing and the title says when the channel fills it. The line is
                     already absent above whenever a tag exists, so this is a state rather than a caption. -->
                <SubCell
                    label="Tags"
                    value="No tags recorded"
                    value-class="text-ink-muted"
                    title="Tags are only recorded when you enter one on the Save Veteran screen. File this career with a tag and it will print here."
                />
            </template>
        </div>

        <div v-if="veteranResultClause(props.veteran.notes) !== null" class="mt-2 text-xs text-ink-muted">
            {{ veteranResultClause(props.veteran.notes) }}
        </div>

        <div v-if="props.veteran.builder_url" class="mt-2 flex flex-wrap gap-2">
            <a
                :href="props.veteran.builder_url"
                class="inline-flex min-h-11 items-center rounded-md border border-rule px-3 font-medium text-ink hover:bg-raised transition-colors duration-150 ease-out"
            >
                Open in Legacy Lab
            </a>
        </div>
    </div>
</template>