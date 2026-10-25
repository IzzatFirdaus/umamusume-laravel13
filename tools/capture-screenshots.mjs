#!/usr/bin/env node
/**
 * Screenshot Capture Script
 *
 * Captures screenshots of all frontend pages at localhost:8000
 * Saves to docs/design-research/screenshots/
 *
 * Usage: node tools/capture-screenshots.mjs
 * Requires: php artisan serve running on port 8000
 */

import { execSync } from 'node:child_process';
import { mkdirSync, existsSync, writeFileSync } from 'node:fs';
import { join, resolve } from 'node:path';
import { chromium } from '@playwright/test';
import http from 'node:http';

const REPO_ROOT = resolve(import.meta.dirname, '..');
const SCREENSHOTS_DIR = join(REPO_ROOT, 'docs', 'design-research', 'screenshots');
const BASE_URL = 'http://localhost:8000';
const VIEWPORTS = [
    { name: 'mobile', width: 390, height: 844 },
    { name: 'tablet', width: 768, height: 1024 },
    { name: 'desktop', width: 1280, height: 720 },
    { name: 'desktop-wide', width: 1920, height: 1080 },
];

function checkServer() {
    return new Promise((resolve) => {
        const req = http.get(`${BASE_URL}/up`, (res) => {
            resolve(res.statusCode === 200);
        });
        req.on('error', () => resolve(false));
        req.setTimeout(3000, () => {
            req.destroy();
            resolve(false);
        });
    });
}

