# Provenance and absence contracts — reconciliation proposal

> **Reference only. Not authorization.** Nothing in this file is a decision, and no behavior, label,
> badge, copy string, seeder or spec was changed to produce it. Each section names the exact files an
> approval would touch and changes none of them. It exists because the repository carries four live
> semantic contradictions and the next implementation pass must not guess at their resolution.
>
> Recorded 2026-10-09 by the Impeccable audit pass, as the hand-off for an owner decision →
> implementation cycle. **Status changed the same day: §§1-8 are the pre-ruling analysis and stand as
> written; §9 onward records the owner's answers, the text held for the shared files, and the coordination
> escalation. Read §9 before acting on §§1-8.** `SCREEN_SPEC.md` §7 items 18 and 20 are the defect-side records; this file is the
> proposal-side counterpart, kept out of the active specification because `SCREEN_SPEC.md`, `DESIGN.md`
> and `KNOWN-ISSUES.md` are all under concurrent edit in this worktree.
>
> **On the line numbers.** Every `file:NNN` below was re-read from the working tree on 2026-10-09 and its
> cited line checked to contain the thing claimed, not merely to exist. They will still drift: this tree is
> under concurrent edit and `TrainingCard.vue` moved under an earlier draft of this file, which is how the
> first version came to cite the Energy cost span as the deck tally. Each citation therefore carries the
> row or state word it argues for, so a reader who finds a different number still finds the right code.

## 0. What governs what

The chain, per `AGENTS.md` §2, is quality bar → gate registry → accepted ADRs → `PRD.md` →
`ARCHITECTURE.md` → `SCREEN_SPEC.md` (screen behavior) → `DESIGN.md` (visual system). Two findings from
reading it in full:

1. **`ADR-0006` Decision 1 assigns this question to `DESIGN.md`**: root `DESIGN.md` is "the single source
   of truth for the app's visual contract". `DESIGN.md` **defines none of the four labels**: across the whole
   file `Confirmed`, `Calculated` and `Estimated` appear zero times, and it names `ProvenanceBadge` once, at
   `:738`, inside the 2026-10-09 dated correction that records the contradiction rather than resolving it.
   The designated authority is the empty one, which is why §1 below is a proposal rather than a restatement.
   What `DESIGN.md` *does* own here is the absence rule at §4.2 (`:394-401`, "an unrecorded value renders
   `N/A` with a `title` naming the kind of absence … never `Unknown`") and the marker-placement rules in §5
   (`:568-594`); both are quoted below because both bear on §1 and §2.
2. **The repository has two different things called "provenance".** The binding one is *data* provenance:
   `GOVERNANCE.md:251-252` and `:1384` require every engine-owned fact to carry a `data_sources` row
   (`url`, `fetched_at`, `snapshot_path`, `confidence`, `source_timezone`), and "a fact without provenance
   is deleted, not stored". The other is *display* provenance — the four badge labels, which describe how
   a rendered number was obtained. They are related only at the seam `ADR-0001` §2 creates: a derived
   number must show the declared constants behind it. §3 is that seam. Note the collision in vocabulary:
   `data_sources.confidence` is a pipeline field, and the UI word "Confidence" is separately banned as an
   invented statistic — the two must never be conflated by a future implementation.

`ADR-0020` status is **Accepted (owner ruling 2026-10-04)**, so its §2 guardrail list binds.

3. **`ADR-0020` §2's mandate is narrower than it is usually quoted as, and one written definition of the
   labels does exist.** Its sentence (`docs/adr/0020-trainer-desk-2-program.md:73-74`) adopts
   "✓ confirmed / ≈ calculated / △ RNG / ? user input" as the display contract **for the advisor's numbers** —
   not for every figure in the app. That scope has two consequences the owner has to hold while deciding:
   the `Estimated` and `Unknown` chips on the reference views and the scenario panels are outside what the
   accepted ADR regulates, while the advisor surfaces are inside it and ship two labels the ADR does not name
   and omit the two it does. And `config/reference.php:28-34` is the one place in the repository that
   **defines all four labels in writing** — `confirmed` "the doc backs the value with an official notice URL
   or an S-file", `estimated` "the doc backs it only with a third-party guide, or marks the contents unread",
   `unknown` "the doc marks the value unverified", `calculated` "no row here is derived by this app; the
   state is reserved". Those are *source-strength* definitions; `ProvenanceBadge.vue:26-27` defines
   `unknown` as "the state for 'there is no number'", an *existence* claim, and `ADR-0001` §3 defines the
   estimated figure as the tool's own opt-in model. §1 below is therefore not "nothing defines these"; it is
   "**three artifacts define them on three different axes**", and the axis has to be chosen.

4. **The absence form IS settled, and settled at the top of the chain — the earlier draft of this file
   cited the wrong layer.** `GOVERNANCE.md:433-444` carries "**Disclosure pattern (false zero), settled
   ruling 2026-09-28**": a field that is zero because it is untracked or not applicable "renders as `N/A`
   with a tooltip **where useful** … optionally **plus a static disclosure line**", never as `0`, never with
   an em dash. `ARCHITECTURE-ESSENTIALS.md:37` states the same as requirement D-220, adding the operative
   parenthetical that the `title` must name **which kind of absence**. Both sit above `DESIGN.md` §4.2, which
   is where §2 of this file was quoting from. Consequences the owner should see plainly: `AbsenceValue`'s
   visible reason line is the sanctioned "static disclosure line" branch; a `title` tooltip is optional, not
   required; and **neither text mentions a provenance badge on an absent value at all**. So §2 below is
   enforcement of a settled ruling rather than new policy, and a `title` that names the field instead of the
   absence is a compliance gap, not a style preference. What the settled ruling leaves genuinely open is only
   the chip-removal step, because removing a visible element is a behavior change.
   Nothing in `GOVERNANCE.md` mentions the four display-provenance labels: the bar settles the absence form
   and is silent on the badge vocabulary.

## 1. Provenance vocabulary

Census on this tree, counted two ways because the two counts answer different questions and adding them
would be a mistake. **Sites** — places a badge is written: 22 `<ProvenanceBadge>` usages in
`resources/js`. Nineteen carry a literal `state="…"`, and those nineteen are 6 `confirmed`, 6 `calculated`,
3 `estimated`, 4 `unknown`. Three take `:state="row.state"` from the server
(`resources/js/pages/Database/Events.vue:120`, `resources/js/pages/Database/ShopItems.vue:124`, `resources/js/pages/Database/Sparks.vue:137`). **Instances** — badges a
Trainer can actually see at once: the three dynamic sites each sit inside a `v-for` over
`config('reference.*')`, whose 26 declared rows are 24 `confirmed`, 1 `unknown` and 1 `estimated`
(`config/reference.php:105` and `:129` are the two non-`confirmed` rows; every `shop_items` and `sparks` row
is `confirmed`, and no reference row is `calculated` — the file's own mapping at `:33` says "no row here is
derived by this app; the state is reserved"). So 19 + 26 = **45 rendered badges**: 30 `confirmed`,
6 `calculated`, 4 `estimated`, 5 `unknown`, and **0 `RNG`, 0 `user input`** on both counts. The last two are
mandated by `ADR-0020` §2 and `PRD.md` FR-F-3 and are emitted by neither the templates nor the config, while
the shipped `estimated` and `unknown` are named by neither of those two artifacts. The vocabulary is closed
in code, not just in the docs: `ProvenanceBadge.vue:12` exports
`ProvenanceState = 'confirmed' | 'calculated' | 'estimated' | 'unknown'`, and each of the three reference
pages re-declares the same union locally because a consuming SFC cannot put an imported type in
`defineProps<>` (`ProvenanceBadge.vue:6-10`). Any fifth label is therefore a four-file type change, not a
string.

| Label | Proposed meaning | Positive example (shipped) | Exclusion — what it must never mark | Evidence status |
|---|---|---|---|---|
| **Confirmed** | The figure is a reading taken as-is from a named owner: a source-published value, a declared constant, or a value the Trainer entered. No arithmetic was performed on it. | Energy cost `E−28 … E−17` (`TrainingCard.vue:234`, a config constant with source and date); hint-ladder percentages (`SkillsPlanner.vue:213`, "Read off the [Global] Learn screen on 2026-10-03"); observed inheritance outcomes (`InheritanceEvent.vue:346`, "Entered by the Trainer") | Anything the tool computed, summed, counted, compared or interpolated — even when every input is a reading | **Inference from four shipped rationales**, consistent with `ADR-0001` §2's entered/declared split. The *definition* is new policy |
| **Calculated** | The figure is the tool's own arithmetic over readings, deterministic and reproducible, with the arithmetic stated beside it. | Target deficit (`TrainingCard.vue:251`, `state="calculated"` beside the deficit row); RacePlanner target alignment (`RacePlanner.vue:268`, "derived from two recorded values, so it is a comparison rather than a reading"); SP coverage (`SkillsPlanner.vue:180`, "the sum of the base prices"); build-target summary (`BuildTarget.vue:530`, "assembled by this tool from the values entered, not a stored fact") | Any figure whose inputs are not all recorded, and any model output (that is `Estimated`) | **Inference**, and the four rationales above are mutually independent. `ADR-0001` §2's R4 replacement is the nearest contract text |
| **Estimated** | Two candidate meanings, and the shipped chips follow the one the accepted ADR does **not** describe. (a) `ADR-0001` §3: the tool's own model output, **off by default**, formula and parameters on the same surface, labelled as this tool's model and not the game's. (b) `config/reference.php:31`: "the doc backs it only with a third-party guide, or marks the contents unread" — a *weak source*, not a model. | All four shipped chips are meaning (b): `UnityCupPanel.vue:110` (the +30 read beside the S emblem), `:164` (a band table transcribed from one publisher), `InheritanceEvent.vue:249` ("Sourced probabilities from the chosen parents and grandparents. **No outcome is computed**"), and `config/reference.php:129`. **Meaning (a) is implemented nowhere:** the `failure_estimate` preference is stored and read back but renders no number — `tests/Feature/PreferenceControlsTest.php:24` states the reason in its own docblock, "it shows no number, because there is no number", and `TrainerAdvisor` ships the two bands only. So no chip today claims a modelled figure, and the opt-in half of `ADR-0001` §3 is an unbuilt option, not a violated rule | A sourced value (that is `Confirmed` under meaning (a), `Estimated` under meaning (b) — the two readings disagree here); a game mechanic presented as fact; any win probability (`ADR-0016`, `PRD.md` §6.11, and `design-2.0` §49's own "Estimated win chance: 82%" example is the banned figure, held by that document's governance banner) | **The word is authorized by two artifacts on different axes.** `ADR-0001` §3 authorizes (a) and the app does not do (a). `config/reference.php` defines (b) and the app does do (b), outside the advisor scope |
| **Unknown** | *No single defensible proposal, and two live definitions already compete.* `config/reference.php:32` defines it as **"the doc marks the value unverified"** — a statement about a source. `ProvenanceBadge.vue:26-27` defines it as "the state for 'there is no number'" — a statement about a value. `DESIGN.md` §4.2, restated in a dated 2026-10-08 correction, says an unrecorded value renders `N/A` with a reason naming the kind of absence, **"never `Unknown`"**. Nothing in the accepted chain arbitrates | The two shipped meanings: the reference Events row (source unverified) and the four absence chips (value absent) | A value the tool derived, and any value that is present and well-sourced | **Contradicted on one reading, authored on another.** The five emissions split: 1 is a source-strength mark with a written definition, 4 are absence marks the absence rule forbids. This is the label whose meaning the owner must pick, per surface class |

Two consequences the owner must rule on, stated without preference:

- **`Estimated` and `Unknown` are not the same kind of accident, and neither is a simple omission.**
  `ADR-0001` §3 authorizes an *opt-in modelled figure* and the app renders none — the `failure_estimate`
  toggle stores and restores a value that gates nothing (`tests/Feature/PreferenceControlsTest.php:24` says
  so outright). The four shipped `Estimated` chips instead mark *weakly sourced* figures, which is the
  `config/reference.php:31` meaning. So `Estimated` is not "authorized in substance and merely missing from
  `ADR-0020` §2's list"; it is a **second, undocumented sense of the word**, used on surfaces the ADR does not
  reach. `Unknown` is a third case: a written source-strength definition in one file, a value-existence
  definition in the component, and a prohibition on unrecorded values in `DESIGN.md`.
- **`RNG` and `user input` must not be added because `ADR-0020` names them.** No shipped figure is a dice
  roll, and Trainer-entered values already read as `Confirmed` under the proposed table. Introducing two
  labels with no referent would be vocabulary, not information.

The deck tally (`TrainingCard.vue:242`, the "Supports entered for this training" badge) and the published
caps (`ScenarioPanel.vue:204`, sourced from `ScenarioCaps::caps()`, which computes
`min(base_cap + cap_bonus, hard_cap)`) are the two call sites where the proposed table and
`SCREEN_SPEC.md` disagree: the SCR-CAR-012 Figure/Source table records the deck tally as `Confirmed` at
`:1961` and "Current value, target, cap" as `Confirmed` at `:1964`, while `:2323` records the Skill Point
coverage — also a sum over stored rows — as `Calculated`. Under the proposed table both become `Calculated`.
`SupportCardEffects::atCap()` stays `Confirmed`: it selects the highest non-`-1` anchor and formats it, and
performs no arithmetic (`:1962` states that row and labels it `Confirmed`, which the reading supports).

### 1a. Runtime inventory of the label, deduplicated

Counted from the tree on 2026-10-09 by scanning every `resources/js` SFC for `<ProvenanceBadge` and then
resolving each state to its producer. **Sites** and **rendered instances** are two different denominators and
are never added together.

| Producer | Sites | Emission | Reachable instances | What the label qualifies | Meaning axis in force | Coverage |
|---|---|---|---|---|---|---|
| `TrainingCard.vue:226/:323/:365` | 3 | `unknown` (literal) | 3, when the value is absent | An `AbsenceValue` in a figure row | value-existence | No test asserts these three chips |
| `ScenarioPanel.vue:246` | 1 | `unknown` (literal) | 1, when a panel is undrawn | A prose absence line | value-existence | `tests/browser/grand-concert-panel.spec.ts:83` |
| `TrainingCard.vue:234/:242`, `ScenarioPanel.vue:204`, `SkillsPlanner.vue:213`, `InheritanceEvent.vue:346/:376` | 6 | `confirmed` (literal) | 6 | A figure or an entered record | reading-vs-derivation | `career-inheritance-event.spec.ts`, `career-skills-planner.spec.ts`, `support-cards.spec.ts`, `scenario-panel.spec.ts` |
| `TrainingCard.vue:251/:311`, `BuildTarget.vue:530`, `RacePlanner.vue:268`, `Result.vue:161`, `SkillsPlanner.vue:180` | 6 | `calculated` (literal) | 6 | A derived figure | reading-vs-derivation | `career-build-target.spec.ts`, `career-skills-planner.spec.ts`, `support-deck.spec.ts` |
| `UnityCupPanel.vue:110/:164`, `InheritanceEvent.vue:249` | 3 | `estimated` (literal) | 3, two of them flag-guarded | A weakly **sourced** figure, never a model | source-strength | `unity-cup-panel.spec.ts:58`, `career-inheritance-event.spec.ts` |
| `resources/js/pages/Database/Events.vue:120`, `resources/js/pages/Database/ShopItems.vue:124`, `resources/js/pages/Database/Sparks.vue:137` | 3 | `:state="row.state"` from `config('reference.*')` | **26 rows** — Events 10 (8 `confirmed`, 1 `unknown`, 1 `estimated`), ShopItems 10 (`confirmed`), Sparks 6 (`confirmed`) | The row's citation strength, not any figure | source-strength, defined in writing at `config/reference.php:30-33` | `tests/Feature/DatabaseTest.php:125`/`:129` (two rows only), `tests/browser/database.spec.ts:69-70` |
| — | 0 | `rng` / `user-input` | 0 | — | — | Mandated by `ADR-0020` §2 and `PRD.md` FR-F-3, emitted by nothing |

Totals: **22 sites, 45 rendered instances** — 30 `confirmed`, 6 `calculated`, 4 `estimated`, 5 `unknown`,
0 `RNG`, 0 `user input`. The two reference-view facts nobody should have to rediscover: the config's
`:33` reserves `calculated` ("no row here is derived by this app"), and **nothing validates the config strings
against the union** — `ProvenanceBadge.vue:61` indexes `BADGES[props.state]` with no fallback, so an
out-of-vocabulary value in `config/reference.php` is a render-time failure on a Database page, not a build
failure.

Three identifiers spell `unknown` and are **not** this label; they must not be swept into any §1 or §2 change:

| Identifier | File | Values | Renders as |
|---|---|---|---|
| Scenario Link state | `app/Models/SupportCard.php:186-205` | `linked` / `not_linked` / `unknown` | `N/A` with the note as its `title` — never a badge (`app/Http/Controllers/TrainingRunController.php:1182-1183`, `app/Http/Controllers/Career/DeckSelectController.php:206-207`) |
| Cap-bonus shape | `app/Http/Controllers/Career/ScenarioSelectController.php:158-174` | `uniform` / `peaked` / `unknown` | Prose, or nothing: `resources/js/pages/Career/ScenarioSelect.vue:78` returns `null` for `unknown` |
| Pipeline candidate verdict | `app/Enums/CandidateStatus.php` | `Pending` / `Confirmed` / `Aliased` / `Rejected` | **Displayed as a word on `/review`**: `resources/js/components/review/CandidateForm.vue:44` offers `<option value="Confirmed">Confirm (merge into suggestion, or create)</option>`. Same word, different claim — a human adjudication, not a figure's origin |
| Advisor/legacy prose | `resources/js/pages/Career/InheritanceEvent.vue:20` | "Predicted carries `Estimated`; Observed carries `Confirmed`" | A fifth voice: a page's own docblock states a vocabulary rule that no specification writes down |

## 2. Absence versus provenance ownership

**Proposal requiring approval:** `ProvenanceBadge` qualifies a *value that is displayed*. `AbsenceValue`
owns the *statement that no value exists*, including which kind of non-existence it is. A row is therefore
either a figure with a label, or an absence with a reason — not both.

Split the claim in two, because the chain settles one half and not the other. **Settled:** how an absent
value renders. `GOVERNANCE.md:433-444` (the bar, top of the chain) and D-220 through
`ARCHITECTURE-ESSENTIALS.md:37` require `N/A` plus a `title` naming which kind of absence, with an optional
static disclosure line, and forbid a default or a `0`. `ADR-0020` §2 line 70 repeats it as "a named absence or
an `N/A`, never invented". So the `AbsenceValue` side of the split is existing requirement, not proposal.
**Not settled:** whether a provenance chip may accompany that absence. No artifact in the chain says either
way, and `ProvenanceBadge.vue:26`'s "A figure with no badge is a bug" pulls in the opposite direction from
`DESIGN.md` §4.2's "never `Unknown`". The proposal is therefore new policy on one axis only — it decides that
a badge's scope is a displayed value, which is the reading under which the two existing statements stop
contradicting each other. Approving it does not change how any absence renders; it removes four chips that
currently sit beside an absence that already renders correctly.

Tested against the cases named in the brief:

| Case | Under the proposal |
|---|---|
| Energy not recorded | `AbsenceValue` only. Reason: the Trainer's own gap ("this run has recorded no Energy") |
| Target not set | `AbsenceValue` only. Same class: Trainer's gap |
| Deck count | A figure. `ProvenanceBadge`, and the label question defers to §1 |
| Published cap | A figure. `ProvenanceBadge`, label defers to §1 |
| Observed inheritance result | A figure, `Confirmed` — the existing title already says "entered by the Trainer" |
| Derived deficit | A figure, `Calculated` — both readings agree |
| Design exclusion (no source publishes it) | **The hard case.** It is an absence, so `AbsenceValue` owns it — but four shipped `unknown` chips assert it is a figure with a provenance (enumerated below; a fifth `unknown` in the runtime population marks a reference row's source strength and is not in this dispute). Approving §2 means removing those four chips; declining it means §4.2 needs an amendment |

> **Ruled and applied 2026-10-09 (owner ruling 2a): all four chips in this section were removed from the
> working tree.** The `ScenarioPanel.vue:245-248` and `TrainingCard.vue:226`/`:323`/`:365` coordinates below
> are the pre-ruling positions, kept because the analysis names them; §16 carries the post-edit state.

Do not read the last row as a recommendation to delete badges. Four chips are in this dispute, not five, and
one `unknown` was wrongly counted in an earlier draft of this file. The four are the literal chips that sit
beside an `AbsenceValue` or an absence line: `TrainingCard.vue:226` (Expected gains), `:323` (Failure
probability), `:365` (Bond gains) and `ScenarioPanel.vue:246` (the `panel_absence` line). The fifth
`unknown` in the runtime inventory — the Events row declared at `config/reference.php:105` and rendered
through `resources/js/pages/Database/Events.vue:120` — is a **different kind of object**: it qualifies how well the whole row is
sourced ("the doc marks the value unverified", the config file's own mapping at `:32`), sits beside no value
at all, and would remain under §2 even if all four absence chips were removed. Approving §2 changes those
four and nothing else. A grep for `state="unknown"` misses the fifth and so under-counts the vocabulary
population while over-counting the change surface; §1's census and this paragraph are counted separately on
purpose.

They are not four identical cases either. The three in `TrainingCard` badge an `AbsenceValue` sitting in a
*figure* row — each has a `<dt>` asking for a number ("Expected gains", "Failure probability", "Bond gains")
— so removing the chip changes what a Trainer sees on a row they currently read as labeled.
`ScenarioPanel.vue:245-248` badges a `panel_absence` line that is already a prose absence statement with no
figure row above it, which is the case where the two owners most obviously overlap.

## 3. Making load-bearing constants visible

`ADR-0001` §2 requires the constants behind a derived number to be "visible wherever the number appears",
and §7 identifies session cost 17–28 and injury −5 to −10 as the load-bearing pair, both from a
2023-02-25 source flagged **STALE**. Today `TrainingCard.vue:96-101` composes source, read date and stale
flag into `costTitle`, which reaches the browser only as the `title` attribute on the figure at `:233`,
beside a `Confirmed` chip at `:234` — so the requirement is met by hover and by
nothing else. **The requirement is not in question; only the presentation is.**

Three patterns, none implemented. Before them, one precedent that changes the cost estimate: **the app
already prints this kind of metadata visibly.** `ScenarioPanel.vue:205-208` renders `Verified {{
props.scenario.caps.verified_at }}` as a visible `text-xs` sibling beside the `Confirmed` chip at `:204`, and
`Dashboard.vue:300-301` prints the same `Verified <date>` string on the data-status line. So a visible read
date is an existing shipped idiom, not a new one; what is missing everywhere is a visible **source** and a
visible **stale** flag, both of which live only in `title` text today.

The `title` route has a hard floor worth stating plainly: **a `title` tooltip is not reachable by keyboard and
not reachable by touch.** `AGENTS.md` §13 and the browser specs hold the 44px touch contract, so the app is
written for touch use, and on a touch device the constant's source and its stale flag are simply unavailable.
Screen readers sit between the two cases, and the difference matters for any §3 decision. A badge folds its
caller's `title` into its own accessible name (`ProvenanceBadge.vue:63`, with `role="img"` at `:68`), so
`Confirmed: <reason>` is announced as one statement. The TrainingCard cost's reason sits on a plain `span` at
`:233`, where a `title` is at best optional help text: it is not in the accessible name and is announced
inconsistently at best, never on a keyboard or touch path. So the sentence "the reason is announced" is true
of the badge and not of the figure, and **`ADR-0001` §2's word "visible" fails for both on keyboard and touch
regardless of how the screen-reader case resolves.** That is not a design preference, and it is the strongest
argument for P1: an inline metadata line is the only pattern that satisfies sighted keyboard users, touch
users, and screen-reader users with one mechanism.

**P1 — inline metadata line under the figure.** `E−28 … E−17 ✓ Confirmed` then
`GameWith 2023-02-25 · stale` in `text-xs text-ink-muted`.
Accessible: no new interaction, nothing hidden, announced in reading order. Responsive: adds one line per
figure; at 320px single-column this is the cheapest option. Fits the language: yes — the repo already uses
`text-xs text-ink-muted` sub-lines for exactly this register. New interaction: none. Touches
`ProvenanceBadge`: no. Preserves copy: yes, the string is already written. Cost: it is the one pattern that
makes the stale flag impossible to miss, and it is also the one that adds height to the densest card.

**P2 — reuse `AbsenceValue`'s disclosure for present values.** A `▸` on the figure that opens the
source/date/stale note.
Accessible: keyboard and screen-reader reachable, proven by the migrated surfaces. Responsive: bounded by
the existing `max-w-sm`; no overflow measured. Fits the language: partly — but it hides required
information behind a deliberate action, which is what §4.2's rule is about. New interaction: no new
component, though it extends `AbsenceValue` beyond absence, which §2 argues against. Touches
`ProvenanceBadge`: no. Preserves copy: yes. **Weakest on this requirement specifically: `ADR-0001` §2 says
visible, not reachable.**

**P3 — a per-card "constants" affordance listing every constant the card used.**
Accessible: needs a new disclosure or dialog; the drawer pattern exists in `RaceCard.vue`. Responsive:
worst of the three at 320px. Fits the language: arguably the most tactical. New interaction: yes — a
second provenance surface, which risks becoming the "second provenance system" the absence pattern was
explicitly kept from becoming. Touches `ProvenanceBadge`: likely yes. Preserves copy: yes, and adds more.

No pattern is recommended, because the choice is a density-versus-visibility trade on the app's densest
surface and that is a design judgment. **P1 is the only one that satisfies `ADR-0001` §2's word "visible" without a
new interaction**, if the owner wants a starting point.

## 4. KI-69 Class 3 — the fixture contract

Established by reading, not by running, and re-read after the two peer commits that landed during this
session. `career-inheritance-event.spec.ts:36-97` builds its state through the application — create a run,
log turns, open the run-scoped Legacy Lab builder, then
`selectOption({ label: 'Symboli Rudolf' })` at `:71` and `'Special Week'` at `:85` on
`select[name="legacies.0.legacy_id"]`. Those two labels are the **whole library dependency**: every other
field the fixture touches is typed rather than chosen from data.

**But the library is only one of Class 3's two blockers, and fixing it does not turn the suite green.**
`KNOWN-ISSUES.md` KI-69 (still **OPEN**, re-read at `:762-783`) measured both: with Veterans inserted the
failure moved off the picker and onto `input[name="legacies.0.rank"]`, because the shipped builder renders
**no `name` attribute on any of the other legacy fields**. Measured on the current tree:

| Field the spec addresses by `name` | What `Builder.vue` actually renders | Locator available today |
|---|---|---|
| `legacies.N.legacy_id` | `:name="controlName"` on the select (`resources/js/pages/Legacy/Builder.vue:242` → `AncestryNode.vue:139`) | works |
| `legacies.N.rank` (`spec:72`, `:86`) | `:id="\`rank-${parent.slot}\`"`, no `name` (`:309`) | `#rank-{slot}` |
| `legacies.N.ancestors.M` (`spec:73-74`, `:88-89`) | `:id="\`ancestor-${parent.slot}-${ancestorIndex}\`"`, no `name` (`:269`) | `#ancestor-{slot}-{i}` |
| `legacies.1.is_guest` (`spec:87`) | bare checkbox: no `id`, no `name` (`:321-325`) | `getByLabel('Rented from a friend')` |
| `legacies.N.sparks.K.kind` (`spec:76`, `:80`, `:91`, `:95`) | select inside a `<label>` with no `id`, no `name` (`:349-359`) | `getByLabel` on the kind label |
| `legacies.N.sparks.K.target` and `.stars` (`spec:77-78`, `:81-82`, `:92-93`, `:96-97`) | inputs with neither `id` nor `name` (`:364-368`, `:373-379`) | `getByLabel('Applies to')` / `getByLabel('Stars')` |

Nineteen of the fixture's locators therefore address a form contract the component does not have, exactly as
KI-69 states: "The spec was written against a form contract the component does not have." So **Option A is
necessary and not sufficient**, and the honest scope of Class 3 is *two* changes: seed or otherwise provide
the roster rows, and choose one of the two locator strategies.

| Choice | What it means | Cost |
|---|---|---|
| A1 — re-target the spec at ids and labels | Test-only change. The two fields with ids travel as `#rank-…`/`#ancestor-…`; the four without ids travel as `getByLabel`. Leaves the builder's markup untouched | Spec edits across ~19 lines; and the missing `name` attributes stay a latent trap for the next spec |
| A2 — add `name` attributes to the builder's legacy inputs | Production change to `Builder.vue` (and `AncestryNode.vue`), matching the contract the spec already assumes | Touches a component under concurrent edit; needs the same field-name audit the picker fix needed |

`A1` is the smaller and safer change and is what this file recommends alongside the label repoint, because
the builder posts through Inertia's `form.legacies` object rather than a native form submit, so `name`
attributes are a testability affordance here, not a functional requirement — and that reasoning is exactly why
they were never added.

`TrackerVeteransSeeder` files seven Veterans from the committed tracker: Maruzensky ×2, Mayano Top Gun,
Haru Urara, Daiwa Scarlet, Vodka ×2. Neither named trainee appears. `LegacyController::roster()` renders
`name` as the bare `umamusume->name`, so the two duplicated trainees also produce two visually identical
options — a separate finding, recorded in the appendix.

Related and worth stating plainly: **the picker could not have worked at all before this session's
`AncestryNode` fix**, because the selection was dropped on save. Part of Class 3's seven failures was a code
defect, now fixed. What remains is **both** blockers above, not fixture identity alone — the seeder is
deliberately absent from `DatabaseSeeder` (`database/seeders/TrackerVeteransSeeder.php:50`, "a fresh install
does not silently write Trainer history"), so `migrate --seed` on the browser scratch database still leaves
`veterans` empty, and the harness build step has to opt into running it.

| Axis | Option A — repoint the specs | Option B — deterministic test-only Veterans |
|---|---|---|
| Fidelity to real user data | Uses the same rows a Trainer would see. The chosen parents are real careers, not props | Invents careers that never happened, even when labelled synthetic |
| Determinism | Fully — the seeder is idempotent on `import_source` | Fully, if the fixture is deterministic; but it adds a second source of library rows to keep stable |
| Maintenance | Two literals in one spec file, plus the ~19 locators of A1 or the `name` attributes of A2 | A new seeder or factory path, plus its own test, plus a rule for who may run it |
| Product semantics | None touched | Forces a decision about whether a Veteran may exist without a real completed career behind it — `PRD.md` FR-G-1 defines one as "a record built from a completed run" |
| Contamination risk | None | Real: a synthetic-history seeder is one mis-wired registration away from appearing in a Trainer's library, and `AGENTS.md` §11 already treats seeded bodies as committed data |
| Harness compatibility | Needs one addition either way: `tests/browser/global-setup.ts:64` runs `php artisan migrate --seed`, and `DatabaseSeeder` does **not** register `TrackerVeteransSeeder` (`database/seeders/DatabaseSeeder.php` calls five seeders; `TrackerVeteransSeeder.php:50` states it is deliberately unregistered so a fresh install never writes Trainer history). So Option A needs the harness to opt into the seeder — a change to the KI-69 contract too, just a smaller one | Needs the harness build step to opt into a second seeder, which is the same class of change |

**Recommendation, labeled as a decision and not implemented:** **Option A** on the library half — repoint
`{ label: 'Symboli Rudolf' }` and `{ label: 'Special Week' }` at two of the three *uniquely-named* seeded
trainees, `Mayano Top Gun` and `Daiwa Scarlet`, because the duplicate names would make a
`{ label: 'Maruzensky' }` or `{ label: 'Vodka' }` selection ambiguous — paired with **A1** on the locator half
(re-target the nineteen `name=` selectors at the ids and labels the component really renders). Option A leaves
the assertions meaning the same thing: they still ask what the inheritance screen shows once a Legacy
selection exists, and the screen's own props are unchanged by the choice. Option B buys nothing Option A does
not, at the cost of inventing trainer history that `FR-G-1` defines as recorded, not constructed.

One consequence worth stating before anyone picks: **neither option makes Class 3 green on its own.** The
fixture blocker and the locator blocker are independent, and the harness has to opt into the seeder either way.
`KNOWN-ISSUES.md` KI-69's Class 3 heading says the fixture "needs data no code path creates"; measured today
it needs that data *and* a locator contract the component honours.

## 5. CareerStatePanel absence interaction

The problem, stated precisely: `CareerStatePanel.vue:64-69` puts two `AbsenceValue` markers and a `/`
separator in one `flex items-baseline` row, and `:76-78` additionally prints a visible prose reason for the
same state. So one row states the same gap twice — once as the disclosure's accessible name (`:65` and `:68`
carry `currentHint`/`targetHint`, which name the kind of absence: "No turn has recorded this stat yet." /
"No target was entered for this stat.") and once as the shorter `v-else` sentence "No reading recorded yet" /
"No target set", which names the same gap less specifically — and opening
either marker grows its box against the separator. `DESIGN.md:571` says a caller that prints prose needs no
component; here both run.

The second half of the same panel is worse, and the owner should see it before choosing. `:82-97` renders the
four meta values through **three different absence idioms in one `<dl>`**: `EnergyGauge` at `:86` owns its own
unrecorded rendering, `MoodPill` at `:87` takes an `unrecorded="N/A, not recorded"` prop string, and Fans and
Skill Points fall through to `AbsenceValue` at `:88-93`. So the panel a Trainer reads as one block already
answers "what does a missing value look like" three ways, and a fourth through the two stat markers. Any §5
choice that touches only `:64-69` leaves that split in place; naming it here is the whole reason for the
section.

The five states, spelled out against the code as it runs today (`:64-78`), before any option is chosen:

| State | Marker row `:64-70` | Below it `:73-78` | Keyboard | What a screen reader hears |
|---|---|---|---|---|
| Current absent, target present | `N/A` marker, `/`, the target number | progress bar hidden (`:73` needs both), prose **"No reading recorded yet"** | one disclosure tab stop | label, "N/A, No turn has recorded this stat yet.", the target value, then the prose sentence again |
| Current present, target absent | the number, `/`, `N/A` marker | bar hidden, prose **"No target set"** | one disclosure tab stop | label, the value, "N/A, No target was entered for this stat.", then the prose |
| **Both absent** | `N/A`, `/`, `N/A` | bar hidden; the ternary at `:77` picks **only** "No reading recorded yet" | two tab stops | two distinct reasons announced, then **one** prose sentence — the target gap is named only inside its disclosure |
| Both present | two numbers, `/` | bar renders; no prose | none | label, value, value |
| Any marker opened | its reason becomes a visible line inside the same flex row, so the row grows and the `/` stays baseline-aligned | unchanged | `Enter`/`Space` toggles; the `<summary>` is the stop | the reason is already in the accessible name, so opening adds nothing audible |

Options, none implemented:

- **O1 — prose only, no disclosure.** The row keeps the `:76-78` sentence and drops the two markers. This is
  the option the visual system already asks for: `DESIGN.md:571` says a caller that "already prints its
  reason as visible prose beside the marker keeps doing that **and needs no component**", and the bar's
  settled ruling (`GOVERNANCE.md:433-444`) sanctions the static disclosure line on its own. The prose is not
  conditional in a way that hides anything: `:73`/`:76` make it print whenever either value is null, so the
  reason is always visible. Cost: the reason becomes shorter than the disclosure's (`"No reading recorded
  yet"` names no kind of absence beyond "not recorded"), and the two gaps collapse into one sentence in the
  both-null case.
- **O2 — one paired disclosure.** The row becomes a single `N/A / N/A` marker whose reason names both sides.
  Compact, one tab stop, but a screen reader hears one announcement for two distinct gaps, which is worse
  for the current/target distinction the panel exists to show.
- **O3 — two independent disclosures, each `min-w-0 flex-1`, separator demoted to a word (`to`).** Keeps the
  per-field reason that `currentHint`/`targetHint` already write, keeps two tab stops where two gaps exist,
  and costs one line of height when either opens. This is the shape the TrainingCard row fix uses. Cost: it
  keeps the duplication `DESIGN.md:571` argues against, and it raises the compact-24px target question twice
  per row (`AbsenceValue.vue:42` records compact as the deliberate dense form at 24px against the house
  44px, and `DESIGN.md:572` sanctions that).

**What changed in this draft:** the earlier version recommended O3. Read against `DESIGN.md:571` the
recommendation inverts — **O1 is the conformant option**, because the rule says a caller printing its reason
as visible prose needs no component, and this caller does print prose. O3 is the option that carries more
information per gap; it is also the option that keeps a second, redundant statement of the same fact. The
choice is therefore a real trade the owner makes, not a default the repo has already settled: `DESIGN.md`
speaks to absences whose reason needs a component at all, and this row already has a sentence. If O1 is
chosen, the prose should name the kind of absence (it currently says "No reading recorded yet" / "No target
set"), because the bar's rule is a `title` or line "naming which kind of absence" (D-220).

## 6. SaveVeteran parent-rank wording

`SaveVeteran.vue:248-249` puts `title="The parent's own rank as the career recorded it."` on the span that
renders `Rank: {{ parent.rank ?? 'N/A' }}`, inside the parent list that begins at `:244`. The read path is
worth naming before any wording is chosen, because an earlier draft of this section got it slightly wrong:
the value comes from `AncestryGraph::build($run->legacySelection(), …)` at
`app/Http/Controllers/Career/SaveVeteranController.php:96-101`, and `AncestryGraph.php:160` takes
`$legacy['rank']` — **this** career's own Legacy Lab entry for the parent. It is not the parent's career
record, and no Veteran row is read for it. So the current sentence is true but easy to misread, and the fix
is not only "state a reason": it is "say whose record". Distinguish the three things a sentence can be:

- **fact about the source** — "the parent's own rank as the career recorded it" (current text; true, not a
  reason, and ambiguous about *which* career)
- **absence reason** — why this particular row is empty, which is what D-220 and the settled ruling ask the
  `title` to carry
- **provenance** — how a present value was obtained (the badge's job, not the reason's)

Candidates, neither shipped, each checked against the read path above:

- **C1** "You have not recorded a rank for this parent." — states the gap, names the right owner, and matches
  the sibling component's existing form word for word: `AncestryNode.vue:100` already says
  `reason="You have not recorded this Legacy’s own rank."` for the same field on the builder. An earlier draft
  of this section proposed "This parent's rank was never recorded **on the career it came from**", and that is
  withdrawn: the read path is this career's own payload, so blaming the parent's career would state a source
  the code never touches.
- **C2** "You have not recorded a rank for this parent, so the row is blank rather than zero." — C1 plus the
  false-zero guard the bar carries at `GOVERNANCE.md:439` ("Never render `0` for a value the schema cannot
  observe"), at the cost of a longer string inside a narrow chip.

C1 is the minimal conforming correction, C2 the more informative one; both satisfy the requirement, which is
D-220's and not taste. Final wording stays the owner's.

One more thing on the same list, recorded rather than acted on: `SaveVeteran.vue:246` renders
`parent.name ?? 'Not recorded'` — a prose fallback — while `:249` renders `Rank: … 'N/A'`, the ruled idiom.
One `<li>`, two absence forms. The settled ruling and D-220 govern the `N/A` half; nothing settles the
fallback-word half, and `AGENTS.md` §5's "never a default" reads as though a substituted word *is* a default.

## 7. What each approval would change

| Approval | Files that would change |
|---|---|
| §1 vocabulary definitions | `DESIGN.md` §5 (new), then `TrainingCard.vue:234`/`:242` and `ScenarioPanel.vue:204` labels if the reading/derivation rule is adopted, and the `SCREEN_SPEC.md` SCR-CAR-012 Figure/Source rows `:1957`-`:1965` plus the Skill Point coverage line `:2323` to match |
| §2 absence ownership | the four absence-adjacent `unknown` call sites (`TrainingCard.vue:226`/`:323`/`:365`, `ScenarioPanel.vue:246`), or `DESIGN.md` §4.2 — one or the other must move. The reference views' `unknown` row is not in this set |
| §3 constant visibility | `TrainingCard.vue` (the `costTitle` site at `:233`/`:234` and its computed at `:96-101`); under P2 also `AbsenceValue.vue`'s contract; under P3 also `ProvenanceBadge.vue` and a new surface. `SkillsPlanner.vue:214`, `RaceCard.vue` and `ScenarioPanel.vue:205-208` carry the same register and should move together or be named as exceptions |
| §4 Class 3 | `tests/browser/career-inheritance-event.spec.ts:71`/`:85` (labels) **and** `:72-97` (the nineteen `name=` locators, via A1) **or** `Builder.vue` / `AncestryNode.vue` input markup (via A2), **and** `tests/browser/global-setup.ts:64` (opt the seeder in). Option B adds a new seeder and its test |
| §5 panel interaction | `CareerStatePanel.vue:60-78` — under O1 the two markers at `:65`/`:68` go and `:76-78` stays; under O3 the row layout and the `:67` separator change |
| §6 rank copy | `SaveVeteran.vue:248-249` |
| Register entry | `KNOWN-ISSUES.md` KI-81 for the picker defect, which is owed and cannot be written correctly until a closure commit exists. The G-4 disposition in the appendix is a second register candidate (it belongs under KI-58's own entry, whose reproducer no longer reproduces) |

## 8. Explicitly unchanged by this pass

No badge label changed. No absence site migrated. No component redesigned. No copy string shipped.
`TrackerVeteransSeeder` unmodified and unrun. No Class 3 spec altered. No fifth provenance category
proposed anywhere in this file. No database touched.

### 8a. Evidence freshness, stated so a later pass does not read a stale figure as current

The runtime evidence behind this file is **from 2026-10-08 and predates the D1-D5 commits**
(`291f6ea`, `033c974`, `a4b2b26`, `fdebcc9`, `be02297`, `dd6dd22`, all 2026-10-09):

- Last executed browser gate for these surfaces: `24 passed / 0 failed` (`.scratch-uma/gate-final3.log`,
  20:04Z), covering `ancestry-node-picker`, `career-legacy-deck-steps`, `career-training-detail`, `legacy`.
- Earlier logs record `51 passed / 0 failed` twice (15:31Z, 16:54Z) **before** `ancestry-node-picker.spec.ts`
  existed, and `51 passed / 1 failed` at 18:42Z (`.scratch-uma/gate-p3.log`) where the picker's
  "reads the current pick back from its prop" case failed on `picked: 9` vs `picked: 7`. That failure was
  fixed and re-run as the 24/0 above.
- **Citing "51/0" alongside "24/0" as the current checkpoint is therefore wrong**: no 51-case run has ever
  included the picker spec and passed.
- `python tools/gate.py`: re-run 2026-10-09, still `GATE FAIL: 10 finding(s) across 3 file(s)` (see appendix
  item 4). It reads only `docs/design-research/prototypes/*.html`, so the D1-D5 commits cannot have changed it.
- Not run this pass and not claimed: `php artisan test`, `npm run typecheck`, `vendor/bin/pint`, PHPStan, the
  lore gates, and `migrate:status`. The D3 commit added a migration
  (`database/migrations/…_add_ownership_to_deck_slots_table.php`), so `AGENTS.md` §9's dev-database check is
  **owed to the next pass** and KI-60's failure mode is live on this tree.

## Appendix — four observations recorded, not acted on

1. `LegacyController` exposes the library twice: the browse list labels rows `"<name> · run #<id>"` while
   `roster()`, which feeds the builder's picker, labels them with the bare trainee name. A library holding
   two careers for one trainee therefore shows the Trainer two indistinguishable options. The docblock
   above `roster()` claims the two "cannot disagree about who is in the library", which is true of the
   population and not of the label.
2. **The words `unknown` and `Confirmed` are already spoken by other vocabularies.** Enumerated in §1a. The
   sharpest case: `SupportCard::scenarioLinkState()` (`app/Models/SupportCard.php:186-205`) returns
   `linked` / `not_linked` / `unknown`, and its `unknown` renders as `N/A` with the note as its `title` — not
   as a badge (`app/Http/Controllers/TrainingRunController.php:1182-1183` and `:1194`,
   `app/Http/Controllers/Career/DeckSelectController.php:206-207`); two callers read it, so it is genuinely
   one owner, which is the right design. Whatever §1 decides, an implementation pass must not assume those
   `unknown`s are the badge's, and `ADR-0020` §2's `RNG` / `user input` labels would arrive into that
   collision — while `/review` already shows the word **Confirm** to mean "a human resolved this match"
   (`resources/js/components/review/CandidateForm.vue:44`).
3. `docs/design-research/CONSTRAINTS.md` is absent from this tree — its D-rules (D-20, D-100, D-256) are
   cited from components and from `ADR-0001` §6, and `AGENTS.md` §17 says folded sources were deleted after
   verification. Any §1 approval should cite the master that now carries those rules, not the deleted file.
4. **The design-artifact gate is failing, and its cause is only half resolved.** `python tools/gate.py` exits
   1 with ten `[G-4 raw hex]` findings across `screen-a-scenario-v10.html`, `screen-c-event-v1-inline.html`
   and `screen-c-event-v2-preview-column.html` — **tracked** prototypes, clean in the tree, committed at
   `e0e043c` (2026-09-27); reproduced identically on 2026-10-09. `KNOWN-ISSUES.md` KI-58 (still **OPEN**,
   filed 2026-10-03) describes the instrument fault: the loader read a `docs/design-research/DESIGN.md` that
   the re-baseline folded and deleted, and the "few tokens" guard could not fire because the second leg alone
   cleared the threshold. That fix has landed — `7d8b62a` (2026-10-08) points `load_token_hexes()` at
   `resources/css/app.css` — and KI-58's three reproducer hexes (`#B45309`, `#0667B0`, `#6E6459`) are legal
   today. The ten failures are the **mirror case**: `#7A7067` and `#106F9F` were token values in the deleted
   research document and are absent from the shipped theme, and `#C5A558` and `#FDF7EF` appear only in the
   gitignored `docs/design-research/_scratch/analysis/tokens/tokens.json`, never in a theme block. So: real
   prototype debt, an open register entry whose stated reproducer no longer reproduces, root `README.md:306` still
   calling KI-58 an open defect, `gate.py:6` still citing the absent `CONSTRAINTS.md` as its rule source, and
   the silent-leg guard now passing at 92 theme hexes against its 40 threshold. `AGENTS.md` §11 marks the
   `docs/design-research/` prototypes do-not-hand-edit, so this cannot be cleared by an agent editing the
   artifacts; it needs an owner disposition (repair the prototypes, re-scope the gate, or extend the map with
   a stated reason). Nothing in this proposal causes it and nothing in it claims the gate passes.

## 9. Owner rulings, answered 2026-10-09

The ten questions this artifact raised were answered the same day. Each row is the ruling plus the one line
of evidence it rests on. §§1-8 above are the analysis that produced them and are not edited here.

| # | Ruling | Evidence it rests on |
|---|---|---|
| **1** | **1c, split by surface class.** `ADR-0020` §2 regulates the advisor's numbers; `config/reference.php:30-33` regulates the reference views; neither reaches the other. Both get written down in `DESIGN.md` prose. Zero code. | `ADR-0020` §2:73-74 scopes the vocabulary "for the advisor's numbers"; the config block defines its own four mappings and reserves `calculated` at `:33` |
| **2** | **2a, remove all four absence chips** (`TrainingCard.vue:226`/`:323`/`:365`, `ScenarioPanel.vue:246`), and update `grand-concert-panel.spec.ts:83` with the ruling named as the reason. **This is policy, not enforcement.** | `DESIGN.md` §4.2 and D-220 fix how an absence renders; nothing in the chain ever said a chip may not accompany one |
| **3** | **3b, per-surface meaning.** The reference views keep "the doc marks the value unverified"; absences retire the word. The one config `unknown` at `reference.php:105` stays. | Coheres with 2; the two emissions were never the same claim (`reference.php:32` vs `ProvenanceBadge.vue:26-27`) |
| **4** | **4b, ratify sense (b).** `Estimated` means weakly sourced. `ADR-0001` §3 gets a narrow appended note that sense (a) is unbuilt; its substance is not rewritten and its text is not deleted. | Sense (a) has no legal object: `design-2.0` §49's worked example is a win chance, and `PRD.md` §6.11 forbids computing one |
| **5** | **5a, P1 inline.** The constant's source, read date and stale flag print visibly under the figure; the one-line cost on the densest card is accepted. `ScenarioPanel.vue:205-208` and `Dashboard.vue:300-301` are cited as the shipped precedent in the commit message. | `ADR-0001` §2:46 says **visible**; §7:101 says every suggestion **displays** the date. A `title` is mouse-only, so it fails both on keyboard and touch |
| **6** | **6a, Option A + A1.** Repoint the two labels to `Mayano Top Gun` and `Daiwa Scarlet`; re-target the ~19 `name=` locators at ids and labels; `global-setup.ts:64` opts the seeder in. Test-only, real rows, no `FR-G-1` collision. → **Partly superseded before it could be executed**, by `c29a71b` (13:44, another session): the A1 locator work landed as written, and the label half was removed instead of repointed — the fixture now leaves the parent pick **unchosen**, because `legacy_id` is nullable and the scratch DB holds no Veterans, with the picker's own contract covered by `ancestry-node-picker.spec.ts`. **Consequence: the seeder opt-in must not be added.** Seeding seven Veterans would break the empty-`veterans` condition plan §4.1 item 7 requires, and no spec needs them any more. | Both blockers were independent; the peer deleted one by removing the dependency rather than satisfying it |
| **7** | **7a, O1 prose only.** The two markers go; the prose must name the kind of absence (D-220). The panel's other half, `:82-97`'s three absence idioms through one `<dl>`, is **explicitly out of scope** and recorded as future cleanup. | `DESIGN.md:571`: a caller printing its reason as visible prose "needs no component" |
| **8** | **8a, C1.** "You have not recorded a rank for this parent." | `AncestryNode.vue:100` already says it in that voice for the same field; `AncestryGraph.php:160` shows the rank is this career's own payload |
| **9** | **9b, one line.** `SaveVeteran.vue:246` moves to `N/A` + reason. The wider sweep was not the ruling; see §14. | `AGENTS.md` §5: an unrecorded value renders `N/A` with a `title`, never a default |
| **10** | **10a with an explicit `AGENTS.md` §11 dispensation.** Repair the three tracked prototypes, hex to token. **10b re-scope is refused**: the gate is correctly reporting. Bookkeeping ships as a separate package (§13). | `git ls-files` proves the three files tracked and clean; the four hexes are absent from `resources/css/app.css` |

**Correction to §2, owed by this pass.** §2 called the four-chip removal "enforcement of a settled ruling
rather than new policy". The ruling is **policy**: the settled text governs how an absence *renders* and is
silent on whether a badge may sit beside it. The analysis in §2 remains useful; the label on it was wrong.

## 10. Convention: how the two records change

1. **`ADR-0001` §3 closes by appended dated note, never by rewrite** — `AGENTS.md` §11 requires a dated
   erratum that preserves the original sentence. The note says: sense (a), the opt-in modelled figure, is
   unbuilt; sense (b), a weakly sourced figure, is what ships; §6.11 leaves no legal target for (a).
2. **KI-58 closes by a dated closure line under its own entry**, naming `7d8b62a` as the fix, and the mirror
   case is filed as a **new** entry continuing the numbering. No existing entry is renumbered, and KI-81
   stays reserved for the picker defect (§7).

## 11. The four locations that carry the vocabulary question

Closing one without the others leaves a live contradiction, so all four move together: `DESIGN.md` §5 (the
definitions themselves), `SCREEN_SPEC.md` §7 item 18 (the defect record), `PRODUCT.md`'s "Open" list at
`:207-214` (which already says "Which provenance vocabulary the product uses is unsettled"), and this
artifact's §1. A fifth, smaller fix travels with them: **`PRODUCT.md:302` cites
`RenderedCopyHygieneTest` as enforcing `DESIGN.md`'s absence rule**, which is the exact error
`DESIGN.md` §10 corrected on 2026-10-09 — that test checks dashes, the `N/A`-instead-of-a-glyph rule and its
own scan coverage, and never contains the word `Unknown`.

## 12. Ruled text, held for paste (do not paste while the files are hot)

*`DESIGN.md` §5, replacing the "no written definition" state:*

> **The four labels carry two axes, and the axis is set by surface class.** On the Trainer Advisor's surfaces
> the state describes the relationship between the figure and its inputs: `Confirmed` is a reading taken as-is
> from a named owner, `Calculated` is this tool's own arithmetic over readings. On the Database reference
> views the state describes how well the row is sourced, exactly as `config/reference.php:30-33` defines it,
> and `calculated` stays reserved there. Neither reading governs the other's surfaces. **The type is shared
> where the meaning is not:** `ProvenanceState` is one union in `ProvenanceBadge.vue:12`, re-declared locally
> in `pages/Database/{Events,ShopItems,Sparks}.vue`, because a consuming SFC cannot name an imported type in
> `defineProps<>`. A fifth label is therefore a four-file change even when it applies to one surface class,
> and no runtime check maps config strings onto the union.

*`SCREEN_SPEC.md` §7 item 18, appended dated note (original text preserved):*

> *Ruled 2026-10-09 (owner). The axis is per surface class, written into `DESIGN.md` §5: derivation on the
> advisor's numbers, source strength on the reference views. The four absence chips were removed as policy,
> not as enforcement of a settled rule, and the `Unknown` beside an absence is retired. The entry stays open
> as the record of the contradiction it documents and closes with the commit that lands those edits.*

*`ADR-0001` §3, appended dated note; `PRODUCT.md` Open-section close-out; both follow the same shape:
quote the ruling, name the date, leave the original sentences standing.*

## 13. 10a bookkeeping text, pending peer-sync paste

1. **KI-58 closure line:** "*Closed 2026-10-09. `7d8b62a` (2026-10-08) implemented fix option 1 —
   `load_token_hexes()` now reads `resources/css/app.css` and both theme blocks — and the entry's three
   reproducer hexes (`#B45309`, `#0667B0`, `#6E6459`) are legal today. What remains is the mirror case,
   filed separately; the register text above is preserved as written.*"
2. **New mirror-case entry**, naming the four hexes and the three tracked prototypes: `#7A7067` and
   `#106F9F` were token values in the deleted `docs/design-research/DESIGN.md` at `e0e043c`; `#C5A558` and
   `#FDF7EF` appear only in the gitignored `docs/design-research/_scratch/analysis/tokens/tokens.json` and
   never in a theme. Ten findings across `screen-a-scenario-v10.html`, `screen-c-event-v1-inline.html` and
   `screen-c-event-v2-preview-column.html`; reproduction command `python tools/gate.py`; closure is the
   hex-to-token repair under the owner's §11 dispensation, and closure does not re-scope the gate.
3. **`tools/gate.py:6`** cites `docs/design-research/CONSTRAINTS.md` as its rule source; that file no longer
   exists, and the rules live in the `docs/research-scratch/` masters.
4. **Root `README.md:306`** still calls KI-58 an open defect affecting rendered-prototype checks; after item 1 it
   must point at the new mirror-case entry instead.

## 14. Ruling 9b, classified

Ruling 9b reached one line: `SaveVeteran.vue:246` now renders `N/A` with "You have not recorded a parent for
this slot." as its reason. The wider set is classified here rather than swept, because in most of these sites
the substituted words *are* the reason and replacing them with `N/A` would delete information. **Every path
in this table is relative to `resources/js/`.**

| Group | Sites | Status |
|---|---|---|
| Done | `SaveVeteran.vue:246` | landed in the working tree |
| **A** — unrecorded value in a value cell, needs `N/A` + reason | `scenario/TeamRacePanel.vue:44`, `pages/Catalog/Show.vue:163`, `pages/Runs/Show.vue:267` | **held for owner scoping.** `Runs/Show.vue` additionally carries ~45 uncommitted peer lines and is blocked this turn regardless |
| **A′** — unresolvable reference, not an unrecorded value | `pages/Runs/Import.vue:106` (`'Unknown trainee'`: a stored id that resolves to no row) | **held.** Its honest sentence is not an absence; needs its own wording |
| **C** — sentence objects; a wording decision, not a substitution | `career/RecommendationCard.vue:64`, `components/DeckPanel.vue:138`, `pages/SupportCards/Show.vue:149`, `components/GradePointMeter.vue:54`, `pages/Career/TraineeSelect.vue:119`, `scenario/TeamRacePanel.vue:42`, `pages/Runs/Show.vue:225` | **held.** A mechanical `?? 'N/A'` on `'No action to rank'` prints a value marker where the sentence was the explanation |
| Not in scope | the remaining `??` sites: already an `N/A` form, or non-display defaults (CSS classes, form initial values, a `:key`, four `:placeholder` attributes at `Runs/Show.vue:606/612/619/623`, the error-message default at `SkillsPlanner.vue:272`) | **leave** |

## 15. Coordination escalation, 2026-10-09

Two sessions are implementing rulings 2a/3b/5a/7a/8a/9b in this one working tree. Evidence, all measured
rather than inferred: `tests/browser/grand-concert-panel.spec.ts` is modified in the tree and carries the
2a-shaped assertion and a comment citing the owner's 2026-10-09 ruling, while HEAD still asserts the chip is
visible; `database/browser-scratch.sqlite` and four `test-results/career-inheritance-event-*` directories
were written within seconds of a check, so a browser run is live; and six commits landed on master during
this review (`291f6ea`, `033c974`, `a4b2b26`, `fdebcc9`, `be02297`, `dd6dd22`), two of them while the run was
in flight. There is no channel between the sessions.

The consequence for the shared tree: the peer's live run is executing against this session's uncommitted
component edits, so neither session's browser numbers are attributable, and either session can overwrite the
other's file on its next write. The component half of 2a and the spec half are a matched pair; committing one
without the other puts red on master, and a partial-staging workaround in a tree that commits hourly is the
mechanism that has already dropped hunks here twice.

**The question for the owner, unanswered as of this writing: which session owns which of rulings
2a/3b/5a/7a/8a/9b, and which session stands down?** Until it is answered, this session holds its four
component files uncommitted, writes nothing to `DESIGN.md`, `SCREEN_SPEC.md`, `PRODUCT.md`, `README.md`,
`KNOWN-ISSUES.md` or `docs/adr/`, starts no browser run, and takes neither 6a nor 10a's file edits.

## 16. Held state, stated plainly

* **Uncommitted, intact, verified this pass:** `career/TrainingCard.vue` (chips at `:226`/`:323`/`:365`
  gone; `costConstants` defined at `:107`, printed at `:252`), `scenario/ScenarioPanel.vue` (chip gone),
  `career/CareerStatePanel.vue` (markers gone, `statAbsence` at `:50`, printed at `:87`),
  `pages/Career/SaveVeteran.vue` (8a and 9b).
* **Verified only by typecheck and reading:** `npm run typecheck` exits 0, and `state="unknown"` now appears
  zero times in `resources/js`. **No browser or Pest run was executed for these edits**, because a peer run
  holds the harness. Nothing here is runtime-verified, and no green claim is made for it.
* **Not run, and not substitutable:** `php artisan migrate:status` on the dev file is an owner action. A
  scratch identity answers the wrong question, because D3 added
  `2026_10_09_120000_add_ownership_to_deck_slots_table.php` and KI-60's failure mode is a committed
  migration that never ran against `database/database.sqlite`. **Until it is run, no session's "the suite is
  green" statement is evidence about the application** — 1,326 tests passed once with four migrations
  pending and the landing page returning 500.

## 17. Executed 2026-10-09, this pass

| Ruling | Landed in the tree | Evidence |
|---|---|---|
| 2a, 3b | `TrainingCard.vue` (three chips), `ScenarioPanel.vue` (the fourth) | `state="unknown"` now appears **zero** times in `resources/js`; `npm run typecheck` exit 0 |
| 5a | `TrainingCard.vue` `costConstants`, defined at `:107`, printed at `:252` | a visible `text-xs` line under the figure; the `title` stays, so the tooltip assertions this card already had still hold |
| 7a | `CareerStatePanel.vue` `statAbsence` at `:50`, printed at `:87` | markers gone; the both-absent row now names **both** gaps, which the old ternary did not |
| 8a, 9b | `SaveVeteran.vue:246`/`:248`, `TeamRacePanel.vue:44`, `Catalog/Show.vue:163` | group A's first two sites landed; `Runs/Show.vue:267` held (peer lines in the file); A′ and C held for wording |
| 4b | `docs/adr/0001` §3 | dated note appended; §3's bullets untouched |
| 1c | `DESIGN.md` §5 | per-surface axes, the shared-`ProvenanceState` caveat, and the absent runtime guard, all written down |
| 10a | `tools/gate.py:6` and the three prototypes | **`GATE PASS: 3 prototype(s), all machine-checkable gates green`** (`.scratch-uma/gate-after-10a.txt`); ten replacements with per-file counts proven (2 + 4 + 4); `#106F9F`→`--color-sp-ink` is evidenced by the artefacts' own dark half `#4FC3F7`, which equals the shipped dark token |
| register | KI-58 closed with its text intact; **KI-83** filed for the mirror case | KI-82 was taken by a peer commit at 13:53, so the numbering continued past it; KI-81 stays reserved |
| 15 | `SCREEN_SPEC.md` §7-18 | dated ruling note appended, entry text preserved |

**Two things this pass could not do, and both are mechanical, not cautious.**

1. **`PRODUCT.md` was not edited.** `AGENTS.md:381` lists it as generator-owned with "the generator wins",
   so hand-editing the `:302` stale `RenderedCopyHygieneTest` citation and the `:207-214` open record would
   be overwritten at the next generation rather than fixed. Both texts are drafted in §11 and §12 and need to
   enter through the generator's own source, or by your hand. That makes the fourth location §11 named the
   one that an agent cannot write in.
2. **`DESIGN.md`, `SCREEN_SPEC.md` and `KNOWN-ISSUES.md` were written but not committed.** Each carries
   uncommitted hunks from other passes, and the only non-interactive routes are a whole-file pathspec commit
   — which would land another session's work under this message — or `git apply --cached`, which you revoked
   on 2026-10-09 after it dropped hunks twice here. `git add -p` is interactive and unavailable to this tool.
   So those three stay modified-in-tree for a session that can commit them wholesale or split them by hand.

**Browser gate not run, deliberately.** KI-80 (open, another session) records that the suite's scratch
database is one shared repo path, so a second session's `globalSetup` **deletes** the first one's data
mid-run; a peer run was live minutes before this line was written. The Vue edits therefore rest on typecheck,
the gate pass, and reading the markup — not on Chromium, and no runtime claim is made for them.
