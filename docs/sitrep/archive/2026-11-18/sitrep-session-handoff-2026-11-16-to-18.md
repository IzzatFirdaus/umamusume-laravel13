# SITREP — Full Session Handoff, 2026-11-16 to 2026-11-18

**Written:** 2026-11-18 13:2x +0800, against HEAD `816e923e` on `master`.
**Session span:** started at `d417a9a5` (2026-11-16 06:29), anchor commit `f737bc2b`. **50 commits** landed by all
sessions in that window; 29 of them are peer commits that arrived after my last commit.
**Purpose of this file:** let another agent resume without repeating anything below.

Two threads ran in this session. The first (roadmap/plan authoring) is closed and its artefacts were later
superseded by peers. The second (the debugging closeout around the pre-existing `review.spec.ts` failures) is closed
with all work verified in-tree. One artefact I created has since been **reverted by a peer commit** — §5 states it
plainly, because it is the single thing a resuming agent must not assume.

---

## 1. Original objectives, as they were stated

| # | Objective | Outcome |
| :--- | :--- | :--- |
| O1 | Author a roadmap in `docs/roadmap/` paired with a plan in `docs/plan/`, separate from a peer's pair, sharing cross-session facts only through mnemon | **Done**, pair written and committed; later marked SUPERSEDED by a peer regeneration |
| O2 | Make the roadmap execution-ready: phases, slices, exact paths, verification commands, no documentation-as-progress | **Done**; the pair carried 537 + 549 lines with per-slice acceptance bars |
| O3 | Close the outstanding `review.spec.ts` / `database.spec.ts` browser failures | **Done**; both target cases green, see §3 |
| O4 | Land the pieces of the visual-overhaul backlog that were still genuinely open | **Done**; 7 commits, §3 |

An owner ruling instructing `ignoreErrors` for two NativePHP symbols was **measured, then deliberately not
implemented**; see §4.3. That is the one place where the work departs from an explicit instruction, and the reason is
evidence, not preference.

---

## 2. What was measured, not assumed

Facts below are labelled **[M]** measured by a command in this session, or **[I]** inferred from files without
executing the behaviour.

- **[M]** At session start the working copy carried ~180 dirty paths, most peer-owned, and `AGENTS.md` held a peer's
  uncommitted whole-file rewrite (458 insertions / 782 deletions against HEAD).
- **[M]** `Umamusume::getRouteKeyName()` returns `slug`, so `career.trainee.profile` URLs must be built from the slug.
- **[M]** House tap-target floor is **44px** (`tests/utils/tap-targets.ts:52`), not WCAG 2.2's 24px minimum.
- **[M]** Page-level `position: sticky` is unavailable: `AppLayout.vue:616` puts `@container` on the canvas and
  containment ends sticky for descendants. KI-150 records "keep no page-level sticky" as the standing choice.
- **[M]** `tools/doc_census.py` counts only the **committed** tree, and `DocCitationParityTest` pins its output at an
  immutable baseline of **708** which may never be raised.
- **[M]** PHPStan fails a run when an `ignoreErrors` pattern matches nothing.
- **[M]** `nativephp/desktop` 2.3 supplies `Native\Desktop\Contracts\ProvidesPhpIni` and the `Window` facade, so the
  two "missing symbol" errors were already gone before any suppression was written.

---

## 3. Commits created in this session

All seven are ancestors of current HEAD **[M]**. Every one was staged with an explicit pathspec after reading
`git diff --cached --name-only`; none carries a peer path.

