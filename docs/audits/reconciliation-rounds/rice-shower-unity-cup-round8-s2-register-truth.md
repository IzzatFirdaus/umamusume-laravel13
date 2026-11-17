# S2 — register truth, and the denominator that replaces "31"

**Date:** 2026-11-17
**Phase:** S2 (of the roadmap pair at `docs/roadmap/implementation-roadmap-2026-11-17.md` and `docs/plan/implementation-plan-2026-11-17.md`)
**Register under test:** `KNOWN-ISSUES.md`
**Nature:** register-integrity pass. No application code, configuration, schema, or data was changed. No commit, no stage, no tag.

---

## 0. Why this pass existed, and what the briefing got right

The briefing predicted S2 would be "nearly empty" because Phase A had "driven body-OPEN to 0 at `be8893a2`". That prediction was **wrong in one direction and right in another**:

- **Right:** the *newer* cohort of entries (roughly KI-129 onward, which use `**State:**`) really did have no OPEN bodies. A grep for `^\*\*State:\*\* OPEN` returns a small, already-triaged set.
- **Wrong:** the register does not use one field name. **Three header spellings coexist**, and the older cohort (~63 entries, KI-59 through KI-128) uses `**Status:**` or `**Status: OPEN.**` — the value *inside* the bold. Phase A's grep could not see them. "Body-OPEN = 0" was an artefact of the pattern, not a property of the file.

So the plan's own grep (`grep -c "Status: OPEN"`) was **not** the trivially-zero one the briefing feared — it was the *more* correct of the two — and it found 27. The briefing's warning was well-founded even though its specific worry was inverted.

### 0.1 The field-name gate, resolved

| Pattern | Count | What it sees |
|---|---|---|
| `^\*\*State:\*\* OPEN` | new cohort only | the Phase A pattern; blind to the older cohort |
| `^\*\*Status:\*\* OPEN` | older cohort | most of the body-OPEN set |
| `^\*\*Status: OPEN\.` | older cohort | the third spelling, value inside the bold |

**All three must be measured together, or the count is a property of the grep.** Any future "is the register clean" check that uses one pattern is measuring its own regex.

---

## 1. P1 — backup integrity (confirmed by name, not assumed)

The briefing's §3 probe was correct to demand names rather than a count.

```
.scratch-uma/s1-backup/s1.patch        28323 bytes, 16 `diff --git` entries
  peer files present: NONE
  app/Http/Controllers/DashboardController.php   absent
  app/Models/Umamusume.php                       absent
  app/Services/Career/RaceStripBuilder.php       absent
```

The patch is S1-only. A future restore cannot re-apply peer code.

**R8 refresh.** The briefing flagged that the backed-up R8 copy might predate its own amendments. It did:

| Copy | Bytes | SHA-256 (first 16) |
|---|---|---|
| live `docs/audits/reconciliation-rounds/…round8-reconciliation.md` | 35,921 | `a72a4220e368f972` |
| backup at `.scratch-uma/s1-backup/…round8-reconciliation.md` (before refresh) | 32,212 | — |
| backup (after refresh) | 35,921 | `a72a4220e368f972` |

The backup now matches the live file byte-for-byte. `KNOWN-ISSUES.md` was also copied into the same directory after this pass.

---

## 2. P2 — index/body reconciliation

### 2.1 The register's own convention

Lines 2–4 of `KNOWN-ISSUES.md` settle the authority question:

> every KI the file names, one current status each. The tree is the authority; where the body has drifted from it, the line below records [the current status].

So **the index line is the current state and the body keeps its filed history.** An index row reading CLOSED while the body still reads OPEN is *conforming*, provided the body's history is legible and a later closure exists in the body or the tree. An index row that disagrees with a body which has *no* later closure is a defect.

### 2.2 What was found

