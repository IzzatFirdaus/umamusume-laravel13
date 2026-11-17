# UX walk audit 6: [Rosy Dreams] Rice Shower, Unity Cup

**Audit ID:** `RS-UC-2026-10` (sixth instantiation of the walk brief; the §7.7 traceability matrix is new to this pass)

## Header

| Field | Value |
| --- | --- |
| Audit name | Rice Shower Unity Cup mid-career UX walk, pass 6 |
| Source document | `docs/[Rosy_Dreams]Rice_Shower_Unity-Cup.md` (1,394 lines; 68 `[Global]` client frames captured 2026-10-03, 67 distinct) |
| Secondary reference (context only) | `docs/UMAMUSUME_REFERENCE.md` |
| Starting URL | `http://127.0.0.1:8000/` |
| Walk start | 2026-11-06 00:20:47 local (shell zone label `MPST`; UTC 2026-11-05 16:21:02) |
| Walk end | 2026-11-06 01:01:55 local (UTC 2026-11-05 17:01:56). Primary pass ≈ 33 min, secondary pass ≈ 8 min |
| Application | Laravel 13, Inertia + Vue 3, SQLite, served by `php -S 127.0.0.1:8000 …/resources/server.php` (PID 17848) |
| Git commit under test | `3f295e80` (`master`); `git status --porcelain` returned 0 lines before the first click |
| App already running? | **Yes.** It was already listening before the first click; this pass did not start, restart or stop it. Pre-flight `curl` returned `HTTP 200 in 3.31s` |
| Vite / asset state | No Vite dev server on `:5173`, no `public/hot` (KI-76 not live); pages served from `public/build` |
| Browser | Qoder 0.5.5 in-app browser, Chromium `150.0.7871.250`, Electron `43.8.4`, Windows NT 10.0 Win64 x64 (10.0.26300) |
| Viewport | 825 × 785 CSS px, `devicePixelRatio` 1, `navigator.language` `en-US` |
| Devtools | No devtools panel opened. The browser was driven through the `browser-use` MCP instrument; DOM reads used `evaluate_script` read-only (see the deviation note in §7.1a) |
| Run created | `/training-runs/1` — `training_runs` id **1**, status `Active`, scenario Unity Cup |
| Screens visited | 20 (S01–S20, visit order, in the ledger below) |
| Version identifier visible in the UI | None. No build number or commit string appeared on any screen visited |
| Pre-existing state | Dashboard read `0 active careers, 0 finished runs`, `VETERANS 0 banked`, `TRAINEES CATALOGUED 133`, `SKILLS 1,910`, `SUPPORT CARDS 559`, `REVIEW QUEUE 304 pending matches`, `Global data · Current · Verified 2026-11-02 · GameTora scenarios.json`. Cookies present (353 chars: session + XSRF). No prior run existed, so the created run took id 1 |

**Method statement.** Every screen below was reached in a real browser session and read from the rendered
accessibility tree or DOM at the time of the visit. Every recorded value was typed, selected or clicked
through on-screen controls (native `<select>`, number and text inputs, radios, checkboxes, disclosure
triangles, the app's own buttons and links). **No seeder, factory, direct database insert or update, SQL,
`tinker`, artisan command that mutates run state, API call, or devtools state edit was used to enter data.**
`git status` at the end of the walk lists only this audit file and `docs/audits/RS-UC-2026-10/screens/`; no
application file was modified. Source code was not read during the walk; nothing in this audit is
attributed to a controller, model or config file.

**Primary-pass scope and stopping point.** The pass ran Dashboard → `New career` → all six wizard steps
(scenario, trainee picker, trainee profile, build target, legacy, deck, preflight) → commit → the run
record and every screen its own Career navigation exposes: Cockpit, Training, Inheritance, Team setup,
Skills planner, Races, Run deck, Timeline, Events, Scenario detail, Run settings. It stopped at Run
settings because that is the last screen reachable from the run record's own navigation that this run can
display; `Result` is gated (`Result: Available when the career is completed.`) and the global `Settings`
(`/preferences`) is outside the run flow and was not entered. Two screens reachable from the run record
were therefore **not** visited and are **not** claimed here: `Result` (gated by status) and `/preferences`.

**Contamination statement.** The source document cites the application's own data layer, code, config and
tests. Each item below was read as context and **none** was treated as a UI observation, and none was used
to predict a screen:

| Item in the source | Status in this audit |
| --- | --- |
| `support_cards` rows 30003, 30016, 30018, 30019, 30020, 30030 | Context only |
| `SupportCard::isScenarioLink('unity_cup')` | Context only. What this pass observed is the picker's own per-card badge, `Not a Scenario Link`, and the deck diagnostic line `Scenario link on a card · 0` |
| `config('scenarios.php')` | Context only |
| `CareerTraineeSelectTest` | Context only. The picker's letters were re-read from the rendered DOM (S03) |
| `database/seeders/data/gametora-characters.e9e9ee6d.json` | Context only |
| The deck table's `Effect set, values at max (catalog)` column | Context only |
| The deck table's `Client rows visible` column | Context only; re-observed as absent (U-09) |
| "D8 of the UX walk remediation", `AUDIT-AND-VERIFICATION.md` | Context only; every finding here is re-derived |
| Prior walks 1–5 and the round 2–5 reconciliations in `docs/audits/` | Context only. No finding below is inherited from them; each rests on this pass's own observation |

**7.1a Deviations, recorded plainly.**

1. **Screen-to-screen navigation after the run was created used direct URL loads** (`/training-runs/1/deck`
   and so on) rather than clicking the nav link, except where noted. Every URL loaded is the `href` of a
   link the Cockpit itself renders, captured verbatim at S09 (`Cockpit`, `Training`, `Races`, `Skills`,
   `Events`, `Inheritance`, `Deck`, `Timeline`, `Result`, `Scenario detail`, `Team setup`, `Run settings`).
   This is a deviation from "click the controls a user would click" and is disclosed rather than hidden.
2. **One DOM write occurred during uid discovery**: an `evaluate_script` call set a transient
   `data-probe-target` attribute on one button on S06 to identify it. No value, form state, application
   state, storage or file was changed by it, and it did not substitute for any data entry. Logged under §4.8.
3. **Deck cards 3–5 were not equipped** (Biko Pegasus, Matikanetannhauser, Haru Urara) and the remaining
   five ancestors' sparks were not entered. This is a scope truncation of this pass, not a UI failure: the
   fields exist and were exercised. Consequences are stated in §8.5 and each unentered datum is marked in
   the §7.7 matrix as `not entered this pass (field exists)`.
4. **The Events screen needs an event to transcribe**; with no event in play it was read empty and no entry
   was made. The Result screen is gated. Neither is a dead end claim beyond what is written.

---

## Screen ledger

| Screen ID | Route | Arrived from (screen / control) | Exited via (control) |
| --- | --- | --- | --- |
| S01 | `/` | — (entry point) | header link `New career` |
| S02 | `/career/setup/scenario` | S01 `New career` | button `Continue to trainee` |
| S03 | `/career/setup/trainee` (later `?direction=asc&search=Rice&sortBy=name`) | S02 `Continue to trainee` | link `Continue to build target` → `/career/setup/trainee/30` |
| S04 | `/career/setup/trainee/30` | S03 `Continue to build target` | button `Continue to build target` |
| S05 | `/career/setup/target` | S04 `Continue to build target` | button `Continue to legacy` (2 failed attempts, then success) |
| S06 | `/career/setup/legacy` | S05 `Continue to legacy` | button `Continue to deck` (after `Save Legacy`) |
| S07 | `/career/setup/deck` | S06 `Continue to deck` | button `Continue to preflight` |
| S08 | `/career/setup/preflight` | S07 `Continue to preflight` | checkbox + button `Create the run` |
| S09 | `/training-runs/1/cockpit` | S08 `Create the run` (redirect) | link `Training` |
| S10 | `/training-runs/1/training` | S09 nav `Training` | button `Record turn 1` (redirects to Cockpit) |
| S11 | `/training-runs/1/inheritance` | S10, direct load of the Cockpit's `Inheritance` href | direct load |
| S12 | `/training-runs/1/team` | S11, direct load of the Cockpit's `Team setup` href | button `Save team state` |
| S13 | `/training-runs/1/cockpit` (second visit) | S12, direct load | direct load |
| S14 | `/training-runs/1/skills` | S13, direct load of the `Skills planner` href | direct load |
| S15 | `/training-runs/1/races` | S14, direct load of the `Race decision` href | direct load |
| S16 | `/training-runs/1/deck` | S15, direct load of the `Run deck` href | reload (secondary pass) |
| S17 | `/training-runs/1/timeline` | S16, deep link (secondary pass, probe 4) | direct load |
| S18 | `/training-runs/1/events` | S17, direct load of the nav `Events` href | direct load |
| S19 | `/training-runs/1/scenario` | S18, direct load of the Cockpit's `Scenario detail` href | direct load |
| S20 | `/training-runs/1/settings` | S19, direct load of the Cockpit's `Run settings` href | — (walk ends) |

Artifacts: `docs/audits/RS-UC-2026-10/screens/` holds 27 PNGs, one per screen plus S03b/S03c, S05b–S05d,
S06b, S07b (state-change captures on screens where a single frame would hide the finding).

---

## Screen-by-screen walk

### S01 — Dashboard (`/`)

**Route and trigger.** `http://127.0.0.1:8000/`, page title `Dashboard`. Initial navigation to the entry
point; HTTP 200, no redirect. No query string, no route id.

**What the screen asks for.** Read-only. In DOM order: skip link `Skip to content`; nav rail (collapsed at
825 px behind a button `Expand navigation`) with group labels `Play`, `My data`, `Library`, `System` and
links `Dashboard`, `New Career`, `Careers`, `Legacy Lab`, `Veterans`, `Review`, `Support Cards`, `Skills`,
`Database`, `Settings`, plus `Data sources`; header band `DASHBOARD`, `h1` `Trainer Desk`, `Help`, the line
`0 active careers, 0 finished runs, and 4 permanent Global scenarios.` and CTAs `New career`,
`All careers`; region `Pick up where you left off` reading `Not recorded: no career is running.`,
`Start a career to track turns, Energy and race record in one place, or import one you have already started
in the client.`, `A running career shows the trainee, the turn, Energy, Mood and Skill Points.`, with links
`Start a career` → `/career/setup/scenario` and `Import an existing career` → `/career/snapshot`; region
`Needs you` (`DATA STATUS`, `Global data · Current`, `Verified 2026-11-02 · GameTora scenarios.json`,
`Source ledger`, `VETERANS / 0 banked`, `Open the library`, `REVIEW QUEUE / 304 pending matches`,
`Open review`); counters `CAREERS RUNNING 0 / No career is running.`, `TRAINEES CATALOGUED 133`,
`SKILLS 1,910`, `SUPPORT CARDS 559`; region `Recent veterans` (`No veterans banked yet. Finish a run, set
it to Completed, and save it from the Legacy Lab.`); region `Quick actions` with `Import a historical
career` → `/training-runs/import`, `Review queue`, `Open settings`, `Database hub`; a disclosure
`Why four destinations`.

**Values entered + source trace.** None entered.

**Source data with no UI home.** None relevant on this screen. Two observations worth carrying forward: the
dashboard exposes **two different import doors** (`Import an existing career` → `/career/snapshot`,
`Import a historical career` → `/training-runs/import`) whose difference is not stated on this screen, and
the phrase `import one you have already started in the client` is the only place in the primary pass where
a mid-career run is acknowledged as an importable thing.

**Friction / errors / dead ends.**
- No app console errors. The only console message is the instrument's own logger:
  `🔍 Browser logger active (MCP server detected). Posting to: http://127.0.0.1:8000/_boost/browser-logs`.
- Three controls on this page target the same route (`New Career` in the rail, `New career` in the header,
  `Start a career` in the main region). Per §3.4 the first *reachable* one was taken. At 825 px the rail is
  collapsed behind `Expand navigation`, so its link is not actionable without an extra click; the header
  `New career` was used.

**Outcome / next screen.** S02.

### S02 — Scenario step (`/career/setup/scenario`)

**Route and trigger.** From S01 by the header link `New career`. URL `/career/setup/scenario`, title
`Which scenario is this run?`. Step indicator text `Step 1 of 6`; the step rail lists
`Scenario`, `Trainee`, `Build target`, `Legacy`, `Deck`, `Preflight`.

**What the screen asks for.** `h1` `Which scenario is this run?`; sub-line `The scenario decides which
panels the cockpit composes, which caps apply, and which systems exist. It cannot be changed after the
first turn.`; `Cancel` → `/`; a `Continue to trainee` button, **disabled**, with `description="Choose a
scenario to continue."` and the hint text `Choose a scenario to continue.`; a draft notice `One draft per
session. Opening this wizard in another tab replaces this one (last write wins); an expired session
restarts the wizard.`; a radiogroup `Which scenario is this career in?` of four option cards, each with
heading, tags and dates: `URA Finale` (`Baseline`, `Global · 2025-06-26`, tags `Race calendar`,
`Career goals`, `Caps 1400 ×5`); `Unity Cup` (`Global · 2025-11-06`, tags `Team races`,
`Team Rank ladder`, `Caps 1300 ×4 · Wit 1800`); `Trackblazer` (`Global · 2026-03-12`,
`Grade Point objectives`, `Pro Shop`, `Caps 1200 ×3 · Stamina 1900 · Wit 1500`); `Our Grand Concert`
(`Global · 2026-07-22`, `Career goals`, `Performance input`, `Caps 1300 ×3 · Speed 1600 · Guts 1500`);
an explanatory line `The panel tags are the composition matrix, not a marketing list: a scenario that
leaves a panel off cannot draw it, and a fifth scenario reaches the same shell for one config entry. The
caps quoted are the published scenario caps, verified 2026-11-02.`; a region `What changes after this
step` table with columns `DECISION / REVERSIBLE? / WHY` and rows `Scenario · No`, `Trainee · No`,
`Build target · Yes`, `Legacy and deck · Yes`.

**Values entered + source trace.** `Unity Cup` radio selected → source §1.1 (data sheet row "Scenario").
Requiredness: signalled by the disabled button plus the hint text, which is the only requirement cue here.

**Source data with no UI home.**
**No UI representation found:** the scenario's full in-game label, `Unity Cup: Shine On, Team Spirit!`
(§1.1). The card prints `Unity Cup` only. (U-01; also true at S08, S09 and S19.)
Nothing on this screen asks for the run's lifecycle state, snapshot point or turns remaining (P-01 begins
here and is answered at S08/S17).

**Friction / errors / dead ends.**
- After selecting `Unity Cup`, a toast printed `Scenario set.`, the card gained a `Selected` badge and the
  button enabled. The card's date label `Global · 2025-11-06` is today's date in the walk; it is the
  scenario's Global release date, not a run date, and the screen never says so.
- The screen states `It cannot be changed after the first turn`, and the reversibility table states
  `Scenario · No`. S20 (Run settings) renders a `Scenario` select with `Change scenario`, which contradicts
  both statements. Recorded as F/E evidence at S20.

**Outcome / next screen.** `Continue to trainee` → S03. The Inertia transition rendered an intermediate
state on the *previous* page: `Loading the page you chose…` plus a `progressbar "Page load progress"`,
which is a genuine loading state on a user-initiated action.

### S03 — Trainee picker (`/career/setup/trainee`)

**Route and trigger.** From S02. Title `Choose a trainee`, `Step 2 of 6`, `Back` link to
`/career/setup/scenario` (not used; one-pass rule), `Continue to build target` disabled with hint
`Choose a trainee to continue.`

**What the screen asks for.** `h1` `Who are you training?`; intro line `66 trainees are catalogued. Filter
by aptitude if you are building toward a specific race, then pick the card you are running.`; a filter form:
`Search` text input; `Surface` select with options `Any`, `S`, `A`, `B`, `C`, `D`, `E`, `F`, `G`;
`Distance` select, same nine options; `Running style` select, same nine options; `Sort` select with
`Name`, `Turf`, `Dirt`, `Sprint`, `Mile`, `Medium`, `Long`, `Front runner`, `Pace chaser`, `Late surger`,
`End closer`; `Clear` button. Result count line `66 trainees · page 1 of 11 · sorted by Name`. Each result
row is a `Select` button (aria-describedby `card-name-<id>`, id `trainee-<id>`) plus text: display name,
Japanese name, `[epithet]`, rarity+rarity-class (e.g. `★★★ SSR`, `★ R`), a form count (`1 form` /
`2 forms`), release state `Released (Global)`, then `Surface`, `Distance`, `Style` letter groups. Pagination
`Previous` (static) / `Next` → `?page=2`.

**Values entered + source trace.**
- `Search = "Rice"` → the picker's own `Search` field. Purpose: locate the trainee named in the data sheet
  ("Trainee name = Rice Shower", §1.1). The search was reflected in the URL as
  `/career/setup/trainee?direction=asc&search=Rice&sortBy=name`.
- Result row read verbatim from the DOM: `Rice Shower`, `ライスシャワー · [Rosy Dreams]`, `★★★ SSR`,
  `2 forms`, `Released (Global)`, `Surface: Turf A · Dirt G`, `Distance: Sprint E · Mile C · Medium A ·
  Long A`, `Style: Front runner B · Pace chaser A · Late surger C · End closer G`.
- `Select` clicked; a `Selected` panel appeared containing the trainee name, `[Rosy Dreams]`, a summary
  `Medium A · Turf A · Pace chaser A.`, the sentence `The form you pick sets this run's rarity and
  potential.`, a **`Form` select with exactly two options: `[Rosy Dreams]` (pre-selected) and
  `[Vampire Makeover!]`**, and a `Preview profile` link.
- `Form = [Rosy Dreams]` left at its default → data sheet row "Card / epithet" (§1.1). Not changed: selecting
  the other form would alter the run's identity, which §4.7 forbids as a probe.

**Source data with no UI home.**
**No UI representation found:** `Potential Lvl 2` (§1.1). The picker mentions potential as a consequence of
the form choice and never asks for or prints a number. (U-04)
**No UI representation found:** the run's own stat line and caps. This screen prints aptitude letters and
rarity only.
The data sheet never carried the trainee's **aptitude letters**, **Fans 209,245 / class Star / 30,755 gap**
or the **second race option** (§1.1, §1.2, §1.6). They were known from the pre-walk read of §1 and are
listed as sheet-coverage gaps at U-12 and in Appendix A P-24.

