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
| #1 | FAIL | This tracker §Integration review record; Laravel v13.42.0 pristine upstream vs local vendor diff; combined suite runs | Temporary vendor patch still present in `vendor/.../DatabaseManager.php`; combined safety suites fail in forward order (7 × `table "migrations" already exists`). Both routed to Backend Architect |
| #2 | PARTIAL | This tracker §Check #2 verification record; `docs/sitrep/DB-SAFETY-DOCS-RECONCILIATION-2026-11-25.md` §Check #2 | Backend and Frontend runtime environments not directly observed by this session (no verified access to their sessions); maintenance auth leak defect routed to Backend #4 |
| #3 | BLOCKED | Static review of `tests/browser/server-router.php`, `tests/browser/browser-isolation-contract.spec.ts`, `tests/browser/support/server-identity.ts` | Frontend implementation present but no executed browser verification report landed at review time; no independent browser run (concurrent agent editing the same files) |
| #4 | FAIL | This tracker §Check #2 verification record (maintenance inheritance probe) | `UMA_OPERATOR_ACKNOWLEDGED=yes` inherited into isolated launcher disarms connection boundary on canonical re-target — proven. Routed to Backend Architect |
| #5 | PARTIAL | This tracker §Integration review record (expected-DB probe) | Boot-time mismatch refused; runtime switch A→B after boot granted. Routed to Backend Architect |
| #6 | PARTIAL | `docs/sitrep/DB-SAFETY-DOCS-RECONCILIATION-2026-11-25.md` §Check #6; `database/factories/UmamusumeFactory.php` | Docs provenance verified (28 source-backed names); Backend's dedicated regression evidence not yet attached |
| #7 | PARTIAL | `docs/sitrep/DB-SAFETY-DOCS-RECONCILIATION-2026-11-25.md`; AGENTS.md §13/§13.5; `docs/INDEX.md`; this tracker | Follow-up pass done (provenance dates, six-category terminology, runtime handoff, full-suite framing, discoverability). Still Awaiting Backend #1/#4/#5 + Frontend #3 implementation reports. Full-suite 1,973-failure run = unresolved integration finding (not 1,973 defects; Fullstack owns classification); not a blanket prerequisite for all checks. No full Pest run (doc-only scope). |
| #8 | PASS | This tracker §Check #8 verification record (fresh read-only probe output) | None found. Fixed canonical counts removed from the boundary suite; AGENTS.md §13.5 wording now stale (routed to Docs #7) |

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

**Note routed to Docs (#7):** AGENTS.md §13.5 still describes the pinned canonical inventory as an
asserted fact; update that wording to structural invariants (owner of that file: Docs agent).

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

### Check #6 — Backend Architect + Docs — PARTIAL

Docs provenance reviewed and independently supported: all 28 `UmamusumeFactory::$sourceNames` entries are curated source-backed names, `is_fixture => true` default with `catalogue()` opt-in, `is_manual` separate. Backend's dedicated factory regression evidence was not attached at review time.

### Check #7 — Docs — PARTIAL

Docs report reconciled AGENTS.md §13 against code and found no discrepancy; however they classify #7 as PARTIAL pending Backend #1/#4/#5 and Frontend #3 reports. One new discrepancy created by this session's Check #8 fix: AGENTS.md §13.5 still says the suites assert pinned canonical counts (`umamusume=135`, etc.); the boundary suite now asserts structural invariants. Routed to Docs.

---

## Routed defects (do not fix in this role)

| ID | Defect | Owner | Check | Evidence |
|---|---|---|---|---|
| FS-INT-1 | Temporary vendor patch remains in `DatabaseManager::connection()` | Backend Architect | #1 | Local lines 102–117 vs pristine upstream at ref `f5c01f6...` |
| FS-INT-2 | Combined safety suites fail in forward order (`:memory:` collision) | Backend Architect | #1 | 7 failed / 34 passed; reverse order 35 passed |
| FS-INT-3 | Maintenance ack inheritance disarms canonical boundary | Backend Architect | #4 | `CONNECTION_GRANTED count=135` vs `REFUSED` |
| FS-INT-4 | Expected-DB identity not enforced on runtime switch A→B | Backend Architect | #5 | `RUNTIME_SWITCH=GRANTED` |
| FS-INT-5 | Frontend browser identity run evidence not attached | Frontend Developer | #3 | Static review only at review time |
| FS-INT-6 | AGENTS.md §13.5 pinned-count wording stale | Docs Engineer | #7 | Check #8 edit to `AgentSessionIsolationTest.php` |

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
| #6 | Backend Architect + Docs | PARTIAL | Source-backed factory, `is_fixture` | Docs provenance: 28/28 names verified | Backend regression evidence pending |
| #7 | Technical Documentation Engineer | PARTIAL | AGENTS.md §13, this tracker | Docs reconciliation report | Peer reports pending; §13.5 wording stale |
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
9. **Source-backed factory regression:** 28 curated names verified against committed catalog by Docs; `is_fixture` default true, `catalogue()` opt-in; Backend regression evidence pending.
10. **Documentation reconciliation:** AGENTS.md §13 matches code except §13.5 pinned canonical counts, now stale after the Check #8 test fix (FS-INT-6).
11. **Persistent database preservation:** fresh read-only run — 135 umamusume / 18 runs (9 active, 9 completed) / 8 veterans / 263 cards / 18 roster / 2 aliases / 96 profiles; #141–145 and #49–51 absent; run #48 = Rice Shower, unity_cup, Active; zero fixture/synthetic contamination; `integrity_check=ok`, `foreign_key_check=0`; both backups present and clean.
12. **Files changed by each agent:** Fullstack — `docs/sitrep/DB-SAFETY-ACCEPTANCE-2026-11-25.md`, `docs/sitrep/INDEX.md`, `tests/Feature/AgentSessionIsolationTest.php`, removal of `tools/debug-events.php`, scratch probes under `.scratch-uma/`. Docs — `docs/sitrep/DB-SAFETY-DOCS-RECONCILIATION-2026-11-25.md` and tracker updates. Frontend — browser harness/spec/global-setup files (in progress). Backend — no new guard source landed during this review window.
13. **Authorization requirements / blockers:** owner has not authorized Windows account or ACL changes; Backend must own FS-INT-1…4, Frontend must attach #3 run evidence, Docs must fix §13.5. No commit/push/deploy performed.
14. **Application vs OS enforcement:** application refuses agent-shaped processes at connection resolution and guards destructive commands/writes; raw `new PDO`, external `sqlite3`, and direct file access are outside that boundary. All agents, `php artisan serve`, and the owner share `LENOVOLEGION\extremerazr` with Modify access to `database/`; no per-process filesystem separation exists without an owner-created account and ACL change.
