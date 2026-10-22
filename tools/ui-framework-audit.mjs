// The framework audit, ported to app routes (plan Phase 1).
//
// Departure from the source: an absent Playwright install exits 2 with UNVERIFIED,
// not 0. A check that cannot run must never report clean.
// Target: UI_AUDIT_BASE_URL (default http://127.0.0.1:8000), UI_AUDIT_URLS (default /).
//
// The framework audit, measured in a rendered frame.
//
// Every file in `docs/framework-ui-ux/` records the same limit in its own
// words: "Chromium cannot launch under this repository's sandbox
// (`spawn EPERM`), so no verdict in this file or the files it feeds is a
// rendered observation." The heuristics file says it of the whole set; the WCAG
// file says it of contrast, target size, reflow and focus; the index says it of
// "every rule in this set that depends on a rendered frame".
//
// That limit was true of the sandbox the documents were written in and is false
// on this host: Chromium launches and a `file://` page loads without a server.
// framework-check.mjs reads markup and CSS as text; render-check.mjs measures
// the rail. This file measures the rest of the framework's own rules, the ones
// that only a painted frame can decide:
//
//   WCAG-2   1.4.3 text contrast, measured per text node, both themes
//   UX-1     44px targets, measured as the EFFECTIVE target
//   UI-1     the heading ladder: three steps, and only three
//   UI-7     one accented element per screen
//   WCAG     1.4.10 reflow at 320px, 1.4.4 text resize, 1.4.12 text spacing
//   WCAG     2.4.7 focus visible, measured by tabbing
//
// It reports UNVERIFIED rather than "fine" when it cannot run: a check that
// cannot run and still passes is worse than no check.
import fs from 'node:fs';
import path from 'node:path';
import { fileURLToPath } from 'node:url';

const BASE = process.env.UI_AUDIT_BASE_URL ?? 'http://127.0.0.1:8000';
const files = (process.env.UI_AUDIT_URLS ?? '/').split(',').map((s) => s.trim()).filter(Boolean);

let chromium;
try {
    ({ chromium } = await import('playwright'));
} catch {
    console.log('=== UNVERIFIED ===');
    console.log('playwright is not installed: no rule in this file could be measured.');
    process.exit(2);
}

const fail = [];
const add = (rule, page, msg) => fail.push(`${page}: ${rule} - ${msg}`);
const unverified = [];

/* ---- WCAG 2.x relative luminance and contrast ---- */
const CONTRAST = `
const parse = (s) => { const m = s.match(/rgba?\\(([^)]+)\\)/); if (!m) return null;
  const p = m[1].split(/[,\\s/]+/).filter(Boolean).map(Number);
  return { rgb: p.slice(0, 3), a: p.length > 3 ? p[3] : 1 }; };
const srgb = (v) => (v <= 0.04045 ? v / 12.92 : Math.pow((v + 0.055) / 1.055, 2.4));
const lum = ([r, g, b]) => 0.2126 * srgb(r / 255) + 0.7152 * srgb(g / 255) + 0.0722 * srgb(b / 255);
const bgOf = (el) => { let n = el;
  while (n && n !== document.documentElement) {
    const c = parse(getComputedStyle(n).backgroundColor);
    if (c && c.a > 0.99) return c.rgb; n = n.parentElement; }
  return [255, 255, 255]; };
`;

let browser;
try {
    browser = await chromium.launch();
} catch (e) {
    console.log('=== UNVERIFIED ===');
    console.log(`Chromium could not launch (${e.message}), so no rule in this file could be measured.`);
    process.exit(2);
}

const context = await browser.newContext({ viewport: { width: 1440, height: 900 } });
const page = await context.newPage();

/**
 * EVERY PAGE STARTS FROM THE SAME STATE. A `file://` origin shares localStorage
 * across the whole directory, so a theme left by one page would be read by the
 * next. The pre-paint script also OVERWRITES `data-theme` from storage, so the
 * key is what has to be set, not the attribute.
 */
const load = async (f, theme) => {
    await page.goto(BASE + f);
    await page.evaluate((t) => { localStorage.setItem('td-wireframe-theme', t); }, theme);
    await page.reload();
    await page.waitForTimeout(90);
    const applied = await page.evaluate(() => document.documentElement.dataset.theme);
    if (applied !== theme) unverified.push(`${f}: the ${theme} theme could not be applied (got ${applied})`);
};

