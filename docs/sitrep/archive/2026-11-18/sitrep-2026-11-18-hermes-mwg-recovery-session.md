# SITREP — Hermes session (roadmap authoring, MWG adoption, recovery gate)

**Date:** 2026-11-18
**Repository:** `D:\Projects\umamusume-laravel13`
**Branch:** `master` (no branch created by this session)
**HEAD at report time:** `816e923e` — `fix(uiux): close KI-160/KI-166 reflow and reconcile AUD-20261118-001`
**Divergence:** ahead of `origin/master` by **38**, behind by **8** (`git rev-list --left-right --count origin/master...HEAD` -> `8 38`)
**Working tree at report time:** 31 rows (22 ` M`, 8 `??`, 1 ` D`), **nothing staged** (`git diff --cached --name-only` empty), 6 stashes
**Commits made by this session:** 3 (`f8e428d9`, `25049095`, `c41abbb8`) — all three are ancestors of HEAD, verified
**Committed by this session:** 3 commits, 26 files total. **Pushed:** nothing. **Tagged:** nothing. **Reset:** nothing.

**Provenance note that matters for every "who did what" claim below.** Every commit in this repository is authored
`Hermes Agent <hermes@local>`, a shared identity used by every Hermes session, so commit metadata cannot attribute work
to a session. Session attribution is verifiable only where this session left its own artefacts: the private-index
message files under `.scratch-uma/mwg-2026-11-16/` and `.scratch-uma/recovery-2026-11-16/`. Three commits carry such
files. Everything else on `master` is attributed to "a peer session" without a name, because the tree cannot supply one.

The working tree moved under this session repeatedly: 207 rows -> 25 staged -> 0 staged -> 27 rows -> 31 rows, and HEAD
advanced from `d417a9a5` to `816e923e`. Every count below names the moment it was read. Any agent resuming must
re-measure rather than inherit.

---

## 1. Original objectives

Five requests were issued in sequence. Each is recorded because the next agent inherits all five obligations.

### 1.1 Objective A — author an execution-ready roadmap and plan pair

Create a complete, step-by-step implementation roadmap organised by phase and slice from the measured current state,
inspect the repository's files, functionality, architecture, tests and documentation, exclude completed work, name
exact objectives and tangible deliverables per remaining step, include dependencies and verification commands, and
prohibit documentation-only output from counting as progress. Deliverable: a roadmap file in `docs/roadmap` paired
with a plan file in `docs/plan`.

### 1.2 Objective B — separate pairs per author, shared state in mnemon

Verbatim: *"create separate roadmap and plans file from peers, shared information should only be in mnemon memory db
(hermes is setup to use mnemon)"*. This superseded the first draft's "consolidate into one canonical pair" framing.

### 1.3 Objective C — the Modern Web Guidance (MWG) implementation dispatch

Install and use MWG, run its `legacy-scan`, apply its guides under per-slice contracts, and bank the result in
commits — with `legacy-scan`'s ~20% coverage stated as a caveat and a prohibition on treating a clean scan as
compliance. Four (later six) owner rulings gate parts of it.

### 1.4 Objective D — the recovery gate dispatch

Recover seven commits dropped by an alleged 2026-11-16 history rewrite, preserving a peer's unstaged work first
(G1), then recover onto a throwaway worktree (G3), then reset and 3-way apply (G4), verify (G5), and write ledger
S70 (G6) plus a roadmap status line (G7).

### 1.5 Objective E — this report

A comprehensive, evidence-based SITREP and handoff saved to `docs/sitrep`, with no further implementation changes.

---

## 2. Completed work

### 2.1 Objective A — roadmap/plan pair (delivered, later committed by a peer)

Two files were authored and revised against measured state, not inherited text:

| File | Lines | State |
|---|---|---|
| `docs/roadmap/implementation-roadmap-hermes-2026-11-16.md` | 379 | tracked at HEAD (added by `98c6e3b2`), fence-clean |
| `docs/plan/implementation-plan-hermes-2026-11-16.md` | 316 | tracked at HEAD (added by `58f32e5d`), fence-clean |

