import { expect, test } from './support/base-test';

/*
 * N6.5 / TD-08: the Source Ledger narrows in place.
 *
 * TD-08 read the page and found no filter control of any kind: the ledger was a fixed list with no
 * way to ask "which sources were fetched?" or "which carry a weight class?". The plan's acceptance
 * line asks for read-only filters over the facts the payload supports and an announced result count.
 *
 * What this file holds that the parity case cannot: `database-parity.spec.ts` compares the ledger
 * block against a static wireframe, so it can only see drift, not whether a control does what it
 * says. These cases drive the controls and read the rows they leave behind.
 *
 * Facts are read off the page rather than hard-coded: the seeded reference corpus has grown before
 * (`8b177036`) and a second Suzuka card already broke one fixture that named a row count's neighbour,
 * so a number restated here would rot the same way. The one constant is a real property of the
 * config: every fetched dataset carries tier `B`, and `S`/`A` belong to transcribed anchors, so
 * `fetched + S` is deterministically empty.
 */

test.beforeEach(async ({ page }) => {
    await page.goto('/database/sources', { waitUntil: 'domcontentloaded' });
    await page.locator('#app > *').first().waitFor();
});

/** The ledger card: its own region, so the assertions never reach a same-named block elsewhere. */
const ledger = (page: import('@playwright/test').Page): import('@playwright/test').Locator =>
    page.locator('[aria-labelledby="sources-ledger"]');

const rows = (page: import('@playwright/test').Page): import('@playwright/test').Locator =>
    ledger(page).locator('tbody tr');

const status = (page: import('@playwright/test').Page): import('@playwright/test').Locator =>
    ledger(page).getByRole('status');

/** The unfiltered total, read from the page's own sentence rather than assumed. */
async function unfilteredTotal(page: import('@playwright/test').Page): Promise<number> {
    const text = (await status(page).textContent()) ?? '';
    const match = text.match(/^(\d+) sources\./);

    expect(match, `the ledger states its own total, got ${JSON.stringify(text)}`).not.toBeNull();

    return Number(match![1]);
}

/**
 * Waits for the ledger to stop changing after a `selectOption`.
 *
 * `selectOption` resolves when the `<select>` carries the new value, which is a tick before Vue
 * patches the rows under it. Counting rows straight afterwards reads the *previous* conjunction,
 * which is why a chained facet case saw an undated set of 0 while the clear set behind it still
 * held 1. The status sentence is the page's own statement of what is filtered, so reading it until
 * two consecutive reads agree is waiting on the thing the assertions are about rather than on a
 * guessed delay.
 */
async function settle(page: import('@playwright/test').Page): Promise<void> {
    let previous = '';

    for (let attempt = 0; attempt < 40; attempt++) {
        const current = (await status(page).textContent()) ?? '';

        if (current === previous) {
            return;
        }

        previous = current;
        await page.waitForTimeout(50);
    }

    throw new Error(`the ledger's count sentence never settled, last read ${JSON.stringify(previous)}`);
}

