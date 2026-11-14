#!/usr/bin/env node
// SessionStart hook shim. Runs the host-local headroom init hook if the
// personal binary exists; exits 0 when missing so a fresh clone never fails.
import { execFileSync } from 'node:child_process';
import { existsSync } from 'node:fs';

const BIN = 'C:/Users/exatf/.local/bin/headroom.EXE';
const ARGS = ['init', 'hook', 'ensure', '--profile', 'init-umamusume-laravel13-9a7a3dd4', '--marker', 'headroom-init-claude'];
if (!existsSync(BIN)) process.exit(0);
try { execFileSync(BIN, ARGS, { stdio: 'ignore', timeout: 60000 }); } catch {}
process.exit(0);
