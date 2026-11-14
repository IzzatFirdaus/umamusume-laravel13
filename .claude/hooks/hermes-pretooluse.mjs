#!/usr/bin/env node
// Hermes pre_tool_call hook adapter for Trainer Desk.
// Reads Hermes' hook payload (hook_event_name, tool_name, tool_input.args, session_id, cwd),
// denies shell calls that duplicate a dedicated tool, and blocks via exit code 2.
// Exit-code 2 + stderr message is the Claude-Code/Cursor-compatible block signal Hermes honors
// on pre_tool_call; stdout JSON {"action":"block","message":"..."} is the Hermes-native dialect.
import { readFileSync } from 'node:fs';
let input = '';
try { input = readFileSync(0, 'utf8'); } catch {}
let p = {};
try { p = JSON.parse(input); } catch {}
const event = p?.hook_event_name ?? '';
if (event !== 'pre_tool_call') process.exit(0);
const tool = p?.tool_name ?? '';
if (tool !== 'terminal' && tool !== 'process') process.exit(0);
const args = p?.tool_input?.args ?? '';
const cmd = typeof args === 'string' ? args : String(args ?? '');
const deny = (msg) => { process.stderr.write(msg + '\n'); process.exit(2); };
if (/^\s*(cat|Get-Content|type|head|tail|more|less)\b/i.test(cmd))
  deny(JSON.stringify({ action: 'block', message: `Blocked: use the Read tool instead of \`${cmd.split(/\s+/)[0]}\`.` }));
if (/\b(Set-Content|Out-File|tee|Add-Content)\b/i.test(cmd) || /(^|[^>])>\s*\S/.test(cmd))
  deny(JSON.stringify({ action: 'block', message: 'Blocked: use the Write/Edit tool instead of shell redirection.' }));
if (/^\s*(curl|wget|Invoke-WebRequest|Invoke-RestMethod)\b/i.test(cmd))
  deny(JSON.stringify({ action: 'block', message: 'Blocked: use the WebFetch tool (or the MCP webfetch) instead of the shell.' }));
process.exit(0);
