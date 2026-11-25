import { createApp, h, ref } from 'vue';
import SearchableSkillPicker from '../../../resources/js/components/Form/SearchableSkillPicker.vue';
import SparkTargetPicker from '../../../resources/js/components/legacy/SparkTargetPicker.vue';
import TraineeCombobox from '../../../resources/js/components/TraineeCombobox.vue';

/*
 * The three remaining unguarded Enter handlers (plan 5.2), mounted over a fixed list so the spec can
 * dispatch a composing Enter and observe whether the picker committed.
 *
 * `ui/Combobox.vue` has its own fixture (`combobox.html`) and its own cases; this fixture exists so
 * the other three are measured in one place rather than three near-identical fixtures. Each picker's
 * commit is rendered into its own `#…-chosen` line, which is what the refusal assertions read.
 */
const skills = [
    { id: 1, name: 'Admire Vega', type: null },
    { id: 2, name: 'Aim for the Brightest', type: null },
    { id: 3, name: 'Accelerator', type: null },
];

const sparkTargets = ['Speed', 'Stamina', 'Power'];

const roster = [
    {
        umamusumeId: 8,
        trainee: 'Vodka',
        traineeJa: 'ウオッカ',
        cards: [
            {
                selectionId: 101,
                sourceCardId: 201,
                title: 'Vodka [Aim for the Brightest]',
                titleKey: 'vodka',
                releaseDate: '2021-04-29',
                debut: false,
            },
        ],
    },
];

const skillChoice = ref<string>('');
const sparkChoice = ref<string | null>(null);
const traineeChoice = ref<string>('');

createApp({
    render: () =>
        h('main', { id: 'fixture-main' }, [
            h('h1', { class: 'sr-only' }, 'Fixture'),

            h(SearchableSkillPicker, {
                id: 'skill-picker',
                label: 'Skill',
                placeholder: 'Search skills',
                options: skills,
                onSelect: (payload: { id: number; name: string }) => {
                    skillChoice.value = `${payload.id}:${payload.name}`;
                },
            }),
            h('p', { id: 'skill-chosen' }, `skill:${skillChoice.value || 'none'}`),

            h(SparkTargetPicker, {
                id: 'spark-target',
                label: 'Target',
                targets: sparkTargets,
                modelValue: sparkChoice.value,
                'onUpdate:modelValue': (value: string | null) => {
                    sparkChoice.value = value;
                },
            }),
            h('p', { id: 'spark-chosen' }, `spark:${sparkChoice.value ?? 'none'}`),

            h(TraineeCombobox, {
                roster,
                initialTraineeId: '',
                initialCardId: '',
                invalid: false,
                onCommit: (hit: { card: { title: string } }) => {
                    traineeChoice.value = hit.card.title;
                },
                onClear: () => {
                    traineeChoice.value = '';
                },
            }),
            h('p', { id: 'trainee-chosen' }, `trainee:${traineeChoice.value || 'none'}`),
        ]),
}).mount('#ime-enter-mount');