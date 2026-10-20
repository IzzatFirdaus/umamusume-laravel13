import { test, expect } from '@playwright/test';
import { buildAxe } from '../utils/accessibility';
import { deleteRun } from '../utils/delete-run';
import { expectTapTargets } from '../utils/tap-targets';

/*
 * SCR-017, the baseline strip (plan §9 E6, D-241, gate G-41). `GrandConcertPanelTest` pins the
 * payload; these cases assert what only a browser reaches: the rendered copy, the caps table as a
 * table, the two provenance badges, the 44px floor and the 320px reflow.
 *
 * A run is created through the assistant screens the way the other career specs do it, then walked to
 * the Cockpit. Writes get 60s because `php artisan serve` is a single-process `php -S`.
 */

const WRITE = { timeout: 60_000 } as const;

const createdRunUrls: string[] = [];

test.afterEach(async ({ page }) => {
    for (const url of createdRunUrls.splice(0)) {
        await deleteRun(page, url);
    }
});

/** Our Grand Concert is the one scenario with every panel flag off, so its Cockpit draws the strip. */
async function openGrandConcertCockpit(page: import('@playwright/test').Page): Promise<void> {
    await page.goto('/training-runs/create', { waitUntil: 'domcontentloaded' });
    await page.locator('#app > *').first().waitFor();
    await page.locator('#trainee-combobox').fill('Rice Shower');
    await page.keyboard.press('Enter');
    await page.selectOption('select[name="scenario"]', { value: 'our_grand_concert' });
    await page.getByRole('button', { name: 'Create run' }).click();
    await page.waitForURL(/\/training-runs\/\d+\/cockpit$/, WRITE);
    const runUrl = page.url().replace(/\/cockpit$/, '');
    createdRunUrls.push(runUrl);

    await page.goto(`${runUrl}/cockpit`, { waitUntil: 'domcontentloaded' });
    await page.locator('#app > *').first().waitFor();
}

test('states the scenario and its partial documentation in the strip', async ({ page }) => {
    await openGrandConcertCockpit(page);

    const section = page.getByRole('region', { name: 'Scenario' });
    await expect(section.getByText('Our Grand Concert', { exact: true })).toBeVisible();
    await expect(section.getByText('Partially documented', { exact: true })).toBeVisible();
});

test('draws the five published caps as a table of base, bonus and cap', async ({ page }) => {
    await openGrandConcertCockpit(page);

    const section = page.getByRole('region', { name: 'Scenario' });
    const table = section.getByRole('table', { name: 'Published stat caps for this scenario' });

    await expect(table).toBeVisible();

    // One header row and the five stats the matrix orders.
    await expect(table.getByRole('row')).toHaveCount(6);

    for (const stat of ['Speed', 'Stamina', 'Power', 'Guts', 'Wit']) {
        await expect(table.getByRole('rowheader', { name: stat })).toBeVisible();
    }

    // Speed carries the scenario's own bonus: 1200 base plus 400, which is the 1600 the plan records
    // as corroborated 2026-10-05. The three terms print as three separate cells, never as one
    // collapsed number.
    const speed = table.getByRole('row', { name: /Speed/ });
    await expect(speed.getByRole('cell').nth(0)).toHaveText('1200');
    await expect(speed.getByRole('cell').nth(1)).toHaveText('400');
    await expect(speed.getByRole('cell').nth(2)).toHaveText('1600');

    // The date is the matrix's verification date, not a fetch date.
    await expect(section.getByText(/Verified 2026-09-27/)).toBeVisible();
});

test('names the caps verified and says why the panel is undrawn in a visible line', async ({ page }) => {
    await openGrandConcertCockpit(page);

    const section = page.getByRole('region', { name: 'Scenario' });

    // The caps are published, and what says so is the `Verified {date}` span beside the heading — the
    // matrix's own verification date, which is the claim the table rests on. This case used to demand
    // a `role="img"` badge named Confirmed; slice 1.7 retired that, because a badge present on every
    // published value marks nothing. The undrawn panel is an absence, not a figure, so the prose line
    // owns that whole statement (`DESIGN.md` §4.2, owner's ruling 2026-10-09).
    await expect(section.getByText(/Verified \d{4}-\d{2}-\d{2}/)).toBeVisible();
    await expect(section.getByRole('img', { name: /^Unknown/ })).toHaveCount(0);

    const sentence = section.getByText(/The mechanics for this scenario are sourced/).first();
    await expect(sentence).toBeVisible();
    await expect(sentence).toHaveAttribute('title', /docs\/scenarios\/07-grand-concert\.md/);
});

test('says nothing about a mechanic no source measures', async ({ page }) => {
    await openGrandConcertCockpit(page);

    const section = page.getByRole('region', { name: 'Scenario' });
    const text = (await section.innerText()).toLowerCase();

    // A floor, not a proof: the words a panel would need if one were invented from the guide rather
    // than drawn from a measured client string.
    for (const word of ['song', 'lesson', 'performance token', 'promotional live', 'hype']) {
        expect(text, `the strip mentions "${word}"`).not.toContain(word);
    }
});

test('keeps the 44px floor across the cockpit and reflows at 320 px', async ({ page }) => {
    await openGrandConcertCockpit(page);

    // The strip adds no control of its own, so a sweep of the strip alone would be a check that cannot
    // fail. The sweep runs over the page with the strip mounted, which is where a target it displaced
    // would show up. `expectTapTargets` skips what a collapsed disclosure hides and asserts it measured
    // something; 10 is well under the ~17 controls this page carries.
    await expectTapTargets(page.locator('main a, main button'), 10);

    await page.setViewportSize({ width: 320, height: 700 });
    await expect(page.getByRole('region', { name: 'Scenario' })).toBeVisible();
    // No sideways scroll at the reflow width (WCAG 1.4.10). The caps table is the widest thing the
    // strip adds, so this is the case that would catch it growing past the viewport.
    const overflow = await page.evaluate(
        () => document.documentElement.scrollWidth - document.documentElement.clientWidth,
    );
    expect(overflow, 'the cockpit scrolls sideways at 320px').toBeLessThanOrEqual(1);
});

test('passes the axe A + AA scan with the strip mounted', async ({ page }) => {
    await openGrandConcertCockpit(page);

    await buildAxe(page).analyze();
});
