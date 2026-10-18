import { createApp, h, ref } from 'vue';
import DataTable from '../../../resources/js/components/ui/DataTable.vue';
import Pagination from '../../../resources/js/components/ui/Pagination.vue';
import type { DataTableColumn, SortDirection } from '../../../resources/js/components/ui/data-table-types';

/*
 * DataTable and Pagination, mounted together with the four shapes the spec has to reach:
 *
 *   - a plain table with a missing value in a data column,
 *   - a table whose first column is a row link (`rowHref`),
 *   - a sortable header, driven by a `sort` prop the page owns,
 *   - a row whose `rowHref` returns null, which must stay unlinked.
 *
 * The missing value is `null` on one row and `''` on another: both must read as absent. A row with
 * `0` is included so an assertion that treats falsy as missing fails here rather than in production.
 */
const columns: DataTableColumn[] = [
    { key: 'race', label: 'Race' },
    { key: 'grade', label: 'Grade' },
    { key: 'fans', label: 'Fan gate', align: 'right', mono: true, sortable: true },
];

const rows = [
    { race: 'Tenno Sho (Spring)', grade: 'G1', fans: 12000, href: '/races/1' },
    { race: 'Yayoi Sho', grade: 'G2', fans: null, href: '/races/2' },
    { race: 'Aoi Stakes', grade: 'G3', fans: '', href: '/races/3' },
    { race: 'Hopeful Stakes', grade: null, fans: 0, href: null },
];

createApp({
    setup() {
        const sort = ref<{ key: string; direction: SortDirection } | null>({
            key: 'fans',
            direction: 'descending',
        });

        const onSort = (key: string): void => {
            sort.value =
                sort.value?.key === key
                    ? { key, direction: sort.value.direction === 'ascending' ? 'descending' : 'ascending' }
                    : { key, direction: 'ascending' };
        };

        return () =>
            h('main', { id: 'fixture-main' }, [
                h('h1', { class: 'sr-only' }, 'Fixture'),
                h(DataTable, {
                    id: 'table-plain',
                    columns,
                    rows,
                    caption: 'Races this career can enter',
                    sort: sort.value,
                    onSort,
                }),
                h(DataTable, {
                    id: 'table-linked',
                    columns,
                    rows,
                    caption: 'Races with a row link',
                    rowHref: (row: Record<string, unknown>) => (row.href as string | null) ?? null,
                }),
                h(Pagination, {
                    id: 'pager',
                    page: 2,
                    pages: 3,
                    from: 26,
                    to: 50,
                    total: 67,
                    noun: 'races',
                }),
            ]);
    },
}).mount('#data-table-mount');
