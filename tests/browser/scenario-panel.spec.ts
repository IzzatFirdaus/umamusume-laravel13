import { test, expect } from '@playwright/test';
import { buildAxe, openAllDisclosures } from '../utils/accessibility';
import { deleteRun } from '../utils/delete-run';
import { expectTapTargets } from '../utils/tap-targets';
import { openScenarioDetail } from '../utils/scenario-detail';

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

test('keeps a widget key from resolving a panel component, and each map resolves its own kind', async () => {
    // The registry seam in isolation. The two maps are separate so a config entry that names a
    // widget exactly like a registered panel flag cannot resolve the panel component against widget
    // props — the defect `f523dcd` removed from the shell's registrations (F3).
    const { registerWidget, registerPanel, resolveWidget, resolvePanel } = await import(
        '../../resources/js/components/scenario/registry'
    );

    expect(resolveWidget('no_renderer_claims_this_key')).toBeUndefined();
    expect(resolvePanel('no_renderer_claims_this_key')).toBeUndefined();

    const widgetProbe = { name: 'WidgetProbe', render: (): null => null };
    const panelProbe = { name: 'PanelProbe', render: (): null => null };

    // A widget registered under a key resolves in the widget map only.
    registerWidget('probe_widget', widgetProbe);
    expect(resolveWidget('probe_widget')).toBe(widgetProbe);
    expect(resolvePanel('probe_widget')).toBeUndefined();

    // A panel registered under the same key resolves in the panel map only.
    registerPanel('probe_widget', panelProbe);
    expect(resolvePanel('probe_widget')).toBe(panelProbe);
    expect(resolveWidget('probe_widget')).toBe(widgetProbe);
});

test('draws a resource the matrix labels through the meter, with the number and a labelled bar', async ({ page }) => {
    await openUnityCupCockpit(page);
    // v3.0.0 moved the scenario boards onto their own page; the widgets ride with them.
    await openScenarioDetail(page);

    const section = page.getByRole('region', { name: 'Scenario' });
    await expect(section).toBeVisible();

    // Unity Cup's own two widgets, asserted through the meter's accessible name. Since E3, the
    // Team Panel below the strip also prints a "Team Rank" heading, so the exact-text locator that
    // used to be unique now matches two nodes — the meter's name is the collision-proof handle.
    // No column holds either reading yet, so each is `not recorded` rather than a zero (D-220).
    // The Team Panel is drawn inside this same region and now carries a meter of its own
    // (`UnityCupPanel.vue` "Current Spirit"), so document order no longer makes the first bar the
    // widget strip's Team Rank. Each meter is addressed by its accessible name instead, which is the
    // handle that cannot collide: the Spirit meter's position in the reading order is the design.
    await expect(section.getByRole('img', { name: /Team Rank: not recorded/ })).toBeVisible();
    await expect(section.getByRole('img', { name: /Spirit Bursts: not recorded/ })).toBeVisible();
    await expect(section.getByRole('img', { name: /Current Spirit: not recorded/ })).toBeVisible();

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
    // v3.0.0 moved the scenario boards onto their own page; the strip rides with them.
    await openScenarioDetail(page);

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

    // DESIGN.md §5 (a), 2026-10-17: one absence channel, and it is reachable without a mouse. The
    // marker was a `<span title>` and the sentence followed it as loose text, so a screen reader heard
    // "N/A" and a keyboard user got nothing. It is now `AbsenceValue`'s `<details>`, whose summary
    // carries the reason as its accessible name and which opens with no script. Red before this case:
    // the strip rendered no `<summary>` at all.
    // Filtered by the reason it carries, not by position: KI-157 moved the team stat-grade absences
    // onto `AbsenceValue` too, so the region now renders several `<summary>` elements and the ruleset
    // one is no longer first in document order. The assertion is about *this* absence, so it says so.
    const marker = section.locator('summary').filter({ hasText: 'No source defines a Global ruleset version' }).first();
    await expect(marker).toContainText('N/A');
    await expect(marker).toHaveAccessibleName(/No source defines a Global ruleset version/);

    // Keyboard-reachable and it actually opens: the reason is a visible statement, not a tooltip.
    await marker.focus();
    await expect(marker).toBeFocused();
    await page.keyboard.press('Enter');
    await expect(section.locator('details[open]').first()).toBeVisible();
    await expect(section.locator('details[open]').first()).toContainText(/No source defines a Global ruleset version/);
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
    // v3.0.0 moved the scenario boards onto their own page, so the empty branch is read there.
    await openScenarioDetail(page);

    const section = page.getByRole('region', { name: 'Scenario' });
    await expect(section.getByText('Our Grand Concert', { exact: true })).toBeVisible();

    // The badge's partial arm, reachable since the owner's 2026-10-07 ruling put the badge on
    // `partially_documented` (plan §4.1 item 6, `SCREEN_SPEC.md` SCR-CAR-019).
    await expect(section.getByText('Partially documented', { exact: true })).toBeVisible();
    await expect(section.getByText('Documented', { exact: true })).toHaveCount(0);

    // Every panel is off, so no renderer mounts and no fallback row does.
    await expect(section.getByText(/cannot draw yet/)).toHaveCount(0);
});

