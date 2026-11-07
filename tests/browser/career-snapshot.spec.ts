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
 * **Nothing here creates a run.** The suite stops at the review screen, which reads the posted payload
 * back from the session, so a fresh context leaves no rows behind. `rice-shower-unity-cup.spec.ts`
 * owns the write.
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

    // Notes take 5000 characters, which is exactly the payload that must not reach a URL. Filled in
    // the shared helper so every case below walks the form with it set.
    await page.locator('textarea[name="notes"]').fill('a note long enough that a URL would carry it');

    // The page draws the wireframe's pair in the head band and the same submit at the foot of the
    // form, so both buttons carry the label. The head one is the wireframe's first action.
    await page.locator('[data-region="hrow-actions"]').getByRole('button', { name: 'Review snapshot' }).click();
    await page.waitForURL(/\/career\/snapshot\/review/, WRITE);
    await page.locator('#app > *').first().waitFor();

    // AUD-20261102-008 F-01: the form posts, so nothing the Trainer typed arrives in the address bar.
    // A query string would carry the note into the browser history and every request log between
    // here and the review, which is the whole finding.
    expect(new URL(page.url()).search, 'the review URL carries a query string').toBe('');
    expect(page.url(), 'the note reached the URL').not.toContain('long enough');
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

    test('offers Review only after a snapshot has been entered', async ({ page }) => {
        // AUD-20261102-007 F-01: the Review step is a read-back, so it is not a door until a draft
        // exists. A first visit states the remedy rather than leading to an empty read-back.
        await hydrate(page, '/career/snapshot');

        const steps = page.getByRole('navigation', { name: 'Snapshot steps' });
        await expect(steps.getByRole('link', { name: 'Review' })).toHaveCount(0);
        await expect(steps.getByRole('button', { name: 'Review' })).toBeDisabled();
        await expect(page.getByText('Enter at least one record to continue.')).toBeVisible();

        // A filled setup parks the draft for review, and the same step becomes a link.
        await fillSetupAndReview(page);
        await hydrate(page, '/career/snapshot');
        await expect(steps.getByRole('link', { name: 'Review' })).toBeVisible();
        await expect(page.getByText('Enter at least one record to continue.')).toHaveCount(0);
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

    test('shows every concurrent position error, not just the first', async ({ page }) => {
        // AUD-20261102-008 F-02: the block used to print only the first truthy error in an or chain,
        // hiding the other two when year, month and half were all refused together.
        await hydrate(page, '/career/snapshot/setup');

        // The controls carry the browser's `required`; the server refuses independently, and the
        // block must name every refused position field. Turn native validation off to reach it.
        await page.locator('#snapshot-form').evaluate((form) => form.setAttribute('novalidate', ''));

        await page.locator('[data-region="hrow-actions"]').getByRole('button', { name: 'Review snapshot' }).click();

        const positionErrors = page.locator('section[aria-labelledby="snapshot-position-heading"] ul.text-risk li');
        await expect(positionErrors).toHaveCount(3);
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

    test('reads back the trainee and the scenario it will write (R7-99)', async ({ page }) => {
        // The screen's own copy promises "This is exactly what will be written. Read it back", and both
        // identity fields are required by the write. Before R7-99 the read-back showed only the eight
        // state fields, so the two things the snapshot is *about* were the two things a Trainer could
        // not confirm. (Snapshot walk §7.3 S04.)
        await fillSetupAndReview(page);

        // `Known · Trainee <name>` and `Known · Scenario <label>` lead the list: the identity is what
        // the write is about, so it reads first. The names come from the setup form's own selections.
        await expect(page.getByText(/Known · Trainee /).first()).toBeVisible();
        await expect(page.getByText(/Known · Scenario /).first()).toBeVisible();

        // And they are real values, not the absence sentence.
        await expect(page.getByText('Not provided · Trainee')).toHaveCount(0);
        await expect(page.getByText('Not provided · Scenario')).toHaveCount(0);
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

    test('refuses the commit until the no-undo acknowledgement is ticked', async ({ page }) => {
        // AUD-20261102-009 F-01: the commit is final, so the read-back alone is not the gate; the
        // Trainer must state that they understand it cannot be undone.
        await fillSetupAndReview(page);

        const commit = page.getByRole('button', { name: 'Record snapshot' });
        await expect(commit).toBeDisabled();

        await page.getByLabel('I understand this snapshot cannot be undone.').check();
        await expect(commit).toBeEnabled();
    });

    test('passes the axe A + AA scan', async ({ page }) => {
        await fillSetupAndReview(page);
        const results = await buildAxe(page).analyze();

        expect(results.violations, JSON.stringify(results.violations, null, 2)).toEqual([]);
    });
});

test.describe('Snapshot review, bare visit', () => {
    test('states that nothing has been entered, with every field not provided', async ({ page }) => {
        // The audit's severe finding: this URL used to render the Setup heading because the GET
        // validated like a submit. The HFW's empty review is a real screen: eight Not provided tiles,
        // the stepper naming Review, and a confirm with nothing to re-post.
        await page.goto('/career/snapshot/review', { waitUntil: 'networkidle' });
        await page.locator('#app > *').first().waitFor();

        await expect(page.getByRole('heading', { name: 'Review snapshot', level: 1 })).toBeVisible();
        await expect(page.getByRole('navigation', { name: 'Snapshot steps' })).toBeVisible();
        // R7-99: ten rows, not eight — the two identity rows lead the read-back and a bare visit
        // reports them as not provided like every other field.
        await expect(page.locator('[data-state="not_provided"]')).toHaveCount(10);
        await expect(page.getByRole('status').filter({ hasText: 'No snapshot has been entered yet' })).toBeVisible();
        await expect(page.getByRole('button', { name: 'Record snapshot' })).toBeDisabled();
    });
});
