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

/**
 * The table row that names the trainee.
 *
 * Slice 3.4 replaced the card list with a `DataTable`, so a row is a `<tr>` and the name is a
 * `<span>` rather than an `<h2>`. Addressed through the row role and the name text, not through the
 * heading, which no longer exists on this page.
 */
function card(page: import('@playwright/test').Page, name: string) {
    return page.getByRole('row').filter({ hasText: name }).filter({ has: page.getByRole('button') });
}

/**
 * The carried-choices strip, addressed by its own `data-field` hook.
 *
 * The strip replaced the per-step "Scenario: ..." / "Stored choice: ..." lines (slice 3.2), and its
 * docblock makes the vocabulary a decision rather than an accident: a draft choice that has not been
 * made yet is *not* an absence. It renders "not chosen" in muted text and deliberately does not use
 * `AbsenceValue` or the `N/A` marker, because nothing failed, no source went silent, and there is no
 * reason to state beyond "this step has not been reached". The three cases below therefore pin the
 * strip's own words — "not chosen" while empty, the trainee's name once set — and must not go looking
 * for `N/A` or a named `group` on this node. That was the retired contract.
 */
function choicesSoFar(page: import('@playwright/test').Page) {
    return page.locator('[data-field="choices-so-far"]');
}

/**
 * The trainee segment of the strip: the one top-level `<span>` whose text starts with the label.
 *
 * Measured on the served page (empty draft): `<span>Trainee: <span class="text-ink-muted">not
 * chosen</span></span>`, between two `aria-hidden` middots. Once set, the inner node becomes
 * `<a href="/career/setup/trainee">Admire Vega</a>` — the strip links a chosen value and leaves an
 * unchosen one as plain text. The `^` anchor is what keeps this segment apart from its two siblings
 * and from the nested `not chosen` spans.
 */
function traineeChoice(page: import('@playwright/test').Page) {
    return choicesSoFar(page).locator('span').filter({ hasText: /^Trainee: / });
}

