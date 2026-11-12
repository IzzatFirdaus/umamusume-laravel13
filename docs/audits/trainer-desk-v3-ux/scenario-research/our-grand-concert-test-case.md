# Our Grand Concert test case

**Phase 0 deliverable 3 of 6.** A representative career that exercises the Our Grand Concert surfaces of Trainer
Desk. Sources are cited by the ids in `scenario-research-sources.md`.

**Scenario key:** `our_grand_concert`. **Client name:** `Brighter Together! Our Grand Concert.` [A3][A7][B3] —
with a full stop inside the quotation, per the official notice, and without one per the export. **Live on Global:**
2026-07-22 — announced 2026-07-20 [A6], celebrated 2026-07-23 [A7] and followed by the support event on 2026-07-27 [A8], and the scenario's own page carries the same
date [A12] — [B3]. **Caps:** Speed 1600, Stamina 1300, Power 1300, Guts 1500, Wit 1300 [B3][C4], implemented in
`config/scenarios.php` as `cap_bonus` `[400, 100, 100, 300, 100]`.

**This is the scenario Phase 0 changed the most.** Five Global client frames were found (§4 of the sources
register), and they settle three separate items that `docs/scenarios/07-grand-concert.md` records as
`❌ UNVERIFIED`. §2 and §7 below say which claims upgrade and which do not.

---

## 1. What is verified, and the boundary that moved

`docs/scenarios/07-grand-concert.md` carries an `❌ UNVERIFIED` list. Before this pass it read, in part: *"the two
bonus layer names, the 23 Song titles and their language, the Lesson confirm and reserve button labels, and the
gauge as rendered"*, with the reason given as **0 frames**.

**The frames exist now.** They are not committed (see the sources register §4 for why), so this document quotes
what they carry and states the boundary that remains:

| Item on the old `❌` list | State after this pass | Evidence |
|---|---|---|
| The resource's name | **CLOSED.** The client prints `Performance Points`, not bare `Performance` | `go_perfpoints.jpg`, `go_songs.jpg` |
| The five type names | **CLOSED.** `Dance` `Passion` `Vocals` `Visuals` `Composure`, with abbreviations `Da Pa Vo Vi Co` | `go_perfpoints.jpg` |
| The two bonus-layer names | **CLOSED.** The mastery layer prints as a plain effect (`Speed +22`); the second layer prints as `Specialty Priority +5` / `Friendship Training Effectiveness +5%` / `Support Chain Event Frequency Lvl +1` | `go_songs.jpg` |
| Song titles and their language | **CLOSED, all 23, and the language is English.** The frames show three; the automation codebase's string table (A14) carries all of them, and the two agree where they overlap. The full list is the sources register §5A | `go_songs.jpg`, A14 |
| The Lesson screen's own wording | **CLOSED.** Title `Lessons`; instruction `Select a technique or song to learn.`; cost heading `Performance Point Cost`; state pills `Learn` / `Learnable!` / `Locked`; the refusal `Not enough performance points`; the card group headings `Stat Gains` / `Skill Hints` / `Support Events` / `Totals` | `go_lessons.jpg`, A14 |
| The gauge as rendered | **PARTIALLY CLOSED.** Global frame shows `Hype Level` and the state word `Mild Hype`. The full state ladder and the `Special Hype!` variant are still unmeasured | `ug_gc_ui_hype_level.webp` |
| The reserve button's label | **STILL OPEN, narrowed.** A14 gives the *refusal* that leads to a reserve (`Not enough performance points`) and the three card states, but no frame or string table shows the scheduled state's own button. The client's reserve word remains unmeasured | A14 (partial) |
| Per-level magnitudes (lesson costs, hype per song, live-bonus ladder) | **STILL OPEN.** The frames show costs for three songs in one run state; they do not establish a cost curve | — |

**The one place the frames contradict the repository's own record:** `docs/scenarios/07-grand-concert.md` gives
`Speciality Rate Up` for the second bonus type. The client prints **`Specialty Priority`**. Those are the same
bonus under two words, and the client's is the one a surface may print. Filed in
`scenario-information-gaps.md` §2.

---

## 2. Verified mechanics

