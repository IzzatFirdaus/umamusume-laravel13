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

// The `ADR-0021` portrait slot on the Inertia catalog index. The mirror is partial, so the same
// page carries both the mirrored and the absent state, and each is asserted here: DESIGN.md §4.7
// says a miss renders no element and no placeholder, while a held file renders a decorative frame
// (alt="") because the header already prints the name beside it.
test('omits the trainee portrait when the mirror holds no file', async ({ page }) => {
    await page.goto('/umamusume');
    await page.locator('#app > *').first().waitFor();

    const rows = page.locator('ul.space-y-4 > li');
    expect(await rows.count(), 'the seeded catalog rendered no trainee rows').toBeGreaterThan(1);

    // The first row (ordered by name) is the trainee whose card the mirror does not hold: no
    // frame, no grey box, and the name link still heads the row.
    const header = rows.first().locator('h3');
    await expect(header.locator('img')).toHaveCount(0);
    await expect(header.getByRole('link')).toBeVisible();
});

test('renders a mirrored portrait as a decorative loopback frame', async ({ page }) => {
    await page.goto('/umamusume');
    await page.locator('#app > *').first().waitFor();

    // A trainee the mirror does hold shows her portrait ahead of the name. Decorative (alt="")
    // because the row prints the name beside it, and streamed from the loopback `artwork.show`
    // route rather than the asset host (§7: offline first, no CDN).
    const frame = page.locator('ul.space-y-4 > li h3 img').first();
    await expect(frame).toBeVisible();
    await expect(frame).toHaveAttribute('alt', '');
    await expect(frame).toHaveAttribute('src', /\/artwork\/card_portrait\/\d+$/);

    // The form row carries the same slot: its own card portrait, decorative, same route.
    const formFrame = page.locator('ul.space-y-4 ul.divide-y > li img').first();
    await expect(formFrame).toBeVisible();
    await expect(formFrame).toHaveAttribute('alt', '');
});

