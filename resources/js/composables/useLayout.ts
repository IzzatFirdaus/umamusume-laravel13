import { computed, type ComputedRef } from 'vue';
import { usePage } from '@inertiajs/vue3';

export type LayoutMode = 'compact' | 'standard' | 'wide';

// The stored values were `focused` / `bento` / `wide_bento`. They map onto the new vocabulary in
// the in-app call site (AppLayout + Dashboard), so a row written before the rename still renders
// the same shell measure and grid shape.
const LEGACY_TO_MODE: Record<string, LayoutMode> = {
    focused: 'compact',
    bento: 'standard',
    wide_bento: 'wide',
};

/*
 * Read the active width preference once. Two consumers reach for it: the shell picks its `max-w`
 * cap from it, and the Dashboard picks its grid shape from it. They both call through this
 * composable so the rename migration lives in one place and the writer's normalised row does not
 * need a second map.
 */
export function useLayout(): { mode: ComputedRef<LayoutMode> } {
    const page = usePage();
    const mode = computed<LayoutMode>(() => {
        const stored = page.props?.preferences?.settings?.dashboard_layout as unknown;

        if (stored === 'compact' || stored === 'standard' || stored === 'wide') {
            return stored;
        }

        if (typeof stored === 'string' && stored in LEGACY_TO_MODE) {
            return LEGACY_TO_MODE[stored];
        }

        return 'standard';
    });

    return { mode };
}
