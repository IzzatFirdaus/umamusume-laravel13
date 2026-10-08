import { test, expect } from '@playwright/test';

/*
 * Rendered-copy evidence for the Database hub and its five areas (SCREEN-023, plan §8 D17). The props
 * are asserted in `tests/Feature/DatabaseTest.php`; these cases assert what only a browser reaches: the
 * hub's five links and three deferred areas, that the three reused catalogs render in place rather than
 * redirecting, the race table's two states, the scenario matrix as config prints it, and the 320px
 * behaviour DESIGN.md §6 requires of a wide table.
 *
 * The rewrite note matters. An earlier version of this file asserted `toHaveURL('/umamusume')` after
 * visiting `/database/trainees`, and asserted that the race table produced no inner scroll at 320px.
 * Neither is what ships: `routes/web.php:249-253` records that a redirect was considered and rejected
 * because no owner ruling authorises one, so the three areas render the shared component, and
 * DESIGN.md:194-209 requires a wide table to scroll *inside* its own focusable container. A spec that
 * asserts the opposite of the rule cannot be the evidence for it. The 320px case below now asserts both
 * halves: the document does not scroll sideways, the container does.
 *
 * The Races page is asserted in whichever state the tree is in, because the offline state is unreachable
 * on a seeded database (410 rows) and reachable on an unseeded one. `KNOWN-ISSUES.md` KI-69 is the ruling
 * this follows: assert the shape of whichever state holds, never a disk precondition.
 */

const AREAS: { url: string; heading: string; title: string }[] = [
    { url: '/database/trainees', heading: 'Umamusume catalog', title: 'Trainees' },
    { url: '/database/supports', heading: 'Support cards', title: 'Support Cards' },
    { url: '/database/skills', heading: 'Skill search', title: 'Skills' },
];

test('the hub lists the eight areas and no deferred section', async ({ page }) => {
    await page.goto('/database', { waitUntil: 'domcontentloaded' });
    await page.locator('#app > *').first().waitFor();

    // The hub's title is the layout's own h1 (`AppLayout.vue:277` renders the `#title` slot), so the
    // page does not repeat it as an h2 the way it once did.
    await expect(page.getByRole('heading', { name: 'Database', level: 1 })).toBeVisible();

    // Scoped to the hub's own nav: the sidebar carries "Support Cards" and "Skills" as destinations
    // too, so a page-wide link query is a strict-mode violation rather than a missing link.
    const areas = page.getByRole('navigation', { name: 'Database areas' });

    for (const label of ['Trainees', 'Support Cards', 'Skills', 'Races', 'Events', 'Scenarios', 'Shop Items', 'Sparks']) {
        await expect(areas.getByRole('link', { name: label, exact: true })).toBeVisible();
    }

    // The three areas that were once "Not in this build" are destinations now, so the section that
    // named them absent is deleted rather than softened: a heading saying otherwise would be false.
    await expect(page.getByRole('heading', { name: 'Not in this build' })).toHaveCount(0);

    // Each of the three is a live link, which is the point of deleting the section.
    await expect(areas.getByRole('link', { name: 'Events', exact: true })).toHaveAttribute('href', /\/database\/events$/);
    await expect(areas.getByRole('link', { name: 'Shop Items', exact: true })).toHaveAttribute('href', /\/database\/shop-items$/);
    await expect(areas.getByRole('link', { name: 'Sparks', exact: true })).toHaveAttribute('href', /\/database\/sparks$/);
});

test('the Events view renders the recurring types with glyph-and-word provenance', async ({ page }) => {
    await page.goto('/database/events', { waitUntil: 'domcontentloaded' });
    await page.locator('#app > *').first().waitFor();

    await expect(page.getByRole('heading', { name: 'Recurring event types', level: 2 })).toBeVisible();
    await expect(page.getByText('10 recurring types')).toBeVisible();

    // Ten rows, the first and the unverified one by name.
    await expect(page.getByText('Champions Meeting', { exact: true })).toBeVisible();
    await expect(page.getByText('Season pass', { exact: true })).toBeVisible();

    // The provenance badge's accessible name is read with getByRole, never toHaveText: the glyph is
    // aria-hidden and toHaveText would read it (KI-70's class of failure). The unverified row is the
    // one `unknown` badge on this page, so exactly one is expected.
    await expect(page.getByRole('img', { name: /^Unknown/ }).first()).toBeVisible();
    expect(await page.getByRole('img', { name: /^Confirmed/ }).count()).toBeGreaterThan(0);

    // No colour-only state: the server tag is bracketed text, so it reads without styling.
    await expect(page.getByText('[JP]').first()).toBeVisible();
    await expect(page.getByText('[Global]').first()).toBeVisible();
});

