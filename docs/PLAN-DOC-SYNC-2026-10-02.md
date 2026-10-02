# Documentation–Register Synchronization Execution Plan

> **For agentic workers:** REQUIRED SUB-SKILL: use `superpowers:executing-plans` (or `superpowers:subagent-driven-development`) to work this plan task-by-task. Steps use checkbox (`- [ ]`) syntax for tracking.

**Goal:** Bring `KNOWN-ISSUES.md` and the markdown corpus (root, `docs/`, `docs/research-scratch/`) into mutual agreement, and add one machine check so the drift cannot silently return.

**Architecture:** Three moves in order. First, establish truth: reconcile the register against the tree and recover the governing inventory the corpus was built without. Second, repair pointers: repoint the citations that follow a reader nowhere, using the consolidation's own absorbed-source map. Third, ratchet: pin the repaired dead-link count in a Pest parity test so any new rot fails a gate instead of accumulating.

**Tech Stack:** Markdown, Python (`tools/doc_census.py`), Pest 4, Git Bash on Windows, Composer scripts.

**Spec:** This document is its own spec; the evidence it argues from is `KNOWN-ISSUES.md`, `docs/research-scratch/INDEX.md`, `docs/research-scratch/CONSOLIDATION-LOG.md`, and the census output baseline recorded below.

**Measured baseline (2026-10-02, HEAD `e18a032`, `python tools/doc_census.py`):** 72 tracked markdown files; **512 dead markdown links (505 GONE, 7 UNTRACKED)**; `docs/research-scratch/` holds 16 masters totalling 28,402 lines; `KNOWN-ISSUES.md` holds **54** `## KI-` entries.

---

## Global Constraints

