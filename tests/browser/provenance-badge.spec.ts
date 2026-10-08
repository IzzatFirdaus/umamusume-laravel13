import { test, expect } from '@playwright/test';
import { createServer, type ViteDevServer } from 'vite';
import vue from '@vitejs/plugin-vue';
import path from 'node:path';
import { fileURLToPath } from 'node:url';

/*
 * KI-62 item 2: the regression for `ProvenanceBadge.vue` itself. The D8 Cockpit case
 * (`career-cockpit.spec.ts`) proved the *class* — a `<script setup>` body runs once per instance, so
 * a derivation taken at setup freezes — but no test rendered the badge with a `state` that changes
 * in place. This one mounts the real SFC, flips the prop on the mounted instance, and asserts the
 * accessible name, the glyph and the word all follow.
 *
 * A component-level browser case, not Vitest: this repo has no Vitest setup, and mounting the SFC
 * needs its compiler either way. The Vite server it starts runs with `configFile: false`, so it
 * never loads `vite.config.js` and never writes `public/hot`; the app's own harness is untouched.
 *
 * DO NOT simplify `copy` / `name` in `ProvenanceBadge.vue` back to setup-time consts. If that
 * happens this case fails: after the flip, the name, the glyph and the word all stay at the first
 * render's state. The same-node probe below keeps that honest — a remount would otherwise let a
 * stale-value check pass by rendering a fresh badge.
 */
let server: ViteDevServer;
let base: string;

// Rooted at the fixtures dir so Vite's dependency crawl and file watcher stay off the repository
// tree (watching the whole root burns minutes of CPU on this box); the SFC import reaches out of
// the root, which `server.fs.allow` permits because the repo root is the workspace root.
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
            port: 5199,
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

test('a badge re-rendered in place tracks the new state name, glyph and word', async ({ page }) => {
    await page.goto(`${base}provenance-badge.html`);

    const badge = page.locator('#badge-mount [role="img"]');
    await expect(badge).toHaveAttribute('aria-label', 'Confirmed');
    // `\s*` between the spans: Vue condenses the template's whitespace, so the glyph and the word
    // sit adjacent in textContent with no guaranteed separator.
    await expect(badge).toHaveText(/^✓\s*Confirmed$/);

    // Marks the live node: every flip below must be observed on this same element.
    await badge.evaluate((el) => {
        (el as unknown as { __ki62?: number }).__ki62 = 1;
    });

    await page.evaluate(() => window.__setBadgeState('unknown'));
    await expect(badge).toHaveAttribute('aria-label', 'Unknown');
    await expect(badge).toHaveText(/^\?\s*Unknown$/);
    await expect(badge).not.toHaveText(/^✓\s*Confirmed$/);

    await page.evaluate(() => window.__setBadgeState('calculated'));
    await expect(badge).toHaveAttribute('aria-label', 'Calculated');
    await expect(badge).toHaveText(/^∑\s*Calculated$/);

    expect(await badge.evaluate((el) => (el as unknown as { __ki62?: number }).__ki62)).toBe(1);
});
