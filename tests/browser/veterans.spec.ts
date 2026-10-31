/*
 * The Veteran library and the mobile navigation bar (`SCREEN-021`, design-2.0 §41).
 *
 * Two claims live here and neither is reachable from the PHP suite. The first is the shape of the bar a
 * Trainer meets at phone width: design-2.0 §41 asks for Home, Career, Legacy, Deck and More, and forbids
 * shrinking the desktop sidebar onto mobile. The sidebar used to be rendered whole inside an
 * `overflow-x-auto` strip, which is why nothing at 320px looked scrollable: a horizontal-only scroller
 * under a vertical gesture shows no affordance and answers no wheel. The second claim is the library's
 * empty state, which is the state a fresh install actually shows: the scratch database holds no Veterans
 * and no career has been filed into it on this database.
 *
 * Axe coverage is provided by `tests/browser/accessibility.spec.ts`: `@axe-core/playwright` is installed
 * and scans pages against `wcag2a`, `wcag2aa`, and `wcag21aa`; this spec retains the hand-rolled checks
 * for target size, keyboard path, focus order, reflow and console errors.
 * The first two claims above and the empty-library case write nothing. The card-shape cases at the end DO
 * file careers (`tests/utils/file-career.ts`), because a paragraph that appears once is only observable on a
 * library that is not empty; they tear their runs down in `afterEach`, so the empty-state case still meets
 * an empty table whenever it runs. This spec's writes are all cleaned up before the next test starts.
 */

import { expect, test } from '@playwright/test';
import { deleteRun } from '../utils/delete-run';
import { fileCareer } from '../utils/file-career';
import { VETERAN_ANCESTRY_NOTE } from '../../resources/js/domain/veteranNote';

test.describe('Mobile navigation', () => {
    test('shows five slots at 320px, not a scrolling copy of the sidebar', async ({ page }) => {
        await page.setViewportSize({ width: 320, height: 720 });
        await page.goto('/');
        await page.locator('#app > *').first().waitFor();

        const bar = page.getByRole('navigation', { name: 'Primary mobile' });

        // The four destinations, and More as the disclosure. The brief's word for the slot is the
        // accessible name, so the count is the claim rather than a label list kept in two places. `More`
        // is a native summary, not a link, so it is asserted by text rather than by role.
        for (const slot of ['Home', 'Career', 'Legacy', 'Deck']) {
            await expect(bar.getByRole('link', { name: slot, exact: true })).toBeVisible();
        }

        await expect(bar.getByText('More')).toBeVisible();

        await expect(bar.getByRole('link', { name: 'Support Cards' })).toHaveCount(0);

        // The document must not scroll sideways (WCAG 1.4.10), which the old strip did not guarantee.
        const overflow = await page.evaluate(
            () => document.documentElement.scrollWidth - document.documentElement.clientWidth,
        );
        expect(overflow, 'the document scrolls horizontally at 320px').toBeLessThanOrEqual(1);
    });

    test('opens More to reach the destinations the bar does not carry', async ({ page }) => {
        await page.setViewportSize({ width: 320, height: 720 });
        await page.goto('/');
        await page.locator('#app > *').first().waitFor();

        const bottom = page.getByRole('navigation', { name: 'Primary mobile' });
        const more = bottom.getByText('More');

        // Closed by default, and the hidden destinations are not in the tab order while it is shut.
        await expect(bottom.getByRole('link', { name: 'Veterans' })).not.toBeVisible();

        await more.click();
        await expect(bottom.getByRole('link', { name: 'Veterans' })).toBeVisible();
        await expect(bottom.getByRole('link', { name: 'Settings' })).toBeVisible();

        // Escape closes the disclosure and puts focus back where the Trainer opened it.
        await bottom.getByRole('link', { name: 'Veterans' }).focus();
        await page.keyboard.press('Escape');
        await expect(bottom.getByRole('link', { name: 'Veterans' })).not.toBeVisible();
        await expect(more).toBeVisible();
    });

    test('keeps every desktop destination live, with no named absence left in the nav', async ({ page }) => {
        await page.goto('/');
        await page.locator('#app > *').first().waitFor();

        const aside = page.locator('aside');

        // Every desktop destination is a live link. The count used to be a ten-item shortcut, but
        // the aside also carries a "Data sources" footer link to /database/sources, so a bare count
        // is brittle — it breaks when any destination is added or removed. Enumerate the
        // destinations that must exist instead.
        for (const destination of [
            'Dashboard',
            'New Career',
            'Careers',
            'Legacy Lab',
            'Veterans',
            'Support Cards',
            'Skills',
            'Review',
            'Database',
            'Settings',
            'Data sources',
        ]) {
            await expect(aside.getByRole('link', { name: destination, exact: true })).toBeVisible();
        }
        await expect(aside.getByRole('link', { name: 'Veterans' })).toHaveAttribute('href', '/veterans');
        await expect(aside.getByRole('link', { name: 'New Career' })).toHaveAttribute(
            'href',
            '/career/setup/scenario',
        );
        // Nothing in the product nav is "not built" any more, so the phrase must not appear at all.
        await expect(aside.getByText('not built')).toHaveCount(0);
    });
});

