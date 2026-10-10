import { test, expect } from '@playwright/test';
import { buildAxe } from '../utils/accessibility';
import { deleteRun } from '../utils/delete-run';
import { recordTurns } from '../utils/record-turns';

/*
 * Rendered-copy, keyboard and accessibility evidence for SCREEN-017, the Scenario Race Planner
 * (`SCR-CAR-023`, plan §9 E5). `CareerRacePlannerTest` asserts the resolved props — the four groups
 * and their rules, the comparison cells present and absent, the alignment words, the held figures and
 * the Critical alert. These cases assert what only a browser reaches: the `N/A` title text a sighted
 * Trainer hovers, the selection control's keyboard behaviour and its pressed state, the comparison
 * table's own rows, the 44px sweep, a 320px reflow and an axe scan.
 *
 * Fixture strategy. The Critical alert is raised by the calendar, so the run has to have reached the
 * turn an obligation sits on. `is_mandatory` is true on exactly one in-career-year row, Junior turn 12
 * (the rest are the finale block, outside the 24-turn grid), so the fixture logs eleven turns and the
 * planner's position lands on turn 12. The turns are logged through the run screen's own raw turn
 * form, which is the app's supported path in, and the run is deleted over HTTP in `afterEach`
 * (`tests/utils/delete-run.ts`).
 *
 * `unity_cup` rather than `ura_finale`: both carry the same shared calendar rows, and unity_cup is what
 * the create form's scenario list and the seeded catalogue already agree on.
 */

const WRITE = { timeout: 60_000 } as const;

const createdRunUrls: string[] = [];

test.afterEach(async ({ page }) => {
    for (const url of createdRunUrls.splice(0)) {
        await deleteRun(page, url);
    }
});

/**
 * A career with eleven turns logged, then the Scenario Race Planner for it.
 *
 * Eleven, not ten: the planner's position is one past the last logged turn, so eleven logged turns put
 * it on turn 12, the Junior Make Debut — the one mandatory race inside the year grid, and therefore the
 * only way the Level 1 alert can be reached at all.
 */
async function openPlanner(page: import('@playwright/test').Page): Promise<void> {
    await page.goto('/training-runs/create', { waitUntil: 'domcontentloaded' });
    await page.locator('#app > *').first().waitFor();
    await page.locator('#trainee-combobox').fill('Agnes Digital');
    await page.keyboard.press('Enter');
    await page.selectOption('select[name="scenario"]', { value: 'unity_cup' });
    await page.getByRole('button', { name: 'Create run' }).click();
    await page.waitForURL(/\/training-runs\/\d+\/cockpit$/, WRITE);
    createdRunUrls.push(page.url());

    const runId = page.url().match(/\/training-runs\/(\d+)/)?.[1] ?? '';
    const runUrl = page.url().replace(/\/cockpit$/, '');

    await recordTurns(page, runId, 11);

    await page.goto(`${runUrl}/races/planner`, { waitUntil: 'domcontentloaded' });
    await page.locator('#app > *').first().waitFor();
}

test('names each held figure with its reason, and states the next obligation without promising it', async ({ page }) => {
    await openPlanner(page);

    await expect(page.getByRole('heading', { name: 'Race planner' })).toBeVisible();

    // The two figures the brief asks for, named as held: hovering each `N/A` says why, and it names
    // the decision rather than a number.
    const held = page.getByRole('region', { name: 'Held figures' });
    await expect(held.getByText('Win probability')).toBeVisible();
    await expect(held.getByText('Expected risk')).toBeVisible();
    for (const term of await held.getByText('N/A').all()) {
        await expect(term).toHaveAttribute('title', /ADR-0016/);
    }

    // Level 1 is a glyph plus the word. The `!`-style mark is aria-hidden, so the alert is located by
    // the sentence the calendar produced rather than by the mark.
    await expect(page.getByText('Critical: Junior Make Debut is due and not recorded')).toBeVisible();

    // The region states the next obligation and its turn, and refuses the brief's promise.
    await expect(page.getByText('Next mandatory race:')).toBeVisible();
    await expect(page.getByText('whether the rest of the career', { exact: false })).toBeVisible();
    await expect(page.locator('main')).not.toContainText('will be missed');
    await expect(page.locator('main')).not.toContainText('%');
});

test('lists the four groups, each with the rule it was built by, and the rival absence', async ({ page }) => {
    await openPlanner(page);

    for (const label of ['Mandatory races', 'Upcoming races', 'Optional races', 'Rival races']) {
        await expect(page.getByRole('heading', { name: label })).toBeVisible();
    }

    // The obligation the position has reached, inside its own group.
    await expect(page.getByRole('heading', { name: 'Junior Make Debut' }).first()).toBeVisible();

    // The rival group has no source, and says so rather than listing races.
    await expect(page.getByText('No column marks a rival race', { exact: false })).toBeVisible();

    // Each group states its own rule, so a reader knows what the group holds rather than inferring it.
    await expect(page.getByText('whose turn has not arrived yet', { exact: false })).toBeVisible();
});

