# UX walk audit (2nd pass): [Rosy Dreams] Rice Shower, Unity Cup

## Header

| Field                | Value                                                                                                              |
| -------------------- | -------------------------------------------------------------------------------------------------------------------- |
| Audit name           | Rice Shower Unity Cup mid-career UX walk — second, independent pass                                                    |
| Source document      | `docs/[Rosy_Dreams]Rice_Shower_Unity-Cup.md` (67 `[Global]` client frames, 2026-10-03; a mid-career snapshot)          |
| Starting URL         | `http://127.0.0.1:8000/`                                                                                               |
| Date/time of walk    | 2026-10-10, a second continuous session on the same day as the first pass                                              |
| Tree under test       | `master` at `aedfc30` ("chore(runs): drop the orphaned stat_ceilings column"). The first pass ran against an earlier    |
|                      | working state whose peer commits had not yet landed, so this pass exercises different application code.                 |
| Environment          | Laravel 13.32.0, PHP 8.5.8, SQLite (`database/database.sqlite`), `php artisan serve` on `127.0.0.1:8000`, Chromium 150 |
| Run created          | `training_runs` id **9** (`/training-runs/9`), status Active, scenario Unity Cup                                         |
| Screens visited      | 20 in visit order (19 belonging to run 9 plus one labelled cross-check against run 8)                                   |

**Relationship to the first audit.** `docs/audits/rice-shower-unity-cup-ux-walk.md` is the first pass. This
is a fresh walk of the same source run through the same flow, written to its own file. Where the two
passes disagree, both readings are recorded with the pass that produced them; neither is silently
overwritten.

