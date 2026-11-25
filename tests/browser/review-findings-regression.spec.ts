import { test, expect } from './support/base-test';
import type { Page } from '@playwright/test';
import { deleteRun } from '../utils/delete-run';
import { recordTurns } from '../utils/record-turns';
import { startCareer } from '../utils/start-career';
import { clearPendingCandidates, seedPendingCandidates } from '../utils/review-candidates';
import { uraCompletedRun } from '../utils/ura-completed-run';

/*
 * Rendered regression for the eight findings of
 * `docs/audits/2026-11-22-frontend-browser-review/01-findings.md`.
 *
 * The audit's own re-validation (2026-11-23) was read-only source inspection and marked all eight
 * OPEN. The tree has since moved, so each finding is measured here against what the browser paints,
 * not against what the source contains:
 *  - TD-01 the three safe exits address the controller's canonical Cockpit URL;
 *  - TD-02 a `gametora-races` candidate reads as Race in the queue and in the case head;
 *  - TD-04 a completed career keeps the nine-step spine and lights Result;
 *  - TD-06 the long absence sentence reaches a reader without being printed five times;
 *  - TD-08 the Source Ledger's kind/read-date/tier filters narrow and announce their count.
 *
 * TD-03, TD-05 and TD-07 are already pinned by `dashboard.spec.ts`, `catalog-detail.spec.ts` and
 * `career-snapshot.spec.ts`; they are re-run rather than duplicated here.
 */

const WRITE = { timeout: 60_000 };
const createdRunUrls: string[] = [];

test.afterEach(async ({ page }) => {
    for (const url of createdRunUrls.splice(0)) {
        await deleteRun(page, url);
    }
    clearPendingCandidates();
});

async function hydrate(page: Page, url: string): Promise<void> {
    await page.goto(url, { waitUntil: 'domcontentloaded' });
    await page.locator('#app > *').first().waitFor();
}

test('TD-01 every safe exit addresses the run Cockpit, never /cockpit/cockpit', async ({ page }) => {
    const runUrl = await startCareer(page, 'unity_cup');
    createdRunUrls.push(runUrl);

    // Run Settings.
    await hydrate(page, `${runUrl}/settings`);
    const settingsBack = page.getByRole('link', { name: 'Back to Cockpit' });
    await expect(settingsBack).toHaveAttribute('href', `${runUrl}/cockpit`);
    await settingsBack.click();
    await page.waitForURL(/\/training-runs\/\d+\/cockpit$/, WRITE);

    // Correct Turn, reached through its own Timeline door so the link is the real one.
    await recordTurns(page, runUrl.split('/').pop() as string, 1);
    await hydrate(page, `${runUrl}/timeline`);
    await page.getByRole('link', { name: 'Edit turn 1' }).first().click();
    await page.waitForURL(/\/training-runs\/\d+\/turns\/\d+\/edit$/, WRITE);
    const turnBack = page.getByRole('link', { name: 'Back to Cockpit' });
    await expect(turnBack).toHaveAttribute('href', `${runUrl}/cockpit`);

    // Delete confirmation: the Cancel path is the one that interrupts recovery, so it is followed.
    await hydrate(page, `${runUrl}/delete`);
    const cancel = page.getByRole('link', { name: 'Cancel' });
    await expect(cancel).toHaveAttribute('href', `${runUrl}/cockpit`);
    await cancel.click();
    await page.waitForURL(/\/training-runs\/\d+\/cockpit$/, WRITE);
    await expect(page.getByRole('heading', { level: 1 })).not.toHaveText(/does not exist/i);

    // The same three doors on the phone layout: the audit checked Run Settings at 320px, and a
    // reflowed shell is where a safe exit goes missing.
    await page.setViewportSize({ width: 390, height: 844 });
    await hydrate(page, `${runUrl}/settings`);
    await expect(page.getByRole('link', { name: 'Back to Cockpit' })).toHaveAttribute('href', `${runUrl}/cockpit`);
    await hydrate(page, `${runUrl}/delete`);
    await expect(page.getByRole('link', { name: 'Cancel' })).toHaveAttribute('href', `${runUrl}/cockpit`);
});

test('the three palettes are selectable, change the rendered surface and survive a reload', async ({ page }) => {
    const surface = (): Promise<string> =>
        page.evaluate(() => getComputedStyle(document.querySelector('main .card') ?? document.body).backgroundColor);

    await hydrate(page, '/preferences');
    const attribute = () => page.evaluate(() => document.documentElement.getAttribute('data-theme'));

    const system = await surface();

    await page.getByRole('radio', { name: /Dark/ }).check();
    await page.getByRole('button', { name: 'Save changes' }).click();
    await expect(page.getByRole('status').filter({ hasText: 'Preferences saved.' })).toBeVisible();
    await expect.poll(attribute).toBe('dark');
    const dark = await surface();

    await page.getByRole('radio', { name: /Evangelion Unit-01/ }).check();
    await page.getByRole('button', { name: 'Save changes' }).click();
    await expect(page.getByRole('status').filter({ hasText: 'Preferences saved.' })).toBeVisible();
    await expect.poll(attribute).toBe('eva01');
    const eva = await surface();

    // Two palettes that measure the same surface colour are the failure this case exists for.
    expect(dark, 'the dark palette must move the card surface off the light one').not.toBe(system);
    expect(eva, 'the Eva palette must not be a rename of one of the other two').not.toBe(system);
    expect(eva).not.toBe(dark);

    await page.reload({ waitUntil: 'domcontentloaded' });
    await page.locator('#app > *').first().waitFor();
    await expect(page.getByRole('radio', { name: /Evangelion Unit-01/ })).toBeChecked();

    // Leave the shared database as the suite found it: system decides, nothing stored.
    await page.getByRole('radio', { name: /Follow the system/ }).check();
    await page.getByRole('button', { name: 'Save changes' }).click();
    await expect(page.getByRole('status').filter({ hasText: 'Preferences saved.' })).toBeVisible();
    await expect.poll(attribute).toBe(null);
});

