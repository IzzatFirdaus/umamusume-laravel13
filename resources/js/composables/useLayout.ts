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
 * The one source of truth for the width → CSS-class map (DESIGN.md §2.3, design-2.0 §11).
 * Every layout imports this rather than hardcoding its own map, so a shell-width change is a
 * single edit and the Settings radio labels cannot drift from the rendered classes.
 */
export const LAYOUT_WIDTH_CLASSES: Record<LayoutMode, string> = {
    compact: 'max-w-3xl',
    standard: 'max-w-6xl',
    wide: 'max-w-shell-wide',
};

/*
 * Read the active width preference once. Three consumers reach for it: the shell picks its `max-w`
 * cap from it, the Dashboard picks its grid shape from it, and SetupLayout/CareerLayout apply the
 * same cap to their wrapper. They all call through this composable so the rename migration lives
 * in one place and the writer's normalised row does not need a second map.
 */
export function useLayout(): { mode: ComputedRef<LayoutMode>; layoutWidthClass: ComputedRef<string> } {
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

    /* The container class for the active mode, sourced from LAYOUT_WIDTH_CLASSES. A missing
       preference reads as `standard`, the layout default (AGENTS.md §5: unrecorded defaults
       to the neutral state for layout concerns). */
    const layoutWidthClass = computed(() => LAYOUT_WIDTH_CLASSES[mode.value]);

    return { mode, layoutWidthClass };
}
