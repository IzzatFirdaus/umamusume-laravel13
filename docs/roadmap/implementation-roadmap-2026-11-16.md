# Trainer Desk — Implementation Roadmap (2026-11-16)

**Status:** active · **Pair:** `docs/plan/implementation-plan-2026-11-16.md`. This file owns the phase/slice map, the
order and the acceptance bar; the plan file owns the code-level steps, paths and commands. Read both together; where
they disagree the roadmap governs and the plan is corrected.

**Basis [M], re-measured 2026-11-16 while the tree moved under it:** §0 below carries the **second P0.1 re-measure, at HEAD `178a9079`** (2 ahead / 0 behind `origin/master` = `0d046532`); the first reading (`ad4b09cc`, equal to origin/master, `10dee9d7` an ancestor) is preserved in the drift note. Snapshot taken at HEAD `d417a9a5` mid-measurement: worktree **196** porcelain rows — `48 " M"`, `112 " D"`, `36 " ??"`, staged index empty; reading at `ad4b09cc`: **199** rows — `49 " M"`, `112 " D"`, `37 " ??"`, staged index empty; 6 stashes; 108 routes (36 write); 66 page `.vue`, 117 component
`.vue`, 80 migrations (all `git ls-files`); 257 `tests/Feature` + 13 `tests/Unit` PHP suites and 124 `tests/browser`
specs tracked (172 tracked rows under `tests/browser` incl. fixtures) — **125 specs and 119 components exist on disk**,
the extras being a peer's untracked stat-visual set (`ui/Sparkline.vue`, `ui/StatRing.vue`,
`tests/browser/stat-visuals.spec.ts`); 108 routes (36 write) from `route:list --json`; `laravel/framework` `v13.32.0`; `lint:ui` baseline `count: 209`, `allowlist: 24`;
`VERSION.md` `3.0.0` pre-release, `git tag` ≤ `2.1.1`; `composer audit` **9 advisories across 6 packages**; the 17 MWG files **landed in HEAD** (`f8e428d9` 17 files, `78b54a71` 16, `178a9079` 15); worktree-vs-HEAD now differs for 2 only (`SearchableSkillPicker.vue`, `Career/TraineeSelect.vue`); `KNOWN-ISSUES.md` 114 headings worktree / 112 HEAD with **34** `KI-nn OPEN` ids at both
refs.

Every figure above is a snapshot of a tree that moves under this work. Re-derive in **P0.1**; a slice that inherits a
number without re-deriving it is a slice that can silently execute against a stale tree.

**Provenance labels used below.** `[M]` measured by a command whose output was quoted in this session · `[S]` static
read of a named file · `[I]` inferred and never used to satisfy an acceptance line.

---

## 0. Measured current state [M]

