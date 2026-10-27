import { test, expect } from '@playwright/test';
import { resolve } from 'node:path';
import { pathToFileURL } from 'node:url';
import { assertStructurallyEqual, capture } from './fixtures/hfw-structure';

/*
 * Rendered-copy, state and accessibility evidence for SCREEN-002, Scenario Selection
 * (ADR-0020 §1; plan §8 D2). `CareerScenarioSelectTest` asserts the resolved props and the session
 * draft; these assert what the browser shows and what only a browser can reach: the card copy, the
 * radio group's checked state after a real PUT round trip, the keyboard path, the 44px sweep, 320px
 * reflow and a clean console.
 *
 * This spec writes no database row. The selection lands in the session draft, which is the point of
 * the mechanism: unlike the run-creating specs it leaves the shared scratch database exactly as it
 * found it, and each Playwright test gets a fresh browser context, so each starts with no draft.
 *
 * Axe coverage is provided by `tests/browser/accessibility.spec.ts`: `@axe-core/playwright` is installed
 * and scans pages against `wcag2a`, `wcag2aa`, and `wcag21aa`; this spec retains the hand-rolled checks
 * for target size, keyboard path, focus order, reflow and console errors.
 */

const CARDS = ['URA Finale', 'Unity Cup', 'Trackblazer', 'Our Grand Concert'];

/**
 * The card that names the scenario in its heading, not anywhere in its body.
 *
 * Addressed through `data-scenario-card` rather than a list role: since 3.3 the cards are a radio
 * group inside a `fieldset`, not a `<ul>` of `<li>`s, so a `listitem` query resolves to nothing. The
 * data attribute is the card's own marker and survives the element change.
 */
function card(page: import('@playwright/test').Page, label: string) {
    return page.locator('[data-scenario-card]').filter({ has: page.getByRole('heading', { name: label, exact: true }) });
}

/**
 * Opens the card's one facts disclosure and returns it.
 *
 * The option-card grammar (Hick's Law, ux-laws §3.2 row 4) keeps the eight-row fact grid and the
 * matrix's metadata behind a card-level collapse, so every assertion that reads a fact opens it first.
 * The filter survives the two nested `<details>` inside it (the ruleset absence and the key-mechanics
 * collapse) because neither contains the summary's own words, and the count guard is what turns a
 * structural mistake into a loud failure instead of a strict-mode collision.
 */
async function openFacts(page: import('@playwright/test').Page, label: string) {
    const facts = card(page, label).locator('details').filter({ hasText: 'Facts and mechanics' });

    await expect(facts, `${label} renders exactly one facts disclosure`).toHaveCount(1);

    // The summary is addressed by its own words, not by `locator('summary')`: the open disclosure
    // holds every nested summary on the card (the absences, the key mechanics), and a descendant
    // query resolves to all of them at once.
    if (!(await facts.evaluate((el) => (el as HTMLDetailsElement).open))) {
        await facts.locator('summary').filter({ hasText: 'Facts and mechanics' }).click();
    }

    return facts;
}

