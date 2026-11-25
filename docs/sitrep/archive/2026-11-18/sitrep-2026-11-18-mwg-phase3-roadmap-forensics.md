# SITREP — MWG Phase 3, roadmap authoring, and the P1.1 attribution forensics

**Written:** 2026-11-18 13:2x +0800 (Malay Peninsula Standard Time)
**Repository:** `D:\Projects\umamusume-laravel13`, branch `master`, upstream `origin/master`
**HEAD at report time:** `816e923e` — `fix(uiux): close KI-160/KI-166 reflow and reconcile AUD-20261118-001`
**Divergence at report time:** behind `origin/master` by 8, ahead by 38 (`git rev-list --left-right --count origin/master...HEAD` → `8	38`)
**Commits made by this session: ZERO.** Nothing was ever staged, committed, pushed, branched, reset, or tagged by it.
**Staged set at report time:** empty (`git diff --cached --name-only` returned nothing).
**Stashes:** 6 (`git stash list`).
**Working-tree size during this session:** read at 24 rows, then 34 rows, then 34 again. The tree moved under the session; every count below names the moment it was read.

**Scope note.** A sibling SITREP for this session lineage already exists at
`docs/sitrep/sitrep-2026-11-18-hermes-mwg-recovery-session.md` (419 lines, written 13:21). It covers the roadmap
authoring and MWG adoption from a different context window. This file does not repeat it: it records what *this*
window executed, and it resolves the one question that file leaves open, which is what happened to the 17-file
uncommitted diff that the P1.1 gate was built around.

---

## 1. Provenance warning that governs every "who did what" claim in this repository

Every commit on this branch is authored `Hermes Agent <hermes@local>`. That is one shared identity used by every
Hermes context window and every peer session working this tree, so **commit metadata cannot attribute work to a
session**. Three consequences follow, and they are load-bearing for everything below:

1. A commit whose subject matches work this session did is **not** proof this session committed it. The commit can be
   a peer's re-application of the same edit, or a continuation window that inherited the worktree.
2. The reverse also holds: a peer's commit can silently adopt this session's uncommitted edits, because
   `git commit <path>` commits that path's *working-tree* content, not the author's intent.
3. Session attribution is verifiable only where a session left its own artefacts: `.scratch-uma/<session>/` files,
   private-index message files, or mnemon entries. Everything else is "a peer context window" without a name.

This report therefore says **"landed in commit X"**, never **"I committed X"**, except where it states the verified
negative: this session made no commit at all.

---

## 2. Original objectives, as they were stated

| # | Objective | Verbatim driver | Outcome |
| :--- | :--- | :--- | :--- |
| O1 | Execute MWG remediation Phase 3, closing Slices 3.1 through 3.4 with measured evidence and no invented progress | full-roadmap hybrid execution prompt (`…19-30-07-407_89f1cf.txt`), then the focused 3.4 brief (`…19-58-21-546_6ac752.txt`) | **Done.** 3.1 CLS no-op, 3.2 16 files, 3.3 one file, 3.4 measured no-change |
| O2 | Author an execution-ready roadmap in `docs/roadmap/` paired with a plan in `docs/plan/`, based on measured state, skipping completed work, with no documentation-only deliverable counted as progress | roadmap brief of 2026-11-16 | **Done.** Pair authored, committed, later marked SUPERSEDED by a peer regeneration |
| O3 | Keep that pair separate from peers; put shared information only in the mnemon memory DB | *"create separate roadmap and plans file from peers, shared information should only be in mnemon memory db"* | **Done.** Cross-pair text stripped from both files; two mnemon entries written and recall-verified |
| O4 | Write a comprehensive evidence-based SITREP and handoff, save to `docs/sitrep`, make no further implementation changes | this request | **This file.** No source file was modified to produce it |

---

## 3. State at the start of this lineage versus state now (all measured)

