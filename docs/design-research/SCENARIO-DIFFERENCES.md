# SCENARIO-DIFFERENCES

Comparative matrix of the three Global career scenarios, for scenario-aware UI design.
Sources: `docs/scenarios/01-ura-finale.md`, `02-unity-cup.md`, `03-trackblazer.md` (owner-supplied, retrieved with Claude), cross-checked against `docs/UMAMUSUME_REFERENCE.md` and `docs/design-research/RAW-FINDINGS.md`.
Compiled 2026-09-27, revised the same day after Global-side verification against Game8's English scenario guides, GameTora's Trackblazer and July-2026-rebalance pages, and `umamusu.wiki`.

**Warning for future readers of the owner-supplied files.** Two of the three were retrieved before the 2026-07-01 Global rework and are stale on status and numbers while remaining sound on mechanism. `02-unity-cup.md` files real, live mechanics under "Not Yet on Global"; `03-trackblazer.md` still describes a scenario that shipped six months ago. Read the resolutions below rather than trusting either file's figures.

## The headline

**The three scenarios do not share a resource model, a progression model, or a goal model.** A design that adds a scenario name to the header and scales a bar is not scenario-aware; it is scenario-decorated. The differences below change which panels exist at all.

| Axis | Ura Finale | Unity Cup | Trackblazer |
|---|---|---|---|
| Core loop | Train solo, scripted events | Team building | Player-directed racing |
| Scenario currency / extra system | **None** | Team Rank, Spirit Bursts, Result Pts | Grade Points, Shop Coins |
| Goal structure | Fixed mandatory race goals | Fixed race goals **plus** 5 Team Race rounds | **No race goals.** Grade Point deadlines |
| Facility level from | Repeating one stat 4x | **Team's** aggregate stat rank for that stat | Repeat one stat 4x **+ levels bought from the shop** |
| Fan role | Gates 3 Unique Skill upgrade events | Secondary; racing is actively discouraged | Primary farming vector |
| Shop | No | No | Yes, in-run, coins from placement |
| Scenario Link character | Aoi Kiryuin | 5 linked characters, chosen via Team Name | **None** |
| Finale | URA elimination races | 4 Team Races then Team Zenith, then URA-style finals | **Twinkle Star Climax: 3-race points league** |
| Secret character events | Yes | Yes | **Do not trigger** |

**This matrix covers three scenarios; the client offers four.** *Brighter Together: Our Grand Concert* has been live on Global since **2026-07-22** with a Speed cap of 1600 and five scenario-linked characters (`scenarios.json` order 3), and no file in `docs/scenarios/` describes it. Everything below is therefore a three-of-four analysis, and the scenario-composition rules in D-220 must be written so a fourth scenario can be added without redesigning the strip.

## Per-scenario UI consequences

### Ura Finale — the baseline, defined by absence

The guide is explicit: *"no team system, no shop, no mid-scenario currency."* So the Ura Finale dashboard is the **minimal** layout. Adding Unity Cup or Trackblazer chrome to it would be wrong, and a mockup that shows a team panel on a URA run is a factual error, not a design choice.

What it does have that the design must carry:

- **Three scripted Inspiration events** at fixed times (Junior start, Classic early April, Senior early April), each scaled by the chosen Legacy. A run-timeline marker with a known future date.
- **NPC friendship as a tracked resource.** Fan Fest needs 3 bars with Director Akikawa; A Super Successful Event needs max Akikawa friendship; Twinkle Monthly Special Issue needs max friendship with Reporter Etsuko Otonashi. Both NPCs begin *appearing in training* at scripted points (after 3 turns; Early July Junior). Friendship bars are therefore first-class run state, and the schema has nowhere to put them.
- **Fan thresholds at a completely different scale from race entry gates.** 60,000 / 70,000 / 120,000 Turf and 40,000 / 60,000 / 80,000 Dirt gate Unique Skill upgrades. `UMAMUSUME_REFERENCE.md` §1.2.6 records race *entry* gates of 350 to 25,000. These are two separate mechanics and a single "Fans: 1,943" readout serves neither well; the UI needs the next *event* threshold, not just the next *entry* gate.
- **Stamina floors by distance**, expressed as practical minimums with rank letters: Sprint ~350-450, Mile ~450-550, Medium ~600-700, Long ~700-800. Treat as hard prerequisites. This is the "recommended target" concept, now with numbers.
- **Summer Camp in both Classic and Senior**, 4 turns each, all facilities Lv5, and the camp *ends* with +5 to three random stats. Rest and Recreation restore both Energy and Mood during camp.