// Route definitions matching routes/web.php
// Format: { path, name, requiresAuth, setupFn }
const ROUTES = [
    // Public / no-data routes
    { path: '/', name: 'home', description: 'Dashboard (empty state)' },
    { path: '/career/setup/scenario', name: 'career.scenario', description: 'Career Setup - Scenario Select' },
    { path: '/career/setup/trainee', name: 'career.trainee', description: 'Career Setup - Trainee Select' },
    { path: '/career/setup/target', name: 'career.target', description: 'Career Setup - Build Target' },
    { path: '/career/setup/legacy', name: 'career.legacy', description: 'Career Setup - Legacy Select' },
    { path: '/career/setup/deck', name: 'career.deck', description: 'Career Setup - Deck Select' },
    { path: '/career/setup/preflight', name: 'career.preflight', description: 'Career Setup - Preflight' },
    { path: '/career/snapshot', name: 'career.snapshot', description: 'Career Snapshot Entry' },
    { path: '/career/snapshot/setup', name: 'career.snapshot.setup', description: 'Career Snapshot Setup' },
    { path: '/career/snapshot/review', name: 'career.snapshot.review', description: 'Career Snapshot Review' },

    // Catalog routes
    { path: '/umamusume', name: 'catalog.index', description: 'Trainee Catalog Index' },
    { path: '/skills', name: 'skills.index', description: 'Skills Index' },
    { path: '/support-cards', name: 'support-cards.index', description: 'Support Cards Index' },

    // Training Runs
    { path: '/training-runs', name: 'runs.index', description: 'Training Runs Index' },
    { path: '/training-runs/create', name: 'runs.create', description: 'Create Training Run' },
    { path: '/training-runs/import', name: 'runs.import', description: 'Import Training Run' },

    // Review
    { path: '/review', name: 'review.index', description: 'Review Queue' },

    // Legacy Lab
    { path: '/legacy', name: 'legacy.index', description: 'Legacy Lab Index' },
    { path: '/legacy/compare', name: 'legacy.compare', description: 'Legacy Compare' },

    // Veterans
    { path: '/veterans', name: 'veterans.index', description: 'Veterans Library' },
    { path: '/veterans/compare', name: 'veterans.compare', description: 'Veterans Compare' },

    // Preferences
    { path: '/preferences', name: 'preferences.edit', description: 'Preferences' },

    // Database Hub
    { path: '/database', name: 'database.index', description: 'Database Hub' },
    { path: '/database/trainees', name: 'database.trainees', description: 'Database Trainees' },
    { path: '/database/supports', name: 'database.supports', description: 'Database Supports' },
    { path: '/database/skills', name: 'database.skills', description: 'Database Skills' },
    { path: '/database/races', name: 'database.races', description: 'Database Races' },
    { path: '/database/scenarios', name: 'database.scenarios', description: 'Database Scenarios' },
    { path: '/database/events', name: 'database.events', description: 'Database Events' },
    { path: '/database/shop-items', name: 'database.shop-items', description: 'Database Shop Items' },
    { path: '/database/sparks', name: 'database.sparks', description: 'Database Sparks' },
    { path: '/database/sources', name: 'database.sources', description: 'Database Sources' },

    // Dynamic routes that need a run ID - these will be skipped or need a run created first
    // { path: '/training-runs/{run}/cockpit', name: 'runs.cockpit', description: 'Career Cockpit' },
    // { path: '/training-runs/{run}/races', name: 'runs.races.decision', description: 'Race Decision' },
    // { path: '/training-runs/{run}/races/planner', name: 'runs.races.planner', description: 'Race Planner' },
    // { path: '/training-runs/{run}/skills', name: 'runs.skills.planner', description: 'Skills Planner' },
    // { path: '/training-runs/{run}/training', name: 'runs.training', description: 'Training Decision' },
    // { path: '/training-runs/{run}/inheritance', name: 'runs.inheritance', description: 'Inheritance Event' },
    // { path: '/training-runs/{run}/events', name: 'runs.events.decision', description: 'Event Decision' },
    // { path: '/training-runs/{run}/timeline', name: 'runs.timeline', description: 'Career Timeline' },
    // { path: '/training-runs/{run}/result', name: 'runs.result', description: 'Career Result' },
    // { path: '/training-runs/{run}/deck', name: 'runs.deck', description: 'Deck Builder' },
    // { path: '/training-runs/{run}/veteran', name: 'runs.veteran', description: 'Save Veteran' },
    // { path: '/legacy/{run}', name: 'legacy.builder', description: 'Legacy Builder' },
    // { path: '/veterans/{veteran}', name: 'veterans.show', description: 'Veteran Detail' },
    // { path: '/umamusume/{slug}', name: 'catalog.show', description: 'Trainee Detail' },
    // { path: '/skills/{skill}', name: 'skills.show', description: 'Skill Detail' },
    // { path: '/support-cards/{card}', name: 'support-cards.show', description: 'Support Card Detail' },
];

async function waitForAppReady(page) {
    await page.locator('#app > *').first().waitFor({ timeout: 15000 });
}

async function captureScreenshot(page, route, viewport, runId = null) {
    const url = runId ? route.path.replace('{run}', runId) : route.path;
    const fullUrl = `${BASE_URL}${url}`;

    try {
        console.log(`  Navigating to ${fullUrl}...`);
        await page.goto(fullUrl, { waitUntil: 'domcontentloaded', timeout: 30000 });
        await waitForAppReady(page);

        // Wait for any animations to settle
        await page.waitForTimeout(500);

        const filename = `${route.name}-${viewport.name}.png`;
        const filepath = join(SCREENSHOTS_DIR, filename);

        await page.screenshot({ path: filepath, fullPage: true });
        console.log(`  ✓ Saved: ${filename}`);
        return { success: true, filename, path: filepath };
    } catch (error) {
        console.error(`  ✗ Failed: ${route.path} (${viewport.name}) - ${error.message}`);
        return { success: false, error: error.message };
    }
}

