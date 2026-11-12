# Trackblazer test case

**Phase 0 deliverable 2 of 6.** A representative career that exercises the Trackblazer surfaces of Trainer Desk
end to end. Sources are cited by the ids in `scenario-research-sources.md`.

**Scenario key:** `trackblazer`. **Client name:** `Trackblazer: Start of the Climax` [A5][A9][A11].
**Live on Global:** 2026-03-12 — announced 2026-03-09 [A4], celebrated 2026-03-13 [A5] — [A11][B2]. **Caps:** Speed 1200, Stamina 1900, Power 1200, Guts 1200, Wit 1500
[B2], implemented in `config/scenarios.php` as `cap_bonus` `[0, 700, 0, 0, 300]` over the 1200 base.

**Why this scenario is the highest-priority walk.** It has the most scenario-specific code in the tree — five
dedicated services and a panel builder with five sections — and the least human evidence. See
`scenario-test-data-matrix.md` §2.

---

## 1. The naming conflict this test case has to settle first

The app, the repository's guides, and every one of its existing tests say **Grade Points**. The publisher's own
Global launch page says **Result Points** [A11]. umamusu.wiki's page title mentions `Grade Points` in its section
heading while its opening sentence says `Result Points` and `Shop Coins` [B2].

| Source | The term |
|---|---|
| Cygames official Global launch page [A11] | `Result Points` — twice, in the goal paragraph and the reset note |
| umamusu.wiki section heading [B2] | `Grade Points and Shop Coins` |
| umamusu.wiki body sentence [B2] | `You can get Result Points and Shop Coins by participating in races` |
| uma.guide [C6] | `Grade Points` throughout |
| Game8 [C3] | `Result Points` |
| `config/scenarios.php` | `grade_objectives`, `grade_point_by_grade`, `grade_points_surplus_carries_over` |
| Twelve + source files and roughly fifty tests | `grade` / `GradePoint` |

**The publisher's page is tier A and it says `Result Points`.** That makes the app's user-facing vocabulary a
question for the owner, not a thing a Phase 0 document may decide. What this document does:

- Every **mechanic** is described here using the publisher's term, `Result Points`, with `Grade Points` given
  once as the synonym the app currently uses.
- The **app-side assertions** (§8) keep using the app's present term, `Grade Points`, because a test that
  asserts vocabulary the app does not carry would fail for the right reason at the wrong moment.
- The vocabulary question itself is filed in `scenario-information-gaps.md` §3 as a ruling owed, with both
  readings and their tiers.

**Consequence for the walk:** if the Trackblazer cockpit prints `Grade Points` anywhere a Trainer reads it, that
is a **reference-pass finding** (the rendered string contradicts the tier-A source), filed once, not once per
screen. It is not a reason to stop the walk.

---

## 2. Verified game mechanics

