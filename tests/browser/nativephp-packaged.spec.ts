import { test, expect, _electron as electron } from '@playwright/test';

test.describe('NativePHP Packaged Runtime', () => {
  test('enforces request-secret gate', async () => {
    const exePath = process.env.NATIVEPHP_EXE_PATH;
    if (!exePath) throw new Error('NATIVEPHP_EXE_PATH is not set');

    const electronApp = await electron.launch({ executablePath: exePath });
    const secret = await electronApp.evaluate(async ({ app }) => process.env.NATIVEPHP_SECRET);
    expect(secret).toBeTruthy();

    const window = await electronApp.firstWindow();
    const baseUrl = new URL(window.url()).origin;

    const res1 = await fetch(baseUrl);
    expect(res1.status).toBe(403); // No secret

    const res2 = await fetch(baseUrl, { headers: { 'X-NativePHP-Secret': 'wrong-secret' } });
    expect(res2.status).toBe(403); // Wrong secret

    const res3 = await fetch(baseUrl, { headers: { 'X-NativePHP-Secret': secret as string } });
    expect(res3.status).toBe(200); // Valid header

    const cookies = await electronApp.context().cookies();
    const secretCookie = cookies.find(c => c.name === '_php_native');
    const res4 = await fetch(baseUrl, { headers: { Cookie: `_php_native=${secretCookie!.value}` } });
    expect(res4.status).toBe(200); // Valid cookie

    await electronApp.close();
  });
});
