import { expect, type Page } from '@playwright/test';

/*
 * Record turns over `runs.turns.store`.
 *
 * No rendered surface creates a turn any more. The run screen is a redirect to the Cockpit
 * (`routes/web.php`), and the Cockpit's own correction form is a PUT over an existing turn, so it
 * cannot create one. A fixture whose subject is a later screen logs its turns through the route — the
 * shape `ura-panel.spec.ts` and `career-inheritance-event.spec.ts` set. Laravel refreshes the XSRF
 * cookie on every response, so it is read again for each write rather than once for the loop: one
 * stale token and the next POST is a 419.
 *
 * `extra` overrides a field of the default turn, for a fixture that needs a recorded value the
 * screen under test reads (a Skill Point total, or the 700s that make Speed the largest deficit).
 */
export async function recordTurns(
    page: Page,
    runId: string,
    count: number,
    extra: Record<string, string> = {},
): Promise<void> {
    for (let turn = 1; turn <= count; turn++) {
        const xsrf = (await page.context().cookies()).find((cookie) => cookie.name === 'XSRF-TOKEN')?.value;

        expect(xsrf, `the session carries no XSRF-TOKEN cookie before turn ${turn}`).toBeDefined();

        const response = await page.request.post(`/training-runs/${runId}/turns`, {
            form: {
                turn: String(turn),
                speed: '600',
                stamina: '500',
                power: '500',
                guts: '500',
                wit: '500',
                ...extra,
            },
            headers: { 'X-XSRF-TOKEN': decodeURIComponent(xsrf as string) },
        });

        expect(response.status(), `turn ${turn} was refused`).toBeLessThan(400);
    }
}
