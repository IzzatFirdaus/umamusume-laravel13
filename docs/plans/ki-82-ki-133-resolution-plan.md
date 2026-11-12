# KI-82 and KI-133 — investigation and implementation plan

**Date:** 2026-11-12
**Inspected HEAD:** `efdbb88d` · branch `master`
**Assignment:** planning only. No code, test, migration or database change was made by this pass.
**Both issues are left in whatever state the evidence supports.** Neither is marked CLOSED here.

Labels used throughout: **VERIFIED** (directly supported by repository, publisher or client evidence) ·
**INFERRED** (reasoned, not confirmed) · **UNKNOWN** (evidence unavailable) · **BLOCKED** (named dependency
unresolved) · **PROPOSED** (recommended future work, not done).

> **Document-home note, stated because it affects where this file lives.** The assignment names
> `docs/plans/ki-82-ki-133-resolution-plan.md`. That directory does not exist, and
> `docs/research-scratch/INDEX.md` §File discipline says content that fits no master "should be proposed to
> the owner with a recommendation for which master to extend, rather than creating a new file". The owner
> named this exact path, which is the authorization the rule asks for, so it is written here. The subject's
> natural master, if this is ever folded, is `docs/research-scratch/PLANS-AND-BRIEFS.md` — it already owns the
> A-0.13 and R8 material this plan cites.

---

## 1. Executive summary

Two issues were assigned. **One of them is already fixed and its register entry is stale; the other is
genuinely blocked, but on a narrower dependency than its entry claims.**

| Issue | Headline finding | Readiness |
| --- | --- | --- |
| **KI-82** | **VERIFIED FIXED at `b637cec2`.** The parser carries `CLIENT_TITLES_BY_SLOT = ['final_live' => 'Grand Concert']` and a test asserts the parse of the committed body yields `Grand Concert`. The live database row 411 already reads `Grand Concert`. The finding describes the pre-fix state. | **The remaining work is a register correction, not a code change.** |
| **KI-133** | **Narrower than its entry says.** Its blocker is quoted as "A-0.13 roster", but per-member identity **and** a six-value state machine already exist as `turn_events` payloads (`SpiritBurstPayload`, `SpiritBurstState`, `TrainingRun::spiritBurstRoster()`). What does **not** exist is a per-member numeric gauge reading — and the client publishes no maximum for one. | **BLOCKED on a game-evidence question, not on the roster.** |

Two additional findings, both outside the assignment's scope but recorded because they bear on the same
code: a **second instance of the KI-82 defect class** on `unity_cup`'s finale row, and a **documented
decision** that makes that second instance intentional rather than a defect. See §7.1.

---

## 2. Current verified repository state

| Item | State | Evidence |
| --- | --- | --- |
| HEAD | `efdbb88d` | `git log --oneline -1` |
| Branch | `master` | `git rev-parse --abbrev-ref HEAD` |
| Commits to preserve | `72160eca`, `2ef286f6`, `efdbb88d` all present, unamended | `git log -1 --format=%s <sha>` |
| Peer-owned modified paths | 17, none touched by this pass | `git status --short` |
| Pending migrations | 0 | `php artisan migrate:status` |
| Dev DB | `current_spirit_proxy` present, `training_runs` 1 row, `integrity_check` ok | `PRAGMA table_info`, `PRAGMA integrity_check` |

**Peer-owned paths observed and left alone:** `CockpitController.php`, `TeamPanelBuilder.php`,
`config/scenarios.php`, `Cockpit.vue`, `NextDecisionTest.php`, `UnityCupPanelTest.php`,
`SpiritBurstCountdownTest.php`, `unity-cup-panel.spec.ts`, and the `docs/` folds (three deleted
`trainer-desk-v3x-*` reports, `PLANS-AND-BRIEFS.md`, `INDEX.md`, `CONSOLIDATION-LOG.md`).

**One caution for whoever implements KI-133.** `TeamPanelBuilder.php` is **peer-modified right now**. Any
change to `spiritBurstRoster()` consumption in that file must be coordinated, or it will collide.

---

## 3. KI-82 — findings

### 3.1 What the defect was

**VERIFIED.** `KNOWN-ISSUES.md` §KI-82 records that the fourth scenario's finale catalogue row carried the
title `"URA Finals Final (Grand Live)"`, sourced verbatim from the dataset's `name_en` for slot id
`final_live`. Two claims in it were wrong for `[Global]`:

