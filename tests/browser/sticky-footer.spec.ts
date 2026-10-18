import { test, expect } from '@playwright/test';
import { createServer, type ViteDevServer } from 'vite';
import vue from '@vitejs/plugin-vue';
import path from 'node:path';
import { fileURLToPath } from 'node:url';

/*
 * StickyFooter (plan 1.10). In-spec Vite fixture: no `public/build`, no `public/hot` (KI-70).
 *
 * The two cases are separated on purpose. "The slots render" is a structural claim that stays true
 * whatever the positioning does; "it sticks" is the claim the component exists for. Removing
 * `position: sticky` must red ONLY the second — if it also reds the first, the cases are measuring the
 * component's existence rather than its behaviour.
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
        server: { host: '127.0.0.1', port: 5208, strictPort: false, watch: null, hmr: false },
    });
    await server.listen();
    base = server.resolvedUrls!.local[0]!;
});

test.afterAll(async () => {
    await server.close();
});

async function open(page: import('@playwright/test').Page): Promise<void> {
    await page.setViewportSize({ width: 1024, height: 700 });
    await page.goto(`${base}sticky-footer.html`);
    await page.locator('#fixture-main').waitFor();
}

test('renders the left and right slots with their own controls', async ({ page }) => {
    await open(page);

    // The fixture supplies both slots, so these are the caller's buttons rather than the defaults.
    await expect(page.locator('#footer #back-btn')).toHaveText('Back');
    await expect(page.locator('#footer #next-btn')).toHaveText('Save and continue');

    // The bar is a footer element with its top rule.
    const footer = page.locator('#footer');
    await expect(footer).toHaveCount(1);
    const border = await footer.evaluate((el) => getComputedStyle(el).borderTopWidth);
    expect(border).toBe('1px');
});

test('sticks to the bottom of the viewport while the content scrolls past it', async ({ page }) => {
    await open(page);

    const footer = page.locator('#footer');

    // Positioned before scrolling.
    const before = await footer.boundingBox();
    expect(before).not.toBeNull();

    // Scrolled to a point where the bar is over the middle of its containing block. Scrolling to the
    // very end of the page is the wrong probe: sticky releases the bar when its CONTAINING BLOCK runs
    // out, so a bar at the bottom of the document is expected to move and proves nothing either way.
    await page.evaluate(() => window.scrollTo(0, 400));
    await page.waitForTimeout(120);

    const after = await footer.boundingBox();
    expect(after).not.toBeNull();

    // The page really moved...
    const scrollY = await page.evaluate(() => window.scrollY);
    expect(scrollY).toBeGreaterThan(0);

    // ...and the footer did not: its distance from the viewport bottom is unchanged.
    const viewport = page.viewportSize()!;
    const bottomBefore = viewport.height - (before!.y + before!.height);
    const bottomAfter = viewport.height - (after!.y + after!.height);
    expect(Math.abs(bottomAfter - bottomBefore)).toBeLessThan(2);
    // It is still on screen, i.e. it is pinned rather than scrolled away.
    expect(after!.y).toBeLessThan(viewport.height);
    expect(after!.y + after!.height).toBeLessThanOrEqual(viewport.height + 1);
});

test('the last focusable field scrolls clear of the bar', async ({ page }) => {
    await open(page);

    // Scroll the last link into view the way a keyboard reader reaching it does.
    await page.locator('#last-link').focus();
    await page.waitForTimeout(120);

    const link = await page.locator('#last-link').boundingBox();
    const footer = await page.locator('#footer').boundingBox();
    expect(link).not.toBeNull();
    expect(footer).not.toBeNull();

    // Not obscured (WCAG 2.4.11): the link's bottom edge is above the bar's top edge.
    expect(link!.y + link!.height).toBeLessThanOrEqual(footer!.y + 1);
});