Phase structure committed: **R0 baseline -> R1 bank -> R2 register truth -> R3 suite truth -> R4 CVE -> R5 feature
residuals -> R6 scenarios -> R7 release**, with R4/R5 hanging off R1/R3.

Five claims in the first draft were **falsified by measurement and rewritten** (each was a real error, not a wording
change):

1. "No factory produces BO-003" — false. `database/factories/UmamusumeFactory.php:27` mints the name via
   `Str::title(fake()->unique()->words(2, true))` and `:33` hard-codes `release_status` to `GlobalReleased`. The dev
   database row is factory-shaped, not a hand seed. Rewritten with the open decision (purge or ignore) retained.
2. `CareerView/Cockpit.vue` — no such directory. Correct path `resources/js/pages/Career/Cockpit.vue:632`.
3. "HEAD equals `origin/master`" — false; `git branch -vv` read **ahead 4 of** `10dee9d7`.
4. KI counts "37 headings / 6 open" — stale. Measured 112 headings / 29 open at HEAD, 114 / 31 in the worktree, and the
   delta is exactly two worktree-only entries.
5. A stale 88-row porcelain histogram was invalidated mid-session (88 -> 175 -> 196). The row was relabelled
   "moving — re-measure" rather than republished.

Two additions came from measurement rather than the brief: **R3.9** (no browser spec reached
`Veterans/TransferRequests.vue`; `grep -rln 'veterans/transfers\|TransferRequests' HEAD -- tests/browser` -> 0) and
**R7 skip-vs-ran condition** for the contrast arm.

### 2.2 Objective B — separation executed

- The earlier unsuffixed pair (`docs/roadmap/implementation-roadmap.md`, `docs/plan/implementation-plan.md`) was
  renamed to the `-hermes-2026-11-16` names and the old paths deleted.
- The "single canonical roadmap" claim, the four-row absorption table naming peer files, and every cross-pair
  reference were **removed**. Both files now state they stand alone and that cross-session facts live in mnemon.
- A supersession sentence this session had appended inside the shared source
  `docs/plans/implementation-roadmap-v3.0.0.md` (139 characters, one occurrence) was **reverted**, so no session pair
  is named from a shared file.
- Three peer pairs were left untouched: `implementation-roadmap-2026-11-16.md` + `implementation-plan-2026-11-16.md`,
  `v3.0.0-implementation-roadmap-2026-11-16.md` + `v3.0.0-execution-plan-2026-11-16.md`, and the tracked
  `docs/plans/*` sources.

### 2.3 Objective C — MWG installed, scanned, and adopted

**Install (ruling 1: vendored).**

- `npm install modern-web-guidance@1.0.0` was run and then **reverted**: `package.json` already carried a peer's
  uncommitted dependency upgrade (`@inertiajs/vue3` ^2.3.28 -> ^3.11.0, `@oddbird/popover-polyfill`,
  `interestfor`) and `npm` reconciled `package-lock.json` against it, producing a 500-line lock diff. Both the
  dependency line and the lockfile were restored; `git status` showed only the peer's three lines again.
- The package's skill payload was copied to `.hermes/skills/modern-web-guidance` (209 guides). `/.hermes/` is
  gitignored at `.gitignore:112`, so **the install is deliberately not in git** — it is reproducible from the npm
  package, not from the repository.
- `hermes config set skills.external_dirs '["D:/Projects/umamusume-laravel13/.hermes/skills"]'` was applied to
  `C:\Users\exatf\AppData\Local\hermes\config.yaml`; readback `- D:/Projects/umamusume-laravel13/.hermes/skills`,
  `hermes config check` exit 0. Re-verified at report time: still set.

**Version and corpus, quoted rather than assumed.**

