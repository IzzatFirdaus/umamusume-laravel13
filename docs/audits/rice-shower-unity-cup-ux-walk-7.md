# Rice Shower / Unity Cup — UX walk 7 (wizard door, `RS-UC-2026-12`)

Status: **walk complete.** Primary pass through the wizard door, 18 screens, one session.

> **Alternate-door companion.** `docs/audits/rice-shower-unity-cup-ux-walk-7-snapshot-door.md` (`RS-UC-2026-11`)
> records the same run through the snapshot import. This document is the canonical walk 7: the wizard door,
> the primary path a new user takes and the door walk 6 also used.

## 7.1 Header

- **Audit name / ID:** Rice Shower Unity Cup UX walk 7, wizard door / `RS-UC-2026-12`.
- **Source document:** `docs/[Rosy_Dreams]Rice_Shower_Unity-Cup.md` (1,652 lines; §11 revision table at 1598;
  read end to end once, pre-walk).
- **Secondary reference (context, not evidence):** `docs/UMAMUSUME_REFERENCE.md`.
- **Starting URL:** `http://127.0.0.1:8000/` (HTTP 200; server already running, not started by this walk).
- **Walk start:** 2026-11-07 16:31:00 +0800. **Walk end:** 2026-11-07 17:35:00 +0800. Duration ~1h 04m.
- **Browser / environment:** HeadlessChrome 156.0.8078.4
  (`Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) HeadlessChrome/156.0.8078.4 Safari/537.36`),
  Windows, viewport 1920×889 CSS px (devicePixelRatio 1), driven by Playwright.
  **Devtools: not opened.** Read-only `page.evaluate` and `document.body.innerText` used for DOM reads; no DOM
  writes, no request replay, no state mutation.
- **Git commit / application version:** the walk began at `3fc7c603`; HEAD moved to `8a3cf21c` during the pass
  (a peer commit, not this walk's). The application version reports `3.0.0` in the Inertia `app` prop.
- **Pre-existing run state:** the `training_runs` table was empty at walk start (a read-only query returned
  `[]`), so run #3 created here is this walk's own. Four peer-owned dirty paths untouched:
  `D docs/audits/AUDIT-SUPPORT-CARDS-UIUX-2026-11-05.md`, `D docs/proposals/PLAN_SLICES_6_7.md`,
  `M docs/research-scratch/AUDIT-AND-VERIFICATION.md`, `M docs/research-scratch/PROCESS-PLANS.md`.
- **Run identity:** Rice Shower, `[Rosy Dreams]`, ★3, Potential Lvl 2, Unity Cup, snapshot at
  **Senior Year Early Oct, turn 67 of 72, 5 turns remaining**, mid-career in progress, no run number in source.
- **Door taken:** **wizard** (canonical entry). The two alternate doors (snapshot `/career/snapshot`, historical
  file `/training-runs/import`) were visible at S01 and not taken; see §0.5 and §7.9.
- **Turn index entered:** **67** at the training form's `Turn *` field, not the default 1. See §7.5 and §7.9
  for what the application rendered in response.
- **Alternate-door evidence:** `docs/audits/rice-shower-unity-cup-ux-walk-7-snapshot-door.md` — this walk's
  companion. Read together, the two establish whether position-carrying is a door property or an application
  property.
- **No-seeding statement:** the walk was performed entirely through the browser UI. No seeder, factory, direct
  DB insert/update/delete, SQL, tinker/REPL, state-mutating artisan command, API substitution, devtools DOM edit,
  or request replay was used to enter data. The one `php artisan tinker --execute` call issued before the walk
  **read** `training_runs` only, to establish that the table was empty; it wrote nothing.
- **Primary-pass scope / stopping point:** the wizard's six setup steps, the trainee profile, the cockpit, and
  the seven remaining career-nav screens — 18 screens, forward order, no backtracking. The walk stopped after
  the Result screen, which is the last career-nav destination; the run is Active at turn 67 of 72, so no result
  exists to read.
- **Coverage statement:** the client's frames cover the **in-career** screens (`Career`, `Sparks`, `Perks`,
  `Learn`, `Log`, the in-career rail) and the five `Career Profile` panels, plus the Senior-year endgame. The
  **pre-career setup flow and turn 0 are unfilmed**, as are the preseason rounds' own screens and the finals'
  race cards. Every setup-path claim in this audit is source-transcribed or publisher-derived, **not**
  frame-observed; where this document and a frame disagree, the frame wins.
- **Contamination statement:** read as **context only, never as UI evidence**: the source document's
  tool-internal citations (`support_cards` rows 30003/30016/30018/30019/30020/30030,
  `SupportCard::isScenarioLink('unity_cup')`, `config('scenarios.php')`, `CareerTraineeSelectTest`,
  `database/seeders/data/gametora-characters.e9e9ee6d.json`, the deck table's catalogue / "Client rows visible"
  columns, and the "D8 of the UX walk remediation" / `AUDIT-AND-VERIFICATION.md` references); and this
  repository's own prior trail (walks 1–6, the round 2–6 registers, `ui-ux-application-context-brief.md`,
  `flows-*.md`, `rice-shower-unity-cup-ux-walk-resolutions.md`, `KNOWN-ISSUES.md`, `docs/framework-ui-ux/`, and
  the peer-landed `rice-shower-unity-cup-round6-reconciliation.md` /
  `rice-shower-unity-cup-round6-slice-audits.md`). Where a prior finding reproduces, this audit gives its own
  observation; where it does not, that is recorded as a finding in its own right.

### 8.5a The door verdict (wizard-specific)

**Outcome 3, unambiguously.** The wizard **can** carry the source's Senior Early October position.

1. The training form's `Turn *` field accepted `67` with no rejection, no clamp, and no validation message:
   `aria-invalid="false"`, `validity.rangeOverflow === false`, and the submit button's own label changed from
   `Record turn 1` to `Record turn 67` as the value was typed.
2. After committing, the cockpit rendered, verbatim:

   > `Senior Year · Early October · Turn 67 of 72. Energy Low band, Mood GREAT ↑ +20%`

   and the header band `Turn 67 · Active`. The same payload carries
   `careerPosition {year:3, month:10, phase:"Early", turn_index:67}`, `header.next_turn_to_play: 68`,
   `header.total_turns: 72`.
3. Every position-derived surface followed the entered turn, not a hardcoded 1: the advisor computed
   `It fell due on turn 12, 55 turns ago` (67 − 12), `Unity Cup finals is 5 turns away` (72 − 67), and the
   scenario panel `Finale 5 turns away`.

**Consequences.**

- **Walk 6's F-13 is falsified at its wizard half.** The wizard is not new-career-only. Its turn field carries
  a mid-career position and the cockpit derives from it.
- **The classification shifts from capability to default.** The remaining substance is not "the wizard cannot
  express the position" but "the wizard opens the training form at `Turn = 1` and labels the button
  `Record turn 1`, so a Trainer who does not override it silently commits a Junior-Year position". That is a
  default-value and affordance note, not a capability defect.
- **R6-13 requires the C10 dated correction** (drafted in §7.5 below).
- **The two doors agree.** Both carry the position; they differ only in vocabulary (the wizard takes a turn
  number at the training form, the snapshot door takes Year/Month/Half at setup). Position-carrying is an
  **application** property, not a door property.

## 0.5 Method: door choice and turn entry

From the landing screen the application offers three ways to bring a career into it. **The wizard
(`New career` → `/career/setup/scenario`) is the canonical entry and the primary door for this walk.** It is
the default path a new user takes; its limitations are findings, not obstacles to route around.

The other two doors are visible at S01 and were **not** the primary path:

- **Import an existing career (snapshot)** → `/career/snapshot` — already walked as the alternate door at
  `docs/audits/rice-shower-unity-cup-ux-walk-7-snapshot-door.md`. Read for contrast; not re-run.
- **Import a historical career (file)** → `/training-runs/import` — not walked; out of scope for this pass.

**The wizard's ability to carry the source's Senior Early Oct position was the walk's central question**, and
it is answered in §8.5a above. Walk 6 took the wizard and accepted the training form's default `Turn = 1`,
never trying 67; this walk overrode it.

**Sequence actually taken.** `/career/setup/scenario` → `/career/setup/trainee` → `/career/setup/trainee/30`
(profile) → `/career/setup/target` → `/career/setup/legacy` → `/career/setup/deck` → `/career/setup/preflight` →
`/training-runs/3/cockpit` → `/training-runs/3/training` (turn 67 committed) → cockpit → races → skills →
events → inheritance → deck → timeline → result.

**What walk 6 established** (context, not evidence): F-01 through F-17 at `3f295e80`, classified in
`rice-shower-unity-cup-round6-reconciliation.md` §4.1–§4.2. Some were fixed since: S1.1 (R6-12 Timeline), S1.2
(R6-11 deck levels), S1.3 (R6-05 manual parents), S2.3 (R6-04 cap labels), S2.4 (§13.2 counter copy), S3.1
(R6-15 hint prefix), S3.2 (R6-23 Pal label), S3.3 (R6-09 + R6-10), S3.4 (R6-08a spark prose), S3.5 (§13.6 band
carrier). Each is re-measured in §7.5a; walk 6's classifications are **not** inherited.

**What the snapshot-door walk established** (context, contrast): at `105765357d` it rendered
`Turn 67 of 72 · Senior Year Early October` from `Year/Month/Half = Senior / 10 / Early`. This walk reached the
same position through a different vocabulary, which is what settles the door-versus-application question.

### 0.5a Reconnaissance disclosure

Before the data sheet was complete, four routes were opened to locate controls and to confirm the wizard's
draft persistence: `/`, `/career/setup/scenario`, `/career/setup/trainee`, `/career/setup/trainee?search=…`.
Those openings are **not** the primary pass. The wizard draft is session-scoped — the screen states
`One draft per session. Opening this wizard in another tab replaces this one (last write wins); an expired
session restarts the wizard.` — so the primary pass started at S01 and revisited every screen in forward order.
Nothing entered during reconnaissance survived into the primary pass; the run created (#3) is the primary
pass's own, and the `training_runs` table was empty before it.

---

## 2.0 Data-sheet provenance

Filled **2026-11-07 16:31 +0800**, before the primary pass began. Anchors cite the **current** section title and
line in `docs/[Rosy_Dreams]Rice_Shower_Unity-Cup.md` (1,652 lines, §11 revision table at 1598), re-verified
against titles after the end-to-end read.

> **Data sheet corrections applied from source §11.** Three rows carry the frames' corrections against the
> pre-revision sheet: `Dirt C` / `End C` (was `Dirt G` / `End G`), the second `[Peak Blue]` Daiwa Scarlet's rank
> `S+` (was `UG`), and the ten-skill list's attribution to `Umamusume Details → Skills tab` (not the `Perks`
> panel). `Mile B` / `Front A` / `Late B` are **not** changed — the client prints those and the source's erratum
> of 2026-10-09 already records the catalogue's disagreement with them.

## 2.1 Identity block (pre-filled; carried through the whole audit)

| Field | Value |
| --- | --- |
| Trainee | Rice Shower, `[Rosy Dreams]` card |
| Rarity | ★3 |
| Potential / awakening level | Potential Lvl 2 |
| Scenario | Unity Cup (full label in source: "Unity Cup: Shine On, Team Spirit!") |
| Snapshot point | Senior Year Early Oct |
| Turn index of that point | **67** of 72 (§2.5 derivation) |
| Turns remaining | 5 turns left; 5 turns until the Unity Cup |
| Lifecycle status | **Mid-career snapshot, in progress** — not completed, not Veteran, not Legacy |
| Run number | **None present in source** |
| Stats | Speed 607 (B) · Stamina 577 (C+) · Power 549 (C+) · Guts 736 (B+) · Wit 397 (D+) |
| Unspent SP | 173 |
| Mood | GREAT |
| Energy | Low; bar near one third; **no numeric value printed by the client**; warning text present |
| Support deck | 6 cards, all SSR; see §2.3 |
| Borrowed / friend-slot card | Super Creek `[Piece of Mind]` — the deck's friend-slot card |
| Skill count | 10 visible chips on `Umamusume Details → Skills`, plus Corner Recovery ○ marked Obtained |
| Ancestors | 2 legacies + 4 grandparents (6 ancestors), frame-verified per-ancestor spark chains |
| Races won | 4 preseason Unity Cup rounds (R1 Junior Late Dec, R2 Classic Late Jun, R3 Classic Late Dec, R4 Senior Late Jun); finals pending |
| Outstanding objective | Place 1st in Arima Kinen (Senior Late Dec, turn 72, 5 turns away); three earlier goals cleared |

## 2.3 Pre-extracted data sheet (filled before the first click)

