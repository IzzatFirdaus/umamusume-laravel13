# TrainerDesk Operational Delivery Manifest

## Source and Target

- **Integration workspace**: `D:\Projects\TrainerDesk-Recovery\recovery-workspace-20261125`, branch `recovery/integration`, base `9c5c343a`
- **Original repository (target)**: `D:\Projects\umamusume-laravel13`, branch `master`, HEAD `320421e7`
- **Target runtime**: `http://localhost:8000` from `D:\Projects\umamusume-laravel13`
- **Canonical database**: `D:\Projects\umamusume-laravel13\database\database.sqlite` (read-only for inspection)

## Specialist Deliverables Integrated

All three specialist workspaces have been merged into the integration workspace:

| Workspace | Branch | Base | Modified Files | Untracked Files |
|-----------|--------|------|----------------|-----------------|
| Backend | `recovery/backend` | 9c5c343a | 16 (4 deleted, 11 modified, 1 untracked) | 1 |
| Frontend | `recovery/frontend` | 9c5c343a | 145 modified | 5 |
| Documentation | `recovery/docs` | 9c5c343a | 1 modified | 0 |

Integration workspace result: **154 modified files**, **6 untracked files** (implementation + test harness).

Preservation location: `D:\Projects\trainerdesk-backend-preservation` (base + modified versions + SHA-256 manifest).

## File Comparison: Integration vs Original Repository

### Files modified in integration only (150 files) — NEED TO BE DELIVERED TO ORIGINAL

These are the recovered implementation changes from specialist workspaces that are NOT yet in the original repository:

**Backend — Database safety (FS-INT-3 containment):**
- `tools/agent-run.php` — Unsets `UMA_OPERATOR_ACKNOWLEDGED` and `UMA_ALLOW_CANONICAL_DESTRUCTIVE` before isolation block (was `putenv($var.'=')`, fixed to `putenv($var)` for true unset)
- `app/Services/DatabaseSafety/DatabaseGuard.php` — Adds agent-session marker check in `allowsCanonicalDestructive()` so agent children cannot inherit operator authority
- `app/Services/DatabaseSafety/AgentSessionGuard.php` — Adds `if ($this->isAgentSession()) return false;` in `isOperatorAuthorised()`

**Backend — Routes and controllers:**
- `routes/web.php` — Adds Unity Cup roster CRUD routes: `POST /training-runs/{run}/roster`, `PUT /training-runs/{run}/roster/{member}`, `DELETE /training-runs/{run}/roster/{member}`
- `app/Http/Controllers/Career/CockpitController.php` — Roster section rewritten to use `RosterMember` model (recorded roster, not derived from deck slots); adds `rosterMembers.umamusume, rosterMembers.supportCard` eager loading; adds write URLs (store, update, destroy), `max_members`, `unrecorded` fields

**Backend — Models and factories:**
- `app/Models/Umamusume.php` — Model changes for roster/Unity Cup integration
- `database/factories/UmamusumeFactory.php` — Factory updates

**Backend — Seeders:**
- `database/seeders/ScenarioSlotSeeder.php` — Seeder updates

**Frontend — Vue components:**
- `resources/js/components/career/TeamOverview.vue` — Team overview panel
- `resources/js/components/career/TeamRoster.vue` — Team roster table
- `resources/js/pages/Career/TeamEdit.vue` — Team edit page
- `resources/js/pages/Career/Timeline.vue` — Career timeline page
- `resources/js/pages/Veterans/Show.vue` — Veteran detail page
- `playwright.config.ts` — Playwright configuration

**Frontend — Browser tests:** 100+ test files covering career workflows, roster, database, themes, responsive, etc.

**Documentation:**
- `docs/adr/CURRENT.md` — ADR-0038/0039 index entries

### Files modified in original repo only (13 files) — PRESERVE AS-IS

These are changes already in the original repository's working tree that are NOT in the integration workspace:

1. `_AGENTS_DO_NOT_CREATE_FOLDERS_OR_FILES_IN_THIS_DIRECTORY.txt`
2. `app/Http/Controllers/Career/TimelineController.php`
3. `app/Http/Controllers/DatabaseController.php`
4. `app/Http/Controllers/TrainingRunController.php`
5. `app/Http/Requests/StoreTrainingRunRequest.php`
6. `app/Models/TrainingRun.php`
7. `app/Services/Career/TeamPanelBuilder.php`
8. `app/Services/Career/TrackblazerPanelBuilder.php`
9. `database/seeders/RosyDreamsRiceShowerSeeder.php`
10. `docs/audits/INDEX.md`
11. `resources/js/components/career/UnityCupEditor.vue`
12. `tests/Feature/CareerTimelineTest.php`
13. `tests/Feature/CockpitTeamPanelTest.php`

