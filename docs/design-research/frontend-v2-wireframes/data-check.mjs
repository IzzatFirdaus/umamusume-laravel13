/* ============================================================================
 * TRAINER DESK v2.0.0 HFW — the mock catalogue's check.
 *
 * A dataset that no check can fail is decoration. This one loads the four
 * files the way a page loads them, and then holds them to the rules the
 * catalogue itself states:
 *
 *   1. the four files load, and each one carries its schema;
 *   2. the counts are the catalogue's counts;
 *   3. every effect a card carries joins to the dictionary;
 *   4. every growth row sums to 30, every base row to 450, 425 or 400 by the
 *      card's rarity, and every breakthrough row to 500 and 550
 *      (docs/UMAMUSUME_REFERENCE.md §1.3.5);
 *   5. no row the game does not have is in the dataset, which is the four
 *      test-fixture trainees and twelve test-fixture forms the development
 *      database carries;
 *   6. no skill carries a description, because no client string for one exists
 *      (G-SK-20), and the field is the one a surface binds to;
 *   7. every field the data carries is named in the schema, so the two cannot
 *      drift apart.
 *
 * Run: node data-check.mjs
 * ============================================================================ */

import fs from 'node:fs';
import path from 'node:path';
import { fileURLToPath } from 'node:url';

const DIR = path.dirname(fileURLToPath(import.meta.url));

await import('./assets/data/umamusume.js');
await import('./assets/data/support-cards.js');
await import('./assets/data/support-effects.js');
await import('./assets/data/skills.js');

const data = globalThis.TRAINER_DESK_DATA ?? {};
const fails = [];
const fail = (what, why) => fails.push(`${what}: ${why}`);

/** The counts the catalogue holds, and the ones the set's own pages print. */
const EXPECTED = {
    umamusume: 67,
    forms: 106,
    supportCards: 559,
    supportEffects: 35,
    skills: 1910,
};

const { umamusume, supportCards, supportEffects, skills } = data;

// 1. THE FILES AND THEIR SCHEMAS.
for (const key of [
    'umamusume',
    'supportCards',
    'supportEffects',
    'skills',
    'umamusumeSchema',
    'supportCardsSchema',
    'supportEffectsSchema',
    'skillsSchema',
]) {
    if (data[key] === undefined) {
        fail('the files', `${key} is not on window.TRAINER_DESK_DATA`);
    }
}
if (fails.length > 0) {
    console.log('=== FAILURES ===');
    fails.forEach((f) => console.log(' - ' + f));
    process.exit(1);
}

// 2. THE COUNTS.
if (umamusume.length !== EXPECTED.umamusume) {
    fail('the roster', `holds ${umamusume.length} trainees, and the catalogue holds ${EXPECTED.umamusume}`);
}
if (supportCards.length !== EXPECTED.supportCards) {
    fail('the support cards', `hold ${supportCards.length}, and the catalogue holds ${EXPECTED.supportCards}`);
}
if (supportEffects.length !== EXPECTED.supportEffects) {
    fail('the effect dictionary', `holds ${supportEffects.length}, and the catalogue holds ${EXPECTED.supportEffects}`);
}
if (skills.length !== EXPECTED.skills) {
    fail('the skills', `hold ${skills.length}, and the catalogue holds ${EXPECTED.skills}`);
}
let formCount = 0;
for (const trainee of umamusume) {
    formCount += trainee.forms.length;
}
if (formCount !== EXPECTED.forms) {
    fail('the roster', `carries ${formCount} forms, and the catalogue holds ${EXPECTED.forms}`);
}

// 3. THE JOIN.
const knownEffects = new Set(supportEffects.map((e) => e.effectId));
for (const card of supportCards) {
    for (const effect of card.effects) {
        if (!knownEffects.has(effect.effectId)) {
            fail(`card ${card.id}`, `carries effect ${effect.effectId}, and the dictionary does not`);
        }
    }
}

// 4. THE FIGURES THE CATALOGUE STATES.
const BASE_TOTALS = { 1: 400, 2: 425, 3: 450 };
for (const trainee of umamusume) {
    for (const form of trainee.forms) {
        const growth = Object.values(form.growthRate ?? {}).reduce((a, b) => a + b, 0);
        if (growth !== 30) {
            fail(`form ${form.id}`, `has a growth row summing to ${growth}, and every card's sums to 30`);
        }
        const base = Object.values(form.baseStats ?? {}).reduce((a, b) => a + b, 0);
        if (base !== BASE_TOTALS[form.rarity]) {
            fail(
                `form ${form.id}`,
                `has base stats summing to ${base}, and a rarity ${form.rarity} card sums to ${BASE_TOTALS[form.rarity]}`
            );
        }
        for (const [row, expected] of [
            ['fourStar', 500],
            ['fiveStar', 550],
        ]) {
            const total = Object.values(form.breakthrough?.[row] ?? {}).reduce((a, b) => a + b, 0);
            if (total !== expected) {
                fail(`form ${form.id}`, `has a ${row} row summing to ${total}, and every card's sums to ${expected}`);
            }
        }
    }
}

