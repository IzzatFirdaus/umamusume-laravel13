import { test, expect } from '@playwright/test';
import { createServer, type ViteDevServer } from 'vite';
import vue from '@vitejs/plugin-vue';
import path from 'node:path';
import { fileURLToPath } from 'node:url';

/*
 * The `id` prop on `Section` (operator ruling 2026-10-20, applied in 4.2): a section can be a named
 * landmark.
 *
 * The prop exists because adoption without it is a regression rather than a style choice. The Career
 * Cockpit's five section headers each backed exactly one `aria-labelledby`, so swapping a hand-rolled
 * `<section aria-labelledby="X">` for a component that cannot carry `X` silently drops five landmark
 * names — two of which the browser suite asserts by name. This spec is the primitive's own guard.
 *
 * A component-level browser case for the reason `base-select.spec.ts` gives: the Vite server here runs
 * with `configFile: false`, so it never loads `vite.config.js`, never writes `public/hot` and never
 * touches `public/build`. That makes it runnable while a peer holds uncommitted files and a production
 * build is withheld (KI-70).
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

test('an idless section stays exactly as it was: no landmark, no heading id', async ({ page }) => {
    await page.goto(`${base}section.html`);

    // The prior behaviour, preserved. Measured on the DOM rather than inferred from the template,
    // because this is the shape every existing consumer renders.
    const heading = page.getByRole('heading', { name: 'Plain region', exact: true });
    await expect(heading).toBeVisible();
    await expect(heading).not.toHaveAttribute('id');

    // And the section is NOT a landmark, so it cannot be found by name.
    await expect(page.getByRole('region', { name: 'Plain region' })).toHaveCount(0);
});

test('an id names the landmark: the heading carries it and the section references it', async ({ page }) => {
    await page.goto(`${base}section.html`);

    // The heading carries the id...
    await expect(page.getByRole('heading', { name: 'Named region', exact: true })).toHaveAttribute('id', 'region-one');

    // ...and the section references it, which is what makes the region findable BY NAME. This is the
    // assertion the adoption depends on: without the extension it resolves to nothing.
    await expect(page.getByRole('region', { name: 'Named region', exact: true })).toHaveCount(1);
});

test('the id follows the heading level', async ({ page }) => {
    await page.goto(`${base}section.html`);

    // `level` picks the element, so the id has to land on whichever element that is.
    const heading = page.getByRole('heading', { name: 'Nested region', exact: true, level: 3 });
    await expect(heading).toHaveAttribute('id', 'region-three');
    await expect(page.getByRole('region', { name: 'Nested region', exact: true })).toHaveCount(1);
});

test('a named section still carries its description and its slot', async ({ page }) => {
    await page.goto(`${base}section.html`);

    // The description was already aria-wired; naming the section must not displace it.
    await expect(page.getByText('One line under the name.')).toBeVisible();
    await expect(page.getByRole('region', { name: 'Described region', exact: true })).toHaveCount(1);

    // And the default slot carries markup, because adoption replaces a section's whole body.
    const slotted = page.getByRole('region', { name: 'Slotted region', exact: true });
    await expect(slotted).toHaveCount(1);
    await expect(slotted.locator('#region-slot-list li')).toHaveCount(2);
});
