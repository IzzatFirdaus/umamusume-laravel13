import { test, expect } from '@playwright/test';
import { buildAxe } from '../utils/accessibility';
import { deleteRun } from '../utils/delete-run';

/*
 * Rendered evidence for SCREEN-013, the Inheritance Event, re-baselined at the HFW's region model
 * after the 2026-10-28 parity audit: the ancestry card, the two parent spark tables, Result of this
 * pick, the stored-selection note and the savebar. The wireframe's per-parent radios are not built
 * because the run stores one legacy selection (ADR-0010); that is the page's documented deviation.
 *
 * The fixture builds a legacy through the run-scoped Legacy Lab, so both parents carry sparks.
 */

const WRITE = { timeout: 60_000 } as const;
const createdRunUrls: string[] = [];

test.afterEach(async ({ page }) => {
    for (const url of createdRunUrls.splice(0)) {
        await deleteRun(page, url);
    }
});

async function recordTurns(page: import('@playwright/test').Page, runId: string, count: number): Promise<void> {
    for (let turn = 1; turn <= count; turn++) {
        const xsrf = await page.context().cookies().then((all) => all.find((cookie) => cookie.name === 'XSRF-TOKEN')?.value);
        expect(xsrf, `the session carries no XSRF-TOKEN cookie before turn ${turn}`).toBeDefined();

        const response = await page.request.post(`/training-runs/${runId}/turns`, {
            form: { turn: String(turn), speed: '600', stamina: '500', power: '500', guts: '500', wit: '500' },
            headers: { 'X-XSRF-TOKEN': decodeURIComponent(xsrf as string) },
        });

        expect(response.status(), `turn ${turn} was refused`).toBeLessThan(400);
    }
}

async function openInheritanceEvent(page: import('@playwright/test').Page): Promise<string> {
    await page.goto('/training-runs/create', { waitUntil: 'domcontentloaded' });
    await page.locator('#app > *').first().waitFor();
    await page.locator('#trainee-combobox').fill('Rice Shower');
    await page.keyboard.press('Enter');
    await page.selectOption('select[name="scenario"]', { value: 'trackblazer' });
    await page.getByRole('button', { name: 'Create run' }).click();
    await page.waitForURL(/\/training-runs\/\d+(\/cockpit)?$/, WRITE);
    createdRunUrls.push(page.url());

    const runId = page.url().match(/\/training-runs\/(\d+)/)?.[1] ?? '';
    // Three turns are enough to render the screen and write an event; the legacy builder carries
    // the sparks. Twenty turns made every case pay a fixture cost the host's single-process server
    // could not always finish inside the test budget.
    await recordTurns(page, runId, 3);

    await page.goto(`/legacy/${runId}`, { waitUntil: 'domcontentloaded' });
    await page.locator('#app > *').first().waitFor();

    const addSpark = (parent: number) => page.getByRole('button', { name: 'Add Spark' }).nth(parent);

    await page.locator('#rank-parent_a').fill('3');
    await page.locator('#ancestor-parent_a-0').fill('Symboli Rudolf');
    await page.locator('#ancestor-parent_a-1').fill('Mejiro McQueen');
    await addSpark(0).click();
    await addSpark(0).click();
    await page.getByRole('combobox', { name: 'Kind' }).nth(0).selectOption('blue');
    await page.getByRole('textbox', { name: 'Applies to' }).nth(0).fill('Speed');
    await page.getByRole('spinbutton', { name: 'Stars' }).nth(0).fill('3');
    await page.getByRole('combobox', { name: 'Kind' }).nth(1).selectOption('pink');
    await page.getByRole('textbox', { name: 'Applies to' }).nth(1).fill('Medium');
    await page.getByRole('spinbutton', { name: 'Stars' }).nth(1).fill('2');

    await page.locator('#rank-parent_b').fill('2');
    await page.locator('#ancestor-parent_b-0').fill('Special Week');
    await addSpark(1).click();
    await page.getByRole('combobox', { name: 'Kind' }).nth(2).selectOption('blue');
    await page.getByRole('textbox', { name: 'Applies to' }).nth(2).fill('Stamina');
    await page.getByRole('spinbutton', { name: 'Stars' }).nth(2).fill('2');

    await page.getByRole('button', { name: 'Save legacy' }).click();
    await page.waitForURL(/\/legacy\/compare/, WRITE);

    await page.goto(`/training-runs/${runId}/inheritance`, { waitUntil: 'domcontentloaded' });
    await page.locator('#app > *').first().waitFor();

    return runId;
}

test('renders the HFW region model with the ancestry and two parent tables', async ({ page }) => {
    await openInheritanceEvent(page);

    await expect(page.getByRole('heading', { name: 'Choose what to inherit', level: 1 })).toBeVisible();
    await expect(page.getByRole('heading', { name: 'Inheritance', level: 2 })).toBeVisible();
    await expect(page.getByRole('heading', { name: /^Parent A/ })).toBeVisible();
    await expect(page.getByRole('heading', { name: /^Parent B/ })).toBeVisible();
    await expect(page.getByRole('heading', { name: 'Result of this pick', level: 2 })).toBeVisible();
    await expect(page.getByRole('group', { name: 'Six ancestor slots' })).toBeVisible();
    await expect(page.locator('.savebar')).toHaveCount(1);
    await expect(page.locator('.tile')).toHaveCount(3);
});

test('names each spark family and its stars, not colour alone', async ({ page }) => {
    await openInheritanceEvent(page);

    await expect(page.getByText('Blue · Speed')).toBeVisible();
    await expect(page.getByText('Pink · Medium')).toBeVisible();
    await expect(page.getByText('Blue · Stamina')).toBeVisible();
});

test('prints the before/after caps and states that no per-pick change is computed', async ({ page }) => {
    await openInheritanceEvent(page);

    await expect(page.getByRole('heading', { name: 'Result of this pick' })).toBeVisible();
    await expect(page.getByText(/The cap raise is the loop/)).toBeVisible();
    await expect(page.getByText(/One selection per run is what this tool stores/)).toBeVisible();
});

test('records the moment through the write disclosure', async ({ page }) => {
    await openInheritanceEvent(page);

    await page.getByText('Record what this moment granted (required to record)').click();
    await page.locator('input[name="source_name"]').fill('Inheritance moment');
    await page.locator('input[name="choice_label"]').fill('Blue · Speed ★3');
    await page.getByRole('button', { name: 'Record inheritance' }).click();

    await expect(page.getByText('Inheritance event recorded.')).toBeVisible(WRITE);
});

test('offers Skip this moment and returns to the cockpit', async ({ page }) => {
    const runId = await openInheritanceEvent(page);

    await page.getByRole('button', { name: 'Skip this moment' }).click();
    await page.waitForURL(`/training-runs/${runId}/cockpit`, WRITE);
});

test('sizes the inheritance controls to the 44px contract', async ({ page }) => {
    await openInheritanceEvent(page);

    const undersized = await page.locator('main a, main button, main select, main summary').evaluateAll((nodes) =>
        nodes.filter((node) => {
            const box = node.getBoundingClientRect();

            return box.width > 0 && box.height > 0 && box.height < 44;
        }).length,
    );

    expect(undersized).toBe(0);
});

test('passes an axe scan at WCAG A and AA', async ({ page }) => {
    await openInheritanceEvent(page);

    const axe = await buildAxe(page);
    const results = await axe.analyze();

    expect(results.violations).toEqual([]);
});
