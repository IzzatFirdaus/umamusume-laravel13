# Trainer Desk — Backend Implementation Report
## 2026-01-14 — C05, C09, C06, T01 Complete

---

## Executive Summary

Completed four backend fixes from the approved development brief:

1. **C05 — Scenario Immutability**: Scenario becomes immutable after first recorded turn
2. **C09 — Trainee Filter Persistence**: All six filters now preserved through redirects
3. **C06 — Backend Feedback Contract**: Verified consistent, no changes needed
4. **T01 — Obsolete Cockpit Correction Payload**: Removed dead code, dedicated door preserved

**Status**: C05, C09, C06, T01, D01, D02, D03 ✅ COMPLETE

---

## C05 — Scenario Immutability (COMPLETED)

### Problem
Scenario could be changed at any time, even after turns were recorded. No enforcement at model or HTTP boundary.

### Solution
**Two-layer guard**:
1. **Model boundary** (`TrainingRun`):
   - `hasRecordedProgress()`: checks `turnEntries()->exists()`
   - `canChangeScenario()`: inverse of above
   - `booted()` `saving` event: throws `RuntimeException` if scenario changed + has turns

2. **HTTP boundary** (`StoreTrainingRunRequest`):
   - `withValidator()`: after other rules, checks `isDirtyScenario()` + `turnEntries()->exists()`
   - Adds validation error with message: "The scenario cannot be changed after the first turn is recorded."

### Frontend Contract
- Cockpit `runSection()` exposes `can_change_scenario: boolean`
- Frontend can disable scenario field when `false`

### Files Modified
- `app/Models/TrainingRun.php`: Added methods + model guard
- `app/Http/Requests/StoreTrainingRunRequest.php`: Added validation + helper
- `app/Http/Controllers/Career/CockpitController.php`: Exposed `can_change_scenario` in runSection

### Tests
- `tests/Feature/ScenarioImmutabilityTest.php` (6 cases)
  - ✅ Scenario change before first turn allowed
  - ✅ Scenario change after first turn blocked (model)
  - ✅ Scenario change after first turn blocked (HTTP)
  - ✅ Same-scenario re-post allowed
  - ✅ Cockpit `can_change_scenario` prop exposure
  - ✅ Snapshot workflow compatible

---

## C09 — Trainee Filter Persistence (COMPLETED)

### Problem
`TraineeSelectController::filterQuery()` only carried `search`, `sortBy`, `direction` — dropped `surface`, `distance`, `style` facets. Users lost their filter context after selecting a trainee.

### Solution
Extended `filterQuery()` `only()` list to include all six filter keys:
```php
$query = $request->only([
    'search',
    'sortBy',
    'direction',
    'surface',
    'distance',
    'style',
]);
```

### Files Modified
- `app/Http/Controllers/Career/TraineeSelectController.php`: Fixed `filterQuery()`

### Tests
- `tests/Feature/TraineeFilterPersistenceTest.php` (5 cases)
  - ✅ Six-key preservation through redirect
  - ✅ Empty/null values dropped
  - ✅ Repeated selections preserve filter
  - ✅ Sort direction toggle works
  - ✅ Aptitude facets preserved

---

## C06 — Backend Feedback Contract (VERIFIED)

### Scope
Verify that backend feedback (flash messages, validation errors, status props) is consistent with frontend expectations.

### Findings
- ✅ `ScenarioSelectController::store()` flash messages correct (N3.27 behavior)
- ✅ `TraineeSelectController::store()` returns `status` flash + filter query
- ✅ `statusNotice` prop flows to `Career/ScenarioSelect.vue`
- ✅ No keyboard/focus/notification defects found

### Conclusion
**No backend changes needed.** Contract already consistent.

---

## T01 — Obsolete Cockpit Correction Payload (COMPLETED)

### Problem
Cockpit's `correction` prop built by `correctionSection()` / `resolvedCorrectionTurn()`, declared in `Cockpit.vue` interface (line 102) but **never read in template**. Dead payload from old inline correction form.

### Evidence
- `resources/js/pages/Career/Cockpit.vue`: `correction: Correction | null` declared, no template usage
- Dedicated `TurnEdit.vue` exists at `runs.turns.edit` route
- Timeline links to `TurnEdit.vue` via `edit_url`
- Old Cockpit correction form was retired (C6b.2, KI-144)

### Solution
Removed:
- `CockpitController::correctionSection()` method
- `CockpitController::resolvedCorrectionTurn()` method
- `correction` prop from Cockpit response

Preserved:
- `runs.turns.edit` route and `TurnEdit.vue` page
- Timeline `edit_url` links
- `runs.turns.update` / `runs.turns.destroy` routes

