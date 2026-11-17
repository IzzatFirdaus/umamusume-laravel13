# Plan 1 of 4 - Land the tree (phase 0) and repair doc truth (phase 1)

**Pair:** `docs/roadmap/remaining-work-roadmap-2026-11-17.md` phase 0 and phase 1.
**Measured at:** `HEAD be8893a2`, `origin/master 0bb3efd0`, 2026-11-17.
**Read first:** AGENTS.md §9 (shared worktree, one Pest process, named pathspecs), §11 (change-safety),
§14 (git). This plan never runs `git add -A`, `git commit -a`, `git checkout HEAD -- .`, `git restore .`,
`git reset --hard`, or `pint --dirty`.

---

## Global constraints for every slice here

- Branch work happens in a worktree cut from `origin/master`, created inside the repo
  (`.scratch-uma/worktrees/`). No directory outside the repository.
- Every commit names explicit pathspecs. The shared working tree holds a peer's unfinished refactor;
  naming a path commits that path's **working-tree** content.
- A slice closes when its command output is quoted, not when its prose is written.
- `composer` works on this host (2.10.2) but `make` does not. Use `php vendor/bin/<tool>` directly.

---

# Slice 0.1 - Land the worktree's product content on a branch from `origin/master`

## Objective

Produce one branch whose diff against `origin/master` is exactly the unlanded product work: the S18 batch,
the peer refactor the two position tests depend on, and the four untracked feature tests. `origin/master`
already carries S1, so S1 must **not** appear in that diff.

## Pre-flight (blocking; run all five)

```bash
cd /d/Projects/umamusume-laravel13
git rev-parse --short HEAD                       # expect be8893a2
git rev-parse --short origin/master              # expect 0bb3efd0
git rev-list --left-right --count origin/master...HEAD   # expect 8  15
git status --porcelain -uall | awk '{print substr($0,1,2)}' | sort | uniq -c
git stash list                                   # record; do NOT pop anything that is not yours
```

If `origin/master` has moved past `0bb3efd0`, re-read §0 of the roadmap and re-derive every figure named here.

## Worktree creation (in-repo)

```bash
mkdir -p .scratch-uma/worktrees
git worktree add .scratch-uma/worktrees/land-2026-11-17 -b feat/land-s18 origin/master
```

Then scaffold the worktree so it can run anything (this is the documented trap):

```bash
cd .scratch-uma/worktrees/land-2026-11-17
# Copies, not links: bootstrap/cache/services.php is what stops "Class not found" on every artisan call.
cp ../../../.env .env
mkdir -p bootstrap/cache
cp ../../../bootstrap/cache/services.php ../../../bootstrap/cache/packages.php bootstrap/cache/ 2>/dev/null || true
# vendor and node_modules may be junctions on Windows; if junction creation is unavailable, copy is acceptable
# for a one-off gate run but costs ~10 minutes.
```

## File inventory to apply

**A. S18 batch** (worktree differs from `HEAD`, carries product change): the roadmap's §1 already establishes
these are the S18 surface fixes. Apply from the shared tree by explicit path:

```
resources/js/pages/Career/Snapshot/Entry.vue
resources/js/pages/Career/Snapshot/Review.vue
resources/js/pages/Career/Timeline.vue
resources/js/pages/Career/TraineeSelect.vue
resources/js/pages/Dashboard.vue
resources/js/pages/Help.vue
resources/js/pages/Preferences/Edit.vue
resources/js/pages/Runs/Index.vue
resources/js/pages/Catalog/Show.vue
resources/js/components/Form/SearchableSkillPicker.vue
resources/js/components/catalog/TraineeCatalog.vue
resources/js/components/career/NextDecision.vue
resources/js/composables/useVisitState.ts
tests/browser/{help,catalog,career-timeline,career-trainee-select,trainee-profile,dashboard,runs,review-detail,career-profile,wizard-parity}.spec.ts
```

**B. Peer refactor the position tests depend on** (apply as its own commit, attributed to that session):

```
app/Http/Controllers/DashboardController.php
app/Services/Career/RaceStripBuilder.php
app/Models/Umamusume.php
tests/Feature/DashboardTest.php
tests/Feature/RunRaceStripTest.php
```

**C. Untracked feature tests** (new files; `git add` them explicitly):

```
tests/Feature/ShippedCopyTruthTest.php
tests/Feature/SnapshotDoorLinkTest.php
tests/Feature/SnapshotOwnedSkillsTest.php
tests/Feature/SnapshotTraineeIdentityTest.php
tests/Feature/TraineeGoalFanGateTest.php
```

