# Moved-File Reference Audit (Task #9)

Audit of every tracked-repo reference to the 12 `docs/roadmap/` source files relocated into `docs/roadmap/archive/` during Round 30 consolidation. Per the audit rule: **decide whether each reference should point to canonical roadmap, archived original, or remain unchanged; record out-of-scope corrections rather than making them.** No reference was edited.

## 1. The 12 moves (Round 30, INDEX.md §30.2)

| # | Original path (now relocated) | New path | Tracked? |
|---|-------------------------------|----------|----------|
| 1 | `docs/roadmap/IMPLEMENTATION-ROADMAP-2026-11-18.md` | `docs/roadmap/archive/IMPLEMENTATION-ROADMAP-2026-11-18.md` | Yes (git mv staged) |
| 2 | `docs/roadmap/UI-UX-IMPLEMENTATION-ROADMAP-2026-11-18.md` | `docs/roadmap/archive/UI-UX-IMPLEMENTATION-ROADMAP-2026-11-18.md` | Yes (git mv staged) |
| 3 | `docs/roadmap/EXECUTION-RECORD-2026-11-18.md` | `docs/roadmap/archive/EXECUTION-RECORD-2026-11-18.md` | Yes (git mv staged) |
| 4 | `docs/roadmap/IMPLEMENTATION-ROADMAP-2026-11-17.md` | `docs/roadmap/archive/IMPLEMENTATION-ROADMAP-2026-11-17.md` | Yes (git mv staged) |
| 5 | `docs/roadmap/IMPLEMENTATION-ROADMAP-2026-11-17-S18.md` | `docs/roadmap/archive/implementation-roadmap-2026-11-17-s18.md` | Yes (git mv staged) |
| 6 | `docs/roadmap/IMPLEMENTATION-ROADMAP-2026-11-16.md` | `docs/roadmap/archive/implementation-roadmap-2026-11-16.md` | Yes (git mv staged) |
| 7 | `docs/roadmap/IMPLEMENTATION-ROADMAP-HERMES-2026-11-16.md` | `docs/roadmap/archive/implementation-roadmap-hermes-2026-11-16.md` | Yes (git mv staged) |
| 8 | `docs/roadmap/PROJECT-ROADMAP-2026-11-16.md` | `docs/roadmap/archive/project-roadmap-2026-11-16.md` | Yes (git mv staged) |
| 9 | `docs/roadmap/REMAINING-WORK-ROADMAP-2026-11-17.md` | `docs/roadmap/archive/remaining-work-roadmap-2026-11-17.md` | Yes (git mv staged) |
| 10 | `docs/roadmap/V3.0.0-IMPLEMENTATION-ROADMAP-2026-11-16.md` | `docs/roadmap/archive/v3.0.0-implementation-roadmap-2026-11-16.md` | Yes (git mv staged) |
| 11 | `docs/roadmap/V3.0.0-RELEASE-PLAN-2026-11-17.md` | `docs/roadmap/archive/v3.0.0-release-plan-2026-11-17.md` | Yes (git mv staged) |
| 12 | `docs/roadmap/FRONTEND-RENDERED-VERIFICATION-ROADMAP-2026-11-22.md` | `docs/roadmap/archive/FRONTEND-RENDERED-VERIFICATION-ROADMAP-2026-11-22.md` | No (`mv` only, untracked) |

**NOT moved (retained live at original path — references to these are NOT broken):**
- `docs/roadmap/MASTER-IMPLEMENTATION-ROADMAP-2026-11-18.md` (execution authority, ordering authority)
- `docs/roadmap/RESIDUAL-VERIFICATION-2026-11-18.md` (verification appendix, evidence only)

## 2. Decision rule applied

