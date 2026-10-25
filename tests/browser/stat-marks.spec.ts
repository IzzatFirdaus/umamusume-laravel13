import { test, expect } from '@playwright/test';

/*
 * The `.stat-*` colour classes and the tokens they claim to resolve (D3 wiring, `52625c8a`).
 *
 * No product component consumes these classes on this tree: they are a ported CSS primitive with no
 * rendered call site, so there is no page to visit that draws them. The contract D3 changed is still
 * measurable: each class resolves to its `--color-stat-*` token in both themes rather than to a
 * frozen literal. The probe is appended to a hydrated page, so the stylesheet under test is the
 * app's own, and both values are read from `getComputedStyle` so hex and `rgb()` compare equal.
 *
 * `.stat-sp` is deliberately absent: it has no `--color-stat-sp` token and is still a literal in
 * `app.css` (`.stat-sp { color:#0E7490 }`), which the D3 pass left behind. This case proves the
 * seven wired classes; the eighth is recorded as a finding, not asserted as green.
 */

const MARKS = ['speed', 'stamina', 'power', 'guts', 'wit', 'pal', 'group'] as const;

test('stat marks resolve their tokens in both themes', async ({ page }) => {
    await page.goto('/', { waitUntil: 'domcontentloaded' });
    await page.locator('#app > *').first().waitFor();

    const read = (theme: 'light' | 'dark') =>
        page.evaluate(
            (args: { theme: 'light' | 'dark'; marks: string[] }) => {
                document.documentElement.dataset.theme = args.theme;

                const host = document.createElement('div');
                document.body.append(host);

                const rows: Record<string, { mark: string; token: string }> = {};

                for (const mark of args.marks) {
                    const el = document.createElement('span');
                    el.className = `stat-${mark}`;
                    host.append(el);

                    const probe = document.createElement('span');
                    probe.style.color = `var(--color-stat-${mark})`;
                    host.append(probe);

                    rows[mark] = {
                        mark: getComputedStyle(el).color,
                        token: getComputedStyle(probe).color,
                    };
                }

                host.remove();

                return rows;
            },
            { theme, marks: [...MARKS] },
        );

    for (const theme of ['light', 'dark'] as const) {
        const rows = await read(theme);

        for (const mark of MARKS) {
            expect(rows[mark].token, `${theme} theme: --color-stat-${mark} did not resolve`).not.toBe('');
            expect(
                rows[mark].mark,
                `${theme} theme: .stat-${mark} does not use --color-stat-${mark}`,
            ).toBe(rows[mark].token);
        }
    }
});
