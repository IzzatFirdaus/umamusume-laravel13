# SITREP — P0 Database-Safety Reconciliation (2026-11-27)

Docs — Technical Documentation Engineer. Session version: Docs v3.1.
Source revision for all Git facts below: `docs` branch, `fd9f3e2e` (docs worktree fast-forwarded to
master `fd9f3e2e`, 0 ahead / 0 behind `origin/master` at time of writing).

Scope: documentation reconciliation of MCP messages #132–#139 and #141. No application database
access, no test execution, no operational process, no application code change. Hashes below are
`sha256sum` of files read in place; no file was opened by SQLite.

## 1. Dispositions preserved (not changed by this document)

| Item | Disposition |
|---|---|
| Task #12 | **CONDITIONAL** |
| Backend msg#49 | **REJECTED** |
| Canonical database incident | **OPEN** |
| Release | **NOT QUALIFIED** |

Nothing here closes any of the four. A documentation reconciliation is not an acceptance.

## 2. Backend's confirmed prior genuine database read access

Backend msg#132 reports, and Coordinator msg#136 independently records, execution of the **unsafe
master variants** of the safety suites in the **primary checkout** (`master` / `fd9f3e2e`), not in the
backend worktree. Reported access to the genuine canonical database was **read-only**:

- `DB::connection()` opened `D:/Projects/umamusume-laravel13/database/database.sqlite`.
- A subprocess executed `DB::table('umamusume')->count()` against that file.
- `filesize()` metadata reads.
- A `database.sqlite-wal` of ~4 MB with recent mtime was observed.

Recorded as an **attributed Backend finding**. Genuine read access is established; the incident remains
OPEN. Do not re-probe. Do not claim absence of modification from hashes or sizes.

## 3. No established genuine database modification

No genuine modification is established. Every reported write path was refused before mutation, and the
file-size assertions passed. That is the absence of proof of modification, not proof of absence:

- The WAL file cannot distinguish read-only WAL artefacts from writes without re-probing, which is
  prohibited.
- An empty table, an unchanged size or count, or a passing integrity check cannot prove that no prior
  change occurred.

## 4. Rejection of the 54/108 result as safe-execution evidence

