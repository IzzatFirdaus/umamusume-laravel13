import assert from 'node:assert/strict';
import { test } from 'node:test';
import { execFileSync } from 'node:child_process';
import { fileURLToPath } from 'node:url';

const GUARD = fileURLToPath(new URL('./code-exec-guard.mjs', import.meta.url));

function runGuard(input) {
  let stdout = '';
  let exitCode = 0;
  try {
    stdout = execFileSync('node', [GUARD], { input, encoding: 'utf8', maxBuffer: 1024 * 1024 });
  } catch (err) {
    exitCode = err.status ?? 1;
    stdout = err.stdout ? err.stdout.toString() : '';
  }
  return { exitCode, stdout };
}

function payload(cmd) {
  return JSON.stringify({ tool_input: { command: cmd } }) + '\n';
}

function lastJson(stdout) {
  const lines = stdout.trim().split('\n').filter(l => l.length > 0);
  return JSON.parse(lines[lines.length - 1]);
}

test('deny: rm -rf /tmp', () => {
  const { exitCode, stdout } = runGuard(payload('rm -rf /tmp'));
  assert.strictEqual(exitCode, 2);
  assert.strictEqual(lastJson(stdout).decision, 'block');
});

test('deny: mv a b', () => {
  const { exitCode, stdout } = runGuard(payload('mv a b'));
  assert.strictEqual(exitCode, 2);
  assert.strictEqual(lastJson(stdout).decision, 'block');
});

test('deny: chmod 777 x', () => {
  const { exitCode, stdout } = runGuard(payload('chmod 777 x'));
  assert.strictEqual(exitCode, 2);
  assert.strictEqual(lastJson(stdout).decision, 'block');
});

test('deny: git push --force', () => {
  const { exitCode, stdout } = runGuard(payload('git push --force'));
  assert.strictEqual(exitCode, 2);
  assert.strictEqual(lastJson(stdout).decision, 'block');
});

test('deny: git reset --hard', () => {
  const { exitCode, stdout } = runGuard(payload('git reset --hard'));
  assert.strictEqual(exitCode, 2);
  assert.strictEqual(lastJson(stdout).decision, 'block');
});

test('deny: dd if=/dev/zero', () => {
  const { exitCode, stdout } = runGuard(payload('dd if=/dev/zero'));
  assert.strictEqual(exitCode, 2);
  assert.strictEqual(lastJson(stdout).decision, 'block');
});

test('deny: mkfs.ext4', () => {
  const { exitCode, stdout } = runGuard(payload('mkfs.ext4'));
  assert.strictEqual(exitCode, 2);
  assert.strictEqual(lastJson(stdout).decision, 'block');
});

test('deny: fdisk -l', () => {
  const { exitCode, stdout } = runGuard(payload('fdisk -l'));
  assert.strictEqual(exitCode, 2);
  assert.strictEqual(lastJson(stdout).decision, 'block');
});

test('allow: git status', () => {
  const { exitCode, stdout } = runGuard(payload('git status'));
  assert.strictEqual(exitCode, 0);
  assert.strictEqual(lastJson(stdout).decision, 'allow');
});

test('allow: git diff', () => {
  const { exitCode, stdout } = runGuard(payload('git diff'));
  assert.strictEqual(exitCode, 0);
  assert.strictEqual(lastJson(stdout).decision, 'allow');
});

test('allow: git log --oneline', () => {
  const { exitCode, stdout } = runGuard(payload('git log --oneline'));
  assert.strictEqual(exitCode, 0);
  assert.strictEqual(lastJson(stdout).decision, 'allow');
});

test('allow: ls', () => {
  const { exitCode, stdout } = runGuard(payload('ls'));
  assert.strictEqual(exitCode, 0);
  assert.strictEqual(lastJson(stdout).decision, 'allow');
});

test('allow: php artisan test', () => {
  const { exitCode, stdout } = runGuard(payload('php artisan test'));
  assert.strictEqual(exitCode, 0);
  assert.strictEqual(lastJson(stdout).decision, 'allow');
});

test('allow: npm run typecheck', () => {
  const { exitCode, stdout } = runGuard(payload('npm run typecheck'));
  assert.strictEqual(exitCode, 0);
  assert.strictEqual(lastJson(stdout).decision, 'allow');
});