### Unity Cup — a second resource layer on top

- **Facility level is a team property, not a personal one.** `G-F=1, E-D=2, C-B=3, A=4, S=5`. The same "Lvl 5" label means opposite things in URA (you ground it) and Unity Cup (your team is strong enough). A level chip that doesn't say *why* it is that level is misleading in one of the two scenarios.
- **Spirit Bursts need their own visual state machine**: chargeable (flame icon), charged (meter full), held-but-not-triggered, and spent-forever. *"Each character only gets one Spirit Burst per career."* A spent teammate must look permanently consumed, not resettable.
- **Extreme (purple) Spirit Bursts are live on Global as of 2026-07-01**, so the state machine has a fifth, higher state, and it carries a hard consequence the design has been getting wrong: **"There is a 0% Failure Rate in the training facility where the Support has active ESB."** An Extreme burst standing up a risk warning on that facility is a false alarm the tool would be inventing. The facility's risk affordance must go quiet, not merely smaller.
- **The 2026-07-01 patch also made a stronger opponent team live** — an **Elite Team** can appear in the 4th Team Race round once the qualifying conditions are met, and losing rounds can now be retried by spending a Clock. Both are timeline events worth a marker, and the retry in particular means a logged loss is no longer necessarily final.
- **Bursts raise Energy cost except on Wit**, where they raise Energy gain. That is a direct, scenario-specific modifier on the §6.15 gauge and on the preview deltas.
- **Multi-uma bonus scales with headcount on the tile** (2 / 3 / 4). Facility occupancy is information, not decoration.
- **Team Races: 5 rounds at fixed calendar points**, each a 5-race card, one per distance, 1-3 racers per distance. Structurally different from goal races and must not share their calendar cell treatment.
- **Team Name selection around Junior Late September**, five options bound to linked characters, reward granted only on beating Team Zenith.
- **Burning / Ignited Spirit ladder** from burst count: 13+, 10-12, 7-9, 4-6. A progress-toward-reward display, variant chosen by the team's highest stat rank.
- **The guide records a genuine usability defect we can fix:** *"Skill hint icons are largely hidden by the Unity Training icon overlay on facilities — check every facility manually."* A tracker that surfaces a hidden skill hint is real value, not a reskin.
- **Racing outside team and goal races is actively bad here.** The opposite of Trackblazer. Any race advisory must be scenario-gated or it will give harmful advice in one of the two.

### Trackblazer — a different game shape

- **No mandatory race goals.** The goal structure is Grade Points against deadlines. **The Race Calendar panel does not apply.** What replaces it is a Grade Point progress meter with deadline markers, and a free-form race log.
- **The deadlines are Late December of each year, and surplus does not carry forward.** That pair of facts defines the meter's job: it is not a running total, it is a countdown against a per-year target. A Grade Point readout that never resets states the wrong thing, and banking points for the next phase is not a strategy available to the Trainer.
- **Shop Coins and an in-run shop.** Coins scale with placement, 100 for first. Items raise stats, set condition, restore Energy, raise bond. Unspent coins are worthless, so the UI should surface a balance with a "spend it" nudge.
- **The shop restocks every 6 turns.** This converts the spend-it nudge into a countdown with a real decision in it: saving coins for an item you want means risking it leaves the rotation before you can afford it. A balance without the restock timer loses information the Trainer is actively reasoning over.
- **Rival races carry a VS icon** and only an outright **1st place** yields the skill hint; out-placing the rival but losing to someone else is a draw and pays nothing. A "must-win" marker distinct from a goal race. **Rivals only appear for distances the trainee has C aptitude or better in**, so the number of rival opportunities a given run can contain is bounded by her aptitude array, and a UI that implies a rival race is available at a distance she is D-rated in is stating something impossible.
- **Secret character events do not fire.** Silence Suzuka's Runaway style is unobtainable. A trainee-selection warning, not a per-turn concern.
- **Fan farming is the scenario's strength**, which inverts the URA framing where fans are three discrete gates.

