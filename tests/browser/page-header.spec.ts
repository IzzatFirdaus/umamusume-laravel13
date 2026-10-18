import { test, expect } from '@playwright/test';

/*
 * PageHeader and its five pilots (plan 1.3). Rendered-copy evidence, so it reads the built bundle.
 *
 * What is asserted, and what is deliberately not:
 *
 *  - Exactly one `h1` per pilot page. This is the property the component exists to guarantee, and
 *    the failure it prevents is a page whose name appears twice at title weight.
 *  - That one heading carries PageHeader's own classes, at `text-3xl` (30px). The plan text asked
 *    for 28px; that size has no token, and `DESIGN.md` §2.2's owner-signed ladder is 12/14/18/30.
 *    The signed 30px ships and the 28px figure stays with the owner.
 *  - The eyebrow, where the page has one, is the 12px uppercase muted line above the name.
 *
 * NOT asserted: "the h1 is outside `<header>`". The dispatch asked for that, but `DESIGN.md` §2.2's
 * owner-signed 2026-10-17 amendment places the page `h1` in the shell's `#title`, and the owner ruled
 * on 2026-10-17 that the signed document wins over the plan. PageHeader therefore renders the h1 into
 * the shell's title band inside the banner, and the assertion below records the shipped position so a
 * later slice that moves it cannot do so silently. Reported as a divergence rather than weakened.
 */

const PILOTS: { url: string; title: string; eyebrow: string }[] = [
    { url: '/', title: 'Dashboard', eyebrow: 'Overview' },
    { url: '/training-runs', title: 'Training runs', eyebrow: 'Overview' },
    { url: '/skills', title: 'Skill search', eyebrow: 'Reference' },
    { url: '/veterans', title: 'Veteran library', eyebrow: 'Library' },
    { url: '/database', title: 'Database', eyebrow: 'Data Quality' },
];

for (const pilot of PILOTS) {
    test(`${pilot.url} renders one PageHeader h1 at the Critical tier`, async ({ page }) => {
        await page.goto(pilot.url, { waitUntil: 'domcontentloaded' });
        await page.locator('#app > *').first().waitFor();

        // One, not "at least one": a second title-weight name is the defect this guards.
        const headings = page.locator('h1');
        await expect(headings).toHaveCount(1);

        const h1 = headings.first();
        await expect(h1).toHaveText(pilot.title);

        // The Critical tier, from the signed ladder: 30px and semibold.
        await expect(h1).toHaveClass(/text-3xl/);
        await expect(h1).toHaveClass(/font-semibold/);
        await expect(h1).toHaveClass(/text-ink-strong/);

        // The eyebrow is the quiet line above the name, not a second title.
        const eyebrow = page.getByText(pilot.eyebrow, { exact: true }).first();
        await expect(eyebrow).toBeVisible();
        await expect(eyebrow).toHaveClass(/uppercase/);
        await expect(eyebrow).toHaveClass(/text-xs/);

        // Shipped position, recorded so a silent move fails here (see the note above).
        expect(await h1.evaluate((el) => el.closest('header') !== null)).toBe(true);
    });
}
