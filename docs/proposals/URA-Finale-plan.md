# URA Finale Plan

This document defines the URA Finale scenario for Trainer Desk. It is written in the same architectural style as the Unity Cup plan: it assumes the shared cockpit frame, the three-layer model (Trainer Home, Career Cockpit, Advisor), decision cards, visible uncertainty, and the Veteran loop, all of which carry over unchanged. Only the URA Finale-specific deltas are spelled out here.

**Last verified:** 2026-10-21, against `master` at `d00a0457` — the tree as it stood when this pass began. Every line-number citation in this document was re-checked against the file it names, and every claim that a reader might act on was re-checked against the repository or the source it cites. Where a check failed, the claim is marked rather than deleted. A concurrent session committed `15bba0f9` and `d664f948` while the pass ran; neither touches `config/scenarios.php`, `SCREEN_SPEC.md`, `docs/scenarios/`, `docs/research-scratch/`, `app/` or `tests/Feature/`, so every file this document names was verified against the same content it names.

**Status vocabulary.** This document is a design plan, not a roadmap. Its features carry the levels `Implemented`, `Verified`, `Game-validated` and `UNASSESSED — requires code audit`, defined in §15 below and assigned per feature there. It does not use the roadmap's `COMPLETE` / `BLOCKED BY EVIDENCE` slice statuses, and the Our Grand Concert roadmap does not use the feature levels; the two vocabularies are deliberately different and neither is a synonym for the other. **Source badges** are shared across all four scenario documents: `verified` (established in this repository or by a tier-S / tier-A source), `derived` (computed from a verified fact by a stated rule), `configurable` (a value the tool reads from configuration), `community` (a third-party guide, guidance and not a game fact), `unknown` (no cited source establishes it).

## Sources

Each source on one line: name — URL or path — last-updated date — the claims it supports.

