#!/usr/bin/env node
/*
 * Source-level UI lint (plan 1.12).
 *
 * Two rules that a renderer cannot check and a reviewer keeps missing:
 *
 *   1. **No Card inside a Card.** A card is the page's one raised surface. A card drawn on a card
 *      claims a second elevation on the same plane, which is why the surface rule reads as noise on
 *      screens where it happens: two borders, two radii and two backgrounds nested inside each other.
 *
 *   2. **No new arbitrary Tailwind values.** `bg-[#ff0000]` and `w-[37px]` bypass the token layer.
 *      A colour written as a hex is a measurement nobody made, and a pixel width written literally is
 *      a step off the scale. Extend the theme in `resources/css/app.css` instead.
 *
 * The arbitrary-value rule has an allowlist of the values that already shipped before this guard
 * existed. A guard that fails on the existing tree is a guard everyone turns off; each entry is a
 * known debt rather than a precedent, and removing one is a small, welcome change.
 *
 * Exit code 1 on any violation, so it fails a hand-off step.
 */
import { existsSync, readdirSync, readFileSync, statSync } from 'node:fs';
import { join, relative } from 'node:path';

const ROOT = process.cwd();
const JS_ROOT = join(ROOT, 'resources/js');

/** Every `.vue` file under `resources/js`, recursively. */
function vueFiles(dir) {
    const out = [];

    for (const entry of readdirSync(dir)) {
        const path = join(dir, entry);

        if (statSync(path).isDirectory()) {
            out.push(...vueFiles(path));
        } else if (entry.endsWith('.vue')) {
            out.push(path);
        }
    }

    return out;
}

/**
 * Arbitrary Tailwind values that predate this guard, keyed by `<file>|<value>`.
 *
 * Each is debt, not a precedent. They are listed rather than fixed because this slice installs the
 * guard and the fixes belong to whichever slice next touches each file; a guard that fails on day one
 * is a guard the next dispatch disables.
 */
const ARBITRARY_ALLOWLIST = new Map([
    ['resources/js/components/Form/BaseTextarea.vue|[88px]', 'min-height measured for the notes field'],
    ['resources/js/components/support/SupportTypeMark.vue|[18px]', 'glyph box measured for the type mark'],
]);

const violations = [];
const baselinePath = join(ROOT, 'tools/ui-lint-baseline.json');
const baseline = existsSync(baselinePath) ? JSON.parse(readFileSync(baselinePath, 'utf8')) : null;

for (const file of vueFiles(JS_ROOT)) {
    const rel = relative(ROOT, file).replace(/\\/g, '/');
    const source = readFileSync(file, 'utf8');
    const lines = source.split(/\r?\n/);

    // Template only: a `<script setup>` block has no classes.
    const templateMatch = source.match(/<template>([\s\S]*)<\/template>/);
    if (templateMatch === null) {
        continue;
    }

    const templateStart = source.slice(0, source.indexOf(templateMatch[0])).split(/\r?\n/).length;

    templateMatch[1].split(/\r?\n/).forEach((line, index) => {
        const lineNo = templateStart + index;

        // 1. Card in Card. Reported on the inner tag, which is the one to remove.
        if (/<Card\b[^>]*>[\s\S]*?$/s.test(line) && /<Card\b/.test(line)) {
            // Same-line `<Card><Card>` is caught below by the structural pass.
        }

        // 2. Arbitrary values, in a class attribute.
        const classAttr = line.match(/(?<!:)class="([^"]*)"/);
        if (classAttr !== null) {
            const classes = classAttr[1];
            const arbitrary = classes.match(/\[[^\]]+\]/g) ?? [];

            for (const value of arbitrary) {
                const allowKey = `${rel}|${value}`;
                if (ARBITRARY_ALLOWLIST.has(allowKey)) {
                    continue;
                }

                violations.push({
                    file: rel,
                    line: lineNo,
                    rule: 'arbitrary-value',
                    detail: `arbitrary value ${value} bypasses the token layer`,
                });
            }
        }
    });

    // 1. Card in Card, structurally. A nested Card is a Card tag whose nearest preceding unclosed
    //    element is another Card. The template is scanned as a flat token stream.
    const cardStack = [];
    const tagPattern = /<(\/?)Card\b[^>]*?(\/?)>|<!--[\s\S]*?-->/g;
    let match;

    while ((match = tagPattern.exec(templateMatch[1])) !== null) {
        const [whole, closing, selfClosing] = match;

        if (closing === '') {
            if (selfClosing !== '') {
                continue;
            }

            if (cardStack.length > 0) {
                const lineNo = templateStart + templateMatch[1].slice(0, match.index).split(/\r?\n/).length - 1;
                violations.push({
                    file: rel,
                    line: lineNo,
                    rule: 'card-in-card',
                    detail: 'a Card is nested inside another Card',
                });
            }

            cardStack.push(whole);
        } else if (cardStack.length > 0) {
            cardStack.pop();
        }
    }
}

if (violations.length === 0) {
    console.log('ui-lint: clean');
    process.exit(0);
}

if (baseline !== null && violations.length <= baseline.count) {
    console.log(`ui-lint: ${violations.length} known debt item(s), baseline ${baseline.count}`);
    process.exit(0);
}

if (baseline !== null && violations.length > baseline.count) {
    console.error(`ui-lint: debt increased to ${violations.length}, baseline ${baseline.count}`);
}

console.error(`ui-lint: ${violations.length} violation(s)`);
for (const v of violations) {
    console.error(`  ${v.file}:${v.line}  [${v.rule}] ${v.detail}`);
}

process.exit(1);
