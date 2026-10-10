import { test, expect } from '@playwright/test';
import { buildAxe } from '../utils/accessibility';
import { deleteRun } from '../utils/delete-run';

/*
 * The scenario panel shell, `SCR-CAR-019` (SCREEN-014 / 015 / 016, plan §9 E1). `ScenarioPanelTest`
 * pins the payload and the G-33 property; these cases assert what only a browser reaches.
 *
 * **The fallback chain's arms are separate cases on purpose**, because their failure messages differ
 * and a parametrised case would collapse them into one: a registered renderer draws its own content,
 * an ON flag with no renderer names the flag, an unlabelled widget names the key. Two of the three
 * are config-gated — the registry is empty until E3 registers, and no Global scenario declares an
 * unlabelled widget — so each case states what it can reach and where the rendered half lands.
 *
 * A run is created through the assistant screens the way the other career specs do it, then walked
 * to the Cockpit. Writes get 60s because `php artisan serve` is a single-process `php -S`.
 */

const WRITE = { timeout: 60_000 } as const;

const createdRunUrls: string[] = [];

test.afterEach(async ({ page }) => {
    for (const url of createdRunUrls.splice(0)) {
        await deleteRun(page, url);
    }
});

/** A career of the given scenario, created the short way, then its Cockpit. */
async function openCockpit(page: import('@playwright/test').Page, scenario: string): Promise<void> {
    await page.goto('/training-runs/create', { waitUntil: 'domcontentloaded' });
    await page.locator('#app > *').first().waitFor();
    await page.locator('#trainee-combobox').fill('Rice Shower');
    await page.keyboard.press('Enter');
    await page.selectOption('select[name="scenario"]', { value: scenario });
    await page.getByRole('button', { name: 'Create run' }).click();
    await page.waitForURL(/\/training-runs\/\d+\/cockpit$/, WRITE);
    const runUrl = page.url().replace(/\/cockpit$/, '');
    createdRunUrls.push(runUrl);

    await page.goto(`${runUrl}/cockpit`, { waitUntil: 'domcontentloaded' });
    await page.locator('#app > *').first().waitFor();
}

/** A Unity Cup career, then its Cockpit: the one scenario with two resources and three panels on. */
async function openUnityCupCockpit(page: import('@playwright/test').Page): Promise<void> {
    await openCockpit(page, 'unity_cup');
}

test('resolves a key no renderer claims to undefined, and returns a component once one is registered', async () => {
    // The registry seam in isolation. E1 registers nothing, so this is the only place the
    // registered arm can be asserted today — the first rendered registration arrives with E3.
    const { register, resolve } = await import('../../resources/js/components/scenario/registry');

    expect(resolve('no_renderer_claims_this_key')).toBeUndefined();

    const probe = { name: 'Probe', render: (): null => null };
    register('probe_key', probe);

    expect(resolve('probe_key')).toBe(probe);
});

test('draws a resource the matrix labels through the meter, with the number and a labelled bar', async ({ page }) => {
    await openUnityCupCockpit(page);

    const section = page.getByRole('region', { name: 'Scenario' });
    await expect(section).toBeVisible();

    // Unity Cup's own two widgets, asserted through the meter's accessible name. Since E3, the
    // Team Panel below the strip also prints a "Team Rank" heading, so the exact-text locator that
    // used to be unique now matches two nodes — the meter's name is the collision-proof handle.
    // No column holds either reading yet, so each is `not recorded` rather than a zero (D-220).
    const bars = section.getByRole('img');
    await expect(bars.first()).toBeVisible();
    await expect(bars.first()).toHaveAttribute('aria-label', /Team Rank: not recorded/);
    await expect(section.getByRole('img', { name: /Spirit Bursts: not recorded/ })).toBeVisible();

    // The baseline three are the header's figures and are not repeated inside the panel.
    await expect(section.getByText('Turn', { exact: true })).toHaveCount(0);
});

test('names no fallback line, because every flag the matrix turns on now has a renderer', async ({ page }) => {
    await openUnityCupCockpit(page);

    const section = page.getByRole('region', { name: 'Scenario' });

    // E1 shipped this case as "an ON panel with no renderer prints a notice naming itself". E2, E3
    // and E4 have since claimed every flag `panel_labels` declares, so no Global scenario can reach
    // that arm any more, and the notice would have to be provoked by a config the app never loads.
    // The property the line guarded still holds two ways: the registry seam above proves `resolve()`
    // returns undefined for a key nobody claims, and `ScenarioPanelTest` proves an unlabelled widget
    // key travels so the shell can name it. What this case now pins is the shipped state: Unity Cup
    // composes three panels and none of them falls back.
    await expect(section.getByText(/cannot draw yet/)).toHaveCount(0);
    await expect(section.getByText(/this build cannot draw/)).toHaveCount(0);
});