- `docs/scenarios/01-ura-finale.md` — repository path, no URL — last verified 2026-09-27 — the URA Finale scenario guide: fan gates, the Summer camp window, the training-level rule, stamina floors, deck shapes, the Legacy Inspiration events, and the fixed event calendar.
- `docs/UMAMUSUME_REFERENCE.md` §2.2.1 — repository path, no URL — last verified 2026-10-21 — the URA Finale `[Global]` mechanics summary: Happy Meek, the fan gates, the beginner targets, the finale rewards.
- `config/scenarios.php`, the `ura_finale` entry — repository path, no URL — last verified 2026-10-21 — `cap_bonus` of +200 per stat, the `panels` map (`race_calendar` and `career_goals` true), `scenario_links` (Aoi Kiryuin), `facility_level_source` = `repetition`, and the `fan_gates` block.
- `docs/research-scratch/SCENARIO-PUBLISHER-REFERENCES.md`, the Game8 scenario extraction, URA Finale section — repository path; the source behind it is [Game8, URA Finale Scenario Guide](https://game8.co/games/Umamusume-Pretty-Derby/archives/536520) — page last updated July 6, 2026 — the July 1, 2026 rework, Happy Meek duels, duel rewards, the Racing Spirit skills, and the raised stat caps.
- `docs/scenarios/09-global-race-calendar.md` — repository path, no URL — last verified 2026-10-21 — the career year's shape: 24 turns, two per month, and the turn-number formula, which `docs/scenarios/01-ura-finale.md` defers to explicitly.
- `.scratch-uma/cited/scenarios.json` — gitignored scratch export — **not present in this tree on 2026-10-21** — the GameTora data export that was the second record of the +200 cap raise. Any claim that rested on it alone is now badge `unknown`; the cap claim survives on the config entry and the Game8 page.
- `docs/research-scratch/SCENARIO-PUBLISHER-REFERENCES.md` — repository path, no URL — last verified 2026-10-21 — the verbatim extractions of every publisher page cited above, and the disagreement register.

**Cross-check note.** URA Finale enters `docs/research-scratch/SCENARIO-PUBLISHER-REFERENCES.md` "solely through the Game8 extraction" — that sentence is the "Publisher coverage is uneven across the three scenarios" bullet in that file's **Disagreements** section, where uma.guide and GameTora are recorded as covering only Trackblazer and Unity Cup. There is no second publisher to cross-check against for URA Finale. The config matrix and the scenario guide are the first author and the Game8 page the second; they agree on every figure cited below.

**Dated note (2026-10-21).** The cross-check note previously cited `SCENARIO-PUBLISHER-REFERENCES.md:33` and the Sources list cited `:892-996` for the Game8 extraction. Both line numbers had drifted; the bullet and the section headings are used above instead, because a section name in a file that grows does not rot. The claim itself is unchanged and re-verified.

The July 1, 2026 rework added Happy Meek duels and raised caps from 1200 to 1400 in all stats. The `ura_finale` entry's `cap_bonus` in `config/scenarios.php` (= 200 each) and the Game8 page both record this; the Game8 page confirms it in §"Increased Stat Caps". The GameTora export that also recorded it is no longer in the tree, so the cap now rests on two sources rather than three. **Badge: `verified`** (config entry, transcribed; Game8 page, community guide).

---

## 1. THE CENTRAL UX CONCEPT

URA Finale is the fundamentals scenario. There is no team, no shop, no mid-scenario currency, and no special program. The single thing layered on top of the base training loop is the **Happy Meek duel**:

```
         STAT TRAINING turn
               │
               ├─► Happy Meek may appear
               │        │
               │        ├─► WIN → stat gain + cap bump + racing-spirit hint + 30 SP
               │        └─► LOSE → minor stat gain + 15 SP
               │
               └─► 6 wins escalate her to powered-up state at the finals
                     WIN → Past My Limits skill + 200+ SP + enhanced spirit spark
```

The persistent question for URA is:

> Which training turn raises the stats I need for the next fan gate or goal race, and should I spend energy on a Happy Meek duel now or save it for a Summer facility upgrade?

---

## 2. SCENARIO SELECT

```
URA FINALE
Fundamentals. Happy Meek duels for cap increases.

Stat caps   SPD 1400  STA 1400  POW 1400  GUT 1400  WIT 1400
Facility level rises by repetition (every 4 same-stat trainings)
Scenario Link: Aoi Kiryuin
```

The +200 cap_bonus is from the `ura_finale` entry's `cap_bonus` in `config/scenarios.php` — `['Speed' => 200, 'Stamina' => 200, 'Power' => 200, 'Guts' => 200, 'Wit' => 200]` over the 1200 base, so the five caps read 1400. **Badge: `configurable`** for the value the tool reads, `verified` for the transcription; the GameTora export that also recorded the raise is no longer in the tree, so the cap now rests on the config entry and the Game8 page. Aoi Kiryuin is the scenario link; the same entry's `scenario_links` lists her, and the "Scenario Link Character: Aoi Kiryuin" section of `docs/scenarios/01-ura-finale.md` confirms her event role.

The facility rule is `facility_level_source` = `repetition` in the same config entry, and `docs/UMAMUSUME_REFERENCE.md` §2.2.1 states the rule itself — "Facility progression by repetition. Training facility level rises from using the same discipline repeatedly, rather than from a team rank or a parallel meter." **Badge: `verified`** for the rule and the config key. The *step* — "every 4 same-stat trainings" — is stated by the "Training Level (Repeat-to-Upgrade)" section of `docs/scenarios/01-ura-finale.md` and is not in the reference, the config, or any captured client screen. **Badge: `community`.**

---

## 3. TRAINEE & TARGET (DELTAS)

The career goals, fan gates, and goal-race ladder are the URA defaults carried from the "Fans and Skill Points" section of `docs/scenarios/01-ura-finale.md` and confirmed in Game8 §"Run More Races to Get Fans and Upgrade Unique Skill".

- **Fan gates** (Turf / Dirt): Valentine's Day 60,000 / 40,000 (Senior, early Feb); Fan Fest 70,000 / 60,000 (Senior, early Apr, with 3-bar Director Akikawa friendship); Holiday Season 120,000 / 80,000 (Senior, late Dec). Source: the three threshold bullets under "Fans and Skill Points" in `docs/scenarios/01-ura-finale.md`, Game8 §"Run More Races...", and `docs/UMAMUSUME_REFERENCE.md` §2.2.1. **Badge: `verified`** — the same three gates are transcribed into the `ura_finale` entry's `fan_gates` block in `config/scenarios.php`, so the repository holds them independently of the guide.
- **Stamina floors** by distance: Sprint 350–450, Mile 450–550, Medium 600–700, Long 700–800. Source: the "Stamina Requirements" table in `docs/scenarios/01-ura-finale.md` and `docs/UMAMUSUME_REFERENCE.md` §2.2.1. **Badge: `community`** — the guide states the ranges; the reference carries them, and the repository's `finale_targets.stamina_by_distance` holds a separate, single figure per distance (400 / 500 / 600 / 700) for the beginner target, which is a different quantity and must not be read as the floor.
- **Unique Skill level-up gates** at those same fan thresholds. Source: same as fan gates above.
- **Goal race distance.** The finale distance is determined by the most-raced distance type across the career; ties resolve to the shortest distance. This is the URA-specific distance ledger and the one surface where URA diverges from the other scenarios' fixed calendars. **Badge: `verified` (repo-recorded).** The Career Cockpit states this rule as its own rule row and cites this document for it; the code reads no column for it, so the rule is carried, not computed. The guide's "so plan a race calendar that reliably clears them" (the "Fans and Skill Points" section of `docs/scenarios/01-ura-finale.md`) is the reason a calendar is needed, not the source of the distance rule; Game8 §"Ensure You Have Enough Stamina" lists all four distance-specific thresholds as the decision driver. **Dated note (2026-10-21).** This bullet previously cited `docs/scenarios/01-ura-finale.md:140`, which is the "Never dip below the distance-appropriate Stamina floor" line of the guide's best-strategy summary and does not support the rule. The citation is replaced by the section that does, and the rule's real home — the repository — is named.

No new goal-race or fan-gate screen is needed — the shared `race_calendar` panel handles distance tracking and the `career_goals` panel handles the gates. Both are `true` in the `ura_finale` entry's `panels` map in `config/scenarios.php`.

---

## 4. LEGACY LAB (DELTAS)

Three scripted Inspiration events scale stat boosts off the Legacy's own build — Junior Year start, Classic Year early April, Senior Year early April. Source: the "Legacy Umamusume (Inheritance)" section of `docs/scenarios/01-ura-finale.md`. **Badge: `community`** (the guide states it; the reference's §2.2.1 does not carry the three-event split).

