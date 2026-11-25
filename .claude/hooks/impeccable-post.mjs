#!/usr/bin/env node
// Impeccable post-Edit/Write hook shim.
// Runs the design check only for frontend files: the check costs ~3.7s, and
// running it on every PHP/config edit was pure overhead. Exits 0 when the
// binary or the file is irrelevant, so a fresh clone never fails a hook.
import { execFileSync } from 'node:child_process';
import { existsSync, readFileSync } from 'node:fs';
import { join, extname } from 'node:path';
import { homedir } from 'node:os';
import { fileURLToPath } from 'node:url';

const REPO = fileURLToPath(new URL('../..', import.meta.url));
const CANDIDATES = [
  join(REPO, '.agents', 'skills', 'impeccable', 'scripts', 'bin', 'windows-x64', 'impeccable.exe'),
  join(homedir(), '.agents', 'skills', 'impeccable', 'scripts', 'impeccable'),
];
const BIN = CANDIDATES.find((p) => existsSync(p));
if (!BIN) process.exit(0);

// Frontend surfaces only. Blade is included because it renders the UI.
const UI_EXT = new Set(['.vue', '.tsx', '.jsx', '.css', '.scss', '.sass', '.less', '.html', '.blade.php']);

let input = '';
try { input = readFileSync(0, 'utf8'); } catch {}
let p = {};
try { p = JSON.parse(input); } catch { process.exit(0); }

const ti = p?.tool_input ?? p?.toolArgs ?? {};
const file = ti.file_path ?? ti.filePath ?? ti.path ?? ti.file ?? '';
if (typeof file !== 'string' || !file) process.exit(0);
const lower = file.toLowerCase();
const isUi = lower.endsWith('.blade.php') || UI_EXT.has(extname(lower));
if (!isUi) process.exit(0);

try { execFileSync(BIN, ['hook'], { stdio: 'ignore', timeout: 15000 }); } catch {}
process.exit(0);
