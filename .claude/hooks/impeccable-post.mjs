#!/usr/bin/env node
// Impeccable post-Edit/Write hook shim. Invokes the host-local impeccable
// design check if the personal binary exists; exits 0 when missing so a fresh
// clone never fails a committed hook.
import { execFileSync } from 'node:child_process';
import { existsSync } from 'node:fs';

const BIN = 'C:/Users/exatf/.agents/skills/impeccable/scripts/impeccable';
if (!existsSync(BIN)) process.exit(0);
try { execFileSync('bash', [BIN, 'hook'], { stdio: 'ignore', timeout: 15000 }); } catch {}
process.exit(0);
