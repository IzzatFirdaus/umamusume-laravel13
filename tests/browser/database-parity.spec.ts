import { expect, test } from '@playwright/test';
import { mkdirSync, writeFileSync } from 'node:fs';
import { resolve } from 'node:path';
import { pathToFileURL } from 'node:url';
import { verifyPage, type VerifyReport } from './fixtures/wizard-verify';

/*
 * HFW parity for the Database reference pages and Help.
 *
 * One case per screen, added with that screen's commit, so no case is ever red for a screen whose
 * alignment has not landed. The harness captures the app from the shell band and the wireframe from
 * `.canvas` (`verifyPage`'s `appRootSelector`), because these pages have no wizard canvas.
 *
 * The gate is:
 *   1. the wireframe's spine paired — no top-level `nav.subnav`/`div.card`/`div.grid`/`div.info` lands in
 *      `missing`, which is what stops a stale selector from turning this file into a vacuous pass;
 *   2. `text`, `computed` and `rect` carry only entries the screen records as an `HFW-OVERRIDE` — one
 *      allowlist entry per override, matched against the report line;
 *   3. `excess` and `missing` are recorded, never asserted empty: two implementations of one design
 *      cannot pair every descendant (`career-profile.spec.ts:154` recorded that before this file).
 *
 * Evidence is opt-in so a normal run stays quiet:
 *   PARITY_EVIDENCE=1  writes .scratch-uma/db-parity/parity/<screen>.json
 *   PARITY_SHOTS=1     writes 1280/390, light/dark PNGs to .scratch-uma/db-parity/parity-shots/
 */

const WIREFRAMES = resolve(process.cwd(), 'docs/design-research/frontend-v2-wireframes');
const PARITY = resolve(process.cwd(), '.scratch-uma/db-parity/parity');
const SHOTS = resolve(process.cwd(), '.scratch-uma/db-parity/parity-shots');

type Allow = {
    /** Substrings of one `text` report line (`selector: app="…" hfw="…"`), one per recorded override. */
    text?: string[];
    /** Selector substrings whose computed drift the screen records as an override. */
    computed?: string[];
    /** Selector substrings whose rect drift the screen records as an override. */
    rect?: string[];
};

type Screen = {
    screen: string;
    appUrl: string;
    wireframe: string;
    allow: Allow;
};

const recorded = (list: string[] | undefined, line: string): boolean =>
    (list ?? []).some((needle) => line.includes(needle));

/**
 * The wireframe's `shell.css` palette is not this app's visual system: `DESIGN.md` owns the tokens and the
 * app's ink, surfaces, type steps and radii are its own, so comparing those props exactly would fail on
 * every element of every screen without saying anything about layout. They are recorded as the
 * design-system divergence; the layout props (display, grid, weight, spacing, borders) stay asserted.
 */
const DESIGN_TOKENS = new Set(['color', 'backgroundColor', 'borderColor', 'fontSize', 'lineHeight', 'borderRadius', 'margin']);

/**
 * The wireframe's own top-level blocks; if one of these is missing the page stopped pairing and the run
 * is void. `>` is excluded on purpose: a path under a card is the card's content, not the spine, and
 * matching those would turn a caption/index shift into a spine failure.
 */
const isSpine = (selector: string): boolean =>
    /^(nav\.subnav|div\.card|div\.grid|div\.info):nth-child\(\d+\)$/.test(selector);

function evidence(screen: string, report: VerifyReport): void {
    if (process.env.PARITY_EVIDENCE !== '1') {
        return;
    }

    mkdirSync(PARITY, { recursive: true });
    writeFileSync(`${PARITY}/${screen}.json`, `${JSON.stringify(report, null, 2)}\n`);
}

async function shots(page: import('@playwright/test').Page, screen: string): Promise<void> {
    if (process.env.PARITY_SHOTS !== '1') {
        return;
    }

    mkdirSync(SHOTS, { recursive: true });

    for (const width of [1280, 390]) {
        await page.setViewportSize({ width, height: 900 });

        for (const theme of ['light', 'dark']) {
            await page.evaluate((value) => document.documentElement.setAttribute('data-theme', value), theme);
            await page.waitForTimeout(120);
            await page.screenshot({ path: `${SHOTS}/${screen}-${width}-${theme}.png`, fullPage: true });
        }
    }
}

