# UX walk audit: [Rosy Dreams] Rice Shower / Unity Cup (round 4)

**Audit ID:** `RS-UC-UXWALK-2026-10-24-04`

**Source document:** `docs/[Rosy_Dreams]Rice_Shower_Unity-Cup.md`

**Deliverable path note:** the procedure template names `docs/audits/rice-shower-unity-cup-ux-walk.md`, but that path is the existing round-1 audit and this task forbids editing prior audits. Rounds 1 to 3 already exist (`-ux-walk.md`, `-ux-walk-2.md`, `-ux-walk-3.md`), so this pass lands as round 4. Nothing in the prior audits was read for this walk; every observation below comes from this browser session.

## 7.1 Header

| Field | Value |
|---|---|
| Starting URL | `http://127.0.0.1:8000/` |
| Walk start | 2026-10-24 04:03 +08:00 (MPST) |
| Walk end | 2026-10-24 04:31 +08:00 |
| Browser | Chrome 156.0.0.0 (Playwright-driven), Windows 10 x64 |
| Viewport | 958 x 888 CSS px, DPR 1 |
| Devtools | Not open; Playwright MCP automation only. Console read via the MCP console API. |
| Application state | Already running; not restarted, no command issued. |
| Git commit | `f6018f879c7191bfe74be0d119ba1750b0b12a61`, working tree clean at start (`git status --short` empty) |
| App version in UI | None visible. The Dashboard header shows `Verified 2026-09-27 · GameTora scenarios.json`, which is data provenance, not an application version. |
| Pre-existing state | One active career already present: Rice Shower, Unity Cup, Junior Year Early January, Turn 1, run id 25. 7 Veterans, 12 runs total. No login, no session boundary. |

**Method statement.** Data entry was performed only through the UI: links, buttons, native selects, checkboxes and text/number inputs driven by the browser. No seeder, factory, direct SQL, tinker, artisan state-mutating command, API call, or devtools DOM mutation was used. `browser_evaluate` was used read-only to enumerate control labels/ids already on screen and, once, to click a nav link that the automation layer reported off-screen; that click is recorded in S15. Screenshots are corroboration; the written record is the audit. Console: 0 errors, 1 informational message across the whole walk.

### Run identity block

| Field | Value | Source |
|---|---|---|
| Trainee | Rice Shower, `[Rosy Dreams]` card | §1.1 line 37; costume form chosen on S03 |
| Rarity | ★3 | §1.1 line 37 |
| Potential / awakening level | Potential Lvl 2 | §1.1 line 37 |
| Scenario | Unity Cup | §1.1 line 36 |
| Snapshot point | Senior Year Early October | §1.1 line 37-39 |
| Turns remaining | 5 turns left, 5 turns until the Unity Cup | §1.1 line 38 |
| Lifecycle status | Mid-career, active, not completed | §1.1, §1.6; no completion claim anywhere in the source |
| Run number | None stated | source has no run number |
| Stats | Speed 607, Stamina 577, Power 549, Guts 736, Wit 397 | §1.2 lines 44-49 |
| Caps shown | 1325 / 1322 / 1368 / 1308 / 1800 | §1.2 lines 44-49 |
| Unspent SP | 173 | §1.2 line 51 |
| Mood | GREAT | §1.7 line 236 |
| Energy | Low band. Bar near a third, low-energy warning text; client prints no number | §1.7 lines 241-247 |
| Support deck | 6 cards, all SSR: 3 Speed (Tokai Teio, Nishino Flower, Biko Pegasus), 1 Stamina (Super Creek), 2 Guts (Matikanetannhauser, Haru Urara) | §1.4 lines 106-124 |
| Borrowed / friend card | Super Creek [Piece of Mind], 50/50, tagged Friends, borrowed from another account | §1.4 lines 114-118 |
| Skill count | 11 owned: 10 visible chips plus Corner Recovery ○ obtained; unique Blue Rose Closer at Lvl 3 | §1.3 lines 92-96 |
| Ancestors and sparks | 6 ancestors. Blue sparks: Vodka Power ★☆☆; Daiwa Scarlet (Legacy 1 side) Guts ★☆☆; Seiun Sky Stamina ★★★; Maruzensky Speed ★★☆; Daiwa Scarlet (Legacy 2 side) Speed ★★☆; Taiki Shuttle Power ★★★. Six green factors. Pink/white lists per §1.5 | §1.5 lines 165-189 |
| Race days already won | All four Unity Cup preseason rounds won (after Junior Late Dec, Classic Late Jun, Classic Late Dec, Senior Late Jun); 3 trainee goals cleared (Nikkei Sho top 3, Tenno Sho Spring 1st, Takarazuka Kinen top 3); race Log empty | §1.6 lines 198-205, 830-832 |
| Outstanding objectives | Place 1st in Arima Kinen (Senior Late Dec); Unity Cup finals (5 turns away); 30,755 fans to Top Star | §1.1 lines 38-39, §1.6 lines 203-205 |

**Primary pass scope.** Routes reached: `/`, `/career/setup/scenario`, `/career/setup/trainee`, `/career/setup/target`, `/career/setup/legacy`, `/career/setup/deck` (plus picker query states), `/career/setup/preflight`, `/training-runs/26/cockpit`, `/training-runs/26/training`, `/training-runs/26/races`, `/training-runs/26/skills`, `/training-runs/26/deck`, `/legacy`, `/training-runs`. The walk stopped at the Careers list (S16). A career has five turns left, so the flow has no natural end; I stopped when no further action could be taken without inventing a race result or a training outcome.

**Deviations from the one-pass rule.**

1. Returned from S07 to S04 through the Preflight screen's own `Edit Your target` link, because Preflight declared the target missing and Start career would otherwise proceed without it. The pass resumed at S04 and returned to S07 by the wizard rail.
2. Returned from S10 to S09 through the Races screen's own instruction, `Record the first turn through Training`, because the race screen refuses to operate with no logged turn. The pass resumed at S09.
3. No browser Back, no reload, no second tab, no flow restart. One mis-click opened the Slot 3 picker while aiming at Slot 2 (S06); it was closed without changing data and the correct slot was then opened.

**Environment mutations.** None: no resize, zoom, cache clear, cookie change, profile switch, login/logout or process restart. One accidental wrong-slot picker open, closed without change.

## 7.2 Screen ledger

| Screen ID | Route | Arrived from (screen / control) | Exited via (control) |
|---|---|---|---|
| S01 | `/` | entry point | `New Career` |
| S02 | `/career/setup/scenario` | S01 `New Career` | `Save and continue` after selecting `Unity Cup` |
| S03 | `/career/setup/trainee`, then `?direction=asc&search=Rice%20Shower&sortBy=name` | S02 `Save and continue` | `Select` on Rice Shower, then rail link `Your target not started` |
| S04 | `/career/setup/target` | S03 rail link | `Save target`, then rail link `Legacy not started` |
| S05 | `/career/setup/legacy` | S04 rail link | `Next: Support deck` |
| S06 | `/career/setup/deck` (picker states `?deck_slot=1..6&q=...`) | S05 `Next: Support deck` | `Next: Preflight` |
| S07 | `/career/setup/preflight` | S06 `Next: Preflight` | `Edit Your target` (returned to S04), then `Start career` |
| S08 | `/training-runs/26/cockpit` | S07 `Start career` | `Record the first turn through Training` |
| S09 | `/training-runs/26/training` | S08 CTA | nav `Races` |
| S10 | `/training-runs/26/races` | S09 nav | its instruction link, back to S09 |
| S11 | `/training-runs/26/cockpit` | S09 `Record turn` submission | nav `Races` |
| S12 | `/training-runs/26/races` | S11 nav | nav `Skills` |
| S13 | `/training-runs/26/skills` | S12 nav | nav `Deck` |
| S14 | `/training-runs/26/deck` | S13 nav | nav `Legacy` |
| S15 | `/legacy` | S14 nav `Legacy` | nav `Careers` |
| S16 | `/training-runs` | S15 nav | walk end |

Artifact note: one screenshot per screen was requested through the automation layer, which reported each save under `docs/audits/rice-shower-unity-cup-ux-walk-4/screens/`. On checking after the walk, no files exist under that path, so no screenshot is attached. The written record below is the evidence; the missing files are a tool limitation, not a missing observation.

## 7.3 Screen-by-screen walk

### S01 — Dashboard

#### Route and trigger
`http://127.0.0.1:8000/`. Entry point; no prior screen. Title `Dashboard`.

#### What the screen asks for
No inputs. It reads: sidebar (`Dashboard`, `New Career`, `Careers`, `Legacy Lab`, `Veterans`, `Support Cards`, `Skills`, `Review`, `Database`, `Settings`, `Data sources`); an `Active Career` region (Rice Shower ライスシャワー, `Unity Cup · Junior Year · Early January`, Turn 1, Energy `N/A`, Team Rank `N/A`, `71 turns to go`, link `Resume career`); `Quick Actions`; `Recent Veterans` (`Showing the 3 most recent of 7`); `Data Status` (Global data current, `Ruleset version: N/A`, Trainees 71, Skills 1,910, Support Cards 559).

