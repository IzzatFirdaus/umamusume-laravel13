# UX walk audit: [Rosy Dreams] Rice Shower / Unity Cup (round 5, second independent pass)

**Audit ID:** `RS-UC-UXWALK-2026-11-03-05`

**Source document:** `docs/[Rosy_Dreams]Rice_Shower_Unity-Cup.md`

**Relationship to round 4:** `docs/audits/rice-shower-unity-cup-ux-walk-4.md` records a walk run on 2026-10-24 against commit `f6018f87`. This pass ran on 2026-11-03 against `34fd3c9e`, and the application between those commits is materially different (rebuilt wizard, renamed steps, new Team setup / Scenario detail / Inheritance pages). Round 4 is context, not evidence; every finding below was re-derived in this session. Appendix D lists where the two passes disagree.

## 7.1 Header

| Field | Value |
|---|---|
| Starting URL | `http://127.0.0.1:8000/` |
| Walk start | 2026-11-03 17:15 +08:00 (MPST) |
| Walk end | 2026-11-03 17:59 +08:00 |
| Browser | Chrome 156.0.0.0 (Playwright MCP), Windows 10 x64, viewport 958 x 888 CSS px |
| Devtools | Not open. Console read via the MCP console API: 0 errors, 1 informational message. |
| Application | Already running; not restarted |
| Git commit | `34fd3c9e7a4ae12aaa85d9e15e451369ed4b184b` |
| Working tree at start | Not clean: several docs tracked as deleted with untracked replacements under `docs/proposals/` (another session's in-flight work). Untouched by this walk. |
| Version in UI | None. Dashboard reads `Verified 2026-11-02 · GameTora scenarios.json` (data provenance). |
| Pre-existing state | Dashboard: `0 active careers, 7 finished runs, and 4 permanent Global scenarios`; 135 trainees, 1,948 skills, 566 support cards, 304 pending review matches. The run created in round 4 (id 26) is gone. |

**Method statement.** All entry went through UI controls: radios, native selects, checkboxes, text and number inputs, links and buttons. No seeder, factory, direct SQL, tinker, state-mutating artisan command, API substitution, or devtools DOM write. `browser_evaluate` was used only to read visible controls and text. One deviation: S15 was opened by typing its URL because no on-page control reached it (§7.1 deviations).

### Run identity block

| Field | Value | Source |
|---|---|---|
| Trainee | Rice Shower, `[Rosy Dreams]` | §1.1 line 37 |
| Rarity | ★3 | §1.1 line 37 |
| Potential | Potential Lvl 2 | §1.1 line 37 |
| Scenario | Unity Cup | §1.1 line 36 |
| Snapshot point | Senior Year Early October | §1.1 line 37 |
| Turns remaining | 5 | §1.1 line 38 |
| Lifecycle | Mid-career, active, unfinished | §1.1, §1.6 |
| Run number | None in source | source |
| Stats | 607 / 577 / 549 / 736 / 397 | §1.2 lines 44-49 |
| Caps | 1325 / 1322 / 1368 / 1308 / 1800 | §1.2 lines 44-49 |
| Unspent SP | 173 | §1.2 line 51 |
| Mood | GREAT | §1.7 line 236 |
| Energy | low band, no numeric value | §1.7 lines 241-247 |
| Deck | 6 SSR: Speed x3, Stamina x1, Guts x2 | §1.4 lines 106-113 |
| Borrowed | Super Creek [Piece of Mind], friend slot | §1.4 lines 114-118 |
| Skills | 11 owned; unique `Blue Rose Closer` Lvl 3 | §1.3 lines 92-96 |
| Ancestors | 6; blue sparks Power 1, Guts 1, Stamina 3, Speed 2, Speed 2, Power 3 | §1.5 lines 165-189 |
| Won | 4 preseason rounds; 3 goals cleared; race log empty | §1.6 lines 198-205 |
| Outstanding | Arima Kinen 1st (Senior Late Dec); Unity Cup finals; 30,755 fans to Top Star | §1.1, §1.6 |

**Scope and stopping point.** Reached the rebuilt six-step setup wizard, the created run 32, the cockpit, the training record, the race desk twice, the team page, the scenario detail, the inheritance screen, the skills planner and the Careers list. Stopped at S19: the race desk refused every race (F2-07) and the source's race has no recorded result.

### Deviations from the one-pass rule

1. S10 to S11 to S12: the Training screen's `Race` option navigated to the race desk, which refused (no turn logged); `Train instead` returned to the same form. No data was written in the excursion.
2. S15 `/training-runs/32/scenario` was opened by typing the URL; the Team page's career nav has no `Scenario detail` link (F2-13). Earlier screens were all seen in this session.
3. No browser Back, no reload, no restart, no second tab.

**Environment mutations.** None.

## 7.2 Screen ledger

| Screen ID | Route | Arrived from | Exited via |
|---|---|---|---|
| S01 | `/` | entry | `New Career` (sidebar) |
| S02 | `/career/setup/scenario` | S01 | `Continue to trainee` |
| S03 | `/career/setup/trainee`, `?direction=asc&search=Rice%20Shower&sortBy=name` | S02 | `Continue to build target` (link) |
| S04 | `/career/setup/trainee/30` | S03 | `Continue to build target` (button) |
| S05 | `/career/setup/target` | S04 | `Continue to legacy` after four attempts |
| S06 | `/career/setup/legacy` | S05 | `Continue to deck` |
| S07 | `/career/setup/deck` (+ `?deck_slot=1..6`, `&q=`) | S06 | `Continue to preflight` |
| S08 | `/career/setup/preflight` | S07 | acknowledge checkbox, `Create the run` |
| S09 | `/training-runs/32/cockpit` | S08 | `Record the first turn through Training` |
| S10 | `/training-runs/32/training` | S09 | `Race` pick |
| S11 | `/training-runs/32/races` | S10 | `Train instead` |
| S12 | `/training-runs/32/training` (re-entry) | S11 | `Client totals this turn` details, `Record turn 1` |
| S13 | `/training-runs/32/cockpit` | S12 submit | `Team setup` |
| S14 | `/training-runs/32/team` | S13 | typed URL |
| S15 | `/training-runs/32/scenario` | typed URL | `Races` |
| S16 | `/training-runs/32/races` | S15 | `Inheritance` |
| S17 | `/training-runs/32/inheritance` | S16 | `Skills` |
| S18 | `/training-runs/32/skills` | S17 | `Careers` |
| S19 | `/training-runs` | S18 | walk end |

No screenshots retained. Round 4's screenshot calls reported paths that do not exist in the repository; this pass therefore did not repeat them, and the written record is the evidence.
## 7.3 Screen-by-screen walk

### S01 Dashboard

#### Route and trigger
`http://127.0.0.1:8000/`, entry point.

#### What the screen asks for
No inputs. Grouped sidebar (`Play`, `My data`, `Library`, `System`), a `Help` control, tiles: `0 active careers, 7 finished runs, and 4 permanent Global scenarios.`; `Start a career`; `Import an existing career`; `DATA STATUS · Global data · Current · Verified 2026-11-02 · GameTora scenarios.json`; `REVIEW QUEUE 304 pending matches`; `TRAINEES CATALOGUED 135`; `SKILLS 1,948`; `SUPPORT CARDS 566`; `Recent veterans` table.

#### Values entered + source trace
None.

#### Source data with no UI home
None on this screen. `Import an existing career` is the door a mid-career snapshot might plausibly use; the brief specifies the setup flow, so `New Career` was taken.

#### Friction / errors / dead ends
- Three controls share the destination `/career/setup/scenario`: `New Career` (sidebar), `New career` (body), `New Career` (mobile nav). First in DOM order used.
- Nothing on screen reveals that the tree moved since the previous pass; only the commit line in this audit's header does.

#### Outcome / next screen
`New Career` → S02.

### S02 Which scenario is this run?

#### Route and trigger
`/career/setup/scenario`, from S01. `Step 1 of 6`.

#### What the screen asks for
`The scenario decides which panels the cockpit composes, which caps apply, and which systems exist. It cannot be changed after the first turn.` Four radio cards, values `ura_finale`, `unity_cup`, `trackblazer`, `our_grand_concert`, each with a release chip (`Global · 2025-11-06` for Unity Cup) and cap chips (`Caps 1300 ×4 · Wit 1800`). Forward `Continue to trainee`, disabled until a choice; `Cancel`; note `One draft per session. Opening this wizard in another tab replaces this one (last write wins); an expired session restarts the wizard.`

#### Values entered + source trace
`Unity Cup` → the `Which scenario is this career in?` radio group → §1.1 line 36.

#### Source data with no UI home
None.

#### Friction / errors / dead ends
- The rail shows step states `Saved`, `Started unfinished`, `Not started`, `Current` with no legend; meaning must be inferred.

#### Outcome / next screen
`Continue to trainee` → S03.

### S03 Choose a trainee

#### Route and trigger
`/career/setup/trainee`, from S02. Live filtering produced `?direction=asc&search=Rice%20Shower&sortBy=name`.

#### What the screen asks for
`Search` (placeholder `Name or unique skill`); `Surface`, `Distance`, `Running style` selects (`Any`, `S`..`G`); `Sort` (`Name`, `Turf`, `Dirt`, `Sprint`, `Mile`, `Medium`, `Long`, `Front runner`, `Pace chaser`, `Late surger`, `End closer`); `Clear`; `Continue to build target`. Rows print rarity (`★★★ SSR`), `Released (Global)`, form count and three aptitude families, e.g. `Surface Turf A · Dirt G`, `Distance Sprint E · Mile C · Medium A · Long A`, `Style Front runner B · Pace chaser A · Late surger C · End closer G`.

#### Values entered + source trace
`Rice Shower` into `Search` → §1.1 line 37. Result `1 trainee · page 1 of 1 · sorted by Name`.

#### Source data with no UI home
None. Catalog letters differ from the frame reading for Mile (C vs B) and Front/Late (B/C vs A/B); the source's dated erratum (lines 78-88) records that disagreement, and the row prints catalog values.

#### Friction / errors / dead ends
- No `Filter` button: search applies live (round 4 required a click).
- `Select` cleared the query and returned `68 trainees · page 1 of 12`, the same state reset seen in round 4.

#### Outcome / next screen
`Continue to build target` (`href="/career/setup/trainee/30"`) → S04.

### S04 Trainee read screen (costume form)

#### Route and trigger
`/career/setup/trainee/30`, from S03. The `30` is a catalog id printed nowhere in UI text.

#### What the screen asks for
`This is a read screen: nothing here is edited in the wizard.` Sections: `Aptitudes` (with the note that aptitude letters and stat grades are different scales); `Growth row` (`SPEED 0% STAMINA 10% POWER 0% GUTS 20% WIT 0%`); `Unique skill` (`Blue Rose Closer ✦ Unique`, `Every Rose Has Its Fangs ✦ Unique`, both `from the source's class code`); `Base stats` (`Speed 71 Stamina 117 Power 70 Guts 102 Wit 90`); `Costume forms` table (`TITLE | RARITY | SELECTED`, `[Rosy Dreams] Debut form · Jun 26, 2025 ★★★ Selected`). Controls: `Choose a different trainee`, `Continue to build target`.

#### Values entered + source trace
`select[name='selected-form']` re-set to `[Rosy Dreams]` → §1.1 line 37. The growth row equals the erratum's `stat_bonus: [0, 10, 0, 20, 0]` reading (lines 78-88).

#### Source data with no UI home
- **No UI representation found:** Potential Lvl 2. The trainee card claimed `The form you pick sets this run's rarity and potential`, yet no potential value is printed here and the word does not appear (U2-03).

#### Friction / errors / dead ends
- `Continue to build target` is a link on S03 and a button here; the same string names both the action and the next step (E2-05).

#### Outcome / next screen
`Continue to build target` → S05.

### S05 What are you building toward?

#### Route and trigger
`/career/setup/target`, from S04. `Step 3 of 6`.

#### What the screen asks for
Copy: `This is the step the current build skips.` and `A run created with no target entered commits with five empty goals, and the cockpit then has nothing to measure a turn against.` Controls: `Purpose` (`Not chosen yet`, `Story Clear`, `Champions Meeting`, `Parent Farming`, `Skill Farming`); `Surface` (`Not chosen yet`, `Turf`, `Dirt`); `Style` (`Not chosen yet`, `Front Runner`, `Pace Chaser`, `Late Surger`, `End Closer`); `Distance` as four radios with metre bands (`Sprint 1400 m and under · Speed-first`, `Mile 1401-1800 m ...`, `Medium 1801-2400 m ...`, `Long 2401 m and up · all five stats high`); per-stat `Target` (`min 0 max 1300`, Wit `max 1800`) and `... ceiling` (`min 1 max 2000`; Wit shows `No enterable ceiling for this stat`); `TOTAL AGAINST THE PUBLISHED SAMPLE` with `Sum of the five scenario caps 7000`; a stamina-floor note; `Use saved defaults` with `No saved stat defaults exist, so there is nothing to apply.`; `Back`; `Continue to legacy`.

#### Values entered + source trace

| Value | Control | Source |
|---|---|---|
| `1325` `1322` `1368` `1308` | the four `... ceiling` inputs | §1.2 lines 44-49 |
| `Story Clear` | `Purpose` | **Not present in source.** UI-required; open items are scenario goals (#2) |
| `Turf` | `Surface` | Derived: open objectives are turf races (§1.6 lines 203-205); not stated as a target (#3) |
| `Long` | `Distance` | Derived: Arima Kinen is 2500 m (§3 line 728) (#4) |
| `Pace Chaser` | `Style` | Derived from §1.2 line 76 (`Pace A`) and §1.3 (#5) |
| `607` `577` `549` `736` `397` | the five `Target` inputs | **Not present in source as targets**; these are the current stat line (§1.2), forced in by validation (F2-02) (#6) |

#### Source data with no UI home
- **No UI representation found:** skill priorities. Round 4 accepted `Pace Chaser Straightaways ◎` and `Kyoto Racecourse ○` (§2.3 lines 451-455) here; this step has no skill list at all.
- **No UI representation found:** the raised Wit cap as an override (Wit shows `No enterable ceiling for this stat`); harmless because the source's 1800 equals the published figure.

#### Friction / errors / dead ends
- Four submit attempts. First attempt returned `The surface field is required.`, `The style field is required.`, `The distance field is required.` and five copies of `Enter a target for every stat, or leave the whole target unset.` (F2-02, blocking). After the targets were filled only the distance error remained; its radios were in the DOM all along, far below the fold, unlocated by the message (F2-03).
- The step calls itself skipped by the build while requiring every field (F2-01).
- The pick does not react until save elsewhere in this wizard; here the selects update.

#### Outcome / next screen
`Continue to legacy` → S06.

### S06 Inherit from two parents

#### Route and trigger
`/career/setup/legacy`, from S05. `Step 4 of 6`.

#### What the screen asks for
`A run starts with the sparks its parents carry. Both slots are optional; a run with no parents starts from the card's own base.` Note: `Record only. This screen stores and compares what you enter. It does not compute inheritance.`
- `Parent A` / `Parent B`: `Choose a veteran` select (`Not chosen`, `Vodka [Wild Top Gear] · 14`, `· 13`, `Daiwa Scarlet [Peak Blue] · 12`, `Haru Urara [Bestest Prize ♪] · 11`, `Mayano Top Gun [Scramble☆Zone] · 10`, `Maruzensky [Formula R] · 9`, `· 8`), `Not in my library - enter a name`, `Leave empty`, and after saving an `Inspect` link.
- `Projected start` table `STAT | SCENARIO CAP | FROM PARENT A | FROM PARENT B | PROJECTED`, with `the projected sum is never computed, because an inheritance outcome is not this tool's to derive.`
- `Pair Affinity`: `Not recorded`, `△`, `○`, `◎`, `The grade you read for the pair, not a score. This tool does not price it.`
- `Save Legacy`; four grandparent blocks `A1`, `A2`, `B1`, `B2`, each with name, `Grandparent rank (letters)`, `Grandparent rank (stars)`, and a `Sparks for Grandparent ...` line.
- Reference table `What inheritance actually does`: `Blue Spark +5, +12 or +21 to the stat it names, for one, two or three stars`, `Pink Spark ...`.

#### Values entered + source trace

| Value | Control | Source |
|---|---|---|
| `Vodka [Wild Top Gear] · 14` | `Choose a veteran` Parent A | §1.5 line 167; index arbitrary (#7) |
| `Maruzensky [Formula R] · 9` | `Choose a veteran` Parent B | §1.5 line 170; index arbitrary (#8) |
| `Daiwa Scarlet [Peak Blue]` / `B+` | Grandparent A1 name / letters | §1.5 lines 168, 178 |
| `Seiun Sky [Reeling in the Big One]` / `A` | Grandparent A2 | §1.5 lines 169, 179 |
| `Daiwa Scarlet [Peak Blue]` / `UG` | Grandparent B1 | §1.5 lines 171, 181 |
| `Taiki Shuttle [Wild Frontier]` / `UG` | Grandparent B2 | §1.5 lines 172, 182 |
| `Not recorded` kept | `Affinity` | source states no pair grade |

After the first `Save Legacy` the headings became `Parent A Chosen Vodka ウオッカ URA Finale · saved 2026-10-15` and `Parent B Chosen Maruzensky マルゼンスキー ...`, each with `Inspect`.

#### Source data with no UI home
- **No UI representation found:** the parents' own ranks `B+` and `UG` (U2-01).
- **No UI representation found:** Maruzensky `flagged Guest` (§1.5 line 170). Round 4 had `Rented for the run`; this step has no ownership control at all (U2-01).
- **No UI representation found:** the six blue sparks and the pink/green/white inventory. No spark control exists on this step (F2-06, U2-07).
- **No UI representation found:** grandparent star counts were left blank because §1.5 gives letters only.

#### Friction / errors / dead ends
- Selecting both veterans left the UI reading `Not chosen` and the projection `-` until `Save Legacy` (F2-04).
- The banked parents carry their own save scenario (`URA Finale · saved 2026-10-15`) while the inheritance tree labels them `Unity Cup` (S17).
- After the first save the screen warns `already holds a Legacy. Saving below replaces it whole; a Spark you remove here is gone, not hidden.` Re-saving kept all four grandparents.
- Parents must exist as banked Veterans; the free-text escape is the only alternative.

#### Outcome / next screen
`Continue to deck` → S07.

### S07 Build the six-card deck

#### Route and trigger
`/career/setup/deck`, from S06; picker states `?deck_slot=1` .. `?deck_slot=6` with `&q=`.

#### What the screen asks for
`Six slots.` ... `Position six is the one the client labels Friends. The role belongs to the position and not to the card.` Per slot: `Owned` / `Rented` buttons, `Choose a card in Slot N`. A shared picker opened by `Add a card`: `Type` select, `q` search, `Filter`, `Close`, rows `Name [Epithet] | type | Equip`. Header counter `Deck 0 of 6 slots filled` → `6 of 6`. `Continue to preflight`.

#### Values entered + source trace

| Slot | Card | Ownership | Source |
|---|---|---|---|
| 1 | `Tokai Teio [Dream Big!]` | Owned | §1.4 line 108 |
| 2 | `Nishino Flower [Even the Littlest Bud]` | Owned | §1.4 line 109 |
| 3 | `Biko Pegasus [Double Carrot Punch!]` | Owned | §1.4 line 110 |
| 4 | `Matikanetannhauser [Just Keep Going]` | Owned | §1.4 line 112 |
| 5 | `Haru Urara [Urara's Day Off!]` | Owned | §1.4 line 113 |
| 6 · Friends | `Super Creek [Piece of Mind]` | Rented | §1.4 lines 111, 114-118 (#14) |

#### Source data with no UI home
- **No UI representation found:** card levels and limit breaks (`35/35`, `30/30`, `50/50`, §1.4) and the client's level-reached effect rows; the picker shows card, type and rarity only.
- **No UI representation found:** the friend card's owner account (§1.4 lines 114-118); `Rented` is the whole encoding.

#### Friction / errors / dead ends
- The picker's search input is invisible until `Add a card` is clicked; a first fill attempt failed as "element is not visible".
- `Filter` keeps the panel open and the full printed name matches exactly, so round 4's two picker defects do not reproduce.
- `Equip` closes the panel and fills the lowest empty slot; the button names no slot, so position is the only targeting (E2-03).

#### Outcome / next screen
`Continue to preflight` → S08.
### S08 Before the first turn (Preflight)

#### Route and trigger
`/career/setup/preflight`, from S07. `Step 6 of 6`.

#### What the screen asks for
`This run` table: `TRAINEE Rice Shower`, `FORM as chosen above`, `SCENARIO Unity Cup`, `DISTANCE Long`, `PARENTS Vodka · Maruzensky`, `DECK 6 of 6`, `TARGET TOTAL 2,866`, `CAREER LENGTH 72 turns` with the note that the post-December finale block sits outside the 24-turn grid. `Checks`: `All 5 entered steps are recorded.` and a table `Scenario Pass Unity Cup | Trainee Pass Rice Showerライスシャワー | Your target Pass Story Clear · Turf Long · Pace Chaser | Legacy Pass No Affinity grade read · 2 of two parents recorded | Support deck Pass 6 of six positions carry a card`. Then `What this scenario asks`, `What is not built yet`, `Ruleset version: N/A`, a `Commit` paragraph, the checkbox `I understand this creates an Active run that cannot be un-created; retirement is the only exit.`, and `Create the run`.

#### Values entered + source trace
Only the acknowledgement. `TARGET TOTAL 2,866` is the tool's sum of the six §1.2-line values plus Wit, i.e. the figures forced in under F2-02.

#### Source data with no UI home
The review shows no ceilings, no deck list, no spark inventory, so a Trainer cannot proofread the transcription here.

#### Friction / errors / dead ends
- `Create the run` begins disabled and the only hint is the sentence `Acknowledge the above to enable "Create the run".`; the checkbox itself carries no name (E2-04).
- The table names the step `Support deck` while the rail calls it `Deck` (E-level naming drift).
- `Your target Pass` certifies values the source never supplies, so a green check here vouches for improvised data.

#### Outcome / next screen
`Create the run` → S09.

### S09 Cockpit (run 32, no turn yet)

#### Route and trigger
`/training-runs/32/cockpit`, from S08. Nav band: `Rice Shower · Unity Cup #32 · Cockpit`.

#### What the screen asks for
Read-only. `No turn recorded yet` with `Record the first turn through Training`; `Stats and state` bars drawn `N/A / 1,325 cap` ... `N/A / 1,800 cap`; `ENERGY Energy N/A`, `MOOD N/A, not recorded`, `FANS N/A`, `SKILL POINTS N/A`; `CapsSpeed 1,325Stamina 1,322Power 1,368Guts 1,308Wit 1,800· Blue sparks No blue spark is recorded in this run's Legacy.`; a `Scenario` panel (`Finale Unity Cup: not yet reached`, `Team N/A`, `League N/A`, `Bursts N/A`, `Team rank not recorded`); `Advisor` (`Nothing can be prioritised until this turn's Energy is recorded.`); `OBLIGATIONS`; a `Where to go` list (`Training decision`, `Race decision`, `Skills planner`, `Run deck`, `Timeline`, `Career result`, `Run settings`, `Scenario detail`, `Team setup`, `Delete this run`).

#### Values entered + source trace
None. Persistence verified: the caps printed are exactly S05's ceilings.

#### Source data with no UI home
- **No UI representation found:** Potential Lvl 2 (U2-03).
- **No UI representation found:** Team Rank S (U2-02).

#### Friction / errors / dead ends
- The caps panel cites the source document by section number: `The source records that they do not reconcile (§1.2): the six blue sparks account for the Guts extra and nothing else`, pointing at prose a Trainer cannot open from the app.
- Obligations list `Junior Make Debut` and three `URA Finals` races for a Unity Cup run (F2-12).

#### Outcome / next screen
`Record the first turn through Training` → S10.

### S10 Training decision (first visit)

#### Route and trigger
`/training-runs/32/training`, from S09.

#### What the screen asks for
A `pick` radio group of eight captions: `1 Speed Feeds Power · Lv not recorded base · failure rate not published ... Energy not recorded`, `2 Stamina Feeds Guts`, `3 Power Feeds Stamina`, `4 Guts Feeds Speed and Power`, `5 Wit no Energy Feeds Speed`, `Rest Refills Energy. A poor rest refills less and can leave a stayed-up-late penalty.`, `Recreation Lifts Mood, and the outing may return a little Energy as well.`, `Race Optional race · see the race desk`. Then `ENERGY AFTER` (`Exact` / `Band` / `Not recorded`), `MOOD AFTER` (`Unchanged`, `GREAT`, `GOOD`, `NORMAL`, `BAD`, `AWFUL`), `FANS`, `SKILL POINTS`, and a collapsed details group `Client totals this turn (required to record)` holding `Turn`, the five stats, five `facility_*` numbers, `outcome` (`Choose` / `Success` / `Failure`) and the energy selects.

#### Values entered + source trace
None on this visit (the `Race` pick moved the walk on).

#### Source data with no UI home
No field for "this turn is a race day being decided"; year and month are derived from `Turn`, never entered.

#### Friction / errors / dead ends
- `Race` is a radio that navigates instead of selecting (E2-10).
- The record is split: required numbers hide inside a collapsed summary labelled `required to record`, while mood, fans and SP sit outside it (E2-01). Two fill attempts failed with "element is not visible" until the summary was opened.
- `outcome` is not marked required and an empty value submits (contrast round 4's forced `Outcome *`).

#### Outcome / next screen
`Race` pick → S11.

### S11 Race decision (via the Race pick)

#### Route and trigger
`/training-runs/32/races`, from S10.

#### What the screen asks for
`Enter a race, or skip the turn`; `Available this turn` (`RACE | GRADE | SURFACE · DISTANCE | FANS NEEDED | SKILL PT`); `Races in a row so far not recorded`; `No race chosen`; buttons `Undo turn 1`, `Train instead`, `Enter a race`, `Race not on the calendar`.

#### Values entered + source trace
None.

#### Source data with no UI home
- **No UI representation found:** this turn's race cards (Kyoto Daishoten; Mile Championship Nambu Hai) (§1.6 lines 210-216) (U2-05).

#### Friction / errors / dead ends
- `Available this turn` states `No turn has been logged yet, so there is no turn to decide about.`, yet the same screen offers `Undo turn 1` for a turn that does not exist (F2-11).

#### Outcome / next screen
`Train instead` → S12.

### S12 Training decision (filled and submitted)

#### Route and trigger
`/training-runs/32/training`, from S11. Re-entry; the pass resumed here.

#### What the screen asks for
As S10.

#### Values entered + source trace

| Value | Control | Source |
|---|---|---|
| `Guts` | `pick` option 4 | **Not present in source as this turn's action.** §5 step 3 names Guts the highest-value facility; the source's turn is a race day (#11) |
| `67` | `Turn` | Derived: 72 − 5 (§1.1 line 38) (#10) |
| `607` `577` `549` `736` `397` | the five stat inputs | §1.2 lines 44-49 |
| `173` | `SKILL POINTS` | §1.2 line 51 |
| `209245` | `FANS` | §1.1 line 38 |
| `Band` = `Low` | `ENERGY AFTER` | §1.7 lines 241-247 (#13) |
| `GREAT` | `MOOD AFTER` | §1.7 line 236 |
| `4` `4` `4` `5` `4` | `facility_speed` .. `facility_wit` | §1.7 line 251 |
| empty | `outcome` | source states no outcome for this turn (#12) |

#### Source data with no UI home
- **No UI representation found:** failure rate `39%` and the preview `+18 Speed / +8 Power / +6 Skill Points` and the banner `Floor Cleaning` (§1.7 lines 249-251). Round 4 offered `Preview readings` and a failure-rate field; this build has neither (U2-06).

#### Friction / errors / dead ends
- Submit button read `Record turn 1` although `Turn` was 67 (E2-02).
- The details group had to be opened before any required number could be typed.

#### Outcome / next screen
Submit → S13.

### S13 Cockpit (turn 67 read-back)

#### Route and trigger
`/training-runs/32/cockpit`, from S12.

#### What the screen asks for
Read-only plus the `Where to go` list.

#### Values entered + source trace
None; this is the verification screen.

#### Source data with no UI home
- **No UI representation found:** the fan class and gap. Round 4 printed `209,245 · Star · 30,755 to next` (§1.1 line 39); this build prints `209,245` only, and the figure `30,755` appears nowhere (F2-10).
- Team Rank and Potential as at S09.

#### Friction / errors / dead ends
Verified correctly: `Senior Year · Early October · Turn 67 of 72 · [Rosy Dreams]. Energy Low band, Mood GREAT ↑ +20%`; stats `607 / 577 / 549 / 736 / 397`; `FANS 209,245`; `SKILL POINTS 173`; `Unity Cup is 5 turns away.`; `Recorded action Guts Recorded 11/3/2026`; `Rest recovers +30 Energy. Source: GameWith, 2026-09-25, read 2026-11-02.` The `Recorded action Guts` line is the walk's improvised pick, now presented as run fact. Obligations still show `Junior Make Debut ... current` (F2-12).

#### Outcome / next screen
`Team setup` → S14.

### S14 Team setup

#### Route and trigger
`/training-runs/32/team`, from S13.

#### What the screen asks for
`team_name`, `team_motto`, `team_league_placement`, `team_preseason_wins`, `unity_trainings_count`, `spirit_bursts_count`, `extreme_bursts_count`, five `team_stat_grades[Stat]` selects (`Not recorded`, `G`, `F`, `E`, `D`, `C`, `B`, `A`, `S`), and `Save team state`. Page note: `Per-stat grades are G to S. S+ is the overall team rank, recorded elsewhere.`

#### Values entered + source trace

| Value | Control | Source |
|---|---|---|
| `Blue Bloom` | `team_name` | §1.6 line 198 |
| `Dreaming Big` | `team_motto` | §1.6 line 198 |
| `8` | `team_league_placement` | §1.6 line 198 |
| `4` | `team_preseason_wins` | §1.6 lines 199-201 |
| `55` | `unity_trainings_count` | §1.8 line 264 |
| `6` | `spirit_bursts_count` | §1.8 line 265 |
| `5` | `extreme_bursts_count` | §1.8 line 266 |
| `A` `A` `A` `S` `A` | the five grade selects | §1.8 line 263 |

#### Source data with no UI home
- **No UI representation found:** Team Rank `S` (U2-02).
- **No UI representation found:** Current Spirit (U2-02 panel shows `Current Spirit N/A`).
- **No UI representation found:** the 20/20 teammate roster and the two clipped members (§1.9 lines 300-351) (U2-09).

#### Friction / errors / dead ends
- `Save team state` leaves no on-page confirmation (E2-06).
- The grade selects list letters out of rank order, `G F E D C B A S` (F2-08).

#### Outcome / next screen
No control reaches Scenario detail from here, so the URL was typed (deviation 2) → S15.

### S15 Scenario detail

#### Route and trigger
`/training-runs/32/scenario`, typed. Title `Scenario detail: Rice Shower`.

#### What the screen asks for
Read-only boards: `Team Race` with `Aim for at least 3 circlesas a margin, not a win condition. ... a loss can be retried with an Alarm Clock.` and `No team races recorded. Circles are entered on the race form as the number the client showed before you committed.`; `Team` (`Name Blue Bloom`, `Motto Dreaming Big`, `League placement 8`, `Preseason rounds won 4`); `Spirit · Current Spirit N/A`; `Team Rank · Rank not recorded: the league letter is read off the client, and this run has none entered yet.` with ladder `G lv 1 F lv 1 D lv 2 E lv 2 B lv 3 C lv 3 A lv 4 S lv 5` and the note that `S+ ... grants a second hint rather than a higher facility`; `Unity Cup progress · Unity Trainings 55 · Spirit Bursts 6 / 5 Extreme`; `Team stat grades Speed A Stamina A Power A Guts S Wit A`; `Burst count bands` table marking `10-12 (this run)` → `Gold hint Lv1, +30 matching stat, +30 SP`.

#### Values entered + source trace
None. Every S14 value read back, including all five grades and the burst tally.

#### Source data with no UI home
Team Rank and Current Spirit, as at S14.

#### Friction / errors / dead ends
- This screen represents the source's own §2.4 arithmetic best: it places the run inside the 10-12 gold band automatically.
- `3 circlesas a margin` is missing a space (E2-07).
- The ladder letters are unordered (F2-08).

#### Outcome / next screen
`Races` → S16.

### S16 Race decision (populated run, turn 68)

#### Route and trigger
`/training-runs/32/races`, from S15.

#### What the screen asks for
`Available this turn` rows include `Brazil Cup 2,100 m OP Dirt · Medium A 350 +30` and `Cassiopeia Stakes 1,800 m OP Turf · Mile C 350 +30`, each carrying `Maiden lock not cleared. The maiden lock is not cleared; this race opens only once a Debut or Maiden is won.`; `Races in a row so far not recorded`; buttons `Enter a race`, `Race not on the calendar`.

#### Values entered + source trace
None; no `Enter a race` was used, since the source's race is unrun and these races are not it.

#### Source data with no UI home
- **No UI representation found:** Kyoto Daishoten and Mile Championship Nambu Hai with grade, course, going and fan payouts (§1.6 lines 210-232) (U2-05).
- **No UI representation found:** the outstanding Arima Kinen goal as an enterable race.

#### Friction / errors / dead ends
- At turn 68 of a Senior career the desk still offers only early-career races, every one refused by a maiden lock that a backfilled run cannot clear (F2-07, blocking).
- The `SURFACE · DISTANCE` column shows the trainee's aptitude letter (`Mile C`) where a distance is expected (E2-08).

#### Outcome / next screen
`Inheritance` → S17.

### S17 Inheritance event

#### Route and trigger
`/training-runs/32/inheritance`, from S16.

#### What the screen asks for
`The inheritance moment happens at career start, Classic and Senior. Pick the spark you want carried from each parent; the run's caps move by the result.` Tree rows `GRANDPARENT Daiwa Scarlet [Peak Blue] recorded ancestor`, `GRANDPARENT Seiun Sky [Reeling in the Big One] recorded ancestor`, `PARENT A Vodka Unity Cup`, `TRAINEE Rice Shower Unity Cup`, `GRANDPARENT Daiwa Scarlet [Peak Blue] recorded ancestor`, `GRANDPARENT Taiki Shuttle [Wild Frontier] recorded ancestor`, `PARENT B Maruzensky Unity Cup`; `COMPATIBILITY Not recorded raises trigger chance and payout`; per parent `This parent carries no recorded spark.`; inputs `turn`, `source_name`, `choice_label`, `origin_note`; buttons `Skip this moment`, `Record inheritance`.

#### Values entered + source trace
None. Recording a moment would assert one of the three inheritance events at this turn, which the source does not state.

#### Source data with no UI home
- **No UI representation found:** the six blue sparks and their star grades (§1.5) (U2-07). The screen invites a spark choice but offers only note fields (F2-06).
- **No UI representation found:** parents' ranks and the Guest flag (U2-01).

#### Friction / errors / dead ends
- Both parents are labelled `Unity Cup` here but `URA Finale · saved 2026-10-15` on S06: two scenario tags for one ancestor.

#### Outcome / next screen
`Skills` → S18.

### S18 Skills planner

#### Route and trigger
`/training-runs/32/skills`, from S17.

#### What the screen asks for
`Spend 173 Skill Points`; `A hint lowers a skill's cost; the ladder is 10% / 20% / 30% / 35% / 40% off. A hint you have not earned still buys the skill at full price.`; budget `SKILL POINTS 173 | PLANNED SPEND 0 | REMAINING 173`; `Learn planned skills`; filters `SEARCH`, `TYPE`, `HINTS`, `Clear`; a table `SKILL | FAMILY | BASE | DISCOUNT | COST`; a collapsed `Build target, recorded skills and the add form` holding `#add-skill-picker` and `Add skill`; per-row `skill-<id>-status`, `skill-<id>-turn`, `Save acquisition status`; and `Why the hint ladder is not linear` with `130 → 104 / 91 / 84`, `160 → 144 / 128 / 112 / 96`, and `130 × 0.65 = 84.5, floored to 84`.

#### Values entered + source trace
`Blue Rose Closer` added, `Acquired`, saved → §1.3 line 93. The row read `Blue Rose Closer ... Unique N/A N/A N/A Acquired`; `REMAINING` stayed `173`.

#### Source data with no UI home
- **No UI representation found:** skill level `Lvl 3` and per-skill hint levels (§1.3, §2.1) (U2-04).
- **No UI representation found:** the recommended spend list (§2.3). With no skill priorities on the target step, `PLANNED SPEND` is the only candidate, and nothing says so.

#### Friction / errors / dead ends
- The row's description prints the engine predicate `is_last_straight==1 (precondition: phase>=2&order<=4&change_order_onetime<0)` (F2-09).
- The add form is inside a collapsed details; the first select attempt failed as "not visible".
- For a Unique skill `BASE`, `DISCOUNT` and `COST` are all `N/A`, so the budget bar cannot show its cost.
- `Acquired` changes neither `PLANNED SPEND` nor `REMAINING`, with no line explaining the difference (E2-09).

#### Outcome / next screen
`Careers` → S19.

### S19 Careers

#### Route and trigger
`/training-runs`, from S18.

#### What the screen asks for
`Search trainee`, `Scenario`, `Status`, `Sort`, `Apply filters`, `Clear`, `New career`, `Import a run`; table `TRAINEE | SCENARIO | TURN | POSITION | STATUS | ACTIONS`.

#### Values entered + source trace
None.

#### Source data with no UI home
None new.

#### Friction / errors / dead ends
- The run reads `Rice Shower ライスシャワー · [Rosy Dreams] · #32 · ★3 | Unity Cup | 67 / 72 | Senior Year · Early October | Active`. The id, the turn and the position all agree with the cockpit; round 4's `Turn ~11 / 24` contradiction does not reproduce.
- 11 careers listed; the round-4 run (id 26) is absent, confirming the tree moved between passes.

#### Outcome / next screen
Walk stopped.
## 7.4 Improvisation log

| # | Screen | Field / control | Value chosen | Why | Alternatives rejected |
|---|---|---|---|---|---|
| 1 | S02 | scenario radio | `Unity Cup` | sourced (§1.1 line 36) | none |
| 2 | S05 | `Purpose` | `Story Clear` | source states no purpose; open items are scenario goals | `Champions Meeting`, `Parent Farming`, `Skill Farming`, blank (rejected by validation) |
| 3 | S05 | `Surface` | `Turf` | all open objectives are turf races; derived, not stated | `Dirt`, blank (rejected) |
| 4 | S05 | `Distance` | `Long` | Arima Kinen, 2500 m (§3 line 728) | `Medium`, blank (rejected) |
| 5 | S05 | `Style` | `Pace Chaser` | §1.2 line 76 `Pace A` plus the pace-oriented skill list | `Front Runner`, `Late Surger`, `End Closer`, blank (rejected) |
| 6 | S05 | five `Target` numbers | `607 577 549 736 397` | validation refused the all-empty state the error text says is allowed; the current stat line was the least invented fill | leaving unset; any forward-looking figure (the source gives none) |
| 7 | S06 | Parent A instance | `Vodka [Wild Top Gear] · 14` | source names the card, not the banked instance | `· 13` |
| 8 | S06 | Parent B instance | `Maruzensky [Formula R] · 9` | same | `· 8` |
| 9 | S06 | grandparent star counts | left blank | §1.5 gives letters only | inventing stars |
| 10 | S12 | `Turn` | `67` | 72 turns with 5 left (§1.1 line 38); the tool then derived the source's own date | `1`, `68` |
| 11 | S12 | `pick` | `Guts` | the totals form only appears once a pick is made; §5 step 3 names Guts the highest-value facility. The source's turn is a race | `Race` (navigates away and refuses), `Rest`, `Recreation`, other facilities |
| 12 | S12 | `outcome` | left empty | turn is unresolved in the source; the build accepts empty | `Success` (what round 4 was forced to type), `Failure` |
| 13 | S12 | `ENERGY AFTER` | `Band` = `Low` | §1.7 prints a low bar and a warning, no number | `Exact`, `Mid` (the default on choosing Band), `Not recorded` |
| 14 | S07 | slot order | friend card in `Slot 6 · Friends` | §1.4 states the sixth slot is the friend card | keeping the §1.4 table order |
| 15 | S17 | inheritance moment | not recorded | the source states no inheritance event at this turn | fabricating `choice_label` |
| 16 | S18 | learned skills | only `Blue Rose Closer` | one representative; no level field exists, so the other 10 would add no source information | entering all 11 |

## 7.5 Findings register

| ID | Type | Screens | Short description | Blocking? |
|---|---|---|---|---|
| F2-01 | Functional | S05 | Copy says `This is the step the current build skips.` while the step requires purpose, surface, distance, style and every target. | No |
| F2-02 | Functional | S05 | Five copies of `Enter a target for every stat, or leave the whole target unset.` fire while all five targets are empty; the named legal state is refused. | Yes |
| F2-03 | Functional | S05 | `The distance field is required.` points at no located control; the radios sit far below the fold. | No |
| F2-04 | Functional | S06 | Veteran picks do not register until `Save Legacy`; headings stay `Not chosen` and `Projected start` stays `-`. | No |
| F2-05 | Functional | S06, S17 | No per-parent rank field and no rented/guest field: `Vodka B+`, `Maruzensky UG` and the Guest flag cannot be recorded. | No |
| F2-06 | Functional | S06, S17 | No spark entry exists anywhere in the flow; the inheritance screen invites a spark choice but offers note fields only. | No |
| F2-07 | Functional | S11, S16 | At turns 67/68 the race desk offers only early-career races, each refused with `Maiden lock not cleared`; no race can be entered and the source's races are absent. | Yes |
| F2-08 | Functional | S14, S15 | Rank letters emitted out of order (`G F E D C B A S`; ladder `G lv1 F lv1 D lv2 E lv2 B lv3 C lv3 A lv4 S lv5`), and Team Rank has no input despite `recorded elsewhere`. | No |
| F2-09 | Functional | S18 | Skills row prints the engine predicate `is_last_straight==1 (precondition: phase>=2&order<=4&change_order_onetime<0)`. | No |
| F2-10 | Functional | S13 | Fans readout dropped the class and gap; `30,755 to next` (§1.1 line 39) appears nowhere. | No |
| F2-11 | Functional | S11 | `Undo turn 1` offered on a run with no logged turn. | No |
| F2-12 | Functional | S09, S13, S16 | Unity Cup career lists `URA Finals Qualifier/Semifinal/Final (Aoharu)`; `Junior Make Debut` reads `current` at turn 67; cleared goals cannot be marked. | No |
| F2-13 | Functional | S14 | No on-page control reaches Scenario detail from Team setup. | No |
| E2-01 | Experiential | S10, S12, S18 | One record split across collapsed details while sibling fields sit outside; fills failed as "not visible" until opened. | — |
| E2-02 | Experiential | S12 | Submit label stays `Record turn 1` after `Turn` is 67. | — |
| E2-03 | Experiential | S07 | `Equip` closes the picker and fills the lowest empty slot; no slot named on the button. | — |
| E2-04 | Experiential | S08 | `Create the run` disabled with only a prose hint; unnamed acknowledgement checkbox. | — |
| E2-05 | Experiential | S03, S04 | `Continue to build target` is a link then a button, and names both action and step. | — |
| E2-06 | Experiential | S14 | `Save team state` gives no confirmation. | — |
| E2-07 | Experiential | S15 | `3 circlesas a margin` renders without a space. | — |
| E2-08 | Experiential | S16 | `SURFACE · DISTANCE` column carries aptitude letters. | — |
| E2-09 | Experiential | S18 | `Acquired` leaves `PLANNED SPEND` and `REMAINING` unchanged with no explanation. | — |
| E2-10 | Experiential | S10 | The `Race` radio navigates instead of selecting. | — |
| E2-11 | Experiential | S01 | Three same-destination `New Career` controls. | — |
| U2-01 | Unrepresented data | S06, S17 | Parent ranks `B+`/`UG` and Maruzensky's Guest/rented status. | — |
| U2-02 | Unrepresented data | S09, S13, S14, S15 | Team Rank `S`; Current Spirit. | — |
| U2-03 | Unrepresented data | S04, S09, S13 | Potential `Lvl 2`. | — |
| U2-04 | Unrepresented data | S18 | Skill levels and per-skill hint levels. | — |
| U2-05 | Unrepresented data | S11, S16 | This turn's race cards, `Recommended` flag, Soft going, fan payouts. | — |
| U2-06 | Unrepresented data | S10, S12 | Failure rate `39%`, preview `+18/+8/+6`, banner `Floor Cleaning`. | — |
| U2-07 | Unrepresented data | S06, S17 | The six blue sparks and the pink/green/white inventory. | — |
| U2-08 | Unrepresented data | S13 | Cleared-goal history (3 of 8 cleared). | — |
| U2-09 | Unrepresented data | S14 | The 20/20 teammate roster including the two clipped members. | — |

## 7.7 Definition-of-done checklist

- [x] Every screen reached has its own section, in visit order (S01-S19).
- [x] Every entered value has a source trace or is marked not present in source and listed in §7.4.
- [x] Every source datum without a UI home is named on the screen where it surfaced.
- [x] Lifecycle preserved: Active, mid-career, five turns remaining; never Completed, never Veteran, never Legacy.
- [x] Functional and experiential findings kept separate.
- [x] Register matches §8 (13 F, 11 E, 9 U).
- [x] Stopping point and reason stated (§7.1, §8.5).
- [ ] Evidence screenshots not retained; the record is textual (see §7.2).
## 8. Closing assessment

### 8.1 What worked

The rebuilt flow carries this run much further than the round-4 build. Ceilings entered at S05 became the cockpit's stat caps (`1,325 / 1,322 / 1,368 / 1,308 / 1,800`, read at S09). Trainee, costume form, deck and parents all survived into the run: the nav band prints `Rice Shower · Unity Cup #32`, `Slot 6 · Friends` holds Super Creek as `Rented`, the deck read `6 of 6`, and the inheritance tree at S17 shows all four grandparent names as `recorded ancestor`. One turn record produced the source's exact moment: `Senior Year · Early October · Turn 67 of 72` and `Unity Cup is 5 turns away.`, with `Energy Low band`, `Mood GREAT ↑ +20%`, `FANS 209,245`, `SKILL POINTS 173` and stats 607 / 577 / 549 / 736 / 397. Team state persisted and the scenario board derived the source's own burst band for it: `Unity Trainings 55 · Spirit Bursts 6 / 5 Extreme` and `10-12 (this run)` → `Gold hint Lv1, +30 matching stat, +30 SP`, alongside the grades A/A/A/S/A. The skills planner now models the §2.1 hint ladder, including the `84.5 floored to 84` rounding case. The run stayed Active and mid-career, and the Careers list agrees: `67 / 72 · Senior Year · Early October`.

### 8.2 Functional findings

**F2-01 / S05.** The step announces `This is the step the current build skips.` and then refuses submission until purpose, surface, distance, style and all five targets are set. Expected: a skipped step cannot be mandatory. Next: filled them.

**F2-02 / S05. Blocking.** With every `Target` empty, five copies of `Enter a target for every stat, or leave the whole target unset.` blocked `Continue to legacy`. The message names all-empty as legal; the form rejects it. Expected: accept the legal state or say which stat is short. Next: entered the current stat line as targets, so the run now carries a `TARGET TOTAL 2,866` the source never states.

**F2-03 / S05.** `The distance field is required.` identified no located control; the four `distance` radios were in the DOM far below the fold, and the message neither names nor scrolls to them. Expected: an error that points at its field. Next: enumerated the form, found the group, chose `Long`.

**F2-04 / S06.** After both veterans were picked, headings still read `Not chosen` and `Projected start` still read `-`; only `Save Legacy` updated them. Expected: selection visible before save. Next: saved, then re-saved with grandparents; the second save kept everything.

**F2-05 / S06, S17.** No per-parent rank field and no rented/guest control, so `Vodka B+`, `Maruzensky UG` and `flagged Guest` (§1.5 lines 167-171) cannot be entered. The round-4 build had `Own rank (letters)` and `Rented for the run`. Expected: the six-node record includes what the client prints per ancestor. Next: recorded names and grandparent letters only.

**F2-06 / S06, S17.** Sparks have no entry point in this flow. The setup step collects none and the inheritance screen, which tells the user to `Pick the spark you want carried from each parent`, exposes only `turn`, `source_name`, `choice_label`, `origin_note` and states `This parent carries no recorded spark.` Expected: spark rows as the screen promises. Next: left them absent; the cockpit then reports the absence as run fact.

**F2-07 / S11, S16. Blocking.** At turns 67 and 68 `Available this turn` lists only early-career races and refuses each with `Maiden lock not cleared. ... this race opens only once a Debut or Maiden is won.` A run whose history was not logged cannot clear that lock, so no race is enterable and the source's own race day is not offered. Expected: the calendar at the run's real turn, or an explicit not-representable state. Next: stopped; recorded the gap.

**F2-08 / S14, S15.** Two rank lists are unordered: the grade selects read `G F E D C B A S`, and the ladder reads `G lv 1 F lv 1 D lv 2 E lv 2 B lv 3 C lv 3 A lv 4 S lv 5`. S14 states `S+ is the overall team rank, recorded elsewhere`, yet S15 still reads `Rank not recorded` and no input was found on S13, S14 or S15. Expected: ordered rungs and a field where the copy points. Next: recorded the absence (U2-02).

**F2-09 / S18.** The skills table prints the engine predicate as the row's description: `is_last_straight==1 (precondition: phase>=2&order<=4&change_order_onetime<0)`. Expected: client-facing wording. Next: reported.

**F2-10 / S13.** The fans readout shows `209,245` alone; the class and the 30,755-fan gap to Top Star (§1.1 line 39) appear nowhere, though §1.1 and §1.6 make that gap the run's headline objective. Expected: the gap the source names. Next: reported.

**F2-11 / S11.** `Undo turn 1` is offered where no turn has been recorded. Expected: undo bounded to real state. Next: used `Train instead`.

**F2-12 / S09, S13, S16.** Obligations are `Junior Make Debut` (`current`, due Junior Year Turn 12), `URA Finals Qualifier`, `URA Finals Semifinal` and `URA Finals Final (Aoharu)`, while the same run's scenario board says `Finale Unity Cup: not yet reached`. Three goals the source records as cleared cannot be marked. Expected: Unity Cup obligations and a cleared state. Next: left as rendered.

**F2-13 / S14.** Team setup's career nav has no `Scenario detail` link, so S15 could only be opened by typing the URL. Expected: the nav that the cockpit offers. Next: deviation recorded in §7.1.

### 8.3 Experiential findings

**E2-01.** The turn record is split: `Turn`, five stats, five facility levels and `outcome` sit inside a collapsed summary labelled `Client totals this turn (required to record)`, while `MOOD AFTER`, `FANS` and `SKILL POINTS` sit outside it. Two fill attempts failed with "element is not visible" before the summary was opened. The same shape recurs at S18 (add form in a details) and at S14's collapsed siblings.

**E2-02.** The submit button read `Record turn 1` after `Turn` was set to 67.

**E2-03.** Deck: `Equip` closes the picker and fills the lowest empty slot; nothing names the slot on the button, so six cards meant six open/filter/equip cycles and counting positions.

**E2-04.** Preflight's `Create the run` is disabled, and the only cause is the prose `Acknowledge the above to enable "Create the run".` above an unnamed checkbox.

**E2-05.** `Continue to build target` is a link on S03 and a button on S04, and the same string names both the action and the destination step.

**E2-06.** `Save team state` leaves no mark; confirmation only appeared on another screen.

**E2-07.** `Aim for at least 3 circlesas a margin` is missing a space.

**E2-08.** The race table's `SURFACE · DISTANCE` column carries the trainee's aptitude letter, so `Mile C` reads as a distance.

**E2-09.** Marking a skill `Acquired` leaves `PLANNED SPEND 0` and `REMAINING 173` unchanged, with no line distinguishing planned from acquired.

**E2-10.** The `Race` option in the pick group navigates instead of selecting, with nothing signalling the page change.

**E2-11.** Three Dashboard controls share one destination and near-identical labels.

*Timing/keyboard:* no timing anomaly; no keyboard-only pass attempted.

### 8.4 Unrepresented source data

| Finding | Datum | Screens |
|---|---|---|
| U2-01 | Parent ranks `B+` / `UG`; Maruzensky `flagged Guest` (§1.5 lines 167-171) | S06, S17 |
| U2-02 | Team Rank `S` (§1.8 line 262); Current Spirit | S09, S13, S14, S15 |
| U2-03 | Potential `Lvl 2` (§1.1 line 37) | S04, S09, S13 |
| U2-04 | Skill levels (`Blue Rose Closer Lvl 3`) and per-skill hint levels (§1.3, §2.1) | S18 |
| U2-05 | Race day cards, `Recommended` flag, Soft going, fan payouts (§1.6 lines 210-232) | S11, S16 |
| U2-06 | Failure rate `39%`, preview `+18 / +8 / +6`, banner `Floor Cleaning` (§1.7 lines 249-251) | S10, S12 |
| U2-07 | Six blue sparks plus the pink/green/white inventory (§1.5) | S06, S17 |
| U2-08 | Cleared-goal history, 3 of 8 (§1.6 lines 203-205) | S13 |
| U2-09 | Teammate roster, 20/20 with two clipped members (§1.9 lines 300-351) | S14 |

### 8.5 Walk completion

The pass did not reach a finished career and could not: five turns remain, and `Result` is correctly gated (`Available when the career is completed`). It stopped at S19 (`/training-runs`) because the race desk refused every entry (F2-07) and `Enter a race` would have committed a result the source does not state; the unvisited screens (`Timeline`, `Events`, `Run settings`) offer no action the source could fill.

Data deliberately not entered for lack of representation: parent ranks and the Guest flag, all sparks, Team Rank, Potential, skill levels, the race day, the failure and preview readings, the cleared-goal history, and the roster.

Values in the persisted state that are not from the source: turn `67` (derived), the five stat targets (forced, F2-02), `Purpose Story Clear`, `Surface Turf`, `Distance Long`, `Style Pace Chaser`, the `Guts` pick, the two veteran instance indexes (`· 14`, `· 9`), and `Energy band Low` (the source's band, selected by the walk). Everything else traces to a cited line.

## Appendix A — Derived probe list and answers

| # | Anomaly | Question | Answer |
|---|---|---|---|
| 1 | Mid-career snapshot | Can the flow hold a partial run? | State yes, history no: one turn record set Year/Month/Turn correctly, but obligations and race locks still behave as if the run started at turn 1 (S13, F2-07, F2-12). |
| 2 | No run number in source | Required? | No; the tool assigns `#32` and displays it (S09, S19). |
| 3 | Caps above the published 1300 | Enterable? | Yes for four stats; Wit has no ceiling field, and 1800 matches the published figure (S05). |
| 4 | Caps vs sparks unreconciled | Does the UI warn? | Neither reconciles nor warns; the cockpit prints both and quotes the source's own non-reconciliation (S09). |
| 5 | Borrowed friend card | Representable? | Yes: `Slot 6 · Friends` plus `Rented`; the owning account is not (S07). |
| 6 | Six ancestors | Fixed? | Yes, two parents plus four grandparents; parents must be banked Veterans or the free-text escape (S06). |
| 7 | Spark categories | Absent/zero/none? | No spark control exists in the flow, so absence is the only state (F2-06). |
| 8 | Skills individually, levels | Modeled? | Individually yes, with a status select and a turn; levels no (S18). |
| 9 | Race days, won/placed/pending | Representable? | Only via `Enter a race`, which is locked out; no pending state, and team-race circles are said to live on a race form never reachable (S16). |
| 10 | Mood, energy, turns left, SP | Current values? | Mood yes; energy exact/band/unknown; SP yes; turns left only derived from the turn number (S10-S13). |
| 11 | Freeform notes | Any? | No notes field on any run screen; the inheritance screen's `origin_note` is the only free text and is scoped to that event. |
| 12 | Persistence | Verified on later screens? | Caps at S09; deck and parents at S17; team state and grades at S15; turn values at S13; skill status at S18. |

## Appendix C — Remediation ideas (non-binding, not implemented)

1. Accept the all-empty target set the error text already permits, or name which stat is short.
2. Give the target step one identity: optional, or required with located errors.
3. Restore per-parent rank letters and a guest/rented flag, and add spark rows the inheritance screen can actually read.
4. Reflect a veteran pick immediately instead of only after save.
5. Resolve the race calendar at the run's real turn and add a scheduled-not-run state.
6. Place a Team Rank input where the copy claims one exists, and order both rank letter lists.
7. Print skill conditions in client words, not engine predicates.
8. Restore the fan class and gap figure on the cockpit.
9. Keep the submit label in step with `Turn`, and confirm `Save team state` on the page.
10. Scope obligations to the scenario and allow a cleared goal to be marked.

## Appendix D — Where pass 1 and pass 2 disagree

Same source document; the tree moved from `f6018f87` (2026-10-24) to `34fd3c9e` (2026-11-03).

| Item | Round 4 | This pass |
|---|---|---|
| Build target attaching to the run | Lost; Preflight `Missing`, cockpit `No build target was set for this run`, planner disabled | Fixed: ceilings drive the caps; Preflight reads `Your target Pass Story Clear · Turf Long · Pace Chaser` |
| Target re-save deleting skill priorities | Reproduced | Not applicable; the target step has no skill list now |
| Outcome required | Forced a fabricated `Success` | Optional; empty submitted cleanly |
| Preflight gate | `Start career` succeeded with a step `Missing` | `Create the run` disabled until acknowledged |
| Run id | URL only | Printed as `#32` in nav and Careers |
| Careers turn | `Turn ~11 / 24` | `67 / 72 · Senior Year · Early October` |
| Race screen turn/date | Header `Senior Year · Late October · Turn 20` | Header gone; the race set is still early-career and maiden-locked |
| Deck picker filter | Navigated and closed; epithet unmatched | Filter keeps panel; full epithet matches; `Equip` still closes it |
| Spark entry | Present per ancestor | Absent from the whole flow |
| Parent rank + rented flag | Present | Absent |
| Team stat grades | N/A, no column | Enterable selects, displayed on the scenario board |
| Fan gap `30,755 to next` | Displayed | Not displayed |
| Failure rate + preview readings | Enterable | Absent |
| Hint discounts | `not modelled` | Modelled with the §2.1 ladder and rounding case |
| URA obligations in a Unity Cup run | Reproduced | Reproduced |
| Filter reset on trainee select | Reproduced | Reproduced |
| Screenshots | Calls reported paths that do not exist | Not attempted; none retained |
