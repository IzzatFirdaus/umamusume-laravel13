import { test, expect } from '@playwright/test';
import { buildAxe, openAllDisclosures } from '../utils/accessibility';

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
    { url: '/database/supports', heading: 'Support card catalog', title: 'Support Cards' },
    { url: '/database/skills', heading: 'Skill catalog', title: 'Skills' },
];

test('the hub lists the nine areas and no deferred section', async ({ page }) => {
    await page.goto('/database', { waitUntil: 'domcontentloaded' });
    await page.locator('#app > *').first().waitFor();

    // The hub's title is the page head's h1 (`PageHeader` renders inside the banner band). The HFW
    // names the destination "Reference data"; the rail's word for it is "Database", and the crumb
    // still says Database because that is where the Trainer is.
    await expect(page.getByRole('heading', { name: 'Reference data', level: 1 })).toBeVisible();

    // Scoped to the hub's own nav: the sidebar carries "Support Cards" and "Skills" as destinations
    // too, so a page-wide link query is a strict-mode violation rather than a missing link.
    const areas = page.getByRole('navigation', { name: 'Reference data', exact: true });

    // The shared subnav strip (database.html) sits between the page head and the tiles, and carries
    // every area plus the hub itself.
    await expect(page.getByRole('navigation', { name: 'Reference data areas' })).toBeVisible();

    // Nine, not eight: "Data sources" joined on 2026-10-22 (UX audit F-14). The page was registered
    // and served but had no door, so it was reachable only by typing the URL.
    for (const label of ['Trainees', 'Support Cards', 'Skills', 'Races', 'Events', 'Scenarios', 'Shop Items', 'Sparks', 'Data sources']) {
        await expect(areas.getByRole('link', { name: label, exact: true })).toBeVisible();
    }

    // One card per area, and the card IS the list item, so the list a screen reader hears is the
    // list the page shows.
    await expect(areas.getByRole('listitem')).toHaveCount(9);

    // The door the audit found missing, asserted on its destination rather than on existing at all.
    await expect(areas.getByRole('link', { name: 'Data sources', exact: true })).toHaveAttribute('href', /\/database\/sources$/);

    // The three areas that were once "Not in this build" are destinations now, so the section that
    // named them absent is deleted rather than softened: a heading saying otherwise would be false.
    await expect(page.getByRole('heading', { name: 'Not in this build' })).toHaveCount(0);

    // Each of the three is a live link, which is the point of deleting the section.
    await expect(areas.getByRole('link', { name: 'Events', exact: true })).toHaveAttribute('href', /\/database\/events$/);
    await expect(areas.getByRole('link', { name: 'Shop Items', exact: true })).toHaveAttribute('href', /\/database\/shop-items$/);
    await expect(areas.getByRole('link', { name: 'Sparks', exact: true })).toHaveAttribute('href', /\/database\/sparks$/);
});

// Plan 5.4 (Sweep 1): the roster printed every costume form of every trainee, which made a 67-row
// list unscannable. The trainee's own header stays visible; only the form rows fold, behind a count.
test('folds each trainee costume-form list behind a disclosure', async ({ page }) => {
    await page.goto('/database/trainees', { waitUntil: 'domcontentloaded' });
    await page.locator('#app > *').first().waitFor();

    // The trainee row is the `<li>` holding the disclosure; the form rows below it are plain `<li>`s.
    const trainee = page.locator('li').filter({ has: page.locator('details') }).first();
    const disclosure = trainee.locator('details');

    await expect(disclosure).toHaveCount(1);

    // The header is a sibling of the disclosure, not a child of it: folding the forms must not hide
    // her name, which is the list's scanning column.
    await expect(trainee.locator('h3')).toBeVisible();
    await expect(trainee.locator('details h3')).toHaveCount(0);

    // Closed by default, and the summary states how many forms are behind it.
    await expect(disclosure).not.toHaveAttribute('open', '');
    await expect(disclosure.locator('summary')).toHaveText(/^\d+ forms?$/);
    await expect(disclosure.locator('h4')).toBeHidden();

    await disclosure.locator('summary').click();
    await expect(disclosure).toHaveAttribute('open', '');
    await expect(disclosure.locator('h4').first()).toBeVisible();

    // The disclosure is the new structure on this page, so the scan runs with it open — the state
    // that exposes the most content. `buildAxe` scopes to `#app`, which is what makes it
    // deterministic (KI-63).
    await openAllDisclosures(page);
    const results = await buildAxe(page).analyze();
    expect(results.violations, JSON.stringify(results.violations, null, 2)).toEqual([]);
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
    // `confirmed` is a figure the tool is sure of, and it deliberately carries no mark: the
    // vocabulary marks only the three exception states. This assertion used to look for a Confirmed
    // badge and had been failing since that ruling landed.
    expect(await page.getByRole('img', { name: /^Confirmed/ }).count()).toBe(0);

    // No colour-only state: the server tag is bracketed text, so it reads without styling.
    await expect(page.getByText('[JP]').first()).toBeVisible();
    await expect(page.getByText('[Global]').first()).toBeVisible();

    // The recurring types are a table: a reader compares a cadence against another cadence, and a
    // table says which column a figure sits in. The server tags are chips in the servers column.
    const types = page.getByRole('table', { name: 'Recurring event types' });
    await expect(types).toBeVisible();
    await expect(types.getByRole('columnheader', { name: 'Cadence' })).toBeVisible();
    await expect(types.getByRole('columnheader', { name: 'Servers' })).toBeVisible();
    await expect(types.getByText('[JP]').first()).toBeVisible();
});