test('narrows by kind and leaves only rows of that kind, with the count stated', async ({ page }) => {
    const total = await unfilteredTotal(page);

    expect(total, 'the seeded corpus configures more than one source').toBeGreaterThan(1);
    await expect(rows(page)).toHaveCount(total);

    await page.selectOption('#sources-kind-filter', { value: 'fetched' });

    // The count is announced, and it agrees with the rows actually rendered: a filter whose label
    // lies about how much it narrowed is the failure this screen was built to remove.
    const fetchedText = (await status(page).textContent()) ?? '';
    const fetchedMatch = fetchedText.match(/^(\d+) of (\d+) sources match\./);
    expect(fetchedMatch, `a filtered ledger announces "N of M sources match", got ${JSON.stringify(fetchedText)}`).not.toBeNull();
    expect(Number(fetchedMatch![2])).toBe(total);

    const fetchedRows = await rows(page).count();
    expect(Number(fetchedMatch![1])).toBe(fetchedRows);
    expect(fetchedRows, 'no fetched dataset is configured, so the kind filter has nothing to show').toBeGreaterThan(0);

    for (const cell of await rows(page).locator('td').nth(0).allTextContents()) {
        expect(cell.trim()).toBe('Fetched');
    }

    await page.selectOption('#sources-kind-filter', { value: 'transcribed' });

    const transcribedText = (await status(page).textContent()) ?? '';
    const transcribedMatch = transcribedText.match(/^(\d+) of (\d+) sources match\./);
    expect(transcribedMatch, `the transcribed half is counted too, got ${JSON.stringify(transcribedText)}`).not.toBeNull();

    const transcribedRows = await rows(page).count();
    expect(Number(transcribedMatch![1])).toBe(transcribedRows);
    expect(transcribedRows, 'the reference anchors are transcribed rows; at least one is expected').toBeGreaterThan(0);

    for (const cell of await rows(page).locator('td').nth(0).allTextContents()) {
        expect(cell.trim()).toBe('Transcribed');
    }
});

test('states an empty filtered result as a result, not as an empty build, and clears back', async ({ page }) => {
    const total = await unfilteredTotal(page);

    // Every fetched dataset's config tier is `B`; `S` belongs to the transcribed anchors. So this
    // pair matches nothing by construction, and the page must say that it matched nothing.
    await page.selectOption('#sources-kind-filter', { value: 'fetched' });
    await page.selectOption('#sources-tier-filter', { value: 'S' });

    await expect(rows(page)).toHaveCount(0);

    const empty = ledger(page).getByText(/No sources match/);
    await expect(empty).toBeVisible();
    // The words name both constraints, because a bare "nothing here" reads as a build with no
    // sources — the other empty state this page deliberately keeps distinct.
    await expect(empty).toContainText('The ledger lists the sources this build actually reads');

    // Two exist while the result is empty: the empty-result notice carries its own, and the control
    // bar keeps the general one. Either clears all three scopes, so the first is enough here.
    await ledger(page).getByRole('button', { name: 'Clear filters' }).first().click();

    await expect(rows(page)).toHaveCount(total);
    expect(((await status(page).textContent()) ?? '').trim()).toBe(`${total} sources.`);
});

test('narrows by read date without inventing a date, and every surviving row agrees', async ({ page }) => {
    await page.selectOption('#sources-kind-filter', { value: 'fetched' });
    const fetchedTotal = await rows(page).count();

    await page.selectOption('#sources-read-filter', { value: 'undated' });

    // "Undated" is the absence itself: a row that survived this filter prints no `<time>`, so the
    // filter and the cell cannot disagree about which datasets were ever fetched.
    const undatedRows = await rows(page).count();
    for (const row of await rows(page).all()) {
        await expect(row.locator('time')).toHaveCount(0);
        await expect(row.locator('td').nth(3)).toContainText('No fetch recorded');
    }

    await page.selectOption('#sources-read-filter', { value: 'dated' });

    const datedRows = await rows(page).count();
    for (const row of await rows(page).all()) {
        await expect(row.locator('time')).toHaveCount(1);
    }

    // The two halves partition the fetched set: a row counted twice would mean the predicate is not
    // one question, and a row counted never would mean the control drops data silently.
    expect(undatedRows + datedRows).toBe(fetchedTotal);
});

/*
 * TD-08's third facet (D-2 landed): the server stamps each row with the *open* conflicts that name
 * it, and the control asks which rows that list is non-empty for.
 *
 * What these cases check beyond "the control filters": that the two halves partition the ledger
 * (so no row is invisible to every setting), that combining the facets narrows rather than
 * replaces, and that the stamp on the page agrees with the filter that used it. The stamp is read
 * from the rendered row rather than restated as a number, because the association is config data
 * and a count written here would rot the way the second Suzuka card broke `8b177036`.
 */