| Reading | At session start (2026-11-16) | Now (2026-11-18 13:2x) | Evidence |
| :--- | :--- | :--- | :--- |
| HEAD | `d417a9a5` | `816e923e` | `git rev-parse --short HEAD`; `d417a9a5` confirmed ancestor of HEAD |
| Commits landing in between | — | **50** | `git rev-list --count d417a9a5..HEAD` |
| Working tree | 82 rows, then 86, then 97 | 34 rows | `git status --porcelain \| wc -l` at three moments |
| Staged | empty, then peer `Veterans/Index.vue`, then empty | empty | `git diff --cached --name-only` |
| The 17 MWG files | all uncommitted ` M` | **all clean, none dirty** | `git status --porcelain -- <17 paths>` → empty |
| MWG diff size | +122/−30 attributed as mine | landed as **+128/−40 across 17 files** | `git show --numstat --format='' f8e428d9` |
| My roadmap file | 13,149 B / 111 lines | **537 lines** (peer-rewritten) | `wc -l` |
| My plan file | 8,992 B / 104 lines | **549 lines** (peer-rewritten) | `wc -l` |
| Pair status | active | both carry `> **SUPERSEDED 2026-11-16**` markers at line 3 | read of both files |
| `KNOWN-ISSUES.md` | OPEN parse = 6 ids | 41 `## KI-` headings, **27** carrying `Status: OPEN` | `grep -c` |
| `:8181` / `:8184` | PID 28080 LISTENING on `:8181` | **clear** | `netstat -ano` |
| `docs/sitrep/` | ad-hoc, 8 tracked files | **standing directory**, 8 tracked + 6 peer SITREPs written 13:10–13:22 | `ls -la`, `git ls-files docs/sitrep` |

The tree moved repeatedly during this window: porcelain read 24, then 34, with `Dashboard.vue`,
`review.spec.ts`, `audit-followup-2026-11-18.spec.ts` and `tools/gates.sh` appearing between reads. Those are peer
rows. No row in the working tree at report time belongs to this session.

---

## 4. Work completed by this session

### 4.1 MWG Phase 3 slices

| Slice | What was done | Verification that was run | State now |
| :--- | :--- | :--- | :--- |
| 3.1 | Closed before this window opened (CLS 0.00000 over 15 navigations; a 0.1319 fixture proved the instrument could see layout shift) | prior window | Closed, no code |
| 3.2 | Form-capture hints adopted across 16 audited files, built on `BaseInput.vue` gaining `autocapitalize` / `spellcheck` / `inputmode` props. An inverse check caught a Boolean-cast defect (`spellcheck="false"` rendering as a truthy HTML attribute); fixed with `withDefaults(..., { spellcheck: undefined })` | `npx vue-tsgo -p tsconfig.vue.json` exit 0, `npm run build` exit 0 | **Landed.** `spellcheck: undefined` present at `resources/js/components/Form/BaseInput.vue:50` in HEAD |
| 3.3 | `fieldset`/`legend` added to `Career/EventDecision.vue` (+35/−28); `Runs/Index.vue` recorded as a no-op because it already carried `<search aria-label="Filter careers">` | `vue-tsgo` exit 0, build exit 0, 10/10 browser + 11 feature tests green | **Landed.** `fieldset` at `EventDecision.vue:271` and `:303` in HEAD; `<search` at `Runs/Index.vue:119` |
| 3.4 | Profiled `content-visibility` eligibility. Retrieved the MWG `performance` guide via the CLI, found the brief's quoted rationale absent from the CLI output, and cited the CLI's actual DO/DON'T pair instead. Profiled `/veterans` and a capped control (`/support-cards`) over three runs each | `probe-cv.mjs` via CDP: `/veterans` 469 nodes / 12 rows / ΔLayout 0 / 64.2 fps / one 61 ms long task; `/support-cards` 1394 nodes / 37 rows / ΔLayout 0 / 64.1 fps / one 67 ms long task | **Closed, no code change.** `grep -rl 'content-visibility' resources tests` → 0 matches, so the decision still holds |

Measured artefact: `.scratch-uma/mwg-34-20261116/cv-profile-summary.json`, 4,421 B, three per-run records per route
with every delta field at 0. Scratch directories `.scratch-uma/mwg-33-20261116/` (23 files) and
`.scratch-uma/mwg-34-20261116/` (5 files) both survive and are gitignored via `.gitignore:137`.

### 4.2 The roadmap and plan pair

