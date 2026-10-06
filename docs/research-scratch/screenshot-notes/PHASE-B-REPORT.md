# Phase B: screenshot extraction pass, report (revision 6)

Phase B is closed for Stage 1. The §8 gate clears after the expansion, so Stage 2 ran and produced `docs/research-scratch/phase-c-synthesis.md`. Revision 6 records the corpus update and the trainee classification pass; see item 15. Phase C closes with that item.

Revision 5 was the provenance patch of 2026-10-03. It changed no finding, re-derived nothing and regenerated no CSV. It re-verified the two canonical SHAs at the paths the `_scratch/` reorganization moved them to, corrected every stale path reference forward while keeping the historical path in place, recorded the `clusters.json` misfiling, and added the sequencing rule in item 11.1.

Spec used for Phase C: the scope defined in Stage 2 of this dispatch. No separate owner-held Phase C spec was available to this pass, and the synthesis says so at its head.

The dispatch's Step 4 item names are used for items 1 through 14. Items 7 and 8 are "New screens" and "Gaps" as this dispatch names them; the trainee-name audit that revision 3 carried as its own item now sits under item 4.1, and obstructions under item 3.1.

## 1. Pre-flight

| Check                  | Value                                                                         |  |
| ---------------------- | ----------------------------------------------------------------------------- |  |
| HEAD                   | `3dc8579`, branch `master`                                                    |  |
| origin/master          | 0 behind, 5 ahead                                                             |  |
| `git status --short`   | 32 entries: 21 modified, 3 staged deletions, 1 unstaged deletion, 6 untracked |  |
| DB fingerprint, before | `5bf3ef99ff87f1bd6319cb18e432164ebbe96d2b4d3f7110f1802ecefe51bbbc`            |  |
| DB fingerprint, after  | `5bf3ef99ff87f1bd6319cb18e432164ebbe96d2b4d3f7110f1802ecefe51bbbc`            |  |

The working tree is dirty against tracked files this pass must not touch (`CONSTRAINTS.md`, `KNOWN-ISSUES.md`, `PLAN.md`, `docs/UMAMUSUME_REFERENCE.md`, several ADRs, a migration and a seeder). That is a concurrent peer, not this pass. `research-scratch/` is excluded by `.gitignore:87`, so nothing this pass wrote appears in that count.

One untracked file is directly relevant: `docs/design-research/_scratch/REORGANIZATION_PLAN.md`, with `reorganize-design-scratch.ps1` beside it. Both target the folder that holds `clusters.json` and `signatures.json`. See item 10.1.

Update from revision 5: that reorganization has since run. The two SHAs were re-verified at their new paths and item 14 carries the current locations. Item 10.1 records the outcome and item 13.5 names each destination.

## 2. Frame set derivation

### 2.1 The canonical funnel, unchanged

| Step | Rule                     | Count                                                   |  |
| ---- | ------------------------ | ------------------------------------------------------- |  |
| 1    | Source rows              | 1160 signature entries, 1159 PNGs on disk, 751 clusters |  |
| 2    | `w == 1920 && h == 1080` | 173 frames on disk, held by 44 full-size clusters       |  |
| 3    | Cluster-rep match        | 44                                                      |  |
| 4    | Portrait additions       | 46                                                      |  |
| 5    | `185506` skip            | Not applicable, excluded at step 2                      |  |
| 6    | Final derived            | 46                                                      |  |

The canonical 46 is not re-derived by this dispatch and was not touched. `clusters.json` was opened read-only.

### 2.2 The expansion set

**The rule, as dispatched.** Candidate set produced by Step 1.1's ordered branches, not copied from the dispatch.

**Branch 1, the 233324 cluster: this is what produced every candidate.** `233324` is not a cluster rep. It is a member of cluster index 726 in `clusters.json`, whose `rep` is `2026-07-18 001811.png` and whose `count` is 58. Reading that member list for skill-selection screens that the canonical rule did not select yielded three frames, all at 1920 by 1080, all in the same temporal run as 233324:

