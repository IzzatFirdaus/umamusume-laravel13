import { test, expect } from '@playwright/test';

/*
 * The shell (HFW `index.html` + `assets/shell.css`). Rendered evidence, so it reads the built bundle
 * and cannot run green before `npm run build` (KI-70).
 *
 * The wireframe is the visual authority for this rewrite (owner ruling 2026-10-26), so what this
 * case pins is the wireframe's own shell rather than the earlier 2.1.0 bar:
 *
 *   1. The bar is the wireframe's blue chrome (#2255FF in light), closed by its 2px pink hairline.
 *   2. It carries the section crumb on the left and the two utilities on the right: the live-career
 *      pill (`1 active career` / `N active careers`) and the `? Help` link.
 *   3. The wireframe's `+ Static wireframe` chip is a document artifact and does not ship.
 *   4. The rail is the wireframe's 236px column, the product name lives in its head, and the current
 *      destination carries `aria-current="page"` plus a left rule.
 *   5. One `h1`, inside the banner band under the bar.
 */

const BAR = '[data-shell-topbar]';
const BLUE = 'rgb(34, 85, 255)'; // --color-brand-chrome, light theme

async function ready(page: import('@playwright/test').Page): Promise<void> {
    await page.goto('/', { waitUntil: 'domcontentloaded' });
    await page.locator('#app > *').first().waitFor();
}

test('the bar is the wireframe blue, 2px hairline, with crumb, career pill and Help', async ({ page }) => {
    await ready(page);

    const bar = page.locator(BAR);
    await expect(bar).toBeVisible();
    await expect(bar).toHaveCSS('background-color', BLUE);
    await expect(bar).toHaveCSS('border-bottom-width', '2px');
    await expect(bar).toHaveCSS('border-bottom-style', 'solid');

    // 1. The crumb names where the Trainer is.
    await expect(bar.locator('[data-shell-crumb]')).toContainText('Dashboard');

    // 2. The two utilities. The pill is present only while a career is running, so the assertion is
    //    "when it renders, it counts careers", not "it is always there".
    const pill = bar.locator('[data-shell-career-pill]');
    if ((await pill.count()) > 0) {
        await expect(pill).toHaveText(/^\d+ active careers?$/);
    }
    await expect(bar.getByRole('link', { name: 'Help' })).toBeVisible();

    // 3. The wireframe's own chip is a document artifact.
    await expect(page.getByText('Static wireframe')).toHaveCount(0);
    await expect(bar).not.toContainText('Static wireframe');

    // The product name is the rail's, not the bar's.
    await expect(bar).not.toContainText('TRAINER DESK');
    await expect(page.locator('aside')).toContainText('TRAINER DESK');

    // No build version string in the chrome (the 2.0 bar carried `v2.0.0`).
    await expect(page.getByRole('banner')).not.toContainText(/\bv\d+\.\d+\.\d+/);

    // 5. One h1, in the banner band under the bar.
    await expect(page.locator('h1')).toHaveCount(1);
    await expect(page.getByRole('banner').getByRole('heading', { level: 1 })).toHaveCount(1);
});

test('the rail is the wireframe 236px column and marks the current destination', async ({ page }) => {
    await ready(page);

    const rail = page.locator('aside').first();
    const box = await rail.boundingBox();
    expect(box).not.toBeNull();
    expect(Math.round(box!.width)).toBe(236);

    const primary = page.getByRole('navigation', { name: 'Global navigation' });
    const current = primary.locator('[aria-current="page"]');
    await expect(current).toHaveCount(1);
    await expect(current).toHaveText('Dashboard');

    // The left rule is a shape as well as a fill, so state never rides on colour alone (WCAG 1.4.1).
    const rule = await current.evaluate((el) => getComputedStyle(el).borderLeftWidth);
    expect(rule).toBe('4px');
});

test('the mobile bar offers the wireframe four slots plus More', async ({ page }) => {
    await ready(page);
    await page.setViewportSize({ width: 390, height: 844 });

    const mobile = page.getByRole('navigation', { name: 'Primary mobile' });
    await expect(mobile).toBeVisible();
    for (const slot of ['Home', 'Career', 'Legacy', 'Deck']) {
        await expect(mobile.getByRole('link', { name: slot, exact: true })).toBeVisible();
    }
    await expect(mobile.locator('summary')).toContainText('More');

    // The desktop rail is not shrunk onto the phone (design-2.0 §41).
    await expect(page.locator('aside').first()).toBeHidden();
});
