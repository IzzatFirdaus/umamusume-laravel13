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
    if (rule.pattern && matchPattern(cmd, rule.pattern)) {
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
