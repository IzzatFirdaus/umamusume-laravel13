# Rice Shower / Unity Cup — UX walk 8 (`RS-UC-2026-13`)

Status: **walk complete.** Primary single pass through the wizard door, 17 screens visited plus
one mis-click, one run created and one turn committed. The walk reached a stopping condition at
S17 (Result, not available while the run is Active).

> **Alternate-door companion.** `docs/audits/rice-shower-unity-cup-ux-walk-7-snapshot-door.md`
> (`RS-UC-2026-11`) records walk 7 through the snapshot import. This document is the wizard door: the
> primary path a new user takes, and the door walks 6 and 7 also used.

## 1. Header

- **Audit name / ID:** Rice Shower Unity Cup UX walk 8 / `RS-UC-2026-13`. The ID continues the
  repository's existing convention (`RS-UC-2026-10`, `-11`, `-12` on disk; `-13` was free).
- **Source document:** `docs/[Rosy_Dreams]Rice_Shower_Unity-Cup.md`, 1,652 lines, 151,468 bytes,
  read end to end once before the first click (sections §1.1–§11, including §9.1's completion
  report and §11's revision table and open-item rows).
- **Secondary reference (context, never UI evidence):** `docs/UMAMUSUME_REFERENCE.md`,
  `docs/scenarios/02-unity-cup.md`.
- **Starting URL:** `http://127.0.0.1:8000/` (HTTP 200 on arrival; the server was already running
  and was **not** started by this walk).
- **Walk start:** 2026-11-11 20:21:56 +0800. **Walk end:** 2026-11-11 20:58 +0800.
- **Browser / environment:** HeadlessChrome 145.0.0.0
  (`Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) HeadlessChrome/145.0.0.0 Safari/537.36`),
  Windows 11, viewport **1440 × 900** CSS px (devicePixelRatio 1), driven by CDP/Playwright.
- **Git state:** branch `master`, HEAD `6c10ff62a12e634123fd4e80945ff49f339412a5`, 15 dirty paths
  at walk start (14 untracked, 1 modified) — all peer-owned, none touched by this walk.
- **Devtools: not opened.** Read-only `page.evaluate`/`document.body.innerText` for DOM reads;
  no DOM writes, no request replay, no storage edits. Values were typed through the app's own
  controls with CDP `Input.insertText` and native `change`/`click` events dispatched on the real
  controls, never by assigning framework state.
- **Pre-existing run state:** `training_runs` held **0 rows** and `veterans` **0 rows** at walk
  start (read-only query). Run **#11** created by this walk is therefore this walk's own.
- **Run identity:** Rice Shower, `[Rosy Dreams]`, ★3, Unity Cup, snapshot at Senior Year Early
  October, **turn 67 of 72**, 5 turns remaining, mid-career and in progress. The source carries no
  run number; no run number was invented.
- **Door taken:** **wizard** (canonical). The two alternate doors visible at S01 (`Import an
  existing career` → `/career/snapshot`, `Import a historical career` → `/training-runs/import`)
  were not taken.
- **No-seeding statement:** every value entered went through a visible control. No seeder, factory,
  direct DB insert/update/delete, SQL, tinker write, state-mutating artisan command, API call,
  devtools DOM edit or request replay was used. Two read-only `php artisan tinker --execute` calls
  ran **before** the walk to establish that `training_runs` was empty; they wrote nothing. Two more
  ran **after** it to read run #11's own row back; they also wrote nothing.
- **Primary-pass scope / stopping point:** the six wizard steps, the cockpit before and after the
  commit, the training form, and the seven remaining career-nav screens — forward order, no
  backtracking. The walk stops at **S17 (Result)**, which states it is unavailable while the run is
  Active. It is not a dead end; it is the last destination in the rail.
- **One documented recovery:** the race-desk detour at S10 (`Race` action) navigated the browser to
  `/training-runs/11/races` and destroyed the unsaved training-form values. The form was re-entered
  once, in full, as **I-01**. The screens visited before it were not revisited as screens.

## 2. Mandatory execution capability check

Established before the first click, on the live application:

| Capability | Established how | Result |
| --- | --- | --- |
| Open the application | `new_tab('http://127.0.0.1:8000/')`, HTTP 200 | PASS |
| Inspect rendered screens | `document.body.innerText`, computed styles, `getBoundingClientRect` | PASS |
| Interact visible controls | real clicks on `a`/`button`/`label`, CDP `Input.insertText` | PASS |
| Enter and select values | 21 controls filled across S05–S10 | PASS |
| Observe navigation | `location.href` after each primary | PASS |
| Observe validation | read `aria-invalid`, `validity`, disabled state, flash copy | PASS |
| Capture screenshots | 43 PNGs written under `docs/audits/RS-UC-2026-13/screens/` | PASS |

No source-code inspection, HTTP request, API call or automated database operation substituted for a
browser interaction at any point in the primary pass. Source code was read (a) to explain an
observed result, and (b) for the two confirms in §9.2; each instance is labelled `CORROBORATED`
there and names the file.

## 3. Preparation — before the first click

### 3.1 Source extraction (structured inventory)

Values preserved exactly as the source states them. No contradiction resolved and no gap filled.

| Datum | Value | Source anchor |
| --- | --- | --- |
| Trainee | Rice Shower, `[Rosy Dreams]` | §1.1 @47 |
| Rarity | ★3 | §1.1 @47 |
| Potential level | Potential Lvl 2 | §1.1 @47 |
| Scenario | Unity Cup ("Unity Cup: Shine On, Team Spirit!") | §1.1 @47 |
| Position | Senior Year Early Oct, **turn 67 of 72**, 5 turns left | §1.1 @47, §2.5 derivation |
| Lifecycle | mid-career, in progress, not Completed/Veteran/Legacy | §1.1 @47, §10 |
| Run number | **none in source** | §1.1 @47 |
| Stats | Speed 607 · Stamina 577 · Power 549 · Guts 736 · Wit 397 | §1.2 @54 |
| In-force caps | 1325 / 1322 / 1368 / 1308 / 1800 | §1.2 @54 |
| Scenario floor | 1300 ×4, Wit 1800 | §1.2 @54 |
| Growth | +0 / +10 / +0 / +20 / +0 | §1.2 @54 |
| Unspent SP | 173 | §1.2 @54 |
| Mood | GREAT | §1.7 @250 |
| Energy | low; **no numeric value printed by the client** | §1.7 @250 |
| Aptitudes | Turf A, Dirt C; Sprint E, Mile B, Medium A, Long A; Front A, Pace A, Late B, End C | §1.2 @54, §11 C-01/C-02 |
| Facility levels | Speed 4, Stamina 4, Power 4, **Guts 5**, Wit 4 | §1.7 @262 |
| Deck card 1 | Tokai Teio `[Dream Big!]`, Speed, 35/35 | §1.4 @119 |
| Deck card 2 | Nishino Flower `[Even the Littlest Bud]`, Speed, 30/30 | §1.4 @122 |
| Deck card 3 | Biko Pegasus `[Double Carrot Punch!]`, Speed, 30/30 | §1.4 @123 |
| Deck card 4 | Super Creek `[Piece of Mind]`, Stamina, 50/50, **friend/borrowed slot** | §1.4 @124, §1.4 @128 |
| Deck card 5 | Matikanetannhauser `[Just Keep Going]`, Guts, 30/30 | §1.4 @125 |
| Deck card 6 | Haru Urara `[Urara's Day Off!]`, Guts, 35/35, **Unity Cup scenario link** | §1.4 @126 |
| Borrowed card | Super Creek, the deck's only 50/50 card and the friends-slot card | §1.4 @128-131 |
| Skills owned (visible ten) | Blue Rose Closer Lvl 3, Red Shift/LP1211-M, Corner Adept ○, Swinging Maestro, Nimble Navigator, Steadfast, Deep Breaths, Extra Tank, Pace Chaser Straightaways ○, Shrewd Step | §1.3 @105 |
| Skill owned (off-list) | Corner Recovery ○ | §1.3 @108 |
| Parents | Vodka `[Wild Top Gear]` rank B+; Maruzensky `[Formula R]` rank UG, **Guest** | §1.5 @180-185 |
| Grandparents (A) | Daiwa Scarlet `[Peak Blue]` B+; Seiun Sky `[Reeling in the Big One]` A | §1.5 @181-182 |
| Grandparents (B) | Daiwa Scarlet `[Peak Blue]` **S+**; Taiki Shuttle `[Wild Frontier]` UG | §1.5 @184-185, §11 C-03 |
| Blue sparks | Power ★☆☆ (Vodka), Speed ★★☆ (Maruzensky) | §1.5 @190, @193 |
| Fans / class | 209,245 fans; class Star; 30,755 to Top Star | §1.1 @51, §1.15 @474 |
| Team | Blue Bloom, motto "Dreaming Big", Team Rank S, league 8th | §1.6 @211, §1.8 @274 |
| Team grades | Speed A, Stamina A, Power A, **Guts S**, Wit A | §1.8 @276 |
| Burst counters | 55 Unity Trainings, 6 Spirit Bursts, 5 Extreme Bursts, 20/20 members | §1.8 @277-280 |
| Races won | 4 preseason rounds (R1 Junior Late Dec … R4 Senior Late Jun) | §1.6 @212 |
| This turn's race | Kyoto Daishoten, G2, Kyoto Turf 2400m, **Soft** | §1.6 @228 |
| Active objective | Place 1st in Arima Kinen, Senior Late Dec, 5 turns | §1.6 @218 |
| Cleared objectives | Nikkei Sho top 3; Tenno Sho (Spring) 1st; Takarazuka Kinen top 3 | §1.6 @216-218 |
| Failure rate on the observed tile | 39% | §1.7 @263 |
| Tile preview | +18 Speed / +8 Power / +6 Skill Points | §1.7 @263 |
| Recommended SP spend | Pace Chaser Straightaways ◎ 91, then one 81 | §2.3 @563-568 |

### 3.2 Source anomaly register

| ID | Source location | Source observation | Question for the UI walk |
| --- | --- | --- | --- |
| P-01 | §1.2 @74-86 | Four cap extras (+25/+22/+68/+8) over the 1300 floor; six blue sparks explain only Guts +8 | Can the run record caps that do not reconcile with the recorded sparks, without the UI claiming a derivation? |
| P-02 | §1.7 @256-260 | Energy is low; the client prints **no number** | Can the form record a band without demanding an exact figure? |
| P-03 | §1.7 @263 | Failure 39% and a +18/+8/+6 preview are client reads for **this** tile | Is there a labelled place for a per-turn failure rate and preview? |
| P-04 | §2.2 @544-547 | Ignited Spirit series runs to five, only four hinted | Does the skill catalogue carry all five, and can a plan name one? |
| P-05 | §2.3 @570-577 | The white→gold prerequisite decides a 130 SP buy; the client has no screen stating a prerequisite | Can the planner express a prerequisite or hint level at all? |
| P-06 | §2.4 @604-605 | Burst tier is cashed at one November event; 11 combined is inside the 10–12 band | Can the run record burst counters and the countdown to that event? |
| P-07 | §2.5 @742-745 | A burst carries **two** numbers: teammate 150-scale and trainee 15-scale | Does any field force them into one "stat gain"? |
| P-08 | §2.6 @760-781 | Facility level is derived from the **team's** grade (S→5, A→4) | Can the run hold per-facility levels and the team grades side by side? |
| P-09 | §4 @851-855, §8.4 @1340-1348 | League 8th against a "≥ 10" condition reads two ways | Does the UI assert a verdict the source declines to? |
| P-10 | §8.7 @1390-1396 | Client prints "5 turn(s) until the Unity Cup" against a 7-half-month calendar gap | Does the countdown the app shows agree with the client's own number? |
| P-11 | §6 item 6 @942-944 | The in-career Log reads "There is no log." in every frame | Can the run record a race/event history at all? |
| P-12 | §6 item 8 @949-959, §8.3 @1300-1336 | Finals venue and exact distance are per-set and ❌ UNVERIFIED | Does any field invite a fabricated venue? |
| P-13 | §6 item 7 @945-948 | 18 named roster entries against a 20/20 counter | Can the run hold a roster count that disagrees with named members? |
| P-14 | §1.4 @148-150 | A padlock number is the unlock **level**, not a value | Does the deck surface print a locked row as a value? |
| P-15 | §1.13 @462, §6 item 12 @976-981 | Learn-screen row tints are observed and unexplained | Does the planner invent a meaning for a tint? |
| P-16 | §11 C-03 @1615 | The same card, `[Peak Blue]` Daiwa Scarlet, carries two different ranks (B+ / S+) | Can both branches hold their own rank for one repeated name? |
| P-17 | §11 open items @1638-1640 | Vodka's Power spark reads ★★☆ in one screen, ★☆☆ in the other | Does the UI force one reading? |
| P-18 | §6 item 11 @972-974 | Maruzensky's rank reads `UG` on frames, `UG1` per the Trainer | Is a rank letter field free-text or a closed list? |
| P-19 | §9 @1442-1450 | A training level "reads like a count of sessions and is not one" | Does the level field say what it is? |
| P-20 | §2.4 @618-621 | The payout event's date is disputed: early vs late November | Does the app name the event's month? |
| P-21 | §9 @1445-1446 | The "5 turns until the Unity Cup" counter is the **finals** counter, not the six-monthly Team Race one | Does the cockpit distinguish them? |
| P-22 | §10 @1574-1581 | Pre-career setup and turn 0 have **no** client capture; the setup screens are unfilmed | Can the setup flow be walked honestly with no frame to compare against? |
| P-23 | §1.9 @363-364 | Two roster members are clipped out of the capture | Does the app assert a complete roster? |
| P-24 | §2.2 @556-557 | Seven skills are out of reach at 173 SP | Does the planner warn on an unaffordable plan? |

### 3.3 Environment pre-flight

| Item | Value | Established how |
| --- | --- | --- |
| Git branch / commit | `master` / `6c10ff62` | `git rev-parse` |
| Working tree | 15 dirty paths (14 untracked, 1 modified) | `git status --short` |
| Application process | already serving `127.0.0.1:8000`; not started by this walk | `curl` HTTP 200 before any action |
| Database | `database/database.sqlite`; `training_runs` 0 rows, `veterans` 0 rows | read-only tinker query |
| Migrations | **1 Pending**: `2026_11_10_010500_add_current_spirit_proxy_to_training_runs_table` | `php artisan migrate:status` |
| Browser | HeadlessChrome 145.0.0.0 | `navigator.userAgent` |
| Viewport / zoom | 1440 × 900 CSS px, DPR 1, 100% zoom | `Emulation.setDeviceMetricsOverride` + `innerWidth/innerHeight` |
| Screenshot capability | working; 43 PNGs written inside the repo | `Page.captureScreenshot` |

**Uncertainty recorded, not resolved.** One committed migration is **Pending** on the dev database
(KI-60's exact shape). This walk did not run `migrate` — that is a write, it is not authorised by
this task, and the walk's own flow does not require the column. Consequence: if the training form or
cockpit reads `current_spirit_proxy`, that read would 500. It did not fail on any screen this walk
visited, so either no visited screen reads it or Eloquent tolerates the missing column. **UNVERIFIED
which**, and recorded as E-04.

### 3.4 Existing state protection

`training_runs` and `veterans` were both empty, so nothing of a prior walk could be overwritten or
duplicated. The one destructive-looking action available (`Create the run`, irreversible per its own
copy) was taken once, deliberately, and is the walk's own run #11. No `migrate:fresh`, no reseed, no
delete, no `uma:backup` restore. The 15 pre-existing dirty paths were left exactly as found.

## 4. Screen ledger

17 screens, in visit order, plus one mis-click recorded separately.

| Screen | Route | Arrived from | Exited via | Shot |
| --- | --- | --- | --- | --- |
| S01 | `/` | entry point | link `Start a career` | `S01-dashboard.png` |
| S02 | `/career/setup/scenario` | S01 | button `Continue to trainee` | `S02-scenario.png`, `S02b-…`, `S02c-…` |
| S03 | `/career/setup/trainee` | S02 | button `Select` on Rice Shower | `S03-trainee-picker.png`, `S03b-…`, `S03c-…` |
| S04 | `/career/setup/trainee/30` | S03 | link `Continue to build target` | `S04-trainee-profile.png` |
| S05 | `/career/setup/target` | S04 | button `Continue to legacy` | `S05-build-target.png`, `S05b-…` |
| S06 | `/career/setup/legacy` | S05 | button `Continue to deck` | `S06-legacy.png`, `S06b2`–`S06g` |
| S07 | `/career/setup/deck` | S06 | button `Continue to preflight` | `S07-deck.png`, `S07b`–`S07f` |
| S08 | `/career/setup/preflight` | S07 | button `Create the run` | `S08-preflight.png`, `S08b-…` |
| S09 | `/training-runs/11/cockpit` | S08 (post-create) | career-nav link `Training` | `S09-cockpit-created.png` |
| S10 | `/training-runs/11/training` | S09 | radio `Race` → `S11`; then `Record turn 67` | `S10-training-form.png`, `S10b`–`S10d`, `S10c-race-action-selected.png` |
| S11 | `/training-runs/11/races` | S10 (the `Race` radio's own navigation, **before** any commit) | `Back to cockpit`, then `Training` to re-enter S10 | `S11-races.png` |
| S12 | `/training-runs/11/cockpit` | S10 (post-commit) | career-nav link `Races` | `S12-cockpit-after-turn67.png` |
| S13 | `/training-runs/11/skills` | S12 | `Back to cockpit`, then `Events` | `S13-run-skills-planner.png`, `S13b`–`S13d` |
| S14 | `/training-runs/11/events` | S13 | career-nav link `Inheritance` | `S14-events.png` |
| S15 | `/training-runs/11/inheritance` | S14 | career-nav link `Deck` | `S15-inheritance.png` |
| S16 | `/training-runs/11/deck` | S15 | career-nav link `Timeline` | `S16-run-deck.png` |
| S17 | `/training-runs/11/timeline` | S16 | career-nav link `Result` | `S17-timeline.png` |
| S18 | `/training-runs/11/result` | S17 | — (walk end) | `S18-result.png` |

> **Ledger order note.** S11 is reached from S10's own `Race` radio and is visited **before** the turn
> is committed; S12 is the cockpit *after* the commit. The filenames match this order.

**Mis-click, recorded because it happened:** the first attempt to reach the run's Skills planner
clicked the sidebar `Skills` link (`/skills`, the global library) rather than the career-nav
`Skills` link (`/training-runs/11/skills`). The screen returned is `S13a-library-skills-misclick.png`
and is **not** a screen of this walk. The cause is that both links carry the identical accessible
name `Skills` on the same page — finding **E-02**.

## 5. Screen-by-screen walk

### S01 — Dashboard (`/`)

**Arrival:** 20:21:56 +0800, entry point, HTTP 200. h1 `Trainer Desk`.

**What the screen asks for:** nothing. No input fields anywhere (`inputs: []`).

**Read:** `0 active careers, 0 finished runs, and 4 permanent Global scenarios.`; the `Get running`
checklist as three ordered steps with live state — 1 `Fetch reference data` **Done**, 2 `Start or
import a career` **To do**, 3 `Record your first turn` **To do**; counters `CAREERS RUNNING 0`,
`TRAINEES CATALOGUED 133`, `SKILLS 1,910`, `SUPPORT CARDS 559`; `DATA STATUS Global data ·
Current`, `Verified 2026-11-02 · GameTora scenarios.json`; `REVIEW QUEUE 304 pending matches`.

**Three career doors, as links:** `Start a career` → `/career/setup/scenario`; `Import an existing
career` → `/career/snapshot`; `Import a historical career` → `/training-runs/import`. A fourth nav
item `New Career` → `/career/setup/scenario` duplicates the first.

**Source data with no UI home:** none at this screen.

**Action taken:** clicked `Start a career`.

**Findings:** **E-01** — `Start a career`, `New career` and `New Career` are three control labels
for one route, and nothing marks the wizard as the primary path. **E-03** — `Import an existing
career` and `Import a historical career` differ by one word and neither states what distinguishes
them (a position snapshot versus a file of rows). Both were clicked around in walk 7; this walk did
not click them.

### S02 — Scenario (`/career/setup/scenario`)

**Arrival:** 20:21:56 +0800 via the S01 link. h1 `Which scenario is this run?`

**Lede:** `The scenario decides which panels the cockpit composes, which caps apply, and which
systems exist. It cannot be changed after the first turn.`

**Controls:** four radios, `name="scenario"`, `required=false`, **none pre-selected**:
`scenario-radio-ura_finale`, `-unity_cup`, `-trackblazer`, `-our_grand_concert`. Each card now
carries a `Global · <date>` stamp, a one-sentence description, and a **panel tag row** —
URA Finale `Race calendar, Career goals, Caps 1400 ×5`; Unity Cup `Team races, Team Rank ladder,
Caps 1300 ×4 · Wit 1800`; Trackblazer `Grade Point objectives, Pro Shop, Caps 1200 ×3 · Stamina
1900 · Wit 1500`; Our Grand Concert `Career goals, Performance input, Caps 1300 ×3 · Speed 1600 ·
Guts 1500`. One button `Continue to trainee`, disabled on arrival, and a helper line
`Choose a scenario to continue.` A six-step rail reads `1 Scenario · 2 Trainee · 3 Build target ·
4 Legacy · 5 Deck · 6 Preflight` with `Step 1 of 6`.

**Below the fold:** a `What changes after this step` table stating `Scenario — No — Switching
composes a different panel set, cap table and currency`, plus rows for Trainee (No), Build target
(Yes), Legacy and deck (Yes).

**Value entered:** `Unity Cup` → source §1.1 @47. **Direct.**

**Source data with no UI home:** the scenario's full label `Unity Cup: Shine On, Team Spirit!` is
not printed anywhere on this step (**U-01**). The rank-to-level ladder, the burst tier bands, the
league bonus ladder and the facility cap source are all scenario mechanics named in the source §1.8,
§2.4 and §2.6, and the step offers no way to read them (**U-02**).

**Findings:** **F-01** — selecting a scenario does **not** clear `Choose a scenario to continue.`
The helper stays under the primary for the rest of the visit; only its parenthetical `(no scenario
selected)` clause disappears if the copy is read closely, and the sentence as a whole still tells
the Trainer to do a thing they have just done. **E-04** — the four radios are `required=false` while
the step is genuinely mandatory; the only cue that selection is compulsory is the greying of the
primary, which also has no `aria-describedby` bound to the helper text.

**Action taken:** clicked the Unity Cup label (`clicked via label`, `checked: ["unity_cup"]`).
Primary became enabled. Clicked `Continue to trainee`.

### S03 — Trainee picker (`/career/setup/trainee`)

**Arrival:** 20:22:56 +0800. h1 `Who are you training?`

**Lede:** `66 trainees are catalogued. Filter by aptitude if you are building toward a specific
race, then pick the card you are running.`

**Controls:** one `input[type=text][name=search][id=filter-search]` placeholder `Name or unique
skill`; three facet selects (`Surface`, `Distance`, `Running style`) each `Any | S | A | B | C | D |
E | F | G`; one `Sort` select with 11 options; a `Clear` button. The roster renders as cards, each
with `Select`, name, `Japanese · [Epithet]`, rarity line `★★★ SSR`, `1 released form`,
`Released (Global)`, and three lines of aptitudes. Pager reads `66 trainees · page 1 of 11 · sorted
by Name`.

**Values entered:** search `Rice Shower` (**Direct**, §1.1 @47) → `1 trainee · page 1 of 1`. Clicked
`Select` on her card.

**After the pick:** the URL became
`/career/setup/trainee?direction=asc&search=Rice%20Shower&sortBy=name`; a flash read
`Trainee set: Rice Shower.`; a `Selected` panel appeared with `Rice Shower [Rosy Dreams]`,
`Medium A · Turf A · Pace Chaser A. The form you pick sets this run's rarity and potential.`, a
`Form` select with two options `[Rosy Dreams]` and `[Vampire Makeover!]`, and `Preview profile`.

**Source data with no UI home:** the source's Potential Level (**Lvl 2**) is not requested on this
step and is not carried into the draft at all — see **U-03**. The card's Japanese name is shown and
the source never states one differently, so no conflict.

**Findings:** **F-02** — the searched roster announces `1 trainee is catalogued` where the unfiltered
roster announced `66 trainees are catalogued`. The count follows the filter but the sentence does
not say so, so a Trainer reading only the second visit is told the catalogue holds one trainee.
**E-05** — the `Form` select shows `[Rosy Dreams]` pre-selected although
`props.selectedCard` is `null` on the cold load (read from the page props: `selectedCard: None,
selected: 30`). A form is *displayed* as chosen that the draft has not stored; the run that gets
created therefore carries `character_card_id: null`, which is what S08's `FORM · N/A` reports.

**Action taken:** clicked `Review Rice Shower's aptitudes and choose a form` → S04.

### S04 — Trainee profile (`/career/setup/trainee/30`)

**Arrival:** 20:23:40 +0800. h1 `Rice Shower ライスシャワー`.

**Read:** the screen states its own role: `This is a read screen: nothing here is edited in the
wizard. It states what the catalog holds about her before the choice is made, and the two controls
in the head band are the only ones on it.`

Sections and values, all matching the source: `Aptitudes` — `Turf A / Dirt G`, `Medium A / Long A ·
Mile C · Sprint E`, `Pace Chaser A / Front Runner B · Late Surger C · End Closer G`. `Growth row`
— Speed 0% · Stamina 10% · Power 0% · Guts 20% · Wit 0%. `Unique skill` — `Blue Rose Closer`,
`Every Rose Has Its Fangs`. `Base stats` — Speed 71 · Stamina 117 · Power 70 · Guts 102 · Wit 90.
`Costume forms` — `3 forms (2 released, 1 undated)` with `[Rosy Dreams] Debut form · Jun 26, 2025`
marked `Selected`, `Yummy Dreamy Fairy` with no rarity, `[Vampire Makeover!] Nov 24, 2025`.
`Scenarios you can run this trainee in` — all four, with the disclosure that `this page does not
carry a per-trainee link list`.

**Aptitude reconciliation against the source:** the screen prints `Dirt G`, and the source's **§11
C-01 correction** says `Dirt C`. The source's own dated erratum at §1.2 @91-101 records this exact
divergence and adjudicates it: the seeded catalogue carries `Dirt G`, the client prints `Dirt C`,
"the two readings disagree about the client… the tool was not changed: the picker's values are the
catalogue's own". The screen reproduces the catalogue letter for letter. **No defect**; recorded so a
later reader does not file one. `Mile C`, `Front B`, `Late C`, `End G` likewise match the erratum's
catalogue values, not the source's frame reads.

**Source data with no UI home:** the **Caps** (1325/1322/1368/1308/1800) do not appear here; they
are entered at S05 as ceilings. The source's **Potential Lvl 2** still has no field (**U-03**).

**Findings:** **E-06** — `Yummy Dreamy Fairy` renders with an empty rarity cell where the other two
forms print `★★★`. The screen's own note says rarity is `printed per form and never as a
trainee-level badge`, so the blank is honest, but it reads as a missing value rather than as an
undated form.

**Action taken:** clicked `Continue to build target`.

### S05 — Build target (`/career/setup/target`)

**Arrival:** 20:24:20 +0800. h1 `What are you building toward?`

**Lede:** `Five stat targets and the distance you are building for. These are your own goals, not a
game rule, and the cockpit's bars are drawn against them.`

**Controls:** three selects — `Purpose` (`Not chosen yet | Story Clear | Champions Meeting | Parent
Farming | Skill Farming`), `Surface` (`Not chosen yet | Turf | Dirt`), `Style` (`Not chosen yet |
Front Runner | Pace Chaser | Late Surger | End Closer`) — and ten number inputs:

| Label | id | min | max |
| --- | --- | --- | --- |
| `Speed` | `targets_Speed` | 0 | 1300 |
| `Speed ceiling` | `cap_speed` | 1 | 2000 |
| `Stamina` | `targets_Stamina` | 0 | 1300 |
| `Stamina ceiling` | `cap_stamina` | 1 | 2000 |
| `Power` | `targets_Power` | 0 | 1300 |
| `Power ceiling` | `cap_power` | 1 | 2000 |
| `Guts` | `targets_Guts` | 0 | 1300 |
| `Guts ceiling` | `cap_guts` | 1 | 2000 |
| `Wit` | `targets_Wit` | 0 | 1800 |

No fifth ceiling input; the page states `No enterable ceiling for this stat` under Wit. Four
`Distance` radios with band sentences. An optional skill-priority picker
(`Search and add skills…`).

**Values entered:** the four **in-force caps** from the source §1.2 — `cap_speed 1325`,
`cap_stamina 1322`, `cap_power 1368`, `cap_guts 1308`. **Direct** (the source states these exact
figures as the caps in force). Purpose / Surface / Style and all five targets were left empty,
because the source carries no goal of its own — its stat line is the current state, not a target.
Classified **Omitted** with the reason recorded here rather than a fabricated goal.

**What the screen said while I typed:** the total row kept reading
`Sum of the entered ceilings: No ceilings entered` even with four ceilings in the fields — see
**F-04** — and `Your total: N/A, No total is printed because at least one of the five targets is
still empty`, which is correct.

**Source data with no UI home:** the caps themselves are enterable, but **there is no field that
says these are the caps in force** rather than goals; the column is documented as a goal
(`a target above the cap is refused at preflight`). Entering the real caps as ceilings is the only
faithful home, and it changes the meaning of the column (**E-07**). The source's **Growth row**, its
**facility levels** and its **Potential level** have no field at this step (**U-03**, **U-04**).

**Findings:** **F-04** — `Sum of the entered ceilings` reads `No ceilings entered` while four
ceilings sit in the inputs. One reload of this screen later showed `5323` for the same four values,
so the figure is correct on load and stale while typing; the total is computed from the saved props
(`props.enteredCaps`), not from the live form. **E-08** — the step's own status line still reads
`This is the step the current build skips.` above a step the wizard will not let you pass without
visiting. **E-09** — `Wit` has no ceiling input while the other four do; explained in place, but it
means the run's own 1800 Wit cap can never be stated as entered.

**Action taken:** clicked `Continue to legacy` with the four caps entered and everything else empty.
Flash on arrival at S06: `Build target saved.`

### S06 — Legacy (`/career/setup/legacy`)

**Arrival:** 20:26:10 +0800 (flash `Build target saved.`). h1 `Inherit from two parents`.

**Lede:** `A run starts with the sparks its parents carry. Both slots are optional; a run with no
parents starts from the card's own base.` Scope note: `Record only. This screen stores and compares
what you enter. It does not compute inheritance.`

**Empty state:** `No Veterans recorded yet. The Veteran library is where a parent is picked from, and
it holds a run once you complete one, so this step cannot name a parent until then.` Each parent slot
offers a `<select>` with `Not chosen | Not in my library - enter a name`, plus `Leave empty`.

**Values entered (all via visible controls):**

| Field | Value | Class | Source |
| --- | --- | --- | --- |
| `Parent A` → `Not in my library - enter a name` | — | UI-required | the library is empty |
| `Parent name` (A) | `Vodka [Wild Top Gear]` | Direct | §1.5 @180 |
| `Rank (letters)` (A) | `B+` | Direct | §1.5 @180 |
| `Guest / rented parent` (A) | unchecked | Direct | §1.5 @183 — only Legacy 2 is Guest |
| `Parent name` (B) | `Maruzensky [Formula R]` | Direct | §1.5 @183 |
| `Rank (letters)` (B) | `UG` | Direct | §1.5 @183 (open item 11 records the `UG`/`UG1` ambiguity; `UG` is what the frames read) |
| `Guest / rented parent` (B) | **checked** | Direct | §1.5 @183 — "flagged Guest" |
| `Grandparent A1` | `Daiwa Scarlet [Peak Blue]` / `B+` | Direct | §1.5 @181 |
| `Grandparent A2` | `Seiun Sky [Reeling in the Big One]` / `A` | Direct | §1.5 @182 |
| `Grandparent B1` | `Daiwa Scarlet [Peak Blue]` / `S+` | Direct | §1.5 @184, §11 C-03 |
| `Grandparent B2` | `Taiki Shuttle [Wild Frontier]` / `UG` | Direct | §1.5 @185 |
| Parent A spark | `Blue` / `Stat` / `Power` / `1` | Direct | §1.5 @190 |
| Parent B spark | `Blue` / `Stat` / `Speed` / `2` | Direct | §1.5 @193 |

The `Rank (stars)` fields were left empty: the source states ranks as letters only.

**P-16 answered — yes.** Two grandparents named `Daiwa Scarlet [Peak Blue]` hold different ranks
(`B+` and `S+`) on one screen with no complaint. **CORROBORATED** against
`resources/js/pages/Career/LegacySelect.vue` (each ancestor row is its own form object; the rank
field is per row) — inspected only to explain why the repeated name was accepted.

**The spark Target is a searchable combobox, not a text field.** Typing `Power` into it did **not**
set the value; the row stayed `Target: Not recorded` through a save. Reading
`resources/js/components/legacy/SparkTargetPicker.vue:139-146` explains the observed result: the
input is a search box and `select()` is the only writer, so a typed string is discarded unless an
option is clicked. The first save therefore persisted `{"kind":"blue","target":null,"stars":1}`.
Recovered within the same screen visit by focusing the field, typing, and clicking the one option
offered. **CORROBORATED**, and the original observation is recorded as **F-03**.

**One recovery — I-01.** A mis-click on `Leave empty` for Parent A cleared that slot's name and rank
(observed: `legacy-name-0` went from `Vodka [Wild Top Gear]` to empty, and the parent block fell
back to `Not chosen`). The slot was re-entered once. Recorded as an improvisation, not a defect: the
button's label matches what it does.

**Source data with no UI home:** the source's **white spark** rows, **pink** aptitude sparks and
**green** unique-skill sparks for the six ancestors are not entered; only the two **blue** sparks —
the ones the source itself uses in its cap arithmetic — were. That is a deliberate **Omission**:
the source's §1.5 tables carry far more spark rows than the wizard can hold in one pass, and none of
them affect a value the run reads. Recorded as **U-05**. The source's **Pair Affinity** is not
stated, so the field was left `Not recorded` (**Omitted**, not derived).

**Findings:** **F-03** — the spark `Target` combobox accepts keystrokes and displays them as a value
without committing them; a Trainer who types and saves stores `target: null` and has no error, no
`aria-invalid` and no warning. There is no visible affordance telling them a click is required
beyond the list appearing. **E-10** — the `Add Spark` buttons for grandparents are rendered with an
empty caption slot: the accessible names read `Add Spark  to Grandparent A1` with a double space
where a category word belongs. **E-11** — `Projected start` prints `-` in **From Parent A** and
**From Parent B** for a stat the parent's blue spark names, until the screen is reloaded. Observed:
with `Blue Power ★1` on Parent A and `Blue Speed ★2` on Parent B, the table showed five `-` rows;
after a `<form>` save and a cold reload the payload carried the sparks but the table still showed
`-`, and the parent cards beside it did print `Blue spark · ★1` / `Blue spark · ★2`. So the parent
summary reads the stored sparks and the table beside it does not.

**Action taken:** `Save and stay` (twice, once to commit both parents and once after the combobox
recovery), then `Continue to deck`.

### S07 — Deck (`/career/setup/deck`)

**Arrival:** 20:33:40 +0800. h1 `Build the six-card deck`.

**Read:** `Six slots. Every card earns its place by the training it boosts, the events it brings, or
the friendship gauge it starts with.` A `Strategic hint for Unity Cup:` reads `Unity Cup's Wit cap is
1800; the other four stats cap at 1300.` Status line `Deck 0 of 6 slots filled`. Each of the six
slots renders `Not equipped.`, an `Ownership` line reading `N/A: No ownership has been recorded for
this slot yet.`, an `Owned`/`Rented` pair of `aria-pressed` buttons, and `Choose a card in Slot N`.
Slot 6 is labelled `Slot 6 · Friends` with a `Friends slot` badge and the note that
`The role belongs to the position and not to the card parked in it, so any card may sit there.`

**The slot picker opened by the slot button.** Clicking `Choose a card in Slot 1` set the URL to
`?deck_slot=1` and rendered a `Choose a card for Slot 1` panel with `Type` (All/Speed/Stamina/Power/
Guts/Pal/Group), a `Name contains` search, a `Filter` button, and an `Owned only` checkbox reading
`NOT RECORDED — Calculated — (no ownership recorded in any saved run)`.

**The filter needs an explicit submit.** Typing `Tokai Teio` into `Name contains` and waiting changed
nothing; pressing `Filter` set `?deck_slot=1&q=Tokai%20Teio` and the list narrowed to her four cards,
two of which carried a `Speed fits this slot` note. Recorded as **E-12**: the field is labelled
`Name contains` and sits beside a `Filter` button, so the requirement is discoverable, but the
absence of live filtering is not what a field with a search-shaped input usually implies.

**Values entered (all Direct, source §1.4 @119-126):**

| Slot | Card | Ownership |
| --- | --- | --- |
| 1 | Tokai Teio `[Dream Big!]` | Owned |
| 2 | Nishino Flower `[Even the Littlest Bud]` | Owned |
| 3 | Biko Pegasus `[Double Carrot Punch!]` | Owned |
| 4 | Haru Urara `[Urara's Day Off!]` | Owned |
| 5 | Matikanetannhauser `[Just Keep Going]` | Owned |
| 6 · Friends | Super Creek `[Piece of Mind]` | **Rented** — the source's borrowed/friend card (§1.4 @128-131) |

Two true state changes happened during this screen and are **recorded, not hidden**. First, the deck
was initially built with Super Creek in slot 4 and Haru Urara in slot 6; the source places them the
other way round, so both slots were replaced (`Replace in Slot 4`, `Replace in Slot 6 · Friends`) and
the Rented flag was set afterwards. Second, the first `Replace in Slot 6 · Friends` attempt reported
`no equip`; the picker was still showing Haru Urara's filtered list and the requested card was not in
it, so the picker was re-opened from `Choose a card in Slot 6 · Friends` and Super Creek was equipped
from there. Neither cost a screen revisit.

**Verified on the run, later:** S16 renders `Slot 6 · Friends — Super Creek [Piece of Mind]` with
`Rented` `aria-pressed=true` and `Owned` `aria-pressed=false`, and `Slot 4 — Haru Urara [Urara's Day
Off!]` with `Owned` true. The Rented flag **persisted onto the run**, which is the source's §1.4
"friend card" fact carried as data.

**Source data with no UI home:** each card's **client level** (35/35, 30/30, 30/30, 50/50, 30/30,
35/35) is not enterable on this step — the slot cards print a `Level` label with an unlabelled number
input beside it and no id, and no value was written by the wizard's own flow. Recorded as **U-06**
and re-measured at S16. The source's per-card **effect sets** and **hint pools** live in the
`Card effects (N)` disclosure, read-only (**U-07**).

**Findings:** **E-12** — the `Name contains` filter requires an explicit `Filter` press. **E-13** —
the `Level` field on each slot card has no `id`, no `<label for>`, and no accessible name, so it is
reachable only by tab order inside a section that already has six other unlabelled numbers. **F-05**
— `Deck 5 of 6 slots filled` was printed while all six positions carried a card, at the moment
between the `Remove` of slot 6 and the re-equip. Confirmed against the payload: the count is honest
about the state it is reading and the state was genuinely empty for that moment; the finding is that
the paragraph under the count still read `Not equipped. The six cards are the deck…` while five of
six were equipped and the count above said five of six, so the two sentences disagree about whether
the deck is built.

**Action taken:** `Continue to preflight`.

### S08 — Preflight (`/career/setup/preflight`)

**Arrival:** 20:43:01 +0800. h1 is the block heading `This run`; the page title band reads
`NEW CAREER`.

**Read — the summary tiles:**

| Tile | Value printed |
| --- | --- |
| `TRAINEE` | `Rice Shower` |
| `FORM` | `N/A`, `No costume form was chosen in this draft, so none is named here.` |
| `SCENARIO` | `Unity Cup` |
| `DISTANCE` | `N/A`, `The target step has not been entered.` |
| `PARENTS` | `Vodka [Wild Top Gear] · Maruzensky [Formula R]` |
| `DECK` | `6 of 6` |
| `TARGET TOTAL` | `N/A`, `The target step has not been entered.` |
| `CAREER LENGTH` | `72 turns` |

**Read — the checks table:** `1 step needs attention` over five rows — `Scenario Pass Unity Cup`;
`Trainee Pass Rice Shower (ライスシャワー)`; `Your target Missing Not entered`; `Legacy Pass No
Affinity grade read · 2 of two parents recorded`; `Deck Pass 6 of six positions carry a card`.

**Two contradictions, both corroborated against the server's own payload.**

1. **`FORM · N/A` is wrong and the page says why it is wrong.** The trainee step's `Form` select
   showed `[Rosy Dreams]` and its `<select id="selected-form">` carried `value="31"`; the picker
   writes `character_card_id` only on `@update:model-value`
   (`resources/js/pages/Career/TraineeSelect.vue:426-432`), and the pre-select is a display default
   from `selectedHeadlineForm`, never a posted value. So the draft genuinely holds no form, the run
   is created with `character_card_id: null` (confirmed on the row: `"card": null`), and the
   preflight sentence is **accurate**. It is the trainee step's display that implies otherwise. That
   makes this **E-05**'s consequence, observed end to end, not a preflight defect.
2. **`DISTANCE · N/A — The target step has not been entered` is false.** The target step *was*
   entered and saved: `Build target saved.` flashed on arrival at S06, the four ceilings persisted
   into the run (`cap_speed 1325`, `cap_stamina 1322`, `cap_power 1368`, `cap_guts 1308` all present
   on run #11), and a re-visit of S05 read those four values back out of the fields. What the
   preflight actually reads is `contract.build.target`, which is `null` because
   `BuildTargetPayload::fromArray()` needs a complete payload — a `purpose`, a `distance band`, a
   `surface`, a `style` and all five target integers — and this draft carries none of them. Verified
   against `app/Http/Controllers/Career/PreflightController.php:206-218`, which returns `null` on
   either an absent draft key or an unparseable payload and the page prints one sentence for both
   cases. **This is finding F-06, and it is the walk's most consequential one:** a Trainer who saved
   a partial target is told their step was never entered, and the run is created with no build
   target at all.

**The irreversible action.** The page states `I understand this creates an Active run that cannot be
un-created; retirement is the only exit.` The `Create the run` button is disabled until that
checkbox is ticked; ticking it enabled the button **while `Your target` still read `Missing`**. So
`Missing` is advisory and does not gate creation.

**Findings:** **F-06** (above). **E-14** — `1 step needs attention` counts a `Missing` row as
"needs attention" and the same page lets the run be created anyway; nothing distinguishes "this will
block you" from "this will be recorded as absent". **U-03** — preflight has a `FORM` tile but no tile
or row for the trainee's **Potential level**, which the source states (§1.1) and the run model has no
column for.

**Action taken:** ticked `confirmed`, then `Create the run` → S09.

### S09 — Cockpit, before any turn (`/training-runs/11/cockpit`)

**Arrival:** 20:51:08 +0800. h1 `Rice Shower`. Band `Active`.

**Read:** `No turn recorded yet`; the explanation that `Energy, Mood, Fans, Skill Points and the five
stats are what this screen reads, and none has been entered for this run. Until one turn is recorded
the advisor has nothing to rank, so it declines rather than guessing.`

Stats render as `N/A / 1,325 cap` … `N/A / 1,800 cap`, with the sentence
`No build target was set for this run, so bars are drawn against this run's ceilings: the ceilings you
entered where you entered one, the scenario's own figure where you did not.`

**The caps travelled.** 1,325 / 1,322 / 1,368 / 1,308 / 1,800 — the four entered ceilings plus the
scenario's own 1800 Wit cap. This is the walk's clearest positive result: a value entered at S05
reaches the cockpit's bar, and the sentence is honest that the figure is *entered* rather than the
scenario's own.

**A panel joins the source's open question to the data.** `CapsSpeed 1,325Stamina 1,322Power
1,368Guts 1,308Wit 1,800 · Blue sparks Power ★ (Parent A), Speed ★★ (Parent B)` with:
`The blue sparks the tree carries and the caps the run states are shown together. The source records
that they do not reconcile: the blue sparks the tree carries account for the Guts extra and nothing
else, so this panel shows the inputs without claiming one derives the other.`

That sentence is the source's §1.2/@74-86 finding, carried as UI copy, and it answers **P-01**
exactly as the register asked. It is the single best instance in this walk of the app declining to
derive what the source declines to derive.

**Advisor:** `not reached`; `▲ Junior Make Debut is due and not recorded. It fell due on turn 12,
55 turns ago.`; `Missing: Energy. Record it on this run and the ranking re-runs against what you
entered.`

**Scenario panel:** `Unity Cup`, `Guide held`, `Finale — Unity Cup: not yet reached`, `Team — N/A,
not recorded`, `League — N/A`, `Bursts — N/A`, `Team rank — not recorded`.

**Source data with no UI home:** the source's team facts — Blue Bloom, motto, Team Rank S, grades
A/A/A/S/A, 55 Unity Trainings, 6 Spirit Bursts, 5 Extreme Bursts, 20/20 members (§1.8) — have no
field on this screen. The cockpit names `Team`, `League`, `Bursts` and `Team rank` and reads
`not recorded` on every one. **U-08** — and this is the largest single representation gap in the
walk, because the source's §2.4 burst-tier conclusion (11 combined bursts ⇒ gold Lv1) is built
entirely on these four numbers and none of them can be entered here.

**Findings:** **F-07** — `Junior Make Debut is due and not recorded. It fell due on turn 12, 55 turns
ago.` The cockpit computes this from the entered turn, and the computation is arithmetically right
(67 − 12 = 55) and useless as advice: the run is being imported at turn 67, so every obligation
before that turn was satisfied in the client and cannot be recorded here. The obligation reads as a
demand. **E-15** — `Finale: Unity Cup: not yet reached` repeats the word *Unity Cup* on a screen that
already states the scenario twice above.

**Action taken:** career-nav link `Training` → S10.

### S10 — Training form (`/training-runs/11/training`)

**Arrival:** 20:51:22 +0800. h1 `Pick this turn's action`.

**Read:** `Five disciplines, rest, recreation and an optional race. The advisor's recommendation is
marked; nothing is committed until you record the turn.` Ten radio actions, `name="pick"`:

`1 Speed — Feeds Power · Lv not recorded base`, `2 Stamina — Feeds Guts`, `3 Power — Feeds Stamina`,
`4 Guts — Feeds Speed and Power`, `5 Wit — no Energy · Feeds Speed`, `Rest`, `Recreation`,
`Unity Training`, `Spirit Burst`, `Race`.

Every discipline tile carries the same absence sentence:
`No facility level is recorded for this training on any turn this run has logged, so the published
per-level table has no row to read; a Level-1 figure would state a level nobody entered.` That is
**U-04** stated by the app itself, and it is the correct behaviour at turn 0.

**Fields** (all read from the live DOM): `ENERGY AFTER` `#turn-energy` 0–100; `MOOD AFTER`
`#turn-mood` (`Unchanged | GREAT | GOOD | NORMAL | BAD | AWFUL`); `FANS` `#turn-fans`; `SKILL
POINTS` `#turn-sp`; **`Turn *`** `#turn-turn` min 1, **no max, default `1`**; `Speed *`
`#turn-speed` max **1325**; `Stamina *` max **1322**; `Power *` max **1368**; `Guts *` max **1308**;
`Wit *` max **1800**; five facility-level inputs `min 1 max 5`; `#turn-outcome`
(`Choose | Success | Failure`); `#turn-energy-state` (`exact | band | unknown`, default **exact**);
`#turn-energy-band` (`low | mid | high`, default **mid**); `#turn-failure_rate` 0–100; six
`preview_gains[*]` inputs.

**The stat maxima are the run's own caps.** 1325/1322/1368/1308 are the four ceilings entered at S05
and 1800 is the scenario's. The form's `max` attributes follow. **This is the walk's second positive
result** and it closes the loop on a value typed four screens earlier.

**Values entered (source-traced):**

| Field | Value | Class | Source |
| --- | --- | --- | --- |
| `Turn *` | **67** | Derived | §2.5 derivation; §1.1 @47 |
| `Speed` / `Stamina` / `Power` / `Guts` / `Wit` | 607 / 577 / 549 / 736 / 397 | Direct | §1.2 @54 |
| `SKILL POINTS` | 173 | Direct | §1.2 @54 |
| `FANS` | 209245 | Direct | §1.1 @51 |
| `Speed level` / `Stamina` / `Power` / `Guts` / `Wit` | 4 / 4 / 4 / **5** / 4 | Direct | §1.7 @262 |
| `MOOD AFTER` | `GREAT` | Direct | §1.7 @250 |
| `Energy state` | **`Band`** | Direct | §1.7 @256-260 — "the client never prints an Energy number" |
| `Energy band` | **`Low`** | Direct | §1.7 @256 — low bar plus the injury warning |
| `ENERGY AFTER` | *(left empty)* | Omitted | no number exists to enter |
| `Turn to play` action | `1 Speed` | Direct | §1.7 @263 — the observed tile was a Speed facility turn, Lv4, `Floor Cleaning` |
| `failure_rate` | **39** | Direct | §1.7 @263 |
| `Outcome` | *(left as `Choose`)* | Omitted | the source records the tile and the failure rate, not the roll's result |
| `preview_gains[*]` | *(left empty)* | Omitted | the source states +18/+8/+6 for the client's tile, which is not a field this form binds to an action |

**P-02 answered — yes.** `Energy state: Band` + `Energy band: Low` records the source's reading
without inventing a number, and the form accepts it: `Record turn 67` was enabled with
`ENERGY AFTER` empty. The cockpit then rendered `Energy Low band`, which is the faithful translation.

**P-03 answered — partly.** The 39% has a home (`failure_rate`) but no visible label; the preview has
six homes and no labels. **E-16**.

**The race-desk detour — the walk's one real friction event.** Selecting the `Race` action
immediately navigated the browser to `/training-runs/11/races` **without committing the turn** and
**destroyed every value typed on the form**. Re-entering the form afterwards read
`{"turn":"1","speed":"","mood":"","es":"exact","sp":"","fans":""}` — the `Turn` field back at its
default `1` and all five stats, the SP, the fans, the mood and both energy selects reset. Re-entered
in full as **I-01**. Recorded as **F-08**, and it is the highest-severity finding of the walk: the
loss is silent, instant, and takes 21 controls to repair.

**Source data with no UI home:** the source's three distinct counters — goal countdown, Unity Cup
countdown, `Entry criteria met!` (§1.10 @403, §6 item 9) — are not on this form (**U-09**). The
client's per-card contribution to the tile (three avatars on the tile, §1.7) has no field (**U-10**).

**Findings:** **F-08** (above). **E-16** — `failure_rate` and the six `preview_gains[*]` inputs render
with no `<label>`, no `id`-bound label and no visible bucket heading; they are seven bare number
boxes at the bottom of the form. **E-17** — `Energy state` defaults to `Exact` while `ENERGY AFTER`
is empty, and `Energy band` defaults to `Mid`; both defaults assert a reading the Trainer has not
taken. **F-09** — the action labelled `Undo turn 1` is present and enabled on a run with no turn
recorded, and its label does not follow the `Turn` field: after typing 67 it still read
`Undo turn 1` while the primary read `Record turn 67`.

**Action taken:** `Record turn 67`.

### S11 — Races (`/training-runs/11/races`)

**Arrival:** 20:53:07 +0800, from S10's own `Race` radio — **before** the turn was committed. h1
`Enter a race, or skip the turn`.

**Read:** `Races pay fans, Skill Points and stat gains; a training turn usually pays more stats. Pick
the race or close this screen.` Under `Available this turn`:

> `No turn has been logged yet, so there is no turn to decide about. Record the first turn through
> Training, then this screen opens on the next one.`

**That sentence was correct at this point and is withdrawn as a finding.** The walk reached this
screen from the `Race` action while the form was still unsaved and the turn was still uncommitted, so
"no turn has been logged yet" described the run accurately. A later cold navigation to the same route
**after** the commit (20:59 +0800) renders the calendar's race rows normally — `Brazil Cup`, `Fuji
Stakes` G2, `Lumiere Autumn Dash` and the rest, each with grade, surface, distance, aptitude, fans
needed and SP. There is no defect here, and the finding drafted from the earlier visit is withdrawn.

**Also read at 20:53:** `Consecutive races — Races in a row raise the failure risk. Trackblazer
publishes the odds; URA Finale and Unity Cup do not, so this screen states the count and not a
probability.` with `Races in a row so far — not recorded`, and a footnote stating that inventing a
probability would be "the 'score' the app is forbidden to add". Controls: `No race chosen`, `Undo turn
1`, `Train instead`, `Enter a race`, `Race not on the calendar`.

**This is also where the unsaved form values died.** The `Race` radio navigates rather than opening a
sub-form, so the 21 values typed at S10 were gone on return: the form re-read
`{"turn":"1","speed":"","mood":"","es":"exact","sp":"","fans":""}`. That is **F-08**, and it stands.

**Source data with no UI home:** the source's actual race card for this turn — **Kyoto Daishoten, G2,
Kyoto Turf 2400m, Soft**, with 6,700 fans on a win and `Full Gate 18` (§1.6 @228-235) — cannot be
identified on the post-commit race list either: the list at turn 67 shows `Fixtures`-style rows
(`Brazil Cup` 2,100 m Dirt, `Cassiopeia Stakes` 1,800 m Turf, `Fuji Stakes` G2 1,600 m Turf,
`Lumiere Autumn Dash` 1,000 m Turf) and **no Kyoto Daishoten**, because the calendar the app reads is
its own imported race catalogue rather than the source's client board. `Enter a race` also exists and
was not exercised: its form asks for fields the source does not fill. Recorded as **U-11** and left
**untested**, per §7's boundary.

**Findings:** **F-08** (the unsaved-value loss, recorded in full at S10). No other finding; the
`No turn has been logged yet` sentence is **not** a finding and is withdrawn.

**Action taken:** `Back to cockpit`, then career-nav link `Training` to re-enter S10 (I-01), re-entered
all 21 values, then `Record turn 67` → S12.

### S12 — Cockpit after the commit (`/training-runs/11/cockpit`)

**Arrival:** 20:55:35 +0800. Band `Turn 67 logged · Active`. Flash title
`Rice Shower · Unity Cup #11 · Cockpit`.

**The position line, verbatim:**

> `Senior Year · Early October · Turn 67 of 72. Energy Low band, Mood GREAT ↑ +20%`

**Read:** `Speed 607 / 1,325 cap`, `Stamina 577 / 1,322 cap`, `Power 549 / 1,368 cap`,
`Guts 736 / 1,308 cap`, `Wit 397 / 1,800 cap`; `ENERGY Low band`; `MOOD GREAT ↑ +20%`; `FANS
209,245`; `SKILL POINTS 173`; `This run is at GREAT: training +20%, running +4%.`

**This is the walk's third and strongest positive result, and it answers P-22.** The wizard carried a
**mid-career** position: `Turn 67 of 72` with the calendar label `Senior Year · Early October`
derived from it, matching the source's own §2.5 derivation exactly. Walk 6 could not do this because
it accepted the form's default `Turn = 1`; walk 7 discovered the field accepts 67; this walk
reproduces that result on a fresh tree and adds the caps and facility levels.

**Scenario panel:** `Finale — 5 turns away`. That is 72 − 67 = 5, computed from the entered turn —
**P-10 answered**, and it agrees with the client's own "5 turn(s) until the Unity Cup" (§1.1) rather
than with the source's §8.7 calendar-arithmetic worry. The app does not attempt to explain the
discrepancy and does not need to. `Team — N/A, not recorded`, `League — N/A`, `Bursts — N/A`,
`Team rank — not recorded` persist.

**Advisor now reached:** `Recommended action — Rest`; `Energy is recorded as a low band, below the 50
advisory line; Rest is advised.` This is the source's §5 step 1 conclusion, reached by the app from
the band the Trainer entered rather than from a number the source does not have.

**Findings:** **F-10** — `▲ Junior Make Debut is due and not recorded. It fell due on turn 12,
55 turns ago.` still stands, and it now sits directly beside a `Recommended action: Rest` that
contradicts it in emphasis: one panel says the run is 55 turns overdue on an obligation, the other
says rest. The obligation cannot be satisfied by any action available on this screen.

**Action taken:** career-nav link `Races` → S13's predecessor route, then `Skills`. (The sidebar
`Skills` was clicked by mistake first; see the mis-click note in §4.)

### S13 — Skills planner (`/training-runs/11/skills`)

**Arrival:** 20:57 +0800. h1 `Spend 173 Skill Points`.

**The h1 is the source's own number.** `Skill Point budget` renders `SKILL POINTS 173`,
`PLANNED SPEND 0`, `REMAINING 173`. The lede states the hint ladder in the client's own words:
`A hint lowers a skill's cost; the ladder is 10% / 20% / 30% / 35% / 40% off. A hint you have not
earned still buys the skill at full price.`

**A `Why the hint ladder is not linear` table** reproduces `Hint Lvl 1` 10% … `Hint Lvl Max` 40%
with the client captions, and its note reconciles the source's arithmetic verbatim:
`130 → 104 / 91 / 84, and 160 → 144 / 128 / 112 / 96. The one rounding case is 130 × 0.65 = 84.5,
floored to 84.` **P-05 and P-15 answered**: the ladder is represented at the exact figures the source
read off the client (§2.1 @492-506), and no meaning is invented for a row tint.

**The picker is inside a collapsed disclosure.** `Build target, recorded skills and the add form` is
a closed `<details>`. With it closed the search input exists in the DOM at `top: 2376` on a document
whose `scrollHeight` is `1202` — **274 px below the bottom of the scrollable area**, so it cannot be
reached or focused. Opening the disclosure raised `scrollHeight` to `2735` and the field became
reachable at `top: 776` after scrolling. **This is finding F-12** and it is the second-highest
severity of the walk: a search field that is present, focusable in principle and impossible to reach
until a `summary` elsewhere on the page is clicked. Every attempt to type into it while closed
produced no keystrokes and no error.

**Values entered (all Direct, source §2.3 @563-568):**

| Field | Value | Class | Source |
| --- | --- | --- | --- |
| Skill picker (in the disclosure) | `Pace Chaser Straightaways ◎` | Direct | §2.3 step 1 — "Cheapest style-matched velocity on the board" |
| `Add to run` | pressed | UI-required | — |

**The filtering worked correctly once reachable.** Typing `Pace Chaser Straightaways` narrowed 623
options to exactly two: `Pace Chaser Straightaways ○` and `Pace Chaser Straightaways ◎`. The `◎`
variant was clicked and added.

**What the plan then showed:** `SKILL — Pace Chaser Straightaways ◎` / `FAMILY Standard` /
`BASE 140` / `DISCOUNT N/A` / `COST 140` / `Suggested`. **`PLANNED SPEND` stayed `0` and `REMAINING`
stayed `173` after the skill was added**, so the budget bar did not count the 140 SP the row beside
it costs. A re-read two seconds later, after the write settled, still read `0`. **This is finding
F-13**, and it is the one that makes the planner's headline unusable: the screen is named
`Spend 173 Skill Points` and its budget bar cannot see the plans it is holding.

**Two more representation facts, both correct.** The `COST` column is computed with a stated
`DISCOUNT` of `N/A`, not the source's 91 — the source's 91 is the price **as the client printed it
under a Lv4 hint**, and this run has recorded no hint level, so the tool charges `BASE 140` instead
of inventing the discount. That is the correct call under the no-invented-figures rule, and it means
**P-05 is answered as "the planner records no hint level, so every plan is priced at base"** — there
is no hint-level control anywhere on the screen (`style="HINTS"` offers only `Any`). Recorded as
**U-12**.

The `Acquired / skipped` table appeared for the added skill with `Current status Suggested` and a
`Change to Starting | Acquired | Skipped` select plus a `Turn acquired` number, which is a real
per-run fact the source's §1.3 skill list could populate later. Not exercised: the source does not
say when each skill was acquired.

**Source data with no UI home:** the source's **owned ten skills plus Corner Recovery ○** (§1.3) are
not recorded by this walk — the source lists them but the screen requires a per-skill acquired turn
the source does not carry, and no bulk import exists (**U-13**). The source's 33 priced Learn rows
(§1.13 @427-461) are not importable; only names can be searched (**U-14**).

**Action taken:** `Back to cockpit`, then career-nav `Events` → S14.

### S14 — Events (`/training-runs/11/events`)

**Arrival:** 20:57 +0800. h1 `A training event is asking`.

**Read:** `Pick one option. The event resolves immediately and cannot be re-run; an Alarm Clock
retries a lost goal race, not an event.` A `What the event shows` block with `Name the event as the
client shows it` and `Source not set`, plus a scope statement:
`Event text is the client's own copy. This tool does not transcribe or paraphrase it; the fields
below record the words the client shows, so the screen never invents event copy.`

**Fields:** `choice_label`; `turn` (default **68**, required); `#event_type` (select, empty); 
`source_name` (required); `support_card_name`; `bond_delta`; `origin_note`. The `Recorded outcome`
block prints `ENERGY Not recorded / MOOD Not recorded / SKILL POINTS Not recorded / STAT CHANGED Not
recorded` with: `This build does not record an event's Energy, Mood, Skill-Point or stat delta, so
those four state "not recorded" rather than a zero.`

**Provenance panel:** `Source Client screen / Effect table Not published / Retryable No: Alarm Clock
covers goal races only`.

**Nothing was entered.** The source's §6 item 6 records that the in-career `Log` reads
`There is no log.` in every one of its 67 frames (§1.14 @466), so the source has **no event text, no
option wording and no outcome** for this turn. Entering anything would be invention. **P-11 answered
negatively and honestly**: the run can record events, and the source supplies none to record.
Recorded as **U-15** and left **untested** rather than fabricated.

**Findings:** **E-18** — the `turn` field defaults to `68` on a run whose last recorded turn is 67.
That is the next turn and therefore plausible, but it is a default asserted before the Trainer has
said the event happened on it. **E-19** — `source_name` is marked required while the whole screen
exists to record a source the Trainer is reading off the client; the required-ness is correct and the
label `Where this event came from (required to record)` sits far below the field it describes.

**Action taken:** career-nav link `Inheritance` → S15.

### S15 — Inheritance (`/training-runs/11/inheritance`)

**Arrival:** 20:57 +0800 (flash band unchanged, no write). h1 `Inheritance`.

**Read:** the six-node tree drawn as a node graph —
`GRANDPARENT Daiwa Scarlet [Peak Blue] recorded ancestor · B+` / `GRANDPARENT Seiun Sky [Reeling in
the Big One] recorded ancestor · A` / `PARENT A Vodka [Wild Top Gear] Unity Cup` / `TRAINEE Rice
Shower Unity Cup` / `GRANDPARENT Daiwa Scarlet [Peak Blue] recorded ancestor · S+` / `GRANDPARENT
Taiki Shuttle [Wild Frontier] recorded ancestor · UG` / `PARENT B Maruzensky [Formula R] Unity Cup`,
with `COMPATIBILITY Not recorded — raises trigger chance and payout`.

**This is the walk's fourth positive result and it answers P-16 end to end.** Both `[Peak Blue]`
Daiwa Scarlet nodes are drawn separately in one graph, one at `B+` and one at `S+`, with no merge and
no complaint. The wizard's per-row model carried into the run's read-back correctly.

**Sparks:** `Parent A · Vodka [Wild Top Gear] — Rank B+ · Unity Cup` with `SPARK Blue · Power ★1
Moves Power recorded`; `Parent B · Maruzensky [Formula R] — Guest · Rank UG · Unity Cup` with
`SPARK Blue · Speed ★2 Moves Speed recorded`. Both sparks survived S06's combobox recovery, and the
`Guest` flag on Parent B survived too.

**The `Result of this pick` table** prints five rows — `Speed cap (entered) 1325`, `Stamina cap
(entered) 1322`, `Power cap (entered) 1368`, `Guts cap (entered) 1308`, `Wit cap (scenario) 1800` —
each with `CHANGE — N/A · this tool computes no inheritance change` and `AFTER` equal to `BEFORE`.
**P-01 answered again, more precisely than at S09**: the table distinguishes *entered* caps from the
*scenario* cap in one column, and declines the derivation twice over (in the `CHANGE` column and in
the note below it).

**Findings:** **E-20** — `Rank B+ · · Unity Cup` prints an empty segment between two separators on
both parent lines, because the source's ranks are letters and the `Rank (stars)` field was left
empty. The double separator is the only trace of the missing value.

**Action taken:** career-nav link `Deck` → S16.

### S16 — Deck, run-scoped (`/training-runs/11/deck`)

**Arrival:** 20:57 +0800. h1 `The six slots`.

**Read:** `A deck is six cards. Position six is the friend slot, and that role belongs to the
position: any card may sit in it.` All six slots with their cards, `Card effects (N)` disclosures,
`Level`, `Ownership`, `Owned`/`Rented`, `Replace`, `Remove`. Status `6 of 6 slots filled`. The
ownership note: `The owned or rented flag is saved with the deck, one value per slot, and stays on
the run after a reload. A slot you have not classified reads as N/A rather than as Owned.`

**Verified:** `Slot 4 — Haru Urara [Urara's Day Off!] · Scenario Link · Owned=true`,
`Slot 6 · Friends — Super Creek [Piece of Mind] · Owned=false · Rented=true`. The source's borrowed
card is on the run as a rented friends-slot card, correctly, and this is the **first evidence in the
walk that a setup-draft flag reached the run row**.

**`Level` still has no writer and no accessible name.** Six unlabelled `input[type=number]` elements
with no `id` and no `<label for>`, one per slot card. The source's per-card client levels (35/30/30/
50/30/35, §1.4) have nowhere to go. **U-06** confirmed on the run surface, not just the wizard.

**Findings:** **E-13** repeated, now on the run surface. **E-21** — the page states
`Saving replaces the whole deck: slots left empty are cleared` under a `Save deck` primary, while the
six slot cards each already carry their own immediate writers (`Remove`, `Replace`, `Owned`,
`Rented`). Two write models on one screen with no demarcation.

**Action taken:** career-nav link `Timeline` → S17.

### S17 — Timeline (`/training-runs/11/timeline`)

**Arrival:** 20:57 +0800. h1 `The record`.

**Counters:** `TURNS RECORDED 1`, `RACES ENTERED 0`, `EVENTS 0`, `FINALE Not reached`. Filters
`KIND All entries`, `YEAR All years | Senior`, `Clear`.

**The one row:** `67 | Senior · Early Oct | Turn | Turn recorded · Recorded as entered Edit turn 67 |
Low band | 173`.

That row is the whole run in one line, and every cell is a value the walk actually entered: turn 67,
the calendar label derived from it, the action kind, the band, and the SP. **It answers P-19**: the
row labels the column `TURN` and the position column separately, so a `67` in the turn column is not
mistaken for a count of sessions.

**Phase grouping:** `Junior Year · 0 entries` (with `Turns 1-24. Duration unknown for most entries;
the Debut race is the one dated obligation.`), `Classic Year · 0 entries`, `Senior Year · 1 entries`,
`After the grid · finale block (0)`.

**Findings:** **E-22** — `Senior Year · 1 entries` prints a plural for a count of one. **E-23** — the
`Junior Year · 0 entries` note states the Debut race is "the one dated obligation", and the cockpit
at S09 and S12 state the same obligation is 55 turns overdue; the two screens describe one fact in
two registers with no cross-reference.

**Action taken:** career-nav link `Result` → S18.

### S18 — Result (`/training-runs/11/result`) — walk end

**Arrival:** 20:58 +0800. The screen states its own gate:
`…reen reads a run whose status is Completed; until then the Cockpit is where the next turn is
decided and where the status changes.` with `Back to the Cockpit` and `Career Timeline`.

**Nothing else is on it.** This is not a dead end and not an error: the source's run is Active at turn
67 of 72 with five turns remaining (§1.1), so no result exists to read. Changing the status to
`Completed` to populate this screen would fabricate the completion the source explicitly does not
have (§11's lifecycle note, and this brief's §4.5 prohibition on "fabricat[ing] a completed career").

**Stopping condition, recorded as a decision not an obstacle:** the walk stops here because the
source's lifecycle is mid-career and the only remaining screen requires the source's own state to
change. The nine career-nav destinations have all been visited; the run is Active at turn 67 of 72.

**Source data with no UI home:** the source's end-of-run material — the completion report (§9.1), the
career-flow stage table (§10) and the revision table (§11) — is material *about* the document, not
about a completed run, and has no bearing on this screen. No unrepresented datum is filed for it.

## 6. Improvisation log

| ID | Where | What happened | Why it was necessary | What it cost |
| --- | --- | --- | --- | --- |
| I-01 | S10 | the `Race` action's detour through the race desk destroyed 21 entered values; the form was re-entered once in full | the values were lost by the app, not by a wrong entry | one re-entry; no screen revisited |
| I-02 | S06 | the `Leave empty` button cleared Parent A's name and rank after they were entered; the slot was re-entered once | a mis-click on a real control | one re-entry within the same visit |
| I-03 | S06 | the spark `Target` combobox discards typed text; the value was set by clicking the option list instead | typing alone does not commit, and the first save had already stored `target: null` | one extra save; the corrected value is what the run holds |
| I-04 | S07 | the deck was first built with Super Creek in slot 4 and Haru Urara in slot 6; both were replaced with the source's actual order | a wrong initial read of §1.4 | two `Replace` operations within the same visit |
| I-05 | S07 | the first `Replace in Slot 6 · Friends` reported `no equip` because the picker was still filtered to Haru Urara; it was re-opened and Super Creek was equipped | the picker kept the previous filter | one extra picker open |
| I-06 | S05 | the source's in-force caps were entered in the **ceiling** fields, which the page documents as goals | no field exists that records "the caps in force"; the alternative was to omit the source's caps entirely | the column's meaning shifts from goal to recorded cap; flagged as E-07 |

No improvisation entered a value the source does not carry. Every number typed is quoted with its
source anchor in §5 except the two `UI-required` flags (`Not in my library` for a parent, `Add to
run`).

## 7. Findings register

### Functional — F

| ID | Screen | Evidence | Finding | Severity |
| --- | --- | --- | --- | --- |
| **F-01** | S02 | `S02b`, `S02c` | `Choose a scenario to continue.` remains under the primary after Unity Cup is selected, telling the Trainer to do what they have just done | 2 |
| **F-02** | S03 | `S03b` | The searched roster prints `1 trainee is catalogued` where the unfiltered roster printed `66`; the count follows the filter and the sentence does not say so | 2 |
| **F-03** | S06 | `S06b2` | The spark `Target` combobox accepts and displays typed text without committing it; a save stores `target: null` with no error, no warning and `aria-invalid=false` | 3 |
| **F-04** | S05 | `S05b` | `Sum of the entered ceilings` reads `No ceilings entered` while four ceilings are in the fields (reads `5323` only after a reload) | 2 |
| **F-05** | S07 | `S07d` | `Deck 5 of 6 slots filled` above a paragraph that still reads `Not equipped. The six cards are the deck…`; the count and the sentence disagree about whether the deck is built | 1 |
| **F-06** | S08 | `S08`, `S08b` | `DISTANCE · N/A — The target step has not been entered` on a draft whose target step **was** entered and saved, because a partial payload parses to `null` and one sentence is printed for both causes; the run is then created with no build target | 3 |
| **F-07** | S09, S12 | `S09`, `S12-cockpit-after-turn67` | `Junior Make Debut is due and not recorded. It fell due on turn 12, 55 turns ago` on a run imported at turn 67; the obligation cites a turn that is 55 turns in the past and cannot be satisfied by any action on the screen | 2 |
| **F-08** | S10 | `S10c` | Selecting the `Race` action navigates away from the form and destroys every unsaved value (21 controls), with no warning and no draft recovery | 3 |
| **F-09** | S10 | `S10-training-form` | `Undo turn 1` is present and enabled on a run with no turn recorded, and its label does not track the `Turn` field (still `Undo turn 1` when the primary reads `Record turn 67`) | 2 |
| **F-10** | S12 | `S12-cockpit-after-turn67` | The cockpit simultaneously reports a 55-turn-overdue obligation and recommends `Rest`; the obligation cannot be acted on and the recommendation does not mention it | 2 |
| **F-11** | S12 | `S12-cockpit-after-turn67` | **Withdrawn, 2026-11-11, by the walk itself.** The `No turn has been logged yet` sentence at S11 was correct: the walk visited the race desk from the `Race` radio **before** committing the turn. A post-commit cold navigation renders the race rows normally. The original claim is preserved in §5's S11 section with this correction, not deleted | — |
| **F-12** | S13 | `S13-run-skills-planner`, `S13b` | The skill search field lives inside a collapsed `<details>` and sits 274 px below the document's scrollable area; it cannot be focused or typed into until a `summary` elsewhere is clicked. No error, no indication | 3 |
| **F-13** | S13 | `S13c` | `PLANNED SPEND` stays `0` and `REMAINING` stays `173` after a 140 SP skill is added; the screen is titled `Spend 173 Skill Points` and its budget bar cannot see its own plans. **Reconciliation note, 2026-11-12: REFUTED — the original observation is preserved above, but the action behind it is not the one this sentence names.** The walk pressed `Add to run` (its own values table at §S13 marks it `UI-required`), which submits the skill as **acquired** and clears the plan list on success; the budget bar correctly reads `0` for an acquired skill. The plan checkbox is the control that feeds the bar, and measured on `3aee95e2` at this same skill (`Pace Chaser Straightaways ◎`, cost 140) it moves planned spend `0 → 140 → 0` and remaining `173 → 33 → 173`. No product defect: the bar sees its plans. Corrected evidence in `AUDIT-AND-VERIFICATION.md` section `## verification-rtx-ux09-f13-2026-11-12.md` §B.4 (folded 2026-11-12, Round 26), disposition in `docs/audits/RECONCILIATION-REGISTER.md` §11.2. The regression gap the finding exposed is real and was closed by `b2832ee6` | 3 |

**Blocking findings.** F-12 and F-08 each stopped forward progress until worked around. F-03 and F-06
each silently produced a wrong or missing stored value (a spark with `target: null`; a run with no
build target) while the UI reported success. Those four are the walk's blocking set. **F-11 was
withdrawn** by the walk's own re-measurement and is not counted.

### Experiential — E

| ID | Screen | Evidence | Finding | Severity |
| --- | --- | --- | --- | --- |
| **E-01** | S01 | `S01` | `Start a career`, `New career` and `New Career` are three labels for one route; nothing marks the wizard as primary | 2 |
| **E-02** | S13 | `S13a` | The sidebar `Skills` (`/skills`, global library) and the career-nav `Skills` (`/training-runs/11/skills`) carry the identical accessible name on the same page; the wrong one was clicked first | 3 |
| **E-03** | S01 | `S01` | `Import an existing career` and `Import a historical career` differ by one word and neither states what distinguishes them | 2 |
| **E-04** | S02 | `S02` | The four scenario radios are `required=false`; the only cue that selection is compulsory is the greying of the primary, which is not `aria-describedby`-bound to the helper | 2 |
| **E-05** | S03 | `S03c` | The `Form` select displays `[Rosy Dreams]` as selected while `selectedCard` is `null`; a form appears chosen that the draft has not stored, and the run is created with no costume form | 3 |
| **E-06** | S04 | `S04` | The undated costume form renders an empty rarity cell where the other two print `★★★` | 1 |
| **E-07** | S05 | `S05b` | The only field able to hold the run's real caps is documented as a goal column; recording a cap and setting a goal are two different facts sharing one control | 3 |
| **E-08** | S05 | `S05` | `This is the step the current build skips.` sits above a step the wizard will not pass without | 2 |
| **E-09** | S05 | `S05` | `Wit` has no ceiling input while the other four do, so the run's 1800 Wit cap can never be stated as entered | 2 |
| **E-10** | S06 | `S06b2` | `Add Spark` buttons carry an empty caption slot; the accessible names read `Add Spark  to Grandparent A1` with a double space | 1 |
| **E-11** | S06 | `S06e`, `S06f` | `Projected start` prints `-` in both parent columns for a stat the parent's blue spark names, while the parent cards beside it print `Blue spark · ★1`; the table does not read the stored sparks | 2 |
| **E-12** | S07 | `S07c` | The `Name contains` field requires an explicit `Filter` press; a search-shaped input that does not filter is not what its shape implies | 2 |
| **E-13** | S07, S16 | `S07`, `S16-run-deck` | The per-slot `Level` input has no `id`, no `<label for>` and no accessible name, on both the wizard and the run | 2 |
| **E-14** | S08 | `S08b` | `1 step needs attention` counts a `Missing` row, and the same page lets the run be created anyway; nothing distinguishes "blocks" from "records as absent" | 2 |
| **E-15** | S09 | `S09` | `Finale: Unity Cup: not yet reached` repeats the scenario name on a screen that has already stated it twice | 1 |
| **E-16** | S10 | `S10-training-form` | `failure_rate` and six `preview_gains[*]` inputs render with no label and no bucket heading; seven bare number boxes at the form's foot | 2 |
| **E-17** | S10 | `S10` | `Energy state` defaults to `Exact` beside an empty `ENERGY AFTER`, and `Energy band` defaults to `Mid`; both assert a reading nobody took | 2 |
| **E-18** | S14 | `S14-events` | The `turn` field defaults to `68` before the Trainer has said when the event happened | 1 |
| **E-19** | S14 | `S14-events` | `Where this event came from (required to record)` sits far below the `source_name` field it describes | 1 |
| **E-20** | S15 | `S15-inheritance` | `Rank B+ · · Unity Cup` prints an empty segment between two separators on both parent lines | 1 |
| **E-21** | S16 | `S16-run-deck` | `Save deck` ("replaces the whole deck") and six per-slot immediate writers coexist with no demarcation of which is authoritative | 2 |
| **E-22** | S17 | `S17-timeline` | `Senior Year · 1 entries` prints a plural for a count of one | 1 |
| **E-23** | S17 | `S17-timeline` | The Debut-race obligation is described in two registers on two screens with no cross-reference | 1 |

### Unrepresented data — U

| ID | Screen | Evidence | Source data | What the absence costs |
| --- | --- | --- | --- | --- |
| **U-01** | S02 | `S02` | Scenario full label `Unity Cup: Shine On, Team Spirit!` (§1.1 @49) | the run cannot record which scenario set the frames were taken under beyond the short key |
| **U-02** | S02 | `S02` | Rank-to-level ladder, burst bands, league bonus, cap source (§1.8, §2.4, §2.6) | the step that claims to describe "which systems exist" names no system's rules |
| **U-03** | S03, S04, S08 | `S03c`, `S04`, `S08` | Potential Lvl 2 (§1.1 @50) | the trainee's awakening level is in the source, has no field anywhere in the wizard, and no column on the run |
| **U-04** | S10 | `S10-training-form` | Facility levels 4/4/4/5/4 (§1.7 @262) | the run **can** hold them and does — but only from the second turn on; on turn 0 the tiles print their own absence sentence while the five level inputs sit unread |
| **U-05** | S06 | `S06e` | White, pink and green sparks for six ancestors (§1.5 @188-195) | only the two blue sparks can be recorded in one pass; the source's own cap arithmetic uses only those two, so nothing the run reads is lost |
| **U-06** | S07, S16 | `S07`, `S16-run-deck` | Per-card client levels 35/30/30/50/30/35 (§1.4 @119-126) | the `Level` input exists on both surfaces and was never writable in this walk |
| **U-07** | S07 | `S07d` | Per-card effect sets and hint pools (§1.4 @139-159) | read-only in `Card effects (N)`; the client column ("FB 16, Mood 45…") cannot be recorded |
| **U-08** | S09, S12 | `S09`, `S12-cockpit-after-turn67` | Team Blue Bloom, motto, Rank S, grades A/A/A/S/A, 55 Unity Trainings, 6+5 bursts, 20/20 members (§1.8) | **the largest gap.** Every one of the four cockpit tiles reads `not recorded`, and the source's §2.4 gold-tier conclusion is built entirely on them |
| **U-09** | S10 | `S10-training-form` | Three distinct counters: goal countdown, Unity Cup countdown, `Entry criteria met!` (§1.10 @403) | none is on the form; the cockpit reconstructs one of them (`Finale 5 turns away`) |
| **U-10** | S10 | `S10-training-form` | Per-tile card avatars and the rainbow multiplier (§1.7, deck hint) | the tile's support-card composition at this turn is unrecordable |
| **U-11** | S11 | `S11-races` | Kyoto Daishoten G2 2400m Turf Soft, 6,700 fans, Full Gate 18 (§1.6 @228-235) | **untested**, not merely absent: `Enter a race` was not exercised because the source fills none of its fields |
| **U-12** | S13 | `S13-run-skills-planner` | The hint level behind each printed price (§2.1 @494-502, §1.13 @423) | no hint-level control exists; every plan is priced at `BASE` and the source's 91 for Pace Chaser Straightaways ◎ is unreachable |
| **U-13** | S13 | `S13-run-skills-planner` | The ten owned skills plus Corner Recovery ○ (§1.3 @105-108) | the run records no owned skills; the table needs an acquired turn the source does not carry |
| **U-14** | S13 | `S13-run-skills-planner` | 33 printed Learn prices (§1.13 @427-461) | not importable; only names are searchable |
| **U-15** | S14 | `S14-events` | Event text, options, outcomes (§6 item 6 @942-944 — the Log reads "There is no log" in every frame) | **untested by design**: nothing was entered because the source has nothing to enter |

## 8. Validation and probing boundaries

**No optional stability probe was performed.** The primary pass produced the two blocking findings it
needed (F-08, F-12) without any probe, and every candidate probe failed §7's conditions:

| Probe considered | Why it was not run |
| --- | --- |
| Change run #11's status to `Completed` to populate S18 | creates the completion the source does not have — prohibited |
| Exercise `Enter a race` on S12 | requires fields the source does not fill; would be an improvisation with no source behind it |
| Enter an event on S14 | the source has no event text, option or outcome |
| Record the ten owned skills on S13 | requires an acquired turn per skill the source does not carry |
| `Remove` a deck slot to see the deck rebuild | destructive to a run that took 30 minutes to build, and F-05 was already observed |
| Re-run the walk to see whether the race-desk sentence changes after a commit | **was** run, as a read-only navigation at 20:59 after the commit; it withdrew F-11 |

## 9. Audit quality gates

### 9.1 Screen completeness

Every screen in §4's ledger appears in §5 with its own section, and every §5 section appears in the
ledger: S01–S18, contiguous, in visit order. The single mis-click (`S13a-library-skills-misclick.png`)
is recorded in §4 as **not** a screen of the walk and appears in no ledger row and no section.

### 9.2 Value completeness

Every value entered through a control is in §5 with an exact value, the visible label, and either a
source anchor or an explicit class. Reconciliations that matter:

- 21 controls were filled at S10; all 21 are in the S10 table.
- 14 fields were filled at S06 (2 names, 2 rank letters, 1 guest flag, 4 grandparent names, 4
  grandparent rank letters, 2 spark targets, 2 spark stars); all are in the S06 table.
- 4 ceilings at S05, 6 deck cards plus 1 ownership flag at S07, 1 skill at S13, 1 checkbox at S08.
- Two `CORROBORATED` source reads are labelled with file and line: `LegacySelect.vue` (repeated
  ancestor names) and `SparkTargetPicker.vue:139-146` (why typed text is discarded), plus 
  `TraineeSelect.vue:426-432` and `PreflightController.php:206-218` for F-06/E-05. No source read is
  presented as browser evidence.

### 9.3 Finding completeness

Every finding named in the closing assessment is a row in §7, and every §7 row is a detailed
observation in §5. Every `U` row is in §7's unrepresented-data inventory.

**Finding accounting, stated three ways so no record can be read two ways:**

| Measure | Count |
| --- | --- |
| Finding records in the register (F + E + U rows) | **51** |
| Active findings | **50** |
| Withdrawn findings | **1** (F-11) |
| Active functional (F) | **12** |
| Functional records incl. the withdrawn row | 13 |
| Experiential (E) | **23** |
| Unrepresented data (U) | **15** |

**F-11 is counted in the 51 records and excluded from the 50 active.** It is not deleted, not
renumbered and not reused: its register row states the withdrawal and points back to §5's S11
section, where the original claim stands with the correction beside it. A later reader comparing
audits will find 51 rows and 50 live findings, and both numbers are correct.

### 9.3a Interaction evidence and its limits

Screenshots prove what a screen **looked like**; they cannot by themselves prove the sequence that
produced it. Four specific claims in this audit therefore rest on a recorded action rather than on a
still, and each is named here with what would settle any doubt:

| Claim | How it was established | Limit |
| --- | --- | --- |
| **F-08** — the `Race` radio navigated away and destroyed 21 values | the URL changed to `/training-runs/11/races` in the same call as the click, and the re-entered form re-read `{"turn":"1","speed":"","mood":"","es":"exact","sp":"","fans":""}` | the intermediate DOM was not snapshotted between the click and the navigation, so "destroyed" is established by the **before** and **after** reads, not by a capture of the loss itself |
| **F-12** — the search field is unreachable while its `<details>` is closed | measured `top: 2376` against `documentElement.scrollHeight: 1202` and `innerHeight: 900`, then `scrollHeight: 2735` after opening | the measurement is a geometry reading, not a video; a screenshot of the closed state and of the open state are both on disk (`S13-run-skills-planner.png`, `S13b-planner-form-open.png`) |
| **F-03** — typed text did not commit | the row still read `Target: Not recorded` after a save, and the stored payload carried `"target": null` | the typed state itself was not screenshotted before the save |
| **E-02** — two same-labelled `Skills` links | the hrefs were read from the DOM in one call: sidebar `/skills`, career nav `/training-runs/11/skills` | the mis-click capture is the wrong screen, not a picture of the ambiguity |

**No Playwright trace was captured.** This walk drove the browser through CDP directly and did not
enable tracing, so there is no `trace.zip`, no before/after DOM snapshot pair and no action log with
timings. The audit is written to be readable without one — every screen has a PNG and a text read —
but a trace would have settled the F-08 intermediate state outright. Recorded as a **method limit**,
not a defect of the application. A later pass that needs that rigor should run with
`PLAYWRIGHT_TRACE=on` or the CDP `Tracing` domain enabled from the first click.

### 9.3b Combobox verification: the three states

The spark `Target` field is an editable-combobox, and the W3C ARIA combobox pattern separates three
distinct states that a single interaction can visit in sequence. This walk recorded all three, and
the **first** of them is where the initial mistake happened:

| State | What it is | What this walk observed |
| --- | --- | --- |
| 1. Text entered | keystrokes land in the input and filter the list; **nothing is committed** | typing `Power` filtered the list to one option and left the stored value unchanged — `Target` still read `Not recorded` |
| 2. Option selected | an option from the list is activated, which is what writes the value | clicking the single remaining option is what set the field; the placeholder then read `Power` |
| 3. Value accepted and persisted | the write survives a save and a reload | after the correct selection, the run's stored payload carried `"target": "Power"` and §5 records the read-back |

**The lesson, stated as a rule for the next pass:** typing into an editable combobox is *filtering*,
not *selection*. A value must be read back from the control **and** from the persisted payload before
it is recorded as entered. F-03 is the finding that came out of learning this the hard way; the
audit keeps both the mistake (I-03) and the rule.

### 9.3c Duplicate-label disambiguation

Five controls in this walk share a visible label with a different control elsewhere on the same
screen. Naming a control by its text alone is therefore unsafe here, and each was resolved by
**location, navigation group and destination** before being clicked:

| Label | Instances | Distinguished by |
| --- | --- | --- |
| `Skills` | sidebar `/skills` (global library) · career nav `/training-runs/11/skills` (run planner) | the URL each `href` carried, read from the DOM in one call |
| `Review` / `New Career` / others | sidebar nav and in-page cards | `href` values, and whether the element sits in the `PLAY`/`MY DATA`/`LIBRARY` group or in the page body |
| `Save deck` and per-slot `Remove`/`Replace` | one page, two write models | the control's own container (page footer vs slot card) |
| `Owned` / `Rented` | six pairs on one page | the enclosing slot card's position label |
| `Add Spark` | two bare parents plus four grandparents | the accessible name that names the node (`Add Spark to Grandparent A1`) |

**The mis-click is recorded rather than concealed**, and E-02 files the ambiguity as a UX finding
in its own right: where two destinations share one name, the interface is asking the Trainer to
remember which is which. It is **not** filed as a navigation defect, because once the intended
control is identified the destination is correct.

### 9.3d Expandable content discovery

A control can be present, focusable in principle, and unreachable in practice. Before recording
anything as unavailable, every legitimate disclosure path was exercised. What this walk found:

| Container | Path found | Result |
| --- | --- | --- |
| `<details>` — `Build target, recorded skills and the add form` (S13) | click its `summary` | **required**: the search field sits 274 px below the scrollable area until this opens (F-12) |
| `<details>` — `Card effects (N)` ×6 (S07, S16) | click each `summary` | opens read-only effect rows; no writer, recorded as U-07 |
| `<details>` — `Deck summary`, `Deck analysis`, `More` | click `summary` | `More` is a zero-height disclosure in the page footer; the other two are informational |
| Slot picker (S07) | `Choose a card in Slot N` | navigates to `?deck_slot=N` and renders the picker panel; **not** a modal |
| Skill picker (S13) | focus the input | opens a `role=listbox` of 623 options, filtered by typing |
| Spark target (S06) | focus the input | opens a `role=listbox`; selection is the writer (F-03) |

**F-12 is the one case where the access path exists but is practically unreachable**, and it is
recorded with the measured geometry rather than as "the field is missing". `Nested pickers` were
checked in the same pass: the deck picker's own `Filter` button is a second submit inside the picker
(E-12), and the skill planner's picker sits inside the disclosure that F-12 names.

### 9.3e Same-screen correction protocol

Two values were corrected **within the visit where they were entered**, before leaving the screen.
Both are recorded with the original attempt preserved:

| # | Screen | Original attempt | Why it was wrong | Corrected to | Verified |
| --- | --- | --- | --- | --- | --- |
| I-04 | S07 | Super Creek in slot 4, Haru Urara in slot 6 | the source places Super Creek in the friends slot (§1.4 @128-131) | Haru Urara slot 4, Super Creek slot 6 | S16 renders both correctly, with `Rented` on slot 6 |
| I-03 | S06 | typed `Power` into the spark Target | typing filters, it does not commit (§9.3b) | clicked the `Power` option | the run's payload carries `"target": "Power"` |
| I-02 | S06 | Parent A name + rank entered | a mis-click on `Leave empty` cleared them | re-entered | S08 lists `Vodka [Wild Top Gear]` |

Each correction stayed inside its own screen visit. **No completed screen was revisited to change an
earlier choice**, and the walk was not restarted. The one recovery that spanned screens (I-01) is
recorded as a forced re-entry caused by F-08, not as a permitted correction.

### 9.3f Application mutation accounting

Four kinds of change are distinguishable in this audit and they are kept apart:

| Kind | This walk |
| --- | --- |
| **Direct database modification** | **none.** No `INSERT`/`UPDATE`/`DELETE`, no `migrate`, no seeder, no factory. |
| **UI-driven application mutation** | **two rows.** `training_runs` id **11** (Active, Unity Cup, four entered ceilings) created via `Create the run`; `turn_entries` id **7** (turn 67) created via `Record turn 67`. Also one `career_setup` session draft, written through the wizard's own steps and expired with the session. |
| **Read-only inspection** | four `php artisan tinker --execute` calls (two before, two after the walk, each a `select`), `php artisan migrate:status`, `git`, `grep`, and every DOM read. |
| **Filesystem changes** | the report and 43 PNGs under `docs/audits/`; nothing else. `.env` untouched. |

**A UI write is still a database mutation.** Run #11 and turn entry 7 exist in
`database/database.sqlite` because this walk clicked two buttons, and the report's clean
`git status` does not imply otherwise. The resulting lifecycle state is **Active, mid-career, turn 67
of 72**, and it is stated in §9.4, in §10 and in §11.

### 9.4 Lifecycle integrity

- The source's actual state is preserved: **Active, mid-career, turn 67 of 72**, 5 turns remaining.
  Verified on the run row (`"status": "Active"`) and on every cockpit read.
- **No run number was invented.** The source carries none; run `#11` is the walk's own local id and
  is labelled as such wherever it appears.
- **No completion status fabricated.** S18 was left unpopulated because the run is Active.
- **No Veteran or Legacy classification introduced.** The source's parents are the source's parents;
  no local veteran was created and `veterans` is still empty.
- **No source contradiction silently resolved.** P-01, P-02, P-09, P-10, P-12, P-16, P-17, P-18 and
  P-21 are each answered in place with what the app does and what it declines to do. The two the app
  *does* inadvertently resolve are recorded as findings, not accepted: F-07 (it decides the Debut
  obligation is unmet) and F-06 (it decides a partial target was never entered).

### 9.5 Evidence integrity

Every claimed screen was visited; every claimed transition was observed in `location.href`; every
claimed accepted value was re-read from the control or from the run's own payload. Two claims deserve
their basis stated plainly:

- **Persistence is claimed for caps, deck ownership and the turn row**, and each is proven by a
  read-back in a later screen, not by the write's flash: the caps by S09/S10's stat maxima, the
  Rented flag by S16's `aria-pressed`, the turn by S17's row and the run's `turn_entries` read.
- **Nothing claims persistence for the build target**, because there is none: F-06 records that the
  run was created with `build_target: null`.

**Not run, and stated rather than implied:** no `migrate`, no seed, no test suite, no browser spec, no
linter. This is a read-and-walk audit, not a code change; §9's hand-off sequence does not apply to a
change set that contains only audit documents and screenshots.

## 10. Final repository safety check

`git status --short` after the walk:

```
 M docs/research/ui-ux-application-context-brief.md      <- peer-owned, pre-existing, untouched
?? docs/audits/RS-UC-2026-13/                            <- this walk: 43 PNGs
?? docs/audits/rice-shower-unity-cup-ux-walk-8.md         <- this walk: the report
?? docs/audits/RS-UC-SCENARIO-DETAIL-2026-11-09.md       <- pre-existing
?? docs/audits/RS-UC-SCENARIO-DETAIL-2026-11-09/         <- pre-existing
?? docs/audits/trainer-desk-v3-ux-retest/                <- pre-existing
?? docs/audits/trainer-desk-v3-ux/…                      <- pre-existing (8 paths)
?? docs/research/RS-UC-SCENARIO-DETAIL-2026-11-09/       <- pre-existing
?? docs/research/scenario-detail-design-language-survey-2026-11-09.md  <- pre-existing
```

- **Created:** `docs/audits/rice-shower-unity-cup-ux-walk-8.md` and
  `docs/audits/RS-UC-2026-13/screens/` (43 PNGs). Nothing else.
- **No application source file changed.** No file under `app/`, `resources/`, `routes/`, `config/`,
  `database/`, `tests/` or `tools/`.
- **No configuration or environment file changed.** `.env` was not read or written.
- **No source-run document and no prior audit changed.** `docs/[Rosy_Dreams]Rice_Shower_Unity-Cup.md`
  and walks 1–7 plus their reconciliations are byte-identical.
- **Database:** one row inserted, `training_runs` id **11**, and one row in `turn_entries`
  (id 7), both by the application's own UI writes. No direct SQL. `veterans` still 0 rows.
- **Unexpected changes:** none. The 15 pre-existing dirty paths are reported as found and were not
  reverted, reformatted or staged.

## 11. Report

- **Audit document:** `docs/audits/rice-shower-unity-cup-ux-walk-8.md`.
- **Trainee and scenario:** Rice Shower `[Rosy Dreams]`, Unity Cup.
- **Audit ID:** `RS-UC-2026-13`.
- **Screens actually visited:** **18** (S01–S18), plus one mis-click recorded as not a screen.
- **Final stopping point:** **S18 `/training-runs/11/result`**, which states it is unavailable while
  the run is Active.
- **Complete workflow reached:** **no, and correctly so.** The wizard, the run creation, the first
  turn, and all nine career-nav destinations were reached. The Result screen requires a `Completed`
  run and the source is mid-career at turn 67 of 72, so reaching a populated Result would require
  fabricating the completion.
- **Functional findings:** **12 active** (F-01 to F-10, F-12, F-13) plus **1 withdrawn** (F-11), out
  of **13 F records**. **Blocking: 4** (F-08 unsaved-value loss on the race detour, F-12 unreachable
  skill search, F-03 silently discarded spark target, F-06 a saved target reported as never entered
  and then dropped from the run).
- **Experiential findings:** **23**.
- **Unrepresented-data findings:** **15**.
- **Major source-specific representation gaps:** (1) **U-08**, the entire Unity Cup team state — four
  cockpit tiles read `not recorded` and the source's burst-tier conclusion rests on them; (2) **U-12**
  and **U-13**, no hint level and no owned-skill record, so the source's 91 SP price and its ten
  skills are unreachable; (3) **U-03**, the trainee's Potential level has no field anywhere; (4)
  **U-11**, the run's actual race cannot be entered without inventing its fields.
- **Improvisations:** 6 (I-01 through I-06), all listed in §6. None entered a value the source does
  not carry.
- **Optional stability probes performed:** **none**, each with its reason in §8.
- **Environment mutations:** one run created (`training_runs` id 11, Active) and one turn committed
  (`turn_entries` id 7) — both the walk's own, both by UI writes. One **pre-existing** condition was
  found and recorded, not fixed: `migrate:status` reports one Pending migration on the dev database
  (E-04 in §3.3).
- **Procedural deviations:** the race-desk detour forced one full re-entry of the training form
  (I-01); the spark combobox forced one extra save (I-03); the deck required two `Replace` operations
  after an initial mis-order (I-04) and one re-opened picker (I-05). No screen was revisited to change
  an earlier choice, and no screen was skipped.
- **Git working-tree verification:** §10 — one new directory containing one document and 43 PNGs; the
  15 pre-existing dirty paths untouched.
- **Data-entry method confirmed:** every value went through a visible control — native `<select>`,
  number and text inputs, radio and checkbox controls, `aria-pressed` buttons, and the app's own
  primaries. No seeder, factory, SQL, tinker write, API call, devtools DOM edit or request replay.

**Non-negotiable, honoured:** **F-11 was withdrawn rather than defended** once the walk's own later
observation contradicted it, and the withdrawal is recorded in place with the original claim preserved
rather than deleted; F-06 stands as a saved value the app reports as absent; U-11, U-15 and the S13
acquired-turn data are marked **untested** rather than filled; and the Result screen is left empty
because a populated one would have had to be invented.
