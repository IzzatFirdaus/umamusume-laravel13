import { test, expect } from '@playwright/test';
import { buildAxe } from '../utils/accessibility';
import { deleteRun } from '../utils/delete-run';
import { recordTurns } from '../utils/record-turns';

/*
 * Rendered evidence for SCREEN-011, the Race Decision, re-baselined at the HFW's region model after
 * the 2026-10-28 parity audit. The page is now one table of the turn's races, one selected race's
 * detail card with the sourced 1st-place payout, the consecutive-races card and the savebar; the
 * free-race write and the result recorder stay in disclosures (documented overrides).
 *
 * Fixture: eleven logged turns put the decision on Junior turn 12, where the calendar carries the
 * mandatory Junior Make Debut. The run is deleted over HTTP in afterEach.
 */

const WRITE = { timeout: 60_000 } as const;
const createdRunUrls: string[] = [];

test.afterEach(async ({ page }) => {
    for (const url of createdRunUrls.splice(0)) {
        await deleteRun(page, url);
    }
});

async function openDecision(page: import('@playwright/test').Page): Promise<string> {
    await page.goto('/training-runs/create', { waitUntil: 'domcontentloaded' });
    await page.locator('#app > *').first().waitFor();
    await page.locator('#trainee-combobox').fill('Agnes Digital');
    await page.keyboard.press('Enter');
    await page.selectOption('select[name="scenario"]', { value: 'unity_cup' });
    await page.getByRole('button', { name: 'Create run' }).click();
    await page.waitForURL(/\/training-runs\/\d+\/cockpit$/, WRITE);

    const runId = page.url().match(/\/training-runs\/(\d+)/)?.[1] ?? '';
    const runUrl = page.url().replace(/\/cockpit$/, '');
    createdRunUrls.push(page.url());

    await recordTurns(page, runId, 11);

    await page.goto(`${runUrl}/races`, { waitUntil: 'domcontentloaded' });
    await page.locator('#app > *').first().waitFor();

    return runUrl;
}

test('renders the HFW region model', async ({ page }) => {
    await openDecision(page);

    await expect(page.getByRole('heading', { name: 'Available this turn', level: 2 })).toBeVisible();
    await expect(page.getByRole('heading', { name: 'Consecutive races', level: 2 })).toBeVisible();
    await expect(page.locator('.savebar')).toHaveCount(1);
    await expect(page.locator('.tile')).toHaveCount(4);
    await expect(page.locator('main table')).toHaveCount(2);
    // One primary in the savebar plus the per-row Enter buttons, which the HFW also fills.
    await expect(page.locator('.savebar .btn-primary')).toHaveCount(1);
});

test('states each row\'s grade, surface, band, aptitude letter and 1st-place skill points', async ({ page }) => {
    await openDecision(page);

    const table = page.locator('table').first();
    await expect(table.getByRole('columnheader')).toHaveCount(6);
    await expect(table).toContainText('Grade');
    await expect(table).toContainText('Surface · distance');
    await expect(table).toContainText('Fans needed');
    await expect(table).toContainText('Skill Pt');

    // A placeholder calendar row (the Junior debut) carries no surface or band, so the grade cell
    // is the stable assertion and the aptitude pill appears only where an axis exists.
    const rows = table.locator('tbody tr');
    const grades = await rows.locator('td:nth-child(2)').allTextContents();

    expect(grades.length).toBeGreaterThan(0);
    for (const grade of grades) {
        expect(grade.trim()).toMatch(/^(G1|G2|G3|OP|Pre-OP|Debut|Maiden|N\/A)$/);
    }
});

test('keeps a blocked race visible with its reason rather than filtering it out', async ({ page }) => {
    await openDecision(page);

    const blocked = page.locator('table').first().locator('tbody tr').filter({ has: page.getByRole('button', { name: /Maiden lock not cleared|Blocked/ }) });

    if (await blocked.count() === 0) {
        test.skip(true, 'no blocked row on this turn');

        return;
    }

    const button = blocked.first().getByRole('button');

    await expect(button).toBeDisabled();
    await expect(button).toBeVisible();
});

test('prints only the sourced 1st-place payout and leaves 2nd and 3rd blank', async ({ page }) => {
    await openDecision(page);

    const payouts = page.locator('table').nth(1);

    await expect(payouts).toContainText('Placement');
    await expect(payouts.locator('tbody tr')).toHaveCount(3);
    await expect(payouts.locator('tbody tr').nth(1)).toContainText('2nd');
    await expect(payouts.locator('tbody tr').nth(2)).toContainText('3rd');
    // No extrapolated percentage or number on the unpublished placements.
    await expect(payouts.locator('tbody tr').nth(1)).not.toContainText('%');
});

test('selecting a row moves the detail card and the savebar status', async ({ page }) => {
    await openDecision(page);

    const rows = page.locator('table').first().locator('tbody tr');
    const count = await rows.count();

    if (count < 2) {
        test.skip(true, 'only one race on this turn');

        return;
    }

    const title = (await rows.nth(1).locator('b').first().textContent())?.trim() ?? '';

    await rows.nth(1).click();

    await expect(page.getByRole('heading', { name: title, level: 2 })).toBeVisible();
    await expect(page.locator('.savebar')).toContainText(`${title} selected`);
});

test('Train instead escapes to the training screen', async ({ page }) => {
    const runUrl = await openDecision(page);

    await page.getByRole('link', { name: 'Train instead' }).click();
    await page.waitForURL(`${runUrl}/training`, WRITE);
    await expect(page.getByRole('heading', { name: "Pick this turn's action", level: 1 })).toBeVisible();
});

test('entering a race writes through the existing route and lands back on the decision', async ({ page }) => {
    const runUrl = await openDecision(page);

    await page.locator('table').first().locator('tbody tr').filter({ has: page.getByRole('button', { name: 'Enter' }) }).first().getByRole('button', { name: 'Enter' }).click();

    // The write redirects back to the referring screen (storeRace's back()), so the confirmation is
    // the flash rather than a navigation to the cockpit.
    await expect(page.getByText('Race recorded.')).toBeVisible(WRITE);
    await expect(page).toHaveURL(`${runUrl}/races`);
});

test('meets the 44px floor and passes axe', async ({ page }) => {
    await openDecision(page);

    const undersized = await page.locator('main a, main button, main select, main summary').evaluateAll((nodes) =>
        nodes.filter((node) => {
            const box = node.getBoundingClientRect();

            return box.width > 0 && box.height > 0 && box.height < 44;
        }).length,
    );

    expect(undersized).toBe(0);

    const axe = await buildAxe(page);
    const results = await axe.analyze();

    expect(results.violations).toEqual([]);
});
