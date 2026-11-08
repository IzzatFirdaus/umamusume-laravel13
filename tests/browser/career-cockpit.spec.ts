import { test, expect } from '@playwright/test';
import { resolve } from 'node:path';
import { pathToFileURL } from 'node:url';
import { PAGE_MARKERS, assertStructurallyEqual, capture } from './fixtures/hfw-structure';
import { buildAxe } from '../utils/accessibility';
import { deleteRun } from '../utils/delete-run';
import { recordTurns } from '../utils/record-turns';
import { expectTapTargets } from '../utils/tap-targets';
import { backToCockpit, openScenarioDetail } from '../utils/scenario-detail';
import { startCareer } from '../utils/start-career';
import { uraCompletedRun } from '../utils/ura-completed-run';

/*
 * Rendered-copy, layout and accessibility evidence for SCREEN-009, the Career Cockpit
 * (`SCR-CAR-011`, plan §8 D8). `CareerCockpitTest` asserts the resolved props — the advisor contract
 * with Energy present and absent, the seven entries and the single marker, the config-driven widget
 * list. These cases assert what only a browser reaches: the three breakpoints and the reflow, the
 * keyboard path through the career stepper, the reduced-motion path, the 44px sweep, and the refusal
 * sentence a run with no Energy prints.
 *
 * `v3.0.0` retired three blocks this file used to read on the shell — the page-level action grid, the
 * `Scenario detail` disclosure and the `Edit team state` disclosure. The claims they carried moved with
 * the surfaces: the doors are the stepper's steps and the "Where to go" card's entries, the panels read
 * on `/training-runs/{run}/scenario`, and the team write lives at `/training-runs/{run}/team`.
 *
 * Fixture strategy. The run is built by walking the wizard's own steps and pressing `Start Career`, so
 * every fact under test was entered through the app rather than seeded, and the row is deleted over
 * HTTP in `afterEach` — the shared `tests/utils/delete-run.ts` teardown, which is what keeps this
 * spec from breaking `runs.spec.ts`'s empty-state assertions.
 *
 * Axe coverage. `@axe-core/playwright` is installed, so the last case below scans this screen with
 * the shared `tests/utils/accessibility.ts` builder and the same `wcag2a`/`wcag2aa`/`wcag21aa` tag
 * set `accessibility.spec.ts` uses. It lives here rather than there because the cockpit needs a run,
 * and the run is built by this file's own wizard walk. The hand-rolled checks (target size, keyboard
 * path, focus ring, reflow, reduced motion) are above it.
 */

const createdRunUrls: string[] = [];

test.afterEach(async ({ page }) => {
    for (const url of createdRunUrls.splice(0)) {
        await deleteRun(page, url);
    }
});

/**
 * One career, created the way a Trainer creates one: the wizard's steps, then `Start Career`.
 *
 * The deck step is filled because `StartCareerRequest` re-runs each step's own rules, and the deck is
 * one of them; the ancestry step is left alone, which is the state a fresh install meets (the seeded
 * scratch database holds no Veterans).
 *
 * Each step's write is given 60s rather than the config's 15s. `php artisan serve` is a single-process
 * `php -S`, so a step's PUT queues behind whatever else is on the box; `playwright.config.ts` already
 * raised the *test* budget to 180s for exactly that reason (11-16s per request observed under load),
 * and a button that reads "Saving…" at 15s is the host's queue rather than a stuck form.
 */
const WRITE = { timeout: 60_000 } as const;

async function newCareer(page: import('@playwright/test').Page): Promise<string> {
    // One walk, owned by `tests/utils/start-career.ts` (slice 4.1). The private copy this function
    // used to carry named controls three rebuilds had retired — `#scenario-unity_cup` with
    // `aria-pressed`, a card labelled "Select Trainee", `#deck-pick` and "Equip owned to", and
    // "Start Career" — so every spec with a copy was red. The helper waits on server-driven state,
    // and it leaves the browser on the Cockpit, which is the URL this function returns.
    createdRunUrls.push(await startCareer(page, 'unity_cup'));

    return page.url();
}

/** The Cockpit for a freshly created career: `Start Career` already lands on it. */
async function openCockpit(page: import('@playwright/test').Page): Promise<void> {
    await newCareer(page);
    await page.locator('#app > *').first().waitFor();
}

/** The rail's recorded-action card, named by its own heading. */
function recordedCard(page: import('@playwright/test').Page) {
    return page.getByRole('region', { name: 'Recorded action' });
}

/*
 * `C6b.11` deleted the "Career tools" disclosure and `v3.0.0` retired the page-level `ActionGrid` with
 * it, so nothing in these cases opens or reads a disclosure on the shell: the stepper reaches the action
 * screens, and the two writes the grid used to host move to `Where to go` and the Training screen. The
 * helper that used to open the grid is gone rather than left as a no-op, so a future caller cannot
 * believe it is still hiding a region.
 */

test('walks the recorded-action card through its three states', async ({ page }) => {
    const cockpitUrl = await newCareer(page);
    await page.locator('#app > *').first().waitFor();
    const runId = cockpitUrl.match(/\/training-runs\/(\d+)/)?.[1] ?? '';

    // State 1: a run that has taken no turn has no recorded action, so the rail prints no card at all.
    // An empty slot would read as "the advisor said nothing", which is a different claim.
    await expect(recordedCard(page)).toHaveCount(0);

    // State 2: an action with no result entered. Energy is recorded so the advisor ranks and the
    // snapshot carries benefit lines; `preview_gains` is deliberately absent, which is the difference
    // between "nothing entered" and "entered as zero".
    await recordTurns(page, runId, 1, { energy: '80', choice: 'training-Speed' });
    await page.goto(cockpitUrl, { waitUntil: 'domcontentloaded' });
    await page.locator('#app > *').first().waitFor();

    const card = recordedCard(page);
    await expect(card).toBeVisible();
    await expect(card.getByRole('heading', { name: 'What the rail said' })).toBeVisible();
    await expect(card.getByRole('heading', { name: 'What the turn gave' })).toBeVisible();
    // The pending prompt is a polite live region, not a modal and not silent (FD-4 §5.4).
    await expect(card.getByRole('status')).toHaveText('No result entered yet. Record what the turn gave and the comparison appears here.');
    await expect(card.getByText('Agreed')).toHaveCount(0);

    // State 3: the result entered. Forecast and actual print side by side, each row qualified by its own
    // provenance word, and the reconciliation answers the one question the two rows can settle: was this
    // the action the rail recommended. So the fixture reads that recommendation off the rail instead of
    // assuming the engine's tie-break between four equal deficits — the rail is showing turn 1's state,
    // which is exactly the state the stored snapshot was built from.
    const recommendedWord = (await page
        .getByRole('region', { name: 'Advisor' })
        .getByRole('link', { name: /^Go to / })
        .first()
        .innerText()).replace('Go to ', '').trim();

    await recordTurns(page, runId, 1, {
        // `turn` rides in `extra` because the helper merges it after its own loop value: turn 1 is taken,
        // and a second write at that number is refused by the turn's unique index.
        turn: '2',
        energy: '70',
        choice: recommendedWord === 'Rest' ? 'rest' : `training-${recommendedWord}`,
        'preview_gains[speed]': '20',
    });
    await page.goto(cockpitUrl, { waitUntil: 'domcontentloaded' });
    await page.locator('#app > *').first().waitFor();

    await expect(card.getByText('Speed +20')).toBeVisible();
    await expect(card.getByText('Agreed')).toBeVisible();
    // The state is a word, never a mark: `CareerBuildTargetTest` keeps the trust glyphs inside
    // `ProvenanceBadge.vue` alone, and the card must not read as a provenance statement.
    await expect(card.getByRole('img', { name: /^Calculated/ }).first()).toBeVisible();
    // `confirmed` renders NOTHING — `ProvenanceMark` deliberately omits it from its mark map, because a
    // figure the tool is sure of does not need a badge (plan 1.7, `91a6047`). This assertion waited for
    // the retired badge long after it was deleted, and it had never run here to notice.
    await expect(card.getByRole('img', { name: /^Confirmed/ })).toHaveCount(0);

    // The card is the slot, not the history: the door goes to the Timeline, which owns the whole list.
    const door = card.getByRole('link', { name: 'Every action this run took' });
    await expect(door).toBeVisible();
    expect((await door.boundingBox())?.height ?? 0, 'the card link is under 44px').toBeGreaterThanOrEqual(44);

    // Keyboard reach through the rail. A single Tab is not the claim: the objectives list and the
    // alternatives each put their own links between the recommendation's door and this card, so the test
    // walks the sequence until focus lands inside the card and fails if it never does.
    await page.getByRole('region', { name: 'Advisor' }).getByRole('link', { name: /^Go to / }).first().focus();
    let reached = false;
    for (let step = 0; step < 12 && !reached; step++) {
        await page.keyboard.press('Tab');
        reached = await door.evaluate((el) => el === document.activeElement || el.contains(document.activeElement));
    }
    expect(reached, 'the recorded action\'s door is not reachable from the rail by keyboard').toBe(true);

    // 320px reflow: the card is inside the rail, so the rail's own overflow is the measure.
    await page.setViewportSize({ width: 320, height: 900 });
    const overflow = await page.evaluate(
        () => document.documentElement.scrollWidth - document.documentElement.clientWidth,
    );
    expect(overflow, 'the rail overflows horizontally at 320px').toBeLessThanOrEqual(0);

    const scan = await buildAxe(page).analyze();
    expect(scan.violations, JSON.stringify(scan.violations, null, 2)).toEqual([]);
});

