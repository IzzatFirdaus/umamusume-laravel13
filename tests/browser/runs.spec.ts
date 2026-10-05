import { test, expect } from '@playwright/test';

// Rendered-copy and behaviour evidence for the ported run screens (SCR-RUN-001/002, ADR-0020 §1).
// The server-side tests (RunsIndexTest, TraineeSelectorTest, RunCreateSurfaceTest) assert the resolved
// props and the POST round trip; these assert what the browser shows and what the combobox does.
//
// This file also carries the coverage that retired with the Blade create page's two halves: the
// no-script fallback <select> (a client-rendered page has no no-script path) and eleven shape pins
// that read `trainee-combobox.ts` source text because no JS runner existed. Those behaviours are
// proven here by running them, which the shape pins could only describe. Owner ruling 2026-10-05.
//
// Fixtures are the app's own seeded scratch database (playwright.config.ts): 67 Global trainees and
// 106 confirmed costume cards. Agnes Digital (id 19) carries two, `[Full-Color Fangirling]` her debut
// form of 2025-11-19 and `[Fanatic♡Jiangshi]` of 2026-08-18, and her Japanese name is
// アグネスデジタル. The eleventh-newest card in the roster is `[Rocket☆Star]`, one row past the
// popup's ten-row cap.
//
// Not covered here, and why:
// - The populated run row and the pagination control. `TrainingRun` holds zero rows in the seeded
//   database, so `/training-runs` can only reach its empty state, and writing runs from a browser
//   test would mutate the development database the suite shares. What covers them instead:
//   `RunsIndexTest` pins the row props and the 25-item boundary, and DesignTokensTest sweeps the
//   pager's classes for the token gate.
// - The cardless band ("No confirmed costume card yet"). Every seeded Global trainee has a confirmed
//   card, so no default list on this database can hold one. `TraineeSelectorTest` pins the payload
//   shape that produces it and the POST that a cardless pick writes.
// - A title that is hostile markup. No seeded title contains one; the props test pins that the
//   payload carries the stored string unchanged, and Vue renders it as text.

const combobox = (page: import('@playwright/test').Page) => page.locator('#trainee-combobox');
const listbox = (page: import('@playwright/test').Page) => page.locator('#trainee-listbox');
const options = (page: import('@playwright/test').Page) =>
    listbox(page).locator('li[role="option"]');
const status = (page: import('@playwright/test').Page) =>
    listbox(page).locator('xpath=following-sibling::p[@aria-live="polite"]');

async function openList(page: import('@playwright/test').Page): Promise<void> {
    await combobox(page).focus();
}

test('renders the run list, and says what happens next when it is empty', async ({ page }) => {
    await page.goto('/training-runs');
    await page.locator('#app > *').first().waitFor();

    await expect(page.getByRole('heading', { name: 'Training runs', level: 1 })).toBeVisible();

    // An empty list is a real state, not a failure, so it names what happens next rather than
    // reading as "no data".
    await expect(page.getByText('No runs yet')).toBeVisible();
    await expect(page.getByText(/nothing to list until you start one/)).toBeVisible();

    // The empty state still offers both ways in. Import sits beside New run because it creates a run
    // too, and a Trainer with a finished career in a file has to find it on the same glance.
    await expect(page.getByRole('link', { name: 'New run' })).toBeVisible();
    await expect(page.getByRole('link', { name: 'Import a historical run' })).toBeVisible();
});

test('sizes the create form controls to the 44px contract', async ({ page }) => {
    await page.goto('/training-runs/create');
    await page.locator('#app > *').first().waitFor();

    // KI-29 / DESIGN.md §6.14: every control a Trainer lands on is 44px.
    const selectors = ['#trainee-combobox', 'select[name="scenario"]', 'select[name="status"]',
        'select[name="inheritance_parent_a_id"]', 'select[name="inheritance_parent_b_id"]',
        'input[name="current_objective_index"]', 'input[name="shop_resets_in"]'];

    for (const selector of selectors) {
        const box = await page.locator(selector).boundingBox();
        expect(box?.height ?? 0, `${selector} is not sized to the 44px contract`).toBeGreaterThanOrEqual(44);
    }

    const submit = await page.getByRole('button', { name: 'Create run' }).boundingBox();
    expect(submit?.height ?? 0, 'the Create run button is not sized to the 44px contract').toBeGreaterThanOrEqual(44);
});

