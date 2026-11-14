#!/usr/bin/env node
// Qoder pre_tool_call hook adapter for Trainer Desk.
// Reads Qoder's Claude-compatible hook payload (tool_name / tool_input.command),
// with fallback field names (toolName / input.command) for Qoder-specific shapes.
// Denies shell calls that duplicate a dedicated tool; blocks via exit code 2
// (Qoder honors exit-code 2 as a deny on PreToolUse).
import { readFileSync } from 'node:fs';
let input = '';
try { input = readFileSync(0, 'utf8'); } catch {}
let p = {};
try { p = JSON.parse(input); } catch {}
const tool = p?.tool_name ?? p?.toolName ?? '';
const cmd = p?.tool_input?.command ?? p?.input?.command ?? '';
const allow = /^\s*(git\b|npm\b|pnpm\b|yarn\b|composer\b|php\b|artisan\b|docker\b|docker-compose\b|node\b|npx\b)(?![\s\S]*\b(open|writeFile|readFile)\b)/i;
if (allow.test(cmd)) process.exit(0);
const deny = (msg) => { process.stderr.write(msg + '\n'); process.exit(2); };
if (/^\s*(cat|Get-Content|type|head|tail|more|less)\b/i.test(cmd))
  deny(`Blocked: use the Read tool instead of \`${cmd.split(/\s+/)[0]}\`.`);
if (/\b(Set-Content|Out-File|tee|Add-Content)\b/i.test(cmd) || /(^|[^>])>\s*\S/.test(cmd))
  deny('Blocked: use the Write/Edit tool instead of shell redirection.');
if (/^\s*(curl|wget|Invoke-WebRequest|Invoke-RestMethod)\b/i.test(cmd))
  deny('Blocked: use the WebFetch tool (or the MCP webfetch) instead of the shell.');
process.exit(0);
