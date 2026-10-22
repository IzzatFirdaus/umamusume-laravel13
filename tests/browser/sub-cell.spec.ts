import { test, expect } from '@playwright/test';
import { createServer, type ViteDevServer } from 'vite';
import vue from '@vitejs/plugin-vue';
import path from 'node:path';
import { fileURLToPath } from 'node:url';

/*
 * SubCell: a dense tile's figure, and the reason it is absent.
 *
 * In-spec Vite fixture: no `public/build`, no `public/hot` (KI-70). The gate is the rendered DOM,
 * because with `configFile: false` there is no Tailwind pipeline and computed styling would be browser
 * defaults — the component owns semantics here, not paint.
 *
 * The defect this file was written for (UX audit F-09): the reason behind an absent figure was a
 * `title` on the `<dd>`, which `DESIGN.md` §5 (a)'s 2026-10-17 amendment supersedes as a hover-only
 * channel. It is `AbsenceValue`'s disclosure now.
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
        server: { host: '127.0.0.1', port: 5211, strictPort: false, watch: null, hmr: false },
    });
    await server.listen();
    base = server.resolvedUrls!.local[0]!;
});

test.afterAll(async () => {
    await server.close();
});

async function open(page: import('@playwright/test').Page): Promise<void> {
    await page.goto(`${base}sub-cell.html`);
    await page.locator('#fixture-main').waitFor();
}

test('an absent figure puts its reason on a keyboard-reachable disclosure, not a title', async ({ page }) => {
    await open(page);

    const tile = page.locator('#absent-with-reason');

    const summary = tile.locator('summary');
    await expect(summary).toContainText('N/A');
    await expect(summary).toHaveAccessibleName(/No due turn is recorded for this milestone/);

    // The hover-only channel is gone.
    await expect(tile.locator('[title]')).toHaveCount(0);

    // It opens from the keyboard, which is the whole reason for the migration.
    await summary.focus();
    await expect(summary).toBeFocused();
    await page.keyboard.press('Enter');
    await expect(tile.locator('details[open]')).toContainText('No due turn is recorded for this milestone.');
});

test('a present figure renders its value plainly, with no absence marker', async ({ page }) => {
    await open(page);

    // A reason supplied beside a real number qualifies it; it must not turn the number into an absence.
    const qualified = page.locator('#present-with-qualifier');
    await expect(qualified.locator('[data-stat-value]')).toHaveText('12,000');
    await expect(qualified.locator('summary')).toHaveCount(0);

    const bare = page.locator('#present-bare');
    await expect(bare.locator('[data-stat-value]')).toHaveText('0');
    await expect(bare.locator('summary')).toHaveCount(0);
});

test('the tile is a valid definition list', async ({ page }) => {
    await open(page);

    // The `<dl>` root with `<dt>`/`<dd>` children is what keeps axe's `dlitem` quiet on every host.
    const list = page.locator('#absent-with-reason dl');
    await expect(list).toHaveCount(1);
    await expect(list.locator('dt')).toHaveCount(1);
    await expect(list.locator('dd')).toHaveCount(1);
});
