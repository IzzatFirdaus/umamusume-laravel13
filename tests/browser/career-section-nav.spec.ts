import { test, expect } from '@playwright/test';
import { deleteRun } from '../utils/delete-run';
import { startCareer } from '../utils/start-career';
import { uraCompletedRun } from '../utils/ura-completed-run';

/*
 * The career stepper: the nine sections a run walks, drawn in the wireframe's own shape —
 * `cockpit-unity-cup.html:71-82`, `<nav aria-label="Career" class="stepper">`, one `.step` per
 * section, the live one a span carrying `aria-current="step"` and the rest links.
 *
 * It replaced the slice-4.1 identity strip plus tab row. The strip went because no cockpit HFW draws
 * a second identity band: the trainee and the scenario live in the topbar crumb, the run's state in
 * the `who` pill, and the calendar, rarity and potential in the page's `lede`. The tab row became the
 * stepper, which is the shape the wizard shell already uses and the shape the cockpit HFW draws.
 *
 * The step labelled `Inheritance` is the career's inheritance event, not the run-scoped Legacy Lab.
 * The HFW names the step after the event, and `/training-runs/{run}/inheritance` is that screen.
 * `Legacy` would have been a second name for a section the HFWs do not draw in the row.
 */

const createdRunUrls: string[] = [];

test.afterEach(async ({ page }) => {
    for (const url of createdRunUrls.splice(0)) {
        await deleteRun(page, url);
    }
});

const STEPS = ['Cockpit', 'Training', 'Races', 'Skills', 'Events', 'Inheritance', 'Deck', 'Timeline', 'Result'];

test('draws the nine steps as the career walks them, with the live one marked', async ({ page }) => {
    createdRunUrls.push(await startCareer(page, 'unity_cup'));

    const nav = page.getByRole('navigation', { name: 'Career', exact: true });
    await expect(nav).toBeVisible();

    // Nine steps. Every one but Result is a real link on an active career; Result is the one section a
    // run earns only by ending, and the case below owns that shape in full.
    for (const label of STEPS.filter((step) => step !== 'Result')) {
        await expect(nav.getByRole('link', { name: label, exact: true })).toHaveCount(1);
    }
    await expect(nav.getByRole('link')).toHaveCount(STEPS.length - 1);

    // The live step is marked with `aria-current="step"`, the value the HFW and the wizard stepper both
    // use — not `page`, which would describe a navigation destination rather than a position in a walk.
    await expect(nav.getByRole('link', { name: 'Cockpit', exact: true })).toHaveAttribute('aria-current', 'step');
});

test('an unreachable Result step is a named absence, not a focusable link', async ({ page }) => {
    createdRunUrls.push(await startCareer(page, 'unity_cup'));

    const nav = page.getByRole('navigation', { name: 'Career', exact: true });

    // DESIGN.md §5's 2026-10-17 (a) amendment: "`aria-disabled` alone is not that shape and never may
    // be used as one". The old tab was `<span role="link" aria-disabled="true" tabindex="0">` with its
    // reason in a `title`, which announced a link, took focus, and reached its reason by hover only.
    // The ruled shape is non-focusable text, so this asserts the absence of every part of the old shape.
    await expect(nav.getByRole('link', { name: 'Result', exact: true })).toHaveCount(0);
    await expect(nav.locator('[aria-disabled="true"]')).toHaveCount(0);
    await expect(nav.locator('[role="link"]')).toHaveCount(0);

    // The step is still named, and the reason is visible prose rather than a `title`.
    const result = nav.getByText('Result', { exact: true });
    await expect(result).toBeVisible();
    await expect(result).not.toHaveAttribute('tabindex');
    await expect(result).not.toHaveAttribute('title');
    await expect(page.getByText(/Available when the career is completed/)).toBeVisible();
});

test('a completed career reaches its Result step', async ({ page }) => {
    // The positive half of the reachability rule the case above asserts in the negative. The fixture is
    // the seeded URA run, not a wizard walk — the same fixture the completed-run cases in
    // career-cockpit.spec.ts use, and for the same reason: rebuilding a finished career through the
    // wizard turn by turn is a second suite, not a fixture.
    await page.goto(`/training-runs/${uraCompletedRun()}/cockpit`, { waitUntil: 'domcontentloaded' });
    await page.locator('#app > *').first().waitFor();

    const result = page
        .getByRole('navigation', { name: 'Career', exact: true })
        .getByRole('link', { name: 'Result', exact: true });

    // A real link, not the span the active state renders: an href ending in /result, no aria-disabled,
    // and an <a> element rather than a <span role="link">. These four mirror the four the negative case
    // asserts, one state apart.
    await expect(result).toBeVisible();
    await expect(result).toHaveAttribute('href', /result$/);
    await expect(result).not.toHaveAttribute('aria-disabled');
    expect(await result.evaluate((el) => el.tagName)).toBe('A');
});

test('an unreachable step cannot be activated by pointer or keyboard', async ({ page }) => {
    createdRunUrls.push(await startCareer(page, 'unity_cup'));

    const nav = page.getByRole('navigation', { name: 'Career', exact: true });
    const before = page.url();

    // Nothing to activate: the step is plain text, so there is no tab stop to reach it with and no
    // element to force a click onto.
    const result = nav.getByText('Result', { exact: true });
    await expect(result).toBeVisible();
    await expect(nav.locator('[role="link"], [tabindex="0"]')).toHaveCount(0);

    await page.keyboard.press('Tab');
    await expect(page).toHaveURL(before);
});

test('renders on the run deck page with a run-scoped Inheritance link', async ({ page }) => {
    createdRunUrls.push(await startCareer(page, 'unity_cup'));
    const runId = createdRunUrls[0].split('/').pop();

    await page.goto(`${createdRunUrls[0]}/deck`, { waitUntil: 'domcontentloaded' });
    await page.locator('#app > *').first().waitFor();

    const nav = page.getByRole('navigation', { name: 'Career', exact: true });
    await expect(nav).toBeVisible();

    // The step must be run-scoped, not the global `/inheritance` or `/legacy`: a career screen may only
    // ever open the section of the run it belongs to.
    const inheritance = nav.getByRole('link', { name: 'Inheritance', exact: true });
    await expect(inheritance).toHaveAttribute('href', `/training-runs/${runId}/inheritance`);
});

test('the old per-screen door pair is gone and Training marks its own step', async ({ page }) => {
    createdRunUrls.push(await startCareer(page, 'unity_cup'));

    const nav = page.getByRole('navigation', { name: 'Career', exact: true });

    // Cockpit: the "Cockpit / Dashboard" pair the career bar used to carry is replaced by the stepper.
    await expect(page.getByRole('link', { name: 'Go to Dashboard' })).toHaveCount(0);
    await expect(page.getByRole('link', { name: 'Back to Cockpit' })).toHaveCount(0);

    // Training Detail: the inline "Career Cockpit" link duplicated the Cockpit step and is removed.
    await page.goto(`${createdRunUrls[0]}/training`, { waitUntil: 'domcontentloaded' });
    await expect(page.getByRole('link', { name: 'Career Cockpit' })).toHaveCount(0);
    await expect(nav.getByRole('link', { name: 'Training', exact: true })).toHaveAttribute('aria-current', 'step');
});
