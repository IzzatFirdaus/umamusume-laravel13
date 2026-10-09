# UX walk audit: [Rosy Dreams] Rice Shower, Unity Cup

## Header

| Field                | Value                                                                                                              |
| -------------------- | ------------------------------------------------------------------------------------------------------------------ |
| Audit name           | Rice Shower Unity Cup mid-career UX walk                                                                            |
| Source document      | `docs/[Rosy_Dreams]Rice_Shower_Unity-Cup.md` (67 `[Global]` client frames, 2026-10-03; a mid-career snapshot)        |
| Starting URL         | `http://127.0.0.1:8000/`                                                                                            |
| Date/time of walk    | 2026-10-09, single continuous session                                                                               |
| Environment          | Laravel 13.32.0, PHP 8.5.8, SQLite (`database/database.sqlite`), `php artisan serve` on `127.0.0.1:8000`, Chromium 150 via the browser-use tool |
| Run created          | `training_runs` id **8** (`/training-runs/8`), status Active, scenario Unity Cup                                      |
| Screens visited      | 18 (listed in visit order below)                                                                                    |

**Method statement.** Every screen below was reached in a real browser session and its contents were
read from the rendered DOM at the time of the visit. Every value recorded was typed or selected
through the on-screen controls (native `<select>`, number/text inputs, radio and checkbox controls,
and the app's own buttons). No seeder, no direct database insert or update, no SQL, no tinker, no
API call, and no edit to application state was used to enter data. Screen-to-screen navigation from
the run record onward used the link targets the UI itself exposes (the same routes the on-screen
links point at); the data-entry controls were always operated directly. Source code was read only to
explain the one server error observed (screen 15).

**One pass.** The walk was made once, forward. No screen was re-entered to change an earlier choice,
and no screen was skipped. Where the UI required a value the source document does not carry, the
minimum available choice was made and is flagged below as **Not present in source — UI-required
choice**. The run was never marked Completed, never filed as a Veteran, and never filed as a Legacy.

**Nothing was fixed.** No route, controller, view, component, migration, model, seeder, test,
validation rule, label, field, or schema was changed. `git status` after the walk shows the same
pre-existing uncommitted files that were present before it began (a shared working tree carrying
other work); this audit added one file, `docs/audits/rice-shower-unity-cup-ux-walk.md`, and nothing
else.

---

## Screen 1 — Dashboard

### Route and trigger
- Route: `/` (`home`), title "Dashboard".
- Trigger: initial navigation to the starting URL.

### What the screen asks for
Read-only. Regions in order: nav rail (Dashboard, New Career, Careers, Legacy Lab, Veterans, Support
Cards, Skills, Review, Database, Settings); ACTIVE CAREER (empty state + "Start a new training run");
Quick actions (New Career, Legacy Lab, Support Cards); RECENT VETERANS (3 of 7); RECENT BUILDS;
DATA STATUS (Global data, "Verified 2026-09-27", Ruleset `N/A`, Trainees 67, Skills 1,910, Support
cards 559). The only control used was the "Start a new training run" link.

### Values entered + source trace
None entered on this screen.

### Source data with no UI home
None relevant on this screen.

### Friction / errors / dead ends
- RECENT VETERANS lists 7 rows produced by earlier work in this shared tree, none of them this run.
  Not a defect, but it means the Dashboard's "no active career" state sat beside 7 existing careers,
  so the landing state is not a clean first-run state on this database.
- `Ruleset: N/A` and `Ruleset snapshot: N/A` recur on nearly every later screen. The app states why
  in the cockpit ("No source defines a Global ruleset version, so this run stores none to print"), so
  this is an intentional `N/A`, not a gap.

### Outcome / next screen
Clicked "Start a new training run" → `/career/setup/scenario`.

---

## Screen 2 — Scenario setup (wizard step 1 of 6)

### Route and trigger
- Route: `/career/setup/scenario`, title "Choose a scenario".
- Trigger: clicked the Dashboard's "Start a new training run".

### What the screen asks for
A step nav (Leave setup; Scenario, Trainee, Your target, Legacy, Support deck, Preflight) and four
scenario cards. Each card prints eight facts (Optimizes, Systems, Tracked resources, Turn loop, Live
on Global, Ruleset, Description, Recommended use) and one `Select Scenario` button. A "Stored choice"
readout sits above the cards. There is no Continue button: forward movement is the step nav.

### Values entered + source trace
- `Unity Cup` → the Unity Cup card's `Select Scenario` button → source: `[Rosy_Dreams]Rice_Shower_Unity-Cup.md` §1.1, line 19 ("Scenario Unity Cup, confirmed by the logo panel").

### Source data with no UI home
- Nothing from §1.1 is asked for here beyond the scenario name; the run's identity (trainee, year,
  month, turns remaining) has no home on this screen.

### Friction / errors / dead ends
- On all four cards, `Ruleset`, `Description` and `Recommended use` render `N/A`. Three of the eight
  facts per card are therefore empty for every scenario, which makes the comparison cards mostly
  scaffolding. (Consistent with the repo's "unrecorded renders as N/A" rule, but the effect on this
  screen is that two thirds of the decision surface is blank.)
- After clicking, the card flashed "Saving your choice…" then "Scenario set.", the button became
  `pressed="true"` with a "Selected" label, and `Stored choice:` changed from `N/A` to `Unity Cup`.

### Outcome / next screen
Clicked the step-nav "Trainee" link → `/career/setup/trainee`.

---

## Screen 3 — Trainee (wizard step 2 of 6)

### Route and trigger
- Route: `/career/setup/trainee`, title "Choose a trainee".
- Trigger: clicked "Trainee" in the wizard step nav.

### What the screen asks for
Filters in order: `Search` (text), `Surface` (select: Any,S..G), `Distance` (select: Any,S..G),
`Running style` (select: Any,S..G), `Unique skill` (select, ~130 options), `Sort by` (select: Name,
Turf, Dirt, Sprint, Mile, Medium, Long, Front runner, Pace chaser, Late surger, End closer), `Order`
(select: Ascending/Descending), `Filter` (submit), `Clear filters`. Then a paginated roster of 67
trainee cards (3 pages). Each card carries: portrait, English name, Japanese name, "Released
(Global)", ten aptitude chips (Turf, Dirt, Sprint, Mile, Medium, Long, Front runner, Pace chaser,
Late surger, End closer — each with a letter and a word), a costume-form summary, a `Select Trainee`
button, and a `View Profile` link.

### Values entered + source trace
- `Rice Shower` → the `Search` field → source: `[Rosy_Dreams]Rice_Shower_Unity-Cup.md` §1.1, line 20 ("Trainee Rice Shower, `[Rosy Dreams]` card, ★3, Potential Lvl 2").
- `Select Trainee` on the Rice Shower card (`id="trainee-30"`) → source: same line 20.

### Source data with no UI home
- **★3 and Potential Lvl 2 (§1.1 line 20)**: the card carries no star rating and no Potential Level
  field anywhere on this screen. There is no control that would accept either.
- **[Rosy Dreams] — the costume**: the card prints "2 costume forms" and two badges, "★★★SSR debut
  form" and a second "★★★SSR" with **no name at all**. The portrait rendered is
  `/artwork/card_portrait/103001`, the debut form. Nothing on the screen lets the user say which
  costume form the career uses; the run's own form is the unlabelled one.