test.describe('Scenario Selection', () => {
    test('renders one card per scenario the matrix composes', async ({ page }) => {
        await page.goto('/career/setup/scenario');
        await page.locator('#app > *').first().waitFor();

        for (const label of CARDS) {
            await expect(card(page, label)).toBeVisible();
        }

        // Four radios, and no more: the count is the matrix, not a hardcoded shape. The card IS the
        // radio's label since 3.3, so the input count and the card count are the same question.
        await expect(page.getByRole('radio')).toHaveCount(CARDS.length);

        // The step position is announced, and the unbuilt steps are named absences rather than links.
        await expect(page.getByRole('navigation', { name: 'Setup steps' }).getByRole('link', { name: 'Scenario' })).toHaveAttribute('aria-current', 'step');
        await expect(page.getByText('Step 1 of 6').first()).toBeVisible();

        // Scoped to the step nav: a page-body phrase like "not built" would otherwise be counted as an
        // absent step. All six steps are live since D7 landed Preflight, so none renders as an absence.
        await expect(page.getByRole('navigation', { name: 'Setup steps' }).getByText('not built')).toHaveCount(0);
    });

    test('shows Our Grand Concert as baseline-only, read off the matrix', async ({ page }) => {
        await page.goto('/career/setup/scenario');
        await page.locator('#app > *').first().waitFor();

        // The option-card grammar: every card carries its facts collapsed, so the choice reads as a
        // name, a subtitle and one action before anything expands.
        for (const label of CARDS) {
            await expect(card(page, label).locator('details').filter({ hasText: 'Facts and mechanics' })).not.toHaveAttribute('open');
        }

        // The scenario composes no panel beyond the baseline, so its resource list is empty and its
        // systems list is the one entry the goals flag names (8668d9d). Both read off the matrix.
        await openFacts(page, 'Our Grand Concert');
        await openFacts(page, 'URA Finale');

        // The career-goals flag turned on for the scenario too (8668d9d), so the Systems row names it
        // instead of the baseline absence it printed while the flag was off. Scoped to the facts
        // disclosure: the card face also carries the composition tags, so an unscoped text match
        // resolves to two elements. The resource half of the baseline statement still holds: no
        // widget beyond the baseline three composes a resource.
        const grandConcertFacts = card(page, 'Our Grand Concert').locator('details').filter({ hasText: 'Facts and mechanics' });
        await expect(grandConcertFacts.getByText('Career goals', { exact: true })).toBeVisible();
        await expect(card(page, 'Our Grand Concert').getByText('None beyond the generic strip')).toBeVisible();

        // Caps are sourced even where mechanics are not, so the card still states what it optimizes.
        await expect(card(page, 'Our Grand Concert').getByText('Speed (+400)')).toBeVisible();

        // The ruleset version is a named absence on every card, and its reason is one statement in
        // two media (DESIGN.md §5.6). Scoped to the `dd` that owns the reason: once the facts
        // disclosure is open, a card-level `details` filter matches the outer collapse too, because
        // the outer's text now contains the reason.
        const ruleset = card(page, 'URA Finale').locator('dd').filter({ hasText: /No source defines a Global ruleset version/ }).locator('details');
        await expect(ruleset).toBeVisible();
        await expect(ruleset).toContainText('N/A');

        // And the brief's description and recommended use render where a scenario guide sources
        // them: URA Finale's guide states both, so this card prints them rather than the absence,
        // and the absence is reserved for a guide that names neither.
        const use = card(page, 'URA Finale').getByText('Recommended use').locator('..');
        await expect(use).toContainText('baseline for learning');
        await expect(use.getByText('N/A')).toHaveCount(0);
    });

    test('keeps the ruleset version, the scenario ruleset date and the rule family as separately named facts', async ({ page }) => {
        // R2-05: the Unity Cup card printed two rows headed `Ruleset`, one `N/A` and one `Team`, which
        // reads as the card contradicting itself. Each fact keeps its own heading and its own value —
        // and since the 2026-10-15 wiring there are three ruleset-worded rows, each still its own:
        // the tool-wide version (absent by ruling), the scenario's own ruleset date (sourced), and
        // the rule family (the matrix's facility_level_source).
        await page.goto('/career/setup/scenario');
        await page.locator('#app > *').first().waitFor();

        await openFacts(page, 'Unity Cup');

        const unity = card(page, 'Unity Cup');

        const unityRuleset = unity.locator('dd').filter({ hasText: /No source defines a Global ruleset version/ }).locator('details');
        await expect(unityRuleset).toBeVisible();
        await expect(unityRuleset).toContainText('N/A');

        // The scenario's own ruleset date renders its sourced value with a derived launch/rework
        // word, and the event that dated it rides the `title`; never inside the version row above.
        const dateRow = unity.getByText('Scenario ruleset date', { exact: true }).locator('..');
        await expect(dateRow).toContainText('2026-07-01 (rework)');
        // SubCell retired the `title` channel for a present value (2026-10-17 amendment), so the
        // citation is a visible provenance line beside the row rather than a tooltip.
        await expect(unity.locator('[data-field="ruleset-date-source"]')).toContainText(/July 1, 2026 rework raised caps/);

        // The family renders the humanized label, never the snake_case taxonomy key (owner
        // ruling, 2026-10-16): the row prints the matrix's taxonomy in words, not config.
        await expect(unity.getByText('Rule family', { exact: true }).locator('..').getByText('Team rank', { exact: true })).toBeVisible();

        // Neither heading is bare `Ruleset` any more, on any card, and no heading repeats. The row
        // that carries the family prints the family where the matrix records one and the named
        // absence where it does not, so a scenario entry holding no ruleset still names the gap
        // rather than dropping the row. Trackblazer's facts open here because the rows inside a
        // closed disclosure are in the tree but not visible.
        await openFacts(page, 'Trackblazer');
        await expect(page.getByText('Ruleset', { exact: true })).toHaveCount(0);
        await expect(card(page, 'Trackblazer').getByText('Rule family', { exact: true })).toHaveCount(1);
        await expect(
            card(page, 'Trackblazer').getByText('Rule family', { exact: true }).locator('..').getByText('Repetition + purchases', { exact: true }),
        ).toBeVisible();

        // The 2026-10-16 publisher pass recovered Unity Cup's recommended use and Trackblazer's
        // planning focus, so those rows print the published line and cite the page it came from in
        // the `title`. The one cell no publisher names, URA Finale's planning focus, still renders
        // the research's own recorded search, verbatim. The filter there is scoped to the card's
        // nested `details` only: with the facts disclosure open, the outer one's text contains the
        // inner reason too, and an unscoped filter would match both.
        const unityUse = unity.getByText('Recommended use', { exact: true }).locator('..').locator('dd');
        await expect(unityUse).toContainText('go-to scenario for building strong, competitive aces');
        await expect(unity.locator('[data-field="recommended-use-source"]')).toContainText(/Game8, Unity Cup Update Guide \(archives\/545572\)/);

        const trackFocus = card(page, 'Trackblazer').getByText('Planning focus', { exact: true }).locator('..').locator('dd');
        await expect(trackFocus).toContainText('Races and the Pro Shop');
        await expect(trackFocus).toHaveAttribute('title', /archives\/580723/);

        await openFacts(page, 'URA Finale');
        await expect(
            card(page, 'URA Finale').locator('details details').filter({ hasText: 'No repo master names a planning focus for this scenario' }),
        ).toHaveCount(1);
    });

    test('states the documentation badge when the matrix records no guide', async ({ page }) => {
        // The badge is driven by `documented` in `config/scenarios.php`. That flag currently reads
        // true for Our Grand Concert because commit `ab53861` recovered the 2026-10-05 primary read,
        // so the badge is asserted here as absent-on-the-stored-value and its positive rendering is
        // pinned against a falsified matrix in `CareerScenarioSelectTest`. If the owner rules the
        // scenario only partially documented, the badge appears with no component change.
        await page.goto('/career/setup/scenario');
        await page.locator('#app > *').first().waitFor();

        await expect(page.getByText('PARTIALLY DOCUMENTED')).toHaveCount(0);
    });

    test('renders the facts grid as a run of `<dl>` cells under a plain container', async ({ page }) => {
        await page.goto('/career/setup/scenario');
        await page.locator('#app > *').first().waitFor();

        // A-91 (plan slice 1.4). The grid's cells are `SubCell` roots, and `SubCell` is a `<dl>` by its
        // own markup, so a `role="list"` over them was a critical `aria-required-children` violation.
        // This is the third site of that shape; the two the audit named were fixed and this one was
        // missed because a settled scan never enters a collapsed `<details>`. The role is asserted
        // absent outright rather than merely not `list`, and the cells as `dl` roots rather than by a
        // count, so the case cannot be satisfied by a different wrong role or broken by the grid
        // gaining a row.
        const grid = (await openFacts(page, 'URA Finale')).locator('[aria-label="Facts and mechanics"]');

        await expect(grid, 'one facts container per card').toHaveCount(1);
        await expect(grid.locator('> dl[data-stat-tile]')).not.toHaveCount(0);
        await expect(grid.locator('> li')).toHaveCount(0);
        // An absent `role` reads `null` on BOTH the attribute and the `HTMLElement.role` property.
        // Measured, not assumed: a bare `<div>` reports `getAttribute` null and `.role` null, while
        // `role="list"` reports `"list"` on both. The assertion this replaced
        // (`toHaveJSProperty('role', '')`) could never pass, which is why this case read
        // red-as-stale; the property is never the empty string.
        await expect(grid).not.toHaveAttribute('role');
        await expect(grid).toHaveJSProperty('role', null);
    });

    test('selects a scenario by keyboard and keeps it after a round trip', async ({ page }) => {
        await page.goto('/career/setup/scenario');
        await page.locator('#app > *').first().waitFor();

        // Addressed by its id, not by its label: the card's own text is the thing under test, so a
        // locator built from it would resolve to nothing the moment the card becomes the selected one.
        const trackblazer = card(page, 'Trackblazer').locator('#scenario-radio-trackblazer');

        // Nothing is checked on a fresh draft, so the stored state is visible rather than implied.
        await expect(trackblazer).not.toBeChecked();

        // The strip states the three carried choices, and says the scenario is unmade. This replaced
        // the per-step "Stored choice:" line in 3.2, so the assertion moved with it.
        await expect(page.locator('[data-field="choices-so-far"]')).toContainText('Scenario:');
        await expect(page.locator('[data-field="choices-so-far"]').getByText('not chosen')).toHaveCount(3);

        await trackblazer.focus();
        await expect(trackblazer).toBeFocused();
        await page.keyboard.press('Space');

        // The PUT re-renders from the session, so the checked state is read off stored state. Focus is
        // restored to the same control (WCAG 2.4.3), which is what the round trip would otherwise lose.
        await expect(page.getByText('Scenario set.')).toBeVisible();
        await expect(trackblazer).toBeChecked();
        await expect(page.getByText('✓ Selected')).toBeVisible();
        await expect(page.locator('[data-field="choices-so-far"]')).toContainText('Trackblazer');
        await expect(trackblazer).toBeFocused();

        // And it survives leaving the wizard entirely, which a client-only selection would not.
        await page.getByRole('link', { name: 'Leave setup' }).click();
        await page.waitForURL(/\/$/);

        await page.goto('/career/setup/scenario');
        await page.locator('#app > *').first().waitFor();
        await expect(card(page, 'Trackblazer').locator('#scenario-radio-trackblazer')).toBeChecked();
    });

    test('choosing a card does not navigate, and the footer action commits the step', async ({ page }) => {
        // The 3.3 contract: a radio records the choice, one footer button advances. The old per-card
        // button navigated on click, which meant a misclick both changed the answer and left the page.
        await page.goto('/career/setup/scenario');
        await page.locator('#app > *').first().waitFor();

        await card(page, 'Our Grand Concert').getByRole('radio').check();

        // Still on step 1: selecting is not the same decision as continuing.
        await expect(page).toHaveURL(/\/career\/setup\/scenario$/);
        await expect(card(page, 'Our Grand Concert').getByRole('radio')).toBeChecked();

        await page.getByRole('button', { name: 'Continue to trainee' }).click();
        await page.waitForURL(/\/career\/setup\/trainee$/);
    });

    test('a chosen scenario can be changed to another without leaving the step', async ({ page }) => {
        // The concern the old deselect case protected is a misclick with no way back. A radio group
        // answers it differently from a toggle: choosing another card replaces the first, in place,
        // with no navigation, so the correction costs one click and never a round trip to a cleared
        // state. A group that cannot be emptied is the right shape here: the next step needs a
        // scenario, so "no scenario" is not a state the wizard should be able to sit in after step 1.
        await page.goto('/career/setup/scenario');
        await page.locator('#app > *').first().waitFor();

        const concert = page.locator('#scenario-radio-our_grand_concert');
        const trackblazer = page.locator('#scenario-radio-trackblazer');

        await concert.check();
        await expect(concert).toBeChecked();
        await expect(page.locator('[data-field="choices-so-far"]')).toContainText('Our Grand Concert');

        await trackblazer.check();
        await expect(trackblazer).toBeChecked();
        await expect(concert).not.toBeChecked();
        await expect(page.locator('[data-field="choices-so-far"]')).toContainText('Trackblazer');

        // Still on the step: changing the answer is not the same as committing it.
        await expect(page).toHaveURL(/\/career\/setup\/scenario$/);
    });

    test('sizes every scenario control to the 44px contract', async ({ page }) => {
        await page.goto('/career/setup/scenario');
        await page.locator('#app > *').first().waitFor();

        // The card is the radio's label since 3.3, so the hit target is the label, not a per-card
        // button. Each label is measured rather than the 16px input inside it.
        for (const label of CARDS) {
            const box = await card(page, label).locator('label').first().boundingBox();
            expect(box?.height ?? 0, `${label}'s card is not sized to 44px`).toBeGreaterThanOrEqual(44);
        }

        // The one action that advances the step, and the one that leaves it.
        const save = await page.getByRole('button', { name: 'Continue to trainee' }).boundingBox();
        expect(save?.height ?? 0, 'Continue to trainee is not sized to 44px').toBeGreaterThanOrEqual(44);

        const leave = await page.getByRole('link', { name: 'Leave setup' }).boundingBox();
        expect(leave?.height ?? 0, 'Leave setup is not sized to 44px').toBeGreaterThanOrEqual(44);

        const stepLink = await page.getByRole('navigation', { name: 'Setup steps' }).getByRole('link', { name: 'Scenario' }).boundingBox();
        expect(stepLink?.height ?? 0, 'the step link is not sized to 44px').toBeGreaterThanOrEqual(44);
    });

    test('reflows at 320px, logs no console error, and renders under reduced motion', async ({ page }) => {
        const consoleErrors: string[] = [];
        page.on('console', (message) => {
            if (message.type() === 'error') {
                consoleErrors.push(message.text());
            }
        });
        page.on('pageerror', (error) => consoleErrors.push(error.message));

        await page.setViewportSize({ width: 320, height: 720 });
        await page.emulateMedia({ reducedMotion: 'reduce' });
        await page.goto('/career/setup/scenario');
        await page.locator('#app > *').first().waitFor();

        // Four cards stack to one column; the document must not scroll sideways (WCAG 1.4.10).
        const overflow = await page.evaluate(
            () => document.documentElement.scrollWidth <= document.documentElement.clientWidth + 1,
        );
        expect(overflow, 'the document scrolls horizontally at 320px').toBe(true);
        await expect(card(page, 'Our Grand Concert')).toBeVisible();

        expect(consoleErrors, `console errors: ${consoleErrors.join(' | ')}`).toEqual([]);
    });

    test('each scenario card carries a sourced summary, not the named absence', async ({ page }) => {
        await page.goto('/career/setup/scenario');
        await page.locator('#app > *').first().waitFor();

        const cards = page.locator('[data-scenario-card]');
        await expect(cards.first()).toBeVisible();
        const count = await cards.count();
        expect(count).toBeGreaterThan(0);

        // Every scenario's guide states a summary, so the fallback sentence must appear nowhere. The
        // earlier form of this case accepted "summary or named absence" and therefore passed while
        // nothing had landed; this form fails in exactly that state, which is the whole point.
        await expect(
            page.getByText('No sourced summary line exists for this scenario in this repository.'),
        ).toHaveCount(0);

        for (let i = 0; i < count; i++) {
            const summary = cards.nth(i).locator('[data-field="summary"]');

            await expect(summary).toBeVisible();
            await expect(summary).not.toHaveText(/^\s*$/);
        }
    });

    /*
     * The step body, measured against the wireframe rather than read off the source.
     *
     * `.canvas` is the compared subtree on both sides: the wireframe wraps the stepper, the scenario
     * grid, the note and the reversibility card in it, and so does `SetupLayout`. `regions: 'subset'`
     * requires the wireframe's markers in order, which covers the four cards, the card-face order
     * (name, baseline pill, release tag, paragraph, tag row), the note and the reversibility table;
     * app-only markers above them (`ChoicesSoFar`, the "Compare scenarios" link) are allowed.
     *
     * `grid: true` compares the track count of the first grid in the subtree, which is the scenario
     * grid: two tracks at 1280 on both sides. The wireframe's own breakpoint is `max-width:820px`
     * (frontend-v2-wireframes/assets/shell.css:406), which is why the page keeps `min-[821px]:`; the
     * instruction's "761px" would draw two columns where the wireframe draws one.
     */
    test('renders the four scenario cards in a two-column grid, HFW structure', async ({ page }) => {
        const hfw = pathToFileURL(
            resolve(process.cwd(), 'docs/design-research/frontend-v2-wireframes/career-scenario.html'),
        ).href;

        await page.goto('/career/setup/scenario');
        await page.locator('#app > *').first().waitFor();
        const app = await capture(page, '.canvas', { slug: 'scenario', side: 'app' });

        await page.goto(hfw);
        const wireframe = await capture(page, '.canvas', { slug: 'scenario', side: 'hfw' });

        assertStructurallyEqual(app, wireframe, { regions: 'subset', grid: true });
    });
});
