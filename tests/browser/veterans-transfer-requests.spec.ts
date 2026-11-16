import { expect, test } from '@playwright/test';

/**
 * `/veterans/transfers` (route `veterans.transfers`, `routes/web.php:298`) is a routable page no
 * browser spec reached before this one (P3.7). The page owns three lifecycle tabs, one in-card pager
 * per tab, and a named empty state, so each of those has to be reached rather than assumed.
 *
 * The suite owns its data (KI-69): this spec creates nothing, so there is nothing to clean up in
 * `afterEach` — the empty-state assertions are the assertions of a database the harness just built,
 * and the row-count check at the end proves the run left it that way.
 */
test('lists transfer requests across the three lifecycle tabs and states its empty case per tab', async ({
    page,
}) => {
    await page.goto('/veterans/transfers');

    // The page renders as its own screen, not as a redirect to a Veteran's page.
    await expect(page).toHaveURL(/\/veterans\/transfers/);
    await expect(
        page.getByRole('heading', { name: /transfer/i }).first(),
    ).toBeVisible();

    // Three lifecycle tabs, each a real tab with a live count.
    const tablist = page.getByRole('tablist', { name: 'Transfer request states' });
    await expect(tablist).toBeVisible();

    const offered = tablist.getByRole('tab', { name: /^Offered/ });
    const completed = tablist.getByRole('tab', { name: /^Completed/ });
    const withdrawn = tablist.getByRole('tab', { name: /^Withdrawn/ });

    await expect(offered).toBeVisible();
    await expect(completed).toBeVisible();
    await expect(withdrawn).toBeVisible();

    // The first tab is the selected one on a fresh visit, and its empty state is reachable.
    await expect(offered).toHaveAttribute('aria-selected', 'true');
    await expect(page.getByTestId('no-transfers')).toContainText(
        'No offered transfer requests',
    );

    // Each other tab is reachable and states its own empty case — the copy names the tab, so a
    // tab that rendered the first tab's message would fail here.
    await completed.click();
    await expect(completed).toHaveAttribute('aria-selected', 'true');
    await expect(offered).toHaveAttribute('aria-selected', 'false');
    await expect(page.getByTestId('no-transfers')).toContainText(
        'No completed transfer requests',
    );

    await withdrawn.click();
    await expect(withdrawn).toHaveAttribute('aria-selected', 'true');
    await expect(page.getByTestId('no-transfers')).toContainText(
        'No withdrawn transfer requests',
    );

    // No pager renders while every tab is empty: the pager is bound to `last_page > 1`, and a
    // pager over zero rows would be a control that moves nothing.
    await expect(page.getByRole('button', { name: '1', exact: true })).toHaveCount(0);

    // The page created no rows, so the empty state is still the truth after the tab walk.
    await page.reload();
    await expect(page.getByTestId('no-transfers')).toBeVisible();
    await expect(page.locator('[data-transfer-row]')).toHaveCount(0);
});
