# Operational Cutover Procedure — TrainerDesk at localhost:8000

**Status**: Prepared for owner authorization. NOT executed.
**Target**: `D:\Projects\umamusume-laravel13` at `http://localhost:8000`
**Base commit**: `9c5c343ae86dc5c03060c78156a977cf19dab5a9`
**Integration workspace**: `D:\Projects\TrainerDesk-Recovery\recovery-workspace-20261125`, branch `recovery/integration`

## Phase 1: Preservation (Before Any Changes)

### 1.1 Preserve original uncommitted work

The original repository's working tree has 18 modified files and 274 untracked implementation files. These must be preserved before any cutover:

```bash
# In D:\Projects\umamusume-laravel13
git stash push -u -m "pre-cutover-owner-work-$(date +%Y%m%d-%H%M%S)"
# Creates stash with both tracked modifications and untracked files
# Verify: git stash list
```

Alternative: copy working tree to preservation directory:
```bash
robocopy "D:\Projects\umamusume-laravel13" "D:\Projects\trainerdesk-original-preservation-$(date +%Y%m%d-%H%M%S)" /E /COPYALL /XD .git vendor node_modules
```

### 1.2 Consistent database backup

Already completed: `D:\Projects\umamusume-laravel13\database\database.sqlite.backup-20261125-215656`
SHA-256: `e426cca629fe6e0414b6bc08756f39b53b1a733333a055453de8f4f5ed3dec57`
Verified: integrity ok, FK violations 0, training_runs=18, roster_members=0, umamusume=135

## Phase 2: Apply Changes to Original Repository

### 2.1 Deliver integration-only modified files (150 files)

Copy the following categories from integration workspace to original repo:

**Database safety (FS-INT-3):**
- `tools/agent-run.php` — unset operator auth env vars before isolation
- `app/Services/DatabaseSafety/DatabaseGuard.php` — agent marker check in `allowsCanonicalDestructive()`
- `app/Services/DatabaseSafety/AgentSessionGuard.php` — reject agent sessions in `isOperatorAuthorised()`

**Routes:**
- `routes/web.php` — add 3 roster CRUD routes (`runs.roster.store`, `runs.roster.update`, `runs.roster.destroy`)

**Controllers and models:**
- `app/Http/Controllers/Career/CockpitController.php` — merge roster section + preseasonRounds loading (see Phase 3 reconciliation)
- `app/Models/Umamusume.php` — model changes
- `database/factories/UmamusumeFactory.php` — factory updates
- `database/seeders/ScenarioSlotSeeder.php` — seeder updates

**Frontend:**
- `resources/js/components/career/TeamOverview.vue`
- `resources/js/components/career/TeamRoster.vue`
- `resources/js/pages/Career/TeamEdit.vue`
- `resources/js/pages/Career/Timeline.vue` (merge with original changes)
- `resources/js/pages/Veterans/Show.vue`
- `playwright.config.ts`
- 100+ browser test files

**Documentation:**
- `docs/adr/CURRENT.md`

**Do NOT use `git add -A` or blind filesystem sync.** Copy files individually with review.

### 2.2 Preserve original-only modified files (13 files)

These files are already modified in the original repo's working tree. Do NOT overwrite with integration versions:

- `app/Http/Controllers/Career/TimelineController.php`
- `app/Http/Controllers/DatabaseController.php`
- `app/Http/Controllers/TrainingRunController.php`
- `app/Http/Requests/StoreTrainingRunRequest.php`
- `app/Models/TrainingRun.php`
- `app/Services/Career/TeamPanelBuilder.php`
- `app/Services/Career/TrackblazerPanelBuilder.php`
- `database/seeders/RosyDreamsRiceShowerSeeder.php`
- `docs/audits/INDEX.md`
- `resources/js/components/career/UnityCupEditor.vue`
- `tests/Feature/CareerTimelineTest.php`
- `tests/Feature/CockpitTeamPanelTest.php`
- `_AGENTS_DO_NOT_CREATE_FOLDERS_OR_FILES_IN_THIS_DIRECTORY.txt`

### 2.3 Reconcile files modified in both workspaces (5 files)

**`app/Http/Controllers/CockpitController.php`:**
- Keep original: `preseasonRounds` in `loadMissing`/`load` calls, `status->recordsEntry()`
- Add integration: `RosterMember` import, `rosterMembers.umamusume, rosterMembers.supportCard` eager loading, `'roster' => $this->rosterSection($run)` in teamEdit response, rewritten `rosterSection()` returning RosterMember-based payload
- Result: Cockpit loads both preseasonRounds AND rosterMembers; teamEdit returns both team and roster data

**`resources/js/pages/Career/Timeline.vue`:**
- Compare original modification vs Frontend workspace modification
- Merge: keep original changes, add Frontend enhancements

**`tests/browser/career-timeline.spec.ts`, `tests/browser/database.spec.ts`, `tests/browser/team-edit.spec.ts`:**
- Same: compare and merge, do not discard either side's assertions

### 2.4 Add untracked implementation files from original repo

