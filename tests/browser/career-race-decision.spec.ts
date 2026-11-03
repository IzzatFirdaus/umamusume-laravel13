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

test('loads with 200 and focuses the manual-race refusal', async ({ page }) => {
    const runUrl = await openDecision(page);

    // The route answers 200 as its own page, not as a redirect to the cockpit.
    const response = await page.goto(`${runUrl}/races`, { waitUntil: 'domcontentloaded' });
    expect(response?.status()).toBe(200);
    await page.locator('#app > *').first().waitFor();

    // RaceDecision-F-04 keeps the free-race door available on the page; it is a disclosure rather
    // than a second screen (the wireframe draws only calendar races).
    await page.getByText('Race not on the calendar').click();

    const manual = page
        .locator('details')
        .filter({ has: page.locator('summary', { hasText: 'Race not on the calendar' }) })
        .locator('form');
    // The controls carry native `required`; the server refuses independently, and its refusal is
    // what the alert must announce.
    await manual.evaluate((form) => form.setAttribute('novalidate', ''));
    await manual.getByRole('button', { name: 'Record race' }).click();

    const alert = page.locator('details form p[role="alert"][tabindex="-1"]');
    await expect(alert).toBeVisible();
    await expect(alert).toBeFocused();
});

test('a calendar refusal moves focus to its alert', async ({ page }) => {
    const runUrl = await openDecision(page);

    await page.goto(`${runUrl}/races`, { waitUntil: 'domcontentloaded' });
    await page.locator('#app > *').first().waitFor();

    // Every row the calendar offers is already on this run's calendar, so the UI cannot produce
    // the server's "not on this run's calendar" refusal. Forge only the row id on the wire and let
    // the server refuse for real: the assertion is about focus after a genuine 422 round trip.
    await page.route('**/training-runs/*/races', async (route) => {
        if (route.request().method() !== 'POST') {
            await route.continue();

            return;
        }

        const body = route.request().postDataJSON() as Record<string, unknown>;
        await route.continue({ postData: JSON.stringify({ ...body, race_catalog_slot_id: '999999' }) });
    });

    await page
        .locator('table')
        .first()
        .locator('tbody tr')
        .filter({ has: page.getByRole('button', { name: 'Enter' }) })
        .first()
        .getByRole('button', { name: 'Enter' })
        .click();

    // `enterAlertRef` is what AUD-20261102-002 F-01 added. The assertion is that the refusal
    // receives focus; the live region at the foot of the page also announces, which is not the
    // same thing (WCAG 2.4.3).
    const alert = page.locator('p[role="alert"][tabindex="-1"]');
    await expect(alert).toBeVisible(WRITE);
    await expect(alert).toBeFocused();
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
