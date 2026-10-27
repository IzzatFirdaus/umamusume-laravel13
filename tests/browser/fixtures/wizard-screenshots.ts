import { type Page } from '@playwright/test';
import { mkdirSync } from 'node:fs';
import { resolve } from 'node:path';
import { pathToFileURL } from 'node:url';

/*
 * The wizard-parity screenshot convention (ST-4 visual-parity instruction, Phase 0.3).
 *
 * `page.screenshot()` is local file writing: it needs no MCP client and no vision model. Every parity
 * phase saves both sides at both viewports into the gitignored `.scratch-uma/wizard-parity/` and the
 * agent reads the PNG pairs with the file browser, side by side, for the divergences a structural
 * assertion cannot name.
 *
 * The HFW wizard pages are light-only (no `data-theme` anywhere in the seven files, measured
 * 2026-10-27), so the wireframe side captures one theme. The app's dark theme is asserted structurally
 * by `wizard-parity.spec.ts` rather than captured here.
 */

export const SHOT_DIR = resolve(process.cwd(), '.scratch-uma', 'wizard-parity');

const HFW_DIR = pathToFileURL(resolve(process.cwd(), 'docs', 'design-research', 'frontend-v2-wireframes') + '/').href;

/** Slug → the wireframe file and the app route that renders the same screen. */
export const WIZARD_PAGES: Record<string, { hfw: string; route?: string }> = {
    scenario: { hfw: 'career-scenario', route: '/career/setup/scenario' },
    trainee: { hfw: 'career-trainee', route: '/career/setup/trainee' },
    // `TraineeProfile` needs a trainee id, so its route has no default and the caller supplies one.
    profile: { hfw: 'career-profile' },
    target: { hfw: 'career-target', route: '/career/setup/target' },
    legacy: { hfw: 'career-legacy', route: '/career/setup/legacy' },
    deck: { hfw: 'career-deck', route: '/career/setup/deck' },
    preflight: { hfw: 'career-preflight', route: '/career/setup/preflight' },
};

export type WizardSlug = keyof typeof WIZARD_PAGES;

const VIEWPORTS = [
    { w: 1280, h: 800 },
    { w: 390, h: 844 },
];

export async function saveShot(page: Page, slug: string, side: 'app' | 'hfw', width: number): Promise<void> {
    mkdirSync(SHOT_DIR, { recursive: true });
    await page.screenshot({ path: resolve(SHOT_DIR, `${slug}-${side}-${width}.png`) });
}

export function hfwUrl(slug: WizardSlug): string {
    return `${HFW_DIR}${WIZARD_PAGES[slug].hfw}.html`;
}

export function appRoute(slug: WizardSlug, override?: string): string {
    const route = override ?? WIZARD_PAGES[slug].route;

    if (route === undefined) {
        throw new Error(`captureBoth: ${slug} has no default app route; pass one (it renders a per-record url).`);
    }

    return route;
}

/**
 * Navigate both sides at both viewports and save `<slug>-{app,hfw}-{1280,390}.png`.
 *
 * The app side waits on the Inertia mount (`#app > *`) before the shot, so a screenshot is never
 * taken against the empty shell.
 */
export async function captureBoth(page: Page, slug: WizardSlug, url?: string): Promise<void> {
    const route = appRoute(slug, url);
    const hfw = hfwUrl(slug);

    for (const viewport of VIEWPORTS) {
        await page.setViewportSize({ width: viewport.w, height: viewport.h });

        await page.goto(route);
        await page.locator('#app > *').first().waitFor();
        await saveShot(page, slug, 'app', viewport.w);

        await page.goto(hfw);
        await saveShot(page, slug, 'hfw', viewport.w);
    }
}