| Item | Reading | Re-derive with |
| :--- | :--- | :--- |
| HEAD | **`178a9079` — 2 ahead / 0 behind `origin/master` (`0d046532`)** (second re-measure; at `ad4b09cc` it equaled `origin/master`) | `git rev-parse HEAD` · `git rev-list --left-right --count origin/master...HEAD` |
| Worktree | **206** rows at `178a9079`: 33 ` M` / 23 `MM` / 112 ` D` / 1 `D ` / 37 `??` (was 199 at `ad4b09cc`) | `git status --porcelain | cut -c1-2 | sort | uniq -c` |
| Staged index | **24 files staged** (peer-staged; holds **stale pre-MWG blobs** of the MWG files — a plain `git commit` would revert hints on 14 of them) | `git diff --cached --name-only` |
| Stashes | 6 | `git stash list` |
| The 112 deletions | 90 `jev-inspect/**` (a foreign tree), 17 `docs/audits/*` **moved** into `capability-matrix/`, `mwg-audit/`, `reconciliation-register/`, `reconciliation-rounds/`, `ux-walkthroughs/` (replacements on disk, untracked), `SKILL.md`, `.kilo/kilo.jsonc`, 2 `tools/`, 1 `docs/research-scratch/` | `git status --porcelain \| grep '^ D'` · `ls docs/audits/` |
| The **37** untracked rows | the two roadmap/plan pairs, the absorbed legacy plans, the reorganised `docs/audits/` subdirectories, a peer stat-visual set (`ui/Sparkline.vue`, `ui/StatRing.vue`, `polyfills.js`, `tests/browser/stat-visuals.spec.ts` + fixtures), `win-unpacked/`, `.devcontainer/bootstrap-agent-controls.sh`, the root artifact sentinel | `git status --porcelain | grep '^??'` |
| 17 MWG files | **landed in HEAD** (`f8e428d9`/`78b54a71`/`178a9079`); worktree vs HEAD differs for 2 only: `SearchableSkillPicker.vue` `+5/−2`, `Career/TraineeSelect.vue` `+5/−1` | `git diff HEAD --numstat -- <the 17>` |
| `resources/css/app.css` `--color-risk` | HEAD `:531` `#FF6F3B`; guard enumerates it (`DesignTokensTest.php:841`); build emits `--color-risk:#ff6f3b` (lowercased) in `html[data-theme=dark]` — **P1.2 closed measurement-only, 17 passed/480 assertions** | `git show HEAD:resources/css/app.css | grep -n 'color-risk'` · `npm run build && grep -rn 'FF6F3B' public/build` |
| CRLF offenders | `resources/js/pages/Career/EventDecision.vue` (444 lines, all CRLF), `tests/browser/career-cockpit-forms.spec.ts` (374, all CRLF); `.gitattributes` declares `eol=lf` | byte count of `\r\n` per file |
| `ProvenanceMark.vue` | renders `<button type="button" class="pill pill-neutral ...">` at `:68-76`; `role="tooltip"` at `:84`; LF file | read the file |
| `Makefile` | file **exists** (4,418 B) with `test`, `lint`, `stan`, `migrate`, `seed`, `fetch`, `backup`, `lore`, `lore-code`, `audit` targets — **but GNU `make` is not found in this shell** (`command not found`), so KI-4 stands as measured here: run targets via composer scripts (`composer test`, `composer lint`, `composer stan`, `composer lore`, `composer lore-code`, `composer audit`) or `vendor/bin/*` directly | `grep -nE '^[a-zA-Z_-]+:' Makefile` · `command -v make` |
| CI | 3 workflows (`nativephp-windows.yml`, `nativephp-windows-acceptance.yml`, `release.yml`); **none runs pest/pint/phpstan/typecheck/the ui-ratchet**; build job green, acceptance run `43768347753` red with `No installed .exe found under $programsDir` | `ls .github/workflows/` · `gh run list` |
| Register | 114 headings worktree (77 `###` + 37 `##`), highest `KI-163`; HEAD 112 (75 + 37), highest `KI-161`; **34** `KI-nn OPEN` ids at both refs; 7 duplicated ids (`KI-70, 71, 101, 102, 103, 104, 142`); a body-reading census filed **53** open | `grep -cE '^#+ KI-'` · `grep -oE '^KI-[0-9]+ OPEN' \| sort -u \| wc -l` |
| Dependencies | `composer audit` 9 advisories / 6 packages (e.g. `CVE-2026-123792`, symfony yaml parse-time); `laravel/framework` v13.32.0 | `composer audit --format=plain` |
|
**P0.1 drift note (blocking).** HEAD advanced `d417a9a5` → `ad4b09cc` while P0.1 was running (peer activity: origin/master fast-forwarded to `ad4b09cc`). Every git sha this roadmap cites was re-verified with `git merge-base --is-ancestor <sha> HEAD` against `ad4b09cc`: **all cited shas are ancestors except the already-flagged off-branch `8ef841fb` and `1392e30f`**; `43768347753` is a CI run number, not a git object. No cited landing sha is lost. Execution proceeds at HEAD `ad4b09cc`; all §0 figures above use the `ad4b09cc` reading.
**P0.1 second re-measure (blocking).** At execution start HEAD had advanced `ad4b09cc` → `25049095` → `78b54a71` → `c41abbb8` → `0d046532` → `178a9079` (peer activity: an MWG hints commit `178a9079` "across 15 audited files", a plans-status commit, a CI chore). Every git sha this roadmap cites was re-verified with `git merge-base --is-ancestor <sha> HEAD` against `178a9079`: **all cited shas are ancestors except the same two off-branch shas `8ef841fb` and `1392e30f`**; `43768347753` is a CI run number, not a git object. Deltas from the §0 table as first written: HEAD moved and origin/master is now 2 behind; worktree 199 → 206 rows (the `MM`/`D ` classes appear because a peer staged 24 files); staged index empty → 24 peer-staged; the 17 MWG files landed (P1.1 effectively complete at HEAD, 2 files still dirty); `make` still not found (KI-4 stands). §0 rows above are corrected to this reading. **The 24 staged rows are peer work and are not to be committed, reset, or restored on this roadmap's authority.**

---
|
## 1. Consolidation — what this roadmap absorbs, and what it refuses to absorb

Every roadmap-shaped text file in the tree at 2026-11-16 15:45, with its disposition. **No file in this table is
edited or deleted by this consolidation**: supersession is recorded here, exactly once, and the sources stay
byte-identical so their provenance survives.

| Source | Size / mtime | State | Disposition |
| :--- | :--- | :--- | :--- |
| `docs/plans/implementation-roadmap-v3.0.0.md` (untracked) | 27,171 B · 15:40:43 | Its own author recorded no tree access; Phases 0–3 and 6 have since landed (`21568017`, `1f4a9c5f`, `5b8ee62c`, `9f800577`, `5156bc49`, `df150cd8`, `d330c82e`, `37fd9c88`, `fe8747c9`) | **Absorbed.** Its still-open Phase 4/5 items survive as P5.1–P5.4. Not executed from; a peer edited it after this ruling, so treat its bytes as a peer artifact |
| `docs/plans/remediation-plan-2026-11-15.md` (untracked) | 28,388 B · 15:26:00 | MWG Phases 1–3 executed (3.1 CLS no-op, 3.2 16 files, 3.3 `EventDecision` fieldset, 3.4 no-change, 2.2b popover decision recorded); its named code defects verified landed (`CompleteTransferRequest` no longer re-reads with `fresh()`, `shop_resets_in` guarded, CSV via `fputcsv`, `veteran_label` column present) | **Absorbed.** Its only live remainder is commit attribution + QA, which is P1.1 and P7 |
| `docs/roadmap/implementation-roadmap-hermes-2026-11-16.md` + `docs/plan/implementation-plan-hermes-2026-11-16.md` | 30,241 B / 14,616 B · 15:39:09 | A sibling session's pair, renamed from the earlier unsuffixed canonical pair at 15:39:09 and still being written while this file was authored | **Not absorbed, not edited.** Authoritative for that session only. Four measured defects are recorded here so an executor who opens it is not misled: its plan has **no R1.3 task** (its roadmap cross-refs a section that does not exist), **no R4 task** (while its self-check claims R0–R7 coverage), duplicate `Step 4`/`Step 5` numbering in its R3 task, and §0 figures that were stale on the day ("31/29" KI, "94 deletions") |
| `docs/roadmap/v3.0.0-implementation-roadmap-2026-11-16.md` + `docs/plan/v3.0.0-execution-plan-2026-11-16.md` | 11,181 B / 4,646 B · 13:37 | An earlier pair, superseded the same afternoon | **Absorbed** where it adds a gate (fresh-SQLite install check, release qualification); otherwise superseded |
| `docs/audits/browser-observation-2026-11-15-followup/10-final-verification.md` (tracked, `21568017`) | 408 lines | Audit record with a "next steps, in execution order" list | **Absorbed item-by-item** into P3; its two stale claims are corrected in P3.4 and P3.6 |
| `docs/plans/{frontend-colour-update-2026-11-14, frontend-stat-colours-and-dark-mode-integration, career-legacy-manual-input-findings}.md` | 11,098 / 11,595 / 3,116 B | Slice plans and research for work already landed | **Left alone** — not roadmaps, no remaining slice |

