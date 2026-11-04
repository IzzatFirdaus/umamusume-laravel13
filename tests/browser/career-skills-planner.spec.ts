import { test, expect } from '@playwright/test';
import { buildAxe } from '../utils/accessibility';
import { deleteRun } from '../utils/delete-run';
import { recordTurns } from '../utils/record-turns';

/*
 * Rendered evidence for SCREEN-014, the Skills Planner, re-baselined at the HFW's region model after
 * the 2026-10-28 parity audit: the Skill Point budget bar, the filters, one skills table with a
 * planned-spend consequence, the hint-ladder card and the savebar. The build target, coverage,
 * purchasable list and recorded groups stay in one disclosure so no write surface is lost.
 *
 * Fixture: two seeded catalogue rows (180 SP each) as priorities, and one turn logging 100 SP, so the
 * plan immediately exceeds the budget - the wireframe's own "too expensive" case.
 */

// Host contention: peer sessions run their own browser suites against the same single-process
// `php -S`, so a fixture plus a write can exceed the default budget.
test.describe.configure({ timeout: 240_000 });

const WRITE = { timeout: 60_000 } as const;
const createdRunUrls: string[] = [];
const PRIORITIES = ['Gourmand', 'Unstoppable'];

test.afterEach(async ({ page }) => {
    for (const url of createdRunUrls.splice(0)) {
        await deleteRun(page, url);
    }
});

async function openPlanner(page: import('@playwright/test').Page): Promise<string> {
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
    for (const name of PRIORITIES) {
        body.append('skill_priorities[]', name);
    }

    const saved = await page.request.put(`${runUrl}/build-target`, {
        headers: {
            'content-type': 'application/x-www-form-urlencoded; charset=UTF-8',
            ...(cookie ? { 'X-XSRF-TOKEN': decodeURIComponent(cookie.value) } : {}),
        },
        data: body.toString(),
        maxRedirects: 0,
    });
    expect(saved.status(), 'the fixture build-target PUT failed').toBe(302);

    await recordTurns(page, runId, 1, { sp: '100' });

    await page.goto(`${runUrl}/skills`, { waitUntil: 'domcontentloaded' });
    await page.locator('#app > *').first().waitFor();

    return runUrl;
}

test('renders the HFW region model with the budget bar and one savebar', async ({ page }) => {
    await openPlanner(page);

    await expect(page.getByRole('heading', { name: /^Spend .* Skill Points$/, level: 1 })).toBeVisible();
    await expect(page.getByText('Planned spend', { exact: true })).toBeVisible();
    await expect(page.getByText('Remaining', { exact: true })).toBeVisible();
    await expect(page.getByRole('heading', { name: 'Why the hint ladder is not linear', level: 2 })).toBeVisible();
    await expect(page.locator('.savebar')).toHaveCount(1);
    await expect(page.locator('main table').first()).toBeVisible();
});

test('planning a row moves the spend and remaining figures, and the row says why it is unaffordable', async ({ page }) => {
    await openPlanner(page);

    const checkbox = page.locator('main table').first().locator('tbody input[type="checkbox"]:not([disabled])').first();

    await checkbox.check();

    await expect(page.locator('.savebar')).toContainText('1 skill planned');
    await expect(page.getByRole('button', { name: 'Learn planned skills' }).first()).toBeDisabled();
    await expect(page.getByText('Too expensive')).toBeVisible();

    await page.getByRole('button', { name: 'Clear plan' }).click();

    await expect(page.locator('.savebar')).toContainText('No skill planned');
});

test('the search narrows the table and Clear restores it', async ({ page }) => {
    await openPlanner(page);

    await page.locator('input[name="skill_search"]').fill('zzzz-no-such-skill');
    await expect(page.getByText('No skill matches that search.')).toBeVisible();

    await page.getByRole('button', { name: 'Clear', exact: true }).click();
    await expect(page.getByText('No skill matches that search.')).toHaveCount(0);
    await expect(page.locator('main table').first().locator('tbody tr').first()).toBeVisible();
});

test('prints the five-step hint ladder with its sourced discounts', async ({ page }) => {
    await openPlanner(page);

    const ladder = page.getByRole('heading', { name: 'Why the hint ladder is not linear' }).locator('xpath=ancestor::section[1]');
    const rows = ladder.locator('tbody tr');

    await expect(rows).toHaveCount(5);
    for (const [index, discount] of ['10%', '20%', '30%', '35%', '40%'].entries()) {
        await expect(rows.nth(index)).toContainText(discount);
    }
});

