import { test, expect } from '@playwright/test';
import { mkdirSync, writeFileSync } from 'node:fs';
import { join, resolve } from 'node:path';
import { pathToFileURL } from 'node:url';
import { verifyPage } from './fixtures/wizard-verify';

/*
 * Rendered-copy, state and accessibility evidence for SCREEN-003 (Trainee Selection) and SCREEN-004
 * (Trainee Profile) (ADR-0020 section 1; plan section 8 D3). `CareerTraineeSelectTest` asserts the resolved props,
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
 * One trainee card.
 *
 * Slice 3.4 had replaced the card list with a `DataTable`; the wireframe parity pass put the cards back,
 * so a trainee is a `[data-trainee-card]` block again and this helper addresses it by that hook plus the
 * name the card prints. The `first()` is deliberate: the roster may hold two trainees whose names share a
 * substring, and the case that needs a unique card filters the roster down to one row first.
 */
function card(page: import('@playwright/test').Page, name: string) {
    return page.locator('[data-trainee-card]').filter({ hasText: name }).first();
}

/**
 * The carried-choices strip, addressed by its own `data-field` hook.
 *
 * The strip replaced the per-step "Scenario: ..." / "Stored choice: ..." lines (slice 3.2), and its
 * docblock makes the vocabulary a decision rather than an accident: a draft choice that has not been
 * made yet is *not* an absence. It renders "not chosen" in muted text and deliberately does not use
 * `AbsenceValue` or the `N/A` marker, because nothing failed, no source went silent, and there is no
 * reason to state beyond "this step has not been reached". The three cases below therefore pin the
 * strip's own words - "not chosen" while empty, the trainee's name once set - and must not go looking
 * for `N/A` or a named `group` on this node. That was the retired contract.
 */
function choicesSoFar(page: import('@playwright/test').Page) {
    return page.locator('[data-field="selected-trainee"]');
}

/**
 * The trainee segment of the strip: the one top-level `<span>` whose text starts with the label.
 *
 * Measured on the served page (empty draft): `<span>Trainee: <span class="text-ink-muted">not
 * chosen</span></span>`, between two `aria-hidden` middots. Once set, the inner node becomes
 * `<a href="/career/setup/trainee">Admire Vega</a>` - the strip links a chosen value and leaves an
 * unchosen one as plain text. The `^` anchor is what keeps this segment apart from its two siblings
 * and from the nested `not chosen` spans.
 */
function traineeChoice(page: import('@playwright/test').Page) {
    return page.locator('[data-field="selected-trainee"] p').first();
}

