#!/usr/bin/env node
// Bootstrap host-local agent controls on a second machine.
// Reads .tooling/host-targets.json and applies each target idempotently:
// skips when the merged result equals the current file, backs up before
// writing, and never prints secret values (only server/hook names).
// Expands ~, ${USERPROFILE}, ${HOME} and ${REPO} before every fs call.
import { readFileSync, existsSync, mkdirSync, writeFileSync, copyFileSync } from 'node:fs';
import { homedir } from 'node:os';
import { join, resolve, dirname } from 'node:path';
import { fileURLToPath } from 'node:url';

const here = dirname(fileURLToPath(import.meta.url));
const repoRoot = resolve(here, '..', '..');
const manifestPath = join(repoRoot, '.tooling', 'host-targets.json');

let manifest;
try {
  manifest = JSON.parse(readFileSync(manifestPath, 'utf8'));
} catch (err) {
  process.stderr.write('bootstrap-host: cannot read ' + manifestPath + ': ' + String(err && err.message ? err.message : err) + '\n');
  process.exit(1);
}

function expand(p) {
  if (typeof p !== 'string') return p;
  return p
    .replace(/^~[/\\]/, homedir() + '/')
    .replace(/^~$/, homedir())
    .replace(/\${USERPROFILE}/gi, homedir())
    .replace(/\${HOME}/gi, homedir())
    .replace(/\${REPO}/g, repoRoot.replace(/\\/g, '/'));
}

const isObj = (v) => v !== null && typeof v === 'object' && !Array.isArray(v);

// Objects merge recursively; arrays union by serialized equality (existing
// entries are never reordered or removed); scalar conflicts take the payload
// value. Key order of the base object is preserved so idempotency holds.
function deepMerge(base, add) {
  const out = Array.isArray(base) ? [...base] : { ...base };
  if (Array.isArray(add)) {
    for (const item of add) {
      if (!out.some((x) => JSON.stringify(x) === JSON.stringify(item))) out.push(item);
    }
    return out;
  }
  for (const [k, v] of Object.entries(add)) {
    if (!(k in out)) {
      out[k] = v;
    } else if (isObj(out[k]) && isObj(v)) {
      out[k] = deepMerge(out[k], v);
    } else if (Array.isArray(out[k]) && Array.isArray(v)) {
      out[k] = deepMerge(out[k], v);
    } else if (JSON.stringify(out[k]) !== JSON.stringify(v)) {
      out[k] = v;
    }
  }
  return out;
}

function readJson(path) {
  try {
    return JSON.parse(readFileSync(path, 'utf8'));
  } catch {
    return null;
  }
}

const backedUp = new Set();
function backup(path) {
  if (backedUp.has(path) || !existsSync(path)) return null;
  backedUp.add(path);
  const bak = path + '.bak-' + Date.now();
  try {
    copyFileSync(path, bak);
    return bak;
  } catch {
    return null;
  }
}

function writeJson(path, value) {
  mkdirSync(dirname(path), { recursive: true });
  writeFileSync(path, JSON.stringify(value, null, 2) + '\n');
}

function applyMerge(path, payload) {
  const existing = readJson(path) ?? {};
  const merged = deepMerge(existing, payload);
  if (JSON.stringify(merged) === JSON.stringify(existing)) return { changed: false };
  const bak = backup(path);
  writeJson(path, merged);
  return { changed: true, backup: bak };
}

// Copies named MCP server definitions from a source config (e.g. the Gemini
// config, authoritative per plan Step 2.1) into the target's mcpServers map.
// Server definitions already present in the target are skipped; values are
// read at runtime so API keys stay host-local and never enter the repo.
function applyCopyServers(path, sourcePath, names) {
  const source = readJson(sourcePath);
  if (!source || !isObj(source.mcpServers)) return { changed: false, note: 'source ' + sourcePath + ' has no mcpServers' };
  const target = readJson(path) ?? {};
  const have = isObj(target.mcpServers) ? target.mcpServers : {};
  const payload = { mcpServers: {} };
  const added = [];
  for (const name of names) {
    if (name in have) continue;
    if (!(name in source.mcpServers)) continue;
    payload.mcpServers[name] = source.mcpServers[name];
    added.push(name);
  }
  if (added.length === 0) return { changed: false };
  const merged = deepMerge(target, payload);
  const bak = backup(path);
  writeJson(path, merged);
  return { changed: true, backup: bak, added };
}

// Ensures a hooks entry for the matcher whose command carries the marker.
// Appends a sibling entry rather than rewriting existing commands, so host
// hooks (e.g. the ponytail task|Task hook) stay intact (plan Step 5.5).
function applyAppendHook(path, target) {
  const existing = readJson(path) ?? {};
  const hooks = isObj(existing.hooks) ? existing.hooks : {};
  const entries = Array.isArray(hooks[target.event]) ? hooks[target.event] : [];
  const present = entries.some((e) =>
    Array.isArray(e?.hooks) &&
    e.hooks.some((h) => String(h?.command ?? '').includes(target.marker))
  );
  if (present) return { changed: false };
  const entry = {
    matcher: target.matcher,
    hooks: [{ type: 'command', command: target.command, timeout: target.timeout ?? 10 }]
  };
  const payload = { hooks: { ...hooks, [target.event]: [...entries, entry] } };
  const merged = deepMerge(existing, payload);
  const bak = backup(path);
  writeJson(path, merged);
  return { changed: true, backup: bak, added: [target.event + ':' + target.matcher] };
}

let changedCount = 0;
for (const target of (manifest.targets || [])) {
  const path = expand(target.path);
  let result = { changed: false };
  if (target.strategy === 'merge') {
    result = applyMerge(path, target.payload ?? {});
  } else if (target.strategy === 'copy-servers') {
    result = applyCopyServers(path, expand(target.source), target.servers ?? []);
  } else if (target.strategy === 'append-hook') {
    result = applyAppendHook(path, { ...target, command: expand(target.command) });
  } else {
    process.stdout.write('skip ' + target.id + ' (strategy ' + target.strategy + ' not exercised in this pass; see host-targets.json note)\n');
    continue;
  }
  if (result.changed) {
    changedCount++;
    const bits = [];
    if (result.added) bits.push('added ' + result.added.join(', '));
    if (result.backup) bits.push('backup ' + result.backup);
    process.stdout.write('applied ' + target.id + ' -> ' + path + (bits.length ? ' (' + bits.join('; ') + ')' : '') + '\n');
  } else {
    process.stdout.write('skip ' + target.id + ' (unchanged' + (result.note ? '; ' + result.note : '') + ')\n');
  }
}
process.stdout.write('bootstrap-host: ' + changedCount + ' target(s) changed\n');
process.exit(0);