test('advertises the combobox ARIA surface, names the field the way its caption reads, and stays quiet until asked', async ({ page }) => {
    await page.goto('/training-runs/create');
    await page.locator('#app > *').first().waitFor();

    const field = combobox(page);
    await expect(field).toHaveAttribute('role', 'combobox');
    await expect(field).toHaveAttribute('aria-expanded', 'false');
    await expect(field).toHaveAttribute('aria-autocomplete', 'list');
    await expect(field).toHaveAttribute('aria-controls', 'trainee-listbox');
    // aria-required, not required: the value that submits is the committed pair, so `required` here
    // would gate on the label text a Trainer typed, which is a different string from the one posted.
    await expect(field).toHaveAttribute('aria-required', 'true');
    // No cursor before the Trainer opens anything: a closed listbox owns none.
    await expect(field).toHaveAttribute('aria-activedescendant', '');

    await expect(listbox(page)).toHaveAttribute('role', 'listbox');

    // WCAG 2.5.3: a speech-input user says the words on screen, so the visible caption has to be a
    // prefix of the accessible name.
    await expect(page.locator('label[for="trainee-combobox"]')).toContainText('Trainee *');
    await expect(field).toHaveAccessibleName('Trainee or costume card name');

    // A polite live region is announced when it changes, so a page load that stated a count was
    // reporting a list nobody asked for.
    await expect(status(page)).toHaveText('');

    // The popup is display:none until the Trainer opens it, and an accessible name computed on a
    // hidden element is empty, so the listbox's own name is read in the state it is actually met.
    await openList(page);
    await expect(listbox(page)).toHaveAccessibleName('Trainees and costume cards');
});

test('opens on focus with no cursor, so the Enter that means submit is not a re-pick', async ({ page }) => {
    await page.goto('/training-runs/create');
    await page.locator('#app > *').first().waitFor();

    await openList(page);

    await expect(combobox(page)).toHaveAttribute('aria-expanded', 'true');
    await expect(combobox(page)).toHaveAttribute('aria-activedescendant', '');
    await expect(options(page).filter({ has: page.locator('[aria-selected="true"]') })).toHaveCount(0);
    await expect(listbox(page).locator('[aria-selected="true"]')).toHaveCount(0);
});

test('caps the default list at ten rows and says it is showing a window, not the roster', async ({ page }) => {
    await page.goto('/training-runs/create');
    await page.locator('#app > *').first().waitFor();

    await openList(page);

    await expect(options(page)).toHaveCount(10);
    await expect(status(page)).toHaveText(/^10 of \d+ \(keep typing\)$/);

    // The eleventh-newest card in the whole roster is off the popup until the Trainer narrows it,
    // which is what makes "(keep typing)" the true statement rather than a count of what exists.
    await expect(options(page).filter({ hasText: 'Rocket☆Star' })).toHaveCount(0);

    // No band seam: on this database every Global trainee has a confirmed card, so a divider naming
    // "these" would have no other side to separate.
    await expect(listbox(page).locator('[data-band-divider]')).toHaveCount(0);
});