There is **no scenario factor** in URA Finale. Unlike Unity Cup (`Power` and `Wit` on inheritance) or Trackblazer (distance-dependent stat grants), URA's Legacy inherits base stats and skills only. The Legacy Lab screen should show no scenario-factor badge to avoid implying a bonus that does not exist.

---

## 5. SUPPORT DECK BUILDER (DELTAS)

Deck templates from the "Building Your Support Deck" section of `docs/scenarios/01-ura-finale.md` and confirmed in Game8 §"Use Proper Support Cards That Will Benefit Your Umamusume":

| Distance   | Build 1                | Build 2                          |
| ---------- | ---------------------- | -------------------------------- |
| Sprint     | 4× Speed, 2× Power     | 3× Speed, 1× Wit, 2× Power       |
| Mile       | 4× Speed, 2× Power     | 3× Speed, 1× Wit, 2× Power       |
| Medium     | 4× Speed, 1× Friend/Guts, 1× Power | 3× Speed, 1× Wit, 1× Friend/Guts, 1× Power |
| Long       | 4× Speed, 2× Friend/Guts | 3× Speed, 1× Wit, 2× Friend/Guts  |

Key principles:
- Check the skills a card provides — a card's built-in skill matching the trainee's running style/distance becomes purchasable during training. Source: Game8 §"Pay Attention to Skills that Cards Provide".
- Speed is close to universally valuable regardless of build.
- Stamina cards are best avoided (you get Stamina mainly from stats, and the Stamina floor is a hard prerequisite, not a flex).

Present these as community guidance only; the Trainer's own cards and distance goal override them.

---

## 6. RUN PREFLIGHT

```
RUN PREFLIGHT                                    URA Finale

DECK          Race Bonus 41%   ✓ adequate for beginner targets
              Aoi Kiryuin (SR) ✓ Scenario Link present
LEGACY        [Legacy Uma Name]  → Inspiration events scale on this
TARGET        Sprint  →  Speed 800+, Stamina ≥ 400
              Mile    →  Speed 800+, Stamina ≥ 500
              Medium  →  Speed 800+, Stamina ≥ 600
              Long    →  Speed 800+, Stamina ≥ 700
GOALS         URA fan gates   ✓ planned
FANS          Valentine 60k   ✓ (10 weeks out)
              Fan Fest 70k    ✓ (22 weeks out, Akikawa friendship planned)
              Holiday 120k    ✓ (final gate)
CAMP          Summer Y2: Jul–Aug, 4 turns, all Lvl 5  ✓ timed
              Summer Y3: Jul–Aug, 4 turns, all Lvl 5  ✓ timed
DUels         Happy Meek     0/6 duels won so far
              Past My Limits  ✗ not yet in reach

RULE CONFIDENCE
  Stat caps                 the ura_finale entry's cap_bonus in config/scenarios.php
  Fan gates                 docs/scenarios/01-ura-finale.md + Game8
  Stamina floors            docs/scenarios/01-ura-finale.md + UMAMUSUME_REFERENCE.md §2.2.1
  Training level (repetition) the ura_finale entry's facility_level_source = 'repetition'
```

*Illustrative only.* Every figure in the preflight block above is a worked example, not a recorded
value: the Race Bonus, the deck contents, the fan counts, the week offsets, the camp rows and the
duel tally are placeholders a live run would fill. What the block is for is the shape and the rule
column. The rule column is the part that is sourced, and each line carries the badge of the source
it names: the caps come from the `ura_finale` entry's `cap_bonus` in `config/scenarios.php`
(`configurable`), the fan gates from the "Fans and Skill Points" section of
`docs/scenarios/01-ura-finale.md` and Game8 (`verified` in the repository, `community` in the
guide), the stamina floors from the same guide and `docs/UMAMUSUME_REFERENCE.md` §2.2.1
(`community`), and the training-level rule from the `ura_finale` entry's `facility_level_source`
(`verified`). The Summer rows state the guide's "4 turns, roughly Early July–Late August" window;
the "every Lvl 5" part is the guide's own claim, not a measured client screen.

---

## 7. THE CAREER COCKPIT

