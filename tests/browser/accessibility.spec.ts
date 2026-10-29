import { test, expect } from '@playwright/test';
import { buildAxe, openAllDisclosures } from '../utils/accessibility';

test.describe('Accessibility', () => {
    test('landing page passes WCAG axe scan', async ({ page }) => {
        await page.goto('/');
        await page.locator('#app > *').first().waitFor();

        await openAllDisclosures(page);
        const results = await buildAxe(page).analyze();

        expect(results.violations, JSON.stringify(results.violations, null, 2)).toEqual([]);
    });

    test('training runs list passes WCAG axe scan', async ({ page }) => {
        await page.goto('/training-runs');
        await page.locator('#app > *').first().waitFor();

        await openAllDisclosures(page);
        const results = await buildAxe(page).analyze();

        expect(results.violations, JSON.stringify(results.violations, null, 2)).toEqual([]);
    });

    /*
     * KI-63: Inertia's bundled NProgress draws `role="bar"`, an invalid ARIA role, so a page-wide
     * axe scan fails at WCAG 2.2 A 4.1.2 whenever a visit is in flight — and the two scans above
     * pass only because a settled `goto` usually leaves none. The replacement indicator is a real
     * `progressbar` with an accessible name and the value attributes the role requires, so this
     * case holds a visit open and reads the live element: the semantics it renders, not the source
     * that promises them.
     */
    test('the visit progress indicator carries valid progressbar semantics mid-visit (KI-63)', async ({ page }) => {
        await page.goto('/');
        await page.locator('#app > *').first().waitFor();

        await page.route('**/review', async (route) => {
            await new Promise((resolve) => setTimeout(resolve, 2500));
            await route.continue();
        });

        /*
         * The route pattern needs the double-star prefix to match the full URL, and the bare path
         * does not; the HFW's landing carries two "Review queue" doors (Quick actions and the Needs
         * you column), so this case names the page's own region, then the link inside it.
         */
        await page.getByRole('region', { name: 'Quick actions' }).getByRole('link', { name: 'Review queue' }).click({ noWaitAfter: true });

        // The indicator is the indeterminate pattern the slice names: a native `progress` with an
        // accessible name and no value attribute. A real percentage is not available to invent -
        // Inertia reports one only for FormData uploads, and this app uploads none - so no
        // aria-valuenow and no trickle is offered either.
        const bar = page.getByRole('progressbar', { name: 'Page load progress' });
        await expect(bar).toBeVisible();

        // The whole point of the replacement: the page-wide scan that KI-63 could never pass
        // mid-visit is clean, and the invalid role is not in the document at all.
        await openAllDisclosures(page);
        const results = await buildAxe(page).analyze();
        expect(results.violations, JSON.stringify(results.violations, null, 2)).toEqual([]);
        await expect(page.locator('[role="bar"]')).toHaveCount(0);

        await page.waitForURL(/\/review$/);

        // Settled: the indicator leaves the tree entirely, so the settled scans above scan a
        // document with no progress control in it.
        await expect(page.getByRole('progressbar')).toHaveCount(0);
    });

    /*
     * The trainee step's selection echo is the wizard's only surface that renders a labelled `select`
     * inside a card, and it appears only once a trainee with more than one costume form is chosen. No
     * page-wide scan covered the route before, so the control entered the tree with no axe pass behind
     * it. This case puts the route in the scanned set with the echo open.
     */
    test('trainee step passes WCAG axe scan with the selection echo and its form picker', async ({ page }) => {
        await page.goto('/career/setup/trainee?search=Rice Shower');
        await page.locator('#app > *').first().waitFor();

        await page.locator('[data-trainee-card]').first().click();
        await expect(page.locator('select#selected-form')).toBeVisible();

        await openAllDisclosures(page);
        const results = await buildAxe(page).analyze();

        expect(results.violations, JSON.stringify(results.violations, null, 2)).toEqual([]);
    });

    /*
     * A-91 (plan slice 1.4). A `role="list"` container whose children are not `listitem`s is an
     * `aria-required-children` violation at critical impact. Three surfaces carried one: the
     * Dashboard's career strip, the wizard's Legacy step, and the scenario step's facts grid. The
     * first two were fixed by name; the third survived because it sits inside a collapsed
     * `<details>`, which a settled scan never descends into - axe skips hidden nodes - and no scan
     * covered the route at all. This case opens one disclosure so the container enters the scanned
     * tree, and covers all three surfaces.
     *
     * The assertion is per-rule rather than page-wide on purpose: the scans above already hold the
     * whole document clean, and naming the rule makes a reintroduction fail with the rule id in the
     * message instead of a diff of every violation on the page.
     */
    test('no list-role container holds non-list children on the dashboard or the two wizard steps', async ({ page }) => {
        for (const path of ['/', '/career/setup/scenario', '/career/setup/legacy'] as const) {
            await page.goto(path);
            await page.locator('#app > *').first().waitFor();

            if (path === '/career/setup/scenario') {
                // Every card renders the same facts markup from one template, so opening one puts the
                // container in the tree; the count is read off what rendered rather than hardcoded.
                const summaries = page.locator('summary').filter({ hasText: 'Facts and mechanics' });
                await expect(summaries, 'the step renders a facts disclosure per scenario card').not.toHaveCount(0);
                await summaries.first().click();
            }

            await openAllDisclosures(page);
            const results = await buildAxe(page).analyze();
            const offenders = results.violations.filter((rule) => rule.id === 'aria-required-children');

            expect(offenders, `${path} - ${JSON.stringify(offenders, null, 2)}`).toEqual([]);
        }
    });

    /*
     * The proof that opening the disclosures is load-bearing, and not tidiness.
     *
     * axe does not descend into a closed `<details>`, so a violation inside one is invisible to the
     * scan. A-91 (plan slice 1.4) found a live `aria-required-children` violation at critical
     * impact that no settled scan had ever seen, because it sat inside a collapsed `<details>` on
     * the wizard's scenario step. The container below is that shape - a `role="list"` whose children
     * are not `listitem`s - injected into a closed disclosure so both halves can be asserted: closed,
     * the scan finds nothing; open, it finds the violation.
     */
    test('a violation hidden inside a closed disclosure is caught once the disclosures are open', async ({ page }) => {
        await page.goto('/');
        await page.locator('#app > *').first().waitFor();

        await page.evaluate(() => {
            const details = document.createElement('details');
            const list = document.createElement('div');
            list.setAttribute('role', 'list');
            const child = document.createElement('div');
            child.textContent = 'not a list item';
            list.appendChild(child);
            details.appendChild(list);
            document.querySelector('#app')?.appendChild(details);
        });

        // Closed: the violation is inside a hidden node, and axe does not descend into it.
        const closed = await buildAxe(page).analyze();
        expect(
            closed.violations.filter((rule) => rule.id === 'aria-required-children'),
            'a closed disclosure hides its contents from the scan',
        ).toEqual([]);

        // Open: the helper opens every disclosure, so the container is in the scanned tree.
        await openAllDisclosures(page);
        const open = await buildAxe(page).analyze();
        expect(
            open.violations.filter((rule) => rule.id === 'aria-required-children').length,
            'the opened disclosure puts the violation in front of the scan',
        ).toBeGreaterThan(0);
    });
});