| Candidate           | Derivation step                 | Distinct state                                                                                         |  |
| ------------------- | ------------------------------- | ------------------------------------------------------------------------------------------------------ |  |
| `2026-07-17 233332` | Branch 1, member of cluster 726 | Gold gradient selected row on "Swinging Maestro", three badged rows at 81, 162 and 323, no obstruction |  |
| `2026-07-17 233339` | Branch 1, member of cluster 726 | An "Obtained" inverted row beside two rows at the same 144, obstruction                                |  |
| `2026-07-17 233344` | Branch 1, member of cluster 726 | Two of three rows with no hint badge at all, red icon family, saturated Confirm, obstruction           |  |

**Branch 2, the contact sheets, was not exercised.** The cap of three new notes was reached on branch 1, and the dispatch's own branch-1 clause says no sheet scan is needed once a member candidate is found. It also could not have produced a full-desktop member candidate: see item 13.1.

**Branch 3, the stratum preference.** All three candidates are 1920 by 1080, so no portrait or mobile frame was admitted and no comparability limit had to be recorded in field 10.

**Branch 4, dedupe by state.** The three candidates differ from each other and from `233324` (extras) on the dispatched axes: visible skill list, per-row price, badge presence, selection treatment and obtained-row presence. All three sit at "Skill Points 169", so the point total is not what separates them. No two candidates were dropped as same-state.

**Branch 5, the cap.** Target 5 total, cap 3 new. Reached exactly: 2 canonical plus 3 expansion is 5. No further candidate was read.

**Branch 6, re-admission.** `233324` was not re-admitted. Its note stays at `docs/research-scratch/screenshot-notes-extras/` and it is counted in neither the canonical 46 nor the expansion 3. It is cited in the synthesis only where its exclusion matters.

### 2.3 B2 provenance, unchanged

`clusters.json` at `docs/design-research/_scratch/`, SHA `0ce49656c816c87befc2787c7007c00845806241cc6f350bab9e3bd04d7c4d1d`, is the canonical artefact and was read only. The file revision 1 deleted was its own temporal dump, not a copy of this.

Forward correction from the patch dispatch of 2026-10-03: that path is where the file stood when revision 4 read it. It now lives at `docs/design-research/_scratch/analysis/color/clusters.json` and hashes identically. Item 14 carries the current path and item 13.5 records why it is misfiled there.

## 3. Readability

Unchanged from revision 3, plus the expansion reads. All three expansion frames read on the first attempt with no error, so the `scratch-priors.md` §6 stop rule was never approached. `233332` is fully legible. `233339` and `233344` each carry a Snipping Tool notification over the lower right; both notes name it as an obstruction and describe only what is behind it, which is the calendar's Late Aug to Late Oct column and the nav rail below Career Profile.

### 3.1 Obstructions

13 of 49 notes carry the Snipping Tool notification: the 11 canonical (`230755`, `230902`, `234521`, `234918`, `235229`, `001817`, `001823`, `133322`, `134917`, `135751`, `135756`) plus the expansion's `233339` and `233344`. In `002941` and `233057` the overlay sits over the Log panel, whose text is additionally blurred by the client.

## 4. Notes produced

| Set                             | Count  | Path                                             |  |
| ------------------------------- | ------ | ------------------------------------------------ |  |
| Canonical                       | 46     | `docs/research-scratch/screenshot-notes/`        |  |
| Expansion                       | 3      | `docs/research-scratch/screenshot-notes/`        |  |
| **Total**                       | **49** |                                                  |  |
| Reclassified, outside both sets | 3      | `docs/research-scratch/screenshot-notes-extras/` |  |

Expansion notes: `Screenshot 2026-07-17 233332.md`, `233339.md`, `233344.md`. All 11 fields in the canonical order, all `Category: skill-selection`.

### 4.1 Field placement, the B3 rule

The B3 audit result stands as accepted: 0 notes carry a trainee name in field 2, because in every frame the screen's own label is a surface name. Field 4 carries a rendered name as observation.

Against the corrected 49-note set: **23 notes carry a trainee name in field 4, 26 do not.** The three expansion notes render no trainee name; the Learn surface shows the artwork and the Skill Points strip and no name, which matches the two canonical Learn notes that pair with the Log and take the name from there instead.

## 5. Category distribution

Recounted across all 49 notes by reading field 1 from each file:

| Category             | Canonical | Expansion | Total  |  |
| -------------------- | --------- | --------- | ------ |  |
| `training`           | 14        | 0         | 14     |  |
| `race-entry`         | 10        | 0         | 10     |  |
| `career-progression` | 9         | 0         | 9      |  |
| `other`              | 5         | 0         | 5      |  |
| `skill-selection`    | 2         | 3         | **5**  |  |
| `menu`               | 3         | 0         | 3      |  |
| `character-detail`   | 3         | 0         | 3      |  |
| **Total**            | 46        | 3         | **49** |  |

## 6. In-scope count

Four in-scope categories per this dispatch's premise list, recounted across canonical plus expansion: `training` 14, `skill-selection` 5, `race-entry` 10, `career-progression` 9. **38 of 49 notes are in scope.** The 11 out-of-scope notes are `other` 5, `menu` 3 and `character-detail` 3.

This is a scope change from revision 3, which treated all seven categories as gating. Under the scope this dispatch states, `character-detail` and `menu` at three notes each are not gate risks at all, and revision 3's "zero margin" warning is void for them.

## 7. New screens

The expansion adds none. All three candidates are the "Learn" surface, which the manifest names as "Skill acquisition" and which `234453` and `234521` already covered.

The eight screens the note set shows that the manifest's coverage table does not name are unchanged from the synthesis, and are listed there in its section 4: the Predictions modal, the Race Day turn surface, Career Profile as a standalone card, the goal ladder, Edit Team, the RACE FINISHED five-leg summary, the Mood Effect reference modal, and the spark-resolution event presentation.

## 8. Gaps

Unchanged, and carried in the synthesis rather than duplicated here. Four gaps against the manifest's own named representatives: the NPC coaching bubble (five representatives named, no note), Inheritance select (two named, no note), the event choice panel representatives `124128` and `124926` (screen type covered by three other frames, representatives not), and the two Ura Finale frames `012428` and `014154`, which the manifest itself says are the cheap way to widen its scenario row.

The expansion narrowed no gap. It deepened a category rather than reaching a new screen.

## 9. §8 gate outcome

Run against the canonical 46 plus the 3 expansion notes, floor of three, four in-scope categories:

| Category             | Count | Floor  |  |
| -------------------- | ----- | ------ |  |
| `training`           | 14    | clears |  |
| `race-entry`         | 10    | clears |  |
| `career-progression` | 9     | clears |  |
| `skill-selection`    | 5     | clears |  |

**No in-scope category holds fewer than three. The gate CLEARS, and Stage 2 ran.**

Margin note: the nearest in-scope category to the floor is `career-progression` at 9. The gap revision 3 flagged is closed with three notes of headroom, and the target of five `skill-selection` notes was met exactly.

In-scope categories for the §8 gate: `training`, `skill-selection`, `race-entry`, `career-progression`. Out-of-scope: `character-detail`, `menu`, `other`. Out-of-scope categories are catalogued and do not gate.

This item authorizes Stage 2 for this dispatch. It does not authorize anything beyond Phase C synthesis.

## 10. Findings the user should know about that this dispatch did not ask for

1. **A pending reorganization targets the folder holding the canonical artefacts.** `docs/design-research/_scratch/REORGANIZATION_PLAN.md` and `reorganize-design-scratch.ps1` are untracked and both describe moving files in that folder, including `clusters.json` and `signatures.json`. Every SHA in this report is paired with a path, and a reorganization invalidates the paths while leaving the SHAs correct. Anyone running the reorganization should either exclude those two files or restate the paths in item 14 afterwards.
   **Outcome, revision 5.** The reorganization ran on 2026-10-03, after revision 4 shipped. The paths were restated and the hashes re-verified, which is the second option, taken after the fact rather than in the same dispatch as the move. The outcome was worse than the forecast: the script bucketed by filename, so `clusters.json`, a frame-clustering output, landed under `analysis/color/` beside the script that generates it, while `signatures.json` went to `analysis/scenarios/`. Item 13.5 names each destination and item 11.1 is the rule this produced.
