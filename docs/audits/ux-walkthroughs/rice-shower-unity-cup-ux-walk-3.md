# UX walk audit (3rd pass): [Rosy Dreams] Rice Shower, Unity Cup

**Audit ID:** `rice-shower-unity-cup-ux-walk-3`

## 7.1 Header

| Field | Value |
|---|---|
| Audit name | Rice Shower Unity Cup mid-career UX walk — third pass, templated procedure |
| Source document | `docs/[Rosy_Dreams]Rice_Shower_Unity-Cup.md` (776 lines; read end to end before the walk) |
| Starting URL | `http://127.0.0.1:8000/` |
| Walk started | 2026-10-11 23:59 +0800 (Asia/Kuala_Lumpur) |
| Walk ended | 2026-10-12 (early hours, same session, after S18) |
| Browser | Chromium 150.0.7871.114 via the Qoder 0.4.4 Electron automation surface; driver: **Playwright MCP** (see Driver note) |
| Viewport / OS | Automation surface reported `0×0` CSS pixels, DPR 1; Windows 10 (10.0.26200), x64 |
| Devtools | Not opened. The only page inspection was read-only DOM/`innerText` reads through the driver's evaluate; nothing was written to the DOM by the auditor |
| Application | Laravel 13.32.0 / PHP 8.5.8 / SQLite; `php artisan serve` on `127.0.0.1:8000`, already running (not started by this walk) |
| Git commit | `2c2fd0e7fd403cf83b4cecd2a7c956fb7d44d92b` (2026-10-11T23:30:34+0800). Working tree dirty with 19 unrelated files from concurrent work, none touched here |
| Run created | `training_runs` id **11** (`/training-runs/11`), status Active, scenario Unity Cup |
| Screens | 18, IDs `S01`–`S18` (§7.2) |
| Artefacts | `docs/audits/rice-shower-unity-cup-ux-walk-3/screens/` was created but is **empty**: the driver saves its a11y snapshots and console logs to its own `.playwright-mcp/` working directory, not to an auditor-chosen path, and capturing 18 screenshots into the requested layout was not cheap. The written record below is the audit. |

**Driver note (environment event, §4.8).** The walk began on the browser-use automation surface. At S01,
before any value was entered, every pointer action on that surface failed with
`NATIVE_BROWSER_VIEWPORT_UNAVAILABLE … viewport=0x0, visible=false, attached=false` (one recorded retry,
same result; `take_snapshot` kept working). The walk was therefore restarted on the Playwright MCP driver
— a separate Chromium with a fresh profile and **no** cookies from the earlier passes — from S01. Nothing
had been entered at the switch, so the one-pass rule is intact. The dashboard had already been read on the
first driver; its content was re-derived on the second.

**Pre-existing state.** The server-side database already contained runs 1–10, including an Active career
(Rice Shower, Unity Cup, run 10, `Junior Year · Early January`, Turn 1) that the Dashboard showed as
ACTIVE CAREER before this walk began, and 7 Veteran rows from earlier work. None of it was read, written,
or cleaned up by this walk.

**Method statement.** Every screen below was reached in the browser and read at the time of the visit;
every value was entered through the app's own controls (search box, native selects, number inputs, radio
group, checkboxes, disclosure summaries, and the app's buttons). No seeder, factory, SQL, tinker, artisan
write, API call, or devtools state manipulation was used. Inter-screen hops used the app's own links and
buttons. `evaluate` was used only to read rendered text and to compute CSS selectors for the next pointer
action — it never set a value, clicked, or replayed a request.

**Scope and stopping point.** The primary pass ran S01→S18 and ended at the run deck screen (S18), the
last surface the run exposes; the Result screen (S16) is the designed dead end for an Active run. Nothing
forced an abandonment.

## Run identity block (§2)

| Field | Value |
|---|---|
| Trainee | Rice Shower |
| Rarity | ★3 |
| Potential / awakening level | Level 2 |
| Scenario | Unity Cup |
| Snapshot point | Senior Year, Early October |
| Turns remaining | 5 (and "5 turns until the Unity Cup", §1.1 line 21) |
| Lifecycle status | **Mid-career snapshot, in progress — not completed** |
| Run number | None in source |
| Stats | Speed 607 / Stamina 577 / Power 549 / Guts 736 / Wit 397 (§1.2 lines 28-32) |
| Unspent SP | 173 (§1.2 line 34) |
| Mood | GREAT (§1.7 line 219) |
| Energy | Low — bar "near a third", warning text printed, **no number exists** (§1.7 lines 224-230) |
| Support deck | Six cards, all SSR: Tokai Teio [Dream Big!], Nishino Flower [Even the Littlest Bud], Biko Pegasus [Double Carrot Punch!], Super Creek [Piece of Mind], Matikanetannhauser [Just Keep Going], Haru Urara [Urara's Day Off!] (§1.4 lines 89-96) |
| Borrowed / friend-slot card | **Super Creek [Piece of Mind]** — the "Friends" pill is on her entry, and the deck's only 50/50 card is the borrowed one (§1.4 lines 98-101; §7 item 8, lines 634-637) |
| Skill count | Ten visible chips plus Corner Recovery ○ on the Learn screen (§1.3 lines 75-79) |
| Ancestor count and sparks | Six ancestors, full Blue/Pink/Green/White table at §1.5 lines 158-165; **no Wit spark anywhere in the tree** (lines 170-171); six blue sparks: Stamina ★★★, Power ★★★, Speed ★★☆ ×2, Guts ★☆☆, Power ★☆☆ (lines 43-44) |
| Race days already won | Four preseason team rounds won; finals pending (§1.6 lines 181-184) |
| Outstanding objectives | Place 1st in Arima Kinen (Senior Late Dec), the Unity Cup finals, 173 SP of §2.2 purchases, two more bursts before the November payout (§1.6 lines 186-188; §5) |

**Source inconsistencies preserved (§2.2), not resolved by this walk:**

- §1.2 (lines 39-56): the four cap extras do not reconcile from the six blue sparks; only Guts +8
  survives. The document says so itself.
- §1.2 line 58 vs the **dated erratum at lines 61-71**: the document's own aptitude letters (Mile B,
  Front A, Late B) disagree with the GameTora export the tool seeds from (Mile C, Front B, Late C), and
  the erratum rules that "the two readings disagree about the client" and that the difference is open —
  explicitly *not* a tool bug.
- §8.2 (lines 671-687): whether Extreme Bursts count toward the gold band is a live publisher
  disagreement; 11 combined is gold only under one reading.
- §8.7 (lines 763-769): the client's "5 turn(s) until the Unity Cup" does not reconcile with seven
  half-month turns to Late December.
- §6 item 7 (lines 577-580): 18 named roster members against a 20/20 counter.

## 7.2 Screen ledger

