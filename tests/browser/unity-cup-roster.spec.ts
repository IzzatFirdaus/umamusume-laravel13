import { test, expect, type Page } from '@playwright/test';
import { deleteRun } from '../utils/delete-run';
import { openTeamEdit } from '../utils/scenario-detail';
import { startCareer } from '../utils/start-career';

/*
 * D01 / UC2.4 / UC3: the Unity Cup roster, end to end.
 *
 * The roster is a table on the team workspace (`TeamRoster.vue` behind
 * `CockpitController::rosterSectionWithUrls()`) and posts the `runs.roster.*` routes
 * (`RosterMemberController`). This case walks a fresh run through the lifecycle the surface
 * supports — empty state, add by name only, add with optional fields, edit through the contextual
 * editor, remove through the same editor — and asserts the persisted state on each step. The backend
 * has its own coverage (`RosterMemberTest`, `RosterDoesNotTouchTeamRankTest`); this spec's job is to
 * prove the wired UI submits the shapes the controllers accept.
 *
 * **The editor is a dialog, not an inline form.** It used to replace the row in place, which pushed
 * every row below it down the page. It is now a native `<dialog>` opened with `showModal()`, so this
 * case asserts the focus lands inside it and that Escape/Close returns to the table — the two
 * properties the platform change was made for.
 *
 * **Fixture strategy.** The run is started through the wizard's own `startCareer`, the same way
 * `team-edit.spec.ts` does; the spec owns its teardown so the run is deleted through `runs.destroy`
 * rather than a UI walk. The database is the harness's scratch DB (KI-69), so a run created here is
 * the only one that exists.
 */
const createdRunUrls: string[] = [];

test.afterEach(async ({ page }) => {
    for (const url of createdRunUrls.splice(0)) {
        await deleteRun(page, url);
    }
});

const WRITE = { timeout: 60_000 } as const;

/** The roster region, named by its own heading so a second `table` elsewhere cannot match it. */
const rosterRegion = (page: Page) => page.getByRole('region', { name: 'Recorded roster' });

/**
 * One recorded row, matched by its own text. `getByText(name)` is not usable here: every row carries
 * the name twice, once in the row header and once in the `sr-only` label of its `Edit` button, so the
 * plain text locator is ambiguous by design (the button has to name its subject for screen readers).
 * Matching the `tr` keeps the assertion on the row rather than on one of its two copies.
 */
const rowNamed = (roster: ReturnType<typeof rosterRegion>, name: string) =>
    roster.locator('tbody tr', { hasText: name });

test('roster walks the add-edit-remove lifecycle on a fresh run', async ({ page }) => {
    createdRunUrls.push(await startCareer(page, 'unity_cup'));

    await openTeamEdit(page);
    await page.locator('#app > *').first().waitFor();

    const roster = rosterRegion(page);

    // Empty state: a fresh run has no rows, so the panel says so rather than fabricating a count.
    await expect(roster.getByText(/No team roster has been recorded/)).toBeVisible();
    await expect(roster.getByText('0 of 20 slots recorded', { exact: false })).toBeVisible();

    // Add by name only. The form is `useForm` posting `runs.roster.store`; the success path resets
    // the form and the Inertia redirect lands the page back on itself with the new row rendered.
    await roster.getByRole('button', { name: 'Add member' }).click();
    await roster.locator('#roster_new_name').fill('Haru Urara');
    await roster.locator('form').getByRole('button', { name: 'Add member' }).click();
    await expect(roster.getByText('1 of 20 slots recorded', { exact: false })).toBeVisible(WRITE);

    // The new row is a table row, and an unrecorded stat renders a dash rather than a zero: a member
    // added by name has no reading yet, and `0` would be a claim about a record that does not exist.
    const row = roster.locator('tbody tr').first();
    await expect(row).toContainText('Haru Urara');
    await expect(row).toContainText('(name only)');
    await expect(row.locator('td.n').first()).toHaveText('—');

    // No category, and the row says exactly that (`UC3.5`). "Unassigned" would be a stronger claim
    // than the column can carry: one nullable field holds both "she stands outside every category"
    // and "the column was never transcribed", and nothing in the payload distinguishes the two.
    await expect(row).toContainText('No category recorded');

    // Edit through the contextual editor: a dialog over the row, not a form inside it.
    await row.getByRole('button', { name: /^Edit/ }).click();
    const dialog = page.getByRole('dialog', { name: /Edit Haru Urara/ });
    await expect(dialog).toBeVisible();
    // The dialog is modal, so the roster behind it is inert and the focus starts on the name field.
    await expect(dialog.locator('#roster_edit_name')).toBeFocused();

    await dialog.locator('#roster_edit_name').fill('Haru Urara');
    await dialog.locator('#roster_edit_grade').selectOption('A');
    await dialog.locator('#roster_edit_distance').selectOption('Dirt');
    await dialog.locator('#roster_edit_speed').fill('733');
    await dialog.locator('#roster_edit_stamina').fill('572');
    await dialog.getByRole('button', { name: 'Save member' }).click();

    // The save closes the dialog and the table re-reads the saved values.
    await expect(dialog).toBeHidden(WRITE);
    const savedRow = roster.locator('tbody tr').first();
    await expect(savedRow).toContainText('Haru Urara');
    // Grade 'A' and distance 'Dirt' are displayed as pills in the new column layout;
    // verify they appear in the row's content rather than at previous pill positions.
    await expect(savedRow).toContainText('A');
    await expect(savedRow).toContainText('Dirt');
    await expect(savedRow.locator('td.n').first()).toHaveText('733');

    // The `(name only)` marker survives the save, and it must: it describes the catalogue link, not the
    // stat readings. Editing a typed name does not identify a character, and a row that stopped saying
    // "name only" once it had numbers would claim an identity the run never recorded.
    await expect(savedRow).toContainText('(name only)');

    // Remove is inside the editor now, not on every row, and the confirmation is unchanged.
    // Playwright dismisses dialogs by default, so accept it to drive `runs.roster.destroy`.
    page.on('dialog', (confirmation) => confirmation.accept());
    await savedRow.getByRole('button', { name: /^Edit/ }).click();
    await page.getByRole('dialog', { name: /Edit Haru Urara/ }).getByRole('button', { name: 'Remove from roster' }).click();

    await expect(roster.getByText('0 of 20 slots recorded', { exact: false })).toBeVisible(WRITE);
    await expect(roster.getByText(/No team roster has been recorded/)).toBeVisible();
    await expect(roster.getByText('Haru Urara')).toHaveCount(0);
});

