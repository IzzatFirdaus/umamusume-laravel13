// The re-check the index asks for, run against the changes in this pass.
//
// This is the procedure from docs/framework-ui-ux/design-framework-index-2026-10-21.md,
// "The re-check, and when it is owed", applied to what changed on 2026-10-22:
//
//   1 controls added/removed/moved    -> Fitts 44px, Hick count
//   2 what is on screen at once       -> Cognitive Load, Choice Overload, Chunking
//   3 colour, tint or state           -> WCAG 1.4.1, 1.4.3, 1.4.11, Laws of UI 4 and 8
//   4 type size or weight             -> Laws of UI 5, Von Restorff
//   5 undo, and where the action is   -> Shneiderman 4, 5, 6; Tognazzini 7, 10; H3, H5
//   6 where a thing lives, its name   -> IA 1, 2, 7, 8; Mental Model; Jakob
//   7 progress, incomplete, boundary  -> Zeigarnik, Laws of UI 9 and 10, Goal-Gradient
//   8 what the user reads or remembers -> Shneiderman 8, Gerhardt-Powals 1 and 3, H6
//   9 destination, landmark, control  -> WCAG 2.4.1, 4.1.2, H4, Laws of UI 6
//  10 the words                       -> WCAG 3.3.1, 3.3.3, H9, D-79
//
// It is a CHECK against the rules, not a claim that the set is conformant: nothing here has been
// rendered, because Chromium cannot launch under this sandbox.
import fs from 'node:fs';
import path from 'node:path';
import { fileURLToPath } from 'node:url';

const DIR = path.dirname(fileURLToPath(import.meta.url));
const CSS = fs.readFileSync(path.join(DIR, 'assets', 'shell.css'), 'utf8');

/**
 * The stylesheet as a map of selector -> declaration text.
 *
 * A regex straight against the source is the wrong tool, twice over. `.glyph{display:inline-flex`
 * also matches the TAIL of `.rail.is-collapsed .glyph{display:inline-flex`, which is a rule that is
 * SUPPOSED to be there. And a comment sitting above a rule becomes part of the selector capture, so
 * the key for `.btn` came out as `/* ... *\/.btn` and nothing ever matched. The first version of
 * this check reported five false failures for exactly those two reasons.
 *
 * So: strip the comments first, then parse.
 */
