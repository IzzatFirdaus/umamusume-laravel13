import { test, expect } from '@playwright/test';

/*
 * The responsive sweep (plan slice 8.4), rendered-copy evidence for the link hit areas.
 *
 * The house bar is a HEIGHT bar: DESIGN.md §12 says "The house bar is 44px (`min-h-11`/`h-11`),
 * stricter than WCAG 2.2 AA's 24px minimum (2.5.8). Every control meets the 24px floor; 44px is
 * the design intent." The 8.4 measurement read the same rule from the tree: all 44px assertions
 * in the suite measure `box?.height`, and none measures width. So these cases measure height, and
 * they are the first responsive spec in the tree - the measurement that produced them found zero
 * horizontal overflow, zero sticky elements and zero focus occlusions across 56 route-widths, and
 * these three link hit areas as the only failures.
 *
 * Each case names the link by its visible text, because the point of the fix is that the link IS
 * the target: the row around it carries no `rowHref` and no row handler, so the name link is the
 * only way into the detail.
 */

test('/database/skills — the skill name link hit area meets the 44px house bar', async ({ page }) => {
    await page.goto('/database/skills', { waitUntil: 'domcontentloaded' });
    await page.locator('#app > *').first().waitFor();

    const link = page.locator('#skill-results a').first();
    const box = await link.boundingBox();

    expect(box?.height ?? 0, `the skill name link is ${box?.height ?? 0}px tall, under the 44px bar`).toBeGreaterThanOrEqual(44);
});

test('/database/supports — the support-card name link hit area meets the 44px house bar', async ({ page }) => {
    await page.goto('/database/supports', { waitUntil: 'domcontentloaded' });
    await page.locator('#app > *').first().waitFor();

    // The row carries TWO links: the artwork slot's own anchor (a `size-12` frame, 48px, already
    // over the bar) and the name link beside it. The name link is the one the 8.4 measurement found
    // at 20px, so the assertion names it by its own class rather than taking the row's first link.
    const link = page.locator('#support-card-results a.font-semibold').first();
    const box = await link.boundingBox();

    expect(box?.height ?? 0, `the support-card name link is ${box?.height ?? 0}px tall, under the 44px bar`).toBeGreaterThanOrEqual(44);
});

test('/database/sources — the source URL link hit area meets the 44px house bar', async ({ page }) => {
    await page.goto('/database/sources', { waitUntil: 'domcontentloaded' });
    await page.locator('#app > *').first().waitFor();

    // The page's own source URL, inside the Fetched section. `a.underline` alone would match the
    // shell's nav link first, which is already 44px; the URL is the link whose text IS the address.
    const link = page.locator('section[aria-labelledby="fetched-heading"] a.underline').first();
    const box = await link.boundingBox();

    expect(box?.height ?? 0, `the source URL link is ${box?.height ?? 0}px tall, under the 44px bar`).toBeGreaterThanOrEqual(44);
});

test('/database/sources — the transcribed publisher link hit area meets the 44px house bar', async ({ page }) => {
    await page.goto('/database/sources', { waitUntil: 'domcontentloaded' });
    await page.locator('#app > *').first().waitFor();

    // The Transcribed section's own link. It is the same 19px shape as the raw URL, and the 8.4
    // measurement counted it in the same group: nine `a.underline` instances, two of them in this
    // page's markup.
    const link = page.locator('section[aria-labelledby="transcribed-heading"] a.underline').first();
    const box = await link.boundingBox();

    expect(box?.height ?? 0, `the transcribed publisher link is ${box?.height ?? 0}px tall, under the 44px bar`).toBeGreaterThanOrEqual(44);
});

test('/database/trainees — the trainee name link hit area meets the 44px house bar', async ({ page }) => {
    await page.goto('/database/trainees', { waitUntil: 'domcontentloaded' });
    await page.locator('#app > *').first().waitFor();

    const link = page.locator('main ul li a.text-lg').first();
    const box = await link.boundingBox();

    expect(box?.height ?? 0, `the trainee name link is ${box?.height ?? 0}px tall, under the 44px bar`).toBeGreaterThanOrEqual(44);
});
