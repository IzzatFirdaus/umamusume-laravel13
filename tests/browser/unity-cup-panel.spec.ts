import { test, expect } from '@playwright/test';
import { buildAxe } from '../utils/accessibility';
import { deleteRun } from '../utils/delete-run';
import { expectTapTargets } from '../utils/tap-targets';

/*
 * The Unity Cup Team Cockpit (SCREEN-015, plan §9.1). `UnityCupPanelTest` asserts the `team`
 * payload — full team, partial team, none; these cases assert what only a browser reaches: the
 * configured facts and named absences a real run shows, the reading order and the keyboard path
 * through the panel, the meter's text and its bar, the 44px sweep, the 320px reflow and axe.
 *
 * **Every data state here is an absence, on purpose.** No UI write records a team rank, a burst
 * state or a Spirit reading (the capture proposal of `SCREEN_SPEC.md` §7-6 is still the owner's),
 * and no seeder creates `team_race` scenario slots, so a browser can only see the panel the way a
 * fresh run meets it: the config facts rendered and every recorded figure named as absent. The
 * populated roster and the recorded rank are proven at props level in `UnityCupPanelTest`, which is
 * the honest split while no write path exists.
 */

const WRITE = { timeout: 60_000 } as const;

const createdRunUrls: string[] = [];

test.afterEach(async ({ page }) => {
    for (const url of createdRunUrls.splice(0)) {
        await deleteRun(page, url);
    }
});

async function openUnityCupCockpit(page: import('@playwright/test').Page): Promise<void> {
    await page.goto('/training-runs/create', { waitUntil: 'domcontentloaded' });
    await page.locator('#app > *').first().waitFor();
    await page.locator('#trainee-combobox').fill('Rice Shower');
    await page.keyboard.press('Enter');
    await page.selectOption('select[name="scenario"]', { value: 'unity_cup' });
    await page.getByRole('button', { name: 'Create run' }).click();
    await page.waitForURL(/\/training-runs\/\d+\/cockpit$/, WRITE);
    const runUrl = page.url().replace(/\/cockpit$/, '');
    createdRunUrls.push(runUrl);

    await page.goto(`${runUrl}/cockpit`, { waitUntil: 'domcontentloaded' });
    await page.locator('#app > *').first().waitFor();
}

test('renders the configured burst bands, their payout timing and the Extreme caveat', async ({ page }) => {
    await openUnityCupCockpit(page);

    const section = page.getByRole('region', { name: 'Scenario' });

    // The four config bands, first and last transcribed values, in a real table with row headers.
    const table = section.getByRole('table', { name: /Reward by combined Spirit Burst/ });
    await expect(table).toBeVisible();
    await expect(table.getByRole('row').getByText('4–6', { exact: true })).toBeVisible();
    await expect(table.getByText('Gold hint Lv3, +40 matching stat, +40 SP')).toBeVisible();

    // The caveat names all three publisher conflicts, in words: state never rides the ⚠ mark alone.
    await expect(section.getByText(/Three parts of this table are publisher conflicts/)).toBeVisible();

    // The table's own provenance marker, per design-2.0 §49.
    await expect(section.getByRole('img', { name: /^Estimated/ })).toBeVisible();

    // The payout timing renders, and the run's own band position is named as absent, not guessed.
    await expect(section.getByText(/The gold versions arrive from the scripted event in Senior Year, Late November/)).toBeVisible();
    await expect(section.getByText(/Where this run sits in the bands is not shown/)).toBeVisible();
});

test('shows the Spirit meter as text and a bar, with readiness and the held advice named absent', async ({ page }) => {
    await openUnityCupCockpit(page);

    const section = page.getByRole('region', { name: 'Scenario' });

    // The meter carries the reading twice: the text and the labelled bar's accessible name.
    const meter = section.getByRole('img', { name: /Current Spirit: not recorded/ });
    await expect(meter).toBeVisible();
    await expect(section.getByText('Current Spirit', { exact: true })).toBeVisible();
    await expect(section.getByText('N/A').first()).toBeVisible();

    // Burst readiness is not derivable — no recorded Spirit, no configured threshold — so the
    // sentence says why instead of printing a percentage or a band off nothing.
    await expect(section.getByText(/Burst readiness is not computed/)).toBeVisible();

    // "Recommended timing" and "projected benefit" are held advice (ADR-0020 §3): a named absence
    // whose title carries the ruling.
    const held = section.getByText('Recommended timing and projected benefit are not built.');
    await expect(held).toBeVisible();
    await expect(held).toHaveAttribute('title', /ADR-0020/);
});