test.describe('Trainee Selection and Profile', () => {
    test('carries the step doors in the head band, not at the foot of the roster', async ({ page }) => {
        // Phase A.2 moved both doors into `.hrow-actions`, where `career-trainee.html:61-65` draws them.
        // The forward control used to sit after the last card (R4-22), which is the position this case
        // used to assert and no longer exists.
        await page.goto('/career/setup/trainee');
        await page.locator('#app > *').first().waitFor();

        const forward = page.getByRole('link', { name: 'Continue to build target' });
        await expect(forward).toBeVisible();
        await expect(forward).toHaveAttribute('href', /\/career\/setup\/target$/);
        await expect(forward).toHaveClass(/bg-primary-action/);

        const back = page.getByRole('link', { name: 'Back', exact: true });
        await expect(back).toBeVisible();
        await expect(back).toHaveAttribute('href', /\/career\/setup\/scenario$/);

        // One filled action on the screen, and it is the forward door.
        await expect(page.locator('.bg-primary-action, [data-variant="primary"]')).toHaveCount(1);
    });

    test('draws the wireframe filter row: five controls and a clear, with no submit', async ({ page }) => {
        // `career-trainee.html:88-104`. The row had a `Unique skill` select, an `Order` select and a
        // `Filter` submit; the wireframe has none of the three, and the roster now filters as it is read.
        await page.goto('/career/setup/trainee');
        await page.locator('#app > *').first().waitFor();

        const filters = page.locator('[data-region="filters"]');

        // Exactly five controls: the search, the three aptitude facets and the sort.
        await expect(filters.locator('input, select')).toHaveCount(5);
        await expect(filters.getByLabel('Search')).toBeVisible();
        await expect(filters.getByLabel('Surface')).toBeVisible();
        await expect(filters.getByLabel('Distance')).toBeVisible();
        await expect(filters.getByLabel('Running style')).toBeVisible();
        await expect(filters.getByLabel('Sort')).toBeVisible();
        await expect(filters.getByRole('button', { name: 'Clear', exact: true })).toBeVisible();

        // The three controls the wireframe does not draw are gone, not hidden.
        await expect(page.getByLabel('Unique skill')).toHaveCount(0);
        await expect(page.getByLabel('Order')).toHaveCount(0);
        await expect(filters.getByRole('button', { name: 'Filter', exact: true })).toHaveCount(0);
        await expect(page.getByRole('button', { name: 'Filter', exact: true })).toHaveCount(0);
    });

    test('pages with Previous and Next, and states the live denominator', async ({ page }) => {
        // `career-trainee.html:180-186` draws two doors rather than a numbered strip.
        await page.goto('/career/setup/trainee');
        await page.locator('#app > *').first().waitFor();

        const pager = page.locator('[data-region="pager"]');
        await expect(pager.getByText('Previous', { exact: true })).toBeVisible();
        await expect(pager.getByText('Next', { exact: true })).toBeVisible();

        // Page one: Previous is the disabled twin, so it is not a link.
        await expect(pager.getByRole('link', { name: 'Previous' })).toHaveCount(0);
        await expect(pager.getByRole('link', { name: 'Next' })).toBeVisible();

        // No numbered page links survive the cut.
        await expect(pager.locator('a[aria-current="page"]')).toHaveCount(0);

        // The denominator line reads "N trainees · page 1 of M · sorted by Name", and N is the roster's
        // live total rather than the page's own row count.
        const denominator = page.locator('[data-field="denominator"]');
        await expect(denominator).toContainText(/^\d+ trainees · page 1 of \d+ · sorted by Name$/);
        await expect(page.getByRole('button', { name: 'Select', exact: true })).toHaveCount(25);
        await expect(denominator).toContainText('trainees');
    });

    test('renders the roster with the wizard shell and no implied choice', async ({ page }) => {
        await page.goto('/career/setup/trainee');
        await page.locator('#app > *').first().waitFor();

        // One card per trainee, name first and the Japanese name beside it in its own language span.
        const first = card(page, 'Admire Vega');
        await expect(first).toBeVisible();
        await expect(first.locator('span[lang="ja"]')).toHaveText('アドマイヤベガ');

        // The three aptitude rows the wireframe draws (`career-trainee.html:127-129`): Surface, Distance
        // and Style, one row each, every letter stated as text. Admire Vega publishes all ten letters -
        // Turf A, Dirt G / Sprint F, Mile C, Medium A, Long C / Front runner G, Pace chaser G, Late surger
        // B, End closer A - and the strongest of each row carries the bold weight, so the comparison reads
        // without colour (design-2.0 §17).
        // The row names are the axis families the controller sends, capitalised by CSS rather than by a
        // second copy of the words in the page, so the DOM text stays the family name and the transform is
        // what makes it read "Surface". Both are asserted: a dropped class would leave the rows lowercase.
        await expect(first.locator('dl > dt')).toHaveText(['surface', 'distance', 'style']);
        await expect(first.locator('dl > dt').first()).toHaveCSS('text-transform', 'capitalize');
        await expect(first.getByText('Turf A', { exact: true })).toBeVisible();
        await expect(first.getByText('Dirt G', { exact: true })).toBeVisible();
        await expect(first.getByText('End closer A', { exact: true })).toBeVisible();
        // The strongest of each row is the bold one, and it is the row's best letter: Turf A over Dirt G,
        // Medium A over Sprint F / Mile C / Long C, End closer A over Late surger B and the two Gs.
        for (const strongest of ['Turf A', 'Medium A', 'End closer A']) {
            await expect(first.getByText(strongest, { exact: true })).toHaveClass(/font-bold/);
        }
        await expect(first.getByText('Dirt G', { exact: true })).not.toHaveClass(/font-bold/);
        await expect(first.getByText('N/A', { exact: true })).toHaveCount(0);

        // One action per card: the pick control. The card is the whole target and its only read
        // affordance is the `Preview profile` control on the Selected card below, so the per-card
        // `Profile` link the wireframe does not draw is asserted absent rather than merely unasserted.
        await expect(first.getByRole('button', { name: 'Select', exact: true })).toBeVisible();
        await expect(first.getByRole('link', { name: 'Profile', exact: true })).toHaveCount(0);
        await expect(first.getByRole('link')).toHaveCount(0);

        // The card's own ceiling, measured rather than assumed.
        //
        // HFW-OVERRIDE (markup: per-letter spans): the wireframe's card is 23 descendants — it packs each
        // aptitude row into one `<td>` of text with a single `<b>` around the strongest letter. This card
        // is 38, because the strongest letter of every row carries its own `<span>` so the comparison reads
        // without colour (design-2.0 §17), and three rows of 2/4/4 letters become 18 spans plus their
        // separators. The ceiling below is therefore a regression guard on this card's shape, not a
        // wireframe measurement; what the controls and the rows are is pinned exactly, above.
        expect(await first.locator('*').count(), 'card descendant count').toBeLessThan(45);
        expect(await first.locator('dl > dt').count(), 'three aptitude rows').toBe(3);
        expect(await first.locator('[data-region^="pill-"]').count(), 'three pills').toBe(3);
        expect(((await first.textContent()) ?? '').trim().length, 'card text length').toBeLessThan(900);

        // The wizard shell: step 2 is the current step, and since D7 all six steps are live links, so
        // the step nav holds no named absence. The back breadcrumb also has a "Trainee" link, so we
        // scope to the Setup steps nav to avoid strict-mode violations.
        await expect(page.getByRole('navigation', { name: 'Setup steps' }).getByRole('listitem', { name: 'Trainee' })).toHaveAttribute('aria-current', 'step');
        await expect(page.getByText('Step 2 of 6').first()).toBeVisible();
        await expect(page.getByRole('navigation', { name: 'Setup steps' }).getByText('not built')).toHaveCount(0);

        // Nothing is chosen on a fresh draft. The carried-choices strip this block used to read was
        // cut in the 2026-10-27 parity audit (no setup wireframe draws one), and the page's own
        // statement of the same fact is the echo card the wireframe draws at the foot of the roster:
        expect(await choicesSoFar(page).count()).toBe(0);
        await expect(page.locator('[data-trainee-card] button[aria-pressed="true"]')).toHaveCount(0);



        await expect(page.getByRole('button', { name: 'Select', exact: true })).toHaveCount(25);
        await expect(page.locator('[data-field="denominator"]')).toContainText('trainees');

        // The wireframe's "Selected" card is absent until a trainee is actually chosen: it restates a pick,
        // and the strip above already says "not chosen" in words. Rendering an empty one would be a second
        // way to say the same absence.
        await expect(page.getByRole('heading', { name: 'Selected' })).toHaveCount(0);
    });

    test('restates the chosen trainee in the Selected card the wireframe draws', async ({ page }) => {
        await page.goto('/career/setup/trainee');
        await page.locator('#app > *').first().waitFor();

        await card(page, 'Admire Vega').getByRole('button', { name: 'Select', exact: true }).click();
        await expect(page.getByText('Trainee set: Admire Vega.')).toBeVisible();

        // `career-trainee.html:191-200`: the heading, her name with the form in brackets, one sentence that
        // names her strongest aptitude on each axis, and the link into the profile screen.
        const selected = page.locator('[data-field="selected-trainee"]');
        await expect(selected.getByText('Admire Vega')).toBeVisible();
        await expect(selected.getByText('[Starry Nocturne]')).toBeVisible();
        await expect(selected.getByText(/Medium A · Turf A · End closer A/)).toBeVisible();
        // The wireframe's sentence names rarity and potential only. "Growth row" is deliberately absent:
        // no column records a growth rate, so promising one would be a claim the schema cannot back.
        await expect(selected.getByText(/sets this run's rarity and potential/)).toBeVisible();
        await expect(selected.getByText(/growth/i)).toHaveCount(0);
        await expect(selected.getByRole('link', { name: 'Preview profile' })).toHaveAttribute(
            'href',
            /\/career\/setup\/trainee\/\d+$/,
        );
    });

    test('restates the pick under the filter row, with her frame and her form choice', async ({ page }) => {
        await page.goto('/career/setup/trainee');
        await page.locator('#app > *').first().waitFor();

        await page.getByLabel('Search').fill('Rice Shower');
        await expect(card(page, 'Rice Shower')).toBeVisible();
        await card(page, 'Rice Shower').getByRole('button', { name: 'Select', exact: true }).click();

        const selected = page.locator('[data-field="selected-trainee"]');
        await expect(selected).toBeVisible();

        // The echo card belongs to the pick, not to the end of the list: it sits between the search-and-sort
        // row and the roster, and the pager stays last. `querySelectorAll` returns document order, so this
        // reads the positions rather than trusting a margin class.
        const order = await page.evaluate(() =>
            Array.from(
                document.querySelectorAll(
                    '.canvas form[data-region="filters"], .canvas [data-field="selected-trainee"], .canvas [data-trainee-card], .canvas [data-region="pager"]',
                ),
            ).map(
                (node) =>
                    node.getAttribute('data-field') ??
                    node.getAttribute('data-region') ??
                    node.tagName.toLowerCase(),
            ),
        );
        expect(order.slice(0, 2)).toEqual(['filters', 'selected-trainee']);
        expect(order.at(-1)).toBe('pager');

        // `ADR-0021`'s display half, and `DESIGN.md` §7: the frame is served by the app's own origin,
        // never by the asset host. Rice Shower's debut form is mirrored on this tree, so the image is
        // asserted rather than the reserve cell.
        const frame = selected.locator('img[data-artwork-slot]');
        await expect(frame).toHaveCount(1);

        const frameSrc = new URL((await frame.getAttribute('src')) ?? '');
        expect(frameSrc.pathname).toMatch(/^\/artwork\/card_portrait\/\d+$/);
        expect(frameSrc.origin, 'the frame comes from the loopback app, not the asset host').toBe(
            new URL(page.url()).origin,
        );

        // Two forms in the catalog, so the card asks which one. `career-trainee.html:191-200` states the
        // form in the name line; the picker is how a Trainer changes the one the draft holds.
        const picker = selected.locator('select#selected-form');
        await expect(picker).toBeVisible();
        await expect(picker.locator('option')).toHaveCount(2);

        const second = (await picker.locator('option').nth(1).textContent())?.trim() ?? '';
        const secondValue = (await picker.locator('option').nth(1).getAttribute('value')) ?? '';
        await picker.selectOption({ index: 1 });

        // The write is the same draft PUT the card row uses, so the run's rarity follows the form stated.
        // The name line is asserted, not the bare words: the option carries the same text, and
        // `getByText` across the card resolves to both controls.
        await expect(page.getByText('Trainee set: Rice Shower.')).toBeVisible();
        await expect(selected.locator('p').first()).toContainText(second);
        await expect(picker).toHaveValue(secondValue);
    });

    /*
     * The roster filters from a 250ms debounce, and Inertia cancels the visit already in flight when a
     * new one starts. Before `standDownFilter()` existed that meant a Trainer who typed a name and
     * clicked a card inside the debounce window had their *write* cancelled by their own filter read:
     * no draft row, no flash, and no echo card, so the screen showed a pick that had not been taken.
     * Measured on this tree as the same click rendering the echo on one run and not the next.
     */
    test('a pick made while the filter read is still pending lands and echoes', async ({ page }) => {
        await page.goto('/career/setup/trainee');
        await page.locator('#app > *').first().waitFor();

        await page.getByLabel('Search').fill('Rice Shower');

        // The click lands inside the debounce window on purpose: that is the race being closed.
        await card(page, 'Rice Shower').getByRole('button', { name: 'Select', exact: true }).click();

        await expect(page.getByText('Trainee set: Rice Shower.')).toBeVisible();
        await expect(page.locator('[data-field="selected-trainee"]')).toBeVisible();
    });

    test('asks no form question of a trainee the catalog holds in one form', async ({ page }) => {
        await page.goto('/career/setup/trainee');
        await page.locator('#app > *').first().waitFor();

        await page.getByLabel('Search').fill('Admire Vega');
        await expect(card(page, 'Admire Vega')).toBeVisible();
        await card(page, 'Admire Vega').getByRole('button', { name: 'Select', exact: true }).click();

        const selected = page.locator('[data-field="selected-trainee"]');
        await expect(selected.locator('img[data-artwork-slot]')).toHaveCount(1);

        // One form is not a choice. A picker here would be a control with exactly one answer.
        await expect(selected.locator('select')).toHaveCount(0);
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
        await expect(traineeChoice(page)).toContainText('Admire Vega');
        await expect(choicesSoFar(page).getByRole('link', { name: 'Preview profile' })).toHaveAttribute(
            'href',
            /\/career\/setup\/trainee\/\d+$/,
        );

        await action.click();
        await expect(action).toHaveAttribute('aria-pressed', 'false');
        await expect(action).toHaveText('Select');
        await expect(page.locator('[data-field="selected-trainee"]')).toHaveCount(0);
        await expect(traineeChoice(page).getByRole('link')).toHaveCount(0);
        await expect(page.getByText('Trainee cleared.')).toBeVisible();
    });

    test('carries no per-card costume picker: the form is chosen on the profile screen', async ({ page }) => {
        /*
         * Retired with the picker (Phase B.2).
         *
         * This case pinned R3-06's costume-form select on the roster card: that a multi-form trainee was
         * asked once, that "Not stated" was offered as a real answer, and that the readable name came from
         * the row's own label node. `career-trainee.html:110-160` draws no per-card select at all — the card
         * is a pick control, a portrait, two name lines, three pills and three aptitude rows — so the
         * picker was a second control per card that the wireframe never had.
         *
         * The R3-06 question still exists and is still answered: `character_card_id` is written from the
         * profile screen, and `CareerTraineeSelectTest` keeps the draft round trip. What is asserted here is
         * the roster's half of the cut, so a re-added picker fails this case instead of passing silently.
         */
        await page.goto('/career/setup/trainee');
        await page.locator('#app > *').first().waitFor();

        await page.getByLabel('Search').fill('Rice Shower');
        await expect(card(page, 'Rice Shower')).toBeVisible();

        await expect(page.locator('select[id^="trainee-form-"]')).toHaveCount(0);
        await expect(page.getByText('Costume form', { exact: true })).toHaveCount(0);
        await expect(page.locator('[data-trainee-card] select')).toHaveCount(0);
    });

    test('narrows the roster with the search and facet filters, states the empty match, then clears back', async ({ page }) => {
        await page.goto('/career/setup/trainee');
        await page.locator('#app > *').first().waitFor();

        // No submit control: the row filters as it is read (`career-trainee.html:88-104`), so filling the
        // search is the whole interaction. The page debounces it, hence the assertion rather than a click.
        await page.getByLabel('Search').fill('biwa');

        await expect(card(page, 'Biwa Hayahide')).toBeVisible();
        await expect(page.getByRole('button', { name: 'Select', exact: true })).toHaveCount(1);

        // The distance facet is a real column and composes with the search: Biwa publishes no G among
        // her four distance letters, so the answer is an empty roster, stated rather than blank.
        await page.getByLabel('Distance').selectOption('G');
        await expect(page.getByText('No trainee matches these filters.')).toBeVisible();

        // The empty state carries the way out, not just the description of it (H9, IA-5): a Trainer who
        // filtered to nothing clears from where they are instead of hunting for the row's control.
        const empty = page.locator('[data-region="empty"]');
        await expect(empty.getByRole('button', { name: 'Clear filters' })).toBeVisible();
        await empty.getByRole('button', { name: 'Clear filters' }).click();
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
        await expect(page.locator('[data-field="selected-trainee"]')).toHaveCount(0);

        await action.focus();
        await expect(action).toBeFocused();
        await page.keyboard.press('Enter');

        // The PUT re-renders from the session, so the state is read off stored data rather than client
        // memory. Focus is restored to the action that was pressed (WCAG 2.4.3), which the redirect
        // would otherwise lose.
        await expect(page.getByText('Trainee set: Admire Vega.')).toBeVisible();
        await expect(action).toHaveAttribute('aria-pressed', 'true');
        await expect(action).toHaveText('Chosen · clear');
        await expect(traineeChoice(page)).toContainText('Admire Vega');
        await expect(action).toBeFocused();

        // The profile screen is the second half of the slice, and it reads the same draft back, so the
        // stored choice survives the move between the two screens. Reached from the Selected card's
        // `Preview profile` control: the per-card `Profile` link this case used to click was cut in
        // Phase B.2 because the wireframe draws no such link on a roster card.
        await choicesSoFar(page).getByRole('link', { name: 'Preview profile' }).click();
        await page.waitForURL(/\/career\/setup\/trainee\/\d+$/);

        // The profile has no table: the roster's row role does not exist here. The page's own
        // heading carries the name, and the footer button carries the chosen state.
        await expect(page.getByRole('heading', { name: /Admire Vega/ }).first()).toBeVisible();
        await expect(page.getByRole('button', { name: 'Continue to build target', exact: true })).toHaveAttribute('aria-pressed', 'true');
        await expect(page.getByText('Chosen', { exact: true })).toBeVisible();

        await page.getByRole('link', { name: 'Choose a different trainee' }).click();
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
        await expect(page.locator('[data-field="selected-trainee"]')).toContainText('Admire Vega');
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
        const grid = page.locator('section[aria-labelledby="aptitudes"]');
        await expect(grid.locator('dt')).toHaveCount(10);
        await expect(grid.locator('dd')).toHaveCount(10);

        // Rarity is the form's own fact, printed beside the form it belongs to.
        await expect(page.getByRole('heading', { name: 'Costume forms' })).toBeVisible();
        await expect(page.getByText('[Starry Nocturne]')).toBeVisible();
        await expect(page.getByRole('img', { name: /Three stars/i }).first()).toBeVisible();
        await expect(page.getByText('Mar 5, 2026')).toBeVisible();

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
        await expect(page.getByText(/No column records a version/).first()).toBeVisible();

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
            page.getByRole('link', { name: 'Choose a different trainee' }),
            page.getByRole('button', { name: 'Continue to build target', exact: true }),
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

    /*
     * The step body, measured against the wireframe.
     *
     * This replaces the `regions: 'subset'` structural case (Phase B.7.1). A subset comparison only asked
     * whether the wireframe's markers appeared in order, so it reported green while the app drew a
     * `Unique skill` select, an `Order` select, a `Filter` submit, a per-card `Profile` link and a
     * per-card costume picker that the wireframe does not have — "in the wireframe, in order" is not the
     * same question as "only the wireframe".
     *
     * `verifyPage` answers the second question in three buckets: own-text equality, computed style, and
     * bounding boxes.
     *
     * HFW-OVERRIDE (markup: element paths) — the `excess`/`missing` buckets are *not* asserted empty here,
     * and the measurement is the reason rather than a tolerance. `visual-diff.ts` names an element by its
     * position inside its own document, which is what makes a positional diff meaningful; but the app and
     * the wireframe are two different DOM implementations of one design, not one tree rendered twice. The
     * wireframe's card is `<label class="card"> > div > input + span.slot + div` with each aptitude row as
     * a `<table>` cell (23 descendants); the app's is `div[data-region="card"] > div.flex > …` with a
     * `<dl>`, a `<button>` pick control and per-letter `<span>`s (38). Neither side can ever produce the
     * other's paths, so a whole-canvas `excess` list is a list of "the implementations differ", not of
     * "the app draws something the design does not".
     *
     * What that bucket *would* catch is asserted directly instead, and more sharply: the filter row's own
     * controls. A re-added `Unique skill` select, `Order` select or `Filter` submit changes the row and
     * fails the case above (`draws the wireframe filter row`), which is exactly the regression Phase B is
     * about. Measured inventory of the row on the served page: `input:search`, `select:surface`,
     * `select:distance`, `select:style`, `select:sortBy`, `button:Clear` — the wireframe's five controls
     * and its clear, with nothing else.
     */
    test('matches the wireframe on content, computed style and geometry', async ({ page }) => {
        const hfw = pathToFileURL(
            resolve(process.cwd(), 'docs/design-research/frontend-v2-wireframes/career-trainee.html'),
        ).href;

        // The wireframe draws the selection echo card at the foot of the roster and the app renders it
        // only once a trainee is chosen, so the comparison needs a draft that holds one. The click lands
        // in the session draft, not in a table, so this case still writes no row.
        await page.goto('/career/setup/trainee');
        await page.locator('#app > *').first().waitFor();
        await page.locator('[data-trainee-card]').first().click();
        await page.locator('[data-field="selected-trainee"]').waitFor();

        const report = await verifyPage(page, {
            appUrl: '/career/setup/trainee',
            hfwUrl: hfw,
            rootSelector: '.canvas',
        });

        // The report is the phase's evidence, not the green tick above it: both viewports' buckets are
        // written where the phase's records live (`B.6`), so a later phase can re-read the same numbers.
        // Raw `excess`/`missing` paths are recorded in those files and not asserted here — see the
        // `HFW-OVERRIDE (markup: element paths)` note on this case: the two sides are different DOM
        // implementations of one design, so their positional paths can never pair, and the buckets
        // would only ever read "the markup differs". What they must not contain — the `Unique skill`
        // select, the `Order` select, the `Filter` submit, per-card pickers — is asserted by the cases
        // above on the served page itself, which is the sharper assertion for the same question.
        const dir = resolve(process.cwd(), '.scratch-uma/wizard-parity');
        mkdirSync(dir, { recursive: true });

        for (const [name, viewport] of [['1280', { width: 1280, height: 800 }], ['390', { width: 390, height: 844 }]] as const) {
            const at = await verifyPage(page, {
                appUrl: '/career/setup/trainee',
                hfwUrl: hfw,
                rootSelector: '.canvas',
                ...viewport,
            });

            writeFileSync(join(dir, `trainee-diff-${name}.json`), `${JSON.stringify(at, null, 2)}\n`);
        }

        // HFW-OVERRIDE (mock: roster rows) — the wireframe lists three mock trainees at its own page 1
        // of 12 and prints its own denominator; the app paginates the live roster at 25 rows
        // (`PageSize::DEFAULT`), so its page 1 is a different 25 rows and its denominator reads
        // `page 1 of 3`. Recorded rather than faked.

        // HFW-OVERRIDE (rect: page height) — the app's roster is real data paginated at 25 rows
        // (`PageSize::DEFAULT`) while the wireframe's mock lists one page of 6, so the page is taller on
        // the app side. Rows above the fold are unaffected.
        //
        // HFW-OVERRIDE (sourced) — the card's running-style axis prints the sourced full names
        // (`Front Runner`, `Pace Chaser`, `Late Surger`, `End Closer`; `lang/en/uma.php:109-112`) where the
        // wireframe abbreviates to `Front`, `Pace`, `Late`, `End`. Recorded on the card's own comment too.
        //
        // Computed style is asserted for the filter row, which is the region this phase rewrote. A
        // whole-subtree colour diff runs at Phase H against a common baseline. Two recorded categories
        // come off that bucket, each named and each pinned so the record cannot rot into live drift:
        //
        // HFW-OVERRIDE (type: field caption) — the wireframe's `.field .k` is a 10px, weight-800,
        // uppercase, letter-spaced block with a 4px bottom margin (`shell.css:496`); the shipped caption
        // is `BaseSelect`'s `text-sm font-medium` span, because DESIGN.md §2.2's owner-signed Metadata
        // tier governs a form label and 10px sits below it. The tier is re-asserted below, so a caption
        // that moves off it fails here.
        //
        // HFW-OVERRIDE (palette: colour between two token sets) — `color` and `borderColor` compare this
        // app's sampled reference palette against the wireframe sheet's own values, which is the
        // common-baseline run Phase H owns. `<option>` colour is UA-rendered from the select's colour.
        const caption = (selector: string): boolean => /label\.text-sm\b.*> span:nth-child\(1\)$/.test(selector);
        const option = (selector: string): boolean => selector.includes('> option');
        const drift = report.computed.filter((entry) => entry.selector.includes('[data-region="filters"]'));
        const recorded = drift.filter((entry) => caption(entry.selector) || option(entry.selector));
        const unrecorded = drift.filter((entry) => !caption(entry.selector) && !option(entry.selector));

        expect(unrecorded, `unrecorded computed drift: ${JSON.stringify(unrecorded)}`).toEqual([]);

        const recordedProps = [...new Set(recorded.map((entry) => entry.prop))].sort();
        expect(
            recordedProps,
            `recorded drift carries props outside the two overrides: ${recordedProps.join(', ')}`,
        ).toEqual(['borderColor', 'color', 'display', 'fontSize', 'fontWeight', 'lineHeight', 'margin']);

        const captionSizes = [...new Set(recorded.filter((entry) => entry.prop === 'fontSize').map((entry) => entry.app))];
        expect(captionSizes, 'the shipped caption must stay on the signed Metadata tier').toEqual(['14px']);
    });
});
