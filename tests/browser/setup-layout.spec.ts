import { test, expect } from '@playwright/test';

/*
 * The setup wizard's shell, SetupLayout (`ADR-0020` §1; plan §5 Slice 4).
 *
 * The stepper is a status readout, not a second navigation: six `.step` spans under a `role="list"`,
 * the current one `aria-current="step"`, and a four-mark legend that names the states. The position
 * ("Step N of 6") lives in the page head, and there is no save bar on any wizard page. These are the
 * shell invariants every step shares, asserted here once rather than per page.
 */

test.describe('SetupLayout shell', () => {
    test('renders the six-step stepper as spans, marks the current step, and names the four states', async ({ page }) => {
        await page.goto('/career/setup/scenario');
        await page.locator('#app > *').first().waitFor();

        const nav = page.getByRole('navigation', { name: 'Setup steps' });
        await expect(nav).toBeVisible();

        // Six `.step` spans, not links or buttons: a step is a status, not a destination.
        const steps = nav.locator('.step');
        await expect(steps).toHaveCount(6);
        await expect(nav.locator('a.step, button.step')).toHaveCount(0);

        // The current step carries aria-current="step" (WCAG 1.4.1: colour never carries it alone).
        await expect(nav.getByRole('listitem', { name: 'Scenario' })).toHaveAttribute('aria-current', 'step');

        // The legend names the four states, so "amber means you started this one" is written down.
        const legend = nav.locator('.step-legend');
        await expect(legend.getByText('Saved')).toBeVisible();
        await expect(legend.getByText('Started unfinished')).toBeVisible();
        await expect(legend.getByText('Not started')).toBeVisible();
        await expect(legend.getByText('Current')).toBeVisible();
    });

    test('states the position in the page head and renders no save bar', async ({ page }) => {
        await page.goto('/career/setup/scenario');
        await page.locator('#app > *').first().waitFor();

        // The position lives on the title row (page head), not in a foot save bar.
        await expect(page.getByText('Step 1 of 6').first()).toBeVisible();

        // No save-bar markup on any wizard page: the one primary action is the page-head Continue.
        await expect(page.locator('[data-save-bar], .save-bar')).toHaveCount(0);
        await expect(page.locator('footer [role="toolbar"], [data-setup-save]')).toHaveCount(0);
    });
});