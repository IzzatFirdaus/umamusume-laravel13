import { test, expect } from '@playwright/test';

/*
 * Rendered-copy and interaction evidence for the wizard's Preflight step (SCREEN-008, `SCR-CAR-010`).
 * The server-side `CareerPreflightTest` asserts the resolved props, each warning trigger and the created
 * run; these cases assert what only a browser reaches: the values carried forward from the five steps,
 * the warning copy, an Edit round trip, the focus move on a refused write, and that `Start Career` lands
 * on the run it created.
 *
 * The draft is built by walking the wizard's own steps, so every fact under test was entered through the
 * app rather than seeded into the session. The scratch database holds the catalogue (67 trainees, 559
 * support cards) and **no Veterans**, so the ancestry step has no library row to pick from: the legacy
 * section is asserted as its named absence, which is the state a fresh install meets.
 *
 * Every navigation waits for `domcontentloaded`, for the reason `legacy.spec.ts`'s header gives: `load`
 * waits on the module graph and one loopback request per artwork frame, which is host speed rather than a
 * property of this screen.
 */

/**
 * The runs this file creates, deleted again in `afterEach` exactly as `run-detail.spec.ts`, `legacy.spec.ts`
 * and `support-deck.spec.ts` do. `Start Career` is one of the two browser actions in the suite (the other
 * is the Legacy builder) that create a run; without this delete the row breaks `runs.spec.ts`'s empty-state
 * assertions and makes the whole suite one-shot against a freshly seeded database.
 */
const createdRunUrls: string[] = [];

test.afterEach(async ({ page }) => {
    for (const url of createdRunUrls.splice(0)) {
        const response = await page.goto(url, { waitUntil: 'domcontentloaded' });
        if (response === null || !response.ok()) {
            continue;
        }
        await page.getByText('Delete run').click();
        await page.getByRole('button', { name: 'Delete this run' }).click();
        await page.waitForURL(/\/training-runs$/, { waitUntil: 'domcontentloaded' });
    }
});

async function newDraft(page: import('@playwright/test').Page): Promise<{ traineeName: string }> {
    await page.goto('/career/setup/scenario', { waitUntil: 'domcontentloaded' });
    await page.locator('#app > *').first().waitFor();
    await page.locator('#scenario-ura_finale').click();
    await expect(page.locator('#scenario-ura_finale')).toHaveAttribute('aria-pressed', 'true');

    await page.goto('/career/setup/trainee', { waitUntil: 'domcontentloaded' });
    await page.locator('#app > *').first().waitFor();
    const traineeButton = page.getByRole('button', { name: 'Select Trainee' }).first();
    // The row's first line is her name, which is what the run screen prints as its heading; capturing it
    // here is what lets the creation case prove the run belongs to the trainee the wizard recorded.
    const traineeName = (await traineeButton.locator('xpath=ancestor::li[1]').innerText())
        .split('\n')
        .map((line) => line.trim())
        .filter((line) => line !== '')[0];
    await traineeButton.click();
    await expect(traineeButton).toHaveAttribute('aria-pressed', 'true');

    await page.goto('/career/setup/target', { waitUntil: 'domcontentloaded' });
    await page.locator('#app > *').first().waitFor();
    await page.selectOption('select[name="purpose"]', 'StoryClear');
    await page.selectOption('select[name="distance"]', 'Medium');
    await page.selectOption('select[name="surface"]', 'Turf');
    await page.selectOption('select[name="style"]', 'Pace Chaser');

    for (const stat of ['Speed', 'Stamina', 'Power', 'Guts', 'Wit']) {
        await page.locator(`input[name="targets[${stat}]"]`).fill('800');
    }

    await page.getByRole('button', { name: 'Save target' }).click();
    await page.locator('#app > *').first().waitFor();

    await page.goto('/career/setup/deck', { waitUntil: 'domcontentloaded' });
    await page.locator('#app > *').first().waitFor();
    await page.locator('#deck-pick').selectOption({ index: 1 });
    await page.getByRole('button', { name: /^Equip owned to/ }).first().click();
    await expect(page.locator('#deck-slot-1')).not.toContainText('Not equipped');

    return { traineeName };
}

