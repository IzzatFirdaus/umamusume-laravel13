import { test, expect } from '@playwright/test';

/*
 * S9.3 — the six reference sub-pages share one pattern, and this file is the pattern's pin.
 *
 * What the HFW asks of every area page, asserted once per area rather than one bespoke case each:
 *
 *   - the same **sub-navigation strip** the hub carries, so "way back" is a strip and not a browser
 *     gesture (`database.html`);
 *   - the **breadcrumb** in the top bar naming `Database` and the area;
 *   - no second `h1` — the area page heads its own document.
 *
 * The per-page content (honesty columns, caveats, retry rules, provenance rows) is each page's own
 * spec: `database.spec.ts` for the hub, the Events/Races/ShopItems/Sparks cases, and the axe scans.
 * This file's subject is the shared frame, which is exactly what drifted when three of the nine areas
 * were missing the strip.
 */

const AREAS = [
    { path: '/database/sources', label: 'Data sources' },
    { path: '/database/scenarios', label: 'Scenarios' },
    { path: '/database/races', label: 'Races' },
    { path: '/database/events', label: 'Events' },
    { path: '/database/shop-items', label: 'Shop Items' },
    { path: '/database/sparks', label: 'Sparks' },
    { path: '/database/skills', label: 'Skills' },
    { path: '/database/supports', label: 'Support Cards' },
    { path: '/database/trainees', label: 'Trainees' },
];

for (const area of AREAS) {
    test(`${area.label}: carries the area strip and the Database breadcrumb`, async ({ page }) => {
        await page.goto(area.path, { waitUntil: 'domcontentloaded' });
        await page.locator('#app > *').first().waitFor();

        // One `h1` per page; the area's name is not restated as a second top-level heading.
        await expect(page.getByRole('heading', { level: 1 })).toHaveCount(1);

        // The shared strip, which is the hub's own nav component.
        await expect(page.getByRole('navigation', { name: 'Reference data areas' })).toBeVisible();

        // The top-bar crumb: Database, then the area. The crumb is one span (`data-shell-crumb`) with
        // a link for the ancestors and bold text for where the Trainer is, not a nav landmark.
        const crumb = page.locator('[data-shell-crumb]');
        await expect(crumb.getByRole('link', { name: 'Database' })).toBeVisible();
        await expect(crumb.getByText(area.label, { exact: true }).first()).toBeVisible();
    });
}

test('the hub still carries the nine-tile grid, the strip and the verification date', async ({ page }) => {
    await page.goto('/database', { waitUntil: 'domcontentloaded' });
    await page.locator('#app > *').first().waitFor();

    await expect(
        page.getByRole('navigation', { name: 'Reference data', exact: true }).getByRole('listitem'),
    ).toHaveCount(9);
    await expect(page.getByRole('navigation', { name: 'Reference data areas' })).toBeVisible();
    // A verification date, never "updated today".
    await expect(page.getByText(/verified 2026-09-27/i).first()).toBeVisible();
    await expect(page.getByText(/updated today/i)).toHaveCount(0);
});
