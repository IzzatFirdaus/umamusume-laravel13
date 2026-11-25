#!/usr/bin/env node
// Impeccable Stop hook shim: full-rule design deep pass.
// Never blocks the stop. A Stop hook that exits 2 re-runs the agent turn, so
// this stays fail-open by design and cannot create a continuation loop.
import { execFileSync } from 'node:child_process';
import { existsSync } from 'node:fs';
import { join } from 'node:path';
import { homedir } from 'node:os';
import { fileURLToPath } from 'node:url';

const REPO = fileURLToPath(new URL('../..', import.meta.url));
const CANDIDATES = [
  join(REPO, '.agents', 'skills', 'impeccable', 'scripts', 'bin', 'windows-x64', 'impeccable.exe'),
  join(homedir(), '.agents', 'skills', 'impeccable', 'scripts', 'impeccable'),
];
const BIN = CANDIDATES.find((p) => existsSync(p));
if (!BIN) process.exit(0);
try { execFileSync(BIN, ['hook'], { stdio: 'ignore', timeout: 30000 }); } catch {}
process.exit(0);
