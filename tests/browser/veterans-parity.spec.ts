/*
 * Structural parity for SCREEN-021 (`/veterans`) against its high-fidelity wireframe
 * (`docs/design-research/frontend-v2-wireframes/veterans.html`).
 *
 * What this proves that a screenshot cannot: the page's *regions* appear in the wireframe's order -
 * the filter row, its three field slots, the table card, the picture slot a row reserves, the pager
 * inside that card, and the two-card lower band with its tiles. `assertStructurallyEqual` compares
 * ordered markers, not pixels, so it stays honest about the two places the product deliberately
 * differs from the mock (a `HFW-OVERRIDE` at each call site in `pages/Veterans/Index.vue`):
 *
 *   1. the filter row carries three controls the static mock does not draw (tag, order, submit);
 *   2. each row keeps a second muted line for the run's state, tags, notes and doors.
 *
 * The wireframe's `.keybox` (its annotation key) is not in `PAGE_MARKERS`, so it is ignored rather
 * than demanded: annotation apparatus is not product UI.
 *
 * `regions: 'subset'` is deliberate. The app renders more markers than the mock in one place (each
 * table row reserves its own picture slot) and fewer in none; subset means "every marker the
 * wireframe draws exists, in order" without pretending the two documents were the same file.
 */

import { expect, test } from '@playwright/test';
import { resolve } from 'node:path';
import { pathToFileURL } from 'node:url';
import { PAGE_MARKERS, assertStructurallyEqual, capture } from './fixtures/hfw-structure';
import { deleteRun } from '../utils/delete-run';
import { fileCareer } from '../utils/file-career';

const WIREFRAME = pathToFileURL(
    resolve('docs/design-research/frontend-v2-wireframes/veterans.html'),
).href;

/** Runs filed by this spec, torn down after each case so the shared scratch database is unchanged. */
const createdRunUrls: string[] = [];

test.afterEach(async ({ page }) => {
    while (createdRunUrls.length > 0) {
        await deleteRun(page, createdRunUrls.pop() as string);
    }
});

test.describe('Veteran library structural parity', () => {
    test('renders the wireframe regions in the wireframe order at 1280', async ({ page }) => {
        // A filed career, so the table renders its rows rather than the empty state: the mock draws the
        // filled library, and an empty page would correctly fail this comparison.
        // Five rows, because the wireframe draws five: `slot` resolves once per row, so a one-row
        // library would correctly report four missing picture slots.
        for (const [trainee, scenario] of [
            ['Agnes Digital', 'ura_finale'],
            ['Eishin Flash', 'unity_cup'],
            ['Gold Ship', 'ura_finale'],
            ['Mejiro McQueen', 'unity_cup'],
            ['Tokai Teio', 'ura_finale'],
        ] as const) {
            createdRunUrls.push(await fileCareer(page, trainee, null, null, scenario));
        }

        await page.setViewportSize({ width: 1280, height: 900 });
        await page.goto('/veterans', { waitUntil: 'domcontentloaded' });
        await page.locator('#app > *').first().waitFor();

        const app = await capture(page, 'main', { markers: PAGE_MARKERS });

        await page.goto(WIREFRAME);
        const hfw = await capture(page, '.canvas', { markers: PAGE_MARKERS });

        assertStructurallyEqual(app, hfw, { regions: 'subset', grid: true });
    });

    test('collapses to one column at 390 with no page-level horizontal overflow', async ({ page }) => {
        createdRunUrls.push(await fileCareer(page, 'Agnes Digital', 'Speed'));

        await page.setViewportSize({ width: 390, height: 844 });
        await page.goto('/veterans', { waitUntil: 'domcontentloaded' });
        await page.locator('#app > *').first().waitFor();

        // The lower band is two cards above 820 and one below: the wireframe's `g-2` band, on the app's
        // own breakpoint (§2.3: desktop first, one column under the small tablet).
        const grid = page.locator('.grid').filter({ has: page.getByRole('heading', { name: 'Factor grades' }) });
        const columns = await grid.evaluate((el) => getComputedStyle(el).gridTemplateColumns.split(' ').length);
        expect(columns, 'the lower band must be one column at 390').toBe(1);

        // The table keeps its own focusable scroll region (`DESIGN.md` §2.3, KI-25) rather than pushing
        // the page: the document itself must not scroll sideways.
        // The page body is the thing this slice owns. `main` must fit the viewport; the table keeps its
        // own focusable scroll region inside the card (`DESIGN.md` §2.3, KI-25).
        const bodyOverflow = await page.evaluate(() => {
            const main = document.querySelector('main');

            return main === null ? 0 : main.scrollWidth - main.clientWidth;
        });
        expect(bodyOverflow, 'the page body must not scroll horizontally at 390').toBeLessThanOrEqual(1);

        const overflow = { px: await page.evaluate(() => document.documentElement.scrollWidth - document.documentElement.clientWidth) };

        // A page is responsible for what it renders, not for the shell's own chrome: measure the
        // untouched `/umamusume` catalog at the same width and require this page to add nothing.
        // Whatever the shell already overflows (a fixed bar's min-content width, today) stays the shell's
        // finding, recorded in the report rather than hidden by a page-level threshold.
        await page.goto('/umamusume', { waitUntil: 'domcontentloaded' });
        await page.locator('#app > *').first().waitFor();
        const baseline = await page.evaluate(
            () => document.documentElement.scrollWidth - document.documentElement.clientWidth,
        );

        expect(
            overflow.px,
            `Veterans adds ${overflow.px - baseline}px of document overflow beyond the /umamusume baseline of ${baseline}px`,
        ).toBeLessThanOrEqual(baseline + 1);
    });
});
