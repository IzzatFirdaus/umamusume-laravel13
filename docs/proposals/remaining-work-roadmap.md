# Remaining work: 2.1.1 pending to 2.2.0

Recorded 2026-10-24 at `499e0105`. This is the on-disk form of the roadmap that had existed
only in review chat until now. It absorbs the corrections verified since the first draft:
the six frontend slices have landed (SHAs below), `python3` is not on this host, and the two
walk documents are tracked.

Binding conventions (every slice): named-path `git add` only, one commit per slice with a
test that fails at the previous commit, `pint --test` on explicit paths (never `--dirty`),
focused specs only (never the full suite), Playwright as a lock, `npm run build` before any
browser case, §11 for schema (migration + ADR + digest + PRD citation), and no push or tag
movement by any agent.

Companion: `docs/proposals/remaining-work-rulings-owed.md` (the eight rulings, with
evidence). No slice below opens before its ruling is recorded.

---

## Phase 0 — Owner rulings

| # | Blocks | Question | Recommendation |
|---|---|---|---|
| R4 | 1.1, 1.3, 1.6, 1.7, 1.8 | Accept ADR-0029? | Accept as drafted |
| R7 | 1.1 | Narrow US-12 or refuse A-0.5? | Dated travel note on US-12 |
| R8 | 1.4, 1.5 | PRD story for the team roster, or refuse? | Write it if KI-130 composes |
| R3 | 2.6 | Amend, restore, or retire the Scenario-region focus rule? | Amend `DESIGN.md` §5(a) |
| R1 | 2.4 | Partial payload, two keys, or refuse? | One partial payload |
| R2 | 2.5 | Copy-only or copy plus shortcut? | Copy-only |
| R5b | 3.1 | Fix the spec, exempt grids, or accept? | Fix the spec |
| R6 | 3.3 | DevTools: env gate, config, or mitigate? | Env gate |

Each ruling lands as a dated note beside its governing KI or document.

---

## Phase 1 — Schema unblock (ADR-0029 + eight slices)

### Slice 1.0 — Draft ADR-0029 — **landed `9c232066`** (`Status: Proposed`)

`docs/adr/0029-per-run-measurement-columns.md`, index row in `docs/adr/README.md`, digest
line in `ARCHITECTURE-ESSENTIALS.md`, contract block `decision: proposed`. The owner's
acceptance is R4; the agent does not flip the status.

### Slices 1.1–1.8 — blocked on R4 (and R7/R8 for 1.1/1.4/1.5)

| Slice | Item / KI | Storage | Test | Ruling |
|---|---|---|---|---|
| 1.1 | A-0.5 / KI-117 | `deck_slots.card_level` (numerator only) | `DeckSlotCardLevelTest` + deck spec | R4, R7 |
| 1.2 | A-0.4 / KI-116 | `deck_slots.reached_effects` (JSON) | `DeckAnalysisReachedEffectsTest` | R4 (after 1.1) |
| 1.3 | A-0.12 / KI-124 | `turn_entries.tile_banner`, `tile_card_avatars` | `TurnEntryTileMetadataTest` | R4 |
| 1.4 | A-0.13 / KI-125 | `training_runs.team_member_count*` or `team_roster` | `CockpitTeamPanelTest` case | R4, R8 |
| 1.5 | A-0.14 / KI-126 | `team_roster_member_skill` | `CockpitRosterTest` | R4, R8 |
| 1.6 | B-0.5 / KI-118 | `training_runs.team_stat_grades` (JSON) | `CockpitTeamPanelTest` case | R4 |
| 1.7 | B-0.6 / KI-119 | `training_runs.current_spirit*` | `CockpitUnityCupCountersTest` | R4 |
| 1.8 | B-0.7 / KI-120 | `skill_levels` | `SkillsPlannerLevelsTest` | R4 |

Each slice: one migration, model/request edits, one Vue surface, one feature case, one
browser case where the value is user-entered; gates per the header plus `migrate:status`
clean on the dev database. Files and payload shapes are in ADR-0029's decision table.

Phase close: close KI-116..KI-120 and KI-124..KI-126 with a dated note naming each commit;
`VERSION.md` to `2.2.0` (MINOR: new schema and surfaces).

---

## Phase 2 — Non-schema defect repair

### Slice 2.1 — KI-127 facility panel wrap — **landed `1119e437`**

`resources/js/pages/Career/Cockpit/Panels/TeamRaceFacilityPanel.vue` now `.card p-3` +
`CapsuleHeader`; per-facility `<h3>` groups unchanged. Pinned by the isolated fixture
`tests/browser/team-race-facility-panel.spec.ts` (the `sub-cell.spec.ts` pattern), because
the panel is a dead registration and does not compose live.

### Slice 2.2 — R4-31 priorities on re-save — **evidence recorded, no code**

Investigated this pass. `StoreBuildTargetRequest::isEmptyTarget()`
(`app/Http/Requests/StoreBuildTargetRequest.php:160-170`) ignores ceilings and priorities,
so a ceilings-plus-priorities save returns `payload() === null` and stores no target; the
next save posts the form's empty echo (`BuildTarget.vue:126,175`). The loss is silent, not
a 422, and is the same mechanism as R4-03. Closes with R1's slice 2.4; no separate fix.

