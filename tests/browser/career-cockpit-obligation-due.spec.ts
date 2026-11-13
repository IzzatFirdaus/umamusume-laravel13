import { test, expect } from '@playwright/test';
import { startCareer } from '../utils/start-career';
import { recordTurns } from '../utils/record-turns';
import { deleteRun } from '../utils/delete-run';

/*
 * UX-09, residual — a dated finale row must not carry the "undated" tooltip.
 *
 * Authority: `AUDIT-AND-VERIFICATION.md` section `## 04-ux-findings.md`, lines 383-417 of the folded source. The finding was that the
 * finale rows printed `unknown / no date` while the Scenario panel on the same screen printed
 * `5 turns away`. `be983f87` fixed the CELL by teaching `CockpitController::dueLabel()` to borrow the
 * advisor's distance.
 *
 * What it left behind, and what this spec pins: `NextDecision.vue` decided the cell's tooltip from
 * `row.due_turn === null` alone. The finale rows have `due_turn === null` by design — they sit
 * outside the per-turn grid, which the finding itself states — so after the repair the cell printed
 * `5 turns away` and the tooltip still said "The calendar does not date this obligation." A reader
 * who hovers the date is told the opposite of what the date says.
 *
 * Two cases, because the tooltip now has three states and the middle one is the new one:
 *   1. a finale row in the advisor's window  -> dated cell, distance tooltip
 *   2. an obligation outside any advice       -> `no date` cell keeps the undated tooltip
 *
 * The second case guards the `unknown`/`no date` behaviour the ordering instruction said to
 * preserve where context is genuinely unavailable.
 */

const createdRunUrls: string[] = [];

test.afterEach(async ({ page }) => {
    for (const url of createdRunUrls.splice(0)) {
        await deleteRun(page, url);
    }
});

test.describe.configure({ timeout: 420_000 });

/** Advance a run into the finale window, where the advisor reports a distance. */
async function advanceToFinaleWindow(page: import('@playwright/test').Page, runUrl: string): Promise<void> {
    const runId = /\/training-runs\/(\d+)/.exec(runUrl)?.[1];
    expect(runId, `not a training-run URL: ${runUrl}`).toBeDefined();

    // 69 of 72 turns: three inside the finale's last-five-turns window, so `finale_context` exists.
    await recordTurns(page, String(runId), 69);
}

test('a dated finale row does not claim the calendar cannot date it', async ({ page }) => {
    const runUrl = await startCareer(page, 'unity_cup');
    createdRunUrls.push(runUrl);

    await advanceToFinaleWindow(page, runUrl);

    await page.goto(`${runUrl}/cockpit`, { waitUntil: 'domcontentloaded' });
    await page.locator('#app > *').first().waitFor();

    const advisor = page.getByRole('region', { name: 'Advisor' });

    // The finale rows are the shared ones the catalogue stores with `turn = NULL`.
    const finaleRow = advisor.locator('tbody tr').filter({ hasText: 'URA Finals' }).first();
    await expect(finaleRow).toBeVisible();

    const dueCell = finaleRow.locator('td').last();

    // Precondition: the cell IS dated. If this fails the run is not in the advisor's window and the
    // second assertion below would be vacuous rather than wrong.
    await expect(dueCell, 'the finale row was not dated, so this case cannot test the tooltip')
        .toHaveText(/turns? away|This turn|Passed/);

    // The defect: the tooltip contradicted the cell it belongs to.
    await expect(
        dueCell,
        'the cell prints a date while its tooltip says the calendar does not date it',
    ).not.toHaveAttribute('title', /does not date/i);

    // And it still explains WHY the figure is a distance rather than a grid position.
    await expect(dueCell).toHaveAttribute('title', /does not place this obligation on the turn grid/i);
});

test('a genuinely undated obligation keeps the undated tooltip', async ({ page }) => {
    const runUrl = await startCareer(page, 'unity_cup');
    createdRunUrls.push(runUrl);

    // Turn 6 is far outside the finale's window, so the advisor reports no `finale_context` and the
    // row correctly falls back to `no date` — the behaviour UX-09's repair deliberately leaves alone.
    await page.goto(`${runUrl}/cockpit`, { waitUntil: 'domcontentloaded' });
    await page.locator('#app > *').first().waitFor();

    const advisor = page.getByRole('region', { name: 'Advisor' });
    const undated = advisor.locator('tbody tr').filter({ hasText: 'no date' }).first();

    if ((await undated.count()) === 0) {
        test.skip(true, 'this run already carries a finale distance, so no row is undated');

        return;
    }

    await expect(undated.getByText('no date')).toBeVisible();
    await expect(
        undated.getByText('no date'),
        'an undated row lost its reason',
    ).toHaveAttribute('title', /does not date/i);
});

/*
 * Walk-8 F-07 / F-10 (reconciled to R7-22): on a run past an obligation it never played, the
 * Advisor's warn block said the obligation "is due and not recorded" and then dated it "55 turns
 * ago" — a deadline presented as actionable beside a Rest recommendation. The state half landed at
 * `30eb2b4d` (the row reads `not reached`); the sentence half is this case. A passed deadline says
 * so: it is not due, it fell due before this run's position, and no door on the screen can settle
 * it (the wire's own "cannot be satisfied by any action on the screen").
 */
test('says a passed deadline is behind the run, not due (walk-8 F-07/F-10)', async ({ page }) => {
    const runUrl = await startCareer(page, 'unity_cup');
    createdRunUrls.push(runUrl);

    // Turn 67, the position walk 8 stood at: the debut's turn 12 is long behind a run that recorded
    // nothing against it. (At 13 turns the row still resolves `current` — the deadline passed during
    // the run's own standing turn — which is a different state and not this case's subject.)
    const runId = /\/training-runs\/(\d+)/.exec(runUrl)?.[1];
    expect(runId, `not a training-run URL: ${runUrl}`).toBeDefined();
    await recordTurns(page, String(runId), 67);

    await page.goto(`${runUrl}/cockpit`, { waitUntil: 'domcontentloaded' });
    await page.locator('#app > *').first().waitFor();

    const advisor = page.getByRole('region', { name: 'Advisor' });

    // The warn block must not call a passed deadline due, and must not read as an invitation.
    await expect(advisor.getByText(/is due and not recorded/)).toHaveCount(0);
    await expect(advisor.getByText(/fell due .* before the position this run|behind this run|passed before this run/)).toBeVisible();

    // And the row itself keeps the honest state word.
    const debut = advisor.locator('tbody tr').filter({ hasText: 'Junior Make Debut' });
    await expect(debut.getByText('not reached', { exact: true })).toBeVisible();
});
