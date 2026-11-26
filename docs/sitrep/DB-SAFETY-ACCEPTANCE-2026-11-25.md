# Database Safety — Eight-Check Acceptance Tracker (2026-11-25)

**Single authoritative tracker.** Owner: Fullstack Senior Developer (integration accountability).
This file preserves all eight acceptance criteria in a durable, repository-owned record and records
the evidence used to accept each check. Use PASS / PARTIAL / FAIL / BLOCKED only with evidence.

**Origin:** `# TrainerDesk — Cross-Agent Database Safety Integration and Final Acceptance` (owner task,
2026-11-25). Criteria quoted below are the authoritative text for this remediation.

## Ownership

| Check | Responsible agent |
|---|---|
| #1 | Backend Architect |
| #2 | Fullstack Senior Developer |
| #3 | Frontend Developer |
| #4 | Backend Architect |
| #5 | Backend Architect |
| #6 | Backend Architect (+ Docs source-provenance review) |
| #7 | Technical Documentation Engineer |
| #8 | Fullstack Senior Developer |

## Status (updated as evidence lands)

| Check | Status | Evidence location | Remaining limitation |
|---|---|---|---|
| #1 | PASS | This tracker §Integration verification record 2026-11-26 (master `1cb070f7`); `vendor/.../DatabaseManager.php` has zero `DatabaseGuard`/`assertWriteSafe` matches (pristine); combined forward-order run 87 passed / 581 assertions | None found at this tree. The temporary vendor patch is gone; `SafeDatabaseManager` is the bound `db` service and a refused connection is purged (probe P5: reconnect after refusal = REFUSED) |
| #2 | PARTIAL | This tracker §Check #2 verification record; `docs/sitrep/DB-SAFETY-DOCS-RECONCILIATION-2026-11-25.md` §Check #2 | Backend and Frontend runtime environments not directly observed by this session (no verified access to their sessions); maintenance auth leak narrowed to the env-precedence limitation now recorded under #4 |
| #3 | BLOCKED | Static review of `tests/browser/server-router.php`, `tests/browser/browser-isolation-contract.spec.ts`, `tests/browser/support/server-identity.ts`; `docs/handoffs/frontend-check3-browser-identity-evidence.md` (executed at `b6e5120e`) | Evidence exists but predates the integrated safety implementation (`6aab6503` runtime identity re-check). **Must be re-verified by Frontend against master `1cb070f7`.** Not carried forward as unconditional |
| #4 | PARTIAL | This tracker §Integration verification record 2026-11-26 (probes L1–L3) | Markerless isolated role + inherited ack on canonical re-target is now **REFUSED** (L3). Remaining: `DatabaseGuard::environment()` reads `$_SERVER` → `$_ENV` → `getenv()`, so a caller that sanitizes only `putenv()` leaves a stale `$_SERVER` value winning (L1 = `yes`). `tools/agent-run.php` clears all three, so the launcher path is closed; any other sanitizer must do the same. **Task #12 disposition (2026-11-26):** conditionally accepted pending Backend resubmit — Fullstack msg#44 REJECTED Backend msg#42 (probe still VACUUMs genuine canonical; requires independently generated synthetic-only fixture + discriminating matrix). Coordinator msg#43 maintains rejected state. Backend msg#49 RESUBMITTED synthetic-only (no genuine `database.sqlite`, no VACUUM; `makeSyntheticDb` per-run uniqid temp; AgentSessionIsolationTest 7/15 + DatabaseSafetyTest 47/92 green; Pint+PHPStan clean) — acceptance_status=synthetic_only_verified_green, AWAITING Fullstack verification+commit+integrate. FS-INT-3 closed; L1 impl-correct (msg#31 audit); acceptance remains BLOCKED until Fullstack verifies msg#49. |
| #5 | PASS | This tracker §Integration verification record 2026-11-26 (probe P3–P5, isolated role, disposable scratch DBs) | Boot with expected=A GRANTED; post-boot switch to B REFUSED (`DatabaseSafetyViolation`); reconnect still REFUSED. Limitation (source-level, not a defect at this tree): a connection resolved *before* the role became isolated is cached and not re-checked (L2) |
| #6 | PASS | This tracker §Check #6 (PASS); `tests/Feature/Check6FixtureClassificationTest.php`; master `0f58aefb` (restored `b9a714f2`) | All three gate conditions met: test committed + frozen (Backend `82786761`) + integrated (Fullstack msg#30, master `0f58aefb`). Functional acceptance per Fullstack msg#32 (35 passed/343). Historical 28-name claim preserved unsupported (no mandatory curated pool — Coordinator disposition 2026-11-26). Docs merge incident `23581aa2` dropped the code; restored by `57faa9de`/`b9a714f2` (code diff vs `6e17dca0` empty). |
| #7 | PARTIAL | `docs/sitrep/DB-SAFETY-DOCS-RECONCILIATION-2026-11-25.md`; AGENTS.md §11 (change-safety; §13 is UI/UX, no §13.5 exists); `docs/INDEX.md`; this tracker | #1/#4/#5 now have Fullstack integration evidence attached; #3 re-verification and the full-suite 1,973-failure classification remain open. Not a blanket prerequisite for all checks |
| #8 | PASS | This tracker §Check #8 verification record (fresh read-only probe output) | Structural preservation only — counts and identity match the 2026-11-25 backup (`umamusume`=135, `training_runs`=18, `roster_members`=0, `character_cards`=263, `race_entries`=39, `veterans`=8, `integrity_check`=ok, `foreign_key_check`=0). **Not** evidence of genuine gameplay restoration; AGENTS.md has no DB-safety section with pinned counts (§11 is change-safety, §13 is UI/UX); the pinned-count wording does not exist (routed to Docs #7, FS-INT-6 closed as not-a-defect) |

**Status vocabulary (2026-11-25):** `PENDING` = not yet worked; `PARTIAL` = some verified evidence landed, named blockers remain; `PASS` = all required evidence green and cited; `FAIL` = verified defect; `BLOCKED` = cannot proceed without owner/peer action. A check is never marked `PASS` on documentation alone or on the existence of a config/test file — only on executed, cited evidence. #7 is `PARTIAL`: documentation reconciliation landed, but #7 cannot close until Backend (#1/#4/#5) and Frontend (#3) implementation reports are attached and the full-suite failure triage is resolved.