test('filters on the trainee name, the Japanese name, and the bracket-stripped epithet', async ({ page }) => {
    await page.goto('/training-runs/create');
    await page.locator('#app > *').first().waitFor();

    // A trainee whose own name matches shows all her forms: "which Agnes Digital card" is the
    // question, not "which card has Agnes Digital in its title".
    await combobox(page).fill('Agnes Digital');
    await expect(options(page)).toHaveCount(2);
    await expect(status(page)).toHaveText('2 matches');
    await expect(options(page).filter({ hasText: '[Full-Color Fangirling]' })).toHaveCount(1);
    await expect(options(page).filter({ hasText: '[Fanatic♡Jiangshi]' })).toHaveCount(1);

    // The Japanese name is a match field in its own right.
    await combobox(page).fill('アグネスデジタル');
    await expect(options(page)).toHaveCount(2);

    // The epithet matches on the bracket-stripped key, so a first-letter query reaches a title whose
    // verbatim string opens with "[". The label still prints the brackets.
    await combobox(page).fill('FULL');
    await expect(options(page)).toHaveCount(1);
    await expect(options(page).first()).toContainText('[Full-Color Fangirling]');
    await expect(status(page)).toHaveText('1 match');
});

test('names the miss in words a Trainer reads, rather than showing an empty box', async ({ page }) => {
    await page.goto('/training-runs/create');
    await page.locator('#app > *').first().waitFor();

    await combobox(page).fill('nothingresemblingatrainee');

    await expect(options(page)).toHaveCount(0);
    await expect(status(page)).toHaveText('No trainee or card found.');
});

test('puts the trainee in each option\'s own accessible name and keeps the headers out of the tree', async ({ page }) => {
    await page.goto('/training-runs/create');
    await page.locator('#app > *').first().waitFor();

    await combobox(page).fill('Agnes Digital');

    const labels = await options(page).evaluateAll((els) =>
        els.map((el) => el.getAttribute('aria-label') ?? ''),
    );

    expect(labels).toHaveLength(2);

    // The per-trainee header is presentation: a group that owns no option names none, and a heading
    // inside the option list would be a row a Trainer could land on. So the name goes into each
    // option, where keyboard navigation always picks it up.
    for (const label of labels) {
        expect(label.startsWith('Agnes Digital · ')).toBe(true);
        // An accessible name is copy a screen reader speaks, and R-02 / D-79 keep a dash out of it.
        expect(label).not.toMatch(/[–—]/);
    }

    const headers = listbox(page).locator('li[role="presentation"]');
    await expect(headers).toHaveCount(1);
    await expect(headers.first()).toHaveAttribute('aria-hidden', 'true');
    await expect(headers.first()).toContainText('Agnes Digital アグネスデジタル');
});

test('moves the cursor with the arrows, opens onto the ends, and keeps Escape a close', async ({ page }) => {
    await page.goto('/training-runs/create');
    await page.locator('#app > *').first().waitFor();

    // Opening by arrow lands on an end, not one in from it: a shared increment from "no cursor" would
    // put ArrowUp on the second-to-last row and skip the row the reopen exists to reach.
    await combobox(page).focus();
    await page.keyboard.press('Escape');
    await expect(combobox(page)).toHaveAttribute('aria-expanded', 'false');
    await expect(combobox(page)).toHaveAttribute('aria-activedescendant', '');

    await page.keyboard.press('ArrowUp');
    await expect(combobox(page)).toHaveAttribute('aria-expanded', 'true');
    const last = options(page).last();
    await expect(last).toHaveAttribute('aria-selected', 'true');
    await expect(combobox(page)).toHaveAttribute('aria-activedescendant', await last.getAttribute('id') ?? '');

    // Wrap, in the direction that wraps: from the last row ArrowDown comes back round to the first,
    // while ArrowUp merely steps off the end.
    await page.keyboard.press('ArrowDown');
    await expect(options(page).first()).toHaveAttribute('aria-selected', 'true');

    await page.keyboard.press('ArrowDown');
    await expect(options(page).nth(1)).toHaveAttribute('aria-selected', 'true');

    // A cardless row would have keyed its element id off the placeholder 0, so three of them painted
    // at once all answered to the same id and the announced row was not the highlighted one. Nothing
    // on this database paints a cardless row, so the ids on screen must simply be distinct.
    const ids = await options(page).evaluateAll((els) => els.map((el) => el.id));
    expect(new Set(ids).size).toBe(ids.length);
});