| Mechanic | Value or rule | Source | Tier |
|---|---|---|---|
| Stat caps | Speed 1600 · Stamina 1300 · Power 1300 · Guts 1500 · Wit 1300 | B3, C4 | B |
| The resource | `Performance Points`, five types | A13, B3 | A |
| Where it comes from | Training. The type a training gives **changes each turn**; friendship training gives **two** types at once | A13, B3, C2 | A |
| Per-facility lean | Speed → Dance/Visual · Stamina → Passion/Vocal · Power → Vocal/Composure · Guts → Visual/Dance · Wit → Composure/Passion | C2 | C |
| Points per training | Roughly 10 at facility level 1 alone; Wit ~6. More with level and with support present | C2 | C |
| Cap per type | Starts at **200**; every concert raises it by **50**, Great Success or not | C2, B3 | C |
| Lessons cost no turn | Several lessons may be taken in one turn; the list refreshes after each | A13, B3, C2 | A |
| Lesson kinds | `Technique` and `Songs` — the client's card pills | A13 | A |
| Technique effects | Stats, Skill Points, a skill hint, or Energy recovery | A13, B3 | A |
| Song effects | Two layers: one immediate, one that switches on after the next concert and lasts to the end of the career | A13, B3 | A |
| The second layer's three kinds | `Specialty Priority`, `Friendship Training Effectiveness`, `Support Chain Event Frequency` | A13 | A |
| Reserving an unaffordable lesson | Possible; the required points print above the training screen | A13, C2 | A |
| Technique-then-song pattern | Before concert 1: `1-2-3` then `4-4-2-3`. Before concerts 2–4: `2-2-2` then `4-5-2-2`. Before the Grand Concert: `2-2-2` then `4-3-2-2` | B3, C2, C7 (all three agreeing) | B |
| Number of songs | 23 total: 21 from lessons, `Make Debut!` automatic, `GIRLS' LEGEND U` at the finale | B3, C2 | B |
| Total cost of all 21 lesson songs | Dance 252 · Passion 201 · Vocal 150 · Visual 275 · Composure 196 | B3, C2 | B |
| Concerts | Five: four Promo Concerts every six months from Junior late December, then the Grand Concert in Senior late December | A13, B3, C2 | A |
| Great Success | Three **new** songs since the last concert fills the gauge and guarantees it | A13, C2 | A |
| Hype resets | Each concert resets the gauge | A13 | A |
| 16-song threshold | Senior early November: the lyrics event fires, offering five lines whose choice decides the hint | B3, C2 | B |
| 18-song threshold | Senior early December is the last turn; 18 songs earns the `I Wanna Win with You` hint and the **special** `GIRLS' LEGEND U` at the Grand Concert | A13(via C8), B3, C2 | B |
| The special version's marker | `Special Hype!` with a gold border, and the line "The next concert is shaping up to be a special one." | B3 | B |
| Career goals | **Present.** `returns the standard trainee career features of race goals and career events` | B3, C2 | B |
| The finale | The four Global scenarios all close with the three URA Finale races unless overridden; the Grand Concert itself takes place in Senior late December | C2, B3 | C |
| Scenario links | Light Hello, Smart Falcon, Agnes Tachyon, Silence Suzuka, Mihono Bourbon | B3 | B |
| Scenario spark | `Grand Concert`, Speed + Guts | B3 | B |
| Banned support card | SSR `[Passing the Dream On] Team Sirius` cannot be used here | B3 | B |
| Secret events | **Back.** Unlike Trackblazer, character secret events fire, so a Runaway-style gate is reachable | B3, C2 | B |

---

## 3. Trainee, Legacy and deck

**Trainee: Silence Suzuka.** The suggestion in the brief, and it is a good one for a reason worth stating: she is a
**scenario link character** for this scenario [B3], which means a surface that reads the deck's scenario-link
status has something real to read. She also has the secret-event contrast with Trackblazer (§4 of the Trackblazer
test case) — the same trainee, two scenarios, opposite behaviour on one mechanic.

Her ladder in the local catalogue (measured 2026-11-12) is eleven rows: a `Participate`, a **`Fan` goal at 5,000**,
five placements including two wins, and the three `FinalRound` rows. The `Fan` goal is the one goal type with no
race attached and is worth exercising here because Grand Concert's own gate structure is fan-based too.