2. **The canonical rule's granularity is what caused the gate failure, and the expansion found the missing frames inside one cluster.** `233324`, a Learn screen, sits in the same 58-frame cluster as `001811`, a Training screen. The manifest's method line explains why: dHash with a Hamming threshold of 6 over a 60-frame rolling window. A rolling window chains neighbours, and every one of these frames shares the same HUD chrome, so a cluster can absorb several distinct screens and the "one note per rep" rule then reports one screen type where the run visited three. The `skill-selection` shortfall was never a scarcity of Learn frames. It was four Learn frames collapsed into a Training cluster. Any future pass that reads the 44 as 44 distinct screens will overstate the dedup.
3. **The manifest's corpus-shape row is now double-confirmed and can be marked verified.** Its figures for 1160 frames, 751 distinct, 173 full desktop at 44 distinct, and a largest cluster of 58 at `001811` all reproduce from a second measurement. This is the one place in the package where Phase B adds confidence rather than content.
4. **The expansion changed what the canonical set could claim about the Learn surface.** Before it, both canonical Learn notes paired with the Log, so the set could not show Learn beside the calendar without reaching outside itself to `233324`. The three new notes establish that pairing inside the set. This is the concrete return on the owner's decision to expand rather than waive.
5. **`234453`'s field 9 contains an inference its own rules forbid.** It reads "the red-to-grey cost text presumably tracks affordability". "Presumably" is an inference marker in a note that elsewhere separates observation from reading. Reported, not edited: the fence limits writes to the three expansion notes, the synthesis and this report.
6. **`_readability-sample.csv` now describes only the canonical 46.** The fence for this dispatch names three writable paths and the CSV is not among them, so the expansion notes are counted in items 4 through 6 of this report rather than carried in the CSV. A reader who counts CSV rows will get 46 and should read that as the canonical set, not as the note set.

## 11. Fence

**Written, 5 files:** `docs/research-scratch/screenshot-notes/Screenshot 2026-07-17 233332.md`, `233339.md`, `233344.md`; `research-scratch/phase-c-synthesis.md`; `docs/research-scratch/screenshot-notes/PHASE-B-REPORT.md`.

**Read, not written:** `docs/design-research/_scratch/clusters.json`, `signatures.json`, `cluster.py`, `sheet.py`, `REORGANIZATION_PLAN.md` (read-only folder); `docs/game-screenshots/` frames `233332`, `233339`, `233344`; `docs/research-scratch/DESIGN-CORPUS.md` (the `## SCREENSHOT-MANIFEST.md` section); all 49 notes; `database/database.sqlite` hashed only.

**Not touched:** any canonical note, any extras note, both CSVs, `_derive-csvs.py`, `KNOWN-ISSUES.md`, `scratch-priors.md`, `docs/research-scratch/screenshots-inventory.csv`, `config/uma.php`, `phpunit.xml`, `DatabaseSeeder.php`, `resources/css/app.css`, `PRD.md`, `CONSTRAINTS.md`, root `DESIGN.md`, either `DESIGN-CORPUS.md`, `docs/UMAMUSUME_REFERENCE.md`, any ADR. No `git push`, no `migrate`, no `db:wipe`, no `paratest`, no `rm` under `database/`.

**DB fingerprint before and after:** `5bf3ef99ff87f1bd6319cb18e432164ebbe96d2b4d3f7110f1802ecefe51bbbc` at both ends. Only the main file is fingerprinted at both ends, as specified. The WAL and SHM sidecars were last hashed at the close of revision 2 (`-wal` `33468d5a359060dae7e45f960f89eda20aebab3221fd52a24d6175ac4c5cf55f`, `-shm` `5005c69cf827d09b640d3a942b3fdfb95bd8cbc1b0869f6c2a24740b85cc644f`) and were not re-hashed here, so this pass cannot show whether they moved. The shared worktree makes that a live uncertainty rather than a formality.

The paths in the "Read, not written" paragraph above are the paths as they stood when that dispatch read them, and are kept verbatim as a record of what was opened. The `_scratch/` reorganization moved four of them afterwards; item 13.5 and item 14 carry the current locations.

### 11.1 Sequencing rule

