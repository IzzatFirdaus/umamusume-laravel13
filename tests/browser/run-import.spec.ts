import { test, expect } from '@playwright/test';

// Rendered-copy and behaviour evidence for the ported import screens (SCR-RUN-004/005, ADR-0020 §1).
// `HistoricalRunImportTest` and `ImportErrorCopyTest` assert the parse, the round trip and the MessageBag;
// these assert what the browser shows.
//
// `ImportErrorCopyTest` closed its own header with the sentence the form prints in front of the
// per-stat messages ("A speed value is outside what this scenario allows:") and said the composite was
// "not verified here: it needs a browser pass on the landed view". That pass is the ceiling test below.
//
// Nothing here used to complete a commit — the confirm step was reached and read, never posted — and
// that gap was KI-64's silent half: the flow could not complete in a browser at all. The commit is
// now exercised end to end by the last case below: preview a two-row sheet, press confirm, and
// assert the redirect lands on the written run. That case writes a run, so it belongs on the
// scratch-database harness (plan §4.1 item 7: `PLAYWRIGHT_BASE_URL` pointing at a private-port
// server over a scratch copy, never the shared dev file). Pest still proves the write against a
// per-test database; this proves the browser flow.
//
// Fixtures are the app's own seeded scratch database. `unity_cup` sets a speed ceiling of 1300, so a
// row at 1400 is a ceiling break the scenario owns rather than a number invented for the test.

const HEADERS = 'turn,speed,stamina,power,guts,wit,sp,condition,energy,mood,fans';

function csv(rows: string[]): string {
    return [HEADERS, ...rows].join('\n');
}

const okRow = (turn: string): string => `${turn},600,400,300,250,200,40,,70,GOOD,1200`;

async function fillForm(page: import('@playwright/test').Page, body: string): Promise<string> {
    const trainee = page.locator('select[name="umamusume_id"]');
    const option = trainee.locator('option').nth(1);
    const traineeName = ((await option.textContent()) ?? '').split('·')[0]?.trim() ?? '';
    await trainee.selectOption((await option.getAttribute('value')) ?? '');

    await page.locator('select[name="scenario"]').selectOption('unity_cup');
    await page.locator('textarea[name="csv"]').fill(body);
    await page.getByRole('button', { name: 'Preview import' }).click();

    return traineeName;
}

