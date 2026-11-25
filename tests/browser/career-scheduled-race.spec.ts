import { test, expect } from './support/base-test';
import { deleteRun } from '../utils/delete-run';
import { recordTurns } from '../utils/record-turns';
import { removeScheduledRace, seedScheduledRace } from '../utils/scheduled-races';

/*
 * KI-173's rendered contract for a run whose only race record is `Scheduled`.
 *
 * `Scheduled` observes a calendar slot without deciding to enter it, so every surface that asks
 * "has the Trainer decided on this race?" must treat the run as having decided nothing
 * (`RaceEntryStatus::recordsEntry()`). `tests/Feature/ScheduledRaceStatusTest.php` asserts the
 * resolved props; these cases assert what only a browser reaches, and each names the misrender a
 * regression would print:
 *
 *   - a planner row greyed or dropped would be the pre-KI-173 defect the planner's own reader
 *     records: the race the slot carries stays in the Mandatory group, and `race.status` arrives
 *     null for a `Scheduled` row, so the row shows nothing recorded either way;
 *   - a row gone from "Still to come" would be the same defect on the strip, whose own comment
 *     records it ("a `Scheduled` row … must keep the race in the still-to-come list rather than
 *     swallow it");
 *   - a `met` pill on the advisor's Obligations table, or `Recorded` on the goal ladder's
 *     Mandatory races group, would claim an obligation settled by a row that decides nothing;
 *   - the strip's recorded region may print the row — it reports what the run holds — but only
 *     under the state's own label, `Scheduled (entry unrecorded)` (`lang/en/uma.php`), and with
 *     no placement, because none was recorded.
 *
 * Fixture strategy follows `career-race-strip.spec.ts`: a run created through the record form,
 * its turns logged over `runs.turns.store`, the `Scheduled` row written into the per-invocation
 * scratch database, and the run deleted over HTTP in `afterEach`. Turn 13 sits after the debut
 * (turn 12), so the decision surface is turn 13 and the seeded slot at turn 13 is both the turn
 * being decided and ahead of nothing — the shape that would let a misrender read as a completed
 * current obligation rather than as one still owed.
 *
 * `RaceCard.vue`'s "Recorded:" line is not asserted here: no page mounts `RaceCard` at this HEAD
 * (the planner renders its groups as tables, and `RacePlanList`, the card's only host, lost its
 * `<RacePlanList>` tag in `602bba4e` while keeping the import), so the line is unreachable from
 * the product UI. The card keeps its null-safe branch for the day the list is re-hosted.
 */

const WRITE = { timeout: 60_000 } as const;

const createdRunUrls: string[] = [];

test.afterEach(async ({ page }) => {
    for (const url of createdRunUrls.splice(0)) {
        await deleteRun(page, url);
    }
});

/** A Unity Cup career with the run's turns logged, at `$turns` logged turns, returning the run's record URL. */
async function newRun(page: import('@playwright/test').Page, turns: number): Promise<{ url: string; id: number }> {
    await page.goto('/training-runs/create', { waitUntil: 'domcontentloaded' });
    await page.locator('#app > *').first().waitFor();
    await page.locator('#trainee-combobox').fill('Agnes Digital');
    await page.keyboard.press('Enter');
    await page.selectOption('select[name="scenario"]', { value: 'unity_cup' });
    await page.getByRole('button', { name: 'Create run' }).click();
    await page.waitForURL(/\/training-runs\/\d+\/cockpit$/, WRITE);

    const url = page.url().replace(/\/cockpit$/, '');
    createdRunUrls.push(url);

    const id = Number(url.match(/\/training-runs\/(\d+)/)?.[1] ?? '');

    if (turns > 0) {
        await recordTurns(page, url.match(/\/training-runs\/(\d+)/)?.[1] ?? '', turns);
    }

    return { url, id };
}

