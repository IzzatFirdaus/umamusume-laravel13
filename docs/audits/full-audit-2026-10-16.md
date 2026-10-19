# Full-audit pass — worktrees, claimed completions, scratch, drift

**Stage 1 of 3.** Audit date 2026-10-16, measured against the live main repo `D:/Projects/umamusume-laravel13`
on `master` at `e845887`. **Read-only: no tracked file was modified, no worktree created or removed, no
scratch data moved, no owner decision answered, no slice started.**

Phase 3 (fullstack 2.0), Phase 5 (owner queue) and Phase 6 (code backlog) were **not reached this pass**.
The split is named in §12, not silent.

### Placement and supersession

This report lives in `docs/audits/`. It does **not** belong in `docs/research-scratch-source/`, which holds
knowledge gathered from and about *Umamusume Pretty Derby* itself — publisher datasets, scenario guides,
catalogue exports, screenshot corpora. A repository audit is an inventory of this codebase, not game
knowledge, and sits beside the `rice-shower-unity-cup-*` walk and reconciliation documents instead.

A first version of this pass — 16 claims, ten sections — was written to `docs/audits/` at 17:32 today and is
**superseded by this file**. Nothing was dropped in the consolidation: its owner queue is Appendix A, its code
backlog is Appendix B, its 16 claim verdicts are a subset of §4's 29, and its 13 drift rows are a subset of §9's
16. Two of its counts were corrected here: it reported **14 verified-closed / 0 false-closed**, which was true
of its 16-claim list; against the 29-claim list this pass verified **23** and found **1** false-closed (§4 B6).

---

## 1. Scope and method

### Read and run (all read-only)

| Instrument | Use |
|---|---|
| `git worktree list/status/log/show/diff/grep/cherry/ls-tree/merge-base/rev-list/remote` | surface state, containment, HEAD-vs-worktree comparison |
| Read / Grep tools | every quoted-literal match, every count a verdict rests on |
| Bash `grep`, `find`, `ls`, `du`, `wc`, `od`, `diff -q`, `sed -n` | single-stage only (see instrument note 2) |
| `php -r` + PDO, SELECT and `pragma` only | `database/database.sqlite` reads for the Turn 67 verdict |
| Two `general-purpose` subagents | Phase 5 and Phase 6, dispatched in parallel; their output is preserved in Appendix A with its own provenance |

### Not run, and why

`npm run build` and the Playwright suite (KI-70/KI-73/KI-80: both write shared state and both void under
contention). `php artisan test`, `pint --test`, PHPStan, `npm run typecheck`, `composer lore`,
`composer lore-code`, `php artisan migrate:status`, `php artisan migrate` — this pass asserts **where code
lives**, not whether the tree is green. **No gate was run and no pass is claimed anywhere below.**

### Six dispatch premises the tree refuted

1. **`uma_musume_race_planner` is not the main tree** — it is a separate repository. See §2.
2. **`D:/Projects/td-cockpit` is not a worktree.** No `.git`, absent from `git worktree list`, and
   `git -C` answers `fatal: not a git repository`. See §3.
3. **`uma_musume_race_planner-wt-citation` is not on a `citation-verify` branch.** It is on a **detached
   HEAD** at `71bb5ed`. No branch named `citation-verify` exists.
4. **A sixth surface was omitted.** `.kilo/worktrees/cherry-pet` on `worktree/cherry-pick` is a live
   worktree, 3 commits ahead of `master`, 18 paths dirty, unmerged. Included in §3.
5. **`.kilo/worktrees/dashing-fact` is not safe to remove.** Its HEAD is contained in `master`, but 58
   paths are uncommitted, including `MM` (staged then further modified) entries and `D` deletions of five
   `archive/adr/*` files. The premise holds for the commits, not the tree.
6. **"Three container-query representatives (wizard 1/2/4, Cockpit 1/2/3, Database/Scenarios 1/2/2)" has no
   referent.** Measured directly — see §4 row F13 — the container-query breakpoint counts on those three
   surfaces match no reading of those triples.

### Two instrument notes that changed verdicts

1. **A local hook strips single quotes from Bash arguments.** `grep -n "'ruleset'" f` executes as
   `grep -n ruleset f`. This pass it produced *both* a false count (14, where the true count was 0) and a
   false absence (`git grep "'ruleset'"` printing nothing). Every quoted-literal match in §4 and §9 was
   retaken with the Grep tool. **Verdict F7 rests on the Grep tool plus `git show HEAD:`, not on Bash.**
2. **`grep -c` counts lines, not occurrences.** The `AGENTS.md` § check reads 43 lines; the byte-level
   record in `.scratch-uma/findings-capture-2026-10-16/ki-drafts-and-encoding-record.md:241-255` counts 72
   occurrences. Both are reported, labelled, in F8.

### Pass-1 → pass-2 deltas (same session, same HEAD, recorded because they are findings)

- Main-tree dirty paths: **124 → 137** during this pass. A peer is writing now. Every dirty count below is
  stamped `17:4x local`.
- `SpiritBurstsPanel.vue` was reported by pass 1 as "the component file stays on disk" without a path. This
  pass located it: `resources/js/pages/Career/Cockpit/Panels/SpiritBurstsPanel.vue` (tracked, present at
  HEAD). Pass 1's check of `components/scenario/` and `components/career/` found nothing and was wrong to
  stop there.
- Pass 1 graded `WriteTeamStateRequest` never; this pass finds the named class **absent** (B6, FALSE-CLOSED).
- Pass 1's F4 quote `TurnChoice.php:72` and this pass's `TurnChoice.php:68` are the same method — the file
  moved under peer edits between readings; the `'Fell' => false` arm is still at `:72`.

---

## 2. Main repo disambiguation

**`D:/Projects/umamusume-laravel13` is the live main repo.** `D:/Projects/uma_musume_race_planner` is a
**different repository**, not a stale checkout of this one.

| | `umamusume-laravel13` | `uma_musume_race_planner` |
|---|---|---|
| `.git` last written | 2026-10-16 17:33 (today) | 2026-09-30 20:56 (16 days stale) |
| `git remote -v` | `github.com/IzzatFirdaus/**umamusume-laravel13**.git` | `github.com/IzzatFirdaus/**uma_musume_race_planner**.git` |
| Branch | `master` @ `e845887` | `main` @ `72074fc` |
| `git log -3` | `e845887 fix(wizard): toggle the scenario choice with the selected card` · `64fc4b8 fix(wizard): deselect the chosen trainee…` · `71bb5ed chore(gitignore): ignore merge-preservation scratch…` | `72074fc chore: add project meta files (AGENTS, ARCHITECTURE, PRD, CONTRIBUTING)` · `65bc8d9 docs: refresh D01-D05 specifications to v3.0` · `676e486 test: add security, import-export, and accessibility coverage` |
| Registered worktrees | **6** (below) | 0 of the five surfaces |

**How they relate:** different remotes, different default branches, no shared object store. The legacy repo is
the *planner* application — its `72074fc`-era stack is Alpine + Blade, which `AGENTS.md` §17 names as one of
the four retired originals consolidated into Trainer Desk. It is not a mirror of this tree and must never be
treated as the main working copy.

**Why the naming confuses:** the two `-wt-` worktrees are *directories* named after the legacy repo
(`uma_musume_race_planner-wt-citation`, `…-wt-deselect`) but are *registered to the live repo* — verified by
`git -C <path> rev-parse --git-common-dir` returning `D:/Projects/umamusume-laravel13/.git` for both. Any
report citing "the main tree at `uma_musume_race_planner`" while discussing a deselect or citation slice is
describing a laravel13 worktree whose parent repo is laravel13, not the legacy planner.

---

## 3. Worktree inventory

All five registered worktrees plus the main tree resolve `--git-common-dir` to
`D:/Projects/umamusume-laravel13/.git`. `td-cockpit` does not.

| Worktree | Branch | HEAD | Ahead of master | vs `origin/master` (behind/ahead) | Dirty | Scratch (MB) | Merged? |
|---|---|---|---|---|---|---|---|
| `umamusume-laravel13` | `master` | `e845887` | 0 | 0 / 11 | **137** | yes, 60+ session dirs | n/a |
| `uma_musume_race_planner-wt-citation` | **(detached HEAD)** | `71bb5ed` | 0 | 0 / 9 | **73** | 2.7 | HEAD merged; its dirty work is a **superseded earlier reading** |
| `uma_musume_race_planner-wt-deselect` | `deselect-fix` | `71bb5ed` | 0 | 0 / 9 | **6** | 35 | HEAD merged; the slice itself landed on `master` as `64fc4b8`/`e845887` |
| `umamusume-laravel13-screenshot-registry` | `feature/screenshot-registry` | `dbc54f4` | **2** | 87 / 2 | 0 (clean) | none | **NOT merged** |
| `.kilo/worktrees/dashing-fact` | `dashing-fact` | `1266182` | 0 | 85 / 0 | **58** | none | HEAD merged; **58 paths uncommitted — not removable** |
| `.kilo/worktrees/cherry-pet` | `worktree/cherry-pick` | `7da9898` | **3** | 387 / 3 | **18** | none | **NOT merged** |
| `D:/Projects/td-cockpit` | **none — not a git repo** | n/a | n/a | n/a | n/a | no | n/a |