| Class | Count | Treatment |
|---|---|---|
| Index said OPEN, body said CLOSED/RESOLVED/TERMINAL with a later marker | 11 | index rewritten to the body's state, each carrying a dated correction note |
| Index said CLOSED, body said OPEN, **no later closure anywhere** | 12 entries (13 body blocks — KI-71 carries two) | body annotated: the index is the current-state owner and that status text is historical |
| Index said OPEN, body said RESOLVED at a named commit (KI-154…157) | 4 | index rewritten to RESOLVED with the commit cited |
| Index and body both live, wording differed (KI-104) | 1 | annotated as agreeing — the fix is uncommitted, so the entry is legitimately live |
| Body present, **no index row at all** (KI-161) | 1 | index row added |
| Index said CLOSED, body OPEN with root cause proven (KI-97) | 1 | annotated with which side wins and what reopens it |

**Every rewrite is exact-match on the status token.** No fuzzy or regex substitution touched a copy string. Two earlier attempts in this session corrupted content with regex (`title` truncation; `fields.*` scramble), which is why the rule exists.

### 2.3 Verification

After the edits, a re-derivation over both layers reports:

```
unannotated contradictions: 0
annotated reconciliations : 14   (KI-71, 72, 73, 76, 87, 88, 91, 95, 97, 104, 108, 109, 111, 128)
index rows               : 108
body entries             : 108 distinct ids
bodies without index row : none
index rows without body  : none
```

**One-to-one.** Every body has a row, every row has a body.

### 2.4 Byte discipline

The file is **CRLF throughout**. Preserving that was checked on every write:

```
before: 441,849 bytes | CRLF 5,196 | lone LF 0
after : 450,280 bytes | CRLF 5,197 | lone LF 0 | strict UTF-8 valid
```

Python's default text-mode read translates CRLF to LF, which silently destroys line endings. All reads here used `newline=''`.

---

## 3. P3 — one denominator, and the 114 target

### 3.1 Published denominator (2026-11-17, S2)

**108 index rows: 9 OPEN, 99 closed-family.**

The 9 OPEN entries:

| ID | State | Why it stays open |
|---|---|---|
| KI-104 | OPEN | ADR-0027 reads `Status: **Proposed**`; the fix landed (`005561c1`) but the owner's signature does not exist. Signature, not code. |
| KI-106 | OPEN | docs-citation ratchet, not re-measured in this pass |
| KI-114 | OPEN | two browser files not re-measured; needs a session-private Playwright pair |
| KI-116 | OPEN, **narrowed** | `DeckAnalysis.php:28-29` now contributes a reached value whenever a slot records a level; only unrecorded slots remain |
| KI-119 | OPEN, **narrowed** | `current_spirit_proxy` is now a typed prop at `UnityCupPanel.vue:87`; the full Current Spirit gauge is still unmodelled |
| KI-127 | OPEN, **confirmed** | `TeamRaceFacilityPanel.vue:54` still renders a bare `<div class="flex flex-col gap-4">` |
| KI-162 | OPEN | **new** — the peer-attributed full-suite failure (P4) |
| KI-163 | OPEN | **new** — four duplicate `GoalRow`/`GoalGroup` interfaces (P5) |
| KI-164 | OPEN | **new** — `SnapshotReviewTest` positional assertion coupling (P5) |

### 3.2 The 114 heading target is arithmetically unreachable

The briefing asked for this to be retired or named. Measured:

```
KI-<n> body headings: 111
distinct ids        : 105
range               : KI-57 .. KI-164, no gaps
duplicate headings  : 6 pairs (KI-71 appears twice and again in another block)
```

The file's range is **contiguous** — there are no missing numbers to "name" — and the heading count exceeds the distinct count because four ids carry more than one body. **114 cannot be reached by adding entries, and nothing needs adding.** The figure was a snapshot of a different block structure. Recommendation: retire it; the denominator that matters is the index-row count above.

---

## 4. P4 — the cross-test failure, filed as KI-162

The briefing's ambiguity warning was justified: the earlier report said "1 failed" and then mentioned `RunRaceStripTest` separately. Both are now named in the register.

**Failing tests, by name:**

1. `DashboardTest > it prints the imported position a snapshot stands at, not the count of turns it logged`
2. `RunRaceStripTest > it stands at the imported position for a snapshot`

