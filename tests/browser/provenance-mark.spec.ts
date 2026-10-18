import { test, expect } from '@playwright/test';
import { createServer, type ViteDevServer } from 'vite';
import vue from '@vitejs/plugin-vue';
import path from 'node:path';
import { fileURLToPath } from 'node:url';

/*
 * ProvenanceMark (plan 1.7). In-spec Vite fixture: no `public/build`, no `public/hot` (KI-70).
 *
 * Two properties, both absolute:
 *   1. A confirmed figure carries NO mark. Marking everything is how a badge stops meaning anything.
 *   2. The reason behind a mark is never hover-only. It is in the accessible name at all times and it
 *      appears visually on focus as well as hover (WCAG 1.4.13).
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
        server: { host: '127.0.0.1', port: 5205, strictPort: false, watch: null, hmr: false },
    });
    await server.listen();
    base = server.resolvedUrls!.local[0]!;
});

test.afterAll(async () => {
    await server.close();
});

async function open(page: import('@playwright/test').Page): Promise<void> {
    await page.goto(`${base}provenance-mark.html`);
    await page.locator('#fixture-main').waitFor();
}

test('a confirmed figure carries no mark at all', async ({ page }) => {
    await open(page);

    // Nothing attached, nothing rendered. Vue drops the subtree entirely when the root renders null,
    // so the absence of the element IS the behaviour: a wrapper with a zero-height span would still
    // occupy a box in a flex row and still be reachable as a node.
    await expect(page.locator('#mark-confirmed')).toHaveCount(0);
    await expect(page.locator('#mark-confirmed [role="img"]')).toHaveCount(0);

    // And the word never reaches the page from this component.
    await expect(page.locator('body')).not.toContainText('Confirmed');
});

test('the three exception states each render word and glyph', async ({ page }) => {
    await open(page);

    const cases: [string, string][] = [
        ['#mark-calculated', 'Calculated'],
        ['#mark-estimated', 'Estimated'],
        ['#mark-unknown', 'Unknown'],
    ];

    for (const [selector, word] of cases) {
        const mark = page.locator(`${selector} [role="img"]`);
        await expect(mark).toBeVisible();

        // The word is the accessible name, so a screen reader hears the state rather than the glyph.
        await expect(mark).toHaveAttribute('aria-label', word);
        // And the visible text carries the word beside the glyph, never the glyph alone.
        await expect(mark).toContainText(word);
    }
});

test('keyboard focus reveals the reason, and the reason is in the name either way', async ({ page }) => {
    await open(page);

    const host = page.locator('#mark-reason');
    const mark = host.locator('[role="img"]');

    // The reason is already part of the accessible name before anything is opened.
    await expect(mark).toHaveAttribute('aria-label', 'Estimated: Averaged across three recorded turns.');

    // Not visible until the reader reaches it.
    await expect(host.locator('[role="tooltip"]')).toHaveCount(0);

    // Revealed by keyboard, with no pointer involved. This is the assertion hover-only styling fails.
    await mark.focus();
    await expect(host.locator('[role="tooltip"]')).toBeVisible();
    await expect(host.locator('[role="tooltip"]')).toHaveText('Averaged across three recorded turns.');

    // And hidden again when focus leaves.
    await page.locator('h1').focus().catch(() => undefined);
    await mark.blur();
    await expect(host.locator('[role="tooltip"]')).toHaveCount(0);
});

test('hover reveals the reason too', async ({ page }) => {
    await open(page);

    const host = page.locator('#mark-reason');
    await host.locator('[role="img"]').hover();
    await expect(host.locator('[role="tooltip"]')).toBeVisible();
});
