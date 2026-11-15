#!/usr/bin/env node
// Code-execution deny-list guard for Hermes.
// Reads newline-delimited JSON hook payloads from stdin, extracts the
// command, classifies each against .tooling/policy.json (shared deny list
// via policy.mjs), emits one JSON object per line on stdout. Fail-closed
// on any policy load/parse error or malformed JSON input.
// Exit 2 if any command is denied; exit 0 if all allowed.
import { classify } from './policy.mjs';

let stdin = '';
process.stdin.on('data', chunk => { stdin += chunk; });
process.stdin.on('end', () => {
  const lines = stdin.split('\n').filter(l => l.trim().length > 0);
  let anyDenied = false;
  const results = [];
  for (const line of lines) {
    let payload;
    try {
      payload = JSON.parse(line);
    } catch {
      // Fail-closed: malformed JSON input blocks.
      anyDenied = true;
      results.push(JSON.stringify({ decision: 'block', reason: 'malformed JSON input; failing closed.' }));
      continue;
    }
    const ti = payload?.tool_input ?? {};
    const cmd = ti?.command ?? ti?.cmd ?? ti?.args ?? payload?.args ?? payload?.command ?? '';
    try {
      const result = classify('code_execution', String(cmd));
      if (result && result.deny === true) {
        anyDenied = true;
        results.push(JSON.stringify({ decision: 'block', reason: result.message }));
      } else {
        results.push(JSON.stringify({ decision: 'allow' }));
      }
    } catch {
      // Fail-closed: any classification error blocks.
      anyDenied = true;
      results.push(JSON.stringify({ decision: 'block', reason: 'guard classification error; failing closed.' }));
    }
  }
  process.stdout.write(results.length ? results.join('\n') + '\n' : '');
  process.exit(anyDenied ? 2 : 0);
});
