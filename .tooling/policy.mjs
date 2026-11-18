#!/usr/bin/env node
// Shared policy classification module for Trainer Desk agent controls.
// Loads .tooling/policy.json (same directory) and exposes classify()/isAllowed().
// Fail-closed: if the table cannot be loaded, classify() returns a deny rule so
// adapters block rather than silently allowing.
import { readFileSync } from 'node:fs';
import { fileURLToPath } from 'node:url';

const policyPath = fileURLToPath(new URL('./policy.json', import.meta.url));

let rules = [];
let loadError = null;
try {
  const parsed = JSON.parse(readFileSync(policyPath, 'utf8'));
  rules = Array.isArray(parsed.rules) ? parsed.rules : [];
} catch (err) {
  loadError = String(err && err.message ? err.message : err);
}

function matchPattern(cmd, pattern) {
  try {
    return new RegExp(pattern, 'i').test(cmd);
  } catch {
    return String(cmd).toLowerCase().includes(String(pattern).toLowerCase());
  }
}

// Remove single- and double-quoted spans (with backslash escapes) so a shell
// operator INSIDE a quote is not read as one. `grep -n 'a -> b' file` is a read;
// `echo x > file` is a write. Only rules that opt in with `ignoreQuoted: true`
// see the stripped text, so rules matching quoted content keep working.
const QUOTED = /'(?:[^'\\]|\\.)*'|"(?:[^"\\]|\\.)*"/g;

// File-descriptor duplication (`2>&1`, `>&2`) and device sinks (`>/dev/null`,
// `>nul`) redirect a stream but write no file the agent owns, so they are
// removed before the write pattern runs. Measured 2026-11-18 over 1018 unique
// denied commands: 883 of them were one of these two forms.
const SINKS = /[0-9]?>>?\s*&[0-9]|[0-9]?>>?\s*\/dev\/null\b|[0-9]?>>?\s*nul\b/gi;

export function normalizeForWriteCheck(cmd) {
  return String(cmd).replace(QUOTED, "''").replace(SINKS, " ");
}

export function policyLoadError() {
  return loadError;
}

export function classify(tool, command) {
  if (loadError !== null) {
    return {
      id: 'policy-load-error',
      deny: true,
      message: 'Agent-controls policy table unavailable; failing closed (' + loadError + ').'
    };
  }
  const cmd = command ?? '';
  for (const rule of rules) {
    if (rule.matcher && !new RegExp(rule.matcher).test(tool ?? '')) continue;
    // `ignoreQuoted` rules test the command with quoted spans blanked and
    // stream sinks removed, so a shell operator inside a string literal or an
    // fd-duplication is not mistaken for a write to a file.
    const subject = rule.ignoreQuoted ? normalizeForWriteCheck(cmd) : cmd;
    if (rule.pattern && matchPattern(subject, rule.pattern)) {
      return { ...rule };
    }
  }
  return null;
}

export function isAllowed(tool, command) {
  const result = classify(tool, command);
  if (!result) return true;
  return result.deny !== true;
}
