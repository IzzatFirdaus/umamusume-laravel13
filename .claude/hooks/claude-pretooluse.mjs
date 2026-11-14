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
const rawCmd = p?.tool_input?.command
  ?? p?.tool_input?.args
  ?? p?.tool_input?.cmd
  ?? p?.input?.command
  ?? p?.input?.args
  ?? '';
const cmd = typeof rawCmd === 'string' ? rawCmd : String(rawCmd ?? '');
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