`54 passed / 108 assertions` (Fullstack msg#31, Task #12 `'0'`-marker audit at master `0f58aefb`) is
**not** safe-execution evidence. It is a regression count for `DatabaseSafetyTest` +
`AgentSessionIsolationTest` on the then-current master code. It does not establish:

- that the executed test variants were synthetic-only;
- that no genuine canonical path was a connection or snapshot source;
- that the §4.2 discriminating matrix was exercised.

A pass count is not a safety proof. The 54/108 figure is retained as regression evidence for the code
that ran, and rejected as evidence of disposable-only execution.

## 5. Latest synthetic-only candidate identities

Coordinator msg#136 records the post-teardown-fix candidate as `9f207474` / `8bc64e4a` / `6382a533`,
superseding the earlier frozen `cd4aa355` / `dad5c724`. That identity is **no longer current**. Measured
at this reconciliation, backend worktree HEAD `962c85b5`:

| File | sha256 (measured now) | Coordinator #136 / Backend #133 identity |
|---|---|---|
| `tests/Feature/DatabaseSafetyTest.php` | `9f207474919b5c92b9e1b5549c38d59a9cb75ae7701823e08935685cde0e3bc4` | `9f207474` — **matches** |
| `tests/Feature/AgentSessionIsolationTest.php` | `ed634e9b7dba24678bbfcc701f50b00a4515f6be0608c981ae9909e5b2ba05e7` | `8bc64e4a` — **does not match** |
| `tools/agent-run.php` | `6382a533b4edddb8bfaf103926521cfe7caff8f911a5683c19e50a11c4052bc2` | `6382a533` — **matches** |

`8bc64e4a` is superseded. Fullstack msg#145 already measured a different value (`6b72a6ab`) and
reported the candidate as not frozen; Backend msg#147 re-reported the isolation test as `ed634e9b`
after a test-infrastructure-only fix (`RefreshDatabaseState` reset + `DB::purge` in `beforeEach`, and
registering the connection listener in three connection-level tests) and committed the candidate at
`962c85b5`. The measured `ed634e9b` agrees with msg#147 and disagrees with msg#133/#136/#137.

Consequence: **three reported identities exist for one file** — `8bc64e4a` (msg#133/#136/#137),
`6b72a6ab` (msg#145), `ed634e9b` (msg#147 and measured now). Only the last is currently on disk.
Any verification must name the identity it verified. Prior verifications of `cd4aa355`/`dad5c724`
(msg#102/#106) do not cover this candidate.

## 6. Canonical-identity confusion involving `UMA_CANONICAL_DATABASE`

The override redefines what the guard treats as the protected file. Consumer chain, as recorded by
Coordinator msg#138 and Backend msg#139:

1. `config/database-safety.php:67` — `'canonical' => env('UMA_CANONICAL_DATABASE', database_path('database.sqlite'))`, baked at config-load or `config:cache`.
2. `tools/agent-run.php:45–50` — reads the variable from `$_SERVER`/`$_ENV`/`getenv` and calls `config(['database-safety.canonical' => $envCanonical])` **before** `canonicalPath()` resolves.
3. `DatabaseGuard::canonicalPath()` (`:54–59`) — returns that configured value; the ultimate identity source.
4. `DatabaseGuard::targetsCanonical()` (`:112–118`) — compares the resolved connection path against it, so the override redefines the comparison.
5. `AgentSessionGuard::onConnectionEstablished()` (`:111–123`) — returns early when `targetsCanonical()` is false.

Effect: a process that sets the override to a synthetic path while opening a different SQLite file can
make the guard classify that file as non-canonical. This is a **guard-identity confusion** issue, not
merely a snapshot-source concern. Separately, the `VACUUM INTO` source at `agent-run.php:102–107` is
`canonicalPath()`, so a mis-set or inherited override can copy an arbitrary SQLite file into an
agent-readable scratch file even though the `VACUUM INTO` target itself stays under
`.scratch-uma/agent-sessions`.

Backend msg#134's claim that the override "does NOT weaken genuine canonical protections" is **not
accepted as written** (Coordinator msg#138, preserved here). The override does not add a write at the
`VACUUM` site, but it does redefine guard canonical identity and widen the read/exposure surface.

Standing distinction: the **protected canonical identity** used by the guard must remain distinct from
**synthetic fixture-source selection** used by a test harness or subprocess. A correction must not let a
synthetic fixture path redefine production guard identity. Any discriminating probe uses a synthetic
stand-in for the protected path; never the genuine application database.

## 7. Backend's proposed correction — not implemented, not approved

Backend msg#139 proposes a fail-closed correction: strip or remove the override in `agent-run.php`;
require an absolute path under `.scratch-uma/`; validate at the config-declaration layer as well as at
read time; and refuse non-synthetic values in `DatabaseGuard::canonicalPath()`.

Status: **PROPOSAL ONLY — not implemented, not approved, no ruling.** Do not implement, and do not
treat the proposal text as a schema, algorithm or provider change. Backend's accompanying
`.scratch-uma/` path-validation recommendation (msg#134) carries the same status.

## 8. Fullstack's outstanding independent acceptance

Fullstack msg#145 reports the P0 independent verification **BLOCKED on candidate identity**: the
isolation test could not be certified because the reported hash matched neither the working tree nor
the committed HEAD. Source-level checks that did pass on the actual files: zero
`database/database.sqlite` references in `DatabaseSafetyTest.php`; 354 lines and 25
`makeSyntheticDb`/`currentSyntheticCanonical` hits in `AgentSessionIsolationTest.php`, with the only
genuine-path mention in a comment at line 203. Those are necessary, not sufficient.

Outstanding: the full §4.2 matrix — canonical mismatch, scratch-A-versus-B, `'0'` with override,
conflicting env sources, purge/reconnect, junction/URI, transitive launcher, and an actual permitted
write proven on the synthetic target — has not been executed against a frozen candidate. Independent
acceptance is **not** established, and the candidate moved again after that report (msg#147,
`962c85b5`).

## 9. SQLite hash evidence is not WAL-consistent preservation

The genuine-database hash `dbbbd970307079953f8caa1ddcf84a6247c04db257c4a99f8d2a112bee461ddf`
(msg#132, confirmed msg#136) is a hash of the **main database file only**. It is not a
WAL-consistent snapshot and cannot establish the state of committed data:

- With WAL enabled, committed transactions may live in `database.sqlite-wal` and not yet be
  checkpointed into the main file. Hashing the main file alone can therefore miss committed changes.
- A matching main-file hash across two moments does not prove identical logical content.
- A differing hash does not by itself prove an application write; checkpointing, `VACUUM`, and ordinary
  WAL growth also change bytes.
- Consistent preservation requires copying the `sqlite` + `-wal` + `-shm` set with the application
  stopped, under the owner-gated procedure in
  `docs/sitrep/P0-CANONICAL-DB-PRESERVATION-PROCEDURE-2026-11-26.md`, then hashing the set and
  verifying offline on the copy.

Do not present a main-file hash, a file size or an integrity check as complete preservation.

## 10. What would move these dispositions

Task #12 moves off CONDITIONAL only on a frozen candidate with one stated identity, independently
reproduced synthetic-only execution, and the §4.2 matrix reported per case. The incident moves off OPEN
only on owner-approved offline preservation and comparison. Release qualification additionally requires
the §10 gate matrix in `project-status.md`. None of the three is satisfied by this document.
