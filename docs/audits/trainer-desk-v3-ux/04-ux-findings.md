# UX findings — Trainer Desk v3.x end-to-end audit

Every finding below is recorded against a running instance at `127.0.0.1:8200` (a byte copy of
`database/database.sqlite` at `.scratch-uma/v3-ux-audit/audit.sqlite`, seeded with the committed
Unity Cup, URA Finale and tracker-Veteran importers) and `127.0.0.1:8201` (a `migrate:fresh`
empty database for the new-install persona). Version `3.0.0`, HEAD `bd0a8693`.

Severity follows the brief's own definitions: **Critical** = data loss, incorrect persistence or an
irrecoverable workflow failure; **High** = the core career workflow is confusing, broken or
misleading, or a critical decision cannot be made reliably; **Medium** = notice­able friction,
excessive navigation, inconsistent interaction; **Low** = cosmetic.

---

## Verification errata (added 2026-11-10, before implementation)

Three findings in this register did not survive re-measurement against the tree at `bd0a8693`. The
register is corrected in place rather than left telling a future implementer to fix something that is
not broken. Each erratum names what was measured and what the first reading missed.

| Finding | Status | Why |
| --- | --- | --- |
| **UX-14** | **Withdrawn, not a defect** | The gate is an overwrite acknowledgement (`SaveVeteran.vue:437`, `!overwriteAcknowledged`), the flag is `ref(props.saved === null)` at `:181`, and the checkbox carrying the reason sits in the same action row as the button. Ticking it enables the button. The first pass read the `disabled` attribute and a page-level text dump but never the control's own row. No code change needed; a test-only case now pins the sentence. |
| **UX-16** | **Downgraded to Low** | There is no off-by-one. At position 67 the pill said `Turn 67 · Active`, `Undo turn 67` named the last logged turn and `Record turn 68` named the next one — all correct. What read as a contradiction was the frame: the pill printed a number without saying what it counted. Fixed by saying `Turn 67 logged · Active`; the submission path was already right and was not touched. |
| **UX-03** | **Stands; mechanism corrected** | The heading and the table had two owners: the heading counted a client `entered` boolean and the table read the server's warning list, and `entered` for the deck is `deck !== null`, so a deck saved with 1 of 6 cards printed "All 5 entered steps are recorded" over a `Deck Warning` row. The disagreement is state-dependent (it needs a `Warning` or a `Skipped` the client counts as entered), which is why a first re-check on a rebuilt draft did not reproduce it. The fix deletes the client computed so one owner remains. The earlier claim that the empty install showed this was wrong: that install correctly said `5 steps need attention`. |

Two further notes for the implementer. Slice 2.2 in the plan should be dropped, not implemented: its
two candidate causes are both wrong. Slice 2.3's steps 2-3 (rewrite the submit to trust a server-side
index) would change correct behaviour. Six paths in the plan are phantom and the repo's browser specs
live at `tests/browser/*.spec.ts`; the plan-verification note in `.scratch-uma/v3-ux-audit/` lists all
of them.

**UX-18 re-measured 2026-11-11 (P1 = planning state).** Two premises in the audit's UX-18 did not
survive measurement before implementation. First, the "nine empty `<h2>` elements" were not empty
headings: all eleven live inside the collapsed *"Build target, recorded skills and the add form"*
disclosure, and a text scan of `innerText` reads an unrendered (`display:none`) subtree as empty while
the accessibility tree correctly exposes nothing to a user who has not opened it. Every one of the
eleven carries real, visible text (`Add a skill`, `Your target`, `Purchasable skills`, ...). No
defect exists; the a11y half of UX-18 is withdrawn. Second, the audit's premise "no plan-skill
endpoint exists" was false in substance: `run_skills` has carried `Suggested` — *"a skill the Trainer
marked for this run before it happened"*, the exact planning state P1 approved — since rev 0.2, with a
write path (`POST /training-runs/{run}/skills`, `runs.skills.sync`) that the planner's picker and
budget bar already drive. Building a second `planned_skills` table would have been a second owner for
one fact, which `ARCHITECTURE-ESSENTIALS.md`'s planner line and the cut-systems ruling both refuse.
The one genuinely missing piece — the server priced nothing, so the write trusted the client's
budget arithmetic — is what shipped: `budgetRefusal()` on the existing sync, pricing the hint-adjusted
figure the bar prints (`HintDiscount::price`, floored identically) against the run's recorded SP.

---

## UX-01 — The support-deck picker is unreachable from the action that asks for it

- **Severity:** Critical
- **Category:** Usability / Broken workflow
- **Persona:** Both
- **Scenario:** Global (every scenario; both deck surfaces)
- **Route:** `runs.deck` → `/training-runs/{run}/deck?slot=N`, `career.deck` → `/career/setup/deck?deck_slot=N`
- **Screen:** `resources/js/pages/Support/Builder.vue` (run-scoped), `Career/DeckSelect.vue` (wizard)
- **Environment:** Chromium (Playwright), 1440×900, default theme
- **Preconditions:** Any run, or any setup draft, at the deck step

**Steps to reproduce**
1. Open `/training-runs/27/deck`.
2. Click the button labelled **"Choose a card in Slot 2"** (accessible name `Choose a card for Slot 2`).
3. Observe the URL becomes `/training-runs/27/deck?slot=2`.

**Expected:** the card picker for Slot 2 opens, or the page scrolls to it and moves focus into it.

**Actual:** nothing visible changes. The picker lives inside a closed `<details>` element whose
summary reads "Choose a card"; the `<details>` sits at y=899 in a 900 px viewport, i.e. exactly one
pixel below the fold, and the click does not open it. Measured: before the click
`details.open = [false, false, false]`, after the click `[false, false, false]`, and visible
`Equip to …` buttons `0`. Manually opening the same `<details>` yields 25 visible
`Equip to Slot 1 : <card name>` buttons — the picker works, it is simply never shown.

The same shape exists in the wizard deck step: measured 251 `Equip` buttons present in the DOM with
`aria-label`/`title` absent on all 251, and the visible page text contains no card rows at all.