**Legacy.** Two parents carrying **Speed and Guts** sparks. Rationale: this scenario's cap bonus is
`Speed +400, Guts +300` over everything else, so a legacy that reinforces those two compounds the scenario's
shape. Recommendation, not a sourced requirement.

**Support deck.** Six slots, slot 6 Friends. The published deck shape [C5, B3] is `3 Speed / 2 Wit / 1 Pal`, or
`2 / 2 / 1` plus one free [B3 tier A/B, recorded in `docs/scenarios/07-grand-concert.md`]. The test uses:

| Slot | Type | Why this slot exists in the test |
|---|---|---|
| 1–2 | Speed | The framework's base; Speed's cap is the scenario's highest |
| 3–4 | Wit | The framework's base |
| 5 | Pal / Friend-type | Exercises the friend slot AND the scenario-link path, since Light Hello is a friend-type link |
| 6 | Friend | The friend-slot rule |

**One deck fact this test can verify that no other scenario can.** Grand Concert **bans** `SSR [Passing the Dream
On] Team Sirius` [B3]. If the deck picker offers that card for a Grand Concert run, that is a finding. No other
scenario in the set has a banned card, so this is the only walk where the rule is testable.

---

## 4. Starting career state

| Field | Value | Source of the value |
|---|---|---|
| Trainee | Silence Suzuka, debut form | The catalogue (`umamusume.id = 3`) |
| Scenario | `our_grand_concert` | The wizard step 1 card |
| Build purpose | Mile or Medium, Front Runner | Her own aptitudes |
| Target | Speed-led to 1600, Guts to 1500 | The scenario's cap shape |
| Legacy | Two parents, Speed and Guts sparks | §3 |
| Deck | Per §3, including the banned-card check | §3 |
| Performance points | **Not recorded.** No column holds a Performance balance | `TrainingRun::performanceObservations()` returns observations, never a total |
| Songs learned | **Not recorded as a count.** No column holds one | Measured: no `song` or `hype` write path exists in `app/` |
| Concerts held | **Not recorded.** No column holds a concert result | Same |

**The three absences above are the heart of this test case.** The scenario's whole loop is earn → spend → perform,
and the app models exactly one of the three: it can record a Performance **change** per turn, and it can record
nothing about what was bought with it or what the concerts paid. That is not an omission the audit should file as
a defect — it is `docs/scenarios/07-grand-concert.md`'s `❌ UNVERIFIED` boundary doing its job. What the audit must
establish is that each absence **renders as a stated absence**, not as a zero and not as a blank.

---

## 5. Scenario-specific test states the audit must reach