test.describe('Veteran library', () => {
test('says the library holds nothing filed rather than showing a blank list', async ({ page }) => {
        await page.goto('/veterans');
        await page.locator('#app > *').first().waitFor();

        await expect(page.getByRole('heading', { name: 'Veteran library', exact: true }).first()).toBeVisible();
        // Plan 2.5: the record-only statement lives once, on the builder, as a help popover. The
        // library does not repeat it.
        await expect(page.getByText('Record only.')).toHaveCount(0);
        await expect(page.getByText('The library holds no Veterans yet.')).toBeVisible();
        // The reason and the door, not an empty box. Save Veteran landed with D16's write half, so the
        // empty library is "nothing filed yet" with a route to filing rather than a wait for a slice.
        await expect(page.getByText(/A Veteran is a completed career filed into the library/)).toBeVisible();
        await expect(page.getByRole('link', { name: 'Open the run list' })).toBeVisible();
        // `SCREEN-021`'s parent search, which is a search rather than an optimizer.
        await expect(page.getByRole('link', { name: 'Find parents for this build' })).toBeVisible();

        // The three facets the query answers, and no others.
        // The wireframe's filter row (`veterans.html:71-75`): a name search where the roster select
        // used to be, then the scenario facet, the tag field and Clear.
        await expect(page.getByLabel('Search')).toBeVisible();
        await expect(page.getByRole('combobox', { name: 'Scenario' })).toBeVisible();
        await expect(page.getByRole('textbox', { name: 'Tag' })).toBeVisible();
        await expect(page.getByRole('button', { name: 'Filter' })).toBeVisible();
        await expect(page.getByRole('link', { name: 'Clear' })).toBeVisible();


        // The brief's unsupportable features are stated as absences with their reason on the page.
        // The "Not in this build" section is now a collapsible <details> element.
        await expect(page.getByText('Not in this build')).toBeVisible();
        // Expand the details to check the absence notes
        await page.locator('details summary:has-text("Not in this build")').click();
        await expect(page.getByText(/Favorite, archive, delete/)).toBeVisible();
        await expect(page.getByText(/no sourced table/i).or(page.getByText(/No sourced table/))).toBeVisible();
    });

    test('orders the library through the bar rather than the filter form', async ({ page }) => {
        await page.goto('/veterans');
        await page.locator('#app > *').first().waitFor();

        const oldest = page.getByRole('button', { name: 'Oldest first' });
        await oldest.click();
        await page.locator('#app > *').first().waitFor();
        await expect(page).toHaveURL(/order=oldest/);
        await expect(oldest).toHaveAttribute('aria-current', 'true');

        // The toggle is a button pair, not a fourth select: a date sort would be a claim the store cannot
        // support, so nothing here pretends to order by completion.
        await expect(page.getByRole('combobox', { name: /order/i })).toHaveCount(0);
    });

    test('collapses the rail to marks and keeps every destination named', async ({ page }) => {
        await page.setViewportSize({ width: 1280, height: 800 });
        await page.goto('/');
        await page.locator('#app > *').first().waitFor();

        const aside = page.locator('aside');

        // Anchored on the region it controls rather than on its own name, because the name is the action
        // and the action changes when the rail collapses: a locator by "Collapse navigation" dissolves the
        // moment it succeeds.
        const toggle = page.locator('button[aria-controls="primary-nav-rail"]');

        await toggle.focus();
        await expect(toggle).toHaveAccessibleName('Collapse navigation');
        await expect(toggle).toHaveAttribute('aria-expanded', 'true');

        const widthBefore = (await aside.boundingBox())?.width ?? 0;

        // The owner asked for the marks at one width only: the full rail is words, and a mark never sits
        // beside the word it stands for. The toggle's own chevron lives in the rail head, so the
        // destination-mark count is scoped to the nav.
        await expect(aside.locator('nav svg')).toHaveCount(0);
        await expect(toggle.locator('svg')).toHaveCount(1);
        await expect(aside.locator('[data-rail-dot]')).toHaveCount(10);

        const markBefore = await aside.locator('[data-rail-mark]').boundingBox();
        const toggleBefore = await toggle.boundingBox();
        expect(toggleBefore?.y ?? 0).toBeLessThan((markBefore?.y ?? 0) + (markBefore?.height ?? 0));
        expect(markBefore?.y ?? 0).toBeLessThan((toggleBefore?.y ?? 0) + (toggleBefore?.height ?? 0));

        await toggle.press('Enter');

        // Collapsed, each destination carries its mark and the word moves to `sr-only`: ten. The dot
        // spacers leave the painted column; the head stacks the tile above the toggle so both fit 56px.
        await expect(aside.locator('nav svg')).toHaveCount(10);
        await expect(toggle.locator('svg')).toHaveCount(1);
        await expect(aside.locator('[data-rail-dot]')).toHaveCount(0);

        const markAfter = await aside.locator('[data-rail-mark]').boundingBox();
        const toggleAfter = await toggle.boundingBox();
        expect(toggleAfter?.y ?? 0).toBeGreaterThanOrEqual((markAfter?.y ?? 0) + (markAfter?.height ?? 0));

        await expect(toggle).toHaveAttribute('aria-expanded', 'false');
        await expect(toggle).toHaveAccessibleName('Expand navigation');

        const widthAfter = (await aside.boundingBox())?.width ?? 0;
        expect(widthAfter, 'the rail did not narrow').toBeLessThan(widthBefore);

        // Collapsed means marks, but nothing loses its name: each destination is still reachable by
        // its own word, which is the difference between an icon rail and ten anonymous links.
        for (const label of ['Dashboard', 'New Career', 'Careers', 'Legacy Lab', 'Veterans', 'Settings']) {
            const link = aside.getByRole('link', { name: label, exact: true });
            await expect(link).toBeVisible();
            await expect(link).toHaveAttribute('title', label);
        }

        // The current page still reads as current, and by more than colour: the collapsed active item
        // carries the HFW's 3px inset rule as well as its fill. The rule is a child span, so the
        // label does not shift when it appears.
        const current = aside.getByRole('link', { name: 'Dashboard', exact: true });
        await expect(current).toHaveAttribute('aria-current', 'page');
        const rule = current.locator('[data-rail-current]');
        await expect(rule).toHaveCount(1);
        expect(await rule.evaluate((el) => getComputedStyle(el).width)).toBe('3px');
        expect(await current.evaluate((el) => getComputedStyle(el).borderLeftWidth)).toBe('0px');

        // The choice outlives the visit. It lives in the browser, not in a preference row, so a reload
        // must carry it and a fresh context must not.
        await page.reload();
        await page.locator('#app > *').first().waitFor();
        await expect(page.getByRole('button', { name: 'Expand navigation' })).toHaveAttribute(
            'aria-expanded',
            'false',
        );
    });

    test('carries the product navigation onto the wizard screens', async ({ page }) => {
        await page.goto('/career/setup/scenario');
        await page.locator('#app > *').first().waitFor();

        // The wizard keeps its step bar as a sub-nav and now inherits the shell, so both landmarks exist
        // and the library is one click from step 1.
        await expect(page.getByRole('navigation', { name: 'Setup steps' })).toBeVisible();
        await expect(page.locator('aside').getByRole('link', { name: 'Veterans' })).toBeVisible();

        // One main region: the wizard must not re-declare the shell's landmarks.
        await expect(page.getByRole('main')).toHaveCount(1);
        await expect(page.getByRole('link', { name: 'Skip to content' })).toHaveCount(1);
        await expect(page.getByRole('link', { name: 'Cancel' })).toBeVisible();
    });
});

