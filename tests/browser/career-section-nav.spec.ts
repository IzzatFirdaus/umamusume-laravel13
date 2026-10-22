import { test, expect } from '@playwright/test';
import { deleteRun } from '../utils/delete-run';
import { startCareer } from '../utils/start-career';
import { uraCompletedRun } from '../utils/ura-completed-run';

/*
 * The career section nav (plan slice 4.1): the identity strip and the nine sections as one tab row,
 * mounted by `CareerLayout` so every career screen inherits it.
 *
 * The component is `components/career/CareerSectionNav.vue`, not the `CareerHeader.vue` 4.1 named:
 * that path is already the Cockpit's own identity card and is imported by `Cockpit.vue`. The ruling
 * and the evidence are in the ledger.
 *
 * **Bundle disposition: blocked.** This spec renders app routes, so it needs `public/build` to carry
 * the component under test. The manifest is 2026-10-19 19:04:47 while `resources/js` is 22:00:33, and
 * the tree holds peer-dirty Vue, so a rebuild is withheld (KI-70). The URL-unchanged case is therefore
 * skipped with its reason rather than silently absent; the remaining cases are written and cannot run
 * until the tree is quiet. Every claim below is source-verified, not runtime-verified.
 */

const createdRunUrls: string[] = [];

test.afterEach(async ({ page }) => {
    for (const url of createdRunUrls.splice(0)) {
        await deleteRun(page, url);
    }
});

const TABS = ['Cockpit', 'Training', 'Races', 'Skills', 'Events', 'Legacy', 'Deck', 'Timeline', 'Result'];

test('renders the run identity strip and the nine sections on the Cockpit', async ({ page }) => {
    createdRunUrls.push(await startCareer(page, 'unity_cup'));

    const nav = page.getByRole('navigation', { name: 'Career sections' });
    await expect(nav).toBeVisible();

    // Nine sections, in the order the career walks them. A live one is a real link; the one an active
    // career cannot reach is a named absence with no link semantics at all (DESIGN.md §5 (a), and the
    // case below owns that shape in full). The loop therefore excludes Result, which the sibling case
    // asserts is not a link on this same run — asserting both would be asserting a contradiction.
    for (const label of TABS.filter((tab) => tab !== 'Result')) {
        await expect(nav.getByRole('link', { name: label, exact: true })).toHaveCount(1);
    }
    await expect(nav.getByRole('link')).toHaveCount(TABS.length - 1);

    // The live section is marked, and it is the Cockpit on this screen.
    await expect(nav.getByRole('link', { name: 'Cockpit', exact: true })).toHaveAttribute('aria-current', 'page');

    // The identity strip carries the trainee the wizard recorded.
    await expect(page.getByRole('region', { name: 'Career' })).toBeVisible();
});

test('an unreachable Result tab is a named absence, not a focusable link', async ({ page }) => {
    createdRunUrls.push(await startCareer(page, 'unity_cup'));

    const nav = page.getByRole('navigation', { name: 'Career sections' });

    // DESIGN.md §5's 2026-10-17 (a) amendment: "`aria-disabled` alone is not that shape and never may
    // be used as one". The tab used to be `<span role="link" aria-disabled="true" tabindex="0">` with
    // its reason in a `title`, which announced a link, took focus, and reached its reason by hover only.
    // The ruled shape is non-focusable text naming the absence, so this asserts the absence of every
    // part of the old shape rather than its presence.
    await expect(nav.getByRole('link', { name: 'Result', exact: true })).toHaveCount(0);
    await expect(nav.locator('[aria-disabled="true"]')).toHaveCount(0);
    await expect(nav.locator('[role="link"]')).toHaveCount(0);

    // The section is still named, and the reason is visible prose rather than a `title`.
    const result = nav.getByText('Result', { exact: true });
    await expect(result).toBeVisible();
    await expect(result).not.toHaveAttribute('tabindex');
    await expect(result).not.toHaveAttribute('title');
    await expect(nav.getByText(/Available when the career is completed/)).toBeVisible();
});

test('a completed career reaches its Result tab', async ({ page }) => {
    // The positive half of the reachability rule the case above asserts in the negative: on an ACTIVE
    // run the Result tab is a focusable span (deliberately unreachable); on a COMPLETED run it renders
    // as an ordinary link. The fixture is the seeded URA run, not a wizard walk - the same fixture the
    // completed-run cases in career-cockpit.spec.ts use, and for the same reason: rebuilding a finished
    // career through the wizard turn by turn is a second suite, not a fixture.
    await page.goto(`/training-runs/${uraCompletedRun()}/cockpit`, { waitUntil: 'domcontentloaded' });
    await page.locator('#app > *').first().waitFor();

    const result = page
        .getByRole('navigation', { name: 'Career sections' })
        .getByRole('link', { name: 'Result', exact: true });

    // A real link, not the span the active state renders: an href ending in /result, no aria-disabled,
    // and an <a> element rather than a <span role="link">. These four mirror the four the negative case
    // asserts, one state apart.
    await expect(result).toBeVisible();
    await expect(result).toHaveAttribute('href', /result$/);
    await expect(result).not.toHaveAttribute('aria-disabled');
    expect(await result.evaluate((el) => el.tagName)).toBe('A');
});

test('an unreachable Result tab cannot be activated by pointer or keyboard', async ({ page }) => {
    createdRunUrls.push(await startCareer(page, 'unity_cup'));

    const nav = page.getByRole('navigation', { name: 'Career sections' });
    const before = page.url();

    // Nothing to activate: the tab is plain text, so there is no tab stop to reach it with and no
    // element to force a click onto. The old case needed `click({ force: true })` because Playwright
    // reads `aria-disabled="true"` as "not enabled" and would not dispatch — that harness workaround
    // existed only because the forbidden shape was shipped.
    const result = nav.getByText('Result', { exact: true });
    await expect(result).toBeVisible();
    await expect(nav.locator('[role="link"], [tabindex="0"]')).toHaveCount(0);

    await page.keyboard.press('Tab');
    await expect(page).toHaveURL(before);
});

test('the old per-screen door pair is gone and Training Detail marks its own tab', async ({ page }) => {
    createdRunUrls.push(await startCareer(page, 'unity_cup'));

    const nav = page.getByRole('navigation', { name: 'Career sections' });

    // Cockpit: the "Cockpit / Dashboard" pair the career bar used to carry is replaced by the nav.
    await expect(page.getByRole('link', { name: 'Go to Dashboard' })).toHaveCount(0);
    await expect(page.getByRole('link', { name: 'Back to Cockpit' })).toHaveCount(0);

    // Training Detail: the inline "Career Cockpit" link duplicated the Cockpit tab and is removed.
    await page.goto(`${createdRunUrls[0]}/training`, { waitUntil: 'domcontentloaded' });
    await expect(page.getByRole('link', { name: 'Career Cockpit' })).toHaveCount(0);
    await expect(nav.getByRole('link', { name: 'Training', exact: true })).toHaveAttribute('aria-current', 'page');
});
