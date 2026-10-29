import { test, expect } from '@playwright/test';
import { startCareer } from '../utils/start-career';
import { recordTurns } from '../utils/record-turns';

/*
 * The Dashboard, built as a structural clone of the HFW `index.html` (owner ruling 2026-10-26: the
 * wireframe is the visual authority, and `index.html` -- not `dashboard.html` -- is the board).
 *
 * What this pins:
 *
 *   - the page head: eyebrow `Dashboard`, h1 `Trainer Desk`, the three-figure lede, and the two
 *     actions in the wireframe's own pair (one outline, one quiet) -- no filled action in the head;
 *   - the wireframe's three rows in its order: "Pick up where you left off" + "Needs you", the
 *     four-figure KPI strip, then "Recent veterans" + "Quick actions", each row 2.1fr / 1fr;
 *   - the resume card's own composition: 64px frame, name beside the accent pill, one meta line,
 *     "Open cockpit" on the row, three tiles with the Turn tile's "of 72" suffix;
 *   - the absence rule: Energy and Team rank keep their tiles and print a word, never a blank;
 *   - the labelled turn-count defect: the note names the recorded count and the cockpit's position;
 *   - the veterans footer's own sentence, "N veterans · N shown";
 *   - exactly one filled primary on the screen (annotations 1 and 6);
 *   - the absence of the wireframe's own meta blocks: the Table of contents card and the annotation
 *     key are document artifacts, not screens.
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

test('the page head names the screen with the wireframe eyebrow and one unfilled action pair', async ({ page }) => {
    await ready(page);

    const banner = page.getByRole('banner');
    // index.html:55 -- the eyebrow is the screen's own name, not "Overview". Scoped to a `p`
    // because the bar's crumb already reads "Dashboard", and the eyebrow is the page's copy of it.
    const eyebrow = banner.locator('p').filter({ hasText: /^Dashboard$/ });
    await expect(eyebrow).toHaveCount(1);
    await expect(eyebrow).toBeVisible();
    await expect(banner.getByText('Overview', { exact: true })).toHaveCount(0);
    await expect(banner.getByRole('heading', { level: 1 })).toHaveText('Trainer Desk');

    // index.html:59 -- the lede's three figures, bound to live props.
    await expect(banner.getByText(/active careers?, \d+ finished runs?, and \d+ permanent Global scenarios?\./)).toBeVisible();

    // index.html:61-64: "New career" is the outline and "All careers" is the quiet one.
    await expect(banner.getByRole('link', { name: 'New career' })).toBeVisible();
    await expect(banner.getByRole('link', { name: 'All careers' })).toBeVisible();

    // Annotations 1 and 6: the head carries no filled action. The accent belongs to "Open cockpit".
    await expect(banner.locator('[data-variant="primary"]')).toHaveCount(0);
    await expect(banner.locator('[data-variant="secondary"]')).toHaveCount(1);
});

test('renders the wireframe rows in its order and none of its meta blocks', async ({ page }) => {
    await ready(page);

    for (const name of ['Pick up where you left off', 'Recent veterans', 'Needs you', 'Quick actions']) {
        await expect(page.getByRole('region', { name })).toBeVisible();
    }

    const order = await page.locator('[data-dashboard-root] [data-card]').evaluateAll((cards) =>
        cards.map((card) => card.getAttribute('data-card') ?? ''),
    );
    // index.html reading order (index.html:71-186): row one, the four-figure strip, then row two.
    expect(order).toEqual([
        'active_career',
        'needs_you',
        'kpi_careers',
        'kpi_trainees',
        'kpi_skills',
        'kpi_support',
        'recent_veterans',
        'quick_actions',
    ]);

    // The wireframe's navigation aid and its annotation key belong to the document, not the product.
    await expect(page.getByRole('region', { name: 'Table of contents' })).toHaveCount(0);
    await expect(page.getByRole('heading', { name: /Table of contents/i })).toHaveCount(0);
    await expect(page.getByRole('heading', { name: /Annotation key/i })).toHaveCount(0);
    // The wireframe's own provenance chip is a document artifact too.
    await expect(page.getByText('Static wireframe')).toHaveCount(0);

    // The wireframe's class names, as a second, copy-independent net: a block pasted in from the
    // document would arrive carrying `.keybox`/`.toc` whether or not its heading text survived.
    await expect(page.locator('.keybox')).toHaveCount(0);
    await expect(page.locator('.toc')).toHaveCount(0);
});

test('the two side rows lay out as a wide column and a narrow one', async ({ page }) => {
    await page.setViewportSize({ width: 1280, height: 800 });
    await ready(page);

    const pickup = await box(page, '[data-dashboard-row="pickup"] [data-card="active_career"]');
    const needs = await box(page, '[data-dashboard-row="pickup"] [data-card="needs_you"]');
    const veterans = await box(page, '[data-dashboard-row="veterans"] [data-card="recent_veterans"]');
    const quick = await box(page, '[data-dashboard-row="veterans"] [data-card="quick_actions"]');

    /*
     * Row one: the wide card, then the narrow one on the same line. The comparison is between row
     * containers, not between cards: `.card + .card` stacks a sibling with 14px of margin in a
     * column, and a grid row that happened to be satisfied by one card must not be read as the card
     * being misplaced. Both cards are in the same row when both share a row container.
     */
    expect(needs.x).toBeGreaterThan(pickup.x + pickup.width - 1);
    expect(pickup.width).toBeGreaterThan(needs.width);
    const rowOne = await box(page, '[data-dashboard-row="pickup"]');
    expect(Math.abs(pickup.y - rowOne.y)).toBeLessThan(2);
    expect(Math.abs(needs.y - rowOne.y)).toBeLessThan(2);

    // Row two: same shape, below the KPI strip.
    expect(quick.x).toBeGreaterThan(veterans.x + veterans.width - 1);
    expect(veterans.y).toBeGreaterThanOrEqual(pickup.y + pickup.height - 1);
    expect(Math.abs(veterans.y - quick.y)).toBeLessThan(2);

    // The wireframe's 2.1fr / 1fr split, measured rather than read from a class list.
    const ratio = pickup.width / needs.width;
    expect(ratio).toBeGreaterThan(2.0);
    expect(ratio).toBeLessThan(2.3);
});