#### Values entered + source trace
None.

#### Source data with no UI home
None on this screen. The source's mid-career state is not offered as an entry path here; `New Career` and `Import an existing career` are the two doors shown.

#### Friction / errors / dead ends
- Pre-existing active career is the same trainee and scenario as the source (Rice Shower, Unity Cup) at Junior Year Early January, run 25. A Trainer starting the source run creates a second, indistinguishable-looking career (confirmed later on S16).
- No application version or commit is visible anywhere on the page.
- `Energy N/A` and `Team Rank N/A` on the active career card are labelled as not recorded, not as zero.

#### Outcome / next screen
Clicked `New Career` → `/career/setup/scenario`.

### S02 — Choose a scenario

#### Route and trigger
`/career/setup/scenario`, from S01 `New Career`. `Step 1 of 6`.

#### What the screen asks for
Four radio cards, each with a heading and a description, plus a collapsed `Facts and mechanics` group:
- `URA Finale`, described as the Global launch baseline, three years of turns, no team system, no shop, no mid-scenario currency.
- `Unity Cup`, described as a team career: "facility level follows the team rank, a team race arrives every six months, and Spirit Bursts from teammates grant exclusive skills."
- `Trackblazer`, described as having "no fixed schedule of mandatory race goals".
- `Our Grand Concert`, described with its vocabulary (Performance, Promo Concert, Grand Concert, lessons, songs, concert techniques, Live Performance Expectations, Great Success).

`Save and continue` is disabled until a card is selected (`disabled` attribute observed). Link `Compare scenarios` goes to `/database/scenarios`. `Leave setup` goes to `/`.

#### Values entered + source trace
`Unity Cup` selected by clicking the radio. Source: §1.1 line 36. Selection state became `✓ Selected`, and `Save and continue` enabled.

#### Source data with no UI home
None on this screen.

#### Friction / errors / dead ends
None observed. The screen does not say whether the scenario can be changed after the run exists, and it offers no resume/import choice; a mid-career Trainer has already had to interpret the Dashboard's two doors.

#### Outcome / next screen
`Save and continue` → `/career/setup/trainee`.

### S03 — Choose a trainee

#### Route and trigger
`/career/setup/trainee`, from S02 `Save and continue`. Filtering produced `/career/setup/trainee?direction=asc&search=Rice%20Shower&sortBy=name`.

#### What the screen asks for
In order: `Search` (placeholder `Trainee name`); `Surface` select (`Any` disabled and selected, then `S` to `G`); `Distance` select with the same options; `Running style` select with the same options; `Unique skill` select with a long alphabetized list (`Any` disabled, then values such as `Angling and Scheming`, `Blue Rose Closer`, `Cut and Drive!`, ...); `Sort by` (`Name`, `Turf`, `Dirt`, `Sprint`, `Mile`, `Medium`, `Long`, `Front runner`, `Pace chaser`, `Late surger`, `End closer`); `Order` (`Ascending`, `Descending`); buttons `Filter` and `Clear filters`. The table has columns `Trainee`, `Aptitudes`, `Forms`, `Choose`. Each row offers a `Select` button, a `Profile` link, and, where a trainee has more than one outfit, a `Costume form` select.

Rice Shower row: `2 costume forms`; `Costume form` options `Not stated`, `[Rosy Dreams] (debut form)`, `[Vampire Makeover!]`; aptitudes rendered as Turf A Strong, Dirt G Very weak, Sprint E Weak, Mile C Neutral, Medium A Strong, Long A Strong.

#### Values entered + source trace
- Search `Rice Shower` → `Search` → source: title line 1 and §1.1 line 37.
- Result count changed to `1 trainee match.`
- Costume form `[Rosy Dreams] (debut form)` → `Costume form` → source: §1.1 line 37 (`[Rosy Dreams]` card).
- `Select` on the row. After selecting, the page header read `Trainee: Rice Shower`, and the list returned to `67 trainees match.` with the query string gone.

#### Source data with no UI home
None. The picker shows the catalog's aptitude letters, which is exactly the discrepancy the source's dated erratum (lines 78-88) records: Mile C here, where the frame read Mile B. The picker is not asserting the client's frame values.

#### Friction / errors / dead ends
- Selecting a trainee drops the active search/filter and reloads all 67 rows. The URL loses `search`, `sortBy` and `direction`.
- There is no forward control at the bottom of the picker. The only way onward is the wizard rail link `Your target not started`.
- The page never asks for a run number, start date or completion state, so it does not force a lifecycle category at this step.

#### Outcome / next screen
Clicked the rail link `Your target not started` → `/career/setup/target`.

### S04 — Your target

#### Route and trigger
`/career/setup/target`, from S03's wizard rail. Revisited once from S07 `Edit Your target`.

#### What the screen asks for
- `Purpose` select: `Not chosen yet` (selected), `Story Clear`, `Champions Meeting`, `Parent Farming`, `Skill Farming`.
- `Distance` select: `Not chosen yet`, `Sprint`, `Mile`, `Medium`, `Long`.
- `Surface` select: `Not chosen yet`, `Turf`, `Dirt`.
- `Style` select: `Not chosen yet`, `Front Runner`, `Pace Chaser`, `Late Surger`, `End Closer`.
- Help text: `The design also names a fifth purpose, Competitive Build, which this tool does not record yet.`
- Per-stat `Target` spinbutton and `Ceiling` spinbutton for Speed, Stamina, Power and Guts; Wit has no enterable ceiling and states `No enterable ceiling for this stat`.
- Help: `Target is the number you aim for this run. Ceiling is the highest the client shows; leave empty to keep the scenario's published figure.`
- `Skill priorities`: `Skill name` textbox (`Enter skill name`), `Add`, an ordered list with Move up / Move down / Remove, and the note that risk tolerance and per-skill marks are not recorded yet.
- `Save target`.
- The page states `No target is recorded yet. The fields below start empty rather than at zero.`

#### Values entered + source trace
Only ceilings and skill priorities were entered on the first pass; Purpose, Distance, Surface, Style, stat Targets stayed at `Not chosen yet`/blank.

| Value | Control | Source |
|---|---|---|
| `1325` | `Speed ceiling` | §1.2 line 44 (cap shown 1325) |
| `1322` | `Stamina ceiling` | §1.2 line 46 |
| `1368` | `Power ceiling` | §1.2 line 47 |
| `1308` | `Guts ceiling` | §1.2 line 48 |
| (Wit 1800 has no input; the page shows `Cap 1800`) | `Wit` row | §1.2 line 49 |
| `Pace Chaser Straightaways ◎` | `Skill name` → `Add` | §2.3 line 451 (recommended first buy) |
| `Kyoto Racecourse ○` | `Skill name` → `Add` | §2.3 lines 453-454 (recommended 81-SP pick for this race) |

After `Save target`, a `Summary` region appeared and read: `Purpose not chosen yet; distance not chosen yet; surface not chosen yet; style not chosen yet; Speed not entered, Stamina not entered, Power not entered, Guts not entered, Wit not entered; skills in order: Pace Chaser Straightaways ◎, Kyoto Racecourse ○.` The cap labels beside each stat changed from `Cap 1300` to the entered ceilings (`Cap 1325`, `Cap 1322`, `Cap 1368`, `Cap 1308`). The page header still read `Target: not chosen`. On the later rail it read `Your target done`.

On the revisit from Preflight, `Purpose` was set to `Story Clear` and saved. The summary then read `Purpose Story Clear; ... skills in order: none listed yet.` The previously saved two skill priorities were gone. The ceilings were still present (`1325`, `1322`, `1368`, `1308`).

#### Source data with no UI home
- **No UI representation found:** Potential Lvl 2. Not asked here, and later (S08) the identity editor offers rarity only. See U-01.
- **No UI representation found:** the run's own cap composition reasoning (the shortfall against spark arithmetic, §1.2 lines 56-73). The form stores the caps, not their derivation, which is fair, but no note field exists to carry the open question.
- `Competitive Build` is named in help text but absent from the select (E-12).

#### Friction / errors / dead ends
- `Save target` gives no success message. The only confirmation is the new Summary region and the changed cap labels; the breadcrumb stays `Target: not chosen`, which reads as unsaved.
- Re-entering the screen and saving Purpose silently deleted both saved skill priorities. Observable before/after summary text is quoted above.
- Saving without a Purpose left the step marked `done` on the rail but `Missing` on Preflight (S07).
- Wit's cap is displayed but cannot be overridden; the source's 1800 happens to equal the published figure, so nothing was lost here, but a run with a raised Wit cap could not record it.
- There is no freeform notes field.

