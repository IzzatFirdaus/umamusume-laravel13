#!/usr/bin/env node
// Shared denial logging helper for Trainer Desk agent controls.
// Appends one JSONL line to .tooling/logs/denials.jsonl with denial telemetry.
// Never throws; write errors are swallowed so the deny path (exit 2) always fires.
import { appendFileSync, mkdirSync } from 'node:fs';
import { fileURLToPath } from 'node:url';
import { join } from 'node:path';

const REPO_ROOT = join(fileURLToPath(new URL('.', import.meta.url)), '..');
const LOG_DIR = join(REPO_ROOT, '.tooling', 'logs');
const LOG_FILE = join(LOG_DIR, 'denials.jsonl');

/**
 * Append a denial record to the shared log.
 * @param {Object} params
 * @param {string} params.runtime - adapter identity ("hermes"|"claude"|"qoder"|"codex")
 * @param {string} params.tool - tool name from the payload
 * @param {string} params.command - extracted command string
 * @param {string} params.rule - matched rule id from policy.mjs
 * @param {string} params.replacement - replacement tool the deny message names
 * @param {string} [params.cwd] - process.cwd() at hook invocation (defaults to process.cwd())
 */
export function logDenial({ runtime, tool, command, rule, replacement, cwd = process.cwd() }) {
  const entry = {
    ts: new Date().toISOString(),
    runtime,
    tool,
    command,
    rule,
    replacement,
    cwd
  };
  try {
    mkdirSync(LOG_DIR, { recursive: true });
    appendFileSync(LOG_FILE, JSON.stringify(entry) + '\n');
  } catch {
    // Swallow write errors: the deny path must still fire (exit 2).
  }
}

// Helper to get the project root for external callers
export function getRepoRoot() {
  return REPO_ROOT;
}