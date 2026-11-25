#!/usr/bin/env node
// PreToolUse hook: shell-command denial adapter (Claude Code + Copilot harness).
// Reads the PreToolUse payload on stdin, classifies shell commands against
// .tooling/policy.json, and exits 2 to block denied commands.
//
// Non-shell tools exit 0 immediately: they carry no command to classify, and
// failing closed on them would deny every Read/Edit call (the DSH bridge
// registers this adapter with matcher ".*", so that path is live).
// Fail closed only for shell tools: a stdin/parse/classify error exits 2.
import { readFileSync } from 'node:fs';
import { classify } from '../../.tooling/policy.mjs';
import { logDenial } from '../../.tooling/denial-log.mjs';

// Shell-ish tool names across runtimes: Claude `Bash`, Copilot `bash`/
// `powershell`, VS Code `run_in_terminal`, Hermes `terminal`/`process`,
// DSH `bash`. Separator-anchored so `execution_subagent` is not read as `exec`.
const SHELL = /(^|[^a-z])(bash|shell|terminal|command|exec|process|cmd|powershell)([^a-z]|$)/i;

let input = '';
try { input = readFileSync(0, 'utf8'); } catch {}
let p = {};
try { p = JSON.parse(input); } catch (err) {
  process.stderr.write('claude-pretooluse: failed to parse hook payload: ' + String(err?.message ?? err) + '\n');
  process.exit(2);
}

const tool = p?.tool_name ?? p?.toolName ?? '';
if (!tool) {
  process.stderr.write('claude-pretooluse: payload has no tool name; failing closed.\n');
  process.exit(2);
}
if (!SHELL.test(tool)) process.exit(0);

// Copilot camelCase payloads carry toolArgs; Claude carries tool_input.
const ti = p?.tool_input ?? p?.toolArgs ?? p?.input ?? {};
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

// The shared table matches on Bash|PowerShell transport names; map every
// shell-ish runtime name onto the policy's Bash matcher.
const result = classify('Bash', cmd);

if (!result || !result.deny) {
  process.exit(0);
}

const first = String(cmd).split(/\s+/)[0] ?? cmd;
const message = (result.message || 'Blocked command.').replace('{cmd}', first);
process.stderr.write(message + '\n');
logDenial({ runtime: 'claude', tool, command: cmd, rule: result.id, replacement: result.replacement ?? '' });
process.exit(2);
