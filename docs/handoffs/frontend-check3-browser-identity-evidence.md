# Frontend Handoff — Check #3 Executed Browser Identity Evidence (FS-INT-5)

**To:** Coordinator (Senior Project Manager), Fullstack (integration accountability)
**From:** Frontend Developer
**Date:** 2026-11-26
**Closes:** FS-INT-5 (Check #3 was `BLOCKED` for missing *executed* browser evidence, not implementation)
**Commit:** `b6e5120e61f679c43ea2565989ec7cdc021f7103` (branch `backend`; see §6 — evidence is against this
tree, nothing committed by me)

Status claim: **Check #3 executed evidence landed.** Implementation was already complete and tracked; only
the run report was missing. I did not redesign, refactor, or add to the harness.

## 1. Exact command run

```bash
PLAYWRIGHT_PORT=8177 node_modules/.bin/playwright test \
  tests/browser/browser-isolation-contract.spec.ts \
  tests/browser/mandatory-preflight-coverage.spec.ts \
  tests/browser/scratch-isolation.spec.ts \
  --reporter=list
```

Result: **15 passed (3.2m)**, 0 failed, 0 skipped. 9 + 3 + 3 tests across the three files.

## 2. Identity proof printed by the run's own setup (verbatim)

```
role          : browser
canonical     : D:\Projects\umamusume-laravel13\database\database.sqlite
DB_DATABASE   : D:\Projects\umamusume-laravel13\.scratch-uma\sqlite\browser-scratch-26212-1795653164955.sqlite
resolved      : D:/Projects/umamusume-laravel13/.scratch-uma/sqlite/browser-scratch-26212-1795653164955.sqlite
expected      : D:\Projects\umamusume-laravel13\.scratch-uma\sqlite\browser-scratch-26212-1795653164955.sqlite
targets canonical: no
Database identity is safe.
```

`resolved == expected`, `targets canonical: no`. The server the harness started served that same scratch file
because `webServer.env` carries `DB_DATABASE` / `UMA_EXPECTED_DATABASE` / `UMA_DATABASE_ROLE=browser` and
`reuseExistingServer: false`.

## 3. Negative-refusal evidence (these are the acceptance-relevant cases)

Every one of these asserted safe refusal **before** the mutating request, and passed:

| # | Case (spec line) | What it proves |
|---|---|---|
| 2 | `browser-isolation-contract.spec.ts:143` | base URL that is not this run's loopback server on this run port → refused |
| 3 | `:153` | unrelated HTTP server answering on another port → refused |
| 4 | `:171` | expected port already occupied → refused (no silent rebind) |
| 5 | `:186` | receiving application using a **different scratch database** → refused (7.9s; the real wrong-DB case) |
| 6 | `:206` | missing or inconsistent expected database identity → refused |
| 7 | `:236` | browser role absent or incorrect → refused |
| 8 | `:256` | two concurrent disposable sessions keep distinct servers, databases and tokens → separated |
| 9 | `:304` | a verified server **replaced** by an incorrect one → refused before the next test mutates |
| 1 | `:130` | the mandatory preflight actually ran in this worker and matched this run's manifest |

Cases 5, 6, 7 are the "incorrect identity refused before a mutating test request" requirement quoted in the
acceptance tracker. Case 9 is the temporal case (identity verified once, then swapped) — it refuses rather
than trusting the earlier verification.

## 4. Enforcement is mandatory, not opt-in — machine-checked in the same run

- `mandatory-preflight-coverage.spec.ts:32` asserts **every** browser spec imports `./support/base-test`, never
  `@playwright/test` directly.
- `:46` asserts the enforced `test` requests the `identityPreflight` fixture.
- `:57` proves the guard's own rule catches a spec that bypasses the import.

Mechanism: `tests/browser/support/base-test.ts` exports `test` extended with
`identityPreflight: [async (...) => ..., { auto: true }]`, so the fixture is requested for every test in any
file importing it, and rejection fails the test before its body. `tests/browser/support/identity-preflight.ts`
is now a re-export shim pointing at that single implementation (the old `beforeEach` registration protected
only 1 of 137 specs).

## 5. Canonical database not touched

Read-only probe (`PDO::SQLITE_OPEN_READONLY`) after the run:

| Item | Value |
|---|---|
| `database/database.sqlite` mtime | `2026-11-25T22:20:54+00:00` — **older than the run start (08:33 +08:00)** |
| umamusume / training_runs / veterans / character_cards | 135 / 18 / 8 / 263 |
| umamusume_aliases / umamusume_profiles | 2 / 96 |
| `PRAGMA integrity_check` | `ok` |
| `PRAGMA foreign_key_check` | 0 violations |

All nine counts equal the historical baseline in the acceptance tracker. Scratch DBs were written under
`.scratch-uma/sqlite/` only.

## 6. File state — nothing committed by me

All harness files are **already tracked**; no new harness source was authored in this session:

```
playwright.config.ts                                  tracked
tests/browser/global-setup.ts                         tracked
tests/browser/browser-isolation-contract.spec.ts       tracked  md5 414fc19310515134c944ee13290bd0bc  (388 lines, 9 tests)
tests/browser/mandatory-preflight-coverage.spec.ts     tracked  md5 3e9663a3f1b78fca23ca04647904e996  (72 lines, 3 tests)
tests/browser/scratch-isolation.spec.ts                tracked  md5 933ff26f2249e2f976b87465f312bc51  (59 lines, 3 tests)
tests/browser/server-router.php                       tracked
tests/browser/support/base-test.ts                    tracked
tests/browser/support/identity-preflight.ts           tracked
tests/browser/support/server-identity.ts              tracked
```

`git status` in the shared checkout shows only `?? .headroom/` and `?? .recovery-2026-11-25/` (not mine —
peer/recovery artifacts). This session issued **no** commit, push, stage, reset, checkout or stash operation.

## 7. Remaining dependencies / not claimed

- **Not claimed:** is this a full-suite browser sweep. I ran the three identity/isolation files only — the
  Check #3 scope. The other ~140 specs were not run (concurrent-agent rule; a full sweep is Fullstack's
  integration call, not a per-check prerequisite).
