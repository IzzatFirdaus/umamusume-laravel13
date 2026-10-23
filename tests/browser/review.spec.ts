import { test, expect } from '@playwright/test';
import { expectTapTargets } from '../utils/tap-targets';
import { buildAxe, openAllDisclosures } from '../utils/accessibility';
import { clearPendingCandidates, seedPendingCandidates } from '../utils/review-candidates';

// Rendered-copy and accessibility evidence for the ported Review queue (SCR-REV-001, F14).
// The server-side ReviewFormAccessibilityTest asserts the props; here the browser computes the
// accessible names, which is the DOM relation the a11y gate is about.
//
// Fixture strategy: the queue is filled by the match pipeline, which needs the network, so a seeded
// scratch database holds no rows and every case here used to skip. `tests/utils/review-candidates.ts`
// writes the pending rows the page reads and removes them afterwards, so each case exercises a real
// queue instead of reporting "no pending candidates".

test.beforeEach(() => {
    clearPendingCandidates();
});

test.afterEach(() => {
    clearPendingCandidates();
});

// MOVED (FQ-10): "every verdict control is named after its own candidate" now asserts on the detail
// page, where the controls render — see tests/browser/review-detail.spec.ts.

// MOVED (FQ-10): "a failed verdict shows its message" now asserts on the detail page — the form that
// refuses the submission renders there. See tests/browser/review-detail.spec.ts.

test('every control on the queue meets the 44px floor', async ({ page }) => {
    seedPendingCandidates(2, { suggested: true });
    seedPendingCandidates(2);
    await page.goto('/review');
    await page.locator('#app > *').first().waitFor();

    const empty = page.getByText('Nothing pending.');
    if (await empty.isVisible().catch(() => false)) {
        test.skip(true, 'No pending candidates in the scratch database to measure.');
    }

    // The queue is the keyboard-first screen and its primary commit spent 2026-10-06 to 2026-10-13
    // under the house bar as the only enamel control without a height floor; this sweep is the pin.
    await expectTapTargets(page.locator('main a, main button, main select, main input'), 4);
});

test('each card explains itself: count, pill, badge, reason, and one primary-action accent', async ({ page }) => {
    seedPendingCandidates(2, { suggested: true });
    seedPendingCandidates(2);
    await page.goto('/review');
    await page.locator('#app > *').first().waitFor();

    const empty = page.getByText('Nothing pending.');
    if (await empty.isVisible().catch(() => false)) {
        test.skip(true, 'No pending candidates in the scratch database.');
    }

    // #11 replaced the "N of M items to resolve" sentence with a progress bar; the count now lives
    // beside the bar as "N of M resolved" and is pinned by the progress case below.
    const card = page.locator('main ul > li').first();

    // 6A.6 + 6A.2: a status pill and a shared provenance badge carry what the metadata row used to
    // print as bare `tier · source · date` text; the date keeps its own element beside the badge.
    await expect(card.getByText(/^(Fuzzy match|No match)$/)).toBeVisible();
    await expect(card.locator('[role="img"]').first()).toBeVisible();
    await expect(card.locator('time')).toBeVisible();

    // 6A.1 + 6A.9: every card carries either the reason it has no suggestion or the hint that
    // names the id it does suggest.
    await expect(card.getByText(/No suggestion:|Suggested id:/)).toBeVisible();

    // DESIGN.md §5 (b), 2026-10-17: one provenance-state label per fact, and `ProvenanceBadge` is the
    // single owner of the state word. Before this case a no-match row rendered both
    // `StatusBadge("No match")` and the badge's `Unknown` for the same tier fact — two labels, one
    // state. A `toHaveCount(0)` on the pill would pass on a card that never had one, so this asserts
    // the badge is still present (the state word survives) and the duplicate word is gone.
    await expect(card.locator('[role="img"]').first()).toBeVisible();
    // The badge is the one label left, so its state word is the card's state word. Asserted on the
    // badge's accessible name rather than on visible text: the state is spelled in the badge's
    // `aria-label`, and the duplicate pill was the only visible copy of it.
    await expect(card.locator('[role="img"]').first()).toHaveAttribute('aria-label', /^(Unknown|Estimated|Calculated)/);
    await expect(card.getByText('No match', { exact: true })).toHaveCount(0);

    // 6A.4, 6A.3 and 6A.10 moved to review-detail.spec.ts with the form: the accented primary, the
    // visible "Verdict" label and the `aria-busy` form are the detail page's now (FQ-10).
    //
    // What the queue still owns is the identity it always carried: the card explains itself, and the
    // row it summarises links to the page that decides.
    await expect(card.getByTestId('review-detail-link')).toBeVisible();
});

