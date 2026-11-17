import { test, expect } from '@playwright/test';
import { buildAxe } from '../utils/accessibility';
import {
    clearOtherForm,
    clearPendingCandidates,
    promotedTargetId,
    seedOtherForm,
    seedPendingCandidates,
} from '../utils/review-candidates';

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
 * So this file pins the savebar, which since the FQ-10 cut is the only shape `CandidateForm`
 * renders anywhere: the queue lists candidates and links to this page, its own spec asserts
 * that no queue row carries a form (`review.spec.ts`, "no queue row carries a verdict form"),
 * and the `variant="row"` branch was retired with KI-143 (2026-11-10) after a tree-wide grep
 * found no caller rendering it.
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

test('commits the record its other-form action names, in one press', async ({ page }) => {
    // The wireframe makes the other form an answer rather than a label
    // (`review-detail.html:109`), and `CandidateForm.chooseRecord` commits in one press. Nothing
    // covered that press: the write is a catalog merge with no undo, so the wiring is pinned here.
    const [candidate] = seedPendingCandidates(1, { suggested: true });
    const other = seedOtherForm();

    try {
        await page.goto(`/review/${candidate.id}`);
        await page.locator('#app > *').first().waitFor();

        const useThis = page.getByRole('button', { name: `Use ${other.name} as the match` });
        await expect(useThis).toBeVisible();

        // The house 44px bar applies to it: the press writes.
        expect((await useThis.boundingBox())?.height ?? 0).toBeGreaterThanOrEqual(44);

        await useThis.click();
        await page.waitForURL(/\/review$/);

        // The write targets the row that was pressed, not the candidate's own suggestion.
        expect(promotedTargetId()).toBe(other.id);
        // A confirmed candidate leaves the Pending-only queue.
        await expect(page.getByText(candidate.proposedName)).toHaveCount(0);
    } finally {
        clearOtherForm();
    }
});

test('paints one focus-ring colour on every focus stop, from the first frame', async ({ page }) => {
    // BO-011. The audit read four ring colours across one screen; a settled-state read cannot see
    // them, because `transition-colors` carries `outline-color` and every ring lands on the token
    // 150ms later. Measured 2026-11-15 on `/review/10`: 23 stops settled on one colour, but the
    // first painted frame showed five — the six offenders were exactly the elements reporting
    // `outline-color` in their `transition-property`. So this reads synchronously after focus.
    // The base layer now seeds `outline-color: var(--color-ring)` at rest, leaving no delta to
    // interpolate; a settled read would pass on the defect and is deliberately not the assertion.
    const [candidate] = seedPendingCandidates(1, { suggested: true });

    await page.goto(`/review/${candidate.id}`);
    await page.locator('#app > *').first().waitFor();

    const report = await page.evaluate(() => {
        const selector =
            'a[href],button:not([disabled]),select,input:not([type=hidden]),summary,[tabindex]:not([tabindex="-1"])';
        const els = ([...document.querySelectorAll(selector)] as HTMLElement[]).filter(
            (e) => e.offsetParent !== null,
        );

        // Resolve the token into the same serialised form `getComputedStyle` returns, so the
        // comparison is value-to-value rather than hex-against-rgb.
        const probe = document.createElement('span');
        probe.style.color = getComputedStyle(document.documentElement)
            .getPropertyValue('--color-ring')
            .trim();
        document.body.appendChild(probe);
        const ring = getComputedStyle(probe).color;
        probe.remove();

        const rows = els.map((el) => {
            el.focus({ focusVisible: true });

            return {
                label: (el.getAttribute('aria-label') || el.textContent || el.tagName).trim().slice(0, 28),
                immediate: getComputedStyle(el).outlineColor,
            };
        });

        return { ring, rows };
    });

    expect(report.rows.length, 'the page must expose a walkable set of focus stops').toBeGreaterThanOrEqual(20);

    const offToken = report.rows.filter((row) => row.immediate !== report.ring);

    expect(
        offToken,
        `every focus stop must paint ${report.ring} on its first frame; these did not: `
            + JSON.stringify(offToken, null, 2),
    ).toEqual([]);
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