| Mechanic | Value or rule | Source | Tier |
|---|---|---|---|
| Stat caps | Speed 1200 · Stamina 1900 · Power 1200 · Guts 1200 · Wit 1500 | B2, B6 | B |
| No trainee-specific goal races | `In this new Scenario, trainees don't have their own goal races` | A11 | A |
| The goal instead | Earn a determined amount of points by the final turn in December of each year; the career **ends** if the target is missed | A11 | A |
| Points are reset | `Result Points are reset every time you accomplish a Career goal` | A11 | A |
| Points by race grade | G1 100 · G2 80 · G3 60 · OP 40 · Pre-OP 20 | B2, B6, C6 | B |
| Placement modifier on points | 1st 100% · 2nd 60% · 3rd 40% · 4th–5th 20% · 6th+ 10% | C6 | C |
| Yearly targets, standard track | Junior 60 · Classic 300 · Senior 300 | C6, B2 | C |
| Yearly targets, dirt-leaning | Junior 30 · Classic 200 · Senior 300 | C6, B2 | C |
| Yearly targets, limited turf range | Junior 60 · Classic 200 · Senior 300 | C6 | C |
| Shop Coins by placement | 1st 100 · 2nd–3rd 60 · 4th–5th 30 · 6th+ 0 | A11 (placement only), B2, C6 | B |
| Shop Coins move with grade? | **No.** `a Pre-OP win pays the same Shop Coins as a G1 win` | B2, C6 | B |
| Pro Shop reset | Once every six turns | B2 | B |
| Pro Shop refresh on a win | A win can add new items, which stay in stock three turns | B2 | B |
| Twinkle Star Climax format | Three races, points league; the highest total across the three wins | A11, B2 | A |
| Climax Victory Points | 1st 10 · 2nd 8 · 3rd 6 · 4th 4 · 5th–6th 3 · 7th–9th 2 · 10th–13th 1 · 14th+ 0 | B2 | B |
| Climax per-race reward | +10 to all stats and +30 skill points per leg, scaled by Race Bonus | B2 | B |
| Climax course | Decided by the races won during the career; racecourses chosen at random per race | A11, B2 | A |
| Rivals | Appear in graded races when both distance and surface aptitude are C or higher; marked with a VS bubble | B2 | B |
| Rival reward | 1st place → a skill hint tied to the race's distance and/or the running style used | B2, C6 | B |
| Rival win/draw rule | Win if 1st; draw if neither you nor the rival place 1st; loss if the rival is 1st | B2 | B |
| Secret career events | Disabled. `Raffle Time!` is specifically absent [A10]; the guides name Silence Suzuka's Runaway style as unobtainable here | A10, C6, C3 | A |
| Scenario Link | **None.** `Scenario Links: N/A` | B2 | B |
| Unique Skill level-up gate | Fans **and** Director Akikawa's bond, at each year end | C6 | C |
| Year-end fan figures | 5,000 · 60,000 · 120,000 at Junior, Classic, Senior year end | C6 | C |
| Year-end bond figures | uma.guide says 19 / 31 / 51; GameWith says 15 / 30 / 50 | C6 + the GameWith row the config already records | C |
| Race Fatigue | Risk rises with consecutive races; **cannot occur after Late December** | C6 | C |
| Career length | Three years plus the Climax, on the shared career grid | B5 | B |

---

## 3. Trainee-specific goals: there are none, and that is the point

Trackblazer **removes** the per-trainee goal ladder. `goal_source => null` in `config/scenarios.php` states this
rather than leaving the key absent, and `panels.race_calendar => false` is what makes the cockpit's obligation
rail return `[]` before it queries anything.

**The audit must therefore verify an absence, not a content set.** Three separate things must be true:

1. `/training-runs/{run}/cockpit` renders **no obligation rail** for a Trackblazer run. This was measured correct
   in the v3 audit and again in the retest; a regression here is a `config/scenarios.php` change, not a
   controller change.
2. `/training-runs/{run}/scenario` renders **no career-goals panel**. Trackblazer's `panels.career_goals` is
   absent from its entry, so the flag is off.
3. The app does **not** fall back to a stored ladder. `trainee_goals` holds 1,677 rows in the local database and
   every one belongs to a URA career; a Trackblazer run must reach none of them.

**A note on the trainee, then.** Oguri Cap is proposed not because her ladder matters here (it does not exist in
this scenario) but because she is the trainee whose *URA* ladder carries the two unusual goal types the audit
wants to contrast against — a `Fan` goal and an `Undecoded` row. Running her in Trackblazer and then in URA Finale
gives one trainee under two goal regimes, which is a sharper comparison than two different trainees.

**Why Oguri Cap and not a secret-event trainee.** Silence Suzuka's build depends on a secret event that fires in
other scenarios and does **not** fire in Trackblazer [A10][C6]. She is the wrong choice here for exactly the
reason she is the right choice for Our Grand Concert, where those events are back. Choosing her would produce a
career whose test data cannot represent what the scenario is for.

---

## 4. Proposed Legacy and support deck

**Legacy.** Two parents carrying **Stamina and Wit** sparks. Rationale: Trackblazer's cap bonus is
`Stamina +700, Wit +300` and nothing else, so a legacy that reinforces those two stats compounds the scenario's
own shape. This is a **recommendation**; no source states a Trackblazer Legacy preference.