test('narrows by open-conflict involvement, and the two halves partition the ledger', async ({ page }) => {
    const total = await unfilteredTotal(page);

    // What each of those rows prints, by id. The ledger's door text is asserted against this
    // vocabulary rather than against a substring of it: a door reading
    // `conflict-distance-band-1400m` must not pass because `conflict-distance-band-1400` is in it.
    // A serialised array of pairs, not a `Map`: the page context has its own `Map` and the host realm
    // would reject a cross-realm object on the way back.
    const questions = (await page.locator('[data-conflict-id]').evaluateAll((nodes) =>
        nodes.map((node) => [
            node.getAttribute('data-conflict-id') ?? '',
            (node.querySelector('th')?.textContent ?? '').trim(),
        ]),
    )) as [string, string][];

    const questionsById = new Map(questions);

    expect(questions.length, 'the page renders no open-conflict rows to point at').toBeGreaterThan(0);

    await page.selectOption('#sources-conflict-filter', { value: 'involved' });
    await settle(page);

    const involved = await rows(page).count();

    expect(involved, 'no source is named in an open conflict (D-2)').toBeGreaterThan(0);

    // The stamp and the printed door are two reads of one list, so they are read together: the row's
    // own ids, and the door each id prints. A row stamped with an id it does not print, or one
    // printed without a stamp behind it, would mean the facet narrows on something other than what
    // the ledger says.
    // The stamp sits on the row's own `<span>`, not on the `<tr>`, so it is read through the same
    // selector the doors are. `data-named-in` is the facet's predicate in the DOM: the control
    // narrows on this list, and the list is what a Trainer reads beside the row's name.
    const namedIn = (row: import('@playwright/test').Locator) => row.locator('[data-named-in]');

    for (const row of await rows(page).all()) {
        const stamp = namedIn(row);

        await expect(stamp, 'a row left by the "involved" facet carries no conflict stamp').toHaveCount(1);

        const named = (await stamp.getAttribute('data-named-in')) ?? '';
        const ids = named.trim() === '' ? [] : named.trim().split(/\s+/);

        expect(ids.length, 'a row left by the "involved" facet carries no conflict id').toBeGreaterThan(0);
        expect(new Set(ids).size, `${ids.join(' ')} repeats an id, so a door printed twice`).toBe(ids.length);

        for (const id of ids) {
            // The door opens the card that prints both sides, and the question it lands on is the
            // one the record carries - so a Trainer following "Named in
            // conflict-distance-band-1400m" reads the disagreement, not a table row.
            expect(questionsById.get(id), `${id} is stamped but has no question recorded`).toBeTruthy();
        }

        const doors = stamp.locator('a');

        expect(await doors.count(), `${ids.join(' ')} is stamped but not printed as a door`).toBe(ids.length);
        expect(await doors.allTextContents(), 'the doors are not the stamped ids').toEqual(ids);

        for (let index = 0; index < ids.length; index++) {
            expect(
                await doors.nth(index).getAttribute('href'),
                `the door for ${ids[index]} does not point at its own card`,
            ).toBe(`/database/sources#${ids[index]}`);
        }
    }

    // The two halves partition the ledger: a row counted in neither would be a row no setting of
    // this control can reach, which is exactly what a partition assertion is for.
    await page.selectOption('#sources-conflict-filter', { value: 'clear' });
    await settle(page);

    const clear = await rows(page).count();

    expect(clear, 'every source is named in an open conflict').toBeGreaterThan(0);
    expect(involved + clear, 'the two facets do not partition the ledger').toBe(total);

    for (const row of await rows(page).all()) {
        await expect(row.locator('[data-named-in]'), 'the "clear" facet left a stamped row').toHaveCount(0);
    }

    // Back to "any" restores the whole ledger: a facet that dropped a row for good would be a
    // control the Trainer cannot undo, and the reset is the same claim one step earlier.
    await page.selectOption('#sources-conflict-filter', { value: 'all' });

    await expect(rows(page)).toHaveCount(total);
    expect(((await status(page).textContent()) ?? '').trim()).toBe(`${total} sources.`);
});

