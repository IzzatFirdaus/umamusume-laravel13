import { createApp, h } from 'vue';
import StatusBadge from '../../../resources/js/components/ui/StatusBadge.vue';

/*
 * StatusBadge in the three states it allows, plus a forced `confirmed` that must render nothing.
 *
 * `confirmed` is passed through a cast because the component's own union refuses it — that refusal is
 * the first line of defence and is a compile-time fact, independent of what the DOM does. The runtime
 * case here is the second line: a page could still hand the component a value from JSON that the type
 * system never saw, and a badge must not appear for it.
 */
const forced = 'confirmed' as unknown as 'estimated';

createApp({
    render: () =>
        h('main', { id: 'fixture-main' }, [
            h('h1', { class: 'sr-only' }, 'Fixture'),
            h(StatusBadge, { id: 'badge-estimated', state: 'estimated' }),
            h(StatusBadge, { id: 'badge-unknown', state: 'unknown' }),
            h(StatusBadge, { id: 'badge-unconfirmed', state: 'unconfirmed' }),
            h(StatusBadge, { id: 'badge-custom', state: 'estimated', label: 'Fuzzy match' }),
            h('div', { id: 'badge-confirmed-host' }, [h(StatusBadge, { state: forced })]),
        ]),
}).mount('#status-badge-mount');