**D. Untracked build inputs and their consumers** (required for the tree to build; see slice 0.3):

```
resources/js/polyfills.js
resources/js/components/ui/Sparkline.vue
resources/js/components/ui/StatRing.vue
resources/js/spa.ts                     (already modified: imports ./polyfills)
resources/views/app.blade.php           (already modified)
tests/browser/stat-visuals.spec.ts
tests/browser/fixtures/stat-visuals.ts
tests/browser/fixtures/stat-visuals.html
```

**E. Fixes that are worktree-only but already applied:** `resources/js/pages/Career/RunSettings.vue`,
`resources/js/pages/Career/Snapshot/Setup.vue`, `resources/js/pages/Career/Cockpit/Panels/CareerGoalsPanel.vue`,
`resources/js/components/scenario/{ScenarioCardGrid,UnityCupPanel}.vue`,
`resources/js/components/scenario/cards/GoalGroupCard.vue`, `tests/browser/career-snapshot.spec.ts`.

**F. Deliberately NOT applied:** the 109 worktree deletions (slice 0.5 decides those), the peer's
`app/Models/Umamusume.php` `resolveRouteBinding()` if that session lands it first (check
`git log origin/master -- app/Models/Umamusume.php`), and any `docs/` file under another session's ownership.

## Steps

1. Copy each file from the shared tree into the worktree with the same relative path, then
   `git -C .scratch-uma/worktrees/land-2026-11-17 status --short` to see exactly what landed.
2. Verify S1 is absent from the diff:
   `git -C .scratch-uma/worktrees/land-2026-11-17 diff --name-only origin/master -- app/Actions/CreateSnapshotRun.php app/Http/Controllers/Career/SnapshotController.php app/Http/Requests/StoreSnapshotRequest.php`
   -> **must print nothing**.
3. Commit in four series with conventional subjects:
   - `feat(snapshot): S18 scenario-flow surface corrections` (group A)
   - `fix(career): resolve career position from the stored decision turn` (group B, naming the peer session)
   - `test(copy): pin shipped copy, snapshot doors, owned skills, trainee identity, goal fan gates` (group C)
   - `build(assets): add tooltip polyfills and stat-visual primitives` (group D)
4. Re-run the type gate inside the worktree: `npm run typecheck` -> expect exit 0.
5. Run the narrow Pest set the batch touches:
   `php artisan test --compact --filter='ShippedCopyTruth|SnapshotDoorLink|SnapshotOwnedSkills|SnapshotTraineeIdentity|TraineeGoalFanGate|DashboardTest|RunRaceStripTest' < /dev/null`
6. Push the branch and open a PR. **PR merge is the owner's remote write; do not merge without it.**

## Files created / modified

Branch content only. No file in the shared tree is modified by this slice.

## Acceptance

- `git diff --stat origin/master <branch> -- app resources config routes database tests` lists only
  groups A-E; the six S1 paths are absent.
- `npm run typecheck` exit 0 **inside the worktree**.
- The five named feature-test files exist in the branch and pass.
- `git show --name-only --format= <each new commit>` shows no path outside the list above.

## Risks

- **`DashboardController.php` conflict.** Both sides touch it: `origin/master` has S1's snapshot-door change,
  the worktree adds `careerPosition()`. Resolve in the worktree, never in the shared tree. The worktree's
  version is measured **identical to `origin/master`** except for the peer refactor, so the collision is
  narrower than it looks.
- **`app/Models/Umamusume.php` is the one file that differs from both refs.** Land the union: `getRouteKeyName()`
  plus `resolveRouteBinding()`, because moving binding to `slug` alone 404s every id-shaped link.
- **A peer may land their own branch first.** Check `git log origin/master -- <path>` before applying group B;
  equivalent content landed by another path means the diff comes back empty, which is success, not a failure.

---

# Slice 0.2 - Regenerate `package-lock.json`

## Objective

Make `npm ci` work. Today it does not: `package.json` asks `@inertiajs/vue3 ^3.11.0`, `interestfor ^1.0.14`
and `@oddbird/popover-polyfill ^0.7.3`; the tracked lock pins `2.3.28` and lacks the other two.

## Steps

1. Confirm the drift exactly:
   ```bash
   npm ci --dry-run            # exit 1, EUSAGE, names all five mismatches
   git status --porcelain -- package-lock.json   # must be empty (unmodified) before you touch it
   ```
2. Run `npm install` (**not** `npm update`) in the branch worktree. This resolves only the declared ranges.
3. Re-run `npm ci --dry-run` -> exit 0.
4. Confirm the resolved versions: `npm ls @inertiajs/vue3 interestfor @oddbird/popover-polyfill --depth=0`.

