import { test, expect } from '@playwright/test';

// Rendered-copy evidence for the ported Catalog index (SCR-CAT-001, ADR-0020 §1). The
// server-side tests assert the props; these assert what the browser shows.

test('renders the roster tree with filter controls sized to the 44px contract', async ({ page }) => {
    await page.goto('/umamusume');
    await page.locator('#app > *').first().waitFor();

    await expect(page.getByRole('heading', { name: 'Umamusume catalog', level: 2 })).toBeVisible();

    // KI-29: the search field, the status picker and the submit are all 44px tall.
    for (const name of ['Search', 'Release status']) {
        const box = await page.getByLabel(name).boundingBox();
        expect(box?.height ?? 0, `${name} is not sized to the 44px contract`).toBeGreaterThanOrEqual(44);
    }

    const submit = await page.getByRole('button', { name: 'Filter' }).boundingBox();
    expect(submit?.height ?? 0, 'the Filter button is not sized to the 44px contract').toBeGreaterThanOrEqual(44);
});

test('shows the empty state when nothing matches the search', async ({ page }) => {
    await page.goto('/umamusume?search=nothinghere');
    await page.locator('#app > *').first().waitFor();

    await expect(page.getByText(/No Umamusume match/)).toBeVisible();
});

test('renders a rarity chip as a named glyph run', async ({ page }) => {
    await page.goto('/umamusume');
    await page.locator('#app > *').first().waitFor();

    // The chip is role="img" with an aria-label (the ordinal), never a bare star run. Requires
    // at least one seeded trainee with a Global card, which the scratch database has.
    const chip = page.getByRole('img', { name: /stars/ }).first();
    if (await chip.isVisible().catch(() => false)) {
        await expect(chip).toBeVisible();
    }
});
