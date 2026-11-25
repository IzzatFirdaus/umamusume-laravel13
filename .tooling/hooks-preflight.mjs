#!/usr/bin/env node
// Expert Session Preflight: validates the agent hook environment before work.
// Checks hook file existence, path portability, harness compatibility, registry
// integrity, payload compatibility, latency and safeguard operation.
// Exit 1 on any failure. No dependencies.
// Usage: node .tooling/hooks-preflight.mjs [--json]
import { readFileSync, existsSync, readdirSync } from 'node:fs';
import { execFileSync } from 'node:child_process';
import { join, dirname } from 'node:path';
import { fileURLToPath } from 'node:url';
import { classify } from './policy.mjs';

const REPO = join(dirname(fileURLToPath(import.meta.url)), '..');
const JSON_OUT = process.argv.includes('--json');
const results = [];
const check = (name, fn) => {
  try { const detail = fn(); results.push({ name, ok: true, detail: detail ?? '' }); }
  catch (err) { results.push({ name, ok: false, detail: String(err && err.message ? err.message : err) }); }
};
const fail = (msg) => { throw new Error(msg); };

// 1. Hook scripts exist and parse.
const HOOKS = [
  'claude-pretooluse.mjs', 'delegate-cap.mjs', 'delegate-release.mjs',
  'delegation-log.mjs', 'impeccable-post.mjs', 'impeccable-stop.mjs', 'headroom-session.mjs',
];
for (const h of HOOKS) {
  check(`hook exists: ${h}`, () => {
    const p = join(REPO, '.claude', 'hooks', h);
    if (!existsSync(p)) fail(`missing ${p}`);
    execFileSync(process.execPath, ['--check', p], { stdio: 'pipe' });
    return 'syntax ok';
  });
}