Two files a previous draft expected do **not** exist and must not be re-created: `docs/roadmap.md`, `docs/plan.md`, and
`docs/proposals/Trainer Desk 2.0 — Remaining implementation.txt`. `probe_runs.php` is gone (peer removed) — its old
Pint trap is dead.

---

## 2. Completed work — excluded, with the evidence that excludes it

Do not re-execute a row below. Each names the commit or the file:line that proves it.

- **v3.0.0 roadmap Phases 0, 1, 2, 3, 6** — `10-final-verification.md` (`21568017`) plus `1f4a9c5f`, `5b8ee62c`,
  `9f800577`, `5156bc49`, `df150cd8`, `d330c82e`, `37fd9c88`, `fe8747c9`.
- **MWG Phases 1–3** — IME guard, `<search>` landmark, overscroll CSS; Slices 2.1/2.2a/2.2b/2.3; 3.1–3.4. The 17
  files' *content* is the only part left, which is P1.1's disposition, not a build.
- **Findings closed with a sha**: BO-002 `2d0b7ee5` · BO-006 `5156bc49` · BO-008 `d330c82e` · BO-009 `1f4a9c5f` +
  `d417a9a5` · BO-010 `9f800577` · BO-013 `c6ac05e7` + `83387115` + `ebe5b3fe` · BO-015 `3ee31da8` · BO-016
  `fc5aa9d3` · BO-017 `2ae2de54` · Walk-8 F-03 `282e5437` + `aaf21cd3` · three review follow-ups `07ec79d9`,
  `4c13e0e0`, `d417a9a5`.
- **Transfer-area remediation** — verified landed by reading the code: `app/Actions/CompleteTransferRequest.php`
  (no `fresh()` re-read), `StoreTrainingRunRequest` (`shop_resets_in` + `mode` guard, scenario-switch cleanup),
  `TrainingRunController` (`fputcsv`), `veteran_label` in `CompleteTransferRequest` + migration, `transfersUrl` in
  `TransferRequestController`.
- **NativePHP packaging** — `composer.json:17-18`, `nativephp/electron/`, `playwright.nativephp.config.ts`, 3
  workflows; the packaged-exe spec's `resources/build/app/public/index.php` fallback landed and its secret gate is
  covered by `EnforceNativePHPSecret`.
