import { test, expect } from '@playwright/test';
import { createServer, type ViteDevServer } from 'vite';
import vue from '@vitejs/plugin-vue';
import path from 'node:path';
import { fileURLToPath } from 'node:url';

/*
 * DataTable and Pagination (plan 1.5). In-spec Vite fixture, so this adds no build debt: it never
 * reads `public/build` and never writes `public/hot` (KI-70).
 *
 * The gate is the rendered DOM, not computed style: with `configFile: false` there is no Tailwind
 * pipeline, so colours and spacing would be browser defaults. What the components own is structure and
 * semantics, and those are what is asserted.
 */
let server: ViteDevServer;
let base: string;

const fixtures = path.resolve(path.dirname(fileURLToPath(import.meta.url)), 'fixtures');

test.beforeAll(async () => {
    server = await createServer({
        configFile: false,
        root: fixtures,
        plugins: [vue()],
        logLevel: 'silent',
        appType: 'mpa',
        server: { host: '127.0.0.1', port: 5203, strictPort: false, watch: null, hmr: false },
    });
    await server.listen();
    base = server.resolvedUrls!.local[0]!;
});

test.afterAll(async () => {
    await server.close();
});

async function open(page: import('@playwright/test').Page): Promise<void> {
    await page.goto(`${base}data-table.html`);
    await page.locator('#fixture-main').waitFor();
}

test('is a real table: thead, th[scope="col"], and a caption that names it', async ({ page }) => {
    await open(page);

    const table = page.locator('#table-plain');
    await expect(table.locator('thead')).toHaveCount(1);
    await expect(table.locator('tbody')).toHaveCount(1);

    const headers = table.getByRole('columnheader');
    await expect(headers).toHaveCount(3);
    for (const name of ['Race', 'Grade', 'Fan gate']) {
        await expect(table.getByRole('columnheader', { name })).toBeVisible();
    }

    // `scope` on every header, so a screen reader ties a cell to its column.
    const scopes = await table.locator('thead th').evaluateAll((els) => els.map((e) => e.getAttribute('scope')));
    expect(scopes).toEqual(['col', 'col', 'col']);

    // The caption is the table's accessible name.
    await expect(table.locator('caption')).toHaveText('Races this career can enter');
    const labelledBy = await table.locator('table').getAttribute('aria-labelledby');
    await expect(page.locator(`#${labelledBy}`)).toHaveText('Races this career can enter');
});

test('renders a missing value as N/A with its reason, never as a dash', async ({ page }) => {
    await open(page);

    const table = page.locator('#table-plain');

    // Row 2 has a null fan gate, row 3 an empty string. Both are absences.
    //
    // DESIGN.md §10's rendered-copy row and AGENTS.md's copy rule both say "`N/A` for absence, never
    // a dash", and this case previously pinned the opposite: an `aria-hidden` en dash with the words
    // beside it. The dash is gone, and the reason is inside `AbsenceValue`'s summary rather than in a
    // sibling span, so it is reachable on focus as well as on hover.
    const absent = table.locator('tbody tr').nth(1).locator('td').nth(2);
    await expect(absent).toContainText('N/A');
    await expect(absent.locator('summary')).toHaveAccessibleName(/Not recorded/);
    await expect(absent).not.toContainText('–');

    const emptyString = table.locator('tbody tr').nth(2).locator('td').nth(2);
    await expect(emptyString.locator('summary')).toHaveAccessibleName(/Not recorded/);

    // A zero is a value, not an absence: rendering it as a dash would be a lie about the data.
    const zero = table.locator('tbody tr').nth(3).locator('td').nth(2);
    await expect(zero).not.toContainText('Not recorded');
    await expect(zero).toHaveText('0');
});

test('the wrapper is a focusable region named after the table', async ({ page }) => {
    await open(page);

    const region = page.locator('#table-plain');
    await expect(region).toHaveAttribute('role', 'region');
    await expect(region).toHaveAttribute('tabindex', '0');
    await expect(region).toHaveAttribute('aria-label', 'Races this career can enter');

    // Focusable for real: a region that cannot take focus cannot be scrolled by keyboard.
    await region.focus();
    expect(await region.evaluate((el) => document.activeElement === el)).toBe(true);
});

test('rowHref makes the first cell a link, and a null href leaves the row plain', async ({ page }) => {
    await open(page);

    const table = page.locator('#table-linked');
    const rows = table.locator('tbody tr');

    // Row 1 carries a link on its first cell.
    const linked = rows.nth(0).locator('td').nth(0).getByRole('link');
    await expect(linked).toHaveText('Tenno Sho (Spring)');
    await expect(linked).toHaveAttribute('href', '/races/1');

    // Row 4's href builder returns null, so that row must not be a link at all.
    const plain = rows.nth(3).locator('td').nth(0);
    await expect(plain.getByRole('link')).toHaveCount(0);
    await expect(plain).toHaveText('Hopeful Stakes');
});

test('a sortable header carries aria-sort and a button, and reports the click', async ({ page }) => {
    await open(page);

    const table = page.locator('#table-plain');
    const fanGate = table.getByRole('columnheader', { name: /Fan gate/ });

    await expect(fanGate).toHaveAttribute('aria-sort', 'descending');
    // The label lives in a real button, so the header is operable by keyboard, not by clicking a `th`.
    await expect(fanGate.getByRole('button')).toBeVisible();

    // A non-sortable column must not claim a sort state.
    await expect(table.getByRole('columnheader', { name: 'Race' })).not.toHaveAttribute('aria-sort');

    await fanGate.getByRole('button').click();
    await expect(fanGate).toHaveAttribute('aria-sort', 'ascending');
});

test('an unrecorded cell states its absence in text a keyboard can reach', async ({ page }) => {
    await open(page);

    // DESIGN.md §5 (a), 2026-10-17: one absence channel per component, and the reason is reachable
    // without a mouse. The cell used to carry `title="Not recorded"` on a bare span — a constant
    // reason with no `sr-only` twin, despite this file's own note claiming one. `<details>` is flow
    // content, so a `td` is exactly the container the component's contract allows for it.
    const absent = page.locator('#table-plain tbody tr').nth(1).locator('td').nth(2);

    const summary = absent.locator('summary');
    await expect(summary).toContainText('N/A');
    await expect(summary).toHaveAccessibleName(/Not recorded/);

    // The hover-only channel is gone.
    await expect(absent.locator('[title]')).toHaveCount(0);

    // And it opens from the keyboard, which is the whole point of the migration.
    await summary.focus();
    await expect(summary).toBeFocused();
    await page.keyboard.press('Enter');
    await expect(absent.locator('details[open]')).toContainText('Not recorded');
});

test('pagination names the range, the page count, and marks the current page', async ({ page }) => {
    await open(page);

    const pager = page.getByRole('navigation', { name: 'Pagination' });
    await expect(pager).toBeVisible();

    await expect(pager).toContainText('Showing 26–50 of 67 races');
    await expect(pager).toContainText('Page 2 of 3');

    await expect(pager.locator('[aria-current="page"]')).toHaveText('2');

    // Previous is available at page 2 of 3; Next is too. Neither is disabled.
    await expect(pager.getByRole('button', { name: 'Previous' })).toBeEnabled();
    await expect(pager.getByRole('button', { name: 'Next' })).toBeEnabled();

    // The counts are announced once, not as three separate live fragments.
    await expect(pager.locator('[role="status"]')).toHaveCount(1);
});