### Slice 2.3 — Snapshot position label — **landed `499e0105`**

`app/Services/Career/CareerPositionLabel.php` is the one owner of the year word, the
Early/Late half and the month names. `DashboardController` and `RaceDecisionController`
call it (byte-identical output); `SnapshotController::review()` ships `position_label` and
`Review.vue` prints it. Pinned by `SnapshotReviewTest` (round-trip against the race header)
and one browser case. Finding: the Dashboard's label is built from the logged-turn count,
so a snapshot reads its position there as an entry count; filed for a later slice, not
fixed here.

### Slice 2.4 — S2b partial target — **blocked on R1**

Files: `StoreBuildTargetRequest::payload()`, `PreflightController`,
`SetupDraft::buildTarget()`, `CockpitController`, `Preflight.vue`;
`CareerBuildTargetTest`, `CareerPreflightTest`, `career-preflight.spec.ts`.

### Slice 2.5 — S11 rank-owner copy — **blocked on R2**

Files: `resources/js/components/career/UnityCupEditor.vue` help text; one assertion in
`CockpitTeamPanelTest` (a copy gate; no behavior change).

### Slice 2.6 — Scenario-region focus rule — **blocked on R3**

Per R3's direction: amend §5(a) + narrow `unity-cup-panel.spec.ts:134/:229`; or restore a
static `AbsenceValue` mode and migrate five call sites; or retire the guard with a dated
note. `UnityCupSurfacesTest` stays 11 passed in every case.

---

## Phase 3 — Triage and cleanup

### Slice 3.1 — `career-deck-select.spec.ts` layout red — **blocked on R5b**

Direction found: **spec stale**. The grid computes three columns
(`resources/css/app.css` has no grid rule); `.card + .card { margin-top: 14px }`
(`app.css:679-681`) shifts siblings 14px, so the spec's equal-top measurement
(`career-deck-select.spec.ts:23-36`) reports one per row. Fix is spec-only (R5b A); a CSS
exemption is Tier C (R5b B).

### Slice 3.2 — aptitude assertion — **landed `b0440ac6`**

Whole-grid: 10 `dt` / 10 `dd` across the three groups (`AptitudeGrid.vue:48-58`).

### Slice 3.3 — DevTools cache — **blocked on R6**

Provider identified: `inertiajs/inertia-laravel` 3.5.1 (`src/DevTools`), gate
`inertia.devtools.enabled` read from `INERTIA_DEVTOOLS_ENABLED`, defaulting to
`app()->environment('local')`. `config/inertia.php` is not published. R6 A: set the env var
for the browser role.

### Slice 3.4 — `DESIGN.md` citation — **landed `311820e4`**

`DESIGN.md:493` now cites `DesignTokensTest` + `BrowserSpecClassAssertionsTest`.

### Slice 3.5 — window-title race — **verify first, no ruling**

Browser re-read at HEAD; if it does not reproduce, close with a dated note. Do not open a
fix slice until the mechanism is understood.

---

## Phase 4 — Owner-gated follow-ups

- **KI-128** — `Delete this career` placement: `move to Run identity` or `leave`.
- **KI-129** — capsule uniqueness: `affirm` / `extend to all cards` / `remove from scenario`.
- **KI-130** — five dead scenario registrations: `compose` (recommended) or `retire`. The
  composition ruling also settles R8.

---

## Phase 5 — Release

- **2.1.1 push** — owner only. Pre-flight: `git ls-remote origin "refs/tags/2.1.0^{}"`,
  confirm the Release exists, confirm `CHANGELOG.md` covers every landed slice. Local tags
  at this HEAD: `2.1.1 → 1f6c911e`, `2.1.0 → 04c16a22`.
- **2.2.0 cut** — after Phase 1: `VERSION.md` MINOR, `CHANGELOG.md` section, pass report
  under `docs/audits/`, tag at the changelog commit. Owner pushes.

---

## Appendix — excluded items

| Item | Reason |
|---|---|
| `:67` assertion | Landed `0483f885` |
| Aptitude assertion (3.2) | Landed `b0440ac6` |
| `DESIGN.md` citation (3.4) | Landed `311820e4` |
| Facility panel wrap (A.1) | Landed `1119e437`; framing corrected `80ecced7` |
| KI-130 five dead registrations | Filed `ec740499`; ruling still owed |
| `ZzProbeOrderTest.php` | Tracked at `04c16a22`; harmless placeholder |
| Walk-4 and round-4 reconciliation docs | Tracked at `294144ca` (the first draft said untracked; corrected) |
| KI-115 / A-0.2 | Ruled out (catalogue authoritative) |
| KI-121 / B-0.8 | Ruled out (loopback will-not-add) |
| KI-122 / B-0.1 | Does not reproduce; register closed by dated note (this pass) |
| KI-123 / B-0.3 | Does not reproduce; register closed by dated note (this pass) |
| `career-trainee-select.spec.ts` R3-31 keyboard case | Unrelated red; not in this roadmap |
| Old tags (`0.1.0`, `2.0.1`, `2.0.2`) | Historical, immutable |

All `python3` commands in the first draft are `python` here: the host has Python 3.12 as
`python` and no `python3` on `PATH`.
