# Group A + Group B plan

**Date.** 2026-10-24.
**Audit.** `docs/audits/group-ab-audit-2026-10-24.md` (19 items, HEAD `3cdc466c`).

## 1. Purpose

This plan turns the audit's classification into an owner-ruled work order. It covers the 19 items the
four UX walks documented as missing: 11 Group A (walk-1/2/3 "no UI home") and 8 Group B (round-4
owner-call rows). It names what is implemented now (the `openable` Group A set), what waits on a ruling,
and what is filed to `KNOWN-ISSUES.md` without landing. It does **not** cover S2b (R4-03, the build-target
carrier) or S11 (R4-13, the team-rank copy line) — both are their own slices, gated on rulings outside
this pass, and `git log --grep="R4-03\|R4-13"` shows neither landed.

## 2. Audit summary

| Item | Group | State | Where it goes |
|---|---|---|---|
| A-0.1 Cap composition | A | openable | Phase 2 |
| A-0.2 Run-stored aptitudes | A | needs-schema | ruling list + KI |
| A-0.3 Owned-skill inventory | A | landed (`fbd37bdd`) | dropped |
| A-0.4 Reached effect values | A | needs-schema | ruling list + KI |
| A-0.5 Card client levels | A | needs-schema | ruling list + KI |
| A-0.6 Roster editor | A | needs-schema | ruling list + KI |
| A-0.7 +30 ranking bonus | A | landed (`6a74f857`) | dropped |
| A-0.8 Purchasable pool | A | openable | Phase 2 |
| A-0.9 Burst band position | A | landed (`5eba0d01`) | dropped |
| A-0.10 Top Star threshold | A | openable | Phase 2 |
| A-0.11 Mood Effect percentages | A | needs-shape-decision | ruling list + KI |
| B-0.1 Scenario mandatory races (R4-06) | B | needs-shape-decision | ruling list + KI |
| B-0.2 Cleared-goal recording (R4-07) | B | needs-shape-decision | ruling list + KI |
| B-0.3 Pending-turn + date (R4-08) | B | needs-shape-decision | ruling list + KI |
| B-0.4 Scheduled-race state (R4-10) | B | needs-shape-decision | ruling list + KI |
| B-0.5 Team stat grades (R4-12) | B | needs-schema | ruling list + KI |
| B-0.6 Current Spirit gauge (R4-14) | B | needs-schema | ruling list + KI |
| B-0.7 Skill levels (R4-16) | B | needs-schema | ruling list + KI |
| B-0.8 Borrowed-card owner (R4-18) | B | needs-shape-decision | ruling list + KI |

Counts at audit time: openable 3 · needs-schema 7 · needs-shape-decision 6 · landed 3 · ruled-out 0.

### Audit summary — classification deltas (owner ruling 2026-10-24)

The audit doc is the record and is **not edited**. Where the owner's ruling moved an item, the row above
is `Superseded by ruling: <ID>` and the row below replaces it.

| Item | Audit state | Ruling | New state |
|---|---|---|---|
| A-0.2 | needs-schema | Catalog-only (D8 verified) | `ruled-out` — no per-run storage |
| A-0.4 | needs-schema | Deferred to A-0.5 | `blocked: A-0.5` — stays maxima until card levels land |
| A-0.6 | needs-schema | Derived read-out (no column) | `openable` — Phase 2 |
| A-0.11 | needs-shape-decision | One sourced figure into `config('uma')` | `openable` — Phase 2 |
| B-0.8 | needs-shape-decision | Will-not-add (loopback tool) | `ruled-out` — `Rented` is the whole record |
| A-0.5 | needs-schema | Accepted: per-run `deck_slots.card_level` | `blocked: ADR` — out of this pass |
| B-0.5 | needs-schema | Accepted: team grades column | `blocked: ADR` — out of this pass |
| B-0.6 | needs-schema | Accepted: `current_spirit` column | `blocked: ADR` — out of this pass |
| B-0.7 | needs-schema | Accepted: skill levels column | `blocked: ADR` — out of this pass |