- `node .hermes/skills/modern-web-guidance/modern-web.mjs --version` -> `1.0.0`
- `skill-version.txt` -> `2026_11_09-5bcc021b`; guide files -> 209
- `search dialog --limit 3` -> **10 results**: the flag is not a hard cap. Recorded because the dispatch assumed it was.
- `legacy-scan ./resources/js --json` -> `totalFindings: 0`, `activeGuideCount: 33`, `activeRuleCount: 35`
- `legacy-scan ./resources/css --json` -> same

The scan result is recorded as **data, not a gate**: the tool's own note says 33 guides / 35 rules is roughly 20% of
the corpus, so zero findings is not compliance. That caveat is carried in both the sitrep and the roadmap.

**Adoption, two commits.**

`f8e428d9` — `feat(forms): adopt MWG form-capture hints across the 17 audited files` (17 files, +128/−40, matching
`git diff HEAD --numstat` exactly). Content: `autocorrect` / `autocapitalize` / `spellcheck` / `enterkeyhint` /
`inputmode` hints, with `spellcheck` defaulted to `undefined` so an absent hint stays absent from the DOM rather than
rendering `spellcheck="false"` on an enumerated attribute; plus a fieldset/legend fix in `EventDecision.vue`, a
`<search>` landmark in `Runs/Index.vue`, and `closedby="any"` in `CardPicker.vue`.

`25049095` — `feat(ui): adopt top-layer dialog, popover and anchor-positioned provenance (MWG)` (8 files, +70/−26).
Content: `dialog:modal` / `dialog::backdrop` overscroll containment, the `[popover]:is(:popover-open,
.\:popover-open)` polyfill escape hatch, an `@supports (position-area: block-start)` anchor block for
`.provenance-tooltip`, `interestfor`-driven provenance tooltips, `eager` + `fetchpriority` on the LCP artwork slot,
and an IME-composition guard (`event.isComposing || event.keyCode === 229`) before submit-on-Enter. Per ruling 6 this
commit carried peer-authored app.css content with the peer's authorship named in the body.

**Stopped slices, with measured grounds.**

- **S4 (forms `:user-invalid`, `field-sizing`, error announcement):** stopped under the dispatch's own 3-component law.
  `:user-invalid` -> 0 references, `field-sizing` -> 0, `aria-invalid` is bound to server error today. Delivering it
  needs `BaseInput` + `BaseTextarea` + a shared invalid-state path + call-site changes.
- **S5 (view transitions, `@starting-style`):** `view-transition` -> 0, `@starting-style` -> 0. Adding motion is a
  `DESIGN.md` ruling, not a mechanical adoption.

### 2.4 Objective D — recovery gate: STOPPED at G0 (correct outcome)

No reset, no checkout, no stash operation, no worktree change. The dispatch's G4 `git reset --hard recover/phase1`
would have destroyed landed work. Three premises were falsified by measurement:

| Premise in the dispatch | Measurement |
|---|---|
| HEAD is at or near `e2739785` | `e2739785` **is** an ancestor of HEAD and sits **21 commits below it** (`git rev-list --count e2739785..HEAD` -> `21`). A reset would have deleted 19 commits present only on HEAD. |
| The pre-reset tree sits in the index | `git diff --cached --stat` read **25 files of removals** against HEAD (app.css −32, BaseInput −28, ArtworkSlot −11). Committing from it would have reverted that day's work. |
| A 04:53 history rewrite dropped commits | The last 60 reflog entries hold exactly one reset — `37fd9c88 HEAD@{2026-11-16 05:23:50} reset: moving to HEAD`, a no-op. 04:53 is `e2739785`'s own commit time. |
| A peer's unstaged `Dashboard.vue:563` work needed preserving | `trainee-scope` is already committed (`d330c82e`); `Dashboard.vue` and `DashboardController.php` were **clean** against HEAD. |
| `QWEN.md` needs a track/ignore ruling | The file does not exist on disk. Ruling 5 is moot. |

