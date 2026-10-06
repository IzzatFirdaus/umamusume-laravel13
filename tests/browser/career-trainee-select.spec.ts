import { test, expect } from '@playwright/test';

/*
 * Rendered-copy, state and accessibility evidence for SCREEN-003 (Trainee Selection) and SCREEN-004
 * (Trainee Profile) (ADR-0020 §1; plan §8 D3). `CareerTraineeSelectTest` asserts the resolved props,
 * the filters and the session draft; these assert what the browser shows and what only a browser can
 * reach: the card copy, the `aria-pressed` state after a real PUT round trip, the keyboard path with
 * focus restore, the 44px sweep, 320px reflow and a clean console.
 *
 * This spec writes no database row. The selection lands in the session draft, which is the point of
 * the mechanism: it leaves the shared scratch database exactly as it found it, and each Playwright
 * test gets a fresh browser context, so each starts with no draft. The roster data it reads is the
 * seeded scratch catalog (67 trainees), and `Admire Vega` is the first row under the default
 * name-ascending sort.
 *
 * Axe coverage is provided by `tests/browser/accessibility.spec.ts`: `@axe-core/playwright` is installed
 * and scans pages against `wcag2a`, `wcag2aa`, and `wcag21aa`; this spec retains the hand-rolled checks
 * for target size, keyboard path, focus order, reflow and console errors.
 */

/** The card that names the trainee in its own heading, wherever the heading's other spans sit. */
function card(page: import('@playwright/test').Page, name: string) {
    return page.getByRole('listitem').filter({ has: page.getByRole('heading', { name }) });
}