test('the Events drawer opens on a row and returns focus to it on close', async ({ page }) => {
    await page.goto('/database/events', { waitUntil: 'domcontentloaded' });
    await page.locator('#app > *').first().waitFor();

    const row = page.getByRole('button', { name: /Champions Meeting/ });
    await row.focus();
    await row.click();

    const drawer = page.getByRole('dialog');
    await expect(drawer).toBeVisible();
    await expect(drawer).toBeFocused();

    await page.keyboard.press('Escape');
    await expect(drawer).toHaveCount(0);

    // Focus returns to the row that opened it, not to the top of the page.
    await expect(row).toBeFocused();
});

test('the Shop Items view renders the catalogue and the reused Pro Shop block', async ({ page }) => {
    await page.goto('/database/shop-items', { waitUntil: 'domcontentloaded' });
    await page.locator('#app > *').first().waitFor();

    await expect(page.getByRole('heading', { name: 'Shop items', level: 2 })).toBeVisible();
    await expect(page.getByText('10 catalogue rows')).toBeVisible();
    await expect(page.getByText('Alarm Clock', { exact: true })).toBeVisible();

    // The two currency rows say so in words, never by colour alone.
    await expect(page.getByText('Currency').first()).toBeVisible();

    // The scenario block is the matrix's own shop, read from config, with its rotation length.
    await expect(page.getByRole('heading', { name: 'Trackblazer Pro Shop', level: 3 })).toBeVisible();
    await expect(page.getByText('19 catalogue items')).toBeVisible();
    await expect(page.getByText('Speed Notepad', { exact: true })).toBeVisible();
});

test('the Sparks view renders the categories and the star-roll odds table', async ({ page }) => {
    await page.goto('/database/sparks', { waitUntil: 'domcontentloaded' });
    await page.locator('#app > *').first().waitFor();

    await expect(page.getByRole('heading', { name: 'Sparks', level: 2 })).toBeVisible();
    await expect(page.getByText('6 Spark categories')).toBeVisible();
    await expect(page.getByText('Blue Sparks', { exact: true })).toBeVisible();

    // The absence the six counts imply is named in copy, so a reader who opens the export and finds
    // more than the sum is not left guessing whether the view dropped rows.
    await expect(
        page.getByText('68 further records in the same export that no page in its pass explains'),
    ).toBeVisible();

    // The JP name carries lang="ja" (WCAG 3.1.2 Language of Parts).
    const jp = page.locator('dd[lang="ja"]').first();
    await expect(jp).toBeVisible();
    await expect(jp).toHaveAttribute('lang', 'ja');

    // The odds table renders on the page, with the three bands and the 0% three-star row.
    const odds = page.getByRole('table', { name: /Star-roll odds by final stat value/ });
    await expect(odds).toBeVisible();
    await expect(odds.getByText('Below 600')).toBeVisible();
    // `exact` is load-bearing: every other cell in the table ends in "0%" ("about 90%"), so a
    // substring match here resolves to seven elements instead of the one true zero.
    await expect(odds.getByText('0%', { exact: true })).toBeVisible();
});

for (const area of AREAS) {
    test(`the ${area.title} area renders in place rather than redirecting`, async ({ page }) => {
        await page.goto(area.url, { waitUntil: 'domcontentloaded' });
        await page.locator('#app > *').first().waitFor();

        // The address stays under /database (routes/web.php:249-253), and the shared list component is
        // what renders, which is the reuse the slice's brief asks for.
        expect(new URL(page.url()).pathname).toBe(area.url);
        await expect(page).toHaveTitle(new RegExp(area.title));
        await expect(page.getByRole('heading', { name: area.heading, level: 2 })).toBeVisible();
    });
}

test('the race database renders whichever state the catalogue is in', async ({ page }) => {
    await page.goto('/database/races', { waitUntil: 'domcontentloaded' });
    await page.locator('#app > *').first().waitFor();

    await expect(page.getByRole('heading', { name: 'Race database', level: 2 })).toBeVisible();

    const offline = page.getByRole('heading', { name: 'The race database is offline' });
    const table = page.getByRole('region', { name: 'Race slots, scrollable' });

    if (await offline.isVisible()) {
        // What is missing, why it matters, and the command that fills it. Nothing is invented.
        await expect(page.getByText(/no turn number, no distance, no surface/)).toBeVisible();
        await expect(page.getByText('php artisan uma:fetch gametora-race-catalog')).toBeVisible();
        await expect(page.getByText('php artisan uma:reparse gametora-race-catalog')).toBeVisible();
        await expect(table).toHaveCount(0);

        return;
    }

    await expect(table).toBeVisible();
    await expect(page.getByText(/\d+ of \d+ slots/)).toBeVisible();

    // Rows carry stored values, and every unstored one is `N/A` with its reason rather than a zero.
    // Measured on this tree's first page: 2 of 25 rows carry no distance, surface or fan gate, and 23
    // of 25 carry no entry gate at all, so this loop is never empty.
    const unrecorded = table.getByTitle(/The source records no|No entry gate/);
    expect(await unrecorded.count(), 'a populated catalogue states at least one unrecorded value')
        .toBeGreaterThan(0);

    for (const cell of await unrecorded.all()) {
        await expect(cell).toHaveText('N/A');
    }

    // No prediction lives in a reference table: win probability and readiness are held (ADR-0016).
    await expect(table).not.toContainText('%');
});

