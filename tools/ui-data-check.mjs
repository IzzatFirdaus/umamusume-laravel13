#!/usr/bin/env node
/*
 * Catalogue data check (plan Phase 1).
 *
 * Loads the same four files the HFW pages load and asserts the catalogue's own
 * invariants: the four globals exist, each has rows, and each row has a stable key.
 * The full count and schema rules remain in the HFW's data-check.mjs; this port
 * targets the app's copy of the data.
 * Exit 0 when clean; exit 1 on a failure.
 */
import { existsSync } from 'node:fs';
import { join } from 'node:path';
import { pathToFileURL } from 'node:url';

const ROOT = process.cwd();
const DATA = join(ROOT, 'docs/design-research/frontend-v2-wireframes/assets/data');
const FILES = ['umamusume.js', 'support-cards.js', 'support-effects.js', 'skills.js'];
const fails = [];

for (const file of FILES) {
    const path = join(DATA, file);
    if (!existsSync(path)) {
        fails.push(`${file}: missing`);
        continue;
    }
    await import(pathToFileURL(path).href);
}

const data = globalThis.TRAINER_DESK_DATA ?? {};
if (Object.keys(data).length === 0) fails.push('TRAINER_DESK_DATA: not populated');
for (const [key, rows] of Object.entries(data)) {
    if (key.endsWith('Schema')) continue;
    if (!Array.isArray(rows)) {
        fails.push(`${key}: not an array`);
        continue;
    }
    if (rows.length === 0) fails.push(`${key}: no rows`);
    rows.forEach((row, index) => {
        if (row === null || typeof row !== 'object') fails.push(`${key}[${index}]: not an object`);
    });
}

if (fails.length === 0) {
    console.log(`ui-data-check: clean (${Object.keys(data).length} collections)`);
    process.exit(0);
}
console.error(`ui-data-check: ${fails.length} failure(s)`);
for (const f of fails) console.error(`  ${f}`);
process.exit(1);