async function assertParity(page: import('@playwright/test').Page, opts: Screen): Promise<void> {
    const report = await verifyPage(page, {
        appUrl: opts.appUrl,
        hfwUrl: pathToFileURL(resolve(WIREFRAMES, opts.wireframe)).href,
        rootSelector: '.canvas',
        appRootSelector: '[data-shell-band]',
    });

    // Written before the assertions so a failing run still leaves the report the failure is read from.
    evidence(opts.screen, report);

    expect(
        report.missing.filter(isSpine),
        `${opts.screen}: the wireframe's own blocks did not pair — the selectors are stale, so the report measures nothing`,
    ).toEqual([]);

    expect(
        report.text.filter((line) => !recorded(opts.allow.text, line)),
        `${opts.screen}: text drift that no HFW-OVERRIDE records`,
    ).toEqual([]);

    expect(
        report.computed
            .filter((entry) => !DESIGN_TOKENS.has(entry.prop))
            .filter((entry) => !recorded(opts.allow.computed, entry.selector))
            .map((entry) => `${entry.selector} ${entry.prop}: app=${entry.app} hfw=${entry.hfw}`),
        `${opts.screen}: layout-property drift that no HFW-OVERRIDE records`,
    ).toEqual([]);

    // Horizontal geometry of the wireframe's own blocks: same left edge and width. Vertical growth is the
    // app's extra content and is recorded in the report, never asserted.
    expect(
        report.rect
            .filter((entry) => isSpine(entry.selector))
            .filter((entry) => !recorded(opts.allow.rect, entry.selector))
            .filter((entry) => Math.abs(entry.dx) > 1 || Math.abs(entry.dw) > 1)
            .map((entry) => `${entry.selector}: dx=${entry.dx} dw=${entry.dw}`),
        `${opts.screen}: a wireframe block does not start at the same edge or width`,
    ).toEqual([]);

    // The house reflow reading, on the same document: a retagged table must not push the root sideways.
    await page.setViewportSize({ width: 320, height: 900 });
    await page.goto(opts.appUrl, { waitUntil: 'domcontentloaded' });
    await page.locator('#app > *').first().waitFor();

    const overflow = await page.evaluate(
        () => document.documentElement.scrollWidth - document.documentElement.clientWidth,
    );

    expect(overflow, `${opts.screen} scrolls the document sideways at 320px`).toBeLessThanOrEqual(1);

    await shots(page, opts.screen);
}

test('the Shop items catalogue sits on its wireframe', async ({ page }) => {
    await assertParity(page, {
        screen: 'shop-items',
        appUrl: '/database/shop-items',
        wireframe: 'database-shop-items.html',
        allow: {
            text: [
                // HFW-OVERRIDE (data: rows): the mock's ten rows are one publisher's priorities; this
                // page prints the Pro Shop's config rows, so costs, effects and verdicts differ.
                'div.card:nth-child(2) > table.t:nth-child(1)',
                // HFW-OVERRIDE (copy: rules note): one sentence is added, §1.6.10's "no item guarantees
                // training success".
                'div.grid:nth-child(3) > div.card:nth-child(1) > p.note:nth-child(3)',
                // HFW-OVERRIDE (copy: ratings note): the mock's sentence leaves its bold span empty and
                // never names the two counters; this page says "Grade Points and Shop Coins".
                'div.grid:nth-child(3) > div.card:nth-child(2) > p.note:nth-child(4)',
            ],
            computed: [],
            rect: [],
        },
    });
});

test('the Sparks ladders sit on their wireframe', async ({ page }) => {
    await assertParity(page, {
        screen: 'sparks',
        appUrl: '/database/sparks',
        wireframe: 'database-sparks.html',
        allow: {
            text: [
                // HFW-OVERRIDE (data: families): the mock lists four families and names the unique-skill
                // one "Unique"; the reference renders Blue, Pink, Green and White Sparks plus the scenario
                // kind, with white carrying both the skill and the competition sub-kinds.
                'div.card:nth-child(2) > table.t',
                // HFW-OVERRIDE (data: blue ladder) and the column heading that goes with it: the mock's
                // "Cap added" +16 / +8 / +5 is the breakthrough figure with two unpublished grades
                // attached; this card prints the +5 / +12 / +21 starting-stat ladder and the one
                // published ★3 cap raise.
                'div.grid:nth-child(3) > div.card:nth-child(1) > table.t',
                // The card states the distinction the mock's note does not draw, so its sentence and its
                // bold word are this build's.
                'div.grid:nth-child(3) > div.card:nth-child(1) > p.note',
                // HFW-OVERRIDE (data: pink ladder): the mock prints the stat figures under an "Aptitude
                // shift" heading; the published aptitude ladder is graded in letters.
                'div.grid:nth-child(3) > div.card:nth-child(2) > table.t',
                'div.grid:nth-child(3) > div.card:nth-child(2) > p.note',
                // The loop's own note says which factor raises the ceiling, because the mock's says
                // "blue sparks" as though the whole family did it.
                'div.card:nth-child(4) > p.note',
            ],
            computed: [],
            rect: [],
        },
    });
});

