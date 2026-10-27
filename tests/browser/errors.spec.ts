import { test, expect } from '@playwright/test';

/*
 * Slice 11.3, the reachable half: the 404 document, end to end.
 *
 * The 419 and the 500 cannot be reached from a browser without expiring a session or crashing the
 * server, so their copy is pinned on the Blade documents in `tests/Feature/ErrorPageCopyTest.php`. This
 * file proves the one error a visitor actually lands on: the response is a real 404, the page answers
 * the data-loss question, offers one filled exit and two outline ones, and does not redirect them.
 */
test('a missing address lands on a 404 that answers the data-loss question and offers three exits', async ({ page }) => {
    const response = await page.goto('/this-address-does-not-exist', { waitUntil: 'domcontentloaded' });

    expect(response?.status(), 'the address did not answer 404').toBe(404);

    // Plain language as the headline; the code is the small eyebrow above it.
    await expect(page.getByRole('heading', { level: 1 })).toHaveText('That page does not exist');
    await expect(page.getByText(/Nothing was changed and nothing was lost/)).toBeVisible();

    // One filled action, three exits, and the exits lead somewhere real.
    await expect(page.locator('main a.bg-primary-action')).toHaveCount(1);
    await expect(page.locator('main a')).toHaveCount(3);
    await expect(page.getByRole('link', { name: 'Dashboard' })).toBeVisible();
    await expect(page.getByRole('link', { name: 'Careers' })).toBeVisible();
    await expect(page.getByRole('link', { name: 'Database' })).toBeVisible();

    // No automatic redirect: the address the visitor asked for is still the address they are on, and
    // nothing in the document refreshes itself.
    expect(new URL(page.url()).pathname).toBe('/this-address-does-not-exist');
    await expect(page.locator('meta[http-equiv]')).toHaveCount(0);
});