| Commit | Message | Files | Why |
| :--- | :--- | :--- | :--- |
| `c3b45049` | `fix(a11y): seed the focus-ring colour at rest and lift the dark risk token (BO-011)` | `resources/css/app.css`, `tests/Feature/DesignTokensTest.php` | Dark risk token `#FF4400` → `#FF6F3B`; contrast on raised/panel/page went 3.66/4.15/5.76 → **4.57/5.19/7.19**, all clearing 4.5 AA. Only the two relevant hunks of `app.css` were staged; three unrelated MWG CSS blocks were left alone. |
| `e73ddfe0` | `test(browser): retarget provenance marks to the button shape` | 8 browser specs + fixture | `ProvenanceMark` had already been migrated to an `interestfor` button by peer `25049095`; 8 specs still queried the retired `role="img"`. 20 locator sites migrated, 5 reverted because meter bars legitimately keep `role="img"`. |
| `302c6e6b` | `chore(ui): remove four orphaned cockpit panel components` | 4 `.vue` files, `tests/Feature/AbsenceChannelTest.php` | `CareerMilestonesPanel`, `HappyMeekPanel`, `PreFinalReviewPanel`, `UraMandatoryLadder` had zero importers. `spa.ts:22` globs `./pages/**/*.vue`, so every page-subtree file becomes a chunk whether or not anything imports it, which is why they still built. 412 lines deleted. |
| `3158a9b8` | `fix(a11y): render BaseButton's label so the button has an accessible name` | `resources/js/components/Form/BaseButton.vue` | `:label="Compare (0)"` fell through as a bare HTML attribute on a component with no `label` prop, so the button rendered empty and axe flagged `button-name`. Fixed in the component (`label?: string` + `<slot>{{ label }}</slot>`), so both call sites keep working. |
| `1a1aeca3` | `test(review): pin the pager order and the ruled no-match pill` | `tests/browser/review.spec.ts` | Two stale assertions, §4.1. |
| `2be83336` | `docs(config): promote docs/sitrep to standing directory` | `AGENTS.md` | One row added to the §3 map, on owner approval. **Since reverted by a peer — see §5.** |
| `7f99e3d6` | `docs(roadmap): stop citing files that do not exist` | roadmap/plan pair | 15 backticks citing non-existent paths, §4.2. |

---

## 4. Decisions and their evidence

### 4.1 `review.spec.ts` was two stale assertions, not two regressions

- `:126` demanded a pager pinned to the viewport after a 900px wheel. `661527e1d` (2026-11-11, UX-07) had already
  removed the `sticky bottom-0` declaration **deliberately**, because containment kills it (KI-150). The case was
  rewritten to pin the property the design does guarantee: the pager renders after the last queue row, and scrolling
  to it brings it into the viewport.
- `:332` wanted a `pill-caution` marker on the no-match pill. `2b864b51` had moved that state to `pill-neutral` under
  audit ruling AUD-20261102-021 F-02, on the grounds that a no-match is an absence rather than a warning. The marker
  is now excluded the same way the dropped card and note already were, and stays in `PAGE_MARKERS` because other
  pages legitimately draw it.
- Both were confirmed by `git blame`/`git show` before any edit. `review.spec.ts` went **11/11 green**.

### 4.2 The citation ratchet went red because my own docs became tracked

The full suite after `2be83336` reported `DocCitationParityTest` failing at **716** against the immutable **708**.
Attribution by running the census resolver across refs: `1a1aeca3` → 701, `aefd6549` → **716**. All 15 arrived in
`aefd6549`, the peer commit that landed my roadmap/plan pair, which had been untracked until then. Census reads only
the committed tree, so every backticked `.md` inside those files started counting at once.

Nine were `QWEN.md`, which the plans themselves describe as a deliverable that must **not** be created yet — so
backticking it made it read as a link a reader would follow. The rest named genuinely absent files. Raising the
baseline is forbidden, so the citations were repaired: names kept, backticks dropped. `7f99e3d6`, 15-for-15.
`DocCitationParityTest` then passed, and the full suite went **2436 passed / 0 failed**.

### 4.3 The NativePHP `ignoreErrors` ruling was implemented, measured harmful, and reverted

The owner ruled to add `ignoreErrors` entries for `NativeAppServiceProvider` and the missing NativePHP symbols.
Before writing them I ran the analyser: **`[OK] No errors`**. `nativephp/desktop` 2.3 is installed and provides the
exact namespace (`vendor/nativephp/desktop/src/Contracts/ProvidesPhpIni.php` declares
`namespace Native\Desktop\Contracts`). The entries were written, run, and produced
`Ignored error pattern ... was not matched` **×2** — PHPStan treats an unused suppression as an error, so the change
would have created two errors to hide two that no longer existed. `phpstan.neon` is byte-identical to HEAD after the
revert. **The ruling is not implemented, deliberately, and would break the gate today if applied.**

### 4.4 `AGENTS.md` was written through an isolated index

