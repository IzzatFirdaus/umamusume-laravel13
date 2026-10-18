import { createApp, h } from 'vue';
import AbsenceNote from '../../../resources/js/components/ui/AbsenceNote.vue';

/*
 * AbsenceNote in the three shapes that matter: one name, several names, and none.
 *
 * The single and multiple cases are separated because they are different claims — that the line
 * renders at all, and that it lists. The elevated failability proof replaces the join with a
 * comma-less concatenation, which must red only the multiple case.
 */
createApp({
    render: () =>
        h('main', { id: 'fixture-main' }, [
            h('h1', { class: 'sr-only' }, 'Fixture'),
            h(AbsenceNote, { id: 'note-single', labels: ['reward'] }),
            h(AbsenceNote, { id: 'note-multi', labels: ['reward', 'running style', 'skill points'] }),
            h(AbsenceNote, { id: 'note-empty', labels: [] }),
            h(AbsenceNote, { id: 'note-blank', labels: ['  ', ''] }),
        ]),
}).mount('#absence-note-mount');
