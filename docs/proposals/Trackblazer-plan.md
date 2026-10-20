# Trackblazer — Implementation Plan

**Project:** Trainer Desk 2.0  
**Scenario:** Trackblazer (Make a New Track / MANT)  
**Purpose:** Spec the Trackblazer career cockpit, the screens that differ from Grand Concert, and the pre-run planning flow.  
**Last verified:** 2026-10-21, against `master` at `d00a0457` — the tree as it stood when this pass began. Every line-number citation was re-checked against the file it names, and every claim a reader might act on was re-checked against the repository or the source it cites. Where a check fails, the claim is marked rather than deleted. A concurrent session committed `15bba0f9` and `d664f948` while the pass ran; neither touches `config/scenarios.php`, `SCREEN_SPEC.md`, `docs/scenarios/`, `docs/research-scratch/`, `app/` or `tests/Feature/`, so every file this document names was verified against the same content it names.

**Status vocabulary.** This document is a design plan, not a roadmap. Its features carry the levels `Implemented`, `Verified`, `Game-validated` and `UNASSESSED — requires code audit`, defined in §21 below and assigned per feature there. It does not use the roadmap's `COMPLETE` / `BLOCKED BY EVIDENCE` slice statuses, and the Our Grand Concert roadmap does not use the feature levels; the two vocabularies are deliberately different and neither is a synonym for the other. **Source badges** are shared across all four scenario documents: `verified` (established in this repository or by a tier-S / tier-A source), `derived` (computed from a verified fact by a stated rule), `configurable` (a value the tool reads from configuration), `community` (a third-party guide, guidance and not a game fact), `unknown` (no cited source establishes it).

## Sources

Each source on one line: name — URL or path — last-updated date — the claims it supports.

