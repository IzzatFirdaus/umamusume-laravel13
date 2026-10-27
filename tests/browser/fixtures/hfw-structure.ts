import { expect, type Page } from '@playwright/test';
import { saveShot } from './wizard-screenshots';

/*
 * Structural parity between a rendered app route and its HFW wireframe, measured from the live DOM
 * rather than read off the source (the ST-4 visual-parity instruction; the close-out that preceded it
 * reviewed code and never opened the page).
 *
 * The two sides share no stylesheet. The wireframe carries the v2 design corpus' own class names
 * (`docs/design-research/frontend-v2-wireframes/*.html`); the app carries Tailwind utilities plus the
 * wireframe names it has ported into `resources/css/app.css` (`.card`, `.tile`, `.note`, `.pill*`,
 * `.srow`, `.slot*`, `.step`, `.stepper`, `.step-legend`, `.tag`, `.warn`). So a marker is resolved in
 * one vocabulary both sides can carry:
 *
 *   1. `data-region="<name>"` wins where present (the app adds these where it has no ported class);
 *   2. otherwise the first exact class token the element carries from the marker list.
 *
 * `regions` is therefore the ordered list of structural markers inside the captured subtree. It is what
 * makes the comparison structural instead of pixel-perfect: a card count, a skeleton order and a grid's
 * track count survive a font metric, a colour token or a padding change, and a missing block does not.
 *
 * Two vocabulary decisions worth stating, because they are deliberate and not oversights:
 *
 *  - The wireframe's `.topbar` is NOT a marker. The banner is `AppLayout`'s (`header[role="banner"]`)
 *    and the instruction forbids editing that shell; the HFW's topbar sits inside its `main` while the
 *    app's does not, so comparing it would fail for a reason no wizard page can fix. The wizard's own
 *    head band is `.phead`, and that is compared.
 *  - `.g-2` / `.g-3` / `.g-4` / `.g-side` are NOT separate markers; the wireframe puts them ON the same
 *    element as `.grid` (`<div class="grid g-2">`) and the app renders the same element as
 *    `grid grid-cols-1 min-[Npx]:grid-cols-2`. Both sides therefore resolve to `grid`, and the column
 *    shape is compared through `gridSignature` below rather than through the class name.
 */

export type Structure = {
    tag: string;
    /** The captured element's own class tokens. */
    cls: string[];
    /** Ordered structural markers inside the subtree, resolved per the docblock above. */
    regions: string[];
    /** The computed `grid-template-columns` of the first grid in the subtree, else `flex` / `block`. */
    grid: string;
    rect: { w: number; h: number };
};

/** The wizard shell: the head band every step renders through `PageHeader` + `SetupLayout`. */
export const SHELL_MARKERS = [
    'phead',
    'hrow-actions',
    'hrow-position',
    'hrow',
    'eyebrow',
    'lede',
    'hr',
    'canvas',
    'stepper',
    'step-legend',
    'step',
];

/** The step bodies: cards, tiles, tables, filters, pills. */
export const PAGE_MARKERS = [
    'filters',
    'pager',
    'warn',
    'grid',
    'card',
    'tile',
    'note',
    'field',
    'srow',
    'slot',
    'track',
    'type-chip',
    'pill-positive',
    'pill-caution',
    'pill-neutral',
    'pill-outline',
    'pill',
    'tag',
];

export const MARKERS = [...SHELL_MARKERS, ...PAGE_MARKERS];

export type CaptureOptions = {
    /** Restrict the marker vocabulary, e.g. `SHELL_MARKERS` for the shell-only case. */
    markers?: string[];
    /** Screenshot slug; a shot is written only when `WIZARD_SCREENSHOTS=1` (see `wizard-screenshots.ts`). */
    slug?: string;
    side?: 'app' | 'hfw';
};

