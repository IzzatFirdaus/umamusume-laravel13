# Laws of UX report — Career Cockpit and the setup wizard

> **Scope.** The two surfaces Agent 3 owns: `SCR-CAR-011` (`/training-runs/{run}/cockpit`) and the six
> `/career/setup/*` steps. Method, instruments and the evidence contract are
> `ux-laws-audit-plan-2026-10-13.md` (§3 matrix, §4 instruments, §5 method, §6 output contract, §9 rules).
> Findings from the three prior audits are cited by their own IDs in the **Existing ID** column and are
> not re-filed. Everything here was re-derived on the tree at the SHAs named in §Verdict, not inherited.
>
> **State of the tree this report was produced on.** Master `0a58c88` (2026-10-13, late). A concurrent
> session landed the ratified type scale (`4d31de4`, `209e05e`) and the scenario systems work is in
> flight in its working tree; the shared `public/build` bundle postdates no Vue change of the day. Every
> verdict below cites a `file:line`, a measured number from the harness runs named, or a committed
> test. Where a verdict depends on a render that no current bundle can show, it is marked
> **needs measurement** and names the run that will settle it.

# UX Audit Summary

- **Overall assessment.** Both surfaces are **Strong with two recorded tensions**. The cockpit honours
  the app's own spine (`design-2.0.md` §2: State → Options → Recommendation → Explanation → Decision)
  structurally, not decoratively: `NextDecision.vue`'s five-element order is its declared contract and
  every branch prints one element. The wizard's one real law failure of the day — the stepper marking
  visited-not-saved steps "done" — was fixed at `4a5ff88` under owner ruling C-1 ("keep visited", answered
  by the owner 2026-10-13 late, through this pass's blocking questions; the plan's §10.3 row and
  `4a5ff88`'s own body, written before the answer, call it open), and
  the scenario screen now opens on an option-card grammar rather than forty facts (`84f9353`).
- **Strongest patterns.** 44px floors hold everywhere they were measured (7/7 anchor targets at 768 and
  1280; the per-screen sweeps); every absence is a named absence (`AbsenceValue`) rather than a blank
  or an invented figure; every recommendation carries its provenance badge and its "why" list in
  visible text; two-step disclosures guard the destructive paths.
- **Highest-risk issues.** (1) The cockpit's visual reading order at 768px puts the timeline strip
  before the decision content while the DOM reads decision-first — a Continuity tension between the
  brief's CSS-only column order and a screen reader's sequence (P2, below). (2) The client's alert
  vocabulary (red exclamation, pink failure badge, obligation tag) has no counterpart in the app's
  semantic set; recorded as a convention per Laws-7, not a defect (P3, below).
- **Laws most frequently violated.** After today's slices: none outright FAIL on either surface. The
  recurring PARTIAL is **Goal-Gradient (18)** on the cockpit (distance-to-goal lives on the training
  cards and in deficits; the goals ladder slice is Agent 1's, pending) and **Von Restorff (15)** /
  **Closure (12)** as recorded by the Laws-7 ruling.
- **Accessibility and responsive risk summary.** axe A+AA scans are green wherever they ran
  (`accessibility.spec.ts`; the cockpit's 9-case spec; the wizard specs). Focus is not obscured by the
  two pinned bars: the cockpit's `scroll-margin-top` block and the wizard's `scroll-padding-top` were
  both click-through verified (0 of 7 anchors obscured; the 9.1 keyboard pass). Reduced motion is
  inherited from the platform; the tree carries one `animate-pulse` (F-G10). The risks that remain are
  the ones this report's numbers cannot reach while the bundle lags the tree — each is named
  **needs measurement** at its row.

# Route-by-route findings

## `/training-runs/{run}/cockpit` — SCR-CAR-011

- **User goal:** see where the career stands this turn and what to do about it, without losing the
  thread of what was already decided.
- **Primary task:** read the current state, take the recommended action or an alternative, record what
  the turn produced.
- **Overall rating:** **Strong**.

| Law or principle | Status | Evidence | User impact | Recommendation | Severity | Issue type | Existing ID |
| --- | --- | --- | --- | --- | --- | --- | --- |
| 3 Fitts's Law | PASS | `s01-measure`: 7/7 anchor targets ≥ 44px at 1280 and 768; the spec's own sweep (`career-cockpit.spec.ts`) | primary actions are where the hand is | none | — | — | cockpit audit §3.2 |
| 11 Continuity | PARTIAL | `s01-measure`: the nav renders first (y=159) and 7/7 anchor targets land below the pinned nav; at 1280 three column headings share y=801 (timeline 20% / decision 50% / advisor 30%, `SCREEN_SPEC.md:1841`'s CSS-only order), and at 768 the timeline strip is visually first (y=849) while DOM reads it after the scenario region (`Cockpit.vue:930` → `:944`) | a sighted scan and a screen-reader sequence disagree about what comes first on tablet | none this pass — the spec's own ruling orders the columns and forbids hiding; the DOM sequence is meaningful on its own. Re-test if the brief's column order changes | P3 | responsive | cockpit audit §3.2 |
| 26 Flow | PASS | `NextDecision.vue:5-8` (the five-element contract in its docblock); the recommended action carries its own door (`:218-223`); `#action-recommended` is the fragment the selector navigates | the next action is the page's answer, not a hunt | none | — | — | cockpit audit §3.2 |
| 20 Tesler's Law | PASS | `NextDecision.vue:10-14` ("the server owns the ranking; this component formats what it is handed and acts for nobody"); refusals name the missing inputs in words (`:172-185`) | the Trainer answers the game's question, not the tool's data model | none | — | — | — |
| 17 Peak–End | PASS | `RecordedActionCard` expected/actual with reconciliation (`NextDecision.vue:261-271`); correction and delete are two-step disclosures with focused alerts (`career-cockpit-forms.spec.ts`) | the turn's ending is stated and correctable | none | — | — | — |
| 16 Serial Position | PASS | nav first (`s01-measure` y=159), advisor rail last and sticky (`AdvisorRail.vue`, pass-2 4.1) | the two ends of the page carry the orientation and the decision | none | — | — | — |
| 19 Zeigarnik | PASS | the identity disclosure's "Unsaved changes." dirty marker and `role="status"` saved lines (pass-2 3.2/3.3) | unfinished edits are visible and resumable | none | — | — | cockpit audit §3.3 |
| 15 Von Restorff | PASS (PARTIAL per Laws-7) | one enamel primary per region (pass-2 4.2); the client's alert channels recorded as a convention, not adopted | the one important action is the one accented | none | — | — | cockpit audit §3.2 |
| 4 Hick's Law | PASS | the advisor's alternatives are the only inline option set (small, with trade-offs, `NextDecision.vue:225-237`); the scenario region reads its panels per scenario | decision points stay small | none | — | — | — |
| 18 Goal-Gradient | PARTIAL | deficits and caps on the training cards (`TrainingCard.vue:15`); the goals ladder is Agent 1's module, Slice 0.3 pending | distance-to-goal is visible per training, not per career | land Slice 0.3 when the module lands | P2 | interaction | — |
| 24 Cognitive Load | PASS | the why list is provenance-badged visible text (`NextDecision.vue:240-250`, `SCREEN_SPEC.md:1886` ruling 5a); no contributor copy (X-6 fixed) | recognition over recall | none | — | — | X-6 |
| 27 Consistency / 1 Aesthetic-Usability | PASS | the ratified scale landed (`209e05e`: cockpit, advisor, scenario panels); tokens only, no `dark:` (G-19 sweep) | one visual voice | none | — | — | X-2 |
| 5 Miller / 8 Common Region / 7 Similarity / 10 Prägnanz / 2 Jakob's / 23 Doherty | PASS | regions chunked to the seven the nav names; `role="status"` save feedback; native controls throughout | — | none | — | — | — |
| 13 Common Fate | N/A | MOTION 1 (`DESIGN.md:694-696`); no coordinated motion exists to judge | — | — | — | — | — |

## `/career/setup/scenario` — SCREEN-002

- **User goal:** choose the scenario this career runs under.
- **Primary task:** one Select press per card.
- **Overall rating:** **Strong** (Mixed before `84f9353`).

| Law or principle | Status | Evidence | User impact | Recommendation | Severity | Issue type | Existing ID |
| --- | --- | --- | --- | --- | --- | --- | --- |
| 4 Hick's Law | PASS | `84f9353`: the eight-row fact grid and the metadata block sit behind one "Facts and mechanics" disclosure per card; the card opens on name, badge, subtitle, status, action | the choice is readable before the facts are | none | — | interaction | wizard audit §3.2 |
| 21 Occam's Razor | PASS | one Select per card (`:271-281`); the page-level ruleset fact stayed per-card but named (R2-05's two-`Ruleset` fix preserved, `:100-116` re-aimed) | no duplicate doors | none | — | — | wizard audit §2.2 |
| 3 Fitts / 19 Zeigarnik / 23 Doherty / 15 Von Restorff | PASS | 44px sweeps (`career-scenario-select.spec.ts:168-182`); `aria-pressed` carries the stored state; `role="status"` "Saving your choice…"; one enamel per card | — | none | — | — | — |
| 12 Closure | PASS | the subtitle's absence is named (`AbsenceValue`, `84f9353`), never left to guesswork | — | none | — | — | X-1 |
| G-33 (gate, not a law) | PASS | no scenario key or name literal in the component (`ScenarioSelect.vue:5-8` docblock; the gate) | a fifth scenario needs no edit here | none | — | — | — |

## `/career/setup/trainee` — SCREEN-003

- **User goal:** pick the trainee this career trains.
- **Primary task:** search, inspect, select.
- **Overall rating:** **Strong**.

| Law or principle | Status | Evidence | User impact | Recommendation | Severity | Issue type | Existing ID |
| --- | --- | --- | --- | --- | --- | --- | --- |
| 4 Hick's / 24 Cognitive Load | PASS | the aptitude block sits behind "Show all aptitudes" (pass-2 Phase 11, option 1); the controller owns the axis order (`TraineeSelectController` `APTITUDE_AXES`) | the selection question stays "who?", not "what are her aptitudes?" | none | — | interaction | wizard audit §2.5 |
| 2 Jakob's Law | PASS | `TraineeCombobox.vue` is a real combobox (listbox + options + `aria-activedescendant` behaviour the runs spec asserts) | the pattern is the platform's | none | — | — | — |
| 3 Fitts / 12 Closure / 27 Consistency | PASS | the screen's sweeps and disclosures in `career-trainee-select.spec.ts` | — | none | — | — | X-1 |

## `/career/setup/target` — SCREEN-004

- **User goal:** state what this career is for.
- **Primary task:** pick a purpose, distances and surfaces, enter per-stat targets.
- **Overall rating:** **Strong**.

| Law or principle | Status | Evidence | User impact | Recommendation | Severity | Issue type | Existing ID |
| --- | --- | --- | --- | --- | --- | --- | --- |
| 18 Goal-Gradient | PASS | each stat field shows the ceiling it aims at; the summary was promoted above the fieldsets (pass-2 Phase 10) | the target reads as a direction, not five numbers | none | — | — | — |
| 5 Miller / 6 Proximity / 8 Common Region | PASS | five stat fields in one fieldset group; ceilings beside their fields | chunked at a glance | none | — | — | — |
| 24 Cognitive Load | PASS | the summary sentence is computed from entered values and says so (pass-2 Phase 10; `BuildTarget.vue`) | the tool's arithmetic is labelled as its own | none | — | — | — |
| 3 Fitts / 19 Zeigarnik / 27 Consistency | PASS | the step bar (post-`4a5ff88`); the screen's spec sweeps | — | none | — | — | — |

## `/career/setup/legacy` — SCREEN-005

- **User goal:** record the ancestry this career inherits from.
- **Primary task:** per grandparent: name, rank, sparks.
- **Overall rating:** **Strong**.

| Law or principle | Status | Evidence | User impact | Recommendation | Severity | Issue type | Existing ID |
| --- | --- | --- | --- | --- | --- | --- | --- |
| 20 Tesler's Law | PASS | `ebcbbac`: each grandparent's spark editor is a disclosure whose visible summary now names her ("Sparks for Grandparent A1"); the wizard writes ancestors as names, not slot records (ADR-0010 §2) | the payload's shape never reaches the Trainer | none | — | interaction | wizard audit §2.5 |
| 7 Similarity / 9 Uniform Connectedness | PASS | the wizard's and the builder's editors are the same shape (`Builder.vue:435-439` names the apartness rule) | one spark grammar everywhere | none | — | — | — |
| 3 Fitts / 12 Closure | PASS | the deck-steps spec's disclosure keyboard path (`career-legacy-deck-steps.spec.ts:195-196`) | — | none | — | — | F-G11 (same pattern) |

## `/career/setup/deck` — SCREEN-006

- **User goal:** equip the six support slots.
- **Primary task:** pick a card per slot.
- **Overall rating:** **Strong**.

| Law or principle | Status | Evidence | User impact | Recommendation | Severity | Issue type | Existing ID |
| --- | --- | --- | --- | --- | --- | --- | --- |
| 4 Hick's / 25 Choice Overload | PASS | the picker narrows per slot and names an ask that matched nothing (`support-deck.spec.ts:267`); one slot is aimed at a time (`?deck_slot=N`) | 500+ cards never meet the Trainer at once | none | — | interaction | — |
| 6 Proximity / 7 Similarity | PASS | the picker rows print type as word + glyph, never colour alone (`support-deck.spec.ts:211`) | — | none | — | — | — |
| 3 Fitts / 19 Zeigarnik / 27 Consistency | PASS | the screen's sweeps; the owned/equipped persistence states (`support-deck.spec.ts:188`) | — | none | — | — | — |

## `/career/setup/preflight` — SCREEN-008

- **User goal:** see the whole entered setup, fix what reads as a gap, start the career.
- **Primary task:** read five composed steps, press Start Career once.
- **Overall rating:** **Strong** (Mixed before pass-2's Phase 14).

| Law or principle | Status | Evidence | User impact | Recommendation | Severity | Issue type | Existing ID |
| --- | --- | --- | --- | --- | --- | --- | --- |
| 21 Occam's Razor / 26 Flow | PASS | one Edit door per previous step, all five distinct (`Preflight.vue:410-442`; the feature test's five-door distinctness case); the duplicate Back/Edit Deck pair is gone | no door is a dead end or a duplicate | none | — | interaction | wizard audit §2.4 |
| 17 Peak–End | PASS | the run is created exactly once on Start Career (the feature test composes the contract); warnings never block | the commitment is deliberate | none | — | — | wizard audit §2.4 |
| 24 Cognitive Load | PASS | each warning carries title + detail + its own Edit link (`:178-202`) — the HLF's single-row shape the slice asked for | a gap is a sentence, not a form | none | — | — | wizard audit §2.4 |
| 19 Zeigarnik | PASS | the step bar's visited marks and the one-column review grids (pass-2 Phase 14.1) | entered vs unentered reads at a glance | none | — | — | wizard audit §2.2 |
| 3 Fitts / 27 Consistency | PASS | `career-preflight.spec.ts` 4 passed this pass | — | none | — | — | — |

# Cross-page consistency findings

| Pattern | Verdict | Evidence |
| --- | --- | --- |
| Step bar | One component, one grammar, all six steps: `aria-current="step"`, count line in two media, sticky md+ with `scroll-padding-top`; the visited/not-started words shipped at `4a5ff88` and ruled final (C-1) | `SetupLayout.vue:61-88`; the 9.1 keyboard pass |
| Disclosure grammar | One pattern everywhere a control is hidden: native `<details>`, `min-h-11` summary, the word carries the state ("visited", "not built", "Facts and mechanics", "Sparks for Grandparent A1") | the four surfaces above |
| Absence grammar | One component (`AbsenceValue`), the reason as the accessible name; every screen in scope migrated (pass-2 Phase 12; wizard slices) | `AbsenceValue.vue` docblock |
| Save feedback | `role="status"` lines and `aria-live` priority, the same shape on both surfaces | pass-2 3.2; `NextDecision.vue:168` |
| Primary action | One enamel per region; secondary actions are bordered links (pass-2 4.2) | the cockpit and wizard screens |
| Type scale | The shell `#title` is `text-2xl`, section headers `text-lg` (`209e05e`, `4d31de4`; `AppLayout.vue:290`) — X-2's spread closed on these surfaces | the shell at HEAD |
| Write protection | Both surfaces' writers refuse double writes (the import guard's measured one-row proof is the pattern; the wizard's Start Career creates exactly once) | A.1's count; `CareerPreflightTest` |

# Prioritized remediation plan

- **P0.** None. The wizard's stepper "done" P0 (the live capture's misleading completion indicator) is
  fixed at `4a5ff88` and ruled final (C-1); its acceptance criterion — a step the session only opened
  never reads as done — holds in the shipped copy, with the rendered re-read **needs measurement** at
  the next build.
- **P1.** None on these two surfaces. The preflight duplication (the audits' P1) landed at `3f9618c`
  and verified this pass.
- **P2.**
  1. **Goal ladder on the cockpit (Law 18).** Acceptance: the goals region reads one "next" plus an
     integer `Goals Achieved {cleared} of {total}` footer, no bar, no percentage, the eight-field row
     contract intact (`CockpitObjectivesTest.php:127`). Blocked on Agent 1's module (Slice 0.3).
  2. **Bundle-lagged renders.** Every verdict above that rests on DOM/copy shipped this pass
     (`4a5ff88`, `0a58c88`, `84f9353`, `ebcbbac`) is **needs measurement**: the run that settles them
     is the next `npm run build` followed by the cockpit, accessibility and six wizard specs on a
     session-private harness, on a quiet box (KI-74 pre-flight first).
- **P3.**
  1. **Continuity's column-order tension (cockpit, tablet).** The brief's CSS-only order is a ruling;
     if it ever changes, re-test the DOM sequence against the new columns. No change now.
  2. **The client's alert vocabulary (Laws-7, ruled "record as convention").** The report records it:
     the client's red exclamation badge (`01:113`), pink failure badge (`01:318`) and pinned
     obligation tag (`05:85`) are alert semantics the app deliberately cut (`app.css:57`,
     `DESIGN.md:53`, §9 item 3). Von Restorff and Closure are scored PARTIAL-with-citation on the
     surfaces where a warning would have carried them; no component may adopt the vocabulary without
     a Track 2 D ruling (`frontend-fidelity-2026-10-13.md` §6 D).

# Verdict on this report's own rules

Every PASS/PARTIAL above carries a `file:line`, a committed test, or a measured number from
`.scratch-uma/agent3-uxlaws/` (`s01-measure.mjs` output, the cockpit and preflight spec runs). No
verdict rests on "it looks clean"; the two tensions the laws lens could not settle inside the ruling
chain (Continuity's column order, the alert channels) are recorded with their governing citations, and
no token, colour, warning role, score or scenario name was introduced anywhere (ux-laws §9, all
fourteen, held).
