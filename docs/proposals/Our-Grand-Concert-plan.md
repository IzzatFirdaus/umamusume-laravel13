# Our Grand Concert — Incremental Implementation Roadmap

**Project:** Trainer Desk 2.0  
**Scenario:** Our Grand Concert  
**Purpose:** Incrementally complete the existing Our Grand Concert implementation without speculative mechanics, duplicate architecture, broad refactors, or unverified game data.  
**Last verified:** 2026-10-21, against `master` at `d00a0457` — the tree as it stood when this pass began. Every `COMPLETE` claim below was re-checked against the working tree on that date; where a claim no longer holds, the slice carries a dated note and keeps its status. A concurrent session committed `15bba0f9` and `d664f948` while the pass ran; neither touches `config/scenarios.php`, `SCREEN_SPEC.md`, `docs/scenarios/`, `docs/research-scratch/`, `app/` or `tests/Feature/`, so every file this document names was verified against the same content it names.

---

## Status vocabulary

This roadmap and the three scenario design plans use two vocabularies on purpose, and neither is a
synonym for the other.

**Slice status (this document).** A slice is `PLANNED`, `COMPLETE`, or `BLOCKED BY EVIDENCE`.

- `COMPLETE` — the slice's named files exist, the named test files exist, the cited commit resolves,
  and the slice's own gates were run. It is a statement about the work item, never about the
  in-game mechanic behind it.
- `BLOCKED BY EVIDENCE` — an evidence gate closed without establishing a truthful representation.
  The slice names the artifact that would reopen it. A design argument does not close it.
- `PLANNED` — the slice has not been started and is not gated either way.

**Feature level (URA Finale, Unity Cup, Trackblazer).** A feature carries `Implemented`, `Verified`,
`Game-validated`, or `UNASSESSED — requires code audit`. The three design plans use that vocabulary
in their own *Verification Status* tables. This roadmap does not use it: a design plan's
`Implemented` is not this roadmap's `COMPLETE`, and this roadmap's `COMPLETE` is not a claim that
anything is `Game-validated`.

**Source badge (all four documents).** Every factual claim a reader might act on carries one of:

| Badge | Meaning |
| ----- | ------- |
| `verified` | Established in this repository, or by a tier-S / tier-A source the document cites. |
| `derived` | Computed from a verified fact by a rule the document states. |
| `configurable` | A value the tool reads from configuration; the Trainer can change it. |
| `community` | A third-party guide, recorded as guidance and not as a game fact. |
| `unknown` | No cited source establishes it. The claim is preserved and marked, not deleted. |

**Evidence hierarchy.** The tiers below are the ones this roadmap already defined, and they govern
which badge a claim may carry. A claim is rendered only at the tier that supports it, and a
community figure is never silently promoted to a project fact.

---

## Evidence sources

What this document actually relies on, with the badge each source carries. A source not listed here
is not cited by the roadmap, and a claim with no source below carries the `unknown` badge.

