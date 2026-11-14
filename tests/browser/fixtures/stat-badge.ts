import { createApp, h } from 'vue';
import StatBadge from '../../../resources/js/components/StatBadge.vue';

/*
 * StatBadge in both variants, all seven stat keys, and the value/no-value states.
 *
 * Every stat key renders so the token classes are visually inspectable side by side; the pal and
 * group chips render in their neutral fallback (bg-sunken + border-rule), which is the documented
 * difference-by-design for the two stats that own no tint/line tokens.
 */
const stats = ['speed', 'stamina', 'power', 'guts', 'wit', 'pal', 'group'] as const;

createApp({
    render: () =>
        h('main', { id: 'fixture-main' }, [
            h('h1', { class: 'sr-only' }, 'Fixture'),
            h('section', { 'aria-label': 'tint variant, label only' }, [
                stats.map((s) => h(StatBadge, { key: s, stat: s, id: `badge-tint-${s}` })),
            ]),
            h('section', { 'aria-label': 'tint variant with value' }, [
                stats.map((s) => h(StatBadge, { key: s, stat: s, value: 1200, id: `badge-value-${s}` })),
            ]),
            h('section', { 'aria-label': 'plain variant' }, [
                h(StatBadge, { stat: 'speed', variant: 'plain', id: 'badge-plain-speed' }),
                h(StatBadge, { stat: 'pal', variant: 'plain', id: 'badge-plain-pal' }),
            ]),
            h('section', { 'aria-label': 'string value' }, [
                h(StatBadge, { stat: 'guts', value: 'not recorded', id: 'badge-string-guts' }),
            ]),
        ]),
}).mount('#stat-badge-mount');