// 5. NOTHING THE GAME DOES NOT HAVE.
for (const trainee of umamusume) {
    if (/^Form Counted/.test(trainee.name)) {
        fail('the roster', `carries the test-fixture trainee "${trainee.name}"`);
    }
    for (const form of trainee.forms) {
        if (form.title === '[Sample Form]') {
            fail(`form ${form.id}`, 'is a test-fixture form');
        }
    }
}

// 6. NO SKILL CARRIES A DESCRIPTION.
for (const skill of skills) {
    if (skill.description !== null) {
        fail(`skill ${skill.id}`, 'carries a description, and no client string for one exists');
    }
}

// 7. EVERY FIELD IS NAMED IN THE SCHEMA.
const documented = (schema, fields) => {
    const named = new Set([
        ...Object.keys(schema.fields ?? {}),
        ...Object.keys(schema.formFields ?? {}),
        ...Object.keys(schema.effectFields ?? {}),
        ...Object.keys(schema.conditionFields ?? {}),
    ]);
    for (const field of fields) {
        if (!named.has(field)) {
            fail('the schema', `does not name the field "${field}"`);
        }
    }
};
const keysOf = (records) => {
    const keys = new Set();
    for (const record of records) {
        for (const key of Object.keys(record)) {
            keys.add(key);
        }
    }
    return keys;
};
documented(data.umamusumeSchema, keysOf(umamusume));
documented(data.umamusumeSchema, keysOf(umamusume.flatMap((u) => u.forms)));
documented(data.supportCardsSchema, keysOf(supportCards));
documented(data.supportCardsSchema, keysOf(supportCards.flatMap((c) => c.effects)));
documented(data.supportEffectsSchema, keysOf(supportEffects));
documented(data.skillsSchema, keysOf(skills));
documented(data.skillsSchema, keysOf(skills.flatMap((s) => s.conditions)));

if (fails.length > 0) {
    console.log('=== FAILURES ===');
    fails.forEach((f) => console.log(' - ' + f));
    process.exit(1);
}

console.log(
    `=== the catalogue is consistent: ${EXPECTED.umamusume} trainees, ${EXPECTED.forms} forms, ${EXPECTED.supportCards} support cards, ${EXPECTED.supportEffects} effects, ${EXPECTED.skills} skills ===`
);

/*
 * THE BROWSER HALF
 *
 * Everything above reads the files as modules. A wireframe page does not: it
 * loads them as classic scripts over `file://`, and a module loaded from
 * `file://` is blocked by the browser's cross-origin rules. So the load the
 * pages actually do is the one that has to be proven, in a browser, and where
 * Chromium cannot launch this reports the rule as UNVERIFIED rather than
 * passing it.
 */
let chromium;
try {
    ({ chromium } = await import('playwright'));
} catch {
    console.log('=== UNVERIFIED ===');
    console.log('playwright is not installed, so the classic-script load is UNVERIFIED, not passing.');
    process.exit(0);
}

const browser = await chromium.launch();
const page = await browser.newPage();
const probe = path.join(DIR, 'assets', 'data', '_load-probe.html');
const scripts = ['umamusume.js', 'support-cards.js', 'support-effects.js', 'skills.js']
    .map((f) => `<script src="${f}"></script>`)
    .join('\n');
fs.writeFileSync(probe, `<!DOCTYPE html><html><head><meta charset="utf-8"></head><body>\n${scripts}\n</body></html>`);

const pageErrors = [];
page.on('pageerror', (e) => pageErrors.push(String(e)));

await page.goto('file:///' + probe.replace(/\\/g, '/'));
const loaded = await page.evaluate(() => {
    const d = globalThis.TRAINER_DESK_DATA ?? {};
    return {
        keys: Object.keys(d).sort(),
        counts: {
            umamusume: d.umamusume?.length ?? 0,
            supportCards: d.supportCards?.length ?? 0,
            supportEffects: d.supportEffects?.length ?? 0,
            skills: d.skills?.length ?? 0,
        },
    };
});
fs.unlinkSync(probe);
await browser.close();

const expectedKeys = [
    'skills',
    'skillsSchema',
    'supportCards',
    'supportCardsSchema',
    'supportEffects',
    'supportEffectsSchema',
    'umamusume',
    'umamusumeSchema',
];
const browserFails = [];
if (pageErrors.length > 0) {
    browserFails.push(`the page raised: ${pageErrors.join('; ')}`);
}
for (const key of expectedKeys) {
    if (!loaded.keys.includes(key)) {
        browserFails.push(`a classic script under file:// did not publish "${key}"`);
    }
}
for (const [key, want] of Object.entries(loaded.counts)) {
    if (want !== EXPECTED[key]) {
        browserFails.push(`the browser loaded ${want} ${key}, and the catalogue holds ${EXPECTED[key]}`);
    }
}

if (browserFails.length > 0) {
    console.log('=== FAILURES (the browser half) ===');
    browserFails.forEach((f) => console.log(' - ' + f));
    process.exit(1);
}

console.log('=== the catalogue loads as a classic script under file:// ===');