- **"URA Finals"** — the first scenario's finale name pasted onto the fourth. The four finals share
  slot-shape, not name.
- **"Grand Live"** — GameTora's rendering of グランドライブ. Cygames' `[Global]` notice 905 prints
  **Grand Concert**, the same class of divergence conflict row 52 records.

### 3.2 It is already fixed

**VERIFIED.** Three independent confirmations:

1. **The parser carries a correction map.** `app/Services/DataPipeline/Parsers/GametoraRaceCatalogParser.php`:

   ```php
   private const CLIENT_TITLES_BY_SLOT = [
       'final_live' => 'Grand Concert',
   ];
   ```

   applied at line 186: `'title' => self::CLIENT_TITLES_BY_SLOT[$slotId] ?? (string) ($details['name_en'] ?? '')`.

2. **A regression test asserts the behaviour, reading the committed body.** `tests/Feature/GametoraRaceCatalogParserTest.php`:"carries the committed body for the fourth scenario's finale and titles it by the client noun" parses `database/seeders/data/race_instances.json` and expects `$finale['title']` to be `'Grand Concert'`. Its own comment states the intent: "if the file regresses to the borrowed name, the parse goes red here before any screen inherits it." **42 passed / 85 assertions** on the file.

3. **The live database already holds the corrected value.** Row 411:

   ```json
   {"id":411,"scenario_key":"our_grand_concert","title":"Grand Concert","turn":null}
   ```

   The dataset still carries `"URA Finals Final (Grand Live)"` verbatim — deliberately, so the committed
   body stays auditable — and the parser normalises it at read time.

**The fix landed at `b637cec2`** (`fix(scenario): Grand Concert finale title and one shared-inclusive read
(KI-82, KI-87)`).

### 3.3 Why the entry still reads OPEN

**INFERRED.** The entry was filed 2026-10-09 against the state before `b637cec2`, and nobody flipped it.
Its "Severity is latent" paragraph says the row is reachable only through `careerGoalSections()`, gated by
`$panels['career_goals']['on']` — and that Our Grand Concert composes no such panel. That reasoning was
about **visibility**, not about whether a correction existed, and it has since been overtaken.

### 3.4 Answers to the assignment's eight questions

| # | Question | Answer |
| --- | --- | --- |
| 1 | What is wrong in the current row? | **Nothing, on the read path.** The stored `race_catalog_slots` row says `Grand Concert`. The wrong string survives only in the committed dataset body, kept verbatim on purpose. |
| 2 | What should the canonical title be? | **`Grand Concert`** — the `[Global]` notice 905 wording, already what config, parser and DB all produce. |
| 3 | Data, import, config or rendering defect? | **Import/transcription**, at exactly one layer: the upstream export's `name_en`. Corrected in the parser's normalisation step, not in the data. |
| 4 | One row, or a naming convention? | **One row, via a one-entry map.** The other finals already carry their client nouns. |
| 5 | Downstream refs that must stay stable? | `external_ref = 'final_live'` is the join key and is untouched; `scenario_key` was corrected separately by KI-71. Nothing joins on the title. |
| 6 | Could a title change affect saved records? | **No.** No table stores the title per run; `race_entries` references the slot by id. |
| 7 | Should display and stored identity stay separate? | **Yes, and that is the shipped design.** The dataset keeps the export verbatim; the parser maps to the client noun at read time. This is the pattern already generalised in conflict row 52. |
| 8 | What regression test prevents recurrence? | **Already exists** — the test named in §3.2 note 2, which reads the committed body rather than a fixture. |

### 3.5 Proposed work — register correction only

**PROPOSED.** Change KI-82's status to **CLOSED**, stating:

- Closure commit `b637cec2`; the map is `CLIENT_TITLES_BY_SLOT` in
  `app/Services/DataPipeline/Parsers/GametoraRaceCatalogParser.php`; test file and case name as in §3.2.
- **What closure does not cover:** the committed dataset still carries the export's wrong string on purpose,
  and the `unity_cup` sibling case is intentionally named under §7.1. Neither is a regression.
- The "latent severity" paragraph is preserved as the historical reading, marked as overtaken.

**No code change is proposed, because none is needed.** That is the finding.

