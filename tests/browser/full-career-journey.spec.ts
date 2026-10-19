import { test, expect } from '@playwright/test';
import { DatabaseSync } from 'node:sqlite';
import path from 'node:path';
import { deleteRun } from '../utils/delete-run';
import { openIdentityDisclosure } from '../utils/identity-disclosure';
import { walkWizard } from '../utils/start-career';

/*
 * The complete new-career journey (Slice 6.1): one deterministic walk from the wizard's first step
 * to a filed Veteran, asserting at the two ends that the run exists only from Preflight on. The
 * steps are the app's own surfaces in their own order - Scenario, Trainee, Target, Legacy, Deck,
 * Preflight, then the Cockpit's identity and team editors, the guided turn on Training Detail, the
 * run-scoped deck, both planners, the status finish and the Save Veteran door.
 *
 * Run counting is one mechanism, fixed here: the scratch database is read directly with `node:sqlite`
 * (Node 22+'s own client; `better-sqlite3` is not a dependency of this package and none is added for
 * a test). The count is asserted as a delta against the file's state at the walk's start, which is
 * the same assertion as "zero runs before Preflight and exactly one after" on the clean scratch
 * database the session-private pair seeds, without making the spec wrong if a crashed spec left a
 * row behind. WAL lets this reader run beside the server the suite started.
 *
 * Every reload assertion reads a value back from storage, so the walk proves persistence and not
 * merely that the write returned 200.
 */

const WRITE = { timeout: 60_000 } as const;

const createdRunUrls: string[] = [];

test.afterEach(async ({ page }) => {
    for (const url of createdRunUrls.splice(0)) {
        await deleteRun(page, url);
    }
});

function scratchRunCount(): number {
    const file = process.env.PLAYWRIGHT_SCRATCH_DB;

    if (file === undefined || file === '') {
        throw new Error(
            'full-career-journey reads the scratch database directly: run it with PLAYWRIGHT_PORT and '
            + 'PLAYWRIGHT_SCRATCH_DB set to the session-private pair (KI-73, KI-80).',
        );
    }

    const db = new DatabaseSync(path.resolve(file));

    try {
        const row = db.prepare('SELECT COUNT(*) AS n FROM training_runs').get();

        return Number((row as { n: number }).n);
    } finally {
        db.close();
    }
}

/**
 * The wizard's first five steps, ending at Preflight, without starting the career.
 *
 * The shared walk (slice 4.1), plus the two assertions only this journey makes: the ancestry step is
 * visited and named, and the trainee's own name comes back so the later screens can be checked against
 * it. `onLegacy` is the additive hook the helper grew for exactly this — the alternative was a second
 * private copy of the walk, which is what four specs carried and what had gone stale.
 */
async function walkToPreflight(page: import('@playwright/test').Page): Promise<string> {
    return walkWizard(page, 'unity_cup', {
        onLegacy: async (step) => {
            await expect(step.getByRole('heading', { name: 'Legacy', level: 1 })).toBeVisible();
            await expect(step.getByText('Step 4 of 6').first()).toBeVisible();
        },
    });
}