test.describe('Trainee Selection and Profile', () => {
    test('renders the roster with the wizard shell and no implied choice', async ({ page }) => {
        await page.goto('/career/setup/trainee');
        await page.locator('#app > *').first().waitFor();

        // One card per trainee, name first and the Japanese name beside it in its own language span.
        const first = card(page, 'Admire Vega');
        await expect(first).toBeVisible();
        await expect(first.locator('span[lang="ja"]')).toHaveText('アドマイヤベガ');

        // The aptitude badges state the axis and the band in words, never colour alone (`design-2.0`
        // §17). The seeded roster publishes all ten letters (the seeder replays the committed
        // `gametora-characters` export), so every badge carries a real letter and its band rather than
        // the absence words: Admire Vega's six full-weight badges are Turf A, Dirt G, Sprint F, Mile C,
        // Medium A and Long C, and her four style badges sit behind the count-summary. A badge that
        // read its axis by the wrong key printed "N/A / Not published" on every row while the columns
        // held letters, which is the regression this pins.
        await expect(first.getByText('Turf', { exact: true })).toBeVisible();
        // The table shows the primary axes; the full list lives on the profile, which is the screen
        // built to read one trainee rather than compare many.
        // The table prints every axis, read from the row's own text rather than assumed: Turf A,
        // Dirt G, Sprint F, Mile C, Medium A, Long C are the six shown by the aptitude cell, and the
        // style axis sits behind the same disclosure the card used. The counts below are the DOM's,
        // measured, not derived: an earlier version of this rewrite guessed "three primary axes" and
        // the guess was wrong.
        await expect(first.getByText('Turf', { exact: true })).toBeVisible();
        await expect(first.getByText('Strong', { exact: true })).toHaveCount(2);
        await expect(first.getByText('Neutral', { exact: true })).toHaveCount(2);
        await expect(first.getByText('Very weak', { exact: true })).toHaveCount(2);
        await expect(first.getByText('N/A', { exact: true })).toHaveCount(0);
        await expect(first.getByText('Not published', { exact: true })).toHaveCount(0);

        // Two actions per card: the read one and the primary one.
        await expect(first.getByRole('button', { name: 'Select', exact: true })).toBeVisible();
        await expect(first.getByRole('link', { name: 'Profile' })).toBeVisible();

        // The wizard shell: step 2 is the current step, and since D7 all six steps are live links, so
        // the step nav holds no named absence. The back breadcrumb also has a "Trainee" link, so we
        // scope to the Setup steps nav to avoid strict-mode violations.
        await expect(page.getByRole('navigation', { name: 'Setup steps' }).getByRole('link', { name: 'Trainee' })).toHaveAttribute('aria-current', 'step');
        await expect(page.getByText('Step 2 of 6').first()).toBeVisible();
        await expect(page.getByRole('navigation', { name: 'Setup steps' }).getByText('not built')).toHaveCount(0);

        // Nothing is chosen on a fresh draft, so the carried-choices strip says so in words and no row
        // is pressed. The per-step "Stored choice:" absence left with slice 3.2, and the strip states
        // a draft gap as "not chosen" rather than as an `N/A` disclosure (see the helper above).
        const strip = choicesSoFar(page);
        await expect(strip.locator('span').filter({ hasText: /^Trainee: / })).toHaveCount(1);
        await expect(strip.getByText('not chosen', { exact: true })).toHaveCount(3);
        await expect(strip.getByText(/^Trainee: not chosen$/)).toHaveCount(1);
        await expect(strip.getByText('N/A', { exact: true })).toHaveCount(0);
        await expect(page.getByRole('button', { name: 'Select', exact: true })).toHaveCount(25);
        await expect(page.getByText('67 trainees match.')).toBeVisible();
    });

    test('a selected trainee can be deselected back to no choice', async ({ page }) => {
        await page.goto('/career/setup/trainee');
        await page.locator('#app > *').first().waitFor();

        // The button's own id is read rather than guessed: the costume-form picker on the same card is
        // `trainee-form-{id}`, so a prefix selector would resolve to two controls at once.
        const firstButton = page.getByRole('button', { name: 'Select', exact: true }).first();
        const buttonId = await firstButton.getAttribute('id');
        expect(buttonId, 'the row names its own action').toMatch(/^trainee-\d+$/);

        const action = page.locator(`#${buttonId}`);

        await action.click();
        await expect(action).toHaveAttribute('aria-pressed', 'true');
        await expect(action).toHaveText('Chosen · clear');
        // The chosen trainee replaces the muted empty word with her name, inside the same segment.
        await expect(traineeChoice(page)).toHaveText('Trainee: Admire Vega');
        await expect(traineeChoice(page).getByRole('link', { name: 'Admire Vega' })).toHaveAttribute(
            'href',
            '/career/setup/trainee',
        );

        await action.click();
        await expect(action).toHaveAttribute('aria-pressed', 'false');
        await expect(action).toHaveText('Select');
        await expect(traineeChoice(page)).toHaveText('Trainee: not chosen');
        await expect(traineeChoice(page).getByRole('link')).toHaveCount(0);
        await expect(page.getByText('Trainee cleared.')).toBeVisible();
    });

    test('a trainee who holds more than one costume form is asked which one the career starts on (R3-06)', async ({ page }) => {
        await page.goto('/career/setup/trainee');
        await page.locator('#app > *').first().waitFor();

        await page.getByLabel('Search').fill('Rice Shower');
        await page.getByRole('button', { name: 'Filter', exact: true }).click();

        const riceShower = card(page, 'Rice Shower');
        await expect(riceShower).toBeVisible();

        // The step asks only where there is a choice, exactly once per card, and the options are the
        // catalog's own form titles plus the stated absence: "Not stated" is a real answer, not a default
        // that would name a form nobody picked (R3-06, walk 3's `[Rosy Dreams]` gap).
        //
        // Addressed by its own id rather than by `getByLabel('Costume form')`: the readable name there
        // depended on the label being wired to the control, and the page's label is a `<label for=...>`
        // wrapping a `BaseSelect` that renders its own `<select>` with the same id. The select is the
        // only element that id can reach, and `trainee-form-` is what separates it from `trainee-{id}`.
        await expect(riceShower.locator('select[id^="trainee-form-"]')).toHaveCount(1);
        const picker = page.locator('select[id^="trainee-form-"]').first();
        await expect(picker).toBeVisible();
        await expect(picker.locator('option', { hasText: 'Not stated' })).toHaveCount(1);
        expect(await picker.locator('option').count(), 'a multi-form trainee offers her own forms')
            .toBeGreaterThanOrEqual(3);

        // One picker on the whole page, not one per row: only the trainees who hold more than one form
        // are asked, and the filtered roster here is a single row. This original scope is the point of
        // the case, and it is the assertion that was red.
        await expect(page.locator('select[id^="trainee-form-"]')).toHaveCount(1);

        // The question is still named for a screen reader, inside the row rather than only by the
        // wrapping label: the visible text "Costume form" is the row's own label node.
        await expect(riceShower.getByText('Costume form', { exact: true })).toBeVisible();
    });

    test('narrows the roster with the search and facet filters, states the empty match, then clears back', async ({ page }) => {
        await page.goto('/career/setup/trainee');
        await page.locator('#app > *').first().waitFor();

        await page.getByLabel('Search').fill('biwa');
        await page.getByRole('button', { name: 'Filter', exact: true }).click();

        await expect(page.getByRole('row').filter({ hasText: 'Biwa Hayahide' })).toBeVisible();
        await expect(page.getByRole('button', { name: 'Select', exact: true })).toHaveCount(1);

        // The distance facet is a real column and composes with the search: Biwa publishes no G among
        // her four distance letters, so the answer is an empty roster, stated rather than blank.
        await page.getByLabel('Distance').selectOption('G');
        await page.getByRole('button', { name: 'Filter', exact: true }).click();
        await expect(page.getByText('No trainee matches these filters.')).toBeVisible();

        await page.getByRole('button', { name: 'Clear filters', exact: true }).click();
        await expect(page.getByRole('button', { name: 'Select', exact: true })).toHaveCount(25);
    });

    test('refuses a bad filter value and states the reason where it was typed', async ({ page }) => {
        // A refused filter is the honest answer: the request redirects back and re-renders with the
        // message, and the roster is unfiltered rather than narrowed as though Z had been applied.
        await page.goto('/career/setup/trainee?surface=Z');
        await page.locator('#app > *').first().waitFor();

        await expect(page.getByRole('alert')).toContainText('Aptitude letters are S, A, B, C, D, E, F and G.');
        await expect(page.getByRole('button', { name: 'Select', exact: true })).toHaveCount(25);
    });

    test('selects a trainee by keyboard, restores focus, and keeps the choice across the profile screen', async ({ page }) => {
        await page.goto('/career/setup/trainee');
        await page.locator('#app > *').first().waitFor();

        const unnamed = card(page, 'Admire Vega').getByRole('button', { name: 'Select', exact: true });
        const actionId = await unnamed.getAttribute('id');
        // From here the row is addressed by its own id: its label is the thing under test, so a locator
        // built from "Select Trainee" resolves to nothing once the row becomes the chosen one.
        const action = page.locator(`#${actionId}`);

        await expect(action).toHaveAttribute('aria-pressed', 'false');
        await expect(traineeChoice(page)).toHaveText('Trainee: not chosen');

        await action.focus();
        await expect(action).toBeFocused();
        await page.keyboard.press('Enter');

        // The PUT re-renders from the session, so the state is read off stored data rather than client
        // memory. Focus is restored to the action that was pressed (WCAG 2.4.3), which the redirect
        // would otherwise lose.
        await expect(page.getByText('Trainee set: Admire Vega.')).toBeVisible();
        await expect(action).toHaveAttribute('aria-pressed', 'true');
        await expect(action).toHaveText('Chosen · clear');
        await expect(traineeChoice(page)).toHaveText('Trainee: Admire Vega');
        await expect(action).toBeFocused();

        // The profile screen is the second half of the slice, and it reads the same draft back, so the
        // stored choice survives the move between the two screens.
        await card(page, 'Admire Vega').getByRole('link', { name: 'Profile' }).click();
        await page.waitForURL(/\/career\/setup\/trainee\/\d+$/);

        // The profile has no table: the roster's row role does not exist here. The page's own
        // heading carries the name, and the footer button carries the chosen state.
        await expect(page.getByRole('heading', { name: /Admire Vega/ }).first()).toBeVisible();
        await expect(page.getByRole('button', { name: 'Select this trainee', exact: true })).toHaveAttribute('aria-pressed', 'true');
        await expect(page.getByText('Chosen')).toBeVisible();

        await page.getByRole('link', { name: 'Back to the roster' }).click();
        await page.waitForURL(/\/career\/setup\/trainee$/);

        await expect(page.locator(`#${actionId}`)).toHaveAttribute('aria-pressed', 'true');
    });

    test('renders the same success flash when the card is clicked (R3-31)', async ({ page }) => {
        await page.goto('/career/setup/trainee');
        await page.locator('#app > *').first().waitFor();

        // R3-31 was filed as a keyboard-only defect and it was not one. The confirmation is a session
        // flash, and the artwork route ran inside the `web` group until this slice, so a portrait request
        // landing between the redirect and this render aged the flash away; the roster carries twelve
        // portraits, which is why the race was lost reliably on both input paths. The keyboard case above
        // was the only case asserting the flash, so this pins the pointer path it never covered.
        await card(page, 'Admire Vega').getByRole('button', { name: 'Select', exact: true }).click();

        await expect(page.getByText('Trainee set: Admire Vega.')).toBeVisible();
        await expect(page.locator('[data-field="choices-so-far"]')).toContainText('Admire Vega');
    });

    test('renders the profile with basic facts, one aptitude grid and skills at card grain', async ({ page }) => {
        await page.goto('/career/setup/trainee/33');
        await page.locator('#app > *').first().waitFor();

        // Basic: the facts the profile document holds, with no row for a fact it does not.
        await expect(page.getByRole('heading', { name: 'Basic' })).toBeVisible();
        await expect(page.getByText('Mar 12, 1996')).toBeVisible();
        await expect(page.getByText('157 cm')).toBeVisible();
        await expect(page.getByText('85 · 82 · 57 cm')).toBeVisible();
        await expect(page.getByText('bust · height · waist')).toBeVisible();
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

        // The Build analysis section is GONE (slice 3.4). It held three absences plus "stated as
        // aptitude above", which this page already says one section up, and a recommendation panel on
        // a selection screen reads as advice about the trainee rather than a fact about her. The one
        // durable sentence it carried (why there is no goal ladder here) survives as prose.
        await expect(page.getByRole('heading', { name: 'Build analysis' })).toHaveCount(0);
        await expect(page.locator('[title*="Recommending a stat distribution"]')).toHaveCount(0);
        await expect(page.locator('[title*="carries no umamusume_id by ruling"]')).toHaveCount(0);

        // The version absence survives and still carries its reason.
        await expect(page.locator('[title*="No column records a version"]')).toHaveText('N/A');

        // The kept sentence still explains the absent goal ladder, and names where the ladder lives.
        await expect(page.getByText(/no career-goal section by design/)).toBeVisible();

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
        // This sweep has the collapsed-disclosure hole KI-92 fixed: a control inside a closed
        // `<details>` has no box and reads as 0. The trainee step holds no `<details>` today; when one
        // lands, move the sweep to `expectTapTargets` in `tests/utils/tap-targets.ts`.
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
        await expect(page.getByRole('button', { name: 'Select', exact: true }).first()).toBeVisible();

        expect(consoleErrors, `console errors: ${consoleErrors.join(' | ')}`).toEqual([]);
    });
});