**Evidence:** `evidence/p11b-deck-details.json` (`details-state`, `fold`, `slot-click-auto-open`);
`evidence/p3b.json` (`picker-a11y` → `equipCount: 251, equipWithName: 0`);
screenshots `p11b-deck-picker-open-1440.png`, `p7b-deck-slot1-1440.png`.

**Impact:** a Trainer cannot fill the six-card deck at all through the shipped UI, on either deck
surface. The deck is the primary luck-multiplier decision in the game and the brief's §5.5 target
area. Nothing downstream (deck analysis, SupportCardEffects, run summary) can be exercised until
this is passed, which makes it the single largest blocker in the journey.

**Recommendation:** make the slot button open the picker (`<details open>` bound to the targeted
slot, or replace `<details>` with an always-rendered panel scrolled into view with focus moved to
the first result), and give each `Equip` button an accessible name in its default state.

**Confidence:** Confirmed (DOM state measured before/after; picker contents verified once opened).

---

## UX-02 — The wizard's "Continue to build target" is a link to the trainee profile, and the profile's forward control is a dead end for a newcomer

- **Severity:** High
- **Category:** Navigation / Information architecture
- **Persona:** New Trainer
- **Scenario:** Global
- **Route:** `career.trainee` → `/career/setup/trainee`, then `career.trainee.profile` → `/career/setup/trainee/{id}`
- **Screen:** `Career/TraineeSelect.vue`, `Career/TraineeProfile.vue`
- **Environment:** 1440×900

**Steps to reproduce**
1. `/career/setup/scenario` → choose Unity Cup → **Continue to trainee**.
2. Search `Rice`, press **Select** on the trainee card.
3. Inspect the wizard's forward control.

**Expected:** the forward control advances to step 3 (Build target), as the step rail says
(`1 Scenario 2 Trainee 3 Build target …`).

**Actual:** after selection the wizard replaces its disabled **button** "Continue to build target"
with an **anchor** whose `href` is `/career/setup/trainee/30` — the trainee's profile. The label and
the destination disagree. From the profile the same label appears as a `<button>`; pressing it does
advance, so the flow is completable, but only after an unannounced detour through a read screen that
the step rail does not list. On the profile itself the only other forward control is that same
button, and no step-3 marker is present.

**Evidence:** `evidence/p1d-controls.json` (`before`/`after` control lists:
`{"t":"Continue to build target","dis":true,"type":"button"}` →
`{"t":"Continue to build target","href":"http://127.0.0.1:8200/career/setup/trainee/30"}`);
`evidence/p1f-profile.json`; screenshots `p1-w2-trainee-selected-1440.png`,
`p1-w2-trainee-profile-1440.png`.

**Impact:** a newcomer looking for "continue to build target" lands on a page about aptitudes and
costume forms. The form picker does live there, so the detour is intentional; the label is not. A
Trainer who wants step 3 and does not want to re-read the profile is given no way to say so.

