import { createApp, h, ref } from 'vue';
import ProvenanceBadge from '../../../resources/js/components/ProvenanceBadge.vue';

// The KI-62 regression fixture: mount the real SFC once and let the spec flip its `state` prop on
// the mounted instance. No remount, no keyed re-render; the prop changes underneath it exactly the
// way an Inertia in-place patch changes it.
const state = ref('confirmed');

window.__setBadgeState = (next: string): void => {
    state.value = next;
};

createApp({ render: () => h(ProvenanceBadge, { state: state.value }) }).mount('#badge-mount');
