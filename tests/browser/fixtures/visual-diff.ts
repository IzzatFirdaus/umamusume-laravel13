import { type Page } from '@playwright/test';

/*
 * Computed-style and bounding-rect diff between the rendered app and its HFW wireframe.
 *
 * The text diff (`hfw-structure.ts`, `missingFrom`) compares which structural markers appear and in
 * what order. It cannot see that both sides draw a `.pill` and one is 11px while the other is 12px, or
 * that a card's border is `rgb(0,0,0)` on one side and `rgb(228,228,231)` on the other. Those are the
 * divergences a Trainer sees first and a marker count reports as clean.
 *
 * The two sides share no stylesheet, so nothing is compared through a class name. Every element is
 * identified by a path resolved inside its own document, and the path is built from the same rule on
 * both sides:
 *
 *   1. an element carrying `data-region` is named by it alone (`[data-region="card"]`);
 *   2. otherwise the element is named `tag.firstClass:nth-child(n)`, where `n` is its 1-based position
 *      among its parent's children.
 *
 * Rule 2 is positional on purpose. A parity page is compared against a wireframe that is supposed to
 * draw the same blocks in the same order, so an inserted element on one side has to surface as
 * `excess` / `missing` — a class-only path would silently pair a card with the card that replaced it.
 *
 * `text` is the element's own text nodes, not `textContent`. Taking `textContent` would repeat every
 * descendant's string on each ancestor, so a single changed word would report once per level above it,
 * and a leaf-only rule (specified as the alternative) would drop the text of a `<p>` that carries a
 * `<strong>` inline. Own-text is the smallest unit that reports a changed word exactly once.
 */

export type Rect = { x: number; y: number; w: number; h: number };

export type Computed = {
    color: string;
    backgroundColor: string;
    borderColor: string;
    borderWidth: string;
    borderRadius: string;
    fontSize: string;
    fontWeight: string;
    lineHeight: string;
    padding: string;
    margin: string;
    gap: string;
    display: string;
    gridTemplateColumns: string;
};

export type ElementSnapshot = {
    /** Unique inside its own document; the primary join key between the two sides. */
    selector: string;
    /** `data-region` value when the element carries one, else `null`; the secondary join key. */
    region: string | null;
    tag: string;
    /** The element's own text nodes, trimmed; `null` when it has none. */
    text: string | null;
    rect: Rect;
    computed: Computed;
};

export type DiffReport = {
    /** In the app, not in the wireframe. */
    excess: ElementSnapshot[];
    /** In the wireframe, not in the app. */
    missing: ElementSnapshot[];
    /** The pairs compared for rect/computed drift, in app document order. */
    paired: Array<{ selector: string; app: ElementSnapshot; hfw: ElementSnapshot }>;
    rectDrift: Array<{ selector: string; app: Rect; hfw: Rect; dx: number; dy: number; dw: number; dh: number }>;
    computedDrift: Array<{ selector: string; prop: string; app: string; hfw: string }>;
};

/** Every property compared. `Computed` is the contract; this list is the iteration order. */
export const COMPUTED_PROPS: (keyof Computed)[] = [
    'color',
    'backgroundColor',
    'borderColor',
    'borderWidth',
    'borderRadius',
    'fontSize',
    'fontWeight',
    'lineHeight',
    'padding',
    'margin',
    'gap',
    'display',
    'gridTemplateColumns',
];

/**
 * Same string, compared exactly. Colours are normalised first because both sides return computed
 * `rgb()` / `rgba()` and a browser may or may not put a space after each comma.
 */
const EXACT: ReadonlySet<keyof Computed> = new Set([
    'color',
    'backgroundColor',
    'borderColor',
    'fontWeight',
    'display',
    'gridTemplateColumns',
]);

/** Single-length properties: equal within 1px. */
const PX_1: ReadonlySet<keyof Computed> = new Set(['fontSize', 'lineHeight', 'borderWidth', 'borderRadius', 'gap']);

/** Shorthand properties: equal within 2px on each of the four axes. */
const PX_2: ReadonlySet<keyof Computed> = new Set(['padding', 'margin']);

const DEFAULT_TOLERANCE = { w: 8, h: 8, x: 4, y: 4 };