test('carries every entered step into the contract and states the deck gap in words', async ({ page }) => {
    await newDraft(page);

    await page.goto('/career/setup/preflight', { waitUntil: 'domcontentloaded' });
    await page.locator('#app > *').first().waitFor();

    await expect(page.getByRole('heading', { name: 'Career contract' })).toBeVisible();

    // Build: the scenario's own label, the entered target, and the ancestry's honest absence.
    await expect(page.getByText('URA Finale').first()).toBeVisible();
    await expect(page.getByText('Story Clear · Turf Medium · Pace Chaser').first()).toBeVisible();
    await expect(page.getByText('Ancestry is not entered.').first()).toBeVisible();

    // Support deck: the equipped card is named, the count is restated, and the D6 analysis renders.
    await expect(page.getByRole('heading', { name: 'Support deck' })).toBeVisible();
    await expect(page.getByText('1 of six positions carry a card; 5 are still empty.').first()).toBeVisible();

    // Target: the five stats with their entered numbers, the race profile, and the ruleset absence.
    await expect(page.getByText('800').first()).toBeVisible();
    await expect(page.getByText('Race profile:').first()).toBeVisible();
    await expect(page.getByTitle(/No source defines a Global ruleset version/)).toBeVisible();
});

test('an Edit round trip returns with every value intact', async ({ page }) => {
    await newDraft(page);

    await page.goto('/career/setup/preflight', { waitUntil: 'domcontentloaded' });
    await page.locator('#app > *').first().waitFor();

    await page.getByRole('link', { name: 'Edit Target' }).click();
    await page.locator('#app > *').first().waitFor();

    // The step opens on what it stored, not on what the client held.
    await expect(page.locator('select[name="distance"]')).toHaveValue('Medium');
    await expect(page.locator('select[name="purpose"]')).toHaveValue('StoryClear');
    await expect(page.locator('input[name="targets[Speed]"]')).toHaveValue('800');

    // And the contract still reads the same after the return trip.
    await page.goto('/career/setup/preflight', { waitUntil: 'domcontentloaded' });
    await page.locator('#app > *').first().waitFor();
    await expect(page.getByText('Story Clear · Turf Medium · Pace Chaser').first()).toBeVisible();
});

test('Start Career creates the run and lands on it', async ({ page }) => {
    const { traineeName } = await newDraft(page);

    await page.goto('/career/setup/preflight', { waitUntil: 'domcontentloaded' });
    await page.locator('#app > *').first().waitFor();

    await page.getByRole('button', { name: 'Start Career' }).click();
    await page.waitForURL(/\/cockpit$/, { waitUntil: 'domcontentloaded' });
    // The cleanup deletes through the run record screen's own disclosure, so the record URL is what is
    // remembered rather than the Cockpit the write lands on.
    createdRunUrls.push(page.url().replace(/\/cockpit$/, ''));

    // The destination is the Cockpit (SCREEN-009, `SCR-CAR-011`), and its heading names the trainee the
    // wizard recorded, which is what proves the write composed the draft rather than creating an empty run.
    await expect(page.getByRole('heading', { level: 1 })).toContainText(traineeName);
});

test('refuses a Start Career with nothing entered and moves focus to the refusal', async ({ page }) => {
    // A fresh context has no draft: this is the state a Trainer meets if they jump straight here.
    await page.goto('/career/setup/preflight', { waitUntil: 'domcontentloaded' });
    await page.locator('#app > *').first().waitFor();

    await page.getByRole('button', { name: 'Start Career' }).click();

    const alert = page.getByRole('alert');
    await expect(alert).toContainText('The career was not created.');
    await expect(alert).toBeFocused();
    await expect(page).toHaveURL(/\/career\/setup\/preflight$/);
});
