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

// KI-131: two steps' accessible names carry the run scope where their visible labels collide
// with the global rail's mobile words (`Legacy Lab` -> `Legacy`, `Support Cards` -> `Deck`). The
// visible text never changed; the loop needs the name a screen reader hears. Walk-8 E-02 adds the
// third: the global rail's `Skills` (`/skills`, the library) and this row's (`/training-runs/N/skills`).
const STEP_NAMES: Record<string, string> = {
    Inheritance: "This run's Inheritance",
    Deck: "This run's Deck",
    Skills: "This run's Skills",
};

const stepName = (label: string): string => STEP_NAMES[label] ?? label;

test('draws the nine steps as the career walks them, with the live one marked', async ({ page }) => {
    createdRunUrls.push(await startCareer(page, 'unity_cup'));

    const nav = page.getByRole('navigation', { name: 'Career', exact: true });
    await expect(nav).toBeVisible();

    // Nine steps. Every one but Result is a real link on an active career; Result is the one section a
    // run earns only by ending, and the case below owns that shape in full.
    for (const label of STEPS.filter((step) => step !== 'Result')) {
        await expect(nav.getByRole('link', { name: stepName(label), exact: true })).toHaveCount(1);
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

test('a completed career retires the stepper and keeps the Result page itself reachable', async ({ page }) => {
    // The reachability rule's completed-run half, re-measured after R7-38/R7-62 landed (68015d36,
    // 2026-11-08): `CareerLayout` mounts the stepper only on an active run, so a completed career
    // draws no nav at all and the Result step it used to link is reached by the run's own
    // `result_url` door. The pre-R7-38 shape this case pinned (a live `Result` link inside the
    // nav on a completed run) is retired behavior and had been red since that commit.
    // The fixture is the seeded URA run, not a wizard walk — the same fixture the completed-run
    // cases in career-cockpit.spec.ts use, and for the same reason: rebuilding a finished career
    // through the wizard turn by turn is a second suite, not a fixture.
    const runId = uraCompletedRun();
    await page.goto(`/training-runs/${runId}/cockpit`, { waitUntil: 'domcontentloaded' });
    await page.locator('#app > *').first().waitFor();

    // The stepper is an active-run surface: a completed career carries no nav to a step it has
    // already earned, so this asserts the retirement rather than a link the layout no longer draws.
    await expect(page.getByRole('navigation', { name: 'Career', exact: true })).toHaveCount(0);

    // And the Result page is still the finished career's own screen: the run's result URL loads
    // and names the trainee, so retirement removed a duplicate door, not the destination.
    await page.goto(`/training-runs/${runId}/result`, { waitUntil: 'domcontentloaded' });
    await page.locator('#app > *').first().waitFor();
    await expect(page.getByRole('heading', { level: 1 })).toBeVisible();
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
    // ever open the section of the run it belongs to. KI-131 scopes the accessible name where the
    // visible word collides with the global rail's mobile label; the href guard matches its
    // run-scoped tail because Inertia v2 renders the `Link` href absolute.
    const inheritance = nav.getByRole('link', { name: "This run's Inheritance", exact: true });
    await expect(inheritance).toHaveAttribute('href', new RegExp('/training-runs/' + runId + '/inheritance$'));
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

test('the run-scoped Skills step does not collide with the global rail (walk-8 E-02)', async ({ page }) => {
    // Walk 8 clicked the sidebar `Skills` (`/skills`, the library) when it meant this run's planner
    // (`/training-runs/N/skills`): two links, one accessible name, one page. The step's name carries
    // the scope, the same mechanism deck and inheritance already use.
    const runUrl = await startCareer(page, 'unity_cup');

    await page.goto(`${runUrl}/cockpit`, { waitUntil: 'domcontentloaded' });
    await page.locator('#app > *').first().waitFor();

    const nav = page.getByRole('navigation', { name: 'Career' });

    await expect(nav.getByRole('link', { name: "This run's Skills" })).toBeVisible();
    // The bare name must not resolve inside the career nav any more.
    await expect(nav.getByRole('link', { name: 'Skills', exact: true })).toHaveCount(0);
});
