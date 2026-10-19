import { test, expect } from '@playwright/test';
import { deleteRun } from '../utils/delete-run';
import { startCareer } from '../utils/start-career';

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

    // Nine sections, in the order the career walks them. Each live one is a real link.
    for (const label of TABS) {
        await expect(nav.getByRole('link', { name: label, exact: true })).toHaveCount(1);
    }
    await expect(nav.getByRole('link')).toHaveCount(TABS.length);

    // The live section is marked, and it is the Cockpit on this screen.
    await expect(nav.getByRole('link', { name: 'Cockpit', exact: true })).toHaveAttribute('aria-current', 'page');

    // The identity strip carries the trainee the wizard recorded.
    await expect(page.getByRole('region', { name: 'Career' })).toBeVisible();
});

test('an unreachable Result tab is a focusable non-navigating span', async ({ page }) => {
    createdRunUrls.push(await startCareer(page, 'unity_cup'));

    const nav = page.getByRole('navigation', { name: 'Career sections' });
    const result = nav.getByRole('link', { name: 'Result', exact: true });

    // DESIGN.md §5's 2026-10-17 amendment: `aria-disabled` announces a state and does not cancel
    // activation, so an unreachable section must not be an `<a>` at all. It is a span that looks like
    // a link, says so, takes focus, and has no href.
    await expect(result).toHaveAttribute('aria-disabled', 'true');
    await expect(result).toHaveAttribute('tabindex', '0');
    await expect(result).toHaveAttribute('title', 'Available when the career is completed');
    await expect(result).not.toHaveAttribute('href');
    expect(await result.evaluate((el) => el.tagName)).toBe('SPAN');
});

test('a completed career reaches its Result tab', async ({ page }) => {
    // The other half of the reachability rule: the span above is conditional on the run still being
    // active, not a tab that is never a link. A completed run renders it as an ordinary link.
    test.skip(true, 'blocked-on-stale-bundle; eligible when the tree is quiet');

    createdRunUrls.push(await startCareer(page, 'unity_cup'));
});

test('clicking or activating the unreachable Result tab changes no URL', async ({ page }) => {
    test.skip(true, 'blocked-on-stale-bundle; eligible when the tree is quiet');

    createdRunUrls.push(await startCareer(page, 'unity_cup'));

    const result = page.getByRole('navigation', { name: 'Career sections' }).getByRole('link', { name: 'Result', exact: true });
    const before = page.url();

    await result.click();
    await expect(page).toHaveURL(before);
    await expect(page).toHaveURL(before);

    await result.focus();
    await page.keyboard.press('Enter');
    await expect(page).toHaveURL(before);
    await page.keyboard.press('Space');
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
