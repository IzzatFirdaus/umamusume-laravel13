<?php

declare(strict_types=1);

/*
 * KI-25: the turn log forced a horizontal scroll at 390px that nobody could reach.
 *
 * Retired with owner ruling R-2 (`docs/proposals/frontend-development-plan.md` §9.6 close-out,
 * 2026-10-09). This file held the two assertions that named the convention: the run page's turn log
 * wrapped in `role="region" tabindex="0" aria-label="Turn log" overflow-x-auto`, and the census that
 * kept the tab stop on the region that still clipped while the calendar - which had given its band
 * back - kept its name and dropped its tab stop.
 *
 * Both subjects are gone, so the file holds no live assertion rather than being deleted:
 *
 * - The turn log's wrapper was a template attribute on `resources/js/pages/Runs/Show.vue`, deleted
 *   with the page. The Cockpit's correction owns the same turn write but renders it as a `select`
 *   plus a Save button, not as a scrolling table, so it has no clipping region to name.
 * - The calendar's region lives in `resources/js/components/RaceCalendar.vue`, which is no longer
 *   mounted: nothing under `resources/js` imports it. Its only other reference is
 *   `RacePanel.vue`'s `composesRaceCalendar` flag, and that component takes the flag without
 *   rendering the calendar. Both files are left in the tree for the retirement ruling to take with
 *   them; neither is in F2's sweep.
 *
 * The KI-25 convention itself - a scroll container that clips must be focusable - is unchanged and
 * still applies to any region that clips. What is gone is its two recorded instances, and
 * `SCREEN_SPEC.md` §4 SCR-RUN-003 carries the dated note.
 *
 * `legacy.spec.ts` asserts the same convention for the import table's own region, and
 * `database.spec.ts` for the race-slots region, so the rule is not unowned.
 */
