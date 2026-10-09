import { test, expect } from '@playwright/test';

/*
 * Rendered-copy, state and accessibility evidence for SCREEN-002, Scenario Selection
 * (ADR-0020 §1; plan §8 D2). `CareerScenarioSelectTest` asserts the resolved props and the session
 * draft; these assert what the browser shows and what only a browser can reach: the card copy, the
 * `aria-pressed` state after a real POST round trip, the keyboard path, the 44px sweep, 320px reflow
 * and a clean console.
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

/** The card that names the scenario in its heading, not anywhere in its body. */
function card(page: import('@playwright/test').Page, label: string) {
    return page.getByRole('listitem').filter({ has: page.getByRole('heading', { name: label, exact: true }) });
}

test.describe('Scenario Selection', () => {
    test('renders one card per scenario the matrix composes', async ({ page }) => {
        await page.goto('/career/setup/scenario');
        await page.locator('#app > *').first().waitFor();

        for (const label of CARDS) {
            await expect(card(page, label)).toBeVisible();
        }

        // Four cards, and no more: the count is the matrix, not a hardcoded shape.
        await expect(page.getByRole('button', { name: 'Select Scenario' })).toHaveCount(CARDS.length);

        // The step position is announced, and the unbuilt steps are named absences rather than links.
        await expect(page.getByRole('link', { name: 'Scenario' })).toHaveAttribute('aria-current', 'step');
        await expect(page.getByText('Step 1 of 6').first()).toBeVisible();

        // Scoped to the step nav: a page-body phrase like "not built" would otherwise be counted as an
        // absent step. All six steps are live since D7 landed Preflight, so none renders as an absence.
        await expect(page.getByRole('navigation', { name: 'Setup steps' }).getByText('not built')).toHaveCount(0);
    });

    test('shows Our Grand Concert as baseline-only, read off the matrix', async ({ page }) => {
        await page.goto('/career/setup/scenario');
        await page.locator('#app > *').first().waitFor();

        // Every panel is off and its widgets are the baseline three, so the derivation produces these
        // two statements with no branch naming the scenario.
        await expect(card(page, 'Our Grand Concert').getByText('No scenario system')).toBeVisible();
        await expect(card(page, 'Our Grand Concert').getByText('None beyond the generic strip')).toBeVisible();

        // Caps are sourced even where mechanics are not, so the card still states what it optimizes.
        await expect(card(page, 'Our Grand Concert').getByText('Speed (+400)')).toBeVisible();

        // The ruleset version is a named absence on every card, with the reason on the element.
        const ruleset = card(page, 'URA Finale').getByText('Ruleset version', { exact: true }).locator('..').getByText('N/A', { exact: true });
        await expect(ruleset).toBeVisible();
        await expect(page.locator('[title*="No source defines a Global ruleset version"]').first()).toBeVisible();

        // And the two brief fields nobody can source say so instead of carrying invented prose.
        await expect(card(page, 'URA Finale').getByText('Recommended use').locator('..').getByText('N/A')).toBeVisible();
    });

    test('keeps the ruleset version and the rule family as two separately named facts', async ({ page }) => {
        // R2-05: the Unity Cup card printed two rows headed `Ruleset`, one `N/A` and one `Team`, which
        // reads as the card contradicting itself. Each fact keeps its own heading and its own value.
        await page.goto('/career/setup/scenario');
        await page.locator('#app > *').first().waitFor();

        const unity = card(page, 'Unity Cup');

        await expect(unity.getByText('Ruleset version', { exact: true }).locator('..').getByText('N/A', { exact: true })).toBeVisible();
        await expect(unity.getByText('Rule family', { exact: true }).locator('..').getByText('Team', { exact: true })).toBeVisible();

        // Neither heading is bare `Ruleset` any more, on any card, and no heading repeats.
        await expect(page.getByText('Ruleset', { exact: true })).toHaveCount(0);
        await expect(card(page, 'Trackblazer').getByText('Rule family', { exact: true })).toHaveCount(0);
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

        const trackblazer = card(page, 'Trackblazer').getByRole('button', { name: 'Select Scenario' });

        // Nothing is pre-pressed on a fresh draft, so the stored state is visible rather than implied.
        await expect(trackblazer).toHaveAttribute('aria-pressed', 'false');
        // Addressed by its own title, because `N/A` appears once per absent field on every card and a
        // text search for it is ambiguous by construction.
        await expect(page.getByTitle('No scenario has been chosen in this setup draft yet.')).toHaveText('N/A');

        await trackblazer.focus();
        await expect(trackblazer).toBeFocused();
        await page.keyboard.press('Enter');

        // The PUT re-renders from the session, so "Selected" is read off stored state. Focus is
        // restored to the same control (WCAG 2.4.3), which is what the round trip would otherwise lose.
        await expect(page.getByText('Scenario set.')).toBeVisible();
        await expect(trackblazer).toHaveAttribute('aria-pressed', 'true');
        await expect(card(page, 'Trackblazer').getByText('Selected')).toBeVisible();
        await expect(page.getByText('Stored choice:')).toContainText('Trackblazer');
        await expect(trackblazer).toBeFocused();

        // And it survives leaving the wizard entirely, which a client-only selection would not.
        await page.getByRole('link', { name: 'Leave setup' }).click();
        await page.waitForURL(/\/$/);

        await page.goto('/career/setup/scenario');
        await page.locator('#app > *').first().waitFor();
        await expect(card(page, 'Trackblazer').getByRole('button', { name: 'Select Scenario' }))
            .toHaveAttribute('aria-pressed', 'true');
    });

    test('sizes every scenario control to the 44px contract', async ({ page }) => {
        await page.goto('/career/setup/scenario');
        await page.locator('#app > *').first().waitFor();

        for (const label of CARDS) {
            const box = await card(page, label).getByRole('button', { name: 'Select Scenario' }).boundingBox();
            expect(box?.height ?? 0, `${label}'s action is not sized to 44px`).toBeGreaterThanOrEqual(44);
        }

        const leave = await page.getByRole('link', { name: 'Leave setup' }).boundingBox();
        expect(leave?.height ?? 0, 'Leave setup is not sized to 44px').toBeGreaterThanOrEqual(44);

        const stepLink = await page.getByRole('link', { name: 'Scenario' }).boundingBox();
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
});