```
┌──────────────────────────────────────────────────────────────────────┐
│ URA FINALE                 CLASSIC • TURN 31 • Energy 58           │
├──────────────────────────────────────────────────────────────────────╢
│ FACILITY LEVELS              Training facility level by repetition  │
│ Speed       3    (12 trainings)                                     │
│ Stamina     2    (8 trainings)                                      │
│ Power       3    (12 trainings)                                     │
│ Guts        2    (8 trainings)                                      │
│ Wit         3    (12 trainings)                                     │
├──────────────────────────────────────────────────────────────────────╢
│ FAN GATES                                                           │
│ Valentine's Day    48,000 / 60,000 (Turf) — 14 turns to gate        │
│ Fan Fest           32,000 / 70,000 (Turf) — 22 turns to gate        │
│ Holiday Season     18,000 / 120,000 (Turf) — 44 turns to gate       │
├──────────────────────────────────────────────────────────────────────╢
│ DUEL TRACKER                                                        │
│ Happy Meek:  3 / 6 wins   (2 more to escalation)                    │
│ Racing Spirit hints earned: Speed Lv1, Power Lv1                    │
├──────────────────────────────────────────────────────────────────────╢
│ ★ RECOMMENDED: TRAIN STA                                            │
│                                                                  │
│ 4 flames: +6 Sta, +3 Guts, +5 SP (repetition Lv3)                  │
│                                                                    │
│ WHY                                                                  │
│ Stamina is your weakest floor for a Medium-distance build.           │
│ Facility is at Lv 2; one more block of 4 reaches Lv 3.               │
│ Energy 58 → 18 after training. Summer starts in 6 turns;              │
│ you need 60 for 4 Lvl-5 facility turns. Recreation on turn 33        │
│ covers the gap.                                                    │
│                                                                    │
│ [ TRAIN STA ]                                                      │
│ Alternatives                                                       │
│ ○ Speed: +facility, but Stamina floor is the blocker                 │
│ ○ Optional race: only if a goal requires it                          │
└──────────────────────────────────────────────────────────────────────┘
```

