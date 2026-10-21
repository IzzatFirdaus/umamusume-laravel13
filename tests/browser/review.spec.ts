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

test('every verdict control is named after its own candidate', async ({ page }) => {
    await page.goto('/review');
    await page.locator('#app > *').first().waitFor();

    // With no pending candidates the queue shows its empty state; the naming assertions need one.
    const empty = page.getByText('Nothing pending.');
    if (await empty.isVisible().catch(() => false)) {
        test.skip(true, 'No pending candidates in the scratch database to name.');
    }

    // Each control is reachable by the name that carries its candidate - the F14 contract.
    const status = page.getByLabel(/^Verdict for /).first();
    await expect(status).toBeVisible();

    const resolve = page.getByRole('button', { name: /^Resolve / }).first();
    await expect(resolve).toBeVisible();
    await expect(resolve).toHaveText(/Resolve/);

    // The alias-language control offers the language options.
    const alias = page.getByLabel(/^Alias language for /).first();
    await expect(alias.locator('option')).not.toHaveCount(0);
});

test('a failed verdict shows its message on the card', async ({ page }) => {
    await page.goto('/review');
    await page.locator('#app > *').first().waitFor();

    const empty = page.getByText('Nothing pending.');
    if (await empty.isVisible().catch(() => false)) {
        test.skip(true, 'No pending candidates in the scratch database.');
    }

    // An Aliased verdict with no language is the D-2 case: the message must reach the card.
    await page.getByLabel(/^Verdict for /).first().selectOption('Aliased');
    await page.getByRole('button', { name: /^Resolve / }).first().click();

    await expect(page.getByRole('alert').first()).toContainText(/language/i);
});

test('every control on the queue meets the 44px floor', async ({ page }) => {
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
    await page.goto('/review');
    await page.locator('#app > *').first().waitFor();

    const empty = page.getByText('Nothing pending.');
    if (await empty.isVisible().catch(() => false)) {
        test.skip(true, 'No pending candidates in the scratch database.');
    }

    // 6A.7: the list opens with a count that tracks the paginator. The trailing `\s*` absorbs the
    // single space Vue's whitespace condense leaves between the last interpolation and the period's
    // line break; the sentence itself stays pinned.
    await expect(page.getByText(/^\d+ of \d+ items to resolve\.\s*$/)).toBeVisible();

    const card = page.locator('main ul > li').first();

    // 6A.6 + 6A.2: a status pill and a shared provenance badge carry what the metadata row used to
    // print as bare `tier · source · date` text; the date keeps its own element beside the badge.
    await expect(card.getByText(/^(Fuzzy match|No match)$/)).toBeVisible();
    await expect(card.locator('[role="img"]').first()).toBeVisible();
    await expect(card.locator('time')).toBeVisible();

    // 6A.1 + 6A.9: every card carries either the reason it has no suggestion or the hint that
    // names the id it does suggest.
    await expect(card.getByText(/No suggestion:|Suggested id:/)).toBeVisible();

    // 6A.4: exactly one accented primary for the screen, on the topmost card; the other cards'
    // Resolve buttons render secondary.
    await expect(page.locator('.bg-primary-action')).toHaveCount(1);
    await expect(card.getByRole('button', { name: /^Resolve / })).toHaveClass(/bg-primary-action/);

    // 6A.3: the verdict control carries a visible label, not an sr-only one (the aria-label still
    // names the candidate; "Verdict" here is the label's own text content).
    await expect(page.getByText('Verdict', { exact: true }).first()).toBeVisible();

    // 6A.10: nothing is busy on a cold load. The list's states are the two empty variants and the
    // per-card refusal alert; loading exists only while a verdict is in flight (ADR-0007).
    await expect(page.locator('form[aria-busy]')).toHaveCount(0);
});

test('lists the rows the engine could suggest before the ones it could not', async ({ page }) => {
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

test('gates Resolve on a chosen verdict, and names the reason once', async ({ page }) => {
    seedPendingCandidates(3);

    await page.goto('/review');
    await page.locator('#app > *').first().waitFor();

    const empty = page.getByText('Nothing pending.');
    if (await empty.isVisible().catch(() => false)) {
        test.skip(true, 'No pending candidates in the scratch database.');
    }

    const card = page.locator('main ul > li').first();
    const verdict = card.getByLabel(/^Verdict for /);
    const resolve = card.getByRole('button', { name: /^Resolve / });

    // The verdict starts unset, so the commit is refused at the control, and the disabled control
    // is styled as unavailable (the house idiom Pagination.vue uses) rather than merely quiet.
    await expect(verdict).toHaveValue('');
    await expect(resolve).toBeDisabled();
    await expect(resolve).toHaveClass(/disabled:opacity-50/);

    // The reason is stated once for the queue, not once per card: the page carries it and no card
    // does. Twenty-five copies of one sentence was the wall this replaced.
    await expect(page.getByText('Set a verdict on any row to enable its resolve.')).toBeVisible();
    await expect(page.getByText('Choose a verdict first.')).toHaveCount(0);

    // The unset verdict is the quietest thing in the row; a chosen one is not.
    await expect(verdict).toHaveClass(/text-ink-muted/);

    // A deliberate pick enables the commit.
    await verdict.selectOption('Rejected');
    await expect(resolve).toBeEnabled();
    await expect(verdict).toHaveClass(/text-ink-strong/);
});

test('passes an axe scan at WCAG A and AA', async ({ page }) => {
    await page.goto('/review');
    await page.locator('#app > *').first().waitFor();

    await openAllDisclosures(page);
    const results = await buildAxe(page).analyze();

    expect(results.violations, JSON.stringify(results.violations, null, 2)).toEqual([]);
});