test('a member\'s recorded ceilings survive a save and are printed beside her stats', async ({ page }) => {
    createdRunUrls.push(await startCareer(page, 'unity_cup'));
    await openTeamEdit(page);
    await page.locator('#app > *').first().waitFor();
    const roster = rosterRegion(page);

    // A stat with no ceiling is the interesting half: the reading prints, and no divider follows it.
    // `1200 /1600` and a bare `1200` are different records, and a formatter that always appended the
    // slash would make the second impossible to express.
    await roster.getByRole('button', { name: 'Add member' }).click();
    await roster.locator('#roster_new_name').fill('Haru Urara');
    await roster.locator('#roster_new_distance').selectOption('Dirt');
    await roster.locator('#roster_new_speed').fill('1200');
    await roster.locator('#roster_new_speed_cap').fill('1600');
    await roster.locator('#roster_new_stamina').fill('640');
    await roster.locator('form').getByRole('button', { name: 'Add member' }).click();
    await expect(rowNamed(roster, 'Haru Urara')).toBeVisible(WRITE);

    const row = roster.locator('tbody tr').first();
    const cells = row.locator('td.n');

    // Speed: reading and ceiling, together.
    await expect(cells.nth(0)).toHaveText('1200/1600');
    // Stamina: the reading alone, because the run recorded no ceiling for it. The dash would be a
    // claim, and the absent ceiling is a fact about the record rather than about the member.
    await expect(cells.nth(1)).toHaveText('640');

    // Editing writes the ceiling through `runs.roster.update`, the same request the stat uses.
    await row.getByRole('button', { name: /^Edit/ }).click();
    const dialog = page.getByRole('dialog', { name: /Edit Haru Urara/ });
    await expect(dialog.locator('#roster_edit_speed_cap')).toHaveValue('1600');
    await expect(dialog.locator('#roster_edit_stamina_cap')).toHaveValue('');
    await dialog.locator('#roster_edit_stamina_cap').fill('1100');
    await dialog.getByRole('button', { name: 'Save member' }).click();
    await expect(dialog).toBeHidden(WRITE);

    await expect(roster.locator('tbody tr').first().locator('td.n').nth(1)).toHaveText('640/1100');

    // And the pair survives a reload, which is what separates a saved ceiling from one the dialog
    // merely remembered.
    await page.reload({ waitUntil: 'domcontentloaded' });
    await page.locator('#app > *').first().waitFor();
    await expect(rosterRegion(page).locator('tbody tr').first().locator('td.n').nth(0)).toHaveText('1200/1600');
});

