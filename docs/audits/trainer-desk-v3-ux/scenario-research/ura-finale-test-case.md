# URA Finale test case

**Phase 0 deliverable 1 of 6.** A representative career that exercises the URA Finale surfaces of Trainer Desk
end to end. Sources are cited by the ids in `scenario-research-sources.md`.

**Scenario key:** `ura_finale`. **Client name:** `The Beginning! URA Finale!` [A1][A2].
**Live on Global:** 2025-06-26; **reworked** 2026-07-01 [A1][B1].
**Caps:** 1400 in all five stats [B1][C4], which `config/scenarios.php` implements as `cap_bonus` 200 over the
1200 base.

---

## 1. Verified game mechanics

Rows are the mechanics this test case depends on. A row whose highest source is tier C is marked, and §6 says
what that costs the audit.

| Mechanic | Value or rule | Source | Tier |
|---|---|---|---|
| Stat caps | 1400 / 1400 / 1400 / 1400 / 1400 | B1, C4 | B |
| 2026-07-01 rework | Raised the caps to 1400, added the Happy Meek duels, and raised training stat and skill-point payouts | A1, B1, C1 | A |
| Facility leveling | One level per **four** uses of the same discipline, to Lv5 | C1, B1 | C |
| Summer camp | All facilities Lv5 for the four summer turns; Rest and Recreation restore both Energy and Mood | C1 | C |
| Mood effect | Good or great mood raises training gains; the exact multiplier is not published on any page read | C1 | C |
| Fan gate 1 | Early February, Senior year: 60,000 fans turf / 40,000 dirt → Unique Skill level up | B1, C1 | B |
| Fan gate 2 | Early April, Senior year: 70,000 / 60,000 fans **plus** a green Akikawa bond → Unique Skill level up | B1, C1 | B |
| Fan gate 3 | Late December, Senior year: 120,000 / 80,000 fans → Unique Skill level up | B1, C1 | B |
| Happy Meek appears | From Junior early May, at random facilities | B1, C1 | B |
| Duel trigger | Her icon carries an orange duel mark; training with her opens the duel. A **failed** training starts no duel | C1 | C |
| Duel options | Three: one is always the facility's stat, the other two are random among the five stats and Energy | B1, C1 | B |
| Duel odds indicator | Four states, `◎` high, `〇` average, `△` small, `✕` very small | C1 | C |
| Duel reward, win | Stat +10 at level 1 rising to +25 at MAX, +30 skill points, +4 max energy, and a hint for that stat's Racing Spirit skill | B1, C1 | B |
| Duel reward, loss | +5 to +15 of the challenged stat and +15 skill points | B1, C1 | B |
| Duel escalation | Each win raises Happy Meek's level; at MAX she appears powered up in the finals | B1, C1 | B |
| Wins to MAX | **Conflicting:** Game8 says six wins, a community guide says five | C1 | C |
| Finals structure | Three races: Qualifier, Semifinal, Finals, on turns **74, 76 and 78**, with one training turn at 73, 75 and 77 | B1, C1 | B |
| Finals advance rule | Each race must be **won** to advance; losing ends the career with a normal ending | B1 | B |
| Finals course | Decided by the distance type most often run in the career, ties to the shortest; 41 possible combinations | B1, C1 | B |
| Qualifier reward | All stats +10, +40 skill points | B1, C1 | B |
| Semifinal reward | All stats +10, +60 skill points | B1, C1 | B |
| Finals reward | All stats +10, +80 skill points; **+20 and +150** if the powered-up Happy Meek was beaten | B1, C1 | B |
| End-of-career event | Win + maxed Akikawa bond: all stats +25, +100 skill points. Win + not maxed: +20 / +80 | C1 | C |
| Scenario spark | `URA Finale`, Speed + Stamina, awarded at random on completing the run; beating the powered-up Meek raises the chance | B1, C1 | B |
| Scenario link | Aoi Kiryuin; her card raises event payouts (the three-legged race gives Iron Will at hint +3 with her card) | B1, C1 | B |

---

## 2. Trainee-specific goals the app must render

The proposed trainee is **Special Week**. This is not an arbitrary pick: her goal ladder is already in the local
catalogue and it is one of the densest, with six distinct race goals before the finals. Measured from
`database/database.sqlite` on 2026-11-12:

| # | Goal | Type | Requirement | Year | Turn |
|---|---|---|---|---|---|
| 1 | Junior Make Debut | Participate | must run | 1 | 12 |
| 2 | Kisaragi Sho | Placement | 5th or better | 2 | 27 |
| 3 | Tokyo Yushun (Japanese Derby) | Placement | 5th or better | 2 | 34 |
| 4 | Kikuka Sho | Placement | 3rd or better | 2 | 44 |
| 5 | Tenno Sho (Spring) | Placement | 3rd or better | 3 | 56 |
| 6 | Japan Cup | Placement | 2nd or better | 3 | 70 |
| 7 | Arima Kinen | Placement | **1st** | 3 | 72 |
| 8–10 | FinalRound ×3 | — | — | 4 | 74, 76, 78 |

**Re-derive:**

```bash
python - <<'PY'
import sqlite3
c=sqlite3.connect('database/database.sqlite')
for r in c.execute("""select tg.goal_type, tg.required_placement, tg.career_year, tg.turn, s.title, s.tier
  from trainee_goals tg left join race_catalog_slots s on s.id=tg.race_catalog_slot_id
  where tg.umamusume_id=1 order by tg.career_year, tg.turn"""):
    print(r)
PY
```

**Why this ladder and not a shorter one.** It contains a `Participate` goal, four different placement
requirements including a win, a G3 and five G1s, and the three `FinalRound` rows. A ladder of only placings
would not exercise the app's `Participate` branch, and a ladder with no `1` would not exercise the win-only
branch. Special Week's ladder has both.

**A contrast case worth running second: Oguri Cap.** Measured the same way, her ladder carries two goal types the
app handles differently — a `Fan` goal at 3,000 fans (turn 24, year 1) and one `Undecoded` row (turn 60, year 3).
A `Fan` goal is the one goal type with no race attached, and `Undecoded` is the parser's own honest way of
saying a source row did not resolve. If the audit can afford two URA careers, run Special Week first and Oguri Cap
second, specifically to see how `Undecoded` renders.

---

## 3. Proposed Legacy and support deck

**Legacy.** Two parents with a Speed + Stamina spark spread, matching the scenario spark's own two stats. This is
a **recommendation**, not a sourced requirement: no source states which Legacy suits URA Finale, and tier-C
guidance only says to pick one that reinforces the trainee's distance and style.

Record the reason in the fixture rather than presenting it as advice. `app/Services/Legacy/StartingCapProjection.php`
is the reader; the test case only needs two parent rows and one spark each.

**Support deck.** Six slots, slot 6 Friends. The shape the scenario's own guide recommends for a Medium-distance
Front Running trainee is `4× Speed, 1× Friend/Guts, 1× Power` [C1-tier guidance, recorded in
`docs/scenarios/01-ura-finale.md`]. For the test, the *shape* matters more than the card identities:

| Slot | Type | Why this slot exists in the test |
|---|---|---|
| 1–4 | Speed | Fills four of six, which is the deck the guide recommends |
| 5 | Power | The one flex slot the guide names |
| 6 | Friend | Exercises the friend-slot rule (`DeckSlot::MAX_POSITION` is the friend slot, per `DeckSlotFactory::friendSlot()`) |

**Important:** in the local database on 2026-11-12 there are 559 support cards, so a real six-card deck can be
built from the catalogue. No synthetic card is needed. The wizard's deck step is the one surface the v3 audit
found blocked (`UX-01`, the picker is unreachable) — see §7.

---

## 4. Starting career state

A **new career from the wizard**, not a snapshot and not an import. The reason: the audit needs to see the
scenario's whole path, and the four existing URA careers in the seed are imported snapshots that skip the
wizard entirely.

| Field | Value | Source of the value |
|---|---|---|
| Trainee | Special Week, debut form | The catalogue (`umamusume.id = 1`) |
| Scenario | `ura_finale` | The wizard step 1 card |
| Build purpose | A distance and style the trainee is strong at | The trainee's own aptitudes; no source picks one for the Trainer |
| Target | Speed-led, Stamina at the Medium floor | `docs/scenarios/01-ura-finale.md`'s stamina band (600–700 for Medium) |
| Caps | 1400 ×5 | The wizard's target form clamps to these; `app/Domain/Training/StatCeilings.php` is the owner |
| Legacy | Two parents, one spark each | §3 |
| Deck | Six cards, slot 6 Friends | §3 |

