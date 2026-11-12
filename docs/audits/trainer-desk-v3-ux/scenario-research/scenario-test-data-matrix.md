# Scenario test-data matrix

**Phase 0 deliverable 5 of 6.** The cross-scenario table the main UI/UX audit works from.

A cell says what the audit must **do** for that area in that scenario, not whether the app passes. Pass/fail is
the audit's output; this document is its input.

**Legend**

| Mark | Meaning |
|---|---|
| **REQUIRED** | A new walk. No existing evidence covers this cell |
| **REF (existing)** | Covered by the Unity Cup reference run plus eight walks. **Retest, do not re-derive** |
| **SHARED** | The surface is scenario-agnostic; one walk covers all four. Do not repeat per scenario |
| **BLOCKED** | Cannot be exercised without data that does not exist. The reason is given |
| **N/A** | The scenario composes nothing here. A walk must confirm the **absence renders**, which is a different assertion |

---

## 1. The matrix

The brief's own 15 rows, plus three this pass measured as necessary (§4).

| Test area | URA Finale | Unity Cup | Trackblazer | Our Grand Concert |
|---|---|---|---|---|
| Scenario selection | REQUIRED | REF (existing) | REQUIRED | REQUIRED |
| Trainee setup | SHARED | SHARED | SHARED | SHARED |
| Target handling | REQUIRED (caps 1400 ×5) | REF (existing) | REQUIRED (1200/1900/1200/1200/1500) | REQUIRED (1600/1300/1300/1500/1300) |
| Legacy setup | SHARED | REF (existing) | SHARED | SHARED |
| Support deck | SHARED + 1 REQUIRED (banned card) | REF (existing) | SHARED | SHARED |
| Preflight | REQUIRED (duel rows present) | REF (existing) | REQUIRED (duel + fan gates absent) | REQUIRED (both absent) |
| Cockpit | REQUIRED | REF (existing) | REQUIRED | REQUIRED |
| Scenario mechanics | REQUIRED | REF (existing) | REQUIRED | REQUIRED |
| Training | SHARED + 1 REQUIRED (Performance pair) | SHARED (Unity extras unrepresented) | SHARED | SHARED + 1 REQUIRED |
| Races | REQUIRED (calendar on) | REF (existing) | REQUIRED (calendar off, planner on) | REQUIRED (calendar off) |
| Skills | SHARED | REF (existing) | SHARED | SHARED |
| Events | SHARED | REF (existing) | SHARED | SHARED |
| Inheritance | SHARED | REF (existing) | SHARED | SHARED |
| Timeline | SHARED | REF (existing) | SHARED | SHARED |
| Result | REQUIRED (completed run exists in seed) | **BLOCKED** | **BLOCKED** | **BLOCKED** |
| Veteran creation | REQUIRED | REF (existing) | BLOCKED | BLOCKED |
| Legacy Lab reuse | SHARED | REF (existing) | SHARED | SHARED |
| **Scenario detail** | REQUIRED | REF (existing) | REQUIRED | REQUIRED |
| **Grade/points economy** | N/A | N/A | REQUIRED | N/A |
| **Pro Shop purchase** | N/A | N/A | REQUIRED | N/A |

---

## 2. Why the scenario walk order is Trackblazer → URA → Our Grand Concert

Measured, not preferred.

| Scenario | Scenario-specific code | Feature files naming it | Browser specs naming it | Human walks |
|---|---|---|---|---|
| Trackblazer | 1 service (a 759-line panel builder) + 5 Vue components | 48 | 10 | **0** |
| URA Finale | 1 component + the duel/objective arms in the cockpit | 81 | 10 | 0 |
| Our Grand Concert | 1 enum + 2 controller arms + the training pair | 28 | 4 | 0 |

**Re-derive all three columns:**

```bash
for k in trackblazer ura_finale our_grand_concert; do
  printf '%s: %s feature, ' "$k" "$(grep -rl -- "$k" tests/Feature | wc -l)"
  printf '%s browser\n' "$(grep -rl -- "$k" tests/browser | wc -l)"
done
wc -l app/Services/Career/TrackblazerPanelBuilder.php
ls resources/js/components/scenario/ | grep -ci trackblazer
```

