import { expect, test } from './support/base-test';
import { readFileSync, readdirSync } from 'node:fs';
import { dirname, join } from 'node:path';
import { fileURLToPath } from 'node:url';

/*
 * The mandatory preflight is only mandatory while every spec imports the enforced `test`.
 *
 * `support/base-test.ts` protects by construction, but construction is per-file: a new spec written
 * with `import { test, expect } from '@playwright/test'` silently opts out of the identity probe and
 * its mutating setup runs against whatever server the base URL names. The old hook had exactly this
 * hole — one spec of 137 imported it — and nothing caught it because nothing measured it.
 *
 * This case measures it. A direct `@playwright/test` value import in any spec fails the run and
 * names the file, so the next author is told at the gate rather than discovering it after a
 * contaminated write.
 */

// This spec sits in `tests/browser`, so the sweep is its own directory. `import.meta.dirname` is
// not defined under the CommonJS transform Playwright applies, so the ESM URL is resolved explicitly.
const specDir = dirname(fileURLToPath(import.meta.url));

/** Value imports only: `import type { Page } from '@playwright/test'` carries no hooks. */
const DIRECT_VALUE_IMPORT = /^import\s+(?!type\b)\{[^}]*\}\s+from\s+'@playwright\/test';$/m;

function specFiles(): string[] {
    return readdirSync(specDir)
        .filter((name) => name.endsWith('.spec.ts'))
        .sort();
}

test('every browser spec imports the enforced test, never @playwright/test directly', () => {
    const files = specFiles();

    expect(files.length, 'the spec sweep found files to check').toBeGreaterThan(0);

    const bypassing = files.filter((name) => DIRECT_VALUE_IMPORT.test(readFileSync(join(specDir, name), 'utf8')));

    expect(
        bypassing,
        'these specs import test from @playwright/test and so skip the mandatory identity preflight; '
        + "import { test, expect } from './support/base-test' instead",
    ).toEqual([]);
});

test('the enforced test requests the identity preflight fixture', () => {
    const source = readFileSync(join(specDir, 'support', 'base-test.ts'), 'utf8');

    // The guarantee is `auto: true` on a fixture that awaits the probe. Without it the fixture is
    // opt-in, which is the defect this module exists to close; assert the two halves are present
    // together rather than trusting the file's shape.
    expect(source).toMatch(/identityPreflight:\s*\[/);
    expect(source).toMatch(/\{\s*auto:\s*true\s*\}/);
    expect(source).toMatch(/await verifyIdentityPreflight\(/);
});

test('a spec that bypasses the import is detected by the same rule the guard uses', () => {
    // A negative control: without one, a rule that matched nothing would read as a clean sweep.
    const bypass = [
        "import { test, expect } from '@playwright/test';",
        '',
        "test('writes data', async () => {});",
    ].join('\n');
    const compliant = [
        "import { test, expect } from './support/base-test';",
        '',
        "import type { Page } from '@playwright/test';",
    ].join('\n');

    expect(DIRECT_VALUE_IMPORT.test(bypass)).toBe(true);
    expect(DIRECT_VALUE_IMPORT.test(compliant)).toBe(false);
});
