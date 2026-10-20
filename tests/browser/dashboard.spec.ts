import { test, expect } from '@playwright/test';
import { deleteRun } from '../utils/delete-run';
import { recordTurns } from '../utils/record-turns';

/*
 * Rendered-copy, state and accessibility evidence for SCREEN-001, the 2.0 landing screen
 * (docs/proposals/screen-spec-2.0.md; ADR-0020 §1). `DashboardTest` asserts the resolved props;
 * these assert what the browser shows and what only a browser can reach: the empty state's words,
 * the data-status badge, the 44px sweep, the keyboard path into the run, `lang="ja"` on the
 * Japanese name, and the page's own loading and error regions.
 *
 * Fixture strategy. The seeded scratch database holds zero training runs, which is the precondition
 * `playwright.config.ts`, `legacy.spec.ts` and `runs.spec.ts` all state, so the empty-career state is
 * asserted as itself. The active-career case creates its own run through the create form and deletes
 * it over HTTP in `afterEach` (`tests/utils/delete-run.ts`), so this spec leaves the database
 * exactly as it found it (the `run-detail.spec.ts` pattern).
 *
 * What is not browser-asserted, and why: the recorded Energy and the career position on the card.
 * The turn count IS reached now: the rail case logs its turns over `runs.turns.store` with
 * `tests/utils/record-turns.ts`, the route-level fixture the career specs already use, because no
 * rendered surface creates a turn any more. `DashboardTest` pins the props that drive the rest,
 * including the position arithmetic on the client's calendar grid. A newly created run has logged
 * nothing, so the states this spec can reach on one are the absent ones, which is where the copy
 * matters most.
 *
 * Axe coverage is provided by `tests/browser/accessibility.spec.ts`: `@axe-core/playwright` is installed
 * and scans pages against `wcag2a`, `wcag2aa`, and `wcag21aa`; this spec retains the hand-rolled checks
 * for target size, keyboard path, focus order, reflow and console errors.
 */

const TRAINEE = 'Agnes Digital';
const TRAINEE_JA = 'アグネスデジタル';
const EMPTY_CAREER = 'No active career. Start a new career.';

const createdRunUrls: string[] = [];

test.afterEach(async ({ page }) => {
    for (const url of createdRunUrls.splice(0)) {
        await deleteRun(page, url);
    }
});

/** One `Active` run on the seeded trainee, created through the app's own form. */
async function createActiveRun(page: import('@playwright/test').Page, scenario: string): Promise<string> {
    await page.goto('/training-runs/create');
    await page.locator('#app > *').first().waitFor();

    await page.locator('#trainee-combobox').fill(TRAINEE);
    await page.keyboard.press('Enter');
    await page.selectOption('select[name="scenario"]', { value: scenario });
    await page.getByRole('button', { name: 'Create run' }).click();
    await page.waitForURL(/\/training-runs\/\d+\/cockpit$/);

    const url = page.url();
    createdRunUrls.push(url);

    return url;
}

test('states the empty career in words a Trainer can act on', async ({ page }) => {
    await page.goto('/');
    await page.locator('#app > *').first().waitFor();

    // design-2.0 §29: what is missing, why it matters, what to do. The brief's own sentence is the
    // first line; the second says what a career is for, so the panel is not just a refusal.
    await expect(page.getByText(EMPTY_CAREER)).toBeVisible();
    await expect(page.getByText(/Start a run to track turns, Energy and race record/)).toBeVisible();
    await expect(page.getByRole('link', { name: 'Start a new career' })).toBeVisible();

    // The snapshot door is the other way in. It now has two faces: the empty state's own ghost
    // button (asserted on the next line) and the Quick Actions paragraph link that has carried it
    // since R2-04. The unscoped role query would resolve to both, so this one reads the card.
    await expect(
        page.getByRole('region', { name: 'ACTIVE CAREER' }).getByRole('link', { name: 'Import an existing career' }),
    ).toBeVisible();
    await expect(page.getByRole('link', { name: 'Import an existing career' })).toHaveCount(2);

    // Paradox of the Active User (plan §13): no onboarding wall. The rest of the page is usable on
    // first load, so the quick actions and the data panels are present with no career at all.
    const quickActions = page.getByRole('navigation', { name: 'Quick actions' });
    // The card now carries only destinations the sidebar does not, so the sweep names those.
    for (const label of ['Review queue', 'Open settings']) {
        await expect(quickActions.getByRole('link', { name: label })).toBeVisible();
    }
    // `New Career` and the two sidebar destinations are gone at the source, in
    // `DashboardController::quickActions()`: with no career live, Active Career's own primary is the
    // only start-a-career door on the screen (Hick, Occam).
    await expect(quickActions.getByRole('link', { name: 'New Career' })).toHaveCount(0);

    // Von Restorff: one filled primary on the screen, and in this state it is the career door.
    // Scoped to `main`, because the shell's own chrome carries no button variant.
    await expect(page.locator('#main [data-variant="primary"]')).toHaveCount(1);

    // The two panels with no source name the absence rather than rendering an empty box.
    await expect(page.getByText('No veterans yet.')).toBeVisible();
    await expect(page.getByText('Recent builds: none yet.')).toBeVisible();
});