Any future structural change to `docs/design-research/_scratch/` requires a provenance freeze first: existing report and synthesis path references are updated in the same dispatch as the move, not in a follow-up. A relocation after the fact leaves stale paths paired with correct hashes, which is worse than either being wrong alone.

The reason this rule exists in this report rather than in a style guide is that the freeze was missed once. Revision 4 shipped with four artefact paths that a script then invalidated, and the correction needed a separate dispatch, a SHA re-verification, and a decision about whether to leave a known misfiling in place. A move that carries its own path update costs one line in the same commit.

### 11.2 Corpus edit path rule

Any corpus edit that cites a note path must cite a path that is tracked.

## 12. Skills

| Skill                       | Result                                                                                                                                                                                                                                                                                                                                                                                                                                     |  |
| --------------------------- | ------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------ |  |
| `ponytail`                  | Resolved without a Skill invocation. It is loaded as an always-on project rule from `.qoder/rules/ponytail.md` and was in context for the whole pass. No refusal.                                                                                                                                                                                                                                                                          |  |
| `gstack:careful`            | Loaded. It activated a session-scoped destructive-command guardrail over `rm -rf`, `DROP TABLE`, force-push, `git reset --hard`, `git checkout .`, `kubectl delete` and `docker rm -f`. No refusal. It then misfired on read-only commands and could not be unloaded, see item 12.1.                                                                                                                                                       |  |
| `source-driven-development` | Loaded. No refusal. Its process targets framework-specific code decisions, and this pass wrote no code, so steps 1 through 3 had nothing to apply to. Its retrieval-safety clause and its "UNVERIFIED" discipline do apply and are reflected in the synthesis, which names what no note establishes rather than filling the gap.                                                                                                           |  |
| `antislop-copywriting`      | Loaded. No refusal. Applied to the synthesis and this report: no em dashes, no invented fact or figure, no buzzword, no generic conclusion, every claim traceable to a cited note. Its inline-header-list pattern was checked and the numbered invariant labels in the synthesis kept, on the skill's own carve-out for structural labels. Its no-fabrication rule (R-17, R-36, R-38) is what blocked the mood-pill hex edit; see item 15. |  |

### 12.1 `gstack:careful` outlived its dispatch and produced false positives

The guardrail was authorized for the pass that loaded it. It stayed armed across the two follow-up dispatches and began blocking ordinary read-only commands, misreading them as an unknown `rtk` executable. Four commands were refused in the corpus pass: `git diff --numstat`, `head -3`, `tail -2`, and one combined `sha256sum` plus `grep` chain. None of them writes anything. Every refusal was a false positive and each was worked around with the Read or Grep tool rather than by weakening the check.

**It could not be unloaded.** The skill's own text says deactivation means ending the conversation, because the hook is session-scoped. There is no in-session unload path, and `~/.qoder/settings.json` holds no `hooks` key and no reference to `careful` or `gstack`, so there is nothing to edit either. The registration is inside the plugin and not operator-reachable mid-session.

**Why this matters beyond annoyance.** A guardrail that blocks `head` teaches the next agent to route around guardrails, and it makes the refusals look like property of the workspace rather than of one expired skill. Whoever opens the next dispatch should start a fresh session instead of carrying this one forward. Recorded here because the report is the durable surface; the guardrail itself cannot be cleared from it.

## 13. Premises wrong in this dispatch

