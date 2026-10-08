import { test, expect, type Page } from '@playwright/test';

/*
 * Runtime determinism for the global navigation and the page-level visit state (PRD NFR-8).
 *
 * These assert the two things `dashboard.spec.ts` cannot: that a *prefetch* is not mistaken for a
 * navigation, and that a settled navigation leaves nothing behind. Inertia fires the same `start`
 * event for a `Link`'s hover prefetch as for a real visit, and a prefetch superseded by the next one
 * is cancelled without ever firing `finish` — so a page-level handler that keys off `start` alone
 * both lights up on hover and can be left stranded. `resources/js/composables/useVisitState.ts` is
 * the one place that guards against it; these tests fail if the guard is dropped.
 *
 * Fixture strategy: read-only. Every route is a GET the seeded scratch database already answers, and
 * the slow routes are held with `page.route`, so no run is created and nothing is written.
 */

const SIDEBAR = '#primary-nav';

async function ready(page: Page, path = '/'): Promise<void> {
    await page.goto(path);
    await page.locator('#app > *').first().waitFor();
}

/** Hold a route so a prefetch's in-flight state is observable instead of raced away. */
async function hold(page: Page, pattern: string, ms: number): Promise<void> {
    await page.route(pattern, async (route) => {
        await new Promise((resolve) => setTimeout(resolve, ms));
        await route.continue();
    });
}

test('a hover prefetch never announces a page load', async ({ page }) => {
    await ready(page);
    await hold(page, '**/veterans', 1200);

    // The sidebar prefetches on hover (`AppLayout`'s `prefetch="hover"`, 75ms delay). The Dashboard is
    // not going anywhere, so nothing may be announced.
    await page.locator(SIDEBAR).getByRole('link', { name: 'Veterans' }).hover();
    await page.waitForTimeout(400);

    await expect(page.getByRole('status'), 'hovering a nav link announced a load').toHaveCount(0);

    await page.unroute('**/veterans');
});

test('an interrupted prefetch does not strand the loading state', async ({ page }) => {
    await ready(page);
    await hold(page, '**/veterans', 1200);
    await hold(page, '**/skills', 1200);

    const sidebar = page.locator(SIDEBAR);

    // The second hover cancels the first prefetch, which then never fires `finish`. Before the guard
    // that left the page announcing a load for good.
    await sidebar.getByRole('link', { name: 'Veterans' }).hover();
    await page.waitForTimeout(300);
    await sidebar.getByRole('link', { name: 'Skills' }).hover();
    await page.waitForTimeout(500);

    await expect(page.getByRole('status'), 'an interrupted prefetch stranded the loading state').toHaveCount(0);

    await page.unroute('**/veterans');
    await page.unroute('**/skills');
});

test('a settled navigation leaves no loading state behind', async ({ page }) => {
    await ready(page);

    await page.locator(SIDEBAR).getByRole('link', { name: 'Careers' }).click();
    await page.waitForURL(/\/training-runs$/);

    await expect(page.getByRole('status')).toHaveCount(0);
});

test('refreshing a route renders it in a single navigation', async ({ page }) => {
    await ready(page, '/training-runs');

    let navigations = 0;
    page.on('framenavigated', (frame) => {
        if (frame === page.mainFrame()) {
            navigations++;
        }
    });

    await page.reload();
    await page.locator('#app > *').first().waitFor();

    // A version mismatch or a redirect loop shows up here as more than the one reload.
    expect(navigations, 'the refresh reloaded more than once').toBe(1);
    await expect(page.getByRole('status')).toHaveCount(0);
});
