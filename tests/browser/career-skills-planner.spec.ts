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

    const budget = page.locator('section').filter({ has: page.locator('#skills-budget-heading') }).first();

    // Before: nothing planned, nothing spent.
    await expect(budget).toContainText(/planned spend/i);
    const spendBefore = await budget.innerText();

    const checkbox = page.locator('main table').first().locator('tbody input[type="checkbox"]:not([disabled])').first();

    await checkbox.check();

    // F-13's claim, held to the rendered figures. The walk reported that `PLANNED SPEND` stayed 0 and
    // `REMAINING` stayed at the run's SP after a skill was added, which would make the bar blind to
    // its own plans. This case pins the opposite: the two figures move with the plan, and the header
    // sentence agrees with them. Before this assertion the test's own title claimed to check the
    // spend and remaining figures while only reading the savebar and the commit button, which is how
    // the claim could be filed and left standing.
    await expect(budget, 'the budget bar did not count the planned row').not.toHaveText(
        spendBefore,
    );

    const spendAfter = await budget.innerText();
    await expect(
        spendAfter,
        'the planned spend still reads zero after a row was planned',
    ).not.toMatch(/planned spend\D*0/i);

    await expect(page.locator('.savebar')).toContainText('1 skill planned');
    await expect(page.getByRole('button', { name: 'Learn planned skills' }).first()).toBeDisabled();
    await expect(page.getByText('Too expensive')).toBeVisible();

    await page.getByRole('button', { name: 'Clear plan' }).click();

    await expect(page.locator('.savebar')).toContainText('No skill planned');
    // And clearing returns the spend to zero rather than leaving the last figure behind. Read the
    // figure itself: the label and its number are sibling nodes with no separator between them, so a
    // text regex over the whole bar cannot tell `Planned spend0` from `Planned spend140`.
    const spendValue = await budget
        .locator('text=/^Planned spend$/i')
        .locator('xpath=following-sibling::*[1]')
        .innerText();

    expect(spendValue.trim(), 'the planned spend survived Clear plan').toBe('0');
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