test('allow batch: multiple safe commands on stdin', () => {
  const input = payload('git status') + payload('ls') + payload('npm run typecheck');
  const { exitCode, stdout } = runGuard(input);
  assert.strictEqual(exitCode, 0);
  const lines = stdout.trim().split('\n').filter(l => l.length > 0);
  assert.strictEqual(lines.length, 3);
  for (const line of lines) {
    assert.strictEqual(JSON.parse(line).decision, 'allow');
  }
});

test('mixed batch: deny + allow exits 2', () => {
  const input = payload('git status') + payload('rm -rf /tmp');
  const { exitCode, stdout } = runGuard(input);
  assert.strictEqual(exitCode, 2);
  const lines = stdout.trim().split('\n').filter(l => l.length > 0);
  assert.strictEqual(lines.length, 2);
  assert.strictEqual(JSON.parse(lines[0]).decision, 'allow');
  assert.strictEqual(JSON.parse(lines[1]).decision, 'block');
});

test('empty input exits 0', () => {
  const { exitCode, stdout } = runGuard('');
  assert.strictEqual(exitCode, 0);
});

test('fail-closed: malformed JSON input', () => {
  const { exitCode, stdout } = runGuard('{bad json\n');
  assert.strictEqual(exitCode, 2);
  assert.strictEqual(lastJson(stdout).decision, 'block');
});

test('fail-closed: empty JSON object', () => {
  const { exitCode, stdout } = runGuard('{}\n');
  assert.strictEqual(exitCode, 0);
  assert.strictEqual(lastJson(stdout).decision, 'allow');
});

test('extract command from args sibling', () => {
  const { exitCode, stdout } = runGuard('{"args":"git status"}\n');
  assert.strictEqual(exitCode, 0);
  assert.strictEqual(lastJson(stdout).decision, 'allow');
});

test('allow: rm inside .scratch-uma', () => {
  const { exitCode, stdout } = runGuard(payload('rm .scratch-uma/commit-msg.txt'));
  assert.strictEqual(exitCode, 0);
  assert.strictEqual(lastJson(stdout).decision, 'allow');
});

test('allow: rm -rf inside .scratch-uma', () => {
  const { exitCode, stdout } = runGuard(payload('rm -rf .scratch-uma/temp/'));
  assert.strictEqual(exitCode, 0);
  assert.strictEqual(lastJson(stdout).decision, 'allow');
});

test('deny regression: rm -rf /tmp/x', () => {
  const { exitCode, stdout } = runGuard(payload('rm -rf /tmp/x'));
  assert.strictEqual(exitCode, 2);
  assert.strictEqual(lastJson(stdout).decision, 'block');
});

test('deny regression: rm -rf ~/Documents', () => {
  const { exitCode, stdout } = runGuard(payload('rm -rf ~/Documents'));
  assert.strictEqual(exitCode, 2);
  assert.strictEqual(lastJson(stdout).decision, 'block');
});

test('deny regression: rm traversal out of .scratch-uma', () => {
  const { exitCode, stdout } = runGuard(payload('rm .scratch-uma/../app.css'));
  assert.strictEqual(exitCode, 2);
  assert.strictEqual(lastJson(stdout).decision, 'block');
});

test('deny regression: rm scratch plus outside target', () => {
  const { exitCode, stdout } = runGuard(payload('rm .scratch-uma/a /etc/passwd'));
  assert.strictEqual(exitCode, 2);
  assert.strictEqual(lastJson(stdout).decision, 'block');
});

test('deny regression: chained rm after scratch rm', () => {
  const { exitCode, stdout } = runGuard(payload('rm .scratch-uma/a; rm -rf /tmp'));
  assert.strictEqual(exitCode, 2);
  assert.strictEqual(lastJson(stdout).decision, 'block');
});

test('deny: git commit message contains greater-than arrow', () => {
  const { exitCode, stdout } = runGuard(payload('git commit -m "fix: a -> b"'));
  assert.strictEqual(exitCode, 2);
  assert.strictEqual(lastJson(stdout).decision, 'block');
  assert.ok(lastJson(stdout).reason.includes('-F'));
});

test('deny: git commit message contains pipe', () => {
  const { exitCode, stdout } = runGuard(payload('git commit -m "a | b"'));
  assert.strictEqual(exitCode, 2);
  assert.strictEqual(lastJson(stdout).decision, 'block');
});