Authored from measured state, then committed by a peer context window in `aefd6549`
(`docs(plans): correct §0 to measured tree at 50e31d14`, `2026-11-16 22:05:51`). The pair has since been rewritten
by peers to 537 and 549 lines and both files now open with a `SUPERSEDED 2026-11-16` marker pointing at
`docs/roadmap/project-roadmap-2026-11-16.md` and `docs/plan/project-plan-2026-11-16.md`.

**The separation ruling (O3) is intact.** After the ruling, the collision row naming the peer pair and the
"Canonicality note" paragraph were removed from both files, and the shared facts were re-homed in mnemon. Verified
at the time by grep: zero hits for `docs/roadmap.md`, `docs/plan.md`, `v3.0.0-implementation-roadmap`,
`v3.0.0-execution-plan`, `collision`, `canonical`, `concurrent peer` in either file. The peer rewrites that followed
re-introduced their own consolidation tables, which is their session's business, not a regression of this one.

### 4.3 Mnemon entries this session owns

| id | category / importance | Content gist |
| :--- | :--- | :--- |
| `498a24c1-3d24-494e-9578-f85dec60f1ef` | decision / 5 | Pair separation ruling: this session's pair paths, the peer pair paths, both consolidating the same two untracked sources, cross-pair information living only in mnemon |
| `8105f08e-824d-40a0-aed5-ac2005b92f64` | fact / 4 | Peer pair relocation: root `docs/roadmap.md` and `docs/plan.md` gone from disk, re-landed as `docs/roadmap/v3.0.0-implementation-roadmap-2026-11-16.md` and `docs/plan/v3.0.0-execution-plan-2026-11-16.md` |

Both were written through the **`mnemon` CLI**, not the in-process tools: `mnemon_remember` and `mnemon_recall`
both fail with `Provider mnemon does not handle tool <name>`. The CLI path works (`mnemon status` reports 738 insights,
8,992 edges at `C:\Users\exatf\.mnemon\data\default\mnemon.db`). Recall round-trip verified both entries at scores
0.929 and 0.698.

---

## 5. The P1.1 gate, and how it actually resolved

This is the single most important section for a resuming agent, because the answer is not what the roadmap predicted.

### 5.1 What the gate was

The roadmap's P1.1 asked the owner to choose A/B/C/D for banking 17 uncommitted MWG files: two of them
(`CardPicker.vue`, `SearchableSkillPicker.vue`) carried interleaved peer hunks, so a naive commit would have swept a
peer's in-flight R6-24 pager work into this session's commit. The decision package was presented with a per-file
attribution table (12 clean / 5 interleaved) and four options with risks. **No option was ever executed by this
session**, and no answer was ever given in this window.

### 5.2 What happened instead (measured, not reported)

| Commit | Timestamp | Subject | Files | numstat |
| :--- | :--- | :--- | :--- | :--- |
| `f8e428d9` | 2026-11-16 18:12:56 | `feat(forms): adopt MWG form-capture hints across the 17 audited files` | **17** | **+128 / −40** |
| `78b54a71` | 2026-11-16 18:25:21 | `feat(forms): adopt MWG form-capture hints across 16 audited files` | 16 | +40 / −123 |
| `178a9079` | 2026-11-16 18:29:34 | `feat(forms): adopt MWG form-capture hints across 15 audited files` | 14 | +113 / −37 |

Read together, the sequence is a re-apply: `f8e428d9` banked the full 17-file diff, `78b54a71` largely reverted it,
and `178a9079` re-applied it to 15 files. The two files that dropped out of the final re-apply are precisely the two
that had been flagged as interleaved (`CardPicker.vue`, `SearchableSkillPicker.vue`) — and both nevertheless carry
their hint attributes at HEAD, because their hints had arrived through other commits:

- `resources/js/components/support/CardPicker.vue:211-212` has `autocapitalize="none"` and `:spellcheck="false"`; the file's last commits are `f8e428d9` and peer `df150cd8` (R6-24 pager).
- `resources/js/components/Form/SearchableSkillPicker.vue:190-191` has the same pair.

### 5.3 What this means for a resuming agent

