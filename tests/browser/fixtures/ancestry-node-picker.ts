import { createApp, h, ref } from 'vue';
import AncestryNode from '../../../resources/js/components/legacy/AncestryNode.vue';

// The pick-control contract, mounted. `LegacySelect.vue` states it in prose — the node takes the current
// pick as a prop and emits on `change`, because a native `<select>` fires `change` and never
// `update:model-value` — and both assignable callers pass `:model-value` and `@update:model-value` on that
// understanding. Before this fixture the node declared neither, so the listener never ran, the attribute
// fell through onto the `<li>`, and a chosen parent was dropped from the PUT.
const picked = ref('none');
const model = ref('');

window.__setModel = (next: string): void => {
    model.value = next;
};

createApp({
    render: () =>
        h('div', [
            h('p', { id: 'picked' }, `picked: ${picked.value}`),
            h(AncestryNode, {
                label: 'Parent A',
                name: null,
                rank: null,
                isGuest: false,
                sparks: [],
                probability: { value: null, title: 'This tool holds no sourced star-roll table.' },
                controlName: 'legacies.0.legacy_id',
                options: [
                    { id: 7, name: 'Symboli Rudolf' },
                    { id: 9, name: 'Oguri Cap' },
                ],
                assignable: true,
                modelValue: model.value,
                // Exactly what the real callers do: `assignParent` writes the emitted id back into the
                // form, and the form's field comes back down as `model-value`. A `<select>` bound with
                // `:value` is controlled, so a fixture that recorded the emit without feeding it back
                // would reset the element on the next patch and the keyboard step below would test
                // nothing.
                'onUpdate:modelValue': (value: string) => {
                    picked.value = value;
                    model.value = value;
                },
            }),
        ]),
}).mount('#node-mount');
