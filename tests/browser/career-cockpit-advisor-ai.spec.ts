import { expect, test } from '@playwright/test';
import { buildAxe, openAllDisclosures } from '../utils/accessibility';
import { deleteRun } from '../utils/delete-run';
import { recordTurns } from '../utils/record-turns';
import { startCareer } from '../utils/start-career';

/*
 * The AI Advisor panel (`career-cockpit-advisor-ai`): the frontend half of the Advisor AI slice,
 * F6's ten-behavior acceptance matrix from
 * `docs/audits/2026-11-22-advisor-ai-audit/15-implementation-roadmap-f1-f6.md`.
 *
 * The panel sits below `NextDecision` in the advisor rail and renders the response of
 * `GET /training-runs/{run}/advisor-analysis` (Hermes' endpoint, `CockpitController.php:170`).
 * Every case below runs against **fixtures**, not against the paid provider: the endpoint is
 * intercepted with `page.route`, so no live inference call is made and no key is consumed. The
 * shapes are the verified contract in `docs/audits/2026-11-22-advisor-ai-audit/14-api-contract.md`
 * §3, field names read off `App\Services\Advisor\AdvisorAnalysis::toArray()`.
 *
 * The one case that does not intercept (the 404 case) is safe: the local server runs with
 * `ADVISOR_AI_ENABLED` unset, so the controller's own first line answers 404 before any provider
 * is touched, and the feature is switched off by construction rather than by a mock.
 *
 * The run is built the way every other cockpit spec builds one: the wizard's own steps and a turn
 * recorded over `runs.turns.store`, deleted in `afterEach` so the empty-state cases elsewhere keep
 * their world.
 */

const createdRunUrls: string[] = [];

/**
 * The revision of the cockpit the current case opened, captured by `openCockpitAtTurn1` so
 * `analysisFixture()` can stamp its answer with a value the page will accept. The suite is serial
 * (`workers: 1`), so one slot is enough; a case that navigates again re-reads it explicitly.
 */
let renderedRevision = 'fixture-revision';

test.afterEach(async ({ page }) => {
    for (const url of createdRunUrls.splice(0)) {
        await deleteRun(page, url);
    }
});

/**
 * One career at turn 1 with an Energy reading, so the deterministic rail ranks instead of refusing
 * and the analysis has a turn to advise. Returns the cockpit URL the wizard lands on.
 */
async function openCockpitAtTurn1(page: import('@playwright/test').Page): Promise<string> {
    const runUrl = await startCareer(page, 'unity_cup');
    const runId = /\/training-runs\/(\d+)/.exec(runUrl)?.[1] ?? '';
    await recordTurns(page, runId, 1, { energy: '80', choice: 'training-Speed' });
    const cockpitUrl = `${runUrl}/cockpit`;
    await page.goto(cockpitUrl, { waitUntil: 'domcontentloaded' });
    await page.locator('#app > *').first().waitFor();
    createdRunUrls.push(runUrl);

    // The live revision this cockpit was rendered for. Fixtures stamp it so a 200 is accepted;
    // the same-turn case moves it to prove the opposite.
    renderedRevision = await cockpitRevision(page);

    return cockpitUrl;
}

/** The rail's own named region, the anchor every assertion in this file lives under. */
function advisorRail(page: import('@playwright/test').Page) {
    return page.getByRole('region', { name: 'Advisor' });
}

/**
 * The career revision the cockpit was rendered for, read off Inertia's own page payload.
 *
 * The fixture has to carry this exact value: since the panel compares a response's
 * `career_revision` against the page's `advisor_revision`, a hardcoded fixture string would be
 * dropped as stale on every case. Reading it here keeps the fixtures honest without asking any
 * spec to know how a fingerprint is built.
 */
async function cockpitRevision(page: import('@playwright/test').Page): Promise<string> {
    const revision = await page.evaluate(() => {
        const el = document.querySelector('script[data-page="app"]');

        if (el === null || el.textContent === null) {
            return null;
        }

        const props = (JSON.parse(el.textContent) as { props?: { advisor_revision?: string | null } }).props ?? {};

        return props.advisor_revision ?? null;
    });

    expect(revision, 'the cockpit carries no advisor_revision prop').toBeTruthy();

    return revision as string;
}

/** The panel's request button. The panel is the only surface on the rail that names the AI. */
function askButton(page: import('@playwright/test').Page) {
    return page.getByRole('button', { name: 'Ask the AI to analyze this turn' });
}