test('draws nothing at all for a panel the scenario leaves off', async ({ page }) => {
    await openUnityCupCockpit(page);

    const section = page.getByRole('region', { name: 'Scenario' });

    // Unity Cup's OFF flags. An OFF flag is not a missing renderer: the region must not mention a
    // system this scenario does not compose, and a future config edit must not turn OFF into
    // "fallback shown".
    for (const label of ['Pro Shop', 'Grade Point objectives', 'Epithet routes']) {
        await expect(section.getByText(label, { exact: true })).toHaveCount(0);
        await expect(section.getByText(new RegExp(`cannot draw yet: ${label}`))).toHaveCount(0);
    }
});

test('states the scenario, its documentation and the ruleset absence in the baseline strip', async ({ page }) => {
    await openUnityCupCockpit(page);

    const section = page.getByRole('region', { name: 'Scenario' });
    await expect(section.getByText('Unity Cup', { exact: true })).toBeVisible();
    await expect(section.getByText('Documented', { exact: true })).toBeVisible();
    await expect(section.getByText(/No source defines a Global ruleset version/).first()).toBeVisible();

    // R2-05's residual: the strip is the Cockpit's only ruleset fact, and it was headed plain
    // `Ruleset` while the setup wizard's card had split into `Ruleset version` and `Rule family`. One
    // heading on one surface is fine, but it has to say which fact it is. Anchored, not `exact: true`:
    // the heading is a bare text node inside the paragraph that carries the refusal, so no element's
    // whole text is the heading alone, and a regex matcher is not whitespace-normalised, hence `\s*`
    // for the template's own indentation. The second line is the red-before-green half — the paragraph
    // opened with `Ruleset:` until the rename, so it cannot pass by matching nothing.
    await expect(section.getByText(/^\s*Ruleset version:/)).toBeVisible();
    await expect(section.getByText(/^\s*Ruleset:/)).toHaveCount(0);
});

test('renders only the baseline strip for a scenario that composes no panel', async ({ page }) => {
    // Our Grand Concert is the scenario whose every panel flag is off, so this is the shell's empty
    // branch. The strip's own copy and its caps table have their own spec
    // (`grand-concert-panel.spec.ts`); this case is the shell's view of it, so that the branch E1
    // shipped stays covered where E1 put it.
    await page.goto('/training-runs/create', { waitUntil: 'domcontentloaded' });
    await page.locator('#app > *').first().waitFor();
    await page.locator('#trainee-combobox').fill('Rice Shower');
    await page.keyboard.press('Enter');
    await page.selectOption('select[name="scenario"]', { value: 'our_grand_concert' });
    await page.getByRole('button', { name: 'Create run' }).click();
    await page.waitForURL(/\/training-runs\/\d+\/cockpit$/, WRITE);
    const runUrl = page.url().replace(/\/cockpit$/, '');
    createdRunUrls.push(runUrl);

    await page.goto(`${runUrl}/cockpit`, { waitUntil: 'domcontentloaded' });
    await page.locator('#app > *').first().waitFor();

    const section = page.getByRole('region', { name: 'Scenario' });
    await expect(section.getByText('Our Grand Concert', { exact: true })).toBeVisible();

    // The badge's partial arm, reachable since the owner's 2026-10-07 ruling put the badge on
    // `partially_documented` (plan §4.1 item 6, `SCREEN_SPEC.md` SCR-CAR-019).
    await expect(section.getByText('Partially documented', { exact: true })).toBeVisible();
    await expect(section.getByText('Documented', { exact: true })).toHaveCount(0);

    // Every panel is off, so no renderer mounts and no fallback row does.
    await expect(section.getByText(/cannot draw yet/)).toHaveCount(0);
});

test('keeps the 44px floor across the cockpit and reflows at 320 px', async ({ page }) => {
    await openUnityCupCockpit(page);

    // The panel adds no control of its own in E1 — every resource it draws is a reading, and its
    // panels arrive with E2 to E4 — so a sweep of the panel alone would be a check that cannot
    // fail. The sweep runs over the page with the panel mounted instead, which is where a target
    // the panel displaced would show up.
    const targets = page.locator('main a, main button');
    const count = await targets.count();
    expect(count, 'the cockpit renders no control at all').toBeGreaterThan(0);

    for (let i = 0; i < count; i++) {
        const box = await targets.nth(i).boundingBox();
        expect(box?.height ?? 0, `control ${i} is not sized to the 44px contract`).toBeGreaterThanOrEqual(44);
    }

    await page.setViewportSize({ width: 320, height: 700 });
    await expect(page.getByRole('region', { name: 'Scenario' })).toBeVisible();
    // No sideways scroll at the reflow width (WCAG 1.4.10).
    const overflow = await page.evaluate(
        () => document.documentElement.scrollWidth - document.documentElement.clientWidth,
    );
    expect(overflow, 'the cockpit scrolls sideways at 320px').toBeLessThanOrEqual(1);
});

test('passes the axe A + AA scan with the panel mounted', async ({ page }) => {
    await openUnityCupCockpit(page);

    await buildAxe(page).analyze();
});