const createdRunUrls: string[] = [];

test.afterEach(async ({ page }) => {
    for (const url of createdRunUrls.splice(0)) {
        await deleteRun(page, url);
    }
});

test.describe('Veteran rows', () => {
    /**
     * Files a career, opens the library and returns the row it landed in. The library only shows a
     * non-empty list once something is filed, so every case below starts by putting one there.
     *
     * The row is a `<tr>` (`veterans.html:83-113`); `[data-veteran-row]` is the stable hook both the
     * app and these cases read, because a table row has no role to filter on by text.
     */
    async function openLibraryRow(
        page: import('@playwright/test').Page,
        trainee: string,
        tag: string | null,
        notes: string | null = null,
    ) {
        createdRunUrls.push(await fileCareer(page, trainee, tag, notes));

        await page.goto('/veterans', { waitUntil: 'domcontentloaded' });
        await page.locator('#app > *').first().waitFor();

        return page.locator('[data-veteran-row]').filter({ hasText: trainee });
    }

    /**
     * A note in the shape the legacy tracker imported: this career's own clause, then the one sentence
     * every tracker career ends with. `RecordVeteran` never writes that sentence, so a browser case can
     * only reach the split by filing it, and the split is what Phase 8.1 changes.
     */
    const trackedNote = (clause: string): string => `${clause} ${VETERAN_ANCESTRY_NOTE}`;

    test('states the shared ancestry sentence once above the list, not once per row', async ({ page }) => {
        await openLibraryRow(page, 'Agnes Digital', 'Speed', trackedNote('Career finished, Vodka 1st place.'));
        await openLibraryRow(page, 'Eishin Flash', null, trackedNote('Career ended, URA Finale Finals won 1st.'));

        const ancestry = page.getByText(/Legacy ancestry unrecorded:/);

        // One statement for the whole list (Phase 8.1), and it sits outside every row: the sentence is
        // about what the tracker did not record, which is the same for every row here.
        await expect(ancestry).toHaveCount(1);
        expect(await ancestry.evaluate((element) => element.closest('tr') !== null)).toBe(false);

        // Each row keeps only the clause that is its own, and none of them repeats the sentence the list
        // just stated. A row printing the stored note verbatim would fail this twice over.
        await expect(page.locator('[data-veteran-row]').filter({ hasText: 'Career finished, Vodka 1st place.' })).toHaveCount(1);
        await expect(page.locator('[data-veteran-row]').filter({ hasText: 'Career ended, URA Finale Finals won 1st.' })).toHaveCount(1);
        expect(
            await page.locator('[data-veteran-row]').evaluateAll((rows) =>
                rows.filter((row) => row.textContent?.includes('Legacy ancestry unrecorded:') ?? false).length,
            ),
            'a row still repeats the sentence the list states once',
        ).toBe(0);
    });

    test('says why there are no tags instead of printing a bare N/A', async ({ page }) => {
        const row = await openLibraryRow(page, 'Agnes Digital', null);

        // The plan's reason channel: the absence states itself, and carries a channel for why.
        await expect(row.getByText('No tags recorded')).toBeVisible();
        await expect(row.getByText(/^N\/A/)).toHaveCount(0);

        // And it disappears on a career that does carry one, so it is a state rather than a caption.
        await expect(page.getByText('No tags recorded')).toHaveCount(1);
    });

    test('keeps the compare door in the row it belongs to, exactly once', async ({ page }) => {
        const row = await openLibraryRow(page, 'Agnes Digital', 'Speed');

        // Proximity (Law 6): the comparison door is read as belonging to the row the Trainer is looking
        // at rather than as a footnote under the table.
        await expect(row.getByRole('link', { name: 'Compare', exact: true })).toHaveCount(1);

        // One per row, not one per page: a second filed career must carry its own.
        await openLibraryRow(page, 'Eishin Flash', null);
        await expect(page.getByRole('link', { name: 'Compare', exact: true })).toHaveCount(2);
    });

    test('renders a portrait or the reserved absence cell on every veteran row', async ({ page }) => {
        // One filed career is enough to have a row to inspect; whether the mirror holds its file decides
        // which of the component's two states the row shows, and both are accepted here.
        await openLibraryRow(page, 'Agnes Digital', 'Speed');

        const rows = page.locator('[data-veteran-row]');
        const count = await rows.count();
        expect(count).toBeGreaterThan(0);

        for (let i = 0; i < count; i++) {
            const slot = rows.nth(i).locator('[data-artwork-slot]');
            await expect(slot).toHaveCount(1);

            // A mirrored file is an `<img>` with a real src, and decorative (`alt=""`) because the row
            // prints the trainee name in the link beside it (`design-2.0` §42). An absent one is the
            // reserved cell: it paints nothing and is `aria-hidden`, so there is no broken-image glyph.
            const isImage = await slot.evaluate((el) => el.tagName.toLowerCase() === 'img');

            if (isImage) {
                await expect(slot).toHaveAttribute('src', /.+/);
                await expect(slot).toHaveAttribute('alt', '');
            } else {
                await expect(slot).toHaveAttribute('aria-hidden', 'true');
            }
        }
    });

    test('states the spark tallies as counts under their own unit', async ({ page }) => {
        await openLibraryRow(page, 'Agnes Digital', 'Speed');

        // The count-for-grade substitution (HFW-OVERRIDE (columns: spark counts, not grades)) is only
        // honest while the unit is printed: three headers say what the numbers are, and every cell is one
        // bare integer - never a star, never a compound, never a dash (ADR-0020 §3).
        await expect(page.getByRole('columnheader', { name: 'Blue sparks' })).toBeVisible();
        await expect(page.getByRole('columnheader', { name: 'Pink sparks' })).toBeVisible();
        await expect(page.getByRole('columnheader', { name: 'Unique sparks' })).toBeVisible();

        const counts = page.locator('[data-veteran-row]').first().locator('td[data-spark]');
        await expect(counts).toHaveCount(3);

        for (const cell of await counts.all()) {
            await expect(cell).toHaveText(/^\d+$/);
        }
    });

    test('collapses the notes that are not in this build by default', async ({ page }) => {
        await page.goto('/veterans');
        await page.locator('#app > *').first().waitFor();

        const summary = page.locator('details summary').filter({ hasText: 'Not in this build' });
        await expect(summary).toBeVisible();

        // Closed, so the screen's default read is the library rather than a wall of absences the Trainer
        // did not ask about (Phase 8.8). The notes stay reachable, and by keyboard as a native
        // `<summary>`.
        const details = page.locator('details').filter({ hasText: 'Not in this build' });
        await expect(details).toHaveCount(1);
        expect(await details.getAttribute('open')).toBeNull();

        await summary.focus();
        await page.keyboard.press('Enter');
        await expect(details).toHaveAttribute('open', '');
        await expect(page.getByText(/Favorite, archive, delete/)).toBeVisible();
    });

    // Plan slice 0.4. Two careers trained in the same form (Agnes Digital) filed in different
    // scenarios must not read as the same row. The date alone cannot separate them: both are filed
    // within the same second by this spec, so the scenario is the part of the record that carries the
    // difference, and the row prints it in its own column.
    test('gives two careers of one trainee different rows', async ({ page }) => {
        createdRunUrls.push(await fileCareer(page, 'Agnes Digital', null, null, 'ura_finale'));
        createdRunUrls.push(await fileCareer(page, 'Agnes Digital', null, null, 'unity_cup'));

        await page.goto('/veterans', { waitUntil: 'domcontentloaded' });
        await page.locator('#app > *').first().waitFor();

        const rows = page.locator('[data-veteran-row]').filter({ hasText: 'Agnes Digital' });
        await expect(rows).toHaveCount(2);

        const texts: string[] = [];
        for (const row of await rows.all()) {
            texts.push((await row.innerText()).replace(/\s+/g, ' ').trim());
        }

        expect(new Set(texts).size, `rows must differ: ${JSON.stringify(texts)}`).toBe(2);
        expect(texts.some((text) => text.includes('URA Finale'))).toBe(true);
        expect(texts.some((text) => text.includes('Unity Cup'))).toBe(true);
    });
});
