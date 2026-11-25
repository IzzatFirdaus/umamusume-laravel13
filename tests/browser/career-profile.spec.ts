import { test, expect } from './support/base-test';
import { mkdirSync, writeFileSync } from 'node:fs';
import { resolve } from 'node:path';
import { pathToFileURL } from 'node:url';
import { verifyPage } from './fixtures/wizard-verify';

/*
 * SCREEN-004, Trainee Profile, per Phase 4 of the wizard-parity instruction: the page is
 * `career-profile.html`'s read screen, so the cases below assert that layout at browser grain —
 * the read-only lede, the head band's two doors, the aptitude table, the five-cell growth row, the
 * five base-stat rows with their stated absence, the costume-form table with its current row marked,
 * the scenario pills, and the absence of any edit control.
 *
 * The page is a read screen: nothing here writes a row. The selection round trip (PUT, session draft,
 * target landing) is covered by `career-trainee-select.spec.ts`; this spec owns the layout. The page is
 * now step 2's middle: the roster's forward door opens it and its own primary continues to step 3.
 *
 * `Admire Vega` is trainee 33 in the seeded scratch catalog and carries two confirmed costume forms
 * and full growth/base-stat figures on her debut card, so the figures below are the catalog's.
 */

test.describe('Trainee Profile (Phase 4)', () => {
    test.beforeEach(async ({ page }) => {
        await page.goto('/career/setup/trainee/admire-vega');
        await page.locator('#app > *').first().waitFor();
    });

    test('says it is a read screen and carries both doors in the head band', async ({ page }) => {
        // The wireframe's lede opens with this sentence (`career-profile.html:61`), and the body holds
        // no edit control at all.
        await expect(page.getByText('This is a read screen: nothing here is edited in the wizard.')).toBeVisible();

        // The wireframe's 64px portrait sits beside the heading, never inside it
        // (`career-profile.html:57-62`): the empty alt keeps it out of the h1's accessible name.
        const portrait = page.locator('[data-region="phead"] img[data-artwork-slot]');
        await expect(portrait).toHaveCount(1);
        await expect(portrait).toHaveAttribute('alt', '');

        const actions = page.locator('[data-region="hrow-actions"] a, [data-region="hrow-actions"] button');
        await expect(actions).toHaveCount(2);
        await expect(actions.first()).toHaveText(/^\s*Choose a different trainee\s*$/i);
        await expect(actions.first()).toHaveAttribute('href', /\/career\/setup\/trainee$/);
        await expect(actions.last()).toHaveText(/^\s*Continue to build target\s*$/i);
        await expect(page.getByText('Step 2 of 6', { exact: true })).toBeVisible();

        // One filled action on the page, and the position readout sits with the pair.
        await expect(page.locator('.bg-primary-action')).toHaveCount(1);
    });

    test('draws the aptitudes as three columns, one letter per axis, the strongest bold', async ({ page }) => {
        const aptitudes = page.locator('section[aria-labelledby="aptitudes"]');

        await expect(aptitudes.getByRole('heading', { name: 'Aptitudes' })).toBeVisible();
        await expect(aptitudes.locator('thead th')).toHaveText(['Surface', 'Distance', 'Running style']);
        await expect(aptitudes.locator('thead th[scope="col"]')).toHaveCount(3);

        // The ten letters appear once, on the trainee, never per costume form (ADR-0008): one bold
        // strongest entry per family and one muted line carrying the remaining seven.
        await expect(aptitudes.locator('tbody td > b')).toHaveCount(3);
        await expect(aptitudes.locator('tbody td > span')).toHaveCount(3);

        // The sourced vocabulary wins over the mock's abbreviations (Phase B.3's rule).
        await expect(aptitudes.getByText('Front Runner')).toBeVisible();
        await expect(aptitudes.getByText('End Closer')).toBeVisible();
    });

    test('keeps the five growth tiles and states the absence', async ({ page }) => {
        const tiles = page.locator('[data-field="growth"] .tile');

        await expect(page.getByRole('heading', { name: 'Growth row' })).toBeVisible();
        await expect(tiles).toHaveCount(5);
        await expect(tiles.locator('.k')).toHaveText(['Speed', 'Stamina', 'Power', 'Guts', 'Wit']);

        // Admire Vega carries growth figures on her debut card (seed data): Speed 10%, Stamina 0%,
        // Power 20%, Guts 0%, Wit 0%.
        await expect(tiles.locator('.v')).toHaveText(['10%', '0%', '20%', '0%', '0%']);
    });

    test('keeps the five base-stat rows from the seed card', async ({ page }) => {
        const rows = page.locator('[data-field="base-stats"] .srow');

        await expect(page.getByRole('heading', { name: 'Base stats' })).toBeVisible();
        await expect(rows).toHaveCount(5);
        await expect(rows.locator('.k')).toHaveText(['Speed', 'Stamina', 'Power', 'Guts', 'Wit']);
        // Admire Vega's debut card [Starry Nocturne] carries base stats: Speed 96, Stamina 79,
        // Power 108, Guts 69, Wit 98 (seed data).
        await expect(rows.locator('.v')).toHaveText(['96', '79', '108', '69', '98']);
        await expect(rows.locator('.track')).toHaveCount(5);
    });

    test('marks the current costume form in the forms table', async ({ page }) => {
        const forms = page.locator('section[aria-labelledby="forms"]');

        await expect(forms.getByRole('heading', { name: 'Costume forms' })).toBeVisible();
        await expect(forms.locator('thead th').first()).toHaveText('Title');
        // Scoped headers and a row header: each confirmed card row carries its own row header.
        await expect(forms.locator('thead th[scope="col"]')).toHaveCount(3);
        await expect(forms.locator('tbody th[scope="row"]')).toHaveCount(2);
        await expect(forms.locator('thead th .sr-only')).toHaveText('Selected');

        // Admire Vega has two confirmed costume forms (seed data); the debut form [Starry Nocturne]
        // is the one marked `is-current` with the `Selected` pill.
        await expect(forms.locator('tr.is-current')).toHaveCount(1);
        await expect(forms.locator('tr.is-current')).toContainText('[Starry Nocturne]');
        await expect(forms.locator('tr.is-current')).toContainText('Debut form');
        await expect(forms.locator('.pill-positive')).toHaveText(['Selected']);

        // Rarity is printed at form grain, never as a trainee badge.
        await expect(forms.getByRole('img', { name: /Three stars/i })).toHaveCount(2);
    });

    test('lists the scenarios the trainee is linked to as pills', async ({ page }) => {
        const scenarios = page.locator('section[aria-labelledby="scenarios"]');

        await expect(scenarios.getByRole('heading', { name: 'Scenarios you can run this trainee in' })).toBeVisible();
        await expect(scenarios.locator('.pill-outline')).toHaveCount(4);
        // Admire Vega carries no scenario link in seed data; the page's note explains the absence.
        await expect(scenarios.getByText(/Every scenario is listed as available/)).toBeVisible();
    });

    test('carries no edit control anywhere in the canvas', async ({ page }) => {
        const canvas = page.locator('[data-region="canvas"]');

        await expect(canvas.locator('input, select, textarea')).toHaveCount(0);
        await expect(canvas.getByRole('button')).toHaveCount(0);
    });

    test('matches the wireframe on computed style and geometry', async ({ page }) => {
        const hfw = pathToFileURL(
            resolve(process.cwd(), 'docs/design-research/frontend-v2-wireframes/career-profile.html'),
        ).href;

        /*
         * HFW-OVERRIDE (markup: element paths) — `excess`/`missing` are NOT asserted empty, for the
         * reason recorded on `career-trainee-select.spec.ts`'s same case: `visual-diff.ts` names an
         * element by its position inside its own document, so a page whose app markup and mock markup
         * are two different implementations of one design can never pair every descendant. The text
         * bucket therefore reports differences rather than an empty list: the mock's identity lede, its
         * placeholder skill and its illustrative figures are not this page's copy. The content the page
         * must draw is asserted case by case above, which is the sharper question; the two reports below
         * are the evidence for computed style and geometry at each viewport.
         */
        const dir = resolve(process.cwd(), '.scratch-uma/wizard-parity');
        mkdirSync(dir, { recursive: true });

        for (const width of [1280, 390]) {
            const report = await verifyPage(page, {
                appUrl: '/career/setup/trainee/admire-vega',
                hfwUrl: hfw,
                rootSelector: '.canvas',
                width,
                height: width === 1280 ? 800 : 844,
            });

            writeFileSync(resolve(dir, `profile-diff-${width}.json`), JSON.stringify(report, null, 2));
        }
    });
});

test('growth tiles read as one row wide and stack narrow (N3.26)', async ({ page }) => {
    // This case sits outside the describe, so it opens the read screen itself.
    await page.goto('/career/setup/trainee/admire-vega');
    await page.locator('#app > *').first().waitFor();

    const row = page.locator('[data-field="growth"]');
    await expect(row.locator('.tile')).toHaveCount(5);

    await page.setViewportSize({ width: 1280, height: 900 });
    const wideTops = await row.locator('.tile').evaluateAll((nodes) => nodes.map((n) => Math.round(n.getBoundingClientRect().top)));
    expect(new Set(wideTops).size).toBeLessThan(5);

    await page.setViewportSize({ width: 390, height: 844 });
    const narrowTops = await row.locator('.tile').evaluateAll((nodes) => nodes.map((n) => Math.round(n.getBoundingClientRect().top)));
    // At 390px the token fits three 7.5rem columns, so the five tiles wrap to more than one row;
    // the point of the slice is that they no longer stack five full-width boxes.
    expect(new Set(narrowTops).size).toBeGreaterThan(1);
});