Post-ruling counts: openable 5 (A-0.1, A-0.6, A-0.8, A-0.10, A-0.11) · ruled-out 2 (A-0.2, B-0.8) ·
blocked 5 (A-0.4, A-0.5, B-0.5, B-0.6, B-0.7) · Phase 3 openable 4 (B-0.1, B-0.2, B-0.3, B-0.4) ·
landed 3 (A-0.3, A-0.7, A-0.9).

*Dated addition 2026-10-24 (release review).* Two of the owner's Phase 3 rulings were corrected by the
evidence at HEAD, and both are recorded rather than re-opened:

- **B-0.1** was ruled "filter mandatory races by scenario key". The audit's implementation found
  `docs/scenarios/09-global-race-calendar.md:105,632-633` states the three URA Finals rounds are **shared**
  across scenarios and only the Final forks (Unity Cup's is Aoharu), so the current mandatory list is correct
  and the ruled filter would have deleted legitimate shared obligations. B-0.1 is `ruled-out` (does not
  reproduce); see `KI-122`.
- **B-0.3** was ruled "add a snapshot date field". `resources/js/pages/Career/Snapshot/Setup.vue:165-186`
  already renders Year/Month/Phase and `CreateSnapshotRun:58-79` writes `career_position`, so the date input
  was already present. B-0.3 is `landed` (does not reproduce); see `KI-123`.

## 3. Slice order (openable Group A)

Ordered cheapest + highest-signal + highest-confidence first.

1. **A-0.10 — name the Top Star threshold.** The fan line renders `class · gap` but drops the
   `nextThreshold` it already receives. Fix: render `240,000` beside the gap in
   `CareerStatePanel.vue` (`fanSuffix`, `:77-85`). Openable because the number is shipped
   (`FanLadder::fromValue`, `app/Services/FanLadder.php:52-66`) — no schema, no shape ruling, one
   render line. Pin: a browser case in `career-cockpit-forms.spec.ts` asserting the threshold text
   renders. Files: `resources/js/components/career/CareerStatePanel.vue`,
   `tests/browser/career-cockpit-forms.spec.ts`.

2. **A-0.1 — show the cap arithmetic's inputs.** The caps ship (`CockpitController::identitySection()`,
   `app/Http/Controllers/Career/CockpitController.php:394-417`) and the six blue sparks are stored
   (`training_runs.legacy_selection`) but the two never sit side by side. Fix: a `capComposition`
   section shipping the caps plus the sparks from the run's own `legacy_selection`, with the panel
   stating the two do not reconcile (§1.2's own open question). Openable: read-only, both inputs already
   stored. Pin: `CockpitCapCompositionTest` (caps + sparks side by side) and a browser case. Files:
   `app/Http/Controllers/Career/CockpitController.php`, `resources/js/pages/Career/Cockpit.vue`,
   `tests/Feature/CockpitCapCompositionTest.php`, `tests/browser/career-cockpit-forms.spec.ts`.

3. **A-0.8 — show the purchasable pool with prices.** `SkillsPlannerController::pickerSkills()`
   (`app/Http/Controllers/Career/SkillsPlannerController.php:539-549`) ships only `{id, name}`, and no
   surface prices the not-yet-acquired pool. Fix: a `purchasable` section — every priced skill minus the
   ones already acquired, sorted by `sp_cost`, with the sum and the shortfall against the run's SP.
   Openable: read-only, the `skills` table already carries `sp_cost`. Pin:
   `CareerSkillsPlannerTest` (pool, sum, shortfall) and a browser case. Files:
   `app/Http/Controllers/Career/SkillsPlannerController.php`, `resources/js/pages/Career/SkillsPlanner.vue`,
   `tests/Feature/CareerSkillsPlannerTest.php`, `tests/browser/career-skills-planner.spec.ts`.

4. **A-0.6 — show the derived roster read-out.** `TrainingRun::spiritBurstRoster()`
   (`app/Models/TrainingRun.php:1255`) already derives a roster from turn-event payloads and
   `TeamPanelBuilder` ships it (`app/Services/Career/TeamPanelBuilder.php:442-452`), but the cockpit's
   stats region does not surface a roster list beside the run's own team. Fix: a `roster` section on the
   cockpit that names every derived teammate and state; no column, the read-out is derived from
   `characters` + the run's deck/burst events per the ruling. Pin: a feature test asserting the roster
   ships and a browser case. Files: `app/Http/Controllers/Career/CockpitController.php`,
   `resources/js/pages/Career/Cockpit.vue`, `tests/Feature/CockpitRosterTest.php`,
   `tests/browser/career-cockpit-forms.spec.ts`.

5. **A-0.11 — show the single sourced mood figure.** The ±20% training / ±4% running figure is sourced in
   `docs/UMAMUSUME_REFERENCE.md` §1.1.6 but lives in no config table. Fix: one `config/uma.php` entry
   carrying the two figures with their citation, and `CareerStatePanel.vue` renders them beside the mood
   readout. Openable because it is a config entry, not a schema. Pin: a feature test asserting the
   section ships the figures and a browser case. Files: `config/uma.php`,
   `app/Http/Controllers/Career/CockpitController.php`,
   `resources/js/components/career/CareerStatePanel.vue`,
   `tests/Feature/CockpitMoodEffectTest.php`, `tests/browser/career-cockpit-forms.spec.ts`.

## 4. Rulings owed

Every blocked item — 7 `needs-schema`, 6 `needs-shape-decision` — is written up in
`docs/proposals/group-ab-rulings-owed.md`, one section each in the two-option form, with the
recommendation and the files the chosen option touches. The list:

- A-0.2, A-0.4, A-0.5, A-0.6, A-0.11 (Group A blocked)
- B-0.1, B-0.2, B-0.3, B-0.4, B-0.5, B-0.6, B-0.7, B-0.8 (Group B — all eight)

## 5. What will not be implemented in this pass

Seven items do not land as code in this pass and go to `KNOWN-ISSUES.md` in Phase 4, one entry each in
the register's `KI-nn` form: A-0.2 and B-0.8 as `ruled-out` (D8 catalog-only; loopback will-not-add);
A-0.4 as `blocked: A-0.5`; A-0.5, B-0.5, B-0.6 and B-0.7 as `blocked: ADR` (each needs a migration
preceded by an ADR). The three `landed` items (A-0.3, A-0.7, A-0.9) drop off work entirely — the audit
records their landing commits and nothing is re-litigated.

## 6. Verification plan

- **Phase 2 gate.** After the five openable slices land:
  `npm run typecheck`, `vendor/bin/pest tests/Feature/CockpitCapCompositionTest.php
  tests/Feature/CareerSkillsPlannerTest.php tests/Feature/CockpitRosterTest.php
  tests/Feature/CockpitMoodEffectTest.php`, and the browser specs
  (`career-cockpit-forms.spec.ts`, `career-skills-planner.spec.ts`).
- **Phase 4 gate.** `grep -cE "^### KI-" KNOWN-ISSUES.md` increases by 7.
- **Close-out.** `CHANGELOG.md` records a PATCH (read-only panels and copy only — no new endpoint,
  screen, or route); `VERSION.md` bumps if a release is cut; the pass report is
  `docs/audits/group-ab-pass-2026-10-24.md`.

## KNOWN-ISSUES filed (2026-10-24)

| Item | Entry | Status |
|---|---|---|
| A-0.2 | `KI-115` | ruled-out (catalog-only, D8) |
| A-0.4 | `KI-116` | blocked: A-0.5 |
| A-0.5 | `KI-117` | blocked: ADR |
| B-0.5 | `KI-118` | blocked: ADR |
| B-0.6 | `KI-119` | blocked: ADR |
| B-0.7 | `KI-120` | blocked: ADR |
| B-0.8 | `KI-121` | ruled-out (loopback will-not-add) |
| B-0.1 | `KI-122` | does not reproduce (shared rounds correct, `09:105`) |
| B-0.3 | `KI-123` | does not reproduce (snapshot form has the date) |

All entries live at the end of `KNOWN-ISSUES.md`.

## Definition of done

- Phase 0: `docs/audits/group-ab-audit-2026-10-24.md` exists with 19 classified items; no source file
  touched.
- Phase 1: this plan and `docs/proposals/group-ab-rulings-owed.md` exist; Phase 2 does not open until the
  owner says go.
- Phase 2: each openable slice lands its own commit with a feature test that fails at the previous
  commit and passes at its own, plus a browser case.
- Phase 4: every non-landed item is filed as a `KI-nn` entry.
- Phase 5: CHANGELOG entry, pass report.