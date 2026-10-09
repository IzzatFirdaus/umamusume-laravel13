import { test, expect } from '@playwright/test';
import { deleteRun } from '../utils/delete-run';

// Rendered-DOM evidence for the deck builder (SCREEN-007, `SCR-CAR-007`). The server-side
// `SupportDeckBuilderTest` asserts the resolved props; these assert what only a browser can reach: the
// keyboard replacement path and where focus lands afterwards, the 44px sweep across the six slots and
// the picker's controls, and the analysis lines carrying their numbers beside their labels.
//
// Fixture strategy follows `run-detail.spec.ts`: the seeded scratch database holds no training runs, so
// this spec creates one through the create form, works on it, and deletes it in `afterEach` even when an
// assertion throws. The card catalogue is seeded (559 rows), so the picker has real cards to equip.

const TRAINEE = 'Agnes Digital';
const createdRunUrls: string[] = [];

test.afterEach(async ({ page }) => {
    for (const url of createdRunUrls.splice(0)) {
        await deleteRun(page, url);
    }
});

async function builderUrl(page: import('@playwright/test').Page, scenario: string): Promise<string> {
    // The picker's 25 thumbnails cost 1.3-2.4s each on this host (`/artwork/support_thumb/10001`
    // measured at 2.40s for 67 KB), and `artisan serve` is a single process on Windows
    // (`PHP_CLI_SERVER_WORKERS` is ignored: six concurrent requests staircased 14s, 19s, 24s, 29s, 35s,
    // 41s). One page view of this screen therefore queues ~50s of PHP work that every later navigation
    // in the file waits behind, which is what turned a 17-test file into a 20-minute one.
    //
    // No assertion in this file reads a decoded image. `ArtworkSlot` puts the `<img>` in the DOM as soon
    // as the mirror holds a file, so the requests are refused at the browser and the markup these tests
    // measure is the same markup. Same `page.route` lever `dashboard.spec.ts` uses for its error state.
    await page.route('**/artwork/**', (route) => route.abort());

    // `domcontentloaded` on every navigation, for the reason `legacy.spec.ts`'s header gives: `load`
    // waits on the module graph and one loopback request per artwork frame, which is host speed rather
    // than a property of this screen.
    await page.goto('/training-runs/create', { waitUntil: 'domcontentloaded' });
    await page.locator('#app > *').first().waitFor();
    await page.locator('#trainee-combobox').fill(TRAINEE);
    await page.keyboard.press('Enter');
    await page.selectOption('select[name="scenario"]', { value: scenario });
    await page.getByRole('button', { name: 'Create run' }).click();
    await page.waitForURL(/\/training-runs\/\d+\/cockpit$/, { waitUntil: 'domcontentloaded' });

    // The bare record URL, not the Cockpit the create redirect lands on: the deck builder is addressed
    // by appending to it (`${runUrl}/deck`).
    const runUrl = page.url().replace(/\/cockpit$/, '');
    createdRunUrls.push(runUrl);

    const url = `${runUrl}/deck`;
    await page.goto(url, { waitUntil: 'domcontentloaded' });
    await page.locator('#app > *').first().waitFor();
    return url;
}

test('renders six slots, each with a labelled ownership toggle, and the seventh-type pickers', async ({ page }) => {
    await builderUrl(page, 'ura_finale');

    // The six positions, in order, with the friend slot named as the position rather than as the card
    // parked in it (ADR-0014 correction 1).
    const slots = page.locator('ul > li[id^="deck-slot-"]');
    await expect(slots).toHaveCount(6);
    await expect(page.locator('#deck-slot-6')).toContainText('Slot 6 · Friends');
    await expect(page.locator('#deck-slot-6')).toContainText('Friend slot');

    // A slot with no card states the gap and offers the way to fill it, rather than showing a blank.
    await expect(page.locator('#deck-slot-1')).toContainText('Not equipped');
    await expect(page.getByRole('button', { name: 'Replace the card in Slot 1' })).toBeVisible();

    // Ownership is a two-button group per slot, each button carrying its word. The state is never a
    // filled box or a colour, which is what `design-2.0` §17 forbids.
    const owned = page.getByRole('button', { name: 'Owned', exact: true });
    const rented = page.getByRole('button', { name: 'Rented', exact: true });
    await expect(owned).toHaveCount(6);
    await expect(rented).toHaveCount(6);
    await expect(owned.first()).toHaveAttribute('aria-pressed', 'true');

    // The seven types are offered as words. `Wit` and `Pal` are the Global client's words for the
    // export's `intelligence` and `friend` keys.
    const typeOptions = await page.locator('select[name="type"] option').allTextContents();
    expect(typeOptions).toEqual(['All', 'Speed', 'Stamina', 'Power', 'Guts', 'Wit', 'Pal', 'Group']);
});

