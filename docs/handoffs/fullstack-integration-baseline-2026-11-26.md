# Fullstack Integration Baseline — 2026-11-26

**To:** Coordinator (Senior Project Manager), Fullstack (integration accountability)
**From:** Senior Developer / Fullstack technical integration
**Mode:** Read-only evidence session. No commit, merge, push, reset, branch switch, hook bypass,
migration, or canonical-database mutation performed.

## 1. Actual commit and tree identities (measured 2026-11-26)

| Ref | Commit | Note |
|---|---|---|
| `master` | `8a9d4f8d` | Matches reported baseline. `origin/master` at `320421e7`; master ahead 10. |
| `fullstack` | `49ca49a3` | Matches reported baseline. Ahead of master 4, behind 0. |
| `backend` | `b6e5120e` at session start → `2ca76621` after peer commit | Matches reported baseline at start. Ahead 10/0. |
| `frontend` | `6bd24a95` | Matches reported baseline. Ahead 8/0. |
| `docs` | `9cc74e62` | Matches reported baseline. Ahead 8/0. |
| Common tree | `42120b30` | Verified as a tree object. |
| DB safety repair | `006734e2` | Present in master ancestry. |

Merge-base of every specialist branch with master = `8a9d4f8d`. Master-only commits = 0 for all
four: no divergence requiring conflict resolution, only unlanded specialist commits.

Active writer during session: Backend. `HEAD` switched `master` → `backend` at 08:16:36 +08:00 and
later advanced to `2ca76621` (`docs(handoffs): record D01 roster prop closure with executed
verification`). Working tree on that branch stayed clean apart from pre-existing untracked dirs.

## 2. Deliveries integrated and conflicts

No merge performed. Write authority was not held: shared checkout switched to Backend mid-session,
and `localhost:8000` shares the same physical repository. Switching branch or merging would disrupt
the active peer and the running server.

Reviewed deliveries (commit history, files, implementation):

- Backend `9e87eab2` — FS-INT-3 launcher sanitization, isolated-role protection, `teamEdit()`
  roster prop; `a2139044` restores CI workflows + CHANGELOG deleted by a historical recovery
  commit (correctly **not** propagated to master per operating model).
- Frontend `186eb10b` — bento team workspace, `CareerReferencePanel` on Timeline and Veterans
  detail, harness gates.
- Docs `06185d54` — ADR-0038/0039 restoration and D-8/D-9 register corrections.
- Fullstack `1d85c86c` — DB safety acceptance and regression checkpoint.
- All four branches share peer commits `1f52d6c7`, `5b29b10c`, `9c5c343a` (ADR-0027 correction
  revisions). No conflicting content detected in tree diffs.

Peer handoff landed this session: `docs/handoffs/backend-d01-roster-prop-closure.md` — D01 backend
gap closed at `9e87eab2`, re-verified 9 tests / 137 assertions at `b6e5120e`. Fullstack integration
of that commit remains pending Coordinator scheduling.

## 3. Four backend dependencies — status

| Dependency | Evidence | Disposition |
|---|---|---|
| `team.team_race_calendar` | 2 failing tests: `UnityCupTeamRaceSlotTest.php:157,183` — property absent from team payload | **Open, Backend**. The calendar omission from the earlier three-gap handoff is confirmed by executable failure. |
| `team_race_countdown` | 10 failing tests: `TeamRaceCountdownTest.php:39,59,73,89,104,119,134,151,167,184` — prop absent, validation errors absent, scenario switch does not clear value | **Open, Backend**. |
| `observed_member_count` | Migration `2026_11_24_160000_add_origin_counts_aptitudes_to_roster.php` adds nullable column on `training_runs`; rendered in `TeamOverview.vue`, `TeamRoster.vue`, `TeamEdit.vue`; `CockpitController::runSection()` carries it | **Resolved** — stored run-level attribute, not a missing response contract. Backend closure documented in `docs/UC-IMPLEMENTATION-RECONCILIATION-2026-11-25.md`. |
| `reference` on Timeline / Veteran details | Frontend panels present (`Timeline.vue:275`, `Veterans/Show.vue:138`, both `CareerReferencePanel`); no `reference` prop found in `app/Http/Controllers` on current tree | **Open, Backend** — controller payload side not located; needs definitive contract check before assignment closure. |