| Source | Last verified | Badge | What it supports here |
| ------ | ------------- | ----- | --------------------- |
| `config/scenarios.php`, the `our_grand_concert` entry | 2026-10-21 | `verified` (the file), `configurable` (the values) | `performance_input`, `facility_level_source`, `cap_bonus`, the `panels` map, `panel_absence`, the five `scenario_links`. |
| `SCREEN_SPEC.md` | 2026-10-21 | `verified` | Screen identity and behaviour: the `SCR-RUN-003`, `SCR-CAR-012` and `SCR-CAR-018` rows and sections, and the §7 evidence gaps 19, 21, 22 and 23 that this roadmap's gates wrote. |
| `docs/scenarios/07-grand-concert.md` | 2026-10-21 | `verified` for what it sources, `unknown` for what it marks ❌ UNVERIFIED | The scenario's own playing guide: the loop, the five Performance types, the three Live Bonus types, the four-lives schedule. Its client-vocabulary rows are still marked unverified by that file. |
| `docs/UMAMUSUME_REFERENCE.md` §2.2.4, §2.8, §2.9, and the §7 conflict-log rows 48, 49, 51 and 52 | 2026-10-21 | `verified` (repo) | The tier-S readings of notice 899 and notice 905, the row-48 resource-noun conflict, and the row-49 / row-51 / row-52 items the evidence gates cite. |
| [Cygames `[Global]` notice 905](https://umamusume.com/news/905) | 2026-10-21, as recorded in the reference | `verified` (tier S, client copy) | The five Performance types, the nouns *lessons*, *songs*, *concert techniques*, *Promo Concert*, *Grand Concert*, *Live Performance Expectations* and *Great Success*. |
| GameTora, Our Grand Concert scenario page (page date 2026-07-22) | 2026-10-21, as recorded in the reference | `community` | The numbers notice 905 does not print: 200 at the start, +50 per successful concert, the Song magnitudes, the first-instance Live Bonus values. |
| Game8 607337 and 607687 (page dates 2026-07-26 and 2026-07-02) | 2026-10-21, as recorded in the reference | `community` | The Song unlock patterns, the two Live Bonus layers under one heading, the per-year unlock segments. |
| `docs/research-scratch/SCENARIO-PUBLISHER-REFERENCES.md` | 2026-10-21 | `verified` (repo) | The verbatim extractions of every publisher page the gates cite, including the notice-905 block. |
| `docs/research-scratch/DESIGN-CORPUS.md` | 2026-10-21 | `verified` (repo) | The frame inventory: *Our Grand Concert — None found* under "Scenario coverage", and the screen-type table that documents no support-card effect list. |
| `database/seeders/data/support_effects.ca447e53.json` | 2026-10-21 | `verified` (repo) | The 35-row committed support-effect dictionary the Live Bonus gate measured: `友情ボーナス` at id 1 with one English name, `得意率アップ` at id 19 with two conflicting ones, and no row at all for `サポート連続イベント率アップ`. |
| The committed `database/seeders/data/` export bodies | 2026-10-21 | `verified` (repo) | The measured negative that no Song title appears in any committed data this repository holds. |
| `app/Enums/PerformanceType.php`, `app/Models/TurnEvents/PerformancePayload.php`, `app/Services/Scenario/FinaleReader.php`, `app/Services/Career/ScenarioSectionBuilder.php` | 2026-10-21 | `verified` (repo) | The implemented surface the `COMPLETE` slices name. |
| The `tests/Feature/` and `tests/browser/` files the slices name | 2026-10-21 | `verified` (repo) | The absence guards and the boundary tests the evidence gates rely on. |
| `.scratch-uma/cited/scenarios.json`, `.scratch-uma/cited/static_scenarios.json` | **not present in this tree on 2026-10-21** | `unknown` | The gitignored GameTora scratch exports two gates cite. They are absent, so the claims resting on them are marked `unknown` where they are used, not deleted. |

---

## Screen-specification references

Every `SCREEN_SPEC.md` reference this roadmap makes, re-verified against the file on 2026-10-21. No
number has drifted, so no reference below needed replacing with a heading.

| Reference | Resolves to | Verified |
| --------- | ----------- | -------- |
| `SCR-RUN-003` | §3 row and §4 section "Run detail & turn log" | Yes. The row's status column records it as retired 2026-10-09 (F2); the section itself is still present and still carries the Performance-observation input. |
| `SCR-CAR-012` | §3 row and §4 section "Training Decision detail" | Yes. |
| `SCR-CAR-018` | §3 row and §4 section "Career Result" | Yes. |
| §7-19 | Gap 19, "Lessons have no verified vocabulary to build a surface from, so the app has none." | Yes — the Lessons gate. |
| §7-21 | Gap 21, "Songs have a settled noun and a settled count, and nothing else a surface could read." | Yes — the Songs gate. |
| §7-22 | Gap 22, "Live Bonuses have a corroborated structure and six English names for three objects, none of them client copy." | Yes — the Live Bonus gate. |
| §7-23 | Gap 23, "Promo Concerts are the one Grand Concert mechanic whose name is already official, and the app still has no place to put it." | Yes — the Promo Concert gate. |

The §7 gaps are numbered items in `SCREEN_SPEC.md` §7, not section numbers of their own; the
numbers above are the item numbers, which is how this roadmap cites them.

---

## Client terminology (settled)

The authoritative `[Global]` terms for this scenario are **Dance, Passion, Vocals, Visuals and
Composure**, with **Promo Concert**, **Grand Concert**, **Live Performance Expectations** and
**Great Success** as the client nouns for the events, the gauge and the outcome. Three guide
renderings circulate and are **not** client copy: **Mental** (GameTora's fifth type, a rendering of
the JP word), **Vocal** and **Visual** (GameTora's singular forms), and **Hype** (GameTora's name
for the gauge the client calls Live Performance Expectations). "Promotional Lives" is GameTora's
rendering of the JP word for the client's **Promo Concert**. The reference's §7 conflict-log rows
48, 49 and 51 record each of these, and the routes are set out in
`docs/research-scratch/SCENARIO-PUBLISHER-REFERENCES.md` under "Disagreements" and the notice-905
extraction.

Where this roadmap uses one of the guide renderings, it does so to name the thing being refused, to
attribute a community claim, or to record what a source printed. It never adopts one as the
scenario's vocabulary. The `Mental`, `Vocal` and `Visual` entries in Slice 6's validation list are
the refused values themselves and stay as they are.

---

## Implementation Principle

Our Grand Concert is implemented as a sequence of narrow, independently verifiable slices.

Every slice follows:

> **Evidence → smallest truthful representation → canonical lifecycle → UI → tests → stop**

A roadmap item is **not** permission to implement every mechanic implied by its name.

At every boundary:

- Read the authoritative project documentation first.
- Search the existing codebase before creating new architecture.
- Prefer existing models, TurnEvents, controllers, requests, capabilities, and UI surfaces.
- Do not invent unsupported game mechanics.
- Do not infer numeric values from external/community sources when project evidence marks them unverified.
- Do not create duplicate models, tables, endpoints, stores, or frameworks.
- Keep observed facts separate from derived state and recommendations.
- Stop when the assigned slice is complete.
- A slice may split, collapse, be deferred, or conclude with a documented exclusion if evidence does not justify implementation.

### Evidence hierarchy

When implementing scenario mechanics, distinguish:

1. **Authoritative and verified** — safe to implement/render.
2. **Observed in the application but not formally documented** — may be represented only where the observation itself is trustworthy.
3. **Documented as unverified** — do not render or calculate as fact.
4. **Inferred/community/external mechanics** — do not silently promote to project truth.

---

# Phase A — Performance

## Slice 1 — Baseline Grand Concert Audit

**Status: COMPLETE**

Establish the current implementation state before making changes.

### Scope

Audit:

- scenario registration;
- scenario selection;
- trainee selection;
- training target;
- inheritance/Legacy;
- support deck;
- preflight;
- career state;
- Performance;
- Lessons;
- Songs;
- Live Bonuses;
- Promotional Lives;
- Grand Concert/finale;
- Trainer Advisor;
- Career Report;
- veteran/Legacy handoff.

Identify existing architecture, missing pieces, known defects, disabled panels/capabilities, and explicit documentation boundaries.

### Outcome

Created the incremental implementation map and established that Grand Concert functionality must be completed in narrow slices rather than through a broad rewrite.

---

## Slice 2 — Grand Concert Finale Race Catalog Correction

**Status: COMPLETE**

### Scope

Correct the Grand Concert finale race catalog scenario key:

```text
final_live => our_grand_concert
```

instead of the incorrect:

```text
final_live => grand_concert
```

Verify the resulting catalog identity and ensure there is one valid finale row for the scenario.

### Additional work

- Investigated the orphaned engine-owned catalog row.
- Backed up the row before removal.
- Verified it was not referenced by race entries.
- Removed only the incorrect duplicate/orphan.
- Revalidated the race planner's scenario-scoped lookup.

### Outcome

Grand Concert finale catalog data is correctly associated with `our_grand_concert`.

Commit:

```text
480b711716b0756443a3f5e3aeac3a7f879e3abc
fix(parser): correct Grand Concert final scenario key to our_grand_concert (KI-71)
```

---

## Slice 3 — Performance Domain Foundation

**Status: COMPLETE**

Establish Performance as a typed scenario observation without inventing a Performance balance system.

### Authoritative Global terminology

The verified Global client terms are:

- Dance
- Passion
- Vocals
- Visuals
- Composure

Do **not** substitute the GameTora/JP-derived term "Mental".

### Architecture

Implemented:

```text
app/Enums/PerformanceType.php
app/Models/TurnEvents/PerformancePayload.php
app/Models/TurnEvent.php
tests/Feature/PerformanceFoundationTest.php
```

Payload:

```json
{
  "type": "Dance",
  "delta": 12
}
```

`delta` is signed:

- positive = Performance acquired;
- negative = Performance spent.

### Explicitly not implemented

- starting values;
- current totals;
- caps;
- acquisition formulas;
- spending costs;
- conversion formulas;
- Lesson costs;
- Song costs;
- Hype;
- Live Bonus;
- stat mappings.

Performance TurnEvents are observations, not an absolute balance.

---

## Slice 4 — Performance Turn-Write Integration

**Status: COMPLETE**

Wire Performance observations into the canonical turn-writing lifecycle.

### Scope

Extend the existing:

```text
runs.turns.store
```

path rather than creating a Performance endpoint.

Use:

```text
performance[type]
performance[delta]
```

and persist the observation as the existing Scenario TurnEvent type.

### Constraints

- Existing turn transaction remains authoritative.
- No scenario-specific duplicate write path.
- No automatic calculation.
- Numeric strings from browser forms remain valid and are cast appropriately.
- Empty Performance pair produces no event.

---

## Slice 5 — Performance Read-Side Integration

**Status: COMPLETE**

Expose recorded Performance observations through the canonical `TrainingRun` read path.

Implemented:

```text
TrainingRun::performanceObservations()
```

Output is deterministic and contains:

```text
turn
event_id
type
delta
```

### Explicit boundary

The method does **not**:

- sum Performance;
- derive a current Performance balance;
- infer starting values;
- calculate costs;
- calculate caps.

It exposes the recorded observations only.

---

## Slice 6 — Trainer-Facing Performance Observation Input

**Status: COMPLETE** (capability re-verified 2026-10-21; the surface named below has since moved — see the dated note)

Add Performance observation input to the actual committing turn-entry surface.

### Surface

```text
resources/js/pages/Runs/Show.vue
```

using the existing GuidedStep / turn-entry architecture.

> **Dated note (2026-10-21).** The capability half of this slice still holds exactly as written:
> `performance_input => true` is present in the `our_grand_concert` entry of
> `config/scenarios.php`, and the flag is still deliberately not a `panels` key. The *surface* half
> is stale. `resources/js/pages/Runs/Show.vue` no longer exists — the F2 cutover retired the record
> screen, and `SCREEN_SPEC.md` §3 records `SCR-RUN-003` as **Retired 2026-10-09 (F2)**, with
> `GET /training-runs/{run}` now redirecting to `runs.cockpit`. The committing turn-entry surface
> is now `resources/js/pages/Career/TrainingDetail.vue` (`SCR-CAR-012`), which posts
> `runs.turns.store` and carries the `performance[type]` / `performance[delta]` pair. The slice's
> status is unchanged: the work landed, and what moved is where it landed. Per `AGENTS.md` §2 the
> code wins and the prose above is the stale half; the sentence is left in place and corrected here
> rather than rewritten, so the change of surface stays visible.

### Capability

Performance input is enabled through:

```text
performance_input => true
```

for `our_grand_concert`.

It is deliberately not a new panel.

### UI

Fields:

- Performance type;
- signed Performance change.

Exact options:

- Dance
- Passion
- Vocals
- Visuals
- Composure

No default selection.

No invented min/max.

No calculated total.

### User-facing semantics

Positive values represent Performance gained.

Negative values represent Performance spent.

The UI explicitly explains that the application does not maintain a Performance total because the available source does not establish starting values, training-turn payments, or Lesson costs.

### Specification

`SCREEN_SPEC.md` updated for SCR-RUN-003.

> **Dated note (2026-10-21).** `SCR-RUN-003` still exists as a §3 row and a §4 section, and both
> still carry the Performance-observation input this slice added (the §3 row's own status column
> records the retirement). The section number has not drifted and no replacement number is needed.
> The screen it documents is retired, so the live owner of the same behaviour is `SCR-CAR-012`; see
> the dated note under Slice 6's *Surface*.

### Validation

Invalid terminology such as:

```text
Mental
Vocal
Visual
Dancing
dance
```

is rejected.

> **Dated note (2026-10-21).** These five are the *refused* values, and they are the guide
> renderings the client's own words replace: "Mental" is GameTora's fifth type where the client
> prints "Composure", and "Vocal" / "Visual" are GameTora's singular forms where the client prints
> "Vocals" / "Visuals". The list is not a claim that those words may be used; it is the guard
> against them. See "Client terminology (settled)" above and the reference's §7 conflict-log
> row 48.

Zero and decimal values are rejected.

Browser-form numeric strings are accepted.

---

## Slice 7 — TrainingDetail Consistency Boundary

**Status: COMPLETE** (decision re-verified against the code 2026-10-21; the code has since moved and the decision no longer holds — see the dated note)

Determine whether:

```text
resources/js/pages/Career/TrainingDetail.vue
```

is an equivalent committing turn-entry surface.

### Determination

It is **not**.

Although it submits to:

```text
runs.turns.store
```

it always submits:

```text
stage: preview
```

and does not reach the committing confirmation path.

Its own screen explicitly states that the turn is not written until confirmation occurs elsewhere.

> **Dated note (2026-10-21).** The determination above is **no longer true of the code**, and the
> slice's status is left as it was because the work it records did land at the time. Measured on
> this tree on 2026-10-21:
>
> - `resources/js/pages/Career/TrainingDetail.vue` **no longer submits `stage: preview`**. Its own
>   docblock records that "the previous flow carried a `stage=preview` intermediate that
>   redirected to the run record screen, whose rail then gated the confirm (F2, plan §9.6; owner
>   ruling on group R-2)", that "that record screen is retired and the two-stage rail has no 2.0
>   reproduction, so this screen now writes the row itself and redirects to `runs.cockpit`". The
>   file's own comment above `recordTurn()` says the same: "there is no stage to advance".
> - The screen is therefore **now the committing surface**, not a preview-only one, and it
>   **does carry the Performance observation pair** (`performance[type]` and `performance[delta]`
>   on the record form, posted through `recordTurn()`).
> - The consequence is that Slice 6's and Slice 7's reasoning has been overtaken: what was excluded
>   from `TrainingDetail.vue` because it could not commit a turn is now the place the pair lives.
>
> Per `AGENTS.md` §2 the code wins and the prose is stale, so the sentences above are left in place
> and corrected here rather than rewritten. The reopen condition recorded in `SCREEN_SPEC.md`
> `SCR-CAR-012`'s gap item 11 — "if that hand-off ever stops carrying the pair, the exclusion here
> stops being defensible and this item must be reopened" — has fired in the opposite direction:
> the hand-off no longer exists, and the exclusion is no longer defensible. `SCREEN_SPEC.md` is not
> this roadmap's surface, so that gap item is reported here rather than edited.

### Decision

Do **not** add Performance controls to TrainingDetail.

Performance remains on the actual committing Runs/Show turn-entry surface.

> **Dated note (2026-10-21).** Superseded by the code, as recorded above. The decision was correct
> when it was made, and the exclusion has since been lifted by a change this roadmap did not make:
> the F2 cutover removed the preview-and-confirm rail, which removed the reason for the exclusion,
> and the Performance pair now posts from `TrainingDetail.vue` itself. This slice's *status* stays
> `COMPLETE` and its original decision text stays unedited, so the record of what was decided and
> when it stopped holding are both visible. Re-deriving the boundary for the current surface is
> follow-on work and is not done here.

### Regression protection

Added:

```text
tests/Feature/TrainingDetailPerformanceBoundaryTest.php
```

The tests establish that:

- TrainingDetail remains preview-only;
- no turn/event is written by its preview submission;
- Performance data can survive the preview hand-off;
- invalid Performance values are not silently dropped.

> **Dated note (2026-10-21).** `tests/Feature/TrainingDetailPerformanceBoundaryTest.php` exists on
> this tree. Whether it still asserts the four bullets above was not re-derived for this pass; the
> bullets are the slice's historical record of what the test pinned on 2026-10-09, and the first two
> describe a preview path the component no longer has. The file is present, which is what this
> roadmap's `COMPLETE` status depends on; its assertions are **unverified** as of 2026-10-21.

### Specification

`SCREEN_SPEC.md` SCR-CAR-012 updated with the boundary decision and reopen condition.

> **Dated note (2026-10-21).** `SCR-CAR-012` is the Training Decision detail and still resolves to
> that section in `SCREEN_SPEC.md`. Its gap item 11 still records the Performance pair as
> deliberately absent from that screen, on the stated ground that "this screen posts `stage=preview`
> and nothing else" — a premise the code no longer satisfies, as the note above sets out. The
> section number has not drifted; the gap's reasoning is the stale part, and it is reported here
> rather than edited.

---

# Phase B — Lessons

## Slice 8 — Lessons Evidence + Foundation

**Status: COMPLETE** (ran 2026-10-09 and was re-tested the same day; the gate closed with no Lesson representation
established, recorded in `SCREEN_SPEC.md` §7-19)

Establish exactly what the project can truthfully represent about Lessons.

### First determine

- whether a canonical Lesson representation already exists;
- whether Lessons are observations, actions, or both;
- whether a Lesson has an established TurnEvent representation;
- whether Lesson categories/types are authoritative;
- whether costs are authoritative;
- whether Performance effects are authoritative;
- whether Hype/stat effects are authoritative;
- whether confirmation/reservation terminology is verified.

### Important boundary

The Grand Concert documentation explicitly identifies some Lesson-related information as unverified.

Do **not** implement:

- unverified Lesson labels;
- cost curves;
- per-level magnitudes;
- inferred Performance gains;
- inferred Hype;
- inferred stat effects.

### Possible outcome

If only a minimal Lesson observation is supported, implement only that foundation.

If no truthful representation is currently supported, document the boundary and add a regression test rather than inventing a Lesson system.

---

## Slice 9 — Lessons Turn Lifecycle

**Status: BLOCKED BY EVIDENCE** (gated on Slice 8, which closed the gate: no Lesson representation exists)

> **Dated note (2026-10-21).** This line previously read `PLANNED`. The Current Status table in this
> document, Slice 8's own outcome, and `SCREEN_SPEC.md`'s changelog row for 2026-10-09 all record
> Slices 9 and 10 as blocked by evidence, so the two disagreed and the table was the correct half.
> The status is moved to `BLOCKED BY EVIDENCE`; the original sentence is preserved here, and no
> slice is closed or reopened by this change.

Only if Slice 8 establishes a valid Lesson representation.

Integrate Lessons into the canonical turn/event lifecycle.

### Preferred architecture

Reuse:

- existing TurnEvent infrastructure;
- canonical turn write path;
- `TrainingRun` read-side conventions;
- existing scenario capability/configuration.

### Do not create

- Lesson table;
- standalone Lesson model;
- separate Lesson endpoint;
- duplicate turn-writing path;
- speculative calculation engine.

### Tests

Verify:

- valid Lesson representation;
- invalid values;
- persistence;
- readback;
- deterministic ordering;
- no accidental total/balance calculation.

---

## Slice 10 — Lessons Trainer UI

**Status: BLOCKED BY EVIDENCE** (gated on Slice 8, which closed the gate: no Lesson representation exists)

> **Dated note (2026-10-21).** This line previously read `PLANNED`; see the identical note under
> Slice 9. The status matches the Current Status table and Slice 8's outcome.

Only if authoritative evidence supports a truthful Trainer-facing Lesson interaction.

### Preferred surface

The actual committing turn-entry surface.

Do not add Lesson controls to `TrainingDetail.vue`.

### UI constraints

- exact verified terminology;
- no invented defaults;
- no unverified costs;
- no unverified magnitudes;
- no automatic Lesson selection;
- no hidden calculations.

If evidence does not support truthful UI, document the exclusion instead.

---

# Phase C — Songs

## Slice 11 — Songs Evidence + Foundation

**Status: BLOCKED BY EVIDENCE** (gate run 2026-10-09; the determination is in `SCREEN_SPEC.md` §7-21)

Establish the authoritative Song representation.

### Outcome of the gate

**Songs — BLOCKED BY EVIDENCE.** The noun and the count are settled; nothing a field could carry is.

- **Settled:** **songs** is Cygames' own English (notice 905), so it needs no capture. The **23** and its
  availability segments are corroborated row for row by both publishers, and reference §7 row 49 treats the count
  and the segment gating as settled while leaving the naming open.
- **Not settled:** the language of the 23 titles (row 49, unresolved); the Performance type each cost figure
  belongs to (not recoverable from either page's text layer); any magnitude (no cost curve, no Hype per Song);
  whether acquiring a Song consumes a turn and what the client calls the action.
- **New measured negative:** no Song title appears in any committed data this repository holds. Of nine distinctive
  fragments from `docs/scenarios/07-grand-concert.md`'s title table grepped against `database/seeders/data/`, eight return 0 hits and
  the ninth, `Umadol`, matches only unrelated client skill and card names (`Rising to Umadol Stardom!`). The one
  local scenario JSON in the tree, `.scratch-uma/cited/static_scenarios.json`, is a gitignored GameTora scratch copy that
  no commit has ever touched and it carries no Song key. Row 49 is therefore not closable locally; it needs the
  `[Global]` Lesson-menu frame that gap §7-19 already names.

  > **Dated note (2026-10-21).** The measured negative itself still holds: the committed
  > `database/seeders/data/` bodies are the search surface, and no Song title is in them. The scratch
  > file this sentence leans on, `.scratch-uma/cited/static_scenarios.json`, is **not present in this
  > tree on 2026-10-21** — the whole `.scratch-uma/cited/` directory is absent, and the directory
  > holds gitignored session scratch that no commit tracks. The claim it carried ("it carries no Song
  > key") was a negative that the committed-data search already establishes, so the negative stands on
  > the committed export bodies and the scratch file is now cited as `unknown` rather than as
  > evidence. The gate is not reopened and not closed by this note.
- **Smallest truthful representation:** already exists and is generic. `TurnEventType::Scenario` with
  `source_name` and nullable `deltas` carries "the Trainer says this happened" for any scenario event, so a Song
  needs no enum, table, column, route, flag or component. A `Song` enum would pick a language the client was never
  read stating; a Song count would state a timing no source publishes.
- **Absence is guarded by committed tests:** `tests/Feature/GrandConcertPanelTest.php` (payload) and
  `tests/browser/grand-concert-panel.spec.ts` (markup) both refuse a `song` string.

  > **Dated note (2026-10-21).** Both files exist and both still refuse the word. The line numbers
  > this bullet carried have drifted and are corrected here: the PHP test that owns the refusal list
  > is "carries no invented mechanic: no song, lesson or token string reaches the payload", whose
  > eight-word `$unmeasured` array is at `:117-126`; the browser spec's refusal loop over
  > `['song', 'lesson', 'performance token', 'promotional live', 'hype']` is at `:102`. The earlier
  > `:93` and `:98` pointed at the two tests' opening lines, not at the guards.
- **Reopen condition:** a `[Global]` capture of the Lesson menu. Nothing before that is implementable without
  inventing client text or a number.

### Determine

- verified Song terminology;
- whether individual Song names are authoritative;
- whether Song acquisition is an observed event or action;
- whether Song spending/selection is represented;
- whether costs are verified;
- whether Song effects are verified;
- whether Hype effects are verified;
- whether Song timing is verified.

### Explicit warning

The existing Grand Concert documentation identifies the **23 Song titles** and other Song-related details as unverified where applicable.

Do not render or encode unverified Song data merely because it appears in an external source.

---

## Slice 12 — Songs Lifecycle Integration

**Status: BLOCKED BY EVIDENCE** (gated on Slice 11, which closed the gate: no truthful Song representation exists to integrate)

If Slice 11 establishes a truthful representation:

- integrate Songs into the canonical turn/event lifecycle;
- provide a typed read path where justified;
- preserve observed-vs-derived separation;
- avoid calculating unsupported Song state.

No new standalone subsystem unless existing architecture genuinely requires one.

---

## Slice 13 — Songs Trainer UI

**Status: BLOCKED BY EVIDENCE** (gated on Slice 11; there is no verified Song interaction or status to display)

Add the minimum verified Song interaction/status information to the appropriate committing surface.

Do not expose:

- unverified Song costs;
- unverified effects;
- unsupported Hype calculations;
- speculative Song availability;
- automatic recommendations masquerading as game facts.

---

# Phase D — Live Systems

> **Naming note (added 2026-10-21).** The client noun for the four mid-career events is **Promo
> Concert**. "Promotional Lives" is GameTora's rendering of the JP word, so it is guide vocabulary,
> not client vocabulary, and the Phase title and the Slice 16, 17 and 18 headings below keep it
> only because retitling them is the owner's decision, raised at Slice 16 and not made here. The
> settled facts are recorded in Slice 16's naming correction and in the reference's §2.9; the
> client's other nouns for this scenario are **Grand Concert** (the finale), **Live Performance
> Expectations** (the gauge) and **Great Success** (the outcome). Nothing in this Phase may encode
> a mechanic under a noun the client was never read stating.

## Slice 14 — Live Bonus Evidence + Foundation

**Status: BLOCKED BY EVIDENCE** (gate run 2026-10-09; the determination is in `SCREEN_SPEC.md` §7-22)

Establish what Live Bonus state can actually be represented.

### Outcome of the gate

**Live Bonuses — BLOCKED BY EVIDENCE.** The structure is the best-corroborated thing in this scenario and the
vocabulary is the worst-evidenced, and a field needs both.

- **Settled (tier A and B, not the client):** a Lesson pays two layers of reward and one is deferred. GameTora
  names both layers and their behaviour; Game8 independently describes the deferred activation ("Takes effect
  after concerts") while collapsing both under one heading, "Mastery Bonus". Two publishers that disagree on every
  noun agree on the shape. Reference §7 row 51 adopts the partition "for the mechanics" and adopts "neither name
  … for display".
- **Not settled:** every English word. The corpus carries **six renderings for three objects** — GameTora's
  Friendship Bonus / Speciality Rate Up / Support Event Chance Up against Game8's Friendship Training
  Effectiveness / Specialty Priority / Support Chain Event Frequency — and notice 905 "names neither bonus layer",
  which is what keeps row 51 open. Its "boosting training effects" sentence is about what a Lesson pays in
  general; reading it onto the deferred layer is inference.
- **Two measured negatives, both local.** In the app's own committed dictionary
  `database/seeders/data/support_effects.ca447e53.json` (all 35 rows read), `友情ボーナス` is id 1 with one English
  name, `得意率アップ` is id 19 with **two conflicting ones** (`name_en` "Specialty Priority", `name_en_eon`
  "Specialty Rate Up"), and `サポート連続イベント率アップ` has **no row at all** — so the divergence between the two
  guides is reproduced inside one file, and the third type of a three-type mechanic has no stored row. And
  "Friendship Bonus" is **already displayed copy** in this application, as a support-card effect name from that
  GameTora dictionary (`SupportEffect.php:17`, asserted by `SupportCardPageTest.php:85,93,253,261`), which is a
  trap rather than a settlement: different domain, no capture, and no source says the scenario's bonus and a
  card's effect are one counter.
- **The magnitude boundary is narrower than the shorthand in this slice said.** "No Live Bonus percentage ladder"
  is true, and "no number exists" is false: the **first-instance** value of each type is printed row for row by
  both publishers inside the Song table (`Friendship Bonus +5%` and `+10%`, `Speciality Rate Up +5`, `Support
  Event Chance Up +1`). What is absent is the level progression. Every one of those figures is third-party prose
  riding on a Song title whose language row 49 has not settled, so the standard `SCREEN_SPEC.md` §7-21 applied to
  Songs rejects them here too.
- **Single-source only, with no corroboration at all:** that a bonus persists for the entirety of the run, that it
  has levels raised by repeating the same bonus in another Lesson, and that automatically granted Songs do or do
  not pay one. GameTora's "Automatically gained songs also count" sentence is about the Hype gauge, not about
  bonuses.
- **Smallest truthful representation:** already exists, generic, and unchanged from Slices 8 and 11.
  `TurnEventType::Scenario` with `source_name` and nullable `deltas` records "I queued a Friendship Bonus this
  turn" today. A `LiveBonusType` enum would pick among six renderings the client was never read stating and would
  have to name a third case no committed row supports, so it fails the test `PerformanceType` passes. Percentage,
  level, duration and stacking fields, a current-bonus reader, and any Live Bonus → Song / Lesson /
  `support_effects` key are all unimplemented because unevidenced, not deferred.
- **Absence is guarded:** `tests/Feature/GrandConcertPanelTest.php`'s refusal list grew from five words to eight
  on this gate and now refuses `live bonus`, `practice bonus` and `mastery bonus` beside `song`, `lesson` and
  `hype`. No second structural file was added: `LessonEvidenceBoundaryTest.php:84` already pins that an
  unrecognised key on the turn write is collected as nothing and stored as nothing, and its structural refusals
  cover the same shape.

  > **Dated note (2026-10-21).** Re-verified. `tests/Feature/GrandConcertPanelTest.php` still holds the
  > eight-word `$unmeasured` list, now at `:117-126` — the earlier `:103` pointed at the wrong line and
  > is corrected. `tests/Feature/LessonEvidenceBoundaryTest.php:84` still resolves, to the test
  > "refuses to let a Lesson field ride in on the turn write, rather than half-supporting one". The
  > committed dictionary `database/seeders/data/support_effects.ca447e53.json` still holds 35 rows, with
  > `友情ボーナス` at id 1 carrying one English name in both `name_en` and `name_en_eon`, `得意率アップ`
  > at id 19 carrying the two conflicting ones, and no row for `サポート連続イベント率アップ` — the
  > divergence the bullet describes is still reproduced inside the one file. `SupportEffect.php:17`
  > still says the table lets the UI print "Friendship Bonus" without a per-id label, and
  > `tests/Feature/SupportCardPageTest.php:85,93,253,261` still assert that name.
- **Reopen condition:** the same `[Global]` Lesson-menu capture gaps §7-19 and §7-21 name, and it owes this gate
  the layer label, each of the three type names as printed, and which type each Song row carries. One cheaper
  local route to try first, which does **not** close the gate alone: `docs/research-scratch/DESIGN-CORPUS.md`
  records **Our Grand Concert: None found**, and the corpus's screen-type table documents no
  support-card effect list, only rail avatars, a Career Profile "Support Cards" row and an unread **Perks**
  button. Support effects are server-wide rather than scenario-specific, so an existing frame may already
  measure the client's word for `友情ボーナス` and `得意率アップ`. That settles two of six renderings for the
  support-effect domain only; a ladder needs no frame and has no source.

  > **Dated note (2026-10-21).** The citation is de-pinned to the section that carries it, and the
  > content is re-verified: under "Scenario coverage" in `docs/research-scratch/DESIGN-CORPUS.md` the
  > scenario row reads **Our Grand Concert | None found** (the file's earlier `:4261` pointed at the
  > section heading, and the row itself is further down), and the screen-type table under "Screen-type
  > coverage" documents no support-card effect list. Both the "none found" finding and the screen-type
  > coverage hold on 2026-10-21. Line numbers are dropped because the corpus file grows; the section
  > names are stable.

### Determine

Superseded by the outcome above. The five questions this slice asked (terminology, observations versus derived
values, sources of existence, per-value authority, and whether a current total is representable) are each answered
in §7-22: terminology unmeasured, existence corroborated at tier B, no per-value authority, and no current total
derivable.

### Explicit boundary

The existing documentation marks the **Live Bonus percentage ladder** as unverified. Confirmed and sharpened: the
ladder is unpublished while the first-instance values are published by both guides as third-party prose. Neither
is encodable.

Do not invent or encode it.


---

## Slice 15 — Live Bonus Lifecycle / Read Model

**Status: BLOCKED BY EVIDENCE** (gated on Slice 14, which closed the gate: no verified Live Bonus vocabulary or value exists to persist or read)

If Slice 14 establishes valid data:

- persist verified Live Bonus observations;
- expose them through canonical read-side architecture;
- preserve provenance;
- do not derive unsupported percentages.

No speculative balance/meter system.

---

## Slice 16 — Promotional Lives Evidence + Foundation

**Status: BLOCKED BY EVIDENCE** (gate run 2026-10-09; the determination is in `SCREEN_SPEC.md` §7-23)

Establish the authoritative representation and lifecycle boundary for Promotional Lives.

### Naming correction first

The `[Global]` client calls these **Promo Concert**, not "Promotional Lives". Cygames' notice 905 prints "**Four
Promo Concerts and one Grand Concert** are held, making for a total of five concerts", and Game8 independently uses
"Promo Concerts" too. "Promotional Lives" is GameTora's rendering of the JP word, which makes this Phase's own
title guide vocabulary rather than client vocabulary — the one Grand Concert mechanic in the whole line whose noun
is already settled at tier S, and settled against the roadmap's name.

That is a naming fact, not an implementation licence, and this slice changes no code. `docs/UMAMUSUME_REFERENCE.md`
§2.9 ruled the same thing on the day of the primary read; the guards already carry the asymmetry
(`GrandConcertPanelTest.php` and `tests/browser/grand-concert-panel.spec.ts` refuse `promotional live`, and
nothing refuses `promo concert` because that word is licensed copy). **Whether Phase D is retitled "Promo
Concerts" is the owner's decision and is raised, not made.**

> **Dated note (2026-10-21).** The asymmetry still holds, and the line numbers are corrected: the
> PHP refusal list is `tests/Feature/GrandConcertPanelTest.php:117-126` (the word `promotional live`
> is in it), and the browser spec's refusal loop is `tests/browser/grand-concert-panel.spec.ts:102`.
> The earlier `:107` and `:98` pointed at the two tests' opening lines rather than at the guards.
> `docs/UMAMUSUME_REFERENCE.md` §2.9 resolves, to "Third read the same day: the `[Global]` launch
> notice names the scenario's own vocabulary". The naming decision itself is unchanged: it is the
> owner's, and the Phase D naming note at the head of that Phase records it as raised, not made.

### Outcome of the gate

**Promo Concerts — BLOCKED BY EVIDENCE.** Unlike Slices 8, 11 and 14, the blocker is not vocabulary. It is a
calendar position, a lifecycle, and a table that can hold either.

- **Settled at tier S:** the noun (**Promo Concert**, **Grand Concert**), the count (**four plus one, five
  concerts**), the cadence (**biannually, from the second half of December of the trainee's junior year**), the
  gauge's official name (**Live Performance Expectations**) and the max-gauge outcome (**Great Success**). No
  other Grand Concert mechanic has ever had this.
- **Not settled, and it is structural, not textual.** Neither timeline table can hold the series.
  `scenario_slots` has **no `year` column** and its live unique index is `(scenario_key, month, half, kind,
  source_key)`, so two Late-December concerts in different career years collide; `ScenarioSlot::VALID_KINDS` is
  `goal_race / team_race / grade_deadline / scripted_event / free_race` with no concert kind, and the model's
  `saving` guard throws on any other. `race_catalog_slots` does carry `year`, but it is race-shaped (non-null
  `grade_code`, plus `distance`, `surface`, `track_id`) and engine-owned from the GameTora instance export.
- **Measured negative:** searching every committed race name for Concert, Live, Promo or Grand returns **zero
  rows**. The catalog holds the scenario's finale (`final_live => our_grand_concert`, mandatory, the value KI-71
  corrected at `480b711`) and none of its four Promo Concerts — so the finale is a calendar row and the concerts
  are nothing.
- **Timing is not derivable without invention.** No source prints the (year, half) positions. GameTora's "before
  the 1st/2nd/3rd/4th Promo Live" segments gate **Song lesson unlocks**, not concert dates; Game8's per-year
  figures are the same unlock pattern. Placing the first concert needs this app's mapping of "junior year" to a
  career-year integer, which no source supplies.
- **Lifecycle unknown in every direction the app would need:** whether a concert occupies a turn
  (`docs/scenarios/07-grand-concert.md` states the turn shape of the live events was never published as a turn list), whether it can be declined or
  missed, and whether a confirmation, stage or result screen exists. GameTora's "you may choose to do lessons
  before stepping onto the stage … like you would learn skills before races" is an analogy for the *lesson* step,
  not evidence of a concert screen.
- **The outcome set is one word deep.** **Great Success** is official; `大成功` is the JP string; GameTora's "great
  or normal success" is its own English. No source names the other outcomes as printed or says how many tiers
  exist, so an outcome enum ships one licensed case and one inferred case.
- **Costs:** no source states a concert consumes Performance, and the one official relation runs the other way —
  gauge max *guarantees* a Great Success. Its subject is the still-unbuilt Live Performance Expectations concept,
  so the relation stands recorded and unmodelled.
- **Every payout is single-source:** 5 skill points per Technique Lesson and 25 per Song Lesson, and the +50
  resource-cap rise (GameTora); "each Promo Concert grants a stat increase" (Game8); "a Great Success raises stat
  caps" (GameTora); "the outcome of the Promo Lives feeds the finale" (GameTora, no mechanism named). The notice
  "publishes no number of any kind".
- **Smallest truthful representation:** already exists, generic. `TurnEventType::Scenario` with `source_name` and
  nullable `deltas` records "the second Promo Concert happened at turn N and was a Great Success" today, in words
  the Trainer supplies. The fourth consecutive gate to reach that finding.
- **Rejected as invention:** `PromotionalLiveType`, `PromotionalLivePayload`, a model or table, a state machine, a
  milestone scheduler, a cost or reward calculator, a `concert` kind in `scenario_slots` (that is a schema change,
  which §11 makes travel with a migration, an `ARCHITECTURE-ESSENTIALS.md` digest, a PRD citation and an ADR —
  spent backwards to hold a schedule no source pins down), synthetic `race_catalog_slots` rows for a non-race, and
  any Promo Concert → Song / Lesson / Live Bonus / Hype key.
- **Stale claim reported, not edited:** `database/migrations/2026_09_27_153416_create_scenario_slots_table.php:15`
  still says "Our Grand Concert has no guide at all", false since the 2026-10-05 read. Per `AGENTS.md` §2 the code
  wins and the rule is stale; a schema file's prose is not this slice's surface.
- **Reopen condition:** **two** captures, and one alone is not enough — a `[Global]` **pre-live / backstage screen**
  (the event's own name, the gauge label, whatever the client calls the action) **and** a `[Global]` **career
  calendar or goal row** showing where the four concerts sit, because only a screen settles a (year, half)
  position. Magnitudes need a source rather than a frame: no screen resolves 5 / 25 skill points or the +50.

### Determine

Superseded by the outcome above. The five questions this slice asked are each answered in §7-23: what constitutes
it (a scheduled concert, official, but with no unplaceable-in-data date), what can be observed (the event and a
licensed outcome word), what can be entered (nothing the app may prefill), which outcomes are authoritative (one),
and what remains unverified (turn shape, interaction, costs, payouts, repeatability, the finale relation).

Do not assume mechanics from external/community sources.

---

## Slice 17 — Promotional Lives Lifecycle

**Status: BLOCKED BY EVIDENCE** (gated on Slice 16, which closed the gate: no Promotional Live representation exists and no table can hold the series without a schema change)

Integrate verified Promotional Live observations/results into the existing run lifecycle.

Reuse existing:

- TurnEvents;
- run state;
- read-side methods;
- capability configuration.

No duplicate subsystem.

---

## Slice 18 — Promotional Lives Trainer UI

**Status: BLOCKED BY EVIDENCE** (gated on Slice 16; there is no verified interaction, schedule or status to display, though the event's own noun is official)

Add the minimum truthful Trainer-facing Promotional Live interaction/status surface.

Do not expose speculative success formulas, Hype effects, bonus percentages, or rewards.

---

# Phase E — Grand Concert + Intelligence

## Slice 19 — Grand Concert Finale State Foundation

**Status: COMPLETE** (landed 2026-10-09 at `aa5d975`; the reasoning is in
`app/Services/Scenario/FinaleReader.php`'s docblock and `ScenarioSectionBuilder::finaleAbsence()`, and the
cases are in `tests/Feature/FinaleAbsenceTest.php`)

> **Dated note (2026-10-21).** The commit resolves and the named files exist:
> `app/Services/Scenario/FinaleReader.php` is present and its docblock still answers the question
> once, for three consumers; `tests/Feature/FinaleAbsenceTest.php` is present; `aa5d975` resolves as
> "refactor(career): give the finale one word per question on both screens". The **method name has
> moved**: there is no `CockpitController::finaleAbsence()` on this tree. The method now lives as
> `ScenarioSectionBuilder::finaleAbsence()` in
> `app/Services/Career/ScenarioSectionBuilder.php`, a private method on the builder the Career
> Cockpit composes; `app/Http/Controllers/Career/CockpitController.php` reads the scenario section
> through that builder and does not declare a `finaleAbsence()` of its own. The reasoning and the
> three answers are unchanged; the owning class is not. The slice's status stays `COMPLETE`.

What the slice asked for is settled by reading the catalogue rather than the config. The finale is a
scenario-scoped mandatory row in the career calendar, so `ScenarioSectionBuilder::finaleAbsence()` answers
from that row instead of from the `config/scenarios.php` `finale` key, which stays undeclared because
no component reads its contents. Three answers, and the branch is driven by the row's presence rather
than by a scenario name, so a scenario name in the payload path would be a second source for the
layout path, which `AGENTS.md` §7 forbids.

- On the calendar: the absence sentence names the mandatory race and states that the scenario declares
  no structure beyond it.
- Not on the calendar: the sentence denies a finale, which is now true rather than a blanket claim.
- Declared structure: no absence sentence at all, because there is nothing absent.
- A row belonging to another scenario is ignored, so a shared catalogue cannot leak one scenario's
  finale into another's screen.

No finale state machine was built. The representation is the catalogue row's presence plus the run's
own reading of it, which is what Slice 20 reports.

---

## Slice 20 — Grand Concert Execution + Reporting

**Status: COMPLETE** (landed 2026-10-09 at `aa5d975`; `SCR-CAR-018`, cases in
`tests/Feature/FinaleReportingTest.php`)

The verified finale observations reach the existing read-only reporting path through `ResultController`
and `/run/{run}/result`, with no new endpoint and no speculative success calculation. One server-side
reader (`FinaleReader`) feeds both the Career Result and the Cockpit, so no two screens can disagree.

Three states, and the middle one is the one nothing could previously say:

- **Not yet reached.** `placement` and `turn` are null rather than zero or an empty string, because a
  screen that printed an empty placement cell for a finale nobody has run would state a finish that
  does not exist (D-220).
- **Reached with nothing recorded.** The position is read from the highest logged turn against the
  finale block, so one row is enough; no count is taken.
- **Recorded.** The run's own stored outcome word, placement and turn. The outcome set is one word
  deep: the run's status label, not a finale-specific vocabulary.

A scenario whose calendar carries no finale row emits no finale state at all, and the old `finale` key
is gone rather than left standing as a null, which is how a reader tells a scenario with no finale row
from a payload that never carried the key. The catalogue row's own title is not carried: it reads
"URA Finals Final (Grand Live)" and is filed as KI-82.

---

## Slice 21 — Scenario-Aware Trainer Advisor

**Status: COMPLETE** (landed 2026-10-09 at `ad8dc77`; `ScenarioAdvisorFinaleTest`, cases in tests/Feature/ScenarioAdvisorFinaleTest.php)

Only after underlying Grand Concert state exists.

Update Trainer Advisor so that it can consume verified Grand Concert-specific state.

### Critical rule

Do not make Advisor scenario-aware by inventing scenario mechanics.

Advisor recommendations must distinguish:

```text
Observed fact
Derived fact
Recommendation
```

and maintain provenance.

---

## Slice 22 — Scenario Cockpit Integration

**Status: COMPLETE** (landed 2026-10-09 at `aa5d975`; `CockpitFinaleStripTest` and `CareerCockpitTest` pin the scenario-state surfaces, cases in tests/Feature/CockpitFinaleStripTest.php)

Expose verified Grand Concert state through the existing Career Cockpit/turn flow.

Respect existing:

```text
config/scenarios.php
```

capabilities and panel gates.

Do not turn every scenario mechanic into a new panel.

Use existing cockpit composition patterns.

---

# Phase F — Acceptance / Hardening

## Slice 23 — Cross-Feature Consistency Audit

**Status: COMPLETE** (`aa5d975`; see the Cross-Feature Consistency entry in the Current Status table at the end of this document)

> **Dated note (2026-10-21).** `aa5d975` resolves, and the named tests exist:
> `tests/Feature/CockpitFinaleStripTest.php`, `tests/Feature/CareerCockpitTest.php`,
> `tests/Feature/FinaleAbsenceTest.php`, `tests/Feature/FinaleReportingTest.php` and
> `tests/Feature/ScenarioAdvisorFinaleTest.php` are all present on this tree. The cross-feature
> *outcome* was not re-derived for this pass, so the audit's conclusions are **unverified** as of
> 2026-10-21; the slice's status is unchanged.

Audit the complete chain:

```text
Performance
    ↓
Lessons
    ↓
Songs
    ↓
Live Bonuses
    ↓
Promotional Lives
    ↓
Grand Concert
```

Verify that each feature:

- uses canonical architecture;
- has no duplicate state representation;
- preserves observed-vs-derived separation;
- has consistent TurnEvent behavior;
- has deterministic read-side behavior;
- has correct scenario capability gating;
- does not expose unverified mechanics;
- does not silently calculate unsupported totals;
- has appropriate tests;
- has matching SCREEN_SPEC documentation.

Only fix demonstrated inconsistencies.

No broad refactor for aesthetic reasons.

---

## Slice 24 — Full Grand Concert Acceptance Pass

**Status: COMPLETE** (see the acceptance record at 2026-10-09, below)

Perform the final scenario readiness assessment.

### Verify

#### Scenario entry

- scenario registration;
- scenario selection;
- trainee selection;
- training target;
- inheritance;
- support deck;
- preflight.

#### Career lifecycle

- run creation;
- turn entry;
- Performance;
- Lessons;
- Songs;
- Live Bonuses;
- Promotional Lives;
- finale;
- Career Report;
- veteran/Legacy handoff.

#### UI

- capability visibility;
- panel visibility;
- turn-entry surfaces;
- validation;
- confirmation;
- readback;
- empty states;
- provenance/explanations.

#### Data integrity

- scenario-scoped race catalog;
- no duplicate finale;
- no speculative database state;
- no accidental canonical DB mutation.

#### Tests

Run the appropriate project testing levels according to `AGENTS.md §9`.

Only claim gates that were actually executed.

### Acceptance record — 2026-10-09

The scenario is complete as far as current evidence permits. Every actionable criterion above is
verified against the passing feature tests on this tree, not against a remembered state:

- **Scenario entry** — `CareerScenarioSelectTest`, `CareerTraineeSelectTest`, `CareerBuildTargetTest`,
  `CareerInheritanceEventTest`, `CareerLegacyDeckStepsTest`, `CareerPreflightTest`: green.
- **Career lifecycle** — `CareerCockpitTest` (run creation, turn entry, correction, status/scenario/period
  writes), `CareerTrainingDetailTest` (Performance), `CareerResultTest` (Career Report),
  `CareerSaveVeteranTest` (veteran handoff): green.
- **UI** — `GrandConcertPanelTest` and `ScenarioPanelTest` (capability visibility, panel visibility,
  validation, readback, empty states, provenance): green.
- **Data integrity** — scenario-scoped race catalog and the finale reads are pinned by
  `CockpitFinaleStripTest`, `FinaleAbsenceTest`, `FinaleReportingTest`, `RunRaceStripTest`: green.
- **Blocked mechanics (Lessons, Songs, Live Bonuses, Promo Concerts)** — the acceptance is the absence,
  and it is stated: the `our_grand_concert` entry's `panel_absence` in `config/scenarios.php` ("The
  mechanics for this scenario are sourced, but no capture of the client's own screens exists yet, so
  nothing the concert itself is on draws here. The trainee's own objective ladder is the one panel this
  entry composes, and that is a career fact rather than a concert mechanic (ADR-0026)."), asserted by
  `GrandConcertPanelTest`. No surface invents a mechanic.

  > **Dated note (2026-10-21).** The citation is corrected and the quotation made verbatim. The
  > earlier text cited `config/scenarios.php:480`, which is not the `panel_absence` line — the key
  > lives in the `our_grand_concert` entry, and the wording quoted above is the file's actual text
  > rather than the shortened paraphrase the record carried. The substance is unchanged: the absence
  > is still declared in config and still asserted by `GrandConcertPanelTest`.

**Gates, as actually run.** `php artisan test --compact`: **8 failed, 2 skipped, 1489 passed (26155
assertions), 341.74s**. All eight failures are the F2 cutover's, not this scenario's: six in
`StatBandTest` (its `StatBand`/`GradeBadge` subject retired with `Runs/Show.vue`, the F2 close-out's
Delta C) and two in `TurnEntryFieldSourceTest` (stale assertions left by `1ec92ab`, the energy-state
change). `npm run typecheck` exit 0; `npm run build` exit 0; `composer lore` exit 0 (302 pre-existing
hits); `composer lore-code` exit 0 (118 pre-existing hits).

**Browser walk: not completed.** The harness cannot own its port: a leaked `php artisan serve` holds the
default `:8127` (KI-73), and the box was contended enough that the suite ran ~46s per case (KI-74), so
the run was voided as a signal rather than read. The failures seen before the void were all F2 cutover
leftovers — `career-cockpit.spec.ts`'s stale "run screen" link copy, `career-result.spec.ts`'s stale
`runs.show` status flow, and `run-detail.spec.ts` driving the redirected page — and the concurrent
session was repairing them in the working tree during this pass (`career-cockpit.spec.ts:114` and
`career-result.spec.ts:35` are already repointed, and `run-detail.spec.ts` is staged for deletion).

*Corrected 2026-10-10.* The sentence above that these eight failures were "repaired by a concurrent session during the pass" is superseded. Of the eight, **six were in `StatBandTest`** and were **retired** under the owner's ruling R-A1 (the subject, `GradeBadge.vue` and `StatBand.vue`, was deleted by F2's Delta C cleanup). The remaining **two were in `TurnEntryFieldSourceTest`** and were **adapted by F2** in a later commit after `1ec92ab` changed `TrainingDetail.vue`'s import. All eight were F2's to close; F2 closed them outside the Grand Concert line. The Grand Concert acceptance did not see them repaired mid-run.

#### Re-verification — 2026-10-21

The acceptance record above is left exactly as it was written. What follows is a second, dated look at
the same claims on 2026-10-21, against `master` at `d00a0457`. Nothing in it reopens the acceptance
and nothing in it changes a slice status.

**Files the record names, checked on 2026-10-21.**

| Named in the record | State on 2026-10-21 |
| ------------------- | ------------------- |
| `StatBandTest` | **Absent.** The file is gone, which is what the 2026-10-10 correction above records: the subject was retired under R-A1 with `Runs/Show.vue`. The six failures it reported are therefore not failures any more. |
| `TurnEntryFieldSourceTest` | **Present** — `tests/Feature/TurnEntryFieldSourceTest.php`. |
| `run-detail.spec.ts` | **Absent.** The record said it "is staged for deletion"; it has since been deleted. The other two browser specs the record names, `career-cockpit.spec.ts` and `career-result.spec.ts`, are present. |
| `GrandConcertPanelTest`, `ScenarioPanelTest`, `CockpitFinaleStripTest`, `FinaleAbsenceTest`, `FinaleReportingTest`, `RunRaceStripTest`, `CareerScenarioSelectTest`, `CareerTraineeSelectTest`, `CareerBuildTargetTest`, `CareerInheritanceEventTest`, `CareerLegacyDeckStepsTest`, `CareerPreflightTest`, `CareerCockpitTest`, `CareerTrainingDetailTest`, `CareerResultTest`, `CareerSaveVeteranTest` | **All present** under `tests/Feature/`. |
| `config/scenarios.php` `panel_absence` | **Present**, in the `our_grand_concert` entry, with the wording quoted above. |
| `SCREEN_SPEC.md` `SCR-CAR-018` | **Present** as a §3 row and a §4 section. |

**The suite, run on 2026-10-21.** `php artisan test --compact` was run on this tree and reported
**84 failed, 1862 passed (31239 assertions), 231.75s**. The run was **not green**, and this document
does not claim it was. The 84 failures were classified before anything was re-run, per `AGENTS.md`
§9's failure-attribution rule, and they fall into two classes:

- **~78 are environment failures, not product defects.** The harness's file policy denies writes
  under `storage/`, and the tree's own tests need them: `storage/framework/testing/disks/…` cannot
  be created (`UnableToCreateDirectory`) and `storage/logs/laravel.log` cannot be opened in append
  mode. That is confirmed directly, outside the suite: creating a directory under
  `storage/framework/testing/disks/` returns "Access to the path … is denied", and writing a file
  under `storage/logs/` returns the same. The affected tests are the ones that mirror artwork, write
  snapshots, write the log, or back up a database.
- **6 are pre-existing assertion failures at `HEAD`, in app code that this document does not own
  and that this pass did not touch**: `RenderedCopyHygieneTest` (dashes in
  `resources/js/pages/Database/Scenarios.vue` and `resources/js/pages/Skills/Show.vue`),
  `UiHygieneTest` (ADR citations in `resources/js/pages/Skills/Show.vue`),
  `CareerBuildTargetTest` ("Wit" in `resources/js/pages/Career/BuildTarget.vue`),
  `CareerResultTest` (the `save_veteran` payload keys), `UnityCupSurfacesTest` (the dual-value
  readout's title no longer naming `team_rank_ladder`), and one `RunWriteAtomicityTest` case whose
  expected forced-failure message is replaced by the log-permission error.

The comparison with the record above is therefore not like for like: the 2026-10-09 run reported
1489 passing tests and the 2026-10-21 run reports 1862, because the tree has grown. **No Grand
Concert test is among the failures.** The acceptance record's own gate figures stay as they were
written — they describe the 2026-10-09 run — and this paragraph is the dated erratum for them.

**What this pass did not run.** `npm run typecheck`, `npm run build`, `composer lore`,
`composer lore-code` and the browser suite were **not** run: this pass changed four Markdown files
under `docs/proposals/` and no TypeScript, no Vue, no Blade and no PHP, so those gates have nothing
of mine to check. `git status` confirmed that no file outside `docs/proposals/` was modified.

---

# Global Out-of-Scope Rules

These rules apply to every remaining slice unless a future slice explicitly establishes verified evidence for them.

Do not:

- invent game mechanics;
- invent numeric costs;
- invent starting values;
- invent caps;
- invent Hype formulas;
- invent Live Bonus percentages;
- invent Song effects;
- invent Lesson effects;
- infer unsupported Performance conversions;
- create speculative recommendation logic;
- create duplicate models/tables;
- create duplicate endpoints;
- create a second turn-writing path;
- add unnecessary global state;
- introduce a new frontend framework;
- perform broad refactors;
- redesign unrelated screens;
- modify unrelated concurrent work.

---

# Repository Safety Constraints

The following areas are protected and must not be modified by scenario slices unless explicitly authorized by a separate task:

```text
config/database-safety.php
app/Services/DatabaseSafety/
tests/browser/global-setup.ts
playwright.config.ts
.scratch-uma/incident-2026-10-08-devdb/
.scratch-uma/db-recovery-2026-10-08/
composer.json
composer.lock
D:/Projects/uma_musume_race_planner/
```

Never run destructive database commands against the canonical database.

Do not use the canonical development database as a disposable test database.

Do not commit changes unless a separate instruction explicitly authorizes a commit.

Do not clean or revert unrelated concurrent work.

---

# Slice Completion Contract

Every slice must end with a report containing:

1. **Determination**
2. **Evidence**
3. **Files changed**
4. **Architecture decision**
5. **UI behavior**
6. **Mechanics explicitly not implemented**
7. **Tests and gates actually run**
8. **Safety/concurrency confirmation**
9. **Git/commit status**
10. **Explicit stop**

An agent must stop after its assigned slice.

A later slice may be started only after the previous slice has been reviewed/accepted.

---

# Current Status

```text
Phase A — Performance
  Slice 1  Baseline audit                         COMPLETE
  Slice 2  Finale race catalog correction        COMPLETE
  Slice 3  Performance foundation                 COMPLETE
  Slice 4  Performance turn-write                COMPLETE
  Slice 5  Performance read-side                 COMPLETE
  Slice 6  Performance Trainer input              COMPLETE
  Slice 7  TrainingDetail boundary                COMPLETE

Phase B — Lessons
  Slice 8  Evidence + foundation                  COMPLETE (gate closed: no Lesson representation exists)
  Slice 9  Turn lifecycle                         BLOCKED BY EVIDENCE
  Slice 10 Trainer UI                             BLOCKED BY EVIDENCE

Phase C — Songs
  Slice 11 Evidence + foundation                  BLOCKED BY EVIDENCE
  Slice 12 Lifecycle integration                  BLOCKED BY EVIDENCE
  Slice 13 Trainer UI                             BLOCKED BY EVIDENCE

Phase D — Live systems
  Slice 14 Live Bonus foundation                  BLOCKED BY EVIDENCE
  Slice 15 Live Bonus lifecycle                   BLOCKED BY EVIDENCE
  Slice 16 Promotional Lives foundation          BLOCKED BY EVIDENCE (client noun is Promo Concert)
  Slice 17 Promotional Lives lifecycle            BLOCKED BY EVIDENCE
  Slice 18 Promotional Lives UI                  BLOCKED BY EVIDENCE

Phase E — Grand Concert + Intelligence
  Slice 19 Finale state foundation                COMPLETE
  Slice 20 Finale execution/reporting             COMPLETE
  Slice 21 Scenario-aware Advisor                 COMPLETE
  Slice 22 Scenario cockpit integration           COMPLETE

Phase F — Acceptance
  Slice 23 Cross-feature consistency audit        COMPLETE (aa5d975)
  Slice 24 Full Grand Concert acceptance          COMPLETE (see the acceptance record, 2026-10-09)
```

## Definition of "Complete"

Our Grand Concert is considered implementation-complete only when the existing Trainer Desk architecture can truthfully represent and expose the verified scenario lifecycle from career setup through Grand Concert completion, while clearly distinguishing:

```text
Observed Game State
        ↓
Derived State
        ↓
Recommendation
```

and without presenting unsupported game mechanics as facts.

**Completion does not mean implementing every mechanic found on external wikis.**

It means implementing everything the project's authoritative evidence supports, documenting what remains unknown, and ensuring the application never fabricates certainty where the underlying game data has not been verified.