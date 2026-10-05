import { test, expect } from '@playwright/test';

// Rendered-copy and behaviour evidence for the ported import screens (SCR-RUN-004/005, ADR-0020 §1).
// `HistoricalRunImportTest` and `ImportErrorCopyTest` assert the parse, the round trip and the MessageBag;
// these assert what the browser shows.
//
// `ImportErrorCopyTest` closed its own header with the sentence the form prints in front of the
// per-stat messages ("A speed value is outside what this scenario allows:") and said the composite was
// "not verified here: it needs a browser pass on the landed view". That pass is the ceiling test below.
//
// Nothing here completes a commit: the confirm step is reached and read, never posted, so no run is
// written into the development database the browser suite shares. The write itself is proven in Pest,
// where the database is per-test.
//
// Fixtures are the app's own seeded scratch database. `unity_cup` sets a speed ceiling of 1300, so a
// row at 1400 is a ceiling break the scenario owns rather than a number invented for the test.

const HEADERS = 'turn,speed,stamina,power,guts,wit,sp,condition,energy,mood,fans';

function csv(rows: string[]): string {
    return [HEADERS, ...rows].join('\n');
}

const okRow = (turn: string): string => `${turn},600,400,300,250,200,40,,70,GOOD,1200`;

async function fillForm(page: import('@playwright/test').Page, body: string): Promise<void> {
    const trainee = page.locator('select[name="umamusume_id"]');
    const first = await trainee.locator('option').nth(1).getAttribute('value');
    await trainee.selectOption(first ?? '');

    await page.locator('select[name="scenario"]').selectOption('unity_cup');
    await page.locator('textarea[name="csv"]').fill(body);
    await page.getByRole('button', { name: 'Preview import' }).click();
}

test('states the column list the export writes, so the paste target is checkable before typing', async ({ page }) => {
    await page.goto('/training-runs/import');
    await page.locator('#app > *').first().waitFor();

    await expect(page.getByRole('heading', { name: 'Import a historical run', level: 1 })).toBeVisible();
    await expect(page.getByText(/nothing is created until you confirm/)).toBeVisible();

    // The hint is the request's own HEADERS joined for reading, and the placeholder the same list joined
    // for pasting, so a Trainer comparing a sheet sees the exact order the parser refuses anything else
    // against.
    await expect(page.getByText(HEADERS.split(',').join(', '))).toBeVisible();
    await expect(page.locator('textarea[name="csv"]')).toHaveAttribute('placeholder', HEADERS);
});

test('sizes the import form controls to the 44px contract', async ({ page }) => {
    await page.goto('/training-runs/import');
    await page.locator('#app > *').first().waitFor();

    for (const selector of ['select[name="umamusume_id"]', 'select[name="scenario"]', 'select[name="status"]', 'input[name="file"]']) {
        const box = await page.locator(selector).boundingBox();
        expect(box?.height ?? 0, `${selector} is not sized to the 44px contract`).toBeGreaterThanOrEqual(44);
    }

    const submit = await page.getByRole('button', { name: 'Preview import' }).boundingBox();
    expect(submit?.height ?? 0, 'the Preview import button is not sized to the 44px contract').toBeGreaterThanOrEqual(44);

    // Status defaults to Completed here rather than Active as on the create form, because this page is
    // for a career that is already finished.
    await expect(page.locator('select[name="status"]')).toHaveValue('Completed');
});

test('previews every row before anything is written, and says turns are all the file carries', async ({ page }) => {
    await page.goto('/training-runs/import');
    await page.locator('#app > *').first().waitFor();

    await fillForm(page, csv([okRow('1'), okRow('2')]));

    await expect(page.getByRole('heading', { name: 'Confirm the import', level: 2 })).toBeVisible();
    await expect(page.getByRole('button', { name: 'Import 2 turns' })).toBeVisible();
    await expect(page.getByRole('link', { name: 'Start over' })).toBeVisible();

    await expect(page.getByRole('note')).toContainText('This imports turns only');

    // Every row is on screen before the commit, which is the whole reason the flow is two POSTs.
    const region = page.getByRole('region', { name: 'Rows to import' });
    await expect(region.locator('tbody tr')).toHaveCount(2);
    await expect(region.locator('tbody tr').nth(1)).toContainText('600');

    // The summary names the trainee, the scenario label rather than its slug, and the count.
    await expect(region.locator('caption')).toHaveText('Every turn row that will be written');
    await expect(page.getByText(/2 turns$/)).toBeVisible();
    await expect(page.getByText(/No scenario/)).toHaveCount(0);
});

test('puts the ceiling break on the row it belongs to and prints the sentence the form completes', async ({ page }) => {
    await page.goto('/training-runs/import');
    await page.locator('#app > *').first().waitFor();

    // Turn 7 at speed 1400 against unity_cup's 1300. The Form Request rewrites its own message to name
    // the turn (KI-46); the form is the half that names the column.
    await fillForm(page, csv([okRow('1'), '7,1400,400,300,250,200,40,,70,GOOD,1200']));

    await expect(
        page.getByText('A speed value is outside what this scenario allows: turn 7 must be between 0 and 1300.')
    ).toBeVisible();

    // Still step one: a rejected file never reaches the confirm step.
    await expect(page.getByRole('heading', { name: 'Confirm the import', level: 2 })).toHaveCount(0);
});

test('refuses a file the app never wrote, in the words that say what to fix', async ({ page }) => {
    await page.goto('/training-runs/import');
    await page.locator('#app > *').first().waitFor();

    await fillForm(page, 'turn,speed\n1,600');

    await expect(
        page.getByText(/That file has no usable turn rows\. The first line must be the header:/)
    ).toBeVisible();
});