### Files modified in BOTH workspaces (5 files) — SEMANTIC RECONCILIATION REQUIRED

| File | Original Repo Change | Integration Workspace Change | Reconciliation |
|------|---------------------|------------------------------|----------------|
| `app/Http/Controllers/Career/CockpitController.php` | Adds `preseasonRounds` to `loadMissing`/`load` calls; null-safe `status?->recordsEntry()` → `status->recordsEntry()` | Adds `RosterMember` import; adds `rosterMembers.umamusume, rosterMembers.supportCard` eager loading; adds `'roster' => $this->rosterSection($run)` to teamEdit; rewrites `rosterSection()` to return RosterMember-based payload | Merge: keep preseasonRounds loading + add roster section + rosterMember eager loading |
| `resources/js/pages/Career/Timeline.vue` | Original repo modification | Frontend workspace modification | Compare and merge |
| `tests/browser/career-timeline.spec.ts` | Original repo modification | Frontend workspace modification | Compare and merge |
| `tests/browser/database.spec.ts` | Original repo modification | Frontend workspace modification | Compare and merge |
| `tests/browser/team-edit.spec.ts` | Original repo modification | Frontend workspace modification | Compare and merge |

### Files in original repo's untracked set (implementation files) — ALREADY PRESENT

The following implementation files exist in the original repo's untracked set and are ALSO present in the integration workspace:

**Models:** `app/Models/RosterMember.php`, `app/Models/PreseasonRound.php`

**Controllers:** `app/Http/Controllers/Career/RosterMemberController.php`, `app/Http/Controllers/Career/AdvisorAnalysisController.php`

**Requests:** `app/Http/Requests/Career/StoreRosterMemberRequest.php`

**Enums:** `app/Enums/RosterDistance.php`

**Services:** `app/Services/Career/CareerReference.php`, `app/Services/Career/VeteranPortfolio.php`

**Migrations:**
- `2026_11_24_120000_create_roster_members_table.php`
- `2026_11_24_130000_add_is_fixture_to_umamusume_table.php`
- `2026_11_24_130100_add_team_race_countdown.php`
- `2026_11_24_140000_repair_roster_members_foreign_key.php`
- `2026_11_24_150000_add_stat_caps_to_roster_members.php`
- `2026_11_24_160000_add_origin_counts_aptitudes_to_roster.php`
- `2026_11_25_120000_create_preseason_rounds_table.php`

**Factories:** `database/factories/PreseasonRoundFactory.php`, `database/factories/RosterMemberFactory.php`

**Seeders:** `database/seeders/data/unity-cup-team-races.json`, `database/seeders/RosyDreamsRiceShowerSeeder.php` (modified)

**Vue components:** `TeamFormation.vue`, `TeamGradeStrip.vue`, `UnityCupRoster.vue`, `VeteranPortfolio.vue`, `CareerReferencePanel.vue`, `AdvisorAnalysisPanel.vue`, `RosterTableRows.ts`, `ErrorSummary.vue`

**Composables/Services:** `useAdvisorAnalysis.ts`, `useDialogLightDismiss.ts`, `advisor/analysis.ts`

**Config:** `config/advisor_ai.php`, `config/imported_career_reference.php`

**Tests:** 50+ feature and browser test files for roster, timeline, advisor, preseason, etc.

## Missing Routes

The integration workspace adds 3 roster CRUD routes to `routes/web.php` that are NOT in the original repo:

```php
Route::post('/training-runs/{run}/roster', [RosterMemberController::class, 'store'])->name('runs.roster.store');
Route::put('/training-runs/{run}/roster/{member}', [RosterMemberController::class, 'update'])->name('runs.roster.update');
Route::delete('/training-runs/{run}/roster/{member}', [RosterMemberController::class, 'destroy'])->name('runs.roster.destroy');
```

These routes are needed for the Unity Cup Team Management workspace (D01). The `RosterMemberController` already exists in the original repo's untracked set.