test('walks the whole career from the wizard to a filed Veteran', async ({ page }) => {
    // The walk is the whole product surface in one pass; the 180s default is a per-screen budget,
    // not a whole-journey one.
    test.setTimeout(300_000);

    const baseCount = scratchRunCount();

    const traineeName = await walkToPreflight(page);

    // The wizard's own contract, held at its end: no run row exists before Preflight.
    expect(scratchRunCount()).toBe(baseCount);

    await page.goto('/career/setup/preflight', { waitUntil: 'domcontentloaded' });
    await page.locator('#app > *').first().waitFor();
    await page.getByRole('button', { name: 'Start career' }).click();
    await page.waitForURL(/\/cockpit$/, { waitUntil: 'domcontentloaded' });

    // And Preflight is the run's birth: exactly one row more than the walk started with.
    expect(scratchRunCount()).toBe(baseCount + 1);

    const runUrl = page.url().replace(/\/cockpit$/, '');
    createdRunUrls.push(runUrl);

    // --- Identity and Unity Cup state, through the Cockpit's own editors (Slices 1.5 and 1.6) ---
    await openIdentityDisclosure(page);
    await page.selectOption('#trainee_rarity', '3');
    await page.locator('#potential_level').fill('2');
    // One growth figure is a legal sparse row: a stat with no growth is simply absent.
    await page.locator('input[name="growth_rate[Speed]"]').fill('10');
    await page.getByRole('button', { name: 'Save identity' }).click();
    await expect(page.getByText('Run updated.')).toBeVisible(WRITE);
    await expect(
        page.locator('section[aria-labelledby="career-header-heading"]').getByText('★3 · Potential 2'),
    ).toBeVisible();

    const teamEditor = page.locator('details', { has: page.getByText('Edit team state') });
    // The panel is `open` while the career is active — Cockpit.vue sets `:open="isActive"`, because the
    // panel the Active screen has always shown stays shown and only closes once the career has ended.
    // The journey used to click the summary first, which TOGGLED IT CLOSED and left the input hidden, so
    // the fill sat at its 300s budget waiting for an element that could never become visible. Asserting
    // the contract instead of toggling it is what makes the next line possible.
    await expect(teamEditor).toHaveAttribute('open', '');
    await teamEditor.locator('input[name="team_name"]').fill('Blue Bloom');
    await teamEditor.locator('input[name="spirit_bursts_count"]').fill('6');
    await teamEditor.getByRole('button', { name: 'Save team state' }).click();
    await expect(page.getByText('Run updated.')).toBeVisible(WRITE);
    await expect(page.getByText('Blue Bloom')).toBeVisible();

    // Reload: the identity and the team state are read back from storage, not from the form.
    await page.reload({ waitUntil: 'domcontentloaded' });
    await page.locator('#app > *').first().waitFor();
    await expect(
        page.locator('section[aria-labelledby="career-header-heading"]').getByText('★3 · Potential 2'),
    ).toBeVisible();
    await expect(page.getByText('Blue Bloom')).toBeVisible();

    // --- One guided turn, recorded on Training Detail (Slice 1.4's surface) ---
    await page.goto(`${runUrl}/training`, { waitUntil: 'domcontentloaded' });
    await page.locator('#app > *').first().waitFor();
    await page.getByRole('article').filter({ hasText: /^Speed/ }).getByRole('button', { name: 'Train' }).click();
    await page.locator('input[name="speed"]').fill('650');
    await page.locator('input[name="stamina"]').fill('600');
    await page.locator('input[name="power"]').fill('600');
    await page.locator('input[name="guts"]').fill('600');
    await page.locator('input[name="wit"]').fill('600');
    await page.locator('input[name="energy"]').fill('62');
    await page.locator('select[name="outcome"]').selectOption('Success');
    await page.getByRole('button', { name: 'Record this training' }).click();
    await page.waitForURL(/\/cockpit$/, { waitUntil: 'domcontentloaded' });
    await expect(page.getByText('Turn 1 logged.')).toBeVisible();

    // Reload: the recorded turn is in the correction selector, which is the Cockpit's own read-back.
    // The selector renders one option per logged turn and no placeholder (the two-turn case in
    // `career-cockpit-forms.spec.ts` pins that shape), so one turn is one option.
    await page.reload({ waitUntil: 'domcontentloaded' });
    await page.locator('#app > *').first().waitFor();
    await expect(page.locator('#correction-turn-select').locator('option')).toHaveCount(1);
    await expect(page.locator('#correction-turn-select').locator('option')).toHaveText('Turn 1');

    // --- The run-scoped deck: six slots, each with its ownership toggles (R2-06's semantics) ---
    await page.getByRole('link', { name: "Edit this run's deck" }).click();
    await page.waitForURL(/\/deck$/, { waitUntil: 'domcontentloaded' });
    await page.locator('#app > *').first().waitFor();

    for (let position = 1; position <= 6; position++) {
        await expect(page.locator(`#deck-slot-${position}`)).toBeVisible();
    }

    await expect(page.getByRole('button', { name: 'Owned', exact: true })).toHaveCount(6);
    await expect(page.getByRole('button', { name: 'Rented', exact: true })).toHaveCount(6);

    // --- The two planners render for this run ---
    await page.goto(`${runUrl}/races/planner`, { waitUntil: 'domcontentloaded' });
    await page.locator('#app > *').first().waitFor();
    await expect(page.getByRole('heading', { name: 'Race planner' })).toBeVisible();

    await page.goto(`${runUrl}/skills`, { waitUntil: 'domcontentloaded' });
    await page.locator('#app > *').first().waitFor();
    await expect(page.getByText('Skill Point coverage')).toBeVisible();

    // --- Finish through the status flow, then file the Veteran from the Result screen ---
    // The status form is the Cockpit's; the walk is on the Skills Planner after the planners' visit.
    await page.goto(`${runUrl}/cockpit`, { waitUntil: 'domcontentloaded' });
    await page.locator('#app > *').first().waitFor();
    await openIdentityDisclosure(page);
    const statusForm = page.locator('form').filter({ has: page.getByRole('button', { name: 'Change status' }) });
    await statusForm.locator('select[name="status"]').selectOption('Completed');
    await statusForm.getByRole('button', { name: 'Change status' }).click();
    await expect(page.getByText('Run updated.')).toBeVisible(WRITE);

    await page.goto(`${runUrl}/result`, { waitUntil: 'domcontentloaded' });
    await page.locator('#app > *').first().waitFor();
    const next = page.getByRole('region', { name: 'What you can do next' });
    await next.getByRole('link', { name: 'Save Veteran' }).click();
    await page.waitForURL(/\/veteran$/, { waitUntil: 'domcontentloaded' });
    await page.locator('#app > *').first().waitFor();

    await page.locator('#veteran-notes').fill('The whole journey, end to end.');
    await page.getByRole('button', { name: 'Save Veteran' }).click();
    await page.waitForURL(/\/veterans\/\d+$/, WRITE);
    await expect(page.getByText(/Veteran saved:/)).toBeVisible();
    await expect(page.getByRole('heading', { name: traineeName })).toBeVisible();

    // Reload: the filed record is read back from storage.
    await page.reload({ waitUntil: 'domcontentloaded' });
    await page.locator('#app > *').first().waitFor();
    await expect(page.getByRole('heading', { name: traineeName })).toBeVisible();

    // And the library lists it: `/veterans` is the library the nav names, and the walk's filed
    // record is one of its rows. The row is addressed as the link whose accessible name carries
    // the trainee, never as a bare text match - the Lab's own picker carries the same name inside
    // a hidden option, and `.first()` over `getByText` would read that one and fail.
    await page.goto('/veterans', { waitUntil: 'domcontentloaded' });
    await page.locator('#app > *').first().waitFor();
    await expect(page.getByRole('link', { name: traineeName })).toBeVisible();
});