## Files modified

`package-lock.json` (and `package.json` only if a version must move to satisfy the resolver; it should not).

## Acceptance

- `npm ci --dry-run` exit 0 in a clean environment.
- `npm ls` prints `3.11.0` and the two new packages at the versions `package.json` declares.
- The lockfile change rides in the same commit as group D of 0.1 (AGENTS.md §11: never alone).

## Risks

- A registry state newer than 2026-11-15 may resolve a patch the tree has never built against. Mitigate by
  running `npm run build` immediately after and comparing the manifest's asset list.
- `package.json`'s `allowScripts` block is a local convention; `npm ci` ignores it. Do not remove it.

---

# Slice 0.3 - Commit the untracked build inputs

## Objective

A clean clone must be able to build. Three files the modified sources import are untracked, so a fresh
checkout produces a build error rather than an app.

## Steps

1. Prove the imports exist and are untracked:
   ```bash
   git status --porcelain -uall -- resources/js/polyfills.js config/inertia.php resources/js/components/ui/
   grep -n 'polyfills' resources/js/spa.ts          # :7 import './polyfills'
   grep -n 'Sparkline' resources/js/pages/Career/Timeline.vue   # :21 import
   ```
2. Verify the scratch DB of the browser harness cannot accidentally be committed:
   `git check-ignore -v .scratch-uma/sqlite` -> must print a `.gitignore` rule.
3. Commit the four source files plus the three browser-fixture files, in the `build(assets)` commit.
4. Verify: `npm run build` exit 0, then `grep -c polyfills public/build` >= 1.
5. `config/inertia.php` is a published config file. Confirm it carries no secret and no absolute path before
   committing; `INERTIA_SSR_ENABLED` default is `false`, which is what `AGENTS.md` §18 wants.

## Files created

`resources/js/polyfills.js`, `resources/js/components/ui/Sparkline.vue`,
`resources/js/components/ui/StatRing.vue`, `config/inertia.php`,
`tests/browser/stat-visuals.spec.ts`, `tests/browser/fixtures/stat-visuals.ts`,
`tests/browser/fixtures/stat-visuals.html`.

## Acceptance

- Fresh clone of the branch: `npm ci && npm run build` -> exit 0.
- `grep -ril 'polyfills' public/build | wc -l` >= 1.
- `git ls-files resources/js/polyfills.js` prints the path.

---

# Slice 0.4 - `pre-native-build`: restore or re-point

## Objective

Do not leave a workflow calling a script that no longer exists.

## Measured state

- `.github/workflows/nativephp-windows.yml:95` runs `composer run pre-native-build --no-interaction`.
- The worktree removed that script from `composer.json`; `HEAD` and `origin/master` still have it.
- `tools/check-electron-env.php` still exists on disk.

## Steps (recommended: restore)

1. `git show origin/master:composer.json | grep -n 'pre-native-build' -A 3` to read the exact block.
2. Restore the block into `composer.json` verbatim.
3. `composer run pre-native-build` -> exit 0.
4. If the owner instead wants the workflow to stop calling it, edit `.github/workflows/nativephp-windows.yml`
   and prove it with a `workflow_dispatch` run: `gh run list --workflow=nativephp-windows.yml --limit 1`.

## Acceptance

Either `composer run pre-native-build` exits 0, or the workflow no longer references it and a dispatched run
is green. Never both halves removed.

---

# Slice 0.5 - Dispose of the 109 deletions (owner decision P-2)

## Objective

The worktree deletes 90 `jev-inspect/` files (a foreign subproject, still tracked at `HEAD`) and 25
`docs/audits/*.md` paths that were **moved** into subdirectories whose replacements are untracked. Neither
class is this session's work, and both break tooling that walks `git ls-files`.

## Steps

1. Enumerate with their class:
   ```bash
   git status --porcelain -uall | grep '^ D' | awk '{print $2}' | sed 's|/.*||' | sort | uniq -c
   ```
   Expect `jev-inspect` 90, `docs/` 25.
2. For each of the 25 `docs/audits` deletions, prove the replacement exists and is tracked in the branch
   (`git ls-files docs/audits/reconciliation-rounds/...`). A deletion whose replacement is untracked is not
   a move.
3. Commit the deletion of `jev-inspect/` **with its reason in the commit body** (a foreign subproject that
   duplicates another repository), or restore it (`git restore --source=HEAD -- jev-inspect`) if the owner
   wants it kept.
4. Verify no tracked path is left dangling:
   `git status --porcelain -uall | grep -c '^ D'` -> a stated number, each explained.

