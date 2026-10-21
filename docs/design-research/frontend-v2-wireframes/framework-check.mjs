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
