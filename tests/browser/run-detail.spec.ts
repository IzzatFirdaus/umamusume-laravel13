import { test, expect } from '@playwright/test';
import { deleteRun } from '../utils/delete-run';

// Rendered-DOM evidence for the ported run detail page (SCR-RUN-003, ADR-0020 §1). The server-side
// tests assert the resolved props; these assert what the browser actually shows and what only a
// browser can reach: the section landmarks and their order, the rail's keyboard path, the 44px
// sweep, and the skip link.
//
// Fixture strategy, and why it is unusual. The seeded scratch database holds ZERO training runs on
// purpose (`runs.spec.ts` asserts the empty state), so there is no URL to visit. This spec therefore
// creates its own run through the create form, asserts against it, and deletes it over HTTP before
// it finishes — the suite is left exactly as it found it, and `test.afterEach` cleans up even when an
// assertion throws. That has a second payoff: it is the only end-to-end proof of the create ->
// detail -> delete loop, which `runs.spec.ts` deliberately does not complete so that it does not
// leave rows behind.

const TRAINEE = 'Agnes Digital';
const createdRunUrls: string[] = [];

test.afterEach(async ({ page }) => {
    for (const url of createdRunUrls.splice(0)) {
        await deleteRun(page, url);
    }
});

async function createRunWithScenario(page: import('@playwright/test').Page, scenario: string): Promise<string> {
    await page.goto('/training-runs/create');
    await page.locator('#app > *').first().waitFor();

    await page.locator('#trainee-combobox').fill(TRAINEE);
    await page.keyboard.press('Enter');

    // Pick the scenario before submitting so the run composes the right panels.
    await page.selectOption('select[name="scenario"]', { value: scenario });
    await page.getByRole('button', { name: 'Create run' }).click();
    await page.waitForURL(/\/training-runs\/\d+$/);

    const url = page.url();
    createdRunUrls.push(url);
    return url;
}

test('creates a run, renders its page, and deletes it again', async ({ page }) => {
    await page.goto('/training-runs/create');
    await page.locator('#app > *').first().waitFor();

    await page.locator('#trainee-combobox').fill(TRAINEE);
    await page.keyboard.press('Enter');
    await page.getByRole('button', { name: 'Create run' }).click();
    await page.waitForURL(/\/training-runs\/\d+$/);

    createdRunUrls.push(page.url());

    // One h1, and it is the shell's: `AppLayout` renders the `#title` slot inside its own heading,
    // which is the pattern every ported page follows. The caption beside it names the status and the
    // absence of a scenario rather than inventing one.
    const headings1 = await page.getByRole('heading', { level: 1 }).allTextContents();
    expect(headings1).toEqual([TRAINEE]);
    await expect(page.getByText(/No scenario set/)).toBeVisible();

    // The eight sections the frame pins, in document order.
    const headings = await page.getByRole('heading', { level: 2 }).allTextContents();
    expect(headings).toEqual([
        'Resources',
        'Support deck',
        'Turns',
        'Skills',
    ]);

    // The scenario panels are absent because this run named no scenario, which is the honest
    // absence rather than a baseline schedule borrowed from URA Finale (D-220).
    await expect(page.getByRole('region', { name: 'Race calendar' })).toHaveCount(0);
    await expect(page.getByText(/No turns logged yet/)).toBeVisible();
    await expect(page.getByText('Search the skill catalog')).toBeVisible();

    // R-6: the hint-level ladder is recorded, so the screen names it rather than saying no source
    // settles it. Static copy on the page, so it is asserted where it renders.
    await expect(
        page.getByText('Hint-level discounts follow the ladder recorded in'),
    ).toBeVisible();

    // The three groups the skills section always prints. A run created from a card is seeded with
    // that card's `Starting` skills (KI-33), so the first group holds rows and only the two the
    // Trainer has not touched read "None." — asserted per group, next to its own heading, because
    // a count of the word would be a claim about what the seeded card happens to carry.
    const skills = page.getByRole('region', { name: 'Skills' });
    for (const group of ['Starting', 'Acquired', 'Skipped']) {
        await expect(skills.getByRole('heading', { name: group, level: 3 })).toBeVisible();
    }

    for (const group of ['Acquired', 'Skipped']) {
        const heading = skills.getByRole('heading', { name: group, level: 3 });
        await expect(heading.locator('xpath=following-sibling::*[1]')).toHaveText('None.');
    }

    // The skip link is a real target, not a trap (it lives in the shell now).
    const skip = page.getByRole('link', { name: 'Skip to content' });
    await skip.focus();
    await expect(skip).toBeVisible();
    await expect(page.locator('main#main')).toHaveCount(1);
});

