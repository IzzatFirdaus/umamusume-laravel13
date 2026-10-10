import { test, expect } from '@playwright/test';
import { buildAxe } from '../utils/accessibility';
import { deleteRun } from '../utils/delete-run';
import { recordTurns } from '../utils/record-turns';

/*
 * Rendered-copy, keyboard and accessibility evidence for the Skills Planner (plan §8 D13,
 * `SCR-CAR-016`). `CareerSkillsPlannerTest` and `SkillSpendCoverageTest` assert the resolved props -
 * the four states and their precedence, the coverage figures, the fit cells, the reorder write, and
 * that no hint ladder survives. These cases assert what only a browser reaches: the warning block a
 * sighted Trainer reads, the stated hint-discount absence, the keyboard path through the reorder and
 * its live announcement, the 44px sweep, and an axe scan.
 *
 * Fixture strategy. The run is created through the create form (the app's supported path in) and
 * deleted over HTTP in `afterEach` (`tests/utils/delete-run.ts`).
 * The build target is set with a direct PUT because no screen edits a saved run's target yet - the
 * planner's own gap 1 - using the page's XSRF cookie and the URL-encoded form body
 * `StoreBuildTargetRequest` parses. The priority names are seeded catalogue rows (skill ids 1 and 2,
 * 180 SP each at the time of writing); if a reseed moves them, the warning case fails visibly rather
 * than passing vacuously, because an unpriced priority refuses the total.
 *
 * The write gets 60s rather than the config's 15s, for the reason `career-race-decision.spec.ts`
 * records: `php artisan serve` is a single-process `php -S`, so a write queues behind whatever else
 * is on the box.
 */

const WRITE = { timeout: 60_000 } as const;

const createdRunUrls: string[] = [];

/** Two seeded catalogue rows, 180 SP base each: 360 together, against the 100 SP the fixture logs. */
const PRIORITIES = ['Gourmand', 'Unstoppable'];

test.afterEach(async ({ page }) => {
    for (const url of createdRunUrls.splice(0)) {
        await deleteRun(page, url);
    }
});

async function openPlanner(
    page: import('@playwright/test').Page,
    priorities: string[] = PRIORITIES,
): Promise<void> {
    await page.goto('/training-runs/create', { waitUntil: 'domcontentloaded' });
    await page.locator('#app > *').first().waitFor();
    await page.locator('#trainee-combobox').fill('Agnes Digital');
    await page.keyboard.press('Enter');
    await page.selectOption('select[name="scenario"]', { value: 'unity_cup' });
    await page.getByRole('button', { name: 'Create run' }).click();
    await page.waitForURL(/\/training-runs\/\d+\/cockpit$/, WRITE);
    const runUrl = page.url().replace(/\/cockpit$/, '');
    createdRunUrls.push(runUrl);

    const runId = runUrl.match(/\/training-runs\/(\d+)/)?.[1] ?? '';

    // The build target, straight to the run-scoped write its Form Request owns. `back()` from
    // there falls back to the run screen, so the redirect target is the run's own URL and the
    // response that matters is the props the planner renders next.
    // A target cannot be written with an empty priority list (`StoreBuildTargetRequest:72` demands
    // `present, array`, and form encoding has no way to send an empty one), so the no-priorities
    // fixture is the other half of the same state: a run with no build target at all. `requiredRows()`
    // reads the priorities off that target either way, and both paths reach `no_priorities`.
    if (priorities.length === 0) {
        await page.goto(`${runUrl}/skills`, { waitUntil: 'domcontentloaded' });
        await page.locator('#app > *').first().waitFor();

        return;
    }

    const cookie = (await page.context().cookies()).find((c) => c.name === 'XSRF-TOKEN');
    const body = new URLSearchParams({
        purpose: 'StoryClear',
        distance: 'Medium',
        surface: 'Turf',
        style: 'Pace Chaser',
        'targets[Speed]': '900',
        'targets[Stamina]': '800',
        'targets[Power]': '700',
        'targets[Guts]': '600',
        'targets[Wit]': '500',
    });
    for (const name of priorities) {
        body.append('skill_priorities[]', name);
    }

    // The write is asserted at its own status, not at wherever `back()` sends the follow-up: a
    // no-Referer PUT has no header for Laravel's `previous()` to prefer, so the redirect goes to
    // the session's last full-page GET, which this flow never guarantees. The data is proven by
    // the planner's own render below.
    const saved = await page.request.put(`${runUrl}/build-target`, {
        headers: {
            'content-type': 'application/x-www-form-urlencoded; charset=UTF-8',
            ...(cookie ? { 'X-XSRF-TOKEN': decodeURIComponent(cookie.value) } : {}),
        },
        data: body.toString(),
        maxRedirects: 0,
    });
    expect(
        saved.status(),
        `the fixture build-target PUT failed: ${saved.status()} ${saved.headers()['location'] ?? ''}`,
    ).toBe(302);

    // One turn logged at 100 SP, so the coverage warning has a recorded total to miss. No rendered
    // surface creates a turn since the run screen became a redirect, so it goes over `runs.turns.store`.
    await recordTurns(page, runId, 1, { sp: '100' });

    await page.goto(`${runUrl}/skills`, { waitUntil: 'domcontentloaded' });
    await page.locator('#app > *').first().waitFor();
}