1. **The contact-sheet premise is wrong on disk.** Step 1.1(2) states the montages "render every cluster as one cell: rep on the left, members to the right". Both generators render one thumbnail per cell and the image they open is the rep: `cluster.py:101` and `sheet.py:34` both read `os.path.join(SRC, r["rep"])`. The `sheet_Nof19.png` set is 751 reps at 40 per sheet, labelled `#{k} x{count} {repid}`. No sheet in that folder shows a member frame, so branch 2 could never have surfaced a member candidate and could only have re-found reps, all 44 of which are already canonical. The expansion rests entirely on branch 1, which is why it worked.
2. **233324's status was misdescribed in revision 3 and is corrected here.** Revision 3's item 2.2 called it "Head of a temporal cluster; not a `clusters.json` rep", which is right about the rep and imprecise about where it lives. It is a member of cluster 726, rep `001811`, count 58. The correction matters because it is the reason branch 1 had anything to find.
3. **The scope split changes a prior gate reading.** This dispatch puts `character-detail`, `menu` and `other` out of scope. Revision 3's item 9 warned that `character-detail` and `menu` at three each left "zero margin" and would break the gate a second time. Under this scope they do not gate, and that warning is void. Revision 3 is left as written for the record.
4. **The dispatch's premise that no Phase C spec is on record is correct.** This pass found none. Stage 2's own definition was used and the synthesis names it.
5. **The `_scratch/` reorganization ran on 2026-10-03 after this report was written.** It moved `clusters.json` to `analysis/color/clusters.json` and `signatures.json` to `analysis/scenarios/signatures.json`. Item 14's paths are updated to match; the SHAs are unchanged and were re-verified at the new paths. `clusters.json` sits under `analysis/color/` because the reorg script name-matched it as a color-clustering output; it is a frame-clustering file and this is a misfiling, left in place to avoid a second relocation. `cluster.py`, the script that generates it, landed in the same `analysis/color/` bucket for the same reason, while `sheet.py` went to `scripts/probe/`, so the line citations in item 13.1 now resolve at `analysis/color/cluster.py:101` and `scripts/probe/sheet.py:34`. Item 10.1 predicted this hazard, but predicted a path churn rather than the systematic mis-bucketing it produced.

### 13.6 Synthesis quality note

Not a dispatch premise failure. Three claims in `phase-c-synthesis.md` section 5 were wrong about their own sources, and each one was only caught because the corpus-update dispatch required the value to be confirmed in the cited note before it was applied. Recorded here so a future reader knows the synthesis's section 5 needs checking against its notes rather than trusting as a summary of them.

1. **Item 1 asserted that `001957` settles the provisional mood tokens.** It does not, on two independent grounds. The note carries colour names and zero hex tokens, so there is no measured value to lift. And the corpus had already excluded that frame as a colour source before this pass read it: the comment block above the palette says a probe on the Mood Effect panel is unusable because it is a legend panel whose row bands average the pill against the panel field, and it names the required source as the HUD. The synthesis claimed to close an open item that the corpus had specified a method for closing, without checking whether its source met that method. The qualitative claim survives and is kept; the hex claim did not and was not applied.
2. **Item 9 stated the denominator range as 1318 to 1358.** Its own five cited notes carry 1304 as the minimum. The range understated the spread by 14 at the low end. Corrected in the synthesis and applied to the corpus only after the owner re-authorized it.
3. **Item 11 scoped the two-trains hazard to the `2026-07-17` and `2026-07-18` sessions and said nothing about `2026-07-14`.** The first corpus application inherited that silence and named only `025344`, which left `194819` and `202142`, both `2026-07-14`, both stat-bearing and both unattributed, inside the scope of a note whose whole purpose was to fence off a run. The classification pass is what exposed it: the hazard cannot be stated by naming the one frame you can identify while leaving two you cannot identify in place. Widened by corpus Edit E.

The pattern across all three is the same: the synthesis summarized its notes and then cited its own summary. A section 5 item is a claim about a note, and the note is the source.

## 14. Provenance