### Friction / errors / dead ends
- The aptitude row the UI printed for Rice Shower is **Turf A, Dirt G, Sprint E, Mile C, Medium A,
  Long A, Front runner B, Pace chaser A, Late surger C, End closer G**. The source document records
  **Mile B, Front runner A, Late surger B** for the same trainee (§1.1, lines 58-59, read in-client
  from frame `1dde6b2f`). Three of ten aptitude letters differ. Recorded as observed; the audit does
  not decide which side is right.
- After `Select Trainee`, the screen flashed "Trainee set: Rice Shower." but the `Stored choice:`
  readout on the same screen still read **`N/A`**, and the search filter cleared (the list returned
  to all 67). So the step's own confirmation of what it stored did not update.

### Outcome / next screen
Clicked "Your target" in the step nav → `/career/setup/target`.

---

## Screen 4 — Your target (wizard step 3 of 6)

### Route and trigger
- Route: `/career/setup/target`, title "Your target".
- Trigger: clicked "Your target" in the step nav.

### What the screen asks for
- `Purpose` (select: Not chosen yet, Story Clear, Champions Meeting, Parent Farming, Skill Farming),
  with a note that a fifth purpose, Competitive Build, is not recorded.
- `Distance` (select: Not chosen yet, Sprint, Mile, Medium, Long).
- `Surface` (select: Not chosen yet, Turf, Dirt).
- `Style` (select: Not chosen yet, Front Runner, Pace Chaser, Late Surger, End Closer).
- `Stat targets`: five number inputs (Speed/Stamina/Power/Guts cap 1300, Wit cap 1800), each with a
  `Cap` label and a ratio readout.
- `Skill priorities`: a free-text `Skill name` + `Add`, an ordered list, with a note that risk
  tolerance and per-skill marks are not recorded.
- A live `Summary` region and a `Save target` button.

### Values entered + source trace
| Value                    | Field          | Source                                                                                                                                  |
| ------------------------ | -------------- | --------------------------------------------------------------------------------------------------------------------------------------- |
| `Story Clear`            | Purpose        | **Not present in source — UI-required choice.** The snapshot records no build purpose; the field is required.                            |
| `Long`                   | Distance       | Partly sourced: §1.1 line 59 "A Medium/Long build"; §3 line 461 names Arima Kinen at 2500 m. **Single-select choice not in source** (Medium and Long are both A). |
| `Turf`                   | Surface        | §1.1 line 58 ("Turf A"); §1.6 lines 186-187 (both race-day options are Turf/Dirt, her races Turf).                                        |
| `Pace Chaser`            | Style          | §1.1 line 59 ("the pace half available", "Pace A"); §1.3 line 66 (Pace Chaser Straightaways ○ owned).                                     |
| `1300, 1300, 1300, 1300, 1800` | Stat targets | **Not present in source — UI-required choice.** These are the form's own printed caps. See the friction note below.                      |

### Source data with no UI home
- **The run's own caps (§1.2, lines 28-32): Speed 1325, Stamina 1322, Power 1368, Guts 1308, Wit
  1800.** The screen prints the scenario's base caps (1300/1300/1300/1300/1800) and its inputs are
  `max=1300` / `max=1800`. Three of the run's four documented caps (1325, 1322, 1368) **exceed the
  form's maximum and cannot be entered at all**. §1.2's whole cap-arithmetic discussion has no
  representation here.
- **The cap arithmetic's inputs (§1.2 lines 39-56): the six blue sparks and any support-card Max-Stat
  layer.** This screen asks for a *target*, not a cap composition. It asks for no spark and no
  support-card cap contribution, so the question §1.2 raises ("what raises the other three extras")
  is not asked anywhere in the wizard.
- **Potential Level 2 and the growth row +0/+10/+0/+20/+0 (§1.1 line 20, §7 item 2):** no field.
- **Fans 209,245 / class Star / the 30,755-fan gap (§1.1 lines 21-22):** no field.

