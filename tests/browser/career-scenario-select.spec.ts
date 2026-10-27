import { test, expect } from '@playwright/test';
import { resolve } from 'node:path';
import { pathToFileURL } from 'node:url';
import { assertStructurallyEqual, capture } from './fixtures/hfw-structure';
import { captureBoth } from './fixtures/wizard-screenshots';

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

/*
     * What the card face states, read off the matrix.
     *
     * This case used to open a "Facts and mechanics" disclosure on each card and assert eleven rows
     * inside it. The wireframe's card is name, baseline pill, release date, one sentence and three tags
     * (career-scenario.html:89-170), and that block was removed from the page, so the case now asserts
     * the same matrix facts where the wireframe puts them: on the face. The caps are matched with a
     * tolerant separator because the controller joins the parts with a middle dot.
     */
    test('states each scenario on the card face the way the matrix composes it', async ({ page }) => {
        await page.goto('/career/setup/scenario');
        await page.locator('#app > *').first().waitFor();

        // The Baseline pill belongs to the matrix's own baseline entry and to no other card.
        await expect(page.getByText('Baseline', { exact: true })).toHaveCount(1);
        await expect(card(page, 'URA Finale').getByText('Baseline', { exact: true })).toBeVisible();

        // The caps print as the matrix states them: the shared value with its count, the outlier named
        // rather than collapsed to one number.
        //
        // HFW-OVERRIDE (sourced): caps string uses the sourced stat name and the shared base; the mock
        // abbreviates and drops the base on this card (career-scenario.html:145 prints
        // "Caps Sta 1900 · Wit 1500"). The page reads from `ScenarioCaps` and is consistent across all
        // four cards, including URA's "1400 ×5", so the page is not corrected to the mock.
        await expect(card(page, 'URA Finale')).toContainText(/1400\s*×5/);
        await expect(card(page, 'Unity Cup')).toContainText(/1300\s*×4/);
        await expect(card(page, 'Unity Cup')).toContainText(/Wit\s*1800/);
        await expect(card(page, 'Trackblazer')).toContainText(/1200\s*×3/);
        await expect(card(page, 'Trackblazer')).toContainText(/Stamina\s*1900/);
        await expect(card(page, 'Trackblazer')).toContainText(/Wit\s*1500/);
        await expect(card(page, 'Our Grand Concert')).toContainText(/Speed\s*1600/);
        await expect(card(page, 'Our Grand Concert')).toContainText(/Guts\s*1500/);

        // Our Grand Concert composes the goals panel and carries the turn-rail flag, so its tag row
        // names both. This is the baseline-only statement the old case read out of the facts grid.
        await expect(card(page, 'Our Grand Concert').getByText('Career goals', { exact: true })).toBeVisible();
        await expect(card(page, 'Our Grand Concert').getByText('Performance input', { exact: true })).toBeVisible();

        /*
         * Four tag markers per card, which is exactly what the wireframe draws: the release date in the
         * head row, then two composition tags and the caps in the tag row. The composition pair is
         * `config/scenarios.php`'s `card_tags`, so a card cannot quietly grow a third again.
         */
        for (const name of CARDS) {
            await expect(card(page, name).locator('[data-region="tag"]'), `${name} tag count`).toHaveCount(4);
        }
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

    test('selects a scenario by keyboard and keeps it after a round trip', async ({ page }) => {
        await page.goto('/career/setup/scenario');
        await page.locator('#app > *').first().waitFor();

        // Addressed by its id, not by its label: the card's own text is the thing under test, so a
        // locator built from it would resolve to nothing the moment the card becomes the selected one.
        const trackblazer = card(page, 'Trackblazer').locator('#scenario-radio-trackblazer');

        // Nothing is checked on a fresh draft, so the stored state is visible rather than implied.
        await expect(trackblazer).not.toBeChecked();

        // No carried-choices strip on this step. `career-scenario.html` does not draw one, because on
        // step 1 nothing has been carried yet, so the page no longer renders it. The strip stays on the
        // steps whose wireframe does draw it (`career-profile.html`), and the empty state here is simply
        // the unchecked group above.

        await trackblazer.focus();
        await expect(trackblazer).toBeFocused();
        await page.keyboard.press('Space');

        // The PUT re-renders from the session, so the checked state is read off stored state. Focus is
        // restored to the same control (WCAG 2.4.3), which is what the round trip would otherwise lose.
        await expect(page.getByText('Scenario set.')).toBeVisible();
        await expect(trackblazer).toBeChecked();
        await expect(page.getByText('✓ Selected')).toBeVisible();
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

        await trackblazer.check();
        await expect(trackblazer).toBeChecked();
        await expect(concert).not.toBeChecked();

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
     * The page compared against the page, not the markers against the markers.
     *
     * The case this replaces asserted only that the wireframe's markers appear somewhere in the app.
     * That passed while each card still carried a 72-line "Facts and mechanics" disclosure the
     * wireframe does not have, and it passed again after those 105 lines were deleted: a one-way
     * subset test cannot see content the reference lacks, and it cannot see size. So this case asks
     * both directions — what must appear, what must not, and how big a card is allowed to be — and
     * keeps the marker comparison only for the skeleton and the grid shape.
     */
    test('renders the HFW scenario page and nothing the wireframe does not have', async ({ page }) => {
        const hfw = pathToFileURL(
            resolve(process.cwd(), 'docs/design-research/frontend-v2-wireframes/career-scenario.html'),
        ).href;

        await page.goto('/career/setup/scenario');
        await page.locator('#app > *').first().waitFor();
        expect(page.url()).toContain('/career/setup/scenario');

        // What the wireframe shows, the page shows.
        for (const name of CARDS) {
            await expect(card(page, name)).toBeVisible();
        }

        await expect(page.getByText('Baseline', { exact: true })).toHaveCount(1);
        await expect(page.getByText('Global · 2025-06-26')).toHaveCount(1);
        await expect(page.getByRole('columnheader', { name: 'Reversible?' })).toBeVisible();

        // Heading order is the page's section order: four cards, then the reversibility card.
        const headings = await page.locator('main h2').evaluateAll((els) => els.map((el) => (el.textContent ?? '').trim()));
        expect(headings).toEqual([...CARDS, 'What changes after this step']);

        /*
         * What the wireframe does not show, the page does not show. Each of these rendered here before
         * the parity pass: a 72-line "Facts and mechanics" disclosure per card, and a "Compare
         * scenarios" link. They are scenario facts the repository still holds — they belong on
         * Database / Scenarios, not on the step that asks one question.
         */
        for (const absent of [
            'Facts and mechanics',
            'Annotation key',
            'Compare scenarios',
            'Optimizes',
            'Tracked resources',
            'Turn loop',
            'Ruleset version',
            'Scenario ruleset date',
            'Recommended use',
            'Rule family',
            'Planning focus',
            'Key mechanics',
        ]) {
            await expect(page.getByText(absent), `the page must not render "${absent}"`).toHaveCount(0);
        }

        // Wireframe-only artifacts are not ported at all (the instruction's exclusion list).
        await expect(page.locator('.keybox, .proto, .wfnav, .mockbar, .anno')).toHaveCount(0);

        // And no carried-choices strip: `career-scenario.html` draws one only on the profile step, so
        // this page stops supplying the shell's `choices` prop.
        await expect(page.locator('[data-field="choices-so-far"]')).toHaveCount(0);

        /*
         * Card scale. The wireframe's card is 11-12 descendants and roughly 400 characters of prose;
         * the app's was 54-68 and 1419-2216 before this pass. A marker test cannot see this at all —
         * the extra rows carried no markers — so the size is asserted directly.
         */
        const sizes = await page.locator('[data-scenario-card]').evaluateAll((els) => els.map((el) => ({
            descendants: el.querySelectorAll('*').length,
            chars: (el.textContent ?? '').trim().length,
            details: el.querySelectorAll('details').length,
        })));

        expect(sizes).toHaveLength(4);

        for (const size of sizes) {
            expect(size.details, 'the card carries no disclosure').toBe(0);
            expect(size.descendants, `card holds ${size.descendants} descendants`).toBeLessThan(25);
            expect(size.chars, `card holds ${size.chars} characters`).toBeLessThan(900);
        }

        const app = await capture(page, '.canvas', { slug: 'scenario', side: 'app' });

        await page.goto(hfw);
        const wireframe = await capture(page, '.canvas', { slug: 'scenario', side: 'hfw' });

        /*
         * Still `subset`, deliberately, and the reason is a finding rather than an oversight: the page
         * prints one tag per panel `config/scenarios.php` switches on, and the matrix has three on for
         * Unity Cup and four for Trackblazer where the wireframe's mock lists two each. Matching the
         * wireframe exactly would mean suppressing real composition facts to hit a marker count, which
         * makes the page state less than the tool knows. The excess that was genuinely wrong (the fact
         * grid, the link, the card size) is asserted above instead.
         */
        assertStructurallyEqual(app, wireframe, { regions: 'subset', grid: true });

        if (process.env.WIZARD_SCREENSHOTS === '1') {
            await captureBoth(page, 'scenario');
        }
    });
});
