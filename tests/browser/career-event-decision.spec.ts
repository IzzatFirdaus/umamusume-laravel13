import { test, expect } from '@playwright/test';
import { buildAxe } from '../utils/accessibility';
import { deleteRun } from '../utils/delete-run';

/*
 * Rendered-copy, keyboard and accessibility evidence for SCREEN-012, the Event Decision
 * (`SCR-CAR-014`, plan §8 D11). `CareerEventDecisionTest` asserts the resolved props — the
 * refusal, the grouped known outcomes, the incomplete flag, the store. These cases assert what
 * only a browser reaches: the advisor's rendered refusal, the N/A titles a sighted Trainer
 * hovers, the radio picker's keyboard behaviour, the 44px sweep on the record form, the
 * incomplete-outcome warning glyph, and an axe scan.
 *
 * Fixture strategy: create a run and log two turns through the run screen's correction form
 * (the same walk the Inheritance spec uses). Writes get 60s because `php artisan serve` is a
 * single-process `php -S`.
 */

const WRITE = { timeout: 60_000 } as const;

const createdRunUrls: string[] = [];

test.afterEach(async ({ page }) => {
    for (const url of createdRunUrls.splice(0)) {
        await deleteRun(page, url);
    }
});

/**
 * A career with logged turns, then the Event Decision screen for it. Two turns are enough for
 * the record form and one recorded choice to reappear in the picker.
 */
async function openEventDecision(page: import('@playwright/test').Page): Promise<void> {
    await page.goto('/training-runs/create', { waitUntil: 'domcontentloaded' });
    await page.locator('#app > *').first().waitFor();
    await page.locator('#trainee-combobox').fill('Rice Shower');
    await page.keyboard.press('Enter');
    await page.selectOption('select[name="scenario"]', { value: 'trackblazer' });
    await page.getByRole('button', { name: 'Create run' }).click();
    await page.waitForURL(/\/training-runs\/\d+$/, WRITE);
    createdRunUrls.push(page.url());

    const hatch = page.locator('details', { has: page.getByText('Correct a turn by hand') });
    await page.getByText('Correct a turn by hand').click();

    for (let turn = 1; turn <= 2; turn++) {
        await hatch.locator('input[name="turn"]').fill(String(turn));
        await hatch.locator('input[name="speed"]').fill('600');
        await hatch.locator('input[name="stamina"]').fill('500');
        await hatch.locator('input[name="power"]').fill('500');
        await hatch.locator('input[name="guts"]').fill('500');
        await hatch.locator('input[name="wit"]').fill('500');
        await hatch.getByRole('button', { name: 'Save correction' }).click();
        await expect(page.getByText(`Turn ${turn} logged.`)).toBeVisible(WRITE);
    }

    await page.goto(`${page.url()}/events`, { waitUntil: 'domcontentloaded' });
    await page.locator('#app > *').first().waitFor();
}

test('refuses a recommendation and shows the current career state with N/A titles', async ({ page }) => {
    await openEventDecision(page);

    await expect(page.getByRole('heading', { name: 'Coach recommendation' })).toBeVisible();
    await expect(page.getByText('No recommendation available')).toBeVisible();
    await expect(page.getByText(/holds no event advice/)).toBeVisible();

    await expect(page.getByRole('heading', { name: 'Current career state' })).toBeVisible();

    // The no-run figures on a fresh state are N/A with a reason, never a default zero.
    const naValues = page.locator('dd[title], dd span[title]');
    const naCount = await naValues.count();
    expect(naCount).toBeGreaterThan(0);
    for (let i = 0; i < naCount; i++) {
        await expect(naValues.nth(i)).toHaveAttribute('title', /.+/);
    }

    // No held computation anywhere in the main surface.
    await expect(page.locator('main')).not.toContainText('confidence');
    await expect(page.locator('main')).not.toContainText('win probability');
});

