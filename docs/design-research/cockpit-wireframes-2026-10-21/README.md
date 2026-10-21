# Cockpit wireframes · 2026-10-21

High-fidelity wireframes of the Career Cockpit for each of the four Global scenarios, derived from the
UX-law audit run on 2026-10-21 against `http://127.0.0.1:8000/training-runs/25/cockpit` (run 25, Rice Shower,
Unity Cup, Active, turn 67).

| File | Scenario | Data provenance |
| --- | --- | --- |
| [`01-unity-cup.html`](./01-unity-cup.html) | Unity Cup | **Real.** Run 25's Inertia props, read from the live dev server. **Superseded by 05.** |
| [`02-ura-finale.html`](./02-ura-finale.html) | URA Finale | **Real.** Run 17 (Maruzensky, Completed, turn 75). No Active URA run exists in the dev database. |
| [`03-trackblazer.html`](./03-trackblazer.html) | Trackblazer | **Mock.** No run exists. Panel set, widgets, caps, Grade Point objectives and the 19 shop rows are real `config/scenarios.php` values; the trainee, turn and stats are invented and the page carries a banner saying so. |
| [`04-our-grand-concert.html`](./04-our-grand-concert.html) | Our Grand Concert | **Mock**, same rule. The five Performance cells are deliberately empty because no stored value exists. |
| [`05-unity-cup-hfw-v2.html`](./05-unity-cup-hfw-v2.html) | Unity Cup | **Real**, run 25. Supersedes 01. Applies the 12 changes from the external `Trainer Desk — career cockpit prototype.htm` review, reconciled against `app.css`, `DESIGN.md` §2.3 and `DesignTokensTest.php`. |

## Domain sources added 2026-10-21

Every file now carries facts from the scenario references, not only from runtime data:

| Source | What it contributed |
| --- | --- |
| `docs/UMAMUSUME_REFERENCE.md` §1.1.5–1.1.6 | Energy below 50 is the failure-risk line; rest pays +30 / +50 / +70; Wit costs no Energy and pays +4–5 SP. Mood is a multiplier: GREAT +20% / +4%, GOOD +10% / +2%, NORMAL 0, BAD −10% / −2%, AWFUL −20% / −4%. |
| `docs/UMAMUSUME_REFERENCE.md` §1.3.4 | Published caps per scenario; the 2000 hard ceiling; the URA client-ceiling caveat (1327/1322/1404/1306/1802 is per-run, not the URA cap). |
| `docs/proposals/Unity-Cup-plan.md` §8, §8a, §9, §10 | Team Board as a permanent fixture; rank → facility mapping (G/F 1, D/E 2, B/C 3, A 4, S 5); advisor priority order; burst breakpoints 4/7/10/13; dual-readout requirement. |
| `docs/proposals/URA-Finale-plan.md` §7, §8, §9 | Happy Meek duel panel (30/15 SP, +1 cap tier, six-win escalation to Past My Limits at 200+ SP); facility repetition; advisor ranking summer timing second. |
| `docs/proposals/Trackblazer-plan.md` §8, §9, §10, §11 | Grade Point runway and the "surplus does not carry over" rule; Race Fatigue table; shop constraints; two currencies are independent. |
| `docs/proposals/Our-Grand-Concert-plan.md` Slice 6 + 2026-10-21 note | The Performance input lands at `Career/TrainingDetail.vue`, not `Runs/Show.vue` (retired at F2); rejected terminology list. |
| `docs/UMAMUSUME_REFERENCE.md` §2.2.4 | Lessons open turn five; Songs fill the gauge; 5 SP per technique Lesson, 25 per Song Lesson; +50 Performance cap per live; four Promo Concerts + one Grand Concert every six months. |

Two facts deliberately **not** printed anywhere, because their sources mark them unconfirmed:
Race Fatigue's "3 races = upcoming or run?" convention and which year "after late December" means;
and the Racing Spirit spark trigger rate and stat-bonus amount.

## What these are, and are not

- **Are:** static HTML, one file each, no build step, no external network dependency. Open directly in a browser.
- **Are not:** shipped screens, or proof that the proposed change renders in Vue. Nothing in `resources/`,
  `config/`, `tests/` or any gate was touched. These are design artifacts only.

## Light and dark, and the three layout widths

Added 2026-10-21, so this set matches the v2 frontend set and the app.

- **Dark theme** — `app.css`'s dark block, verbatim: page `#08070A`, panel `#26262E`, raised `#24262A`,
  rule `#6E6C73`, muted ink `#AAABB5`. Not an inversion. The dark ramp puts `panel` *above* `sunken`,
  mirroring the light order, because `app.css` widened that step so a bordered card does not dissolve.
  `--status-caution` is deliberately not overridden: the amber pill is chrome the client paints
  identically in either theme.
- **Theme resolution follows D-104** — stored preference → `prefers-color-scheme` → light, resolved in a
  pre-paint script in `<head>`, so there is no flash of the wrong theme. Each file's `<html>` no longer
  hardcodes `data-theme="light"`.
