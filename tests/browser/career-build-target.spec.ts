import { test, expect } from '@playwright/test';

/*
 * Rendered-copy, state and accessibility evidence for SCREEN-005 (Build Target) and the shared
 * provenance badge (`ADR-0020` §1; plan §8 D4). `CareerBuildTargetTest` asserts the resolved props,
 * the draft round trip, the ceiling clamp and the glyph sweep; these assert what only a browser can
 * reach: the rendered copy, the summary the page assembles, the error message tied to its input with
 * focus, the keyboard reorder path, the 44px contract and 320px reflow.
 *
 * This spec writes no database row and visits no run-scoped URL: the target lands in the session
 * draft, and the shared scratch database has no training run. Each Playwright test gets a fresh
 * browser context, so each starts with an empty draft and the base caps.
 *
 * The badge's accessible name is asserted through `getByRole('img', { name })`, never `toHaveText`
 * on the wrapper: Playwright's `toHaveText` reads `textContent` including `aria-hidden` text, so it
 * would see the glyph the name deliberately hides (`legacy.spec.ts` is red for exactly that reason).
 *
 * Axe coverage is provided by `tests/browser/accessibility.spec.ts`: `@axe-core/playwright` is installed
 * and scans pages against `wcag2a`, `wcag2aa`, and `wcag21aa`; this spec retains the hand-rolled checks
 * for target size, keyboard path, focus order, reflow and console errors.
 */