Ancestry of the dispatch's 17 protected shas at that moment (0 = ancestor, 1 = absent from HEAD):
`9c99aeaa 0 · 133e61bc 0 · 8aff0d83 0 · 86d649d3 0 · 24b0aba4 0 · abfd1e5f 0 · 5869d1a0 1 · 8ef841fb 1 ·
1392e30f 1 · 69488c1f 1 · f737bc2b 1 · d19f4da2 1 · 58333135 1 · b1650b4e 1 · 94f9407b 1 · e2739785 0 ·
ab6fda83 1`. All seven recovery targets stayed unreachable from HEAD; `cat-file -t` returned `commit` for each, so
no objects were lost.

G1 preservation ran anyway and is retained: `.scratch-uma/pre-recovery-2026-11-16/` holds `preflight.json` (5915 B),
`status.txt` (9278 B), `worktree-vs-HEAD.patch` (3069342 B), `index-vs-HEAD.patch` (33307 B), `peer-unstaged.patch`
(1457 B), `notes.txt` (2302 B), plus an earlier run's `head.txt`, `QWEN.md.copy`, `peer-work-vs-HEAD.patch`,
`untracked-files.txt` (no name collision; nothing clobbered).

**Owner ruling on the outcome: Option B — write S70 fresh; do not cherry-pick `ba34a3a1` / `c12229b1` / `79d8d33a`.**
`recover/phase1` (tip `79d8d33a`, base `df150cd8`) stays as orphaned evidence: 6 of its commits are not in HEAD, 19 of
HEAD's are not in it.

### 2.5 Objective E — the one open deliverable, executed

`c41abbb8` — `docs(plans): mark phase status on the roadmap to prevent re-execution`. One file, pathspec-only, private
index seeded with `read-tree HEAD` and passed as an `env=` mapping. Line 1 of
`docs/plans/implementation-roadmap-v3.0.0.md` now reads (verified present in the commit and still line 1 at HEAD):

> STATUS (2026-11-17): The 2026-11-16 recovery premise was falsified at G0 — no reset occurred; seven fixes landed
> on master via other routes. `recover/phase1` (79d8d33a) is preserved as orphaned evidence. `SLICE-RECORDS` at HEAD
> carries no S67–S69; the ledger gap is real and is future work. Rulings 1–4 shipped (MWG vendored, Baseline 2024,
> BaseInput folded, app.css EOF with peer authorship). Ruling 5 moot (QWEN.md absent).

That the file was untracked means `git show --numstat` reads `355 0` and `create mode 100644` — a whole-file add, not
the one-line insert the dispatch predicted. The dispatch's "one-line change, NOT a 355-line add" acceptance criterion
was unachievable for an untracked file and is recorded as a dispatch error, not a deviation.

---

## 3. Commits, branches, and their present reachability

