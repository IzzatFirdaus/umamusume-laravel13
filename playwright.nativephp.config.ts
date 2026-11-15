import { defineConfig } from '@playwright/test';

export default defineConfig({
  testDir: './tests/browser',
  testMatch: 'nativephp-packaged.spec.ts',
  fullyParallel: false,
  workers: 1,
  timeout: 600000,
  use: { trace: 'on-first-retry' },
});