**Recommendation:** either label the control for what it does ("Review {name}'s aptitudes", "Choose
a form") and put the step advance on the profile screen, or advance directly and expose the form
picker as an optional disclosure inside step 2.

**Confidence:** Confirmed (href and disabled state read from the DOM, both paths walked).

---

## UX-03 — Preflight reports "All 5 entered steps are recorded" while three of them are missing

- **Severity:** High
- **Category:** Feedback / Data integrity presentation
- **Persona:** Both
- **Scenario:** Global
- **Route:** `career.preflight` → `/career/setup/preflight`
- **Screen:** `Career/Preflight.vue`, `Career/PreflightController::show`
- **Environment:** 1440×900

**Steps to reproduce**
1. Visit `/career/setup/preflight` directly with a fresh session (no draft).
2. Read the **Checks** block.

**Expected:** the summary counts what is actually recorded and names what is missing, as its own
per-row table does.

**Actual:** the heading reads **"5 steps need attention"**, the table underneath lists
`Scenario Missing`, `Trainee Missing`, `Your target Skipped`, `Legacy Missing`, `Deck Missing` — and
immediately below, a second sentence reads **"All 5 entered steps are recorded."** The two statements
contradict each other on the same screen. The same contradiction appears with a partial draft
(measured with scenario + trainee + target + legacy saved and the deck empty: heading "A warning is
not a block", table `Deck Warning 0 of six positions carry a card`, and the body still asserting all
entered steps are recorded).

**Evidence:** `evidence/p19-empty.json` (`preflight`), `evidence/p4-walk.json` (`preflight`);
screenshots `p19-empty-preflight.png`, `p6-preflight-1440.png`.

**Impact:** Preflight is the last screen before an irreversible write ("this creates an Active run
that cannot be un-created"). A summary that says everything is recorded when four of five steps are
empty is precisely the wrong signal at the one moment the brief calls the final confirmation.

**Recommendation:** derive the sentence from the same computed list the table renders, and drop it
when the list contains any non-Pass row.

**Confidence:** Confirmed (both states rendered and captured).

---

## UX-04 — A Unity Cup cockpit shows the URA Finals obligation ladder

- **Severity:** High
- **Category:** Domain fidelity / Misleading information
- **Persona:** Experienced Trainer
- **Scenario:** Unity Cup (config-gated code path affects every scenario except URA Finale)
- **Route:** `runs.cockpit` → `/training-runs/9/cockpit`
- **Screen:** `CockpitController::objectiveSection()` + `models/race_catalog_slots`
- **Environment:** 1440×900

**Steps to reproduce**
1. Open `/training-runs/9/cockpit` (seeded Rice Shower Unity Cup career, turn 67).
2. Read the **Advisor → OBLIGATIONS** table.

**Expected:** the scenario's own obligations. Unity Cup's decisive event is the **Unity Cup** /
Team Race ladder and its Team Rank, and its three finals rounds are the Aoharu variants.

**Actual:** the table lists `Junior Make Debut`, `URA Finals Qualifier`, `URA Finals Semifinal`,
`URA Finals Final (Aoharu)`. The first three are the **shared** rows
(`race_catalog_slots.scenario_key IS NULL`), which `RaceCatalogSlot::scopeForScenario()` returns for
*every* scenario by design (`whereNull('scenario_key')->orWhere('scenario_key', $key)`). So a Unity
Cup career trains against a ladder whose first three rungs are labelled with a different scenario's
name.

Measured: mandatory slots are `Junior Make Debut`, `URA Finals Qualifier`, `URA Finals Semifinal`
(scenario_key NULL) plus one per scenario (`URA Finals Final (URA/Aoharu/Grand Live)`, `Twinkle Star
Climax`).

**Scope, measured rather than inferred.** The rail only renders for a run that has a turn to decide,
and the only seeded career in that state is the Unity Cup snapshot (#9). A fresh Trackblazer (#30), a
fresh Grand Concert (#31) and the completed URA run (#12) all render **no** obligation rail at all —
measured, `"OBLIGATION" not in bodyText` for all three. Trackblazer sets
`panels.race_calendar => false`, so `objectiveSection()` returns `[]` and the rail is suppressed by
the config gate as intended. The shared-row leak is therefore **confirmed on Unity Cup only**; the
same rows would surface on any scenario that composes `race_calendar` **and** has a turn to decide,
which on this tree is Unity Cup alone. URA Finale would qualify but no turn-bearing URA run existed.

**Evidence:** `evidence/p8-cockpit-uc-mid.json` (OBLIGATIONS table); SQL
`select id,scenario_key,title from race_catalog_slots where is_mandatory=1`;
`app/Models/RaceCatalogSlot.php:149-154`; `evidence/p9-trackblazer-cockpit.json`.

**Impact:** the brief asks whether "irrelevant scenario panels" appear. Here the panel is
scenario-gated correctly but its *contents* are not: an experienced Trainer reading "URA Finals
Qualifier" on a Unity Cup career has to know from outside knowledge that the label is shared
scaffolding and that Unity Cup's real deadline is the Team Race cadence. That is exactly the
"differences between actual career progression and Trainer Desk's representation" the brief targets.

**Recommendation:** either label shared rows with scenario-neutral wording ("Career finals
qualifier"), or per-scenario override the three shared titles, and gate the whole rail on
`panels.race_calendar` before composing rather than after.

**Confidence:** Confirmed for the rendering and for the scope behaviour; the *intent* of the shared
rows is read from the code comment, not from a config entry.

---

## UX-05 — The Cockpit's "Starting" stat line renders five numbers with no separators

- **Severity:** Medium
- **Category:** Visual / Information hierarchy
- **Persona:** Both
- **Scenario:** Global
- **Route:** `runs.cockpit` → `/training-runs/9/cockpit`
- **Screen:** `resources/js/components/career/CareerStatePanel.vue:256-266`
- **Environment:** 1440×900

**Steps to reproduce**
1. Open `/training-runs/9/cockpit`.
2. Read the line under the five stat bars.

**Expected:** five labelled starting values, or at least a readable sequence.

**Actual:** the line renders as `Starting8768917480(the card's base line, before any training)`.
The five values (87, 68, 91, 74, 80) are separate `<span class="ml-2 …">` elements with margin and
`font-mono`, which is visually legible on screen, but the text content has no separators at all —
so the DOM order, a screen reader, copy-paste and any text extraction see one run-together number.
The `ml-2` spacing is also the only thing distinguishing the values from the label, so at a glance
"Starting 8768917480" reads as a single very large figure.

**Evidence:** `evidence/p8-cockpit-uc-mid.json`; probe output
`bodySnippet: "Starting8768917480(the card's base line, before any training)"`;
`CareerStatePanel.vue:261-265`.

**Impact:** a starting-stats reference is meant to be compared against the live bars above it. As
rendered it reads as one implausible number, and assistive technology announces it as such.

**Recommendation:** print each value with its stat name (`Spd 87 · Sta 68 · …`) or a list with
`aria-label`s, so the line is self-describing without relying on margin.

**Confidence:** Confirmed (rendered text and DOM structure measured).

---

## UX-06 — "No action selected selected" on the training save bar

- **Severity:** Low
- **Category:** Feedback / Copy
- **Persona:** Both
- **Scenario:** Global
- **Route:** `runs.training` → `/training-runs/9/training`
- **Screen:** `resources/js/pages/Career/TrainingDetail.vue:723`
- **Environment:** 1440×900

**Steps to reproduce**
1. Open `/training-runs/9/training` and scroll to the save bar.

**Expected:** "No action selected · 1-5 pick · Enter records · Esc back".

**Actual:** "**No action selected selected** · 1-5 pick · Enter records · Esc back". The template
is `{{ chosenLabel ?? 'No action selected' }} selected ·`, so the fallback label already ends in
"selected" and the template appends a second one. Choosing a discipline renders correctly
("Speed selected ·"), which is why the defect only shows in the initial state — the state every
Trainer sees first.

**Evidence:** `evidence/p8-training-uc-mid.json`, `evidence/p16-context.json`
(`[selected selected] … "No action selected selected · 1-5 pick · Enter records · Esc back"`);
screenshot `p8-training-new-1440.png`.

**Impact:** small, but it is the instruction line a Trainer reads at the moment of the primary
career action, and it reads as a bug rather than as a tool.

**Recommendation:** store the label without the trailing word ("No action") or move "selected" into
the fallback only.

**Confidence:** Confirmed.

---

## UX-07 — The review queue overflows the viewport at every measured width

- **Severity:** Medium
- **Category:** Responsive
- **Persona:** Both
- **Scenario:** Global
- **Route:** `review.index` → `/review`
- **Screen:** `resources/js/pages/Review/Index.vue` (pager wrapper)
- **Environment:** 1440×900, 1280×800, 768×1024, 390×844

**Steps to reproduce**
1. Open `/review` at each of the four viewports.
2. Compare `document.documentElement.scrollWidth` with `window.innerWidth`.

**Expected:** no horizontal scroll at any width.

**Actual:** horizontal overflow at all four: 1446 vs 1440, 1286 vs 1280, 774 vs 768, **431 vs 390**.
The offender is the pager wrapper `<div class="z-10 -mx-4 mt-6 … md:-mx-8 md:px-8">`, whose negative
margins are not compensated at the shell's own padding. At 390 px the page is 10.5 % wider than the
viewport, so the pager sits partly off-screen.

**Evidence:** `evidence/p12-responsive.json` (72 measurements, `over: true` on `review` at every
viewport); `evidence/p1-summary.json`; `Review/Index.vue` pager div.

**Impact:** the only screen that rescales horizontally; on a phone the pager's right-hand controls
are pushed out of reach. 304 pending candidates make this a screen a Trainer will actually use.

**Recommendation:** drop the negative margin on the pager (or apply it only where the parent padding
is known), and re-check at 390.

**Confidence:** Confirmed (four measurements).

---

## UX-08 — The Dashboard's "Pick up where you left off" card explains a defect to the Trainer

- **Severity:** Medium
- **Category:** Information architecture / Copy
- **Persona:** New Trainer
- **Scenario:** Global
- **Route:** `home` → `/`
- **Screen:** `resources/js/pages/Dashboard.vue:209-232`
- **Environment:** 1440×900

**Steps to reproduce**
1. Open `/` with a snapshot-imported career present (the seeded Rice Shower run, or any career whose
   imported position disagrees with its logged turn count).
2. Read the paragraph under the TURN / ENERGY / TEAM RANK tiles.

**Expected:** the front door tells the Trainer where the career stands.

**Actual:** it prints an engineering note verbatim:

> "No logged turn has reported a team rank yet. A turn is counted from the turns recorded, not from
> the position the run was imported at. recorded turns: 1; imported position: 56. Both are labelled,
> and the disagreement is a known defect, not a rendering choice."

"a known defect", "not a rendering choice", "imported position", "recorded turns: 1" are the
vocabulary of a bug tracker, not of a front door. The card also shows `TURN 1 of 72` beside a
position label reading `Senior Year · Late April`, which is the defect the sentence is apologising
for, and `TEAM RANK not recorded` with a `○` marker.

**Evidence:** `evidence/p1-desk-dash.json` / `p1-desk-dash.json`;
`Dashboard.vue:231`; screenshot `p1-01-dashboard-1440.png`.

**Impact:** a brand-new Trainer's first substantive paragraph is a software-defect disclosure with
two contradictory numbers. It undermines confidence in every other figure on the screen, and it is
the first thing the Dashboard says about the career it is asking the Trainer to resume.

**Recommendation:** resolve the underlying double-count (one turn authority for the strip and the
cockpit), and in the meantime say what the Trainer can act on ("2 turns logged since import") rather
than what the code cannot reconcile.

**Confidence:** Confirmed.

---

## UX-09 — Cockpit obligation rows say "unknown / no date" for obligations that have a date

- **Severity:** Medium
- **Category:** Domain fidelity / Feedback
- **Persona:** Experienced Trainer
- **Scenario:** Unity Cup, Trackblazer, Grand Concert (every scenario's finals block)
- **Route:** `runs.cockpit`
- **Screen:** `CockpitController::objectiveSection()`
- **Environment:** 1440×900

**Steps to reproduce**
1. Open `/training-runs/9/cockpit`.
2. Read the OBLIGATIONS table's DUE column.

**Expected:** a due turn or a due window for each obligation.

**Actual:** rows read `URA Finals Qualifier / unknown / no date`, `URA Finals Semifinal / unknown /
no date`, `URA Finals Final (Aoharu) / unknown / no date`. The rows exist because the shared catalogue
carries `year = 4` with `turn = NULL`; `due_label` is null so the cell renders `no date`. A Trainer
reading this cannot tell whether the finals are imminent (this career is at turn 67 of 72 with the
finale 5 turns away) or undated. The Scenario panel two blocks above *does* say `Finale 5 turns
away`, so the screen holds both the answer and its absence at once.

**Evidence:** `evidence/p8-cockpit-uc-mid.json` (OBLIGATIONS + Scenario panel);
SQL: the three shared rows have `turn IS NULL`, `year = 4`.

**Impact:** the obligation rail is the Cockpit's "what is due" answer, and for the three rows that
matter most at endgame it refuses to answer while a different panel already knows.

**Recommendation:** let the rail read `FinaleReader`'s position for the finals block instead of
printing the null.

**Confidence:** Confirmed for the rendering; the two-surfaces disagreement is measured on one
screen.

---

## UX-10 — The wizard cannot proceed past the build-target step without three fields the screen calls optional-by-context

- **Severity:** Medium
- **Category:** Validation / Feedback
- **Persona:** New Trainer
- **Scenario:** Global
- **Route:** `career.target.store` → `PUT /career/setup/target`
- **Screen:** `Career/BuildTarget.vue`, `StoreBuildTargetRequest`
- **Environment:** 1440×900

**Steps to reproduce**
1. Reach `/career/setup/target`, enter five stat targets and pick a distance band.
2. Press **Continue to legacy**.

**Expected:** either the step advances, or the refusal names the missing controls at the control.

**Actual:** the write returns 303 back to `/career/setup/target` (PHP session round-trip) and the
page renders `The purpose field is required.`, `The surface field is required.`,
`The style field is required.` — placed in a band that is visually distant from the three `<select>`s
at the top of the page. Entering the five numbers first is the natural order (they are the screen's
main content and the only numeric inputs), and nothing marks purpose/surface/style as required
before submission. Measured: a submit with only stats + distance yields 303 and three errors; adding
the three selects advances to legacy.

The rule is documented as "all or nothing" in `StoreBuildTargetRequest` — that intent is sound — but
the screen never states that the three selects are part of the same all-or-nothing bundle as the
numbers, and the error band does not point at them.

**Evidence:** `evidence/p2c-target-probe.json` (`submit-response: 303`,
`errors: ["The purpose field is required.", "The surface field is required.", "The style field is required."]`);
`evidence/p4-walk.json`; screenshots `p1-w3-target-1440.png`, `p1-w3-target-overcap-1440.png`.

**Impact:** the step that the page itself labels "This is the step the current build skips" is also
the step where a first-time Trainer stalls, with errors that do not identify the controls.

**Recommendation:** mark the three selects as required in the label (or pre-select a default), and
link each error to its control.

**Confidence:** Confirmed.

---

## UX-11 — The build-target screen prints a `N/A` "Total against the published sample" row plus a paragraph explaining why it is `N/A`

- **Severity:** Low
- **Category:** Information hierarchy
- **Persona:** Both
- **Scenario:** Global
- **Route:** `career.target`, `runs.settings`
- **Screen:** `Career/BuildTarget.vue`
- **Environment:** 1440×900

**Actual:** the totals table contains `Your total 4400`, `Published Global sample build Special Week
N/A`, `Sum of the entered ceilings No ceilings entered`, followed by a four-sentence disclaimer
beginning "The published Global sample is one publisher's build, not a requirement and not this
tool's recommendation". The row can never hold a number (the repo stores no sample-build column),
so it is a permanent `N/A` with a permanent essay.

**Evidence:** `evidence/p1c-wizard.json`, `evidence/w3-target-filled.json`; screenshot
`p1-w3-target-1440.png`.

**Impact:** competes with the two figures that do carry information, on the screen where the Trainer
is setting the run's success criteria.

**Recommendation:** drop the row and keep the disclaimer as a single footnote, or replace the row
with a sourced comparison the tool actually holds.

**Confidence:** Confirmed.

---

## UX-12 — `career.legacy` prints `nullable` and `column` to the Trainer

- **Severity:** Low
- **Category:** Copy
- **Persona:** New Trainer
- **Scenario:** Global
- **Route:** `career.legacy` → `/career/setup/legacy`
- **Screen:** `Career/LegacySelect.vue`
- **Environment:** 1440×900

**Actual:** two shipped sentences use database vocabulary: "the run carries two **nullable** parent
ids so a Trainer can record what they do not know yet", and "The recorded Blue Spark against a stat
is what a parent **column** prints". Neither term has a meaning to a Trainer.

**Evidence:** `evidence/p16-context.json` (`legacy-setup` entries for `nullable` and `column`);
screenshot `p1-w4-legacy-1440.png`.

**Impact:** low individually; part of a pattern (see UX-13) that makes the product read as a
developer tool in places a newcomer meets first.

**Recommendation:** "a parent can be left unset" / "each parent's own figure".

**Confidence:** Confirmed.

---

## UX-13 — Developer vocabulary reaches shipped copy on at least eight screens

- **Severity:** Medium
- **Category:** Copy / Information architecture
- **Persona:** New Trainer
- **Scenario:** Global
- **Routes:** `/training-runs/{run}/deck`, `/veterans/{id}`, `/career/setup/preflight`,
  `/training-runs/{run}/events`, `/database/scenarios`, `/legacy/compare`, `/career/setup/scenario`
- **Environment:** 1440×900

**Measured occurrences (each quoted from a rendered page):**

| Screen | Shipped text |
| --- | --- |
| run deck | "The deck is **run-scoped**: changing it here does not touch any other career" |
| veteran detail | "No **column** holds any of the three on a library row, and adding one is a stored-shape change the **schema** owner decides" |
| veteran detail | "No sourced **table** in this **repository** prices a Spark" |
| save veteran | "Factor analysis, a legacy value, a best use: Held computation, and no **table** in this **repository** prices a Spark" |
| preflight | "No **column** records a Global ruleset version" |
| events | "No **column** holds an event's Energy, Mood, Skill-Point or stat delta" |
| database/scenarios | "No subsequent rework documented in any source in this **repository**" |
| legacy compare | "a comparison needs two **columns** to be a comparison" |
| scenario setup | "Both are **run-scoped** selections with their own editors" |

**Evidence:** `evidence/p15-copy.json` (per-page `internal` lists), `evidence/p16-context.json`
(context windows).

**Impact:** the brief asks for guidance a newcomer can follow. "No column holds this" explains an
implementation choice; it does not tell a Trainer what they can do. The same fact stated as "this
build does not record that" would be both honest and comprehensible.

**Recommendation:** one pass replacing schema/column/repository/run-scoped with the plain
equivalents, keeping the disclosure but not the implementation detail.

**Confidence:** Confirmed (each string read from a rendered page).

---

## UX-14 — Save Veteran's action is disabled with no on-screen reason

- **Severity:** High
- **Category:** Feedback / Broken workflow
- **Persona:** Both
- **Scenario:** Global
- **Route:** `runs.veteran` → `/training-runs/{run}/veteran`
- **Screen:** `Career/SaveVeteran.vue`
- **Environment:** 1440×900

**Steps to reproduce**
1. Open `/training-runs/12/veteran` (the seeded, Completed, already-filed Maruzensky career).
2. Read the action row at the bottom of the form.

**Expected:** the primary action is enabled, or a visible explanation of what is still required.

**Actual:** both actions are **disabled**: `Add tag (disabled: true)` and
`Update Veteran (disabled: true)`. The 19 tag chips (`Speed`, `Stamina`, … `Scenario`) are all
enabled, and the `Note` textarea is present. No rendered text anywhere on the screen says why either
control is inert. The nearest copy is the action's own footnote, "This replaces the Veteran already
filed for this career: its tags and note are rewritten…", which describes the effect but not the
precondition.

Scope correction, measured rather than assumed: every Completed run in the seed (12, 13, 15, 16, 17,
23, 24, 25) already has a `veterans` row, so **this audit could not observe a first-time save on an
unfiled career**. The disabled state therefore reproduces on the "update an existing Veteran" path
only. Whether a first save is also blocked is **untested**, because no unfiled Completed career
existed and creating one would have meant editing a run's status, which this audit does not do.

**Evidence:** `evidence/p10-lifecycle.json` (`veteran-complete` → `buttons`); screenshot
`p10-veteran-complete-1440.png`; SQL: `veterans` rows are training_run_id 12, 13, 15, 16, 17, 23, 24,
25 — the same set as the Completed runs.

**Impact:** "Save Veteran" is step 23 of the brief's 28-step journey. On the only path this audit
could reach, the completion control cannot be pressed and the screen does not say why. A Trainer
cannot tell whether they need a tag, a note, or something they cannot see.

**Recommendation:** enable the action and validate on submit with a named error, or print the unmet
precondition beside the disabled control. Rename the action for the never-filed case ("Save this
career") so the copy and the state agree.

**Confidence:** Confirmed for the disabled state and the absence of a reason; the *precondition* is
**needs verification** — the audit could not determine what the form requires because no reachable
career was in the unfiled state.

---

## UX-15 — The Legacy step presents "Save Legacy" as a separate gate that its own forward button bypasses

- **Severity:** Medium
- **Category:** Feedback / State management
- **Persona:** Both
- **Scenario:** Global
- **Route:** `career.legacy` → `PUT /career/setup/legacy`
- **Screen:** `Career/LegacySelect.vue`
- **Environment:** 1440×900

**Steps to reproduce**
1. Reach `/career/setup/legacy`, choose a parent in the Parent A select.
2. Press **Continue to deck** (not "Save Legacy").

**Expected:** one forward action, or a warning that the unsaved choice will be lost.

**Actual:** **Continue to deck** issues its own `PUT /career/setup/legacy` (measured: 303 →
`/career/setup/deck`), so the choice is submitted even though the screen presents "Save Legacy" as a
separate, apparently required step and the screen's own copy says "Nothing is stored until you save".
**Save Legacy** also issues a PUT, which redirects back to `/career/setup/legacy` with a
"Legacy recorded." flash. Two controls, two writes, and the visible model ("save, then continue")
does not describe what the flow actually does.

**Evidence:** `evidence/p6-create.json` net log (`PUT /career/setup/legacy` → 303
`/career/setup/deck`); `evidence/p2a-wizard-rest.json` (`w4-legacy` copy); `evidence/p4-walk.json`
(`legacy-leave-empty-count: 2`, `legacy-saved-state`).

**Impact:** a Trainer who fills a parent, reads "nothing is stored until you save", then presses the
obvious forward button cannot tell whether the choice survived. They may press Save first and be
returned to the same screen wondering whether it took.

**Recommendation:** one forward action that saves and advances; if a separate save is kept, disable
forward while the form is dirty and say why.

**Confidence:** Confirmed.

---

## UX-16 — The training step records turn 68 while the run stands at turn 67, with no on-screen reconciliation

- **Severity:** Medium
- **Category:** Domain fidelity
- **Persona:** Experienced Trainer
- **Scenario:** Unity Cup (snapshot-imported career)
- **Route:** `runs.training` → `/training-runs/9/training`
- **Screen:** `Career/TrainingDetail.vue`
- **Environment:** 1440×900

**Actual:** the save bar reads `Undo turn 67 … Record turn 68`, and the "Turn to play (the run's next
turn)" field is prefilled with the same index. The career header on the same page says
`Turn 67 · Active`, and the client position is Senior Year Early October = turn 67. So the screen is
recording turn 68 while displaying turn 67 twice. The helper text explains the general rule ("As the
client shows this turn, the calendar name and the index are the same fact"), which contradicts the
prefilled number.

**Evidence:** `evidence/p8-training-uc-mid.json` (`Record turn 68`, `Undo turn 67`,
`Turn 67 · Active`); screenshot `p8-training-uc-mid-1440.png`.

**Impact:** the single most important field on the primary data-entry screen is prefilled with a
number the rest of the screen disagrees with by one. An off-by-one in turn records corrupts the
career position, the obligation ladder and the timeline.

**Recommendation:** decide whether the imported turn is recorded or pending, and make the header,
the undo label and the prefill read from one function (the repo already has
`lib/career/turnDisplay.ts` and `TrainingRun::nextTurnNumber()` as the declared owners).

**Confidence:** Confirmed for the three labels on one screen; the *correct* index depends on the
import contract, which this audit does not adjudicate.

---

## UX-17 — The race decision screen lists a race whose aptitude the trainee cannot run

- **Severity:** Medium
- **Category:** Decision support / Domain fidelity
- **Persona:** Experienced Trainer
- **Scenario:** Unity Cup
- **Route:** `runs.races.decision` → `/training-runs/9/races`
- **Screen:** `Career/RaceDecision.vue`
- **Environment:** 1440×900

**Actual:** for Rice Shower (Turf A · Dirt G, Sprint E · Mile C · Medium A · Long A, Pace Chaser A)
the available list includes `Green Channel Cup — 1,400 m — OP — Dirt · Sprint E`, `Ladies' Prelude —
1,800 m — G2 — Dirt · Mile C`, `M.C. Nambu Hai — 1,600 m — G1 — Dirt · Mile C`, `Tokyo Hai —
1,200 m — G2 — Dirt · Sprint E` and `Uzumasa Stakes — 1,800 m — OP — Dirt · Mile C`. Five of the
twelve offered races are on **Dirt**, where the trainee holds G, and two are Sprint where she holds
E. The aptitude letter is printed per row (`Dirt · Sprint E`), so the information is present, but no
row is marked unsuitable, sorted away, or accompanied by a warning, and the primary control on each
row is an enabled **Enter** button.

**Evidence:** `evidence/p18-depth.json` (`races-mid`), screenshot `p18-races-mid-1440.png`;
`evidence/p8-races-new.json`.

**Impact:** the brief's §8.1 asks whether "unsuitable races are distinguishable from suitable ones".
They are not distinguished by anything but a letter the Trainer must decode against a different
screen (the trainee's aptitudes are not shown on this screen).

**Recommendation:** mark rows where the trainee's aptitude is below a threshold, and show the
trainee's aptitude inline on the row (it is already computed for the label).

**Confidence:** Confirmed for the list and the letters; the threshold is a design decision.

---

## UX-18 — The skills planner's Add form is unreachable in the empty state, and 11 acquired skills fill page 1 of 2 with no planning surface visible

- **Severity:** Medium
- **Category:** Information hierarchy / Broken workflow
- **Persona:** Both
- **Scenario:** Global
- **Route:** `runs.skills.planner` → `/training-runs/9/skills`
- **Screen:** `Career/SkillsPlanner.vue`

**Actual (measured on the seeded Unity Cup run, 173 SP, 11 acquired skills):** the page renders 11
rows and a pager reading `11 skills · page 1 of 2`; below it the "Why the hint ladder is not linear"
table and the note; then `No skill planned`, `Clear plan`, and a heading `Build target, recorded
skills and the add form` with **no form** under it, followed by nine empty `H2:` headings (measured
`H2: ''` ×9 in the heading list). The add form that heading announces is not rendered.

**Evidence:** `evidence/p18-depth.json` (`skills-mid`, headings list with nine empty `H2`),
`evidence/p8-skills-new.json` (`H2: ''` ×9); screenshot `p18-skills-mid-1440.png`.

**Impact:** the planner's stated job is to sum planned skills against recorded SP; the planning
control is missing, so 173 SP cannot be spent from this screen. Nine empty headings also pollute the
document outline for screen-reader navigation.

**Recommendation:** render the add form (or remove the heading and the invitation), and give the
form its own heading rather than empty `H2`s.

**Confidence:** Confirmed for the rendered state; **inferred** that the empty `H2`s are the add
form's own headings (they appear where the form's label should be).

---

## UX-19 — Save Veteran and the Veteran detail disclose "NOT IN THIS BUILD" as a heading beside ordinary content

- **Severity:** Low
- **Category:** Information architecture
- **Persona:** New Trainer
- **Scenario:** Global
- **Routes:** `/training-runs/12/veteran`, `/veterans/8`, `/veterans/compare`
- **Environment:** 1440×900

**Actual:** three screens carry a section headed `NOT IN THIS BUILD` in the same typographic weight as
`Tags`, `Note` and `Provenance`, listing held computations and why. On `/veterans/compare` the
heading sits directly under "Nothing selected" and is the third-largest block on an otherwise empty
screen.

**Evidence:** `evidence/p10-lifecycle.json` (`veteran-complete` headings,
`veteran-detail` headings); `evidence/p8-veteran-detail.json`; screenshots
`p10-veteran-complete-1440.png`, `p11-veteran-8-1440.png`.

**Impact:** the honesty is correct and unusual in a good way; the placement is not. A screen's
primary task competing with a build-status list makes the product read as a work in progress at the
moment a Trainer is trying to bank a career.

**Recommendation:** keep the content, move it behind a disclosure or to Help, so the primary task
owns the screen.

**Confidence:** Confirmed.

---

## UX-20 — The Deck step's strategic hint names a cap the scenario card contradicts

- **Severity:** Low
- **Category:** Domain fidelity
- **Persona:** Experienced Trainer
- **Scenario:** Unity Cup
- **Route:** `career.deck` → `/career/setup/deck`
- **Environment:** 1440×900

**Actual:** the deck step prints `Strategic hint for Unity Cup: Wit cap is 1800. Consider a Wit-heavy
deck.` The scenario selection card prints `Caps 1300 ×4 · Wit 1800`, and the Cockpit prints
`Wit 1,800 cap`. The three agree numerically, but the hint phrases the cap as a recommendation ("Wit
is high, so build Wit") without stating that the other four stats cap at 1300 — a Trainer who takes
the hint literally over-invests in the one stat that is already cheapest to cap.

**Evidence:** `evidence/p2d-full-wizard.json` (`w5-deck`), `evidence/p20-catalog.json`
(`scenario-caps`), `evidence/p8-cockpit-uc-mid.json`.

**Impact:** a nudge toward a genuine scenario property, phrased as advice the tool is not authorised
to give (`ADR-0020`: no recommendations). It is the only place the tool volunteers a build opinion.

**Recommendation:** state the cap difference as a fact ("Unity Cup's Wit cap is 1800; the other four
are 1300") and let the Trainer draw the conclusion.

**Confidence:** Confirmed.

---

## UX-21 — The wizard's scenario step warns that the choice "cannot be changed after the first turn" but nothing enforces or explains that on the next screens

- **Severity:** Low
- **Category:** Feedback
- **Persona:** New Trainer
- **Scenario:** Global
- **Routes:** `career.scenario` → `career.trainee` → …
- **Environment:** 1440×900

**Actual:** step 1 states "It cannot be changed after the first turn" and its decision table repeats
`Scenario · No · Switching composes a different panel set, cap table and currency; the cockpit has no
shape for a mid-run swap.` Once the Trainer advances, no screen repeats the choice or warns before
the commit. Preflight does restate `SCENARIO Unity Cup`, which is the effective safeguard, so the
risk is low; the missing piece is that the step-1 warning is not carried to the irreversible action.

**Evidence:** `evidence/p1-desk-scenario.json`, `evidence/p4-walk.json` (`preflight`).

**Impact:** the tool correctly identifies an irreversible decision and then relies on a single
summary row four screens later.

**Recommendation:** repeat the scenario in the step rail's Preflight row (it already lists the step
names) so the irreversible choice is visible on every step.

**Confidence:** Confirmed.

---

## UX-22 — `/legacy/{run}` 404s for a completed career

- **Severity:** Low (correct behaviour, unclear affordance)
- **Category:** Navigation
- **Persona:** Both
- **Scenario:** Global
- **Route:** `legacy.builder` → `/legacy/12`
- **Environment:** 1440×900

**Actual:** `/legacy/12` returns 404 "That page does not exist" because `LegacyController::builder()`
calls `abort_unless($run->status === RunStatus::Active, 404)`. The Veteran detail for the same career
prints "This run has no Legacy read-back recorded, so there is nothing to open in the builder." and so
does not link there. A Trainer who constructs the URL, or who follows a bookmarked link after
completing a career, gets a bare 404 rather than "this career is finished; its Legacy is fixed".

**Evidence:** `evidence/p8-sweep.json` (`legacy-builder` → 404); `LegacyController.php:218-220`.

**Impact:** small; the guard is correct and the Veteran screen explains the state.

**Recommendation:** redirect to the Veteran detail with the explanatory sentence instead of 404.

**Confidence:** Confirmed.

---

## UX-23 — A brand-new install renders a Dashboard whose every tile reads 0 and whose only instruction is inside an empty-state card

- **Severity:** Medium
- **Category:** Information architecture / Onboarding
- **Persona:** New Trainer
- **Scenario:** Global (empty database)
- **Route:** `home` → `/`
- **Environment:** 1440×900, `migrate:fresh` database

**Actual:** the lede reads "0 active careers, 0 finished runs, and 4 permanent Global scenarios", the
four KPI tiles read `0`, `0`, `0`, `0`, and the resume card says `Not recorded: no career is running.`
followed by a two-sentence explanation. The actionable route out ("Start a career", "Import an
existing career") is present and clear. But nothing on the screen tells the Trainer that **three of
the four zeroes are because the catalogue has never been fetched** — `0 trainees`, `0 skills`,
`0 support cards` are data-absence, not emptiness, and the only hint is on `/review`
("This database has never been fetched, so there is nothing here yet. Run php artisan uma:fetch to
populate.") and on the trainee step ("The roster is filled by seed data or php artisan uma:fetch").
A new Trainer who starts the wizard is stopped at step 2 with `0 trainees are catalogued` and an
instruction to run a shell command.

**Evidence:** `evidence/p19-empty.json` (`home`, `trainee`, `review`); screenshots
`p19-empty-home.png`, `p19-empty-trainee.png`.

**Impact:** the brief's Persona A is precisely this user. The first-run experience requires a CLI
command that no screen in the onboarding path mentions until the Trainer is already blocked.

**Recommendation:** on a zero-catalogue Dashboard, replace the KPI tiles' `0` with a single
"Reference data not loaded" state and the fetch command (as `/review` already does), and repeat it
in the trainee step's empty state with a copy-to-clipboard control.

**Confidence:** Confirmed.

---

## UX-24 — The empty Veteran library's "Team eligibility" panel says a Champions Meeting team "needs 3" — a claim the PRD does not authorise as a gate

- **Severity:** Low
- **Category:** Domain fidelity
- **Persona:** New Trainer
- **Scenario:** Global
- **Route:** `veterans.index` → `/veterans`
- **Environment:** empty database

**Actual:** the empty state reads "Entering a Champions Meeting needs three Veteran Umamusume
registered as a team. This library holds 0, so the requirement is not met yet." and a three-tile
panel `REGISTERED 0 / NEEDED FOR A TEAM 3 / TEAMS AVAILABLE 0`. On the populated database the same
panel reads "8 are registered; a Champions Meeting team needs 3."

**Evidence:** `evidence/p19-empty.json` (`veterans`), `evidence/p8-veterans.json`.

**Impact:** stated as a hard requirement ("needs"), the figure governs a game mode this tool does
not model; a Trainer comparing their library against it may believe the tool is gating something.

**Recommendation:** phrase it as a game fact with its source, not as a tool requirement.

**Confidence:** Confirmed for the rendering; the mechanic itself is outside this audit's
verification (see `docs/UMAMUSUME_REFERENCE.md` and the scenario guides for the sourced position).

---

## UX-25 — Our Grand Concert's finale is named with a URA title in shipped copy

- **Severity:** Low
- **Category:** Domain fidelity
- **Persona:** Both
- **Scenario:** Our Grand Concert
- **Route:** `runs.scenario-detail`, `/training-runs/31/scenario`
- **Environment:** 1440×900

**Actual:** the Grand Concert scenario detail's `Mandatory races` list heads its finale row
`URA Finals Final (Grand Live)`. The scenario guide names the finale `GIRLS' LEGEND U` (client title)
and `Girls' Legend U` (`docs/scenarios/07-grand-concert.md:129`), and the same page's own scenario
block correctly prints `Finale: Our Grand Concert. Not yet reached.` So one page names the finale two
different ways, only one of which matches the guide.

**Scope, measured:** Trackblazer's finale row is correct (`Twinkle Star Climax`), and URA's is correct
(`URA Finals Final (URA)`). Only the Grand Concert label is wrong, and it is wrong because the
catalogue row carries the export's title (`final_live`) rather than the scenario's own noun.

**Evidence:** `evidence/p9-grandconcert-scenario.json`; `docs/scenarios/07-grand-concert.md:129`;
`KNOWN-ISSUES.md` KI-82 records the catalogue title as a known defect with an owner decision owed.

**Impact:** the finale is the run's endpoint. A Trainer reading the Grand Concert board is told the
wrong name for the event they are working toward. KI-82 already records this; this audit confirms it
still reproduces at `bd0a8693`.

**Recommendation:** resolve KI-82. The config already carries `scenarios.{key}.label`, so the finale
title can come from the same owner the page's `Finale: Our Grand Concert` line already reads.

**Confidence:** Confirmed (reproduces; KI-82 is the existing record).

---

## UX-26 — The wizard's session-draft warning is accurate but its consequence is not visible when it fires

- **Severity:** Low
- **Category:** State management
- **Persona:** Both
- **Route:** every wizard step
- **Environment:** 1440×900

**Actual:** every step prints "One draft per session. Opening this wizard in another tab replaces this
one (last write wins); an expired session restarts the wizard." Back/forward and refresh preserve the
draft (measured: after `goBack` to step 1 the Unity Cup radio was still checked; refresh on step 2
kept the trainee). What is not observable is the failure: a second tab silently overwrites the first,
and there is no per-step "last edited" marker or recovery. The disclosure is honest; the risk is
unenforced.

**Evidence:** `evidence/p17-recovery.json` (`wizard-back`, `wizard-forward`, `wizard-refresh`).

**Impact:** low for a single-Trainer local tool, but the workflow is long enough (six steps) that
losing it is costly.

**Recommendation:** keep the disclosure; consider persisting the draft per-tab (a tab id in the
session key) rather than per-session.

**Confidence:** Confirmed for the preservation; the overwrite is **inferred** from the copy (not
reproduced with two live tabs, which the audit did not drive).

---

## Summary

| Severity | Count | IDs |
| --- | --- | --- |
| Critical | 1 | UX-01 |
| High | 4 | UX-02, UX-03, UX-04, UX-14 |
| Medium | 11 | UX-05, UX-07, UX-08, UX-09, UX-10, UX-13, UX-15, UX-16, UX-17, UX-18, UX-23 |
| Low | 10 | UX-06, UX-11, UX-12, UX-19, UX-20, UX-21, UX-22, UX-24, UX-25, UX-26 |

**26 findings total: 1 Critical, 4 High, 11 Medium, 10 Low.**

Confidence across the register: 23 **Confirmed** (rendered and measured), 2 **Confirmed with a
needs-verification half** (UX-14's precondition), 1 **Confirmed for rendering / inferred for intent**
(UX-04's shared-row design, UX-16's correct index). No finding in this register rests on inspection
alone.