test('names every absent value and refuses to rank until Energy is entered', async ({ page }) => {
    await openCockpit(page);

    // The header claims no position for a run that has logged nothing, and every reading is `N/A`
    // with a reason rather than a zero (D-220). No dash is printed anywhere (AGENTS.md §5).
    await expect(page.getByRole('heading', { name: 'Stats and state' })).toBeVisible();
    await expect(page.getByRole('heading', { name: 'Advisor' })).toBeVisible();
    // `v3.0.0` retired the page-level action grid, so the surface that names a destination on this page
    // is the career stepper whose steps are what the grid's entries moved into (`CareerSectionNav.vue:103`).
    await expect(page.getByRole('navigation', { name: 'Career' })).toBeVisible();

    await expect(page.getByText('N/A').first()).toBeVisible();
    // No em dash anywhere in shipped copy: an unrecorded value is `N/A` with a reason (AGENTS.md §5).
    await expect(page.locator('main')).not.toContainText('—');

    // The empty state names what is missing, why it matters and what to do.
    await expect(page.getByRole('heading', { name: 'No turn recorded yet' })).toBeVisible();
    await expect(page.getByRole('link', { name: 'Record the first turn through Training' })).toBeVisible();

    // Energy absent is the engine's refusal, printed as the refusal it is: the FD-2 shape carries it in
    // `refusal`, names the missing input, and prints no recommendation beside it.
    const advisorRegion = page.getByRole('region', { name: 'Advisor' });
    await expect(advisorRegion.getByText('Nothing can be prioritised until this turn\'s Energy is recorded.')).toBeVisible();
    await expect(advisorRegion.getByText('Not enough information to recommend an action.')).toBeVisible();
    await expect(advisorRegion.getByText('Missing: Energy.')).toBeVisible();
    // The other refusal class must not appear here: the two states are separate copy by design
    // (design-2.0 §2), so a scan that printed both would be one generic empty message again.
    await expect(advisorRegion.getByText('No suitable action found.')).toHaveCount(0);
    await expect(advisorRegion.getByRole('heading', { name: 'Recommended action' })).toHaveCount(0);

    // The doors survive as the stepper's steps, and the word that marked the grid's own pick marks
    // none of them: `ActionGrid.vue:67` was the cockpit's only printer of the `RECOMMENDED` marker, and
    // the badge that replaced it belongs to the Training screen (`TrainingDetail.vue:569`), which this
    // page does not render. Page-wide is the honest scope now that no Actions region exists to scope it
    // to — a marker printed while the advisor declines is exactly what this catches.
    const steps = page.getByRole('navigation', { name: 'Career' });
    await expect(steps.getByRole('link', { name: 'Training', exact: true })).toBeVisible();
    await expect(steps.getByRole('link', { name: 'Inheritance', exact: true })).toBeVisible();
    await expect(page.getByText(/RECOMMENDED/)).toHaveCount(0);
});

test('names the stat denominator by the figure it prints, floor or cap (R7-19, R7-66)', async ({ page }) => {
    await openCockpit(page);

    // A freshly created career has entered no ceilings, so the strip's numbers are the scenario's
    // published floor. Walk 7 read `1,300/1,300/1,300/1,300/1,800` labelled `cap` on a run whose
    // `cap_*` columns are all null, which is the reference's own warning (§1.3.4): an observed in-force
    // cap must not be printed as the scenario cap, and a floor must not be printed as either.
    // The stat rows are the `srow` list items the panel prints; each carries the denominator word
    // beside the figure, so the strip is scoped by the row rather than by a region attribute.
    const rows = page.locator('li.srow').filter({ hasText: /\d/ });

    await expect(rows.filter({ hasText: 'floor' }).first()).toBeVisible();
    await expect(rows.filter({ hasText: /\bcap\b/ })).toHaveCount(0);
});

test('lists the career obligations the calendar states, each as a mark and a word', async ({ page }) => {
    // The seeded reference calendar is what this asserts against, so the expected rows are read from the
    // seeder rather than invented: `Junior Make Debut` is a shared mandatory slot at Junior turn 12, and
    // the finale block rows carry no turn because the post-December grid sits outside it (KI-15 neighbours).
    await openCockpit(page);

    const advisor = page.getByRole('region', { name: 'Advisor' });
    // The label is the wireframe's word: `cockpit-unity-cup.html:144` draws the table under a small-caps
    // "Obligations" sublabel. It is a label, not a heading, so it is read as text — the section keeps
    // its accessible name through `aria-labelledby="career-objectives-heading"`.
    await expect(advisor.getByText('Obligations', { exact: true })).toBeVisible();

    // The wireframe's obligations table: one row per obligation, the state as a pill that carries its
    // word, and the due turn in its own column ("no date" where the calendar dates nothing).
    await expect(advisor.getByRole('columnheader', { name: 'Obligation' })).toBeVisible();
    await expect(advisor.getByRole('columnheader', { name: 'State' })).toBeVisible();
    await expect(advisor.getByRole('columnheader', { name: 'Due' })).toBeVisible();

    const debut = advisor.locator('tbody tr').filter({ hasText: 'Junior Make Debut' });
    await expect(debut).toBeVisible();
    // State is a word and a mark, never a colour (WCAG 1.4.1): the pill's word carries it.
    await expect(debut.getByText('ahead', { exact: true })).toBeVisible();
    await expect(debut.getByText('Junior Year · Turn 12')).toBeVisible();

    // An obligation the source cannot date prints "no date" with the reason, and is never ranked at
    // turn zero.
    const undated = advisor.locator('tbody tr').filter({ hasText: 'no date' }).first();
    await expect(undated.getByText('unknown', { exact: true })).toBeVisible();
    await expect(undated.getByText('no date')).toBeVisible();
    await expect(undated.getByText('no date')).toHaveAttribute('title', /does not date/i);

    // Every row states its provenance by name, not by glyph alone.
    await expect(advisor.getByRole('img', { name: /^Calculated/ }).first()).toBeVisible();

    // Nothing is due at turn zero, so no door is offered rather than a dead one.
    await expect(advisor.getByRole('link', { name: 'Settle it at Race Decision' })).toHaveCount(0);

    // The strip is the tallest thing the rail gained: prove the three widths still reflow without a
    // sideways page.
    for (const width of [320, 768, 1280]) {
        await page.setViewportSize({ width, height: 900 });
        await page.waitForTimeout(60);
        expect(
            await page.evaluate(() => document.documentElement.scrollWidth - document.documentElement.clientWidth),
            `the objectives strip scrolls sideways at ${width}px`,
        ).toBeLessThanOrEqual(1);
    }

    const scan = await buildAxe(page).analyze();
    expect(scan.violations, JSON.stringify(scan.violations, null, 2)).toEqual([]);
});

