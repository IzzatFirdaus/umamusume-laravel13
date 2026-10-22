#!/usr/bin/env node
/*
 * Source-level UI framework check (plan Phase 1, rule set A5).
 *
 * Eleven rules, one function each. This check is ADDITIONAL, never a replacement:
 * `DesignTokensTest` owns token resolution and contrast, `UiHygieneTest` owns copy and
 * identifiers, and the axe pass in the browser specs owns rendered accessibility. A
 * source-level check cannot see a rendered frame and may not be cited for anything those
 * three own.
 *
 * Exit 0 when clean or reporting debt; exit 1 on a violation under --strict. Rule 8 is disabled until
 * `tools/ui-lint-baseline.json` exists; it then fails only when the debt COUNT grows.
 */
import { existsSync, readdirSync, readFileSync, statSync } from 'node:fs';
import { join, relative } from 'node:path';

const ROOT = process.cwd();
const APP_CSS = join(ROOT, 'resources/css/app.css');
const JS_ROOT = join(ROOT, 'resources/js');
const BASELINE = join(ROOT, 'tools/ui-lint-baseline.json');
const CSS = readFileSync(APP_CSS, 'utf8');

const findings = [];
const add = (rule, file, line, detail) => findings.push({ rule, file, line, detail });

function walk(dir, extension) {
    const out = [];
    for (const entry of readdirSync(dir)) {
        const path = join(dir, entry);
        if (statSync(path).isDirectory()) out.push(...walk(path, extension));
        else if (entry.endsWith(extension)) out.push(path);
    }
    return out;
}

const lineAt = (source, index) => source.slice(0, index).split(/\r?\n/).length;