| Datum | Value to enter | Source reference |
| --- | --- | --- |
| Trainee name | Rice Shower | §1.1 @47 |
| Card / epithet | `[Rosy Dreams]` | §1.1 @47 |
| Rarity | ★3 | §1.1 @47 |
| Potential / awakening | Potential Lvl 2 | §1.1 @47 |
| Scenario | Unity Cup (full: "Unity Cup: Shine On, Team Spirit!") | §1.1 @47 |
| Snapshot point | Senior Year Early Oct | §1.1 @47 |
| Turn index | 67 | §2.5 derivation; §1.1 @47 |
| Turns remaining | 5 | §1.1 @47 |
| Lifecycle status | mid-career, in progress | §1.1 @47 |
| Run number | *(not present in source)* | — |
| Speed / Stamina / Power / Guts / Wit | 607 / 577 / 549 / 736 / 397 | §1.2 @54 |
| Caps in force | 1325 / 1322 / 1368 / 1308 / 1800 | §1.2 @54 |
| Scenario floor | 1300 / 1300 / 1300 / 1300 / 1800 | §1.2 @54 |
| Growth (S/St/P/G/W) | +0 / +10 / +0 / +20 / +0 | §1.2 @54 |
| Unspent SP | 173 | §1.2 @54 |
| Mood | GREAT | §1.7 @250 |
| Energy | *(low; no number)* | §1.7 @250 |
| Aptitudes (client, frame-corrected) | **Turf A, Dirt C; Sprint E, Mile B, Medium A, Long A; Front A, Pace A, Late B, End C** | §1.2, §11 C-01/C-02 |
| Deck card 1 | Tokai Teio `[Dream Big!]`, Speed, 35/35 | §1.4 @106 |
| Deck card 2 | Nishino Flower `[Even the Littlest Bud]`, Speed, 30/30 | §1.4 @106 |
| Deck card 3 | Biko Pegasus `[Double Carrot Punch!]`, Speed, 30/30 | §1.4 @106 |
| Deck card 4 | Super Creek `[Piece of Mind]`, Stamina, 50/50, **borrowed / friend slot** | §1.4 @106 |
| Deck card 5 | Matikanetannhauser `[Just Keep Going]`, Guts, 30/30 | §1.4 @106 |
| Deck card 6 | Haru Urara `[Urara's Day Off!]`, Guts, 35/35, Unity Cup linked | §1.4 @106 |
| Skills owned (visible ten) | Blue Rose Closer Lvl 3, Red Shift/LP1211-M, Corner Adept ○, Swinging Maestro, Nimble Navigator, Steadfast, Deep Breaths, Extra Tank, Pace Chaser Straightaways ○, Shrewd Step — **from `Umamusume Details → Skills tab`** | §1.3, §11 C-04 |
| Skill owned (not in visible ten) | Corner Recovery ○ | §1.3 |
| Ancestor 1 (legacy) | Vodka `[Wild Top Gear]`, rank B+ | §1.5 @216 |
| Ancestor 2 (legacy) | Maruzensky `[Formula R]`, rank UG, **Guest** | §1.5 @216 |
| Grandparent L1 | Daiwa Scarlet `[Peak Blue]` rank B+; Seiun Sky `[Reeling in the Big One]` rank A | §1.5 @216 |
| Grandparent L2 | Daiwa Scarlet `[Peak Blue]` rank **S+**; Taiki Shuttle `[Wild Frontier]` rank UG | §1.5 @216, §11 C-03 |
| Blue sparks | Stamina ★★★ (Seiun Sky), Power ★★★ (Taiki Shuttle), Speed ★★☆ (Maruzensky), Speed ★★☆ (Daiwa L2), Guts ★☆☆ (Daiwa L1), Power ★☆☆ (Vodka); **no Wit spark anywhere** | §1.5 @216 |
| Team | Blue Bloom, motto "Dreaming Big", Team Rank S (five stars), league 8th | §1.8 @262 |
| Team stat grades | Speed A, Stamina A, Power A, Guts S, Wit A | §1.8 @262 |
| Unity Trainings / Spirits | 55 / 6 Spirit Bursts / 5 Extreme Spirit Bursts / 20 members of 20 | §1.8 @262 |
| Facility levels | Speed 4, Stamina 4, Power 4, Guts 5, Wit 4 | §1.7 @250 |
| Races won | 4 preseason rounds (R1–R4) | §1.6 @225 |
| Race scheduled this turn | Kyoto Daishoten (G2, Kyoto Turf 2400m, Soft) | §1.6 @225 |
| Pending race | Unity Cup finals, after Senior Late Dec | §1.6 @225 |
| Active objective | Place 1st in Arima Kinen, Senior Late Dec, turn 72, 5 turns | §1.6 @225 |
| Cleared objectives | Nikkei Sho top 3; Tenno Sho (Spring) 1st; Takarazuka Kinen top 3 | §1.6 @225 |
| Fans / class | 209,245 fans; class Star; 30,755 to Top Star | §1.1 @47, §2.3g @493 |
| Coverage statement | The wizard walk supersedes §10's original coverage framing: pre-career setup and turn 0 are unfilmed, but the in-career `Career`, `Sparks`, `Perks`, `Learn`, `Log` and `Career Profile` screens **are** captured (§§1.10–1.15). Setup-path claims remain source-transcribed or publisher-derived, not frame-observed. | §10, §11 C-05 |
| In-career screens (context) | The client's `Career` screen (Cockpit equivalent), in-career `Sparks`, `Perks`, `Learn`, `Log`, and the five `Career Profile` panels are all frame-captured (§§1.10–1.15). Use them as reference for what the client does on the wizard path; do not treat them as application observations. | §§1.10–1.15 |

## 2.5 Turn index derivation

The client's calendar is a 12-month year with an early and a late half-month per month, so 24 turns per year.
`turn = (career_year − 1) × 24 + (month − 1) × 2 + 1 + (0 early, 1 late)`. Junior 1–24, Classic 25–48,
Senior 49–72. Anchors: Junior Make Debut = turn 12 (Junior Late June); Arima Kinen = turn 72 (Senior Late
December). Therefore **Senior Year Early October is turn 67**, and the client's "5 turns left" is 72 − 67 = 5.

**Applied:** the training form's `Turn *` field received `67`. The cockpit rendered
`Senior Year · Early October · Turn 67 of 72`, which matches the derivation and the source.

## 7.2 Screen ledger

18 screens, exact visit order.

| Screen ID | Route | Arrived from | Exited via |
| --- | --- | --- | --- |
| S01 | `/` | — (entry point) | link `Start a career` |
| S02 | `/career/setup/scenario` | S01 | button `Continue to trainee` |
| S03 | `/career/setup/trainee` | S02 | search field → card click |
| S04 | `/career/setup/trainee/30` | S03 | link `Continue to build target` |
| S05 | `/career/setup/target` | S04 | button `Continue to legacy` |
| S06 | `/career/setup/legacy` | S05 | button `Continue to deck` |
| S07 | `/career/setup/deck` | S06 | button `Continue to preflight` |
| S08 | `/career/setup/preflight` | S07 | button `Create the run` |
| S09 | `/training-runs/3/cockpit` | S08 (post-create) | career-nav link `Training` |
| S10 | `/training-runs/3/training` | S09 | button `Record turn 67` |
| S11 | `/training-runs/3/cockpit` | S10 (post-commit) | career-nav link `Races` |
| S12 | `/training-runs/3/races` | S11 | career-nav link `Skills` |
| S13 | `/training-runs/3/skills` | S12 | career-nav link `Events` |
| S14 | `/training-runs/3/events` | S13 | career-nav link `Inheritance` |
| S15 | `/training-runs/3/inheritance` | S14 | career-nav link `Deck` |
| S16 | `/training-runs/3/deck` | S15 | career-nav link `Timeline` |
| S17 | `/training-runs/3/timeline` | S16 | career-nav link `Result` |
| S18 | `/training-runs/3/result` | S17 | — (walk end) |

Sub-observations recorded under their parent screen's ID: `S02b` (Unity Cup radio selected), `S03b` (search
`Rice Shower`), `S03c` (card chosen, `Form` select appears), `S05b` (empty-target submit), `S06b`–`S06g`
(manual parents, save, cold reload, Guest correction), `S07b`–`S07e` (slot picker, card search, six cards
equipped), `S08b` (acknowledge checked), `S10b`–`S10d` (turn 67 typed, values entered, pre-commit screenshot),
`S11b` (cockpit position re-read).

## 7.3 Screen-by-screen walk

### S01 — Dashboard (`/`)

**Route and trigger.** `http://127.0.0.1:8000/`, entry point, HTTP 200.

**What the screen asks for.** Nothing. h1 `Trainer Desk`; h2 blocks `Pick up where you left off`, `Needs you`,
`CAREERS RUNNING`, `TRAINEES CATALOGUED`, `SKILLS`, `SUPPORT CARDS`, `Recent veterans`, `Quick actions`. No
input fields. Three career doors are present as links: `Start a career` → `/career/setup/scenario`,
`Import an existing career` → `/career/snapshot`, `Import a historical career` → `/training-runs/import`. A
fourth nav item `New Career` → `/career/setup/scenario` duplicates the first.

**Values entered + source trace.** None.

**Source data with no UI home.** None at this screen.

**Friction / errors / dead ends.**
- **`Start a career` and `New Career` lead to the same route.** Two labels, one destination; the nav item is the
  persistent one and the card is the discoverable one. Not a defect, but nothing marks the wizard as the primary
  path (E-01).
- **`Import an existing career` and `Import a historical career` differ by one word.** Neither card states what
  distinguishes them (a position snapshot versus a file of rows). A Trainer choosing between them must click to
  find out (E-02).

**Outcome / next screen.** Clicked `Start a career` → S02. Screenshot `S01-dashboard.png`.

### S02 — Scenario (`/career/setup/scenario`)

**Route and trigger.** `http://127.0.0.1:8000/career/setup/scenario` via the `Start a career` link (HTTP GET).

**What the screen asks for.** h1 `Which scenario is this run?`; lede: `The scenario decides which panels the
cockpit composes, which caps apply, and which systems exist. It cannot be changed after the first turn.` A
six-step rail (`1 Scenario · 2 Trainee · 3 Build target · 4 Legacy · 5 Deck · 6 Preflight`) with step states
`Saved / Started unfinished / Not started / Current` and `Step 1 of 6`. Four **radio** inputs, all
`name="scenario"`, all `required=false`, none pre-selected:

| id | Label (h2) |
| --- | --- |
| `scenario-radio-ura_finale` | `URA Finale` |
| `scenario-radio-unity_cup` | `Unity Cup` |
| `scenario-radio-trackblazer` | `Trackblazer` |
| `scenario-radio-our_grand_concert` | `Our Grand Concert` |

One button, `Continue to trainee`, **disabled on arrival**. Selecting a scenario enables it and adds a `✕`
control to the head band. Pressing `✕` clears the selection.

**Values entered + source trace.** `Unity Cup` → radio `Unity Cup` → source §1.1 @47 ("Unity Cup: Shine On,
Team Spirit!", logo panel `885d4671`).

**Source data with no UI home.**
- **The scenario's full label.** The screen prints `Unity Cup` only. `Unity Cup: Shine On, Team Spirit!` is not
  shown anywhere in the wizard (U-01).

**Friction / errors / dead ends.**
- **`Continue to trainee` is disabled with no visible reason.** No asterisk, no `required` attribute on the
  radios, no inline message; the only cue is the greying. The lede states the scenario's importance but not that
  it is mandatory to proceed (E-03).
- **No scenario descriptions.** The four labels carry no surface, distance, or mechanic summary; a Trainer who
  does not already know which scenario they are running has nothing to read here. The Unity Cup card elsewhere
  in the app carries a strategic hint (seen on S07), but this step does not (E-04).

**Outcome / next screen.** `Continue to trainee` → S03. Screenshot `S02-scenario.png`.

### S03 — Trainee picker (`/career/setup/trainee`)

**Route and trigger.** `http://127.0.0.1:8000/career/setup/trainee`, via the `Continue to trainee` button.
Searching rewrites the URL to `?direction=asc&search=Rice+Shower&sortBy=name`.

**What the screen asks for.** h1 `Who are you training?`; lede `66 trainees are catalogued. Filter by aptitude if
you are building toward a specific race, then pick the card you are running.` Five filter controls, all
optional: `Search` (text, `name="search"`, placeholder `Name or unique skill`), `Surface` (select, options
`Any S A B C D E F G`), `Distance` (same), `Running style` (same), `Sort` (select, default `name`, options
`Name Turf Dirt Sprint Mile Medium Long Front runner Pace chaser Late surger End closer`). A `Clear` button.
Pagination `66 trainees · page 1 of 11 · sorted by Name`, `Previous` / `Next`. Each row is a card carrying the
name, the JP name, epithet, `★★★ SSR`, `N forms`, `Released (Global)`, `Surface`, `Distance`, `Style`, and a
`Select` button. A head-band link `Continue to build target` appears once a trainee is chosen.

**Filtered state (search `Rice Shower`).** `1 trainee are catalogued.`; one card: `Rice Shower · ライスシャワー ·
[Rosy Dreams] · ★★★ SSR · 2 forms · Released (Global) · Surface Turf A · Dirt G · Distance Sprint E · Mile C ·
Medium A · Long A · Style Front runner B · Pace chaser A · Late surger C · End closer G`.

**Values entered + source trace.** Selected Rice Shower by clicking the card → source §1.1 @47.

**Source data with no UI home.**
- **The frame-corrected aptitudes.** The picker prints the **catalogue's** letters: `Dirt G`, `Mile C`,
  `Front runner B`, `Late surger C`, `End closer G`. The source's §11 C-01/C-02 and the frame `1dde6b2f` record
  `Dirt C`, `Mile B`, `Front A`, `Late B`, `End C`. The client's letters have no entry path here (U-02;
  probe P-29).
- **`Potential Lvl 2`.** The row prints rarity and form count but not the potential level; the profile (S04)
  does not either. The value has no home until Run settings (U-03).

**Friction / errors / dead ends.**
- **`1 trainee are catalogued.`** — number agreement, carried from walk 6 (E-02 there). Reproduces at this
  commit (E-05).
- **Selecting a card resets the list but keeps the typed term.** After the card click the URL drops to
  `/career/setup/trainee`, the list reverts to all 66 rows on page 1, and the `Search` field still reads
  `Rice Shower` while the list no longer reflects it. The selection is retained (a `Selected` block with a
  `Form` select appears at the top). Walk 6's F-03 reproduces in shape, though the term is now preserved in the
  box rather than cleared (F-01).
- **A `Form` select appears only after selection**, with `[Rosy Dreams]` and `[Vampire Makeover!]`, and the copy
  `The form you pick sets this run's rarity and potential.` The picker row said `2 forms` and the dropdown
  offers exactly 2, so walk 6's F-02 (2 vs 3) does not reproduce (F-02).
- **`Form` defaults to `[Rosy Dreams]`** but the preflight then prints `FORM · N/A, No costume form was chosen in
  this draft` — the default is not written to the draft (F-03).

**Outcome / next screen.** `Continue to build target` → S04. Screenshots `S03-trainee-list.png`,
`S03b-trainee-search.png`, `S03c-trainee-chosen.png`.

### S04 — Trainee profile (`/career/setup/trainee/30`)

**Route and trigger.** `http://127.0.0.1:8000/career/setup/trainee/30`, via the `Continue to build target` link
in the head band. The route repeats the picker's own label: the same `Continue to build target` string opens
this read screen from the picker and advances from here to S05.

**What the screen asks for.** Nothing is entered. h1 `Rice Shower ライスシャワー`; h2 sections `Aptitudes`,
`Growth row`, `Unique skill`, `Base stats`, `Costume forms`, `Scenarios this trainee is linked to`. The screen's
own copy: `This is a read screen: nothing here is edited in the wizard. It states what the catalog holds about
her before the choice is made, and the two controls in the head band are the only ones on it.` Two head-band
controls: `Choose a different trainee` and `Continue to build target` (a button here).

**Values entered + source trace.** None (read screen).

**Aptitudes printed:** `Turf A | Dirt G` · `Medium A | Long A · Mile C · Sprint E` · `Pace Chaser A | Front
Runner B · Late Surger C · End Closer G` — the catalogue's letters, not the client's.

**Source data with no UI home.** The frame-corrected letters (`Dirt C`, `Mile B`, `Front A`, `Late B`,
`End C`) again have no home (U-02). The six-ancestor tree and the per-ancestor spark chains are not here by
design (they belong to the Legacy step).

**Friction / errors / dead ends.**
- **Same label, two destinations.** `Continue to build target` on S03 opens this profile (step 2 still); on S04
  it advances to step 3. Walk 6's E-03 reproduces (E-06).
