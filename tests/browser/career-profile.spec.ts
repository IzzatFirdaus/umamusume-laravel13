import { test, expect } from '@playwright/test';
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
 * `Admire Vega` is trainee 33 in the seeded scratch catalog and has one confirmed debut form, so the
 * cost/aptitude figures below are the catalog's.
 */

test.describe('Trainee Profile (Phase 4)', () => {
    test.beforeEach(async ({ page }) => {
        await page.goto('/career/setup/trainee/33');
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

        // The client prints a real zero as +0% (`UMAMUSUME_REFERENCE.md` §1.3.5); this catalogue holds
        // no growth figure, so every cell states the absence rather than a fabricated zero.
        await expect(tiles.locator('.v')).toHaveText(['N/A', 'N/A', 'N/A', 'N/A', 'N/A']);
        await expect(page.getByText(/No growth figure is stored for any costume form/)).toBeVisible();
    });

    test('keeps the five base-stat rows and states the absence', async ({ page }) => {
        const rows = page.locator('[data-field="base-stats"] .srow');

        await expect(page.getByRole('heading', { name: 'Base stats' })).toBeVisible();
        await expect(rows).toHaveCount(5);
        await expect(rows.locator('.k')).toHaveText(['Speed', 'Stamina', 'Power', 'Guts', 'Wit']);
        await expect(rows.locator('.v')).toHaveText(['N/A', 'N/A', 'N/A', 'N/A', 'N/A']);
        // The wireframe draws an empty rail per row; no fill is painted, so it never reads as zero.
        await expect(rows.locator('.track')).toHaveCount(5);
        await expect(page.getByText(/No base-stat figure is stored/)).toBeVisible();
        await expect(page.getByText(/sum to 450 for a three-star card/)).toBeVisible();
    });

    test('marks the current costume form in the forms table', async ({ page }) => {
        const forms = page.locator('section[aria-labelledby="forms"]');

        await expect(forms.getByRole('heading', { name: 'Costume forms' })).toBeVisible();
        await expect(forms.locator('thead th').first()).toHaveText('Title');
        // Scoped headers and a row header: the title cell names its own row, and the wireframe's blank
        // third column carries the "Selected" state word for a screen reader.
        await expect(forms.locator('thead th[scope="col"]')).toHaveCount(3);
        await expect(forms.locator('tbody th[scope="row"]')).toHaveCount(1);
        await expect(forms.locator('thead th .sr-only')).toHaveText('Selected');

        // The debut form is the row the card is read from, so it is the one the wireframe marks
        // `is-current` with the `Selected` pill.
        await expect(forms.locator('tr.is-current')).toHaveCount(1);
        await expect(forms.locator('tr.is-current')).toContainText('[Starry Nocturne]');
        await expect(forms.locator('tr.is-current')).toContainText('Debut form');
        await expect(forms.locator('.pill-positive')).toHaveText(['Selected']);

        // Rarity is printed at form grain, never as a trainee badge.
        await expect(forms.getByRole('img', { name: /Three stars/i })).toHaveCount(1);
    });

    test('lists the scenarios the trainee is linked to as pills', async ({ page }) => {
        const scenarios = page.locator('section[aria-labelledby="scenarios"]');

        await expect(scenarios.getByRole('heading', { name: 'Scenarios this trainee is linked to' })).toBeVisible();
        await expect(scenarios.locator('.pill-outline')).toHaveText([
            'URA Finale',
            'Unity Cup',
            'Trackblazer',
            'Our Grand Concert',
        ]);
        await expect(scenarios.getByText(/No per-trainee link list is carried/)).toBeVisible();
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
                appUrl: '/career/setup/trainee/33',
                hfwUrl: hfw,
                rootSelector: '.canvas',
                width,
                height: width === 1280 ? 800 : 844,
            });

            writeFileSync(resolve(dir, `profile-diff-${width}.json`), JSON.stringify(report, null, 2));
        }
    });
});
