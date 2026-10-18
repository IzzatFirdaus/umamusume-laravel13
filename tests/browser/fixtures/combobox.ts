import { createApp, h, ref } from 'vue';
import Combobox from '../../../resources/js/components/ui/Combobox.vue';

/*
 * Combobox over a small fixed list. The options are ordered so arrow-key movement is observable:
 * Down from the first move must land on the second, not the fifth.
 */
const options = [
    { value: 1, label: 'Agnes Digital', sublabel: 'アグネスデジタル', lang: 'ja' },
    { value: 2, label: 'Gold Ship', sublabel: 'ゴールドシップ', lang: 'ja' },
    { value: 3, label: 'Icy Pearl', sublabel: 'アイスパール', lang: 'ja' },
    { value: 4, label: 'Rice Shower', sublabel: 'ライスシャワー', lang: 'ja' },
];

const chosen = ref<string | number | null>(null);

createApp({
    render: () =>
        h('main', { id: 'fixture-main' }, [
            h('h1', { class: 'sr-only' }, 'Fixture'),
            h(Combobox, {
                id: 'cb',
                label: 'Trainee',
                options,
                modelValue: chosen.value,
                'onUpdate:modelValue': (v: string | number | null) => (chosen.value = v),
            }),
            h('p', { id: 'chosen' }, `chosen:${chosen.value ?? 'none'}`),
        ]),
}).mount('#combobox-mount');