/**
 * Wireframe scaffolding that is deliberately not built.
 *
 * The HFW files carry their own annotation layer — a numbered `.keybox` listing what each block is meant
 * to demonstrate, a `.step-legend` for the draw's own state dots, `.wfnav`, `.mockbar`, `.proto` and
 * `.anno`. `AGENTS.md` and this slice's brief both forbid building any of them: they are the document's
 * notes about the design, not the design. A diff that reported them as `missing` on every page would be
 * reporting the same known, intended gap eight times.
 *
 * Filtered out of the wireframe snapshot only. If the app ever draws one of these, the selector appears
 * on the app side and shows up as `excess`, which is the direction that should fail.
 */
const ANNOTATION_SELECTOR = '.keybox, .step-legend, .wfnav, .mockbar, .proto, .anno, [class*="annotation"]';

/** `rgb(0, 0, 0)` and `rgb(0,0,0)` are the same colour. */
function normaliseColour(value: string): string {
    return value.replace(/\s+/g, '');
}

/**
 * A box shorthand expanded to `[top, right, bottom, left]`.
 *
 * `getComputedStyle` returns the longhand-resolved shorthand, but the number of components still
 * follows the CSS shorthand rule (1, 2, 3 or 4 values), so both sides are compared axis by axis rather
 * than string by string: `8px` and `8px 8px` are the same padding.
 */
function axes(value: string): number[] {
    const parts = value
        .trim()
        .split(/\s+/)
        .map((part) => Number.parseFloat(part))
        .filter((part) => !Number.isNaN(part));

    switch (parts.length) {
        case 1:
            return [parts[0], parts[0], parts[0], parts[0]];
        case 2:
            return [parts[0], parts[1], parts[0], parts[1]];
        case 3:
            return [parts[0], parts[1], parts[2], parts[1]];
        case 4:
            return [parts[0], parts[1], parts[2], parts[3]];
        default:
            // `none`, `normal`, or an empty string. Nothing to measure, so the caller compares text.
            return [];
    }
}

function within(a: number, b: number, tolerance: number): boolean {
    return Math.abs(a - b) <= tolerance;
}

function sameComputed(prop: keyof Computed, app: string, hfw: string): boolean {
    if (EXACT.has(prop)) {
        return prop === 'color' || prop === 'backgroundColor' || prop === 'borderColor'
            ? normaliseColour(app) === normaliseColour(hfw)
            : app === hfw;
    }

    if (PX_1.has(prop)) {
        const a = Number.parseFloat(app);
        const b = Number.parseFloat(hfw);

        return Number.isNaN(a) || Number.isNaN(b) ? app === hfw : within(a, b, 1);
    }

    if (PX_2.has(prop)) {
        const a = axes(app);
        const b = axes(hfw);

        if (a.length === 0 || b.length === 0 || a.length !== b.length) {
            return app === hfw;
        }

        return a.every((value, index) => within(value, b[index], 2));
    }

    return app === hfw;
}

/**
 * Snapshot every descendant of `rootSelector`, in document order.
 *
 * Uses `page.evaluate` rather than `page.$$eval`: one round trip, no element handles to leak, and the
 * whole walk stays in one expression, which is what keeps the path rule above identical on both sides.
 */