**Friction / errors / dead ends.**
- **The picker prints the catalogue's letters, not the source's.** Observed `Mile C`, `Front runner B`,
  `Late surger C`; the source records `Mile B`, `Front A`, `Late B` (§1.2) and its dated erratum records the
  disagreement without resolving it. Both readings are preserved in this audit; P-24.
- Three filter selects are labelled `Surface`, `Distance`, `Running style` but every one of their options is
  a grade letter (`S…G`). Nothing on the screen says the filter means "at least this grade"; a user looking
  for a Dirt runner gets no `Dirt` option.
- The intro line, filtered: `1 trainee are catalogued.` (F/E copy defect, E-02). The result count line
  correctly read `1 trainee · page 1 of 1`.
- After clicking `Select`, the retained search text stayed `Rice` while the list reverted to
  `66 trainees · page 1 of 11` and the query string was dropped from the URL: the box no longer described
  the list under it (F-03).

**Bounded probe.** None on this screen. The one probe that would have been informative, changing the `Form`
select, was refused because it would change the run's identity (§4.7).

**Outcome / next screen.** The header's `Continue to build target` became a **link** to
`/career/setup/trainee/30`. Two notes recorded per §4.1: the forward control's label promises step 3 but
delivers a step-2 profile screen, and the route carries a catalogue id (`30`) that the user never saw and
never chose (it surfaces in the DOM as `id="trainee-30"` before it appears in any URL). → S04.

### S04 — Trainee profile (`/career/setup/trainee/30`)

**Route and trigger.** From S03's `Continue to build target` link. Title `Rice Shower`, eyebrow
`TRAINEE PROFILE`, still `Step 2 of 6`. Controls: `Choose a different trainee` → `/career/setup/trainee`
(not used), `Continue to build target` (button), a `Chosen` badge, the draft notice, and the statement
`This is a read screen: nothing here is edited in the wizard. It states what the catalog holds about her
before the choice is made, and the two controls in the head band are the only ones on it.`

**What the screen asks for.** Nothing; it asks for no input. Regions, in order:
`h1` `Rice Shower ライスシャワー`; **Aptitudes** with the line `The aptitudes the source states, one letter
per axis. The strongest of each family is bold; it is not a judgement about which distance or style suits a
career.`, columns `SURFACE`, `DISTANCE`, `RUNNING STYLE`, values `Turf A`, `Dirt G`, `Medium A`,
`Long A · Mile C · Sprint E`, `Pace Chaser A`, `Front Runner B · Late Surger C · End Closer G`, and the
note `An aptitude letter (S to G) grades how well she handles a surface, distance or running style. A stat
grade grades a number out of the run's cap. They are different scales and neither converts into the other,
so a strong aptitude letter says nothing about how high a stat will climb.`; **Growth row** — `The place
this trainee's training gains are weighted.` — `SPEED 0%`, `STAMINA 10%`, `POWER 0%`, `GUTS 20%`, `WIT 0%`;
**Unique skill** — links `Blue Rose Closer` (`/skills/47`) and `Every Rose Has Its Fangs` (`/skills/406`),
each tagged `✦ Unique` `· from the source's class code`, plus `The SP figure on a skill is its own published
cost. No setup screen applies a discount to it.`; **Base stats** — `What the card starts with, before any
training.` — `Speed 71`, `Stamina 117`, `Power 70`, `Guts 102`, `Wit 90`; **Costume forms** — columns
`TITLE / RARITY / SELECTED` and three rows: `[Rosy Dreams]` `Debut form · Jun 26, 2025` three stars
`Selected`; `Yummy Dreamy Fairy` three stars (no date, no form label); `[Vampire Makeover!]`
`Nov 24, 2025` three stars — with the note `Rarity belongs to a costume form, so it is printed per form and
never as a trainee-level badge. A trainee with several forms shows one row each.`; **Scenarios this trainee
is linked to** — `A scenario link decides which scenario cards name this trainee. It does not lock the
choice.` then `URA Finale`, `Unity Cup`, `Trackblazer`, `Our Grand Concert`, and the caveat `No per-trainee
link list is carried on this payload, so the scenarios are shown as available rather than as links. The
scenario step is where the run's choice is made.`; closing note `This page carries no career-goal section by
design: the trainee's own objectives are fetched per character and read under Trainee goals in the
Cockpit's Scenario panel, where the run's race record decides whether each one is met.`

**Values entered + source trace.** None entered (read screen). Artwork frames rendered from
`/artwork/card_portrait/103001`, `103003`, `103002`, i.e. the mirror is populated on this tree.

**Source data with no UI home.**
**No UI representation found:** the run's **current** stat line (607/577/549/736/397). This screen prints
the card's **base** stats (71/117/70/102/90). The two are different things and no field here distinguishes
them; the run's line was entered later, at S10.
**No UI representation found:** the run's **caps** (1325/1322/1368/1308/1800). Not shown, not asked here.
The **Growth row** printed here matches the source's `+0 / +10 / +0 / +20 / +0` exactly, but it is the
catalogue's figure for the card, not a field for this run: the run-side equivalent exists only at S20 and
is empty. P-04.

**Friction / errors / dead ends.**
- **The form count disagrees with the profile.** S03's `Form` select listed **two** options and the row badge
  said `2 forms`; this screen lists **three** costume forms, including `Yummy Dreamy Fairy`, which appears
  nowhere in the selector. A user who wanted that form could not reach it from the wizard. F-02.
- Aptitude naming changes case between the two screens: S03 `Pace chaser A` / `Front runner B` /
  `Late surger C` vs S04 `Pace Chaser A` / `Front Runner B` / `Late Surger C`. E-04.
- The region heading `Scenarios this trainee is linked to` promises link data its own note then withdraws.
  E-05.
- The forward control label is unchanged from S03 (`Continue to build target`) but this time it goes to step
  3; the same words performed two different actions on consecutive screens. E-03.

**Bounded probe.** None: the screen has no field to probe.

**Outcome / next screen.** → S05.

### S05 — Build target (`/career/setup/target`)

**Route and trigger.** From S04 `Continue to build target`. Title `What are you building toward?`,
`Step 3 of 6`, `Back` (not used), `Continue to legacy`.

**What the screen asks for.** Sub-line `Five stat targets and the distance you are building for. These are
your own goals, not a game rule, and the cockpit's bars are drawn against them.`; three statements —
`This is the step the current build skips.`, `A run created with no target entered commits with five empty
goals, and the cockpit then has nothing to measure a turn against. Filling these in is what makes the run's
progress answerable.`, `No target is recorded yet. The fields below start empty rather than at zero.`;
region **What the career is for** with `Purpose` (`Not chosen yet`, `Story Clear`, `Champions Meeting`,
`Parent Farming`, `Skill Farming`), `Surface` (`Not chosen yet`, `Turf`, `Dirt`), `Style`
(`Not chosen yet`, `Front Runner`, `Pace Chaser`, `Late Surger`, `End Closer`); region **Stat targets** with
the line `Each target is compared against the run's scenario cap, so a target above the cap is refused at
preflight rather than silently clipped.` and, per stat, a `spinbutton` (`Speed`, `Stamina`, `Power`, `Guts`:
`valuemin=0 valuemax=1300`; `Wit`: `valuemin=0 valuemax=1800`), a helper `- / 1300 cap` (or `- / 1800 cap`),
a note `not entered, so no share of the cap is claimed`, and a separate ceiling input (`Speed ceiling` …
`Guts ceiling`: `valuemin=1 valuemax=2000`) except Wit, which prints `No enterable ceiling for this stat`;
region **Total against the published sample** (`Your total N/A`, `Published Global sample build · Special
Week · N/A`, `Sum of the five scenario caps 7000`, with `This repository stores no sample-build column, so
the row prints N/A rather than a number it cannot source.`); region **Distance** radios `Sprint 1400 m and
under · Speed-first`, `Mile 1401–1800 m · Speed-first, stamina as insurance`, `Medium 1801–2400 m · Speed
plus the stamina to finish`, `Long 2401 m and up · all five stats high`, plus the disclosed band conflict
`Band edges are Game8's. 1400 m is an open conflict: Game8 places it in Sprint, the data export files every
1400 m course under distance code 2, which reads as Mile, and the client's own skills tagged for that band
gate on distance_type == 2. This tool states the disagreement rather than picking a side…`; region **Skill
priorities (optional)** with a combobox `SKILL PRIORITIES` (`Pick skills by name; type to filter. Chosen
skills are added to the list below.`) and `No skill priorities chosen yet.`; region **Stamina floor** with a
collapsed disclosure `N/A , The chosen scenario publishes no stamina floor per distance, so none is
stated.`; region **Target from a saved default** with a disabled `Use saved defaults`, a link
`Set defaults in Settings` and `No saved stat defaults exist, so there is nothing to apply. Settings is
where they are set.`
**Requiredness is not signalled anywhere on arrival**: no asterisks, no required attributes, and
`Continue to legacy` is enabled.

**Values entered + source trace.**
- `Speed ceiling = 1325`, `Stamina ceiling = 1322`, `Power ceiling = 1368`, `Guts ceiling = 1308` → data
  sheet rows "Speed/Power/Stamina/Guts cap", source §1.2. Accepted; these are the source's printed caps, and
  three of them sit **above** the 1300 the target inputs allow.
- `Distance = Medium` → **not present in §2.3 — UI-assisted choice**, derived from the sheet row "Race
  scheduled this turn = Kyoto Daishoten (G2, Kyoto Turf 2400 m, Soft)": 2400 m falls in this screen's own
  stated band `1801–2400 m`. Logged I-04.
- `Purpose = Story Clear`, `Surface = Turf`, `Style = Pace Chaser` → **not present in the data sheet —
  UI-required choices** forced by a validation failure (see Friction). Derivations and rejected alternatives
  in §7.4 (I-01, I-02, I-03).
- `Speed/Stamina/Power/Guts/Wit` targets = `1300 / 1300 / 1300 / 1300 / 1800` → **not present in the data
  sheet — UI-required**, value taken from the screen's own printed scenario cap (the maximum each input
  accepts). The source states no targets. Logged I-05…I-09.
- `Skill priorities`: left empty. The sheet's skill rows are **owned** skills, not planned ones; putting them
  here would be routing data into an unrelated field (§4.4 forbids), and the sheet carried no §2.3
  recommended-spend rows.

**Source data with no UI home.**
**No UI representation found:** the run's current stat line as a *state*, distinct from a target. The five
required inputs mean "goal"; entering 607/577/549/736/397 there would assert that the run's goal is the
value it has already reached. Those numbers were therefore held for S10 (where the form has a distinct
per-turn stat field) and the mismatch is recorded here per §4.4.
**No UI representation found:** `Wit cap 1800` as an *entered* ceiling — the screen prints
`No enterable ceiling for this stat`. The value survives only as the scenario's published 1800, which
happens to equal it. U-07.
**No UI representation found:** the cap-vs-spark arithmetic gap itself (+25/+22/+68/+8 over the Unity Cup
floor, explained only for the +8). The screen asks for ceilings and (at S06) sparks, never for their
relationship, and offers no place to say "these do not reconcile". U-21, and P-03.

**Friction / errors / dead ends.** This is the walk's worst screen and the only one that blocked.
- First press of `Continue to legacy` (with ceilings entered and targets empty) **did not navigate**. It
  rendered three previously invisible requirements — `The purpose field is required.`,
  `The surface field is required.`, `The style field is required.` — and put `invalid="true"` plus
  `Enter a target for every stat, or leave the whole target unset.` on **all five** empty target inputs.
- Second press, after filling Purpose/Surface/Style, also failed; the five target errors persisted.
- A hypothesis was tested rather than assumed: that entering the ceilings made the target group
  partially-filled. All four ceilings were cleared and the step was submitted again with the target block
  wholly unset. **The hypothesis was refuted** — the same five errors persisted and the step still would not
  advance. So the message's offered escape ("or leave the whole target unset") is not actually available,
  and the copy on this very screen ("This is the step the current build skips", "The fields below start
  empty rather than at zero") describes a state the validator refuses. F-01, Blocking.
- Because progression required five numbers the source does not contain, the screen forced invented data
  into the run. The least-fabricating available value (each stat's own printed scenario cap, the maximum the
  field accepts) was used and logged.
- After the ceilings were entered, the helper line still read `- / 1300 cap` and the sample table still read
  `Sum of the five scenario caps 7000`: nothing on the screen acknowledged that the run's ceilings are
  1325/1322/1368/1308. F-04.
- The three `… required.` messages stayed rendered on fields that had become valid, while
  `Saving your target…` ran; residual error text on satisfied fields. E-01 companion.

**Bounded probe.** The empty-submit validation was observed, not staged: it was produced by pressing the
real forward control. No additional probe was made on this screen (§4.7 one-per-screen, consumed).

**Outcome / next screen.** `Continue to legacy` → S06, with toast `Build target saved.` The invented target
total later read back as `TARGET TOTAL 7,000` at S08.

### S06 — Legacy / inheritance step (`/career/setup/legacy`)

**Route and trigger.** From S05 `Continue to legacy`. Title `Inherit from two parents`, `Step 4 of 6`.

**What the screen asks for.** Sub-line `A run starts with the sparks its parents carry. Both slots are
optional; a run with no parents starts from the card's own base.`; a scope statement `Record only. This
screen stores and compares what you enter. It does not compute inheritance.`; a library notice `No Veterans
recorded yet. The Veteran library is where a parent is picked from, and it holds a run once you complete
one, so this step cannot name a parent until then.`; two blocks `Parent A Not chosen` / `Parent B Not
chosen`, each with `Pick from your veteran library, or leave this slot empty.`, a `Choose a veteran` select
whose **only** options are `Not chosen` and `Not in my library - enter a name`, and a `Leave empty` button.
Choosing `Not in my library - enter a name` reveals four controls: `Parent name` (combobox,
`autocomplete="list"`), `Rank (letters)` (combobox), `Rank (stars)` (number input, `valuemin="0"`,
**no max**, `step` unset), and a checkbox `Guest / rented parent (not a local run)`; plus a
`▸ Sparks for this parent` disclosure and the aid line `The name of the Umamusume who finished the career,
as you read it. The list is the catalogue of trainees this tool knows, offered as a spelling aid; a name not
in it is still storable, because a friend's finished run is never a local row.`; region **Projected start**
(`What the two parents add to this run, before a single turn is played. The recorded Blue Spark against a
stat is what a parent column prints; the projected sum is never computed, because an inheritance outcome is
not this tool's to derive.`) with columns `STAT / SCENARIO CAP / FROM PARENT A / FROM PARENT B / PROJECTED`
and rows `Speed 1300`, `Stamina 1300`, `Power 1300`, `Guts 1300`, `Wit 1800`; region **Pair Affinity** with
an `Affinity` select (`Not recorded`, `△`, `○`, `◎`), the note `The grade you read for the pair, not a score.
This tool does not price it.`, `No Legacy is recorded for this setup yet. Nothing is stored until you save,
and a figure you have not read is left blank rather than filled in.` and a `Save Legacy` button; region
**Grandparents** with the sub-headings `Grandparents of Parent A` / `Grandparents of Parent B`, and for each
of `Grandparent A1`, `A2`, `B1`, `B2` a name combobox, `Grandparent rank (letters)` combobox,
`Grandparent rank (stars)` number input (`valuemin="0"`, no max) and a `▸ Sparks for Grandparent …`
disclosure; region **What inheritance actually does**, a table of `MECHANISM / EFFECT / WHEN IT APPLIES`
with rows `Blue Spark` (`+5, +12 or +21 to the stat it names, for one, two or three stars`), `Pink Spark`,
`Green Spark`, `White Spark`, `Scenario Spark`, followed by the paragraph `The cap raise is the loop. A
Blue Spark that rolls three stars lifts the cap of the stat it names by 16, at career start and at both
April inheritance moments. Two such Sparks on one stat therefore begin a run above the ceiling the scenario
publishes, which is why the cockpit shows an entered ceiling beside the scenario's rather than one number.`;
region **Parents must differ** with `Both slots may name any veteran in your library. The save refuses two
cases: a slot naming the trainee you are running, and a slot naming the same veteran twice. Two entries
naming the same character in different records are stored, because this screen records what you enter rather
than pricing what a repeat would cost.` and `Leaving both slots empty is this screen's choice, not the
client's: the run carries two nullable parent ids so a Trainer can record what they do not know yet, and no
screen invents a default pair.`
Expanding a Sparks disclosure reveals per-row controls: `Kind` (`Blue`, `Pink`, `Green`, `White`,
`Scenario`), `Category` (`Stat`, `Aptitude`, `Skill`), `Target` (`Not recorded`, `Speed`, `Stamina`, `Power`,
`Guts`, `Wit`), `Stars` (number, `valuemin=1 valuemax=3`, default `1`), plus `Remove Spark from …` and
`Add Spark to …`. An empty panel reads `No Sparks recorded for this node.`

**Values entered + source trace.**
- `Parent A`: `Not in my library - enter a name`; `Parent name = Vodka`; `Rank (letters) = B+` → sheet row
  "Ancestor 1 (legacy)" (§1.5). `Rank (stars)` left empty; `Guest` left unchecked.
- `Parent B`: manual; `Parent name = Maruzensky`; `Rank (letters) = UG`; **`Guest / rented parent` checked**
  → sheet row "Ancestor 2 (legacy) … **Guest**" (§1.5).
- `Grandparent A1 = Daiwa Scarlet`, rank `B+`; `A2 = Seiun Sky`, rank `A`; `B1 = Daiwa Scarlet`, rank `UG`;
  `B2 = Taiki Shuttle`, rank `UG` → sheet row "Grandparents" (§1.5).
- One spark: Grandparent A2 (Seiun Sky) `Kind=Blue`, `Category=Stat`, `Target=Stamina`, `Stars=3`
  → sheet row "Blue sparks: Stamina ★★★" (§1.5).
- `Affinity` left `Not recorded` — the source states no pair affinity for Vodka/Maruzensky, and the UI offers
  that option, so no improvisation was needed. (A positive finding: absence is a selectable state.)
- The five remaining ancestors' sparks (the source's §1.5 table carries roughly 60 rows across blue/pink/
  green/white) were **not** entered — see §7.1a item 3 and U-14.

