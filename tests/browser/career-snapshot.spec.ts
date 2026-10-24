import { test, expect } from '@playwright/test';
import { buildAxe } from '../utils/accessibility';
import { STARTING_STATE, RICE_SHOWER } from './fixtures/riceShowerUnityCup.fixture';

/*
 * Rendered-copy and behaviour evidence for the snapshot record surface (`SCR-CAR-0xx`, the second door
 * beside the setup wizard): `/career/snapshot` (the entry), `/career/snapshot/setup` (the form) and
 * `/career/snapshot/review` (the read-back before the write).
 *
 * The server-side tests own the payload and the refusals; these own what only a browser can reach: the
 * copy the Trainer reads, the three state treatments the read-back keeps apart, that every state control
 * carries its own field name as an accessible name, the one accent per screen, and the axe scan.
 *
 * **Nothing here creates a run.** The suite stops at the review screen, which is a GET of the session
 * payload, so a fresh context leaves no rows behind. `rice-shower-unity-cup.spec.ts` owns the write.
 */

const WRITE = { timeout: 60_000 } as const;

async function hydrate(page: import('@playwright/test').Page, url: string): Promise<void> {
    await page.goto(url, { waitUntil: 'domcontentloaded' });
    await page.locator('#app > *').first().waitFor();
}

/** Fill the setup form and land on the read-back. Returns the review URL's own page. */
async function fillSetupAndReview(page: import('@playwright/test').Page): Promise<void> {
    await hydrate(page, '/career/snapshot/setup');

    await page.selectOption('select[name="scenario"]', { value: STARTING_STATE.scenario });
    await page.selectOption('select[name="umamusume_id"]', { label: RICE_SHOWER.name });
    await page.selectOption('select[name="career_year"]', { value: String(STARTING_STATE.careerYear) });
    await page.selectOption('select[name="career_month"]', { value: String(STARTING_STATE.careerMonth) });
    await page.selectOption('select[name="career_phase"]', { value: STARTING_STATE.careerPhase });

    await page.locator('input[name="speed"]').fill(String(STARTING_STATE.stats.speed));
    await page.locator('input[name="stamina"]').fill(String(STARTING_STATE.stats.stamina));
    await page.locator('input[name="power"]').fill(String(STARTING_STATE.stats.power));

    await page.getByRole('button', { name: 'Review snapshot' }).click();
    await page.waitForURL(/\/career\/snapshot\/review/, WRITE);
    await page.locator('#app > *').first().waitFor();
}

