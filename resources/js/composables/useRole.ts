import { computed } from 'vue';

export type Role =
    | 'page'
    | 'panel'
    | 'raised'
    | 'sunken'
    | 'ink'
    | 'ink-strong'
    | 'ink-muted'
    | 'rule'
    | 'chrome'
    | 'on-chrome'
    | 'brand-chrome'
    | 'on-brand-chrome'
    | 'brand-accent'
    | 'brand-accent-ink'
    | 'brand-accent-soft'
    | 'status-positive'
    | 'status-positive-ink'
    | 'status-caution'
    | 'status-caution-ink';

const TOKEN_TO_VAR: Record<Role, string> = {
    page: '--color-page',
    panel: '--color-panel',
    raised: '--color-raised',
    sunken: '--color-sunken',
    ink: '--color-ink',
    'ink-strong': '--color-ink-strong',
    'ink-muted': '--color-ink-muted',
    rule: '--color-rule',
    chrome: '--color-chrome',
    'on-chrome': '--color-on-chrome',
    'brand-chrome': '--color-brand-chrome',
    'on-brand-chrome': '--color-on-brand-chrome',
    'brand-accent': '--color-brand-accent',
    'brand-accent-ink': '--color-brand-accent-ink',
    'brand-accent-soft': '--color-brand-accent-soft',
    'status-positive': '--color-status-positive',
    'status-positive-ink': '--color-status-positive-ink',
    'status-caution': '--color-status-caution',
    'status-caution-ink': '--color-status-caution-ink',
};

// `useRole` resolves a Role to the resolved `var(...)` shape; components are meant to reference the
// token via a Tailwind class (e.g. `bg-page`, `text-ink-strong`) and never a hex. Tracking this here
// makes G-18 enforceable from one place: any new role must be added to `Role` and `TOKEN_TO_VAR` in
// the same change, or `DesignTokensTest` will fail.
export function useRole(role: Role): { cssVar: string } {
    return {
        cssVar: computed(() => `var(${TOKEN_TO_VAR[role]})`).value,
    };
}
