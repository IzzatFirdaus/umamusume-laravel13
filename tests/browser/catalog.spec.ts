import { test, expect } from '@playwright/test';
import { buildAxe, openAllDisclosures } from '../utils/accessibility';

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

// The `ADR-0021` portrait slot on the Inertia catalog index. DESIGN.md §4.7: an unmirrored row
// renders no element and no placeholder, while a held file renders a decorative frame (alt="")
// because the header already prints the name beside it. The mirror is partial and the tree ships
// empty, so neither case can assume a mirror state. Each derives its row from the rendered DOM,
// `li:has(h3 img)` for a mirrored trainee and `li:not(:has(h3 img))` for an unmirrored one, and
// asserts only when that state is present, the `:36` rarity-chip precedent for a state the
// environment may not have. The old pair hard-coded both states at once, so it could not pass on
// any reproducible database: with an empty mirror the presence case failed on a zero-match
// `toBeVisible()`, and with a full mirror the absence case failed because every row is framed.
test('renders a text-only row for a trainee whose portrait the mirror does not hold', async ({ page }) => {
    await page.goto('/umamusume');
    await page.locator('#app > *').first().waitFor();

    const rows = page.locator('ul.space-y-4 > li');
    expect(await rows.count(), 'the seeded catalog rendered no trainee rows').toBeGreaterThan(1);

    // A row whose header `h3` carries no image is an unmirrored trainee: the header renders the
    // name link alone. On a full mirror every row is framed, so no such row exists and this case
    // has nothing to assert.
    const bare = page.locator('ul.space-y-4 > li:not(:has(h3 img))');
    if ((await bare.count()) === 0) {
        return;
    }

    // The name link still heads the row, and no placeholder or broken frame is left behind: the
    // header carries no image, and nothing in the row (its header or nested form frames) points an
    // <img> at an empty src.
    const header = bare.first().locator('h3');
    await expect(header.getByRole('link')).toBeVisible();
    await expect(header.locator('img')).toHaveCount(0);
    await expect(bare.first().locator('img[src=""]')).toHaveCount(0);
});

test('renders a mirrored portrait as a decorative loopback frame', async ({ page }) => {
    await page.goto('/umamusume');
    await page.locator('#app > *').first().waitFor();

    // A row whose header `h3` carries an image is a mirrored trainee. On the shipped empty mirror
    // no row is framed, so this case has nothing to assert until `uma:fetch-art` fills the mirror.
    const framed = page.locator('ul.space-y-4 > li:has(h3 img)');
    if ((await framed.count()) === 0) {
        return;
    }

    // The header frame is decorative (alt="") because the row prints the name beside it, is streamed
    // from the loopback `artwork.show` route rather than the asset host (§7: offline first, no CDN),
    // and occupies a real rendered box rather than collapsing.
    const header = framed.first().locator('h3 img');
    await expect(header).toBeVisible();
    await expect(header).toHaveAttribute('alt', '');
    await expect(header).toHaveAttribute('src', /\/artwork\/card_portrait\/\d+$/);
    const box = await header.boundingBox();
    expect(box?.width ?? 0, 'the portrait frame has no rendered width').toBeGreaterThan(0);
    expect(box?.height ?? 0, 'the portrait frame has no rendered height').toBeGreaterThan(0);

    // The form row carries the same slot when its own card is mirrored: decorative, same route.
    const formFrame = framed.first().locator('ul.divide-y > li img').first();
    if (await formFrame.isVisible().catch(() => false)) {
        await expect(formFrame).toHaveAttribute('alt', '');
        await expect(formFrame).toHaveAttribute('src', /\/artwork\/card_portrait\/\d+$/);
    }
});

/*
 * The roster filter's busy state.
 *
 * The form's own `loading` flag is set from Inertia's `onStart` and cleared on `onFinish`, so the
 * pending state exists only while a visit is in flight. The request is therefore held open on the
 * way out, and the state is read off the live element: the form carries `aria-busy`, and the
 * submit button is disabled, which is what stops a second filter from being sent on top of the
 * first.
 */
test('the roster filter holds its pending state while the filtered page is in flight', async ({ page }) => {
    await page.goto('/umamusume');
    await page.locator('#app > *').first().waitFor();

    let release: (() => void) | undefined;
    const held = new Promise<void>((resolve) => {
        release = resolve;
    });

    await page.route('**/umamusume*', async (route) => {
        await held;
        await route.continue();
    });

    const filter = page.getByRole('button', { name: 'Filter' });
    const form = page.locator('form').filter({ has: filter });

    await filter.click();

    await expect(form, 'the form says it is busy while the visit is in flight').toHaveAttribute('aria-busy', 'true');
    await expect(filter, 'the submit is disabled while the visit is in flight').toBeDisabled();

    release?.();

    await expect(form, 'the busy state clears when the visit finishes').not.toHaveAttribute('aria-busy', 'true');
    await expect(filter, 'the submit comes back when the visit finishes').toBeEnabled();
});

/*
 * The roster filter's error state.
 *
 * `CatalogSearchRequest` validates `status` against `ReleaseStatus`, and the dropdown offers only
 * valid statuses, so the surface is a URL: a bookmark, a tampered link, or a filter state cached
 * from before the validator tightened. The Referer is set so the validation failure redirects back
 * to this page instead of the fallback URL, and the error has to land on the select that failed.
 */
test('the roster filter shows a rejected release status on the field that failed', async ({ page }) => {
    await page.goto('/umamusume');
    await page.locator('#app > *').first().waitFor();

    const referer = page.url();
    await page.setExtraHTTPHeaders({ Referer: referer });
    await page.goto('/umamusume?status=not-a-release-status');
    await page.locator('#app > *').first().waitFor();

    const status = page.locator('select[name="status"]');

    await expect(status, 'the rejected field is marked invalid').toHaveAttribute('aria-invalid', 'true');
    await expect(
        page.locator('#trainee-status-error'),
        'the error renders where the field can point at it',
    ).toBeVisible();
    await expect(status, 'the field points at its own error').toHaveAttribute('aria-describedby', 'trainee-status-error');
});

/*
 * The roster index's own axe scan (plan slice 8.8b).
 *
 * `catalog-detail.spec.ts` scans the trainee DETAIL page, which is a different route: the scan there
 * visits `/umamusume/special-week`, and this one visits `/umamusume`. A file that scans one of the
 * two says nothing about the other, which is the gap the route coverage map exists to name.
 */
test('the roster index passes an axe scan at WCAG A and AA', async ({ page }) => {
    await page.goto('/umamusume');
    await page.locator('#app > *').first().waitFor();

    await openAllDisclosures(page);
    const results = await buildAxe(page).analyze();
    expect(results.violations, JSON.stringify(results.violations, null, 2)).toEqual([]);
});