## Resolutions to the two blocking conflicts

Both were opened on 2026-09-27 as "needs an owner ruling". Both are now closed against primary sources dated after the files in question, so no ruling is required.

### 1. Unity Cup caps — resolved, and the higher figures are live

`docs/scenarios/06-unity-cup-gametora.md`, written **after** the 2026-07-01 Global update (last updated 2026-07-20), gives the caps directly as `1300 / 1300 / 1300 / 1300 / 1800` and states in parentheses: *"This matches the 'future update' caps mentioned as pending in the original overview document — they are now confirmed active."* That is the exact reconciliation of the conflict: `02-unity-cup.md` was right when written and is now superseded by its own predicted future. The game data independently produces the same numbers.

`UMAMUSUME_REFERENCE.md` §1.3.4 is therefore correct as applied to Global today, despite its rows being flagged `STALE` — stale sourcing that happened to converge on the post-rework values.

**Consequence:** the 1,800 Wit denominator in `DESIGN.md` §6.22 and in the v7 and v8 mockups is **confirmed**, not unconfirmed. It no longer needs a caveat.

### 2. Trackblazer status — resolved, and its unknowns are now filled in

Global release **2026-03-12**, confirmed three ways: the dataset's `start_en`, `UMAMUSUME_REFERENCE.md` §1.6 item 3, and both owner-supplied Trackblazer files. `docs/scenarios/03-trackblazer.md`'s "What We Don't Know Yet" section is now largely answered by `04-trackblazer-umaguide.md` (strategy, epithets, shop ratings) and `05-trackblazer-gametora.md` (patch-accurate mechanics and the full item list):

| Fact | Value | Source |
|---|---|---|
| Grade Points per 1st place | G1 100 · G2 80 · G3 60 · OP 40 · Pre-OP 20 | 05, 04 (agree) |
| Grade Point placement modifier | 1st 100% · 2nd 60% · 3rd 40% · 4th-5th 20% · 6th+ 10% | 04 |
| Shop Coins by placement | 1st 100 · 2nd-3rd 60 · 4th-5th 30 · 6th+ 0 — **independent of race grade** | 05, 04 (agree) |
| Grade Point objectives | 4 total: Debut race, then 60 / +300 / +300 | 05 |
| Deadline timing | End of Junior, Classic, Senior year | 04 |
| Aptitude exception | High-dirt/low-turf: obj 2 drops to **30**, obj 3 to **200**. Poor non-short aptitude (e.g. Curren Chan): obj 3 = **200** | 05 |
| Surplus carry-over | **None** — each objective period starts at zero | 05, 04 |
| Shop restock | Lineup refreshes **every 6 turns**, with a visible in-game timer | 05 |
| Shop stock limit | **Max 5 copies** of a single item held at once | 05 |
| Shop special offers | Limited items (own window, flagged top-right) · Sales 10-20% off (flagged top-left) | 05 |
| Rival appearance | Random, from **Early August, Junior Year**; red/blue VS speech-bubble icon | 04, 05 |
| Rival reward | Win = a skill hint (05) or a hint **or** +5 to 2 random stats (04). Participating alone can add shop items | 04, 05 — minor conflict, see below |
| Facility leveling | Repeat one stat 4x, **plus** permanent +1 from shop Training Application items (150 coins) | 05 |
| Scenario Link character | **None.** No new story characters either | 05 |
| Finale | **Twinkle Star Climax**: 3-race points league, not an elimination bracket | 05, 04 |
| Secret events | Cannot trigger | 05, 04 |

The one internal conflict worth recording: umamusu.wiki quoted Grade Point thresholds of 100 / 450 / 480, which does not match the 60 / 300 / 300 both owner files agree on. Two agreeing primary files outrank it; treat the wiki figures as likely cumulative.

### The trap that produced the wrong Trackblazer row