---

## Check #1 — Connection lifecycle protection (Backend Architect)

Verify exact Laravel version, pristine vendor comparison, durable connection lifecycle protection and
permanent regression coverage. Confirm no temporary vendor patch remains. Verify that the combined
safety regression suites pass.

## Check #2 — WorkBuddy environment compatibility (Fullstack Senior Developer)

Verify actual execution environments for Fullstack — Senior Developer, Backend — Backend Architect,
Frontend — Frontend Developer, and Documentation — Technical Documentation Engineer, including
relevant child processes. Determine whether `CLAUDE_SESSION_ID` and `CODEBUDDY_SESSION_ID` adequately
identify the actual execution environments. Coordinate minimal session-local diagnostic requests with
each agent when necessary. Verify:

- `composer agent -- <script>`
- `composer agent:tinker`
- Session-private database creation
- Concurrent session separation
- Safe refusal when recognized agent processes omit isolated roles
- Expected database identity propagation
- Appropriate maintenance environment-variable sanitization
- Child-process inheritance behaviour

Do not infer that all sessions behave identically merely because they share the same IDE. Do not
expose secrets or unrestricted environment dumps. Record actual verified environments and limitations.

## Check #3 — Browser identity enforcement (Frontend Developer)

Verify actual browser server and database identity enforcement. Confirm negative regression tests
demonstrate safe refusal of incorrect identities before mutating test requests.

## Check #4 — Maintenance authorization containment (Backend Architect)

Verify maintenance authorization does not accidentally leak into ordinary isolated workflows. Confirm
legitimate approved maintenance remains supported.

## Check #5 — Executable expected-database identity (Backend Architect)

Verify expected database identity is executable protection rather than instruction-only guidance.
Require evidence that a process assigned scratch database A is refused access to scratch database B.

## Check #6 — Source-backed factory and fixture classification (Backend Architect + Docs)

Verify source-backed factory regression protection and fixture classification. Review the Docs
agent's provenance findings.

### Resolved requirement (Coordinator, 2026-11-26) — status still PARTIAL

No accepted mandatory curated pool of 28 genuine Global-eligible fixture identities has been established. The authoritative coordination disposition is:

- Factory rows default to `is_fixture=true`.
- `catalogue()` explicitly opts into `is_fixture=false`.
- `is_manual` remains independently classified.
- Synthetic fixtures remain distinguishable from genuine source-backed catalogue rows.
- Fixtures remain excluded from applicable catalogue queries and user-facing collections.
- Global eligibility is supported by applicable source records.
- Current focused executable evidence remains required.

Dataset-level source availability is distinct from individual character and card-variant eligibility. The original historical 28-name assertion is preserved as an unsupported historical claim; no replacement identity pool is invented.