- **Not work at all**: BO-014 (reserved id, never a finding), BO-005 (does not reproduce — data-state artifact),
  the `#FF6F3B`-vs-`#FF4400` question *as a question* (it is measured above; what remains is P1.2's commit).

**Off-branch sha hazard [M].** `8ef841fb` and `1392e30f` — cited by two absorbed documents as landed BO-011/BO-013
fixes — are **not ancestors of HEAD**; they live on another branch. Their content exists **only in the worktree**
(`app.css` `#FF6F3B` and the focus-ring seed). An executor who assumes "already committed" loses the fix at the next
`git checkout`, which is precisely why P1.2 exists.

---

## 3. Roadmap laws

1. **Measure before executing.** Every path and claim is re-derived (`git ls-files`, `git status`, a read) before a
   slice starts. Plan text is a claim in both directions: named paths are often wrong, and named pending work is often
   already done.
2. **A slice is complete only when its verification commands have run and their output is quoted in the hand-off.**
   A report is not progress. The only documentation deliverables this roadmap permits are the ones a slice names
   explicitly (P5.3 `QWEN.md`, P7.2 `VERSION.md`/`CHANGELOG.md`, P7.3 tag request, `KNOWN-ISSUES.md` register writes
   that a change obliges), because the project's own exit bar requires them.
3. **Never stage, commit, push or install without the owner naming the paths in that turn.** No `git add -A`, no
   `git commit -a`, no real-index commit — the private-index recipe in the plan. `pint --test <paths>`, never
   `--dirty`.
4. **Shared worktree discipline.** Session-private ports and scratch DB copies; artifacts under
   `.scratch-uma/<session>/`; one Pest process at a time (overlapping runs hang rather than fail); browser token tests
   read `public/build`, so rebuild before believing a token reading; never measure against a peer's server.
5. **No gate is weakened to pass.** Failures are classified (regression / pre-existing / unrelated / infrastructure /
   flaky) before any re-run; a red is fixed at root or recorded in `KNOWN-ISSUES.md` in its four-part form. A skipped
   gate is never reported as green.
6. **A peer's artifact is never adopted on this roadmap's authority.** Uncommitted peer work is measured and
   dispositioned by the owner (P1), never swept into a commit, restored, or deleted to make a count tidy.

---

## 4. Dependency graph

```
P0 baseline ──► P1 attribution ──► P2 dependencies ────────────────────────────┐
                     │                                                        │
                     ├──► P3 suite truth ──► P6 scenario walks ──► P7 release ──┤
                     │            │                                           │
                     └──► P4 register truth ──► P5 feature residuals ──────────┘
```

P1 precedes P2/P3/P4 because a register entry and a regression claim both need a sha to name. P3 precedes P6 because
a walk over a suite with unexplained reds measures the harness, not the product. P7 runs last, on a settled tree.
P4 and P5 may interleave with P3 but never precede P1.

---

## P0 — Baseline re-verification (blocking, minutes)

### P0.1 Re-measure the tree
**Objective.** Replace every inherited figure with a live one, and detect peer drift before it corrupts attribution.
**Depends.** Nothing. **Blocks.** Everything.
**Files.** None written; the output is a quoted block in the session notes.
**Steps.** See plan P0.1 — `git rev-parse HEAD`, porcelain histogram, `git diff --cached --name-only`,
`git stash list`, the 17-file check, `make --dry-run test` (the Makefile exists; KI-4 is stale).
**Verify.** Every command's output quoted in the hand-off, with the deltas against the §0 table named.
**Acceptance.** A baseline block exists naming HEAD, the histogram, the staged state and each delta from §0. If the 17
MWG files are no longer all ` M`, P1.1 is re-read against the new state before it executes. **No delta is "fixed" to
match the table** — the table is corrected by measurement, never the reverse.

---

## P1 — Attribution: bank or park what the worktree already holds (owner decision, blocking)

### P1.1 The 17 MWG files
**Objective.** Land or park the MWG first-pass remediation (`docs/audits/mwg-audit/mwg-audit-report.md`) without
sweeping a peer's work.
**Depends.** P0.1.
**Files.** All under `resources/js/`: `components/Form/BaseInput.vue`, `components/Form/SearchableSkillPicker.vue`,
`components/catalog/TraineeCatalog.vue`, `components/legacy/AncestorEditor.vue`, `components/skills/SkillCatalog.vue`,
`components/support/CardPicker.vue`, `pages/Career/{EventDecision,LegacySelect,SaveVeteran,SkillsPlanner,TraineeSelect}.vue`,
`pages/Database/Races.vue`, `pages/Legacy/Builder.vue`, `pages/Runs/Index.vue`, `pages/Support/Builder.vue`,
`pages/SupportCards/Index.vue`, `pages/Veterans/Index.vue`. **`+128/−40` together [M].**
**What the diff is.** `BaseInput.vue` gains `autocorrect`, `autocapitalize`, `spellcheck`, `enterkeyhint` and
`inputmode` props defaulted to `undefined` — deliberately, so an absent hint stays absent from the DOM instead of
rendering `spellcheck="false"` (a `false` boolean renders on an enumerated attribute). The other 16 files opt call
sites in per field semantics.
**Steps.** Plan P1.1: re-derive the set, present the per-path table, the owner picks A/B/C/D per item, execute the
picked branch through the private index.
**Verify.** `git show --stat <sha>` lists only authorized paths · `npm run typecheck` exit 0 · `npm run build` exit 0 ·
for a park, the `--numstat` reading re-printed unchanged.
**Acceptance.** One or two conventional commits containing only authorized paths, **or** a park record naming all 17
with the reading that parked them. A peer hunk never rides in a commit it does not own.

### P1.2 The BO-011 dark risk token + focus ring
**Objective.** Commit the worktree's accessibility fix, which exists nowhere in HEAD's ancestry.
**Depends.** P0.1.
**Files.** `resources/css/app.css` (dark block `--color-risk`: `#FF6F3B` worktree vs `#FF4400` at HEAD, line 531;
focus-ring seed block; `#C62828` light at `:200`, `#FF7E8C` `:689` unchanged) · `tests/Feature/DesignTokensTest.php`
(the named-token list at `:836-841` must enumerate `--color-risk`).
**Steps.** Plan P1.2: re-measure the three contrast pairs on the worktree value, confirm the guard enumerates the
token, commit through the private index with the `BO-011` reference intact, rebuild.
**Verify.** `vendor/bin/pest --filter=DesignTokensTest --compact` · `npm run build` exit 0 · the emitted CSS carries
`#FF6F3B` · each surface's ratio quoted.
**Acceptance.** The token is committed with its ring seed, every dark surface's pair ≥ 3:1 for non-text and ≥ 4.5:1
where it carries text (quoted per surface, not asserted once), and the emitted build contains the value. **BO-004
(4.15:1 dark `To do` pill) is not closed here** — it is P5.5, an owner ruling.

### P1.3 Dispositions for 112 deletions and 36 untracked rows
**Objective.** Every worktree row has a recorded owner and fate, so nothing is lost by a future `git checkout` and no
peer's tree is "restored" into a commit.
**Depends.** P0.1.
**Files.** `git status --porcelain` rows only.
**Steps.** Plan P1.3: classify each ` D` as **move** (prove the replacement path exists under `docs/audits/<subdir>/`),
**peer removal** (not this change's to restore), or **loss**; classify each `??` as session scratch (gitignored),
owner-named commit, or recorded-with-owner.
**Verify.** `git status --porcelain | cut -c1-2 | sort | uniq -c` before and after · `git ls-files --deleted | wc -l` ·
a paired existence check per move.
**Acceptance.** No row unnamed; every *loss* restored or committed with a stated reason; the 90 `jev-inspect/**` rows
recorded as a foreign tree's removal, not silently dropped.

---

## P2 — Dependency and security closure

### P2.1 Advisories verified, not inherited
**Objective.** Turn "9 advisories across 6 packages" into a reachability verdict per advisory.
**Depends.** P1 (an attributed tree makes a regression attributable).
**Files.** Read-only: `composer.lock`, `composer.json`.
**Steps.** Plan P2.1: `composer audit --format=plain`, then per advisory record affected range, fixed-in version, and
whether the vulnerable path is reachable here (console-only, dev-only, unexercised).
**Verify.** The audit output quoted whole; `composer show laravel/framework` (`v13.32.0` [M]).
**Acceptance.** Each advisory carries one of reachable-fixed / not-reachable / owner-gated. **No CVE number is pasted
into a lock file from a register** — a number that cannot be re-confirmed stops the slice.

### P2.2 Narrow update to a verified fixed-in
**Objective.** Move only the packages a confirmed reachable advisory requires.
**Depends.** P2.1.
**Files.** `composer.lock` (and `composer.json` only if a constraint must move — owner call). Never a new package
(`AGENTS.md` §5).
**Steps.** Plan P2.2: `composer update <package> --with-dependencies`; never a blanket `composer update`; re-audit.
**Verify.** `composer update` output · `composer audit` clean of reachable critical/high · `vendor/bin/pest --compact`
compared against P3.8's classified baseline · `make stan` (PHPStan) at the recorded level.
**Acceptance.** The lock moves to a confirmed fixed-in version, or a written reason says why it must not. Any new test
failure is classified dependency-caused vs pre-existing before a re-run, and a break needing an owner call is parked
by restoring the lock.

### P2.3 Front-end advisories
**Objective.** The npm half of the same question, measured rather than assumed.
**Depends.** P2.1.
**Files.** `package-lock.json`, only if a reachable advisory requires it.
**Steps.** Plan P2.3: `npm audit --omit=dev --json`, classify, narrow-update if warranted.
**Verify.** The audit output · `npm run typecheck` · `npm run build` · `npx playwright test` on the token-sensitive
specs after the rebuild.
**Acceptance.** Every advisory classified, or a written reason it is out of reach; build and typecheck exit 0.

---

## P3 — Suite truth (the large implementation phase)

**Objective.** A full run whose every failure carries a class, so P6's walks measure the product.
**Depends.** P1 (a red attributed to an uncommitted diff cannot be classified).
**Measured seed [M].** PHP: 6 failures — `CareerScenarioSelectTest` (`Spirit Bursts (combined)` label), three
`CareerTraineeSelectTest` cases + `TraineeCardFormTest` (slug binding), `LineEndingGuardTest` (two CRLF files).
Browser: 11 failures — `ProvenanceMark` pill regression (`role="img"` → 23px `<button>`), `app.css` drift,
`config/scenarios.php`'s `Spirit Bursts (combined)`. PHPStan: 1 (`EnforceNativePHPSecret.php:13`).

### P3.1 `ProvenanceMark` shape — one shape, all specs green
**Objective.** Resolve the interactive-vs-static divergence the peer's rewrite created.
**Depends.** P1 (the file is peer-modified uncommitted — coordinate through the owner).
**Files.** `resources/js/components/ProvenanceMark.vue` (`<button type="button" class="pill pill-neutral ...">` at
`:68-76`, `role="tooltip"` at `:84`) · affected specs: `tests/browser/provenance-mark.spec.ts`,
`career-cockpit.spec.ts`, `unity-cup-panel.spec.ts`, `fixtures/provenance-mark.{html,ts}`.
**Steps.** Plan P3.1: measure the hit area; owner picks (a) keep the button and raise it to the SC 2.5.8 minimum of
24×24 CSS px (measured ~23px — note 44px is 2.5.5 AAA, not 2.5.8), updating the affected specs, or (b) restore the
non-interactive `role="img" tabindex="0"` shape and leave specs alone.
**Verify.** The affected specs green · `npm run typecheck` exit 0 · `npm run build` exit 0 · the measured box quoted.
**Acceptance.** One shape across component and specs; no spec asserts a shape the component no longer renders.

### P3.2 Slug binding fixtures
**Objective.** Make the `{umamusume}` binding's tests true against `getRouteKeyName() = 'slug'`.
**Depends.** P1.1 (the model diff is part of that set).
**Files.** `app/Models/Umamusume.php` (binding present [S]) · `tests/Feature/{CareerTraineeSelectTest,TraineeCardFormTest}.php`.
**Steps.** Plan P3.2: run both suites; for a real failure replace numeric ids with slugs in request paths/fixtures, or
add a dual binding only if the owner prefers it.
**Verify.** `vendor/bin/pest --filter=CareerTraineeSelectTest --compact` · `--filter=TraineeCardFormTest --compact` ·
`--filter='CatalogDetailPageTest|CatalogProfileRomanisationTest'`.
**Acceptance.** Both suites green, and a slug that looks numeric resolves without ambiguity (the case that breaks a
slug-only binding).

### P3.3 Scenario label reconciliation
**Objective.** End the `Spirit Bursts` / `Spirit Bursts (combined)` split in one direction.
**Depends.** P1.
**Files.** `config/scenarios.php:68` (label `Spirit Bursts (combined)` [S]) · `tests/browser/career-cockpit-forms.spec.ts:182`
(expects `(combined)`) and `:248` (expects bare) · `tests/browser/scenario-panel.spec.ts:94` (bare).
**Steps.** Plan P3.3: run the three suites; apply one owner-confirmed direction to every expectation. The config is
not changed to dodge a test.
**Verify.** All three suites green · `composer lore-code` added-lines = 0.
**Acceptance.** Zero remaining expectations in the other direction, proven by `grep -rn 'Spirit Bursts' tests/`.

### P3.4 Line-ending conversion
**Objective.** `LineEndingGuardTest` green with the repo's `eol=lf` contract intact.
**Depends.** P1.1 (`EventDecision.vue` carries part of that diff).
**Files.** `resources/js/pages/Career/EventDecision.vue` (444 lines, all CRLF [M]) ·
`tests/browser/career-cockpit-forms.spec.ts` (374, all CRLF [M]).
**Steps.** Plan P3.4: convert whole files CRLF→LF (no partial conversion — a mixed file re-breaks the guard).
**Verify.** `vendor/bin/pest --filter=LineEndingGuard` · the guard's own `>500 files` floor still passes (a sweep that
reads zero files proves nothing).
**Acceptance.** The offender list is empty and the guard's file-count floor still trips its assertion, i.e. the check
ran.

### P3.5 `env()` behind config
**Objective.** PHPStan clean at `EnforceNativePHPSecret.php:13` without changing the middleware's behaviour.
**Depends.** P1.
**Files.** `app/Http/Middleware/EnforceNativePHPSecret.php:13` · `config/nativephp.php` (**does not exist** [M] — it is
created here).
**Steps.** Plan P3.5: add the key to a config file (new `config/nativephp.php` following repo conventions), read it
via `config()`, keep generated-secret behaviour intact.
**Verify.** `vendor/bin/phpstan analyse --no-progress --memory-limit=1G` clean on the file · the packaged-exe
acceptance path still resolves the secret (403/200 via `NATIVEPHP_SECRET`).
**Acceptance.** No `env()` in `app/` for this key; PHPStan's failure count drops by exactly one; the 403/200 behaviour
is demonstrated by a reading, not asserted.

### P3.6 `lint:ui` disagreement settled by measurement
**Objective.** Resolve the 209-vs-282 split without widening a gate.
**Depends.** P1.
**Files.** `tools/ui-lint-baseline.json` (`count: 209`, `allowlist: 24` [M]) · KI-145 (duplicated: `:96` CLOSED
"202 vs 209, 7 under, exit 0"; `:205` OPEN "282 vs 219").
**Steps.** Plan P3.6: `npm run lint:ui`, read the emitted count, compare against the baseline file. Fix new values or
re-baseline **with the reason in the file's own `reason` field**.
**Verify.** The lint output's count line and exit code quoted · the baseline file's diff.
**Acceptance.** The gate is 0 findings or a re-baseline whose reason names what moved; never a widened threshold to
make the run pass.

### P3.7 The unreached page gets a spec
**Objective.** Close the one routable page no browser spec reaches.
**Depends.** P1.
**Files.** `resources/js/pages/Veterans/TransferRequests.vue` (exists [M]) · route `/veterans/transfers`
(`routes/web.php:298`) · pager at `:134` · `data-testid="no-transfers"` empty state · new
`tests/browser/veterans-transfer-requests.spec.ts`.
**Steps.** Plan P3.7: drive the tabs, the pager and the empty state; clean up anything created in `afterEach`.
**Verify.** `PLAYWRIGHT_PORT=<private> PLAYWRIGHT_SCRATCH_DB=.scratch-uma/<session>/browser-<port>.sqlite npx playwright test tests/browser/veterans-transfer-requests.spec.ts --workers=1 --reporter=line`.
**Acceptance.** The spec passes on a scratch DB, asserts an empty state it can actually reach, and leaves no rows
behind (proven by a post-spec count).

### P3.8 Level-3 sweep, classification and skip census
**Objective.** The full run that P6 depends on, with nothing unexplained and no gate reported green while skipped.
**Depends.** P3.1–P3.7.
**Files.** None modified by the sweep; its output is the evidence.
**Steps.** Plan P3.8: `vendor/bin/pest --compact` and the browser suite on a private port/DB, one process at a time;
classify every failure; fix regressions at root; record pre-existing in `KNOWN-ISSUES.md`.
**Verify.** Each command's summary line **and** exit code quoted · the skip census re-derived (exactly 2 true skips
tree-wide: `DesignTokensTest.php:651`, `SkillAutomationTest.php:12`; the 11 `test.skip(true, ...)` calls in
`tests/browser` are data-conditional guards inside live cases).
**Acceptance.** Zero unclassified failures; a skipped contrast arm is reported as skipped, never as green.