**Source data with no UI home.**
**No UI representation found:** the ancestors' **epithets** (`Vodka [Wild Top Gear]`,
`Maruzensky [Formula R]`, `Daiwa Scarlet [Peak Blue]`, `Seiun Sky [Reeling in the Big One]`,
`Taiki Shuttle [Wild Frontier]`). The name field takes a bare name and the source's card identity is the
epithet; nothing here records which card the ancestor ran.
**No UI representation found:** the spark *values* beyond kind/target/stars (e.g.
`Ignited Spirit: Guts + ★☆☆`, `Resplendent Red Ace ★☆☆`) — `Category=Skill` accepts a skill, but the
observed `Target` list under `Category=Stat` is only the five stats; the pink-spaptitude targets were not
exercised this pass.
**No UI representation found:** the rank badges' meaning. The screen prints a `Rank (stars)` field beside
`Rank (letters)`, while §1.5 states these badges are the ancestor's end-of-run class, "not a spark grade".
No field says that, and nothing stops a user from double-recording the same fact as stars.

**Friction / errors / dead ends.**
- **The headings never update.** Both blocks stayed `Parent A Not chosen` and `Parent B Not chosen` after the
  names were typed and while the values were visibly held in the inputs. A user cannot tell from the heading
  whether the slot is filled.
- **`Guest` is representable — the answer to P-07 is yes** — but only inside the manual branch: a library
  veteran never sees the checkbox, and the label never uses the client's word "Guest" alone
  (`Guest / rented parent (not a local run)`).
- **The explainer contradicts itself about the ★★★ cap raise**: the table row says `+5, +12 or +21 … for one,
  two or three stars` and the paragraph immediately below says a three-star blue spark `lifts the cap … by
  16`. Both are on screen at once. The source's §1.2 cites ★3 = +16. F-06 (and S11 repeats the 16 figure).
- **The screen collects four grandparent slots but its own summary has no column for them.** `Projected
  start` prints `FROM PARENT A` / `FROM PARENT B` only, and states the parent column prints the recorded Blue
  Spark. So a grandparent's spark — exactly the two ★★★ the source names, Seiun Sky's Stamina and Taiki
  Shuttle's Power — is stored and never surfaced on the screen that claims to show what the tree adds.
  Verified live: after saving with A2's Stamina ★★★ recorded, all five rows still read `-` under both parent
  columns.
- The spark `Stars` input correctly exposes `valuemin=1 valuemax=3`, which proves the `valuemax="0"` reported
  on the *rank* stars inputs is not an app-wide convention but the accessibility tree's reading of an input
  with **no** `max` attribute. A screen-reader user is told `Rank (stars)` can hold only 0. F-07.
- `Add Spark` exists once per node and renders only inside the expanded `<details>`; the disclosure labels
  (`▸ Sparks for Grandparent A2`) are `<summary>` elements with a 44 px min-height hit area — good targets.

**Bounded probe (once, per §4.7).** Typed `3` into `Rank (stars)` for Parent A. Observed: the DOM accepted it
(`value="3"`), `min="0"`, `max=""` (unset), `step=""` (unset), `required=false`, `aria-invalid="false"`, no
constraint message. Then cleared it back to empty so the submitted value stayed source-faithful (the source
gives ancestor ranks as letters, not stars). **The final submitted value was the source value (empty).**
Read alongside the accessibility tree, this shows a field that accepts 0–9999 silently while announcing a
maximum of 0.

**Outcome / next screen.** `Save Legacy` → toast `Legacy recorded.`; the affinity note changed to `This setup
already holds a Legacy. Saving below replaces it whole; a Spark you remove here is gone, not hidden.`
(verified persistence on this screen: A2's spark read back `Target=Stamina`, `Stars=3`, and Parent B's Guest
checkbox stayed checked). Then `Continue to deck`. The button is disabled while the save is in flight, and
the first `Continue to deck` press was consumed during that window (logged in Appendix B). → S07.

### S07 — Deck step (`/career/setup/deck`)

**Route and trigger.** From S06 `Continue to deck`. Title `Build the six-card deck`, `Step 5 of 6`.

**What the screen asks for.** Sub-line `Six slots. Every card earns its place by the training it boosts,
the events it brings, or the friendship gauge it starts with.`; context `Scenario: Unity Cup`; an
advisory line `Strategic hint for Unity Cup: Wit cap is 1800. Consider a Wit-heavy deck.`; region
`Deck 0 of 6 slots filled` with the key explanatory paragraph `Not equipped. The six cards are the deck the
run starts with, so an empty slot here is a gap the Trainer can still fill. Position six is the one the
client labels Friends. The role belongs to the position and not to the card parked in it, so any card may
sit there. The owned or rented flag is kept in this setup draft, and Preflight writes it onto the run as one
value per slot.`; six slot rows — Slots 1–5 labelled `Slot N`, Slot 6 labelled **`Slot 6 · Friends`** with a
second tag **`Pal slot`** — each with `Not equipped.`, `Ownership N/A : No ownership has been recorded for
this slot yet.`, an `Owned` button and a `Rented` button (both `aria-pressed="false"`), and a
`Choose a card for Slot N` button; an `Add a card` button; two mechanics notes (`Two or more rainbow cards on
one tile is the largest multiplier in a training turn.`, `Bonuses multiply rather than add, so a +25% and a
+30% card give 1.25 × 1.30 = 1.625, not +55%.`); region **Deck diagnostics** with `Check / State` rows
`Six slots filled · 0 of 6`, `Duplicate card in the deck · None`, `Cards already at orange gauge` with a
disclosure `N/A , Not recorded. The setup draft has no friendship-gauge value per slot, and this step does
not read the client's in-run gauge.`, `Cards carrying a friendship bonus · 0 of 0`,
`Scenario link on a card · 0`, plus the gauge note `A card reaches friendship training at gauge 80, which
the client draws as the fourth mark and colours orange…`; region **Card types** with a `Type / What it does`
table listing `Speed`, `Stamina`, `Power`, `Guts`, `Wit` (`Boosts its own training tile; carries the
friendship kit.`), `Pal` (`No friendship bonus. Earns its slot on event chains: energy return, mood, failure
protection.`), `Group` (`SSR only. Full friendship kit plus a Wit friendship recovery; several characters
act as the one card.`), and the line `Seven types in total, and Group is a type rather than a flag on another
one. Global holds 115 SSR cards against JP's 312, so the two catalogues are not interchangeable.`; a
collapsed `Deck summary 0 assigned` disclosure with `Expand all card sections` / `Collapse all card
sections`; and a closing rule `Two identical cards cannot sit in one deck.` with `The client spends a
duplicate on a limit break instead, and the save here refuses a deck that repeats a card. The picker does
list a card the deck already holds, because the draft's own cards stay selectable so a slot can be re-set to
the card it has.`
The card picker is a modal dialog titled `Choose a card for Slot N`, containing `THE SEVEN SUPPORT TYPES`
(`Speed Stamina Power Guts Wit Pal Group`), a `Type` select (`All` + the seven), a `Name contains` search,
a `Filter` button, a checkbox `Owned only (show only cards marked owned in a saved run's deck)` with the
state readout `NOT RECORDED · Calculated · (no ownership recorded in any saved run)`, and an unpaginated
scrollable `list "Support cards, scrollable"` of name / type / `Equip` rows, some annotated
`<type> fits this slot`.

**Values entered + source trace.**
- Slot 1: search `Tokai Teio` → four Tokai Teio rows; equipped `Tokai Teio [Dream Big!]` (`Speed`,
  `SSR`, `Not a Scenario Link`, `Card effects (9)`, catalogue id 250 in the link `/support-cards/250`)
  → sheet "Deck card 1", §1.4. `Level = 35`, `Owned`.
- Slot 2: search `Nishino Flower` → equipped `Nishino Flower [Even the Littlest Bud]` (`Speed`, `SSR`,
  `Not a Scenario Link`, `Card effects (10)`) → sheet "Deck card 2". (Level not entered; the source gives
  30/30.)
- Slot 6 · Friends: search `Super Creek` → equipped `Super Creek [Piece of Mind]`, `SSR`, **`Stamina`**,
  `Not a Scenario Link`, `Card effects (8)` → sheet "Deck card 4 … **borrowed**". `Level = 50`, **`Rented`
  pressed** (the other five `Rented` buttons remained `aria-pressed="false"`).
- Slots 3, 4, 5: **not equipped** (Biko Pegasus, Matikanetannhauser, Haru Urara remain unentered — §7.1a).
- `Add a card` not pressed (its effect on the six-slot invariant was not tested).

**Source data with no UI home.**
**No UI representation found:** each card's **limit-break state**. One `Level` field (1–50) exists; the
source's `35/35`, `30/30`, `50/50` pairs are level/level, and the level cap differences come from breaks
(reference §1.4.2, context). No break field. U-08.
**No UI representation found:** the **catalogue-max vs currently-reached effect values** distinction the
source draws across two columns (§1.4). The picker stores identity; the row offers a `Card effects (N)`
disclosure of catalogue values only, and the screen's own diagnostics confirm the gap:
`The setup draft has no friendship-gauge value per slot, and this step does not read the client's in-run
gauge.` P-09, U-09.
**No UI representation found:** the fact that Super Creek is borrowed *from another account* as a person
relationship. `Rented` is the only available flag and the source calls it a friend card; the UI never uses
the word `Friends` as an ownership value, only as the slot label. P-08/P-11.

**Friction / errors / dead ends.**
- **`Pal slot` is used for the position while `Pal` is also a card type.** The same screen labels Slot 6
  `Slot 6 · Friends` **and** `Pal slot`, and its Card types table lists `Pal` as one of seven types with a
  distinct ruleset (`No friendship bonus…`). The source's borrowed card is a **Stamina** card, not a Pal, and
  the UI accepted it into the `Pal slot` with no warning, which confirms the screen's own prose
  (`any card may sit there`) and contradicts its own label. E-06; answers P-11 as a conflation, not a
  distinction.
- The `Strategic hint for Unity Cup: … Consider a Wit-heavy deck.` line is unconditional. This deck has no
  Wit card by design (§1.4: `Three Speed, one Stamina, two Guts, no Wit, no Pal`), so the advice is wrong
  for the run being recorded and cannot be dismissed. E-08.
- The picker rendered **251 rows** in one unpaginated, scrollable list while the Dashboard reports
  `SUPPORT CARDS 559`. Nothing explains the difference; narrowing required typing. E-07.
- After equipping, the slot row's **heading stays `Slot 6 · Friends` / `Pal slot`** and the Ownership label
  flips to `Owned` pressed by default, so a borrowed card silently reads as owned until the user notices the
  second button. The `N/A` → `Owned` auto-fill contradicts the screen's own care elsewhere
  (`A slot you have not classified reads as N/A rather than as Owned`, which S16 repeats).
- Duplicate prevention was **not** exercised (it would have required equipping a repeated card, i.e.
  creating state that then needs unwinding). The claim is recorded as stated by the UI, not verified.

**Bounded probe.** None beyond the ordinary search/filter path; the dialog was left in a clean state each
time. The empty-result behaviour was not probed.

**Outcome / next screen.** `Continue to preflight` (enabled at 3 of 6) → S08.

### S08 — Preflight (`/career/setup/preflight`)

**Route and trigger.** From S07 `Continue to preflight`. Title `Before the first turn`, `Step 6 of 6`,
`Back to deck`.

**What the screen asks for.** Sub-line `One screen listing what will be created, what the run will be
measured against, and the things worth knowing before you commit.`; region **This run** with
`TRAINEE Rice Shower`, `FORM as chosen above`, `SCENARIO Unity Cup`, `DISTANCE Medium`,
`PARENTS empty`, `DECK 3 of 6`, `TARGET TOTAL 7,000`, `CAREER LENGTH 72 turns` and the note `72 turns is
three career years of the client's own 24-turn grid. The post-December finale block sits outside that grid
and is not counted.`; region **Checks** — `All 5 entered steps are recorded.` and a
`CHECK / STATE / DETAIL` table: `Scenario · Pass · Unity Cup`;
`Trainee · Pass · Rice Shower ライスシャワー`;
`Your target · Pass · Story Clear · Turf Medium · Pace Chaser`;
`Legacy · Pass · No Affinity grade read · 0 of two parents recorded`;
`Deck · Warning · 3 of six positions carry a card` with a `Fix this` link back to `/career/setup/deck`;
plus `A warning is not a block.` and `The one warning here is a deck with empty slots, which is a legitimate
choice: the run commits either way and the warning is stated once, not repeated on every screen.`; region
**What this scenario asks** (`Scenario Unity Cup`; `Focus Wit carries the scenario's own cap bonus; team
rank and Spirit Burst counts decide the exclusive-skill payout.`; `Purpose Story Clear`;
`Course Turf Medium · Pace Chaser`; `Facility levels Rise with the team rank, not by repeated training.`;
`Fan gates` disclosure `N/A , This scenario's record states no fan gates.`; `Happy Meek` disclosure
`N/A , This scenario's record has no duel system.`; `Finale targets` disclosure `N/A , This scenario's
record states no finale target.`); region **What is not built yet** (`Per-discipline facility training counts
are not recorded, so no facility level can be shown.`; `No column records a Global ruleset version, so the
version is stated as an absence beside the ruleset it would qualify.`; `A deck position's owned-or-rented
flag is stored per position, and a position holding no card creates no deck row.`; `Ruleset version:`
disclosure `N/A , No source defines a Global ruleset version, so this snapshot names the absence rather than
a version.`); region **Commit** (`Creating the run writes the trainee, scenario, target, parents and deck.
Turn 1 is recorded from the cockpit, not here, so nothing is marked played until a turn is actually logged.`),
a checkbox `I understand this creates an Active run that cannot be un-created; retirement is the only
exit.`, the line `Acknowledge the above to enable "Create the run".`, and a disabled `Create the run`
button.

**Values entered + source trace.**
- Checkbox checked (a confirmation, not a datum).
- `Create the run` → the run was written with status **Active**, which is the nearest honest lifecycle value
  the flow offers for `mid-career, in progress` (§2.1). It was not marked Completed, and no Veteran or Legacy
  classification was applied. Logged I-10.
- **P-01 answered here and at S17**: this path has **no** mid-career/snapshot/partial state to select. There
  is no start-offset, no snapshot point, no turns-remaining field. The status vocabulary on the run itself
  (S20) is `Active / Completed / Retired` — so the least-wrong value exists and was taken, and the framing
  the flow imposes is "a run that is beginning". The app *does* acknowledge the other case elsewhere (S01's
  `Import an existing career` → `/career/snapshot`, `Import a historical career` → `/training-runs/import`),
  but neither is on this forward path. F-13, Blocking.

**Source data with no UI home.**
**No UI representation found:** `Snapshot point = Senior Year Early Oct` and `Turns remaining = 5`. Preflight
instead prints `CAREER LENGTH 72 turns` and S17 renders the recorded turn as `Junior · Early Jan`; S09 then
derives `Need 2 more bursts in 69 turns`. The tool's arithmetic says 69 turns remain where the source says
five. U-02, U-03.
**No UI representation found:** `Run number` — and **P-02 is answered in the negative**: no run number was
ever requested, so nothing was invented and no skip/unknown option was needed. The id `1` appeared only
afterwards, in the URL (`/training-runs/1/…`), a value the user never saw or chose (§4.1).
**No UI representation found:** `Potential Lvl 2`, `rarity ★3` as run data, the run's `Growth rate` row,
Mood, Energy, SP, the stat line and every Team Info value. None is summarised here.

**Friction / errors / dead ends.**
- **`PARENTS empty` and `0 of two parents recorded` are wrong**, and Preflight still marks the step `Pass`.
  Both parents were named on S06, retained in-form, and saved under a `Legacy recorded.` toast. F-05 (the
  loss is confirmed at S11) and F-10 (the Pass verdict on discarded data).
- `FORM as chosen above` is a placeholder that never resolves to `[Rosy Dreams]`. F-09.
- `TARGET TOTAL 7,000` is exactly `Sum of the five scenario caps 7000` from S05 — because the forced targets
  were set to those caps. The screen cannot tell a user that this total is an artefact of a validation
  failure three steps earlier.
- The `Deck · Warning` row is the only honest signal that the run departs from the source's deck, and it is
  correctly framed as a legitimate choice rather than an error.

**Bounded probe.** None: the forward control is a single destructive-commit action; an empty-field probe
here would have created a different run.

**Outcome / next screen.** → S09, `/training-runs/1/cockpit`.

### S09 — Cockpit, first visit (`/training-runs/1/cockpit`)

**Route and trigger.** From S08 `Create the run` (server redirect). Title `Career: Rice Shower`, `h1`
`Rice Shower`, status chip `Active`, eyebrow `COCKPIT`, `All careers` link.

**What the screen asks for.** Nothing; it is a read-only dashboard. Career nav: `Cockpit`, `Training`,
`Races`, `Skills`, `Events`, `Inheritance`, `Deck`, `Timeline`, `Result` (disabled, with
`Result: Available when the career is completed.`); region `No turn recorded yet` (`Energy, Mood, Fans,
Skill Points and the five stats are what this screen reads, and none has been entered for this run. Until
one turn is recorded the advisor has nothing to rank, so it declines rather than guessing.` + CTA
`Record the first turn through Training`); region **Stats and state** (`Bars are drawn against the scenario
ceiling.`; per stat `Speed N/A / 1,325 cap` … `Wit N/A / 1,800 cap` with `No turn has recorded this stat
yet.`; `ENERGY Energy N/A`; `MOOD N/A, not recorded`; `FANS` disclosure `N/A , Fans has not been recorded
for this run.`; `SKILL POINTS` disclosure `N/A , Skill Points has not been recorded for this run.`; a
disclosure `Mood effect at every tier`); a `Caps` strip repeating `1,325 / 1,322 / 1,368 / 1,308 / 1,800`;
`· Blue sparks · Stamina ★★★ (Grandparent · Parent A)` and the paragraph `The blue sparks the tree carries
and the caps the run states are shown together. The source records that they do not reconcile: the six blue
sparks account for the Guts extra and nothing else, so this panel shows the inputs without claiming one
derives the other.`; region **Scenario** (`Unity Cup`, `Guide held`, `Finale · Unity Cup: not yet reached`,
`Team · N/A, not recorded`, `League · N/A`, `Bursts · N/A`, `Team rank · not recorded`); region **Advisor**
(`PRIORITY Nothing can be prioritised until this turn's Energy is recorded.`,
`Not enough information to recommend an action.`, `Missing: Energy. Record it on this run and the ranking
re-runs against what you entered.`, an `OBLIGATIONS` table with `Junior Make Debut · ahead · Junior Year ·
Turn 12` and three rows `URA Finals Qualifier / Semifinal / Final (Aoharu) · unknown · no date`, a
`Why this answer` heading with `The advisory line is Energy 50, read as-is from a named source. Source:
qualitative only, read 2026-11-02.`); region **Where to go** with links `Training decision`,
`Race decision`, `Skills planner`, `Run deck`, `Timeline`, `Career result`, `Run settings`,
`Scenario detail`, `Team setup`; and a `Delete this run` link.

