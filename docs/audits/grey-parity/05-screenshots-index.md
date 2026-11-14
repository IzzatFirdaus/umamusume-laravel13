# GP-06 Screenshots index

Captured 2026-11-14, Chromium repo-local (`.local-browsers`, Playwright 1.63.0), default light
theme, no `data-theme` override. App side: live app on session-private scratch DB
(`.scratch-uma/grey-parity-2026-11-14/app.sqlite`, migrate:fresh + UraFinaleRunsSeeder +
TrackerVeteransSeeder; 133 trainees, 17 runs, 8 veterans). HFW side:
`docs/design-research/frontend-v2-wireframes` served statically over loopback. All 16 captures
returned HTTP 200 (capture log in `.scratch-uma/grey-parity-2026-11-14/`).

| File | Screen / route (or wireframe file) | Viewport | Referenced by |
|---|---|---|---|
| `dashboard-1440x900-app.png` | Dashboard `/` | 1440x900 | 02 row 1; GP-07 |
| `dashboard-1440x900-hfw.png` | `dashboard.html` | 1440x900 | 02 row 1; GP-07 |
| `cockpit-1440x900-app.png` | `/training-runs/1/cockpit` (Active, turn 48) | 1440x900 | 02 row 2; GP-07 |
| `cockpit-1440x900-hfw.png` | `cockpit-ura-finale.html` (completed exemplar) | 1440x900 | 02 row 2; GP-07 |
| `veterans-1440x900-app.png` | `/veterans` | 1440x900 | 02 row 3; GP-07, GP-09 |
| `veterans-1440x900-hfw.png` | `veterans.html` | 1440x900 | 02 row 3; GP-07 |
| `wizard-scenario-1440x900-app.png` | `/career/setup/scenario` | 1440x900 | 02 row 4; GP-07 |
| `wizard-scenario-1440x900-hfw.png` | `career-scenario.html` | 1440x900 | 02 row 4; GP-08 |
| `dashboard-390x844-app.png` | Dashboard `/` | 390x844 | 02 row 5 (individually inspected) |
| `dashboard-390x844-hfw.png` | `dashboard.html` | 390x844 | 02 row 5 (individually inspected) |
| `cockpit-390x844-app.png` | `/training-runs/1/cockpit` | 390x844 | 02 row 6 (carried) |
| `cockpit-390x844-hfw.png` | `cockpit-ura-finale.html` | 390x844 | 02 row 6 (carried) |
| `veterans-390x844-app.png` | `/veterans` | 390x844 | 02 row 6 (carried) |
| `veterans-390x844-hfw.png` | `veterans.html` | 390x844 | 02 row 6 (carried) |
| `wizard-scenario-390x844-app.png` | `/career/setup/scenario` | 390x844 | 02 row 6 (carried) |
| `wizard-scenario-390x844-hfw.png` | `career-scenario.html` | 390x844 | 02 row 6 (carried) |

Carry-over basis for row 6: neither stylesheet reassigns surface tokens inside a media query
(01, method note), so narrow-width reflow changes layout, not the grey language; the directly
inspected mobile dashboard pair confirmed the model holds.