**Full-suite output:** `Tests: 1 failed, 2453 passed (37521 assertions)`, 465.68s, single process.
**Isolation:** `DashboardTest` 18 passed / 339 assertions; `RunRaceStripTest` 18 passed / 286 assertions.
**Pairings:** `Dashboard` against each of eight S1 suites, both directions — **16 pairings, all pass.**

**Attribution, written at its true strength:** *peer-attributed, causality not proven by revert.* The peer diff rewrites `'position_label' => $this->positionLabel($turn)` into `$this->positionLabel($position->turnIndex)` with a new `careerPosition()` owner — precisely the field that fails. That is a strong inference. The proof the briefing proposed is still available and still unrun: a worktree at HEAD with **no working-tree changes**, failing tests run in full-suite order. Green there implicates the uncommitted diff; red means the defect pre-dates it.

**Release-bar consequence.** S4 requires zero failures *or a named set each carrying a filed row*. KI-162 is that filing. It makes the failure legible; it does not make the bar pass.

---

## 5. P5 — the two candidates, filed

**KI-163 — four duplicate `GoalRow`/`GoalGroup` interfaces.** `GoalGroupCard.vue`, `ScenarioCardGrid.vue`, `UnityCupPanel.vue`, `CareerGoalsPanel.vue`. Adding the three fan-gate fields required four edits, and `npm run typecheck` failed twice, once per missed copy. Verdict: **DEFER as refactor debt.** TypeScript caught the drift, so the cost is edit sites, not correctness.

**KI-164 — positional assertion coupling.** `SnapshotReviewTest` reads `fields.2.display`, `fields.7.state`; two added identity rows moved every state row and cost six assertion edits plus a corrected `has()` count. Verdict: **DEFER.** The assertions are correct — they pin the exact read-back the screen renders — but a keyed lookup would survive any insertion. Repairing it is a test-shape refactor, not a feature slice.

---

## 6. P6 — verdicts on the previously-OPEN set

Fifteen index rows were rewritten from a measured probe, not from a reading. Every CLOSED verdict below cites the command that produced it.

| ID | Verdict | Evidence |
|---|---|---|
| KI-59 | **CLOSED** | fixed by branching, not deletion: `catalog-detail.spec.ts:271-281` asserts `if (frame.count() === 0) {img count 0} else {alt=""/src/no-anchor}`, so a populated and an empty mirror both pass. The `:250` assertion targets an anchor around the frame, not the frame's presence |
| KI-60 | **CLOSED** | `php artisan migrate:status --no-ansi` → **0 Pending**, 80 migration rows on `database/database.sqlite`. The four migrations this body names have run |
| KI-61 | **CLOSED** | `career-preflight.spec.ts` no longer drives an empty-body PUT; it waits on the deck PUT at `:109` |
| KI-69 | **CLOSED** | `playwright.config.ts:18` pins `DB_DATABASE: SCRATCH_DATABASE` imported from `global-setup`; port default 8123, not 8127. The shared-dev-file harness this entry describes is gone |
| KI-70 | **TERMINAL** | accepted process hazard, not a code defect. `AGENTS.md` §9 now carries the scratch-directory, one-Pest-process and session-private-harness rules that answer it |
| KI-74 | **TERMINAL** | accepted as a class; enforced by the pre-flight in `AGENTS.md` §9 and `tools/gate-preflight.php` |
| KI-100 | **TERMINAL** | accepted limitation: owner ruling 4a chose on-screen disclosure over inference for the 22 undecoded objective rows |
| KI-107 | **CLOSED** | `CLAUDE.md:12` records the fold into `GOVERNANCE.md`; `:257` forbids adding `CLAUDE.local.md`. Neither absent file is cited as if it existed |
| KI-117 | **CLOSED** | migration `2026_10_25_130000_add_card_level_to_deck_slots_table.php` adds `card_level`; `DeckSlot` `#[Fillable]` and casts carry it |
| KI-124 | **TERMINAL** | accepted limitation per its own body: banner and avatar list are migrations needing an ADR |

Staying OPEN, with narrowed scope: **KI-116** and **KI-119** (see §3.1). Staying OPEN unmeasured: **KI-106**, **KI-114**. Confirmed reproducing: **KI-127**.

### 6.1 KI-145, and a correction to the briefing's figure