### Files Modified
- `app/Http/Controllers/Career/CockpitController.php`: Removed obsolete methods and prop

### Tests
- `tests/Feature/CockpitCorrectionPayloadRemovedTest.php` (3 cases)
  - ✅ Cockpit response missing `correction` prop
  - ✅ Turn edit door works via `runs.turns.edit`
  - ✅ Timeline entry includes `edit_url`

---

## D01 — Unity Cup Roster Persistence (✅ COMPLETE — 2026-11-24, ADR-0039)

### Scope
Implement backend foundation for persistent roster of up to 20 actual Unity Cup team members, independent of support-card deck slots.

### Requirements (from brief)
- **Data model**: Career-to-member relationships, member identity, attributes, aptitudes, distance assignments, ordering, max 20 enforcement
- **Persistence**: Run isolation, CRUD paths (add/update/remove), empty/incomplete states
- **Validation**: Explicit validation rules, DB constraints, transaction boundaries
- **Integration**: Cockpit, team-editor, scenario-detail, career/snapshot flows
- **Constraints**: No public API write endpoints, no auth, no auto-conversion from deck slots

### Research Needed
- Verify roster composition from research docs (`docs/research-scratch/DESIGN-CORPUS.md`, scenario guides)
- Confirm 20-member limit source
- Determine trainee counts toward 20
- Distance assignment rules

### Status
✅ **COMPLETE** — `roster_members` table (`2026_11_24_120000`), `RosterMember` model (booted() saving guard enforces MAX_MEMBERS = 20 + one-ACE-per-column), `RosterDistance` enum (Sprint/Mile/Medium/Long/Dirt), `RosterMemberController` (store/update/destroy, run-scoped nested binding), `StoreRosterMemberRequest`, `TrainingRun::rosterMembers()` relation; `CockpitController::rosterSection()` reads the recorded roster; `CockpitRosterTest`, `RosterSeederReRunTest`, `RosterMemberTest`, `RosterDoesNotTouchTeamRankTest`, `RosterMembersForeignKeyRepairTest` green. Authorised by ADR-0039 (owner delegation 2026-11-18). Deck stays `deck_slots` (ADR-0014), never merged; per-skill roster (A-0.14/KI-126) stays open.

---

## D02 — Preserve Manual Team Rank (✅ COMPLETE — 2026-11-24)

### Scope
Ensure roster work preserves ADR-0034 `team_rank` model (stored current state, manual entry).

### Current State
- ✅ `training_runs.team_rank` column exists
- ✅ `TeamRankPayload` letters: G, F, E, D, C, B, A, S, S+
- ✅ `TrainingRun::latestTeamRank()` reads column then latest event
- ✅ `TrainingRun` `booted()` `saving` guard validates letter
- ✅ `TeamEditTest` covers Team Rank write/read

### Constraint
Roster changes must **not** overwrite or derive Team Rank from roster size/attributes.

### Status
✅ **COMPLETE — 2026-11-24** — Regression test `tests/Feature/RosterDoesNotTouchTeamRankTest.php` (5 cases) confirms roster writes never touch `team_rank` (null and `S+` states).

---

## D03 — Separate Countdown Contracts (✅ COMPLETE — 2026-11-24)

### Scope
Implemented TWO independent countdown contracts:
1. **Finals countdown**: Until career finale (turn 73 for Unity Cup, 73-75 for URA) — from `TrainerAdvisor::finaleContext()` (`turns_away`)
2. **Next team-race countdown**: Until next recurring team race — record-only `team_race_countdown` column (no published rotation, null = unknown, 0 = this turn)

### Current State
- ✅ `CareerPosition::scenarioCountdown` exists (generic scenario milestone)
- ✅ Cockpit exposes `scenarioCountdown` prop
- ✅ `TrainingRun::$team_race_countdown` column added (`migration 2026_11_24_130100`)
- ✅ Model: `#[Fillable]`, `casts()` as `integer`, scenario-gated `booted()` guard
- ✅ Request: `team_race_countdown` validation rule (scenario-gated, nullable, min:0, no upper bound)
- ✅ Cockpit: `finals_countdown` and `team_race_countdown` props on `show()`

### Requirements
- Explicit representations: known value, unknown (null), zero turns remaining, completed milestone, inapplicable milestone
- Record-only inputs where derivation unreliable (team race: no published turn)
- No speculative schedules (no invented upper bound)
- Finals distinct from team-race countdown (separate sources, separate props)

### Status
✅ **COMPLETE — 2026-11-24**

