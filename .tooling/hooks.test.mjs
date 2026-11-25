#!/usr/bin/env node
// Regression tests for the agent hook layer. Run: node .tooling/hooks.test.mjs
// Covers the failure modes that produced the NonBlockingError storm:
//   1. hook commands must not depend on ${CLAUDE_PROJECT_DIR}
//   2. non-shell tools must not reach the policy classifier
//   3. both the Claude and Copilot payload shapes must be understood
//   4. delegation state must survive concurrent spawns and release correctly
//   5. the impeccable shim must skip non-frontend files
import { execFileSync } from 'node:child_process';
import { readFileSync } from 'node:fs';
import { join, dirname } from 'node:path';
import { fileURLToPath } from 'node:url';
import assert from 'node:assert';

const REPO = join(dirname(fileURLToPath(import.meta.url)), '..');
const HOOK = join(REPO, '.claude', 'hooks', 'claude-pretooluse.mjs');
const CAP = join(REPO, '.claude', 'hooks', 'delegate-cap.mjs');
const RELEASE = join(REPO, '.claude', 'hooks', 'delegate-release.mjs');
const LOG = join(REPO, '.claude', 'hooks', 'delegation-log.mjs');
const IMPECCABLE = join(REPO, '.claude', 'hooks', 'impeccable-post.mjs');

// Run a hook with a JSON payload on stdin; return { code, stderr }.
function run(hook, payload) {
  try {
    execFileSync(process.execPath, [hook], {
      input: typeof payload === 'string' ? payload : JSON.stringify(payload),
      encoding: 'utf8', stdio: ['pipe', 'pipe', 'pipe'],
    });
    return { code: 0, stderr: '' };
  } catch (err) {
    return { code: err.status ?? 1, stderr: String(err.stderr ?? '') };
  }
}

const cwd = REPO;
const pre = (tool, input) => ({ hook_event_name: 'PreToolUse', tool_name: tool, tool_input: input, cwd });

// 1. settings.json is portable.
const settings = readFileSync(join(REPO, '.claude', 'settings.json'), 'utf8');
assert.ok(!settings.includes('CLAUDE_PROJECT_DIR'), 'settings.json must not use ${CLAUDE_PROJECT_DIR}');
assert.ok(!/[A-Za-z]:[\\/]{1,2}Users/.test(settings), 'settings.json must not hardcode a user path');

// 2. Non-shell tools pass without touching the policy table.
for (const tool of ['read_file', 'memory', 'list_dir', 'file_search', 'manage_todo_list', 'execution_subagent']) {
  const r = run(HOOK, pre(tool, { filePath: 'x' }));
  assert.strictEqual(r.code, 0, `${tool} must not be blocked (got ${r.code}: ${r.stderr})`);
}

// 3. Shell tools are still classified: allow trusted, deny destructive.
assert.strictEqual(run(HOOK, pre('run_in_terminal', { command: 'git status' })).code, 0, 'git status must pass');
assert.strictEqual(run(HOOK, pre('run_in_terminal', { command: 'rm -rf /' })).code, 2, 'rm -rf / must be denied');
assert.strictEqual(run(HOOK, pre('run_in_terminal', { command: 'cat .env' })).code, 2, 'cat .env must be denied');
assert.strictEqual(run(HOOK, pre('bash', { command: 'git push --force' })).code, 2, 'force-push must be denied');

// 4. Copilot camelCase payload shape is understood.
assert.strictEqual(
  run(HOOK, { toolName: 'bash', toolArgs: { command: 'git status' } }).code, 0,
  'Copilot shape: git status must pass'
);
assert.strictEqual(
  run(HOOK, { toolName: 'bash', toolArgs: { command: 'git push --force' } }).code, 2,
  'Copilot shape: force-push must be denied'
);

// 5. Malformed payload fails closed for shell tools.
assert.strictEqual(run(HOOK, 'not json').code, 2, 'malformed payload must exit 2 (fail closed)');

// 6. Delegation cap: non-delegation tools are ignored, depth cap still enforced.
assert.strictEqual(run(CAP, pre('read_file', { filePath: 'x' })).code, 0, 'read_file must not touch depth state');
const session = 'test-' + Date.now();
const spawn = { hook_event_name: 'PreToolUse', tool_name: 'Task', tool_input: { prompt: 'x' }, session_id: session, cwd };
assert.strictEqual(run(CAP, spawn).code, 0, 'spawn 1 allowed');
assert.strictEqual(run(CAP, spawn).code, 0, 'spawn 2 allowed');
assert.strictEqual(run(CAP, spawn).code, 2, 'spawn 3 denied by depth cap');

// 7. Release frees a level, so the next spawn is allowed again.
assert.strictEqual(run(RELEASE, spawn).code, 0, 'release must exit 0');
assert.strictEqual(run(CAP, spawn).code, 0, 'spawn after release allowed');

// 8. Delegation log always exits 0 and never blocks.
assert.strictEqual(run(LOG, { session_id: 's', agent_name: 'x' }).code, 0, 'delegation-log must exit 0');
assert.strictEqual(run(LOG, 'not json').code, 0, 'delegation-log must tolerate malformed input');

// 9. Impeccable shim skips non-frontend files (the check costs ~3.7s).
const phpEdit = run(IMPECCABLE, pre('Edit', { file_path: 'app/Models/Foo.php' }));
assert.strictEqual(phpEdit.code, 0, 'PHP edit must exit 0');
assert.strictEqual(run(IMPECCABLE, pre('Edit', { file_path: 'resources/js/Pages/Home.vue' })).code, 0, 'Vue edit must exit 0');
assert.strictEqual(run(IMPECCABLE, 'not json').code, 0, 'impeccable must tolerate malformed input');

console.log('hooks.test.mjs: all assertions passed');
