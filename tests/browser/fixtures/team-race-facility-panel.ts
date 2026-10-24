import { createApp, h } from 'vue';
import TeamRaceFacilityPanel from '../../../resources/js/pages/Career/Cockpit/Panels/TeamRaceFacilityPanel.vue';
import { mockScenario } from './team-race-facility-panel.fixture';

createApp({
    render: () =>
        h('main', { id: 'fixture-main' }, [
            h('h1', { class: 'sr-only' }, 'Fixture'),
            h(TeamRaceFacilityPanel, {
                scenario: mockScenario,
                label: 'Team Race Facilities',
                kind: 'panel',
                name: 'team_race_facility',
            }),
        ]),
}).mount('#facility-panel-mount');
