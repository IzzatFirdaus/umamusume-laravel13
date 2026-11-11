# `docs/audits/` — audit reports and their evidence

An index, not a specification. It exists because this directory grew three different conventions
for where screenshots live, and nothing on disk said which one was current. Every line below is
measured from the tree, not remembered; the command that re-derives each figure is given beside it.

**Two shapes, and both are legitimate.** What was *not* legitimate was leaving the choice unrecorded.

**One cross-walk index sits above all of them:** `RECONCILIATION-REGISTER.md` owns cross-walk issue
identity, walk-8 dispositions and recurrence prevention. It is not a third shape and not a defect
register — `KNOWN-ISSUES.md` keeps permanent `KI-nn` identity. Read it before starting a new walk so
a familiar symptom is recognised instead of re-investigated.

| Shape | Report | Evidence | Used by |
| --- | --- | --- | --- |
| **Split** (the norm) | loose at `docs/audits/<name>.md` | `docs/audits/<AUDIT-ID>/screens/` | every Rice Shower walk that captured frames, and the scenario-detail report |
| **Bundle** | `docs/audits/<set>/NN-topic.md` | `docs/audits/<set>/screenshots/` | the Trainer Desk v3.x series, where one audit is a nine-part report rather than one file |

**Rules that follow from the tree, in the order a new pass should apply them.**

1. **The report is a loose `.md`** unless the audit is genuinely multi-part. 19 reports sit at
   `docs/audits/*.md`; only the v3.x series is bundled, and it is bundled because it is nine
   documents with a shared executive summary.
2. **Evidence goes in a subdirectory, never beside the report.** `screens/` for a split audit,
   `screenshots/` for a bundled set. Nothing is written to the top level of `docs/audits/` except
   reports.
3. **The evidence directory is named for the audit ID**, not the report filename:
   `RS-UC-2026-13/screens/` holds walk 8's 43 PNGs. Three reports also declare an ID they do not
   own (a reconciliation naming the walk it reconciles); only the walk itself gets a directory.
4. **A report that captured nothing gets no directory.** Do not create an empty `screens/` as a
   placeholder — walks 3 and 4 did, the directories were never populated, and both reports had to
   record the absence in prose. An absent directory is honest; an empty one is noise.

## Reports

`lines` and `date` are the file's own length and mtime; `id` is the audit ID the report *declares*
(a blank cell means the report declares none); `evid` is the evidence directory it actually has on
disk. Re-derive with `wc -l docs/audits/*.md` and `ls docs/audits/*/screens/ docs/audits/*/screenshots/`.

| Date | Report | Lines | ID | Evidence |
| --- | --- | --- | --- | --- |
| 2026-10-10 | `rice-shower-unity-cup-ux-walk.md` — walk 1, the baseline | 957 | — | none (pre-convention) |
| 2026-10-10 | `rice-shower-unity-cup-ux-walk-2.md` — walk 2 | 941 | — | none (pre-convention) |
| 2026-10-10 | `rice-shower-unity-cup-ux-walk-resolutions.md` — what happened to walk 1's findings | 495 | — | none (a register, not a walk) |
| 2026-10-12 | `rice-shower-unity-cup-ux-walk-3.md` — walk 3 | 989 | — | none (declared `screens/`, never populated) |
| 2026-10-19 | `rice-shower-unity-cup-round2-reconciliation.md` — round 2 register | 1526 | — | none |
| 2026-10-20 | `rice-shower-unity-cup-round3-reconciliation.md` — round 3 register | 405 | — | none |
| 2026-10-24 | `rice-shower-unity-cup-ux-walk-4.md` — walk 4 | 795 | — | none (declared `screens/`, never populated) |
| 2026-10-25 | `rice-shower-unity-cup-round4-reconciliation.md` — round 4 register | 446 | — | none |
| 2026-11-03 | `rice-shower-unity-cup-round5-reconciliation.md` — round 5 register | 583 | — | none |
| 2026-11-03 | `rice-shower-unity-cup-ux-walk-5.md` — walk 5 | 756 | — | none |
| 2026-11-06 | `rice-shower-unity-cup-ux-walk-6.md` — walk 6, wizard door | 1691 | `RS-UC-2026-10` | `RS-UC-2026-10/screens/` (27 PNG) |
| 2026-11-07 | `rice-shower-unity-cup-round6-reconciliation.md` — round 6 register | 1038 | `RS-UC-2026-10` | names walk 6's directory |
| 2026-11-07 | `rice-shower-unity-cup-round6-slice-audits.md` — round 6 slice audits | 2050 | `RS-UC-2026-10` | names no directory |
| 2026-11-07 | `rice-shower-unity-cup-ux-walk-7-snapshot-door.md` — walk 7, snapshot door | 935 | `RS-UC-2026-11` | `RS-UC-2026-11-snapshot-door/screens/` (13 PNG) |
| 2026-11-07 | `rice-shower-unity-cup-ux-walk-7.md` — walk 7, wizard door (canonical) | 1461 | `RS-UC-2026-12` | `RS-UC-2026-12/screens/` (34 PNG) |
| 2026-11-08 | `rice-shower-unity-cup-round7-owner-calls.md` — round 7 owner rulings | 160 | — | none |
| 2026-11-10 | `rice-shower-unity-cup-round7-reconciliation.md` — round 7 register | 1042 | — | names walks 7's two directories |
| 2026-11-11 | `RS-UC-SCENARIO-DETAIL-2026-11-09.md` — scenario-detail page report | 163 | — | `RS-UC-SCENARIO-DETAIL-2026-11-09/screens/` (4 PNG) |
| 2026-11-11 | `rice-shower-unity-cup-ux-walk-8.md` — walk 8, wizard door | 1358 | `RS-UC-2026-13` | `RS-UC-2026-13/screens/` (43 PNG) |
| 2026-11-11 | `RECONCILIATION-REGISTER.md` — cross-walk issue index, walk-8 dispositions, prevention | 332 | — | none (an index, not a walk) |
| 2026-11-11 | `walk-8-follow-up-plan.md` — PA specs, new-finding triage, scenario coverage | 416 | — | none (a plan, not a walk) |
| 2026-11-11 | `audit-methodology-and-remediation-specs.md` — capability rule, F-03/F-08 specs, 3 scenario-walk plans | 499 | — | none (specs, not a walk) |