test('reads the data-status badge and the named ruleset, and the freshness line on the page header', async ({ page }) => {
    await page.goto('/');
    await page.locator('#app > *').first().waitFor();

    const status = page.getByRole('region', { name: 'DATA STATUS' });

    // Glyph plus text: the bullet is `aria-hidden` and the word "Current" carries the state, so
    // nothing here is shown through colour alone (WCAG 1.4.1).
    await expect(status.getByText('GLOBAL DATA')).toBeVisible();
    await expect(status.getByText('Current', { exact: true })).toBeVisible();
    // The freshness line is page-level now, not card-level (2026-10-21 operator ruling). It is
    // asserted on the page and asserted absent from the card, so the move is pinned both ways.
    await expect(page.getByText('Verified 2026-09-27')).toBeVisible();
    await expect(status.getByText(/Verified/)).toHaveCount(0);

    // `app.ruleset` is null, so the row is N/A with its reason on the element rather than a version
    // this tool does not hold (design-2.0 §48). R2-19 heads the row for the fact it prints: the word
    // the refusal title beside it already uses is `version`, and the heading now agrees with it. The
    // second line is the retired wording, anchored to the paragraph's leading indentation.
    await expect(status.getByText(/^\s*Ruleset version:/)).toBeVisible();
    await expect(status.getByText(/^\s*Ruleset:/)).toHaveCount(0);
    await expect(status.locator('[title*="No source defines a Global ruleset version"]')).toHaveText('N/A');
});

test('sizes the dashboard controls to the 44px contract', async ({ page }) => {
    await page.goto('/');
    await page.locator('#app > *').first().waitFor();

    // WCAG 2.2 SC 2.5.8, floor 44px (plan §12.1). The same sweep `skills.spec.ts` and
    // `legacy.spec.ts` run, repeated here because this screen's controls are new.
    const targets = [
        page.getByRole('link', { name: 'Start a new career' }),
        // "New Career" is not in this list on purpose: with no career live, Quick Actions drops it so
        // the screen offers one start-a-career door rather than two, and the sweep below measures the
        // doors that remain.
        page.getByRole('navigation', { name: 'Quick actions' }).getByRole('link', { name: 'Review queue' }),
        page.getByRole('navigation', { name: 'Quick actions' }).getByRole('link', { name: 'Open settings' }),
        // The panel's door is the Veteran library now: the panel lists the library's rows and its
        // empty state names the Result screen's Save Veteran as the filing step.
        page.getByRole('link', { name: 'Open the Veteran library' }),
    ];

    for (const target of targets) {
        const box = await target.boundingBox();
        expect(box?.height ?? 0, 'a dashboard control is not sized to the 44px contract').toBeGreaterThanOrEqual(44);
    }
});