- **P1.1 is moot.** All 17 files are clean at HEAD. There is nothing to commit and nothing to park. Do not re-open the A/B/C/D question.
- The Boolean-cast fix survived all three commits: `spellcheck: undefined` is at `BaseInput.vue:50` in HEAD.
- The interleaving risk that motivated the gate never materialised as a bad commit. Whether that is luck or correct ordering cannot be determined from the tree; the outcome is verified, the mechanism is not.

---

## 6. Roadmap and plan claims falsified by later measurement

The roadmap pair was written against HEAD `d417a9a5`. Three of its claims are now wrong, and a resuming agent must
not execute them.

| Claim as written | Measured now | Verdict |
| :--- | :--- | :--- |
| "`EventDecision.vue` (444 CRLF) and `career-cockpit-forms.spec.ts` (374 CRLF) need CRLF→LF conversion" | All 17 MWG files clean; `fieldset` present in HEAD | Superseded. The same class of claim was later made about `tests/browser/career-trainee-select.spec.ts` and `wizard-flow.spec.ts`, and **that is also falsified**: measured `CRLF=0 / bareLF=762` and `CRLF=0 / bareLF=112`. Both are LF-clean in the working tree. |
| "`tools/lore.php` must be called directly because composer is broken" | All expected tools present: `tools/lore.php`, `tools/doc_census.py`, `tools/ui-lint-baseline.json`, `Makefile`; `tools/gates.sh` is a new peer file | Partly stale. `Makefile` exists with `test`/`lint`/`stan`/`lore`/`audit` targets, so the "no make" trap (KI-4) is dead on this host. |
| "OPEN KI parse measures 6 ids (KI-100, 105, 106, 131, 132, 163)" | 41 `## KI-` headings, 27 rows carrying `Status: OPEN` | Wrong by an order of magnitude. The 6-id parse caught one status-line shape only. The peer pair's "20+" was closer; neither number should be quoted as *the* count without naming the parse method. |

Two structural facts that hold and should be inherited:
`tests/Feature/LineEndingGuardTest.php` is the only line-ending guard (no `tests/Browser/` copy), and it walks
`resources/js`, `resources/views`, `tests`, `app`, `config`, `database`, `routes` for
`php|vue|ts|json|css` — so a CRLF regression anywhere in those roots fails it, with the offending path named.
`tools/ui-lint-baseline.json` pins `count: 209`, unchanged from the roadmap's reading.

---

## 7. Decisions and rationale

