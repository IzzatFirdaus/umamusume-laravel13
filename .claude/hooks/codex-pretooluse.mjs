#!/usr/bin/env node
// Codex PreToolUse hook adapter for Trainer Desk.
// Reads dual-shape payloads: { tool, arguments: { command } } or
// { event: { tool, arguments: { command } } }. Denies shell commands that
// duplicate a dedicated tool; blocks via exit code 2.
import { readFileSync } from 'node:fs';
import { classify } from '../tooling/policy.mjs';

let input = '';
try { input = readFileSync(0, 'utf8'); } catch {}
let p = {};
try { p = JSON.parse(input); } catch (err) {
  process.stderr.write('codex-pretooluse: failed to parse hook payload: ' + String(err && err.message ? err.message : err) + '\n');
  process.exit(2);
}

const event = p?.event ?? {};
const tool = p?.tool ?? event?.tool ?? '';
const cmd = p?.arguments?.command ?? event?.arguments?.command ?? '';
const result = classify(tool, cmd);

if (!result || !result.deny) {
  process.exit(0);
}

const first = String(cmd).split(/\s+/)[0] ?? cmd;
const message = (result.message || 'Blocked command.').replace('{cmd}', first);
process.stderr.write(message + '\n');
process.exit(2);