The original repo already has these untracked implementation files. They remain in place:

- `app/Models/RosterMember.php`, `app/Models/PreseasonRound.php`
- `app/Http/Controllers/Career/RosterMemberController.php`, `AdvisorAnalysisController.php`
- `app/Http/Requests/Career/StoreRosterMemberRequest.php`
- `app/Enums/RosterDistance.php`
- `app/Services/Career/CareerReference.php`, `VeteranPortfolio.php`
- All 7 migrations (roster_members, preseason_rounds, etc.)
- `database/factories/PreseasonRoundFactory.php`, `RosterMemberFactory.php`
- `database/seeders/RosyDreamsRiceShowerSeeder.php` (modified)
- `database/seeders/data/unity-cup-team-races.json`
- All Vue components, composables, services, config files
- All test files

### 2.5 Run additive migrations

On a scratch database first, then on the target:

```bash
php artisan migrate --database=sqlite
```

Migrations to run (in order):
1. `2026_11_24_120000_create_roster_members_table.php`
2. `2026_11_24_130000_add_is_fixture_to_umamusume_table.php`
3. `2026_11_24_130100_add_team_race_countdown.php`
4. `2026_11_24_140000_repair_roster_members_foreign_key.php`
5. `2026_11_24_150000_add_stat_caps_to_roster_members.php`
6. `2026_11_24_160000_add_origin_counts_aptitudes_to_roster.php`
7. `2026_11_25_120000_create_preseason_rounds_table.php`

All are additive (create or add columns), no drops. Safe on existing data.

### 2.6 Build production frontend assets

```bash
cd "D:\Projects\umamusume-laravel13"
npm ci
npm run build
```

Verify `public/build/` contains compiled assets with manifest.

### 2.7 Refresh caches

```bash
php artisan config:clear
php artisan route:clear
php artisan view:clear
```

## Phase 3: Verification (Before Cutover)

Run on scratch database at unused loopback port (NOT port 8000 yet):

```bash
# Bootstrap check
php artisan tinker --execute="echo app()->runningInConsole() ? 'console' : 'web';"

# Migration check
php artisan migrate:status

# Database safety
php tools/agent-run.php --migrate-fresh  # uses scratch DB, not canonical

# Browser identity preflight
# Verify mandatory preflight coverage spec runs
```

## Phase 4: Cutover (Owner Authorization Required)

**DO NOT EXECUTE WITHOUT OWNER AUTHORIZATION.**

1. Stop existing PHP process on port 8000 (if any)
2. Stop existing Vite dev server (if any)
3. Apply all Phase 2 changes to `D:\Projects\umamusume-laravel13`
4. Run migrations on target database
5. Build production assets
6. Refresh caches
7. Start `php artisan serve --host=127.0.0.1 --port=8000` from `D:\Projects\umamusume-laravel13`
8. Start Vite dev server or use built assets
9. Verify at `http://localhost:8000`

## Phase 5: Rollback Plan

### Code rollback
```bash
cd "D:\Projects\umamusume-laravel13"
git stash pop  # restores pre-cutover owner work
# Or: restore from preservation directory
```

### Database rollback
- Migrations are additive; rolling back drops new tables (`roster_members`, `preseason_rounds`) only
- Canonical data untouched (no data-modifying migrations)
- Restore from backup: `database.sqlite.backup-20261125-215656`

### Asset rollback
- Restore `public/build/` from pre-cutover state (if any existed)
- Vite dev server can be stopped and rebuilt

### Rollback verification
- `php artisan migrate:status` shows no pending migrations
- Application boots without errors
- Canonical database SHA-256 unchanged: `e426cca629fe6e0414b6bc08756f39b53b1a733333a055453de8f4f5ed3dec57`

## Authorization Request

The following operational changes are proposed for owner authorization:

| Change | Scope | Reversible |
|--------|-------|------------|
| Copy 150 integration-only modified files to original repo | Code | Yes (git stash/pop) |
| Preserve 13 original-only modified files | Code | N/A (already present) |
| Reconcile 5 both-modified files | Code | Yes |
| Run 7 additive migrations | Database | Yes (migrate:rollback drops new tables only) |
| Build production assets | Assets | Yes (rebuild) |
| Refresh caches | Runtime | Yes (clear) |
| Start PHP on port 8000 | Runtime | Yes (stop process) |

**No canonical database mutation** beyond additive schema changes.
**No data-seeding against canonical database.**
**No operational cutover without explicit owner authorization.**

## Files to Present for Authorization

1. `DELIVERY-MANIFEST.md` — complete file comparison
2. `SOURCE-BACKED-RECONSTRUCTION.md` — roster/career reconstruction mapping
3. `CUTOVER-PROCEDURE.md` — this document
4. `D:\Projects\trainerdesk-backend-preservation\PRESERVATION_MANIFEST.csv` — Backend preservation hashes
5. `D:\Projects\umamusume-laravel13\database\database.sqlite.backup-20261125-215656` — database backup (SHA-256 verified)