| Artefact                   | Path                                                                                                                                                                                                                                                     | SHA-256                                                            | Status                                                                                                                                                                                                                                                                                                     |  |
| -------------------------- | -------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------- | ------------------------------------------------------------------ | ---------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------- |  |
| Frame signatures           | `docs/design-research/_scratch/analysis/scenarios/signatures.json` (moved from `docs/design-research/_scratch/signatures.json` at 2026-10-03 by `reorganize-design-scratch.ps1`), SHA `0111030b32c012fb9e5f4e210ba7af9e589f8ae3efc1e49d384ef30b5a3135bb` | `0111030b32c012fb9e5f4e210ba7af9e589f8ae3efc1e49d384ef30b5a3135bb` | Verified at the new path in Step 0 of the patch dispatch, unchanged from the value revision 2 recorded at the old path. Read only                                                                                                                                                                          |  |
| Screen clusters, canonical | `docs/design-research/_scratch/analysis/color/clusters.json` (moved from `docs/design-research/_scratch/clusters.json` at 2026-10-03 by `reorganize-design-scratch.ps1`), SHA `0ce49656c816c87befc2787c7007c00845806241cc6f350bab9e3bd04d7c4d1d`         | `0ce49656c816c87befc2787c7007c00845806241cc6f350bab9e3bd04d7c4d1d` | Verified at the new path in Step 0, unchanged. Read only. Source of the branch-1 member list. **Misfiled:** it is a frame-clustering output and sits under `analysis/color/` because the reorg script matched it on the word "clusters". Left in place; see item 13.5 and the sequencing rule in item 11.1 |  |
| Screenshot inventory       | `docs/research-scratch/screenshots-inventory.csv`                                                                                                                                                                                                        | `aef857cea24cf4cdd3cbc33726b2b8de2053d673cd2f9e7daffccdcb0428810b` | Read only. `hash_group` and `category` both degenerate                                                                                                                                                                                                                                                     |  |
| Cluster table              | `docs/research-scratch/screenshot-notes/_screen-clusters.csv`                                                                                                                                                                                            | regenerated in revision 3                                          | 751 rows, `full_size` yes on 44, `source_sha` on every row. Not touched by this dispatch                                                                                                                                                                                                                   |  |
| Readability sample         | `docs/research-scratch/screenshot-notes/_readability-sample.csv`                                                                                                                                                                                         | regenerated in revision 3                                          | 46 canonical rows. Does not carry the expansion, see item 10.6                                                                                                                                                                                                                                             |  |
| Derivation script          | `docs/research-scratch/screenshot-notes/_derive-csvs.py`                                                                                                                                                                                                 | from revision 3                                                    | Reproduction path for the two CSVs above                                                                                                                                                                                                                                                                   |  |
| Phase C synthesis          | `research-scratch/phase-c-synthesis.md`                                                                                                                                                                                                                  | this revision                                                      | Handoff. Cites note ids throughout                                                                                                                                                                                                                                                                         |  |

`research-scratch/` is excluded by `.gitignore:87`, so everything this pass wrote is untracked. `docs/design-research/_scratch/` is tracked, and no revision of this pass wrote into it.

## 15. Corpus update applied, Phase C closeout

Applied 2026-10-03 on the owner's rulings 1(a), 2(a), 3(a) and 4(a), with 4(c) run as the classification pass.

**Corpus file edited:** `docs/research-scratch/DESIGN-CORPUS.md`, section `## SCREENSHOT-MANIFEST.md` only. SHA before `d9e5c681283f6e345a3eeb3169a4630b25a6e598b9ab7d17b2638b910f489c3b`. SHA after the first three edits `79f380119665b4cded92951fc2fc0f2d0319a48e6276efd858da2abb4115556b`. SHA after the owner's two follow-up edits `fb9bb295e500438262d4d8526d7ef4451ad687e81857112ebc512deb011a0136`.

**Diff-level audit trail:** `docs/research-scratch/corpus-edit-summary.md`. That file is the record; this item is the index.

**Five edits applied to the corpus:**

| Edit | Content                                                                                                                                            | Authority                                                     |  |
| ---- | -------------------------------------------------------------------------------------------------------------------------------------------------- | ------------------------------------------------------------- |  |
| A    | `### Preamble: what the 44 representatives are` plus `### Standing note: the corpus spans two trains`, inserted before `### How this was produced` | Ruling 1(a), synthesis item 11                                |  |
| B    | `**Verified against a second measurement, 2026-10-03.**` note beneath the `### Corpus shape` table                                                 | Ruling 4(a), synthesis item 12                                |  |
| C    | `### Additions from the Phase B note set`, eight entries appended to the section                                                                   | Ruling 4(a), synthesis items 2 to 8 and 10                    |  |
| E    | The standing note widened from one frame to the whole `2026-07-14` session                                                                         | Owner follow-up, item 1                                       |  |
| F    | Entry 9 in the additions subsection, the stat denominators                                                                                         | Owner follow-up, item 2, re-authorization of synthesis item 9 |  |

No row of the `### Screen-type coverage` table was edited or renumbered, and no scenario-coverage row was touched. After all five edits the section holds its original 8 subsections plus the 2 new ones, and the coverage table is byte-identical.