### Friction / errors / dead ends
- Submitting with everything empty produced four `role="alert"` errors ("The purpose field is
  required." etc.) **and five more, one under each stat**: "Enter a target for every stat, or leave
  the whole target unset." — even though every stat field was empty, i.e. the state that message says
  is acceptable. Submitting again with the four selects filled and the stats still empty left the
  five stat errors in place. So the "leave the whole target unset" branch is unreachable: the target
  is never wholly unset, because four of its fields are required. Entering all five stats cleared
  them. (Recorded as observed; no code was changed.)
- Because the stats could not be left empty, the five numbers entered are the form's own caps and are
  not sourced from the run. On this screen, the run's documented caps are both unenterable and not
  what the field asks for.
- The screen's own copy says "the cap is the server's own ceiling for the chosen scenario", which is
  true; but the run's real caps sit above that ceiling, so the tool's ceiling is lower than the run's.

### Outcome / next screen
Clicked `Save target` → "Build target saved.", summary updated to "Purpose Story Clear; distance
Long; surface Turf; style Pace Chaser; Speed 1300, Stamina 1300, Power 1300, Guts 1300, Wit 1800;
skills in order: none listed yet." Then clicked "Legacy" in the step nav → `/career/setup/legacy`.

---

## Screen 5 — Legacy (wizard step 4 of 6)

### Route and trigger
- Route: `/career/setup/legacy`, title "Legacy".
- Trigger: clicked "Legacy" in the step nav.

### What the screen asks for
The screen states it records and compares but computes nothing. Two parent panels (PARENT A, PARENT
B), each with:
- a readout of the chosen parent's own rank and spark chance;
- `Assign to Parent N` (select, listing the saved Veteran library: Not chosen, Vodka, Vodka, Daiwa
  Scarlet, Haru Urara, Mayano Top Gun, Maruzensky, Maruzensky);
- `Grandparent N1` and `Grandparent N2` (type-ahead comboboxes, free text);
- an "AS YOU READ HER" block: `Own rank` (number), `Rented for the run` (checkbox), and a `SPARKS`
  list with `Add Spark to Parent N` / `Remove Spark from Parent N`;
- each spark row carries `Kind` (select: Blue, Pink, Green, White, Scenario), `Applies to` (free
  text), `Stars` (number, min 1 max 3).
Then `Affinity for the pair` (select: Not recorded, △, ○, ◎), `Save Legacy`, `Next: Support deck`.

### Values entered + source trace
| Value                                  | Field                             | Source                                                                                                     |
| -------------------------------------- | --------------------------------- | ---------------------------------------------------------------------------------------------------------- |
| `Vodka`                                | Assign to Parent A                | §1.5 line 138 ("Legacy 1  Vodka [Wild Top Gear]  rank B+")                                                  |
| `Daiwa Scarlet` / `Seiun Sky`          | Grandparent A1 / A2               | §1.5 lines 139-140                                                                                          |
| Blue / `Power` / ★1                    | Spark 1 on Parent A               | §1.5 line 148 (Vodka Blue: "Power ★☆☆")                                                                     |
| Pink / `Late Surger` / ★2              | Spark 2 on Parent A               | §1.5 line 148 (Vodka Pink: "Late Surger ★★☆")                                                               |
| Green / `Cut and Drive!` / ★2          | Spark 3 on Parent A               | §1.5 line 148 (Vodka Green unique: "Cut and Drive! ★★☆")                                                    |
| `Maruzensky`                           | Assign to Parent B                | §1.5 line 141 ("Legacy 2  Maruzensky [Formula R]  rank UG, flagged Guest")                                  |
| `Daiwa Scarlet` / `Taiki Shuttle`      | Grandparent B1 / B2               | §1.5 lines 142-143                                                                                          |
| `Rented for the run` = checked         | Parent B                          | §1.5 line 141 ("flagged Guest") — the UI then prints "Rented from a friend"                                  |
| Blue / `Speed` / ★2                    | Spark 1 on Parent B               | §1.5 line 151 (Maruzensky Blue: "Speed ★★☆")                                                                |
| Pink / `Turf` / ★2                     | Spark 2 on Parent B               | §1.5 line 151 (Maruzensky Pink: "Turf ★★☆")                                                                 |
| Green / `Red Shift/LP1211-M` / ★1      | Spark 3 on Parent B               | §1.5 line 151 (Maruzensky Green unique: "Red Shift/LP1211-M ★☆☆")                                           |
| (left unset)                           | Affinity for the pair             | **Not present in source.** §1.5 states no pair affinity grade, so it stayed "Not recorded" and rendered `N/A`. |

### Source data with no UI home
- **The two parents' end-of-run ranks, B+, A, UG (§1.5 lines 138-143).** The only rank field is
  `Own rank`, a number input whose helper text says it is the "Legacy's own star count". A letter
  class (B+, A, UG) has no field. It was left blank, so the Preflight summary printed `Rank N/A` for
  both parents.
- **The four grandparents' sparks (§1.5 lines 149, 150, 152, 153).** Daiwa Scarlet (Legacy 1 side),
  Seiun Sky, Daiwa Scarlet (Legacy 2 side) and Taiki Shuttle each carry a full Blue/Pink/Green/White
  spark set in the source. **The screen has a SPARKS block for PARENT A and PARENT B only** — the four
  grandparent fields are name-only. Four of the tree's six spark sets have no UI home. In particular
  the source's "six blue sparks" (§1.5 line 159) can only be two here.
- **The white sparks.** Not entered (see the completion note); they have a home (`Kind = White`) but
  the mechanism is identical to the coloured sparks that were entered.
- **The two parents' epithets, `[Wild Top Gear]` and `[Formula R]` (§1.5 lines 138, 141).** The
  `Assign to Parent` options read as bare names; there is no epithet field and no place to type one.
- **The ancestor's own star count / the "no ceiling" note** is offered as `Own rank`, but the source
  gives no star count for any ancestor, so it stayed blank.

### Friction / errors / dead ends
- The `Assign to Parent A` list contains **Vodka twice and Maruzensky twice** with no
  disambiguating detail (no epithet, no rank, no id). The two entries are visually identical; the
  user cannot tell which saved record is which.
- Spark rows: clicking `Add Spark to Parent A` appends a **blank trailing row** (Kind Blue, no
  `Applies to`, no `Stars`). That blank row is not a hint — after `Save Legacy` the Preflight summary
  listed Parent A's sparks as "Blue on Power★1 / Pink on Late Surger★2 / Green on Cut and Drive!★2 /
  **Blue**", i.e. **the empty trailing row was stored as a real spark** with no stat and no stars.
  This is observable on the Preflight screen (screen 7) and was not corrected by the app.
- The `Applies to` field accepts three different kinds of value (a stat name, an aptitude name, a
  skill name) with no list and no validation; the only clue is the spark's `Kind`.
- `Rented for the run` is the only representation of the source's "flagged Guest" parent; it renders
  as "Rented from a friend", which is a different phrase but the right idea.
- After `Save Legacy` the flash read "Legacy recorded." and the blank trailing spark row was still
  present in the editor.

### Outcome / next screen
Clicked `Next: Support deck` → `/career/setup/deck`.

---

## Screen 6 — Support deck (wizard step 5 of 6)

### Route and trigger
- Route: `/career/setup/deck`, title "Support deck".
- Trigger: clicked "Next: Support deck" on the Legacy screen.

### What the screen asks for
Six slots (Slot 1..5, and `Slot 6 · Friends`, labelled "Friend slot"). Each slot carries the equipped
card or an empty state, an `Ownership` control (`Owned` / `Rented`, a two-button toggle starting at
`N/A`), and `Replace the card in Slot N`. Below: `Save deck`, a `Choose a card` picker with a `Type`
filter (All, Speed, Stamina, Power, Guts, Wit, Pal, Group), a `Name contains` field, a `Filter`
button, a `Card for Slot N` select listing every catalog card, and `Equip owned to Slot N` / `Equip
rented to Slot N` / `Set Slot N to not equipped`. A `Deck analysis` region fills in as cards land.

### Values entered + source trace
| Value                                            | Field                             | Source                                                                        |
| ------------------------------------------------ | --------------------------------- | ----------------------------------------------------------------------------- |
| `Tokai Teio [Dream Big!] · SSR Speed` / Owned     | Slot 1                            | §1.4 line 79 (deck table row 1)                                                |
| `Nishino Flower [Even the Littlest Bud] · SSR Speed` / Owned | Slot 2                 | §1.4 line 80                                                                   |
| `Biko Pegasus [Double Carrot Punch!] · SSR Speed` / Owned | Slot 3                    | §1.4 line 81                                                                   |
| `Matikanetannhauser [Just Keep Going] · SSR Guts` / Owned | Slot 4                    | §1.4 line 83                                                                   |
| `Haru Urara [Urara's Day Off!] · SSR Guts` / Owned | Slot 5                           | §1.4 line 84                                                                   |
| `Super Creek [Piece of Mind] · SSR Stamina` / **Rented** | Slot 6 · Friends          | §1.4 lines 82 and 86-88 (the friend card is Super Creek)                       |

Slot numbering for the five owned cards is **Not present in source — UI-required choice**: §1.4 gives
the deck as a list, not a slot order. The friend card is placed in Slot 6 because the source names it
the friend card and the UI names Slot 6 the friend slot.

### Source data with no UI home
- **Card levels and limit breaks (§1.4 lines 77-84, the "Client level" column: 35/35, 30/30, 30/30,
  50/50, 30/30, 35/35).** No field on any deck surface accepts a card level.
- **The per-card "Client rows visible" values (§1.4 lines 97-104)** — the effects this run has
  actually reached. The Deck analysis explicitly uses catalog maxima instead ("Every figure is an
  effect's highest stated anchor … never an interpolation for a level this deck holds"), so the
  run's reached values have no home.
- **The sixth slot's "Friends" pill origin (§1.4 line 86, §7 item 8):** the source derives the
  friend card from the Career Profile rail. Here the role is attached to the *position*, which is a
  reasonable mapping, but the fact that Super Creek is borrowed from another account survives only as
  the `Rented` toggle, and (see screen 18) that toggle is not persisted.

### Friction / errors / dead ends
- The screen's own copy warns: "The owned or rented flag is kept in this setup draft. The deck table
  has no column for it yet, so Preflight writes the six cards it can store and the flag stays here
  until one exists." The rented flag is therefore knowingly non-persistent, and screen 18 confirms it
  is lost.
- The picker defaults to Slot 1 and its target only changes via the per-slot `Replace the card in
  Slot N` button; the button that equips keeps the slot name in its label ("Equip owned to Slot 4"),
  which is the only cue that the target moved.
- Deck analysis states "Friendship Bonus … The export marks this effect as multiplicative, so the
  values above are listed separately rather than added", while Mood Effect, Specialty Priority,
  Training Effectiveness, Race Bonus, Fan Bonus etc. are summed. Correct, but two adjacent blocks
  treat their numbers differently without a visual separator beyond the note.
- The six card pickers render fine and the analysis block is populated only after a card lands
  ("Nothing to analyse yet" beforehand), which matches its own copy.

### Outcome / next screen
Clicked `Save deck` → "Deck saved." (URL gained `?deck_slot=6`). Then clicked "Next: Preflight" →
`/career/setup/preflight`.

---

## Screen 7 — Preflight (wizard step 6 of 6)

### Route and trigger
- Route: `/career/setup/preflight`, title "Career contract".
- Trigger: clicked "Next: Preflight" on the deck screen.

### What the screen asks for
Read-only summary, then the wizard's one write. It prints: a warning line ("No warnings. Every
section is filled…"), Build (trainee, scenario, target, ancestry with parents, grandparents and
sparks), Support deck (six slots with type and ownership), a full Deck analysis, Target (five stat
values, race profile, skill priorities, ruleset snapshot), and four controls: `Back`, `Edit Legacy`,
`Edit Deck`, `Edit Target`, and `Start Career`.

### Values entered + source trace
Only the final action: clicked `Start Career` (no value). Every value shown is traced above.

### Source data with no UI home
- The ancestry block confirms the gap found on screen 5: **four grandparents appear as names only,
  with no sparks**, and the parents' ranks read `N/A`. The four grandparent spark sets from §1.5
  (lines 149, 150, 152, 153) are absent from the contract.

### Friction / errors / dead ends
- The summary's Parent A spark line reads "Blue on Power★1 / Pink on Late Surger★2 / Green on Cut and
  Drive!★2 / **Blue**". The trailing bare "Blue" is the blank spark row from screen 5, now part of the
  contract. A user reading this screen would see a fourth spark that was never intended.
- "Affinity N/A" is correct (the source gives no grade) but appears with no explanation on this
  screen.
- "Skill priorities: None entered" and "Ruleset snapshot: N/A" both read as gaps, though both are
  honest states.

### Outcome / next screen
Clicked `Start Career` → "Run created." → `/training-runs/8/cockpit`.

---

## Screen 8 — Career Cockpit

### Route and trigger
- Route: `/training-runs/8/cockpit`, title "Career: Rice Shower".
- Trigger: clicked `Start Career` on Preflight (the wizard's one write).

### What the screen asks for
Read-only. Run record (scenario, YEAR, MONTH, TURN, Team Rank, Spirit Bursts); Stats and state (five
stats against caps, Energy, Mood, Fans, Skill Points); Advisor (recommendation + why); an empty-state
block; Actions (Training, Race, Rest, Recreation, Scenario action, Event, Inheritance); a Scenario
block (Unity Cup, team race circles, the rank ladder G..S+, team stat grades, Spirit Burst states,
Current Spirit, the burst-count band table, Special Training notes); Races in this run (recorded,
this turn, still to come); and links to the Career Timeline and Career Result.

### Values entered + source trace
None entered. (The screen was re-read later — see the persistence note under screen 9.)

### Source data with no UI home
- **Fans class and the fan ladder (§1.1 lines 21-22).** Fans is a free number; the class ("Star"),
  the Top Star threshold (240,000) and the 30,755-fan gap have no field.
- **Team identity (§1.6 lines 169-172): team name "Blue Bloom", motto "Dreaming Big", league
  placement 8th, four preseason rounds won, finals pending.** The cockpit's Scenario block has
  Team Rank and Spirit Bursts readouts but no team name, no motto, no league placement, and no
  per-round win record. Only the *count of circles* is entered, and only on the race form.
- **Team stat grades A/A/A/S/A and the +30 ranking bonus (§1.8 lines 232-234).** The cockpit prints a
  five-stat grade row (Speed..Wit, all `N/A`), but there is no field for the bonus.
- **The Unity Trainings count (55) and the 6 / 5 burst split (§1.8 lines 235-237).** The cockpit shows
  "Spirit Bursts N/A" and its own copy says bursts are "recorded as states, not counters (D-223)".
  The source's two numbers (6 normal, 5 Extreme) and the 55 Unity Trainings cannot be entered; the
  band table consequently cannot place the run in its 10-12 band even though §1.8 derives it.
- **The run's YEAR/MONTH/turns-remaining.** The cockpit derives YEAR and MONTH from the turn number
  (see friction).

### Friction / errors / dead ends
- On first arrival everything is `N/A` and the Advisor declines ("Recommendation unavailable because
  Energy has not been entered"). That is correct behaviour and is well explained.
- **The mid-career position has no representation.** The cockpit's YEAR/MONTH/TURN come out of the
  turn number; with no turn logged it printed `YEAR N/A / MONTH N/A / TURN 0`. The source's "Senior
  Year Early Oct, 5 turns left, 5 turns until the Unity Cup" (§1.1 line 21) has no field anywhere.
- The burst-count band table carries a visible caveat badge and a note that the run's own position in
  the bands is not shown. Good disclosure, but it means the source's §2.4/§8.2 conclusion ("11
  combined is inside the 10-12 gold band") cannot be expressed in the tool.

### Outcome / next screen
Followed the cockpit's own prompt, "Record the first turn on the run screen" → `/training-runs/8`.

---

## Screen 9 — Run record (turn logger)

### Route and trigger
- Route: `/training-runs/8`, title "Run: Rice Shower".
- Trigger: clicked "Record the first turn on the run screen" in the cockpit's empty state.

### What the screen asks for
A very large screen. In order: header links (Career Cockpit, Skills planner, Career result, Export
CSV, Export JSON); a Resources strip; a three-year race calendar with per-turn race names; a race
record form (`race_catalog_slot_id`, `status` Not/Offered/Skipped/Entered/Completed, `placement`,
`circles`, `Record race`); a scenario select + `Change scenario`; a status select (Active/Completed/
Retired) + `Change status`; six deck slots (`deck[N][support_card_id]`) + `Save deck`; the Turns
table; and the turn form — "Step 2 of 5 · Choose activity" with seven numbered activities (Speed,
Stamina, Power, Guts, Wit, Rest, Recreation), then `Turn`, `Speed`, `Stamina`, `Power`, `Guts`, `Wit`,
`Skill Points`, `Energy (after this turn)`, `Fans (after this turn)`, `Mood`, `Outcome`, `Penalty
kind`, `Preview this turn`; a `Correct a turn by hand` disclosure; and a Skills block (Starting /
Acquired / Skipped, plus a `Skill` + status + turn form).

### Values entered + source trace
| Value        | Field                       | Source                                                                                                 |
| ------------ | --------------------------- | ------------------------------------------------------------------------------------------------------ |
| `1` (default, unchanged) | Turn            | **Not present in source — UI-required choice.** §1.1 line 21 gives a position ("Senior Year Early Oct, 5 turns left") but no turn number, and the field is required with a default of 1. |
| Speed        | Choose activity (radio)     | §1.7 line 220 (the last observed training turn is the Speed tile). **Activity is not stated as a turn record in the source; UI-required choice.** |
| `607`        | Speed                       | §1.2 line 28                                                                                            |
| `577`        | Stamina                     | §1.2 line 29                                                                                            |
| `549`        | Power                       | §1.2 line 30                                                                                            |
| `736`        | Guts                        | §1.2 line 31                                                                                            |
| `397`        | Wit                         | §1.2 line 32                                                                                            |
| `173`        | Skill Points                | §1.2 line 34 ("Skill Points: 173 unspent")                                                               |
| `209245`     | Fans (after this turn)      | §1.1 line 22 ("Fans 209,245")                                                                           |
| `GREAT ↑`    | Mood                        | §1.7 line 208 ("Mood is GREAT")                                                                          |
| Success      | Outcome (default)           | Default. §1.7 line 220 records the tile's 39% failure rate but no resolved outcome; left at the default. |
| (left empty) | Energy (after this turn)    | **No value exists in the source.** §1.7 lines 213-216: "The client never prints an Energy number", the bar is "near a third". Left blank rather than invented. |
| (left empty) | Penalty kind                | Not applicable with a Success outcome.                                                                   |

### Source data with no UI home
- **Energy (§1.7 lines 213-218).** No number exists in the source ("The client never prints an
  Energy number"; the bar sits "near a third"). The field is a number, so the snapshot's energy state
  has no representation at all. Leaving it blank leaves the run's Energy `N/A` on every screen, which
  in turn disables the Advisor everywhere.
- **The run's position in the career (§1.1 line 21).** `Turn` is a bare integer; "Senior Year Early
  Oct", "5 turns left" and "5 turns until the Unity Cup" have no field. The calendar renders all three
  years, but nothing records which one the run is in except the derivation from the turn number.
- **The last observed turn (§1.7 lines 220-222).** Speed facility Lv4, the "Floor Cleaning" banner,
  three card avatars, the preview "+18 Speed / +8 Power / +6 Skill Points", **Failure 39%**, and the
  five facility levels (Speed 4, Stamina 4, Power 4, Guts 5, Wit 4). There is no per-turn failure
  rate, no facility level and no preview figure anywhere on the form.
- **The unspent SP's composition (§1.3, §2.2).** SP is one number. The ten-plus owned skills, the
  purchasable pool with its prices, and the hint discounts have no home on the turn form.
- **The Unity Cup state (§1.6, §1.8).** Team name, motto, league placement, the four rounds won, the
  55 Unity Trainings and the 6 + 5 burst split. The only nearby control is the race form's `circles`,
  which is per-race.

### Friction / errors / dead ends
- The turn form's required fields are `Turn`, the five stats and `Outcome`; `Energy`, `Fans`,
  `Skill Points` and `Mood` are optional. Because the source cannot supply Energy, the Advisor stays
  disabled on this and every later screen: the app's own recommendation engine is unusable for a run
  whose energy the source cannot state.
- The form is a small wizard inside the page. It announced "Step 2 of 5 · Choose activity", then
  after `Preview this turn` it announced "Step 4 of 5 · Record outcome". No step 3 label was ever
  shown.
- `Preview this turn` does not write; it added a `Confirm turn` button, and only `Confirm turn`
  persisted. After confirming, the Turns table read
  `1 | 607 | 577 | 549 | 736 | 397 | 173 | (blank) | GREAT ↑`. The table's columns are Turn, Speed,
  Stamina, Power, Guts, Wit, SP, Condition, Mood and actions — **there is no Fans column and no
  Energy column**, so two of the values the form accepted are not shown back on this screen.
- **The mid-career position cannot be expressed.** Re-reading the cockpit after the turn shows the
  derivation: `YEAR Junior Year`, `MONTH Early January`, `TURN 1`. The source's snapshot is Senior
  Early October. Entering the snapshot's values at the form's default turn number places a
  Senior-year state at the career's first turn, and no control on the screen would have let the user
  place it anywhere else.

### Outcome / next screen
After `Confirm turn`, clicked "Skills planner" in the screen header → `/training-runs/8/skills`.

---

## Screen 10 — Skills planner

### Route and trigger
- Route: `/training-runs/8/skills`, title "Skills planner: Rice Shower".
- Trigger: clicked "Skills planner" in the run record screen's header.

### What the screen asks for
Read-only. A "Your target" block (purpose, distance, surface, style and the five stat targets), a
"Skill Point coverage" line, the hint-discount ladder (Hint Lvl 1 10% off … Lvl Max 40% off), and a
legend of five skill states (Confirmed, Required, Available, Learned, Inherited).

### Values entered + source trace
None entered.

### Source data with no UI home
- **The ten-plus owned skills and Corner Recovery ○ (§1.3 lines 63-67).** The planner prints "No skill
  is learned yet" and "No skill is marked for this run yet"; §1.3's owned list has no entry point on
  this screen.
- **The Ignited Spirit hints and the Burning Spirit gold (§2.2 lines 363-373, §2.4).** The screen has
  no scenario-skill row.
- **The recommended spend (§2.3).** No advice surface exists here.

### Friction / errors / dead ends
- The screen reads back "the run holds 173 SP", so the turn entry persisted.
- Coverage is stated as "The skills still to learn cost N/A SP", so the headline number of the screen
  cannot be computed even though the run's own SP is known. A user gets a coverage panel that can
  never resolve.
- "No column holds the skills a Legacy configuration passes down" — the screen explains its own gap,
  but it means the ancestry recorded on screen 5 contributes nothing here.

### Outcome / next screen
Navigated back to the run record, then to the cockpit, then followed the cockpit's "Training" action.

---

## Screen 11 — Training decision

### Route and trigger
- Route: `/training-runs/8/training`, title "Training decision: Rice Shower".
- Trigger: the cockpit's "Training" action link.

### What the screen asks for
Five training cards (Speed, Stamina, Power, Guts, Wit). Each carries a deficit line, `EXPECTED GAINS`,
`ENERGY COST`, `SUPPORTS ENTERED FOR THIS TRAINING`, `TARGET DEFICIT`, `RISK`, a `Train` button and an
`Inspect details` disclosure. Below them, "Record the turn" with `Turn *`, `Speed total *`,
`Stamina total *`, `Power total *`, `Guts total *`, `Wit total *`, `Skill Points`, `Energy`, `Fans`,
`Mood`, `Outcome *`, and `Preview this turn`.

### Values entered + source trace
None entered on this screen.

### Source data with no UI home
- The source's tile preview and failure rate (§1.7 lines 220-222) have no field, as on screen 9.
- The source's facility levels (Speed 4, Stamina 4, Power 4, Guts 5, Wit 4) have no field; the app
  says per-facility levels "are not recorded here".

### Friction / errors / dead ends
- The screen's computed deficits match the entered target against the entered stats exactly:
  Speed 693 (1300−607), Stamina 723, Power 751, Guts 564, Wit 1,403. The arithmetic is right, and it
  is right against a target the source does not state.
- Supports-per-training counts match the deck and the source: Speed 3, Stamina 1, Guts 2, Power 0,
  Wit 0 (§1.4 line 86: "Three Speed, one Stamina, two Guts, no Wit, no Pal").
- `EXPECTED GAINS` and `RISK` are `N/A` on every card, with sourced reasons (ADR-0001 §3). Honest, but
  it leaves each card with one usable number (the deficit) out of five.
- This screen repeats the run record's turn form with different labels (`Speed` vs `Speed total *`),
  and its own copy says the preview "renders on the run record screen … (Rice Shower, turn 2)". A user
  meets the same form twice and has to work out which one to submit.
- The screen names the next turn as 2, i.e. it has already advanced past the turn that was logged.

### Outcome / next screen
Returned to the cockpit and followed its "Race" action.

---

## Screen 12 — Race decision

### Route and trigger
- Route: `/training-runs/8/races`, title "Race decision: Rice Shower".
- Trigger: the cockpit's "Race" action link.

### What the screen asks for
"The turn being decided" (printed as `Junior Year · Late January · Turn 2`); Mandatory races (Junior
Make Debut, Junior turn 12, flagged NEXT, "Not recorded yet"; URA Finals Qualifier / Semifinal /
Final (Aoharu) in Finale, all "Not recorded yet"); a "Next obligation" line; Readiness (`N/A`); and
"Races to decide between" with a `Plan the whole calendar` link.

### Values entered + source trace
None entered.

### Source data with no UI home
- **The race-day card (§1.6 lines 181-193):** Kyoto Daishoten (G2, Kyoto Turf 2400 m, Soft) and Mile
  Championship Nambu Hai (G1, Morioka Dirt 1600 m), the 6,700-fan payout, the "2,000 fans or more"
  entry criteria, and the client's "top contender" verdict. Because the run sits at turn 1/2, the
  screen reports "No race on this scenario's calendar falls at this turn", so none of it is reachable.
- **The race calendar's Senior board (§1.6 lines 178-179):** the scheduled Kyoto Daishoten and the
  Arima Kinen goal slot. Not reachable from this turn.

### Friction / errors / dead ends
- This screen is the clearest demonstration of the mid-career mismatch. The app can only decide a race
  at the turn it is at; the turn is 1/2 (Junior January), while the source's turn is Senior Early
  October. The screen has no way to jump to the run's real turn.
- The mandatory-race list is the only populated race content, and it is the *scenario's* obligation
  list, not the run's own four won rounds (§1.6 line 170).

### Outcome / next screen
Clicked `Plan the whole calendar` → `/training-runs/8/races/planner`.

---

## Screen 13 — Race planner

### Route and trigger
- Route: `/training-runs/8/races/planner`, title "Race planner: Rice Shower".
- Trigger: clicked "Plan the whole calendar" on the Race decision screen.

### What the screen asks for
Read-only. "Planning from Junior Year · Turn 2"; Held figures (Win probability `N/A`, Expected risk
`N/A`); Deadlines; Mandatory races, each with a per-race detail panel (Grade, Distance, Distance
band, Surface, Running style, Fan gain, Reward, Skill points, Scenario reward, Estimated win
probability, "Details and actions"); and Upcoming races (the calendar's non-mandatory races).

### Values entered + source trace
None entered.

### Source data with no UI home
- The source's Soft going and per-race fan rewards (§1.6 lines 185-191) have no column; the Fan gain
  row explains that the payout curve id is stored but no payout table is.
- The Arima Kinen goal (§1.6 line 176) is not in this list; goal races are `N/A` (KI-34).

### Friction / errors / dead ends
- The detail panels are mostly `N/A` with a reason each. Junior Make Debut and Junior Maiden Race
  carry no distance, distance band or surface; Chukyo Junior Stakes does (1,600 m, Mile, Turf, "Fan
  gate: 350 fans"). The inconsistency is in the catalog, and the screen discloses it per field.
- Win probability is withheld on purpose (ADR-0016 / `PRD.md` §6.11), stated on every panel.
- The screen is the longest in the walk (16,001 characters of text) and repeats the same eleven-field
  panel for every race, so most of it is `N/A` rows.

### Outcome / next screen
Navigated to the Event decision screen.

---

## Screen 14 — Event decision

### Route and trigger
- Route: `/training-runs/8/events`, title "Event decision: Rice Shower".
- Trigger: the cockpit's "Event" action link.

### What the screen asks for
Coach recommendation (none), Current career state (read-back of five stats, Energy, Mood, Fans, Skill
Points), "Known outcomes on this run", "Recorded events", and a "Record an event choice" form:
`Turn *` (Select turn → Turn 1), `Source *` (Character, Support Card, Group, Scenario), `Event name *`,
`Choice you made *`, `Support card (if the event came from one)`, `What the choice gave`, and
`Record choice`.

### Values entered + source trace
None entered.

### Source data with no UI home
- The source records no event that fired during the run, so nothing from §1.8's Team Info panel or
  §2.4's payout event can be entered as an event choice. The screen's own copy is explicit that it
  "does not look up events it has not seen", so the source's scripted-event content has no home.
- **The four Ignited Spirit hints (§1.8 line 243-244).** These are the run's recorded inheritance/
  scenario hint state; there is no field for a hint, only for an event choice.

### Friction / errors / dead ends
- The read-back confirms persistence of the turn entry: Speed 607/1300, Stamina 577/1300, Power
  549/1300, Guts 736/1300, Wit 397/1800, `Energy N/A`, `Mood GREAT`, `Fans 209245`, `Skill Points 173`.
- `Turn *` offers only the turns that exist on the run (Turn 1), so this screen inherits the same
  positional problem as the rest of the run.
- Energy reads `N/A` here too, and the screen offers no advice (`TrainerAdvisor holds no event advice`).

### Outcome / next screen
Navigated to the Inheritance event screen.

---

## Screen 15 — Inheritance event (server error)

### Route and trigger
- Route: `/training-runs/8/inheritance`.
- Trigger: the cockpit's "Inheritance" action link.

### What the screen asks for
Nothing: the route returned **HTTP 500** and rendered Laravel's error page, not the app screen.

### Values entered + source trace
None. The screen never rendered, so nothing could be entered.

### Source data with no UI home
- The whole screen's subject matter (§1.5's ancestry, sparks and the three inheritance moments) is
  unreachable for this run, because the screen cannot load.

### Friction / errors / dead ends
- The error page reports: `TypeError` — `array_map(): Argument #1 ($a) must be of type array, string
  given`, in `App\Http\Controllers\Career\InheritanceEventController.php:162`, inside
  `legacySection()`.
- Read from the source (to explain the page, not as evidence of the screen's behaviour):
  `StoreLegacySelectionRequest.php:186-189` writes `legacies.*.ancestors` as a **list of name
  strings**, with the comment that ancestors are names and not ids (`ADR-0010`), while
  `InheritanceEventController.php:161-164` maps each entry as an **array** with `slot` and `name`
  keys. Any run whose stored legacy selection carries ancestor entries therefore fails to render.
- This is a hard dead end: the cockpit offers the link, the link is a 500, and there is no
  in-app route around it. The run's ancestry was entered entirely through the UI on screen 5.
- Not fixed, per the brief.

### Outcome / next screen
No forward movement from this screen. Navigated to the Career Timeline.

---

## Screen 16 — Career Timeline

### Route and trigger
- Route: `/training-runs/8/timeline`, title "Career timeline: Rice Shower".
- Trigger: the cockpit's "Career Timeline" link.

### What the screen asks for
A `Filter by type` control ("Toggle a type to hide its rows. Empty filter shows everything"), a count,
and a rail of rows. Each row's BEFORE stat block is the previous logged turn; EXPECTED and ACTUAL
collapse because `ADR-0003` stores the absolute end-of-turn value.

### Values entered + source trace
None entered.

### Source data with no UI home
- The source's four preset race days and the pending finals (§1.6 line 170-172) would appear here as
  race rows; none can be recorded at turn 1, so none is present.
- The source's turns beyond the snapshot (the §5 plan for the five remaining turns) have no
  representation.

### Friction / errors / dead ends
- The rail shows exactly one row: `TURN · Turn 1 · Turn recorded`, and the count reads
  "showing 1 of 1". Correct, and it is a direct consequence of the positional problem: a mid-career
  snapshot logged as turn 1 produces a one-row timeline.

### Outcome / next screen
Navigated to the Career Result screen.

---

## Screen 17 — Career Result

### Route and trigger
- Route: `/training-runs/8/result`, title "Career result: Rice Shower".
- Trigger: the cockpit's "Career Result" link.

### What the screen asks for
Read-only. It refuses to render a result and offers `Back to the Cockpit`, `Career Timeline` and
`Run record`.

### Values entered + source trace
None entered.

### Source data with no UI home
- The source's mid-career state is not a result, so nothing here is expected to have a home. The
  screen's subject matter (a finished build, a final race history, scenario objectives) is not
  reachable while the run is Active.

### Friction / errors / dead ends
- The screen's copy is exactly the behaviour the brief required: "This career is still running, so
  there is no result to read yet. The result screen reads a run whose status is Completed". The
  snapshot was not silently converted into a finished career, and the walk did not change the status
  to force it.
- This is a designed dead end for an unfinished run, not a defect.

### Outcome / next screen
Navigated to the run-scoped deck builder, the last screen the run exposes.

---

## Screen 18 — Support deck (run-scoped)

### Route and trigger
- Route: `/training-runs/8/deck`, title "Support deck".
- Trigger: the run record screen's "Change slot N" links.

### What the screen asks for
The six slots, each with the equipped card, its type, its scenario-link flag and its full effect
list; an `Ownership` toggle (`Owned` / `Rented`) per slot; `Replace the card in Slot N`; `Confirm
deck` ("Confirming replaces the whole deck: slots left empty are cleared"); and a picker with `Type`,
`Rarity`, `Availability` and `Name contains` filters over "251 of 251 cards available".

### Values entered + source trace
None entered.

### Source data with no UI home
- **Card client levels and limit breaks (§1.4 lines 77-84).** Still no field.
- **The reached effect values (§1.4 lines 97-104).** The screen prints catalog anchors only.

### Friction / errors / dead ends
- All six cards persisted and render with their effects. Haru Urara is the only slot flagged
  `Scenario Link`, which matches §1.4 lines 92-94 ("Haru Urara is the deck's only Unity Cup link").
- **The friend-slot Rented flag did not persist.** Slot 6 · Friends reads `Owned: true`,
  `Rented: false`. The screen states the reason: "The run record stores no field for the owned or
  rented flag yet, so the six toggles above hold their choice on this page only and a reload returns
  them to Owned. Nothing is posted for them, so nothing is claimed to have been saved."
- The consequence is that the source's one borrowed card (§1.4 lines 86-88, §7 item 8: the sixth slot
  is a card borrowed from another account, "which is why the only 50/50 card in the deck is the one
  borrowed") is indistinguishable from an owned card once the run exists. The distinction survives
  only inside the wizard's draft.

### Outcome / next screen
None. This is the last screen reachable for this run.

---

## Closing assessment

### What worked

- The wizard accepted the run's identity end to end. Scenario, trainee, target, ancestry and deck all
  persisted, and the Preflight contract echoed every entered value back before the write.
- The deck represented the source's deck exactly, including the friend slot as a **position** with an
  `Owned` / `Rented` flag, and it independently derived the one Scenario Link the source also derives
  (Haru Urara).
- `Rented for the run` → "Rented from a friend" is the right representation for the source's Guest
  parent, and it was the only place the borrowed-parent fact could go.
- The spark editor accepted kind, target and stars for every coloured spark entered, and **nothing
  forced a value for a category the source lacks**. There is no Wit spark row anywhere and no control
  demanded one, so the source's "no Wit spark anywhere in the tree" (§1.5 line 159) is representable
  by omission.
- The turn form accepted the run's five stats, its SP, its fans and its mood, and every later screen
  read them back correctly (cockpit, event decision, skills planner, deck).
- The Career Result screen refused to present an unfinished career as finished, and the walk never
  changed the run's status to make it do so.
- Unrecorded values render `N/A` with a stated reason throughout. The app never invented a number to
  fill a field.

### Functional findings

Problems where the UI failed to do what it claims, rejected valid information, lost information,
produced contradictory state, or prevented legitimate progression.

1. **`/training-runs/{run}/inheritance` returns HTTP 500.** `TypeError: array_map(): Argument #1 ($a)
   must be of type array, string given` at `InheritanceEventController.php:162`, because
   `StoreLegacySelectionRequest.php:186-189` stores `ancestors` as name strings and the controller
   maps them as arrays. Observed on a run created entirely through the UI with grandparent names
   entered. The cockpit's Inheritance action is a dead end and the screen is unusable.
2. **A blank trailing spark row is stored as a real spark.** After three `Add Spark` clicks on Parent
   A, the fourth (blank) row was persisted; the Preflight contract lists Parent A's sparks as
   "Blue on Power★1 / Pink on Late Surger★2 / Green on Cut and Drive!★2 / **Blue**", with no target and
   no stars on the fourth.
3. **The "Your target" step cannot be saved with the stat target unset**, even though its own
   validation message ("Enter a target for every stat, **or leave the whole target unset**") says that
   state is allowed. Submitting with four selects filled and all five stats empty produced five
   `role="alert"` errors, one per stat. Because four fields of the target are required, "the whole
   target unset" is unreachable, so the message describes a state the form forbids.
4. **The friend-slot Rented flag does not persist.** Set to `Rented` in the wizard, it reads `Owned`
   on `/training-runs/8/deck`, and the screen confirms the run record has no column for it. The
   source's borrowed-card distinction is lost at the moment the run is created.
5. **The trainee step's own `Stored choice:` readout does not update.** After `Select Trainee`
   succeeded (flash "Trainee set: Rice Shower."), the readout on the same screen still read `N/A`.
6. **The run record's turn table shows no Fans and no Energy column.** Both values were accepted by
   the form; neither is shown back anywhere on the screen that accepted them.
7. **The run record's inner turn flow reports "Step 2 of 5" and then "Step 4 of 5".** No step 3 label
   is ever rendered.

### Experiential findings

Problems where the UI technically works but is unclear, requires unnecessary rework, forces repeated
consultation of the source, uses confusing terminology, has poor information architecture, requires
awkward workarounds, or creates avoidable friction.

1. **The run's own caps cannot be entered.** §1.2's caps (Speed 1325, Stamina 1322, Power 1368, Guts
   1308) exceed the target form's maxima (1300), and the form asks for a *target* rather than a cap.
   The source's central cap-arithmetic discussion has no representation, and the tool's ceiling is
   lower than the run's.
2. **The mid-career position has no representation.** YEAR and MONTH are derived from a bare turn
   number. The only way to enter the snapshot's stats is to log them at a turn the form defaults to 1,
   which places a Senior-year state at Junior January. A user holding a mid-career snapshot must
   invent a position, and the screens then contradict the source.
3. **Four of the tree's six spark sets have no home.** The SPARKS block exists only for PARENT A and
   PARENT B; the four grandparents are name-only. Of the source's six blue sparks, only two can be
   recorded.
4. **Ancestor ranks are letter classes and the only rank field is a star count.** B+, A and UG have
   no field, so both parents' ranks print `N/A` on the contract.
5. **The parent picker lists duplicate bare names.** `Vodka` twice and `Maruzensky` twice, with no
   epithet, rank or id, so the two records cannot be told apart; and neither parent's epithet
   (`[Wild Top Gear]`, `[Formula R]`) has a field anywhere.
6. **Three of the four scenario cards print `Ruleset`, `Description` and `Recommended use` as `N/A`.**
   Two thirds of the comparison surface is blank for every option.
7. **The same turn form appears twice with different labels.** The run record says `Speed`; the
   Training decision says `Speed total *`. A user meets the identical form twice and must work out
   which one to submit.
8. **The trainee card's aptitude row disagrees with the source on three of ten letters** (Mile B→C,
   Front runner A→B, Late surger B→C), and the costume form the run actually uses is the unlabelled
   one while the debut form is named.
9. **Energy cannot be entered from this source, which disables the Advisor for the whole run.** §1.7
   explains why the source has no number, but the app offers no band or qualitative alternative, so
   every screen's recommendation panel is permanently blank for this run.
10. **The spark `Applies to` field accepts a stat name, an aptitude name or a skill name** with no
    list, no hint and no validation; the only clue is the spark's `Kind`.
11. **The race surfaces are unreachable in practice.** Because the run's turn is 1/2, the Race
    decision reports "No race on this scenario's calendar falls at this turn" and the planner offers
    only mandatory races, so the source's race-day card (§1.6) cannot be entered or even seen.

### Note on the brief versus the source

The task brief states that "Haru Urara occupies the friend slot". The source document says the friend
card is **Super Creek**: §1.4 lines 86-88, "the Career Profile rail tags Super Creek's entry with a
'Friends' pill where the other five carry their discipline icon, which is why the only 50/50 card in
the deck is the one borrowed from another account". This walk followed the source document, as the
brief directs, and placed Super Creek in `Slot 6 · Friends`. Recorded here because a reader comparing
the two will meet the difference.

### Unrepresented source data (final inventory)

Every source datum encountered for which the UI has no appropriate field:

- Run position: Senior Year Early Oct; 5 turns left; 5 turns until the Unity Cup (§1.1 line 21).
- Rarity ★3 and Potential Level 2 (§1.1 line 20).
- The `[Rosy Dreams]` costume identity: the card's second form is unlabelled (§1.1 line 20).
- The growth-rate row +0/+10/+0/+20/+0 (§1.1 line 20; §7 item 2).
- Fans class Star, the Top Star threshold 240,000 and the 30,755-fan gap (§1.1 lines 21-22).
- The run's own per-stat caps 1325 / 1322 / 1368 / 1308 / 1800 (§1.2 lines 28-32).
- The cap shortfall and its candidate causes (§1.2 lines 39-56; §8.5).
- Aptitudes (§1.1 lines 58-59): shown on the picker but not stored against the run, and three letters
  disagree with the source.
- The ten-plus owned skills and Corner Recovery ○ (§1.3 lines 63-67).
- Card client levels / limit breaks and the reached effect values (§1.4 lines 77-104).
- Both parents' letter ranks B+ / UG, and the grandparents' ranks B+ / A / UG (§1.5 lines 138-143).
- The four grandparents' spark sets (§1.5 lines 149, 150, 152, 153).
- The white sparks for both parents — representable (`Kind = White`) but not entered; see completion.
- Team identity: "Blue Bloom", motto "Dreaming Big", league placement 8th, the four preseason rounds
  won, finals pending (§1.6 lines 169-172).
- Goals: the three cleared and the active Arima Kinen goal (§1.6 lines 174-176).
- The race-day card: Kyoto Daishoten (G2) and Mile Championship Nambu Hai (G1), the Soft going, the
  "top contender" verdict, the fan rewards and the entry criteria (§1.6 lines 181-193).
- The Mood Effect panel percentages (§1.7 line 208).
- Energy: low, with the warning text; no number exists (§1.7 lines 213-218).
- The last observed turn: Speed facility Lv4, "Floor Cleaning", three avatars, +18 Speed / +8 Power /
  +6 Skill Points, Failure 39%, facility levels 4/4/4/5/4 (§1.7 lines 220-222).
- Team Info: 55 Unity Trainings, 6 Spirit Bursts, 5 Extreme Spirit Bursts, 20/20 members, the Team
  Ranking Bonus +30 and the team stat grades A/A/A/S/A (§1.8).
- The 18-member roster and the guests' skill lists (§1.9).
- The skill pool, its prices, the hint-discount ladder and the recommended spend (§2.1-2.3).
- The burst economy and the 10-12 band conclusion (§2.4, §8.2).

### Walk completion

- **Reached the end of the available flow: yes, for an Active run.** Eighteen screens were visited,
  in visit order, from the Dashboard to the run-scoped deck builder.
- **Where it stopped:** the flow terminates at the Career Result screen's "still running" state, and
  the Inheritance screen terminates in an HTTP 500. Nothing further is reachable for this run.
- **Why it stopped:** the run was deliberately left Active — the brief forbids marking the mid-career
  snapshot as completed — so the Result screen has nothing to read; and the Inheritance screen cannot
  render at all.
- **Intentionally omitted data:** the twelve white sparks from §1.5 (six on Vodka, six on
  Maruzensky) were not entered. They have a UI home (`Kind = White`) and the identical control to the
  nine coloured sparks that were entered, so no further UX behaviour was at stake; the omission is
  stated here rather than presented as a gap. The pair's affinity grade was left unset because the
  source states none. Every other source datum the UI cannot hold is listed under Unrepresented
  source data above.
- **Persistence note:** the values entered were verified to persist only where a screen read them
  back — the cockpit, the event decision screen, the skills planner, the timeline and the run-scoped
  deck all printed the entered stats, SP, fans and mood. No claim of persistence is made for anything
  a screen did not show again, and the friend-slot `Rented` flag is explicitly recorded as **not**
  persisting.
