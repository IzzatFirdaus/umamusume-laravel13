# Trainer Desk — Implementation Roadmap (regenerated 2026-11-16)

Supersedes `implementation-roadmap-2026-11-16.md`, whose P0–P5 slices are all closed. This document
covers **only work that still exists**. Every completed item is excluded with the evidence that
excludes it, so no executor re-opens a closed slice.

Paired plan: `docs/plan/project-plan-2026-11-16.md`. The roadmap states *what* remains and *why*; the
plan states *how*, with commands.

---

## 0. Measured current state

Measured on `HEAD 3cbbd8b9` (2026-11-16). Line counts are file counts, taken by `ls`/`find`.

| Dimension | Measured | Command |
| --- | --- | --- |
| Version | `3.0.0`, pre-release, untagged | `VERSION.md:3` |
| Tags shipped | `0.1.0`, `2.0.1`, `2.0.2`, `2.1.0`, `2.1.1` | `VERSION.md:3` |
| PHP feature test files | 257 | `ls tests/Feature/**/*.php \| wc -l` |
| PHP unit test files | 13 | `ls tests/Unit/**/*.php \| wc -l` |
| Browser specs | 126 | `ls tests/browser/*.spec.ts \| wc -l` |
| Vue pages | 58 | `ls resources/js/pages/**/*.vue \| wc -l` |
| Vue components | 119 | `find resources/js/components -name '*.vue' \| wc -l` |
| Migrations | 80 | `ls database/migrations/*.php \| wc -l` |
| ADRs | 30 (`0001`–`0030`) | `ls docs/adr/0*.md \| wc -l` |
| Register headings | 114 | `grep -cE '^#{2,3} KI-' KNOWN-ISSUES.md` |
| Register body-open | 17 | parse each entry body for `OPEN`/`ACKNOWLEDGED`/`PROPOSED` |
| Workflows | 3 | `.github/workflows/` |
| Framework | Laravel 13.42.0 | `composer.lock` |

**Known measurement hazards.** `phpunit.xml` forces `DB_DATABASE=:memory:`, so a green PHP suite proves
nothing about `database/database.sqlite`. The browser suite re-seeds its scratch DB per invocation, so a
retry costs ~23 minutes. `npm run build` writes lowercase colour literals into `public/build/assets/*.css`;
read those with `grep -i`.

---

## 1. Completed work — excluded, with evidence

Do not re-open any row below.

| Slice | What it was | Excluding evidence |
| --- | --- | --- |
| P1.1 | 14 MWG files banked | commit `178a9079`; typecheck 0, build 0, cockpit-forms 14/14 |
| P1.2 | BO-011 dark risk token + focus ring | token `#FF6F3B` in `resources/css/app.css:531`; `DesignTokensTest` 17 passed |
| P2 | Dependency advisories | framework 13.42.0 / http-foundation 8.1.11; `composer audit` 1 (unreachable `symfony/yaml`); `npm audit` 0 |
| P3.1–P3.7 | Suite-truth fixes | each landed by peer commits `44px` button, `f8e428d9`, `veterans-transfer-requests.spec.ts` |
| P3.8 PHP | Level-3 sweep | 1 failed / 2435 passed at pinned `aefd6549`; the one failure was `DocCitationParityTest` 716 vs 708 |
| P4.1 | Register reconciliation | worktree 39 status-line / census 53 body-open, duplicated ids named |
| P5.3 | `QWEN.md` landed true | commit `3cbbd8b9`; census 716 → **691** ≤ baseline 708; `DocCitationParityTest` 2 passed |
| P5.5 | BO-004 dark `To do` pill | `--color-risk: #FF6F3B`; ratios 4.566 / 5.187 / 7.190 all ≥ 4.5:1 |
| R1 (KI-101) | Refresh navigation count | `tests/browser/navigation-determinism.spec.ts:75-95` already classifies the tracker artifact and asserts `toEqual(['reload'])` |
| R2 (KI-128) | `Delete career` placement | `resources/js/pages/Career/Cockpit.vue:1230-1237` sits outside the race `<section>` that closes at `:1224` |
| R4 (KI-132) | Build-target payload contract | partial payloads are refused at `app/Http/Requests/StoreBuildTargetRequest.php:69-71` ("all or nothing"); the two-key split already ships at `BuildTargetController::store():172-178` with caps as run columns (`ADR-0025`) |
| R6 (P5.1) | `/api/v1/skills` | `PRD.md:296-299` FR-E E-1 names "catalog and runs" only; recorded terminal in `ARCHITECTURE.md:327` |
| R7 (P5.2) | NFR-19 diagnostics | recorded terminal in `ARCHITECTURE.md:554` |
| R8 (KI-104) | ADR-0027 turn/decision logging | zero hits for `decision_log`, `turn_log`, `turn_and_decision` in `app`, `config`, `database/migrations`; ADR stays Proposed |