- **Aptitude naming case changes between S03 and S04.** S03 prints `Pace chaser A`; S04 prints `Pace Chaser A`.
  Walk 6's E-04 reproduces (E-07).
- **`Scenarios this trainee is linked to` lists all four and then withdraws its own claim.** Walk 6's E-05
  reproduces (E-08).

**Outcome / next screen.** `Continue to build target` → S05. Screenshot `S04-trainee-profile.png`.

### S05 — Build target (`/career/setup/target`)

**Route and trigger.** `http://127.0.0.1:8000/career/setup/target`, via the S04 button.

**What the screen asks for.** h1 `What are you building toward?`; lede `Five stat targets and the distance you are
building for. These are your own goals, not a game rule, and the cockpit's bars are drawn against them.` Three
selects: `Purpose` (`Not chosen yet | Story Clear | Champions Meeting | Parent Farming | Skill Farming`),
`Surface` (`Not chosen yet | Turf | Dirt`), `Style` (`Not chosen yet | Front Runner | Pace Chaser | Late Surger |
End Closer`). Ten number inputs, all `required=false`:

| Label | `min` | `max` |
| --- | --- | --- |
| `Speed` | 0 | 1300 |
| `Speed ceiling` | 1 | 2000 |
| `Stamina` | 0 | 1300 |
| `Stamina ceiling` | 1 | 2000 |
| `Power` | 0 | 1300 |
| `Power ceiling` | 1 | 2000 |
| `Guts` | 0 | 1300 |
| `Guts ceiling` | 1 | 2000 |
| `Wit` | 0 | 1800 |
| `Wit ceiling` | — | — (`No enterable ceiling for this stat`) |

Four `Distance` radios: `Sprint 1400 m and under · Speed-first`, `Mile 1401–1800 m · Speed-first, stamina as
insurance`, `Medium 1801–2400 m · Speed plus the stamina to finish`, `Long 2401 m and up · all five stats high`.
An optional `SKILL PRIORITIES` text field. A `Use saved defaults` button, disabled. Two action controls:
`Continue to legacy`, and a `✕` clear.

**Screen copy relevant to the findings.** `This is the step the current build skips. | A run created with no
target entered commits with five empty goals, and the cockpit then has nothing to measure a turn against.
Filling these in is what makes the run's progress answerable.` and `No target is recorded yet. The fields below
start empty rather than at zero.`

**Values entered + source trace.** **None — the whole block was submitted empty.** The source carries no targets
of its own (its stat line is the current state, not a goal), so an empty target block is the faithful entry; the
source's `Purpose`, `Surface` and `Style` are not stated either.

**Source data with no UI home.**
- **The source's non-reconciling caps.** The five `ceiling` inputs accept up to 2000 and the targets up to the
  scenario floor (1300/1300/1300/1300/1800). The in-force caps (1325/1322/1368/1308/1800) fit the ceiling inputs
  but there is no field in which to say *these are the caps in force*; the ceiling is a goal, not a recorded cap
  (U-04).
- **Growth row (+0/+10/+0/+20/+0).** No field on this step; the row appears read-only on S04 (U-05).
- **Facility levels (4/4/4/5/4).** Not here; they are entered per turn on the training form (S10).

**Friction / errors / dead ends.**
- **The empty block is accepted — walk 6's F-01 does not reproduce.** Submitting with all five targets, all five
  ceilings, `Purpose`, `Surface`, `Style` and `Distance` empty returned `Build target saved.` and advanced to
  `/career/setup/legacy` with no error. The empty-target branch works, and the copy's promise holds (`A run
  created with no target entered commits with five empty goals`). **P-67 answered: no refusal** (F-04).
- **The step's own status line still says it is skipped.** `This is the step the current build skips.` appears
  above a form the wizard has already made mandatory to pass through (E-09).
- **`Sum of the five entered ceilings` reads `7000` before anything is entered**, and stays `7000` after an
  empty submit. It is the scenario floor's sum presented as the sum of *entered* ceilings (F-05).
- **`Wit` has no ceiling input** while the other four do, with the note `No enterable ceiling for this stat`. The
  asymmetry is explained but means Wit's cap cannot be expressed on this step at all (E-10).
- **`TOTAL AGAINST THE PUBLISHED SAMPLE` prints `N/A` on both sides** (`Your total N/A`, `Published Global sample
  build Special Week N/A`) with the reason that no sample-build column is stored. A comparison table with no
  comparison (E-11).

**Outcome / next screen.** `Continue to legacy` → S06. Screenshots `S05-build-target.png`,
`S05b-build-target-empty-accepted.png`.

### S06 — Legacy (`/career/setup/legacy`)

**Route and trigger.** `http://127.0.0.1:8000/career/setup/legacy`, via `Continue to legacy` (empty target
accepted).

**What the screen asks for.** h1 `Inherit from two parents`; lede `A run starts with the sparks its parents carry.
Both slots are optional; a run with no parents starts from the card's own base.` Its own scope note:
`Record only. This screen stores and compares what you enter. It does not compute inheritance.`

Two parent slots, each with a `Choose a veteran` select whose only options are `Not chosen` and `Not in my
library - enter a name` (`__manual__`), plus a `Leave empty` button. Choosing `__manual__` reveals: `Parent name`
(text), `Rank (letters)` (text), `Rank (stars)` (number, `min=0`, **no `max`**), and a `Guest / rented parent (not
a local run)` checkbox. Empty-select copy: `No Veterans recorded yet. The Veteran library is where a parent is
picked from, and it holds a run once you complete one, so this step cannot name a parent until then.`

An `Affinity` select: `Not recorded | △ | ○ | ◎`. Four grandparent blocks — `GRANDPARENT A1`, `A2`, `B1`, `B2` —
each with a name text field, `Grandparent rank (letters)` (text) and `Grandparent rank (stars)` (number, `min=0`,
**no `max`**). h2 groups: `Parent A Chosen`, `Parent B Chosen`, `Projected start`, `Pair Affinity`,
`Grandparents`, `What inheritance actually does`, `Parents must differ`. Controls: `Continue to deck`,
`Save Legacy`, four `Leave empty`, and the `✕` clear.

A reference table `MECHANISM | EFFECT | WHEN IT APPLIES` documents `Blue Spark`, `Pink Spark`, `Green Spark`,
`White Spark`.

**Values entered + source trace.**

| Field | Value | Source |
| --- | --- | --- |
| Parent A · Parent name | `Vodka [Wild Top Gear]` | §1.5 @216 |
| Parent A · Rank (letters) | `B+` | §1.5 @216 |
| Parent A · Rank (stars) | `3` | improvisation (I-01) |
| Parent A · Guest | unchecked | §1.5 @216 (Vodka is not flagged Guest) |
| Parent B · Parent name | `Maruzensky [Formula R]` | §1.5 @216 |
| Parent B · Rank (letters) | `UG` | §1.5 @216; §11 item 11 keeps `UG` vs `UG1` open |
| Parent B · Rank (stars) | `3` | improvisation (I-01) |
| Parent B · Guest | **checked** | §1.5 @216 (`flagged Guest`) |
| Affinity | left `Not recorded` | source carries no affinity grade |
| Grandparent A1 | `Daiwa Scarlet [Peak Blue]`, `B+`, `3` | §1.5 @216 |
| Grandparent A2 | `Seiun Sky [Reeling in the Big One]`, `A`, `3` | §1.5 @216 |
| Grandparent B1 | `Daiwa Scarlet [Peak Blue]`, **`S+`**, `3` | §1.5 @216, §11 C-03 |
| Grandparent B2 | `Taiki Shuttle [Wild Frontier]`, `UG`, `3` | §1.5 @216 |

**Source data with no UI home.**
- **The per-ancestor spark chains.** Six ancestors × between 4 and 16 spark rows each (source §2.3b table),
  with star counts per row. The parent and grandparent blocks carry **name, rank letters, rank stars and Guest
  only** — no spark rows at all. Every blue/pink/green/white spark in the source is unrepresented (U-06, the
  largest gap in this walk).
- **The `Guest` flag's per-parent placement** is present, which the source needs for Maruzensky.
- **Affinity `△ / ○ / ◎`** exists but the source states no value; left `Not recorded`.
- **Star bounds per kind** (blue and white to three, pink and green to two) are not enforced: `Rank (stars)` has
  `min=0` and no `max`, and no per-kind ceiling appears.

**Friction / errors / dead ends.**
- **`Rank (stars)` and `Grandparent rank (stars)` expose no `max`.** Walk 6's F-07 reproduces on these fields: the
  accessibility tree reports `valuemax="0"` for an input that in fact accepts 3 (F-06).
- **Two different ★★★ blue-spark figures on one screen.** The `MECHANISM` table reads `Blue Spark  +5, +12 or +21
  to the stat it names, for one, two or three stars`, while the prose below it reads `A Blue Spark that rolls
  three stars lifts the cap of the stat it names by 16`. Walk 6's F-06 reproduces verbatim at this commit
  (F-07).
- **Saving confirms only by toast.** `Legacy recorded.` is the only signal; the persistence is real (see the
  cold-reload probe) but the screen does not show the saved values as a summary.
- **The Guest checkboxes are not addressable except by position.** With two manual parents the DOM carries two
  bare checkboxes with no `id` and no `aria-label` beyond their visible text; the first belongs to Parent A.
  This walk's first scripted pass checked the wrong one and corrected it (recorded in place; E-12).
- **`Parents must differ` is presented as a heading with no content under it** in the reachable state.

**P-68 — cold reload after save: PASSES.** After `Save Legacy`, a full page reload re-rendered every entered
value: both parent names, both rank letters, both rank stars, both Guest states, all four grandparent names,
letters and stars. **Walk 6's F-05 does not reproduce** — the manual-parent loss is fixed (F-08).

**Outcome / next screen.** `Continue to deck` → S07. Screenshots `S06-legacy.png`, `S06b-legacy-manual-open.png`,
`S06d-legacy-saved.png`, `S06e-legacy-after-reload.png`.

### S07 — Deck (`/career/setup/deck`)

**Route and trigger.** `http://127.0.0.1:8000/career/setup/deck`, via `Continue to deck`. Opening a slot writes
`?deck_slot=N` to the URL.

**What the screen asks for.** h1 `Build the six-card deck`; lede `Six slots. Every card earns its place by the
training it boosts, the events it brings, or the friendship gauge it starts with.` A `Scenario: Unity Cup` line
and an unconditional strategic hint: `Strategic hint for Unity Cup: | Wit cap is 1800. Consider a Wit-heavy
deck.` Six slot cards — `Slot 1` … `Slot 5`, `Slot 6 · Friends` — each with `Not equipped.` when empty, an
`Ownership` group (`N/A` default, `Owned` / `Rented`), and a `Choose a card in Slot N` button. Once equipped a
slot gains a `Level` number input (`min=1`, `max=50`, `aria-label="Level for Slot N"`), `Replace in Slot N` and
`Remove` buttons. A `Deck 0 of 6 slots filled` counter, `Deck diagnostics`, `Card types`, `Deck summary N
assigned`, `Add a card`. The slot picker is a modal over the page with a `Type` select, a `Name contains` search,
an `Owned only` checkbox, `Close`, `Filter`, and one `Equip` button per row.

**Values entered + source trace.**

| Slot | Card | Level | Ownership | Source |
| --- | --- | --- | --- | --- |
| 1 | `Tokai Teio [Dream Big!]`, SSR Speed | 35 | Owned | §1.4 @106 |
| 2 | `Nishino Flower [Even the Littlest Bud]`, SSR Speed | 30 | Owned | §1.4 @106 |
| 3 | `Biko Pegasus [Double Carrot Punch!]`, SSR Speed | 30 | Owned | §1.4 @106 |
| 4 | `Super Creek [Piece of Mind]`, SSR Stamina | 50 | **Rented** | §1.4 @106 (the borrowed card) |
| 5 | `Matikanetannhauser [Just Keep Going]`, SSR Guts | 30 | Owned | §1.4 @106 |
| 6 · Friends | `Haru Urara [Urara's Day Off!]`, SSR Guts | 35 | Owned | §1.4 @106 |

Final counter: `Deck 6 of 6 slots filled`; `Deck diagnostics · Six slots filled  6 of 6`; `Deck summary 6
assigned`; `Cards carrying a friendship bonus  6 of 6`; `Scenario link on a card  1`.

**Source data with no UI home.**
- **The deck's effect values (catalogue max vs client current).** The screen shows `Card effects (9)` / `(10)` /
  `(8)` as a disclosure of the card's **own** effect set, with no second column for what the run has reached. The
  source's two-column comparison has no home (U-07).
- **The borrowed card as a *slot* fact.** Slot 6 is labelled `Friends`, and the note says the role belongs to the
  position, but the source's borrowed card (Super Creek) is in **slot 4**, not slot 6. The screen's model
  (position six = friend slot) and the source's model cannot both be expressed; this walk marked slot 4 `Rented`
  and slot 6 `Owned`, which records the borrowing but not the client's slot-6-as-Friends placement (F-09, U-08).
- **Card level caps per rarity** (SSR 30/35/40/45/50 by break) are not enforced: the `Level` input accepts
  `max=50` for every card, and the catalogue's own cap for each is not shown.

**Friction / errors / dead ends.**
- **`Continue to preflight` stays disabled until every equipped slot has a `Level`.** With all six cards equipped
  but one `Level` empty, the primary action refused with no visible explanation — no `*` on the Level label, no
  inline error, no summary line. The deck counter read `Deck 6 of 6 slots filled` while the button refused.
  Typing the missing level enabled it (F-10, blocking-adjacent).
- **Card matching by name picks the wrong variant.** Typing `Tokai Teio` and taking the first `Equip` equipped
  `[Teio-Oo-Oo!!!]`; `Nishino Flower` gave `[Tracen Academy]` (a Pal card), `Biko Pegasus` gave
  `[Tracen Academy]`. The picker's `Name contains` search matches the character name, and the epithet is not the
  sort key, so three of six slots had to be re-picked by searching the epithet instead (E-13).
- **`Not equipped.` appears unconditionally**, printed under the counter even when all six slots are filled.
- **The strategic hint is unconditional.** `Wit cap is 1800. Consider a Wit-heavy deck.` is printed for this run
  whose deck is three Speed, one Stamina, two Guts, no Wit. Walk 6's E-08 reproduces (E-14).
- **The slot picker renders one `Equip` button per row with no pagination and no row count** (E-15).

**P-69 — deck levels through Preflight into the run deck: PASSES.** The six levels entered here
(35/30/30/50/30/35) appear unchanged on the run's own deck screen (S16: `Level for Slot 1 = 35`, `2 = 30`,
`3 = 30`, `4 = 50`, `5 = 30`, `6 = 35`). **Walk 6's F-11 does not reproduce** — the level transfer is fixed
(F-11).