`.scratch-uma/` is gitignored in every tree at `.gitignore:91` (`/.scratch-uma/`).

### Purpose, from the branch and the commit subjects

- **citation / deselect** — both sit at `71bb5ed`, whose log is `71bb5ed chore(gitignore)…` ·
  `b00691b chore: untrack scratch and agent-config files swept in by git add -A` ·
  `53e491a Merge branch 'cockpit-wt'` · `04bb402 Preserve stranded concurrent-session work before merging
  cockpit-wt`. Both were branched off the merge-preservation point for the wizard deselect arc.
  `git cherry master deselect-fix` is **empty** and `master..deselect-fix` is **empty** — the branch has no
  commits of its own; all its work is uncommitted.
- **screenshot-registry** — `dbc54f4 Screenshot registry: terminal state (Phases 16-18)` ·
  `193f7c4 feat(registry): screenshot registry work through v10 Phase 16`. Clean and tracking its own remote.
- **dashing-fact** — `1266182 test(browser): say what the disclosure guard actually catches` ·
  `d627349 test(support): move the deck analysis pin onto the settled plural`. Dirty set includes
  `MM DESIGN.md`, `MM KNOWN-ISSUES.md`, `MM CatalogController.php`, `MM ReviewController.php`,
  `MM app/Services/DeckAnalysis.php`, and `D` on five `archive/adr/*` plus two
  `docs/Trainer-Docs-2.0-*-Prompts.md`.
- **cherry-pet** — NativePHP desktop integration (`3e22cbe feat(nativephp): add NativeAppServiceProvider…`,
  `d9a5267 feat: NativePHP desktop integration work`), plus 16 untracked `nativephp/electron/*` paths and
  `scripts/`. This is a **desktop-packaging branch that `AGENTS.md` §12 does not contemplate** (loopback-only,
  no deploy path) and it is unmerged.