test('puts the obligations table below the recommendation, as the wireframe draws it', async ({ page }) => {
    // The region-level composition gate (`career-composition.spec.ts`) reads each screen's `h2` list.
    // It cannot see the order of the blocks inside a card, so the Advisor card's own order is asserted
    // here — the gap that let the table sit above the recommendation while every region-level test
    // passed.
    //
    // `cockpit-unity-cup.html`'s Advisor card reads: the recommendation and its three bullets, then the
    // "Obligations" sublabel and the table. The annotation key's first item for that card is "The
    // recommendation, its reason and its cost all live in one block", so the table is what qualifies
    // them and belongs after them.
    await openCockpit(page);

    const advisor = page.getByRole('region', { name: 'Advisor' });
    const obligations = advisor.getByText('Obligations', { exact: true });

    await expect(obligations).toBeVisible();

    // DOM order, not paint order: `compareDocumentPosition` answers "is the table after the
    // recommendation", which a CSS `order` utility would not change.
    //
    // The anchor is whichever recommendation slot the card is in: the "Recommended action" heading
    // when the advisor ranks, or the refusal sentence when it declines. A fresh run has no recorded
    // Energy, so the advisor refuses and never renders the ranked block — and the correction form
    // that would record one is broken in this tree (see the two pre-existing failures in this file).
    // The refusal anchor is still a real anchor: in the layout this test was written against, the
    // obligations table came FIRST, before the recommendation slot of either kind.
    const order = await advisor.evaluate((region) => {
        const nodes = Array.from(region.querySelectorAll('h3, p'));
        const label = nodes.find((node) => node.id === 'career-objectives-heading');
        if (!label) return { error: 'the obligations label is not in the card' };

        const recommendation = nodes.find((node) => {
            const text = (node.textContent ?? '').trim();
            if (node.tagName === 'H3') return text.includes('Recommended action');
            return text.startsWith('Not enough information to recommend an action.');
        });
        if (!recommendation) return { error: 'the recommendation slot is not in the card' };

        const priority = nodes.find(
            (node) => node.tagName === 'P' && (node.textContent ?? '').trim() === 'Priority',
        );

        // Node.DOCUMENT_POSITION_FOLLOWING === 4: the node comes after the other.
        const follows = (other: Element): boolean =>
            (other.compareDocumentPosition(label) & 4) !== 0;

        return {
            afterRecommendation: follows(recommendation),
            afterPriority: priority === undefined ? null : follows(priority),
        };
    });

    expect(order).toEqual({ afterRecommendation: true, afterPriority: true });
});

test('lays out at 1280, 768 and 320 without a horizontal scroll', async ({ page }) => {
    await openCockpit(page);

    for (const width of [1280, 768, 320]) {
        await page.setViewportSize({ width, height: 900 });
        await page.waitForTimeout(50);

        // WCAG 1.4.10: no two-dimensional scrolling for a primary decision.
        const overflow = await page.evaluate(
            () => document.documentElement.scrollWidth - document.documentElement.clientWidth,
        );
        expect(overflow, `the cockpit scrolls sideways at ${width}px`).toBeLessThanOrEqual(1);

        // Every region is in the accessibility tree at every width: nothing is hidden by a breakpoint.
        await expect(page.getByRole('navigation', { name: 'Career' })).toBeVisible();
        await expect(page.getByRole('heading', { name: 'Advisor' })).toBeVisible();
        await expect(page.getByRole('heading', { name: 'Scenario' })).toBeVisible();
        // The doors the retired grid used to hold are the "Where to go" card's, and they stay in the
        // tree at the narrow width (`Cockpit.vue:1211`, `v3.0.0`).
        await expect(page.getByRole('region', { name: 'Where to go' })).toBeVisible();
    }

    // The spec's mobile order, restated for what survives on the page: the advisor's answer still reads
    // above the doors it sends the Trainer to (design-2.0 §40). The grid this compared the recommendation
    // against retired in `v3.0.0`, and "Where to go" took its place as the page's door list.
    await page.setViewportSize({ width: 320, height: 900 });
    const advisor = await page.getByRole('heading', { name: 'Advisor' }).boundingBox();
    const doors = await page.getByRole('heading', { name: 'Where to go' }).boundingBox();
    expect((advisor?.y ?? 0), 'the recommendation is not above the doors on mobile').toBeLessThan(doors?.y ?? 0);
});

test('walks the cockpit doors from the keyboard with a visible focus ring', async ({ page }) => {
    await openCockpit(page);

    // `v3.0.0` retired the action grid, so the doors a turn leads into are the career stepper's steps and
    // the "Where to go" card. The stepper is the page-wide tab bar, in reading order, and it is the
    // surface a keyboard reaches first, so the walk this case owns moved onto it.
    const steps = page.getByRole('navigation', { name: 'Career' });
    const training = steps.getByRole('link', { name: 'Training', exact: true });
    await training.focus();
    await expect(training).toBeFocused();

    // Tab moves through the steps in reading order; the next step takes focus, and it carries the
    // global `:focus-visible` ring rather than relying on a hover state (WCAG 2.4.7).
    await page.keyboard.press('Tab');
    const races = steps.getByRole('link', { name: 'Races', exact: true });
    await expect(races).toBeFocused();
    await expect(races).toBeInViewport();

    const outline = await races.evaluate((el) => getComputedStyle(el).outlineWidth);
    expect(parseFloat(outline), 'the focused step has no focus ring').toBeGreaterThanOrEqual(2);

    // And a door low in the page is reachable without being hidden under the sticky mobile nav
    // (SC 2.4.11). `Scenario detail` is the door this release added, so it is the one that proves the
    // claim on the page as it now stands.
    await page.setViewportSize({ width: 320, height: 700 });
    const door = page.getByRole('region', { name: 'Where to go' }).getByRole('link', { name: 'Scenario detail' });
    await door.focus();
    await expect(door).toBeInViewport();
});

