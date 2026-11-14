#!/usr/bin/env node
// Shared policy classification module for Trainer Desk agent controls.
// Loads .tooling/policy.json and exposes isAllowed()/classify().
import { readFileSync } from 'node:fs';

const policyPath = new URL('./policy.json', import.meta.url).pathname;
let policy;
try {
  policy = JSON.parse(readFileSync(policyPath, 'utf8'));
} catch {
  policy = { version: 1, rules: [] };
}

const rules = Array.isArray(policy.rules) ? policy.rules : [];

function matchPattern(cmd, pattern) {
  try {
    return new RegExp(pattern, 'i').test(cmd);
  } catch {
    return String(cmd).toLowerCase().includes(String(pattern).toLowerCase());
  }
}

export function classify(tool, command) {
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
