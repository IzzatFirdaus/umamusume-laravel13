#!/usr/bin/env node
/**
 * Install the local-only MCP adapters for clients that do not read the tracked workspace-root
 * `.mcp.json`. Run from any checkout or linked worktree after setting:
 *
 *   TRAINERDESK_CHAT_PYTHON = verified Python executable (C:\Program Files\Python312\python.exe)
 *   TRAINERDESK_CHAT_SERVER = pinned server.py absolute path
 *   TRAINERDESK_CHAT_DB     = one absolute messages.sqlite3 path shared by every participant
 *
 * Cursor keeps portable `${env:NAME}` references in `.cursor/mcp.json`. Codex TOML has no equivalent
 * interpolation in its documented MCP schema, so `.codex/config.toml` receives the resolved absolute
 * paths. Both files are gitignored: they are machine-local adapters, not project state. Existing
 * servers and existing trainerdesk-chat entries are preserved — this script never deletes config.
 */
import { mkdirSync, readFileSync, writeFileSync } from 'node:fs';
import { dirname, join, resolve } from 'node:path';
import { fileURLToPath } from 'node:url';

const repoRoot = resolve(dirname(fileURLToPath(import.meta.url)), '..');
const required = ['TRAINERDESK_CHAT_PYTHON', 'TRAINERDESK_CHAT_SERVER', 'TRAINERDESK_CHAT_DB'];
const missing = required.filter((name) => !process.env[name]);

if (missing.length > 0) {
    console.error(`install-local-mcp-adapters: missing environment variables: ${missing.join(', ')}`);
    process.exit(2);
}

const { TRAINERDESK_CHAT_PYTHON: python, TRAINERDESK_CHAT_SERVER: server, TRAINERDESK_CHAT_DB: db } = process.env;
const args = ['-I', '-B', '-X', 'utf8', server, 'chat'];

function writeIfChanged(path, content) {
    let previous = null;

    try {
        previous = readFileSync(path, 'utf8');
    } catch {
        // First install in this worktree.
    }

    if (previous === content) {
        console.log(`unchanged ${path}`);
        return;
    }

    mkdirSync(dirname(path), { recursive: true });
    writeFileSync(path, content);
    console.log(`updated  ${path}`);
}

// Cursor: project-scoped, ${env:NAME} interpolation, no private paths in the file.
const cursorPath = join(repoRoot, '.cursor', 'mcp.json');
let cursor = { mcpServers: {} };

try {
    const parsed = JSON.parse(readFileSync(cursorPath, 'utf8'));

    if (parsed !== null && typeof parsed === 'object' && !Array.isArray(parsed)) {
        cursor = parsed;

        if (cursor.mcpServers === undefined || cursor.mcpServers === null || typeof cursor.mcpServers !== 'object') {
            cursor.mcpServers = {};
        }
    }
} catch (error) {
    if (error.code !== 'ENOENT') {
        console.error(`install-local-mcp-adapters: cannot parse ${cursorPath}: ${error.message}`);
        process.exit(1);
    }
}

// A fresh linked worktree has no local Cursor adapter. Seed the project's existing Laravel Boost
// entry so adding chat does not silently remove an integration present in the main checkout.
if (cursor.mcpServers['laravel-boost'] === undefined) {
    cursor.mcpServers['laravel-boost'] = { command: 'php', args: ['artisan', 'boost:mcp'] };
}

if (cursor.mcpServers['trainerdesk-chat'] === undefined) {
    cursor.mcpServers['trainerdesk-chat'] = {
        type: 'stdio',
        command: '${env:TRAINERDESK_CHAT_PYTHON}',
        args: ['-I', '-B', '-X', 'utf8', '${env:TRAINERDESK_CHAT_SERVER}', 'chat'],
        env: { AGENT_CHAT_DB: '${env:TRAINERDESK_CHAT_DB}' },
    };
}

writeIfChanged(cursorPath, `${JSON.stringify(cursor, null, 4)}\n`);

// Codex: project-scoped TOML; append only when this server is not already declared.
const codexPath = join(repoRoot, '.codex', 'config.toml');
let codex = '';

try {
    codex = readFileSync(codexPath, 'utf8');
} catch (error) {
    if (error.code !== 'ENOENT') {
        console.error(`install-local-mcp-adapters: cannot read ${codexPath}: ${error.message}`);
        process.exit(1);
    }
}

if (!/^\[mcp_servers\.trainerdesk-chat\](\s|$)/m.test(codex)) {
    const tomlLiteral = (value) => JSON.stringify(value);
    const block = [
        '',
        '[mcp_servers.trainerdesk-chat]',
        `command = ${tomlLiteral(python)}`,
        `args = [${args.map(tomlLiteral).join(', ')}]`,
        '',
        '[mcp_servers.trainerdesk-chat.env]',
        `AGENT_CHAT_DB = ${tomlLiteral(db)}`,
        '',
    ].join('\n');

    writeIfChanged(codexPath, `${codex.replace(/\s*$/, '')}\n${block}`);
} else {
    console.log(`unchanged ${codexPath}`);
}
