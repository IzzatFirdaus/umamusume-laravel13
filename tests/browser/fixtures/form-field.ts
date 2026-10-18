import { createApp, h, ref } from 'vue';
import FormField from '../../../resources/js/components/ui/FormField.vue';
import TextInput from '../../../resources/js/components/ui/TextInput.vue';
import Textarea from '../../../resources/js/components/ui/Textarea.vue';
import Checkbox from '../../../resources/js/components/ui/Checkbox.vue';
import Select from '../../../resources/js/components/ui/Select.vue';
import SegmentedControl from '../../../resources/js/components/ui/SegmentedControl.vue';

/*
 * The FormField family, mounted the way a page uses it: a FormField whose default slot wires the
 * control's ids from the slot props.
 *
 * The two selects are the pair the elevated failability proof needs. They differ ONLY in `required`,
 * so removing `:aria-required` from the chain must red the required case and leave the optional case
 * green — if both go red, the assertion is measuring something coarser than the property it names.
 */
const text = ref('');
const note = ref('');
const checked = ref(false);
const requiredChoice = ref<string | number | null>(null);
const optionalChoice = ref<string | number | null>(null);
const purpose = ref<string | null>('speed');

const options = [
    { value: 'unity_cup', label: 'Unity Cup' },
    { value: 'ura_finale', label: 'URA Finale' },
];

createApp({
    render: () =>
        h('main', { id: 'fixture-main' }, [
            h('h1', { class: 'sr-only' }, 'Fixture'),

            h(FormField, { id: 'ff-text', label: 'Trainee', hint: 'The form you trained.' }, {
                default: (slot: { id: string; describedBy?: string; invalid: boolean; required: boolean }) =>
                    h(TextInput, {
                        id: slot.id,
                        describedBy: slot.describedBy,
                        invalid: slot.invalid,
                        required: slot.required,
                        modelValue: text.value,
                        'onUpdate:modelValue': (v: string) => (text.value = v),
                    }),
            }),

            h(FormField, { id: 'ff-error', label: 'Scenario', error: 'Pick a scenario.' }, {
                default: (slot: { id: string; describedBy?: string; invalid: boolean; required: boolean }) =>
                    h(TextInput, {
                        id: slot.id,
                        describedBy: slot.describedBy,
                        invalid: slot.invalid,
                        required: slot.required,
                        modelValue: text.value,
                        'onUpdate:modelValue': (v: string) => (text.value = v),
                    }),
            }),

            h(FormField, { id: 'ff-optional', label: 'Nickname', required: false }, {
                default: (slot: { id: string; describedBy?: string; invalid: boolean; required: boolean }) =>
                    h(TextInput, {
                        id: slot.id,
                        describedBy: slot.describedBy,
                        invalid: slot.invalid,
                        required: slot.required,
                        modelValue: text.value,
                        'onUpdate:modelValue': (v: string) => (text.value = v),
                    }),
            }),

            h(FormField, { id: 'ff-note', label: 'Notes', hint: 'What happened.' }, {
                default: (slot: { id: string; describedBy?: string; invalid: boolean; required: boolean }) =>
                    h(Textarea, {
                        id: slot.id,
                        describedBy: slot.describedBy,
                        invalid: slot.invalid,
                        required: slot.required,
                        modelValue: note.value,
                        'onUpdate:modelValue': (v: string) => (note.value = v),
                    }),
            }),

            h(FormField, { id: 'ff-check', label: 'Imported', required: false }, {
                default: () => h(Checkbox, {
                    id: 'ff-check-input',
                    label: 'This run was imported',
                    modelValue: checked.value,
                    'onUpdate:modelValue': (v: boolean) => (checked.value = v),
                }),
            }),

            // REQUIRED select: the case the c2de34b regression guard protects.
            h(Select, {
                id: 'select-required',
                name: 'required_choice',
                label: 'Required scenario',
                options,
                modelValue: requiredChoice.value,
                required: true,
                'onUpdate:modelValue': (v: string | number | null) => (requiredChoice.value = v),
            }),

            // OPTIONAL select: identical but for `required`. Its sibling in the proof.
            h(Select, {
                id: 'select-optional',
                name: 'optional_choice',
                label: 'Optional scenario',
                options,
                modelValue: optionalChoice.value,
                required: false,
                'onUpdate:modelValue': (v: string | number | null) => (optionalChoice.value = v),
            }),

            h(SegmentedControl, {
                legend: 'Purpose',
                options: [
                    { value: 'speed', label: 'Speed' },
                    { value: 'stamina', label: 'Stamina' },
                    { value: 'power', label: 'Power' },
                ],
                modelValue: purpose.value,
                'onUpdate:modelValue': (v: string) => (purpose.value = v),
            }),
        ]),
}).mount('#form-field-mount');
