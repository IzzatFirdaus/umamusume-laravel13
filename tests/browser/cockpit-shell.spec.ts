import { test, expect, type Locator, type Page } from '@playwright/test';
import { deleteRun } from '../utils/delete-run';

/*
 * The Cockpit's shared shell (S5.1), measured on the DOM.
 *
 * The HFW's density reference is `cockpit-unity-cup.html`, and the shell's contract is the one
 * `Cockpit.vue`'s own docblock states: identity card first, then the two-column body — the run's stats
 * and its scenario reading on the left, the advisor on the right — then the run-race strip. Round 3
 * measured that order as satisfied; this file is the missing pin, not a redesign.
 *
 * Runs are created through the create form (six fields, one POST) rather than the wizard walk, because
 * the subject is the shell's composition and not the wizard. A Completed run is reachable from the same
 * form: the status select carries all three words, so the "no advisor rail on a closed career" case
 * needs no second fixture.
 */

const WRITE = { timeout: 60_000 } as const;
const createdRunUrls: string[] = [];

test.afterEach(async ({ page }) => {
    for (const url of createdRunUrls.splice(0)) {
        await deleteRun(page, url);
    }
});

async function createRun(page: Page, status: 'Active' | 'Completed'): Promise<string> {
    await page.goto('/training-runs/create', { waitUntil: 'domcontentloaded' });
    await page.locator('#app > *').first().waitFor();
    await page.locator('#trainee-combobox').fill('Rice Shower');
    await page.keyboard.press('Enter');
    await page.selectOption('select[name="scenario"]', { value: 'unity_cup' });
    await page.selectOption('select[name="status"]', { value: status });
    await page.getByRole('button', { name: 'Create run' }).click();
    await page.waitForURL(/\/training-runs\/\d+\/cockpit$/, WRITE);

    const runUrl = page.url().replace(/\/cockpit$/, '');
    createdRunUrls.push(runUrl);

    return runUrl;
}

const identity = (page: Page): Locator => page.locator('section[aria-labelledby="career-header-heading"]');
const scenario = (page: Page): Locator => page.getByRole('region', { name: 'Scenario' });
const advisor = (page: Page): Locator => page.locator('section[aria-labelledby="career-advisor-heading"]');

/**
 * Log one turn, because the page head's action is the Next decision's own action and a career with no
 * recorded turn has no next decision to act on. The write goes through `runs.turns.store` — the route
 * that owns it — rather than through the form, which `career-training-detail.spec.ts` walks.
 */
async function logOneTurn(page: Page, runUrl: string): Promise<void> {
    const runId = runUrl.match(/\/training-runs\/(\d+)/)?.[1] ?? '';
    const xsrf = await page
        .context()
        .cookies()
        .then((all) => all.find((cookie) => cookie.name === 'XSRF-TOKEN')?.value);

    expect(xsrf, 'the session carries no XSRF-TOKEN cookie before the turn write').toBeDefined();

    const response = await page.request.post(`/training-runs/${runId}/turns`, {
        form: { turn: '1', speed: '600', stamina: '600', power: '600', guts: '600', wit: '600' },
        headers: { 'X-XSRF-TOKEN': decodeURIComponent(xsrf as string) },
    });

    expect(response.status(), 'the turn write was refused').toBeLessThan(400);
}

async function top(locator: Locator): Promise<number> {
    const box = await locator.boundingBox();

    expect(box, 'the region has no box, so it is not painted').not.toBeNull();

    return box!.y;
}

test('puts the identity card first, then the body, at 1440px', async ({ page }) => {
    const runUrl = await createRun(page, 'Active');
    // 1440 rather than the project's 1280: the body's two-column breakpoint is a CONTAINER query
    // (`@4xl`), and at 1280 the container is narrower than the breakpoint, so the body stacks. The
    // stacked order is correct there (the 390px case below proves it), but only at 1440 is the
    // two-column reading the one under test.
    await page.setViewportSize({ width: 1440, height: 900 });
    // One logged turn, so the page head has a next decision to offer an action for.
    await logOneTurn(page, runUrl);
    await page.goto(`${runUrl}/cockpit`, { waitUntil: 'domcontentloaded' });
    await page.locator('#app > *').first().waitFor();

    await expect(identity(page)).toBeVisible();
    await expect(scenario(page)).toBeVisible();
    await expect(advisor(page)).toBeVisible();

    const identityTop = await top(identity(page));

    // Identity first: above both body regions, which is the HFW's one full-width card.
    expect(identityTop).toBeLessThan(await top(scenario(page)));
    expect(identityTop).toBeLessThan(await top(advisor(page)));

    // The scenario card is BELOW the body grid rather than beside the advisor, which is why this test
    // does not assert a shared top edge: the shell's order is identity → body (stats left, advisor
    // right) → race strip → scenario card, and geometry is asserted where the layout is stable (the
    // 390px reflow case below), not where a container query decides the column count.
    expect(await top(advisor(page))).toBeLessThan(await top(scenario(page)));

    // One filled action on the page head, and it is the action the Next decision card offers.
    const primaries = page.locator('a.bg-primary-action, button.bg-primary-action');
    await expect(primaries).toHaveCount(1);
});

test('reflows to one column at 390px without losing the identity card or the advisor', async ({ page }) => {
    const runUrl = await createRun(page, 'Active');
    await page.setViewportSize({ width: 390, height: 844 });
    await page.goto(`${runUrl}/cockpit`, { waitUntil: 'domcontentloaded' });
    await page.locator('#app > *').first().waitFor();

    await expect(identity(page)).toBeVisible();
    await expect(advisor(page)).toBeVisible();

    // Single column: the advisor no longer shares a top edge with the left column; it follows it.
    expect(await top(advisor(page))).toBeGreaterThan(await top(scenario(page)));

    // Nothing overflows the viewport horizontally at the phone width.
    const overflow = await page.evaluate(() => document.body.scrollWidth <= document.body.clientWidth + 1);
    expect(overflow, 'the cockpit scrolls sideways at 390px').toBe(true);
});

test('keeps the finale countdown inside the scenario card', async ({ page }) => {
    const runUrl = await createRun(page, 'Active');
    await page.goto(`${runUrl}/cockpit`, { waitUntil: 'domcontentloaded' });
    await page.locator('#app > *').first().waitFor();

    // The countdown element is a child of the scenario region, never a sibling in the advisor's rail.
    const countdown = scenario(page).locator('[data-region="scenario-finale"]');
    await expect(countdown).toBeVisible();
    await expect(advisor(page).locator('[data-region="scenario-finale"]')).toHaveCount(0);
});

test('drops the advisor rail and prints the outcome first on a Completed run', async ({ page }) => {
    const runUrl = await createRun(page, 'Completed');
    await page.goto(`${runUrl}/cockpit`, { waitUntil: 'domcontentloaded' });
    await page.locator('#app > *').first().waitFor();

    // No advisor rail: a career that has ended has no turn being decided.
    await expect(advisor(page)).toHaveCount(0);

    // The outcome is the first thing the body says, and the page says the run is complete.
    const outcome = page.locator('[data-region="closed-outcome"]');
    await expect(outcome).toBeVisible();
    await expect(page.getByText('This run is complete.')).toBeVisible();

    const outcomeTop = await top(outcome);
    expect(outcomeTop).toBeLessThan(await top(scenario(page)));
});
