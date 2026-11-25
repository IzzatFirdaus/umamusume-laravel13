/**
 * Drives the refusal-before-body proof and reports what actually happened.
 *
 * The claim under test is behavioural, not structural: when the serving process is not this run's
 * assigned server, an ordinary Playwright test must be refused BEFORE its `beforeEach` and body. The
 * only way to establish that is to run Playwright on such a server and look at the markers.
 *
 * Run: node tests/browser/harness/run-refusal-proof.mjs
 *
 * Exit 0 means the refusal happened and nothing was executed. Exit 1 means the guarantee is absent
 * and the driver says which marker was written.
 */
import { spawnSync } from 'node:child_process';
import { existsSync, mkdirSync, readFileSync, rmSync } from 'node:fs';
import { join } from 'node:path';

const dir = join(process.cwd(), '.scratch-uma', 'refusal-proof');
const bodyMarker = join(dir, 'body-ran.txt');
const setupMarker = join(dir, 'before-each-ran.txt');

rmSync(dir, { recursive: true, force: true });
mkdirSync(dir, { recursive: true });

const result = spawnSync(
    'npx',
    ['playwright', 'test', '--config', 'tests/browser/harness/playwright.refusal.config.ts'],
    {
        cwd: process.cwd(),
        env: { ...process.env, REFUSAL_PROOF_DIR: dir },
        encoding: 'utf8',
        shell: true,
    },
);

const output = `${result.stdout ?? ''}${result.stderr ?? ''}`;

console.log('--- playwright output ---');
console.log(output.trim() || '(empty)');
console.log('--- end ---');

const bodyRan = existsSync(bodyMarker);
const setupRan = existsSync(setupMarker);
const reportPath = join(dir, 'refusal-proof-report.json');
const report = existsSync(reportPath) ? JSON.parse(readFileSync(reportPath, 'utf8')) : null;
const specs = report?.suites?.flatMap((suite) => suite.specs ?? []) ?? [];
const results = specs.flatMap((spec) => spec.tests.flatMap((entry) => entry.results ?? []));
const firstResult = results[0];
const errorMessage = firstResult?.error?.message ?? '';
const status = firstResult?.status ?? '(no result recorded)';

const checks = [
    ['the run reported a failure', result.status !== 0],
    // A port clash also fails the run, and it would read as a pass. The claim is narrower: the
    // probe reached the impostor and rejected what it answered.
    ['the failure is not a port clash', !/already occupied/i.test(errorMessage)],
    ['the failure is the identity probe rejecting the token', /does not carry this run's identity token/i.test(errorMessage)],
    ['the specimen test did not pass', status !== 'passed'],
    ['the mutating beforeEach did not execute', !setupRan],
    ['the test body did not execute', !bodyRan],
];
const failed = checks.filter(([, passed]) => !passed);

console.log('');
console.log('status:', status);
console.log('error:', errorMessage.split('\n')[0] || '(none)');
console.log('');
for (const [name, passed] of checks) {
    console.log(`${passed ? 'PASS' : 'FAIL'}  ${name}`);
}

if (failed.length > 0) {
    console.error(`\nREFUSAL-BEFORE-BODY: FAIL (${failed.map(([name]) => name).join(', ')})`);
    process.exit(1);
}

console.log('\nREFUSAL-BEFORE-BODY: PASS');