All numbers in that wireframe are illustrative except the rule they encode: facility level rises every 4 repetitions (the `ura_finale` entry's `facility_level_source` = `repetition` in `config/scenarios.php`, with the "every 4" step from the "Training Level (Repeat-to-Upgrade)" section of `docs/scenarios/01-ura-finale.md` — `community`), and Stamina is a floor not a flex stat.

---

## 8. HAPPY MEEK DUEL PANEL (NEW, THE KEY IN-RUN SCREEN)

This is the URA-only mechanic, so it gets a dedicated panel — not folded into the generic cockpit.

### When she appears

Happy Meek appears on any stat-training turn after the "I'm Here to Challenge You" event. Source: Game8 §"Training With Happy Meek". She does **not** give Friendship trainings (unlike Team Sirius or Heirs to the Throne). Source: Game8 §"Training With Happy Meek".

### Duel choice

You are prompted to pick between three options: two random stats and the stat you dueled Happy Meek on. Each option has an icon indicating win chance. Source: Game8 §"Duel for Additional Stat Cap Increases and Skill Points".

### Rewards

| Outcome | Primary                     | Secondary        | Cap bump | Skill Points | Racing Spirit hint |
| ------- | --------------------------- | ---------------- | -------- | ------------ | ------------------- |
| Win     | Stat gain (varies)          | Related stat +SP | +1 tier  | 30           | Yes (stat-appropriate) |
| Lose    | Minor stat gain             | —                | —        | 15           | —                   |

Source: Game8 §"Duel for...", UMAMUSUME_REFERENCE.md §2.2.1.

**Badge: `verified`** for the 30 / 15 Skill Point split, the six-win escalation count, the `Past My Limits` skill, the 200-or-more SP figure and the enhanced-spark sentence: `docs/UMAMUSUME_REFERENCE.md` §2.2.1 carries all of them, and the repository holds them independently in the `ura_finale` entry's `duel` block in `config/scenarios.php`. **Badge: `community`** for the per-outcome stat gain and the cap-bump "+1 tier" wording, which §11 items 1 and 2 record as unpublished. **Badge: `unknown`** for the per-option win chance, the per-stat gain values and the enhanced-spark rate, which the same config block holds as `null` on purpose.

### Escalation

After **6 wins**, Happy Meek escalates to a powered-up state at the finals. Beating her there grants:
- The `Past My Limits` skill (source: Game8 §"Powered Up Happy Meek", and the linked skill page title)
- 200 or more Skill Points
- A chance at an enhanced Racing Spirit spark (carries a stat bonus and a skill hint into inheritance)

Source: Game8 §"Powered Up Happy Meek", UMAMUSUME_REFERENCE.md §2.2.1.

**Badge: `verified`** for the six-win escalation count, the `Past My Limits` skill, the 200-or-more Skill Points and the enhanced-spark sentence. **Badge: `unknown`** for the powered-up stat values themselves (§11 item 5) and for the spark's trigger rate and stat-bonus amount (§11 item 6).

### Racing Spirit skills

Each Racing Spirit skill activates on the stat you dueled Happy Meek on. Source: Game8 §"New Racing Spirit Skills".

**Badge: `community`** for the six skill descriptions: the table transcribes Game8's own wording for what each Racing Spirit skill does, and no captured client screen or authoritative page states the effects numerically. The six skill *names* are `verified` in the repository: the `ura_finale` entry's `duel.hints` block in `config/scenarios.php` maps Speed, Stamina, Power, Guts and Wit to their Racing Spirit skill names, and it deliberately omits `Racing Spirit: Mood`, because a duel is fought on a stat and the sixth skill belongs to no stat the Trainer can pick. The table below therefore carries all six, with Mood marked as the one the tool does not offer.

| Skill                    | Effect                                                  |
| ------------------------ | ------------------------------------------------------- |
| Racing Spirit: Speed     | Slightly increase velocity upon approaching late-race   |
| Racing Spirit: Stamina   | Moderately expend endurance to moderately increase velocity in the last spurt |
| Racing Spirit: Power     | Slightly increase velocity for a medium duration when passing another runner in the last spurt |
| Racing Spirit: Guts      | Slightly increase acceleration when dueling             |
| Racing Spirit: Wit       | Slightly increase velocity upon activating 2 skills during the second half |
| Racing Spirit: Mood      | Stamina, Guts, and Wit might moderately increase when mood is good or great |

Display these as hints earned — the duel tells you which Racing Spirit skill the hint is for, and winning a powered-up duel enhances the spark that carries the hint into inheritance.

---

## 9. ADVISOR PRIORITY HIERARCHY

1. **Don't end the run.** The URA goal races, fan gates, and energy come first, and the Alarm Clock covers goal-race failures.
2. **Summer timing.** Is Summer within 6 turns? Bank full energy and good mood now — a forced Rest during Summer wastes the free Level-5 facility turns.
3. **Fan gate runway.** How many turns until the next fan gate, and is your race calendar on track to clear it?
4. **Happy Meek duel economics.** Cash the duel now (energy cost vs. stat gain + cap bump + SP) or save energy for Summer. A duel win also raises a cap, which matters for Long builds hitting the 1200→1400 transition.
5. **Facility level.** Track repetition counts; a stat at 8/12 trainings is one block away from the next level.
6. **Stamina floor.** Before a Medium or Long race, is Stamina at the floor? If not, that training turn is non-negotiable.
7. **Training target.**
8. **Optimization.**

---

## 10. CAREER REPORT

URA Finale specifics for the report: final stat breakdown, which fan gates were cleared, Summer camp utilization, Happy Meek duel record (wins / escalation), Racing Spirit hints earned, unique skill level at career end, and whether the finale was won. Then offer "Promote to Veteran" as before.

Post-race rewards (Game8 §"After URA Finale Races"):
- **Qualifier win:** All stats +10, +40 Skill Points, affected by Race Bonus.
- **Semifinals win:** All stats +10, +60 Skill Points, affected by Race Bonus.
- **Finals win:** All stats +10, +80 Skill Points, affected by Race Bonus.

**Badge: `verified`.** All three rows are carried in the "Full Fixed Event Calendar" section of `docs/scenarios/01-ura-finale.md` (the Senior Year table records "After URA Finale Qualifier — Win qualifier — All stats +10, +40 Skill Pts, affected by Race Bonus", and the same pattern for the semifinals and the finals) and in `docs/UMAMUSUME_REFERENCE.md` §2.2.1. The three SP figures and the "+10 to all stats" are the same numbers in both, and the Race Bonus qualifier is the client's own wording in both.

---

## 11. WHAT I COULD NOT CONFIRM

Every item below is preserved. Each carries a status and the date it was last reviewed: `OPEN` (no
source and no code settles it), `PARTIALLY RESOLVED` (one half is settled and the other is not),
`RESOLVED` (a source or the repository settles it), `SUPERSEDED` (a later source replaced it).

**Reviewed 2026-10-21.**

1. **OPEN — reviewed 2026-10-21. Per-option duel win-chance percentages.** Game8 states each option "has an icon which indicates your chances of winning" but does not print the percentage values. Read the icon from the game; the plan's duel rewards table is confirmed, the exact probability per option is not. The repository holds the same gap and says so: the `ura_finale` entry's `duel` block in `config/scenarios.php` carries `'win_chance' => null`, with a comment saying the nulls there are the items on this list and that they "stay null rather than holding a placeholder a later slice fills in with an estimate."

2. **OPEN — reviewed 2026-10-21. Happy Meek stat-gain formulas.** Game8 says winning grants "stat gains" and a cap bump but does not quantify the per-stat gain values beyond "the stat you dueled on." The 30/15 Skill Point split and the Racing Spirit hint award are confirmed; the exact stat-delta per win is not in any cited source. The repository holds the same gap: `'stat_gain_values' => null` in the same `duel` block.

3. **OPEN (magnitude only) — reviewed 2026-10-21. Aoi Kiryuin event bonuses.** The "Scenario Link Character: Aoi Kiryuin" section of `docs/scenarios/01-ura-finale.md` states she "boosts your stats via her interactions with another character, Happy Meek" but does not quantify the multiplier. Game8 §"Aoi Kiryuin is the Scenario Link" confirms the link role and recommends the SR card to amplify effects, but gives no numeric bonus. Treat this as a qualitative bonus, not a quantified one. **Dated note (2026-10-21).** The *role* half is now `verified` in two places — `docs/UMAMUSUME_REFERENCE.md` §2.2.1 and the `ura_finale` entry's `scenario_links` in `config/scenarios.php`, which lists `['Aoi Kiryuin']` — and the earlier citation `docs/scenarios/01-ura-finale.md:17` pointed at the section heading rather than the sentence that carries the claim. Only the multiplier remains open, and it is not in any source.

4. **PARTIALLY RESOLVED — reviewed 2026-10-21. Turns-per-year count.** The Game8 calendar uses relative timing ("After 11 turns", "Early July", "Late August") and does not give a total turn count per year, but the repository now supplies one: `docs/scenarios/09-global-race-calendar.md`, under "How a career year is laid out", states that `[Global]` runs three career years of **24 turns each, two per month, January through December**, with the turn number in every table equal to `(month − 1) × 2 + half`. That file exists, and the pointer this item relies on is accurate: the "Full Fixed Event Calendar" section of `docs/scenarios/01-ura-finale.md` states in a callout that "Race timing is not in this file" and defers the per-turn schedule to `docs/scenarios/09-global-race-calendar.md`. The Summer window remains confirmed as 4 turns, Early July through Late August, in both Classic and Senior years. What is still open is the offset from career start to each **scenario event** — the Inspiration events, the fan gates and the Summer camps are dated by month in the guide, not numbered by turn, and the calendar's formula makes them derivable rather than stated. The earlier citation `docs/scenarios/01-ura-finale.md:86-132` is replaced by the section it names.

5. **OPEN — reviewed 2026-10-21. Powered-up Happy Meek exact stat values.** Game8 §"Powered Up Happy Meek" states she "gets increased stats and is overall harder to beat" but does not list the specific numbers. The reward list (Past My Limits skill, 200+ SP, enhanced spark) is confirmed, and the repository holds it too: the `duel.escalation_reward` block in `config/scenarios.php` carries `skill`, `sp_minimum` and the spark sentence, and `escalation_wins` = 6.

6. **OPEN — reviewed 2026-10-21. Enhanced Racing Spirit spark mechanics.** "A chance to get an enhanced spark" — the exact trigger rate and the exact stat-bonus amount are not quantified in any cited source. The repository holds the same gap: `'spark_rate' => null` in the `duel` block.

7. **OPEN — reviewed 2026-10-21. Raffle Time! outcomes.** Game8 §"Raffle Time!" lists one of four random effects but does not state the probability distribution among them. Treated as a non-quantified random event. The four effects themselves are listed in the "Full Fixed Event Calendar" section of `docs/scenarios/01-ura-finale.md`, in the Senior Year table: "Random: all stats up / energy+mood up / energy only / mood down". Only the weights are unknown.

8. **RESOLVED — reviewed 2026-10-21. New Year's Resolutions / New Year's Shrine Visit choice weights.** Game8 lists the three options per event but does not state whether the choice is purely player-selected (it is — these are choice events, not random). This is confirmed by the scenario guide calling them "Choice:" events: the Classic Year table of the "Full Fixed Event Calendar" section in `docs/scenarios/01-ura-finale.md` records "New Year's Resolutions — Early Jan (start of turn) — Choice: Stat +10 / Energy +20 / Skill Pts +20", and the Senior Year table records "New Year's Shrine Visit — Early Jan (start of turn) — Choice: Energy +30 / All Stats +5 / Skill Pts +35". The plan records the options, not probabilities. **Dated note (2026-10-21).** The earlier citation `docs/scenarios/01-ura-finale.md:117-119` covered only the Senior Year row; both rows are named above.

---

## WHAT I WOULD NOT BUILD

- An auto-player for Happy Meek duels (read the icon, let the Trainer decide).
- A fixed "best deck" that overrides the Trainer's collection (present deck templates as guidance, not prescription).
- The enhanced Racing Spirit spark rate as a numeric prediction (no source quotes it).
- A stamina floor that silently defaults to a value a run has not recorded (render `N/A` with a title, never a default).

---

## MVP SCREEN SET

1. Trainer Home
2. Trainee & Target
3. Legacy Lab
4. Support Deck Builder
5. Run Preflight
6. Career Cockpit (with Fan Gates, Facility Levels, and Duel Tracker)
7. Happy Meek Duel Panel (in-run overlay triggered on a duel appearance)
8. Career Report / Veteran

§6 and §7 get most of the URA-specific design effort. The Duel Panel exists because URA Finale is the only scenario where a single in-run choice (duel stat + option) carries a cap-bump reward, and that choice is opaque without the panel surfacing the Racing Spirit hint tied to each result.

---

## 12. DECISION CONTRACT

Every recommendation the Advisor produces follows this shape — it must not be a bare action label:

1. **Current state and immediate objective.**
2. **Recommended action** and expected benefit (with numbers).
3. **Best alternative** and its trade-off.
4. **Confidence** — verified, derived, configurable, or unknown — with the source badge.
5. **Conditions that would change the recommendation.**

Example:

> **★ RECOMMENDED: TRAIN STA**
>
> You are at 48,000 / 60,000 fans for Valentine's gate, 14 turns out, and Stamina is below the Medium-distance floor (600–700).
>
> - **Train Stamina (Lv 2):** +8 Sta, +4 SP. Facility is 6/8 to Lv 3.
> - **Alternative — race a Tier 2 (Mile):** +4 Skill Points, +fans, but consumes energy and risks mood drop.
> - **Confidence: verified** (facility level by repetition, config `facility_level_source = 'repetition'`).
> - **Switch if:** Summer camp starts in 5 turns (bank energy first), or Happy Meek appears on a Speed tile (cap-bump opportunity).

> **This is the canonical wording of the decision contract across the four scenario documents.**
> The confidence badge above is correct for URA Finale and is the example the other three are
> corrected against: the claim is about a rule the repository actually holds, the badge names the
> rule, and the config key is named so a reader can check it. Re-verified 2026-10-21: the `ura_finale`
> entry's `facility_level_source` in `config/scenarios.php` is `repetition`, so the badge's citation
> is exact for this scenario. The "every 4" step in the same recommendation is a guide figure and
> carries `community`, not `verified`; the recommendation as a whole is `derived` from the rule plus
> the recorded state. Where another document's example cannot be sourced the same way, its badge is
> downgraded rather than this one relaxed.

---

## 13. CAREER MILESTONE TRACKER

URA Finale is a deadline scenario: three goal races with win-or-end stakes, three fan-gate events, and two Summer camps. The cockpit should surface a timeline, not just the current turn.

```
CAREER MILESTONES

UPCOMING
  • Valentine's Day gate    60,000 fans    14 turns    ⚠ 12,000 short
  • Summer camp (Y2)        4 turns        18 turns    Energy: 58 / 60
  • Fan Fest gate           70,000 fans    22 turns    ⚠ 32,000 short
  • URA Qualifier           Win required   36 turns    Speed target: 800+

COMPLETED
  • Junior debut            ✓ (Turn 11)
  • Inspiration 1           ✓ (Career start)
  • Inspiration 2           ✓ (Classic Apr)
  • 3-bar Akikawa friend    ✓ (for Fan Fest)
```

*Illustrative only.* The fan thresholds and the Summer turn count are sourced — 60,000 / 70,000 /
120,000 turf from the "Fans and Skill Points" section of `docs/scenarios/01-ura-finale.md` and from
the `ura_finale` entry's `fan_gates` block in `config/scenarios.php`, and the 4-turn Summer window
from the same guide. The turn offsets, the shortfall figures, the energy readings and the completed
list are a worked example, and the "Speed target: 800+" is the `finale_targets.speed_to_win`
approximation from the same config entry. None of the numbers in the block are recorded values.

The timeline should distinguish **optimal now** (e.g., train Stamina) from **keeping the run viable later** (e.g., bank energy before Summer, hit Valentine's gate for Unique Skill upgrade).

---

## 14. ACCEPTANCE CRITERIA

| Feature                    | Success Criterion                                                                 | Edge Case                              |
| -------------------------- | --------------------------------------------------------------------------------- | ---------------------------------------- |
| Career Cockpit             | Shows stat caps, facility levels, fan gates, and duel count in a single view      | No fans yet (N/A, not 0)                 |
| Happy Meek Duel Panel      | Surfaces the 3 options, their icons, and Racing Spirit hint tied to each outcome  | All 6 duel wins achieved (escalation shown) |
| Career Milestone Tracker   | Surfaces each gate 5+ turns in advance, flags shortfalls                          | Gate already missed (run-at-risk flag)   |
| Stamina floor check        | Flags before any Medium/Long race if Stamina is below the distance floor          | Dirt surface (different floor threshold) |
| Recommendation reconciliation | Predicted outcome vs actual entry updates the run state and flags deviation   | User edits state manually              |

---

## 15. VERIFICATION STATUS

Each feature carries one of three levels — never collapsed:

- **Implemented** — the code exists and the behavior is connected.
- **Verified** — automated tests or reproducible manual checks demonstrate the intended behavior.
- **Game-validated** — calculations have been compared against authoritative mechanics or observed in-game.

A page that renders is not verified. A verified calculation can still rest on an incorrect understanding of the game. This distinction is tracked per feature.

A fourth level is available and is used wherever a feature was not audited: **UNASSESSED — requires code audit**. It is not a synonym for `Implemented`; it records that nobody looked.

### Per-feature status

Audit date: **2026-10-21**, a read of the code and the test files on `master` at `d00a0457`. The audit establishes that a surface exists and that a test pins it. It does not establish that the surface's content matches this plan's intent in every clause, and it compares nothing against an observed client screen.

| Feature | Level | Evidence | What this audit did not confirm |
| ------- | ----- | -------- | ------------------------------ |
| Career Cockpit | `Verified` | `app/Http/Controllers/Career/CockpitController.php` builds the URA scenario section (`milestones`, `duel`, `pre_final`); `tests/Feature/CareerCockpitTest.php`, `tests/Feature/UraPanelTest.php`, `tests/Feature/UraFinaleSurfacesTest.php` and `tests/browser/ura-panel.spec.ts` exercise it. | The acceptance criterion in §14 says the cockpit shows "stat caps, facility levels, fan gates, and duel count in a single view". Stat caps, fan gates and the duel record are confirmed. **Facility levels in the cockpit were not audited in this pass** and are not claimed. |
| Happy Meek Duel Panel | `Verified` | `resources/js/pages/Career/Cockpit/Panels/HappyMeekPanel.vue` with `resources/js/components/scenario/UraDuelPanel.vue`; `tests/Feature/UraFinaleSurfacesTest.php` and `tests/browser/ura-panel.spec.ts`. The three unpublished duel figures stay absent: `duel.stat_gain_values`, `duel.win_chance` and `duel.spark_rate` are `null` in config and are asserted to be. | The criterion's "their icons" is **not** implemented, and by design: the win-chance icon is item 1 of §11 and is `unknown`. The panel draws the option rule, the SP values and the hint, not a probability. |
| Career Milestone Tracker | `Verified` | `resources/js/pages/Career/Cockpit/Panels/CareerMilestonesPanel.vue` with `resources/js/components/scenario/UraMilestoneTracker.vue`; `tests/Feature/UraFinaleSurfacesTest.php` and `tests/browser/ura-panel.spec.ts` (a "Career milestones" region, with `Upcoming` and `Missed` states). | The §14 edge case "gate already missed (run-at-risk flag)" was not audited in this pass; the browser spec's `Missed` state is confirmed, the specific flag is not. |
| Stamina floor check | `Verified` | `app/Http/Controllers/Career/CockpitController.php` builds `stamina_floors` from the `ura_finale` entry's `finale_targets.stamina_by_distance`; `resources/js/pages/Career/Cockpit/Panels/PreFinalReviewPanel.vue` renders it; `tests/Feature/UraFinaleSurfacesTest.php` asserts the four rows. | **The shipped shape is not the shape §14 describes.** The criterion asks for a flag "before any Medium/Long race"; what was built is a four-distance floor table in the pre-final review, and the controller's own note says the four floors "stay a table rather than collapsing to one verdict" and that no single floor applies. |
| Recommendation reconciliation | `Verified` | `app/Services/Advisor/NextDecision.php` carries the `reconciliation` slot — the action the Trainer took against the action the rail recommended — and `tests/Feature/TurnChoiceTest.php` asserts the null, the match and the two notes; `tests/browser/career-cockpit.spec.ts` covers it. | The criterion's "flags deviation" is confirmed at the data level (a `match` boolean and a note). The §14 edge case "user edits state manually" was not audited in this pass. |

**No feature in this document is `Game-validated`.** No URA Finale calculation in this plan was compared against an observed client screen or an authoritative capture, and nothing above should be read as saying so. The sources behind the scenario's mechanics are the reference, the scenario guide and the Game8 page, which are `verified` as *citations* and `community` as *game facts* — a distinction §14's confidence badge is what carries.

**Dated note (2026-10-21).** This table did not exist before this pass: the section defined three levels and assigned none. Every level above was assigned from a named file read on 2026-10-21, and the "not confirmed" column records the gaps rather than smoothing them. A feature whose audit was not performed at all would carry `UNASSESSED — requires code audit`, and no row here carries it, because every row was audited.

---

## 16. IMPLEMENTATION AND TESTING PLAN

| Phase                      | Deliverable                                                                     | Completion evidence                                              |
| -------------------------- | ------------------------------------------------------------------------------- | ---------------------------------------------------------------- |
| 0. Reconnaissance          | Inspect existing scenario code, plans, contracts, routes, data models and tests | Inventory of existing, partial, missing and conflicting behavior |
| 1. Rules audit             | Document verified scenario mechanics and unresolved assumptions                 | Source-backed rules and explicit unknowns                        |
| 2. State model             | Define run state, transitions, persistence and validation                       | Tests for valid and invalid state changes                        |
| 3. Decision engine         | Implement scenario-specific candidate evaluation and ranking                    | Deterministic tests using known inputs and expected outputs      |
| 4. Command center          | Build the primary screen around the next actionable decision                    | UI tests for loading, empty, error, stale and normal states      |
| 5. Reconciliation          | Compare predicted versus actual outcomes                                        | Tests for updating run state without losing history              |
| 6. End-to-end verification | Exercise complete careers and edge cases                                        | Reproducible scenarios, regression results and completion report |

**URA Finale focus:** phase 3 builds the scenario rules layer (repetition-based facility levels, fan gates, Happy Meek duel rewards as verified). Phase 4 builds the Career Cockpit with the Duel Panel. Phase 5 adds reconciliation for duel outcomes (predicted stat gain vs. actual). Phase 6 validates cap-bump behavior against in-game captures.

---

## 17. PRIORITY MATRIX

| Priority | Scope                    | Rationale                                                  |
| -------- | ------------------------ | ---------------------------------------------------------- |
| P0       | Canonical run state, verified mechanics, regression tests | Protects the trustworthiness of the fundamentals scenario    |
| P1       | Next-action recommendations, deadline tracking, duel preview | Turns Cockpit from display into a command center          |
| P2       | Stat-cap ceiling arithmetic, distance-floor optimization | Materially affects finale eligibility                     |
| P3       | Keyboard-friendly controls, richer historical analytics | Convenience only                                           |
