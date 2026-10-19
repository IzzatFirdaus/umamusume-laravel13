import { test, expect } from '@playwright/test';
import { createServer, type ViteDevServer } from 'vite';
import vue from '@vitejs/plugin-vue';
import path from 'node:path';
import { fileURLToPath } from 'node:url';

/*
 * A-101 (plan slice 1.12 / 0.6): does a required `BaseSelect` expose its required state, or only
 * paint it?
 *
 * The gate is the rendered attribute, not the accessibility tree, and that choice was measured rather
 * than assumed. Read over CDP, Chromium exposes `required=true` for `<input required>` and for
 * `<input aria-required="true">` (both role=textbox), and exposes **no `required` property at all**
 * for `<select required>` and `<select aria-required="true">` (both role=combobox). So for this
 * control the tree reports the same thing with the fix and without it, and an assertion written
 * against the tree would fail on a Chromium serialization limit instead of on the application. The
 * DOM attribute is the channel assistive tech reads and the one this component can actually change.
 *
 * A component-level browser case for the reason `provenance-badge.spec.ts` gives: the Vite server
 * here runs with `configFile: false`, so it never loads `vite.config.js`, never writes `public/hot`
 * and never touches `public/build`. The app harness is untouched, which is what makes this runnable
 * while a peer holds uncommitted files and `npm run build` is withheld (KI-70).
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

test('a required select exposes aria-required on both template branches, and an optional one says so', async ({ page }) => {
    await page.goto(`${base}base-select.html`);
    await expect(page.locator('#select-mount select')).toHaveCount(4);

    // An optional select reads `aria-required="false"`, not nothing: Vue casts an absent Boolean prop
    // to `false`, and this component already exposes `aria-invalid="false"` the same way. Asserting
    // the explicit false keeps the case honest about what the control renders rather than what a
    // reader expects it to omit.

    // The labelled branch.
    await expect(page.locator('#labelled-required')).toHaveAttribute('required', '');
    await expect(page.locator('#labelled-required')).toHaveAttribute('aria-required', 'true');
    await expect(page.locator('#labelled-optional')).not.toHaveAttribute('required');
    await expect(page.locator('#labelled-optional')).toHaveAttribute('aria-required', 'false');

    // The bare branch, which is the one the Snapshot form renders.
    await expect(page.locator('#bare-required')).toHaveAttribute('required', '');
    await expect(page.locator('#bare-required')).toHaveAttribute('aria-required', 'true');
    await expect(page.locator('#bare-optional')).not.toHaveAttribute('required');
    await expect(page.locator('#bare-optional')).toHaveAttribute('aria-required', 'false');
});

test('a labelled select takes its accessible name from the label prop, exactly once', async ({ page }) => {
    await page.goto(`${base}base-select.html`);

    // Plan 4.x / A-98's contract: a call site passes its label IN as a prop, and BaseSelect owns the
    // `<label for>` that names the select. `exact` is the assertion, not a convenience: a site that
    // both passes the prop and wraps the control in its own `<label>` yields the name twice, and an
    // inexact match would accept that doubled name as a pass.
    await expect(page.getByRole('combobox', { name: 'Required field', exact: true })).toHaveCount(1);
    await expect(page.getByRole('combobox', { name: 'Optional field', exact: true })).toHaveCount(1);

    // The doubled name a wrapper label would produce is absent, which is what the exact matches above
    // depend on.
    await expect(page.getByRole('combobox', { name: 'Required field Required field' })).toHaveCount(0);
    await expect(page.getByRole('combobox', { name: 'Optional field Optional field' })).toHaveCount(0);
});