**Method statement.** Every screen below was reached in a real browser session and its contents were read
from the rendered DOM at the moment of the visit. Every value was typed or selected through the on-screen
controls (search box, native selects, number inputs, radio controls, checkboxes, disclosure summaries and
the app's own buttons). No seeder, no database insert or update, no SQL, no tinker, no API call and no
hand-edited application state was used to enter data. Inter-screen hops after the wizard hub used the
address bar with the exact href each screen's own link exposes; the target route is the route the link
points at. Source code was not needed to explain anything in this pass, because every claim here is
backed by a rendered screen.

**One pass.** Made once, forward. No screen was re-entered to change an earlier choice and none was
skipped. Where the UI demanded a value the source does not carry, the minimum available choice was made
and is flagged **Not present in source — UI-required choice**. The run was never marked Completed, never
filed as a Veteran and never filed as a Legacy.

**Nothing was fixed.** No route, controller, view, component, migration, model, seeder, test, validation
rule, label, field or schema was changed. The only file this pass added is
`docs/audits/rice-shower-unity-cup-ux-walk-2.md`.

---

## Screen 1 — Dashboard

### Route and trigger
- Route: `/` (`home`), title "Dashboard".
- Trigger: initial navigation to the starting URL.

### What the screen asks for
Read-only. ACTIVE CAREER (trainee, scenario, `Junior Year · Early January`, Turn 1, Energy `N/A`, Team
Rank `N/A`, `Resume Career`); Quick actions (`New Career`, `Legacy Lab`, `Support Cards`); RECENT
VETERANS (3 of 7); RECENT BUILDS; DATA STATUS (Verified 2026-09-27 · GameTora `scenarios.json`, Ruleset
`N/A`, Trainees 67, Skills 1,910, Support cards 559).

### Values entered + source trace
None. The control used was `New Career`.

### Source data with no UI home
None relevant on this screen.

### Friction / errors / dead ends
- The Dashboard now renders a career state, and it showed the run the **first** pass created (run 8):
  "Rice Shower … Junior Year · Early January · Turn 1". The same misplacement the first pass recorded is
  now on the landing screen before the walk starts.
- `New Career` was still available with an Active career already present, and it started a clean draft
  (step 1 showed `Stored choice: N/A`), so the two coexist without a prompt about the existing career.
- The Veterans rows now link to `/training-runs/N/cockpit`, not `/training-runs/N`.

### Outcome / next screen
Clicked `New Career` → `/career/setup/scenario`.

---

## Screen 2 — Scenario setup (step 1 of 6)

### Route and trigger
- Route: `/career/setup/scenario`, title "Choose a scenario".
- Trigger: clicked `New Career` on the Dashboard.

### What the screen asks for
Step nav (`Leave setup`; Scenario, Trainee, Your target, Legacy, Support deck, Preflight), a `Stored
choice` readout, and four scenario cards of eight facts (Optimizes, Systems, Tracked resources, Turn
loop, Live on Global, Ruleset, Description, Recommended use) each with a `Select Scenario` button. No
Continue button: forward movement is the step nav.

### Values entered + source trace
- `Unity Cup` → the Unity Cup card's `Select Scenario` → source §1.1, line 19 ("Scenario Unity Cup,
  confirmed by the logo panel").

### Source data with no UI home
The run's identity beyond the scenario name (trainee, year, month, turns remaining) is not asked for
here, as in the first pass.

### Friction / errors / dead ends
- **The Unity Cup card prints two Ruleset rows with different values.** The eight-fact block ends with
  `Ruleset · N/A`; below it the new description block prints `Ruleset · Team`. Both are on screen at the
  same time, so the same label carries `N/A` and `Team` on one card.
- Only Unity Cup gained the richer block (description, Ruleset, Planning focus, three KEY MECHANICS
  bullets). URA Finale, Trackblazer and Our Grand Concert still print `Ruleset`/`Description`
  /`Recommended use` as `N/A` plus a `Not recorded` line, so the comparison is now asymmetric: one card
  is fully described and the other three are mostly blank.
- Selecting flashed "Saving your choice…" then "Scenario set.", the button became `pressed="true"` with
  a "Selected" tag, and `Stored choice:` moved from `N/A` to `Unity Cup`.

### Outcome / next screen
Clicked "Trainee" in the step nav → `/career/setup/trainee`.

---

## Screen 3 — Trainee (step 2 of 6)

### Route and trigger
- Route: `/career/setup/trainee`, title "Choose a trainee".
- Trigger: clicked "Trainee" in the step nav.

### What the screen asks for
Filters in UI order: `Search` (text), `Surface`, `Distance`, `Running style` (selects Any,S..G), `Unique
skill` (≈130 options), `Sort by`, `Order`, `Filter`, `Clear filters`. Then a paginated 67-trainee roster.
Each card: portrait, English name, Japanese name, "Released (Global)", ten aptitude chips (letter plus
word), a costume-form summary, `Select Trainee`, `View Profile`.

### Values entered + source trace
- `Rice Shower` → `Search` → source §1.1, line 20.
- `Select Trainee` on the Rice Shower card (`id="trainee-30"`) → source §1.1, line 20.

### Source data with no UI home
- **★3 and Potential Level 2 (§1.1 line 20)**: no star rating and no Potential field on the card, and
  none is reachable from it. (See screens 8 and 11 — the run now *labels* this gap but offers no field.)
- **[Rosy Dreams]**: the card reads "2 costume forms ★★★SSRdebut form★★★SSR" — the second form, which is
  the run's, carries no name, and the portrait is `artwork/card_portrait/103001`, the debut form.

### Friction / errors / dead ends
- The aptitude row printed **Turf A, Dirt G, Sprint E, Mile C, Medium A, Long A, Front runner B, Pace
  chaser A, Late surger C, End closer G**. The source reads **Mile B, Front runner A, Late surger B**
  from the client (§1.1 lines 58-59). Three letters differ, and they differ **exactly as they did in the
  first pass** — same card id, same values — so this is a stable divergence, not a flake.
- `Select Trainee` flashed "Trainee set: Rice Shower." and `Stored choice:` then read **`Rice Shower`**.
  In the first pass the same readout stayed `N/A` at this point. Unreconciled between the two passes;
  the likely cause is that the first pass read the DOM during the post-save re-render, but it could also
  be one of the commits that landed between the passes. Recorded as a difference, not resolved.
- The search filter cleared after the write, returning the list to all 67.

### Outcome / next screen
Clicked "Your target" in the step nav → `/career/setup/target`.

---

## Screen 4 — Your target (step 3 of 6)

### Route and trigger
- Route: `/career/setup/target`, title "Your target".
- Trigger: clicked "Your target" in the step nav.

### What the screen asks for
`Purpose` (Not chosen yet, Story Clear, Champions Meeting, Parent Farming, Skill Farming) with a note
that a fifth purpose, Competitive Build, is not recorded; `Distance` (Sprint, Mile, Medium, Long);
`Surface` (Turf, Dirt); `Style` (Front Runner, Pace Chaser, Late Surger, End Closer); five stat targets
(Speed/Stamina/Power/Guts max 1300, Wit max 1800) each with a `Cap` label and a ratio readout; a `Skill
name` + `Add` priorities list; a live `Summary`; `Save target`.

### Values entered + source trace
**None.** The step was submitted with every field left at its empty state and the app accepted it. See
the first bullet under Friction for why this is the faithful entry rather than an omission.

### Source data with no UI home
- **The run's own caps (1325 / 1322 / 1368 / 1308 / 1800, §1.2 lines 28-32)** remain unenterable: the
  inputs carry `max=1300` / `max=1800`, so three of the run's four documented caps exceed the field
  ceiling. The §1.2 cap arithmetic still has no representation: the screen asks for a target, not a cap,
  and it asks for no spark and no support-card cap contribution.
- **Build purpose, and the single distance the run is "for"**: the source names both Medium and Long as
  A and calls it "A Medium/Long build" (§1.1 line 59) without picking one.
- Potential Level, the growth row +0/+10/+0/+20/+0, and the fan class/threshold.

### Friction / errors / dead ends
- **The first pass's contradiction is gone.** Submitting with nothing entered now returns
  "Build target saved." with **zero** `[role=alert]` elements. In the first pass the same submission
  produced nine errors, five of them reading "Enter a target for every stat, or leave the whole target
  unset." while the target was in fact wholly unset. The purpose/distance/surface/style fields are no
  longer required, so the state the message offered is now reachable.
- Consequence for this walk: because nothing is forced, the source's real condition — a mid-career
  snapshot with no recorded build target — was carried forward honestly instead of being padded with
  invented values, as the first pass had to do.
- The step kept printing "No target is recorded yet. The fields below start empty rather than at zero."
  after the save, which is accurate, but the success flash beside it is easy to misread as "recorded".

### Outcome / next screen
Clicked "Legacy" in the step nav → `/career/setup/legacy`.

---

## Screen 5 — Legacy (step 4 of 6)

### Route and trigger
- Route: `/career/setup/legacy`, title "Legacy".
- Trigger: clicked "Legacy" in the step nav.

### What the screen asks for
Two parent panels. Each: a rank and spark-chance readout; `Assign to Parent N` (select over the saved
Veteran library); `Grandparent N1`/`N2` (type-ahead over the catalog roster); **each grandparent now has
its own `Add spark to grandparent …` disclosure**; an "AS YOU READ HER" block with `Own rank (letters)`
(combo with a fixed suggestion set), `Own rank (stars)` (number), `Rented for the run` (checkbox) and a
`SPARKS` list. Each spark row is `Kind` (Blue, Pink, Green, White, Scenario), `Applies to` (Stat,
Aptitude, Skill) and a dependent `Target` select plus `Stars` (1-3). Then `Affinity for the pair`
(Not recorded, △, ○, ◎), `Save Legacy`, `Next: Support deck`.

### Values entered + source trace
| Value | Field | Source |
| ----- | ----- | ------ |
| `Vodka [Wild Top Gear] · 7` | Assign to Parent A | §1.5 line 138 |
| `Daiwa Scarlet` / `Seiun Sky` | Grandparent A1 / A2 | §1.5 lines 139-140 |
| `B+` | Parent A → Own rank (letters) | §1.5 line 138 ("rank B+") |
| Blue / Stat / `Power` / ★1 | Parent A spark | §1.5 line 148 |
| `Maruzensky [Formula R] · 2` | Assign to Parent B | §1.5 line 141 |
| `Daiwa Scarlet` / `Taiki Shuttle` | Grandparent B1 / B2 | §1.5 lines 142-143 |
| `UG` | Parent B → Own rank (letters) | §1.5 line 141 ("rank UG") |
| checked | Parent B → Rented for the run | §1.5 line 141 ("flagged Guest") |
| Blue / Stat / `Speed` / ★2 | Parent B spark | §1.5 line 151 |
| Blue / Stat / `Stamina` / ★3 | Grandparent A2 (Seiun Sky) spark | §1.5 line 150 |
| (left "Not recorded") | Affinity for the pair | **Not present in source** — §1.5 states no pair grade |

### Source data with no UI home
- **The four grandparents' ranks** (B+ line 139, A line 140, UG lines 142-143): `Own rank (letters)` and
  `Own rank (stars)` exist only for the two parents. Grandparent nodes take a name and now sparks, but no
  rank.
- **The remaining three of the six blue sparks** (Daiwa Scarlet Legacy-1 side Guts ★1 line 149, Daiwa
  Scarlet Legacy-2 side Speed ★2 line 152, Taiki Shuttle Power ★3 line 153) and every pink, green and
  white spark in §1.5's table: the fields exist and the two I entered were accepted by the editor, so
  this is no longer a missing-field gap — it is the volume of rows the control requires. The first pass
  could not place them at all.
- **Star-roll chance**: `Spark chance` is `N/A` with the stated reason ("This tool holds no sourced
  star-roll table"), so §1.2's per-activation arithmetic still has nothing to read.

### Friction / errors / dead ends
- **The save was silently discarded.** `Save Legacy` produced **no** confirmation flash, **no**
  `[role=alert]` error, and left the line "No Legacy is recorded for this setup yet" in place. Screen 7
  then reported `ANCESTRY · N/A` and listed "Ancestry is not entered." among its warnings. Every value in
  the table above was entered and none of it survived. The first pass saw a "Legacy recorded." flash here;
  this pass saw nothing.
- The rank-letter suggestion set is `G, F, E, D, C, B, A, S, B+, A+, S+`. It has no `SS`, `U`, `UU` or
  `UG`, and `UG` is the rank the source gives for Legacy 2 and both of its grandparents. The field is an
  editable combo, so typing `UG` was accepted without complaint — the value simply is not one the control
  offers, and nothing warns that it is off-set.
- The grandparent spark editor's Low/Mid-style band select renders with **no accessible name** in the
  accessibility tree (a bare `combobox`), unlike its siblings.
- Three changes that removed first-pass friction, confirmed live here: parent options now read
  `Vodka [Wild Top Gear] · 7` / `· 6`, so the duplicates are distinguishable; `Own rank (letters)` exists
  alongside the star count; and all four grandparents gained a spark disclosure. Spark `Applies to` is now
  a Stat/Aptitude/Skill select with a dependent Target list, replacing the free-text field.

### Outcome / next screen
Clicked `Next: Support deck` → `/career/setup/deck`. The ancestry entered on this screen was gone by the
time the contract was assembled.

---

## Screen 6 — Support deck (step 5 of 6)

### Route and trigger
- Route: `/career/setup/deck`, title "Support deck".
- Trigger: clicked `Next: Support deck` on the Legacy screen.

### What the screen asks for
Six slots (1-5 and `Slot 6 · Friends`, tagged "Friend slot"), each with the equipped card or an empty
state, an `Ownership` `Owned`/`Rented` toggle pair and `Replace the card in Slot N`; `Save deck`; then a
`Choose a card` picker with `Type`, `Name contains`, `Filter`, a `Card for Slot N` select and
`Equip owned` / `Equip rented` / `Set Slot N to not equipped`.

### Values entered + source trace
| Slot | Card | Ownership | Source |
| ---- | ---- | --------- | ------ |
| 1 | `Tokai Teio [Dream Big!] · SSR Speed` | Owned | §1.4 line 79 |
| 2 | `Nishino Flower [Even the Littlest Bud] · SSR Speed` | Owned | §1.4 line 80 |
| 3 | `Biko Pegasus [Double Carrot Punch!] · SSR Speed` | Owned | §1.4 line 81 |
| 4 | `Matikanetannhauser [Just Keep Going] · SSR Guts` | Owned | §1.4 line 83 |
| 5 | `Haru Urara [Urara's Day Off!] · SSR Guts` | Owned | §1.4 line 84 |
| 6 · Friends | `Super Creek [Piece of Mind] · SSR Stamina` | **Rented** | §1.4 lines 82, 86-88 |

Slot order for the five owned cards is **Not present in source — UI-required choice**: §1.4 lists the deck
without slot numbers. The friend card went to Slot 6 because §1.4 lines 86-88 names Super Creek as the
borrowed card and the UI names Slot 6 the friend slot.

### Source data with no UI home
- **Client levels and limit breaks (35/35, 30/30, 30/30, 50/50, 30/30, 35/35, §1.4 lines 77-84)**: no
  field.
- **The reached effect values ("Client rows visible", §1.4 lines 97-104)**: the analysis prints catalog
  maxima and says so; the run's own reached figures have nowhere to go.

### Friction / errors / dead ends
- The copy on this screen still says the flag lives only in the draft ("The deck table has no column for
  it yet, so Preflight writes the six cards it can store and the flag stays here until one exists"), while
  screen 18 reports the flag as saved and reload-stable. The two statements contradict each other; see
  Screen 18.
- The picker's target slot is set only by `Replace the card in Slot N`, and the only signal that it moved
  is the slot name inside the equip button's label.

### Outcome / next screen
`Save deck` → "Deck saved." (URL gained `?deck_slot=6`), then `Next: Preflight` → `/career/setup/preflight`.

---

## Screen 7 — Preflight (step 6 of 6)

### Route and trigger
- Route: `/career/setup/preflight`, title "Career contract".
- Trigger: clicked `Next: Preflight`.

### What the screen asks for
Read-only, then the wizard's single write: a **Warnings** region; Build (trainee, scenario, target,
ancestry); Support deck (six slots with type and ownership); the full Deck analysis; Target; and
`Back`, `Edit Legacy`, `Edit Deck`, `Edit Target`, `Start Career`.

### Values entered + source trace
None. Only `Start Career` was pressed.

### Source data with no UI home
- `YOUR TARGET · N/A` and `ANCESTRY · N/A` are the contract's own statement that the ancestry and the
  target from §1.4/§1.5 and the (absent) build target are not in the run about to be created.

### Friction / errors / dead ends
- **The contract names both gaps, which is what makes the silent Legacy save visible.** Warnings reads:
  "Your target is not entered." and "Ancestry is not entered.", each with an `Open that step` link and the
  line "nothing is lost by going back". The first pass printed "No warnings. Every section is filled…".
- The claim "nothing is lost by going back" is the one this walk cannot corroborate: leaving step 4 and
  coming forward again is exactly the path along which the ancestry vanished.
- `Start Career` stayed enabled with two warnings outstanding, so the wizard will create a career from a
  contract that says two of its own sections are empty.

### Outcome / next screen
Clicked `Start Career` → "Run created." → `/training-runs/9/cockpit`.

---

## Screen 8 — Career Cockpit (first arrival)

### Route and trigger
- Route: `/training-runs/9/cockpit`, title "Career: Rice Shower".
- Trigger: `Start Career` on the contract.

### What the screen asks for
A run identity block (trainee, a rarity/potential `N/A` badge, SCENARIO, YEAR, MONTH, TURN, Team Rank,
Spirit Bursts, and an "Edit the run's identity" form holding `Status`, `Scenario` and `Delete this
career`); Stats and state (five stats, Energy, Mood, Fans, Skill Points); Advisor (recommendation and
why); a "No turn recorded yet" region; Actions (Training, Race, Rest, Recreation, Scenario action, Event,
Inheritance); the Unity Cup scenario panel (team race circles, the G..S+ ladder, team stat grades, Spirit
Burst states, Current Spirit, the burst-count band table, Special Training notes); Races in this run; and
links to the Career Timeline and Career Result.

### Values entered + source trace
None.

### Source data with no UI home
- **`YEAR N/A` / `MONTH N/A` / `TURN 0`.** §1.1 line 21's "Senior Year Early Oct, 5 turns left, 5 turns
  until the Unity Cup" still has no field; the three readouts are derived, not entered.
- **Rarity ★3 and Potential Lvl 2 (§1.1 line 20).** The screen now *names* this gap — "The trainee's
  rarity and potential level has not been recorded for this run" — but the identity form beside it holds
  only `Status`, `Scenario` and `Delete this career`. A badge describing a missing field is not a field.
- **Team identity (§1.6 lines 169-172)**: team name "Blue Bloom", motto "Dreaming Big", league 8th, four
  rounds won, finals pending. The panel has Team Rank and Spirit Bursts only.
- **Unity Trainings 55, Spirit Bursts 6, Extreme 5, members 20/20, the +30 ranking bonus, team grades
  A/A/A/S/A (§1.8)**: the band table exists but the run's own counts have no input, and the panel's copy
  states bursts "are recorded as states, not counters (D-223)".

### Friction / errors / dead ends
- **The stat denominators are the target, not the cap.** With no target entered the strip reads
  `Speed N/A / N/A — No turn has recorded this stat yet, and no target was entered for it.` In the first
  pass, which had invented a 1300 target, the same strip read `N/A / 1,300`. So the scenario's own ceiling
  (1300/1300/1300/1300/1800, which §1.2 compares the run against) is now absent from the cockpit whenever
  no target exists. Screen 15 shows a different denominator for the same stat.
- The Advisor declines for want of Energy, with the reason printed. Correct behaviour, and it becomes the
  subject of screen 11.
- The Unity Cup panel still carries the estimated burst-band table with its three-way publisher caveat,
  which is the honest way to show the §2.4/§8.2 dispute.

### Outcome / next screen
Followed the run's own identity route, `/training-runs/9`.

---

## Screen 9 — Run root (`/training-runs/9`), redirected

### Route and trigger
- Requested route: `/training-runs/9` (`runs.show`). Final route: **`/training-runs/9/cockpit`**.
- Trigger: clicked `Cockpit` in the run header after reading screen 8, then navigated to the bare run id.

### What the screen asks for
Nothing of its own: the URL resolved to the same visible Cockpit as screen 8.

### Values entered + source trace
None.

### Source data with no UI home
As screen 8 — the two routes present one screen.

### Friction / errors / dead ends
- **The run record screen no longer exists as a distinct surface.** In the first pass `/training-runs/8`
  was the turn logger: a three-year race calendar, a race form, deck selects, the turn form, a correction
  form and a skills panel (49 controls, 34,793 characters of text). This pass found 2 controls and 5,527
  characters at the same address, because it redirects to the Cockpit.
- Three labels still point users at that retired screen. The Career Result says "the run record screen is
  where the status changes" (status is on the Cockpit); the Training decision offers "Record the first turn
  **on the run screen**", and that link's href is `/training-runs/9/cockpit`; the Cockpit itself now says
  "Record the first turn **through Training**". Two of the three instructions are stale in wording while
  all three resolve correctly.

### Outcome / next screen
Took the Cockpit's `Training` action → `/training-runs/9/training`.

---

## Screen 10 — Training decision and the turn write

### Route and trigger
- Route: `/training-runs/9/training`, title "Training decision: Rice Shower".
- Trigger: the Cockpit's "Training" action link.

### What the screen asks for
Five training cards (Speed, Stamina, Power, Guts, Wit), each with a rank line, `EXPECTED GAINS`,
`ENERGY COST` (now with its declared-constant provenance), `SUPPORTS ENTERED FOR THIS TRAINING`, `TARGET
DEFICIT`, `RISK`, `Train` and `Inspect details`. Then "Record the turn": `Turn *`, `Speed *`,
`Stamina *`, `Power *`, `Guts *`, `Wit *`, `Skill Points`, `Fans`, **`Energy` as Exact / Band / Not
recorded** (a Low/Mid/High select appears under Band), `Mood`, `Outcome *`, a `Facility levels this turn`
disclosure (five 1-5 inputs), a `Preview readings` disclosure (`Failure rate` 0-100 plus six gain
inputs), and `Record this training`.

### Values entered + source trace
| Value | Field | Source |
| ----- | ----- | ------ |
| Speed | `Train` on the Speed card | §1.7 line 220 ("Speed facility Lv4 with the banner 'Floor Cleaning'") |
| `1` (left at the default) | Turn * | **Not present in source — UI-required choice.** §1.1 line 21 gives a position, not a turn number, and the field is required with default 1. |
| `607` | Speed * | §1.2 line 28 |
| `577` | Stamina * | §1.2 line 29 |
| `549` | Power * | §1.2 line 30 |
| `736` | Guts * | §1.2 line 31 |
| `397` | Wit * | §1.2 line 32 |
| `173` | Skill Points | §1.2 line 34 |
| `209245` | Fans | §1.1 line 22 |
| Band → `Low` | Energy | §1.7 lines 213-216: the bar "sits near a third" with the low-energy warning printed, and "the client never prints an Energy number" |
| `GREAT` | Mood | §1.7 line 208 |
| `Success` | Outcome * | Default. §1.7 records the tile's 39% failure rate, not a resolved outcome; the choice is **Not present in source — UI-required choice.** |
| `4 / 4 / 4 / 5 / 4` | Facility levels Speed..Wit | §1.7 line 222 |
| `39` | Preview readings → Failure rate | §1.7 line 222 ("Failure 39%") |
| `18` | Preview gains → Speed | §1.7 line 221 ("+18 Speed") |
| `8` | Preview gains → Power | §1.7 line 221 ("+8 Power") |
| `6` | Preview gains → Skill Points | §1.7 line 221 ("+6 Skill Points") |

### Source data with no UI home
- **The run's position again.** Everything above is the Senior Early October state; `Turn` could only be
  carried at its default 1, and `Outcome` had to be chosen because the source records an unresolved tile.
- **The `Floor Cleaning` banner and the three card avatars (§1.7 line 221)** have no field.
- **§1.3's owned-skill list and §1.5's rank letters** are not reachable from this screen; the skill panel
  that walk 1 found on the run record screen is not present here.

### Friction / errors / dead ends
- Four of the first pass's "no field exists" items on this run are now fields: facility levels, failure
  rate, per-gain previews, and Energy as a band instead of a forced number.
- The Energy band select has no accessible name (an unnamed `combobox`), so its purpose is carried only by
  the adjacent radios and the "Energy" text.
- The cards' `TARGET DEFICIT` reads `N/A — No build target was entered for this stat`, which is the honest
  result of carrying no target; the first pass showed four- and three-digit deficits computed from a target
  the source never stated.
- No validation error appeared at any point in the write, and every entered value survived the
  re-renders.

### Outcome / next screen
`Record this training` → the write landed on `/training-runs/9/cockpit` with no error.

---

## Screen 11 — Career Cockpit (after the turn write)

### Route and trigger
- Route: `/training-runs/9/cockpit`.
- Trigger: the Training decision's own write, which states "The write lands on the Cockpit".

### What the screen asks for
Same surface as screen 8, now populated.

### Values entered + source trace
Nothing entered here; this is the read-back of screen 10.

### Source data with no UI home
- **Energy.** The band `Low` was entered and accepted on screen 10; this screen prints `ENERGY · Energy
  N/A`, and the Advisor still says "Recommendation unavailable because Energy has not been entered … there
  is no band to read and nothing to rank against."
- **The turn's activity, failure rate, facility levels and preview gains** are not shown: the strip carries
  stats, Mood, Fans and Skill Points only.

### Friction / errors / dead ends
- **Persistence verified for what the screen echoes:** Speed 607, Stamina 577, Power 549, Guts 736, Wit
  397, Mood GREAT ↑, Skill Points 173, and Fans rendered as **`209,245 · Star · 30,755 to next`** — the
  class and the gap the source states at §1.1 lines 21-22, now derived by the app from the fan count
  instead of needing a field.
- **The band reading is contradicted by the app's own words.** The Advisor's stated reason is the absence
  of a band; a band was entered. Whether it is stored or dropped cannot be settled from the UI: the
  correction form for turn 1 prefills Speed 607, Fans 209245 and Mood GREAT and exposes an `energy`
  number field with **no** `energy_state` or band control at all, so there is no screen that reads the band
  back. From the user's side the value is lost.
- `YEAR Junior Year / MONTH Early January / TURN 1` — the source's snapshot is Senior Early October. The
  first pass met the same contradiction; it reproduces.

### Outcome / next screen
Went to the Inheritance screen.

---

## Screen 12 — Inheritance event (run 9)

### Route and trigger
- Route: `/training-runs/9/inheritance`, title "Inheritance: Rice Shower".
- Trigger: navigated from the Cockpit's "Inheritance" action.

### What the screen asks for
An explanatory header ("Track the three inheritance moments … the Predicted section shows the Sparks
carried by the chosen parents and grandparents … no inheritance outcome is computed"), an Inheritance
Milestones strip (`● Career Start completed`, `○ Classic Early April upcoming`, `○ Senior Early April
upcoming`), a `Predicted Sparks` region with Blue/Pink/Green counts, `EXPECTED INHERITANCE · N/A (Not
computed)`, and an Observed section.

### Values entered + source trace
None entered.

### Source data with no UI home
- The whole §1.5 tree, because the ancestry never reached the run: the screen reads "No Legacy
  configuration recorded for this …".

### Friction / errors / dead ends
- **It renders.** The first pass met an HTTP 500 here. This run reaches the same route cleanly, but only
  because it has no ancestry to render — so this visit cannot by itself say whether that crash was fixed.
  Screen 13 settles it.
- The milestone strip marks "Career Start completed" for a run with one logged turn, which is consistent
  with the app's own derivation of position from the turn number.

### Outcome / next screen
Navigated to the same route on run 8, the first pass's run, which does carry an ancestry.

---

## Screen 13 — Inheritance event (run 8) — cross-check, not this run's flow

### Route and trigger
- Route: `/training-runs/8/inheritance`, title "Inheritance: Rice Shower".
- Trigger: navigated from screen 12. This is the run the **first** pass created; it is in the library with
  an ancestry attached, which is what the crash needed.

### What the screen asks for
The same surface as screen 12, now with data.

### Values entered + source trace
None entered.

### Source data with no UI home
Not applicable — this is a check on the other run.

### Friction / errors / dead ends
- **The first pass's 500 is gone.** `InheritanceEventController.php:162` no longer throws on run 8's
  ancestry; the screen renders, which the first pass could not do.
- **The phantom spark the first pass produced is still there and is now visible as a number.** Run 8's
  `Predicted Sparks` reports **BLUE 3 Sparks**, PINK 2, GREEN 2. The first pass entered two real blue
  sparks (Vodka Power ★1, Maruzensky Speed ★2) plus one blank trailing row, and it recorded that the
  contract printed a bare fourth "Blue". Three blues counted where two are real is the same inflation, read
  off a screen rather than inferred.

### Outcome / next screen
Returned to run 9 and took its "Race" action.

---

## Screen 14 — Race decision

### Route and trigger
- Route: `/training-runs/9/races`, title "Race decision: Rice Shower".
- Trigger: the Cockpit's "Race" action link.

### What the screen asks for
"The turn being decided" (`Junior Year · Late January · Turn 2`); Mandatory races (Junior Make Debut,
Junior turn 12, flagged NEXT; URA Finals Qualifier / Semifinal / Final (Aoharu), Finale, all "Not recorded
yet"); a next-obligation line; `Readiness`; and "Races to decide between" with `Calendar race` / `Race
not on the calendar` controls and a `Plan the whole calendar` link.

### Values entered + source trace
None entered.

### Source data with no UI home
- **The entire §1.6 race-day card**: Kyoto Daishoten (G2, Kyoto Turf 2400 m, Fall, **Soft**, 6,700 fans on
  a win) and Mile Championship Nambu Hai (G1, Morioka Dirt 1600 m), the entry criteria, the five-place fan
  ladder and the "top contender" verdict (lines 181-193).
- **The four rounds already won and the pending finals** (lines 169-172).

### Friction / errors / dead ends
- "No race on this scenario's calendar falls at this turn" is correct for turn 2 and wrong for the run:
  the source's turn is Senior Early October, where two races are offered. The position problem reaches the
  race surface unchanged from the first pass.
- The obligation list is the scenario's, not the run's, so it cannot show that this run already cleared
  four team rounds.
- `Readiness` is `N/A` with a stated reason.

### Outcome / next screen
Left the race screen for the Event decision; the planner was visited later at the end of the flow
(screen 20).

---

## Screen 15 — Event decision

### Route and trigger
- Route: `/training-runs/9/events`, title "Event decision: Rice Shower".
- Trigger: the Cockpit's "Event" action link.

### What the screen asks for
Coach recommendation; Current career state (five stats, Energy, Mood, Fans, Skill Points); Known outcomes
on this run; Recorded events; and a record form: `turn`, `event_type`, `source_name`, `choice_label`,
`support_card_name`, and its submit.

### Values entered + source trace
None entered.

### Source data with no UI home
- The source records no event that fired during the run, so nothing from §1.8's Team Info panel or §2.4's
  payout event maps to an event choice.
- **§1.3's ten-plus owned skills and Corner Recovery ○**: this screen records choices, not an owned-skill
  list, and no other screen in the flow holds one.

### Friction / errors / dead ends
- The read-back confirms the write: Speed 607, Stamina 577, Power 549, Guts 736, Wit 397, Mood GREAT,
  Fans 209245, Skill Points 173 — and **Energy N/A**.
- **This screen prints the same five stats as `607/1300`, `397/1800`** while the Cockpit prints
  `607 / N/A — No target was entered for this stat.` The denominators disagree between two screens of one
  run: scenario cap here, build target there.
- The form's `turn` select offers only turns that exist (Turn 1), so it inherits the position problem.

### Outcome / next screen
Navigated to the Career Timeline.

---

## Screen 16 — Career Timeline

### Route and trigger
- Route: `/training-runs/9/timeline`, title "Career timeline: Rice Shower".
- Trigger: the Cockpit's "Career Timeline" link.

### What the screen asks for
A type filter and a rail. Its header states that each row's BEFORE block is the previous logged turn and
that EXPECTED and ACTUAL collapse because `ADR-0003` stores the absolute end-of-turn value.

### Values entered + source trace
None entered.

### Source data with no UI home
- The source's four won team rounds, its cleared goals and its pending finals would all appear as rows;
  none exists, because the run sits at turn 1.

### Friction / errors / dead ends
- The rail reads `Junior Year · Early January · Turn 1`, one row, "showing 1 of 1" — a Senior October
  snapshot rendered as the career's opening turn.
- The row says "Turn recorded" and does not show the activity, so the Speed choice made on screen 10 is
  not visible here.

### Outcome / next screen
Navigated to the Career Result.

---

## Screen 17 — Career Result

### Route and trigger
- Route: `/training-runs/9/result`, title "Career result: Rice Shower".
- Trigger: the Cockpit's "Career Result" link.

### What the screen asks for
Read-only. It refuses and offers `Back to the Cockpit`, `Career Timeline` and `Cockpit`.

### Values entered + source trace
None entered.

### Source data with no UI home
Its subject matter — a finished build, a final race history, scenario objectives — is not reachable while
the run is Active, which is the correct outcome for a mid-career snapshot.

### Friction / errors / dead ends
- **"This career is still running"** with the stated rule that the screen reads a Completed run. The
  snapshot was not converted into a finished career, and the status was never touched.
- Its copy sends the user to "the run record screen" to change the status, and that address redirects to
  the Cockpit (screen 9).

### Outcome / next screen
Navigated to the Skills planner.

---

## Screen 18 — Skills planner

### Route and trigger
- Route: `/training-runs/9/skills`, title "Skills planner: Rice Shower".
- Trigger: the run header's "Skills planner" link.

### What the screen asks for
Read-only. A "Your target" state line, `Skill Point coverage`, `Hint discounts`, and five priority states
(Required, Available, Learned, Inherited).

### Values entered + source trace
None entered.

### Source data with no UI home
- **§1.3's owned skill list** (ten chips plus Corner Recovery ○) and **§2.2's purchasable pool with its
  173-SP question**: the screen reports the run's SP but lists no skills, because none are marked.
- **§2.1's hint discount ladder**: see the friction note.

### Friction / errors / dead ends
- Coverage resolves to "The skills still to learn cost **0** SP, and the run holds 173 SP, leaving 173" —
  arithmetic over an empty set, presented as a result.
- **`Hint discounts are not modelled.`** The first pass rendered the ladder here in full — "Hint Lvl 1 10%
  off … Hint Lvl Max 40% off" with a Confirmed marker — which is the §2.1 figure this repository records as
  client-read. One pass showed it; this pass disclaims it. Reported as observed on each pass, not
  reconciled.
- "No build target is set on this run, so the fit cells below compare against nothing" correctly carries
  the empty target forward.

### Outcome / next screen
Navigated to the run-scoped deck screen.

---

## Screen 19 — Support deck (run-scoped)

### Route and trigger
- Route: `/training-runs/9/deck`, title "Support deck".
- Trigger: the run deck surface reached from the run flow (reached by its route in this pass).

### What the screen asks for
Six slots with their cards, types, scenario-link flags and full effect lists; a per-slot `Owned`/`Rented`
toggle; `Replace the card in Slot N`; `Confirm deck`; and a picker with `Type`, `Rarity`, `Availability`,
`Name contains` filters.

### Values entered + source trace
None entered — this screen was used to read back what step 5 had set.

### Source data with no UI home
- Card client levels and limit breaks, and the reached per-card effect values, as on screen 6.

### Friction / errors / dead ends
- **The friend card's rented status survived this time.** Slot 6 · Friends reads `Owned:false
  Rented:true` after a full page load, and the screen states: "The owned or rented flag is saved with the
  deck, one value per slot, and stays on the run after a reload. A slot you have not classified reads as
  N/A rather than as Owned." Slots 1-5 read `Owned:true`.
- The deck step's own copy (screen 6) still says the flag has no column and stays in the draft, which this
  screen contradicts.

### Outcome / next screen
Navigated to the Race planner, the last screen the flow exposes.

---

## Screen 20 — Race planner

### Route and trigger
- Route: `/training-runs/9/races/planner`, title "Race planner: Rice Shower".
- Trigger: `Plan the whole calendar` on the Race decision screen.

### What the screen asks for
Read-only. "Planning from Junior Year · Turn 2"; Held figures (Win probability, Expected risk);
Deadlines; Mandatory races, each expandable to a per-race panel (Grade, Distance, Distance band, Surface,
Running style, Fan gain, Reward, Skill points, Scenario reward, Estimated win probability); Upcoming races;
and a `Compare` control per race.

### Values entered + source trace
None entered.

### Source data with no UI home
- The Soft going and the per-place fan payouts (§1.6 lines 189-191): `FAN GAIN` is `N/A` with the reason
  that the payout curve id is stored but no payout table is.
- The Arima Kinen goal (§1.6 line 176): goal races remain unrecorded.

### Friction / errors / dead ends
- The panels are uniformly `N/A` on the calendar's own placeholder races (Junior Make Debut and Junior
  Maiden Race carry no distance, band or surface), while Chukyo Junior Stakes resolves to 1,600 m / Mile /
  Turf with a 350-fan gate. Consistent disclosure per field, but the page runs to 17,409 characters of
  mostly empty panels.
- Win probability is withheld on every panel by design.

### Outcome / next screen
End of the reachable flow for an Active run. Nothing further is offered.

---

# Closing assessment

## What worked

- **The deck is faithfully representable end to end, including the friend card.** All six cards from §1.4
  equipped, Haru Urara alone carried the Scenario Link flag the source derives independently (§1.4 lines
  92-94), Super Creek sat in `Slot 6 · Friends`, and — unlike the first pass — the `Rented` flag survived a
  full reload to screen 19. This is the §5 "sixth support-card slot" question answered affirmatively: the
  borrowed card is representable as a friend slot **and** an ownership flag, and the flag persists.
- **The mid-career stat line entered cleanly and read back.** Every figure from §1.2 (607/577/549/736/397,
  SP 173) plus §1.1's 209,245 fans and §1.7's GREAT mood were accepted and echoed by the Cockpit, the
  Event decision and the Timeline, with no validation error anywhere.
- **The cockpit derived the fan class and the gap** ("209,245 · Star · 30,755 to next") from the fan count,
  which is §1.1 lines 21-22's pair of figures arriving without a field being needed.
- **The §1.7 turn record now has fields.** Facility levels 4/4/4/5/4, Failure 39% and the +18/+8/+6
  preview were all accepted on screen 10; none of them existed in the first pass.
- **Energy without a number is finally expressible.** Exact / Band / Not recorded with Low/Mid/High answers
  §1.7's "the client never prints an Energy number" directly, and the source's "low" was selectable. This
  was the first pass's most structural entry obstacle and it is gone at the control.
- **The ancestry form got the three things the first pass said it lacked**: disambiguated parent options
  (`Vodka [Wild Top Gear] · 7`), a letter-rank field beside the star count, and spark editors on all four
  grandparents — so §5's cap-arithmetic question now has a place for sparks on every node, and the two
  ★★★ blues the §1.2 shortfall turns on (Seiun Sky's Stamina, and by extension Taiki Shuttle's Power) can be
  entered where the first pass could place only two of the six.
- **The wizard stopped forcing invention.** The target step accepted an empty submission, so this pass
  carried the source's real "no documented build target" instead of manufacturing purpose, distance, style
  and five cap numbers, and the downstream screens reported `N/A` and "compare against nothing" honestly.
- **The contract reports its own gaps** (Warnings: target, ancestry) and **the Result screen refused to
  present an unfinished career as finished.**

## Functional findings

Where the UI failed to do what it claims, lost information, produced contradictory state, or blocked
legitimate progression.

1. **The Legacy step discards its entire input with no signal.** Every value in the screen 5 table was
   entered, `Save Legacy` was pressed, and the result was no confirmation flash, no `[role=alert]` error,
   and the unchanged line "No Legacy is recorded for this setup yet". The next screen that reads the draft
   — Preflight — then reported `ANCESTRY · N/A` and "Ancestry is not entered." The first pass, on the same
   route with the same kind of input, received a "Legacy recorded." flash and a populated contract, so the
   failure is not inherent to the data entered.
2. **The Preflight warning text promises the opposite of what happened.** "No two-parent ancestry is
   recorded in this setup. Step 4 enters it, and nothing is lost by going back." The ancestry was entered on
   step 4 and lost on the way forward, without going back at all.
3. **`Start Career` stays enabled against its own contract.** The screen lists two open warnings and still
   creates the run, so the contract's final state can read `YOUR TARGET · N/A / ANCESTRY · N/A` while
   announcing "Everything the six steps recorded".
4. **The Energy band is accepted and then read back nowhere.** `Band → Low` was submitted on screen 10; the
   Cockpit prints `Energy N/A`, the Advisor states "there is no band to read", and turn 1's correction form
   prefills the other three values but exposes no energy-state or band control at all. Whether the value is
   stored or dropped cannot be determined from the UI — and that is itself the finding: no screen the user
   can reach confirms it.
5. **Two screens give the same stat two different denominators.** Cockpit: `Speed 607 / N/A — No target was
   entered for this stat.` Event decision, same run, same moment: `Speed 607/1300`. One is the build target
   and the other the scenario cap, and neither screen labels which it is printing.
6. **With no target, the scenario caps vanish from the cockpit stat strip.** `N/A / N/A` replaces the
   `N/A / 1,300` the first pass saw, so §1.2's comparison of the run's stats against a 1300/1800 base is
   no longer available on the screen that exists to show it.
7. **Three instructions point at a screen that no longer exists.** `/training-runs/9` redirects to
   `/training-runs/9/cockpit`, yet the Career Result says the run record screen changes the status, the
   Training decision offers "Record the first turn **on the run screen**" linking to the Cockpit, and the
   deck step still asserts that the owned/rented flag has no column and stays in the draft, which screen 19
   contradicts by persisting it.
8. **One card prints two contradictory values under the same label.** The Unity Cup scenario card carries
   `Ruleset · N/A` in its fact block and `Ruleset · Team` in the description block beneath it.
9. **A blank spark row survives as counted data (verified on the adjacent run).** Run 8's Inheritance screen
   reports `BLUE 3 Sparks` where two real blue sparks were entered — the third being the empty trailing row
   the first pass recorded being written into its contract. The crash that hid this in the first pass is
   fixed, so the inflation is now on screen.

## Experiential findings

Where the UI works but costs the user clarity, repetition or a workaround.

1. **The run's position is still inexpressible.** YEAR and MONTH are derived from `Turn`, so a Senior Early
   October snapshot logged at the field's default becomes `Junior Year · Early January · Turn 1`, and the
   Timeline, the race surfaces and the mandatory-race logic all read that derived position as fact.
2. **§1.2's caps remain unenterable.** 1325, 1322 and 1368 exceed the 1300 maximum on both the target step
   and the turn form, and no screen asks for a cap at all.
3. **Grandparents take a name and sparks but no rank.** `Own rank (letters)` exists for the two parents
   only, so §1.5's B+ / A / UG / UG grandparent classes have nowhere to go.
4. **The letter-rank suggestion set cannot express `UG`.** It offers G..S, B+, A+, S+ — no SS, U, UU or UG.
   The field is editable, so typing the source's rank passed with no note that it is off the offered set.
5. **Rarity and Potential are named as missing without being reachable.** The Cockpit's badge says the
   trainee's rarity and potential level is unrecorded; the identity form beside it holds only Status,
   Scenario and Delete.
6. **The run's own costume is the unnamed one.** The trainee card lists "debut form" and a second bare
   `★★★SSR`, and shows the debut portrait, so `[Rosy Dreams]` cannot be picked out.
7. **The catalog's aptitudes still disagree with the client read** on Mile, Front runner and Late surger —
   identically in both passes — so a user checking the source against step 2 finds three mismatches.
8. **Recording §1.5 as written is a hundred clicks.** Every spark is one row of three controls behind an
   Add button with no bulk entry, no ordering by kind, and no visible count while editing, and the
   grandparent editors sit behind disclosures that must be opened per node.
9. **Deck comparison must be re-derived from the source.** The analysis prints catalog maxima and disclaims
   interpolation, so §1.4's "Client rows visible" column can only be checked by returning to the document.
10. **Scenario comparison is now lopsided.** Unity Cup carries a description, a ruleset, a planning focus and
    three mechanics; the other three carry `N/A` rows and `Not recorded`, so the cards no longer present
    like-for-like choices.
11. **Two readings of this pass disagree with two readings of the first and were not reconciled**: the
    trainee step's `Stored choice` (updated here, stale in pass one) and the Skills planner's hint ladder
    (disclaimed here, shown with a Confirmed marker in pass one). Both are flagged rather than assumed away.
12. **The Energy band select has no accessible name**, unlike its labelled siblings.

## Unrepresented source data

Source information with no appropriate UI representation for this run:

- Run position: Senior Year Early Oct; 5 turns left; 5 turns until the Unity Cup (§1.1 line 21).
- Rarity ★3 and Potential Level 2 (§1.1 line 20) — named as missing, not enterable.
- The `[Rosy Dreams]` costume identity, whose form is the unlabelled one.
- The growth-rate row +0/+10/+0/+20/+0 (§1.1 line 20; §7 item 2).
- The run's own per-stat caps 1325/1322/1368/1308/1800 and the whole §1.2 cap-composition question
  (lines 39-56, §8.5).
- The documented aptitude letters, which disagree with the catalog on three of ten.
- The ten-plus owned skills and Corner Recovery ○ (§1.3 lines 63-67).
- Card client levels, limit breaks, and the reached "Client rows visible" effect values (§1.4 lines 77-104).
- All six ancestors' ranks as letter classes on the grandparents, and `UG` on the parents' suggested set
  (screen 5's §1.5 lines 138-143).
- Four of the six blue sparks and every pink, green and white spark in §1.5's table (not entered; the
  fields now exist — see Walk completion).
- The pair's affinity grade (§1.5 states none; left unset and rendered `N/A`).
- The entire §1.6 Unity Cup state: team "Blue Bloom", motto "Dreaming Big", league 8th, four rounds won,
  finals pending, and both cleared and active goals.
- The §1.6 race-day card: both options, the Soft going, the fan ladder, the entry criteria, the "top
  contender" verdict.
- The `Floor Cleaning` banner and the three card avatars on the observed tile (§1.7 line 221).
- The Team Info numbers: 55 Unity Trainings, 6 Spirit Bursts, 5 Extreme, 20/20 members, the +30 ranking
  bonus, grades A/A/A/S/A (§1.8) — and therefore the run's place in §2.4's 10-12 burst band.
- The §1.9 roster and the guests' skill lists.
- The §2.1 hint-discount ladder as a displayed figure on this pass, and the §2.2/§2.3 pool, prices and
  recommended spend.
- The §2.4/§8.2 payout dispute is surfaced by the app rather than represented by it: the band table renders
  with its own caveat, which is the right treatment of an unsettled figure.

## Walk completion

- **Reached the end of the available flow: yes, for an Active run.** Twenty visits in order, from the
  Dashboard to the Race planner, which is the last surface the run exposes.
- **Where it stopped and why.** The flow terminates at the Career Result's designed "still running" state
  and at the Race planner; the Cockpit's remaining actions (Rest, Recreation, Scenario action) resolve to
  the Cockpit itself, and the Inheritance route renders but has no ancestry to show because of functional
  finding 1. Nothing further is reachable without changing the run's status, which the brief forbids.
- **Data intentionally not entered.** §1.5's remaining four blue sparks and every pink, green and white
  spark were not typed in. The fields exist and accepted the five sparks that were entered — including two
  on grandparents, which the first pass had nowhere to put — so the mechanism is proven and the remainder is
  volume rather than a gap. The pair's affinity grade was left unset because the source states none, and the
  Energy number was left unset because the source states none; the band was used instead.
- **Data entered but not retained by the application.** The whole step 4 ancestry — both parents, all four
  grandparents, both rank letters, the rented-parent flag and three sparks. That is functional finding 1,
  not a choice by this walk, and it is why run 9's contract reads `ANCESTRY · N/A`.
- **Persistence claims are limited to what a screen echoed back.** Stats, SP, fans, mood and the deck
  (including the Rented flag) are verified by read-back on the Cockpit, the Event decision, the Timeline and
  the run deck screen. The Energy band is explicitly **not** claimed to persist: no reachable screen reads
  it back. The ancestry is explicitly recorded as absent from the contract rather than as deleted from
  storage, because storage was not inspected.


