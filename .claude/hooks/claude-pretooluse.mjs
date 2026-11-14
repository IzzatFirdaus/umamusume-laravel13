#!/usr/bin/env node
// Claude Code PreToolUse hook: terminal/file/syscall denial adapter.
// Reads the Claude Code PreToolUse payload on stdin, classifies shell commands
// against .tooling/policy.json, and exits 2 to block denied commands.
// Fail closed: a stdin/parse/classify error exits 2 (block) rather than allowing.
import { readFileSync } from 'node:fs';
import { classify } from '../../.tooling/policy.mjs';

let input = '';
try { input = readFileSync(0, 'utf8'); } catch {}
let p = {};
try { p = JSON.parse(input); } catch (err) {
  process.stderr.write('claude-pretooluse: failed to parse hook payload: ' + String(err && err.message ? err.message : err) + '\n');
  process.exit(2);
}

const tool = p?.tool_name ?? p?.toolName ?? '';
const ti = p?.tool_input ?? p?.input ?? {};
const rawCmd = ti.command ?? ti.cmd ?? ti.args;
// Shape-tolerant extraction: string passes through; arrays join (argv form);
// objects use .command/.args; anything else is unrecognized -> fail closed
// (a String()-coerced "[object Object]" would match no rule and silently allow).
function extractCommand(raw) {
  if (raw === undefined || raw === null) return null;
  if (typeof raw === 'string') return raw;
  if (Array.isArray(raw)) return raw.map((x) => String(x)).join(' ');
  if (typeof raw === 'object') {
    const inner = raw.command ?? raw.args ?? raw.cmd;
    if (typeof inner === 'string') return inner;
    if (Array.isArray(inner)) return inner.map((x) => String(x)).join(' ');
    return null;
  }
  return null;
}
const cmd = extractCommand(rawCmd);
if (cmd === null) {
  process.stderr.write(
    'claude-pretooluse: unrecognized payload shape for tool "' + String(tool) + '"; failing closed (no command field extracted).\n'
  );
  process.exit(2);
}
// Other runtimes sharing this adapter (e.g. the DSH bridge) name their shell
// tool differently; map shell-ish tool names onto the policy's Bash matcher.
const policyTool = /bash|shell|terminal|command|exec|process|cmd/i.test(tool) ? 'Bash' : tool;
const result = classify(policyTool, cmd);

if (!result || !result.deny) {
  process.exit(0);
}

const first = String(cmd).split(/\s+/)[0] ?? cmd;
const message = (result.message || 'Blocked command.').replace('{cmd}', first);
process.stderr.write(message + '\n');
process.exit(2);
