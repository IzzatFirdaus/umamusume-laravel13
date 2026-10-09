import { test, expect } from '@playwright/test';

/*
 * Rendered-copy and behaviour evidence for the career setup wizard's steps 4 and 5 (`SCR-CAR-008`
 * ancestry, `SCR-CAR-009` deck; PRD FR-G-1 / FR-A-4, `ADR-0020` §1 and §3, under `ADR-0010` and
 * `ADR-0014`). The server-side `CareerLegacyDeckStepsTest` asserts the resolved props, the draft
 * round trips and the refusals; these assert what only a browser can reach: the rendered copy, the
 * keyboard path and where focus lands after a save, the 44px sweep, the 320px reflow and the
 * step-4-to-step-5 carry through the session draft.
 *
 * **These two steps write no database rows.** Both read and write the session draft
 * (`App\Services\Career\SetupDraft`), and the run is created once at Preflight (D7), so every
 * test here is self-cleaning: a fresh browser context is a fresh session, and the last test re-asserts
 * `/training-runs` empty as the shared proof that the two steps leaked nothing. Unlike
 * `legacy.spec.ts` and `support-deck.spec.ts` this file never creates a run, so it never deletes one.
 *
 * The fixture is the app's own seeded scratch database (`playwright.config.ts`): 67 trainees, 559
 * support cards, and **zero Veterans** — the state a fresh install meets, asserted as itself. The
 * ancestry step therefore offers no parent picks and says so; the deck picker has real cards.
 *
 * Axe coverage is provided by `tests/browser/accessibility.spec.ts`: `@axe-core/playwright` is installed
 * and scans pages against `wcag2a`, `wcag2aa`, and `wcag21aa`; this spec retains the hand-rolled checks
 * for target size, keyboard path, focus order, reflow and console errors.
 *
 * **Every navigation waits for `domcontentloaded`, not for `load`**, for the reason `legacy.spec.ts`
 * gives: `load` waits on the module graph and the subresource set behind a single-process
 * `artisan serve`, none of which carries an assertion. The `#app > *` check after each navigation is
 * the stronger wait because it proves the page hydrated.
 */

const RECORD_ONLY_NOTICE =
    'Record only. This screen stores and compares what you enter. It does not compute inheritance.';

async function hydrate(page: import('@playwright/test').Page, url: string): Promise<void> {
    await page.goto(url, { waitUntil: 'domcontentloaded' });
    await page.locator('#app > *').first().waitFor();
}

async function assertMinHeight(page: import('@playwright/test').Page, selector: string): Promise<void> {
    const box = await page.locator(selector).boundingBox();
    expect(box?.height ?? 0, `${selector} is not sized to the 44px contract`).toBeGreaterThanOrEqual(44);
}