/** The endpoint pattern, derived the same way the page derives it: the cockpit URL, segment swapped. */
function analysisPattern(runUrl: string): string {
    return `**/training-runs/${/\/training-runs\/(\d+)/.exec(runUrl)?.[1] ?? ''}/advisor-analysis`;
}

/**
 * A 200 fixture in the contract's own shape. `overrides` replaces whole fields, which is how each
 * case states the one fact it is testing (the provider that answered, the action it chose, the
 * turn it belongs to) without re-typing the shape.
 */
function analysisFixture(overrides: Record<string, unknown> = {}): Record<string, unknown> {
    return {
        contract_version: '1',
        career_id: 1,
        // The page's own revision (`openCockpitAtTurn1` captures it), so the answer is fresh for
        // the career on screen. A fixed string would be rejected as stale on every case now that
        // the panel compares revisions; the same-turn case overrides it deliberately.
        career_revision: renderedRevision,
        turn: 1,
        scenario: 'Unity Cup',
        recommended_action: {
            id: 'training-Speed',
            label: 'Speed training',
            kind: 'training',
            eligibility: 'eligible',
        },
        deterministic_action: {
            id: 'training-Speed',
            label: 'Speed training',
        },
        decision: {
            provider: 'deterministic',
            model: null,
            status: 'fallback',
            fallback_used: true,
            preference: {},
            confidence: null,
        },
        explanation: 'The engine ranked Speed first: the largest deficit against the target you entered.',
        explanation_source: 'deterministic',
        evidence: [],
        retrieval: {
            degraded: false,
            lexical_count: 2,
            semantic_count: 0,
        },
        unknowns: [],
        conflicts: [],
        obligation: null,
        alternatives: [],
        blocked: [],
        generated_at: '2026-11-23T02:00:00+00:00',
        ...overrides,
    };
}

const EVIDENCE = [
    {
        id: 'ev-1',
        path: 'docs/UMAMUSUME_REFERENCE.md',
        heading: 'Training',
        line: 120,
        servers: ['Global'],
        citation: 'docs/UMAMUSUME_REFERENCE.md § Training [Global]',
    },
];

const ALTERNATIVES = [
    {
        id: 'training-Power',
        label: 'Power training',
        reason: 'the second-largest deficit',
        engine_choice: false,
    },
    {
        id: 'training-Speed',
        label: 'Speed training',
        reason: 'the largest deficit',
        engine_choice: true,
    },
];

const BLOCKED = [
    {
        id: 'training-Stamina',
        label: 'Stamina training',
        blocker: 'the Energy band is Low: a tiring session is not offered in it',
    },
];

/**
 * Behavior 1: the existing deterministic Advisor is preserved. The panel adds a block below
 * `NextDecision`; it never re-renders, replaces or reorders the five-element answer, and its
 * primary door is still the engine's.
 */
test('keeps the deterministic advisor untouched: the AI block sits below it', async ({ page }) => {
    const cockpitUrl = await openCockpitAtTurn1(page);

    const rail = advisorRail(page);
    const runUrl = cockpitUrl.replace(/\/cockpit$/, '');

    // The AI block is in the rail, and it sits after the recommendation slot the order test names.
    await expect(rail.getByRole('heading', { name: 'AI advisor' })).toBeVisible();

    const order = await rail.evaluate((region) => {
        const nodes = Array.from(region.querySelectorAll('h3, p'));
        const recommendation = nodes.find((node) => {
            const text = (node.textContent ?? '').trim();
            if (node.tagName === 'H3') {
                return text === 'Recommended action' || text === 'AI advisor';
            }
            return text.startsWith('Not enough information to recommend an action.');
        });
        const ai = nodes.find((node) => node.textContent?.trim() === 'AI advisor');
        if (recommendation === undefined || ai === undefined) {
            return { error: 'the anchors are not both in the rail' };
        }

        // Node.DOCUMENT_POSITION_PRECEDING === 2: the recommendation slot comes before the AI block.
        return (ai.compareDocumentPosition(recommendation) & 2) !== 0;
    });
    expect(order, 'the AI block must sit below the deterministic recommendation').toBe(true);

    // The recommendation slot renders the engine's answer, and the primary door points at the
    // engine's action: the panel below it never re-aimed the door.
    await expect(rail.getByRole('heading', { name: 'Recommended action' })).toBeVisible();
    const primaryDoor = rail.getByRole('link', { name: /Go to / });
    await expect(primaryDoor).toHaveCount(1);
    await expect(primaryDoor).toBeVisible();
    expect(runUrl).toMatch(/\/training-runs\/\d+$/);
});

