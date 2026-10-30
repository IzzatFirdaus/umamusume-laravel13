import { test, expect } from '@playwright/test';
import { mkdirSync, writeFileSync } from 'node:fs';
import { join, resolve } from 'node:path';
import { pathToFileURL } from 'node:url';
import { buildAxe } from '../utils/accessibility';
import { verifyPage } from './fixtures/wizard-verify';

/*
 * Rendered-copy and behaviour evidence for the career setup wizard's steps 4 and 5 (`SCR-CAR-008`
 * legacy, `SCR-CAR-009` deck; PRD FR-G-1 / FR-A-4, `ADR-0020` §1 and §3, under `ADR-0010` and
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
 * legacy step therefore offers no library picks and says so; the deck picker has real cards.
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

test.beforeEach(async ({ page }) => {
    // The deck picker renders up to 25 thumbnails, and `artisan serve` is one process: the queue turns
    // a page view into ~50 s and stretched two cases in this file to the 180 s timeout. No assertion
    // here reads a decoded image, so refuse them at the browser; `support-deck.spec.ts` documents the
    // same lever for the same reason.
    await page.route('**/artwork/**', (route) => route.abort());
});

async function hydrate(page: import('@playwright/test').Page, url: string): Promise<void> {
    await page.goto(url, { waitUntil: 'domcontentloaded' });
    await page.locator('#app > *').first().waitFor();
}

async function assertMinHeight(page: import('@playwright/test').Page, selector: string): Promise<void> {
    const box = await page.locator(selector).boundingBox();
    expect(box?.height ?? 0, `${selector} is not sized to the 44px contract`).toBeGreaterThanOrEqual(44);
}

test.describe('Setup step 4 — Legacy', () => {
    /*
     * Phase D retirement note. The cases that pinned the six-node ancestry editor on this step
     * (ancestor name inputs, Add Spark buttons, rank and rank-letter fields, the rented checkbox,
     * spark chips and the star-roll disclosure) retired with the editor: `career-legacy.html:87-120`
     * draws two parent cards instead, and full ancestor recording is the run-scoped Legacy builder's
     * surface, covered by `legacy.spec.ts`. The payload round trip that guarantees a re-save never
     * erases a recorded ancestry is `CareerLegacyDeckStepsTest`'s half.
     */
    test('renders the two parent slots, the mechanism table and the projected-start table', async ({ page }) => {
        await hydrate(page, '/career/setup/legacy');

        await expect(page.getByRole('heading', { name: 'Inherit from two parents', level: 1 })).toBeVisible();
        await expect(page.getByText('Step 4 of 6').first()).toBeVisible();
        await expect(
            page.getByRole('navigation', { name: 'Setup steps' }).getByRole('listitem', { name: 'Legacy' }),
        ).toHaveAttribute('aria-current', 'step');

        // The same record-only sentence the Legacy Lab carries, from its one owner.
        await expect(page.getByRole('note')).toContainText(RECORD_ONLY_NOTICE);

        // The library is empty on the scratch database, and the emptiness is disclosed with the way
        // to fill it rather than left as two unused controls.
        await expect(page.getByText(/No Veterans recorded yet/)).toBeVisible();

        // Two parent cards, each `Not chosen` while the slot is empty: the pill sits inside the
        // card's own heading; each pick control's blank option carries the same words, so the pill
        // assertion is scoped to the headings rather than counted page-wide.
        const headingA = page.getByRole('heading', { name: 'Parent A' });
        const headingB = page.getByRole('heading', { name: 'Parent B' });
        await expect(headingA).toBeVisible();
        await expect(headingB).toBeVisible();
        await expect(headingA.getByText('Not chosen')).toBeVisible();
        await expect(headingB.getByText('Not chosen')).toBeVisible();
        // `getByText` is a case-insensitive substring match, so the bare word would also match each
        // "Not chosen"; the positive pill is asserted as an exact string.
        await expect(page.getByText('Chosen', { exact: true })).toHaveCount(0);

        // The mechanism table: the five kinds the payload and the Spark editor already offer, each with
        // the figure the corpus gives it. `scr-car-008-legacy-source-accuracy-2026-10-30.md` records why
        // this list is five and not the wireframe's three, and why the sixth row the page used to print
        // ('Unique-skill factor') is gone: inheriting a unique skill IS the Green Spark, so the two rows
        // stated one mechanism twice.
        const mechanism = page.locator('section[aria-labelledby="inheritance-does"]');
        // Exact strings and the cell role: the Pink row's own prose mentions the Blue Spark, so a
        // plain text lookup resolves to two cells.
        for (const kind of ['Blue Spark', 'Pink Spark', 'Green Spark', 'White Spark', 'Scenario Spark']) {
            await expect(mechanism.getByRole('cell', { name: kind, exact: true })).toBeVisible();
        }
        await expect(mechanism.locator('tbody tr')).toHaveCount(5);
        // No named absence stands in this table any more. The row that carried one ('Enhanced spark') was
        // removed for being unsourced rather than reworded, and every row left prints a figure a cited
        // source states, so the disclosure the page used to count here has nothing left to hold.
        await expect(mechanism.locator('summary')).toHaveCount(0);
        // The Blue and Pink figures are the career-start stat award and the aptitude grade ladder; the
        // page had them swapped, so both are pinned here in the words the table prints.
        await expect(mechanism.getByRole('cell', { name: /\+5, \+12 or \+21 to the stat it names/ })).toBeVisible();
        await expect(mechanism.getByRole('cell', { name: /One aptitude grade at one star/ })).toBeVisible();
        // The cap raise is stated once, in the note, where the ceiling is the subject.
        await expect(mechanism.getByText(/lifts the cap of the stat it names by 16/)).toBeVisible();
        // `factor` is not in shipped copy: the client's word is Spark, and `lang/en/uma.php:90` is the
        // terms map that says so.
        await expect(mechanism.getByText(/factor/i)).toHaveCount(0);

        // The projected-start table is per stat, five rows, and no projected figure is computed
        // (`ADR-0020` §3): every cell prints the named absence or the recorded ceiling.
        const projected = page.locator('section[aria-labelledby="projected-start"]');
        await expect(projected.locator('tbody tr')).toHaveCount(5);
        await expect(projected.getByRole('columnheader', { name: 'Projected' })).toBeVisible();
        await expect(projected.getByText(/not this tool's to derive/)).toBeVisible();
        // The draft this harness carries has no scenario, so the ceiling column is headed for what it
        // prints: the 1200 baseline, not a scenario's numbers.
        await expect(projected.getByRole('columnheader', { name: 'Base cap' })).toBeVisible();
        await expect(projected.getByRole('columnheader', { name: 'Scenario cap' })).toHaveCount(0);

        // The parents-must-differ note the wireframe carries, and the empty-slot disclosure. Both are
        // stated against the code that performs them: the picker lists the library unfiltered and the
        // save is what refuses (`assertNoOwnTrainee`, `assertParentsDiffer`), and the empty pair is this
        // screen's allowance rather than a client rule.
        await expect(page.getByRole('heading', { name: 'Parents must differ' })).toBeVisible();
        await expect(page.getByText(/The save refuses two cases/)).toBeVisible();
        await expect(page.getByText(/Leaving both slots empty is this screen's choice/)).toBeVisible();
        await expect(page.getByText(/hiding the trainee you are running/)).toHaveCount(0);

        // DELIBERATELY RETIRED, not weakened. This case used to assert the ancestor editor was
        // *absent* from this step: no SVG, no ancestor inputs, no spark rows, no rank fields. That
        // guard existed because the six-node graph had been taken off the wizard, and the claim it
        // protected was "the editor does not live here". The step now renders the four grandparents
        // on purpose (a parent's grandparents are frequently not in the local catalogue and are
        // recorded by name), so the absence claim is no longer true and the positive form replaces
        // it: four nodes, each with a name, two ranks and a Spark panel.
        //
        // The SVG claim survives as the sharper half of the original: the step draws no picture of
        // the tree, only list items, which is what made the count the structure rather than a
        // drawing of it.
        await expect(page.locator('.canvas svg')).toHaveCount(0);
        // The four name inputs, counted by their exact ids rather than the `ancestor-` prefix: the
        // same prefix also names the rank-letter and star-rank fields, so a prefix count would read 12
        // and claim the editor drew three times what it did.
        await expect(page.locator('input[id^="ancestor-a-"], input[id^="ancestor-b-"]')).toHaveCount(4);
        for (const slot of ['a', 'b']) {
            for (const index of [0, 1]) {
                await expect(page.locator(`input[id="ancestor-${slot}-${index}"]`)).toBeVisible();
                await expect(page.locator(`input[id="ancestor-rank-letter-${slot}-${index}"]`)).toBeVisible();
                await expect(page.locator(`input[id="ancestor-rank-${slot}-${index}"]`)).toBeVisible();
            }
        }
        // A Spark panel per node, as the same collapsed disclosure the Legacy Lab uses: four
        // summaries, each naming its own grandparent, and no Spark fields until a node is opened and
        // one is added.
        // Scoped by content, never by tag (the idiom the Legacy Lab's own spec records): the
        // disclosure's <summary> is what carries the node's name.
        await expect(page.locator('summary').filter({ hasText: 'Sparks for Grandparent A1' })).toHaveCount(1);
        await expect(page.locator('summary').filter({ hasText: 'Sparks for Grandparent B2' })).toHaveCount(1);
        await expect(page.locator('input[id^="ancestor-spark-kind-"]')).toHaveCount(0);
    });

    test('places the projection beside the affinity card and the save inside it', async ({ page }) => {
        await hydrate(page, '/career/setup/legacy');

        // The projection is read against the affinity grade, so the two cards share one row and the
        // save control sits beside the fields it writes rather than at the foot of the page.
        const projected = page.locator('section[aria-labelledby="projected-start"]');
        const affinity = page.locator('section[aria-labelledby="pair-affinity"]');
        await expect(affinity).toBeVisible();
        await expect(affinity.getByRole('heading', { name: 'Pair Affinity' })).toBeVisible();
        await expect(affinity.locator('#affinity')).toBeVisible();
        await expect(affinity.locator('#legacy-save')).toBeVisible();

        // Siblings in one grid: same parent box, and each is the other's next element sibling at the
        // grid level. That is what 'beside' means here, so it is asserted rather than assumed.
        const sameParent = await page.evaluate(() => {
            const start = document.querySelector('section[aria-labelledby="projected-start"]');
            const pair = document.querySelector('section[aria-labelledby="pair-affinity"]');
            if (start === null || pair === null) return null;
            return { same: start.parentElement === pair.parentElement, adjacent: start.nextElementSibling === pair };
        });
        expect(sameParent, 'the projection and the affinity card are not siblings in one row').toEqual({
            same: true,
            adjacent: true,
        });

        // The projection now precedes the mechanism table, because the table explains what the numbers
        // in it do and a reader arrives needing the numbers first.
        const order = await page.evaluate(() => {
            const start = document.querySelector('section[aria-labelledby="projected-start"]');
            const does = document.querySelector('section[aria-labelledby="inheritance-does"]');
            const differ = document.querySelector('section[aria-labelledby="parents-differ"]');
            if (start === null || does === null || differ === null) return null;
            return {
                startBeforeDoes: Boolean(start.compareDocumentPosition(does) & Node.DOCUMENT_POSITION_FOLLOWING),
                doesBeforeDiffer: Boolean(does.compareDocumentPosition(differ) & Node.DOCUMENT_POSITION_FOLLOWING),
            };
        });
        expect(order, 'the cards are not in the order the step states').toEqual({
            startBeforeDoes: true,
            doesBeforeDiffer: true,
        });

        // `Parents must differ` is a full-width statement of the rule now, so it keeps the readable
        // measure on its prose rather than running the width of the page.
        const differ = page.locator('section[aria-labelledby="parents-differ"]');
        for (const paragraph of await differ.locator('p').all()) {
            await expect(paragraph).toHaveClass(/max-w-3xl/);
        }
    });
    test('sizes the step controls to the 44px contract', async ({ page }) => {
        await hydrate(page, '/career/setup/legacy');

        for (const selector of ['#legacy-save', '#affinity', '#legacy-pick-0', '#legacy-pick-1']) {
            await assertMinHeight(page, selector);
        }
        for (const button of await page.getByRole('button', { name: 'Leave empty' }).all()) {
            const box = await button.boundingBox();
            expect(box?.height ?? 0, 'a Leave empty control is not sized to the 44px contract').toBeGreaterThanOrEqual(44);
        }
    });

    test('saves an empty selection, reads it back from stored data, and returns focus to the save', async ({ page }) => {
        await hydrate(page, '/career/setup/legacy');

        // Two empty slots are a legitimate build and a stored record, not an error.
        await page.locator('#affinity').selectOption('◎');
        await page.getByRole('button', { name: 'Save Legacy' }).click();
        await expect(page.getByText('Legacy recorded.')).toBeVisible();

        // The stored state is read from the draft on the way back, not kept in client memory.
        await expect(page.getByText(/This setup already holds a Legacy/)).toBeVisible();
        await expect(page.getByText(/No Legacy is recorded for this setup yet/)).toHaveCount(0);
        await expect(page.locator('#affinity')).toHaveValue('◎');

        // Focus returns to the control the Trainer pressed, after the new props land (WCAG 2.4.3).
        await expect(page.locator('#legacy-save')).toBeFocused();
    });

    test('reaches the manual parent entry from the picker and keeps it through a save', async ({ page }) => {
        await hydrate(page, '/career/setup/legacy');

        // The sentinel is the only way into manual mode: without it `assignParent`'s
        // `if (value === '__manual__')` branch is unreachable, `manual[index]` is permanently false,
        // and every editor behind it stays hidden. That gap shipped once; this case is the pin.
        const picker = page.locator('#legacy-pick-0');
        await expect(
            picker.getByRole('option', { name: 'Not in my library - enter a name' }),
        ).toHaveCount(1);

        await picker.selectOption('__manual__');

        // Every editor the mode promises, each on its own id so a silent rename fails here.
        const name = page.locator('#legacy-name-0');
        await expect(name).toBeVisible();
        await expect(name).toHaveAttribute('list', 'known-umamusume-names');
        await expect(page.locator('#legacy-rank-letter-0')).toBeVisible();
        await expect(page.locator('#legacy-rank-0')).toBeVisible();
        await expect(page.getByText('Sparks for this parent').first()).toBeVisible();

        await name.fill('Silence Suzuka');
        await page.locator('#legacy-rank-letter-0').fill('A');
        await page.locator('#legacy-rank-0').fill('3');

        await page.getByRole('button', { name: 'Save Legacy' }).click();
        await expect(page.getByText('Legacy recorded.')).toBeVisible();

        // The round-trip is the half a fresh page cannot prove: the name comes back, the picker
        // still reads the sentinel rather than "Not chosen", and the editors are still open.
        await expect(page.locator('#legacy-name-0')).toHaveValue('Silence Suzuka');
        await expect(page.locator('#legacy-name-0')).toBeVisible();
        await expect(page.locator('#legacy-pick-0')).toHaveValue('__manual__');
        await expect(page.locator('#legacy-rank-letter-0')).toHaveValue('A');
    });


    test('updates the projected start from the form without a save', async ({ page }) => {
        await hydrate(page, '/career/setup/legacy');

        // The probe the source reading needed. A parent typed by name reaches manual mode, and the
        // parent's OWN Sparks are what the projected column binds to (`form.legacies[i].sparks`).
        // A grandparent's Sparks live in `ancestors_sparks` and cannot move this column.
        await page.locator('#legacy-pick-0').selectOption('__manual__');
        await page.locator('#legacy-name-0').fill('Silence Suzuka');

        const speedRow = page
            .locator('section[aria-labelledby="projected-start"] tbody tr')
            .filter({ hasText: 'Speed' });
        const fromParentA = speedRow.locator('td').nth(2);

        await expect(fromParentA).toHaveText('-');

        const shots = join(process.cwd(), '.scratch-uma', 'session_31b8f2ea', 'legacy-realtime-probe');
        mkdirSync(shots, { recursive: true });
        await page.setViewportSize({ width: 1280, height: 900 });
        await page.screenshot({ path: join(shots, 'before-1280.png'), fullPage: true });

        // Add the parent's own Blue Speed spark at three stars through its editor.
        await page.getByText('Sparks for this parent').first().click();
        await page.getByRole('button', { name: /Add Spark/ }).first().click();
        await page.locator('#legacy-spark-target-0-0').selectOption('Speed');
        await page.locator('#legacy-spark-stars-0-0').fill('3');

        // No save: the cell follows the form.
        await expect(fromParentA).toContainText('Blue');
        await expect(fromParentA).toContainText('3');

        await page.screenshot({ path: join(shots, 'after-1280.png'), fullPage: true });
    });
    test('every control on the step carries an accessible name', async ({ page }) => {
        await hydrate(page, '/career/setup/legacy');

        const results = await buildAxe(page).analyze();
        const unnamed = results.violations.filter((violation) => violation.id === 'label');
        expect(unnamed, JSON.stringify(unnamed, null, 2)).toEqual([]);
    });

    test('keeps the page to one primary action and the card ceiling', async ({ page }) => {
        await hydrate(page, '/career/setup/legacy');

        // `Save Legacy` is a secondary write control beside the fields it writes; the single primary
        // is the head band's `Continue to deck`.
        await expect(page.locator('main .bg-primary-action, main a.bg-primary-action')).toHaveCount(1);

        for (const card of await page.locator('article').all()) {
            const descendants = await card.locator('*').count();
            expect(descendants, 'a parent card is over the 25-descendant ceiling').toBeLessThan(25);
        }
    });

    /*
     * HFW-OVERRIDE (markup: element paths) — the same measured constraint the trainee step's three-diff
     * case records: the app and the wireframe are two DOM implementations of one design, so
     * `visual-diff`'s positional selectors can never pair and the raw `excess`/`missing` buckets read
     * "the implementations differ", not "the app draws something the design does not". They are written
     * to the JSON artifacts as this phase's evidence, and the regressions this page must not re-acquire —
     * the six-node graph, the ancestor inputs, the spark editors — are asserted on the served page by
     * the cases above, which is the sharper question.
     *
     * HFW-OVERRIDE (mock: parent cards) — the wireframe's two cards show chosen mock veterans with spark
     * tags, and its projected-start table prints the `cap + 16 per ★3` sums; the scratch database holds
     * zero Veterans, so the app renders the empty slots, the library disclosure, and the projected
     * column as named absences — `ADR-0020` §3 forbids the sum, and the page carries the
     * `DOMAIN OVERRIDE (ADR-0020 §3)` comment where the wireframe prints it. Recorded rather than faked.
     */
    test('three-diff against career-legacy.html writes the phase evidence', async ({ page }) => {
        const hfw = pathToFileURL(
            resolve(process.cwd(), 'docs/design-research/frontend-v2-wireframes/career-legacy.html'),
        ).href;

        const dir = resolve(process.cwd(), '.scratch-uma/wizard-parity');
        mkdirSync(dir, { recursive: true });

        for (const [name, viewport] of [['1280', { width: 1280, height: 800 }], ['390', { width: 390, height: 844 }]] as const) {
            const at = await verifyPage(page, {
                appUrl: '/career/setup/legacy',
                hfwUrl: hfw,
                rootSelector: '.canvas',
                ...viewport,
            });

            writeFileSync(join(dir, `legacy-diff-${name}.json`), `${JSON.stringify(at, null, 2)}\n`);
        }

        // Computed drift is asserted for the head band, the region Phase A.2 rewrote against this page.
        const report = await verifyPage(page, {
            appUrl: '/career/setup/legacy',
            hfwUrl: hfw,
            rootSelector: '.canvas',
        });
        const filtered = report.computed.filter((entry) => entry.selector.includes('hrow-actions'));
        expect(filtered, `computed drift: ${JSON.stringify(filtered)}`).toEqual([]);
    });
});

test.describe('Setup step 5 — Support deck', () => {
    test('renders six slots with the flag as a named absence', async ({ page }) => {
        await hydrate(page, '/career/setup/deck');

        await expect(page.getByRole('heading', { name: 'Build the six-card deck', level: 1 })).toBeVisible();
        await expect(page.getByText('Step 5 of 6').first()).toBeVisible();
        await expect(
            page.getByRole('navigation', { name: 'Setup steps' }).getByRole('listitem', { name: 'Deck' }),
        ).toHaveAttribute('aria-current', 'step');

        // No scenario in the draft yet, and the page says so through `AbsenceValue`'s disclosure:
        // the summary carries the reason and the marker is inside it, so there is no bare
        // "Scenario: N/A" text node to read (`DESIGN.md` §5(a)).
        const scenarioAbsence = page.locator('details').filter({ hasText: /so a card's Scenario Link cannot be checked/ });
        await expect(scenarioAbsence).toBeVisible();
        await expect(scenarioAbsence).toContainText('N/A');

        // Six named positions, the friend role on position six whatever card may sit there
        // (`ADR-0014` correction 1), and an empty slot stated as a statement rather than a blank.
        const slots = page.locator('ul > li[id^="deck-slot-"]');
        await expect(slots).toHaveCount(6);
        await expect(page.locator('#deck-slot-6')).toContainText('Slot 6 · Friends');
        await expect(page.locator('#deck-slot-6')).toContainText('Friend slot');
        await expect(page.locator('#deck-slot-1')).toContainText('Not equipped');

        // The flag is `N/A` with a reason before any write. A default of "Owned" would be the
        // invented fact the Floor forbids.
        await expect(page.getByText('No ownership has been recorded for this slot yet.')).toHaveCount(6);

        // The seven-types legend and the ownership-prose block are cut (Phase E): `career-deck.html`
        // draws neither. The type words still render inside the picker's dialog legend
        // (`CardPicker.vue`), and the ownership destination is stated on the run-scoped builder
        // (`resources/js/pages/Support/Builder.vue`), which the page's own header comment records.
        await expect(page.getByText(/Preflight writes it onto the run\s+as one value per slot/)).toHaveCount(0);
        await expect(page.getByText(/reads\s+as N\/A rather than as Owned/)).toHaveCount(0);
    });

    test('equips from the picker, marks the flag, and reads each write back from the session', async ({ page }) => {
        await hydrate(page, '/career/setup/deck');

        // The slot owns the picker aim: the empty slot's own button opens the dialog for Slot 1
        // (R4-20/R4-21 moved this off the page-level select the older case drove).
        await page.getByRole('button', { name: 'Choose a card for Slot 1' }).click();
        const dialog = page.getByRole('dialog', { name: 'Choose a card for Slot 1' });
        await expect(dialog).toBeVisible();

        await dialog.locator('input[name="q"]').fill('Agnes Digital');
        await dialog.getByRole('button', { name: 'Filter' }).click();
        await expect(page).toHaveURL(/q=Agnes(\+|%20)Digital/);
        const agnes = dialog.locator('ul > li').filter({ hasText: 'Agnes Digital' }).first();
        await expect(agnes).toBeVisible();

        // The dialog's own Equip button names the card it equips, so there is no select to read back.
        await agnes.getByRole('button', { name: 'Equip', exact: true }).click();
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
        await expect(page.getByText('No ownership has been recorded for this slot yet.')).toHaveCount(6);
    });

    test('sizes the deck-step controls to the 44px contract', async ({ page }) => {
        await hydrate(page, '/career/setup/deck');

        // The picker's controls live inside its dialog, so they have no box until the dialog is open.
        await page.getByRole('button', { name: 'Choose a card for Slot 1' }).click();
        await expect(page.getByRole('dialog', { name: 'Choose a card for Slot 1' })).toBeVisible();

        for (const selector of ['select[name="type"]', 'input[name="q"]']) {
            await assertMinHeight(page, selector);
        }

        // "Filter" is the dialog's one submit, and `h-11` like every other control. The step's old
        // page-level "Save deck" submit is cut (Phase E): the forward door writes before it leaves.
        const submits = await page.locator('button[type="submit"]').all();
        expect(submits).toHaveLength(1);
        for (const submit of submits) {
            const box = await submit.boundingBox();
            expect(box?.height ?? 0, 'a submit control is not sized to the 44px contract').toBeGreaterThanOrEqual(44);
        }

        for (const label of ['Choose a card for Slot 1', 'Owned', 'Rented']) {
            const box = await page.getByRole('button', { name: label, exact: true }).first().boundingBox();
            expect(box?.height ?? 0, `"${label}" is not sized to the 44px contract`).toBeGreaterThanOrEqual(44);
        }
    });

    test('reflows to one column at 320px without two-dimensional document scroll', async ({ page }) => {
        await hydrate(page, '/career/setup/deck');
        await page.setViewportSize({ width: 320, height: 720 });
        await page.locator('#app > *').first().waitFor();

        // WCAG 1.4.10 Reflow: the two-column grid stacks, and the document itself never scrolls
        // sideways.
        await expect(page.getByRole('heading', { name: /Deck\s+\d+ of 6 slots filled/ })).toBeVisible();

        // The picker is a dialog opened by a slot, so its heading is asserted where the page renders
        // it: open the dialog and measure the document with it up.
        await page.getByRole('button', { name: 'Choose a card for Slot 1' }).click();
        await expect(page.getByRole('heading', { name: 'Choose a card for Slot 1' })).toBeVisible();

        const overflow = await page.evaluate(
            () => document.documentElement.scrollWidth <= document.documentElement.clientWidth + 1,
        );
        expect(overflow, 'the document scrolls horizontally at 320px').toBe(true);
    });

    test('reflows the legacy tables inside their own regions at 320px', async ({ page }) => {
        await hydrate(page, '/career/setup/legacy');
        await page.setViewportSize({ width: 320, height: 720 });
        await page.locator('#app > *').first().waitFor();

        // DESIGN.md §6: a wide table scrolls inside its own focusable container, never the document.
        // Measured 2026-10-28: both legacy tables painted 331px into a 320px viewport and the document
        // scrolled 44px sideways, because neither had a container.
        //
        // The outcome that rule pins is the document's: it never scrolls sideways. Whether a given
        // table is wide enough to need its container is a content fact, re-measured 2026-10-29 after
        // the card primitive landed: the mechanism table fits 254px and the projected-start table
        // still scrolls (331px into 254px). Both must sit in a focusable region either way.
        for (const label of ['What inheritance actually does, scrollable', 'Projected start, scrollable']) {
            const region = page.getByRole('region', { name: label });
            await expect(region).toBeVisible();
            await expect(region).toHaveAttribute('tabindex', '0');
            await expect(region.locator('table')).toHaveCount(1);
        }

        const overflow = await page.evaluate(
            () => document.documentElement.scrollWidth - document.documentElement.clientWidth,
        );
        expect(overflow, 'the document scrolls horizontally at 320px').toBeLessThanOrEqual(1);
    });

    test('carries the ancestry and the deck through the steps and writes no run behind them', async ({ page }) => {
        // Step 4 first: one recorded grade. The ancestry-entry half of this case retired with the
        // step's editor (Phase D); the draft key it now proves carries is the Affinity grade.
        await hydrate(page, '/career/setup/legacy');
        await page.locator('#affinity').selectOption('◎');
        await page.getByRole('button', { name: 'Save Legacy' }).click();
        await expect(page.getByText('Legacy recorded.')).toBeVisible();

        // Step 5 through the page's own primary action: one equipped card. The card is whichever the
        // deterministic picker order puts first, so the test lifts its name off the rendered slot
        // rather than hardcoding today's first row.
        await page.getByRole('link', { name: 'Continue to deck' }).click();
        await page.locator('#app > *').first().waitFor();
        await expect(page).toHaveURL(/\/career\/setup\/deck/);
        await page.getByRole('button', { name: 'Choose a card for Slot 1' }).click();
        const dialog = page.getByRole('dialog', { name: 'Choose a card for Slot 1' });
        await expect(dialog).toBeVisible();
        await dialog.getByRole('button', { name: 'Equip', exact: true }).first().click();
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

        // Back to step 4 through the page's own Back control: the value stays because
        // `SetupDraft::write()` merges and neither step owns the other's keys. It is read back off the
        // session, not remembered. The step bar is a status readout, not a second navigation, so the
        // round trip uses the same door a Trainer uses.
        await page.getByRole('link', { name: 'Back', exact: true }).click();
        await page.locator('#app > *').first().waitFor();
        await expect(page.locator('#affinity')).toHaveValue('◎');
        await expect(page.getByText(/This setup already holds a Legacy/)).toBeVisible();

        // And forward again: the deck survived the round trip too.
        await page.getByRole('link', { name: 'Continue to deck' }).click();
        await page.locator('#app > *').first().waitFor();
        await expect(page).toHaveURL(/\/career\/setup\/deck/);
        await expect(page.locator('#deck-slot-1')).toContainText(cardName);

        // The last shared invariant: writes across both steps, and still no run. The two steps
        // store draft keys precisely because the run arrives at Preflight (`ADR-0020` §1).
        await hydrate(page, '/training-runs');
        await expect(page.getByText('No runs yet')).toBeVisible();
    });
});