---

## P4 — Register truth (the defect register becomes verdicts)

### P4.1 Reconcile the two open populations
**Objective.** Settle what "open" means before classifying anything.
**Depends.** P1.
**Files.** `KNOWN-ISSUES.md` (worktree, uncommitted, `+172/−3` vs HEAD).
**Steps.** Plan P4.1: re-run both readings — the 34 `KI-nn OPEN` status-line ids and the peer census's 53 body-open
entries (`.scratch-uma/roadmap-recon/ki-census.{md,json}`) — then classify each of the 19-entry gap as *status line
stale*, *duplicate block*, or *body-only openness*, citing the entry and line anchor.
**Verify.** `grep -oE '^KI-[0-9]+ OPEN' | sort -u | wc -l` = 34 at both refs · the census file's own counts quoted ·
the 7 duplicated ids listed.
**Acceptance.** One measured list of live entries with the method that produced each count. The 34 and the 53 are
both recorded with their definitions — neither is averaged, and neither is called "the" count again.

### P4.2 CODE_FIX bucket
**Objective.** Work the entries whose fix is product code.
**Depends.** P4.1.
**Files.** Per entry; the bucket is the census's CODE_FIX set (23 entries at its reading), starting from KI-59, 60,
61, 87, 91, 95, 98, 99, 102, 103, 104, 114, 116, 131, 136, 137, 147, 149, 151, 152, 159, 160, 162.
**Steps.** Plan P4.2: per entry — reproduce, fix, focused spec, `pint --test <paths>`, PHPStan on touched files, lore
added-lines 0, then the register write.
**Verify.** The focused spec's exit code · the register entry parses (`grep -A6 '^### KI-<n>'`).
**Acceptance.** Each entry ends CONFIRMED-and-fixed (with the proving command and the commit) or keeps its class with
its evidence. A register write is the deliverable, not a progress note.

