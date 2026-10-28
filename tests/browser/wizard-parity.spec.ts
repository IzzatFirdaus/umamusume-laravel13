import { test, expect, type Page } from '@playwright/test';

/**
 * Sweep over the seven setup steps: the shape every page owes the wireframe regardless of what it
 * draws, checked on each page rather than once on one.
 *
 * What this file is for: a rule that holds page-locally can pass seven times and still be violated by
 * the step that was added last. Every case here loops the whole wizard, so a new step is covered the
 * moment it is added to `PAGES`.
 *
 * What this file is NOT: parity evidence. "A green test is not evidence" — the per-page three-diff
 * reports under `.scratch-uma/wizard-parity/` are, and each phase's own spec fixes the page's
 * content. These cases hold only the invariants that the wireframes share across all seven:
 * one `h1`, one pair of head-band actions, six stepper items with one current, one filled action,
 * no artifact vocabulary, no dark: variants, and no horizontal overflow at 390px.
 */

type Step = { name: string; path: string };

/** The wizard in step order, paths as `routes/web.php` names them. */
const PAGES: Step[] = [
    { name: 'scenario', path: '/career/setup/scenario' },
    { name: 'trainee', path: '/career/setup/trainee' },
    // The profile step is per-trainee; `career-trainee-select.spec.ts` proves `/33` is in the roster.
    { name: 'profile', path: '/career/setup/trainee/33' },
    { name: 'target', path: '/career/setup/target' },
    { name: 'legacy', path: '/career/setup/legacy' },
    { name: 'deck', path: '/career/setup/deck' },
    { name: 'preflight', path: '/career/setup/preflight' },
];

async function open(page: Page, step: Step): Promise<void> {
    await page.goto(step.path, { waitUntil: 'domcontentloaded' });
    await page.locator('#app > *').first().waitFor();
}

test.describe('setup wizard sweep', () => {
    for (const step of PAGES) {
        test(`${step.name}: one h1, one head band, six stepper items, one filled action`, async ({ page }) => {
            await open(page, step);

            // One `h1`: the step's own title, not a card heading promoted to page rank.
            await expect(page.locator('h1')).toHaveCount(1);

            // The head band carries the wireframe's back + forward pair, exactly two controls. A third
            // control there is a page-level action the wireframe draws somewhere else. Phase 4 put the
            // profile step's pair in the band, so the rule now holds on all seven steps.
            const actions = page.locator('[data-region="hrow-actions"] a, [data-region="hrow-actions"] button');
            await expect(actions).toHaveCount(2);

            // Six steps, one of them current. The stepper is a status readout, not a second nav.
            await expect(page.locator('[data-setup-steps] .step')).toHaveCount(6);
            await expect(page.locator('[data-setup-steps] [aria-current="step"]')).toHaveCount(1);

            // One filled action per page: the forward door. Finding two means a card grew a primary.
            await expect(page.locator('.bg-primary-action')).toHaveCount(1);
        });

        test(`${step.name}: no artifact vocabulary, no dark variants, no horizontal overflow`, async ({ page }) => {
            await open(page, step);

            // Artifacts the wireframes use to annotate themselves. None of them is a screen's content.
            for (const marker of ['.keybox', '.proto', '.wfnav', '.mockbar', '.anno', '[class*="annotation"]']) {
                await expect(page.locator(marker), `${marker} must not reach the rendered page`).toHaveCount(0);
            }

            // `title=` is not an accessible name: it survives neither a keyboard nor a touch user. Every
            // tooltip-shaped fact belongs in visible copy or in an aria-label on an interactive control.
            const titled = await page.locator('[title]').evaluateAll((nodes) =>
                nodes.filter((node) => {
                    const role = node.getAttribute('role');
                    const interactive =
                        ['A', 'BUTTON', 'INPUT', 'SELECT', 'TEXTAREA', 'SUMMARY'].includes(node.tagName) ||
                        (role !== null && ['button', 'link', 'tab', 'menuitem'].includes(role));
                    return !interactive;
                }).length,
            );
            expect(titled, 'title= on non-interactive elements').toBe(0);

            // No dark: variants. The design system ships one palette and a theme is not a per-page
            // decision (`DESIGN.md` §3 "Colour").
            const dark = await page.locator('[class*="dark:"]').count();
            expect(dark, 'dark: variants on the wizard').toBe(0);

            // The one measured rule at 390: the wizard does not scroll sideways. Wide tables scroll
            // inside their own focusable region, which is why this measures the document, not a table.
            await page.setViewportSize({ width: 390, height: 844 });
            await page.waitForTimeout(50);
            const overflow = await page.evaluate(
                () => document.documentElement.scrollWidth - document.documentElement.clientWidth,
            );
            expect(overflow, 'horizontal overflow at 390x844').toBeLessThanOrEqual(0);
        });
    }

    for (const step of PAGES) {
        test(`${step.name}: the words the wireframes do not draw never reappear`, async ({ page }) => {
            await open(page, step);

            // `Leave setup` was the shell's exit before the head band carried the wireframe's own pair.
            await expect(page.getByText('Leave setup')).toHaveCount(0);

            // The position readout is the head band's own element, not a free-floating line: one per
            // page, inside the actions row where the wireframe puts it.
            await expect(page.locator('[data-region="hrow-position"]')).toHaveCount(1);
            await expect(page.locator('[data-region="hrow-actions"] [data-region="hrow-position"]')).toHaveCount(1);
        });

        test(`${step.name}: both themes keep the same region set`, async ({ page }) => {
            // The wireframes are light-only, so the HFW cannot answer this: the check is app-side. A
            // theme that hides a region is a region that exists only by accident of colour.
            //
            // The theme is the app's own switch, `html[data-theme="dark"]` (`app.css:395`,
            // `useTheme.ts`), not a `.dark` class: the first version toggled a class no stylesheet
            // reads, so both counts described the light theme and the case could not fail.
            await open(page, step);
            const light = await page.locator('[data-region]').count();

            await page.evaluate(() => {
                document.documentElement.setAttribute('data-theme', 'dark');
            });
            const dark = await page.locator('[data-region]').count();

            await page.evaluate(() => {
                document.documentElement.removeAttribute('data-theme');
            });

            expect(dark, 'region count under [data-theme="dark"]').toBe(light);
        });
    }
});

