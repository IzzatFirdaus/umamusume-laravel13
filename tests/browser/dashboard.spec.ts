import { test, expect } from '@playwright/test';
import { startCareer } from '../utils/start-career';
import { recordTurns } from '../utils/record-turns';

/*
 * The Dashboard, built as a structural clone of the HFW `index.html` (owner ruling 2026-10-26: the
 * wireframe is the visual authority).
 *
 * What this pins:
 *
 *   - the wireframe's two rows, in its order: "Pick up where you left off" + "Recent veterans", then
 *     "Needs you" + "Quick actions", each 2.1fr / 1fr on desktop;
 *   - the wireframe's sections that the earlier build had left out: the Needs-you column with its
 *     data-status line and the catalogue, veteran and review doors;
 *   - the absence rule: Energy and Team rank keep their tiles and print a word, never a blank;
 *   - exactly one filled primary on the screen (annotation 1);
 *   - the absence of the wireframe's own meta blocks: the Table of contents card and the annotation
 *     key are document artifacts.
 */

const BLUE = 'rgb(34, 85, 255)'; // --color-brand-chrome #2255FF (light)

async function ready(page: import('@playwright/test').Page): Promise<void> {
    await page.goto('/', { waitUntil: 'domcontentloaded' });
    await page.locator('#app > *').first().waitFor();
}

async function createActiveRun(page: import('@playwright/test').Page): Promise<void> {
    // `startCareer` returns the record URL with its `/cockpit` suffix already removed.
    const url = await startCareer(page, 'unity_cup');
    const runId = url.match(/\/training-runs\/(\d+)/)?.[1] ?? '';

    expect(runId, 'startCareer did not land on a cockpit URL').not.toBe('');
    await recordTurns(page, runId, 3);
}

async function box(page: import('@playwright/test').Page, selector: string) {
    const el = page.locator(selector);
    await expect(el).toBeVisible();
    const rect = await el.boundingBox();
    expect(rect, `${selector} has no painted box`).not.toBeNull();
    return rect!;
}

test('the page head names the screen and offers one filled action', async ({ page }) => {
    await ready(page);

    const banner = page.getByRole('banner');
    await expect(banner.getByText('Overview', { exact: true })).toBeVisible();
    await expect(banner.getByRole('heading', { level: 1 })).toHaveText('Trainer Desk');
    await expect(banner.getByRole('link', { name: 'New career' })).toBeVisible();
    await expect(banner.getByRole('link', { name: 'All careers' })).toBeVisible();

    // Annotations 1 and 6: the head carries no filled action. "New career" is an outline button and
    // "All careers" is a quiet link, so the accent is not spent twice on one screen.
    await expect(banner.locator('[data-variant="primary"]')).toHaveCount(0);
    await expect(banner.locator('[data-variant="secondary"]')).toHaveCount(1);
});

test('renders the wireframe regions in its order and none of its meta blocks', async ({ page }) => {
    await ready(page);

    for (const name of ['Pick up where you left off', 'Recent veterans', 'Needs you', 'Quick actions']) {
        await expect(page.getByRole('region', { name })).toBeVisible();
    }

    const order = await page.locator('[data-dashboard-root] [data-card]').evaluateAll((cards) =>
        cards.map((card) => card.getAttribute('data-card') ?? ''),
    );
    expect(order).toEqual(['active_career', 'recent_veterans', 'needs_you', 'quick_actions']);

    // The wireframe's navigation aid and its annotation key belong to the document, not the product.
    await expect(page.getByRole('region', { name: 'Table of contents' })).toHaveCount(0);
    await expect(page.getByRole('heading', { name: /Table of contents/i })).toHaveCount(0);
    await expect(page.getByRole('heading', { name: /Annotation key/i })).toHaveCount(0);
});

test('the two rows lay out as a wide column and a narrow one', async ({ page }) => {
    await page.setViewportSize({ width: 1280, height: 800 });
    await ready(page);

    const pickup = await box(page, '[data-dashboard-row="pickup"] [data-card="active_career"]');
    const veterans = await box(page, '[data-dashboard-row="pickup"] [data-card="recent_veterans"]');
    const needs = await box(page, '[data-dashboard-row="needs-you"] [data-card="needs_you"]');
    const quick = await box(page, '[data-dashboard-row="needs-you"] [data-card="quick_actions"]');

    /*
     * Row one: the wide card, then the narrow one on the same line. The comparison is between row
     * containers, not between cards: `.card + .card` stacks a sibling with 14px of margin in a
     * column, and a grid row that happened to be satisfied by one card must not be read as the card
     * being misplaced. Both cards are in the same row when both share a row container.
     */
    expect(veterans.x).toBeGreaterThan(pickup.x + pickup.width - 1);
    expect(pickup.width).toBeGreaterThan(veterans.width);
    const rowOne = await box(page, '[data-dashboard-row="pickup"]');
    expect(Math.abs(pickup.y - rowOne.y)).toBeLessThan(2);
    expect(Math.abs(veterans.y - rowOne.y)).toBeLessThan(2);

    // Row two: same shape, below row one.
    expect(needs.y).toBeGreaterThanOrEqual(pickup.y + pickup.height - 1);
    expect(quick.x).toBeGreaterThan(needs.x + needs.width - 1);
    expect(needs.y).toBeGreaterThanOrEqual(Math.max(pickup.y + pickup.height, veterans.y + veterans.height) - 1);

    // The wireframe's 2.1fr / 1fr split, measured rather than read from a class list.
    const ratio = pickup.width / veterans.width;
    expect(ratio).toBeGreaterThan(2.0);
    expect(ratio).toBeLessThan(2.3);
});

