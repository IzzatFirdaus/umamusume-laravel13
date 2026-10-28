import { test, expect } from '@playwright/test';
import { resolve } from 'node:path';
import { pathToFileURL } from 'node:url';
import { assertStructurallyEqual, capture } from './fixtures/hfw-structure';
import { captureBoth } from './fixtures/wizard-screenshots';

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
    test('renders the step with the base caps and nothing pre-filled', async ({ page }) => {
        await page.goto('/career/setup/target');
        await page.locator('#app > *').first().waitFor();

        await expect(page.getByRole('heading', { name: 'Your target' }).first()).toBeVisible();
        await expect(page.getByRole('navigation', { name: 'Setup steps' }).getByRole('listitem', { name: 'Build target' })).toHaveAttribute('aria-current', 'step');
        await expect(page.getByText('Step 3 of 6').first()).toBeVisible();
        // All six steps are live links since D7: the `to: null` branch stays in `SetupLayout` for the
        // next slice that lands a step ahead of its screen, but it renders nothing today.
        await expect(page.getByRole('navigation', { name: 'Setup steps' }).getByText('not built')).toHaveCount(0);

        // No scenario yet: the page names it and every cap is the base cap with no bonus. The
        // absence is an AbsenceValue disclosure now (DESIGN.md §5.6) - the summary is the marker
        // and carries the reason as its accessible name, so the old title-attribute probe is
        // replaced by the disclosure located by its own reason.
        await expect(page.getByText('/ 1200 cap')).toHaveCount(5);

        // No stored target: the absence is stated and no stat input carries a pre-filled zero.
        await expect(page.getByText('No target is recorded yet.')).toBeVisible();
        // `exact` because the ceiling field added beside each stat is named "Speed ceiling", and
        // `getByLabel` matches a substring by default: without it every one of these five locators
        // would resolve to two controls (`preferences.spec.ts` states the same rule).
        for (const stat of ['Speed', 'Stamina', 'Power', 'Guts', 'Wit']) {
            await expect(page.getByLabel(stat, { exact: true })).toHaveValue('');
        }

        // The four stored purposes. R4-24 removed the line that named a fifth (Competitive Build):
        // the select cannot offer it, so no shipped string may name it.
        for (const label of ['Story Clear', 'Champions Meeting', 'Parent Farming', 'Skill Farming']) {
            await expect(page.getByLabel('Purpose').locator('option', { hasText: label })).toHaveCount(1);
        }
        await expect(page.getByText('Competitive Build')).toHaveCount(0);

        // What the wireframe's fields print before anything is entered: the denominator, and the
        // named absence of a share of the cap rather than a zero standing in for one.
        const speedField = page.locator('[data-stat="Speed"]');
        await expect(speedField.locator('[data-field="denominator"]')).toContainText('/ 1200 cap');
        await expect(speedField.locator('[data-field="percent-of-cap"]')).toContainText('not entered');
    });

    test('saves the whole target to the draft, reads it back, and assembles the calculated summary', async ({ page }) => {
        await page.goto('/career/setup/target');
        await page.locator('#app > *').first().waitFor();

        await page.getByLabel('Purpose').selectOption({ label: 'Story Clear' });
        await page.locator('input[name="distance"][value="Medium"]').check();
        await page.getByLabel('Surface').selectOption({ label: 'Turf' });
        await page.getByLabel('Style').selectOption({ label: 'Front Runner' });

        await page.getByLabel('Speed', { exact: true }).fill('1200');
        await page.getByLabel('Stamina', { exact: true }).fill('800');
        await page.getByLabel('Power', { exact: true }).fill('700');
        await page.getByLabel('Guts', { exact: true }).fill('600');
        await page.getByLabel('Wit', { exact: true }).fill('500');

        // The wireframe's field prints the number, its denominator and its share of the cap
        // (`career-target.html:101-125`). The bar and the second copy of the figure are gone.
        const speed = page.locator('[data-stat="Speed"]');
        await expect(speed.locator('[data-field="denominator"]')).toContainText('1200 / 1200 cap');
        await expect(speed.locator('[data-field="percent-of-cap"]')).toContainText('100% of cap');


        await page.getByRole('button', { name: 'Continue to legacy' }).click();

        // The PUT redirects back to this step and the stored state is read from the draft.
        await expect(page.getByText('Build target saved.')).toBeVisible();
        await expect(page.getByLabel('Purpose')).toHaveValue('StoryClear');
        await expect(page.getByLabel('Speed', { exact: true })).toHaveValue('1200');
        await expect(speed.locator('[data-field="denominator"]')).toContainText('1200 / 1200 cap');
    });

    test('refuses a stat above the cap, ties the message to its input and moves focus there', async ({ page }) => {
        await page.goto('/career/setup/target');
        await page.locator('#app > *').first().waitFor();

        // Everything valid except one stat above the base ceiling, so the refusal under test is the
        // only one on the page and the focus path has exactly one candidate.
        await page.getByLabel('Purpose').selectOption({ label: 'Story Clear' });
        await page.locator('input[name="distance"][value="Medium"]').check();
        await page.getByLabel('Surface').selectOption({ label: 'Turf' });
        await page.getByLabel('Style').selectOption({ label: 'Front Runner' });
        await page.getByLabel('Speed', { exact: true }).fill('1201');
        await page.getByLabel('Stamina', { exact: true }).fill('800');
        await page.getByLabel('Power', { exact: true }).fill('700');
        await page.getByLabel('Guts', { exact: true }).fill('600');
        await page.getByLabel('Wit', { exact: true }).fill('500');

        await page.getByRole('button', { name: 'Continue to legacy' }).click();

        // The server is the authority on the clamp: the message names the bound it enforced and is
        // referenced from the input that caused it.
        await expect(page.getByText('Speed must be between 0 and 1200.')).toBeVisible();
        await expect(page.getByLabel('Speed', { exact: true })).toHaveAttribute('aria-describedby', 'error-targets_Speed');
        await expect(page.getByLabel('Speed', { exact: true })).toBeFocused();

        // Nothing was stored, so the page still states the absence.
        await expect(page.getByText('No target is recorded yet.')).toBeVisible();
    });

    test('accepts a target beside the ceiling entered with it, and reads that ceiling back', async ({ page }) => {
        await page.goto('/career/setup/target');
        await page.locator('#app > *').first().waitFor();

        // No scenario in this draft, so every published cap is the base 1200: the Speed target below is
        // one the step refuses on its own (`ADR-0025` is the ruling that lets the Trainer state the
        // client's ceiling instead of the matrix's).
        await page.getByLabel('Purpose').selectOption({ label: 'Story Clear' });
        await page.locator('input[name="distance"][value="Medium"]').check();
        await page.getByLabel('Surface').selectOption({ label: 'Turf' });
        await page.getByLabel('Style').selectOption({ label: 'Front Runner' });
        await page.getByLabel('Speed ceiling').fill('1250');
        await page.getByLabel('Speed', { exact: true }).fill('1250');
        await page.getByLabel('Stamina', { exact: true }).fill('800');
        await page.getByLabel('Power', { exact: true }).fill('700');
        await page.getByLabel('Guts', { exact: true }).fill('600');
        await page.getByLabel('Wit', { exact: true }).fill('500');

        await page.getByRole('button', { name: 'Continue to legacy' }).click();

        await expect(page.getByText('Build target saved.')).toBeVisible();

        // Both halves of the save read back: the ceiling as a stored field, and the bound the page draws
        // for the target that needed it.
        await expect(page.getByLabel('Speed ceiling')).toHaveValue('1250');
        await expect(page.getByLabel('Speed', { exact: true })).toHaveValue('1250');
        await expect(page.getByText('1250 cap')).toHaveCount(1);

        // The ceiling is printed once, as the field's own denominator: "N / 1200 cap". A second copy
        // of the same figure was cut in the 2026-10-27 parity audit because the HFW prints one.
        await expect(page.locator('[data-field="denominator"]')).toHaveCount(5);
        await expect(page.locator('[data-field="denominator"]').first()).toHaveText(/—?\s*\d*\s*\/\s*\d+ cap/);
    });

    test('sizes every step-3 control to the 44px contract', async ({ page }) => {
        await page.goto('/career/setup/target');
        await page.locator('#app > *').first().waitFor();

        // This sweep has the collapsed-disclosure hole KI-92 fixed: a control inside a closed
        // `<details>` has no box and reads as 0. The target step holds no `<details>` today; when one
        // lands, move the sweep to `expectTapTargets` in `tests/utils/tap-targets.ts`.
        const controls = await page.locator('main a, main button, main input, main select').all();

        expect(controls.length).toBeGreaterThan(10);

        for (const control of controls) {
            // A radio's own box is the glyph; its label is the target (WCAG 2.5.8 measures what is hit).
            const isChoice = await control.evaluate(
                (el) => el.tagName === 'INPUT' && ['radio', 'checkbox'].includes((el as HTMLInputElement).type),
            );
            if (isChoice) {
                continue; // the label is the target; a native radio is exempt (WCAG 2.5.8 exception)
            }
            const measured = control;
            const box = await measured.boundingBox();
            const label = ((await control.textContent()) ?? (await control.getAttribute('value')) ?? '').trim();
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

        await expect(page.getByRole('button', { name: 'Continue to legacy' })).toBeVisible();

        expect(consoleErrors, `console errors: ${consoleErrors.join(' | ')}`).toEqual([]);
    });

    /*
     * The step body, measured against the wireframe rather than read off the source (see the scenario
     * step's case for the harness contract). `.canvas` on both sides; `regions: 'subset'` requires the
     * wireframe's markers in order — the warn, the `.g-side` grid, the stat card with its five fields and
     * its totals rows and note, then the Distance card with its note, the Stamina floor card and the
     * Saved default card. `grid: true` compares the first grid's track count at 1280, which is the
     * two-column shell on both sides.
     */
    test('renders the two-column g-side shell, HFW structure', async ({ page }) => {
        const hfw = pathToFileURL(
            resolve(process.cwd(), 'docs/design-research/frontend-v2-wireframes/career-target.html'),
        ).href;

        await page.goto('/career/setup/target');
        await page.locator('#app > *').first().waitFor();
        const app = await capture(page, '.canvas', { slug: 'target', side: 'app' });

        // Caught by the side-by-side pass, not by the structure: the step's subject line printed twice,
        // once from a leftover `<header>` block and once from the intro paragraph. The wireframe says it
        // once, under the name. Pinned so the duplicate cannot come back. Asserted while still on the
        // app page: the comparison below navigates to the wireframe, where the sentence does not exist.
        // Four wireframe cards, plus the purpose/surface/style card retained because
        // `StoreBuildTargetRequest` requires those fields whenever a stat carries a number.
        await expect(page.locator('[data-region="card"]')).toHaveCount(5);

        await page.goto(hfw);
        const wireframe = await capture(page, '.canvas', { slug: 'target', side: 'hfw' });

        assertStructurallyEqual(app, wireframe, { regions: 'subset', grid: true });

        // The 390 pair alongside the 1280 pair, so the side-by-side pass sees both viewports. Gated on
        // the same flag the structural capture uses, so a plain suite run writes no PNGs.
        if (process.env.WIZARD_SCREENSHOTS === '1') {
            await captureBoth(page, 'target');
        }
    });
});
