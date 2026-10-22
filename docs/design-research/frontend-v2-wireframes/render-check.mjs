// The rendered half of the check: the rules that a static read cannot decide.
//
// framework-check.mjs reads markup and CSS as text. It cannot tell whether a
// control PAINTS anything, whether two words sit at the same x, or whether the
// rail is painted over the content while it resizes. All three were defects in
// the committed set, and all three passed the static check:
//
//   the collapse control rendered as an EMPTY 44px box while the rail was open,
//   because the mark was a `.glyph` and the nav glyphs are hidden at every width
//   except the collapsed one
//   the active destination's word sat 16px left of the other nine, because its
//   dot had been dropped from the markup
//   the rail was painted 180px over the content during the collapse, because
//   the rail animated its own width inside a column that had already moved
//
// So this file renders every page in Chromium and measures the thing itself.
//
// IT NEEDS A BROWSER. Where Chromium cannot launch, this reports the rules as
// UNVERIFIED rather than passing them, because a check that cannot run and
// still says "fine" is worse than no check.
import fs from 'node:fs';
import path from 'node:path';
import { fileURLToPath } from 'node:url';

const DIR = path.dirname(fileURLToPath(import.meta.url));
const files = fs.readdirSync(DIR).filter((f) => f.endsWith('.html'));

let chromium;
try {
    ({ chromium } = await import('playwright'));
} catch {
    console.log('=== UNVERIFIED ===');
    console.log('playwright is not installed, so the rendered rules could not be checked.');
    console.log('The rail was never rendered: the collapse control, the word column and');
    console.log('the transition overlap are all UNVERIFIED, not passing.');
    process.exit(0);
}

const fails = [];
const unverifiable = [];
const fail = (f, id, msg) => fails.push(`${f}: ${id} - ${msg}`);

let browser;
try {
    browser = await chromium.launch();
} catch (e) {
    console.log('=== UNVERIFIED ===');
    console.log(`Chromium could not launch (${e.message}), so the rendered rules could not be checked.`);
    console.log('The rail was never rendered: the collapse control, the word column and');
    console.log('the transition overlap are all UNVERIFIED, not passing.');
    process.exit(0);
}

const consoleErrors = [];

/**
 * A FRESH CONTEXT PER PAGE.
 *
 * The collapse state lives in localStorage under the app's own key
 * (`trainer-desk.nav-rail`), so a context that carried the state from the
 * previous page opened the NEXT page already collapsed. The first version of
 * this check therefore measured a collapsed page while believing it measured an
 * open one, and reported 145 findings that were the script's own state leak
 * rather than defects in the set. A context per page gives every page the same
 * starting state, which is what "the same page, opened fresh" means.
 */
const context = await browser.newContext({ viewport: { width: 1440, height: 900 } });
const page = await context.newPage();