**19 reports, 4 with evidence, 15 without, 121 PNGs across the five split-audit directories.**
Re-derive: `ls docs/audits/*.md | wc -l`, `find docs/audits -path '*/screens/*.png' | wc -l`.

## Bundled sets

| Directory | Contents | State |
| --- | --- | --- |
| `trainer-desk-v3-ux/` | nine parts `00 executive summary` … `08 screenshot index`, plus `screenshots/` (59 PNG) | **in flight.** Only `04-ux-findings.md` is tracked; the rest is uncommitted peer work |
| `trainer-desk-v3-ux-retest/` | `screenshots/` only, 40 PNG, no report | **in flight.** A peer session was writing to it during the last pass (8 files in 90 minutes). Its report does not exist yet |

Both are left untouched by the 2026-11-11 organisation pass: a directory under active edit is not
a directory to rename.

## Known inconsistencies, recorded rather than silently repaired

Each of these is real, dated, and in a file whose text is append-only history.

| # | What | Where | Why it is left |
| --- | --- | --- | --- |
| 1 | A `screens/` path cited for a directory that never existed | `rice-shower-unity-cup-ux-walk-3.md` (2 citations), `rice-shower-unity-cup-ux-walk-4.md` (1) | both reports already state in prose that the capture failed and no file exists. Deleting the citation would erase the evidence that it was attempted; the correction belongs in a dated erratum, not an edit |
| 2 | `58 screenshots` claimed, 59 on disk | `trainer-desk-v3-ux/08-screenshot-index.md:3` | the count was taken before one file was added. The directory's own index is uncommitted peer text; correcting a number someone else is still writing is out of scope |
| 3 | Two spellings for the evidence directory, `screens/` and `screenshots/` | split audits vs bundled set | both are in use and both are now stated above, so the pair is a documented convention rather than an accident |
| 4 | Three reports declare `RS-UC-2026-10` | walks 6 and the two round-6 registers | the registers cite the walk they reconcile. Per rule 3 they get no directory of their own, and the shared ID is correct for them |
| 5 | The declared ID and the directory name differ by a suffix | walk 7's snapshot door declares `RS-UC-2026-11`; its directory is `RS-UC-2026-11-snapshot-door/` | the suffix is what distinguishes two walks that share one number, so it is doing real work. Renaming the directory to the bare ID would collide with the wizard-door walk; renaming the declared ID would break three citations in the round-7 register |

## What a new audit should do

1. Pick the shape: one report → **split**; nine parts → **bundle**.
2. Name the evidence directory for the **audit ID**, and put every PNG inside it.
3. Write the report loose at `docs/audits/<name>.md` (split) and cite each screenshot by its full
   path.
4. If the capture fails, write that in the report and create **no** directory.
5. Add the new row to the table above. Nothing else in the tree needs to know.

*Measured 2026-11-11 against `master` `6c10ff62`. This file is a map; when it and the tree
disagree, the tree wins and the map is stale.*