test('warns when SP cannot cover the skills still to learn, and states the discounts it does not model', async ({ page }) => {
    await openPlanner(page);

    // The warning is glyph plus text, and the text carries both numbers: the base-price sum and
    // the recorded total.
    const warn = page.getByRole('status').filter({ hasText: 'The skills still to learn cost 360 SP' });
    await expect(warn).toBeVisible();
    await expect(warn).toContainText('the run holds 100');

    // The hint-discount ladder was removed (A5): the screen states the absence rather than pricing
    // a percentage no stored column backs, so no `Hint Lvl` string renders anywhere.
    await expect(page.getByText('Hint discounts are not modelled.')).toBeVisible();
    await expect(page.getByText(/Hint Lvl/)).toHaveCount(0);

    // The fit cells render their three states as words, not colour.
    await expect(page.getByText('Not recorded').first()).toBeVisible();
    await expect(page.getByRole('heading', { name: /Required/ })).toBeVisible();
});

test('states that nothing is prioritized instead of pricing an empty list', async ({ page }) => {
    // R2-10. With no priorities the panel used to print the zero an empty sum happens to add up to,
    // "The skills still to learn cost 0 SP, and the run holds 173, leaving 173", which a Trainer could
    // only read as a finished plan. The target is written with an empty priority list, so the run has
    // a target and no priorities: the honest state names the absence and shows no figure.
    await openPlanner(page, []);

    await expect(
        page.getByText('This run has no build target, so no skill is Required and there is nothing to sum.'),
    ).toBeVisible();

    // No number of this shape renders at all, in either of its two sentences.
    await expect(page.getByText(/still to learn cost 0 SP/)).toHaveCount(0);
    await expect(page.getByRole('status').filter({ hasText: 'still to learn cost' })).toHaveCount(0);

    // The run's recorded Skill Point total is a reading, not a coverage result, so it still prints.
    await expect(page.getByText('Skill Point coverage')).toBeVisible();
});

test('reorders the priority list from the keyboard, announces the new position, and saves it', async ({ page }) => {
    await openPlanner(page);

    const requiredRows = page.locator('ol').filter({ has: page.getByRole('button', { name: /Move / }) }).first();
    const first = requiredRows.getByRole('button', { name: `Move ${PRIORITIES[0]} down, position 1 of 2` });
    await first.focus();
    await expect(first).toBeFocused();

    await page.keyboard.press('Enter');
    await expect(page.getByRole('status').filter({ hasText: 'moved to position 2 of 2' })).toBeVisible();

    // Focus travels with the moved row, because the list moves the keyed DOM node rather than
    // rebuilding it.
    await expect(requiredRows.getByRole('button', { name: `Move ${PRIORITIES[0]} up, position 2 of 2` })).toBeFocused();

    // The saved order comes back from the server: after the save the rows render in the new order.
    await page.getByRole('button', { name: 'Save order' }).click();
    await page.waitForURL(/\/skills$/, WRITE);
    await expect(page.getByText('Build target saved.')).toBeVisible(WRITE);
    await page.reload({ waitUntil: 'domcontentloaded' });
    await page.locator('#app > *').first().waitFor();
    await expect(requiredRows.locator('li').first()).toContainText(PRIORITIES[1]);
    await expect(requiredRows.locator('li').first()).not.toContainText(PRIORITIES[0]);
});

test('sizes the skills planner controls to the 44px contract', async ({ page }) => {
    await openPlanner(page);

    for (const control of [
        page.getByRole('button', { name: `Move ${PRIORITIES[0]} down, position 1 of 2` }),
        page.getByRole('button', { name: 'Save order' }),
    ]) {
        const box = await control.boundingBox();
        expect(box?.height ?? 0, 'a skills planner control is not sized to the 44px contract')
            .toBeGreaterThanOrEqual(44);
    }
});

test('passes an axe scan at WCAG A and AA', async ({ page }) => {
    await openPlanner(page);

    // `buildAxe` scopes to `#app`, which is what makes this deterministic (KI-63).
    const results = await buildAxe(page).analyze();

    expect(results.violations, JSON.stringify(results.violations, null, 2)).toEqual([]);
});