---

## 2. What actually remains

Four bodies of work. Ordered by dependency: **A** unblocks **C**; **B** is independent; **D** last.

```
A (register truth)  ──┐
B (browser truth)   ──┼──> C (release bar) ──> D (tag)
                      │
E (scenario walks) ───┘
```

---

## Phase A — Register truth: 17 open entries become verdicts

**Goal.** No `OPEN` entry survives without either a landed fix or a recorded terminal classification.
**Why now.** P7.1 condition 7 requires the ledger to name every finding's terminal state.

Each entry is one of three verdicts: **FIX** (code lands), **TERMINAL** (recorded decision, no code),
**ALREADY-FIXED** (verify, then close).

### A.1 — Verify first, then bucket (blocking, ~30 min)

Measure each entry against HEAD before assigning a verdict. Three are already known-fixed:

| Entry | Verdict | Evidence |
| --- | --- | --- |
| KI-152 Catalog Show omits `base_stats`/`growth_rates` | **ALREADY-FIXED** | `resources/js/pages/Catalog/Show.vue:44-46,150-152` reads and renders both |
| KI-144 `Career tools` still hosts write surfaces | **ALREADY-FIXED** | `Cockpit.vue` has 0 live disclosures; `ConfirmDelete.vue` is its own door (C6b.3) |
| KI-148 Four Form Counted rows in dev DB | **TERMINAL** | its own closure says purged by direct delete, no code change |
| KI-140 `FilterContractTest` page size | **ALREADY-FIXED** | `FilterContractTest` no longer exists in `tests/` |
| KI-151 `--text-kpi` type drift | **ALREADY-FIXED** | `--text-kpi` has 0 hits in `resources/css` |

The remaining 12 need the same one-command probe before they are assigned. Do not assume.

### A.2 — FIX bucket (code lands)

| Entry | Defect | Target files | Acceptance |
| --- | --- | --- | --- |
| KI-137 | `full-career-journey.spec.ts` has three stale/ambiguous assertions | `tests/browser/full-career-journey.spec.ts` | spec passes isolated; its own `:114`/`:128` fix text followed |
| KI-136 | same spec, locator matches two disclosures | same file | locator disambiguated; peer-owned file — coordinate before touching |
| KI-141 | Build target step: two `title`-only reasons | `resources/js/pages/Career/BuildTarget.vue` | reasons visible as text or `AbsenceValue`, not `title` alone |
| KI-159 | deck step: six `title`-only reasons | `BuildTarget.vue`, `SupportSlot.vue` | same pattern as KI-141 |
| KI-143 | `review-detail.spec.ts` describes an unrendered variant | `tests/browser/review-detail.spec.ts` | spec header states what is rendered, or variant is rendered |
| KI-147 | `career-race-strip.spec.ts` walks a strip the cockpit stopped rendering | `tests/browser/career-race-strip.spec.ts` | spec matches the current cockpit |
| KI-160 | Cockpit overflows 88px at 640px | `resources/js/components/Career/NextDecision.vue` | no horizontal overflow at 640px, spec asserts it |
| KI-145 | `lint:ui` reports 282 arbitrary values vs 219 baseline | `tools/ui-lint-baseline.json` | either debt paid down or each new value enumerated with a reason |

### A.3 — TERMINAL bucket (recorded, no code)

| Entry | Terminal classification | Where recorded |
| --- | --- | --- |
| KI-150 page-level sticky unavailable | **CONFIRMED-terminal, option 3** (no page-level sticky). `AppLayout.vue:615-618` states `@container` is load-bearing for 21 files and "Do not remove it to make an element stick"; `StickyFooter.vue` has 0 consumers; the one bitten case was removed in pass 5 | `KNOWN-ISSUES.md` KI-150 State line |
| KI-142 pager range wordings + dash form | **TERMINAL** — duplicate block at `:4621`; second copy must be deleted | `KNOWN-ISSUES.md` |
| KI-149 `.card > h2` 15→16px, 13 screens unmeasured | **TERMINAL** pending measurement pass, or **FIX** if the pass is taken | `KNOWN-ISSUES.md` |