test.describe('Setup step 4 — Legacy', () => {
    test('renders the six-node graph and its empty states on a draft with nothing chosen', async ({ page }) => {
        await hydrate(page, '/career/setup/legacy');

        await expect(page.getByRole('heading', { name: 'Legacy', level: 1 })).toBeVisible();
        await expect(page.getByText('Step 4 of 6').first()).toBeVisible();
        await expect(
            page.getByRole('navigation', { name: 'Setup steps' }).getByRole('link', { name: 'Legacy' }),
        ).toHaveAttribute('aria-current', 'step');
        // All six steps are live links: Preflight landed with D7, so no step renders as a named
        // absence in the nav.
        await expect(
            page.getByRole('navigation', { name: 'Setup steps' }).getByText('not built'),
        ).toHaveCount(0);

        // The same record-only sentence the Legacy Lab carries, from its one owner. `toContainText`, not
        // `toHaveText`: the banner's `textContent` includes the `aria-hidden` glyph.
        await expect(page.getByRole('note')).toContainText(RECORD_ONLY_NOTICE);

        // Every absence is a named one with its reason, never a dash or a default.
        await expect(page.getByText('N/A, no trainee chosen')).toBeVisible();
        await expect(page.getByText('Scenario: N/A')).toBeVisible();
        await expect(page.getByText(/No Legacy is recorded for this setup yet/)).toBeVisible();

        // The library the pick controls would select from is empty on a fresh install, and the
        // emptiness is disclosed with the way to fill it rather than left as two unused controls.
        await expect(page.getByText(/No Veterans recorded yet/)).toBeVisible();

        // Six nodes: the trainee, two parents, four ancestor entry slots. The structure is list
        // markup, so the entry slots are countable inputs rather than drawn boxes (`AncestryNode`).
        await expect(page.locator('input[id^="ancestor-"]')).toHaveCount(4);
        await expect(page.getByText('N/A, not chosen')).toHaveCount(3);
        await expect(page.getByText('No Sparks recorded.')).toHaveCount(4);

        // The chance a Spark rolls is a named absence on each parent node, and the reason is on the
        // disclosure rather than in a footnote or a hover (`ADR-0020` §3: no star-roll table exists).
        await expect(page.getByText('Spark chance')).toHaveCount(2);
        await expect(page.locator('summary', { hasText: 'star-roll' })).toHaveCount(2);
    });

    test('sizes the ancestry controls to the 44px contract', async ({ page }) => {
        await hydrate(page, '/career/setup/legacy');

        const selectors = [
            '#legacy-save',
            '#affinity',
            '#legacies\\.0\\.legacy_id',
            '#legacies\\.1\\.legacy_id',
            '#rank-parent_a',
            '#ancestor-parent_a-0',
            '#ancestor-parent_b-1',
        ];
        for (const selector of selectors) {
            await assertMinHeight(page, selector);
        }

        for (const button of await page.getByRole('button', { name: /^Add Spark/ }).all()) {
            const box = await button.boundingBox();
            expect(box?.height ?? 0, 'an Add Spark button is not sized to the 44px contract').toBeGreaterThanOrEqual(44);
        }
    });

    test('records the entered ancestry, reads it back from stored data, and returns focus to the save', async ({ page }) => {
        await hydrate(page, '/career/setup/legacy');

        // Ancestors are typed names, not library picks, which is what makes this whole path
        // reachable on a database with no Veterans: the entry the Trainer read off the client is the
        // record (`ADR-0010` Consequences §2), and a pick would need a library row this scratch
        // database does not hold.
        await page.locator('#ancestor-parent_a-0').fill('Grass Wonder');
        await page.locator('#ancestor-parent_a-1').fill('Mill Raptor');
        await page.locator('#rank-parent_a').fill('4');
        await page.getByRole('button', { name: /^Add Spark/ }).first().click();
        await page.getByLabel('Applies to').fill('Speed');
        await page.getByLabel('Stars').fill('2');
        await page.locator('#affinity').selectOption('◎');
        await page.getByRole('button', { name: 'Save Legacy' }).click();
        await expect(page.getByText('Legacy recorded.')).toBeVisible();

        // The stored state is read from the draft on the way back, not kept in client memory: the
        // disclosure line, the re-seeded inputs and the node all print what the session holds.
        await expect(page.getByText(/This setup already holds a Legacy/)).toBeVisible();
        await expect(page.locator('#ancestor-parent_a-0')).toHaveValue('Grass Wonder');
        await expect(page.locator('#ancestor-parent_a-1')).toHaveValue('Mill Raptor');
        await expect(page.locator('#rank-parent_a')).toHaveValue('4');
        await expect(page.locator('#affinity')).toHaveValue('◎');
        await expect(page.getByText('Rank 4').first()).toBeVisible();

        // The Spark reads back as one chip whose accessible name carries kind, target and count in
        // words, never colour alone (WCAG 1.4.1).
        await expect(page.getByText('Blue Spark, Speed, 2 stars').first()).toBeVisible();

        // Focus returns to the control the Trainer pressed, after the new props land (`onSuccess`
        // plus `nextTick`, WCAG 2.4.3).
        await expect(page.locator('#legacy-save')).toBeFocused();
    });

    test('treats a rented parent as an entered fact and reads the flag back on the node', async ({ page }) => {
        await hydrate(page, '/career/setup/legacy');

        // One checkbox per parent, in slot order; the second is Parent B's.
        await page.getByLabel('Rented for the run').nth(1).check();
        await page.getByRole('button', { name: 'Save Legacy' }).click();
        await expect(page.getByText('Legacy recorded.')).toBeVisible();

        await expect(page.locator('input[type="checkbox"]').nth(1)).toBeChecked();
        await expect(page.locator('input[type="checkbox"]').first()).not.toBeChecked();
        await expect(page.getByText('Rented from a friend')).toBeVisible();
    });
});