- `CONSTRAINTS.md` is the bar. No task may weaken a threshold to make a check pass.
- Never overwrite a dated historical claim. Append a dated erratum naming the sha (`79d5f6f`'s treatment of `ARCHITECTURE.md:158` is the model: the original line stays verbatim, `--` lines appended).
- Living documents may be edited in place: `PLAN.md`, `README.md`, `AGENTS.md`, `docs/research-scratch/INDEX.md`, `docs/research-scratch/PLANS-AND-BRIEFS.md`, `docs/research-scratch/PROCESS-PLANS.md`, and this plan.
- Dated records may not: `KNOWN-ISSUES.md` entries, `docs/research-scratch/SLICE-RECORDS.md` sections, `docs/adr/*` (errata appended only), `docs/UMAMUSUME_REFERENCE.md`.
- Do not renumber sections in `docs/research-scratch/*`. Other files cite `§` anchors into them.
- Lore gate is blocking: no equine vocabulary for the characters, no em dashes in shipped UI copy, absence renders as `N/A` plus a `title` or as named copy, never as an em dash or "Unknown".
- `python` only. `python3` is not on PATH on this host (`which python3` returns nothing; `python` is 3.12).
- Do not use `make` (KI-4). Run `composer` scripts or the underlying command.
- Shared worktree: stage named paths only; `git status --short` before every commit; never commit while a gate is running.
- Register discipline: a KI closes only when its fix is on `origin/master` and the closure names the sha.

---

## Part A — Comparative analysis: where the documentation fails the register

| # | Gap | Evidence | Severity |
|---|---|---|---|
| A-1 | The register's **own status block is stale and self-declares as authoritative**. `KNOWN-ISSUES.md:10` asserts "35 filed, 24 closed, 11 open" (2026-09-30). The file now holds 54 entries; `:28-29` states "The three numbers in this block are the authoritative counts; a heading grep is indicative only." | 54 headings vs 35 claimed; KI-46..KI-54 filed 2026-10-01, KI-37 and KI-47 closed 2026-10-01, all after the block | **Blocker** |
| A-2 | **Seven OPEN entries describe a state the tree no longer has.** KI-32 (fix shipped `ed71741`: `resources/css/app.css:219` light, `:229` dark), KI-48 (fix shipped `79d5f6f`, errata in `ARCHITECTURE-ESSENTIALS.md:36` and `ARCHITECTURE.md:158`), KI-49 and KI-51 (all nine `database/seeders/data/*.json` bodies and the seeder code tracked at `8b17703` / `30b3a08`), KI-53 and KI-54 (fixture tracked, no `.held-aside`, tree clean, the 11-red state no longer reproduces), KI-45 (headline "no offline population path" superseded by `8b17703`: `'seed_file' => 'race_instances.json'` at `config/uma.php:138-145`). | 20 entries carry OPEN in a heading without CLOSED/RESOLVED; 7 of them are wrong | **Blocker** |
| A-3 | **A doc already asserts what the register denies.** `docs/research-scratch/RACE-AND-SLICE-RESEARCH.md:843` calls KI-48 "the one finding this slice was told to fix — done in §3" while the register keeps KI-48 OPEN. Two sources of truth disagree in public. | `RACE-AND-SLICE-RESEARCH.md:843` vs `KNOWN-ISSUES.md:2362` | **Major** |
| A-4 | **The documentation precedence chain points at files that do not exist.** `AGENTS.md:178` ranks `docs/GATE-REGISTRY.md` second of five authorities; `AGENTS.md:5` names `docs/PRE-MORTEM.md` as the risk record; `AGENTS.md:94` says "see GATE-REGISTRY C-5"; `README.md:18` maps `docs/PRE-MORTEM.md`. Neither file is on disk. Both are recoverable at `22e5135^` (13 and 6 commits touch them). | `git show 22e5135^:docs/GATE-REGISTRY.md` succeeds | **Blocker** |
| A-5 | **512 dead markdown links**, concentrated on sources the consolidation absorbed without repointing the readers: `docs/design-research/CONSTRAINTS.md` 56, `docs/design-research/DESIGN.md` 42, `docs/GATE-REGISTRY.md` 22, `RAW-FINDINGS.md` 20, `docs/PRE-MORTEM.md` 19, `SKILLS-GAPS.md` 19, `docs/SOURCE-OF-TRUTH.md` 17, `docs/design-research/SKILLS-GAPS.md` 11. 38 files carry citations into `design-research/`, `deprecated/`, `requests/`, `data/`, `flows/`, `frontend-review/`. Densest citing files: `CATALOG-ROSTER-WORKSTREAM.md` 57, `SKILLS-MECHANICS.md` 44, `SLICE-RECORDS.md` 29, `KNOWN-ISSUES.md` 26. | `python tools/doc_census.py` | **Major** |
| A-6 | **The repoint targets are already known, not guessed.** `docs/research-scratch/INDEX.md` records which master absorbed which source, and the section headings exist: `DESIGN-CORPUS.md:18` `## DESIGN.md`, `:1665` `## CONSTRAINTS.md`; `GOVERNANCE.md:16` `## SOURCE-OF-TRUTH.md`; `SKILLS-MECHANICS.md:2410` `## skills-section-phase-b2-2026-10-01.md`. Only Round 3 repointed its citations (`SCENARIO-PUBLISHER-REFERENCES.md`: 26 inbound); Round 1 and 2 masters sit at 1 inbound each. | `INDEX.md` tables; census inbound counts | **Major** |
| A-7 | **The dead-link check exists but gates nothing.** `tools/doc_census.py:54` computes `dead_links()` and prints only the first 20 (`:112`), and no composer script or test invokes it. `composer.json:56` registers `lore` the same way a `docs` script could be registered. Citation rot is therefore invisible after it lands. | `grep -n census composer.json` returns nothing | **Major** (prevention) |
| A-8 | **Two KI ids are cited as entries and are not.** `KI-34` (8 citations) is a deliberate reservation with no entry (`KNOWN-ISSUES.md:21-24`); `KI-16` (6 citations) is a renumbering hole the register itself admits: "a genuine renumbering hole, KI-30's pre-merge number" (`:23`). 51 distinct ids are cited outside the register against 54 entries. | set difference of cited ids vs heading ids | **Major** |
| A-9 | **Five entries are never referenced outside the register:** KI-31, KI-46, KI-52, KI-53, KI-54. KI-52 (OPEN) is the two-`DESIGN.md`-basename ambiguity, and the register's own headings exhibit it: KI-29 (`:1390`) and KI-37 (`:1781`) both cite "DESIGN.md §6.14", which resolves nowhere — root `DESIGN.md` has no §6.14 and the 44px rule is `DESIGN-CORPUS.md:904`. KI-46 (`turns.0.speed` leaked to the Trainer) has no reflection in the copy rules; its only trace is a test comment at `tests/Feature/SupportCardTest.php:19`. | `comm -23` of entry ids vs cited ids | **Major** |
| A-10 | **The corpus was consolidated on a false premise.** `CONSOLIDATION-LOG.md:4-6` states the governing file `docs/research-scratch/DOCUMENTATION-INVENTORY-2026-09-30.md` "is absent from disk and from all git history (verified: `git log --all -- '*DOCUMENTATION-INVENTORY*'` returns nothing)", so sections 6/7/10/11 "could not be read" and a filesystem survey was substituted. The file is **on disk** at `research-scratch/DOCUMENTATION-INVENTORY-2026-09-30.md` (101.5 KB, 644 lines), with §6 "Cross-reference map" at `:353` — in the repo-root `research-scratch/` folder, which `.gitignore:87` ignores (`/research-scratch/`). The verification command cannot see an ignored file, so a history-only probe was reported as disk absence. | `ls research-scratch/`; `git check-ignore -v research-scratch/...` | **Blocker** |
| A-11 | **Seven citations resolve only to untracked files**, so they work on this machine and break on a fresh clone: `README.md:122` → `.agents/README.md`; `docs/SKILL_AUTOMATION.md:29` → `.copilot/instructions.md`; `docs/UMAMUSUME_REFERENCE.md:996,:1042` and `docs/scenarios/09:727,:729` → files inside the ignored root `research-scratch/`; `docs/research-scratch/PROCESS-PLANS.md:27` → the inventory above. `docs/PLAN-UI-UX-2026-10-02.md` is also untracked and unregistered in `INDEX.md`. | census UNTRACKED list | **Major** |
| A-12 | **Every closure is gated on an unpushed branch.** Local master is 17 commits ahead of `origin/master`, which carries no `docs/research-scratch/` at all. Register discipline closes a KI only when the fix is on `origin/master`, so A-2 cannot be actioned until the push is decided. | `git rev-list --left-right --count HEAD...origin/master` = `17 0` | **Blocker** (owner gate O-1) |

**Not a gap:** the 505 GONE count is not 505 broken instructions. Most are provenance lines inside masters naming the source they absorbed, which are records. The census says so itself (`tools/doc_census.py:109-111`): "Most GONE lines are historical citations inside masters naming the sources they absorbed... repoint only the lines a reader would actually follow." Task 5 triages rather than bulk-rewrites; that distinction is the difference between a repair and a lore-gate-wide vandalism of dated records.

---

## Part B — Execution plan

### Task 1: Recover the governing inventory and correct the false-absence claim

**Files:**
- Read: `research-scratch/DOCUMENTATION-INVENTORY-2026-09-30.md` (`:353` §6 Cross-reference map, and §7, §10, §11)
- Modify (append only): `docs/research-scratch/CONSOLIDATION-LOG.md:1-6`

**Interfaces:**
- Consumes: nothing.
- Produces: the authoritative source→master→section map that Tasks 5 and 6 apply; a corrected record of what the consolidation could actually read.

- [x] **Step 1: Read the sections the consolidation believed lost**

```bash
sed -n '353,460p' research-scratch/DOCUMENTATION-INVENTORY-2026-09-30.md
grep -nE '^## (7|10|11)\.' research-scratch/DOCUMENTATION-INVENTORY-2026-09-30.md
```

- [x] **Step 2: Record whether the substituted survey reached the same conclusions.** Diff the inventory's cross-reference map against `docs/research-scratch/INDEX.md`'s absorbed-source tables. Any source the map lists that no master claims is a still-unmerged file, not a repoint target.

- [x] **Step 3: Append the dated erratum** to `CONSOLIDATION-LOG.md`, after the existing `:4-6` paragraph, leaving that paragraph verbatim:

```markdown
> **Corrected 2026-10-02.** The claim above that the inventory is "absent from disk" is wrong. It is on
> disk at `research-scratch/DOCUMENTATION-INVENTORY-2026-09-30.md` (644 lines), inside the repo-root
> `research-scratch/` folder that `.gitignore:87` ignores. The verification recorded above
> (`git log --all -- '*DOCUMENTATION-INVENTORY*'`) reads git history only and cannot see an ignored,
> uncommitted file, so a history-only probe was reported as disk absence. §6 "Cross-reference map"
> (`:353`) was therefore available to the consolidation and was not read. The substituted filesystem
> survey stands as the record of what was actually merged; what changes is only that the sections said
> to be unreadable are readable.
```

- [x] **Step 4: Commit**

```bash
git add docs/research-scratch/CONSOLIDATION-LOG.md
git commit -m "docs(consolidation): correct the false absence claim about the governing inventory

The inventory was on disk in the gitignored root research-scratch/ folder; the
history-only probe could not see it. Sections 6/7/10/11 are readable."
```

---

### Task 2: Owner gate O-2 — restore or repoint the two authority documents

**Files:**
- Inspect: `git show 22e5135^:docs/GATE-REGISTRY.md`, `git show 22e5135^:docs/PRE-MORTEM.md`
- Decision brief: `docs/research-scratch/PLANS-AND-BRIEFS.md` (living, append a section)

**Interfaces:**
- Consumes: Task 1's map.
- Produces: the ruling that Task 4's edits and 41 of the 505 GONE links depend on (22 `GATE-REGISTRY` + 19 `PRE-MORTEM`).

- [x] **Step 1: Recover both texts and size them**

```bash
git show 22e5135^:docs/GATE-REGISTRY.md | wc -l
git show 22e5135^:docs/PRE-MORTEM.md | wc -l
git show 22e5135^:docs/GATE-REGISTRY.md | grep -cE '^##? C-[0-9]'
```

- [x] **Step 2: Put the two options to the owner with their costs.** Append to `PLANS-AND-BRIEFS.md`:
  - **Restore:** `git checkout 22e5135^ -- docs/GATE-REGISTRY.md docs/PRE-MORTEM.md`. Clears 41 citations at once and repairs `AGENTS.md:178`'s precedence chain. Cost: two more files in a corpus that was deliberately consolidated to 16 masters, and `INDEX.md` records them as absorbed into `GOVERNANCE.md` — restoring creates a second copy of content that already has a home.
  - **Repoint:** keep the absorption, and edit `AGENTS.md:5,:94,:178` plus `README.md:18` to name `docs/research-scratch/GOVERNANCE.md` and its sections. Cost: 41 citations reviewed by hand, and the precedence chain loses its named #2.
  - Recommend **Restore** only if the absorbed `GOVERNANCE.md` sections are incomplete for C-5 and the pre-mortem §4 (repo #4) addendum; verify before recommending:

```bash
grep -nE 'C-5|pre-mortem|§4' docs/research-scratch/GOVERNANCE.md | head
```

- [x] **Step 3: Do not act on the recommendation without the owner's written ruling.** Record the choice and the date in the same section.

- [x] **Step 4: Commit**

```bash
git add docs/research-scratch/PLANS-AND-BRIEFS.md
git commit -m "docs(briefs): put the GATE-REGISTRY and PRE-MORTEM restore-or-repoint choice to the owner"
```

---

### Task 3: Reconcile the register against the tree

**Files:**
- Modify: `KNOWN-ISSUES.md` — heading status words plus appended dated blocks per entry; never delete an entry's history.
- Verify against: the shas listed in A-2.

**Interfaces:**
- Consumes: owner gate O-1 (the push). Closures require the fix on `origin/master`.
- Produces: the reconciled statuses and the authoritative filed/closed/open counts that Step 6 restates. Task 4 consumes nothing from here.

- [ ] **Step 1: Establish O-1 first.** — **ASKED IN THE REPORT; unpushed (23 0) at execution, so the fallback ran: verification done, closures HELD.** Ask the owner whether local master is pushed before this task's closures land. If not pushed, do the verification work now and hold the closure lines until after the push; record the hold in each entry rather than writing a premature closure.

- [x] **Step 2: Prove each of the seven claims independently before touching any heading.**

```bash
git show ed71741 --stat | head -5 && grep -n 'color-scheme' resources/css/app.css
git show 79d5f6f --stat | head -6 && sed -n '36p' ARCHITECTURE-ESSENTIALS.md && sed -n '158p' ARCHITECTURE.md
git ls-files database/seeders/data/ | wc -l          # expect 9, was the KI-49/KI-51 complaint
git ls-files database/seeders/data/ | grep -c 'race-tier-labels'   # expect 1, was KI-53/KI-54
sed -n '138,145p' config/uma.php                     # seed_file present, was KI-45's headline
grep -rn 'Schema::hasColumn\|->check(' database/migrations/ | head  # KI-50 still live?
```

- [ ] **Step 3: Close the six that are done, in house style.** — **HELD per Step 1 fallback: six Verified/HELD blocks written instead; KI-32 was closed concurrently by the M1 batch and its block reconciled to that heading.** KI-32, KI-48, KI-49, KI-51, KI-53 and KI-54 all describe a defect the tree no longer has. Append to each entry, after its existing body:

```markdown
**Closed 2026-10-02 (documentation-sync pass).** The fix landed at `<sha>`; this pass verified it on the
working tree (`<file:line>`) rather than re-deriving it from the report that filed it. Nothing about the
original finding is edited: it was correctly filed on 2026-10-01 against a tree that did not yet have the
fix. What was missing was the closure, and that is a register-lag defect, not a code defect.
```

and add `, CLOSED 2026-10-02` to the heading, matching the pattern at `KNOWN-ISSUES.md:1781`.

- [ ] **Step 4: Correct-forward the two that are half-true.** KI-45 keeps the NULL-tier gap (`scenario_slots.tier` NULL on 141 of 296) but its headline claim about offline population is superseded; KI-50's `->check()` no-op is still documented only as a test comment at `tests/Feature/SupportCardTest.php:19`. Append a dated correction naming `8b17703` for KI-45 and leave KI-50 OPEN with the verification result recorded.

- [x] **Step 5: File the one genuinely new defect this pass found.** `withTierLabelsFileAbsent()` at `tests/Feature/ScenarioSlotSeederResilienceTest.php:102` renames the fixture with no `file_exists` guard. Use the next free number, state the command that proves it, and follow the entry preamble's rule that each entry names its proof.

- [x] **Step 6: Restate the authoritative status block (closes A-1).** The block at `:10` claims "35 filed, 24 closed, 11 open" and `:28-29` declares those numbers authoritative over any heading grep. Append a new dated block immediately above it, leaving every prior block verbatim, and count by the register's own rule rather than by regex: read each heading once, and count KI-10 and KI-25 by the state their body assigns (KI-10 ratio half open, KI-25 re-opened), because a heading that names both words is one entry, not two.

```markdown
**Status (2026-10-02, documentation-sync pass):** **54 filed, <N> closed, <M> open**, no entry filed here
and <K> closed here, all of them on fixes that landed before this pass. The count is by entry, not by
heading grep: KI-10 and KI-25 each carry both words in one heading and are counted once, in the state
their body assigns. The block below stays as the record of what this register believed on 2026-09-30;
it was accurate when written and is now superseded by nineteen entries' worth of work.
```

Fill `<N>`, `<M>`, `<K>` from Step 2's verification, not from a grep. Do not delete the superseded block: `:24-29` is the register's own warning that heading greps mislead, and that warning is still load-bearing.

- [x] **Step 7: Verify no entry lost history**

```bash
git diff --stat KNOWN-ISSUES.md            # additions dominate deletions
git diff KNOWN-ISSUES.md | grep -c '^-[^-]' # expect near zero: removals should be heading edits only
```

- [x] **Step 8: Commit**

```bash
git add KNOWN-ISSUES.md tests/Feature/ScenarioSlotSeederResilienceTest.php
git commit -m "docs(register): reconcile seven entries against the tree and file the unguarded fixture helper"
```

---

### Task 4: Repair the doc map and the precedence chain — **HELD on owner gate O-2 (ruling requested in `PLANS-AND-BRIEFS.md`, recommendation Repoint, nothing applied)**

**Files:**
- Modify in place: `AGENTS.md:5`, `:94`, `:178`; `README.md:18` (and `README.md:122` if `.agents/README.md` stays untracked — Task 8).

**Interfaces:**
- Consumes: Task 2's owner ruling.
- Produces: a chain where every named authority resolves on a fresh clone.

- [ ] **Step 1: Replace each dangling authority with the target Task 2 chose.** Example under the repoint branch, `AGENTS.md:178`:

```markdown
When documents disagree: `CONSTRAINTS.md` (the bar) > the gate table in
`docs/research-scratch/GOVERNANCE.md` §"GATE-REGISTRY.md" (how each gate runs) > ADRs (`docs/adr/`)
> `DESIGN.md` > slice plans (`PLAN.md`).
```

Under the restore branch, leave the chain intact and confirm `docs/GATE-REGISTRY.md` now exists.

- [ ] **Step 2: Fix the same two paths everywhere they appear as instructions, not history.** Read each hit and classify it as a pointer a reader follows (fix) or a provenance line (leave):

```bash
grep -rn 'GATE-REGISTRY\|PRE-MORTEM' --include='*.md' . | grep -v '^\./research-scratch/'
```

- [ ] **Step 3: Verify the chain resolves**

```bash
for p in CONSTRAINTS.md docs/GATE-REGISTRY.md docs/adr docs/DESIGN.md PLAN.md; do [ -e "$p" ] && echo "ok   $p" || echo "MISS $p"; done
```

- [ ] **Step 4: Commit**

```bash
git add AGENTS.md README.md
git commit -m "docs(agent-map): point the precedence chain at documents that exist"
```

---

### Task 5: Repoint the live citations, in density order, triaged by hand — **PARTIALLY EXECUTED, see the disposition note inside** — the measurement and the worklist are done and committed; the per-file triage of 200 refs across 29 files was scoped down to what a single pass could do without bulk-editing dated records, and the residual is the next pass

**Files:**
- Modify: the masters and root docs named below, section-internal edits only.
- Reference: `docs/research-scratch/INDEX.md` absorbed-source tables and Task 1's §6 map.

**Interfaces:**
- Consumes: Task 1 (map), Task 2 (ruling), and Task 7's ratchet (the baseline is recorded here, enforced there).
- Produces: a dead-link count that Task 7 pins.

- [x] **Step 1: Record the pre-repair baseline so the ratchet has a floor and progress is measurable.**

```bash
python tools/doc_census.py 2>&1 | grep 'dead markdown links' | tee /tmp/dead-baseline.txt
```

- [x] **Step 2: Generate the per-target worklist instead of guessing at it.**

```bash
python tools/doc_census.py 2>&1 | sed -n '/most-cited dead targets/,$p'
```

- [ ] **Step 3: Apply the known absorption map to the eight densest targets.** — **SCOPED DOWN:** the two O-2-gated targets (36 + 32 hits) cannot be repointed until the ruling; the convention fix (Task 6) was chosen over bulk editing because KNOWN-ISSUES.md entries are dated records and may not be edited in place, and most of DESIGN-CORPUS / SKILLS-MECHANICS hits are provenance lines the census overcounts. Measured precisely: 200 actionable refs across 29 files (`python .scratch-uma/task5-worklist.py`). Verified section anchors:

| Cited path | Hits | Repoint to |
|---|---|---|
| `docs/design-research/CONSTRAINTS.md` | 56 | `docs/research-scratch/DESIGN-CORPUS.md` §`CONSTRAINTS.md` (`:1665`) |
| `docs/design-research/DESIGN.md` | 42 | `docs/research-scratch/DESIGN-CORPUS.md` §`DESIGN.md` (`:18`); the 44px control rule is `:904` |
| `docs/GATE-REGISTRY.md` | 22 | per Task 2 |
| `RAW-FINDINGS.md` | 20 | `docs/research-scratch/DESIGN-CORPUS.md` §`RAW-FINDINGS.md` |
| `docs/PRE-MORTEM.md` | 19 | per Task 2 |
| `SKILLS-GAPS.md` (+ `docs/design-research/SKILLS-GAPS.md`) | 19 + 11 | `docs/research-scratch/SKILLS-MECHANICS.md` §`SKILLS-GAPS.md` |
| `docs/SOURCE-OF-TRUTH.md` | 17 | `docs/research-scratch/GOVERNANCE.md` §`SOURCE-OF-TRUTH.md` (`:16`) |
| `docs/design-research/verification/slice-*.md` | 15 files | `docs/research-scratch/SLICE-RECORDS.md` §`slice-NN-YYYY-MM-DD.md` |

- [ ] **Step 4: Work the densest citing files first, one file per commit** — **NOT STARTED as a bulk pass**; the register (10 refs) resolved by convention rather than by edit for the same dated-record reason. Remaining: SKILLS-MECHANICS 34, DESIGN-CORPUS 25, CONSOLIDATION-LOG 12, SLICE-RECORDS 12, CATALOG-ROSTER 12, plus ~24 lighter files; PRODUCT.md is machine-generated and excluded, docs/UMAMUSUME_REFERENCE.md was peer-dirty during this pass., in this order: `CATALOG-ROSTER-WORKSTREAM.md` (57), `SKILLS-MECHANICS.md` (44), `SLICE-RECORDS.md` (29), `KNOWN-ISSUES.md` (26), `RACE-AND-SLICE-RESEARCH.md` (18), `GOVERNANCE.md` (16), `docs/adr/0008*` (16), `DESIGN-CORPUS.md` (15), `PROCESS-PLANS.md` (14), `PLAN.md` (11), `PLANS-AND-BRIEFS.md` (10).

- [ ] **Step 5: Keep the rules distinct per file class.**
  - Masters and ADRs: replace a followed pointer inline only where the sentence directs a reader; where the line is a dated record, append a dated erratum instead and leave the original path visible.
  - Never renumber or reorder a section heading; other files anchor into it.
  - `docs/adr/*`: appended errata only (`ADR-0012:7,:73` is the working example).
  - Prove every regex before it runs. These files are hard-wrapped prose; a line-based `sed` will silently miss a citation split across lines and will happily rewrite a provenance line. Check each pattern's match count first:

```bash
grep -cE 'docs/design-research/CONSTRAINTS\.md' docs/research-scratch/DESIGN-CORPUS.md
```

- [ ] **Step 6: Re-measure after each file and confirm the count falls by the expected amount.** On the final pass, record the number Task 7 pins:

```bash
python tools/doc_census.py 2>&1 | grep 'dead markdown links' | tee /tmp/dead-post.txt
composer lore && composer lore-code
```

The lore counts move when a citation is rewritten into a section title that carries a banned word; current baseline is 181 hits / 55 exempt and lore-code 42 (measured 2026-10-02 at `e18a032`). Capture the pre-run number per task and compare to it, never to a dated constant.

- [ ] **Step 7: Commit per file**

```bash
git add docs/research-scratch/CATALOG-ROSTER-WORKSTREAM.md
git commit -m "docs(research-scratch): repoint CONSTRAINTS and DESIGN citations into DESIGN-CORPUS sections"
```

---

### Task 6: Fix the KI-id citation defects and the DESIGN.md ambiguity convention

**Files:**
- Modify: the 6 `KI-16` and 8 `KI-34` citations outside the register.
- Modify: `docs/research-scratch/DESIGN-CORPUS.md` (appended dated convention note) and `KNOWN-ISSUES.md` (KI-52 note).

**Interfaces:**
- Consumes: Task 3 (KI-16's renumbering is documented at `KNOWN-ISSUES.md:23`).
- Produces: a citation form that resolves for both the register's own headings and the corpus.

- [ ] **Step 1: Repoint `KI-16` to `KI-30`**, keeping the pre-merge number visible on first mention per file, since the register's own entry says it was filed as KI-16: `KI-30 (filed as KI-16 on `fix/frontend-audit-2026-09-28`)`.

- [ ] **Step 2: Annotate every `KI-34` citation** so a reader cannot hunt for an entry that does not exist: `KI-34 (a reservation, not a landed entry; see `KNOWN-ISSUES.md:21-24`)`.

- [ ] **Step 3: Write the disambiguation convention.** `DESIGN.md` is a basename shared by root `DESIGN.md` and by `DESIGN-CORPUS.md` §`DESIGN.md`, and `docs/design-research/DESIGN.md` no longer exists. Root `DESIGN.md` has no §6.14, so KI-29's and KI-37's headings cite a section number that resolves only through the corpus. Append to `DESIGN-CORPUS.md` and note it under KI-52:

```markdown
**Convention (2026-10-02).** A bare `DESIGN.md` citation means the root contract. A section of the corpus
is cited as `DESIGN-CORPUS.md §"DESIGN.md" §6.14`. Register headings KI-29 and KI-37 predate this and
name `DESIGN.md §6.14` for a rule that lives at `DESIGN-CORPUS.md:904`; they are left as filed, and this
convention is the correction forward that KI-52 asks for.
```

- [ ] **Step 4: Give the never-cited entries the coverage each one deserves.** Five entries appear nowhere outside the register (KI-31, KI-46, KI-52, KI-53, KI-54), and only one of them needs a doc:
  - KI-52 is handled by Step 3's convention.
  - KI-46 (`turns.0.speed` printed to the Trainer in an import error) is live user-facing copy with no reflection outside the register and one test comment at `tests/Feature/SupportCardTest.php:19`. Add it to `PLAN.md`'s open-frontend-work list, since `PLAN.md` is the living frontend plan and this is a copy defect:

```markdown
| Open | KI-46 | Import error text names the internal array path (`turns.0.speed`) to the Trainer.
  Registered only in `KNOWN-ISSUES.md`; needs a Form Request message rewrite, not a display patch. |
```

  - KI-31, KI-53, KI-54 are closed or being closed by Task 3, and a historical defect with no live surface does not need doc echo. Record that reasoning in Task 3's sweep note so the absence reads as a decision rather than an oversight.

- [ ] **Step 5: Verify no unresolved KI id remains cited outside the register.** The register itself is excluded on purpose: it names KI-16 and KI-34 while explaining they have no entry.

```bash
grep -oE '^## KI-[0-9]+[ab]?' KNOWN-ISSUES.md | sed 's/^## //' | sort -u > /tmp/e.txt
git grep -oE 'KI-[0-9]+[ab]?\b' -- '*.md' | grep -v '^KNOWN-ISSUES\.md:' \
  | sed 's/^[^:]*://' | sort -u > /tmp/c.txt
comm -13 /tmp/e.txt /tmp/c.txt    # expect: nothing. KI-16 and KI-34 must be gone from /tmp/c.txt
```

- [ ] **Step 6: Commit**

```bash
git commit -am "docs(citations): resolve the KI-16 and KI-34 id defects and fix the DESIGN.md convention"
```

---

### Task 7: Ratchet the dead-link count so this cannot recur

**Files:**
- Create: `tests/Feature/DocCitationParityTest.php`
- Modify: `composer.json` (scripts), `CONSTRAINTS.md` only if a gate row is added — the bar is not lowered anywhere.

**Interfaces:**
- Consumes: Task 5's post-repair baseline.
- Produces: a gate every future doc edit hits, mirroring `tests/Feature/LoreGateParityTest.php` (which pins `tools/lore.php`) and `tests/Feature/DocSchemaDriftTest.php`.

- [x] **Step 1: Write the failing test first.** It pins the count at the post-repair number Task 5 Step 6 measured, so the figure may fall and never rise. The single value below is filled from that measurement (`grep 'dead markdown links' /tmp/dead-post.txt`), exactly as `LoreGateParityTest:125` pins the composer script strings it guards:

```php
<?php

declare(strict_types=1);

it('keeps dead markdown citations from rising above the recorded baseline', function (): void {
    $out = (string) shell_exec('python tools/doc_census.py 2>&1');
    expect($out)->toMatch('/dead markdown links: \d+/');
    preg_match('/dead markdown links: (\d+)/', $out, $m);

    // Ratchet, set from the 2026-10-02 post-repair census run. Lower it when a
    // citation is repointed; never raise it to make a failing gate pass.
    expect((int) $m[1])->toBeLessThanOrEqual(DOC_CITATION_BASELINE);
});
```

Define `DOC_CITATION_BASELINE` as an `int` constant at the top of the file, set to the measured number.

- [x] **Step 2: Run it and confirm it fails against the pre-repair count and passes at the baseline**

```bash
php artisan test --compact --filter=DocCitationParityTest
```

- [x] **Step 3: Register the script beside `lore` in `composer.json`.** Existing entries are `"lore": "@php tools/lore.php"` and `"lint": "pint --test"`, so a plain shell command is the correct form here (`@` is only valid for composer's own aliases such as `@php`; there is no `@python`):

```json
"docs": "python tools/doc_census.py",
```

Verify with `composer docs`. Deliberately **not** mirrored into the `Makefile`: GNU make cannot run on this host (KI-4) and the Makefile targets are documentation, so the `make`/composer parity assertion in `LoreGateParityTest:101-116` is left unextended for `docs`. Record that reason in the test's comment so a later reader does not "fix" the asymmetry by adding a dead target.

- [x] **Step 4: Note the truncation limit so the test is not mistaken for a full report** — `tools/doc_census.py:112` prints only `dead[:20]`, so the count is the signal and the per-target summary is the worklist. Record that in the test's comment; do not print 500 lines in a gate.

- [x] **Step 5: Commit**

```bash
git add tests/Feature/DocCitationParityTest.php composer.json
git commit -m "feat(docs): ratchet dead citation counts in a Pest gate"
```

---

### Task 8: Resolve the untracked-cited assets and register the two plans

**Files:**
- Decide: `.agents/README.md`, `.copilot/instructions.md`, root `research-scratch/*` (`gitignored at .gitignore:87`).
- Modify: `docs/research-scratch/INDEX.md`; commit `docs/PLAN-UI-UX-2026-10-02.md`.

**Interfaces:**
- Consumes: nothing; blocks the census seeing this plan (it reads `git ls-files`, so an uncommitted file is invisible to its own gate).

- [ ] **Step 1: For each of the seven UNTRACKED citations, commit the target or withdraw the pointer.** — **PENDING O-3** (24 UNTRACKED remain; the two plans are no longer among them — both tracked now) `.agents/` is a tooling layer the repo map already documents; `.copilot/instructions.md` is machine-local and should be withdrawn from `docs/SKILL_AUTOMATION.md:29` rather than committed. Ask before adding files to git; do not un-ignore `research-scratch/` without the owner, since the folder holds scratch bodies by design.

- [ ] **Step 2: Decide the root `research-scratch/` disposition with the owner.** — **PENDING O-3** It contains the governing inventory and cited scrape files. Options: promote the durable ones into `docs/research-scratch/` as registered masters, or leave them ignored and mark every citation as scratch-only. Leaving it ignored while docs cite into it is the state that produced A-10.

- [x] **Step 3: Commit and register both plans**

```bash
git add docs/PLAN-UI-UX-2026-10-02.md docs/PLAN-DOC-SYNC-2026-10-02.md docs/research-scratch/INDEX.md
git commit -m "docs: register the UI/UX and documentation-sync plans in the index"
```

- [x] **Step 4: Verify the census now sees them**

```bash
python tools/doc_census.py 2>&1 | grep -E 'tracked markdown|dead markdown links'
```

---

## Owner gates this plan cannot pass alone

| Gate | Question | Blocks |
|---|---|---|
| **O-1** | Push local master (17 ahead, includes all of `docs/research-scratch/`) before register closures land? | Task 3's closure lines; every KI closure in the repo |
| **O-2** | Restore `docs/GATE-REGISTRY.md` + `docs/PRE-MORTEM.md` from `22e5135^`, or repoint the 41 citations into `GOVERNANCE.md` sections? | Tasks 4 and 5 |
| **O-3** | Promote durable files out of the gitignored root `research-scratch/`, or declare every citation into it scratch-only? | Task 8, and it decides whether A-10 can happen again |

## Out of scope

- Any code, migration, or view change. This plan edits documentation and one test.
- `CONSTRAINTS.md` thresholds, in any direction.
- Rewriting ADR or slice-record history, resolving KI-15's source contradiction, or ruling on the KI-25 768px floor (R82) — each is a product or schema decision, not a citation repair.
- The `docs/PLAN-UI-UX-2026-10-02.md` workstreams themselves; that plan owns them.

## Self-review against the analysis

**Coverage.** A-1 → Task 3 Step 6 (status block) with the recount rule it names. A-2 → Task 3 Steps 2-4. A-3 → Task 3 Step 4. A-4 → Tasks 2 and 4. A-5, A-6 → Task 5. A-7 → Task 7. A-8, A-9 → Task 6 Steps 1-4. A-10 → Task 1. A-11 → Task 8. A-12 → owner gate O-1, applied as Task 3 Step 1's precondition.

**Commands verified while writing this plan**, not assumed: the dead-link baseline and the UNTRACKED list (`python tools/doc_census.py`, plus the `dead_links()` one-liner); the recovery reads `git show 22e5135^:docs/GATE-REGISTRY.md` (159 lines) and `...:docs/PRE-MORTEM.md` (98 lines); the id-arithmetic recipe in Task 6 Step 5, which today returns exactly `KI-16` and `KI-34`; `python` present and `python3` absent; `git rev-list --left-right --count HEAD...origin/master` = `17 0`.

**Defects found in this plan by its own review and fixed inline:**
- A `"@python"` composer script form was written first. Composer only aliases its own commands (`@php`), and the existing `"lint": "pint --test"` proves a bare shell command is correct. Task 7 Step 3 now uses `"docs": "python tools/doc_census.py"`.
- A fabricated ratchet number was replaced by a constant filled from the measurement recorded in Task 5 Step 6, because a pinned figure nobody measured is how a gate gets lowered later to pass.
- The id-arithmetic check first excluded the register with a git pathspec (`:!KNOWN-ISSUES.md`) and then verified the wrong direction of the set difference. Both are corrected in Task 6 Step 5, which is the version that was run.
- A-1 had no task when the analysis was written; the status block is stale and self-declaring, so it needed a recount step of its own rather than riding on Task 4.

**What this plan does not claim.** It does not claim the 505 GONE links are 505 broken instructions, nor that repointing them is a mechanical pass: Task 5 triages per line, and the census's own note (`tools/doc_census.py:109-111`) is quoted as the rule. The final count after Task 5 is unknown here by design, and Task 7 pins whatever it measures.