**P-75 — `Pal slot` label: PASSES.** The wizard deck labels position six `Slot 6 · Friends` and `Friends slot`;
the string `Pal slot` does not appear. Walk 6's E-06 is fixed at the label. The `Pal` card **type** still exists
in the `Card types` table beside the `Friends` **slot**, which is the distinction the source insists on (F-12).

**Outcome / next screen.** `Continue to preflight` → S08. Screenshots `S07-deck.png`,
`S07b-deck-slot1-picker.png`, `S07e-deck-source.png`.

### S08 — Preflight (`/career/setup/preflight`)

**Route and trigger.** `http://127.0.0.1:8000/career/setup/preflight`, via `Continue to preflight` once every
level was set.

**What the screen asks for.** h1 `Before the first turn`; lede `One screen listing what will be created, what the
run will be measured against, and the things worth knowing before you commit.` `Step 6 of 6`, `Back to deck`,
`Create the run` (disabled until acknowledged). A summary block: `TRAINEE Rice Shower`, `FORM`, `SCENARIO Unity
Cup`, `DISTANCE`, `CAREER LENGTH  72 turns` with the note `72 turns is three career years of the client's own
24-turn grid. The post-December finale block sits outside that grid and is not counted.` A five-row setup check
table:

| Check | State | Detail |
| --- | --- | --- |
| `Scenario` | `Pass` | `Unity Cup` |
| `Trainee` | `Pass` | `Rice Showerライスシャワー` |
| `Your target` | `Missing` | `Not entered` |
| `Legacy` | `Pass` | `No Affinity grade read · 2 of two parents recorded` |
| `Deck` | `Pass` | `6 of six positions carry a card` |

Then `What this scenario asks` (`Scenario Unity Cup`, `Focus Wit carries the scenario's own cap bonus; team rank
and Spirit Burst counts decide the exclusive-skill payout.`, `Purpose N/A`, `Course N/A`, `Facility levels Rise
with the team rank, not by repeated training.`, `Fan gates N/A`, `Happy Meek N/A`, `Finale targets N/A`), `What
is not built yet` (three absence statements), `Ruleset version: N/A`, a `Commit` paragraph, one checkbox `I
understand this creates an Active run that cannot be un-created; retirement is the only exit.`, and the note
`Acknowledge the above to enable "Create the run".`

**Values entered + source trace.** The acknowledge checkbox, checked. Not a source value; an acknowledgement
(I-02).

**Source data with no UI home.**
- **The in-force caps.** `Purpose`, `Course`, `Fan gates` and `Finale targets` print `N/A` with reasons. The run's
  actual caps (1325/1322/1368/1308) appear nowhere in this summary (U-04).
- **The borrowed card.** The deck check prints `6 of six positions carry a card` but not which is rented.

**Friction / errors / dead ends.**
- **`Trainee · Pass · Rice Showerライスシャワー`** — the Latin and Japanese names run together with no separator
  (E-16).
- **`FORM · N/A, No costume form was chosen in this draft`** while S03's `Form` select showed `[Rosy Dreams]`
  selected. Walk 6's F-09 (`FORM · as chosen above`) is **fixed in wording** — the placeholder is gone — but the
  underlying issue changed shape: the form still is not recorded, only the placeholder's text (F-03, F-13).
- **`Legacy · Pass` with `2 of two parents recorded`.** Walk 6's F-10 (a `Pass` on a step whose data was
  discarded) does **not** reproduce: the data is genuinely recorded now, so the `Pass` is earned (F-08).
- **`Your target · Missing` reads as a failure.** The copy above the form said the step is skippable and the run
  commits with five empty goals; here the missing target is reported in the same `STATE` column that carries
  `Pass`, so a skipped-by-design step renders as a defect against the run (F-14).
- **`CAREER LENGTH 72 turns` is a literal.** P-71 answered: unchanged at this commit, still `72`, so the
  source's §2.5 derivation holds (F-15).
- **`Create the run` is disabled with its explanation below the button.** The final line `Acknowledge the above
  to enable "Create the run".` explains it, but it sits below. It is the third primary action in the wizard that
  greys out without an inline cue (E-17).

**P-70 — preflight read-back names scenario and trainee: PASSES.** `TRAINEE Rice Shower` and `SCENARIO Unity Cup`
are both printed. The snapshot-door walk's F-15 (preflight omitting both) does not reproduce (F-16).

**Outcome / next screen.** `Create the run` → S09, with flash `Career started for Rice Shower, run #3.`
Screenshot `S08-preflight.png`.

### S09 — Cockpit, before any turn (`/training-runs/3/cockpit`)

**Route and trigger.** `http://127.0.0.1:8000/training-runs/3/cockpit`, created by `Create the run`. Flash:
`Career started for Rice Shower, run #3.`

**What the screen asks for.** Nothing yet. h1 `Rice Shower`; career nav `Cockpit · Training · Races · Skills ·
Events · Inheritance · Deck · Timeline · Result` with `Result: Available when the career is completed.` Head
state `No turn recorded yet`, with the lede `Energy, Mood, Fans, Skill Points and the five stats are what this
screen reads, and none has been entered for this run. Until one turn is recorded the advisor has nothing to
rank, so it declines rather than guessing.` Link `Record the first turn through Training`. `Stats and state`
prints five stats as `N/A / 1,300 cap` (Wit `1,800`), with the note `No build target was set for this run, so
bars are drawn against this run's ceilings: the ceilings you entered where you entered one, the scenario's own
figure where you did not.` Advisor: `PRIORITY Nothing can be prioritised until this turn's Energy is recorded.`
and obligations `Junior Make Debut · ahead · Junior Year · Turn 12`, plus three `URA Finals` rows.

**Values entered + source trace.** None.

**Source data with no UI home.**
- **Turns remaining.** No counter toward 72 appears; the position is `No turn recorded yet` (F-17).
- **The in-force caps.** `1,300 cap` is the scenario floor, printed under the word `cap` (U-04, P-74).

**Friction / errors / dead ends.**
- **The Advisor offers `Junior Make Debut` on a run about to be positioned at turn 67.** At this point the run
  has no position, so the Junior obligation is defensible; it becomes a finding after the commit (S11).
- **`URA Finals Qualifier / Semifinal / Final (Aoharu)` obligations on a Unity Cup run.** Walk 6's F-14
  reproduces verbatim, and the labels are URA-specific on a Unity Cup career (F-18).
- **`N/A / 1,300 cap`** presents the scenario floor as this run's cap with no field anywhere to raise it (F-19).

**Outcome / next screen.** `Training` in the career nav → S10. Screenshot `S09-after-create.png`.

### S10 — Training form (`/training-runs/3/training`)

**Route and trigger.** `http://127.0.0.1:8000/training-runs/3/training`, via the career-nav `Training` link.
h1 `Pick this turn's action`.

**What the screen asks for.** Eight radio actions `name="pick"`: `1 Speed · Feeds Power · Lv not recorded base`,
`2 Stamina · Feeds Guts`, `3 Power · Feeds Stamina`, `4 Guts · Feeds Speed and Power`, `5 Wit · no Energy · Feeds
Speed`, `Rest · Refills Energy. A poor rest refills less and can leave a stayed-up-late penalty.`,
`Recreation · Lifts Mood, and the outing may return a little Energy as well.`, `Race · Optional race · see the
race desk`. Then:

| Label | name / id | type | min | max | default |
| --- | --- | --- | --- | --- | --- |
| `ENERGY AFTER` | `energy` / `turn-energy` | number | 0 | 100 | — |
| `MOOD AFTER` | `mood` / `turn-mood` | select | — | — | `Unchanged` |
| `FANS` | `fans` / `turn-fans` | number | 0 | — | — |
| `SKILL POINTS` | `sp` / `turn-sp` | number | 0 | — | — |
| **`Turn *`** | `turn` / `turn-turn` | number | 1 | **— (no max)** | **`1`** |
| `Speed *` | `speed` / `turn-speed` | number | 0 | 1300 | — |
| `Stamina *` | `stamina` / `turn-stamina` | number | 0 | 1300 | — |
| `Power *` | `power` / `turn-power` | number | 0 | 1300 | — |
| `Guts *` | `guts` / `turn-guts` | number | 0 | 1300 | — |
| `Wit *` | `wit` / `turn-wit` | number | 0 | 1800 | — |
| `Speed level` | `facility_speed` | number | 1 | 5 | — |
| `Stamina level` | `facility_stamina` | number | 1 | 5 | — |
| `Power level` | `facility_power` | number | 1 | 5 | — |
| `Guts level` | `facility_guts` | number | 1 | 5 | — |
| `Wit level` | `facility_wit` | number | 1 | 5 | — |
| `Outcome` | `outcome` | select | — | — | `Choose` |
| `Energy state` | `energy_state` | select | — | — | `exact` |
| `Energy band` | `energy_band` | select | — | — | `mid` |
| (unlabelled) | `failure_rate` | number | 0 | 100 | — |
| (unlabelled) | `preview_gains[speed…sp]` | number ×6 | — | — | — |

Buttons: `Undo turn 1` (enabled), `Discard`, `Record turn 1`.

**Values entered + source trace.**

| Field | Value | Source |
| --- | --- | --- |
| Action | `1 Speed` | §1.7 @250 (the observed training turn was a Speed facility turn, Lv4, `Floor Cleaning`) |
| `Turn *` | **`67`** | §2.5 derivation; §1.1 @47 |
| `Energy state` | `Band` | §1.7 @250 (`the client never prints an Energy number ... a Trainer can identify the low band`) |
| `Energy band` | `Low` | §1.7 @250 (low bar, warning text) |
| `ENERGY AFTER` | *(left empty)* | source carries no number (I-03) |
| `MOOD AFTER` | `GREAT` | §1.7 @250 |
| `FANS` | `209245` | §1.1 @47 |
| `SKILL POINTS` | `173` | §1.2 @54 |
| `Speed` | `607` | §1.2 @54 |
| `Stamina` | `577` | §1.2 @54 |
| `Power` | `549` | §1.2 @54 |
| `Guts` | `736` | §1.2 @54 |
| `Wit` | `397` | §1.2 @54 |
| `Speed level` | `4` | §1.7 @250 |
| `Stamina level` | `4` | §1.7 @250 |
| `Power level` | `4` | §1.7 @250 |
| `Guts level` | `5` | §1.7 @250 |
| `Wit level` | `4` | §1.7 @250 |
| `Outcome` | `Success` | improvisation (I-04) |
| `failure_rate`, `preview_gains` | *(left empty)* | the source states `Failure 39%` for the client's own tile, not for an unbound forecast field (I-05) |

**P-65 — does `Turn *` accept 67?** **Yes.** Typing `67` left `aria-invalid="false"`, every `validity` flag
false, and changed the submit button from `Record turn 1` to `Record turn 67`.

**Source data with no UI home.**
- **The client's three counters.** The training screen carries no goal countdown, no Unity Cup countdown and no
  `Entry criteria met!` (U-09).
- **The facility tiles' effect line.** The tiles print `No facility level is recorded for this training on any
  turn this run has logged`, which is correct at this point but stays that way even after the level fields below
  are filled (U-10).
- **The client's preview (+18 Speed / +8 Power / +6 Skill Points) and Failure 39%.** `preview_gains` and
  `failure_rate` exist as unlabelled inputs, not as a rendered forecast.
- **Kyoto Daishoten (this turn's scheduled race).** Not surfaced on the training form at all (U-13).

**Friction / errors / dead ends.**
- **`Turn *` defaults to `1` and the primary button reads `Record turn 1`.** This is the surface that made walk 6
  commit a Junior-Year position. The field is unconstrained (`min=1`, no `max`), so 67 is accepted — but nothing
  on the screen suggests the source's position, and a Trainer who trusts the default records the wrong turn.
  **This is the single most consequential default in the flow** (F-20).
- **`Energy state` defaults to `Exact` while `ENERGY AFTER` is empty.** Choosing `Band` is required to record the
  source's actual reading; the default state contradicts the empty field (E-18).
- **`ENERGY AFTER` accepts 0–100 but the source has no number.** Filling it would be a fabrication; leaving it
  empty under state `Band` is the only faithful entry (I-03).
- **An unlabelled cluster of seven number inputs** (`failure_rate`, six `preview_gains[*]`) sits at the bottom of
  the form with no visible label, no help text and no bucket heading (E-19).
- **`Undo turn 1` is enabled before any turn exists**, and its label does not follow the `Turn` field — after
  typing 67 the button still read `Undo turn 1` (F-21).

**Outcome / next screen.** `Record turn 67` → S11. Screenshots `S10-training-form.png`,
`S10b-training-turn-67.png`, `S10c-training-filled.png`, `S10d-training-before-record.png`.

### S11 — Cockpit after the commit (`/training-runs/3/cockpit`)

**Route and trigger.** `http://127.0.0.1:8000/training-runs/3/cockpit`, after `Record turn 67`.

**What the screen asks for.** Nothing; it reports. h1 `Rice Shower`. The position line, verbatim:

> `Senior Year · Early October · Turn 67 of 72. Energy Low band, Mood GREAT ↑ +20%`

and the band `Turn 67 · Active`. Stats: `Speed 607 / 1,300 cap`, `Stamina 577 / 1,300 cap`,
`Power 549 / 1,300 cap`, `Guts 736 / 1,300 cap`, `Wit 397 / 1,800 cap`. `ENERGY Low band`, `MOOD GREAT ↑ +20%`,
`FANS 209,245`, `SKILL POINTS 173`. `This run is at GREAT: training +20%, running +4%` with a `Mood effect at
every tier` disclosure. A `Caps` block: `Speed 1,300 · Stamina 1,300 · Power 1,300 · Guts 1,300 · Wit 1,800 ·
Blue sparks No blue spark is recorded in this run's Legacy.` Scenario panel: `Finale 5 turns away`. Advisor:
`PRIORITY Junior Make Debut is due and not recorded. It fell due on turn 12, 55 turns ago.` with `Rest`
recommended and the benefit line `Energy is recorded as a low band, below the 50 advisory line; Rest is
advised.` Obligations table with `Junior Make Debut · current · Junior Year · Turn 12` and the three `URA
Finals` rows. `Recorded action  Speed · Recorded 07/11/2026`, `The rail gave no benefit line for this action
when it was taken.`, `Expected (from sources) N/A, No source publishes a per-training yield for a discipline.`

**Values entered + source trace.** None; confirmatory read.

**P-66 — does the cockpit render the position?** **Yes: `Senior Year · Early October · Turn 67 of 72`.** The
payload agrees: `careerPosition {year:3, month:10, phase:"Early", turn_index:67}`,
`header.next_turn_to_play: 68`, `header.total_turns: 72`, `header.logged_turns: 1`.