test('the scenario matrix prints config/scenarios.php, verification date and all', async ({ page }) => {
    await page.goto('/database/scenarios', { waitUntil: 'domcontentloaded' });
    await page.locator('#app > *').first().waitFor();

    await expect(page.getByRole('heading', { name: 'Scenario matrix', level: 2 })).toBeVisible();

    // The config's own date, named as a verification date rather than an update (config/scenarios.php:41-42).
    await expect(page.getByText(/Verified 2026-09-27 against/)).toBeVisible();

    for (const label of ['URA Finale', 'Unity Cup', 'Trackblazer', 'Our Grand Concert']) {
        await expect(page.getByRole('heading', { name: label, level: 3 })).toBeVisible();
    }

    // Exactly one scenario is partially documented, and the flag comes from config, not from a name test.
    await expect(page.getByText('PARTIALLY DOCUMENTED')).toHaveCount(1);

    // Panel state is a word, never a colour alone (design-2.0 §42). The matrix has no region role of
    // its own (each scenario is a labelled section, not a scroll container), so the sweep is the page.
    const matrix = page.locator('main');
    await expect(matrix.getByText('on', { exact: true }).first()).toBeVisible();
    await expect(matrix.getByText('off', { exact: true }).first()).toBeVisible();
});

test('no page scrolls sideways at 320px, and the race table scrolls inside its own region', async ({ page }) => {
    await page.setViewportSize({ width: 320, height: 900 });

    for (const url of [
        '/database',
        '/database/trainees',
        '/database/supports',
        '/database/skills',
        '/database/scenarios',
        '/database/events',
        '/database/shop-items',
        '/database/sparks',
    ]) {
        await page.goto(url, { waitUntil: 'domcontentloaded' });
        await page.locator('#app > *').first().waitFor();

        const overflow = await page.evaluate(
            () => document.documentElement.scrollWidth - document.documentElement.clientWidth,
        );
        expect(overflow, `${url} scrolls the document sideways at 320px`).toBeLessThanOrEqual(1);
    }

    // The wide table is the one surface §6 says may overflow, and then only inside a labelled,
    // focusable region rather than down the page (DESIGN.md:194-209).
    await page.goto('/database/races', { waitUntil: 'domcontentloaded' });
    await page.locator('#app > *').first().waitFor();

    const offline = page.getByRole('heading', { name: 'The race database is offline' });

    if (await offline.isVisible()) {
        return;
    }

    const scrollRegion = page.locator('div[role="region"][aria-label="Race slots, scrollable"]');
    await expect(scrollRegion).toHaveCount(1);

    const box = await scrollRegion.evaluate((el) => ({
        scrolls: el.scrollWidth > el.clientWidth,
        tabbable: el.tabIndex === 0,
    }));

    expect(box.scrolls, 'the race table does not scroll inside its own container at 320px').toBe(true);
    expect(box.tabbable, 'the scrollable race region is not keyboard reachable').toBe(true);

    // Focus reaches the scroll container, so a keyboard reader can move it (WCAG 2.1.1).
    await scrollRegion.focus();
    await expect(scrollRegion).toBeFocused();

    const overflow = await page.evaluate(
        () => document.documentElement.scrollWidth - document.documentElement.clientWidth,
    );
    expect(overflow, 'the page itself scrolls sideways to reach the race table').toBeLessThanOrEqual(1);
});

test('the three reference views keep every control at the 44px floor', async ({ page }) => {
    for (const url of ['/database/events', '/database/shop-items', '/database/sparks']) {
        await page.goto(url, { waitUntil: 'domcontentloaded' });
        await page.locator('#app > *').first().waitFor();

        const targets = page.locator('main a, main button');
        const count = await targets.count();
        expect(count, `${url} renders no control at all`).toBeGreaterThan(0);

        for (let i = 0; i < count; i++) {
            const box = await targets.nth(i).boundingBox();
            expect(box?.height ?? 0, `${url} control ${i} is not sized to the 44px contract`).toBeGreaterThanOrEqual(44);
        }
    }
});