test('sizes the run page controls to the 44px contract', async ({ page }) => {
    // The escape-hatch and delete disclosures are opened rather than skipped: a control inside a
    // closed <details> has no box to measure, and a sweep that only ever sees the open controls is
    // how a 32px button survives the claim.
    test.setTimeout(90_000);

    await page.goto('/training-runs/create');
    await page.locator('#app > *').first().waitFor();
    await page.locator('#trainee-combobox').fill(TRAINEE);
    await page.keyboard.press('Enter');
    await page.getByRole('button', { name: 'Create run' }).click();
    await page.waitForURL(/\/training-runs\/\d+$/);

    createdRunUrls.push(page.url());

    for (const selector of ['a:has-text("Export CSV")', 'a:has-text("Export JSON")']) {
        const box = await page.locator(selector).boundingBox();
        expect(box?.height ?? 0, `${selector} is not sized to the 44px contract`).toBeGreaterThanOrEqual(44);
    }

    for (const name of ['Change scenario', 'Change status', 'Save skill status']) {
        const box = await page.getByRole('button', { name }).boundingBox();
        expect(box?.height ?? 0, `${name} is not sized to the 44px contract`).toBeGreaterThanOrEqual(44);
    }

    // The escape hatch's summary and the delete disclosure's summary are the two 44px disclosures,
    // and the save button inside the first is measured with the disclosure open.
    for (const summary of ['Correct a turn by hand', 'Delete run']) {
        const box = await page.getByText(summary, { exact: true }).boundingBox();
        expect(box?.height ?? 0, `${summary} is not sized to the 44px contract`).toBeGreaterThanOrEqual(44);
    }

    await page.getByText('Correct a turn by hand', { exact: true }).click();
    const save = await page.getByRole('button', { name: 'Save correction' }).boundingBox();
    expect(save?.height ?? 0, 'Save correction is not sized to the 44px contract').toBeGreaterThanOrEqual(44);
});

test('carries the guided rail keyboard path and its advertised shortcuts', async ({ page }) => {
    await page.goto('/training-runs/create');
    await page.locator('#app > *').first().waitFor();
    await page.locator('#trainee-combobox').fill(TRAINEE);
    await page.keyboard.press('Enter');
    await page.getByRole('button', { name: 'Create run' }).click();
    await page.waitForURL(/\/training-runs\/\d+$/);

    createdRunUrls.push(page.url());

    const group = page.getByRole('radiogroup', { name: 'Turn choice' });
    await expect(group).toBeVisible();

    // D-55's shortcuts are advertised on screen, never folklore, and the count comes from the choices
    // themselves. URA Finale's rail offers the five disciplines plus Rest and Recreation.
    await expect(page.getByText(/Keys 1 to 7 choose an activity/)).toBeVisible();
    await expect(group.getByRole('radio')).toHaveCount(7);

    // The digit binding selects the nth activity. Pressed with focus outside a text field, because a
    // digit typed into Speed is a number and must not be a command.
    await group.getByRole('radio').first().focus();
    await page.keyboard.press('3');
    await expect(group.getByRole('radio').nth(2)).toBeChecked();

    // Escape steps back to the choice group rather than clearing typed numbers (D-56). It is
    // pressed from a non-editing context on purpose: while a number field has focus the handler
    // deliberately does nothing, because a digit typed into Speed is a number and the same
    // reasoning says Escape inside Speed is the Trainer's, not the rail's.
    await page.getByText('Correct a turn by hand', { exact: true }).focus();
    await page.keyboard.press('Escape');
    await expect(group.getByRole('radio').nth(2)).toBeFocused();

    // And with the numbers half-typed, Escape leaves the work alone.
    await page.locator('input[name="speed"]').first().fill('55');
    await page.locator('input[name="speed"]').first().focus();
    await page.keyboard.press('Escape');
    await expect(page.locator('input[name="speed"]').first()).toHaveValue('55');

    // The first turn has a previous turn to compare against (the create form seeded none, so this run
    // is genuinely empty), and the rail still offers the door.
    await expect(page.getByRole('button', { name: 'Preview this turn' })).toBeVisible();
});