test('sizes every control on the screen to the 44px contract', async ({ page }) => {
    await builderUrl(page, 'ura_finale');

    // The four pickers, the Filter submit, the primary action, the two ownership buttons and Replace.
    // The selects are addressed by name rather than by label: a wrapping label's computed name swallows
    // its option text, so `getByLabel('Type')` also matches the "Type" option inside another select.
    for (const name of ['type', 'rarity', 'status', 'query']) {
        const box = await page.locator(`select[name="${name}"], input[name="${name}"]`).boundingBox();
        expect(box?.height ?? 0, `the ${name} picker is not sized to the 44px contract`).toBeGreaterThanOrEqual(44);
    }

    for (const [selector, what] of [
        ['button:has-text("Filter")', 'Filter'],
        ['button:has-text("Confirm deck")', 'Confirm deck'],
        ['button:has-text("Owned")', 'an Owned toggle'],
        ['button:has-text("Replace")', 'a Replace button'],
    ] as const) {
        const box = await page.locator(selector).first().boundingBox();
        expect(box?.height ?? 0, `${what} is not sized to the 44px contract`).toBeGreaterThanOrEqual(44);
    }
});

test('replaces a slot with the keyboard alone and returns focus to the slot it changed', async ({ page }) => {
    await builderUrl(page, 'ura_finale');

    // The path a keyboard user has: focus the Replace button, press it, then reach the picker's Equip
    // button. No drag, and no per-slot select holding five hundred options.
    const replace = page.getByRole('button', { name: 'Replace the card in Slot 2' });
    await replace.focus();
    await page.keyboard.press('Enter');

    // The picker now writes to slot two, and the button says so in words rather than by position alone.
    // The picker renders one equip button per card row, so the name is a prefix match against many
    // elements; `.first()` is what `runs.spec.ts` and this file's own line below already do. Without
    // it this is a strict-mode violation, not a missing control.
    await expect(page.getByRole('button', { name: /^Equip to Slot 2/ }).first()).toBeVisible();

    // Equip the first card the picker offers, by keyboard.
    const equip = page.getByRole('button', { name: /^Equip to Slot 2/ }).first();
    await equip.focus();
    await page.keyboard.press('Enter');

    // Focus comes back to the slot that changed, so a Trainer replacing several cards in a row never
    // has to re-navigate from the top of the document.
    await expect(page.locator('#deck-slot-2')).toBeFocused();

    // The slot now holds a card, and it is the one the picker wrote there.
    const name = (await page.locator('#deck-slot-2 a').first().innerText()).trim();
    await expect(page.locator('#deck-slot-2')).toContainText(name);
    await expect(page.locator('#deck-slot-2')).not.toContainText('Not equipped');

    // The other five picks are untouched by the equip, which is what carrying the six in the query
    // string is for.
    await expect(page.locator('#deck-slot-1')).toContainText('Not equipped');
    await expect(page.locator('#deck-slot-3')).toContainText('Not equipped');
});

test('shows the analysis figures with their labels and no bar', async ({ page }) => {
    await builderUrl(page, 'ura_finale');

    // The empty state says what is missing, why it matters and what to do, rather than showing six
    // categories of zeros.
    await expect(page.getByText('Nothing to analyse yet.')).toBeVisible();

    // Put one card in so the analysis has something to read.
    await page.getByRole('button', { name: 'Replace the card in Slot 1' }).click();
    await page.getByRole('button', { name: /^Equip to Slot 1/ }).first().click();
    await expect(page.locator('#deck-slot-1')).not.toContainText('Not equipped');

    const analysis = page.locator('#deck-analysis-heading').locator('..');
    await expect(analysis).toBeVisible();

    // All six categories, each named.
    for (const label of ['Training power', 'Early run', 'Race bonus', 'Safety', 'Events', 'Skills']) {
        await expect(page.getByRole('heading', { name: label, exact: true })).toBeVisible();
    }

    // At least one category now carries a number, and the number is printed beside its own label with
    // the provenance word. The seeded catalogue's first card carries a Friendship Bonus, so the
    // training-power category is the one that fills.
    await expect(page.getByText('summed across the 1 card carrying it').first()).toBeVisible();
    await expect(page.getByText('Calculated', { exact: true }).first()).toBeVisible();

    // A category the deck carries nothing in says so in a sentence naming the group, which is what lets
    // a Trainer tell "no card has this" from "I have not looked yet".
    await expect(page.getByText('No card in the deck carries an effect here.').first()).toBeVisible();

    // No bar anywhere. The brief sketches a five-row bar chart and what ships is the number beside its
    // label, so a progress element would be a score wearing a chart costume.
    await expect(analysis.locator('[role="progressbar"]')).toHaveCount(0);
    await expect(analysis.locator('meter')).toHaveCount(0);

    // The recommended replacement is a named absence, not a card this tool picked.
    await expect(page.getByRole('heading', { name: 'Recommended replacement' })).toBeVisible();
    await expect(page.getByText('Not built.')).toBeVisible();
});

