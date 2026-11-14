#!/usr/bin/env node
// Impeccable Stop hook shim. Invokes the host-local impeccable design deep
// pass if the personal binary exists; exits 0 when missing.
import { execFileSync } from 'node:child_process';
import { existsSync } from 'node:fs';

const BIN = 'C:/Users/exatf/.agents/skills/impeccable/scripts/impeccable';
if (!existsSync(BIN)) process.exit(0);
try { execFileSync('bash', [BIN, 'hook'], { stdio: 'ignore', timeout: 30000 }); } catch {}
process.exit(0);
