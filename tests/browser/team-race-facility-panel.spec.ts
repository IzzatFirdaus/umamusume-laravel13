import { test, expect } from '@playwright/test';
import { createServer, type ViteDevServer } from 'vite';
import vue from '@vitejs/plugin-vue';
import path from 'node:path';
import { fileURLToPath } from 'node:url';
import { buildAxe } from '../utils/accessibility';

/*
 * TeamRaceFacilityPanel: isolated component fixture (plan A.1 / Direction 2).
 *
 * The panel is a dead registration (SCREEN_SPEC.md:3892-3901 item 24, pending owner ruling)
 * — registered against the `team_race_facility` flag in ScenarioPanel.vue:79, but never
 * composed by any scenario's `panels` block in config/scenarios.php and carrying no backend
 * `facilities` payload in ScenarioSectionBuilder.
 *
 * This spec pins the component's markup (card boundary, CapsuleHeader with .lattice-bleed,
 * and preservation of per-facility h3 headings) using the in-spec Vite fixture pattern that
 * sub-cell.spec.ts and card-section.spec.ts already use.
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
            port: 5218,
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

async function openFixture(page: import('@playwright/test').Page): Promise<void> {
    await page.goto(`${base}team-race-facility-panel.html`);
    await page.locator('#fixture-main').waitFor();
}

test('wraps the facility panel in .card and renders the capsule header with .lattice-bleed while preserving h3 groups', async ({ page }) => {
    await openFixture(page);

    const root = page.locator('#fixture-main > div');

    // The root boundary must be .card (or carry class="card p-3")
    await expect(root).toHaveClass(/\bcard\b/);

    // Capsule header renders the franchise argyle lattice-bleed motif
    const capsule = root.locator('.lattice-bleed');
    await expect(capsule).toHaveCount(1);
    await expect(capsule).toContainText('Team Race Facilities');

    // Preserves per-facility <section> and <h3> headings untouched
    const sections = root.locator('section');
    await expect(sections).toHaveCount(2);

    const headings = root.locator('h3');
    await expect(headings).toHaveCount(2);
    await expect(headings.nth(0)).toHaveText('Track Facility');
    await expect(headings.nth(1)).toHaveText('Training Facility');
});

test('passes the axe scan on the isolated mount', async ({ page }) => {
    await openFixture(page);

    const results = await buildAxe(page).analyze();
    expect(results.violations, JSON.stringify(results.violations, null, 2)).toEqual([]);
});