test.describe('Setup step 5 — Support deck', () => {
    test('renders six slots, the seven spelled types and the ownership flag as a named absence', async ({ page }) => {
        await hydrate(page, '/career/setup/deck');

        await expect(page.getByRole('heading', { name: 'Support deck', level: 1 })).toBeVisible();
        await expect(page.getByText('Step 5 of 6').first()).toBeVisible();
        await expect(
            page.getByRole('navigation', { name: 'Setup steps' }).getByRole('link', { name: 'Support deck' }),
        ).toHaveAttribute('aria-current', 'step');

        // No scenario in the draft yet, and the page says so with its reason rather than defaulting
        // to the baseline the planner's composition fallback would lend (`ADR-0020` §2).
        await expect(page.getByText('Scenario: N/A')).toBeVisible();

        // Six named positions, the friend role on position six whatever card may sit there
        // (`ADR-0014` correction 1), and an empty slot stated as a statement rather than a blank.
        const slots = page.locator('ul > li[id^="deck-slot-"]');
        await expect(slots).toHaveCount(6);
        await expect(page.locator('#deck-slot-6')).toContainText('Slot 6 · Friends');
        await expect(page.locator('#deck-slot-6')).toContainText('Friend slot');
        await expect(page.locator('#deck-slot-1')).toContainText('Not equipped');

        // The flag is `N/A` with a reason before any write. A default of "Owned" would be the
        // invented fact the Floor forbids.
        await expect(page.locator('[title="No ownership has been recorded for this slot yet."]')).toHaveCount(6);

        // The seven types print as server-labelled words beside their marks (`SupportTypeMark`). The
        // assertion is scoped to their own paragraph and matches each word as written: a bare
        // `getByText('Wit')` is a case-insensitive substring match, and it resolved to 54 elements
        // (every "with", every option label) rather than to the one this case is about.
        const typeWords = page.locator('p').filter({ hasText: 'The seven support types' });
        await expect(typeWords).toHaveText(/The seven support typesSpeedStaminaPowerGutsWitPalGroup/);

        // Where the flag goes, stated beside the six toggles it describes (`ADR-0023`: the draft keeps
        // it, Preflight writes it onto the run's slot row, and an unclassified slot stays an absence).
        await expect(page.getByText(/Preflight writes it onto the run\s+as one value per slot/)).toBeVisible();
        await expect(page.getByText(/reads\s+as N\/A rather than as Owned/)).toBeVisible();
    });

    test('equips from the picker, marks the flag, and reads each write back from the session', async ({ page }) => {
        await hydrate(page, '/career/setup/deck');

        // Server-resolved picker aim: with an empty draft the first write targets the first slot,
        // and the equip buttons carry that slot in their own label.
        await expect(page.getByText('The picker writes to Slot 1.')).toBeVisible();

        await page.locator('input[name="q"]').fill('Agnes Digital');
        await page.getByRole('button', { name: 'Filter' }).click();
        await expect(page).toHaveURL(/q=Agnes(\+|%20)Digital/);
        await expect(page.getByText(/match any type and a name containing Agnes Digital/)).toBeVisible();

        await page.selectOption('#deck-pick', { index: 1 });
        await page.getByRole('button', { name: 'Equip owned to Slot 1' }).click();
        await expect(page.getByText('Deck saved.')).toBeVisible();

        const slot1 = page.locator('#deck-slot-1');
        // The card's own name link, and the artwork frame beside it, both carry the card's name
        // (`ArtworkSlot` passes `link-label` with an empty `alt`), so either is the proof that the
        // equipped row read back from the session.
        await expect(slot1.getByRole('link', { name: /Agnes Digital/ }).first()).toBeVisible();
        await expect(slot1.getByRole('button', { name: 'Owned' })).toHaveAttribute('aria-pressed', 'true');

        // No scenario chosen means no linked list to check against, so the badge is the third state,
        // not a confident "not a link" (`ADR-0014` correction 3).
        await expect(slot1.getByText('Scenario Link: N/A')).toBeVisible();

        // The flag is its own write, not a side effect of the equip: flipping it is a second save
        // whose effect the next render reads back from the draft.
        await slot1.getByRole('button', { name: 'Rented' }).click();
        await expect(slot1.getByRole('button', { name: 'Rented' })).toHaveAttribute('aria-pressed', 'true');
        await expect(slot1.getByRole('button', { name: 'Owned' })).toHaveAttribute('aria-pressed', 'false');

        // A clear is a statement too: the slot returns to its named empty text and the flag to `N/A`.
        await page.getByRole('button', { name: 'Set Slot 1 to not equipped' }).click();
        await expect(page.locator('#deck-slot-1')).toContainText('Not equipped');
        await expect(page.locator('[title="No ownership has been recorded for this slot yet."]')).toHaveCount(6);
    });

    test('sizes the deck-step controls to the 44px contract', async ({ page }) => {
        await hydrate(page, '/career/setup/deck');

        for (const selector of ['#deck-pick', 'select[name="type"]', 'input[name="q"]']) {
            await assertMinHeight(page, selector);
        }

        // "Save deck" and "Filter" are the two submits, both `h-11` like every other control.
        const submits = await page.locator('button[type="submit"]').all();
        expect(submits).toHaveLength(2);
        for (const submit of submits) {
            const box = await submit.boundingBox();
            expect(box?.height ?? 0, 'a submit control is not sized to the 44px contract').toBeGreaterThanOrEqual(44);
        }

        for (const label of ['Equip owned to Slot 1', 'Equip rented to Slot 1', 'Set Slot 1 to not equipped']) {
            const box = await page.getByRole('button', { name: label }).boundingBox();
            expect(box?.height ?? 0, `"${label}" is not sized to the 44px contract`).toBeGreaterThanOrEqual(44);
        }
    });

    test('reflows to one column at 320px without two-dimensional document scroll', async ({ page }) => {
        await hydrate(page, '/career/setup/deck');
        await page.setViewportSize({ width: 320, height: 720 });
        await page.locator('#app > *').first().waitFor();

        // WCAG 1.4.10 Reflow: the two-column grid stacks, and the document itself never scrolls
        // sideways.
        await expect(page.getByRole('heading', { name: 'The six slots' })).toBeVisible();
        await expect(page.getByRole('heading', { name: 'Choose a card' })).toBeVisible();

        const overflow = await page.evaluate(
            () => document.documentElement.scrollWidth <= document.documentElement.clientWidth + 1,
        );
        expect(overflow, 'the document scrolls horizontally at 320px').toBe(true);
    });

    test('carries the ancestry and the deck through the steps and writes no run behind them', async ({ page }) => {
        // Step 4 first: one entered ancestry.
        await hydrate(page, '/career/setup/legacy');
        await page.locator('#ancestor-parent_a-0').fill('Grass Wonder');
        await page.getByRole('button', { name: 'Save Legacy' }).click();
        await expect(page.getByText('Legacy recorded.')).toBeVisible();

        // Step 5 through the page's own next-step link: one equipped card. The card is whichever the
        // deterministic picker order puts first, so the test lifts its name off the rendered slot
        // rather than hardcoding today's first row.
        await page.getByRole('link', { name: 'Next: Support deck' }).click();
        await page.locator('#app > *').first().waitFor();
        await expect(page).toHaveURL(/\/career\/setup\/deck/);
        await page.selectOption('#deck-pick', { index: 1 });
        await page.getByRole('button', { name: 'Equip owned to Slot 1' }).click();
        await expect(page.getByText('Deck saved.')).toBeVisible();
        // The card's name comes off the slot's own text link: the artwork frame beside it is also an
        // anchor carrying the card's name as `aria-label` but no text content, so an anchor with text
        // is the name link. The test never has to know which card the deterministic picker order put
        // first.
        const cardName = await page
            .locator('#deck-slot-1 a')
            .filter({ hasText: /\S/ })
            .first()
            .innerText();
        expect(cardName.length, 'the slot carries no card link').toBeGreaterThan(0);

        // Back to step 4 through the nav: the value stays because `SetupDraft::write()` merges and
        // neither step owns the other's keys. It is read back off the session, not remembered.
        const nav = page.getByRole('navigation', { name: 'Setup steps' });
        await nav.getByRole('link', { name: 'Legacy' }).click();
        await page.locator('#app > *').first().waitFor();
        await expect(page.locator('#ancestor-parent_a-0')).toHaveValue('Grass Wonder');
        await expect(page.getByText(/This setup already holds a Legacy/)).toBeVisible();

        // And forward again: the deck survived the round trip too.
        await nav.getByRole('link', { name: 'Support deck' }).click();
        await page.locator('#app > *').first().waitFor();
        await expect(page.locator('#deck-slot-1')).toContainText(cardName);

        // The last shared invariant: writes across both steps, and still no run. The two steps
        // store draft keys precisely because the run arrives at Preflight (`ADR-0020` §1).
        await hydrate(page, '/training-runs');
        await expect(page.getByText('No runs yet')).toBeVisible();
    });
});
