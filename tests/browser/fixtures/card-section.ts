import { createApp, h } from 'vue';
import Card from '../../../resources/js/components/ui/Card.vue';
import Section from '../../../resources/js/components/ui/Section.vue';

/*
 * Card and Section, mounted together so the one relationship that matters is real: a Card inside a
 * Section, and the Section's own heading above it. The page head `h1` stands in for the shell's, so
 * the fixture is a valid document outline (h1 -> h2 -> h3) rather than two headings floating free.
 *
 * Two cards, both padding steps, so an assertion that reads only the first cannot pass on a constant.
 */
createApp({
    render: () =>
        h('main', { id: 'fixture-main' }, [
            h('h1', { class: 'sr-only' }, 'Fixture'),
            h(
                Section,
                { title: 'Training history', description: 'Every recorded turn, newest first.' },
                {
                    default: () => [
                        h(Card, { id: 'card-md' }, () => 'Dense card body'),
                        h(Card, { id: 'card-lg', padding: 'lg' }, () => 'Roomy card body'),
                    ],
                },
            ),
        ]),
}).mount('#card-section-mount');