export async function capture(page: Page, selector: string, opts: CaptureOptions = {}): Promise<Structure> {
    const markers = opts.markers ?? MARKERS;

    const structure = await page.evaluate(
        ({ sel, markerList }) => {
            const root = document.querySelector(sel);

            if (root === null) {
                throw new Error(`capture: nothing matches ${sel}`);
            }

            const nameOf = (el: Element): string | null => {
                const named = el.getAttribute('data-region');

                if (named !== null && named !== '') {
                    return named;
                }

                return markerList.find((marker) => el.classList.contains(marker)) ?? null;
            };

            const regions: string[] = [];
            // A marker resolves once per marker name down any one branch. `SubCell.vue` styles its stat
            // cell with the ported `.card` class (`resources/js/components/SubCell.vue:37`), so a card
            // that carries a facts grid would otherwise register one card per cell and the wireframe's
            // single `.card` would have nothing to line up against. Nesting a marker of the same name is
            // already forbidden by the repo's own UI lint (Card inside a Card), so suppressing the inner
            // one is the rule the design system already implies, not a special case for this page.
            const walk = (node: Element, seen: Set<string>): void => {
                for (const child of Array.from(node.children)) {
                    const name = nameOf(child);

                    if (name !== null && !seen.has(name)) {
                        regions.push(name);
                        walk(child, new Set([...seen, name]));
                    } else {
                        walk(child, seen);
                    }
                }
            };
            walk(root, new Set());

            // A grid's track count is the shape that matters, not the literal track size: the wireframe
            // and the app disagree on `minmax()` spelling and mean the same two columns.
            const style = getComputedStyle(root);
            let grid = style.display === 'grid' ? style.gridTemplateColumns : style.display;

            if (style.display !== 'grid') {
                // `grid`, or a wireframe `g-*` class, whether or not the app ported the class.
                const inner = root.querySelector('.grid, .g-2, .g-3, .g-4, .g-side');

                if (inner !== null) {
                    const innerStyle = getComputedStyle(inner);
                    grid = innerStyle.display === 'grid' ? innerStyle.gridTemplateColumns : innerStyle.display;
                }
            }

            const box = root.getBoundingClientRect();

            return {
                tag: root.tagName.toLowerCase(),
                cls: Array.from(root.classList),
                regions,
                grid,
                rect: { w: box.width, h: box.height },
            };
        },
        { sel: selector, markerList: markers },
    );

    if (process.env.WIZARD_SCREENSHOTS === '1' && opts.slug !== undefined && opts.side !== undefined) {
        await saveShot(page, opts.slug, opts.side, page.viewportSize()?.width ?? 1280);
    }

    return structure;
}

/** `repeat(2, minmax(0px, 1fr))` → `2`. Anything that is not a grid keeps its display verbatim. */
export function gridSignature(grid: string): string {
    if (grid === 'flex' || grid === 'block' || grid === 'inline-flex') {
        return grid;
    }

    const repeat = grid.match(/^repeat\(\s*(\d+)/);

    if (repeat !== null) {
        return `grid:${repeat[1]}`;
    }

    const tracks = grid
        .split(/\s+(?![^(]*\))/)
        .map((track) => track.trim())
        .filter((track) => track !== '' && track !== 'none');

    return `grid:${tracks.length}`;
}

/** The HFW markers with no in-order match in the app's list. Empty means parity. */
export function missingFrom(app: string[], hfw: string[]): string[] {
    const missing: string[] = [];
    let cursor = 0;

    for (const marker of hfw) {
        const at = app.indexOf(marker, cursor);

        if (at === -1) {
            missing.push(marker);
        } else {
            cursor = at + 1;
        }
    }

    return missing;
}

export function assertStructurallyEqual(
    app: Structure,
    hfw: Structure,
    opts: { regions?: 'exact' | 'subset'; grid?: boolean; rect?: { tol?: number } } = {},
): void {
    const mode = opts.regions ?? 'subset';
    const dump = `\n  app: ${app.regions.join(' → ')}\n  hfw: ${hfw.regions.join(' → ')}`;

    if (mode === 'exact') {
        expect(app.regions, `region markers must match exactly:${dump}`).toEqual(hfw.regions);
    } else {
        const missing = missingFrom(app.regions, hfw.regions);

        expect(missing, `HFW markers the app does not render, in order: ${missing.join(' → ')}${dump}`).toEqual([]);
    }

    if (opts.grid === true) {
        expect(
            gridSignature(app.grid),
            `grid shape differs: app=${app.grid}; hfw=${hfw.grid}`,
        ).toBe(gridSignature(hfw.grid));
    }

    if (opts.rect !== undefined) {
        const tol = opts.rect.tol ?? 0;
        const dw = Math.abs(app.rect.w - hfw.rect.w);
        const dh = Math.abs(app.rect.h - hfw.rect.h);
        const boxes = `app=${Math.round(app.rect.w)}x${Math.round(app.rect.h)} hfw=${Math.round(hfw.rect.w)}x${Math.round(hfw.rect.h)}`;

        expect(dw, `width differs by ${Math.round(dw)}px (tolerance ${tol}); ${boxes}`).toBeLessThanOrEqual(tol);
        expect(dh, `height differs by ${Math.round(dh)}px (tolerance ${tol}); ${boxes}`).toBeLessThanOrEqual(tol);
    }
}