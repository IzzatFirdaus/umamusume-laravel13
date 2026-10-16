# Unity Cup Plan

This document defines the Unity Cup scenario for Trainer Desk. It is written in the same architectural style as the Trackblazer plan: it assumes the shared cockpit frame, the three-layer model (Trainer Home, Career Cockpit, Advisor), decision cards, visible uncertainty, and the Veteran loop, all of which carry over unchanged. Only the Unity Cup-specific deltas are spelled out here.

## Sources

- Your `UMAMUSUME_REFERENCE.md` (sections 1.3.4, 2.1, 2.2.2, 2.5)
- [GameTora, Unity Cup Scenario](https://gametora.com/umamusume/unity-cup) (last updated 2026-07-20)
- [Game8, Unity Cup Guide](https://game8.co/games/Umamusume-Pretty-Derby/archives/545572) (returned pre-update copy)
- [uma.guide, Unity Cup Deckbuilding Guide](https://uma.guide/guides/unity-cup-deckbuilding-guide) (last updated Feb 2, 2026)
- [uma.guide, Special Scaling Skills](https://uma.guide/guides/skills-special-scaling)

Unity Cup changed a lot on Global on 2026-07-01. The Game8 page returned the pre-update values (older training bonuses, no Extreme Spirit Burst). GameTora (updated 2026-07-20) supplied every post-update number. Game8 and uma.guide supply the schedule, deck advice, and team-name data, which are tagged where they predate the update.

---

## 1. THE CENTRAL UX CONCEPT

Unity Cup is URA with a second thing to raise: your team. The persistent question becomes:

> Which training turn builds the most team and trainee value, and should I cash a Spirit Burst now or bank it?

Facility levels no longer rise with use. They are set by your team's stat rank for that training type (F/G = Lv1, D/E = Lv2, B/C = Lv3, A = Lv4, S = Lv5). So training with teammates is also how you raise your own training bonuses.

```
   TRAIN WITH TEAMMATES (white flames)
        │
        ├─► spirit gauge fills ──► SPIRIT BURST ──► big member stats, trainee stats,
        │                                            skill hint
        │                              │
        │                              └─► EXTREME BURST (once per member, after
        │                                   normal): 0% failure, raises member caps,
        │                                   Ignited Spirit hint
        ▼
   TEAM STAT RANKS rise ──► FACILITY LEVELS rise ──► better training
        │
        ▼
   TEAM RACES every 6 months (5 races vs an NPC team) ──► league rank, stats
        │
        ▼
   ZENITH FINALS ──► then the three URA Finals races
```

The race calendar and career goals are the URA ones. The "extra sprinkle" is entirely the team layer.

The defining design principle: the Advisor must compare two kinds of value — what a training action gives the trainee now, versus what it contributes to the team and to future training opportunities. The UI should make that trade-off legible, not just report the numbers.

---

## 2. SCENARIO SELECT

```
UNITY CUP
Team career. Spirit Bursts, team races, Zenith finals.

Stat caps   SPD 1300  STA 1300  POW 1300  GUT 1300  WIT 1800
Facility levels set by team rank, not by use
Scenario Link: Taiki Shuttle, Rice Shower, Haru Urara,
               Matikanefukukitaru, Riko Kashimoto
```

The cap figures match both the reference and GameTora's current page. The five linked names are from the reference's scenario data; GameTora's article names four story characters plus Riko as the linked Pal card.

---

## 3. TRAINEE & TARGET (DELTAS)

- Career goals and the fan gates are the URA ones, so keep that screen mostly as is.
- The unique-skill level-up gates are 60,000 fans by Valentine's Day, 70,000 by early April, and 120,000 by late December of Senior year. Dirt-leaning trainees (Haru Urara, Smart Falcon) use 40,000 / 60,000 / 80,000.
- Akikawa is absent here, so the April gate has no green-bond requirement.
- Add a Team Name picker, chosen in late September of Junior year. The options depend on which linked character is your trainee or in your deck. Winning the finals grants a gold skill by name (Game8's pre-update table):

| Team Name          | Linked Character        | Gold Skill Granted |
|--------------------|-------------------------|--------------------|
| Happy Hoppers      | Taiki Shuttle           | Mile Maven         |
| Sunny Runners      | Matikanefukukitaru      | Clairvoyance       |
| Carrot Pudding     | Haru Urara              | Indomitable        |
| Blue Bloom         | Rice Shower             | Cooldown           |
| Team Carrot        | (fallback)              | No Stopping Me!    |

- Show cap headroom per stat. Wit is the outlier at 1800.

---

## 4. LEGACY LAB (DELTAS)

- The scenario factor is "Unity Cup" and grants Power and Wit on inheritance.
- Beating the strengthened Zenith makes it easier to get the scenario factor, and it unlocks the "+" versions of the scenario skill factors.
- Linked characters get extra bonuses. Show them as a read-only list, since the badge is derived and not stored per card.

---

## 5. SUPPORT DECK BUILDER (DELTAS)

The team is built from your deck, so the deck screen should show which slots become teammates:

- Support cards (except Pals) become team members. They are the only members with a bond gauge, so they are the only ones that can do friendship training.
- Story characters join early, and random characters join after each team race. These have no gauge, but they can take part in Special Training and Spirit Bursts.

Deck guidance from uma.guide (Feb 2026, before the July update):

- The framework is 2 Speed, 3 Wit, and Riko Kashimoto.
- Riko is the linked Pal and is called a must-slot in most decks.
- Race Bonus: aim for at least 35 total, up to 50 to 60 with multiple SSRs.
- Power cards are weak here because you get Power mainly from Sparks. Stamina cards are best avoided.

Present these as community guidance, and let the Trainer's own cards and distance goal override them. Show Race Bonus as a meter with markers at 35 and 50 to 60, labelled as guidelines.

---

## 6. TEAM PLANNER (NEW, AND THE KEY PRE-RUN SCREEN)

This is the Unity Cup equivalent of the Route Planner. It turns the team layer into something the Trainer can plan before starting.

```
TEAM PLAN                                   Rice Shower • Blue Bloom

TEAM MEMBERS (from your deck)
 Speed SSR      ✓ team member
 Speed SR       ✓ team member
 Wit SSR        ✓ team member
 Wit SSR        ✓ team member
 Wit SR         ✓ team member
 Riko (Pal)     ✗ not a team member, but linked: +energy, +events

JOINS LATER   Story: Haru Urara, Rice Shower, Matikanefukukitaru, Taiki Shuttle
              Random characters after each team race

DISTANCE GROUPS (up to 3 per group)
 Sprint   [ ]   Mile  [ ]   Medium [ ]   Long [ ]   Dirt [ ]

TEAM RACE CALENDAR            Junior late Dec • Classic late Jun • Classic late Dec
                              Senior late Jun • FINALS Senior late Dec
```

The race calendar comes from Game8's pre-update page, so it is tagged as such. The planner also reminds the Trainer that team members should sit in the distance where their aptitude is A.

---

## 7. RUN PREFLIGHT

```
RUN PREFLIGHT

TEAM NAME     Blue Bloom → Cooldown (if finals won)       ✓ available
DECK          Race Bonus 41%   ✓ above 35
              Riko slotted     ✓
TEAM PLAN     Wit cards: 3     ✓ burst/energy support
              Stamina cards: 0 ✓
GOALS         URA fan gates    ✓ planned
ELITE TEAM    Needs league rank 10+, team rank A+, 1+ Extreme Burst by the 4th race
              ⚠ none planned yet

RULE CONFIDENCE
 Burst/Extreme values (post-update)   GameTora, 2026-07-20
 Race calendar                        Game8, pre-update
```

---

## 8. THE CAREER COCKPIT

```
┌──────────────────────────────────────────────────────────────────────┐
│ UNITY CUP                           CLASSIC • TURN 31 • Energy 58    │
├──────────────────────────────────────────────────────────────────────┤
│ TEAM BOARD                  League rank 6 • Team rank B              │
│            rank   facility                                           │
│ Speed       A       Lv 4                                             │
│ Stamina     C       Lv 3                                             │
│ Power       C       Lv 3                                             │
│ Guts        D       Lv 2                                             │
│ Wit         B       Lv 3                                             │
│ Next team race: 14 turns   Weakest group: Dirt                       │
│ Bursts done: 5  (next skill breakpoint: 7)   Extreme done: 1         │
├──────────────────────────────────────────────────────────────────────┤
│ BURST BANK                                                           │
│ Rice Shower (linked)  normal burst READY   best on: Stamina          │
│ Fine Motion           gauge 70%                                      │
├──────────────────────────────────────────────────────────────────────┤
│ ★ RECOMMENDED: TRAIN STAMINA                                         │
│                                                                      │
│ 4 flames: +6 Stamina, +3 Guts, +5 SP (Special Training)              │
│ Rice Shower burst (linked): +20 Stamina, +10 Guts, +10 SP            │
│                                                                      │
│ WHY                                                                  │
│ Cashing the burst here raises your weakest team stat and moves       │
│ Stamina toward B (facility Lv 3 → Lv 3). No energy penalty.         │
│ ⚠ Banking it for a Wit tile gives less because Rice Shower's         │
│   burst is a Stamina one.                                            │
│                                                                      │
│ [ TRAIN STAMINA ]                                                    │
│ Alternatives                                                         │
│ ○ Wit: +energy, burst not ready for it                               │
│ ○ Optional race: only if a goal requires it                          │
└──────────────────────────────────────────────────────────────────────┘
```

All numbers in that wireframe are illustrative except where tied to a table:

- The 4-flame row and the linked burst values come from GameTora's post-update tables.
- The rank-to-level mapping is the published one (F/G = Lv1, D/E = Lv2, B/C = Lv3, A = Lv4, S = Lv5).

Two behaviours to build in:

### Flame triggers

Flames are the trigger. A training only pays the extra Special Training stats when it has at least 2 white flames. GameTora's per-flame table for Speed, Stamina and Power:

| Flames | Primary | Secondary | SP |
|--------|---------|-----------|-----|
| 2      | 2       | 1         | 3   |
| 3      | 4       | 1         | 3   |
| 4      | 6       | 3         | 5   |
| 5      | 10      | 5         | 7   |

### Bursts can be held

Per GameTora, "you don't have to trigger it at the first opportunity", and an unused burst can come back on another facility. That is why the Burst Bank exists.

---

## 8a. TEAM BOARD (PERMANENT COCKPIT ELEMENT)

The Team Board is a permanent fixture of the Career Cockpit — not a one-off pre-race screen, but a continuously visible summary that makes the team-development trade-off legible on every turn. It should support manual team assignments, show each member's aptitude and available skills, track development through training, and forecast which categories need attention before the next showdown.

```
UNITY CUP · TEAM BOARD                      Classic • Turn 31 • Energy 58

OVERALL TEAM RANK           A
NEXT CUP                    4 turns

FACILITY PROGRESSION
Speed       A → Lv 4        (rank determines facility level)
Stamina     B → Lv 3
Power       A → Lv 4
Guts        C → Lv 2
Wit         B → Lv 3

TEAM STAT RANKS
Speed       A                Mile       Review mile aptitude and available skills
Stamina     B                Medium     Review balanced race coverage
Power       A                Long       Review stamina and long-distance skills
Guts        C                Dirt       Review dirt aptitude and available racers
Wit         B

ADVISOR PRIORITY
Develop weak team categories (Guts → B), improve facility ranks,
and assign the most suitable available racers before the next Cup.
```

The Team Board tracks four things the Trainer needs on every turn:

1. **Overall team rank** — the aggregate that determines Zenith-finals eligibility.
2. **Facility progression** — the rank-to-level mapping for each stat, derived from team stat ranks, shown as the current rank transitioning to the next level.
3. **Team stat ranks** — the five-letter-rank grid (Speed, Stamina, Power, Guts, Wit), the same scale that drives facility levels.
4. **Team race coverage** — a distance-by-distance checklist (Sprint, Mile, Medium, Long, Dirt) that surfaces aptitude and available-skill gaps per category.

All values must come from the actual team data via `TrainingRun::gradeObjectives()` and the rules engine; the wireframe above is illustrative only.

---

## 9. ADVISOR PRIORITY HIERARCHY

1. **Don't end the run.** The URA goal races, fan gates, and energy come first, and the Alarm Clock covers goal-race failures.
2. **Team race readiness.** How many turns until the next race, which distance group is weakest, and whether a loss would drop the league rank.
3. **Burst economics.** Cash or bank, which facility, and progress toward the 4 / 7 / 10 / 13 burst-count breakpoints.
4. **Facility levels.** These are derived from team ranks, so show what a training does to the rank.
5. **Training target.**
6. **Optimization.**

Each recommendation must carry a dual-value readout: the trainee gain this turn versus the team-gain / future-opportunity cost. The Advisor should make that trade-off explicit rather than collapsing it into a single number.

An optional race is rarely worth a turn here. Game8 says to skip non-goal races in favour of teammate training, because a race turn is a turn not spent developing the team.

---

## 10. BURST BANK AND EXTREME BURSTS

This is the new mechanic, so it gets its own panel. For each team member, show:

- Gauge progress (a full gauge means the next Special Training with them bursts).
- Whether the normal burst is ready, and whether the Extreme burst is unlocked (it appears for that member during the next Unity Training after their normal burst).
- Where cashing it pays most. The stat bonus depends on the facility.

Values to load as data (GameTora, post-update):

### Normal burst — Speed tile

| Variant   | Speed | Power | SP  |
|-----------|-------|-------|-----|
| Base      | +15   | +7    | +5  |
| Linked    | +20   | +10   | +10 |

### Extreme burst — Speed tile

| Variant   | Speed | Power | SP  |
|-----------|-------|-------|-----|
| Base      | +20   | +10   | +15 |
| Linked    | +25   | +15   | +20 |

### Wit tile

- Normal burst: +15 Wit and +2 Speed. A burst on a Wit tile adds +5 energy recovery.
- Bursts are additive, so there is no bonus for stacking several.
- The hint level from a burst is the card's hint bonus plus 2, and one more for linked cards. The hint comes from the support card's own pool.

An Extreme burst also sets that turn's failure chance to 0%, raises the member's stat caps, and gives a hint for the Ignited Spirit skill for that facility. Treat it as a zero-risk turn when planning energy.

---

## 11. TEAM RACE BRIEFING (EVERY SIX MONTHS)

```
TEAM RACE 2 of 4                    Classic late June
League rank 6      Alarm Clocks 2

OPPONENT (top = strongest)
 ○ Strong     rank if won: (read from game)
 ○ Medium
 ○ Weak

TAZUNA'S OVERVIEW (enter what the game shows)
 Sprint ●●○   Mile ●○○   Medium ●●●   Long ●●○   Dirt ○○○
 Total circles: 8  ✓ ≥ 3 is the "safe" reading

WIN CONDITION   3 of 5 races won → league rank rises. Lose → it falls.
```

Rules to encode:

- A team race is five races, one each of Sprint, Mile, Medium, Long and Dirt.
- Winning at least three raises league rank, and a loss lowers it.
- Alarm Clocks can be used on a team-race loss since the 2026-07-01 update.
- GameTora says at least three circles in total is the generally safe reading of Tazuna's overview.

### ELITE TEAM (race 4 only)

```
ELITE TEAM (race 4 only)
  League rank ≥ 10     ✓ 11
  Team rank ≥ A        ✓ A
  1+ Extreme Burst     ✓ 2
  → option available. Winning gives extra stats and lets you face the
    strengthened Zenith in the finals.
```

### Team Roster Dashboard

The briefing should also surface the team development layer — who needs what, and whether they can deliver at the next Cup:

```
TEAM ROSTER                      League rank 6 • Next Cup in 4 turns

Member            Stat focus     Rank  Needs before Cup       Contribution
Rice Shower       Stamina        B     +30 Sta for race 2     Burst: Stamina tile
Fine Motion       Speed          A     —                      Burst: Speed tile (Lv 2)
Biwa Hayahide     Power          C     +50 Power, +2 wins     Epithet: Goddess route
Silence Suzuka†   Guts           D     Bond 2 bars, +40 Guts  Burst: Guts tile
Mejiro Ryan†      Wit            C     +30 Wit                 Burst: Wit tile

† Story characters (no bond gauge, no Friendship training)
```

This makes the trade-off explicit: training Rice Shower's Stamina this turn raises a facility level for everyone, but leaves Biwa at C rank for the next Cup.

### Tournament Readiness

```
TOURNAMENT READINESS                    Classic late June • Next Cup in 4 turns

OPPONENT PROJECTIONS (top = strongest)
  ○ Strong     rank if won: A+          ⚠ Rice Shower needs +30 Stamina for Guts races
  ○ Medium     rank if won: B           ✓ Biwa's Power is borderline
  ○ Weak       rank if won: C

RACE PREVIEW
  Sprint     Stamina 580      ✓ covered (Rice Shower +20 from burst)
  Mile       Power 920        ⚠ borderline (Fine Motion +30 needed)
  Medium     Speed 1010       ✓ covered
  Long       Stamina 1180     ✓ covered
  Dirt       Guts 640         ⚠ low (Mejiro Ryan bond not ready)

UNRESOLVED RISKS
  • Mejiro Ryan Dirt Guts: no Spirit Burst hint unlocked yet
  • Biwa Hayahide: C rank in Power → facility stays at Lv 3
```

---

## 12. FINALS BRIEFING

```
UNITY CUP FINALS                Senior late December

OPPONENT       Zenith  (strengthened if the elite team was beaten)
TEAM RANK      S   → "It's On" hint (+3 levels with a linked trainee)
               S+  → one more hint level (new rank in the July update)

BURST COUNT    9   breakpoints: 4 / 7 / 10 / 13
SKILL EVENT    "Team Zenith Declares War", Senior late November:
               skill type follows your highest team stat rank

THEN: the three URA Finals races.
```

The late-November event gives hint levels by burst count (GameTora):

| Bursts | Hint Level | Bonus |
|--------|------------|-------|
| 4+     | white Lv1  | +10   |
| 7+     | white Lv3  | +20   |
| 10+    | gold Lv1   | +30   |
| 13+    | gold Lv3   | +40   |

The skill's strength scales with team total stats. uma.guide's example is that 3,580 total Speed gives a 1.1x multiplier.

---

## 13. CAREER REPORT

Unity Cup specifics for the report: final team ranks per stat, league rank, bursts and Extreme bursts, team races won, whether the elite team and strengthened Zenith were beaten, and which gold skill the team name earned. Then offer "Promote to Veteran" as before.

---

## 14. WHAT I COULD NOT CONFIRM

1. **Rank stat thresholds.** GameTora says members are ranked by the average of their stats, but no source read gives the stat-value boundary for each letter rank. Read the rank from the game, and only project a rank if the rule becomes available.

2. **Burst payout split.** The table shows total gains but does not state how much goes to the teammate versus the trainee (the scenario guide says "a large boost to the teammate, a moderate boost to your trainee" but gives no ratio). The Advisor shows the table total and does not split it.

3. **Burst-count breakpoints.** RESOLVED. Post-update sources agree on four non-overlapping bands: 4–6, 7–9, 10–12, 13+ (docs/scenarios/02-unity-cup.md:209-218, SCENARIO-PUBLISHER-REFERENCES.md:1105-1108, config `spirit_burst_bands`). The earlier "4 to 9" / "7 to 9" overlap was pre-rework; the corrected values are non-overlapping and the plan's 4 / 7 / 10 / 13 breakpoints are confirmed.

4. **Event timing.** RESOLVED. Post-rework sources place the burst-count skill event at **Senior Year, Late November** (docs/scenarios/02-unity-cup.md:220, SCENARIO-PUBLISHER-REFERENCES.md:1109). The Game8 extract that printed "Early November" was pre-update; the plan correctly adopted Late November.

5. **Elite team colour.** RESOLVED. The elite team's background is **pink/purple** — the discrepancy was that GameTora's update-list entry used "purple" for Extreme Spirit Bursts while a later section used "pink" for the elite team itself; both colours describe the same pink/purple background (docs/scenarios/02-unity-cup.md:174, SCENARIO-PUBLISHER-REFERENCES.md:1095, UX-DELIVERABLES.md:3058).

6. **Pre-update Game8 data.** Game8's team-rank all-stat bonuses (F +2 up to S +5) and its "rank 5 gives +50" claim before the finals predate the update, so they are left out.

7. **Calendar.** RESOLVED. All five team-race dates are confirmed from post-update sources: Round 1 Junior Late Dec, Round 2 Classic Late Jun, Round 3 Classic Late Dec, Round 4 Senior Late Jun, Finals Senior Late Dec (SCENARIO-PUBLISHER-REFERENCES.md:1021-1025, docs/scenarios/02-unity-cup.md:141-147).

8. **"Result Pts".** RESOLVED. DESIGN-CORPUS.md:1174, 1215, 3612 and 4258 record `Result Pts` as an observed Unity Cup UI counter, measured from captured frames alongside Team Rank and Spirit Burst counts. The term reaches the repo from client captures, not just the reference.

9. **Pal membership.** RESOLVED. docs/scenarios/02-unity-cup.md:56 and :261 state explicitly: "Pal-type cards are excluded from the team roster, so a Pal buys you no Spirit Bursts at all." The GameTora team description is corroborated by the scenario guide.

10. **Alarm Clock limits on team-race retries.** PARTIALLY RESOLVED. The 2026-07-01 rework added Alarm Clock retry on a lost Team Race, confirmed live (SCENARIO-PUBLISHER-REFERENCES.md:577, :661, :1016; docs/scenarios/02-unity-cup.md:151; config `loss_retryable_with_alarm_clock`). The general per-career cap ("up to 5 Alarm Clocks per career", SCENARIO-PUBLISHER-REFERENCES.md:1223) is from the data export but is not Unity-Cup-specific; the per-run allowance and resume turn for a team-race retry remain undocumented.

11. **Names.** Team names and skill names are from English guides, not captured client strings.

---

## WHAT I WOULD NOT BUILD

- An auto-player.
- Fake win-probability numbers for team races (use Tazuna's circles as entered).
- A fixed "best deck" that overrides the Trainer's collection.

---

## MVP SCREEN SET

1. Trainer Home
2. Trainee & Target
3. Legacy Lab
4. Support Deck Builder
5. Team Planner
6. Run Preflight
7. Career Cockpit (with Team Board and Burst Bank)
8. Team Race & Finals Briefing
9. Career Report / Veteran

#7 gets most of the design effort again, and the Team Planner has no equivalent in URA.

The following table maps the recommended MVP features to the screen set above for implementation prioritisation:

| Priority | Feature                  | Screen(s)                | Why it matters                                                |
| -------- | ------------------------ | ------------------------ | ------------------------------------------------------------- |
| 1        | Career Cockpit           | #7                       | Integrates trainee growth and team development                |
| 2        | Team Board               | #7 (permanent panel)     | Tracks members, aptitude, ranks and race coverage             |
| 3        | Unity Training evaluator | #7                       | Identifies valuable multi-member training                     |
| 4        | Spirit Burst planner     | #7 (§10 Burst Bank)      | Tracks gauges and helps time burst opportunities              |
| 5        | Unity Cup match planner  | #8                       | Evaluates opponents and five-race team assignments            |
| 6        | Support Deck Builder     | #4                       | Balances scenario-linked effects, training and skill coverage |
| 7        | Career Report / Veteran  | #9                       | Preserves trainee outcomes and team-development history       |

---

## 17. DECISION CONTRACT

Every recommendation the Advisor produces follows this shape — it must not be a bare action label:

1. **Current state and immediate objective.**
2. **Recommended action** and expected benefit (with numbers).
3. **Best alternative** and its trade-off.
4. **Confidence** — verified, derived, configurable, or unknown — with the source badge.
5. **Conditions that would change the recommendation.**

Example:

> **★ RECOMMENDED: TRAIN STAMINA**
>
> You are 18,800 fans short of the Classic gate with 8 turns remaining, and Stamina is below the Medium-distance floor.
>
> - **Train Stamina (Lv 3):** +6 Sta, +3 Guts, +5 SP. Facility is 4/12 to Lv 4.
> - **Alternative — race a G3 (Mile):** +100 coins, +60 GP if 1st, but consumes energy and risks Mood Down from fatigue.
> - **Confidence: verified** (facility level by repetition, config `facility_level_source = 'repetition'`).
> - **Switch if:** Summer camp starts in 6 turns (bank energy first), or Akikawa bond reaches 3-bar (Fan Fest gate opened).

---

## 18. ACCEPTANCE CRITERIA

Each planned feature ships with concrete success conditions:

| Feature                    | Success Criterion                                                                 | Edge Case                                  |
| -------------------------- | --------------------------------------------------------------------------------- | ------------------------------------------ |
| Career Cockpit             | Shows trainee stats, team ranks, burst gauges, and next cup date in a single view | Team member has zero bonds                 |
| Team Board                 | Tracks 5+ members, surfaces weak race categories, updates after training          | All members at S rank (no upgrades left)   |
| Spirit Burst planner       | Warns when a burst is missed (turn passed without cashing)                        | Burst ready on multiple members simultaneously |
| Unity Training evaluator   | Identifies multi-member trainings worth 3+ flames                                 | No teammates available for a facility      |
| Unity Cup match planner    | Assigns 5 racers to 5 distances, highlights missing aptitudes                     | Fewer than 5 eligible team members         |
| Recommendation reconciliation | Predicted outcome vs actual entry updates the run state and flags deviation    | User edits state manually after the fact   |

---

## 19. VERIFICATION STATUS

Each feature carries one of three levels — never collapsed:

- **Implemented** — the code exists and the behavior is connected.
- **Verified** — automated tests or reproducible manual checks demonstrate the intended behavior.
- **Game-validated** — calculations have been compared against authoritative mechanics or observed in-game.

An "Implemented and verified" feature can still rest on an incorrect understanding of the game. A page that renders is not verified. This distinction is tracked per feature, not per scenario.

---

## 20. IMPLEMENTATION AND TESTING PLAN

| Phase                      | Deliverable                                                                     | Completion evidence                                              |
| -------------------------- | ------------------------------------------------------------------------------- | ---------------------------------------------------------------- |
| 0. Reconnaissance          | Inspect existing scenario code, plans, contracts, routes, data models and tests | Inventory of existing, partial, missing and conflicting behavior |
| 1. Rules audit             | Document verified scenario mechanics and unresolved assumptions                 | Source-backed rules and explicit unknowns                        |
| 2. State model             | Define run state, transitions, persistence and validation                       | Tests for valid and invalid state changes                        |
| 3. Decision engine         | Implement scenario-specific candidate evaluation and ranking                    | Deterministic tests using known inputs and expected outputs      |
| 4. Command center          | Build the primary screen around the next actionable decision                      | UI tests for loading, empty, error, stale and normal states      |
| 5. Reconciliation          | Compare predicted versus actual outcomes                                        | Tests for updating run state without losing history              |
| 6. End-to-end verification | Exercise complete careers and edge cases                                        | Reproducible scenarios, regression results and completion report |

**Unity Cup focus:** phase 4 builds the Team Board and Unity Training evaluator as integrated cockpit panels. Phase 5 adds reconciliation for Spirit Burst outcomes (predicted stat gain vs. actual). Phase 6 validates team-rank-to-facility-level math against in-game captures.

---

## 21. PRIORITY MATRIX

| Priority | Scope                    | Rationale                                                  |
| -------- | ------------------------ | ---------------------------------------------------------- |
| P0       | Canonical run state, scenario isolation, regression tests | Protects the trustworthiness of team-development tracking  |
| P1       | Next-action recommendations, deadline tracking, dual-value readout | Turns the cockpit from a display into a command center     |
| P2       | Matchup analysis, support-deck optimization for team synergy | Materially affects tournament outcomes                     |
| P3       | Keyboard-friendly controls, richer historical analytics | Convenience only                                           |