/**
 * Behaviors 2 and 3: the user initiates the request, and a 200 renders the answer. The request is
 * the Trainer's decision, not the page's: nothing fires on mount, and the one click is the only
 * trigger the panel exposes.
 */
test('fires on the Trainer click, not on mount, and renders the 200 answer', async ({ page }) => {
    const runUrl = (await openCockpitAtTurn1(page)).replace(/\/cockpit$/, '');
    await page.route(analysisPattern(runUrl), (route) => route.fulfill({ json: analysisFixture() }));

    const rail = advisorRail(page);

    // Mount fired nothing: no request was in flight and the button was on screen.
    const requests: string[] = [];
    page.on('request', (request) => requests.push(request.url()));
    await expect(askButton(page)).toBeVisible();
    expect(requests.filter((url) => url.includes('/advisor-analysis'))).toEqual([]);

    // One click, one request, and the answer renders: the explanation, the turn badge and the
    // attribution line the provider state composed.
    await askButton(page).click();
    await expect
        .poll(() => requests.filter((url) => url.includes('/advisor-analysis')).length, { timeout: 10_000 })
        .toBe(1);
    await expect(rail.getByText('The engine ranked Speed first: the largest deficit against the target you entered.')).toBeVisible();
    await expect(rail.getByText('Turn 1', { exact: true })).toBeVisible();
    await expect(rail.getByText("The engine's own ranking, no AI consulted.")).toBeVisible();
});

/**
 * Behavior 4: provider and explanation attribution. Clef's answer and the engine's floor read as
 * different sentences, and the explanation's source line says who wrote it.
 */
test('attributes a Clef answer and its explanation to their owners', async ({ page }) => {
    const runUrl = (await openCockpitAtTurn1(page)).replace(/\/cockpit$/, '');
    await page.route(
        analysisPattern(runUrl),
        (route) =>
            route.fulfill({
                json: analysisFixture({
                    decision: {
                        provider: 'clef',
                        model: 'clef',
                        status: 'validated',
                        fallback_used: false,
                        preference: { 'training-Speed': 0.7, 'training-Power': 0.3 },
                        confidence: 0.7,
                    },
                    explanation: 'Clef ranked the candidates against the target; Speed led by the margin the preference line carries.',
                    explanation_source: 'logfare',
                }),
            }),
    );

    await askButton(page).click();
    const rail = advisorRail(page);

    await expect(rail.getByText('Clef ranked this turn for you.')).toBeVisible();
    await expect(rail.getByText('Explanation: AI.')).toBeVisible();
    // The preference figures are diagnostics, not probabilities: the panel never prints them.
    await expect(rail.getByText('0.7')).toHaveCount(0);
    await expect(rail.getByText('70%')).toHaveCount(0);
});

/**
 * Behavior 5: evidence disclosure. Citations render as text in a disclosure, with no invented
 * links: a repository path is not a served route, and the panel must not turn it into one.
 */
test('shows verified citations as text, never as invented links', async ({ page }) => {
    const runUrl = (await openCockpitAtTurn1(page)).replace(/\/cockpit$/, '');
    await page.route(
        analysisPattern(runUrl),
        (route) => route.fulfill({ json: analysisFixture({ evidence: EVIDENCE }) }),
    );

    await askButton(page).click();
    const rail = advisorRail(page);

    // The native summary is exposed as the <details> group's name in Chromium, not as a button.
    const disclosure = rail.locator('details > summary').filter({ hasText: 'Sources (1)' });
    await expect(disclosure).toBeVisible();

    // A closed disclosure keeps its content out of the tree; open it the way the axe helper does.
    await openAllDisclosures(page);
    await expect(rail.getByText('docs/UMAMUSUME_REFERENCE.md § Training [Global]')).toBeVisible();

    // The citation's path is printed as text. There is no link whose href is a repository path,
    // because no such route exists: a link to it would be the invented URL F4 forbids.
    const links = await rail.locator('a[href*="UMAMUSUME_REFERENCE"]').count();
    expect(links, 'a citation path is text, not a link').toBe(0);
});

/**
 * Accessibility regression (the R5 handoff exception): the Sources (n) summary is a 44px
 * project-convention target, keyboard-operable and visibly focused at every supported width.
 * The height assertion is rendered geometry, not the presence of a utility class: a class can be
 * present while the control still paints short. Native <details>/<summary> remains the control.
 */