- `docs/UMAMUSUME_REFERENCE.md` §1.3.4, §1.4.8, §2.2.3, §2.5, §2.7 — repository path, no URL — last verified 2026-10-21 — caps and soft caps, where each support-card effect lands in a run, the Trackblazer `[Global]` mechanics summary, the resolutions from the full Game8 read, and the confirmed Trackblazer caps with their server caveat.
- [GameTora, Trackblazer Scenario](https://gametora.com/umamusume/trackblazer) — last updated 2026-03-12 — the scenario's basics, the Grade Point and shop-coin values, the shop catalogue, the finale rules, and the Unique Skill level-up gate.
- [uma.guide, Trackblazer Guide](https://uma.guide/guides/trackblazer) — last updated Apr 29, 2026 — the deck archetypes, the Grade Point and placement-modifier tables, the shop rules and reserve advice, the race-fatigue odds, the epithet routes, the Summer camp window, and the finale briefing.
- [Game8, Trackblazer Scenario Guide](https://game8.co/games/Umamusume-Pretty-Derby/archives/580723) — **pre-release copy**, page dated August 25, 2026 — the scenario's phase structure and its numeric effects, including the per-career Alarm Clock cap and the Summer camp window.
- `docs/research-scratch/SCENARIO-PUBLISHER-REFERENCES.md` — repository path, no URL — last verified 2026-10-21 — the verbatim extractions of every publisher page above, under the "Trackblazer (uma.guide)", "Trackblazer (GameTora)" and Game8 Trackblazer sections.
- `docs/scenarios/03-trackblazer.md` — repository path, no URL — last verified 2026-10-21 — the repo-authored Trackblazer guide.
- `config/scenarios.php`, the `trackblazer` entry — repository path, no URL — last verified 2026-10-21 — `cap_bonus`, `live_on_global`, `facility_level_source` = `repetition_plus_purchases`, the three Grade Point profiles, the shop rules, the epithet routes and the finale rules.
- `docs/UMAMUSUME_REFERENCE.md` §2.2.3 and `app/Models/TrainingRun.php` — repository paths, no URL — last verified 2026-10-21 — the fact that the tool renders only the `standard` Grade Point track, and the two open defects that record it.

**Dated note (2026-10-21).** The Sources list previously named `docs/research-scratch/SCENARIO-PUBLISHER-REFERENCES.md` by file and the reference by "sections" without dates, URLs or a claims column. It is normalised above to one line per source in the form *name — URL or path — last-updated date — claims supported*, matching the URA Finale and Unity Cup documents. The publisher master's own section names are used in place of its line numbers throughout this document; see the note under §15 for why.

---

## 1. The Central UX Concept

Grand Concert is a conversion loop: training makes Performance, Lessons turn it into Songs, Songs raise the Hype gauge. Trackblazer is an economy loop with a hard deadline. The persistent question changes to:

**Dated note (2026-10-21).** "Hype" in the sentence above is GameTora's name for the gauge the `[Global]` client calls **Live Performance Expectations**. It is used here because it is the word the guide uses in the sentence being paraphrased, and it is not this application's vocabulary. The settled client terms for that scenario are **Dance, Passion, Vocals, Visuals** and **Composure** — never "Mental", which is GameTora's fifth type — with **Promo Concert** and **Grand Concert** as the client nouns for the events. `docs/proposals/Our-Grand-Concert-plan.md` carries the full set and the evidence for it; see its "Client terminology (settled)" section.

**Race, train, or rest this turn, and does it keep me alive at the next Grade Point checkpoint?**

About half of all turns are races, and a typical career runs 30 to 40 of them. That is why the race decision, not the training decision, should lead the cockpit.

```
   RACE ──► placement ──► Grade Points + Shop Coins + stats/SP/fans
    ▲                          │                 │
    │                          ▼                 ▼
    │                   CHECKPOINT          SPECIAL SHOP
    │                  (60 / 300 / 300)          │
    │                   miss = run ends          ▼
    │                                    ITEMS (energy, training
    │                                    boosts, race bonus, ...)
    │                                            │
    └──────── stronger races / training ◄────────┘

   EPITHMET ROUTES: win every race in a route = bonus stats/hints
   FINALE: Twinkle Star Climax (3 races, Victory Points)
```

Three meters stay on screen all run: Grade Point runway, Coin bank, and Race fatigue. They play the role Performance played in the Grand Concert cockpit.

---

## 2. Scenario Select

The scenario card should show what makes Trackblazer different. It also loads the rules engine.

```
TRACKBLAZER
Race-driven career. Grade Points replace career goals.

Stat caps   SPD 1200  STA 1900  POW 1200  GUT 1200  WIT 1500
No career goals  •  No secret events  •  No Scenario Link
```

The caps are the figures in the reference, and whether they are live on Global today is still open (section 15). The engine loads these tables, each with a source badge:

**Dated note (2026-10-21).** The sentence above is stale on one point and is corrected in place. §15 item 2 records the caps question as **RESOLVED**, and the repository agrees: the `trackblazer` entry's `cap_bonus` in `config/scenarios.php` is `['Speed' => 0, 'Stamina' => 700, 'Power' => 0, 'Guts' => 0, 'Wit' => 300]` over the 1200 base, which reads as 1200 / 1900 / 1200 / 1200 / 1500 — the five figures printed above — and the same entry's `live_on_global` is `2026-03-12`, exactly the date §15 item 2 names. The two sections now agree, as they must.

**Cap badge: `verified`** for the figures, and `configurable` for the values themselves, which the tool reads from config rather than hard-coding. The confidence in the *currency* of the figures is the part §15 item 2 records, and it names the two artifacts that settle it: the GameTora export fetched 2026-09-27 (after the 2026-07-01 rework), and the config's own `live_on_global` date. The "not yet published elsewhere" caveat in that item is about the absence of a second publisher, not about the repo's own record.

- Grade Point objectives, by checkpoint profile
- Grade Points per race grade, and the placement modifiers
- Shop Coins by placement
- The shop catalog
- Race-fatigue odds
- Epithet routes
- Year-end unique-skill gates
- Finale rules

---

## 3. Trainee & Target

Trackblazer needs four checks that the Grand Concert flow did not.

**Checkpoint profile.** Both sources agree on three of the numbers, so default to the standard profile and let the Trainer override it:

- Standard: 60 / 300 / 300.
- High-dirt, low-turf trainees (Haru Urara is the example both sources give): 30 / 200 / 300.
- Short-distance turf trainees (Curren Chan is GameTora's example): sources disagree on the first number (section 15).

Show the profile as editable, with a source badge, and tell the Trainer to confirm it against the in-game objectives screen.

`config/scenarios.php` carries all three tracks (`standard` / `dirt_leaning` / `limited_turf_range`); `TrainingRun::gradeObjectives()` renders only `standard` and says so, because a wrong denominator is worse than a conservative one. Tracked as **KI-15**, OPEN.

**Race coverage.** Mile and Medium dominate graded racing. By uma.guide's counts, 24 of 30 unique G1s, 25 of 36 G2s and 50 of 69 G3s are Mile or Medium. The guide says to bring at least a C, ideally a B, in both. So show a coverage number:

```
RACE COVERAGE
Graded races this trainee can run at C or better:
G1  21 / 30    G2  22 / 36    G3  44 / 69
⚠ Weak Medium aptitude: 14 G1s are Medium
```

The values above are illustrative. The real numbers come from the aptitude data joined to the race list.

**Secret-event warnings.** Secret events and career goals are disabled. Both guides give the same example: Silence Suzuka cannot get Runaway here. If the Trainer's target relies on a secret event, flag it before the run starts.

`docs/UMAMUSUME_REFERENCE.md` §2.2.3 confirms: "Character secret events do not fire here, which is the mechanical reason a character cannot unlock an extra strategy option in this scenario."

**Cap-aware targets.** Speed, Power and Guts cap at 1200, Stamina at 1900, Wit at 1500. The target screen should show headroom per stat, so a Trainer does not aim Speed at 1400.

---

## 4. Legacy Lab (Deltas Only)

- No Scenario Link badge, because Trackblazer has zero linked characters. `docs/UMAMUSUME_REFERENCE.md` §2.2.3: "this scenario has zero linked characters, the only one of the fourteen with none."
- The scenario factor is **"Climax Scenario"**, which gives Stamina and Guts on inheritance. `docs/UMAMUSUME_REFERENCE.md` §2.7: "The Trackblazer scenario factor is printed as 'Climax Scenario' and grants Stamina and Guts bonuses on successful inheritance."
- Only show cap-raise projections where the data supports them. The cap-breakthrough rule in the reference is JP-tagged. The Global 2026-07-01 rework raises caps above the 1200 base; whether the raised Trackblazer caps (1900 Stamina, 1500 Wit) are live on Global today is open (section 15).

---

## 5. Support Deck Builder (Deltas)

Deck effects you evaluate:

- Race Bonus (effect id 15). uma.guide says to stay at or above 50. Draw a meter with a marker at 50, labelled as a community guideline and not a game rule.
- Fan Bonus (16).
- Skill Point Bonus (30).
- Failure Protection (27).
- Energy Cost Reduction (28).
- Bond coverage.

Archetypes are suggestions, not rules. The guide describes two frameworks:

- Speed + Wit: 2 Speed and 2 Wit, with the remaining slots flexed for skills or Stamina.
- Wit + Guts: 3 Guts and 2 Wit, leaning on Guts Ankle Weights. It can cap Guts, but skills and Stamina are harder to source.

Detect which shape the deck resembles, but evaluate the real cards.

**Friendship is valued differently here.** uma.guide says a lone friendship training often isn't worth skipping a graded race, because race rewards are high and Trackblazer training bases are low. Base facility values match Unity Cup's (lower than URA Finale's): Speed +8/+4/+2 SP, Stamina +7/+3/+2 SP, Power +4/+6/+2 SP, Guts +3/+3/+6/+2 SP, Wit +2/+6/+3 SP (energy costs −19 to −20, Wit +5). The Advisor should compare friendship value against race value instead of assuming friendship wins.

Source: `docs/research-scratch/SCENARIO-PUBLISHER-REFERENCES.md` — the "Base Training Values (Facility Level 1, No Supports)" table in the GameTora Trackblazer section, and the base-training-values table in the uma.guide Trackblazer section under its "Classic/Senior Year" subsection. The Game8 Trackblazer section's "Training-relevant decision the scenario forces" states the same repetition rule for this scenario ("Leveling up facilities in Trackblazer works just like the URA Finale, where they increase in level the more you use them") and names the Training Application item.

**Dated note (2026-10-21).** The citation previously named two line ranges in the publisher master, one of which (`:1276-1280`) is the Game8 "Training-relevant decision" bullet and the other (`:260-268`) is the uma.guide base-training-values table. Section names are used in place of both. The figures themselves are unchanged and re-verified, and the section names are what carry the citation from here: the master grows, and a line number in it rots at the next commit.

---

## 6. Route Planner (New, and the Key Pre-Run Screen)

This takes the place the Concert Planner held in the Grand Concert flow. The route is a plan, not a script, and both guides say to deviate when a training turn is exceptional.

```
ROUTE PLAN                             Rice Shower • Standard profile

JUNIOR         ──●debut───────────────────●60 GP checkpoint──
 GP this period   0 / 60   planned: 100   floor (2nd-3rd): 52

CLASSIC        ─────[SUMMER]──────────────●300 GP checkpoint─
 GP this period   0 / 300  planned: 340   floor: 214

SENIOR         ─────[SUMMER]──────────────●300 GP─[FINALE]───
 GP this period   0 / 300  planned: 320   floor: 190

EPITHMET ROUTES IN PLAN
 Oka Sho / Oaks / Shuka Sho line    1 of 3 planned   ⚠ 2 races unscheduled
 Spring Champion                    0 of 3
 Mile route                         2 of 3

FLAGS
 ⚠ 5 consecutive races planned in Classic spring (fatigue risk)
 ⚠ Year-end gate, Classic: 60,000 fans + Akikawa bond 31
 ○ Rival races are random; they cannot be scheduled
```

The numbers above are illustrative. What is sourced:

- Grade Points for a win: G1 100, G2 80, G3 60, OP 40, Pre-OP 20. Source: the "Grade Points (Replacing Career Goals)" section of the uma.guide Trackblazer section in `docs/research-scratch/SCENARIO-PUBLISHER-REFERENCES.md`, which prints the per-grade table.
- Placement modifiers (uma.guide, single source): 1st 100%, 2nd 60%, 3rd 40%, 4th-5th 20%, 6th and lower 10%. Source: the "Placement modifier" table in that same "Grade Points (Replacing Career Goals)" section.

**Dated note (2026-10-21).** The two line ranges this bullet cited are replaced by the two section and table names that carry the same content — the per-grade Grade Point table and the "Placement modifier" table, both under "Grade Points (Replacing Career Goals)". The figures are re-verified and unchanged.
- Points never carry over. Each period starts at zero. Source: both publishers, confirmed in `docs/UMAMUSUME_REFERENCE.md` §2.2.3.

So the planner can show three numbers per period: needed, planned if you win, and a floor if you finish 2nd to 3rd. A G2 win is 80 and a G2 second place is 48.

---

## 7. Run Preflight

```
RUN PREFLIGHT

TRAINEE   Rice Shower     SCENARIO  Trackblazer
PROFILE   Standard 60 / 300 / 300 (confirm in game)

CHECKPOINTS
 ✓ Junior 60 GP reachable (plan 100, floor 52)
 ⚠ Floor is under target: needs 1+ win
 ✓ Classic / Senior reachable

RACE COVERAGE      ✓ Mile B  ✓ Medium A  ○ Long A
DECK               ✓ Race Bonus 56%   ⚠ Stamina coverage low
ROUTES             ✓ 3 epithet routes planned
YEAR-END GATES     ⚠ Akikawa bond plan needed (19 / 31 / 51)
SECRET EVENTS      ✓ Plan does not depend on any

RULE CONFIDENCE
 GP table, coin table, shop    community-sourced
 Checkpoint profile            confirm in game

[ START CAREER ]
```

*Illustrative only.* The trainee, the deck figures, the coverage checks, the route count and the
readiness ticks are a worked example. The two sourced things in the block are the three Grade Point
profiles — 60 / 300 / 300 for the standard track, with the dirt-leaning and limited-turf-range
profiles also in the config but not selectable (see §15 item 1) — and the source tags in the rule
column, which say plainly that the Grade Point, coin and shop tables are community-sourced and the
checkpoint profile is to be confirmed in game. The year-end bond figures are the uma.guide set; the
config also carries a second, disputed set, as §13 records.

---

## 8. The Career Cockpit

```
┌─────────────────────────────────────────────────────────────────────┐
│ TRACKBLAZER                          CLASSIC • TURN 31 • Energy 54  │
├─────────────────────────────────────────────────────────────────────┤
│ GRADE POINT RUNWAY            period needs 300                      │
│ ███████████░░░░░░░░░  172 / 300    128 short • 9 turns left        │
│ Races that fit: 2 G2 wins + 1 G3 win ≈ 220                          │
├─────────────────────────────────────────────────────────────────────┤
│ COINS  210       SHOP refresh in 3 turns      ITEMS 7 (cap 5/item) │
│ RACE FATIGUE  2 consecutive races  → next race: see table          │
├─────────────────────────────────────────────────────────────────────┤
│ ★ RECOMMENDED: RACE (G2, mile)                                      │
│                                                                     │
│ 1st  +80 GP  +100 coins   2nd  +48 GP  +60 coins   3rd  +32 GP  +60 │
│                                                                     │
│ WHY                                                                 │
│ You are 128 GP short with 9 turns left. A G2 win is worth 80.       │
│ Training Speed here gains less than the checkpoint risk costs.      │
│ ⚠ A third consecutive race raises fatigue risk (60% Mood Down).     │
│                                                                     │
│ [ RACE ]                                                            │
│                                                                     │
│ Alternatives                                                        │
│ ○ Train Wit: safer, +energy, does not move the runway               │
│ ○ Rest: clears the race chain, loses a turn of GP                   │
│ ○ Use Good-Luck Charm, then train Power: 0% failure for 1 turn      │
└─────────────────────────────────────────────────────────────────────┘
```

Every number in that wireframe is illustrative. The tie to sources:

- 80 GP and 100 coins are the G2 first-place values. Source: the "Grade Points (Replacing Career Goals)" and "Shop Coins" sections of the uma.guide Trackblazer section in `docs/research-scratch/SCENARIO-PUBLISHER-REFERENCES.md`.
- 48 GP is 80 × 60%. Source: the "Placement modifier" table in the same section (80 × 0.60).
- The fatigue figure comes from the table in section 10.

Two design points:

- The Race Desk should show the same breakdown for any race the Trainer picks. I would not display an exact placement probability, and I would show the 1st, 2nd and 3rd branches instead.
- After a period's threshold is met, further Grade Points do not help the checkpoint. The Advisor should say so ("checkpoint met; this race is now worth coins, stats and epithet progress only"). Races still pay coins, stats and fans after that.

---

## 9. Advisor Priority Hierarchy

1. Don't end the run. This is Grade Point feasibility at the next checkpoint, plus the debut race (which costs energy here).
2. Don't wreck the run. This covers energy floors and race fatigue.
3. Route integrity. An epithet needs every race in its route won, so a skipped race can break a route.
4. Economy. This is the coin bank, the shop rotation, the Summer stockpile and the finale reserve.
5. Training target.
6. Optimization.

The explanation shape stays the same: recommendation, reason, alternatives. For example, "Speed gives the most raw stats, but a race is better now because you are 128 GP short."

---

## 10. Race Fatigue Panel

Consecutive races matter more here than in any other scenario. uma.guide publishes the odds of the fatigue event by consecutive-race count. Show the row for the current count:

```
RACE FATIGUE          chain so far: 2

                       1 race   2 races   3 races   4+ races
Mood Down (1+ energy)    0%       0%        60%       100%
Mood Down (0 energy)    15%      33%       90%+      100%
Skin Outbreak (1+)       0%       0%        15%        33%
Skin Outbreak (0)        4%       8%        25%        33%
3 random stats -10 (any)  0%       0%         0%        40%

Cannot occur after late December.
Guaranteed recovery events: Classic early Feb (+1 mood), early Mar (+20
energy), late Sep (+1 mood); Senior late Jun (+20 energy), late Oct (+1
mood), late Dec (+30 energy).
```

Source: the "Gameplay Flow & Race Fatigue" section of the uma.guide Trackblazer section in `docs/research-scratch/SCENARIO-PUBLISHER-REFERENCES.md`, which prints the consecutive-race table and the guaranteed recovery events.

**Dated note (2026-10-21).** The line range this section cited is replaced by the section name; the table's figures and its two caveats are re-verified and unchanged.

Items mitigate this, so the Advisor should factor in what the Trainer is holding. The guide says to race without fear if you hold the right items. Two caveats:

1. The guide does not say whether "3 races" counts the upcoming race or the ones already run. Show the exact row being read, and flag the convention as unverified.
2. "After late December" does not say which year.

---

## 11. Shop Drawer (In the Cockpit, Not a Separate Page)

The shop is used almost every turn, so it should be a drawer in the cockpit. Display the rules the game imposes:

- The shop is locked until the Debut race.
- It refreshes every 6 turns.
- A maximum of 5 copies per item.
- Race-triggered items last 3 turns. **Badge: `unknown`** — no source in the tree states a duration for race-triggered items, and the publisher master's "Special Shop Mechanics" section and its "Full Shop Item List" section print durations only for the items that have one. The claim is kept because it is in the plan, and it is marked rather than deleted.
- Sales are 10% to 20%.
- Multi-turn items cannot be used while still active.
- A lower-grade item used before a higher one is wasted, and the reverse is not possible.

**Badge: `verified`** for the first, second and third rules and for the sales and overwrite rules: the `trackblazer` entry's `shop` key in `config/scenarios.php` holds `['rotation_turns' => 6, 'max_copies_per_item' => 5, 'locked_until_debut' => true]`, and the publisher master's "Special Shop Mechanics" section prints the same five rules in prose, including the sales range and the overwrite rule.

```
SHOP                                coins 210   refresh in 3 turns

★ ADVISOR      Keep 100+ coins: Summer starts in 6 turns and the shop resets then

Master Cleat Hammer    40   +35% race bonus, 1 turn     held 1/5
Empowering Megaphone   70   +60% training, 2 turns      held 2/5
Reset Whistle          20   shuffle support cards       held 0/5
Good-Luck Charm        40   0% failure, 1 turn          held 1/5
Vita 65                75   +65 energy                  held 0/5

Community tags (uma.guide, opinion): Must Buy / Skip
```

*Illustrative only.* The coin balance, the refresh countdown, the held counts and the advisor line are a worked example. The five item rows are the shop's real items at their real prices and durations, transcribed from the publisher master's "Full Shop Item List" section into the `trackblazer.shop_items` catalogue in `config/scenarios.php` — the Cleat Hammer, the Megaphones, the Whistle, the Charm and the Vita all appear there. The advisor line's coin floor is a guide recommendation, not a rule.

Keep community opinions visibly separate from game rules. Items you will want to track:

- Training Applications (150 coins) permanently raise a facility level for the run. Source: the "Training Facilities" table under the publisher master's "Full Shop Item List" section.
- Grilled Carrots (40) give +5 bond to all supports. Source: the "Bond" table under the same section.
- Yummy Cat Food (10) gives +5 Akikawa bond. Source: the "Bond" table under the same section. **Dated note (2026-10-21).** The attribution "uma.guide says to skip it" is **not supported** and is corrected. The publisher master contains no skip advice for Yummy Cat Food; the item appears once, in the "Bond" table. The "skip" advice that *is* on record in the master concerns the **Fast Learner** status item — the Scholar's Hat — in the "Shop Item Priorities (uma.guide's 'Worth' Ratings)" section and again in the "Best Strategy Summary (uma.guide)" section ("Treat the Fast Learner shop item with suspicion — it's a coin sink that usually loses to just buying more Hammers across the run"). What survives unchanged is the plan's own reasoning: §13 gives a case where this item matters, and that case is the plan's derivation, not the guide's advice.
- Scholar's Hat (280 coins) — "the equivalent of 3 first-place wins" — grants the Fast Learner discount on skill hints. Source: the "Get Good Conditions" table under the publisher master's "Full Shop Item List" section for the cost and the effect, and the Game8 Trackblazer "Numeric effects as printed" table for the "equivalent of 3 first-place wins" phrase.
- Stat books at +7 or +15, avoid the +3 ones — recommended purchase tiers. Source: the "Stats" table under the publisher master's "Full Shop Item List" section, which prints them under the client's own names: Notepad (10, +3), Manual (15, +7), Scroll (30, +15). The names in the shop catalogue in `config/scenarios.php` follow the same three.
- Royal Kale Juice restores 100 Energy at the cost of 1 Mood; the mood debuff is fixed with a Cupcake. Source: the "Energy and Motivation" table under the publisher master's "Full Shop Item List" section, and the Game8 Trackblazer "Numeric effects as printed" table.
- Cleat Hammers do not stack: Master Cleat Hammer used after Artisan Cleat Hammer gives only the 35% from the Master, not a combined 55%. Source: the Game8 Trackblazer "Numeric effects as printed" table, with the two items' own prices and effects in the "Races" table under the publisher master's "Full Shop Item List" section.
- Empowering Megaphone (60% training boost, 2 turns) overwrites an earlier Coaching Megaphone. Source: the "Special Shop Mechanics" section for the overwrite rule, and the "Training Effects" table under the publisher master's "Full Shop Item List" section for the two items.

Reserve rules from uma.guide, shown as guidance — **badge `community`**, in every case:

- Keep at least 100 coins entering Summer. Source: the uma.guide Trackblazer section's "Classic/Senior Year" subsection, which tips keeping at least 100 coins banked into Summer because the shop resets on Summer's first turn.
- Stock two Megaphones for the four Summer turns. Source: the "Summer Strategy" subsection of the same section.
- Save about 150 coins for the final shop. Source: the "Twinkle Star Climax (The Finale)" section of the uma.guide Trackblazer section.
- Save three Gold Hammers for the finale. Source: the same "Twinkle Star Climax (The Finale)" section, and the Game8 Trackblazer "Numeric effects as printed" table.

Full item list source: the "Full Shop Item List (Exact Costs & Effects)" section of the GameTora Trackblazer section in `docs/research-scratch/SCENARIO-PUBLISHER-REFERENCES.md`; Summer Camp window: the Game8 Trackblazer "Numeric effects as printed" table, which prints it tagged `[Both]`; cleat-hammer stacking and the Empowering Megaphone overwrite: the Game8 "Numeric effects as printed" table and the "Special Shop Mechanics" section respectively.

**Dated note (2026-10-21).** Every citation in this section was a bare line number into a file that grows; they are replaced by the section and table names that carry the same content. The figures are re-verified and unchanged, with one exception recorded above: the "uma.guide says to skip it" attribution for Yummy Cat Food, which no source supports and which is corrected rather than carried forward.

---

## 12. Epithet Tracker

Epithets pay random stats, and some give hints. Examples from uma.guide:

- Lady: win the Oka Sho, Japanese Oaks and Shuka Sho, for 2 random stats +10.
- Stunning: the Satsuki Sho, Japanese Derby and Kikuka Sho line, for +10.
- Goddess: Lady plus three more races, for +15.
- The dirt epithets: win 5, 10 or 15 dirt races, and 3, 4, 5 or 9 dirt G1s.

Source: the "Epithets (Race Route Bonuses)" section of the uma.guide Trackblazer section in `docs/research-scratch/SCENARIO-PUBLISHER-REFERENCES.md`, which prints the route tables the four bullets above are drawn from.

**Badge: `community`.** The route names, the win counts and the stat values are a third-party guide's, and the guide's own framing is that an epithet "pays random stats" — the *which stat* is not stated, and nothing here says it is.

Show each route as a chain: won, remaining, and whether it is still alive. If a race in a route is lost, grey the whole route out and say so.

**Summer Camp.** Early July to Late August, in both Classic and Senior Years, training facilities reach temporary Level 5. This is the highest-value training window for the scenario, and cleat hammers / megaphones reserved for it pay the most. Source: the Game8 Trackblazer "Numeric effects as printed" table in `docs/research-scratch/SCENARIO-PUBLISHER-REFERENCES.md`, which prints the window tagged `[Both]`. **Badge: `community`**, with the tag itself recorded: `[Both]` means both servers publish it, not that a client screen was measured.

---

## 13. Year-End Gate (Unique Skill Level-Up)

The gate is separate from Grade Points. In late December of each year the game picks the "Umamusume of the Year". uma.guide gives numeric thresholds:

- Junior: 5,000 fans and Akikawa bond 19.
- Classic: 60,000 fans and bond 31.
- Senior: 120,000 fans and bond 51.

GameTora describes the same gate as bar colors (blue 1 bar, blue 2 bars, green 3 bars) and says the fan and win requirements are unclear. The two are consistent if one bar is about 20 points. Treat the uma.guide numbers as single-source.

**Dated note (2026-10-21).** "Treat the uma.guide numbers as single-source" is **no longer accurate**, and the correction is recorded here rather than the sentence being left to stand. The repository now holds a **second, conflicting set** of bond figures: the `trackblazer` entry's `year_end_gates` in `config/scenarios.php` carries `bond_uma_guide` and `bond_game_with` side by side — 19 / 31 / 51 against 15 / 30 / 50 — and the entry's own comment says the Director's bond "does not agree with itself" between the two sources and that "both pairs are therefore printed and neither is settled here, the way KI-15 keeps two published tracks unchosen rather than picking a denominator". The fan figures (5,000 / 60,000 / 120,000) are not in dispute. The recommendation below is therefore `derived` from a **disputed** threshold, and the surface must print both bond readings rather than one.

```
YEAR-END GATE  Classic, late December
Fans         41,200 / 60,000     ⚠ 18,800 short
Akikawa bond     24 / 31         ⚠ 7 short
```

*Illustrative only.* The readings and the shortfalls are a worked example. The thresholds are the
"Unique Skill Level-Up Requirements" section of the uma.guide Trackblazer section in
`docs/research-scratch/SCENARIO-PUBLISHER-REFERENCES.md` for the fans and the uma.guide bond
figures, with the GameWith bond figures recorded alongside them in config as above.

Source: the "Unique Skill Level-Up Requirements" section of the uma.guide Trackblazer section in `docs/research-scratch/SCENARIO-PUBLISHER-REFERENCES.md`.

If bond is the shortfall, the Advisor can note that Yummy Cat Food costs 10 coins for +5. That is a derivation from the item list — the "Bond" table under the publisher master's "Full Shop Item List" section. **Dated note (2026-10-21).** The earlier attribution "uma.guide itself says to skip the item" is not supported by the publisher master and is corrected here for the same reason as in §11: the master contains no skip advice for Yummy Cat Food, and the "skip" advice it does carry concerns the Fast Learner status item. Label the recommendation as `derived`, not sourced, and note that the threshold it is derived against is itself disputed.

---

## 14. Twinkle Star Climax Briefing

The finale is three races and three training turns. It is a mini-league, not an elimination bracket. Victory Points by placement:

- 1st 10, 2nd 8, 3rd 6, 4th 4, 5th-6th 3, 7th-9th 2, 10th-13th 1, 14th and lower 0.
- The maximum is 30, and the top total wins.

```
TWINKLE STAR CLIMAX PREFLIGHT

FINAL STATS      SPD 1180  STA 1620  POW 1090  GUT 880  WIT 1210
VICTORY POINTS   0 / 30 possible
TRAINING TURNS   3  (hold a few training items)
HAMMERS HELD     Gold 3   ✓ recommended: 3
COINS            150      ✓ the finale races pay no coins
```

*Illustrative only.* The final stat line, the points standing and the two inventory counts are a
worked example. What is sourced in the block is the **shape** and three of its rules: the finale is
three training turns and three race turns, the maximum is 30 Victory Points and the top total wins,
the finale races pay no Shop Coins, and the guide's reserve advice is to save three gold cleat
hammers and roughly 150 coins for them. The per-race placement values that produce the maximum are
in §14's list above.

Per uma.guide, each finale race gives a base +10 to all stats, which is why the hammers matter. Winning also gives a hint for the Radiant Star skill. GameTora says the finale's distance and terrain depend on the races you ran across the three years, but does not give the rule, so it would not be modeled.

Source: the "Twinkle Star Climax (The Finale)" section of both the uma.guide and the GameTora Trackblazer sections in `docs/research-scratch/SCENARIO-PUBLISHER-REFERENCES.md`.

**Dated note (2026-10-21).** The two line ranges this section cited are replaced by the two section names that carry the same content — the uma.guide's finale section and the GameTora's. The placement values, the maximum of 30, the "+10 to all stats" and the Radiant Star hint are re-verified and unchanged.

---

## 15. What I Could Not Confirm

Every item below is preserved. Each carries a status and the date it was last reviewed: `OPEN` (no
source and no code settles it), `PARTIALLY RESOLVED` (one half is settled and the other is not),
`RESOLVED` (a source or the repository settles it), `SUPERSEDED` (a later source replaced it).

**Reviewed 2026-10-21.**

1. **OPEN — reviewed 2026-10-21. Short-distance turf checkpoints.** GameTora (page stamped 2026-03-12) says Curren Chan's profile is 60 / 200 / 300, with only the third objective reduced. uma.guide (2026-04-29) says sprinters with weak other aptitudes follow the dirt profile, 30 / 200 / 300. `config/scenarios.php` carries all three tracks (`standard` / `dirt_leaning` / `limited_turf_range`); `TrainingRun::gradeObjectives()` renders only `standard` and says so, because a wrong denominator is worse than a conservative one. Keep the profile editable and confirm in game. KI-15, OPEN.

   **Dated note (2026-10-21).** The item is substantively open and the repository still behaves as the item says: the `trackblazer` entry carries all three profiles, `TrainingRun::gradeObjectives()` selects only `standard` and its docblock names the reason ("a wrong denominator is worse than a conservative one", D-256, KI-15). The **register id is the one thing that has moved**: the current `KNOWN-ISSUES.md` is a later generation whose headings run from KI-57 to KI-111, and it carries **no KI-15 entry**. The defect is still real and still disclosed in the product — `SCREEN_SPEC.md` records that the ladder shows only the standard track "because dirt-leaning and limited-turf-range tracks have no aptitude input that places a trainee on them (KI-15)", and the scenario-panel disclosure names the same id — but the id now points at a master that no longer holds it. The id is kept because the product's own copy uses it, and this note records where it lives.

2. **RESOLVED — reviewed 2026-10-21. Stat caps on Global.** The GameTora data export `scenarios.json` (fetched 2026-09-27, after the 2026-07-01 update) carries Trackblazer `stats: [0, 700, 0, 0, 300]` over the 1200 base, confirming 1200 / 1900 / 1200 / 1200 / 1500 on Global. The config `cap_bonus` matches exactly, with `live_on_global` set to `2026-03-12`.

   **Dated note (2026-10-21).** Re-verified against the tree, and the resolution holds in two places rather than one: the `trackblazer` entry's `cap_bonus` in `config/scenarios.php` is `['Speed' => 0, 'Stamina' => 700, 'Power' => 0, 'Guts' => 0, 'Wit' => 300]` and its `live_on_global` is `2026-03-12`, exactly as this item states. §2's cap line and this item therefore agree, as they must.

3. **OPEN — reviewed 2026-10-21. Names.** GameTora calls it the "Special Shop", and uma.guide calls it the "Coin Shop" or "Climax Store". Do not hard-code a display name until it is read from the client. The publisher master uses the client's own heading, "Special Shop Mechanics" and "Full Shop Item List", and the repository's config names the key `shop` and the catalogue `shop_items` — so the *internal* names are settled, and only the **display** name is open. No capture of the shop's own heading is in the tree.

4. **RESOLVED — reviewed 2026-10-21. Placement modifiers.** The 100/60/40/20/10% curve is confirmed `[Both]` — uma.guide prints it as a labeled table and GameTora records the same modifiers in prose ("proportionally lower"). Source: the "Placement modifier" table in the "Grade Points (Replacing Career Goals)" section of the uma.guide Trackblazer section in `docs/research-scratch/SCENARIO-PUBLISHER-REFERENCES.md`. **Dated note (2026-10-21).** The line range this item cited is replaced by the table and section name; the curve itself is re-verified and unchanged.

5. **OPEN — reviewed 2026-10-21. Race stat table.** uma.guide's per-grade table gives base figures with Race Bonus and hammer scaling. The page excerpt does not say how the stat figure is split across stats, so treat the unit as unclear until checked. No post-update source read supplies the split, and the repository stores the Race Bonus and the hammer as a multiplier on a per-grade figure rather than a per-stat one, so the tool has nothing to split either.

6. **PARTIALLY RESOLVED — reviewed 2026-10-21. Alarm Clock retries.** The Game8 Trackblazer "Numeric effects as printed" table in `docs/research-scratch/SCENARIO-PUBLISHER-REFERENCES.md` confirms the per-career cap is **5 Alarm Clocks** ("the item that re-runs a race you failed to win, up to 5 per career"), not three per run as the wiki stated. The resume point remains unconfirmed: the wording is *that race*, not a period start (`docs/UMAMUSUME_REFERENCE.md` §2.2.3). Which turn the retry resumes on is unstated by any source. **Dated note (2026-10-21).** The line number this item cited is replaced by the section and table name; the 5-per-career cap and the per-career wording are re-verified and unchanged.

7. **OPEN — reviewed 2026-10-21. Finale selection rule, and which year "after late December" means in the fatigue rule.** Two questions, neither settled. The fatigue section of the publisher master prints "Race Fatigue Events cannot occur after Late December" without a year, and the finale section states that the finale's distance and terrain depend on the races run across the three years without giving the rule, as this plan already records in §14.

What I would not build:

- A fixed "best route" that plays itself. Trainer decides, assistant advises.
- A placement-probability number. The game does not publish one.
- A hard-coded shop tier list presented as a game rule.

---

## MVP Screen Set

1. Trainer Home
2. Trainee & Target
3. Legacy Lab
4. Support Deck Builder
5. Route Planner
6. Run Preflight
7. Career Cockpit (with the Race Desk and Shop drawer)
8. Checkpoint & Finale Briefing
9. Career Report / Veteran

#7 should again get most of the design effort. The route planner is the screen that has no equivalent in Grand Concert.

---

## 16. DECISION CONTRACT

Every recommendation the Advisor produces follows this shape — it must not be a bare action label:

1. **Current state and immediate objective.**
2. **Recommended action** and expected benefit (with numbers).
3. **Best alternative** and its trade-off.
4. **Confidence** — verified, derived, configurable, or unknown — with the source badge.
5. **Conditions that would change the recommendation.**

Example:

> **★ RECOMMENDED: RACE (G2, Mile)**
>
> You need 128 GP this period with 9 turns left. A G2 win is worth 80; the floor (2nd–3rd) is 48.
>
> - **Race G2 Mile:** +80 GP (1st), +100 coins, advances route progress.
> - **Alternative — train Speed:** +stats but 0 GP; checkpoint risk remains.
> - **Confidence: verified** (GP values, the "Grade Points (Replacing Career Goals)" section of the uma.guide Trackblazer section in `docs/research-scratch/SCENARIO-PUBLISHER-REFERENCES.md`).
> - **Switch if:** fatigue chain hits 3 (60% Mood Down), or a Summer Camp turn opens (bank coins first).

> **Dated note (2026-10-21).** The badge above is re-verified and is the correct badge for this
> document: the claim it names is the Grade Point value, the section it names holds that table, and
> the figure is the one the section prints. Two things are recorded here because the shared
> decision contract spans three documents and a reader may compare them.
>
> - **This example does not carry a facility-level rule, and it should not.** It was checked
>   specifically for a copied "facility level by repetition, config `facility_level_source =
>   'repetition'`" badge; that text is not in this document's example. Nothing here needed
>   downgrading for it.
> - **If a facility-level rule were added to this example, the citation would be different from
>   URA's.** The `trackblazer` entry's `facility_level_source` in `config/scenarios.php` is
>   `repetition_plus_purchases`, not `repetition`: facilities level up with use *and* with the
>   Training Application shop item. The Game8 Trackblazer section's "Training-relevant decision
>   the scenario forces" states the repetition half in the client's own words — "Leveling up
>   facilities in Trackblazer works just like the URA Finale, where they increase in level the more
>   you use them" — and the same publisher's "Full Shop Item List" section prices the Training
>   Application that buys the other half. A badge citing `facility_level_source = 'repetition'`
>   here would name a value the config does not hold.
> - The canonical shape of the contract, and the badge vocabulary, are set by URA Finale's §12.

---

## 17. RACE CALENDAR PANEL

Trackblazer is race-driven, so the race calendar deserves a dedicated panel in the cockpit — not just the route planner's period view.

```
RACE CALENDAR                Classic • Turn 31

UPCOMING RACES                             DISTANCE / SURFACE
• G2 Mile (Satsuki Sho qualifier)           Mile / Turf
  → 4 turns                                Win: +80 GP, +100 coins
• G3 Sprint (fill-in)                       Sprint / Turf
  → 2 turns                                Win: +60 GP, +80 coins
• G1 Long (Derby lead-up)                   Long / Turf
  → 18 turns                               Win: +100 GP, +150 coins

CONSEQUENCES OF SKIPPING
• G2 Mile: +1 route gap → Spring Champion route at risk
• G3 Sprint: no impact on epithet progress
• G1 Long: -100 GP from runway, -150 coins from economy

ADVISOR: Race the G2 Mile now. You are 128 GP short with 9 turns left.
```

Source values: GP per grade from §6 (G1 100, G2 80, G3 60), placement modifiers from §6.

*Illustrative only.* The three upcoming races, their distances and surfaces, the win values and the
skip consequences are a worked example. The win values are the §6 ones — G2 80 GP and 100 coins, G3
60 GP and 80 coins, G1 100 GP and 150 coins — and the advisor line's 128 GP shortfall and 9 turns
are the cockpit figures from §8's wireframe, not a recorded run.

---

## 18. SCHEDULE RISK WARNINGS

The cockpit should surface five risk types as flags, each with a severity level:

| Risk                     | Trigger                              | Severity | Mitigation                                  |
| ------------------------ | ------------------------------------ | -------- | ------------------------------------------- |
| Race density             | 3+ consecutive races without rest    | High     | Insert rest turn or use recovery item       |
| Fitness deficit          | 2+ consecutive races at 1 energy      | Medium   | Hold Vita 65 or Good-Luck Charm             |
| GP shortfall             | Floor projection < checkpoint target  | High     | Race higher-grade or use Master Cleat       |
| Coin shortfall           | < 100 entering Summer                 | Medium   | Race an extra G2 before Summer              |
| Epithet decay            | Lost race in an active route          | High     | Grey the route; abandon or replan           |

Source: fatigue table §10, coin reserves §11, GP values §6, epithet rules §12, checkpoint tables — the "Grade Points (Replacing Career Goals)" section of the uma.guide Trackblazer section in `docs/research-scratch/SCENARIO-PUBLISHER-REFERENCES.md`.

**Dated note (2026-10-21).** The source pointer this line ended with was `SCENARIO-PUBLISHER-REFERENCES.md §7`, which resolves to nothing in that file — it has no §7, and the section it was reaching for is the Grade Point table, not a numbered section. The section name replaces it.

---

## 19. RESOURCE LEDGER

A persistent ledger tracks coins, items, and expected income/expense across the remaining career.

```
RESOURCE LEDGER                    Classic • Turn 31

COINS
  Current:          210
  Projected inflow: +180 (2 races)
  Projected cost:   -120 (2 Megaphones + Hammer)
  Summer reserve:   100 (target)

ITEMS
  Good-Luck Charm   1/5   ✓ hold for fatigue risk
  Master Cleat       1/5   ⚠ use before G1 Long (next race)
  Empowering Meg.    2/5   ✓ Summer stock: 2 held, need 2 more

ADVISOR: Hold 100+ coins: Summer starts in 6 turns and the shop resets then.
```

Rules: shop locked until Debut (§11), refresh every 6 turns (§11), 5 copies per item max (§11), Summer Camp window (§12).

*Illustrative only.* The coin balance, the projected inflow and outflow, the Summer reserve target
and the three item counts are a worked example; the held counts are per-item against the matrix's
5-copy cap. The four rules in the line above are the sourced ones, and the reserve target is uma.guide's
advice rather than a rule.

---

## 20. ACCEPTANCE CRITERIA

| Feature                    | Success Criterion                                                                 | Edge Case                              |
| -------------------------- | --------------------------------------------------------------------------------- | ---------------------------------------- |
| Race Calendar panel        | Shows next 3 races, distances, GP/coin values, and skip consequences              | No eligible races in upcoming period    |
| Schedule risk warnings     | Flags fatigue chains, GP shortfalls, and coin shortfalls before they bite         | All risks resolved (empty flag set)     |
| Resource ledger            | Tracks coins + 5 key items, forecasts to Summer and Finale                       | Item held at 5/5 cap                    |
| Checkpoint projector       | Shows needed, planned (wins), and floor (2nd–3rd) for each GP period             | Current period already failed           |
| Route tracker              | Greys out routes when a race is lost, tracks remaining per route                  | Rival races random (cannot schedule)    |
| Recommendation reconciliation | Predicted outcome vs actual entry updates run state and flags deviation        | User edits state manually              |

---

## 21. VERIFICATION STATUS

Each feature carries one of three levels — never collapsed:

- **Implemented** — the code exists and the behavior is connected.
- **Verified** — automated tests or reproducible manual checks demonstrate the intended behavior.
- **Game-validated** — calculations have been compared against authoritative mechanics or observed in-game.

A page that renders is not verified. A verified calculation can still rest on an incorrect understanding of the game. This distinction is tracked per feature.

Two further values are used where the three above would mislead, and they are not a fifth level of confidence:

- **UNASSESSED — requires code audit** — nobody looked. It is not a synonym for `Implemented`.
- **Not implemented** — a code audit was done and found no counterpart for the feature in the tree. It is recorded so that an absent feature is never read as an unaudited one.

### Per-feature status

Audit date: **2026-10-21**, a read of the code and the test files on `master` at `d00a0457`. The audit establishes that a surface exists and that a test pins it. It compares nothing against an observed client screen, so no row is `Game-validated`.

| Feature | Level | Evidence | What this audit did not confirm |
| ------- | ----- | -------- | ------------------------------ |
| Race Calendar panel | `Verified` | `resources/js/components/scenario/TrackblazerRaceCalendar.vue`, mounted by `TrackblazerPanel.vue`; `tests/browser/trackblazer-panel.spec.ts` asserts that "races ahead prints the window with each row its published win value" and that the year-end gate "appears once a period is reported". | The §20 criterion "shows next 3 races, distances, GP/coin values, and skip consequences" is confirmed for the window and the win values. The **skip-consequences** half was not audited in this pass. |
| Schedule risk warnings | `Verified` | `resources/js/components/scenario/TrackblazerFatiguePanel.vue`; `tests/browser/trackblazer-panel.spec.ts` asserts that "race fatigue states that no chain is entered and keeps both caveats on the page", and `app/Services/Career/TrackblazerPanelBuilder.php` builds the fatigue rows from the matrix. | §18 lists **five** risk types with severities. The **fatigue** one is confirmed. The other four — race density, fitness deficit, GP shortfall, coin shortfall, epithet decay — each with a severity level, were not audited in this pass. |
| Resource ledger | `Verified` | `resources/js/components/scenario/TrackblazerResourceLedger.vue`; `tests/browser/trackblazer-panel.spec.ts` asserts that "the ledger names what this run holds, against the cap, with the publisher rating beside it" and that "the ledger prints the reserve as a tip and leaves the inflow absent until a race is entered"; the `trackblazer` entry's `shop` key in `config/scenarios.php` carries the 6-turn rotation, the 5-copy cap and the debut lock. | The §20 criterion "forecasts to Summer and Finale" is confirmed for the reserve tip. A **projected inflow figure** was not audited in this pass; the spec pins its absence before a race is entered. |
| Checkpoint projector | `Verified` | `app/Services/Career/TrackblazerPanelBuilder.php::gradeSection()` and `TrackblazerRoutePlanner.vue`; `tests/browser/trackblazer-panel.spec.ts` asserts that "the race plan prints three periods, their published figures and the gap the plan has not filled"; `tests/Feature/TrackblazerPanelTest.php` asserts that the panel "prints the grade-points period without an invented total". | The §20 criterion "Shows needed, planned (wins), and floor (2nd–3rd) for each GP period" is confirmed for the three periods and their published figures. Whether all three figures (needed, planned, floor) render for every period was not audited in this pass. |
| Route tracker | `Verified` | `TrackblazerPanelBuilder::epithetSection()` and the epithet checklist in `TrackblazerPanel.vue`; `tests/Feature/TrackblazerPanelTest.php` asserts that the panel "lists epithets from config with the run-recorded titles showing the open rows", and `tests/browser/trackblazer-panel.spec.ts` asserts the checklist renders "with three states, glyph plus word". | The §20 criterion "Greys out routes when a race is lost" is confirmed at the state level — the three states are `earned`, `open` and `unverifiable`, and the third is a named absence rather than a loss. The **lost-race greying** half was not audited in this pass. |
| Recommendation reconciliation | `Verified` | `app/Services/Advisor/NextDecision.php` carries the `reconciliation` slot — the action the Trainer took against the action the rail recommended — and `tests/Feature/TurnChoiceTest.php` asserts the null, the match and both notes; `tests/browser/career-cockpit.spec.ts` covers it. | The §20 criterion "Predicted outcome vs actual entry updates run state and flags deviation" is confirmed at the data level. A scenario-specific Trackblazer reconciliation was not audited in this pass. |

**No feature in this document is `Game-validated`.** Nothing here was compared against an observed client screen or an authoritative capture. The mechanics behind the scenario come from uma.guide, GameTora and Game8, which are `verified` as *citations* and `community` as *game facts*.

**Dated note (2026-10-21).** This table did not exist before this pass: the section defined three levels and assigned none. Every level above was assigned from a named file read on 2026-10-21, and the "not confirmed" column records the gaps rather than smoothing them.

---

## 22. IMPLEMENTATION AND TESTING PLAN

| Phase                      | Deliverable                                                                     | Completion evidence                                              |
| -------------------------- | ------------------------------------------------------------------------------- | ---------------------------------------------------------------- |
| 0. Reconnaissance          | Inspect existing scenario code, plans, contracts, routes, data models and tests | Inventory of existing, partial, missing and conflicting behavior |
| 1. Rules audit             | Document verified scenario mechanics and unresolved assumptions                 | Source-backed rules and explicit unknowns                        |
| 2. State model             | Define run state, transitions, persistence and validation                       | Tests for valid and invalid state changes                        |
| 3. Decision engine         | Implement scenario-specific candidate evaluation and ranking                    | Deterministic tests using known inputs and expected outputs      |
| 4. Command center          | Build the primary screen around the next actionable decision                    | UI tests for loading, empty, error, stale and normal states      |
| 5. Reconciliation          | Compare predicted versus actual outcomes                                        | Tests for updating run state without losing history              |
| 6. End-to-end verification | Exercise complete careers and edge cases                                        | Reproducible scenarios, regression results and completion report |

**Trackblazer focus:** phase 3 builds the Grade Point rules engine (GP per grade, placement modifiers, coin payouts, fatigue odds). Phase 4 builds the Race Desk and Shop drawer. Phase 5 adds reconciliation for race outcomes (predicted GP vs. actual placement). Phase 6 validates checkpoint feasibility against in-game captures.

---

## 23. PRIORITY MATRIX

| Priority | Scope                    | Rationale                                                  |
| -------- | ------------------------ | ---------------------------------------------------------- |
| P0       | Canonical run state, GP rules, regression tests | Protects the trustworthiness of the race-driven scenario     |
| P1       | Next-action recommendations, deadline tracking, risk warnings | Turns Cockpit from display into a command center          |
| P2       | Resource forecasting, route optimization, matchup analysis | Materially affects coin and GP planning                   |
| P3       | Keyboard-friendly controls, richer historical analytics | Convenience only                                           |

---

## Note on Grand Concert Write-Up

The Grand Concert scenario's terminology is settled in `docs/proposals/Our-Grand-Concert-plan.md`, under
"Client terminology (settled)" and the naming note at the head of its Phase D: the authoritative
`[Global]` terms are **Dance, Passion, Vocals, Visuals** and **Composure** — never "Mental", which
is GameTora's rendering of the JP word — with **Promo Concert** and **Grand Concert** as the client
nouns for the events and **Live Performance Expectations** for the gauge GameTora calls "Hype".

**Dated note (2026-10-21).** The note this replaces said the Grand Concert write-up "uses GameTora's
wording (Hype, Mental, Vocal and Visual)" and that the change "is worth a pass before it goes into a
spec". That was accurate when it was written and is now stale: the pass happened, the terminology is
settled, and the document it pointed at is `docs/proposals/Our-Grand-Concert-plan.md` — the path
this note gives, and the same document this file's §1 cross-reference now uses. The note is replaced
rather than annotated so that no reader is left with a description of a state that no longer exists.
The one word of the old note that still stands is the point of this one: "Hype" is a guide rendering,
and the gauge's client name is Live Performance Expectations.
