import { test, expect } from '@playwright/test';
import { clearPendingCandidates, seedPendingCandidates } from '../utils/review-candidates';

/*
 * The review detail page (SCR-REV-001’s decision screen, FQ-10).
 *
 * The verdict form used to render inside each queue row; it renders here now. These six cases are the
 * ones that broke when the form moved, moved with it: the same facts, the same assertions, a different
 * page. `:23`, `:46`, `:76`’s accent half, `:140` and `:175` of `review.spec.ts` are the source.
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

    // The proof of one rendering: the region the page exists to draw appears once, and the queue’s
    // per-row form is not also on this page.
    await expect(page.getByTestId('review-detail')).toHaveCount(1);
    await expect(page.getByTestId('review-form-row')).toHaveCount(1);
    await expect(page.getByRole('heading', { name: candidate.proposedName })).toBeVisible();
});

test('every verdict control is named after its own candidate', async ({ page }) => {
    const [candidate] = seedPendingCandidates(1, { suggested: true });

    await page.goto(`/review/${candidate.id}`);
    await page.locator('#app > *').first().waitFor();

    // Each control is reachable by the name that carries its candidate — the F14 contract, unchanged
    // by the move: it is a DOM relation between a label and its control.
    const status = page.getByLabel(`Verdict for ${candidate.proposedName}`);
    await expect(status).toBeVisible();

    const resolve = page.getByRole('button', { name: /^Resolve / });
    await expect(resolve).toBeVisible();
    await expect(resolve).toHaveText(/Resolve/);

    const alias = page.getByLabel(`Alias language for ${candidate.proposedName}`);
    await expect(alias.locator('option')).not.toHaveCount(0);
});

test('a failed verdict shows its message on the detail, not on a card', async ({ page }) => {
    const [candidate] = seedPendingCandidates(1, { suggested: true });

    await page.goto(`/review/${candidate.id}`);
    await page.locator('#app > *').first().waitFor();

    // An Aliased verdict with no language is the D-2 case: the message must reach the reader.
    await page.getByLabel(`Verdict for ${candidate.proposedName}`).selectOption('Aliased');
    await page.getByRole('button', { name: /^Resolve / }).click();

    await expect(page.getByRole('alert').first()).toContainText(/language/i);
});

test('gates Resolve on a chosen verdict, and names the reason once', async ({ page }) => {
    const [candidate] = seedPendingCandidates(1, { suggested: true });

    await page.goto(`/review/${candidate.id}`);
    await page.locator('#app > *').first().waitFor();

    const verdict = page.getByLabel(`Verdict for ${candidate.proposedName}`);
    const resolve = page.getByRole('button', { name: /^Resolve / });

    await expect(verdict).toHaveValue('');
    await expect(resolve).toBeDisabled();
    await expect(resolve).toHaveClass(/disabled:opacity-50/);

    // The unset verdict is the quietest control on the page; a chosen one is not.
    await expect(verdict).toHaveClass(/text-ink-muted/);

    await verdict.selectOption('Rejected');
    await expect(resolve).toBeEnabled();
    await expect(verdict).toHaveClass(/text-ink-strong/);
});

test('lines the three controls up on one baseline', async ({ page }) => {
    const [candidate] = seedPendingCandidates(1, { suggested: true });

    await page.goto(`/review/${candidate.id}`);
    await page.locator('#app > *').first().waitFor();

    const row = page.getByTestId('review-form-row').first();

    // The id field’s “(optional)” hint used to render below its own input, and the row aligned the
    // columns’ bottoms, so that one control sat 20px above the other two. The hint is a caption row
    // now and the controls share a top edge; this is the measurement, not the class name.
    const tops = await row.locator('select, input').evaluateAll((elements) =>
        elements.map((element) => element.getBoundingClientRect().top),
    );

    expect(tops.length, 'the row renders the three controls').toBeGreaterThanOrEqual(3);
    expect(Math.max(...tops) - Math.min(...tops), 'form controls share a baseline').toBeLessThanOrEqual(1);

    await expect(row.getByText('(optional)', { exact: true })).toBeAttached();
});

test('carries the one accented primary and the visible Verdict label the queue used to', async ({ page }) => {
    const [candidate] = seedPendingCandidates(1, { suggested: true });

    await page.goto(`/review/${candidate.id}`);
    await page.locator('#app > *').first().waitFor();

    // 6A.4: exactly one accented primary for the screen. On the queue that was the topmost card’s
    // Resolve; here the screen is one decision, so it is this page’s Resolve.
    await expect(page.locator('.bg-primary-action')).toHaveCount(1);
    await expect(page.getByRole('button', { name: /^Resolve / })).toHaveClass(/bg-primary-action/);

    // 6A.3: the verdict control carries a visible label, not an sr-only one.
    await expect(page.getByText('Verdict', { exact: true }).first()).toBeVisible();

    // 6A.10: nothing is busy on a cold load.
    await expect(page.locator('form[aria-busy]')).toHaveCount(0);
});
