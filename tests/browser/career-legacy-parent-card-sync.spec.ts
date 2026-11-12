import { test, expect } from '@playwright/test';
import { execFileSync } from 'node:child_process';
import { resolvedScratchDatabase } from './global-setup';

/*
 * RTX-01 — the Legacy step's parent card must show the pick as soon as it is made.
 *
 * Authority: `AUDIT-AND-VERIFICATION.md` section `## 04-ux-findings-retest.md`, lines 44-58 of the folded source.
 * Expected: "the step's own summary (\"Choose a veteran | Not chosen\") reflects the pick."
 * Actual:   "the select's value commits (`#legacy-pick-0.value === \"1\"`) but the visible summary
 *            above the form still reads \"Parent A | Not chosen\" until the step is saved and
 *            re-rendered."
 *
 * Root cause (read, not assumed): `LegacySelect.vue`'s `cardFor()` resolves a NON-manual slot from
 * the server props (`props.parentA`), while `assignParent()` writes only the local `useForm` row.
 * So the card — and the `Chosen` / `Not chosen` pill it drives — cannot see a library pick until a
 * save round-trips and the server rebuilds the prop. Manual (typed-name) slots already read from the
 * form, which is why only the library path shows the stale state.
 *
 * This is NOT F-03's defect and NOT the `BaseSelect` change-forwarding defect: the picker's
 * `@update:model-value` does fire and `form.legacies[i].legacy_id` does update. Only the resolver's
 * precedence is wrong. `S-NOT-F03` below holds that fact to the test.
 *
 * The library comes from the repository's own documented fixture rather than a hand-built row:
 * `docs/imports/ura-finale-runs.md` states that `UraFinaleRunsSeeder` imports seventeen tracker runs,
 * three of them Completed and therefore readable as Veterans — run 3 is `Maruzensky ([Formula R])`,
 * the row RTX-01's authority names. The seeder is idempotent by `import_source` and absent from
 * `DatabaseSeeder`, so this writes Trainer history only into the suite's scratch database.
 */

const DB = resolvedScratchDatabase();

const HARNESS_ENV = {
    ...process.env,
    DB_DATABASE: DB,
    UMA_DATABASE_ROLE: 'browser',
    UMA_EXPECTED_DATABASE: DB,
};

/** Run a tinker snippet against the suite's own scratch database and return its stdout. */
function tinker(code: string): string {
    return execFileSync('php', ['artisan', 'tinker', '--execute=' + code], {
        cwd: process.cwd(),
        env: HARNESS_ENV,
        encoding: 'utf8',
    });
}

/**
 * Seed the library the Legacy picker reads.
 *
 * `docs/imports/ura-finale-runs.md` documents the repository's own fixture for exactly this state:
 * `UraFinaleRunsSeeder` imports the seventeen tracker runs, three of which are `Completed` and
 * therefore readable as Veterans — run 3 is `Maruzensky ([Formula R])`, the very row RTX-01's
 * authority names. The seeder is idempotent by `import_source` and deliberately absent from
 * `DatabaseSeeder`, so calling it here writes Trainer history only into this suite's scratch file.
 *
 * Using it rather than a hand-built factory row also means the picker's label, its sparks and its
 * rank come from the same path a real Trainer's library takes.
 */
function seedVeteranLibrary(): void {
    execFileSync('php', ['artisan', 'db:seed', '--class=UraFinaleRunsSeeder', '--no-interaction'], {
        cwd: process.cwd(),
        env: HARNESS_ENV,
        encoding: 'utf8',
    });
}

/**
 * The library row the picker offers for a named trainee, created if the seeder did not.
 *
 * `UraFinaleRunsSeeder` imports the seventeen tracker runs but writes **no** `veterans` rows:
 * `docs/imports/ura-finale-runs.md` lists "the three new Completed runs appear in the Veterans
 * library" under its manual verification pass, not under what the seeder does. The picker's roster
 * reads `veterans`, so the row is created here against a run the seeder has already marked
 * Completed. `firstOrCreate` keeps the spec idempotent.
 */