- **Layout width** — the app's three shell measures, from `useLayout.ts:20-24`:
  Compact `max-w-3xl` (768px), Standard `max-w-6xl` (1152px, the default), Wide `max-w-shell-wide`
  (1536px). `CareerLayout.vue:56` applies the same class, because the cockpit page renders inside
  `CareerLayout` (`Career/Cockpit.vue:27`).
- **Compact is a composition, not just a narrower cap** — `Dashboard.vue:168-169` returns `grid-cols-1`
  for compact, so the cockpit's multi-column grid collapses to one column there too.
- **Where the control lives** — there is none on these pages, by design. The app's only theme control is
  Preferences → Theme (`Preferences/Edit.vue:315-327`, a three-option select: Follow the system / Light /
  Dark), and the only layout control is Preferences → Layout width (three radios). Both are on the
  Preferences page of the v2 set; these files read the stored values.
- **One concession** — the stored values ride `localStorage` here (`td-wireframe-theme`,
  `td-wireframe-layout`) because a static artifact has no database. The app stores both server-side;
  `PRD.md` §6.12 cuts browser storage as a second source of truth.

## Token source, and one divergence worth knowing

Values come from `resources/css/app.css` `@theme static`, read 2026-10-21, because that is what the app
actually renders. `DESIGN.md` §2.1's table **disagrees with the runtime stylesheet on several roles**, and
the wireframes follow the stylesheet:

| Role | `DESIGN.md` §2.1 | `app.css` (what renders) |
| --- | --- | --- |
| `--color-page` | `#F2F1F8` | `#F4F4FF` |
| `--color-panel` | `#F8F8FB` | `#FBFBFD` |
| `--color-ink-muted` | `#6E6459` | `#6A6A75` |
| `--color-rule` | `#E4E1EA` | `#84828C` |
| `--color-brand-accent-ink` | `#8877FF` | `#6554E0` |
| dark `--color-page` | `#0D0C0F` | `#08070A` |

That is a documentation-vs-runtime gap in `DESIGN.md`, not something these wireframes introduce. Recorded
here rather than fixed, because `DESIGN.md` is outside the scope of this task.

## The one finding all four share

The app owns real per-scenario progress data and places every piece of it **below the decision**:

| Scenario | Progress data the app owns | Where it renders today | Wireframe fix |
| --- | --- | --- | --- |
| Trackblazer | Grade Point checkpoints 60 / 300 / 300, aptitude-conditional | Scenario panel | B1 |
| URA Finale | Fan gates 60k/70k/120k; finale targets Speed ≈800, Stamina 400–700 | Scenario panel | A9 |
| Unity Cup | Spirit Burst bands (10–12 for this run) | Scenario panel | A1 |
| Our Grand Concert | none — Performance stores no value at all | nowhere | C1 |

Goal-Gradient (law 18) is the law this Cockpit fails most consistently, and for Unity Cup, URA Finale and
Trackblazer it fails for a **layout** reason rather than a missing-fact reason. Our Grand Concert is the
exception where the fact genuinely does not exist.

## Cross-scenario law matrix, as drawn

| Law | Unity Cup | URA Finale | Trackblazer | Our Grand Concert |
| --- | --- | --- | --- | --- |
| Goal-Gradient (18) | FAIL | FAIL (data exists below fold) | likely PASS | FAIL (no number exists) |
| Selective Attention (33) | FAIL (both scenario widgets null) | n/a | unmeasured | n/a |
| Prägnanz (10) | PASS | PASS | PASS | PASS (single-sourced vocabulary) |
| Occam's Razor (21) | partial | PASS | risk (19 items) | PASS |
| Hick's Law (4) | PASS | PASS | risk (19 shop rows, 12 rated Must Buy) | PASS |
| Similarity (7) | unmeasured | unmeasured | risk (two placement tables) | unmeasured |
| Continuity (11, Gestalt) | unmeasured | unmeasured | PASS by design | unmeasured |
| Fitts (3), Serial Position (16), Peak-End (17), Doherty (23) | PASS | not rendered (Closed) | not rendered | not rendered |

## What was not verified

- **No rendered screenshots.** Chromium cannot launch under this sandbox (`spawn EPERM`), so every claim here
  and in the audit comes from Inertia props and component source, never from a rendered frame.
- **No contrast arithmetic.** The wireframes use the runtime token values; they do not re-check any pair
  against WCAG. `DESIGN.md` §2.1 already records that the G-18 browser gate measures nothing yet.
- **No mobile breakpoint check.** The wireframes carry responsive rules (1180px / 820px), but nothing was
  measured at 390px. The audit's 390px overflow question stays open.
- **Trackblazer and Our Grand Concert are composition contracts, not observations.** No run exists for either.

## Annotation conventions

Markers `A1`–`A9`, `B1`–`B4`, `C1`–`C3` in the pages map to the numbered key in each file's footer. Every
marker names the law it answers and whether the wireframe is fixing a failure or flagging a risk. Nothing is
marked "fixed" on the strength of a measurement — only on the strength of a source-level change.