test('folds a race the catalogue only places, and keeps the grid for one it describes', async ({ page }) => {
    // R2-12. The audit measured 17,409 characters on this page, most of them empty panels: the seeded
    // debut row carries the tier word `Debut` and no distance, band, surface or fan figure, so its card
    // printed ten labelled refusals. The fold is the card's own; the fact list survives in the drawer,
    // which is the disclosure this component already had.
    await openPlanner(page);

    const debut = page.locator('li').filter({
        has: page.getByRole('heading', { name: 'Junior Make Debut' }),
    });

    await expect(debut.getByText('Details not recorded for this race.')).toBeVisible();

    // The two statements that are not refused figures still print: the obligation marker and the year.
    await expect(debut.getByText('Mandatory', { exact: false }).first()).toBeVisible();
    await expect(debut.getByText('Junior').first()).toBeVisible();

    // A race the catalogue does describe keeps its course facts on the card.
    const described = page.locator('li').filter({ has: page.getByText('Distance band', { exact: true }) }).first();
    await expect(described).toBeVisible();

    // Nothing was hidden by the fold: the debut's own drawer lists every fact, refusal included.
    await debut.getByRole('button', { name: 'Details and actions for Junior Make Debut' }).click();
    const drawer = page.getByRole('dialog');
    await expect(drawer.getByText('Distance band', { exact: true }).first()).toBeVisible();
    await expect(drawer.getByText('Estimated win probability', { exact: false }).first()).toBeVisible();
    await drawer.getByRole('button', { name: 'Close' }).click();
});

test('compares the selected races side by side, one property per row, by keyboard', async ({ page }) => {
    await openPlanner(page);

    // Nothing is selected, so the comparison says what to do rather than drawing an empty table.
    await expect(page.getByText('Nothing to compare yet', { exact: false })).toBeVisible();

    // The selection control is a pressed toggle, so the state is exposed rather than implied by colour.
    const first = page.getByRole('button', { name: /^Compare / }).first();
    const second = page.getByRole('button', { name: /^Compare / }).nth(1);

    await first.focus();
    await expect(first).toBeFocused();
    await page.keyboard.press('Enter');
    await expect(first).toHaveAttribute('aria-pressed', 'true');

    await second.focus();
    await page.keyboard.press('Enter');
    await expect(second).toHaveAttribute('aria-pressed', 'true');

    // The comparison is a real table with a row header per property, not a grid of cards.
    const rewards = page.getByRole('region', { name: 'Selected races, rewards side by side' });
    await expect(rewards).toBeVisible();
    await expect(rewards.getByRole('rowheader', { name: 'Grade', exact: true })).toBeVisible();
    await expect(rewards.getByRole('rowheader', { name: 'Shop Coins' })).toBeVisible();
    await expect(rewards.getByRole('columnheader')).toHaveCount(3);

    // The alignment table carries the same columns and the plain words, with no score and no
    // percentage anywhere in it.
    const alignment = page.getByRole('region', { name: 'Selected races against the build target' });
    await expect(alignment).toBeVisible();
    await expect(alignment.getByRole('rowheader', { name: 'Running style' })).toBeVisible();
    await expect(alignment.getByText('Not recorded').first()).toBeVisible();
    await expect(alignment).not.toContainText('%');

    // The scroll container is the focus stop, which is what makes the wide table keyboard-reachable
    // at 320px (WCAG 1.4.10).
    await rewards.focus();
    await expect(rewards).toBeFocused();

    // Deselecting returns the section to its instruction.
    await first.focus();
    await page.keyboard.press('Enter');
    await expect(first).toHaveAttribute('aria-pressed', 'false');
});

test('sizes the planner controls to the 44px contract', async ({ page }) => {
    await openPlanner(page);

    // One measurement inside the browser rather than a locator walked element by element: a locator
    // re-resolves on every `nth()`, so a list that reflows mid-sweep (hydration, a font swap) can
    // hand back a different element, or a box for one that has not been laid out yet. This reads
    // every control's geometry in a single pass and names the offenders, so a failure says which
    // control and how tall it was rather than only that one was short.
    const undersized = await page.locator('main').evaluate((root) =>
        Array.from(root.querySelectorAll('button, a[href]'))
            .map((element) => {
                const box = element.getBoundingClientRect();
                const style = getComputedStyle(element);

                return {
                    text: (element.textContent ?? '').trim().replace(/\s+/g, ' ').slice(0, 60),
                    height: Math.round(box.height),
                    placed: box.width > 0 && box.height > 0 && style.visibility !== 'hidden' && style.display !== 'none',
                };
            })
            .filter((control) => control.placed && control.height < 44)
            .map((control) => `${control.height}px: ${control.text}`),
    );

    expect(undersized, `controls under the 44px contract: ${JSON.stringify(undersized)}`).toEqual([]);
});

test('reflows at 320px without pushing the page sideways', async ({ page }) => {
    await openPlanner(page);

    await page.getByRole('button', { name: /^Compare / }).first().click();
    await page.getByRole('button', { name: /^Compare / }).nth(1).click();

    await page.setViewportSize({ width: 320, height: 800 });

    // The wide comparison table scrolls inside its own focusable region rather than widening the
    // document (WCAG 1.4.10 Reflow).
    const overflow = await page.evaluate(() => document.documentElement.scrollWidth - document.documentElement.clientWidth);

    expect(overflow, 'the document scrolls horizontally at 320px').toBeLessThanOrEqual(1);
});

test('passes an axe scan at WCAG A and AA', async ({ page }) => {
    await openPlanner(page);

    await page.getByRole('button', { name: /^Compare / }).first().click();
    await page.getByRole('button', { name: /^Compare / }).nth(1).click();

    // `buildAxe` scopes to `#app`, which is what makes this deterministic (KI-63).
    const results = await buildAxe(page).analyze();

    expect(results.violations, JSON.stringify(results.violations, null, 2)).toEqual([]);
});