function findVeteran(traineeName: string): { id: number; label: string } {
    tinker(
        '$run = \\App\\Models\\TrainingRun::query()'
        + '->where("status", \\App\\Enums\\RunStatus::Completed)'
        + '->whereHas("umamusume", function ($q) { $q->where("name", "' + traineeName + '"); })'
        + '->firstOrFail(); '
        + '\\App\\Models\\Veteran::query()->firstOrCreate('
        + '["training_run_id" => $run->id], '
        + '["tags" => [], "tags_normalized" => [], "notes" => null]);',
    );

    const out = tinker(
        'foreach (\\App\\Models\\Veteran::query()->get() as $v) { '
        + 'echo $v->id, "|", ($v->trainingRun?->umamusume?->name ?? ""), PHP_EOL; }',
    );

    const line = out.split('\n').map((l) => l.trim()).find((l) => l.includes(traineeName));

    expect(line, `no Veteran in the library names ${traineeName}: ${out}`).toBeDefined();

    const [id] = (line ?? '').split('|');
    expect(Number.isInteger(Number(id)), `the Veteran row for ${traineeName} has no usable id`).toBe(true);

    return { id: Number(id), label: traineeName };
}

test.describe.configure({ timeout: 240_000 });

test.beforeAll(() => {
    seedVeteranLibrary();
});



test('the parent card shows the pick before the step is saved', async ({ page }) => {
    const { id: veteranId } = findVeteran('Maruzensky');

    await page.goto('/career/setup/legacy', { waitUntil: 'domcontentloaded' });
    await page.locator('#app > *').first().waitFor();

    // Parent A's card, scoped by its own heading so Parent B cannot satisfy the assertions.
    const parentACard = page.locator('.card', { hasText: 'Parent A' }).first();
    await expect(parentACard.locator('.pill')).toHaveText('Not chosen');

    const picker = page.locator('#legacy-pick-0');
    await picker.selectOption(String(veteranId));

    // The picker commits — the authority confirms this half already worked.
    await expect(picker).toHaveValue(String(veteranId));

    // The defect: the card's own pill does not follow the pick until a save round-trips.
    await expect(
        parentACard.locator('.pill'),
        'the card still reads "Not chosen" although the picker holds a veteran',
    ).toHaveText('Chosen');

    // The card must have switched out of its empty state, which is what carries the veteran's name.
    // A bare text search would match the picker's own `<option>` too, so the empty-state line is the
    // unambiguous witness that the card itself resolved a name.
    await expect(
        parentACard.getByText('Pick from your veteran library, or leave this slot empty.'),
        'the card is still in its empty state although a veteran was picked',
    ).toHaveCount(0);
});

test('S-NOT-F03: the pick reaches the form model, so only the resolver is wrong', async ({ page }) => {
    const { id: veteranId } = findVeteran('Maruzensky');

    await page.goto('/career/setup/legacy', { waitUntil: 'domcontentloaded' });
    await page.locator('#app > *').first().waitFor();

    await page.locator('#legacy-pick-0').selectOption(String(veteranId));

    // `assignParent` writes `form.legacies[0].legacy_id`. If this is set, the `BaseSelect`
    // event-forwarding and `update:modelValue` paths are both healthy and RTX-01 is a precedence
    // bug in `cardFor`, not a second visit to F-03 or the BaseSelect repair.
    const pickerValue = await page.locator('#legacy-pick-0').inputValue();
    expect(pickerValue, 'the picker did not commit, which would be the BaseSelect defect again')
        .toBe(String(veteranId));

    // And the card's pill follows it, which is the fix: the value is in the model and the display
    // now reads it. Before the repair this assertion was the one that held the defect in place, so
    // it is inverted here — that inversion is the regression guard.
    const parentACard = page.locator('.card', { hasText: 'Parent A' }).first();
    await expect(
        parentACard.locator('.pill'),
        'the picker holds the veteran but the card still reads Not chosen',
    ).toHaveText('Chosen');
});

test('the picked veteran survives a save and reads back as Chosen', async ({ page }) => {
    const { id: veteranId } = findVeteran('Maruzensky');

    await page.goto('/career/setup/legacy', { waitUntil: 'domcontentloaded' });
    await page.locator('#app > *').first().waitFor();

    await page.locator('#legacy-pick-0').selectOption(String(veteranId));
    await page.getByRole('button', { name: /Save and stay/i }).first().click();
    await page.waitForLoadState('domcontentloaded');
    await page.locator('#app > *').first().waitFor();

    // After the round trip the server prop carries the pick, which is the behaviour the defect
    // currently relies on. This case guards the fix from breaking the saved state.
    const parentACard = page.locator('.card', { hasText: 'Parent A' }).first();
    await expect(parentACard.locator('.pill')).toHaveText('Chosen');
    await expect(page.locator('#legacy-pick-0')).toHaveValue(String(veteranId));
});