test('combines all four facets, and still announces a count that agrees with the rows', async ({ page }) => {
    const total = await unfilteredTotal(page);

    // The stamped keys are all `config('uma.sources')` entries, so they resolve onto fetched rows
    // only. The conjunction is therefore empty by construction, and the page must call that an empty
    // *result* rather than an empty build.
    await page.selectOption('#sources-conflict-filter', { value: 'involved' });
    await page.selectOption('#sources-kind-filter', { value: 'transcribed' });

    await expect(rows(page)).toHaveCount(0);
    await expect(ledger(page).getByText(/No sources match/)).toBeVisible();

    // All four facets, one ledger: read date is crossed with the conflict facet, then the tier, then
    // kind last. Each step can only hold or drop rows, because the facets are conjoined; a step that
    // added a row would mean one of them was being ignored. Kind goes last because it is the only
    // facet whose surviving set is *known* here - the stamped keys resolve onto fetched rows only -
    // so a tier or read-date step before it would be measured against a mixed set and prove nothing.
    await page.selectOption('#sources-kind-filter', { value: 'fetched' });
    await settle(page);

    const fetchedRows = await rows(page).count();

    expect(fetchedRows, 'no fetched dataset is configured').toBeGreaterThan(0);
    expect(fetchedRows).toBeLessThan(total);

    // The fetched-undated set, before the conflict facet divides it. The conflict facet still reads
    // "involved" above, so the set is re-read here with it open ("all") to hold the whole that the
    // involved and clear halves are about to partition.
    await page.selectOption('#sources-conflict-filter', { value: 'all' });
    await page.selectOption('#sources-read-filter', { value: 'undated' });
    await settle(page);

    const fetchedUndated = await rows(page).count();

    // The involved half, then the clear half. Each is a subset of the fetched-undated set and together
    // they are the whole of it: the conflict facet is the one place a row is either named by an open
    // record or not, with no third state.
    await page.selectOption('#sources-conflict-filter', { value: 'involved' });
    await settle(page);

    const undatedRows = await rows(page).count();

    expect(undatedRows).toBeLessThanOrEqual(fetchedUndated);

    await page.selectOption('#sources-conflict-filter', { value: 'clear' });
    await settle(page);

    const clearUndated = await rows(page).count();

    expect(clearUndated).toBeLessThanOrEqual(fetchedUndated);
    expect(clearUndated + undatedRows, 'the involved and clear facets do not partition the fetched-undated set')
        .toBe(fetchedUndated);

    // `B` is the class the config records on every fetched source, so on the fetched half this facet
    // can only hold what is already there. Equality, not a bound: a drop here would mean the tier
    // option and the rows disagree, and a growth would mean the tier was being ignored.
    await page.selectOption('#sources-tier-filter', { value: 'B' });
    await settle(page);

    const finalRows = await rows(page).count();

    expect(finalRows).toBe(clearUndated);
    for (const cell of await rows(page).locator('td').nth(1).allTextContents()) {
        expect(cell.trim(), 'a row of another tier survived the tier facet').toBe('B');
    }

    // The announced count is the conjunction's own: the rows left, out of the whole ledger, and it
    // must agree with what is on screen rather than with what any one facet did.
    const announced = (await status(page).textContent()) ?? '';
    const match = announced.match(/^(\d+) of (\d+) sources match\./);

    expect(match, `a four-facet ledger announces "N of M sources match", got ${JSON.stringify(announced)}`).not.toBeNull();
    expect(Number(match![2])).toBe(total);
    expect(Number(match![1])).toBe(finalRows);

    // What survived is one kind, one tier, one read-date half and unstamped, which is what makes the
    // four facets legible rather than a count the Trainer has to take on trust.
    for (const cell of await rows(page).locator('td').nth(0).allTextContents()) {
        expect(cell.trim()).toBe('Fetched');
    }

    for (const row of await rows(page).all()) {
        await expect(row.locator('[data-named-in]'), 'a row none names it survived').toHaveCount(0);
    }

    // One clear returns the ledger whole, because the reset drops the facet set and not just the
    // select the Trainer last touched.
    await ledger(page).getByRole('button', { name: 'Clear filters' }).first().click();

    await expect(rows(page)).toHaveCount(total);
    expect(((await status(page).textContent()) ?? '').trim()).toBe(`${total} sources.`);
});