test('names the active career and resumes it from the keyboard', async ({ page }) => {
    await createActiveRun(page, 'unity_cup');

    await page.goto('/');
    await page.locator('#app > *').first().waitFor();

    const card = page.getByRole('region', { name: 'ACTIVE CAREER' });

    // The trainee, with her Japanese name in its own language (WCAG 3.1.2).
    await expect(card.getByText(TRAINEE)).toBeVisible();
    const japanese = card.locator(`[lang="ja"]`);
    await expect(japanese).toHaveText(TRAINEE_JA);

    // The scenario's label from the matrix, never its storage key (D-240).
    await expect(card.getByText('Unity Cup')).toBeVisible();

    // A brand-new run has logged nothing, so the position and Energy are absent states: N/A with a
    // reason, never a zero and never Early January (D-220).
    await expect(card.getByTitle(/not standing anywhere on the calendar yet/)).toHaveText('N/A');
    await expect(card.getByTitle(/No logged turn has recorded Energy/)).toHaveText('N/A');

    // Team Rank has its own row now (Slice 1.1), read through the model's one owner
    // (`latestTeamRank()`): a brand-new run has nothing reported, so it is N/A with the reason
    // attached. Unity Cup's config widget IS the rank, so the resource slot defers to this row
    // rather than printing a second, permanently-absent Team Rank cell beside it.
    await expect(card.getByText('Team Rank')).toBeVisible();
    await expect(card.getByTitle(/No logged turn has reported a team rank yet/)).toHaveText('N/A');

    // R2-04: this is the state the snapshot door exists for. A Trainer already partway through a
    // career in the client is the one who has a career to resume here, so the door has to be on the
    // screen they land on rather than only on the empty one. It is not one of the resume card's own
    // controls, so it is read from the page.
    const door = page.getByRole('link', { name: 'Import an existing career' });
    await expect(door).toBeVisible();
    await expect(door).toHaveAttribute('href', '/career/snapshot');

    // The primary action, reached and taken by keyboard alone.
    const resume = card.getByRole('link', { name: 'Resume Career' });
    const box = await resume.boundingBox();
    expect(box?.height ?? 0, 'Resume Career is not sized to the 44px contract').toBeGreaterThanOrEqual(44);

    await resume.focus();
    await expect(resume).toBeFocused();
    await page.keyboard.press('Enter');
    // Resuming continues the career, so it lands on the Cockpit (SCREEN-009), not the record sheet.
    await page.waitForURL(/\/cockpit$/);
});

test('weight-emphasizes the active career name over the other card headings', async ({ page }) => {
    await createActiveRun(page, 'unity_cup');

    await page.goto('/');
    await page.locator('#app > *').first().waitFor();

    const card = page.getByRole('region', { name: 'ACTIVE CAREER' });

    // Von Restorff by weight, not by restructure: the trainee's name is the largest thing in the
    // card, and the grid it sits in is the one the two-column layout already established.
    const name = card.getByText(TRAINEE);
    await expect(name).toHaveClass(/text-3xl/);
    await expect(name).toHaveClass(/font-bold/);

    // The Japanese name scales with it and stays subordinate: it is muted and not bold.
    const japanese = card.locator('[lang="ja"]');
    await expect(japanese).toHaveClass(/text-xl/);
    await expect(japanese).not.toHaveClass(/font-bold/);

    // The card's own heading and the other two cards' headings do not move with it: the
    // emphasis is the name inside the card, not a louder set of headings.
    await expect(card.getByRole('heading', { name: 'Active Career' })).toHaveClass(/text-lg/);
    for (const region of ['QUICK ACTIONS', 'DATA STATUS']) {
        await expect(page.getByRole('region', { name: region }).getByRole('heading')).toHaveClass(/text-lg/);
    }
});

test('reads the career progress as one mark per career turn', async ({ page }) => {
    const runUrl = await createActiveRun(page, 'unity_cup');
    const runId = /\/training-runs\/(\d+)/.exec(runUrl)?.[1];

    expect(runId, 'the created run carries no id').toBeDefined();

    // Three logged turns, so the rail has filled marks and a mark to stand on. They go over the
    // route that owns the write, because no rendered surface creates a turn any more.
    await recordTurns(page, runId as string, 3);

    await page.goto('/');
    await page.locator('#app > *').first().waitFor();

    const rail = page.locator('ol[aria-label="Career progress"]');

    // One mark per career turn. The career is three career years of the client's 24-turn grid, so
    // the rail carries seventy-two marks and not the twenty-four of a single year: a bar measured
    // against one year read as a hundred percent from turn twenty-four onward and then stopped.
    await expect(rail.getByRole('listitem')).toHaveCount(72);

    // The logged turns are the filled marks, and the last of them is where the career stands.
    await expect(page.locator('ol[aria-label="Career progress"] li.bg-primary-action')).toHaveCount(3);
    await expect(rail.getByRole('listitem').nth(2)).toHaveAttribute('aria-current', 'step');
    await expect(rail.getByRole('listitem').nth(1)).not.toHaveAttribute('aria-current', 'step');

    // The text names the position, and the turns left are a real signal rather than decoration.
    await expect(page.getByText('Turn 3 of 72')).toBeVisible();
    await expect(page.getByText('69 turns to go')).toBeVisible();
});