test('Sources disclosure is a 44px keyboard-operable target at 1280, 768, 390 and 320', async ({ page }) => {
    const runUrl = (await openCockpitAtTurn1(page)).replace(/\/cockpit$/, '');
    await page.route(
        analysisPattern(runUrl),
        (route) => route.fulfill({ json: analysisFixture({ evidence: EVIDENCE }) }),
    );

    await askButton(page).click();
    const rail = advisorRail(page);

    // 1. The controlled 200 reached ready: the panel, the turn badge and the attribution are up.
    await expect(rail.getByRole('heading', { name: 'AI advisor' })).toBeVisible();
    await expect(rail.getByText('Turn 1', { exact: true })).toBeVisible();
    await expect(rail.getByText(/own ranking, no AI consulted/)).toBeVisible();

    // 2. The disclosure is present under its visible name. Chromium exposes a closed
    // <summary> as the <details> group's name rather than as a button, so locate the native
    // element itself instead of relying on a role mapping that changes with open state.
    const disclosure = rail.locator('details > summary').filter({ hasText: 'Sources (1)' });
    await expect(disclosure).toBeVisible();
    expect((await disclosure.textContent())?.trim()).toBe('Sources (1)');
    const details = disclosure.locator('xpath=..');
    const citation = rail.getByText('docs/UMAMUSUME_REFERENCE.md § Training [Global]');
    const isOpen = (): Promise<boolean> =>
        details.evaluate((element) => (element as HTMLDetailsElement).open);

    const widths = [1280, 768, 390, 320];
    const geometry: Record<number, { w: number; h: number }> = {};

    // Measure every width first, then assert: a failure still records the whole matrix rather
    // than stopping at the first short target.
    for (const width of widths) {
        await page.setViewportSize({ width, height: 900 });
        geometry[width] = await disclosure.evaluate((element) => {
            const rect = element.getBoundingClientRect();
            return { w: Math.round(rect.width), h: Math.round(rect.height) };
        });
    }
    console.log('ADVISOR-SOURCES-GEOMETRY ' + JSON.stringify(geometry));

    for (const width of widths) {
        await page.setViewportSize({ width, height: 900 });

        // 3. Rendered target height (project convention), not a class-name check.
        expect(geometry[width]?.h, `Sources summary height at ${width}px`).toBeGreaterThanOrEqual(44);
        expect(geometry[width]?.w, `Sources summary width at ${width}px`).toBeGreaterThan(0);

        // 4. No document-level horizontal overflow at this width.
        const noOverflow = await page.evaluate(
            () => document.body.scrollWidth <= document.body.clientWidth + 1,
        );
        expect(noOverflow, `horizontal overflow at ${width}px`).toBe(true);

        // 5. Keyboard reachability: Tab from the rail's primary door lands on the summary.
        await rail.getByRole('link', { name: /^Go to / }).focus();
        let reachedByTab = false;
        for (let step = 0; step < 12; step += 1) {
            await page.keyboard.press('Tab');
            if (await disclosure.evaluate((element) => document.activeElement === element)) {
                reachedByTab = true;
                break;
            }
        }
        expect(reachedByTab, `Tab reaches the Sources summary at ${width}px`).toBe(true);

        // 6. The focus indicator paints while keyboard focus is on the control.
        const ring = await disclosure.evaluate((element) => {
            const style = getComputedStyle(element);
            return { style: style.outlineStyle, width: parseFloat(style.outlineWidth) };
        });
        expect(ring.style, `focus ring style at ${width}px`).toBe('solid');
        expect(ring.width, `focus ring width at ${width}px`).toBeGreaterThanOrEqual(2);

        // 7-9. Native semantics: closed hides, Enter opens, Space closes.
        expect(await isOpen(), `disclosure starts closed at ${width}px`).toBe(false);
        await expect(citation).toBeHidden();
        await page.keyboard.press('Enter');
        await expect.poll(isOpen).toBe(true);
        await expect(citation).toBeVisible();
        await page.keyboard.press('Space');
        await expect.poll(isOpen).toBe(false);
        await expect(citation).toBeHidden();
    }

    // 10. Surrounding Advisor controls still work: the deterministic door is unchanged and the
    // AI block adds no focus stop beyond the disclosure itself.
    const door = rail.getByRole('link', { name: /^Go to / });
    await expect(door).toHaveCount(1);
    await expect(door).toBeVisible();
    expect(await door.getAttribute('href')).toBeTruthy();
    // summary -> details -> the AI block itself; other rail sections share the mt-4 border-t-2
    // idiom, so walk up from the disclosure rather than matching that class globally.
    const panelFocusStops = await disclosure.locator('xpath=../..').evaluate(
        (panel) =>
            panel.querySelectorAll('a[href], button, summary, [tabindex]:not([tabindex="-1"])').length,
    );
    expect(panelFocusStops, 'one focus stop in the AI block: the native disclosure').toBe(1);
    await expect(askButton(page)).toHaveCount(0);
});