## Acceptance

`git status --porcelain -uall | grep -c '^ D'` equals the number the owner ruled on; every remaining deletion
is named in the commit body with its reason.

---

# Slice 0.6 - Tree hygiene

## Objective

Stop 273 MB of build output and one artefact file from appearing in every status read.

## Steps

1. Append to `.gitignore` under the build-artifacts section:
   ```
   /win-unpacked/
   ```
   The leading slash matters: without it the rule matches any nested directory of that name.
2. Remove the zero-byte root file created by an unquoted shell glob: `` rm -- "table.t)'" `` (quote it; the
   name contains a quote character).
3. Verify: `git check-ignore -v win-unpacked` prints the new rule; `git status --porcelain -uall | wc -l`
   drops by exactly the 5 `win-unpacked` rows plus 1.

## Acceptance

`git status --porcelain -uall` no longer lists `win-unpacked/` or `` table.t)' ``; the ignore rule names the
path with a leading slash.

---

# Slice 1.1 - Close the six rows the tree already satisfies

## Objective

The register reads 11 OPEN. Six describe defects the tree no longer has. Close them with dated notes that
name a proving command.

## Measured state per row (re-derive each before writing; a peer is editing this file)

| KI | Claim | Measured | Proving command | Action |
| --- | --- | --- | --- | --- |
| 104 | nested `<details>`; fix uncommitted | `UnityCupEditor.vue` `<form>` is the root; the two `<details` hits are comments; `005561c1` is an ancestor | `grep -n '<details' resources/js/components/career/UnityCupEditor.vue`; `git merge-base --is-ancestor 005561c1 HEAD` | Close the **code half** with a dated note; leave the ADR half to 3.5 |
| 106 | ratchet red, 270 dead | 690 dead vs baseline 708; `DocCitationParityTest` 2 passed | `python tools/doc_census.py \| grep 'dead markdown links'` | Close; the body's 270 is superseded |
| 114 | two specs drive a removed picker, 11 red | the eleven literals are absent; `#deck-picker-results` lives on `resources/js/pages/Support/Builder.vue:494` | `grep -c 'Equip owned to Slot' tests/browser/*.spec.ts` -> 0 | Close as spec-migrated (`a58e4942`, `6a44a750`, `9674180d`) |
| 116 | `forLevel()` absent | present at `app/Services/SupportCardEffects.php:209`, consumed at `app/Services/DeckAnalysis.php:109` | `grep -rn 'forLevel' app tests \| head` | Close; residual = slots with no level, by design |
| 119 | no proxy column/prop | migration `2026_11_10_010500`, Fillable, cast, request rule, prop | `ls database/migrations/*current_spirit_proxy*` | Close |
| 127 | bare div, no card | `resources/js/pages/Career/Cockpit/Panels/TeamRaceFacilityPanel.vue:51` is `<div class="card p-3">` | `sed -n '50,54p' resources/js/pages/Career/Cockpit/Panels/TeamRaceFacilityPanel.vue` | Close; record the panel is dead (no scenario composes it) as a separate note |

## Steps

1. Re-run each row's proving command immediately before writing it. A peer edits this file; a line number
   from this plan may be stale.
2. Append to each entry body a dated note in the register's own form:
   `**Dated note 2026-11-17 - CLOSED.** <what was measured> `<command>` -> <output>.`
3. Flip that row's **index** line in the same edit, or the contradiction count rises.
4. Do not renumber, do not delete a body, do not rewrite the original sentence (AGENTS.md §11).

## Files modified

`KNOWN-ISSUES.md` only.

## Acceptance

`python <skill>/scripts/audit_status_register.py KNOWN-ISSUES.md` reports OPEN = 5 (KI-162, 163, 164, 165,
166) and `CONTRADICTIONS (0)`.

## Pitfalls

- **The file has three status spellings** (`**State:**`, `**Status:**`, `**Status: VALUE`). One grep pattern
  measures the pattern, not the file; use the audit script.
- **The file is CRLF with 34 lone LFs.** Edit with a tool that preserves line endings, or diff with
  `git diff --ignore-cr-at-eol` and compare byte counts.
- **Six ids carry two body blocks.** Closing KI-104's code half means closing the block at the *lower*
  line, not the index row's neighbour.

---

# Slice 1.2 - Repair the six index/body contradictions

## Objective

Zero contradictions. In all six, the **index** is the correct half.

