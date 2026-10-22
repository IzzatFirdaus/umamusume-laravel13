#!/usr/bin/env node
/**
 * ui-codemod — exact-set class rewrite, refusal first.
 *
 * Interface
 *   node tools/ui-codemod.mjs --dry-run [<path>]
 *   node tools/ui-codemod.mjs <path>
 *
 * No path: operates on resources/js/components/**\/*.vue. --dry-run prints the
 * plan and writes nothing. Without --dry-run, a file is rewritten only when
 * every candidate class attribute matched the map; otherwise nothing is written
 * for that file.
 *
 * Behaviour, quoted from the plan (A23 block):
 *
 *   1. Parse. For each .vue file, use @vue/compiler-sfc's parse(). Walk the
 *      template AST for attribute nodes named class. Both static class="..." and
 *      dynamic :class="..." — only static is a candidate; :class bindings are
 *      always refused.
 *   2. Extract. Split the static class value on whitespace.
 *   3. Refuse. If any token contains ":", or starts with "group", or is
 *      "!"-prefixed, or is any variant marker (sm, md, lg, xl, 2xl, hover,
 *      focus, focus-visible, active, disabled, aria-*, dark), refuse the entire
 *      attribute. Print REFUSED: <file>:<line> <class-string>.
 *   4. Normalise. Sort tokens lexicographically; rejoin with single space.
 *   5. Lookup. Exact-set match in ui-codemod-map.json. If no match: REFUSED.
 *   6. Rewrite. Replace the class attribute value with the single primitive
 *      name from the map. Do not change any other attribute, node, or file
 *      content.
 *   7. Atomicity. A file is "fully rewritable" only if every candidate class
 *      attribute matched. If any is refused, write nothing for that file.
 *   8. Output. Three lists at the end: APPLIED (file:line, from-class ->
 *      to-class), REFUSED (file:line, reason), SKIPPED (file, reason).
 *   9. Exit codes. 0 = completed (regardless of refusals); 1 = map malformed
 *      (not JSON, not object, value not string); 2 = any file failed to parse.
 *
 * And the expected shape, quoted:
 *
 *   "The expected auto-apply rate is therefore low by design — the tool is a
 *    refusal generator that occasionally applies, not a bulk rewriter."
 *
 * Only template class attributes are touched. <script>, <script setup> and
 * <style> blocks are never read for rewriting.
 */
import fs from 'node:fs';
import path from 'node:path';
import { fileURLToPath } from 'node:url';
import { parse } from '@vue/compiler-sfc';

const ROOT = path.resolve(path.dirname(fileURLToPath(import.meta.url)), '..');
const MAP_PATH = path.join(ROOT, 'tools', 'ui-codemod-map.json');
const DEFAULT_TARGET = path.join(ROOT, 'resources', 'js', 'components');

const VARIANT_MARKERS = new Set([
  'sm', 'md', 'lg', 'xl', '2xl',
  'hover', 'focus', 'focus-visible', 'focus-within', 'active', 'visited',
  'disabled', 'dark', 'motion-safe', 'motion-reduce', 'print', 'contrast-more',
]);

const argv = process.argv.slice(2);
const dryRun = argv.includes('--dry-run');
const targetArg = argv.find((a) => !a.startsWith('--'));
const target = targetArg ? path.resolve(process.cwd(), targetArg) : DEFAULT_TARGET;

function loadMap() {
  let raw;
  try {
    raw = fs.readFileSync(MAP_PATH, 'utf8');
  } catch (err) {
    console.error(`map unreadable: ${MAP_PATH}: ${err.message}`);
    process.exit(1);
  }

  let map;
  try {
    map = JSON.parse(raw);
  } catch (err) {
    console.error(`map is not JSON: ${MAP_PATH}: ${err.message}`);
    process.exit(1);
  }

  if (map === null || typeof map !== 'object' || Array.isArray(map)) {
    console.error('map is not an object');
    process.exit(1);
  }

  for (const [key, value] of Object.entries(map)) {
    if (key === '_schema') continue;
    if (typeof value !== 'string') {
      console.error(`map value for "${key}" is not a string`);
      process.exit(1);
    }
  }

  delete map._schema;
  return map;
}

function collectVueFiles(entry) {
  const stat = fs.statSync(entry);
  if (stat.isFile()) return entry.endsWith('.vue') ? [entry] : [];

  const out = [];
  for (const dirent of fs.readdirSync(entry, { withFileTypes: true })) {
    const full = path.join(entry, dirent.name);
    if (dirent.isDirectory()) out.push(...collectVueFiles(full));
    else if (dirent.name.endsWith('.vue')) out.push(full);
  }
  return out.sort();
}

