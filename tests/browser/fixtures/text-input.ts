import { createApp, h, ref } from 'vue';
import TextInput from '../../../resources/js/components/ui/TextInput.vue';

/*
 * TextInput mounted with the two types it must render.
 *
 * The `number` case passes `min`/`max` as UNDECLARED attributes on purpose. The primitive has a
 * single root `<input>` and no `inheritAttrs: false`, so Vue's attribute fallthrough is what puts
 * them on the control. That is the contract this fixture proves.
 *
 * The union's own proof is not here — it is typecheck-level, and it fires at the real consumer
 * (`Runs/Create.vue`'s `<TextInput type="number">` template), not through `h()`, whose props are
 * loosely typed. Two contracts, two proofs: the union at typecheck, the fallthrough in the DOM.
 */
const numeric = ref<string | number | null>(null);
const text = ref<string | number | null>('');

createApp({
    render: () =>
        h('main', { id: 'fixture-main' }, [
            h('h1', { class: 'sr-only' }, 'Fixture'),

            h(TextInput, {
                id: 'ti-number',
                type: 'number',
                min: '1',
                max: '10',
                modelValue: numeric.value,
                'onUpdate:modelValue': (v: string) => (numeric.value = v),
            }),

            h(TextInput, {
                id: 'ti-text',
                type: 'text',
                modelValue: text.value,
                'onUpdate:modelValue': (v: string) => (text.value = v),
            }),
        ]),
}).mount('#text-input-mount');