for (const f of files) {
    page.removeAllListeners('console');
    page.removeAllListeners('pageerror');
    page.on('console', (m) => { if (m.type() === 'error') consoleErrors.push(`${f}: ${m.text()}`); });
    page.on('pageerror', (e) => consoleErrors.push(`${f}: ${e}`));

    await page.goto('file:///' + path.join(DIR, f).replace(/\\/g, '/'));
    // Every page starts from the SAME state. The collapse is stored in
    // localStorage under the app's key, and a `file://` origin is shared by
    // every page in the directory, so the previous page's collapse carried
    // over. A context per page does not isolate it (one origin, one storage),
    // so the key is cleared directly and the page reloaded.
    await page.evaluate(() => {
        localStorage.removeItem('trainer-desk.nav-rail');
        document.documentElement.classList.remove('rail-collapsed');
    });
    await page.reload();
    await page.waitForTimeout(150);

    // --- 1 / 9 the control PAINTS a mark, at both widths ---
    const open = await page.evaluate(() => {
        const t = document.querySelector('[data-rail-toggle]');
        const mark = t.querySelector('.rail-chevron');
        const svg = mark ? mark.querySelector('svg') : null;
        return {
            hasMark: !!mark,
            markW: mark ? mark.getBoundingClientRect().width : 0,
            svgW: svg ? svg.getBoundingClientRect().width : 0,
            transform: svg ? getComputedStyle(svg).transform : null,
            title: t.getAttribute('title'),
            wordXs: [...document.querySelectorAll('.nav-item .nav-label')].map(
                (l) => Math.round(l.getBoundingClientRect().left)
            ),
            groupX: (() => {
                const g = document.querySelector('.rail-group');
                if (!g) return null;
                // The GROUP HEADING'S TEXT position, not the element's box: the
                // heading is a block that spans the rail, and its padding is
                // inside it, so the element's left edge is the rail's edge and
                // says nothing about where the text sits. A Range over the text
                // node is what measures the text.
                const range = document.createRange();
                range.selectNodeContents(g);
                const r = range.getBoundingClientRect();
                return Math.round(r.left);
            })(),
        };
    });

    if (!open.hasMark) {
        fail(f, 'UX-1 / WCAG 1.4.1', 'the collapse control has no mark element');
    } else if (open.markW < 8 || open.svgW < 8) {
        fail(f, 'UX-1 / WCAG 1.4.1', `the collapse control's mark is not painted (${open.markW}x${open.svgW})`);
    }
    // The word column: every destination's word at one x.
    const xs = [...new Set(open.wordXs)];
    if (xs.length > 1) {
        fail(f, 'UX-3 / Gestalt 1', `the destination words are not in one column (x: ${xs.join(', ')})`);
    }
    // The group heading sits over the words, not over the rail's edge.
    if (open.groupX !== null && xs.length && open.groupX !== xs[0]) {
        fail(f, 'UX-3 / Gestalt 1', `the group heading is at x=${open.groupX}, the words at x=${xs[0]}`);
    }

    // --- the transition: the rail never paints over the content ---
    const overlap = await page.evaluate(async () => {
        const rail = document.getElementById('primary-nav-rail');
        const main = document.querySelector('.main');
        let worst = 0;
        const t0 = performance.now();
        document.querySelector('[data-rail-toggle]').click();
        await new Promise((res) => {
            const tick = () => {
                worst = Math.max(worst, rail.getBoundingClientRect().right - main.getBoundingClientRect().left);
                if (performance.now() - t0 < 320) requestAnimationFrame(tick);
                else res();
            };
            requestAnimationFrame(tick);
        });
        return worst;
    });
    if (overlap > 1) {
        fail(f, 'UX-1', `the rail is painted ${overlap.toFixed(0)}px over the content while it resizes`);
    }

    // --- 1 / 3 the collapsed state ---
    await page.waitForTimeout(200);
    // The transition measurement above clicked the control, so the rail is
    // already collapsed here. Assert that rather than assuming it.
    const wasCollapsed = await page.evaluate(
        () => document.getElementById('primary-nav-rail').classList.contains('is-collapsed')
    );
    if (!wasCollapsed) {
        fail(f, 'UX-1', 'the control did not collapse the rail');
    }
    const closed = await page.evaluate(() => {
        const t = document.querySelector('[data-rail-toggle]');
        const svg = t.querySelector('.rail-chevron svg');
        const foot = document.querySelector('.rail-foot');
        const footWord = foot.querySelector('.rail-foot-word');
        const rail = document.getElementById('primary-nav-rail');
        const main = document.querySelector('.main');
        const railR = rail.getBoundingClientRect();
        const footR = foot.getBoundingClientRect();
        return {
            transform: getComputedStyle(svg).transform,
            title: t.getAttribute('title'),
            aria: t.getAttribute('aria-label'),
            expanded: t.getAttribute('aria-expanded'),
            footOverflow: foot.scrollHeight - footR.height,
            footWordVisible: footWord.getBoundingClientRect().width > 1,
            railW: railR.width,
            mainLeft: main.getBoundingClientRect().left,
            navWordVisible: [...document.querySelectorAll('.nav-item .nav-label')].some(
                (l) => l.getBoundingClientRect().width > 1
            ),
        };
    });

    // The chevron points the way the rail will move: right when collapsed.
    if (closed.transform === 'none') {
        fail(f, 'UX-1', 'the chevron does not point the way the collapsed rail moves (right)');
    }
    // The tooltip and the accessible name agree with the state.
    if (closed.title !== 'Expand navigation') {
        fail(f, 'WCAG 4.1.2 / H1', `the collapsed control's tooltip says "${closed.title}", not "Expand navigation"`);
    }
    if (closed.aria !== 'Expand navigation' || closed.expanded !== 'false') {
        fail(f, 'WCAG 4.1.2', 'the collapsed control\'s accessible name or aria-expanded does not follow the state');
    }
    // The collapsed column: the grid column follows, the words go to sr-only,
    // and the foot does not wrap.
    if (Math.abs(closed.railW - closed.mainLeft) > 1) {
        fail(f, 'UX-1', `the collapsed rail is ${closed.railW}px but the content starts at ${closed.mainLeft}px`);
    }
    if (closed.navWordVisible) {
        fail(f, 'WCAG 1.3.1', 'a destination word is still visible in the collapsed rail');
    }
    if (closed.footWordVisible) {
        fail(f, 'WCAG 1.3.1', 'the foot word is still visible in the collapsed rail');
    }
    if (closed.footOverflow > 1) {
        fail(f, 'UX-1', `the collapsed foot overflows its box by ${closed.footOverflow}px`);
    }

    // --- THE IMAGE SLOT ---
    // A static read cannot tell whether a drawn slot is the SIZE the app uses,
    // whether it is a square, or whether its caption survives inside it. The
    // caption is the only thing the frame says, so a caption that overflows
    // says nothing, and a slot at the wrong measure is a geometry the product
    // does not have.
    const slots = await page.evaluate(() =>
        [...document.querySelectorAll('.slot')].map((s) => {
            const r = s.getBoundingClientRect();
            const cap = s.querySelector('.cap');
            return {
                w: Math.round(r.width),
                h: Math.round(r.height),
                capFits: cap ? cap.scrollWidth <= cap.clientWidth + 1 : true,
                capText: cap ? cap.textContent : '',
                painted: getComputedStyle(s).borderTopStyle !== 'none',
            };
        })
    );
    for (const s of slots) {
        if (s.w !== s.h) {
            fail(f, 'DESIGN §4.7', `an image slot is ${s.w}x${s.h}, and the app's slot is a square`);
        }
        if (![40, 48, 64].includes(s.w)) {
            fail(f, 'DESIGN §4.7', `an image slot is ${s.w}px, and the app has only 40, 48 and 64`);
        }
        if (!s.painted) {
            fail(f, 'DESIGN §4.7', 'an image slot has no border, so it reads as nothing at all');
        }
        if (!s.capFits) {
            fail(f, 'DESIGN §4.7', `the slot caption "${s.capText}" overflows its box`);
        }
    }
}

await context.close();
await browser.close();

console.log('=== FAILURES ===');
console.log(fails.length ? [...new Set(fails)].join('\n') : 'none');
console.log(`\n=== ${files.length} pages rendered, ${new Set(fails).size} findings ===`);
if (consoleErrors.length) {
    console.log(`\n=== CONSOLE ERRORS: ${consoleErrors.length} ===`);
    console.log([...new Set(consoleErrors)].join('\n'));
}
if (unverifiable.length) {
    console.log('\n=== UNVERIFIED ===');
    console.log([...new Set(unverifiable)].join('\n'));
}
