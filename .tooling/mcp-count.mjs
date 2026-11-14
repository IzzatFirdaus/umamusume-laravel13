#!/usr/bin/env node
// Reads .mcp.json and prints each declared server with its tool count.
import { readFileSync } from 'node:fs';

let mcp;
try { mcp = JSON.parse(readFileSync('.mcp.json', 'utf8')); } catch {
  process.stderr.write('mcp-count: cannot read .mcp.json\n');
  process.exit(1);
}
const servers = mcp.mcpServers || {};
for (const [name, def] of Object.entries(servers)) {
  const tools = Array.isArray(def.tools) ? def.tools.length : 'n/a';
  process.stdout.write(name + ' -> ' + tools + ' tools\n');
}
process.exit(0);