`AGENTS.md` is a protected agent-instruction file. The first write attempt was refused when its approval prompt timed
out, and that was left alone: a timeout is a deny and must not be retried through another path. After explicit owner
approval the row was committed **against HEAD's bytes** with a temporary `GIT_INDEX_FILE`, because the working tree
held a peer's whole-file rewrite and `git add -p` cannot isolate one line from a 23-line hunk. Result:
`git show --stat 2be83336` → `AGENTS.md | 1 +`.

Two side effects of that technique were caught: the real index still held a pre-commit entry staging a *deletion* of
the new row (`0 1` numstat), repaired with `git reset HEAD -- AGENTS.md`; and the peer's working copy predated the
commit, so their eventual rewrite dropped the row. That second one **did** fire — §5.

---

## 5. The one reverted artefact, stated plainly

**[M] `AGENTS.md` at HEAD no longer contains the `docs/sitrep/` row.** Chain, measured commit by commit:

| Ref | `docs/sitrep` mentions in `AGENTS.md` |
| :--- | :--- |
| `2be83336` (mine) | 1 |
| `f82b6bd0` (parent of the compaction) | 1 |
| `a5a87e9c` (`docs(contract): compact AGENTS.md and fold dated corrections`) | **0** |
| `816e923e` (HEAD) | **0** |

`a5a87e9c` rewrote 1,241 lines of `AGENTS.md` from a base that predated my commit, so the row was dropped. This is
functionally harmless — `docs/sitrep/` is now populated by **eight tracked sitreps**, so the directory is established
practice regardless — but **do not claim the §3 promotion still stands.** Either re-add the row or drop the claim.

---

## 6. Tests and outcomes

Every figure below is a command output from this session.

| Gate | Command | Result |
| :--- | :--- | :--- |
| Pest full (final, on my tree) | `vendor/bin/pest --compact` | **2436 passed (37,419 assertions), exit 0, 371.96s** |
| Pest full (first closeout run) | same | 2436 passed, exit 0, 338.45s |
| Pest full (**current HEAD `816e923e`**, peer tree) | same | **2 failed, 2467 passed (37,575 assertions), 458.74s** — see §6.1 |
| Citation ratchet, after fix | `--filter=DocCitationParityTest` | 2 passed (5 assertions) |
| Citation ratchet, **on current HEAD** | same | **1 failed, 1 passed** — census reads **808** vs baseline 708 (§7) |
| PHPStan | `vendor/bin/phpstan analyse --no-progress --memory-limit=1G` | `[OK] No errors`, zero suppressions |
| Pint | `vendor/bin/pint --test --format agent app tests` | passed |
| Typecheck | `npm run typecheck` | exit 0 |
| Build | `npm run build` | exit 0 |
| Playwright `review.spec.ts` | port 8205 | **11 passed** |
| Playwright `career-cockpit.spec.ts` | port 8183 | 33 passed |
| Playwright `career-cockpit-forms` + `scenario-panel` | port 8202 | 26 passed |
| Playwright `database.spec.ts` | port 8203 | 28 passed, 1 failed (`:305`, classified out of scope) |

Playwright port hygiene mattered: reusing port 8190 produced **four false failures** that all passed in isolation.

### 6.1 The two reds on current HEAD, both classified as peer-owned

The suite re-run on `816e923e` after my commits reported **2 failed, 2467 passed**:

- `DocCitationParityTest` — census **808 vs 708** (§4.2 method; my last commit left it at 699). Peer-introduced.
- `LineEndingGuardTest:57` — `resources/js/spa.ts` carries **87 CRLF** and 0 bare LF in the working tree, against a
  HEAD blob that is pure LF, and the file is dirty (` M`, 44/2 numstat). A peer is mid-rewrite without honouring
  `.gitattributes`' `eol=lf`. This is the same transient shape that bit the first closeout run; it resolves when the
  peer's editor writes LF, and it is not a defect in anything this session committed.

---

## 7. Open defects and risks a resuming agent inherits

Ordered by what a resume would want to touch first.

1. **[M] The citation ratchet is RED on current HEAD: `DocCitationParityTest` fails at 808 vs 708.** This is not mine;
   the number moved after `7f99e3d6` committed at 699. Attribute it the same way (§4.2): run the census resolver
   against the two refs either side of the suspect commit and diff by target. Do not raise the baseline.
2. **[M] `database.spec.ts:305`** expects a 5-item column-header list, the table renders 6 (an `Open the skill`
   column). Spec-versus-app drift, unrelated to this session, still open.
