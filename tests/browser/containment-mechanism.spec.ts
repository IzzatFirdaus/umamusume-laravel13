import { test, expect } from '@playwright/test';
import { createServer, type ViteDevServer } from 'vite';
import vue from '@vitejs/plugin-vue';
import path from 'node:path';
import { fileURLToPath } from 'node:url';

/*
 * Does `container-type: inline-size` on an ancestor make `position: sticky` a no-op?
 *
 * The containment retirement at 1150825 asserted that it does, on the strength of a computed-style
 * reading plus an ancestor walk in the app. That is a CORRELATION, not a proof: the app has many
 * ancestors and only one was singled out. This fixture is the controlled version - two containers,
 * identical but for that one declaration, both scrolling with the page.
 *
 * MEASUREMENT FIRST, in this file's first revision: the numbers are logged before any assertion is
 * written, because the assertion has to encode what the browser actually does rather than what the
 * spec prose says it should.
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
        server: { host: '127.0.0.1', port: 5201, strictPort: false, watch: null, hmr: false },
    });
    await server.listen();
    base = server.resolvedUrls!.local[0]!;
});

test.afterAll(async () => {
    await server.close();
});

test('container-type on an ancestor makes a descendant sticky element a no-op', async ({ page }) => {
    await page.setViewportSize({ width: 800, height: 600 });
    await page.goto(`${base}containment.html`);

    const driftOf = async (selector: string): Promise<number> => {
        await page.evaluate(() => window.scrollTo(0, 0));
        await page.waitForTimeout(50);
        const before = await page.locator(selector).evaluate((el) => el.getBoundingClientRect().top);
        await page.evaluate(() => window.scrollTo(0, 200));
        await page.waitForTimeout(50);
        const after = await page.locator(selector).evaluate((el) => el.getBoundingClientRect().top);
        return before - after;
    };

    const control = await driftOf('#sticky-control');
    const contained = await driftOf('#sticky-contained');
    const scrollY = await page.evaluate(() => window.scrollY);

    // The scroll has to have happened, or neither number means anything.
    expect(scrollY, 'the fixture did not scroll').toBeGreaterThanOrEqual(198);

    // The two halves must differ ONLY by the parent's `container-type`, and both children must actually
    // be sticky - otherwise this measures a different question than the one it claims.
    const computed = await page.evaluate(() => {
        const c = document.querySelector('.control')!;
        const d = document.querySelector('.contained')!;
        return {
            controlContainerType: getComputedStyle(c).containerType,
            containedContainerType: getComputedStyle(d).containerType,
            controlSticky: getComputedStyle(c.querySelector('.sticky-child')!).position,
            containedSticky: getComputedStyle(d.querySelector('.sticky-child')!).position,
        };
    });

    expect(computed.controlSticky).toBe('sticky');
    expect(computed.containedSticky).toBe('sticky');
    expect(computed.controlContainerType).toBe('normal');
    expect(computed.containedContainerType).toBe('inline-size');

    console.log('FIXTURE-CONTROL-DRIFT=' + control);
    console.log('FIXTURE-CONTAINED-DRIFT=' + contained);

    // Sticky WORKS with no containment: the control pins and drifts by nothing.
    expect(control, `the control drifted ${control}px, so this fixture cannot distinguish the two states`).toBeLessThanOrEqual(2);

    // And it is a NO-OP under `container-type: inline-size`: the child drifts by the whole scroll.
    expect(
        contained,
        `the contained child drifted ${contained}px; if this is small, container-type did NOT break sticky and the retirement premise is wrong`,
    ).toBeGreaterThanOrEqual(190);
});