test('keeps the ownership choice on the page and says the run stores it', async ({ page }) => {
    await builderUrl(page, 'ura_finale');

    // The toggle responds at once, with no round trip, and the save then carries the value per slot.
    const rented = page.locator('#deck-slot-3').getByRole('button', { name: 'Rented', exact: true });
    await rented.click();
    await expect(rented).toHaveAttribute('aria-pressed', 'true');
    await expect(page.locator('#deck-slot-3').getByRole('button', { name: 'Owned', exact: true })).toHaveAttribute(
        'aria-pressed',
        'false',
    );

    // What the screen says about the flag it writes. This sentence is the current one: the run-scoped
    // write put the value on `deck_slots.ownership` from `ADR-0023` (D3) onward, and the assertion here
    // still quoted the pre-D3 wording, so it failed against a screen that was telling the truth
    // (round-2 audit R2-06). The per-slot claim is unchanged either way: no slot's value is derived
    // from its position, and an unclassified slot reads as N/A rather than as Owned.
    await expect(
        page.getByText('The owned or rented flag is saved with the deck, one value per slot'),
    ).toBeVisible();
    await expect(page.getByText(/stays on the\s+run after a reload/)).toBeVisible();
});

test('prints the seven types on the picker rows as a word and a glyph, never colour alone', async ({ page }) => {
    await builderUrl(page, 'ura_finale');

    const firstRow = page.locator('#deck-picker-results > li').first();
    await expect(firstRow).toBeVisible();

    // The glyph is decorative and the word beside it carries the meaning, so a screen reader hears the
    // type once and a Trainer who cannot separate the seven fills can still read it.
    const marks = firstRow.locator('svg[aria-hidden="true"]');
    await expect(marks.first()).toBeVisible();
    await expect(firstRow).toContainText(/Speed|Stamina|Power|Guts|Wit|Pal|Group/);
});

test('never paints a placeholder frame or a stranded anchor in the picker', async ({ page }) => {
    await builderUrl(page, 'ura_finale');

    // The invariants `DESIGN.md` §4.7 and `ADR-0021` §7 actually promise, stated so they hold in
    // either mirror state: a slot paints a real file or it paints nothing, and an absent file never
    // becomes `src=""`, a grey box, or an anchor wrapped around nothing.
    //
    // This test used to assert `img` count zero, which encoded one environment — a mirror nobody had
    // filled. `uma:fetch-art` has since run on this tree (666 files under
    // `storage/app/private/artwork`), so that assertion was describing the disk, not the contract.
    // A test that can only pass on an unfilled mirror is the trap AGENTS.md §18 names.
    // A row has to exist before an absence can be measured. On an unhydrated page every `toHaveCount(0)`
    // below passes for the wrong reason, which is the trap `AGENTS.md` §18's empty-mirror note describes
    // from the other side: the old assertion could only pass on an unfilled mirror, and a zero-count on
    // no rows at all can only pass on nothing.
    const rows = page.locator('#deck-picker-results > li');
    await expect(rows.first()).toBeVisible();

    await expect(page.locator('#deck-picker-results img[src=""]')).toHaveCount(0);
    await expect(page.locator('#deck-picker-results img:not([src])')).toHaveCount(0);
    await expect(page.locator('#deck-picker-results a:has(img[src=""])')).toHaveCount(0);

    // Every row still offers its text link, framed or not, and the row name is the link's own text.
    await expect(rows.first().locator('a').first()).toBeVisible();
});

test('narrowing the picker leaves the six picks exactly where they were', async ({ page }) => {
    await builderUrl(page, 'ura_finale');

    await page.getByRole('button', { name: 'Replace the card in Slot 1' }).click();
    await page.getByRole('button', { name: /^Equip to Slot 1/ }).first().click();
    await expect(page.locator('#deck-slot-1')).not.toContainText('Not equipped');

    // A filter click is a `router.get` onto the same route. If the six picks rode in component state
    // rather than the query string, this would empty slot one with no way back.
    await page.locator('select[name="type"]').selectOption('guts');
    await page.getByRole('button', { name: 'Filter' }).click();
    await page.waitForURL(/type=guts/, { waitUntil: 'domcontentloaded' });

    await expect(page.locator('#deck-slot-1')).not.toContainText('Not equipped');
    await expect(page.locator('select[name="type"]')).toHaveValue('guts');
});

test('names the ask when a filter reaches no card rather than showing an empty frame', async ({ page }) => {
    await builderUrl(page, 'ura_finale');

    // No R-rarity group card exists in the catalogue, so this is a valid combination with no rows.
    await page.locator('select[name="rarity"]').selectOption('1');
    await page.locator('select[name="type"]').selectOption('group');
    await page.getByRole('button', { name: 'Filter' }).click();
    await page.waitForURL(/rarity=1/, { waitUntil: 'domcontentloaded' });

    await expect(page.getByText(/Nothing matches rarity R \+ type Group/)).toBeVisible();
});