/*
 * N2.11: the ledger names which provenance store dated each fetched row.
 *
 * The server unions two arms — the `data_sources` ledger of requests this tool performed and the
 * reference rows' own inline `source_url`/`fetched_at` pair (ADR-0003 R3) — and stamps the arm that
 * supplied each date. The screen has to surface that distinction: a ledger date and an inline date
 * are different facts, and a row dated by neither is an absence, not a zero. These cases are
 * state-derived — they read what the seeded corpus actually rendered rather than restating a count —
 * because the corpus has grown before and a hard-coded row count would rot the same way.
 */
test('a dated fetched row names the provenance arm that dated it (N2.11)', async ({ page }) => {
    await page.selectOption('#sources-kind-filter', { value: 'fetched' });

    // Every dated row carries exactly one channel label, and it is one of the two stores the server
    // names. A row dated with no label, or a label naming a store the server does not ship, would
    // mean the screen invented a provenance the payload does not hold.
    const dated = rows(page).filter({ has: page.locator('time') });
    const datedCount = await dated.count();

    if (datedCount === 0) {
        // A build whose every fetched row is undated has nothing to label; the undated case below
        // still holds, and this case has no positive evidence to assert.
        return;
    }

    for (const row of await dated.all()) {
        await expect(row.locator('time')).toHaveCount(1);

        const channel = row.locator('[data-channel]');
        await expect(channel, 'a dated row names no provenance arm').toHaveCount(1);

        const value = await channel.getAttribute('data-channel');
        expect(
            value === 'data_sources' || value === 'inline',
            `a dated row names an unknown provenance arm ${JSON.stringify(value)}`,
        ).toBe(true);

        // The label is the Trainer-facing word for that arm, not the raw table name: the ledger page
        // never explains what `data_sources` is, so the cell has to say "the fetch ledger".
        const label = ((await channel.textContent()) ?? '').trim();
        expect(label, `the ${value} arm prints an empty label`).not.toBe('');
        expect(label, `the ${value} arm prints its raw table name rather than a Trainer word`).not.toBe(value);
    }
});

/*
 * The undated half of N2.11: a fetched row the server could not date says so in words and carries
 * no channel label. This is the absence arm — "No fetch recorded" and "fetched in March" are
 * different facts — and a channel span beside an absent date would be a fabricated provenance.
 */
test('a fetched row with no recorded fetch prints the absence and no channel (N2.11)', async ({ page }) => {
    await page.selectOption('#sources-kind-filter', { value: 'fetched' });
    await page.selectOption('#sources-read-filter', { value: 'undated' });

    const undated = rows(page);

    // "Undated" is the absence itself: every surviving row prints the words, no `<time>`, and no
    // channel span. The three assertions are one fact read three ways — a row that printed a date
    // here would mean the read filter and the cell disagree about which datasets were ever fetched.
    for (const row of await undated.all()) {
        await expect(row.locator('td').nth(3)).toContainText('No fetch recorded');
        await expect(row.locator('time')).toHaveCount(0);
        await expect(row.locator('[data-channel]')).toHaveCount(0);
    }
});

