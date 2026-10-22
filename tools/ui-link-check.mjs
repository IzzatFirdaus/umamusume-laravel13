#!/usr/bin/env node
/*
 * Link-shape check for the Vue tree (plan Phase 1).
 *
 * Checks literal internal hrefs a source read can resolve: empty, javascript:, and
 * protocol-relative targets. Route names passed through `route()` are the server's
 * contract and are not re-parsed here.
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
    const lines = readFileSync(file, 'utf8').split(/\r?\n/);
    lines.forEach((line, index) => {
        for (const href of line.matchAll(/\bhref="([^"]*)"/g)) {
            const value = href[1];
            if (value === '' || value === '#') findings.push(`${rel}:${index + 1} [empty-link] href="${value}"`);
            if (/^javascript:/i.test(value)) findings.push(`${rel}:${index + 1} [javascript-link] ${value}`);
            if (/^\/\//.test(value)) findings.push(`${rel}:${index + 1} [protocol-relative] ${value}`);
        }
    });
}

if (findings.length === 0) {
    console.log('ui-link-check: clean');
    process.exit(0);
}
console.error(`ui-link-check: ${findings.length} finding(s)`);
for (const f of findings) console.error(`  ${f}`);
process.exit(process.argv.includes('--strict') ? 1 : 0);
