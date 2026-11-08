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

    // Each `Add Spark` names its own parent in an `sr-only` suffix, so the button is addressed by the
    // parent it belongs to rather than by its index. Index addressing broke as soon as a neighbouring
    // editor started rendering (R7-36 opened the grandparent sections), because `nth(0)` was then a
    // grandparent's button rather than Parent A's.
    const addSpark = (parent: string) =>
        page.getByRole('button', { name: new RegExp(`Add Spark to ${parent}`) });

    /**
     * Choose a Spark row's `Target` through the combobox (`S-A-P02.4`).
     *
     * The field is an `<input role="combobox">` since `S-A-P02.3`, so a bare `fill()` types a filter and
     * never commits — the write would post an empty target. Typing a prefix and clicking the option is the
     * path a Trainer takes. `category` is set first when the row's default (`Stat`) would not offer the
     * value, because the list is the category's own vocabulary.
     */
    const pickSparkTarget = async (index: number, label: string, category?: string): Promise<void> => {
        if (category !== undefined) {
            await page.getByRole('combobox', { name: 'Category' }).nth(index).selectOption(category);
        }

        const target = page.getByRole('combobox', { name: 'Applies to' }).nth(index);
        await target.click();
        await target.fill(label.slice(0, 3));
        await page.locator('[role="option"]', { hasText: label }).first().click();
    };

    await page.locator('#rank-parent_a').fill('3');
    // R7-33: a letter rank is recorded alongside the stars, so the screen has something to render.
    await page.locator('#rank-letter-parent_a').fill('B+');
    await page.locator('#ancestor-parent_a-0').fill('Symboli Rudolf');
    await page.locator('#ancestor-parent_a-1').fill('Mejiro McQueen');
    await addSpark('Parent A').click();
    await addSpark('Parent A').click();

    // `Applies to` is the picker's combobox since `S-A-P02.3`, so each Spark's target is chosen from the
    // list its Category scopes rather than typed (`S-A-P02.4`). The Category is set explicitly because the
    // row's default is `Stat`, which would not offer `Medium`.
    await page.getByRole('combobox', { name: 'Kind' }).nth(0).selectOption('blue');
    await pickSparkTarget(0, 'Speed');
    await page.getByRole('spinbutton', { name: 'Stars' }).nth(0).fill('3');

    await page.getByRole('combobox', { name: 'Kind' }).nth(1).selectOption('pink');
    await pickSparkTarget(1, 'Medium', 'Aptitude');
    await page.getByRole('spinbutton', { name: 'Stars' }).nth(1).fill('2');

    await page.locator('#rank-parent_b').fill('2');
    await page.locator('#ancestor-parent_b-0').fill('Special Week');
    await addSpark('Parent B').click();
    await page.getByRole('combobox', { name: 'Kind' }).nth(2).selectOption('blue');
    await pickSparkTarget(2, 'Stamina');
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

test('renders the entered letter rank beside the star grade (R7-33, R7-80)', async ({ page }) => {
    // The Legacy step records a letter per node; `AncestryGraph` and this page's own payload both carry
    // it as `rank_label`. The screen drew only stars, so a Trainer who entered `B+` read `★★★` and no
    // letter. The stars stay — they render a value the entry form genuinely recorded (R7-34) — and the
    // letter now prints beside them.
    await openInheritanceEvent(page);

    await expect(page.getByText(/Rank B\+/).first()).toBeVisible();

    // And the parent's own row carries the star grade it recorded, unchanged.
    await expect(page.getByText(/★{2,}/).first()).toBeVisible();
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

test('holds the primary disabled until the required fields are filled (R7-35)', async ({ page }) => {
    // `StoreInheritanceEventRequest` requires `turn` and `source_name`. The primary carried only
    // `:disabled="form.processing"`, so it invited a submit the server would reject. `choice_label`,
    // `deltas` and `origin_note` are nullable and must NOT be gated: a Trainer may record an event
    // with no choice named, and a gate that demanded one would refuse valid input.
    await openInheritanceEvent(page);

    const primary = page.getByRole('button', { name: /Record inheritance/ });

    // `turn` is prefilled from the run; `source_name` is not, so the primary starts inert.
    await expect(primary).toBeDisabled();

    // The required fields live inside the page's own "(required to record)" disclosure, closed by
    // default — the same shape R7-57 measured on the Training screen.
    await page.getByText('Record what this moment granted (required to record)').click();
    await page.locator('input[name="source_name"]').fill('Valentine chocolate');

    // Both required fields now present.
    await expect(primary).toBeEnabled();

    // Clearing one makes it inert again, proving the gate reads the values.
    await page.locator('input[name="turn"]').fill('');
    await expect(primary).toBeDisabled();

    await page.locator('input[name="turn"]').fill('4');

    // `choice_label` is nullable on the request, so an event with no choice named still records.
    await expect(primary).toBeEnabled();
});
