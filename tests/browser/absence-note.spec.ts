import { test, expect } from '@playwright/test';
import { createServer, type ViteDevServer } from 'vite';
import vue from '@vitejs/plugin-vue';
import path from 'node:path';
import { fileURLToPath } from 'node:url';

/*
 * AbsenceNote (plan 1.11). In-spec Vite fixture: no `public/build`, no `public/hot` (KI-70).
 *
 * The single-label and multi-label cases are separate on purpose: one asserts the line renders, the
 * other asserts it lists. Replacing the join with a comma-less concatenation must red only the second.
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
        server: { host: '127.0.0.1', port: 5212, strictPort: false, watch: null, hmr: false },
    });
    await server.listen();
    base = server.resolvedUrls!.local[0]!;
});

test.afterAll(async () => {
    await server.close();
});

async function open(page: import('@playwright/test').Page): Promise<void> {
    await page.goto(`${base}absence-note.html`);
    await page.locator('#fixture-main').waitFor();
}

test('names a single absence in one sentence', async ({ page }) => {
    await open(page);

    await expect(page.locator('#note-single')).toHaveText('Not recorded: reward.');
});

test('joins several absences with commas so the region says it once', async ({ page }) => {
    await open(page);

    // One line, not three markers. This is the whole point of the component.
    await expect(page.locator('#note-multi')).toHaveText('Not recorded: reward, running style, skill points.');
    await expect(page.locator('#note-multi')).toHaveCount(1);
});

test('renders nothing when every value is present', async ({ page }) => {
    await open(page);

    // An empty list has no absence to describe; a component that printed "Not recorded:" with no
    // list would invent one.
    await expect(page.locator('#note-empty')).toHaveCount(0);

    // Whitespace-only names are not names either.
    await expect(page.locator('#note-blank')).toHaveCount(0);
});