test('keeps the no-panels sentence off a scenario that composes panels', async ({ page }) => {
    await openUnityCupCockpit(page);
    // v3.0.0 moved the scenario boards onto their own page; this case reads the region there.
    await openScenarioDetail(page);

    const section = page.getByRole('region', { name: 'Scenario' });

    // Round-7 F-01 (plan `2026-11-09-scenario-detail-upgrade` §2.1, now section
    // `## 2026-11-09-scenario-detail-upgrade.md` of `docs/research-scratch/PROCESS-PLANS.md`).
    // `5e7da543` widened the strip's
    // gate to `careerGoalsOn ||` so the published caps would stop hiding behind the goals ladder, and
    // the "composes no panels of its own" sentence rode along inside the widened branch. Unity Cup
    // draws Team races, the Team Rank ladder and Career goals, so the sentence contradicted the page
    // above it. Red before the guard: this text was visible on this run.
    await expect(section.getByText(/composes no panels of its own/)).toHaveCount(0);

    // The caps the widening was for still render, and the panels above still render: the fix removes
    // the false sentence and nothing else.
    await expect(section.getByRole('table', { name: 'Published stat caps for this scenario' })).toBeVisible();
    await expect(page.locator('[data-region="scenario-panels"]')).toBeVisible();

    // The strip's own scenario identity is the shell's floor and is NOT part of this fix: it renders
    // above the panels for every scenario, declared or not.
    await expect(page.locator('[data-region="scenario"]').getByText('Unity Cup', { exact: true })).toBeVisible();
});

test('keeps the 44px floor across the cockpit and reflows at 320 px', async ({ page }) => {
    await openUnityCupCockpit(page);

    // The panel adds no control of its own in E1 — every resource it draws is a reading, and its
    // panels arrive with E2 to E4 — so a sweep of the panel alone would be a check that cannot
    // fail. The sweep runs over the page with the panel mounted instead, which is where a target
    // the panel displaced would show up. `expectTapTargets` skips what a collapsed disclosure hides
    // and asserts it measured something; 10 is well under the ~17 controls this page carries, so a
    // selector that stopped matching still fails.
    await expectTapTargets(page.locator('main a, main button'), 10);

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

    await openAllDisclosures(page);
    await buildAxe(page).analyze();
});

test('prints no spark count the run does not hold (R6-08)', async ({ page }) => {
    // R6-08 facet (a): the cap panel's note named a literal `six blue sparks`, taken from the source
    // document's tree rather than from this run's. It sat directly above `capSparkText`, which is
    // derived, so a run holding fewer sparks read a count beside a shorter list.
    //
    // The assertion is on the rendered note's relationship to the derived line, not on a wording: a
    // count-neutral sentence and a correctly-derived one both pass; `six` over a one-spark list does not.
    await openCockpit(page, 'unity_cup');

    const blueSparks = page.getByText('Blue sparks').first();
    await expect(blueSparks).toBeVisible();

    // The derived line beside it lists one entry per recorded spark. Read the note under it.
    const note = page.locator('p.text-xs.text-ink-muted').filter({ hasText: 'blue sparks the tree carries' }).first();

    if ((await note.count()) > 0) {
        const text = ((await note.textContent()) ?? '').trim();

        expect(text).not.toContain('six');
    }
});

test('names the band carrier, states the low-band ambiguity, and separates the two thirties', async ({ page }) => {
    // Section 13.6. The band table printed four reward rows with no carrier and no low-band caveat, so a
    // reader could not tell whose bands these are or that the published sets disagree at the bottom of the
    // table. And two unrelated `+30` figures share this panel: the band's "+30 to the matching stat" and
    // the team ranking bonus's "All Attributes +30". They must be separable by their words, not by
    // position, because a reader scanning for "the +30" finds two.
    //
    // The board lives on Scenario detail as of v3.0.0 -- the cockpit's own note says so -- so this case
    // opens the detail page rather than hunting the panel on the cockpit.
    await openCockpit(page, 'unity_cup');
    await page.locator('#app > *').first().waitFor();

    // The band table renders only when the run holds both burst tallies: a fresh run records neither, so
    // the panel draws no bands and no caveat to assert. Record them through the team editor, the same
    // door `career-cockpit-forms.spec.ts` uses, then come back to this run.
    const runUrl = page.url().replace(/\/cockpit$/, '');

    await page.goto(`${runUrl}/team`, { waitUntil: 'domcontentloaded' });
    await page.locator('#app > *').first().waitFor();

    const editor = page.locator('main');

    await editor.locator('input[name="spirit_bursts_count"]').fill('6');
    await editor.locator('input[name="extreme_bursts_count"]').fill('5');
    await editor.getByRole('button', { name: 'Save team state' }).click();
    await expect(page.getByText('Run updated.')).toBeVisible(WRITE);

    await page.goto(`${runUrl}/cockpit`, { waitUntil: 'domcontentloaded' });
    await page.locator('#app > *').first().waitFor();

    await openScenarioDetail(page);

    const bandsTitle = page.getByText('Burst count bands').first();

    await expect(bandsTitle).toBeVisible();

    const card = bandsTitle.locator('xpath=ancestor::div[contains(@class,"card")][1]');

    // The carrier is named, and it names the file the figures are transcribed from.
    await expect(card).toContainText('Source:');
    await expect(card).toContainText('scenarios.php');

    // The caveat states the overlap rather than saying the figures may differ.
    await expect(card).toContainText('4 to 9');
    await expect(card).toContainText('7 to 9');

    // The band's own +30 names its target, so it cannot be read as the other +30 on this panel.
    await expect(card).toContainText('+30 to the matching stat');

    // The other +30 -- `Team Ranking Bonus: All Attributes +30` -- renders only when the team rank is `S`,
    // and NO trainer form writes a team rank (R6-48's known limitation: `TeamRankPayload` is constructed
    // only in tests). So the two cannot be put on screen together through the UI today, and this case
    // asserts the half a browser can reach: the band's own label is unambiguous on its own.
    //
    // The other label's wording is asserted where it is reachable -- as a source string -- rather than
    // claimed here as rendered.
    expect((await page.locator('body').innerText()).includes('All Attributes +30')).toBe(false);
});
