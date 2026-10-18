/*
 * Files one career into the Veteran library, by the path a Trainer takes: create the run, finish it on
 * its own status control, then save it as a Veteran. This is the only path that puts a row in `veterans`,
 * so a spec that needs the library populated builds its fixtures through it rather than inserting rows.
 *
 * The helper is deliberately NOT responsible for cleanup: it returns the bare run URL and the calling spec
 * tracks it, because each spec owns its `afterEach` teardown (`tests/utils/delete-run.ts` cascades the
 * library row with the run). Keeping the array in the spec keeps the fixture lifecycle readable next to
 * the assertions that depend on it.
 */
import { expect, type Page } from '@playwright/test';
import { openIdentityDisclosure } from './identity-disclosure';

const WRITE = { timeout: 60_000 } as const;

/**
 * Files one career and returns its bare record URL.
 *
 * `tag` is nullable on purpose: an untagged career must actually be on screen for a "No tags recorded"
 * assertion to be a fact rather than a phrase the spec never earns.
 *
 * `notes` is optional and it is how a spec reaches the shape the legacy tracker wrote: `RecordVeteran`
 * stores whatever the Trainer types, and the tracker's seeder appended one shared ancestry sentence to
 * every career it imported. Filing with that sentence in the note is the only way a browser case can see
 * the library split it back off (`resources/js/domain/veteranNote.ts`), because the filing path itself
 * never writes one.
 *
 * `scenario` is optional and defaults to `unity_cup`, the value this helper has always posted. A spec
 * that needs two careers of the SAME trainee to carry DIFFERENT labels has to vary the scenario, because
 * the label is `trainee · scenario · date` and two careers filed in the same minute share the date. The
 * key must be one `config/scenarios.php` declares, since the create request validates against it.
 */
export async function fileCareer(
    page: Page,
    traineeName: string,
    tag: string | null,
    notes: string | null = null,
    scenario: string = 'unity_cup',
): Promise<string> {
    await page.goto('/training-runs/create', { waitUntil: 'domcontentloaded' });
    await page.locator('#app > *').first().waitFor();
    await page.locator('#trainee-combobox').fill(traineeName);
    await page.keyboard.press('Enter');
    await page.selectOption('select[name="scenario"]', { value: scenario });
    await page.getByRole('button', { name: 'Create run' }).click();
    await page.waitForURL(/\/training-runs\/\d+\/cockpit$/, WRITE);

    // The bare record URL, not the Cockpit the create redirect lands on: cases address sub-screens by
    // appending to it (`${runUrl}/veteran`).
    const runUrl = page.url().replace(/\/cockpit$/, '');

    // Scoped to its own form: the Cockpit's header edits and its correction panel share the page, so a
    // name query is ambiguous and a label query worse.
    await openIdentityDisclosure(page);
    const statusForm = page.locator('form').filter({ has: page.getByRole('button', { name: 'Change status' }) });

    await statusForm.locator('select[name="status"]').selectOption('Completed');
    await statusForm.getByRole('button', { name: 'Change status' }).click();
    await expect(page.getByText('Run updated.')).toBeVisible(WRITE);

    await page.goto(`${runUrl}/veteran`, { waitUntil: 'domcontentloaded' });
    await page.locator('#app > *').first().waitFor();

    if (tag !== null) {
        await page.getByRole('button', { name: new RegExp(`^${tag}`) }).click();
    }

    if (notes !== null) {
        await page.locator('#veteran-notes').fill(notes);
    }

    await page.getByRole('button', { name: 'Save Veteran' }).click();
    await page.waitForURL(/\/veterans\/\d+$/, WRITE);

    return runUrl;
}
