import { test, expect } from '@playwright/test';
import { deleteRun } from '../utils/delete-run';
import { openScenarioDetail } from '../utils/scenario-detail';

/*
 * The Scenario detail page's card order, pinned against the HFW the owner ruled on
 * (`docs/design-research/scenario-detail-hfw-2026-11-09/scenario-detail-hfw.html`).
 *
 * **Why this case exists.** `S-RS-VISUAL-1` reordered the page from the component's DOM order to the
 * HFW's run-state-primary order (owner ruling W.2). Nothing in the suite could see that order before:
 * the capsule cases asserted chrome, and the panel cases asserted facts inside a card. A later
 * restyle could have reordered the page with every existing case still green, so the order is pinned
 * here as data rather than left to a human eye.
 *
 * **The expected list is the rendered subset, not the HFW's full twelve.** A fresh career records no
 * team identity, no Unity Cup counters and no Spirit reading, so `Team`, `Unity Cup progress` and
 * `Special Training` are `v-if`-absent; the goal cards are always present. Asserting the
 * full twelve would fail on correct code. The subset is asserted in the HFW's relative order, which is
 * the property that actually matters, and the two absences are named so a reader knows why.
 *
 * The reference strip (`Unity Cup`, `Published stat caps`) sits outside the grid by design and is
 * asserted to stay there: the HFW draws it outside `.b12` too.
 */

const WRITE = { timeout: 60_000 } as const;

const createdRunUrls: string[] = [];

test.afterEach(async ({ page }) => {
    for (const url of createdRunUrls.splice(0)) {
        await deleteRun(page, url);
    }
});

/** A fresh Unity Cup career, walked to its Scenario detail page. */
async function openFreshUnityCupDetail(page: import('@playwright/test').Page): Promise<void> {
    await page.goto('/training-runs/create', { waitUntil: 'domcontentloaded' });
    await page.locator('#app > *').first().waitFor();
    await page.locator('#trainee-combobox').fill('Rice Shower');
    await page.keyboard.press('Enter');
    await page.selectOption('select[name="scenario"]', { value: 'unity_cup' });
    await page.getByRole('button', { name: 'Create run' }).click();
    await page.waitForURL(/\/training-runs\/\d+\/cockpit$/, WRITE);
    createdRunUrls.push(page.url().replace(/\/cockpit$/, ''));

    await openScenarioDetail(page);
}

test('renders the scenario cards in the HFW run-state-primary order', async ({ page }) => {
    await page.setViewportSize({ width: 1280, height: 900 });
    await openFreshUnityCupDetail(page);

    const grid = page.locator('[data-bento="scenario-cards"]');
    await expect(grid).toBeVisible();

    const order = await grid.locator(':scope > [data-panel]').evaluateAll((els) =>
        els.map((el) => (el.querySelector('h2')?.textContent ?? '').trim()));

    // The HFW's own sequence, filtered to the cards a fresh run actually records. `Team` and
    // `Unity Cup progress` are absent because the run records no team identity and no counters;
    // `Special Training` IS present, because config states both of its facts unconditionally.
    expect(order).toEqual([
        'Team Race',
        'Burst count bands',
        'Special Training',
        'Trainee goals',
        'Mandatory races',
        'Team race coverage',
        'Team race shape',
        'Spirit',
        'Team Rank',
        'Team stat grades',
    ]);
});

test('draws the HFW span for every rendered card', async ({ page }) => {
    await page.setViewportSize({ width: 1280, height: 900 });
    await openFreshUnityCupDetail(page);

    const spans = await page
        .locator('[data-bento="scenario-cards"] > [data-panel]')
        .evaluateAll((els) => els.map((el) => {
            const h2 = (el.querySelector('h2')?.textContent ?? '').trim();
            return { title: h2, span: getComputedStyle(el).gridColumn.split('/')[0].trim() };
        }));

    // The HFW's spans at a 12-track container: `12 / 4 / 12 / 4 / 12 / 12 / 8 / 12 / 4 / 4 / 4 / 8`.
    // The four sub-12 rows are the drawing's own holes and are kept deliberately (owner ruling: match
    // the HFW, holes included); this case is what stops a later "cleanup" from quietly packing them.
    const expected: Record<string, string> = {
        'Team Race': 'span 12',
        'Burst count bands': 'span 12',
        'Special Training': 'span 4',
        'Trainee goals': 'span 12',
        'Mandatory races': 'span 12',
        'Team race coverage': 'span 8',
        'Team race shape': 'span 12',
        'Spirit': 'span 4',
        'Team Rank': 'span 4',
        'Team stat grades': 'span 8',
    };

    for (const row of spans) {
        expect(expected[row.title], `unexpected card ${row.title}`).toBeDefined();
        expect(row.span, `${row.title} span`).toBe(expected[row.title]);
    }
});

test('prints no capsule chrome anywhere in the scenario region', async ({ page }) => {
    await page.setViewportSize({ width: 1280, height: 900 });
    await openFreshUnityCupDetail(page);

    const section = page.getByRole('region', { name: 'Scenario' });
    await expect(section.locator('.lattice-bleed')).toHaveCount(0);
});

test('keeps the reference strip outside the card grid', async ({ page }) => {
    await page.setViewportSize({ width: 1280, height: 900 });
    await openFreshUnityCupDetail(page);

    // The HFW draws `Unity Cup` and `Published stat caps` outside the 12-track bento, and the page
    // keeps them there: a `s12` strip inside the grid would take a row of its own in the ruled order.
    const grid = page.locator('[data-bento="scenario-cards"]');

    await expect(grid.getByRole('heading', { name: 'Unity Cup', exact: true })).toHaveCount(0);
    await expect(grid.getByRole('heading', { name: 'Published stat caps', exact: true })).toHaveCount(0);

    const section = page.getByRole('region', { name: 'Scenario' });
    await expect(section.getByRole('heading', { name: 'Published stat caps', exact: true })).toHaveCount(1);
});