// Prose that the server-side ScenarioPanelUiTest now asserts as resolved props, moved here as
// rendered-DOM evidence (migration rule: prop values → assertInertia; rendered copy → Playwright).

test('renders Unity Cup scenario prose on an empty run', async ({ page }) => {
    await createRunWithScenario(page, 'unity_cup');

    // Team Rank gauge with no recorded rank: the unrecorded reason, not a number or a guess.
    await expect(page.getByText(/Rank not recorded/)).toBeVisible();
    await expect(page.getByText(/facility level 0/)).toHaveCount(0);

    // Spirit Burst roster: empty, with the six-state legend so colour alone is never the signal.
    await expect(page.getByText(/No teammate burst states recorded/)).toBeVisible();

    // Race form: the Circles field appears because Unity Cup composes team_race.
    await expect(page.getByText(/Circles read/)).toBeVisible();

    // Team Race panel: empty state, not zero circles.
    await expect(page.getByText(/No team races recorded/)).toBeVisible();
});

test('renders Trackblazer scenario prose on an empty run', async ({ page }) => {
    await createRunWithScenario(page, 'trackblazer');

    // Fatigue chip: unrecorded, not zero.
    await expect(page.getByText(/no consecutive-race reading recorded/)).toBeVisible();
    await expect(page.getByText(/60-90|0-33|0-15/)).toHaveCount(0);

    // Shop panel: the overwrite warning is static copy that always renders.
    await expect(page.getByText(/Buying at a higher rank overwrites the lower one/)).toBeVisible();

    // Grade Point meter: the standard-track disclosure and the KI-15 pointer.
    await expect(page.getByText(/Targets shown are the standard track/)).toBeVisible();
    await expect(page.getByText(/KI-15 carries the disagreement/)).toBeVisible();

    // Epithet checklist: empty state names the derivation source, not zero. Whitespace-tolerant
    // because the sentence is wrapped in the template, and a regex match is not whitespace-normalised.
    await expect(page.getByText(/Derived\s+from no entered races yet/)).toBeVisible();

    // The cap the scenario sets, printed beside the purchase control rather than only held in the
    // payload (ShopPurchasePayloadTest pins the prop; this is the sentence a Trainer reads).
    await expect(page.getByText(/up to 5 copies of an item can be held at a time/)).toBeVisible();

// The coin balance is not stored per turn, so the panel says so rather than subtracting toward
    // zero and printing a balance nobody entered (D-232). Both the shop and the Grade Point meter
    // state the same absence, and both are honest, so the first is asserted.
await expect(page.getByText('Shop Coins: not yet recorded').first()).toBeVisible();
});