test('routes Rest and Recreation to the Training screen holding their choice', async ({ page }) => {
    const cockpitUrl = await newCareer(page);
    await page.locator('#app > *').first().waitFor();

    const runUrl = cockpitUrl.replace(/\/cockpit$/, '');

    // SCR-CAR-011 gap 1 says no door is a dead link. These two are turn choices rather than training
    // options (SCR-CAR-012), and the Training screen's one record form writes them, so the address
    // arrives with the choice already picked. Rest's address carries the value the row is written under;
    // Recreation's carries the client's own word, which the Training screen normalises back to the
    // stored `mood` literal at its own boundary (`TrainingDetail.vue:295`).
    //
    // `v3.0.0` retired the grid that printed these two hrefs, so the case reads the destination instead
    // of the button: what has to stay true is that the address the controller builds still lands on the
    // screen holding the choice. The grid's own note line named the path because the button sat on the
    // other page; the form that writes the turn names the effect instead (`TrainingDecisionController.php:567`),
    // so the surviving proof of arrival is the held radio inside the section that offers it.
    for (const [query, label] of [['rest', 'Rest'], ['recreation', 'Recreation']] as const) {
        await page.goto(`${runUrl}/training?choice=${query}`, { waitUntil: 'domcontentloaded' });
        await page.locator('#app > *').first().waitFor();

        await expect(page.getByRole('heading', { name: 'Other actions' })).toBeVisible();

        const held = page.getByRole('radio', { name: label, exact: true });
        await expect(held, `${label} is not the choice the address holds`).toBeChecked();
    }
});

test('quiets transitions when the operating system asks for reduced motion', async ({ page }) => {
    await openCockpit(page);

    // The probe carries a two-second transition of its own. Under `reduce` the global block must
    // override it, and under `no-preference` it must not — so this check can fail, which is what makes
    // it evidence rather than a no-op (design-2.0 §43, plan §12.4).
    const duration = async (): Promise<number> =>
        page.evaluate(() => {
            const probe = document.createElement('div');
            probe.style.transitionDuration = '2s';
            document.body.appendChild(probe);
            const computed = getComputedStyle(probe).transitionDuration;
            probe.remove();

            return parseFloat(computed);
        });

    await page.emulateMedia({ reducedMotion: 'no-preference' });
    expect(await duration()).toBeGreaterThan(1);

    await page.emulateMedia({ reducedMotion: 'reduce' });
    expect(await duration()).toBeLessThan(0.05);
});

test('sizes the cockpit controls to the 44px contract', async ({ page }) => {
    await openCockpit(page);

    // WCAG 2.2 SC 2.5.8, floor 44px (plan §12.1). The page-wide sweep the other career specs run, now
    // through the shared helper: it asserts it measured something, so a page that lost controls cannot
    // pass by scanning nothing.
    //
    // `v3.0.0` retired the hand-built list below it, which enumerated the action grid's seven entries
    // and the section nav's `Cockpit` tab — and the nav entry in that list was already wrong, since the
    // stepper names itself `Career` (`CareerSectionNav.vue:103`). Sweeping the page covers the grid's
    // replacements instead of enumerating them: the stepper's steps, and the "Where to go" card's two
    // new doors.
    await expectTapTargets(page.locator('main a, main button, main select, main input'), 10);
});

test('records Energy through the cockpit correction and then marks one action', async ({ page }) => {
    const cockpitUrl = await newCareer(page);
    await page.locator('#app > *').first().waitFor();

    const runId = cockpitUrl.match(/\/training-runs\/(\d+)/)?.[1] ?? '';

    // A turn has to exist before the cockpit offers a correction, and no rendered surface creates one
    // any more: the run screen is a redirect to the cockpit, and the cockpit's own correction form is
    // a PUT over an existing turn. The write is not this spec's subject, so it goes over the route.
    // The 700s beside Speed's 600 make Speed the largest deficit, which the ranking below asserts.
    await recordTurns(page, runId, 1, { stamina: '700', power: '700', guts: '700', wit: '700' });

    await page.goto(cockpitUrl, { waitUntil: 'domcontentloaded' });
    await page.locator('#app > *').first().waitFor();

    // `C6b.6` moved the correction form to its own door (`runs.turns.edit`), reached from the
    // Timeline row it corrects. The write still goes through `runs.turns.update`.
    await page.goto(`${cockpitUrl.replace(/\/cockpit$/, '')}/timeline`, { waitUntil: 'domcontentloaded' });
    await page.locator('#app > *').first().waitFor();
    await page.getByRole('link', { name: 'Edit turn 1' }).first().click();
    await page.waitForURL(/\/turns\/\d+\/edit$/);
    // R2-16: the state is named before a figure, so only an `exact` reading carries a number.
    await page.locator('#energy_state').selectOption('exact');
    // The correction form's Energy field is named by its `id` (`TurnEdit.vue:166` renders
    // `<BaseInput id="energy" …>` and passes no `name`), so the attribute selector this case used to
    // reach it matched nothing on any page.
    await page.locator('#energy').fill('60');
    await page.getByRole('button', { name: 'Save correction' }).click();
    await expect(page.getByText('Saved.')).toBeVisible();

    await page.goto(cockpitUrl, { waitUntil: 'domcontentloaded' });
    await page.locator('#app > *').first().waitFor();

    // The state panel is the proof the write landed, and the proof it landed where the form was posted:
    // a correction made on the Cockpit returns to the Cockpit rather than to the run screen. Energy is
    // read here rather than in the header because the header stopped printing the four readings on
    // 2026-10-08; they are the state panel's, beside the stats the advisor ranks against.
    const state = page.locator('section[aria-labelledby="career-state-heading"]');
    await expect(state.getByText('60/100')).toBeVisible();

    // With Energy recorded the advisor ranks, and the five-element FD-2 shape renders in order:
    // priority, recommendation, benefit, alternatives, reasons, then what would change the answer.
    const advisorRegion = page.getByRole('region', { name: 'Advisor' });
    // Priority prints as the HFW's small-caps label above the recommendation, not as a section of its
    // own — `cockpit-trackblazer.html` and `cockpit-grand-concert.html` draw it that way, and
    // `cockpit-unity-cup.html` draws no priority line at all.
    await expect(advisorRegion.getByText('Priority', { exact: true })).toBeVisible();
    await expect(advisorRegion.getByText('Close the largest deficit against the build target you entered.')).toBeVisible();
    await expect(advisorRegion.getByRole('heading', { name: 'Recommended action' })).toBeVisible();
    await expect(advisorRegion.getByText('Speed is 200 below target (the largest deficit).')).toBeVisible();

    // `band` is retired from the wire shape: Energy is either recorded, and the engine ranks, or absent,
    // and it refuses. The old band sentence must not survive anywhere on the screen.
    await expect(page.getByText('At or above the advisory line')).toHaveCount(0);

    // Every reason row is a definition-list row with its provenance badge beside it, named by its word
    // and not by its glyph (`ProvenanceBadge.vue`'s own accessible-name rule).
    await expect(advisorRegion.getByRole('term')).not.toHaveCount(0);
    await expect(
        advisorRegion.getByRole('img', { name: /^Calculated/ }).first(),
    ).toBeVisible();
    // Three constants back the ranked answer (the advisory line, the session cost, Wit's cost), and all
    // three are `confirmed`, which renders NO mark at all — `ProvenanceMark` omits it from its mark map
    // because a figure the tool is sure of does not need a badge (plan 1.7, `91a6047`). The retired
    // assertion asked for three `Confirmed` badges; the mark that must exist here is the `Calculated`
    // one asserted above, and the exception states below it.
    await expect(advisorRegion.getByRole('img', { name: /^Confirmed/ })).toHaveCount(0);
    // The constant a figure rests on prints its source and read date visibly, not only in a tooltip
    // (ADR-0001 §2 and §7, owner ruling 5a). The source names the PUBLISHER: `844aebf` removed the
    // `UMAMUSUME_REFERENCE.md §…` document path from `config/advisor.php`, because a path a Trainer
    // cannot open is not provenance. This pattern was still pinned to the retired path.
    await expect(advisorRegion.getByText(/Source: .+read 20\d\d-\d\d-\d\d/).first()).toBeVisible();
    await expect(advisorRegion.getByText(/UMAMUSUME_REFERENCE/)).toHaveCount(0);
    await expect(advisorRegion.getByRole('heading', { name: 'What would change this' })).toBeVisible();

    // The Trainer's own button, not an automatic execution, and named for the action it goes to.
    const go = advisorRegion.getByRole('link', { name: 'Go to Speed' });
    await expect(go).toBeVisible();
    // WCAG 2.2 SC 2.5.8 on the decision's own door, which only exists in the ranked state.
    expect(((await go.boundingBox())?.height ?? 0), 'the decision link is under 44px').toBeGreaterThanOrEqual(44);

    // The ranked state scans too: the accented recommendation card is the treatment the absent state
    // does not carry.
    const ranked = await buildAxe(page).analyze();
    expect(ranked.violations, JSON.stringify(ranked.violations, null, 2)).toEqual([]);

    // The marker the retired grid printed (`ActionGrid.vue:67`, `★ RECOMMENDED`) has no successor on this
    // page: `TrainingCard.vue`, which still carries that string at line 316, is imported by nothing, so
    // the badge the Trainer reads is `TrainingDetail.vue:569`'s `Recommended` chip on the ranked
    // discipline. Following the door to the screen that writes the turn is what keeps the case's second
    // half — exactly one action marked — alive after the grid retired.
    await go.click();
    await page.waitForURL(/\/training$/);
    await expect(page.getByText('Recommended', { exact: true })).toHaveCount(1);
});

