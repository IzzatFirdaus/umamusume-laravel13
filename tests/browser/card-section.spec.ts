import { test, expect } from '@playwright/test';
import { createServer, type ViteDevServer } from 'vite';
import vue from '@vitejs/plugin-vue';
import path from 'node:path';
import { fileURLToPath } from 'node:url';

/*
 * Card and Section (plan 1.4). A component-level browser case for the reason `base-select.spec.ts`
 * gives: `configFile: false` means the fixture server never loads `vite.config.js`, never writes
 * `public/hot` and never reads `public/build`, so this runs while the peer holds uncommitted files
 * and `npm run build` is withheld (KI-70).
 *
 * The gate is the rendered class list, not computed style. With no config there is no Tailwind
 * pipeline, so `getComputedStyle` reads the browser default for every element and would pass against
 * a card carrying no classes at all. What the component owns is the utility set it renders, and that
 * is exactly what is asserted. The tokens themselves are gated by `tests/Feature/DesignTokensTest.php`.
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
            port: 5201,
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

/*
 * `goto` resolves when the document is parsed, not when the module graph has loaded and Vue has
 * mounted: the fixture's `<script type="module">` fetches each SFC over the in-spec Vite server,
 * which on a cold cache is several round trips. Waiting on the mounted node rather than on a timer
 * keeps the first assertion off an empty mount point and stays honest when the cache warms.
 */
async function openFixture(page: import('@playwright/test').Page): Promise<void> {
    await page.goto(`${base}card-section.html`);
    await page.locator('#fixture-main').waitFor();
    // A mount that produced nothing would otherwise fail later as a "missing heading" and read
    // like a component defect. This names the fixture instead.
    await expect(page.locator('#card-section-mount > main')).toHaveCount(1);
}

test('Card is the raised sheet: rule border, medium radius, rounded corner, no shadow', async ({ page }) => {
    await openFixture(page);

    for (const id of ['#card-md', '#card-lg']) {
        const card = page.locator(id);
        await expect(card).toBeVisible();

        const classes = (await card.getAttribute('class'))?.split(/\s+/) ?? [];
        expect(classes).toContain('bg-raised');
        expect(classes).toContain('border');
        expect(classes).toContain('border-rule');
        expect(classes).toContain('rounded-md');

        // No elevation. A static card that casts the overlay shadow claims a layer it does not occupy.
        expect(classes.filter((c) => /^shadow/.test(c))).toEqual([]);
    }

    // The two padding steps are distinct, so the prop is bound rather than ignored.
    await expect(page.locator('#card-md')).toHaveClass(/\bp-4\b/);
    await expect(page.locator('#card-lg')).toHaveClass(/\bp-5\b/);
});

test('Section renders its name as an h2 at the Heading tier and links its description', async ({ page }) => {
    await openFixture(page);

    const heading = page.getByRole('heading', { name: 'Training history', level: 2 });
    await expect(heading).toBeVisible();
    await expect(heading).toHaveClass(/text-lg/);
    await expect(heading).toHaveClass(/font-semibold/);
    await expect(heading).toHaveClass(/text-ink-strong/);

    // The region is a landmark, and its description is associated rather than left as a loose
    // paragraph a screen-reader user may skip.
    const region = page.locator('section[aria-describedby]');
    await expect(region).toHaveCount(1);
    const describedBy = await region.getAttribute('aria-describedby');
    await expect(page.locator(`#${describedBy}`)).toHaveText('Every recorded turn, newest first.');

    // The outline holds: one h1, one h2, and the cards add no headings of their own.
    await expect(page.locator('h1')).toHaveCount(1);
    await expect(page.locator('h2')).toHaveCount(1);
    await expect(page.locator('h3')).toHaveCount(0);
});
