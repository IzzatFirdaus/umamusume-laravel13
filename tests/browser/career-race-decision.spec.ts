import { test, expect } from '@playwright/test';
import { buildAxe } from '../utils/accessibility';
import { deleteRun } from '../utils/delete-run';

/*
 * Rendered-copy, keyboard and accessibility evidence for SCREEN-011, the Race Decision
 * (`SCR-CAR-013`, plan §8 D10). `CareerRaceDecisionTest` asserts the resolved props — the ten facts
 * present and absent, the readiness refusal, the mandatory list, the four empty states. These cases
 * assert what only a browser reaches: the `N/A` title text a sighted Trainer hovers, the drawer's
 * keyboard behaviour, the 44px sweep, and an axe scan.
 *
 * Fixture strategy. The turn being decided has to fall on a turn the calendar carries a race on, and
 * for the Level 1 assertions it has to fall on a race the calendar calls mandatory. `is_mandatory` is
 * true on exactly one in-career-year row, Junior turn 12 (the rest are the finale block, which is
 * outside the 24-turn grid), so the fixture logs eleven turns and the decision lands on turn 12. The
 * turns are logged through the run screen's own raw turn form, which is the app's supported path in,
 * and the run is deleted over HTTP in `afterEach` (`tests/utils/delete-run.ts`).
 *
 * The CSV import would have been one write instead of ten, and it is broken: `Import.vue`'s
 * `confirmImport()` posts `preview.run`, which carries only the run's own columns, while
 * `ImportHistoricalRunRequest` requires `csv` and `turns`. The confirm step therefore always fails
 * validation, which no test has noticed because `run-import.spec.ts` deliberately reaches the confirm
 * step and never posts it. Filed as KI-64; this spec does not depend on it.
 *
 * `unity_cup` rather than `ura_finale`: both carry the same shared calendar rows, and unity_cup is
 * what the create form's scenario list and the seeded catalogue already agree on.
 */

const WRITE = { timeout: 60_000 } as const;

const createdRunUrls: string[] = [];

test.afterEach(async ({ page }) => {
    for (const url of createdRunUrls.splice(0)) {
        await deleteRun(page, url);
    }
});

/**
 * A career with eleven turns logged, then the Race Decision screen for it.
 *
 * Eleven, not ten: the turn being decided is one past the last logged one, so eleven logged turns put
 * the decision on turn 12, the Junior Make Debut — the one mandatory race inside the year grid.
 *
 * The writes get 60s rather than the config's 15s, for the reason `career-cockpit.spec.ts` records:
 * `php artisan serve` is a single-process `php -S`, so a write queues behind whatever else is on the
 * box, and `playwright.config.ts` already raised the test budget for exactly that.
 */
async function openDecision(page: import('@playwright/test').Page): Promise<void> {
    await page.goto('/training-runs/create', { waitUntil: 'domcontentloaded' });
    await page.locator('#app > *').first().waitFor();
    await page.locator('#trainee-combobox').fill('Agnes Digital');
    await page.keyboard.press('Enter');
    await page.selectOption('select[name="scenario"]', { value: 'unity_cup' });
    await page.getByRole('button', { name: 'Create run' }).click();
    await page.waitForURL(/\/training-runs\/\d+$/, WRITE);
    createdRunUrls.push(page.url());

    // Eleven turns, through the run screen's own raw form, so the turn being decided is turn 12 —
    // Junior Year, Late June, where the calendar carries the mandatory debut. The guided rail also
    // carries a `turn` input, so every locator is scoped to the disclosure.
    const hatch = page.locator('details', { has: page.getByText('Correct a turn by hand') });
    await page.getByText('Correct a turn by hand').click();

    for (let turn = 1; turn <= 11; turn++) {
        await hatch.locator('input[name="turn"]').fill(String(turn));
        await hatch.locator('input[name="speed"]').fill('600');
        await hatch.locator('input[name="stamina"]').fill('500');
        await hatch.locator('input[name="power"]').fill('500');
        await hatch.locator('input[name="guts"]').fill('500');
        await hatch.locator('input[name="wit"]').fill('500');
        await hatch.getByRole('button', { name: 'Save correction' }).click();
        await expect(page.getByText(`Turn ${turn} logged.`)).toBeVisible(WRITE);
    }

    await page.goto(`${page.url()}/races`, { waitUntil: 'domcontentloaded' });
    await page.locator('#app > *').first().waitFor();
}

