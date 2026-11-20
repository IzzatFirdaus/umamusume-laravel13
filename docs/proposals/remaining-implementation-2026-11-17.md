# Remaining implementation - proposal and owner-ruling package

**Status:** proposal, not a slice. Nothing in this file authorises a code change.
**Date opened:** 2026-11-17
**Source:** owner ask, 2026-11-17: "Every incomplete plans that was documented or not ... create one
roadmap, one proposal, then specific plans."
**Register reference:** `KNOWN-ISSUES.md` (110 index rows, 11 index-OPEN, 6 index/body contradictions)
**Audit reference:** `docs/audits/reconciliation-rounds/rice-shower-unity-cup-round8-*.md`,
`docs/audits/reconciliation-rounds/scenario-flow-surface-s18-handoff.md`
**Ownership:** this proposal recommends; the owner rules. The roadmap states what remains and in what
order (`docs/roadmap/remaining-work-roadmap-2026-11-17.md`); the four plans state how.

---

## 1. Problem

Nine roadmap/plan pairs and four scenario plans sit in the tree, written between 2026-11-14 and 2026-11-17.
Each was correct for the tree it measured. None is correct for this one:

- `docs/roadmap/implementation-roadmap-2026-11-17.md` calls S1 "uncommitted work" in five places. PR #27
  merged it as `0bb3efd0`, and six of the seven S1 paths in the working tree are now **byte-identical to
  `origin/master`** `[M]`.
- `docs/plan/optimal-approach-2026-11-17-s18-release.md` lists five slices. Two are done (§12 of that file
  records them), one was retired rather than fixed, and the remaining two have moved: its "15 ahead / 7
  behind" reading is now "15 ahead / 8 behind", and PR #27 is merged rather than open.
- `docs/roadmap/v3.0.0-release-plan-2026-11-17.md` and `VERSION.md:9` both name
  `full-career-journey.spec.ts` as the tag blocker, with the fix `details[data-region="team-state"]`. The
  file contains no such locator; its identity helper was rewritten to navigate to `/settings` instead. A
  release is being held by a false premise.
- The register reads 11 OPEN rows. Six of them describe defects the tree already fixed, and six rows
  disagree with their own bodies.

The ask is therefore not "write another plan". It is: establish what is actually left, put the owner's
decisions in one place, and write plans an executor can follow without re-deriving anything.

## 2. Evidence

Measured 2026-11-17, 20:00-21:00 MPST, at `HEAD be8893a2` / `origin/master 0bb3efd0`.