test('lists the rows the engine could suggest before the ones it could not', async ({ page }) => {
    seedPendingCandidates(1, { suggested: true });
    seedPendingCandidates(2);
    await page.goto('/review');
    await page.locator('#app > *').first().waitFor();

    const empty = page.getByText('Nothing pending.');
    if (await empty.isVisible().catch(() => false)) {
        test.skip(true, 'No pending candidates in the scratch database.');
    }

    // The seeded queue holds exactly one suggested row, and every seeded row shares one fetch
    // timestamp, so before the ordering fix the list opened on a row with no suggestion at all.
    // 6A.1's suggestion-first order is what puts the click-decidable row where the Trainer meets it.
    await expect(page.locator('main ul > li').first()).toContainText('Suggested id:');
});

// MOVED (FQ-10): "gates Resolve on a chosen verdict" now asserts on the detail page, where the verdict
// control and its Resolve button live. See tests/browser/review-detail.spec.ts.

// MOVED (FQ-10): "lines the three controls up on one baseline" now asserts on the detail page, where
// `review-form-row` renders. See tests/browser/review-detail.spec.ts.

test('splits the queue into the decidable half and the research half', async ({ page }) => {
    seedPendingCandidates(2, { suggested: true });
    seedPendingCandidates(3);

    await page.goto('/review');
    await page.locator('#app > *').first().waitFor();

    // The controller answers one paginated list, already ordered suggestion-first, so the split is
    // presentation: two named sections, and no card in the wrong half. The counts are read from the
    // page rather than pinned, because the queue is shared state: a seed that is already there (the
    // pipeline's own pending rows) lands in one of these sections too.
    const decidable = page.locator('section[aria-labelledby="review-with-suggestion"]');
    const research = page.locator('section[aria-labelledby="review-without-suggestion"]');

    await expect(decidable).toBeVisible();
    await expect(research).toBeVisible();

    // The heading carries its own count, and the count matches the cards under it.
    const decidableHeading = (await decidable.locator('h3').innerText()).replace(/\s+/g, ' ').trim();
    const researchHeading = (await research.locator('h3').innerText()).replace(/\s+/g, ' ').trim();
    const decidableCards = await decidable.locator('ul > li').count();
    const researchCards = await research.locator('ul > li').count();

    expect(decidableHeading).toBe(`Needs a decision (${decidableCards})`);
    expect(researchHeading).toBe(`No fuzzy match (${researchCards})`);

    // The membership rule: a suggestion in the first half, none in the second. The second half's
    // state word is the badge's now, not the removed "No match" pill (DESIGN.md §5 (b)), so it is
    // counted on the badge's accessible name.
    await expect(decidable.getByText('Suggested id:')).toHaveCount(decidableCards);
    await expect(research.getByText('Suggested id:')).toHaveCount(0);
    await expect(research.locator('[role="img"][aria-label^="Unknown"]')).toHaveCount(researchCards);
    await expect(research.getByText('No match', { exact: true })).toHaveCount(0);
});