test('names each absent race field with its reason, and refuses a readiness band', async ({ page }) => {
    await openDecision(page);

    // The turn being decided, on the client's own grid. Scoped to the region: each race card prints
    // its own turn too, so a page-wide `Turn 12` is ambiguous.
    const turnRegion = page.getByRole('region', { name: 'The turn being decided' });
    await expect(turnRegion).toBeVisible();
    await expect(turnRegion.getByText('Turn 12')).toBeVisible();

    // The race the calendar carries at that turn. The title is the catalogue's own for this scenario
    // and turn, which is "Junior Make Debut" at Junior turn 12.
    await expect(page.getByRole('heading', { name: 'Races to decide between' })).toBeVisible();
    await expect(page.getByRole('heading', { name: 'Junior Make Debut' })).toBeVisible();

    // The held figure, named as held: hovering the N/A says why, and it names the decision rather
    // than a number.
    const readiness = page.getByRole('heading', { name: 'Readiness' }).locator('..').getByText('N/A');
    await expect(readiness).toHaveAttribute('title', /ADR-0016/);

    // A brief field with no column behind it: the reason travels with the value on a disclosure a
    // keyboard and a screen reader reach, not only on hover.
    await expect(page.getByText('Running style').first()).toBeVisible();
    await expect(page.locator('summary', { hasText: 'aptitude, not a property of the race' }).first()).toBeVisible();

    // Level 1 is a glyph plus the word, in three places: the region heading, the next obligation in
    // the list, and the card itself. The `!` is aria-hidden, so the heading matched by accessible name
    // is the word alone while a sighted Trainer reads glyph and word together — the state never rests
    // on colour or on the glyph. Exact text is what these three assert: the marker element's own text
    // is "! NEXT" and "! Mandatory", so `exact: true` on the bare word matches nothing. And the screen
    // says "Mandatory", never "Goal" (`79ffad5`).
    await expect(page.getByRole('heading', { name: 'Mandatory races' })).toBeVisible();
    await expect(page.getByText('! NEXT', { exact: true })).toBeVisible();
    await expect(page.getByText('! Mandatory', { exact: true })).toBeVisible();
    await expect(page.getByText('Goal', { exact: true })).toHaveCount(0);

    // The screen carries no percentage anywhere: a win estimate and the brief's risk thresholds are
    // both percentages, and neither is built.
    await expect(page.locator('main')).not.toContainText('%');
});

test('opens the drawer from the keyboard, closes it on Escape and returns focus to the opener', async ({ page }) => {
    await openDecision(page);

    const opener = page.getByRole('button', { name: /^Details and actions for/ }).first();
    await opener.focus();
    await expect(opener).toBeFocused();

    // Enter on the opener is the keyboard path in.
    await page.keyboard.press('Enter');
    const dialog = page.locator('dialog[open]');
    await expect(dialog).toBeVisible();

    // `showModal()` moves focus into the drawer — to the first focusable descendant, or to the dialog
    // itself — so the drawer is reachable without a pointer, and the page behind it is inert while it
    // is open. Which of the two receives it is the browser's choice, so this asserts containment.
    expect(await dialog.evaluate((el) => el.contains(document.activeElement))).toBe(true);

    // Escape is the dialog's own behaviour, and the focus return is the component's.
    await page.keyboard.press('Escape');
    await expect(page.locator('dialog[open]')).toHaveCount(0);
    await expect(opener).toBeFocused();

    // And the drawer's own actions are reachable from the keyboard.
    await page.keyboard.press('Enter');
    await expect(page.getByRole('button', { name: 'Enter Race' }).first()).toBeVisible();
    await expect(page.getByRole('link', { name: 'Skip for now' }).first()).toBeVisible();
    await page.keyboard.press('Escape');
});

test('sizes the race decision controls to the 44px contract', async ({ page }) => {
    await openDecision(page);

    const details = page.getByRole('button', { name: /^Details and actions for/ }).first();
    expect((await details.boundingBox())?.height ?? 0, 'Details is not sized to the 44px contract')
        .toBeGreaterThanOrEqual(44);

    await details.click();
    await expect(page.locator('dialog[open]')).toBeVisible();

    for (const control of [
        page.getByRole('button', { name: 'Close' }).first(),
        page.getByRole('button', { name: 'Enter Race' }).first(),
        page.getByRole('link', { name: 'Skip for now' }).first(),
    ]) {
        const box = await control.boundingBox();
        expect(box?.height ?? 0, 'a race decision control is not sized to the 44px contract')
            .toBeGreaterThanOrEqual(44);
    }

    await page.keyboard.press('Escape');
});

test('passes an axe scan at WCAG A and AA', async ({ page }) => {
    await openDecision(page);

    // `buildAxe` scopes to `#app`, which is what makes this deterministic (KI-63).
    const results = await buildAxe(page).analyze();

    expect(results.violations, JSON.stringify(results.violations, null, 2)).toEqual([]);
});