| # | Fact | Command / file | Output |
| --- | --- | --- | --- |
| M-1 | Divergence | `git rev-list --left-right --count origin/master...HEAD` | `8  15` |
| M-2 | Working tree | `git status --porcelain -uall \| awk '{print substr($0,1,2)}'` | **measured 2026-11-17 at `be8893a2`: 109 ` D`, 57 ` M`, 86 `??`**; **superseded at `HEAD 2546689e`: 22 rows (1 `M ` / 16 ` M` / 1 ` D` / 4 `??`)** because a peer committed the deletions |
| M-3 | S1 already on `origin/master` | `git diff --name-only origin/master -- <3 S1 paths>` | empty |
| M-4 | Six S1 paths identical to origin | per-file `git show origin/master:<f>` vs worktree | `CreateSnapshotRun`, `SnapshotController`, `DashboardController`, `StoreSnapshotRequest`, `TeamPanelBuilder`, `scenarios.php` all True |
| M-5 | `npm ci` cannot install | `npm ci --dry-run` | exit 1, `EUSAGE`, `lock file's @inertiajs/vue3@2.3.28 does not satisfy 3.11.0`, missing `interestfor`, `@oddbird/popover-polyfill` |
| M-6 | Lock is clean and drifted | `git status --porcelain -- package-lock.json` | empty (unmodified), pinning 2.3.28 while `package.json` asks 3.11.0 |
| M-7 | Untracked build inputs | `git status --porcelain -uall` | `resources/js/polyfills.js` (imported `resources/js/spa.ts:7`), `config/inertia.php`, `ui/{Sparkline,StatRing}.vue` (imported `resources/js/pages/Career/Timeline.vue:21`) |
| M-8 | Workflow calls a removed script | `.github/workflows/nativephp-windows.yml:95` vs `composer.json` | workflow runs `composer run pre-native-build`; the worktree removed that script; `tools/check-electron-env.php` still exists |
| M-9 | Build output untracked | `du -sm win-unpacked`; `git check-ignore win-unpacked` | 273 MB, exit 1 (not ignored) |
| M-10 | Register census | `audit_status_register.py KNOWN-ISSUES.md` | 110 index rows, 116 body headings / 110 ids, 11 OPEN, 6 contradictions |
| M-11 | Browser suite | `npx playwright test --list` | 876 tests in 125 files |
| M-12 | `nativephp-packaged` in the default sweep | same listing | present; the spec throws on line 6 without two env vars |
| M-13 | `career-preflight-indep` drives removed controls | `grep -rn 'Career mode' resources/js` | 0 hits |
| M-14 | Fence tool bare invocation | `python tools/md_fence_check.py` | crash on the first deleted tracked doc |
| M-15 | Fan ladder | `config/uma.php:578-580` | one band, `Star 0-240000` |
| M-16 | Ladder source | `docs/[Rosy_Dreams]Rice_Shower_Unity-Cup.md:531`; `docs/research-scratch/DESIGN-CORPUS.md:1388-1400` | nine tiers with thresholds, two non-numeric |
| M-17 | `FanLadderTest` pins the one-band behaviour | `tests/Feature/FanLadderTest.php:41` | asserts `classFor(240_000)` is null |
| M-18 | KI-166 probe refuted its own candidate | `KNOWN-ISSUES.md` KI-166 dated note | `culprits: []`, `cardHasRuleMatched: true`, `docOverflow: 59` |
| M-19 | No reflow case on the races page | `grep -c '320' tests/browser/career-race-decision.spec.ts` | 0 |
| M-20 | Team Rank has readers, no writer | `grep team_rank app/Http/Requests` | no hits; readers at `app/Http/Controllers/Career/CockpitController.php:535`, `app/Http/Controllers/DashboardController.php:234` |
| M-21 | Unity Cup declares no finale | `grep "'finale'" config/scenarios.php` | only `:1024` (`trackblazer`) |
| M-22 | No team-race slot can exist | `database/seeders/ScenarioSlotSeeder.php:18`; `app/Http/Requests/StoreRaceEntryRequest.php:66` | "zero rows (no citable source yet)"; circles validate against a slot no code creates |
| M-23 | Gates green today | typecheck / phpstan / pint / lint:ui | exit 0 / `[OK] No errors` / `passed` / 207 vs 209 |
| M-24 | `composer test` is not a release gate | `composer.json` `scripts.test` | `config:clear`, `npm run typecheck`, `artisan test` - no pint, phpstan, lint:ui, lore, browser |
| M-25 | CI runs no gate | `grep -nE 'pest\|phpstan\|pint\|typecheck\|lint:ui' .github/workflows/*.yml` | exit 1, no matches |
| M-26 | Dependency audit | `composer audit --no-dev`; `npm audit --omit=dev` | both clean |