test('draws the rank gauge, the five stat grades and the roster as their empty states', async ({ page }) => {
    await openUnityCupCockpit(page);

    const section = page.getByRole('region', { name: 'Scenario' });

    // No rank recorded: the gauge says so, and the config ladder still renders as reference.
    await expect(section.getByText(/Rank not recorded/)).toBeVisible();
    const ladder = section.getByRole('list', { name: 'League ladder' });
    await expect(ladder).toBeVisible();
    // Each rung is one `li` carrying the letter AND its level, so the rung is counted and read by its
    // own text rather than matched as an exact string (an exact 'G' never matches `G lv 1`).
    await expect(ladder.getByRole('listitem')).toHaveCount(8);
    await expect(ladder.locator('li').first()).toContainText('G');
    await expect(ladder.locator('li').last()).toContainText('S');
    // The +30 bonus binds to a recorded rank; with none, no bonus claim is made at all.
    await expect(section.getByText(/Team Ranking Bonus/)).toHaveCount(0);

    // Five team stat grades, each N/A with the reason, never a zero or a dash.
    const grades = section.getByRole('list', { name: 'Team stat grades' });
    await expect(grades).toBeVisible();
    await expect(grades.getByText('N/A')).toHaveCount(5);

    // The roster is empty and says what a recorded one would carry, including the six states.
    await expect(section.getByText(/No teammate burst states recorded/)).toBeVisible();

    // The Special Training facts are config's: the rework removal and the Wit burst bonus.
    await expect(section.getByText(/The extra Energy cost on Special Training was removed on 2026-07-01/)).toBeVisible();
    await expect(section.getByText(/A burst on the Wit facility grants 5 Energy recovery/)).toBeVisible();
});

test('renders the team races panel with the configured margin and its empty state', async ({ page }) => {
    await openUnityCupCockpit(page);

    const section = page.getByRole('region', { name: 'Scenario' });

    await expect(section.getByText(/Aim for at least 3 circles/)).toBeVisible();
    await expect(section.getByText(/as a margin, not a win condition/)).toBeVisible();
    await expect(section.getByText(/No team races recorded/)).toBeVisible();

    // The calendar flag's renderer points at the column that draws it rather than the fallback
    // E1 showed before E3 registered.
    await expect(section.getByText(/The race calendar renders in this screen's Races in this run column/)).toBeVisible();
    await expect(section.getByText(/cannot draw yet/)).toHaveCount(0);
});

test('reaches the panel by keyboard without adding a control or trapping focus', async ({ page }) => {
    await openUnityCupCockpit(page);

    const section = page.getByRole('region', { name: 'Scenario' });

    // Nothing is pointer-gated: the roster's empty reading is announced through its own status role,
    // with no click and no hover. This fails if the panel ever hides a reading behind disclosure.
    await expect(section.getByText(/No teammate burst states recorded\./)).toBeVisible();

    // The panel adds no focusable element of its own, so there is no control inside it that a
    // keyboard path could step over. This assertion fails the day a form control lands in a panel.
    await expect(section.locator('a, button, input, select, textarea, [tabindex]')).toHaveCount(0);

    // And focus passes the region on its way down the page rather than stopping in it: take the last
    // control above the region, Tab once, and require focus to have landed outside the region.
    const controls = page.locator('main a, main button');
    const count = await controls.count();
    const region = await section.boundingBox();
    expect(region, 'the scenario region has no box').not.toBeNull();

    let above = -1;

    for (let i = 0; i < count; i++) {
        const box = await controls.nth(i).boundingBox();

        if (box !== null && region !== null && box.y + box.height <= region.y) {
            above = i;
        }
    }

    expect(above, 'no control sits above the scenario region').toBeGreaterThanOrEqual(0);

    await controls.nth(above).focus();
    await page.keyboard.press('Tab');

    const active = await page.evaluate(() => {
        const el = document.activeElement;

        if (el === null) {
            return null;
        }

        return {
            tag: el.tagName,
            insideRegion: el.closest('section[aria-labelledby="career-scenario-heading"]') !== null,
        };
    });

    expect(active, 'focus left the document after one Tab').not.toBeNull();
    expect(active?.insideRegion, 'focus stopped inside a region that renders no controls').toBe(false);

    // The panel's copy stayed on screen while focus moved past it.
    await expect(section.getByText(/No teammate burst states recorded\./)).toBeVisible();
});

test('sizes the panel controls to the 44px contract and reflows at 320 px', async ({ page }) => {
    await openUnityCupCockpit(page);

    // The same page-wide sweep E1 runs, now through the shared helper: it skips what a collapsed
    // disclosure hides and asserts it measured something. 10 is well under the ~17 controls this page
    // carries.
    await expectTapTargets(page.locator('main a, main button'), 10);

    // WCAG 1.4.10: both columns stack at 320 and nothing scrolls sideways.
    await page.setViewportSize({ width: 320, height: 900 });
    const section = page.getByRole('region', { name: 'Scenario' });
    await expect(section.getByText(/Rank not recorded/)).toBeVisible();
    await expect(section.getByText(/No team races recorded/)).toBeVisible();
    const overflow = await page.evaluate(
        () => document.documentElement.scrollWidth - document.documentElement.clientWidth,
    );
    expect(overflow, 'the cockpit scrolls sideways at 320px').toBeLessThanOrEqual(1);
});

test('passes the axe A + AA scan with the Team Cockpit mounted', async ({ page }) => {
    await openUnityCupCockpit(page);

    const results = await buildAxe(page).analyze();

    expect(results.violations, JSON.stringify(results.violations, null, 2)).toEqual([]);
});