for (const theme of ['light', 'dark']) {
    for (const f of files) {
        await load(f, theme);

        /* ---- WCAG-2 1.4.3: every text node against its own background ---- */
        const contrast = await page.evaluate(new Function(CONTRAST + `
            const out = [];
            const walk = document.createTreeWalker(document.body, NodeFilter.SHOW_TEXT);
            let t;
            while ((t = walk.nextNode())) {
                const txt = t.textContent.trim();
                if (!txt) continue;
                const el = t.parentElement;
                if (!el) continue;
                const cs = getComputedStyle(el);
                if (cs.display === 'none' || cs.visibility === 'hidden') continue;
                const r = el.getBoundingClientRect();
                if (r.width < 1 || r.height < 1) continue;
                if (el.closest('.sr-only') || el.closest('.skip')) continue;
                const fg = parse(cs.color);
                if (!fg) continue;
                const bg = bgOf(el);
                const flat = fg.a < 1 ? fg.rgb.map((v, i) => v * fg.a + bg[i] * (1 - fg.a)) : fg.rgb;
                const [l1, l2] = [lum(flat), lum(bg)].sort((x, y) => y - x);
                const ratio = (l1 + 0.05) / (l2 + 0.05);
                const size = parseFloat(cs.fontSize);
                const weight = +cs.fontWeight || 400;
                const large = size >= 24 || (size >= 18.66 && weight >= 700);
                const need = large ? 3 : 4.5;
                if (ratio < need) {
                    out.push({ txt: txt.slice(0, 34), ratio: +ratio.toFixed(2), need, size, weight,
                        cls: (el.className || '').toString().slice(0, 32) || el.tagName.toLowerCase() });
                }
            }
            return out;
        `));
        for (const v of contrast) {
            add('WCAG-2', `${f} [${theme}]`, `1.4.3 contrast ${v.ratio}:1 (needs ${v.need}) for "${v.txt}" ${v.size}px/${v.weight} [.${v.cls}]`);
        }

        /* ---- UX-1: the EFFECTIVE target is 44px ---- */
        const targets = await page.evaluate(new Function(`
            const vis = (el) => { const cs = getComputedStyle(el);
                if (cs.display === 'none' || cs.visibility === 'hidden' || +cs.opacity === 0) return false;
                const r = el.getBoundingClientRect(); return r.width > 0 && r.height > 0; };
            const out = [];
            document.querySelectorAll('a,button,input,select,textarea,[role="button"]').forEach((el) => {
                if (!vis(el)) return;
                if (el.closest('.anno')) return;
                const cs = getComputedStyle(el);
                if (el.tagName === 'A' && cs.display === 'inline') return;
                const r = el.getBoundingClientRect();
                // THE EFFECTIVE TARGET. A 20px radio inside a 44px label is a
                // 44px target; a control inside the field row is the field; a
                // radio in a table cell is the cell; a radio inside a card that
                // is wired to it is the card. What a pointer can hit is the box
                // that receives the event.
                const lab = el.closest('label');
                const card = el.closest('.pickcard');
                const row = el.closest('.in');
                const cell = el.closest('td, th');
                const box = lab || card || row || cell;
                const br = box ? box.getBoundingClientRect() : r;
                if (br.width < 44 || br.height < 44) {
                    out.push({ tag: el.tagName.toLowerCase(), cls: (el.className || '').toString().slice(0, 28),
                        w: +br.width.toFixed(1), h: +br.height.toFixed(1) });
                }
            });
            return out;
        `));
        for (const t of targets) {
            add('UX-1', `${f} [${theme}]`, `target ${t.w}x${t.h} under 44px (<${t.tag} .${t.cls}>)`);
        }

        /* ---- UI-1: the heading ladder ---- */
        const ladder = await page.evaluate(() => {
            const vis = (el) => {
                const cs = getComputedStyle(el);
                if (cs.display === 'none' || cs.visibility === 'hidden') return false;
                const r = el.getBoundingClientRect();
                return r.width > 0 && r.height > 0;
            };
            const sizes = new Set();
            document.querySelectorAll('h1,h2,h3,h4,h5,h6').forEach((h) => {
                if (vis(h)) sizes.add(`${h.tagName.toLowerCase()} ${parseFloat(getComputedStyle(h).fontSize)}`);
            });
            // The body level: the commonest size among paragraphs that are not
            // inside a heading or the annotation key.
            const body = new Map();
            document.querySelectorAll('p,li,td').forEach((el) => {
                if (!vis(el)) return;
                if (el.closest('h1,h2,h3,h4,h5,h6,.keybox,.anno')) return;
                const t = (el.textContent || '').trim();
                if (t.length < 20) return;
                const s = parseFloat(getComputedStyle(el).fontSize);
                body.set(s, (body.get(s) || 0) + 1);
            });
            const common = [...body.entries()].sort((a, b) => b[1] - a[1])[0];
            return { headings: [...sizes], bodySize: common ? common[0] : null };
        });
        // Three steps means the headings occupy at most TWO distinct sizes
        // (the page title and the section heading) before the body joins.
        const headingSizes = new Set(ladder.headings.map((h) => parseFloat(h.split(' ')[1])));
        headingSizes.add(ladder.bodySize);
        if (headingSizes.size > 3) {
            add('UI-1', `${f} [${theme}]`, `${headingSizes.size} heading/body steps: ${ladder.headings.join(', ')} + body ${ladder.bodySize}px`);
        }

        /* ---- UI-7: one accented element per screen ---- */
        const accent = await page.evaluate(() => {
            const vis = (el) => {
                const cs = getComputedStyle(el);
                if (cs.display === 'none' || cs.visibility === 'hidden') return false;
                const r = el.getBoundingClientRect();
                return r.width > 0 && r.height > 0;
            };
            // The accent is read from the stylesheet's own pair, so this does
            // not hardcode a colour the tokens can change.
            const probe = document.createElement('span');
            document.body.appendChild(probe);
            probe.style.color = 'var(--brand-accent-ink)';
            probe.style.backgroundColor = 'var(--brand-accent-soft)';
            const ink = getComputedStyle(probe).color;
            const soft = getComputedStyle(probe).backgroundColor;
            probe.remove();
            const hits = [];
            document.querySelectorAll('body *').forEach((el) => {
                if (!vis(el)) return;
                if (el.closest('.keybox')) return; // the annotation key documents the artifact
                const cs = getComputedStyle(el);
                if (cs.color === ink || cs.backgroundColor === ink || cs.backgroundColor === soft) {
                    const cls = (el.className || el.tagName).toString().slice(0, 28);
                    if (!hits.includes(cls)) hits.push(cls);
                }
            });
            return hits;
        });
        if (accent.length > 1) {
            add('UI-7', `${f} [${theme}]`, `${accent.length} accented elements (${accent.join(', ')}); the accent appears once, on the thing that matters`);
        }
    }
}

