# GP-02 Visual comparison — screen pairs, app vs HFW

Method: Chromium (repo-local install under `.local-browsers`, Playwright 1.63.0) against the live
app on a session-private scratch DB (`.scratch-uma/grey-parity-2026-11-14/app.sqlite`, fresh
migrate + seed: 133 trainees, 17 runs, 8 veterans) and the wireframes served statically. Both
servers loopback-only. All 16 shots captured at HTTP 200. Readings are structured vision-model
observations of the PNGs; every classification below carries its screenshot paths.

KI-74: pre-flight passed (timed SQLite read 0.37 s, shared dev DB untouched). No red runs occurred;
nothing was re-run hoping for green.

Pairs: Dashboard `/` ↔ `dashboard.html`; Cockpit `/training-runs/1/cockpit` (mid-career, Active,
turn 48) ↔ `cockpit-ura-finale.html`; Veterans `/veterans` ↔ `veterans.html`; Wizard step 1
`/career/setup/scenario` ↔ `career-scenario.html`.

Named limitation, applies to every row: the two sides show the same *screen* with different
*data* (live seeded records vs static exemplars), so content density differs. The grey language —
surface polarity, ink weight, border weight — is compared, not content.

| Screen | Viewport | Card vs page polarity | Ink at body size | Border | Classification | PNGs |
|---|---|---|---|---|---|---|
| Dashboard | 1440x900 | App: card brighter, quiet step. HFW: page tinted, card near-white. **Polarity now matches.** | App: two-tier, hard near-black reserved for headings; body soft. HFW: warm-brown soft throughout. Ink family still differs. | Both: quiet 1px, borders do the elevation work | **visually similar** (surfaces alike; ink differs at a glance) | `dashboard-1440x900-app.png` / `dashboard-1440x900-hfw.png` |
| Cockpit | 1440x900 | App: "elevated white figure on tinted canvas" — same sentence the HFW gets. | App: near-black tiers; HFW: warm-taupe soft. | App: uniform 1px; HFW: faint but visible 1px | **visually similar** | `cockpit-1440x900-app.png` / `cockpit-1440x900-hfw.png` |
| Veterans | 1440x900 | Both: white card on tinted lavender canvas, border-carried lift. | App: cool slate; HFW: warm grey-brown. | App: three border weights; HFW: quiet hairlines | **visually similar** | `veterans-1440x900-app.png` / `veterans-1440x900-hfw.png` |
| Wizard step 1 | 1440x900 | Both: white cards on pale canvas, joint tint+border separation. | App: slate; HFW: softened slate, near-black headings. (HFW sidebar is a dark-slate variant; see GP-08.) | App: active-state border steps; HFW: quiet 1px | **visually similar** | `wizard-scenario-1440x900-app.png` / `wizard-scenario-1440x900-hfw.png` |
| Dashboard | 390x844 | App: white card brighter than lavender-grey tint, holds. HFW: tinted canvas, near-white card, holds. | Both: soft at body size, accents only are saturated. | Both: finished borders in the reflow | **visually similar** (directly inspected) | `dashboard-390x844-app.png` / `dashboard-390x844-hfw.png` |
| Cockpit / Veterans / Wizard | 390x844 | Not individually inspected; carried on desktop verdicts + verified absence of media-query token overrides (01, method note). | — | — | carried, see 01 | `cockpit-390x844-{app,hfw}.png`, `veterans-390x844-{app,hfw}.png`, `wizard-scenario-390x844-{app,hfw}.png` |

## The brief's four questions, answered per the desktop rows

- **Is the card fill visibly brighter than the page in the app?** Yes — and so is the HFW's, now.
  Before `bc102252` the app's card was the brightest surface and the HFW's was not; both now read
  as near-white cards lifted off a tinted page by a quiet step plus the border.
- **Is the ink weight clearly different at body size?** Yes — still. The app's body ink is a cool
  slate/near-black family; the HFW's is warm brown/taupe. This is the one dimension where the
  screens still part company at a glance.
- **Trained eye, screenshot alone, no token table?** Would notice the ink difference immediately;
  would no longer see a surface mismatch. Pre-`bc102252` they would also have caught the
  brightest-card inversion on the dashboard and cockpit.
- **First-time Trainer?** Would not consciously register either difference on any of the four
  pairs; both sides read as "clean light SaaS with white cards". The ink warmth difference reads
  as overall tone, not as a defect.

## Off-family surface observations (grey-scope only)

- App Veterans: the secondary "Offer for Transfer" button fill reads a slightly warmer neutral
  than the cool slate family. Token-level note, not a defect; outside this audit's scope to
  re-theme (GP-09).
- App Cockpit: the stat progress-bar fill is a mid-grey data ink, deliberately darker than the
  surface ramp; consistent across all five bars, reads as intended.
- HFW wizard: dark-slate sidebar variant — see GP-08.