test('commits a choice, names it, and makes the next keystroke a replacement', async ({ page }) => {
    await page.goto('/training-runs/create');
    await page.locator('#app > *').first().waitFor();

    await combobox(page).fill('Agnes Digital');
    await page.keyboard.press('Enter');

    await expect(combobox(page)).toHaveValue('Agnes Digital · [Full-Color Fangirling]');
    await expect(status(page)).toHaveText('Selected Agnes Digital · [Full-Color Fangirling]');
    await expect(combobox(page)).toHaveAttribute('aria-expanded', 'false');

    // The label is selected on commit. Without that, a Trainer who picks the wrong form and immediately
    // retypes appends to the label and is told "No trainee or card found" about what is really a
    // stuck filter.
    await combobox(page).fill('Vodka');
    await expect(options(page).filter({ hasText: 'Fiery Aqua Vitae' })).toHaveCount(1);
    await expect(status(page)).not.toHaveText('No trainee or card found.');
});

test('resolves a whole trainee name typed with Enter to her debut form', async ({ page }) => {
    await page.goto('/training-runs/create');
    await page.locator('#app > *').first().waitFor();

    await combobox(page).fill('Agnes Digital');
    // Close the popup first: with a cursor parked on row zero Enter means "take that row", and it only
    // falls through to the name resolution when nothing is highlighted.
    await page.keyboard.press('Escape');
    await page.keyboard.press('Enter');

    await expect(combobox(page)).toHaveValue('Agnes Digital · [Full-Color Fangirling] (debut)');
});

test('drops the submittable pair when the field stops naming it, so a stale choice cannot write the wrong run', async ({ page }) => {
    await page.goto('/training-runs/create');
    await page.locator('#app > *').first().waitFor();

    await combobox(page).fill('Agnes Digital');
    await page.keyboard.press('Enter');

    // A field reading a fragment over a hidden pair still pointing at Agnes Digital's card would
    // create a run describing something its own input no longer says, and the server cannot catch it:
    // trainee and card agree with each other, they only disagree with the Trainer.
    await combobox(page).fill('nothingresemblingatrainee');

    await expect(status(page)).toHaveText('No trainee or card found.');

    // Enter with nothing highlighted is the Trainer submitting, so the cleared pair reaches the server
    // and `required` refuses it there rather than writing a run.
    await page.keyboard.press('Enter');

    await expect(page).toHaveURL(/\/training-runs\/create$/);
    await expect(page.getByText('The umamusume id field is required.')).toBeVisible();
});

test('hands the chosen trainee and card back after a failed write, and re-paints the field from the ids', async ({ page }) => {
    await page.goto('/training-runs/create');
    await page.locator('#app > *').first().waitFor();

    await combobox(page).fill('Agnes Digital');
    await page.keyboard.press('Enter');

    // Fail a different field with a mistake only the server can see: the Grade Point period is
    // accepted only on a scenario that composes that panel, and "Not set" does not.
    await page.locator('select[name="scenario"]').selectOption('');
    await page.locator('input[name="current_objective_index"]').fill('3');
    await page.getByRole('button', { name: 'Create run' }).click();

    await expect(page).toHaveURL(/\/training-runs\/create$/);
    await expect(page.getByText('This scenario has no Grade Point periods, so there is no period to report.')).toBeVisible();

    // The selection survives the round trip: the label is re-derived from the flashed ids, not
    // re-typed, so the visible name and the posted pair agree again. This is the last assertion in the
    // file on purpose, because the run is never created. A successful create would leave a row in the
    // development database the browser suite shares, and the empty-state test above would then fail
    // on the next pass. The write itself is proven in Pest, where the database is per-test.
    await expect(combobox(page)).toHaveValue('Agnes Digital · [Full-Color Fangirling]');
});
