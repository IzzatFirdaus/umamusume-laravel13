# Consolidated UI/UX and remaining-work instruction set — 2026-11-18

**Status:** proposal, not a slice. Nothing here authorises a commit; the owner names pathspecs.
**Date:** 2026-11-18. **Measured at:** `HEAD 816e923e`, branch `master`, **8 behind / 38 ahead** of `origin/master`, 75 modified + 23 untracked + 1 deleted working-tree rows.
**Method:** every planning document in the repo was censused (497 `.md` files), every claim that states remaining work was extracted, and **every open claim was re-measured against the live tree the same day**. Provenance per item: `[M]` = command output this pass; `[S]` = static source read; `[X]` = claim tested and **refuted** — the work is already done and must not be re-done.
**This file supersedes the *ordering* role of all prior roadmaps; it does not edit them.** Peer-owned working-tree files are named, never adopted.

---

## 1. Source census — what was read and what survived

| Corpus | Files | Surviving value |
|---|---|---|
| `docs/roadmap/*.md` (10) | read all | `remaining-work-roadmap-2026-11-17.md` phases 0–5 plus `MASTER-IMPLEMENTATION-ROADMAP-2026-11-18.md` S0–S5 carry the live slices; the rest are superseded history |
| `docs/plan/*.md` (13) | read all | the four 11-17 plans (tree-truth / truth-gates / defects / capability-release) carry per-slice steps and commands |
| `docs/plans/*.md` (6) | read all | `remediation-plan-2026-11-15.md` items all landed or ruled; no live slice |
| `docs/proposals/*.md` (5) | read all | scenario plans contribute finals/team-race items; `remaining-implementation-2026-11-17.md` contributes the owner-decision sheet |
| `docs/audits/` UI/UX corpus | read all | `UI-UX-PRODUCT-AUDIT-2026-11-18.md` §6/§8, `uiux-comprehensive-walk-2026-11-18/01+04+06`, `browser-observation-2026-11-14/15` |
| Root registers | `KNOWN-ISSUES.md`, `PRD.md`, `VERSION.md`, `CHANGELOG.md`, `SCREEN_SPEC.md` | index-OPEN set, NFR deferrals, release conditions |
| Everything else (`docs/research-scratch/`, `docs/research/`, `.gemini/`, `.headroom-env/`, …) | 380+ files | research record and agent scaffolding; no slice survives measurement |

**Extraction artifact:** `.scratch-uma/uiux-consolidate-2026-11-18/candidates.md` (extract-only, unverified claims with source:line each).

---

## 2. Already done — do not re-plan these `[X]` refuted claims

Each row below is a claim a planning document still states as open, tested against the tree on 2026-11-18.