test('closing the member editor without saving changes nothing', async ({ page }) => {
    createdRunUrls.push(await startCareer(page, 'unity_cup'));
    await openTeamEdit(page);
    await page.locator('#app > *').first().waitFor();
    const roster = rosterRegion(page);

    await roster.getByRole('button', { name: 'Add member' }).click();
    await roster.locator('#roster_new_name').fill('Tokai Teio');
    await roster.locator('form').getByRole('button', { name: 'Add member' }).click();
    await expect(roster.getByText('1 of 20 slots recorded', { exact: false })).toBeVisible(WRITE);

    await roster.locator('tbody tr').first().getByRole('button', { name: /^Edit/ }).click();
    const dialog = page.getByRole('dialog', { name: /Edit Tokai Teio/ });
    await dialog.locator('#roster_edit_name').fill('Renamed In Error');

    // Escape is the platform's own close path for a modal dialog; the unsaved name is discarded
    // because nothing was submitted.
    await page.keyboard.press('Escape');
    await expect(dialog).toBeHidden();

    await page.reload({ waitUntil: 'domcontentloaded' });
    await page.locator('#app > *').first().waitFor();

    await expect(rowNamed(rosterRegion(page), 'Tokai Teio')).toBeVisible();
    await expect(rosterRegion(page).getByText('Renamed In Error')).toHaveCount(0);
});

test('roster refuses a second ACE in one category', async ({ page }) => {
    createdRunUrls.push(await startCareer(page, 'unity_cup'));

    await openTeamEdit(page);
    await page.locator('#app > *').first().waitFor();
    const roster = rosterRegion(page);

    // Two ACEs in the same category is the model's own guard (`RosterMember::booted()`); the
    // controller translates it to a validation error beside `distance_assignment`, which the add
    // form prints.
    for (const name of ['First ACE', 'Second ACE'] as const) {
        await roster.getByRole('button', { name: 'Add member' }).click();
        await roster.locator('#roster_new_name').fill(name);
        await roster.locator('#roster_new_distance').selectOption('Long');
        await roster.locator('#roster_new_ace').check();
        await roster.locator('form').getByRole('button', { name: 'Add member' }).click();

        if (name === 'First ACE') {
            await expect(roster.getByText('1 of 20 slots recorded', { exact: false })).toBeVisible(WRITE);
        }
    }

    await expect(roster.getByText(/already has its ACE/i)).toBeVisible(WRITE);
    await expect(roster.getByText('1 of 20 slots recorded', { exact: false })).toBeVisible();
});

test('roster searches and filters over the recorded rows', async ({ page }) => {
    createdRunUrls.push(await startCareer(page, 'unity_cup'));
    await openTeamEdit(page);
    await page.locator('#app > *').first().waitFor();
    const roster = rosterRegion(page);

    // Two members in different categories, so the category filter has something to separate.
    for (const [name, category] of [['Haru Urara', 'Dirt'], ['Tokai Teio', 'Medium']] as const) {
        await roster.getByRole('button', { name: 'Add member' }).click();
        await roster.locator('#roster_new_name').fill(name);
        await roster.locator('#roster_new_distance').selectOption(category);
        await roster.locator('form').getByRole('button', { name: 'Add member' }).click();
        await expect(rowNamed(roster, name)).toBeVisible(WRITE);
    }

    await expect(roster.locator('tbody tr')).toHaveCount(2);

    // Search by name.
    await roster.locator('#roster_search').fill('urara');
    await expect(roster.locator('tbody tr')).toHaveCount(1);
    await expect(roster.locator('tbody tr').first()).toContainText('Haru Urara');

    await roster.getByRole('button', { name: 'Clear' }).click();
    await expect(roster.locator('tbody tr')).toHaveCount(2);

    // Filter by category. `Dirt` is a team column here even though it is not a distance band.
    await roster.locator('#roster_filter_category').selectOption('Dirt');
    await expect(roster.locator('tbody tr')).toHaveCount(1);
    await expect(roster.locator('tbody tr').first()).toContainText('Haru Urara');

    // A query that matches nothing says so rather than rendering an empty table with no explanation.
    await roster.getByRole('button', { name: 'Clear' }).click();
    await roster.locator('#roster_search').fill('nobody by this name');
    await expect(roster.getByText('No recorded member matches these filters.')).toBeVisible();
});

