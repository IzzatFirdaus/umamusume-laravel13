import { test, expect } from '@playwright/test';
import { uraCompletedRun } from '../utils/ura-completed-run';

/*
 * The containment constraint, pinned.
 *
 * `main` in `AppLayout.vue` carries `container-type: inline-size` (the `@container` class), which is
 * load-bearing: twenty-one files lay themselves out with the `@2xl:`/`@3xl:`/`@4xl:`/`@5xl:` variants and
 * those resolve against that element rather than the viewport. The CONSEQUENCE is that `position: sticky`
 * is a no-op for every descendant, because the sticky containing block is the containment ancestor rather
 * than the scrollport.
 *
 * So this file asserts the CURRENT, DELIBERATE behaviour: these elements do NOT stick. It is a regression
 * test that documents a limitation, not a test of a feature, and it exists so the retired classes cannot
 * quietly come back as decoration.
 *
 * If you are here because an assertion failed, you may have fixed the containment. Then the right move is
 * to invert these cases to assert sticking, and remove the retirement notes from `AppLayout.vue`,
 * `SetupLayout.vue` and `Career/Cockpit.vue`. Fixing it is a design decision, not a cleanup.
 */

/** How far the element's top moved after scrolling the window from the top to `to` px. */
async function driftAfterScrollTo(page: import('@playwright/test').Page, selector: string, to: number): Promise<number> {
    const locator = page.locator(selector).first();
    await expect(locator).toBeVisible();

    // Reset to the top FIRST. Without this the second call measures from a page already scrolled by the
    // first, so `before` and `after` are the same position and the drift reads 0 - which is what a broken
    // measurement looks like, not what a sticky element looks like.
    const reset = await page.evaluate(() => {
        window.scrollTo(0, 0);
        return window.scrollY;
    });
    expect(reset, 'the page did not return to the top before measuring').toBeLessThanOrEqual(2);

    const before = await locator.evaluate((el) => el.getBoundingClientRect().top);
    const scrolled = await page.evaluate((y) => {
        window.scrollTo(0, y);
        return window.scrollY;
    }, to);

    // The scroll has to have HAPPENED, or the measurement proves nothing at all.
    expect(scrolled, `the page did not scroll to ${to}`).toBeGreaterThanOrEqual(to - 2);

    const after = await locator.evaluate((el) => el.getBoundingClientRect().top);

    return before - after;
}

test('the containment keeps the stepper and the run tools from sticking', async ({ page }) => {
    await page.setViewportSize({ width: 1280, height: 800 });
    await page.goto(`/training-runs/${uraCompletedRun()}/cockpit`, { waitUntil: 'domcontentloaded' });
    await page.locator('#app > *').first().waitFor();

    /*
     * 2026-10-28: the two probes were `nav[aria-label="On this page"]` and `[aria-label="Run
     * summary"]`. Both elements are gone from the cockpit — the parity pass cut the wayfinder and the
     * summary strip because no cockpit HFW draws either, so the two names this case measured no longer
     * exist on any page. The PROPERTY under test is unchanged and still load-bearing: `main` carries
     * `container-type: inline-size`, that makes it the sticky containing block, and a descendant that
     * asks to stick therefore scrolls with the page instead of pinning. Retargeted to the two elements
     * that now hold the same positions in the same container, rather than deleted, so the gate still
     * guards the containment.
     */
    const stepper = await driftAfterScrollTo(page, 'nav[aria-label="Career"]', 200);
    const tools = await driftAfterScrollTo(page, '[data-region="run-tools"]', 200);

    expect(stepper, `the stepper drifted ${stepper}px, so something made it stick`).toBeGreaterThanOrEqual(190);
    expect(tools, `the run tools drifted ${tools}px, so something made it stick`).toBeGreaterThanOrEqual(190);
});