test('the KPI strip draws four cards in one row at 1280', async ({ page }) => {
    await page.setViewportSize({ width: 1280, height: 800 });
    await ready(page);

    for (const name of ['Careers running', 'Trainees catalogued', 'Skills', 'Support cards']) {
        await expect(page.getByRole('heading', { name })).toBeVisible();
    }

    // index.html:133-154 -- `.g-4` is four across on desktop, not two by two.
    const cards = await page
        .locator('[data-dashboard-row="kpi"] [data-card]')
        .evaluateAll((nodes) => nodes.map((node) => node.getBoundingClientRect()));
    expect(cards).toHaveLength(4);
    const tops = new Set(cards.map((rect) => Math.round(rect.top)));
    expect(tops.size, 'the four KPI cards are not on one line').toBe(1);

    // index.html:142-152: each catalogue figure is the door to the screen that owns its list.
    await expect(page.getByRole('link', { name: 'Browse catalog' })).toBeVisible();
    await expect(page.getByRole('link', { name: 'Skill search' })).toBeVisible();
    await expect(page.getByRole('link', { name: 'Browse cards' })).toBeVisible();
});

test('the Needs you column draws three tiles with their own doors', async ({ page }) => {
    await ready(page);

    const needs = page.getByRole('region', { name: 'Needs you' });
    const tiles = needs.locator('[data-needs-you]');
    await expect(tiles).toHaveCount(3);

    // index.html:113-118: the data-status tile, its risk edge, and the verified line.
    const status = needs.locator('[data-needs-you="data_status"]');
    await expect(status.getByText('Data status')).toBeVisible();
    await expect(status.getByText(/Global data · (Current|Stale)/)).toBeVisible();
    await expect(status.getByText(/Verified \d{4}-\d{2}-\d{2}/)).toBeVisible();
    await expect(status.getByRole('link', { name: 'Source ledger' })).toBeVisible();

    // index.html:119-123: "N banked", and the library door.
    const veterans = needs.locator('[data-needs-you="veterans"]');
    await expect(veterans.getByText(/\d+ banked/)).toBeVisible();
    await expect(veterans.getByRole('link', { name: 'Open the library' })).toBeVisible();

    // index.html:124-128: the review queue's instruction and its door.
    const review = needs.locator('[data-needs-you="review"]');
    await expect(review.getByText(/pending match|Check pending matches/)).toBeVisible();
    await expect(review.getByRole('link', { name: 'Open review' })).toBeVisible();
});

