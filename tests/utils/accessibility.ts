import { AxeBuilder } from '@axe-core/playwright';

/*
 * Every scan is scoped to the Inertia root, `#app`, and never to the whole document.
 *
 * Inertia drives NProgress on every visit, and NProgress appends its bar to `<body>` — a sibling of
 * `#app`, not a descendant — carrying `role="bar"`, which is not a valid ARIA role. axe reports that
 * as a critical `aria-roles` violation under 4.1.2 Name, Role, Value for as long as the bar is
 * mounted, which is exactly as long as a visit is in flight. A page-wide scan therefore passes or
 * fails on timing rather than on this application's markup: the same suite was green and red on
 * consecutive runs against identical code (KNOWN-ISSUES.md KI-63).
 *
 * Scoping here rather than in each spec is what makes every scan deterministic and keeps one
 * statement of the reason. It is a scope, not a suppression: the excluded node belongs to Inertia,
 * and the tree asserted is the one this repository renders.
 */
export function buildAxe(page: import('@playwright/test').Page) {
    return new AxeBuilder({ page })
        .include('#app')
        .withTags(['wcag2a', 'wcag2aa', 'wcag21aa']);
}
