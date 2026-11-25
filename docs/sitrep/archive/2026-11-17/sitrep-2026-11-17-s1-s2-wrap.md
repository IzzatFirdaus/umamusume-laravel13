# SITREP — session S1 + S2 (walk-9 remediation and register truth)

**Date:** 2026-11-17
**Repository:** `D:\Projects\umamusume-laravel13`
**Branch:** `master` (no branch created)
**HEAD at report time:** `be8893a25879acad04935a3aeb47c5c38ed84517` ("docs(register): close Phase A entries")
**Ahead of `origin/master` by:** 15 commits · **Behind by:** 7 commits (so the branch has *diverged*; ahead-only framing hides this) · `origin/master` tip: `969eb560` (Merge PR #26)
**Working tree:** 207 dirty entries (50 modified, 108 deleted, 49 untracked), 6 stashes
**Committed by this session:** nothing. **Staged by this session:** nothing. **Tagged:** nothing.

Every number below is a reading from this tree on this date. Where a figure is an inference rather than a measurement it is labelled as one.

---

## 1. Original objectives

Three separate requests were executed in sequence. Each is recorded because the next agent inherits the obligations of all three.

### 1.1 Objective A — re-run the RS-UC-2026-11 UX walk through Hermes

The governing brief was an instantiation of a 64-probe audit for the Rice Shower / Unity Cup run, with the deliverable at `docs/audits/rice-shower-unity-cup-ux-walk-7.md`, screenshots under `docs/audits/RS-UC-2026-11/screens/`, and a hard constraint that data entry happen **only** through the browser (no seeders, factories, direct DB writes, API substitution, or devtools state manipulation).

The immediately preceding walk-9 had died at screen S01 under **ENV-01**: an embedded browser surface reporting `visibilityState: hidden`, where clicks returned success and no request ever reached the application. The strategic finding of the research report attached to the request was that the failure was in the *substrate*, not the app, and that Hermes `browser_exec` (CDP-driven real Chromium) bypasses it.

### 1.2 Objective B — Phase S1, the walk-9 / Round-8 FIX batch

Execute the FIX batch end to end: probe before assuming, implement, verify, close register rows. Explicit authority order: owner rulings > plan pair > R8 register > walk-9 observations > source run document (SR) > the mechanics reference. Explicit constraint: **no commit, push, or tag without instruction.**

### 1.3 Objective C — Phase S2, register truth

Re-derive the open KI set at the current HEAD, reconcile index against bodies register-wide, publish one denominator, file a KI row for the cross-test failure so the release bar can name it, and render verdicts on the genuinely open entries.

The brief's own framing predicted S2 would be "nearly empty" and warned that the plan's grep might measure the wrong field. **That warning was correct and the prediction was wrong** — see §3.

---

## 2. Objective A — the walk, and what actually changed

### 2.1 What was done

The walk was executed in the parent session with `browser_exec`, not delegated to a subagent. The delegation path was dropped for a measured reason: Hermes documentation conflicts on whether `delegate_task` children inherit the browser toolset, and a walk that silently loses its substrate would reproduce ENV-01 one layer up.

Screens S01–S19 were driven and recorded at the time of encounter, not reconstructed. Evidence per screen was appended to an `evidence.json` as the walk progressed.

### 2.2 Environment deviations encountered (all logged at the time)

| ID | Deviation | Recovery |
|---|---|---|
| **ENV-DEV-05** | Browser-harness daemon crash: `PermissionError [WinError 5]` on the port file after four tab resets | Switched to a fresh session name (`rs-uc-rerun2`), which starts a new daemon |
| **ENV-DEV-06** | The same daemon crash recurring | Retried; resumed by deep-linking to the route |
| **ENV-DEV-07** | Blank tab / empty `innerText` reads mid-reset | Built an `ensure_page()` aliveness loop gating on `document.body.innerText.length > 300`; nothing was saved during a blank, so each screen was re-filled in one call |

**ENV-01 is resolved by substrate change.** No application change was made to fix it, and none is needed.

### 2.3 Method rules banked during the walk

- **Every click is verified by `page_info()` showing a URL change**, never by the click's own success report. This is the direct lesson of ENV-01.
- **A form filled across a fresh navigation loses its URL-param staged picks.** Staged picks must be completed in one navigation.
- **Exact-name or href-scoped selectors in every future walk.** Playwright's `getByRole(name:)` is case-insensitive *substring* matching by default, which is what produced walk-6/9's "Skills" misroute.
- **Presence-in-file is not presence-on-screen**, and presence-only probes produce false verdicts **in both directions**. The state under which a string renders (branch, guard, prop) must be established. This rule came from a specific error: a probe found the snapshot door string at `Dashboard.vue` and narrowed a CONFIRMED finding to "half-fixed"; the string in fact sat inside a `v-else-if` empty-resume branch unreachable with any active career, so the original walk observation had been correct all along.

### 2.4 Deliverable state

- `docs/audits/ux-walkthroughs/rice-shower-unity-cup-ux-walk-9.md` — completed and folded: **661 → 1458 lines**, additions only, LF preserved. Moved from `docs/audits/` and its relative screenshot links repointed to `../RS-UC-2026-11/screens/`; all links resolve, zero dangling.
- `docs/audits/RS-UC-2026-11/screens/` — **34 screenshots**, corroboration only.
- The traceability matrix was found short at 43 grouped rows against a 59-row denominator and regenerated to exactly 59 rows matching the source's §2.2 labels.

Note: `docs/audits/**/screens/` is gitignored by `.gitignore:181`. The screenshots are deliberately untracked.

---

## 3. Objective C — S2, and the correction that matters most

### 3.1 The premise was wrong, and the reason is methodologically important

The brief predicted S2 would be nearly empty because "Phase A drove body-OPEN to 0 at `be8893a2`". Measured: **it had not.** The register carries **three header spellings**:

| Spelling | Cohort | Visible to |
|---|---|---|
| `**State:**` | newer entries (roughly KI-129 onward) | the Phase A grep |
| `**Status:**` | older entries (roughly KI-59 … KI-128) | only a `Status` grep |
| `**Status: OPEN.**` (value inside the bold) | older entries | neither naive grep |

Phase A measured `^\*\*State:\*\* OPEN` and therefore saw **only the newer cohort**. "Body-OPEN = 0" was a property of the regex, not of the file.

**Banked rule: one pattern measures the pattern, not the file.** Any future "is the register clean" check must measure all three spellings together, or it is measuring itself.

**Verbatim wording for the plan pair (owner-requested):**

> A register-status count is valid only if it unions every header spelling the file actually uses. `KNOWN-ISSUES.md` carries three: `**State:**`, `**Status:**`, and `**Status: VALUE.**` with the value inside the bold. A check that measures one of them reports a property of its own regex. The S2 pass measured `0` under `State` and `27` under `Status` on the same file at the same HEAD; both "counts" were true and neither was the answer. Correct procedure: enumerate the spellings first (`grep -nE '^\*\*(State|Status)'`), then count with all of them.

### 3.2 Final register state (verified at report time)

```
index rows : 109
body entries: 109 distinct ids
rows == bodies: True
OPEN       : 10   (9 at S2 close, + KI-165 filed during this review)
closed-family: 99   (CLOSED / RESOLVED / SUPERSEDED / FIXED / TERMINAL / ACKNOWLEDGED / ACCEPTED)
CRLF 5197 | lone LF 0 | strict UTF-8 valid
```

The 10 OPEN entries:

| ID | Why it stays open |
|---|---|
| KI-104 | ADR-0027 reads `Status: **Proposed**` — the fix landed in code (`005561c1`) but the owner's signature does not exist. Signature, not code. |
| KI-106 | docs-citation ratchet; not re-measured this session |
| KI-114 | two browser files; needs a session-private Playwright pair |
| KI-116 | **narrowed**: `DeckAnalysis.php:28-29` contributes a reached value whenever a slot records a level; only unrecorded slots remain |
| KI-119 | **narrowed**: `current_spirit_proxy` is now a typed prop at `UnityCupPanel.vue:87` |
| KI-127 | **confirmed reproducing**: `TeamRaceFacilityPanel.vue:54` still renders a bare `<div class="flex flex-col gap-4">` |
| KI-162 | **new** — the peer-attributed full-suite failure (§4) |
| KI-163 | **new** — four duplicate `GoalRow`/`GoalGroup` interfaces |
| KI-164 | **new** — positional assertion coupling in `SnapshotReviewTest` |
| KI-165 | **new, filed during this review** — the fan ladder renders correctly but `config('uma.fan_ladder')` holds one band of nine; path closed (`27800fa8`), data absent |

### 3.3 Reconciliation performed

| Class | Count | Treatment |
|---|---|---|
| Index said OPEN, body said closed-family with a later marker | 11 | index rewritten, dated correction note on each |
| Index said CLOSED, body said OPEN, **no closure anywhere** | 12 entries / 13 body blocks (KI-71 carries two) | body annotated: the index owns current state, that text is history |
| Body said RESOLVED at a named commit, index said OPEN (KI-154…157) | 4 | index rewritten to RESOLVED with the commit cited |
| KI-104 | 1 | annotated as agreeing — uncommitted fix means the entry is legitimately live |
| Body existed, **no index row** (KI-161) | 1 | index row added |
| KI-97 (index CLOSED, body OPEN with root cause proven) | 1 | annotated with which side wins and what reopens it |

**Result: zero unannotated contradictions; 14 annotated reconciliations.** Every body has a row and every row has a body.

**The authority rule used** is the register's own, stated at its lines 2–4: the index line is the *current state*, the body keeps *filed history*. An index row that disagrees with a body that has no later closure is a defect; one that disagrees with a body carrying a closure is conforming.

### 3.4 Verdicts rendered (each from a measured probe, not a reading)

| ID | Verdict | Evidence |
|---|---|---|
| KI-59 | CLOSED | fixed **by branching, not deletion**: `catalog-detail.spec.ts:271-281` asserts `if (frame.count() === 0) {img count 0} else {alt=""/src/no-anchor}`, so populated and empty mirrors both pass. The surviving `:250` assertion targets an *anchor around* the frame, not the frame's presence. |
| KI-60 | CLOSED | `php artisan migrate:status --no-ansi` → **0 Pending**; **80** migration rows, which equals the **80** `.php` files on disk. The "83" in the earlier S1 handoff counted *lines*, not migrations — resolved, not drifted |
| KI-61 | CLOSED | `career-preflight.spec.ts` no longer drives an empty-body PUT; it waits on the deck PUT at `:109` |
| KI-69 | CLOSED | `playwright.config.ts:18` pins `DB_DATABASE: SCRATCH_DATABASE` from `global-setup`; port default **8123**, not 8127. The file's own comment cites KI-69. |
| KI-107 | CLOSED | `CLAUDE.md:12` records the fold into `GOVERNANCE.md`; `:257` forbids adding `CLAUDE.local.md` |
| KI-117 | CLOSED | migration `2026_10_25_130000_add_card_level_to_deck_slots_table.php` adds `card_level`; `DeckSlot` `#[Fillable]` + casts carry it |
| KI-70 | TERMINAL | accepted process hazard; `AGENTS.md` §9 now carries the rules that answer it |
| KI-74 | TERMINAL | accepted as a class; enforced by the pre-flight in `AGENTS.md` §9 and `tools/gate-preflight.php` |
| KI-100 | TERMINAL | accepted limitation: owner ruling 4a chose on-screen disclosure over inference |
| KI-124 | TERMINAL | accepted limitation per its own body: both halves are migrations needing an ADR |

### 3.5 A correction to the brief's own figure

The brief carried "lint debt 282". Measured:

```
node tools/ui-framework-check.mjs  -> exit 0
516 findings across EIGHT rules:
  literal-length 220 | arbitrary-value 207 | literal-colour 78 | box-shadow 3
  colour-var 3 | radius-scale 2 | outline-width 2 | transition-motion 1
arbitrary-value 207 vs baseline.count 209 -> WITHIN BASELINE
debt-increase finding present: False
```

**The gate is correct.** Rule 8 counts only `arbitrary-value`; 207 < 209, so exit 0 is right, and per `tools/ui-framework-check.mjs:228` a debt increase fails on its own without `--strict`. The 516 is the total across all eight rules, which no baseline governs. Both "282 vs 219" and the earlier "202 vs 209" are stale; the index row now carries the measured figure.

### 3.6 The 114 heading target: it is a heading count, and it already matches

**Corrected after a re-probe.** The first draft of this report claimed "111 headings, 105 distinct ids … arithmetically unreachable". That was wrong on both numbers and on the reasoning. Measured:

```
body headings (with duplicates) : 114
distinct ids                    : 108
range                           : KI-57 .. KI-164
range span (164-57+1)           : 108
gaps                            : 0
duplicate ids (6)               : KI-70, KI-71, KI-101, KI-102, KI-103, KI-104
108 + 6 duplicates              : 114
```

So the range is gapless, the distinct count equals the span exactly, and **114 = 108 distinct + 6 duplicate headings**. The target was never an aspiration to reach by adding entries: it is the *heading* count of the current file, duplicates included, and **it already matches exactly**.

Retirement is therefore the right call, but for a different reason than the draft gave: not "unreachable", but "already true, and a poor denominator because six ids are double-headed". The number that should be cited is the **index-row count and its OPEN count**, which stood at 108 / 9 at S2 close and at 109 / 10 once KI-165 was filed during this review.

A caution this probe earns: the draft's 111/105 came from a stale read of the file after an earlier edit, and the arithmetic contradiction (a gapless 108-wide range cannot hold 105 distinct) is what exposed it. **When two sections of one document disagree on a count, the arithmetic is the referee.**

### 3.7 Two R8 items found already built

While probing KI-116/KI-119:

- **R8 P-41 (hint discount, floored):** `app/Services/HintDiscount.php` exists, uses `floor(base × (1 − discount/100))`, and documents integer arithmetic specifically because the float form drifts (`180 × 0.7` → 125.99999999999999 → 125).
- **R8 P-60 (fan ladder):** `app/Services/FanLadder.php` exists, is **called** by `CockpitController.php:581`, and **renders**: `CareerStatePanel.vue:309` gates on `row.key === 'fans' && props.fanLadder && props.fanLadder.class !== null`. All four files are committed and clean, and the prop is present in the **served bundle** (`public/build/assets/Cockpit-1FC9-BSb.js`). It landed in **`27800fa8`, 2026-11-15**, "render the fan ladder class and the Top Star gap (R6-43 coverage)" — i.e. *after* walk-9's observation at `248e1cbb`, which is why the walk correctly saw it absent.

  **But the ladder is one band, not nine.** `config/uma.php:578-580` holds exactly one entry: `['class' => 'Star', 'from' => 0, 'to' => 240000, 'next_class' => 'Top Star']`. So for this run's 209,245 fans the class (`Star`), threshold (240,000) and gap (**30,755**, matching SR exactly) are all correct — and `FanLadderTest` asserts `classFor(240_000) === null`, i.e. a count above the only band is deliberately unclassifiable. The other eight tiers from SR §2.3g (Legend 320,000 … Beginner) are **not representable**.

  Verdict: P-60's *path* is closed (L1 schema, L2 writer, L3 render, L4 served all present); its *data* is one-ninth complete. The owner's caution was right in mechanism — a service existing does not refute a rendered-absence observation — but this one happens to be rendered too, and the remaining gap is the band list rather than the wiring.

Neither is a new finding; both **shrink remaining S3+ scope**, and P-60 splits into a closed path plus an open data question.

---

## 4. Objective B — S1, the FIX batch

### 4.1 Code landed (13 files, +147/−4)

| File | Δ | What |
|---|---|---|
| `app/Services/Career/TeamPanelBuilder.php` | +23 | goal rows and mandatory rows carry `fan_gate`, `fan_gate_label`, `fan_gate_title`; gate read via `RaceCatalogSlot::hasFanGate()`, never derived from grade |
| `app/Http/Controllers/Career/SnapshotController.php` | +47 | `scenarioLabel()` kept; new `scenarioFullLabel()` reading `config('scenarios.scenarios.<key>.full_label')`, passed as its own Inertia prop **deliberately outside `fields`** |
| `app/Http/Requests/StoreSnapshotRequest.php` | +6 | mirrors the existing `between:1,5` for potential; does not widen for JP's 覚醒 Lv6/7 |
| `app/Actions/CreateSnapshotRun.php` | +7 | writer carries the new fields |
| `resources/js/components/scenario/cards/GoalGroupCard.vue` | +10 | interface + `data-fan-gate` span |
| `resources/js/components/scenario/ScenarioCardGrid.vue` | +4 | duplicate interface made to agree |
| `resources/js/components/scenario/UnityCupPanel.vue` | +4 | duplicate interface made to agree |
| `resources/js/pages/Career/Cockpit/Panels/CareerGoalsPanel.vue` | +4 | duplicate interface made to agree |
| `resources/js/pages/Career/Snapshot/Review.vue` | +10 | `data-owned-skills-handoff` paragraph (U-02 / P-2 ruling) |
| `resources/js/pages/Runs/Index.vue` | +5 | `Start from existing snapshot` → `/career/snapshot` |
| `resources/js/pages/Dashboard.vue` | +14/−3 | duplicate door label unified to the destination's own H1 |
| `tests/browser/dashboard.spec.ts` | +3/−1 | label assertion updated |
| `tests/browser/runs.spec.ts` | +10 | visibility + href + click + URL for the snapshot door |

### 4.2 New tests (3 files, all passing)

| File | Result (this session, re-run) |
|---|---|
| `tests/Feature/TraineeGoalFanGateTest.php` | **3 passed (28 assertions)** |
| `tests/Feature/SnapshotOwnedSkillsTest.php` | **1 passed (12 assertions)** |
| `tests/Feature/SnapshotDoorLinkTest.php` | **3 passed (4 assertions)** |
| `tests/Feature/SnapshotReviewTest.php` (updated) | **10 passed (127 assertions)** |

### 4.3 Key S1 decisions and their authority

- **Fan gate read off `race_catalog_slots.fans_needed` via `hasFanGate()`, never derived from grade.** The reference publishes fan *ranges* per grade, so a lookup would fabricate. Absence renders `N/A` with a `title`, never `0`.
- **Full scenario label as its own Inertia prop, not inside `fields`.** `SnapshotReviewTest` deliberately pins the `fields` rows ("Config's label, never the stored key"); conflating them would silently break the identity contract.
- **Mandatory-race rows carry the same three fan keys** so the row shape does not depend on which group draws.
- **`potential_level` mirrors `between:1,5`.** The report's "must not cap at 3/5" was a mangled carry of a warning; JP's 覚醒 Lv6/7 is a future-watch, not a reason to widen a Global bound no Global notice has changed.
- **Duplicate `GoalGroup` interfaces made to agree, not consolidated.** Consolidation is a refactor; it became KI-163.

### 4.4 Register tally

**12 rows / 11 findings · 8 closed · 1 terminal · 3 deferred.** The 8 closed = 2 by erratum + 6 in code across five changes. Supersedes the older "7 of 11 dispositions / 10 findings".

Closure provenance is mixed and is recorded as such: E-01/U-07 were **probe-refuted** (fixed before the walk); E-02/U-05b were **fixed in uncommitted working-tree code**. One `ALREADY-FIXED` label over both provenances is loose, so each closure note states which it is.

---

## 5. Verification evidence — re-measured this session

I re-ran every gate that can be re-run, rather than quoting prior output.

| Gate | Command | Result |
|---|---|---|
| TypeScript | `npm run typecheck` | **exit 0**, 33s |
| Style | `php vendor/laravel/pint/builds/pint --test --format agent <4 PHP files>` | **`{"tool":"pint","result":"passed"}`**, exit 0 |
| Static analysis | `php vendor/phpstan/phpstan/phpstan analyse --no-progress --memory-limit=1G` | **`[OK] No errors`**, exit 0, 19s |
| Targeted tests | four filters | all exit 0 (see §4.2) |
| Markdown fences | `python tools/md_fence_check.py` on four touched docs | **0 invalid closing fences** |
| Register integrity | re-derivation | **109 rows == 109 bodies**, 0 unannotated contradictions |
| Byte discipline | CRLF / lone-LF / UTF-8 | 5197 CRLF, 0 lone LF, strict UTF-8 valid |

### 5.1 The cross-test failure (KI-162) — re-verified, still unexplained

| Measurement | Result |
|---|---|
| `DashboardTest` alone | **18 passed (339 assertions)** |
| `RunRaceStripTest` alone | **18 passed (286 assertions)** |
| `DashboardTest\|RunRaceStripTest` together | **36 passed (625 assertions)** |
| `DashboardTest\|TraineeGoalFanGateTest` | **21 passed (367 assertions)** |
| `DashboardTest\|SnapshotReviewTest` | **28 passed (466 assertions)** |

So the failure is **order-dependent, not pair-dependent**, and it does not reproduce under any pairing. Attribution remains **peer-attributed, causality not proven by revert**: the peer diff rewrites `'position_label' => $this->positionLabel($turn)` into `$this->positionLabel($position->turnIndex)` with a new `careerPosition()` owner, which is precisely the field that fails. The peer files are still in the tree:

```
app/Http/Controllers/DashboardController.php   +17 −2
app/Models/Umamusume.php                        +9 −0
app/Services/Career/RaceStripBuilder.php       +11 −1
```

#### The full suite now runs clean — and KI-162 does not reproduce

A complete suite was run to completion during this report, against the same HEAD and with the peer files still in the tree:

```
$ php artisan test --compact
Tests:    2455 passed (37540 assertions)
Duration: 445.02s
```

**Zero failures.** Every failure-shaped marker was searched for and none is present: no `FAILED`, no `⨯`, no `N failed`, and exactly one `Tests:` line, reading `2455 passed`.

Two things must be stated precisely, because the process exit code is **1** and that would otherwise be misread:

1. **Exit 1 is not a test failure.** The complete log shows the summary at character offset 2549 and an `Amp\Websocket\WebsocketClosedException` — `Client unexpectedly closed; Code 1006 (ABNORMAL_CLOSE); "Writing to the client failed"` — at offset 2629, i.e. **after** the summary. It is a Playwright-plugin teardown fault raised once the run had already finished, in `Pest\Browser\Playwright\Client::execute()`. Nothing failed. The suite is green; the harness's shutdown is not clean.

2. **This suite is Unit + Feature only.** `phpunit.xml` declares exactly two suites, `Unit -> tests/Unit` and `Feature -> tests/Feature`, and no `Browser` suite. So this figure says nothing about `tests/browser/`.

**Consequence for KI-162, and it is a material change.** KI-162 recorded `1 failed, 2453 passed` from a prior full run and attributed the failure to the peer refactor. The same suite, same HEAD, same peer files, now reports **2455 passed / 0 failed**. The two named cases (`DashboardTest`, `RunRaceStripTest`) pass alone, pass in every pairing tried, and passed inside this full run.

So: **the failure is not currently reproducible.** The honest classification is *intermittent or already-resolved*, not "peer-attributed, awaiting proof". Three possible explanations fit the evidence and this session did not distinguish between them:

- a genuine order/contention effect that depends on machine state rather than test order;
- the peer session having changed its uncommitted files between the two runs (the numstat in §5.1 was taken *after* the earlier run);
- a flaky case that the earlier run happened to catch.

**KI-162 should be re-read before it is trusted as a live defect.** Its row currently says "once per full suite", which this measurement falsifies. The next agent should either re-verify it against the current peer bytes or narrow the row to record that it was observed once and has not recurred. Do not let the earlier figure reach the S4 release gate as a current failure.

---

## 6. Files created or modified by this session

### Created (untracked)

```
docs/audits/reconciliation-rounds/rice-shower-unity-cup-round8-s2-register-truth.md   16,260 bytes
docs/audits/reconciliation-rounds/rice-shower-unity-cup-round8-s1-handoff.md          21,386 bytes
docs/roadmap/implementation-roadmap-2026-11-17.md                                     ~13,400 bytes
docs/plan/implementation-plan-2026-11-17.md                                            ~9,400 bytes
docs/audits/ux-walkthroughs/            (directory, 11 walk docs moved in)
docs/audits/reconciliation-rounds/      (directory, 10 files)
docs/audits/reconciliation-register/    (directory)
docs/audits/capability-matrix/          (directory)
docs/audits/mwg-audit/                  (directory)
tests/Feature/TraineeGoalFanGateTest.php
tests/Feature/SnapshotOwnedSkillsTest.php
tests/Feature/SnapshotDoorLinkTest.php
.scratch-uma/s1-backup/                 (backup set, see §7)
.scratch-uma/sitrep-full-suite.log      (in-flight suite log)
```

### Modified (tracked, unstaged)

```
KNOWN-ISSUES.md                                      450,504 bytes (was 437,045)
docs/roadmap/implementation-roadmap-2026-11-17.md    erratum applied to the "31" claims
docs/plan/implementation-plan-2026-11-17.md          erratum applied
```

### Backup set — `.scratch-uma/s1-backup/`

`s1.patch` is **28,323 bytes, 16 `diff --git` entries, and contains none of the three peer files**. Confirmed by name, not by count. `git apply --check --reverse` exits 0, meaning the patch matches the working tree it was taken from.

The R8 backup was found stale and refreshed: it held **32,212** bytes while the live file was **35,921**. Both now report SHA-256 prefix `a72a4220e368f972`. `KNOWN-ISSUES.md` was added to the backup directory after this pass.

---

## 7. Working-tree risks the next agent must know

### 7.1 90 tracked files are missing from disk: `jev-inspect/`

`git status` reports **108 deletions**. Breaking them down was necessary, because two very different situations wear the same status code:

| Group | Count | Real state |
|---|---|---|
| `docs/audits/*` old round/walk files | 18 | **Moved**, not deleted — the files live in the untracked `docs/audits/reconciliation-rounds/` and `docs/audits/ux-walkthroughs/` subdirectories |
| `jev-inspect/` | 90 | **Gone.** The directory does not exist on disk and has not been moved anywhere in the repo. Last touched by commit `451f411c` (2026-10-08). |

**I did not delete these and did not restore them.** They are almost certainly another session's or a manual removal. `jev-inspect/` is a separate Python project tracked inside this repo, so its removal is either intentional cleanup or an accident — **the owner must decide**, and until then a plain `git commit -a` would silently encode the deletion.

### 7.2 Peer uncommitted work in a shared tree

Three app files (listed in §5.1) belong to another session. Per the brief's own instruction, landing / reverting / parking that refactor is a **scheduling decision, not an agent one**. It is the only reason the suite cannot go green.

### 7.3 Untracked test files that must not be lost

`tests/Feature/ShippedCopyTruthTest.php` and the four S1 test files are untracked. A peer reset would lose them; the `.scratch-uma/s1-backup/` copy is the recovery point.

### 7.4 Other untracked loose ends observed (not mine)

```
resources/js/components/ui/Sparkline.vue
resources/js/components/ui/StatRing.vue
resources/js/polyfills.js
tests/browser/_probe-overflow.spec.ts
tests/browser/stat-visuals.spec.ts (+ 2 fixtures)
config/inertia.php
win-unpacked/            (build output — should not be committed)
```

`_probe-overflow.spec.ts` and `_AGENTS_DO_NOT_CREATE_FOLDERS_OR_FILES_IN_THIS_DIRECTORY.txt` are named hazards, not deliverables.

---

## 8. Deviations from the plan

| Planned | Actual | Why |
|---|---|---|
| S2 closes in one turn; register nearly empty | 108 rows / 9 OPEN at S2 close (**109 / 10** after KI-165); a full reconciliation was required | The "0 body-OPEN" figure was a regex artefact (§3.1) |
| `grep -c "Status: OPEN"` as the S2 acceptance check | rejected | It is one of three spellings; using it would have re-measured the pattern |
| 114 heading target to be hit or named | **retired, and already satisfied** | Re-probed: **114 headings / 108 distinct / span 108 / 0 gaps / 6 duplicate ids**. 114 = 108 + 6, i.e. it is the current heading count exactly. A poor denominator (six ids double-headed), not an unreachable one |
| Walk via subagent | parent session, `browser_exec` | Delegation docs conflict on toolset inheritance |
| Walk-7 deliverable at `docs/audits/rice-shower-unity-cup-ux-walk-7.md` | walk-9 completed and moved to `docs/audits/ux-walkthroughs/` | The brief was instantiated against a superseded revision; walk-9 was the live document |
| 15 tracked docs to commit | nothing committed | No commit instruction was given; the tree also holds peer work |
| Lint debt 282 | 207 arbitrary-value vs 209 baseline, exit 0 | Measured; 516 is the eight-rule total |

---

## 9. Known risks

1. **Nothing is committed.** All S1 and S2 work lives in a shared, 207-entry dirty tree. A peer reset or another daemon episode loses it. The patch backup is the only recovery path.
2. **`jev-inspect/` is 90 files deleted from disk** and would be encoded by any `git commit -a` (§7.1). This is the single most dangerous item in the tree.
3. **The suite DID go green** with the peer refactor in place: `2455 passed (37540 assertions)`, zero failures (§5.1). The earlier "cannot go green" blocker is withdrawn — it was measured at a different moment and the peer files have since changed. The peer refactor remains a *scheduling* question, not a test blocker.
4. **KI-162's causality is inferred.** Field overlap is strong evidence, not proof.
5. **The full suite is green but the harness exits non-zero** (§5.1): `2455 passed (37540 assertions)`, exit 1 from a Playwright websocket teardown *after* the summary. Any CI or gate that reads the exit code alone will report a failure that does not exist; any reader who reads only the code will miss that the suite passed. Both must be read.
6. **The suite does not cover `tests/browser/`.** `phpunit.xml` declares only `Unit` and `Feature`. A green `artisan test` is silent about 125 browser specs.
7. **RELEASE-BAR HAZARD: a green suite exits 1.** The summary (`Tests: 2455 passed`) lands at log offset 2549 and the `Amp\Websocket\WebsocketClosedException` at 2629 — *after* it. Any gate condition written as "pest is green" that reads the **exit code** will report a failure that does not exist, and will do so at tag time when it is most expensive. Two acceptable resolutions: fix the Playwright-plugin teardown, or write an explicit offset-evidence carve-out into the gate ("read the `Tests:` line; exit 1 with a post-summary `WebsocketClosedException` is a pass"). **Do not leave this to be rediscovered.**
7. **KI-162 no longer reproduces**, so a prior finding's premise is unsupported (§5.1). Treating its filed row as a current failure would put a phantom into the release bar; deleting the row would discard a real observation. It needs re-reading, not resolution.
6. **Stale PHP dev servers.** At measurement, **ten `php artisan serve` processes** were alive on ports 8000, 8181, 8196 (two pairs), 8198, 8264. They are not `pest` processes, so they do not invalidate a test run, but they are a contention surface under the KI-74 rule.
7. **`screens/` is gitignored**, so the walk's corroborating evidence does not travel with a clone.
8. **The 6 stashes are unexplained.** They may hold other sessions' work; do not `stash drop` or `stash pop` blindly.

---

## 10. Next steps, in execution order

1. **Read the suite result correctly.** It is done: `2455 passed (37540 assertions)` in 445.02s, log at `.scratch-uma/sitrep-suite2.log`, pinned by a `FINAL_EXIT 1` line written after the summary. The exit code is a Playwright teardown artefact, not a failure. Before launching another suite, enumerate `php.exe` (the KI-74 stale-process rule); a previous attempt of mine died mid-run leaving 33 KB of dots and no summary, so **always confirm a `Tests:` line exists before trusting a log**.
2. **Owner decision: `jev-inspect/`.** Restore it (`git checkout -- jev-inspect/`) or accept the deletion explicitly. **Do not let a commit-a encode it by accident.**
3. **Owner decision: the peer refactor.** Land, revert, or park in a worktree. Until this is settled every label-related verdict is unstable.
4. **Re-verify or narrow KI-162.** The worktree proof is now less urgent than the row's accuracy: the failure did not recur in a complete run at this HEAD with the peer files present (§5.1). Either reproduce it against the current peer bytes, or amend the row to say it was observed once on 2026-11-17 and has not recurred. **Do not carry it to S4 as a live failure, and do not delete it.**
5. **KI-114** — session-private Playwright pair on `career-legacy-deck-steps.spec.ts` and `support-deck.spec.ts`. Never `career-preflight.spec.ts` (counter-spec).
6. **KI-106** — re-measure `DocCitationParityTest` against the current `DOC_CITATION_BASELINE`.
7. **P7 live leg** — scratch DB with zero careers so the `hasCareer === 0` empty-resume branch fires; confirm the unified `Start from existing snapshot` renders **live**. This is the one S1 change never observed rendering.
8. **Owner decision: S1 onto the tag path.** `origin/master` does not contain S1, so v3.0.0 as planned ships without all five fixes. Worktree-from-`origin/master` plus PR is the proven pattern (PR #25).
9. **Commit only on explicit instruction, with exact pathspecs.** The safe form is `git commit --only -- <paths>`; untracked docs need `git add` first, which mutates the shared index.

---

## 10a. Owner review of the first draft, and what it corrected

Four corrections came back on this report. All four were checked and three were mine to fix; the fourth changed a
verdict. Recorded because the pattern matters more than the individual errors.

| # | Caught | Finding |
|---|---|---|
| 1 | §9 said "the suite cannot go green while the peer refactor is in place", contradicting §5.1's measured `2455 passed / 0 failed` | **Real.** A terminal section carried an intermediate blocker forward. §9 now reads that the suite *did* go green. |
| 2 | §3.6's "111 headings / 105 distinct, contiguous, no gaps" is arithmetically impossible — a gapless KI-57..KI-164 range spans 108 | **Real, and the arithmetic was the referee.** Re-probed: **114 headings, 108 distinct, span 108, zero gaps, 6 duplicate ids**. 114 = 108 + 6, so the retired target was never unreachable — it is the current heading count exactly. §3.6 rewritten. |
| 3 | A green suite exiting 1 is a release-bar hazard, not a footnote | **Real.** Promoted to §9 item 7 with two acceptable resolutions. A gate that reads exit code alone reports a phantom failure at tag time. |
| 4 | §3.7's "`FanLadder.php` exists" does not refute walk-9's rendered-absence observation — the exact E-02 error in reverse | **Real, and it changed a verdict.** Probed L1–L4: the path is fully built, *committed* (`27800fa8`), and in the served bundle — but `config('uma.fan_ladder')` holds **one band**, not nine. P-60 splits: path closed, data 1/9. Filed as **KI-165**. |

Two smaller carries also resolved: the **83-vs-80** migration conflict was a line-count-vs-row-count artefact (80 rows, 80 files on disk, 0 pending); and the **ahead-only** framing hid divergence — the branch is **ahead 15, behind 7**, so it has genuinely diverged from `origin/master` (`969eb560`).

**The methodological point worth keeping:** every one of the four was a case of a *derived* claim outliving the measurement that produced it. The draft was internally inconsistent in two places, and in both the inconsistency — not a new probe — was what exposed it. A report that states its own counts in two sections has a free consistency check; it should be run before the report is handed over, not after.

## 11. Verified vs assumed

**Verified by command in this session:** HEAD, branch, **ahead 15 / behind 7**; working-tree census and its 90/18 deletion split; stash count; a **complete full suite: `2455 passed (37540 assertions)`, 445.02s, zero failures, exit 1 from post-summary Playwright teardown**; `phpunit.xml` declares only Unit + Feature; the four targeted test files; `DashboardTest` and `RunRaceStripTest` alone and in three pairings; typecheck; pint; phpstan; fence check on five docs; register **109==109 with 10 OPEN** and zero unannotated contradictions; CRLF/UTF-8 integrity; `migrate:status` **0 Pending, 80 rows == 80 files on disk**; the lint rule breakdown (207 vs 209 baseline); **all four layers of the fan ladder (service, controller call, render guard, served bundle) plus its single-band config**; the `card_level` migration; peer file numstat; `s1.patch` 16-file list excluding all three peer paths and reverse-applying cleanly; R8 backup SHA match; the live `artisan serve` process census.

**Assumed, and labelled:** that the 18 `docs/audits/*` deletions are moves rather than copies (the files are present in the untracked subdirectories and their sizes match the backup copies); that the 6 stashes belong to other sessions; that the `jev-inspect/` removal was not this session's. **Peer causality for KI-162 is no longer asserted at all** — the failure did not reproduce, so the earlier attribution is unsupported rather than merely unproven.

**Not measured:** why KI-162 failed once and not again; KI-114's browsers; KI-106's citation count; the empty-state live branch; the 125 browser specs (not in any phpunit suite).
