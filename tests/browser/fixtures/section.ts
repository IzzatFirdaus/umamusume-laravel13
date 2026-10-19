import { createApp, h } from 'vue';
import Section from '../../../resources/js/components/ui/Section.vue';

/*
 * The `id` contract, mounted. Five instances on three axes:
 *
 *  - with and without `id`, because an assertion that reads only the first cannot tell a binding from a
 *    constant, and the `id`-less shape is what every existing consumer already renders;
 *  - `level` 2 and 3, because the heading element is dynamic and the `id` has to follow it;
 *  - `description` present and absent, because the description already had its own aria wiring and must
 *    not be displaced by the new one.
 *
 * Plus one slotted instance: adoption replaces a hand-rolled `<section>` and its children with this
 * component, so the default slot has to carry markup and not only text.
 */
createApp({
    render: () =>
        h('div', [
            h(Section, { title: 'Plain region' }),
            h(Section, { id: 'region-one', title: 'Named region' }),
            h(Section, { id: 'region-three', title: 'Nested region', level: 3 }),
            h(Section, { id: 'region-desc', title: 'Described region', description: 'One line under the name.' }),
        ].concat([
            h(Section, { id: 'region-slot', title: 'Slotted region' }, () =>
                h('ul', { id: 'region-slot-list' }, [h('li', 'first child'), h('li', 'second child')]),
            ),
        ])),
}).mount('#section-mount');
