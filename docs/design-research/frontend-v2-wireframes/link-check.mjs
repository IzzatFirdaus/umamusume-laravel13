// Checks that every internal link and image in the set resolves.
//
// The first version resolved a path by stripping a leading "../" and comparing the
// remainder against the HTML file names in this directory, which reported all 46
// icon references as broken. The icons live ONE LEVEL UP, in
// docs/design-research/icons_svg/, and every reference is correct. So the path
// is resolved against the page's own directory instead.
import fs from 'node:fs';
import path from 'node:path';
import { fileURLToPath } from 'node:url';

const DIR = path.dirname(fileURLToPath(import.meta.url));
const files = fs.readdirSync(DIR).filter((f) => f.endsWith('.html'));

const broken = [];
let checked = 0;

for (const f of files) {
    const c = fs.readFileSync(path.join(DIR, f), 'utf8');
    for (const m of c.matchAll(/(?:href|src)="([^"]+)"/g)) {
        const ref = m[1];
        if (/^(?:https?:|mailto:|#|data:)/.test(ref)) continue;
        const target = path.resolve(DIR, ref);
        checked++;
        if (!fs.existsSync(target)) {
            broken.push(`${f} -> ${ref}`);
        }
    }
}

console.log(`checked ${checked} references across ${files.length} pages`);
console.log(broken.length ? `BROKEN:\n  ${broken.join('\n  ')}` : 'no broken references');