test('the wizard footer still sticks, because the step wrapper reaches the scrollport', async ({ page }) => {
    await page.setViewportSize({ width: 1280, height: 800 });
    await page.goto('/career/setup/scenario', { waitUntil: 'domcontentloaded' });
    await page.locator('#app > *').first().waitFor();

    /*
     * The one descendant of `main` that DOES stick, and the case that makes the difference legible.
     *
     * `tests/browser/sticky-footer.spec.ts` already pins that `StickyFooter` sticks - but it pins it in
     * its own Vite fixture, which has no `main`, no `@container` and no container-query ancestor at all.
     * That is the component's ISOLATED contract: the `position: sticky` in its class attribute is real
     * and effective. It says nothing about whether the bar survives `main`'s containment, and nothing
     * pinned that, which is why a proposal to "fix" the bar by wrapping it could have landed without
     * anything going red.
     *
     * The mechanism is GEOMETRY, and it is why the bar survives here at all.
     *
     * `SetupLayout.vue:182` gives the wizard step its own `@container` wrapper, and that wrapper is
     * TALLER than the scrollport, so its box overlaps it. A sticky element can only be pinned while its
     * containing block gives it somewhere to move: the bar's containing block is that wrapper, which
     * extends below the viewport, so the browser lifts the bar to the viewport bottom and holds it
     * there while the page scrolls.
     *
     * A wrapper drawn around the bar alone would replace that containing block with one the height of
     * the bar - 93px, measured, against a 782px wrapper - which sits entirely below the scrollport and
     * leaves the bar nothing to move across. Measured with that wrapper in place: the bar's drift went
     * from 0px to the full 200px of the scroll, and its bottom edge finished 48px below the viewport.
     * So the bar's stickiness is carried by the wrapper being TALL, and `StickyFooter.vue` says the
     * same thing in its own docblock: the footer is the root element of the component.
     */
    const footer = page.locator('footer.sticky').first();
    await expect(footer).toBeVisible();

    // A sticky bar does not drift: it stays put while the page moves under it.
    const drift = await driftAfterScrollTo(page, 'footer.sticky', 200);
    expect(drift, `the footer drifted ${drift}px, so it is not sticking`).toBeLessThanOrEqual(2);

    // And the observable that separates sticking from drifting: the bar's bottom edge sits ON the
    // viewport bottom, and it is still on screen rather than scrolled past.
    const rect = await footer.evaluate((el) => {
        const r = el.getBoundingClientRect();
        return { top: r.top, bottom: r.bottom };
    });
    const viewport = page.viewportSize()!;

    expect(
        Math.abs(viewport.height - rect.bottom),
        `the footer's bottom edge is at ${rect.bottom}px, not the viewport bottom ${viewport.height}px`,
    ).toBeLessThanOrEqual(1);
    expect(rect.top, 'the footer scrolled away instead of sticking').toBeLessThan(viewport.height);
});

test('no retired sticky utility survives in a class attribute', async () => {
    /*
     * A SOURCE check, and it is here for a measured reason: the behaviour case above CANNOT test the
     * retirement. Reintroducing `md:sticky` on the wayfinder leaves the DOM behaving identically, because
     * the containment turns the class into a no-op - the proof was attempted and the case still passed
     * with the class back. So the retirement is a SOURCE fact, and this is the assertion that can fail.
     *
     * It matches the utility inside a `class="..."` attribute only. The retirement notes in these files
     * name the utilities in prose, and a blanket text scan would flag the very comments explaining the
     * removal - the same trap the retired component name set in career-cockpit.spec.ts.
     */
    const fs = await import('node:fs');

    // The two files this slice retired the claims from. `StickyFooter.vue` and `Veterans/Compare.vue`
    // also use sticky positioning and are deliberately NOT in this list: the first is a primitive used
    // by two pages and retiring its contract is its own decision, and the second positions sticky cells
    // inside a table's own scroll container, which is a different containing block.
    const retired: string[] = [
        'resources/js/layouts/SetupLayout.vue',
        'resources/js/pages/Career/Cockpit.vue',
    ];

    const offenders = retired.filter((file) =>
        /class="[^"]*\bmd:sticky\b/.test(fs.readFileSync(file, 'utf8')),
    );

    expect(offenders, `a retired sticky utility came back in ${offenders.join(', ')}`).toEqual([]);
});