**All three figures agree exactly with the repository's own claim** (48 / 81 / 28 feature files; 10 / 10 / 4 browser
specs, per `audit-methodology-and-remediation-specs.md` §4). A scenario-*name* grep gives different totals, and the
difference is the match pattern, not the tree.

**Trackblazer first** because it has the most code and the least evidence, and because its 759-line panel builder
carries arithmetic no test walks. **URA second** because its ladder is the most-tested and its walk adds least,
but it is the baseline every other scenario descends from. **Grand Concert last** because its largest surface is
an absence, and an absence is the cheapest thing to confirm — but see §5, because that ordering interacts with a
blocked cell.

**This inverts the earlier plan.** `docs/audits/audit-methodology-and-remediation-specs.md` §4.4 sequences
`Trackblazer → Our Grand Concert → URA Finale`. The change is Grand Concert moving from second to last, on the
ground that its three most expensive states (§3) are all BLOCKED and its reachable states are cheap.

---

## 3. The blocked cells, with what each needs

Three Result cells and one Veteran cell cannot be walked. This is the same hole the v3 audit recorded, and it is
worth restating precisely because it is the matrix's largest gap.

| Cell | Why blocked | What would unblock it | Cost |
|---|---|---|---|
| Trackblazer Result | A completed Trackblazer career does not exist in any seed, and creating one needs a 72-turn play-through or a status write | A committed seeder that files a completed Trackblazer career from a real run record | **High** — needs a player who finished one and is willing to have it recorded |
| Grand Concert Result | Same | Same | High |
| Unity Cup Result | Same | Same | High |
| Trackblazer / GC Veteran creation | Veteran creation needs a **Completed** run first, and completing one is blocked above | The same seeder, then `RecordVeteran` | Follows the Result row |

**A synthetic completed career is the obvious shortcut and it is not available for these cells.** §5 of the
brief permits a synthetic fixture for exercising application UI, and a completed-run fixture would satisfy that
rule on its face. But the Result screen's whole job is to report **what a real career produced**, and a fabricated
final stat block is exactly the class of evidence the brief forbids: it would be a completed career presented as
an observation.

**What a synthetic fixture CAN do here, and is worth doing:** prove the **active-run refusal** on all three
scenarios' Result screens (which is the branch that does not need a completed career), and prove that a Veteran
save is **refused** on an Active run. Both are real assertions about real code, and neither fabricates a career.

```bash
# the refusal branch, on a run the walk created
curl -s -o /dev/null -w '%{http_code}\n' /training-runs/{id}/result   # expect a rendered refusal, not a 500
```

---

## 3A. The safe database strategy, and the three kinds of test information

The brief's §0.5 requirement, stated as the rule every walk in this programme follows.

### 3A.1 Three kinds of test information, and how a reader tells them apart

| Kind | Definition | How it is marked in a report |
|---|---|---|
| **Observed** | Something read off the game, a client screenshot, or a publisher's page | Carries a source id from `scenario-research-sources.md`, or a frame path |
| **Verified** | A fact from an authoritative source that this application does not itself store | Carries the source id **and** the note that the app does not model it |
| **Synthetic** | A value the audit invented to exercise a form or a branch | Carries the word **SYNTHETIC** in the report and in the fixture's own name, and never appears in a screenshot that could be read as evidence |

**The rule that binds all three:** a synthetic value may exercise the application, and it may never be reported
as a game observation. A report that says "the cockpit shows Speed 620" without saying where 620 came from is a
defect in the report, not in the app.

### 3A.2 What a synthetic fixture may and may not do

