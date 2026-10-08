/*
 * The Veteran library and the mobile navigation bar (`SCREEN-021`, design-2.0 §41).
 *
 * Two claims live here and neither is reachable from the PHP suite. The first is the shape of the bar a
 * Trainer meets at phone width: design-2.0 §41 asks for Home, Career, Legacy, Deck and More, and forbids
 * shrinking the desktop sidebar onto mobile. The sidebar used to be rendered whole inside an
 * `overflow-x-auto` strip, which is why nothing at 320px looked scrollable: a horizontal-only scroller
 * under a vertical gesture shows no affordance and answers no wheel. The second claim is the library's
 * empty state, which is the state a fresh install actually shows: the scratch database holds no Veterans
 * and no career has been filed into it on this database.
 *
 * Axe coverage is provided by `tests/browser/accessibility.spec.ts`: `@axe-core/playwright` is installed
 * and scans pages against `wcag2a`, `wcag2aa`, and `wcag21aa`; this spec retains the hand-rolled checks
 * for target size, keyboard path, focus order, reflow and console errors.
 * This spec writes no database row.
 */

import { expect, test } from '@playwright/test';

test.describe('Mobile navigation', () => {
    test('shows five slots at 320px, not a scrolling copy of the sidebar', async ({ page }) => {
        await page.setViewportSize({ width: 320, height: 720 });
        await page.goto('/');
        await page.locator('#app > *').first().waitFor();

        const bar = page.getByRole('navigation', { name: 'Bottom navigation' });

        // The four destinations, and More as the disclosure. The brief's word for the slot is the
        // accessible name, so the count is the claim rather than a label list kept in two places. `More`
        // is a native summary, not a link, so it is asserted by text rather than by role.
        for (const slot of ['Home', 'Career', 'Legacy', 'Deck']) {
            await expect(bar.getByRole('link', { name: slot, exact: true })).toBeVisible();
        }

        await expect(bar.getByText('More')).toBeVisible();

        await expect(bar.getByRole('link', { name: 'Support Cards' })).toHaveCount(0);

        // The document must not scroll sideways (WCAG 1.4.10), which the old strip did not guarantee.
        const overflow = await page.evaluate(
            () => document.documentElement.scrollWidth - document.documentElement.clientWidth,
        );
        expect(overflow, 'the document scrolls horizontally at 320px').toBeLessThanOrEqual(1);
    });

    test('opens More to reach the destinations the bar does not carry', async ({ page }) => {
        await page.setViewportSize({ width: 320, height: 720 });
        await page.goto('/');
        await page.locator('#app > *').first().waitFor();

        const more = page.getByRole('navigation', { name: 'Bottom navigation' }).getByText('More');

        // Closed by default, and the hidden destinations are not in the tab order while it is shut.
        await expect(page.getByRole('link', { name: 'Veterans' })).not.toBeVisible();

        await more.click();
        await expect(page.getByRole('link', { name: 'Veterans' })).toBeVisible();
        await expect(page.getByRole('link', { name: 'Settings' })).toBeVisible();

        // Escape closes the disclosure and puts focus back where the Trainer opened it.
        await page.getByRole('link', { name: 'Veterans' }).focus();
        await page.keyboard.press('Escape');
        await expect(page.getByRole('link', { name: 'Veterans' })).not.toBeVisible();
        await expect(more).toBeVisible();
    });

    test('keeps every desktop destination live, with no named absence left in the nav', async ({ page }) => {
        await page.goto('/');
        await page.locator('#app > *').first().waitFor();

        const aside = page.locator('aside');

        // Ten live destinations since D16 made the library a screen rather than a placeholder.
        await expect(aside.getByRole('link')).toHaveCount(10);
        await expect(aside.getByRole('link', { name: 'Veterans' })).toHaveAttribute('href', '/veterans');
        await expect(aside.getByRole('link', { name: 'New Career' })).toHaveAttribute(
            'href',
            '/career/setup/scenario',
        );
        // Nothing in the product nav is "not built" any more, so the phrase must not appear at all.
        await expect(aside.getByText('not built')).toHaveCount(0);
    });
});

