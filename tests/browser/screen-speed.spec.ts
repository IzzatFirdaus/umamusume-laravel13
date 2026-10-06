/*
 * Two load-speed contracts that only a browser can prove (plan §12, `AGENTS.md` §9's Vue row).
 *
 * Neither is reachable from the PHP suite. A feature test can assert that `artwork.show` *sends* a
 * `Cache-Control`, but it cannot show that a second visit reuses the frame instead of asking again, and
 * a build log can report chunk sizes without saying what one screen actually has to download before it
 * paints. Both halves here are the owner's reported symptoms: frames reloading on every screen change,
 * and slow screens.
 *
 * These are budgets, so they are written as ceilings with the measured number in the message. A ceiling
 * that a change breaks fails loudly; a ceiling nobody re-measures is decoration.
 */

import { expect, test } from '@playwright/test';

/** How many times each `/artwork/` url was asked for. A cache hit is a url that never appears twice. */
function artworkRequests(page: import('@playwright/test').Page): Map<string, number> {
    const counts = new Map<string, number>();

    page.on('request', (request) => {
        const url = request.url();

        if (url.includes('/artwork/')) {
            counts.set(url, (counts.get(url) ?? 0) + 1);
        }
    });

    return counts;
}

test.describe('Screen load speed', () => {
    test('asks for each mirrored frame once, not once per visit', async ({ page }) => {
        // Installed before the first navigation: a listener attached after a page has loaded misses
        // exactly the requests it exists to count.
        const requests = artworkRequests(page);

        await page.goto('/umamusume');
        await page.locator('#app > *').first().waitFor();

        const firstPass = [...requests.keys()];

        // An empty mirror would pass this test by having nothing to reuse, so the mirror has to hold at
        // least one frame before the absence of a second request means anything.
        expect(firstPass.length, 'no artwork frame was requested, so reuse is unproven').toBeGreaterThan(0);

        // A client-side visit, then a full reload: the two ways a Trainer leaves and returns.
        await page.getByRole('link', { name: 'Support Cards' }).first().click();
        await page.locator('#app > *').first().waitFor();

        await page.goto('/umamusume');
        await page.locator('#app > *').first().waitFor();

        const repeated = firstPass.filter((url) => (requests.get(url) ?? 0) > 1);

        expect(
            repeated,
            `${repeated.length} of ${firstPass.length} frames were fetched again after a visit and a reload`,
        ).toHaveCount(0);
    });

    test('sends one screen rather than every screen in the initial bundle', async ({ page }) => {
        const scripts: { url: string; bytes: number }[] = [];

        page.on('response', async (response) => {
            const url = response.url();

            if (!url.includes('/build/') || !url.endsWith('.js')) {
                return;
            }

            const length = Number(response.headers()['content-length'] ?? 0);

            if (length > 0) {
                scripts.push({ url, bytes: length });
            }
        });

        await page.goto('/');
        await page.locator('#app > *').first().waitFor();

        // The eager page glob used to put every screen in one 602,189-byte file. Lazily resolving the
        // same glob keeps the nested-path fix intact and lets Vite emit a chunk per page, so what the
        // Dashboard downloads is the shell plus the Dashboard.
        expect(scripts.length, 'no build scripts were measured').toBeGreaterThan(1);

        const total = scripts.reduce((sum, script) => sum + script.bytes, 0);

        expect(
            total,
            `one screen downloaded ${total} bytes of JavaScript across ${scripts.length} chunks: ${scripts
                .map((script) => `${script.url.split('/').pop()}=${script.bytes}`)
                .join(', ')}`,
        ).toBeLessThan(400_000);
    });

    test('prefetches a hovered destination, and leaves the draft flow alone', async ({ page }) => {
        const askedFor: string[] = [];

        page.on('request', (request) => {
            const url = request.url();

            // The document itself is a same-url request too; only Inertia visits carry the header, and
            // those are the ones a prefetch produces.
            if (request.headers()['x-inertia'] === 'true') {
                askedFor.push(url);
            }
        });

        await page.goto('/');
        await page.locator('#app > *').first().waitFor();

        await page.locator('aside').getByRole('link', { name: 'Veterans' }).hover();

        // Hover must fetch the destination without navigating to it, so the click that follows is a
        // cache read rather than a round trip on the single-process server.
        await expect
            .poll(() => askedFor.filter((url) => url.includes('/veterans')).length, { timeout: 5_000 })
            .toBeGreaterThan(0);

        expect(page.url(), 'prefetch must not move the Trainer off the screen they were on').toMatch(/\/$/);

        // The one exclusion, proven rather than asserted in a comment: this destination renders
        // session-draft state, so reusing a 30-second-old prefetch of it would show a choice the
        // Trainer has just changed.
        const before = askedFor.length;

        await page.locator('aside').getByRole('link', { name: 'New Career' }).hover();
        await page.waitForTimeout(1_200);

        expect(
            askedFor.slice(before).filter((url) => url.includes('/career/setup/scenario')),
            'the wizard entry was prefetched, so a stale draft could be served',
        ).toHaveCount(0);
    });

    test('keeps a nested page reachable, which is why this is a glob and not a template import', async ({
        page,
    }) => {
        // `Preferences/Edit` is two segments deep. The original comment on the glob records that a
        // dynamic `import()` template matched only one segment and mounted blank, and lazy chunks are
        // exactly where that regression would come back.
        await page.goto('/preferences');
        await page.locator('#app > *').first().waitFor();

        await expect(page.getByRole('heading', { level: 1 }).first()).toBeVisible();
        await expect(page.locator('aside')).toBeVisible();
    });
});