test('shows review progress and keeps the pager reachable', async ({ page }) => {
    await page.setViewportSize({ width: 1280, height: 720 });
    seedPendingCandidates(2, { suggested: true });
    seedPendingCandidates(24);

    await page.goto('/review');
    await page.locator('#app > *').first().waitFor();

    // The bar carries the queue's numbers as a value a screen reader can read, not only as a width.
    const bar = page.getByRole('progressbar', { name: 'Review progress' });
    await expect(bar).toBeVisible();
    await expect(bar).toHaveAttribute('aria-valuemin', '0');

    const total = Number(await bar.getAttribute('aria-valuemax'));
    const now = Number(await bar.getAttribute('aria-valuenow'));
    expect(total, 'the bar is measured against the whole queue').toBeGreaterThan(0);
    expect(now, 'resolved cannot exceed the total').toBeLessThanOrEqual(total);

    // The sentence the bar replaced is gone; the same two numbers are still stated beside it.
    await expect(page.getByText(/\d+ of \d+ items to resolve\./)).toHaveCount(0);
    await expect(page.getByText(`${now} of ${total} resolved`)).toBeVisible();

    // The pager stays put while the queue scrolls under it. This is measured, not asserted as a
    // class: the shell's `main` carries `@container`, and containment makes `position: sticky` a
    // no-op for its descendants (containment-mechanism.spec.ts proves that), so a `sticky` class
    // here would be a claim the browser does not honour.
    const pager = page.getByTestId('review-pager');
    await expect(pager).toBeVisible();
    const beforeScroll = await pager.evaluate((element) => element.getBoundingClientRect().bottom);
    await page.mouse.wheel(0, 900);
    await page.waitForTimeout(250);
    const afterScroll = await pager.evaluate((element) => element.getBoundingClientRect().bottom);

    expect(afterScroll, 'the pager is still at the bottom of the viewport after scrolling').toBeLessThanOrEqual(720 + 1);
    expect(Math.abs(afterScroll - beforeScroll), 'the pager did not scroll away with the content').toBeLessThanOrEqual(1);
});

test('measures the queue against the shell gutter', async ({ page }) => {
    seedPendingCandidates(1);

    await page.goto('/review');
    await page.locator('#app > *').first().waitFor();

    // The proposal's numbers: the review page's content appeared to start at x=352 while the
    // dashboard's starts at x=256, and the question was whether this page adds a gutter of its own
    // on top of the shell's.
    //
    // It does not. Measured: the page's first CARD edge sits exactly on the shell's content edge,
    // and the 17px between that edge and the form inside it is the card's own `p-4` plus its 1px
    // border, not a page-level inset. The 352 measurement counted from inside a card. Declared here
    // as a measurement against the card, which is the element the page actually positions.
    const edges = await page.evaluate(() => {
        const main = document.querySelector('main') as HTMLElement;
        const card = document.querySelector('section[aria-labelledby] ul > li') as HTMLElement | null;
        const style = getComputedStyle(main);

        return {
            mainContentLeft: main.getBoundingClientRect().left + Number.parseFloat(style.paddingLeft),
            firstCardLeft: card === null ? null : card.getBoundingClientRect().left,
            mainPaddingLeft: Number.parseFloat(style.paddingLeft),
        };
    });

    expect(edges.firstCardLeft).not.toBeNull();
    // The card is the page's own first element, and it sits on the shell's content edge: no page
    // padding of its own, so the review page has no left-edge bug to fix.
    expect(Math.abs((edges.firstCardLeft as number) - edges.mainContentLeft)).toBeLessThanOrEqual(1);
    expect(edges.mainPaddingLeft, 'the shell gutter is the only inset').toBeLessThanOrEqual(96);
});

test('summarises a no-match row on one line and links to the page that decides', async ({ page }) => {
    seedPendingCandidates(1, { suggested: true });
    seedPendingCandidates(3);

    await page.goto('/review');
    await page.locator('#app > *').first().waitFor();

    const research = page.locator('section[aria-labelledby="review-without-suggestion"]');
    const decidable = page.locator('section[aria-labelledby="review-with-suggestion"]');

    // FQ-10 moved the verdict form to its own page. Both halves therefore summarise and link: the
    // decidable half keeps its comparison, the research half keeps its one line.
    const openCard = decidable.locator('ul > li').first();
    await expect(openCard.getByTestId('review-detail-link')).toBeVisible();

    const row = research.locator('ul > li').first();

    // The disclosure the old case drove is gone, not merely hidden: there is no toggle to expand.
    await expect(row.getByRole('button', { name: /the verdict form for / })).toHaveCount(0);
    await expect(row.getByTestId('review-detail-link')).toBeVisible();

    // One line still means one line. The old case proved it by opening and closing a disclosure; the
    // new shape proves the same user-visible fact by measuring the row it now renders.
    const height = await row.evaluate((element) => element.getBoundingClientRect().height);
    expect(height, 'the research row is a single summary line').toBeLessThanOrEqual(64);

    // The link names the row's own candidate, so twenty of them stay distinguishable by a screen reader.
    await expect(row.getByTestId('review-detail-link')).toHaveAttribute('aria-label', /^Review /);

    // And it goes where it says it goes: the page that decides.
    await row.getByTestId('review-detail-link').click();
    await expect(page.getByTestId('review-detail')).toHaveCount(1);
});