- **Dependency on Backend (#1/#4/#5):** the browser role's safety is enforced by the same
  `DatabaseGuard`/`SafeDatabaseManager` boundary that Backend is repairing. This evidence shows the harness
  refuses correctly **as of `b6e5120e`**; if Backend changes `DatabaseGuard::resolvedPath()` semantics or the
  `UMA_*` contract, re-run command in §1 before closing #3.
- **Tracker row:** `docs/sitrep/DB-SAFETY-ACCEPTANCE-2026-11-25.md` still lists `#3 BLOCKED` and routes
  FS-INT-5 to me. That file is Fullstack-owned — **Fullstack should flip #3 to PASS** citing §1–§5 above. I
  did not edit it (single authoritative tracker, not my write authority).
- **Two open behavioural notes** (neither blocks #3): the preflight is cached per worker
  (`lastVerifiedIdentity`), so a swap is caught at the *next* test rather than mid-test (case 9 covers that
  window); and `mandatory-preflight-coverage` is a static-source check, so it proves the import rule, not
  runtime identity for specs other than the ones run here.

## 8. Reconciliation with the 2026-11-26 baseline (added on resume)

Read-only reconciliation; no commit, checkout, branch switch or canonical mutation. The 15/15 result
in §1–§5 is preserved unchanged — this section only pins it to a tree identity and records what the
resume pass measured.

### 8.1 Tested identity — tree equality with the baseline

| Ref | Commit | Tree |
|---|---|---|
| Tested (evidence run) | `b6e5120e61f679c43ea2565989ec7cdc021f7103` (`Merge branch 'master' into backend`) | `42120b3016ce6c7e139f328958adc60dc6b3e03d` |
| `master` (current HEAD, `8a9d4f8d`) | `8a9d4f8da2168c30407f53872cdb57791e0f72a5` | `42120b3016ce6c7e139f328958adc60dc6b3e03d` |
| `frontend` (assigned branch, `6bd24a95`) | `6bd24a95897ea9e7b485697441d159f3ccb3e55d` | `42120b3016ce6c7e139f328958adc60dc6b3e03d` |

All three are the **same tree object** `42120b30`. `b6e5120e` is a backend merge commit not in
`master`'s ancestry (`git merge-base --is-ancestor b6e5120e master` → false), but its tree is
byte-identical to the current baseline, so this evidence applies to the baseline without re-running.

### 8.2 Harness identity — blob equality across refs

`tests/browser/browser-isolation-contract.spec.ts`, `tests/browser/mandatory-preflight-coverage.spec.ts`,
`tests/browser/scratch-isolation.spec.ts`, `tests/browser/support/base-test.ts`, `playwright.config.ts`
are **identical blobs** in worktree, `frontend` and `master` (single blob hash each). The rules the run
exercised (`identityPreflight` auto-fixture, banned direct `@playwright/test` import, loopback probe)
are the same rules on the assigned branch. No harness drift between tested tree and branch.

### 8.3 Worker / retry evidence (why 15/15 is a first-attempt result)

`playwright.config.ts`: `fullyParallel: false`, `workers: 1`, `retries: 0`, `timeout: 180_000`,
`expect.timeout: 15_000`, `webServer.reuseExistingServer: false`. Serial single worker with **zero
retries** means each of the 15 passes is a first-attempt pass — no pass is a retry masking a flake, and
no reused server could have carried a stale database.

### 8.4 Cleanup result

- Evidence-run scratch database **retained by design**: `.scratch-uma/sqlite/browser-scratch-26212-1795653164955.sqlite`
  (3.4 MB, mtime `2026-11-26 08:34 +08:00`). `global-teardown.ts` documents the scratch DB as
  *intentionally left* — it is not a leaked canonical artifact.
- Canonical DB untouched (§5 stands): mtime predates the run, nine counts match baseline,
  `integrity_check ok`, `foreign_key_check` 0 violations.
- Accumulation backlog: `.scratch-uma/sqlite/` holds **1054 `.sqlite` files, 2.6 GB**; 30 `browser-*`
  probe dirs. `.scratch-uma/` is gitignored (`.gitignore:137`). Reclaiming space is a housekeeping
  call for the Coordinator, not a Check #3 condition, and was **not** performed on resume (no write
  authority confirmed; deletion is destructive and outside scope).

### 8.5 Remaining limitations (unchanged, restated against baseline)

1. Scope is the three identity/isolation files (15 tests), not the ~140-spec full browser sweep.
2. Dependency on Backend #1/#4/#5: if `DatabaseGuard::resolvedPath()` semantics or the `UMA_*`
   contract change, re-run §1 before any #3 disposition.
3. Tracker row: `docs/sitrep/DB-SAFETY-ACCEPTANCE-2026-11-25.md` still lists `#3 BLOCKED`; that file
   is Fullstack-owned. Frontend does not flip it.
4. Behavioural notes: preflight cached per worker (`lastVerifiedIdentity`) → swap caught at the next
   test (case 9 covers the window); `mandatory-preflight-coverage` is a static-source check, proving
   the import rule, not runtime identity for unrun specs.

## 9. Coordination request (Frontend → Coordinator, Fullstack)

Ownership: this document lives beside the peer handoffs in `docs/handoffs/`. That folder is **not in
the `AGENTS.md` §3 doc map** (which sanctions `docs/sitrep/` for session reports/handoffs and
`docs/research-scratch/` masters for process docs, and bars inventing folders). Peers already placed
`frontend-database-sources-contract.md` and `fullstack-integration-baseline-2026-11-26.md` here.
**Coordinator action:** rule whether this evidence is integrated by moving/landing it into the
sanctioned home (`docs/sitrep/` or the owning master) or by registering `docs/handoffs/` in the map.
Frontend will follow that ruling; it will not commit tracked files without the exclusive window.

**Frontend remaining functional requirements — determination:** *none independently actionable.*
Every frontend-side blocker resolves to a Backend payload contract still open on the current tree:

- `team_race_calendar` — missing from team payload (`UnityCupTeamRaceSlotTest.php:157,183`).
- `team_race_countdown` — prop absent / validation absent / stale on scenario switch
  (`TeamRaceCountdownTest.php:39..184`).
- `reference` on Timeline / Veterans — panel renders (`Timeline.vue:275`, `Veterans/Show.vue:138`),
  but **no `reference` prop found in `app/Http/Controllers`** on the reconciled tree; the controller
  side is not located.

Per the resume instruction, implementation authority is released through the Coordinator rather than
introducing changes: **no frontend harness or component change is warranted now.** Resume integrated
frontend functional verification when (a) Backend lands the three contracts and (b) an exclusive
Frontend write window is granted.

**Not done, deliberately:** Check #3 tracker row not edited (Fullstack-owned); no successful
verification repeated (no demonstrated need — tree is identical); no new branch, merge, commit, push,
reset, stash, migration or canonical-DB mutation.
