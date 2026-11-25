import { expect, test } from '@playwright/test';
import { buildAxe, openAllDisclosures } from '../utils/accessibility';

/*
 * N6.17 — Database/Help theme and reflow verification.
 *
 * The historical target is ten areas (the nine Database areas plus Help) x three themes
 * (`light`/`dark`/`eva01`) x two viewports (320/1280) = 60 cells. This file is that matrix,
 * following `four-screen-ux.spec.ts`: one case per route, the viewport and theme swept inside
 * the case through `test.step`, so a red cell names its own route, theme and width.
 *
 * What is reused rather than re-asserted:
 *   - the persisted-preference path is exercised once, below, through the real Preferences Save;
 *     the sweep sets `html[data-theme]` the same way `four-screen-ux.spec.ts` does, because the
 *     palette lives in CSS keyed on that attribute and no reload is needed to render it;
 *   - per-route axe scans already exist in `database.spec.ts` for eight areas and the hub; this
 *     matrix carries the scan per route/theme/viewport so `/database/trainees` and `/help` get
 *     their first scans and the rest are read at both widths under all three palettes;
 *   - the hub itself (`/database`) is not one of the nine areas and keeps its coverage in
 *     `database.spec.ts`.
 *
 * The gate per cell: the page's own `h1` stays visible, `main` stays rendered, the document does
 * not scroll sideways, a theme token resolves, and the WCAG A/AA scan (`buildAxe` scopes to
 * `#app`; `openAllDisclosures` opens every `<details>`) reports no violations.
 */

const ROUTES = [
    { path: '/database/trainees', heading: 'Trainees' },
    { path: '/database/supports', heading: 'Support Cards' },
    { path: '/database/skills', heading: 'Skills' },
    { path: '/database/races', heading: 'Race catalog' },
    { path: '/database/events', heading: 'Event catalog' },
    { path: '/database/scenarios', heading: 'Scenario matrix' },
    { path: '/database/shop-items', heading: 'Pro Shop catalogue' },
    { path: '/database/sparks', heading: 'Spark families and grades' },
    { path: '/database/sources', heading: 'Where every figure came from' },
    { path: '/help', heading: 'How this works' },
] as const;

const THEMES = ['light', 'dark', 'eva01'] as const;
const VIEWPORTS = [1280, 320] as const;

for (const route of ROUTES) {
    test(`${route.path} holds its heading and an axe-clean render in three themes at 1280 and 320`, async ({ page }) => {
        await page.goto(route.path, { waitUntil: 'domcontentloaded' });
        await page.locator('#app > *').first().waitFor();

        const heading = page.getByRole('heading', { name: route.heading, level: 1 });
        await expect(heading).toBeVisible();
        await expect(page.getByRole('main')).toBeVisible();

        for (const width of VIEWPORTS) {
            await page.setViewportSize({ width, height: 900 });

            for (const theme of THEMES) {
                await test.step(`${theme} at ${width}px`, async () => {
                    await page.evaluate(
                        (value) => document.documentElement.setAttribute('data-theme', value),
                        theme,
                    );
                    await expect(page.locator('html')).toHaveAttribute('data-theme', theme);

                    // The palette is token-driven; an empty custom property means the sheet never
                    // resolved, which is a different failure from a wrong colour.
                    const token = await page.evaluate(
                        () => getComputedStyle(document.documentElement).getPropertyValue('--color-panel').trim(),
                    );
                    expect(token, `${route.path}: --color-panel did not resolve in ${theme}`).not.toBe('');

                    await expect(heading).toBeVisible();
                    await expect(page.getByRole('main')).toBeVisible();

                    const overflow = await page.evaluate(
                        () => document.documentElement.scrollWidth - document.documentElement.clientWidth,
                    );
                    expect(
                        overflow,
                        `${route.path} scrolls the document sideways at ${width}px in ${theme}`,
                    ).toBeLessThanOrEqual(1);

                    await openAllDisclosures(page);
                    const { violations } = await buildAxe(page).analyze();
                    expect(
                        violations.map((item) => ({
                            id: item.id,
                            nodes: item.nodes.map((node) => ({ target: node.target, summary: node.failureSummary })),
                        })),
                        `${route.path} fails axe in ${theme} at ${width}px`,
                    ).toEqual([]);
                });
            }
        }
    });
}

/*
 * The sweep above exercises the palette attribute; this case proves the persisted half through
 * the app's own control. Each theme is saved on `/preferences`, the page is reloaded, and the
 * attribute the server printed is read back — a stored row, not a client-side shortcut. The store
 * is left on "Follow the system" so the shared database is as the suite found it.
 */
test('the three palettes persist through a real Preferences save', async ({ page }) => {
    await page.goto('/preferences', { waitUntil: 'domcontentloaded' });
    await page.locator('#app > *').first().waitFor();

    const options = [
        ['field-theme_light', 'light'],
        ['field-theme_dark', 'dark'],
        ['field-theme_eva01', 'eva01'],
    ] as const;

    for (const [control, theme] of options) {
        await page.locator(`#${control}`).check();
        await expect(page.getByRole('button', { name: 'Save changes' })).toBeEnabled();
        await page.getByRole('button', { name: 'Save changes' }).click();
        await expect(page.getByRole('status').filter({ hasText: 'Preferences saved.' })).toBeVisible();

        await page.reload({ waitUntil: 'domcontentloaded' });
        await page.locator('#app > *').first().waitFor();
        await expect(page.locator('html'), `${theme} did not survive the reload`).toHaveAttribute('data-theme', theme);
    }

    // The saved theme reaches a Database area and Help on first paint, not only Preferences.
    await page.goto('/database/sources', { waitUntil: 'domcontentloaded' });
    await page.locator('#app > *').first().waitFor();
    await expect(page.getByRole('heading', { name: 'Where every figure came from', level: 1 })).toBeVisible();
    await expect(page.locator('html')).toHaveAttribute('data-theme', 'eva01');

    await page.goto('/help', { waitUntil: 'domcontentloaded' });
    await page.locator('#app > *').first().waitFor();
    await expect(page.getByRole('heading', { name: 'How this works', level: 1 })).toBeVisible();
    await expect(page.locator('html')).toHaveAttribute('data-theme', 'eva01');

    await page.goto('/preferences', { waitUntil: 'domcontentloaded' });
    await page.locator('#app > *').first().waitFor();
    await page.locator('#field-theme_system').check();
    await page.getByRole('button', { name: 'Save changes' }).click();
    await expect(page.getByRole('status').filter({ hasText: 'Preferences saved.' })).toBeVisible();
});