**Values entered + source trace.** None entered. Two persistence results read here: **the caps entered at S05
are already live** (`1,325 / 1,322 / 1,368 / 1,308 / 1,800`, not the published 1300) and **the single spark
recorded at S06 is printed** (`Stamina ★★★ (Grandparent · Parent A)`). Both were verified on this subsequent
application screen, not inferred.

**Source data with no UI home.**
**No UI representation found:** the deck's `Rented` provenance is not printed on the cockpit at all (it is
visible only at S16), and the source's other five blue sparks (Power ★★★, Speed ★★☆ ×2, Power ★☆☆,
Guts ★☆☆) are absent because they were not entered. U-14.
The `+30` figure the source carries (§1.8 `tooltip "All Attributes + 30"`) has no field anywhere in the pass;
the source itself records (§1.8 system note) that the `+30` is the **league-placement** bonus rather than the
Team Rank S reading, and the tool offers no way to express either attribution. U-17.

**Friction / errors / dead ends.**
- The panel asserts `the six blue sparks` while listing exactly **one**, and S11 reports
  `BLUE SPARKS · 0 recorded · 0★`. Whatever the prose's intent, it names a count that is not this run's
  stored data. F-08.
- `Bars are drawn against the scenario ceiling.` is false as printed: the bars are drawn against the
  **entered** ceilings (1,325 etc.), which are not the scenario ceiling. F-04.
- For a **Unity Cup** run, the Advisor's obligations are `URA Finals Qualifier`, `URA Finals Semifinal`,
  `URA Finals Final (Aoharu)`. The tool's own tooltips explain the `no date` state
  (`race_catalog_slots.turn is null`), but the scenario mismatch is unexplained. F-14.
- `Delete this run` sits on the run's primary screen, one click, while S08 warned that the run
  `cannot be un-created; retirement is the only exit`. Two screens state opposite things about reversibility.
  E-09, E-10.
- Positive: every unknown here is an explicit absence with a reason (`N/A` plus a disclosure), never a zero
  or a dash, which is the behaviour the repo's copy rules require.

**Outcome / next screen.** Nav link `Training` → S10.

### S10 — Training turn form (`/training-runs/1/training`)

**Route and trigger.** From S09 nav `Training`. Title `Pick this turn's action`.