All four are recorded here for Coordinator assignment; the two confirmed missing contracts and the
unverified controller `reference` prop are Backend work. Do not close on file existence alone.

## 4. Verification executed (commands, exit codes, results)

Run on the live shared tree while it was checked out to Backend. Logs:
`.scratch-uma/integration-20261126/`.

| Command | Exit | Result |
|---|---:|---|
| `php artisan --version` | 0 | Laravel Framework 13.42.0 |
| `php artisan migrate:status` | 1 | Correct refusal: `DatabaseSafetyViolation` — agent session must isolate via `UMA_DATABASE_ROLE` + scratch `DB_DATABASE`. Canonical DB untouched. |
| `php artisan test --compact tests/Feature/DatabaseSafetyTest.php` | 0 | **29 passed (70 assertions)** — reproduces reported 29/29 |
| `php artisan test --compact tests/Feature/AgentSessionIsolationTest.php` | 0 | **7 passed (16 assertions)** — reproduces reported 7/7 |
| `php artisan test --compact` on DatabaseSafety, AgentSessionIsolation, TeamRaceCountdown, UnityCupTeamRaceSlot, CockpitTeamPanel | 1 | **59 passed, 12 failed (408 assertions)**. All 12 failures are the two missing backend contracts; no safety-suite regression. |
| `node_modules/.bin/vue-tsgo -p tsconfig.vue.json` | 0 | clean |
| `node_modules/.bin/tsc --noEmit` | 0 | clean |
| `node_modules/.bin/vite build` | 0 | built in 1m24s; manifest hash `spa-BQYfWbdx.js` |

Runtime: `127.0.0.1:8000` HTTP 200; PID 29736 = `php -S 127.0.0.1:8000` rooted at
`D:\Projects\umamusume-laravel13`; served HTML references the freshly built hash. HTTP 200 alone is
not deployment evidence; process identity and asset hash were checked together.

Historical evidence not reproduced: 75/75 five-suite regression and 2,843 passed / 54 failed
feature suite. No comprehensive qualification run was authorized during active peer writes.
The 54 failures remain historical and unresolved; the focused run above accounts for 12 concrete
failures, not all 54.

## 5. Remaining failure inventory (prioritised)

1. **Missing backend contracts (12 confirmed)** — `team_race_countdown` (10), `team_race_calendar`
   (2). Backend. Blocks Unity Cup team-race acceptance.
2. **Unverified `reference` payload** — frontend renders the panel; no controller prop located.
   Backend contract check. Could not be conclusively grepped because shell tooling degraded during
   the session; re-check on a stable tree.
3. **Historical 54 feature failures** — cause groups not established. Cannot be dismissed as
   environmental; requires authorized triage on a frozen tree.
4. **Comprehensive-run 1,973 failures at `11207c42`** — unresolved spectrum with 65 unstaged
   files at that time; per `docs/sitrep/sitrep-2026-11-25-verification-failure.md`, no classification
   without evidence. Historical, not a current-tree measurement.
5. **DB safety #1/#4/#5** — verified FAIL/PARTIAL in tracker; routed to Backend, none closed here.

## 6. Database safety acceptance — individual dispositions

Per `docs/sitrep/DB-SAFETY-ACCEPTANCE-2026-11-25.md` (authoritative tracker), unchanged by this
session:

- **#1 Connection lifecycle** — FAIL. Temporary `vendor/.../DatabaseManager.php` patch; combined
  suites previously failed in forward order. Backend.
