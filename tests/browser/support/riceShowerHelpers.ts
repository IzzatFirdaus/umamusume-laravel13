import type { Page } from '@playwright/test';
import { OWNED_SKILLS } from '../fixtures/riceShowerUnityCup.fixture';

const WRITE = { timeout: 60_000 } as const;

export interface HelperOptions {
    runUrl: string;
    page: Page;
}

/**
 * Seed the run with the report's owned skills as Suggested so the Skills Planner can see them.
 * The planner has no picker for catalogue skills that are not already on the run, so this setup
 * uses the same `runs.skills.sync` endpoint the AcquisitionStatusEditor posts to.
 */
export async function seedOwnedSkills({ runUrl, page }: HelperOptions): Promise<void> {
    const id = runUrl.match(/\/training-runs\/(\d+)/)?.[1];
    if (id === undefined) {
        throw new Error(`Not a training-run URL: ${runUrl}`);
    }

    const xsrf = (await page.context().cookies()).find((c) => c.name === 'XSRF-TOKEN')?.value;
    if (xsrf === undefined) {
        throw new Error('No XSRF-TOKEN cookie found');
    }

    const body = new URLSearchParams();
    OWNED_SKILLS.forEach((skill, index) => {
        body.append(`skills[${index}][skill_id]`, String(skill.id));
        body.append(`skills[${index}][status]`, 'Suggested');
    });

    const response = await page.request.post(`/training-runs/${id}/skills`, {
        data: body.toString(),
        headers: {
            'content-type': 'application/x-www-form-urlencoded; charset=UTF-8',
            'X-XSRF-TOKEN': decodeURIComponent(xsrf),
        },
    });

    if (response.status() >= 400) {
        throw new Error(`Skill sync failed: ${response.status()} ${await response.text()}`);
    }
}

/**
 * Set a build target on the run so the Skills Planner has a Required list to render.
 */
export async function setBuildTarget({ runUrl, page }: HelperOptions): Promise<void> {
    const id = runUrl.match(/\/training-runs\/(\d+)/)?.[1];
    if (id === undefined) {
        throw new Error(`Not a training-run URL: ${runUrl}`);
    }

    const xsrf = (await page.context().cookies()).find((c) => c.name === 'XSRF-TOKEN')?.value;
    if (xsrf === undefined) {
        throw new Error('No XSRF-TOKEN cookie found');
    }

    const body = new URLSearchParams();
    body.append('purpose', 'StoryClear');
    body.append('distance', 'Medium');
    body.append('surface', 'Turf');
    body.append('style', 'Pace Chaser');
    body.append('targets[Speed]', '900');
    body.append('targets[Stamina]', '800');
    body.append('targets[Power]', '700');
    body.append('targets[Guts]', '600');
    body.append('targets[Wit]', '500');
    for (const skill of OWNED_SKILLS.slice(0, 3)) {
        body.append('skill_priorities[]', skill.name);
    }

    const response = await page.request.put(`${runUrl}/build-target`, {
        headers: {
            'content-type': 'application/x-www-form-urlencoded; charset=UTF-8',
            'X-XSRF-TOKEN': decodeURIComponent(xsrf),
        },
        data: body.toString(),
        maxRedirects: 0,
    });

    if (response.status() >= 400) {
        throw new Error(`Build target update failed: ${response.status()} ${await response.text()}`);
    }
}