| Sha | Subject | Session | At HEAD now |
|---|---|---|---|
| `f8e428d9` | feat(forms): adopt MWG form-capture hints across the 17 audited files | **this session** | ancestor |
| `25049095` | feat(ui): adopt top-layer dialog, popover and anchor-positioned provenance (MWG) | **this session** | ancestor |
| `c41abbb8` | docs(plans): mark phase status on the roadmap to prevent re-execution | **this session** | ancestor |
| `ad4b09cc` | fix(ci): fall back to packaged resources/build and capture acceptance diagnostics | peer (earlier, same identity) | ancestor |
| `c3b45049` | fix(a11y): seed the focus-ring colour at rest and lift the dark risk token (BO-011) | peer (landed the staged BO-011 half) | ancestor |
| `78b54a71` | feat(forms): adopt MWG form-capture hints across 16 audited files | peer | ancestor |
| `178a9079` | feat(forms): adopt MWG form-capture hints across 15 audited files | peer | ancestor |
| `98c6e3b2` | docs(roadmap): land dated roadmap tree | peer (added this session's roadmap) | ancestor |
| `58f32e5d` | docs(planning): land dated plan tree | peer (added this session's plan) | ancestor |
| `816e923e` | fix(uiux): close KI-160/KI-166 reflow and reconcile AUD-20261118-001 | peer | HEAD |

Branches present, none created by this session: `master`; `recover/phase1`; `salvage/bo010-ab6fda83`;
`salvage/phase1-94f9407b`; `origin/copilot/worktreecherry-pick-recover-nativephp-packaging`.

Worktrees present: the main tree plus six under `.scratch-uma/worktrees/` (`docs-corrections` `2142f553`,
`roadmap-baseline` `cfd8bd78`, `s18-release` `0bb3efd0`, `s2-head` `7f99e3d6`, `v3.0.0-s1-2026-11-17` `d723f9c3`)
and **one outside the repository**: `D:/tmp/td-test-f737` detached at `e1f4d66a`. That last one violates the
`AGENTS.md` §9 rule that every artefact stays inside the repository. It was not created by this session and was not
removed; it is flagged in §9.

---

## 4. Verification actually run (each command, its result, its ref)

Run during Objective C against a tree then at `d417a9a5`/`25049095`; the results describe that tree, not today's HEAD.

| Command | Result |
|---|---|
| `php vendor/bin/pest --filter DesignTokensTest --compact` | exit 0, `Tests: 17 passed (480 assertions)`, 0 skipped — **the contrast arm ran**, it did not skip |
| `npm run typecheck` | exit 0 |
| `npm run lint:ui` | exit 0, 3 pre-existing arbitrary-value reports in `Veterans/Index.vue` (`0.07em`, `12.5px`, `1.55`) |
| `npm run build` | exit 0, `✓ built in 39.03s` |
| `PLAYWRIGHT_PORT=8188 PLAYWRIGHT_SCRATCH_DB=.scratch-uma/mwg-2026-11-16/browser-8188.sqlite npm run test:browser -- tests/browser/career-deck-select.spec.ts` | **18 passed, 1 failed** |
| `git show --stat f8e428d9` | 17 files, +128/−40 — matches intent |
| `git show --stat 25049095` | 8 files, +70/−26 — matches intent |
| `git show --stat c41abbb8` | 1 file, 355 insertions — matches intent |
| `python tools/md_fence_check.py` on the pair + `docs/plans/…` | `0 invalid closing fence(s) in 3 file(s)`, exit 0 |
| `hermes config get skills.external_dirs` | `- D:/Projects/umamusume-laravel13/.hermes/skills` |

The one browser failure is classified **pre-existing / unrelated**: `career-deck-select.spec.ts:184` fails a
three-diff computed-style gate on `td` (`font-size` 14px vs fixture 12.5px, `padding` `8px 0` vs `7px 9px`). Neither
commit adds a font or padding rule; the only app.css additions are the dialog/popover/`@supports` blocks. It is not
recorded in `KNOWN-ISSUES.md` (`git grep -i 'career-deck\|three-diff' -- KNOWN-ISSUES.md` -> no match), so it needs a
KI row before it can be scheduled.

**Not run, and therefore not claimed:** the full `pest` suite, the full browser suite, `phpstan`, `pint`, `composer
lore` / `lore-code`, `composer audit`, `npm audit`. A prior session at the same tree recorded the two standing reds
(`nativephp-packaged.spec.ts:37,38` TS18047; phpstan `EnforceNativePHPSecret.php:13` calling `env()` outside `config/`);
this session did not re-measure them.

---

## 5. Decisions and rationale

1. **Date-suffixed filenames for the pair, not bare names.** A peer already held `docs/roadmap/implementation-roadmap.md`
   collision risk; dating the filename prevents another session's commit from silently overwriting the pair.
2. **Vendored MWG, not a package dependency.** Ruling 1 chose reproducibility inside `AGENTS.md` §5's approval rule.
   Consequence accepted: the install is gitignored and must be recreated, not restored.
3. **`legacy-scan` treated as discovery, never as a gate.** Its note states ~20% coverage; a zero-finding scan was
   recorded as a reading with that caveat attached everywhere it appears.
4. **`spellcheck` defaulted to `undefined`** rather than `false`, because `spellcheck` is an enumerated attribute and
   `false` renders `spellcheck="false"` on every input instead of omitting the attribute.
5. **Stop rather than improvise on S4/S5**, per the dispatch's own 3-component and design-ruling limits. Both stops are
   recorded with the measurements that justify them, not as "not done".
6. **Stop at G0 rather than execute G4.** A `reset --hard` onto a branch 21 commits behind HEAD is unrecoverable for
   19 commits. The dispatch's own abort condition fired; executing anyway would have been the failure mode it exists
   to prevent. This is the single most important decision in the session.
7. **Refuse to write S70.** `git show HEAD:docs/research-scratch/SLICE-RECORDS.md` contains **no `### S67`, `S68`,
   `S69` or `S70`** — the ledger's `### S<n>` headings are `S0b`, `S4b`, `S20`-family only. Appending S70 would backfill
   a gap while pretending to append, so the decision was escalated and the owner ruled Option B: write it fresh, later,
   as its own task. Re-verified at report time: still absent.
8. **Mnemon, not markdown, for cross-session state.** Per Objective B, three durable entries were written rather than
   being duplicated into a pair file or a peer's source.

---

## 6. Files created or modified

**Created and committed (by this session, or added by a peer from this session's work):**

| Path | Lines | State |
|---|---|---|
| `docs/roadmap/implementation-roadmap-hermes-2026-11-16.md` | 379 | tracked (`98c6e3b2`), fence-clean, line 1 still the session header |
| `docs/plan/implementation-plan-hermes-2026-11-16.md` | 316 | tracked (`58f32e5d`), fence-clean |
| `docs/plans/implementation-roadmap-v3.0.0.md` | 355 | tracked (`c41abbb8`), first add, STATUS line 1 |

**Created and deliberately uncommitted (gitignored):**

- `.hermes/skills/modern-web-guidance/` — vendored MWG 1.0.0, 209 guides. `/.hermes/` is ignored, so this is a local
  install, not repository content.
- `.scratch-uma/mwg-2026-11-16/` — `scan-before.json` (281 B), `scan-before-css.json` (282 B), `measured-list.txt`
  (1458 B), `s1-numstat.txt` (829 B), the two commit-message files, two private indexes, the browser scratch sqlite.
- `.scratch-uma/recovery-2026-11-16/` — `sitrep-2026-11-16-recovery.md` (6854 B), `commit-msg-g7.txt`, `index-g7`.
- `.scratch-uma/pre-recovery-2026-11-16/` — the ten preservation artefacts listed in §2.4.
- `.scratch-uma/roadmap-recon/` — `fr-coverage.{json,md}`, `ki-census.{json,md}`, `verification-surface.{json,md}`
  (from an earlier subagent batch in this session).

**Modified in the working tree, not by this session, not committed:** 22 ` M` rows and 1 ` D`, listed in §8.

---

## 7. Outstanding tasks, in execution order

1. **Re-measure before anything else.** `git rev-parse --short HEAD`, `git status --porcelain`, `git diff --cached`.
   HEAD has moved 50 commits since `d417a9a5` and today alone has taken 23. Nothing in this report's counts is stable.
2. **Write S70 as a backfill, not an append.** The ledger has a structural gap: the fix commits landed, their ledger
   sections did not. The entry must say the sections are absent and why, and must not claim an incident the reflog
   does not show. Suggested content is durable in mnemon entry `3d7114d4`.
3. **File a KI row for the `career-deck-select` three-diff failure** (`td` font-size 14px vs 12.5px, padding
   `8px 0` vs `7px 9px`) so the release bar can name it, since it is currently only in a session sitrep.
4. **Reconcile the MWG net result before claiming it.** `f8e428d9` was reverted by `78b54a71` and re-applied by
   `178a9079`. Net: 19 `autocorrect` occurrences across 16 files, identical to the post-`f8e428d9` count, but
   `SearchableSkillPicker.vue` (−7) and `TraineeSelect.vue` (−6) differ from this session's version. Someone should
   confirm the surviving variant is the intended one rather than two sessions fighting over the same hunks.
5. **Decide the coupled dependency set** (`spa.ts` + `polyfills.js` + `package.json` + `package-lock.json`). Partly
   resolved by a peer: all three files are in HEAD, and `package.json` at HEAD carries `@inertiajs/vue3 ^3.11.0`,
   `@oddbird/popover-polyfill ^0.7.3` and `interestfor ^1.0.14`. What is not verified is whether `package-lock.json`
   in HEAD matches those ranges — this session never ran a clean `npm ci` to prove it.
6. **S4 and S5 remain unstarted** and require a design ruling (`DESIGN.md`) and a 4-file scope respectively. Do not
   start them as "small MWG follow-ups"; they are not.
7. **`recover/phase1` disposition.** It is orphaned by ruling. Either keep it as audit evidence with a note, or delete
   it as part of a deliberate cleanup — but not silently.
8. **Move or remove `D:/tmp/td-test-f737`.** It is a git worktree outside the repository, against `AGENTS.md` §9.

---

## 8. Known risks and uncommitted state

**Working tree at final read (31 rows, 22 ` M` / 8 `??` / 1 ` D`, nothing staged):**

- ` D docs/research-scratch/INDEX.md` — **deleted on disk, deletion unstaged**. The file is present at HEAD. A
  `git checkout` or a peer's pathspec commit would record a deletion nobody decided on. Not this session's action.
- ` M KNOWN-ISSUES.md` (7/7 lines), `composer.json`, five `docs/adr/*`, six `docs/plan/*-2026-11-17.md`,
  `docs/proposals/remaining-implementation-2026-11-17.md`, `docs/roadmap/IMPLEMENTATION-ROADMAP-2026-11-18.md`,
  `docs/roadmap/remaining-work-roadmap-2026-11-17.md`, and source files `Career/Timeline.vue`, `Dashboard.vue`,
  `Review/Index.vue`, `Veterans/TransferRequests.vue`, `spa.ts`, plus three browser specs.
- `?? docs/audits/uiux-comprehensive-walk-2026-11-18/` (6 files), `docs/roadmap/INDEX.md`,
  `docs/sitrep/sitrep-2026-11-18.md`, `docs/sitrep/sitrep-2026-11-18-sweep-reconciliation-and-patch-forensics.md`,
  `tests/browser/navigation-focus.spec.ts`, `tools/gates.sh`.

Risks a resuming agent must carry:

1. **Counts drift.** This tree took 207 -> 25 -> 0 -> 27 -> 31 rows and moved HEAD five times inside this session. Any
   number quoted here is a timestamp, not a fact.
2. **Shared identity.** `Hermes Agent <hermes@local>` is used by every session, so a commit's author cannot identify
   who did the work. Attribution needs artefacts.
3. **Peer index hazards, observed twice.** A peer's staged set once carried a **deletion** of
   `docs/plans/implementation-roadmap-v3.0.0.md` while the worktree held a new copy, and their staged app.css/forms
   content was the pre-MWG variant. Both resolved, but the pattern repeats: re-read `git diff --cached` before any
   commit of your own.
4. **The vendored skill is not in git.** A fresh clone has no MWG. `skills.external_dirs` points at a directory that
   will be empty, which fails silently as "skill not found", not as an error.
5. **Six peer-owned stashes** (`stash@{0}`..`{5}`) must not be popped or applied.
6. **`recover/phase1` looks like a recovery base and is not.** It is 21+ commits behind and carries a ledger lineage
   that never reached master. Any future agent that treats it as a base repeats the G4 hazard.

---

## 9. Deviations from the briefs

1. **The roadmap pair was renamed rather than consolidated.** Objective A said "consolidate existing text file of this
   roadmap"; Objective B overrode that with "separate pairs, shared facts in mnemon". B wins, and the shared source
   files were left unedited.
2. **`npm install` was executed and then reverted.** The dispatch assumed a clean dependency state; the tree had a
   peer's uncommitted upgrade. The package-manager route was abandoned for a copy-and-wire route.
3. **The AGENTS.md `Baseline 2024` line was written but not committed by this session**, because that file carried a
   peer's 458/782-line uncommitted rewrite. It has since landed: `git log -S "Baseline 2024" -- AGENTS.md` ->
   `a5a87e9c docs(contract): compact AGENTS.md and fold dated corrections`. Verified present at HEAD.
4. **The recovery sitrep was written to a session subdirectory, not the dispatch's named path.**
   `.scratch-uma/recovery-2026-11-16/sitrep-2026-11-16-recovery.md` instead of a `.scratch-uma/` root file, because
   `AGENTS.md` §9 forbids writing to the shared `.scratch-uma/` root. The dispatch's path was the weaker instruction.
5. **This report is dated 2026-11-18 and saved under `docs/sitrep/`**, not `.scratch-uma/`, because the request named
   `docs/sitrep` and that directory is the established home for these (nine prior reports).
6. **G6/G7 of the recovery dispatch were not executed as written.** G7 shipped as `c41abbb8`; G6 was refused on
   measured grounds and replaced by an owner ruling.

---

## 10. State block

```
head:                816e923e (master, ahead 38 / behind 8 of origin/master)
session_commits:     f8e428d9, 25049095, c41abbb8 — all ancestors of HEAD: yes
commits_since_basis: 50 since d417a9a5; 23 authored today
working_tree:        31 rows (22 M / 8 ?? / 1 D), staged: empty, stashes: 6
staged_by_session:   nothing
pushed_by_session:   nothing
tagged_by_session:   nothing
reset_by_session:    nothing
mwg_install:         .hermes/skills/modern-web-guidance (gitignored), v1.0.0, 209 guides,
                     skill-version 2026_11_09-5bcc021b
mwg_wiring:          skills.external_dirs = D:/Projects/umamusume-laravel13/.hermes/skills (verified at report time)
mwg_scan:            0 findings js, 0 findings css; 33 guides / 35 rules = ~20% of corpus — NOT a compliance signal
mwg_net_result:      19 `autocorrect` occurrences / 16 files (f8e428d9 reverted by 78b54a71, re-applied by 178a9079)
baseline_target:     AGENTS.md "Baseline 2024" — landed in a5a87e9c, verified present at HEAD
design_tokens_contrast_arm: ran (17 passed / 480 assertions / 0 skipped)
roadmap_pair:        docs/roadmap/implementation-roadmap-hermes-2026-11-16.md (379L) +
                     docs/plan/implementation-plan-hermes-2026-11-16.md (316L) — both tracked, both fence-clean
roadmap_status_line: docs/plans/implementation-roadmap-v3.0.0.md line 1 = STATUS (2026-11-17) — present at HEAD: yes
recovery_gate:       STOPPED at G0, zero state-changing operations; premises falsified with readings (§2.4)
ledger_S70:          NOT written — SLICE-RECORDS at HEAD has no S67/S68/S69/S70 (re-verified)
recover_phase1:      79d8d33a, orphaned evidence, 6 ahead / 19 behind HEAD, not merged, not removed
pre_existing_reds:   nativephp-packaged.spec.ts:37,38 TS18047 | phpstan EnforceNativePHPSecret.php:13 |
                     career-deck-select.spec.ts:184 three-diff drift (no KI row yet)
rulings:             1 vendored (done) | 2 Baseline 2024 (landed) | 3 BaseInput folded (landed) |
                     4 app.css EOF with peer authorship (landed) | 5 QWEN.md moot (absent) |
                     6 peer app.css authorship (landed) | 7 dependency set (peer-landed; lockfile unverified)
```

---

## 11. What the next agent should do first

1. Re-measure HEAD and the tree; discard every count above that disagrees.
2. Read `docs/plans/implementation-roadmap-v3.0.0.md` line 1 for the standing status, then the roadmap pair for scope.
3. Write S70 as a **backfill entry naming the gap** (mnemon `3d7114d4` holds the content spec), and file a KI row for
   the `career-deck-select` three-diff drift.
4. Prove the lockfile with `npm ci` on a scratch copy before anyone tags a release on the strength of it.
5. Leave `recover/phase1`, the six stashes, and the five untracked peer artefacts alone unless the owner names them.