#### Outcome / next screen
After the first save, advanced via the rail link `Legacy not started`. Later returned from S07 and resumed the flow via the rail link `Preflight`.
### S05 — Legacy

#### Route and trigger
`/career/setup/legacy`, from S04's rail link `Legacy not started`. `Step 4 of 6`.

#### What the screen asks for
Intro copy: "The two parents this career will be bred from are entered here as you read them in the client, before any run exists. Their own two ancestors each are part of the record, so the diagram holds six nodes." A note reads `Record only. This screen stores and compares what you enter. It does not compute inheritance.`

Six nodes:
- `Parent A` and `Parent B`, each with an `Assign to Parent A/B` select listing catalog records (`Not chosen`, `Vodka [Wild Top Gear] · 14`, `Vodka [Wild Top Gear] · 13`, `Daiwa Scarlet [Peak Blue] · 12`, `Haru Urara [Bestest Prize ♪] · 11`, `Mayano Top Gun [Scramble☆Zone] · 10`, `Maruzensky [Formula R] · 9`, `Maruzensky [Formula R] · 8`); `Own rank (letters)` text combobox; `Own rank (stars)` number; checkbox `Rented for the run`; a `Sparks` group with `Add Spark to Parent A/B`.
- `Grandparent A1`, `A2`, `B1`, `B2`, each with a free-text name combobox (`Enter the name as you read it. Blank stays N/A.`), `Own rank (letters)`, `Own rank (stars)`, and an `Add spark to grandparent` control.
- A Spark row asks `Kind` (`Blue`, `Pink`, `Green`, `White`, `Scenario`), `Applies to` (`Stat`, `Aptitude`, `Skill`), `Target` (`Not recorded`, `Speed`, `Stamina`, `Power`, `Guts`, `Wit`), `Stars` (number), and `Remove Spark`.
- `Pair` → `Affinity` select (`Not recorded`, `△`, `○`, `◎`), with `The grade you read for the pair, not a score. This tool does not price it.`
- `Save Legacy`, `Back: Target`, `Next: Support deck`.
- Every node states `Spark chance N/A` with a tooltip that the tool holds no sourced star-roll table.

#### Values entered + source trace

