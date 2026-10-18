import { createApp, h, ref } from 'vue';
import ProvenanceBadge from '../../../resources/js/components/ProvenanceBadge.vue';

/*
 * The KI-62 regression fixture: mount the real SFC once and let the spec flip its `state` prop on the
 * mounted instance. No remount, no keyed re-render; the prop changes underneath it exactly the way an
 * Inertia in-place patch changes it.
 *
 * Plan 1.7 changed the starting point: `confirmed` now renders NOTHING, so a fixture that began there
 * would have no node to observe. It starts at `unknown`, which draws a mark, and the spec drives it
 * through the other exception states and into `confirmed` to prove the mark disappears and returns.
 */
const state = ref('unknown');

window.__setBadgeState = (next: string): void => {
    state.value = next;
};

createApp({ render: () => h(ProvenanceBadge, { state: state.value }) }).mount('#badge-mount');