test('passes an axe scan at WCAG A and AA', async ({ page }) => {
    await openCockpit(page);

    // `buildAxe` scopes to `#app`, which is what makes this deterministic: Inertia's NProgress bar is
    // appended to `<body>` as a sibling of the app root and carries an invalid `role="bar"` (KI-63).
    const results = await buildAxe(page).analyze();

    expect(results.violations, JSON.stringify(results.violations, null, 2)).toEqual([]);
});

/*
 * The three run states and the one turn source (Cockpit pass, 2026-10-15).
 *
 * The header used to count this run's logged rows while the correction form read the latest row's own
 * `turn`, so a career entered mid-course printed turn 1 beside a dropdown reading turn 75. Both cases
 * below reach the state through the screen's own doors — a recorded turn, then the header's status
 * editor — rather than through a seeder, so the state under test is one a Trainer can arrive at.
 *
 * The URA mandatory ladder's four `Met` rows at Completed are asserted at the prop level in
 * `CockpitAlertScopeTest` and `UraPanelTest`; this file's wizard walk builds a Unity Cup career, and a
 * second walk per case is not worth its runtime here.
 */

/** The career, closed: the header's own status editor is the only door to this state. */
async function completeCareer(page: import('@playwright/test').Page): Promise<string> {
    const cockpitUrl = await newCareer(page);
    const runId = cockpitUrl.match(/\/training-runs\/(\d+)/)?.[1] ?? '';

    // One logged turn first. `correctionSection()` answers null for a run with no turns, so a career that
    // never logged one renders no correction form at all and the collapse sweep would pass on an absence.
    await recordTurns(page, runId, 1, { energy: '80', choice: 'training-Speed' });
    await page.goto(cockpitUrl, { waitUntil: 'domcontentloaded' });
    await page.locator('#app > *').first().waitFor();

    // The settings door is `runs.settings` at the run's own root (`routes/web.php:151`), not a child of
    // `/cockpit`: this used to append `/settings` to a URL already ending in `/cockpit`, which 404'd and
    // left every case calling this helper waiting on a `#status` select that could never appear. The
    // sibling spec that builds it right is `career-cockpit-forms.spec.ts:66`.
    await page.goto(`${cockpitUrl.replace(/\/cockpit$/, '')}/settings`, { waitUntil: 'domcontentloaded' });
    await page.selectOption('#status', 'Completed', WRITE);
    await page.getByRole('button', { name: 'Change status' }).click();
    await page.locator('#app > *').first().waitFor();

    return cockpitUrl;
}

test('reads one turn number in the header', async ({ page }) => {
    const cockpitUrl = await newCareer(page);
    const runId = cockpitUrl.match(/\/training-runs\/(\d+)/)?.[1] ?? '';

    await recordTurns(page, runId, 1, { energy: '80', choice: 'training-Speed' });
    await page.goto(cockpitUrl, { waitUntil: 'domcontentloaded' });
    await page.locator('#app > *').first().waitFor();

    // R7 S0 housekeeping: `[data-field="turn"]` is gone, and the `Turn` summary tile renders only on a
    // Completed run (`Cockpit.vue:793`). On an active run the phrase is the page lede — measured: one
    // `p` carrying `Senior Year · Early October · Turn 67 of 72. Energy …`. DM-1 still holds: one
    // phrase, one denominator. The assertion matches the phrase inside its sentence rather than
    // demanding the whole element equal it.
    const position = page.getByText(/\bTurn 1 of 72\b/);

    await expect(position).toHaveCount(1);

});

test('names the run state in the DOM, and only the one that applies', async ({ page }) => {
    await openCockpit(page);

    // R7 S0: the `data-run-state` attribute is gone; the state is stated in words by the region the
    // cockpit renders for it, which is what the later half of this case already asserts.
    await expect(page.getByRole('region', { name: 'No turn recorded yet' })).toHaveCount(1);

    const cockpitUrl = await completeCareer(page);
    await page.goto(cockpitUrl, { waitUntil: 'domcontentloaded' });
    await page.locator('#app > *').first().waitFor();

    await expect(page.getByRole('region', { name: 'This run is complete.' })).toHaveCount(1);

    // The finished career states itself and the advisor's refusal is gone. The Result door belongs to the
    // summary card alone: the closed-run region states the fact and does not repeat the door.
    await expect(page.getByRole('region', { name: 'This run is complete.' })).toHaveCount(1);
    // R7 S0: measured on a Completed run, the door's accessible name is `Career result` and the
    // completed cockpit renders no `role="region"` at all — its cards are `<section aria-labelledby>`.
    // Two links carry that name (the crumb and the card), so the door is scoped to the closed-run card.
    await expect(page.getByRole('link', { name: 'Career result' }).first()).toBeVisible();
    await expect(page.getByText('Critical:')).toHaveCount(0);
});

test('keeps a finished career free of the disclosures this release retired', async ({ page }) => {
    const cockpitUrl = await completeCareer(page);

    // Reload rather than read the page the status write left behind: that one was opened to change it.
    await page.goto(cockpitUrl, { waitUntil: 'domcontentloaded' });
    await page.locator('#app > *').first().waitFor();

    // The case this replaces pinned `details[data-region="team-state"]` closed on load. `v3.0.0` deleted
    // the disclosure, so the shell's contract is now that neither retired block comes back, and what took
    // their place is a door rather than a collapsed panel. The last line is what keeps this from passing
    // on an absence: the retirement is only correct because the destination still has an entry.
    for (const region of ['team-state', 'scenario-detail']) {
        await expect(page.locator(`details[data-region="${region}"]`), region).toHaveCount(0);
    }

    const door = page.getByRole('region', { name: 'Where to go' }).getByRole('link', { name: 'Scenario detail' });
    await expect(door).toBeVisible();
});