test('states the column list the export writes, so the paste target is checkable before typing', async ({ page }) => {
    await page.goto('/training-runs/import');
    await page.locator('#app > *').first().waitFor();

    // The HFW import page's own heading, which the app ships verbatim; the earlier pin used a
    // draft title the wireframe never carried (HFW precedence over the v2.1.0 test).
    await expect(page.getByRole('heading', { name: 'Bring in a historical career', level: 1 })).toBeVisible();
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

    for (const selector of ['select[name="umamusume_id"]', 'select[name="scenario"]', 'select[name="status"]']) {
        const box = await page.locator(selector).boundingBox();
        expect(box?.height ?? 0, `${selector} is not sized to the 44px contract`).toBeGreaterThanOrEqual(44);
    }

    // The upload control is behind the Upload door, so it is measured there.
    await page.getByRole('button', { name: 'Upload' }).click();
    const file = await page.locator('input[name="file"]').boundingBox();
    expect(file?.height ?? 0, 'input[name="file"] is not sized to the 44px contract').toBeGreaterThanOrEqual(44);
    await page.getByRole('button', { name: 'Paste' }).click();

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

    // The summary names the trainee, the scenario label rather than its slug, and the count. Anchored on
    // the separator because the confirm button's own label ends the same way, and `getByText` matches
    // substrings: `/2 turns$/` alone resolves to both and fails in strict mode.
    await expect(region.locator('caption')).toHaveText('Every turn row that will be written');
    await expect(page.getByText(/· 2 turns$/)).toBeVisible();
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

test('commits the previewed file: confirm writes the run and the page leaves the import URL', async ({ page }) => {
    await page.goto('/training-runs/import');
    await page.locator('#app > *').first().waitFor();

    const traineeName = await fillForm(page, csv([okRow('1'), okRow('2')]));
    await expect(page.getByRole('heading', { name: 'Confirm the import', level: 2 })).toBeVisible();

    // The preview prints the provenance string the run will carry, which is the one moment a Trainer can
    // read it before anything is written. `HistoricalRunImportTest` pins what the row stores; the Cockpit
    // the commit lands on prints no provenance line at all, and KI-95 records that gap rather than this
    // step pretending to read it back.
    await expect(page.getByText(/from pasted CSV \(/)).toBeVisible();

    await page.getByRole('button', { name: 'Import 2 turns' }).click();

    // The commit redirects to the run's own page (`runs.show`, which is itself a redirect to the
    // Cockpit), and the URL leaving the import path is exactly the step KI-64 recorded as never
    // reached: the POST used to omit the file body, get refused, and leave the page (and its visitor)
    // sitting on the same URL with nothing said.
    await page.waitForURL((url) => /\/training-runs\/(\d+)(\/cockpit)?$/.test(url.pathname));
    await expect(page.getByRole('heading', { level: 1 })).toContainText(traineeName);
});

// Slice 3 step 1.3's two additions. The import page's own annotation 3 makes the textarea the primary
// control, above the upload path, and import-preview annotation 2 makes every field state itself as
// found or missing rather than defaulting silently.
test('offers Paste and Upload as one control each, with the paste box as the primary one', async ({ page }) => {
    await page.goto('/training-runs/import');
    await page.locator('#app > *').first().waitFor();

    const group = page.getByRole('group', { name: 'Where the text comes from' });
    await expect(group.getByRole('button', { name: 'Paste' })).toHaveAttribute('aria-pressed', 'true');
    await expect(group.getByRole('button', { name: 'Upload' })).toHaveAttribute('aria-pressed', 'false');

    const textarea = page.locator('textarea[name="csv"]');
    await expect(textarea).toBeVisible();
    await expect(textarea).toHaveAttribute('rows', /^(8|9|10)$/);
    await expect(page.locator('input[name="file"]')).toHaveCount(0);

    await group.getByRole('button', { name: 'Upload' }).click();
    await expect(group.getByRole('button', { name: 'Upload' })).toHaveAttribute('aria-pressed', 'true');
    await expect(page.locator('input[name="file"]')).toBeVisible();
    await expect(textarea).toHaveCount(0);
});

test('tags every previewed field found or missing, and names what to do about a miss', async ({ page }) => {
    await page.goto('/training-runs/import');
    await page.locator('#app > *').first().waitFor();

    // The form's own status select names the accepted set, which is the set the preview's miss sentence
    // quotes. `Active, Completed or Retired` is the vocabulary, not a bare "required".
    const statusOptions = await page.locator('select[name="status"] option').allTextContents();
    expect(statusOptions.map((label) => label.trim()).sort()).toEqual(['Active', 'Completed', 'Retired']);

    await fillForm(page, csv([okRow('1')]));

    // A file the app wrote carries the trainee, the scenario and the status; it carries no notes, so the
    // notes field is the one honest miss on a well-formed sheet.
    const notesLabel = page.locator('#preview-notes-label');
    await expect(notesLabel.getByText('missing', { exact: true })).toBeVisible();
    await expect(page.getByText(/Not in the file\. Notes are optional/)).toBeVisible();

    for (const field of ['umamusume_id', 'scenario', 'status']) {
        await expect(
            page.locator(`#preview-${field}-label`).getByText('found', { exact: true }),
            `${field} is not tagged found`,
        ).toBeVisible();
    }
});


/*
 * The Phase 8 capabilities the wireframe asks for on top of the original two-step flow: the column
 * mapping (import.html "Map the columns"), the row-size selector over the preview, and the
 * fill/drop pair. The mapping's refusals live in `HistoricalRunImportTest`; these are the halves
 * only a browser reaches.
 */
test('maps a sheet whose columns are in another order, and previews the right values', async ({ page }) => {
    await page.goto('/training-runs/import');
    await page.locator('#app > *').first().waitFor();

    const foreign = ['speed', 'turn', 'stamina', 'power', 'guts', 'wit', 'sp', 'condition', 'energy', 'mood', 'fans'];
    await page.locator('textarea[name="csv"]').fill([foreign.join(','), '640,7,400,300,250,200,40,,70,GOOD,1200'].join('\n'));

    const mapping = page.getByRole('heading', { name: 'Map the columns', level: 3 });
    await expect(mapping).toBeVisible();

    const selects = page.locator('select[name^="mapping["]');
    await expect(selects).toHaveCount(foreign.length);

    // The names the file shares with the importer are pre-selected; the two moved columns are set by
    // the Trainer, which is what the selectors exist for.
    await page.locator('select[name="mapping[0]"]').selectOption('speed');
    await page.locator('select[name="mapping[1]"]').selectOption('turn');

    await page.locator('select[name="umamusume_id"]').selectOption({ index: 1 });
    await page.locator('select[name="status"]').selectOption('Completed');
    await page.getByRole('button', { name: 'Preview import' }).click();

    await expect(page.getByRole('heading', { name: 'Confirm the import', level: 2 })).toBeVisible();
    const region = page.getByRole('region', { name: 'Rows to import' });
    const row = region.locator('tbody tr').first();
    await expect(row.locator('td').nth(0)).toHaveText('7');
    await expect(row.locator('td').nth(1)).toHaveText('640');
});

test('draws only a page of parsed rows without changing the count the import writes', async ({ page }) => {
    await page.goto('/training-runs/import');
    await page.locator('#app > *').first().waitFor();

    const rows = Array.from({ length: 30 }, (_, index) => okRow(String(index + 1)));
    await fillForm(page, csv(rows));

    const region = page.getByRole('region', { name: 'Rows to import' });
    await expect(region.locator('tbody tr')).toHaveCount(25);
    await expect(page.getByText('Showing 25 of 30 turn rows; the import writes all of them.')).toBeVisible();
    await expect(page.getByRole('button', { name: 'Import 30 turns' })).toBeVisible();

    await page.locator('select[name="row_limit"]').selectOption('10');
    await expect(region.locator('tbody tr')).toHaveCount(10);

    await page.locator('select[name="row_limit"]').selectOption('all');
    await expect(region.locator('tbody tr')).toHaveCount(30);
});

test('fills to the first required miss and drops only the optional fields', async ({ page }) => {
    await page.goto('/training-runs/import');
    await page.locator('#app > *').first().waitFor();

    await fillForm(page, csv([okRow('1')]));

    // Make the trainee the first required miss, read the verdict back so the clear has rendered, then
    // ask the page to fill it.
    await page.locator('#preview-umamusume_id').selectOption('');
    await expect(page.locator('#preview-umamusume_id-label')).toContainText('missing');
    await page.getByRole('button', { name: 'Fill missing' }).click();
    await expect(page.locator('#preview-umamusume_id')).toBeFocused();

    // Drop clears the two optional fields and leaves the required pair alone.
    await page.getByRole('button', { name: 'Drop missing' }).click();
    await expect(page.locator('select[name="scenario"]')).toHaveValue('');
    await expect(page.locator('textarea[name="notes"]')).toHaveValue('');
    await expect(page.getByText('Trainee and Status are required.')).toBeVisible();
});