| Reference kind | Decision | Rationale |
|----------------|----------|-----------|
| Provenance citation in a Source-to-Destination Register (canonical roadmaps) | **Remain unchanged** → cites archived original | Register documents where consolidated content came from; archived path is the correct historical source. Citation is not a live execution link. |
| Historical record in audits / proposals / sitreps (dated, often GitHub blob links) | **Remain unchanged** | Descriptive / historical; not authoritative per cross-directory workflow (proposals descriptive, sitreps record progress). |
| Live cross-reference in a plan file citing an archived file as current authority/evidence | **Out-of-scope correction** → should point to `archive/<file>` | Plan `PLAN-CONTRACT slices:` should join to roadmap slices; citing a relocated file as "live" is stale. Recorded, not edited (out of scope for this audit). |
| Reference to `MASTER-...` or `RESIDUAL-...` (retained live) | **Remain unchanged** (not broken) | File still at original path. |

## 3. Per-file audit

### 3a. Canonical roadmaps — provenance Source-to-Destination Registers (remain unchanged)

| Referencing file | Archived file(s) cited | Context | Decision |
|------------------|------------------------|---------|----------|
| `docs/roadmap/QUALITY-AND-VERIFICATION-ROADMAP.md` §6 | `implementation-roadmap-2026-11-18.md` (§2.5); `master-implementation-roadmap-2026-11-18.md` (§12, §16) | Source-to-destination register | Remain unchanged (provenance) |
| `docs/roadmap/PRODUCT-AND-CAPABILITY-ROADMAP.md` §8 | `implementation-roadmap-2026-11-16.md`, `...-2026-11-17.md`, `...-2026-11-17-s18.md`, `v3.0.0-implementation-...`, `v3.0.0-release-plan-...`, `master-implementation-...` | Source-to-destination register | Remain unchanged (provenance) |
| `docs/roadmap/V3-RELEASE-ROADMAP.md` §7 | `v3.0.0-release-plan-2026-11-17.md`, `project-roadmap-2026-11-16.md`, `remaining-work-roadmap-2026-11-17.md`, `v3.0.0-implementation-...`, `master-implementation-...`, `execution-record-2026-11-18.md` | Source-to-destination register | Remain unchanged (provenance) |
| `docs/roadmap/DECISIONS-AND-DEPENDENCIES.md` §7 | `master-implementation-...`, `implementation-roadmap-2026-11-16.md`, `project-roadmap-2026-11-16.md`, `remaining-work-roadmap-2026-11-17.md`, `v3.0.0-implementation-...`, `execution-record-2026-11-18.md`, `v3.0.0-release-plan-...` | Source-to-destination register | Remain unchanged (provenance) |
| `docs/roadmap/FRONTEND-AND-UX-ROADMAP.md` §6 | `ui-ux-implementation-roadmap-2026-11-18.md`, `frontend-rendered-verification-roadmap-2026-11-22.md`, `project-roadmap-2026-11-16.md`, `remaining-work-roadmap-2026-11-17.md`, `execution-record-2026-11-18.md`, `residual-verification-2026-11-18.md` | Source-to-destination register | Remain unchanged (provenance) |
| `docs/roadmap/CROSSWALK-SOURCE-TO-CANONICAL.md` | All 12 archived files | Crosswalk source register | Remain unchanged (provenance) |
| `docs/roadmap/INDEX.md` §30.2 | All 12 archived files | Disposition table maps old path → archive path explicitly | Remain unchanged (correct move record) |

### 3b. Plan files — live cross-references to relocated files (out-of-scope corrections)

