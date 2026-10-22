import { test, expect } from '@playwright/test';

/*
 * Route-level UI hygiene (plan 1.12).
 *
 * Three claims that can only be made of a rendered page, checked over every primary route:
 *
 *   1. Exactly one `h1`, and no `h2` repeating it. Two title-weight names of the same thing is the
 *      defect the PageHeader work removed from five screens; this keeps it removed.
 *   2. No build version string in the banner. The version left the chrome in slice 1.2 and must not
 *      come back.
 *   3. No legacy brand-chrome background on an app page. The error documents were repainted in 0.9
 *      and `bg-chrome` was the old shell fill; a page still carrying it has skipped the repaint.
 *
 * Every assertion reads an attribute or computed style, never a screenshot (Rule 5). A capture cannot
 * distinguish `title="…"` from its absence, which is exactly the mistake §16 records.
 *
 * The route names are the app's own: `/umamusume` is the trainee catalog and `/support-cards` is the
 * card catalog. The list carried `/catalog` and `/support-decks`, which the app does not serve, and a
 * 404 answers with no mounted app, so each of those two cases burned the whole test timeout and said
 * nothing. The status is asserted before the wait below, so a dead route names itself in one line
 * instead of timing out in silence.
 */

const ROUTES = [
    '/',
    '/training-runs',
    '/skills',
    '/veterans',
    '/database',
    '/review',
    '/preferences',
    '/support-cards',
    '/umamusume',
    '/training-runs/create',
];

for (const route of ROUTES) {
    test(`${route} keeps the one-arrival contract`, async ({ page }) => {
        const response = await page.goto(route, { waitUntil: 'domcontentloaded' });
        expect(response?.status(), `${route} must be a live route`).toBe(200);
        await page.locator('#app > *').first().waitFor();

        // 1. One h1, and it is not duplicated as an h2.
        const h1s = page.locator('h1');
        await expect(h1s, `${route} must have exactly one h1`).toHaveCount(1);

        const h1Text = ((await h1s.first().innerText()) || '').trim();
        if (h1Text !== '') {
            const repeated = page.locator('h2').filter({ hasText: new RegExp(`^${escapeRe(h1Text)}$`) });
            await expect(repeated, `${route} repeats its h1 as an h2`).toHaveCount(0);
        }

        // 2. No version string in the banner (slice 1.2 removed it).
        const banner = page.getByRole('banner');
        if ((await banner.count()) > 0) {
            await expect(banner).not.toContainText(/\bv\d+\.\d+\.\d+/);
        }

        // 3. No legacy chrome fill on the page body.
        const chromeFills = await page.evaluate(() => {
            const legacy = ['rgb(0, 33, 71)', 'rgb(0, 36, 85)'];

            return Array.from(document.querySelectorAll('main, main > *, header > div')).filter((el) =>
                legacy.includes(getComputedStyle(el).backgroundColor),
            ).length;
        });
        expect(chromeFills, `${route} still paints a legacy chrome fill`).toBe(0);
    });
}

function escapeRe(value: string): string {
    return value.replace(/[.*+?^${}()|[\]\\]/g, '\\$&');
}
