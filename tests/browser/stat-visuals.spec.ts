import { test, expect } from './support/base-test';
import { createServer, type ViteDevServer } from 'vite';
import vue from '@vitejs/plugin-vue';
import path from 'node:path';
import { fileURLToPath } from 'node:url';

/*
 * StatRing and Sparkline (master instruction §3.1, §3.3) — the Phase 3 data-visualisation pair.
 *
 * StatRing is an SVG stroke arc, not the conic gradient the instruction named: `DESIGN.md:68` permits
 * a gradient only as a documented motion cue and none is proposed. The spec pins the fraction the
 * dasharray encodes, so a ring that stops matching its own value reds here.
 *
 * Sparkline pins the two edges a polyline gets wrong: a flat series (zero span) and a one-point
 * series, which is not a line and must not draw one.
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
        server: { host: '127.0.0.1', port: 5220, strictPort: false, watch: null, hmr: false },
    });
    await server.listen();
    base = server.resolvedUrls!.local[0]!;
});

test.afterAll(async () => {
    await server.close();
});

async function open(page: import('@playwright/test').Page): Promise<void> {
    await page.goto(`${base}stat-visuals.html`);
    await page.locator('#fixture-main').waitFor();
}

/** Radius 16 on the component's 36 box. */
const CIRCUMFERENCE = 2 * Math.PI * 16;

test('a half-full ring encodes half the circumference and names its reading', async ({ page }) => {
    await open(page);

    const ring = page.locator('#ring-half');
    await expect(ring).toHaveAttribute('role', 'progressbar');
    await expect(ring).toHaveAttribute('aria-valuenow', '600');
    await expect(ring).toHaveAttribute('aria-valuemin', '0');
    await expect(ring).toHaveAttribute('aria-valuemax', '1200');
    await expect(ring).toHaveAttribute('aria-label', 'Speed');

    const arc = ring.locator('circle').nth(1);
    const [filled] = ((await arc.getAttribute('stroke-dasharray')) ?? '').split(' ').map(Number);
    expect(filled).toBeCloseTo(CIRCUMFERENCE / 2, 1);

    // The ink tier the caller passed colours the arc and the number, both through `currentColor`.
    expect(await ring.getAttribute('class')).toContain('text-stat-speed-ink');
    expect(await arc.getAttribute('stroke')).toBe('currentColor');

    // The centre prints the reading, tabular so a row of rings aligns.
    await expect(ring.locator('span')).toHaveText('600');
    expect(await ring.locator('span').getAttribute('class')).toContain('tabular-nums');

    // The track is the idle token, not an invented grey.
    expect(await ring.locator('circle').first().getAttribute('class')).toContain('stroke-idle');
});

test('a value past the cap fills the ring and a zero value draws no arc', async ({ page }) => {
    await open(page);

    const over = ((await page.locator('#ring-over circle').nth(1).getAttribute('stroke-dasharray')) ?? '')
        .split(' ')
        .map(Number);
    expect(over[0]).toBeCloseTo(CIRCUMFERENCE, 1);

    const zero = ((await page.locator('#ring-zero circle').nth(1).getAttribute('stroke-dasharray')) ?? '')
        .split(' ')
        .map(Number);
    expect(zero[0]).toBeCloseTo(0, 1);
});

test('a series draws one point per value, left to right, rising up the box', async ({ page }) => {
    await open(page);

    const svg = page.locator('#spark-rise');
    await expect(svg).toHaveAttribute('aria-hidden', 'true');

    const points = ((await svg.locator('polyline').getAttribute('points')) ?? '')
        .split(' ')
        .map((pair) => pair.split(',').map(Number));

    expect(points).toHaveLength(5);

    for (let i = 1; i < points.length; i += 1) {
        expect(points[i][0]).toBeGreaterThan(points[i - 1][0]);
    }

    // SVG y grows downward: a larger reading sits higher, so y falls.
    expect(points[1][1]).toBeLessThan(points[0][1]);
    expect(points[3][1]).toBeLessThan(points[1][1]);
});

test('a flat series sits on one line instead of dividing by a zero span', async ({ page }) => {
    await open(page);

    const ys = ((await page.locator('#spark-flat polyline').getAttribute('points')) ?? '')
        .split(' ')
        .map((pair) => Number(pair.split(',')[1]));

    expect(new Set(ys).size).toBe(1);
    expect(ys[0]).toBeCloseTo(12, 1);
});

test('a one-point series draws no line', async ({ page }) => {
    await open(page);

    await expect(page.locator('#spark-single')).toHaveCount(1);
    await expect(page.locator('#spark-single polyline')).toHaveCount(0);
});