- **#2 WorkBuddy environment compatibility** — PARTIAL. Fullstack verification recorded; Backend
  and Frontend environments not directly observed.
- **#3 Browser identity enforcement** — BLOCKED. Frontend implementation present, no executed
  negative browser evidence at review time. See `docs/handoffs/frontend-check3-browser-identity-evidence.md`.
- **#4 Maintenance authorization containment** — FAIL. `UMA_OPERATOR_ACKNOWLEDGED=yes` inheritance
  proven. Backend.
- **#5 Expected-database identity** — PARTIAL. Boot-time refusal works; runtime A→B switch after
  boot was granted. Backend.
- **#6 Source-backed factory / fixture classification** — PARTIAL. Docs provenance verified;
  Backend regression evidence outstanding.
- **#7 Documentation reconciliation** — PARTIAL. Cannot close until #1/#3/#4/#5 reports land and
  failure triage resolves. Docs.
- **#8 Persistent database preservation** — PASS as of 2026-11-25. **Not re-probed this session** —
  canonical probes are prohibited without separate authorization. Guard effectively refused an
  incidental canonical read (`migrate:status`, exit 1), which is positive evidence but not a
  substitute for the eight required checks.

No check was upgraded. Passing focused guard suites does not establish the whole architecture.

## 7. Canonical database and physical consolidation

Canonical DB: not read, not written, not migrated, not backed up this session. Guard refused the one
incidental access attempt. Genuine gameplay records (Rice Shower #48, run #54, roster observations)
untouched; no recovery seeders run.

Physical topology measured:

- `trainerdesk-backend`, `trainerdesk-frontend`, `trainerdesk-docs` — **absent** under `D:\Projects`.
- `git worktree list` — one entry only (main checkout); no registered linked worktrees.
- `D:\Projects\TrainerDesk-Recovery\recovery-workspace-20261125` — **still present**. Contains
  `.git` with unique refs: master/recovery/integration at `9c5c343a`, tags `0.1.0`, `2.0.1`,
  `2.0.2`, `2.1.0`, `2.1.1`, and
  `backup/backend/20261125T123228-backend-1795581148-worktree`; stash list empty.
- Authoritative repo has 10 stash entries. Unique Docs stash objects and recovery refs have **not**
  been proven preserved in the authoritative repo. Do not remove the recovery workspace until that
  preservation is verified by object-level comparison.
- Retain: `docs/sitrep/` incident archives, recovery manifests, verified DB backups, unrelated
  original source projects.
- Untracked in main checkout: `.headroom/`, `.recovery-2026-11-25/archives/`, and peer handoff docs.
  Left intact.
- Stale directories `.scratch-uma/worktrees/docs-corrections` and `.scratch-uma/worktrees/s18-release`
  exist but are not registered worktrees. Do not delete without content verification.

## 8. Write-authority handoff

**Current authority:** Backend (active writer, branch `backend`, advanced to `2ca76621` this session).
**Fullstack position:** technical integration ready but blocked; no branch switch or merge performed.
**Requested Coordinator action:**

1. Schedule an exclusive Fullstack write window: confirm Backend has released `backend` and that
   `localhost:8000` runtime is coordinated.
2. Direct Fullstack to merge verified Backend commits (`9e87eab2`, `a2139044`, `2ca76621`) into
   `master` after four-dependency re-check, then realign `backend`/`frontend`/`docs`/`fullstack`
   branches with master.
3. Assign the two confirmed missing contracts and the `reference` payload check to Backend before
   granting Frontend functional-acceptance authority.
4. Commission authorized failure triage for the historical 54/1,973 failures on a frozen tree.
5. Decide preservation verification method for `recovery-workspace-20261125` before authorizing its
   removal.

No competing roadmap, plan, or specialist implementation was created. No owner decision was
relaxed. No remote push, force operation, hook bypass, operational migration, canonical data
mutation, external provider activation, or comprehensive qualification was performed.
