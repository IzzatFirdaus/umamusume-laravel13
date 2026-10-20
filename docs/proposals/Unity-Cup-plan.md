# Unity Cup Plan

This document defines the Unity Cup scenario for Trainer Desk. It is written in the same architectural style as the Trackblazer plan: it assumes the shared cockpit frame, the three-layer model (Trainer Home, Career Cockpit, Advisor), decision cards, visible uncertainty, and the Veteran loop, all of which carry over unchanged. Only the Unity Cup-specific deltas are spelled out here.

**Last verified:** 2026-10-21, against `master` at `d00a0457` — the tree as it stood when this pass began. Every line-number citation was re-checked against the file it names, and every claim a reader might act on was re-checked against the repository or the source it cites. Where a check failed, the claim is marked rather than deleted. A concurrent session committed `15bba0f9` and `d664f948` while the pass ran; neither touches `config/scenarios.php`, `SCREEN_SPEC.md`, `docs/scenarios/`, `docs/research-scratch/`, `app/` or `tests/Feature/`, so every file this document names was verified against the same content it names.

**Status vocabulary.** This document is a design plan, not a roadmap. Its features carry the levels `Implemented`, `Verified`, `Game-validated` and `UNASSESSED — requires code audit`, defined in §19 below and assigned per feature there. It does not use the roadmap's `COMPLETE` / `BLOCKED BY EVIDENCE` slice statuses, and the Our Grand Concert roadmap does not use the feature levels; the two vocabularies are deliberately different and neither is a synonym for the other. **Source badges** are shared across all four scenario documents: `verified` (established in this repository or by a tier-S / tier-A source), `derived` (computed from a verified fact by a stated rule), `configurable` (a value the tool reads from configuration), `community` (a third-party guide, guidance and not a game fact), `unknown` (no cited source establishes it).

## Sources

Each source on one line: name — URL or path — last-updated date — the claims it supports.