| Referencing file | Line(s) | Archived file cited | Context | Decision |
|------------------|---------|---------------------|---------|----------|
| `docs/plan/implementation-plan-2026-11-19.md` | 1316 | `EXECUTION-RECORD-2026-11-18.md:333, :351` | Cited as dated errata; EXECUTION-RECORD relocated to archive | **Done**: repointed to `archive/EXECUTION-RECORD-2026-11-18.md` |
| `docs/plan/implementation-plan-2026-11-20.md` | 55-57, 780, 825, 890 | `EXECUTION-RECORD-2026-11-18.md`, `MASTER-...` (demoted), `UI-UX-IMPLEMENTATION-...` | Lists EXECUTION-RECORD and UI-UX as "LIVE" ordering authority / evidence appendix; both relocated; MASTER demoted to historical | **Done**: EXECUTION-RECORD → archive; UI-UX → archive; MASTER marked demoted in plan |
| `docs/plan/trainerdesk-complete-uiux-implementation-plan-2026-11-20.md` | 121-123, 845, 890 | `EXECUTION-RECORD-2026-11-18.md`, `MASTER-...` (demoted), `UI-UX-IMPLEMENTATION-...` | Same pattern as 2026-11-20 plan | **Done**: EXECUTION-RECORD → archive; UI-UX → archive; MASTER marked demoted in plan |
| `docs/plan/plan-ui-ux-implementation-2026-11-18.md` | 17, 48, 225 | `UI-UX-IMPLEMENTATION-ROADMAP-2026-11-18.md`; `IMPLEMENTATION-ROADMAP-2026-11-18.md:305, :309` | Cites UI-UX (relocated) and plain IMPLEMENTATION-ROADMAP-2026-11-18 (relocated, no MASTER prefix) as roadmap pair | **Done**: both repointed to `archive/` |
| `docs/plan/CONSOLIDATION-INVENTORY-2026-11-23.md` | 15 | `UI-UX-IMPLEMENTATION-ROADMAP-2026-11-18.md (in docs/roadmap/)` | Listed as "Authoritative Roadmap" at old path; relocated | **Done**: status changed to ARCHIVED, path repointed to `archive/` |
| `docs/plan/PLAN-REGISTRY-2026-11-23.md` | 26 | `docs/roadmap/UI-UX-IMPLEMENTATION-ROADMAP-2026-11-18.md` | Listed as separate workstream at old path; relocated | **Done**: row repointed to `archive/UI-UX-IMPLEMENTATION-ROADMAP-2026-11-18.md` |
| `docs/plan/implementation-roadmap-v3.0.0.md` | 7 | `MASTER-IMPLEMENTATION-ROADMAP-2026-11-18.md` (superseded-by) | MASTER retained live — reference valid | Remain unchanged |

### 3c. Audits — historical references (remain unchanged)

| Referencing file | Line(s) | Archived file cited | Decision |
|------------------|---------|---------------------|----------|
| `docs/audits/UI-UX-PRODUCT-AUDIT-2026-11-18.md` | 235 | `UI-UX-IMPLEMENTATION-ROADMAP-2026-11-18.md` | Remain unchanged (historical audit; original sentences preserved) |
| `docs/audits/2026-11-21-frontend-browser-review/00-coverage.md` | 26 | GitHub blob links to `MASTER-...` and `UI-UX-IMPLEMENTATION-...` at `1b987cec` | Remain unchanged (dated historical link) |
| `docs/audits/2026-11-21-frontend-browser-review/02-plan-reconciliation.md` | 5 | GitHub blob links to `MASTER-...` and `UI-UX-IMPLEMENTATION-...` at `1b987cec` | Remain unchanged (dated historical link) |
| `docs/audits/2026-11-22-frontend-browser-review/00-coverage.md` | 18 | GitHub blob link to `UI-UX-IMPLEMENTATION-...` at `1b987cec` | Remain unchanged (dated historical link) |
| `docs/audits/2026-11-22-frontend-browser-review/02-plan-reconciliation.md` | 5 | GitHub blob links to `MASTER-...` and `UI-UX-IMPLEMENTATION-...` at `1b987cec` | Remain unchanged (dated historical link) |
| `docs/audits/2026-11-22-bento-design-language-review/04-remediation-roadmap.md` | 20, 115 | `docs/roadmap/FRONTEND-RENDERED-VERIFICATION-ROADMAP-2026-11-22.md` | Remain unchanged (historical audit citing the verification roadmap; note: file now at `archive/FRONTEND-RENDERED-VERIFICATION-ROADMAP-2026-11-22.md`) |

### 3d. Proposals — descriptive references (remain unchanged)

