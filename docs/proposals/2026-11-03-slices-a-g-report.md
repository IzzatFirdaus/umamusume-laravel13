# Slices A–G — execution report (2026-11-03)

**Dispatch state:** HEAD `82551fdf`, 3 commits ahead of `origin/master`.
**End state:** HEAD moves to this report's commit; `origin/master` is still `78755486`; push
remains owner-gated.

## What happened, and who did it

The dispatch assumed the peer was only on the Snapshot surface. In fact a **concurrent session was
executing the same dispatch**, and by the time this pass read the tree it had already landed
Slices A/B, C, D, E and G.1–G.4 (commits `4f5b9d3d`, `cbaed673`, `e4786df4`, `c91458f4`,
`6f3d5a81`), with E and G still uncommitted and being edited at 13:21–13:39. The owner directed
this pass to take over. It did not duplicate that work: it **verified every slice**, added the
missing regression coverage, ran the Slice E proof, ran the gates and targeted browser set,
classified failures, and wrote this record plus KI-151. Where the landed commit's subject differs
from the dispatch's prescribed subject, the landed subject is what is recorded below.

## Slice status

| Slice | Landed in | Verification | Method |
|---|---|---|---|
| A — SnapshotSetup-F-01 | `4f5b9d3d` | `routes/web.php:113` POST `career.snapshot.review.submit` + `:114` GET `career.snapshot.review`; `SnapshotController` reads the body bag; `career-snapshot.spec.ts:53` asserts an empty query string; feature tests converted in the same commit | RENDERED (career-snapshot 14/14 in the targeted run) |
| B — SnapshotSetup-F-03 | `4f5b9d3d` (folded in) | `Setup.vue` has no `action` prop (only the `#actions` slot); `SnapshotController:119` is the review's own action; `SnapshotEntryTest` assertion dropped | SOURCE |
| C — RunsIndex-F-01 + VeteranRow | `cbaed673` | `Runs/Index.vue` and `components/VeteranRow.vue` pass `run.status`/`veteran.status`; `data-status` carried; `app/Services/Legacy/VeteranRow.php` in the same commit | SOURCE + runs-index 3/3 |
| D — LegacyCompare-F-01 | `e4786df4` | `'single column'` joins the neutral branch; two `not.toHaveClass(/pill-caution/)` assertions; the `4657be9e` guard stays reverted | RENDERED (legacy 9/9) |
| E — `.env.example` SQLite | `c91458f4` | fresh clone of `9a61197f`, `cp .env.example .env` **unedited** (DB_CONNECTION=sqlite, mysql block commented), `migrate --seed` ✓, `npm ci` ✓, `build` ✓ | RENDERED (see §E) |
| G.1 — calendar refusal focus | `6f3d5a81` + spec `9a61197f` | alert gets `ref`/`tabindex="-1"`, watcher moves focus; new spec forges only the row id and lets the server refuse | RENDERED; **fails on pre-fix source + bundle** |
| G.2 — reset arm announcement | `6f3d5a81` + spec `2b61a1da` | `resetStatus` written on arm; `role=status` asserts "Reset armed."; button `aria-label` asserts `/five seconds/` | RENDERED |
| G.3 — Preferences focus regression | `6f3d5a81` + specs `6f3d5a81`, `9a61197f` | `firstControlId()` covers `settings.default_scenario` and `settings.stat_target_defaults.*`; two specs, one per family | RENDERED; **both fail on pre-fix source + bundle** |
| G.4 — SupportCards summary tables | `6f3d5a81` | wrap applied; `/support-cards` removed from `layout-compositions.spec.ts`'s exclusion list | RENDERED |

### E — fresh-clone proof (this pass)

```
clone of 9a61197f into .scratch-uma/20261103-1455/verify-clone
composer install            OK
cp .env.example .env        (no edits)  ->  DB_CONNECTION=sqlite; # DB_CONNECTION=mysql etc. commented
php artisan key:generate    OK
php artisan migrate --seed  OK  (all three 2026-11-02 migrations DONE)
npm ci                      OK
npm run build               OK (16.37s)
php artisan test --compact  28 failed, 9 warnings, 7 skipped, 2089 passed (33981 assertions), 279s
```

**KI-102 is closed by behaviour, not by prose:** the literal `cp .env.example .env && php artisan
migrate --seed` now runs with no `DB_*` edits.

**Clone test failures, classified.** 18 classes fail. Running the same 18 files in this unmodified
dev tree gives **23 failed / 188 passed** — so 23 of the 28 are pre-existing repository debt, not
clone-specific. The remaining ~5 are clone-environment expectations in `DatabaseSafetyTest`
(canonical-path/role assertions) and similar; this pass does not claim a per-test mapping beyond
the 23 it reproduced here.

### F — gates (this pass)

```
npm run typecheck           exit 0
npm run lint:ui             exit 0, 207 finding rows (tool total 509), <= 209 baseline
npm run build               exit 0
pint --test (changed PHP)   passed
phpstan analyse             11 errors, unchanged — red-by-debt, no baseline, no suppressions
public/hot                  absent
```

Targeted browser set (career-snapshot, legacy, runs-index, career-race-decision, preferences,
layout-compositions, trainee-profile, race-detail), one process, session-private DB:

```
59 passed, 2 failed, 2 skipped (3.3m)
```

Both failures are **pre-existing**, verified by running them against `e4786df4` with the bundle
rebuilt: `career-race-decision.spec.ts:211` (44px/axe count 8) and `preferences.spec.ts:272` (the
spec still expects a "Download a backup" button; the page has said "Write a backup" since
`c2967b36`). Neither is caused by this pass.

### G.3 — load-bearing proof

With `Preferences/Edit.vue` and `Career/RaceDecision.vue` checked out from `e4786df4` **and the
bundle rebuilt**, both new specs fail; with HEAD restored and rebuilt, both pass. (The first
attempt without a rebuild passed on both sides — the server serves `public/build`, so a source-only
checkout proves nothing. That mistake is recorded rather than hidden.)

## Documentation

- this report;
- `KNOWN-ISSUES.md` — **KI-151**, Dashboard-F-01 type-scale drift, filed as OPEN with its closure
  scope (KI-102 is now behaviourally closed; no separate entry was needed).

## Deviations

1. **Concurrency.** The dispatch's model of the peer (Snapshot only) was incomplete: another
   session was executing this same dispatch. Slices A–E and G.1–G.4 landed from it; this pass
   verified and completed the test coverage rather than re-implementing.
2. **Commit granularity.** The dispatch prescribed one commit per slice with AUD-tagged subjects.
   The landed commits group differently (`4f5b9d3d` A+B, `6f3d5a81` G.1–G.4). Subjects were not
   rewritten on unpushed-but-authored-by-another-session commits.
3. **Two extra spec commits** (`9a61197f`, `2b61a1da`) exist because the concurrent session left
   the calendar and default-scenario focus cases unwritten and the G.2 label half unasserted.
4. **Clone PHP failures** are reported as a set with the 23-reproduced-in-dev-tree split, not a
   per-test table; the console's ANSI wrapping defeats a clean automated mapping and the pass did
   not hand-build one.

## Unpushed

`git rev-list --count origin/master..HEAD` is recorded in the final status; all of it is local.
