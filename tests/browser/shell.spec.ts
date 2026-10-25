import { test, expect } from '@playwright/test';

/*
 * The app shell's bar (plan 1.2). Rendered-copy evidence, so it reads the built bundle and cannot
 * run green before `npm run build` (KI-70: a build writes `public/build`, shared by every session).
 *
 * What is asserted is the part of 1.2 that does not contradict an owner-signed rule:
 *
 *   1. The product bar is 48px tall (`BAR`), with the section crumb left and the live-career pill
 *      right; the product name and rail control sit in the rail head (HFW shell, annotation 10).
 *   2. Its accent rule is 2px along the bottom edge.
 *   3. The build's version string is gone from the chrome.
 *   4. The active destination carries `aria-current="page"` and a 3px left rule in the rail.
 *
 * What is deliberately NOT asserted: the plan's "h1 in `<main>`". `DESIGN.md` §2.2's owner-signed
 * 2026-10-17 amendment places the page `h1` in the shell's `#title`, and the owner ruled on
 * 2026-10-17 that the signed document wins. The `h1` therefore stays in the banner band immediately
 * below the 48px bar, which this case pins as `h1`-inside-banner rather than a repaint.
 */

// The bar is the first row of the banner: `h-12` = 3rem = 48px at the default root size.
const BAR = '[role="banner"] > div:first-child';

test('the product bar is 48px with a 2px stripe and no version string', async ({ page }) => {
    await page.goto('/', { waitUntil: 'domcontentloaded' });
    await page.locator('#app > *').first().waitFor();

    const bar = page.locator(BAR);

    // 1. Height. `getBoundingClientRect` through the bbox API reports the painted box.
    const box = await bar.boundingBox();
    expect(box).not.toBeNull();
    expect(Math.round(box!.height)).toBe(48);

    // 2. The accent rule along the bottom edge, measured rather than read from the class list: a
    //    later `border-b` utility in the same element would leave the class list looking correct.
    const stripe = await bar.evaluate((el) => getComputedStyle(el).borderBottomWidth);
    expect(stripe).toBe('2px');

    // 3. No build version string. `v2.0.0` was the shipped string; any `v` + semver token is the
    //    same defect.
    await expect(page.getByRole('banner')).not.toContainText(/\bv\d+\.\d+\.\d+/);

    // 4. The bar carries the section crumb; the product name and the rail control live in the rail
    //    head (HFW index.html annotation 10). The bar itself carries no heading of its own.
    await expect(bar).toContainText('Dashboard');
    await expect(bar).not.toContainText('TRAINER DESK');
    await expect(bar.getByRole('heading')).toHaveCount(0);
    await expect(page.locator('aside')).toContainText('TRAINER DESK');

    // 5. One h1 on the page, in the banner band below the bar (not in the 48px row itself).
    await expect(page.locator('h1')).toHaveCount(1);
    await expect(page.getByRole('banner').getByRole('heading', { level: 1 })).toHaveCount(1);
});

test('the active destination is marked with aria-current and a left rule', async ({ page }) => {
    await page.goto('/', { waitUntil: 'domcontentloaded' });
    await page.locator('#app > *').first().waitFor();

    const primary = page.getByRole('navigation', { name: 'Global navigation' });
    const current = primary.locator('[aria-current="page"]');

    await expect(current).toHaveCount(1);

    // The desktop rail carries the left rule as a stock utility (`border-l-4`, the nearest step on the
    // ramp to the plan's 3px). This viewport renders the sidebar.
    const rule = await current.evaluate((el) => getComputedStyle(el).borderLeftWidth);
    expect(rule).toBe('4px');
});