export async function captureSnapshot(page: Page, rootSelector: string): Promise<ElementSnapshot[]> {
    return page.evaluate(({ root, annotations }) => {
        const host = document.querySelector(root);

        if (host === null) {
            throw new Error(`captureSnapshot: nothing matches ${root}`);
        }

        const skip = (el: Element): boolean => el.closest(annotations) !== null;

        const segment = (el: Element): string => {
            const region = el.getAttribute('data-region');

            if (region !== null && region !== '') {
                return `[data-region="${region}"]`;
            }

            const tag = el.tagName.toLowerCase();
            const cls = el.classList[0];
            const parent = el.parentElement;
            let index = 1;

            if (parent !== null) {
                for (const sibling of Array.from(parent.children)) {
                    if (sibling === el) {
                        break;
                    }

                    index += 1;
                }
            }

            return cls === undefined ? `${tag}:nth-child(${index})` : `${tag}.${cls}:nth-child(${index})`;
        };

        const pathOf = (el: Element): string => {
            const parts: string[] = [];
            let node: Element | null = el;

            while (node !== null && node !== host) {
                parts.unshift(segment(node));
                node = node.parentElement;
            }

            return parts.join(' > ');
        };

        const ownText = (el: Element): string | null => {
            const text = Array.from(el.childNodes)
                .filter((node) => node.nodeType === 3)
                .map((node) => node.textContent ?? '')
                .join(' ')
                .replace(/\s+/g, ' ')
                .trim();

            return text === '' ? null : text;
        };

        return Array.from(host.querySelectorAll('*'))
            .filter((el) => !skip(el))
            .map((el) => {
            const style = getComputedStyle(el);
            const box = el.getBoundingClientRect();

            return {
                selector: pathOf(el),
                region: el.getAttribute('data-region') ?? null,
                tag: el.tagName.toLowerCase(),
                text: ownText(el),
                rect: { x: box.x, y: box.y, w: box.width, h: box.height },
                computed: {
                    color: style.color,
                    backgroundColor: style.backgroundColor,
                    borderColor: style.borderColor,
                    borderWidth: style.borderWidth,
                    borderRadius: style.borderRadius,
                    fontSize: style.fontSize,
                    fontWeight: style.fontWeight,
                    lineHeight: style.lineHeight,
                    padding: style.padding,
                    margin: style.margin,
                    gap: style.gap,
                    display: style.display,
                    gridTemplateColumns: style.gridTemplateColumns,
                },
            };
        });
    }, { root: rootSelector, annotations: ANNOTATION_SELECTOR });
}

export function diffSnapshots(
    app: ElementSnapshot[],
    hfw: ElementSnapshot[],
    tolerance: { rect?: { w?: number; h?: number; x?: number; y?: number } } = {},
): DiffReport {
    const tol = { ...DEFAULT_TOLERANCE, ...(tolerance.rect ?? {}) };

    // Pairing is three passes, most evidence first.
    //
    // 1. Exact selector. The strongest key: same named block in the same position.
    // 2. `data-region`. The shell may wrap the stepper in a `<nav>` where the wireframe draws it bare,
    //    which shifts every positional path below the wrapper. A region name survives that shift, so
    //    pairs the element that is semantically the same thing.
    // 3. Tag + own text. Text is the page's content; where both sides print the same words in the same
    //    element kind, they are the same element even if a wrapper moved it. Only non-null text keys,
    //    and only when the key is unique on each side, so a repeated "Back" cannot pair with the wrong
    //    Back.
    //
    // Only elements that fail all three passes land in `excess` / `missing`. Rect and computed drift are
    // measured over the `paired` set: without this, a path mismatch between the two documents would
    // leave the measurable buckets empty and report "0 drift" where the truth is "nothing comparable".

    const appBySelector = new Map(app.map((entry) => [entry.selector, entry]));
    const hfwBySelector = new Map(hfw.map((entry) => [entry.selector, entry]));

    const paired: DiffReport['paired'] = [];
    const appLeft = new Map<string, ElementSnapshot>();
    const hfwLeft = new Map<string, ElementSnapshot>();

    for (const entry of app) {
        const other = hfwBySelector.get(entry.selector);

        if (other === undefined) {
            appLeft.set(entry.selector, entry);
        } else {
            paired.push({ selector: entry.selector, app: entry, hfw: other });
            hfwBySelector.delete(entry.selector);
        }
    }

    for (const [selector, entry] of hfwBySelector) {
        if (!appBySelector.has(selector)) {
            hfwLeft.set(selector, entry);
        }
    }

    // Pass 2: pair leftovers by region. Groups are ordered by document order on each side and zipped,
    // so a repeated region (six slot rows) still pairs row 1 with row 1.
    const appRegions = new Map<string, ElementSnapshot[]>();
    const hfwRegions = new Map<string, ElementSnapshot[]>();

    for (const entry of appLeft.values()) {
        if (entry.region !== null) {
            (appRegions.get(entry.region) ?? appRegions.set(entry.region, []).get(entry.region)!).push(entry);
        }
    }

    for (const entry of hfwLeft.values()) {
        if (entry.region !== null) {
            (hfwRegions.get(entry.region) ?? hfwRegions.set(entry.region, []).get(entry.region)!).push(entry);
        }
    }

    for (const [region, appGroup] of appRegions) {
        const hfwGroup = hfwRegions.get(region) ?? [];

        for (let index = 0; index < Math.min(appGroup.length, hfwGroup.length); index += 1) {
            const a = appGroup[index];
            const h = hfwGroup[index];
            paired.push({ selector: a.selector, app: a, hfw: h });
            appLeft.delete(a.selector);
            hfwLeft.delete(h.selector);
        }
    }

    // Pass 3: pair by tag + own text, unique on each side.
    const appTextKeys = new Map<string, ElementSnapshot[]>();
    const hfwTextKeys = new Map<string, ElementSnapshot[]>();

    const textKey = (entry: ElementSnapshot): string => `${entry.tag}|${(entry.text ?? '').toLowerCase()}`;

    for (const entry of appLeft.values()) {
        if (entry.text !== null) {
            (appTextKeys.get(textKey(entry)) ?? appTextKeys.set(textKey(entry), []).get(textKey(entry))!).push(entry);
        }
    }

    for (const entry of hfwLeft.values()) {
        if (entry.text !== null) {
            (hfwTextKeys.get(textKey(entry)) ?? hfwTextKeys.set(textKey(entry), []).get(textKey(entry))!).push(entry);
        }
    }

    for (const [key, appGroup] of appTextKeys) {
        const hfwGroup = hfwTextKeys.get(key) ?? [];

        if (appGroup.length !== 1 || hfwGroup.length !== 1) {
            continue;
        }

        const a = appGroup[0];
        const h = hfwGroup[0];
        paired.push({ selector: a.selector, app: a, hfw: h });
        appLeft.delete(a.selector);
        hfwLeft.delete(h.selector);
    }

    const excess = [...appLeft.values()];
    const missing = [...hfwLeft.values()];

    const rectDrift: DiffReport['rectDrift'] = [];
    const computedDrift: DiffReport['computedDrift'] = [];

    for (const { selector, app: a, hfw: h } of paired) {
        const dx = a.rect.x - h.rect.x;
        const dy = a.rect.y - h.rect.y;
        const dw = a.rect.w - h.rect.w;
        const dh = a.rect.h - h.rect.h;

        if (Math.abs(dx) > tol.x || Math.abs(dy) > tol.y || Math.abs(dw) > tol.w || Math.abs(dh) > tol.h) {
            rectDrift.push({ selector, app: a.rect, hfw: h.rect, dx, dy, dw, dh });
        }

        for (const prop of COMPUTED_PROPS) {
            if (!sameComputed(prop, a.computed[prop], h.computed[prop])) {
                computedDrift.push({ selector, prop, app: a.computed[prop], hfw: h.computed[prop] });
            }
        }
    }

    return { excess, missing, paired, rectDrift, computedDrift };
}