async function createTestRun(page) {
    console.log('Creating a test run for dynamic routes...');
    try {
        await page.goto(`${BASE_URL}/training-runs/create`, { waitUntil: 'domcontentloaded' });
        await waitForAppReady(page);

        // Fill trainee combobox
        await page.locator('#trainee-combobox').fill('Agnes Digital');
        await page.keyboard.press('Enter');

        // Select scenario
        await page.selectOption('select[name="scenario"]', { value: 'unity_cup' });

        // Click create
        await page.getByRole('button', { name: 'Create run' }).click();

        // Wait for redirect to cockpit
        await page.waitForURL(/\/training-runs\/\d+\/cockpit$/, { timeout: 15000 });

        const url = page.url();
        const match = url.match(/\/training-runs\/(\d+)/);
        if (match) {
            const runId = match[1];
            console.log(`  Created run ID: ${runId}`);
            return runId;
        }
        throw new Error('Could not extract run ID from URL');
    } catch (error) {
        console.error(`  Failed to create test run: ${error.message}`);
        return null;
    }
}

async function main() {
    console.log('╔══════════════════════════════════════════════════════════════╗');
    console.log('║  Frontend Screenshot Capture                                  ║');
    console.log('║  Target: http://localhost:8000                                ║');
    console.log('║  Output: docs/design-research/screenshots/                    ║');
    console.log('╚══════════════════════════════════════════════════════════════╝');
    console.log('');

    // Ensure output directory exists
    if (!existsSync(SCREENSHOTS_DIR)) {
        mkdirSync(SCREENSHOTS_DIR, { recursive: true });
        console.log(`Created directory: ${SCREENSHOTS_DIR}`);
    }

    // Check if server is running
    const serverRunning = await checkServer();
    if (serverRunning) {
        console.log('✓ Server is running at http://localhost:8000');
    } else {
        console.error('✗ Server not responding at http://localhost:8000');
        console.error('  Please run: php artisan serve --port=8000');
        process.exit(1);
    }

    // Launch browser
    console.log('\nLaunching browser...');
    const browser = await chromium.launch({ headless: true });
    const context = await browser.newContext();
    const page = await context.newPage();

    // Set default timeout
    page.setDefaultTimeout(30000);

    const results = {
        successful: [],
        failed: [],
        skipped: [],
    };

    // Create a test run for dynamic routes
    const runId = await createTestRun(page);

    // Capture screenshots for each route and viewport
    console.log(`\nCapturing screenshots for ${ROUTES.length} routes × ${VIEWPORTS.length} viewports...`);
    console.log('');

    for (const route of ROUTES) {
        console.log(`📄 ${route.description} (${route.path})`);

        for (const viewport of VIEWPORTS) {
            await page.setViewportSize({ width: viewport.width, height: viewport.height });

            const result = await captureScreenshot(page, route, viewport, runId);

            if (result.success) {
                results.successful.push({
                    route: route.name,
                    path: route.path,
                    viewport: viewport.name,
                    filename: result.filename,
                });
            } else {
                results.failed.push({
                    route: route.name,
                    path: route.path,
                    viewport: viewport.name,
                    error: result.error,
                });
            }
        }
    }

    // Also capture dynamic routes with the run ID
    if (runId) {
        const dynamicRoutes = [
            { path: `/training-runs/${runId}/cockpit`, name: 'runs.cockpit', description: 'Career Cockpit' },
            { path: `/training-runs/${runId}/races`, name: 'runs.races.decision', description: 'Race Decision' },
            { path: `/training-runs/${runId}/races/planner`, name: 'runs.races.planner', description: 'Race Planner' },
            { path: `/training-runs/${runId}/skills`, name: 'runs.skills.planner', description: 'Skills Planner' },
            { path: `/training-runs/${runId}/training`, name: 'runs.training', description: 'Training Decision' },
            { path: `/training-runs/${runId}/inheritance`, name: 'runs.inheritance', description: 'Inheritance Event' },
            { path: `/training-runs/${runId}/events`, name: 'runs.events.decision', description: 'Event Decision' },
            { path: `/training-runs/${runId}/timeline`, name: 'runs.timeline', description: 'Career Timeline' },
            { path: `/training-runs/${runId}/result`, name: 'runs.result', description: 'Career Result' },
            { path: `/training-runs/${runId}/deck`, name: 'runs.deck', description: 'Deck Builder' },
            { path: `/training-runs/${runId}/veteran`, name: 'runs.veteran', description: 'Save Veteran' },
            { path: `/legacy/${runId}`, name: 'legacy.builder', description: 'Legacy Builder' },
        ];

        console.log(`\n📄 Capturing ${dynamicRoutes.length} dynamic routes with run ID ${runId}...`);

        for (const route of dynamicRoutes) {
            console.log(`  ${route.description} (${route.path})`);

            for (const viewport of VIEWPORTS) {
                await page.setViewportSize({ width: viewport.width, height: viewport.height });

                const result = await captureScreenshot(page, route, viewport);

                if (result.success) {
                    results.successful.push({
                        route: route.name,
                        path: route.path,
                        viewport: viewport.name,
                        filename: result.filename,
                    });
                } else {
                    results.failed.push({
                        route: route.name,
                        path: route.path,
                        viewport: viewport.name,
                        error: result.error,
                    });
                }
            }
        }
    }

    // Try to capture a few detail pages
    console.log('\n📄 Capturing detail pages...');
    const detailRoutes = [
        { path: '/umamusume/agnes-digital', name: 'catalog.show', description: 'Trainee Detail (Agnes Digital)' },
        { path: '/skills/1', name: 'skills.show', description: 'Skill Detail' },
        { path: '/support-cards/1', name: 'support-cards.show', description: 'Support Card Detail' },
        { path: '/veterans/1', name: 'veterans.show', description: 'Veteran Detail' },
    ];

    for (const route of detailRoutes) {
        console.log(`  ${route.description} (${route.path})`);

        for (const viewport of VIEWPORTS) {
            await page.setViewportSize({ width: viewport.width, height: viewport.height });

            const result = await captureScreenshot(page, route, viewport);

            if (result.success) {
                results.successful.push({
                    route: route.name,
                    path: route.path,
                    viewport: viewport.name,
                    filename: result.filename,
                });
            } else {
                // Detail pages might 404 if data doesn't exist - mark as skipped
                results.skipped.push({
                    route: route.name,
                    path: route.path,
                    viewport: viewport.name,
                    reason: result.error,
                });
            }
        }
    }

    await browser.close();

    // Generate summary report
    const dynamicRoutesCount = runId ? 12 : 0; // Number of dynamic routes we capture
    const report = {
        timestamp: new Date().toISOString(),
        baseUrl: BASE_URL,
        viewports: VIEWPORTS.map(v => v.name),
        totalRoutes: ROUTES.length + dynamicRoutesCount + detailRoutes.length,
        successful: results.successful.length,
        failed: results.failed.length,
        skipped: results.skipped.length,
        results: results,
    };

    const reportPath = join(SCREENSHOTS_DIR, 'capture-report.json');
    writeFileSync(reportPath, JSON.stringify(report, null, 2));

    // Print summary
    console.log('\n╔══════════════════════════════════════════════════════════════╗');
    console.log('║  Capture Complete                                             ║');
    console.log('╚══════════════════════════════════════════════════════════════╝');
    console.log(`✓ Successful: ${results.successful.length}`);
    console.log(`✗ Failed:     ${results.failed.length}`);
    console.log(`⊘ Skipped:    ${results.skipped.length}`);
    console.log(`\nReport saved to: ${reportPath}`);
    console.log(`Screenshots saved to: ${SCREENSHOTS_DIR}`);

    if (results.failed.length > 0) {
        console.log('\nFailed captures:');
        for (const f of results.failed) {
            console.log(`  - ${f.path} (${f.viewport}): ${f.error}`);
        }
    }

    process.exit(results.failed.length > 0 ? 1 : 0);
}

main().catch(console.error);