- **td-cockpit** — ownership provenanced to session **a430** (referenced only in
  .scratch-uma/a430/h14-prompt.md, `shots*.mjs`, `wt-spec*.log`; zero references in any tracked file).
  Top level holds `README.md`, `SCREEN_SPEC.md`, `SKILL.md`, `VERSION.md`, `tsconfig.json`,
  `vite.config.js`, `node_modules` (**a symlink to the main tree's**), and `public/`, `resources/`, `routes/`,
  `storage/`, `test-results/`, `tests/`, `tools/`, `vendor/`. **Absent: `app/`, `bootstrap/`, `config/`,
  `database/`, `artisan`, `.env`, `composer.json`, `package.json`** — so it cannot boot Laravel. `public/` is
  empty; `test-results/.last-run.json` reads `{"status":"passed","failedTests":[]}` (16 Oct 01:08);
  `storage/framework/views/` holds compiled Blade. `diff -q` against the main tree shows divergence across
  most of `resources/js` and `tests/`, and it still carries `resources/js/components/scenario/UraPanel.vue`,
  which the main tree replaced with `UraDuelPanel.vue`. **Verdict: a stale, non-bootable frontend-only scratch
  copy of an earlier tree.** Its only unique content is that obsolete `UraPanel.vue`.

### Cross-worktree overlap

Three surfaces modify the same five scenario-field paths:

```
app/Http/Controllers/Career/ScenarioSelectController.php
app/Services/Career/SetupDraft.php
resources/js/pages/Career/ScenarioSelect.vue
tests/Feature/CareerScenarioSelectTest.php
tests/browser/career-scenario-select.spec.ts
```

| Surface | Diffstat on the set |
|---|---|
| citation worktree | Controller 68++ · SetupDraft 28++ · ScenarioSelect.vue 41++ → **124 insertions, 13 deletions** |
| deselect worktree | Controller 20± · SetupDraft 15++ · ScenarioSelect.vue 25± → **37 insertions, 23 deletions** |
| main tree (working) | Controller 68++ · ScenarioSelect.vue 43++ · Feature test 60++ · browser spec 43++ → **191 insertions, 23 deletions** (`SetupDraft.php` clean; `scenarioRecorded()` already committed) |

File-by-file, main working copy vs citation working copy: `SetupDraft.php` **IDENTICAL**;
`ScenarioSelectController.php` differs by 20 lines; `ScenarioSelect.vue` by 27; `CareerScenarioSelectTest.php`
by 57; `career-scenario-select.spec.ts` by 30.

Also shared with the citation worktree's dirty set (73 paths) and the main tree's (137): `Cockpit.vue`,
`UnityCupEditor.vue`, `TrainingCard.vue`, `ScenarioPanel.vue`, `NextDecision.php`, `VeteranRow.php`,
`routes/web.php`, `DESIGN.md`, `KNOWN-ISSUES.md`, `AGENTS.md`, and 12 test files. **No authority is decided
here.** The reading the numbers support is that citation is an earlier state of the wiring the main tree has
since extended, and that the citation worktree carries no work the main tree lacks — but that is not proven
per-hunk and must not be acted on as if it were.

---

## 4. Claimed completions verified

29 claims. "Landed" = present at `HEAD` = `e845887`. "in tree" = working copy only, **uncommitted**.

### Frontend (20)

| # | Claim | Evidence | Landed? | Verdict |
|---|---|---|---|---|
| F1 | Scenario deselect | `app/Services/Career/SetupDraft.php:90 scenarioRecorded()`; consumed `ScenarioSelectController.php:57`; affordance `resources/js/pages/Career/ScenarioSelect.vue:283` `'Selected · clear'`; pinned `tests/Feature/CareerScenarioSelectTest.php:209,224,235-237` | yes (`64fc4b8`,`e845887`) | **VERIFIED-CLOSED** |
| F2 | Trainee deselect | `resources/js/pages/Career/TraineeSelect.vue:201,203,204` — `deselect` nulls `umamusume_id` and `character_card_id` | yes (`64fc4b8`) | **VERIFIED-CLOSED** |
| F3 | SpiritBurstsPanel regression | Root cause now located: `ScenarioPanel.vue:79` carries the comment that the Phase 7A.1 `register(… , SpiritBurstsPanel)` against the **widget** key `spirit_bursts` is removed; component retained at `resources/js/pages/Career/Cockpit/Panels/SpiritBurstsPanel.vue` (tracked, present at HEAD); regression case `tests/browser/career-cockpit-forms.spec.ts:194-195` | yes | **VERIFIED-CLOSED** |
| F4 | Team editor duplicate fixed | **HEAD is still nested:** `git show HEAD:…/UnityCupEditor.vue` has `<details>` at 78, `Edit team state` at 80, `</details>` at 168, inside `Cockpit.vue:759 <details data-region="team-state">` with the same summary at `:764`. **Working copy:** `UnityCupEditor.vue` root is `<form>` at `:81` with the caller-owns-disclosure comment at `:76-80` | **no** (`git diff --stat` 13±) | **PARTIALLY-CLOSED.** Missing: the commit, and the two strict-mode cases at `career-cockpit-forms.spec.ts:161,225` were never re-run |
| F5 | `team_rank_ladder` unshadow | `ScenarioPanel.vue:66 register('team_rank_ladder', UnityCupPanel)`; gated live at `CockpitController.php:144` and `:2448`; `config/scenarios.php:375 'team_rank_ladder' => true` for Unity Cup | yes | **VERIFIED-CLOSED.** The single-`Map` hazard it depends on is unfixed (`registry.ts:19`) and `SCREEN_SPEC.md:2979` still assigns it to the shell owner |
| F6 | Rule family label humanized | `ScenarioSelectController::ruleFamilyLabel()` at `:208` (a `match` returning `Repetition`, `Team rank`, `Repetition + purchases`, `default => ucfirst(str_replace('_',' ',$key))`), fed from `facility_level_source` at `:155-157`; row at `ScenarioSelect.vue:242-243`; pinned `ScenarioMetadataTest` + `career-scenario-select.spec.ts:111,136,145,147` | yes | **VERIFIED-CLOSED** |
| F7 | `metadata.ruleset` deleted | Grep tool, pattern `'ruleset'`, path `config/scenarios.php`: **no matches.** At HEAD it is live: `git show HEAD:config/scenarios.php` line 379 = `'ruleset' => 'Team',` | **no** | **VERIFIED-CLOSED (in tree only)** |
| F8 | `AGENTS.md` encoding clean | 61,700 bytes / 618 lines. First three bytes `23 20 41` — **no BOM**. Double-encoded `C3 83 C2` = **0**; literal `Â` = **0**. `§` on **43 lines** (byte-level record says **72 occurrences** — different units, both true) | **no** — file is ` M`; HEAD is a different clean reading (61,537 bytes, 70 `§`) | **VERIFIED-CLOSED** |
| F9 | KI-102 filed | `KNOWN-ISSUES.md:2778`, status line `FILED 2026-10-16 (close-out pass…), OPEN` | **no** — `git show HEAD:KNOWN-ISSUES.md` count = 0 | **VERIFIED-CLOSED (in tree only)** |
| F10 | KI-103 filed | `KNOWN-ISSUES.md:2834`, `FILED 2026-10-16 (findings capture pass), OPEN` | **no** — 0 at HEAD | **VERIFIED-CLOSED (in tree only)** |
| F11 | KI-104 filed | `KNOWN-ISSUES.md:2901`, `FILED 2026-10-16 (findings capture pass), FIXED IN TREE, NOT LANDED` | **no** — 0 at HEAD | **VERIFIED-CLOSED (in tree only)** |
| F12 | TrainingCard progressive-disclosure refactor | `resources/js/components/career/TrainingCard.vue` (26.0K), imported `TrainingDetail.vue:34`, rendered `:459-467` with `scenario-label`, `deck-recorded`, `chosen`, `turn`. Migration residue resolved: `StatBand.vue` matches at **neither** `components/` nor `components/career/` | yes | **VERIFIED-CLOSED** |
| F13 | Three container-query representatives (wizard 1/2/4, Cockpit 1/2/3, Database/Scenarios 1/2/2) | Measured the triples directly: `Cockpit.vue` `@4xl`=15, `@5xl`=12, `@2xl/@3xl/@7xl`=0, `@container`=0; `Database/Scenarios.vue` `@5xl`=1 only; `Database/Index.vue` none; `SetupLayout.vue` `@container`=1 only; `ScenarioSelect.vue` `@4xl`=1, `@7xl`=1; `TraineeSelect.vue` none. **No reading of any breakpoint matches the stated triples.** `grep -rn "1/2/2" docs/ .scratch-uma/` = 0 hits; `grep -rni representatives docs/` = 0 hits | — | **NOT-REACHABLE-IN-AUDIT.** The claim has no referent in this tree. Settle it with `git log --all -S"1/2/4" --oneline` and by asking which report minted the triple |
| F14 | Settings copy names compositions | `resources/js/pages/Preferences/Edit.vue:389` `"Bento composition. Grouped cards at the default shell width (1152px)."`; `:399` `"Wide bento composition… (1536px)."`; `:408` `"Focused composition. A single reading column…"`; labels at `:387,397,406` read Standard/Wide/Compact | yes | **VERIFIED-CLOSED** — with the copy defect in §9 D-10 |
| F15 | `DESIGN.md` erratum recorded | `DESIGN.md:325` begins `**Erratum 2026-10-16 (owner ruling).** The Dashboard-only scope of the 1536px wide step… is superseded` | yes | **VERIFIED-CLOSED** |
| F16 | Scenario metadata wiring (8 fields) | The 8-row table at `docs/research-scratch-source/scenario-field-metadata-wiring-report-2026-10-16.md` §1 names `ruleset_version`, `recommended_use`, `rule_family`, `planning_focus`, `expected_gains`, `energy_cost`, `risk`, `team_rank`. Tree confirms rows 1–4 render on Scenario Select (`ScenarioSelect.vue:202,219,229,230,242-243` + the metadata block) and row 8 needed no change (`TeamRankGauge` + `config team_rank_ladder`) | **mixed** — rows 1–4 depend on the uncommitted F7 wiring | **VERIFIED-CLOSED**, with one caveat: only **3** rows carry a `data-field` marker (`summary`, `description`, `recommended_use`), so the other five are addressed by label text, not a stable hook |
| F17 | Rest/Recreation turn choices | `app/Enums/TurnChoice.php:29 case Rest = 'rest'`, `:30 case Recreation = 'mood'`, worded at `:41-42`, branch rule at `:52-56`; both recorded through the same form at `career-training-detail.spec.ts:500` and `:542` | yes | **VERIFIED-CLOSED** — and it refutes the `SCREEN_SPEC.md:2110` clause (§9 D-5) |
| F18 | Four locator fixes | `git diff` on the two named specs (**37 insertions, 6 deletions**, `scenario-panel.spec.ts` 9±, `trackblazer-panel.spec.ts` 34±) shows exactly four: (1) `getByRole('img')` + `.first()` → `getByRole('img', { name: /Team Rank: not recorded/ })`; (2) `#purchase-item-error` → `#error-purchase-item`; (3) `getByRole('alert')` → `getByRole('alert').filter({ hasText: /not recorded/i })`; (4) `#purchase-cost-error` → `#error-purchase-cost` | **no** — uncommitted | **VERIFIED-CLOSED (in tree only)** |
| F19 | Phase 7A.1 unshadowings | `ScenarioPanel.vue:69 register('grade_objectives', TrackblazerPanel)`, `:70 …('shop'…)`, `:71 …('epithet_routes'…)` | yes | **VERIFIED-CLOSED** |
| F20 | Heading em-dash fix + two de-dashes | The two-de-dash commit is `0b6a8a8 Scrub two em dashes swept into Stage 1 (A.5 follow-up)` and its stat is **`docs/UMAMUSUME_REFERENCE.md \| 2 +-`, one file, 1 insertion 1 deletion** — documentation, not a heading. No rendered heading carries an em dash: `grep -rn "<h1\|<h2\|<h3" resources/js --include=*.vue \| grep —` = **0 hits**. The bar is enforced by `tests/Feature/RenderedCopyHygieneTest.php:177 'ships no em dash or en dash in rendered copy'` plus four browser specs (`career-cockpit.spec.ts:195`, `career-race-strip.spec.ts:173`, `career-training-detail.spec.ts:150`, `catalog-detail.spec.ts:83`) | partly (`0646381 fix(copy)…`) | **PARTIALLY-CLOSED.** The end state is clean and gated; the *named* heading fix is not identifiable in history and the "two de-dashes" landed in a doc, not a heading |

### Backend (9)

| # | Claim | Evidence | Landed? | Verdict |
|---|---|---|---|---|
| B1 | Turn 67 facility ladder on run 25 | PDO read of `database/database.sqlite`: `turn_entries` **id=22, training_run_id=25, turn=67**, `facility_speed=4, facility_stamina=4, facility_power=4, facility_guts=5, facility_wit=4`; `mood=GREAT`, `energy=NULL`, `choice=NULL`, created `2026-10-15 11:11:19`. Run 25 = `umamusume_id=30`, `scenario=unity_cup`, `status=Active`, and holds exactly one entry (`max(turn)=67, count=1`) | yes | **VERIFIED-CLOSED.** Note `energy` and `choice` are NULL on the row |
| B2 | `TurnChoice::outcomes()` Verdict B applied | `app/Enums/TurnChoice.php:68 outcomes(): array` returns `Rest => ['Success'=>false,'Poor Rest'=>true]`, `Recreation => ['Lifted'=>false,'No Change'=>false,'Fell'=>false]`, `default => ['Success'=>false,'Failure'=>true]`; consumed through the payload shape at `TrainingDecisionController.php:551` | yes | **PARTIALLY-CLOSED.** The method exists and the docblock at `:60-66` records the intent, but **`'Fell' => false` at `:72` directly contradicts `TrainingDecisionController.php:548`**, which states "penalty field has to appear for `Poor Rest` and `Fell` exactly as it does for `Failure`". One of the two is wrong; this is the same unresolved `Fell` question in §12 Appendix A row 39 |
| B3 | `facilityLevels()` walk-back | `TrainingDecisionController.php:490 private function facilityLevels(TrainingRun $run): array`, called at `:110` and threaded through `:142`, `:151`, `:200`, `:222`, `:378`, `:402` | yes | **VERIFIED-CLOSED** |
| B4 | Pre-fill from `write.previous` | `TrainingDecisionController.php:533` ("`previous` is the latest logged row, offered as a placeholder and never as a value"), shape at `:551`, built at `:581` | yes | **VERIFIED-CLOSED** |
| B5 | Veteran portrait (identity frame, not chosen form) | `app/Services/Legacy/VeteranRow.php:90 public static function portraitUrl(?TrainingRun $run): ?string`, called at `VeteranController.php:132` under the comment at `:130` that names it as the single owner for the detail header and the library row | yes | **VERIFIED-CLOSED** |
| B6 | `WriteTeamStateRequest` (Unity Cup) | **The class does not exist.** `grep -rn WriteTeamStateRequest app/ tests/ routes/` = 0 hits; no file at `app/Http/Requests/` or `app/Http/Requests/Career/`. The Unity Cup team write is served by **`StoreTrainingRunRequest`** — `app/Http/Requests/StoreTrainingRunRequest.php:85 'team_name' => ['nullable','string','max:255']` — reached by `routes/web.php:195 Route::put('/training-runs/{run}', [TrainingRunController::class,'update'])->name('runs.update')` → `TrainingRunController::update(StoreTrainingRunRequest $request, TrainingRun $run)` at `:657`, which is the `action` `UnityCupEditor.vue` posts to | n/a | **FALSE-CLOSED.** The named artefact is absent. The *capability* exists under a different class name — one request object serves run create, run update and the team-state write |
| B7 | `Preference` settings blob (D18b) | `app/Models/Preference.php:43 const KEYS = ['theme','failure_estimate','settings']`; `:46` names "the keys the `settings` blob carries (SCREEN-024, the D18b slice plan)"; `:113 settings(): array`; `:140 put('settings', json_encode(…, JSON_THROW_ON_ERROR))` | yes | **VERIFIED-CLOSED** |
| B8 | `ScenarioCaps::hardCap()` | `app/Services/ScenarioCaps.php:83 public static function hardCap(): int`, used at `:120` and `:201`; ceiling also read as `config('scenarios.hard_cap')` at `:57` and `:167` | yes | **VERIFIED-CLOSED.** Two read paths to the same number — `hardCap()` and the raw `config()` at `:57`/`:167` — which `ADR-0015` and `AGENTS.md` §8 say must both go through the single owner |
| B9 | `BackupDatabase` action + `UmaBackup` command | Both files exist: `app/Actions/BackupDatabase.php` (2.7K), `app/Console/Commands/UmaBackup.php` (1.1K) | yes | **PARTIALLY-CLOSED on coverage.** The artefacts exist, but `git grep -l "uma:backup\|BackupDatabase" -- tests/` returns **no file** — the only backup test is `tests/Feature/MariaDbBackupTest.php`, which matches on `hardCap`-adjacent grep noise and is named for a driver this project does not use (`AGENTS.md` §1: SQLite only). Command-level test coverage is unproven |

### Counts

**23 VERIFIED-CLOSED · 4 PARTIALLY-CLOSED (F4, F20, B2, B9) · 1 FALSE-CLOSED (B6) · 1 NOT-REACHABLE (F13).**

Of the 23, **six are in the working copy only and are not at HEAD**: F4's neighbour claims F7, F9, F10, F11,
F18, and F8's clean state. A peer commit or a `git add -A` round-trip can erase them. This is the highest-value
finding in the pass.

---

## 5. Fullstack 2.0 state — NOT AUDITED THIS PASS

Phase 3 (§3.1 controllers, §3.2 requests/models/services/actions/enums, §3.3 schema, §3.4 pipeline,
§3.5 routes, §3.6 config, §3.7 quality bar, §3.8 ADR coverage, §3.9 tests by layer, §3.10 lang, §3.11 tools,
§3.12 legacy Blade) is **Stage 2**. It was not attempted rather than attempted thinly; §12 names the split.

What this pass touched in passing, offered as **provisional pointers for the Stage-2 pass, not as verdicts**:

| Layer | Provisional observation, with the one cite I actually measured |
|---|---|
| Controllers | `CockpitController.php` is ≥ 2,650 lines (methods cited at `:144`, `:683`, `:2437`, `:2448`, `:2470`, `:2500`, `:2579`, `:2634`) against `AGENTS.md` §7's "thin controllers" rule. Stage 2 should size every controller, not just this one |
| Requests | `StoreTrainingRunRequest` serves create **and** update **and** the Unity Cup team write (B6). Whether that is one boundary or three is a §3.2 question |
| Data layer | `turn_entries` carries `facility_*`×5, `energy_band`, `choice`, `decision_snapshot`, `corrected_from`, `superseded_by`; run 25's only row has `energy`/`choice` NULL. Column-vs-reader mapping is unread |
| Config | `hardCap` is reachable two ways (B8). `config/dashboard.php:8` carries a known-bad `DESIGN.md` cite (§9 D-1) |
| Routes | `routes/web.php:136 runs.show` is a **redirect-only** method (`TrainingRunController::redirect`) and `:139-140` comments state it is kept as a "compatibility doorway pending the owner's ruling on the redirect itself". Orphan/redirect census unread |
| Tests | `AGENTS.md` §11's "`down()` proof" is recorded in project memory as an open repo-wide gap. Not re-checked |

---

## 6. Misplaced scratch data

`D:\.scratch-uma` — all five sub-answers.

1. **Content.** Three top-level directories, **no loose files**:

   | Entry | Files | Size | Newest write | Classification |
   |---|---|---|---|---|
   | `scenario-field-wiring-2026-10-16/build-check/` | 108 (105 `.js`, 2 `.css`, 1 manifest `.json`) | 1.3M | 2026-10-16 03:07 | Vite build output |
   | `spirit-bursts-panel-regression/build-check/` | 108, same shape | 1.3M | 2026-10-16 03:34 | Vite build output |
   | `team-editor-fix/build-check/` | 108, same shape | 1.3M | 2026-10-16 05:15 | Vite build output |

   324 files, 3.9M total. No SQLite, no logs, no source files, no `.exit` sidecars.

2. **Ownership.** Written by the three sessions that also own same-named directories under the repo tree.
   Reference sweep: `git grep` for `D:\.scratch-uma` / `D:/.scratch-uma` across all tracked files — **zero
   hits**; the same pattern across the four reachable worktrees — **zero hits**; a bounded working-tree sweep
   excluding `node_modules`, `vendor`, `.git`, `storage` — **zero hits**. Caveat, stated plainly: the one
   *unbounded* sweep exceeded its timeout and was backgrounded; it completed with an empty result body.
   Absence is proven on tracked files and on the bounded sweep, not over every untracked byte.

3. **Link type.** **Real directory.** `ls -la` shows `drwxr-xr-x` with no `l` bit; `cmd //c dir /AL "D:\\"`
   lists `.scratch-uma` as `<DIR>`, not `<JUNCTION>` or `<SYMLINK>`, with no reparse entry at that level. Its
   three children are likewise real directories. There is no link to redirect or break.

4. **Collision.** The three names **also** exist under `…umamusume-laravel13\.scratch-uma\`, with
   **disjoint contents** and **zero filename collisions**, so no `sha256` comparison was required:

   | Session dir | Repo copy | D-drive copy |
   |---|---|---|
   | `scenario-field-wiring-2026-10-16` | 17 files, 99M (13 gate logs incl. three 33M `lore-docs` dumps, `browser-8141.sqlite`) | `build-check/` only |
   | `spirit-bursts-panel-regression` | 15 files, 38M (logs, 2 scratch SQLite, `capture.mjs`, 2 PNGs) | `build-check/` only |
   | `team-editor-fix` | 8 files, 35M (logs, `browser-8147.sqlite`) | `build-check/` only |

   None of the repo copies contains a `build-check/` directory.

5. **Verdict: `SAFE-TO-MOVE`.** All three conditions the dispatch sets hold — no live references, no
   divergence on collision, no symlink ambiguity.

   **Operational caveat, and it is load-bearing:** the destination directories already exist, so this is a
   **merge, not a rename**. `mv /d/.scratch-uma/<s> <repo>/.scratch-uma/` would nest
   (`…/<s>/<s>/build-check`). The correct move addresses the inner directory:

   ```
   mv /d/.scratch-uma/<session>/build-check  D:/Projects/umamusume-laravel13/.scratch-uma/<session>/build-check
   ```

   Nothing was moved. The dispatch requires the owner to pre-approve the specific move, and §9 of
   `AGENTS.md` makes `.scratch-uma/` layout an owner rule.

---

## 7. Owner-decision queue — SEE APPENDIX A (Stage 3)

Not re-derived this pass. The 45-row queue measured **earlier in this same session, at the same HEAD
`e845887`**, is carried in Appendix A with its provenance line. Its counts were re-derived this pass by reading
every row: **29 OPEN · 13 RESOLVED-UPSTREAM · 2 WITHDRAWN · 1 UNDETERMINED (= 45)**. The 32/11 figure the queue
arrived with summed to 46 against a 45-row table and was wrong on both terms; row 6 moved separately, from OPEN
to RESOLVED-UPSTREAM, once `acff64d` and `app.css:441` were found. The table has not been re-validated against
the 140-path dirty tree, and the Stage-3 pass should re-check rows 3, 5, 7, 13, 40 and 41 first — those are the
rows whose evidence this pass found to be in motion.

## 8. Code backlog — SEE APPENDIX B (Stage 3)

Not re-derived this pass. The 53-row backlog table measured earlier in this session at the same HEAD is
preserved in Appendix B with counts **33 LANDED · 15 PARTIAL · 5 NOT-STARTED** after this pass reclassified
T1-6, plus the six places the plan's own status contradicts the tree.

---

## 9. Documentation drift

| # | Item | Current line, quoted | Confirmed? |
|---|---|---|---|
| D-1 | `config/dashboard.php:8` cites `DESIGN.md:210-221` | `* \`wide_bento_enabled\` defaults to true now that the DESIGN.md:210-221 shell-width ruling` | **CONFIRMED.** `DESIGN.md:210-221` is the tail of the 40-roles token-family table and the `### 2.2 Typography` heading. The shell-width ruling is at `:300-320`. The comment also names `wide_bento`, which `useLayout.ts:9-13` documents as a **retired** stored value |
| D-2 | `AppLayout.vue:366` cites `DESIGN.md:197-208` | `<!-- The ratified shell measure (DESIGN.md:197-208): the error documents constrain` | **CONFIRMED.** `:197-208` is the same token-family table, not a width ruling |
| D-3 | `NextDecision.php:548`, `:567` em dashes | `:548 "A training session costs {$cost[0]} to {$cost[1]} Energy — declared as a range because it "` · `:567 "config('advisor.constants.{…}') — a declared constant printed as read, not a "` | **CONFIRMED.** Byte check on those two lines: 2 × `E2 80 94`. `AGENTS.md` §5 bans the em dash in shipped copy and both strings reach the advisor rail. Pre-existing, not introduced by this arc |
| D-4 | `SCREEN_SPEC.md:2180` SCR-CAR-012 case counts | ``Tests: `tests/Feature/CareerTrainingDetailTest.php` (10 cases: …) and `tests/browser/career-training-detail.spec.ts` (6 cases: …)`` | **CONFIRMED, and worse than reported.** Grep-tool counts: the feature file holds **21** top-level cases (doc says 10); the browser spec holds **14** (doc says 6) |
| D-5 | `SCREEN_SPEC.md:2110` first clause | `Rest and Recreation have no 2.0 surface of their own: they are turn choices, not training options` | **CONFIRMED misleading.** Narrowly true (`TrainingDetail.vue:138` "they get no card"), but the same file at `:27` says they are "offered beside the five cards", and `career-training-detail.spec.ts:500`/`:542` pin both as recording turns through the live form. The clause reads as an absence the tree refutes |
| D-6 | `TrainingRunController::turnChoices()` doubled `/**` | `:492     /**` · `:493     /**` | **CONFIRMED.** Two consecutive docblock openers before prose at `:494-499` |
| D-7 | `git add -A` hazard on `AppLayout.vue`, `Cockpit.vue` | both ` M` in the main tree; `git diff --stat main master` on them = **142** and **886** lines | **CONFIRMED, and it already fired once:** `b00691b chore: untrack scratch and agent-config files swept in by git add -A`. The tree is 137 paths dirty across at least five sessions, and `DESIGN.md:316` says "the measure lives on `main` in `AppLayout.vue`", so a blanket stage would pull a second branch's 142-line variant into a `master` commit |
| D-8 | `frontend-development-plan.md:2107` | asserts a live "`StatBand.vue` mid-migration into `CareerStatePanel.vue` and `TrainingCard.vue`. The tree disagrees" | **NEW / CONFIRMED stale.** `StatBand.vue` matches at neither path. Needs a dated correction, not an in-place rewrite (§11) |
| D-9 | `DESIGN.md:317` vs `Dashboard.vue:139` | doc: "the Dashboard's grid no longer carries a page-level `max-w-*`"; code: `'grid w-full max-w-6xl mx-auto …'` with a comment at `:135-138` defending the cap | **NEW / CONFIRMED.** Code and bar disagree. §2 says code wins and the rule is stale — filed as an owner decision rather than silently rewritten either side |
| D-10 | Settings vocabulary split | `Preferences/Edit.vue:389,399,408` hints name "Bento / Wide bento / Focused composition"; labels at `:387,397,406` read Standard / Wide / Compact; `useLayout.ts:6-13` documents the first three as retired **stored values** | **NEW / CONFIRMED.** Displayed copy describes the new modes in the retired vocabulary |
| D-11 | `frontend-fidelity-2026-10-13.md:335-346` | presents Track 2 as needing an owner amendment for every clause | **NEW / CONFIRMED stale.** `DESIGN.md` carries five owner-signed amendments (`:286,348,358,819,1117`) |
| D-12 | `registry.ts:4` | `* The scenario panel registry (plan M-BM-'9 E1).` | **NEW.** A mangled cross-reference (`-` substituted for the dash) inside a cite. Cosmetic, but the cite resolves to nothing |
| D-13 | `KI-100` heading collision | `KNOWN-ISSUES.md:2694` `## KI-100 Twenty-two stored career objectives…` and `:2820` `### KI-100 — Workflow guardrails added…` | **NEW / CONFIRMED.** Two different entries share number 100 at two heading levels. Flagged at `ki-drafts-and-encoding-record.md:10-11` and unfixed; §11 forbids renumbering, so the shape needs an owner ruling |
| D-14 | **new this pass:** B2 enum-vs-comment contradiction | `TurnChoice.php:72 'Fell' => false` vs `TrainingDecisionController.php:548` "penalty field has to appear for `Poor Rest` and `Fell` exactly as it does for `Failure`" | **CONFIRMED.** A code-comment and the enum it describes disagree about whether `Fell` is a failure. One must move |
| D-15 | **new this pass:** B9 untested command | `git grep -l "uma:backup\|BackupDatabase" -- tests/` → no file | **CONFIRMED.** `AGENTS.md` §10 lists `uma:backup` as the pre-migration safety net (§11 calls it mandatory before `migrate:fresh --seed`) and it has no command-level test |
| D-16 | **new this pass:** reports cite the wrong main tree | `uma_musume_race_planner` appears in prior reports as "the main tree"; it is a different repository with a different remote (§2) | **CONFIRMED.** A citation that points at the retired legacy repo is a navigation defect, not a documentation one |

---

## 10. What the next pass can start immediately

From `OPEN`, `NOT-STARTED`, `SAFE-TO-MOVE`, `FALSE-CLOSED`, `PARTIAL` and `ORPHANED` only. No item widens scope.

1. **Land the six uncommitted closures** — `UnityCupEditor.vue` (F4), the `metadata.ruleset` removal in
   `config/scenarios.php` (F7), `KNOWN-ISSUES.md` KI-102/103/104 (F9–F11), and the four locator fixes (F18).
   All written, all one commit from `HEAD`, all on paths other sessions also dirty. **Stage explicit paths;
   never `git add -A`** (D-7, and `b00691b` is the precedent).
2. **Move the three `build-check/` directories** per §6 step 5, once the owner approves the specific move.
3. **Resolve D-14** — `TurnChoice.php:72` versus `TrainingDecisionController.php:548` on `Fell`. This is a
   one-line contradiction with a documented authority (`DESIGN-CORPUS` D-200/D-201/D-205).
4. **Fix the six cited-line drifts** D-1, D-2, D-4, D-5, D-6, D-12 — stale cites and one doubled `/**`. None
   changes behavior.
5. **Retire the phantom artefact B6** — either rename `StoreTrainingRunRequest` usage in the reports, or build
   the `WriteTeamStateRequest` the claim names. The claim as written describes a class that does not exist.
6. **Add the missing `uma:backup` test** (D-15) — the command is the documented safety net before a
   destructive migration and currently has no command-level test.
7. **Run Stage 2 (Phase 3)** — the fullstack audit. Start at §3.5 routes and §3.3 schema; §3.1 should open
   with the `CockpitController.php` size question surfaced in §5.
8. **SCREEN-014's two absent modules** (Readiness Summary, Training-vs-Recovery) and **SCREEN-016's two**
   (race-vs-training comparison, late-career planning) — PARTIAL with named missing halves, beside files that
   already exist.
9. **dev-plan 6.3 and 7.2** — the same quality-gate record counted twice, both NOT-STARTED, no dependencies.

## 11. What still needs the owner

1. **Pre-approval of the scratch move** — §6 is `SAFE-TO-MOVE` but the dispatch requires owner sign-off on
   the specific move.
2. **`Fell` as a failure outcome** (D-14 / Appendix A row 39) — the enum and its controller comment disagree.
3. **KI-102 fix shape** — three options; the register recommends replacing the MySQL block (Appendix A row 43).
4. **KI-103 direction** — make the `saved` path live or delete it, and if live, **which of the two written
   semantics governs the step bar** (row 44).
5. **Dashboard `max-w-6xl` vs `DESIGN.md:317`** (D-9) — the code decided; the bar says it did not. One must be
   re-ruled in writing.
6. **T2-F four-level hierarchy** (row 13) — open, with no evidence found either way; possibly a question
   without a referent.
7. **The fidelity §9 nine questions** (rows 14–22) and the **ux-laws §10 eight** (rows 23–30), including
   whether the HLF is the target of record.
8. **dev-plan §11 risks 7, 9, 10 and §4.1 items** (rows 32–36).
9. **Worktree disposition** — `dashing-fact` (58 uncommitted) is not removable as assumed;
   `feature/screenshot-registry` (2 commits) and `worktree/cherry-pick` (3 commits, NativePHP desktop
   packaging that `AGENTS.md` §12's loopback-only model does not contemplate) are unmerged; `td-cockpit` is a
   stale non-worktree copy. Nothing was touched.
10. **`KI-100` double heading** (D-13) — renumbering is barred by §11, so only the owner can settle the shape.

## 12. Explicit stop, and the split this pass named

**Reached this pass (Stage 1):** Phase 1 worktree inventory and reconciliation; Phase 2 claimed-completion
verification, 29 rows; Phase 4 misplaced-scratch verdict, five sub-answers; Phase 7 documentation drift,
seven named items verified plus eight new.

**NOT reached — deferred, with the stage the dispatch assigns:**

| Phase | Scope deferred | Stage | What it needs |
|---|---|---|---|
| **3** | Fullstack 2.0, §3.1 controllers · §3.2 requests/models/services/actions/enums · §3.3 schema · §3.4 data pipeline · §3.5 routes · §3.6 config · §3.7 quality bar · §3.8 ADR coverage incl. `ADR-0020` · §3.9 tests by layer · §3.10 `lang/en` · §3.11 tools · §3.12 legacy Blade | **Stage 2** | A dedicated pass. §3.1 alone enumerates every method of every controller against every route; §3.3 needs `php artisan migrate:status`, which this pass deliberately did not run |
| **5** | Owner-decision queue re-derived against the 137-path dirty tree | **Stage 3** | Re-validation of Appendix A rows 3, 5, 7, 13, 40, 41 first |
| **6** | Code backlog re-derived | **Stage 3** | Re-validation of Appendix B against `frontend-development-plan.md` §4 as it now stands |

Deferred rather than done thinly: a Phase 3 pass that cannot run `migrate:status` or read every controller
method would produce the kind of confident-but-unmeasured inventory this whole arc exists to stop.

This pass fixed nothing, moved nothing, committed nothing, answered no decision and started no slice. **No gate
was run and no suite is claimed to pass** — `php artisan test`, `pint --test`, PHPStan, `npm run typecheck`,
`composer lore`, `composer lore-code`, `migrate:status` and the browser suite are all **NOT RUN**, by design.

Cross-model second opinion was **not offered and not used**; the fresh-context substitute was the two
independent document agents whose five conflicting rows this pass re-measured directly, and the pass-1 →
pass-2 deltas recorded in §1.

**`AUDIT COMPLETE — 23 verified-closed, 1 false-closed across frontend + backend; 29 open owner decisions (re-counted from the 45-row table, not re-validated against the tree); 5 unstarted slices (carried); scratch SAFE-TO-MOVE; report at docs/audits/full-audit-2026-10-16.md. No file changed.`**

---

### Errata, 2026-10-16 (filed after the follow-up passes)

Nine corrections were checked. Five have a matching original in this file and are filed below; four do not, and
are reported under **"Checked, no matching original"** rather than invented. Original text is preserved above per
`AGENTS.md` §11 — each erratum quotes the claim it supersedes, states the correction, then names the evidence.

**E-2 — §4 F6 is not landed.** Original, the F6 row: `Landed?` = **yes**, verdict `**VERIFIED-CLOSED**`.
Correction: `ruleFamilyLabel|rule_family` matches **3** times in the working copy and **0** at `HEAD`;
`ScenarioSelect.vue` reads `metadata.ruleset` **0** times in the working copy and **1** at `HEAD`. The fix lives
only in the working tree, so F6's verdict is **VERIFIED-CLOSED (in tree only)**, not closed. The row's own
§3 header rule — "Landed = present at `HEAD`" — was violated by this cell.
Verified: `grep -c "ruleFamilyLabel\|rule_family" app/Http/Controllers/Career/ScenarioSelectController.php` → 3;
`git show HEAD:…ScenarioSelectController.php | grep -c …` → 0;
`grep -c metadata.ruleset resources/js/pages/Career/ScenarioSelect.vue` → 0;
`git show HEAD:…ScenarioSelect.vue | grep -c metadata.ruleset` → 1.

**E-3 — §4 F16 is not landed, and F6/F7/F16 are one uncommitted unit.** Original, the F16 row: `Landed?` =
"mixed — rows 1–4 depend on the uncommitted F7 wiring", verdict `**VERIFIED-CLOSED**`. Correction: at `HEAD`,
`ruleset_date` and `rule_family` return **0** matches in `ScenarioSelect.vue` and `ruleset_version` returns **0**
in `config/scenarios.php`, while the six pre-existing metadata fields (`recommended_use` 2, `planning_focus` 2,
`key_mechanics` 4, `description` 2) are present. **Two of the eight fields are absent at `HEAD`**, so F16 is
working-copy-only too. The consequence the follow-up pass established: **F6, F7 and F16 are a single
interdependent uncommitted unit, and F7 cannot land alone** — deleting `'ruleset' => 'Team'` from the config while
`HEAD`'s Vue still reads `card.metadata.ruleset` would render `undefined`. This is why the F7 hold in the
follow-up pass was correct and is now explained rather than merely asserted.
Verified: `for f in ruleset_date rule_family; do git show HEAD:resources/js/pages/Career/ScenarioSelect.vue | grep -c "$f"; done` → 0, 0.

**E-4 — §9 D-12 is withdrawn.** Original, the D-12 row: *"`registry.ts:4` — a mangled cross-reference (`-`
substituted for the dash) inside a cite. Cosmetic, but the cite resolves to nothing."* Correction: the line reads
`* The scenario panel registry (plan §9 E1).` — a clean U+00A7 SECTION SIGN. The `M-BM-'` reported here was
`cat -A` rendering that UTF-8 sequence under a non-UTF-8 locale. **Instrument artifact, not drift.** D-12 is
withdrawn and the follow-up pass's no-op on it was correct.
Verified: `python -c "print(open('resources/js/components/scenario/registry.ts',encoding='utf-8').read().splitlines()[3].encode('unicode_escape').decode())"` → ` * The scenario panel registry (plan \xa79 E1).`

**E-5 — §3's dirty-path read has a blind spot: `git diff HEAD` drops `MM` paths.** Original, §3's dirtiness
method and its `MM` observations (the dashing-fact worktree row naming `MM DESIGN.md`, `MM KNOWN-ISSUES.md`,
`MM CatalogController.php`, `MM ReviewController.php`, `MM app/Services/DeckAnalysis.php`). Correction: a path
that is **staged and then further modified** shows content under `git diff --cached` while `git diff HEAD`
returns nothing for the *staged* portion, so a snapshot built from `git diff HEAD` alone silently drops those
paths. Any dirty-path count derived that way is an undercount. A fourth layer — `staged-vs-head.diff` — is
required. The main tree currently holds **0** `MM` paths, so the blind spot could not be re-demonstrated here;
the evidence is the five `MM` paths §3 itself recorded in the dashing-fact worktree.
Verified: `git status --porcelain | grep -c "^MM"` → 0 (main tree), against §3's five recorded in the worktree.

**E-7 — the base ref for a temporary worktree is local `master`, not `origin/master`.** Original, §3's table
column header `vs origin/master (behind/ahead)`, which treats `origin/master` as the reference point.
Correction: `origin/master` is **18** commits behind local `master` (it was 11 when this audit was written), so a
runbook that branches a temporary worktree off `origin/master` and then expects `--ff-only` to succeed cannot —
the branch is stale on arrival. **The correct base ref is local `master`.**
Verified: `git rev-list --left-right --count origin/master...master` → `0  18`.

#### Checked, no matching original — reported, not filed

Four of the nine corrections name a claim that **does not appear in this file**. They are reported here so the
reader knows they were checked; no quote was invented to house them.

| ID | Searched for | Result |
|---|---|---|
| E-1 | `No \`main\``, `no \`main\``, `main branch` | **Not present.** §2's table describes the *legacy* repo `uma_musume_race_planner` as `main @ 72074fc`, which is correct. The live repo does have a `main` branch (`ef1f29d chore: remove empty favicon placeholder`, an ancestor of `master`) that neither audit mentions — an **omission**, not a false claim, so there is nothing to correct |
| E-6 | `worktree remove`, `tail` | **Not present.** The trap (a destructive git invocation piped to `tail` reports the pipe's exit code, masking a half-succeeded removal) is real and worth recording, but this audit never made the claim it would correct. Recorded as a rule, not an erratum: **read a destructive git invocation without a pipe** |
| E-8 | `228011e` | **Not present** in either audit file. This audit was written before that commit was discussed, so it carries no account of when it landed. `git merge-base --is-ancestor 228011e HEAD` exits 0 — it is old history in `master`, not a commit that landed during the audited pass |
| E-9 | `scratch-umera` across `docs/` and `.scratch-uma/` | **Zero hits.** No file in the tree carries that typo, so no erratum can be anchored to it |

#### Anchor note

This audit was measured at `e845887`. The tree has since advanced to **`23f9d64`**: `cbb7df8` merged
`feature/screenshot-registry` into `master`, and four follow-up commits sit on top — `7788b03` (F4 landed),
`c816625` (F18/F19 landed), `958cf47` (D-1 landed), `23f9d64` (`UmaBackupTest` added). The errata above are
filed against the later tree; the audit body above remains the record of the earlier read, and its verdict
counts (`23 verified-closed`, `1 false-closed`) stand as counts **for that read** — E-2 and E-3 move two of the
23 from landed to working-copy-only, which the counts do not encode and should not be silently rewritten to.

---

## Appendix A — owner-decision queue (45 rows)

**Provenance: measured earlier in this same session, 2026-10-16, against `master` @ `e845887`, by a
fresh-context subagent and reconciled by direct re-measurement where it conflicted with Appendix B. NOT
re-validated against the current 137-path dirty tree. Stage 3 owns re-validation.**

Two source-numbering corrections found: the fidelity doc's Track 1 / Track 2 / questions are §5, §6 and §9;
the ux-laws questions are §10 and its four-law gap §2.1, both as cited. Three consolidated audits
(`ux-laws-report-cockpit-wizard-2026-10-13.md`, `remaining-screens-ui-audit-2026-10-13.md`,
`setup-wizard-ui-audit-2026-10-12.md`) are **no longer files** — folded verbatim into
`docs/research-scratch/AUDIT-AND-VERIFICATION.md` (anchors `:8726`, `:9154`, `:9551`, `:10204`), so all such
rows cite the master.

| # | Source (§) | Decision | Verdict | Evidence |
|---|---|---|---|---|
| 1 | fidelity §5 T1-1 | "Apply the ratified shell measure … `mx-auto w-full max-w-5xl`" | RESOLVED-UPSTREAM (shape changed) | `useLayout.ts:20-24` preference-driven map; `DESIGN.md:311-317` |
| 2 | fidelity §5 T1-2 | Land the type scale; `#title` → `text-2xl`, section headers → `text-lg` | RESOLVED-UPSTREAM | `AUDIT-AND-VERIFICATION.md:10379` (`209e05e`, `4d31de4`); `TrainingDetail.vue:457` |
| 3 | fidelity §5 T1-3 | Dark ramp real separation, measured non-text step ≥ 3:1 | **OPEN** | ramp shipped `app.css:343-348`; the ≥3:1 measurement is recorded nowhere |
| 4 | fidelity §5 T1-4 | One form-control treatment across input/select/checkbox | RESOLVED-UPSTREAM | `components/Form/` = 9 entries; `BaseInput.vue:105` `min-h-11` + shared border/focus |
| 5 | fidelity §5 T1-5 | Densify where the data already exists | **OPEN** | no measured dead-space reduction recorded |
| 6 | fidelity §5 T1-6 | Spend the motif budget; enamel once per screen | RESOLVED-UPSTREAM | `acff64d feat(components): the enamel sheen goes back to one control per screen`, recorded in `bf91d18 docs(slice-records)… the enamel budget`; `DESIGN.md:375` "primary action button only"; then `app.css:441` **"Enamel is gone (2026-10-15). The variant named for the gloss was collapsed into `primary`"**, and `248e51b fix(errors): make the error pages' secondary anchors read as secondary` explains the three surviving `enamel` classes at `views/errors/404:49`, `419:46`, `500:43`. **Corrected this pass** — an earlier reading called this OPEN on the strength of a zero-count in `resources/js`, which was the deliberate removal, not an omission |
| 7 | fidelity §5 T1-7 | Finish empty / loading / error states (C-7, ADR-0007) | **OPEN** | states per page (`Dashboard.vue:178,182`); no sweep records "every data view"; `AUDIT-AND-VERIFICATION.md:10012` F-F1 lists four screens `title`-only |
| 8 | fidelity §6 T2-A | Radius scale 6/10/14/16/999 | RESOLVED-UPSTREAM | `DESIGN.md:348-353` owner-signed 2026-10-14; `527cfeb` |
| 9 | fidelity §6 T2-B | Sparing elevation for overlays only | RESOLVED-UPSTREAM | `DESIGN.md:358-366` owner-signed 2026-10-15; `app.css:300-303` |
| 10 | fidelity §6 T2-C | Display type tier (self-hosted face) | **WITHDRAWN** | `DESIGN.md:280-284` "closed permanently"; no `@font-face` ships |
| 11 | fidelity §6 T2-D | Wider semantic colour vocabulary | RESOLVED-UPSTREAM | `DESIGN.md:286` amended to `--color-primary-action`; `:1117` |
| 12 | fidelity §6 T2-E | Motion budget above 1 | RESOLVED-UPSTREAM | `DESIGN.md:819-821` MOTION 2, owner-signed 2026-10-15 |
| 13 | fidelity §6 T2-F | Adopt the four-level visual hierarchy | **OPEN** | no `DESIGN.md` passage states one; both agents' cites land on unrelated text |
| 14 | fidelity §9 Q1 | "Which product is it?" serious analytics vs optimistic, game-adjacent, modern | **OPEN** | `frontend-fidelity-2026-10-13.md:390-392` |
| 15 | fidelity §9 Q2 | Radius and elevation — adopt the 2.0 scale and sparing shadows? | **OPEN (elevation half only)** | `:393-394`; both ruled in rows 8–9 |
| 16 | fidelity §9 Q3 | Display type — is C-8 approval worth a self-hosted face? | **WITHDRAWN** | closed by `DESIGN.md:280-284` |
| 17 | fidelity §9 Q4 | Sequencing — Track 1 as one slice, or wait for approved Track 2? | **OPEN** | `:398-399` |
| 18 | fidelity §9 Q5 | Where does the amendment live — `DESIGN.md` §2 rewrite or a new ADR? | **OPEN** | `:400-402` |
| 19 | fidelity §9 Q6 | HLF wizard simplification — do per-stat, ancestry and deck editors move or cut? | **OPEN** | `:403-405` |
| 20 | fidelity §9 Q7 | HLF badge colours — adopt lime/amber, or map status to existing tokens? | **OPEN** | `:406-407` |
| 21 | fidelity §9 Q8 | What is the target of record — does the HLF become it? | **OPEN** | `:408-409` |
| 22 | fidelity §9 Q9 | Does the target adopt the official site's royal-blue chrome and pink CTA? | **OPEN** | `:410-414` |
| 23 | ux-laws §10 Q1 | One table or two — restate §13 to the canonical 27? | **OPEN** | `ux-laws-audit-plan-2026-10-13.md:486-489` |
| 24 | ux-laws §10 Q2 | The four laws §13 omits — in scope? | **OPEN** | `:490-492` |
| 25 | ux-laws §10 Q3 | Zeigarnik and the step bar — change the label or the state model? | RESOLVED-UPSTREAM | `AUDIT-AND-VERIFICATION.md:10238-10240` (ruling C-1, `4a5ff88`) |
| 26 | ux-laws §10 Q4 | `Measure` budget — full four-width capture on ~50 routes, or the reduced pass? | **OPEN** | `:497-499` |
| 27 | ux-laws §10 Q5 | Register — confirm "cite existing `F-xx`/`X-n`, add no new entry"? | **OPEN** | `:500-502` |
| 28 | ux-laws §10 Q6 | Is the HLF the canonical comparison surface? | **OPEN** | `:503-507` |
| 29 | ux-laws §10 Q7 | The client's alert channels — record as convention, or out of scope? | RESOLVED-UPSTREAM | `AUDIT-AND-VERIFICATION.md:10421-10426` |
| 30 | ux-laws §10 Q8 | Method of record — adopt the 0-10/0-4 rating alongside PASS/PARTIAL/FAIL? | **OPEN** | `:513-517` |
| 31 | ux-laws §2.1 | Four canonical laws absent from §13 (Continuity, Closure, Common Fate, Experience) | **OPEN** | `ux-laws-audit-plan-2026-10-13.md:95-98` |
| 32 | dev-plan §11 risk 7 | Energy records as a band — extend C2 to a band input, or accept the refusal state? | **OPEN** | `frontend-development-plan.md:2378-2382`. Write path exists (`StoreTurnEntryRequest.php:129-133`, `TurnEntry.php:125`, spec `:422`); the ruling is still owed |
| 33 | dev-plan §11 risk 9 | G3's Duel Panel has no recorded source — authorize a `TurnEntry` flag, or keep display-only? | **OPEN** | `:2391-2401` |
| 34 | dev-plan §11 risk 10 | Place the gates under `ura_finale`'s config entry, or source them into the catalogue? | **OPEN** | `:2403-2414` |
| 35 | dev-plan §4.1 item 6 | Does `BuildPurpose` gain a "Competitive Build" case? | **OPEN** | `:397`; `app/Enums/BuildPurpose.php:23-26` has four cases, none named that |
| 36 | dev-plan §4.1 item 6 | Do risk tolerance and per-skill priority marks enter `BuildTargetPayload`? | **OPEN** | `:397-398` |
| 37 | dev-plan §4.1 item 6 | Does `config/scenarios.php` gain a sourced `description` and `recommended_use`? | RESOLVED-UPSTREAM | `config/scenarios.php:146-147,483,560,565,957,962` |
| 38 | dev-plan §4.1 item 6 | Should `our_grand_concert.documented` still read `true`? | RESOLVED-UPSTREAM | `frontend-development-plan.md:416-420` (E6 close-out) |
| 39 | outcome-vocabulary pass | Is `Fell` a failure outcome? | **OPEN** | `TurnChoice.php:72 'Fell' => false` vs `TrainingDecisionController.php:548`. Both re-measured this pass; see §9 D-14 |
| 40 | layout-width pass | "Database/Scenarios composition (1/2/2)" | **UNDETERMINED** | no referent — see §4 F13. `Database/Scenarios.vue:88` composes `@5xl:grid-cols-2`; `Database/Index.vue:45-66` single-column |
| 41 | layout-width pass | Dashboard `max-w-6xl` cap — keep or retire? | **OPEN** | `Dashboard.vue:139` caps the grid, comment at `:135-138` defends it; `DESIGN.md:317` rules against. §9 D-9 |
| 42 | layout-width pass | The six stranded wizard walks | RESOLVED-UPSTREAM | all six now cap: `ScenarioSelect.vue:136`, `TraineeSelect.vue:246,275`, `BuildTarget.vue:321,371`, `LegacySelect.vue:254,314`, `DeckSelect.vue:248,269`, `Preflight.vue:191,216`; was "five of six ignore the ratified form width" (`AUDIT-AND-VERIFICATION.md:9248-9260`) |
| 43 | KI queue | KI-102 `.env.example` — which fix shape? | **OPEN** | `KNOWN-ISSUES.md:2778`; recommendation `:2802-2815` |
| 44 | KI queue | KI-103 `SetupDraft::saved()` — make live, or delete? | **OPEN** | `KNOWN-ISSUES.md:2834`; the two disagreeing rules are `saved()`'s docblock vs the 2026-10-13 `visited()` ruling |
| 45 | KI queue | KI-104 — land the written fix | **OPEN** | `KNOWN-ISSUES.md:2901,2905`; uncommitted diff `UnityCupEditor.vue` 13±; proof needs a rebuild-then-rerun of `career-cockpit-forms.spec.ts` on a quiet tree (KI-70 bars the rebuild while a peer works) |

**Counts, re-derived by reading all 45 rows: 29 OPEN · 13 RESOLVED-UPSTREAM · 2 WITHDRAWN · 1 UNDETERMINED
(= 45).** The figure carried in from the earlier pass, "32 OPEN · 11 RESOLVED-UPSTREAM", summed to 46 against a
45-row table and was wrong on both terms; row 6 was separately reclassified from OPEN to RESOLVED-UPSTREAM once
`acff64d` and `app.css:441` were found.

Three OPENs (#43–45) are landing decisions, not design rulings. Two (#41, and #3's missing measurement) are
doc-vs-code adjudications where the code already decided.

---

## Appendix B — code backlog (53 rows)

**Provenance: measured earlier in this same session, 2026-10-16, against `master` @ `e845887`, by a
fresh-context subagent; the five rows marked `*` were re-measured directly by this pass after the two agents
disagreed on them. Plan checkbox state was ignored throughout — the tree is the source. NOT re-validated
against the current 137-path dirty tree.**

| Phase | Slice | Deliverable | State | Evidence |
|---|---|---|---|---|
| A0 | A0a | Reduced-motion, scroll-padding, skip link, 44px, `aria-current` | LANDED | `app.css:565,605`; `AppLayout.vue:368` |
| A0 | A0b | Ported Vue pages carry loading/error + `lang="ja"` | LANDED | `Dashboard.vue:178,182` |
| A0 | A0c | Live Blade screens get states, 44px, `lang="ja"` | LANDED (targets retired) | Blade screens gone by B1; `resources/views/` holds `app.blade.php` + `errors/` |
| A0 | A0d | Error pages: heading, focus target, skip-to-main | LANDED | `views/errors/404.blade.php:34-39` (+419, +500) |
| A0 | A0e | `DESIGN.md` declares WCAG 2.2 AA + the rubric | LANDED | `DESIGN.md:1015,1054` |
| 1 | 1.1 | Stat denominator explicit, target vs ceiling | LANDED | `CareerStatePanel.vue:6,61,107`; `EventDecision.vue:218` |
| 1 | 1.2 | Skills Planner empty-priority, no false "0 SP" | LANDED | `SkillsPlanner.vue:256-267`; `app/Services/SkillSpendCoverage.php` |
| 1 | 1.3 | Run-scoped deck builder inbound path | LANDED | `TrainingDetail.vue:791`; `Cockpit.vue:585`; `routes/web.php:206` |
| 1 | 1.4 | Energy-band write and display path | LANDED | `StoreTurnEntryRequest.php:129-133`; `TurnEntry.php:125`; `Cockpit.vue:213-214` |
| 1 | 1.5 | UI write path for run identity fields | LANDED | `Cockpit.vue:390-426` |
| 1 | 1.6 | Unity Cup state write path | LANDED | `UnityCupEditor.vue` → `runs.update`; gated `Cockpit.vue:766` |
| 2 | 2.1 | Race Planner noise reduced, required races kept | PARTIAL | grouping `RacePlanner.vue:198`; placeholder disclosure absent (`RacePlanList.vue:72,112`) |
| 2 | 2.2 | Dashboard multi-active-career context | LANDED | `Dashboard.vue:85` |
| 2 | 2.3 | Safer, explicit turn corrections | LANDED | `Cockpit.vue:14-15,199-216` |
| 3 | 3.1 | Trackblazer grade-point / shop state editing | LANDED | `TrackblazerPanel.vue:46,141-145,549-655` |
| 3 | 3.2 | Grand Concert state-recording UI | PARTIAL | baseline strip only (`grand-concert-panel.spec.ts`); no state write |
| 3 | 3.3 | Consistent scenario-panel shell + edit affordances | LANDED | `ScenarioPanel.vue:64-104` |
| 4 | 4.1 | Result → Save Veteran clear, recoverable | LANDED | `Result.vue:78,352-361` |
| 4 | 4.2 | Veterans / Legacy comparison usability | LANDED | `pages/Veterans/Compare.vue`, `pages/Legacy/Compare.vue` |
| 4 | 4.3 | Catalog-to-career entry, retire transitional path | LANDED | `Catalog/Show.vue:122,408-419` |
| 5 | 5.1 | Fix the Inertia progress-bar ARIA (KI-63) | LANDED | `spa.ts:17-20`; native bar `AppLayout.vue:255` |
| 5 | 5.2 | Standardize loading / validation / empty / success | LANDED* | branches per page; the C-7 sweep is not recorded (Appendix A row 7) |
| 5 | 5.3 | Verify mobile / compact career loop | LANDED | `accessibility.spec.ts`, `career-cockpit.spec.ts`, `career-race-planner.spec.ts` |
| 6 | 6.1 | Complete new-career Playwright journey | LANDED | `full-career-journey.spec.ts` |
| 6 | 6.2 | Scenario-isolation + partial-update regression | LANDED | `tests/Feature/ScenarioIsolationTest.php` |
| 6 | 6.3 | Run the final quality gates and record | **NOT-STARTED** | no gate-record artefact |
| 7 | 7.1 | Verify doc accuracy vs implementation | PARTIAL | close-outs only for Phases A–E (`:2561-2572`) |
| 7 | 7.2 | Add the final quality-gate verification record | **NOT-STARTED** | no matching section or file |
| 7 | 7.3 | Final documentation update and cleanup | PARTIAL | change log stops at 2026-10-08; "Deferred work" section still at `:3520` |
| T1 | T1-1 | Ratified shell measure `max-w-5xl` | PARTIAL* | `useLayout.ts:20-24` is preference-driven; superseded (row 1) |
| T1 | T1-2 | Type scale landed | LANDED* | `ScenarioSelect.vue:132`; `TrainingDetail.vue:457` |
| T1 | T1-3 | Dark ramp real separation | PARTIAL* | ramp `app.css:343-348`; ≥3:1 measurement unrecorded |
| T1 | T1-4 | One form-control treatment, ≥44px | LANDED* | `components/Form/` 9 entries; `BaseInput.vue:105` |
| T1 | T1-5 | Densify Scenario / Trainee / Legacy / Deck | PARTIAL | no measured dead-space reduction |
| T1 | T1-6 | Enamel once per screen; render `ArtworkSlot` | LANDED | `ArtworkSlot` on 10 surfaces; the one-control enamel rule landed as `acff64d` and was then collapsed into `primary` per `app.css:441` ("Enamel is gone (2026-10-15)"); the three `views/errors/*` `enamel` classes are the residue `248e51b` left standing. **Reclassified this pass** — see Appendix A row 6 |
| T1 | T1-7 | Finish empty / loading / error states | PARTIAL* | per-page states; no sweep proves "every data view" |
| T2 | T2-A | Radius scale | LANDED | `DESIGN.md:348-350`; `527cfeb` |
| T2 | T2-B | Sparing elevation for overlays | LANDED* | `DESIGN.md:358-366`; `app.css:300-303` |
| T2 | T2-C | Display type tier | **NOT-STARTED** (declined) | `DESIGN.md:282` |
| T2 | T2-D | Wider semantic colour vocabulary | LANDED | `DESIGN.md:286,1117` |
| T2 | T2-E | Motion budget above 1 | LANDED* | `DESIGN.md:819-821` |
| T2 | T2-F | Four-level visual hierarchy | PARTIAL | no `DESIGN.md` passage states one |
| SS | SCREEN-009 | Five Next Decision states | LANDED | `NextDecision.vue:176-189`; `RecordedActionCard.vue:159-164,178-186` |
| SS | SCREEN-014 | Four new modules | PARTIAL | milestones + pre-final exist; no Readiness Summary, no Training-vs-Recovery |
| SS | SCREEN-015 | Three enhancements | LANDED | `SpiritBurstRoster.vue`, `UnityCupTournamentReadiness.vue`, `UnityCupPanel.vue:227-246` |
| SS | SCREEN-016 | Five new modules | PARTIAL | calendar, ledger, risk exist; comparison and late-career planning absent |
| SS | SCREEN-018 | Decision-audit trail | PARTIAL | before/action/expected/actual in `CareerTimeline.vue:30-31,117,180`; confidence omitted by ruling (`TimelineController.php:24-29`) |
| SS | SCREEN-019 | Veteran Creation screen | PARTIAL | `Result.vue` carries build/aptitudes/skills/races/scenario; no sparks, legacy value or best use |
| SS | SCREEN-020 | Factor Analysis workflow | **NOT-STARTED** | `SaveVeteran.vue:14-17` holds all three as `N/A` |
| SS | SCREEN-021 | Three-view library | PARTIAL | Veterans list only (`Veterans/Index.vue:239-243`); Factors held (`VeteranController.php:261-262`); Ancestry on Legacy Lab |
| SS | SCREEN-022 | Comparison features | PARTIAL | `Veterans/Compare.vue:14-18` record-only; three rows held under "NOT IN THIS BUILD" |
| SS | SCREEN-023 | Three new views | LANDED | `Database/Index.vue:5-6` |
| SS | SCREEN-024 | Race-risk thresholds setting | **NOT-STARTED** | `app/Models/Preference.php:52` — held on ADR-0016 |

**Counts, after this pass reclassified T1-6 from PARTIAL to LANDED: 33 LANDED · 15 PARTIAL · 5 NOT-STARTED
(= 53).**

Plan-vs-tree contradictions the backlog pass should correct: **Phases 1–7** mark nothing complete yet 18 of 21
are landed; **Phase A0** reads pending except A0a but A0a–A0e are all in place and A0c's Blade targets no
longer exist; **Track 2** reads "needs an owner amendment" for all six clauses while five are owner-signed;
**T1-1** names a fixed `max-w-5xl` the tree replaced with a preference map; **`frontend-development-plan.md:2107`**
still asserts a live `StatBand.vue` mid-migration the tree has since resolved (§9 D-8).