| # | State | How to reach it | What must be observable |
|---|---|---|---|
| G1 | Scenario card | `/career/setup/scenario` | `Our Grand Concert` label, `Global · 2026-07-22`, `Caps 1300 ×3 · Speed 1600 · Guts 1500`, panel tags `Career goals` + a performance tag |
| G2 | Trainee profile shows the link | `/career/setup/trainee` → Silence Suzuka | Her aptitudes; the scenario link is a **deck** fact, not a profile fact |
| G3 | Target ceilings | `/career/setup/target` | `#targets_Speed max=1600`, `#targets_Guts max=1500`, the other three at 1300 |
| G4 | Deck step, banned card | `/career/setup/deck` | `[Passing the Dream On] Team Sirius` is **not selectable** for this run |
| G5 | Preflight | `/career/setup/preflight` | Facility levels print the repetition rule (this scenario uses `repetition`, like URA). **Fan gates print the absence sentence, not the three URA thresholds** — measured 2026-11-12, only `ura_finale` carries a `fan_gates` key in `config/scenarios.php`. Happy Meek prints the **absence** sentence too |
| G6 | Cockpit, panel absence | `/training-runs/{id}/cockpit` | The career-goals panel renders; **no** race calendar, no team block, no shop, no grade meter. The baseline strip prints the scenario's own `panel_absence` sentence naming why nothing the concert is draws here |
| G7 | Performance observation input | `/training-runs/{id}/training` | The form carries `Performance type` (five client names) and `Performance delta` fields, and **only** for this scenario |
| G8 | Record a Performance change | enter type `Dance`, delta `+10`, submit | A `turn_events` Scenario row lands with a `PerformancePayload`; the turn reads back |
| G9 | Zero delta refused | enter delta `0` | A field-bound error, not a silent no-op |
| G10 | Performance read-back | cockpit | The recorded observations render as **observations** (turn + type + delta), never as a running total |
| G11 | The absence of a total | cockpit | No surface prints a "current Dance Performance" figure. If one appears, the first term is missing and that is the finding |
| G12 | Performance fields for other scenarios | any URA / Unity / Trackblazer run's training form | The two fields are **absent**, not disabled-looking |
| G13 | Scenario detail | `/training-runs/{run}/scenario` | The career-goals panel, the published caps table at 1600/1300/1300/1500/1300, and the `panel_absence` sentence |
| G14 | Finale label, three surfaces | cockpit obligation rail, race planner, scenario detail | KI-82's fix reads `config(...).finale_label` (`The Grand Concert`) in **two** places: `CockpitController::obligationLabel()` and `RacePlannerController::raceRow()`. **`FinaleReader::forRun()` does not** — it returns `config(...).label`, which is `Our Grand Concert`. So the finale row can read `The Grand Concert` on two surfaces and `Our Grand Concert` on a third. **Probe all three and record which each prints**; do not assume the fix covers the scenario detail |
| G15 | Result, active run | `/training-runs/{id}/result` | The active-run refusal: `This career is still running` |
| G16 | Result, completed | mark Completed, reopen | Final stats with reasons, the finale block, the scenario result |
| G17 | Veteran + reuse | save, then `/legacy/compare` | The Veteran is a parent; its Speed/Guts spark projects into a starting cap |

---

## 6. The three refusals this scenario's walk must not treat as defects

Recorded here so the walk does not re-file them. Each is a deliberate boundary with a source behind it.

| Refusal | Why it is correct |
|---|---|
| No Performance **balance** is printed | No source publishes a run's starting Performance, so a sum of deltas would have a missing first term. `TrainingRun::performanceObservations()` says this at length |
| No song count, no Hype value, no concert history is stored | Nothing in the app writes them; `docs/scenarios/07-grand-concert.md` marks every per-level magnitude unverified, and a stored count with no source would be invented data |
| No lesson or song can be recorded as purchased | The same boundary one layer down. A `Lesson` purchase is not a `turn_events` payload the app carries |

**What would close each, recorded so a later pass knows the upgrade path:** a verified starting Performance value
per type would close the first; a verified song list with per-type costs (which the frames now **partially**
provide, for three songs) would let the second become a recorded fact rather than a count; the third needs a
payload shape, which is an architecture decision and not an audit's.

---

## 7. Expected behaviour, stated as assertions

1. The scenario card prints four facts and no more, and its caps line is `Caps 1300 ×3 · Speed 1600 · Guts 1500`.
2. The training form's Performance pair appears for this scenario and for no other.
3. The pair posts only when both halves are present; a type with no delta and a delta with no type are each
   refused.
4. A delta of `0` is refused with a field-bound error.
5. The cockpit renders the recorded observations as a list with turn, type and delta, and prints **no total**.
6. Every unrecorded Performance type renders `N/A` with a `title`/reason, never `0`.
7. The baseline strip prints this scenario's own `panel_absence` sentence, and it is the only scenario with one.
8. The scenario detail page draws no concert board, no gauge, no lesson list and no song list.
9. The finale row prints this scenario's own label.
10. The banned support card is not selectable in the deck step for this run.

---

## 8. What this test case does not establish

- It does not establish the **reserve** button's label; no frame was found showing one.
- It does not establish a cost curve for lessons, or a Hype value per song. Three song costs were read off one
  run state and are recorded as observations, not as prices.
- It does not test the 16-song lyrics event or the 18-song special version, because the app stores neither a song
  count nor a concert result.
- It does not test the second bonus layer being **active**, because nothing shows which bonuses a run has.
- It does not test the scenario's second-level link rule (a specific link character's card raising the skill tier
  in November); that needs a deck the app does not model.
