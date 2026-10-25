import { test, expect } from '@playwright/test';
import { startCareer } from '../utils/start-career';
import { recordTurns } from '../utils/record-turns';

/*
 * The Dashboard, built as a structural clone of the HFW dashboard.html. This spec asserts the
 * wireframe's four grid rows, the blue chrome, the three-tile KPI strip, the Roster card and the
 * one-filled-primary rule. It also asserts the absence of the wireframe's own meta artifacts: the
 * annotation key, the table of contents and the "Static wireframe" chip belong to the document set,
 * not to the product (owner ruling 2026-10-26).
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
    await recordTurns(page, runId, 67);
}

/** The painted box of one grid child, so row geometry is measured rather than read off a class list. */
async function box(page: import('@playwright/test').Page, selector: string) {
    const el = page.locator(selector as string);
    await expect(el).toBeVisible();
    const rect = await el.boundingBox();
    expect(rect, `${selector} has no painted box`).not.toBeNull();
    return rect!;
}

test('the chrome is blue and carries the Help link and no wireframe chip', async ({ page }) => {
    await ready(page);

    const bar = page.locator('[role="banner"] > div:first-child');
    await expect(bar).toHaveCSS('background-color', BLUE);
    await expect(bar).toHaveCSS('border-bottom-width', '2px');
    await expect(bar.getByRole('link', { name: 'Help' })).toBeVisible();
    await expect(bar).toContainText('Dashboard');

    await expect(bar.locator('[data-shell-proto-chip]')).toHaveCount(0);
    await expect(page.getByText('Static wireframe')).toHaveCount(0);
});

test('the empty state offers the one filled primary', async ({ page }) => {
    await ready(page);

    // The suite's scratch database starts with no active career, and this case runs first.
    const card = page.getByRole('region', { name: 'Active Career' });
    await expect(card.getByText('No active career. Start a new career.')).toBeVisible();
    await expect(page.locator('[data-variant="primary"]')).toHaveCount(1);
});

test('renders the five wireframe regions and none of the wireframe meta blocks', async ({ page }) => {
    await ready(page);

    for (const name of ['Active Career', 'Quick Actions', 'Roster', 'Data Status', 'Recent Veterans']) {
        await expect(page.getByRole('region', { name })).toBeVisible();
    }

    const cards = await page.locator('[data-dashboard-root] > section').evaluateAll((sections) =>
        sections.map((s) => s.getAttribute('data-card') ?? ''),
    );
    expect(cards).toEqual(['active_career', 'quick_actions', 'roster', 'data_status', 'recent_veterans']);

    await expect(page.getByRole('region', { name: 'Table of contents' })).toHaveCount(0);
    await expect(page.getByRole('region', { name: 'Annotation key' })).toHaveCount(0);
    await expect(page.getByRole('heading', { name: 'Annotation key' })).toHaveCount(0);
});

test('the grid stacks the wireframe rows: career + actions, KPI, roster + status, veterans', async ({ page }) => {
    await page.setViewportSize({ width: 1440, height: 900 });
    await ready(page);

    const career = await box(page, '[data-card="active_career"]');
    const actions = await box(page, '[data-card="quick_actions"]');
    const kpi = await box(page, '[data-dashboard-kpi]');
    const roster = await box(page, '[data-card="roster"]');
    const status = await box(page, '[data-card="data_status"]');
    const veterans = await box(page, '[data-card="recent_veterans"]');
    const grid = await box(page, '[data-dashboard-root]');

    // Row 1: the resume card, then the actions card to its right, on one line.
    expect(Math.abs(career.y - actions.y)).toBeLessThan(2);
    expect(actions.x).toBeGreaterThan(career.x + career.width - 1);

    // Row 2: the KPI strip clears row 1 and is wider than either card above it.
    expect(kpi.y).toBeGreaterThan(career.y + career.height - 1);
    expect(kpi.width).toBeGreaterThan(career.width);

    // Row 3: Roster, then Data Status to its right, on one line, below the strip.
    expect(Math.abs(roster.y - status.y)).toBeLessThan(2);
    expect(status.x).toBeGreaterThan(roster.x + roster.width - 1);
    expect(roster.y).toBeGreaterThan(kpi.y + kpi.height - 1);

    // Row 4: Recent Veterans across the whole grid, below row 3.
    expect(veterans.y).toBeGreaterThan(Math.max(roster.y + roster.height, status.y + status.height) - 1);
    expect(veterans.width).toBeGreaterThanOrEqual(grid.width - 1);
});

test('shows the three-tile KPI strip and the Roster card', async ({ page }) => {
    await ready(page);

    const kpi = page.locator('[data-dashboard-kpi]');
    await expect(kpi.getByText('Trainees', { exact: true })).toBeVisible();
    await expect(kpi.getByText('Skills', { exact: true })).toBeVisible();
    await expect(kpi.getByText('Support cards', { exact: true })).toBeVisible();
    await expect(kpi.getByText('Careers running')).toHaveCount(0);

    const roster = page.getByRole('region', { name: 'Roster' });
    await expect(roster.getByText('Forms', { exact: true })).toBeVisible();
    await expect(roster.getByText('Release', { exact: true })).toBeVisible();
    await expect(roster.getByRole('link', { name: 'Browse catalog' })).toBeVisible();
});

test('the active career card reads the wireframe state and holds the one filled primary', async ({ page }) => {
    await createActiveRun(page);
    await ready(page);

    const card = page.getByRole('region', { name: 'Active Career' });
    await expect(card).toBeVisible();
    await expect(card).toContainText('Turn 67 of 72');
    await expect(card.getByRole('link', { name: 'Resume career' })).toBeVisible();

    // One filled primary on the whole screen: the Resume button.
    await expect(page.locator('[data-variant="primary"]')).toHaveCount(1);
    await expect(card.locator('[data-variant="primary"]')).toHaveCount(1);
});

test('the recent veterans region names its state', async ({ page }) => {
    await ready(page);

    // The scratch database holds no veterans, so the region prints the empty state rather than a
    // table with invented rows.
    const card = page.getByRole('region', { name: 'Recent Veterans' });
    await expect(card.getByText('No veterans yet.')).toBeVisible();
});

test('logs no console errors at 1280 and 390', async ({ page }) => {
    const errors: string[] = [];
    page.on('console', (message) => {
        if (message.type() === 'error') errors.push(message.text());
    });

    await ready(page);
    await page.setViewportSize({ width: 390, height: 844 });
    await ready(page);

    expect(errors).toEqual([]);
});
