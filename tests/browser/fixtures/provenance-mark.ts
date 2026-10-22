import { createApp, h } from 'vue';
import ProvenanceMark from '../../../resources/js/components/ProvenanceMark.vue';

/*
 * ProvenanceMark in the four states plus the two routes to its reason.
 *
 * `confirmed` is passed through a cast because the union still includes it (it is a server contract),
 * and the point of the case is that the component accepts it and draws nothing.
 */
createApp({
    render: () =>
        h('main', { id: 'fixture-main' }, [
            h('h1', { class: 'sr-only' }, 'Fixture'),
            h(ProvenanceMark, { id: 'mark-confirmed', state: 'confirmed' }),
            h(ProvenanceMark, { id: 'mark-calculated', state: 'calculated' }),
            h(ProvenanceMark, { id: 'mark-estimated', state: 'estimated' }),
            h(ProvenanceMark, { id: 'mark-unknown', state: 'unknown' }),
            // A mark with a reason, for the popover cases. It sits in its own block so the absolutely
            // positioned tooltip does not overlap the marks above it.
            h('div', { id: 'mark-reason', class: 'mt-24' }, [
                h(ProvenanceMark, { state: 'estimated', title: 'Averaged across three recorded turns.' }),
            ]),
            // An out-of-vocabulary state, as a config string could supply one. `defineProps` does not
            // validate at runtime, so the component is the only guard: it must paint nothing rather
            // than dereference a map entry that does not exist.
            h('div', { id: 'mark-out-of-vocabulary' }, [
                h(ProvenanceMark, { state: 'derived' as never }),
            ]),
        ]),
}).mount('#provenance-mark-mount');