test('names the multi-career count only when a second career is live', async ({ page }) => {
    // Zero runs: the spec's own precondition, and the notice renders nothing.
    await page.goto('/');
    await page.locator('#app > *').first().waitFor();
    await expect(page.getByText(/careers are active/)).toHaveCount(0);

    // One active career: still nothing new on the page.
    await createActiveRun(page, 'unity_cup');
    await page.goto('/');
    await page.locator('#app > *').first().waitFor();
    await expect(page.getByText(/careers are active/)).toHaveCount(0);

    // Two live careers: the count names itself and hands over the full list. The featured pick
    // stays the newest run, so the card still shows the second run's trainee.
    await createActiveRun(page, 'ura_finale');
    await page.goto('/');
    await page.locator('#app > *').first().waitFor();

    const notice = page.getByRole('region', { name: 'ACTIVE CAREER' }).getByText(/careers are active/);
    await expect(notice).toBeVisible();
    // `route()` hands the page an absolute URL, so the assertion is the destination, not the origin.
    await expect(notice.getByRole('link', { name: /^View all careers/ })).toHaveAttribute('href', /\/training-runs$/);
    await expect(page.getByRole('region', { name: 'ACTIVE CAREER' }).getByText(TRAINEE)).toBeVisible();
});

// The case above cannot see this one: its regex is plural-only (`/careers are active/`), so
// "1 career are active." matched nothing and it passed for the wrong reason while the notice was
// still rendering for a single career. This asserts the notice's ABSENCE instead, which is what the
// section's own comment already claimed ("One career renders nothing here").
test('renders no multi-career notice while exactly one career is live', async ({ page }) => {
    await createActiveRun(page, 'unity_cup');

    await page.goto('/');
    await page.locator('#app > *').first().waitFor();

    const card = page.getByRole('region', { name: 'ACTIVE CAREER' });

    // The card still features the one career it has...
    await expect(card.getByRole('link', { name: 'Resume career' })).toBeVisible();

    // ...and the count-and-link line is not in the DOM at all, rather than reading "1 career are active."
    await expect(card.getByRole('link', { name: /^View all careers/ })).toHaveCount(0);
    await expect(card.getByText(/careers? (are|is) active/)).toHaveCount(0);
});

test('reflows at 320px, logs no console error, and renders under reduced motion', async ({ page }) => {
    const consoleErrors: string[] = [];
    page.on('console', (message) => {
        if (message.type() === 'error') {
            consoleErrors.push(message.text());
        }
    });
    page.on('pageerror', (error) => consoleErrors.push(error.message));

    // WCAG 1.4.10 Reflow: at 320px the document must not scroll in two dimensions. `legacy.spec.ts`
    // runs the same check on its compare table; this screen has no scrollable region of its own, so
    // the document is the whole claim.
    await page.setViewportSize({ width: 320, height: 720 });
    await page.emulateMedia({ reducedMotion: 'reduce' });
    await page.goto('/');
    await page.locator('#app > *').first().waitFor();

    const overflow = await page.evaluate(
        () => document.documentElement.scrollWidth <= document.documentElement.clientWidth + 1,
    );
    expect(overflow, 'the document scrolls horizontally at 320px').toBe(true);

    // WCAG 2.3.3 / the plan's §12 reduced-motion path: the screen is fully usable with the OS
    // preference set. The MOTION dial is 1, so this asserts the page renders and its primary action
    // is reachable rather than that a specific animation was suppressed.
    await expect(page.getByRole('link', { name: 'Start a new career' })).toBeVisible();
    await expect(page.getByRole('navigation', { name: 'Quick actions' })).toBeVisible();

    expect(consoleErrors, `console errors: ${consoleErrors.join(' | ')}`).toEqual([]);
});