| Allowed | Forbidden |
|---|---|
| Fill a form the walk is testing | Enter a completed career's final stat block and call it a result |
| Set a status to Completed to reach a screen's rendered branch | Present an invented career as a run that happened |
| Create a run through the wizard, which is the app's own validation | Insert rows the app's own requests would refuse |
| Use the catalogue's real 139 trainees, 559 cards, 410 race slots, 1,910 skills | Modify `database/seeders/data/*.json` to make a fixture look better |
| Write to a session-private scratch database | Write to `database/database.sqlite` |

**Prefer the app's own paths.** `RosyDreamsRiceShowerSeeder`, `UraFinaleRunsSeeder` and `TrackerVeteransSeeder` are
committed, idempotent and already carry real careers. A walk that needs a mid-career state should reach for one of
them before it builds anything.

### 3A.3 The two databases, and which one is never touched

| Database | Path | Use |
|---|---|---|
| **Shared development file** | `database/database.sqlite` | **Read-only, and only through a copy.** Never opened by a browser session, never written |
| **Session scratch — populated** | `.scratch-uma/<session>/audit.sqlite` | A byte copy of the dev file, plus the committed seeders. The walk's working copy |
| **Session scratch — empty** | `.scratch-uma/<session>/empty.sqlite` | A `migrate:fresh` on a copy of the populated one. The first-run persona |

Two rules the v3 audit established and this programme keeps:

1. **The empty persona is built by `migrate:fresh` on a copy**, never by truncating tables in the populated copy.
   Truncation keeps schemas and preferences and loses catalogue rows, which is a state no install ever has.
2. **Build the empty copy from the populated one after seeding**, so the two personas differ only in the data the
   walk itself adds.

### 3A.4 The exact commands

```bash
# 1. the working copies, both under one session directory
SESSION=scenario-walks-2026-11-12
mkdir -p .scratch-uma/$SESSION
cp database/database.sqlite .scratch-uma/$SESSION/audit.sqlite

# 2. the committed careers, so the walk is not the only data in the file
DB_DATABASE=.scratch-uma/$SESSION/audit.sqlite php artisan db:seed --class=RosyDreamsRiceShowerSeeder --no-interaction
DB_DATABASE=.scratch-uma/$SESSION/audit.sqlite php artisan db:seed --class=UraFinaleRunsSeeder --no-interaction
DB_DATABASE=.scratch-uma/$SESSION/audit.sqlite php artisan db:seed --class=TrackerVeteransSeeder --no-interaction

# 3. the first-run persona, from a copy of the seeded file
cp .scratch-uma/$SESSION/audit.sqlite .scratch-uma/$SESSION/empty.sqlite
DB_DATABASE=.scratch-uma/$SESSION/empty.sqlite php artisan migrate:fresh --force --no-interaction
```

**Count the rows before and after**, and put both numbers in the report. A walk that cannot say how many runs
existed before it started cannot say which runs are its own.

### 3A.5 A synthetic fixture, when one is genuinely needed

No walk in this programme needs one for a **surface**. The reachable cells all have catalogue data. A fixture is
needed only if a walk must reach a state the app can produce but the seed does not supply — and then the cheapest
honest fixture is the app's own factories:

```bash
php artisan tinker --execute="require base_path('.scratch-uma/$SESSION/fixture.php');"
```

...where `fixture.php` uses `TrainingRun::factory()`, `TurnEntry::factory()`, `TurnEvent::factory()` and
`RaceEntry::factory()` with explicit state. **Never** a hand-written `INSERT`. The factories carry the casts, the
`priceGradePoints()` hook and the run's own relations, and a raw insert silently skips all three.

**A fixture of this kind is always disposable.** It lives in the session's scratch database, its file is named
with `SYNTHETIC` in the path, and the report states which screens were reached through it. Nothing about it is
committed.

---

## 4. Three rows the brief's matrix did not carry

Added because this pass measured them as separate, load-bearing surfaces.

### 4.1 Scenario detail (`/training-runs/{run}/scenario`)

A route of its own since `v3.0.0`, and the only place four of the scenarios' panels are reachable. The v3 audit's
§8 measured it for all four and found composition correct; the retest confirmed. **A walk that skips this route
skips every Trackblazer panel.**

### 4.2 Trackblazer's points economy