**Acceptance for Phase A.** Every one of the 17 entries carries exactly one of `FIXED`, `TERMINAL`,
or `ALREADY-FIXED` plus the command evidence. Duplicate blocks (KI-142 appears twice) collapse to one.

---

## Phase B — Browser truth: 15 failing specs

**Goal.** The browser suite fails on zero specs, or on a small named set with filed reasons.

The sweep at `HEAD 3cbbd8b9` produced **38 failed tests across 15 unique specs**. The sweep was killed at
5007 s because it re-seeds the scratch DB per retry (~23 min each) and the classification was already
decidable from the error contexts it had written.

### B.1 — Triage gate (per spec, one grep)

The primitive is one `grep` of the spec's asserted literal against `resources/js`. Three outcomes:

| Outcome | Action |
| --- | --- |
| Literal **absent** from `resources/js` | spec is stale; rewrite the assertion or delete the case |
| Literal **present** in `resources/js` | failure is not stale-assertion; flag and re-run isolated |
| Ambiguous | file in `KNOWN-ISSUES.md` with the triage evidence; leave untouched |

**Already triaged — literal absent, spec is stale:**

| Spec | Asserted literal | Proof |
| --- | --- | --- |
| `_screens-indep-training.spec.ts:51` | `getByRole('group', {name: 'Career mode'})` | `grep -rn "Career mode" resources/js` → 0 hits |
| `career-preflight-indep.spec.ts:57` | same | same |
| `accessibility.spec.ts:110` | `summary` hasText `Facts and mechanics` | `grep -rn "Facts and mechanics" resources/js` → 0 hits |

The authoritative counter-spec is `career-preflight.spec.ts:299-305`, which asserts these surfaces are
**gone** ("Phase E2 cut … `Career mode` `toHaveCount(0)`"). **That spec must not be touched** — it is
the record of the decision, and deleting it would delete the evidence.

**Still to triage — literal asserted, needs isolated re-run:**

`career-deck-summary.spec.ts` (6), `career-trainee-select.spec.ts` (6), `career-legacy-deck-steps.spec.ts`
(4), `career-profile.spec.ts` (8, all timing out in `beforeEach` = likely contention),
`career-surface-cutover.spec.ts` (2), `career-deck-select.spec.ts` (1), `career-race-planner.spec.ts` (1),
`career-race-strip.spec.ts` (1), `career-scenario-select.spec.ts` (1), `career-snapshot.spec.ts` (1),
`catalog-detail.spec.ts` (1), `catalog-to-wizard.spec.ts` (1).

`career-profile.spec.ts` failing in `beforeEach` on all 8 tests is the signature of a shared-fixture
timeout, not eight independent defects. Re-run it **first** — one run may clear all eight.

### B.2 — Isolated re-run discipline

One process at a time, session-private port and DB:

```bash
PLAYWRIGHT_PORT=8191 \
PLAYWRIGHT_SCRATCH_DB=.scratch-uma/<session>/browser-8191.sqlite \
npx playwright test tests/browser/<spec>.spec.ts --workers=1 --reporter=line
```

Classify each result: **regression** (persists, name the SHA range), **contention artifact** (passes
isolated), **fixed between sweep and HEAD**, or **stale spec** (literal absent from the app).

**Acceptance for Phase B.** Every one of the 15 specs is in exactly one state: rewritten, deleted,
flagged with an isolated re-run result, or filed. `career-preflight.spec.ts` untouched. Failure count
drops to zero or a small named set with reasons.

---

## Phase C — Release bar (`v3.0.0` exit)

Seven conditions, each quoted with its gate command.