test('announces a visit in flight and takes over the failure state', async ({ page }) => {
    await page.goto('/');
    await page.locator('#app > *').first().waitFor();

    // Loading: hold the response so the in-flight state is observable rather than raced. This page's
    // only user-initiated async action is following one of its links (ADR-0007), so the state is a
    // status line rather than a skeleton (design-2.0 §30).
    await page.route('**/career/setup/scenario', async (route) => {
        await new Promise((resolve) => setTimeout(resolve, 1000));
        await route.continue();
    });

    // The empty-career primary is the door to that route now; Quick Actions no longer repeats it.
    await page.getByRole('link', { name: 'Start a new career' }).click();
    // The shell's flash region is always mounted (WCAG 4.1.3), so the assertion targets the region
    // that is saying something, not the only one.
    await expect(page.getByRole('status').filter({ hasText: 'Loading…' })).toHaveText('Loading…');
    await page.waitForURL(/\/career\/setup\/scenario$/);

    await page.unroute('**/career/setup/scenario');

    // Error: a response Inertia cannot read fires `invalid`, and the page's own alert is the surface
    // because the listener suppresses Inertia's default modal.
    await page.goto('/');
    await page.locator('#app > *').first().waitFor();

    await page.route('**/review', (route) =>
        route.fulfill({ status: 200, contentType: 'text/html', body: '<html><body>not an Inertia response</body></html>' }),
    );

    await page.getByRole('navigation', { name: 'Quick actions' }).getByRole('link', { name: 'Review queue' }).click();
    await expect(page.getByRole('alert')).toContainText('That page could not be loaded');
});

test('honours the dashboard layout preference: bento by default, focused single-column', async ({ page }) => {
    await page.goto('/');
    await page.locator('#app > *').first().waitFor();

    // Default is bento: the grid carries the bento layout and the four always-present regions show.
    const grid = page.locator('[data-layout]');
    await expect(grid).toHaveAttribute('data-layout', 'standard');
    for (const name of ['ACTIVE CAREER', 'QUICK ACTIONS', 'RECENT VETERANS', 'DATA STATUS']) {
        await expect(page.getByRole('region', { name })).toBeVisible();
    }

    // Focused: set it through Settings, then read it back here. The grid becomes single-column at
    // max-w-3xl (DL.1, DL.4).
    await page.goto('/preferences');
    await page.locator('#app > *').first().waitFor();
    await page.getByRole('radio', { name: 'Compact' }).check();
    await page.getByRole('button', { name: 'Save preferences' }).click();
    await expect(page.getByRole('status')).toHaveText('Preferences saved.');

    await page.goto('/');
    await page.locator('#app > *').first().waitFor();
    const focused = page.locator('[data-layout]');
    await expect(focused).toHaveAttribute('data-layout', 'compact');
    await expect(focused).toHaveClass(/grid-cols-1/);
    await expect(focused).toHaveClass(/max-w-3xl/);

    // Reset to Bento so the suite leaves the shared store as it found it.
    await page.goto('/preferences');
    await page.locator('#app > *').first().waitFor();
    await page.getByRole('radio', { name: 'Standard', exact: true }).check();
    await page.getByRole('button', { name: 'Save preferences' }).click();
    await expect(page.getByRole('status')).toHaveText('Preferences saved.');

    await page.goto('/');
    await page.locator('#app > *').first().waitFor();
    await expect(page.locator('[data-layout]')).toHaveAttribute('data-layout', 'standard');
});

test('renders the wide shell when Wide is selected', async ({ page }) => {
    await page.goto('/preferences');
    await page.locator('#app > *').first().waitFor();
    await page.getByRole('radio', { name: 'Wide', exact: true }).check();
    await page.getByRole('button', { name: 'Save preferences' }).click();
    await expect(page.getByRole('status')).toHaveText('Preferences saved.');

    await page.goto('/');
    await page.locator('#app > *').first().waitFor();
    const wide = page.locator('[data-layout]');
    await expect(wide).toHaveAttribute('data-layout', 'wide');
    await expect(wide).toHaveClass(/grid-cols-12/);
    // The grid follows the shell's own cap instead of capping itself at the standard measure: the
    // shell's `main` is the one that carries the layout width (2026-10-21 operator ruling).
    await expect(wide).not.toHaveClass(/max-w-6xl/);
    await expect(page.locator('#main')).toHaveClass(/max-w-shell-wide/);

    // Reset to Standard so the suite leaves the shared store as it found it.
    await page.goto('/preferences');
    await page.locator('#app > *').first().waitFor();
    await page.getByRole('radio', { name: 'Standard', exact: true }).check();
    await page.getByRole('button', { name: 'Save preferences' }).click();
    await expect(page.getByRole('status')).toHaveText('Preferences saved.');
});

