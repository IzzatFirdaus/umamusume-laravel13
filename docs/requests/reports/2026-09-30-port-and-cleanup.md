# Port three artifacts, withdraw ADR-0013, delete the branch — run report

**verified-against:** `a4da6d1` (master tip at the close of this run). All counts re-derived against
the on-disk saved body `research-scratch/data/json/characters.json` (manifest hash `c6676539` at
measurement), not a live fetch — see §6.

## 1. Preconditions

| # | Check | Result |
|---|---|---|
| 1 | `git fetch`; divergence at start | `behind=0`, `ahead=21`. No fast-forward (origin/master had not moved past local). |
| 2 | `git rev-parse HEAD` at start | `85b37a40e3bdc93e059bf9ec00c4d3a6ac53d95a` |
| 3 | `php artisan test --compact` | green: 826 passed, 2 skipped, 0 failed (baseline, with the peer's tree changes present) |
| 4 | `git status --short` clean | **FAILED on first check** — 5 tracked files carried an uncommitted peer changeset, including a PRD A-7 re-asserting the `va_en` defect Step 1 fixes. |
| 5 | branch exists | `feat/umamusume-detail-page` = `8ffab63`, checked out in worktree `D:/Projects/umamusume-laravel13-catalog-roster`. |

Precondition 4 was surfaced to the human owner before any edit. Disposition chosen: **commit the peer's
work as its own preservation commit, then run the sequence.** The 5-file changeset (KNOWN-ISSUES, PLAN,
PRD, ADR-0012, SkillAutomationTest — the brief said "four files"; it is five) was last written 04:30–04:54,
idle ~2h, and I verified none of its hunks lands `e22d05e`'s "withdraw Decision 4" direction onto master
before committing it.

## 2. Commits landed (oldest → newest)

| SHA | Step | What |
|---|---|---|
| `71fabfc` | (pre) | preserve the pre-existing 5-file peer changeset |
| `5774a8b` | 1 | `va_en` docblock correction + characterization pin |
| `88d2830` | 2 (net-new) | `race === 'uma'` filter + test moves |
| `3bac088` | 5 | PRD A-7 `va_en` reading + uma-scope |
| `d6670d8` | 3 | port store-side allowlist guard + all-null coercion (parser-side already covered) |
| `4ba2962` | 2 (artifact) | port the probe record + correct the migration `va_en` comment |
| `5a50900` | 4 | withdraw ADR-0013, land on master |
| `a4da6d1` | 7 | two PLAN.md exit criteria (checkpoint, collision) |

## 3. The `va_en` defect

Master's parser docblock claimed `va_en` is "the English dub cast" and `va_ja` the romanised cast, built
on the wrong denominator (163/26). The body refutes it: `va_ja` 和氣あず未 / `va_en` Azumi Waki / `va_ko`
와키 아즈미 is one performer in three scripts; `Machico`/`Lynn` rows hold the identical string across all
three. Absences are missing romanisations (3 of the 105 trainee rows), not missing dub credits.

Corrected in three surfaces, each as dated errata quoting the withdrawn claim (not a silent rewrite):
parser docblock (`5774a8b`), PRD A-7 (`3bac088`), and the **migration comment** (`4ba2962` — the migration
carried the identical wrong reading; Step 1 named only the docblock, I found it while porting and fixed it
so master holds one consistent reading across code, PRD and schema).

## 4. RED-then-GREEN evidence — stated honestly, not staged

**The one genuine executable RED this run was the race filter,** not the `va_en` pin:

```
$ php artisan test --compact tests/Feature/GametoraCharacterProfileParserTest.php   # BEFORE the filter
Tests: 1 failed, 11 passed
  keeps only rows whose race is exactly "uma"...
    --- (6 expected: 1,2)
    + (6: kept char:3 race:false, char:4 human, char:5 unknown, char:6 no-key)

$ ... # AFTER `race !== 'uma' -> return null`
Tests: 12 passed (43 assertions)
```

The **`va_en` pin** and the **two ported guards** (store-side refused-key drop, all-null coercion) are
**characterization tests: green by construction against master's already-correct parser/store, red on a
regression.** A docblock correction has no executable RED (the code was right; only the comment was
wrong), and re-derivation says so rather than performing a fake red-green. That the brief listed them
under "RED-then-GREEN evidence" is a premise that did not hold — flagged, not papered over.

## 5. The race filter — net-new behavior, not a port

A-7 promises the profile is "sourced from the `characters` document filtered to `race === 'uma'`."
**Neither master nor the branch implemented this** — both lean on the ten-key field allowlist and iterate
all 163 rows. So the filter is behavior the brief did not ask for and the port made real: without it,
`StoreCharacterProfiles` would attach a profile block — and, since 78 of 105 trainee rows carry a non-null
`rl.death`, potentially a death record — to real-world namesake rows. Measured: 105 `uma`, 58 refused
(17 `false`, 4 `human`, 2 `unknown`, 35 key-absent).

Blast radius (all repaired, suite green after): the filter drops fixture row 1095 "Believe" (`race:false`),
which five master tests had been using *as a trainee* — including `StoreCharacterProfilesTest:121`
("stores a null" via `external_ref=gametora:char:1095`). That test was built on the unfiltered-parser bug;
1095 is exactly the namesake the filter refuses. Repointed to 9040 "Darley Arabian" (genuine `uma` row with
absent `va_en`), preserving the "store the null when absent" intent. `git grep 1095 HEAD -- tests/` after
the change confirms no other profile test depends on it (remaining hits are the fixture row + the
intentional refusal assertions; the `1007`/`9044` hits are card-domain, different grain).

Per the human's decision, fixture row 1095 is **kept as the in-fixture refused case** — the `resolves
every trainee row` count 4→3 assertion documents the filter itself.

## 6. Count reconciliation

**No count failed to reproduce.** Both denominators verified against the saved body (live re-run):

- file-wide (163): `va_en` 137/26, `birth_year` 146/17, `three_sizes` 127/36, `rl.death` 90, race-histogram
  uma 105 / false 17 / human 4 / unknown 2 / none 35, `playable_en` 68/95, `jp_name_real` 3/160 — all match.
- uma-scoped (105): `va_en` 102/**3**, `birth_year` 98/**7**, `three_sizes` 98/**7**, `rl.death` **78 = 74.3%**,
  8 distinct key sets, top-level (excl `rl`) nulls/empties = 0 — all match.

The correction is **denominator, not measurement**: the branch's coverage figures are true of the whole
document but the parser keeps only the 105 trainee rows, so the operative gaps are 3/7/7, not 26/17/36.
Re-scoping to 105 removes the false implication that the trainee profile is materially sparse. (The brief's
own `characters.c6676539.json` path is stale on disk — it is `characters.json` — corrected in the probe. No
banned C-4 term is typed in any changed file; the ten-key docblock lead uses "real-world namesake.")

## 7. The three ports — and why the third artifact is a placement, not a branch edit

1. **Probe record** — ported to `docs/data/2026-09-30-characters-source-probe.md` with the path correction,
   the saved-body/rotation disclosure (a live re-fetch may diverge; same shape as the roster `e9e9ee6d`
   correction — the source rotated once before, `679f7c2e`→`e9e9ee6d`), the `n=105` re-scope, the 8-key-set
   finding, and the `rl` obligation restated as a **tested parser+store guard** pointing at the now-landed
   master tests.
2. **Tests** — ported only the two that are new (store-side refused-key drop; all-null coercion). The
   **parser-side mutation probe was NOT ported**: master's 11-key projection pin and the Step-2
   refused-namesake fixture already prove what the parser must not emit. Duplicating would inflate
   coverage without adding a check. All ported names re-bound to master's schema (`height` not `height_cm`,
   `three_sizes_*` not `bust/waist/hip_cm`).
3. **ADR-0013** — withdrawn and placed **directly on master**, not edited on the branch then copied: the
   source branch is deleted in §9, so only the durable branch matters and copying-then-deleting would lose
   the edit. The withdrawal names where each piece goes (field inventory + coverage → ADR-0012 Decision 4 +
   probe; schema decision moot because master's `182820` landed first; drafted FR-A-7 landed as `A-7`). The
   file body is kept as a record; nothing deleted.

## 8. Contradictory records found — quarantined, not reconciled

`e22d05e` ("supersede ADR-0012 Decision 4 **in favour of** ADR-0013") and `47a60bb` ("record
feat/catalog-detail-page as superseded") live on **`feat/catalog-detail-page`** — a *different* branch from
the deletion target, holding a disposition **opposite** to what this brief's Step 4 executed. Both survive
the deletion untouched (confirmed `git branch --contains`). They are left as a record of a moment an earlier
session believed the reverse; reconciling them would require a decision outside this brief's scope, and
neither this nor the implementer can signal the peer session from inside the worktree boundary. `e22d05e`'s
marker **supersedes/withdraws** Decision 4 (its first line reads "SUPERSEDED — withdrawn. See ADR-0013") —
not annotates it. On master the opposite holds: **Decision 4 is the authority, ADR-0013 withdrawn.**

## 9. Branch deletion + recovery

`feat/umamusume-detail-page` had **10 commits** not on master, all unpushed (no `origin/` tracking) and
unmerged — so `-D` discards irreversibly. Recovery SHA if needed before reflog expiry:
`8ffab6363492e81f50043f90460f2909f94a3767`. Worktree `D:/Projects/umamusume-laravel13-catalog-roster` was
**clean (0 changes)** and removed first; the branch then deleted. Confirmed gone. `feat/catalog-detail-page`
and `feat/catalog-roster-and-trainee-selector` both **INTACT** (never deletion targets).

## 10. Open gaps flagged, not silently dropped

- **Profile `down()` rollback is unproven on master.** The branch had a rollback test
  (`CharacterProfileTest.php:183`, in-memory `profile_rollback` connection) proving `migrate:rollback` drops
  only `umamusume_profiles`. It is not one of the three named artifacts and the brief said not to port the
  branch's other tests, so it was **not** ported. Master's migration has a `dropIfExists` `down()` with no
  test. Recorded as a coverage gap for the owner, not hidden by the port.
- **Live re-verification of the saved body** was deliberately not done (source-rotation risk; a divergent
  re-fetch would break the probe's reproducibility). It is its own future task with its own record.

## 11. Which port was hardest

The **test port**, not the probe or the ADR. The probe was text re-scoping (mechanical once measured) and
the ADR was a placement. The test port required, per test: a duplication verdict against master's broader
coverage (rejecting the parser-side probe as already covered), name re-binding across two divergent schemas,
and catching that `StoreCharacterProfilesTest:121` was built on the unfiltered-parser premise — a real
defect the filter exposed, where the "obvious" fix (just change 4→3) would have left a test asserting on
refused data. Making it *correct* rather than *green* was the work.

## 12. Final gate evidence (tree `a4da6d1`)

```
Tests:    2 skipped, 830 passed (2890 assertions)      # full suite
[OK]    No errors                                       # phpstan level 6
pint --test: passed                                     # C-3
lore-code: 8 hit(s)   lore-docs: 116 hit(s), 57 exempt  # == baselines, no new banned-term hit
tsc --noEmit: exit 0                                    # C-9 (no .ts touched this run)
ahead=29 behind=0 vs origin/master                      # 8 commits landed, all local
```
