/*
 * Column shape for `DataTable.vue`, in a plain module rather than exported from the SFC.
 *
 * A `export interface` inside `<script setup>` is not visible to `tsc --noEmit`: the type checker
 * resolves `*.vue` modules to a generic shape, so importing the type from the component fails with
 * TS2614 even though Vite compiles it. Keeping it here means the type is checkable and the component
 * and its fixtures agree on one definition.
 */
export interface DataTableColumn {
    /** Key into a row object. */
    key: string;
    /** The column's name, rendered in `th[scope="col"]`. */
    label: string;
    /** Text alignment. Numeric columns read best right-aligned. */
    align?: 'left' | 'right';
    /** Marks the column sortable; the page that owns the sort passes `aria-sort` through `sort`. */
    sortable?: boolean;
    /** Tabular figures for numbers, so columns line up. */
    mono?: boolean;
}

/** The direction a sortable column reports through `aria-sort`. */
export type SortDirection = 'ascending' | 'descending';