| KI | Index | Body | Tree backs | Evidence |
| --- | --- | --- | --- | --- |
| 69 | CLOSED | OPEN | index | `playwright.config.ts:18` takes `DB_DATABASE` from the scratch path; `global-setup.ts` builds it |
| 74 | TERMINAL | OPEN | index | `tools/gate-preflight.php` exists (1565 B); AGENTS.md §9 carries the rule |
| 100 | TERMINAL | OPEN | index | disclosure ships: `app/Services/Career/TeamPanelBuilder.php:400` "What it asks for is not decoded here." |
| 107 | CLOSED | OPEN | index | `CLAUDE.md:12` records the fold; `:257` forbids re-creating the file |
| 117 | CLOSED | OPEN | index | `database/migrations/2026_10_25_130000_add_card_level_to_deck_slots_table.php:19`; `DeckSlot` Fillable + cast |
| 124 | TERMINAL | OPEN | index (owner-facing) | `TurnEntry` Fillable has no `facility_banner`; no migration |

## Steps

1. Re-read each body block at its own line, not at the index line.
2. Append a dated note recording which half governs and the command that shows it.
3. Flip the index row to the body's new status **in the same edit**.

## Acceptance

`audit_status_register.py` reports `CONTRADICTIONS (0)`.

---

# Slice 1.3 - Six ids carry two body blocks

## Objective

116 body headings under 110 distinct ids. KI-70, 71, 101, 102, 103 and 104 each carry two blocks, and in
each pair the second block is either a superseded note or a different defect that reused the number.

## Measured pairs

| Id | First block | Second block |
| --- | --- | --- |
| 70 | concurrent-session shared-file hazard | 44px-floor sweep failure on a cockpit control |
| 71 | 27 career-phase files exist only in the worktree | The Grand Concert final tagged with a key no run carries |
| 101 | harness serves `public/build` without a freshness check | refresh counter counts two navigations |
| 102 | `.env.example` unparseable by the repo's dotenv | NFR-20 item 5 has no test |
| 103 | `SetupDraft::saved()` reaches six pages, none declares it | NFR-20 item 6 has no test |
| 104 | two `Edit team state` disclosures nested | ADR-0027 Proposed, no binding decision |

## Steps

1. For each pair, decide which number **owns** which defect. The rule already used by this register is that a
   later, different subject gets a **new** number; reusing one is how the duplicate arose.
2. Allocate the next free numbers (currently 167 upward) to the second subject of each pair, and retitle that
   heading. Do not delete either block.
3. Add one line to each retitled block: `Renumbered 2026-11-17 from a second block that reused KI-nn.`
4. Update any cross-reference to the moved number (`grep -rn 'KI-70\\|KI-71\\|KI-10[1234]' docs/ KNOWN-ISSUES.md`).

## Acceptance

`grep -cE '^#{2,3} KI-' KNOWN-ISSUES.md` equals the distinct-id count; the audit tool reports no duplicate.

## Pitfall

Numbering is append-only at the **top** of the range. Reusing 167 twice, or renumbering an existing block,
reintroduces the defect.

---

# Slice 1.4 - Agent-facing doc truth

## Objective

Two documents cite paths that do not exist.

## Measured

- `AGENTS.md:195` cites `docs/SKILL_AUTOMATION.md`; the file is absent (the `.agents/` directory exists).
- `PRD.md` §7 links `docs/OPEN-QUESTIONS.md`; the file is absent (the register lives at
  `docs/research-scratch/PLANS-AND-BRIEFS.md`).

## Steps

1. For each citation, find the file that **does** own the subject
   (`ls .agents/`; `grep -n 'OPEN-QUESTIONS' docs/research-scratch/PLANS-AND-BRIEFS.md | head`).
2. Repoint the citation to that path, or append a dated note recording the fold (AGENTS.md §11 favours the note
   for a dated claim, the repoint for a live pointer).
3. `python tools/doc_census.py` before and after: the dead count must not rise.

## Acceptance

`python <skill>/scripts/verify_record_citations.py <edited file>` reports no `MISSING` for those two tokens;
`python tools/doc_census.py` dead count is <= 690.

---

## Gate sequence for this plan

```bash
php vendor/bin/pint --test --format agent <changed php paths>     # none expected in 1.x
php vendor/bin/phpstan analyse --no-progress --memory-limit=1G
npm run typecheck
node tools/ui-framework-check.mjs                                  # 207 arbitrary-value, baseline 209
python tools/md_fence_check.py KNOWN-ISSUES.md AGENTS.md PRD.md
php tools/lore.php; php tools/lore.php code
php artisan test --compact --filter='DocCitationParityTest' < /dev/null
```

Quote each output. A gate that did not run is reported as not run.
