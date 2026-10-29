import { test, expect } from '@playwright/test';
import { buildAxe } from '../utils/accessibility';
import { deleteRun } from '../utils/delete-run';
import { recordTurns } from '../utils/record-turns';

/*
 * Rendered evidence for SCREEN-012, the Event Decision, re-baselined at the HFW's region model after
 * the 2026-10-28 parity audit: What the event shows, Options, Record the outcome, This event's
 * provenance and the savebar. The event's own copy is the client's and is never transcribed by this
 * tool, so the option rows are the trainer's transcription (known choices become radios when the run
 * has any); that is the documented deviation in the page.
 */

const WRITE = { timeout: 60_000 } as const;
const createdRunUrls: string[] = [];

test.afterEach(async ({ page }) => {
    for (const url of createdRunUrls.splice(0)) {
        await deleteRun(page, url);
    }
});

async function openEventDecision(page: import('@playwright/test').Page): Promise<string> {
    await page.goto('/training-runs/create', { waitUntil: 'domcontentloaded' });
    await page.locator('#app > *').first().waitFor();
    await page.locator('#trainee-combobox').fill('Rice Shower');
    await page.keyboard.press('Enter');
    await page.selectOption('select[name="scenario"]', { value: 'trackblazer' });
    await page.getByRole('button', { name: 'Create run' }).click();
    await page.waitForURL(/\/training-runs\/\d+\/cockpit$/, WRITE);

    const runId = page.url().match(/\/training-runs\/(\d+)/)?.[1] ?? '';
    const runUrl = page.url().replace(/\/cockpit$/, '');
    createdRunUrls.push(page.url());

    await recordTurns(page, runId, 2);

    await page.goto(`${runUrl}/events`, { waitUntil: 'domcontentloaded' });
    await page.locator('#app > *').first().waitFor();

    return runUrl;
}

test('renders the HFW region model with one savebar', async ({ page }) => {
    await openEventDecision(page);

    await expect(page.getByRole('heading', { name: 'A training event is asking', level: 1 })).toBeVisible();
    await expect(page.getByRole('heading', { name: 'What the event shows', level: 2 })).toBeVisible();
    await expect(page.getByRole('heading', { name: 'Record the outcome', level: 2 })).toBeVisible();
    await expect(page.getByRole('heading', { name: "This event's provenance", level: 2 })).toBeVisible();
    await expect(page.locator('main table')).toHaveCount(1);
    await expect(page.locator('.savebar')).toHaveCount(1);
    await expect(page.locator('.savebar .btn-primary')).toHaveCount(1);
});

test('states the event-copy rule and keeps the keying fields behind one disclosure', async ({ page }) => {
    await openEventDecision(page);

    await expect(page.getByText(/Event text is the client's own copy/)).toBeVisible();
    await expect(page.getByText('No option predicts its outcome.')).toBeVisible();

    const details = page.locator('details').filter({ hasText: 'Where this event came from (required to record)' });

    await expect(details).not.toHaveAttribute('open', '');
    await expect(page.locator('input[name="source_name"]')).toBeHidden();
});

test('prints the provenance rows and the held outcome fields as absences', async ({ page }) => {
    await openEventDecision(page);

    await expect(page.getByText('Client screen')).toBeVisible();
    await expect(page.getByText('Not published')).toBeVisible();
    await expect(page.getByText(/Alarm Clock covers goal races only/)).toBeVisible();
    // Four marked absence cells, not every occurrence of the words: the card's own sentence quotes
    // them too, and a text count would read five.
    await expect(page.locator('[data-outcome="not-recorded"]')).toHaveCount(4);
});

test('records a transcribed choice and the run lists it afterwards', async ({ page }) => {
    const runUrl = await openEventDecision(page);

    await page.getByText('Where this event came from (required to record)').click();
    await page.selectOption('select[name="event_type"]', { value: 'SupportCard' });
    await page.locator('input[name="source_name"]').fill('The reporter asks for a quote');
    await page.locator('input[name="support_card_name"]').fill('Special Week');
    await page.locator('input[name="choice_label"]').fill('Say something kind.');

    await expect(page.locator('.savebar')).toContainText('Say something kind. selected');

    await page.getByRole('button', { name: 'Record event' }).click();
    await expect(page.getByText('Event choice recorded.')).toBeVisible(WRITE);

    await page.goto(`${runUrl}/events`, { waitUntil: 'domcontentloaded' });
    await page.locator('#app > *').first().waitFor();
    await page.getByText('Recorded events on this run').click();

    await expect(page.getByText(/The reporter asks for a quote/)).toBeVisible();
});

test('sizes the event controls to the 44px contract', async ({ page }) => {
    await openEventDecision(page);

    const undersized = await page.locator('main a, main button, main select, main summary').evaluateAll((nodes) =>
        nodes.filter((node) => {
            const box = node.getBoundingClientRect();

            return box.width > 0 && box.height > 0 && box.height < 44;
        }).length,
    );

    expect(undersized).toBe(0);
});

test('reflows at 320px without pushing the page sideways', async ({ page }) => {
    await openEventDecision(page);
    await page.setViewportSize({ width: 320, height: 900 });

    expect(await page.evaluate(() => document.documentElement.scrollWidth > window.innerWidth + 1)).toBe(false);
});

test('passes an axe scan at WCAG A and AA', async ({ page }) => {
    await openEventDecision(page);

    const axe = await buildAxe(page);
    const results = await axe.analyze();

    expect(results.violations).toEqual([]);
});