test('the Data sources ledger sits on its wireframe', async ({ page }) => {
    await assertParity(page, {
        screen: 'sources',
        appUrl: '/database/sources',
        wireframe: 'database-sources.html',
        allow: {
            text: [
                // HFW-OVERRIDE (copy: tier S row): the mock writes that row with an em dash; shipped
                // copy bans the glyph, so the app's colon form is the required divergence.
                'div.card:nth-child(3) > table.t:nth-child(2) > tbody:nth-child(2) > tr:nth-child(1)',
                // HFW-OVERRIDE (data: ledger columns): the mock's Tier and State columns have no
                // per-dataset value in this build, and its eight sources are not this build's twelve.
                'div.card:nth-child(4) > table.t',
                // HFW-OVERRIDE (data: conflicts): the mock lists four open disagreements; the mood race
                // effect was resolved on 2026-09-27, and the app states each row's two sides in a
                // sentence rather than a pill.
                'div.grid:nth-child(5) > div.card:nth-child(1) > table.t',
                // The note names the resolved row, which the mock's sentence cannot.
                'div.grid:nth-child(5) > div.card:nth-child(1) > p.note',
                // The tiles count this build's ledger and its open rows, not the mock's.
                'div.grid:nth-child(5) > div.card:nth-child(2) > div.grid',
            ],
            computed: [],
            rect: [],
        },
    });
});

test('the Scenarios matrices sit on their wireframe', async ({ page }) => {
    await assertParity(page, {
        screen: 'scenarios',
        appUrl: '/database/scenarios',
        wireframe: 'database-scenarios.html',
        allow: {
            text: [
                // HFW-OVERRIDE (copy: caps note): the mock's note carries its own two examples; this
                // page keeps the base and hard-ceiling sentence and adds the matrix-verification
                // sentences that used to sit in a band above the cards.
                'div.card:nth-child(2) > p.note:nth-child(3)',
                // HFW-OVERRIDE (data: panel row order): the matrix rows follow the config's own key
                // order, and its column headers print the scenario labels rather than the mock's
                // abbreviations. The harness pairs by position, so it compares a different panel on
                // each row: read by panel, all 28 flags agree with `config/scenarios.php`.
                'div.card:nth-child(3) > table.t:nth-child(2)',
                // HFW-OVERRIDE (copy: heading): "Last rework" is ruled over "Ruleset dates" by `5fd9a8ad`.
                'div.card:nth-child(4) > h2:nth-child(1)',
                // HFW-OVERRIDE (data: rework text): the config's own event strings, not the mock's
                // abbreviated ones.
                'div.card:nth-child(4) > table.t:nth-child(2)',
                // The note's antecedent moved with the heading: a rework date, not a ruleset date.
                'div.card:nth-child(4) > p.note:nth-child(3)',
            ],
            computed: [
                // HFW-OVERRIDE (style: no current row): the mock marks its Unity Cup row
                // `tr.is-current`, and `table.t tr.is-current td` raises that row's weight to 700 (900
                // inside its bold outlier). No row of a reference matrix is current, so the highlight
                // is not drawn and the two cells that pair into it read lighter here.
                'div.card:nth-child(2) > table.t:nth-child(2) > tbody:nth-child(2) > tr:nth-child(2)',
            ],
            rect: [],
        },
    });
});

test('the Events catalogue sits on its wireframe', async ({ page }) => {
    await assertParity(page, {
        screen: 'events',
        appUrl: '/database/events',
        wireframe: 'database-events.html',
        allow: {
            text: [
                // HFW-OVERRIDE (data: table columns): the mock's chain/option columns and its four mock
                // rows; this catalogue prints the guide's ten recurring types (§4.4), cadence and all.
                'div.card:nth-child(2) > table.t:nth-child(1)',
                // HFW-OVERRIDE (copy: catalogue note): the count and the cadence sentence are this
                // build's wording of the mock's one-line note.
                'div.card:nth-child(2) > p.note:nth-child(2)',
            ],
            computed: [],
            rect: [],
        },
    });
});
