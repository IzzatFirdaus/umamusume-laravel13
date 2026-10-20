import { test, expect } from '@playwright/test';
import { createServer, type ViteDevServer } from 'vite';
import vue from '@vitejs/plugin-vue';
import path from 'node:path';
import { fileURLToPath } from 'node:url';

/*
 * TextInput's type contract (5eccaaf8): the union gained `number`.
 *
 * The union's enforcement is typecheck-level and fires at the real consumer — `Runs/Create.vue`
 * renders `<TextInput type="number">`, so reverting the union reds `npm run typecheck`. It does NOT
 * fire through `h()`, whose props are loosely typed, which is why these cases measure the DOM
 * contract instead: the declared `type` reaches the `<input>`, and the UNDECLARED `min`/`max` reach
 * it through Vue's single-root attribute fallthrough.
 *
 * In-spec Vite fixture: no `public/build`, no `public/hot` (KI-70).
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
        server: { host: '127.0.0.1', port: 5207, strictPort: false, watch: null, hmr: false },
    });
    await server.listen();
    base = server.resolvedUrls!.local[0]!;
});

test.afterAll(async () => {
    await server.close();
});

async function open(page: import('@playwright/test').Page): Promise<void> {
    await page.goto(`${base}text-input.html`);
    await page.locator('#fixture-main').waitFor();
}

test('a number input renders type=number and takes min/max through attribute fallthrough', async ({ page }) => {
    await open(page);

    const input = page.locator('#ti-number');

    await expect(input).toBeVisible();

    // The declared prop.
    await expect(input).toHaveAttribute('type', 'number');

    // The undeclared pair: neither `min` nor `max` is a TextInput prop, so their presence proves
    // Vue's single-root fallthrough is doing the work.
    await expect(input).toHaveAttribute('min', '1');
    await expect(input).toHaveAttribute('max', '10');
});

test('a text input still renders type=text', async ({ page }) => {
    await open(page);

    await expect(page.locator('#ti-text')).toHaveAttribute('type', 'text');
});