- `docs/UMAMUSUME_REFERENCE.md` §1.3.4, §2.1, §2.2.2, §2.5 — repository path, no URL — last verified 2026-10-21 — caps and soft caps, the Global scenario surface, the Unity Cup `[Global]` mechanics summary, and the resolutions from the full Game8 read.
- [GameTora, Unity Cup Scenario](https://gametora.com/umamusume/unity-cup) — last updated 2026-07-20 — every post-update number: the July 1, 2026 rework list, Spirit Burst and Extreme Spirit Burst values, the team-rank-to-facility-level mapping, the team names, and the burst-count skill tiers.
- [Game8, Unity Cup Guide](https://game8.co/games/Umamusume-Pretty-Derby/archives/545572) — **post-update copy**: the July 2026 rework guide, page last updated 2026-07-07 02:39 AM — the scenario's own numbers (the Extreme Spirit Burst tables, the S+ rank, the stat caps) plus the team-race schedule, the deck advice and the team-name data. **Corrected 2026-10-21:** this line previously called the page a pre-update copy, which was wrong — it is the rework guide, and the repository's own extract of it carries the post-update content.
- [Game8, How to Win Unity Cup Team Races](https://game8.co/games/Umamusume-Pretty-Derby/archives/563652) — last updated November 27, 2025 09:02 PM — the **team-race** guide, a different page from the Game8 Unity Cup Guide above and not interchangeable with it: the team-race schedule block, the "won by winning in three tracks per match" win condition, the skill-inspector gesture (a teammate's skills are seen by holding their icon in the team menu and in the races), and the statement that a trainee can run only one of the five races. ⚠️ STALE on the repo's 90-day rule and **pre-dates the 2026-07-01 rework**, so it describes the pre-rework scenario. It carries **no venue and no distance for the finals legs**, and no Spirit Burst stat values.
- [uma.guide, Unity Cup Deckbuilding Guide](https://uma.guide/guides/unity-cup-deckbuilding-guide) — last updated Feb 2, 2026 — the deck framework advice, which predates the July rework.
- [uma.guide, Special Scaling Skills](https://uma.guide/guides/skills-special-scaling) — no date recorded in this document — the scaling rule behind the 3,580-total-Speed → 1.1× example in §12.
- `docs/scenarios/02-unity-cup.md` — repository path, no URL — last verified 2026-10-21 — the scenario guide: the team-race calendar, the roster and Pal-membership rules, the burst-count bands, the elite-team colour, and the rework notes.
- `docs/research-scratch/SCENARIO-PUBLISHER-REFERENCES.md` — repository path, no URL — last verified 2026-10-21 — the verbatim extractions of every publisher page above, including the Game8 Unity Cup section and the GameTora Unity Cup section.
- `config/scenarios.php`, the `unity_cup` entry — repository path, no URL — last verified 2026-10-21 — `cap_bonus`, `facility_level_source` = `team_rank`, the `team_rank_ladder`, `spirit_burst_bands`, `loss_retryable_with_alarm_clock`, `burst_payout_timing`, the `panels` map and `scenario_links`.
- `docs/research-scratch/DESIGN-CORPUS.md` — repository path, no URL — last verified 2026-10-21 — the observed `Result Pts` counter and the `TEAM RANK` badge, measured from captured Unity Cup frames.

Unity Cup changed a lot on Global on 2026-07-01. The Game8 page returned the pre-update values (older training bonuses, no Extreme Spirit Burst). GameTora (updated 2026-07-20) supplied every post-update number. Game8 and uma.guide supply the schedule, deck advice, and team-name data, which are tagged where they predate the update.

**Dated note (2026-10-21).** The Sources list previously named "Your `UMAMUSUME_REFERENCE.md`" in the second person and cited no dates, URLs or claims per source. It is normalised above to one line per source in the form *name — URL or path — last-updated date — claims supported*, matching the URA Finale and Trackblazer documents. The reference's own path is `docs/UMAMUSUME_REFERENCE.md`; "Your" was an address to the reader, not a file name.

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

**Cap badge: `verified`.** The `unity_cup` entry's `cap_bonus` in `config/scenarios.php` is `['Speed' => 100, 'Stamina' => 100, 'Power' => 100, 'Guts' => 100, 'Wit' => 600]` over the 1200 base, which reads as 1300 / 1300 / 1300 / 1300 / 1800 — the figures above, transcribed independently of the guides, and the same five the reference and GameTora's current page print. The entry's own `ruleset_version` dates the change to the 2026-07-01 rework. The caps are also `configurable`: they are a config value, not a constant.

**Facility-level badge: `verified`.** "Facility levels set by team rank, not by use" is the `unity_cup` entry's `facility_level_source` = `team_rank` in `config/scenarios.php`, and the mapping itself is the entry's `team_rank_ladder`: G/F → Lv 1, D/E → Lv 2, B/C → Lv 3, A → Lv 4, S → Lv 5. This is the opposite rule to URA Finale's, and §17's decision-contract example is corrected against it.

---

## 3. TRAINEE & TARGET (DELTAS)

- Career goals and the fan gates are the URA ones, so keep that screen mostly as is.
- The unique-skill level-up gates are 60,000 fans by Valentine's Day, 70,000 by early April, and 120,000 by late December of Senior year. Dirt-leaning trainees (Haru Urara, Smart Falcon) use 40,000 / 60,000 / 80,000.
- Akikawa is absent here, so the April gate has no green-bond requirement.
- Add a Team Name picker, chosen in late September of Junior year. The options depend on which linked character is your trainee or in your deck. Winning the finals grants a gold skill by name (Game8's pre-update table):

**Table 3a — Team names and the gold skill each grants. Source: Game8's Unity Cup guide, which returned pre-update copy; the page predates the 2026-07-01 rework, so every cell in this table is `community` and pre-update.**

| Team Name          | Linked Character        | Gold Skill Granted |
|--------------------|-------------------------|--------------------|
| Happy Hoppers      | Taiki Shuttle           | Mile Maven         |
| Sunny Runners      | Matikanefukukitaru      | Clairvoyance       |
| Carrot Pudding     | Haru Urara              | Indomitable        |
| Blue Bloom         | Rice Shower             | Cooldown           |
| Team Carrot        | (fallback)              | No Stopping Me!    |

**Dated note (2026-10-21).** The pre-update tag was previously carried only in the sentence above the table ("Game8's pre-update table") and in the Sources list. It is now in the table's own caption, so a reader who reaches the table from a link or a search sees it without reading the prose. The late-September Junior-year picker timing is `community` and pre-update on the same basis: it is printed in the Game8 extraction's Unity Cup section and in the same pre-update page, and no post-rework source re-states it. The two facts are kept together because the schedule and the names come from the same print.

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

*Illustrative only.* The member list, the distance checkboxes and the roster are a worked example.
The one sourced thing in the block is the **calendar**: it comes from Game8's pre-update page, so it
is tagged as such here and in the repository's config, which carries the same six-month cadence
(`team_race.occurs_every_months` = 6) rather than a list of dates. The rule that a team member sits
in the distance where their aptitude is A is uma.guide's framing, not a measured client screen.

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

*Illustrative only.* The team name, the deck figures, the Wit/Stamina counts, the eligibility ticks
and the advisor state are a worked example. The two sourced things are the rule tags in the column
below them: the burst and extreme-burst values are GameTora's post-rework figures, and the race
calendar is Game8's pre-update copy. The `team_race` panel in the `unity_cup` entry of
`config/scenarios.php` carries the same six-month cadence and the same "aim for at least 3 circles"
guidance.

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

**The TEAM BOARD block above is the compact in-cockpit rendering. The authoritative Team Board wireframe is §8a**, which carries the full element, its four tracked quantities and the rule that drives the facility level. The two blocks are two illustrative snapshots of the same element, not two specifications: the rank columns differ between them because each is a worked example, and neither is a recorded run.

All numbers in that wireframe are illustrative except where tied to a table:

- The 4-flame row and the linked burst values come from GameTora's post-update tables.
- The rank-to-level mapping is the published one (F/G = Lv1, D/E = Lv2, B/C = Lv3, A = Lv4, S = Lv5), and the same mapping is the `unity_cup` entry's `team_rank_ladder` in `config/scenarios.php`.

**Dated note (2026-10-21).** Three things in the block above are corrected or bounded, and none of the figures is changed:

1. **"facility Lv 3 → Lv 3" is not a typo, and it is the point.** Stamina is at rank C, which is Lv 3; the recommendation moves it toward B, which is *also* Lv 3, because B and C share a band in the published mapping. The arrow therefore shows a rank move that does not cross a level boundary — the same fact the WHY block is about, that a Stamina-tile burst helps the rank while the facility level sits still. The mapping is the `unity_cup` entry's `team_rank_ladder`, and the block now says so, so the arrow reads as a statement rather than as a slip. **What is wrong elsewhere is §8a's own facility column, and that is corrected there.**
2. **The Stamina-tile burst values in the example are not tabulated anywhere in this document.** §10 tabulates the **Speed** tile (base +15 Speed / +7 Power / +5 SP; linked +20 Speed / +10 Power / +10 SP) and the **Wit** tile (normal burst +15 Wit and +2 Speed, plus a +5 energy recovery). No Stamina-tile table exists here, so the example's "+20 Stamina, +10 Guts, +10 SP" is an **illustrative figure in the shape of the published linked-burst row, not a published value**, and the WHOY line's "+20 Stamina" carries the same caveat. The burst is also shown as a Stamina-tile burst because the block says it is, which is a scenario fact rather than a number; the numbers on that tile are `unknown` until a source tabulates them.
3. The "next skill breakpoint: 7" and the "Bursts done: 5" pair is consistent with the 4 / 7 / 10 / 13 bands in §12 and with the `spirit_burst_bands` in `config/scenarios.php`; the "Extreme done: 1" is consistent with the extreme-burst rule in §10.

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

> **This is the authoritative Team Board wireframe for the scenario.** §8's cockpit block carries a
> compact in-cockpit rendering of the same element; where the two differ, this section governs. The
> two are two illustrative snapshots of one element, not two specifications.

The Team Board is a permanent fixture of the Career Cockpit — not a one-off pre-race screen, but a continuously visible summary that makes the team-development trade-off legible on every turn. It should support manual team assignments, show each member's aptitude and available skills, track development through training, and forecast which categories need attention before the next showdown.

```
UNITY CUP · TEAM BOARD                      Classic • Turn 31 • Energy 58

OVERALL TEAM RANK           A
NEXT CUP                    4 turns

FACILITY PROGRESSION
Speed       A → Lv 4        (rank determines facility level)
Stamina     B → Lv 3
Power       A → Lv 4
Guts        C → Lv 3
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

*Illustrative only.* Every value in the block above is a worked example. The one thing it is not
illustrative about is the **mapping**: each rank column must agree with the published rank-to-level
rule and with the `unity_cup` entry's `team_rank_ladder` in `config/scenarios.php` — G/F → Lv 1,
D/E → Lv 2, B/C → Lv 3, A → Lv 4, S → Lv 5. A rank and a level in the same row that disagree with
that mapping are a defect in the drawing, not a variant of the rule.

**Dated note (2026-10-21).** The Guts row previously read "C → Lv 2". That contradicts the mapping
printed directly above it in the same wireframe — C is Lv 3, and Lv 2 is D/E — so the row is
corrected to "C → Lv 3". The advisor-priority line at the foot of the block ("Guts → B") is
consistent with the corrected row: B and C share Lv 3, so the priority is a rank move that does not
cross a level boundary, which is the same fact §8's "facility Lv 3 → Lv 3" arrow states.

The Team Board tracks four things the Trainer needs on every turn:

1. **Overall team rank** — the aggregate that determines Zenith-finals eligibility.
2. **Facility progression** — the rank-to-level mapping for each stat, derived from team stat ranks, shown as the current rank transitioning to the next level.
3. **Team stat ranks** — the five-letter-rank grid (Speed, Stamina, Power, Guts, Wit), the same scale that drives facility levels.
4. **Team race coverage** — a distance-by-distance checklist (Sprint, Mile, Medium, Long, Dirt) that surfaces aptitude and available-skill gaps per category.

All values must come from the actual team data — the cockpit's team panel builder and the recorded team state — and the rules engine; the wireframe above is illustrative only.

**Dated note (2026-10-21).** The data source named here was wrong and is corrected. The block previously said the values come "via `TrainingRun::gradeObjectives()`", but that method reads the `grade_objective_labels` and `grade_objectives.standard` config keys — the **Trackblazer** Grade Point tracks, keyed to the three Trackblazer profiles whose selection rule is itself open (KI-15). It reads no team data and is not the Team Board's source. The Team Board's ranks, levels, roster and next-Cup countdown come from the recorded team state and the Unity Cup panel builder, and the rank-to-level mapping is the `unity_cup` entry's `team_rank_ladder` in `config/scenarios.php`. The requirement itself — real data, no illustration in the product — is unchanged.

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

**Which half of a burst these tables are, and where the other half is.** §10's tables are the **trainee's**
share of the burst — the "moderate boost to your trainee" — not a total. The source says so directly:
"Explosions give two stat bonuses, one big boost for your team members (the chart below) and a moderate
boost for your trainee. The big boost to your team member's stats are based on their Support Card Type …
The moderate boost for your trainee is based on the facility being trained at"
([umareference.com, Unity Cup Scenario: Advanced Training Strategy, 2026-08-26](https://www.umareference.com/guide/advanced-training-strategy/unity-cup-scenario-1),
tier `[B]`). The teammate's half is a separate and much larger table — Speed 150 / Stamina 80 / Power 110 /
Guts 80 / Wit 70, rising to 150 on the row's own stat — and it is **scaled by that member's growth bonus**:
"The above chart is affected by your uma's growth bonuses … These stats are given to each team member." So a
burst writes two different numbers to two different stat lines, and a form that stores one field for it will
be wrong by about a factor of ten. **Source-specific note on the Wit cell, 2026-10-21:** gametora's rendering
of the trainee table gives Wit +15 with 5 Skill points on every row; game8.co's rendering of the same table
gives Wit +10 with the Skill Point cell filled only on the Wit row. The two are recorded side by side rather
than reconciled — game8.co carries no 150-scale teammate table at all, so the earlier "reads 5 lower" flag
was a comparison of two different tables — and the value loaded here is gametora's. **The JP side names Wit
as the mechanic's exception**, which is independent support for reading that differing Wit cell as designed
rather than miscopied: 「アオハル魂爆発での能力上昇量は賢さ以外は「+15」の固定数値で、ウマ娘の成長率やサポートの
トレーニング効果アップの影響を受けません。また、トレーニングLvにも影響されない」
([game8.jp 395608, ⚠️ STALE: 2025-11-20](https://game8.jp/umamusume/395608), tier `[A]`, `[JP]`). Note the
tension: that JP sentence says the gain is *not* affected by growth rate, while the teammate table above
*is* scaled by that member's growth bonus. The two describe different halves of the two-table mechanic, so
both are recorded and neither is rewritten to fit the other. **Closed 2026-10-21: the difference is a version
boundary, not a discrepancy.** gametora's Unity Cup page carries **both** the current and the pre-rework
trainee-share table under its own "Before the Update → Pre-Update Spirit Bursts" heading, and the
pre-rework Wit row is **2 / 10 / 5** with the Skill Point cell filled only on the Wit row — game8.co's
rendering, exactly, in both cells. So game8.co's table is a **vintage capture of the pre-rework scenario**,
and the current table is gametora's Wit 2 / 15 / 5 with 5 Skill Points on every row, which is the value
loaded here. The JP guide's naming of Wit as the exception is consistent with a cell that was re-specified
at the rework.

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

*Illustrative only.* The opponent, the round number, the league rank, the alarm-clock count, Tazuna's
circles and the total are a worked example. The sourced parts are the **shape** and the **rules**:
a team race is five races, one each of Sprint, Mile, Medium, Long and Dirt; three or more wins
raises the league rank and a loss lowers it; an Alarm Clock may be used on a team-race loss since the
2026-07-01 rework; and "at least 3 circles in total" is uma.guide's own safety-margin reading, which
the `unity_cup` entry of `config/scenarios.php` carries as `team_race.circles_guidance` = 3 with the
note that the circle estimate is the client's own display and is never computed.

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

*Illustrative only.* The three eligibility values are a worked example. The three **conditions** are
the sourced ones — a league rank of 10 or above, an overall team rank of A or above, and at least
one Extreme Spirit Burst — and they are carried in the `unity_cup` entry of `config/scenarios.php`
and in the Game8 Unity Cup numeric-effects table, which prints them together.

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

*Illustrative only.* Every member, rank, need and contribution in the table is a worked example.
The two sourced rules in the block are the footnote — story characters join the team but carry no
bond gauge and cannot do Friendship training — and the "not dependent on a bond gauge" half of the
team-rank rule, which the Game8 Unity Cup team-race section states alongside the rule that a
teammate's rank rises only through Special Training.

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

*Illustrative only.* Every projection, coverage figure and risk line is a worked example. The two
rules the block rests on are the §8a rank-to-level mapping — a C rank is Lv 3, so the facility does
not move — and the rule that a member's own rank is the only thing that lifts it. The projections
are the kind of thing this plan's "what I would not build" list forbids the tool from computing:
the client shows the opponent's own circles, and the tool records what the Trainer saw.

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

*Illustrative only.* The burst count, the team rank and the skill-event timing are a worked example.
The sourced parts are the **rules**: the team ranks S and S+ and what each grants, the four
burst-count bands with their hint levels and bonuses in the table above, the "Team Zenith Declares
War" event and the rule that its skill type follows the team's highest stat rank, and the fact that
the three URA Finals races follow. The burst-count bands are also the `spirit_burst_bands` key in
the `unity_cup` entry of `config/scenarios.php`, and the payout timing is that entry's
`burst_payout_timing`.

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

Every item below is preserved. Each carries a status and the date it was last reviewed: `OPEN` (no
source and no code settles it), `PARTIALLY RESOLVED` (one half is settled and the other is not),
`RESOLVED` (a source or the repository settles it), `SUPERSEDED` (a later source replaced it).

**Reviewed 2026-10-21.**

1. **PARTIALLY RESOLVED — re-checked 2026-10-21. Rank stat thresholds.** GameTora says members are ranked by the average of their stats, and the average-stat half of this question now has a source: "The training levels are based on the average stat of your umas. Speed training is based on their average Speed stat, etc." with the ladder **Level 2 → 200, Level 3 → 340, Level 4 → 510, Level 5 → 610** (approximate; the source prints them as plain thresholds and hedges in the surrounding prose) ([umareference.com, Unity Cup Scenario: Advanced Training Strategy, 2026-08-26](https://www.umareference.com/guide/advanced-training-strategy/unity-cup-scenario-1), tier `[B]`). Paired with the `unity_cup` entry's `team_rank_ladder` = G/F → 1, D/E → 2, B/C → 3, A → 4, S → 5, those four numbers are the boundaries **between** the bands: the average at which the E/D band begins, then C/B, then A, then S. **What is still open is the boundary inside a band** — where G ends and F begins, where E ends and D begins, where C ends and B begins — because the ladder maps two letter ranks to each level and the source prints one number per level. A second carrier was looked for and could not be used: the "Umamusume Global Reference Document" Google Doc is by the same author and its body sits behind a sign-in, so the thresholds stay single-source `[B]`. The **mapping** is a separate matter and is now carried twice: gametora's table above, and game8.jp's Aoharu Hai guide, which prints the same five rungs as an image whose alt text reads 【G〜F】【E〜D】【C〜B】【A】【S】 over Lv1 Lv2 Lv3 Lv4 Lv5 — the same rank-to-level correspondence, in the same order ([game8.jp 395608, ⚠️ STALE: 2025-11-20](https://game8.jp/umamusume/395608), tier `[A]`, `[JP]`). Five rungs there because it is the JP table, which predates the Global S+ rank. Read the rank from the game, and only project a rank if the rule becomes available — the remaining within-band boundary is observable and does not need to be guessed.

2. **RESOLVED — re-checked 2026-10-21. Burst payout split.** A burst writes **two** numbers and both halves are now published. The **teammate's** half is the larger table — Speed 150 / Stamina 80 / Power 110 / Guts 80 / Wit 70, rising to 150 on the row's own stat — scaled by that member's growth bonus and given to each member: "Explosions give two stat bonuses, one big boost for your team members (the chart below) and a moderate boost for your trainee. The big boost to your team member's stats are based on their Support Card Type … The above chart is affected by your uma's growth bonuses … These stats are given to each team member" ([umareference.com, Unity Cup Scenario: Advanced Training Strategy, 2026-08-26](https://www.umareference.com/guide/advanced-training-strategy/unity-cup-scenario-1), tier `[B]`). The **trainee's** half is the moderate boost "based on the facility being trained at", and it is what §10's tables already carry. The item's premise was wrong in one respect and that matters: **§10's tables are not totals** — they are the trainee's share, keyed by facility, so §10 was reading a half as a whole. §10 now names the two halves, and the split is not a ratio any more but two published tables. **Dated note (2026-10-21).** The Advisor needs to store them as two fields, one per side, rather than one total; no screen above changes in this pass.

3. **RESOLVED — reviewed 2026-10-21. Burst-count breakpoints.** Post-update sources agree on four non-overlapping bands: 4–6, 7–9, 10–12, 13+ (the "Exclusive Skills from Spirit Burst Count" section of `docs/scenarios/02-unity-cup.md`, the Spirit Burst skill-reward table in the Game8 Unity Cup section of `docs/research-scratch/SCENARIO-PUBLISHER-REFERENCES.md`, and the `spirit_burst_bands` key in the `unity_cup` entry of `config/scenarios.php`). The earlier "4 to 9" / "7 to 9" overlap was pre-rework; the corrected values are non-overlapping and the plan's 4 / 7 / 10 / 13 breakpoints are confirmed. **Dated note (2026-10-21).** The publisher-reference citation is corrected: the Game8 burst-reward table runs to the fourth band, and the earlier `:1105-1108` stopped one row short of the 4–6 band that the resolution depends on. The `docs/scenarios/02-unity-cup.md` citation and the config key both still hold.

4. **RESOLVED — reviewed 2026-10-21. Event timing.** Post-rework sources place the burst-count skill event at **Senior Year, Late November**: the "Team Zenith Declares War" event in the "Exclusive Skills from Spirit Burst Count" section of `docs/scenarios/02-unity-cup.md`, which also records that this file previously dated the payout to "early November" and that its own white/gold framing supersedes the withdrawn tier names; the "Team Names and Skills" material in the GameTora Unity Cup section of `docs/research-scratch/SCENARIO-PUBLISHER-REFERENCES.md`; and the repository itself, whose `unity_cup` entry carries `'burst_payout_timing' => 'Senior Year, Late November'` in `config/scenarios.php` and cites `docs/scenarios/02-unity-cup.md:220` for it. The Game8 extract that printed "Early November" was pre-update; the plan correctly adopted Late November. **Dated note (2026-10-21).** The earlier publisher-reference citation was wrong: the row it named was a burst-reward row, not the timing row. Corrected, that row is the Game8 table's "Senior Year Early November" line, which **still prints Early November** and is tagged `[Both]`. The divergence is recorded rather than deleted: the repository has ruled Late November in config and in the scenario guide, the GameTora section agrees, and the Game8 table is the one source that dissents. If the Game8 row is ever corrected, this item's basis is unchanged.

5. **RESOLVED — reviewed 2026-10-21. Elite team colour.** The elite team's background is **pink/purple** — the discrepancy was that GameTora's update-list entry used "purple" for Extreme Spirit Bursts while a later section used "pink" for the elite team itself; both colours describe the same pink/purple background (the "Team Composition Rules" section of `docs/scenarios/02-unity-cup.md`, the Game8 Unity Cup numeric-effects table in `docs/research-scratch/SCENARIO-PUBLISHER-REFERENCES.md`, which prints "Rank 2 team, pink background", and the Team Board tree in `docs/research-scratch/UX-DELIVERABLES.md`, which prints "An Elite Team appears (pink/purple background,"). **Dated note (2026-10-21).** Two line numbers are corrected: the Game8 row is the "pink background" line, not the row above it, and the `UX-DELIVERABLES.md` tree line is further down the file than the one the item named.

6. **OPEN — reviewed 2026-10-21. Pre-update Game8 data.** Game8's team-rank all-stat bonuses (F +2 up to S +5) and its "rank 5 gives +50" claim before the finals predate the update, so they are left out. **Status is `OPEN` rather than resolved, and the distinction is deliberate:** excluding the figures is a rule this plan follows, not a resolution of the question underneath. No post-update source read supplies the replacement values for the per-rank all-stat bonus or for the finals bonus, so the figures the plan needs in their place are still `unknown`. The two stale rows remain visible in the publisher master rather than being deleted, and the `unity_cup` entry in `config/scenarios.php` does not carry either.

7. **RESOLVED — reviewed 2026-10-21. Calendar.** All five team-race dates are confirmed from post-update sources: Round 1 Junior Late Dec, Round 2 Classic Late Jun, Round 3 Classic Late Dec, Round 4 Senior Late Jun, Finals Senior Late Dec (the Game8 Unity Cup "Phase structure" section in `docs/research-scratch/SCENARIO-PUBLISHER-REFERENCES.md`, and the "Core Mechanic: Team Races" table in `docs/scenarios/02-unity-cup.md`). The repository carries the same cadence: the `unity_cup` entry's `team_race.occurs_every_months` is 6. **Dated note (2026-10-21).** The publisher-reference citation is corrected: the five rounds and the Finals occupy six consecutive lines of the phase-structure list, and the earlier `:1021-1025` started one line early and stopped one line short of the Finals.

8. **RESOLVED — reviewed 2026-10-21. "Result Pts".** `docs/research-scratch/DESIGN-CORPUS.md` records `Result Pts` as an observed Unity Cup UI counter, measured from captured frames alongside Team Rank and Spirit Burst counts: the scenario-chrome paragraph naming "a `Result Pts` counter and a `TEAM RANK B 11/16` badge", the currency-identity rule that lists "Grade Points, Shop Coins, Team Rank, Result Pts and Spirit Burst counts", and the scenario-coverage row describing the "`TEAM RANK B 11/16` and `Result Pts` counters" in the Unity Cup frames. The term reaches the repo from client captures, not just the reference. **Dated note (2026-10-21).** All four line numbers this item carried were wrong — three pointed at blank lines and one at a warning paragraph about the owner-supplied files. The three references above replace them; the substance of the resolution is unchanged.

9. **RESOLVED — reviewed 2026-10-21. Pal membership.** `docs/scenarios/02-unity-cup.md:56` states explicitly: "**Pal-type cards are excluded from the team roster**, so a Pal buys you no Spirit Bursts at all." The GameTora team description is corroborated by the scenario guide. **Dated note (2026-10-21).** The earlier citation also named `:261`, which is a best-strategy bullet and does not carry the statement; the sentence appears once, on the line cited above. The resolution stands on the one source that prints it.

10. **PARTIALLY RESOLVED — reviewed 2026-10-21. Alarm Clock limits on team-race retries.** The 2026-07-01 rework added Alarm Clock retry on a lost Team Race, confirmed live (the "July 1, 2026 Update" and "Unity Cup Team Races" sections and the Game8 Unity Cup special-rule list in `docs/research-scratch/SCENARIO-PUBLISHER-REFERENCES.md`; the "Core Mechanic: Team Races" section of `docs/scenarios/02-unity-cup.md`; and the `unity_cup` entry's `team_race.loss_retryable_with_alarm_clock` in `config/scenarios.php`, which is `true`). The general per-career cap ("up to 5 Alarm Clocks per career", from the Trackblazer numeric-effects table in the same publisher master) is from the data export but is not Unity-Cup-specific; the per-run allowance and resume turn for a team-race retry remain undocumented. **Dated note (2026-10-21).** The three publisher line numbers this item carried are corrected — each pointed at a neighbouring line in the same table rather than at the row that carries the Alarm Clock fact — and the config key is named, which is what makes the retry rule `verified` rather than `community`.

11. **OPEN — reviewed 2026-10-21. Names.** Team names and skill names are from English guides, not captured client strings. The names themselves are `community`: the table in §3 and the Team Board tree in `docs/research-scratch/UX-DELIVERABLES.md` both name them, and §3's table is additionally tagged as pre-update Game8 copy. A `Result Pts`-style capture of the Unity Cup scenario-select screen would settle the team names, and none is in the tree.

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
> You are 18,800 fans short of the 70,000-fan gate with 8 turns remaining, and Stamina is below the Medium-distance floor (600–700).
>
> - **Train Stamina (Lv 3):** +6 Sta, +3 Guts, +5 SP. The facility is Lv 3 because your team's Stamina rank is C, and C shares Lv 3 with B; the facility reaches Lv 4 when the team rank reaches A.
> - **Alternative — train with a teammate on Wit:** +energy, but the team stat it raises is not the one the gate needs.
> - **Confidence: verified** (facility level by team stat rank, config `facility_level_source = 'team_rank'`; the rank-to-level mapping is the `unity_cup` entry's `team_rank_ladder`).
> - **Switch if:** a team race falls in 6 turns (bank energy first), or the team Stamina rank reaches A, which lifts the facility to Lv 4 on its own.

> **Dated note (2026-10-21).** The example above was rewritten, because it contradicted this
> document's own §1, §2 and §8. What it carried before, and why each part was wrong for Unity Cup:
>
> - **"Facility is 4/12 to Lv 4"** stated a repetition count. Unity Cup has no repetition-based
>   facility level: the `unity_cup` entry's `facility_level_source` in `config/scenarios.php` is
>   `team_rank`, and the level follows the team's stat rank through the same entry's
>   `team_rank_ladder`. A "4/12" repetition count is URA Finale's rule and belongs to URA's example
>   in §12.
> - **"Confidence: verified (facility level by repetition, config `facility_level_source = 'repetition'`)"**
>   cited a config key that does not hold that value for this scenario. The badge now cites the
>   rule that does hold — `facility_level_source = 'team_rank'` — and names the mapping beside it.
> - **"Alternative — race a G3 (Mile): +100 coins, +60 GP if 1st"** offered Grade Points and shop
>   coins, which are **Trackblazer** mechanics. Unity Cup has no Grade Point ladder and no shop
>   (`grade_objectives` and `shop` are both `false` in the `unity_cup` entry's `panels` map). The
>   alternative is now a Unity-Cup-shaped action.
> - **"Switch if: … Akikawa bond reaches 3-bar (Fan Fest gate opened)"** named a gate condition
>   that does not exist here. §3 records that Akikawa is absent from Unity Cup, so the April gate
>   has no three-bar requirement. The switch condition is now a team-race timing and a team-rank
>   condition, both of which are this scenario's own.
> - **"18,800 fans short of the Classic gate"** named a Classic-year gate. Unity Cup's three gates
>   are the URA fan gates: 60,000 by Valentine's Day, 70,000 by early April, 120,000 by late
>   December, all in Senior year. The example now names the 70,000-fan gate.
>
> The shape of the contract is unchanged, and no level of the vocabulary is changed: `verified` is
> still the right badge, because the rule it names is one the repository holds exactly.

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

Two further values are used where the three above would mislead, and they are not a fifth level of confidence:

- **UNASSESSED — requires code audit** — nobody looked. It is not a synonym for `Implemented`.
- **Not implemented** — a code audit was done and found no counterpart for the feature in the tree. It is recorded so that an absent feature is never read as an unaudited one.

### Per-feature status

Audit date: **2026-10-21**, a read of the code and the test files on `master` at `d00a0457`. The audit establishes that a surface exists and that a test pins it. It compares nothing against an observed client screen, so no row is `Game-validated`.

| Feature | Level | Evidence | What this audit did not confirm |
| ------- | ----- | -------- | ------------------------------ |
| Career Cockpit | `Verified` | `app/Services/Career/TeamPanelBuilder.php` builds the team section and `app/Http/Controllers/Career/CockpitController.php` composes it; `resources/js/components/scenario/UnityCupPanel.vue` is registered for the `team_race` and `team_rank_ladder` panel flags; `tests/Feature/UnityCupPanelTest.php` (7 cases) and `tests/browser/unity-cup-panel.spec.ts` (12 cases) exercise it. | The §18 criterion "shows trainee stats, team ranks, burst gauges, and next cup date in a single view" is confirmed for the team, burst and race parts. The **next cup date** is `unknown` in the tree — the team section carries a league placement and a preseason-wins count, not a dated next cup — and the spec asserts the team-race panel's *shape* and margin, not a date. |
| Team Board | `Verified` | `resources/js/components/scenario/UnityCupTeamBoard.vue` is mounted by `UnityCupPanel.vue`; `tests/browser/unity-cup-panel.spec.ts` covers the rank gauge, the five stat grades and the roster, including their empty states; `tests/Feature/UnityCupPanelTest.php` covers the empty and partial states. | §8a's **manual team assignment** and the per-member "available skills" display were not audited in this pass. The board's coverage table is confirmed; the editing affordance is not. |
| Spirit Burst planner | `Verified` | `resources/js/components/scenario/SpiritBurstRoster.vue` and the burst state set `chargeable / charged / held / spent / extreme_ready / extreme_spent` in the `unity_cup` entry of `config/scenarios.php`; `tests/Feature/UnityCupPanelTest.php` asserts the configured burst bands, the payout timing and the Special Training facts; `tests/browser/unity-cup-panel.spec.ts` asserts the Spirit meter and the named absences. | The §18 criterion "warns when a burst is missed (turn passed without cashing)" was not audited in this pass. The state machine and the band table are confirmed; the missed-burst warning is not. |
| Unity Training evaluator | **Not implemented** | No counterpart exists in the tree. The `TrainingRun` model carries `unity_trainings_count`, and the model docblock says it is "the tally **the Trainer reports**" — a recorded count, not a computed one. `app/` contains no flame computation at all, so the §18 criterion "identifies multi-member trainings worth 3+ flames" has nothing behind it, and the published per-flame table in §8 has no code that reads it. | Nothing. The absence is the finding. The plan's own §18 and the MVP table's priority 3 describe a feature the tree does not carry; that gap is recorded here rather than left implied by the absence of a row. |
| Unity Cup match planner | `Verified` | `resources/js/components/scenario/UnityCupTournamentReadiness.vue`; `tests/browser/unity-cup-panel.spec.ts` asserts that the five team-race categories draw as a table with the published ask and one named absence, and that the panel "prints no opponent projection, no chance and no percentage" — the absence the plan's "what I would not build" list requires. `app/Services/Career/TeamPanelBuilder.php` builds the category table; the `unity_cup` entry's `team_race.circles_guidance` is 3 and its comment records that the circle estimate is the client's own display, never computed. | The §18 criterion "assigns 5 racers to 5 distances" is confirmed as the **category table**; the per-category racer assignment was not audited in this pass. |
| Recommendation reconciliation | `Verified` | `app/Services/Advisor/NextDecision.php` carries the `reconciliation` slot — the action the Trainer took against the action the rail recommended — and `tests/Feature/TurnChoiceTest.php` asserts the null, the match and both notes; `tests/browser/career-cockpit.spec.ts` covers it. | The §9 "dual-value readout" — the trainee gain this turn against the team-gain cost — is confirmed to **exist**: `tests/Feature/UnityCupSurfacesTest.php` pins the "Trainee value" and "Team value" rows on the advised training action. **One of those pins is red on this tree** — the "Team value" title no longer contains `team_rank_ladder` — so the feature is present and one of its assertions is failing at `HEAD`, independently of this document. |

**No feature in this document is `Game-validated`.** Nothing here was compared against an observed client screen or an authoritative capture. The mechanics behind the scenario come from GameTora, Game8 and uma.guide, which are `verified` as *citations* and `community` as *game facts*.

**Dated note (2026-10-21).** This table did not exist before this pass: the section defined three levels and assigned none. Every level above was assigned from a named file read on 2026-10-21, and the "not confirmed" column records the gaps rather than smoothing them.

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
