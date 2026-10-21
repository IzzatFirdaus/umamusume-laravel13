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

## Usability heuristics

The reference is [`docs/proposals/usability-heuristics-2026-10-21.md`](../../proposals/usability-heuristics-2026-10-21.md),
which records Nielsen's ten as NN/g states them. Every page carries a **Heuristic coverage** item in its
own annotation key.

**H1 Visibility of System Status is the heuristic this set exists for.** A cockpit answers "where am I"
before "what next", so every live resource is printed rather than implied: Energy band, mood tier,
Spirit Burst count, Grade Points against the next deadline, coins, rotation countdown, Race Fatigue
band, the finale or concert countdown. The audit's recurring finding was that the app owned these
facts and placed them below the decision, which is the H1 failure at the layout level rather than the
copy level.

Two heuristics are answered narrowly and deliberately:

- **H5 Error Prevention** appears only where a scenario has a costly action. Trackblazer's Race Fatigue
  band sits beside the race action, so the cost of a third consecutive race is prevented rather than
  reported after the fact.
- **H9 Help Users Recognize, Diagnose, and Recover from Errors** appears on the URA Finale cockpit,
  which is the one file here that depicts a closed run. It states the outcome and replaces the advisor
  slot with the end-state rather than leaving an empty rail, because an absence rendered as a blank
  region reports "nothing" instead of "finished".

**H10 is not claimed here.** These are per-scenario cockpit views reached from a run; help belongs in
the shell, and the `help.html` page in the v2 set is where it lives.

## The seven frameworks this set answers to

The references are in [`docs/proposals/`](../../proposals/), one file per framework, each quoted from its
source, with [`design-framework-index-2026-10-21.md`](../../proposals/design-framework-index-2026-10-21.md)
as the entry point. Every page here carries a **Framework coverage** item naming the framework that
decides that cockpit's distinctive decision.

| Framework | Pages | The decision it settles there |
| --- | --- | --- |
| **Gerhardt-Powals** (10) | all four | Whether the product left arithmetic in the user's head. The Grade Point runway computes the shortfall, so the Trainer never subtracts 212 from 300. |
| **Shneiderman** (8) | `01`, `02`, `04`, `05` | How a flow behaves from open to close. A closed run states its outcome rather than leaving an empty advisor rail. |
| **Information architecture** (8) | all four | The space between screens. The rail stays banded rather than growing with the run, and this scenario degrades to baseline plus known caps rather than inventing chrome. |
| **Tognazzini** (16) | `01` | Latency, 44px targets, and the labels that lead with the differentiating word. |
| **Gestalt** (12) | `01`, `04` | Whether two things will be read as one group. |
| **Norman** (6) | `02`, `03` | Affordance versus signifier, and the two gulfs. |
| **WCAG 2.2 AA** | all five | The only normative one, and these are cockpits that carry live figures, so it binds hardest here. |

### Accessibility, as WCAG 2.2 AA

This set had **no skip link, no `main` landmark and no status-message region on any of its five pages**.
All three are now present, and the set has no `shell.css`, so the skip link's own rules are inlined per
file.

What is **not** verified: no contrast ratio has been measured in a browser, because Chromium cannot launch
under this repository's sandbox. 1.4.3 and 1.4.11 remain unverified here and are recorded as such.

## Laws applied, and the rules each page had to satisfy

The inventory is read from `lawsofux.com` (28 index entries plus Zeigarnik Effect) and `uilaws.com`
(13 entries, which do publish Continuity and Closure). Two attribution notes carried from the audit:
**Common Fate is published by neither site** and is cited as a Gestalt principle where used, and no
"Law of Experience" exists — the concept is Prior Experience.

| Law or rule | Where it shows |
| --- | --- |
| **Goal-Gradient (18)** | The scenario's one dated obligation is in the first viewport on all four cockpits: the finale countdown, the Grade Point deadline, the concert countdown. |
| **Hick's Law (4)** | One recommended action and one alternative per cockpit, not six buttons. The advisor's priority order is stated once. |
| **Miller's Law (5)** | Stat bands are five plus four tiles. Teammate rosters chunk by distance. |
| **Von Restorff (15)** | One filled action per screen. An unmarked figure is the norm; only exceptions carry a provenance mark. |
| **Selective Attention (33) / D-259** | Mood renders `GREAT ↑ +20%` — tier, arrow and percentage. The arrow is the only part carrying ordinal direction, because the five pill colours sit at equal luminance and `GOOD`/`BAD` are 3.3 degrees of hue apart. |
| **Fitts's Law (3)** | 44px minimum on every control, matching the app's `h-11`. |
| **Law of Uniform Connectedness (9)** | Where a relationship exists between records it is drawn, not implied: the party strip and the teammate rows share a boundary, and a member is never slotted into two distance rows at once (the v9 defect, `DESIGN-CORPUS.md:2433`). |
| **Closure (12, uilaws)** | Partial indicators stay honest. A stat the run does not record reads `N/A` with a reason, never a closed-up or invented figure. |
| **Chunking (28) / Cognitive Load (24)** | The advisor prints the number the decision needs, so the Trainer never recalls it from another screen. |
| **Peak-End (17)** | The finale panel is the scenario's own shape — elimination, 4 Team Races, or a 3-race points league — not a generic bracket. |
| **D-220 / D-241 — scenario composition** | Each cockpit renders only the widgets its scenario owns. URA Finale has no Team Rank gauge or shop; Trackblazer has no race calendar; Our Grand Concert degrades to baseline plus known caps rather than inventing chrome. |
| **D-222 — a level chip names its cause** | Unity Cup's facility level comes from team stat rank (`G/F=1 … S=5`), Trackblazer's from repetition plus purchased levels, URA's from repetition alone. The identical label `Lv 5` means different things, so each says which. |
| **D-223 — Spirit Burst is a six-state machine** | `spent` is not terminal: chargeable → charged → held → normal spent → Extreme chargeable → Extreme spent. |
| **D-232 — two countdowns on one strip** | Trackblazer shows both the 6-turn shop rotation and the Grade Point deadline, and states that surplus does not carry over. |
| **D-81 — an inert control says so** | Every page carries a `Static wireframe` chip. Only the theme and layout-width controls are live anywhere in the set. |

## Where the facts come from

| Kind | Source |
| --- | --- |
| Live run and dashboard values | Inertia props read from `http://127.0.0.1:8000` on 2026-10-21 |
| Per-scenario mechanics | `docs/research-scratch/SCENARIO-PUBLISHER-REFERENCES.md` (GameTora, uma.guide, Game8 sections) |
| Scenario rules and caps | `docs/UMAMUSUME_REFERENCE.md` §1.3.4, §2.2.1–2.2.4; the four `docs/proposals/*-plan.md` |
| Scenario configuration matrix | `docs/research-scratch/DESIGN-CORPUS.md` §10n |
| Support cards, skills, sparks | `docs/research-scratch/SUPPORT-CARDS.md`, `SKILLS-MECHANICS.md` |
| Tokens | `resources/css/app.css` `@theme static` (read 2026-10-21) |

## Two disputed figures these pages print as disputed

The repository records these as unresolved, so a wireframe must not pick one silently.

- **Trackblazer's Akikawa bond gates** — uma.guide reads `19 / 31 / 51`, GameWith reads `15 / 30 / 50`. The fan figures are not disputed; the bond figures are. Both readings belong on the surface.
- **Trackblazer's grade objectives for a limited-turf-range trainee** — GameTora says `60 / 200 / 300`, uma.guide says such a trainee follows the dirt track at `30 / 200 / 300` (KI-15). The repo renders only the `standard` profile.

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