test.describe('Snapshot entry', () => {
    test('says what the door is for and offers one way in', async ({ page }) => {
        await hydrate(page, '/career/snapshot');

        await expect(page.getByRole('heading', { name: 'Start from existing snapshot', level: 1 })).toBeVisible();
        await expect(page.getByRole('heading', { name: 'Start from existing snapshot', level: 2 })).toBeVisible();

        // The one promise the screen makes: the position entered becomes the run's own position, and
        // nothing is guessed at. Both sentences are the page's own copy, not a summary of it.
        await expect(page.getByText(/the position you enter becomes the run's own position/)).toBeVisible();
        await expect(page.getByText(/recorded as absent rather than guessed at/)).toBeVisible();

        // One accent on the screen (Von Restorff): the primary-action fill is the only accented control.
        await expect(page.locator('.bg-primary-action')).toHaveCount(1);

        await page.getByRole('link', { name: 'Enter the snapshot' }).click();
        await page.waitForURL(/\/career\/snapshot\/setup/, WRITE);
        await page.locator('#app > *').first().waitFor();
        await expect(page.getByRole('heading', { name: 'Scenario', level: 2 })).toBeVisible();
    });

    test('passes the axe A + AA scan', async ({ page }) => {
        await hydrate(page, '/career/snapshot');
        const results = await buildAxe(page).analyze();

        expect(results.violations, JSON.stringify(results.violations, null, 2)).toEqual([]);
    });
});

test.describe('Snapshot setup', () => {
    test('renders the six sections, one accent, and a labelled control per state field', async ({ page }) => {
        await hydrate(page, '/career/snapshot/setup');

        for (const heading of [
            'Scenario',
            'Trainee',
            'Career position',
            'Current state',
            'Notes',
            'Recorded on their own screens',
        ]) {
            await expect(page.getByRole('heading', { name: heading, level: 2 })).toBeVisible();
        }

        // Prägnanz: the section headers are the heading tier, never a body-size literal.
        await expect(page.locator('h2.text-base')).toHaveCount(0);

        // One accent per screen: the submit is the only primary-action control.
        await expect(page.locator('.bg-primary-action')).toHaveCount(1);

        // Each state field's own name reaches its control. An interpolated attribute is a literal in
        // Vue, so this asserts the rendered name rather than the intent: the number input answers to
        // `Speed`, not to `{{ field.label }}`.
        await expect(page.getByLabel('Speed', { exact: true })).toBeVisible();
        await expect(page.getByLabel('Stamina', { exact: true })).toBeVisible();
        await expect(page.getByLabel('Wit', { exact: true })).toBeVisible();
        await expect(page.getByText('{{', { exact: false })).toHaveCount(0);

        // Every state control carries the 44px floor.
        const speed = await page.getByLabel('Speed', { exact: true }).boundingBox();
        expect(speed?.height ?? 0, 'the Speed control is not sized to the 44px contract').toBeGreaterThanOrEqual(44);
    });

    test('opens with no career position chosen', async ({ page }) => {
        await hydrate(page, '/career/snapshot/setup');

        // A-100: the Month select had no empty option while Year and Half did, so a fresh form left it with
        // no option selected at all (selectedIndex -1, which renders blank and reports '' on submit) rather
        // than the placeholder its two siblings show. All three controls now name their empty state.
        for (const [name, label] of [['career_year', 'Year'], ['career_month', 'Month'], ['career_phase', 'Half']] as const) {
            const select = page.locator(`select[name="${name}"]`);
            await expect(select, name).toHaveValue('');
            await expect(select.locator('option:checked'), name).toHaveText(label);
        }
    });

    test('names the unknown flag beside the field it silences', async ({ page }) => {
        await hydrate(page, '/career/snapshot/setup');

        // One flag per state field, each an accessible name of its own and each tied back to the input
        // it disables, so a screen reader hears which field the flag is about.
        const flags = page.getByRole('checkbox', { name: "I don't know this value" });
        await expect(flags.first()).toBeVisible();

        const speedFlag = page.locator('#snapshot-unknown-speed');
        await expect(speedFlag).toHaveAttribute('aria-describedby', 'snapshot-speed');
    });

    test('passes the axe A + AA scan', async ({ page }) => {
        await hydrate(page, '/career/snapshot/setup');
        const results = await buildAxe(page).analyze();

        expect(results.violations, JSON.stringify(results.violations, null, 2)).toEqual([]);
    });
});

test.describe('Snapshot review', () => {
    test('names the reviewed career position in the client\'s own words', async ({ page }) => {
        // R4-08 date half: the read-back states the position the Trainer entered, using the same
        // derivation the race header uses for the run the write creates.
        await fillSetupAndReview(page);

        await expect(page.getByText(/Position: Senior Year . Early October/)).toBeVisible();
    });

    test('reads the three states back apart and offers one commit', async ({ page }) => {
        await fillSetupAndReview(page);

        await expect(page.getByRole('heading', { name: 'Review snapshot', level: 1 })).toBeVisible();
        await expect(page.getByText(/a field marked Unknown is one the client/)).toBeVisible();

        // The read-back keeps known, unknown and not-provided apart as three states on the element,
        // not as one blank. The state is carried on `data-state`, which is what the copy promises.
        const states = page.locator('[data-state]');
        await expect(states.first()).toBeVisible();
        const rendered = await states.evaluateAll((nodes) => [...new Set(nodes.map((n) => n.getAttribute('data-state')))]);
        expect(rendered.length, 'the read-back flattened its states').toBeGreaterThanOrEqual(2);
        for (const state of rendered) {
            expect(['known', 'unknown', 'not_provided']).toContain(state);
        }

        // One accent on the screen: the commit is the only primary-action control.
        await expect(page.locator('.bg-primary-action')).toHaveCount(1);

        // The way back is present and does not commit anything.
        await expect(page.getByRole('link', { name: 'Edit the snapshot' })).toBeVisible();
    });

    test('returns to the form without writing a run', async ({ page }) => {
        await fillSetupAndReview(page);

        await page.getByRole('link', { name: 'Edit the snapshot' }).click();
        await page.waitForURL(/\/career\/snapshot\/setup/, WRITE);
        await page.locator('#app > *').first().waitFor();

        // The form came back holding what was entered, which is the draft being a draft.
        await expect(page.locator('input[name="speed"]')).toHaveValue(String(STARTING_STATE.stats.speed));

        // And the write never happened: the careers list is still empty.
        await hydrate(page, '/training-runs');
        await expect(page.getByText('No runs yet')).toBeVisible();
    });

    test('passes the axe A + AA scan', async ({ page }) => {
        await fillSetupAndReview(page);
        const results = await buildAxe(page).analyze();

        expect(results.violations, JSON.stringify(results.violations, null, 2)).toEqual([]);
    });
});