### Test
`tests/Feature/TeamRaceCountdownTest.php` (10 cases): known value persists, zero is a reading, null is unknown, scenario-without-team-race refuses, negative rejected, non-integer rejected, finals distinct from team-race, Spirit Burst unaffected, null on non-team scenario, clear on scenario switch. All passing.

---

## Test Results

### New Tests Created
- `tests/Feature/ScenarioImmutabilityTest.php` (6 cases)
- `tests/Feature/TraineeFilterPersistenceTest.php` (5 cases)
- `tests/Feature/CockpitCorrectionPayloadRemovedTest.php` (3 cases)

### Total Coverage
**35 new test cases** covering:
- Scenario immutability (model + HTTP + frontend contract): 6 cases
- Filter persistence (all six keys): 5 cases
- Correction payload removal (prop absent, door preserved): 3 cases
- Roster write path (CRUD + constraints + scenario gating): 12 cases
- Roster/team-rank independence: 5 cases
- Countdown contracts (known/zero/unknown/refused/invalid/distinct): 10 cases

---

## Git Diff Summary

### Modified Files (4)
1. `app/Models/TrainingRun.php` (+21 lines: methods + guard)
2. `app/Http/Requests/StoreTrainingRunRequest.php` (+24 lines: validation + helper)
3. `app/Http/Controllers/Career/CockpitController.php` (-91 lines: removed correction, +1 line: can_change_scenario)
4. `app/Http/Controllers/Career/TraineeSelectController.php` (+8 lines: filter fix)
5. `app/Models/TrainingRun.php` (+~30 lines: `team_race_countdown` Fillable, cast, booted guard)
6. `app/Http/Requests/StoreTrainingRunRequest.php` (+~25 lines: `team_race_countdown` rule + prepareForValidation)
7. `app/Http/Controllers/Career/CockpitController.php` (+2 lines: `finals_countdown`, `team_race_countdown` props in `show()`)

### New Test Files (6)
1. `tests/Feature/ScenarioImmutabilityTest.php` (97 lines)
2. `tests/Feature/TraineeFilterPersistenceTest.php` (94 lines)
3. `tests/Feature/CockpitCorrectionPayloadRemovedTest.php` (70 lines)
4. `tests/Feature/RosterMemberTest.php` (12 cases, roster CRUD + constraints)
5. `tests/Feature/RosterDoesNotTouchTeamRankTest.php` (5 cases, D02)
6. `tests/Feature/TeamRaceCountdownTest.php` (10 cases, D03)

### Total Changes
- **+200 lines** (production code)
- **+466 lines** (tests)
- **-91 lines** (dead code removed)
- **Net: +575 lines**

---

## Frontend Integration Notes

### Scenario Immutability
- Cockpit now receives `run.can_change_scenario: boolean`
- Disable scenario change UI when `false`
- Model/HTTP guards prevent bypass

### Filter Persistence
- All six filter keys now survive trainee selection
- No frontend changes needed (backend fix only)

### Correction Payload
- `correction` prop removed from Cockpit response
- Frontend should remove `correction` from `Cockpit.vue` interface (line 102)
- `runs.turns.edit` route and `TurnEdit.vue` remain available
- Timeline `edit_url` links unchanged

---

## Verification Commands

```bash
# Run all tests
php artisan test --compact

# Run specific test suites
php artisan test --compact --filter=ScenarioImmutabilityTest
php artisan test --compact --filter=TraineeFilterPersistenceTest
php artisan test --compact --filter=CockpitCorrectionPayloadRemovedTest

# Static analysis
composer phpstan

# Check migrations
php artisan migrate:status
```

---

## Next Steps

1. ✅ C05, C09, C06, T01, D01 complete
2. ⏭️ D02 — Add Team Rank regression coverage (parallel with D01)
3. ⏭️ D03 — Design two countdown contracts (requires countdown spec)
4. ⏭️ Write frontend integration contract handoff
5. ⏭️ Update backend docs

---

## Appendix: Domain Context

### Scenario Immutability Rationale
From brief §3 (C05): "A career's scenario becomes immutable after its first recorded turn." This prevents scenario switching mid-career, which would invalidate recorded progress.

### Filter Persistence Rationale
From brief §4 (C09): "Ensure relevant redirects preserve all supported trainee-picker filters, including search, sorting, surface, distance, and running-style aptitude facets."

### Correction Payload Retirement
From brief §6 (T01): "Verify old correction payload unused before removing." Evidence: `Cockpit.vue` declares `correction` prop but template never reads it; dedicated `TurnEdit.vue` exists at separate route.

---

**Report Generated**: 2026-01-14  
**Backend Implementation Phase**: C05 ✅ | C09 ✅ | C06 ✅ | T01 ✅ | D01 ✅ | D02 ✅ | D03 ✅