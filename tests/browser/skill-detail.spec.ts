import { test, expect } from '@playwright/test';
import { removeScopeFixtures, seedScopeFixtures } from '../utils/seed-range-operator';

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

/*
 * C2 — the description panel's rule holds on the detail surface, at both scopes.
 *
 * The catalogue case lives in `skills.spec.ts`; this is the other surface of the same predicate and
 * a separate failure. `skills.spec.ts` reads a table cell, this reads `section[aria-labelledby="description"]`,
 * and `SkillController::skillPayload()` is a second payload built by a second method. The G1 widening
 * fixed a real defect here: `skillPayload()` was missing `release_status`, so the panel's predicate
 * compared `undefined` and printed the description for every row regardless of availability.
 *
 * The fixture pair is `seedScopeFixtures()`: a JapanOnly row whose `desc_en` IS set (so the absence is
 * the display path's decision, not the source's silence) and a Global row whose description is client
 * copy (so a page that printed the absence for everything fails too).
 */
test('the description panel refuses client prose on a Japan-only row, at any scope', async ({ page }) => {
    seedScopeFixtures();

    try {
        const panel = () => page.locator('section[aria-labelledby="description"]');

        // The JP row's detail, reached at `scope=japan_only`. Its id is read from the catalogue rather
        // than hardcoded: the fixture's id is assigned by SQLite and a recycled scratch file could
        // shift it.
        await test.step('at scope=japan_only', async () => {
            await page.goto('/skills?scope=japan_only&search=' + encodeURIComponent('an english sentence'));
            await page.locator('#app > *').first().waitFor();

            const href = await page
                .locator('#skill-results')
                .getByRole('link', { name: '掲げよ、己が魂の剣を！' })
                .getAttribute('href');
            expect(href, 'the JP fixture is reachable from the catalogue').toBeTruthy();

            await page.goto(href as string);
            await page.locator('#app > *').first().waitFor();

            // The panel prints exactly the named absence, byte for byte.
            await expect(panel().locator('.text-sm.text-ink')).toHaveText('No client string.');
            // And it does not print the source's sentence anywhere in the section.
            await expect(panel()).not.toContainText('The source states an English sentence for this row.');
            // The note says why the block is empty rather than naming a field it did not read.
            await expect(panel()).not.toContainText('From GameTora export');
        });

        // The same row reached at `scope=all`. The rule is the ROW's availability, not the scope the
        // URL carried: gating on scope would let `?scope=all` leak what `?scope=japan_only` refuses.
        await test.step('at scope=all', async () => {
            await page.goto('/skills?scope=all&search=' + encodeURIComponent('an english sentence'));
            await page.locator('#app > *').first().waitFor();

            const href = await page
                .locator('#skill-results')
                .getByRole('link', { name: '掲げよ、己が魂の剣を！' })
                .getAttribute('href');
            expect(href, 'the JP fixture is reachable at scope=all too').toBeTruthy();
            expect(href, 'the scope survives into the detail URL').toContain('scope=all');

            await page.goto(href as string);
            await page.locator('#app > *').first().waitFor();

            await expect(panel().locator('.text-sm.text-ink')).toHaveText('No client string.');
            await expect(panel()).not.toContainText('The source states an English sentence for this row.');
            await expect(panel()).not.toContainText('From GameTora export');
        });

        // The Global row's detail: client copy IS printed, with its source line. Without this the case
        // above would pass on a panel that showed the absence unconditionally.
        await test.step('a Global row prints its client string', async () => {
            await page.goto('/skills?search=' + encodeURIComponent('a client sentence'));
            await page.locator('#app > *').first().waitFor();

            const href = await page
                .locator('#skill-results')
                .getByRole('link', { name: 'Client Named Row' })
                .getAttribute('href');
            expect(href).toBeTruthy();

            await page.goto(href as string);
            await page.locator('#app > *').first().waitFor();

            await expect(panel().locator('.text-sm.text-ink')).toHaveText('A client sentence for the Global row.');
            await expect(panel()).toContainText('From GameTora export');
            await expect(panel()).not.toContainText('No client string.');
        });
    } finally {
        removeScopeFixtures();
    }
});