test('keeps the build target and add-skill writers reachable in one disclosure', async ({ page }) => {
    await openPlanner(page);

    const summary = page.getByText('Build target, recorded skills and the add form');

    await expect(summary).toBeVisible();
    await summary.click();
    await expect(page.getByRole('heading', { name: 'Your target', level: 2 })).toBeVisible();
});

test('sizes the skills planner controls to the 44px contract', async ({ page }) => {
    await openPlanner(page);

    const undersized = await page.locator('main a, main button, main select, main summary').evaluateAll((nodes) =>
        nodes.filter((node) => {
            const box = node.getBoundingClientRect();

            return box.width > 0 && box.height > 0 && box.height < 44;
        }).length,
    );

    expect(undersized).toBe(0);
});

test('passes an axe scan at WCAG A and AA', async ({ page }) => {
    await openPlanner(page);

    const axe = await buildAxe(page);
    const results = await axe.analyze();

    expect(results.violations).toEqual([]);
});

/*
 * P4: the "Recommended (Calculated)" panel. The rule, its footnote and the numbers are the
 * feature test's (`CareerSkillsPlannerRecommendationTest`); what only a browser can prove is that
 * the panel reached the screen, that the add button writes one plan entry per skill on a double
 * click, and that the budget bar moves with the plan.
 *
 * Fixture: two 180 SP priorities, 100 SP logged — so the priorities alone are 360 SP and the
 * shortfall is 260, the panel's own "priorities exceed the budget" path.
 */
async function openRecommendation(page: import('@playwright/test').Page) {
    await openPlanner(page);
    await page.getByText('Build target, recorded skills and the add form').click();

    const panel = page.locator('section[aria-labelledby="skills-recommended-heading"]');
    await expect(panel).toBeVisible();

    return panel;
}

test('the recommendation panel states the closure figures and adds one plan entry per skill', async ({ page }) => {
    const panel = await openRecommendation(page);
    const budget = page.locator('section[aria-labelledby="skills-budget-heading"]');

    await expect(panel.getByRole('heading', { name: /Recommended/ })).toBeVisible();
    await expect(panel).toContainText('2 recommended skills cost');
    await expect(panel).toContainText('360 SP');
    await expect(panel).toContainText('a shortfall of');
    await expect(panel).toContainText('260 SP');
    await expect(panel.locator('ol li')).toHaveCount(2);

    const add = panel.getByRole('button', { name: 'Add recommendation to plan' });

    // A double click must land two plan entries, not four: the handler filters ids already planned
    // and the endpoint upserts, so the count and the spend stay at one set.
    await add.dblclick({ force: true });

    await expect(page.locator('.savebar')).toContainText('2 skills planned');
    await expect(budget.getByText('360', { exact: true })).toBeVisible();
    await expect(budget.getByText('-260', { exact: true })).toBeVisible();
});

test('plan entries added from the recommendation stay editable and removable', async ({ page }) => {
    const panel = await openRecommendation(page);
    const budget = page.locator('section[aria-labelledby="skills-budget-heading"]');

    await panel.getByRole('button', { name: 'Add recommendation to plan' }).click();
    await expect(page.locator('.savebar')).toContainText('2 skills planned');

    // Hint editability is covered by SkillHintLevelTest and
    // CareerSkillsPlannerRecommendationTest; the two recommended priorities here are
    // outside the run's pool, so the page correctly offers no hint selector for them
    // (P2: hint applies to pool skills only).

    // Removal: unchecking the row takes its cost back out of the budget bar.
    await page.getByRole('checkbox', { name: 'Plan Gourmand' }).first().uncheck();
    await expect(page.locator('.savebar')).toContainText('1 skill planned');
    await expect(budget.getByText('180', { exact: true })).toBeVisible();
    // Outside-pool priorities offer no hint selector (P2: hint applies to pool skills only).
    await expect(page.getByRole('combobox', { name: 'Hint level for Gourmand' })).toHaveCount(0);
    await expect(page.getByRole('combobox', { name: 'Hint level for Unstoppable' })).toHaveCount(0);
});