test('the summarised queue fits on one screen', async ({ page }) => {
    await page.setViewportSize({ width: 1280, height: 900 });
    // 25 rows exactly: the paginator's page size. The fixture now clears the whole table, so the seeded
    // count IS the row count — the previous 2 + 22 reached 25 only with another case's leftovers.
    seedPendingCandidates(2, { suggested: true });
    seedPendingCandidates(23);

    await page.goto('/review');
    await page.locator('#app > *').first().waitFor();

    const measured = await page.evaluate(() => {
        const section = document.querySelector('section[aria-labelledby="review-without-suggestion"]') as HTMLElement | null;
        const cards = [...(section?.querySelectorAll('ul > li') ?? [])] as HTMLElement[];

        return {
            height: document.documentElement.scrollHeight,
            cards: document.querySelectorAll('main ul > li').length,
            sectionHeight: section === null ? 0 : Math.round(section.getBoundingClientRect().height),
            decidableHeight: Math.round((document.querySelector('section[aria-labelledby="review-with-suggestion"]') as HTMLElement | null)?.getBoundingClientRect().height ?? 0),
            pageChrome: Math.round(document.querySelector('main')!.getBoundingClientRect().top) + 134,
            cardHeights: cards.slice(0, 3).map((c) => Math.round(c.getBoundingClientRect().height)),
            // FQ-10 removed every inline form, so "collapsed" is no longer a `display:none` form to
            // count: the evidence is that no row carries a form at all, and that the row it does render
            // is one summary line.
            forms: cards.filter((c) => c.querySelector('form') !== null).length,
            cardCount: cards.length,
        };
    });

    console.log(
        `REVIEW-COLLAPSE total=${measured.height}px research=${measured.sectionHeight}px `
        + `decidable=${measured.decidableHeight}px row=${measured.cardHeights[0]}px `
        + `forms=${measured.forms}/${measured.cardCount}`,
    );

    // Before the collapse the same 25 cards measured 4,949px, because every no-match row carried
    // three controls plus a Resolve button. The mechanism is now measured directly: every no-match
    // form is display:none.
    expect(measured.cards, 'the queue is paginated at 25').toBe(25);
    // The old case proved "every no-match form is collapsed"; FQ-10 removed the forms, so the same
    // claim is proved one step stronger — no row on this page carries a form at all.
    expect(measured.forms, 'no queue row carries a verdict form').toBe(0);

    // The acceptance is per-row, and that is the honest unit. A page-level number also encodes the
    // seed's mix (the decidable section is expanded by design) and the shell chrome, neither of
    // which the collapse governs. A collapsed row is one 44px control plus the card's own padding.
    expect(measured.cardHeights[0], 'a summarised row is one line, not a form').toBeLessThanOrEqual(56);

    // The page still moves a long way down from where it started: 4,949 -> under 2,700. It does not
    // reach the proposal's ~1,000-1,400 estimate, and cannot without breaking the tree's 44px tap
    // floor: 22 rows at the 44px minimum is 968px of rows before any card padding or gap.
    expect(measured.height, `collapsed queue height was ${measured.height}px`).toBeLessThan(2700);
});

test('passes an axe scan at WCAG A and AA', async ({ page }) => {
    seedPendingCandidates(3, { suggested: true });
    seedPendingCandidates(2);

    await page.goto('/review');
    await page.locator('#app > *').first().waitFor();

    await openAllDisclosures(page);
    const results = await buildAxe(page).analyze();

    expect(results.violations, JSON.stringify(results.violations, null, 2)).toEqual([]);
});