test.describe('Build Target and the provenance badge', () => {
    test('renders the step with the base caps, nothing pre-filled and one Calculated badge', async ({ page }) => {
        await page.goto('/career/setup/target');
        await page.locator('#app > *').first().waitFor();

        await expect(page.getByRole('heading', { name: 'Your target' }).first()).toBeVisible();
        await expect(page.getByRole('link', { name: 'Your target' })).toHaveAttribute('aria-current', 'step');
        await expect(page.getByText('Step 3 of 6').first()).toBeVisible();
        // All six steps are live links since D7: the `to: null` branch stays in `SetupLayout` for the
        // next slice that lands a step ahead of its screen, but it renders nothing today.
        await expect(page.getByRole('navigation', { name: 'Setup steps' }).getByText('not built')).toHaveCount(0);

        // No scenario yet: the page names it and every cap is the base cap with no bonus.
        await expect(page.getByTitle('No scenario has been chosen in this setup draft yet, so every cap below is the base cap with no scenario bonus.'))
            .toHaveText('N/A');
        await expect(page.getByText('Cap 1200')).toHaveCount(5);

        // No stored target: the absence is stated and no stat input carries a pre-filled zero.
        await expect(page.getByText('No target is recorded yet.')).toBeVisible();
        for (const stat of ['Speed', 'Stamina', 'Power', 'Guts', 'Wit']) {
            await expect(page.getByLabel(stat)).toHaveValue('');
        }

        // The four stored purposes, and the fifth the tool does not record stated in one line.
        for (const label of ['Story Clear', 'Champions Meeting', 'Parent Farming', 'Skill Farming']) {
            await expect(page.getByLabel('Purpose').locator('option', { hasText: label })).toHaveCount(1);
        }
        await expect(page.getByText('The design also names a fifth purpose, Competitive Build')).toBeVisible();
        await expect(page.getByText('A risk tolerance and per-skill marks')).toBeVisible();

        // The summary names every absence rather than omitting it.
        const summary = page.getByRole('region', { name: 'Summary' });
        await expect(summary).toContainText('Purpose not chosen yet');
        await expect(summary).toContainText('distance not chosen yet');
        await expect(summary).toContainText('Speed not entered');
        await expect(summary).toContainText('skills in order: none listed yet');

        // The badge: one statement, the word rather than the glyph, and no percentage anywhere in it.
        const badge = page.getByRole('img', { name: 'Calculated' });
        await expect(badge).toBeVisible();
        await expect(badge).not.toContainText('%');
    });

    test('saves the whole target to the draft, reads it back, and assembles the calculated summary', async ({ page }) => {
        await page.goto('/career/setup/target');
        await page.locator('#app > *').first().waitFor();

        await page.getByLabel('Purpose').selectOption({ label: 'Story Clear' });
        await page.getByLabel('Distance').selectOption({ label: 'Medium' });
        await page.getByLabel('Surface').selectOption({ label: 'Turf' });
        await page.getByLabel('Style').selectOption({ label: 'Front Runner' });

        await page.getByLabel('Speed').fill('1200');
        await page.getByLabel('Stamina').fill('800');
        await page.getByLabel('Power').fill('700');
        await page.getByLabel('Guts').fill('600');
        await page.getByLabel('Wit').fill('500');

        // The bar is the entered number against the cap, with the number printed beside it.
        await expect(page.locator('[data-stat="Speed"] [data-fill]')).toHaveAttribute('data-fill', '100');
        await expect(page.getByText('1200 of 1200')).toBeVisible();

        await page.getByLabel('Skill name').fill('Silent Hunter');
        await page.getByRole('button', { name: 'Add', exact: true }).click();
        await page.getByLabel('Skill name').fill('Corner Adept');
        await page.getByRole('button', { name: 'Add', exact: true }).click();
        await expect(page.locator('[data-skill-row]')).toHaveCount(2);

        await page.getByRole('button', { name: 'Save target' }).click();

        // The PUT redirects back to this step and the stored state is read from the draft.
        await expect(page.getByText('Build target saved.')).toBeVisible();
        await expect(page.getByLabel('Purpose')).toHaveValue('StoryClear');
        await expect(page.getByLabel('Speed')).toHaveValue('1200');
        await expect(page.locator('[data-skill-row]').nth(0)).toContainText('Silent Hunter');

        const summary = page.getByRole('region', { name: 'Summary' });
        await expect(summary).toContainText('Purpose Story Clear');
        await expect(summary).toContainText('Speed 1200');
        await expect(summary).toContainText('skills in order: Silent Hunter, Corner Adept');
    });

    test('refuses a stat above the cap, ties the message to its input and moves focus there', async ({ page }) => {
        await page.goto('/career/setup/target');
        await page.locator('#app > *').first().waitFor();

        // Everything valid except one stat above the base ceiling, so the refusal under test is the
        // only one on the page and the focus path has exactly one candidate.
        await page.getByLabel('Purpose').selectOption({ label: 'Story Clear' });
        await page.getByLabel('Distance').selectOption({ label: 'Medium' });
        await page.getByLabel('Surface').selectOption({ label: 'Turf' });
        await page.getByLabel('Style').selectOption({ label: 'Front Runner' });
        await page.getByLabel('Speed').fill('1201');
        await page.getByLabel('Stamina').fill('800');
        await page.getByLabel('Power').fill('700');
        await page.getByLabel('Guts').fill('600');
        await page.getByLabel('Wit').fill('500');

        await page.getByRole('button', { name: 'Save target' }).click();

        // The server is the authority on the clamp: the message names the bound it enforced and is
        // referenced from the input that caused it.
        await expect(page.getByText('Speed must be between 0 and 1200.')).toBeVisible();
        await expect(page.getByLabel('Speed')).toHaveAttribute('aria-describedby', 'error-targets_Speed');
        await expect(page.getByLabel('Speed')).toBeFocused();

        // Nothing was stored, so the page still states the absence.
        await expect(page.getByText('No target is recorded yet.')).toBeVisible();
    });

    test('reorders the skill priorities by keyboard and never requires a drag', async ({ page }) => {
        await page.goto('/career/setup/target');
        await page.locator('#app > *').first().waitFor();

        await page.getByLabel('Skill name').fill('Silent Hunter');
        await page.getByRole('button', { name: 'Add', exact: true }).click();
        await page.getByLabel('Skill name').fill('Corner Adept');
        await page.getByRole('button', { name: 'Add', exact: true }).click();

        const rows = page.locator('[data-skill-row]');
        await expect(rows.nth(0)).toContainText('Silent Hunter');
        await expect(rows.nth(1)).toContainText('Corner Adept');

        // Move the second entry up with the keyboard alone (WCAG 2.5.7: no dragging path required).
        const moveUp = page.getByRole('button', { name: 'Move Corner Adept up' });
        await moveUp.focus();
        await expect(moveUp).toBeFocused();
        await page.keyboard.press('Enter');
        await expect(rows.nth(0)).toContainText('Corner Adept');
        await expect(rows.nth(1)).toContainText('Silent Hunter');

        // The first row's Move up is out of action at the edge, and moving down restores the order.
        await expect(page.getByRole('button', { name: 'Move Corner Adept up' })).toBeDisabled();
        const moveDown = page.getByRole('button', { name: 'Move Corner Adept down' });
        await moveDown.focus();
        await page.keyboard.press('Enter');
        await expect(rows.nth(0)).toContainText('Silent Hunter');

        await page.getByRole('button', { name: 'Remove Silent Hunter' }).click();
        await expect(rows).toHaveCount(1);
        await expect(rows.nth(0)).toContainText('Corner Adept');
    });

    test('sizes every step-3 control to the 44px contract', async ({ page }) => {
        await page.goto('/career/setup/target');
        await page.locator('#app > *').first().waitFor();

        const controls = await page.locator('main a, main button, main input, main select').all();

        expect(controls.length).toBeGreaterThan(10);

        for (const control of controls) {
            const box = await control.boundingBox();
            const label = ((await control.textContent()) ?? '').trim();
            expect(box?.height ?? 0, `"${label}" is not sized to 44px`).toBeGreaterThanOrEqual(44);
        }
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
        await page.goto('/career/setup/target');
        await page.locator('#app > *').first().waitFor();

        const overflow = await page.evaluate(
            () => document.documentElement.scrollWidth <= document.documentElement.clientWidth + 1,
        );
        expect(overflow, 'the document scrolls horizontally at 320px').toBe(true);

        await expect(page.getByRole('button', { name: 'Save target' })).toBeVisible();
        await expect(page.getByRole('img', { name: 'Calculated' })).toBeVisible();

        expect(consoleErrors, `console errors: ${consoleErrors.join(' | ')}`).toEqual([]);
    });
});
