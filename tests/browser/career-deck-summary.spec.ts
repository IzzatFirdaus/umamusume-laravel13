import { test, expect } from '@playwright/test';

/*
 * Rendered-copy and behaviour evidence for the deck step's summary section (`SCR-CAR-009`,
 * `resources/js/components/support/DeckSummary.vue`).
 *
 * The server half is `CareerDeckSummaryTest`: it pins the ladder props, the per-rarity caps and the absence
 * of any stored break count. What only a browser can reach is what this file asserts: that the section is
 * where the brief put it (above the duplicate rule, below the two columns), that a card named in the picker
 * appears in it with its ownership word, and that the level column tracks the keystroke rather than the
 * save. The last is the section's whole claim to be real-time, and it is proved by counting writes: the
 * tier readout changes on input and no PUT leaves the page.
 *
 * **The two fixtures are chosen by name, not by position.** The picker's option list is ordered by type then
 * character then epithet, so `first()` would hand back whichever card the catalogue puts at the top and the
 * ladder assertions would drift with the import. Both searches below narrow to exactly one Global card, one
 * SSR and one R, which is what lets the case name the caps it expects: an SSR ladder is
 * 30/35/40/45/50 and an R stops at 40 (`UMAMUSUME_REFERENCE.md` §1.4.2).
 *
 * Axe coverage is provided by `tests/browser/accessibility.spec.ts`. As in `career-legacy-deck-steps.spec.ts`
 * the artwork route is refused: no assertion here reads a decoded image and `artisan serve` is one process.
 */

const SSR_CARD = "Agnes Digital [A Fan's Joy]";
const R_CARD = 'Agnes Digital [Tracen Academy]';
const THIRD_CARD = 'Team Sirius [Passing the Dream On]';

const CAPS: Record<string, number[]> = {
    R: [20, 25, 30, 35, 40],
    SR: [25, 30, 35, 40, 45],
    SSR: [30, 35, 40, 45, 50],
};

const WIDTHS = [320, 390, 768, 1280] as const;

async function hydrate(page: import('@playwright/test').Page, url: string): Promise<void> {
    await page.goto(url, { waitUntil: 'domcontentloaded' });
    await page.locator('#app > *').first().waitFor();
}

/** The theme the Trainer picked, applied the way the shell applies it: an attribute on the root. */
async function applyTheme(page: import('@playwright/test').Page, theme: string): Promise<void> {
    await page.evaluate((value) => document.documentElement.setAttribute('data-theme', value), theme);
}

/** The section is a native `<details>` so the Trainer can collapse it; it is found by its heading. */
const summarySection = (page: import('@playwright/test').Page) =>
    page.locator('details[aria-labelledby="deck-summary-heading"]');

/** The disclosure line, which is also the section's only content while it is closed. */
const disclosure = (page: import('@playwright/test').Page) => summarySection(page).locator('> summary');

/** The section arrives closed, so every case that reads inside it opens it first. */
async function openSummary(page: import('@playwright/test').Page): Promise<void> {
    await disclosure(page).click();
    await expect(summarySection(page)).toHaveAttribute('open', '');
}

/** Equip the one card the picker narrows to, and wait for the step's own read-back. */
async function equip(
    page: import('@playwright/test').Page,
    slot: number,
    search: string,
    cardName: string,
): Promise<void> {
    await page.getByRole('button', { name: `Choose a card for Slot ${slot}` }).click();
    const dialog = page.getByRole('dialog', { name: `Choose a card for Slot ${slot}` });
    await dialog.locator('input[name="q"]').fill(search);
    await dialog.getByRole('button', { name: 'Filter' }).click();

    // The row prints the card's name, not the terms typed into the box, so the count is on the name.
    const row = dialog.locator('ul > li').filter({ hasText: cardName });
    await expect(row).toHaveCount(1);
    await row.getByRole('button', { name: 'Equip', exact: true }).click();
    await expect(page.getByText('Deck saved.')).toBeVisible();
}

/** Three assigned cards: the widest state the section reaches, and the one a containment reading needs. */
async function assignThree(page: import('@playwright/test').Page): Promise<void> {
    await hydrate(page, '/career/setup/deck');
    await equip(page, 1, 'Agnes Joy', SSR_CARD);
    await equip(page, 2, 'Digital Tracen', R_CARD);
    await equip(page, 6, 'Team Sirius', THIRD_CARD);
}

test.beforeEach(async ({ page }) => {
    await page.route('**/artwork/**', (route) => route.abort());
});