## Missing Frontend Contracts

The integration workspace includes these frontend files not yet in the original repo's committed state:

- `resources/js/components/career/TeamOverview.vue`
- `resources/js/components/career/TeamRoster.vue`
- `resources/js/pages/Career/TeamEdit.vue`
- `resources/js/pages/Career/Timeline.vue` (modified in both — needs reconciliation)
- `resources/js/pages/Veterans/Show.vue`

Plus 100+ browser test files covering the full career workflow.

## Required Migrations

The following additive migrations exist in the original repo's untracked set and are in the integration workspace. They must be run in order:

1. `2026_11_24_120000_create_roster_members_table.php` — Creates `roster_members` table
2. `2026_11_24_130000_add_is_fixture_to_umamusume_table.php`
3. `2026_11_24_130100_add_team_race_countdown.php`
4. `2026_11_24_140000_repair_roster_members_foreign_key.php`
5. `2026_11_24_150000_add_stat_caps_to_roster_members.php`
6. `2026_11_24_160000_add_origin_counts_aptitudes_to_roster.php`
7. `2026_11_25_120000_create_preseason_rounds_table.php` — Creates `preseason_rounds` table

All are additive (no column drops), safe to run on existing database.

## Configuration Compatibility

**Config files in original repo's untracked set:**
- `config/advisor_ai.php` — AI Advisor optional read-only layer (ADR-0038)
- `config/imported_career_reference.php` — Career reference configuration

**Database safety config:** `config/database-safety.php` exists in both workspaces. The integration workspace's `DatabaseGuard` reads from this config.

**No config conflicts detected.** All config files are additive.

## Existing Owner Changes to Preserve

The original repo's working tree has 18 modified files and 429 untracked files (including 274 filtered implementation files). These must NOT be overwritten:

- `app/Http/Controllers/Career/CockpitController.php` — Modified (preseasonRounds loading)
- `resources/js/pages/Career/Timeline.vue` — Modified
- `database/seeders/RosyDreamsRiceShowerSeeder.php` — Modified (255 lines, Rice Shower Unity Cup source-backed seeder)
- All untracked implementation files listed above

## Database State

**Canonical database**: `D:\Projects\umamusume-laravel13\database\database.sqlite`
- 38 tables, integrity OK, FK violations: 0
- `training_runs`: 18 (9 active, 9 completed)
- `roster_members`: 0 (baseline 18 — missing, needs source-backed reconstruction)
- `umamusume`: 135, `veterans`: 8, `character_cards`: 263
- Run #48 NOT FOUND. Run #54 exists (Rice Shower Unity Cup, Active)

**Backup file**: `database.sqlite.bak-before-rosy-reseed` (36 tables, no roster_members, no run #48/#54, has run #14 Rice Shower) — NOT a historical source for career #48 or roster.

**Required**: Fresh consistent backup with SHA-256 before cutover.

## Source-Backed Reconstruction Available

`database/seeders/RosyDreamsRiceShowerSeeder.php` contains complete source-backed data from `docs/[Rosy_Dreams]Rice_Shower_Unity-Cup.md` (68 client frames, 2026-10-03):

- Rice Shower career run (um_id=30, card_id=31, scenario=unity_cup, status=Active, turn 67 of 72)
- 18 roster members with full stat lines, grades, aptitudes, origins
- 6 deck slots with support card IDs
- 5 race entries (Nikkei Sho, Tenno Sho Spring, Takarazuka Kinen, Kyoto Daishoten, Arima Kinen)
- 11 skills
- 2 turn events at turn 67
- Legacy selection (Vodka + Maruzensky parents)

This seeder is idempotent by `import_source` and can be run on an isolated database for reconstruction without touching the canonical database.

## Next Steps (Prerequisites Before Cutover Authorization)

1. [ ] Reconcile 5 files modified in both workspaces (CockpitController.php, Timeline.vue, 3 test files)
2. [ ] Deliver 150 integration-only modified files to original repo
3. [ ] Preserve 13 original-only modified files (already in original repo)
4. [ ] Create fresh consistent database backup with SHA-256
5. [ ] Run additive migrations on scratch database (not canonical)
6. [ ] Build production frontend assets (Vite)
7. [ ] Verify application bootstrap on scratch database at unused loopback port
8. [ ] Present cutover authorization request to owner