The briefing carried "lint debt 282". Measured:

```
node tools/ui-framework-check.mjs
exit: 0
ui-framework-check: 516 finding(s)
  by rule: literal-length 220 | arbitrary-value 207 | literal-colour 78
           box-shadow 3 | colour-var 3 | radius-scale 2 | outline-width 2 | transition-motion 1
arbitrary-value = 207 vs baseline.count 209 -> within baseline
debt-increase finding present: False
```

**The gate is working correctly.** Rule 8 counts only `arbitrary-value`; 207 is under the 209 baseline, so exit 0 is right — and per `tools/ui-framework-check.mjs:228`, a debt increase fails on its own without `--strict`. The 516 is the total across all eight rules, which no baseline governs. Both the body's "282 vs 219" and the earlier "202 vs 209" are stale; the index row now carries the measured figure.

### 6.2 Two R8 probes that turn out to be already built

While probing KI-116/KI-119 this pass found two R8 items already implemented, which matters for the roadmap's remaining scope:

- **R8 P-41 (hint discount, floored):** `app/Services/HintDiscount.php` exists, uses `floor(base × (1 − discount/100))`, and documents integer arithmetic specifically because the float form drifts (`180 × 0.7` → 125.99999999999999 → 125).
- **R8 P-60 (fan ladder with thresholds):** `app/Services/FanLadder.php` exists with `classFor()`, `nextThreshold()`, `gapToNext()`, reading `config('uma.fan_ladder')`.

Neither is a new finding; both shrink the remaining S3+ scope.

---

## 7. What this pass did not do

- **No code, config, schema, data, or fixture change.** `git status` shows `KNOWN-ISSUES.md` among the working-tree changes; nothing else in this pass touched the tree.
- **No commit, stage, or tag.** All S1 and S2 work remains uncommitted by instruction.
- **KI-106 and KI-114 were not re-measured.** Both need instruments this pass did not run (`DocCitationParityTest`; a session-private Playwright pair). Their rows say so rather than guessing.
- **The peer worktree proof was not run.** It is the exact next probe for KI-162 and it is cheap; it needs a worktree and a full-suite-order run, which is a scheduling call, not a register edit.
- **The empty-state live leg (P7) was not run.** `Start from existing snapshot` is verified statically and in the served bundle; the `hasCareer === 0` branch needs a scratch DB with zero runs. It remains the one S1 change never observed rendering.

---

## 8. Carry-forward, in execution order

1. **Owner: schedule the peer refactor** (land / revert / park). It blocks a green suite and destabilises any label-related verdict.
2. **Run the worktree proof for KI-162** — worktree at HEAD, no working-tree changes, two failing tests in full-suite order. This converts "peer-attributed" into either proof or refutation, and it is the cheapest remaining evidence in the project.
3. **KI-114** — session-private Playwright pair on the two named files; verdict from the run, not the body.
4. **KI-106** — re-measure `DocCitationParityTest` against the current baseline.
5. **P7 live leg** — scratch DB with zero careers, confirm the unified snapshot door renders in the empty-resume branch.
6. **Owner: S1 onto the tag path.** S1 is uncommitted on local `master`; `origin/master` does not contain it, so v3.0.0 as planned ships without the fixes. Worktree-from-`origin/master` plus PR is the proven pattern (PR #25).

## 9. Decisions this pass made on the operator's behalf

| Decision | Choice | Why |
|---|---|---|
| Field-name ambiguity | measure all three spellings | one pattern measures the pattern, not the file |
| Index vs body authority | index is current state, body is history | the file states this convention at lines 2–4 |
| Body with no later closure | annotate, do not rewrite | rewriting would assert a closure the tree does not show |
| KI-97 (index CLOSED, body OPEN, root cause proven) | annotate both sides and state what reopens it | neither reading is provably right without a revert test |
| KI-104 | remain OPEN | ADR-0027 is Proposed; an owner signature is not an agent action |
| 114 heading target | retire | contiguous range, no gaps, count exceeds distinct ids by design |
| KI-163 / KI-164 | file as OPEN refactor debt, do not fix | both are refactors; S2 is a register pass |