test.describe('Trainee Selection and Profile', () => {
    test('renders the roster with the wizard shell and no implied choice', async ({ page }) => {
        await page.goto('/career/setup/trainee');
        await page.locator('#app > *').first().waitFor();

        // One card per trainee, name first and the Japanese name beside it in its own language span.
        const first = card(page, 'Admire Vega');
        await expect(first).toBeVisible();
        await expect(first.locator('span[lang="ja"]')).toHaveText('アドマイヤベガ');

        // The aptitude letters carry their band word as text, not colour alone (`design-2.0` §17).
        await expect(first.getByText('Turf', { exact: true })).toBeVisible();
        await expect(first.getByText('A', { exact: true }).first()).toBeVisible();
        await expect(first.getByText('Very weak').first()).toBeVisible();

        // Two actions per card: the read one and the primary one.
        await expect(first.getByRole('button', { name: 'Select Trainee', exact: true })).toBeVisible();
        await expect(first.getByRole('link', { name: 'View Profile' })).toBeVisible();

        // The wizard shell: step 2 is the current step, and since D7 all six steps are live links, so
        // the step nav holds no named absence.
        await expect(page.getByRole('link', { name: 'Trainee' })).toHaveAttribute('aria-current', 'step');
        await expect(page.getByText('Step 2 of 6').first()).toBeVisible();
        await expect(page.getByRole('navigation', { name: 'Setup steps' }).getByText('not built')).toHaveCount(0);

        // Nothing is chosen on a fresh draft, so the stored-choice line states the absence and no row
        // is pre-pressed.
        await expect(page.getByTitle('No trainee has been chosen in this setup draft yet.')).toHaveText('N/A');
        await expect(page.getByRole('button', { name: 'Select Trainee', exact: true })).toHaveCount(25);
        await expect(page.getByText('67 trainees match.')).toBeVisible();
    });

    test('narrows the roster with the search and facet filters, states the empty match, then clears back', async ({ page }) => {
        await page.goto('/career/setup/trainee');
        await page.locator('#app > *').first().waitFor();

        await page.getByLabel('Search').fill('biwa');
        await page.getByRole('button', { name: 'Filter', exact: true }).click();

        await expect(page.getByRole('heading', { name: 'Biwa Hayahide' })).toBeVisible();
        await expect(page.getByRole('button', { name: 'Select Trainee', exact: true })).toHaveCount(1);

        // The distance facet is a real column and composes with the search: Biwa publishes no G among
        // her four distance letters, so the answer is an empty roster, stated rather than blank.
        await page.getByLabel('Distance').selectOption('G');
        await page.getByRole('button', { name: 'Filter', exact: true }).click();
        await expect(page.getByText('No trainee matches these filters.')).toBeVisible();

        await page.getByRole('button', { name: 'Clear filters', exact: true }).click();
        await expect(page.getByRole('button', { name: 'Select Trainee', exact: true })).toHaveCount(25);
    });

    test('refuses a bad filter value and states the reason where it was typed', async ({ page }) => {
        // A refused filter is the honest answer: the request redirects back and re-renders with the
        // message, and the roster is unfiltered rather than narrowed as though Z had been applied.
        await page.goto('/career/setup/trainee?surface=Z');
        await page.locator('#app > *').first().waitFor();

        await expect(page.getByRole('alert')).toContainText('Aptitude letters are S, A, B, C, D, E, F and G.');
        await expect(page.getByRole('button', { name: 'Select Trainee', exact: true })).toHaveCount(25);
    });

    test('selects a trainee by keyboard, restores focus, and keeps the choice across the profile screen', async ({ page }) => {
        await page.goto('/career/setup/trainee');
        await page.locator('#app > *').first().waitFor();

        const action = card(page, 'Admire Vega').getByRole('button', { name: 'Select Trainee', exact: true });

        await expect(action).toHaveAttribute('aria-pressed', 'false');
        await expect(page.getByTitle('No trainee has been chosen in this setup draft yet.')).toHaveText('N/A');

        await action.focus();
        await expect(action).toBeFocused();
        await page.keyboard.press('Enter');

        // The PUT re-renders from the session, so the state is read off stored data rather than client
        // memory. Focus is restored to the action that was pressed (WCAG 2.4.3), which the redirect
        // would otherwise lose.
        await expect(page.getByText('Trainee set: Admire Vega.')).toBeVisible();
        await expect(action).toHaveAttribute('aria-pressed', 'true');
        await expect(card(page, 'Admire Vega').getByText('Chosen')).toBeVisible();
        await expect(page.getByText('Stored choice:')).toContainText('Admire Vega');
        await expect(action).toBeFocused();

        // The profile screen is the second half of the slice, and it reads the same draft back, so the
        // stored choice survives the move between the two screens.
        await card(page, 'Admire Vega').getByRole('link', { name: 'View Profile' }).click();
        await page.waitForURL(/\/career\/setup\/trainee\/\d+$/);

        await expect(page.getByRole('heading', { name: 'Admire Vega' })).toBeVisible();
        await expect(page.getByRole('button', { name: 'Select this trainee', exact: true })).toHaveAttribute('aria-pressed', 'true');
        await expect(page.getByText('Chosen')).toBeVisible();

        await page.getByRole('link', { name: 'Back to the roster' }).click();
        await page.waitForURL(/\/career\/setup\/trainee$/);

        await expect(card(page, 'Admire Vega').getByRole('button', { name: 'Select Trainee', exact: true }))
            .toHaveAttribute('aria-pressed', 'true');
    });

    test('renders the profile with basic facts, one aptitude grid and skills at card grain', async ({ page }) => {
        await page.goto('/career/setup/trainee/33');
        await page.locator('#app > *').first().waitFor();

        // Basic: the facts the profile document holds, with no row for a fact it does not.
        await expect(page.getByRole('heading', { name: 'Basic' })).toBeVisible();
        await expect(page.getByText('Mar 12, 1996')).toBeVisible();
        await expect(page.getByText('157 cm')).toBeVisible();
        await expect(page.getByText('咲々木瞳')).toBeVisible();
        await expect(page.getByText('Hitomi Sasaki')).toBeVisible();

        // Aptitude appears once for the trainee: ten letters, none of them per costume form.
        const grid = page.locator('dl').filter({ hasText: 'End closer' });
        await expect(grid.locator('dt')).toHaveCount(10);
        await expect(grid.locator('dd')).toHaveCount(10);

        // Rarity is the form's own fact, printed beside the form it belongs to.
        await expect(page.getByRole('heading', { name: 'Costume forms' })).toBeVisible();
        await expect(page.getByText('[Starry Nocturne]')).toBeVisible();
        await expect(page.getByText('SSR')).toBeVisible();
        await expect(page.getByText('Released (Global) Mar 5, 2026')).toBeVisible();

        // Skills, resolved through the Global scope. The unique skill publishes no SP cost, so the row
        // states the absence rather than a number.
        await expect(page.getByRole('heading', { name: 'Unique skill' })).toBeVisible();
        await expect(page.getByText('Shooting Star of Dioskouroi')).toBeVisible();
        await expect(page.getByText('N/A SP')).toBeVisible();

        // Build analysis: the three figures no column can answer are absences with their reasons, and
        // the distance/style reading is labelled as aptitude rather than as a recommendation.
        await expect(page.getByRole('heading', { name: 'Build analysis' })).toBeVisible();
        await expect(page.locator('[title*="No column records a version"]')).toHaveText('N/A');
        await expect(page.locator('[title*="Recommending a stat distribution"]')).toHaveText('N/A');
        await expect(page.locator('[title*="Inheritance is computed at the Legacy step"]')).toHaveText('N/A');
        await expect(page.locator('[title*="carries no umamusume_id by ruling"]')).toHaveText('N/A');

        // Career goals and growth rates are omitted entirely: the schema holds neither, so no heading
        // and no row advertises a fact the tool is known not to have.
        await expect(page.getByRole('heading', { name: 'Career goals' })).toHaveCount(0);
        await expect(page.getByText('Growth rate')).toHaveCount(0);
    });

    test('sizes every trainee control to the 44px contract', async ({ page }) => {
        await page.goto('/career/setup/trainee');
        await page.locator('#app > *').first().waitFor();

        // Every control the roster body renders: both actions on each card, the filter form, the step
        // link and every pagination link.
        const controls = await page.locator('main a, main button, main input, main select').all();

        for (const control of controls) {
            const box = await control.boundingBox();
            const label = ((await control.textContent()) ?? '').trim();
            expect(box?.height ?? 0, `"${label}" is not sized to 44px`).toBeGreaterThanOrEqual(44);
        }

        // The profile's two controls that leave or commit the step.
        await page.goto('/career/setup/trainee/33');
        await page.locator('#app > *').first().waitFor();

        for (const control of [
            page.getByRole('link', { name: 'Back to the roster' }),
            page.getByRole('button', { name: 'Select this trainee', exact: true }),
        ]) {
            const box = await control.boundingBox();
            expect(box?.height ?? 0, 'a profile control is not sized to 44px').toBeGreaterThanOrEqual(44);
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
        await page.goto('/career/setup/trainee');
        await page.locator('#app > *').first().waitFor();

        // The cards stack to one column; the document must not scroll sideways (WCAG 1.4.10).
        const overflow = await page.evaluate(
            () => document.documentElement.scrollWidth <= document.documentElement.clientWidth + 1,
        );
        expect(overflow, 'the document scrolls horizontally at 320px').toBe(true);
        await expect(card(page, 'Admire Vega')).toBeVisible();
        await expect(page.getByRole('button', { name: 'Select Trainee', exact: true }).first()).toBeVisible();

        expect(consoleErrors, `console errors: ${consoleErrors.join(' | ')}`).toEqual([]);
    });
});