/**
 * The three ratified themes × the four supported widths. `data-theme` is the attribute
 * `useTheme()` mirrors from the server-side preference; setting it directly exercises the same
 * CSS tokens without a Preferences write. No theme-specific styling lives in the AI block, so
 * the assertion is that the corrected control and its panel keep their geometry and stay inside
 * the document under each palette.
 */
test('keeps the disclosure and panel intact across all three themes and four widths', async ({ page }) => {
    const runUrl = (await openCockpitAtTurn1(page)).replace(/\/cockpit$/, '');
    await page.route(
        analysisPattern(runUrl),
        (route) => route.fulfill({ json: analysisFixture({ evidence: EVIDENCE }) }),
    );

    await askButton(page).click();
    const rail = advisorRail(page);
    const disclosure = rail.locator('details > summary').filter({ hasText: 'Sources (1)' });
    await expect(disclosure).toBeVisible();

    const pages: Record<string, string> = {};
    for (const theme of ['light', 'dark', 'eva01']) {
        await page.evaluate((value) => {
            document.documentElement.dataset.theme = value;
        }, theme);
        pages[theme] = await page.evaluate(() =>
            getComputedStyle(document.documentElement).getPropertyValue('--color-page').trim(),
        );

        for (const width of [1280, 768, 390, 320]) {
            await page.setViewportSize({ width, height: 900 });
            const height = await disclosure.evaluate(
                (element) => element.getBoundingClientRect().height,
            );
            expect(height, `${theme} @${width}: summary height`).toBeGreaterThanOrEqual(44);
            const noOverflow = await page.evaluate(
                () => document.body.scrollWidth <= document.body.clientWidth + 1,
            );
            expect(noOverflow, `${theme} @${width}: horizontal overflow`).toBe(true);
        }
    }
    console.log('ADVISOR-THEME-PALETTES ' + JSON.stringify(pages));
    expect(new Set(Object.values(pages)).size, 'three distinct page palettes').toBe(3);
    await expect(rail.getByText('AI advisor', { exact: true })).toBeVisible();
});

/**
 * Behavior 6: the disabled state. The local server runs with the feature off, so the endpoint's
 * real answer is the 404 the controller composes. The panel reads it as a quiet off-line, not an
 * error: no `role="alert"`, no retry button.
 */
test('renders the 404 as a quiet terminal off-state without a retry', async ({ page }) => {
    const runUrl = (await openCockpitAtTurn1(page)).replace(/\/cockpit$/, '');
    await page.route(analysisPattern(runUrl), (route) =>
        route.fulfill({ status: 404, body: 'not found' }),
    );

    await askButton(page).click();
    const rail = advisorRail(page);

    await expect(
            rail.getByText(
                "No AI analysis is available for this run here. The recommendation above is the engine's own, and it stands on its own.",
            ),
        ).toBeVisible();
    await expect(rail.getByRole('alert')).toHaveCount(0);
    await expect(page.getByRole('button', { name: 'Try again' })).toHaveCount(0);
});

/**
 * Behavior 7: the 503 failure is recoverable. The server's own line prints in a `role="alert"`,
 * a retry is offered, and the retry recovers when the endpoint answers 200.
 */
test('shows the 503 line with a retry that recovers', async ({ page }) => {
    const runUrl = (await openCockpitAtTurn1(page)).replace(/\/cockpit$/, '');

    let calls = 0;
    await page.route(analysisPattern(runUrl), (route) => {
        calls += 1;

        if (calls === 1) {
            return route.fulfill({
                status: 503,
                contentType: 'application/json',
                body: JSON.stringify({
                    error: {
                        code: 'advisor_analysis_unavailable',
                        message: 'The analysis could not be produced. The recommendation on the cockpit is unaffected.',
                    },
                }),
            });
        }

        return route.fulfill({ json: analysisFixture() });
    });

    await askButton(page).click();

    const alert = advisorRail(page).getByRole('alert');
    await expect(alert).toHaveText(
        'The analysis could not be produced. The recommendation on the cockpit is unaffected.',
    );

    await page.getByRole('button', { name: 'Try again' }).click();
    await expect(advisorRail(page).getByText('The engine ranked Speed first: the largest deficit against the target you entered.')).toBeVisible();
    expect(calls).toBe(2);
});