const CLEAN = CSS.replace(/\/\*[\s\S]*?\*\//g, '');
const RULES = new Map();
for (const m of CLEAN.matchAll(/([^{}]+)\{([^{}]*)\}/g)) {
    for (const sel of m[1].split(',')) {
        const key = sel.trim().replace(/\s+/g, ' ');
        if (!key) continue;
        RULES.set(key, (RULES.get(key) || '') + m[2]);
    }
}

/** The declarations for a selector, or ''. */
const rule = (sel) => RULES.get(sel) || '';
/** True when a selector exists and its declarations say so. */
const has = (sel, prop) => {
    const d = rule(sel);
    return d.includes(prop);
};
/** A numeric px value from a declaration, or null. */
const px = (sel, prop) => {
    const m = rule(sel).match(new RegExp(`${prop}:\\s*(\\d+)px`));
    return m ? Number(m[1]) : null;
};

const files = fs.readdirSync(DIR).filter((f) => f.endsWith('.html'));
const WIZARD = [
    'career-scenario.html',
    'career-trainee.html',
    'career-profile.html',
    'career-target.html',
    'career-legacy.html',
    'career-deck.html',
    'career-preflight.html',
];

/**
 * THE IMAGE SLOTS, as the app has them.
 *
 * The app renders a mirrored file in a square beside a name (`ArtworkSlot.vue`,
 * `DESIGN.md` §4.7, `ADR-0021`). There are two kinds and three sizes, and both
 * are the app's own vocabulary: `card_portrait` and `support_thumb`, at
 * `size-10` (40px), `size-12` (48px) and `size-16` (64px).
 *
 * The wireframes have no mirror and no database, so each of those cells is
 * DRAWN as a dashed frame. The drawing has to be honest about three things, or
 * it misstates the product: which file belongs in the cell, how big the cell is,
 * and that absence renders NOTHING (no grey box, no placeholder glyph, and the
 * row keeps its text).
 *
 * So the expected set is written down per page, and it is checked three ways:
 * a slot that is not in the registry, a registry entry with no slot, a wrong
 * kind or a wrong size, a slot that is not `aria-hidden`, and a page that draws
 * a slot without naming the file it stands for in its own annotation key, are
 * all findings. The registry is the one place the answer lives, so a slot
 * cannot drift away from the app without the check noticing.
 *
 * A slot the app does not have is not invented here. The catalog index's
 * costume-form row and the deck picker's option list both carry a `size-10`
 * frame in the app, and neither has a counterpart on the wireframe pages that
 * draw those lists, so neither is drawn.
 */
const ARTWORK = {
    'index.html': ['card_portrait:64', 'card_portrait:40', 'card_portrait:40', 'card_portrait:40'],
    'career-trainee.html': ['card_portrait:40', 'card_portrait:40', 'card_portrait:40'],
    'career-profile.html': ['card_portrait:64', 'card_portrait:40'],
    'career-deck.html': ['support_thumb:48', 'support_thumb:48', 'support_thumb:48', 'support_thumb:48'],
    'catalog.html': [
        'card_portrait:48',
        'card_portrait:48',
        'card_portrait:48',
        'card_portrait:48',
        'card_portrait:48',
        'card_portrait:48',
    ],
    'catalog-detail.html': ['card_portrait:64'],
    'support-cards.html': [
        'support_thumb:48',
        'support_thumb:48',
        'support_thumb:48',
        'support_thumb:48',
        'support_thumb:48',
        'support_thumb:48',
        'support_thumb:48',
    ],
    'support-card-detail.html': ['support_thumb:64'],
    'veterans.html': [
        'card_portrait:48',
        'card_portrait:48',
        'card_portrait:48',
        'card_portrait:48',
        'card_portrait:48',
    ],
    'veteran-detail.html': ['card_portrait:48'],
    'run-deck.html': ['support_thumb:48', 'support_thumb:48', 'support_thumb:48', 'support_thumb:48'],
};
/** The two kinds the asset host declares (`ArtworkAssetController.php:22`). */
const ART_KINDS = ['card_portrait', 'support_thumb'];
/** The three sizes, as the app's own class names and their pixel measures. */
const ART_SIZES = { '.slot-10': 40, '.slot-12': 48, '.slot-16': 64 };

const fails = [];
const unverifiable = [];
const fail = (f, id, msg) => fails.push(`${f}: ${id} - ${msg}`);

// --- the stylesheet, checked once ---

// 1 CONTROLS. Every control at least 44px.
for (const sel of ['.btn', '.btn-sm', '.nav-item', '.step', '.rail-toggle', '.iconbtn', '.subnav-item', '.subnav-hub']) {
    const h = px(sel, 'min-height');
    if (h === null) {
        unverifiable.push(`styles: ${sel} has no min-height rule; a control target size is UNVERIFIED`);
    } else if (h < 44) {
        fails.push(`styles: UX-1 / Fitts - ${sel} min-height is ${h}px, not 44px`);
    }
}
for (const sel of ['.field input', '.field select', '.field textarea', '.in', '.input']) {
    const h = px(sel, 'min-height');
    if (h !== null && h < 44) {
        fails.push(`styles: UX-1 / Fitts - ${sel} min-height is ${h}px, not 44px`);
    }
}

// 3 COLOUR. The rail's mark and the word are never both visible.
if (!has('.glyph', 'display:none')) {
    fails.push('styles: UI-3 / Laws of UX 15 - the rail mark is not hidden at the expanded width');
}
if (!has('.rail.is-collapsed .glyph', 'display:inline-flex')) {
    fails.push('styles: UX-1 - the rail mark never appears when collapsed');
}
if (!has('.rail.is-collapsed .nav-label', 'clip:rect')) {
    fails.push('styles: WCAG 1.3.1 / NRM-1 - the word does not move to sr-only when collapsed');
}

// 4 TYPE. The collapsed rail's head stacks, so the mark and the control both fit.
if (!has('.rail.is-collapsed .rail-top', 'flex-direction:column')) {
    fails.push('styles: UX-1 - the collapsed rail head does not stack, so the mark and control collide');
}

// 9 the grid column follows the rail.
if (!has('html.rail-collapsed', '--rail-w:var(--rail-w-collapsed)')) {
    fails.push('styles: UX-1 - the grid column does not follow the collapse, so the content leaves a gap');
}

// 1 / 9 THE CONTROL'S MARK IS VISIBLE AT BOTH WIDTHS. The nav glyphs are
// display:none at every width except the collapsed one, so the control's mark
// cannot be a `.glyph`: it rendered as an empty 44px box while the rail was
// open, on all 49 pages. The control carries its own class, and that class
// paints it at both widths.
if (!has('.rail-chevron', 'display:inline-flex')) {
    fails.push('styles: UX-1 - the collapse control has no mark class of its own, so the control renders empty');
}
if (!has('.rail-chevron svg', 'transform:rotate(180deg)')) {
    fails.push('styles: UX-1 - the chevron does not point the way the open rail moves (left)');
}
if (!has('.rail.is-collapsed .rail-chevron svg', 'transform:rotate(0deg)')) {
    fails.push('styles: UX-1 - the chevron does not point the way the collapsed rail moves (right)');
}

// 1 / 9 THE GRID COLUMN IS WHAT ANIMATES. Animating the rail's own width
// leaves it 236px wide inside a 56px column for the length of the transition,
// so the rail is painted over the content (measured: 180px of overlap). The
// column animates and the rail stretches to it.
if (!has('.app', 'transition:grid-template-columns')) {
    fails.push('styles: UX-1 - the grid column does not animate, so the rail is painted over the content while it resizes');
}
if (!has('.rail', 'width:auto')) {
    fails.push('styles: UX-1 - the rail does not stretch to the grid column, so it overhangs the content');
}
if (has('.rail', 'transition:width')) {
    fails.push('styles: UX-1 - the rail animates its own width, which paints it over the content');
}

// 3 COLOUR / 1.3.1 THE COLLAPSED FOOT. The rule has to match the MARKUP:
// the anchor IS `.rail-foot`, so a rule that targets an `<a>` inside it never
// matched, and "Data sources" wrapped onto three lines in a 56px column.
if (!has('.rail.is-collapsed .rail-foot .rail-foot-word', 'clip:rect')) {
    fails.push('styles: WCAG 1.3.1 - the collapsed foot word does not move to sr-only');
}

// THE IMAGE SLOT'S GEOMETRY. The app's three sizes are the only ones the
// component's type union allows (`ArtworkSlot.vue:59`), so a fourth measure
// here would be a geometry the product does not have.
for (const [sel, want] of Object.entries(ART_SIZES)) {
    const w = px(sel, 'width');
    const h = px(sel, 'height');
    if (w === null || h === null) {
        fails.push(`styles: ${sel} has no recorded width and height, so an image slot has no geometry`);
    } else if (w !== want || h !== want) {
        fails.push(`styles: ${sel} is ${w}x${h}, and the app's ${sel.replace('.slot-', 'size-')} is ${want}x${want}`);
    }
}
if (!has('.slot', 'border:1px dashed')) {
    fails.push('styles: DESIGN §4.7 - the image slot is not a dashed frame, so it reads as content rather than as a drawing');
}

// --- each page ---

for (const f of files) {
    const c = fs.readFileSync(path.join(DIR, f), 'utf8');

    // 1 / 5 the collapse control sits with the sidebar, and it has a name
    const toggleInRail = /class="rail-top"[\s\S]{0,1200}data-rail-toggle/.test(c);
    const toggleInTopbar = /class="topbar-in"[\s\S]{0,1200}data-rail-toggle/.test(c);
    if (!toggleInRail) fail(f, 'SHR-7 / H3', 'the collapse control is not with the sidebar');
    if (toggleInTopbar) fail(f, 'SHR-7 / H3', 'the collapse control is still in the top bar');
    if (!/data-rail-toggle[\s\S]{0,400}class="sr-only"/.test(c)) {
        fail(f, 'WCAG 4.1.2 / NRM-1', 'the collapse control has no accessible name beyond the icon');
    }
    if (!/data-rail-toggle[^>]*aria-expanded/.test(c)) {
        fail(f, 'WCAG 4.1.2', 'the collapse control has no aria-expanded');
    }
    if (!/data-rail-toggle[^>]*aria-controls="primary-nav-rail"/.test(c)) {
        fail(f, 'WCAG 4.1.2', 'the collapse control does not point at the rail it resizes');
    }
    // The control's mark carries its own class, not `.glyph`: the nav glyphs are
    // hidden at every width except the collapsed one, so a `.glyph` mark left the
    // control empty while the rail was open.
    if (!/data-rail-toggle[\s\S]{0,400}class="rail-chevron"/.test(c)) {
        fail(f, 'UX-1 / WCAG 1.4.1', 'the collapse control has no visible mark of its own');
    }
    // The tooltip follows the state. A hard-coded title said "Collapse" on a
    // control whose action was "Expand".
    if (!/toggle\.setAttribute\('title'/.test(c)) {
        fail(f, 'WCAG 4.1.2 / H1', 'the collapse control tooltip does not follow the rail state');
    }
    // The collapsed foot. The anchor IS `.rail-foot`, so the word needs a class
    // the collapsed rule can target.
    if (!/class="rail-foot-word"/.test(c)) {
        fail(f, 'WCAG 1.3.1', 'the collapsed foot has no class to move its word to sr-only');
    }

    // 3 COLOUR / 1.3.1 EVERY WORD IS IN ONE COLUMN. The ten destinations are a
    // column, so the dot's job is to hold each word at the same x. The active
    // item's dot was dropped, which pulled the active word 16px left of the
    // other nine: the one destination the eye is on was the one out of line.
    const navItems = [...c.matchAll(/<a class="nav-item([^"]*)"[^>]*>([\s\S]*?)<\/a>/g)];
    for (const [, attrs, inner] of navItems) {
        if (!inner.includes('class="dot"')) {
            const active = attrs.includes('active');
            fail(
                f,
                'UX-3 / Gestalt 1 / WCAG 1.4.1',
                `the ${active ? 'active ' : ''}nav item has no dot, so its word does not line up with the other nine`
            );
        }
    }

    // 3 COLOUR. A type chip carries the mark AND the word.
    // `<img>` is a VOID element written as `<img ...>`, with no self-closing
    // slash and no `</img>`, so the check looks for the word that follows the
    // tag itself.
    for (const chip of c.match(/<span class="type-chip">[\s\S]*?<\/span>/g) || []) {
        if (!/height="18">[A-Za-z]/.test(chip)) {
            fail(f, 'WCAG 1.4.1 / UI-3', 'a type chip carries the mark without the word');
        }
    }
    if (c.includes('class="nav-item active"') && !c.includes('aria-current="page"')) {
        fail(f, 'WCAG 1.4.1 / UX-10', 'the active nav item has no aria-current');
    }
    // Two landmarks must not share a name.
    const labels = [...c.matchAll(/<(?:aside|nav)\b[^>]*aria-label="([^"]*)"/g)].map((m) => m[1]);
    if (new Set(labels).size !== labels.length) {
        fail(f, 'WCAG 1.3.1 / UX-4', `two landmarks share a name: ${labels.join(', ')}`);
    }

    // 5 UNDO.
    if (/^(training-decision|race-decision|event-decision|timeline)\.html$/.test(f) && !c.includes('Undo turn')) {
        fail(f, 'SHR-6 / Tognazzini 7', 'no undo affordance on a screen that commits a turn');
    }

    // 6 WHERE THINGS LIVE. The reference-data pages carry the sub-navigation, and
    // they must not ALSO carry a stepper that lists the same nine areas, because
    // that is the same list twice on one screen.
    //
    // The list is EXPLICIT, not a prefix match: `skills-planner` starts with
    // "skills" and is a career-flow page, not a reference-data page, so a prefix
    // match reported it wrongly.
    const REFERENCE = [
        'database.html',
        'database-races.html',
        'database-scenarios.html',
        'database-events.html',
        'database-shop-items.html',
        'database-sparks.html',
        'database-sources.html',
        'catalog.html',
        'catalog-detail.html',
        'support-cards.html',
        'support-card-detail.html',
        'skills.html',
        'skill-detail.html',
    ];
    if (REFERENCE.includes(f) && !c.includes('class="subnav"')) {
        fail(f, 'IA 7 / IA 5', 'a reference-data page with no way back to the hub');
    }
    if (c.includes('class="subnav"') && /aria-label="Database areas"/.test(c)) {
        fail(f, 'IA 7 / SHR-8', 'the nine areas are listed twice, by the sub-nav and by a stepper');
    }

    // 7 PROGRESS. The WIZARD's step bar carries four states, so it needs the
    // legend that names them. The "Career" steppers are a position in a FLOW, not
    // a state machine, and they are correctly labelled <nav aria-label="Career">;
    // they need no legend and are not a finding without one.
    if (/aria-label="Setup steps"/.test(c) && !c.includes('step-legend')) {
        fail(f, 'UX-3 / Zeigarnik', 'the wizard step bar has no legend, so four states read as two');
    }

    // 9 LANDMARKS.
    if (!c.includes('<main')) fail(f, 'WCAG 1.3.1', 'no main landmark');
    if (!c.includes('class="skip"')) fail(f, 'WCAG 2.4.1', 'no skip link');
    for (const id of ['primary-nav-rail']) {
        const n = (c.match(new RegExp(`id="${id}"`, 'g')) || []).length;
        if (n !== 1) fail(f, 'WCAG 4.1.1', `id "${id}" appears ${n} times`);
    }

    // 10 THE WORDS.
    if (/\bLorem ipsum\b/i.test(c)) fail(f, 'D-77', 'placeholder copy');
    // A bare "Continue" does not name its destination, and the wizard's primary
    // action is the one a Trainer must recognise without reading the page.
    for (const m of c.matchAll(/<a class="btn btn-primary"[^>]*>\s*([^<]*?)\s*<\/a>/g)) {
        if (m[1] === 'Continue') {
            fail(f, 'D-79', 'the primary action is a bare "Continue", which does not name its destination');
        }
    }

    // THE IMAGE SLOTS. The registry is the answer; the page is checked against it.
    const slotTags = [...c.matchAll(/<span class="slot[^"]*"[^>]*>/g)].map((m) => m[0]);
    const drawn = slotTags
        .map((a) => {
            const kind = (a.match(/data-art="([^"]+)"/) || [])[1] || 'none';
            // The class carries the app's own size name (`slot-12` is the app's
            // `size-12`), and the registry records the measure it stands for.
            const cls = (a.match(/class="[^"]*\bslot-(\d+)\b/) || [])[1];
            const size = cls ? ART_SIZES[`.slot-${cls}`] ?? 'none' : 'none';
            return `${kind}:${size}`;
        })
        .sort();
    const expected = (ARTWORK[f] || []).slice().sort();
    if (JSON.stringify(drawn) !== JSON.stringify(expected)) {
        fail(
            f,
            'DESIGN §4.7 / ADR-0021',
            `the image slots are [${drawn.join(', ') || 'none'}]; the registry says [${expected.join(', ') || 'none'}]`
        );
    }
    if (!(f in ARTWORK) && drawn.length) {
        fail(f, 'DESIGN §4.7', 'the page draws an image slot, and it is not in the artwork registry');
    }
    for (const a of slotTags) {
        const kind = (a.match(/data-art="([^"]+)"/) || [])[1];
        if (!kind || !ART_KINDS.includes(kind)) {
            fail(
                f,
                'DESIGN §4.7',
                `an image slot names no artwork kind the asset host declares (${kind || 'none'})`
            );
        }
        if (!/aria-hidden="true"/.test(a)) {
            fail(
                f,
                'WCAG 1.1.1 / DESIGN §4.7',
                'an image slot is not aria-hidden, so the drawing is read as content rather than as a slot'
            );
        }
        if (!/title="(card_portrait|support_thumb) · \d+px"/.test(a)) {
            fail(f, 'DESIGN §4.7', 'an image slot does not carry its kind and its measure in its title');
        }
    }
    // A page that draws a slot must say what the slot is, in its own words, and
    // must cite the section that owns the rule. The annotation is where the
    // drawing is made honest: the product paints no frame when the mirror misses.
    const keybox = (c.match(/<div class="keybox">([\s\S]*?)<\/div>/) || [])[1] || '';
    if (drawn.length) {
        for (const kind of new Set(drawn.map((d) => d.split(':')[0]))) {
            if (!keybox.includes(kind)) {
                fail(f, 'DESIGN §4.7', `the page draws a ${kind} slot, and its annotation key never names it`);
            }
        }
        if (!keybox.includes('4.7')) {
            fail(
                f,
                'DESIGN §4.7',
                'the page draws a slot, and its annotation key does not cite the section that owns the rule'
            );
        }
    }

    // 2 THE WIZARD. One place for the position and the action, above the rule.
    if (WIZARD.includes(f)) {
        const headAt = c.indexOf('class="hrow-actions"');
        const hrAt = c.indexOf('class="hr"');
        if (headAt < 0) {
            fail(f, 'SHR-4 / H3', 'no hrow-actions in the page head');
        } else if (hrAt >= 0 && headAt > hrAt) {
            fail(f, 'SHR-4', 'the action row is BELOW the rule');
        }
        if (c.includes('class="savebar"')) {
            fail(f, 'SHR-8', 'a savebar remains, so the position or the action is stated twice');
        }
        const pos = c.match(/class="hrow-position"[^>]*>([^<]*)</);
        if (!pos) {
            fail(f, 'SHR-8', 'no position in the page head');
        } else if (/Step \d+ \d/.test(pos[1]) || /^\d+ \d/.test(pos[1])) {
            fail(f, 'SHR-8', `the position reads "${pos[1].trim()}", missing "of"`);
        }
        // The wizard's position must not also live in the breadcrumb.
        const crumb = c.match(/class="crumb"[^>]*>([^<]*)</);
        if (crumb && /Step \d/.test(crumb[1])) {
            fail(f, 'SHR-8', 'the position is also in the breadcrumb, so it is stated twice');
        }
    }
}

console.log('=== FAILURES ===');
console.log(fails.length ? [...new Set(fails)].join('\n') : 'none');
console.log(`\n=== ${files.length} pages checked, ${new Set(fails).size} findings ===`);
if (unverifiable.length) {
    console.log('\n=== UNVERIFIED (cannot be checked without a render) ===');
    console.log([...new Set(unverifiable)].join('\n'));
}