**Support deck.** Six slots, slot 6 Friends. uma.guide names two frameworks: `2 Speed + 2 Wit + 2 flex` and
`3 Guts + 2 Wit + 1 flex` [C6]. The test uses the first, because it exercises a spread rather than a single-stat
stack:

| Slot | Type | Why this slot exists in the test |
|---|---|---|
| 1–2 | Speed | The framework's base |
| 3–4 | Wit | The framework's base; also the stat uma.guide calls hardest to raise here |
| 5 | Power | A flex slot carrying a skill |
| 6 | Friend | The friend-slot rule |

**Race Bonus matters more here than in URA**, because roughly half a Trackblazer career's turns are races [C6].
The deck's `Race Bonus` total is worth recording as a starting fact even though the app does not model it — see
§6.

---

## 5. Starting career state

| Field | Value | Source of the value |
|---|---|---|
| Trainee | Oguri Cap, debut form | The catalogue (`umamusume.id = 6`) |
| Scenario | `trackblazer` | The wizard step 1 card |
| Build purpose | Mile or Medium, a style she is strong at | Her own aptitudes |
| Target | Stamina-led to 1900, Speed to 1200 | The scenario's cap shape |
| Legacy | Two parents, Stamina and Wit sparks | §4 |
| Deck | Six cards per §4 | §4 |
| Current objective index | `1` (the debut race period) | `training_runs.current_objective_index`, 1-based |
| Shop coins | **Not recorded.** No column holds a coin balance | `config/scenarios.php` has no coin-balance reader |

**The coin balance is the single most important absence in this scenario.** The Pro Shop is the scenario's
economy, and the app models purchases as `turn_events` rows with a `ShopPurchasePayload` — it can total **spend**
(`TrainingRun::shopSpendTotal()`) but not **holdings**, because nothing enters an earned figure. A surface that
prints a coin balance would be inventing it. See §6.

---

## 6. Scenario-specific test states the audit must reach

| # | State | How to reach it | What must be observable |
|---|---|---|---|
| T1 | Scenario card | `/career/setup/scenario` | `Trackblazer` label, `Global · 2026-03-12`, `Caps 1200 ×3 · Stamina 1900 · Wit 1500`, panel tags `Grade Point objectives · Pro Shop` |
| T2 | Preflight's scenario block | `/career/setup/preflight` | Facility levels print `repetition_plus_purchases`' sentence or an honest absence; **fan gates row must be an absence**, because Trackblazer's entry declares none; Happy Meek row must be the absence sentence, not the duel rules |
| T3 | Cockpit, no obligation rail | `/training-runs/{id}/cockpit` | The career-goals panel and the obligation rail are both **not rendered**, and nothing prints a placeholder for them |
| T4 | Cockpit widgets | same | `Grade Points` and `Shop Coins` render as named absences with reasons, never `0` |
| T5 | Grade Point ladder | cockpit / scenario detail | Four rows: `Debut race`, `End of Junior Year` 60, `End of Classic Year` 300, `End of Senior Year` 300 — the **standard** track only, with the reason the other two tracks are not printed |
| T6 | Earned points from entered races | enter a G1 race in the current period | The period's `plan` total prices it at 100; the `floor_second` / `floor_third` figures price it at 60 and 40 |
| T7 | An unpriced grade | enter a race with no tier | The `uncounted` count rises and the reason renders; the total does **not** silently omit it |
| T8 | A completed race's earned points | record a placement of 1st on a G1 | `grade_points_earned` = 100; a 2nd place is **unpriced**, not guessed |
| T9 | Pro Shop catalogue | cockpit / scenario detail | The 19 config items render in config order with name, cost and effect; **no `recommended` field** |
| T10 | Record a purchase | the shop's purchase form | The purchase lands as a `turn_events` Scenario row; `spend_total` rises; the ledger lists the item with its held count against the cap of 5 |
| T11 | Off-catalogue purchase refused | POST an item not in the catalogue | A field-bound error, not a 500 |
| T12 | Purchase above the held-copies cap | buy a sixth copy of one item | Refused with a field-bound error |
| T13 | Epithet checklist | cockpit / scenario detail | Routes render as `unmet` / `met` / **`unverifiable`** — the third state is the load-bearing one; an aggregate condition must never render as `unmet` |
| T14 | Race fatigue | cockpit | With no fatigue count entered, the chip prints its reason and reads **no row**; with a count of 3 entered, the `3 races` row renders and its two caveats print as visible copy, not tooltips |
| T15 | Shop ledger inflow | enter races, record none as run | The inflow projects `entered races × 100` and labels it `calculated`, with the sentence that it assumes every one is won |
| T16 | Reserve tip | cockpit | The 100-coin Summer reserve renders as `estimated` with uma.guide's attribution, not as a rule |
| T17 | Race planner | `/training-runs/{run}/races/planner` | The calendar renders even though `panels.race_calendar` is off — the planner is run-scoped, not calendar-gated. Verify this is deliberate and stated |
| T18 | Climax state | any point | The finale renders as a `points_league` over three races. The client name `Twinkle Star Climax` is currently withheld by the app as an unverified name — see §7 |
| T19 | Result, completed | record the finale, mark Completed | Final stats and the scenario result block; the Climax placement summary |
| T20 | Veteran + Legacy Lab | save the Veteran, then `/legacy/compare` | The Veteran is a selectable parent |