/**
 * Behavior 8: stale-result rejection. A response stamped for an earlier turn never overwrites the
 * view of the turn the page stands on. The fixture answers turn 0 for a page that stands on turn 1;
 * the panel must drop it back to the idle state rather than render it.
 */
test('drops a response for an earlier turn instead of rendering it', async ({ page }) => {
    const runUrl = (await openCockpitAtTurn1(page)).replace(/\/cockpit$/, '');
    await page.route(
        analysisPattern(runUrl),
        (route) => route.fulfill({ json: analysisFixture({ turn: 0 }) }),
    );

    await askButton(page).click();

    const rail = advisorRail(page);

    // The stale answer never rendered: no attribution line, no turn badge.
    await expect
        .poll(async () => (await askButton(page).count()) > 0, { timeout: 10_000 })
        .toBe(true);
    await expect(rail.getByText('Clef ranked this turn for you.')).toHaveCount(0);
    await expect(rail.getByText('Turn 1', { exact: true })).toHaveCount(0);
});

/**
 * Behavior 8b: the delayed-response race. The server's revision key protects only its cache, not
 * the wire: a response that left before a newer request started can still land after it. The
 * composable's request token makes the late 200 a no-op — the page holds the newer answer, and
 * the late duplicate never renders (Hermes sitrep 2026-11-23 §3).
 */
test('drops a delayed response once a newer request superseded it', async ({ page }) => {
    const runUrl = (await openCockpitAtTurn1(page)).replace(/\/cockpit$/, '');
    let calls = 0;
    let releaseFirst: (() => void) | null = null;
    const firstGates = new Promise<void>((resolve) => {
        releaseFirst = resolve;
    });

    await page.route(analysisPattern(runUrl), async (route) => {
        calls += 1;
        if (calls === 1) {
            // The first request hangs until the second has already been answered.
            await firstGates;
            return route.fulfill({ json: analysisFixture({ turn: 1 }) });
        }
        return route.fulfill({ json: analysisFixture({ turn: 1 }) });
    });

    // First click: the request that will be left hanging on the gated route.
    await askButton(page).click();
    await expect(advisorRail(page).getByText('Working on it.')).toBeVisible();

    // The Trainer cancels and asks again: request two is the one this page now waits for.
    await page.getByRole('button', { name: 'Cancel' }).click();
    await expect(advisorRail(page).getByRole('button', { name: 'Ask the AI to analyze this turn' })).toBeVisible();
    await askButton(page).click();
    await expect(
        advisorRail(page).getByText(
            'The engine ranked Speed first: the largest deficit against the target you entered.',
        ),
    ).toBeVisible();

    // Release the first response late: it must not overwrite, re-render or reset anything.
    (releaseFirst as (() => void) | null)?.();
    await page.waitForTimeout(300);

    const rail = advisorRail(page);
    await expect(rail.getByText('Turn 1', { exact: true })).toBeVisible();
    await expect(rail.getByText('Working on it.')).toHaveCount(0);
    expect(calls).toBe(2);
});

/**
 * The deterministic 200 observed in production carries `preference: []` — a PHP list where the
 * documentation typed an object (Hermes' captured payload, sitrep 2026-11-23 §3). The type
 * documents the tolerance; this case pins that such a payload renders the full answer rather
 * than tripping on the empty-list shape.
 */
test('renders the observed deterministic payload with preference as an empty list', async ({ page }) => {
    const runUrl = (await openCockpitAtTurn1(page)).replace(/\/cockpit$/, '');
    await page.route(
        analysisPattern(runUrl),
        (route) => route.fulfill({ json: analysisFixture({ decision: { preference: [] } }) }),
    );

    await askButton(page).click();

    const rail = advisorRail(page);
    await expect(
        rail.getByText(
            'The engine ranked Speed first: the largest deficit against the target you entered.',
        ),
    ).toBeVisible();
    await expect(rail.getByText('Turn 1', { exact: true })).toBeVisible();
});

/**
 * Behavior 9: no automatic gameplay execution. The panel renders advice; it exposes no control
 * that records a turn, posts a write, or re-aims the rail's primary door. The only links inside
 * the panel are none at all, and the rail's single primary door is still the engine's.
 */