### P4.3 OWNER_RULING bucket
**Objective.** Turn owner-gated entries into decision requests the owner can answer in one pass.
**Depends.** P4.1.
**Files.** The bucket (8 entries at the census's reading: KI-63, 101, 104, 128, 129, 130, 132, 150).
**Steps.** Plan P4.3: for each, write the question, the measured state, and both branches — no code executes before
the ruling.
**Verify.** Each request cites a command reading or an exact file:line.
**Acceptance.** Eight answerable requests; zero unruled code changes.

### P4.4 Stale and duplicate closures
**Objective.** Retire the entries the tree has already answered.
**Depends.** P4.1.
**Files.** `KNOWN-ISSUES.md` only.
**Steps.** Plan P4.4: close with the falsifying evidence (sha or reading), never renumber; duplicate blocks are
cross-referenced, not deleted, since a duplicate records a filing error worth keeping visible.
**Verify.** The changed entries parse; the duplicate ids still resolve.
**Acceptance.** Every closure names what closed it and what closure does not cover.

### P4.5 Dead-panel ruling
**Objective.** Resolve the four panel components with zero importers.
**Depends.** P4.1 (KI-130's real state).
**Files.** `resources/js/pages/Career/Cockpit/Panels/{CareerMilestonesPanel,HappyMeekPanel,PreFinalReviewPanel,UraMandatoryLadder}.vue`
(zero importers at HEAD [S]) · `SpiritBurstsPanel.vue`, `TeamRankLadderPanel.vue` (named only in `ScenarioPanel.vue`
comments).
**Steps.** Plan P4.5: present compose-or-delete to the owner; execute the picked branch. Do not delete another
session's component on this roadmap's authority.
**Verify.** `grep -rn '<ComponentName' resources/js` re-run after the change · `npm run build` exit 0 ·
`npm run typecheck` exit 0.
**Acceptance.** Either the panels are composed (and a spec reaches them) or they are deleted with the ruling recorded
in `KNOWN-ISSUES.md`.

---

## P5 — Feature and integration residuals (each gated on an owner ruling)

### P5.1 `/api/v1/skills` endpoint
**Objective.** Decide FR-E's true edge.
**Depends.** P1.
**Files.** `routes/api.php:10-17` (six endpoints: umamusume index/show, training-runs index/show, support-cards
index/show; **no skills** [M]) · `app/Http/Controllers/Api/V1/SupportCardController.php` (the mirror) ·
`ARCHITECTURE.md:313-316` (documents umamusume + training-runs only).
**Steps.** Plan P5.1: present both branches — (a) add read-only index+show mirroring the support-cards controller,
with a feature test and the contract row; (b) record "FR-E stops at six".
**Verify.** Branch (a): the new feature test green · the route appears in `php artisan route:list --json` · the
contract row added. Branch (b): the ruling recorded.
**Acceptance.** One branch executed and its evidence quoted; no endpoint is built before the ruling.

### P5.2 NFR-19 diagnostics
**Objective.** Resolve a requirement that `ARCHITECTURE.md` records as intentionally deferred.
**Depends.** P1.
**Files.** `PRD.md:393-395` (requirement) · `ARCHITECTURE.md:548-549` (deferral) · measured absence: no `diagnos*`
route, component, config or test [M].
**Steps.** Plan P5.2: owner picks build (dev-only middleware + Inertia page + tests, route guarded to local env) or
confirm the deferral as terminal and record it in the deferred list.
**Verify.** Build branch: the page renders on a local-env route with duration, query count and payload size, proven by
a feature test · `composer lore` 0. Deferral branch: the `ARCHITECTURE.md` line updated.
**Acceptance.** The requirement is either implemented behind a local-env guard or recorded as a terminal deferral —
never silently absent.

### P5.3 CI premise and `QWEN.md` accuracy
**Objective.** Make the operator-facing guide true, and settle what CI is.
**Depends.** P3.8 (the statements being corrected must be true when written).
**Files.** `QWEN.md` (untracked) · the three workflows · `AGENTS.md` §1 (its "No CI" half is falsified: three
workflows exist; its "every gate runs locally" half stands — none of the three runs pest/pint/phpstan/typecheck/the
ui-ratchet).
**Steps.** Plan P5.3: owner decides track-vs-ignore first; then correct only statements the tree falsifies
(artwork-mirror manual-ness — auto-mirror landed; uploads remain cut per `PRD.md` §6.13 — verify, do not delete; CI).
**Verify.** Each corrected sentence paired with the reading that falsified the old one · regeneration-risk note.
**Acceptance.** This is the one slice whose deliverable is partly prose, and it is permitted because the project's own
v3.0.0 exit bar requires `QWEN.md` to contradict nothing in the tree.

### P5.4 Externally gated items (no execution)
**Objective.** Keep the gate visible rather than fabricating progress.
**Depends.** P1.
**Files.** none.
**Steps.** Plan P5.4: record the three gates — trainer-supplied image uploads (owner threat-model sign-off), Docker /
devcontainer build (Docker absent), tool-config schemas (absent).
**Verify.** Each gate's absence re-measured at execution (`docker --version`, the schema path).
**Acceptance.** Three recorded gates with the command that proved each closed. **No slice here produces code, and no
slice here pretends to.**

### P5.5 BO-004 dark `To do` pill contrast
**Objective.** Convert the standing 4.15:1 measurement into a ruling.
**Depends.** P1.2 (the token is only committed there).
**Files.** `resources/css/app.css` (dark `--color-risk`) · the audit finding that recorded 4.15:1.
**Steps.** Plan P5.5: re-measure on `/` in dark theme with a probe; present both branches (replay a candidate token
and re-measure, or record "keep" with the owner's name).
**Verify.** The probe's computed colour and ratio quoted.
**Acceptance.** An owner decision recorded, and — if the token changes — P1.2's rebuild step re-run so the browser gate
reads the new value.

---

## P6 — Scenario coverage (gated on P3.8)

**Objective.** Walk the three scenarios no walk has covered.
**Depends.** P3.8 (no walk opens while a failure is unclassified).
**Measured state [M].** Panel specs exist for all four scenarios (`trackblazer-panel.spec.ts`,
`grand-concert-panel.spec.ts`, `ura-panel.spec.ts`, `unity-cup-panel.spec.ts`); **every recorded UX walk is Unity Cup**.
A panel spec is a render assertion, not a walk.

### P6.1 Trackblazer
**Objective.** The largest untested surface, five dedicated components.
**Depends.** P3.8.
**Files.** The wizard, the cockpit, the scenario detail and the run deck for Trackblazer; evidence into the audit's own
evidence directory.
**Steps.** Plan P6.1: drive a real run end to end, screenshot each screen, read every panel against its source.
**Verify.** Screenshots on disk + a value per reading (a screenshot without a value is a caption).
**Acceptance.** A findings register with per-screen readings; a scenario-specific defect filed, or a shared defect
attributed to the shared surface rather than to the scenario.

### P6.2 Our Grand Concert, then URA Finale
**Objective.** The thinnest coverage, then the most-covered scenario as a control.
**Depends.** P6.1.
**Files.** As P6.1, per scenario.
**Steps.** Plan P6.2: same walk discipline, same evidence standard.
**Verify.** As P6.1.
**Acceptance.** As P6.1; URA acts as the control that the method finds what earlier walks found.

---

## P7 — Release bar (`v3.0.0` exit)

**Objective.** The seven conditions of the v3.0.0 definition, each proven by a command on a settled tree.
**Depends.** P2, P3, P4, P5, P6.

### P7.1 The seven conditions
1. `vendor/bin/pest` green, no unexplained skips.
2. Browser suite green on a session-private harness; every fix-backed finding assertion-verified.
3. `vendor/bin/pint --test` clean (scoped) · `npm run typecheck` exit 0 · `make stan` at the recorded level.
4. WCAG AA on every token pair except documented carve-outs (BO-004 resolved by P5.5 or recorded).
5. Clean clone + install renders artwork with no manual command and shows a labelled placeholder offline
   (`ArtworkSlot.vue` placeholder confirmed present; auto-mirror path re-verified).
6. `QWEN.md` contradicts nothing in the tree (P5.3).
7. The ledger names every BO finding's terminal state.
**Steps.** Plan P7.1: run each condition, quote every command's summary and exit code.
**Verify.** The seven outputs, quoted.
**Acceptance.** Seven conditions pass, or the exceptions are named with the owner ruling that permits them. A skipped
arm is reported as skipped.

### P7.2 `VERSION.md` / `CHANGELOG.md`
**Objective.** Record the phase exits that produced them, in the project's own formats.
**Depends.** P7.1.
**Files.** `VERSION.md` (currently `3.0.0` pre-release, tags ≤ `2.1.1`) · `CHANGELOG.md` (Keep a Changelog, SemVer per
`VERSION.md` + `ADR-0019`).
**Steps.** Plan P7.2: update both for the phase exits landed; no version bump beyond what the releases justify.
**Verify.** `VERSION.md` head · `git tag` · the changelog's newest section.
**Acceptance.** The declared version, the tag list and the changelog agree; an untagged `3.0.0` is stated as
pre-release rather than implied released.

### P7.3 Tag request
**Objective.** The release act, which is the owner's.
**Depends.** P7.1, P7.2.
**Files.** none.
**Steps.** Plan P7.3: present the seven conditions' evidence, the version state and the tag to the owner.
**Verify.** The presented evidence is the quoted outputs, not a summary.
**Acceptance.** The owner cuts the tag, or records why the release waits. This slice ends in the owner's decision and
is the one place a decision is the deliverable.

---

## Progression guarantee

P1 banks real code and disposes real rows · P2 moves a lock file to a verified fixed version · P3 turns red suites
green with root-cause fixes and adds the missing spec · P4 converts a drifting register into verdicts · P5 implements
or records each feature requirement · P6 walks three untested scenarios · P7 proves the release bar by command. No
phase terminates in prose alone, and the four prose deliverables this roadmap permits (P5.3 `QWEN.md`, P7.2
`VERSION.md`/`CHANGELOG.md`, P7.3 the owner decision, and the `KNOWN-ISSUES.md` register writes a change obliges) are
the ones the project's own exit bar requires.

**Non-goals, stated so they cannot creep in.** No new feature absent from `PRD.md`; no schema change without an ADR;
no dependency added without an owner call; no test deleted or skipped without owner approval; no gate threshold
widened; no peer's uncommitted work adopted, restored or deleted on this roadmap's authority; no documentation update
substituted for a slice's code.
