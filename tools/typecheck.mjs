#!/usr/bin/env node
/**
 * The repository typecheck gate, with a private vue-tsgo cache per invocation.
 *
 * vue-tsgo 0.3.0 derives its generated-project directory from the tsconfig path alone and deletes it
 * at the start of every run (`Project.generate()` -> `rm(targetRoot, { recursive: true })`). When two
 * typechecks race, or when the shared directory has accumulated its symlinked `node_modules` tree,
 * that delete fails on Windows — observed as `[safe-delete] ... ETIMEDOUT` / `ENOENT ... mkdir
 * node_modules/.cache/vue-tsgo/...`. Neither means a type error.
 *
 * `empathic.cache()` honours `CACHE_DIR` as its base, so this wrapper points each invocation at an
 * unused directory that it creates before vue-tsgo starts. The deletion then targets a directory
 * this invocation owns and no peer can race it. The checks themselves are untouched: the same
 * `vue-tsgo -p tsconfig.vue.json` and `tsc --noEmit` run in order, and the first non-zero exit code
 * is returned verbatim. No dependency is upgraded and no gate is weakened.
 */
import { spawnSync } from 'node:child_process';
import { randomBytes } from 'node:crypto';
import { mkdirSync } from 'node:fs';
import { dirname, join, resolve } from 'node:path';
import { fileURLToPath } from 'node:url';

const repoRoot = resolve(dirname(fileURLToPath(import.meta.url)), '..');
const mode = (process.argv[2] ?? 'all').replace(/^--/, '');

if (!['all', 'vue', 'ts'].includes(mode)) {
    console.error(`typecheck: unknown mode ${mode}; expected all, vue or ts.`);
    process.exit(2);
}

const invocation = `${process.pid}-${Date.now().toString(36)}-${randomBytes(3).toString('hex')}`;
const cacheDir = join(repoRoot, 'node_modules', '.cache', 'vue-tsgo-runs', invocation);
mkdirSync(cacheDir, { recursive: true });

function run(script, args, env) {
    const result = spawnSync(process.execPath, [join(repoRoot, 'node_modules', script), ...args], {
        cwd: repoRoot,
        env,
        stdio: 'inherit',
    });

    if (result.error) {
        console.error(`typecheck: could not run ${script}: ${result.error.message}`);
        return 1;
    }

    return result.status ?? 1;
}

const env = { ...process.env, CACHE_DIR: cacheDir };

if (mode !== 'ts') {
    const status = run('vue-tsgo/dist/cli.mjs', ['-p', 'tsconfig.vue.json'], env);

    if (status !== 0) {
        process.exit(status);
    }
}

if (mode !== 'vue') {
    process.exitCode = run('typescript/bin/tsc', ['--noEmit'], env);
}