| Value | Control | Source |
|---|---|---|
| `Vodka [Wild Top Gear] · 14` | `Assign to Parent A` | §1.5 lines 167-173 (Legacy 1 Vodka [Wild Top Gear]); the instance index 14 is a UI-required choice (see Improvisation log #3) |
| `B+` | Parent A `Own rank (letters)` | §1.5 line 167 |
| Blue, Stat, Power, `1` | Parent A spark row | §1.5 line 177 (Vodka Power ★☆☆) |
| `Maruzensky [Formula R] · 9` | `Assign to Parent B` | §1.5 line 170 (Legacy 2 Maruzensky [Formula R]); index 9 is a UI-required choice (#4) |
| `UG` | Parent B `Own rank (letters)` | §1.5 line 170 |
| `Rented for the run` checked | Parent B checkbox | §1.5 line 170 (`flagged Guest`), §1.4 lines 114-118 (friend card) |
| Blue, Stat, Speed, `2` | Parent B spark row | §1.5 line 180 (Maruzensky Speed ★★☆) |
| `Daiwa Scarlet [Peak Blue]`, `B+` | Grandparent A1 name, letters | §1.5 line 168 |
| `Seiun Sky [Reeling in the Big One]`, `A` | Grandparent A2 name, letters | §1.5 line 169 |
| `Daiwa Scarlet [Peak Blue]`, `UG` | Grandparent B1 name, letters | §1.5 line 171 |
| `Taiki Shuttle [Wild Frontier]`, `UG` | Grandparent B2 name, letters | §1.5 line 172 |

After `Save Legacy` the page replaced the empty-state sentence with `This setup already holds a Legacy. Saving below replaces it whole; a Spark you remove here is gone, not hidden.` Parent A read `Rank B+`; Parent B read `Rank UG` plus `Rented from a friend`; the two blue sparks were listed as `Blue Spark, Power, 1 star` and `Blue Spark, Speed, 2 stars`.

Not entered: the remaining four blue sparks, every pink, green and white spark, grandparent star counts (the source gives letters for ancestry ranks, not stars), and `Affinity` (the source states no pair grade).

#### Source data with no UI home
- **No UI representation found:** pair affinity grade. The control exists but the source provides no value, so it stayed `Not recorded`; the omission is on the source side, not the UI's.
- **No UI representation found:** the six green factors' names (Cut and Drive!, Angling and Scheming, Resplendent Red Ace ×2, Red Shift/LP1211-M, Shooting for Victory!, §8.5 lines 1191-1197). The spark form's `Kind = Green` has `Applies to` set to Stat/Aptitude/Skill and a target, but no field for a unique-skill name paired with a star grade, so a green factor cannot be recorded as the source reads it.
- Blue/pink/white sparks do have a home; they were not entered in this walk except the two blues above (walk limitation, not an omission by the UI).

#### Friction / errors / dead ends
- Choosing a catalog `Assign` record fills only the display name; rank and sparks stay blank and must be retyped. The record already exists in the tool's own catalog, so the retype is avoidable work.
- Adding one spark requires: open the ancestor/spark form, set `Kind`, `Applies to`, `Target`, `Stars`, then click `Add Spark` again to add the next row. A six-ancestor tree with 30+ sparks is a long form with no bulk entry.
- The grandparent `Add spark` control's first click opened a collapsed region that still said `No sparks recorded`; a second, separate button inside the region had to be clicked before any spark fields appeared. This cost one extra interaction per grandparent.
- Two catalog entries for the same card and epithet appear with different numeric suffixes (`Vodka ... · 14` and `· 13`, `Maruzensky ... · 9` and `· 8`); the suffix is not explained, and the source does not say which instance this run used.
- Saving replaces the stored Legacy whole, and the page says so only after the first save.

#### Outcome / next screen
Clicked `Next: Support deck` → `/career/setup/deck`.

### S06 — Support deck

#### Route and trigger
`/career/setup/deck`, from S05 `Next: Support deck`. Each picker state appended `?deck_slot=N&q=...`.

#### What the screen asks for
Six slots. Slots 1 to 5 are plain; Slot 6 is labelled `Slot 6 · Friends` with `Friend slot`. Each slot carries:
- `Ownership` with current value (`N/A` until chosen) and two buttons, `Owned` and `Rented`.
- `Choose a card in Slot N` (opens a dialog).

Dialog `Choose a card for Slot N`: a seven-type summary, `Type` select (`All`, `Speed`, `Stamina`, `Power`, `Guts`, `Wit`, `Pal`, `Group`), `Name contains` searchbox, `Filter`, `Close`, and a result list where each row is `Name [Epithet]`, type, and `Equip`.

Page copy: `A deck is the six cards equipped before the career starts. Position six is the friend slot, and that role belongs to the position: any card may sit in it.` and `The owned or rented flag is kept in this setup draft, and Preflight writes it onto the run as one value per slot, where it stays after a reload. A slot you have not classified reads as N/A rather than as Owned.` `Save deck`, `Back: Legacy`, `Next: Preflight`.

#### Values entered + source trace

| Slot | Card | Ownership | Source |
|---|---|---|---|
| 1 | `Tokai Teio [Dream Big!]` | Owned | §1.4 lines 108, 114-118 |
| 2 | `Nishino Flower [Even the Littlest Bud]` | Owned | §1.4 line 109 |
| 3 | `Biko Pegasus [Double Carrot Punch!]` | Owned | §1.4 line 110 |
| 4 | `Matikanetannhauser [Just Keep Going]` | Owned | §1.4 line 112 |
| 5 | `Haru Urara [Urara's Day Off!]` | Owned | §1.4 line 113 |
| 6 · Friends | `Super Creek [Piece of Mind]` | Rented | §1.4 lines 111, 114-118 |

Slot order: the source's table order puts Super Creek fourth, but the same section says the sixth slot is the friend card (§1.4 lines 114-118), so the friend slot was filled last. That reorder is recorded in the Improvisation log (#7).

The page rendered each equipped card with its catalog maximum effect set and a link label: Haru Urara read `Scenario Link`; the other five read `Not a Scenario Link`. This matches the source's derivation (§1.4 lines 119-124).

#### Source data with no UI home
- **No UI representation found:** the borrowing account or owner identity of the friend card. `Rented` records that it is external but not whose it is (U-08).
- Card levels (35/35, 30/30, 50/50) and the client's per-level effect rows have no field; the slot shows only the catalog maximum set. The source distinguishes "what the card can be" from "what this run has" (§1.4 lines 100-104). The run's current card level is not representable.
- Deck slot ordering has no stated meaning in the UI; the source does not give slot numbers either.

#### Friction / errors / dead ends
- Searching `Tokai Teio [Dream Big!]` (the full printed name) and pressing `Filter` returned `No support card matches that filter.` Searching `Tokai Teio` returned four cards including the target. The search does not match the bracketed epithet as printed on the row.
- Pressing `Filter` re-navigates the page (URL gains `q=`) and closes the dialog. The slot must be reopened for every search and every card. Six cards took 18 dialog/navigation steps.
- The first `Choose a card` after Slot 1 was filled resolves to Slot 3 by DOM position, not Slot 2; a user clicking by position without checking the slot heading can equip the wrong slot. I hit this once and closed the dialog without changing data.
- Ownership is a separate decision from card choice and defaults to `N/A`; equipping six cards and pressing `Save deck` can store six unclassified slots, as the page's own sentence warns.
- `Clear filters` exists on the trainee picker but not in the deck dialog; the only reset is editing the search text and filtering again.

#### Outcome / next screen
All six cards equipped and ownership set (5 Owned, 1 Rented), then `Next: Preflight` → `/career/setup/preflight`.

### S07 — Review and start (Preflight)

#### Route and trigger
`/career/setup/preflight`, from S06 `Next: Preflight`. `Setup · Step 6 of 6`.

#### What the screen asks for
`The five entered steps` with a status line and one row per step:
- `✓ Entered Scenario · Unity Cup` + `Edit`
- `✓ Entered Trainee · Rice Showerライスシャワー` + `Edit`
- `✗ Missing Your target · Not entered` + `Edit`
- `✓ Entered Legacy · No Affinity grade read · 2 of two parents recorded` + `Edit`
- `✓ Entered Support deck · 6 of six positions carry a card` + `Edit`

Status line: `1 step needs attention`. `Before you start` explains that the owned/rented flag is written per slot and an unchosen flag is stored as no flag. `Ruleset version: N/A` with a tooltip that no source defines one. Buttons: `Back`, `Start career`.

#### Values entered + source trace
None. This screen is read-only plus the two buttons and the Edit links.

#### Source data with no UI home
None new; it is a review screen, and it omits the entered caps and skill priorities from the review entirely (only "Not entered" for the target).

#### Friction / errors / dead ends
- The target is reported `Missing` even though S04 showed a saved Summary, entered ceilings and a `done` rail mark.
- `Start career` is enabled and does accept the click with a missing step; the attention line does not gate the action.
- The review does not show the entered caps, skills, parent names or deck composition, so a Trainer cannot verify transcription from this screen.
- Heading says `The five entered steps` while the wizard has six steps (Preflight is the sixth).
- The Trainee row prints `Rice Showerライスシャワー` with no space between the English and Japanese names.

#### Outcome / next screen
Clicked `Edit Your target` → S04. After the Purpose edit, returned by the rail to Preflight (still `Missing`), then clicked `Start career` → `/training-runs/26/cockpit`.

### S08 — Cockpit, first visit (new run 26)

#### Route and trigger
`/training-runs/26/cockpit`, from S07 `Start career`. The run id 26 appeared for the first time in this URL; no earlier screen displayed it.

#### What the screen asks for
Read-only plus three editor groups:
- Career header: `Rice Shower ライスシャワー`, `Unity Cup`, status `Active`; career nav (`Cockpit`, `Training`, `Races`, `Skills`, `Events`, `Legacy`, `Deck`, `Timeline`, `Result`); `Result: Available when the career is completed.`
- Run summary: Turn 0; Energy, Mood, Fans, Skill Points all `N/A`; `No recommendation. See the advisor.`
- `Edit identity` (collapsed): status select (`Active`, `Completed`, `Retired`), scenario select (`No scenario set`, `URA Finale`, `Unity Cup`, `Trackblazer`, `Our Grand Concert`), rarity select (`Not stated`, `★1`, `★2`, `★3`).
- `Edit team state` (expanded): `Team name`, `Team motto`, `League placement`, `Preseason wins`, `Unity Trainings`, `Spirit Bursts`, `Extreme Bursts`; `Save team state`.
- `Stats and state` with five stats; `No build target was set for this run.`; Energy/Mood/Fans/Skill Points.
- `Advisor` with `Objectives` and `Current priority`; the advisor declines because Energy is unrecorded.
- `Actions` grid (`Training`, `Race`, `Rest`, `Recreation`, `Scenario action`, `Event`, `Inheritance`).
- `Scenario` panel: Team Race, Team Rank, team stat grades, Spirit Burst, Spirit, burst count bands, team race coverage.
- `Races in this run`: `No race is recorded for this run yet`.

Rarity and potential: the header shows `N/A` with `The trainee's rarity and potential level has not been recorded for this run.`

#### Values entered + source trace

| Value | Control | Source |
|---|---|---|
| `Blue Bloom` | `Team name` | §1.6 line 198 |
| `Dreaming Big` | `Team motto` | §1.6 line 198 |
| `8` | `League placement` | §1.6 line 198 (`league placement 8th`) |
| `4` | `Preseason wins` | §1.6 lines 199-201 (all four preseason rounds won) |
| `55` | `Unity Trainings` | §1.8 line 264 |
| `6` | `Spirit Bursts` | §1.8 line 265 |
| `5` | `Extreme Bursts` | §1.8 line 266 |

Clicked `Save team state`; the inputs retained the values on the same page. Cross-screen persistence was verified later on S11, where the cockpit read `Spirit Bursts 11 normal plus Extreme`, i.e. 6 + 5.

#### Source data with no UI home
- **No UI representation found:** Potential Lvl 2 (U-01).
- **No UI representation found:** team stat grades Speed A, Stamina A, Power A, Guts S, Wit A. The panel itself states `No column records a team stat grade. The client prints these letters on its Team Info panel, and this tool has no input that stores them.` (U-02)
- **No UI representation found:** Team Rank S. `Edit team state` has league placement but no rank field; the Scenario panel shows `Team Rank N/A` / `not yet recorded` (U-03).
- **No UI representation found:** Current Spirit gauge (U-04).
- **No UI representation found:** the two teammate rows clipped from the source roster (§1.9 lines 350-351). The tool has no roster editor at all.

#### Friction / errors / dead ends
- New run opens at Turn 0 with every value `N/A`, so the first cockpit is a wall of absence. The only path forward is the CTA at the bottom of the advisor column, `Record the first turn through Training`.
- The run identity knows the trainee, scenario and rarity-pending state, but not year or month; those become readable only after a turn is recorded.
- `No build target was set for this run` contradicts what S04 stored and S07 partially acknowledged.
- `Result` is a disabled nav item with a tooltip, not a link.
- Two `Edit` groups (`Edit identity`, `Edit team state`) are collapsed `<details>`; their inputs exist in the DOM while collapsed.

#### Outcome / next screen
Clicked `Record the first turn through Training` → `/training-runs/26/training`.

### S09 — Training decision

#### Route and trigger
`/training-runs/26/training`, from S08 CTA. Revisited from S10's instruction link.

#### What the screen asks for
First visit (no turn recorded): a `No turn recorded yet` region with a link back to the Cockpit; five training cards (`Speed`, `Stamina`, `Power`, `Guts`, `Wit`), each with `Expected gains` and `Energy cost` (`E−28 … E−17`, Wit `E−0`), `Train` and `Inspect details`; and the `Record the turn` form:
- `Turn *` number, default `1`
- `Speed *`, `Stamina *`, `Power *`, `Guts *`, `Wit *` numbers, empty
- `Skill Points`, `Fans` numbers, empty
- `Energy` radios: `Exact`, `Band` (checked only after clicking), `Not recorded`; `Band` reveals a select `Low`/`Mid`/`High`
- `Mood` select: `not recorded` (selected), `GREAT`, `GOOD`, `NORMAL`, `BAD`, `AWFUL`
- `Outcome *` select: `Choose` (selected), `Success`, `Failure`
- collapsed `Facility levels this turn`: Speed, Stamina, Power, Guts, Wit (number, min 1 max 5), plus a failure-rate number
- collapsed `Preview readings`: per-stat gains plus SP
- buttons `Rest`, `Recreation`, and `Record turn 1`
- copy: `Every number below is what the client shows after the turn resolves. This tool does not project them, so an empty field is the honest state and the write refuses a turn that has not been read.`

#### Values entered + source trace

| Value | Control | Source |
|---|---|---|
| `67` | `Turn *` | Derived: the career is 72 turns and the source says `5 turns left` (§1.1 line 38), so 72 − 5 = 67. The source states no turn number; see Improvisation log #8. |
| `607` | `Speed *` | §1.2 line 44 |
| `577` | `Stamina *` | §1.2 line 45 |
| `549` | `Power *` | §1.2 line 47 |
| `736` | `Guts *` | §1.2 line 48 |
| `397` | `Wit *` | §1.2 line 49 |
| `173` | `Skill Points` | §1.2 line 51 |
| `209245` | `Fans` | §1.1 line 38 |
| `Band` + `Low` | `Energy` | §1.7 lines 241-247 (no numeric Energy; bar near a third with low-energy warning) |
| `GREAT` | `Mood` | §1.7 line 236 |
| `Success` | `Outcome *` | **Not present in source — UI-required choice.** The source's turn is a race decision with no outcome. |
| `4/4/4/5/4` | facility levels | §1.7 line 251 |
| `39` | failure rate | §1.7 line 251 |
| `18` / `8` / `6` | preview Speed / Power / SP | §1.7 line 250 |

Clicked `Record turn` → `/training-runs/26/cockpit` (S11).

#### Source data with no UI home
- **No UI representation found:** the year/month pair as an input. It is derived from the turn (verified: turn 67 produced `Senior Year`/`Early October` on S11), which is accurate for this career but means a snapshot can only be entered by choosing a turn number.
- **No UI representation found:** a turn state of "pending" or "decided but not resolved". `Outcome *` forces `Success` or `Failure` (F-07).
- **No UI representation found:** the scheduled race itself (Kyoto Daishoten) as the content of this turn. The training form can only record a training, rest or recreation; the race screen refuses until a turn exists (S10).

#### Friction / errors / dead ends
- On the first visit, all five training cards show `Expected gains N/A` because no facility level exists yet, and the facility-level inputs only exist inside the turn form further down the same page. The preview cannot be read before the turn is recorded.
- `Outcome *` has no third state. Recording a snapshot forced a result the source does not state.
- The form is labelled `Record turn 1` while Turn can be overridden; after typing 67, the button label changed to `Record turn 67`.
- Energy exact/band/unknown is a three-way radio plus a conditional band select; the band defaults to `Mid` as soon as `Band` is chosen, so an unreflective submit would record `Mid` where the source is low.
- `Rest` and `Recreation` are separate buttons above the form. Their relationship to the form (do they submit a turn too?) is not explained.
- No validation copy is visible until submit; the walk did not submit an invalid form, to avoid orphaned state.

#### Outcome / next screen
Clicked `Record turn 67`; landed on the Cockpit, which is S11.
### S10 — Race decision, first visit (no turn logged)

#### Route and trigger
`/training-runs/26/races`, from S09 nav `Races`.

#### What the screen asks for
Nothing usable. It renders `The turn being decided` with `N/A`; a `Mandatory races` list (`Junior Make Debut`, `URA Finals Qualifier`, `URA Finals Semifinal`, `URA Finals Final (Aoharu)`); a `Readiness` region with `N/A No readiness band is computed for this race.`; and `Races to decide between` with `Calendar race` (pressed) and `Race not on the calendar` buttons plus a `Plan the whole calendar` link. The blocking sentence is: `No turn has been logged yet, so there is no turn to decide about. Record the first turn through Training, then this screen opens on the next one.`

#### Values entered + source trace
None.

#### Source data with no UI home
The source's race day (Kyoto Daishoten and Mile Championship Nambu Hai, §1.6 lines 210-216) cannot appear because the screen has no turn.

#### Friction / errors / dead ends
- The screen is a dead end for this run state: its only actionable control is the `Calendar race` / `Race not on the calendar` toggle pair, and both sit above a message saying there is no turn.
- Every mandatory race is a URA Finals race, in a Unity Cup career.
- The nav item `Result` is a link on this page (it was disabled on S08); its tooltip behaviour was not exercised.

#### Outcome / next screen
Followed the screen's own instruction link to Training (S09 again).

### S11 — Cockpit, second visit (turn 67 recorded)

#### Route and trigger
`/training-runs/26/cockpit`, from S09 `Record turn 67`.

#### What the screen asks for
Same structure as S08, now populated. The `Correct a turn by hand` region appears with `Correct turn 67 by hand`.

#### Values entered + source trace
None new. This screen is the read-back of the S09 submission.

#### Source data with no UI home
Same inventory as S08 (potential level, team rank, team grades, Current Spirit).

#### Friction / errors / dead ends
None observed on this visit. The data reads correctly against the source except for the items above.

#### Outcome / next screen
The screen rendered: Year `Senior Year`, Month `Early October`, Turn `67`, Energy `Low band`, Mood `GREAT`, Fans `209,245 · Star · 30,755 to next`, Skill Points `173`, stats 607 / 577 / 549 / 736 / 397, Spirit Bursts `11 normal plus Extreme`. Clicked nav `Races` → S12.

### S12 — Race decision, second visit (turn 67 recorded)

#### Route and trigger
`/training-runs/26/races`, from S11 nav `Races`.

#### What the screen asks for
- `The turn being decided` header.
- The same four mandatory races.
- `Races to decide between` with `Calendar race` (pressed) / `Race not on the calendar`, `Plan the whole calendar`.
- Seven race cards, each with grade, year, turn, a fan gate, and rows for `GRADE`, `DISTANCE`, `DISTANCE BAND`, `SURFACE`, `RUNNING STYLE`, `FAN GAIN`, `REWARD`, `SKILL POINTS`, `SCENARIO REWARD`, `ESTIMATED WIN PROBABILITY`; each opens `Details and actions for <race>` with a `Close` and an `Enter Race` button. Races listed: `Brazil Cup`, `Cassiopeia Stakes`, `Fuji Stakes`, `Lumiere Autumn Dash`, `Muromachi Stakes`, `Swan Stakes`, `Tenno Sho (Autumn)`.
- Almost every detail row reads `N/A` with a tooltip explaining why the tool holds no value.

#### Values entered + source trace
None. No `Enter Race` was clicked: the source's turn is a race decision whose result is not recorded, and `Enter Race` is a commit action.

#### Source data with no UI home
- **No UI representation found:** the source's actual race day (Kyoto Daishoten G2, Kyoto Turf 2400m Medium, Soft going, 6,700 fans on a win; Mile Championship Nambu Hai G1, Morioka Dirt 1600m, 6,000 fans; the client's `Recommended` flag and `top contender` verdict). None of these races is offered, so the card cannot be represented (U-05).
- **No UI representation found:** a race that is scheduled but not yet run. The only per-race action is `Enter Race` (E-09).

#### Friction / errors / dead ends
- The header reads `Senior Year · Late October · Turn 20`. The cockpit on the same run reads `Senior Year · Early October · Turn 67`. The next turn after 67 is 68 (Late October), so the month is plausible but the turn number 20 is not.
- Every race detail dialog is dominated by `N/A` rows; the seven cards repeat the same ten-row pattern.
- The mandatory-race warning says `! Next obligation: Junior Make Debut, Junior turn 12. It reads "Not recorded yet".` at turn 67, so the run is told its earliest obligation is still next while its own date is far past it.
- The screen offers no way to record the pending Arima Kinen goal or the Unity Cup finals.

#### Outcome / next screen
Clicked nav `Skills` → S13.

### S13 — Skills planner

#### Route and trigger
`/training-runs/26/skills`, from S12 nav.

#### What the screen asks for
- `Your target`: `No build target is set on this run, so the fit cells below compare against nothing. This screen reorders the priorities a target names; it does not add any.`
- `Skill Point coverage`: `This run has no build target, so no skill is Required and there is nothing to sum.`
- `Hint discounts`: `Hint discounts are not modelled.`
- Groups `! Required`, `+ Available`, `● Learned`, `◇ Inherited`; the Inherited group states no column holds inherited skills.
- `Add a skill`: `Skill to add` select with a very long skill list; many entries carry a cost in `CC` (e.g. `1,500,000 CC`). `Add skill`.
- After a skill is added: an acquisition-state select with `Suggested`, `Acquired`, `Skipped`, a `Save acquisition status` button, and a conditions block (`DISTANCE`, `SURFACE`, `STYLE`, `COURSE`, `WEATHER`, `GROUND`, `PHASE`, each `Not recorded`).

#### Values entered + source trace
`Blue Rose Closer` from the `Skill to add` list, added, then state `Acquired` and `Save acquisition status`. Source: §1.3 lines 92-94 (her unique, Lvl 3, owned). The skill moved from `+ Available` to `● Learned` and read `Recorded: Acquired`. The cost cell read `N/A SP`. No skill level was entered; the UI offers none.

The remaining ten owned skills from §1.3 were not entered (walk limitation).

#### Source data with no UI home
- **No UI representation found:** skill levels. Blue Rose Closer's `Lvl 3` and every hint level in §1.3, §2.1 has no field (U-06).
- **No UI representation found:** hint discounts. The screen states `Hint discounts are not modelled` (U-07).
- **No UI representation found:** the owned/unowned distinction is representable (`Acquired` / `Suggested` / `Skipped`), but the unique-skill marker `✦ Unique` is display-only and there is no awakening level.

#### Friction / errors / dead ends
- With no build target, the screen cannot rank or save priorities: `Nothing to save: a run with no build target has no priority list to reorder.` The target data entered on S04 is not present on the run.
- Skill costs are printed in `CC` while the run tracks Skill Points; the same screen says the run's SP coverage cannot be computed. The unit mismatch is unexplained.
- Adding a skill requires selecting from a single long native select rather than searching by name.

#### Outcome / next screen
Clicked nav `Deck` → S14.

### S14 — Run deck

#### Route and trigger
`/training-runs/26/deck`, from S13 nav.

#### What the screen asks for
The same six-slot deck view as S06, read-only in this visit. Each slot shows the equipped card, its SSR rarity, type, `Scenario Link` / `Not a Scenario Link`, and the catalog effect set.

#### Values entered + source trace
None.

#### Source data with no UI home
Card level (35/35, 30/30, 50/50) is still absent; the effect set is always the catalog maximum, not the level-reached values the client shows (§1.4 lines 100-104).

#### Friction / errors / dead ends
- The nav link `Legacy` on this page points to `/legacy`, while the same nav item on the cockpit pointed to `/legacy/26` (F-11).
- Ownership flags are not shown in this read-only view, so a Trainer cannot confirm the `Rented` friend slot from this screen.

#### Outcome / next screen
Clicked nav `Legacy` → `/legacy`.

### S15 — Legacy Lab

#### Route and trigger
`/legacy`, from S14 nav `Legacy`. Title `Legacy Lab`.

#### What the screen asks for
A reference/library screen: `Veteran library`, `Trainee` filter (All, Special Week, Tokai Teio, ..., Rice Shower, ...), `Scenario` filter (All, URA Finale, Unity Cup, Trackblazer, Our Grand Concert), `Tag`, `Filter`; `7 of 7 Veterans`; cards for completed runs with a `Compare` action; filter `Form Counted 1..4`.

#### Values entered + source trace
None. The navigation itself used `browser_evaluate` to click the link after the automation layer reported it not visible; no value was injected.

#### Source data with no UI home
The run 26 Legacy record (Vodka and Maruzensky parents, four grandparents) does not appear here at all; the page counts only Veterans.

#### Friction / errors / dead ends
- The career nav's `Legacy` item left the run context. The run-scoped legacy page seen earlier (`/legacy/26`) was not reachable from this page.
- The library shows 7 Veterans, none of them the run under test. An in-progress career is correctly excluded, but there is no indication of where its recorded Legacy lives.
- `Compare` was not exercised.

#### Outcome / next screen
Clicked nav `Careers` → `/training-runs`.

### S16 — Careers list

#### Route and trigger
`/training-runs`, from S15 nav. Title `Careers`.

#### What the screen asks for
`Import a historical career`, `New career`, `12 runs total`, and run rows showing trainee, `Scenario`, status, started date and `Turn ~11 / 24`.

#### Values entered + source trace
None.

#### Source data with no UI home
None new.

#### Friction / errors / dead ends
- The new run appears as `Rice Shower / Unity Cup / Active / Started 2026-10-23 / Turn ~11 / 24`. The cockpit for the same run reads Turn 67, and the career was introduced on the Dashboard as a 72-turn career. The row's turn figure contradicts both.
- The pre-existing run 25 renders as an identical-looking `Rice Shower / Unity Cup / Active / Started 2026-10-15 / Turn ~11 / 24` row. No run id is shown, so the two cannot be told apart from this screen.
- The winner of the walk's setup (`run 26`) was never named on screen; its id appeared only in the URL on S08.

#### Outcome / next screen
Walk stopped here. No further forward action was required by the source run.
## 7.4 Improvisation log

| # | Screen ID | Field / control | Value chosen | Why this value | Alternatives rejected |
|---|---|---|---|---|---|
| 1 | S04 | `Purpose` | `Story Clear` | The run's outstanding work is scenario goals (Arima Kinen, Unity Cup finals), and Preflight treats the target as missing until a purpose exists. | `Champions Meeting`, `Parent Farming`, `Skill Farming` (no source basis); leaving blank (kept the step `Missing`) |
| 2 | S04 | `Purpose`, `Distance`, `Surface`, `Style` on first save | left at `Not chosen yet` | The source states no forward target; the first save recorded only the caps and skill priorities. | Guessing Medium/Turf/Pace Chaser from aptitudes, which would invent intent |
| 3 | S05 | `Assign to Parent A` | `Vodka [Wild Top Gear] · 14` | The source names Vodka [Wild Top Gear] but gives no instance suffix; the first matching option was taken. | `Vodka [Wild Top Gear] · 13` (same card and epithet) |
| 4 | S05 | `Assign to Parent B` | `Maruzensky [Formula R] · 9` | Same reason; the first matching option was taken. | `Maruzensky [Formula R] · 8` |
| 5 | S05 | Spark rows | Only the two blue sparks (Power 1★, Speed 2★) | These are the two parents' blue sparks in §1.5; the full 30+ spark tree would take dozens of form interactions and was out of the walk's budget. | Entering every blue, pink, green and white spark (would still not close, because green factors have no name+star field) |
| 6 | S05 | Grandparent `Own rank (stars)` | left blank | §1.5 gives ancestor ranks as letters (B+, A, UG), not star counts. | Entering the letter as a star number (wrong unit) |
| 7 | S06 | Slot order | Super Creek in `Slot 6 · Friends`; Teio, Flower, Pegasus, Tannhauser, Urara in slots 1-5 | §1.4 states the sixth slot is the friend card; the table's row order is not a slot order. | Keeping the §1.4 table order and putting Super Creek in slot 4, which would contradict the same section |
| 8 | S09 | `Turn *` | `67` | Derived from a 72-turn career and `5 turns left` in §1.1: 72 − 5 = 67. The tool then derived Senior Year / Early October, matching the source. | Turn 1 (would place the run in Junior January), turn 68 (would place it one turn past the snapshot) |
| 9 | S09 | `Outcome *` | `Success` | The form requires `Success` or `Failure`; the source's turn is an unresolved race decision, so no outcome exists. Success is the only choice that does not assert a failure the source never mentions. | `Failure`; leaving `Choose` (the submit would be refused) |
| 10 | S09 | Facility levels, failure rate, preview gains | Speed 4, Stamina 4, Power 4, Guts 5, Wit 4; failure 39; +18 Speed / +8 Power / +6 SP | §1.7 records these on the training screen at the same snapshot; they are the only per-facility readings available. They describe a training tile, while the snapshot's turn is a race decision. | Leaving them empty (allowed, but would drop §1.7 entirely) |
| 11 | S09 | `Energy` band | `Low` | §1.7: bar near a third and the low-energy warning. The client prints no number. | `Mid` (the default once `Band` is chosen), `Exact` (would invent a number), `Not recorded` (would contradict the warning) |
| 12 | S13 | Learned skills | Only `Blue Rose Closer` | One representative of the owned set; the screen has no level field, so entering all eleven would still not capture §1.3. | Entering all eleven owned skills |

## 7.5 Findings register

| ID | Type | Screen(s) | Short description | Blocking? |
|---|---|---|---|---|
| F-01 | Functional | S04, S07, S08, S13 | Build target saved in setup never becomes the run's target; Preflight says `Missing`, cockpit and skills planner say no target is set. | No |
| F-02 | Functional | S04 | Re-saving the target for the missing Purpose deleted both previously saved skill priorities. | No |
| F-03 | Functional | S07 | `1 step needs attention` did not block `Start career`; the run was created with the step marked missing. | No |
| F-04 | Functional | S07, S08, S16 | Run id 26 first appeared in the URL; no screen displayed it before or after. | No |
| F-05 | Functional | S08, S10, S12 | A Unity Cup career lists `URA Finals Qualifier`, `URA Finals Semifinal` and `URA Finals Final (Aoharu)` as mandatory races. | No |
| F-06 | Functional | S08, S11 | At turn 67 all eight trainee goals read Upcoming/Due, the earliest (`Junior Make Debut`, turn 12) stays `Due`, and cleared goals cannot be recorded. | No |
| F-07 | Functional | S09 | `Outcome *` has no pending/unresolved state and there is no year/month input; representing the snapshot required a fabricated `Success`. | Yes |
| F-08 | Functional | S12 | Race header reads `Senior Year · Late October · Turn 20` while the cockpit reads `Senior Year · Early October · Turn 67`. | No |
| F-09 | Functional | S16 | Careers row reads `Turn ~11 / 24` for the run whose cockpit reads Turn 67 of 72. | No |
| F-10 | Functional | S03 | Selecting a trainee cleared the search and filter and reloaded all 67 rows. | No |
| F-11 | Functional | S14, S15 | Career nav `Legacy` pointed to `/legacy` from the run deck, losing run context; the cockpit's link pointed to `/legacy/26`. | No |
| E-01 | Experiential | S03 | No forward control at the bottom of the trainee picker; advancement is via the wizard rail only. | — |
| E-02 | Experiential | S04 | `Save target` gives no confirmation; the breadcrumb still reads `Target: not chosen` after a save. | — |
| E-03 | Experiential | S05 | Spark entry costs one expander plus four fields per spark, with a second button inside grandparents; a full tree is impractical. | — |
| E-04 | Experiential | S06 | The card picker's `Filter` navigates and closes the dialog; every search needs the slot reopened, and the full printed epithet matches nothing. | — |
| E-05 | Experiential | S06 | Ownership defaults to `N/A` and is a separate decision from card choice; six slots can be saved unclassified. | — |
| E-06 | Experiential | S07 | Heading says `The five entered steps` while the wizard has six steps. | — |
| E-07 | Experiential | S08 | The first cockpit is a wall of N/A with the only forward CTA at the bottom of the advisor. | — |
| E-08 | Experiential | S09 | Training cards cannot show expected gains until facility levels are recorded, but those inputs live inside the turn form below them. | — |
| E-09 | Experiential | S12 | A run deciding a race is offered only `Enter Race`; no scheduled / not-yet-run state. | — |
| E-10 | Experiential | S13 | Skill costs are printed in `CC` while the run tracks Skill Points, with no explanation. | — |
| E-11 | Experiential | S01, S16 | Two identical-looking Rice Shower / Unity Cup / Active rows with no run id. | — |
| E-12 | Experiential | S04 | Help text names a fifth purpose, `Competitive Build`, that the select does not offer. | — |
| E-13 | Experiential | S12 | Every race detail is a ten-row block of N/A values repeated across seven races. | — |
| U-01 | Unrepresented data | S08, S11 | Potential Lvl 2 has no field; identity records rarity only. | — |
| U-02 | Unrepresented data | S08, S11 | Team stat grades A/A/A/S/A have no input; the panel says so. | — |
| U-03 | Unrepresented data | S08, S11 | Team Rank S has no input; only league placement is stored. | — |
| U-04 | Unrepresented data | S08, S11 | Current Spirit gauge has no input. | — |
| U-05 | Unrepresented data | S12 | The snapshot's race day (Kyoto Daishoten, Mile Championship Nambu Hai, Soft going, Recommended flag) has no representation. | — |
| U-06 | Unrepresented data | S13 | Skill levels (unique Lvl 3, hint levels) have no field. | — |
| U-07 | Unrepresented data | S13 | Hint discounts are explicitly not modelled. | — |
| U-08 | Unrepresented data | S06 | The borrowed card's owner account is not representable beyond `Rented`. | — |
## 7.7 Definition-of-done checklist

- [x] Every screen reached has its own section, in visit order (S01 to S16).
- [x] Every entered value has a source trace or is marked "not present in source" and listed in the Improvisation log.
- [x] Every source datum without a UI home is named, on the screen where it became relevant.
- [x] The run's actual lifecycle status is preserved throughout: Active, mid-career, five turns left.
- [x] The run is not classified as Veteran or Legacy; it appears only in the active Careers list.
- [x] Functional and experiential findings are separate.
- [x] The findings register matches the findings sections (11 F, 13 E, 8 U).
- [x] The walk's stopping point and reason are stated.

Evidence caveat: the walk was performed with an automation-driven browser, not a human hand. Controls were clicked, text was typed, and selects were chosen through the page; `browser_evaluate` was used read-only except for the single off-screen nav link click recorded in S15. No stability probe pass (Appendix B) was run.

## 8. Closing assessment

### 8.1 What worked

The flow successfully represented most of the source run once a turn was logged:

- Scenario and trainee selection carried forward without asking again; the costume form recorded `[Rosy Dreams]`.
- The run's own caps were enterable as ceilings (1325 / 1322 / 1368 / 1308) and replaced the published 1300 labels on the form.
- The six-card deck persisted into the run, including `Slot 6 · Friends` for Super Creek with `Rented`, and Haru Urara alone rendered `Scenario Link`.
- The six-node Legacy accepted both parents and all four grandparents, the `Rented for the run` checkbox rendered as `Rented from a friend`, and two blue sparks persisted.
- Team state (Blue Bloom, Dreaming Big, 8th, 4 preseason wins, 55 trainings, 6 + 5 bursts) persisted; the cockpit later showed `Spirit Bursts 11 normal plus Extreme`, the expected sum.
- After the turn record, the cockpit derived the correct moment from turn 67: `Senior Year` / `Early October`, and displayed Energy `Low band`, Mood `GREAT`, Fans `209,245 · Star · 30,755 to next`, Skill Points `173`, and stats 607 / 577 / 549 / 736 / 397. The fans gap and the stat line match the source exactly.
- Skills distinguish `Suggested` / `Acquired` / `Skipped`, and `Blue Rose Closer` persisted as Learned.
- The run stayed `Active` throughout; it was never labelled Veteran or Legacy.

### 8.2 Functional findings

**F-01. Build target never attaches to the run.** Screens S04, S07, S08, S13. S04 saved ceilings, two skill priorities and later a Purpose, and showed a Summary; the rail marked the step `done`. S07 nevertheless listed `Your target · Not entered` and `Missing`; S08 read `No build target was set for this run.`; S13 repeated that and refused to rank skills. Expected: a saved target becomes the run's target. Next: proceeded through Preflight and Start career; the loss persisted on every later screen.

**F-02. Target re-save deletes saved skill priorities.** S04. The first save's Summary read `skills in order: Pace Chaser Straightaways ◎, Kyoto Racecourse ○.` After returning from Preflight and setting only `Purpose`, the Summary read `skills in order: none listed yet.` Expected: unrelated field edits preserve the list. Next: continued without re-adding them; S13 confirmed no priorities exist.

**F-03. A declared missing step does not block the start.** S07. The page showed `1 step needs attention` and `✗ Missing Your target · Not entered`, yet `Start career` was enabled and created run 26. Expected: either a blocking state or no warning. Next: used the warning as the reason to edit the target, then started the run after it remained missing.

**F-04. Run id never surfaces.** S07 to S08 to S16. `/training-runs/26/cockpit` introduced `26` only in the URL. The Careers row for the run shows no id. Expected: the created run is named somewhere a user can see. Next: used the URL to reference the run.

**F-05. Unity Cup career shows URA Finals obligations.** S08, S10, S12. `Mandatory races` listed `URA Finals Qualifier`, `URA Finals Semifinal` and `URA Finals Final (Aoharu)`; the scenario panel simultaneously says `Finale: Unity Cup. Not yet reached.` Expected: Unity Cup obligations (team races, Unity Cup finals). Next: recorded nothing against them.

**F-06. Goal state cannot be brought up to the snapshot.** S08, S11. At turn 67, all eight goals read Upcoming or Due; `Junior Make Debut` at Junior turn 12 was still `Due`, and `Goals Achieved 0 of 8` was shown, although the source records three cleared goals. No control offers a past goal's cleared state. Expected: the three cleared goals (Nikkei Sho, Tenno Sho Spring, Takarazuka Kinen) and the active Arima Kinen goal are representable at turn 67. Next: left the goal list as rendered.

**F-07. Required outcome with no pending state; no date input.** S09. `Outcome *` offers `Choose`, `Success`, `Failure`; recording the snapshot required selecting `Success` for a turn the source leaves at a race decision. The form has no year or month; the moment is only reachable by choosing a turn number. Expected: a way to record a read state or an unresolved turn. Next: selected `Success` (Improvisation log #9) and proceeded. Blocking: yes, it forced a non-source value.

**F-08. Race screen contradicts the cockpit's turn and date.** S12. `The turn being decided` read `Senior Year · Late October · Turn 20`; the cockpit reads `Senior Year · Early October · Turn 67`. Expected: the next turn after 67, i.e. Late October turn 68, or no turn. Next: used the cockpit as the authority and did not enter a race.

**F-09. Careers list turn contradicts the cockpit.** S16. The row read `Turn ~11 / 24` for run 26 while the cockpit read Turn 67 (72-turn career). Expected: the same position as the cockpit, or a labelled different measure. Next: none; the row was read-only.

**F-10. Selecting a trainee discards the filter.** S03. After `Select`, the list showed `67 trainees match.` and the URL's `search`, `sortBy` and `direction` were gone. Expected: selection leaves the filter state available, or a clear reset. Next: advanced via the rail; no re-search was needed for this walk.

**F-11. Career nav `Legacy` loses run context.** S14, S15. On the cockpit the link was `/legacy/26`; on the run deck it was `/legacy`, which opens the Veteran library (7 of 7 Veterans) with no row for run 26. Expected: the run's own Legacy record. Next: followed it, then left via `Careers`.

### 8.3 Experiential findings

**E-01. No forward button on the trainee picker.** S03. The page ends at pagination; advancement is the rail's `Your target not started`. A user who expects a primary action at the end of the form has to discover the rail.

**E-02. Silent save on the target screen.** S04. No toast, banner or state change accompanies `Save target`; the breadcrumb continues to read `Target: not chosen`, which reads as "not saved" and sends the user back to a form they already completed.

**E-03. Spark entry is per-spark form work.** S05. Each spark needs `Kind`, `Applies to`, `Target`, `Stars`, and a second `Add Spark` click for the next row; grandparents hide the entry behind a two-step expander. Six ancestors with the source's full inventory is not a practical form.

**E-04. Deck picker filtering reloads the page.** S06. `Filter` navigates, closes the dialog and strands the user on the slot list; the slot must be reopened. The full printed row name (`Tokai Teio [Dream Big!]`) matches nothing while the short name matches.

**E-05. Ownership is easy to skip.** S06. Every slot begins `Ownership N/A` and is classified by a separate `Owned`/`Rented` pair; the page's own note warns that an unchosen flag stores as no flag.

**E-06. Mismatched count in the Preflight heading.** S07. `The five entered steps` while Step 6 is the screen itself.

**E-07. First cockpit is an N/A wall.** S08. Twenty-plus `N/A` cells and a declining advisor precede the one action that resolves them, which sits at the bottom of a long page.

**E-08. Training preview is circular on the first turn.** S09. Expected gains read `N/A` on all five cards because no facility level exists, and the level inputs are inside the turn form below the cards.

**E-09. No scheduled-race state.** S12. For a run that is deciding a race, every card offers only `Enter Race`; there is no way to say "this is the race being considered" without committing it.

**E-10. Cost units disagree.** S13. The skill list prints `CC` prices (e.g. `1,500,000 CC`) while the run tracks Skill Points, and the screen's own coverage line says it cannot compare against anything.

**E-11. Two indistinguishable careers.** S01, S16. The pre-existing run 25 and the new run 26 both read `Rice Shower / Unity Cup / Active`, with no id or start-time distinction beyond a date.

**E-12. A purpose named but not offered.** S04. The help text lists `Competitive Build`; the select does not.

**E-13. Repeated N/A detail panels.** S12. Each of seven race cards opens a ten-row detail block that is mostly `N/A` with long tooltips; scanning the race day is slow.

*Timing and keyboard observations:* no timing anomaly was observed; no keyboard-only pass was performed. The automation clicked controls directly, so focus order is unassessed. One nav link (`Legacy`) was reported "not visible" by the automation layer and had to be clicked through the page's own DOM anchor; this is recorded as a tooling note, not a UA finding.

### 8.4 Unrepresented source data

| Finding | Source datum | Where it became relevant |
|---|---|---|
| U-01 | Potential Lvl 2 (§1.1 line 37) | S08 identity editor offers rarity only; header reads N/A |
| U-02 | Team stat grades Speed A, Stamina A, Power A, Guts S, Wit A (§1.8 line 263) | S08 `Edit team state` and the Scenario panel; the panel states no column stores them |
| U-03 | Team Rank S (§1.8 lines 262-263) | S08/S11 `Team Rank N/A`; no rank input |
| U-04 | Current Spirit gauge (§1.8; panel) | S08 scenario panel states no column stores it |
| U-05 | The turn's race day: Kyoto Daishoten, Mile Championship Nambu Hai, Soft going, `Recommended`, fan payouts (§1.6 lines 210-232) | S12 lists seven unrelated races; the Daishoten is absent |
| U-06 | Skill levels: Blue Rose Closer Lvl 3 and the hint levels (§1.3 lines 92-96, §2.1) | S13 acquisition state only |
| U-07 | Hint discounts (§2.1) | S13 writes `Hint discounts are not modelled` |
| U-08 | The friend card's owner account (§1.4 lines 114-118) | S06/S14 `Rented` flag only |

Additionally representable but under-used in this walk, and therefore not findings: card levels, ancestor pink/green/white sparks, and the remaining owned skills all have UI homes; they were omitted for walk budget. The green factors are the exception: the spark form has no field pairing a unique-skill name with a star grade, so the six green factors in §8.5 cannot be stored as the source reads them.

### 8.5 Walk completion

- **End of flow:** not reached in the sense of a completed career. The career has five turns left, so there is no natural end. The walk stopped at S16 (`/training-runs`), after the run was created, populated, and reviewed through the cockpit, training, races, skills and deck screens.
- **Why it stopped:** no further forward action could be taken without inventing a race result or a training outcome. `Enter Race` on S12 would commit a race the source has not run; the Training form had already forced the one available `Success`. The remaining run sections (`Timeline`, `Result`, `Events`) were not required to answer the checklist and the run is unfinished, so `Result` is gated.
- **Data intentionally omitted for lack of representation:** none at entry time except the green factors (no name+star field). Data omitted for budget: most spark rows, 10 of 11 owned skills.
- **Persisted values not sourced from the source document:** turn `67` (derived from 72 − 5), `Outcome Success` (fabricated to satisfy a required field), `Purpose Story Clear` (improvised), `Energy band Low` (the source has a qualitative band, not a value; the band is a legitimate encoding, but the choice of the band is the walk's), facility levels / failure rate / preview gains (sourced from §1.7 but describing a training turn rather than the race turn), and the two catalog-instance choices (`· 14`, `· 9`). Everything else entered traces to a source line quoted in §7.3.

## Appendix A — Derived probe list and answers

Derived before the first click from the source document's own anomalies.

| # | Source anomaly | Question for the walk | Answer |
|---|---|---|---|
| 1 | Mid-career snapshot with 5 turns left (§1.1) | Does setup offer a snapshot/partial state, or assume a new career? | It assumes a new career. A run is created at Turn 0 and only reaches the snapshot by logging turn 67 (S07, S08, S09). |
| 2 | No run number in the source | Does the flow require one? | No. It asks for none, and never displays the id it assigns (S01-S08, F-04). |
| 3 | Caps (1325/1322/1368/1308) exceed the published 1300 (§1.2) | Does the UI ask for caps and can it hold the run's? | Yes, ceilings are enterable for four stats; Wit has no input but the source's 1800 equals the published figure (S04). |
| 4 | Six blue sparks but cap arithmetic does not reconcile (§1.2 lines 56-64) | Does the UI reconcile caps against sparks? | No. Caps are stored as entered; sparks are a separate record; no warning (S04, S05). |
| 5 | Friend-slot Super Creek, 50/50, borrowed (§1.4) | Is the friend/borrowed slot representable? | Yes: `Slot 6 · Friends` plus `Rented` on the card. The owner account is not (S06, U-08). |
| 6 | 6 ancestors with per-instance spark sets (§1.5) | Is a fixed six-node tree supported? | Yes, two parents and four grandparents. Sparks are per node. |
| 7 | Green factors carry unique-skill names (§8.5) | Can a green factor be recorded? | Partly: `Kind = Green` exists but `Target` offers stats only and no star-graded unique-skill name (S05, §8.4). |
| 8 | 11 owned skills, unique at Lvl 3 (§1.3) | Are individual skills and levels representable? | Skills are individual with `Acquired` state; levels are absent (S13, U-06). |
| 9 | Hint discounts 10-40% (§2.1) | Are hints/discounts representable? | No; the screen states they are not modelled (U-07). |
| 10 | Race day scheduled but not run, Soft going, `Recommended` (§1.6) | Can a pending race day be recorded? | No. The race screen shows unrelated races and only `Enter Race`; the source race is absent (S12, U-05). |
| 11 | Mood GREAT and Energy low band, no Energy number (§1.7) | Are mood and energy current values? | Mood is a five-value select; Energy supports an exact number, a band, or not recorded. `Low` is available (S09). |
| 12 | 173 unspent SP (§1.2) | Is unspent SP a field? | Yes, `Skill Points` on the turn form; read back on the cockpit (S09, S11). |
| 13 | 72-turn career, 5 turns left | Can "turns remaining" be represented? | Only implicitly through the turn number; no turns-remaining field (S09). |
| 14 | Three goals cleared, one active (§1.6) | Can cleared goals be marked? | No; all goals read Upcoming/Due at turn 67 (F-06). |
| 15 | Run is Active, not completed (§1.1, §1.6) | Does the UI force a category? | No. Status defaults to `Active` and `Completed`/`Retired` are optional. |

## Appendix C — Remediation ideas (non-binding, not implemented)

Outside the findings. Nothing here was implemented.

1. Attach the saved setup target to the created run, and make Preflight's `Missing` state block `Start career` or be removed.
2. Treat the target as a patch, so saving one field does not drop the others (skill priorities).
3. Add an explicit "resume from current state" path, or a pending outcome state on the turn form, so a mid-career snapshot does not require a fabricated turn.
4. Fix the race screen's turn/date derivation, and either scope mandatory races to the scenario or explain why URA obligations apply.
5. Let the deck picker filter in place (no navigation/close), and match the printed `Name [Epithet]`.
6. Make run identity visible on the Careers rows and the cockpit header.
7. Show ownership on the run deck's read-only view.
8. Give the Career nav's `Legacy` link a run-scoped target from every career section.

Evidence caveat: the walk was performed with an automation-driven browser, not a human hand. Controls were clicked, text was typed, and selects were chosen through the page; `browser_evaluate` was used read-only except for the single off-screen nav link click recorded in S15. No stability probe pass (Appendix B) was run.