/**
 * The report as Markdown, for a terminal run.
 *
 * Not written into the repository: a bucket is either empty or it has an `HFW-OVERRIDE` comment in the
 * page's template, and the comment is the durable record. This string is what a phase reads while it
 * works.
 */
export function reportDiffToMarkdown(report: DiffReport): string {
    const lines: string[] = [];
    const box = (rect: Rect): string => `${Math.round(rect.w)}x${Math.round(rect.h)} @ ${Math.round(rect.x)},${Math.round(rect.y)}`;

    lines.push(`## excess (${report.excess.length})`);

    for (const entry of report.excess) {
        lines.push(`- \`${entry.selector}\` <${entry.tag}> ${box(entry.rect)}${entry.text === null ? '' : ` — "${entry.text}"`}`);
    }

    lines.push('', `## missing (${report.missing.length})`);

    for (const entry of report.missing) {
        lines.push(`- \`${entry.selector}\` <${entry.tag}> ${box(entry.rect)}${entry.text === null ? '' : ` — "${entry.text}"`}`);
    }

    lines.push('', `## rectDrift (${report.rectDrift.length})`);

    for (const entry of report.rectDrift) {
        const signed = (value: number): string => (value > 0 ? `+${value}` : `${value}`);

        lines.push(`- \`${entry.selector}\` dx=${signed(Math.round(entry.dx))} dy=${signed(Math.round(entry.dy))} dw=${signed(Math.round(entry.dw))} dh=${signed(Math.round(entry.dh))}`);
    }

    lines.push('', `## computedDrift (${report.computedDrift.length})`);

    for (const entry of report.computedDrift) {
        lines.push(`- \`${entry.selector}\` ${entry.prop}: app=${entry.app} hfw=${entry.hfw}`);
    }

    return lines.join('\n');
}