---

## 7. The `Twinkle Star Climax` name is no longer unverified

`app/Services/Career/ScenarioSectionBuilder.php:253` withholds the name with this sentence:

> No published client name. "Twinkle Star Climax" is an unverified entry in the reference guide, so the export
> label "Trackblazer" stands until the guide publishes a client string.

**That premise is now false.** The publisher's own Global launch page prints `Twinkle Star Climax` as the
scenario's finals race [A11], and umamusu.wiki's page carries the same name in its own heading and body [B2].
Two tier-A/B sources now carry it.

**What this means for the walk.** A Trackblazer run that reaches the finale will render the withheld-name
sentence. That is a **correct** rendering of a stale premise — the defect is upstream in the premise, not in the
surface. The walk should record it as a reference-pass finding against
`ScenarioSectionBuilder.php:244-253` and quote both sources, rather than filing it as a UI bug on the finale row.

**Do not fix it during this audit.** The name is load-bearing in `config/scenarios.php`'s comment on KI-82 and in
the reference guide's §7 conflict row 31; changing it is a documentation-sourced correction with its own
evidence trail, not a Phase 0 edit.

---

## 8. Expected behaviour, stated as assertions

1. A Trackblazer run renders **no** obligation rail and **no** career-goals ladder anywhere, on any route.
2. The grade ladder prints exactly four rows and names the `standard` track as the one it prints.
3. `grade` and `shop` payload sections are `null` for every other scenario and populated for this one.
4. Every figure the panel prints carries one of the three provenance badges — `confirmed`, `calculated`,
   `estimated` — and no fourth state exists.
5. A coin balance is never printed. `spend_total` may be, because both sides of that subtraction are entered.
6. The purchase form refuses an off-catalogue item and an over-cap quantity with field-bound errors.
7. The epithet checklist's third state renders the word for "cannot be evaluated from what this build stores",
   not `unmet`.
8. With no fatigue count entered, the fatigue surface prints a reason and no odds row.
9. The finale renders as a points league over three races.
10. Half a career's turns being races produces no advisory telling the Trainer to stop racing — the scenario's
    strategy is racing, and a race advisory is gated off for it (`config/scenarios.php` `notes`, D-225).

---

## 9. What this test case does not establish

- It does not settle the `Grade Points` vs `Result Points` vocabulary; that is a ruling owed (§1).
- It does not verify the Climax course, which needs a full career's win history.
- It does not verify the Pro Shop's rotation, refresh-on-win, or stock duration: the app models none of the three
  and says so, and a walk can only record that the surfaces state their absence.
- It does not verify the year-end bond gate, because no column stores a bond reading (§2, last rows).
- It does not test the dirt-leaning or limited-turf-range point tracks; the app prints only the standard track
  because no column says which trainee belongs on which, and that is a recorded gap rather than a defect.