| Claim still in a plan | Measured reality | Proof |
|---|---|---|
| Phase 0 lockfile drift (`npm ci` fails) | **Fixed.** `f82b6bd0` landed Inertia v3; lock agrees | `npm ci --dry-run` exit 0 `[M 2026-11-17 roadmap §0.2]` |
| Three untracked build inputs (`polyfills.js`, `config/inertia.php`, `Sparkline/StatRing.vue`) | **Tracked** | `git ls-files` names each `[M]` |
| `pre-native-build` script removed | **Restored**; `composer run pre-native-build` exit 0 | `composer.json:62-64` `[M]` |
| `jev-inspect/` deletion decision | **Committed** `e5fc0e1c`; do not restore | `git ls-tree -r HEAD -- jev-inspect` → 0 `[M]` |
| `win-unpacked/` unignored | **Ignored** at `.gitignore:92` | `git check-ignore -v` match `[M]` |
| KI-166 59 px overflow open | **CLOSED 2026-11-18** at `816e923e` (`816e923e` = today's HEAD); second-pass CSS rule `:where(.card,[class*="overflow-x-auto"]):has(table)` closed the `/legacy` subclass too; 48-route 390 px sweep = 0 overflow | `git show 816e923e --stat`; register row CLOSED `[M]` |
| KI-165 fan ladder one band | **CLOSED**; 7 sourced bands in `config('uma.fan_ladder')`, test re-pinned 22 passed | register row `[M]` |
| KI-163 goal-row duplication, KI-164 positional assertions | **BOTH CLOSED 2026-11-18** (shared type; keyed `reviewField()` helper) | register rows `[M]` |
| Six index/body register contradictions | **Reconciled**; `audit_status_register.py` reports clean | roadmap §1.2 `[M]` |
| Audit rec 1 (KI-160 overflow), rec 2 (global reduced-motion), rec 3 (mobile nav bands), rec 5 (register 9 routes) | **All landed** | `app.css` rule present `[S]`; `AppLayout.vue` `inBar`/`barItems` split `[S]`; all nine route ids in `SCREEN_SPEC.md` `[S]` |
| Audit F-01 mobile nav flat strip / F-03 no reduced-motion | **Resolved** by the audit's own §11 erratum | `docs/audits/UI-UX-PRODUCT-AUDIT-2026-11-18.md` §11 `[M]` |
| NativePHP spec in default sweep, `career-preflight-indep` drives removed controls, fence-tool crash, no gate script | **All closed** (2.1/2.2/2.4/2.5) | `playwright.config.ts` `testIgnore`; spec asserts absence by design; `2546689e`; `tools/gates.sh` + `composer gates` `[M]` |
| Full-career-journey spec holds the tag with a `details[data-region="team-state"]` fix | **False premise** — the file contains no such locator and no `/settings` nav | grep 0 hits `[S]`; `VERSION.md:9` row is the false text |
| 4.6 "no per-ancestor guest flag" | **Half false** — `is_guest` exists end-to-end (request rule `StoreLegacySelectionRequest.php:113`, payload `LEGACY_KEYS`, UI checkbox `LegacySelect.vue:565`). The genuine residual is **per-ancestor identity only** (`AncestorEditor.vue` has no per-ancestor label input) | grep `[S]` |
| ADR-0036 Japanese search key never back-filled | **Column is complete**: 0 null `match_key_ja` in the dev DB | `Skill::whereNull('match_key_ja')->count()` → 0 `[M]` |

---

| A-11 portrait consolidation owed (PRD Phase 5.4) | **Landed**: `Umamusume::defaultForm()` exists | `[S: app/Models/Umamusume.php]` |
| `veteran_label` audit stamp (remediation-plan §2.2) | **Landed** in `2026_11_15_131115_fix_transfer_requests_table` | grep `[S]` |

## 3. Remaining work — execution-ready, dependency-ordered

Verdicts: **CODE** = build it; **SPEC** = test-only change; **DOC** = one dated register/docs write; **OWNER** = blocked on a human decision; measured evidence beside each.

### Phase A — Land the shared working tree (blocks every code phase)

**A1 [CODE]** Commit the uncommitted product batches as reviewed series, one concern per commit, explicit pathspecs only.

- Batch 1 (UI/UX accessibility, this session's verified work): `resources/js/components/ui/Tile.vue`, `resources/js/pages/Career/Preflight.vue`, `resources/js/pages/Career/ScenarioSelect.vue`, `resources/css/app.css`, `tests/browser/dashboard.spec.ts`, new `tests/browser/browser-walk-2026-11-18.spec.ts`, `tests/browser/navigation-focus.spec.ts`, `tests/browser/audit-followup-2026-11-18.spec.ts`, plus the five files `MASTER` §S0.1 lists (`spa.ts`, `Timeline.vue`, `Dashboard.vue`, `Review/Index.vue`, `TransferRequests.vue`). All are build-verified and spec-green at ports 8451–8459 `[M]`.
- Batch 2 (gate): `tools/gates.sh` (untracked), `composer.json` `gates` script. Read the script before committing: `MASTER` S0.2 requires the pint step to take explicit paths and three added steps (`adr-check`, `doc_census`, `check_untracked`); verify against the file, then repair in the same commit if the untracked draft predates that requirement.
- Batch 3 (ADR drafts + docs): `docs/adr/0031..0036`, `docs/roadmap/{INDEX,MASTER-IMPLEMENTATION-ROADMAP,RESIDUAL-VERIFICATION}-2026-11-18.md`, `docs/audits/uiux-comprehensive-walk-2026-11-18/`, `docs/audits/mwg-audit/mwg-verified-research-2026-11-18.md`, this file. Commit the **deletion** of `docs/research-scratch/INDEX.md` only after diffing its heading list against the new `docs/roadmap/INDEX.md` (MASTER S0.3).
- **Do not stage blind:** `Preflight.vue` (+14) and `ScenarioSelect.vue` (+22/-1) are this session's work and carry the whole-card hit-area + preflight-hint changes; `KNOWN-ISSUES.md` (+22/-7) carries KI-166 closure + KI-167 filing — attribute per hunk against peer edits in the same file before staging.

**A2 [SPEC]** Clear the two empty-diff ` M` rows (`career-trainee-select.spec.ts`, `wizard-flow.spec.ts` — byte-identical to HEAD, stat-only): `git checkout -- <both>` after confirming `git diff --numstat` empty and 0 CRLF. Then `LineEndingGuardTest` green. `[M: identical-bytes measured this pass]`

**A3 [DOC + OWNER]** Reconcile `master` with `origin/master` (merge default; rebase needs owner ruling), then push the 38 local commits on a peer-clean tree. Acceptance: `git rev-list --left-right --count origin/master...HEAD` → `0 0`; `npm ci --dry-run` exit 0 on a fresh clone. `[M: currently 8/38]`

### Phase B — Suite truth (unblocks every verdict)

**B1 [CODE→SPEC]** **KI-167** `career-scenario-select.spec.ts:147` fails at HEAD (proved by HEAD-revert A/B this session). Root-cause the harness-vs-component question; add the three uncovered cases KI-167's register note names (mouse selection, arrow-key navigation, slow PUT). Fix the harness or component — never the assertion.

**B2 [X→re-scoped] KI-114 is 31/32 green measured, not open.** Ran both files on the session-private harness (port 8462, fresh build): `support-deck.spec.ts` **12/12 passed**, `career-legacy-deck-steps.spec.ts` **19/20**. The `#deck-picker-results` locator is legitimate — it lives on the **run-scoped** `Support/Builder.vue:497`, which still renders `name="type|rarity|status|query"` (`:437-455`), not on the retired wizard picker the register entry named. The one red case is not the deck picker at all: `career-legacy-deck-steps.spec.ts:387` expects the server's spark-refusal sentence, but `BaseSelect` re-emits `change` since `282e5437` (2026-11-12, one day after the spec was last touched at `a58e4942`), so the editor's `@change="spark.target = ''"` now clears the stale target the test engineered, the validator returns early on the empty target, and the error node never renders. **The fix is the spec** — drive the stale-target state through the component's new emit contract (or assert the reset behaviour directly) — plus a dated KI-114 note recording the 31/32 and the mechanism. No product change.

**B3 [SPEC]** **page-header.spec.ts** asserts `font-extrabold`/`text-[28px]` against a component that ships `text-3xl font-semibold text-ink-strong` (`PageHeader.vue`, `DESIGN.md` §2.2 amendment). Update the assertions; owner approval for the spec edit recorded in the commit message. `[M: 1 stale assertion line measured]`

**B4 [SPEC→CODE-question] three failing LegacySelect cases re-scoped.** B2's run measured the other two named failures as **green** (`:269` save-focus, `:740` combobox clear) — only the spark-error case fails, and its mechanism is B2's. The `#legacy-spark-target-0-0-error` element exists (`SparkTargetPicker.vue` renders `${id}-error` on `props.error`) `[S]`; it is absent because no error is produced once the target resets. Fold this case into B2's spec repair.

**B5 [CODE]** **KI-127** `TeamRaceFacilityPanel.vue:54` — the panel root already carries `card p-3` (`:51` measured `[S]`, so the register claim is **stale**); the live gap is the missing capsule header/sibling parity. Re-measure in the browser first; if the panel now matches its siblings, close the row with the reading instead of changing markup.

**B6 [CODE]** **Citation ratchet KI-106**: 808 dead links vs baseline 708 `[M: counted this pass]`. Worklist by dead-target frequency (top: `rice-shower-unity-cup-ux-walk.md` ×16, `docs/audit-corrections.md` ×13, `RECONCILIATION-REGISTER.md` ×13, `provenance-and-absence-contracts.md` ×12, `SKILLS-GAPS.md` ×11). 53 of 808 are agent-scaffolding citations (`SKILL.md`/`.claude`/`.hermes`) that may warrant an `EXEMPT_PREFIXES` entry **by owner ruling** rather than 53 repoints. Repoint only lines a reader would follow; never raise the baseline; target ≤708.

**B7 [SPEC]** **Probe-spec ruling (P-5, open)**: retire the five `flash-name-*` specs + `containment-mechanism.spec.ts` (6 cases measured `[M]`), keep `containment.spec.ts`. Owner has pre-approved the shape; needs the ruling quoted in the commit.

**B8 [SPEC]** **Full browser sweep** at the landed HEAD, session-private port + absolute `PLAYWRIGHT_SCRATCH_DB`, fresh `public/build`, one worker, quiet box (`php tools/gate-preflight.php` first). Acceptance: every failure classified (product / spec / harness) or filed. `[not run this pass — the one measurement gate still missing]`

**B9 [SPEC]** **Full Pest baseline** on the quiet box; classify every red; settle KI-162 (intermittent) with a recorded verdict. `DocCitationParityTest` red is expected until B6.

### Phase C — Product defects and UI/UX gaps with a named site

**C1 [CODE] Team Rank writer (4.1)** — column, `TeamRankPayload::LETTERS`, five readers and `TrainingRun::saving` guard all exist; **no request rule and no UI write path** (`grep team_rank app/Http/Requests` → 0 `[M]`; `UnityCupEditor.vue:226` still points at a turn-event path no UI reaches — `TurnEdit` has zero team refs `[M]`).

- `StoreTrainingRunRequest`: `'team_rank' => ['nullable', new Rule::in(TeamRankPayload::LETTERS)]` with the S+ exception (legal letter, no ladder level) stated in the message.
- `UnityCupEditor.vue`: one select bound to the form, and **delete the :226 sentence**. Note: `MASTER` records a conflict with earlier ruling R2 ("editor must have no rank input") — that ruling predates the column's landing; the owner confirms the flip (D-sheet).
- Feature test writes through `runs.update`; Cockpit strip + Dashboard tile read it back; invalid letter refused.

**C2 [DOC] Unity Cup finale (4.2)** — measured **terminal, not code**: `config/scenarios.php` `unity_cup` (lines 416–672) has no `finale` key `[M]`; `Unity-Cup-plan.md` item 12 records the owner ruling that no client capture exists, so **no config entry is added**. The deliverable is a dated `KNOWN-ISSUES.md` note recording the terminal absence so the roadmap row stops reopening. `finaleAbsence()` (`ScenarioSectionBuilder.php:281-298`) already discloses the absence correctly.

**C3 [CODE] Per-ancestor identity (4.6, re-scoped)** — guest flag is done `[X]`; the residual is a distinguishing label for same-named ancestors.

- `LegacySelectionPayload::LEGACY_KEYS` gains `label` per ancestor (or reuse the existing-but-unread `ancestors_meta` — read `:52`, `:249` first).
- `AncestorEditor.vue`: one text input beside rank/stars (`min-h-11`, existing field pattern; component has no `name=` inputs at all today `[S]`).
- `StoreLegacySelectionRequest`: `'legacies.*.ancestors_label.*' => ['nullable','string','max:255']`.
- Round-trip test: two same-named ancestors, distinct labels, survive save + reload. PRD §6.3 permits recording, not computing.

**C4 [UI/UX, CODE] The four unmeasured static candidates** (`06-static-candidates.md`) — all four re-verified as still present this pass `[S]`:

- **C-05** `SparkSummary.vue` star-count absence is `title`-only (`<span v-else title="No star count was recorded…">N/A</span>`): pair with a visible sentence per the `Tile.vue`/DESIGN §5 rule, or an `AbsenceValue` slot.
- **C-06** `RacePlanner.vue:351/381` — both wide tables scroll in `overflow-x-auto` with **no `role="region"`/`tabindex`/label** (0 hits `[S]`). Same fix as `Timeline.vue:259` (BO-002): region + name + tab stop. 11 further table-scrollers across the app lack the region (`[S]` inventory: CareerStatePanel, NextDecision, CardCompare, SkillsPlanner, Legacy/Index, Review/Index, Runs/Index, Skills/Show, SupportCards/Index ×2, Veterans/Index) — one sweep, one pattern, one spec.
- **C-08** `RacePlanner.vue` 9 `:title=`-only `N/A` reasons — same named-absence rule as C-05.
- **C-09** `TransferRequests.vue` dates via `new Date(value).toLocaleDateString('en-US', …)` — house rule is explicit formatting, not browser-locale-dependent output; route through the existing formatter helper.

**C5 [UI/UX, CODE] Audit §6 unmeasured register → measured** — the seven claims the audit refused to assert (contrast both themes, focus visibility, 2.5.8 targets, 1.4.10 at 320, loading/error frames, reduced-motion emulation, 2.5.7 drag alternatives, 3.2.6 help order, 3.3.7 draft non-repetition, error views). One instrumented browser pass (contrast probe + keyboard trail + `getBoundingClientRect` sweep + `emulateMedia({reducedMotion})`) closes the measurable rows with dated artifacts; the rest are explicitly retained as unmeasured. Loading/error coverage today is 6 specs touching loading and none forcing an error view `[S]`.

**C6 [DOC] KI-104/106/114/116/119/127 register closeout** — the index-OPEN six. 106/114/116/119 close on B6/B2/`forLevel()` evidence/`KI-133` supersession with dated notes; 127 closes or narrows on B5's re-measurement; 104 narrows to its ADR half only. Register tool `audit_status_register.py` currently lives in a skill dir, not `tools/` — vendor it or record the substitute command `[S]`. Also merge or cross-reference the six duplicate body blocks (KI-70/71/101/102/103/104; 117 body headings → 110) by **retitle-and-point, never delete**.

**C7 [DOC] Dangling citations in authority docs** — `AGENTS.md:195` → absent `docs/SKILL_AUTOMATION.md`; `PRD.md` §7 → absent `docs/OPEN-QUESTIONS.md` (`[S]`, both still live). Repoint to the owning file or fold with a dated note; census must not rise.

### Phase D — Release bar

**D1 [DOC] `VERSION.md:9`** states two false things: the journey-spec tag blocker (refuted, §2 above) and "only 0.1.0 tagged" (five tags exist `[M]`). Correct both with dated notes; duplicate sentence at the row end deduplicated.

**D2 [DOC] `CHANGELOG.md`** — one `[Unreleased]` heading is correct now (`:14` + the retired second at `:79` `[M]`), but zero entries name the S1 snapshot pipeline, the S18 batch, the fan ladder, Inertia v3, or the accessibility batches (`grep S1|S18` → 0 `[M]`). Add entries under Keep-a-Changelog order.

**D3 [SPEC] Seven release conditions quoted** — pest, browser sweep (B8), pint/phpstan/typecheck/build, WCAG token pairs, clean-clone artwork render, QWEN truth, BO ledger terminals. This pass added: build/typecheck/`tsc`/lint:ui(516, unchanged)/targeted browser runs all green `[M]`; full pest and full sweep still unquoted.

**D4 [OWNER] ADR signatures** — 0022, 0023, 0027 (KI-104's other half), 0031 (maiden-lock inference), 0032 (Independent Training door), 0033, 0035 (hint-level copy wording), 0036. `adr-check.py` → 0 findings after 0026/0029 word-cap trims. Then the owner cuts `v3.0.0` — the agent never tags.

### Phase E — Owner-gated capabilities (recorded, not built until ruled)

| Capability | Measured state today | Unblocks |
|---|---|---|
| Team-race slots | No source package; `ScenarioSlotSeeder.php` has no `team_race` row `[S]`; `StoreRaceEntryRequest.php` validates circles against a slot no code creates `[S]` | D-11: citable source or terminal note |
| Roster sub-model / preseason per-row | Derived read-only; both DEFERRED by R8 | D-12 + ADR |
| `match_candidates` entity discriminator | **304 rows, all Pending, `match_tier` NULL** `[M: dev DB]` | D-8 ADR → review queue |
| `data_sources` provenance | **133 rows, 1 distinct `source_key`** vs 13 configured sources `[M: dev DB]` | D-9: backfill or terminal gap |
| Independent Training UI door | Backend complete (`Lifecycle::start()` from `PreflightController.php:194-197`), **0 UI controls, 0 `IndependentTrainingRun` rows** `[M: dev DB + grep]` | ADR-0032 signature → three Preflight controls + tests |
| Hint-level copy | Stale sentences live at `Skills/Show.vue:397` and `:522` `[M: grep]` | ADR-0035 wording → two sentences + one assertion |
| Grand Concert packages S9–S18 | Blocked on evidence acceptance | per-package ruling |

---

## 4. Dependency graph

```
A1 land batches ─┬─> A3 merge+push ─┬─> B8 sweep ─┬─> D3 conditions ─> D4 tag (owner)
A2 empty-diff ───┘                  │             └─> C5 unmeasured register
B1 KI-167 ── B2 KI-114 ── B3 page-header ── B4 legacy cases ── B5 KI-127 ── B6 citations ── B7 probes
C1 team rank ── C3 ancestor label ── C4 UI/UX candidates (independent of A; browser-verifiable now)
C2 unity cup note ── C6 register closeout ── C7 dangling citations (docs-only, anytime)
D1 VERSION ── D2 CHANGELOG (after A1 lands the history they describe)
```

Phase C items C1/C3/C4 are **independent of the landing** and each produces user-visible behaviour on its own; they are the recommended first code work if the owner wants product movement before the merge gate.

## 5. Acceptance for the whole set

1. `git status --porcelain` carries no product file outside named pathspecs after A1; both empty-diff rows gone (A2).
2. Divergence `0 0` and clean-clone `npm ci --dry-run` exit 0 (A3).
3. Browser sweep completes with every failure classified or filed (B8); full Pest with the same bar (B9).
4. KI-167 green with three new cases; KI-114 and page-header specs green (B1–B3).
5. Team Rank writable, readable on Cockpit + Dashboard, invalid letter refused (C1).
6. All 11 table scrollers keyboard-reachable with named regions; C-05/C-08 absences visible without hover; C-09 dates deterministic (C4).
7. `doc_census.py` ≤ 708 with the baseline untouched (B6).
8. `VERSION.md`, `CHANGELOG.md`, `git tag -l` agree about what 3.0.0 is (D1–D4).

## 6. Explicitly not in scope

Roster sub-model, preseason rows, team-race slots, Independent-Training evaluator maths, Grand Concert packages — each owner-gated per §Phase E; no agent code ahead of a ruling. No new dependency. No CI enforcement (R-9 stands). No baseline raise on any gate. No rewrite of a dated record.