test('the Data sources page lists every dataset and names no repository path', async ({ page }) => {
    await page.goto('/database/sources', { waitUntil: 'domcontentloaded' });
    await page.locator('#app > *').first().waitFor();

    await expect(page.getByRole('heading', { name: 'Data sources', level: 1 })).toBeVisible();
    await expect(page.getByRole('heading', { name: 'Fetched', level: 2 })).toBeVisible();
    await expect(page.getByRole('heading', { name: 'Transcribed', level: 2 })).toBeVisible();

    // A transcribed dataset is named by subject, and the page carries no section number or file path.
    await expect(page.getByText('Recurring event types').first()).toBeVisible();
    await expect(page.getByText('Spark categories').first()).toBeVisible();
    await expect(page.locator('body')).not.toContainText('UMAMUSUME_REFERENCE');
    await expect(page.locator('body')).not.toContainText('§');

    // The reference views link here rather than printing the guide's path.
    await page.goto('/database/events', { waitUntil: 'domcontentloaded' });
    await page.locator('#app > *').first().waitFor();
    await expect(page.locator('body')).not.toContainText('UMAMUSUME_REFERENCE');
    await expect(page.getByRole('link', { name: 'Where this dataset comes from' })).toBeVisible();
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

    await expect(page.getByRole('heading', { name: 'Shop Items', level: 1 })).toBeVisible();
    await expect(page.getByText('10 catalogue rows')).toBeVisible();
    await expect(page.getByText('Alarm Clock', { exact: true })).toBeVisible();

    // The two currency rows say so in words, never by colour alone.
    await expect(page.getByText('Currency').first()).toBeVisible();

    // The scenario block is the matrix's own shop, read from config, with its rotation length.
    await expect(page.getByRole('heading', { name: 'Trackblazer Pro Shop', level: 3 })).toBeVisible();
    await expect(page.getByText('19 catalogue items')).toBeVisible();
    await expect(page.getByText('Speed Notepad', { exact: true })).toBeVisible();

    // The catalogue and the Pro Shop are two tables, and the price sits in the column it belongs
    // to rather than in a row of prose.
    const catalogue = page.getByRole('table', { name: 'Shop item catalogue' });
    await expect(catalogue).toBeVisible();
    await expect(catalogue.getByRole('columnheader', { name: 'Spend site' })).toBeVisible();
    await expect(catalogue.getByRole('columnheader', { name: 'Kind' })).toBeVisible();

    const proShop = page.getByRole('table', { name: 'Pro Shop catalogue items' });
    await expect(proShop).toBeVisible();
    await expect(proShop.getByRole('columnheader', { name: 'Cost (coins)' })).toBeVisible();
});

test('the Sparks view renders the categories and the star-roll odds table', async ({ page }) => {
    await page.goto('/database/sparks', { waitUntil: 'domcontentloaded' });
    await page.locator('#app > *').first().waitFor();

    await expect(page.getByRole('heading', { name: 'Sparks', level: 1 })).toBeVisible();
    await expect(page.getByText('6 Spark categories')).toBeVisible();
    await expect(page.getByText('Blue Sparks', { exact: true })).toBeVisible();

    // The absence the six counts imply is named in copy, so a reader who opens the export and finds
    // more than the sum is not left guessing whether the view dropped rows.
    await expect(
        page.getByText('68 further records in the same export that no page in its pass explains'),
    ).toBeVisible();

    // The JP name carries lang="ja" (WCAG 3.1.2 Language of Parts). It is a cell in the categories
    // table now, not a term in a definition list.
    const categories = page.getByRole('table', { name: 'Spark categories' });
    await expect(categories).toBeVisible();
    await expect(categories.getByRole('columnheader', { name: 'Records' })).toBeVisible();

    const jp = categories.locator('[lang="ja"]').first();
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

    // The canonical pager states the range: which slice of the whole this is, and how big the whole
    // is. The line it replaced said "410 of 410 slots" above a table holding 25 rows, which read as
    // the whole set rather than one page of it.
    await expect(page.locator('nav[aria-label="Pagination"] [role="status"]')).toContainText(
        /Showing \d+ to \d+ of \d+ slots/,
    );

    // Rows carry stored values, and every unstored one is `N/A` rather than a zero. The reason used to
    // sit in a per-cell `title`; the 12.1 sweep moved it out of the hover and into one note under the
    // table, because a reason carried only in a `title` is invisible on touch. Measured on this tree's
    // first page: 2 of 25 rows carry no distance, surface or fan gate, and 23 of 25 carry no entry gate.
    const cells = table.getByText('N/A', { exact: true });
    expect(await cells.count(), 'a populated catalogue states at least one unrecorded value')
        .toBeGreaterThan(0);

    await expect(page.getByText(/N\/A marks a field the source records nothing for/)).toBeVisible();

    // No prediction lives in a reference table: win probability and readiness are held (ADR-0016).
    await expect(table).not.toContainText('%');
});