/** Why a class string is refused, or null when it is a candidate. */
function refusalReason(classValue) {
  const tokens = classValue.split(/\s+/).filter(Boolean);
  if (tokens.length === 0) return 'empty class value';

  for (const token of tokens) {
    if (token.includes(':')) return `variant token "${token}"`;
    if (token.startsWith('group')) return `group token "${token}"`;
    if (token.startsWith('!')) return `important token "${token}"`;
    if (token.startsWith('aria-')) return `aria token "${token}"`;
    if (VARIANT_MARKERS.has(token)) return `variant marker "${token}"`;
  }

  return null;
}

function normalise(classValue) {
  return classValue.split(/\s+/).filter(Boolean).sort().join(' ');
}

/** Walk the template AST, collecting class attributes and :class bindings. */
function walk(node, visit) {
  if (!node || typeof node !== 'object') return;

  if (Array.isArray(node.props)) {
    for (const prop of node.props) visit(prop);
  }
  if (Array.isArray(node.children)) {
    for (const child of node.children) walk(child, visit);
  }
  if (Array.isArray(node.branches)) {
    for (const branch of node.branches) walk(branch, visit);
  }
  if (node.elseBranch) walk(node.elseBranch, visit);
}

const map = loadMap();
const files = collectVueFiles(target);

const applied = [];
const refused = [];
const skipped = [];
const parseFailures = [];

let rewrittenCount = 0;

for (const file of files) {
  const rel = path.relative(ROOT, file).split(path.sep).join('/');
  const source = fs.readFileSync(file, 'utf8');

  let descriptor;
  try {
    ({ descriptor } = parse(source, { filename: file }));
  } catch (err) {
    parseFailures.push(`${rel}: ${err.message}`);
    skipped.push(`${rel} — parse error`);
    continue;
  }

  if (!descriptor.template || !descriptor.template.ast) {
    skipped.push(`${rel} — no template block`);
    continue;
  }

  const candidates = [];
  let hasDynamicClass = false;

  walk(descriptor.template.ast, (prop) => {
    if (!prop || prop.type === undefined) return;

    // DirectiveNode for :class / v-bind:class
    if (prop.type === 7 && prop.name === 'bind' && prop.arg && prop.arg.content === 'class') {
      hasDynamicClass = true;
      return;
    }

    // AttributeNode named class
    if (prop.type === 6 && prop.name === 'class' && prop.value) {
      candidates.push(prop);
    }
  });

  if (hasDynamicClass) {
    skipped.push(`${rel} — :class binding`);
    continue;
  }

  if (candidates.length === 0) {
    skipped.push(`${rel} — no static class attributes`);
    continue;
  }

  const edits = [];
  let fileRefused = false;

  for (const attr of candidates) {
    const line = attr.loc.start.line;
    const raw = attr.loc.source;
    const match = /^class\s*=\s*(["'])([\s\S]*)\1$/.exec(raw);

    if (!match) {
      refused.push(`${rel}:${line} ${raw} — unparseable class attribute`);
      fileRefused = true;
      continue;
    }

    const quote = match[1];
    const value = match[2];
    const reason = refusalReason(value);

    if (reason) {
      refused.push(`${rel}:${line} ${value} — ${reason}`);
      fileRefused = true;
      continue;
    }

    const key = normalise(value);
    const primitive = map[key];

    if (!primitive) {
      refused.push(`${rel}:${line} ${value} — no exact-set match`);
      fileRefused = true;
      continue;
    }

    if (primitive === value.trim()) {
      continue; // already the primitive; no edit, nothing to report
    }

    edits.push({
      start: attr.loc.start.offset,
      end: attr.loc.end.offset,
      replacement: `class=${quote}${primitive}${quote}`,
      line,
      from: value,
      to: primitive,
    });
    applied.push(`${rel}:${line} ${value} -> ${primitive}`);
  }

  if (fileRefused || edits.length === 0) continue;

  if (!dryRun) {
    const ordered = [...edits].sort((a, b) => b.start - a.start);
    let next = source;
    for (const edit of ordered) {
      next = next.slice(0, edit.start) + edit.replacement + next.slice(edit.end);
    }
    fs.writeFileSync(file, next);
    rewrittenCount += 1;
  }
}

const report = (title, rows) => {
  console.log(`\n${title} (${rows.length})`);
  for (const row of rows) console.log(`  ${row}`);
};

console.log(`ui-codemod ${dryRun ? '(dry run)' : ''} — ${files.length} file(s) under ${path.relative(ROOT, target) || '.'}`);
report('APPLIED', applied);
report('REFUSED', refused);
report('SKIPPED', skipped);

if (parseFailures.length > 0) {
  console.log(`\nPARSE FAILURES (${parseFailures.length})`);
  for (const row of parseFailures) console.log(`  ${row}`);
  process.exit(2);
}

if (!dryRun) console.log(`\n${rewrittenCount} file(s) rewritten`);
process.exit(0);
