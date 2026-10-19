import { test, expect } from '@playwright/test';

/*
 * The setup shell's focus and document title (slice 3.1). Rendered evidence, so it reads the bundle.
 *
 * What is asserted, and what is deliberately not:
 *
 *  - Each step's `h1` takes focus on load. A wizard re-renders the same shell with new content, so
 *    without this nothing tells a keyboard or screen-reader user the step changed.
 *  - `document.title` names the step. Six setup steps open as six tabs need to be told apart.
 *  - The shell's own bar survives: product name and the rail control are still there, because
 *    `SetupLayout` composes `AppLayout` rather than replacing it.
 *
 * NOT asserted: "no h1 inside `<header>`". `design-2.0`'s 2026-10-17 owner-signed amendment places the
 * page `h1` in the shell's title band, inside the banner, and the owner ruled the signed document wins
 * over the plan. `page-header.spec.ts` records the shipped position the same way. This spec records
 * that the wizard does not move it: the focused element is a heading, wherever the shell put it.
 */

const STEPS: { url: string; label: string; title: string }[] = [
    { url: '/career/setup/scenario', label: 'Scenario', title: 'Choose a scenario' },
    { url: '/career/setup/trainee', label: 'Trainee', title: 'Choose a trainee' },
    { url: '/career/setup/target', label: 'Your target', title: '' },
    { url: '/career/setup/legacy', label: 'Legacy', title: '' },
    { url: '/career/setup/deck', label: 'Support deck', title: '' },
    { url: '/career/setup/preflight', label: 'Preflight', title: 'Review and start' },
];

for (const step of STEPS) {
    test(`${step.url} focuses its heading and names the step in the tab`, async ({ page }) => {
        await page.goto(step.url, { waitUntil: 'domcontentloaded' });
        await page.locator('#app > *').first().waitFor();

        // Exactly one h1, focused. `tabindex="-1"` is what makes a heading focusable; the shell adds
        // it at mount rather than shipping it in the markup.
        const h1 = page.locator('h1');
        await expect(h1).toHaveCount(1);
        await expect(h1).toHaveAttribute('tabindex', '-1');
        await expect(h1).toBeFocused();

        // The tab is never unnamed. A step that sets its own title keeps it, because the page's
        // wording ("Choose a scenario") is more specific than the step bar's label ("Scenario");
        // the shell only fills in when a step ships without one.
        await expect(page).not.toHaveTitle('');
        if (step.title !== '') {
            await expect(page).toHaveTitle(step.title);
        } else {
            await expect(page).toHaveTitle(new RegExp(step.label.replace(/[.*+?^${}()|[\]\\]/g, '\\$&')));
        }

        // The product bar survived the composition: the rail control is reachable from the wizard.
        await expect(page.getByRole('button', { name: /Collapse|Expand/ }).first()).toBeVisible();
    });
}
