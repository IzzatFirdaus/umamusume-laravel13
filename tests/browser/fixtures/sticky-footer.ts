import { createApp, h } from 'vue';
import StickyFooter from '../../../resources/js/components/ui/StickyFooter.vue';

/*
 * StickyFooter in the shape a wizard step actually has: content, then the bar, then MORE content
 * below it.
 *
 * The trailing block is not padding for the test's convenience. `position: sticky` pins an element
 * only while its containing block still extends below it; a bar placed as the last child of a page has
 * no room to stick across and behaves like `static` even when its computed position is `sticky`. Real
 * steps look like this because the "About this data" disclosure and the step's own footer note sit
 * under the bar.
 */
const paragraphs = Array.from({ length: 40 }, (_, i) =>
    h('p', { class: 'py-2 text-sm text-ink' }, `Row ${i + 1} of the step's content.`),
);

createApp({
    render: () =>
        h('main', { id: 'fixture-main', class: 'min-h-screen' }, [
            h('h1', { class: 'sr-only' }, 'Fixture'),
            ...paragraphs,
            h('a', { href: '#end', id: 'last-link', class: 'inline-block py-2' }, 'Last field'),
            h(StickyFooter, { id: 'footer' }, {
                left: () => h('button', { type: 'button', id: 'back-btn' }, 'Back'),
                right: () => h('button', { type: 'button', id: 'next-btn' }, 'Save and continue'),
            }),
            // Content below the bar, which is what gives it a containing block to stick across.
            h('div', { class: 'mt-6', style: 'height: 60vh' }, [
                h('p', { id: 'below-bar' }, 'Disclosure text below the action bar.'),
            ]),
        ]),
}).mount('#sticky-footer-mount');
