import { test, expect } from './support/base-test';
import { createServer, type ViteDevServer } from 'vite';
import vue from '@vitejs/plugin-vue';
import path from 'node:path';
import { fileURLToPath } from 'node:url';

/*
 * StatBadge (stat-colour Slice B). In-spec Vite fixture: no `public/build`, no `public/hot` (KI-70).
 *
 * The component's contract: both variants resolve their token classes (the maps are whole-string,
 * per the Tailwind v4 source-scan rule), all seven stat keys map, the value renders when present
 * and the label always does (the word carries the meaning; the colour never does alone), and the
 * two stats without tint/line tokens fall back to the neutral chip rather than borrowing another
 * stat's identity.
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
        server: { host: '127.0.0.1', port: 5219, strictPort: false, watch: null, hmr: false },
    });
    await server.listen();
    base = server.resolvedUrls!.local[0]!;
});

test.afterAll(async () => {
    await server.close();
});

async function open(page: import('@playwright/test').Page): Promise<void> {
    await page.goto(`${base}stat-badge.html`);
    await page.locator('#fixture-main').waitFor();
}

const STATS = ['speed', 'stamina', 'power', 'guts', 'wit', 'pal', 'group'] as const;

test('renders all seven stats in the tint variant with their token classes', async ({ page }) => {
    await open(page);

    const expected: Record<(typeof STATS)[number], string[]> = {
        speed: ['bg-tint-speed', 'text-stat-speed-ink', 'border-line-speed'],
        stamina: ['bg-tint-stamina', 'text-stat-stamina-ink', 'border-line-stamina'],
        power: ['bg-tint-power', 'text-stat-power-ink', 'border-line-power'],
        guts: ['bg-tint-guts', 'text-stat-guts-ink', 'border-line-guts'],
        wit: ['bg-tint-wit', 'text-stat-wit-ink', 'border-line-wit'],
        pal: ['bg-sunken', 'border-rule'],
        group: ['bg-sunken', 'border-rule'],
    };

    for (const stat of STATS) {
        const badge = page.locator(`#badge-tint-${stat}`);
        await expect(badge).toBeVisible();
        const cls = await badge.getAttribute('class');
        for (const token of expected[stat]) {
            expect(cls, `${stat} carries ${token}`).toContain(token);
        }
        await expect(badge).toContainText(stat[0].toUpperCase() + stat.slice(1));
    }
});

test('renders the value when passed and omits the value slot when not', async ({ page }) => {
    await open(page);

    await expect(page.locator('#badge-value-speed')).toContainText('1,200'.replace(',', ''));
    // The fixture passes the number 1200; the badge prints it verbatim.
    await expect(page.locator('#badge-value-speed')).toContainText('1200');

    // Label-only chips carry exactly one text node group: the label word, no value span.
    const bare = page.locator('#badge-tint-speed');
    await expect(bare).toContainText('Speed');
});

test('plain variant carries the ink tier and no tint/border family', async ({ page }) => {
    await open(page);

    const plain = page.locator('#badge-plain-speed');
    const cls = await plain.getAttribute('class');
    expect(cls).toContain('text-stat-speed-ink');
    expect(cls).not.toContain('bg-tint-speed');
    expect(cls).not.toContain('border-line-speed');

    const plainPal = page.locator('#badge-plain-pal');
    expect(await plainPal.getAttribute('class')).toContain('text-stat-pal-ink');
});

test('a string value renders verbatim', async ({ page }) => {
    await open(page);

    await expect(page.locator('#badge-string-guts')).toContainText('not recorded');
});