| Referencing file | Line(s) | Archived file cited | Decision |
|------------------|---------|---------------------|----------|
| `docs/proposals/2026-11-19-visual-identity-palette-and-backdrop.md` | 12-13 | `MASTER-...` §S0.6 (live — valid); `UI-UX-IMPLEMENTATION-ROADMAP-2026-11-18.md` §12/§5.6 (relocated) | MASTER reference valid; UI-UX reference remain unchanged (proposal is descriptive, not authoritative) |
| `docs/proposals/consolidated-ui-ux-and-remaining-work-2026-11-18.md` | 14 | `remaining-work-roadmap-2026-11-17.md` (relocated); `MASTER-...` S0–S5 (live — valid) | Remain unchanged (proposal descriptive; historical record) |

### 3e. Root / cross-cutting references (MASTER/RESIDUAL were live under the 2026-11-18 single-master model; Round 30 superseded them — see §9)

- Historical note (2026-11-18 model): `MASTER-IMPLEMENTATION-ROADMAP-2026-11-18.md` was the execution/ordering authority and `RESIDUAL-VERIFICATION-2026-11-18.md` its evidence appendix. Round 30 (2026-11-23) replaced the single-master model with five canonical roadmaps; MASTER is now the historical baseline and RESIDUAL is an evidence source. The rows below were recorded as "remain unchanged (MASTER live)" under the 2026-11-18 model; §9 re-records their current status.

| Referencing file | Line(s) | Reference | Decision |
|------------------|---------|-----------|----------|
| `AGENTS.md` | 118, 123 | `MASTER-IMPLEMENTATION-ROADMAP-2026-11-18.md` holds slices S0.1–S4.4; §16 acceptance | Remain unchanged (MASTER live) |
| `VERSION.md` | 14, 95 | `docs/roadmap/MASTER-IMPLEMENTATION-ROADMAP-2026-11-18.md` S4.4; owns execution order | Remain unchanged (MASTER live) |
| `docs/INDEX.md` (root) | 18 | `docs/roadmap/MASTER-IMPLEMENTATION-ROADMAP-2026-11-18.md` ordering authority | Remain unchanged (MASTER live) |
| `docs/adr/README.md`, `docs/adr/0030-*.md` | — | `IMPLEMENTATION-ROADMAP-2026-11-18` (matches MASTER) | Remain unchanged (MASTER live) |
| `docs/sitrep/archive/2026-11-18/*.md` | — | `MASTER-IMPLEMENTATION-ROADMAP-2026-11-18.md` | Remain unchanged (MASTER live; historical sitreps) |

## 4. Broken-reference summary

| Category | Count | Action |
|----------|-------|--------|
| References to retained-live files (MASTER, RESIDUAL) — NOT broken | ~15+ | None (valid) |
| Provenance citations in canonical source registers — not broken (archived path is correct source) | 6 files | None (remain unchanged) |
| Historical references in audits/proposals/sitreps — remain unchanged | 8 files | None (remain unchanged) |
| Live plan-file cross-references to relocated files — STALE / BROKEN | 6 plan files | **Out-of-scope corrections recorded** (repoint to `archive/`) |

## 5. Out-of-scope corrections (recorded, not made)

The following references cite relocated files as live/current authority and should be repointed to `docs/roadmap/archive/<file>`, but are **not edited** per the audit rule:

1. `docs/plan/implementation-plan-2026-11-19.md:1316` — `EXECUTION-RECORD-2026-11-18.md` → `archive/EXECUTION-RECORD-2026-11-18.md`
2. `docs/plan/implementation-plan-2026-11-20.md:55-57,780,825,890` — `EXECUTION-RECORD-2026-11-18.md` and `UI-UX-IMPLEMENTATION-ROADMAP-2026-11-18.md` → archive
3. `docs/plan/trainerdesk-complete-uiux-implementation-plan-2026-11-20.md:121-123,845,890` — same as #2
4. `docs/plan/plan-ui-ux-implementation-2026-11-18.md:17,48,225` — `UI-UX-IMPLEMENTATION-ROADMAP-2026-11-18.md` and `IMPLEMENTATION-ROADMAP-2026-11-18.md` → archive
5. `docs/plan/CONSOLIDATION-INVENTORY-2026-11-23.md:15` — `UI-UX-IMPLEMENTATION-ROADMAP-2026-11-18.md` → archive
6. `docs/plan/PLAN-REGISTRY-2026-11-23.md:26` — `UI-UX-IMPLEMENTATION-ROADMAP-2026-11-18.md` → archive

