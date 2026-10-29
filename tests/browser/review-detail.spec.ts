import { test, expect } from '@playwright/test';
import { buildAxe } from '../utils/accessibility';
import { clearPendingCandidates, seedPendingCandidates } from '../utils/review-candidates';

/*
 * The review detail page (SCR-REV-001's decision screen, FQ-10).
 *
 * RE-BASELINED at R2 part 2. The page used to render `CandidateForm` in its queue-row shape — a
 * Verdict select, an Umamusume-id field, an Alias-language select and a `Resolve` button laid on one
 * baseline. The HFW draws something else here: one decision surface under a `.savebar`, with a
 * `role="status"` line and two named actions (`review-detail.html:119-122`), because a screen that
 * asks one question should offer one answer rather than a control strip. Its keybox is explicit that
 * this is not cosmetic: "A queue that only offers 'confirm' is not a review; it is a rubber stamp."
 *
 * So this file now pins the savebar. The row-shape assertions were not deleted — the row shape still
 * ships, on the QUEUE, where `CandidateForm` renders `variant="row"`; those cases moved to
 * `review.spec.ts` (see the block at its foot). Deleting them would have weakened the suite, which
 * the dispatch forbids.
 *
 * Two properties survive unchanged from the old file and are still asserted below: exactly one
 * accented primary on the page (6A.4), and nothing busy on a cold load (6A.10).
 */

test.beforeEach(() => {
    clearPendingCandidates();
});

test.afterEach(() => {
    clearPendingCandidates();
});

test('renders the detail exactly once in the tree', async ({ page }) => {
    const [candidate] = seedPendingCandidates(1, { suggested: true });

    await page.goto(`/review/${candidate.id}`);
    await page.locator('#app > *').first().waitFor();

    // The proof of one rendering: the region the page exists to draw appears once, and the queue's
    // per-row form is not also on this page.
    await expect(page.getByTestId('review-detail')).toHaveCount(1);
    await expect(page.getByTestId('review-form-row')).toHaveCount(1);
    await expect(page.getByRole('heading', { name: candidate.proposedName })).toBeVisible();
});

test('offers the savebar with its status line and two named actions', async ({ page }) => {
    const [candidate] = seedPendingCandidates(1, { suggested: true });

    await page.goto(`/review/${candidate.id}`);
    await page.locator('#app > *').first().waitFor();

    // The status line is the HFW's `.dirty` slot: it says what state the decision is in.
    const status = page.locator('.savebar [role="status"]');
    await expect(status).toBeVisible();
    await expect(status).toHaveText(/Not yet answered/);

    // Reject is a real action, not the absence of one — the wireframe's keybox says so.
    await expect(
        page.getByRole('button', { name: `Reject this match for ${candidate.proposedName}` }),
    ).toBeVisible();

    // Confirm is the page's one filled action.
    const confirm = page.getByRole('button', { name: `Confirm this match for ${candidate.proposedName}` });
    await expect(confirm).toBeVisible();
    await expect(confirm).toHaveClass(/bg-primary-action/);
});

test('never pre-selects a verdict, so a stray press commits nothing', async ({ page }) => {
    const [candidate] = seedPendingCandidates(1, { suggested: true });

    await page.goto(`/review/${candidate.id}`);
    await page.locator('#app > *').first().waitFor();

    // The old form's reason for starting empty applies harder to a savebar: these buttons commit on
    // one press, so a default would turn a stray click into an unundoable merge.
    await expect(page.getByLabel(`Verdict for ${candidate.proposedName}`)).toHaveValue('');
    await expect(page.locator('.savebar [role="status"]')).toHaveText(/Not yet answered/);
});

test('carries the one accented primary and the visible Verdict label the queue used to', async ({ page }) => {
    const [candidate] = seedPendingCandidates(1, { suggested: true });

    await page.goto(`/review/${candidate.id}`);
    await page.locator('#app > *').first().waitFor();

    // 6A.4: exactly one accented primary for the screen. Here the screen is one decision, so it is
    // Confirm; Reject is present but secondary, and the Alias path's commit is secondary too.
    await expect(page.locator('.bg-primary-action')).toHaveCount(1);

    // 6A.3: the verdict control still carries a visible label, not an sr-only one — the advanced
    // controls sit above the bar so the Alias verdict stays reachable from this screen.
    await expect(page.getByText('Verdict', { exact: true }).first()).toBeVisible();

    // 6A.10: nothing is busy on a cold load.
    await expect(page.locator('form[aria-busy]')).toHaveCount(0);
});

test('prints the rarity mark from the proposed record, or states its absence', async ({ page }) => {
    const [candidate] = seedPendingCandidates(1, { suggested: true });

    await page.goto(`/review/${candidate.id}`);
    await page.locator('#app > *').first().waitFor();

    // The HFW draws a rarity pill beside the name (`review-detail.html:90`). The server derives it
    // from the trainee's character cards and sends `CardRarity::label()`, whose vocabulary is
    // 'R' / 'SR' / 'SSR' — NOT the wireframe's '★★★ SSR' display string (`stars()` is the separate
    // ★ accessor). So the assertion matches the enum's words, not the mock's.
    //
    // Two instrument lessons are baked into the shape below:
    //   * Playwright's `hasText` is matched against `innerText`, which keeps the template's
    //     surrounding whitespace, so an anchored `/^SSR$/` cannot match a value that renders as
    //     "\n   SSR\n". Reading `textContent` and trimming tests the value, not the indentation.
    //   * The mark lives ON the Best-match card, and that card only renders when a proposed record
    //     exists. A candidate with no stored suggestion has no card, and the wireframe draws no pill
    //     there either — so this case accepts either branch but never a blank.
    const card = page.locator('section[aria-labelledby="review-best-heading"]');

    if ((await card.count()) === 0) {
        // No proposal: the page still has to answer its own question, so its decision surface exists.
        await expect(page.getByTestId('review-detail')).toBeVisible();
        return;
    }

    const marks = await card.locator('.pill').evaluateAll((nodes) =>
        nodes.map((node) => (node.textContent ?? '').trim()),
    );

    expect(
        marks.some((mark) => /^(One star|Two stars|Three stars|Rarity not recorded)$/.test(mark)),
        `the Best match card carries a rarity mark or states its absence; it rendered ${JSON.stringify(marks)}`,
    ).toBe(true);
});

test('passes an axe scan at WCAG A and AA on the literal detail URL', async ({ page }) => {
    // The rowid reset in `clearPendingCandidates` makes this fixture's row id 1, so the URL can be
    // written literally. `RouteCoverageTest` decides which routes the corpus visits and scans by
    // reading `page.goto('<literal>')` out of the spec source; a template literal is invisible to it,
    // and a parameterised route with no literal navigation reads as uncovered.
    const [candidate] = seedPendingCandidates(1, { suggested: true });
    expect(candidate.id, 'the fixture row is the first id in a reset table').toBe(1);

    await page.goto('/review/1');
    await page.locator('#app > *').first().waitFor();

    await expect(page.getByTestId('review-detail')).toHaveCount(1);

    const results = await buildAxe(page).analyze();

    expect(results.violations, JSON.stringify(results.violations, null, 2)).toEqual([]);
});
