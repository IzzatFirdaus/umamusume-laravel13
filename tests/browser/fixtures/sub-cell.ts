import { createApp, h } from 'vue';
import SubCell from '../../../resources/js/components/SubCell.vue';

/*
 * SubCell in its two meaningful shapes, because the component's job is to tell them apart:
 *
 *   - an absent figure whose host supplies the reason (the common host shape after the audit),
 *   - a present figure the host also qualifies, which must NOT wear an absence marker.
 *
 * A present value with no reason is covered by the same branch as the second case.
 */
createApp({
    render: () =>
        h('main', { id: 'fixture-main' }, [
            h('h1', { class: 'sr-only' }, 'Fixture'),
            h('div', { id: 'absent-with-reason' }, [
                h(SubCell, { label: 'Due', value: 'N/A', title: 'No due turn is recorded for this milestone.' }),
            ]),
            h('div', { id: 'present-with-qualifier' }, [
                h(SubCell, { label: 'Fan gate', value: '12,000', title: 'Averaged across three recorded turns.' }),
            ]),
            h('div', { id: 'present-bare' }, [
                h(SubCell, { label: 'Fans', value: '0' }),
            ]),
        ]),
}).mount('#sub-cell-mount');