**Scope:** one file, `KNOWN-ISSUES.md`, one entry. **Data impact:** none. **Rollback:** revert one commit.
**Readiness: READY** as a documentation task.

---

## 4. KI-82 — proposed implementation plan

Not applicable to code. The plan is the register correction in §3.5, with this acceptance criterion:

| Criterion | Pass condition |
| --- | --- |
| Entry reflects the tree | KI-82 reads CLOSED with `b637cec2` cited, and no sentence in it asserts the defect as present |
| Fix is protected | `GametoraRaceCatalogParserTest` still asserts `Grand Concert` for `final_live` |
| No overclaim | The entry says the dataset keeps the export verbatim and that KI-71's key fix is separate |
| Latent-severity history kept | The 2026-10-09 reading survives as dated history, not deleted |

---

## 5. KI-133 — findings

### 5.1 What the game concept actually is

**VERIFIED**, from `KNOWN-ISSUES.md` §KI-133 and `docs/UMAMUSUME_REFERENCE.md` §2.2.2's 2026-10-25 note:

- The Spirit gauge is **per team member** — every member of the Unity team (deck supports, story characters,
  joiners) carries their own.
- It has **no published numeric maximum**. The client renders a bar with a flame icon.
- The burst is a **once-per-member availability**, with roughly a **40% per-turn chance** once full.
- There is a six-state machine, `app/Enums/SpiritBurstState.php`:

  `Chargeable → Charged → Held → NormalBurstSpent → ExtremeChargeable → ExtremeSpent`

### 5.2 The entry's stated blocker is wrong, and this is the important finding

**VERIFIED.** KI-133 says it is `blocked: shape decision + A-0.13` and both status lists describe the
per-member shape as waiting on a roster. **Per-member identity already exists**, and it is not new:

| What KI-133 says is missing | What the tree actually has |
| --- | --- |
| A roster to hold members | `TrainingRun::spiritBurstRoster()` (`app/Models/TrainingRun.php:1527`) derives members from `turn_events`, keyed by teammate name. **The limit is precise: it lists only members who have a burst event.** A member the Trainer has never recorded is absent, not `Chargeable` — which is why §6.4's last criterion demands that an absent member render as an absence rather than a default state |
| Per-member state | `app/Models/TurnEvents/SpiritBurstPayload.php` — `readonly class` with `teammate` (string, non-empty) and `state` (`SpiritBurstState`) |
| The state machine | `app/Enums/SpiritBurstState.php` — six cases, all mapped to labels |

`TeamPanelBuilder.php:540` already consumes it: `foreach ($run->spiritBurstRoster() as $row)`.

So the honest statement is: **identity and state are captured; a numeric gauge reading is not, and the client
publishes no maximum for one.** A-0.13's roster is about a *different* thing — the 20/20 team-members
**counter** (KI-125), which was declined per R8 at `c734a280` because no composed team-panel surface exists
for a roster *table*. KI-133's per-member Spirit data needs no such table: it rides `turn_events`, which is
already populated.

### 5.3 What is genuinely missing

**VERIFIED.** Two things, and only one is a game-evidence question:

1. **A per-member numeric gauge value.** No source read publishes one, and the client shows a bare bar with
   no printed maximum. **UNKNOWN, and probably unknowable** — that is why the `*Proxy` column exists.
2. **A decision on whether to model the gauge as a level/bucket rather than a number.** The six states are an
   ordered machine, so a **state-based** representation is available today with zero new evidence. A
   **value-based** one is not.

**INFERRED.** The reason KI-133 conflated these is that it was filed the same week as KI-125/A-0.13, both
touching "the roster", and the two got merged into one blocker sentence.

### 5.4 Does the proxy lose information?

**VERIFIED yes, and the loss is precisely characterisable.** `current_spirit_proxy` is one integer for the
whole team. It cannot express:

- **which** member is where in the six-state machine (that information exists in `turn_events` and is simply
  not joined to the proxy);
- that a member is `Held` (charged but deliberately untriggered) versus spent — a distinction that changes
  strategy;
- that a member is `ExtremeChargeable`, i.e. the burst was already used once.

So the proxy is not merely imprecise; it is **orthogonal** to the state data that already exists.

---

## 6. KI-133 — proposed implementation plan

### 6.1 Candidate representations — alternatives, not facts

