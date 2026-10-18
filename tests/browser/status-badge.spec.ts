import { test, expect } from '@playwright/test';
import { createServer, type ViteDevServer } from 'vite';
import vue from '@vitejs/plugin-vue';
import path from 'node:path';
import { fileURLToPath } from 'node:url';

/*
 * StatusBadge (plan 1.6). In-spec Vite fixture: no `public/build`, no `public/hot` (KI-70).
 *
 * The component's contract is small and absolute: three exception states render a worded pill, and
 * anything else renders nothing. Both halves are asserted, because a badge that renders "Confirmed"
 * beside every row is the defect the component exists to prevent.
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
        server: { host: '127.0.0.1', port: 5204, strictPort: false, watch: null, hmr: false },
    });
    await server.listen();
    base = server.resolvedUrls!.local[0]!;
});

test.afterAll(async () => {
    await server.close();
});

async function open(page: import('@playwright/test').Page): Promise<void> {
    await page.goto(`${base}status-badge.html`);
    await page.locator('#fixture-main').waitFor();
}

test('renders a worded pill for each of the three exception states', async ({ page }) => {
    await open(page);

    const cases: [string, string][] = [
        ['#badge-estimated', 'Estimated'],
        ['#badge-unknown', 'Unknown'],
        ['#badge-unconfirmed', 'Unconfirmed'],
    ];

    for (const [selector, word] of cases) {
        const pill = page.locator(selector);
        await expect(pill).toBeVisible();
        // The word is present, so the state is never carried by colour alone.
        await expect(pill).toContainText(word);
    }

    // A caller may supply its own word; it replaces the default rather than sitting beside it.
    const custom = page.locator('#badge-custom');
    await expect(custom).toContainText('Fuzzy match');
    await expect(custom).not.toContainText('Estimated');
});

test('renders nothing at all for a confirmed row', async ({ page }) => {
    await open(page);

    const host = page.locator('#badge-confirmed-host');

    // The host exists (the fixture put it there) and is empty: no pill, no word, no wrapper.
    await expect(host).toBeAttached();
    await expect(host).toBeEmpty();

    // And specifically: the word Confirmed never appears anywhere in the document.
    await expect(page.locator('body')).not.toContainText('Confirmed');
});

test('does not expose the decorative dot as text', async ({ page }) => {
    await open(page);

    // The bullet is aria-hidden, so the accessible text is the word alone.
    const pill = page.locator('#badge-estimated');
    const dot = pill.locator('span[aria-hidden="true"]');
    await expect(dot).toHaveText('•');

    const accessible = await pill.evaluate((el) => (el as HTMLElement).innerText.replace(/\u2022/g, '').trim());
    expect(accessible).toBe('Estimated');
});
