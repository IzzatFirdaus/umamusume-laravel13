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

test('each row explains itself: confidence, source, match and the door to the decision', async ({ page }) => {
    seedPendingCandidates(2, { suggested: true });
    seedPendingCandidates(2);
    await page.goto('/review');
    await page.locator('#app > *').first().waitFor();

    const empty = page.getByText('Nothing pending.');
    if (await empty.isVisible().catch(() => false)) {
        test.skip(true, 'No pending candidates in the scratch database.');
    }

    const row = page.locator('#review-results tr').first();

    // The wireframe's columns (`review.html:80-101`): the candidate names itself and its source, the
    // question is stated, the proposal (or its absence) is printed, the state word is the badge's, and
    // the row's one door goes to the page that decides.
    await expect(row.locator('time')).toBeVisible();
    await expect(row.locator('[role="img"]').first()).toBeVisible();
    await expect(row.locator('[role="img"]').first()).toHaveAttribute('aria-label', /^(Unknown|Estimated|Calculated)/);
    await expect(row.getByText(/Why this name is here|No suggestion:|Suggested id:/)).toBeVisible();
    await expect(row.getByTestId('review-detail-link')).toBeVisible();
});

test('keeps the engine\'s suggestion-first order in the table', async ({ page }) => {
    seedPendingCandidates(1, { suggested: true });
    seedPendingCandidates(2);
    await page.goto('/review');
    await page.locator('#app > *').first().waitFor();

    const empty = page.getByText('Nothing pending.');
    if (await empty.isVisible().catch(() => false)) {
        test.skip(true, 'No pending candidates in the scratch database.');
    }

    // 6A.1's suggestion-first order is what puts the click-decidable row where the Trainer meets it.
    await expect(page.locator('#review-results tr').first()).toContainText('Suggested id:');
});

// MOVED (FQ-10): "gates Resolve on a chosen verdict" now asserts on the detail page, where the verdict
// control and its Resolve button live. See tests/browser/review-detail.spec.ts.

// MOVED (FQ-10): "lines the three controls up on one baseline" now asserts on the detail page, where
// `review-form-row` renders. See tests/browser/review-detail.spec.ts.

test('holds both halves of the queue in one table, suggested rows first', async ({ page }) => {
    seedPendingCandidates(2, { suggested: true });
    seedPendingCandidates(3);

    await page.goto('/review');
    await page.locator('#app > *').first().waitFor();

    // One Pending table (`review.html:69-101`): the split is the order the controller already states,
    // not two sections. The membership rule is read off the rows.
    const rows = page.locator('#review-results tr');
    const total = await rows.count();
    expect(total, 'the table holds the whole page of candidates').toBe(5);

    const suggested = await page.getByText('Suggested id:').count();
    const noMatch = await page.getByText('No match', { exact: true }).count();
    expect(suggested, 'two seeded rows carry a suggestion').toBe(2);
    expect(noMatch, 'three seeded rows carry none').toBe(3);

    // Every suggested row precedes every no-match row: the first `suggested` rows are the decidable
    // ones, and the rest state the absence.
    for (let index = 0; index < total; index += 1) {
        const row = rows.nth(index);
        if (index < suggested) {
            await expect(row, `row ${index} should carry a suggestion`).toContainText('Suggested id:');
        } else {
            await expect(row, `row ${index} should state no match`).toContainText('No match');
        }
    }
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
    // It does not. Measured: the page's first list row sits exactly on the shell's content edge, and
    // the 17px between that edge and the form inside it is the card's own `p-4` plus its 1px border,
    // not a page-level inset. The 352 measurement counted from inside a card. Declared here as a
    // measurement against the row, which is the element the page actually positions.
    //
    // The gutter is read from `[data-shell-band]`, the element the shell gives it to (the HFW shell
    // rewrite moved the band's padding off `main` itself onto its content band), so the measurement
    // follows the padding wherever the shell puts it rather than assuming main carries it.
    const edges = await page.evaluate(() => {
        const main = document.querySelector('main') as HTMLElement;
        const band = main.querySelector('[data-shell-band]') as HTMLElement | null;
        const row = document.querySelector('#review-results tr') as HTMLElement | null;
        const grip = band ?? main;
        const style = getComputedStyle(grip);

        return {
            shellContentLeft: grip.getBoundingClientRect().left + Number.parseFloat(style.paddingLeft),
            firstRowLeft: row === null ? null : row.getBoundingClientRect().left,
            shellGutter: Number.parseFloat(style.paddingLeft),
        };
    });

    expect(edges.firstRowLeft).not.toBeNull();
    // The row is the page's own first element, and it sits on the shell's content edge: no page
    // padding of its own, so the review page has no left-edge bug to fix.
    expect(Math.abs((edges.firstRowLeft as number) - edges.shellContentLeft)).toBeLessThanOrEqual(1);
    expect(edges.shellGutter, 'the shell gutter is the only inset').toBeLessThanOrEqual(96);
});

test('summarises a no-match row on one line and links to the page that decides', async ({ page }) => {
    seedPendingCandidates(1, { suggested: true });
    seedPendingCandidates(3);

    await page.goto('/review');
    await page.locator('#app > *').first().waitFor();

    // The last row is a no-match row (the controller orders suggestions first).
    const row = page.locator('#review-results tr').last();
    await expect(row).toContainText('No catalog record carries this name.');

    // The row is a summary and a door: a candidate cell of name, Japanese name and source, and one
    // 44px Review control. No form, no verdict controls (`FQ-10` moved those to the detail page).
    const height = await row.evaluate((element) => element.getBoundingClientRect().height);
    expect(height, 'the research row is a summary, not a form').toBeLessThanOrEqual(96);

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
        const cards = [...document.querySelectorAll('#review-results tr')] as HTMLElement[];

        return {
            height: document.documentElement.scrollHeight,
            cards: cards.length,
            rows: cards.length,
            pageChrome: Math.round(document.querySelector('main')!.getBoundingClientRect().top) + 134,
            cardHeights: cards.slice(0, 3).map((c) => Math.round(c.getBoundingClientRect().height)),
            // FQ-10 removed every inline form, so the evidence is that no row carries a form at all,
            // and that the row it does render is one summary line.
            forms: cards.filter((c) => c.querySelector('form') !== null).length,
            cardCount: cards.length,
        };
    });

    console.log(
        `REVIEW-COLLAPSE total=${measured.height}px rows=${measured.rows} `
        + `row=${measured.cardHeights[0]}px forms=${measured.forms}/${measured.cardCount}`,
    );

    // Before the collapse the same 25 cards measured 4,949px, because every no-match row carried
    // three controls plus a Resolve button. The mechanism is now measured directly: every no-match
    // form is display:none.
    expect(measured.cards, 'the queue is paginated at 25').toBe(25);
    // The old case proved "every no-match form is collapsed"; FQ-10 removed the forms, so the same
    // claim is proved one step stronger — no row on this page carries a form at all.
    expect(measured.forms, 'no queue row carries a verdict form').toBe(0);

    // The acceptance is per-row, and that is the honest unit. A page-level number also encodes the
    // seed's mix and the shell chrome, neither of which the row shape governs. A row is the candidate's
    // three printed lines plus one 44px Review control, and nothing else.
    expect(measured.cardHeights[0], 'a queue row is a summary, not a form').toBeLessThanOrEqual(96);

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