| Option | Shape | Evidence needed | Data preservation | Migration cost |
| --- | --- | --- | --- | --- |
| **A. Status quo plus a join** | Keep `current_spirit_proxy`; derive the per-member board from `turn_events` on read, as `spiritBurstRoster()` already does | **None — buildable today** | Total. Nothing rewritten. | **Zero.** Read-only composition. |
| **B. Per-member table** | `spirit_gauges(training_run_id, member_slot_or_name, state, position)` | None for `state`; a virtue for `position` is still **UNKNOWN** | Full, additive | One additive migration; backfill derivable from `turn_events` |
| **C. Numeric per-member gauge** | `spirit_gauges(..., numeric_value)` | **BLOCKED** — no published maximum, no numeric client reading | Full | Same as B, but the column would be a labelled proxy again — the same compromise, multiplied by member count |

**PROPOSED reading:** **A is the correct first step** and needs no new evidence. **B is the right eventual
shape** if a `position`/bucket proves useful. **C should not be built** until a source publishes a maximum,
because it would replace one labelled proxy with N labelled proxies — strictly worse.

### 6.2 Safe interim behaviour

**PROPOSED.** Nothing to change. The shipped surface already does the right thing: `SpiritCard.vue` labels the
meter `Current Spirit (manual proxy)` and its absence line says the gauge's real shape is not what is stored.
That labelling was the substance of KI-133's closure on the proxy half, and it is correct.

### 6.3 Regression test matrix

| Test | Proves | Status |
| --- | --- | --- |
| `UnityCupPanelTest` (existing, 12 tests / 261 assertions) | the proxy round-trips and the label is `(manual proxy)` | **exists** |
| `TurnEventTypePayloadsTest` (existing) | `SpiritBurstPayload` rejects a blank teammate and a seventh state | **exists** |
| **New:** `SpiritBurstRosterTest` | one member at `Held` and another at `ExtremeChargeable` both appear, keyed by name, in `spiritBurstRoster()` order | **PROPOSED** |
| **New:** a cockpit-surface case | the six-state board renders each state's own label, and never a numeric gauge | **PROPOSED**, gated on UI work |
| **New:** proxy/state independence | writing `current_spirit_proxy` does not alter any member's state, and vice versa | **PROPOSED** — this is the test that pins §5.4 |

### 6.4 Acceptance criteria for any KI-133 implementation

| Criterion | Pass condition |
| --- | --- |
| No fabricated member values | Every rendered per-member row traces to a `turn_events` payload row |
| No inferred numeric gauge | No surface prints a per-member number unless a source publishes a maximum |
| Proxy not silently promoted | `current_spirit_proxy` keeps its `(manual proxy)` label; it never appears as a game reading |
| Historical records intact | Existing runs render their roster from `turn_events` with no backfill required |
| Uncertainty visible | A member with no payload row renders as an absence, not as `Chargeable` |

### 6.5 Conditions before implementation begins

**BLOCKED — but not on A-0.13.** The real gates are:

1. **Owner decision:** is the six-state per-member board in scope for the Unity Cup surface at all? That is a
   product question, and §6.1's option A is buildable without it.
2. **Evidence, only for option C:** a source that publishes a per-member gauge maximum. Without it, C stays
   unbuilt.

**A-0.13's roster is NOT a prerequisite** for options A or B, and the entry's dependency is a misattribution
that should be corrected.

**The precise difference, because it matters for anyone implementing this.** A-0.13 is about a roster that
**enumerates the team** — the 20/20 counter wants to know every member, including one who has done nothing
yet. `spiritBurstRoster()` answers a narrower question: *which members have a recorded burst state*. Those are
different data needs, and only the narrower one is satisfied. So the honest statement is not "the roster
already exists" but **"per-member state exists for members who have one; enumeration of the full team does
not, and options A and B do not require it."** Option B's `member_slot_or_name` would inherit the same limit
unless a team-enumeration column is added later, which is A-0.13's actual subject.

---

## 7. Evidence gaps and external dependencies

### 7.1 A second KI-82-class case, already decided

**VERIFIED.** Database row 408 reads:

```json
{"id":408,"scenario_key":"unity_cup","title":"URA Finals Final (Aoharu)","turn":null}
```