**Starting stats: not recorded.** No source publishes a trainee's starting stat block, so the test case must not
invent one. The first turn entry starts empty and the trainer records what the client shows.

---

## 5. Scenario-specific test states the audit must reach

Each state names what to do and what must be observable. "Observable" means a rendered string, a payload key, or
a persisted row — not an impression.

| # | State | How to reach it | What must be observable |
|---|---|---|---|
| S1 | Scenario selection with URA Finale's badges | `/career/setup/scenario` | `URA Finale` label, `Global · 2025-06-26` date, `Baseline` badge, `Caps 1400 ×5` line, and the panel tags `Race calendar · Career goals` |
| S2 | Trainee with a ten-row ladder | `/career/setup/trainee` → Special Week → profile | The profile renders her aptitudes; the goals are **not** on this screen (they belong to the run) |
| S3 | Target ceiling at the scenario's own caps | `/career/setup/target` | Each `#targets_<Stat>` carries `max=1400`. Entering 1401 is refused with a field error, not silently clamped |
| S4 | Deck with a friend slot | `/career/setup/deck` | Six slots; slot 6 labelled Friends; the picker reachable from this step (this is the `UX-01` regression probe) |
| S5 | Preflight's scenario block | `/career/setup/preflight` | The "What this scenario asks" table shows: facility levels **"Rise by repetition: four uses per level, capped at Lv5."**, the three fan gates with their turf and dirt figures, and the Happy Meek row — which for URA must print the **duel** rows, not the "no duel system" absence |
| S6 | Cockpit at turn 0 | `/training-runs/{id}/cockpit` | The career-goals panel renders all ten ladder rows with a state each; the obligation rail renders the next due race |
| S7 | A due mandatory race | record turns up to 12 | Turn 12's `Junior Make Debut` shows `Due now`, not `Upcoming`; a turn past 12 with nothing recorded shows `Missed` |
| S8 | A recorded mandatory race | record a race entry against the debut slot | The row flips to `Recorded` and carries no alert |
| S9 | Fan-gate progress | enter a fan count on a turn | The gates print the shortfall against the recorded figure, or `N/A` with a reason when no fan count is recorded |
| S10 | Happy Meek duel state | cockpit, before any duel is recorded | If the panel renders at all, every duel figure must be labelled as an **unrecorded reading** with a reason, never a number |
| S11 | Pre-final review window | record turns up to 73 | The pre-final panel shows the training turns ahead and the three finals, with `turns away` counted from the recorded turn |
| S12 | Finals progression | record turns 74, 76, 78 with results | Each finals row shows its own state; a lost qualifier must **stop** the ladder rather than show the semifinal as reachable |
| S13 | Result screen | mark the run Completed and open `/training-runs/{id}/result` | Final stats with `Target N/A / Deficit N/A` reasons, the finale block `FINALE Completed · 1st`, and the scenario result |
| S14 | Veteran creation | `/training-runs/{id}/veteran` | The Veteran is filed with the run's own identity; a second save does not duplicate it |
| S15 | Legacy Lab reuse | `/legacy/compare` with the new Veteran as a parent | The Veteran appears as a selectable parent and its spark projects into a starting cap |

**States that must be recorded as unreachable if they are.** No source publishes a trainee's starting stats, so
S6's stat bars start at `N/A`; that is correct behaviour and not a finding.

---

## 6. Data limitations

| Limitation | Consequence for the audit |
|---|---|
| The three fan gates' Akikawa-bond requirement has **no column** in this app | The April gate cannot be evaluated; the surface must print the requirement and the absence of a reading, never a verdict |
| Wins-to-MAX is **five or six** depending on the guide [C1] | The duel escalation must not print a single figure as fact. If it prints one, that is a finding against `config/scenarios.php`'s `escalation_wins => 6` |
| No source publishes the per-duel stat gain as a function of the dueled stat | The duel panel's `stat_gain_values` is `null` in config and must render as an absence |
| The finals course depends on the whole career's race history | No test career short of 78 turns can reach a "correct" course; the test asserts the ladder, not the course |
| Facility leveling at 4 uses/level is tier C (one guide) | A surface that prints it must cite the guide, not present it as a client rule |
| Mood's exact multiplier is unstated | Any figure the app prints for mood gain is invented; none should exist |

