import { expect, test } from './support/base-test';
import { resolveConfig } from 'vite';

test('Vite keeps frontend hot updates without broadcasting reloads for server-side edits', async () => {
    const config = await resolveConfig({}, 'serve');
    const plugins = config.plugins.map((plugin) => plugin.name);

    expect(plugins).toContain('vite:vue');
    expect(config.server.hmr).not.toBe(false);
    expect(plugins).not.toContain('vite-plugin-full-reload');
    expect(config.server.watch?.ignored).toEqual(expect.arrayContaining([
        '**/.scratch-uma/**',
        '**/database/*.sqlite*',
    ]));
});