test('the race database pager names the range it shows and moves a page', async ({ page }) => {
    await page.goto('/database/races', { waitUntil: 'domcontentloaded' });
    await page.locator('#app > *').first().waitFor();

    // The offline state has no table and no pager, so there is nothing to drive. KI-69: assert the
    // shape of whichever state holds, never a disk precondition.
    if (await page.getByRole('heading', { name: 'The race database is offline' }).isVisible()) {
        test.skip(true, 'The race catalogue is empty in this database, so there is no pager to drive.');
    }

    const status = page.locator('nav[aria-label="Pagination"] [role="status"]');
    await expect(status).toContainText(/Showing \d+ to \d+ of \d+ slots/);

    // The canonical pager is a button pair, not a set of links, so the PAGE owns the request. That is
    // the behaviour that changed: the pager this replaced was Inertia links, and this one is not.
    await page.getByRole('button', { name: 'Next' }).click();
    await expect(status).toContainText(/Page 2 of \d+/);
    await expect(page).toHaveURL(/[?&]page=2/);
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

    // Panel state is the word itself, so the state never rides on a glyph or on colour alone
    // (design-2.0 §42). A tick used to sit beside the word and an em dash stood for "off"; the
    // dash is banned in shipped copy (R-02, D-79) and the tick was decoration, so the word is
    // printed and the glyph pair is gone.
    const panels = page.getByRole('table', { name: /panels$/ }).first();
    await expect(panels).toBeVisible();
    await expect(panels.getByText('on', { exact: true }).first()).toBeVisible();
    // Some scenario has an off panel, and its word is in that table for a screen reader to read.
    await expect(page.getByText('off', { exact: true }).first()).toBeVisible();

    const caps = page.getByRole('table', { name: 'URA Finale stat caps' });
    await expect(caps).toBeVisible();
    await expect(caps.getByRole('columnheader', { name: 'Published cap' })).toBeVisible();
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

        // This sweep has the collapsed-disclosure hole KI-92 fixed: a control inside a closed
        // `<details>` has no box and reads as 0. No `<details>` sits on these three views today; when one
        // lands, move the sweep to `expectTapTargets` in `tests/utils/tap-targets.ts`.
        const targets = page.locator('main a, main button');
        const count = await targets.count();
        expect(count, `${url} renders no control at all`).toBeGreaterThan(0);

        for (let i = 0; i < count; i++) {
            const box = await targets.nth(i).boundingBox();
            expect(box?.height ?? 0, `${url} control ${i} is not sized to the 44px contract`).toBeGreaterThanOrEqual(44);
        }
    }
});

/*
 * The skills filter's busy state: the same idiom the roster and support-card filters use, read off
 * the live element while a visit is held open.
 */