test('prints each hint caption once, never "Hint Lvl Hint Lvl 1" (R6-15)', async ({ page }) => {
    // R6-15: the payload's labels are already `Hint Lvl 1` … `Hint Lvl Max`
    // (`HintDiscount::ladder()`), and the template prefixed each with `'Hint Lvl ' +` again, so the
    // Client caption column read `Hint Lvl Hint Lvl 1`. The prefix was written for the SHORT form
    // `priceLadder()` returns (`Lv1` … `Lv Max`), which is a different consumer.
    await openPlanner(page);

    const ladder = page.getByRole('heading', { name: 'Why the hint ladder is not linear' }).locator('xpath=ancestor::section[1]');
    const rows = ladder.locator('tbody tr');

    await expect(rows).toHaveCount(5);

    const captions = await rows.evaluateAll((nodes) =>
        nodes.map((node) => (node.lastElementChild?.textContent ?? '').trim()),
    );

    expect(captions).toEqual(['Hint Lvl 1', 'Hint Lvl 2', 'Hint Lvl 3', 'Hint Lvl 4', 'Hint Lvl Max']);

    // The duplication is what the walk saw, so it is asserted as an absence too rather than only as
    // a positive shape: a future prefix would satisfy neither assertion.
    await expect(ladder.getByText(/Hint Lvl Hint Lvl/)).toHaveCount(0);
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

/*
 * Inv-3 (DOM half): the recommendation panel's figures are marked "Calculated".
 *
 * `InvariantSelfCheckTest::test_inv3_calculated_figures_reach_the_payload` holds the payload; this
 * holds the mark. A panel that printed the figures without the mark would pass the payload test
 * and fail here, which is the invariant.
 */
test('marks the recommendation panel Calculated', async ({ page }) => {
    const panel = await openRecommendation(page);
    const heading = panel.getByRole('heading', { name: /Recommended/ });

    await expect(heading).toBeVisible();
    await expect(heading).toContainText('Calculated');

    // The panel's own derivation note states the rule is the tool's, not the client's.
    await expect(panel).toContainText("this tool's heuristic, not a client mechanic");
});

/*
 * P1/F2: the add form's single `select` emit carries `{id, name}`, and a double submit lands one row.
 *
 * The picker emits the whole object rather than a bare id or name — both consumers (this form and
 * the compare control) read `.id`, and the label shows `.name`. A double submit must upsert, not
 * duplicate, which is the same dedupe direction the recommendation panel's button takes.
 */
test('adds a picked skill once even when the submit is double-clicked', async ({ page }) => {
    await openPlanner(page);

    // The add form lives inside the writers disclosure ("Build target, recorded skills and the add
    // form"), which is collapsed by default.
    const disclosure = page.locator('details').filter({ hasText: 'Build target, recorded skills and the add form' }).first();
    if ((await disclosure.getAttribute('open')) === null) {
        await disclosure.locator('summary').first().click();
    }

    const picker = page.getByRole('textbox', { name: 'Skill to add' });
    await expect(picker).toBeVisible();

    await picker.fill('Up-Tempo');
    const option = page.getByRole('option', { name: /Up-Tempo/ }).first();
    await expect(option).toBeVisible();
    await option.click();

    const add = page.getByRole('button', { name: 'Add to run' });
    await expect(add).toBeEnabled();
    await add.dblclick({ force: true });

    // The endpoint upserts on (run, skill), so a double submit lands one row. The observable proof
    // is that the skill leaves the picker's candidate list exactly once: `picker_skills` excludes
    // every skill the run already holds, so a duplicated write would leave a second candidate and a
    // missing write would leave the first.
    await expect(page.getByRole('textbox', { name: 'Skill to add' })).toBeVisible();

    await picker.fill('Up-Tempo');
    await expect(page.getByRole('option', { name: /Up-Tempo/ })).toHaveCount(0);

    // And the run now records it, in the status group the form defaults to.
    await expect(page.getByText('Up-Tempo').first()).toBeVisible();
});

/*
 * F2: the budget bar moves after each add and each remove.
 *
 * The bar's spend figure is the sum of planned rows, so it is the cheapest observable proof that
 * the plan state is reactive rather than a snapshot. A add that did not re-render would leave the
 * figure at 0; a remove that did not would leave it at the added total.
 */
test('moves the budget bar after each add and each remove', async ({ page }) => {
    await openPlanner(page);
    const budget = page.locator('section[aria-labelledby="skills-budget-heading"]');
    const savebar = page.locator('.savebar');

    // The plan starts empty — the fixture's priorities are *listed* as candidates, not planned.
    await expect(savebar).toContainText('No skill planned');

    // Add one: 180 SP of the 100 held, so the bar moves to 180 and the remainder goes negative.
    await page.getByRole('checkbox', { name: 'Plan Gourmand' }).first().check();
    await expect(savebar).toContainText('1 skill planned');
    await expect(budget.getByText('180', { exact: true })).toBeVisible();

    // Add the second: 360 total.
    await page.getByRole('checkbox', { name: 'Plan Unstoppable' }).first().check();
    await expect(savebar).toContainText('2 skills planned');
    await expect(budget.getByText('360', { exact: true })).toBeVisible();

    // Remove one: back to 180.
    await page.getByRole('checkbox', { name: 'Plan Gourmand' }).first().uncheck();
    await expect(savebar).toContainText('1 skill planned');
    await expect(budget.getByText('180', { exact: true })).toBeVisible();

    // Remove the last: back to zero.
    await page.getByRole('checkbox', { name: 'Plan Unstoppable' }).first().uncheck();
    await expect(savebar).toContainText('No skill planned');
});

/*
 * UX-18a (P1 = planning state), the write side. The client's bar gates what its own commit button
 * sends, so the reachable end-to-end flow is the plan state itself: add a skill through the
 * picker's own form, which posts a Suggested row - the state P1 approved - and read it back after a
 * cold reload. The over-budget refusal the server adds is pinned in `RunSkillInventoryTest`; it is
 * reachable only from a post this screen cannot produce (a bypassed bar, or a spend the bar never
 * saw), so no legitimate UI path of this page drives it.
 */
test('an added plan skill survives the write and a cold reload (UX-18a)', async ({ page }) => {
    await openPlanner(page);
    const runUrl = page.url().replace(/\/skills$/, '');

    // The picker's add form posts a Suggested row - the plan state P1 approved - through the same
    // sync the savebar's commit uses, and the sibling double-click case pins the dedup half. This
    // case pins the persistence half: the row is in the database, so a cold reload still shows it
    // and the picker no longer offers it.
    const disclosure = page.locator('details').filter({ hasText: 'Build target, recorded skills and the add form' }).first();
    if ((await disclosure.getAttribute('open')) === null) {
        await disclosure.locator('summary').first().click();
    }

    const picker = page.getByRole('textbox', { name: 'Skill to add' });
    await picker.fill('Gourmand');
    await page.getByRole('option', { name: /Gourmand/ }).first().click();
    await page.getByRole('button', { name: 'Add to run' }).click();

    await page.goto(`${runUrl}/skills`, { waitUntil: 'domcontentloaded' });
    await page.locator('#app > *').first().waitFor();

    // The recorded groups list the skill, and the picker excludes what the run already holds.
    await expect(page.getByText('Gourmand').first()).toBeVisible();

    const reopened = page.locator('details').filter({ hasText: 'Build target, recorded skills and the add form' }).first();
    if ((await reopened.getAttribute('open')) === null) {
        await reopened.locator('summary').first().click();
    }

    await page.getByRole('textbox', { name: 'Skill to add' }).fill('Gourmand');
    await expect(page.getByRole('option', { name: /Gourmand/ })).toHaveCount(0);
});
