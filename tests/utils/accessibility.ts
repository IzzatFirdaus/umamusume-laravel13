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

/*
 * Open every `<details>` on the page before a scan.
 *
 * axe does not descend into a closed disclosure: a `role="list"` whose children are not
 * `listitem`s, sitting inside a collapsed `<details>`, is invisible to the scan. That is not a
 * hypothetical - A-91 (plan slice 1.4) found a live `aria-required-children` violation at
 * critical impact that a settled scan had never seen, because it sat inside a collapsed
 * `<details>` on the wizard's scenario step, and no scan covered the route at all.
 *
 * The property is set rather than the summary clicked: a click fires the summary's own handler,
 * and a summary is a button on some surfaces. Setting `open` changes only what the disclosure
 * shows, which is what a scan needs, and it is idempotent, so a scan that runs after a case
 * already opened a disclosure is unaffected.
 */
export async function openAllDisclosures(page: import('@playwright/test').Page): Promise<void> {
    await page.evaluate(() => {
        document.querySelectorAll('details').forEach((details) => {
            (details as HTMLDetailsElement).open = true;
        });
    });
}