test('the skills filter holds its pending state while the filtered page is in flight', async ({ page }) => {
    await page.goto('/database/skills', { waitUntil: 'domcontentloaded' });
    await page.locator('#app > *').first().waitFor();

    let release: (() => void) | undefined;
    const held = new Promise<void>((resolve) => {
        release = resolve;
    });

    await page.route('**/skills*', async (route) => {
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
});

/*
 * The skills filter's error state. `SkillSearchRequest` rejects a search longer than 120
 * characters, and the input has no maxlength of its own, so this one is reachable from the UI:
 * the message has to land on the field a Trainer has to shorten.
 */
test('the skills filter shows a rejected search on the field that failed', async ({ page }) => {
    await page.goto('/database/skills', { waitUntil: 'domcontentloaded' });
    await page.locator('#app > *').first().waitFor();

    await page.locator('#skill-search').fill('x'.repeat(121));
    await page.getByRole('button', { name: 'Filter' }).click();
    await page.locator('#app > *').first().waitFor();

    const search = page.locator('#skill-search');

    await expect(search, 'the rejected field is marked invalid').toHaveAttribute('aria-invalid', 'true');
    await expect(
        page.locator('#error-skill-search'),
        'the error renders where the field can point at it',
    ).toBeVisible();
    await expect(search, 'the field points at its own error').toHaveAttribute('aria-describedby', 'error-skill-search');
});

/*
 * The axe sweep for the Database area (plan slice 8.8b).
 *
 * One case per route, and each case is its own scan: a case that visited two routes and scanned
 * at the end would assert the LAST route it reached, so a scan that belonged to the first route
 * would be attributed to the second. The tree's own idiom is a dedicated scan case - `review.spec.ts`
 * and `accessibility.spec.ts` both carry one - and the coverage map that lands after this reads
 * these cases to say which routes are scanned.
 *
 * The scan itself is the suite's: `buildAxe` scopes to `#app`, and `openAllDisclosures` opens every
 * `<details>` first, because axe does not descend into a closed one (8.7).
 */
test('the database hub passes an axe scan at WCAG A and AA', async ({ page }) => {
    await page.goto('/database', { waitUntil: 'domcontentloaded' });
    await page.locator('#app > *').first().waitFor();

    await openAllDisclosures(page);
    const results = await buildAxe(page).analyze();
    expect(results.violations, JSON.stringify(results.violations, null, 2)).toEqual([]);
});

test('the Events view passes an axe scan at WCAG A and AA', async ({ page }) => {
    await page.goto('/database/events', { waitUntil: 'domcontentloaded' });
    await page.locator('#app > *').first().waitFor();

    await openAllDisclosures(page);
    const results = await buildAxe(page).analyze();
    expect(results.violations, JSON.stringify(results.violations, null, 2)).toEqual([]);
});

test('the race database passes an axe scan at WCAG A and AA', async ({ page }) => {
    await page.goto('/database/races', { waitUntil: 'domcontentloaded' });
    await page.locator('#app > *').first().waitFor();

    await openAllDisclosures(page);
    const results = await buildAxe(page).analyze();
    expect(results.violations, JSON.stringify(results.violations, null, 2)).toEqual([]);
});

test('the scenario matrix passes an axe scan at WCAG A and AA', async ({ page }) => {
    await page.goto('/database/scenarios', { waitUntil: 'domcontentloaded' });
    await page.locator('#app > *').first().waitFor();

    await openAllDisclosures(page);
    const results = await buildAxe(page).analyze();
    expect(results.violations, JSON.stringify(results.violations, null, 2)).toEqual([]);
});

test('the Shop Items view passes an axe scan at WCAG A and AA', async ({ page }) => {
    await page.goto('/database/shop-items', { waitUntil: 'domcontentloaded' });
    await page.locator('#app > *').first().waitFor();

    await openAllDisclosures(page);
    const results = await buildAxe(page).analyze();
    expect(results.violations, JSON.stringify(results.violations, null, 2)).toEqual([]);
});

test('the skills area passes an axe scan at WCAG A and AA', async ({ page }) => {
    await page.goto('/database/skills', { waitUntil: 'domcontentloaded' });
    await page.locator('#app > *').first().waitFor();

    await openAllDisclosures(page);
    const results = await buildAxe(page).analyze();
    expect(results.violations, JSON.stringify(results.violations, null, 2)).toEqual([]);
});

test('the data sources page passes an axe scan at WCAG A and AA', async ({ page }) => {
    await page.goto('/database/sources', { waitUntil: 'domcontentloaded' });
    await page.locator('#app > *').first().waitFor();

    await openAllDisclosures(page);
    const results = await buildAxe(page).analyze();
    expect(results.violations, JSON.stringify(results.violations, null, 2)).toEqual([]);
});

test('the Sparks view passes an axe scan at WCAG A and AA', async ({ page }) => {
    await page.goto('/database/sparks', { waitUntil: 'domcontentloaded' });
    await page.locator('#app > *').first().waitFor();

    await openAllDisclosures(page);
    const results = await buildAxe(page).analyze();
    expect(results.violations, JSON.stringify(results.violations, null, 2)).toEqual([]);
});

test('the support-card area passes an axe scan at WCAG A and AA', async ({ page }) => {
    await page.goto('/database/supports', { waitUntil: 'domcontentloaded' });
    await page.locator('#app > *').first().waitFor();

    await openAllDisclosures(page);
    const results = await buildAxe(page).analyze();
    expect(results.violations, JSON.stringify(results.violations, null, 2)).toEqual([]);
});