**One item not applied.**

**Ruling 2(a), the mood-pill hexes. Blocked, not skipped.** The `001957` note contains zero hex tokens; it records colour names only, so there is no measured value to quote and supplying one would be fabrication. Independently, the corpus already rules this frame out as a colour source: the comment block above the palette says the legend panel cannot be probed because sampling its row bands averages the pill against the panel field, and it names the open item as the three lower pill colours captured from the **HUD**. `001957` is that legend panel. Changing the hexes would also have falsified the `on-mood` contrast rows at 6.25 and 6.23 and their AA verdicts. The hue-family reading, BAD blue and AWFUL purple, stands as a finding here and in the synthesis and is deliberately not in the corpus.

The premise that this note settles the provisional tokens came from the synthesis, section 5 item 1, not from the dispatch. That is a synthesis quality error and it is recorded at item 13.6 rather than as a dispatch premise failure. What remains is an owner decision and a new pass, not a pending edit: obtain a HUD capture of the mood pill, which is the only source the corpus accepts, or leave the three provisional tokens and keep D-259's arrow rule justified on the hue-family reading alone.

**Synthesis item 9 was withheld and then applied.** It first read 1318 to 1358; the five cited notes carry 1304 as the minimum. Corrected in the synthesis, re-authorized by the owner, and applied as Edit F with the range attributed to the specific notes holding its endpoints. Its five source frames are all from the `2026-07-17` and `2026-07-18` sessions, so the read already satisfies the standing note's exclusion rather than merely being qualified by it, and the entry says so.

**Trainee classification pass, Ruling 4(c):** `docs/research-scratch/trainee-classification.csv`, 52 rows, one per note across both folders. Columns are the dispatch's five plus `stat_bearing`, which Step 3.4 requires.

| Measure                                  | Count |  |
| ---------------------------------------- | ----- |  |
| Attributed to Rice Shower                | 23    |  |
| Attributed to Curren Chan                | 1     |  |
| Attributed to another trainee            | 0     |  |
| `unattributed`                           | 28    |  |
| `unattributed` and stat-bearing          | 16    |  |
| Rows resolved by field 2 as screen title | 0     |  |
| Rows marked `multi`                      | 0     |  |

The single Curren Chan row is `2026-07-14 025344`, which is the hazard's origin. The 28 unattributed rows are unattributed **as a property of the note's wording, not of the frame**: several of these frames render the trainee card and its name, and the note describes that card without quoting it. `2026-07-18 154126` shows the opposite case, an extras note that does quote "[Rosy Dreams] Rice Shower" and so attributes. Per the dispatch's instruction the note is the source and nothing was inferred, so no frame was re-read to promote an unattributed row.

**What the pass changed in the corpus.** The note set holds exactly three frames from `2026-07-14`: `025344`, which attributes to Curren Chan, and the two portrait additions `194819` and `202142`, both stat-bearing and both unattributed. The standing note as first applied fenced off only `025344`, which left two frames from the same session inside the scope of a hazard written to exclude that session. That is what corpus Edit E fixes. The classification pass produced no other corpus change; it is the evidence, and the widening is the only claim it supports.

Of the 16 stat-bearing unattributed rows, 9 carry a run-specific stat band with denominators (`194819`, `202142`, `221504`, `230755`, `230902`, `003303`, `134917`, `135745`, `135751`) and 7 carry only percentages or prices (`233332`, `233339`, `233344`, `001957`, `135756`, `135801`, `233324` (extras)). The distinction matters for the hazard: the 9 are numbers that belong to a run and cannot be tied to one from the note, while the 7 are mostly client constants or skill prices that the hazard cannot reach at all. `135756` and `135801` are the exception in the second group, since their failure percentages are run-specific even though they carry no denominators.

**No stat correction was applied.** The pass is classification only and the corpus values from Edit C stand with the hazard attached inline.

**Phase C output set:** `docs/research-scratch/DESIGN-CORPUS.md` (edited), `docs/research-scratch/corpus-edit-summary.md`, `docs/research-scratch/trainee-classification.csv`, `research-scratch/phase-c-synthesis.md` (section 6 and the closeout line), and this report.