test('TD-02 a race-origin candidate is labelled Race in the queue and in the case head', async ({ page }) => {
    clearPendingCandidates();
    seedPendingCandidates(1, { sourceKey: 'gametora-races', prefix: 'Hanshin Umamusume Stakes' });

    await hydrate(page, '/review');
    const row = page.locator('#review-results tr').first();
    await expect(row).toContainText('Race · from gametora-races');
    await expect(row).not.toContainText('Trainee Umamusume');

    await row.getByTestId('review-detail-link').click();
    await page.waitForURL(/\/review\/\d+$/, WRITE);
    await expect(page.getByRole('heading', { level: 1, name: 'Is this the right race?' })).toBeVisible();
    await expect(page.getByLabel(/^Umamusume id for /)).toHaveCount(0);
});

test('TD-04 a completed career keeps the step spine and lights Result', async ({ page }) => {
    const runId = String(uraCompletedRun());
    const runUrl = `/training-runs/${runId}`;

    await hydrate(page, `${runUrl}/cockpit`);
    const nav = page.getByRole('navigation', { name: 'Career', exact: true });
    await expect(nav).toBeVisible();

    // `Result` is the one section an unfinished career lacks; a finished one reports its terminal
    // status, which is what makes the step live rather than plain text.
    await expect(nav.getByRole('link', { name: 'Result', exact: true })).toBeVisible();

    await hydrate(page, `${runUrl}/result`);
    await expect(page.getByRole('navigation', { name: 'Career', exact: true })).toBeVisible();
    await expect(
        page.getByRole('navigation', { name: 'Career', exact: true }).getByRole('link', { name: 'Result', exact: true }),
    ).toHaveAttribute('aria-current', 'step');
});

test('TD-06 the absence explanation is not printed down the discipline column', async ({ page }) => {
    const runUrl = await startCareer(page, 'unity_cup');
    createdRunUrls.push(runUrl);

    await hydrate(page, `${runUrl}/training`);

    // The audit's measurement was the long sentence repeated five times. It is counted here over
    // every text node in `main`, sr-only included, because `AbsenceValue` puts its reason in the
    // disclosure's accessible name and that is the path the finding reported.
    const occurrences = await page.evaluate(() => {
        const probe = 'No facility level is recorded for this training';
        const walker = document.createTreeWalker(document.querySelector('main') ?? document.body, NodeFilter.SHOW_TEXT);
        let hits = 0;

        for (let node = walker.nextNode(); node; node = walker.nextNode()) {
            if ((node.textContent ?? '').includes(probe)) hits += 1;
        }

        return hits;
    });

    expect(occurrences, 'the ~180-character sentence must not reach a reader once, let alone five times').toBe(0);
    await expect(page.locator('[data-region="training-gains-absence"]')).toHaveCount(1);

    // Each marker still says what is missing, in the caller's own short words.
    const markers = page.locator('main summary', { hasText: 'N/A' });
    expect(await markers.count()).toBeGreaterThan(0);
    await expect(markers.first().locator('span.sr-only')).toContainText('Facility level not recorded');
});

test('TD-08 the Source Ledger filters narrow the ledger and announce the count', async ({ page }) => {
    await hydrate(page, '/database/sources');

    const ledger = page.locator('[aria-labelledby="sources-ledger"]');
    const status = ledger.locator('p[role="status"]');

    const before = (await status.innerText()).trim();
    expect(before, 'the unfiltered ledger states its size').toMatch(/^\d+ sources\.$/);

    await ledger.locator('#sources-kind-filter').selectOption('fetched');
    const narrowed = (await status.innerText()).trim();
    expect(narrowed).toMatch(/^\d+ of \d+ sources match\.$/);
    expect(narrowed).not.toBe(before);
    await expect(ledger.getByRole('button', { name: 'Clear filters' })).toBeVisible();

    // A filter that leaves nothing still answers, rather than drawing an empty card.
    await ledger.locator('#sources-read-filter').selectOption('undated');
    const empty = (await status.innerText()).trim();
    if (/^0 of \d+/.test(empty)) {
        await expect(ledger.getByText(/No sources match the current filters/)).toBeVisible();
    }

    await ledger.getByRole('button', { name: 'Clear filters' }).click();
    await expect(status).toHaveText(before);
});