`UMAMUSUME_REFERENCE.md` line 357 records the Climax row as `1200 / 1900 / 1200 / 1500 / 1200` and attributes it to **Game8 JP 2025-11-21** and **Kamigame 2024-02-19**. Climax is JP's name for the scenario Global shipped as Trackblazer (§1.6 item 3). The game data says `1200 / 1900 / 1200 / 1200 / 1500`, so **the reference doc has Guts and Wit transposed**, and `ADR-0002` plus `DESIGN.md` §6.22 faithfully carried that error forward under a Global label.

Stamina 1900 agrees either way, which is what kept it invisible: the one number large enough to break validation was right, and only the two below it were swapped.

Two lessons, and the second is the one that generalises:
1. A stale row propagates silently as long as it stays plausible.
2. **Translating a scenario's name is not re-sourcing its numbers.** Any value carried across the JP/Global boundary needs a Global-side source, not a renamed label. `UMAMUSUME_REFERENCE.md` §6 exists to catch this class and did not.

### The same trap in the other direction

`docs/scenarios/05-trackblazer-gametora.md` states Global Trackblazer is *"expected to launch with the standard 1200/1200/1200/1200/1200 caps"* and warns against assuming the higher Stamina/Wit caps. That file is dated **2026-03-12, the launch day**, and hedges explicitly ("expected", "may be adjusted post-launch", "check current patch notes"). The data says it was adjusted. So a source that was correct-then-stale on its own publication date is now wrong, and its own advice was the load-bearing line. A file's authority is its date, not its publisher.

## Live Global stat caps — measured from the game's own data

Resolved with a **machine-readable source** rather than prose, because the guide files disagreed with each other. GameTora serves the scenario table as plain JSON; manifest key `scenarios`, hash `61b7c51c`, fetched 2026-09-27. Each row carries a five-element `stats` array and a `hard_caps` array.

**`stats` is a bonus over a 1200 base, not a ceiling.** Two independent proofs: every row reproduces its published caps when added to 1200, and JP scenario id=8 carries a **negative** Stamina bonus of `-200`, giving a cap of **1000** — below the base. A field that can sit under the base is a delta, not a limit.

`hard_caps` is a separate, higher engine ceiling: **2000** for every scenario live on Global, 2500 for the two JP-only future ones. Its sixth element (`9999` / `99999`) has unexplained semantics; do not assume it is a stat.

| Order | Scenario | Global live | Speed | Stamina | Power | Guts | Wit | Hard cap |
|---|---|---|---|---|---|---|---|---|
| 1 | URA Finale | 2025-06-26 | 1400 | 1400 | 1400 | 1400 | 1400 | 2000 |
| 2 | Unity Cup | 2025-11-06 | 1300 | 1300 | 1300 | 1300 | **1800** | 2000 |
| 3 | Trackblazer | 2026-03-12 | 1200 | **1900** | 1200 | 1200 | 1500 | 2000 |
| 4 | Our Grand Concert | 2026-07-22 | 1600 | 1300 | 1300 | 1500 | 1300 | 2000 |

Three prose sources cross-check the arithmetic exactly: Game8's Global URA guide ("+200 stat caps" → 1400), Game8's Global Unity Cup guide ("+100 … +600 Wit" → 1300/1800), and `06-unity-cup-gametora.md`'s caps table.

**A fifth finding hiding in that table: Our Grand Concert has been live on Global since 2026-07-22.** It is the fourth permanent scenario and it caps Speed at **1600** — the highest Speed ceiling on Global, above URA's 1400. `UMAMUSUME_REFERENCE.md` §1.6 records it and states **"4 total selectable"** on Global, so the repo knew; what is missing is a mechanics guide, and none of `docs/scenarios/` covers it. Every scenario-composition rule in this document is therefore a **three-of-four** analysis, and D-220 and D-233 in particular must be written so a fourth scenario can be added without redesigning the strip or the finale panel.

**The ceiling a Trainer can legitimately reach today is 1,900** (Trackblazer Stamina). `PRD.md` FR-C-2 validates `TurnEntry` stats at **0..1200**, so since 2026-07-01 any Global player who pushed a stat past 1200 has been unable to log it. The stored data also answers what to widen the bound to: not a guessed round number but the game's own `hard_caps` = **2000**. See `ADR-0002`.

The 1200 line still matters as a *displayed* value, because it is the halved-gains threshold — a game mechanic, not an app limit.

