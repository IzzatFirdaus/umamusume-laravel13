import { test, expect } from '@playwright/test';

/*
 * SCREEN-002, the skill detail, as a browser surface.
 *
 * `tests/browser/skills.spec.ts` covers the screen's copy and its parity with the wireframe; this
 * file holds the three cases the invariant self-check names as DOM-only:
 *
 *  - the description's source line (Inv-4/Inv-6),
 *  - an unknown `?compare=` id degrading to the normal page rather than a 404,
 *  - the differs marker carrying a word and a glyph rather than colour alone (Inv-9).
 *
 * It reads the seeded reference catalogue, which is what the harness builds (KI-69).
 */

test.describe.configure({ timeout: 240_000 });

test('prints the description source line when a client string exists', async ({ page }) => {
    await page.goto('/skills?search=gourmand');
    await page.locator('#app > *').first().waitFor();

    await page.locator('#skill-results').getByRole('link', { name: 'Gourmand' }).click();

    const panel = page.locator('section[aria-labelledby="description"]');
    await expect(panel).toBeVisible();

    // The prose is the export's own, and the note names the field it came from. A paraphrase or a
    // missing provenance line fails here.
    await expect(panel).toContainText('Recover endurance mid-race.');
    await expect(panel).toContainText('From GameTora export');
    await expect(panel).toContainText('desc_en');
});

test('an unknown compare id renders the normal page, not a 404', async ({ page }) => {
    await page.goto('/skills?search=gourmand');
    await page.locator('#app > *').first().waitFor();

    const href = await page.locator('#skill-results').getByRole('link', { name: 'Gourmand' }).getAttribute('href');
    const url = new URL(href as string, 'http://127.0.0.1');
    url.searchParams.set('compare', '99999999');

    const response = await page.goto(`${url.pathname}${url.search}`);

    expect(response?.status(), 'an unknown compare id must not 404').toBe(200);
    await expect(page.getByRole('heading', { name: 'Gourmand', level: 1 })).toBeVisible();

    // No compare panel: the param is ignored, not half-rendered.
    await expect(page.getByRole('heading', { name: /Compare with/ })).toHaveCount(0);
});

test('marks differing rows with the word and the glyph, never colour alone', async ({ page }) => {
    await page.goto('/skills?search=gourmand');
    await page.locator('#app > *').first().waitFor();

    const href = await page.locator('#skill-results').getByRole('link', { name: 'Gourmand' }).getAttribute('href');
    const url = new URL(href as string, 'http://127.0.0.1');

    // Gourmand is Recovery; Unstoppable (id 2) is Speed, so the Type row differs.
    url.searchParams.set('compare', '2');

    await page.goto(`${url.pathname}${url.search}`);

    const marker = page.locator('[data-differs]').first();
    await expect(marker).toBeVisible();
    await expect(marker).toContainText('differs');
    await expect(marker).toContainText('≠');

    // Not colour-only: the marker's own text carries the state, so it survives a greyscale render
    // and a screen reader hears it.
    await expect(marker).toHaveAttribute('data-differs', '');
});
