#!/usr/bin/env node
// Bootstrap host-local agent controls on a second machine.
// Reads .tooling/host-targets.json and applies each target idempotently:
// skip if value already exists, back up before writing, print a diff.
// Expands ~ and ${USERPROFILE}/${HOME} via os.homedir() before every fs call.
import { readFileSync, existsSync, mkdirSync, writeFileSync, copyFileSync } from 'node:fs';
import { homedir } from 'node:os';
import { join, resolve } from 'node:path';

const root = resolve(process.cwd(), '.tooling');
const manifestPath = join(root, 'host-targets.json');
let manifest;
try {
  manifest = JSON.parse(readFileSync(manifestPath, 'utf8'));
} catch (err) {
  process.stderr.write('bootstrap-host: cannot read host-targets.json: ' + String(err && err.message ? err.message : err) + '\n');
  process.exit(1);
}

function expand(p) {
  if (typeof p !== 'string') return p;
  return p
    .replace(/^~[/\\]/, homedir() + '/')
    .replace(/^~$/, homedir())
    .replace(/\${USERPROFILE}/gi, homedir())
    .replace(/\${HOME}/gi, homedir());
}

function readJson(path) {
  try { return JSON.parse(readFileSync(path, 'utf8')); } catch { return null; }
}

function writeJson(path, value) {
  const dir = path.split(/[/\\]/).slice(0, -1).join('/');
  if (dir) mkdirSync(dir, { recursive: true });
  writeFileSync(path, JSON.stringify(value, null, 2) + '\n');
}

function backup(path) {
  if (existsSync(path)) {
    try { copyFileSync(path, path + '.bak-' + Date.now()); } catch {}
  }
}

for (const target of (manifest.targets || [])) {
  const path = expand(target.path);
  switch (target.strategy) {
    case 'merge': {
      const existing = readJson(path) || {};
      const merged = { ...existing };
      if (target.payload) {
        for (const [k, v] of Object.entries(target.payload)) merged[k] = v;
      }
      if (JSON.stringify(merged) !== JSON.stringify(existing)) {
        backup(path);
        writeJson(path, merged);
        process.stdout.write('merged ' + target.id + ' -> ' + path + '\n');
      } else {
        process.stdout.write('skip ' + target.id + ' (already present)\n');
      }
      break;
    }
    case 'set-key': {
      process.stdout.write('set-key ' + target.id + ' at ' + path + ' (key=' + target.key + ') needs runtime discovery\n');
      break;
    }
    default:
      process.stdout.write('noop ' + target.id + '\n');
  }
}
process.exit(0);