/** `@theme static { ... }` spans, by brace matching. */
function themeSpans(source) {
    const spans = [];
    const open = /@theme\s+static\s*\{/g;
    let match;
    while ((match = open.exec(source)) !== null) {
        let depth = 1;
        let i = open.lastIndex;
        while (i < source.length && depth > 0) {
            if (source[i] === '{') depth++;
            if (source[i] === '}') depth--;
            i++;
        }
        spans.push([match.index, i]);
    }
    return spans;
}

const stripComments = (source) => source.replace(/\/\*[\s\S]*?\*\//g, (m) => ' '.repeat(m.length));
const css = stripComments(CSS);

/** Token blocks: `@theme static` plus the dark override that redeclares its names. */
function tokenSpans(source) {
    const spans = themeSpans(source);
    const open = /html\[data-theme=['"]dark['"]\]\s*\{/g;
    let match;
    while ((match = open.exec(source)) !== null) {
        let depth = 1;
        let i = open.lastIndex;
        while (i < source.length && depth > 0) {
            if (source[i] === '{') depth++;
            if (source[i] === '}') depth--;
            i++;
        }
        spans.push([match.index, i]);
    }
    return spans;
}

const SPANS = tokenSpans(css);
const inTheme = (index) => SPANS.some(([start, end]) => index >= start && index < end);
const cssLines = css.split(/\r?\n/);
const appRel = relative(ROOT, APP_CSS).replace(/\\/g, '/');

// Rule 1: no literal colour outside @theme static.
for (const m of css.matchAll(/(#[0-9a-fA-F]{3,8}\b|\brgba?\(|\bhsla?\()/g)) {
    if (inTheme(m.index)) continue;
    add('literal-colour', appRel, lineAt(css, m.index), `${m[1]} sits outside @theme static`);
}

// Rule 2: no literal px/rem length outside @theme static.
for (const m of css.matchAll(/\b\d+(?:\.\d+)?(?:px|rem)\b/g)) {
    if (inTheme(m.index)) continue;
    add('literal-length', appRel, lineAt(css, m.index), `${m[0]} sits outside @theme static`);
}

// Rule 3: border-radius outside 6/10/14/16/999, plus 50%.
for (const m of css.matchAll(/border-radius\s*:\s*([^;}]+)/g)) {
    if (inTheme(m.index)) continue;
    const value = m[1].trim();
    const allowed = /^(?:0|50%|6px|10px|14px|16px|999px|var\(--radius-(?:sm|md|lg|xl|pill)\))(?:\s+(?:0|50%|6px|10px|14px|16px|999px|var\(--radius-(?:sm|md|lg|xl|pill)\)))*$/;
    if (!allowed.test(value)) add('radius-scale', appRel, lineAt(css, m.index), `border-radius:${value}`);
}

// Rule 4: no box-shadow anywhere.
for (const m of css.matchAll(/box-shadow\s*:/g)) {
    add('box-shadow', appRel, lineAt(css, m.index), 'box-shadow is a house invention');
}

// Rule 5: transitions only 150ms ease-out, and never on layout properties.
for (const m of css.matchAll(/transition(?:-[a-z]+)?\s*:\s*([^;}]+)/g)) {
    const value = m[1].trim();
    const layout = /(?:^|[\s,])(?:width|height|inline-size|block-size|grid-template|grid-auto|margin|padding|top|right|bottom|left)(?:\s|,|$)/;
    if (/grid-template|width|height/i.test(value) || layout.test(value)) {
        add('transition-layout', appRel, lineAt(css, m.index), `layout transition: ${value}`);
    } else if (!/150ms/.test(value) || !/ease-out/.test(value)) {
        add('transition-motion', appRel, lineAt(css, m.index), `outside 150ms ease-out: ${value}`);
    }
}

// Rule 6: outline no wider than 2px, offset no wider than 2px.
for (const m of css.matchAll(/outline(?:-width|-offset)?\s*:\s*([^;}]+)/g)) {
    const value = m[1].trim();
    const px = value.match(/(\d+(?:\.\d+)?)px/);
    if (px && Number(px[1]) > 2) add('outline-width', appRel, lineAt(css, m.index), value);
}

// Rule 7: every colour declaration resolves through var(--color-*).
for (const m of css.matchAll(/\b(?:color|background|background-color|border-color)\s*:\s*([^;}]+)/g)) {
    const value = m[1].trim();
    if (/^(?:transparent|currentColor|inherit|none|0)$/.test(value)) continue;
    if (value.includes('var(--color-') || value.includes('var(--shadow-')) continue;
    if (inTheme(m.index)) continue;
    add('colour-var', appRel, lineAt(css, m.index), `colour does not resolve through var(--color-*): ${value}`);
}

// Rules 8, 9, 10, 11: source-level template checks.
const ALLOWLIST = existsSync(BASELINE)
    ? new Set((JSON.parse(readFileSync(BASELINE, 'utf8')).allowlist ?? []))
    : new Set();
const baseline = existsSync(BASELINE) ? JSON.parse(readFileSync(BASELINE, 'utf8')) : null;
let arbitraryCount = 0;

for (const file of walk(JS_ROOT, '.vue')) {
    const rel = relative(ROOT, file).replace(/\\/g, '/');
    const source = readFileSync(file, 'utf8');
    const template = source.match(/<template>([\s\S]*)<\/template>/);
    if (template === null) continue;
    const startLine = source.slice(0, source.indexOf(template[0])).split(/\r?\n/).length;

    template[1].split(/\r?\n/).forEach((line, index) => {
        const lineNo = startLine + index;
        const classAttr = line.match(/(?<!:)class="([^"]*)"/);
        if (classAttr !== null) {
            const classes = classAttr[1];
            for (const colour of classes.match(/\b(?:bg|text|border)-\[#[0-9a-fA-F]{3,8}\]/g) ?? []) {
                add('literal-colour', rel, lineNo, `${colour} is a literal colour in a class attribute`);
            }
            for (const value of classes.match(/\[[^\]]+\]/g) ?? []) {
                const key = `${rel}|${value}`;
                if (!ALLOWLIST.has(key)) {
                    arbitraryCount++;
                    add('arbitrary-value', rel, lineNo, `${value} bypasses the token layer`);
                }
            }
            if (/\bdark:/.test(classes)) add('dark-utility', rel, lineNo, 'dark: utility');
        }
    });

    const cardPattern = /<(\/?)Card\b[^>]*?(\/?)>/g;
    const stack = [];
    let match;
    while ((match = cardPattern.exec(template[1])) !== null) {
        const [whole, closing, selfClosing] = match;
        if (closing === '') {
            if (selfClosing !== '') continue;
            if (stack.length > 0) add('card-in-card', rel, startLine + template[1].slice(0, match.index).split(/\r?\n/).length - 1, 'a Card is nested inside another Card');
            stack.push(whole);
        } else if (stack.length > 0) stack.pop();
    }
}

// Rule 8 anti-regression: only when a baseline exists.
if (baseline === null) {
    console.log('ui-framework-check: rule 8 disabled (no tools/ui-lint-baseline.json yet)');
} else if (arbitraryCount > baseline.count) {
    add('debt-increase', 'resources/js', 0, `arbitrary-value count ${arbitraryCount} exceeds baseline ${baseline.count}`);
}

// Rule 11: inside the primitive layer (after the `@primitives` marker), every class
// selector must carry a docstring within three lines above it. Before the marker the file is
// the app's legacy utility layer, not the primitive layer, so it is out of scope here.
const primitiveMarker = css.indexOf('@primitives');
if (primitiveMarker !== -1) {
    const selectorPattern = /(^|\n)([^\n{}]+)\{/g;
    selectorPattern.lastIndex = primitiveMarker;
    let selector;
    while ((selector = selectorPattern.exec(css)) !== null) {
        const raw = selector[2].trim();
        if (!raw.startsWith('.')) continue;
        const line = lineAt(css, selector.index + selector[1].length);
        const before = cssLines.slice(Math.max(0, line - 4), line - 1).join('\n');
        if (!/\*/.test(before)) add('primitive-docstring', appRel, line, `${raw} has no docstring`);
    }
}

if (findings.length === 0) {
    console.log('ui-framework-check: clean');
    process.exit(0);
}

console.error(`ui-framework-check: ${findings.length} finding(s)`);
for (const f of findings) console.error(`  ${f.file}:${f.line}  [${f.rule}] ${f.detail}`);
process.exit(process.argv.includes('--strict') ? 1 : 0);
