import { createApp, h } from 'vue';
import BaseSelect from '../../../resources/js/components/Form/BaseSelect.vue';

/*
 * The A-101 contract, mounted. A control the caller marks required must expose that state to
 * assistive tech, not merely paint it.
 *
 * Four instances, on two axes. Required beside optional, because an assertion that reads only the
 * first cannot tell a binding from a constant. And both branches of the template: `BaseSelect`
 * renders a labelled `<select>` when the caller passes `label` and a bare one otherwise, and the
 * Snapshot form passes none (it owns its own `<label>` markup), so fixing only the labelled branch
 * would leave the screen the finding names unchanged.
 */
const options = [
    { value: '', label: 'Choose' },
    { value: 'a', label: 'Option A' },
];

createApp({
    render: () =>
        h('div', [
            h(BaseSelect, {
                id: 'labelled-required',
                name: 'labelled_required',
                modelValue: '',
                options,
                label: 'Required field',
                required: true,
            }),
            h(BaseSelect, {
                id: 'labelled-optional',
                name: 'labelled_optional',
                modelValue: '',
                options,
                label: 'Optional field',
            }),
            h(BaseSelect, {
                id: 'bare-required',
                name: 'bare_required',
                modelValue: '',
                options,
                required: true,
            }),
            h(BaseSelect, {
                id: 'bare-optional',
                name: 'bare_optional',
                modelValue: '',
                options,
            }),
        ]),
}).mount('#select-mount');
