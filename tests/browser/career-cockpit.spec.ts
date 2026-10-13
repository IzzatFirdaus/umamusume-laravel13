import { test, expect } from '@playwright/test';
import { buildAxe } from '../utils/accessibility';
import { deleteRun } from '../utils/delete-run';
import { recordTurns } from '../utils/record-turns';

/*
 * Rendered-copy, layout and accessibility evidence for SCREEN-009, the Career Cockpit
 * (`SCR-CAR-011`, plan §8 D8). `CareerCockpitTest` asserts the resolved props — the advisor contract
 * with Energy present and absent, the seven entries and the single marker, the config-driven widget
 * list. These cases assert what only a browser reaches: the three breakpoints and the reflow, the
 * keyboard path through the action grid, the reduced-motion path, the 44px sweep, and the refusal
 * sentence a run with no Energy prints.
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
    await page.goto('/career/setup/scenario', { waitUntil: 'domcontentloaded' });
    await page.locator('#app > *').first().waitFor();
    await page.locator('#scenario-unity_cup').click();
    await expect(page.locator('#scenario-unity_cup')).toHaveAttribute('aria-pressed', 'true', WRITE);

    await page.goto('/career/setup/trainee', { waitUntil: 'domcontentloaded' });
    await page.locator('#app > *').first().waitFor();
    const traineeButton = page.getByRole('button', { name: 'Select Trainee' }).first();
    await traineeButton.click();
    await expect(traineeButton).toHaveAttribute('aria-pressed', 'true', WRITE);

    await page.goto('/career/setup/target', { waitUntil: 'domcontentloaded' });
    await page.locator('#app > *').first().waitFor();
    await page.selectOption('select[name="purpose"]', 'StoryClear');
    await page.selectOption('select[name="distance"]', 'Medium');
    await page.selectOption('select[name="surface"]', 'Turf');
    await page.selectOption('select[name="style"]', 'Pace Chaser');

    for (const stat of ['Speed', 'Stamina', 'Power', 'Guts', 'Wit']) {
        await page.locator(`input[name="targets[${stat}]"]`).fill('800');
    }

    await page.getByRole('button', { name: 'Save target' }).click();
    await page.locator('#app > *').first().waitFor();

    await page.goto('/career/setup/deck', { waitUntil: 'domcontentloaded' });
    await page.locator('#app > *').first().waitFor();
    await page.locator('#deck-pick').selectOption({ index: 1 });
    await page.getByRole('button', { name: /^Equip owned to/ }).first().click();
    await expect(page.locator('#deck-slot-1')).not.toContainText('Not equipped', WRITE);

    await page.goto('/career/setup/preflight', { waitUntil: 'domcontentloaded' });
    await page.locator('#app > *').first().waitFor();
    await page.getByRole('button', { name: 'Start Career' }).click();
    // `Start Career` lands on the Cockpit, not the run record screen (SCR-CAR-010 redirects to
    // `runs.cockpit`), so this waits for the Cockpit and remembers the *record* URL: the cleanup
    // addresses the run by the id in that path, which is the plainest form of it.
    await page.waitForURL(/\/cockpit$/, { waitUntil: 'domcontentloaded' });

    createdRunUrls.push(page.url().replace(/\/cockpit$/, ''));

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
    await expect(card.getByRole('img', { name: /^Confirmed/ }).first()).toBeVisible();

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
    await expect(page.getByRole('heading', { name: 'Actions' })).toBeVisible();

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

    // Seven entries, and no RECOMMENDED marker at all while the advisor declines.
    await expect(page.getByRole('link', { name: /^Training/ })).toBeVisible();
    await expect(page.getByRole('link', { name: /^Inheritance/ })).toBeVisible();
    // Scoped to the Actions region the way the ranked case below is, and for the same reason:
    // `getByText` matches case-insensitively as a substring, and since Phase E the word also occurs
    // in panel prose naming a held field ("Recommended timing and projected benefit are not built",
    // Unity Cup's SCREEN-015 absence). The claim here is about the grid's marker, not about the word.
    const actions = page.getByRole('region', { name: 'Actions' });
    await expect(actions.getByText('RECOMMENDED')).toHaveCount(0);
});

test('lists the career obligations the calendar states, each as a mark and a word', async ({ page }) => {
    // The seeded reference calendar is what this asserts against, so the expected rows are read from the
    // seeder rather than invented: `Junior Make Debut` is a shared mandatory slot at Junior turn 12, and
    // the finale block rows carry no turn because the post-December grid sits outside it (KI-15 neighbours).
    await openCockpit(page);

    const advisor = page.getByRole('region', { name: 'Advisor' });
    await expect(advisor.getByRole('heading', { name: 'Objectives' })).toBeVisible();

    const debut = advisor.getByRole('listitem').filter({ hasText: 'Junior Make Debut' });
    await expect(debut).toBeVisible();
    // State is a word and a mark, never a colour (WCAG 1.4.1): the word carries it on its own.
    await expect(debut.getByText('Ahead', { exact: true })).toBeVisible();
    await expect(debut.getByText('Junior Year · Turn 12')).toBeVisible();

    // An obligation the source cannot date prints N/A with the reason, and is never ranked at turn zero.
    const undated = advisor.getByRole('listitem').filter({ hasText: 'Undated' }).first();
    await expect(undated.getByText('N/A')).toBeVisible();
    await expect(undated.getByText('N/A')).toHaveAttribute('title', /no turn/i);

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
        await expect(page.getByRole('heading', { name: 'Races in this run' })).toBeVisible();
        await expect(page.getByRole('heading', { name: 'Actions' })).toBeVisible();
        await expect(page.getByRole('heading', { name: 'Advisor' })).toBeVisible();
        await expect(page.getByRole('heading', { name: 'Scenario' })).toBeVisible();
    }

    // The spec's mobile order: the recommendation stays above the actions (design-2.0 §40).
    await page.setViewportSize({ width: 320, height: 900 });
    const advisor = await page.getByRole('heading', { name: 'Advisor' }).boundingBox();
    const actions = await page.getByRole('heading', { name: 'Actions' }).boundingBox();
    expect((advisor?.y ?? 0), 'the recommendation is not above the actions on mobile').toBeLessThan(actions?.y ?? 0);
});

test('walks the action grid from the keyboard with a visible focus ring', async ({ page }) => {
    await openCockpit(page);

    // Scoped to the Actions region: the section nav (audit 2026-10-12 §3.6) adds a "Races in this
    // run" link whose name also starts with "Race", and the grid's own entry is the one meant here.
    const actions = page.getByRole('region', { name: 'Actions' });
    const training = actions.getByRole('link', { name: /^Training/ });
    await training.focus();
    await expect(training).toBeFocused();

    // Tab moves through the grid in reading order; the next entry takes focus, and it carries the
    // global `:focus-visible` ring rather than relying on a hover state (WCAG 2.4.7).
    await page.keyboard.press('Tab');
    const race = actions.getByRole('link', { name: /^Race/ });
    await expect(race).toBeFocused();
    await expect(race).toBeInViewport();

    const outline = await race.evaluate((el) => getComputedStyle(el).outlineWidth);
    expect(parseFloat(outline), 'the focused grid entry has no focus ring').toBeGreaterThanOrEqual(2);

    // And the last entry is reachable without being hidden under the sticky mobile nav (SC 2.4.11).
    await page.setViewportSize({ width: 320, height: 700 });
    const inheritance = page.getByRole('link', { name: /^Inheritance/ });
    await inheritance.focus();
    await expect(inheritance).toBeInViewport();
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

    // WCAG 2.2 SC 2.5.8, floor 44px (plan §12.1). The same sweep `dashboard.spec.ts` runs.
    // The grid entries are scoped to the Actions region: the section nav (audit 2026-10-12 §3.6)
    // adds a "Races in this run" link whose name also starts with "Race".
    const actions = page.getByRole('region', { name: 'Actions' });
    const targets = [
        actions.getByRole('link', { name: /^Training/ }),
        actions.getByRole('link', { name: /^Race/ }),
        page.getByRole('link', { name: /^Rest/ }),
        page.getByRole('link', { name: /^Recreation/ }),
        page.getByRole('link', { name: /^Scenario action/ }),
        page.getByRole('link', { name: /^Event/ }),
        page.getByRole('link', { name: /^Inheritance/ }),
        // `exact`, because `getByRole`'s name match is a case-insensitive substring: the career bar's
        // door is `Cockpit` and the race strip's own door reads `Go to the Cockpit`.
        page.getByRole('link', { name: 'Cockpit', exact: true }),
        page.getByRole('link', { name: 'Dashboard' }).first(),
        page.getByRole('link', { name: 'Record the first turn through Training' }),
    ];

    for (const target of targets) {
        const box = await target.boundingBox();
        expect(box?.height ?? 0, 'a cockpit control is not sized to the 44px contract').toBeGreaterThanOrEqual(44);
    }
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

    // The correction disclosure opens on the stored turn and writes through the route that already
    // owns a turn: no second endpoint exists for it.
    const correction = page.locator('details', { has: page.getByText('Correct turn 1 by hand') });
    await page.getByText('Correct turn 1 by hand').click();
    // R2-16 gave the correction form the same Energy control the guided form has: the state is a radio
    // group and the number input renders only while the state is `exact`, so recording a figure names
    // its state first. The stored turn carries no state, which is why the input is not there yet.
    await correction.getByRole('radio', { name: 'Exact' }).check();
    await correction.locator('input[name="energy"]').fill('60');
    await correction.getByRole('button', { name: 'Save correction' }).click();

    // The state panel is the proof the write landed, and the proof it landed where the form was posted:
    // a correction made on the Cockpit returns to the Cockpit rather than to the run screen. Energy is
    // read here rather than in the header because the header stopped printing the four readings on
    // 2026-10-08; they are the state panel's, beside the stats the advisor ranks against.
    const state = page.locator('section[aria-labelledby="career-state-heading"]');
    await expect(state.getByText('60/100')).toBeVisible();

    // With Energy recorded the advisor ranks, and the five-element FD-2 shape renders in order:
    // priority, recommendation, benefit, alternatives, reasons, then what would change the answer.
    const advisorRegion = page.getByRole('region', { name: 'Advisor' });
    await expect(advisorRegion.getByRole('heading', { name: 'Current priority' })).toBeVisible();
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
    // Three constants back the ranked answer (the advisory line, the session cost, Wit's cost), so the
    // Confirmed locator resolves to more than one node and `.first()` is the strict-mode requirement.
    await expect(
        advisorRegion.getByRole('img', { name: /^Confirmed/ }).first(),
    ).toBeVisible();
    await expect(advisorRegion.getByRole('img', { name: /^Confirmed/ })).toHaveCount(3);
    // The constant a figure rests on prints its source and read date visibly, not only in a tooltip
    // (ADR-0001 §2 and §7, owner ruling 5a). The source string carries a section anchor between the
    // file name and the date, so the pattern allows it rather than pinning one spelling.
    await expect(advisorRegion.getByText(/Source: UMAMUSUME_REFERENCE\.md.+read 20\d\d-\d\d-\d\d/).first()).toBeVisible();
    await expect(advisorRegion.getByRole('heading', { name: 'What would change this' })).toBeVisible();

    // The Trainer's own button, not an automatic execution, and named for the action it goes to.
    const go = advisorRegion.getByRole('link', { name: 'Go to Speed' });
    await expect(go).toBeVisible();
    // WCAG 2.2 SC 2.5.8 on the decision's own door, which only exists in the ranked state.
    expect(((await go.boundingBox())?.height ?? 0), 'the decision link is under 44px').toBeGreaterThanOrEqual(44);

    // Scoped to the Actions region, not the page: `getByText` matches case-insensitively, and the
    // ranked marker is the grid's, not the card's.
    const actions = page.getByRole('region', { name: 'Actions' });
    await expect(actions.getByText('RECOMMENDED')).toHaveCount(1);
    await expect(actions.getByRole('link', { name: /^Training.*RECOMMENDED/ })).toBeVisible();

    // The ranked state scans too: the accented recommendation card and the RECOMMENDED marker are the
    // two treatments the absent state does not carry.
    const ranked = await buildAxe(page).analyze();
    expect(ranked.violations, JSON.stringify(ranked.violations, null, 2)).toEqual([]);
});

test('passes an axe scan at WCAG A and AA', async ({ page }) => {
    await openCockpit(page);

    // `buildAxe` scopes to `#app`, which is what makes this deterministic: Inertia's NProgress bar is
    // appended to `<body>` as a sibling of the app root and carries an invalid `role="bar"` (KI-63).
    const results = await buildAxe(page).analyze();

    expect(results.violations, JSON.stringify(results.violations, null, 2)).toEqual([]);
});
