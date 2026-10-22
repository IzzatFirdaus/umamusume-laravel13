#!/usr/bin/env node
/*
 * Screenshot diff against app routes (plan Phase 1).
 *
 * Targets the application, not a `file://` set page: `shell.css` and `app.css` have
 * separate token namespaces, so a set baseline cannot observe an app-side change.
 *
 * Usage:
 *   node tools/ui-screenshot-diff.mjs --write primitives
 *   node tools/ui-screenshot-diff.mjs --check primitives
 *
 * URLs: UI_DIFF_BASE_URL (default http://127.0.0.1:8000), UI_DIFF_URLS (default /).
 * Snapshots: UI_DIFF_DIR or .scratch-uma/<session>/snap. An absent Playwright install
 * exits 2 with UNVERIFIED, never 0.
 */
import { existsSync, mkdirSync, readFileSync, writeFileSync } from 'node:fs';
import { join } from 'node:path';

const args = process.argv.slice(2);
const mode = args[0];
const target = args[1] ?? 'primitives';
if (!['--write', '--check'].includes(mode)) {
    console.error('usage: ui-screenshot-diff.mjs --write|--check <target>');
    process.exit(1);
}

let chromium;
try {
    ({ chromium } = await import('playwright'));
} catch {
    console.log('=== UNVERIFIED ===');
    console.log('playwright is not installed: no screenshot could be taken.');
    process.exit(2);
}

const BASE = process.env.UI_DIFF_BASE_URL ?? 'http://127.0.0.1:8000';
const urls = (process.env.UI_DIFF_URLS ?? '/').split(',').map((s) => s.trim()).filter(Boolean);
const DIR = process.env.UI_DIFF_DIR ?? join('.scratch-uma', 'snap');
mkdirSync(DIR, { recursive: true });

let browser;
try {
    browser = await chromium.launch();
} catch (e) {
    console.log('=== UNVERIFIED ===');
    console.log(`Chromium could not launch (${e.message}).`);
    process.exit(2);
}

const page = await browser.newPage({ viewport: { width: 1440, height: 900 } });
const fails = [];

for (const url of urls) {
    const name = `${target}-${url.replace(/[^a-z0-9]+/gi, '-').replace(/^-|-$/g, '') || 'root'}.png`;
    const path = join(DIR, name);
    await page.goto(BASE + url, { waitUntil: 'networkidle' });
    const png = await page.screenshot({ fullPage: true });

    if (mode === '--write') {
        writeFileSync(path, png);
        console.log(`wrote ${path}`);
        continue;
    }

    if (!existsSync(path)) {
        fails.push(`${url}: no baseline at ${path}`);
        continue;
    }
    if (!readFileSync(path).equals(png)) fails.push(`${url}: differs from ${path}`);
}

await browser.close();

if (fails.length === 0) {
    console.log(`ui-screenshot-diff: ${mode === '--write' ? 'wrote' : 'clean'} ${urls.length} page(s)`);
    process.exit(0);
}
console.error(`ui-screenshot-diff: ${fails.length} difference(s)`);
for (const f of fails) console.error(`  ${f}`);
process.exit(1);
