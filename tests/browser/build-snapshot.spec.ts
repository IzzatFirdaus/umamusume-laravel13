import { expect, test } from '@playwright/test';
import { mkdirSync, mkdtempSync, rmSync, writeFileSync } from 'node:fs';
import { tmpdir } from 'node:os';
import { join } from 'node:path';
import { assertBuildSnapshot, captureBuildSnapshot } from './global-setup';

/**
 * Build-identity regression coverage without running Vite or touching `public/build`.
 *
 * A concurrent `npm run build` can swap the assets between setup and the last spec. The suite must
 * still be able to name the exact bytes it measured and fail loudly if those bytes change mid-run;
 * these cases exercise both halves against a temp fixture.
 */
test('a build snapshot identifies its bytes and detects a mid-run asset swap', () => {
    const root = mkdtempSync(join(tmpdir(), 'td-build-'));
    const build = join(root, 'build');
    const assets = join(build, 'assets');
    const artifacts = join(root, 'artifacts');
    mkdirSync(assets, { recursive: true });

    const env: NodeJS.ProcessEnv = {
        PLAYWRIGHT_RUN_ID: 'snapshot-regression',
        PLAYWRIGHT_BUILD_DIR: build,
        PLAYWRIGHT_ARTIFACT_DIR: artifacts,
    };

    try {
        writeFileSync(join(build, 'manifest.json'), JSON.stringify({ 'resources/js/app.ts': { file: 'assets/app-a.js' } }));
        writeFileSync(join(assets, 'app-a.js'), 'console.log("first");');

        const snapshot = captureBuildSnapshot(env);

        expect(snapshot.id).toMatch(/^[0-9a-f]{12}$/);
        expect(env.PLAYWRIGHT_BUILD_ID).toBe(snapshot.id);
        expect(() => assertBuildSnapshot(env)).not.toThrow();

        writeFileSync(join(assets, 'app-a.js'), 'console.log("second");');

        expect(() => assertBuildSnapshot(env)).toThrow(/changed during this Playwright run/);
    } finally {
        rmSync(root, { recursive: true, force: true });
    }
});
