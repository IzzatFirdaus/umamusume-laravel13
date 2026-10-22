#!/usr/bin/env node
/*
 * Render-shape check for the Vue tree (plan Phase 1).
 *
 * Source-level, not a rendered frame: it checks the markup contracts a template can
 * state without a browser. Additional, never a replacement for axe or the browser specs.
 * Exit 0 when clean or reporting debt; exit 1 on a finding under --strict.
 */
import { readdirSync, readFileSync, statSync } from 'node:fs';
import { join, relative } from 'node:path';

const ROOT = process.cwd();
const JS_ROOT = join(ROOT, 'resources/js');
const findings = [];

function walk(dir) {
    const out = [];
    for (const entry of readdirSync(dir)) {
        const path = join(dir, entry);
        if (statSync(path).isDirectory()) out.push(...walk(path));
        else if (entry.endsWith('.vue')) out.push(path);
    }
    return out;
}

for (const file of walk(JS_ROOT)) {
    const rel = relative(ROOT, file).replace(/\\/g, '/');
    const source = readFileSync(file, 'utf8');
    const template = source.match(/<template>([\s\S]*)<\/template>/);
    if (template === null) continue;
    const start = source.slice(0, source.indexOf(template[0])).split(/\r?\n/).length;
    template[1].split(/\r?\n/).forEach((line, index) => {
        const lineNo = start + index;
        for (const img of line.matchAll(/<img\b[^>]*>/g)) {
            if (!/\balt=/.test(img[0])) findings.push(`${rel}:${lineNo} [img-alt] <img> has no alt`);
        }
        for (const button of line.matchAll(/<button\b[^>]*>/g)) {
            if (!/\btype=/.test(button[0])) findings.push(`${rel}:${lineNo} [button-type] <button> has no type`);
        }
    });
}

if (findings.length === 0) {
    console.log('ui-render-check: clean');
    process.exit(0);
}
console.error(`ui-render-check: ${findings.length} finding(s)`);
for (const f of findings) console.error(`  ${f}`);
process.exit(process.argv.includes('--strict') ? 1 : 0);
