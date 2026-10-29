import { test, expect } from '@playwright/test';
import { resolve } from 'node:path';
import { pathToFileURL } from 'node:url';
import { BAND_MARKERS, assertStructurallyEqual, capture } from './fixtures/hfw-structure';

/*
 * The setup wizard's shell, SetupLayout (`ADR-0020` §1; plan §5 Slice 4).
 *
 * The stepper is a status readout, not a second navigation: six `.step` spans under a `role="list"`,
 * the current one `aria-current="step"`, and a four-mark legend that names the states. The owner's
 * 2026-10-28 ruling moved it out of the step body and into the head band's single right toolbar, above
 * the rule (`HFW-OVERRIDE (structure: wizard toolbar)` in `SetupLayout.vue`), so this file asserts that
 * placement rather than the wireframe's `canvas > .stepper` position. The position ("Step N of 6")
 * stays in the same row as the two doors, and there is no save bar on any wizard page. These are the
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

        // The owner's single right toolbar: chips and legend in the head band's action column, above
        // the rule, not in the step body. Measured rather than read off the markup: the chips' bottom
        // edge has to land above the rule's top edge.
        const band = page.locator('[data-region="hrow-actions"]');
        await expect(band.locator('[data-setup-steps]')).toHaveCount(1);
        await expect(band.locator('.step-legend')).toHaveCount(1);

        const chips = await page.locator('[data-setup-steps]').boundingBox();
        const rule = await page.locator('[data-region="hr"]').boundingBox();

        if (chips === null || rule === null) {
            throw new Error('the stepper or the head band rule is not laid out');
        }

        expect(chips.y + chips.height, 'the stepper sits above the rule').toBeLessThanOrEqual(rule.y);
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

    /*
     * The shell, measured against the wireframe rather than read off the source.
     *
     * The comparison is over `BAND_MARKERS` on purpose: the step body (its cards, notes and pills) is
     * each page's own case, so gating the shell on a page-body marker would make this case fail for a
     * reason this file does not own, and the six step chips are excluded because the toolbar override
     * deliberately moved them above the rule (asserted in the first case). `main` is the captured
     * subtree on both sides (`docs/design-research/frontend-v2-wireframes/career-scenario.html`).
     */
    test('renders the HFW shell structure', async ({ page }) => {
        const hfw = pathToFileURL(
            resolve(process.cwd(), 'docs/design-research/frontend-v2-wireframes/career-scenario.html'),
        ).href;

        await page.goto('/career/setup/scenario');
        await page.locator('#app > *').first().waitFor();
        const app = await capture(page, 'main', { markers: BAND_MARKERS, slug: 'scenario', side: 'app' });

        await page.goto(hfw);
        const wireframe = await capture(page, 'main', { markers: BAND_MARKERS, slug: 'scenario', side: 'hfw' });

        /*
         * No `rect` here, unlike the instruction's sketch. The captured root is the whole of `main`, and
         * its HEIGHT is not a structural property: the app renders a live draft strip, a real step body
         * and the shell's own chrome, while the wireframe is a static document, so the two heights
         * differ by design (measured 2026-10-27: width matched inside 40px, height differed by 243px).
         * A height tolerance on this root would assert that the page is a wireframe, which is not the
         * claim. Per-card sizes are compared where a card is the captured root.
         */
        assertStructurallyEqual(app, wireframe, { regions: 'subset', grid: true });
    });
});