test('never executes or re-aims a gameplay action', async ({ page }) => {
    const runUrl = (await openCockpitAtTurn1(page)).replace(/\/cockpit$/, '');
    await page.route(
        analysisPattern(runUrl),
        (route) =>
            route.fulfill({
                json: analysisFixture({
                    // Clef chose a different action than the engine. The difference must be
                    // stated without moving the door: the panel names both, the door stays the
                    // engine's.
                    recommended_action: {
                        id: 'training-Power',
                        label: 'Power training',
                        kind: 'training',
                        eligibility: 'eligible',
                    },
                    decision: {
                        provider: 'clef',
                        model: 'clef',
                        status: 'validated',
                        fallback_used: false,
                        preference: { 'training-Power': 0.6, 'training-Speed': 0.4 },
                        confidence: 0.6,
                    },
                }),
            }),
    );

    const writes: string[] = [];
    page.on('request', (request) => {
        if (request.method() !== 'GET') {
            writes.push(`${request.method()} ${request.url()}`);
        }
    });

    await askButton(page).click();
    const rail = advisorRail(page);
    await expect(rail.getByText(/Clef would pick Power training/)).toBeVisible();

    // The rail's one primary door is the engine's answer, untouched by the AI block below it.
    // Its label is derived from the live readings (Stamina on this fixture), not from the stubbed
    // AI response: assert the single door and the absence of the AI's candidate as a door.
    const primaryDoor = rail.getByRole('link', { name: /^Go to / });
    await expect(primaryDoor).toHaveCount(1);
    await expect(primaryDoor).toBeVisible();
    await expect(rail.getByRole('link', { name: 'Go to Power training' })).toHaveCount(0);

    // Nothing in the panel is a link at all, and the interaction posted no write. Scope from the
    // disclosure: other rail sections reuse the mt-4 border-t-2 idiom and contain their own links.
    const aiBlock = rail
        .locator('details > summary')
        .filter({ hasText: /^Sources \(/ })
        .locator('xpath=../..');
    const panelLinks = await aiBlock.locator('a').count();
    expect(panelLinks, 'the AI block contains no links').toBe(0);
    expect(writes, 'the panel interaction posted no write').toEqual([]);
});

/**
 * Behavior 10: the existing Cockpit layout and prop contracts are preserved. The cockpit's own
 * frozen assertions (the advisor prop, the rail's region, the card order) are the existing spec's;
 * this case re-asserts the three facts an integration can break: the rail is still the named
 * region, the advisor card's block order still reads recommendation before the AI block before
 * the standing explanation, and the rail's standing explanation still closes the rail.
 */
test('keeps the rail\u2019s block order and its standing explanation', async ({ page }) => {
    const runUrl = (await openCockpitAtTurn1(page)).replace(/\/cockpit$/, '');
    await page.route(
        analysisPattern(runUrl),
        (route) => route.fulfill({ json: analysisFixture() }),
    );
    await askButton(page).click();
    const rail = advisorRail(page);
    await expect(rail.getByText('The engine ranked Speed first')).toBeVisible();

    const order = await rail.evaluate((region) => {
        const nodes = Array.from(region.querySelectorAll('h3, p'));
        const recommendation = nodes.find((node) => {
            const text = (node.textContent ?? '').trim();
            if (node.tagName === 'H3') {
                return text === 'Recommended action';
            }
            return text.startsWith('Not enough information to recommend an action.');
        });
        const ai = nodes.find((node) => node.tagName === 'H3' && node.textContent?.trim() === 'AI advisor');
        const standing = nodes.find(
            (node) =>
                node.tagName === 'P' &&
                node.textContent?.includes('It records nothing and acts for nobody'),
        );
        if (recommendation === undefined || ai === undefined || standing === undefined) {
            return { error: 'an anchor is missing' };
        }

        const before = (later: Element, earlier: Element): boolean =>
            (later.compareDocumentPosition(earlier) & 2) !== 0;

        return {
            aiAfterRecommendation: before(ai, recommendation),
            standingAfterAi: before(standing, ai),
        };
    });
    expect(order).toEqual({ aiAfterRecommendation: true, standingAfterAi: true });
});

/**
 * Behavior 10 (layout half): no horizontal overflow at the two widths the rail's own spec walks
 * (1280 and 320), with the panel rendered. The rail's existing `lg:max-h` + `overflow-y-auto`
 * treatment is unchanged by the block it adds.
 */
test('does not add horizontal overflow with the panel rendered', async ({ page }) => {
    const runUrl = (await openCockpitAtTurn1(page)).replace(/\/cockpit$/, '');
    await page.route(
        analysisPattern(runUrl),
        (route) =>
            route.fulfill({
                json: analysisFixture({ evidence: EVIDENCE, alternatives: ALTERNATIVES, blocked: BLOCKED }),
            }),
    );

    await askButton(page).click();
    await expect(advisorRail(page).getByText('The engine ranked Speed first')).toBeVisible();
    await openAllDisclosures(page);

    for (const width of [1280, 768, 390, 320]) {
        await page.setViewportSize({ width, height: 900 });
        const overflow = await page.evaluate(() => document.body.scrollWidth <= document.body.clientWidth + 1);
        expect(overflow, `horizontal overflow at ${width}px`).toBe(true);
    }
});

/**
 * Accessibility: the panel's states carry their announcements, the button's focus ring renders,
 * and the ready state (disclosure open) passes the shared axe tag set. The loading state's
 * `role="status"` and the failure's `role="alert"` are asserted in the behavior cases above; this
 * case covers the keyboard path and the scan.
 */
test('keyboard: the button is reachable and its focus ring renders; axe is clean', async ({ page }) => {
    const runUrl = (await openCockpitAtTurn1(page)).replace(/\/cockpit$/, '');
    await page.route(
        analysisPattern(runUrl),
        (route) => route.fulfill({ json: analysisFixture({ evidence: EVIDENCE }) }),
    );

    // The rail is the focusable container. Its deterministic block owns focusable controls and
    // links before the AI block, so walk Tab until the ask button is focused: the assertion is
    // that a keyboard path exists and paints a visible ring, not that it is the first stop.
    const rail = advisorRail(page);
    await rail.focus();
    let reachedByTab = false;
    for (let step = 0; step < 20; step += 1) {
        await page.keyboard.press('Tab');
        if (await askButton(page).evaluate((button) => document.activeElement === button)) {
            reachedByTab = true;
            break;
        }
    }
    expect(reachedByTab, 'Tab from the rail reaches the ask button').toBe(true);

    const focus = await askButton(page).evaluate((button) => {
        const active = document.activeElement;
        if (!(active instanceof HTMLElement) || active !== button) {
            return null;
        }

        const style = getComputedStyle(active);

        return { ring: style.outlineStyle, width: parseFloat(style.outlineWidth) };
    });
    expect(focus, 'Tab from the rail reaches the ask button').toMatchObject({ ring: 'solid' });
    expect(focus?.width).toBeGreaterThanOrEqual(2);

    // The ready state, disclosure open, under the shared tag set.
    await askButton(page).click();
    await expect(rail.getByText('The engine ranked Speed first')).toBeVisible();
    await openAllDisclosures(page);

    const { violations } = await buildAxe(page).analyze();
    expect(
        violations.map((item) => ({
            id: item.id,
            nodes: item.nodes.map((node) => ({ target: node.target, summary: node.failureSummary })),
        })),
    ).toEqual([]);
});

/**
 * Behavior 8c (the same-turn guarantee): a response whose turn matches the page but whose career
 * revision does not belongs to a career state this page no longer shows, so it is dropped.
 *
 * This is the case a turn comparison cannot see. A correction to this turn's readings, or a
 * build-target edit, moves the server's fingerprint and leaves the turn number where it was; the
 * fixture below reproduces exactly that: correct turn, wrong career. It is also the only revision
 * case a browser test can stage honestly without a second tab (see the note at the end of this
 * file on cross-tab freshness).
 */
test('drops a same-turn answer whose revision does not match the page', async ({ page }) => {
    const runUrl = (await openCockpitAtTurn1(page)).replace(/\/cockpit$/, '');

    // Turn 1 is what the page stands on; the revision is a career the page does not show.
    await page.route(
        analysisPattern(runUrl),
        (route) => route.fulfill({
            json: analysisFixture({ turn: 1, career_revision: 'a-career-this-page-does-not-show' }),
        }),
    );

    await askButton(page).click();

    const rail = advisorRail(page);

    // Back to idle: the answer never rendered, so the ask button is back and no badge is printed.
    await expect
        .poll(async () => (await askButton(page).count()) > 0, { timeout: 10_000 })
        .toBe(true);
    await expect(rail.getByText('The engine ranked Speed first')).toHaveCount(0);
    await expect(rail.getByText('Turn 1', { exact: true })).toHaveCount(0);

    // And the deterministic answer above it is untouched by the dropped response.
    await expect(rail.getByRole('heading', { name: 'Recommended action' })).toBeVisible();
});

/*
 * What this file does NOT claim: cross-tab freshness. The revision arrives as an Inertia prop, so a
 * career edited in a second tab is only seen here after a visit or a reload — the cockpit has no
 * broadcast channel and this slice adds none (the supported model is refresh-based, and
 * `useAdvisorAnalysis.ts` documents that boundary). The cases above cover same-page staleness, which
 * is what the prop can actually prove.
 */