The dataset's `final_aoharu` row carries `name_en: "URA Finals Final (Aoharu)"` and
`name_jp: "URAファイナルズ決勝 (アオハル杯)"`. This is the same "URA Finals" borrowing KI-82 describes — but
`docs/scenarios/02-unity-cup.md:21-24` records it as **intentional**: Unity Cup's calendar forks from URA
Finale's at "the forked final (`URA Finals Final (Aoharu)`)", named by the Japanese scenario name Aoharu Hai.
The entry should say so rather than leaving a reader to re-file it as KI-82 again.

**PROPOSED:** note it inside KI-82's closure. **No code change** — the name is the documented one.

### 7.2 Evidence still missing

| Item | Status | What would close it |
| --- | --- | --- |
| Per-member Spirit gauge maximum | **UNKNOWN** | A `[Global]` Help topic or client frame showing a gauge with a printed maximum. None exists in `docs/game-screenshots/` |
| Whether `Held` is distinguishable on the client | **UNKNOWN** | One frame of a charged-but-untriggered member |
| A-0.13's roster for a 20/20 counter | **BLOCKED**, declined per R8 (`c734a280`) | Owner re-opening R8. **Not needed by KI-133** |

---

## 8. Cross-issue sequencing

| Question | Answer |
| --- | --- |
| Shared components? | **No.** KI-82 is pipeline/parser and register; KI-133 is Unity Cup read-model and UI. |
| Shared tests or fixtures? | **No.** Different suites; `GametoraRaceCatalogParserTest` versus `UnityCupPanelTest`/`TurnEventTypePayloadsTest`. |
| Naming/data-contract conflicts? | **One, already resolved:** both touch `finale_label`-adjacent naming, and `72160eca` settled the advisor side. KI-82's remaining work touches no config. |
| Can they be implemented independently? | **Yes.** KI-82 is a documentation correction that could land immediately; KI-133 needs an owner scope decision. |
| Risk in combining? | **Material.** KI-133 touches `TeamPanelBuilder.php`, which a peer is editing *right now*. Bundling them would put a live-collision file in a commit about a register entry. |
| Recommended order | **KI-82 register correction first** (minutes, no code), then KI-133's entry correction (fix the wrong blocker), then KI-133 implementation only after the owner's scope decision. |

**Acceptance criteria stay independent.** Neither commit should mention the other's criteria.

---

## 9. Regression and acceptance matrix

| Area | KI-82 | KI-133 |
| --- | --- | --- |
| Test files | `tests/Feature/GametoraRaceCatalogParserTest.php` (exists) | `tests/Feature/UnityCupPanelTest.php`, `tests/Feature/TurnEventTypePayloadsTest.php` (exist); `SpiritBurstRosterTest` (proposed) |
| Current evidence | 42 passed / 85 assertions | 12 passed / 261 assertions |
| New assertions needed | **none** | roster ordering, six-state rendering, proxy/state independence |
| Gates | `md_fence_check` on the entry, lore gate | Pint, PHPStan, typecheck, `lint:ui`, browser spec if UI moves |
| Must not regress | the parser map and the body-reading test | the proxy round-trip and the `(manual proxy)` label |

---

## 10. Risks and rollback

| Risk | Mitigation |
| --- | --- |
| Closing KI-82 is read as "the dataset was fixed" | The closure text must say the committed body keeps the export verbatim on purpose |
| KI-133's wrong blocker is quoted onward | Correct it in the entry; say explicitly that A-0.13 is not a prerequisite |
| Option C gets built on a guess | Acceptance criterion forbids a per-member number without a published maximum |
| `TeamPanelBuilder.php` collision | KI-133 work waits for the peer to land, or edits a different file |
| Rollback | Both are additive. KI-82 is docs-only. KI-133 option B is one additive migration with a real `down()`; option A is read-only and needs no rollback |

---

## 11. Implementation readiness decision

| Issue | Readiness | Basis |
| --- | --- | --- |
| **KI-82** | **READY** | The code fix is already at `b637cec2` with a regression test. What remains is a one-entry register correction, and the evidence for every sentence of it is in §3. |
| **KI-133** | **BLOCKED** — on an owner scope decision, **not** on A-0.13 | Option A is buildable today from existing `turn_events` data. Options B/C need a decision and, for C, a game source that does not exist. |

**Neither issue is marked CLOSED by this document.** KI-82's closure is proposed in §3.5 and should be
committed as its own documentation change; KI-133's entry needs its blocker corrected before anyone plans
against it.