3. **[M] Working tree is peer-owned and moving.** At the time of writing: 31 dirty paths, 8 untracked, **6 stashes**.
   Untracked files include three peer sitreps dated 2026-11-18, `tools/gates.sh`, two new browser specs, and a
   `docs/audits/` tree. It moved again while this report was being written (34 dirty, 11 untracked moments later),
   so re-measure rather than trusting this count. Do not stage, stash, or reset any of it.
4. **[M] `tests/browser/review.spec.ts` is dirty again** — peer work replacing the second "No match" pill with an
   `Unknown` badge (A-96 duplicate-label defect). Not my change.
5. **[M] `docs/research-scratch/INDEX.md` is deleted in the working tree** while still tracked at HEAD.
6. **[I] `AGENTS.md`'s §3 row is gone** (§5). Whether to restore it is an owner call, not an agent one.
7. **[M] My roadmap/plan pair is formally SUPERSEDED.** `248e1cbb` / `47c148be` regenerated
   `docs/roadmap/project-roadmap-2026-11-16.md` and marked my pair superseded in its own line 3. The files remain
   tracked (537 and 549 lines) and my no-dead-backtick state survives inside them. Do not extend them.

---

## 8. Unverified or unmeasured

Stated so nobody inherits them as fact.

- **[I]** That the 808 census figure is peer-introduced rather than partly mine: I measured that my commits added
  zero `.md` refs when I wrote `7f99e3d6`, but I did not bisect all 29 peer commits after `3cbbd8b9`.
- **[I]** That `a5a87e9c` is the sole cause of the missing `AGENTS.md` row. Counts before/after it make it the only
  candidate, but I did not diff its 1,241-line change hunk by hunk.
- **[I]** Full Playwright suite green. Only the specs named in §6 were run; the last full browser sweep was never
  executed in this session.
- **[I]** `BaseButton`'s `label` prop causing no visual regression elsewhere: only two call sites were grepped
  (`SupportCardCatalog.vue:318` and `:370`), and no visual diff was taken.
- **[I]** Peer commits `cfd8bd78` and `816e923e` did not re-break anything I fixed. My commits are ancestors and the
  files carry my content, but I did not re-run the browser specs after those two landed.

---

## 9. Precise next steps, in order

1. `git rev-parse HEAD` and `git status --porcelain` — the tree moved five times during this session and peer commits
   land without warning. Re-measure before trusting anything in §5 or §7.
2. Reproduce the two current reds: `vendor/bin/pest --filter=DocCitationParityTest --compact` (expect 808 vs 708) and
   `vendor/bin/pest --filter=LineEndingGuardTest --compact`. If the line-ending one passes, the peer's editor has
   written LF and only the census remains true.
3. Attribute the census gap with the resolver-vs-two-refs diff (§4.2 method), then repair the citations. **Never raise
   the baseline.**
4. Run `vendor/bin/pest --compact` for the whole-suite gate.
5. If the owner wants `docs/sitrep/` back in `AGENTS.md` §3, re-add the row — one line, same table — but that file is
   protected and each write needs fresh approval; do not retry a timed-out prompt.
6. Leave the peer paths, untracked files, and 6 stashes alone.

---

## 10. Artefacts this session produced

| Path | State |
| :--- | :--- |
| `docs/sitrep/sitrep-visual-overhaul-2026-11-16-close.md` | **tracked** (peer `0cde2fae` landed it); this session authored it |
| `docs/sitrep/sitrep-visual-overhaul-2026-11-16.md` | tracked, same commit |
| `docs/roadmap/implementation-roadmap-2026-11-16.md` | tracked, 537 lines, **marked superseded** |
| `docs/plan/implementation-plan-2026-11-16.md` | tracked, 549 lines, **marked superseded** |
| `.scratch-uma/roadmap-recon/*` | untracked recon artefacts (KI census, FR coverage, verification surface) |
| `.scratch-uma/census-*.py`, `agents-sitrep-row.txt`, `sitrep-index` | untracked scratch from this pass |

mnemon records written this session: `498a24c1`, `8105f08e`, `e66ea923`, `2dbb20b0`, `966bc383`, `8964b7bb`
(wording imprecise, superseded), `3aab2661`, `383d823d`, `05f92e29`, `8fc4f731`. The mnemon CLI has **no update
verb** — records can only be added or forgotten, so a bad record is superseded rather than corrected.