**Executable evidence (Fullstack, 2026-11-26, msg#2):** Backend's `tests/Feature/Check6FixtureClassificationTest.php` (183 lines, 8 tests) run in the backend worktree @ `d9bf146b`: **8 passed / 29 assertions**. Coverage: `is_fixture` default true, `catalogue()` opt-out, `is_manual` independence, fixture exclusion from API v1 + user collections, `isFixture` resource flag, `release_status` eligibility, identity-vs-card-variant. Status: **PASS** — Backend committed + froze file (commit `82786761`, 9 files), Fullstack integrated to master `0f58aefb` (msg#30). All three gate conditions met. No replacement identity pool invented; historical 28-name claim preserved unsupported.

## Check #7 — Documentation reconciliation (Technical Documentation Engineer)

Verify the reconciled documentation accurately reflects implemented and tested behaviour. Confirm all
eight acceptance criteria have been preserved in a durable repository-owned record.

## Check #8 — Persistent database preservation (Fullstack Senior Developer)

Perform fresh read-only verification of the canonical TrainerDesk SQLite database using the approved
safety procedure. Confirm:

- Previously identified synthetic trainees #141–145 remain absent.
- Previously identified synthetic careers #49–51 remain absent.
- Genuine training careers remain preserved.
- Rice Shower's Unity Cup career #48 remains intact.
- Existing Veterans remain preserved, accounting for legitimate owner activity.
- Recovery backups remain available.
- SQLite integrity checks pass.
- SQLite foreign-key checks pass.
- No new synthetic fixture contamination is identified.

Historical comparison baseline (historical evidence, **not** permanent invariants):

| Record type | Previous count |
|---|---:|
| Training runs | 18 |
| Active runs | 9 |
| Completed runs | 9 |
| Veterans | 8 |
| Umamusume records | 135 |
| Character cards | 263 |
| Roster members | 18 |
| Umamusume aliases | 2 |
| Umamusume profiles | 96 |

Recovery backups:

- `.scratch-uma/senior-delete-repair/before-repair.sqlite`
- `.scratch-uma/fullstack-guard/pre-delete-141-145.sqlite`

Investigate differences using actual record identities and available provenance. Do not modify, delete
or restore persistent records without explicit owner authorization. Ensure automated regression tests
do not depend on fixed canonical database record counts.

---

## Integration constraints (binding)

- Review actual implementation and regression evidence; do not accept completion claims without evidence.
- Do not repeatedly run the entire project test suite during active concurrent changes.
- Do not unnecessarily rebuild frontend assets.
- If combined verification fails, classify failures with reproducible evidence and route to the owning agent.
- Do not dismiss failures as pre-existing without controlled evidence.
- Preserve concurrent changes; avoid competing implementations.
- Do not modify Windows accounts or filesystem ACLs; no destructive Git operations; no commit/push/deploy.
- Canonical database contains genuine personal gameplay records — never disposable.

---

## Check #2 verification record (Fullstack Senior Developer, 2026-11-25)

**Method.** Session-local probes only; presence checks report names/prefixes, never secret values.
All probes read data through a scratch copy or a refused connection; **no canonical write was issued**.
Probe scripts live under `.scratch-uma/` (gitignored scratch), not in tracked source.

**Actual environments directly observed by this session:**

| Role | Marker observation | Role / ack defaults | Source |
|---|---|---|---|
| Fullstack — Senior Developer (this session) | Both `CLAUDE_SESSION_ID` and `CODEBUDDY_SESSION_ID` SET | `UMA_DATABASE_ROLE`, `UMA_EXPECTED_DATABASE`, `UMA_DATABASE_ROLE`, `UMA_OPERATOR_ACKNOWLEDGED`, `UMA_ALLOW_CANONICAL_DESTRUCTIVE`, `DB_DATABASE` all UNSET | Direct environment presence probe in this shell (names only) |
| Documentation — Technical Documentation Engineer | Both markers SET (prefix `98ca019e`); child `bash -c` inherits `CLAUDE_SESSION_ID`; no role/DB isolation by default | No isolation declared by default | Docs agent report, `DB-SAFETY-DOCS-RECONCILIATION-2026-11-25.md` §Check #2 (peer-reported, not independently observed here) |
| Backend — Backend Architect | **Not directly observed** | Unknown | No verified runtime access to that session; do not infer from shared IDE |
| Frontend — Frontend Developer | **Not directly observed** | Unknown | No verified runtime access to that session; do not infer from shared IDE |

**Adequacy of markers.** Both markers are present in the two observed agent sessions and are the
trigger used by `AgentSessionGuard::isAgentSession()`. A bare process with markers, no isolated role
and canonical target was **refused** (`REFUSAL_PROBE=REFUSED App\Exceptions\DatabaseSafetyViolation`),
so markers adequately identify an agent process for the fail-safe. They do **not** identify which
*role* is running — the guard deliberately treats all marked processes identically. Both markers
being set is harmless (`isAgentSession()` short-circuits on the first match).

**Verified launcher behaviour** (all executed, not inferred from config):

| Item | Evidence |
|---|---|
| `composer agent -- <script>` | Ran successfully; seeded `session-3448b2b7.sqlite` from a read-only `VACUUM INTO` snapshot; printed `PARENT_SESSION_DB=session-3448b2b7.sqlite`, `PARENT_ROLE=test`, `PARENT_EXPECTED=session-3448b2b7.sqlite`, 135 rows read from the copy |
| `composer agent:tinker` | Launched with isolated session banner and exited 0 on piped `exit` |
| Session-private DB creation | Observed above; file under `.scratch-uma/agent-sessions/session-<id>.sqlite` |
| Concurrent session separation | Two processes with distinct `CLAUDE_SESSION_ID` values produced `session-probeAAA.sqlite` and `session-probeBBB.sqlite`; expected identity matched each; child `DB_DATABASE` matched its own parent |
| Safe refusal without isolated role | Bare agent-style process, markers present, canonical target, no role and no ack → `DatabaseSafetyViolation` |
| Expected DB identity propagation | `UMA_EXPECTED_DATABASE` set to the same session path in parent and child; `uma:db-guard` pre-flight runs before the payload |
| Child-process inheritance | Child `php` inherited both markers, `UMA_DATABASE_ROLE=test`, `UMA_EXPECTED_DATABASE` and `DB_DATABASE` pointing at the session copy |
| Maintenance env sanitization | **FAIL — defect found.** `UMA_OPERATOR_ACKNOWLEDGED=yes` and `UMA_ALLOW_CANONICAL_DESTRUCTIVE=true` set in a maintenance parent are inherited unchanged by `composer agent` and its children. Impact proven: inside the isolated launcher, re-pointing config to canonical and issuing a read-only count was `REFUSED` without the inherited ack but `CONNECTION_GRANTED count=135` with it. The launcher does not sanitize maintenance authorization before establishing isolation. Routed to Backend Architect under Check #4 (maintenance authorization containment); this session did **not** implement the fix to avoid competing work. |

**Limitations.** Backend/Frontend runtime environments were not directly accessed; only the Docs
session is peer-reported. No unrestricted environment dump was captured or stored. The marker pair
identifies "agent process", not a specific agent role, and is inherited by children by design —
sanitization of maintenance authorization is required at the isolation boundary.

---

## Check #8 verification record (Fullstack Senior Developer, 2026-11-25)

**Method.** Fresh read-only verification in a subprocess using the approved operator-acknowledged
path (`UMA_OPERATOR_ACKNOWLEDGED=yes`, explicit canonical `DB_DATABASE`, no isolated role). No
INSERT/UPDATE/DELETE/DDL was issued. Backup files were opened with `PDO::SQLITE_OPEN_READONLY`.
Probe scripts: `.scratch-uma/acceptance-db-preservation.php` and `-2.php`.

**Counts vs historical baseline (all exact matches):**

| Record type | Baseline | Observed |
|---|---:|---:|
| Umamusume records | 135 | 135 |
| Training runs | 18 | 18 |
| Active runs | 9 | 9 |
| Completed runs | 9 | 9 |
| Veterans | 8 | 8 |
| Character cards | 263 | 263 |
| Roster members | 18 | 18 |
| Umamusume aliases | 2 | 2 |
| Umamusume profiles | 96 | 96 |

**Identity checks.**

- Synthetic trainees #141–145: **ABSENT** (id range 141–145 and slugs `id-et`, `consequatur-ea` queried; zero rows).
- Synthetic careers #49–51: **ABSENT** (`run_ids_absent=49,50,51`).
- Run #48: **intact** — `id=48, uma=30, scenario=unity_cup, status=Active, source=docs/[Rosy_Dreams]Rice_Shower_Unity-Cup.md (Run Rosy Dreams Rice Shower)`.
- Trainee 30: `Rice Shower / rice-shower / ライスシャワー`, `fixture=0`, `manual=0` — genuine catalogue identity.
- Veterans 1–8 all present.
- Contamination: `fixture_trainees=0`, `name_ja_null=0`, `synthetic_named_trainees=0`, suspicious slug/name patterns = 0.

**Backups (read-only):**

| Backup | Present | Bytes | integrity_check | foreign_key_check | umamusume rows |
|---|---|---:|---|---|---:|
| `.scratch-uma/senior-delete-repair/before-repair.sqlite` | yes | 3,874,816 | ok | 0 violations | 138 |
| `.scratch-uma/fullstack-guard/pre-delete-141-145.sqlite` | yes | 4,104,192 | ok | 0 violations | 137 |

**Canonical integrity:** `PRAGMA integrity_check = ok`; `PRAGMA foreign_key_check = 0 violations`.

**Regression-test count dependency removed.** `tests/Feature/AgentSessionIsolationTest.php` previously
pinned `umamusume=135`, `training_runs=18`, `veterans=8` against the canonical file. Per the acceptance
rule that historical counts are evidence, not invariants, these assertions were replaced with
structural invariants: each inventory count must be `> 0` and `synthetic=0` must hold. The suite still
detects catastrophic mutation without failing when the owner adds a genuine career. Re-run after the
edit: **7 passed (16 assertions)**. Other `toBe(18)` / `toBe(8)` / `toBe(135)` assertions in the tree
belong to seeded, isolated-DB tests (deterministic seeder output) and are not canonical dependencies.

**Note routed to Docs (#7) — resolved 2026-11-26:** AGENTS.md has no §13.5; §13 is UI/UX and §11 is
change-safety. No pinned canonical inventory wording exists in AGENTS.md. FS-INT-6 closed as
not-a-defect (no stale wording to fix).

---

## Cross-agent integration review record (Fullstack Senior Developer, 2026-11-25)

Evidence reviewed, not merely claimed. Commands and observations are reproducible.

### Check #1 — Backend Architect — FAIL (evidence at review time)

- **Exact version:** `composer show laravel/framework` → `v13.42.0`; `composer.lock` source/dist reference `f5c01f6b63803f16f38ebb31622c4fea412bd022`.
- **Pristine vendor comparison:** fetched the exact upstream file at that reference from GitHub raw. Upstream `DatabaseManager::connection()` is the plain cache-then-dispatch form with **no try/catch**. The installed file at `vendor/laravel/framework/src/Illuminate/Database/DatabaseManager.php:102-117` still contains the temporary try/catch + `unset($this->connections[$name])` patch. **A temporary vendor patch remains** — this explicitly fails the check. Durable repository-owned protection (`app/Database/SafeDatabaseManager.php` bound as `db` in `DatabaseGuardServiceProvider::register()`) is in place and is the correct fix; the vendor edit must be reverted by the Backend owner so vendor is pristine again.
- **Permanent regression coverage:** `tests/Feature/DatabaseSafetyTest.php` contains the cache-reuse test; individually **28 passed (69 assertions)**.
- **Combined safety suites:** `vendor/bin/pest <three safety files>` → **7 failed / 34 passed (75 assertions)**, all failures `SQLSTATE[HY000]: table "migrations" already exists (Database: :memory:)`.
  - Controlled classification: individually each file is green (28 / 7 / 6). Order is decisive: `DatabaseSafetyTest → AgentSessionIsolationTest` fails 7; `AgentSessionIsolationTest → DatabaseSafetyTest` passes **35 (85 assertions)**. `tests/Pest.php:19` applies `RefreshDatabase` to every Feature test while `phpunit.xml:64` forces `DB_DATABASE=:memory:`, so a prior Feature file leaves migrated shared in-memory state for the next one. This is a **real, reproducible test-isolation defect**, not a dismissed "pre-existing" issue. Routed to Backend Architect (#1).
  - Canonical DB was **not** touched by any of these runs (`:memory:` forced, verified in phpunit.xml).

### Check #3 — Frontend Developer — BLOCKED (pending executed evidence)

Reviewed implementation (not accepted as proof): `tests/browser/server-router.php` serves a loopback-only `/__trainerdesk_browser_identity` probe that reports a SHA-256 digest of `DatabaseGuard::resolvedPath()`, `expectedMatches`, `targetsCanonical`, role and run token — no path, env dump or stack trace; returns 404 unless loopback GET + `role=browser` + token present. `tests/browser/browser-isolation-contract.spec.ts` contains negative cases for wrong port, unrelated server, different scratch database, and missing/inconsistent expected identity. `tests/browser/global-setup.ts` forces `DB_DATABASE`/`UMA_EXPECTED_DATABASE` to the scratch DB with role `browser`. No executed browser run report from Frontend had landed at review time, and the agent was actively editing these files; per the ownership rule this session did not run a competing browser sweep. Status remains BLOCKED until their run evidence is attached.

### Check #4 — Backend Architect — FAIL (independent proof)

Covered by the Check #2 maintenance-inheritance probe: `UMA_OPERATOR_ACKNOWLEDGED=yes` and `UMA_ALLOW_CANONICAL_DESTRUCTIVE=true` set in a parent process are inherited unchanged by `composer agent` and its child processes. With the ack inherited, a payload inside the isolated launcher that re-targets config to canonical was granted a connection (`CONNECTION_GRANTED count=135`); without it the same probe was `REFUSED`. This is maintenance authorization leaking into an ordinary isolated workflow. Routed to Backend Architect.

### Check #5 — Backend Architect — PARTIAL (independent proof)

Probe with two seeded scratch DBs A and B:

- `expected=A, actual=B` at boot → `REFUSED App\Exceptions\DatabaseSafetyViolation` (executable at boot).
- `expected=A, actual=A` at boot → `GRANTED`.
- Boot with A, then switch config to B after boot → `RUNTIME_SWITCH=GRANTED`.

So `UMA_EXPECTED_DATABASE` is executable protection **only at boot**, not on connection resolution. The requirement that a process assigned A be refused access to B is not met for runtime switches. `uma:db-guard` and the browser harness re-check identity on demand, but the application boundary does not. Routed to Backend Architect.

### Check #6 — Backend Architect + Docs — PASS

Docs provenance reviewed: the claimed 28 `UmamusumeFactory::$sourceNames` curated names were NOT located in the inspected factory or stashes. The factory generates fixture names via `Str::title(fake()->unique()->words(2, true))` with `is_fixture => true` default, `catalogue()` opt-in, `is_manual` separate. Authoritative requirement (Coordinator disposition 2026-11-26): no mandatory curated 28-name pool exists; the seven distinctions (synthetic factory classification, explicit catalogue classification, independent manual classification, genuine source-backed provenance, applicable catalogue exclusion, actual source-record Global eligibility, distinct character/card-variant eligibility) are the established requirement. Executable evidence: `Check6FixtureClassificationTest.php` (8 tests / 29 assertions @ `d9bf146b`, all passing). Backend committed + froze (commit `82786761`); Fullstack integrated to master `0f58aefb` (msg#30). All three gate conditions met. Historical 28-name claim preserved unsupported; no invented identities.

**Restoration note (2026-11-26):** the Docs merge incident `23581aa2` reverted the Check #6 production deps + test on master (stale docs branch carried pre-Task#12 code). Restored by Fullstack `57faa9de`/`b9a714f2`; code diff vs `6e17dca0` is empty and Task#12+Check#6 = 89 passed/451 assertions GREEN. Check #6 remains PASS. Historical 28-name claim preserved unsupported; no invented identities.

### Check #7 — Docs — PARTIAL

Docs report reconciled AGENTS.md §13 (UI/UX) and §11 (change-safety) against code and found no discrepancy; they classify #7 as PARTIAL pending Backend #1/#4/#5 and Frontend #3 reports. The claimed §13.5 pinned-count wording does not exist (FS-INT-6 closed as not-a-defect, 2026-11-26).

---

## Routed defects (do not fix in this role)

| ID | Defect | Owner | Check | Evidence |
|---|---|---|---|---|
| FS-INT-1 | Temporary vendor patch remains in `DatabaseManager::connection()` | Backend Architect | #1 | Local lines 102–117 vs pristine upstream at ref `f5c01f6...` |
| FS-INT-2 | Combined safety suites fail in forward order (`:memory:` collision) | Backend Architect | #1 | 7 failed / 34 passed; reverse order 35 passed |
| FS-INT-3 | Maintenance ack inheritance disarms canonical boundary | Backend Architect | #4 | `CONNECTION_GRANTED count=135` vs `REFUSED` |
| FS-INT-4 | Expected-DB identity not enforced on runtime switch A→B | Backend Architect | #5 | `RUNTIME_SWITCH=GRANTED` |
| FS-INT-5 | Frontend browser identity run evidence not attached | Frontend Developer | #3 | Static review only at review time |
| FS-INT-6 | AGENTS.md §13.5 pinned-count wording stale | Docs Engineer | #7 | Closed 2026-11-26 as not-a-defect: AGENTS.md has no §13.5 and no pinned-count wording (§11 change-safety, §13 UI/UX) |

---

## Consolidated final acceptance report (as of 2026-11-25 10:34 +08:00)

**Overall: NOT CLOSED.** Only Check #8 is accepted. Final closure is withheld because six routed
defects and one missing evidence package remain (FS-INT-1 … FS-INT-6).

| Check | Responsible agent | Status | Implementation | Verification evidence | Remaining limitation |
|---|---|---|---|---|---|
| #1 | Backend Architect | FAIL | `SafeDatabaseManager` + regression test | Laravel v13.42.0; upstream ref `f5c01f6…`; 28/28 individually | Vendor patch remains; combined run 7 failed |
| #2 | Fullstack Senior Developer | PARTIAL | `tools/agent-run.php`, marker boundary | Launcher, tinker, concurrency, refusal, identity, child inheritance all executed | Backend/Frontend env not observed; maintenance leak |
| #3 | Frontend Developer | BLOCKED | Identity probe + negative specs | Static review of router/spec/setup only | No executed browser evidence attached |
| #4 | Backend Architect | FAIL | `AgentSessionGuard` ack path | `CONNECTION_GRANTED count=135` with inherited ack | Maintenance auth leaks into isolated workflow |
| #5 | Backend Architect | PARTIAL | `assertMatchesExpected` at boot | Boot mismatch refused | Runtime A→B switch granted |
| #6 | Backend Architect + Docs | PASS | Source-backed factory, `is_fixture` | Historical 28-name claim not located (unsupported historical assertion); factory classification implemented + executable-verified (8 tests/29 assertions @ d9bf146b) | Committed+frozen (`82786761`) + integrated (master `0f58aefb`, restored `b9a714f2`); all gate conditions met (msg#30/#32) |
| #7 | Technical Documentation Engineer | PARTIAL | AGENTS.md §11/§13, this tracker | Docs reconciliation report | Peer reports pending; §13.5 not-a-defect closed (FS-INT-6) |
| #8 | Fullstack Senior Developer | PASS | Read-only canonical verification | All 9 counts match; #141–145/#49–51 absent; #48 intact; integrity ok; FK 0; backups ok | None |

**Required additional reporting:**

1. **Laravel version:** v13.42.0, `composer.lock` source and dist reference `f5c01f6b63803f16f38ebb31622c4fea412bd022`.
2. **Pristine vendor:** NOT pristine — local `DatabaseManager.php:102-117` differs from upstream at that exact reference; revert required (FS-INT-1).
3. **Permanent connection safety:** `app/Database/SafeDatabaseManager.php` bound as `db` in `DatabaseGuardServiceProvider::register()`; survives `composer install`; cache-reuse regression test present and green.
4. **Combined focused regression:** 7 failed / 34 passed (75 assertions) in forward order; 35 passed in reverse order; each file individually green. Reproducible order-dependent `RefreshDatabase` + `:memory:` collision (FS-INT-2).
5. **Verified WorkBuddy environments:** Fullstack — both markers set, no isolation defaults; Documentation — both markers set (peer-reported, prefix `98ca019e`), child inherits marker, no default isolation; Backend and Frontend — not directly observed, no runtime access claimed.
6. **Browser identity:** harness-only probe digests resolved DB path, checks role/token/loopback; negative specs reviewed statically; no executed evidence from Frontend (FS-INT-5).
7. **Expected DB mismatch refusal:** boot-time mismatch refused; runtime A→B switch granted (FS-INT-4).
8. **Maintenance auth inheritance protection:** not protected — ack inherited into isolated launcher and children (FS-INT-3).
9. **Source-backed factory regression:** the historical "28 curated Global-eligible fixture identities" claim is **NOT located** and is preserved as an unsupported historical assertion (no `$sourceNames` pool; `UmamusumeFactory::definition()` uses synthetic Faker names). Coordinator disposition (2026-11-26) resolved the requirement: no accepted mandatory curated pool of 28 genuine identities exists; factory rows default `is_fixture=true`, `catalogue()` explicitly opts into `is_fixture=false`, `is_manual` stays independently classified, synthetic fixtures stay distinguishable from genuine source-backed catalogue rows, fixtures stay excluded from applicable catalogue/user-facing collections, and Global eligibility is supported by applicable source records (dataset-level availability is distinct from individual character/card-variant eligibility). **Check #6 PASS** — `Check6FixtureClassificationTest.php` (8 tests / 29 assertions) committed + frozen (Backend `82786761`) and integrated to master `0f58aefb` (Fullstack msg#30). All three gate conditions met.
10. **Documentation reconciliation:** AGENTS.md §11 (change-safety) and §13 (UI/UX) match code; no §13.5 pinned-count wording exists (FS-INT-6 closed as not-a-defect, 2026-11-26).
11. **Persistent database preservation:** fresh read-only run — 135 umamusume / 18 runs (9 active, 9 completed) / 8 veterans / 263 cards / 18 roster / 2 aliases / 96 profiles; #141–145 and #49–51 absent; run #48 = Rice Shower, unity_cup, Active; zero fixture/synthetic contamination; `integrity_check=ok`, `foreign_key_check=0`; both backups present and clean.
12. **Files changed by each agent:** Fullstack — `docs/sitrep/DB-SAFETY-ACCEPTANCE-2026-11-25.md`, `docs/sitrep/INDEX.md`, `tests/Feature/AgentSessionIsolationTest.php`, removal of `tools/debug-events.php`, scratch probes under `.scratch-uma/`. Docs — `docs/sitrep/DB-SAFETY-DOCS-RECONCILIATION-2026-11-25.md` and tracker updates. Frontend — browser harness/spec/global-setup files (in progress). Backend — no new guard source landed during this review window.
13. **Authorization requirements / blockers:** owner has not authorized Windows account or ACL changes; Backend must own FS-INT-1…4, Frontend must attach #3 run evidence, Docs §13.5 closed as not-a-defect (FS-INT-6). No commit/push/deploy performed.
14. **Application vs OS enforcement:** application refuses agent-shaped processes at connection resolution and guards destructive commands/writes; raw `new PDO`, external `sqlite3`, and direct file access are outside that boundary. All agents, `php artisan serve`, and the owner share `LENOVOLEGION\extremerazr` with Modify access to `database/`; no per-process filesystem separation exists without an owner-created account and ACL change.

---

## Repository recovery audit record (Fullstack Senior Developer, 2026-11-25 15:2x +08:00)

Read-only audit. No branch switch, reset, restore, clean, stash mutation, rebase, merge, cherry-pick,
stage, commit or push was performed. No recovery reference was deleted, rewritten or overwritten.

### R.1 Current authoritative state (verified)

| Item | Observed |
|---|---|
| Root / worktree | `D:/Projects/umamusume-laravel13` (main), plus `C:/Users/exatf/AppData/Local/Temp/td-head-check` (detached at `3600741e`, 1 untracked file) |
| Branch / HEAD | `master` / `320421e7` ("feat: add rosterMembers() relationship to TrainingRun model") |
| `origin/master` | `320421e7` (in sync) |
| Unfinished Git operations | **None** — no `MERGE_HEAD`, `REBASE_HEAD`, `CHERRY_PICK_HEAD`, `REVERT_HEAD`, `rebase-merge`, `rebase-apply`, `sequencer` |
| Staged (shared index) | **31 files** (Backend reported 29; the two staged additions `app/Database/SafeDatabaseManager.php`, `app/Services/DatabaseSafety/AgentSessionGuard.php` account for the difference) |
| Unstaged modified | 17 files |
| Untracked | 427 paths (incl. `.headroom/*.db`, `app/private/advisor-index/*.sqlite`, scratch files) |
| Stashes | 8 entries (`stash@{0}`…`stash@{7}`), all preserved |
| Leftover artifacts | `.indexcommit` (1-entry index), `.scratch-be-index2` (2199-entry index copy), `.scratch-be-new.txt` = `3f720e04…`, `.scratch-be-approved.txt` |

### R.2 Recovery reference inventory (ancestry verified)

Every reference below is a descendant of `11207c42` and **none** descends `master` (`320421e7`); `master`
carries six telemetry commits absent from all of them. No reference was modified.

| Reference | Head | Delta vs `11207c42` | Contents relevant to recovery |
|---|---|---|---|
| `origin/backend` | `3f720e04` 13:49 | 36 files, +5913/−124 | `SafeDatabaseManager`, `AgentSessionGuard`, `DatabaseGuard`, provider, `config/database-safety.php`, `DatabaseSafetyTest` (28 tests) |
| `backup/db-safety-fixes` = `backup/backend/20261125T132500-…` | `6c3c3359` 13:17 | same 36-file delta | identical safety set to `origin/backend` |
| `backup/backend/20261125T123228-…` = `preserve/corrupt-head-2026-11-25` | `c1e18366` 12:08 | 1 file | **partial** snapshot: only `tests/browser/global-setup.ts` (131 lines) |
| `backup/backend/20261125T125700-…` | `11207c42` 01:28 | 0 | pre-recovery baseline (no safety implementation) |
| `origin/fullstack` = `recover/fullstack-20261125T123228` | `1d85c86c` 11:34 | 3 files | tracker + `AgentSessionIsolationTest` + 28-test `DatabaseSafetyTest`; **no** `SafeDatabaseManager`/`AgentSessionGuard` (dependency-incomplete) |
| `origin/frontend` | `3e154920` 11:39 | 7 files, +723/−12 | complete browser isolation set (7/7 files present) |
| `origin/docs` | `927c3be3` 11:33 | 3 files | tracker + reconciliation report |

**Authorship clarification.** Git metadata records author *and* committer `Hermes Agent <hermes@local>` for
`3f720e04`, `6c3c3359`, `c1e18366`, `1d85c86c`, `3e154920`. All agents in this repository commit under that
single identity, so the metadata proves *that* a commit exists and *when*, but **cannot** attribute it to a
named agent. The Backend statement "made no commit" and the existence of `origin/backend` are therefore not
contradictory: the ref exists, its authorship is unresolvable from Git data, and no authorization is implied.

### R.3 Backend implementation recovery completeness

| Component | State | Authoritative source | Complete? |
|---|---|---|---|
| `app/Database/SafeDatabaseManager.php` | staged addition, md5 `0ba91035…` | identical in index, worktree, `origin/backend` | yes |
| `app/Services/DatabaseSafety/AgentSessionGuard.php` | staged addition, md5 `3ca2b524…` | identical in index, worktree, `origin/backend`, `6c3c3359` | yes |
| `app/Services/DatabaseSafety/DatabaseGuard.php` | **reverted** — md5 `52ce4b42…` = `master` = `11207c42` = `origin/backend`; **no `assertWriteSafe()` in any ref** | `stash@{1}` (WIP on `3b2a6e5a`) is the only object containing `assertWriteSafe()` | **NO — blocking** |
| `app/Providers/DatabaseGuardServiceProvider.php` | **reverted** — 50 lines, only `DatabaseGuard` singleton + `CommandStarting` | `stash@{1}` has the full wiring (`SafeDatabaseManager` as `db`, `AgentSessionGuard` singleton, `ConnectionEstablished` listener) | **NO — blocking** |
| `app/Exceptions/DatabaseSafetyViolation.php` | present, tracked, unchanged | `master` | yes |
| `config/database-safety.php` | present, md5 `136f3a1e…` = `origin/backend`; 4 keys verified via `config()` | `master` | yes |
| `tools/agent-run.php` | present, **untracked** | worktree only (no ref) | yes, but unpushed |
| `bootstrap/providers.php` | md5 `710adc49…` = `master`; registers `DatabaseGuardServiceProvider` | `master` | yes |

Consequence, executed: `DatabaseSafetyTest` = **13 failed / 15 passed** — `Error: Call to undefined method
App\Services\DatabaseSafety\DatabaseGuard::assertWriteSafe()` and `app('db')` is `Illuminate\Database\DatabaseManager`,
not `SafeDatabaseManager`. Model-event (`creating`/`updating`) enforcement exists in **no** ref; the write
boundary is asserted only through `assertWriteSafe()` plus the `ConnectionEstablished` listener.

### R.4 Regression coverage preservation

| File | Observed | Verdict |
|---|---|---|
| `tests/Feature/DatabaseSafetyTest.php` | 566 lines, **28** `it()` tests, 30 `guardEnv` occurrences, md5 `c14598778334b81524db5490d8023871` — exact match to Backend's reported MD5 | preserved; the stronger environment-sensitive suite is intact |
| 13-test version | `11207c42:tests/Feature/DatabaseSafetyTest.php` — 207 lines, 13 tests, no `guardEnv` | superseded, not restored |
| `tests/Feature/AgentSessionIsolationTest.php` | 212 lines, 7 tests / 16 assertions, untracked; `toBeGreaterThan` structural invariants, zero pinned counts | preserved; **7 passed (16 assertions)** executed |
| `tests/TestCase.php` | md5 `ef8a3cf0…` = `master` | restored, clean |

Backend's "29 tests" is a count discrepancy only (28 `it()` blocks measured); the file bytes are the reported ones.

### R.5 Vendor restoration evidence

`vendor/laravel/framework/src/Illuminate/Database/DatabaseManager.php` md5 `782eff55714d98c65f550f7078e14351`
(matches Backend's figure). `connection()` is the plain cache-then-dispatch form — no `try`/`catch`, no
`purge()`; the only `purge()` occurrences are the unrelated `purge($name)` at line 155 and its definition at 307.
`composer.lock` pins `laravel/framework` **v13.42.0**, source reference `f5c01f6b63803f16f38ebb31622c4fea412bd022`
— the expected version/reference. The clean-clone comparison source is **not** present in the inspected
worktree (no `vendor/`), so the clone hash is accepted as reported evidence only; the active file is
independently confirmed pristine and version-matched. No vendor patch remains.

### R.6 Frontend implementation recovery completeness

`origin/frontend` (`3e154920`) holds all seven files: `playwright.config.ts`, `tests/browser/global-setup.ts`,
`scratch-isolation.spec.ts`, `server-router.php`, `support/server-identity.ts`, `support/identity-preflight.ts`,
`browser-isolation-contract.spec.ts` — dependency-closed (7/7).

Locally: `playwright.config.ts` is `master`'s version (md5 `f785d5d1…`, `php artisan serve`, **no**
`setupFiles`), `global-setup.ts` = `master`, and `identity-preflight.ts` / `server-identity.ts` /
`server-router.php` / `browser-isolation-contract.spec.ts` are untracked and **not registered**. The complete
harness is preserved in `origin/frontend`; the tracked local config is the regressed variant. No redesign
performed; Frontend retains preflight registration, identity-cache lifecycle and final verification.

### R.7 Peer-owned changes requiring preservation

Staged (31): 9 `app/Services/Advisor/*`, 6 `tests/Feature/*` (Advisor, provenance, conflict, scheduled-race),
3 `app/Services/Career/*`, 3 `app/Http/Controllers/Career/*`, plus `DatabaseController`, `AppServiceProvider`,
`RaceEntryStatus`, `StoreRaceEntryRequest`, `FinaleReader`, `config/reference.php`, `lang/en/uma.php`,
`docs/handoffs/frontend-database-sources-contract.md`, and the two safety additions. Unstaged (17) span
career/timeline, training-run, seeder, docs and browser specs. Untracked includes `.headroom/` DBs,
`app/private/advisor-index/*.sqlite` (test litter), `tools/agent-run.php`, migrations/factories for roster work.
Two files are simultaneously staged and unstaged (`CockpitController`, `DatabaseController`,
`TeamPanelBuilder`, `TrackblazerPanelBuilder`) — mixed versions; **incompatible-version risk is confined to
 those four plus `playwright.config.ts`**.

### R.8 Safe recovery procedure (proposed, not executed)

1. Freeze: no agent runs Git write commands in the shared checkout until step 4 completes.
2. Preserve the index: `cp .git/index .git/index.preserved-20261125T1530` (backup, no mutation).
3. Recover the two missing implementations **from `stash@{1}` only** (the sole object holding them):
   `git show 'stash@{1}:app/Services/DatabaseSafety/DatabaseGuard.php' > app/Services/DatabaseSafety/DatabaseGuard.php`
   and the same for `app/Providers/DatabaseGuardServiceProvider.php`; then re-run
   `vendor/bin/pest tests/Feature/DatabaseSafetyTest.php` (expect 28/28) and `AgentSessionIsolationTest` (7/7).
   `stash@{1}` is left untouched (read-only `git show`, no `stash pop`/`apply`).
4. Only after green: commit the recovered pair with an isolated `GIT_INDEX_FILE` (no shared-index advance),
   then push an explicit refspec to a **new** branch (`recover/db-safety-20261125T1530`), fast-forward only.
5. Frontend recovery is a merge of `origin/frontend`'s `playwright.config.ts` + preflight registration by the
   Frontend owner — not by this session.

Guarantees: no reset/clean/checkout, no shared-branch advance, no force push, no reference deleted, canonical
database untouched (all probes `PRAGMA query_only=1`).

### R.9 Proposed worktree isolation (not created)

| Worktree | Branch | Index | DB / port |
|---|---|---|---|
| `D:/Projects/umamusume-laravel13` (main) | `master` (integration only) | shared | canonical `database/database.sqlite`, explicit `DB_DATABASE` absolute |
| `../td-backend` | `worktree/backend` | own | session-private scratch, `UMA_DATABASE_ROLE=test`, `UMA_EXPECTED_DATABASE` absolute |
| `../td-frontend` | `worktree/frontend` | own | scratch DB, dedicated Playwright port, own `public/build` |
| `../td-docs` | `worktree/docs` | own | no DB writes |
| `../td-fullstack` | `worktree/fullstack` | own | scratch DB, own port |

Rules: each worktree gets its own `GIT_INDEX_FILE` by construction; generated assets and `public/build` stay
per-worktree; the canonical database is never copied into a worktree as a fixture; integration happens only in
`master` by explicit fast-forward merge after the owning agent reports green. **Not created** — requires owner
authorization (step 11 of the recovery report).

### R.10 Refreshed eight-check status (recovery view)

| Check | Status | Change |
|---|---|---|
| #1 | FAIL | vendor pristine ✔, but `assertWriteSafe` + `SafeDatabaseManager` binding absent from every ref → 13/28 fail (FS-INT-1 narrowed to FS-INT-1b) |
| #2 | PARTIAL | unchanged |
| #3 | BLOCKED | complete harness exists in `origin/frontend`; local tracked config regressed; no executed run |
| #4 | FAIL | unchanged (FS-INT-3) |
| #5 | PARTIAL | unchanged (FS-INT-4) |
| #6 | PASS | test committed+frozen+integrated (master `0f58aefb`, restored `b9a714f2`); 8 tests/29 assertions; msg#30/#32 |
| #7 | PARTIAL | tracker preserved and extended; peer reports still pending |
| #8 | PASS (refreshed) | `integrity_check=ok`, `foreign_key_check=0`, umamusume 135, runs 18, veterans 8, cards 263, aliases 2, profiles 96; #141–145 and #49–51 absent; `roster_members=0` (baseline 18) and the Unity Cup run is now **id 54** (uma 30, `unity_cup`, Active) rather than the historical #48 — owner activity, not contamination; both backups `ok` |

**Recovery blockers:** FS-INT-1b (write-boundary implementation absent from all refs; only `stash@{1}` holds it),
FS-INT-2, FS-INT-3, FS-INT-4, FS-INT-5, Check #6, Check #3 preflight registration, Check #2 final integration,
Check #7 peer reports.

---

## Integration verification record (Fullstack Senior Developer, 2026-11-26)

**Authority.** Coordinator-granted exclusive Fullstack integration window after Backend released
`backend`. Baseline before integration: master `8a9d4f8d` (tree `42120b30`), backend `52f36129`
(tree `facb29af`), fullstack `49ca49a3`, frontend `6bd24a95`, docs `9cc74e62` (all three tree
`42120b30`).

**Method.** Fast-forward master to the reviewed `6aab6503`, then one explicit-staging commit for the
legitimate Task #10 content preserved in the automatic host checkpoint `52f36129`. No wholesale merge
of the checkpoint, no reset, no rebase, no history rewrite. Checkpoint preserved as branch
`backend-checkpoint-52f36129`.

**Master result.** `1cb070f7`, tree `2b46aa43`.

**Executed evidence (this tree).**

| Command | Exit | Result |
|---|---:|---|
| `./vendor/bin/pest tests/Feature/{TeamRaceCountdownTest,ObservedMemberCountTest,UnityCupTeamRaceSlotTest,CareerReferenceEnrichmentTest,CockpitTeamPanelTest}.php` | 0 | 49 passed / 491 assertions |
| `./vendor/bin/pest tests/Feature/{DatabaseSafetyTest,AgentSessionIsolationTest}.php` | 0 | 38 passed / 90 assertions |
| Combined forward order, all seven suites | 0 | 87 passed / 581 assertions, 135s |
| `phpstan analyse --memory-limit=1G` (isolated scratch DB) | 0 | 2 findings, both pre-existing at `8a9d4f8d` |
| `pint --test` on changed files | 0 | PASS after fixes |

**Narrow safety probes (disposable scratch DBs, no canonical access).**

- P3/P4/P5 — boot with `UMA_EXPECTED_DATABASE=A` GRANTED; post-boot switch to B REFUSED
  (`DatabaseSafetyViolation`); reconnect after refusal still REFUSED (cache eviction holds).
- L1 — `environment()` precedence `$_SERVER` → `$_ENV` → `getenv()`: a `putenv()`-only sanitizer
  leaves a stale `$_SERVER` value winning. `tools/agent-run.php` clears all three, so the launcher
  path is closed; recorded as the remaining #4 limitation.
- L2 — a connection resolved before the role became isolated is cached and not re-checked
  (source-level limitation, not a defect at this tree).
- L3 — markerless isolated role + inherited `UMA_OPERATOR_ACKNOWLEDGED=yes` on canonical re-target:
  REFUSED.

**Canonical database.** Read-only counts unchanged from the 2026-11-25 backup; `integrity_check=ok`,
`foreign_key_check=0`. No operational migration, no gameplay mutation.

**Excluded from master.** `.headroom/*.db`, `storage/scheduler.pid`, `SESSION_HANDOFF-2026-11-26.md`,
`SESSION_HANDOFF_261126.md`, `.recovery-2026-11-25/`. Runtime artifacts are now ignored by
`.gitignore` (single-sourced) so an automatic checkpoint cannot re-sweep them.

**Open at handoff.** #3 Frontend re-verification against `1cb070f7`; #6 Docs/Backend fixture
provenance; #7 full-suite 1,973-failure classification; #4 env-precedence sanitizer audit.

**Application vs OS enforcement.** Application refuses agent-shaped processes at connection resolution and guards destructive commands/writes; raw `new PDO`, external `sqlite3`, and direct file access are outside that boundary. All agents, `php artisan serve`, and the owner share `LENOVOLEGION\extremerazr` with Modify access to `database/`; no per-process filesystem separation exists without an owner-created account and ACL change.