**P-72 — the Junior obligation on a Senior run: reproduces.** `Junior Make Debut is due and not recorded. It fell
due on turn 12, 55 turns ago.` and the obligations row `Junior Make Debut · current · Junior Year · Turn 12`, on
a run standing at turn 67. Same controller, different door, so this is a controller defect rather than a door
artefact (F-22).

**P-74 — in-force caps vs floor under the word `cap`: the floor.** `Speed 607 / 1,300 cap` and the `Caps` block
print `1,300/1,300/1,300/1,300/1,800` — the scenario floor — where the source's in-force caps are
`1325/1322/1368/1308/1800`. No field in the wizard carried the in-force values (the build-target ceilings were
left empty by design). The snapshot-door walk's F-07 and round-6 R6-04 reproduce (F-19, U-04).

**Source data with no UI home.**
- **The in-force caps** (above, U-04).
- **`Entry criteria met!`** The client's goal bar prints it; this cockpit prints `Finale 5 turns away` and the
  obligations table, but no `Entry criteria met!` state (U-09, U-19).
- **The class badge / fan ladder.** The payload carries `fan_ladder {class:"Star", nextThreshold:240000,
  gap:30755}` but the screen prints only `FANS 209,245`; the ladder itself is not rendered (F-23, U-11).
- **Team Blue Bloom, Team Rank S, league 8th, 55 Unity Trainings, 6/5 bursts, 20/20 members.** The scenario panel
  prints `Team N/A, not recorded`, `League N/A`, `Bursts N/A`, `Team rank not recorded` — six source values with
  no entry path anywhere in the wizard (U-12).

**Friction / errors / dead ends.**
- **Energy self-contradiction.** The live advisor says `Energy is recorded as a low band, below the 50 advisory
  line` while the recorded-action block's refusal in the same payload says `This run has recorded no Energy`.
  Both are available for the same run (F-24).
- **The obligations table carries URA rows on a Unity Cup run**, as on S09 (F-18).
- **`Junior Make Debut ... 55 turns ago`** is arithmetically right (67 − 12) and semantically wrong: the run did
  not skip the Debut, it was imported at turn 67. The copy asserts a missed obligation the source does not claim
  (F-25).

**Outcome / next screen.** `Races` → S12. Screenshots `S11-after-record.png`, `S11b-cockpit-position.png`.

### S12 — Races (`/training-runs/3/races`)

**Route and trigger.** `http://127.0.0.1:8000/training-runs/3/races`, via the career-nav `Races` link.

**What the screen asks for.** h1 `Enter a race, or skip the turn`. h2 `Available this turn`, `Brazil Cup`,
`Consecutive races`. The `Available this turn` table carries one row per race with columns
`RACE | GRADE | SURFACE · DISTANCE · APTITUDE | FANS NEEDED | SKILL PT |` and a state cell. Seven rows are
listed, each ending in a disabled button `Maiden lock not cleared` with the note `The maiden lock is not cleared;
this race opens only once a Debut or Maiden is won.` An `Enter a race` button (disabled). An `Undo turn 19`
button (disabled). An off-calendar form with month/half/race-name selects and a `NotOffered` state select.

**Values entered + source trace.** None — every calendar race is locked.

**P-73 — maiden lock on a wizard-created Senior run: reproduces.** All seven available races carry `Maiden lock
not cleared`, and `Enter a race` is disabled. The lock is the same mechanism the snapshot door showed, so it is
not a door artefact (F-26).

**Source data with no UI home.**
- **Kyoto Daishoten (G2, Kyoto Turf 2400m, Soft, 6,700 fans on a win)** — the source's race *scheduled this turn*
  — is not in the available list, and cannot be entered while every row is maiden-locked (U-13).
- **The four preseason rounds won** (R1–R4) have no row and no representation (U-14).
- **The pending Unity Cup finals** has no row; the cockpit carries `Finale 5 turns away` but the race desk does
  not list the finals (U-15).
- **The cleared objectives** (Nikkei Sho, Tenno Sho Spring, Takarazuka Kinen) have no row here.

**Friction / errors / dead ends.**
- **`Undo turn 19` on a run standing at turn 67.** The button names a turn far from the run's own position, and
  it is disabled so it cannot be exercised. The number does not correspond to any turn this run has logged (turn
  67 is the only one). The snapshot-door walk's "Undo turn 18" reproduces with a different number at this commit
  (F-27).
- **`Brazil Cup` appears twice** — once as the h2 block and once as the first table row.
- **The off-calendar race form defaults to `NotOffered`, `month = 10`, `half = Late`**, none of which match the
  run's position (`Early`) (F-28).
- **Seven locked rows and no explanation of how the lock is cleared.** The note says a Debut or Maiden must be
  won, but no control on this screen records one and every row is disabled (F-26, blocking).

**Outcome / next screen.** `Skills` → S13. Screenshot `S12-races.png`.

### S13 — Skills (`/training-runs/3/skills`)

**Route and trigger.** `http://127.0.0.1:8000/training-runs/3/skills`, via the career-nav `Skills` link.

**What the screen asks for.** h1 `Spend 173 Skill Points`. h2 `Skill Point budget`, `Skills`, `Why the hint
ladder is not linear`. Budget block: `SKILL POINTS 173 · PLANNED SPEND 0 · REMAINING 173`, and a `Learn planned
skills` button (disabled). Filters `SEARCH` (search), `TYPE` (select, **disabled**, single option `Any`), `HINTS`
(select, **disabled**, single option `Any`), `Clear`, `Clear plan`. h2 `Skills` prints `No skill is recorded on
this run yet.` A `Why the hint ladder is not linear` table: `Hint Lvl 1 10% · Hint Lvl 2 20% · Hint Lvl 3 30% ·
Hint Lvl 4 35% · Hint Lvl Max 40%`.

**Values entered + source trace.** None.

**Source data with no UI home.**
- **The ten owned skills.** `No skill is recorded on this run yet` — the source's ten chips plus Corner Recovery
  ○ have no entry path anywhere in the wizard (U-16). The wizard has no skills step.
- **Skill levels** (`Blue Rose Closer Lvl 3`) and **hint levels** have no field (U-17).
- **The Learn screen's thirty-three printed prices and the `Obtained` wording** have no counterpart.

**Friction / errors / dead ends.**
- **`No skill is recorded on this run yet` sits beside `SKILL POINTS 173`.** The run has 173 SP and no skills;
  both are true, but the screen does not distinguish "never entered" from "never acquired" (E-21).
- **`TYPE` and `HINTS` filters are disabled with one inert option each** — two controls that look actionable and
  do nothing (F-29).
- **Walk 6's F-15 does not reproduce.** The `CLIENT CAPTION` column is gone; `Hint Lvl 1` appears once per row.
  Fixed (F-30).
- **`Learn planned skills` is disabled with an empty plan**, which is correct, but the disabled state has no
  inline reason.

**Outcome / next screen.** `Events` → S14. Screenshot `S13-skills.png`.

### S14 — Events (`/training-runs/3/events`)

**Route and trigger.** `http://127.0.0.1:8000/training-runs/3/events`, via the career-nav `Events` link.

**What the screen asks for.** h1 `A training event is asking`. h2 `What the event shows`, `Record the outcome`,
`This event's provenance`. An event-name field with `Source not set`. Copy: `Event text is the client's own copy.
This tool does not transcribe or paraphrase it; the fields below record the words the client shows, so the screen
never invents event copy.` and `No option predicts its outcome. The app is forbidden to invent a score, a
percentage or a "best pick" for an event whose results no source publishes.` Fields: `The option's own words`
(text), a Turn (number, prefilled `68`), a select, two text fields, `BOND CHANGE` (number), `NOTE` (text). The
outcome block prints `ENERGY Not recorded`, `MOOD Not recorded`, `SKILL POINTS Not recorded`, `STAT CHANGED Not
recorded`. Buttons `Undo turn 67`, `Record event`, and a `Record what this moment granted (required to record)`
disclosure.

**Values entered + source trace.** None — the source records no event.

**Source data with no UI home.**
- **The source carries no event transcription**, so this screen has nothing to accept. Its empty state is
  correct.
- **The event's four outcome deltas** have no columns, as the screen itself states.

**Friction / errors / dead ends.**
- **`Record event` is enabled with every required field empty.** The button is live while `The option's own
  words`, the Turn, the select and the provenance fields are all empty. The screen's line `Record what this
  moment granted (required to record)` marks the requirement but the primary is already live (F-31).
- **`Undo turn 67` here, `Undo turn 19` on Races, `Undo turn 1` on Training.** Three screens, three different
  undo turns, none following the run's position (F-21, F-27).
- **Turn field prefills `68`,** the *next* turn to play rather than the turn being recorded (67). The Timeline
  labels the same record `67` (F-32).

**Outcome / next screen.** `Inheritance` → S15. Screenshot `S14-events.png`.

### S15 — Inheritance (`/training-runs/3/inheritance`)

**Route and trigger.** `http://127.0.0.1:8000/training-runs/3/inheritance`, via the career-nav `Inheritance`
link.

**What the screen asks for.** h1 `Choose what to inherit`. h2 `Inheritance`, `Parent A · Vodka [Wild Top Gear]`,
`Parent B · Maruzensky [Formula R]`, `Result of this pick`. A six-ancestor grid is rendered and **populated from
the wizard's legacy step**:

```
GRANDPARENT  Daiwa Scarlet [Peak Blue]              · recorded ancestor
GRANDPARENT  Seiun Sky [Reeling in the Big One]     · recorded ancestor
PARENT A     Vodka [Wild Top Gear]                  · Unity Cup
TRAINEE      Rice Shower                            · Unity Cup
GRANDPARENT  Daiwa Scarlet [Peak Blue]              · recorded ancestor
GRANDPARENT  Taiki Shuttle [Wild Frontier]          · recorded ancestor
PARENT B     Maruzensky [Formula R]                 · Unity Cup
COMPATIBILITY Not recorded                          · raises trigger chance and payout
```

Per-parent panels: `Parent A · Vodka [Wild Top Gear] · ★★★ · Unity Cup` with `This parent carries no recorded
spark.`; `Parent B · Maruzensky [Formula R] · Guest · ★★★ · Unity Cup` with `This parent carries no recorded
spark.` Copy: `One selection per run is what this tool stores. The moment grants one spark per parent, but the
run carries a single legacy selection, so this screen lists the sparks each parent carries and the run's
recorded selection rather than promising per-parent radios the column cannot hold.` A `Result of this pick`
table. Buttons `Skip this moment`, `Record inheritance`, and a Turn (number, prefilled `68`).

**Values entered + source trace.** None. The source's per-ancestor spark chains have no field (see below).

**Source data with no UI home.**
- **All six ancestors' spark chains.** Every panel prints `This parent carries no recorded spark.` — because the
  Legacy step has no spark fields. The source's blue/pink/green/white rows per ancestor (§2.3b) are entirely
  unrepresented (U-06, continued).
- **The second Daiwa Scarlet's `S+` badge.** Entered as letters `S+` on S06 and accepted, but the panel here
  labels both Daiwa Scarlet instances `recorded ancestor` without a rank (F-33, U-18).
- **Compatibility** — `Not recorded`, source states none.

**Friction / errors / dead ends.**
- **`★★★` on both parents.** The `Rank (stars)` value entered as `3` renders as `★★★`, and the source's rank is
  a **letter** badge (`B+`, `UG`), not a star grade. The two are separate concepts in the client (rank badge vs
  spark grade), and the screen conflates them into one star display (F-34).
- **`Record inheritance` is enabled with nothing selected** — the snapshot-door walk's finding reproduces (F-35).
- **`This parent carries no recorded spark`** is honest but is the direct consequence of the Legacy step's
  missing spark fields, so the Inheritance screen cannot do its job for this source at all (F-36).

**Outcome / next screen.** `Deck` → S16. Screenshot `S15-inheritance.png`.

### S16 — Deck, run-scoped (`/training-runs/3/deck`)

**Route and trigger.** `http://127.0.0.1:8000/training-runs/3/deck`, via the career-nav `Deck` link.

**What the screen asks for.** h1 `This run's six slots`. Lede: `The deck is run-scoped: changing it here does not
touch any other career. Effects apply from the next turn.` `Back to cockpit`. Six slots, each with the card,
rarity chip, type, `Not a Scenario Link`, a `Card effects (N)` disclosure, a `Level` input, `Ownership` (`Owned` /
`Rented`), `Replace in Slot N`, `Remove`.

**Values entered + source trace.** None; confirmatory read of the wizard's deck.

| Slot | Card | Level | Ownership |
| --- | --- | --- | --- |
| 1 | Tokai Teio `[Dream Big!]` | **35** | Owned |
| 2 | Nishino Flower `[Even the Littlest Bud]` | **30** | Owned |
| 3 | Biko Pegasus `[Double Carrot Punch!]` | **30** | Owned |
| 4 | Super Creek `[Piece of Mind]` | **50** | Rented |
| 5 | Matikanetannhauser `[Just Keep Going]` | **30** | Owned |
| 6 · Friends | Haru Urara `[Urara's Day Off!]` | **35** | Owned |

**P-69 confirmed here**: the six levels entered in the wizard survived to the run record.

**Source data with no UI home.**
- **Super Creek's borrowed status.** The screen shows `Rented` on slot 4, which records the borrowing; but the
  client's `Friends` pill sits on Super Creek (slot 4 here) while this screen's `Friends` role belongs to slot 6.
  The source's placement cannot be reproduced (U-08, continued).
- **Catalogue-max vs current effect values** — the `Card effects (N)` disclosure shows one set (U-07).

**Friction / errors / dead ends.**
- **`Not a Scenario Link` on every card including Haru Urara**, whom the source identifies as the deck's only
  Unity Cup link via `SupportCard::isScenarioLink('unity_cup')`. The wizard's deck screen reported `Scenario link
  on a card  1`, so the run-scoped screen contradicts the setup screen for the same deck (F-37).
- **`Level` accepts `1–50` for every card** with no rarity cap surfaced (as on S07).
- **No save control visible in the reachable state**; the section is a read/edit surface whose commit path is not
  stated on screen (E-22).

**Outcome / next screen.** `Timeline` → S17. Screenshot `S16-deck.png`.

### S17 — Timeline (`/training-runs/3/timeline`)

**Route and trigger.** `http://127.0.0.1:8000/training-runs/3/timeline`, via the career-nav `Timeline` link.

**What the screen asks for.** h1 `Every logged turn, race and event`. Counters: `TURNS RECORDED 1`,
`RACES ENTERED 0`, `EVENTS 0`, `FINALE Not reached`. Filters `KIND` (`All entries`, `Turn`), `YEAR` (`All years`,
`Senior`), `Clear`, `Previous` / `Next` (both disabled). Row:

| TURN | POSITION | KIND | WHAT HAPPENED | ENERGY | SP |
| --- | --- | --- | --- | --- | --- |
| `67` | `Senior · Early Oct` | `Turn` | `Turn recorded · Recorded as entered` `Edit turn 67` | `Low band` | `173` |

Copy: `Turn numbers are the run's own; the position is the calendar's label for that turn. A turn row prints the
Energy reading and the Skill Points that turn recorded. Race and event rows record neither, so they print N/A: an
N/A is "not recorded", never zero.` `Phase grouping` with `Junior Year · 0 entries` (`Turns 1-24. Duration
unknown for most entries; the Debut race is the one dated obligation.`), `Classic Year · 0 entries`,
`Senior Year · 1 entries`, `After the grid · finale block (0)`.

**Values entered + source trace.** Confirms turn 67's record: `ENERGY Low band`, `SP 173`, position
`Senior · Early Oct`.

**Source data with no UI home.**
- **The four preseason rounds won** (`RACES ENTERED 0`) and the scheduled Kyoto Daishoten have no rows
  (U-13, U-14).
- **`Entry criteria met!`, the goal list, the team block** — not on the timeline.

**Friction / errors / dead ends.**
- **`Junior Make Debut` is the only dated obligation the phase copy names**, described as `the one dated
  obligation` for a run that will never play a Junior-year turn (E-23).
- **`1 entries`** — pluralization, carried from the snapshot door.
- **`Edit turn 67` navigates to the training form prefilled 68**, as on Events (F-32, cross-screen).

**Outcome / next screen.** `Result` → S18. Screenshot `S17-timeline.png`.

### S18 — Result (`/training-runs/3/result`)

**Route and trigger.** `http://127.0.0.1:8000/training-runs/3/result`, via the career-nav `Result` link.

**What the screen asks for.** Nothing. h1 `Rice Shower · Unity Cup`; h2 `This career is still running`; copy
`This career is still running, so there is no result to read yet. The result screen reads a run whose status is
Completed; until then the Cockpit is where the next turn is decided and where the status changes.` Links `Back
to the Cockpit`, `Career Timeline`, `Cockpit`.

**Values entered + source trace.** None. The source is a mid-career snapshot; no result exists.

**Source data with no UI home.** Not applicable — the screen correctly refuses to fabricate a result.

**Friction / errors / dead ends.**
- **The career-nav `Result` item is a live link on every screen from S09 onward**, while the nav band prints
  `Result: Available when the career is completed.` and the screen itself refuses. The gate is enforced in the
  screen, not the nav (F-38).
- **The nav item was absent before the run existed** and became a link after create. Inconsistent presentation of
  the same gate across the flow (E-24).

**Outcome / next screen.** **Walk end.** All nine career-nav screens visited; the wizard's six steps and the
trainee profile visited. The run is Active at turn 67 of 72, so the Result screen correctly has nothing to read.
Screenshot `S18-result.png`.

## 7.4 Improvisation log

Values and choices the source does not provide.

| # | Screen | Field / control | Value chosen | Why this value | Alternatives rejected |
| --- | --- | --- | --- | --- | --- |
| I-01 | S06 | `Rank (stars)` (both parents, all four grandparents) | `3` | The source gives letter badges only (`B+`, `UG`, `A`, `S+`) and no star grade for an ancestor. `3` is the maximum the star vocabulary allows and leaves the field populated so the Inheritance screen can be compared against the source's letter badge. | `0` (asserts no stars, a claim the source does not make); blank (admissible — the field is not required; see the note below) |
| I-02 | S08 | `I understand this creates an Active run that cannot be un-created…` | checked | Required to enable `Create the run`. Not a source value; an acknowledgement. | — |
| I-03 | S10 | `ENERGY AFTER` | left empty, with `Energy state = Band` and `Energy band = Low` | The client prints no Energy number (§1.7). Entering one would fabricate a value; the band is the source's own reading. | a number (fabricated); `Energy state = Exact` (contradicts the empty field) |
| I-04 | S10 | `Outcome` | `Success` | The source records the training turn without stating success or failure; `Success` is the ordinary case and the field is optional. | `Failure` (would assert a failed training the source does not describe) |
| I-05 | S10 | `failure_rate`, `preview_gains[*]` | left empty | The source states `Failure 39%` and `+18 Speed / +8 Power / +6 Skill Points` for the client's own tile, but these unlabelled inputs have no stated turn binding, and the screen's own `Expected (from sources)` line says `No source publishes a per-training yield for a discipline.` | entering `39` and the three gains (would place a client preview into an unbound forecast field and contradict the screen's sourcing rule) |
| I-06 | S03 | `Form` select | left at the default `[Rosy Dreams]` | The source names the card `[Rosy Dreams]`; the default matches, so no change was made. The draft does not record it (F-03). | selecting `[Vampire Makeover!]` (wrong card) |
| I-07 | S06 | `Affinity` | `Not recorded` | The source states no affinity grade. | `△` / `○` / `◎` (all fabricated) |
| I-08 | S07 | Slot-4 ownership | `Rented` | The source's Super Creek is the borrowed card (§1.4). | `Owned` (contradicts the source) |
| I-09 | S12–S17 | Every write control on the decision screens | not exercised | The source carries no event, no inheritance pick, no race entry and no further turn. | — |

**Note on I-01.** The star field was populated because the same number renders the parent's `★★★` on S15; left
blank, the Inheritance screen would have shown a bare rank letter. Both readings are improvisation and neither is
sourced; the walk took the one that produced a surface it could compare against the source's letter badge, and
records here that the star grade is invented.

## 7.5 Door and turn index used

**Door:** the wizard (`New career` → `/career/setup/scenario` → `/career/setup/trainee` →
`/career/setup/trainee/30` → `/career/setup/target` → `/career/setup/legacy` → `/career/setup/deck` →
`/career/setup/preflight`).

**Turn index entered:** `67` at the training form's `Turn *` field (`#turn-turn`), not `1` (the default).

**What the application rendered:** `Senior Year · Early October · Turn 67 of 72` — the position line verbatim:
`Senior Year · Early October · Turn 67 of 72. Energy Low band, Mood GREAT ↑ +20%`, with the band
`Turn 67 · Active`.

**Compared to the snapshot door:** the alternate-door walk rendered `Turn 67 of 72 · Senior Year Early October`
from `Year/Month/Half = Senior / 10 / Early`. The wizard matches it exactly through a different vocabulary. **The
two doors agree; position-carrying is an application property, not a door property.**

**What the wizard enabled measuring that the snapshot door did not:** the build-target step's target and ceiling
fields and its empty-block branch (S05, P-67); the legacy step's manual-parent flow and its cold-reload
persistence (S06, P-68); the deck step's per-slot `Level` fields and their transfer to the run record (S07 → S16,
P-69); the preflight contract's read-back of scenario and trainee (S08, P-70); the wizard's own 72-turn `CAREER
LENGTH` statement (S08, P-71); and every wizard-path pre-commit surface.

**What the wizard prevented measuring that the snapshot door did:** the snapshot's `Year / Month / Half` entry
field and its `I don't know this value` absent-state per field. Those belong to the alternate-door walk and were
not re-measured here.

**R6-13 correction status: C10 required.** Drafted here for the round-6 register (§6, or the round-7 register's
dated-corrections section):

> **C10.** R6-13's mid-career-entry finding was door-specific, not application-specific. Walk 6 took the wizard
> door and accepted the training form's default of 1; walk 7 entered 67 at the same field and the cockpit
> rendered `Senior Year · Early October · Turn 67 of 72`. The wizard can carry the position; the default of 1
> hid it. R6-13's remaining substance is not "no mid-career entry" but "the wizard path defaults to 1 rather
> than the source's position" — a default-value note, not a capability defect.

This correction does not edit R6-13's row; it records that walk 7 falsified the row's framing.

## 7.5a Walk 6's F-01 → F-17 re-measurement

Protocol: each finding observed at this commit, recorded as `confirms fix`, `confirms defect at current commit`,
or `new state`.

| Walk 6 ID | Walk 6 claim | Observed at `8a3cf21c` | Verdict |
| --- | --- | --- | --- |
| F-01 | Build target mandates all five targets; the "unset" branch it offers is refused | Empty submit (all targets, ceilings, Purpose, Surface, Style, Distance empty) returned `Build target saved.` and advanced | **confirms fix** |
| F-02 | `Form` dropdown lists 2 forms while the profile lists 3 | Picker row says `2 forms`; the post-selection `Form` select offers exactly 2 (`[Rosy Dreams]`, `[Vampire Makeover!]`) | **confirms fix** (counts now agree) |
| F-03 | After `Select`, the search box keeps the term while the list reverts and the query string drops | List reverts to all 66 rows and the URL drops to `/career/setup/trainee`, but the `Search` field keeps `Rice Shower` and a `Selected` block appears | **confirms defect at current commit** (shape changed: term preserved, list still unfiltered) |
| F-04 | Entered ceilings never reach the cap text; cockpit claims scenario ceiling while drawing entered values | Build-target ceilings were left empty by design, so no entered ceiling existed to propagate; the cockpit printed `1,300 cap` (the floor). The propagation half could not be exercised | **inconclusive for the entered-ceiling half; the `cap` label half reproduces** as F-19 |
| F-05 | Manually named parents silently lost at commit | Parents and grandparents survived `Save Legacy`, a cold reload, and reached Preflight (`2 of two parents recorded`) and the Inheritance screen (`Parent A · Vodka`, `Parent B · Maruzensky · Guest`) | **confirms fix** |
| F-06 | Two different ★★★ blue-spark cap raises on screen (`+5/+12/+21` vs `16`) | Both strings present on `/career/setup/legacy` at this commit | **confirms defect at current commit** |
| F-07 | Number inputs with no `max` expose `valuemax="0"` | `Rank (stars)` and `Grandparent rank (stars)` still carry `min=0` and no `max` | **confirms defect at current commit** |
| F-08 | Cockpit prose asserts six blue sparks while listing one; Inheritance reports `0 recorded` | Cockpit prints `Blue sparks No blue spark is recorded in this run's Legacy.`; Inheritance prints `This parent carries no recorded spark.` for both parents. The two agree, both reporting zero — because the Legacy step has no spark fields | **new state** (the contradiction is gone; the underlying absence is now consistent and total) |
| F-09 | Preflight prints `FORM · as chosen above` | Preflight prints `FORM · N/A, No costume form was chosen in this draft` | **confirms fix** in wording; the unrecorded-form defect is now F-03/F-13 |
| F-10 | Preflight marks `Legacy` `Pass` while reporting `0 of two parents recorded` | Preflight prints `Legacy · Pass · No Affinity grade read · 2 of two parents recorded` | **confirms fix** (the `Pass` is now earned) |
| F-11 | Deck card levels arrive empty on the run record | Levels 35/30/30/50/30/35 transferred to the run deck unchanged | **confirms fix** |
| F-12 | Timeline prints `ENERGY N/A` and `SP N/A` for a UI-entered turn | Timeline prints `Low band` and `173` for turn 67 | **confirms fix** |
| F-13 | No mid-career entry point; the wizard commits turn 1; `Need 2 more bursts in 69 turns` | `Turn = 67` accepted; cockpit renders `Turn 67 of 72`; `Finale 5 turns away`; `Junior Make Debut ... 55 turns ago` | **falsified** — see §8.5a and C10 |
| F-14 | Advisor `OBLIGATIONS` lists URA races on a Unity Cup run | Same three `URA Finals` rows on the cockpit | **confirms defect at current commit** |
| F-15 | Skills planner prints `Hint Lvl Hint Lvl 1` | The `CLIENT CAPTION` column is gone; `Hint Lvl 1` appears once | **confirms fix** |
| F-16 | Picker says `The form you pick sets this run's rarity and potential`; Run settings holds empty `Rarity` and `Potential Level` | Settings still carries `Rarity` (`Not stated`) and `Potential Level`, both empty for this run | **confirms defect at current commit** |
| F-17 | Scenario immutability asserted twice while Run settings offers `Change scenario` | Settings offers a `scenario` select including `No scenario set` and a `Change scenario` control | **confirms defect at current commit** |

## 7.6 Findings register