test('records a choice by keyboard and the choice returns as a known outcome', async ({ page }) => {
    await openEventDecision(page);

    await expect(page.getByRole('heading', { name: 'Record an event choice' })).toBeVisible();

    await page.getByRole('combobox', { name: 'Turn *' }).first().focus();
    await page.keyboard.press('ArrowDown');
    await page.keyboard.press('Enter');

    await page.getByRole('combobox', { name: 'Source *' }).first().focus();
    await page.keyboard.press('ArrowDown');
    await page.keyboard.press('Enter');

    await page.locator('input[name="source_name"]').fill('Go Beyond');
    await page.locator('input[name="choice_label"]').fill('Option A');
    await page.locator('textarea[name="origin_note"]').fill('+30 Energy, +10 Mood');

    await page.getByRole('button', { name: 'Record choice' }).click();

    await expect(page.getByText('Event choice recorded.')).toBeVisible(WRITE);

    // The history card carries the source label, the choice and the recorded outcome.
    await expect(page.getByText('Go Beyond').first()).toBeVisible();
    await expect(page.getByText('Option A').first()).toBeVisible();
    await expect(page.getByText('+30 Energy, +10 Mood').first()).toBeVisible();

    // Known outcomes group now exists.
    await expect(page.getByRole('heading', { name: 'Known outcomes on this run' })).toBeVisible();
    await expect(page.getByText('Known outcomes on this run').locator('..').getByText('Go Beyond')).toBeVisible();

    // Now pick the same event again: the keyboard radio group appears, nothing preselected,
    // and choosing one fills the choice field.
    const radios = page.locator('input[name="known_choice"]');
    await expect(radios).toHaveCount(1);
    await expect(page.locator('input[name="choice_label"]')).toHaveValue('');

    await radios.first().focus();
    await page.keyboard.press('Space');
    await expect(page.locator('input[name="choice_label"]')).toHaveValue('Option A');
});

test('flags a recorded choice with no outcome as incomplete', async ({ page }) => {
    await openEventDecision(page);

    await page.getByRole('combobox', { name: 'Turn *' }).first().focus();
    await page.keyboard.press('ArrowDown');
    await page.keyboard.press('Enter');

    await page.getByRole('combobox', { name: 'Source *' }).first().focus();
    await page.keyboard.press('ArrowDown');
    await page.keyboard.press('Enter');

    await page.locator('input[name="source_name"]').fill('Team Meeting');
    await page.locator('input[name="choice_label"]').fill('Drill');
    // Deliberately no outcome note: the warning is the point of this case.

    await page.getByRole('button', { name: 'Record choice' }).click();
    await expect(page.getByText('Event choice recorded.')).toBeVisible(WRITE);

    // The glyph-and-word warning, with its title reason.
    const warning = page.getByText(/Event outcome incomplete/).first();
    await expect(warning).toBeVisible();
    await expect(warning).toHaveAttribute('title', /choose manually/i);
});

test('sizes the event form controls to the 44px contract', async ({ page }) => {
    await openEventDecision(page);

    const controls = [
        page.getByRole('combobox', { name: 'Turn *' }).first(),
        page.getByRole('combobox', { name: 'Source *' }).first(),
        page.locator('input[name="source_name"]'),
        page.locator('input[name="choice_label"]'),
        page.getByRole('button', { name: 'Record choice' }),
        page.locator('textarea[name="origin_note"]'),
    ];

    for (const control of controls) {
        const box = await control.boundingBox();
        expect(box?.height ?? 0, 'an event form control is not sized to the 44px contract')
            .toBeGreaterThanOrEqual(44);
    }
});

test('passes an axe scan at WCAG A and AA', async ({ page }) => {
    await openEventDecision(page);

    const results = await buildAxe(page).analyze();

    expect(results.violations, JSON.stringify(results.violations, null, 2)).toEqual([]);
});

test('names the missing turns instead of showing an enabled empty form', async ({ page }) => {
    // A run with no logged turns: the record form is replaced by the named absence.
    await page.goto('/training-runs/create', { waitUntil: 'domcontentloaded' });
    await page.locator('#app > *').first().waitFor();
    await page.locator('#trainee-combobox').fill('Agnes Digital');
    await page.keyboard.press('Enter');
    await page.selectOption('select[name="scenario"]', { value: 'unity_cup' });
    await page.getByRole('button', { name: 'Create run' }).click();
    await page.waitForURL(/\/training-runs\/\d+$/, WRITE);
    createdRunUrls.push(page.url());

    const runId = page.url().match(/training-runs\/(\d+)/)?.[1];
    await page.goto(`/training-runs/${runId}/events`, { waitUntil: 'domcontentloaded' });
    await page.locator('#app > *').first().waitFor();

    await expect(page.getByText('No turns logged yet.')).toBeVisible();
    await expect(page.getByRole('heading', { name: 'Record an event choice' })).toHaveCount(0);

    // The empty history is the page's `empty` copy, not a blank list.
    await expect(page.getByText(/No event choices recorded yet/)).toBeVisible();
});