test('records a shop purchase and refuses a price the catalogue disagrees with', async ({ page }) => {
    await createRunWithScenario(page, 'trackblazer');

    // Empty state first, so the recorded row below is provably what the write produced.
    await expect(page.getByText('No purchases recorded for this run.')).toBeVisible();

    await page.locator('#purchase-turn').fill('4');
    await page.locator('#purchase-item').selectOption('Royal Kale Juice');
    await page.locator('#purchase-cost').fill('70');
    await page.locator('#purchase-effect').fill('Energy +100, Mood −1');
    await page.getByRole('button', { name: 'Record purchase' }).click();

    // Scoped to the recorded list: the item's name also names an option in the select above it.
    const recorded = page.getByRole('list', { name: 'Recorded purchases' });
    await expect(recorded.getByText('Royal Kale Juice')).toBeVisible();
    await expect(recorded.getByText('70 coins')).toBeVisible();
    await expect(page.getByText(/Spent:\s*70\s*coins/)).toBeVisible();

    // A price the client does not charge answers at the field, marked and explained, rather than
    // reaching the payload and throwing (D-256).
    await page.locator('#purchase-turn').fill('5');
    await page.locator('#purchase-item').selectOption('Vita 40');
    await page.locator('#purchase-cost').fill('40');
    await page.locator('#purchase-effect').fill('Energy +40');
    await page.getByRole('button', { name: 'Record purchase' }).click();

    const cost = page.locator('#purchase-cost');
    await expect(cost).toHaveAttribute('aria-invalid', 'true');
    await expect(page.locator('#purchase-cost-error')).toContainText('charges 55 coins for Vita 40');
});

test('rates a logged stat with a badge the banding can produce', async ({ page }) => {
    // One write plus a full page render, plus the afterEach that deletes the run over HTTP. The dev
    // server is single-threaded, so each navigation waits on the previous one.
    test.setTimeout(120_000);

    await createRunWithScenario(page, 'ura_finale');

    // No turn logged means no band: five zeroes would be a claim about a trainee nobody entered.
    await expect(page.getByRole('region', { name: 'Stats' })).toHaveCount(0);

    // The escape hatch is the one write path that takes raw values with no rail stage, which is
    // what makes an exact reading reachable here.
    await page.getByText('Correct a turn by hand').click();
    await page.locator('input[name="turn"][required]').last().fill('1');
    await page.locator('input[name="speed"][required]').last().fill('550');
    await page.locator('input[name="stamina"][required]').last().fill('280');
    await page.locator('input[name="power"][required]').last().fill('240');
    await page.locator('input[name="guts"][required]').last().fill('210');
    await page.locator('input[name="wit"][required]').last().fill('150');
// Waited on the write itself rather than on the button: the POST is an Inertia XHR, so the
    // address bar never changes and a bare `expect(...).toBeVisible()` is a race against a
    // single-threaded dev server shared with three other suites on this host.
const written = page.waitForResponse(
    (response) => response.url().includes('/turns') && response.request().method() === 'POST',
);
await page.getByRole('button', { name: 'Save correction' }).click();
await written;

// The turn is in the log, so the band has a reading to draw: the band follows the latest turn
// and five zeroes would be a claim about a trainee nobody entered (D-220). The row is addressed by
// its own button, because the log's outer section and its clipping scroll region carry the same name.
await expect(page.getByRole('button', { name: 'Edit turn 1' })).toBeVisible();

const stats = page.getByRole('region', { name: 'Stats' });
await expect(stats).toBeVisible();

    // One badge per rated stat, and the fifth column is Skill Points, which has no grade.
    const badges = stats.getByTitle('Derived from the entered value, not read from the client');
    await expect(badges).toHaveCount(5);

    // KI-8 and R13: 550 lands on the half-step `B+`, which is a B's fill and the full letter's
    // text. The Blade badge's class map once keyed the nine base letters only, so `B+` threw and
    // took the page down; asserting the pair proves both halves of the fix.
    await expect(badges.first()).toHaveText('B+');
    await expect(badges.first()).toHaveClass(/bg-grade-b(?![\w-])/);

    // The band prints its own arithmetic line, so the ceiling is explained rather than implied.
    await expect(stats.getByText(/is where training gains halve/)).toBeVisible();
});