/*
 * The shell measure, the bento shape and the per-card void at 1920 (DESIGN.md §2.3, owner amendment
 * 2026-10-15). Each width band is written as a band rather than a `>=` floor on purpose: a `>=` also
 * passes when the cap fails to compile and `main` runs uncapped to the full 1,664px column, which is
 * the defect the rule exists to prevent. Compact is measured on `#main`: with the rename the shell
 * itself carries the 3xl cap, so `main` and the grid are both 768px.
 */
test('measures the three shell widths, the bento shape and the card voids at a 1920 viewport', async ({ page }, testInfo) => {
    await page.setViewportSize({ width: 1920, height: 1080 });
    const main = page.locator('#main');

    async function mainWidth(): Promise<number> {
        const box = await main.boundingBox();

        return box?.width ?? 0;
    }

    /*
     * The void inside each region: the distance from a card's last child to its own content-box
     * bottom. This is the "no stretched box" measure. A grid that equalises a row by stretching the
     * shorter cards, or a `min-h-*` that stretches them to the shell, both show up here before they
     * show up in a page-height number, because the defect is a border around empty space rather than
     * a short page.
     */
    async function maxCardVoid(): Promise<number> {
        return page.evaluate(() => {
            const cards = [...document.querySelectorAll('[data-dashboard-root] > [role="region"]')];

            return Math.max(
                ...cards.map((c) => {
                    const kids = [...c.children];
                    const last = kids[kids.length - 1];

                    if (last === undefined) {
                        return 0;
                    }

                    const contentBottom =
                        c.getBoundingClientRect().bottom - Number.parseFloat(getComputedStyle(c).paddingBottom);

                    return Math.round(contentBottom - last.getBoundingClientRect().bottom);
                }),
            );
        });
    }

    async function rootHeight(): Promise<number> {
        return page.evaluate(() => (document.querySelector('[data-dashboard-root]') as HTMLElement | null)?.offsetHeight ?? 0);
    }

    /** The first region's width as a percentage of the grid: `col-span-8` of 12 is 66. */
    async function firstCardShare(): Promise<number> {
        return page.evaluate(() => {
            const grid = document.querySelector('[data-dashboard-root]');
            const first = document.querySelector('[data-dashboard-root] > [role="region"]');

            if (grid === null || first === null) {
                return 0;
            }

            return Math.round((first.getBoundingClientRect().width / grid.getBoundingClientRect().width) * 100);
        });
    }

    /** The tallest `dl` stat cell inside a named region, in px. */
    async function tallestCell(heading: string): Promise<number> {
        return page.evaluate((name) => {
            const cards = [...document.querySelectorAll('[data-dashboard-root] > [role="region"]')];
            const card = cards.find((c) => (c.querySelector('h2, h3')?.textContent ?? '').trim() === name);
            const cells = card === undefined ? [] : [...card.querySelectorAll('dl')];

            return cells.length === 0 ? 0 : Math.max(...cells.map((d) => Math.round(d.getBoundingClientRect().height)));
        }, heading);
    }

    // Standard is the default the suite's scratch database carries.
    await page.goto('/');
    await page.locator('#app > *').first().waitFor();
    await expect(main).toHaveClass(/max-w-6xl/);
    const standard = await mainWidth();
    expect(standard).toBeGreaterThanOrEqual(1100);
    expect(standard).toBeLessThanOrEqual(1200);
    // The bento shape, not a single-column stack: 12 tracks, first region eight of them.
    await expect(page.locator('[data-dashboard-root]')).toHaveClass(/xl:grid-cols-12/);
    expect(await firstCardShare()).toBeGreaterThanOrEqual(55);
    expect(await firstCardShare()).toBeLessThanOrEqual(70);
    expect(await maxCardVoid()).toBeLessThanOrEqual(24);
    expect(await tallestCell('Data Status')).toBeGreaterThanOrEqual(88);
    // No Active Career stat-cell assertion here: this screen's career strip is `v-if` on an active
    // run and the suite's scratch database holds none, so a check on it would skip on every run.
    // `names the active career and resumes it from the keyboard` owns the with-career state.
    await page.screenshot({ path: testInfo.outputPath('dashboard-standard-1920.png'), fullPage: true });

    await page.goto('/preferences');
    await page.locator('#app > *').first().waitFor();
    await page.getByRole('radio', { name: 'Wide', exact: true }).check();
    await page.getByRole('button', { name: 'Save preferences' }).click();
    await expect(page.getByRole('status')).toHaveText('Preferences saved.');

    await page.goto('/');
    await page.locator('#app > *').first().waitFor();
    const wide = await mainWidth();
    expect(wide).toBeGreaterThanOrEqual(1450);
    // The grid fills the shell's content box, so the two measure the same width. Before the
    // 2026-10-21 ruling the grid capped itself at the standard measure and sat 320px inside the
    // wide shell, with the page's right-hand side empty. Measured at 1920 on purpose: at the
    // default 1280 viewport the shell is narrower than the cap, so the two widths agree either
    // way and the assertion would pass on the old grid.
    const gridWidth = await page.locator('[data-dashboard-root]').evaluate((el) => el.getBoundingClientRect().width);
    const mainContentWidth = await main.evaluate((el) => {
        const style = getComputedStyle(el);

        return el.clientWidth - parseFloat(style.paddingLeft) - parseFloat(style.paddingRight);
    });
    expect(Math.abs(gridWidth - mainContentWidth)).toBeLessThanOrEqual(1);
    expect(await firstCardShare()).toBeGreaterThanOrEqual(55);
    expect(await firstCardShare()).toBeLessThanOrEqual(70);
    expect(await maxCardVoid()).toBeLessThanOrEqual(24);
    expect(await tallestCell('Data Status')).toBeGreaterThanOrEqual(88);
    await page.screenshot({ path: testInfo.outputPath('dashboard-wide-1920.png'), fullPage: true });

    await page.goto('/preferences');
    await page.locator('#app > *').first().waitFor();
    await page.getByRole('radio', { name: 'Compact' }).check();
    await page.getByRole('button', { name: 'Save preferences' }).click();
    await expect(page.getByRole('status')).toHaveText('Preferences saved.');

    await page.goto('/');
    await page.locator('#app > *').first().waitFor();
    const compact = await mainWidth();
    expect(compact).toBeGreaterThanOrEqual(700);
    expect(compact).toBeLessThanOrEqual(850);
    // Compact is a single column, so it scrolls when the content asks for more than the shell
    // offers (835px with no career, 1063px with one). The bar is that the stack stays content-sized
    // and untrimmed, not that it fits a 1080px viewport, which it cannot without hiding a region.
    expect(await rootHeight()).toBeLessThanOrEqual(1100);
    expect(await maxCardVoid()).toBeLessThanOrEqual(24);
    await page.screenshot({ path: testInfo.outputPath('dashboard-compact-1920.png'), fullPage: true });

    // Reset to Standard so the suite leaves the shared store as it found it.
    await page.goto('/preferences');
    await page.locator('#app > *').first().waitFor();
    await page.getByRole('radio', { name: 'Standard', exact: true }).check();
    await page.getByRole('button', { name: 'Save preferences' }).click();
    await expect(page.getByRole('status')).toHaveText('Preferences saved.');
});