## Mechanics the 2026-07-01 patch superseded

`02-unity-cup.md` predates the update and says so of several features; `06-unity-cup-gametora.md` is written against it and states plainly that earlier guides, *"including the original overview document in this set"*, carry wrong Spirit Burst values. Reading the two side by side invalidated rules I had already written into `CONSTRAINTS.md`, not only the owner's file. These are the ones that change UI behaviour.

| Claim as designed | Post-patch fact | What the UI must do differently |
|---|---|---|
| Spirit Bursts raise Energy cost on every facility except Wit (my **D-223**) | *"the additional energy cost was removed from Special Training"* | Do not add an energy penalty to a Unity Training preview. The Wit exception survives only as a **burst** bonus: a Wit burst grants **+5 extra energy recovery**. |
| One Spirit Burst per character per career; a spent teammate is permanently consumed (my **D-223**) | Extreme Spirit Burst is available on a teammate's **next** Unity Training after their normal burst fires | A spent teammate is **not** terminal. They enter an Extreme-chargeable state. Rendering "spent" as a dead end is now wrong, and it was the load-bearing idea of the four-state machine. |
| Bursts grant a **random** skill hint (both owner files) | Hints now draw from that support card's own hint pool; falls back to A-rank aptitude when the pool is exhausted | The tool can name *which pool* a hint came from. It still must not predict which hint — that is a draw, and Planner Rule 1 forbids simulating it. |
| Burst ladder: 13+ → Burning Spirit X Lv.3 + 20 SP, and 02's "Burning/Ignited" naming | Rewards now scale on **bursts + Extreme bursts combined**: 4-6 white Lv.1 +10 stat +10 SP · 7-9 white Lv.3 +20/+20 · 10-12 gold Lv.1 +30/+30 · 13+ gold Lv.3 +40/+40 | The progress meter's denominator changed meaning, not just its numbers. Count both burst tiers in one total. |
| Team Rank tops out at S (my **D-222**) | **S+** exists above S | One more rung. Facility level still caps at 5 at S, so rank and facility level are now decoupled at the top — S+ grants a *second* "It's On!" hint rather than a higher facility. |
| No league-rank concept | League rank rises with opponent strength and **falls on a loss**; Elite Teams need rank ≥10, Team Rank ≥A, and ≥1 Extreme Burst | A new tracked resource with a downside, and a gating condition for the Elite Team path. The client shows a circle-based win-odds estimate with a "3 circles" rule of thumb. |
| NPC friendship is a universal run resource (my **D-226**) | **Akikawa is absent from Unity Cup**; Riko Kashimoto stands in, so Unity Cup's April Unique Skill level-up has **no bond condition** | Which NPCs exist at all is scenario-dependent. A friendship gauge for an NPC who is not in the scenario is worse than no gauge. |
| A logged loss is final | Team Race losses can be **retried with an Alarm Clock** | The race log must allow a loss to be superseded, and should say so rather than silently overwriting. |
| Unity Cup teammates' stats track the support card's level | Teammate stats are raised **only** through Special Training and are *"NOT dependent on the support card's own level or limit break"* | Team composition display must not imply card level drives team rank. Only real Support Cards carry a bond gauge or can do Friendship Training; story and random teammates cannot. |
| Finals are the URA-shaped elimination races | Unity Cup finals come **after 4 Team Races** vs Team Zenith; beating Zenith at Rank S puts **Little Cocon and Bitter Glasse** into the later URA-style final race as opponents | The end-of-run sequence is scenario-specific and introduces two named original characters the design has never accounted for. |

### Trackblazer's own structural corrections

Beyond filling in unknowns, three things in `04`/`05` contradict what this document and `CONSTRAINTS.md` had asserted:

