import { createApp, h } from 'vue';
import StatRing from '../../../resources/js/components/ui/StatRing.vue';
import Sparkline from '../../../resources/js/components/ui/Sparkline.vue';

/*
 * StatRing and Sparkline together, with the edges the spec names: a half-full ring, a value past its
 * cap, a zero value, a rising series, a flat series, and a one-point series that must not draw a line.
 */
createApp({
    render: () =>
        h('main', { id: 'fixture-main' }, [
            h('h1', { class: 'sr-only' }, 'Fixture'),

            h('section', { 'aria-label': 'stat rings' }, [
                h(StatRing, { value: 600, max: 1200, color: 'text-stat-speed-ink', label: 'Speed', id: 'ring-half' }),
                h(StatRing, { value: 1300, max: 1200, label: 'Stamina', id: 'ring-over' }),
                h(StatRing, { value: 0, max: 1200, label: 'Power', id: 'ring-zero' }),
            ]),

            h('section', { 'aria-label': 'sparklines' }, [
                h(Sparkline, { values: [1, 4, 2, 8, 6], id: 'spark-rise', class: 'text-sp-ink' }),
                h(Sparkline, { values: [5, 5, 5, 5], id: 'spark-flat' }),
                h(Sparkline, { values: [7], id: 'spark-single' }),
            ]),
        ]),
}).mount('#stat-visuals-mount');