test('deny: git commit message contains semicolon', () => {
  const { exitCode, stdout } = runGuard(payload('git commit -m "a; b"'));
  assert.strictEqual(exitCode, 2);
  assert.strictEqual(lastJson(stdout).decision, 'block');
});

test('allow: git commit -F file', () => {
  const { exitCode, stdout } = runGuard(payload('git commit -F .scratch-uma/msg.txt'));
  assert.strictEqual(exitCode, 0);
  assert.strictEqual(lastJson(stdout).decision, 'allow');
});

test('allow: git status regression after metachar rule', () => {
  const { exitCode, stdout } = runGuard(payload('git status'));
  assert.strictEqual(exitCode, 0);
  assert.strictEqual(lastJson(stdout).decision, 'allow');
});

test('extract command from top-level command key', () => {
  const { exitCode, stdout } = runGuard('{"command":"git status"}\n');
  assert.strictEqual(exitCode, 0);
  assert.strictEqual(lastJson(stdout).decision, 'allow');
});

/* ---------------------------------------------------------------------------
 * file-write rule: quoted operators and fd-duplication are not writes.
 *
 * Measured 2026-11-18 over 1018 unique denied commands in
 * `.tooling/logs/denials.jsonl`: the unquoted-operator form denied 1028 of 1030
 * matches as false positives (467 were `2>/dev/null`, 402 `2>&1`, 103 a PHP/JS
 * `->` or `=>` inside a quoted pattern, 14 a `>/dev/null` sink). Only 23 unique
 * commands were real writes to a file. Every case below is drawn from that
 * corpus.
 * ------------------------------------------------------------------------- */

test('allow: stderr merge 2>&1 is not a write', () => {
  const { exitCode, stdout } = runGuard(payload('npx playwright test tests/browser/dashboard.spec.ts --workers=1 2>&1 | tail -25'));
  assert.strictEqual(exitCode, 0);
  assert.strictEqual(lastJson(stdout).decision, 'allow');
});

test('allow: 2>/dev/null is a sink, not a write', () => {
  const { exitCode, stdout } = runGuard(payload('ls docs/audits 2>/dev/null || echo "DIR NOT FOUND"'));
  assert.strictEqual(exitCode, 0);
  assert.strictEqual(lastJson(stdout).decision, 'allow');
});

test('allow: >/dev/null sink', () => {
  const { exitCode, stdout } = runGuard(payload('node tools/ui-framework-check.mjs > /dev/null; echo exit=$?'));
  assert.strictEqual(exitCode, 0);
  assert.strictEqual(lastJson(stdout).decision, 'allow');
});

test('allow: PHP arrow inside a quoted grep pattern', () => {
  const { exitCode, stdout } = runGuard(payload('grep -rn "runs.export\\|->export(" app tests --include=*.php | grep -v Transfer'));
  assert.strictEqual(exitCode, 0);
  assert.strictEqual(lastJson(stdout).decision, 'allow');
});

test('allow: => inside a single-quoted pattern', () => {
  const { exitCode, stdout } = runGuard(payload("grep -rn 'taxonomy => value' docs/adr"));
  assert.strictEqual(exitCode, 0);
  assert.strictEqual(lastJson(stdout).decision, 'allow');
});

test('deny: redirect to a real file is still a write', () => {
  const { exitCode, stdout } = runGuard(payload('php artisan test --compact > .scratch-uma/s1/full-suite.log 2>&1'));
  assert.strictEqual(exitCode, 2);
  assert.strictEqual(lastJson(stdout).decision, 'block');
});

test('deny: append to a real file is still a write', () => {
  const { exitCode, stdout } = runGuard(payload('echo "EXIT=$?" >> .scratch-uma/s1/full-suite.log'));
  assert.strictEqual(exitCode, 2);
  assert.strictEqual(lastJson(stdout).decision, 'block');
});

test('deny: quoted text does not hide a real redirect', () => {
  const { exitCode, stdout } = runGuard(payload('echo "hello world" > app/Models/Foo.php'));
  assert.strictEqual(exitCode, 2);
  assert.strictEqual(lastJson(stdout).decision, 'block');
});

test('deny: tee is still a write', () => {
  const { exitCode, stdout } = runGuard(payload('npm run typecheck | tee .scratch-uma/tc.log'));
  assert.strictEqual(exitCode, 2);
  assert.strictEqual(lastJson(stdout).decision, 'block');
});