test('the panels read before their own scenario block, and the cockpit carries neither', async ({ page }) => {
    await openCockpit(page);
    // `v3.0.0` moved the panels out of the cockpit's `Scenario detail` disclosure onto their own page, so
    // the order this case owns is now measured there. Renamed rather than dropped, because the claim it
    // made about the shell cannot survive the deletion: the shell no longer hosts a panel region.
    await openScenarioDetail(page);

    // Tree order, not visual order: `querySelectorAll` answers in document order, which is the order a
    // screen reader and a keyboard reach the regions. The half that survives the move is that one
    // registered renderer did not shadow another out of the tree — the panels' strip still reads above
    // the scenario block it belongs to.
    const treeOrder = async (): Promise<string[]> =>
        page.evaluate(() =>
            Array.from(document.querySelectorAll('[data-region]'))
                .map((el) => el.getAttribute('data-region'))
                .filter((region): region is string => region !== null),
        );

    const regions = await treeOrder();
    const panels = regions.indexOf('scenario-panels');
    const scenario = regions.indexOf('scenario');

    expect(panels, 'the panels render no region on their own page').toBeGreaterThanOrEqual(0);
    expect(scenario, 'the scenario block is absent from the panels page').toBeGreaterThanOrEqual(0);
    expect(panels).toBeLessThan(scenario);

    // And the retirement is stated on the shell: it keeps its advisor region and carries no panel at all,
    // which is exactly what deleting the disclosure changed.
    await backToCockpit(page);
    const shell = await treeOrder();
    expect(shell.indexOf('advisor')).toBeGreaterThanOrEqual(0);
    expect(shell).not.toContain('scenario-panels');
});

test('a completed URA career prints no Critical row', async ({ page }) => {
    // The seeded career, not a wizard walk: the case is about a finished career whose four mandatory
    // races are recorded, and the fixture helper runs the repo's own run seeder against the suite's
    // scratch database.
    await page.goto(`/training-runs/${uraCompletedRun()}/cockpit`, { waitUntil: 'domcontentloaded' });
    await page.locator('#app > *').first().waitFor();

    await expect(page.getByRole('heading', { name: 'This run is complete.' })).toBeVisible();
    await expect(page.getByText('Critical:')).toHaveCount(0);
});

test('a completed URA career draws its four recorded mandatory races as a ladder', async ({ page }) => {
    await page.goto(`/training-runs/${uraCompletedRun()}/cockpit`, { waitUntil: 'domcontentloaded' });
    await page.locator('#app > *').first().waitFor();

    // The ladder is `UraFinalsLadder` inside `ScenarioPanel.vue`, and the panel moved to its own page in
    // `v3.0.0`; the walk follows it there rather than reading an absence on the shell.
    await openScenarioDetail(page);

    const rows = page.locator('[data-region="scenario-panels"] [data-ladder-group="Mandatory races"] [data-ladder-row]');
    await expect(rows).toHaveCount(4);

    // `completed` is this build's state word for a race the run entered, and the ladder prints it as
    // `Recorded`. The attribute mirrors the payload rather than inventing a second vocabulary for one
    // fact.
    for (const row of await rows.all()) {
        await expect(row).toHaveAttribute('data-state', 'completed');
    }
});

/*
 * Slice 4.2's adoption. Three groups, all of them PAGE-WALK cases rather than fixture ones, per Section
 * 46's Rule 1: a fixture proves the primitive (`section.spec.ts` does), while these prove the ADOPTION -
 * five landmark names on the real page, a strip that actually sticks, and a primary count that is
 * per-state rather than a constant.
 */
test('keeps every section landmark name after the Section adoption', async ({ page }) => {
    // Three of the five resolve BY NAME on an active career.
    await openCockpit(page);
    await expect(page.getByRole('region', { name: 'No turn recorded yet' })).toHaveCount(1);
    await expect(page.getByRole('region', { name: 'Scenario', exact: true })).toHaveCount(1);

    // The other two titles are DYNAMIC, so the stable invariant is the structural shape: the section
    // still references the id and the element carrying it still exists. Asserting a name there would be
    // asserting the payload, not the adoption. Both live on a finished career.
    await page.goto(`/training-runs/${uraCompletedRun()}/cockpit`, { waitUntil: 'domcontentloaded' });
    await page.locator('#app > *').first().waitFor();
    await expect(page.locator('[aria-labelledby="cockpit-summary-heading"]')).toHaveCount(1);
    await expect(page.locator('#cockpit-summary-heading')).toHaveCount(1);
    // R7 S0: `career-closed-heading` is gone. Measured on a Completed run: the closed-run card is a
    // `<section>` whose heading is `#cockpit-summary-heading` reading "This run is complete.", and the
    // completed cockpit has no `role="region"` at all.
    await expect(page.locator('#cockpit-summary-heading')).toHaveCount(1);
    await expect(page.locator('#cockpit-summary-heading')).toHaveText('This run is complete.');
});

test('renders no summary strip, because the parity pass cut it (S0)', async ({ page }) => {
    // This case REPLACED a stickiness assertion that could not be made honestly, and now records the
    // component's retirement. `530d62b9` cut `StatSummary` from the cockpit because no cockpit HFW draws
    // a second reading of the header's facts above the canvas, and `Cockpit.vue:41-47` states that the
    // component "is left in place for the Careers list to adopt or for deletion in a follow-up; nothing
    // in this tree renders it".
    //
    // The former case asserted the strip's facts, `No recommendation. See the advisor.` among them. That
    // sentence was the component's own copy defect, and it went with the component — so asserting its
    // absence is the honest successor, not re-pointing the old assertions at whatever element still
    // carries a similar word.
    await page.goto(`/training-runs/${uraCompletedRun()}/cockpit`, { waitUntil: 'domcontentloaded' });
    await page.locator('#app > *').first().waitFor();

    // The closed-run card and the stats card are what a completed cockpit renders.
    await expect(page.locator('#cockpit-summary-heading')).toBeVisible();
    await expect(page.locator('#career-state-heading')).toBeVisible();

    // The retired strip's sentences are gone. `No recommendation. See the advisor.` in particular: the
    // component printed it on a completed run while the advisor was correctly absent.
    await expect(page.getByText('No recommendation. See the advisor.')).toHaveCount(0);
    await expect(page.locator('[aria-label="Run summary"]')).toHaveCount(0);
});