test('the Needs you column carries the doors the wireframe draws', async ({ page }) => {
    await ready(page);

    const needs = page.getByRole('region', { name: 'Needs you' });
    await expect(needs.getByText('GLOBAL DATA')).toBeVisible();
    await expect(needs.getByText(/Verified \d{4}-\d{2}-\d{2}/)).toBeVisible();
    await expect(needs.getByRole('link', { name: 'Source ledger' })).toBeVisible();

    // The catalogue figures the retired KPI strip carried still reach the screen.
    await expect(needs.getByText(/trainees/)).toBeVisible();
    await expect(needs.getByText(/support cards/)).toBeVisible();
    await expect(needs.getByRole('link', { name: 'Browse catalog' })).toBeVisible();

    await expect(needs.getByText(/veterans saved/)).toBeVisible();
    await expect(needs.getByRole('link', { name: 'Open library' })).toBeVisible();

    await expect(needs.getByText(/pending\s+candidates?/)).toBeVisible();
    await expect(needs.getByRole('link', { name: 'Open queue' })).toBeVisible();
});

test('the quick actions are four outline destinations with the rail marks', async ({ page }) => {
    await ready(page);

    const quick = page.getByRole('region', { name: 'Quick actions' });
    const nav = quick.getByRole('navigation', { name: 'Quick actions' });
    await expect(nav.getByRole('link')).toHaveCount(4);
    for (const label of ['Import a historical career', 'Review queue', 'Open settings', 'Database hub']) {
        await expect(nav.getByRole('link', { name: label })).toBeVisible();
    }
    // A quick action is never the filled primary; the page head owns that.
    await expect(quick.locator('[data-variant="primary"]')).toHaveCount(0);
});

test('with no active career the resume card names the absence and keeps its actions', async ({ page }) => {
    await ready(page);

    const card = page.getByRole('region', { name: 'Pick up where you left off' });
    const hasCareer = await card.getByText(/turns to go/).count();

    if (hasCareer === 0) {
        await expect(card.getByText('Not recorded: no career is running.')).toBeVisible();
        await expect(card.getByRole('link', { name: 'Start a career' })).toBeVisible();
        await expect(card.getByRole('link', { name: 'Import an existing career' })).toBeVisible();
        // With nothing to resume, the head's outline is not enough: this card owns the one accent.
        await expect(card.locator('[data-variant="primary"]')).toHaveCount(1);
        await expect(page.locator('[data-variant="primary"]')).toHaveCount(1);
    }
});

test('an active career shows the wireframe tiles, the 72-mark rail and Open cockpit', async ({ page }) => {
    await createActiveRun(page);
    await ready(page);

    const card = page.getByRole('region', { name: 'Pick up where you left off' });
    await expect(card.getByText('Turn', { exact: true })).toBeVisible();
    await expect(card.getByText(/Turn \d+ of 72/)).toBeVisible();
    await expect(card.getByRole('list', { name: 'Career progress' }).locator('li')).toHaveCount(72);
    await expect(card.getByRole('link', { name: 'Open cockpit' })).toBeVisible();

    /*
     * Absence keeps its tile (annotation 2). The team rank tile always exists, and it prints either a
     * rank or the word \`N/A\`; a blank value is the defect this guards.
     */
    const rank = card.locator('[data-tile="team_rank"] [data-tile-value]');
    await expect(rank).not.toHaveText('');

    // Annotation 1: "Open cockpit" is the only filled action in the resume card.
    await expect(card.locator('[data-variant="primary"]')).toHaveCount(1);
    await expect(page.locator('[data-variant="primary"]')).toHaveCount(1);
});

test('the recent veterans card names its rows, or its absence', async ({ page }) => {
    await ready(page);

    const card = page.getByRole('region', { name: 'Recent veterans' });
    await expect(card.getByRole('heading', { level: 2 })).toHaveText('Recent veterans');

    const rows = card.locator('tbody tr');
    const count = await rows.count();

    if (count === 0) {
        await expect(card.getByText('Not recorded: the library holds no veterans yet.')).toBeVisible();
    } else {
        await expect(card.getByRole('columnheader', { name: 'Trainee' })).toBeVisible();
        await expect(card.getByRole('columnheader', { name: 'Scenario' })).toBeVisible();
        await expect(card.getByRole('columnheader', { name: 'Saved' })).toBeVisible();
        await expect(rows.first().getByRole('link', { name: 'Open' })).toBeVisible();
    }
    await expect(card.getByRole('link', { name: /All veterans|Open the Veteran library/ })).toBeVisible();
});

test('logs no console errors at 1280 and 390, and the page does not scroll sideways on the phone', async ({ page }) => {
    const errors: string[] = [];
    page.on('console', (message) => {
        if (message.type() === 'error') errors.push(message.text());
    });

    await ready(page);
    await page.setViewportSize({ width: 390, height: 844 });
    await ready(page);

    expect(errors).toEqual([]);

    // WCAG 1.4.10: the phone reflows into one column instead of offering a sideways scroll.
    const overflow = await page.evaluate(
        () => document.documentElement.scrollWidth - document.documentElement.clientWidth,
    );
    expect(overflow).toBeLessThanOrEqual(1);
});

test('the chrome is still the wireframe blue on this page', async ({ page }) => {
    await ready(page);
    await expect(page.locator('[data-shell-topbar]')).toHaveCSS('background-color', BLUE);
});