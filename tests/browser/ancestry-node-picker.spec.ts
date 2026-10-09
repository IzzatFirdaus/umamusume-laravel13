import { test, expect } from '@playwright/test';
import { createServer, type ViteDevServer } from 'vite';
import vue from '@vitejs/plugin-vue';
import path from 'node:path';
import { fileURLToPath } from 'node:url';

/*
 * The AncestryNode pick control, mounted and exercised.
 *
 * `LegacySelect.vue`'s own docblock states the contract: "The node emits on `change` and takes its current
 * value as a prop", because a native `<select>` fires `change` and never `update:model-value`. Both
 * assignable callers (`LegacySelect.vue`, `Builder.vue`) pass `:model-value` and
 * `@update:model-value` on that understanding. The node declared neither, so the listener never ran, the
 * attribute fell through onto the node's `<li>`, and the parent a Trainer picked was dropped from the PUT
 * that saves the step. This case is the check that keeps it wired: with the emit removed it fails at the
 * `picked: 9` assertion, and with the prop removed it fails at the read-back.
 *
 * A component-level browser case rather than a page walk, for the reason `provenance-badge.spec.ts`
 * records: the roster this picker lists is the Veteran library, which is empty on a fresh install
 * (`KNOWN-ISSUES.md` KI-69 Class 3), so no page-level spec can select an option at all. The Vite server
 * runs with `configFile: false`, so it never loads the app config and never writes `public/hot`.
 */
let server: ViteDevServer;
let base: string;

// Rooted at the fixtures dir so Vite's crawl and watcher stay off the repository tree, as the badge
// fixture does; the SFC import reaches out of the root, which `server.fs.allow` permits.
const fixtures = path.resolve(path.dirname(fileURLToPath(import.meta.url)), 'fixtures');

test.beforeAll(async () => {
    server = await createServer({
        configFile: false,
        root: fixtures,
        plugins: [vue()],
        logLevel: 'silent',
        appType: 'mpa',
        server: {
            host: '127.0.0.1',
            port: 5198,
            strictPort: false,
            watch: null,
            hmr: false,
        },
    });
    await server.listen();
    base = server.resolvedUrls!.local[0]!;
});

test.afterAll(async () => {
    await server.close();
});

test('the node emits the pick on change and reads the current pick back from its prop', async ({ page }) => {
    await page.goto(`${base}ancestry-node-picker.html`);

    const picker = page.getByLabel('Assign to Parent A');
    await expect(picker).toBeVisible();
    await expect(picker).toHaveValue('');

    // The bug this exists for: a chosen parent has to reach the caller's form, not just the DOM.
    await picker.selectOption({ label: 'Oguri Cap' });
    await expect(page.locator('#picked')).toHaveText('picked: 9');

    // Keyboard path, since this is the only way the library is assignable.
    await picker.focus();
    await page.keyboard.press('ArrowUp');
    await expect(page.locator('#picked')).toHaveText('picked: 7');

    // And the value the caller holds is what the control shows, so a saved step reads back.
    await page.evaluate(() => window.__setModel('9'));
    await expect(picker).toHaveValue('9');
});