/* ---- WCAG 1.4.10 reflow, 1.4.4 resize, 1.4.12 spacing ---- */
for (const f of files) {
    await load(f, 'light');

    await page.setViewportSize({ width: 320, height: 780 });
    await page.waitForTimeout(180);
    const reflow = await page.evaluate(() => ({
        scrollW: document.documentElement.scrollWidth,
        clientW: document.documentElement.clientWidth,
    }));
    if (reflow.scrollW > reflow.clientW + 1) {
        add('WCAG-2', f, `1.4.10 reflow: the page scrolls sideways at 320px (${reflow.scrollW} > ${reflow.clientW})`);
    }

    await page.setViewportSize({ width: 1440, height: 900 });
    await page.addStyleTag({ content: 'html{font-size:200% !important}' });
    await page.waitForTimeout(220);
    const resize = await page.evaluate(() => ({
        scrollW: document.documentElement.scrollWidth,
        clientW: document.documentElement.clientWidth,
    }));
    if (resize.scrollW > resize.clientW + 1) {
        add('WCAG-2', f, `1.4.4 resize: the page scrolls sideways at 200% text (${resize.scrollW} > ${resize.clientW})`);
    }

    await page.goto(BASE + f);
    await page.waitForTimeout(90);
    await page.addStyleTag({
        content: '*{line-height:1.5 !important;letter-spacing:0.12em !important;word-spacing:0.16em !important}',
    });
    await page.waitForTimeout(220);
    const spacing = await page.evaluate(() => ({
        scrollW: document.documentElement.scrollWidth,
        clientW: document.documentElement.clientWidth,
    }));
    if (spacing.scrollW > spacing.clientW + 1) {
        add('WCAG-2', f, `1.4.12 spacing: the page scrolls sideways under the four text-spacing overrides (${spacing.scrollW} > ${spacing.clientW})`);
    }
}

/* ---- WCAG 2.4.7 focus visible, measured by TABBING ---- */
for (const f of files) {
    await load(f, 'light');
    const seen = [];
    for (let i = 0; i < 80; i++) {
        await page.keyboard.press('Tab');
        const info = await page.evaluate(() => {
            const el = document.activeElement;
            if (!el || el === document.body) return null;
            const cs = getComputedStyle(el);
            // :focus-visible MATCHING is not the indicator. It says the browser
            // considers this a keyboard focus; whether anything is PAINTED is a
            // separate fact, and it is the one 2.4.7 is about. An element can
            // match and still paint nothing when `outline: none` suppresses the
            // browser's own fallback ring.
            const ring = cs.outlineStyle !== 'none' && parseFloat(cs.outlineWidth) > 0;
            const shadow = cs.boxShadow !== 'none';
            const border = parseFloat(cs.borderTopWidth) > 0
                && cs.borderTopColor !== cs.borderBottomColor;
            return {
                tag: el.tagName.toLowerCase(),
                cls: (el.className || '').toString().slice(0, 28),
                ok: ring || shadow || border,
            };
        });
        if (!info) break;
        if (!info.ok) seen.push(`<${info.tag} .${info.cls}>`);
    }
    for (const s of [...new Set(seen)]) {
        add('WCAG-2', f, `2.4.7 a focused control paints no indicator: ${s}`);
    }
}

await context.close();
await browser.close();

console.log('=== FAILURES ===');
console.log(fail.length ? [...new Set(fail)].join('\n') : 'none');
console.log(`\n=== ${files.length} pages x 2 themes, ${new Set(fail).size} findings ===`);
if (unverified.length) {
    console.log('\n=== UNVERIFIED ===');
    console.log([...new Set(unverified)].join('\n'));
}