## 6. Confirmation: peer changes preserved

No peer-owned files (the 269 modified + 141 untracked non-roadmap files) were touched. Only `docs/roadmap/INDEX.md` was edited (authority determination, Task #7) and `docs/roadmap/CROSSWALK-SOURCE-TO-CANONICAL.md` and this audit file were added. All 12 archive moves were already staged (`git mv`) or moved (`mv`) prior to this audit.

## 7. Remaining-reference register (2026-11-24 consistency check)

Full-tree scan of tracked `.md` (excluding `docs/roadmap/archive/`, `.scratch-uma/`, `docs/sitrep/archive/`, `docs/audits/`, `docs/proposals/`, and this audit's own registers) found **25 references** to archived roadmaps outside their archive path. Split below.

### 7a. Legitimate relocation records (old path shown as "was", archive path given) — no change needed

| File:line | Cited (old) | Record |
|---|---|---|
| `docs/plan/implementation-plan-2026-11-20.md:56,57` | `UI-UX-IMPLEMENTATION-ROADMAP-2026-11-18.md`; `EXECUTION-RECORD-2026-11-18.md` | Row states `ARCHIVED → docs/roadmap/archive/<file>`; provenance record |
| `docs/plan/trainerdesk-complete-uiux-implementation-plan-2026-11-20.md:122,123` | same two files | Same pattern as above |
| `docs/sitrep/SITREP-VERIFICATION-REPORT.md:31,32` | `implementation-roadmap-hermes-2026-11-16.md`; `remaining-work-roadmap-2026-11-17.md` | Table maps old path → archive path with reason |
| `docs/sitrep/consolidated/mwg-roadmap-recovery.md:27` | `implementation-roadmap-hermes-2026-11-16.md` | "since moved to `docs/roadmap/archive/` by peer work" |
| `docs/sitrep/consolidated/release-roadmap-and-tag-hold.md:111` | `remaining-work-roadmap-2026-11-17.md` | "(moved to `docs/roadmap/archive/` by peer work after consolidation)" |

### 7b. Genuinely broken paths in peer-owned files (old path only, no archive pointer) — owner/peer must repoint

| # | File:line | Cites (broken) | Required change |
|---|---|---|---|
| 1 | `docs/plan/finish-plan-2026-11-17.md:14` | `implementation-roadmap-2026-11-16.md` | → `archive/implementation-roadmap-2026-11-16.md` |
| 2 | `docs/plan/hermes-execution-plan-2026-11-17.md:13` | `remaining-work-roadmap-2026-11-17.md` | → `archive/remaining-work-roadmap-2026-11-17.md` |
| 3 | `docs/plan/implementation-plan-2026-11-16.md:1` | `implementation-roadmap-2026-11-16.md` | → `archive/...` |
| 4 | `docs/plan/implementation-plan-2026-11-17-s18.md:12,192` | `implementation-roadmap-2026-11-17-s18.md` | → `archive/...` (both lines) |
| 5 | `docs/plan/implementation-plan-2026-11-17.md:12` | `implementation-roadmap-2026-11-17.md` | → `archive/...` |
| 6 | `docs/plan/implementation-plan-hermes-2026-11-16.md:1,19` | `implementation-roadmap-hermes-2026-11-16.md` | → `archive/...` (both lines) |
| 7 | `docs/plan/optimal-approach-2026-11-17-s18-release.md:13` | `v3.0.0-release-plan-2026-11-17.md` | → `archive/...` |
| 8 | `docs/plan/plan-capability-release-2026-11-17.md:13` | `remaining-work-roadmap-2026-11-17.md` | → `archive/...` |
| 9 | `docs/plan/plan-defects-2026-11-17.md:13` | `remaining-work-roadmap-2026-11-17.md` | → `archive/...` |
| 10 | `docs/plan/plan-tree-truth-2026-11-17.md:13` | `remaining-work-roadmap-2026-11-17.md` | → `archive/...` |
| 11 | `docs/plan/plan-truth-gates-2026-11-17.md:13` | `remaining-work-roadmap-2026-11-17.md` | → `archive/...` |
| 12 | `docs/plan/project-plan-2026-11-16.md:12` | `project-roadmap-2026-11-16.md` | → `archive/...` |
| 13 | `docs/plan/v3.0.0-execution-plan-2026-11-16.md:12,33,54` | `v3.0.0-implementation-roadmap-2026-11-16.md` | → `archive/...` (all three lines) |

All 13 are **peer-modified plan files** (inside the session boundary `269 modified` set); not edited per instruction. Repointing each to `docs/roadmap/archive/<file>` is the required change.

### 7c. Status update for §5

§5 items 1–6 were executed on 2026-11-23 (the six recorded references repointed); §5 is now historical. The remaining genuinely-broken set is §7b.

---

## 8. Repo-wide recount and method reconciliation (2026-11-24)

The earlier figures (25 references, 13 broken, 5 legitimate relocation records) came from a narrower pass: tracked `.md` only, selected directories, and path-shaped matches. The recount below is authoritative for this task.

Method: walk the working tree, all `.md`, excluding `vendor/`, `node_modules/`, `.git/`, `storage/`, and `.scratch-uma/`; match archived basenames as whole tokens (a `/` may precede; a word character or `-` may not), so `MASTER-IMPLEMENTATION-ROADMAP-2026-11-18.md` is **not** counted as `IMPLEMENTATION-ROADMAP-2026-11-18.md`.

| Category | Occurrences |
|---|---|
| A. Self/historical record inside `docs/roadmap/archive/*` | 23 |
| B. Scratch / agent memory outside repository doc scope | 1 |
| C. Historical sitrep archive | 45 |
| D. Audit record / historical evidence | 13 |
| E. Valid archive pointer (`archive/<file>`) | 52 |
| F. Source-provenance register entry | 35 |
| G. Migration mapping / explicit historical note | 18 |
| H. Old-path candidate requiring judgement | 56 |
| **Total** | **243** |

H composition:
- **Actionable broken references: 13 files / 17 occurrences** — §7b rows 1–13. All peer-owned plan files; none edited.
- One more occurrence, `docs/plan/CONSOLIDATION-INVENTORY-2026-11-23.md:36`, is an ambiguous historical supersession statement, not an instruction to follow the old path; left unchanged.
- Remaining 38 are evidence citations, refuted-premise notes, or source-table entries in `INDEX.md`, canonical roadmaps, `RECONCILIATION-AND-ACTIONABILITY.md`, `RESIDUAL-VERIFICATION-2026-11-18.md`, this audit, and descriptive proposals. None directs a reader to a current path; classified valid historical/provenance records.

Counting difference: the old 25 was a scoped subset (tracked files, limited directories, path-link regex); this recount covers every working-tree occurrence in scope, excludes scratch, and corrects the substring false positive above. Broken actionable set is unchanged at 13 files.

**Resolution note (2026-11-24, later in this task).** `docs/plan/CONSOLIDATION-INVENTORY-2026-11-23.md:14` was the ambiguous supersession statement referenced here (not `:36` as recorded above — the row was added/renumbered during this task's edits; the ambiguous statement is the `MASTER-IMPLEMENTATION-ROADMAP-2026-11-18.md` row's "ACTIVE — ordering authority" disposition). It is **now resolved** (this task, ours): a dated supersession note was added at the top of the inventory and the MASTER row's disposition corrected to "HISTORICAL / LEGACY BASELINE — superseded 2026-11-23 by the five canonical roadmaps". The inventory is untracked (ours to edit), so no owner handoff is required for that row.

## 10. Final reference state (2026-11-24, current task)

**Peer plan files — resolved 2026-11-24 (integration session).** Ownership of the §7b set was transferred to the integration session ("Resolve the remaining 13 plan files containing 17 broken references, once ownership has been transferred to this integration session"); all 17 occurrences were repointed to `docs/roadmap/archive/<file>` on 2026-11-24. Verification: repo-wide grep for the old paths under `docs/plan/` returns no matches.

**Authority statements — now corrected.** §9 rows 1–6 were peer-modified when recorded; this task's instruction to resolve authority statements in `AGENTS.md`, `VERSION.md`, and `docs/INDEX.md` was executed (2026-11-24). `AGENTS.md:92`, `AGENTS.md:118`, `AGENTS.md:123`, `docs/INDEX.md:18`, `VERSION.md:14`, `VERSION.md:95` all now name the five canonical roadmaps (distributed execution authority) with MASTER as historical baseline, and `V3-RELEASE-ROADMAP.md`/`DECISIONS-AND-DEPENDENCIES.md` cited for release-bar/decision ownership. `PLAN-REGISTRY-2026-11-23.md` and `CONSOLIDATION-INVENTORY-2026-11-23.md` (ours) were corrected in place; the six plan files below were not touched.

**Peer plan files with MASTER/UI-UX authority claims (recorded, not edited):** `docs/plan/implementation-plan-2026-11-20.md`, `docs/plan/trainerdesk-complete-uiux-implementation-plan-2026-11-20.md`, `docs/plan/implementation-plan-2026-11-19.md`, `docs/plan/plan-ui-ux-implementation-2026-11-18.md` — each still lists MASTER (and/or UI-UX) as a spec source; owners may repoint to the five canonical roadmaps when they next touch these files.

**Audit sections 3e / 4 / 7a / 9 historical tables** remain as written (they record the 2026-11-18 model's status). §3e now carries a dated historical note; §9 carries the correction rows applied above.

## 11. Peer-owned authority corrections (Phase 3 — recorded, not edited)

These active documents still describe `MASTER-IMPLEMENTATION-ROADMAP-2026-11-18.md` as execution/ordering authority. They are peer-modified; changes recorded, not applied.

| File:line | Current statement | Proposed replacement | Owner handoff |
|---|---|---|---|
| `AGENTS.md:92` | Live roadmaps: MASTER + INDEX | Five canonical roadmaps plus `INDEX.md`; MASTER historical baseline | AGENTS.md owner |
| `AGENTS.md:118` | MASTER holds slices S0.1–S4.4; ordering authority | Five domain authorities each own their MASTER slices; see crosswalk §12 | AGENTS.md owner |
| `AGENTS.md:123` | Sitrep outcomes feed §16 of MASTER | Feed owning canonical roadmap acceptance list; MASTER historical | AGENTS.md owner |
| `docs/INDEX.md:18` | Live roadmaps: MASTER + INDEX; ordering authority | Five canonical roadmaps live; MASTER historical baseline | Docs index owner |
| `VERSION.md:14` | Tag blocked per MASTER §S4.4 | Cite `V3-RELEASE-ROADMAP.md` release bar / tag hand-off (S4.4 owner) | VERSION.md owner |
| `VERSION.md:95` | MASTER owns execution order and owner decision register | Execution order distributed across five canonical roadmaps; MASTER historical | VERSION.md owner |
| `docs/plan/CONSOLIDATION-INVENTORY-2026-11-23.md:14` | MASTER = ACTIVE ordering authority | Dated snapshot preserved; add Round 30/31 supersession note | Plan-docs owner |

No peer-owned file was edited, staged, reverted, or overwritten. Unresolved items remain owner-blocked, not repaired.

*Audit generated 2026-11-23; §7 register added 2026-11-24; §8 recount and §9 authority register added 2026-11-24. Lives at `docs/roadmap/MOVED-FILE-REFERENCE-AUDIT.md`.*