test.describe('Setup step 5 — the deck summary', () => {
    test('renders empty, above the duplicate rule', async ({ page }) => {
        await hydrate(page, '/career/setup/deck');

        const summary = summarySection(page);
        await expect(summary).toBeVisible();
        await expect(summary.getByRole('heading', { level: 2 })).toContainText('Deck summary');
        await expect(summary).toContainText('0 assigned');

        await openSummary(page);
        await expect(summary.locator('> p').nth(1)).toContainText('Nothing assigned yet.');

        // The brief places the section above the duplicate rule. Bounding boxes, not DOM order: the rule
        // sits outside the section, so ancestry assertions would pass even if the page moved it below.
        const box = await summary.boundingBox();
        const rule = await page.locator('#deck-duplicate-rule').boundingBox();
        expect(box?.y).toBeDefined();
        expect(box!.y + box!.height).toBeLessThanOrEqual(rule!.y);
    });

    test('arrives closed, states the count on the closed line, and remembers no opening', async ({ page }) => {
        await page.setViewportSize({ width: 320, height: 900 });
        await hydrate(page, '/career/setup/deck');
        await equip(page, 1, 'Agnes Joy', SSR_CARD);

        // The shipped default: closed, with the assigned count as the whole statement on the line.
        const summary = summarySection(page);
        await expect(summary).not.toHaveAttribute('open');
        await expect(disclosure(page)).toContainText('1 assigned');
        await expect(summary.locator('> p').first()).toBeHidden();

        // A closed section holds no table in the accessibility tree at all, which is the arrival reading
        // 1.4.10 asks for: the wide reference table is not on the page until the Trainer asks for it.
        await expect(summary.getByRole('table')).toHaveCount(0);

        for (const theme of ['light', 'dark']) {
            await applyTheme(page, theme);

            const reading = await page.evaluate(() => {
                window.scrollTo(400, 0);

                return { scroll: document.documentElement.scrollWidth, pan: window.scrollX };
            });

            expect(reading.scroll, `${theme}: the closed section still widens the page`).toBe(320);
            expect(reading.pan, `${theme}: the page pans with the section closed`).toBe(0);
        }

        await openSummary(page);
        // One card assigned, two tables open: the assigned-cards table and that card's own ladder.
        await expect(summary.getByRole('table')).toHaveCount(2);

        // Away and back. Nothing persists the open state, so the step re-arrives at the preview rather
        // than wherever the last visit left it.
        await hydrate(page, '/career/setup/target');
        await hydrate(page, '/career/setup/deck');
        await expect(summarySection(page)).not.toHaveAttribute('open');
    });

    test('names an equipped card, its ownership and its ladder from unbroken to Max Limit Break', async ({ page }) => {
        await page.route('**/artwork/**', (route) => route.abort());
        await hydrate(page, '/career/setup/deck');
        await equip(page, 1, 'Agnes Joy', SSR_CARD);

        const summary = summarySection(page);
        await expect(summary).toContainText('1 assigned');
        await openSummary(page);

        const row = summary.getByRole('table').first().locator('tbody > tr');
        await expect(row).toHaveCount(1);
        await expect(row).toContainText('Slot 1');
        await expect(row.getByRole('link', { name: SSR_CARD })).toBeVisible();
        await expect(row).toContainText('SSR');
        await expect(row).toContainText('Power');

        // An equip saves the flag it already holds, or Owned where the Trainer has not said yet
        // (`DeckSelect.vue`'s `equipCard()`), so the row reads Owned rather than an absence.
        await expect(row).toContainText('Owned');

        await page.locator('#deck-slot-1').getByRole('button', { name: 'Rented' }).click();
        // The summary prints the same word the slot's own control prints, one rendering of one value.
        await expect(summary.getByRole('table').first().locator('tbody > tr')).toContainText('Rented');

        // The ladder: five tiers, named 0 unbroken through Max Limit Break, capped at the SSR levels.
        const ladder = page.locator('section[aria-labelledby="deck-ladder-1"]');
        const headers = ladder.locator('thead th');
        await expect(headers).toHaveCount(6);
        await expect(headers.nth(1)).toContainText('Unbroken');
        await expect(headers.nth(5)).toContainText('Max Limit Break');

        for (const [index, level] of CAPS.SSR.entries()) {
            await expect(headers.nth(index + 1)).toContainText(`Lvl ${level}`);
        }

        // A row per stated effect, each carrying a value in every tier column.
        const firstEffectRow = ladder.locator('tbody > tr').first();
        await expect(firstEffectRow.locator('td')).toHaveCount(5);
        for (const cell of await firstEffectRow.locator('td').all()) {
            await expect(cell).toContainText(/%/);
        }

        await expect(summary).toContainText('not a reading of your collection');

        // AUD-20261030-001 F-03: the level field is bounded by the card's own ceiling, which the payload
        // states (the top tier of the ladder it sends) rather than a number this file copies.
        await expect(page.getByRole('spinbutton', { name: 'Level for Slot 1' })).toHaveAttribute('max', '50');
    });

    test('contains its tables inside a focusable region at 320px and nowhere scrolls the page', async ({ page }) => {
        await assignThree(page);
        await openSummary(page);

        const summary = summarySection(page);
        await expect(summary).toContainText('3 assigned');

        /**
         * The audit's acceptance figure, on the populated state and in both themes: the document is exactly
         * as wide as the viewport, so no table escapes it (WCAG 1.4.10 reflow), and at 320 px that width is
         * 320. Measured with the section OPEN, which is the half default-closed does not cover.
         */
        async function measure(label: string): Promise<void> {
            for (const width of WIDTHS) {
                await page.setViewportSize({ width, height: 900 });

                for (const theme of ['light', 'dark']) {
                    await applyTheme(page, theme);

                    const r = await page.evaluate(() => ({
                        scroll: document.documentElement.scrollWidth,
                        client: document.documentElement.clientWidth,
                        pan: (window.scrollTo(400, 0), window.scrollX),
                    }));

                    expect(r.scroll, `${label} ${width} ${theme}: the page scrolls sideways`).toBe(r.client);
                    expect(r.pan, `${label} ${width} ${theme}: the document pans horizontally`).toBe(0);

                    if (width === 320) {
                        expect(r.scroll, `${label} ${width} ${theme}: scrollWidth is not the viewport`).toBe(320);
                    }
                }
            }
        }

        await measure('3 assigned');

        // The same sweep at the deck's widest state: every slot filled, so seven regions are open at once.
        await equip(page, 3, 'Your Team Ace', 'Mejiro McQueen [Your Team Ace]');
        await equip(page, 4, 'Piece of Mind', 'Super Creek [Piece of Mind]');
        await equip(page, 5, 'Poolside', 'Narita Taishin [Poolside High Tides]');
        await expect(summary).toContainText('6 assigned');

        await measure('6 assigned');

        // The tables scroll in their own region, which a keyboard user must be able to reach: a scroll
        // container with no focus stop cannot be scrolled by the arrow keys (WCAG 2.1.1).
        const region = page.getByRole('region', { name: 'Assigned support cards, scrollable' });
        await expect(region).toHaveAttribute('tabindex', '0');
        await expect(region).toHaveCSS('overflow-x', 'auto');

        const scrolls = page.getByRole('region', { name: /values by level, scrollable/ });
        await expect(scrolls).toHaveCount(6);

        await page.setViewportSize({ width: 320, height: 900 });
        await applyTheme(page, 'light');
        await region.focus();
        await page.keyboard.press('ArrowRight');

        expect(await region.evaluate((el) => (el as HTMLElement).scrollLeft)).toBeGreaterThan(0);
        expect(await page.evaluate(() => document.documentElement.scrollLeft)).toBe(0);
    });

    test('every focusable inside the scroll regions shows its whole ring at 320px', async ({ page }) => {
        await assignThree(page);
        await openSummary(page);
        await page.setViewportSize({ width: 320, height: 900 });

        const regions = [
            page.getByRole('region', { name: 'Assigned support cards, scrollable' }),
            page.getByRole('region', { name: /values by level, scrollable/ }).first(),
        ];

        let seen = 0;

        for (const theme of ['light', 'dark']) {
            await applyTheme(page, theme);

            for (const region of regions) {
                const focusables = await region.locator('a, summary, button, [tabindex]').all();
                seen += focusables.length;

                // The house ring is `outline: 2px` with a 2px offset (`app.css`'s `:focus-visible` rule), so
                // a whole ring reaches 4 px outside the element's box. `contain-paint` on the region clips
                // descendant painting at that box, which is what this measures: an element sitting entirely
                // inside the scrollport must still show all four sides of its ring.
                //
                // One real Tab first: `:focus-visible` answers to the input modality, and programmatic focus
                // after a keyboard press carries the ring the way a keyboard user's does.
                await page.keyboard.press('Tab');
                const name = await region.getAttribute('aria-label');

                for (const [index, el] of focusables.entries()) {
                    const reading = await el.evaluate((node, args) => {
                        const host = node.closest('[role="region"]') as HTMLElement;
                        host.scrollLeft = 0;
                        node.scrollIntoView({ block: 'nearest', inline: 'nearest' });
                        (node as HTMLElement).focus();

                        const s = getComputedStyle(node);
                        const c = host.getBoundingClientRect();
                        const r = node.getBoundingClientRect();
                        const reach = (parseFloat(s.outlineWidth) || 0) + (parseFloat(s.outlineOffset) || 0);

                        return {
                            id: args.index,
                            label: (node.getAttribute('aria-label') ?? node.textContent ?? node.tagName)
                                .trim().replace(/\s+/g, ' ').slice(0, 28),
                            outlineStyle: s.outlineStyle,
                            outlineWidth: s.outlineWidth,
                            ring: reach,
                            element: { l: r.left, rt: r.right, t: r.top, b: r.bottom },
                            clip: { l: c.left, rt: c.right, t: c.top, b: c.bottom },
                            fullyInside: r.left >= c.left - 0.5 && r.right <= c.right + 0.5
                                && r.top >= c.top - 0.5 && r.bottom <= c.bottom + 0.5,
                        };
                    }, { index });

                    if (! reading.fullyInside) {
                        // An element that straddles the scrollport edge is cut by the scroll, not by the
                        // containment: the same geometry would clip without `contain-paint`.
                        continue;
                    }

                    expect(reading.outlineStyle, `${theme} ${name} [${reading.label}] has no visible ring`)
                        .not.toBe('none');
                    expect(reading.ring, `${theme} ${name} [${reading.label}] draws no ring`).toBeGreaterThan(0);

                    const clipped = reading.element.l - reading.ring < reading.clip.l - 0.5
                        || reading.element.rt + reading.ring > reading.clip.rt + 0.5
                        || reading.element.t - reading.ring < reading.clip.t - 0.5
                        || reading.element.b + reading.ring > reading.clip.b + 0.5;

                    expect(
                        clipped,
                        `${theme} ${name} [${reading.label}] ring cut by the region: `
                        + `${JSON.stringify({ e: reading.element, c: reading.clip, ring: reading.ring })}`,
                    ).toBe(false);
                }
            }
        }

        // The non-vacuity guard, on the state three cards make: one link per assigned card, and an
        // `AbsenceValue` disclosure for the level and the tier while those are unrecorded. The ladder
        // region is reference numbers and holds no focusable, which is its own measured answer.
        // One theme's count is enough: it is the same DOM in both.
        expect(seen, 'no focusable was measured inside either region').toBeGreaterThanOrEqual(3);
    });

    test('prints the rarity ceiling in ink, not in the risk hue', async ({ page }) => {
        await hydrate(page, '/career/setup/deck');
        await equip(page, 2, 'Digital Tracen', R_CARD);
        await openSummary(page);
        await page.getByRole('spinbutton', { name: 'Level for Slot 2' }).fill('45');

        // The sentence is the tier cell's own span: `getByText('Above the ladder', { exact: true })` finds
        // nothing, because the element's text runs on into the nested explanation it carries.
        const sentence = summarySection(page)
            .getByRole('table')
            .first()
            .locator('tbody > tr th, tbody > tr td')
            .last()
            .locator('> span');

        await expect(sentence).toHaveCount(1);
        await expect(sentence).toContainText('Above the ladder');

        for (const width of WIDTHS) {
            await page.setViewportSize({ width, height: 900 });

            for (const theme of ['light', 'dark']) {
                await applyTheme(page, theme);

                // The audit's instrument, unchanged: sRGB relative luminance against the first solid
                // ancestor background, two decimals (WCAG 1.4.3's 4.5:1 for body text).
                const reading = await sentence.evaluate((el) => {
                    const parse = (value: string): number[] => {
                        const m = value.match(/rgba?\(([^)]+)\)/);

                        return m ? m[1].split(',').slice(0, 3).map((n) => Number(n.trim())) : [0, 0, 0];
                    };
                    const lum = (rgb: number[]): number => {
                        const [r, g, b] = rgb.map((c) => {
                            const s = c / 255;

                            return s <= 0.03928 ? s / 12.92 : ((s + 0.055) / 1.055) ** 2.4;
                        });

                        return 0.2126 * r + 0.7152 * g + 0.0722 * b;
                    };
                    const solid = (value: string): boolean => {
                        if (value === 'transparent') {
                            return false;
                        }

                        const m = value.match(/rgba\(([^)]+)\)/);

                        return m ? Number(m[1].split(',')[3]?.trim() ?? '1') > 0.95 : true;
                    };

                    let node: Element | null = el;

                    while (node) {
                        const bg = getComputedStyle(node).backgroundColor;

                        if (solid(bg)) {
                            const [a, b] = [lum(parse(getComputedStyle(el).color)), lum(parse(bg))];
                            const [hi, lo] = a > b ? [a, b] : [b, a];

                            return {
                                color: getComputedStyle(el).color,
                                bg,
                                ratio: Number(((hi + 0.05) / (lo + 0.05)).toFixed(2)),
                            };
                        }

                        node = node.parentElement;
                    }

                    return { color: '', bg: '', ratio: 0 };
                });

                expect(reading.ratio, `${width} ${theme}: ${reading.color} on ${reading.bg}`)
                    .toBeGreaterThanOrEqual(4.5);
            }
        }
    });

    test('collapses back to its heading, which still states the count', async ({ page }) => {
        await hydrate(page, '/career/setup/deck');
        await equip(page, 1, 'Agnes Joy', SSR_CARD);

        const summary = summarySection(page);
        const line = summary.locator('> summary');
        const assignedTable = summary.locator('table').first();

        // Closed on arrival, and the line carries the count while it is closed: the state a Trainer reads
        // without asking for the detail.
        await expect(assignedTable).toBeHidden();
        await expect(line).toContainText('1 assigned');

        await openSummary(page);
        await expect(assignedTable).toBeVisible();
        await expect(assignedTable.locator('thead th').first()).toHaveText('Slot');

        await line.click();

        await expect(summary).not.toHaveAttribute('open');
        await expect(assignedTable).toBeHidden();
        await expect(summary.locator('section').first()).toBeHidden();
        await expect(line).toContainText('1 assigned');
        await expect(line).toBeVisible();
    });

    test('sizes the disclosure to the 44px contract', async ({ page }) => {
        await hydrate(page, '/career/setup/deck');

        const box = await summarySection(page).locator('> summary').boundingBox();
        expect(box?.height ?? 0, 'the deck-summary disclosure is not sized to the 44px contract')
            .toBeGreaterThanOrEqual(44);
    });

    test('tracks the level as it is typed, with no write behind it', async ({ page }) => {
        await page.route('**/artwork/**', (route) => route.abort());
        await hydrate(page, '/career/setup/deck');
        await equip(page, 1, 'Agnes Joy', SSR_CARD);

        const summary = summarySection(page);
        await openSummary(page);
        const tierCell = summary.getByRole('table').first().locator('tbody > tr th, tbody > tr td').last();

        // Equipping posts the slot with no level, so before a keystroke the tier is the named absence.
        await expect(tierCell).toContainText('N/A');

        const writes: string[] = [];
        page.on('request', (request) => {
            if (request.method() === 'PUT') {
                writes.push(request.url());
            }
        });

        await page.getByRole('spinbutton', { name: 'Level for Slot 1' }).fill('40');

        // Lvl 40 is an SSR's third tier (0 breaks is 30, so 40 needs two) and it reads immediately.
        await expect(tierCell).toContainText('2 breaks');
        await expect(tierCell).toContainText('cap Lvl 40');
        expect(writes, 'the level readout must not cost a write').toHaveLength(0);

        await expect(page.getByRole('spinbutton', { name: 'Level for Slot 1' })).toHaveValue('40');
    });

    test('names a level that clears the rarity ceiling instead of hiding it', async ({ page }) => {
        await page.route('**/artwork/**', (route) => route.abort());
        await hydrate(page, '/career/setup/deck');
        await equip(page, 2, 'Digital Tracen', R_CARD);

        const summary = summarySection(page);
        await openSummary(page);
        const row = summary.getByRole('table').first().locator('tbody > tr');
        await expect(row).toContainText(R_CARD);
        await expect(row.getByText('R', { exact: true })).toBeVisible();

        await page.getByRole('spinbutton', { name: 'Level for Slot 2' }).fill('45');

        // The field reports the R ceiling (AUD-20261030-001 F-03), a keystroke can still pass it, and the
        // save refuses the value: `StoreDeckRequest` bounds the level to the same ladder this section
        // prints. The sentence appears on the typed number, before any write, which is the point of it.
        await expect(page.getByRole('spinbutton', { name: 'Level for Slot 2' })).toHaveAttribute('max', '40');
        await expect(row).toContainText('Above the ladder');
        await expect(row).toContainText('No break takes an R card past Lvl 40');
    });
});