test('roster sorts by grade and keeps the ACE flag on the row', async ({ page }) => {
    createdRunUrls.push(await startCareer(page, 'unity_cup'));
    await openTeamEdit(page);
    await page.locator('#app > *').first().waitFor();
    const roster = rosterRegion(page);

    for (const [name, grade, ace] of [['Low Grade', 'C', false], ['High Grade', 'S', true]] as const) {
        await roster.getByRole('button', { name: 'Add member' }).click();
        await roster.locator('#roster_new_name').fill(name);
        await roster.locator('#roster_new_distance').selectOption('Sprint');
        await roster.locator('#roster_new_grade').selectOption(grade);
        if (ace) {
            await roster.locator('#roster_new_ace').check();
        }
        await roster.locator('form').getByRole('button', { name: 'Add member' }).click();
        await expect(rowNamed(roster, name)).toBeVisible(WRITE);
    }

    // The ACE designation is on the row, not inferred from the grade.
    const aceRow = roster.locator('tbody tr', { hasText: 'High Grade' });
    await expect(aceRow.locator('.pill-accent')).toHaveText('ACE');

    // The sort control, matched exactly: the row buttons are named `Edit <member>`, so a substring
    // match on `Grade` also resolves the two `Edit Low Grade` / `Edit High Grade` buttons.
    const gradeHeader = roster.getByRole('button', { name: 'Grade', exact: true });

    // Ascending grade order puts G..S in the client's own ladder order, so C precedes S.
    await gradeHeader.click();
    await expect(roster.locator('tbody tr').first()).toContainText('Low Grade');

    // A second activation reverses it. The header carries `aria-sort`, so the direction is announced
    // and not carried by the glyph alone.
    await gradeHeader.click();
    await expect(roster.locator('tbody tr').first()).toContainText('High Grade');
    await expect(roster.getByRole('columnheader', { name: /Grade/ })).toHaveAttribute('aria-sort', 'descending');
});

test('the formation board places the recorded ACEs in their categories', async ({ page }) => {
    createdRunUrls.push(await startCareer(page, 'unity_cup'));
    await openTeamEdit(page);
    await page.locator('#app > *').first().waitFor();

    // The board draws the five published categories even before any member is recorded, and each
    // column states which of the two absences applies rather than rendering a blank slot.
    const board = page.getByRole('region', { name: 'Team formation' });
    for (const category of ['Sprint', 'Mile', 'Medium', 'Long', 'Dirt']) {
        await expect(board.getByRole('heading', { name: category, exact: false }).first()).toBeVisible();
    }
    await expect(board.getByText('No member recorded in this category.').first()).toBeVisible();
    await expect(board.getByText(/No team roster has been recorded on this run yet/)).toBeVisible();

    // Record the Dirt ACE and the board reads it back in the Dirt column.
    const roster = rosterRegion(page);
    await roster.getByRole('button', { name: 'Add member' }).click();
    await roster.locator('#roster_new_name').fill('Haru Urara');
    await roster.locator('#roster_new_distance').selectOption('Dirt');
    await roster.locator('#roster_new_ace').check();
    await roster.locator('form').getByRole('button', { name: 'Add member' }).click();
    await expect(rowNamed(roster, 'Haru Urara')).toBeVisible(WRITE);

    const dirtColumn = board.locator('div.rounded-md', { has: page.getByRole('heading', { name: /^Dirt/ }) }).first();
    await expect(dirtColumn).toContainText('Haru Urara');
    await expect(dirtColumn.locator('.pill-accent')).toHaveText('ACE');

    // The other four columns still report no member, and specifically not an empty one: the board
    // never claims a category the run recorded nothing for is empty in the game.
    await expect(board.getByText('No member recorded in this category.')).toHaveCount(4);

    // `UC3.5`'s three states are three sentences, not one blank. A category that holds a member with
    // no ACE crowned is a different fact from a category with nobody in it, and the board has to say
    // which it is holding. Record one more member in Long without the ACE flag.
    await roster.getByRole('button', { name: 'Add member' }).click();
    await roster.locator('#roster_new_name').fill('Rice Shower');
    await roster.locator('#roster_new_distance').selectOption('Long');
    await roster.locator('form').getByRole('button', { name: 'Add member' }).click();
    await expect(rowNamed(roster, 'Rice Shower')).toBeVisible(WRITE);

    const longColumn = board.locator('div.rounded-md', { has: page.getByRole('heading', { name: /^Long/ }) }).first();
    await expect(longColumn).toContainText('Rice Shower');
    await expect(longColumn).toContainText('No ACE crowned in this category.');
    // Three empty categories remain, so the two sentences are genuinely distinct on the same board.
    await expect(board.getByText('No member recorded in this category.')).toHaveCount(3);
    await expect(board.getByText('No ACE crowned in this category.')).toHaveCount(1);
});