| # | Condition | Command | Current state |
| --- | --- | --- | --- |
| 1 | PHP suite green, no unexplained skips | `php vendor/bin/pest --compact` | pending re-run at HEAD |
| 2 | Browser suite green on session-private harness | `npx playwright test` with `PLAYWRIGHT_PORT` + `PLAYWRIGHT_SCRATCH_DB` | **blocked by Phase B** |
| 3 | Style, types, build, static analysis | `vendor/bin/pint --test --format agent <paths>`, `npm run typecheck`, `npm run build`, `vendor/bin/phpstan analyse --no-progress --memory-limit=1G` | pending |
| 4 | WCAG AA on every token pair, carve-outs named | `php vendor/bin/pest --filter=DesignTokensTest` | **green** — 17 passed (480 assertions), ratios 4.566 / 5.187 / 7.190 |
| 5 | Clean clone + install renders artwork; offline placeholder shows | `composer setup`, then load a card detail with the mirror empty | pending |
| 6 | `QWEN.md` contradicts nothing | manual read against the tree | **green** — corrected at `3cbbd8b9`, seven facts fixed |
| 7 | BO/KI ledger names every finding's terminal state | `KNOWN-ISSUES.md` parse | **blocked by Phase A** |

Skip census to quote in condition 1, measured at `3cbbd8b9`: **2 PHP skips**, both `markTestSkipped`
(`grep -rnF "markTestSkipped" tests/Feature tests/Unit` → 2 hits; `->skip(` → 0) —
`tests/Feature/DesignTokensTest.php:651` (Playwright driver absent) and
`tests/Feature/SkillAutomationTest.php:12`. Plus **11 browser data-conditional guards**
(`test.skip(true, …)`) across 8 files.

`phpstan` needs `--memory-limit=1G`; `composer analyse` omits it.

---

## Phase D — Version truth and tag

| Slice | Work | Acceptance |
| --- | --- | --- |
| D.1 | `VERSION.md` and `CHANGELOG.md` agree on `3.0.0` untagged pre-release | declared version, tag list and changelog sections all name `3.0.0` and the five shipped tags; `3.0.0` stated as untagged |
| D.2 | Tag request presented to the owner with Phase C evidence | owner cuts the tag; the agent does not |

**Stop here.** The agent does not cut tags.

---

## Phase E — Scenario walks (independent of A–D, gates nothing but condition 7 completeness)

Three scenarios, in order, each a manual walk with a strict evidence standard.

**Evidence standard — one line per observable event:** *what happened / intent / expected vs observed /
evidence*. A screenshot without a value caption is not evidence. When the evidence is unclear, write
**unverified** and name what is needed. Never write "pass" without a reading.

| Slice | Scenario | Why this one | Status |
| --- | --- | --- | --- |
| E.1 | **Trackblazer** | largest untested surface; the only scenario with no prior Unity Cup cross-reference | pending |
| E.2 | **Our Grand Concert** then **URA Finale** (control) | second-most complete; URA Finale as the control that must not regress | pending |

A defect found on a walk goes into `KNOWN-ISSUES.md` as a reading. **Code changes and walks do not share
a turn.**

**Host-limited QA, recorded once, never forced:** ProvenanceMark screen-reader double-read (no
NVDA/VoiceOver on this host); physical mobile keyboard check (no device); Safari find-in-page/paint
(property not in the tree — closed with no code change).

---

## 3. Roadmap laws

1. **No slice is closed on assertion.** A slice closes on command output quoted in the hand-off.
2. **A recorded owner ruling is terminal.** Reversing one requires a new written ruling; a later ruling
   that contradicts it does not silently supersede it (this is why 6bB and 8B were abandoned).
3. **The baseline is never raised to pass.** `DocCitationParityTest.php:26` forbids it; the fix is making
   the docs true.
4. **`career-preflight.spec.ts` is the record of the Phase E2 cut.** It is not a stale spec; the specs
   that contradict it are.
5. **Never `pint --dirty`.** The dirty set is not your slice on a shared worktree.
6. **One Pest process at a time.** Overlapping runs hang rather than error.
7. **Private index for every commit.** Peer files are staged intermittently; `git add -A` is a
   commit-sweep hazard.
8. **Stale evidence is not evidence.** `HEAD` moves mid-session; re-derive the SHA before attributing any
   count to it.

---

## 4. Progression guarantee

Each phase ends in a state that unblocks the next, and each acceptance test is a command whose output is
the evidence:

- Phase A ends with 17 verdicts → condition 7 satisfiable.
- Phase B ends with a classified failure set → condition 2 satisfiable.
- Phase C runs seven gates → a green ledger.
- Phase D records version truth → the owner has something to tag.
- Phase E produces readings, not reports, so the release claim rests on the UI actually saying what the
  specs assert.

No phase produces documentation for its own sake. Every slice above changes code, changes a spec, changes
a register verdict, or measures a gate whose result gates the tag.