test('a scheduled-only record keeps the race visible and undecided on every reader', async ({ page }) => {
    const run = await newRun(page, 12);

    // Turn 13: the debut (turn 12) is behind the decision, and the seeded slot sits at the turn
    // being decided — the least ambiguous place for the misrenders named above.
    const seeded = seedScheduledRace(run.id, 13, 'Fixture Scheduled Stakes');

    try {
        // 1. Race Planner: the race stays in the calendar's own groups — a `Scheduled` row must not
        // grey it out of the Mandatory group or drop it entirely (the KI-173 planner defect) — and
        // the row prints no placement, because none was recorded. The planner's tables carry no
        // recorded-status column, so "no placement" is the visible face of `status: null`.
        await page.goto(`${run.url}/races/planner`, { waitUntil: 'domcontentloaded' });
        await page.locator('#app > *').first().waitFor();

        const mandatoryGroup = page.getByRole('region', { name: /Mandatory/ });
        const plannerRow = mandatoryGroup.locator('tr').filter({ hasText: 'Fixture Scheduled Stakes' });
        await expect(plannerRow, 'a scheduled-only race dropped from the planner').toHaveCount(1);
        await expect(
            plannerRow.getByText(/1st|2nd|3rd|\d+th/),
            'the planner printed a placement for a scheduled-only record',
        ).toHaveCount(0);

        // 2. Cockpit race strip. The seeded slot sits at the turn being decided, so the strip reads
        // it in the "This turn" region — the region that reports what the run holds against this
        // turn and therefore may print the row — under the state's own label and with no placement,
        // because none was recorded. The misrender the pass records ("a `Scheduled` row … must keep
        // the race in the still-to-come list rather than swallow it") is a race *behind* the
        // decision; the label and absence-of-placement here are the same reader contract, and the
        // still-to-come list stays intact around it.
        await page.goto(`${run.url}/cockpit`, { waitUntil: 'domcontentloaded' });
        await page.locator('#app > *').first().waitFor();

        const recorded = page.getByRole('region', { name: 'Recorded in this run' });
        const row = recorded.locator('li').filter({ hasText: 'Fixture Scheduled Stakes' });
        await expect(row).toHaveCount(1);
        await expect(row.getByText('Scheduled (entry unrecorded)'), 'the scheduled row printed under a decided status').toBeVisible();
        await expect(row.getByText(/1st|2nd|3rd|\d+th/), 'a placement printed where none was recorded').toHaveCount(0);

        const thisTurn = page.getByRole('region', { name: /This turn/ });
        await expect(thisTurn.getByText('Fixture Scheduled Stakes'), 'the turn being decided stopped listing the scheduled race').toBeVisible();

        // The ahead list is the obligations after the decision turn; the seeded row must not have
        // emptied it (the pre-KI-173 failure emptied exactly this list).
        await expect(page.getByRole('region', { name: 'Still to come' }).getByText('! Mandatory', { exact: true }).first()).toBeVisible();

        // 3. Cockpit advisor: the Obligations table does not call the obligation met. The seeded
        // slot is the shared calendar row at the turn being decided, so the pill it must not print
        // is `met` — the word `NextDecision.vue`'s state map gives a completed row.
        const obligations = page.getByRole('region', { name: 'Obligations, scrollable' });
        const obligationRow = obligations.locator('tr').filter({ hasText: 'Fixture Scheduled Stakes' });
        await expect(obligationRow).toHaveCount(1);
        await expect(
            obligationRow.getByText('met', { exact: true }),
            'a scheduled-only record settled the obligation in the advisor',
        ).toHaveCount(0);

        // 4. Scenario panel: the goal ladder's Mandatory races group does not call the rung
        // recorded either. `GoalGroupCard`'s word for `completed` is `Recorded`.
        await page.goto(`${run.url}/scenario`, { waitUntil: 'domcontentloaded' });
        await page.locator('#app > *').first().waitFor();

        const mandatory = page.getByRole('region', { name: 'Mandatory races' });
        const rung = mandatory.locator('li, tr').filter({ hasText: 'Fixture Scheduled Stakes' }).first();
        await expect(rung).toBeVisible();
        await expect(
            rung.getByText('Recorded', { exact: true }),
            'the goal ladder called a scheduled-only record recorded',
        ).toHaveCount(0);

        // No page on the walk threw: the assertions above read text a TypeError in any consumer
        // would have replaced with an error boundary, and the strip's own null-safe reads are the
        // contract the pass recorded.
        await expect(page.locator('#app')).toBeVisible();
    } finally {
        removeScheduledRace(seeded);
    }
});
