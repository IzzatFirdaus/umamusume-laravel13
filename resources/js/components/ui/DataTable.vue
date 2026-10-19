<script setup lang="ts">
/*
 * A real table (plan 1.5). `<table>`/`<thead>`/`<th scope="col">` rather than a grid of divs, because
 * a screen reader announces a table as a table: its row and column count, the header each cell belongs
 * to, and the sort order of the column being read. A div grid can be made to look like a table and
 * never be one.
 *
 * Missing values render an en dash with an `sr-only` "Not recorded". The dash is the visual absence a
 * reader scans for; the words are what a screen reader hears, so the cell is never silent. Detail
 * views use `AbsenceNote` instead (plan 2.3); a table cell is the one place a dash is right.
 *
 * The wrapper is a focusable `role="region"` with an accessible name, so a keyboard user can scroll a
 * wide table horizontally without the arrow keys moving the page instead (WCAG 2.1.1). `tabindex="0"`
 * on the region is what makes that possible; a region without it cannot receive the keystrokes.
 */
import { useId } from 'vue';
import type { DataTableColumn, SortDirection } from './data-table-types';

/*
 * The column shape lives in `./data-table-types.ts` so `tsc --noEmit` can see it: a type exported from
 * `<script setup>` is invisible to the type checker. Re-exported here for callers that import the
 * component and the type together.
 */
export type { DataTableColumn };

const props = withDefaults(
    defineProps<{
        columns: DataTableColumn[];
        rows: Record<string, unknown>[];
        /**
         * Builds a link for a row. When given, the first cell becomes the link. Returning `null` for a
         * row leaves that row plain, which is how a disabled or summary row stays unclickable.
         */
        rowHref?: (row: Record<string, unknown>) => string | null;
        /** The table's caption. Required in practice: a table without a name is a grid of numbers. */
        caption: string;
        /**
         * The column currently sorted, and its direction. Omit for an unsorted table. This is passed
         * in rather than owned here: sorting belongs to the request on a server-sorted table, and a
         * primitive that sorted locally would disagree with the page it lives on.
         */
        sort?: { key: string; direction: SortDirection } | null;
        /** Emitted when a sortable header is activated. The page owns the resulting request. */
        onSort?: (key: string) => void;
    }>(),
    { rowHref: undefined, sort: null, onSort: undefined },
);

const alignClass = (column: DataTableColumn): string => (column.align === 'right' ? 'text-right' : 'text-left');

const monoClass = (column: DataTableColumn): string => (column.mono ? 'font-mono tabular-nums' : '');

/*
 * A value is missing when it is `null`, `undefined` or an empty/whitespace string. Zero and `false`
 * are values, not absences: a stat of 0 means zero, and rendering it as a dash would be a lie.
 */
function isMissing(value: unknown): boolean {
    if (value === null || value === undefined) {
        return true;
    }

    return typeof value === 'string' && value.trim() === '';
}

function ariaSort(column: DataTableColumn): SortDirection | 'none' | undefined {
    if (column.sortable !== true) {
        return undefined;
    }

    if (props.sort?.key !== column.key) {
        return 'none';
    }

    return props.sort.direction;
}

const captionId = `datatable-caption-${useId()}`;
</script>

<template>
    <div
        role="region"
        tabindex="0"
        :aria-label="caption"
        class="w-full min-w-0 overflow-x-auto rounded-md border border-rule focus:outline-2 focus:outline-offset-2"
    >
        <table :aria-labelledby="captionId" class="w-full border-collapse text-sm">
            <!-- The caption names the table and is sr-only, because the page above it already shows
                 the same words as its heading; repeating it visibly would say the title twice. -->
            <caption :id="captionId" class="sr-only">
                {{ caption }}
            </caption>
            <thead>
                <tr class="border-b border-rule">
                    <th
                        v-for="column in columns"
                        :key="column.key"
                        scope="col"
                        :aria-sort="ariaSort(column)"
                        :class="[
                            'bg-sunken px-2 py-2 text-xs font-semibold text-ink-strong @2xl:px-3',
                            column.secondary ? 'hidden @2xl:table-cell' : '',
                            alignClass(column),
                        ]"
                    >
                        <button
                            v-if="column.sortable"
                            type="button"
                            class="inline-flex min-h-11 items-center gap-1 hover:text-ink-strong"
                            @click="onSort?.(column.key)"
                        >
                            {{ column.label }}
                            <span aria-hidden="true">{{ sort?.key === column.key ? (sort.direction === 'ascending' ? '▲' : '▼') : '↕' }}</span>
                        </button>
                        <span v-else>{{ column.label }}</span>
                    </th>
                </tr>
            </thead>
            <tbody>
                <tr
                    v-for="(row, index) in rows"
                    :key="index"
                    class="border-b border-rule last:border-b-0"
                >
                    <td
                        v-for="(column, columnIndex) in columns"
                        :key="column.key"
                        :class="[
                            'px-2 py-2 align-top text-ink @2xl:px-3',
                            column.secondary ? 'hidden @2xl:table-cell' : '',
                            alignClass(column),
                            monoClass(column),
                        ]"
                    >
                        <!--
                             A page may render a cell itself, keyed on the column. This branch comes
                             BEFORE the missing-value check: a slot-filled column's row value is often
                             absent (there is no `select` field on a trainee row), and testing for
                             `isMissing` first printed the dash and never reached the slot, which is how
                             a table of slot-filled cells rendered as a column of dashes.
                        -->
                        <slot
                            v-if="$slots[`cell-${column.key}`]"
                            :name="`cell-${column.key}`"
                            :row="row"
                            :value="row[column.key]"
                        />
                        <template v-else-if="isMissing(row[column.key])">
                            <!-- The dash is decorative to a screen reader; the words carry the fact. -->
                            <span aria-hidden="true">–</span>
                            <span class="sr-only">Not recorded</span>
                        </template>
                        <a
                            v-else-if="columnIndex === 0 && rowHref && rowHref(row) !== null"
                            :href="rowHref(row) as string"
                            class="font-semibold text-ink-strong underline hover:text-brand-chrome"
                        >
                            {{ row[column.key] }}
                        </a>
                        <template v-else>{{ row[column.key] }}</template>
                    </td>
                </tr>
            </tbody>
        </table>
    </div>
</template>