Not a screen — a set of assertions that span the cockpit, the scenario detail and the race decision screen:
the four-row ladder, the entered-plan total, the two placement floors, the unpriced count, the spend total, the
ledger's held counts and its inflow projection. Eight distinct readings, three surfaces. No existing test walks
them as a unit.

### 4.3 The Pro Shop's purchase flow

The one write in the set that a Trainer completes end to end: catalogue → form → validation → `turn_events` row →
spend total → ledger entry. The v3 retest recorded this as PARTIAL, **purchase flow not exercised**, and named the
reason: no seeded recorded-turn state exists for the scenario. That reason no longer holds — a walk can create
the run and record the purchase itself.

---

## 5. What each scenario can be walked for, honestly

| Scenario | Fully testable | Testable with a synthetic fixture | Partially testable | Not implemented | Not applicable by design |
|---|---|---|---|---|---|
| **URA Finale** | selection, target, preflight, cockpit, career-goals ladder, obligation rail, races, skills, events, inheritance, timeline, result, veteran, legacy reuse | none needed — all surfaces have data | the duel **rules** print but no duel **outcome** can be recorded; the fan-gate **bond** half cannot be evaluated | a duel-outcome record; a bond reading | a Performance field; a grade economy; a shop |
| **Unity Cup** | everything the reference run covers, already walked 8× | none needed | Spirit Burst and Unity Training are counted, never modelled as decisions | Unity Training as a turn choice | a grade economy; a shop; a Performance field |
| **Trackblazer** | selection, target, preflight, cockpit's five sections, ladder, entered plan, floors, epithets, fatigue, purchase flow, spend total, race planner | none needed for the reachable set | a **coin balance** cannot be read; the variant point tracks are not selectable; rotation/refresh/stock are unmodelled | a coin balance; shop rotation; a bond reading | a race calendar; a career-goals ladder; a duel |
| **Our Grand Concert** | selection, target, preflight, cockpit, scenario detail, Performance pair, panel absence, caps table | none needed for the reachable set | a Performance **balance** cannot be read; songs, lessons and concerts cannot be recorded | lesson purchases; song counts; concert results; a Hype value | a race calendar; a shop; a team block; a grade economy |

**The honest summary of that table:** every scenario is walkable end to end for its **record-keeping** surfaces,
and none is walkable for its **balance** surfaces. That is one architectural condition (§4 of
`scenario-information-gaps.md`), not four separate failures, and the audit should say so once.

---

## 6. Evidence that already exists, per scenario, and what it is worth

| Scenario | Existing evidence | What it establishes | What it does not |
|---|---|---|---|
| Unity Cup | `[Rosy_Dreams]Rice_Shower_Unity-Cup.md`, 68 frames, `RosyDreamsRiceShowerSeeder` (turn 67 of 72), 8 walk reports; the scenario's own mechanics table at B4 | A mid-career state, in detail, with a real deck and real stats | Anything about the other three scenarios; anything about turn 1 |
| URA Finale | `UraFinaleRunsSeeder` — 8 completed careers from the committed tracker; 77 feature files | Result screens, veteran filing, legacy reuse | The wizard, the cockpit at turn 1, the ladder's live states |
| Trackblazer | 50 feature files, 12 browser specs, 0 walks | The panel payloads and their keys | What a Trainer sees; whether the arithmetic reads |
| Our Grand Concert | 29 feature files, 8 browser specs, 0 walks | The Performance enum and payload; the panel-absence contract | The lesson/song/concert loop entirely |

**The Unity Cup reference is a reference, not a baseline.** It proves the scenario composes; it does not prove the
application still behaves. The retest re-walked it and confirmed; a third walk is not needed.

---

## 7. Sequencing, with the dependency that actually binds

```text
1. Trackblazer walk      ← highest code density, zero human evidence
2. URA Finale walk       ← the baseline ladder; cheapest of the three to walk
3. Our Grand Concert     ← largest surface is an absence; confirm the absence, record the blocked cells
4. Unity Cup retest      ← only if 1–3 changed a shared surface
```