test('the quick actions are four outline destinations and a note', async ({ page }) => {
    await ready(page);

    const quick = page.getByRole('region', { name: 'Quick actions' });
    const nav = quick.getByRole('navigation', { name: 'Quick actions' });
    await expect(nav.getByRole('link')).toHaveCount(4);
    for (const label of ['Import a historical career', 'Review queue', 'Open settings', 'Database hub']) {
        await expect(nav.getByRole('link', { name: label })).toBeVisible();
    }
    // A quick action is never the filled primary; the resume card owns that.
    await expect(quick.locator('[data-variant="primary"]')).toHaveCount(0);
    // index.html:184 -- the card's own note, in the wireframe's words.
    await expect(quick.getByText(/Four destinations\./)).toBeVisible();
});

test('with no active career the resume card names the absence and keeps its actions', async ({ page }) => {
    await ready(page);

    const card = page.getByRole('region', { name: 'Pick up where you left off' });
    const hasCareer = await card.getByRole('link', { name: 'Open cockpit' }).count();

    if (hasCareer === 0) {
        await expect(card.getByText('Not recorded: no career is running.')).toBeVisible();
        await expect(card.getByRole('link', { name: 'Start a career' })).toBeVisible();
        await expect(card.getByRole('link', { name: 'Import an existing career' })).toBeVisible();
        // With nothing to resume, the head's outline is not enough: this card owns the one accent.
        await expect(card.locator('[data-variant="primary"]')).toHaveCount(1);
        await expect(page.locator('[data-variant="primary"]')).toHaveCount(1);
    }
});

test('an active career draws the resume card the wireframe draws', async ({ page }) => {
    await createActiveRun(page);
    await ready(page);

    const card = page.getByRole('region', { name: 'Pick up where you left off' });

    // index.html:87-100: three tiles, the Turn tile's "of 72" beside the figure.
    await expect(card.getByText('Turn', { exact: true })).toBeVisible();
    const turn = card.locator('[data-tile="turn"] [data-tile-value]');
    await expect(turn).toHaveText(/^\d+of \d+$/);

    // index.html:96-99: absence keeps its tile and prints a word, never a blank.
    const rank = card.locator('[data-tile="team_rank"] [data-tile-value]');
    await expect(rank).not.toHaveText('');

    // index.html:102-107: the labelled defect, naming both numbers.
    await expect(card.getByText(/A turn is counted from the turns recorded/)).toBeVisible();
    await expect(card.getByText(/cockpit reports turn \d+/)).toBeVisible();

    // index.html:84: "Open cockpit" is the card's filled action, and the page's only one.
    await expect(card.getByRole('link', { name: 'Open cockpit' })).toBeVisible();
    await expect(card.locator('[data-variant="primary"]')).toHaveCount(1);
    await expect(page.locator('[data-variant="primary"]')).toHaveCount(1);

    // The 72-mark rail is `dashboard.html`'s drawing, not this board's.
    await expect(card.getByRole('list', { name: 'Career progress' })).toHaveCount(0);
});

test('the recent veterans card names its rows, its footer and its door', async ({ page }) => {
    await ready(page);

    const card = page.getByRole('region', { name: 'Recent veterans' });
    await expect(card.getByRole('heading', { level: 2 })).toHaveText('Recent veterans');

    const rows = card.locator('tbody tr');
    const count = await rows.count();

    if (count > 0) {
        await expect(card.getByRole('columnheader', { name: 'Trainee' })).toBeVisible();
        await expect(card.getByRole('columnheader', { name: 'Scenario' })).toBeVisible();
        await expect(card.getByRole('columnheader', { name: 'Saved' })).toBeVisible();
        await expect(rows.first().getByRole('link', { name: 'Open' })).toBeVisible();
    }

    // index.html:170-173: "N veterans · N shown" beside the library door.
    await expect(card.getByText(/\d+ veterans? · \d+ shown/)).toBeVisible();
    await expect(card.getByRole('link', { name: 'All veterans' })).toBeVisible();
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

test('the chrome is the wireframe blue with its accent stripe on this page', async ({ page }) => {
    await ready(page);
    await expect(page.locator('[data-shell-topbar]')).toHaveCSS('background-color', BLUE);
    // index.html:45-52 -- the 2px accent rule under the bar.
    await expect(page.locator('[data-shell-topbar]')).toHaveCSS('border-bottom-width', '2px');
    // index.html:47 -- the crumb names the screen, and the product name lives in the rail.
    await expect(page.locator('[data-shell-crumb]')).toContainText('Dashboard');
    await expect(page.locator('[data-shell-crumb]')).not.toContainText('TRAINER DESK');
});