---

## 7. Required UI screens, in the order a career meets them

This is the screen set the audit walks. It is the same for all four scenarios except where a row says otherwise.

| # | Route | Screen | URA-specific expectation |
|---|---|---|---|
| 1 | `/career/setup/scenario` | Scenario selection | URA card with `Baseline` badge and its caps line |
| 2 | `/career/setup/trainee` | Trainee selection | Roster unfiltered by scenario |
| 3 | `/career/setup/trainee/{slug}` | Trainee profile | Aptitudes; no goal ladder here |
| 4 | `/career/setup/target` | Build target | Ceilings at 1400 |
| 5 | `/career/setup/legacy` | Legacy | Two parents, spark editor |
| 6 | `/career/setup/deck` | Support deck | Six slots, friend slot 6, picker reachable |
| 7 | `/career/setup/preflight` | Preflight | The scenario's asks block with the duel rows present |
| 8 | `/training-runs/{run}/cockpit` | Cockpit | Career-goals ladder + obligation rail |
| 9 | `/training-runs/{run}/scenario` | Scenario detail | The career-goals panel and the published caps table |
| 10 | `/training-runs/{run}/training` | Training decision | Five disciplines; no scenario extra |
| 11 | `/training-runs/{run}/races` | Race decision | Today's race or skip |
| 12 | `/training-runs/{run}/races/planner` | Race planner | The whole calendar with obligations marked |
| 13 | `/training-runs/{run}/skills` | Skills planner | SP budget and acquisition |
| 14 | `/training-runs/{run}/events` | Event decision | Record-only |
| 15 | `/training-runs/{run}/inheritance` | Inheritance event | Predicted vs observed |
| 16 | `/training-runs/{run}/timeline` | Timeline | Flat ordered rail |
| 17 | `/training-runs/{run}/result` | Result | Final stats, finale block, scenario result |
| 18 | `/training-runs/{run}/veteran` | Save Veteran | Files the career |
| 19 | `/legacy/compare` | Legacy Lab | The new Veteran as a parent |

**One screen is expected to be a known failure and must be recorded as such, not re-filed.** The deck step
(screen 6) was found unreachable in the v3 audit as `UX-01` and again in the retest. A URA walk that reaches it
should record whether the retest's remediation changed anything, and cite the existing finding rather than
opening a fourth one.

---

## 8. Expected behaviour, stated as assertions

These are what "the scenario works" means for this test case. Each is checkable by reading a payload or a
rendered string.

1. The wizard's URA card prints four facts and no more: label, Global date, `Baseline` badge, caps line.
2. The target step's five ceilings are each 1400, and a value above the ceiling is refused with a field-bound
   error rather than silently clamped.
3. The preflight block prints **three** fan gates with both turf and dirt figures, and the Happy Meek row prints
   the duel rules rather than an absence sentence.
4. The cockpit's career-goals panel prints all ten of Special Week's ladder rows, each with one of the states
   `Recorded`, `Due now`, `Upcoming`, `Missed`, or a named not-reached state.
5. A `Participate` goal (row 1) renders its requirement as participation, not as a placing.
6. A `Placement` goal whose requirement is 1 renders as a win requirement, not "1st or better" ambiguity.
7. The obligation rail renders nothing for a scenario whose `race_calendar` panel is off — true for Trackblazer
   and Our Grand Concert, false for URA.
8. Any duel figure with no source behind it renders `N/A` with a `title` carrying the reason, and the word `N/A`
   is never bare.
9. A lost finals race stops the ladder; the two later races are not shown as pending.
10. The result screen prints `FINALE Completed · 1st` for a completed URA run, and the scenario result block
    names `URA Finale`.

---

## 9. What this test case does not establish

- It does not claim the four seeded URA careers in `UraFinaleRunsSeeder` look right; those are imported
  snapshots and are a different path.
- It does not assert anything about finals **course selection**, because that needs a full 78-turn history.
- It does not test the `Undecoded` goal type; that is Oguri Cap's ladder (§2) and is a second walk.
- It does not settle whether `escalation_wins` should be 5 or 6; it records that a source disagrees with the
  config and leaves the ruling to the owner.