**What the screen asks for.** Region **Disciplines**: radios `Speed training`, `Stamina training`,
`Power training`, `Guts training`, `Wit training`. Region **Other actions**: radios `Rest`, `Recreation`,
`Race`. Region **Why this action** (explanatory). Region **Recorded outcome**: `ENERGY AFTER`
(number `valuemin=0 valuemax=100`) with a mode select `Energy state` (`Exact`, `Band`, `Not recorded`) and a
`Energy band` select (`Low`, `Mid`, `High`); `MOOD AFTER` select (`Unchanged`, `GREAT`, `GOOD`, `NORMAL`,
`BAD`, `AWFUL`); `FANS` number (`valuemin=0`); `SKILL POINTS` number (`valuemin=0`);
`Turn *` number (required, `value="1"`, `valuemin=1`); `Speed *`, `Stamina *`, `Power *`, `Guts *`, `Wit *`
numbers (all required, `valuemin=0`, and **`valuemax` = this run's entered ceilings** `1325/1322/1368/1308/1800`);
`Speed level`, `Stamina level`, `Power level`, `Guts level`, `Wit level` numbers (`valuemin=1 valuemax=5`);
`Outcome` select (`Choose`, `Success`, `Failure`); `failure_rate` number (`valuemin=0 valuemax=100`);
`preview_gains[speed|stamina|power|guts|wit|sp]` numbers; buttons `Undo turn 1`, `Discard`, `Record turn 1`.

**Values entered + source trace.** All from the data sheet unless marked.
- `Speed training` radio → §1.7 (the last observed turn was on the Speed facility).
- `Turn = 1` (default) → **not present in the source — UI-required.** The source's snapshot is Senior Early
  Oct; the tool numbers this run's turns from 1. Logged I-11 with rejected alternatives.
- `Speed = 607`, `Stamina = 577`, `Power = 549`, `Guts = 736`, `Wit = 397` → sheet stat rows, §1.2.
- `Energy state = Band`, `Energy band = Low` → sheet row "Energy (low; no number)", §1.7. **No number was
  invented**: the UI offered a band, so §3.5's anticipated improvisation did not occur. P-19.
- `MOOD AFTER = GREAT` → sheet "Mood", §1.7. The option strings are the client's exact uppercase words.
- `SKILL POINTS = 173` → sheet "Unspent SP", §1.2. Accepted.
- `Speed level 4`, `Stamina level 4`, `Power level 4`, `Guts level 5`, `Wit level 4` → sheet "Facility
  levels", §1.7.
- Left empty, deliberately: `FANS` (the sheet never carried the source's `Fans 209,245`),
  `failure_rate` (source 39%, not in the sheet), all six `preview_gains` fields (source +18/+8/+6, not in the
  sheet), `Outcome` (the capture shows an offered tile, not a resolved one). Each is a **sheet gap**, so
  entering it would have meant reaching back into the source mid-walk, which §1.2/§3.2 forbid. Recorded at
  U-12 and U-13.

**Source data with no UI home.**
**No UI representation found:** the tile banner text `Floor Cleaning` and the `three card avatars on the
tile` (§1.7). No field names a tile's flavour text or its companion count.
**No UI representation found:** the client's energy warning strings — `Your trainee's energy is getting low,
so she's more susceptible to injury. Rest is important too.` and Rice Shower's own line
`Phew... I guess I'm just... a little tired...`. The Events screen (S18) is the only transcription surface
in the flow and it is scoped to training *events* with options, not to header warnings. U-11.
**No UI representation found:** `Turns remaining = 5` (P-20). The form asks for the turn's **index**, never
for a remaining count, and nothing on the screen rejects `Turn = 1` for a run whose client position is
Senior Early Oct.

**Friction / errors / dead ends.**
- The five stat inputs are validated against the **entered ceilings** (`valuemax=1325` etc.), which is the
  right behaviour and the clearest payoff of the ceiling fields; it also means the S05 helper text
  (`- / 1300 cap`) is stale in a way that has a consequence downstream. F-04.
- `SKILL POINTS` and `FANS` expose `valuemax="0"` in the accessibility tree (no DOM `max`), the same artifact
  as S06's rank inputs — a screen reader would read the SP field as unable to hold 173. F-07.
- One page mixes action selection, outcome, preview and facility state under four headings; requiredness is
  marked with `*` only on `Turn` and the five stats, and the required stat inputs are visually interleaved
  with a dozen optional ones. E-11.

**Bounded probe.** None. `Record turn 1` was pressed once with source values.

**Outcome / next screen.** `Record turn 1` redirected to `/training-runs/1/cockpit` (S13). → persistence read.

### S11 — Inheritance (`/training-runs/1/inheritance`)

**Route and trigger.** From S10; loaded as the direct target of the Cockpit's `Inheritance` nav href
(deviation §7.1a-1). Title `Choose what to inherit`.

**What the screen asks for.** Intro `Two parents and two grandparents behind each, per the six-ancestor rule.
A repeated ancestor contributes compatibility 0.`; an ancestry diagram listing `GRANDPARENT Daiwa Scarlet ·
recorded ancestor`, `GRANDPARENT Seiun Sky · recorded ancestor`, `PARENT A Not set · Unity Cup`,
`TRAINEE Rice Shower · Unity Cup`, `GRANDPARENT Daiwa Scarlet · recorded ancestor`,
`GRANDPARENT Taiki Shuttle · recorded ancestor`, `PARENT B Not set · Unity Cup`;
`COMPATIBILITY · Not recorded · raises trigger chance and payout`; two per-parent panels
`Parent A · Not set · · Unity Cup · This parent carries no recorded spark.` and
`Parent B · Not set · Guest · · Unity Cup · This parent carries no recorded spark.`; the note
`One selection per run is what this tool stores. The moment grants one spark per parent, but the run carries
a single legacy selection, so this screen lists the sparks each parent carries and the run's recorded
selection rather than promising per-parent radios the column cannot hold.`; a **Result of this pick** table
(`STAT / BEFORE / CHANGE / AFTER`) with `Speed cap (entered) 1325 · N/A · this tool computes no inheritance
change · 1325` (and likewise Stamina 1322, Power 1368, Guts 1308, `Wit cap (scenario) 1800`);
`BLUE SPARKS · 0 recorded · 0★`; `APTITUDE SHIFTS · none recorded this pick`;
`SKILL HINTS · none recorded this pick`; the paragraph `The cap raise is the loop. Two ★3 blue parents raise
a cap by 16 each at the start of a career, and this run's ceilings are already above its scenario's
published cap where an entered ceiling was recorded; inheritance adds to the entered value, never to the
published one.`; then `Legacy selection recorded`, `Skip this moment`, `Record inheritance`,
`Record what this moment granted (required to record)`, `Observed inheritance events (0)`,
`Inheritance milestones`.

**Values entered + source trace.** None. No inheritance moment was recorded: the source's inheritance has
already happened in the client and this pass had no moment to grant, so pressing `Record inheritance` would
have invented an event. `Skip this moment` exists for exactly this case and was left alone.
Read here as **persistence evidence**: the four grandparents came back (`recorded ancestor`), including the
repeated `Daiwa Scarlet` on both branches; **Parent A and Parent B came back as `Not set`**; and Parent B's
`Guest` flag **did** survive while its name did not.

**Source data with no UI home.**
**No UI representation found:** the ancestry **tree shape with the parents' own names**. The diagram renders
the two parent nodes as `Not set` placeholders around correctly-recorded grandparents, so the four
grandparents are shown as descending from nothing. The source's §1.5 tree (Vodka → Daiwa Scarlet/Seiun Sky;
Maruzensky → Daiwa Scarlet/Taiki Shuttle) is therefore only half representable once committed.
**No UI representation found:** the parents' ranks (`B+`, `UG`) — they belonged to the lost parent rows.
**No UI representation found:** the 50-odd unrecorded sparks, and the spark *identity* for green/white kinds
(the `Target` list observed under `Category=Stat` is the five stats).

**Friction / errors / dead ends.**
- The headline defect of the walk: **entered parents vanish between the draft and the run**, silently, and
  the only recovery is to re-do the setup step (which one-pass rules prevent) or find another editor — none
  was observed on this screen for *naming* a parent; the panel here is for selecting a moment's grant, not for
  re-entering ancestry. F-05, Blocking.
- `BLUE SPARKS 0 recorded · 0★` directly contradicts S09's `Blue sparks · Stamina ★★★ (Grandparent · Parent A)`
  for the same stored data. One of the two is wrong about the same tree; the discrepancy is reported as
  observed, not resolved. F-08 companion.
- The per-parent panel reads `Parent B · Not set · Guest ·` — an orphaned flag on an unnamed node, which is
  the most legible possible demonstration of F-05.
- `This tool computes no inheritance change` is stated in the `CHANGE` column of every row, and the note
  `Two ★3 blue parents raise a cap by 16 each` repeats the **16** figure that S06's own table contradicts
  with **+21**. F-06.
- Positives worth recording: `A repeated ancestor contributes compatibility 0` shows the tool models the
  same-card-twice case, which is P-06's answer, and the `N/A · this tool computes no inheritance change`
  column refuses to fabricate an outcome — the same discipline the source's §1.2 open question asks for.

**Bounded probe.** None (no field to probe without recording state).

**Outcome / next screen.** → S12 (Team setup), direct load of the Cockpit's `Team setup` href.

### S12 — Team setup (`/training-runs/1/team`)

**Route and trigger.** From S11. Title `Edit team state`; sub-line `What this run recorded about its Unity
Cup team. The form writes through the run's own update route, so an edit here is the same edit the Cockpit
made.`

**What the screen asks for.** `Team name` (text), `Team motto` (text), `League placement`
(number `valuemin=1 valuemax=99`), `Preseason wins` (number `valuemin=0 valuemax=4`), `Unity Trainings`
(number `valuemin=0`), `Spirit Bursts` (number `valuemin=0`), `Extreme Bursts` (number `valuemin=0`), then
**Team stat grades**: five selects labelled `Speed`, `Stamina`, `Power`, `Guts`, `Wit`, each offering
`Not recorded`, `G`, `F`, `E`, `D`, `C`, `B`, `A`, `S`; the rule line `Per-stat grades are G to S. S+ is the
overall team rank, recorded elsewhere.`; `Team Rank is recorded through a turn's team-race event, not
through this editor.`; and `Save team state`.

**Values entered + source trace.**
- `League placement = 8` → sheet "League placement", §1.8.
- `Preseason wins = 4` → sheet "Races won: 4 preseason rounds", §1.6. (`valuemax=4` happened to fit exactly.)
- `Unity Trainings = 55`, `Spirit Bursts = 6`, `Extreme Bursts = 5` → sheet rows, §1.8.
- `Speed = A`, `Stamina = A`, `Power = A`, `Guts = S`, `Wit = A` → sheet "Team stat grades", §1.8.
- `Team name`, `Team motto` left empty: the sheet carried no team name or motto row (the source's §1.6/§1.8
  give `Blue Bloom` / `Dreaming Big`), so entering them would have been a mid-walk return to the source.
  Logged as a sheet gap, not an improvisation.
- `Save team state` pressed. No inline confirmation appeared; the values were still present on re-read
  (`Preseason wins` read back `value="4"`), and they were verified on S13/S19.

**Source data with no UI home.**
**No UI representation found:** `Team members 20/20` — there is **no** member-count field anywhere in the
flow (P-22's residual) and no roster editor at all, so the source's 17 named guests plus the trainee, and
the two members clipped out of the capture, have no home (P-23, U-16). The source's own
roster-count discrepancy therefore cannot be recorded, let alone flagged.
**No UI representation found:** `Team Rank S (five stars)`. The screen says so itself:
`Team Rank is recorded through a turn's team-race event, not through this editor.` (U-17.)
**No UI representation found:** the ACE-per-distance roster assignment (§1.8: Sprint `Seeking the Pearl`,
Mile `Nishino Flower`, Medium `Tokai Teio`, Long the trainee, Dirt `Haru Urara`) and the five distance
columns.
**No UI representation found:** the `+30` bonus and its disputed attribution (§1.8 states the `+30` is the
league-placement bonus, not the Team Rank S reading). Nothing distinguishes the two.

**Friction / errors / dead ends.**
- The screen's vocabulary is inconsistent with the source's labels for the same facts: the field is
  `Extreme Bursts` where §1.8 prints `Extreme Spirit Bursts`, and `Preseason wins` where §1.6 names the four
  rounds individually.
- `Preseason wins` maxes at 4, which is this source's value; a run with a different preseason round count
  (a different scenario) would have nowhere to go, and the screen gives no hint why 4 is the ceiling.
- No save feedback of any kind was observed on this screen (corroborated by Appendix B probe 3). E-12.
- **P-26 answered cleanly and favourably**: per-stat grades stop at `S`, and the screen explicitly states
  `S+ is the overall team rank, recorded elsewhere.` That matches the reference guide's §2.2.2 rule
  (context, not evidence) and is the correct client semantics.
- **P-27 answered at S19** (the ladder), not here: this editor does not *derive* facility level from grade;
  it just collects the grade. The derivation lives on Scenario detail.

**Bounded probe.** None on the primary pass. The out-of-range probe was deferred to the secondary pass
(Appendix B, probe 5) so that no non-source value was submitted here.

**Outcome / next screen.** Saved in place; the walk continued to S13 by re-reading the Cockpit.

### S13 — Cockpit, second visit (`/training-runs/1/cockpit`)

**Route and trigger.** Direct load of the Cockpit URL after S12, to test persistence on a *different* screen
than the one that wrote the data.

**What the screen asks for.** Identical regions to S09, now populated. Nothing asked.

**Values entered + source trace.** None entered; read-back only. Observed verbatim:
`Speed 607 / 1,325 cap`, `Stamina 577 / 1,322 cap`, `Power 549 / 1,368 cap`, `Guts 736 / 1,308 cap`,
`Wit 397 / 1,800 cap`; `ENERGY · Low band`; `MOOD · GREAT ↑ +20%`; `FANS · N/A, Fans has not been recorded
for this run.`; `SKILL POINTS · 173`; `Scenario · Unity Cup / Guide held / Finale · Unity Cup: not yet
reached / Team · N/A, not recorded / League · 8 / Bursts · 11 (inside 10-12) / Spirit Burst Countdown · Need
2 more bursts in 69 turns for Gold Lv3 (Senior November payout). / Team rank · not recorded`;
Advisor `PRIORITY · Recover Energy before the next session.`, `Recommended action · Rest`,
`Energy is recorded as a low band, below the 50 advisory line; Rest is advised.`

**Source data with no UI home.** As at S09: no roster, no team rank, no members, no snapshot position.

**Friction / errors / dead ends.**
- **Persistence proved for the stat line, SP, mood, energy band and every team value** — each was read on a
  screen other than the one that entered it. This is the only persistence claim in the audit that rests on
  subsequent application screens.
- The band is not decorative: the Advisor consumed `Band/Low` and produced `below the 50 advisory line`, so
  a run whose client prints no Energy number is still actionable. Strongest positive finding of the pass (P-19).
- `Bursts 11 (inside 10-12)` is a derived sum of the two entered counts, and the band matches the source's
  own §1.8 reasoning (11 combined bursts → gold scenario skill at Lv1). Correct derivation, honestly shown.
- `Need 2 more bursts in 69 turns` embeds F-13: the tool believes 69 turns remain. The source says
  **5 turns left, 5 turns until the Unity Cup**. A trainer reading this line would plan a different career
  than the one being recorded.
- `Bars are drawn against the scenario ceiling` remains false (they are drawn against the entered ceilings).
  F-04.

**Bounded probe.** None (read-only screen).

**Outcome / next screen.** → S14 (Skills planner).

### S14 — Skills planner (`/training-runs/1/skills`)

**Route and trigger.** Direct load of the Cockpit's `Skills planner` href. Title `Spend 173 Skill Points`.

**What the screen asks for.** `Skill Point budget` — `SKILL POINTS 173`, `PLANNED SPEND 0`, `REMAINING 173`,
a `Learn planned skills` control, and the note `The budget bar prints the run's recorded SP beside the
plan's own sum, so the constraint is visible while browsing rather than discovered at commit.`; filters
`SEARCH`, `TYPE` (`Any` + classes), `HINTS` (`Any` + levels), `Clear`; a `Skills` list currently reading
`No skill is recorded on this run yet.`; a reference table **Why the hint ladder is not linear** with columns
`HINT LEVEL / DISCOUNT / CLIENT CAPTION` and five rows (`Hint Lvl 1 · 10% · Hint Lvl Hint Lvl 1`,
`Hint Lvl 2 · 20% · Hint Lvl Hint Lvl 2`, `Hint Lvl 3 · 30% · Hint Lvl Hint Lvl 3`,
`Hint Lvl 4 · 35% · Hint Lvl Hint Lvl 4`, `Hint Lvl Max · 40% · Hint Lvl Hint Lvl Max`), with the note
`The ladder steps +10, +10, +10, +5, +5, and it is not linear past Lv3. The measured sample reconciles
exactly: 130 → 104 / 91 / 84, and 160 → 144 / 128 / 112 / 96. The one rounding case is 130 × 0.65 = 84.5,
floored to 84. "Lv Max" is a caption, not a fifth number.`; and `No skill planned`, `Clear plan`,
`Build target, recorded skills and the add form`.

**Values entered + source trace.** None entered. Recording a *planned* skill would assert an intention the
source states only as §2.3's recommended spend, which the data sheet never carried; the eleven **owned**
skills are a different thing from a plan and there is no "record as owned" affordance in the region reached.

**Source data with no UI home.**
**No UI representation found (in this pass):** the eleven owned skills — `Blue Rose Closer Lvl 3`,
`Red Shift/LP1211-M`, `Corner Adept ○`, `Swinging Maestro`, `Nimble Navigator`, `Steadfast`, `Deep Breaths`,
`Extra Tank`, `Pace Chaser Straightaways ○`, `Shrewd Step`, plus `Corner Recovery ○` marked Obtained on the
client's Learn screen (§1.3). The screen models skills individually, so the count is not capped at the ten
visible chips (P-12 answered structurally), but no owned skill was recorded, and the pass cannot claim the
list holds eleven.
**No UI representation found:** hint *frequency* and per-skill hint levels as run data (the source's §1.4
`Hint Levels 2/3`, `Hint Frequency 40/50`). The `HINTS` filter exists, but the run-side value was never
shown. P-13 partial.

**Friction / errors / dead ends.**
- **Copy defect:** the `CLIENT CAPTION` column prints the prefix twice on all five rows —
  `Hint Lvl Hint Lvl 1`. The adjacent `HINT LEVEL` column already reads `Hint Lvl 1`. F-15.
- The ladder figures match the source's §2.1 exactly (10/20/30/35/40, the 130×0.65=84.5 floor case), and the
  screen cites them as measured rather than derived — a genuine strength.
- **P-14 answered yes structurally**: the screen separates *planned* (`PLANNED SPEND`, `Learn planned skills`,
  `No skill planned`) from *recorded* (`No skill is recorded on this run yet`, `recorded skills and the add
  form`), i.e. acquired vs planned is a real distinction in the model.
- The `173` budget flows from the turn record, which is the second confirmation that S10's data persisted
  through a third screen.

**Bounded probe.** None.

**Outcome / next screen.** → S15 (Races).

### S15 — Races (`/training-runs/1/races`)

**Route and trigger.** Direct load of the Cockpit's `Race decision` href. Title `Enter a race, or skip the
turn`.

**What the screen asks for.** Region `Available this turn` (`The calendar's races at this turn. Every row
states the grade, the surface and the distance band, and the trainee's own letter for that axis.`) currently
reading `No race on this scenario's calendar falls at this turn. The race calendar on the Cockpit header
shows which turns carry one.`; region `Consecutive races` (`Races in a row raise the failure risk. Trackblazer
publishes the odds; URA Finale and Unity Cup do not, so this screen states the count and not a probability.`)
with `Races in a row so far · not recorded`; a race form whose selects offer a position number `1…8`,
`Early`/`Late`, and a status select with options `NotOffered`, `Skipped`, `Entered`, `Completed`; buttons
`No race chosen`, `Undo turn 1`, `Train instead`, `Enter a race`, `Race not on the calendar`.

**Values entered + source trace.** None. The source's `Kyoto Daishoten (G2, Kyoto Turf 2400 m, Soft)` is
scheduled at Senior Early Oct; this run is at turn 1, which the calendar places in Junior Early Jan (S17).
Entering the Daishoten at turn 1 — via `Race not on the calendar` — would have created a race record dated to
a turn the client says is three years earlier, which §2.2 forbids. So the race-day data was left unrecorded.

**Source data with no UI home.**
**No UI representation found (this pass):** the four preseason round rows individually (R1 Junior Late Dec,
R2 Classic Late Jun, R3 Classic Late Dec, R4 Senior Late Jun) — only the aggregate `Preseason wins = 4` was
enterable (S12), and no per-round record could be created because each falls at a turn this run has not
played. P-15 partial; U-18.
**No UI representation found:** the pending finals as a *fixture*. `Finale · Unity Cup: not yet reached`
(S09/S13/S19) is a state, and the status vocabulary (`NotOffered`, `Skipped`, `Entered`, `Completed`) can
express a race that has not been run — but the source's finals are **five races** (§8.3) with no date in
these frames, and no structure here holds "five races, after Senior Late Dec". P-16 partial; U-19.

**Friction / errors / dead ends.**
- The screen's anti-fabrication note is exemplary and worth quoting in the audit's favour column:
  `No probability is printed for this scenario. Inventing one would be the "score" the app is forbidden to
  add.`
- The real friction is structural, not local: because F-13 forces the run to start at turn 1, every race fact
  in the source (which is 24 turns from the end of the grid) becomes unrecordable *in sequence*. The
  `Race not on the calendar` affordance exists precisely to break that lock, and a user would reach for it —
  this pass deliberately did not.
- `Races in a row so far · not recorded` is an honest absence.

**Bounded probe.** None: the one available probe (submitting an invalid race) would have written a race row.

**Outcome / next screen.** → S16 (Run deck).

### S16 — Run deck (`/training-runs/1/deck`)

**Route and trigger.** Direct load of the Cockpit's `Run deck` href. Title `This run's six slots`.

**What the screen asks for.** The six slots, each with the equipped card (name link to
`/support-cards/<id>`), `SSR`, type, scenario-link badge, a `Card effects (N)` disclosure, a `Level` number
input (`valuemin=1 valuemax=50`), `Ownership` with `Owned`/`Rented` buttons, `Replace in Slot N`, `Remove`;
unfilled slots read `Not equipped.` and `Ownership N/A : No ownership has been recorded for this slot yet.`;
Slot 6 is labelled `Slot 6 · Friends` **and** `Pal slot`. Header notes: `The deck is run-scoped: changing it
here does not touch any other career. Effects apply from the next turn.`;
`A deck is six cards. Position six is the friend slot, and that role belongs to the position: any card may
sit in it.`; `The owned or rented flag is saved with the deck, one value per slot, and stays on the run after
a reload. A slot you have not classified reads as N/A rather than as Owned.`; a count `3 of 6 slots filled`;
and a `Cancel` / `Save deck` pair with the warning `Saving replaces the whole deck: slots left empty are
cleared`.

**Values entered + source trace.** None (primary pass). This screen was read to **test persistence** of S07.

**Source data with no UI home.**
Persisted correctly: the three equipped identities, their types, `Card effects (9/10/8)` counts, and the
**`Rented` flag on slot 6** (`aria-pressed="true"`, the only one).
**Lost between S07 and here:** the deck **levels**. `Level for Slot 1`, `Level for Slot 2` and
`Level for Slot 6 · Friends` all read `value=""`, although Slot 1's level had been verified as `35`
*in the setup screen* (`spinbutton "Level for Slot 1" … value="35"`) and Slot 6's was entered as `50`. No
warning accompanied the loss, at Preflight or here. **F-11, Blocking.**

**Friction / errors / dead ends.**
- F-11's shape matters for the audit's central question: the wizard collected a value the run model
  *supports* (the run-deck screen renders the same input with the same 1–50 range) and then discarded it,
  while keeping the harder-to-implement per-slot ownership flag. The loss is not a missing feature; it is a
  dropped transfer.
- `Pal slot` reappears here, still mislabelling a position that holds a Stamina card, and still alongside the
  screen's own `any card may sit in it` correction. E-06.
- The run-deck screen offers what the wizard silently dropped, so a user who spotted the loss could recover
  here — which is why the deck row's `Saving replaces the whole deck` warning is worth noting: recovering the
  levels here would re-write all six positions.
- `Effects apply from the next turn.` is a useful semantic promise, and one the source's mid-career position
  quietly breaks (this run's cards have been "in effect" for 24 turns of client play).

**Bounded probe.** None here; the reload that followed belongs to the secondary pass (Appendix B, probe 1)
and found the same empties, which is what confirmed the loss is stored rather than a render artifact.

**Outcome / next screen.** → S17 (Timeline), reached by deep link in the secondary pass.

### S17 — Timeline (`/training-runs/1/timeline`)

**Route and trigger.** Deep link (secondary pass, probe 4) to the Cockpit's `Timeline` href. Title
`Every logged turn, race and event`.

**What the screen asks for.** Counters `TURNS RECORDED 1`, `RACES ENTERED 0`, `EVENTS 0`,
`FINALE Not reached`; filters `KIND` (`All entries`, `Turn`), `YEAR` (`All years`, `Junior`), `Clear`; a
record table with columns `TURN / POSITION / KIND / WHAT HAPPENED / ENERGY / SP` and one row
`1 · Junior · Early Jan · Turn · Turn recorded · Recorded as entered · [Edit turn 1] · N/A · N/A`;
`1 entries · showing 1` with `Previous`/`Next`; the note `Turn numbers are the run's own; the position is
the calendar's label for that turn. Energy and SP print N/A where the import did not capture them: an N/A is
"not recorded", never zero.`; and **Phase grouping** — `Collapsed by phase, the shape a 72-turn run needs.`
— with `Junior Year · 1 entries` (`Turns 1-24. Duration unknown for most entries; the Debut race is the one
dated obligation.`), `Classic Year · 0 entries`, `Senior Year · 0 entries`,
`After the grid · finale block (0)`.

**Values entered + source trace.** None.

**Source data with no UI home.**
**No UI representation found:** the row's own data is the finding — the turn was recorded with `Energy band =
Low` and `SKILL POINTS = 173`, yet this screen prints `N/A` in both columns while the Cockpit (S13) prints
`Low band` and `173` for the same run. F-12.
**No UI representation found:** `Senior Year Early Oct`. The turn's `POSITION` is *computed* as
`Junior · Early Jan` from the turn index, so the client's position is not a value a trainer can set; and the
phase grouping is built for a 72-turn run, which the screen says out loud.

**Friction / errors / dead ends.**
- `Turn recorded · Recorded as entered` is a low-information description; the row does not say the action was
  `Speed training`, does not show the stat line, and the only way to see what was entered is `Edit turn 1`
  (not opened, one-pass rule).
- The ENERGY/SP `N/A` is the clearest instance in the walk of two screens disagreeing about one record. The
  screen's own justification names *imports* (`where the import did not capture them`), but this turn was not
  imported — it was entered through S10. Reported as observed; the possible reading (the columns expect exact
  values and a band is not one) is labelled as interpretation, not established.
- Positive: `an N/A is "not recorded", never zero` is exactly the discipline §2.2 of the walk brief asks the
  source's ambiguities to be met with.

**Bounded probe.** Superseded: this screen was reached in the secondary pass, so its evidence is labelled
Appendix B, probe 4.

**Outcome / next screen.** → S18 (Events).

### S18 — Events (`/training-runs/1/events`)

**Route and trigger.** Direct load of the nav `Events` href. Title `A training event is asking`.

**What the screen asks for.** `What the event shows`; a name field with the aid
`Name the event as the client shows it` and state `Source not set`; the statement `Event text is the
client's own copy. This tool does not transcribe or paraphrase it; the fields below record the words the
client shows, so the screen never invents event copy.`; `A real event screen prints what the client prints.
What is stored here is the trainer's own transcription of it.`; an `OPTIONS` area with
`The option's own words`, `Selecting shown; outcome not predicted.`, `No recorded choices for this event
yet. The first entry becomes the first known outcome.`; `No option predicts its outcome. The app is forbidden
to invent a score, a percentage or a "best pick" for an event whose results no source publishes. It records
the choice and the effect you report afterwards.`; then `Where this event came from (required to record)`,
`Record the outcome`.

**Values entered + source trace.** None. This run's captured turn carries no training event with options, so
there was nothing to transcribe.

**Source data with no UI home.**
**No UI representation found:** the client's spoken lines and warning copy
(`Your trainee's energy is getting low, so she's more susceptible to injury. Rest is important too.`;
`Phew... I guess I'm just... a little tired...`). This screen is the transcription surface, but it is scoped
to *events that are asking*, with options; an energy warning printed on the training header is not such an
event. U-11.

**Friction / errors / dead ends.**
- The screen is honest to the point of usefulness: it explicitly refuses to paraphrase or score client copy,
  which is the behaviour the walk brief's fidelity rules require of an auditor too.
- Friction is reachability, not behaviour: a trainer with the source's energy warning has no obvious place to
  put it, and this screen's title (`A training event is asking`) reads as inapplicable to a header string, so
  the user is unlikely to try.

**Bounded probe.** None.

**Outcome / next screen.** → S19 (Scenario detail).

### S19 — Scenario detail (`/training-runs/1/scenario`)

**Route and trigger.** Direct load of the Cockpit's `Scenario detail` href. Title `Scenario detail`.

**What the screen asks for.** `The race calendar renders in this screen's Races in this run column: this
run's recorded races, the turn being decided, and the mandatory races still to come.`; a **Team Race** note
(`Aim for at least 3 circles as a margin, not a win condition. A loss lowers the league rank, and after the
2026-07-01 rework a loss can be retried with an Alarm Clock.`) and
`No team races recorded. Circles are entered on the race form as the number the client showed before you
committed.`; a **Team** block printing `League placement 8`, `Preseason rounds won 4`, `Current Spirit N/A`
with `Burst readiness is not computed: the bands below are thresholds, but no Spirit reading is recorded, so
there is nothing to set against them.` and `Recommended timing and projected benefit are not built.`; a
**Team Rank** block — `Rank not recorded: the league letter is read off the client, and this run has none
entered yet.` — with the ladder `G lv 1 · F lv 1 · D lv 2 · E lv 2 · B lv 3 · C lv 3 · A lv 4 · S lv 5` and
the line `The ladder carries S+ above S: it grants a second hint rather than a higher facility, so it has no
level to show.`; `Unity Cup progress · Unity Trainings 55 · Spirit Bursts 6 / 5 Extreme`; a
**Team stat grades** read-back (`Speed A`, `Stamina A`, `Power A`, `Guts S`, `Wit A`); a **Burst count bands**
table (`Total bursts / Reward`: `4–6 · White hint Lv1, +10 matching stat, +10 SP`;
`7–9 · White hint Lv3, +20 matching stat, +20 SP`; `10–12 (this run) · Gold hint Lv1, +3…`);
`Finale: Unity Cup. Not yet reached.`; a note `No column records who this run fields in each category, so
the board states the ask and not the run.`; and a **Trainee goals** list:
`Participate in Junior Make Debut · Upcoming · Junior year, turn 12 · Record this race`;
`Place top 5 in Spring Stakes · Upcoming · Classic year, turn 6 · Record this race`;
`Place top 5 in Tokyo Yushun (Japanese Derby) · Upcoming · Classic year, turn 10 · Record this race`;
`Place top 3 in Kikuka S…`, and further entries including an Arima Kinen row present in the page text.

**Values entered + source trace.** None entered; read-back only. Everything in the Team block read back
exactly as entered at S12, on a **third** screen — the strongest persistence confirmation in the pass.

**Source data with no UI home.**
**No UI representation found:** `Team Rank S (five stars)` still — `Rank not recorded`, and the only route is
a team-race turn event that F-13 blocks (U-17).
**No UI representation found:** the three **cleared** objectives and the one **active** objective as a state
the trainer sets (P-17). The `Trainee goals` block is *derived from the catalogue for Rice Shower*, not from
this run's client capture: it opens with `Participate in Junior Make Debut`, which is a Junior-year goal, and
every row reads `Upcoming` because the run has no race records. The source's actual goal state —
`Nikkei Sho top 3`, `Tenno Sho (Spring) 1st`, `Takarazuka Kinen top 3` cleared and
`Place 1st in Arima Kinen · Senior Late Dec · 5 turns` active — cannot be expressed, because marking a goal
met requires a race record at a turn this run cannot reach. U-20.
**No UI representation found:** the scenario's full label (U-01) — `Unity Cup progress` only.

**Friction / errors / dead ends.**
- **P-27 answered, and in the app's favour**: the per-stat grade → facility level rule is *stated* here
  (`A lv 4`, `S lv 5`), which is exactly the source's §1.7/§1.8 observation (Guts 5 because team Guts is S).
  It is reference material, however, not a derivation applied to the run: `Per-discipline facility training
  counts are not recorded, so no facility level can be shown` (S08) and the facility levels I entered at S10
  appear nowhere on this ladder. So the rule is documented and disconnected.
- `Aim for at least 3 circles as a margin, not a win condition.` and the Alarm Clock note are scenario advice
  with no corresponding field (`circles` are entered on the race form, which F-13 makes unreachable at turn
  1).
- The ladder lists `G lv 1` and `F lv 1` at the same level and `D lv 2`/`E lv 2`, `B lv 3`/`C lv 3` — a
  compressed mapping shown without a key, so a reader cannot tell whether the duplication is a rounding rule
  or a data defect. Interpretation flagged, not asserted.

**Bounded probe.** None (read-only).

**Outcome / next screen.** → S20 (Run settings).

### S20 — Run settings (`/training-runs/1/settings`)

**Route and trigger.** Direct load of the Cockpit's `Run settings` href. Title `Rice Shower`, section
`Run identity`.

**What the screen asks for.** Header line `What this run was recorded as. Every field writes through the
run's own update route, so an edit here is the same edit the Cockpit made.`; a `Status` select
(`Active`, `Completed`, `Retired`) + `Change status`; a `Scenario` select (`No scenario set`, `URA Finale`,
`Unity Cup`, `Trackblazer`, `Our Grand Concert`) + `Change scenario`; a **Trainee rarity and potential**
block with a `Rarity` select (`Not stated`, `★1`, `★2`, `★3`) and a `Potential Level` number input; a
**Growth rate** block with five number inputs `Speed %`, `Stamina %`, `Power %`, `Guts %`, `Wit %`; a
**Stat ceilings** block with five number inputs each labelled with the published cap
(`Speed · Scenario 1300` etc.); and `Save identity`.

**Values entered + source trace.** None entered. This screen was reached last, and writing here would have
started a second pass on data the primary pass had already committed elsewhere; the pass ends as a read.

**Source data with no UI home — reframed, and this is the screen's importance.** Several data the §2.3 sheet
predicted as homeless **do** have fields here; they are simply not on the path the wizard takes:
- `Potential Lvl 2` → `Potential Level` field exists, currently empty (U-04 revised: field exists, never asked).
- `Rarity ★3` → `Rarity` select exists, empty (U-05 revised).
- The run's `Growth rate` row `+0/+10/+0/+20/+0` → five `%` fields exist, empty (U-06 revised; and the S04
  `Growth row` is the catalogue's copy of the same numbers, so the run-side field is *separate* from the
  read-only display).
- The ceilings → present and correct (`1325/1322/1368/1308`, with the scenario `1300` printed beside each).
**No UI representation found anywhere in the pass, including here:** a notes/freeform field. No
`Notes`/`Description` control appeared on any of the 20 screens, so the brief's "legitimate notes field"
escape does not exist, and the unrepresentable source data has nowhere to go (U-22).

**Friction / errors / dead ends.**
- **S03 told the user otherwise.** The trainee picker's `Selected` panel says
  `The form you pick sets this run's rarity and potential.` Here, Rarity reads empty and `Potential Level`
  reads empty for the very form that was supposedly setting them. Either the sentence is false or the
  transfer is missing; both readings are a defect, and the fields being *here* and *not asked* is the
  milder version. F-16.
- **S02/S08 said the scenario cannot be changed** (`It cannot be changed after the first turn`; reversibility
  `Scenario · No`), and this screen offers `Change scenario` with all four scenarios and a
  `No scenario set` value. Contradiction between screens on the run's most consequential choice.
- **`Status: Active / Completed / Retired` contains no mid-career or snapshot value**, which closes P-01
  from the other side: the closest available state is `Active` (already set at commit), and there is no way
  to say "this run began in progress".
- Positive: the ceiling block prints `Scenario 1300` beside the entered `1325` — precisely what S06's prose
  promised (`the cockpit shows an entered ceiling beside the scenario's rather than one number`). This is the
  one place the walk's cap-arithmetic question (P-03) is handled exactly as documented.

**Bounded probe.** None.

**Outcome / next screen.** Walk ends here. `Result` is gated; `/preferences` was not entered.

## Improvisation log (§7.4)

Every value below is absent from the §2.3 data sheet. This table exists so the fabrications are auditable in
one place, as the brief requires.

| # | Screen | Field / control | Value chosen | Why this value | Alternatives rejected |
| --- | --- | --- | --- | --- | --- |
| I-01 | S05 | `Purpose` | `Story Clear` | Validated as required (`The purpose field is required.`) after two refused submits; sheet carried no purpose. A run being carried to its scenario's finals is clearing the scenario. | `Champions Meeting` (no meeting in the remaining five turns); `Parent Farming`; `Skill Farming` (§2.3's recommended spend is a residual, not the run's purpose); unset (refused) |
| I-02 | S05 | `Surface` | `Turf` | Required. Derived from the sheet's own row "Race scheduled this turn = Kyoto Daishoten (G2, **Kyoto Turf** 2400 m, Soft)" and from all four won preseason rounds being turf. | `Dirt` (source `Dirt G`; §1.6 refuses the dirt G1 as "the worse ride"); unset (refused) |
| I-03 | S05 | `Style` | `Pace Chaser` | Required. `Pace chaser A` is the one style letter agreeing between the source (§1.2 `Pace A`) and the catalogue (picker `Pace chaser A`), so the choice does not depend on the unresolved aptitude dispute. | `Front Runner` (`Front A` vs `Front runner B` — would silently resolve §2.2 item 2); `Late Surger` (`Late B` vs `Late surger C`); `End Closer` (`End G` in both); unset (refused) |
| I-04 | S05 | `Distance` | `Medium` | Not required; chosen to exercise the band's effect. Derived: the sheet's Daishoten row is 2400 m and this screen's own band text reads `Medium 1801–2400 m`. | `Long 2401 m and up` (2400 is not above 2401); `Mile`; unset (available — and the choice proved inert: `The chosen scenario publishes no stamina floor per distance`) |
| I-05 | S05 | `Speed` (target) | `1300` | The form refused all-five-empty and demanded a value. Chosen as the screen's own printed scenario cap, which is also the maximum the input accepts, so no number came from outside the app. | source current stat `607` (a target is a goal; entering the already-reached value asserts a met goal — §4.4 field-misrouting); source cap `1325` (exceeds `valuemax=1300`); blank (refused — F-01) |
| I-06 | S05 | `Stamina` (target) | `1300` | As I-05. | source `577`; source cap `1322` (> max); blank |
| I-07 | S05 | `Power` (target) | `1300` | As I-05. | source `549`; source cap `1368` (> max); blank |
| I-08 | S05 | `Guts` (target) | `1300` | As I-05. | source `736`; source cap `1308` (> max); blank |
| I-09 | S05 | `Wit` (target) | `1800` | As I-05; the published Wit cap equals the source's printed Wit cap, so this one is coincidentally faithful. | source `397`; blank |
| I-10 | S08 | `I understand this creates an Active run…` | checked | §3.5 anticipated a forced "completed" framing and directed choosing the non-completing option. `Active` is the only non-terminal status in the flow (S20 confirms the set is `Active / Completed / Retired`), so mid-career is preserved as far as the model allows. | marking Completed (forbidden by §2.2); Veteran/Legacy classification (never offered, never applied); abandoning the walk (no forward control was withheld) |
| I-11 | S10 | `Turn` | `1` (field default) | Required. The tool numbers this run's turns from its own creation; the source's position is Senior Early Oct, roughly turn 60 of a 72-turn grid. The default was accepted rather than fabricating an index. | `60` or similar (§1.1 gives a calendar position, not a turn number; converting is derivation); blank (refused) |
| I-12 | S10 | `ENERGY AFTER` (number) | **not entered** — `Energy state = Band`, `Energy band = Low` | No improvisation was needed: the UI offered a band, which represents "low bar + warning text, no number" without inventing a figure. §3.5's anticipated Energy improvisation did not occur. | any exact number (fabrication); `Not recorded` (would discard the observable low-energy state and leave the Advisor blind — it had said `Missing: Energy`) |
| I-13 | S10 | `FANS`, `failure_rate`, `preview_gains[*]` | **not entered** | The sheet carried no Fans, failure-rate or preview-gain row; entering them meant returning to the source mid-walk (§1.2/§3.2 forbid). The fields are optional and render as `N/A` elsewhere. | re-reading §1.7 mid-walk for `39%`, `+18/+8/+6`, `209,245` |
| I-14 | S12 | `Team name`, `Team motto` | **not entered** | Same reason as I-13: no sheet row. Recorded as a sheet-coverage gap rather than invented. | entering `Blue Bloom` / `Dreaming Big` from the pre-walk read |

## Findings register (§7.5)

`Blocking` = it prevented a legitimate user from continuing, or forced incorrect data into the system to
proceed.

| ID | Type | Screen(s) | Short description | Blocking? |
| --- | --- | --- | --- | --- |
| F-01 | Functional | S05 | Build target mandates all five stat targets plus Purpose/Surface/Style while its own copy calls the step skipped and says the fields start empty; the error `Enter a target for every stat, or leave the whole target unset.` is shown on empty targets and the unset branch it offers is refused (proved by clearing the ceilings and resubmitting) | **Yes** — forced five invented numbers |
| F-02 | Functional | S03, S04 | The `Form` dropdown lists 2 costume forms while the profile lists 3 (`Yummy Dreamy Fairy` appears only on the profile); the badge reads `2 forms` | No |
| F-03 | Functional | S03 | After `Select`, the search box keeps the term `Rice` while the list reverts to all 66 unfiltered rows and the query string is dropped | No |
| F-04 | Functional | S05, S09, S13 | Entered ceilings never reach the cap text: the helper stays `- / 1300 cap`, the sample total stays `Sum of the five scenario caps 7000`, and the Cockpit claims `Bars are drawn against the scenario ceiling.` while drawing against the entered 1,325/1,322/1,368/1,308 | No |
| F-05 | Functional | S06 → S08 → S11 | Manually named parents are silently lost at commit: retained in-form and saved under `Legacy recorded.`, yet Preflight prints `PARENTS empty` / `0 of two parents recorded` and Inheritance prints `PARENT A Not set` / `PARENT B Not set` — while all four grandparents persisted and Parent B's `Guest` flag survived without its name | **Yes** — entered data destroyed |
| F-06 | Functional | S06, S11 | Two different ★★★ blue-spark cap raises on screen: `+5, +12 or +21 … for one, two or three stars` beside `A Blue Spark that rolls three stars lifts the cap … by 16`; S11 repeats the 16 | No |
| F-07 | Functional | S06, S10, S12 | Number inputs with no `max` attribute expose `valuemax="0"` to the accessibility tree (`Rank (stars)`, `Grandparent rank (stars)`, `FANS`, `SKILL POINTS`, `Unity Trainings`, `Spirit Bursts`, `Extreme Bursts`), telling AT users the field holds only zero. The spark `Stars` input, which does set min/max, correctly reports `1…3` — proving the cause | No |
| F-08 | Functional | S09, S11 | Cockpit prose asserts `the six blue sparks account for the Guts extra and nothing else` while listing one recorded spark, and Inheritance reports `BLUE SPARKS 0 recorded · 0★` for the same tree | No |
| F-09 | Functional | S08 | Preflight prints `FORM · as chosen above` — an unresolved placeholder where `[Rosy Dreams]` belongs | No |
| F-10 | Functional | S08 | Preflight marks the `Legacy` check `Pass` in the same row reporting `0 of two parents recorded`, certifying a step whose data was discarded | No |
| F-11 | Functional | S07 → S16 | Deck card levels entered (Slot 1 `35` verified in-form, Slot 6 `50`) arrive at the run record empty on all three equipped cards; the per-slot `Rented` flag did persist, so the loss is a dropped transfer, not a missing feature | **Yes** — silent data loss |
| F-12 | Functional | S13 vs S17 | Timeline prints `ENERGY N/A` and `SP N/A` for turn 1 while the Cockpit prints `Low band` and `173` for the same record; the screen's stated reason covers imports (`where the import did not capture them`) but this turn was entered through the UI | No |
| F-13 | Functional | S05, S08, S13, S17 | No mid-career entry point on this path: the wizard commits a 72-turn grid (`CAREER LENGTH 72 turns`), auto-positions turn 1 as `Junior · Early Jan`, and derives `Need 2 more bursts in 69 turns` where the source states 5. Every race, goal and round fact in the source sits at a turn this run cannot reach | **Yes** — forces a "beginning career" frame |
| F-14 | Functional | S09 | Advisor `OBLIGATIONS` for a Unity Cup run lists `URA Finals Qualifier`, `URA Finals Semifinal`, `URA Finals Final (Aoharu)` | No |
| F-15 | Functional | S14 | Skills planner `CLIENT CAPTION` prints the prefix twice on all five rows: `Hint Lvl Hint Lvl 1` … `Hint Lvl Hint Lvl Max` | No |
| F-16 | Functional | S03 vs S20 | The picker states `The form you pick sets this run's rarity and potential.`; Run settings holds a `Rarity` select and a `Potential Level` input, both empty for this run | No |
| F-17 | Functional | S02/S08 vs S20 | Scenario immutability asserted twice (`It cannot be changed after the first turn`; reversibility `Scenario · No`) while Run settings offers `Change scenario` including `No scenario set` | No |

| E-01 | Experiential | S05 | Required fields undiscoverable until a submit fails: no asterisk, no `required` attribute, `Continue to legacy` enabled on arrival | — |
| E-02 | Experiential | S03 | Filtered intro reads `1 trainee are catalogued.` (the count line is correct: `1 trainee · page 1 of 1`) | — |
| E-03 | Experiential | S03, S04 | One label, two different actions: `Continue to build target` on the picker opens the profile (still step 2); the same label on the profile advances to step 3 | — |
| E-04 | Experiential | S03 vs S04 | Aptitude naming changes case between consecutive screens (`Pace chaser A` vs `Pace Chaser A`; `Front runner B` vs `Front Runner B`; `Late surger C` vs `Late Surger C`) | — |
| E-05 | Experiential | S04 | A region headed `Scenarios this trainee is linked to` lists all four as available and its own note withdraws the claim (`No per-trainee link list is carried on this payload`) | — |
| E-06 | Experiential | S07, S16 | Slot 6 carries `Slot 6 · Friends` **and** `Pal slot` on a screen that also lists `Pal` as one of seven card types; the position accepted an `SSR Stamina` card with no warning — prose right, label wrong | — |
| E-07 | Experiential | S07 | The picker renders 251 rows in one unpaginated scrollable list while the Dashboard reports `SUPPORT CARDS 559`; nothing explains the difference and narrowing needs typing | — |
| E-08 | Experiential | S07 | `Strategic hint for Unity Cup: Wit cap is 1800. Consider a Wit-heavy deck.` is unconditional and contradicts this run's deck (`Three Speed, one Stamina, two Guts, no Wit, no Pal`); it cannot be dismissed | — |
| E-09 | Experiential | S09 | `Delete this run` sits on the run's primary screen, one click from the nav | — |
| E-10 | Experiential | S08 vs S09 | Contradictory reversibility claims two screens apart: `cannot be un-created; retirement is the only exit` beside a `Delete this run` link | — |
| E-11 | Experiential | S10 | One page mixes action choice, outcome, preview and facility state under four headings, with required `*` on only six of ~20 inputs | — |
| E-12 | Experiential | S12 | No save confirmation of any kind on the Team editor (re-checked in Appendix B probe 3) | — |
| E-13 | Experiential | S11 | A table headed `Result of this pick` answers every row with `N/A · this tool computes no inheritance change` — honest, but the heading promises an output the tool declines to produce | — |
| E-14 | Experiential | S06 | Recording the source's spark table is a 6-field, 2-click-per-row task (~60 rows across six ancestors) and nothing on the screen tells the trainer the cost before they start | — |

| ID | Type | Screen(s) | Short description | Blocking? |
| --- | --- | --- | --- | --- |
| U-01 | Unrepresented | S02, S08, S09, S13, S19 | Scenario full label `Unity Cup: Shine On, Team Spirit!` never appears; only `Unity Cup` | — |
| U-02 | Unrepresented | S05, S08, S17 | Snapshot point `Senior Year Early Oct` has no field; turn 1 is auto-positioned `Junior · Early Jan` | — |
| U-03 | Unrepresented | S05, S08, S13 | `Turns remaining = 5` has no field; the tool derives 69 | — |
| U-04 | Unrepresented | S03, S20 | `Potential Lvl 2`: a `Potential Level` field exists only on Run settings and the wizard never asks for it | — |
| U-05 | Unrepresented | S03, S20 | Rarity `★3` as run data: a `Rarity` select exists only on Run settings and was never populated | — |
| U-06 | Unrepresented | S04, S20 | The run's `Growth rate` row: five `%` fields exist only on Run settings; S04's `Growth row` is the catalogue's read-only copy of the same numbers | — |
| U-07 | Unrepresented | S05 | `Wit cap 1800` as an entered ceiling: the screen prints `No enterable ceiling for this stat` | — |
| U-08 | Unrepresented | S06 | Ancestors' epithets (`[Wild Top Gear]`, `[Formula R]`, `[Peak Blue]`, `[Reeling in the Big One]`, `[Wild Frontier]`) — name-only fields | — |
| U-09 | Unrepresented | S07, S16 | Per-card limit-break state: one `Level` (1–50) field, no break field | — |
| U-10 | Unrepresented | S07, S16 | Catalogue-max vs currently-reached effect values (§1.4's two columns); only a `Card effects (N)` catalogue disclosure, and the screen admits `no friendship-gauge value per slot` | — |
| U-11 | Unrepresented | S10, S18 | Client warning and quote strings (the energy warning, `Phew... I guess I'm just... a little tired...`, the tile banner `Floor Cleaning`); Events transcribes option-bearing events only | — |
| U-12 | Unrepresented | S10 | `Fans 209,245`, class `Star`, `30,755` to Top Star — a Fans field exists but the sheet carried no value; class and gap target have no field at all | — |
| U-13 | Unrepresented | S10 | Tile `Failure 39%` and preview `+18 Speed / +8 Power / +6 Skill Points` — fields exist, values absent from the sheet, so unentered | — |
| U-14 | Unrepresented | S06, S09 | The five unrecorded ancestors' spark sets, and the resulting picture: the Cockpit says `six` while one is stored | — |
| U-15 | Unrepresented | S06 | Spark values beyond kind/category/target/stars — e.g. `Ignited Spirit: Guts + ★☆☆`, `Competitive Spirit ○ ★★★`; both parents' pink/green/white sparks likewise | — |
| U-16 | Unrepresented | S12 | Team member count `20/20`, the 17-name roster, the two clipped members, and the ACE-per-distance board (`No column records who this run fields in each category`) | — |
| U-17 | Unrepresented | S12, S19 | `Team Rank S (five stars)` and the `+30` tooltip with its disputed attribution; rank arrives only through a team-race event that F-13 blocks | — |
| U-18 | Unrepresented | S12, S15 | The four preseason rounds individually (R1 Junior Late Dec … R4 Senior Late Jun); only the count `4` is enterable | — |
| U-19 | Unrepresented | S15, S19 | The pending finals as a five-race fixture after Senior Late Dec; `Finale · Unity Cup: not yet reached` is a state, not the fixture | — |
| U-20 | Unrepresented | S19 | Three cleared objectives and one active as trainer-set states; `Trainee goals` is catalogue-derived and reads `Upcoming` throughout | — |
| U-21 | Unrepresented | S05, S09 | The cap-vs-spark arithmetic gap itself (+25/+22/+68/+8, explained only for the +8): the inputs are representable, the non-reconciliation has no field — only prose | — |
| U-22 | Unrepresented | all | No notes/freeform field on any of the 20 screens, so the brief's "legitimate notes field" escape does not exist | — |
| U-23 | Unrepresented (this pass) | S07 | Deck cards 3–5 (Biko Pegasus 30/30, Matikanetannhauser 30/30, Haru Urara 35/35) not equipped — the field exists and was exercised three times; the truncation is this pass's, not the UI's | — |

Counts: **17 functional** (4 blocking: F-01, F-05, F-11, F-13), **14 experiential**, **23 unrepresented**.

## Source-to-UI traceability matrix (§7.7)

Outcome codes: `accepted` = entered and observed; **`persisted`** = re-read on a **later** application screen;
`no field (U-nn)`; `lost` = accepted then destroyed; `not entered this pass`.

| Datum | Value | Screen | Field / control | Outcome |
| --- | --- | --- | --- | --- |
| Trainee name | Rice Shower | S03 | `Search` + result row `Select` | accepted, **persisted** (S08 `TRAINEE Rice Shower`; S09 `h1`) |
| Card / epithet | `[Rosy Dreams]` | S03/S04 | `Form` select (default) | accepted; **not carried** — S08 prints `FORM · as chosen above` (F-09) |
| Rarity | ★3 | S03/S04 | `★★★ SSR` badge (display) and S20 `Rarity` select | `no field in flow (U-05)`; S20 field present, empty |
| Potential / awakening | Potential Lvl 2 | S20 | `Potential Level` (off the wizard path) | `no field in flow (U-04)`; not entered |
| Scenario | Unity Cup | S02 | radio `Unity Cup` | accepted, **persisted** (S08, S09, S13, S19) |
| Scenario full label | Unity Cup: Shine On, Team Spirit! | S02 | — | `no field (U-01)` |
| Snapshot point | Senior Year Early Oct | S05/S17 | — | `no field (U-02)` |
| Turns remaining | 5 | S05/S08 | — | `no field (U-03)`; tool derives 69 |
| Lifecycle status | mid-career, in progress | S08/S20 | `Status: Active` | accepted as least-wrong existing value (I-10); never Completed, never Veteran, never Legacy |
| Run number | none in source | — | — | never requested (P-02 answered in the negative); id `1` surfaced post-commit in the URL |
| Speed | 607 | S10 | `Speed *` | accepted, **persisted** (S13 `Speed 607 / 1,325 cap`) |
| Stamina | 577 | S10 | `Stamina *` | accepted, **persisted** (S13) |
| Power | 549 | S10 | `Power *` | accepted, **persisted** (S13) |
| Guts | 736 | S10 | `Guts *` | accepted, **persisted** (S13) |
| Wit | 397 | S10 | `Wit *` | accepted, **persisted** (S13) |
| Speed cap | 1325 | S05 | `Speed ceiling` | accepted, **persisted** (S09 `1,325`; S10 `valuemax=1325`; S20 shown beside `Scenario 1300`) |
| Stamina cap | 1322 | S05 | `Stamina ceiling` | accepted, **persisted** (S09, S10, S20) |
| Power cap | 1368 | S05 | `Power ceiling` | accepted, **persisted** (S09, S10, S20) |
| Guts cap | 1308 | S05 | `Guts ceiling` | accepted, **persisted** (S09, S10, S20) |
| Wit cap | 1800 | S05 | none — `No enterable ceiling for this stat` | `no field (U-07)`; survives only as the published scenario cap, which equals it |
| Growth (S/St/P/G/W) | +0/+10/+0/+20/+0 | S04 / S20 | `Growth row` (read-only, identical) / `Growth rate %` fields | catalogue agreement confirmed on screen; run-side fields empty, `no field in flow (U-06)` |
| Unspent SP | 173 | S10 | `SKILL POINTS` | accepted, **persisted** (S13 `173`; S14 budget `173`); S17 contradicts with `N/A` (F-12) |
| Mood | GREAT | S10 | `MOOD AFTER` | accepted, **persisted** (S13 `GREAT ↑ +20%`) |
| Energy | low bar + warning, no number | S10 | `Energy state=Band`, `Energy band=Low` | accepted, **persisted** (S13 `Low band`) and consumed by the Advisor; warning text `no field (U-11)` |
| Deck card 1 | Tokai Teio `[Dream Big!]`, Speed, 35/35 | S07 | picker `Equip`, `Level for Slot 1`, `Owned` | card accepted, **persisted** (S16); **level 35 lost** (F-11) |
| Deck card 2 | Nishino Flower `[Even the Littlest Bud]`, Speed, 30/30 | S07 | picker `Equip` | card accepted, **persisted** (S16); level not entered |
| Deck card 3 | Biko Pegasus `[Double Carrot Punch!]`, Speed, 30/30 | — | — | not entered this pass (U-23) |
| Deck card 4 | Super Creek `[Piece of Mind]`, Stamina, 50/50, **borrowed** | S07 | Slot 6 `Equip`, `Level`, `Rented` | card accepted; **`Rented` persisted** (S16 `aria-pressed="true"`); **level 50 lost** (F-11); position labelled `Pal slot` while holding a Stamina card (E-06) |
| Deck card 5 | Matikanetannhauser `[Just Keep Going]`, Guts, 30/30 | — | — | not entered this pass (U-23) |
| Deck card 6 | Haru Urara `[Urara's Day Off!]`, Guts, 35/35 | — | — | not entered this pass (U-23). This is the deck's only Unity Cup link card, so S07's `Scenario link on a card · 0` is accurate only because it was not equipped |

| Datum | Value | Screen | Field / control | Outcome |
| --- | --- | --- | --- | --- |
| Skills owned (10 visible chips) | Blue Rose Closer Lvl 3, Red Shift/LP1211-M, Corner Adept ○, Swinging Maestro, Nimble Navigator, Steadfast, Deep Breaths, Extra Tank, Pace Chaser Straightaways ○, Shrewd Step | S14 | planner skill list / `Build target, recorded skills and the add form` | field exists, `not entered this pass`; `No skill is recorded on this run yet` (U-14/U-15) |
| Skill owned (not in the visible ten) | Corner Recovery ○ (marked Obtained on the client's Learn screen) | S14 | — | `no field (U-14)` — no acquired-vs-learned-elsewhere marker was reached |
| Ancestor 1 (legacy) | Vodka `[Wild Top Gear]`, rank B+ | S06 | `Parent name`, `Rank (letters)` | accepted, `Legacy recorded.` toast, then **lost** (S08 `PARENTS empty`; S11 `PARENT A Not set`) — F-05 |
| Ancestor 2 (legacy) | Maruzensky `[Formula R]`, UG, **Guest** | S06 | name, rank, `Guest / rented parent` | name **lost**; **Guest flag persisted** (S11 `Parent B · Not set · Guest ·`) — F-05, P-07 |
| Grandparents | Daiwa Scarlet `[Peak Blue]` ×2, Seiun Sky, Taiki Shuttle | S06 | `Grandparent A1/A2/B1/B2` + ranks | accepted, **persisted** (S11, all four `recorded ancestor`, including the repeat) — P-06 answered yes; epithets `no field (U-08)` |
| Blue sparks | Stamina ★★★, Power ★★★, Speed ★★☆ ×2, Power ★☆☆, Guts ★☆☆ | S06 | per-node `Add Spark` | one of six accepted (A2 Stamina ★★★) and surfaced at S09; five `not entered this pass (U-14)`; S11 reports `0 recorded` (F-08) |
| Team rank | S (five stars) | S12/S19 | none — `Team Rank is recorded through a turn's team-race event` | `no field (U-17)`; S19 `Rank not recorded` |
| League placement | 8th | S12 | `League placement` (1–99) | accepted, **persisted** (S13 `League 8`; S19 `League placement 8`) |
| Unity Trainings | 55 | S12 | `Unity Trainings` | accepted, **persisted** (S19 `Unity Trainings 55`) |
| Spirit Bursts | 6 | S12 | `Spirit Bursts` | accepted, **persisted** (S19); S13 shows the derived `Bursts 11 (inside 10-12)` |
| Extreme Spirit Bursts | 5 | S12 | `Extreme Bursts` | accepted, **persisted** (S19 `… / 5 Extreme`) |
| Team members | 20/20 | — | none | `no field (U-16)` |
| Team stat grades | Speed A, Stamina A, Power A, Guts S, Wit A | S12 | five grade selects | accepted, **persisted** (S19 read-back, all five) |
| Facility levels | Speed 4, Stamina 4, Power 4, Guts 5, Wit 4 | S10 | five `… level` inputs (1–5) | entered and submitted; **not re-read on any screen visited**, so persistence is unverified (see §8.5) |
| Races won | 4 preseason rounds (R1–R4) | S12/S15 | `Preseason wins` (max 4) | count accepted and **persisted** (S19 `Preseason rounds won 4`); the individual rounds `no field (U-18)` |
| Race scheduled this turn | Kyoto Daishoten (G2, Kyoto Turf 2400 m, Soft) | S15 | `Available this turn` — `No race on this scenario's calendar falls at this turn` | `no home at turn 1` (F-13); `Race not on the calendar` exists but was not used |
| Pending race | Unity Cup finals, after Senior Late Dec | S09/S15/S19 | `Finale · Unity Cup: not yet reached` | state accepted without won/lost (P-16); the five-race fixture `no field (U-19)` |
| Active objective | Place 1st in Arima Kinen, Senior Late Dec, 5 turns | S19 | `Trainee goals` (catalogue-derived, `Upcoming`) | field exists but is not trainer-settable (U-20) |
| Cleared objectives | Nikkei Sho top 3; Tenno Sho (Spring) 1st; Takarazuka Kinen top 3 | S19 | `Trainee goals` | `no field to mark cleared (U-20)` |

**Survival summary.** Of the 47 §2.3 rows: **21 accepted and re-verified on a later application screen**;
**5 accepted then destroyed or never carried** (form label, both parent names, both deck levels — F-05, F-09,
F-11); **6 whose fields exist only off the wizard path** (rarity, potential, growth rate ×5 inputs, and the
absent notes field); **15 with no field at all** across the 20 screens; and 3 groups deliberately not entered
by this pass (deck cards 3–5, five ancestors' sparks, sheet-gap values). Two further rows — facility levels
and the run-number question — are answered but not both ways: facility levels were entered without a visible
read-back, and the absence of a run-number request *is* the answer.

## Closing assessment (§8)

### 8.1 What worked

The walk's central question — can a mid-career Unity Cup snapshot be recorded through the browser at all —
answers yes for the run's **live state**, and the state survives. The stat line, the SP balance, the mood and
the caps were entered through two screens and re-read on a third: `607 / 1,325 cap`, `577 / 1,322`,
`549 / 1,368`, `736 / 1,308`, `397 / 1,800`, `173`, `GREAT ↑ +20%`, `Low band`. Four things are specifically
good, and good in the way this run needs:

- **Energy is representable without a number.** `Energy state` offers `Exact / Band / Not recorded` and
  `Energy band` offers `Low / Mid / High`. The source's evidence is a near-one-third bar plus a warning
  string, and `Band = Low` carries exactly that. It is not decoration either: the Cockpit's Advisor consumed
  it and produced `Energy is recorded as a low band, below the 50 advisory line; Rest is advised.` This is the
  single most useful design decision found in the pass, and it means §3.5's anticipated improvisation never
  had to happen.
- **The entered ceiling governs where it should.** `1325/1322/1368/1308` became the turn form's own `valuemax`
  per stat, so the ceilings the client prints are the ceilings the app enforces, and Run settings prints them
  beside `Scenario 1300`. The source's cap question (P-03) is therefore *structurally* representable, which is
  more than the brief expected.
- **Absence is a first-class value almost everywhere.** `Not recorded`, `N/A` with a disclosure saying why,
  `Not chosen`, `Leave empty`, `Skip this moment`, nullable parents, `A slot you have not classified reads as
  N/A rather than as Owned`, `an N/A is "not recorded", never zero`, plus two explicit refusals to invent a
  figure (`Inventing one would be the "score" the app is forbidden to add`; `This repository stores no
  sample-build column, so the row prints N/A rather than a number it cannot source`). For a source document
  whose whole method is preserving what it cannot measure, this is the right behaviour.
- **The awkward ancestry largely survived.** All four grandparent slots persisted, including `Daiwa Scarlet`
  twice on different branches with different ranks; the `Guest / rented parent` flag persisted; a Stamina card
  was accepted into the Friends position with the `Rented` flag intact; per-stat grades stop at `S` with the
  note `S+ is the overall team rank, recorded elsewhere`; and Scenario detail prints the grade→facility ladder
  (`A lv 4`, `S lv 5`) matching the source's §1.7/§1.8 observation.

### 8.2 Functional findings

Seventeen, four blocking. The blocking four are the ones that would strand a trainer:

- **F-01 (S05).** The build-target step demands five stat targets plus Purpose, Surface and Style. Its own copy
  says the step is skipped by the current build and that the fields start empty rather than at zero, and its
  error message offers to let the target stay unset. It will not. This was proved rather than assumed: after
  clearing all four ceiling inputs, the wholly-unset target block was still refused with the same five errors.
  What happened: a legitimate user holding a captured run with no stated goals is made to type five numbers.
  What I did next: entered the maximum each input accepts (the screen's own printed cap) so no figure came
  from outside the app, and logged all five in §7.4.
- **F-05 (S06 → S08 → S11).** Both manually named parents were destroyed between draft and run. The Legacy
  screen held the values, toasted `Legacy recorded.`, and re-read them after saving; Preflight printed
  `PARENTS empty`, and Inheritance printed `PARENT A Not set` / `PARENT B Not set`. The grandparents all came
  through, and Parent B's `Guest` flag came through without its name — the clearest possible evidence that
  this is a dropped transfer rather than a missing feature.
- **F-11 (S07 → S16).** Deck card levels came through empty. Slot 1's level was verified `35` in the setup
  screen and reads `""` on the run deck; Slot 6's `50` likewise. The `Rented` flag, a harder thing to carry,
  did survive.
- **F-13 (S05, S08, S13, S17).** There is no way to say "this run is already 24 turns old". The wizard commits
  `CAREER LENGTH 72 turns`, positions turn 1 at `Junior · Early Jan`, and derives `Need 2 more bursts in 69
  turns` for a run the client says has five turns left. Downstream of that one decision the Daishoten, the
  Arima Kinen goal, the three cleared goals and the four preseason round rows all become unrecordable in
  sequence, because they live at turns this run cannot reach.

Non-blocking but material: **F-06** (the app prints `+21` and `16` for the same ★★★ cap raise, on one screen);
**F-07** (six inputs with no `max` announce `valuemax="0"` to assistive tech while the one input that sets its
bounds reports them correctly); **F-08** (the Cockpit asserts `the six blue sparks` where one is stored, and
Inheritance says `0 recorded` for the same tree); **F-09** and **F-10** (`FORM · as chosen above`; and a
`Pass` verdict on the step whose data was just discarded); **F-12** (Timeline prints `N/A` for values the
Cockpit prints as recorded); **F-14** (URA Finals obligations on a Unity Cup run); **F-15**
(`Hint Lvl Hint Lvl 1`); **F-16** and **F-17** (screens contradicting each other about what the form sets, and
about whether the scenario can be changed); **F-02**, **F-03**, **F-04**.

### 8.3 Experiential findings

Fourteen. The pattern is an app that is unusually honest and unusually hard to steer. Three cost the most:
**E-01/E-03** — requiredness arrives only on failure, and `Continue to build target` means two different things
on two consecutive screens, so the step count cannot be held in mind (`Step 2 of 6` spans three screens);
**E-06** — `Pal slot` on a position the same screen says may hold any card, beside a `Card types` table where
`Pal` is a type with its own rules. That is precisely the conflation §2.2 item 6 told this walk to watch for.
**E-07** — 251 unpaginated picker rows against a Dashboard figure of 559 cards, with nothing reconciling them.

**E-08** is the most interesting failure of advice: `Consider a Wit-heavy deck` is unconditional for Unity Cup,
and this deck has no Wit card by design, because Wit is the scenario's high-cap stat rather than a training
target. A correct hint, printed where the trainer has already decided otherwise, is noise.

Keyboard and focus, optional and brief: focus moved sensibly (the `Select` result handed focus to the
`Selected` panel's controls, and mid-save the forward button correctly disabled itself). Every disclosure
summary and button hit area inspected was ≥ 44 px (`min-h-11`). No keyboard-only traversal of the wizard was
attempted, so nothing is claimed about tab order.

### 8.4 Unrepresented source data

Twenty-three findings in five groups. **Run framing** (U-01 to U-03): the scenario's full label, the
`Senior Year Early Oct` snapshot point, and `5 turns left` — the last being the root of most of the others.
**Identity numbers that exist off-path** (U-04 to U-06): `Potential Lvl 2`, rarity `★3` and the run's own
`Growth rate` row each have a field on Run settings that the wizard never visits, so they read empty on a run
whose picker promised to set them. **Cap arithmetic** (U-07, U-21): Wit's ceiling cannot be entered at all, and
the source's central open question — the +25/+22/+68 that no spark explains — has no field, only the Cockpit's
prose, which does at least state the non-reconciliation rather than hide it. **Deck** (U-08 to U-10, U-23):
limit breaks, the catalogue-vs-current effect split, ancestors' and cards' epithets, and the three cards this
pass left out. **Team, races and objectives** (U-11 to U-20): `20/20` members and the roster with its two
clipped names; Team Rank S and the `+30` attribution dispute; the four preseason rounds as individual rows;
the finals as a five-race fixture; the three cleared and one active objectives as trainer-set states; and the
client's warning strings, for which the only transcription surface is scoped to events that carry options.
**U-22** closes it: there is no notes field on any of the 20 screens, so none of the above has a fallback home.

### 8.5 Walk completion

- **Reached the end of the available flow?** Yes, with two named exceptions. Every screen the run record's own
  navigation offers was visited except `Result`, which the UI gates (`Result: Available when the career is
  completed.`), and `/preferences`, which sits outside the run flow. Nothing is claimed about either.
- **Where and why it stopped.** S20 `/training-runs/1/settings`, after reading it. Continuing further would
  have meant writing second-pass data into a run the primary pass had already committed.
- **Data intentionally omitted because the UI had no valid representation:** the Energy number (none exists in
  the source; `Band = Low` used instead, so nothing was invented); the five ancestors' spark sets; the `20/20`
  member count and the roster; Team Rank S; the four individual preseason round rows; the finals fixture; the
  cleared/active objective states; `Potential Lvl 2`; rarity; the run-side growth row; the Wit ceiling;
  limit-break state; the catalogue-vs-current effect split; the client warning strings; Fans, the failure rate
  and the preview gains.
- **Values in the final persisted state that are not from the source document:** the five stat targets
  `1300/1300/1300/1300/1800` (I-05…I-09, forced); `Purpose = Story Clear`, `Surface = Turf`,
  `Style = Pace Chaser`, `Distance = Medium` (I-01…I-04, forced or derived); `Turn = 1` (I-11, forced by the
  grid); and the derived `TARGET TOTAL 7,000` that follows from the forced targets. Each is in §7.4 with its
  rejected alternatives. No value in the persisted run is marked Completed, Veteran or Legacy.
- **Persistence claims and their limits.** Every `persisted` row in §7.7 was read on a later application screen
  in this session. One row is **not** claimed as persisted: the five facility levels entered at S10 were never
  re-read on any screen visited, so they are reported as entered-and-submitted only. No database inspection was
  performed at any point, so nothing in this audit rests on the storage layer.
- **One-pass rule.** No earlier screen was re-entered, no `Back` control was used, and the flow was not
  restarted. Three deviations are logged at §7.1a: direct URL loads after commit, one transient DOM attribute
  write during uid discovery, and the deck/spark scope truncation. The `Continue to deck` press absorbed by the
  mid-save disabled button at S06 is recorded in Appendix B.
- **Environment mutations.** None. No cache or cookie clearing, no resize, no profile switch, no login or
  logout, no process restart, no rebuild. The app was already running and was left running.
- **No application code, configuration or data was modified.** The only files this session wrote are this
  audit and its screenshots; the only writes to the application are the ones a user makes through the UI (one
  run, one turn, one team block, one Legacy save).

## Appendix A — Per-run probe list (§5.2) and answers

| # | Answer observed in this pass | Screen |
| --- | --- | --- |
| P-01 | **No** mid-career/snapshot/partial state on this path. The wizard commits a fresh 72-turn grid (`CAREER LENGTH 72 turns`), turn 1 renders as `Junior · Early Jan`, and `Status` offers only `Active / Completed / Retired`. `Active` was taken as least-wrong. The app *does* acknowledge the case elsewhere: S01 offers `Import an existing career` → `/career/snapshot` and `Import a historical career` → `/training-runs/import`, neither on this forward path | S01, S08, S17, S20 |
| P-02 | A run number is **not** required anywhere in the wizard and was never requested, so no skip/unknown option was needed. The id `1` surfaced only after commit, in the URL | S05–S08 |
| P-03 | **Both.** Caps are asked as per-stat `ceiling` inputs with the scenario caps printed beside them; sparks are asked per ancestor node. Nothing reconciles them: ceiling and spark live on different screens, no cross-check runs at Preflight, and the Cockpit settles it in prose only — `The source records that they do not reconcile: the six blue sparks account for the Guts extra and nothing else` (a count that is itself wrong, F-08). The UI offered `1300/1800` as fixed and accepted `1325/1322/1368/1308` without comment | S05, S06, S09, S20 |
| P-04 | Called **`Growth row`** on the profile, read-only, and it matches the source exactly (`SPEED 0% STAMINA 10% POWER 0% GUTS 20% WIT 0%`). The *run-side* equivalent is called **`Growth rate`** and exists only on Run settings as five empty `%` inputs | S04, S20 |
| P-05 | **Yes, as absence rather than zero.** No row = `No Sparks recorded for this node`; `Target` includes `Not recorded`; spark `Stars` is bounded 1–3, so a zero-star spark cannot be forced. Wit's absence needed no action | S06 |
| P-06 | **Yes.** Four distinct grandparent slots; `Daiwa Scarlet` entered on both branches with ranks `B+` and `UG`, and S11 renders both as separate `recorded ancestor` nodes. Sparks are per node (each has its own panel) and the screen states `Two entries naming the same character in different records are stored` | S06, S11 |
| P-07 | **Yes** — a checkbox `Guest / rented parent (not a local run)`, revealed only in the manual-name branch, and it persisted (S11 renders `Parent B · Not set · Guest ·`). The client's word "Guest" is used and is not confused with a card type | S06, S11 |
| P-08 | **Yes, distinctly.** Position 6 is `Slot 6 · Friends`, and each slot carries its own `Owned` / `Rented` control with a documented third state `N/A`. The `Rented` flag on the Stamina card survived commit and re-read (`aria-pressed="true"`) | S07, S16 |
| P-09 | **No.** Only the catalogue side exists (`Card effects (N)`); no field for currently-reached values, and the screen says so: `The setup draft has no friendship-gauge value per slot, and this step does not read the client's in-run gauge.` So the source's five-cards-differ column pair cannot be expressed | S07, S16 |
| P-10 | **Only one, and it leaked.** A single `Level` (1–50) per slot, no limit-break field. The values entered (35, 50) did not reach the run record (F-11) | S07, S16 |
| P-11 | **The UI conflates them rather than distinguishing them.** Slot 6 is tagged `Pal slot` while `Pal` is simultaneously listed as one of seven card types with its own ruleset; the Friends position accepted a Stamina card silently. "Borrowed Pal card" and "friend slot" are therefore not separable concepts in this interface | S07, S16 |
| P-12 | **Individually**, on both sides: a per-run skill list (`No skill is recorded on this run yet`) and a separate plan (`No skill planned`, `PLANNED SPEND`, `REMAINING`). Nothing is count-only, so 11+ is structurally fine. None was recorded this pass | S14 |
| P-13 | **Separate concepts, both modelled.** The planner exposes `HINTS` filtering and a full `Why the hint ladder is not linear` table (`Hint Lvl 1 … Hint Lvl Max`, `10/20/30/35/40%`) whose figures match the source's §2.1 exactly, including 130 × 0.65 = 84.5 → 84 | S14 |
| P-14 | **Yes.** `Learn planned skills` + `PLANNED SPEND` on one side; `recorded skills` + `No skill is recorded on this run yet` on the other | S14 |
| P-15 | **Partly.** The race form carries a status vocabulary `NotOffered / Skipped / Entered / Completed`, and the calendar claims to list `the grade, the surface and the distance band, and the trainee's own letter for that axis`. But at turn 1 it printed `No race on this scenario's calendar falls at this turn`, so the four won rounds and the Daishoten could not be recorded individually (F-13) | S15 |
| P-16 | **Yes as a state, not as a fixture.** `Finale · Unity Cup: not yet reached` and `FINALE Not reached` express pending without won/lost. The five-race structure has no field (U-19) | S09, S17, S19 |
| P-17 | **Partly, and not trainer-settable.** A `Trainee goals` block exists with per-goal states (`Upcoming`) and turn positions, each offering `Record this race`, but it is derived from the catalogue for Rice Shower rather than from this run's capture, and every row reads `Upcoming` because the run holds no race records. The Cockpit's `OBLIGATIONS` shows the same shape — and the wrong scenario's finals (F-14) | S09, S19 |
| P-18 | **Yes, with the client's exact strings.** `MOOD AFTER` offers `Unchanged / GREAT / GOOD / NORMAL / BAD / AWFUL`; the Cockpit rendered `GREAT ↑ +20%`, matching §1.7's `GREAT +20% training`. A `Mood effect at every tier` disclosure is also present | S10, S13 |
| P-19 | **Band, and it is honoured.** `Energy state = Exact / Band / Not recorded`, `Energy band = Low / Mid / High`. `Band + Low` was submitted with no number, persisted as `Low band`, and the Advisor reasoned from it (`below the 50 advisory line`). No improvisation was required | S10, S13 |
| P-20 | **Not a field.** `Turn *` (default `1`) is an index, not a countdown; nothing asks for turns remaining, so `5` had nowhere to go. The figure the app works with is 69 | S10, S13, S17 |
| P-21 | **Yes, and it accepts `173`.** `SKILL POINTS` on the turn form, then the planner's budget (`Spend 173 Skill Points`; `SKILL POINTS 173 / PLANNED SPEND 0 / REMAINING 173`) | S10, S13, S14 |
| P-22 | **Five of six.** `League placement`, `Unity Trainings`, `Spirit Bursts`, `Extreme Bursts` and the five `Team stat grades` are fields on Team setup; `Team Rank` is not (`recorded through a turn's team-race event`); member count has no field at all. The app derived `Bursts 11 (inside 10-12)` correctly | S12, S13, S19 |
| P-23 | **No.** No roster field exists anywhere in the pass, so 18 named members against a `20/20` panel cannot be expressed and the discrepancy cannot be recorded. Scenario detail states the same gap for the ACE board: `No column records who this run fields in each category, so the board states the ask and not the run.` | S12, S19 |
| P-24 | **The picker shows the catalogue's letters**: `Mile C`, `Front runner B`, `Late surger C` (the profile repeats them as `Mile C`, `Front Runner B`, `Late Surger C`). The source records `Mile B`, `Front A`, `Late B`. Both readings are preserved; the disagreement is reported as the picker's behaviour, not resolved | S03, S04 |
| P-25 | **One label, `Potential Level`, and it is not derived.** `awakening` never appears in the flow. The picker says `The form you pick sets this run's rarity and potential.`, yet Run settings holds an empty `Potential Level` input and an empty `Rarity` select for exactly that form (F-16) | S03, S20 |
| P-26 | **Yes, cleanly.** Team setup offers per-stat grades `G…S` with no `S+`, stating `Per-stat grades are G to S. S+ is the overall team rank, recorded elsewhere.` Scenario detail goes further: `The ladder carries S+ above S: it grants a second hint rather than a higher facility, so it has no level to show.` | S12, S19 |
| P-27 | **Stated, not derived.** Scenario detail prints the ladder (`G lv 1 F lv 1 D lv 2 E lv 2 B lv 3 C lv 3 A lv 4 S lv 5`), which encodes the source's observation that Guts 5 follows team Guts S. But the run's facility levels are asked as five independent `… level` inputs on the turn form and nothing links the two; Preflight adds `Per-discipline facility training counts are not recorded, so no facility level can be shown.` | S08, S10, S19 |
| P-28 | **Only `Unity Cup`.** The full label `Unity Cup: Shine On, Team Spirit!` appears on no screen visited: not the scenario card, not Preflight, not the Cockpit, not Scenario detail (`Unity Cup progress`) | S02, S08, S09, S19 |

All 28 probes answered; **none marked unanswered**. P-12 and P-15 are answered about the *interface* while
noting the corresponding data was not entered this pass — stated in the answer rather than left implicit.

## Appendix B — Stability probes (second pass)

**This is a second pass against the same run (`/training-runs/1`), made after the primary pass ended. It is
not evidence about the primary pass**, does not re-open any screen for the screen-by-screen record, and no
finding in §8 depends on it alone. Each probe was run once, as permitted. Viewport, browser and session were
unchanged; no environment mutation occurred.

| Probe | Screen | What was done | What happened |
| --- | --- | --- | --- |
| 1. Single reload | S16 `/training-runs/1/deck` | Reloaded the run deck once | State identical: all three `Level` inputs still `""`, slot 6's `Rented` still `aria-pressed="true"`, count still `3 of 6 slots filled`. This is what confirms F-11 is stored data, not a render artifact |
| 2. Back then Forward | S16 | One `back`, one `forward` | No state loss and no error. Instrument artifact noted honestly: the `forward` action *reported* landing on `/training-runs/1/races` while the live document read `location.href = /training-runs/1/deck`, `h1 = This run's six slots`. The app's state was consistent; the reported destination was not. Recorded as inconclusive about the instrument, not about the app |
| 3. Double-submit | S12 `/training-runs/1/team` | One double-click on `Save team state` | Idempotent: `errCount 0`, no duplicate rows, `Preseason wins` still `4`. Also no confirmation of any kind — the user gets neither an error nor an acknowledgement (supports E-12) |
| 4. Deep link | S17 `/training-runs/1/timeline` | Loaded a screen never reached by clicking, direct by URL | Rendered correctly with no session or auth friction, and its data matched the run. **Deviation:** the brief asks for "a fresh tab"; this instrument exposes one page, so the deep link was opened in the same tab. The screen is recorded in the ledger at its actual visit position |
| 5. Invalid input | S12 | Typed `9` into `Preseason wins`, whose `valuemax="4"`, then read validity, then restored `4` | The field reported `validity.rangeOverflow = true`, `validity.valid = false` — but the app attached **no message and no `aria-describedby`**, so the only signal is native browser validity, which most users never see. Nothing was submitted while invalid; **the final submitted value was the source value (`4`)** |

One further observation carried out of the primary pass for completeness: at S06 the first
`Continue to deck` press landed while `Save Legacy` was still running. The button was correctly disabled
(`Saving…`) and the press was absorbed rather than double-firing. No duplicate Legacy was created — the later
note `This setup already holds a Legacy. Saving below replaces it whole` appeared exactly once.

## Appendix C — Remediation ideas (non-binding, **not implemented**)

Nothing below was applied. This appendix records options only; it is not a plan and creates no obligation.

1. **F-05 / F-11 — the two draft→run transfer losses.** Both are the same class of defect: a value the run
   model can store (parent identity, per-slot level) that the commit path does not carry. A single check —
   "for every field the draft collects, does the committed run expose it?" — would catch both, and the walk
   found them only by reading a later screen.
2. **F-01 — make the unset branch real, or remove the sentence.** Either accept a wholly-empty target block,
   or stop telling users the step is skippable. The message also needs to name which group it is complaining
   about: it blamed five empty targets while the blockers were three unmarked selects.
3. **F-13 — the real gap.** The app already has the right objects (`/career/snapshot` at S01, nullable
   parents, `Not recorded` everywhere). What it lacks is a starting position: a turn index plus a calendar
   label at commit would let a captured run land at Senior Early Oct instead of Junior Early Jan, which in
   turn unlocks the races, rounds, goals and fixture that F-13 currently blocks.
4. **F-07 — set `max` on the star, SP and Fans inputs**, or drop the attribute expectation; the spark `Stars`
   input shows the correct pattern already in the same component family.
5. **F-06 — pick one ★★★ figure.** `+21` and `16` cannot both be the app's claim; the source document cites
   `+16` with two dated publisher references, which is where a ruling would start. That is a Lore/Docs
   question, not a code question, and it is not this pass's to settle.
6. **F-08 — derive the spark sentence from the tree** or stop printing a count. The paragraph's *substance*
   (show entered ceilings and recorded sparks side by side without claiming derivation) is exactly right; only
   the number is wrong.
7. **F-14 — filter obligations by scenario.** The tooltips are honest about undated slots, but a Unity Cup run
   should not be told to prepare three URA Finals.
8. **F-15, F-09, E-02 — three string fixes** (`Hint Lvl` prefix duplication, the unresolved
   `as chosen above`, and `1 trainee are catalogued.`).
9. **E-06 — rename `Pal slot`.** The screen's own prose is correct; the label is the problem. `Friends slot`
   would match both the prose and the client.
10. **E-07 — paginate or virtualise the picker** and reconcile 251 against 559 on screen.
11. **E-09/E-10 — reconcile the reversibility claims**, and move `Delete this run` off the primary screen
    behind the same acknowledgement discipline Preflight already uses.

## Definition-of-done checklist (§7.8)

- [x] Every screen reached has its own section, in visit order (S01–S20; S13 is a second visit to the Cockpit
      and has its own section).
- [x] Every entered value has a source trace or is marked "not present in source" (§4.3 per screen; §7.4
      collects all fourteen).
- [x] Every source datum without a UI home is named on the screen where it became relevant (§4.4 subsections,
      U-01…U-23).
- [x] The mid-career snapshot nature is preserved throughout: status `Active`, never Completed, never Veteran,
      never Legacy; the mismatch itself is reported as F-13.
- [x] The run is not classified as Veteran or Legacy anywhere.
- [x] Functional (F-01…F-17) and experiential (E-01…E-14) findings are separate.
- [x] The findings register matches §8.2/§8.3/§8.4; counts agree (17 / 14 / 23).
- [x] The walk's stopping point and reason are stated (§8.5; S20).
- [x] Every P-nn in §5.2 is answered (Appendix A, 28 of 28, none unanswered).
- [x] The §7.7 traceability matrix is complete for all §2.3 rows, including the three groups this pass did not
      enter.
- [x] The §7.1 contamination statement is present, naming each quarantined item.
- [x] One-pass deviations and the environment-mutation log are recorded (§7.1a, §8.5: no mutations).
- [x] No fix was implemented; §6 honoured — remediation exists only in Appendix C.

### Gates not run, stated plainly

This was an audit pass, not a code change, so the §9 hand-off sequence was not applicable and was **not**
run: no `php artisan test`, no `pint --test`, no PHPStan, no `npm run typecheck` / `lint:ui`, no
`composer audit` / `npm audit`. Nothing in this audit was executed against `database/database.sqlite`, and no
`migrate:status` check was needed, since no migration or schema path was touched.

**The lore gate was run, and it is clean for this file.** `php tools/lore.php` (docs mode, which is
`composer lore`) scanned the repository and reported **555 hits, 75 exempt lines**. AGENTS.md §9's last
recorded figure is 478 hits / 75 exempt at `5ed45d67` (2026-10-20), so the count is **up 77 across the docs
corpus since that reading** — attributed to corpus growth, since walks 2–5, the resolutions file and the
round-2 to round-5 reconciliations all landed after it, and several of them appear in this run's output.
**Zero hits fall in this file.** That was checked two ways, because a silent absence looks the same as a clean
result: the output was searched for `rice-shower-unity-cup-ux-walk-6` (no matches) and separately confirmed
that `docs/audits/` is inside the scanner's scope at all — it is, since `rice-shower-unity-cup-ux-walk-2.md`,
`…-resolutions.md` and `…-round2-reconciliation.md` each return hits in the same run. The quoted client
vocabulary this document carries (`Wit`, `Mood`, `Spirit Burst`, `Unity Cup`, the `GREAT/GOOD/NORMAL/BAD/AWFUL`
strings) is the Global client set that `lore-code` treats as the words the UI may print, which is why it
raises no docs-mode hit; no wiki gloss was introduced, and no dataset key was renamed. Evidence log:
`.scratch-uma/ux-walk-6/ux-walk-lore.txt`.

The working tree at the end of the walk carried exactly two untracked paths and no modifications:
`docs/audits/rice-shower-unity-cup-ux-walk-6.md` and `docs/audits/RS-UC-2026-10/` (27 screenshots). **This
file is not committed**; committing it is the owner's call, and it is the commit that owes the §15 hand-off.