| ID | Type | Screen(s) | Short description | Blocking? |
| --- | --- | --- | --- | --- |
| F-01 | Functional | S03 | Selecting a trainee reverts the list to all 66 rows and drops the query string while the `Search` field keeps the term; filter and visible list disagree | No |
| F-02 | Functional | S03, S04 | Former form-count mismatch (2 vs 3) no longer reproduces; picker row and `Form` select both say 2 | No |
| F-03 | Functional | S03 → S08 | A chosen `Form` is not written to the draft, so Preflight reports no form while the picker showed one selected | No |
| F-04 | Functional | S05 | Walk 6's empty-target refusal no longer reproduces: a wholly empty target block is accepted and saves | No |
| F-05 | Functional | S05 | `Sum of the five entered ceilings` prints `7000` before any ceiling is entered and after an empty submit — the scenario floor's sum labelled as the sum of entered values | No |
| F-06 | Functional | S06 | `Rank (stars)` and `Grandparent rank (stars)` have `min=0` and no `max`; the accessibility tree reports `valuemax="0"` for inputs that accept 3 | No |
| F-07 | Functional | S06 | Two different ★★★ blue-spark cap raises on one screen: `+5, +12 or +21 … for one, two or three stars` beside `lifts the cap … by 16` | No |
| F-08 | Functional | S06 → S08 → S15 | Former manual-parent loss no longer reproduces: parents, ranks and Guest survive save, cold reload, Preflight and the Inheritance grid | No |
| F-09 | Functional | S07, S16 | Slot 6 is the `Friends` slot while the source's borrowed card sits in slot 4; the screen's slot-role model and the client's placement cannot both be expressed | No |
| F-10 | Functional | S07 | `Continue to preflight` stays disabled while any equipped slot lacks a `Level`, with no `*`, no inline error and no summary line — the counter reads `Deck 6 of 6 slots filled` while the primary refuses | **Yes** |
| F-11 | Functional | S07 → S16 | Former deck-level loss no longer reproduces: six per-slot levels reach the run deck unchanged | No |
| F-12 | Functional | S07, S16 | `Pal slot` label no longer appears; position six reads `Slot 6 · Friends` / `Friends slot` | No |
| F-13 | Functional | S08 | Preflight's form line states the form was not chosen even though the picker's `Form` select defaults to `[Rosy Dreams]`; the default is not persisted | No |
| F-14 | Functional | S05, S08 | A deliberately skipped build target renders as `Your target · Missing` in the same `STATE` column that carries `Pass` | No |
| F-15 | Functional | S08 | `CAREER LENGTH 72 turns` is a literal string; the run's position derivation depends on it and nothing links the two | No |
| F-16 | Functional | S08 | Preflight read-back now names scenario and trainee — the former omission is fixed | No |
| F-17 | Functional | S09 | Before any turn, the cockpit prints `No turn recorded yet` and no position at all — no turn of 72, no year, no months remaining | No |
| F-18 | Functional | S09, S11 | Advisor `OBLIGATIONS` lists `URA Finals Qualifier`, `URA Finals Semifinal`, `URA Finals Final (Aoharu)` on a Unity Cup run | No |
| F-19 | Functional | S09, S11 | The cockpit prints the scenario floor `1,300/1,300/1,300/1,300/1,800` under the word `cap` where the source's in-force caps are `1325/1322/1368/1308/1800`; no field accepts the in-force values | No |
| F-20 | Functional | S10 | `Turn *` defaults to `1` and the primary button reads `Record turn 1`; the field accepts 67 but nothing suggests the run's real position, so the default silently commits a Junior-Year record | **Yes** |
| F-21 | Functional | S10 | `Undo turn 1` is enabled before any turn exists, on the same screen whose `Turn` field reads 67 after entry | No |
| F-22 | Functional | S11 | The Advisor reports `Junior Make Debut is due and not recorded. It fell due on turn 12, 55 turns ago.` and lists it `current` on a run standing at turn 67 | No |
| F-23 | Functional | S11 | The payload carries `fan_ladder {class:"Star", gap:30755}` but the screen prints only `FANS 209,245`; the class and the gap are computed and discarded | No |
| F-24 | Functional | S11 | Two contradictory Energy statements for one run: the live advisor says `recorded a low band, below the 50 advisory line`, while the recorded-action refusal says `This run has recorded no Energy` | No |
| F-25 | Functional | S11 | `55 turns ago` is arithmetic (67 − 12) on a position the run did not play; the copy asserts a missed Junior obligation the source never claims | No |
| F-26 | Functional | S12 | All seven calendar races are locked `Maiden lock not cleared` and `Enter a race` is disabled, with no control on the screen that can clear the lock | **Yes** |
| F-27 | Functional | S12, S14, S17 | Three screens print three different `Undo turn N` values (`1`, `19`, `67`) for the same run, and the Races value corresponds to no turn this run has logged | No |
| F-28 | Functional | S12 | The off-calendar race form defaults to `NotOffered`, `month = 10`, `half = Late`, none of which match the run's position (`Early`) | No |
| F-29 | Functional | S13 | `TYPE` and `HINTS` filters are `disabled` with a single inert option `Any` each — two controls that look actionable and do nothing | No |
| F-30 | Functional | S13 | Former doubled hint caption (`Hint Lvl Hint Lvl 1`) no longer reproduces | No |
| F-31 | Functional | S14 | `Record event` is enabled with every required field empty, including the provenance block its own copy marks `(required to record)` | No |
| F-32 | Functional | S14, S15, S17 | Turn convention is split: Events and Inheritance prefill `68`, the Timeline labels the same record `67`, and `Edit turn 67` opens a form prefilled 68 | No |
| F-33 | Functional | S15 | Both Daiwa Scarlet instances render as `recorded ancestor` with no rank, though `S+` and `B+` were entered on S06 | No |
| F-34 | Functional | S15 | The source's letter rank badges (`B+`, `UG`, `A`, `S+`) render as `★★★` star grades, conflating the ancestor's end-of-run class with a spark star grade | No |
| F-35 | Functional | S15 | `Record inheritance` is enabled with no parent selection and no spark chosen | No |
| F-36 | Functional | S15 | Every parent prints `This parent carries no recorded spark.` because the Legacy step has no spark fields, so the Inheritance screen cannot represent the source's tree at all | No |
| F-37 | Functional | S16 | Every run-deck card reads `Not a Scenario Link`, including Haru Urara, while the wizard's deck step reported `Scenario link on a card  1` for the same deck | No |
| F-38 | Functional | S18 + nav | The career-nav `Result` item is a live link on every screen from S09 while the nav band prints `Available when the career is completed` and the screen itself refuses; the gate lives in the screen, not the nav | No |

| ID | Type | Screen(s) | Short description |
| --- | --- | --- | --- |
| E-01 | Experiential | S01 | Two labels (`Start a career`, `New Career`) lead to the same route; nothing marks the wizard as primary |
| E-02 | Experiential | S01 | `Import an existing career` and `Import a historical career` differ by one word and neither card says what the difference is |
| E-03 | Experiential | S02 | `Continue to trainee` is disabled with no asterisk, no `required` attribute and no inline reason |
| E-04 | Experiential | S02 | Four scenario labels carry no description; a Trainer who does not already know the scenario has nothing to read |
| E-05 | Experiential | S03 | `1 trainee are catalogued.` — number agreement |
| E-06 | Experiential | S03, S04 | One label, two destinations: `Continue to build target` opens the profile from the picker and advances from the profile |
| E-07 | Experiential | S03, S04 | Aptitude naming changes case between screens (`Pace chaser A` vs `Pace Chaser A`) |
| E-08 | Experiential | S04 | A region headed `Scenarios this trainee is linked to` lists all four and its own note withdraws the claim |
| E-09 | Experiential | S05 | `This is the step the current build skips.` sits above a form the wizard has already made mandatory to pass |
| E-10 | Experiential | S05 | Only four of five stats have a ceiling input; Wit's absence is explained but leaves the cap inexpressible |
| E-11 | Experiential | S05 | `TOTAL AGAINST THE PUBLISHED SAMPLE` prints `N/A` on both sides — a comparison table with no comparison |
| E-12 | Experiential | S06 | The Guest checkboxes carry no `id` and no `aria-label` beyond their visible text, so they are not addressable except by position |
| E-13 | Experiential | S07 | `Name contains` matches the character name, not the epithet, so picking the right card variant requires knowing and typing the epithet |
| E-14 | Experiential | S07 | `Wit cap is 1800. Consider a Wit-heavy deck.` is unconditional and contradicts this run's three-Speed deck |
| E-15 | Experiential | S07 | The slot picker renders one `Equip` per row with no pagination and no row count |
| E-16 | Experiential | S08 | `Trainee · Pass · Rice Showerライスシャワー` — Latin and Japanese names run together |
| E-17 | Experiential | S08 | `Create the run` is disabled with its explanation below the button rather than beside it |
| E-18 | Experiential | S10 | `Energy state` defaults to `Exact` while `ENERGY AFTER` is empty |
| E-19 | Experiential | S10 | Seven unlabelled number inputs (`failure_rate`, six `preview_gains[*]`) with no heading, label or help text |
| E-20 | Experiential | S12 | The off-calendar race form's defaults (`NotOffered`, `Late`) do not match the run's position (`Early`) |
| E-21 | Experiential | S13 | `No skill is recorded on this run yet` beside `SKILL POINTS 173` — accurate but does not distinguish "never entered" from "never acquired" |
| E-22 | Experiential | S16 | The run-scoped deck shows editable `Level` and `Ownership` controls with no visible save control or commit path |
| E-23 | Experiential | S17 | Phase copy names `Junior Make Debut` as `the one dated obligation` on a run that will never play a Junior turn |
| E-24 | Experiential | S18 | The same gate is absent before the run exists and a live link after, while the screen refuses either way |

| ID | Type | Screen(s) | Short description |
| --- | --- | --- | --- |
| U-01 | Unrepresented | S02 | Scenario full label `Unity Cup: Shine On, Team Spirit!` — only `Unity Cup` appears |
| U-02 | Unrepresented | S03, S04 | The client's frame-corrected aptitudes (`Dirt C`, `Mile B`, `Front A`, `Late B`, `End C`); the screens print the catalogue's `Dirt G`, `Mile C`, `Front runner B`, `Late surger C`, `End closer G` |
| U-03 | Unrepresented | S03, S04 | `Potential Lvl 2` — not on the picker or the profile; only Run settings carries a `Potential Level` field |
| U-04 | Unrepresented | S05 → S11 | The caps in force (`1325/1322/1368/1308/1800`); the cockpit prints the scenario floor under the word `cap` and no step accepts the in-force values |
| U-05 | Unrepresented | S04, S05 | The Growth Rate row (`+0/+10/+0/+20/+0`) is read-only on the profile and absent from the setup steps |
| U-06 | Unrepresented | S06, S15 | Every per-ancestor spark row: six ancestors, blue/pink/green/white kinds, star counts per row. The Legacy step collects name, rank letters, rank stars and Guest only |
| U-07 | Unrepresented | S07, S16 | A support card's catalogue-max effect set versus the values this run has reached; the screens show one set |
| U-08 | Unrepresented | S07, S16 | The client's borrowed-card placement (Super Creek in the sixth position with a `Friends` pill) versus this screen's position-six-is-Friends role |
| U-09 | Unrepresented | S09, S10, S11 | The client's three Career-screen counters (`5 turn(s) left`, `5 Turn(s) Until the Unity Cup`, `Entry criteria met!`) |
| U-10 | Unrepresented | S10 | A facility level's effect on the per-turn tile: the tiles print `Lv not recorded base` and `Energy not recorded` even after the level fields are filled |
| U-11 | Unrepresented | S11 | The class badge and fan ladder (`Star`, 30,755 to Top Star) — computed in the payload, not rendered |
| U-12 | Unrepresented | S11 | Six Team-block values: Team Blue Bloom, Team Rank S (five stars), league 8th, 55 Unity Trainings, 6 Spirit Bursts, 5 Extreme Spirit Bursts, 20/20 members — all print `N/A, not recorded` |
| U-13 | Unrepresented | S10, S12 | Kyoto Daishoten, the race scheduled this turn (G2, Kyoto Turf 2400m, Soft, 6,700 fans) |
| U-14 | Unrepresented | S12, S17 | The four preseason Unity Cup rounds won (R1 Junior Late Dec, R2 Classic Late Jun, R3 Classic Late Dec, R4 Senior Late Jun) |
| U-15 | Unrepresented | S12 | The pending Unity Cup finals as a race-desk row |
| U-16 | Unrepresented | S13 | The ten owned skills plus Corner Recovery ○; `No skill is recorded on this run yet` and the wizard has no skills step |
| U-17 | Unrepresented | S13 | Per-skill level (`Blue Rose Closer Lvl 3`) and hint level |
| U-18 | Unrepresented | S15 | The ancestor rank badges as letters (`B+`, `UG`, `A`, `S+`) — entered on S06, not rendered on S15 |
| U-19 | Unrepresented | S11 | `Entry criteria met!` on the active Arima Kinen objective |

## 7.6a Counts

