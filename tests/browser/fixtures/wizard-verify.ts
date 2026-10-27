import { type Page } from '@playwright/test';
import { captureSnapshot, diffSnapshots, type DiffReport, type ElementSnapshot } from './visual-diff';

/*
 * One page's three-diff parity report: text, computed style, and bounding box.
 *
 * This is the harness a parity phase closes on. `hfw-structure.ts` answers "are the same blocks here, in
 * this order" and the answer survives a wrong colour, a wrong font weight and a card that is 40px taller
 * than the wireframe's. The three buckets below are the rest of the question, and a phase does not close
 * while any of them carries an entry that the page has not recorded as an `HFW-OVERRIDE` comment.
 *
 * Both sides are captured from the live DOM at the same viewport, so the comparison is between two
 * renders rather than between a render and a wireframe's source. The app is served by the Playwright
 * `webServer` (its own scratch database, `playwright.config.ts`); the wireframe is a local file, reached
 * over `file://`.
 *
 * `appUrl` may be a path (`/career/setup/trainee`), which resolves against the Playwright `baseURL` and
 * therefore against whichever port the run bound. A full URL is also accepted, for a per-record page.
 */

export type VerifyOptions = {
    appUrl: string;
    hfwUrl: string;
    /** Defaults to the wizard canvas, which both sides draw around the step body. */
    rootSelector?: string;
    width?: number;
    height?: number;
};

export type VerifyReport = {
    /** Text-content drift, one line per shared element whose own text differs. */
    text: string[];
    computed: DiffReport['computedDrift'];
    rect: DiffReport['rectDrift'];
    /** Selector per element the app draws and the wireframe does not. */
    excess: string[];
    /** Selector per element the wireframe draws and the app does not. */
    missing: string[];
};

/** Own-text drift over the elements both sides draw. */
function textDrift(app: ElementSnapshot[], hfw: ElementSnapshot[]): string[] {
    const hfwBySelector = new Map(hfw.map((entry) => [entry.selector, entry]));
    const lines: string[] = [];

    for (const entry of app) {
        const other = hfwBySelector.get(entry.selector);

        if (other === undefined || other.text === entry.text) {
            continue;
        }

        lines.push(`${entry.selector}: app="${entry.text ?? ''}" hfw="${other.text ?? ''}"`);
    }

    return lines;
}

export async function verifyPage(page: Page, opts: VerifyOptions): Promise<VerifyReport> {
    const rootSelector = opts.rootSelector ?? '.canvas';
    const width = opts.width ?? 1280;
    const height = opts.height ?? 800;

    await page.setViewportSize({ width, height });

    // The app waits on the Inertia mount, so the snapshot is never taken against the empty shell. The
    // wireframe is static markup and needs no mount.
    await page.goto(opts.appUrl);
    await page.locator('#app > *').first().waitFor();
    const app = await captureSnapshot(page, rootSelector);

    await page.goto(opts.hfwUrl);
    const hfw = await captureSnapshot(page, rootSelector);

    const report = diffSnapshots(app, hfw);

    return {
        text: textDrift(app, hfw),
        computed: report.computedDrift,
        rect: report.rectDrift,
        excess: report.excess.map((entry) => entry.selector),
        missing: report.missing.map((entry) => entry.selector),
    };
}

/** True when all three buckets are empty: the per-page acceptance test in one call. */
export function isClean(report: VerifyReport): boolean {
    return (
        report.text.length === 0 &&
        report.computed.length === 0 &&
        report.rect.length === 0 &&
        report.excess.length === 0 &&
        report.missing.length === 0
    );
}