// 2. settings.json is valid, portable, and every command resolves.
let settings = null;
check('settings.json parses', () => {
  settings = JSON.parse(readFileSync(join(REPO, '.claude', 'settings.json'), 'utf8'));
  return `${Object.keys(settings.hooks ?? {}).length} events`;
});
check('no ${CLAUDE_PROJECT_DIR} in hook commands', () => {
  const raw = readFileSync(join(REPO, '.claude', 'settings.json'), 'utf8');
  if (raw.includes('CLAUDE_PROJECT_DIR')) fail('unexpanded ${CLAUDE_PROJECT_DIR} breaks under the Copilot harness');
  return 'portable';
});
check('no absolute machine paths in hook commands', () => {
  const raw = readFileSync(join(REPO, '.claude', 'settings.json'), 'utf8');
  const m = raw.match(/[A-Za-z]:[\\/]{1,2}(Users|Projects)[^"]*/);
  if (m) fail(`hardcoded path: ${m[0]}`);
  return 'repo-relative';
});
check('every registered hook script exists', () => {
  const cmds = [];
  for (const ev of Object.values(settings.hooks ?? {})) {
    for (const group of ev) for (const h of group.hooks ?? []) cmds.push(h.command);
  }
  for (const c of cmds) {
    const m = c.match(/node\s+(\S+\.mjs)/);
    if (!m) continue;
    if (!existsSync(join(REPO, m[1]))) fail(`registered but missing: ${m[1]}`);
  }
  return `${cmds.length} commands`;
});

// 3. No duplicate hook command across configuration sources.
check('no duplicate hook commands across sources', () => {
  const sources = ['.claude/settings.json', '.claude/settings.local.json', '.codex/hooks.json', '.cursor/hooks.json'];
  const seen = new Map();
  for (const s of sources) {
    const p = join(REPO, s);
    if (!existsSync(p)) continue;
    const raw = readFileSync(p, 'utf8');
    for (const m of raw.matchAll(/node\s+([^\s"\\]+\.mjs)/g)) {
      const key = m[1];
      if (seen.has(key)) fail(`${key} registered in both ${seen.get(key)} and ${s}`);
      seen.set(key, s);
    }
  }
  return `${seen.size} unique`;
});

// 4. Safeguard operation: positive and negative policy tests.
check('policy denies destructive command', () => {
  const r = classify('Bash', 'rm -rf /');
  if (!r || !r.deny) fail('rm -rf / was not denied');
  return r.id;
});
check('policy allows trusted command', () => {
  const r = classify('Bash', 'git status');
  if (r && r.deny) fail(`git status denied by ${r.id}`);
  return 'allowed';
});
check('policy denies file read via shell', () => {
  const r = classify('Bash', 'cat .env');
  if (!r || !r.deny) fail('cat .env was not denied');
  return r.id;
});

// 5. Payload compatibility: the adapter must accept both the Claude
// (tool_name/tool_input) and Copilot (toolName/toolArgs) shapes, and must not
// block non-shell tools. A hook that loads but misreads the payload is the
// failure mode that config discovery cannot catch.
const runHook = (hook, payload) => {
  try {
    execFileSync(process.execPath, [join(REPO, '.claude', 'hooks', hook)], {
      input: typeof payload === 'string' ? payload : JSON.stringify(payload),
      encoding: 'utf8', stdio: ['pipe', 'pipe', 'pipe'],
    });
    return 0;
  } catch (err) { return err.status ?? 1; }
};
check('payload: non-shell tool passes', () => {
  const code = runHook('claude-pretooluse.mjs', { tool_name: 'read_file', tool_input: { filePath: 'x' } });
  if (code !== 0) fail(`read_file blocked (exit ${code})`);
  return 'exit 0';
});
check('payload: Claude shape denies cat .env', () => {
  const code = runHook('claude-pretooluse.mjs', { tool_name: 'Bash', tool_input: { command: 'cat .env' } });
  if (code !== 2) fail(`expected exit 2, got ${code}`);
  return 'exit 2';
});
check('payload: Copilot shape denies force-push', () => {
  const code = runHook('claude-pretooluse.mjs', { toolName: 'bash', toolArgs: { command: 'git push --force' } });
  if (code !== 2) fail(`expected exit 2, got ${code}`);
  return 'exit 2';
});
check('payload: malformed input fails closed', () => {
  const code = runHook('claude-pretooluse.mjs', 'not json');
  if (code !== 2) fail(`expected exit 2, got ${code}`);
  return 'exit 2';
});

// 6. Hook latency: a hook that runs on every tool call must stay well under
// its registered timeout. 500ms is the ceiling for the classifier path.
check('hook latency under 500ms', () => {
  const start = process.hrtime.bigint();
  runHook('claude-pretooluse.mjs', { tool_name: 'Bash', tool_input: { command: 'git status' } });
  const ms = Number(process.hrtime.bigint() - start) / 1e6;
  if (ms > 500) fail(`${ms.toFixed(0)}ms exceeds 500ms ceiling`);
  return `${ms.toFixed(0)}ms`;
});

// 7. Skills registry integrity. Discovery reads directories with a SKILL.md;
// the lock file is a marketplace manifest and may legitimately list skills that
// are not installed, so drift is reported as a warning, not a failure.
const warnings = [];
check('skill directories are discoverable', () => {
  const dir = join(REPO, '.agents', 'skills');
  if (!existsSync(dir)) return 'absent (optional)';
  const dirs = readdirSync(dir, { withFileTypes: true }).filter((d) => d.isDirectory()).map((d) => d.name);
  const broken = dirs.filter((n) => !existsSync(join(dir, n, 'SKILL.md')));
  if (broken.length) fail(`${broken.length} skill dir(s) without SKILL.md: ${broken.slice(0, 5).join(', ')}`);
  return `${dirs.length} skills`;
});
check('skills-lock.json parses', () => {
  const p = join(REPO, '.agents', 'skills-lock.json');
  if (!existsSync(p)) return 'absent (optional)';
  const lock = JSON.parse(readFileSync(p, 'utf8'));
  const names = Object.keys(lock.skills ?? {});
  const missing = names.filter((n) => !existsSync(join(REPO, '.agents', 'skills', n)));
  if (missing.length) warnings.push(`skills-lock drift: ${missing.length}/${names.length} locked skills not installed (${missing.slice(0, 3).join(', ')}...)`);
  return `${names.length} locked`;
});

// Report.
const failed = results.filter((r) => !r.ok);
if (JSON_OUT) {
  console.log(JSON.stringify({ ok: failed.length === 0, results, warnings }, null, 2));
} else {
  for (const r of results) console.log(`${r.ok ? 'PASS' : 'FAIL'}  ${r.name}${r.detail ? '  (' + r.detail + ')' : ''}`);
  for (const w of warnings) console.log(`WARN  ${w}`);
  console.log(`\n${results.length - failed.length}/${results.length} checks passed`);
}
process.exit(failed.length === 0 ? 0 : 1);