test('one filled primary button in the empty-career state', async ({ page }) => {
    await page.goto('/');
    await page.locator('#app > *').first().waitFor();
    await expect(page.locator('[data-variant="primary"]:visible')).toHaveCount(1);
});

test('no card encloses more than 24px of void', async ({ page }) => {
    await page.goto('/');
    await page.locator('#app > *').first().waitFor();
    const offenders = await page.evaluate(() =>
        Array.from(document.querySelectorAll('[data-card]')).map((card) => {
            const last = card.lastElementChild as HTMLElement | null;
            if (!last) return { id: card.getAttribute('data-card'), void: 0 };
            const bottom = last.offsetTop + last.offsetHeight;
            const pad = parseFloat(getComputedStyle(card).paddingBottom);

            return { id: card.getAttribute('data-card'), void: card.clientHeight - pad - bottom };
        }).filter((c) => c.void > 24)
    );
    expect(offenders).toEqual([]);
});

test('no stat value overflows its tile', async ({ page }) => {
    await page.goto('/');
    await page.locator('#app > *').first().waitFor();
    const offenders = await page.evaluate(() =>
        Array.from(document.querySelectorAll('[data-stat-tile]')).map((tile) => {
            const value = tile.querySelector('[data-stat-value]') as HTMLElement | null;
            if (!value) return null;

            return value.scrollWidth > value.clientWidth ? tile.textContent?.trim() : null;
        }).filter(Boolean)
    );
    expect(offenders).toEqual([]);
});