| Screen ID | Route | Arrived from (screen / control) | Exited via (control) |
|---|---|---|---|
| S01 | `/` | — (entry point) | Quick actions `New Career` |
| S02 | `/career/setup/scenario` | S01 `New Career` | Step nav `Trainee` |
| S03 | `/career/setup/trainee` (filter state: `?direction=asc&search=Rice%20Shower&sortBy=name`) | S02 `Trainee` | Step nav `Your target` |
| S04 | `/career/setup/target` | S03 `Your target` | Step nav `Legacy` |
| S05 | `/career/setup/legacy` | S04 `Legacy` | `Next: Support deck` |
| S06 | `/career/setup/deck` (`?deck_slot=N` lagging, see F-03) | S05 `Next: Support deck` | `Next: Preflight` |
| S07 | `/career/setup/preflight` | S06 `Next: Preflight` | `Start Career` |
| S08 | `/training-runs/11/cockpit` | S07 `Start Career` | CTA `Record the first turn through Training` |
| S09 | `/training-runs/11/training` | S08 CTA | `Record this training` |
| S10 | `/training-runs/11/cockpit` | S09 write (its own stated landing) | — (also used the correction and identity forms) |
| S11 | `/training-runs/11/inheritance` | S10 (address bar, run's own route) | — |
| S12 | `/training-runs/11/races` | S11 | — |
| S13 | `/training-runs/11/races/planner` | S12 `Plan the whole calendar` (route) | — |
| S14 | `/training-runs/11/events` | S13 | — |
| S15 | `/training-runs/11/timeline` | S14 | — |
| S16 | `/training-runs/11/result` | S15 | — (designed dead end) |
| S17 | `/training-runs/11/skills` | S16 | — |
| S18 | `/training-runs/11/deck` | S17 | — (end of flow) |

## 7.3 Screen-by-screen walk

### S01 — Dashboard

#### Route and trigger
Route `/` (`home`), title `Dashboard`. Trigger: initial navigation on the Playwright driver (re-derived
after the driver switch; the same page had been read once on the browser-use driver).

#### What the screen asks for
Read-only. Regions in order: ACTIVE CAREER (trainee, `Unity Cup · Junior Year · Early January`, `Turn 1`,
`Energy N/A`, `Team Rank N/A`, `Resume Career`) plus the line "Already partway through a career?
`Import an existing career` instead, and this tool starts from where your client says it stands."
linking `/career/snapshot`; Quick actions `New Career` / `Legacy Lab` / `Support Cards`; RECENT VETERANS
(3 of 7); RECENT BUILDS ("No builds recorded yet."); DATA STATUS (`Verified 2026-09-27 · GameTora
scenarios.json`, `Ruleset version: N/A`, Trainees 67, Skills 1,910, Support cards 559). A bottom
navigation bar (`Home`, `Career`, `Legacy`, `Deck`, `More`) is new this pass.

#### Values entered + source trace
None entered. (No source datum is requested on this screen.)

#### Source data with no UI home
None relevant.

#### Friction / errors / dead ends
- The ACTIVE CAREER block showed run 10 — a career created by other work, not by this walk — so the
  landing state was not a clean first-run state (logged as pre-existing context).
- `New Career` was still offered beside an Active career, and it began a clean draft (`Stored choice:
  N/A` on S02) with no prompt about the career already in progress.
- The Dashboard's own copy for the active career derived `Junior Year · Early January · Turn 1` for what
  the source records as Senior Early October — the same derivation finding recorded at S09/S15.

#### Outcome / next screen
Clicked Quick actions `New Career` → S02.

---

### S02 — Scenario setup (step 1 of 6)

#### Route and trigger
Route `/career/setup/scenario`, title `Choose a scenario`. Trigger: S01 `New Career`.

#### What the screen asks for
Step nav (`Leave setup`; Scenario, Trainee, Your target, Legacy, Support deck, Preflight), a `Stored
choice: N/A` readout, and four scenario cards — URA Finale, Unity Cup, Trackblazer, Our Grand Concert —
each printing Optimizes / Systems / Tracked resources / Turn loop / Live on Global / `Ruleset version` /
`Description` / `Recommended use` and a `Select Scenario` button. The Unity Cup card additionally carries
a description paragraph, `Rule family: Team`, a `Planning focus` line and three `KEY MECHANICS` bullets.
No Continue control; forward movement is the step nav.

#### Values entered + source trace
- `Unity Cup` → the Unity Cup card's `Select Scenario` button → source §1.1 line 19.

#### Source data with no UI home
None on this screen.

#### Friction / errors / dead ends
- The Unity Cup card prints `Ruleset version · N/A` in its fact block and `Rule family · Team` in the
  description block — the same value surface twice, once as N/A and once filled. (Pass 2 recorded two
  contradictory `Ruleset` labels; the labels were renamed between passes, so the outright contradiction is
  gone but the duplication and the N/A remain.)
- URA Finale, Trackblazer and Our Grand Concert still print `Ruleset version`/`Description`/
  `Recommended use` as `N/A` plus a `Not recorded` line, so the four-way comparison is one rich card
  against three mostly blank ones.
- `Select Scenario` flashed `Scenario set.` and moved `Stored choice:` from `N/A` to `Unity Cup`.

#### Outcome / next screen
Step nav `Trainee` → S03.

---

### S03 — Trainee (step 2 of 6)

#### Route and trigger
Route `/career/setup/trainee`, title `Choose a trainee`; after filtering,
`?direction=asc&search=Rice%20Shower&sortBy=name`. Trigger: S02 step nav `Trainee`.

#### What the screen asks for
`Search` (placeholder `Trainee name`), `Surface` / `Distance` / `Running style` selects (Any,S…G),
`Unique skill` select (≈130 options), `Sort by` (Name, Turf, Dirt, Sprint, Mile, Medium, Long, Front
runner, Pace chaser, Late surger, End closer), `Order` (Ascending/Descending), `Filter`, `Clear filters`.
A 67-trainee paginated roster; each card carries portrait, English and Japanese name, `Released (Global)`,
ten aptitude chips (letter + word), a costume-form summary, `Select Trainee`, `View Profile`.

#### Values entered + source trace
- `Rice Shower` → `Search` → source §1.1 line 20.
- `Filter` click (no value).
- `Select Trainee` on the Rice Shower card (`id="trainee-30"`) → source §1.1 line 20.

#### Source data with no UI home
- **Rarity ★3 and Potential Lvl 2** (§1.1 line 20): no star or potential field on the card or anywhere
  downstream that this walk could reach (see S08).
- **[Rosy Dreams]** (§1.1 line 20): the card reads `2 costume forms ★★★SSRdebut form★★★SSR` — the run's
  own costume is the one with no name, and the portrait served is `artwork/card_portrait/103001`, the
  debut form.

#### Friction / errors / dead ends
- The picker's ten aptitude letters are Turf A, Dirt G, Sprint E, **Mile C**, Medium A, Long A, **Front
  runner B**, Pace chaser A, **Late surger C**, End closer G. The source's line 58 reads Mile B, Front A,
  Late B. This is exactly the disagreement the source's own dated erratum (lines 61-71) records — "the
  seeded column and the trainee picker both reproduce the export letter for letter … the two readings
  disagree about the client … should [be treated] as open, not as a bug in the tool." Recorded as open,
  per the erratum; it reproduced letter-for-letter across all three passes.
- `Select Trainee` flashed `Trainee set: Rice Shower.` and `Stored choice:` updated to `Rice Shower`
  (pass 1 had seen it stay `N/A`; passes 2 and 3 both saw it update).
- The search filter cleared after the write; the list returned to 67.

#### Outcome / next screen
Step nav `Your target` → S04.

---

### S04 — Your target (step 3 of 6)

#### Route and trigger
Route `/career/setup/target`, title `Your target`. Trigger: S03 step nav `Your target`.

#### What the screen asks for
`Purpose` (Not chosen yet, Story Clear, Champions Meeting, Parent Farming, Skill Farming; note that a
fifth purpose, Competitive Build, is not recorded), `Distance` (Sprint/Mile/Medium/Long), `Surface`
(Turf/Dirt), `Style` (Front Runner/Pace Chaser/Late Surger/End Closer), five stat targets (Speed, Stamina,
Power, Guts `max=1300`; Wit `max=1800`) each with a `Cap` label and ratio readout, a `Skill name` + `Add`
priority list, a live `Summary`, and `Save target`.

#### Values entered + source trace
**None — the §4.7 bounded probe (submit with every field at its empty state) is also the faithful entry**,
because the source records no build target. Observed: `Build target saved.`, zero `[role=alert]`, and the
line `No target is recorded yet. The fields below start empty rather than at zero.` still true. The same
probe in pass 2 produced the same result; in pass 1 it produced nine validation errors. Recorded per pass.

#### Source data with no UI home
- **The run's own caps 1325 / 1322 / 1368 / 1308** (§1.2 lines 28-31) exceed the inputs' `max=1300`, and
  the screen asks for a *target*, not a cap. §1.2's cap composition (lines 39-56) — sparks, support-card
  Max-Stat values — is asked for nowhere on this screen (U-01).
- **A single "distance the career is for"**: the source says Medium and Long are both A and calls it
  "A Medium/Long build" (§1.2 line 59) without choosing.
- **Purpose**: not stated anywhere in the source.

#### Friction / errors / dead ends
- Nothing is required, so a mid-career snapshot with no plan can be carried forward without invention —
  the direct opposite of pass 1's forced fabrication.
- The success flash next to `No target is recorded yet` reads as contradictory at a glance, though the
  second line is accurate.

#### Outcome / next screen
Step nav `Legacy` → S05.

---

### S05 — Legacy (step 4 of 6)

#### Route and trigger
Route `/career/setup/legacy`, title `Legacy`. Trigger: S04 step nav `Legacy`.

#### What the screen asks for
Two parent panels. Each: rank and spark-chance readouts; `Assign to Parent N` (select over the Veteran
library, options labelled `Vodka [Wild Top Gear] · 7`, `Maruzensky [Formula R] · 2`, …); `Grandparent
N1`/`N2` type-ahead fields plus a per-grandparent `Add spark to grandparent …` disclosure; an "AS YOU READ
HER" block with `Own rank (letters)` (editable combo, suggestion set G, F, E, D, C, B, A, S, B+, A+, S+),
`Own rank (stars)` (number), `Rented for the run` (checkbox), and a `SPARKS` list where each row is `Kind`
(Blue, Pink, Green, White, Scenario), `Applies to` (Stat, Aptitude, Skill) and a dependent `Target` select
(Not recorded, Speed, Stamina, Power, Guts, Wit) plus `Stars` (1–3). Then `Affinity for the pair` (Not
recorded, △, ○, ◎), `Save Legacy` (`id=legacy-save`), `Next: Support deck`.

#### Values entered + source trace
| Value | Field | Source |
|---|---|---|
| `Vodka [Wild Top Gear] · 7` | Assign to Parent A | §1.5 lines 148-156 (tree) |
| `Daiwa Scarlet` / `Seiun Sky` | Grandparent A1 / A2 | §1.5 lines 151-152 |
| `B+` | Parent A `Own rank (letters)` | §1.5 line 150 (`rank B+`) |
| Blue / Stat / `Power` / ★1 | Parent A spark | §1.5 line 160 |
| `Maruzensky [Formula R] · 2` | Assign to Parent B | §1.5 line 153 |
| `Daiwa Scarlet` / `Taiki Shuttle` | Grandparent B1 / B2 | §1.5 lines 154-155 |
| `UG` | Parent B `Own rank (letters)` | §1.5 line 153 (`rank UG`) |
| checked | Parent B `Rented for the run` | §1.5 line 153 (`flagged Guest`) |
| Blue / Stat / `Speed` / ★2 | Parent B spark | §1.5 line 163 |
| Blue / Stat / `Stamina` / ★3 | Grandparent A2 (Seiun Sky) spark | §1.5 line 162 |
| (left `Not recorded`) | `Affinity for the pair` | **Not present in source** — §1.5 states no pair grade |

The remaining three blue sparks (§1.5 lines 161, 164, 165) and every pink, green and white spark were
**not entered** — the fields exist and accepted the three above, including one on a grandparent; the rest
is volume (see Walk completion). This omission is documented, not routed around.

#### Source data with no UI home
- **The four grandparents' ranks** — B+ (line 151), A (line 152), UG (lines 154-155) — have no field:
  `Own rank (letters)`/`(stars)` exist only for the two parents (E-05, U-05).
- **Spark chance** is `N/A` by the app's own statement ("This tool holds no sourced star-roll table"), so
  §1.2's per-activation arithmetic has nothing to read.

#### Friction / errors / dead ends
- **`Save Legacy` produced `Legacy recorded.` with no validation errors, and everything entered
  survived** — Parent A `Rank B+` with its spark, Parent B `Rank UG` / `Rented from a friend` with its
  spark, all four grandparent names, and the grandparent spark (confirmed on the S07 contract). **Pass 2's
  silent total discard did not reproduce.**
- `UG` is not in the `Own rank (letters)` suggestion set. Typing it was accepted without warning and it
  renders on the parent readout and on the S07 contract (E-04).
- The grandparent spark editor sits behind a disclosure and, once open, renders its `Target` select with
  no accessible name (the parent-row selects are likewise unnamed) — locating them required DOM reads
  (E-12).
- Filling a spark row commits it live; the parent readout updated as soon as `Target` and `Stars` were set.

#### Outcome / next screen
`Next: Support deck` → S06.

---

### S06 — Support deck (step 5 of 6)

#### Route and trigger
Route `/career/setup/deck`, title `Support deck` (the window title rendered as the generic `Trainer Desk`
on arrival, settling to `Support deck` after the first re-render). Trigger: S05 `Next: Support deck`.

#### What the screen asks for
Six slots (1–5 and `Slot 6 · Friends`, tagged `Friend slot`), each with the equipped card or an empty
state, an `Ownership` `Owned`/`Rented` toggle pair and `Replace the card in Slot N`; `Save deck`; then a
`Choose a card` region: `The picker writes to Slot 1. Say which it is as you equip it: a card you own or
one you rented.`, a `Type` filter, `Name contains` + `Filter`, the `#deck-pick` select (251 options), and
`Equip owned to Slot N` / `Equip rented to Slot N` / `Set Slot N to not equipped`.

#### Values entered + source trace
| Slot | Card (picker label) | Control used | Source |
|---|---|---|---|
| 1 | `Tokai Teio [Dream Big!] · SSR Speed` | Equip owned | §1.4 line 91 |
| 2 | `Nishino Flower [Even the Littlest Bud] · SSR Speed` | Replace Slot 2 → Equip owned | §1.4 line 92 |
| 3 | `Biko Pegasus [Double Carrot Punch!] · SSR Speed` | Replace Slot 3 → Equip owned | §1.4 line 93 |
| 4 | `Matikanetannhauser [Just Keep Going] · SSR Guts` | Replace Slot 4 → Equip owned | §1.4 line 95 |
| 5 | `Haru Urara [Urara's Day Off!] · SSR Guts` | Replace Slot 5 → Equip owned | §1.4 line 96 |
| 6 · Friends | `Super Creek [Piece of Mind] · SSR Stamina` | Replace Slot 6 → **Equip rented** | §1.4 lines 94, 98-101 |

Slot order for the five owned cards is **Not present in source — UI-required choice**: §1.4 lists the deck
without slot numbers (Improvisation log I-06). Super Creek went to Slot 6 because §1.4 lines 98-101 make
her the borrowed card and the UI names Slot 6 the friend slot.

#### Source data with no UI home
- **Client levels and limit breaks** (35/35, 30/30, 30/30, 50/50, 30/30, 35/35, §1.4 lines 89-96): no
  field (U-08).
- **The reached per-card effect values** ("Client rows visible", §1.4 lines 109-116): the deck analysis
  prints catalog maxima and disclaims interpolation, so the run's own figures have nowhere to go (U-08).

#### Friction / errors / dead ends
- **The URL query lagged the picker by one click.** After `Replace the card in Slot 2` the URL read
  `?deck_slot=1`; after `Replace the card in Slot 3` it read `?deck_slot=2`; the picker itself was correct
  every time (`Card for Slot 2`, equip buttons labelled `… to Slot 2`). The query caught up on the next
  render (F-03).
- The step's copy now states the ownership flag **is written to the run**: "The owned or rented flag is
  kept in this setup draft, and Preflight writes it onto the run as one value per slot, where it stays
  after a reload." — the pass-1/pass-2 "no column for it yet" sentence is gone.
- Two controls advance to the same place: the step-nav `Support deck` link and the `Next: Support deck`
  button; the first strict-mode click attempt resolved to both and had to be re-targeted (§3.3: the
  latter, the wizard's own forward control, was used).

#### Outcome / next screen
`Save deck` → `Next: Preflight` → S07.

---

### S07 — Preflight (step 6 of 6)

#### Route and trigger
Route `/career/setup/preflight`, title `Career contract`. Trigger: S06 `Next: Preflight`.

#### What the screen asks for
Read-only, then the wizard's single write. A `Warnings` region; `Build` (trainee, scenario, target,
ancestry with parents, grandparents and sparks); `Support deck` (six slots with type and ownership, and
"6 of six positions carry a card."); the Deck analysis; `Target`; and `Back` / `Edit Legacy` / `Edit Deck`
/ `Edit Target` / `Start Career`.

#### Values entered + source trace
None. Only `Start Career` was pressed.

#### Source data with no UI home
- `YOUR TARGET · N/A` — the contract's own record that the run carries no build target.

#### Friction / errors / dead ends
- **The ancestry survived to the contract.** `Parent A: Vodka Rank B+` with `Blue on Power★1`;
  `Grandparent A1: Daiwa Scarlet No sparks recorded`; **`Grandparent A2: Seiun Sky Blue on Stamina★3`**;
  `Parent B: Maruzensky Rank UG Rented from a friend` with `Blue on Speed★2`; `Grandparent B1/B2: No
  sparks recorded`. This is the pass-2 finding reversed, and it shows the `UG` letter and a grandparent
  spark persisting end to end.
- Exactly one warning: `Your target is not entered.` with `Open that step`. (Pass 2 had two.)
- The deck copy is explicit and correct: "starting the career writes it onto each slot's deck row, one
  value per position. A position holding no card creates no deck row, and a card whose flag was never
  chosen is stored as no flag, never as Owned."
- `Start Career` remained enabled with the warning outstanding.

#### Outcome / next screen
`Start Career` → `Run created.` → S08 at `/training-runs/11/cockpit`.

---

### S08 — Career Cockpit (first arrival)

#### Route and trigger
Route `/training-runs/11/cockpit`, title `Career: Rice Shower`. Trigger: S07 `Start Career`.

#### What the screen asks for
Run identity block (trainee; a `rarity and potential level has not been recorded` badge; SCENARIO; YEAR;
MONTH; TURN; TEAM RANK; SPIRIT BURSTS; an "Edit the run's identity" form holding `Status`, `Scenario`,
the **Growth rate** five-field row, and `Delete this career`); Export `Download CSV` / `Download JSON`; a
"Support deck / Edit this run's deck" region; `Stats and state` (five stats, Energy, Mood, Fans, Skill
Points); `Advisor` (recommendation + why); a no-turn region; `Actions` (Training, Race, Rest, Recreation,
Scenario action, Event, Inheritance); the Unity Cup scenario panel (team race circles, the G…S+ ladder,
team stat grades, Spirit Burst states, Current Spirit, the burst-count band table, Special Training
notes); `Races in this run`; and links to the Career Timeline and Career Result.

#### Values entered + source trace
None on first arrival. (Later on this same screen, at S10, the Growth rate row was filled — recorded
there.)

#### Source data with no UI home
- **YEAR / MONTH / TURN** read `N/A / N/A / 0`: §1.1 line 21's "Senior Year Early Oct, 5 turns left, 5
  turns until the Unity Cup" has no field anywhere (U-02).
- **Rarity ★3 and Potential Lvl 2**: named as unrecorded by the badge, but the identity form offers no
  field for either — it holds status, scenario, growth rate and delete (E-08, U-03).
- **Team identity and the §1.8 numbers** — "Blue Bloom", motto "Dreaming Big", league 8th, four rounds
  won, 55 Unity Trainings, 6 Spirit Bursts, 5 Extreme, 20/20 members, the +30 ranking bonus, grades
  A/A/A/S/A — the panel carries Team Rank and Spirit Bursts readouts only, and states bursts "are recorded
  as states, not counters (D-223)", so §2.4's 10–12 band conclusion cannot be entered (U-09).

#### Friction / errors / dead ends
- The stat strip prints `Speed N/A / no target set — No turn has recorded this stat yet, and no target was
  entered for it.` The scenario's own 1300/1800 base is absent from the strip whenever no target exists
  (pass 2's finding; still true, though the wording `no target set` is clearer than pass 2's bare `N/A`).
- The Advisor declines: `Recommendation unavailable because Energy has not been entered.` /
  `This run has recorded no Energy, so there is no band to read and nothing to rank against.` Correct for
  an empty run — and it becomes the subject of F-01 once a band is recorded.
- The Unity Cup band table carries its caveat badge naming all three publisher conflicts (§8.2's live
  disagreement is surfaced, not silently settled).

#### Outcome / next screen
CTA `Record the first turn through Training` → S09.

---

### S09 — Training decision and the turn write

#### Route and trigger
Route `/training-runs/11/training`; the window title rendered `Trainer Desk` on arrival and
`Training decision: Rice Shower` after the card selection. Trigger: S08 CTA
`Record the first turn through Training`.

#### What the screen asks for
Five training cards (Speed, Stamina, Power, Guts, Wit), each with a rank line, `EXPECTED GAINS`,
`ENERGY COST` with its declared-constant provenance, `SUPPORTS ENTERED FOR THIS TRAINING`, `TARGET
DEFICIT`, `RISK`, `Train` and `Inspect details`; then `Record the turn`: `Turn *` (default 1, `min=1`),
`Speed *`, `Stamina *`, `Power *`, `Guts *` (`max=1300`), `Wit *` (`max=1800`), `Skill Points`, `Fans`,
`Energy` as `Exact` / `Band` / `Not recorded` radios (Band reveals a Low/Mid/High select), `Mood`
(not recorded, GREAT, GOOD, NORMAL, BAD, AWFUL), `Outcome *` (Choose, Success, Failure), a `Facility
levels this turn` disclosure (five 1–5 inputs), a `Preview readings` disclosure (`Failure rate` 0–100 and
six gain fields), and `Record this training`. Requiredness is signalled by `*` and enforced on submit.

#### Values entered + source trace
| Value | Field | Source |
|---|---|---|
| Speed card | `Train` | §1.7 lines 232-234 (the observed tile is the Speed facility) |
| `1` (left at the default) | `Turn *` | **Not present in source — UI-required choice** (§1.1 line 21 gives a position, not a turn number) |
| `607` | `Speed *` | §1.2 line 28 |
| `577` | `Stamina *` | §1.2 line 29 |
| `549` | `Power *` | §1.2 line 30 |
| `736` | `Guts *` | §1.2 line 31 |
| `397` | `Wit *` | §1.2 line 32 |
| `173` | `Skill Points` | §1.2 line 34 |
| `209245` | `Fans` | §1.1 line 22 |
| Band → `Low` | `Energy` | §1.7 lines 224-230 (no number exists; "low" is the client's own band) |
| `GREAT` | `Mood` | §1.7 line 219 |
| `Success` | `Outcome *` | **Not present in source — UI-required choice** (§1.7 records the tile's 39% failure rate, not a resolved outcome) |
| `4 / 4 / 4 / 5 / 4` | Facility levels Speed…Wit | §1.7 line 234 |
| `39` | Preview `Failure rate` | §1.7 line 233 |
| `18` | Preview gains → Speed | §1.7 line 233 |
| `8` | Preview gains → Power | §1.7 line 233 |
| `6` | Preview gains → Skill Points | §1.7 line 233 |

All 21 form values were read back from the DOM immediately before submit and matched the table above.

#### Source data with no UI home
- **The run's position** again: every value above is the Senior Early October state recorded at turn 1.
- **The `Floor Cleaning` banner and the three card avatars** (§1.7 line 232): no field.
- **§1.3's owned skills and §2.2's purchasable pool**: not on this screen.

#### Friction / errors / dead ends
- Two pointer actions timed out on this screen and each succeeded on a single recorded retry: the
  `preview_gains[power]` fill (element resolved, fill did not complete inside the 5 s tool window) and the
  `Record this training` click (the click **was** dispatched — the write landed while the tool timed out
  waiting for the response). Both are harness timing, not application behaviour; the final submitted
  values were the source values (§4.8).
- The cards' `TARGET DEFICIT` reads `N/A — No build target was entered for this stat`, the honest result
  of carrying no target; `EXPECTED GAINS` and `RISK` are `N/A` with sourced reasons.
- The five `Train` buttons and the submit button are distinguishable only by position; the submit is the
  11th of 11 buttons on the screen.

#### Outcome / next screen
`Record this training` → the write landed on S10 (the form's own copy says "The write lands on the
Cockpit").

---

### S10 — Career Cockpit (after the turn write; correction and identity forms)

#### Route and trigger
Route `/training-runs/11/cockpit`. Trigger: S09's own write, which states "The write lands on the
Cockpit, where the recorded turn appears in the correction selector".

#### What the screen asks for
As S08, now populated, plus two further forms this pass exercised: a `Correct a turn by hand` /
`Correct turn 1 by hand` disclosure whose form prefills `speed 607`, `fans 209245`, `mood GREAT`,
`energy_state band`, `energy_band low`; and the `Edit the run's identity` form whose **Growth rate** row
holds five fields `growth_rate[Speed…Wit]` (labelled with a `%` unit in the visible row) and submits via
`Save identity`.

#### Values entered + source trace
- Read-back verified, no entry: Speed 607, Stamina 577, Power 549, Guts 736, Wit 397, `Mood GREAT ↑`,
  `Fans 209,245 · Star · 30,755 to next`, Skill Points 173 (source §1.2 lines 28-34, §1.1 line 22,
  §1.7 line 219).
- `growth_rate` 0 / 10 / 0 / 20 / 0 → the identity form's `Growth rate` row, submitted with `Save
  identity` → source §7 item 2 (lines 595-602): the export's `stat_bonus` row is the Growth Rate display,
  reading +0/+10/+0/+20/+0 in GameTora's column order.
- The `Correct turn 1 by hand` disclosure was opened to read the prefilled energy state; `Save correction`
  was pressed once with no field changed, as the bounded probe for feedback behaviour (see Friction).

#### Source data with no UI home
- **Energy.** `ENERGY · Energy N/A` in the strip and `Recommendation unavailable because Energy has not
  been entered … there is no band to read` in the Advisor, although a `Low` band is on the run (F-01).
- **The turn's activity, failure rate, facility levels and preview gains** are not displayed anywhere on
  the cockpit.
- **Fans class** is now *derived* (`Star`, `30,755 to next` — §1.1 lines 21-22's pair arrives without a
  field), but the class ladder's target (Top Star 240,000) is not shown as a number.

#### Friction / errors / dead ends
- **F-01 (the pass-2 finding, narrowed).** The `Low` band was accepted on S09 and **round-trips into the
  correction form** (`energy_state: band`, `energy_band: low` prefilled — pass 2's correction form had no
  band control at all), yet no read display consumes it: the strip says `Energy N/A`, the Advisor says
  there is no band to read, and S14's career-state block also prints `Energy N/A`. The value is stored and
  editable but invisible everywhere a user would look for it.
- **Neither `Save correction` nor `Save identity` produced any visible feedback** — no flash line, no
  `[role=alert]`. The identity form's five values were still present in the inputs after the submit, which
  is indistinguishable from an unsaved form (F-04).
- The growth-rate row lives in the **identity** form, not the correction form; both sit on the same screen
  behind similar disclosures and neither names the other. The first submit (`Save correction`) was there-
  fore a no-op for the values I had entered, discovered only by inspecting which form owned the fields.
- `YEAR Junior Year / MONTH Early January / TURN 1` — the source's Senior Early October snapshot rendered
  as the career's opening turn (E-07).

#### Outcome / next screen
Went to S11.

---

### S11 — Inheritance event

#### Route and trigger
Route `/training-runs/11/inheritance`, title `Inheritance: Rice Shower`. Trigger: navigated to the run's
own route (the Cockpit's `Inheritance` action target).

#### What the screen asks for
Read-only. An explanation that the Predicted section shows the Sparks carried by the chosen parents and
grandparents and that no outcome is computed (`ADR-0020 §3`); an `Inheritance Milestones` strip
(`● Career Start completed`, `○ Classic Early April upcoming`, `○ Senior Early April upcoming`); a
`Predicted Sparks` region with per-kind counts labelled "Total across both parents and grandparents";
`EXPECTED INHERITANCE · N/A (Not computed)`; and an Observed section.

#### Values entered + source trace
None entered.

#### Source data with no UI home
- The three inheritance moments exist as glyphs, but the run is at turn 1, so two of three are `upcoming`
  regardless of the source's actual position.

#### Friction / errors / dead ends
- **F-02: `BLUE 2 Sparks` is wrong for this run.** Three blue sparks are on the run's Legacy
  configuration — Parent A Power ★1, Parent B Speed ★2, and **Grandparent A2 Stamina ★3** — and the S07
  contract displays all three. The count is labelled "Total across both parents and grandparents", yet the
  grandparent spark is excluded, so the total understates the configuration by one. (Passes 1 and 2 could
  not test this: pass 1 had no grandparent sparks, and pass 2's ancestry was never saved.)
- The screen renders (pass 1's 500 on this route is gone for a run that has ancestry — that fix was
  already verified against run 8 during pass 2's cross-check).

#### Outcome / next screen
Navigated to S12.

---

### S12 — Race decision

#### Route and trigger
Route `/training-runs/11/races`, title `Race decision: Rice Shower`. Trigger: navigated from S11.

#### What the screen asks for
`The turn being decided` (`Junior Year · Late January · Turn 2`); `Mandatory races` (Junior Make Debut,
Junior turn 12, flagged `NEXT`, `Not recorded yet`; URA Finals Qualifier / Semifinal / Final (Aoharu),
Finale, all `Not recorded yet`); `Next obligation: Junior Make Debut, Junior turn 12. It reads "Not
recorded yet".`; `Readiness · N/A — No readiness band is computed for this race.`; and `Races to decide
between` with `Calendar race` / `Race not on the calendar` and `Plan the whole calendar`.

#### Values entered + source trace
None entered.

#### Source data with no UI home
- **The §1.6 race-day card** (lines 193-205): Kyoto Daishoten (G2, Kyoto Turf 2400 m, Fall, **Soft**,
  6,700 fans on a win) and Mile Championship Nambu Hai (G1, Morioka Dirt 1600 m), the entry criteria, the
  five-place fan ladder, and the client's `top contender` verdict (U-10).
- **The four team rounds already won and the pending finals** (§1.6 lines 181-184), and the **Arima Kinen
  goal** (lines 186-188) (U-11).

#### Friction / errors / dead ends
- `No race on this scenario's calendar falls at this turn` is true for turn 2 and false for the run: the
  source's turn is Senior Early October, where two races are offered. The position problem governs this
  screen exactly as in passes 1 and 2.
- The obligation list is the scenario's, so it cannot show that this run already cleared four team rounds.

#### Outcome / next screen
`Plan the whole calendar` → S13.

---

### S13 — Race planner

#### Route and trigger
Route `/training-runs/11/races/planner`, title `Race planner: Rice Shower`. Trigger: S12
`Plan the whole calendar`.

#### What the screen asks for
Read-only. `Planning from Junior Year · Turn 2`; `Held figures` (Win probability, Expected risk, both
`N/A`); `Deadlines`; `Mandatory races`, each expandable to a per-race panel (Grade, Distance, Distance
band, Surface, Running style, Fan gain, Reward, Skill points, Scenario reward, Estimated win probability)
with a `Compare` control; `Upcoming races`. 11,769 characters of text (pass 2 measured 17,409 on the same
route).

#### Values entered + source trace
None entered.

#### Source data with no UI home
- The **Soft** going and the per-place fan payouts (§1.6 lines 198-203): `FAN GAIN` is `N/A` with the
  reason that the payout curve id is stored but no payout table is (U-10).
- Goal races remain unrecorded (`N/A`, KI-34) (U-11).

#### Friction / errors / dead ends
- The placeholder races (Junior Make Debut, Junior Maiden Race) carry no distance, band or surface, while
  Chukyo Junior Stakes resolves to 1,600 m / Mile / Turf with a 350-fan gate — catalog completeness, per-
  field disclosure, not an app defect.
- Win probability is withheld on every panel by design.

#### Outcome / next screen
Navigated to S14.

---

### S14 — Event decision

#### Route and trigger
Route `/training-runs/11/events`, title `Event decision: Rice Shower`. Trigger: navigated from S13.

#### What the screen asks for
Coach recommendation; `Current career state` (five stats, Energy, Mood, Fans, Skill Points); `Known
outcomes on this run`; `Recorded events`; and a record form (`turn`, `event_type`, `source_name`,
`choice_label`, `support_card_name`) with its submit.

#### Values entered + source trace
None entered.

#### Source data with no UI home
- The source records no event that fired during the run, so §1.8's Team Info content and §2.4's payout
  event have nothing to map here.
- §1.3's owned-skill list has no home on any screen of the flow (U-07).

#### Friction / errors / dead ends
- The career-state block reads back the write correctly: `Speed 607/1300 cap`, `Wit 397/1800 cap`,
  `Mood GREAT`, `Fans 209245`, `Skill Points 173` — and **`Energy N/A`** despite the stored band (part of
  F-01). The denominator is now explicitly labelled `cap`, which answers pass 2's unlabelled-denominator
  finding on this screen (the cockpit still shows the target-based view).
- The `turn` select offers only turns that exist (Turn 1), inheriting the position problem.

#### Outcome / next screen
Navigated to S15.

---

### S15 — Career Timeline

#### Route and trigger
Route `/training-runs/11/timeline`, title `Career timeline: Rice Shower`. Trigger: navigated from S14.

#### What the screen asks for
A type filter and a rail. Header states each row's BEFORE block is the previous logged turn and that
EXPECTED/ACTUAL collapse because `ADR-0003` stores the absolute end-of-turn value.

#### Values entered + source trace
None entered.

#### Source data with no UI home
- The four won team rounds, the cleared goals and the pending finals would appear as rows; none can exist
  at turn 1 (U-09, U-11).

#### Friction / errors / dead ends
- `Timeline opens here · Junior Year · Early January · Turn 1`, one row, `showing 1 of 1`, row text `Turn
  1 · Turn recorded` — the row does not name the activity recorded on S09, so the Speed choice is not
  visible here.

#### Outcome / next screen
Navigated to S16.

---

### S16 — Career Result

#### Route and trigger
Route `/training-runs/11/result`, title `Career result: Rice Shower`. Trigger: navigated from S15.

#### What the screen asks for
Read-only refusal, with `Back to the Cockpit`, `Career Timeline`, `Cockpit`.

#### Values entered + source trace
None entered.

#### Source data with no UI home
Its subject matter — final build, final race history, scenario objectives — is unreachable while the run
is Active, which is the correct outcome for a mid-career snapshot.

#### Friction / errors / dead ends
- `This career is still running, so there is no result to read yet. The result screen reads a run whose
  status is Completed; until then the Cockpit is where the next turn is decided and where the status
  changes.` The status was never touched, so the snapshot was not converted into a finished career. The
  pass-2 stale reference to "the run record screen" is gone.

#### Outcome / next screen
Navigated to S17.

---

### S17 — Skills planner

#### Route and trigger
Route `/training-runs/11/skills`, title `Skills planner: Rice Shower`. Trigger: navigated from S16.

#### What the screen asks for
Read-only. A `Your target` state line, `Skill Point coverage`, `Hint discounts`, and priority states
(`! Required`, `+ Available`, `● Learned`, `◇ Inherited`).

#### Values entered + source trace
None entered.

#### Source data with no UI home
- **§1.3's ten owned skills plus Corner Recovery ○**: `No skill is marked for this run yet`, and the
  planner states it "reorders the priorities a target names; it does not add any" — with no target, no
  skill can be marked from here either (U-07).
- **§2.2's purchasable pool and the 173 SP question**: the coverage panel has nothing to sum.

#### Friction / errors / dead ends
- `Hint discounts are not modelled.` — the §2.1 ladder (10/20/30/35/40%) is absent this pass. Pass 1
  rendered the ladder on this route with a Confirmed marker; passes 2 and 3 both find it disclaimed. The
  two readings are recorded per pass and not reconciled (E-11).
- Coverage reads `This run has no build target, so no skill is Required and there is nothing to sum` —
  clearer than pass 2's "cost 0 SP … leaving 173".

#### Outcome / next screen
Navigated to S18.

---

### S18 — Support deck (run-scoped)

#### Route and trigger
Route `/training-runs/11/deck`, title `Support deck`. Trigger: navigated from S17 (the last surface the
run exposes).

#### What the screen asks for
The six slots, each with the card, its type, its scenario-link flag and its full effect list; a per-slot
`Owned`/`Rented` toggle; `Replace the card in Slot N`; `Confirm deck` ("Confirming replaces the whole
deck: slots left empty are cleared."); and a picker with `Type`, `Rarity`, `Availability`, `Name contains`
filters.

#### Values entered + source trace
None entered — used to read back what S06 set.

#### Source data with no UI home
- Card client levels / limit breaks and the reached per-card effect values, as on S06 (U-08).

#### Friction / errors / dead ends
- **All six cards persisted with their ownership flags**: slots 1–5 `Owned:true`, slot 6 · Friends
  `Rented:true` — the borrowed friend card is distinguishable after a full page load (pass 1's loss, now
  fixed and re-confirmed on a third run).
- Scenario-link derivation matches the source exactly: only Slot 5 (Haru Urara) reads `Scenario Link`;
  the other five read `Not a Scenario Link` (§1.4 lines 103-107). An intermediate read in this pass
  reported all six as linked; that was the auditor's own loose regex matching the substring inside
  `Not a Scenario Link`, corrected before recording — noted here because §4.5 requires the instrument to
  be distinguished from the observation.

#### Outcome / next screen
End of the reachable flow for an Active run.

---

## 7.4 Improvisation log

Every value or choice made that the source does not provide:

| # | Screen ID | Field / control | Value chosen | Why this value | Alternatives rejected |
|---|---|---|---|---|---|
| I-01 | S09 | `Turn *` | `1` (the field's own default, left untouched) | Required field with `min=1` and a default; the source gives a position, not a number, and inventing "67" would fabricate history | Deriving a turn number from "5 turns left" against a 72-slot calendar — rejected as arithmetic the source does not state |
| I-02 | S09 | `Outcome *` | `Success` | Required select with no "not resolved" option; the source records an unresolved tile (39% failure), so a value had to be picked | `Failure` — rejected: the snapshot's stats are the *result* of the turn, and Failure with a penalty kind would contradict them |
| I-03 | S09 | `Energy` representation | `Band → Low` | §1.7 lines 224-230: no number exists; "low" is the client's own band, and the UI's `Band` option exists for exactly this | `Exact` with an invented number — rejected; `Not recorded` — rejected because the source does state the band |
| I-04 | S04 | All target fields | Left empty (submitted) | The source records no build target; the empty state is the faithful entry | Entering Turf/Pace Chaser from §1.2 line 59 — rejected: partial entry would still fabricate purpose and five numbers |
| I-05 | S05 | Spark coverage | 3 of the source's blue sparks entered (Parent A Power ★1, Parent B Speed ★2, Grandparent A2 Stamina ★3); pink/green/white and 3 blues not entered | Proves the mechanism, including the new grandparent surface, at one-third of the cost of ~35 rows | Entering the full §1.5 table — rejected for budget; the omission is documented, not silent |
| I-06 | S06 | Slot order for the five owned cards | §1.4 table order, Tokai Teio first | The source lists the deck without slot numbers; table order is the only ordering it provides | Any other permutation — equally unsourced |
| I-07 | S06 | Super Creek's ownership | `Equip rented` | §1.4 lines 98-101: the sixth slot is a friend card borrowed from another account | `Equip owned` — rejected: would erase the one distinction the source draws |
| I-08 | S10 | Growth rate row | `0 / 10 / 0 / 20 / 0` | Source §7 item 2 states the export's `stat_bonus` row is the Growth Rate display in this column order | Leaving blank — rejected: the value is sourced and the field exists |
| I-09 | S05 | `Affinity for the pair` | Left `Not recorded` | §1.5 states no pair grade | Any of △/○/◎ — would be fabrication |
| I-10 | S02 | Scenario card | Unity Cup (only selection made) | §1.1 line 19 | — |

## 7.5 Findings register

| ID | Type | Screen(s) | Short description | Blocking? |
|---|---|---|---|---|
| F-01 | Functional | S09, S10, S14 | A recorded Energy **band** (`Low`) is stored and round-trips into the correction form, but no read display consumes it: the cockpit strip prints `Energy N/A`, the Advisor says "there is no band to read", and the event screen's career state also prints `Energy N/A` | No |
| F-02 | Functional | S11 | `Predicted Sparks` counts `BLUE 2 Sparks` where the run's Legacy configuration holds three blue sparks (two parents + one grandparent, all shown on the S07 contract); the count is labelled "Total across both parents and grandparents" | No |
| F-03 | Functional | S06 | The URL query `?deck_slot=N` lags one click behind the picker's actual target after `Replace the card in Slot N`, so a copied deep link can point at the previous slot | No |
| F-04 | Functional | S10 | `Save correction` and `Save identity` give no visible feedback (no flash, no alert); a user cannot tell whether the write happened | No |
| E-01 | Experiential | S03 | Catalogue aptitudes (Mile C, Front B, Late C) disagree with the source's client read (Mile B, Front A, Late B); the source's own erratum (lines 61-71) declares the difference open, and it reproduced identically across three passes | — |
| E-02 | Experiential | S03 | The run's costume form is the unnamed second form; the picker shows the debut portrait, so `[Rosy Dreams]` cannot be identified or chosen | — |
| E-03 | Experiential | S04, S08, S14, S17 | The no-target state is stated three different ways (`no target set`, `N/1300 cap`, "compare against nothing"), and the scenario's 1300/1800 base disappears from the cockpit strip when no target exists | — |
| E-04 | Experiential | S05 | `UG` is not in the `Own rank (letters)` suggestion set (G…S, B+, A+, S+); typing it works and renders, with no indication that it is off the offered set | — |
| E-05 | Experiential | S05 | Grandparents take a name and sparks but no rank field, so four of the tree's six ranks (B+, A, UG, UG) have no home | — |
| E-06 | Experiential | S05 | Recording §1.5 as written means ~35 spark rows of three controls each through one-row-at-a-time editors behind per-node disclosures, with no bulk entry and no running count | — |
| E-07 | Experiential | S04, S08, S09, S15 | The run's position is inexpressible: YEAR/MONTH derive from `Turn`, so a Senior Early October snapshot logs as `Junior Year · Early January · Turn 1` and the Timeline, race surfaces and milestones all treat that as fact | — |
| E-08 | Experiential | S03, S08 | Rarity ★3 / Potential Lvl 2 are named as unrecorded by a cockpit badge, but the identity form offers no field for either | — |
| E-09 | Experiential | S02 | Scenario cards are asymmetric (Unity Cup fully described; three others mostly `N/A`), and the Unity Cup card still prints the value twice — `Ruleset version · N/A` and `Rule family · Team` | — |
| E-10 | Experiential | S01 | `New Career` starts a clean draft beside an existing Active career with no prompt or handoff | — |
| E-11 | Experiential | S17 | The §2.1 hint-discount ladder reads `Hint discounts are not modelled.` this pass (rendered with a Confirmed marker in pass 1); unreconciled across passes | — |
| E-12 | Experiential | S05, S09 | The spark `Target` selects and the energy band select render without accessible names, and two pointer actions needed single recorded retries on S09 (harness timing, not app behaviour) | — |
| E-13 | Experiential | S06, S09 | The window title falls back to the generic `Trainer Desk` on some Inertia arrivals before settling (`Support deck`, `Training decision: Rice Shower`) | — |
| U-01 | Unrepresented | S04, S09 | The run's own caps 1325/1322/1368/1308 and §1.2's whole cap-composition question — fields max out at 1300/1800 and no screen asks for a cap or its inputs | — |
| U-02 | Unrepresented | S04, S08, S09, S15 | The run's position: Senior Year Early Oct, 5 turns left, 5 turns until the Unity Cup | — |
| U-03 | Unrepresented | S03, S08 | Rarity ★3 and Potential Level 2 | — |
| U-04 | Unrepresented | S03 | The `[Rosy Dreams]` costume identity | — |
| U-05 | Unrepresented | S05 | The four grandparents' ranks (B+, A, UG, UG) | — |
| U-06 | Unrepresented | S05 | Three of six blue sparks and every pink, green and white spark in §1.5's table (fields exist; not entered — I-05) | — |
| U-07 | Unrepresented | S14, S17 | §1.3's owned-skill list (ten chips plus Corner Recovery ○) and §2.2's purchasable pool with the 173 SP question | — |
| U-08 | Unrepresented | S06, S18 | Card client levels / limit breaks and the deck's reached per-card effect values | — |
| U-09 | Unrepresented | S08, S15 | §1.6/§1.8 team identity: name, motto, league 8th, four rounds won, 55 Unity Trainings, 6+5 bursts, 20/20 members, the +30 ranking bonus, grades A/A/A/S/A | — |
| U-10 | Unrepresented | S12, S13 | §1.6's race-day card: both options, the Soft going, the fan ladder, entry criteria, the `top contender` verdict | — |
| U-11 | Unrepresented | S12, S13 | Goals: the three cleared and the active Arima Kinen goal | — |
| U-12 | Unrepresented | S17 | §2.1's hint-discount ladder as a displayed figure this pass | — |

## 8. Closing assessment

### 8.1 What worked

- **The whole setup flow, this time.** Unlike pass 2, every step's write landed: scenario, trainee, an
  empty target, the full ancestry (both parents with letter ranks, all four grandparents, three sparks
  including one on a grandparent), and the six-card deck with its ownership flags. The S07 contract echoed
  all of it, `Rank B+` and `Rank UG` included.
- **The borrowed friend card is representable and persistent.** Super Creek sat in `Slot 6 · Friends` as
  `Rented`, survived `Start Career`, and read back `Rented:true` on the run's own deck screen — the §5
  "sixth slot" question answered affirmatively for the third run in a row, with the wizard's copy now
  stating plainly that the flag is written onto the run.
- **The scenario-link derivation matches the source**: only Haru Urara reads `Scenario Link` (§1.4 lines
  103-107).
- **The §1.2 stat line, SP, fans and mood entered cleanly and read back** on the cockpit, the event
  screen and the timeline, with no validation error anywhere in the pass.
- **§1.7's numberless Energy finally has a write path**: `Exact / Band / Not recorded` with Low/Mid/High
  accepts the source's "low" without inventing a number — and the band provably persists (the correction
  form round-trips it), which narrows the residual problem to display (F-01).
- **§1.7's observed turn is now fully recordable**: facility levels 4/4/4/5/4, Failure 39%, and the
  +18/+8/+6 preview all have fields; none existed in pass 1.
- **The source's Growth Rate row gained a home this pass** (the identity form's five-field `Growth rate`
  row), and the cockpit **derives** the fans class and gap (`209,245 · Star · 30,755 to next`) from the
  count.
- **The wizard stopped forcing invention** (empty target accepted) and **the contract reports its own
  gaps** (`Your target is not entered.`), while **the Result screen refused to present an unfinished
  career as finished** and the status was never touched.

### 8.2 Functional findings

- **F-01 (S09, S10, S14).** *Observed:* `Band → Low` submitted on S09; the cockpit strip prints `Energy
  N/A`; the Advisor prints `Recommendation unavailable because Energy has not been entered.` and `there is
  no band to read`; S14's career state prints `Energy N/A`; the correction form prefills `energy_state:
  band`, `energy_band: low`. *Expected:* a stored band to be visible where Energy is displayed, or the
  Advisor to read it. *What next:* recorded and continued; the band was left in place. (Pass 2 recorded
  the same display gap and could not tell whether the value was stored; this pass establishes that it is.)
- **F-02 (S11).** *Observed:* `BLUE 2 Sparks` against a configuration whose contract lists three blues,
  on a count labelled "Total across both parents and grandparents". *Expected:* 3, or a label that names
  what it counts. *What next:* recorded; nothing was changed.
- **F-03 (S06).** *Observed:* after `Replace the card in Slot 2` the URL read `?deck_slot=1` (and
  similarly one behind at each subsequent retarget), while the picker and the equip buttons showed the
  correct slot. *Expected:* the query to match the picker's target at rest. *What next:* continued; the
  value caught up on the following render.
- **F-04 (S10).** *Observed:* `Save correction` and `Save identity` each returned with no flash and no
  alert. *Expected:* confirmation, or an error. *What next:* the identity values were re-read from the
  inputs (indistinguishable from unsaved state) and the observation recorded; persistence of that write is
  **not** claimed.

### 8.3 Experiential findings

See E-01 … E-13 in the register (§7.5); each is anchored to its screen section above. Two are worth
singling out because they dominate the cost of the walk: E-07 (the run's position is derived from a
required turn number, so a Senior October snapshot becomes the career's first turn on every screen
downstream) and E-06 (the source's §1.5 table is ~35 rows of three controls each, with no bulk entry).
Timing/keyboard observations, marked optional per the procedure: two 5 s pointer timeouts on S09
(harness, single recorded retries each, final values correct) and the unnamed selects noted in E-12.

### 8.4 Unrepresented source data

U-01 … U-12 in the register (§7.5), each cross-referenced to the screen where it became relevant. The
heaviest are U-01 (§1.2's caps and their composition — the document's central unresolved arithmetic has
had no home in any of the three passes), U-09 (the entire §1.8 Team Info panel, without which §2.4's
10–12 band conclusion cannot be recorded), and U-07 (§1.3's owned-skill list).

### 8.5 Walk completion

- **Reached the end of the available flow: yes.** S01→S18 in order; S16 is the designed dead end for an
  Active run and S18 is the last surface offered.
- **Stopping point and reason:** none forced; the pass ended because the flow ended.
- **One-pass deviations:** the driver switch at S01 (recorded above) happened before any value was
  entered, so no screen was revisited and no choice was remade. One bounded empty-submit probe on S04 and
  one no-change `Save correction` probe on S10, both permitted by §4.7. Two single retries on S09 pointer
  timeouts, recorded in §4.8.
- **Environment mutations:** the driver switch itself; nothing else — no cache or cookie clearing, no
  resize, no login, no process restart.
- **Data intentionally omitted:** three blue sparks and all pink, green and white sparks of §1.5 (I-05 —
  the fields exist and accepted the three entered; volume, not a gap); the pair's affinity grade and the
  Energy number were left unset because the source states neither.
- **Values in the final persisted state that are not from the source document:** the `Turn` number (1,
  I-01), the `Outcome` (Success, I-02), the deck's slot ordering (I-06), and the empty build target (I-04,
  which records the source's actual condition). The Energy band (Low), all stats, SP, fans, mood, facility
  levels, failure rate, preview gains, growth rates, both ranks, the rented flag and every entered spark
  are sourced.
- **Persistence claims are limited to read-backs:** deck and flags (S18), ancestry and ranks (S07
  contract), stats/SP/fans/mood (S10, S14, S15), the Energy band (S10's correction form only — and its
  absence from every display is F-01). The identity-form growth-rate write is **not** claimed as persisted
  (F-04).

## Appendix A — Derived probe list and answers

Derived from the source before the walk (§5.2); each answered with a screen ID.

| # | Source anomaly (with line) | Question for the walk | Answer |
|---|---|---|---|
| P1 | §1.2 lines 39-56: cap extras do not reconcile from the six blue sparks | Can the UI represent caps, sparks, both, or neither? | Neither: no cap field exists (S04 `max=1300`), sparks are enterable per ancestor (S05), and the cap composition itself has no home (U-01) |
| P2 | §1.5 lines 170-171: "no Wit spark anywhere in the tree" | Does the UI force a value for a category the source lacks? | No — sparks are opt-in rows; absence is representable by omission (S05) |
| P3 | §1.4 lines 98-101, §7 item 8: the sixth slot is a borrowed card | Is the borrowed distinction representable? | Yes: `Slot 6 · Friends` + `Rented`, persisted to the run (S06, S07, S18) |
| P4 | No run number; mid-career | Does setup assume a completed career? | No — but the position is inexpressible (E-07, U-02); nothing forced Completed, and S16 refused to show a result |
| P5 | §1.7 lines 224-230: Energy has no number | Can Energy be recorded without one? | Yes at the write (Band → Low, S09) and in the correction form (S10); no read display consumes it (F-01) |
| P6 | §8.2 lines 671-687: Extremes-count disagreement | How does the UI treat the disputed band table? | It renders it with a caveat badge naming all three conflicts and states the run's band position is not computed (S08) — the dispute is surfaced, not settled |
| P7 | §1.4 lines 103-107: only Haru Urara is a Unity Cup link | Does the app derive the same? | Yes — slot 5 only (S06, S18) |
| P8 | §6 item 7, lines 577-580: 18 named vs a 20/20 counter | Is the roster representable? | No roster surface exists anywhere in the flow (U-09) |
| P9 | §1.5 line 153: Legacy 2 is rank UG | Is a letter rank enterable? | `B+` is in the suggestion set; `UG` is not, but typing it is accepted and renders (S05, S07) (E-04) |
| P10 | §1.2 lines 43-44: six blue sparks | Are they all countable once entered? | No — the grandparent blue is excluded from the S11 total (F-02) |

## Appendix B — Stability probes

**Not run.** The primary pass consumed the available budget; §3.4 requires it to be completed first, and
it was, but no second pass was started. No reload, Back/Forward, double-submit, deep-link, or additional
invalid-input probe was performed beyond the two §4.7 probes recorded inline (S04 empty submit, S10 no-op
correction save).

## Appendix C — Remediation ideas (non-binding, not implemented)

Outside the audit's findings; nothing here was implemented, and no remediation was accepted.

- F-01: give the Energy band a read path — render it in the strip (`Energy: Low (band)`) and let the
  Advisor treat a band as sufficient for its coarse ranking.
- F-02: include grandparent sparks in the Predicted Sparks totals, or relabel the count to say which
  nodes it sums.
- F-03: update the `deck_slot` query in the same render as the retarget, or drop it from the URL.
- F-04: flash on `Save correction` / `Save identity` like the wizard's other writes.
- E-07/U-02: let the wizard or cockpit accept a position (year/month or a turn number derived from one),
  which is the single change that would make every downstream surface read the source's actual moment.
- E-05/U-05: add the two `Own rank` fields to the grandparent nodes.

## 7.7 Definition-of-done checklist

- [x] Every screen reached has its own section, in visit order (S01–S18).
- [x] Every entered value has a source trace or is marked "not present in source" (and appears in §7.4).
- [x] Every source datum without a UI home is named, on the screen where it became relevant (U-01…U-12).
- [x] The run's actual lifecycle status is preserved throughout (mid-career snapshot, never Completed).
- [x] The run is not classified as Veteran or Legacy (parents were *selected from* the Veteran library;
      the run itself was never filed as either).
- [x] Functional and experiential findings are separate (§8.2 / §8.3).
- [x] The findings register matches the findings sections (F-01…F-04, E-01…E-13, U-01…U-12).
- [x] The walk's stopping point and reason are stated (§8.5).
- [ ] One screenshot per screen under `docs/audits/rice-shower-unity-cup-ux-walk-3/screens/` — **not
    met**: the directory was created but no screenshots were captured (see the header's Artefacts line);
    the driver's own `.playwright-mcp/` snapshots and console logs exist outside the requested layout.