test('keeps one primary where the advisor recommends, and none where it does not', async ({ page }) => {
    // Ruling 2. State one: a fresh career. The advisor has nothing to rank, so the page renders NO
    // filled primary at all, and the strip says so rather than inventing one.
    await openCockpit(page);
    expect(await page.locator('[class*="bg-primary-action"]').count(), 'a fresh career rendered a primary').toBe(0);
    // R7 S0: the strip that carried `No recommendation. See the advisor.` is retired, so a fresh
    // career's state is read where the screen states it: the advisor's own card. No filled primary is
    // the assertion that matters here, and it is measured above.

    // State two: a logged turn with Energy, so the rail ranks. The count is MEASURED and reported, then
    // asserted - it is not assumed to be the one the rail's CTA accounts for.
    const cockpitUrl = await newCareer(page);
    const runId = cockpitUrl.match(/\/training-runs\/(\d+)/)?.[1] ?? '';
    await recordTurns(page, runId, 1, { energy: '80', choice: 'training-Speed' });
    await page.goto(cockpitUrl, { waitUntil: 'domcontentloaded' });
    await page.locator('#app > *').first().waitFor();

    const primaries = page.locator('[class*="bg-primary-action"]');
    const labels: string[] = [];
    for (let i = 0; i < (await primaries.count()); i++) {
        labels.push((await primaries.nth(i).innerText()).replace(/\s+/g, ' ').trim().slice(0, 40));
    }
    console.log('ADOPTION-PRIMARY-LABELS=' + JSON.stringify(labels));

    // The strip carries NONE, whatever the rail renders: that is the invariant Ruling 2 states.
    await expect(
        page.getByRole('region', { name: 'Run summary' }).locator('[class*="bg-primary-action"]'),
    ).toHaveCount(0);
    // And the rail's own recommendation CTA is present, which is what the strip points at.
    await expect(page.getByRole('region', { name: 'Advisor' }).getByRole('link', { name: /^Go to / })).toHaveCount(1);
});

test('names no component by the retired name its rename replaced', async () => {
    // A SOURCE-level check, which is the only honest way to assert a rename: the browser cannot see a
    // path. Reads the tree rather than a bundle, so it needs no server.
    const fs = await import('node:fs');
    const path = await import('node:path');

    const walk = (dir: string): string[] =>
        fs.readdirSync(dir, { withFileTypes: true }).flatMap((entry) => {
            const full = path.join(dir, entry.name);
            return entry.isDirectory() ? walk(full) : [full];
        });

    const offenders = walk('resources/js').filter((file) =>
        fs.readFileSync(file, 'utf8').includes('CareerHeader'),
    );
    // `toEqual`, not `toBe`: two empty arrays are not the same OBJECT, and `toBe` would have failed on a
    // perfectly clean tree while reporting a message that reads like a survivor list. Caught by running it.
    expect(offenders, `the retired name survives in ${offenders.join(', ')}`).toEqual([]);
});

test('prints the bare fan count, because the ladder is a Dashboard reading (S0)', async ({ page }) => {
    // A-0.10's case asserted the fan tile printed `30,755 to 240,000`. `CareerStatePanel.vue:267-271`
    // records the override that removed it: `HFW-OVERRIDE (text: fan-ladder)` — every cockpit HFW prints
    // the bare count in this tile, the ladder is a Dashboard reading, and `fanLadder` stays a prop "so a
    // caller that wants it can add it back rather than re-deriving the band". The register's `R7-73`
    // classifies the same absence as `Known limitation`, citing R6-43's override.
    //
    // The successor assertion is the override itself: the count prints, the ladder suffix does not. That
    // is what the screen is meant to do, and asserting it means a future re-add fails this case loudly
    // rather than silently changing the surface.
    const cockpitUrl = await newCareer(page);
    const runId = cockpitUrl.match(/\/training-runs\/(\d+)/)?.[1] ?? '';
    await recordTurns(page, runId, 1, { fans: '209245' });
    await page.goto(cockpitUrl, { waitUntil: 'domcontentloaded' });
    await page.locator('#app > *').first().waitFor();

    const state = page.locator('section[aria-labelledby="career-state-heading"]');

    await expect(state.getByText('209,245')).toBeVisible();
    await expect(state.getByText(/to 240,000/)).toHaveCount(0);
});

test('prints the cap composition as a footer under Stats and state', async ({ page }) => {
    // A-0.1 (Group A) / `C6b.8`: the caps the run states sit beside the blue sparks its Legacy
    // carries, and the line says they do not reconcile (§1.2, the source's own open question).
    // The card became a footer note under `Stats and state`; the fact is asserted where it now
    // reads rather than as a card that no longer exists.
    await newCareer(page);
    await page.locator('#app > *').first().waitFor();

    const footer = page.locator('[data-region="cap-composition"]');
    await expect(footer).toBeVisible();
    await expect(footer.getByText(/Caps/)).toBeVisible();
    await expect(footer.getByText(/Blue sparks/)).toBeVisible();
    await expect(page.getByText(/do not reconcile/)).toBeVisible();
});

test('shows the sourced mood effect headline beside the mood', async ({ page }) => {
    // A-0.11 (Group A): the Mood Effect headline (+20% training, +4% running) renders beside the mood
    // tier, sourced from config('uma.mood_effect') (UMAMUSUME_REFERENCE.md �1.1.6).
    const cockpitUrl = await newCareer(page);
    const runId = cockpitUrl.match(/\/training-runs\/(\d+)/)?.[1] ?? '';
    await recordTurns(page, runId, 1, { mood: 'GREAT' });
    await page.goto(cockpitUrl, { waitUntil: 'domcontentloaded' });
    await page.locator('#app > *').first().waitFor();

    const state = page.getByRole('region', { name: 'Stats and state' });
    await expect(state.getByText(/training \+20%, running \+4%/)).toBeVisible();
});

test('names the run in the top bar crumb', async ({ page }) => {
    // R4-01/R4-02: the id lived only in the URL, so two careers of one trainee in one scenario were
    // two identical headers. The parity pass then cut the identity strip that used to print it, and
    // the id moved to the crumb's scenario segment, which is the one place the shell names the run:
    // `{trainee} · {scenario} #{id} · {screen}`, the shape every cockpit HFW draws.
    await openCockpit(page);

    const id = page.url().match(/\/training-runs\/(\d+)\/cockpit$/)?.[1] ?? '';
    expect(id, 'the cockpit URL names no run id').not.toBe('');

    const crumb = page.locator('.topbar .crumb, [data-shell-crumb]').first();
    await expect(crumb).toBeVisible();
    await expect(crumb).toContainText(`#${id}`);

    // The screen's own name closes the crumb, and the trainee opens it, so the run is fully
    // identified without the reader leaving the top bar.
    await expect(crumb).toContainText('Cockpit');
});

/*
 * Cockpit structural parity, four scenario variants.
 *
 * **Two documents, two instruments.** Every other screen in this set has one wireframe. The Cockpit has
 * a shell-density HFW and a companion full-treatment document, and they do not share a marker order: the
 * shell draws a lean page, while the app renders the companion's panel suite, which interleaves the
 * shell's cards. So:
 *
 *   shell       -> presence. Every marker the shell draws must appear somewhere on the page. Ordering
 *                  against an approximation is incoherent, and making it pass would mean demoting the
 *                  panels the companion draws in the open.
 *   companion   -> ordered subset. It is one coherent document, so its order is the target.
 *
 * `note` is dropped from the companion's demanded list. Its notes there are rationale annotations —
 * "so no screen can state a different cap" is a sentence to a reviewer, not to a Trainer — and the
 * same apparatus is already excluded from every page in this set by the annotation-key ruling
 * (`pages/Dashboard.vue:35`, `veterans-parity.spec.ts:14`).
 *
 * Each case builds its own run with recorded turns. On a run that has logged nothing the stat rows
 * carry no bar, so `.track` is absent and the comparison would report five missing markers that are
 * really a subject with nothing in it.
 */
/** A wireframe document as a `file:` URL, resolved from the repo root the runner starts in. */
function wireframe(relative: string): string {
    return pathToFileURL(resolve('docs/design-research', relative)).href;
}