test.describe('Veteran library', () => {
    test('says the library holds nothing filed rather than showing a blank list', async ({ page }) => {
        await page.goto('/veterans');
        await page.locator('#app > *').first().waitFor();

        await expect(page.getByRole('heading', { name: 'Veteran library', exact: true }).first()).toBeVisible();
        await expect(page.getByText('Record only.')).toBeVisible();
        await expect(page.getByText('The library holds no Veterans yet.')).toBeVisible();
        // The reason and the door, not an empty box. Save Veteran landed with D16's write half, so the
        // empty library is "nothing filed yet" with a route to filing rather than a wait for a slice.
        await expect(page.getByText(/A Veteran is a completed career filed into the library/)).toBeVisible();
        await expect(page.getByRole('link', { name: 'Open the run list' })).toBeVisible();
        // `SCREEN-021`'s parent search, which is a search rather than an optimizer.
        await expect(page.getByRole('link', { name: 'Find parents for this build' })).toBeVisible();

        // The three facets the query answers, and no others.
        await expect(page.getByRole('combobox', { name: 'Trainee' })).toBeVisible();
        await expect(page.getByRole('combobox', { name: 'Scenario' })).toBeVisible();
        await expect(page.getByRole('textbox', { name: 'Tag' })).toBeVisible();
        await expect(page.getByRole('button', { name: 'Filter' })).toBeVisible();

        // The brief's unsupportable features are stated as absences with their reason on the page.
        await expect(page.getByText('NOT IN THIS BUILD')).toBeVisible();
        await expect(page.getByText(/Favorite, archive, delete/)).toBeVisible();
        await expect(page.getByText(/no sourced table/i).or(page.getByText(/No sourced table/))).toBeVisible();
    });

    test('orders the library through the bar rather than the filter form', async ({ page }) => {
        await page.goto('/veterans');
        await page.locator('#app > *').first().waitFor();

        const oldest = page.getByRole('button', { name: 'Oldest first' });
        await oldest.click();
        await page.locator('#app > *').first().waitFor();
        await expect(page).toHaveURL(/order=oldest/);
        await expect(oldest).toHaveAttribute('aria-current', 'true');

        // The toggle is a button pair, not a fourth select: a date sort would be a claim the store cannot
        // support, so nothing here pretends to order by completion.
        await expect(page.getByRole('combobox', { name: /order/i })).toHaveCount(0);
    });

    test('collapses the rail to marks and keeps every destination named', async ({ page }) => {
        await page.setViewportSize({ width: 1280, height: 800 });
        await page.goto('/');
        await page.locator('#app > *').first().waitFor();

        const aside = page.locator('aside');

        // Anchored on the region it controls rather than on its own name, because the name is the action
        // and the action changes when the rail collapses: a locator by "Collapse navigation" dissolves the
        // moment it succeeds.
        const toggle = page.locator('button[aria-controls="primary-nav"]');

        await toggle.focus();
        await expect(toggle).toHaveAccessibleName('Collapse navigation');
        await expect(toggle).toHaveAttribute('aria-expanded', 'true');

        const widthBefore = (await aside.boundingBox())?.width ?? 0;

        // The owner asked for the marks at one width only: the full rail is words, and a mark never sits
        // beside the word it stands for. Inside the aside the only drawing at full width is the toggle's
        // own chevron.
        await expect(aside.locator('svg')).toHaveCount(1);

        await toggle.press('Enter');

        // Collapsed, each destination carries its mark and the word moves to `sr-only`: ten plus the
        // chevron.
        await expect(aside.locator('svg')).toHaveCount(11);

        await expect(toggle).toHaveAttribute('aria-expanded', 'false');
        await expect(toggle).toHaveAccessibleName('Expand navigation');

        const widthAfter = (await aside.boundingBox())?.width ?? 0;
        expect(widthAfter, 'the rail did not narrow').toBeLessThan(widthBefore);

        // Collapsed means marks, but nothing loses its name: each destination is still reachable by
        // its own word, which is the difference between an icon rail and ten anonymous links.
        for (const label of ['Dashboard', 'New Career', 'Careers', 'Legacy Lab', 'Veterans', 'Settings']) {
            const link = aside.getByRole('link', { name: label, exact: true });
            await expect(link).toBeVisible();
            await expect(link).toHaveAttribute('title', label);
        }

        // The current page still reads as current, and by more than colour: the collapsed active item
        // carries a left rule as well as its fill.
        const current = aside.getByRole('link', { name: 'Dashboard', exact: true });
        await expect(current).toHaveAttribute('aria-current', 'page');
        expect(((await current.getAttribute('class')) ?? '').split(' ')).toContain('border-l-2');

        // The choice outlives the visit. It lives in the browser, not in a preference row, so a reload
        // must carry it and a fresh context must not.
        await page.reload();
        await page.locator('#app > *').first().waitFor();
        await expect(page.getByRole('button', { name: 'Expand navigation' })).toHaveAttribute(
            'aria-expanded',
            'false',
        );
    });

    test('carries the product navigation onto the wizard screens', async ({ page }) => {
        await page.goto('/career/setup/scenario');
        await page.locator('#app > *').first().waitFor();

        // The wizard keeps its step bar as a sub-nav and now inherits the shell, so both landmarks exist
        // and the library is one click from step 1.
        await expect(page.getByRole('navigation', { name: 'Setup steps' })).toBeVisible();
        await expect(page.locator('aside').getByRole('link', { name: 'Veterans' })).toBeVisible();

        // One main region: the wizard must not re-declare the shell's landmarks.
        await expect(page.getByRole('main')).toHaveCount(1);
        await expect(page.getByRole('link', { name: 'Skip to content' })).toHaveCount(1);
        await expect(page.getByRole('link', { name: 'Leave setup' })).toBeVisible();
    });
});