- **Functional findings:** 38 (`F-01` … `F-38`), of which **3 blocking** (`F-10`, `F-20`, `F-26`).
- **Experiential findings:** 24 (`E-01` … `E-24`).
- **Unrepresented source data:** 19 (`U-01` … `U-19`).
- **Walk 6 re-measurement:** 17 findings — **8 fixed** (F-01, F-02, F-05, F-09, F-10, F-11, F-12, F-15),
  **7 reproduce** (F-03, F-06, F-07, F-14, F-16, F-17, and F-04's label half), **1 falsified** (F-13), **1 new
  state** (F-08).

## 7.7 Source-to-UI traceability matrix

| Datum | Value | Screen ID | Field / control | Outcome |
| --- | --- | --- | --- | --- |
| Scenario | Unity Cup | S02 | radio `Unity Cup` | accepted |
| Scenario full label | Unity Cup: Shine On, Team Spirit! | — | — | no field (U-01) |
| Trainee | Rice Shower | S03 | card click | accepted |
| Card / epithet | `[Rosy Dreams]` | S03 → S07 | `Form` select default | shown, not recorded (F-03) |
| Rarity | ★3 | S03 | row chip `★★★ SSR` | shown read-only |
| Potential Lvl 2 | 2 | — | — | no field in the wizard (U-03) |
| Snapshot point | Senior Year Early Oct | S10 | `Turn * = 67` → cockpit | accepted; rendered `Senior Year · Early October · Turn 67 of 72` |
| Turn index | 67 | S10 | `Turn *` | accepted (P-65) |
| Turns remaining | 5 | S11 | `Finale 5 turns away` | derived, agreed |
| Lifecycle | mid-career, in progress | S08 → S09 | run created `Active` | accepted |
| Run number | *(none)* | — | — | not requested |
| Speed / Stamina / Power / Guts / Wit | 607 / 577 / 549 / 736 / 397 | S10 | five `*` stat fields | accepted; rendered on S11 |
| Caps in force | 1325 / 1322 / 1368 / 1308 / 1800 | — | — | no field; cockpit prints floor under `cap` (U-04) |
| Scenario floor | 1300 ×4 / 1800 | S05 | ceiling inputs' `max` | used as the input ceiling only |
| Growth row | +0 / +10 / +0 / +20 / +0 | S04 | read-only row | shown, not entered (U-05) |
| Unspent SP | 173 | S10 → S11 | `SKILL POINTS` | accepted; rendered 173 |
| Mood | GREAT | S10 → S11 | `MOOD AFTER = GREAT` | accepted; rendered `GREAT` |
| Energy | low, no number | S10 → S11 | `Energy state = Band`, `Energy band = Low` | accepted; rendered `Low band` |
| Aptitudes (client) | Turf A, Dirt C; Sprint E, Mile B, Medium A, Long A; Front A, Pace A, Late B, End C | S03, S04 | none | no field; screens print catalogue letters (U-02) |
| Deck 1 | Tokai Teio `[Dream Big!]` 35 | S07 → S16 | slot 1 picker + `Level` | accepted; transferred |
| Deck 2 | Nishino Flower `[Even the Littlest Bud]` 30 | S07 → S16 | slot 2 | accepted; transferred |
| Deck 3 | Biko Pegasus `[Double Carrot Punch!]` 30 | S07 → S16 | slot 3 | accepted; transferred |
| Deck 4 | Super Creek `[Piece of Mind]` 50, borrowed | S07 → S16 | slot 4 + `Rented` | accepted as Rented; client's slot-6 placement not representable (U-08) |
| Deck 5 | Matikanetannhauser `[Just Keep Going]` 30 | S07 → S16 | slot 5 | accepted; transferred |
| Deck 6 | Haru Urara `[Urara's Day Off!]` 35 | S07 → S16 | slot 6 · Friends | accepted; but `Not a Scenario Link` (F-37) |
| Skills owned (ten + one) | Blue Rose Closer Lvl 3 … Shrewd Step, Corner Recovery ○ | — | — | no field; `No skill is recorded on this run yet` (U-16) |
| Skill levels / hints | Lvl 3; hint ladder | — | — | no field (U-17) |
| Ancestor 1 | Vodka `[Wild Top Gear]` B+ | S06 → S15 | Parent A name + rank letters | accepted; letter not rendered (U-18) |
| Ancestor 2 | Maruzensky `[Formula R]` UG, Guest | S06 → S15 | Parent B name + rank letters + Guest | accepted; Guest rendered |
| Grandparent A1 | Daiwa Scarlet `[Peak Blue]` B+ | S06 → S15 | A1 name + letters | accepted |
| Grandparent A2 | Seiun Sky `[Reeling in the Big One]` A | S06 → S15 | A2 name + letters | accepted |
| Grandparent B1 | Daiwa Scarlet `[Peak Blue]` **S+** | S06 → S15 | B1 name + letters `S+` | accepted as text; not rendered (F-33) |
| Grandparent B2 | Taiki Shuttle `[Wild Frontier]` UG | S06 → S15 | B2 name + letters | accepted |
| Blue sparks (six) | Stamina ★★★, Power ★★★, Speed ★★☆ ×2, Guts ★☆☆, Power ★☆☆ | — | — | no field (U-06) |
| Pink / green / white spark rows | per §2.3b | — | — | no field (U-06) |
| Team Blue Bloom / rank S / league 8th | — | S11 | scenario panel | `N/A, not recorded` (U-12) |
| Unity Trainings 55 / bursts 6+5 / members 20/20 | — | — | — | no field (U-12) |
| Facility levels | 4 / 4 / 4 / 5 / 4 | S10 | five level fields | accepted; tiles still print `Lv not recorded base` (U-10) |
| Races won R1–R4 | 4 rounds | — | — | no field (U-14) |
| Race scheduled this turn | Kyoto Daishoten G2 | — | — | not listed; every row maiden-locked (U-13) |
| Pending finals | Unity Cup finals | S11 | `Finale 5 turns away` | shown as a count only (U-15) |
| Active objective | Arima Kinen 1st, turn 72 | S11 | objectives table | listed as `upcoming` |
| Cleared objectives | Nikkei Sho, Tenno Sho Spring, Takarazuka Kinen | S11 | objectives table | listed as `missed` — the source says cleared (F-25-adjacent) |
| Fans / class | 209,245 / Star / 30,755 to Top Star | S10 → S11 | `FANS` | fans accepted; class and gap not rendered (F-23, U-11) |

## 7.8 Definition-of-done checklist

- [x] Every screen reached has its own section, in visit order — 18 sections, S01–S18.
- [x] Every entered value has a source trace or is marked "not present in source" — see §7.3 per screen and §7.4.
- [x] Every source datum without a UI home is named on the screen where it became relevant — 19 `U-` findings.
- [x] The run's actual lifecycle status (mid-career snapshot) is preserved throughout.
- [x] The run is not classified as Veteran or Legacy.
- [x] The door chosen and the turn index used are recorded in §7.9, with what each enabled or hid.
- [x] The position the application renders is the source's position (`Turn 67 of 72 · Senior Year Early October`).
- [x] Each counter observed is recorded with the event it points at — `Finale 5 turns away` (Unity Cup finals, turn 72).
- [x] Functional and experiential findings are separate — F-01…F-38 vs E-01…E-24.
- [x] The findings register matches the findings sections.
- [x] The walk's stopping point and reason are stated (§7.3 S18).
- [x] Every P-nn is answered or explicitly marked unanswered — see Appendix A.
- [x] The traceability matrix in §7.7 is complete.
- [x] The contamination statement in §7.1 is present.

## Appendix A — Derived probe list and answers

Walk 6's 64-row list plus the eleven wizard-specific probes. Answers cite the screen.

**Wizard-specific probes**

| # | Answer |
| --- | --- |
| **P-65** | **Accepted.** `Turn *` took `67`: `aria-invalid="false"`, `rangeOverflow === false`, and the button relabelled `Record turn 67`. S10. |
| **P-66** | **Position carried.** Cockpit rendered `Senior Year · Early October · Turn 67 of 72`. S11. |
| **P-67** | **The empty-target branch works.** Submitting with all five targets, all ceilings, Purpose, Surface, Style and Distance empty returned `Build target saved.` and advanced. S05b. |
| **P-68** | **The manual-parent path survives a cold reload.** All six ancestor names, four rank letters, star values and both Guest states re-rendered after a full reload. S06e. |
| **P-69** | **Per-slot levels transfer.** 35/30/30/50/30/35 entered on S07 appear unchanged on S16. |
| **P-70** | **Read-back names both.** Preflight prints `TRAINEE Rice Shower` and `SCENARIO Unity Cup`. S08. |
| **P-71** | **Unchanged.** Preflight prints `CAREER LENGTH 72 turns` with the note about the finale block sitting outside the grid. S08. |
| **P-72** | **Reproduces.** `Junior Make Debut is due and not recorded. It fell due on turn 12, 55 turns ago.` S11. |
| **P-73** | **Reproduces.** All seven calendar races carry `Maiden lock not cleared`; `Enter a race` disabled. S12. |
| **P-74** | **The floor, not the in-force caps.** `1,300/1,300/1,300/1,300/1,800` under `cap`. S11. |
| **P-75** | **`Friends slot`.** The string `Pal slot` does not appear; position six reads `Slot 6 · Friends` / `Friends slot`, with `Pal` remaining a card *type* in the `Card types` table. S07. |

**Walk 6's P-01 → P-64** — the fixed-checklist probes.

| # | Answer |
| --- | --- |
| P-01 | **Yes, in effect.** No `snapshot` lifecycle option exists in the wizard, but the run commits as `Active` and the training form's `Turn` field carries any position, so an in-progress state is reachable without a lifecycle flag. S08, S10. |
| P-02 | No run number is requested anywhere. S05, S08. |
| P-03 | Wizard (door 1 of 3). See §7.9. |
| P-04 | Created at turn 67, and every derived surface followed: position label, 72-turn total, next turn 68, `Finale 5 turns away`. S11. |
| P-05 | The wizard asks for **ceilings** (a goal), not caps. Sparks are not asked at all. S05, S06. |
| P-06 | The build-target `max` values and the cockpit's `cap` label both refer to the **scenario floor** (`1300…/1800`); no label anywhere refers to the in-force caps. S05, S11. |
| P-07 | No `Growth Rate` field; the profile shows the row read-only. S04. |
| P-08 | No spark field of any kind, so an absent Wit spark cannot be distinguished from an absent anything. S06. |
| P-09 | **Yes**, as free-text names: `Daiwa Scarlet [Peak Blue]` was entered in two grandparent slots with different letter ranks and both persisted (visible on S15 as two nodes). |
| P-10 | **Yes** — a `Guest / rented parent (not a local run)` checkbox per parent, rendered as `Guest` on S15. |
| P-11 | The friend slot is a **position** (`Slot 6 · Friends`); borrowing is a separate `Owned / Rented` flag per slot. The two are independent, which is why Super Creek can be `Rented` in slot 4. S07, S16. |
| P-12 | No. One effect set per card (`Card effects (N)`). S07. |
| P-13 | Level only, one field per slot (`min=1 max=50`); no limit-break state. S07. |
| P-14 | `Pal` is a card **type** in the `Card types` table and a type chip on cards; the friend slot is a position. They are distinct. S07. |
| P-15 | Not at all — `No skill is recorded on this run yet`, and the wizard has no skills step. S13. |
| P-16 | No skill level or hint fields. S13. |
| P-17 | Neither state exists; the planner has a `PLANNED SPEND` total but no per-skill acquired/planned distinction because no skills are recorded. S13. |
| P-18 | Individual race rows exist with a state select (`NotOffered / Skipped / Entered / Completed`) on the off-calendar form, but every calendar row is locked. S12. |
| P-19 | Not as a race row; only `Finale 5 turns away` on the cockpit. S11, S12. |
| P-20 | The cockpit's objectives table lists 8 trainee goals with `missed` / `upcoming` state, including the active Arima Kinen as `upcoming`. It opens on the run's own era. S11. |
| P-21 | **Yes** — `MOOD AFTER` uses the client's strings `GREAT / GOOD / NORMAL / BAD / AWFUL`, and the cockpit prints `GREAT ↑ +20%`. S10, S11. |
| P-22 | **A band.** `Energy state = Band` + `Energy band = Low`; the exact field was left empty. S10. |
| P-23 | Derived, not a field: `5 turns away` on the cockpit. S11. |
| P-24 | One counter only: `Finale 5 turns away` (Unity Cup finals, turn 72). No Spirit Burst payout counter. S11. |
| P-25 | The advisor prints `Rest recovers +50 Energy in the common case, +70 on a big success, and +30 on failure` — the modal tier is stated correctly as 50 and 30 as failure. S11. |
| P-26 | Yes — `SKILL POINTS` accepted `173`. S10. |
| P-27 | No field for any of the six Team values; all print `N/A, not recorded`. S11. |
| P-28 | No roster field, and none asked. S11. |
| P-29 | The picker prints the **catalogue's** letters. S03. |
| P-30 | One label only (`Potential Lvl 2` in the source's vocabulary); the wizard has no field, and Run settings has `Potential Level` with no `Potential` alias. S03, settings. |
| P-31 | Per-stat team grades have no field; the overall `Team rank` prints `not recorded`. `S+` was accepted as an ancestor **rank letter** text field, so the ancestor badge is not constrained by the team-rank rule. S06, S11. |
| P-32 | The preflight states the rule — `Facility levels Rise with the team rank, not by repeated training.` — but no field records the team rank, so the derivation cannot run. S08, S11. |
| P-33 | The preflight states the convention: `CAREER LENGTH 72 turns` plus `The post-December finale block sits outside that grid and is not counted.` S08. |
| P-34 | No — `Unity Cup` only. S02, S08. |
| P-35 | **Yes** on the Legacy step (two free-text grandparent slots accepted the same card twice). S06, S15. |
| P-36 | No spark vocabulary at all on the wizard path. S06. |
| P-37 | No per-kind star ceiling; `Rank (stars)` has `min=0` and no `max`. S06. |
| P-38 | Rank letters and the `Guest` flag are both accepted. S06. |
| P-39 | No skill-provider field. S13. |
| P-40 | No `○` corner-variant field and no skill-type grouping on a skill row (no skill rows exist). S13. |
| P-41 | The hint ladder is documented correctly (`10 / 20 / 30 / 35 / 40%`, with the `130 → 84` floor case explained) but no prices are printed because no skills are available. S13. |
| P-42 | There is no `Obtained` state. S13. |
| P-43 | No skill-origin grouping. S13. |
| P-44 | The career nav has nine sections; the client's rail has seven. `Sparks` and `Agenda` have no direct equivalent (`Inheritance` covers part of `Sparks`); `Log` maps to `Timeline`, whose state here is `1 entries`, not `There is no log.`. S11–S18. |
| P-45 | No effect text with style/distance, and no `○`. S13. |
| P-46 | No ancestor-unique skills in the pool (no pool). S13. |
| P-47 | No skill-type grouping. S13. |
| P-48 | No venue-skill distinction. S13. |
| P-49 | No row tint. S13. |
| P-50 | **Yes** — `S+` was accepted in the `Grandparent rank (letters)` text field on S06. It is not rendered on S15 (F-33). |
| P-51 | Neither: no spark is stored at all. S15. |
| P-52 | None of the three: the cockpit carries `Finale 5 turns away` only, with no goal countdown and no `Entry criteria met!`. S11. |
| P-53 | The Inheritance screen is a **two-column** layout (`PARENT A` … `TRAINEE` … `PARENT B`) with grandparents flanking, closer to the post-career shape than the vertical in-career shape. S15. |
| P-54 | No field for either client voice. S11. |
| P-55 | **No.** No row per round exists; the source's four wins and the pending finals print as `N/A, not recorded` (team block) and one countdown. S11, S12. |
| P-56 | The run deck shows card name, rarity and type with a `Card effects (N)` disclosure; no `Unique Perk`, no `Unity Cup Exclusive` badge, no per-stat letter-and-cap line. S16. |
| P-57 | No SP-to-next-level figure. S07, S16. |
| P-58 | No tabs on the card surface. S16. |
| P-59 | The cockpit prints `This run is at GREAT: training +20%, running +4%` and a `Mood effect at every tier` disclosure — the client's sentences and a current marker are both present in substance. S11. |
| P-60 | No class ladder rendered, though the payload computes `fan_ladder {class: Star, gap: 30755}`. S11. |
| P-61 | No fan criterion per goal; the objectives table carries label, state, year and turn only, and the three cleared goals print `missed`. S11. |
| P-62 | The catalogue's letters, not the client's. S03, S04. |
| P-63 | No skill tint. S13. |
| P-64 | **No single destination.** Trainee identity is on S04 (pre-create) and Run settings; deck on S16; ancestry on S06/S15; stats on S11. The client's `Career Profile` has no counterpart. |

## Appendix B — Stability probes

Not run. The primary pass is the audit, and the optional second pass was not performed; nothing in this document
rests on it.

## Appendix C — Remediation ideas (non-binding, not implemented)

Not written as proposals. Names only, for a register's own use, and none was implemented:

- The `Turn *` default and its button label (F-20) — the worthiest single change, since it is what produced walk
  6's false conclusion.
- A per-slot spark row set on the Legacy step (U-06) — the largest representation gap.
- The `cap` label versus the in-force caps (U-04, F-19).
- The maiden lock's unescapable state (F-26).
- The `Continue to preflight` disabled state with no stated requirement (F-10).

---

## §9 Success criteria

- [x] The wizard path was taken as the primary door; the snapshot door was not re-run.
- [x] `Turn = 67` was entered at the training form and the cockpit's rendered position was recorded verbatim
      (`Senior Year · Early October · Turn 67 of 72`).
- [x] The door-and-turn verdict is stated explicitly in §7.9 and §8.5a.
- [x] Walk 6's F-01 through F-17 are re-measured per the protocol; each is recorded as confirms / diverges / new
      state in §7.5a, not inherited from round 6.
- [x] Every screen reached has its own audit section, in visit order, with stable screen IDs.
- [x] The screen ledger, findings register, traceability matrix, method-decision section and definition-of-done
      checklist are present and consistent.
- [x] Every value entered is accompanied by a source reference, or is explicitly marked as not present in source
      and listed in the Improvisation log.
- [x] The position the application renders is the source's position.
- [x] Every source datum encountered without a UI representation is named.
- [x] Functional and experiential findings are separated.
- [x] The run is not classified as Veteran or Legacy.
- [x] The mid-career snapshot nature of the run is preserved.
- [x] The audit reflects actual browser behaviour rather than assumptions from source code, prior registers, or
      the source document's tool-internal citations.
- [x] The walk was performed through the UI rather than via a seeder, factory, API, or direct database
      manipulation.
- [x] No application code, configuration, or data was modified.
- [x] Deviations from the one-pass rule are recorded (§0.5a reconnaissance disclosure; the Guest-checkbox
      correction on S06 and the card-variant re-picks on S07 are both recorded in place).