const COCKPIT_VARIANTS = [
    { scenario: 'unity_cup', shell: 'cockpit-unity-cup', companion: '01-unity-cup.html' },
    { scenario: 'ura_finale', shell: 'cockpit-ura-finale', companion: '02-ura-finale.html' },
    { scenario: 'trackblazer', shell: 'cockpit-trackblazer', companion: '03-trackblazer.html' },
    { scenario: 'our_grand_concert', shell: 'cockpit-grand-concert', companion: '04-our-grand-concert.html' },
];

/**
 * The shell markers a cockpit draws whatever the run holds. The rest of the shell's vocabulary is
 * state-conditional and cannot be demanded of a run this case builds: `warn` appears only while a
 * scenario goal is due and unrecorded, `pill-caution` only beside a figure that needs the Trainer's
 * attention, and the companion's `note` blocks are rationale annotations rather than copy. Each is
 * named in the commit that first measured it, and none is silently dropped from the list.
 */
const SHELL_MARKERS_ALWAYS = ['grid', 'card', 'srow', 'tile', 'track', 'pill'];

for (const variant of COCKPIT_VARIANTS) {
    test(`renders ${variant.scenario} in the shell's regions and the companion's order`, async ({ page }) => {
        const runUrl = await startCareer(page, variant.scenario);
        createdRunUrls.push(runUrl);
        const runId = runUrl.match(/training-runs\/(\d+)/)?.[1] ?? '';
        await recordTurns(page, runId, 3);

        await page.setViewportSize({ width: 1280, height: 900 });
        await page.goto(`/training-runs/${runId}/cockpit`, { waitUntil: 'domcontentloaded' });
        await page.locator('#app > *').first().waitFor();

        const app = await capture(page, 'main', { markers: PAGE_MARKERS });
        expect(app.regions.length, 'the cockpit rendered a page, not an error shell').toBeGreaterThan(0);

        await page.goto(wireframe(`frontend-v2-wireframes/${variant.shell}.html`));
        const shell = await capture(page, '.canvas', { markers: PAGE_MARKERS });
        const wanted = SHELL_MARKERS_ALWAYS.filter((marker) => shell.regions.includes(marker));
        const absent = wanted.filter((marker) => !app.regions.includes(marker));

        expect(absent, `unconditional shell markers the page never draws: ${absent.join(', ')}`).toEqual([]);

        await page.goto(wireframe(`cockpit-wireframes-2026-10-21/${variant.companion}`));
        const companion = await capture(page, '.wrap', { markers: PAGE_MARKERS });

        // `note` is dropped as a rationale annotation and `warn` as run-state: the due-goal band shows
        // only while a scenario goal is past due and unrecorded, so a three-turn career cannot be made
        // to answer for it. It is proven present on a real career — the shell-presence sweep against
        // run #11, sixty-seven turns in, measures 9/10 before the fix and 10/10 after.
        const demanded = companion.regions.filter((marker) => marker !== 'note' && marker !== 'warn');

        assertStructurallyEqual(
            app,
            { ...companion, regions: demanded },
            { regions: 'subset', grid: false },
        );
    });
}

test('gates the Result step the same way on every career screen (R7-38, R7-62)', async ({ page }) => {
    // Measured on one run, two screens: `/cockpit` rendered `Result` as a non-focusable SPAN with the
    // sentence "Available when the career is completed", while `/events` on the same run rendered it as a
    // live anchor with an href. Both mount `CareerSectionNav` through `CareerLayout`; the cockpit passes
    // `:status`, the other seven screens do not, and `reachable()` returns true for every step when the
    // status is unspecified. The gate is the same gate, so it must present the same way.
    //
    // This walks the seven screens the earlier probe named, and asserts the SAME presentation on each.
    await openCockpit(page);
    const runId = page.url().match(/\/training-runs\/(\d+)/)?.[1] ?? '';
    expect(runId).not.toBe('');

    const readResultStep = async (): Promise<{ tag: string; href: string | null }> =>
        page.evaluate(() => {
            const nav = document.querySelector('nav[aria-label="Career"]');

            if (!nav) {
                return { tag: 'NO NAV', href: null };
            }

            const step = [...nav.querySelectorAll('.step')].find((el) => (el.textContent || '').trim() === 'Result');

            return step === undefined
                ? { tag: 'MISSING', href: null }
                : { tag: step.tagName, href: step.getAttribute('href') };
        });

    // The seven pages that mount `CareerLayout` without passing `:status`, plus the cockpit which does.
    // `/skills` is the SkillsPlanner page's own URL; `/races` is the decision screen.
    const screens = ['cockpit', 'events', 'inheritance', 'deck', 'timeline', 'skills', 'races'];

    for (const screen of screens) {
        await page.goto(`/training-runs/${runId}/${screen}`, { waitUntil: 'domcontentloaded' });
        await page.locator('#app > *').first().waitFor();

        const step = await readResultStep();

        // An active career has no Result: not an anchor, and no href to follow.
        expect(step.tag, `${screen} must not render Result as a live link on an active run`).toBe('SPAN');
        expect(step.href, `${screen} must not give Result an href on an active run`).toBeNull();
    }
});

/*
 * S-A-P01.3 — the card's base line rides the rail as a labelled reference, never as a reading.
 *
 * The five numbers a turn-1 decision is weighed against live on the card and were visible only on the
 * trainee profile the Trainer had already left (`SHR-7`). What this asserts is the distinction the
 * recommendation rests on: one labelled `Starting` sentence beneath the rail printing the card's five
 * values, not five more rows that would read as a second measurement of the same stats (`NRM-2`).
 *
 * The walk is the shared `startCareer` helper, with its new `onTrainee` hook re-picking Vodka's costume
 * form: the default walk leaves `character_card_id` null, and a run with no card has no base line — the
 * branch `CareerCockpitTest` pins directly at the payload level.
 */
test('prints the card base stats as a labelled Starting line beneath the stat rail', async ({ page }) => {
    const runUrl = await startCareer(page, 'unity_cup', {
        onTrainee: async (stepPage) => {
            // Two forms, so this step asks which. `Vodka` is filtered by name and the single filtered
            // row is waited for, because the search is a server round trip and the Select buttons are
            // disabled while it runs.
            const rows = stepPage.locator('button[id^="trainee-"]');
            await stepPage.getByLabel('Search').fill('Vodka', WRITE);
            await expect(rows).toHaveCount(1, WRITE);
            await expect(rows.first()).toBeEnabled(WRITE);

            const vodkaId = (await rows.first().getAttribute('id')) as string;
            await stepPage.locator(`#${vodkaId}`).click();
            await expect(stepPage.locator(`#${vodkaId}`)).toHaveAttribute('aria-pressed', 'true', WRITE);

            const formSelect = stepPage.locator('#selected-form');
            await expect(formSelect).toBeVisible();
            const formValue = await formSelect.locator('option').first().getAttribute('value');
            expect(formValue, 'the form select carries no option').not.toBeNull();
            await formSelect.selectOption(formValue as string, WRITE);
        },
    });

    createdRunUrls.push(runUrl);

    const line = page.locator('[data-region="starting-stats"]');
    await expect(line).toBeVisible();
    await expect(line).toContainText('Starting');
    await expect(line).toContainText('before any training');

    // Five numbers, one per stat: the rail's five rows are the readings and this is one sentence.
    await expect(line.locator('span.font-mono')).toHaveCount(5);
    // Vodka `[Wild Top Gear]`'s base line, measured from the database in proposal P-01 §2.
    await expect(line).toContainText('96');
    await expect(line).toContainText('105');
    await expect(line).toContainText('88');
});