- **There is no Scenario Link character and no new story character in Trackblazer.** My **D-216** stated *"Each scenario has a Scenario Link character."* That is false. The dataset confirms it independently: Trackblazer's `scenario_linked_characters` is an empty array, while URA has Aoi Kiryuin and Unity Cup has five entries. A scenario-link affordance that assumes one exists per scenario breaks on the third scenario.
- **Facility level is documented, not unknown.** My matrix said "Unknown / not documented" for Trackblazer. It follows the URA rule — repeat one stat 4x, max Level 5 — **plus** permanent +1 purchases from the shop at 150 coins. That makes Trackblazer the only scenario where facility level is partly *bought*, which is a shop consequence the UI should surface.
- **The finale is a points league, not an elimination bracket.** Twinkle Star Climax is 3 races scored by Victory Points (1st 10 · 2nd 8 · 3rd 6 · 4th 4 · 5th-6th 3 · 7th-9th 2 · 10th-13th 1 · 14th+ 0), max 30, and you win on total points rather than by winning all three. Climax races **pay no Shop Coins**, so the run's final economy decision is spending down beforehand. An end-of-career panel modelled on URA's qualifier → semifinal → final progression is wrong for this scenario.

### A terminology hazard in the source files themselves

These three files are written in the wikis' English, which is **not** the Global client's English, and they carry two banned terms in table headings:

- **"Wisdom"** appears throughout `05` and `06` for the stat Global calls **Wit** — including in facility rows and `hard_caps`-adjacent tables.
- **"Motivation"** appears in `05`'s shop item table (`Motivation +1`, `Motivation +2`) for what the client calls **Mood**.

Both are exactly the class `CONSTRAINTS.md` D-20 exists to stop. Copying a table out of these files into UI copy would import a wrong term wholesale. The numbers are trustworthy; the headings are not.

### The Race Fatigue table, and what it does to ADR-0001

`ADR-0001` concluded that training failure **percentages** could not be sourced and must ship as bands. `04-trackblazer-umaguide.md` contains a fully quantified probability table for a different failure class — Race Fatigue, scaling with consecutive races:

| Consecutive races | Mood down (1+ Energy) | Mood down (0 Energy) | Skin Outbreak (1+E) | Skin Outbreak (0E) | 3 random stats −10 |
|---|---|---|---|---|---|
| 1 | 0% | 15% | 0% | 4% | 0% |
| 2 | 0% | 33% | 0% | 8% | 0% |
| 3 | 60% | 90%+ | 15% | 25% | 0% |
| 4+ | 100% | 100% | 33% | 33% | 40% |

Two rules follow. **The risk is a function of a count the tool already keeps** — consecutive races — so this is the rare case where a real probability can be shown instead of a band, derived deterministically from entered turns exactly as Planner Rule 4 requires. And **Race Fatigue cannot occur after Late December**, so the warning must switch off in the endgame stretch rather than following the player into the Climax.

### 3. Mood race-performance figure

`01-ura-finale.md` gives Great Mood as `+20% training, +4% race performance`. `UMAMUSUME_REFERENCE.md` §1.1.6 gives 絶好調 as `+20% training, +10% pre-race base ability`. The training figure agrees across both; the race figure does not. Neither is UI-blocking today because the tool displays the tier word, not the multiplier.

### 4. Terminology: Recreation vs Outing

`01-ura-finale.md` uses **Recreation** for お出かけ. `UMAMUSUME_REFERENCE.md` §1.1.5 uses **Outings**. Same action, two English terms, neither verified as the Global client string. D-20 blocks promoting either. Unresolved.

## What this does to the schema

`ADR-0003` proposed `scenario_races` and `race_entries`. That design is now known to be **URA/Unity-Cup-shaped** and does not fit Trackblazer, which has no race goals. Two options:

- **Generalise** `scenario_races` into `scenario_slots` with a `kind` discriminator (`GoalRace`, `TeamRace`, `GradeDeadline`, `ScriptedEvent`), letting each scenario populate different slot types against one table.
- **Split**, giving Trackblazer its own `grade_deadlines` and leaving `scenario_races` for the goal-race scenarios.

Generalising is the smaller schema and the one that keeps a single timeline view working across scenarios, which matters because the timeline is the product. Recommend generalising.

Also newly required by the URA material and not in ADR-0003: **NPC friendship state** (which NPC, how many bars) and **Spirit Burst state** (per teammate, spent or not). Both are per-run, per-turn facts a Trainer would want logged, and both are scenario-specific, which is the argument for storing them as a typed json payload on `turn_events` rather than as columns on `turn_entries`.

## Design rules this generates

Recorded in `docs/design-research/CONSTRAINTS.md` as D-220 to D-229.