**The binding dependency is not ordering, it is `F-03` and `F-08`.** Both were specified in
`docs/audits/audit-methodology-and-remediation-specs.md` §2 and §3, and both are silent data-loss defects on
surfaces all four walks cross:

| Id | Defect | Surface every walk crosses |
|---|---|---|
| `F-03` | The spark `Target` combobox stores `null` while showing typed text | `/career/setup/legacy` — step 5 of the wizard, all four scenarios |
| `F-08` | Choosing `Race` destroys the training form's 21 entered values | `/training-runs/{run}/training` — all four scenarios |

**Per this repository's state on 2026-11-12, both are addressed at HEAD, and the fix set is three commits rather
than two.** Verified with `git log`:

| Commit | Subject | What it fixed |
|---|---|---|
| `282e5437` | `fix(legacy): a spark target shown in the field is now the target stored` | `F-03`. `SparkTargetPicker.vue` +99 lines, `BaseSelect.vue` +7, and a 252-line new browser spec |
| `51d78d3e` | `fix(training): ask before the Race action discards an entered turn` | `F-08`'s **confirm guard** — the dirty-check that prompts before the hard navigation |
| `3aee95e2` | `fix(training): keep an in-progress turn as a per-tab draft (F-08)` | `F-08`'s **persistence** — `TrainingDetail.vue` +147 and a 414-line spec |

**Three things this changes for the walks:**

1. `F-08` is addressed **twice, by two different mechanisms** — a confirm guard and a draft. A walk must probe
   both, because they can disagree: a guard that fires on a clean form is as bad as a guard that never fires, and
   a draft that restores stale values is worse than no draft.
2. `F-03`'s commit subject claims the displayed value now equals the stored one. The walk should verify the
   **third** state of the W3C sequence the spec was written against: type, select, **save, cold reload, read back**.
   The first two states pass in a component that is still broken.
3. The consolidated remediation plan's `F-13` remains an open re-observation and is unaffected.

**The walks should re-probe all of this as their first two states on the first scenario they reach, and record
the verdict once, rather than three times.**

---

## 8. What "done" means for a scenario walk

Five conditions, and a walk that meets four is not done.

1. Every route in `*-test-case.md` §7 (or §5 for Trackblazer and Grand Concert) was reached, or the reason it was
   not is recorded with the route named.
2. Every state in the test case's state table carries a rendered observation — a string, a payload key, a
   persisted row — not an impression.
3. Every absence was checked against the four-layer rule in `docs/framework-ui-ux/_audit-prompt.md` §6.4 before it
   was filed as an absence. **An absence claim that reached only the screen layer is `UNVERIFIED`, never
   `ABSENT`.**
4. The three refusals each scenario declares (see the test cases' "what this does not establish" sections) are
   recorded as **passing** states if they still hold, and as findings if they do not.
5. The walk's own created runs are named in its report, so a reader can tell the walk's data from a seed's.

---

## 9. Cross-references

| Document | What it owns |
|---|---|
| `scenario-research-sources.md` | The URLs, dates, regions and tiers every citation resolves to |
| `ura-finale-test-case.md` | URA's mechanics, ladder, deck, 15 states, 10 assertions, 19 screens |
| `trackblazer-test-case.md` | Trackblazer's mechanics, the vocabulary conflict, 20 states, 10 assertions |
| `our-grand-concert-test-case.md` | Grand Concert's mechanics, the closed `❌` list, 17 states, 10 assertions |
| `scenario-information-gaps.md` | 17 gaps, 9 conflicts, 4 rulings owed, 8 missing-data needs |
| `../04-ux-findings.md` | The prior register these walks extend |
| `../09-consolidated-remediation-plan.md` | The owner-ordered fix sequence the walks must not pre-empt |

**Counts in this document, re-derivable:** 3 blocked Result cells + 1 blocked Veteran cell (§3); 3 added matrix
rows (§4); 4 walk steps (§7); 5 done conditions (§8).
