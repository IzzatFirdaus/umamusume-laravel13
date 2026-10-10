import { expect, type Locator } from '@playwright/test';

/*
 * WCAG 2.2 SC 2.5.8's floor (44px, plan §12.1) asserted over a set of controls, in one place.
 *
 * The rule has two halves and the second is the one that was missing. The first is the floor itself:
 * every control a Trainer can reach must be at least 44px tall. The second is that a control inside a
 * **collapsed** `<details>` is not reachable at all — the browser does not lay it out, so it has no box,
 * `boundingBox()` answers null, and reading that as `0` reports a violation of an invariant the control
 * does not break. The disclosure's own `<summary>` is the trigger a Trainer hits, and it is measured
 * like any other control because it is one.
 *
 * This lived inline in each spec, which is how four of them came to fail together when `91494a1`
 * (2026-10-09) put a `<details>`-held confirm button on the Cockpit: the sweep that `6a74f85`
 * (2026-10-08) wrote over `main a, main button` had no way to know a control it had matched was not
 * rendered, and every copy of the sweep had the same hole (KNOWN-ISSUES.md KI-92). One statement of the
 * rule, next to `buildAxe`, is what keeps the next page from rediscovering it.
 *
 * This is a scope, not a weakening: a control that is genuinely visible and genuinely under the floor
 * still fails, and `minimumMeasured` keeps the skip above from turning the sweep into a way to pass on
 * nothing. Callers that know how many controls their page holds should pass that number.
 */
export async function expectTapTargets(targets: Locator, minimumMeasured = 1): Promise<void> {
    const count = await targets.count();
    expect(count, 'the page renders no control at all').toBeGreaterThan(0);

    let measured = 0;

    for (let i = 0; i < count; i++) {
        const target = targets.nth(i);

        if (!(await target.isVisible())) {
            continue;
        }

        measured++;
        const box = await target.boundingBox();
        expect(box?.height ?? 0, `control ${i} is not sized to the 44px contract`).toBeGreaterThanOrEqual(44);
    }

    expect(measured, 'no control was visible to measure').toBeGreaterThanOrEqual(minimumMeasured);
}