/*
 * TD-08's historical-involvement facet, as the page states it.
 *
 * The five states are a backend computation, and `DatabaseSourceProvenanceTest` proves the
 * arithmetic. What it cannot prove is that the screen says what it was given: a page that printed
 * "No record" for `unknown` would pass every backend case and still hand the Trainer a clean bill
 * of health the record does not support, which is the one outcome the state exists to prevent.
 *
 * So this case reads the rendered states and asserts the vocabulary rather than a count. It does not
 * force a resolved or mixed state into the seeded corpus: inventing a production conflict here would
 * be exactly the fabrication the task forbids, and the resolved/mixed presentation is proven against
 * config fixtures in the feature suite instead.
 */
test('states each source\'s dispute history in words, never as a colour alone (TD-08)', async ({ page }) => {
    const stamps = page.locator('[data-history-state]');
    const count = await stamps.count();

    expect(count, 'the ledger states no dispute history at all').toBeGreaterThan(0);

    const vocabulary = ['open', 'resolved', 'mixed', 'none', 'unknown'];
    const seen = new Set<string>();

    for (const stamp of await stamps.all()) {
        const state = await stamp.getAttribute('data-history-state');

        expect(vocabulary, `a row reports an unknown state ${JSON.stringify(state)}`).toContain(state);
        seen.add(state as string);

        // The state is a word, not an ink: the rendered text is readable with the stylesheet gone.
        const words = ((await stamp.textContent()) ?? '').trim();
        expect(words, `the ${state} state prints no words`).not.toBe('');

        // And the row's own explanation travels with it, so `unknown` cannot be mistaken for clear.
        const note = await stamp.getAttribute('title');
        expect(note ?? '', `the ${state} state carries no explanation`).not.toBe('');
    }

    // `unknown` and `none` are the pair the distinction exists for, so their wording must differ:
    // a page that printed "No record" for both would be unable to say which one it found.
    if (seen.has('unknown') && seen.has('none')) {
        const unknownText = await page.locator('[data-history-state="unknown"]').first().innerText();
        const noneText = await page.locator('[data-history-state="none"]').first().innerText();

        expect(unknownText.trim()).not.toBe(noneText.trim());
    }

    // Filtering by one state leaves only rows in that state, and the count agrees.
    await page.selectOption('#sources-history-filter', { value: 'unknown' });
    await settle(page);

    const narrowed = await rows(page).count();

    for (const row of await rows(page).all()) {
        await expect(row.locator('[data-history-state]')).toHaveAttribute('data-history-state', 'unknown');
    }

    const announced = ((await status(page).textContent()) ?? '').trim();
    const match = announced.match(/^(\d+) of (\d+) sources match\.$/);

    expect(match, `the history facet does not announce a count, got ${JSON.stringify(announced)}`).not.toBeNull();
    expect(Number(match![1])).toBe(narrowed);
});

/*
 * N2.11's other half: an undated row names no channel, so a channel span would be a provenance the
 * payload does not hold. The dated arm is asserted above; this case is the absence arm read as a
 * pair, so neither half can drift without the other failing.
 */
test('a row with no recorded channel prints no channel label, and a dated one names its arm (N2.11)', async ({ page }) => {
    const all = await page.locator('tbody tr').all();

    expect(all.length, 'the ledger rendered no rows').toBeGreaterThan(0);

    for (const row of all) {
        const time = row.locator('time');
        const channel = row.locator('[data-channel]');

        if (await time.count() === 0) {
            // No date at all: no channel, because nothing dated it.
            await expect(channel, 'a row with no date names a provenance arm').toHaveCount(0);
            continue;
        }

        // A date exists, so the arm that supplied it is named. A transcribed row is the one
        // exception the payload models: its date is its anchor, which the transcribing pass wrote
        // itself, so it carries no cross-store channel and must not invent one.
        const channelCount = await channel.count();

        if (channelCount === 0) {
            await expect(row).toContainText('Transcribed');
            continue;
        }

        const value = await channel.getAttribute('data-channel');

        expect(['inline', 'data_sources'], `an unknown provenance arm ${JSON.stringify(value)}`).toContain(value);
    }
});