**RENDERED_EVIDENCE = PARTIAL.** Browser-accessible tooling refuses loopback on this host, so no rendered
confirmation was taken this pass. Every copy and layout claim above rests on a source read plus, where
noted, the served bundle. The one rendered measurement available (KI-166's 320 px probe) was taken by an
earlier session and is quoted with its own provenance, not re-run here.

## 3. What current code does

Read-only summary; `file:line` throughout.

**Landing state.** `origin/master` carries S1 (`0bb3efd0`, 7 files). `HEAD` (15 local commits, 4 of them
carrying product files) does **not**. The working tree carries S1, the S18 batch, and a peer refactor of
`DashboardController` / `RaceStripBuilder` / `Umamusume::resolveRouteBinding` that the two position tests
depend on. `app/Models/Umamusume.php` is the only file that differs from **both** refs: `origin/master`
has `getRouteKeyName()`, the worktree adds `resolveRouteBinding()` (`:106-132`) which accepts the id form
too, because moving binding to `slug` 404'd every id-shaped link.

**Installability.** `package.json:34-36` asks for `@inertiajs/vue3 ^3.11.0`, `interestfor ^1.0.14` and
`@oddbird/popover-polyfill ^0.7.3`; `package-lock.json` (unmodified, tracked) pins `2.3.28` and lacks all
three. `node_modules` has 3.11.0 installed, so the local build works and a clean install does not. Three
files the modified sources import are untracked, so a clean clone cannot build at all.

**Register.** 110 index rows against 116 body headings under 110 distinct ids: KI-70, 71, 101, 102, 103 and
104 each carry a second block. Eleven rows read OPEN. Six rows contradict their bodies, and in all six the
**index** is the correct half (`playwright.config.ts` owns its scratch DB; `tools/gate-preflight.php`
exists; the undecoded-objective disclosure ships at `app/Services/Career/TeamPanelBuilder.php:400`; `CLAUDE.md` no longer
cites the absent files; `card_level` exists; `TurnEntry` carries no facility-banner column).

**Test surface.** 270 PHP files across `tests/Feature` (257) and `tests/Unit` (13), two deliberate skips,
both on an absent input. 125 browser spec files, 876 executable cases, `workers: 1`, `fullyParallel:
false`, 180 s per case, a per-invocation scratch DB, and a stale-bundle refusal in `global-setup.ts` that
compares the manifest's mtime against the newest `resources/js` file.

**Defects with a named site.** KI-166's overflow has a refuted candidate and no named offender: the table
**is** the card's direct child, the `.card:has(> table.t)` rule **did** apply, and every box with
`right > 320` sits under a scrolling ancestor, yet `document.scrollWidth` is 379. KI-165's ladder is one
band while four documents carry the nine-tier table. KI-163's duplication is three declarations plus two
inline shapes, one of which (`resources/js/pages/Catalog/Show.vue:91`) has diverged. KI-164's positional coupling is 12
assertions in one file.

**Capability gaps.** Team Rank has a column, a payload class, five readers and no writer; the editor prints
a sentence pointing at a turn-event path no UI reaches. Unity Cup's `finale` key is absent, so the scenario
page prints "declares no structure beyond it" while the finale race sits on the calendar. No code creates a
`team_race` slot, so the circles field validates against a row that cannot exist. Roster and preseason
rounds are derived/count-only by recorded decision. The per-ancestor editor ships ranks, stars and sparks
but no guest flag and no identity field.

## 4. Recommendation

**R-1. Land the tree before writing another slice (roadmap 0.1-0.6).** Branch from `origin/master`, apply
the worktree's product content with explicit pathspecs, and prove the result builds from a clean clone. Every
gate in §7 is measured against a tree that cannot be `npm ci`-installed and whose build imports three
untracked files, so any slice executed before this lands on an unstable base. Follows AGENTS.md §14 (named paths, never
`git add -A`) and the shared-worktree rule at §9.

**R-2. Regenerate the lockfile in the same commit series as `package.json`.** The lock is tracked and clean;
the drift is a defect, not a peer artifact. AGENTS.md §11 says a lockfile update ships with the dependency
change, never alone, so 0.2 and 0.3 are one commit.

**R-3. Close the six already-satisfied register rows with dated notes, and repair the six contradictions from
the index side.** The register is the release bar's condition 7, and today it overstates the defect count
by six and contradicts itself in six places. AGENTS.md §11 forbids rewriting an entry; the shape is a dated
closure note naming the proving command.

**R-4. Make the browser suite runnable before believing any of it.** Three separate defects block a clean
sweep: one case fails by construction in the default config (M-12), one spec drives controls that no longer
exist (M-13), and seven probe files carry 9 investigation cases into a release gate (M-11). None is a product
defect; all three make a sweep's result uninterpretable. Fix them, then run the sweep once.

**R-5. Fix KI-165 with the seven sourced bands and record the two unsourceable ones as a bounded absence.**
Four documents in the repo carry the thresholds; inventing a fan count for "Beginner 1st place" would be the
one thing the repo bans outright. `FanLadderTest:41` currently pins the one-band behaviour and must be
re-pinned in the same commit, not deleted.

**R-6. Diagnose KI-166 upward, then fix the smallest wrapper.** The probe already refuted the child-escape
theory; the next instrument is a `scrollWidth` vs `clientWidth` walk from `documentElement` down. The
cockpit's own fix was a three-line wrapper, and the closure bar this row already states is a 320 px case in
`career-race-decision.spec.ts`, which has none today.

**R-7. Ship Team Rank's writer, and stop at the tier boundary that needs a source.** One select, one request
rule, one page change, and the `resources/js/components/career/UnityCupEditor.vue:226` sentence goes. `S+` is already a legal payload
letter and has no facility mapping, which is why it must be accepted by the validator and refused a level by
the ladder - not dropped from the vocabulary.

**R-8. Do not widen 3.0.0 for the three owner-gated capabilities.** Roster, preseason-rounds-as-rows and
team-race slots each need an ADR plus a sub-model or a source that does not exist. Each is recorded, not
built. PRD §6.11 keeps simulation and race-outcome prediction cut, and none of the three needs it.

**R-9. Make the gate sequence one command, and leave CI alone until the owner says otherwise.**
`composer test` is not a release gate (M-24) and CI runs no gate at all (M-25). One `composer gates` script
that runs the seven §9 commands in order turns the local evidence from seven runs into one exit code. Adding
CI enforcement is a separate owner call: this repo's own rule is that the gates are local, and a 23-minute
browser job on a shared runner is a flake factory.

## 4.5 Framework adherence

**Justified by a rule.** R-1 follows AGENTS.md §14 (conventional commits, one slice per series, explicit
paths) and §9's shared-worktree discipline. R-3 follows §11 (append dated notes, never rewrite). R-4 follows
§9's change-class minimums (a browser gate is Level 3 and needs a quiet box). R-5 follows §5's "no fact
without provenance". R-8 follows PRD §6.11 and §6.9/ADR-0029. R-9 follows §2's precedence table: a local-run
rule is the quality bar's, not a tooling preference.

**Constrained by a rule.** R-2 is constrained by §11's "lockfile updates ship with the dependency change,
never alone". R-6 is constrained by §11's defect-register form (dated note, never renumber). R-7 is
constrained by ADR-0029's separation of `team_rank` from `team_rank_ladder`.

**Accepted limitation, stated.** The ladder keeps two tiers unrepresentable (D-10). `FanLadder` answers null
outside its bands by design; that null is a disclosure, not a defect, and the renderer already prints the
bare count for it (`resources/js/components/career/CareerStatePanel.vue:309`).

**Not applicable.** No accessibility carve-out is requested; no token threshold moves; no test is deleted
without an owner ruling (2.3 asks for one).

**Attribution.** Owner rulings already in force and untouched here: ADR-0027 remains Proposed until signed
(3.5); NFR-19 is a terminal deferral for 3.0.0 (`ARCHITECTURE.md:554`); FR-E is six endpoints, terminal
(`ARCHITECTURE.md:329`).

## 5. Rejected alternatives

| Alternative | Mechanism of rejection |
| --- | --- |
| **Tag `be8893a2` directly** | It lacks the 7 S1 files and the 15 local commits are 11 docs-only; the tag path must be `origin/master` plus the landed S18 series (D-1). Tagging `HEAD` publishes a tree that cannot `npm ci`. |
| **Cut a branch from `HEAD` and cherry-pick S1** | S1 is already byte-identical in six of seven paths (M-4). Cherry-picking re-applies merged work and manufactures conflicts in `DashboardController.php`, which both sides touch. |
| **Apply the worktree diff wholesale with `git stash`/`commit -a`** | AGENTS.md §14: naming a path commits that path's working-tree content, other sessions' lines included. The worktree at `be8893a2` holds a peer's unfinished refactor and 109 deletions. |
| **Raise `DOC_CITATION_BASELINE`** | The gate is 18 links *under* baseline. Raising it is the "weaken a threshold to pass" class the quality bar forbids in writing. |
| **Delete the five flash-name probes silently** | A test deletion needs an owner approval plus a stated reason in the commit (AGENTS.md §9/FLOOR). D-5 asks rather than acts. |
| **Fill the fan ladder with nine bands including invented thresholds** | Two tiers have no fan count to key on. Fabricating a stat table is the repo's hardest ban; R-5 ships seven and discloses two. |
| **Add CI enforcement for all gates now** | 876 browser cases at `workers: 1` is ~23 min and flake-prone on a shared runner; the owner has not ruled on CI gating, and PRD §6.10's loopback rule is about the app, not the repo. R-9's local script gets the same assurance at no runner cost. |
| **Treat the 6 "already fixed" KIs as closed by assertion** | Each closure names a proving command in 1.1; a row that cannot quote one stays OPEN. |
| **Build the roster / preseason-rows / team-race slots to "finish" the capability matrix** | Each is owner-gated by a recorded decision (R8 §8.3) and needs schema plus ADR. Building them widens 3.0.0 for no release bar item. |

## 6. Would not change

- `career-preflight.spec.ts` - the Phase E2 cut record, and the counter-spec that proves 2.2's spec stale.
- `docs/adr/0027-*` status - an owner signature, not an agent edit.
- `ARCHITECTURE.md`'s FR-E and NFR-19 rulings.
- `photos`/design tokens, `lang/en/uma.php` vocabulary, the six `/api/v1` endpoints.
- `docs/proposals/{Unity-Cup,Trackblazer,Our-Grand-Concert,URA-Finale}-plan.md` - game-mechanics plans;
  only their finals/team-race items are taken up, and only as 4.2/4.3.
- The peer pair `docs/roadmap/implementation-roadmap-hermes-2026-11-16.md` + its plan.

## 7. Owner decisions

Numbered for the ruling sheet; the roadmap repeats them as D-1..D-12 so a reader of either file sees one set.

| # | Decision | Recommendation | Blocks |
| --- | --- | --- | --- |
| P-1 | Land worktree as one series or split S18 from the peer refactor | Split | 0.1, everything downstream |
| P-2 | `jev-inspect/` - 90 tracked files deleted in the worktree | Commit the deletion with its reason, or restore | 0.5, 2.4 |
| P-3 | `win-unpacked/` 273 MB untracked | Ignore it | 0.6 |
| P-4 | `pre-native-build` script removed while a workflow calls it | Restore the script | 0.4 |
| P-5 | Seven probe specs / 9 cases in the sweep | Retire five `flash-name-*` + `containment-mechanism`; keep `containment.spec.ts` | 2.3, 2.6 |
| P-6 | BO-004 dark risk ink | Re-measure the pair, then close if it clears | 5.1 condition 4 |
| P-7 | `match_candidates` holds 304 race rows, all Pending | Entity discriminator plus ADR, then a race queue | S18-08 |
| P-8 | `data_sources` covers 1 of 14 sources | Backfill or record the terminal gap | S18-09 |
| P-9 | Fan ladder's two non-numeric tiers | Seven bands now, two disclosed | 3.2 |
| P-10 | Team-race slots | Terminal absence until a citable source exists | 4.3 |
| P-11 | Roster and preseason rounds | Keep DEFERRED; do not widen 3.0.0 | 4.4, 4.5 |
| P-12 | ADR-0027 signature | Owner signs or the row stays OPEN | 3.5, 5.1 condition 7 |

## 8. Risk and reversibility

- **Blast radius of R-1** is the largest: it moves the S18 batch, the peer refactor and four untracked
  feature tests onto a new branch. Reverting is a branch delete, because nothing lands on `master` until the
  series is reviewed. The peer's working-tree content stays untouched in the shared tree throughout.
- **R-2's risk** is a lockfile resolved against a different registry state than the 2026-11-15 one. Mitigate
  by running `npm install` (not `npm update`) so only the declared ranges move, then `npm ci --dry-run`.
- **R-3 is text-only** and reversible by `git revert`; the failure mode is a wrongly closed row, which the
  proving command in each note makes checkable.
- **R-4's risk** is a sweep that fails for an environmental reason (contention, stale build). `global-setup`
  already refuses a stale bundle; the quiet-box pre-flight is the second guard.
- **R-5 and R-6 change user-visible behaviour** (a class name and a gap; a layout at 320 px). Both are
  guarded by a test in the same commit, and both are small enough to revert alone.
- **R-7 adds a write path.** It is the only change here that can lose Trainer data if wrong; mitigate with
  the model's existing `saving` guard, a Form Request rule, and a reload assertion.
- **R-9 changes no application behaviour**, no dependency, no schema.

## 9. Proposed slice shape

Not a commitment and not a slice id. The shape is: **land (0.x) -> truth (1.x, 2.x) -> defects (3.x) ->
capabilities (4.x) -> release bar (5.x)**, with phase 3 and 4 gated on phase 0 because the worktree is the
only place the fixes exist. The four plans under `docs/plan/` carry the per-slice steps, files and commands.

## 10. Does not do

- No code, no slice, no dispatch, no commit. This file recommends.
- No new dependency, no new MCP server, no CI workflow.
- No schema change without an ADR (4.3/4.4/4.5 are owner-gated).
- No re-open of a landed slice, no edit of a peer's file, no rewrite of a dated record.
- No browser run in this pass: `RENDERED_EVIDENCE = PARTIAL` and every rendered claim says so.

## 11. Change log

| Date | Change |
| --- | --- |
| 2026-11-17 | Pre-flight: baseline `HEAD be8893a2`, `origin/master 0bb3efd0`, 252 porcelain rows (109 ` D` / 57 ` M` / 86 `??`), index empty. `RENDERED_EVIDENCE = PARTIAL` (loopback refused). Duplicate check: `ls docs/proposals \| grep -iE 'remaining\|implementation'` -> 0 matches; body grep for `Team Rank`, `fan_ladder`, `KI-166` in `docs/proposals/` -> 0 matches. Opened with §1-§11. Twelve decisions separated into roadmap D-1..D-12 / proposal P-1..P-12. Two premises refuted while measuring: the S1 "uncommitted" claim (M-3, M-4) and the journey-spec tag blocker (`VERSION.md:9` vs the file's contents). |
