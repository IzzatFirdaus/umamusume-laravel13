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
        await expect(page.getByRole('combobox', { name: 'Trainee' })).toBeVisible();
        await expect(page.getByRole('combobox', { name: 'Scenario' })).toBeVisible();
        await expect(page.getByRole('textbox', { name: 'Tag' })).toBeVisible();
        await expect(page.getByRole('button', { name: 'Filter' })).toBeVisible();

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
        // beside the word it stands for. Plan 1.2 moved the toggle into the 48px product bar, so the
        // bar's chevron is no longer inside the aside: a full-width rail carries no mark at all.
        await expect(aside.locator('svg')).toHaveCount(0);

        await toggle.press('Enter');

        // Collapsed, each destination carries its mark and the word moves to `sr-only`: ten, and the
        // chevron belongs to the bar rather than to the rail.
        await expect(aside.locator('svg')).toHaveCount(10);

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
        // carries a left rule as well as its fill. `border-l-4` since plan 1.2, which took the nearest
        // stock step to the 3px the plan named rather than introducing an arbitrary value.
        const current = aside.getByRole('link', { name: 'Dashboard', exact: true });
        await expect(current).toHaveAttribute('aria-current', 'page');
        expect(((await current.getAttribute('class')) ?? '').split(' ')).toContain('border-l-4');

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
        await expect(page.getByRole('link', { name: 'Leave setup' })).toBeVisible();
    });
});

const createdRunUrls: string[] = [];

test.afterEach(async ({ page }) => {
    for (const url of createdRunUrls.splice(0)) {
        await deleteRun(page, url);
    }
});

test.describe('Veteran cards', () => {
    /**
     * Files a career, opens the library and returns the row it landed in. The library only shows a
     * non-empty list once something is filed, so every case below starts by putting one there.
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

        return page.getByRole('listitem').filter({ hasText: trainee });
    }

    /**
     * A note in the shape the legacy tracker imported: this career's own clause, then the one sentence
     * every tracker career ends with. `RecordVeteran` never writes that sentence, so a browser case can
     * only reach the split by filing it, and the split is what Phase 8.1 changes.
     */
    const trackedNote = (clause: string): string => `${clause} ${VETERAN_ANCESTRY_NOTE}`;

    test('states the shared ancestry sentence once above the list, not once per card', async ({ page }) => {
        await openLibraryRow(page, 'Agnes Digital', 'Speed', trackedNote('Career finished, Vodka 1st place.'));
        await openLibraryRow(page, 'Eishin Flash', null, trackedNote('Career ended, URA Finale Finals won 1st.'));

        const ancestry = page.getByText(/Legacy ancestry unrecorded:/);

        // One statement for the whole list (Phase 8.1), and it sits outside every card: the sentence is
        // about what the tracker did not record, which is the same for every row here.
        await expect(ancestry).toHaveCount(1);
        expect(await ancestry.evaluate((element) => element.closest('li') !== null)).toBe(false);

        // Each card keeps only the clause that is its own, and none of them repeats the sentence the list
        // just stated. A card printing the stored note verbatim would fail here twice over.
        await expect(page.getByRole('listitem').filter({ hasText: 'Career finished, Vodka 1st place.' })).toHaveCount(1);
        await expect(page.getByRole('listitem').filter({ hasText: 'Career ended, URA Finale Finals won 1st.' })).toHaveCount(1);
        expect(
            await page.getByRole('listitem').evaluateAll((rows) =>
                rows.filter((row) => row.textContent?.includes('Legacy ancestry unrecorded:') ?? false).length,
            ),
            'a card still repeats the sentence the list states once',
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

    test('puts the career action in the header row rather than under the note', async ({ page }) => {
        const row = await openLibraryRow(page, 'Agnes Digital', 'Speed');

        // The row's children: `VeteranRow` supplies the card root, and its own blocks hang off that, so
        // the header is the first block inside it and the footer actions the last. The Compare door is
        // read as belonging to the row the Trainer is looking at rather than as a footnote below the
        // paragraph (Phase 8.3; Law 6 Proximity).
        const header = row.locator(':scope > div > div').first();
        const footer = row.locator(':scope > div > div').last();

        // Exactly one in the whole card, and it sits in the header block: a second copy in the footer
        // would fail both assertions at once.
        await expect(row.getByRole('link', { name: 'Compare this career' })).toHaveCount(1);
        await expect(header.getByRole('link', { name: 'Compare this career' })).toBeVisible();
        expect(
            await footer.getByRole('link', { name: 'Compare this career' }).count(),
            'the card still carries a bottom action beside the Compare link',
        ).toBe(0);

        // The header block also names the action, so the row reads as one.
        await expect(header).toContainText('Compare this career');
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
    // within the same second by this spec, so the scenario is the part of the label that carries the
    // difference. The data is created here, not assumed from the seeded library.
    test('gives two careers of one trainee different row labels', async ({ page }) => {
        createdRunUrls.push(await fileCareer(page, 'Agnes Digital', null, null, 'ura_finale'));
        createdRunUrls.push(await fileCareer(page, 'Agnes Digital', null, null, 'unity_cup'));

        await page.goto('/veterans', { waitUntil: 'domcontentloaded' });
        await page.locator('#app > *').first().waitFor();

        const rows = page.locator('[data-veteran-row]').filter({ hasText: 'Agnes Digital' });
        await expect(rows).toHaveCount(2);

        const labels: string[] = [];
        for (const row of await rows.all()) {
            labels.push((await row.getByRole('link').first().innerText()).trim());
        }

        expect(new Set(labels).size, `row labels must differ: ${JSON.stringify(labels)}`).toBe(2);
        expect(labels.some((label) => label.includes('URA Finale'))).toBe(true);
        expect(labels.some((label) => label.includes('Unity Cup'))).toBe(true);
    });
});