**D1 — Slice 3.4 shipped no code.** The MWG `performance` guide excludes capped lists by its own wording ("DON'T
apply on smaller, simpler pages"), and both profiled routes showed zero scroll-phase layout passes at 64 fps. The
Safari find-in-page gate was never the deciding ground, and the report said so rather than borrowing a stronger
reason. A no-op slice that names which gates were inapplicable is a complete slice; a no-op that omits them is not.

**D2 — A brief's quote was checked, found absent, and not obeyed.** The 3.4 brief quoted the guide as excluding
"hundreds of offscreen elements / VS Code webview panels with 20–30 items". That sentence is not in the CLI output.
The report cited the CLI's actual DO/DON'T pair and flagged the discrepancy. The owner subsequently confirmed the
paraphrase was theirs and the correction was right.

**D3 — Cross-pair information lives only in mnemon.** Written into both file pairs as an instruction, then enforced
by deleting the cross-pair text. The rationale is that two sessions writing about each other's files produce
guaranteed staleness; a shared fact belongs where both can read it and neither can corrupt it.

**D4 — Nothing was staged despite a presented recommendation.** The 4.1 package recommended option C (wait for peer
landings) with A as fallback, and stopped. The recommendation was not executed, because an irreversible-action gate
is not satisfied by an agent's own recommendation.

---

## 8. Tests and outcomes

Only what actually ran is listed. No suite was run in *this* context window; the runs below belong to earlier windows
of this lineage and are labelled as such.

| Gate | Result | Who ran it |
| :--- | :--- | :--- |
| `npx vue-tsgo -p tsconfig.vue.json` | exit 0 (after 3.2 and again after 3.3) | earlier window of this lineage |
| `npm run build` | exit 0, 1109 modules, 46.2 s (3.4 preflight), and exit 0 again in 3.3 | earlier windows |
| Browser specs for the touched pages | 10/10 green (3.3) | earlier window |
| Feature tests for the touched pages | 11/11 green (3.3) | earlier window |
| `probe-cv.mjs` CDP profile | 3 runs × 2 routes, all layout deltas 0 | this lineage |
| `git check-ignore` on the SITREP artefact | confirmed ignored by `.gitignore:137` | this lineage |
| Working-tree probes in this window | `od`/byte-level CRLF census on 4 files, `git ls-files`, `git grep` at HEAD, `netstat` on `:8181`/`:8184` | this window |

**A full Pest suite was not run in this lineage and no pass is claimed for it.** A known-red set exists and is
recorded elsewhere in the tree; the relevant warning for a resuming agent is that a green focused run proves nothing
about the suite, and that a full run must be classified before it is believed.

---

## 9. Known risks and traps confirmed the hard way

1. **The working tree moves mid-read.** Porcelain went 24 → 34 within this window while files were being measured. Any absolute count quoted without its timestamp is wrong by the time it is read. Quote the delta, not the total.
2. **Redirect characters are blocked.** The `terminal` tool refuses any command containing `>`, including `2>&1`, `2>/dev/null`, `tee`, and PHP's `=>`. Two probes in this window were lost to it. Workaround: write a `.php`/`.mjs`/`.py` file and run the file.
3. **`head`/`cat`/`grep` on some shapes is blocked** in favour of `read_file`/`search_files`. Composed pipelines that mix a blocked reader with a legitimate filter fail as a unit.
4. **Escaped bytes in Python probes lie.** `b'\\r\\n'` counts literal backslash-r, not CRLF. It reported `CRLF=0` on a file that is 100% CRLF. Use `b"\x0d"`/`b"\x0a"` and re-measure before publishing a line-ending claim.
5. **Line endings are not uniform across the tree.** `resources/js/spa.ts` is 87 CRLF / 0 LF in the working tree and its HEAD blob also carries CR, while `KNOWN-ISSUES.md` is 5,417 CRLF / 0 LF. `LineEndingGuardTest` does not cover `.md` at the root or the whole of `resources/js` uniformly, which is why these coexist.
6. **Peer SITREP collisions are live.** Six SITREPs landed in `docs/sitrep/` between 13:10 and 13:22 while this one was being prepared. A new file must carry a distinct name; this one is `sitrep-2026-11-18-mwg-phase3-roadmap-forensics.md`, checked against the directory before writing.
7. **A port that was LISTENING can clear itself.** `:8181` held PID 28080 at the end of the 3.4 harness and is clear now, without this session killing it. Do not assume a server you left running is still there, and do not assume it is gone either: check.

---

## 10. Uncommitted changes at report time, and their ownership

34 porcelain rows. **None of them belong to this session.** Grouped by owner as best the tree can supply:

| Group | Rows | Owner |
| :--- | :--- | :--- |
| `docs/sitrep/*.md` (6) | `sitrep-2026-11-18*.md`, `sitrep-session-handoff-2026-11-16-to-18.md` | peer context windows, written 13:10–13:22 |
| Canonical plan/roadmap edits (5) | `docs/plan/plan-{capability-release,defects,tree-truth,truth-gates}-2026-11-17.md`, `docs/plan/hermes-execution-plan-2026-11-17.md`, `docs/roadmap/IMPLEMENTATION-ROADMAP-2026-11-18.md`, `docs/roadmap/remaining-work-roadmap-2026-11-17.md` | peer |
| ADR set (4) | `docs/adr/0019…`, `0028…`, `0029…`, `docs/adr/README.md` | peer |
| Register and proposals (3) | `KNOWN-ISSUES.md`, `composer.json`, `docs/proposals/remaining-implementation-2026-11-17.md` | peer |
| Source (6) | `Career/Timeline.vue`, `Dashboard.vue`, `Review/Index.vue`, `Veterans/TransferRequests.vue`, `spa.ts`, `tests/browser/review.spec.ts` | peer |
| Deletion (1) | `D docs/research-scratch/INDEX.md` | peer, disposition unknown |
| Untracked (7) | `docs/audits/uiux-comprehensive-walk-2026-11-18/`, `docs/roadmap/INDEX.md`, `tests/browser/audit-followup-2026-11-18.spec.ts`, `tests/browser/navigation-focus.spec.ts`, `tools/gates.sh`, plus the sitreps above | peer |

The two files this session authored are **tracked and clean**, rewritten by peers, both marked SUPERSEDED.

---

## 11. Deviations from the plan

| Planned | Actual | Why |
| :--- | :--- | :--- |
| P1.1 owner decision on A/B/C/D | Never taken; gate dissolved | A peer context window banked the diff in `f8e428d9` first. Presenting four options for a set that is already clean would have been theatre. |
| 3.4 execute the "apply" branch if thresholds met | No branch reached apply | Measured: 0 layout passes, 64 fps, capped lists |
| Roadmap to be the long-lived authoritative pair | Both files SUPERSEDED by peer regenerations and are now historical record | The owner's own separation ruling anticipated this: each session owns its pair, and the canonical choice was deferred |
| SITREP to `.scratch-uma/<session>/` | Written to `docs/sitrep/` as instructed in this turn | `docs/sitrep` was promoted to a standing tracked directory; 8 tracked files precede this one |

---

## 12. Next steps, in execution order

1. **Do not re-open P1.1.** Confirm all 17 MWG paths are clean before assuming anything else; if one turns dirty, it is a new peer edit, not a residue of 3.2/3.3.
2. **Re-derive the KI open set with a named method.** Neither 6 nor "20+" is usable. Count `## KI-` headings, then count `Status: OPEN` rows, and state both numbers with the parse used. `grep -c 'Status: OPEN' KNOWN-ISSUES.md` → 27 at this reading, against 41 headings.
3. **Settle the deletion.** `docs/research-scratch/INDEX.md` is deleted in the working tree while `docs/roadmap/INDEX.md` is new and untracked. Prove the replacement covers the old content before the deletion is committed; do not restore it to tidy a count.
4. **Run the full suite once, and classify before believing it.** No full-suite pass is claimed anywhere in this lineage. The known-red set includes the doc-citation ratchet and pre-existing browser failures; a green focused run is not evidence about the suite.
5. **Pick up the current roadmap, not the 2026-11-16 pair.** `docs/roadmap/IMPLEMENTATION-ROADMAP-2026-11-18.md` (47,132 B, re-baselined at `2546689e`) is the live ordering; the 2026-11-16 pair is the historical record of how each slice's rationale was derived.
6. **Leave the pending-QA register alone unless a device appears.** ProvenanceMark screen-reader double-read, the physical mobile keyboard check, and Safari find-in-page/paint are unclosable on this host. Record once, do not force.

---

## 13. Verified facts versus assumptions

**Verified by command in this window or by a commit/HEAD read-back:**
HEAD and divergence; 0 staged; 6 stashes; 50 commits since `d417a9a5`; all 17 MWG files clean; `+128/−40` in `f8e428d9`; `fieldset` at `EventDecision.vue:271`/`:303`; `<search` at `Runs/Index.vue:119`; `spellcheck: undefined` at `BaseInput.vue:50`; hint attributes at `CardPicker.vue:211-212`, `SearchableSkillPicker.vue:190-191`, `TraineeSelect.vue:591-592`; 0 `content-visibility` matches; `polyfills.js` tracked; three commits' timestamps and numstat; 0 commits by this session; mnemon DB size, insight count and edge count; both mnemon entries readable; 41 KI headings and 27 `Status: OPEN`; ui-lint baseline 209; `LineEndingGuardTest.php` under `tests/Feature/` only; CRLF census on four files; `:8181`/`:8184` clear; both authored files tracked and clean.

**Assumed, not verified:**
That `f8e428d9` banked *this* session's working-tree edits rather than a peer's independent reproduction of the same change (shared commit identity makes this unprovable from the tree). That the `78b54a71` → `178a9079` pair is a revert-then-reapply rather than two independent edits. That the 34 porcelain rows all belong to live peer work rather than abandoned work. That the CV profile numbers still describe today's pages, since they were measured before several peer source commits.

**Explicitly not claimed:** any full-suite pass; any typecheck or build result in this window; completion of any roadmap phase after P1.1; that the SUPERSEDED pair is authoritative for anything.
