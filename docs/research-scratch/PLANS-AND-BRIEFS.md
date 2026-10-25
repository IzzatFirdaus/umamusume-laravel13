# Plans and Briefs

## Provenance

This document consolidates the following source files verbatim (no summarization, no deduplication):

- `docs/design-research/TASK-16-RUN-VIEW-FRAME-BRIEF.md` (481 lines)
- `docs/design-research/replan-mobile-first.md` (372 lines)
- `docs/design-research/d-30-amendment-draft-2026-10-01.md` (51 lines)
- `docs/proposals/audit-decisions-2026-10.md` (389 lines, 16 headings), folded in 2026-10-04 (Round 11)
  as the section `## audit-decisions-2026-10.md` at the end of this file. Chosen home: this master already
  carries the O-2 and O-3 owner-gate packages, and the file is a third one, options and recommendations
  with a named owner per item and no ruling recorded. It is live, not closed. Three of its twelve items
  were settled by peer commits before the fold and are marked as such in the section itself. Its `O-2`
  and `O-3` labels are its own numbering and collide with the O-2/O-3 packages above and with the
  `O-1` to `O-3` gates in `PROCESS-PLANS.md`; read the section heading before following an `O-n`.
- `docs/superpowers/plans/2026-10-05-image-slot-display.md` (911 lines, 18 headings), folded in
  2026-10-06 as the section `## 2026-10-05-image-slot-display.md` at the end of this file. Chosen home:
  it is a bite-sized slice plan with a per-task progress table, a slice log and a filed erratum, which is
  the class of the Task 16 brief and the replan above, and its subject is owned elsewhere
  (`docs/adr/0021-sourced-character-artwork.md` for the mirror, `DESIGN.md` Â§4.7 for the slot rules,
  `PRD.md` OQ-6 for what is still open), so the plan itself is the only piece with no master. It was
  fully executed: the progress table records all ten tasks, Task 9 as blocked with no placeable surface
  and Task 4, 5, 6 and 8 as superseded in part by the Inertia port. The original was tracked, so its
  deletion after this fold is recoverable from git history.
- `docs/proposals/provenance-and-absence-contracts.md` (839 lines, appended 2026-10-10, Round 14,
  augment-only) as the section `## provenance-and-absence-contracts.md`, and
  `docs/research-scratch/PLANS-AND-BRIEFS.md` section `## screen-spec-2.0.md` (1,787 lines, appended 2026-10-10, Round 14, augment-only) as the
  section `## screen-spec-2.0.md`: both are live Trainer Desk 2.0 reference documents (a pre-ruling
  reconciliation proposal and the 2.0 screen-set target, reference-only, not authorization) that stay at
  their own paths; nothing was deleted and no citation was repointed.
- `docs/proposals/provenance-and-absence-contracts.md` (839 lines) and `docs/research-scratch/PLANS-AND-BRIEFS.md` section `## screen-spec-2.0.md`
  (1,787 lines), embedded verbatim 2026-10-10 (Round 14, augment-only) as the sections
  `## provenance-and-absence-contracts.md` and `## screen-spec-2.0.md`. The first is the reconciliation
  proposal with its ten owner rulings; the second is the 2.0 screen-set target with its governance holds.
  Both stay live at their own paths; nothing was deleted and no citation was repointed.
- `docs/proposals/provenance-and-absence-contracts.md` refreshed and consolidated 2026-10-15 (Round 16,
  owner-ordered): the source had grown to 889 lines (§22, the 2026-10-11 Phase G preflight, added after
  the Round 14 embed), so the section above was re-embedded from the current source, verified line by
  line (0 missing), and the original was deleted from `docs/proposals/`. Live citations were repointed to
  this section; `docs/research-scratch/PLANS-AND-BRIEFS.md` section `## screen-spec-2.0.md` stays live at its own path.
- Round 18 (2026-10-21, owner-ordered): two more `docs/proposals/` files were consolidated here, and
  the Round 14 embed of a third was refreshed.
  - `docs/proposals/frontend-fidelity-2026-10-13.md` (476 lines) as the section
    `## frontend-fidelity-2026-10-13.md`, and
    `docs/proposals/ux-laws-audit-plan-2026-10-13.md` (626 lines) as the section
    `## ux-laws-audit-plan-2026-10-13.md`. The first is the two-track fidelity plan (why the shipped
    surface reads as a wireframe, and the two-track path); the second is its sibling, the 27-law
    checklist with its coverage matrix, instruments and output contract. Both were re-verified
    line-by-line before the delete (399 of 399 and 521 of 521 non-blank lines present, in order, with
    one-level heading demotion; 0 missing) and again after it.
  - `docs/proposals/screen-spec-2.0.md` (1,788 lines) was already embedded by Round 14 and was measured
    on 2026-10-21 as already containing every source body line, so it was not re-embedded; only the
    framing note was refreshed, to record that the source is now deleted.
  - The owner approved one departure from verbatim: each source's own cross-references inside the
    embedded body are repointed to the master and its section anchor, so every citation in the tree
    resolves rather than rotting. The bodies are otherwise byte-identical to their sources, and the
    verification compares against the source lines with the same substitution applied.
  - The other five files of this round landed in `PROCESS-PLANS.md` (one), `SLICE-RECORDS.md` (one) and
    `AUDIT-AND-VERIFICATION.md` (two), with one refreshed in place in `DESIGN-CORPUS.md`; the routing
    table for all eight is in `docs/research-scratch/INDEX.md` §Round 18.
- **Round 20 (2026-10-21, owner-ordered): seven more frameworks, also not consolidated.**
  One live `docs/framework-ui-ux/` file was added per framework, deliberately left in place like the
  Round 19 file:

  | File | Framework |
  | --- | --- |
  | `docs/framework-ui-ux/design-framework-index-2026-10-21.md` | the entry point |
  | `docs/framework-ui-ux/usability-heuristics-2026-10-21.md` | Nielsen's ten |
  | `docs/framework-ui-ux/shneiderman-golden-rules-2026-10-21.md` | eight rules |
  | `docs/framework-ui-ux/tognazzini-first-principles-2026-10-21.md` | sixteen principles |
  | `docs/framework-ui-ux/gerhardt-powals-principles-2026-10-21.md` | ten cognitive engineering principles |
  | `docs/framework-ui-ux/information-architecture-principles-2026-10-21.md` | Dan Brown's eight |
  | `docs/framework-ui-ux/gestalt-principles-2026-10-21.md` | twelve Gestalt principles |
  | `docs/framework-ui-ux/norman-interaction-principles-2026-10-21.md` | Norman's six, the two gulfs, the seven stages |
  | `docs/framework-ui-ux/wcag-2.2-accessibility-2026-10-21.md` | WCAG 2.2 AA |

  The same reason as Round 19 applies, and more strongly: three of these had a **blocked or partial
  primary source** (Gerhardt-Powals is paywalled, Dan Brown's article is behind a Cloudflare
  interstitial, and Norman's book is not fetchable), and each file records that at the top of itself.
  A single citable copy that can be re-read against its source is the point; embedding would create
  the second copy a source revision then has to chase. `INDEX.md` carries the routing rows,
  `DESIGN-CORPUS.md` names the layering, and `GOVERNANCE.md` §9 records which of them the C-7
  review actually uses.

- **Round 19 (2026-10-21, owner-ordered): no consolidation.** One new live `docs/framework-ui-ux/` file was
  added and deliberately left in place: `docs/framework-ui-ux/usability-heuristics-2026-10-21.md`. It records
  Nielsen's ten usability heuristics verbatim from NN/g, the per-heuristic detail articles, the mapping
  onto the D-rules, and the H3 / H7 / H10 gap register.

  Why it is not embedded here. This section's `## ux-laws-audit-plan-2026-10-13.md` is the **Laws of
  UX and Laws of UI** checklist and stays the authority for those. The new file is a different
  collection with a different authority structure: NN/g revises the heuristics (the page was last
  reviewed 2024-01-30, and its own text changed), so the file has to stay a single citable copy that can
  be re-read against the source. Embedding it would create the second copy that a revision then has to
  chase. `INDEX.md` routing row carries it, `UX-DELIVERABLES.md` Part 3 defers to it for the statements
  and keeps the ratings, and `GOVERNANCE.md` §9 records it as the C-7 review checklist.


- Round 21 (2026-10-26, owner-ordered): the two rulings-owed briefs of the Group A + Group B / remaining-work thread were consolidated here and their originals deleted — `docs/proposals/group-ab-rulings-owed.md` (223 lines, 184/184 verified) and `docs/proposals/remaining-work-rulings-owed.md` (305 lines, 217/217 verified), each as its own `## <filename>` section. Chosen home: this master already carries the O-2, O-3 and `audit-decisions` packages, and a two-option write-up with a recommendation per item is that same class. The plans went to `PROCESS-PLANS.md`, the audits to `AUDIT-AND-VERIFICATION.md`; see `docs/research-scratch/INDEX.md` section `### Round 21`. <!-- census: quoted-original -->

---

## TASK-16-RUN-VIEW-FRAME-BRIEF.md

### Task 16 — The run view axis: two-region frame, and the scenario-composed right rail

Revised 2026-09-29 against the tree at `a8a52cd`, after a pre-dispatch verification pass. The four rulings
in §R were returned by the owner the same day; §0.5 records what was read from the files rather than assumed.

**Dispatch gate.** This brief is not runnable until the replan's acceptance is a fact on disk.
`docs/design-research/replan-mobile-first.md` currently reads **"Status: proposal. Nothing here is landed."**
Step 0 halts on that line, correctly, and the fix is the owner's to write — not the agent's to infer. The
wording proposed for acceptance is in §0; if the header still reads `proposal` at execution time, stop and
report rather than proceeding on a chat message.

Three things land in one slice because they are one decision seen from three angles:

1. The D-40 addendum (R82), which turns the live contradiction between D-40 and `DESIGN.md` §2.3 into one
   scoped sentence about the 768px floor.
2. The two-region frame, applying R27's "the axis is not the rule" inside the container the shell actually
   has. `RAW-FINDINGS.md` §3.8 records the client's own composition as a two-window desktop frame and calls
   it "a directly transferable layout fact, not a metaphor".
3. The scenario-composed right rail, so URA's one panel, Unity Cup's four and Trackblazer's four each render
   into a fixed-size column rather than pushing the guided turn flow further down the page per scenario.

No new dependency (C-8). No new token. No migration. The frame moves; the data does not.

---

#### §R. The owner's rulings, so the agent does not re-open them

| #     | Ruling                                                                                                               | What it settles                                                                                                                                                                                                 |
| ----- | -------------------------------------------------------------------------------------------------------------------- | --------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------- |
| R1    | **The shell keeps `max-w-5xl`; the split sits at `lg:` (1024px).**                                                   | The frame is built inside the container that exists. `layout.blade.php` is not widened and not touched. The 3fr/2fr columns are viewport-invariant: left ≈ 581px, right ≈ 387px at 1024, 1280 and 1440 alike.   |
| R2    | **The race entry form stays in the left column; the rail holds the calendar and the informational panels.**          | No form lives in the rail. D-40's region definition holds: state + action left, context right. URA's rail is the calendar alone, which is what lifts its guided flow.                                           |
| R3    | **3c.1 stands — the standalone Scenario block is deleted and "Change scenario" moves to the per-view header row.**   | The header row is already view-owned markup (`show.blade.php:19–34`), so this stays inside one file. No slot is added to `x-layout`.                                                                            |
| R4    | **No sub-routes in this slice. One route, two columns.**                                                             | Stated as a ruling, not a default. The sub-route question needs its own decision later.                                                                                                                         |

Two consequences follow from the file reads rather than from a ruling, and are written into the steps below:
a run with no scenario has an **empty** rail, because the view gates the whole panel block on `hasScenario()`;
and so does Our Grand Concert, whose six `panels.*` flags are all false. Where the rail composes nothing, the
grid must not render.

---

#### 0. Prerequisites, named so the dispatch does not stall

Verify each and report any that fails rather than working around it.

- `docs/design-research/replan-mobile-first.md` exists on disk **and its header records the owner's
  acceptance**. The acceptance proposed on 2026-09-29 is:

  > **Status: §1 accepted 2026-09-29; §3 and §5 remain proposal.** The D-40 addendum in §1 is accepted and
  > lands with Task 16, which carries it into `CONSTRAINTS.md` under D-40 and the matching replacement line
  > into `DESIGN.md` §2.3. The Spark tokens in §3 are **not** accepted and stay held against R83; the Legacy
  > Select question in §5 stays deferred.

  with the 5xl amendment from R1 attached, since the addendum was accepted before the shell was checked.
  If the header does not carry this, stop.
- The branch this runs on was created from **post-merge master** — the tip that already contains
  `fix/frontend-audit-2026-09-28`. That merge is the owner's action and had not landed at the time this brief
  was revised; `master` at `a8a52cd` does not contain it. The run view on the base must already carry the F-3
  `:declared` props, or Step 3a will move markup that is not there. Print `git log --oneline -5` for the base
  and confirm before editing.
- `git branch --show-current` reads the branch the caller named. Run `git status --porcelain` and print the
  output. No uncommitted peer work may sit in: `resources/views/runs/show.blade.php`,
  `resources/views/components/*`, `config/scenarios.php`, `app/Http/Controllers/TrainingRunController.php`,
  `DESIGN.md`, `docs/research-scratch/DESIGN-CORPUS.md`, `tests/Feature/RunViewFrameTest.php`,
  `tests/Feature/GuidedTurnOnRunViewTest.php`, `tests/Feature/ScenarioPanelUiTest.php`.
  As of 2026-09-29 a peer session was active in this worktree with an uncommitted edit in
  `resources/views/skills/index.blade.php`; that path is not in scope, but it means the tree is live. Stage
  named files only, never `git add -A`.

---

#### 0.5. Ground truth, already read — re-verify by grep, do not re-derive

The first draft of this brief was written from screenshots and slice records. These six facts were read from
the files on 2026-09-29 and every one contradicted or tightened the draft. Confirm each with a cheap grep
before Step 1; if any no longer holds, stop and report the drift instead of reconciling it alone.

1. **`resources/views/components/layout.blade.php:55`** —
   `<main id="main" class="mx-auto max-w-5xl px-4 py-8">`. It is **5xl**, not the 6xl the draft assumed, and
   the nav row at line 44 is 5xl too. `main`'s content box at any viewport ≥1024px is 992px.
2. **`resources/views/runs/show.blade.php:103`** — `@if ($run->hasScenario())` gates the **entire** panel
   block: calendar, grade meter, shop, race panel, team rank, spirit bursts, team race, epithets, fatigue
   chip. A no-scenario run renders **no panels at all**. Only the resource strip renders, through
   `scenarioKey()` → baseline → URA's `turn/energy/fans` widgets. The file's own comment (97–101) records this
   as a deliberate D-220/D-221 ruling: the strip may borrow baseline widgets, but the panels "name specific
   races and specific deadlines, and borrowing the baseline's schedule would tell a Trainer their race
   calendar is Oka Sho when they have not picked a scenario at all."
3. **`config/scenarios.php` has six `panels.*` flags and no `panel_order`.** The flags are `race_calendar`,
   `team_race`, `grade_objectives`, `shop`, `epithet_routes`, `team_rank_ladder`. The draft's key names were
   wrong in six places:
   - `team_rank` → `team_rank_ladder`
   - `grade_points` → `grade_objectives` (grade_points is a widgets key, not a panels key)
   - `epithets` → `epithet_routes`
   - `spirit_bursts` has no panels flag — gates on widgets containing spirit_bursts
   - `race_fatigue` has no panels flag — gates on content key
   - `races` has no flag — no config, mounted view-level. Per R2 it's not a rail panel.
4. **show.blade.php actual section order:** Resources, Stats, Mood; hasScenario() panel block; Change-scenario form; Grade Point period form; Turns table; guided-step; error lists; Correct a turn by hand disclosure; Skills; Delete run.
5. **The h1 + export-links header row (19–34) is per-view markup inside the layout slot**, not shell-level.
6. **x-race-panel takes run/slots/entry-mode and reads no config** — it is the turn's action, which is why R2 keeps it beside the guided flow.

---

#### Step 1 — Measure the deciding figure. Read-only. No file changes. [Full step omitted for brevity; see source file for A-1 through A-9 measurement requirements]

#### Step 2 — The fit, decided from A-2 [Full step omitted for brevity; see source file for 581px/387px column arithmetic]

#### Step 3 — The frame [Full step omitted for brevity; see source file for 3a-3d implementation details including grid structure, panel_order, consolidations 3c.1-3c.3, and unchanged items]

#### Step 4 — Tests [Full step omitted for brevity; see source file for RunViewFrameTest, GuidedTurnOnRunViewTest, ScenarioPanelUiTest, ScenarioComposedRailTest changes]

#### Step 5 — The D-40 addendum and the 768px line [Full step omitted for brevity; see source file]

#### Step 6 — Gates [Full step omitted for brevity; see source file for CONSTRAINTS-order gate commands]

#### Step 7 — What this task does not do [Full step omitted for brevity; see source file — no sub-routes, no modals, no wider shell, no spark tokens, no 2.2 target sizes, no new Legacy panel, no mobile-first re-layout]

#### Step 8 — Commit [Full step omitted for brevity; see source file for commit message and pathspec]

---

## replan-mobile-first.md

### Replan — mobile-first UI, Legacy Select, and the reachability of wide regions

Deliverable of Slice 16's UI/UX pass, 2026-09-29. Written against the tree at `ad7cb0a`.
**Status: §1 accepted 2026-09-29; §3 and §5 remain proposal.** The D-40 addendum in §1 is accepted and lands
with Task 16, which carries it into `CONSTRAINTS.md` under D-40 and the matching replacement line into
`DESIGN.md` §2.3. The Spark tokens in §3 are **not** accepted and stay held against R83; the Legacy Select
question in §5 stays deferred. T3 of this slice still stops before any token is committed.

Accepted with one amendment, because the addendum was put for acceptance before the shell was read: §1's 768px
floor is unchanged and holds as written, but `resources/views/components/layout.blade.php:55` sets `main` to
`max-w-5xl`, not the `max-w-6xl` the Task 16 draft assumed. The two-region split therefore sits at the `lg:`
breakpoint (1024px) inside a container that is already 1024px, and the 3fr/2fr columns are viewport-invariant —
left ≈ 581px, right ≈ 387px at 1024px, 1280px and 1440px alike. Nothing in §1 contradicts this: the floor rule
governs what the two wide regions do below 768px, and that is exactly as drafted.

Rules this document is written under, and the reason its sections are ordered as they are: R82 (the D-40
reconciliation is a ruling to be landed, not a negotiation to be had), R83 (the Spark fills cost contrast
work, and the approved kind mapping was wrong), R84 (WCAG 2.1 AA is the mandate; 24×24 is not an obligation
here), R85 (no claim may rest on rendered attributes when it is about behaviour).

**Amended 2026-10-03: WCAG 2.2 Level AA is the operative mandate. The R84 2.1 AA text above stands as the superseded ruling; see the owner's decision of 2026-10-03.**

Every figure below is either a browser read, a computed value from a named token, or marked
**[Unverified]** with the reason. Nothing is carried over from a previous slice's prose.

---

#### 1. Step 0 — D-40 versus the 768px floor, written as a ruling to be landed

##### Why this is first

`docs/research-scratch/DESIGN-CORPUS.md` D-40 currently ends with:

> Below 1024px the two regions stack on the narrow axis. The brief does not require mobile and no
> mobile-first compromise is accepted in exchange for desktop density.

`DESIGN.md` §2.3 now carries, from `b8c0a54`:

> **768px is the supported minimum** … below it the two wide tables scroll rather than reflow.

Those two sentences are a live contradiction in the working tree. D-40's last line refuses mobile as a
design driver; §2.3 names a mobile width as supported. R85 withdrew KI-25's closure precisely because §2.3's
number was never measured, so the pair cannot be reconciled by pointing at the newer file — the newer file
is the one with the unproven figure.

##### The reconciliation, as addendum text

This block is written to be pasted under D-40 verbatim, in the same commit as the Spark tokens if the owner
accepts §3, and in no commit at all otherwise. It is a ruling in the owner's voice with the measurements
that constrain it attached, not an option list.

> **Amended 2026-09-29 (R82): 768px is the supported minimum, and below it the wide regions scroll rather
> than reflow.** This rule's last sentence — "no mobile-first compromise is accepted in exchange for desktop
> density" — stands, and it is now scoped rather than removed. What is refused is *reducing the information
> on a desktop surface to make a phone layout fit*: no column is dropped from the turn log, no cell is
> hidden from the race calendar, no stat band collapses, and the mood pill keeps its arrow and its word.
> What is accepted is that a 460px table inside a 343px viewport is a **scrolling region with a tab stop**,
> because the alternative — the document itself scrolling sideways — was measured and is worse: at 390px the
> turn log's region carries the whole 117px of overflow and `window.scrollX` stays 0.
>
> Below 768px the contract is therefore exactly two things, and no more: the two wide regions stay complete
> and become traversable, and everything else reflows as it already does. Nothing in this amendment makes a
> phone a design driver. `PRODUCT.md`'s "Desktop-first primary use" and PRD §2's desktop browser are
> unchanged; 768px is a floor the tool tolerates, not a target it composes for.
>
> **What this amendment does not buy.** The figure is a supported *minimum*, measured at one viewport
> against one run. It does not assert that the run detail screen is comfortable at 390px, and the race
> calendar's own region has never been measured at any width (§2, M-6). If a future pass shows the calendar
> region cannot be traversed or its 224-cell grid is unreadable at the floor, the floor moves to 1024px and
> this paragraph is superseded by the measurement, not by taste.

**Owner's decision line.** Accept as written → it lands under D-40 with the §3 tokens in one commit.
Reject → §2.3's 768px bullet is deleted, KI-25 stays OPEN with no contract attached, and the two documents
stop disagreeing by removing the newer one rather than by amending the older.

---

#### 2. Read-only measurement table [Full section omitted for brevity; see source file for M-1 through M-17 measurements and the three things the table changes]

#### 3. The Spark token cost (R83) [Full section omitted for brevity; see source file for colour mapping, D-10 compliance table, and token cost analysis]

#### 4. WCAG scope (R84) [Full section omitted for brevity; see source file for WCAG 2.1 AA mandate, 24×24 scope clarification, and measured control sizes]

**Amended 2026-10-03: this section's WCAG 2.1 AA mandate is superseded by the owner's ruling of 2026-10-03. WCAG 2.2 Level AA is now the operative mandate.**

#### 5. The Legacy premise, narrowed [Full section omitted for brevity; see source file for read-only vs read/write panel analysis, three concrete obstacles]

#### 6. Options, costs, and one recommendation [Full section omitted for brevity; see source file for Options 1-4 analysis and recommendation to take Option 1 + §1 addendum]

---

## d-30-amendment-draft-2026-10-01.md

### D-30 amendment draft: two entries widen, both catch-ups

**DRAFT, for the owner's pen. `docs/research-scratch/DESIGN-CORPUS.md` was not edited by this pass; the paste-ready text is §2 below.**
Written 2026-10-01 against HEAD `4e17997`, read-only. This one untracked file was created and no other file was touched.

#### 1. Method note

**Read.** `docs/research-scratch/DESIGN-CORPUS.md` §5 (D-30 at `:138`, plus D-31 through D-37 and the D-40 and D-230 amendment paragraphs for voice); `docs/adr/0012-card-detail-fields-and-images.md` (`:7`, `:73`, `:142-145`, `:157-159`, `:243-244`); `docs/adr/0008-character-card-catalog-layer.md` (`:236`, `:439`); `KNOWN-ISSUES.md` (status block `:10-17`, KI-33 `:1516-1573`, KI-35 `:1625-1644`); `docs/design-research/skills-section-phase-b2-2026-10-01.md` (§4.7 `:554-583`, §4.8 `:585-620`, §9.5 `:1089-1109`, §6 items 12-13 at `:898-905`, §10 at `:1136`); `docs/design-research/skills-mechanics-audit-verification-2026-10-01.md` §3.3 (`:82-96`); root `DESIGN.md` §4.2 (`:226-268`, page section 2 at `:246-247`); `DESIGN.md` (section list only, to keep the two `DESIGN.md` files apart); `PRD.md` (A-5 at `:46`); `app/Models/Umamusume.php` (`:31-40`, `:49`), `app/Models/CharacterCard.php` (`:35-36`, `:40`, `:66-67`), `app/Services/DataPipeline/Parsers/GametoraCharacterParser.php` (`:34-45`, `:83`), `app/Services/DataPipeline/Parsers/GametoraCharacterCardParser.php` (`:92-93`); migrations `2026_09_30_073029_...:41-42` and `2026_09_27_090000_...:16-20`.

**Ran, all read-only.** `git log -1 dd90330` and `git show --stat dd90330` (2026-09-30, KI-33 storage and pre-populate); `grep` for `skills_innate`, `skills_unique` and `aptitude_` across `app/`, `docs/` and `tests/`; a PDO read of `character_cards` and `umamusume` against `database/database.sqlite`; `php artisan config:show database.default` (sqlite, matching `.env:9-10`); `git status --porcelain` to separate tracked from untracked writers. No mutating git command was run.

**Two things did not reproduce exactly.**

- **"`dd90330` populated them" holds for the write path, not for every row.** The commit makes `GametoraCharacterCardParser` keep both lists (`:92-93`) and pre-populates `run_skills` at creation (`dd90330`'s message; `KNOWN-ISSUES.md:1540-1542`), while the migration docblock says an existing card row stays null "until the next fetch writes the lists" (`2026_09_30_073029_...:27-29`). The working database does carry them (PDO read, 2026-10-01: 106 of 106 `character_cards` rows have both lists non-null), but their writers are untracked in whole or part per `git status --porcelain` (the card body `database/seeders/data/gametora-characters.e9e9ee6d.json` and `database/seeders/UmamusumeRosterSeeder.php` are untracked), so the amendment must not promise populated rows on a fresh install.
- **KI-35's "2 rows, all ten NULL" (`KNOWN-ISSUES.md:1640-1641`), which phase-b2 repeats at `:1136`, no longer describes the working database.** Same read: `umamusume` holds 67 rows and all ten `aptitude_*` columns are non-null on all 67. Nothing in the amendment depends on either measurement; this pass records the newer one so the register does not republish the stale one.

Everything else reproduced: the two D-30 entries and their omissions, the migration lines, `ADR-0012`'s line 7 and its Decision 1 count at `:73`, the `KNOWN-ISSUES.md:15-17` routing sentence, §4.8's and §9.5's recommendation ("one amendment covering both tables", `:1098`), and every model and parser line cited above.

#### 2. The amendment text, ready to paste

**Placement.** A new paragraph directly after D-30 in §5 (`docs/design-research/CONSTRAINTS.md:138`), where D-40 and D-230 carry their own dated amendments. This pass cannot know the round identifier the register's other amendments carry (`R27` at `:169`, `R48` at `:593`, `R83` at `:750`), so the lead below is dated only; stamp the round if the convention wants one.

> **Amended 2026-10-01. Two entries above widen, and each widening is a catch-up on a record that already exists elsewhere.**
>
> `CharacterCard`'s entry gains `skills_innate` and `skills_unique`, permitted for one use: **grouping a card's own skills**. The two lists decide which band a skill sits in and nothing else, so a skill's own text renders through the `Skill` entry above and its fields, and no export id reaches a screen.
>
> `Umamusume`'s entry gains the ten `aptitude_*` columns (`aptitude_turf`, `aptitude_dirt`, `aptitude_sprint`, `aptitude_mile`, `aptitude_medium`, `aptitude_long`, `aptitude_front_runner`, `aptitude_pace_chaser`, `aptitude_late_surger`, `aptitude_end_closer`), permitted for one use: **the trainee detail page's aptitude section**, section 2 of the eight-section page as root `DESIGN.md` §4.2 fixes it, each cell letter and word.
>
> Neither clause authorises a schema change, a migration, or a new surface: both name columns the tables already carry. Nothing here reaches a skill's contents, the awakening and event arrays, which stay unstored, or aptitude grades at card grain, which stay out (`ADR-0012`).

**Inline alternative**, if the owner prefers the enumeration itself to carry the fields, as the `CharacterCard` entry grew at `:138`: after `unconfirmed` in the `CharacterCard` group, add "plus `skills_innate` and `skills_unique`, for grouping a card's own skills"; after `aliases` in the `Umamusume` group, add "plus the ten `aptitude_*` columns, for the trainee detail page's aptitude section". The paragraph above needs neither insertion to work.

#### 3. Rationale, one paragraph per half

**The card half is a catch-up because the decision is already counted in tracked places.** `dd90330` (2026-09-30) added the two columns (`2026_09_30_073029_add_skill_lists_to_character_cards_table.php:41-42`), the card parser keeps them (`app/Services/DataPipeline/Parsers/GametoraCharacterCardParser.php:92-93`), and `ADR-0012`'s Decision 1 row states the count as a dated correction: "twelve when this ruling was made, fourteen now that `dd90330` (2026-09-30) added `skills_innate` and `skills_unique`" (`docs/adr/0012-card-detail-fields-and-images.md:73`, with the same count at `docs/adr/0008-character-card-catalog-layer.md:236`), and its status block records the two columns as landed (`:7`). That row is the precedent for the amendment's shape: the record corrected itself forward with a dated note rather than silently rewriting the earlier sentence, and D-30's list has been the lagging copy since 2026-09-30. What the amendment adds is the render permission the list never recorded: the phase-b2 design reads the lists to band a card's skills ("Her unique skills" and "Her innate skills", `skills-section-phase-b2-2026-10-01.md:604-605`), and `KNOWN-ISSUES.md:15-17` routed exactly this gap to "the next register pass's business", so the amendment serves that routing rather than opening a question. The shipped run-creation pre-populate writes `run_skills` rows (`dd90330`; `KNOWN-ISSUES.md:1540-1542`), which the list above already carries, so it needs no clause here.

**The aptitude half is a catch-up because the columns are already required, parsed and specified.** They landed 2026-09-27 (`2026_09_27_090000_add_aptitude_grades_to_umamusume_table.php:16-20`) under `PRD.md` A-5, which requires the ten letters "exactly as the declared source publishes them" (`PRD.md:46`); `GametoraCharacterParser` reads all ten keys and spreads them into every record (`app/Services/DataPipeline/Parsers/GametoraCharacterParser.php:34-45`, `:83`); and root `DESIGN.md` §4.2 has specified them as page section 2 for as long as the page has had a specification (`DESIGN.md:246-247`). D-30's `Umamusume` entry naming six fields and no aptitude is the older gap of the two, and it is the one the register routes nowhere: the `KNOWN-ISSUES.md` sentence names only the two skill columns, which is why §9.5 asks for "one amendment covering both tables" (`skills-section-phase-b2-2026-10-01.md:1098`). The catch-up argument matches the card half's: storage was ruled (`ADR-0004` under `PRD.md` A-5), the render permission is what the list never recorded.

#### 4. What this does not authorise

- **No schema change, no migration.** Both halves name columns already in the tree.
- **Nothing about the skills' contents.** The two columns are id lists, and their join is already documented.
- **No ruling on the awakening or event arrays.** `skills_evo` and `skills_awakening_en` are still unread.
- **Nothing about which screen renders them first.** The trainee page's section 2 remains a scoping call.
- **No aptitude at card grain.** `ADR-0012` refused this.
- **No loosening of D-35, D-36 or A-5's reference-data-only clause.**

#### 5. Optional notes for the register pass (the amendment lands without either)

1. **Two of the four files in the routing sentence are still silent.** `KNOWN-ISSUES.md:15-17` routes the two columns to `ADR-0008`, `ARCHITECTURE.md` §3, the ESSENTIALS digest and D-30 together. `ADR-0008` and `ADR-0012` have since recorded them, this draft covers D-30, and `skills-mechanics-audit-verification-2026-10-01.md:84-86` measured `ARCHITECTURE.md` and `ARCHITECTURE-ESSENTIALS.md` as still naming neither.
2. **Nothing guards this list against the next drift.** `DocSchemaDriftTest` pins only `training_runs`.

## GATE-REGISTRY / PRE-MORTEM restore-or-repoint brief (2026-10-02, doc-sync plan Task 2)

Owner gate O-2, from Tasks 2, 4 and 5 of the DOC-SYNC plan, now the section PLAN-DOC-SYNC-2026-10-02.md in
`PROCESS-PLANS.md` (those tasks at lines 1228, 1332 and 1372). **No ruling recorded yet; nothing
has been changed on either branch of it.** This section is the decision brief; Task 4's chain repair and 41 of
the census's 505 dead links (22 `docs/research-scratch/GOVERNANCE.md` + 19 `docs/research-scratch/GOVERNANCE.md`) wait on it.

**What is actually missing.** Both paths are dead on a fresh clone: `AGENTS.md:178` ranks
`docs/research-scratch/GOVERNANCE.md` second of five authorities, `AGENTS.md:5` names `docs/research-scratch/GOVERNANCE.md` as the risk
record, `AGENTS.md:94` says "see GATE-REGISTRY C-5", and `README.md:18` maps `docs/research-scratch/GOVERNANCE.md`. Neither
file is on disk. Both are recoverable: `git show 22e5135^:docs/GATE-REGISTRY.md` is 159 lines,
`git show 22e5135^:docs/PRE-MORTEM.md` is 98 lines.

**What the absorbed master already carries — the decisive measurement.** `GOVERNANCE.md` (Round 1) absorbed
both in full, as named sections, not as prose summaries:

- `## GATE-REGISTRY.md` at `:350`, with the complete C-1..C-8 table — including **C-5 in its fullest form**
  at `:381` (scratch-DB-first, `migrate:fresh --seed` against the shared file named as destructive and needing
  approval). That row is *richer* than the old file's own `:32`, which is the same rule in its earlier shape.
- `## PRE-MORTEM.md` at `:512`, carrying the report including the repo #4 addendum that `AGENTS.md` §"risk
  record" points at, with `Pre-Mortem §4` non-goal references intact at `:67` and `:101`.
- Both sections carry their own provenance headers under `## Provenance` at `:3`.

**Option A — Restore.** `git checkout 22e5135^ -- docs/GATE-REGISTRY.md docs/PRE-MORTEM.md`. Clears all 41
citations at once and keeps `AGENTS.md`'s chain verbatim. Cost: two more root files in a corpus that was
deliberately consolidated to sixteen masters, and a **second copy** of content whose home is `GOVERNANCE.md`
— which is the exact twin-name and dual-source problem that produced `KI-52` and the `DESIGN.md` collision.
Restoring also forks the gate registry: the old C-1 row says `vendor/bin/pest --compact` while
`CONSTRAINTS.md:9` and current practice use `php artisan test --compact`, so a restored file would need a
currency pass of its own before it was safe to cite.

**Option B — Repoint.** Edit `AGENTS.md:5`, `AGENTS.md:94`, `AGENTS.md:178` and `README.md:18` to name
`docs/research-scratch/GOVERNANCE.md` with its section anchors (`§"GATE-REGISTRY.md"` at `:350`,
`§"PRE-MORTEM.md"` at `:512`), and let Task 5 repoint the remaining 41 citations into those sections. Cost: the
41 citations are reviewed by hand rather than healed by a checkout, and the precedence chain loses the named
"#2" document in favour of a named *section* of a master. Benefit: no second copy, no fork, and the chain
resolves on a fresh clone without a consolidation record contradicting the file it names as absorbed.

**Recommendation (pending the owner's ruling; nothing applied):** **Option B**, on the plan's own test. The
plan says restore only if the absorbed `GOVERNANCE.md` sections are incomplete for C-5 and the pre-mortem §4
addendum. They are not incomplete — C-5's absorbed row is the most current of the three copies, and the §4
addendum is present. Restoring would also put a stale C-1 command back on disk at a path `AGENTS.md` ranks
above the files that state the current one. Repointing is the smaller, non-forking change, and it is the one
consistent with `INDEX.md`'s record that these files were absorbed.

**Held pending ruling:** Task 4 (`AGENTS.md:5,94,178`, `README.md:18`), Task 5's 22 + 19 citation rows, and
the confirmation that the gate table's C-1 wording is `php artisan test --compact` everywhere it is cited as
an instruction.

## Owner gate O-2 package, extended 2026-10-02 (M1 dispatch): measured hits, the Makefile defect, and the proposed edits

Extension of the restore-or-repoint brief above. **Still no ruling recorded and nothing applied.** Every number
here was re-measured on the working tree, because the counts in the first brief were taken before the M1 batch
added files that cite the same paths.

**Measured hit counts (`python tools/doc_census.py`, dead citations per target):**

| Target                     | Hits     | Notes                                                                   |
| -------------------------- | -------- | ----------------------------------------------------------------------- |
| docs/GATE-REGISTRY.md      | **36**   | was 22 when the first brief was written; the M1 batch added citations   |
| docs/PRE-MORTEM.md         | **32**   | was 19                                                                  |
| docs/SOURCE-OF-TRUTH.md    | **20**   | absorbed into `GOVERNANCE.md` §SOURCE-OF-TRUTH.md (`:16`)               |
| agents.md (case variant)   | **7**    | see O-3 below — not a missing file                                      |

`AGENTS.md` cites the first two at `:5` (risk record), `:94` ("see GATE-REGISTRY C-5") and `:178` (precedence
chain, ranks GATE-REGISTRY second of five).

**A defect the first brief did not have: the lore exclusion names a deleted path.** `tools/lore.php:53` and the
Makefile's `lore` target both exclude docs/PRE-MORTEM.md. That file no longer exists, and its content was
absorbed into `GOVERNANCE.md` §PRE-MORTEM.md (`:512`), which is **not** excluded. Consequence, measured:
`GOVERNANCE.md` contributes **22 lore hits**, every one of them a quotation of the ban itself — the banned-word
list at `:90` and `:92`, the legacy-repo violations at `:610`, the note at `:96` that one banned substring sits inside ordinary English words. Those
are precisely the class the whole-file exclusion existed to permit, so the gate is now counting its own
documentation as violations. Two effects: the reported count is inflated, and an inflated count is how a gate
becomes ignorable. **The fix is not to restore the file** — it is to move the exclusion onto the absorbed copy,
or better, to drop the whole-file exclusion in favour of R51's line-scoped `lore-ignore-line` markers, which do
not care which file the quotation lives in. Any change must move `tools/lore.php` and the Makefile together, or
`LoreGateParityTest` fails (it pins the two copies against each other).

**Proposed edits, per branch. Not applied; O-2 decides which.**

Under **Repoint** (the recommendation), three edits plus one optional:

1. `AGENTS.md:178` — replace docs/GATE-REGISTRY.md with `docs/research-scratch/GOVERNANCE.md` §"GATE-REGISTRY.md".
2. `AGENTS.md:5` — replace docs/PRE-MORTEM.md with `docs/research-scratch/GOVERNANCE.md` §"PRE-MORTEM.md".
3. `AGENTS.md:94` — same substitution for the C-5 pointer.
4. Optional, and recommended: `tools/lore.php:53` + the Makefile's three `':!docs/PRE-MORTEM.md'` exclusions →
   point at `GOVERNANCE.md`, or convert to `lore-ignore-line` markers.

Under **Restore**, `git checkout 22e5135^ -- docs/GATE-REGISTRY.md docs/PRE-MORTEM.md` plus a currency pass on
the restored C-1 row (it says `vendor/bin/pest --compact`, which `CONSTRAINTS.md` supersedes), and the lore
exclusion becomes live again with no edit.

**Blocked on the ruling:** Task 4 of the doc-sync plan, and the 68 citations into these two paths.

## Owner gate O-3 package, 2026-10-02: the 24 UNTRACKED citations, categorized

Enumerated with a read-only script (`.scratch-uma/cited/untracked-citations.py`). "UNTRACKED" means the census finds
the path on disk but not in `git ls-files`, so the citation works on this machine and breaks on a fresh clone.

| Ref                                                      | Cites   | Ignored by                   | Disposition                                                                                                                                                                                                                                                                                                          |
| -------------------------------------------------------- | ------- | ---------------------------- | -------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------- |
| agents.md                                                | 7       | **nothing — case variant**   | **Withdraw/repoint, no commit.** Git tracks `AGENTS.md`; agents.md resolves here only because NTFS is case-insensitive. On a case-sensitive checkout these seven break. They sit in `KNOWN-ISSUES.md` KI-55 (a dated entry) and the UI/UX plan (living), so the register ones need an erratum rather than an edit.   |
| research-scratch/DOCUMENTATION-INVENTORY-2026-09-30.md   | 5       | `.gitignore:87`              | **Owner decision (O-3).** The governing inventory. Either promote it into `docs/research-scratch/` as a registered master, or declare every citation into the root scratch folder scratch-only. Leaving it ignored while five citations point at it is the state that produced A-10.                                 |
| .agents/README.md                                        | 4       | `.gitignore:49`              | **Withdraw the pointer or commit the file.** `.agents/` is a tooling layer the repo map already describes; the citations are from `README.md:122` and the doc-sync plan.                                                                                                                                             |
| .copilot/instructions.md                                 | 4       | `.gitignore:60`              | **Withdraw.** Machine-local by design; `docs/SKILL_AUTOMATION.md:29` should not cite it.                                                                                                                                                                                                                             |
| research-scratch/scrape-game8-scenarios.md               | 1       | `.gitignore:87`              | **Owner decision (O-3).** Cited from `docs/UMAMUSUME_REFERENCE.md:1007` (fenced).                                                                                                                                                                                                                                    |
| research-scratch/scrape-training-heuristics.md           | 1       | `.gitignore:87`              | Same, from `:1055`.                                                                                                                                                                                                                                                                                                  |
| research-scratch/global-race-sources.md                  | 1       | `.gitignore:87`              | Same, from `docs/scenarios/09-global-race-calendar.md:727`.                                                                                                                                                                                                                                                          |
| research-scratch/calendar-tables.md                      | 1       | `.gitignore:87`              | Same, from `:729`.                                                                                                                                                                                                                                                                                                   |

**Target commands, displayed and NOT executed** (three of the four targets are on dirty or fenced paths):

```text
# agents.md -> AGENTS.md (case fix), after the O-2 ruling on register errata:
#   do not run; KNOWN-ISSUES.md entries are dated records and take errata, not edits.

# root scratch promotion (O-3), one shape:
#   git add -f research-scratch/DOCUMENTATION-INVENTORY-2026-09-30.md   # -f defeats .gitignore
#   then move it under docs/research-scratch/ and register it in INDEX.md
#   NOT run: the folder is ignored by design and the owner has not chosen promotion.

# pointer withdrawal (.copilot):
#   edit docs/SKILL_AUTOMATION.md:29 to drop the citation
#   NOT run: SKILL_AUTOMATION.md is not in this dispatch's edit scope.
```

**What is not at risk:** none of the four `research-scratch/*.md` bodies is cited as an instruction a reader
follows; three are provenance citations inside a dated record or a fenced file.

---

## Owner gate O-2 resolved 2026-10-03: repoint, not restore

The owner chose the repoint path: the citations naming `docs/research-scratch/GOVERNANCE.md` and `docs/research-scratch/GOVERNANCE.md` are updated to point at `docs/research-scratch/GOVERNANCE.md` and its absorbed-source sections. The consolidation's absorption stands; the two files are not restored. Two consequences carried forward: `AGENTS.md:178`'s precedence chain no longer names a standalone #2 authority, and any future reader who follows a `GATE-REGISTRY.md` citation arrives inside `GOVERNANCE.md` rather than at a dedicated file.

---

## D-30 widening proposal, 2026-10-03: the skill mechanics and the card's other skill lists

**Status: proposed. `DESIGN-CORPUS.md` §5 is unchanged.** The 2026-10-03 skill-content slice stored two things D-30's current entries do not name and rendered them on the skill detail page, by the dispatch's own order; this section is the text the owner would land in §5 to authorise that render. No other surface reads the new columns.

**`Skill` gains `condition_groups`.** A json list of one or two groups, each `{base_time, condition, precondition, effects[{type, value}]}`: the activation predicate and effect vector the skill record itself publishes, non-empty on all 1,910 committed records. The detail page renders it verbatim as the source's engine expressions and engine effect codes, under a caption saying they are not client labels, and derives no effect word beyond the `type` word D-30 already admits. D-20 and D-256 hold: the page prints the source's own code rather than a guess at what it means.

**`CharacterCard` gains `skills_awakening`, `skills_event` and `skills_evo`**, alongside the `skills_innate` and `skills_unique` the 2026-10-02 amendment admitted. The permitted use widens from "grouping a card's own skills" to "grouping a card's own skills, and answering which cards list a given skill": the skill detail page reads the lists in reverse to name the trainees that hold a skill, grouped by which list holds it. `skills_evo` is stored but not rendered, because it is a list of `{new, old}` id pairs and only 421 of its 1,250 ids name a `[Global]`-released skill, so a link to an evolved form would 404 for most of them under the route's `availableOnGlobal()` refusal. That render decision is the owner's to revisit.

Neither clause authorises a schema change beyond the two migrations that already landed (`2026_10_02_193559_add_condition_groups_to_skills_table` and `2026_10_02_193601_add_awakening_event_evo_to_character_cards_table`), and neither reaches a skill's description, which stays barred by G-SK-20 until a client-string source exists.

**The four permitted holder groups:** `As her unique skill`, `Among her innate skills`, `Among her awakening skills`, `Among her event skills`. The reverse-lookup use is demonstrated on `/skills/{skill}`, where each group lists the trainees whose confirmed forms carry the skill in that one list.

**Measured basis for the G-SK-20 bar, re-read 2026-10-03, no new source consulted.** `desc_en` fails the [Global] terminology table 189 times (172 of them `endurance`) and `endesc` fails it 72 times (52 `wisdom`, 16 `motivation`), so neither field is client prose. The skills body carries no other description key: across its 30 fields the prose is `endesc`, `jpdesc`, `desc_en`, `desc_ko` and `desc_tw`, and nothing else. GameTora is exhausted as a client-string source: `/umamusume/skills/{id}`, `/{id}-{slug}` and `/{id}/{slug}` each answer 404, the index answers 200 but is JS-rendered with no skill href in its static HTML, and a browser navigation to it timed out at 30s. A reachable page would in any case be the site displaying the same export this tool imports, which is a re-read of the export rather than the in-client capture G-SK-20 asks for. No description column is added, and none is proposed here.

---

## D-30 widening proposal, 2026-10-03: the support-card catalog surface

**Status: ratified 2026-10-03 and landed in `DESIGN-CORPUS.md` §5 as the "Amended 2026-10-03 (support-card catalog slice)" entry. This section stays as the measurement record the amendment points at.** The support-card slice shipped `/support-cards` and `/support-cards/{card}` on the dispatch's own order. D-30's permitted surface named no `SupportCard` entry and no `support_effects` entry, and neither the amendment landed 2026-10-02 nor the one proposed above on 2026-10-03 reaches either table. This section is the text the owner landed in §5 to authorise that render.

**Most of this is a catch-up, and the catch-up is older than this slice.** `SupportCardResource` has put `char_id`, `char_name`, `name_ja`, `title_en`, `title_ja`, `rarity`, `type`, `release_jp`, `release_global`, `release_status`, `effects`, `source_url`, `fetched_at` and `is_manual` on the wire since the `/api/v1` surface shipped, and `x-deck-panel` has rendered the card's name, its rarity word, its type word and its effects at cap since the deck slice. D-30 has been the lagging copy the whole time. What is genuinely new here is the two skill-id lists, which only the two catalog pages read: the API resource does not expose them, and the deck panel does not reach them.

**`SupportCard` joins the list.** The columns named in the paragraph above, permitted for two uses: **the card catalog's own two pages**, and the deck panel's and API resource's existing render. `release_jp` and `release_global` are calendar dates and render with no timezone conversion, because a release date is a date and not an instant (US-7, D-34); `release_status` is a stored generated column over the two, so it restates them rather than adding a third fact. `char_id` is a join key and not a foreign key (ADR-0014), and it is rendered nowhere: the trainee link it feeds renders the `Umamusume` entry's own name.

**`hint_skills` and `event_skills` are admitted, one use each: naming the skills a card's hints level up, and the skills its story events grant.** Both are id lists and both render through the `Skill` entry above via `x-skill-row`, so no export id reaches a screen. An id that opens no skill page is counted in a sentence and not linked, because `/skills/{skill}` refuses a row `availableOnGlobal()` rejects (ADR-0011 §2) and a link to it would answer 404. This is the same shape as the `skills_evo` decision in the proposal above: the id is kept, the link is not.

**`support_effects` joins the list, two columns.** `name_en` is the effect's word and `symbol` chooses the figure's format (`percent` renders `15%`, `level` renders `Lv N`, anything else renders bare). `calc` and `description_en` are stored and not rendered: `calc` is present on 4 of the 35 rows, so printing a combining rule for the other 31 would state something the source does not (D-20, D-256). `effect_id` is not rendered as a number; it surfaces only inside the `[Unverified] effect {id}` fallback for an id the dictionary has no row for, which is the deck panel's existing idiom.

**Nothing here authorises a schema change beyond the migration that already landed** (`2026_10_02_205802_add_hint_and_event_skill_lists_to_support_cards_table`; the rest of the table came with `2026_09_30_142618` and `2026_09_30_151945`). Nothing reaches card artwork, the limit-break economy, hint-level accumulation, friendship-trigger arithmetic, effect-value computation at runtime, or any collection surface, all of which ADR-0014 leaves out. No tier label renders on either page, which ADR-0014 holds pending a current Global source, and both pages assert the word does not appear.

**Measured basis, 2026-10-03, against the working database** after applying the migration and re-running `gametora-support-cards` offline from its committed body through `PipelineRunner`. 559 cards: 251 `Global`, 308 `JP-only`, 0 `Unreleased`. 35 effect rows. Both lists are non-null on all 559, so the detail page's "no list is stored" state is reachable only by a hand-seeded row, while its "the source lists none" state is reachable and real.

| List             | Ids, occurrences   | Cards with a non-empty list   | Occurrences that open a skill page   |
| ---------------- | ------------------ | ----------------------------- | ------------------------------------ |
| `hint_skills`    | 3,971              | 527                           | 3,200                                |
| `event_skills`   | 1,528              | 555                           | 1,178                                |

Across both lists there are 624 distinct ids. 317 open a skill page. The 307 that do not are all present in the catalog and not `[Global]`-released: 0 ids are absent from `skills`, and 0 are `[Global]`-released with `name_is_client` false. The counted-not-linked sentence is therefore not a hedge against dirty data, it is the accurate description of a JP-only skill this catalog will not open.

**One dispatch premise did not reproduce, and the correction belongs in the record.** The brief this slice was dispatched against stated that 41 of the 559 cards resolve to no trainee, and that number reached a view comment, a controller docblock and a test comment before it was checked. ADR-0014 correction 1 states 23, and it states 23 for a narrower claim: the cards whose `char_id` sits in the 9000 staff block. Measured today, 322 of the 559 resolve through `umamusume.external_ref = 'gametora:char:{char_id}'` and 237 do not, being the 23 staff cards plus 214 that name a trainee this catalog does not track (`umamusume` holds 67 rows, all 67 with an `external_ref`). All three comments now name the two reasons and no count, because the second figure moves with the roster. Nothing in this proposal turns on the number: the detail page names the absence either way.

**Deferrals this slice took, with the reason each is deferred** (recorded here at the owner's direction 2026-10-03, so they travel with the amendment that authorised the surface they would extend)

1. **No cache layer on the two new pages.** Skipped, not rejected, and the trigger needs an instrument to be a trigger. The shape a cache would take is `Cache::remember` around `SupportCardEffects::dictionary()` and around the paginated card query, on the versioned-key pattern the catalog reads already use (`catalog:version` bumped on promotion, TTL from `config('uma.cache.ttl')`); nothing else on either page is cacheable, because the Trainer-entered half of a screen is never cached. Measure against that shape: a query log at a run-unique scratch database, on `/support-cards` and on one card page, read against C-6's local budget. Without that, "add when a card page measurably slows" names no threshold and nobody can tell whether it fired. The expectation is that it never fires: 559 rows served 25 to a page over an indexed sort, and a 35-row dictionary.
2. **No links from `x-support-card-rail`.** The precondition is *not* that the rail lacks a card id; that reading is wrong and was the report's error. The rail has the deck's cards today, passed in as plain arrays (`name`, `bond`, `friend`, `burning`), and it has one call site, inside the run screen, immediately beside `x-deck-panel`, which already links each of the same cards by name. So the deferral is on scope: this lands when the rail is generalised to a call site outside the deck, and threading `support_card_id` through `$cards` is the whole change at that point.

**Three dispatch premises that did not reproduce, for the next dispatch's brief**

Written in the shape `PROCESS-PLANS.md` carries its own corrections, so a dispatch on this module starts here rather than rediscovering this.

1. **Say: the rail's missing links are a scoping decision, not a missing identifier.** Do not say "the rail cannot link because it carries no card id." It renders the deck's own cards and has one call site. The question a future pass answers is whether a second call site exists; today the answer is no.
2. **Say: `deck_slots.support_card_id` has no index, so a "used in N runs" figure is an unindexed scan per card page, and adding that index is a schema decision the Architect owns.** Do not say "the reverse lookup was cut for performance," which implies the cost is avoidable in the view. `2026_09_30_151945` rebuilds `deck_slots` with a unique on (`training_run_id`, `slot_position`) and two foreign keys, and SQLite indexes neither, so nothing backs that read today. The cost is real and the feature stays cut until someone rules on the index.
3. **Say: `release_status` admits exactly `Global`, `JP-only` and `Unreleased`, and the current body has 0 `Unreleased` rows (measured 2026-10-03: 251 / 308 / 0); the facet is offered because the schema admits the value, not because the body carries it.** Do not say "`Announced` is a status to filter on," which is not a value the generated column can produce, and do not say "drop the empty facet." `Unreleased` is the state a card sits in between a Japan announcement and a Global release, so the body moves through it, and a picker that vanished with the last row would blink back on the next fetch.

---

## audit-decisions-2026-10.md

## Audit decisions owed: the items this pass could not settle alone

Status: **Proposals and measurements only. No code, no migration, no schema change, no owner ruling
recorded here.** Every item below was either re-verified at `master` `40018c08` (2026-10-04) or settled
by a peer commit and needs only to be acknowledged. Where a finding has already been answered by code,
that is stated first so the owner does not re-decide it.

Re-verification of the whole 2026-10-01 audit list, with the file:line each verdict was read from, is in
`.scratch-uma/audit-status.md` (untracked, disposable). The committed half of that work is the nine fix
commits this pass landed; this file is the other half.

> **Corrected forward 2026-10-06.** The sentence above is left verbatim as the 2026-10-04 record of where
> that re-verification lived, but the path it names no longer exists: the file was folded into
> `AUDIT-AND-VERIFICATION.md` as the section `## audit-status.md` and deleted from `.scratch-uma/` the same
> day on the owner's instruction, so the embedded section is now the only copy.

### 1. Settled by a peer commit, no decision left

| Item                      | What the audit asked                                              | What the tree does now                                                                                                                                                                                                                                                                                                                                                                               |
| ------------------------- | ----------------------------------------------------------------- | ---------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------- |
| F-6 dead job              | `FetchSourceJob` is declared and never dispatched                 | Deleted at `fda8bba` (32 lines). `app/Jobs/` is empty and `git grep FetchSourceJob` hits only prose in `docs/research-scratch/AUDIT-AND-VERIFICATION.md` and `RACE-AND-SLICE-RESEARCH.md`. Nothing to approve                                                                                                                                                                                        |
| F-4 unbounded growth      | No unique constraint on `data_sources` or `match_candidates`      | `2026_10_01_124039` puts a unique index on `data_sources (umamusume_id, source_key, url)` and `2026_10_01_124051` a partial unique on `match_candidates (source_key, IFNULL(external_ref, ''), proposed_match_key)`, both at `fda8bba`. `PromoteMatchedRecord.php:86` writes provenance through `DataSource::updateOrCreate` on exactly the indexed triple, so the constraint and the writer agree   |
| F-8 unlocked reparse      | `uma:reparse` wrote without the fetch lock                        | `UmaReparse.php:43` takes the same `Cache::lock("uma-fetch:{$key}")` as `UmaFetch.php:55`                                                                                                                                                                                                                                                                                                            |
| L-F01 target size         | Nav links, export links, helper link and `<summary>` under 24px   | All carry `min-h-11`: `components/layout.blade.php:45,49,52,53,54,61`, `runs/show.blade.php:55,56,448,655,812`. Peer commit `ecae77d`                                                                                                                                                                                                                                                                |
| F-04 error envelope       | Two error treatments on one envelope                              | One `<ul>` now carries both the `previewed` stage marker and the field errors, `runs/show.blade.php:571-576`. Peer commit `a3e323c`                                                                                                                                                                                                                                                                  |
| KI-35 debut copy          | "Unknown" invented as an absence word                             | `catalog/show.blade.php:164,172` render `N/A` with a `title`. Commit `80caefd`                                                                                                                                                                                                                                                                                                                       |
| `.gitignore` tail         | UTF-16 dead line, `/vibe_images/` pointing nowhere                | The file is ASCII end to end (`file .gitignore`), and line 102 is `docs/vibe_images/`, confirmed by `git check-ignore -v docs/vibe_images/`                                                                                                                                                                                                                                                          |
| KI-51 untracked seeders   | Three seeder classes on disk, in no commit                        | `git ls-files database/seeders` now lists `ReadsCommittedSource.php`, `SourceDocumentSeeder.php` and `UmamusumeRosterSeeder.php`. The register entry that recorded them as unreachable is now stale                                                                                                                                                                                                  |

### 2. F-5, KI-24, KI-27: what a snapshot is for

The three are one decision. The audit's F-5 asked for a content-hash snapshot path; that landed at
`20364ae` and now reads `snapshots/{sourceKey}/{hash}.html` (`SourceFetcher.php:60`). Fixing the key is
what surfaced KI-27: the path is a property of the **document**, while `storage/` is shared by every
database on this machine, so a second database is told "nothing changed" and stays empty.

Measured by the audit, not by me: after 1,910 rows landed in `.scratch-uma/cited/skills-c5.sqlite`,
`uma:fetch` against `database/database.sqlite` printed "unchanged", and that database held 0
GlobalReleased client-named skills. `uma:reparse` then wrote 7 updated and 1,903 created.

KI-24 is the same class from the other end: a withdrawn document keeps answering `200` with stale
content, so a pinned URL is silent about being behind. `gametora-skills` and the sources that copied its
shape now resolve through a manifest; `gametora-characters` and `race_instances` still pin.

Options, as KI-27 lists them, with what each costs:

1. **Compare against what the database holds**, not against the file. The short-circuit asks the right
   question and the snapshot stays a body cache. Costs a per-source read of the promoted rows on every
   fetch.
2. **Keep the snapshot as a body cache and always continue into the pipeline.** Cheapest, idempotent
   because every store action upserts (proved this pass: `migrate --seed` twice exits 0 the second time
   and reports 0 created), but a fetch that used to short-circuit now reparses on every run.
3. **Key the snapshot per database.** Preserves the short-circuit and fixes the cross-database lie, at
   the cost of a second copy of a ~2.5 MB body per database and a path that names something nobody
   reads.

Recommendation, not a ruling: option 2. It is the only one that does not require the fetch to know what
another database did, and the idempotency it depends on is now tested (`RosterSeederReRunTest`,
`2aa0e5a`).

Owner: Data Engineer for the shape, Architect for the decision, per KI-27's own line.

### N-3 addendum: the fetch-catch was narrower than the audit premise

The audit's N-3 reads: `SourceFetcher.php` catches `RequestException` and returns null, and because
`->retry(..., throw: false)` is in play, an exhausted retry arrives at that catch and leaves without a log
line. Measured while fixing it, the premise held for half the failure modes and failed for the common one.
On this framework version `ConnectionException` is **not** a subtype of `RequestException`: both extend
`HttpClientException` (`vendor/laravel/framework/src/Illuminate/Http/Client/ConnectionException.php:5` and
`.../RequestException.php:7`). A DNS fault, a refused connection or a timeout therefore never entered the
`catch (RequestException)` block. It escaped `send()`, escaped `fetch()`, and ended the whole `uma:fetch`
run with an unhandled exception, while the same body fed through `db:seed` was caught by
`SourceDocumentSeeder`'s `catch (Throwable)` and turned into a warning plus a skipped source. One document
failure, two different outcomes, and the silence N-3 named was only the smaller half of it.

`9b8a9d5` widens the catch to `HttpClientException`, the parent of both, which is what `fetch()`'s own
docblock had always promised (null when the request ultimately failed) and what lets one source fail
without ending the run. Both failure branches now log the URL with the reason or the status.
`tests/Feature/FetchFailureReportingTest.php` covers the three paths: a connection fault returns null and
logs, a 404 returns null and logs, and a successful fetch logs nothing, which is what stops the first two
from passing because every path talks.

The audit entry for N-3 should be updated to record the mechanism rather than only the logging gap, because
the entry as written describes a defect that would have been fixed by adding a log line, and the defect that
crashed the run needed the catch class changed.

### 3. C-4: the `scenarios` table holds caps the app never consults

`Scenario::cap_speed`, `cap_stamina`, `cap_power`, `cap_guts`, `cap_wit` and `hard_cap` are filled by
`GametoraScenarioParser.php:78-80` and declared at `app/Models/Scenario.php:39,57-59`. Every ceiling the
app actually uses comes from `App\Services\ScenarioCaps`, which `ADR-0015` names the single owner. The
columns are therefore fetched truth nobody reads, and a future reader will not know which is authoritative.

This is a decision, not a bug: the numbers in the table are the source's, and `ScenarioCaps` is this
tool's arithmetic. Three ways out:

1. **Read them.** Make `ScenarioCaps` consult the stored caps when a scenario row carries them and fall
   back to its own table otherwise. Cost: two owners of one number, which is exactly what `ADR-0015`
   exists to prevent.
2. **Drop the columns.** A migration plus the `ARCHITECTURE-ESSENTIALS.md` digest and PRD citation
   `AGENTS.md` §11 requires. Cost: the fetched values stop being auditable, and the source's own ceiling
   can no longer be compared against ours.
3. **Keep and label.** Add a column comment or a docs line stating the columns are a source snapshot and
   not the ceiling the app enforces. Cost: one paragraph. Benefit: the next reader is not deciding this
   again.

Recommendation: option 3, because option 1 contradicts an accepted ADR and option 2 spends a migration to
lose data the fetch engine was told to keep.

Owner: Architect.

### 4. KI-38: one card stores the source's placeholder as its title

`character_cards` row `card_id=103601` carries `title="[unsigned]"` beside a real release date and
rarity, and the catalog list, the detail page and the run-form selector print `[unsigned]` as though it
were the costume name. Re-measured against `database/database.sqlite` on 2026-10-04 (read-only,
`.scratch-uma/cited/ki38-probe.php`): exactly one row, `rarity 3`, `global_release_date 2026-06-18`,
`unconfirmed = 0`, out of 106 stored cards. The audit's own figure was one placeholder over the 268-row
export, so the export and the table agree.

The entry's three options: treat a placeholder title as absent for display and search and keep the row;
flag it `unconfirmed` and let the cross-check queue decide; or accept it as a dated snapshot and render it
with a marker.

One consequence the entry does not state, and it changes the ranking: `unconfirmed = false` is a filter on
the read paths, not just a column (`SkillController.php` `holders()` and the run-page card scope both
constrain on it). Setting option 2's flag therefore does not rename the card, it deletes it from the deck
picker and the holders list, and a Trainer who owns that costume loses a legal choice. Option 2 is the
option that quietly destroys data the Trainer can see.

Recommendation: the first option, scoped to the display path, because `AGENTS.md` §5 gates this on the
display path and never by editing the data. The entry itself asks Lore Guardian to be consulted on the
third option only.

Owner: Data Engineer, with Lore Guardian.

### 5. KI-55: two documents `AGENTS.md` cites do not exist

Confirmed again this pass: `ls docs/GATE-REGISTRY.md docs/PRE-MORTEM.md` returns no such file for both,
and neither is tracked at HEAD. Their content lives as sections inside
`docs/research-scratch/GOVERNANCE.md`, which `AGENTS.md` §2 already points at for the quality bar.
`docs/research-scratch/DOCUMENTATION-INVENTORY-2026-09-30.md:243-244` contradicts this and marks both
files as tracked; that row is wrong, and it is the reason KI-55 reads as a conflict rather than a typo.

Three ways out, as the entry states them: restore both files at the cited paths, re-point the three
`AGENTS.md` citations at the GOVERNANCE.md sections, or cut the citations. Restoring is the one that
creates a second copy of a register, which is how `AGENTS.md` §3's file discipline says registers drift.

Recommendation: re-point. It is a three-line edit to `AGENTS.md` and it makes the precedence chain name a
file that exists.

Owner: Docs Writer for the re-point; the restore/re-point/cut choice is the owner's.

### 6. KI-45: the tier-to-grade mapping now misses every row

The entry's headline (no offline population path for `race_catalog_slots`) is superseded by `8b17703`,
and this pass confirmed the correction by seeding a fresh database offline: 410 race rows and a `migrate
--force --seed` that exited 0.

The second half has not improved. Read-only query against `database/database.sqlite` on 2026-10-04:
`scenario_slots` holds 296 rows and `tier` is NULL on **296 of 296**. At filing it was 141 of 296. Any
feature that maps a race's tier to a grade now misses every row, which is why Slice 3 stayed held.

The decision is upstream of code: either a source names the tier per slot, or the tier column is declared
unpopulated and the mapping is dropped from the plan. Inventing the mapping is the one thing
`AGENTS.md` §5 forbids, and `docs/UMAMUSUME_REFERENCE.md` carries grade-point figures the KI-10 entry
below says do not resolve this.

Owner: Architect with Planner Domain Specialist.

### 7. KI-25 and R82: phone width, and the floor that is not ratified

`R82` is not a defect, it is an unratified ruling: `PLANS-AND-BRIEFS.md:188-201` drafts it as a D-40
amendment saying 768px is the supported minimum and below it wide regions scroll rather than reflow, and
`PROCESS-PLANS.md:416` registers it as "the unratified proposal for a 768px responsive floor" with the
ratification gate at `PROCESS-PLANS.md:391,862-864`.

KI-25 (the nine-column turn log forcing the whole page into horizontal scroll at 390px: `scrollWidth 476`
against `innerWidth 390`) was closed on rendered attributes, re-opened by R85 because the closure never
read a browser, and cannot be finished before R82 is decided: whether the fix is a scoped scroll region
or a stacked layout below a breakpoint is exactly the difference R82 has not yet ruled.

Decision the owner owes: ratify R82 as drafted, or amend D-40. Everything about KI-25's fix shape follows
from that one answer, and the re-measurement has to be done in a browser either way.

Owner: human owner (ratification), then Frontend/Design-system with Architect.

### 8. KI-42: nothing runs the gates on push

`.github/` holds agents, hooks, prompts and skills and there is no `.github/workflows/`, so the full gate
is a local `composer test` run when a person chooses. The entry's own analysis is that the two defects it
recites were caught by a person noticing, not by a control, and that the common cause is every gate
running inside one developer's working tree where ignored files exist.

This is not a code question in this repository's current shape: there is no remote deployment path, no
CI in the plan, and `AGENTS.md` §1 says every gate runs locally and its output is the only evidence.
Options, in the order the entry implies:

1. **A clean-checkout job on push** (one workflow: `composer install`, `npm ci`, `php artisan test`,
   `pint --test`, `phpstan`, the lore gates). It makes the ignored-input and wrong-tree classes visible
   without a person. Cost: a CI surface the repo has deliberately never had, and it needs the host to
   push somewhere that can run it.
2. **A local pre-push hook** running the same sequence. Cost: per-machine, so it is exactly the
   "whoever remembers" control the entry is complaining about.
3. **A named-owner ritual**: the hand-off sequence in `AGENTS.md` §9 already is this, and the register
   records it being followed.

Recommendation: none is free, and option 1 contradicts §1's "no CI" statement, which would need an owner
decision of its own. If a control is wanted, the smallest honest one is a tracked script that runs the
hand-off sequence end to end and prints the evidence, so the gate is one command rather than nine
remembered ones.

Owner: human owner.

### 9. KI-43: the deck picker is most of the run page

Measured by the entry and worth restating: a six-equipped run page is 360,492 bytes, of which the deck
block is 296,537 (82.3%); with **nothing** equipped it is 329,355 bytes with the block at 291,547
(88.5%), because six `<select>` elements each repeat all 252 Global cards, 1,512 `<option>` elements on
every run screen.

Options: a search-first picker reusing `resources/js/trainee-combobox.ts` with the six selects as a
disabled no-JS fallback; a filtered shortlist of about forty cards plus a "show every card" affordance;
or leave it.

The decision is a design call about what a run page is for, and the fallback rule matters: whichever
picker shape is chosen has to keep the no-JS path, because `ADR-0007` and the server-driven disclosure
convention in `DESIGN.md` are what the rest of the screen already obeys.

Owner: human owner with the designer.

### 10. KI-10 and KI-15: the grade-point halves that need a source or a ruling

Both are data questions the repository cannot answer from itself.

**KI-10**, `Trackblazer Grade Points cannot be totalled`: `gradeEarned()` returns null for any finish
below 1st because no source names the placement ratio. The objective-bucket and stored-figure halves are
closed (`e103122`, `3711894`) and `GradePointPeriodTest` passes nine cases. Open half needs either a
sourced placement ratio or an owner ruling to ship an `[Unverified]` placeholder, and any turn/year bucket
on `race_entries` is a schema decision travelling with an ADR.

**KI-15**, `the three Grade Point tracks have no sourced rule for choosing one`: the page renders the
`standard` track (60/300/300) for every Trackblazer run while a dirt-leaning trainee asks 30/200/300 and a
weak-turf trainee 60/200/300. Either a dated capture names the condition, or the owner rules a
Trainer-entered track selector, which is a third column on `training_runs` and follows the `D-270` pattern.

Read against the corpus rather than the entry alone, the gap is narrower than "no source":
`docs/UMAMUSUME_REFERENCE.md:883-888` states the three threshold sets, says the choice is **by aptitude**
("a high-dirt or low-turf character takes 30 / 200 / 300, and a turf character whose range is narrow takes
60 / 200 / 300"), names Haru Urara and Curren Chan as the two cases, and records two independent
extractions agreeing. What it does not give is the *letter threshold*: `aptitude_dirt` at what grade,
`turf` at what grade, "range is narrow" measured against what. So the rule exists as a qualitative
statement and cannot be computed from the columns this database holds.

Recommendation for both: do not infer the threshold. For KI-15 the choice is therefore between a capture
that states the letters and a Trainer-entered track selector, and the second is the only one this tool can
ship today. For KI-10 the placement ratio has no figure anywhere in the corpus (`:900-901` covers shop
coins by placement, which is a different currency), so a sourced ratio or an `[Unverified]` placeholder is
still the whole decision.

Owner: Planner Domain Specialist with the human owner; the schema half is the Architect's alone.

### 11. F-01: the inline confirm on deleting a run, closed

The frontend register (`AUDIT-AND-VERIFICATION.md:3948`, §4.1) filed `onsubmit="return confirm(...)"` on
the Delete Run form as an Observation, not a defect, with "Fix in scope: No, its own dispatch" and no
claimed owner. That dispatch has since run: `ecae77d` is titled "Fix F-01: remove inline JS from run
detail route (WCAG 2.2)", and the tree now shows the replacement at `runs/show.blade.php:806-823`, a
`<details>` disclosure whose `<summary>` is the prompt and whose form submits the DELETE only once the
Trainer opens it, with `RunViewFrameTest` pinning the disclosure count. A `grep` for `onsubmit` or
`confirm(` across `resources/views/runs/` returns that comment text and nothing else.

Nothing is owed here except the register row's status, which is the closure/hold process this pass was
told not to run.

One hazard worth keeping in mind when reading history: the audit's own N-1 records that commit `4e17997`
used the labels "F-1, F-2" for unrelated UI fixes, so a search for `F-1`/`F-2`/`F-01` in the log can
return a fix for a different finding. The `F-n` audit ids from 2026-10-01, the `F-nn` frontend ids from
2026-10-03, and the `KI-nn` register are three separate numbering systems.

### 12. Residuals from this pass that are file-ownership problems, not findings

Recorded here because a later pass will otherwise re-find them.

- **KI-41 has a seventh site this pass could not commit.** `SkillController.php:53` orders the skill list
  by name with no tiebreaker, same as the six that were fixed at `f8594e1`. That file carries a peer's
  uncommitted `json_valid` guard in `holders()`, so committing it would have landed their work under this
  commit's message.
- **KI-46's envelope is a view decision.** The messages now name their row, but
  `resources/views/runs/import.blade.php:153` prints only the first message per column (Blade's `@error`
  resolves through `MessageBag::first()`), so two broken rows still show one line. That file is peer-dirty.
- **Two `KNOWN-ISSUES.md` forward notes are drafted and unapplied.** The welcome.blade.php sweep and the
  KI-23 versus KI-23b reconciliation are written out in `.scratch-uma/audit-status.md` §5. Root
  `KNOWN-ISSUES.md` is in the working tree as a 71-line pointer stub against a 2,655-line committed
  register, a peer's 2,722-line change in progress. Appending to it and committing would land that
  restructure under a docs-fix message, which `AGENTS.md` §11 and the shared-worktree rule both forbid.
- **`runs/import.blade.php` and `form-detail.blade.php` are peer-dirty**, so any copy fix on those screens
  needs the peer's change landed first.

> **Corrected forward 2026-10-06.** The bullet above that names `.scratch-uma/audit-status.md` §5 is left
> verbatim as the 2026-10-04 record, but that file was folded into `AUDIT-AND-VERIFICATION.md` (section
> `## audit-status.md`, and the forward notes sit in the `For the welcome.blade sweep` and
> `For KI-23 vs KI-23b` blocks near the end of it) and deleted from `.scratch-uma/` the same day on the
> owner's instruction.

### Register corrections from the 2026-10-04 pass

Three findings' recorded status no longer matches what the tree does. None of them needs code; each needs
the register line moved. They are listed here rather than edited into the register because root
`KNOWN-ISSUES.md` is a peer's in-flight rewrite (see section 12) and
`docs/research-scratch/AUDIT-AND-VERIFICATION.md` is closed to this pass by the dispatch that ordered it.

**F-2, `cff9e92`: the fix carries a consequence the entry does not name.** `db:seed` now exits non-zero when
a source fails to import, which is what F-2 asked for. The consequence: `DatabaseSeeder` calls seeders in a
fixed order, and because the failure is raised at the end of `SourceDocumentSeeder::run()`, anything after it
in that list does not execute on a partial seed. Today that is `ScenarioSlotSeeder`, so a failed source also
leaves the URA finale goal races unpopulated.

Accepted as the trade, and the reasoning is recorded because "seed exits with a code" does not say it: the
skip is survivable only since KI-56 (`2aa0e5a`) made `db:seed` re-runnable. Before that, a re-run was the one
action that could not repair a partial seed, because it aborted on its own first pass. The repair path for a
failed source is `php artisan uma:reparse <source>` for a snapshot the engine already holds, or a plain re-run
of `migrate --seed` once the input is fixed. A register entry that records F-2 as closed without naming the
seeder skip will read, to the next person, as though one source failed quietly.

**KI-50, `5a7e5e6`: one half of option (a) landed.** The DDL half is closed:
`tests/Feature/SchemaCheckConstraintTest.php` reads `sqlite_master` and asserts both that each of the five
tables carrying a constrained domain has a real `CHECK` clause and the value list that clause permits. It was
mutation-checked by deleting the `support_effects` calc clause, which turned two of its seven cases red.

The grep half of option (a), "a gate that greps `database/migrations/` for `->check(` and fails", is not
built, and it cannot be built as written: `->check(` is present today in
`2026_09_30_142618_create_support_cards_and_support_effects_tables.php` and in
`2026_09_30_151945_correct_support_card_schema_and_constraints.php`, and the entry's own text says those two
files "must not be modified" because `151945`'s `down()` deliberately reconstructs the constraint-free shape.
A gate that fails at HEAD is either suppressed or wrong, and the Floor forbids the new suppression. So KI-50
should read **partially resolved: DDL guard closed at `5a7e5e6`, spelling gate open, blocked by the two
grandfathered migrations**, not open and not closed. If the owner wants the spelling gate, the decision it
needs is whether those two migrations may carry an allowlist exception, which is a different ruling than the
one the entry already records.

**KI-55: the re-point this document recommended has already happened.** Section 5 above was written from the
2026-10-02 register entry and recommends re-pointing `AGENTS.md`'s three citations at the GOVERNANCE.md
sections. Checked at this HEAD: `git show HEAD:AGENTS.md` lines 5, 94 and 178 already name
`docs/research-scratch/GOVERNANCE.md` §"GATE-REGISTRY.md" and §"PRE-MORTEM.md", and the re-point landed as
`bc42d93` ("O-2: repoint citations to GOVERNANCE.md sections"). Section 5's recommendation is therefore
closed, and the owner decision it asked for is already given.

What remained open was the other half of the contradiction, and it is the reason section 5's own citation was
wrong: the rows marking the two paths as tracked are at lines 90 and 91 of
`docs/research-scratch/DOCUMENTATION-INVENTORY-2026-09-30.md` as that file was written, **not** at the
`243-244` this document cited. Lines 243-244 are frontend probe records. That citation came from a subagent
report this pass relayed without re-reading the target, which is the failure mode the relay rule exists to
stop. Item 1 of this dispatch corrected rows 90 and 91 in place at `f1c18fc`.

Still open on KI-55, and small: the inventory file names `docs/research-scratch/GOVERNANCE.md` or `docs/research-scratch/GOVERNANCE.md` as
live paths in thirteen other rows and tables too (counted at `f1c18fc` by grepping both paths and excluding
the three corrected lines; the line numbers move on every edit, so re-run the grep rather than trusting a
list here). Those are outside Item 1's stated scope and are recorded rather than edited.

### Coordination: the peer-dirty file pattern

Three files have blocked items across two dispatches and all three are still dirty at this HEAD:
`KNOWN-ISSUES.md` (a 2,655 to 71 line restructure in flight, 2,722 lines of deletion in the working tree),
`app/Http/Controllers/SkillController.php` (an uncommitted `json_valid` guard in `holders()`), and
`resources/views/runs/import.blade.php` (two uncommitted copy hunks). What they cost, in order: the two
register forward notes, KI-41's seventh `orderBy('name')` site, and KI-46's view half. This is the second
dispatch to stop at the same three files.

The pattern is wider than three files. `git status --porcelain -- resources/views/` at this HEAD returns 13
entries, and 11 of them are `resources/views/components/*.blade.php`. A fourth blocked dispatch looks more
likely than a coincidence. The file this dispatch suspected as the fourth,
`resources/views/preferences/edit.blade.php`, is not among them.

Three ways out, for the owner to choose. This pass takes none of them and lands none of the blocked items.

**Option A, peer commits first.** The session holding those files lands its work before the next triage pass
runs. Cheapest, no new machinery, nothing to maintain. It depends on coordination between two agent sessions
that cannot signal each other from inside the worktree boundary, which is what the owner already observed on
the last coordination request of this kind.

**Option B, fork per session.** Each session works on a named branch (`session/triage-2026-10-04`,
`session/audit-2026-10-04`) and merges on owner review. Ends the collision class outright, at the cost of one
merge step per dispatch. Two measurements bear on it. `git worktree list` at this HEAD shows three working
directories, the primary plus two `.kilo` sandboxes, and one of those is at detached HEAD, so isolated
workspaces already exist on this host while carrying no reviewable branch name; a detached worktree's commits
are reachable only by that SHA until someone branches them. And a prior pass on this repository measured that
a second worktree arrives with no `vendor/`, that linking the primary's `vendor/` makes Composer's autoloader
resolve through the link back to the primary's `tests/` so Pest's binding never applies, and that a real
`composer install` is the only version that runs the suite. Option B pays that per branch.

**Option C, file-ownership lockfile.** A tracked record declares which paths each active session holds, and a
dispatch reads it at premise time and refuses to start on a locked file. Explicit and auditable, which is what
this repository's provenance rules ask of any coordination mechanism, and it turns "we both rewrote
`stat-band.blade.php`" into a precondition instead of a post-merge repair. Costs one more tracked thing to
maintain, one more thing a session can forget to update, and AGENTS.md §3 forbids creating a new markdown file
in the repository root without a ruling, so the lockfile would have to live under `docs/research-scratch/` or
as a section of `INDEX.md` rather than at `.worktree-locks` in the root.

If the same three files block a third dispatch, A has been shown not to work and B or C is warranted.

---

## 2026-10-05-image-slot-display.md

## Image Slot Display Plan

> **For agentic workers:** REQUIRED SUB-SKILL: Use `superpowers:subagent-driven-development`
> (recommended) or `superpowers:executing-plans` to execute this plan task-by-task.
> Steps use checkbox (`- [ ]`) syntax for tracking. **The Progress section at the
> bottom mirrors each task's state and is updated as the slice lands.**

> **SUPERSEDED IN PART, 2026-10-05.** Tasks 4, 5, 6 and 8 as written below build the slots as
> **Blade** components (`<x-character-portrait>`, `<x-support-thumb>`). They shipped on
> `feat/image-slot-display` and were deleted by `b645048` on `trainer-desk-2.0`, which replaced
> both with one Vue SFC, `resources/js/components/ArtworkSlot.vue`, adopted at `fff920f` when the
> A1 to A3 ports retired the Blade screens those components were built for. **Do not execute Tasks
> 4, 5, 6 and 8.** Read them as the record of what landed and why the port retired it. Tasks 1 to 3
> (the mirror probe, the streaming route, the url helper) and Task 7 (the catalog index wire-up)
> stand as written; Task 9 is recorded as unplaceable and Task 10 landed.
>
> The single `decorative` flag these tasks specify was a **WCAG 2.2 AA 4.1.2 defect**, not a style
> preference: it blanked `alt` and `aria-label` together, so a decorative image inside a named link
> was unrepresentable and each of the three no-action slots rendered an anchor with a blanked
> accessible name. `ArtworkSlot.vue` takes `alt` and `href`/`linkLabel` separately, which makes the
> defect unrepresentable. `DESIGN.md` §4.7's fifth bullet records the gap and its resolution. The
> full dated erratum is at the foot of this file.

**Goal:** Render id-addressed artwork (trainee portraits, support-card thumbnails)
on the screens that already display the rows that own them, with WCAG 2.2 AA
conformance and the UX laws named in `docs/research-scratch/PROCESS-PLANS.md` section `## frontend-development-plan.md`
§13, while preserving the existing text-only row as the universal fallback.

**Architecture:** The mirror is already built (`uma:fetch-art`,
`App\Services\DataPipeline\ArtworkMirror`). What this plan adds is the display
half: a loopback route at `/artwork/{kind}/{id}` that streams a stored file
from private storage, a slot-helper that returns the URL only when the file is
on disk, and per-screen wire-ups into the existing catalog / support-card /
run-create Blade templates and the Inertia Vue catalog index. Nothing enters
the database. Skill icons stay deferred because `skills` has no `iconid`
column (`ADR-0021` Verification).

**Tech Stack:** Laravel 13, Blade, `php artisan make:*`, Pest 4,
`Http::fake` for tests, `@inertiajs/vue3` for the catalog index page, Tailwind v4
tokens only. Runs on a single SQLite file under loopback. No new package.

**Spec — what the plan implements:**

- `docs/adr/0021-sourced-character-artwork.md` (Decision rows 2 to 6).
- `docs/proposals/design-2.0.md §42 Image slots` + `§45a Sourced image slots`
  (filed 2026-10-05).
- `docs/proposals/screen-spec-2.0.md §34 Image slots` + `§35 Sourced image
  slots` (renumbered; same date).
- `DESIGN.md §4.7 Sourced artwork slots` (binding rules: absence normal, geometry
  decided once, `src` local, alt inside C-4, decorative → `alt=""`).
- `docs/proposals/frontend-development-plan.md §12 WCAG 2.2 AA`, `§13 Laws of UX`,
  `§14 Phase A0`. The plan's per-screen slices are the Blade-side realization of
  Phase A0c.

### Global Constraints

Every slice implicitly carries the rules below from the doctrine, the spec, and
the operational contract.

- **No new package, no dependency update.** `composer audit` and `npm audit --omit=dev`
  clean before the slice lands; relock speeds the rest of the work.
- **No image upload surface.** `PRD §6.13` stays cut. `ADR-0021` is the only path:
  the tool fetches id-addressable art from an allowlisted asset host into private
  storage; nothing in the database; nothing exposed through a public route.
- **Local mirror only, loopback only.** Files live under `storage/app/private/artwork/`
  (gitignored) and are served by a named route that binds to loopback. No
  `storage:link`, no `public/` export.
- **WCAG 2.2 AA on every slot-bearing screen.** The binding rules in
  `design-2.0 §42` and the `frontend-development-plan §12` carry; the four
  per-screen clauses the spec names (alt text, reserved-box contrast, label in
  name, omitted at narrow viewports) are non-negotiable.
- **Lore gate clean.** Every diff line written this turn is in the doc/coded
  corpus. Banned vocabulary is enforced by `composer lore` and
  `composer lore-code`; hits carry an inline ruling.
- **Pint + PHPStan L6 + the affected feature test + the cross-screen doc-gate
  tests** run on every slice that touches behaviour, not prose-only slices.
  Prose-only slices still run `composer lore` and the doc gates.
- **One slice, one commit.** Conventional subject: `feat(slots):`, `test(slots):`,
  `docs(slots):`, `refactor(slots):`. PRs do not exist on this repo; commits
  are de facto PRs.
- **TDD where the slice has logic.** A trivial view markup edit ships with a
  asserting feature test for the rendered `<img>` (or its absence); a
  service or controller change ships with the model/route/unit test that
  proves it. No test asserts the user's prose; tests assert behaviour.

### File Structure Lock-in

Files this plan creates or modifies, with single-responsibility intent.

- Create: `app/Http/Controllers/ArtworkAssetController.php` — one method,
  `show(string $kind, int $id): Response` streams the file or aborts 404.
- Modify: `routes/web.php` — one `Route::get('/artwork/{kind}/{id}', …)` line.
- Modify: `app/Services/DataPipeline/ArtworkMirror.php` — add `exists(string $kind,
  int $id): bool`, `url(string $kind, int $id): ?string`, lift `storedPath()` and
  `disk()` to public so the controller and tests can name them.
- Create: `resources/views/components/character-portrait.blade.php` — `<img>`
  wrapper for trainee portraits.
- Create: `resources/views/components/support-thumb.blade.php` — same shape
  for support-card thumbs.
- Modify: `resources/views/catalog/show.blade.php` — Identity row gets
  `<x-character-portrait>`.
- Modify: `resources/views/support-cards/index.blade.php` — card row gets
  `<x-support-thumb>`.
- Modify: `resources/views/support-cards/show.blade.php` — header gets
  `<x-support-thumb>`.
- Modify: `resources/views/runs/create.blade.php` — Legacy Select row + form row.
- Modify: `resources/js/pages/Catalog/Index.vue` — trainee card header + form row.

Tests:

- Extend: `tests/Feature/ArtworkMirrorTest.php` — two cases for `exists()` and
  two for `url()`.
- Create: `tests/Feature/ArtworkAssetRouteTest.php` — 3 cases (mirrored 200,
  absent 404, unknown `kind` 404).
- Create: `tests/Feature/CatalogDetailPortraitTest.php`, `SupportCardsIndexThumbnailTest.php`,
  `SupportCardsDetailThumbnailTest.php`, `RunsCreateFormPortraitTest.php`,
  `CatalogIndexPortraitTest.php`.

A repo-wide `policies` table migration or a controller-level `authorize()`
call is **out of scope** (escalation 7 territory per `AGENTS.md §4`).

---

### Task 1: `ArtworkMirror::exists` — TDD surface for views

**Files:**

- Modify: `app/Services/DataPipeline/ArtworkMirror.php`
- Modify: `tests/Feature/ArtworkMirrorTest.php`

**Interfaces:**

- Consumes: `Storage::disk('local')->exists($path)`.
- Produces:

```php
public function exists(string $kind, int $id): bool
```

Returns `true` when the file is on disk under the mirror's `ROOT.'/'.$relative`,
`false` otherwise.

- [ ] **Step 1: Write the failing test**

  Append two cases to `tests/Feature/ArtworkMirrorTest.php`:

  ```php
  it('reports a mirrored file as existing', function (): void {
```text
  Storage::disk('local')->put(
      'artwork/characters/portrait/trainee/256/100101.png',
      'BYTES'
  );

  expect(app(\App\Services\DataPipeline\ArtworkMirror::class)
      ->exists('card_portrait', 100101))->toBeTrue();
```

  });

  it('reports an absent file as not existing', function (): void {

```text
  expect(app(\App\Services\DataPipeline\ArtworkMirror::class)
      ->exists('card_portrait', 100102))->toBeFalse();
```

  });

  ```text

- [ ] **Step 2: Run tests to verify they fail**

  Run: `vendor/bin/pest --compact --filter "reports a mirrored file" tests/Feature/ArtworkMirrorTest.php`
  Expected: FAIL with `Call to undefined method …::exists()`.

- [ ] **Step 3: Write minimum implementation**

  In `app/Services/DataPipeline/ArtworkMirror.php`, directly after the
  `kinds()` method, add:

  ```php
  public function exists(string $kind, int $id): bool
  {
      return Storage::disk(self::DISK)->exists($this->storedPath($this->relativePath($kind, $id)));
  }

  public function storedPath(string $relativePath): string
  {
      return self::ROOT.'/'.$relativePath;
  }

  public function disk(): \Illuminate\Contracts\Filesystem\Filesystem
  {
      return Storage::disk(self::DISK);
  }
  ```

  `storedPath()` is lifted from private to public because the controller and
  the helper both need the absolute path; `disk()` exposes the storage
  filesystem so the controller's `get()` is testable.

- [ ] **Step 4: Run tests to verify they pass**

  Run: `vendor/bin/pest --compact tests/Feature/ArtworkMirrorTest.php`
  Expected: PASS (counts: 8 + 2 = 10 cases).

- [ ] **Step 5: Commit**

  ```bash
  git add app/Services/DataPipeline/ArtworkMirror.php tests/Feature/ArtworkMirrorTest.php
  git commit -m "feat(slots): expose mirrored-file probe on ArtworkMirror"
  ```

### Task 2: Streaming route for `<img src>`

**Files:**

- Create: `app/Http/Controllers/ArtworkAssetController.php`
- Modify: `routes/web.php` (one line)
- Create: `tests/Feature/ArtworkAssetRouteTest.php`

**Interfaces:**

- Consumes: `ArtworkMirror::exists`, `ArtworkMirror::disk()->get($path)`.
- Produces:
  - Route `artwork.show` at `/artwork/{kind}/{id}` where

```text
`kind ∈ {card_portrait, support_thumb}` (the two paths the config
declared). Any other `kind` returns 404.
```

- 200 with `image/png` body and the raw bytes when mirrored; 404
    otherwise. The controller **never** reads a `kind` outside the allowlist.

- [ ] **Step 1: Write the failing test**

  ```php
  use App\Services\DataPipeline\ArtworkMirror;
  use Illuminate\Support\Facades\Route;
  use Illuminate\Support\Facades\Storage;

  beforeEach(function (): void {

```text
  Storage::fake('local');
  config(['uma.sources.gametora-artwork.delay_ms' => 0]);
```

  });

  it('streams a mirrored portrait', function (): void {

```text
  Storage::disk('local')->put(
      'artwork/characters/portrait/trainee/256/100101.png',
      'PNG-BYTES'
  );

  $response = $this->get('/artwork/card_portrait/100101');

  $response->assertOk();
  $response->assertHeader('Content-Type', 'image/png');
  expect($response->getContent())->toBe('PNG-BYTES');
```

  });

  it('returns 404 when the file is not mirrored', function (): void {

```text
  // File not seeded.
  $this->get('/artwork/card_portrait/100102')->assertNotFound();
```

  });

  it('rejects an unknown kind with 404', function (): void {

```text
  Storage::disk('local')->put(
      'artwork/characters/portrait/trainee/256/100101.png',
      'PNG-BYTES'
  );

  $this->get('/artwork/profile_pose/100101')->assertNotFound();
```

  });

  ```text

- [ ] **Step 2: Run tests to verify they fail**

  Run: `vendor/bin/pest --compact tests/Feature/ArtworkAssetRouteTest.php`
  Expected: FAIL with `Route [artwork.show] not defined`.

- [ ] **Step 3: Minimum implementation**

  Create `app/Http/Controllers/ArtworkAssetController.php`:

  ```php
  declare(strict_types=1);

  namespace App\Http\Controllers;

  use App\Services\DataPipeline\ArtworkMirror;
  use Illuminate\Http\Request;
  use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

  /**
   * Streams an artwork file the mirror has already fetched. Id-addressed, not
   * path-addressed: the controller validates `kind` against the asset host's
   * declared paths and `id` against the integer cast, so a request like
   * `/artwork/../../etc/passwd` is refused before it reaches Storage.
   */
  final class ArtworkAssetController extends Controller
  {
```text
  public function show(Request $request, ArtworkMirror $mirror, string $kind, int $id)
  {
      $allowed = ['card_portrait', 'support_thumb'];

      if (! in_array($kind, $allowed, true) || $id <= 0) {
          throw new NotFoundHttpException();
      }

      if (! $mirror->exists($kind, $id)) {
          throw new NotFoundHttpException();
      }

      $bytes = $mirror->disk()->get(
          $mirror->storedPath($mirror->relativePath($kind, $id))
      );

      return response($bytes, 200, ['Content-Type' => 'image/png']);
  }
```

  }

  ```text

  Append to `routes/web.php` inside the existing web group:

  ```php
  Route::get('/artwork/{kind}/{id}', [ArtworkAssetController::class, 'show'])
```text
  ->whereNumber('id')
  ->name('artwork.show');
```

  ```text

- [ ] **Step 4: Run tests to verify they pass**

  Run: `vendor/bin/pest --compact tests/Feature/ArtworkAssetRouteTest.php`
  Expected: PASS (3 cases).

- [ ] **Step 5: Commit**

  ```bash
  git add app/Http/Controllers/ArtworkAssetController.php app/Services/DataPipeline/ArtworkMirror.php routes/web.php tests/Feature/ArtworkAssetRouteTest.php
  git commit -m "feat(slots): stream mirrored artwork over a loopback route"
  ```

### Task 3: `ArtworkMirror::url()` — name-routed helper for views

**Files:**

- Modify: `app/Services/DataPipeline/ArtworkMirror.php`
- Modify: `tests/Feature/ArtworkMirrorTest.php`

**Produces:**

```php
public function url(string $kind, int $id): ?string
```

Returns `route('artwork.show', ['kind' => $kind, 'id' => $id])` when mirrored;
`null` otherwise.

- [ ] **Step 1: Write the failing test**

  ```php
  it('returns the named-route url when mirrored', function (): void {
```text
  Storage::disk('local')->put(
      'artwork/characters/portrait/trainee/256/100101.png',
      'BYTES'
  );
  // The stub route covers test-only environments; full route is registered
  // in Task 2's commit and by tests/Pest.php's web group binding.
  \Illuminate\Support\Facades\Route::get(
      '/artwork/{kind}/{id}',
      fn () => ''
  )->name('artwork.show');

  expect(app(\App\Services\DataPipeline\ArtworkMirror::class)
      ->url('card_portrait', 100101)
  )->toBe(route('artwork.show', ['kind' => 'card_portrait', 'id' => 100101]));
```

  });

  it('returns null when the file is not mirrored', function (): void {

```text
  expect(app(\App\Services\DataPipeline\ArtworkMirror::class)
      ->url('card_portrait', 100103))->toBeNull();
```

  });

  ```text

- [ ] **Step 2: Run tests to verify they fail**

  Run: `vendor/bin/pest --compact --filter "returns the named-route url" tests/Feature/ArtworkMirrorTest.php`
  Expected: FAIL with `Call to undefined method …::url()`.

- [ ] **Step 3: Minimum implementation**

  ```php
  public function url(string $kind, int $id): ?string
  {
```text
  return $this->exists($kind, $id)
      ? route('artwork.show', ['kind' => $kind, 'id' => $id])
      : null;
```

  }

  ```text

- [ ] **Step 4: Run tests to verify they pass**

  Run: `vendor/bin/pest --compact tests/Feature/ArtworkMirrorTest.php`
  Expected: PASS (10 + 2 = 12 cases).

- [ ] **Step 5: Commit**

  ```bash
  git add app/Services/DataPipeline/ArtworkMirror.php tests/Feature/ArtworkMirrorTest.php
  git commit -m "feat(slots): expose name-routed url helper on ArtworkMirror"
  ```

### Task 4: `<x-character-portrait>` Blade component

**Files:**

- Create: `resources/views/components/character-portrait.blade.php`
- Create: `tests/Feature/CharacterPortraitComponentTest.php`

- [ ] **Step 1: Failing test**

  ```php
  use Illuminate\Support\Facades\Storage;

  beforeEach(function (): void {

```text
  Storage::fake('local');
  config(['uma.sources.gametora-artwork.delay_ms' => 0]);
  \Illuminate\Support\Facades\Route::get(
      '/artwork/{kind}/{id}',
      fn () => ''
  )->name('artwork.show');
  \Illuminate\Support\Facades\Route::get(
      '/umamusume/{slug}',
      fn () => ''
  )->name('catalog.show');
```

  });

  it('renders an img when the file is mirrored', function (): void {

```text
  Storage::disk('local')->put(
      'artwork/characters/portrait/trainee/256/100101.png',
      'PNG'
  );

  $rendered = (string) blade(
      '<x-character-portrait :card-id="100101" size-class="size-12" name="Air Groove" :route-args="[\'slug\' => \'air-groove\']" />',
      []
  );

  expect($rendered)->toContain('<img');
  expect($rendered)->toContain(route('artwork.show', ['kind' => 'card_portrait', 'id' => 100101]));
  expect($rendered)->toContain('alt="Air Groove"');
```

  });

  it('renders nothing when the file is not mirrored', function (): void {

```text
  $rendered = (string) blade(
      '<x-character-portrait :card-id="100101" size-class="size-12" name="Air Groove" />',
      []
  );

  expect(trim($rendered))->toBe('');
```

  });

  it('uses alt="" when decorative is set', function (): void {

```text
  Storage::disk('local')->put('artwork/characters/portrait/trainee/256/100101.png', 'PNG');

  $rendered = (string) blade(
      '<x-character-portrait :card-id="100101" size-class="size-12" name="Air Groove" decorative />',
      []
  );

  expect($rendered)->toContain('alt=""');
```

  });

  ```text

- [ ] **Step 2: Run tests to verify they fail**

  Run: `vendor/bin/pest --compact tests/Feature/CharacterPortraitComponentTest.php`
  Expected: FAIL with `Component [character-portrait] not found`.

- [ ] **Step 3: Component file**

  ```blade
  @props([
```text
  'cardId' => 0,
  'sizeClass' => 'size-12',
  'name' => '',
  'decorative' => false,
  'routeArgs' => [],
```

  ])

  @php

```text
  $mirrored = app(\App\Services\DataPipeline\ArtworkMirror::class);
  $url = $mirrored->url('card_portrait', (int) $cardId);
```

  @endphp

  @if ($url !== null)

```text
  <a href="{{ route('catalog.show', $routeArgs) }}"
     aria-label="{{ $decorative ? '' : $name }}"
     class="block {{ $sizeClass }}">
      <img src="{{ $url }}"
           alt="{{ $decorative ? '' : $name }}"
           class="{{ $sizeClass }} object-cover rounded-md"
           width="64" height="64">
  </a>
```

  @endif

  ```text

- [ ] **Step 4: Run tests to verify they pass**

  Expected: PASS (3 cases).

- [ ] **Step 5: Commit**

  ```bash
  git add resources/views/components/character-portrait.blade.php tests/Feature/CharacterPortraitComponentTest.php
  git commit -m "feat(slots): add <x-character-portrait> with decorative alt handling"
  ```

### Task 5: `<x-support-thumb>` Blade component

**Files:**

- Create: `resources/views/components/support-thumb.blade.php`
- Create: `tests/Feature/SupportThumbComponentTest.php`

Task 5 mirrors Task 4 exactly, swapping `card_portrait`/`cardId` for
`support_thumb`/`supportId` and `catalog.show` for `support-cards.show`.

- [ ] **Steps 1–5**

  Follow the Task 4 workflow verbatim with the substitutions above. Two cases:
  "renders an img when mirrored" and "renders nothing when absent". The
  decorative test is implicit because support cards rarely print their
  title beside the thumb — set `decorative` only when the placeholder calls for it.

- [ ] **Commit subject**

  ```bash
  git commit -m "feat(slots): add <x-support-thumb> with decorative alt handling"
  ```

### Task 6: Catalog detail Identity section lands the slot

**Files:**

- Modify: `resources/views/catalog/show.blade.php` — Identity row.
- Create: `tests/Feature/CatalogDetailPortraitTest.php`

Note `decorative="true"`: the section already prints the trainee's name above
the slot, so the spec's "where the same name is printed beside the image, the
image is decorative and takes `alt=""`" rule fires.

- [ ] **Step 1: Failing test**

  ```php
  it('shows the mirrored portrait when present', function (): void {

```text
  $card = \App\Models\CharacterCard::factory()->create(['card_id' => 100101]);
  Storage::disk('local')->put('artwork/characters/portrait/trainee/256/100101.png', 'PNG');

  $this->get('/umamusume/'.$card->umamusume->slug)
      ->assertOk()
      ->assertSee('<img', false)
      ->assertSee('alt=""', false);
```

  });

  it('omits the slot when the portrait is not mirrored', function (): void {

```text
  $card = \App\Models\CharacterCard::factory()->create(['card_id' => 100101]);

  $this->get('/umamusume/'.$card->umamusume->slug)
      ->assertOk()
      ->assertDontSee('<img', false);
```

  });

  ```text

- [ ] **Step 2: Run tests to verify they fail**

- [ ] **Step 3: Minimum edit**

  In `catalog/show.blade.php`, find the Identity row that prints the
  trainee name (currently a header element inside section 1). Insert
  immediately above that header:

  ```blade
  <x-character-portrait
```text
  :card-id="$card->card_id"
  size-class="size-16"
  :name="$trainee->name"
  decorative
  :route-args="['slug' => $trainee->slug]"
```

  />

  ```text

- [ ] **Step 4: Run tests to verify they pass**

- [ ] **Step 5: Commit**

  ```bash
  git commit -m "feat(slots): wire portrait into catalog detail Identity"
  ```

### Task 7: Catalog index Vue page lands the slot in trainee card + form rows

**Files:**

- Modify: `resources/js/pages/Catalog/Index.vue`
- Modify: `app/Http/Controllers/CatalogController.php`
- Create: `tests/Feature/CatalogIndexPortraitTest.php`

- [ ] **Step 1: Failing test**

  ```php
  use Illuminate\Support\Facades\Storage;
  use Inertia\Testing\AssertableInertia;

  it('passes portrait urls per trainee row and per form', function (): void {

```text
  $card = \App\Models\CharacterCard::factory()->create(['card_id' => 100101]);
  Storage::disk('local')->put('artwork/characters/portrait/trainee/256/100101.png', 'PNG');

  $this->get('/umamusume')
      ->assertInertia(fn (AssertableInertia $page) =>
          $page->component('Catalog/Index')
              ->where('rows.0.artworkURL', route('artwork.show', ['kind' => 'card_portrait', 'id' => 100101]))
      );
```

  });

  it('passes null when the trainee has no mirrored portrait', function (): void {

```text
  \App\Models\CharacterCard::factory()->create(['card_id' => 100101]);

  $this->get('/umamusume')
      ->assertInertia(fn (AssertableInertia $page) =>
          $page->component('Catalog/Index')
              ->where('rows.0.artworkURL', null)
      );
```

  });

  ```text

- [ ] **Step 2: Run tests to verify they fail**

- [ ] **Step 3: Controller + page minimum**

  In `CatalogController::index()`, after the existing trainees->map, attach
  per-row artwork URLs:

  ```php
  $mirror = app(\App\Services\DataPipeline\ArtworkMirror::class);
  $rows = $trainees->map(function ($trainee) use ($mirror) {
```text
  $topForm = $trainee->cards->sortByDesc('rarity')->first();
  return [
      'id' => $trainee->id,
      'slug' => $trainee->slug,
      'name' => $trainee->name,
      'artworkURL' => $topForm
          ? $mirror->url('card_portrait', (int) $topForm->card_id)
          : null,
      'forms' => $trainee->cards->map(fn ($c) => [
          'id' => $c->id,
          'title' => $c->title,
          'rarity' => $c->rarity,
          'artworkURL' => $mirror->url('card_portrait', (int) $c->card_id),
      ])->all(),
  ];
```

  })->all();

  ```text

  Return `rows` instead of the existing trainees-shaped array (the existing
  shape's keys stay, the new keys are added).

  In `Catalog/Index.vue`, render the slot before each row's name cell and
  before each form's title cell. Existing two-level layout fits the
  `size-12` cell ahead of the header and the `size-10` cell inline with
  the form row.

- [ ] **Step 4: Run tests to verify they pass**

- [ ] **Step 5: Commit**

  ```bash
  git commit -m "feat(slots): wire portraits into catalog index Inertia page"
  ```

### Task 8: Support-card index + detail wire the thumb slot

**Files:**

- Modify: `resources/views/support-cards/index.blade.php`
- Modify: `resources/views/support-cards/show.blade.php`
- Create: `tests/Feature/SupportCardsIndexThumbnailTest.php`
- Create: `tests/Feature/SupportCardsDetailThumbnailTest.php`

Two screens, one component. Both tests use the same shape:

```php
it('renders the thumb on the support-card index', function (): void {
    \App\Models\SupportCard::factory()->create(['support_id' => 10001]);
    Storage::disk('local')->put('artwork/supports/full/small/10001.png', 'PNG');

    $this->get('/support-cards')
        ->assertOk()
        ->assertSee('<img', false)
        ->assertSee(route('artwork.show', ['kind' => 'support_thumb', 'id' => 10001]), false);
});

it('omits the thumb when not mirrored', function (): void {
    \App\Models\SupportCard::factory()->create(['support_id' => 10001]);

    $this->get('/support-cards')->assertOk()->assertDontSee('<img', false);
});
```

Wire the slot in the existing header block of both, alongside the title/rarity:

```blade
<x-support-thumb
    :support-id="$card->support_id"
    size-class="size-12"
    :name="$card->displayName()"
    :route-args="['card' => $card->support_id]"
/>
```

- [ ] **Step 1 + 2 (running failing tests)** for both files with the appropriate route name.
- [ ] **Step 3 (minimum edit)** for both files.
- [ ] **Step 4 (verify all pass)**: `vendor/bin/pest --compact tests/Feature/SupportCardsIndexThumbnailTest.php tests/Feature/SupportCardsDetailThumbnailTest.php`.
- [ ] **Step 5: Commit**

  ```bash
  git commit -m "feat(slots): wire thumbs into support-card index and detail"
  ```

### Task 9: Run Create — Legacy Select and form rows

**Files:**

- Modify: `resources/views/runs/create.blade.php`
- Create: `tests/Feature/RunsCreateFormPortraitTest.php`

- [ ] **Step 1: Failing test**

  ```php
  it('renders the trainee portrait on a Legacy Select row', function (): void {

```text
  $card = \App\Models\CharacterCard::factory()->create(['card_id' => 100101]);
  Storage::disk('local')->put('artwork/characters/portrait/trainee/256/100101.png', 'PNG');
  // Build a session-backed Legacy Select payload; the existing test fixture in
  // tests/Feature/RunCreateSurfaceTest.php shows how.
  // …then run the Legacy Select POST and assert the rendered form carries the slot.

  // (See RunCreateSurfaceTest for the surrounding payload wiring — keep
  //  this test narrow; assert only the rendered `<img>` line.)
```

  });

  ```text

- [ ] **Step 2: Run tests to verify they fail**

- [ ] **Step 3: Minimum edit**

  In the Legacy Select section and the create table:

  ```blade
  <x-character-portrait :card-id="$form->card_id" size-class="size-10" :name="$form->displayName()" :route-args="['slug' => $form->umamusume->slug]" />
  ```

- [ ] **Step 4: Run tests to verify they pass**

- [ ] **Step 5: Commit**

  ```bash
  git commit -m "feat(slots): wire portrait into run create Legacy Select"
  ```

### Task 10: Cross-doc updates + gate evidence

**Files:**

- Modify: `docs/research-scratch/DESIGN-CORPUS.md` section `## design-2.0.md` — change log row (the prose is already in §42 + §45a, land a dated row).
- Modify: `docs/research-scratch/PLANS-AND-BRIEFS.md` section `## screen-spec-2.0.md` — change log row for §34 footnote + §35.
- Modify: `DESIGN.md §4.7` — status word tone (mirror exists, slots do not; the Blade rendering of one slot is now also part of this file's binding rules by example).
- Modify: `SCREEN_SPEC.md §7-16` — status widens: the mirror exists, the per-screen wire-ups exist, what remains is the skill icon migration question.
- Modify: `AGENTS.md §10 commands` — already has `uma:fetch-art`; nothing changes.
- Modify: `README.md` — already has the command in the table; nothing changes.
- Modify: `PRD.md OQ-6` — narrow: the OQ-6 question is now about which skills get icons, not about whether the mechanism works.

- [ ] **Step 1: Land the change log rows**

  In each doc, find the change-log / open-question list and append a dated
  2026-10-05 row about the image-slot display work.

- [ ] **Step 2: Run the gate suite**

  ```bash
  vendor/bin/pint --dirty --format agent
  vendor/bin/phpstan analyse --no-progress --memory-limit=1G
  composer lore
  composer lore-code
  php -d memory_limit=1G artisan test --compact
  vendor/bin/pest --compact tests/Feature/DocCitationParityTest.php tests/Feature/DocSchemaDriftTest.php tests/Feature/LoreGateParityTest.php tests/Feature/EmptyStateCommandNamesTest.php
  python tools/doc_census.py
  ```

  Expected: `pint` reports `passed`; `phpstan` reports `[OK] No errors`;
  `composer lore` reports docs `238/77` and code `0/1` (or whatever the
  baseline reads at the moment — record the actual numbers); `composer
  lore-code` reports `46` hits with one new ruling (or the reading at the
  moment); the Pest `--compact` summary line is one larger than before this
  plan touched the suite (the new feature tests land); the four doc-gate
  tests are green; `tools/doc_census.py` prints no orphan-markdown findings
  and no `OUTDATED CLAIM` rows under the `## Image slot display` heading.

  Evidence rule (per `AGENTS.md §15`): attach the run output to the slice's
  hand-off report. A gate that was not run is reported as not run, never as
  passed. After the gate block, end-of-slice report goes through the
  format in `## Progress` below.

- [ ] **Step 3: Update the Progress section**

  Move each task's checkbox from `[ ]` to `[x]` in `## Progress` below as the
  slice lands. The plan and the Progress section are the same file so this
  step is one edit per slice; do not split.

- [ ] **Step 4: Commit**

  ```bash
  git add docs/proposals/design-2.0.md \

```text
      docs/proposals/screen-spec-2.0.md \
      docs/superpowers/plans/2026-10-05-image-slot-display.md \
      DESIGN.md \
      SCREEN_SPEC.md \
      PRD.md
```

  git commit -m "docs(slots): land change-log rows and progress baseline"

  ```text

  > **Editorial note, 2026-10-06 (not part of the plan).** The third path in this `git add` no longer
  > exists: the plan file was folded into this master and deleted, recoverable from `2de0c54`. The command
  > above is left verbatim as the record of what ran on 2026-10-05; re-run it without that path.

---

### Progress

| Task   | Description                                                                                                   | Status                      | Commit                 | Evidence                                                                                                                                                                                                                                                                                                                                                                   |
| ------ | ------------------------------------------------------------------------------------------------------------- | --------------------------- | ---------------------- | -------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------- |
| 1      | `ArtworkMirror::exists(string $kind, int $id): bool` + `storedPath()` / `disk()` lifts                        | `[x]`                       | `a0899a3`              | 10 cases green; pint, phpstan clean                                                                                                                                                                                                                                                                                                                                        |
| 2      | Streaming route `/artwork/{kind}/{id}` via `ArtworkAssetController::show`, allowlist on `kind`                | `[x]`                       | `ce376f7`              | 3 cases green (200 / 404 absent / 404 unknown kind); phpstan clean                                                                                                                                                                                                                                                                                                         |
| 3      | `ArtworkMirror::url(string $kind, int $id): ?string`                                                          | `[x]`                       | `fde4439`              | 12 cases green; the brief's route stub dropped after the real route resolved                                                                                                                                                                                                                                                                                               |
| 4      | `<x-character-portrait>` Blade component (`alt=""` when `decorative`)                                         | `[x]`                       | `bebdd74`, `898aa9e`   | 3 cases green; guard ordering proved by a hoist-RED experiment                                                                                                                                                                                                                                                                                                             |
| 5      | `<x-support-thumb>` Blade component                                                                           | `[x]`                       | `ac60f61`              | 2 cases green; mirror fidelity held                                                                                                                                                                                                                                                                                                                                        |
| 6      | Catalog detail Identity (`catalog/show.blade.php`) — `decorative`, `size-16`                                  | `[x]`                       | `53e5d12`              | 2 cases green; the `$activeCard ?? first` duplication in the view is recorded, not fixed                                                                                                                                                                                                                                                                                   |
| 7      | Catalog index Inertia page (`Catalog/Index.vue` + `CatalogController::index`) — `artworkURL` per row + form   | `[x]`                       | `b5547d6`, `625c86e`   | 2 Pest + 2 Playwright green; browser cases derived from the DOM after a fix round                                                                                                                                                                                                                                                                                          |
| 8      | Support-card index + detail (Blade) — `<x-support-thumb>` `size-12` / `size-16`                               | `[x]`                       | `7d7e5d6`              | 5 cases green; the id-swap guard proved by a swap experiment; full suite 1240 passed                                                                                                                                                                                                                                                                                       |
| 9      | Run Create / Legacy Select — `size-10`                                                                        | `[x]` **not implemented**   | —                      | **Blocked, no slice committed.** No placeable surface: the view's picker is a native `<select>` (an `<img>` cannot render inside `<option>`) plus a client-rendered combobox listbox, and on `trainer-desk-2.0` both the view and `trainee-combobox.ts` are deleted by the port. Recorded as unplaceable in `PRD.md` OQ-6, `SCREEN_SPEC.md` §7-16 and `docs/research-scratch/DESIGN-CORPUS.md` section `## design-2.0.md` §45a.   |
| 10     | Cross-doc change-log rows + gate run-down                                                                     | `[x]`                       | see below              | `DESIGN.md` §3 count, §4.7 status and geometry, `SCREEN_SPEC.md` §7-16 superseded-in-part, `PRD.md` OQ-6 narrowed, `docs/research-scratch/DESIGN-CORPUS.md` section `## design-2.0.md` §45a shipped-state note; full gate block in the Slice Log                                                                                                                                                                                  |

**Slice report format.** For each landing slice, append a dated block under
`## Slice Log` below in this shape (`Phase gate reporting format`):

```markdown
#### YYYY-MM-DD — Task N: <title>
- Files changed: <paths>
- Gates: pint `passed`, phpstan `[OK] No errors`, lore/docs <before>/<after> or
  `<count>/<count>` (unchanged), lore-code `<count>` (unchanged), full suite
  `<headline>` (e.g. `Tests: 1457 passed`), the four doc-gate tests green,
  doc_census clean on the heading.
- Deviations: <list, or `none`>.
- Open decisions: <list with named owner, or `none`>.
- Next slice: Task N+1, deferred reason <if any>.
```

---

### Slice Log

(Slice reports land here as each task commits. Entries are append-only;
neither renumbered nor rewritten.)

---

### Self-Review Notes (filed pre-development)

- **Spec coverage.** Each design-2.0 §45a row is a slice or a deliberate
  deferral: catalog index portrait + form, catalog detail Identity, support
  index + detail, run create / legacy select. Skill icons are the named
  deferral; the prose names the migration requirement.
- **Placeholders.** No `TODO`, no "implement later", no "add validation" step
  that doesn't carry the validation. `ArtworkAssetController` declares the
  allowlist on `kind`; the slot component declares the `decorative` flag;
  the test set asserts the boundary cases (mirrored 200, absent 404,
  unknown kind 404, absent in mirror 200 but absent on disk 404).
- **Type consistency.** `ArtworkMirror::exists(string, int)`,
  `url(string, int)` (and on the controller, `int $id` after the route's
  `whereNumber('id')`) agree across Tasks 1–3, 4–5, and the route
  registration in Task 2.
- **Lore-gate provenance.** All four doc/coded edits made in pre-development
  (design-2.0 §45a, screen-spec-2.0 §34 footnote + §35, the
  `stable`→`fixed focal-length` correction, and this plan file itself) read
  clean against `composer lore` and `composer lore-code`. The plan avoids
  enumerating the banned families inline (`AGENTS.md §18` keeps that list
  in `tools/lore.php` only); any future hit lands with an inline ruling
  per the §5 lore-gate rule.

---

### Erratum, 2026-10-05 (preserves every sentence above)

Filed per `AGENTS.md §11`: a dated claim that later proves wrong is corrected by appending, not by
rewriting. The four claims above that stopped being true:

1. **"per-screen wire-ups into the existing catalog / support-card / run-create Blade templates."**
   Three of those four wire-ups (`Task 6` catalog detail, `Task 8` support-card index and detail)
   targeted Blade templates that the A1 to A3 ports retired. The slots now live on the Vue pages:
   `Catalog/Show.vue`, `SupportCards/Index.vue`, `SupportCards/Show.vue` (`fff920f`).
2. **"Create: `resources/views/components/character-portrait.blade.php`"** (`Task 4`) and the
   `support-thumb.blade.php` twin (`Task 5`). Both files landed as written, then `b645048` deleted
   them with zero call sites remaining, which is `docs/research-scratch/PROCESS-PLANS.md` section `## frontend-development-plan.md` §5.1
   step 8 behaving as designed. One SFC, `ArtworkSlot.vue`, is now the single owner of the slot
   contract.
3. **"the slot component declares the `decorative` flag"** (Self-Review Notes, Type consistency).
   One flag blanking `alt` and `aria-label` together could not express a decorative image inside a
   named link, and could not express a no-action slot without an anchor. The three no-action slots
   therefore each rendered a focusable anchor with an empty accessible name, failing WCAG 2.2 AA
   4.1.2 Name, Role, Value on the slice whose binding constraint was WCAG 2.2 AA. `ArtworkSlot.vue`
   splits `alt` from `href`/`linkLabel`; with no `href` a slot renders no anchor at all, so the
   defect cannot be expressed.
4. **"Task 9 ... Run Create / Legacy Select."** Never built, and not buildable as specified: that
   picker is a native `<select>`, and an `<img>` cannot render inside an `<option>`. Recorded as
   unplaceable in `PRD.md` OQ-6, `SCREEN_SPEC.md` §7-16 and `docs/research-scratch/DESIGN-CORPUS.md` section `## design-2.0.md` §45a.

**Process note, so the record is complete.** Finding 3 was raised by review while `Task 4` was in
flight and named the axe rule it would fail. It was deferred as a minor on the grounds that the
slice's file list excluded the component's contract, then carried unchanged into Tasks 5, 6 and 8.
A finding that names the plan's own binding constraint is not scope-fenceable: it gets fixed, or it
gets a written ruling against it. Deferring it into a minor list is how a conformance failure
shipped.

**What still stands.** `ArtworkMirror::exists`, `url`, `storedPath` and `disk` (`Tasks 1`, `3`), the
`/artwork/{kind}/{id}` loopback route with its `kind` allowlist and `whereNumber` guard (`Task 2`),
the catalog index wire-up (`Task 7`), the absent-file-renders-nothing rule, the local-`src`-only
rule, and the `skills.iconid` deferral. All five remain as specified.

---

## E4 — Trackblazer scenario panel (SCREEN-016), 2026-10-07

Slice row: `docs/research-scratch/PROCESS-PLANS.md` section `## frontend-development-plan.md` line 195 (E4, SCREEN-016, depends on E1,
plan §9 E4). Status before this slice: `config/scenarios.php` `trackblazer` entry present and
verified 2026-09-27 against three Global prose sources; matrix flags `grade_objectives`,
`shop`, `epithet_routes` ON; matrix flag `race_calendar` OFF (no mandatory calendar). The
E1 shell (`ScenarioPanel.vue`) plus `UraPanel.vue` and `UnityCupPanel.vue` already register
`career_goals`, `team_race`, `team_rank_ladder`. No Trackblazer renderer exists; the
Cockpit falls back to `WidgetFallback` on every Trackblazer flag.

Plan phase per `spec-driven-development`; task list per `planning-and-task-breakdown`.

### What this slice is

The Trackblazer scenario panel set, mounted inside the Cockpit's scenario region as one
component per ON flag: grade-points meter, Pro Shop catalogue + purchase flow, epithet
checklist, rival races from `RaceCatalogSlot`, and the points-league finale. The
component file name `TrackblazerPanel.vue` is fixed by the brief; the component branches
on the flag key (`props.name`), never on a scenario name or label — a fifth scenario is
one config entry and zero component edits (gate G-33, `D-240`, plan §9 E1, design-2.0 §25).

### Files

- Modify: `app/Http/Controllers/Career/CockpitController.php` — `scenarioSection()` gains
  `grade`, `shop`, `epithet`, `rival` and `finale_official_title_absence` keys, each
  `null` for any scenario that does not compose the matching flag so the shape stays
  uniform across the four Global scenarios.
- Modify: `resources/js/types.ts` — extend `ScenarioPanelSection` with the same keys,
  each nullable.
- Create: `resources/js/components/scenario/TrackblazerPanel.vue` — branches on
  `props.name` to render `grade_objectives`, `shop`, or `epithet_routes`. Same props
  contract as `UraPanel.vue` and `UnityCupPanel.vue` (`{ scenario, label, kind, name }`).
- Modify: `resources/js/components/scenario/ScenarioPanel.vue` — register
  `TrackblazerPanel` against `grade_objectives`, `shop`, `epithet_routes`. No change
  beyond three `register(...)` lines.
- Modify: `resources/js/pages/Career/Cockpit.vue` — add a scenario-actions subregion
  immediately above the existing ActionGrid that renders a single "Shop" button when
  `scenario.shop !== null` (Trackblazer) and scrolls/focuses `#trackblazer-shop-heading`
  on click (WCAG 2.4.11, plan §12). The button is hidden when there is no shop section.
- Modify: `SCREEN_SPEC.md` — add the SCREEN-016 / `SCR-CAR-022` row, with empty,
  loading and error states on TrackblazerPanel.
- Create: `tests/Feature/TrackblazerPanelTest.php` — props contract under a
  Trackblazer run; no scenario identity in any assertion; catalogue is in
  `config('shop_items')` order; a rejected purchase yields field-bound errors.
- Create: `tests/browser/trackblazer-panel.spec.ts` — keyboard path through the shop
  region, purchase round-trip + rejected-purchase error text bound to its input by
  `aria-describedby`, epithet glyph+text, 44px control sweep, 320px reflow, axe A+AA
  scoped to `#app`.

### Registration and the three mounts

The shell renders one component per ON flag. Trackblazer has three ON: `grade_objectives`,
`shop`, `epithet_routes`. `TrackblazerPanel` registers for the three flags and branches
on `props.name`, the flag key the shell mounted it for. `race_calendar` is OFF for
Trackblazer per `config/scenarios.php`; rival races are drawn directly from
`RaceCatalogSlot` rather than via a separate panel flag, because the matrix has them
off but the brief still names them as facts to surface.

- `name === 'grade_objectives'` — grade-points meter (working-toward + bar + ladder +
  surplus line + rotation countdown).
- `name === 'shop'` — catalogue + purchased list + rotation countdown + purchase form +
  finale absence.
- `name === 'epithet_routes'` — checklist (earned / open / unverifiable) rendered as
  ordered `<li>` with a pill badge per row.

### Shop: catalogue-order, no recommendation, no default, no highlight

The catalogue list is the §E4 rule. Items render in
`config('scenarios.scenarios.trackblazer.shop_items')` order. Item, cost, effect and
rotation-time facts print verbatim from config; no "Recommended purchase" card, no
`recommended` flag, no "best value" sort or highlight, no default-selected item. The
state copy says in text that the shop rotation is not modelled, so what the panel
prints is the catalogue and only the catalogue. A fifth catalogue entry from config
alone would land at the same place with no component edit.

### Purchase write: validate at the boundary

`runs.purchases.store` and its `StoreShopPurchaseRequest` are the authority: the
catalogue, the price, and the held-copies cap (`shop.max_copies_per_item`) all reject
bad submissions before they reach the database. The Vue form only posts
`{turn, item, cost, effect}`. Inputs are labelled by `for`/`id`. Error text is
rendered outside the `<label>` so the input's accessible name stays clean, and
`aria-describedby` ties each per-field message to its input (WCAG 3.3.1, plan §12).
A purchase form is rendered only when `scenario.shop !== null`.

### Quick access from the Cockpit's action area

A small scenario-actions subregion above the ActionGrid renders a single "Shop"
button when Trackblazer is the active scenario. On click the button scrolls the
shop region into view and focuses its heading (`tabindex="-1"`). Other scenarios
use the ActionGrid only; no other scenario-actions entries are added by E4.

### Cited inputs (every number or label below traces to the source noted)

- `trackblazer` matrix entry: `config/scenarios.php` lines 211-362 (verified
  2026-09-27 against `scenarios.json` caps and three Global prose sources).
- Shop catalogue rows: `docs/research-scratch/SCENARIO-PUBLISHER-REFERENCES.md`
  section "Full Shop Item List" (GameTora, 2026). Source rows carry item name
  spelled as the client spells it; `Good-Luck Charm`'s "Luck" is the verbatim
  client item name and stays outside the C-4 copy ban per its docblock.
- Epithet routes: same reference, section "Epithets (Race Route Bonuses)"
  (uma.guide, after 2026-07-01 rework).
- Finale kind and races: `config/scenarios.php` line 358, `finale.kind === 'points_league'`,
  `finale.races === 3`.
- `Twinkle Star Climax` exclusion: `docs/research-scratch/PLANS-AND-BRIEFS.md` section `## screen-spec-2.0.md` line 138 conflict row 31;
  `docs/UMAMUSUME_REFERENCE.md` §7 conflict row 31; the named-absence `title` cites
  the conflict row.
- Purchase validation rules and held-copies cap: `app/Http/Requests/StoreShopPurchaseRequest.php`
  docblock + body.
- Trust vocabulary: `docs/research-scratch/DESIGN-CORPUS.md` section `## design-2.0.md` §49 (`Confirmed / Calculated / Estimated / Unknown`)
  and `ADR-0020` §2.
- 44px / `min-h-11` target rule + WCAG 2.4.11: `docs/research-scratch/PROCESS-PLANS.md` section `## frontend-development-plan.md` §12.

### Not built, and why

- Shop item recommendation (rotation not modelled; plan §3 Knowledge groundings).
- Shop balance (no earning-side writer; rendered as "Shop Coins: not yet recorded"
  with `title`, same carve-out as the original Blade).
- Win probability, readiness band, race risk (ADR-0016, plan §3).
- Official finale title (conflict row 31 UNVERIFIED; named absence with `title`).
- Scenario-specific advisor output for the rival races (ADR-0020 §3; rivalry is
  rendered as catalogued facts only).

### Acceptance

- `grep` over the diff finds none of: `recommended`, `best value`, `Twinkle Star
  Climax` outside the named-absence phrase.
- `TrackblazerPanelTest` asserts: Trackblazer scenario exposes `grade`, `shop`,
  `epithet` sections in `scenarioSection()`; `shop.catalogue` is in `shop_items` order;
  `shop` carries no `recommended` field; no scenario identity in any assertion key;
  `StoreShopPurchaseRequest` rejects an off-catalogue item and a price mismatch with
  field-bound errors; a recorded purchase reads back through the same payload path
  (`ShopPurchasePayload::fromArray()`) the Blade form used.
- `trackblazer-panel.spec.ts`: keyboard reading through the shop, purchase round-trip,
  rejected-purchase error text bound to its `<input>` by `aria-describedby`, epithet
  glyph+text, 44px control sweep, 320px reflow, axe A+AA inside `#app` if the
  dependency is installed.
- Hand-off sequence: targeted tests → `php artisan test --compact` →
  `vendor/bin/pint --dirty --format agent` → `vendor/bin/phpstan analyse
  --no-progress --memory-limit=1G` → `npm run typecheck` → `npm run build` →
  `npm run test:browser` → `composer lore` + `composer lore-code` with a one-line
  ruling per hit. An unrun gate is reported as not run, per AGENTS.md §9.
- `SCREEN_SPEC.md` SCR-CAR-022 row exists in §2 with empty / loading / error sub-rows.
- Diff scope: none of URA / Unity Cup / Cockpit / Scenario is touched beyond the
  registration lines and a Cockpit scenario-actions subregion; no Blade component
  is reintroduced.

### Open questions

- A "Twinkle Star Climax" patch in any official client update would let the
  finale absence read a real name. Out of scope; record in
  `docs/UMAMUSUME_REFERENCE.md` §4.6 if and when it lands. The slice leaves the
  absence in place.
- A future `shop.rotation` writer would let the catalogue render `on sale` or
  `limited` flags. The component contract supports them; the catalogue that ships
  with E4 does not carry any flag because no rotation exists.

---

## E6 — Our Grand Concert baseline strip (SCR-017), 2026-10-07

Written before code. The dispatch brief for this slice is
`docs/research-scratch/PROCESS-PLANS.md`, section `## Trainer-Docs-2.0-E-Prompts.md`, §"PROMPT E6"
(repointed 2026-10-12 when the original `docs/Trainer-Docs-2.0-E-Prompts.md` was consolidated and
deleted). Three of its premises do not hold on
this tree; they are corrected in the section below rather than followed, because following
them would put a false sentence in shipped copy and would take a ruling the plan reserves
to the owner.

### What this slice is

The fourth `[Global]` scenario composes no panel: `our_grand_concert.panels` is false on
every flag, so `ScenarioPanel.vue`'s panel region has nothing to mount. That region's
`v-else` branch is the baseline strip, and this slice is what goes in it. It is not a new
component and not a new flag: there is no flag to register, because "every panel off" is
the absence of a flag. The brief's phrase "registered in the E1 registry for the every
panel off case" is therefore read as the shell's own empty branch, and the strip stays
config-driven so a fifth scenario inherits it for one config entry (gate G-33).

### Premise corrections (dispatch brief vs. this tree)

1. **`documented => false` is stale.** `config/scenarios.php` `our_grand_concert.documented`
   reads `true`, in `HEAD` and in the working tree. The entry's own comment dates the flip
   to the 2026-10-05 primary read and states that `documented` is a provenance marker, not
   a rendering switch. Plan §9's E6 row and the brief both still quote `false`.
2. **"Songs, lessons and Performance Tokens do not exist in the corpus" is false.**
   `docs/scenarios/07-grand-concert.md` is a 32.9 KB, 262-line sourced guide, refilled
   2026-10-05 from a primary read; its own header records that Global notice 905 prints
   *Performance*, *Promo Concert*, *Grand Live*, *lessons*, *songs* and *concert
   techniques*. What is **not** measured is the text on the client's screens (0 frames in
   the screenshot corpus), which is the reason the panels stay off. The strip states that
   reason instead. The brief also calls the file a stub; it stopped being one on 2026-10-05.
3. **The badge key was not this slice's to choose.** Plan §4.1 item 6 and
   `SCREEN_SPEC.md:2425` reserve "which key drives the badge" to the owner alone. The owner
   ruled on 2026-10-07: **`partially_documented` drives the badge.** That is the one
   controller line §4.1 item 6 anticipated changing in a follow-up commit, and E6 is that
   follow-up. `partially_documented` and its reader (`DatabaseController` / the Scenarios
   screen, D17) are still uncommitted in the shared working tree, so E6 depends on a
   sibling slice's unlanded work and says so here.

Two smaller findings, recorded and not acted on: the config cites `DESIGN.md §6.22` for the
base-plus-bonus rendering rule, and `DESIGN.md` has no §6.22 (the rule survives in the
config comment itself); and gate G-41's own premise ("a scenario with no guide") is now
false for this scenario, which the config comment already records as the gate registry's
wording to amend, not a slice's.

### Files

| File | Change |
| --- | --- |
| `config/scenarios.php` | `our_grand_concert` gains `panel_absence` and `panel_absence_title` (display strings for the strip's one honesty line). Nothing else moves; `documented` stays a provenance marker. |
| `app/Http/Controllers/Career/CockpitController.php` | `scenarioSection()`: the badge line now reads `partially_documented`; three new uniform-shape keys, `caps`, `panel_absence`, `panel_absence_title`. |
| `resources/js/components/scenario/ScenarioPanel.vue` | The every-panel-off branch becomes the baseline strip. |
| `resources/js/types.ts` | `ScenarioSection` gains the three keys. |
| `tests/Feature/GrandConcertPanelTest.php` | New. |
| `tests/Feature/ScenarioPanelTest.php` | Pinned key list gains the three keys; the documentation-flag case now pins `partially_documented`. |
| `tests/browser/grand-concert-panel.spec.ts` | New. |
| `tests/browser/scenario-panel.spec.ts` | E1's Grand Concert case: `Documented` becomes `Partially documented`, and the empty-state sentence becomes the strip. |
| `SCREEN_SPEC.md` | §2 row `SCR-CAR-024` and its state table. `SCR-CAR-023` is E5's Scenario Race Planner, which landed in the same working tree. |

**Dependency:** E6 requires D17's `partially_documented` reader (`DatabaseController` / Scenarios screen) to be present and committed; both must land together or E6's badge reverts to the prior `documented` key. The two boolean keys in `config/scenarios.php` are disambiguated by comment: `documented` is a provenance marker recording whether a guide is held (no screen reads it), while `partially_documented` is the rendering switch that drives `ScenarioStatusBadge`.

No new package, no migration, no route, no ADR. `ScenarioStatusBadge`, `ResourceMeter`,
`AlertRow` and `ProvenanceBadge` are used as they are; nothing Grand-Concert-specific is
added beyond the strip.

### Props contract

Three keys join the `scenario` section, uniform for all four scenarios so one renderer can
draw any of them without branching:

```php
'caps' => [
    'verified_at' => '2026-09-27',          // config('scenarios.verified_at'), a verification date
    'base' => 1200,                          // config('scenarios.base_cap')
    'hard_cap' => 2000,                      // config('scenarios.hard_cap')
    'source_title' => '...',                 // what the caps are and where they come from
    'rows' => [                              // one row per config('scenarios.stat_order') stat
        ['key' => 'Speed', 'label' => 'Speed', 'base' => 1200, 'bonus' => 400, 'cap' => 1600],
        // ... Stamina 1300, Power 1300, Guts 1500, Wit 1300
    ],
],
'panel_absence' => '...' | null,             // the scenario's own reason its panels are off
'panel_absence_title' => '...' | null,       // where that reason is recorded
```

`base` and `bonus` are emitted as separate terms, never collapsed into `cap`, because the
config's own header forbids collapsing them and one JP scenario carries a negative bonus.
`cap` is read through `ScenarioCaps::caps()`, the single owner of the arithmetic
(`ADR-0015`), so the strip and the stat bars cannot disagree. `panel_absence` is null for
every scenario that declares none, which is the shape `grade`/`shop`/`epithet` already use.

### The baseline strip

Rendered only where the panel region has nothing to mount. A scenario with every flag off
draws, in this order:

1. the label and `ScenarioStatusBadge` (E1, unchanged);
2. the ruleset `N/A` line and its title (E1, unchanged);
3. the sentence saying no panel is composed (E1, unchanged);
4. **the caps table**: one row per stat with `base`, `bonus` and `cap` as labelled columns,
   under a `ProvenanceBadge state="confirmed"` whose title names the source, with the
   verification date printed beside it;
5. **the honesty line**, when the scenario declares one, as an `AlertRow` carrying a
   `ProvenanceBadge state="unknown"` whose title points at `docs/scenarios/07-grand-concert.md`;
6. **the advisor-scope line**: `recommendations_absence`, already in the payload and
   rendered nowhere until now, so the Trainer is told the scenario contributes no advice of
   its own and the recommendation is the generic engine's.

The caps table is drawn only for a scenario the run declared (`declared === true`); an
undeclared run keeps E1's sentence and shows no caps, because lending it the baseline
entry's caps would state a composition nobody chose.

### Cited inputs

Every number and string below traces to a source, and nothing else appears:

- the five caps, `1200 + bonus`, from `config/scenarios.php` `our_grand_concert.cap_bonus`
  (Speed 400, Stamina 100, Power 100, Guts 300, Wit 100) over `base_cap` 1200, which is the
  1600/1300/1300/1500/1300 the plan §3 row records as corroborated 2026-10-05 by two
  independent sources;
- the verification date, `config('scenarios.verified_at')` = `2026-09-27`, labelled a
  verification date and not an "updated" one;
- the honesty line and its title, new display strings on the scenario's own config entry,
  stating the reason `docs/scenarios/07-grand-concert.md` gives in its own header (0 frames
  captured, so the client's screen text is unmeasured);
- the advisor-scope line, `recommendations_absence`, which the controller already composes.

### Tests

`GrandConcertPanelTest` (Pest, feature):

- **G-41 as an assertion.** Every key of `config('scenarios.panel_labels')` is present with
  `on === false` for `our_grand_concert`, and the section carries the strip's keys; the same
  run in a scenario with a flag on carries a different set. The assertion reads the flag set
  from config, so E2's seventh flag cannot rot it.
- **No invented mechanic.** The whole Inertia payload for the run is walked, keys and
  values, and asserted to contain none of a small list of strings no source measures. The
  list is the brief's own three nouns plus the client's song/skill vocabulary, and the case
  states that it is a floor, not a proof of absence.
- **The badge key.** `scenario.documented` is `false` for `our_grand_concert` and `true` for
  the other three, which is the owner's 2026-10-07 ruling pinned.
- **The caps rows.** Five rows in `stat_order`, each `base + bonus === cap`, and `cap`
  equal to `ScenarioCaps::caps()` for the same key, which is the ADR-0015 agreement.
- **The absence is a named absence.** `panel_absence` and its title are non-null for
  `our_grand_concert` and null for the other three.
- **A fifth scenario gets the strip from config alone.** One config entry with every flag
  off, zero component edits: the strip's keys arrive and nothing scenario-specific does.

`ScenarioPanelTest` gains the three keys in its pinned list (the pin exists to make a
contract change test-visible) and its documentation case moves from `documented` to
`partially_documented`.

### Acceptance

G-41's assertion is a test; no songs, lessons or token strings appear in the rendered props
or markup; a grep proves no scenario-name branching in the new code; and
`grand-concert-panel.spec.ts` covers the badge text, the caps list, the honesty sentence and
its title, the 320 px reflow and the 44 px sweep in a real browser.

### Extension point, for the owner

When the client's own strings are captured, this scenario gains surfaces by **one config
edit plus new widget renderers**, never by editing the strip: a widget key added to
`our_grand_concert.widgets` reaches `ResourceMeter` or a newly registered renderer through
the existing chain, and a flag added to `panels` reaches the registry. The strip needs no
change for either. The `panel_absence` line is what should be dropped at that point, and it
is one config key.

### Open questions

1. `partially_documented` and its D17 reader are uncommitted in the shared working tree.
   E6 depends on them landing; if D17 is reverted, the badge silently reverts to
   `Documented` and E6's browser case goes red. Owner call: land D17 before or with E6.
2. The strip renders the caps the header's stat bars already render. The owner chose the
   strip list on 2026-10-07 (G-41's own wording is "baseline strip plus known caps"), so the
   duplication is deliberate; a later pass could drop one of the two.
3. `config/scenarios.php` cites `DESIGN.md §6.22`, which does not exist. Left as found; it
   is a documentation defect, not this slice's.

---

## Database reference views — Events, Shop Items, Sparks (SCR-SYS-008/009/010), 2026-10-08

Completes `SCREEN-023`, whose Sections list already carries all eight areas. The D17 slice
landed five and declared the other three "Not in this build"; both halves of that label are
now false (the sections are in the brief, and the source data exists). The section is deleted,
not softened.

**Numbering note.** `SCR-SYS-006` and `SCR-SYS-007` are already taken (the race database and
the scenario matrix, both documented inside the `SCR-SYS-005` section). The three new areas are
therefore `SCR-SYS-008`, `009` and `010`, and they extend the `SCR-SYS-005` section the way
`006` and `007` do.

### Corpus re-check (2026-10-08), and what moved

Read against `docs/UMAMUSUME_REFERENCE.md`:

| Section | Read | Delta from the dispatch brief |
| --- | --- | --- |
| §4.4 Recurring event types | 2083-2102 | The table holds **ten** rows, not nine: the brief's list omits *Story-unlock campaign*, which is row 10. Cadence figures are stated per server and are dated to the export read. |
| §1.6.10 Consumables | 790-811 | The table holds **eight** rows plus **two** currency items described in the prose above it (the Cleat family and the Goddess Statue). The brief's list matches the eight; the two currencies are added because the brief's own text names them. |
| §1.5.2 Factor categories | 615-630 | **Six** rows; the record counts are 5 / 10 / 268 / 452 / 37 / 34 = **806**, which matches the brief's 806 across six categories. The doc also notes 68 further records in an `other` bucket that "no page in this pass explains"; they are counted, not described. *Ruled 2026-10-08 by the owner, and shipped with this slice:* the Sparks view names that remainder in one muted line under the categories instead of leaving the sum to imply otherwise. See the `factors.json` cross-check below for what the bucket actually holds. |
| §1.5.3 Star ratings and ceilings | 632-648 | The star-roll-odds table is three rows by final stat value; the aptitude-roll rule and the three ceilings are prose. Rendered as the odds table plus one prose row. |
| §1.6.5 Racing Carnival | 756-760 | Prices are guide-sourced: Rainbow Crystal Shards 30,000 Pt, Gold 15,000 Pt, Scout and Support tickets 6,000 Pt. Used only in the Shop Items drawer note for the two Crystal Shard rows. |
| §2.2.3 Trackblazer | 909-916 | The Pro Shop is the scenario's own block; the item class already lives in `config/scenarios.php` `trackblazer.shop_items` and is **reused, not duplicated**. |

**Local export availability, checked and reported.** `database/seeders/data/` holds
`characters`, `gametora-characters`, `race-tier-labels`, `race_instances`, `races`, `skills`,
`support-cards`, `support_effects` and `ura-races`. It holds **no** `factors.json`, **no**
`items.json` and **no** `events__*.json`; `storage/app/private/` holds only `artwork` and
`snapshots`. Per the brief's rule, no fetch step is added in this slice: the views render from
the doc's tables and cite the doc as the source, not the export.

**Dated correction, 2026-10-08, same session, before hand-off: that read was wrong, and the paragraph
above is kept as a record of the mistake.** The exports DO exist on this machine, under
`research-scratch/data/json/` (`factors.json`, `items.json`, `career-rivals.json`, the `events__*` and
`en__events__*` files), with file dates 2026-09-27 and 2026-09-28. The original read looked only in
`database/seeders/data/` and `storage/app/private/` and stopped there. The conclusion the slice acted on
still stands, for a different reason: that directory is **gitignored**, so no fresh worktree is
guaranteed to hold it and no runtime path reads it. The rows stay transcribed, and the files were then
used as the cross-check they should have been from the start:

- `factors.json`: `blue` 5, `pink` 10, `skill` 452, `race` 37, `scenario` 34, `other` 336, total **874**.
  Five of the six counts the Sparks view prints match those cells exactly. The sixth, 268, is the guide's
  own reading of part of `other`, and **336 − 268 = 68** is the remainder the guide calls counted but not
  described. So the 806 the six rows sum to is the described total, not the export's 874.
- `items.json`: 205 records. Four of the Shop Items view's ten rows match a client name in it exactly
  (Alarm Clock, Toughness 30, Pleasing Parfait, Goddess Statue); the other six are the guide's grouped
  labels, several client items under one row, so no single name matches them.

### Data home

A new `config/reference.php`, transcribed from the doc, with one key per view and a `source`
block per view (section anchor, doc path, "how to re-check"). This follows the D17
**config-derived** pattern the Scenarios view already established, needs **no migration**, and
keeps the tables in one versioned artifact. It is not an inventory: the Sparks key holds six
category rows, not the 806 records the doc counts.

`config/scenarios.php` is **read, not edited**: the Shop Items view reuses
`config('scenarios.scenarios.trackblazer.shop_items')` for its scenario block.

### Provenance mapping (stated once, applied per row)

The prompt's own definition is the rule: *Confirmed* = sourced with a URL or an S-file;
*Estimated* = a third-party guide or an export read this build cannot re-verify; *Unknown* =
the doc marks the value ❌ UNVERIFIED; *Calculated* = a value this app derives (none here).
The state is authored per row in `config/reference.php` and passed through unchanged; the view
renders it with `ProvenanceBadge.vue`, the single owner of the four glyphs. No second badge.

### Files

| File | Change |
| --- | --- |
| `config/reference.php` | New. The three tables plus per-view `source` blocks. |
| `app/Http/Controllers/DatabaseController.php` | Three methods: `events()`, `shopItems()`, `sparks()`. Each `Inertia::render` with an explicit array. `index()` gains three areas. |
| `routes/web.php` | Three `Route::get('/database/...')` lines. |
| `resources/js/pages/Database/Index.vue` | "Not in this build" section deleted; three areas added. |
| `resources/js/pages/Database/Events.vue` | New. |
| `resources/js/pages/Database/ShopItems.vue` | New. |
| `resources/js/pages/Database/Sparks.vue` | New. |
| `resources/js/components/database/ReferenceDrawer.vue` | New. One drawer shared by all three views (§32). |
| `resources/js/components/database/SourceNote.vue` | New. The §48 metadata row, shared. |
| `tests/Feature/DatabaseTest.php` | Extended: three new cases; the hub case's area count moves 5 → 8. |
| `tests/browser/database.spec.ts` | Extended: the hub's deferred-section case is replaced; three view cases added. |
| `SCREEN_SPEC.md` | `SCR-SYS-008` / `SCR-SYS-009` / `SCR-SYS-010` rows and sections; `SCR-SYS-005`'s row notes the completion. |

### Props contract

```php
// Database/Events
'events' => [
    'rows' => [ ['name','servers'=>['JP'|'Global'],'cadence','mechanics','state','source_title'], ... ],
    'recheck' => ['text' => '...', 'url' => '...'],
    'source' => ['section' => '§4.4', 'anchor' => '2026-10-02', 'doc' => 'docs/UMAMUSUME_REFERENCE.md'],
    'totalCount' => 10,
],

// Database/ShopItems
'shopItems' => [
    'rows' => [ ['name','effect','spend_site','kind' => 'consumable'|'currency','state','source_title','detail'], ... ],
    'scenarioShop' => ['label' => 'Trackblazer', 'rotation_turns' => 6, 'items' => [['name','cost','effect'], ...]],
    'source' => ['section' => '§1.6.10', ...],
    'totalCount' => 10,
],

// Database/Sparks
'sparks' => [
    'categories' => [ ['category','jp_name','global_name','effect','records','state','source_title'], ... ],
    'rollOdds' => [ ['band','one_star','two_stars','three_stars'], ... ],
    'source' => ['section' => '§1.5.2', ...],
    'totalCount' => 6,
],
```

Every figure is a string or an int from the config; no Eloquent model crosses the wire.

### What is deliberately not built

Per the design brief's "does not apply" list, stated so a reviewer can check the boundary: no
recommendation, no confidence, no risk, no chart, no primary action, no Expert-mode toggle, no
modal. The drawer is §32's detail pattern, not §31's modal. No scenario visual language is
borrowed — the Shop Items view draws the corpus table, not §28's Trackblazer panel.

### Tests

`DatabaseTest` gains: the hub has **8** areas and no deferred section; `Database/Events` with
10 rows, the first named, a server list, and one row carrying `unknown`; `Database/ShopItems`
with 10 rows, the reused Trackblazer block present with its **19** config items, and a `currency`
row; `Database/Sparks` with 6 categories summing to 806 and the three-row odds table.

`database.spec.ts` gains: each view renders its heading and rows; the provenance badge's
accessible name is read with `getByRole('img', { name })` and never `toHaveText`; a drawer
opens on row click and returns focus to the row on close; the 44 px sweep on `main a, main
button`; 320 px reflow; the empty-state copy is present in the markup.

### Acceptance

`php artisan test --compact` green; Pint, PHPStan L6, `npm run typecheck`, `npm run build`,
`npm run test:browser` green; `composer lore` + `composer lore-code` clean on the new files
with a ruling per hit. Committed on explicit pathspecs only (KI-70); the E1–E6 career files
stay untouched.

### Open questions for the owner

1. **Should the 68 unexplained `other` records be surfaced?** The doc counts them and says no page
   explains them. Rendering a row for them would be honest (a count with an `Unknown` badge) but the
   brief does not ask for it. Left out, stated here. **Ruled 2026-10-08 by the owner: closed by a named
   absence in copy, not by a row.** `Sparks.vue` prints one muted line under the categories carrying the
   guide's own sentence; the view's sum stays 806 because that is the described total, and the line says
   the export holds more than that. No seventh category row and no invented badge, and no new screen id.
2. **A future export snapshot would upgrade the provenance.** Written before the cross-check, so its
   premise was wrong: `factors.json` and `items.json` ARE held on a developed machine, under the
   gitignored `research-scratch/data/json/`. What is still owed is a *committed* snapshot of the subset
   these views print, which is what would let the Sparks counts and the Shop Items effect strings move
   from doc-cited to export-cited with a `source_url` and a `fetched_at`. The config's `source` block is
   where that lands. Until then the two `recheck` strings carry the 2026-10-08 read and its limits.

### Tree state at hand-off, 2026-10-08 (measured, so the next session does not rediscover it)

- `HEAD` is `7b04b6c` and `master` is **7 ahead of `origin/master`**, unpushed (the O-1 push gate is still
  the owner's to lift). This slice's commit is the first to move `HEAD` since `b5d69be`.
- Port 8127 carries **two** listeners, PIDs `30320` and `30528`. Neither belongs to a committed slice, and
  a `php -S` on that port is what every session's Playwright config reuses. Per KI-73 its owner reaps it;
  this slice did not kill either listener and ran its browser gate against the one that answered `200`.
- The dev database holds **12 `training_runs`** rows and **0 `veterans`**. Those runs are other sessions'
  leaks, not this slice's: this slice writes no table at all, and its three views read `config/reference.php`
  and `config/scenarios.php` only.
- `app/Http/Controllers/PreferenceController.php` carries a peer's D18b work (138 uncommitted lines) plus one
  import line that `pint --dirty` removed during this hand-off. It is not in this commit and must not be
  reverted to undo that line. See KI-75.
- `KNOWN-ISSUES.md` gained **KI-75** (Pint in a shared tree) and **KI-77** (the tracked route file importing
  seven untracked controllers). KI-76 was taken between drafting and writing by another session's Vite-SSR
  entry, which is also the mechanism behind this slice's `public/hot` harness failures; the numbering
  collision is recorded inside KI-77 because the register has no duplicate-number check.
- **Numbering collision, owed to the register (KI-70/KI-71/KI-76).** The file holds two headings numbered
  KI-70 (`KNOWN-ISSUES.md:858` and `:991`) and two numbered KI-71 (`:928` and `:1044`), and KI-76 itself is
  shared with another session's Vite-SSR entry. KI-77's own text names all three. Nothing is renumbered here:
  the register forbids it and each collision has inbound anchors in this plan and in `KNOWN-ISSUES.md`.
- `public/hot` appears and disappears as sessions run `npm run dev`. The clean browser-harness state is **no
  `public/hot` file** with `public/build/manifest.json` present: check the file and the Vite origin
  (`curl -m 8 -o /dev/null -w '%{http_code}' http://127.0.0.1:5174/resources/js/spa.ts`) before reading a
  180-second per-case timeout as a page defect.

---

## provenance-and-absence-contracts.md

> **Augmented 2026-10-10 (Round 14, augment-only).** Source: `docs/proposals/provenance-and-absence-contracts.md` (839 lines), reproduced verbatim below with headings demoted one level. The source file remains in place; no citation was repointed and nothing was deleted.
>
> **Refreshed and consolidated 2026-10-15 (Round 16, owner-ordered).** By 2026-10-12 the source had grown to 889 lines (§22, the 2026-10-11 Phase G preflight, plus a citation repoint inside §22), so this section was re-embedded from the current source and the original was deleted from `docs/proposals/` after line-by-line verification (0 missing). Line and section numbers inside this section follow the 889-line source. Cite this anchor, not the deleted path.

## Provenance and absence contracts — reconciliation proposal

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

### 0. What governs what

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

### 1. Provenance vocabulary

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
| **Estimated** | Two candidate meanings, and the shipped chips follow the one the accepted ADR does **not** describe. (a) `ADR-0001` §3: the tool's own model output, **off by default**, formula and parameters on the same surface, labelled as this tool's model and not the game's. (b) `config/reference.php:31`: "the doc backs it only with a third-party guide, or marks the contents unread" — a *weak source*, not a model. | All four shipped chips are meaning (b): `UnityCupPanel.vue:110` (the +30 read beside the S emblem), `:164` (a band table transcribed from one publisher), `InheritanceEvent.vue:249` ("Sourced probabilities from the chosen parents and grandparents. **No outcome is computed**"), and `config/reference.php:129`. **Meaning (a) is implemented nowhere:** the `failure_estimate` preference is stored and read back but renders no number — `tests/Feature/PreferenceControlsTest.php:24` states the reason in its own docblock, "it shows no number, because there is no number", and `TrainerAdvisor` ships the two bands only. So no chip today claims a modelled figure, and the opt-in half of `ADR-0001` §3 is an unbuilt option, not a violated rule | A sourced value (that is `Confirmed` under meaning (a), `Estimated` under meaning (b) — the two readings disagree here); a game mechanic presented as fact; any win probability (`ADR-0016`, `PRD.md` §6.11, and `docs/research-scratch/DESIGN-CORPUS.md` section `## design-2.0.md` §49's own "Estimated win chance: 82%" example is the banned figure, held by that document's governance banner) | **The word is authorized by two artifacts on different axes.** `ADR-0001` §3 authorizes (a) and the app does not do (a). `config/reference.php` defines (b) and the app does do (b), outside the advisor scope |
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

#### 1a. Runtime inventory of the label, deduplicated

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

### 2. Absence versus provenance ownership

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

### 3. Making load-bearing constants visible

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

### 4. KI-69 Class 3 — the fixture contract

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

### 5. CareerStatePanel absence interaction

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

### 6. SaveVeteran parent-rank wording

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

### 7. What each approval would change

| Approval | Files that would change |
|---|---|
| §1 vocabulary definitions | `DESIGN.md` §5 (new), then `TrainingCard.vue:234`/`:242` and `ScenarioPanel.vue:204` labels if the reading/derivation rule is adopted, and the `SCREEN_SPEC.md` SCR-CAR-012 Figure/Source rows `:1957`-`:1965` plus the Skill Point coverage line `:2323` to match |
| §2 absence ownership | the four absence-adjacent `unknown` call sites (`TrainingCard.vue:226`/`:323`/`:365`, `ScenarioPanel.vue:246`), or `DESIGN.md` §4.2 — one or the other must move. The reference views' `unknown` row is not in this set |
| §3 constant visibility | `TrainingCard.vue` (the `costTitle` site at `:233`/`:234` and its computed at `:96-101`); under P2 also `AbsenceValue.vue`'s contract; under P3 also `ProvenanceBadge.vue` and a new surface. `SkillsPlanner.vue:214`, `RaceCard.vue` and `ScenarioPanel.vue:205-208` carry the same register and should move together or be named as exceptions |
| §4 Class 3 | `tests/browser/career-inheritance-event.spec.ts:71`/`:85` (labels) **and** `:72-97` (the nineteen `name=` locators, via A1) **or** `Builder.vue` / `AncestryNode.vue` input markup (via A2), **and** `tests/browser/global-setup.ts:64` (opt the seeder in). Option B adds a new seeder and its test |
| §5 panel interaction | `CareerStatePanel.vue:60-78` — under O1 the two markers at `:65`/`:68` go and `:76-78` stays; under O3 the row layout and the `:67` separator change |
| §6 rank copy | `SaveVeteran.vue:248-249` |
| Register entry | `KNOWN-ISSUES.md` KI-81 for the picker defect, which is owed and cannot be written correctly until a closure commit exists. The G-4 disposition in the appendix is a second register candidate (it belongs under KI-58's own entry, whose reproducer no longer reproduces) |

### 8. Explicitly unchanged by this pass

No badge label changed. No absence site migrated. No component redesigned. No copy string shipped.
`TrackerVeteransSeeder` unmodified and unrun. No Class 3 spec altered. No fifth provenance category
proposed anywhere in this file. No database touched.

#### 8a. Evidence freshness, stated so a later pass does not read a stale figure as current

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

### Appendix — four observations recorded, not acted on

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
3. `docs/research-scratch/DESIGN-CORPUS.md` is absent from this tree — its D-rules (D-20, D-100, D-256) are
   cited from components and from `ADR-0001` §6, and `AGENTS.md` §17 says folded sources were deleted after
   verification. Any §1 approval should cite the master that now carries those rules, not the deleted file.
4. **The design-artifact gate is failing, and its cause is only half resolved.** `python tools/gate.py` exits
   1 with ten `[G-4 raw hex]` findings across `screen-a-scenario-v10.html`, `screen-c-event-v1-inline.html`
   and `screen-c-event-v2-preview-column.html` — **tracked** prototypes, clean in the tree, committed at
   `e0e043c` (2026-09-27); reproduced identically on 2026-10-09. `KNOWN-ISSUES.md` KI-58 (still **OPEN**,
   filed 2026-10-03) describes the instrument fault: the loader read a `DESIGN.md` that
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

### 9. Owner rulings, answered 2026-10-09

The ten questions this artifact raised were answered the same day. Each row is the ruling plus the one line
of evidence it rests on. §§1-8 above are the analysis that produced them and are not edited here.

| # | Ruling | Evidence it rests on |
|---|---|---|
| **1** | **1c, split by surface class.** `ADR-0020` §2 regulates the advisor's numbers; `config/reference.php:30-33` regulates the reference views; neither reaches the other. Both get written down in `DESIGN.md` prose. Zero code. | `ADR-0020` §2:73-74 scopes the vocabulary "for the advisor's numbers"; the config block defines its own four mappings and reserves `calculated` at `:33` |
| **2** | **2a, remove all four absence chips** (`TrainingCard.vue:226`/`:323`/`:365`, `ScenarioPanel.vue:246`), and update `grand-concert-panel.spec.ts:83` with the ruling named as the reason. **This is policy, not enforcement.** | `DESIGN.md` §4.2 and D-220 fix how an absence renders; nothing in the chain ever said a chip may not accompany one |
| **3** | **3b, per-surface meaning.** The reference views keep "the doc marks the value unverified"; absences retire the word. The one config `unknown` at `reference.php:105` stays. | Coheres with 2; the two emissions were never the same claim (`reference.php:32` vs `ProvenanceBadge.vue:26-27`) |
| **4** | **4b, ratify sense (b).** `Estimated` means weakly sourced. `ADR-0001` §3 gets a narrow appended note that sense (a) is unbuilt; its substance is not rewritten and its text is not deleted. | Sense (a) has no legal object: `docs/research-scratch/DESIGN-CORPUS.md` section `## design-2.0.md` §49's worked example is a win chance, and `PRD.md` §6.11 forbids computing one |
| **5** | **5a, P1 inline.** The constant's source, read date and stale flag print visibly under the figure; the one-line cost on the densest card is accepted. `ScenarioPanel.vue:205-208` and `Dashboard.vue:300-301` are cited as the shipped precedent in the commit message. | `ADR-0001` §2:46 says **visible**; §7:101 says every suggestion **displays** the date. A `title` is mouse-only, so it fails both on keyboard and touch |
| **6** | **6a, Option A + A1.** Repoint the two labels to `Mayano Top Gun` and `Daiwa Scarlet`; re-target the ~19 `name=` locators at ids and labels; `global-setup.ts:64` opts the seeder in. Test-only, real rows, no `FR-G-1` collision. → **Partly superseded before it could be executed**, by `c29a71b` (13:44, another session): the A1 locator work landed as written, and the label half was removed instead of repointed — the fixture now leaves the parent pick **unchosen**, because `legacy_id` is nullable and the scratch DB holds no Veterans, with the picker's own contract covered by `ancestry-node-picker.spec.ts`. **Consequence: the seeder opt-in must not be added.** Seeding seven Veterans would break the empty-`veterans` condition plan §4.1 item 7 requires, and no spec needs them any more. | Both blockers were independent; the peer deleted one by removing the dependency rather than satisfying it |
| **7** | **7a, O1 prose only.** The two markers go; the prose must name the kind of absence (D-220). The panel's other half, `:82-97`'s three absence idioms through one `<dl>`, is **explicitly out of scope** and recorded as future cleanup. | `DESIGN.md:571`: a caller printing its reason as visible prose "needs no component" |
| **8** | **8a, C1.** "You have not recorded a rank for this parent." | `AncestryNode.vue:100` already says it in that voice for the same field; `AncestryGraph.php:160` shows the rank is this career's own payload |
| **9** | **9b, one line.** `SaveVeteran.vue:246` moves to `N/A` + reason. The wider sweep was not the ruling; see §14. | `AGENTS.md` §5: an unrecorded value renders `N/A` with a `title`, never a default |
| **10** | **10a with an explicit `AGENTS.md` §11 dispensation.** Repair the three tracked prototypes, hex to token. **10b re-scope is refused**: the gate is correctly reporting. Bookkeeping ships as a separate package (§13). | `git ls-files` proves the three files tracked and clean; the four hexes are absent from `resources/css/app.css` |

**Correction to §2, owed by this pass.** §2 called the four-chip removal "enforcement of a settled ruling
rather than new policy". The ruling is **policy**: the settled text governs how an absence *renders* and is
silent on whether a badge may sit beside it. The analysis in §2 remains useful; the label on it was wrong.

### 10. Convention: how the two records change

1. **`ADR-0001` §3 closes by appended dated note, never by rewrite** — `AGENTS.md` §11 requires a dated
   erratum that preserves the original sentence. The note says: sense (a), the opt-in modelled figure, is
   unbuilt; sense (b), a weakly sourced figure, is what ships; §6.11 leaves no legal target for (a).
2. **KI-58 closes by a dated closure line under its own entry**, naming `7d8b62a` as the fix, and the mirror
   case is filed as a **new** entry continuing the numbering. No existing entry is renumbered, and KI-81
   stays reserved for the picker defect (§7).

### 11. The four locations that carry the vocabulary question

Closing one without the others leaves a live contradiction, so all four move together: `DESIGN.md` §5 (the
definitions themselves), `SCREEN_SPEC.md` §7 item 18 (the defect record), `PRODUCT.md`'s "Open" list at
`:207-214` (which already says "Which provenance vocabulary the product uses is unsettled"), and this
artifact's §1. A fifth, smaller fix travels with them: **`PRODUCT.md:302` cites
`RenderedCopyHygieneTest` as enforcing `DESIGN.md`'s absence rule**, which is the exact error
`DESIGN.md` §10 corrected on 2026-10-09 — that test checks dashes, the `N/A`-instead-of-a-glyph rule and its
own scan coverage, and never contains the word `Unknown`.

### 12. Ruled text, held for paste (do not paste while the files are hot)

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

### 13. 10a bookkeeping text, pending peer-sync paste

1. **KI-58 closure line:** "*Closed 2026-10-09. `7d8b62a` (2026-10-08) implemented fix option 1 —
   `load_token_hexes()` now reads `resources/css/app.css` and both theme blocks — and the entry's three
   reproducer hexes (`#B45309`, `#0667B0`, `#6E6459`) are legal today. What remains is the mirror case,
   filed separately; the register text above is preserved as written.*"
2. **New mirror-case entry**, naming the four hexes and the three tracked prototypes: `#7A7067` and
   `#106F9F` were token values in the deleted `DESIGN.md` at `e0e043c`; `#C5A558` and
   `#FDF7EF` appear only in the gitignored `docs/design-research/_scratch/analysis/tokens/tokens.json` and
   never in a theme. Ten findings across `screen-a-scenario-v10.html`, `screen-c-event-v1-inline.html` and
   `screen-c-event-v2-preview-column.html`; reproduction command `python tools/gate.py`; closure is the
   hex-to-token repair under the owner's §11 dispensation, and closure does not re-scope the gate.
3. **`tools/gate.py:6`** cites `docs/research-scratch/DESIGN-CORPUS.md` as its rule source; that file no longer
   exists, and the rules live in the `docs/research-scratch/` masters.
4. **Root `README.md:306`** still calls KI-58 an open defect affecting rendered-prototype checks; after item 1 it
   must point at the new mirror-case entry instead.

### 14. Ruling 9b, classified

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

### 15. Coordination escalation, 2026-10-09

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

### 16. Held state, stated plainly

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

### 17. Executed 2026-10-09, this pass

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

### 18. Remaining-work pass, executed 2026-10-09

This pass lands `cf68a7a`, `e373588`, `0c1ec6d`, `fc5fa1a`, `4f1aae8`, `92abcca`, `9849f8c`, `b3ba5f3`,
`59ae3e3`, `284f40d` and this file on `master`; each pathspec commit was blob-verified
(`git rev-parse HEAD:<path>` against the rebuilt file) before the peer's lines were restored.

**Tier 0, landed.** `DESIGN.md` §5 and §10 (`cf68a7a`, corrected forward by `e373588`), `SCREEN_SPEC.md`
§7 items 18 and 20 (`0c1ec6d`), `KNOWN-ISSUES.md` KI-58 closure plus KI-83 (`fc5fa1a`). The first
isolation splice treated a unified-diff header as a 0-based index, so a replacement hunk kept the line it
was meant to remove and removed the next one instead; the tool now asserts that the lines it cuts are
byte-identical to the hunk's own removed lines, and the damage was fixed by a follow-up commit rather
than a rewrite.

**Tier 1, landed.** `4f1aae8` deletes every `focus-visible:outline-green` override; `92abcca` prints the
constants behind the coverage, caps and race-fact figures; `9849f8c` rewords the unresolvable trainee id;
`b3ba5f3` corrects §8's site list and stops pre-deciding the motion question; `59ae3e3` files KI-81 in its
reserved slot; `284f40d` records SCR-VET-004's correction 3.

Three of the seven Tier 1 premises did not survive contact with the tree, and each is reported rather than
built to match the brief:

1. **"four `outline-green` deletions" is 22.** `DESIGN.md` §8 named two Blade templates as the call sites;
   both were deleted in slice B1 (`0ea8d43`). The overrides were actually 22 sites across seven Vue
   components — three in `skills/SkillCatalog.vue`, four in `support/SupportCardCatalog.vue`, two in
   `support/SupportSlot.vue`, one in `TraineeCombobox.vue`, three in `Runs/Show.vue`, six in
   `Support/Builder.vue`, three in `Veterans/Index.vue`. All 22 are deleted, each site keeps its outline
   width and offset, and `grep -r outline-green resources/` returns 0.
2. **The `veteran-compare.spec.ts` assertion was already loosened**, at `eb2d7e9` and not by this session:
   `:98` waits on the path with any query while `:89` keeps the library door's `veterans[]=N`, which is
   server-rendered and really does emit that spelling. So the deliverable became the status record
   (`§4.20` correction 3), not an edit to a passing spec. Section 3's `SCR-VET-004` row still repeats the
   stale failure cause; it lies inside a concurrent session's uncommitted hunk range, so it is named in
   that correction instead of being edited there.
3. **There is no unheld 768px claim to withdraw.** `DESIGN.md` §2.3's own status bullet already calls the
   minimum "a proposal, not a ratified contract" resting "on an attribute read rather than a browser
   measurement", §10 marks it **Unverified**, and §11 question 5 puts the choice in front of the owner.
   Withdrawing the number would delete the measured half (the two wide tables, 476px at the narrowest
   viewport tested, and their scrolling contract), so nothing moved. Measuring it is Tier 2's work.

**Tier 1, held.** `Dashboard.vue:300-301` prints the verified date visibly already; what it lacks is the
source, and `dataStatus` is typed `{ label, state, verified_at }` — no source string exists in the payload,
so adding one is a `DashboardController` change, and that file carries a concurrent session's uncommitted
lines. Left as decision (d) below rather than invented as copy.

### 19. Tier 2 runbook, KI-80, not executed this pass

The remaining budget did not cover writing the fix *and* running the six held specs, so this is the runbook,
not a claim.

1. `tests/browser/global-setup.ts` currently points every session at one `database/browser-scratch.sqlite`
   and wipes it. Give it a per-session path from `process.env.PLAYWRIGHT_SCRATCH_DB`, falling back to the
   shared path only when the variable is unset, and have the wipe touch only that path.
2. Build each copy with `VACUUM INTO` from `database/database.sqlite` — never `cp`: a WAL database copied
   file-by-file arrives malformed, missing tables, or silently empty. Seed a Veteran set into the copy so
   KI-69 class 3 can stop being a fixture blocker.
3. Serve each session's own port and kill the server inside the same command as the spec run; a backgrounded
   `artisan serve` dies between turns, and a leaked one answers 500, which reads as a web-server timeout.
4. Then the six held specs: `career-inheritance-event`, `grand-concert-panel`, `scenario-panel`,
   `veteran-compare`, `ancestry-node-picker`, `career-training-detail`. Report each with its own exit code;
   no session may claim the suite is green until `php artisan migrate:status` has been run on the dev file
   (KI-60, owner action).

### 20. Decisions owed, as at the end of this pass

| # | Decision | Why it is not an agent's |
|---|---|---|
| a | Group C's seven sentence-objects (`RecommendationCard.vue:64`, `DeckPanel.vue:138`, `SupportCards/Show.vue:149`, `GradePointMeter.vue:54`, `TraineeSelect.vue:119`, `TeamRacePanel.vue:42`, `Runs/Show.vue:225`): `N/A` plus reason, or the sentence stays | A mechanical substitution deletes the information the sentence carries |
| b | `Runs/Show.vue:267`, the third group A site | The file carries ~45 concurrent lines; the site is cold but the commit is not separable without a decision on who owns the file |
| c | Whether to guard config provenance strings against `ProvenanceState` at build time | `ProvenanceBadge.vue:61` indexes `BADGES` with no fallback, so an out-of-vocabulary `state` in `config/reference.php` fails at render; the fix is a validator nobody has authorised |
| d | Whether `dataStatus` gains a `source` field so the dashboard can print it | Server payload change in a file another session is editing |
| e | `DESIGN.md` §11 question 3, motion: keep the tree motionless or ratify one transition token | §8 no longer pre-decides it |
| f | `DESIGN.md` §11 question 5, the 768px minimum: ratify with the measurement or withdraw the number | Needs the browser gate Tier 2 unblocks |
| g | KI-84: ten feature-test files staged for deletion by a concurrent session, three of them Our Grand Concert's own absence guards | The Floor requires owner approval for a deleted test |
| h | Section 15's coordination question, still unanswered: which session owns rulings 2a/3b/5a/7a/8a/9b | Both sessions are landing them; `822d97b` and `92abcca` touched the same file within minutes of each other |

**Gates for this pass.** `npm run typecheck` exit 0. `php tools/lore.php`: 303 hits, none in a line this
pass added (proved by comparing hit line numbers against the added-line set of this pass's commits; the doc
hits are pre-existing forbidden-list and substring-in-word classes). `composer lore-code`: 121 hits, zero
in the four Vue files. `vendor/bin/pint --dirty` and PHPStan: not run, no PHP changed. `php artisan test`:
not run; the change class is Vue and the Level 3 checkpoint is still owed before this slice is called done.
`php artisan migrate:status`: not run, owner action. Browser: not run, KI-80.

### 21. Browser-unblock pass, executed 2026-10-09 (Tier A to F of the remaining-work prompt)

The prompt that opened this pass is the execution half: KI-80 first, then the six held browser gates, then
the last UI items, the cumulative PHP gates and the register work. Commits added by it: `4996877` (KI-80),
`554a6b4` (app.css header), `53ce731` (README), `86177eb` (DESIGN §2.2 and §11), `1e7f3a5` (KI-88),
`39da5d5` and `625e012` (the two cumulative PHP fixes). A concurrent session then marked KI-80 FIXED IN
TREE in `d8739a9`, naming `4996877` — not closed, because its closure evidence is the browser suite and
KI-88 blocks three of the six specs.

**Tier A, KI-80 landed at `4996877`, with one instruction corrected.** `SCRATCH_DATABASE` was one constant;
it now comes from `PLAYWRIGHT_SCRATCH_DB` or from a default carrying the pid and the load-time millisecond,
and the delete step is `removeScratchDatabase(path)`, which removes exactly that path and its `-wal`/`-shm`
siblings. **`VACUUM INTO` from the canonical dev database was not used, contrary to the brief.** The KI-69
contract is a database built from nothing because five cases assert empty states (`No runs yet` in two
specs, three dashboard cases with no active career), and a copy of the dev file would carry whichever runs a
peer left behind and fail them for the wrong reason; the dev file is still never opened, and each run's
own db-guard output proves `targets canonical: no`. `.gitignore` needed no edit: `/database/*.sqlite*`
already covers the new names. Evidence: `scratch-isolation.spec.ts` 3 passed (named path, pid default, the
sweep leaves a peer's file and siblings alone), then two real invocations in parallel on :8180 and :8181
with distinct scratch files, both exit 0, 3 passed each (47.8s / 47.1s), logs `.scratch-uma/tierA-par1.log`
and `tierA-par2.log`.

**Tier B, the six gates, with the tree — not HEAD — as the subject.** Every case below ran on its own port
and its own scratch database, and every result describes the working tree, which carried 97 dirty entries
when the first spec started and 78 when the last finished; a concurrent session committed twice mid-batch.
That is the limitation KI-80's fix does not remove: the database is now private per run, the source is not.
One case is unaffected by it (`career-inheritance-event` passed before either peer commit landed) and one is
excluded outright (`career-training-detail`, below).

| Spec | Port / log | Result |
|---|---|---|
| `career-inheritance-event` | 8180, `tierB-inheritance.log` | **8 passed, exit 0** — the Class 3 cases, first green browser run for that screen |
| `career-save-veteran` | 8181, `tierB-save-veteran.log` | **5 failed**, all at the fixture's `:54` pre-cutover URL wait |
| `veteran-compare` | 8182, `tierB-veteran-compare.log` | **1 passed / 3 failed**, same wait at `:53` |
| `career-training-detail` | 8183, `tierB-training-detail.log` | **6 failed**, timed out on a `Record this training` button its spec (` M`, +26/−23) expects; a concurrent session holds that file, so not counted as evidence |
| `career-cockpit` | 8184, `tierB-cockpit.log` | **4 passed / 3 failed** in 9.8m: the 1280/768/320 layout, the keyboard ring and the reduced-motion case are green; `:113` expects a link to the deleted run screen, `:226` sizes a control that is not there, `:237` is the stale wait |
| `career-race-strip` | 8185, `tierB-race-strip.log` | **6 failed**, same wait at `:42` |

So the redirect the cutover landed inverts the earlier picture: the database collision is gone and one spec
is fully green, but 30 fixture waits across 22 spec files still expect the address `runs.show` no longer
serves. Both halves are filed as **KI-88**, with the probe that proves the application half: the cockpit's
three header forms (`saveStatus` `:310`, `saveScenario` `:325`, `savePeriod` `:343`) each open a form whose
handler hangs off a submit event and render **no** button or submit input — `form locator('button,
[type="submit"]')` resolved to 0 elements across 32 retries.

**Tier C.** `app.css`'s header now states light is the default (`554a6b4`), with the same block's two dead
citations repointed to `docs/research-scratch/DESIGN-CORPUS.md`; README's Current Limitations line now
names KI-58 as closed and KI-83 as the live mirror case (`53ce731`). `DESIGN.md` §2.2 and §11 carry the
dated close-out (`86177eb`). The dashboard's `source` field stays a held decision: `dataStatus` is typed
`{label, state, verified_at}` and `DashboardController` is another session's file.

**Tier D.** `phpstan analyse app/` was **2 errors a file, both in committed finale code, both fixed**:
`Advice.php:22` and `TrainerAdvisor.php:108` each returned or took an `array` with no value type while
Advice's own inline docblock already spelled the shape out. `pint --test app/ database/ tests/` was 3
issues, of which `TrainerAdvisor.php`'s missing final newline and one test file's chaining indentation are
fixed or resolved; **one remains and is not mine to touch**: `tests/Feature/FinaleVocabularyTest.php` is an
untracked file a concurrent session created this hour, so fixing it would edit work in flight — named here
instead. Post-fix: PHPStan `[OK] No errors`, Pint clean on `app/Services/Advisor/`.

**Tier D.1, the Level 3 Pest checkpoint: not run, and the reason is attribution rather than time.** The
suite builds its own schema from `phpunit.xml`'s in-memory database, so a scratch file changes nothing; but
the tree holds 26 modified and 13 deleted test files from another session, and a run over it would produce
a number that describes neither HEAD nor this pass. Recorded as owed, not as green.

**Tier E.** KI-84's test deletions were **not** landed and **not** reverted, and the reason is that the set
will not hold still: KI-84 records ten staged, this pass measured thirteen gone with three of them staged,
and by the time this section was written the staged set was empty again with twelve deleted, fifteen
modified and seven untracked. Every count here is a dated reading, not a state. The ` D` set cannot have its
assertions run because the files are off disk, and two of the twelve guard subjects the cutover deleted
(`RunViewTargetSizeTest` reads `Runs/Show.vue`; `RunStatusControlTest` pinned the run page's status select,
which is the control KI-88 says nothing now renders), so the honest disposition is the owner's under the
Floor, not an agent's — it was already theirs at §20(g), now with the split measured. The `outline-green`
re-check (5.2) was satisfied at `4f1aae8` and needed nothing further: zero occurrences under `resources/`
at HEAD.

**Tier F.** All the owner decisions the prompt lists are recorded, each with its anchor: `migrate:status`
(§16, §20), group C (§14, §20a), `PRODUCT.md` regeneration (§11, §20 note), Unity Cup capture
(`SCREEN_SPEC.md` §7-6), artwork placement (`DESIGN.md` §4.7, §11 Q7, PRD OQ-6), the 768px question left as
`DESIGN.md` §11 Q5 rather than withdrawn, 4b's sense (a) (`ADR-0001` §3's dated note), and §20's remaining
rows. Two are added by this pass and are not in §20's table: **KI-88's remedy** (a Save control per header
form versus autosave on change, which changes what the 44px sweep and the axe scan measure) and **KI-84's
13-file deletion split**, which needs the owner's approval under the Floor before any session commits it.

### 22. Phase G preflight, executed 2026-10-11 (Part 1 §2.1 to §2.4, measured at `5d26b49`)

The G prompt set forbids taking a previous agent's word for §20(h) and requires the current state instead
(`docs/research-scratch/PROCESS-PLANS.md`, section `## Trainer-Desk-2.0-G-Prompts.md`, source line 60;
repointed 2026-10-12 when the original `docs/Trainer-Desk-2.0-G-Prompts.md` was consolidated and
deleted). This pass measured it rather than reasoning from §16.

**§20(h), measured.** The question asks which session owns rulings 2a/3b/5a/7a/8a/9b. All six are committed,
and each ruling's content reads off HEAD:

| Ruling | File §16 listed as held | Committed in | Proof read this pass |
|---|---|---|---|
| 2a, 3b | `career/TrainingCard.vue`, `scenario/ScenarioPanel.vue` | `b30906a`, `aa5d975` | `state="unknown"` appears **zero** times under `resources/js` |
| 5a | `career/TrainingCard.vue`, `costConstants` | `b30906a` | defined at `:107`, printed at `:252` |
| 7a | `career/CareerStatePanel.vue`, `statAbsence` | `5eba0d0` | defined at `:90`, printed at `:144`; the `:50`/`:87` figures moved when R2-09 added `denominatorLabel` |
| 8a, 9b | `pages/Career/SaveVeteran.vue` | `d00f965` | `:254` renders `Rank: {{ parent.rank ?? 'N/A' }}`, the ruling carried by the `title` |
| 8a siblings | `TeamRacePanel.vue`, `Catalog/Show.vue` | already on HEAD before this pass | tier `N/A` with its own `title` at `TeamRacePanel.vue:44`; `va_ja` at `Catalog/Show.vue:163` |

The four files §16 lists as held are not held any more, so the state §15 and §16 describe has no
referent left. What this table does **not** do is rule on §20(h): which session stands down remains the
owner's row to close, and no line here is evidence that a standing-down happened. The distinction matters
because §15's consequences were a set of abstentions (no doc writes, no browser run, neither 6a nor 10a), and
an abstention lifted by inference is a guardrail quietly lowered.

**§20(b) has no site either.** That row held group A's third edit because `Runs/Show.vue` carried roughly 45
concurrent lines. `b30906a` deleted the file, 831 lines, so the decision now concerns a surface nothing
renders; KI-95 records what the deletion cost rather than what it gained.

**§2.2, KI-88.** Closed by the owner at `48e4f22`, on the save-control branch of the two candidates:
`Cockpit.vue` carries seven `type="submit"` controls and the fixture re-anchors travelled with the commit.
What §2.2 still asks for is its item 4 — the post-fix baseline browser numbers, including the 44px sweep and
the axe scan. That is a run, not a ruling, and the last paragraph says why a run today proves nothing about
the tree it ran on.

**§2.4, dependencies.** G1 and G2 are on HEAD rather than merely authorized: `app/Services/Advisor/NextDecision.php`
sits beside `TrainerAdvisor.php`, `career/NextDecision.vue` and `career/ObjectiveRow.vue` sit in the cockpit
set, and the G-33 property is asserted at `tests/Feature/CareerScenarioSelectTest.php:152` and in seven other
files. G3 through G7 were not audited by this pass; nothing here states what they still owe.

**Phase 1 is closed.** Its last producing item, R2-17, landed at `5d26b49` — four files, 47 insertions, the
`deck_url` prop plus one header anchor plus the feature and browser assertions that pin it. The working tree
carried thirteen modified tracked files when this pass measured it, before §22 was itself written: twelve docs
and `tests/Feature/CareerLegacyDeckStepsTest.php`, which is a peer's R2-02 file and not this pass's to touch.

**The hazard §20(h) never named.** A peer session is in this tree while this section is written:
`.scratch-uma/browser-8173.sqlite` through `browser-8177.sqlite` were written between 02:02 and 02:51, and
`5d26b49` landed at 02:55. KI-80's fix gave every run its own database, so the collision that broke Tier B is
gone, but the source is shared, which is the limit §21 already recorded. A browser gate run inside that window
is attributable to neither session. It is stated here rather than assumed resolved.
---

## screen-spec-2.0.md

> **Refreshed 2026-10-21 (Round 18, owner-ordered).** Source: `docs/proposals/screen-spec-2.0.md`. The section below was embedded by Round 14 and was measured on 2026-10-21 as already containing every source body line, so it is not re-embedded. The original was deleted from `docs/proposals/` after that measurement. Cite this anchor, not the deleted path. <!-- census: quoted-original -->

## Uma Trainer Desk — Screen Specification (2.0 design target)

> **Status: 2.0 design target — reference only, filed 2026-10-04. Not authorization.**
>
> Source: owner-supplied design brief (attachment, 2026-10-04), reproduced verbatim below the rule line.
> This specifies the Trainer Desk 2.0 screen set for the Inertia + Vue rewrite authorized by `ADR-0020` §1.
> It is the *target* for the SPA slice. The **current** Blade app is documented by `SCREEN_SPEC.md`; where
> the two disagree about what exists today, `SCREEN_SPEC.md` wins.
>
> **Governance holds — these sections describe computation the project has ruled deferred or banned, and
> filing this document does not authorize them:**
>
> - **SCREEN-010 / SCREEN-014** — per-training stat-yield numbers ("+62 Speed / +25 Power") and numeric
>   "Failure: 2%": no source publishes these. The approved advisor v1 excludes them
>   (`docs/research-scratch/PROCESS-PLANS.md`, section `## trainer-advisor.md` §1, §5; `ADR-0001` §3).
> - **SCREEN-011 / SCREEN-017** — race win-probability ("Win probability: 84%") and risk percentages:
>   race prediction, deferred on the `ADR-0016` data blocker; `ADR-0020` §4 reaffirms §6.11.
> - **SCREEN-006** — "expected inheritance" and "up to three recommended combinations": inheritance
>   computation. Only the record-only Veteran library is authorized (`ADR-0020` §3, `ADR-0010`).
> - **SCREEN-016** — shop-item "recommendation": the shop rotation is not modelled
>   (`config/scenarios.php` `trackblazer.shop`).
> - **§30** — numeric confidence / turn-delay quantification: a reason line is authorized
>   (`ADR-0001` §4); a numeric confidence model is not sourced.
>
> Where this target and a landed ADR conflict, the ADR wins (`AGENTS.md` §2). The deferred sections are
> kept verbatim so the design intent survives for the slice that closes their blocker.
> *Dated close-out 2026-10-08 (documentation-sync pass): the Inertia + Vue rewrite this banner says "§1
> targets" landed through Phases A–E, and each screen in this inventory shipped under the union-with-
> `docs/research-scratch/DESIGN-CORPUS.md` section `## design-2.0.md` mapping the plan's §4 preamble records. `SCREEN_SPEC.md` §3/§4 is the authoritative record
> of what exists (`SCR-CAR-001`–`024`, `SCR-VET-001`–`004`, `SCR-SYS-005`–`007`), and the plan's §4–§9
> close-outs record each slice. The governance holds above still bind: the win-probability, per-training
> yield, expected-inheritance, shop-recommendation and numeric-confidence sections remain unbuilt and
> render `N/A` with the blocker named, exactly as this banner states. Nothing in this file's screen set was
> changed to match what shipped — it remains the design target it was filed as.*

---

### Product direction corrections (added 2026-10-05)

A new product/UX strategy document (owner-supplied, 2026-10-05) corrects several assumptions in this brief.
This section records those corrections without rewriting the brief itself. The brief stays verbatim; the
corrections are additive notes so a reader can tell what stands and what must change before implementation.

**Core UX model shift.** The application is reoriented from "career optimizer" to **"Trainer's planning desk."**
It separates **Preparation mode** ("What am I going to build?") from **Active Career mode** ("Given what actually
happened, what should I do next?"). The app never pretends its simulated state is authoritative game state.

**Screen-level corrections:**

| Screen                             | Original assumption                     | Corrected direction                                                                                                                                                                                                                                                                                                                                                                                           |
| ---------------------------------- | --------------------------------------- | ------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------- |
| SCREEN-002 (Scenario Selection)    | Three scenarios with difficulty stars   | **Four Global scenarios**; remove star ratings; show optimization focus (primary/secondary/training complexity); Grand Concert marked "PARTIALLY DOCUMENTED"                                                                                                                                                                                                                                                  |
| SCREEN-005 (Build Target)          | Generic stat targets                    | Add **Career Plan object**: purpose, scenario, trainee, race profile, stat/aptitude/style/skill targets, legacy/support requirements, risk tolerance. Render as "Your target" not "The correct target"                                                                                                                                                                                                        |
| SCREEN-006 (Legacy Lab)            | Two-parent picker                       | Upgrade to **six-node ancestry planner** (Parent A/B + four grandparents). Show Spark probabilities (`~10% ★★★`), not guarantees. Distinguish Blue/Pink/Green/White/Scenario Sparks. Optimize entire ancestry configuration. **Factor yield clarified 2026-10-05**: exactly 1 Blue + 1 Pink per run, at most 1 Green (requires 3★ parent), White sparks unbounded — model as variable yield, not a slot cap   |
| SCREEN-007 (Support Deck)          | Five owned + one borrowed               | **Six slots**, ownership flag (OWNED/RENTED). **Seven support types**: Speed, Stamina, Power, Guts, Wit, Pal, Group. Scenario Link derived from scenario+character, not stored on card. Granular deck analysis (training power, early run, race, safety, events, skills)                                                                                                                                      |
| SCREEN-008 (Run Preflight)         | Simple validation                       | Rename internally to **"Career Contract"**: scenario, trainee, six Legacy members, sparks analyzed, support deck, race profile, stat/skill/aptitude targets, risk profile. Warnings for low factor probability, missing aptitudes                                                                                                                                                                             |
| SCREEN-009 (Career Cockpit)        | Dashboard                               | Make it a **state machine**: explicit `CareerState` (year, half, turn, energy, mood, stats, SP, fans, bonds, races, events, goals, scenario_state, inheritance_state, action_history). Every action: BEFORE → USER ENTERS → EXPECTED → ACTUAL → UPDATED STATE → RECALCULATED                                                                                                                                  |
| SCREEN-010 (Training Decision)     | "+62 Speed / +25 Power" yields          | Per-training yields are **unsourced** (§1.1.1 ⚠️ STALE). Render `N/A`. Keep decision card structure but remove specific yield numbers                                                                                                                                                                                                                                                                         |
| SCREEN-011 (Race Decision)         | Win probability percentage              | Race prediction **deferred** (`ADR-0016`). Use conservative readiness bands (Excellent/Good/Borderline/Poor), not fake precision ("Win probability: 84%"). Model race as complex object (surface, distance, band, style, grade, venue, layout, corners, straights, elevation, weather, ground, season, time, fans, skill interactions, scenario effects)                                                      |
| SCREEN-012 (Event Decision)        | Simple choice recording                 | Model event as: source (Support/Character/Scenario/Random), choices, known outcomes, current career state, expected effect, recommendation. If outcome incomplete: "⚠ Event outcome incomplete — choose manually"                                                                                                                                                                                             |
| SCREEN-013 (Inheritance Event)     | Expected inheritance display            | Show **probability, not guarantees**. Factor outlook: `Speed ★★★ Potential payout: +21 Estimated roll: ~10%`. NOT "guaranteed"                                                                                                                                                                                                                                                                                |
| SCREEN-014–017 (Scenario Panels)   | Static modules                          | Each panel genuinely different: URA (goals + Happy Meek), Unity Cup (**Team Cockpit** with team rank/spirit/bursts), Trackblazer (Grade Points + shop + rivals + Twinkle Star Climax), Grand Concert (basic tracker only, advanced advisor limited)                                                                                                                                                           |
| SCREEN-018 (Career Timeline)       | Turn log                                | Record **decisions, not just turns**: BEFORE state, ACTION, EXPECTED result, ACTUAL result, RESULT, DECISION accepted/rejected, ADVISOR CONFIDENCE. Creates career audit trail                                                                                                                                                                                                                                |
| SCREEN-019 (Career Result)         | Summary report                          | Upgrade to **Veteran Creation screen**: career result, aptitudes, skills, race history, sparks, legacy value (factor quality assessment), best use recommendation                                                                                                                                                                                                                                             |
| SCREEN-020 (Save Veteran)          | Simple save                             | Add **Factor Analysis** workflow: veteran review → factor analysis → legacy value → tag → save. Recommendation: "Excellent Medium parent. Best used for: Medium, Pace Chaser, Speed-oriented builds"                                                                                                                                                                                                          |
| SCREEN-021 (Veteran Library)       | List view                               | Three views: **Veterans** (all completed careers), **Factors** (searchable factor inventory), **Ancestry** (see where Veteran came from — six-node graph). Add "Find Parents for This Build" button                                                                                                                                                                                                           |
| SCREEN-022 (Veteran Comparison)    | Side-by-side stats                      | Add factor comparison, ancestry visualization, compatibility calculation. Search by factor requirements, aptitude requirements, skill requirements, compatibility, scenario requirements                                                                                                                                                                                                                      |
| New (not numbered)                 | —                                       | Add **Race Planner sophistication**: course analysis (final corner, slope, backstretch, final straight, last-spurt conditions, position triggers). Don't hard-code distance ranges without resolving conflicts (1400m: Game8=Sprint vs export=Mile)                                                                                                                                                           |

**New interaction patterns:**

- **Undo last recorded action**: Safe because local planning tool, not game manipulation
- **Screenshot-assisted entry** (future): Import screenshot → OCR → user confirmation → store as `USER_CONFIRMED_SCREENSHOT`
- **Three complexity levels**: Beginner ("What should I do?" → "Train Speed"), Intermediate (+ why), Advanced (full modifier breakdown)
- **Progressive disclosure**: Default shows summary; expand for training calculation, support contribution, scenario modifier, race requirements, source/confidence

**Data integrity layer additions:**

Every important game mechanic represented as `GameRule` with: key, scenario_id, server, version, value, source, source_type, confidence, verified_at, notes. Example:

```text
scenario.trackblazer.stat_cap.stamina
value: 1900
server: global
source: gametora_scenario_export
confidence: verified
verified_at: 2026-09-27
```

**Terminology authority:** Global terminology is authoritative (Scouts, Transfer Requests, Veteran Umamusume, Pal, Wit, Inspiration, Legacies, Sparks). UI always uses Global labels; internal keys separate. JP-only mechanics explicitly excluded (four Global scenarios vs fourteen JP; JP-only features like Independent Training marked unverified for Global).

**Confidence model:** Every recommendation carries confidence level: HIGH (supported by current client/data + current source), MEDIUM (current data + older guide), LOW (derived from incomplete information), UNVERIFIED (do not use for automatic recommendation). Example: "Train Speed — Confidence: HIGH" vs "Predicted exact stamina for 2200m — Confidence: LOW" (no verified metres-per-Stamina conversion).

**Multi-objective optimizer architecture:** Hard constraints (mandatory race within N turns, fan requirements) come before optimization (career goals, scenario objectives, build targets, legacy value, risk model). LLM explains recommendation, does not invent it.

**Versioning mandatory:** Every career run preserves ruleset snapshot. Old Veteran says "Ruleset: Global Trackblazer v2026-04" not silently recalculated with today's rules. Data status indicator on dashboard: "GLOBAL DATA ● Current Updated: 2026-09-27" or "⚠ Snapshot Last verified: 2026-09-27".

**Strongest differentiator clarified:** Not "calculates training gains" (existing calculators do that). It is: **"Understands relationship between current Career Plan, six-person Legacy configuration, Support Deck, current Career State, and the Veteran being created."**

---

### Knowledge grounding (added 2026-10-05)

The brief numbers its own screens `SCREEN-001` … `SCREEN-027`. The repository numbers screens
`SCR-<AREA>-<nnn>` (`SCREEN_SPEC.md` §2). This section maps the two so a slice can cite one ID, and
records what the corpus holds for each screen's data. It does not edit the brief.

**Terminology.** "July 2026 rebalance" → the corpus's **2026-07-01 Global rework**
(`docs/UMAMUSUME_REFERENCE.md` L1016; `SCENARIO-PUBLISHER-REFERENCES.md` L544). "Support Decks" in the
brief's nav (§3) is the repository's **Support Cards** (`SCREEN_SPEC.md` §3 nav order).

**Screen mapping.** This document's screens are `SCREEN-001` … `SCREEN-024`. `docs/research-scratch/DESIGN-CORPUS.md` section `## design-2.0.md` carries a
**different** inventory (`SCR-001` … `SCR-027`) that adds a Skills Planner and a Grand Concert panel and
omits Inheritance Event, Career Timeline and Veteran Comparison. The two lists disagree; neither is
authoritative over the other, and the development plan builds their union. "Port" = the screen exists today
and this rewrite re-renders it in Vue; "New" = no current screen.

| Brief ID     | Screen                        | Repo ID                                                   | Route / source                                                                   | State                                                            |
| ------------ | ----------------------------- | --------------------------------------------------------- | -------------------------------------------------------------------------------- | ---------------------------------------------------------------- |
| SCREEN-001   | Dashboard                     | — (SPA `Dashboard.vue`)                                   | `GET /` (`home`)                                                                 | Partly built; Active-Career/Recent-Veterans are named absences   |
| SCREEN-002   | Scenario Selection            | New                                                       | `config/scenarios.php`                                                           | New; binds the four-scenario matrix                              |
| SCREEN-003   | Trainee Selection             | `SCR-CAT-001`                                             | `GET /umamusume`                                                                 | Port                                                             |
| SCREEN-004   | Trainee Profile               | `SCR-CAT-002`                                             | `GET /umamusume/{slug}`                                                          | Port                                                             |
| SCREEN-005   | Build Target                  | New (`FR-F`)                                              | `training_runs.build_target`                                                     | New; spec `PROCESS-PLANS.md` `## trainer-advisor.md` §2          |
| SCREEN-006   | Legacy Lab                    | New (`FR-G`, record-only)                                 | Veteran library                                                                  | New; optimization parts **held** (`ADR-0020` §3)                 |
| SCREEN-007   | Support Deck Builder          | New                                                       | `runs.deck.sync`; `x-deck-editor`                                                | New screen over an existing write path                           |
| SCREEN-008   | Run Preflight                 | New                                                       | composes 005–007                                                                 | New                                                              |
| SCREEN-009   | Career Cockpit                | New (descends `SCR-RUN-003`)                              | `GET /training-runs/{run}`                                                       | New; the flagship screen                                         |
| SCREEN-010   | Training Decision             | New (`FR-F`)                                              | advisor spec                                                                     | New; per-training yields **held**                                |
| SCREEN-011   | Race Decision                 | New                                                       | `RaceCatalogSlot`                                                                | New; win-probability **held** (`ADR-0016`)                       |
| SCREEN-012   | Event Decision                | New                                                       | `TurnEvent` + `TurnEvents\*Payload`                                              | New over existing event recording                                |
| SCREEN-013   | Inheritance Event             | New                                                       | —                                                                                | New; **record-only** (`ADR-0020` §3)                             |
| SCREEN-014   | Scenario Panel: URA Finale    | New                                                       | `ura_finale`                                                                     | New                                                              |
| SCREEN-015   | Scenario Panel: Unity Cup     | New                                                       | `unity_cup`; `x-spirit-burst-roster`, `x-team-rank-gauge`, `x-team-race-panel`   | New over existing components                                     |
| SCREEN-016   | Scenario Panel: Trackblazer   | New                                                       | `trackblazer`; `x-shop-panel`, `x-grade-point-meter`, `x-epithet-checklist`      | New; shop recommendation **held** (rotation not modelled)        |
| SCREEN-017   | Scenario Race Planner         | New                                                       | `RaceCatalogSlot`                                                                | New; win-probability **held** (`ADR-0016`)                       |
| SCREEN-018   | Career Timeline               | New                                                       | `TurnEntry` / `TurnEvent`                                                        | New                                                              |
| SCREEN-019   | Career Result                 | New                                                       | `TrainingRun`                                                                    | New                                                              |
| SCREEN-020   | Save Veteran                  | New (`FR-G`)                                              | —                                                                                | New                                                              |
| SCREEN-021   | Veteran Library               | New (`FR-G`)                                              | —                                                                                | New                                                              |
| SCREEN-022   | Veteran Comparison            | New (`FR-G`)                                              | —                                                                                | New                                                              |
| SCREEN-023   | Database                      | `SCR-CAT-001/002`, `SCR-SKL-001/002`, `SCR-SUP-001/002`   | `GET /umamusume`, `/skills`, `/support-cards`                                    | Port; races and scenarios are new                                |
| SCREEN-024   | Settings                      | `SCR-SYS-002`                                             | `GET /preferences`                                                               | Ported to Vue 2026-10-04                                         |

**From `docs/research-scratch/DESIGN-CORPUS.md` section `## design-2.0.md` but not here:** a Skills Planner (`SCR-013`) and a Grand Concert panel (`SCR-017`).
Both are in the development plan's union. Grand Concert's panel is a baseline strip only (below).

**Not in either brief but already shipping:** the Review queue (`SCR-REV-001`, `GET /review`, ported to Vue)
and the run import screens (`SCR-RUN-004`/`005`). They stay.

**Corpus authority per screen's data.** Scenario panels read `config/scenarios.php`, whose per-number
provenance is stated in its own header (`GameTora scenarios.json`, verified 2026-09-27). Sparks/Affinity
data (006, 013, 021) is `UMAMUSUME_REFERENCE.md` §1.5. Support-card effects (007, 022) are §1.4. The
Trackblazer shop catalogue and epithet routes are transcribed from `SCENARIO-PUBLISHER-REFERENCES.md`.

**Unknowns the plan must render as absence, never invent:** Grand Concert mechanics (07 stub, §2.2.4
❌ UNVERIFIED); race distance/surface on goal races and any win probability (`ADR-0016`); per-training
stat yields and failure % (§1.1.1 ⚠️ STALE, excluded by the advisor spec); the official scenario title
"Twinkle Star Climax" (§7 conflict row 31 ❌ UNVERIFIED as the official name; "Trackblazer" is the export
label); the factor-slot count (§1.5 L641 ❌ UNVERIFIED); and the `app.ruleset` string, currently `null`.

---

## Uma Trainer Desk — Screen Specification

**Project:** Uma Trainer Desk
**Platform:** Local-only web application
**Stack:** Laravel 13 + Inertia.js + Vue 3
**Primary use:** Umamusume: Pretty Derby career planning and run management
**Target server:** Global English
**Primary scenarios:** URA Finale, Unity Cup, Trackblazer
**Future-compatible:** Additional scenarios such as Brighter Together: Our Grand Concert

---

### 1. Product Definition

Uma Trainer Desk is a local-first Trainer companion application for planning,
tracking, and analyzing Umamusume career runs.

The application does NOT attempt to play the game automatically.

It assists the player by:

- planning a career before starting
- evaluating trainee builds
- comparing inheritance candidates
- building support card decks
- tracking career state
- comparing available actions
- explaining recommendations
- tracking scenario-specific mechanics
- recording the completed career
- maintaining a Veteran/Legacy library

The user remains the final decision-maker.

---

## 2. Primary User Flow

```text
Dashboard
    │
    ▼
New Career
    │
    ▼
Scenario Selection
    │
    ▼
Trainee Selection
    │
    ▼
Build Target
    │
    ▼
Inheritance / Legacy Lab
    │
    ▼
Support Deck Builder
    │
    ▼
Run Preflight
    │
    ▼
Career Cockpit
    │
    ├── Training
    ├── Race
    ├── Rest
    ├── Recreation
    ├── Scenario Action
    ├── Event
    └── Inheritance
    │
    ▼
Career Result
    │
    ▼
Save Veteran
    │
    ▼
Veteran / Legacy Library
```

---

## 3. Application Shell

All screens except the initial setup screens use the Application Shell.

```text
┌──────────────────────────────────────────────────────────┐
│ Logo / App Name                  Current Career / Profile │
├──────────────┬───────────────────────────────────────────┤
│              │                                           │
│ Navigation   │               Page Content                │
│              │                                           │
│ Dashboard    │                                           │
│ New Career   │                                           │
│ Legacy Lab   │                                           │
│ Support      │                                           │
│ Veterans     │                                           │
│ Database     │                                           │
│ Settings     │                                           │
│              │                                           │
├──────────────┴───────────────────────────────────────────┤
│ Status / Local Data / Version                            │
└──────────────────────────────────────────────────────────┘
```

### Global navigation

- Dashboard
- New Career
- Legacy Lab
- Support Decks
- Veterans
- Database
- Settings

When a career is active, a dedicated Career navigation becomes available.

---

## 4. SCREEN-001 — Dashboard

### Purpose

Landing screen for returning users.

### Primary goals

- resume active career
- start new career
- inspect recent veterans
- access legacy tools
- see application/data status

### Layout

```text
┌──────────────────────────────────────────────────────────┐
│ Uma Trainer Desk                                         │
│                                                          │
│ Welcome back, Trainer.                                  │
│                                                          │
│ ┌──────────────────────────────────────────────────────┐ │
│ │ ACTIVE CAREER                                        │ │
│ │                                                      │ │
│ │ Tokai Teio                                           │ │
│ │ Trackblazer · Senior Year                            │ │
│ │                                                      │ │
│ │ Turn 37 · Energy 72 · Grade Points 280              │ │
│ │                                                      │ │
│ │ [Resume Career]                                      │ │
│ └──────────────────────────────────────────────────────┘ │
│                                                          │
│ [New Career]   [Legacy Lab]   [Support Decks]            │
│                                                          │
│ Recent Veterans                                          │
│ Recent Builds                                            │
└──────────────────────────────────────────────────────────┘
```

### Components

- ActiveCareerCard
- NewCareerButton
- RecentVeterans
- RecentBuilds
- DataStatus
- QuickActions

### Empty state

If no active career exists:

> No active career. Start a new training run.

---

## 5. SCREEN-002 — Scenario Selection

### Purpose

Choose the career scenario.

### Scenario cards

Each card must display:

- scenario name
- short description
- complexity
- primary mechanic
- recommended use
- availability
- ruleset version

### Current scenarios

#### URA Finale

Primary mechanic:

- standard career progression
- character goals
- Happy Meek system

#### Unity Cup

Primary mechanic:

- team development
- team races
- Spirit
- Spirit Bursts
- Extreme Spirit Bursts

#### Trackblazer

Primary mechanic:

- Grade Points
- Shop Coins
- Pro Shop
- Rival races
- Twinkle Star Climax

### Future scenarios

Future scenarios must be represented through the same Scenario interface.

The UI must not assume that every scenario uses:

- the same objectives
- the same currencies
- the same finale
- the same training rules

---

## 6. SCREEN-003 — Trainee Selection

### Purpose

Select the trainee for the career.

### Features

- searchable roster
- sorting
- filtering
- trainee comparison
- detailed trainee profile

### Filters

- Surface
- Distance
- Running style
- Aptitude
- Growth rate
- Scenario suitability
- Unique skill
- Character

### Trainee card

```text
┌──────────────────────────────────────┐
│ Tokai Teio                          │
│                                      │
│ Turf       A                         │
│ Sprint     F                         │
│ Mile       E                         │
│ Medium     A                         │
│ Long       B                         │
│                                      │
│ Pace Chaser A                        │
│                                      │
│ Growth                                     │
│ Speed +20%                              │
│ Power +10%                              │
│                                      │
│ [View Profile] [Select]              │
└──────────────────────────────────────┘
```

---

## 7. SCREEN-004 — Trainee Profile

### Purpose

Provide complete build-relevant information before selection.

### Sections

#### Basic

- Name
- Rarity
- Version
- Growth rates

#### Aptitudes

- Surface
- Distance
- Running style

#### Skills

- Unique skill
- Starting skills
- Awakening skills
- Event skills

#### Career goals

Display the trainee's expected career objectives.

#### Build analysis

Show:

- ideal distances
- suitable running styles
- recommended stat distribution
- useful inheritance
- useful support types

---

## 8. SCREEN-005 — Build Target

### Purpose

Define what the player wants from the run.

### Target purpose

Options:

- Story Clear
- Competitive Build
- Champions Meeting
- Parent Farming
- Skill Farming
- General Training

### Race profile

- Surface
- Distance
- Running style

### Target stats

```text
Speed       1200
Stamina      700
Power       1000
Guts         400
Wit          900
```

### Skill priorities

Each skill may be marked:

- Required
- High priority
- Optional
- Ignore

### Target summary

The screen must generate a human-readable summary:

> Build Tokai Teio for Turf Medium-distance Pace Chaser.
> Prioritize Speed, Power and Wit.
> Focus inheritance on Medium and Pace Chaser aptitude.

---

## 9. SCREEN-006 — Legacy Lab

### Purpose

Select and optimize inheritance.

This is a primary feature of the application.

### Layout

```text
┌──────────────────────────────────────────────────────────┐
│ LEGACY LAB                                               │
├───────────────────────┬──────────────────────────────────┤
│ Candidate Veterans    │ Current Build                    │
│                       │                                  │
│ Search                │ Trainee                          │
│ Filters               │       ↓                          │
│                       │ Parent A      Parent B            │
│ Veteran cards         │       ↓          ↓                │
│                       │ Grandparents                     │
│                       │                                  │
│                       │ Inheritance Analysis             │
└───────────────────────┴──────────────────────────────────┘
```

### Candidate filters

- Blue Sparks
- Pink Sparks
- Green Sparks
- White Sparks
- Distance
- Surface
- Running style
- Skills
- Race history
- Scenario factor
- Affinity

### Parent slots

- Parent A
- Parent B

### Grandparent visualization

Show:

```text
Parent A
├── Grandparent A1
└── Grandparent A2

Parent B
├── Grandparent B1
└── Grandparent B2
```

### Compatibility analysis

Display:

- affinity
- expected inheritance
- useful Sparks
- missing Sparks
- race compatibility
- skill coverage

### Recommendation

Provide up to three candidate combinations.

Each recommendation must include a reason.

Example:

> Recommended because it provides the strongest Medium aptitude coverage
> while retaining the required Speed and skill Sparks.

---

## 10. SCREEN-007 — Support Deck Builder

### Purpose

Build the six-card support deck.

### Slots

- five owned cards
- one borrowed card

### Layout

```text
┌──────────────────────────────────────────────────────────┐
│ SUPPORT DECK                                             │
│                                                          │
│ [Speed] [Speed] [Power] [Wit] [Wit] [Borrowed]          │
│                                                          │
├──────────────────────────────────────────────────────────┤
│ DECK ANALYSIS                                            │
│                                                          │
│ Training        ████████░░                              │
│ Skill Coverage  █████████░                              │
│ Race Bonus      ██████░░░░                              │
│ Bond Potential  █████████░                              │
│ Scenario Fit    ████████░░                              │
└──────────────────────────────────────────────────────────┘
```

### Filters

- Type
- Rarity
- Level
- Limit Break
- Skill
- Training bonus
- Race bonus
- Scenario compatibility

### Deck analysis

Must explain:

- strengths
- weaknesses
- skill coverage
- stat coverage
- scenario compatibility
- recommended replacement

---

## 11. SCREEN-008 — Run Preflight

### Purpose

Final validation before creating a career run.

### Sections

#### Build

- trainee
- scenario
- target
- inheritance

#### Support deck

- six cards
- deck analysis

#### Target

- stats
- skills
- race profile

#### Warnings

Examples:

- Missing distance aptitude
- Weak stamina plan
- Low skill coverage
- Poor support synergy
- Missing scenario requirement

### Actions

- Back
- Edit Legacy
- Edit Deck
- Edit Target
- Start Career

---

## 12. SCREEN-009 — Career Cockpit

### Purpose

Primary screen used throughout the career.

This is the application's most important screen.

### Layout

```text
┌─────────────────────────────────────────────────────────────┐
│ Career Header                                               │
├──────────────┬──────────────────────────────┬───────────────┤
│ Timeline     │ Current Turn                 │ Advisor       │
│              │                              │               │
│ Career       │ Current stats                │ Best Action   │
│ milestones   │ Training options             │ Why           │
│              │ Race                         │ Projection    │
│              │ Scenario action              │ Alternatives  │
│              │                              │               │
├──────────────┴──────────────────────────────┴───────────────┤
│ Scenario Panel / Upcoming Events / Race Calendar            │
└─────────────────────────────────────────────────────────────┘
```

### Header

Show:

- scenario
- year
- month
- turn
- energy
- mood
- fans
- skill points
- scenario resources

### Next Decision Component (new spec)

The Advisor column is a shared component, not a screen variant. It receives scenario-specific objectives, constraints, and rules via props — never by branching on scenario name.

**Required behavior:**

- Display the current objective and the reason it matters now.
- Present a recommended action only when sufficient verified rules and state support it.
- Explain the decisive factors and relevant trade-offs (constraints, opportunity cost, missing information).
- Show viable alternatives where the available data permits a meaningful comparison.
- Distinguish recorded facts, calculated results, estimates, and unknown values using the trust vocabulary (Confirmed / Calculated / Estimated / Unknown, per `ADR-0020` §2).
- Let the user choose another action without being blocked by the recommendation.
- Record the action, expected consequences where supportable, and actual result when entered.
- Re-evaluate the next decision after the run state changes.

**Explicit states:**

```
No action selected yet
  → "No recommendation — insufficient verified rules or state to rank actions"

Action under review
  → show forecasted consequences with provenance labels

Action recorded, actual result pending
  → grey out, show "ENTER ACTUAL RESULT"

Action recorded, actual result entered
  → show reconciliation (expectation vs. observation)

No eligible action
  → "No viable action available — all options violate a constraint"
```

---

## 13. Career State Panel

Always display:

```text
Speed
Stamina
Power
Guts
Wit

Energy
Mood
Fans
Skill Points
```

Stat targets should appear as progress bars.

---

## 14. SCREEN-010 — Training Decision

### Purpose

Compare available training actions.

### Training card

```text
SPEED

Facility level: 3 (repetition: 12/16)
Energy cost: 19

Support: 3 flames
Bond gains: +7 / +7 / +5

Target impact:
Speed: 742 / 800 (93%)

Missing data: exact stat yield (+X Speed), failure probability
```

**No per-training stat yields.** Numeric training gains are unsourced (`ADR-0020` §3, deferral table line 13-14). The card explains the factors used to compare actions without showing unsupported yield or failure numbers.

### Actions

- Train
- Inspect details

### Details

Display:

- facility level and progress to next
- energy cost
- support flame count
- bond gains (per support card, where recorded)
- scenario effects (where verified, e.g. Happy Meek duel on Stamina)
- target impact (distance to goal, with provenance label)

---

## 15. SCREEN-011 — Race Decision

### Purpose

Select whether and where to race.

### Race cards

Display:

- race name
- grade
- distance
- surface
- running style
- expected reward (Confirmed / Calculated / Estimated)
- fan gain
- skill point gain
- scenario reward (if sourced)

**No numeric win probability.** Race prediction is deferred on the `ADR-0016` data blocker (`ADR-0020` §4). Instead, show readiness bands using verified stat comparisons:

```
READINESS BANDS

EXCELLENT  Your stat profile matches the race requirements
GOOD       Minor deficits, win is plausible with skill support
BORDERLINE Key stat below the distance-appropriate floor
POOR       Stamina or Speed well below the distance floor

Missing data: opponent strength, exact placement modifiers
```

### Risk indicator

```text
LOW       No fatigue chain, full energy
MEDIUM    2-consecutive race chain (see §10 Race Fatigue)
HIGH      3+ consecutive races (fatigue event likely)
```

The exact thresholds come from the race-fatigue table (`SCENARIO-PUBLISHER-REFERENCES.md §7`); they are Trackblazer-specific and must not be hardcoded in the component.

---

## 16. SCREEN-012 — Event Decision

### Purpose

Record event choices and their effects.

### Layout

```text
EVENT

Kitasan Black

Option A
+20 Speed
+10 SP

Option B
+30 Energy
+10 Mood

────────────────────

Trainer Advisor

Recommended: Option B

Reason:
Energy is currently low and the next
important race is approaching.
```

The user must be able to override the recommendation.

---

## 17. SCREEN-013 — Inheritance Event

### Purpose

Track inheritance milestones during the career.

### Display

- parent Sparks
- grandparent Sparks
- expected inheritance
- previous inheritance
- newly activated Sparks

### Timeline

```text
Career Start
    ✓

Classic April
    ○

Senior April
    ○
```

---

## 18. SCREEN-014 — Scenario Panel: URA Finale

### Purpose

Expose URA-specific information.

### Components

- character goals
- URA progression
- Happy Meek status
- finale preparation
- upcoming mandatory races

### Happy Meek

Show:

- current level (wins / 6)
- duel availability (next training turn)
- potential reward (stat, cap bump, Racing Spirit hint, SP)
- final-race contribution (powered-up state at 6 wins → Past My Limits)

### Career Milestone Tracker (new)

A milestone strip showing the current career objective, deadline, completion state, and next required step:

```
MILESTONES

[ ✓ ] Valentine's gate (60k fans)    completed, turn 8
[ • ] Fan Fest gate (70k fans)       32k / 70k, 18 turns left
[ ○ ] Holiday gate (120k fans)       upcoming, 42 turns left
[ • ] URA Qualifier                scheduled, turn 36
[ ○ ] URA Semifinals               lock after Qualifier win
[ ○ ] URA Finals                   lock after Semifinals win
```

Source: `docs/scenarios/01-ura-finale.md:77-84`, `UMAMUSUME_REFERENCE.md §2.2.1`.

### Readiness Summary (new)

Assess race preparation using verified data only:

```
READINESS FOR URA QUALIFIER (Scheduled: turn 36)

Distance    Medium    ✓ Stamina 610 ≥ 600 floor
Surface     Turf      ✓ aptitude B
Stat target Speed 800  ⚠ 742 / 800
Skills      Last spurt  ✓ owned
            Power surge  ⚠ hint unlocked, not purchased

Missing data: exact qualifier distance (determined by most-raced type)
```

### Training-vs-Recovery Decision Area (new)

Explain the current priority and known trade-offs: train, rest, or race. Each option shows its primary constraint and what it preserves for later:

```
RECOMMENDED: TRAIN STAMINA
  Supports: Medium-distance floor ahead of Qualifier
  Constraint: facility at Lv 2, 1 block from Lv 3
  Trade-off: -40 energy; Summer camp in 6 turns

[ Train Stamina ]   [ Race a Tier 2 (SP: +40) ]   [ Rest ]
```

### Pre-Final Review (new)

Before the three finale races, identify recorded strengths, known deficits, and unresolved requirements:

```
PRE-FINALE READINESS

Strengths:   Speed 920+, Stamina 650+, Guts 700+
Deficits:    Power 580 (below 600 Medium floor)
Unresolved:  Qualifier distance (tied to most-raced type)
```

---

## 19. SCREEN-015 — Scenario Panel: Unity Cup

### Purpose

Manage scenario-specific team progression.

### Components

- team rank
- team member stats
- team composition
- team races
- Special Training
- Spirit
- Spirit Burst
- Extreme Spirit Burst

### Team management view (enhanced)

A team roster dashboard that tracks members, their relevant strengths, development needs, and contribution to upcoming competitions. Show each member's aptitude and available skills, and distinguish individual trainee development from team-level progression:

```
TEAM ROSTER                                Next Cup: 4 turns

Member          Stat focus   Rank  Needs before Cup        Contribution
Rice Shower      Stamina     B     +30 Sta for race 2     Burst: Stamina tile
Fine Motion      Speed       A     —                      Burst: Speed tile (Lv 2)
Biwa Hayahide    Power       C     +50 Power, +2 wins     Epithet: Goddess route
Silence Suzuka†   Guts       D     Bond 2 bars            Burst: Guts tile
Mejiro Ryan†      Wit        C     +30 Wit                Burst: Wit tile

† Story characters (no bond gauge, no Friendship training)
```

Source: `docs/scenarios/02-unity-cup.md`, `SCENARIO-PUBLISHER-REFERENCES.md §7`.

### Tournament preparation (new)

Show known requirements, relevant rival information, and unresolved readiness concerns. Explicit missing-data states when the opponent, matchup, or team calculation is unavailable:

```
TOURNAMENT READINESS              Classic late June • Next Cup in 4 turns

OPPONENT PROJECTIONS
  ○ Strong     rank if won: A+         ⚠ Rice Shower needs +30 Stamina
  ○ Medium     rank if won: B          ✓ Biwa's Power is borderline
  ○ Weak       rank if won: C

RACE PREVIEW
  Sprint     ✓ covered (Fine Motion +20 from burst)
  Mile       ⚠ borderline (Fine Motion +30 needed)
  Medium     ✓ covered
  Long       ✓ covered
  Dirt       ⚠ Mejiro Ryan bond not ready

Status: Insufficient data for opponent strength projection
```

### Spirit panel

Display:

- current Spirit
- burst readiness (gauge vs. configured threshold)
- recommended timing (held advice — see `ADR-0020` §3)
- projected benefit (held advice — see `ADR-0020` §3)

---

## 20. SCREEN-016 — Scenario Panel: Trackblazer

### Purpose

Manage Trackblazer-specific resources.

### Components

- Grade Points
- Shop Coins
- Pro Shop
- purchased items
- Rival races
- race schedule
- Twinkle Star Climax

### Race calendar (new)

Visualize upcoming races, known objectives, and the current preparation window. Chronological view with distance, surface, grade, and consequence of selection:

```
RACE CALENDAR                    Classic • Turn 31

UPCOMING (next 6 turns)
  Turn 34  G2 Mile (Turf)        +80 GP, +100 coins  ✓ in plan
  Turn 35  G3 Sprint (Turf)      +60 GP, +80 coins    ✓ in plan
  Turn 36  G1 Long (Turf)        +100 GP, +150 coins  ⚠ tight spacing

CONSEQUENCES OF SKIPPING
  G2 Mile: -80 GP from runway, -2 route progress
  G3 Sprint: no objective impact
  G1 Long: -100 GP, epithet route at risk

Status: 128 GP needed, 220 projected (with wins)
```

Source: `SCENARIO-PUBLISHER-REFERENCES.md §7`, `docs/Trackblazer-plan.md §6`.

### Resource ledger (new)

Track Grade Points, purchased items, and remaining balances. Distinguish actual balances from planned spending and hypothetical spending:

```
RESOURCE LEDGER

GRADE POINTS
  Current:        172 / 300 (Classic period)
  Projected:      392 (2 G2 wins + 1 G3 win)
  Shortfall:      none

COINS
  Current:        210
  Planned spend:  100 (Summer items)
  Reserve:        150 (final shop)

ITEMS
  Good-Luck Charm  1/5   ✓ held
  Master Cleat     1/5   ⚠ use before G1 Long
  Empowering Meg.  2/5   ✓ Summer stock
```

### Race-versus-training comparison (new)

Make the opportunity cost of each option explicit — not just GP vs. stats, but fatigue, recovery, and epithet progress:

```
RACE VS. TRAINING

Race G2 Mile:        +80 GP, +100 coins, 1 fatigue, -2 route progress if skipped
Train Speed:         +6 Speed, +3 Power, +5 SP, facility +0.25 progression
Rest:                clears fatigue chain, -1 turn of GP

ADVISOR: Race G2 Mile — you are 128 GP short with 9 turns left.
         Training Speed gains less than the checkpoint risk costs.
```

### Schedule risk warnings (new)

Flag potentially unsustainable race sequences, inadequate recovery, and missed objective opportunities, using only supported data:

```
SCHEDULE RISKS

⚠ 5 consecutive races planned in Classic spring (fatigue risk)
⚠ Year-end gate: 60k fans + Akikawa bond 31 needed
○ Rival races are random; cannot be scheduled
```

Source: fatigue table `SCENARIO-PUBLISHER-REFERENCES.md §7 lines 229-242`, year-end gate §13.

### Late-career resource planning (new)

Review remaining objectives and available resources before the finale:

```
LATE-CAREER REVIEW

Remaining GP:     190 (Senior period)
Coins:            150 (no income after finale races)
Hammers held:     3/5  ✓ recommended
Megaphones:       2/5  ✓ Summer stock sufficient
```

### Shop

Display:

- item
- cost
- effect
- duration
- eligibility (locked until Debut, per `SCENARIO-PUBLISHER-REFERENCES.md` line 374)

**No recommendation field.** The shop rotation is not modelled (`config/scenarios.php` `trackblazer.shop`); displaying a "recommended purchase" would fabricate data. Community opinions are shown as separate, labelled tags only.

Example:

```text
Pro Shop                                coins 210   refresh in 3 turns

Master Cleat Hammer    40   +35% race bonus, 1 turn     held 1/5
Empowering Megaphone   70   +60% training, 2 turns      held 2/5
Reset Whistle          20   shuffle support cards       held 0/5
Good-Luck Charm        40   0% failure, 1 turn          held 1/5
Vita 65                75   +65 energy                  held 0/5

Community tags (uma.guide): Must Buy / Skip
```

---

## 20b. SCREEN-016b — Scenario Panel: Grand Concert (our_grand_concert)

### Purpose

Surface verified Grand Concert state. The evidence hierarchy (`docs/proposals/Our-Grand-Concert-plan.md`) determines what is renderable:

- **Available (Confirmed):** Performance type (Dance, Passion, Vocals, Visuals, Composure) and signed delta as turn observations.
- **Observed but undocumented:** nothing beyond the above.
- **Documented as unverified:** starting values, caps, Lesson costs, Song titles, Live Bonus percentages, Hype, Promo Concert timing, outcome words beyond "Great Success".
- **Inferred/community:** none rendered.

### Components

- Performance strip (type selector + signed delta input)
- Finale presence indicator (catalogue row-driven, not config-key-driven)
- Blocked-mechanic placeholders (Lessons, Songs, Live Bonuses, Promo Concerts) with the evidence gate and reopen condition

### States

- **Pre-debut:** Performance input disabled; "Waiting for career start."
- **Active:** Performance type selector (5 options, no default) + signed delta field.
- **Recording error:** zero, decimal, or invalid terminology rejected (`GrandConcertPanelTest.php:93,103`).

### What is explicitly not built

- Starting values, current totals, caps, acquisition formulas, Lesson costs, Song titles, Hype, Live Bonus percentages, Promo Concert scheduling, or any recommendation logic. The panel states the evidence boundary and links to `SCREEN_SPEC.md` §7-19 through §7-23 for the reopen conditions.

Source: `docs/proposals/Our-Grand-Concert-plan.md` Slices 1–18, `docs/UMAMUSUME_REFERENCE.md §2.9`.

---

## 21. SCREEN-017 — Scenario Race Planner

### Purpose

Provide scenario-aware race planning.

### Features

- upcoming races
- mandatory races
- optional races
- Rival races
- reward comparison
- target alignment
- expected risk

### Recommendation

```text
RECOMMENDED

Race — Kyoto 1600m (G2 Mile, Turf)

READINESS: GOOD (Speed meets target, Stamina at floor)

Benefits:
  + 80 Grade Points
  + 100 Shop Coins
  + Route progress (2 of 3 on Spring Champion line)

Trade-off:
  -2 fatigue risk on this chain
  Training Speed now would gain less than the checkpoint risk

Missing data: opponent strength, exact placement modifiers
```

**No numeric win probability.** Race prediction is deferred on the `ADR-0016` data blocker. The recommendation shows readiness bands, known benefits, and the opportunity cost — never a fabricated percentage.

---

## 22. SCREEN-018 — Career Timeline

### Purpose

Provide a complete historical view of the run.

### Timeline events

- training
- races
- events
- inheritance
- scenario actions
- purchases
- important decisions

Each event records:

- before state
- action
- expected consequence (where supportable, with provenance label)
- actual result (when entered by the user)
- after state
- advisor confidence at time of decision

Example:

```text
Turn 37

TRAIN SPEED

Before:
Speed 842
Energy 72

Expected (Calculated):
+6 Speed, +2 Power, +5 SP
Facility: 8/12 to Lv 3

Action: Train Speed  ✓

Actual:
Speed 904
Energy 51

After:
Speed 904
Energy 51
Facility: 9/12 to Lv 3

Advisor at turn 37: recommended Speed training (verified, repetition level)
```

---

## 23. SCREEN-019 — Career Result

### Purpose

Summarize the completed career.

### Sections

#### Final build

- stats
- skills
- aptitudes

#### Race history

- races
- wins
- losses
- G1 victories

#### Scenario result

- objectives
- scenario score
- scenario rewards

#### Build quality

- target completion
- skill coverage
- inheritance quality

---

## 24. SCREEN-020 — Save Veteran

### Purpose

Convert the finished trainee into a reusable Veteran record.

### Fields

- Veteran name
- tags
- notes
- parent suitability
- intended use

### Suggested tags

- Speed
- Stamina
- Power
- Guts
- Wit
- Sprint
- Mile
- Medium
- Long
- Dirt
- Turf
- Front Runner
- Pace Chaser
- Late Surger
- End Closer
- Skill
- Race
- Scenario

---

## 25. SCREEN-021 — Veteran Library

### Purpose

Manage completed veterans.

### Features

- search
- filtering
- sorting
- comparison
- favorite
- archive
- delete

### Sort options

- Spark quality
- aptitude
- skill coverage
- race history
- completion date
- scenario
- overall usefulness

---

## 26. SCREEN-022 — Veteran Comparison

Compare up to four veterans.

Columns:

- stats
- Sparks
- skills
- race history
- aptitude
- scenario factor
- inheritance usefulness

---

## 27. SCREEN-023 — Database

### Purpose

Browse game data.

### Sections

- Trainees
- Support Cards
- Skills
- Races
- Events
- Scenarios
- Shop Items
- Sparks

Database screens are informational and should not overwhelm the main career workflow.

---

## 28. SCREEN-024 — Settings

### Categories

#### General

- theme
- language
- units
- default scenario

#### Recommendation

- recommendation aggressiveness
- risk tolerance
- stat target defaults
- race risk thresholds

#### Data

- import
- export
- backup
- restore
- reset

#### Game Version

Display:

```text
Global Ruleset
Version: YYYY.MM
```

Game data and scenario rules must be versioned.

---

## 29. Global Interaction Rules

### Recommendation hierarchy

The recommendation engine evaluates:

1. Survival / avoiding failure
2. Mandatory objectives
3. Scenario objectives
4. Build targets
5. Support bonding
6. Scenario resources
7. Long-term optimization

### Recommendations must be explainable

Never show:

> "Best action: Train Speed."

without a reason.

Instead:

> Train Speed because Speed is 94 below target, three supports gain bond,
> and there is no mandatory race before the next recovery opportunity.

---

## 30. Recommendation States

Every recommendation has:

- Recommended action
- Confidence
- Reasons
- Alternatives
- Risks

Example:

```text
BEST ACTION
Train Speed

Confidence: HIGH

Reasons:
+ Target deficit
+ Strong support concentration
+ Low failure chance
+ No immediate race requirement

Alternative:
Train Wit

Risk:
Delays Speed target by approximately one turn.
```

---

## 31. Data Integrity

The application must distinguish:

CONFIRMED

- manually entered state
- database values

CALCULATED

- derived stat
- recommendation
- projected outcome

PROBABILISTIC

- estimated race result
- RNG outcome

UNKNOWN

- information not yet entered

---

## 32. Career State Persistence

Every action should be persisted locally.

A CareerRun contains:

- scenario
- trainee
- target
- inheritance
- support deck
- current state
- action history
- scenario state
- final result

The user must be able to:

- resume
- inspect history
- undo the latest action
- manually correct state

---

## 33. Responsive Behavior

### Desktop

Primary target.

Use:

```text
Navigation
+
Timeline
+
Main Decision Area
+
Advisor
```

### Tablet

Collapse:

- Timeline into drawer
- Advisor into collapsible panel

### Mobile

Use a single-column workflow:

```text
Header
Current State
Recommendation
Action
Scenario
Timeline
```

Never require horizontal scrolling for primary decisions.

---

## 34. Accessibility

Requirements:

- keyboard navigation
- visible focus
- minimum touch target 44px
- semantic headings
- accessible labels
- do not rely on color alone
- support reduced motion
- sufficient contrast
- numerical values paired with visual indicators

**Image slots** are governed by `docs/research-scratch/DESIGN-CORPUS.md` section `## design-2.0.md` §42 (alt text, reserved-box contrast, label-in-name) and §45a (per-screen placement, geometry, click action). WCAG 2.2 AA conformance for slot-bearing screens is verified by the axe pass in `docs/research-scratch/PROCESS-PLANS.md` section `## frontend-development-plan.md` §12, *per screen*; the law-by-law review of "recognition rather than recall" and "minimal design" sits in that plan's §13.

---

## 35. Sourced image slots

Per-screen placement is `docs/research-scratch/DESIGN-CORPUS.md` section `## design-2.0.md` §45a; the Blade parity rule is `DESIGN.md` §4.7. A cell that ships no `<img>` today stays a text-only row. The four absence states (`never mirrored`, `gone upstream`, `unreadable on disk`, `not yet mirrored`) render identically — there is no broken frame, no grey box, no placeholder glyph (R-31). Skill icons are deferred until `skills.iconid` exists as a column (a migration of its own; `ADR-0021` Verification records the finding). Reduced motion is inherited from `docs/research-scratch/DESIGN-CORPUS.md` section `## design-2.0.md` §43 and from `docs/research-scratch/PROCESS-PLANS.md` section `## frontend-development-plan.md` §12.2: a slot's reveal is no animation, so a fixed focal-length layout is what a screen reader and a sighted Trainer both experience.

---

## 36. Error States

The UI must clearly handle:

- incomplete career state
- missing database data
- invalid support deck
- invalid inheritance configuration
- stale game ruleset
- corrupted local data
- unavailable recommendation

Example:

> Recommendation unavailable because Energy and Mood have not been entered.

Do not silently guess.

---

## 37. Screen States for Changed Screens

For each screen that changed in this pass, the following states must be defined and handled:

- **Initial / empty** — no run data loaded.
- **Normal populated** — full run state with verified data.
- **Partial or unknown data** — some fields missing, stale, or `N/A`.
- **Validation or recording error** — user entry fails validation or save fails.
- **Completed or no-longer-applicable** — objective achieved or superseded.
- **Recommendation unavailable** — insufficient rules or state to rank actions.

| Screen        | States asserted in browser tests                                                                 |
| ------------- | ----------------------------------------------------------------------------------------------- |
| SCREEN-009    | Empty run (no state), populated run with recommendation, recommendation unsupported, action recorded but result pending, action recorded with reconciliation |
| SCREEN-014    | Pre-debut (no fan gates active), gates active with partial progress, qualifier passed, pre-finale review, all gates completed |
| SCREEN-015    | No team data, partial roster, full roster with mixed ranks, tournament locked, team rank `N/A` (no recorded letter) |
| SCREEN-016    | Pre-debut (shop locked), shop open with partial coin balance, GP period completed, finale reached (coin income ends), schedule risk flagged |

**Keyboard and narrow-screen behavior:** the primary recommendation must remain visible above secondary charts and logs at 320px width. The Next Decision component must be reachable via keyboard in a single `Tab` from the stat bands. Colour is never the sole status indicator — text and icon must carry the same meaning.

---

## frontend-fidelity-2026-10-13.md

> **Consolidated 2026-10-21 (Round 18, owner-ordered).** Source: `docs/proposals/frontend-fidelity-2026-10-13.md`, reproduced verbatim below with its headings demoted one level. The original was deleted from `docs/proposals/` after line-by-line verification (0 missing). Cite this anchor, not the deleted path. <!-- census: quoted-original -->

## Frontend fidelity — why the shipped surface reads as a wireframe, and the two-track path to a product

> **Reference only. Not authorization.** Nothing in this file is a decision. No component, token, copy
> string, gate or spec line was changed to produce it. Each section names the files an approval would
> touch and touches none of them.
>
> Recorded 2026-10-13 from the owner's own assessment ("these are what I consider high-fidelity
> wireframes, but not what the frontend of this application could be"), the six dark-theme wizard
> screenshots supplied with it, a read of the visual contract (`DESIGN.md`), the richer 2.0 target
> (`docs/research-scratch/DESIGN-CORPUS.md` section `## design-2.0.md`), the token block (`resources/css/app.css`) and the three existing UI
> audits in this directory. It is the fifth proposal-side audit file; it reconciles the other four rather
> than restating them. **Added 2026-10-13:** the 25 full-page captures in `docs/proposals/HLF_reference/`
> (a v0 high-fidelity build) are folded in at §4 as the concrete, screen-by-screen target. They are still,
> in the owner's words, a high-fidelity wireframe rather than the finished product.
>
> **The one-line finding:** the surface is not under-built as engineering. It is a faithful implementation
> of an **austere** visual contract, and the richer target that would make it read as a product already
> exists on paper — but sits **lower in the precedence chain**, so the austere reading wins by default.

### 0. What governs this question

Per `AGENTS.md` §2 the chain is: quality bar → gate registry → ADRs → `PRD.md` → `ARCHITECTURE.md` →
`SCREEN_SPEC.md` → **`DESIGN.md`** (visual system) → `docs/research-scratch/DESIGN-CORPUS.md` section `## design-2.0.md` (reference target).
The last two are the whole argument:

1. **`DESIGN.md` is the shipped contract** (`DESIGN.md:41-47`, `ADR-0006` authority split). It carries the
   austere rules: dial `ENERGY 1 / RHYTHM 1 / MOTION 1` (`:24-27`), "no pastel anime gradients, no soft drop
   shadows, no marketing layout" (`:58-60`), "Radius `rounded-md` consistently" (`:243`), "Shadows: none.
   Separation comes from the rule edge and the raised/sunken step" (`:244-245`), "No page animations; a desk
   does not move" (`:696`).
2. **`docs/research-scratch/DESIGN-CORPUS.md` section `## design-2.0.md` is the richer target** and it is **outranked**: it describes a four-level visual
   hierarchy (`:290-358`), a radius scale (`:525-540`), sparing shadows for dialogs/panels/recommendations
   (`:543-560`), a Display/Heading/Body/Numeric type hierarchy (`:443-494`), and cards as the fundamental
   grouping mechanism (`:586-613`). Under `AGENTS.md` §2 the reference target loses to `DESIGN.md`; where
   they disagree, the austere clause ships.

So the wireframe read is **the precedence chain working as designed**, not a build failure. Changing it is
a **contract amendment** (an owner ruling), which is why this file has two tracks and only one of them is a
styling job.

### 1. The verdict, stated plainly

- **The engineering is not the problem.** The audits record a token-only surface with zero `dark:` forks,
  44px targets, one focus ring, both themes measured, and single-owner discipline that holds
  (`docs/research-scratch/AUDIT-AND-VERIFICATION.md` §"remaining-screens-ui-audit-2026-10-13.md" §1.3). The dark screenshots show correct, accessible,
  consistent components.
- **The expression is the problem**, and it is **partly deliberate** (the `DESIGN.md` clauses above) and
  **partly unlanded craft** (the type scale, the measure, the density — things both documents already ask
  for and neither has shipped).
- **"What the frontend could be" is already written down** — it is `docs/research-scratch/DESIGN-CORPUS.md` section `## design-2.0.md`. The task is not to
  invent a direction; it is to decide which of the 2.0 expression clauses become binding, and to land the
  craft both documents agree on.

### 2. Why it reads as a wireframe (evidence)

#### 2.1 The tells

| Tell (visible in the screenshots) | Evidence |
| --- | --- |
| Cards dissolve into the page; the surface is flat | `DESIGN.md:244-245` "Shadows: none"; `app.css:20-21` "No shadows and no custom radius tokens". In the **dark** override the ramp collapses: `page #0D0C0F` ≈ `panel #121013`, and the only separation is the hairline `rule #2E2C33` (`app.css:221-231`). |
| Uniform typography; nothing leads the eye | `DESIGN.md:188-190` specifies section `text-lg` / title `text-2xl`; the audit measured **75** headers at `text-base` and the shell `#title` at `text-base` (`docs/research-scratch/AUDIT-AND-VERIFICATION.md` §"remaining-screens-ui-audit-2026-10-13.md" §2.2, X-2). |
| One accent, grayscale everywhere else | `app.css:57` "Green is action and affordability only, never 'good'"; `DESIGN.md:53-54` "Colour is semantic only". Lime on the button is the page's only hue. Sampled against the client sheets and the official site in §2.3. |
| Native form controls | selects/inputs are `border border-rule bg-raised rounded-md` and nothing more; there is no shared form-control component. |
| Content pinned left, vast dead space | `AppLayout.vue:295` `main` has no `max-w` (`remaining-screens` §2.8, F-A2); the ratified widths (`DESIGN.md:210-221`) are used by almost nobody. |
| No imagery | the artwork system exists and is built (`ADR-0021`, `uma:fetch-art`) but no wizard or catalog-in-wizard surface shows a frame. |
| No motion | `DESIGN.md:692-709`: MOTION 1, and `transition-colors` "ships nowhere"; one `animate-pulse` in the whole tree (`F-G10`). |
| No display voice | `app.css:31` system font stack only; the rounded display font "stays parked behind a C-8 approval" (`DESIGN.md:204-206`, `app.css:29`). |

#### 2.2 The root cause

Two contracts, and the austere one outranks the rich one. `docs/research-scratch/DESIGN-CORPUS.md` section `## design-2.0.md` §3 asks for a surface that is
"optimistic, tactical, game-adjacent, modern"; `DESIGN.md` §1 asks for a "sports-science or esports
analytics tool, never like a gacha wiki". Both are defensible; they are not the same product, and the
shipped surface is the second one, implemented cleanly and completely. A "wireframe" is the honest
description of a fully-realised austere analytics tool.

#### 2.3 The colour question — sampled from the client sheets and the official site

The owner's assessment is specifically about colour: the light surface "does not resemble Umamusume
Pretty Derby". Two palettes were sampled to test that claim rather than assert it — the 19 in-game sheets
in `docs/design-research/_scratch/references/sheets/` and the 11 official captures in
`.../references/web/`. Both are read-only (`AGENTS.md` §11). The values below are **quantized buckets of
dominant and saturated pixels, not a published spec**; treat them as indicative.

| Sampled from | Dominant field | Signature accents |
| --- | --- | --- |
| Client sheets (19) | near-black `#161616` (40-53% of every frame) | cream/parchment `#FFEECC`, pale lavender `#F4F4FF`, lime `#77CC11`, sky `#77CCFF`/`#55BBFF`, violet `#554499`, crimson `#BB3355`, warm tan `#BB9988` |
| Official site (11) | royal blue `#2255FF` (6-29% of saturated pixels) | hot pink `#FF77CC` call-to-action, lime `#BBDD11`, racing green `#44AA33`, violet `#8877FF`, teal `#00BBBB`, gold `#CCBB00` |

Two findings, and the first corrects a framing this proposal would otherwise repeat:

1. **The shipped palette is already client-measured — the hues line up.** `DESIGN.md:56` states both
   palettes are "measured from client frames", `DESIGN.md:120-137` is a measured pair table, and `:149-151`
   warns that six degrees of drift off the client's action green "breaks the resemblance". The sampling
   agrees: shipped action green `#7FCC09` ≈ sheet lime `#77CC11`; shipped ink brown `#6A5641` ≈ sheet tan
   `#BB9988`; shipped page `#F2F1F8` ≈ sheet `#F4F4FF`; shipped Skill-Points cyan `#009FE1`
   (`DESIGN.md:133`, "the client's cyan, exact") is the sheet sky family. So "the app does not use the
   game's colours" is **not** the finding — the app does.
2. **What is missing is the *marketing* palette and the client's *chrome distribution*.** The royal blue
   `#2255FF` and hot pink `#FF77CC` that carry the official site are **not in the token set at all**, and
   the client's own base in the sampled frames is dark (`#161616`) — the opposite of the shipped default.
   The shipped surface uses the client's *hues* but not its *product surface*: no blue chrome field, no
   pink call-to-action, no violet, and a pale default where the client frames are dark. That distribution
   gap is what the eye reads as "does not resemble the game".

The consequence for the tracks: **colour is almost entirely a Track 2 question.** `app.css:57` ("Green is
action and affordability only, never 'good'") and `DESIGN.md:53` ("Colour is semantic only") forbid a brand
blue or pink inside the contract; adding either is a `DESIGN.md` §2.1 amendment (Track 2 D) with a measured
pair per new role (G-18). Track 1 can only (a) spend the *existing* client-derived tokens more deliberately
— gold `pick` `#EFC96A`, cyan `sp` `#009FE1`, warm ink — where the client itself uses them, and (b) fix the
dark ramp (§2.1), which is the one move that brings a theme *closer* to the client base. One hue family,
violet (client `#554499` / site `#8877FF`), has no shipped token in either theme.

Honesty note: the sheets are client captures with artwork, anti-aliasing and a quantized palette; the site
is a marketing surface, not the client. Neither is a brand specification, and no official palette is
published in-repo. These are directions to audit against, not values to paste.

#### 2.4 The game client corpus — the fidelity reference above the HLF

The repo already holds a detailed, cited description of the *real* game client's UI, and it outranks the HLF
as a fidelity target: the HLF is a wireframe of a tool, while these notes describe the product the Trainer
already uses. The corpus is `docs/research-scratch/screenshot-notes/00`-`06` (7 notes over 52 reconciled
frames), `screenshot-notes-extras/` (3), and the `screenshot-registry/` evidence set. Each note carries the
same four headings — *Visual hierarchy*, *What carries state*, *Absences*, *What this screen teaches the
app* — so it reads as a UI specification, not a gameplay log.

Patterns the client uses that the shipped build lacks:

| Client pattern | Where | What Trainer Desk lacks |
| --- | --- | --- |
| Per-item detail as a modal over a dimmed backdrop | `00:40`, `04:24` | detail is a page, not a modal grammar |
| A negative-capable mark vocabulary with no legend (double-circle / circle / triangle / cross) | `00:16`, `00:97` | no shape-rank vocabulary; grades are letters |
| Stacked base/bonus gain bubbles (small upper bubble = bonus) | `01:53` | no projected-gain channel at all |
| A three-kind log grammar (named speech / unnamed narration / signed outcome) | `01:143` | the log/rail is flat |
| A risk channel that appears only when non-zero (pink `Failure 5%`) | `01:318` | no conditional alert treatment |
| Pending basket kept distinct from the displayed balance | `03:27` | selection and balance are not separated |
| A goal ladder with one "next" plus a `Goals Achieved 5/8` footer | `02:160`, `02:165` | goals are a list, not a ladder |
| A contextual hint naming the weak stat, on the decision surface | `05:32` | the advisor is a flat rail, not a hint bubble |
| Accent taken as data (per-trainee / per-state tint) | `01:86` | per-scenario accent exists; per-state tint does not |

Three of these reinforce §2.3 rather than opening a new track:

- **The client's signed-change colours match the shipped tokens.** `00:75` records outcome deltas as
  "sentences with direction words colour-coded (down blue, up orange)"; the shipped `--color-down` is blue
  and `--color-up` is orange (`DESIGN.md:129-130`). The app already reproduces the client's up/down
  semantics, which is further evidence for §2.3 finding 1.
- **The client's mood ramp is non-monotone, negatives cool.** `06:103`: "GREAT deep pink, GOOD burnt orange,
  NORMAL gold, BAD blue, AWFUL purple... the two negative tiers sit on the cool side of the wheel". That is
  the same warm/cool split the app's up/down pair uses, and it is a *semantic* ramp, not brand chrome.
- **The client confirms the app's cut features.** No win probability (`02:27`, `02:76`, `02:190`), no score
  or aggregate (`02:217`: a clean sweep renders as "five identical 'WIN' words with no aggregate"). The app's
  cuts are faithful to the client, not a reduction of it.

One tension, carried to the law audit (`docs/research-scratch/PLANS-AND-BRIEFS.md` section `## ux-laws-audit-plan-2026-10-13.md` §2.7): the client *does* carry
alert channels — a red exclamation badge on a support card (`01:113`), a pink failure badge (`01:318`), and a
pending-obligation tag pinned to an action button (`05:85`) — which sits against the app's "no warning role"
cut (`app.css:57`, `DESIGN.md:53`).

**Law attribution (corrected 2026-10-21).** Where this file names a law by number it inherits the numbering of
`docs/research-scratch/PLANS-AND-BRIEFS.md` section `## ux-laws-audit-plan-2026-10-13.md`, and that numbering is deliberately unchanged. What changed there is
attribution only, and it narrows three claims this file shares:

- The audit plan's rows **11 Continuity**, **12 Closure** and **13 Common Fate** are Gestalt principles, not
  lawsofux.com entries (the site publishes neither Closure, nor Continuity, nor Common Fate); row **14** is no
  longer called "Law of Experience" because no such law is published by lawsofux.com or uilaws.com. A sentence
  in this file that says "this is a law" of one of those four should be read as "this is a Gestalt-derived
  principle". The §2.4 tension above is unaffected: it is about the client's alert vocabulary versus the app's
  colour cut, and it does not depend on which collection an entry sits in.
- The audit plan's **row 27** ("aesthetic consistency and visual hierarchy") is a house principle, not a law;
  where this file treats typography/consistency as a cross-cutting standard, that standard is the repo's own.
- **Nothing in this file moves a token.** §2.3's finding (the palette is client-measured; the gap is the
  marketing palette and the client's chrome distribution) is unaffected by attribution, and §6's Track 2 D
  remains a `DESIGN.md` amendment requiring an owner ruling, not a law fix.

Track placement: the modal grammar, the shape vocabulary, the gain bubbles, the log grammar, the goal ladder
and the decision-surface hint are **Track 1** (craft). The conditional alert channel is **Track 2 D**
(colour/role); the per-state accent is a data-model question. The notes are notes, not frames: the registry's
own rule applies — "a note is a lead, not a verification" (`screenshot-registry/README.md:166-167`).

#### 2.5 The corpus's own interaction principles, and the web study

`DESIGN-CORPUS.md` is the folded corpus `DESIGN.md` was derived from, and it states interaction patterns
"worth carrying" that the shipped build does not carry:

- **Preview before commit** (`:4093`) — "every consequential choice shows its outcome numbers before it is
  accepted, and acceptance is a separate action".
- **Colour always paired with a word** (`:4094`) — "both the aesthetic and, conveniently, the accessible
  implementation".
- **Numbers live above the thing they change** (`:4095`) — the gain bubble floats over the stat it changes.
- **One saturated tile marks the active destination** (`:4096`) — a filled block, not an underline.
- **Grouping by phase, collapsible** (`:4097`) — the Log's phase bar, "exactly the shape a 24-turn run needs".
- **Shape carries meaning** (`:4098`) — "Circular buttons for the five disciplines, rectangles for everything
  else".

Plus §35 **Progressive Disclosure** (`:6503`, show `Speed +62 / 3 Supports / 2% Failure`, expand to the
breakdown) and §36 **Explainability** (`:6538`, every recommendation answers "why?"), and the "Additional
Design Details" (`:5614-5622`): never a single unexplained score, `N/A` + `title` for unavailable data,
progressive disclosure at 320px, **non-colour-only warnings**, and actual-vs-hypothetical balances.

These are the repo's own declared principles, not new ones, and each is a **Track 1** gap where the shipped
build does not render it: the wizard's per-stat editor shows the breakdown without a preview-and-accept step,
and the advisor is a flat rail with no "why".

One correction to §3's shadow question. The `DESIGN-CORPUS.md` §6 web study (`:4104-4116`) measured the
official properties as carrying **zero** `box-shadow` and **zero** `linear-gradient`/`radial-gradient`, with
all their depth from `filter: drop-shadow()`. That both supports the app's flat, gradient-free surface and
nuances Track 2 B: where the app does add elevation, the brand precedent is `drop-shadow`, not `box-shadow`.

### 3. The richer target already exists — reconciliation table

Every clause a "real frontend" needs is already specified in `docs/research-scratch/DESIGN-CORPUS.md` section `## design-2.0.md` and is currently losing to
`DESIGN.md`. This is the reconciliation the owner has to make:

| Expression clause | `docs/research-scratch/DESIGN-CORPUS.md` section `## design-2.0.md` (rich target) | `DESIGN.md` (shipped, wins) | Conflict? |
| --- | --- | --- | --- |
| Visual hierarchy | four levels, Critical→Metadata (`:290-358`) | none stated as levels | additive, no conflict |
| Radius | 6 / 10 / 14 / 16 / 999 (`:525-540`) | one `rounded-md` (`:243`) | **direct** |
| Shadows | sparing, for dialogs/panels/recommendations/menus (`:543-560`) | "Shadows: none" (`:244-245`) | **direct** |
| Type hierarchy | Display / Heading / Body / Numeric (`:443-494`) | scale is **Proposed**, display font parked (`:201-206`) | **direct on display** |
| Cards | the fundamental grouping mechanism (`:586-613`) | cards exist but with no elevation | partial |
| Personality | optimistic, game-adjacent, modern (`:266-288`) | serious analytics tool, no marketing (`:58-60`) | **tonal** |
| Data visualization / density / disclosure | `:1398`, `:1113`, `:1143` | not addressed | additive |

Three clauses conflict directly. Under `AGENTS.md` §2 the shipped surface is correct to ignore them, and
an agent may not resolve them silently — this is escalation territory (a design-contract change).

### 4. The HLF reference set — the high-fidelity target, pattern by pattern

`docs/proposals/HLF_reference/` holds 25 full-page captures of a v0 build (the filenames read
`ui-improvement-suggestions-v0-build`, page badge `v2.0.0`). It is the concrete, screen-by-screen answer to
"what the frontend could be", and it is the most useful artifact in this directory for the fidelity
question because it renders the target instead of describing it. It is also, per the owner, still a
**high-fidelity wireframe**: its data is placeholder, some copy is invented, and it is not bound by the
token contract. The game client corpus (§2.4) is the higher-fidelity reference beneath it: this section
treats the HLF as the tool-shaped target, §2.4 as the product-shaped one.

#### 4.1 The pattern inventory

| Pattern | Where the HLF shows it | What the shipped build lacks |
| --- | --- | --- |
| Page header: lime eyebrow + large title + one-line subtitle | every screen (`OVERVIEW`/`LIBRARY`/`COMPARE`/`REFERENCE`/`DATA QUALITY`/`PREFERENCES`) | the shell `#title` is `text-base`; no eyebrow/subtitle convention |
| Back breadcrumb (`← Back to …`) | every detail screen | present on some, not as a convention |
| KPI stat cards (label, big number, sub-label) | Dashboard | none |
| Status pills (In progress, Complete, Needs review, Needs legacy, Active, Completed, Observed, Mandatory) | Careers, Cockpit, Review, Legacy, Veterans | status exists but flat |
| Provenance badges (Confirmed, Unknown, Calculated, Partial) | Events, Shop Items, Sparks, Cockpit | `ProvenanceBadge` exists but is under-used |
| Category/type chips (SSR/SR/R, Power/Speed, Stat/Aptitude/Skill/Competition/Scenario, Consumable/Currency) | Support cards, Sparks, Shop Items | partial |
| Effect chips (`Friendship Bonus 15%`, `Speed Bonus 1`, …) | Support card detail | none |
| Labeled sub-cells (key/value mini-boxes) | Cockpit identity + stats, Compare, Support/Skill detail | none — the signature HLF component |
| Contained bordered cards on a raised surface | everywhere | cards have no elevation and dissolve in dark |
| Progress bars (lime fill) | Careers | none |
| Row-count line (`400 catalogue rows`, `559 of 559`) | Support cards, Skills, Races, Events, Sparks | partial |
| Tables (Races; Compare as property/value; Sparks odds) | Races, Compare, Sparks | tables exist |
| Search + filter rows in a bordered container | Careers, Support cards, Veterans, Skills, Legacy, Trainee | partial, unstyled controls |
| Tabs | Settings (General/Recommendation/Data/Game version), Cockpit (Cockpit/Dashboard) | none |
| Anchor nav | Cockpit (Position, Run identity, Stats and state, Actions, Scenario, Races, Advisor) | none |
| Pagination | Support cards, Skills, Review, Races | exists |
| Artwork thumbnails | Support cards list + detail header | artwork system built (`ADR-0021`), unused here |
| Wizard option cards (status line + one Select button) | Scenario, Target, Legacy, Deck | the steps are heavy editors instead |
| Advisor structure: Objectives / Current priority / Recommended action / Recorded action | Cockpit | the advisor is a flat rail |
| Scenario panels grid with honesty notes | Cockpit | scenario region is sparser |
| Raw engine data shown as-is with a note | Skill detail (`Condition`, `Base time`, effect codes) | partial |

#### 4.2 What the HLF proves is reachable inside the current contract

The HLF's "product" feel comes mostly from **borders + a real surface step + a type scale + density + a
badge/sub-cell vocabulary + artwork** — **not from shadows**. Its cards are bordered, not shadowed. So
Track 1 (plus a radius decision) gets most of the way, and Track 2's **shadow clause is optional**: the HLF
weakens the case for amending "Shadows: none" (`DESIGN.md:244-245`). What the HLF does require beyond
Track 1:

- a **radius scale** (its cards are visibly rounder than `rounded-md`) → Track 2 A;
- a **badge colour vocabulary** (lime `In progress` / `Confirmed` / `Mandatory`, amber `Unknown` / `Needs
  decision`) → **conflicts with `app.css:57` "green is action and affordability only, never 'good'"** →
  Track 2 D;
- a **real type scale** (Display / Heading / Body / Numeric) → Track 1-2, plus Track 2 C for the display
  face.

#### 4.3 What the HLF changes beyond styling (scope / semantics — needs a decision)

- **Wizard steps become option-card presets.** Target → `Speed focus / Balanced growth / Race
  preparation`; Legacy → `No legacy recorded / Use a saved selection`; Deck → `Current support deck /
  Start with empty deck`. This **removes the per-stat target editor, the two-parent ancestry editor and
  the six-slot deck editor from the wizard**. That is a domain/scope change, not a skin — those editors
  must move elsewhere or be cut, and it needs a `SCREEN_SPEC.md` + `PRD` decision (escalation 2).
- **Preflight drops the duplicate "Edit step" buttons** and gives each warning an inline reason
  (`Scenario is not chosen. Nothing is recorded in this setup.`). This matches the audits' Occam's Razor
  finding and is adoptable inside Track 1.
- **Cockpit gains a tab toggle ("Cockpit | Dashboard") and an anchor nav.** Per cockpit audit §0.1,
  `SCREEN_SPEC.md:1841` forbids hiding an element at a breakpoint and a tab strip that shows one section
  at a time; an **anchor** nav is allowed. So the anchor nav is compatible; the "Cockpit | Dashboard"
  toggle must be checked against that ruling before it ships.
- **Compare becomes a property/value table** instead of a wide matrix — this is *better* for the
  responsive region contract than the current compare tables, and worth adopting.
- **A richer status vocabulary** (`Needs legacy`, `Needs review`, `In progress`, `Partial record`,
  `Observed`) — a copy/state expansion that ties to the absence/state work in the audits.
- **The stepper still says "done" for un-entered steps.** On the Career-contract screen all six steps read
  "done" while every warning says "Nothing is recorded in this setup". The HLF **reproduces the P0** the
  live capture found (`docs/research-scratch/AUDIT-AND-VERIFICATION.md` §"setup-wizard-ui-audit-2026-10-12.md" §2.2); adopting the HLF does not fix the
  stepper semantics — the three-state decision still stands.

#### 4.4 Honesty patterns worth keeping

- "Each figure is the effect's highest stated anchor, not an interpolation."
- "The expressions and effect codes above are the source's own engine data, not client labels."
- "Reference note: this scenario intentionally exposes only the widgets and panels documented for its
  ruleset."
- Raw engine data (skill `Condition`, `Base time`, effect codes) shown as-is with a note.

These are exactly the provenance/absence discipline the audits ask for, rendered well, and they are the
part of the HLF that is safest to adopt.

#### 4.5 Where the HLF lands each track

| HLF pattern | Lands in |
| --- | --- |
| Header, sub-cells, badges, chips, progress, counts, tabs, anchor nav, artwork, tables, filters | **Track 1** (craft, no ruling) |
| Radius scale | **Track 2 A** |
| Badge colours (lime/amber status) | **Track 2 D** |
| Display type tier | Track 1-2, full version **Track 2 C** |
| Card borders + surface step | **Track 1** (no shadow amendment needed) |
| Wizard option-card simplification | **scope decision** (escalation 2), not styling |
| Cockpit tab toggle | check against `SCREEN_SPEC.md:1841` |
| Compare as property/value table | Track 1 (and improves the responsive contract) |
| Stepper "done" wording | unchanged — the P0 remains |

#### 4.6 The caveat, restated

The HLF is itself a wireframe: it uses placeholder values (a completed run showing `Turn 1`), invented copy
("Good morning, Trainer"), and colours that may not clear the token contract. It is a **direction to audit
against**, not a build to copy. Its value is that it answers "what could this look like" with pixels, so
the two tracks can be argued concretely rather than abstractly.

### 5. Track 1 — inside the current contract (no ruling needed)

These are craft layers that **both** documents already ask for and neither has shipped. They are the
cheapest real gain and they need no contract change. Sequenced by leverage:

| # | Move | Where | Acceptance |
| --- | --- | --- | --- |
| 1 | Apply the ratified shell measure | `AppLayout.vue:295` → `mx-auto w-full max-w-5xl` (F-A2, X-8) | no page runs to the shell's full width; the dead right column is gone; `dashboard.spec.ts:195-224`, `veterans.spec.ts:40-43` move with it |
| 2 | Land the type scale (one shell decision, then pages) | shell `#title` → `text-2xl`; section headers → `text-lg font-semibold`; promote the `text-xs uppercase` eyebrows (X-2, F-A3; cockpit audit §3.1) | one `h1` per page at `text-2xl`; headers at `text-lg`; the 75-vs-30 spread closed |
| 3 | Give the dark ramp real separation | dark override `panel`/`page` step, or render cards on `raised` (`app.css:219-231`); the rule edge is the *sanctioned* mechanism (`DESIGN.md:244-245`), it is simply too weak in dark | a measured non-text step ≥ 3:1 (WCAG 1.4.11) between a card and its page; axe clean |
| 4 | One form-control treatment | a shared input/select/checkbox style; the `caret-color` rule already exists (`app.css:718`) | every control ≥ 44px, one border and radius, consistent focus ring |
| 5 | Densify where the data already exists | Scenario, Trainee, Legacy, Deck have sparse layouts with content available; tighten to the 4/8/12/16/24 scale (`DESIGN.md:238-240`) | measured reduction in vertical dead space; no clipping at 768/1280 |
| 6 | Spend the motif budget, add the artwork | enamel once per screen (X-3, F-F3); render `ArtworkSlot` on the catalog/trainee/deck surfaces (`ADR-0021`) | enamel count = one primary per screen; four artwork slots render |
| 7 | Finish the empty / loading / error states | C-7, `ADR-0007`; the audits found several silent states (X-4) | every data view renders its three states |

Note the ordering constraint the audits already state: **do not restyle per-page headings before the shell
`#title` decision** (`remaining-screens` §5.8). Move 2 is one shell decision first, then the pages.

### 6. Track 2 — needs a `DESIGN.md` amendment (owner ruling)

Each of these reverses or extends a clause in the shipped contract. None is an agent's call.

| # | Amendment | Reverses / extends | Path |
| --- | --- | --- | --- |
| A | Radius scale (6/10/14/16/999) | `DESIGN.md:243` "`rounded-md` consistently" | owner ruling → `DESIGN.md` §2.3 rewrite |
| B | Sparing elevation for overlays only (dialogs, floating panels, the recommendation card, menus) | `DESIGN.md:244-245` "Shadows: none"; `app.css:20-21`; brand precedent is `drop-shadow`, not `box-shadow` (§2.5) | owner ruling → new `DESIGN.md` elevation table + a shadow token |
| C | A Display type tier (self-hosted rounded font) | `DESIGN.md:204-206`, parked behind **C-8** | C-8 dependency approval **and** owner ruling; offline constraint (`DESIGN.md:675-678`) still binds |
| D | A wider semantic colour vocabulary (brand blue chrome, pink call-to-action, violet; §2.3) | `DESIGN.md:53-54`, `app.css:57` (green = action only) | owner ruling; every new role needs a measured pair (G-18) |
| E | A motion budget above 1 for state transitions | `DESIGN.md:694-696`, MOTION 1 | owner ruling → raise the dial; respect the reduced-motion block |
| F | Adopt the four-level visual hierarchy | additive (no shipped equivalent) | owner ruling → `DESIGN.md` §2 gains a hierarchy table |

Track 2 is where "what the frontend could be" actually lives. Track 1 alone will make it read as a
*well-crafted* austere tool, not a product with a voice.

### 7. What each move touches

| Move | Audits / rulings it moves with | Guarded |
| --- | --- | --- |
| T1-1 measure | `remaining-screens` §2.8 / F-A2; `DESIGN.md:210-221` | `dashboard.spec.ts`, `veterans.spec.ts` |
| T1-2 type scale | `remaining-screens` §2.2 / X-2; cockpit audit §3.1 | `dashboard.spec.ts:87-92,130`, the accessible-name assertions in `database.spec.ts` |
| T1-3 dark ramp | `DESIGN.md:244-245`; G-18 pair list (`DesignTokensTest.php`) | the G-18 browser gate; propose pair rows, do not move a token |
| T1-4 form controls | `remaining-screens` §2.5 / X-5 | `tests/utils/tap-targets.ts`, `accessibility.spec.ts` |
| T1-6 enamel / artwork | `remaining-screens` §2.3 / X-3; `ADR-0021` | four 44px sweeps; `ArtworkMirrorTest` |
| T2-A..F | a new owner ruling per clause; `DESIGN.md` §2 amendment | the G-18 gate, `DesignTokensTest.php`, `TokenPairHygieneTest.php` |

### 8. What this proposal must not do

Inherited from `docs/research-scratch/AUDIT-AND-VERIFICATION.md` §"remaining-screens-ui-audit-2026-10-13.md" §5 and `docs/research-scratch/PLANS-AND-BRIEFS.md` section `## ux-laws-audit-plan-2026-10-13.md` §9,
plus three this document adds:

1. Do not move a colour token to answer a contrast question; propose pair rows and let the gate decide.
2. Do not add a cold-load spinner, skeleton, or `aria-busy` on page load (`ADR-0007`).
3. Do not add a score, percentage, confidence, grade, or recommendation to any surface.
4. Do not restyle per-page before the shell `#title` decision.
5. Do not reflow the race calendar / turn log / compare tables into stacked cards.
6. **Do not implement a Track 2 clause (radius, shadow, font, colour, motion) before the `DESIGN.md`
   amendment is signed.** That is the exact drift the precedence chain exists to prevent.
7. **Do not treat `docs/research-scratch/DESIGN-CORPUS.md` section `## design-2.0.md` as binding.** It is a reference target (`AGENTS.md` §2); until amended,
   `DESIGN.md` wins on every conflict.
8. **Do not let "make it prettier" reintroduce slop.** The audits confirmed zero gradients-as-identity,
   zero glass, zero glow (`remaining-screens` §1.3); the richer target is hierarchy and material, not
   decoration.
9. **Do not copy the HLF's placeholder data or invented copy.** Its completed-run-shows-`Turn 1` state and
   lines like "Good morning, Trainer" are mock content; the copy rules (`AGENTS.md` §5, the lore gate,
   the client vocabulary) still bind.
10. **Do not adopt the HLF's wizard option-card simplification as a restyle.** It removes the per-stat
    target, ancestry and deck editors from setup; that is a scope change and needs a `SCREEN_SPEC.md` +
    `PRD` decision before any component moves.
11. **Do not adopt the HLF's badge colours before Track 2 D is ruled.** Lime for `In progress` / `Confirmed`
    / `Mandatory` breaks `app.css:57` today.

### 9. Questions for the owner

1. **Which product is it?** `DESIGN.md`'s "serious analytics tool, never a gacha wiki" or
   `docs/research-scratch/DESIGN-CORPUS.md` section `## design-2.0.md`'s "optimistic, game-adjacent, modern"? Track 2 only makes sense under the second, and
   the answer changes every downstream clause.
2. **Radius and elevation (Track 2 A/B).** Adopt the 2.0 scale and sparing shadows, or hold the flat
   contract and rely on Track 1's dark-ramp fix to give cards presence without elevation?
3. **Display type (Track 2 C).** Is the C-8 dependency approval worth a self-hosted display font for the
   Display tier, given the offline constraint (`DESIGN.md:675-678`), or does the Display tier get its
   emphasis from weight and size on the system stack?
4. **Sequencing.** Land Track 1 as one slice (it is the visible, no-ruling gain), or wait and do Track 1 +
   the approved Track 2 clauses together so the shell is only touched once?
5. **Where does the amendment live?** A `DESIGN.md` §2 rewrite with new owner-ruling lines, or a new ADR
   that `DESIGN.md` cites — given `ADR-0006` makes `DESIGN.md` the single source of truth for the visual
   contract.
6. **The HLF wizard simplification (§4.3).** Do the per-stat target, two-parent ancestry and six-slot deck
   editors move to the run screens, or are they cut? The HLF reduces all three steps to a preset choice,
   which is the single largest change in the reference set.
7. **The HLF badge colours (§4.2).** Adopt the lime/amber status vocabulary as a new semantic role
   (Track 2 D), or map status to the existing tokens so no new pair is needed?
8. **What is the target of record?** Does the HLF become the concrete target (with `docs/research-scratch/DESIGN-CORPUS.md` section `## design-2.0.md` updated
   to cite it), or does `docs/research-scratch/DESIGN-CORPUS.md` section `## design-2.0.md` stay the target and the HLF remain illustration?
9. **Colour (§2.3).** Does the target adopt the official site's royal-blue chrome and pink call-to-action
   (Track 2 D, with a measured pair per role), or hold the client-measured, green-is-action palette and
   accept that the surface will resemble the *client* but not the *marketing site*? Relatedly: the shipped
   default theme is light (owner ruling 2026-09-27, `app.css:35`) while the client frames sampled here are
   dark — does light stay the default, or does the fidelity goal pull the default back to the client base?

### 10. Verification plan

Per `AGENTS.md` §9. Nothing below was run for this proposal.

| Approved change | Level 1 while working | Level 2 at slice end | Level 3 before hand-off |
| --- | --- | --- | --- |
| Track 1 (any move) | the touched screen's spec; `tests/utils/tap-targets.ts` where height is involved | the cluster's specs + `tests/browser/accessibility.spec.ts` | the full suite (the shell is felt by every screen); `npm run typecheck`, `pint --test --format agent <paths>`, PHPStan |
| Track 1-3 (dark ramp) | `php artisan test --compact --filter=DesignTokensTest` | the browser half of the same file (G-18, both themes) | the pair list and the `DESIGN.md:145-148` count move together |
| A Track 2 clause | not testable until the `DESIGN.md` amendment lands | — | re-baseline the audits against the amended contract before any component change |

Pre-flight before any broad sweep: time one trivial SQLite read (`KI-74`); a contended box voids the sweep
before it starts.

### 11. Hand-off notes

- **Deliverables produced:** this file. No component, token, copy string, gate, spec, or test was changed.
- **Facts this pass relies on:** the dial and the flat rules are `DESIGN.md:24-27, 58-60, 243-245, 696`; the
  richer target is `docs/proposals/design-2.0.md:290-358, 443-494, 525-560, 586-613`; the token values and
  the dark-ramp collapse are `app.css:20-21, 31, 219-231`; the type-scale and measure gaps are
  `docs/research-scratch/AUDIT-AND-VERIFICATION.md` §"remaining-screens-ui-audit-2026-10-13.md" §2.2 and §2.8; the live evidence is the six dark wizard
  screenshots and the 81 captures in `.scratch-uma/ux-audit/`; the concrete target is the 25 captures in
  `docs/proposals/HLF_reference/` (read in full, §4).
- **Colour evidence (§2.3):** sampled programmatically (`.scratch-uma/ux-audit/palette-extract.ps1`,
  `palette-accents.ps1`) from the 19 client sheets and the 11 official-site captures, because attaching the
  ~2 MB frames directly exceeded the model's context limit. The values are quantized buckets, not a
  published brand specification.
- **Client-UI corpus (§2.4):** `docs/research-scratch/screenshot-notes/00`-`06` (7 notes, 52 reconciled
  frames), `screenshot-notes-extras/` (3) and `screenshot-registry/` describe the real client's UI with
  per-screen *Visual hierarchy / What carries state / Absences / What this screen teaches the app* notes;
  folded in as the fidelity reference above the HLF. All citations verified against the source files.
- **Corpus principles (§2.5):** `DESIGN-CORPUS.md` §5 "Interaction and state patterns worth carrying"
  (`:4091-4098`), §35 Progressive Disclosure (`:6503`), §36 Explainability (`:6538`) and the "Additional
  Design Details" block (`:5614-5622`) declare the interaction principles the app should carry; the §6 web
  study (`:4104-4116`) shows the official properties are gradient-free with `drop-shadow` depth.
- **Fact this pass corrected in its own framing:** the shipped palette is not un-game-like — it is
  client-measured (`DESIGN.md:56, 120-137, 149-151`, including "the client's cyan, exact"). The gap the
  owner sees is the *marketing* palette (royal blue, hot pink) and the client's chrome distribution, not
  the hues the app already uses.
- **Not delivered, stated plainly:** no prototype, no measurement, no suite run, no lore gate, no fix. The
  HLF was read as reference; nothing in it was implemented or copied.
- **Fact this pass corrected in its own framing:** "the frontend could be X" is not an open design
  question — a richer target already exists in `docs/research-scratch/DESIGN-CORPUS.md` section `## design-2.0.md`, and the HLF reference set now renders it;
  the real question is that it is outranked by `DESIGN.md`, so the work is a contract decision plus the
  craft both documents already ask for.

---

## ux-laws-audit-plan-2026-10-13.md

> **Consolidated 2026-10-21 (Round 18, owner-ordered).** Source: `docs/proposals/ux-laws-audit-plan-2026-10-13.md`, reproduced verbatim below with its headings demoted one level. The original was deleted from `docs/proposals/` after line-by-line verification (0 missing). Cite this anchor, not the deleted path. <!-- census: quoted-original -->

## Laws of UX audit — plan (canonical checklist, coverage matrix, instruments, output contract)

> **Reference only. Not authorization.** Nothing in this file is a decision. No component, token, copy
> string, gate or spec line was changed to produce it. Every section names the files an approval would
> touch and touches none of them.
>
> Recorded 2026-10-13. This is a **plan**, not an audit: it defines the method, the coverage matrix, the
> instruments and the output contract for auditing Trainer Desk against the Laws of UX. It is the fourth
> file in the proposal-side audit set and it **extends** the existing three rather than restating them:
> `docs/research-scratch/AUDIT-AND-VERIFICATION.md` §"cockpit-ui-audit-2026-10-12.md" owns `SCR-CAR-011`, `docs/research-scratch/AUDIT-AND-VERIFICATION.md` §"setup-wizard-ui-audit-2026-10-12.md"
> owns the six `/career/setup/*` steps, and `docs/research-scratch/AUDIT-AND-VERIFICATION.md` §"remaining-screens-ui-audit-2026-10-13.md" owns
> the other 40 pages. Their findings are the audit's **input**; this plan says how to evaluate them (and
> the screens they did not reach) against the laws, and what instrument produces each verdict.
>
> **What this plan deliberately does not do:** it does not re-run the audits, does not assign new finding
> IDs, and does not re-open any of the eleven "what a later pass must not do" items in
> `docs/research-scratch/AUDIT-AND-VERIFICATION.md` §"remaining-screens-ui-audit-2026-10-13.md" §5. It inherits them (restated in §9).

### 0. What governs this plan

Per `AGENTS.md` §2 the precedence chain that settles every dispute below is: quality bar → gate registry
→ accepted ADRs → `PRD.md` → `ARCHITECTURE.md` → `SCREEN_SPEC.md` (screen behaviour) → `DESIGN.md` (visual
system) → `docs/research-scratch/DESIGN-CORPUS.md` section `## design-2.0.md` (reference target). The Laws of UX are a **lens over that chain,
never a replacement for it**: where a law appears to ask for something an ADR or a `SCREEN_SPEC` ruling
forbids (a score, a win-chance, a warning role, a scenario name in a component), the law loses and the
conflict is recorded, not resolved silently. Five facts from the existing files frame the whole plan:

1. **The app has its own UX spine already.** `docs/research-scratch/DESIGN-CORPUS.md` section `## design-2.0.md` §2 states it as
   `State → Options → Recommendation → Explanation → Decision`. The laws are how we test whether each
   screen honours that spine; they are not a second spine.
2. **The visual contract is already measured in places.** `DESIGN.md` §2-§8 (tokens, type scale at
   `:188-190`, ratified widths at `:197-208`, the wide-table region rule at `:209-224`, motifs at
   `:231-248`, absence/provenance at `:567-624`, motion at `:679-726`, WCAG 2.2 AA floor at `:851-872`).
   A law that is really a contract question is settled by the contract, not by taste.
3. **The three audits already produced a score card.** `docs/research-scratch/AUDIT-AND-VERIFICATION.md` §"remaining-screens-ui-audit-2026-10-13.md" §1.1
   scores eight clusters on impeccable's five dimensions (Accessibility, Performance, Theming,
   Responsive, Implementation Integrity), 104/160; §1.2 counts 1 P0, 26 P1, 31 P2, 29 P3. This plan adds
   a **laws** axis on top of that score card; it does not rescore the clusters.
4. **The bar cannot see several classes the laws travel on.** `docs/research-scratch/AUDIT-AND-VERIFICATION.md` §"remaining-screens-ui-audit-2026-10-13.md"
   §4 is the list: `npm run typecheck` reads no `.vue` file (§4.1), the copy-hygiene guard sees no
   server-composed string (§4.2), the G-18 contrast contract pins 19 pairs with one `ink-muted` pair
   (§4.3), registers lag the code (§4.4), an unvalidated config→badge index exists (§4.5), and the
   deterministic slop detector returned `[]` (§4.6). The plan's instruments (§4) are chosen to reach the
   channels those gaps leave dark.
5. **The 2026-10-13 live capture already corroborates two existing findings.** The session-private
   harness (§4.2) rendered the setup wizard with every later step marked `done` while nothing was entered
   (`SetupLayout.vue:45-46`; corroborates `docs/research-scratch/AUDIT-AND-VERIFICATION.md` §"setup-wizard-ui-audit-2026-10-12.md" §2.2), and rendered
   Preflight's five warnings beside the same all-`done` bar. Those are the first pixel-level confirmations
   in this corpus; the plan makes that harness the default measurement instrument, and the pass's own laws
   report is reconciled in §2.9.

### 1. The canonical checklist this audit applies (verbatim)

This is the checklist the audit is run against, reproduced exactly as supplied. It is the audit's spine;
§3 maps each row to a repo-grounded check and an instrument.

**Attribution correction (2026-10-21).** The supplied checklist attributes all 27 rows to "Laws of UX
collection: https://lawsofux.com/", and that attribution is wrong for six of them. Checked against the live
index on 2026-10-21: lawsofux.com publishes **28** entries (Aesthetic-Usability Effect, Choice Overload,
Chunking, Cognitive Bias, Cognitive Load, Doherty Threshold, Fitts's Law, Flow, Goal-Gradient Effect, Hick's
Law, Jakob's Law, Law of Common Region, Law of Proximity, Law of Prägnanz, Law of Similarity, Law of Uniform
Connectedness, Mental Model, Miller's Law, Occam's Razor, Paradox of the Active User, Pareto Principle,
Parkinson's Law, Peak-End Rule, Postel's Law, Selective Attention, Serial Position Effect, Tesler's Law, Von
Restorff Effect, plus Zeigarnik Effect, which the index truncates but which has its own page at
`lawsofux.com/zeigarnik-effect/`). Of the checklist's 27 rows:

- **Six are not Laws of UX at all.** Rows **11 Continuity**, **12 Closure** and **13 Common Fate** are
  Gestalt grouping principles, not entries in the lawsofux.com collection. They are lawful design constraints
  and the repo checks behind them stay, but they belong to the Gestalt/visual family that
  https://www.uilaws.com/ actually publishes (its index lists Closure and Continuity explicitly alongside
  Symmetry, Rule of Thirds, White Space, Color Theory, Typography Hierarchy, Consistency, Proximity, Contrast,
  Fitts's Law, Hick's Law, Jakob's Law). Row **14 "Law of Experience"** is not published on either site and has
  no source; it is renamed below to the sourced principle it was standing in for.
- **Row 27 "Aesthetic consistency and visual hierarchy" is not a law**, and the checklist itself says so ("Although not
  a single law"). It is a cross-cutting house principle and is kept as such, clearly labelled.
- **Eleven lawsofux.com entries are absent from the 27 as first-class rows**: Chunking, Cognitive Bias, Mental
  Model, Paradox of the Active User, Parkinson's Law, Postel's Law, Selective Attention, and — named in §2.1
  as "adjacent principles" — the last four of those. They are folded in below as **§1.1b**, with numbering that
  starts at 28 so that **rows 1-27 keep the numbers every other section of this plan and
  `docs/research-scratch/PLANS-AND-BRIEFS.md` section `## frontend-fidelity-2026-10-13.md` cite**. No renumbering is performed; a reference to "(14)" still resolves
  to the same subject it resolved to before this correction.

This note is the before/after record. Every downstream "law N" citation in this file is left numerically
stable on purpose, and §2.1, §2.4, §2.8 and §12 carry their own dated corrections where the attribution
change altered their reasoning.

#### 1.1 The laws and principles

````text
Below is a practical checklist based on the Laws of UX collection: https://lawsofux.com/ ## UX laws and principles 1. **Aesthetic–Usability Effect** — Attractive interfaces are perceived as easier to use. Use intentional visual hierarchy, spacing, typography, contrast, and polished states; do not let aesthetics hide usability problems. 2. **Jakob's Law** — Users expect interfaces to work like familiar products. Use recognizable navigation, buttons, forms, filters, tables, dialogs, and wizard conventions. 3. **Fitts's Law** — The time to reach a target depends on its size and distance. Make primary actions large enough, keep related controls close, and provide generous touch targets. 4. **Hick's Law** — More choices increase decision time. Reduce simultaneous options, group them, use progressive disclosure, and provide sensible defaults. 5. **Miller's Law** — Working memory is limited. Chunk information into meaningful groups; do not treat "7±2" as a literal UI quota. 6. **Law of Proximity** — Nearby elements are perceived as related. Keep labels close to fields, actions close to the content they affect, and separate unrelated sections with spacing. 7. **Law of Similarity** — Similar-looking elements are perceived as having similar roles. Keep equivalent controls visually consistent and avoid making different actions look identical. 8. **Law of Common Region** — Elements inside the same boundary are perceived as a group. Use cards, panels, fieldsets, and table sections purposefully; avoid excessive containers. 9. **Law of Uniform Connectedness** — Visually connected elements are perceived as related. Use shared borders, background, connector lines, or grouping patterns for related steps and relationships. 10. **Law of Prägnanz** — People interpret ambiguous visuals in the simplest possible way. Prefer clear shapes, obvious hierarchy, plain labels, and low-noise layouts. 11. **Law of Continuity** (Gestalt; published by uilaws.com, *not* a lawsofux.com entry) — Users follow smooth visual paths. Align content, preserve reading order, and make steppers, timelines, tables, and lists scan naturally. 12. **Law of Closure** (Gestalt; published by uilaws.com, *not* a lawsofux.com entry) — People mentally complete incomplete shapes or groups. Use carefully designed partial indicators, but never make critical information depend on guesswork. 13. **Law of Common Fate** (Gestalt; not published by either collection) — Elements moving or changing together are perceived as related. Use coordinated transitions and state changes; avoid unrelated motion. 14. **Prior Experience** (sourced general principle; no "Law of Experience" is published by either collection) — Previous experience shapes perception. Reuse established patterns and domain conventions, especially for navigation, status, editing, filtering, and confirmation. 15. **Von Restorff Effect** — The distinctive item is remembered. Reserve contrast, color, size, and emphasis for the most important action, warning, or status. 16. **Serial Position Effect** — Users tend to remember the first and last items best. Put high-priority navigation and actions at predictable beginning or ending positions. 17. **Peak–End Rule** — People judge an experience largely by its most intense moment and its ending. Make errors, completion, saving, onboarding, and final confirmation especially clear and reassuring. 18. **Goal-Gradient Effect** — Motivation increases as users approach a goal. Show meaningful progress, remaining work, completion counts, and a clear next step. 19. **Zeigarnik Effect** — Unfinished tasks remain mentally active. Make incomplete work visible, resumable, and clearly distinguishable from completed work. 20. **Tesler's Law** — Every system has unavoidable complexity. The product should absorb complexity wherever possible instead of making users understand internal data models. 21. **Occam's Razor** — Prefer the simplest explanation and interaction that solves the problem. Remove duplicate copy, empty states, redundant controls, and unnecessary steps. 22. **Pareto Principle** — A small portion of features often provides most value. Prioritize the core workflow and keep advanced or low-frequency features secondary. 23. **Doherty Threshold** — Productivity improves when the system responds quickly. Provide immediate feedback, loading states, optimistic updates where safe, and avoid unexplained pauses. 24. **Cognitive Load** — Every interface consumes mental effort. Reduce memory demands, use recognition over recall, expose only relevant information, and write concise instructions. 25. **Choice Overload / Paradox of Choice** — Too many options reduce confidence and completion. Use defaults, recommendations, filtering, categories, and progressive disclosure. 26. **Flow** — Users perform best when challenges, feedback, and goals are balanced. Keep users oriented, avoid unnecessary interruptions, and make the next action obvious. 27. **Aesthetic consistency and visual hierarchy** (house principle, *not* a published law) — Although not a single law, these are essential cross-cutting principles: consistent tokens, typography, spacing, color semantics, focus states, and component behavior.
````

#### 1.1b Laws of UX entries absent from the 27 (added 2026-10-21)

lawsofux.com publishes 28 entries; the supplied list of 27 omits six of them and invents four. The list below
closes the gap with numbering that continues from 27, so nothing above this section renumbers. Each row is
judged by the same §3 matrix format. Instruments are proposed, not adopted: no check here has been run.

| # | Law (source) | Screens | What to check | Instrument |
| --- | --- | --- | --- | --- |
| 28 | Chunking (lawsofux.com) | C, E, F | long option lists, stat blocks and modifier sets are grouped meaningfully, not as one flat run | Judge |
| 29 | Mental Model (lawsofux.com) | all | the app's nouns match what a trainer thinks a race, a run and a stat are; no internal-model leak (`DESIGN-CORPUS.md` §36) | Judge |
| 30 | Paradox of the Active User (lawsofux.com) | Wizard, onboarding | every screen works without reading docs; the first run needs no manual | Judge |
| 31 | Parkinson's Law (lawsofux.com) | F, Wizard | no screen invents busywork or fills an empty layout with controls | Judge |
| 32 | Postel's Law (lawsofux.com) | B, D1, D2, Wizard | liberal input acceptance (paste, partial) with conservative output (canonical enum labels from `lang/en/uma.php`) | Judge + source |
| 33 | Selective Attention (lawsofux.com) | Cockpit, Result | the goal-relevant datum wins attention; no competing accent on the same screen | Judge + Measure (accent count) |

Two further caveats on the source list:

- **Working Memory is not a lawsofux.com entry** and stays an adjacent principle (bound to Miller's, row 5).
- **uilaws.com's 13 entries** (Symmetry, Rule of Thirds, White Space, Color Theory, Typography Hierarchy,
  Consistency, Proximity, Contrast, Closure, Continuity, Fitts's Law, Hick's Law, Jakob's Law) are a visual-style
  collection, not part of the Laws of UX spine. Four of them overlap rows already here. The remaining nine are
  layout/visual standards this repo already owns in `DESIGN.md`, so they are **not** added as audit rows; the
  audit borrows only Closure and Continuity (rows 11-12), which describe scanned-reading behaviour the repo
  actually tests.



````text
Check each page for: - **Purpose:** Is the page goal obvious within seconds? - **Hierarchy:** Can users identify the page title, primary action, secondary actions, status, and next step? - **Navigation:** Is the current location clear? Can users move backward, forward, or exit without losing work? - **State:** Are loading, empty, saved, unsaved, error, disabled, selected, and completed states distinguishable? - **Content:** Is information prioritized, concise, scannable, and free of repeated or empty-value noise? - **Interaction:** Are controls discoverable, correctly labeled, reachable, keyboard accessible, and close to the content they affect? - **Feedback:** Does every important action produce timely confirmation or an actionable error? - **Accessibility:** Check contrast, focus visibility, target size, labels, semantic structure, keyboard flow, screen-reader names, error association, and reduced motion. - **Responsive behavior:** Check desktop, tablet, mobile, zoom, long labels, localization, and narrow widths; ensure no clipping, overlap, or horizontal scrolling unless intentional. - **Consistency:** Compare the page with adjacent pages for navigation, button hierarchy, terminology, spacing, forms, status labels, and component behavior.
````

#### 1.3 The states to test

````text
Test these states where applicable: - first visit - populated state - empty state - loading state - error state - validation failure - disabled controls - selected and completed states - saved versus unsaved changes - navigation backward and forward - refresh or interrupted flow - keyboard-only navigation - focus visibility - screen-reader semantics - narrow mobile viewport - long content and long labels - zoom or text scaling
````

#### 1.4 Special attention

````text
Pay special attention to: - misleading completion indicators - duplicated or low-value information - unclear primary versus secondary actions - controls that are too small or too far from their target content - excessive choices presented at once - unexplained N/A, empty, or technical values - repeated explanatory copy - inconsistent terminology or component behavior - pages that make users manage internal system complexity - missing feedback after save, submit, filter, select, or navigation actions - warnings that do not look meaningfully different from ordinary content - forms without clear labels, units, constraints, defaults, or errors - content that is visually clipped, crowded, or difficult to scan
````

#### 1.5 The output structure

````text
Output the audit in this exact structure: # UX Audit Summary - Overall assessment - Strongest patterns - Highest-risk issues - Laws most frequently violated - Accessibility and responsive risk summary # Route-by-route findings For each route: ## [Route name] - User goal: - Primary task: - Overall rating: Strong / Mixed / Weak - Findings table with columns: Law or principle | Status | Evidence | User impact | Recommendation | Severity | Issue type # Cross-page consistency findings Compare navigation, page headers, breadcrumbs or steppers, buttons, forms, cards, tables, filters, status indicators, empty states, errors, save feedback, and responsive behavior. # Prioritized remediation plan Group recommendations into: - P0: blocks task completion, causes data loss, or creates serious accessibility failure - P1: materially increases confusion, errors, or abandonment - P2: reduces efficiency or scanability - P3: visual polish and refinement For each recommendation include: - exact UI change - affected route/component - law or principle improved - acceptance criteria Be rigorous and evidence-based. Do not claim a law is satisfied merely because the page looks clean. Distinguish visual polish from actual usability, accessibility, comprehension, and task success. If a law is not applicable, mark it N/A and explain why.
````

### 2. Reconciliation with the existing corpus

#### 2.1 Against `docs/research-scratch/PROCESS-PLANS.md` section `## frontend-development-plan.md` §13 ("Laws of UX applied")

The directory **already contains a Laws-of-UX table**, at `docs/research-scratch/PROCESS-PLANS.md` section `## frontend-development-plan.md`:2479-2519. It is
not the same list as the canonical checklist and this plan must reconcile the two rather than ship a
second, contradictory table.

- **Four distinction checks, only two of them Laws of UX (corrected 2026-10-21):** the original text here read
  "four canonical laws are absent from §13: Law of Continuity (11), Law of Closure (12), Law of Common Fate
  (13), Law of Experience (14)". That premise is false: Continuity and Closure are Gestalt principles published
  by uilaws.com, Common Fate is published by neither collection, and no "Law of Experience" exists on either
  site. The checks themselves are still valid and §13 still does not carry them — it does not test
  reading-path continuity, partial-indicator honesty, coordinated motion, or cross-product convention reuse.
  What is wrong is only the label. Read the four as "checks §13 omits", not "laws §13 omits".
- **Five of §13's rows are canonical after all (corrected 2026-10-21):** the original text here read "Seven of
  §13's rows are not in the canonical 27: Chunking, Mental Model, Paradox of the Active User, Parkinson's Law,
  Selective Attention, Working Memory, Postel's Law". Five of those seven *are* published Laws of UX entries —
  Chunking, Mental Model, Paradox of the Active User, Parkinson's Law, Selective Attention and Postel's Law —
  so they are canonical, not adjacent. Only **Working Memory** is genuinely adjacent (it is a capacity notion
  that Miller's Law bounds). They stay bound to real decisions, and §1.1b now carries them as first-class rows
  28-33 rather than as "adjacent principles"; the folding below is retained only as a note on where each bound
  in §13: Chunking→Miller's (5), Working Memory→Cognitive Load (24), Selective Attention→Von Restorff (15),
  Postel's Law→§9 ruling vocabulary (state semantics), Mental Model / Paradox of the Active User / Parkinson's
  Law→design intents the original 27 did not carry, now rows 28-33.
- **§13 is a design-intent table; this plan is a verification contract.** §13 says where a law *binds*;
  §3 says how a verdict on it is *produced and evidenced*. The two compose: §13's "Where it binds" column
  is the starting screen list, and §3's instrument column is what turns the binding into a PASS/PARTIAL/FAIL.
- **No row of §13 is deleted by this plan.** Where a §13 row and a canonical law overlap (e.g. §13
  `Miller's Law` ↔ canonical 5), the plan treats §13 as the prior art and cites it, rather than opening a
  competing row.

#### 2.2 Against the three existing UI audits (findings already law-shaped)

The audits did not name laws, but many of their findings are law failures in everything but name. The
plan **adopts these as pre-classified**, so the audit re-tests them against the law rather than
re-discovering them. The mapping is one-directional: an existing finding can evidence a law; the law does
not create a new finding.

| Existing finding (source) | Canonical law it evidences | What the plan adds |
| --- | --- | --- |
| X-1 / `F-A1`,`F-B2`,`F-E3`,`F-G7` absence reasons mouse-only (P1) | Accessibility channel; Law of Closure (12) is NOT satisfied by a hidden reason; Cognitive Load (24) | a keyboard-reachability check per absence site, not a `title` grep |
| X-2 / `F-A3`,`F-B6`,`F-C4`,`F-G12` type scale never landed; titles twice | Aesthetic consistency (27); Prägnanz (10); Serial Position (16) | a rendered type-scale read at 390/768/1024/1280 |
| X-3 / `F-F3`,`F-G9`,`F-E7` enamel lost its single home | Von Restorff (15); Aesthetic consistency (27) | count of accented primaries per screen, measured |
| X-4 / `F-D2`,`F-D3`,`F-E2`,`F-E4`,`F-G5` silent writes, double-submit | Doherty Threshold (23); Feedback (1.2); Flow (26) | a pending/disabled state read on each write |
| X-5 / `F-D4`,`F-D22`,`F-E11`,`F-F8`,`F-G4`,`F-G11` mouse-only form semantics | Fitts's Law (3); Proximity (6); Accessibility | the 44px sweep + axe + `aria-describedby` association per field |
| X-6 / `F-G2`,`F-B3`,`F-B7`,`F-D28`,`F-E5` contributor copy | Cognitive Load (24); Occam's Razor (21) | a copy read against the client-vocabulary map |
| X-7 / `F-C1`,`F-G1`,`F-G6` props that match neither child nor server | Prägnanz (10) (a control that renders nothing is ambiguous); Implementation Integrity | the two named TS2339-class escapes, plus the `warning`-token dead-class read |
| X-8 / `F-A2`,`F-F6`,`F-D6`,`F-G12` no ratified measure | Miller's Law (5) line length; Law of Continuity (11) | a measured prose width per cluster |
| `setup-wizard-ui-audit` §2.2 step bar cannot tell saved from unsaved | Zeigarnik (19); Visibility of system status; misleading completion indicator (§1.4) | the live capture now proves it (see §0.5); the plan adds the three-state acceptance |
| `cockpit-ui-audit` §3.2 advisor sticky at `lg` | Fitts's Law (3); Law of Continuity (11) | a measured advisor track width and the `min-w-0` collapse check |
| `remaining-screens` §1.2 one P0 (`F-E1`, Legacy builder data loss) | Peak-End Rule (17) at a destructive confirm; error prevention | kept as a defect, not a design finding (its own note, §3.6) |

#### 2.3 Against the app's own UX spine

`docs/research-scratch/DESIGN-CORPUS.md` section `## design-2.0.md` §2's `State → Options → Recommendation → Explanation → Decision` is the app's intended
decision hierarchy. The canonical laws 17-26 (Peak-End, Goal-Gradient, Zeigarnik, Tesler's, Occam's,
Pareto, Doherty, Cognitive Load, Choice Overload, Flow) are exactly the tests of whether a screen honours
that spine. Where a screen's `Recommendation` is missing or its `Explanation` duplicates, the law row
carries the verdict and the spine names the fix.

#### 2.4 Against the instrument findings

`docs/research-scratch/AUDIT-AND-VERIFICATION.md` §"remaining-screens-ui-audit-2026-10-13.md" §4 names six things the quality bar cannot see. Each is a
**blind channel a law travels on**, so this plan adds an instrument for it (§4) rather than repeating the
finding:

| Instrument gap (§4) | Law that travels on it | Instrument this plan adds |
| --- | --- | --- |
| §4.1 typecheck reads no SFC | Prägnanz (10), state semantics | source read + the live capture; do not claim type safety |
| §4.2 copy guard sees no server copy | Cognitive Load (24), Occam's (21) | read the PHP that composes UI strings |
| §4.3 G-18 covers one `ink-muted` pair | Aesthetic consistency (27); contrast | propose pair rows, let the browser gate decide (never move a token) |
| §4.4 registers lag the code | Prior Experience (14) | report the doc/code drift, do not edit the rule |
| §4.5 unvalidated config→badge index | Prägnanz (10) | read the three `DatabaseController` loops |
| §4.6 detector saw nothing | — | treat `[]` as an instrument limit, not a pass |

#### 2.5 Against the HLF reference set (the target rendered)

`docs/proposals/HLF_reference/` (25 captures of a v0 build, `v2.0.0`) is the first artifact in the corpus
that **renders** the target rather than describing it, and it is the natural comparison surface for a laws
verdict: a law that FAILs on the shipped screen but PASSes in the HLF names a concrete, already-drawn fix.
Its patterns map to the canonical laws as:

| HLF pattern | Canonical law it satisfies | Note for the audit |
| --- | --- | --- |
| Eyebrow + large title + subtitle page header | Visual hierarchy (27), Prägnanz (10) | the shipped shell `#title` is `text-base` (X-2) |
| Labeled sub-cells (key/value mini-boxes) | Proximity (6), Common Region (8), Prägnanz (10) | absent from the shipped build |
| Status / provenance / category / effect chips | Von Restorff (15), Similarity (7) | the badge colours conflict with `app.css:57` |
| Progress bars + row-count lines | Goal-Gradient (18), Zeigarnik (19) | Careers progress is the clearest gain |
| KPI stat cards + "Next best action" panel | Pareto (22), Serial Position (16), Flow (26) | the shipped Dashboard has no next action |
| Wizard option cards (one Select button + status line) | Hick's (4), Choice Overload (25), Occam's (21) | a **scope** change, not styling (fidelity file §4.3) |
| Preflight single-row warnings, no duplicate Edit | Occam's (21), Flow (26) | matches the audit's own P1 |
| Advisor: Objectives / Priority / Recommended / Recorded | Flow (26), Peak-End (17), Tesler's (20) | the shipped advisor is a flat rail |
| Compare as a property/value table | Continuity (11), Miller's (5) | better for the responsive region contract |
| Honesty notes on raw engine data | Cognitive Load (24), Prior Experience (14) | already the audits' discipline |

Two cautions the audit must carry: the HLF is itself a **wireframe** (placeholder data, invented copy,
unmeasured colours), so it is a comparison target, not evidence of a passing verdict; and it **reproduces
the stepper P0** (all six steps read "done" while every warning says nothing is recorded), so a screen that
matches the HLF is not automatically correct.

#### 2.6 Against the colour evidence (`docs/research-scratch/PLANS-AND-BRIEFS.md` section `## frontend-fidelity-2026-10-13.md` §2.3)

The fidelity file samples the 19 client sheets and the 11 official-site captures and finds the shipped
palette is **already client-measured**, but drops the marketing palette (royal blue `#2255FF`, hot pink
`#FF77CC`) and the client's chrome distribution (fidelity §2.3). Read through the laws:

- **Von Restorff (15) is satisfied today, and a brand palette can break it.** The single lime accent is the
  one distinct element; the audit's instrument (accent count per screen, §3.2 row 15) is the guard. Adding
  blue chrome and a pink call-to-action is a *Track 2 D* contract change (fidelity §6), not a law fix, and it
  must not raise the accented-primary count above one.
- **Aesthetic-Usability (1) is the law the owner's complaint travels on** — but §9 item 13 binds: a richer
  palette is not a PASS by itself and must not mask a usability defect.
- **Prior Experience (14) and Jakob's Law (2) both point at the client.** The Trainer's familiar product is
  the game; a surface that keeps the client's hues but drops its chrome distribution fails the expectation
  those laws describe. This is the same `app.css:57` conflict §2.5 already flags for the badge colours.

A law does not move a token: any colour change goes through the pair table and the G-18 browser gate (§9
item 1, §4.3), and the scope decision is the owner question in `docs/research-scratch/PLANS-AND-BRIEFS.md` section `## frontend-fidelity-2026-10-13.md` §9 item 9.

#### 2.7 Against the game-client screenshot corpus (the domain precedent)

`docs/research-scratch/screenshot-notes/00`-`06`, `screenshot-notes-extras/` and the `screenshot-registry/`
evidence set describe the real client's UI. This matters to two laws above all: **Jakob's Law (2)** and the
**Prior Experience (14)** are measured against *the product the Trainer already uses*, not against generic
web convention, so the client corpus is the audit's primary convention reference. Its conventions map as:

| Client convention | Where | Laws it informs | Note for the audit |
| --- | --- | --- | --- |
| Per-item detail as a modal over a dimmed backdrop | `00:40`, `04:24` | 2 Jakob's, 8 Common Region | a conventional affordance the app does not use |
| Shape-rank vocabulary with no legend (double-circle/circle/triangle/cross) | `00:16`, `00:97` | 7 Similarity, 14 Experience | graded marks read at a glance; the app uses letters |
| One deviant mark carries the worst rating ("the only negative mark in the row") | `00:26` | 15 Von Restorff | |
| Stacked base/bonus gain bubbles | `01:53` | 6 Proximity, 10 Prägnanz | composition encoded by stacking, unlabelled |
| Three-kind log grammar (speech / narration / signed outcome) | `01:143` | 7 Similarity, 8 Common Region, 14 | one list, three entry kinds |
| Conditional risk channel (pink badge only when non-zero) | `01:318` | 15 Von Restorff, 12 Closure | see the warning-role tension below |
| Pending basket distinct from displayed balance | `03:27` | 14 Experience, 27 consistency | selection and commitment modelled apart |
| Stepper's *absence* marks "owned" | `03:50` | 12 Closure, 7 Similarity | absence of a control is the state |
| Goal ladder: one "next" plus a `Goals Achieved 5/8` footer | `02:160`, `02:165` | 18 Goal-Gradient, 19 Zeigarnik | |
| Contextual hint naming the weak stat, on the decision surface | `05:32` | 4 Hick's, 15 Von Restorff, 26 Flow | guidance on the decision, not a help screen |
| Obligation tag pinned to the action button | `05:85` | 9 Uniform Connectedness, 8 Common Region | obligation and action are separate layers |
| Active row breaks an otherwise uniform table (`Current →`) | `06:100` | 15 Von Restorff, 16 Serial Position | |
| One outcome shown as art-anchored prose and a full running log | `06:26` | 17 Peak-End, 20 Tesler's, 14 | summary and auditable record |

Two cautions, and they are why this is a *reference*, not a checklist:

- **The client is not uniformly law-compliant.** It deliberately raises choice load: the Choices panel is
  "a comparison table, not a confirmation dialog: every option's full branch set is listed at once"
  (`02:101`), and the turn is "a six-way choice presented flat" (`05:32`). "The client does it" is not a PASS
  for Hick's Law (4) or Choice Overload (25); the audit must separate a client convention worth copying from
  a client anti-pattern.
- **The client carries alert channels the app cut.** A red exclamation badge (`01:113`), a pink failure
  badge (`01:318`) and a pinned obligation tag (`05:85`) are alert semantics, while §9 item 3 forbids
  inventing a warning role. The conflict is recorded, not resolved (owner question 7).

#### 2.8 Against the UX material already in `docs/research-scratch`

Beyond the three audits, the client notes and the HLF, `docs/research-scratch` already carries UX-law and
heuristic material. The plan must reconcile it rather than re-derive it:

| Source | What it is | Canonical laws it touches | How the plan uses it |
| --- | --- | --- | --- |
| `AUDIT-AND-VERIFICATION.md` §3 "UX-law lens (driven, not asserted)" (`:4284-4293`) | the repo's only *driven* law table: Fitts's Law FAILs on nav/export links (L-F01); Hick's, Common Region, Zeigarnik and Von Restorff HOLD; Jakob's/Prägnanz/Aesthetic-Usability "not invoked as anchors" | 3, 4, 8, 19, 15, 2, 10, 1 | prior art and the *method*: a law verdict carries a driven check, not an assertion |
| `UX-DELIVERABLES.md` Part 3 (`:1729-2182`) | a full Nielsen-10 + 5 game-specific heuristic evaluation of the *game*, rated 0-10, severity 0-4, overall 6.6/10 | overlaps 1-7, 10-12, 15, 17-19, 21, 23-27 | the only existing *heuristic* evaluation in the repo; see the two cautions below |
| `DESIGN-CORPUS.md` §5 "Interaction and state patterns worth carrying" (`:4091-4098`) | six declared patterns: preview-before-commit, colour-always-paired-with-a-word, numbers-above-what-they-change, one-saturated-tile-marks-the-active-destination, grouping-by-phase-collapsible, shape-carries-meaning | 3, 6, 8, 9, 12, 15, 18, 24, 27 | the app's own declared principles; a screen that violates one is a law FAIL with a repo citation |
| `DESIGN-CORPUS.md` §35 Progressive Disclosure (`:6503`), §36 Explainability (`:6538`) | declared principles: do not expose every modifier at once; every recommendation must answer "why?" | 4, 20, 21, 24, 25, 26 | the concrete check for the Hick's / Choice Overload / Cognitive Load rows |
| `DESIGN-CORPUS.md` "Additional Design Details" (`:5614-5622`) | shared comparison pattern (never a single unexplained score); `N/A` + `title` for unavailable; progressive disclosure at 320px; non-colour-only warnings; actual-vs-hypothetical balances | 1, 12, 14, 20, 22, 24, 27 | the accessibility floor the laws are tested against; also the source of §9's "no score" rule |

Two cautions:

- **Nielsen's heuristics are not the canonical 27.** The `UX-DELIVERABLES.md` evaluation is organised around
  Nielsen's 10 plus five game-specific heuristics (Feedback Loop Quality, Reward Cadence, Cognitive Load
  Management, Information Architecture, Progressive Disclosure). The plan maps them to the canonical 27
  rather than adopting them as a second checklist — the same treatment §2.1 gives `docs/research-scratch/PROCESS-PLANS.md` section `## frontend-development-plan.md`
  §13.
- **That evaluation scores the *game*, not Trainer Desk.** Its ratings and severities are about the client,
  and the whole file is a triaged mirror (`DESIGN-CORPUS.md:3371, 3389-3391`, `D-283`/`D-285`): it supplies
  patterns and a method, never values, and it is not a verdict on this app. Its own provenance is the
  verbatim merge of three source write-ups, tooled at
  `docs/research-scratch-source/scripts/merge_ux_docs.py` and `verify_ux_merge.py`.

#### 2.9 Against the 2026-10-13 live-capture laws pass (`UX-AUDIT-REPORT.md`)

A first **partial laws pass** already exists: `docs/audits/UX-AUDIT-REPORT.md` (2026-10-13),
framework "Laws of UX (lawsofux.com)", produced from the same session-private harness this plan adopts in
§4.2 (21 screens × 4 widths, 81 captures). It is the plan's output contract (§1.5, §6) already prototyped:
a summary, per-screen findings tables with a law column and P0-P3 severity, cross-page findings, and a
prioritized remediation plan.

- **Its declared violations:** Visibility of system status, Postel's Law, Occam's Razor, Tesler's Law,
  Zeigarnik, Von Restorff. Note the mix: "Visibility of system status" and "Recognition over recall" are
  Nielsen heuristics, and "Postel's Law" is a §2.1 adjacent principle, not a canonical row.
- **Its declared strengths:** Jakob's Law, Common Region / Proximity, Aesthetic-Usability, Fitts's Law,
  Serial Position.
- **Its findings corroborate the three audits independently** — the stepper "done" P0 (`SetupLayout.vue:45-46`),
  the Preflight duplication, the undifferentiated `N/A`, and the advisor track's missing width floor — a fourth
  confirmation alongside `setup-wizard-ui-audit` §2.2 and the cockpit audit.
- **The cluster-audit rubric already encodes a cognitive-load checklist** (.scratch-uma/ui-audit-2026-10-13/rubric.md:
  single focus, chunking ≤ 4, grouping, one thing at a time, ≤ 4 options at a decision point, no memory bridge,
  progressive disclosure) — the instrument for the Miller / Hick's / Choice Overload / Cognitive Load rows.

**Durability caveat.** This report lives under gitignored `.scratch-uma/` (KI-70), so it is session-private and
**not durable evidence** under §4.6: its findings must be re-derived on a quiet box or promoted into a tracked
document before they count. The plan treats it as a pilot of the output contract, not as a filed audit.

### 3. Coverage matrix — law × cluster × instrument

#### 3.1 Clusters (inherited, unchanged)

The eight clusters are `docs/research-scratch/AUDIT-AND-VERIFICATION.md` §"remaining-screens-ui-audit-2026-10-13.md" §0.1 (A shell/home/errors, B reference
catalogs, C Database hub, D1 run lifecycle, D2 queue/settings, E Legacy/Veterans/deck, F career decisions,
G record/outcome/snapshot), plus the two surfaces the other audits own: **Cockpit** (`SCR-CAR-011`) and
**Wizard** (the six `/career/setup/*` steps). Ten units in total. The audit adds no cluster.

#### 3.2 The matrix

For each canonical item: the cluster(s) where it is load-bearing, the concrete repo check, and the
instrument that produces the verdict. `Auto` = a gate or script can decide it; `Judge` = expert read
against a screenshot/DOM; `Measure` = a live capture reading (width, height, line count, timing).

| # | Law / principle | Binds (clusters) | Concrete repo check | Instrument |
| --- | --- | --- | --- | --- |
| 1 | Aesthetic-Usability Effect | all | tokens-only, no `dark:`, no shadow/gradient identity (`DESIGN.md:231`); a "clean" screen is not a pass | Judge + token grep |
| 2 | Jakob's Law | A, D1, Wizard | sidebar/table/form/stepper conventions present; `TraineeCombobox.vue` is a real combobox | Judge |
| 3 | Fitts's Law | A, D1, D2, F | 44px house bar; primary action nearest the decision | Auto (`tests/utils/tap-targets.ts`) |
| 4 | Hick's Law | B, C, D1, F, Wizard | simultaneous option count per screen; defaults present | Judge + Measure (control count) |
| 5 | Miller's Law | C, E, F, G, Wizard | chunking to ≤ ~5-7; nav ≤ 8 (`PROCESS-PLANS.md:6560`) | Judge |
| 6 | Law of Proximity | all | label↔field, action↔content distance; provenance badge beside its number | Judge + Measure |
| 7 | Law of Similarity | B, C, E, F | equivalent controls share one component/class; `VeteranRow` is the one row shape; one `SparkChip` per spark (`PROCESS-PLANS.md:6686`) | Auto (class grep) + Judge |
| 8 | Law of Common Region | all | cards/fieldsets bound one concept; no over-containerisation | Judge |
| 9 | Law of Uniform Connectedness | E, G | ancestry graph lines, timeline rail, stepper connector | Judge |
| 10 | Law of Prägnanz | all | one idea per card; no ambiguous control; dead `warning` classes (`F-G1`) render nothing | Judge + source |
| 11 | Law of Continuity (Gestalt, uilaws.com) | A, C, F, G | reading order; stepper/timeline/table scan path; `lg` track balance | Judge + Measure |
| 12 | Law of Closure (Gestalt, uilaws.com) | A, B, G | partial indicators honest; a hidden reason is a FAIL, not closure | Judge + a11y |
| 13 | Law of Common Fate (Gestalt, unpub.) | F, G | one `animate-pulse` (`F-G10`); coordinated transitions only | Auto (motion grep) |
| 14 | Prior Experience (sourced principle, no published law) | all | reuses domain conventions; docs/code drift reported (§4.4) | Judge |
| 15 | Von Restorff Effect | A, D2, F, G | accented primaries per screen; the one `RECOMMENDED` marker | Measure (count) |
| 16 | Serial Position Effect | A, F, Wizard | primary action first/last; high-priority nav at ends | Judge |
| 17 | Peak-End Rule | G, Wizard, Result | errors/completion/save/final-confirm are clear; `F-E1` destructive confirm | Judge + source |
| 18 | Goal-Gradient Effect | F, Cockpit, targets | progress shows distance-to-goal, not just current value | Judge |
| 19 | Zeigarnik Effect | Wizard, E, F | incomplete work visible/resumable/distinct; step bar three-state | Judge + Measure (stepper) |
| 20 | Tesler's Law | Wizard, E, F | the tool absorbs complexity; no internal-model exposure | Judge |
| 21 | Occam's Razor | all | duplicate copy/controls/steps removed; deck paragraph ×6 | Judge + source |
| 22 | Pareto Principle | A, D1, Wizard | core workflow first; advanced features secondary | Judge |
| 23 | Doherty Threshold | B, D1, D2 | immediate feedback; `processing` exists; no unexplained pause | Measure (timing) + source |
| 24 | Cognitive Load | all | recognition over recall; concise instructions; no contributor copy | Judge + source |
| 25 | Choice Overload | B, C, Wizard | defaults/recommendations/filtering/progressive disclosure | Judge |
| 26 | Flow | Cockpit, F, Wizard | oriented, uninterrupted, next action obvious; no modal over a decision | Judge |
| 27 | Aesthetic consistency & hierarchy (house principle, not a law) | all | one token set, one type scale, one focus ring, one component behaviour | Auto (token grep, G-18) + Measure |

#### 3.3 Automatable vs judgement

- **Automatable now:** 3 (tap-targets), 13 (motion grep), 27 (token/type grep + G-18 browser gate), and the
  accessibility sub-checks under 1.2's Accessibility row (axe, `aria-describedby` association).
- **Measure (live capture):** 4, 6, 11, 15, 19, 23 — width, control count, accent count, stepper state,
  timing. These are the rows `docs/research-scratch/AUDIT-AND-VERIFICATION.md` §"remaining-screens-ui-audit-2026-10-13.md" marks `needs measurement`.
- **Judgement only:** 1, 2, 8, 9, 14, 16, 17, 18, 20, 21, 22, 24, 25, 26. Each must cite a screenshot or a
  `file:line`, never an impression.

### 4. Instruments

#### 4.1 Source read (primary, cheapest)

Read each page and its composed components plus the controller that builds its props, as the existing
audits did. Every claim about rendered appearance that the source cannot settle is marked
`needs measurement` and names the measurement (the discipline `docs/research-scratch/AUDIT-AND-VERIFICATION.md` §"remaining-screens-ui-audit-2026-10-13.md"
states at its head, `:14-18`).

#### 4.2 Live browser capture (the measurement instrument)

The 2026-10-13 session established the harness and it is reusable:

- **Session-private, always** (`AGENTS.md` §9, KI-73, KI-80): a private port and a private scratch
  database, e.g. `PLAYWRIGHT_PORT=8177 PLAYWRIGHT_SCRATCH_DB=.scratch-uma/ux-audit/browser-8177.sqlite`.
  An unqualified `npm run test:browser` in a shared worktree takes a peer's suite down.
- **Pre-flight every broad sweep** (`KI-74`): time one trivial SQLite read against the dev file; if it
  takes more than a few seconds the box is contended and the sweep is **void before it starts**. Measured
  2026-10-13: 0.93s quiet (`docs/research-scratch/PROCESS-PLANS.md` §"trainer-desk-remaining-baseline.txt" (source line 17)), 6ms on a second quiet read, but a
  later run was cut short by peer Pest suites and two dev servers (8143, 8000) and was **not** relaunched.
- **Capture contract per (route × width):** full-page screenshot at 390/768/1024/1280, plus a metric pass
  — horizontal overflow (`scrollWidth - clientWidth`), elements where `scrollWidth > clientWidth`, the
  minimum visible control height, and a "squeeze" scan (a short leaf text node wrapped to ≥3 lines, the
  proxy for the "one character per column" defect class).
- **Resumable and incremental:** write results after every capture and skip a `(label,width)` already in
  `results.json`, so a crash or a contended box loses nothing. The 2026-10-13 first attempt lost its
  metrics because it wrote only at the end; the rewrite fixed that.
- **Axe as a separate pass** (`AUDIT_AXE=1`), never inside the timing sweep, so the layout pass stays
  fast and hang-resistant.

#### 4.3 Gates reused (not reinvented)

`tests/browser/accessibility.spec.ts` (axe A+AA scoped to `#app`), `tests/utils/tap-targets.ts` (44px),
`tests/utils/accessibility.ts` (`buildAxe`), `tests/Feature/DesignTokensTest.php` (the G-18 pair list at
`:298`, `:540-546`), `tests/Feature/RenderedCopyHygieneTest.php` (with its §4.2 limit), and the browser
specs named per screen in the three audits' "Guarded" columns.

#### 4.4 Measurement discipline

A verdict is `Measure` only when a number was read. Contrast is never asserted from a class list; the
G-18 pair list is extended and the browser gate decides (§4.3, `remaining-screens` §5.1). A passing axe
scan is never proof of an accessible name (`remaining-screens` §5.11, `F-G4`).

#### 4.5 Reference-target comparison (the HLF)

For any law that FAILs on a shipped screen, compare it against the matching capture in
`docs/proposals/HLF_reference/` before writing the recommendation (§2.5): a pattern the HLF already renders
(sub-cells, badges, progress bars, the option-card wizard) turns a judgement call into "adopt this pattern,
subject to the token and scope decisions in `docs/research-scratch/PLANS-AND-BRIEFS.md` section `## frontend-fidelity-2026-10-13.md` §4-§6". This is a `Judge`
instrument with a rendered citation, not a `Measure` one, and the HLF's own caveats (§2.5) apply.

#### 4.6 Evidence discipline borrowed from the screenshot registry

The registry is a worked example of the evidence contract this audit needs, and its rules are stricter than
"cite a file":

- **A note is a lead, not a verification.** `screenshot-registry/README.md:166-167`; the state
  `documented_unverified` exists to mark that boundary. A law verdict is never based on a note alone.
- **No rule claim from a single frame.** `screenshot-registry/FINAL_REPORT.md:277`: "No claim about a rule
  has been made from a single screenshot." A single frame may evidence a *state*, not a *rule*.
- **Confidence is state-encoded, not prose.** `verified` requires a recorded observation; `partial` requires
  an outstanding question (`README.md:92-93`). A law verdict carries the same: PASS with its evidence, or
  PARTIAL naming what is unresolved.
- **Provenance is append-only** (`README.md:112`), and a cluster "is not a byte identity" (`README.md:61`,
  `FINAL_REPORT.md:276`). Where a finding rests on a perceptual group or a derived index, the caveat travels
  with it.
- **Inference is fenced from observation.** `PHASE-B-REPORT.md:154` flags a note's "presumably" as "an
  inference marker in a note that elsewhere separates observation from reading". A verdict that reasons past
  what a frame shows must say so.

Applied here: every PASS/PARTIAL/FAIL in the §6 report carries a screenshot path or a `file:line`; a measured
claim names the number (§4.4); and a claim resting on one frame is downgraded to `needs measurement`.

### 5. Method per page

For each route:

1. State the page's primary user goal and the main task.
2. Walk §1.2's ten axes and §1.3's states; note which states the page cannot reach.
3. Score each **applicable** canonical law PASS / PARTIAL / FAIL / N/A; N/A names why (e.g. Goal-Gradient
   is N/A on a static reference table; Common Fate is N/A on a screen with no motion).
4. Cite concrete evidence: a `file:line`, a screenshot path, or a measured number. No claim without one.
5. State the user impact in UX terms (discoverability, comprehension, decision cost, error risk,
   confidence, efficiency, accessibility, completion).
6. Give one specific recommended change, never a vague suggestion.
7. Assign severity P0-P3 (§7) and an issue type (visual, content, interaction, responsive, accessibility,
   architectural).
8. Reconcile with the existing audit: if the finding is already in one of the three audits, cite its ID
   (`F-xx`) instead of filing a new one; if it is new, it is new evidence, not a new register entry.

### 6. Output contract

The report is produced in the exact structure of §1.5, with one addition that the existing corpus needs:
each route's findings table carries an **`Existing ID`** column so a reader can join a law verdict to the
audit finding it came from (`F-A1`, `X-1`, …) and never sees the same defect filed twice. The four report
sections map to the existing files as: **Summary** ≈ `remaining-screens` §1 (score card, reused) plus a
laws-frequency count; **Route-by-route** ≈ the cluster tables (§3) re-projected per law; **Cross-page
consistency** ≈ `remaining-screens` §2 (X-1..X-8) re-read as law rows; **Prioritized remediation** ≈ the
existing severities, grouped P0-P3, each with an acceptance criterion.

### 7. Severity and scoring

Severity is the impeccable scale already in use (`docs/research-scratch/AUDIT-AND-VERIFICATION.md` §"remaining-screens-ui-audit-2026-10-13.md" §1.2): P0
blocks task completion, causes data loss, or is a serious accessibility failure; P1 materially increases
confusion, errors, or abandonment; P2 reduces efficiency or scanability; P3 is polish. The plan adds no
new scale. A law FAIL is not automatically P0: severity is the impact, not the law. The cluster score card
(§1.1) is **not** rescored; the laws layer reports counts per law, joined to the existing severity counts.

### 8. Effort and batching

- Ten clusters, ~50 routes, four widths. Capture dominates wall-clock (a document is ~0.3-1s plus a
  screenshot; the 2026-10-13 sweep averaged roughly 0.3 shots/s before contention).
- Batch one cluster at a time, in the existing order (A → G, plus Cockpit and Wizard), each batch =
  capture → per-law verdicts → reconcile with the existing IDs. Two checkpoints (after D2, after G) so
  emphasis can be redirected before the tail.
- The sweep is split into a **layout pass** (no axe, fast, resumable) and an **axe pass** (subset), so
  neither can void the other under load.

### 9. What this audit must not do

Inherited verbatim in substance from `docs/research-scratch/AUDIT-AND-VERIFICATION.md` §"remaining-screens-ui-audit-2026-10-13.md" §5, because a law is not a
licence to break a ruling:

1. Do not move a colour token to answer a contrast question; propose pair rows and let the gate decide.
2. Do not add a fifth provenance label or a section-qualifying badge.
3. Do not invent a `--color-warning` role; the declared pair is `risk`.
4. Do not reflow the race calendar, turn log, or compare tables into stacked cards; the region contract is
   `overflow-x-auto` + `role="region"` + `tabindex="0"` + `aria-label`.
5. Do not add a cold-load spinner, skeleton, or `aria-busy` on page load (`ADR-0007`).
6. Do not add a score, percentage, confidence, risk grade, win chance, or recommendation to any surface.
7. Do not name a scenario in a component (gate G-33) or default a `scenarioLabel`.
8. Do not restyle per-page headings before the shell `#title` decision is settled.
9. Do not migrate an absence without moving the specs that pin the current channel (owner approval).
10. Do not add a client-side theme write.
11. Do not treat a passing axe scan as proof of an accessible name.
12. Do not widen `AGENTS.md` §7's component list, and do not hand-edit `PRODUCT.md`, `SKILL.md`, or the
    derived ADR index.

And two the laws lens specifically tempts, added here:

13. Do not treat "it looks clean" as a PASS. §1.5's closing line binds the auditor: polish is not
    usability, accessibility, comprehension, or task success.
14. Do not let a law override a ruling. Where a canonical law asks for what an ADR or a `SCREEN_SPEC`
    ruling forbids (no score, no win-chance, no warning role, no scenario name in a component), the
    conflict is recorded and the ruling wins.

### 10. Questions for the owner

1. **One table or two (§2.1)?** `docs/research-scratch/PROCESS-PLANS.md` section `## frontend-development-plan.md` §13 and this plan both map laws to
   decisions. Should §13 be restated to the canonical 27 and the seven extras folded in as adjacent
   principles, or should §13 stand as design-intent and this plan stay the verification contract beside
   it? (The plan currently assumes the latter.)
2. **The four laws §13 omits (Continuity, Closure, Common Fate, Experience).** Are they in scope as
   first-class rows (the plan assumes yes), or deliberately traded as §13's "Deliberately traded" note
   does for Occam's Razor in the Database area?
3. **Zeigarnik and the step bar.** The live capture proves the bar marks visited-not-saved steps "done".
   Does the fix change the label (keep the 2026-10-13 ruling that "completed" means visited) or the state
   model (three states, "done" only when saved)? This is the same question `setup-wizard-ui-audit` §6
   raises; it needs one answer.
4. **`Measure` budget.** Do you want the full four-width capture on all ~50 routes, or the reduced
   first pass (one representative per cluster) that the 2026-10-13 live run effectively produced before
   contention?
5. **Register (§2.2, §4.4).** A law audit will surface the same defects the three audits already filed.
   Confirm the rule the plan assumes: cite the existing `F-xx`/`X-n` and add no new register entry unless
   the defect is genuinely new.
6. **The HLF as the comparison surface (§2.5, §4.5).** Should every FAIL be compared against the matching
   `HLF_reference` capture before a recommendation is written — i.e. is the HLF the canonical comparison
   target? And does the HLF's wizard simplification (option-card presets that drop the per-stat, ancestry
   and deck editors) fall inside the audit's scope, or stay a separate `SCREEN_SPEC`/`PRD` scope decision
   as `docs/research-scratch/PLANS-AND-BRIEFS.md` section `## frontend-fidelity-2026-10-13.md` §4.3 proposes?
7. **The client's alert channels (§2.7).** The client carries a red exclamation badge (`01:113`), a pink
   failure badge (`01:318`) and a pinned obligation tag (`05:85`), while §9 item 3 forbids inventing a
   `--color-warning` role and the declared pair is `risk`. Does the audit record the client's alert
   vocabulary as a convention to *report* (a PARTIAL on Von Restorff/Closure), or does it stay out of scope
   because the app's cut is deliberate?
8. **Method of record (§2.8).** The repo already holds a Nielsen-style heuristic evaluation of the game
   (`UX-DELIVERABLES.md` Part 3: 0-10 ratings, 0-4 severity, an overall 6.6/10). Should this audit adopt that
   rating *shape* (a per-heuristic score and an overall number) alongside the PASS/PARTIAL/FAIL contract and
   the impeccable severity scale, or keep the two separate — the laws layer reporting counts per law, as §7
   assumes?

### 11. Verification plan for any approved subset

Per `AGENTS.md` §9's cost-aware escalation. Nothing below was run for this plan.

| Approved change | Level 1 while working | Level 2 at slice end | Level 3 before hand-off |
| --- | --- | --- | --- |
| This plan itself (docs only) | `python tools/md_fence_check.py docs/proposals/ux-laws-audit-plan-2026-10-13.md` | — | the file's own fence check; no code gate applies to a proposal |
| A law verdict re-classified against an existing finding | the named finding's Guarded tests | the cluster's specs | the hand-off sequence only if the re-classification changes a proposed fix |
| Any fix a law row recommends | that screen's spec + `tests/utils/tap-targets.ts` where height is involved | `tests/browser/accessibility.spec.ts` | the full suite; a `Measure` claim must carry the number, not a screenshot alone |
| The live capture harness (§4.2) | one cluster at one width | the cluster at four widths | pre-flight the SQLite read (`KI-74`); if contended, the sweep is void and is reported void |

### 12. Hand-off notes

- **Deliverables produced:** this file. No component, token, copy string, gate, spec, or test was changed.
- **Facts this plan relies on, and their source:** the canonical list comes from the supplied checklist (§1),
  corrected 2026-10-21 against the live indexes at https://lawsofux.com/ (28 entries) and
  https://www.uilaws.com/ (13 visual principles): four of the supplied rows are re-attributed (§1.1) and six
  lawsofux.com entries the supplied list omitted are added as rows 28-33 (§1.1b);
  the existing Laws table is `docs/research-scratch/PROCESS-PLANS.md` section `## frontend-development-plan.md`:2479-2519; the findings and severities are
  `docs/research-scratch/AUDIT-AND-VERIFICATION.md` §"remaining-screens-ui-audit-2026-10-13.md" §1-§3; the instrument limits are its §4; the two other
  audits own the Cockpit and the wizard; the baseline is `docs/research-scratch/PROCESS-PLANS.md` §"trainer-desk-remaining-baseline.txt"; the live
  capture harness and the 81 screenshots are `.scratch-uma/ux-audit/`; the rendered comparison target is
  the 25 captures in `docs/proposals/HLF_reference/` (§2.5), read in full; the colour evidence in §2.6 is
  sampled in `docs/research-scratch/PLANS-AND-BRIEFS.md` section `## frontend-fidelity-2026-10-13.md` §2.3 (quantized pixel buckets from the 19 client sheets and
  11 site captures, via `.scratch-uma/ux-audit/palette-extract.ps1` and `palette-accents.ps1`); the
  game-client conventions in §2.7 are from `docs/research-scratch/screenshot-notes/00`-`06`,
  `screenshot-notes-extras/` and `screenshot-registry/` (all citations verified against source), and §4.6's
  evidence rules are `README.md` and `FINAL_REPORT.md`; the prior UX-law and heuristic
  material reconciled in §2.8 is `AUDIT-AND-VERIFICATION.md:4284-4293` (the only driven law table),
  `UX-DELIVERABLES.md:1729-2182` (the Nielsen-style heuristic evaluation) and `DESIGN-CORPUS.md:4091-4098,
  5614-5622, 6503, 6538`. `docs/research-scratch-source/` was scanned and holds **no** UX-law material (0
  markdown files; raw scrapes, game JSON, a screenshot inventory and build scripts), so nothing was drawn
  from it beyond the merge-tool provenance of `UX-DELIVERABLES.md`; `docs/audits/UX-AUDIT-REPORT.md`
  (2026-10-13) is a first partial laws pass, reconciled in §2.9 with a durability caveat, and
  .scratch-uma/ui-audit-2026-10-13/rubric.md supplies the cluster-audit cognitive-load checklist.
- **Not delivered, stated plainly:** no law verdict was produced here; this is the plan that says how to
  produce them. No browser measurement, no contrast arithmetic, no suite run, no lore gate. The HLF was
  read as reference; nothing in it was implemented or copied.
- **Facts this pass corrected in its own brief:** the directory already carries a Laws-of-UX table (§13 of
  the frontend plan) and it is not the canonical 27 — four canonical laws are missing from it and seven of
  its rows are not canonical; the live capture corroborates two existing findings (the step bar and
  Preflight) rather than opening new ones; and the HLF reference set now supplies a rendered target, which
  makes the §4.5 comparison instrument possible. **Added 2026-10-13:** the game-client screenshot corpus is
  the primary convention reference for Jakob's Law and Prior Experience (14), so §2.7 maps it and §4.6 binds
  the audit's evidence discipline to the registry's rules.

---

## group-ab-rulings-owed.md

> **Consolidated 2026-10-26 (Round 21, owner-ordered).** Source: `docs/proposals/group-ab-rulings-owed.md` (223 lines), <!-- census: quoted-original -->
> reproduced verbatim below with headings demoted one level. The original was deleted from the tree
> after every non-blank line was found here (184/184); the fold, the pointers repointed
> and the disposition of the originals are recorded in `docs/research-scratch/INDEX.md`
> section `### Round 21`. Read the sibling sections in these masters rather than looking for the
> old paths: the group-ab and remaining-work plans are in `PROCESS-PLANS.md`, the two rulings-owed
> briefs in `PLANS-AND-BRIEFS.md`, the three audits in `AUDIT-AND-VERIFICATION.md`, and the two root skill-registry files in
> `GOVERNANCE.md`.

## Group A + Group B — rulings owed

**Date.** 2026-10-24. One section per blocked item, two options each, with a recommendation. The owner
rules; nothing in Phase 2 or Phase 3 opens until these are answered. Audit: `docs/research-scratch/AUDIT-AND-VERIFICATION.md`, section `## group-ab-audit-2026-10-24.md`.

---

#### A-0.2 — Does the run store its own aptitude letters, or does the catalog reading suffice?

**Source.** Walk-1 finding D8 documents the aptitude question and was verified **not a tool defect** —
the catalog letters are correct; the frame's were wrong. Walk-3 U-02 and the source §1.1/§1.2 record the
run's aptitude letters. The app holds aptitudes only on the `umamusume` catalog table
(`database/migrations/2026_09_27_090000_add_aptitude_grades_to_umamusume_table.php`); `TrainingRun`
carries no aptitude columns (`app/Models/TrainingRun.php:105`).
**Current state.** Aptitudes are catalog-only and correct; a run's post-inheritance aptitude drift is not
representable.
**Option A — add per-run aptitude columns.** Consequence: a run can record its own letters, but it is a
migration and needs an ADR plus a digest line.
**Option B — keep the catalog reading.** Consequence: the gap stays closed as "verified, not a defect"
(D8), and no schema lands.
**Recommendation.** B — D8 already settled the reading, and per-run aptitude drift is low-value for a
single-Trainer tool.
**Lands in.** nothing (a "will not add" note), or a new ADR if A.
**Blocks.** nothing — filed as KNOWN-ISSUES.

---

#### A-0.4 — Does the run store the effect values each card has actually reached?

**Source.** Walk-1 Screen 6 (`:309-312`) and walk-3 U-08 document "the per-card 'Client rows visible'
values … the effects this run has actually reached" (§1.4 lines 97-104). The deck builder uses catalog
maxima (`ADR-0014:8`).
**Current state.** Unrecorded; `ADR-0014:8` states it open — "the builder's analysis continues to use
catalog anchors".
**Option A — store per-run reached effects.** Consequence: needs a card-level column first (A-0.5) plus
an effect lookup; a migration and ADR.
**Option B — keep catalog maxima.** Consequence: the deck analysis stays an anchor read, which it
discloses; no schema.
**Recommendation.** Defer to A-0.5 — reached effects are derivable from a stored level, so this rides the
card-level ruling.
**Lands in.** nothing now; a new ADR if A-0.5 lands and A.
**Blocks.** nothing — filed as KNOWN-ISSUES.

---

#### A-0.5 — Is the run's per-run card level a fact to store, or is it collection state already declined?

**Source.** Walk-1 Screen 6 (`:307-308`) and walk-3 U-08 document "card levels and limit breaks … no
field on any deck surface" (§1.4 "Client level" column).
**Current state.** `ADR-0014:24` declines the **collection** (`user_support_cards` — level, breaks, perk)
as §6.9-cut; `ADR-0014:8` leaves "card levels and limit breaks are still unrecorded" open. `deck_slots`
carries no level column.
**Option A — add `deck_slots.card_level` (per-run).** Consequence: a run records the level its equipped
card sits at, distinct from collection ownership; a migration and ADR.
**Option B — treat card level as collection state, already declined.** Consequence: nothing lands, and
the absence is recorded as a ruling (ADR-0014), not an oversight.
**Recommendation.** A — the source distinguishes "what the card can be" from "what this run has", and the
per-run level is a run fact, not the Trainer's collection.
**Lands in.** a new ADR plus `deck_slots` migration and the deck surfaces.
**Blocks.** nothing — filed as KNOWN-ISSUES.

---

#### A-0.6 — Does the tool need a roster editor, or is the derived read-out enough?

**Source.** Walk-3 U-09 and probe P8 (`:949`) and walk-4 S08 document "no roster surface exists anywhere
in the flow" (§1.9, §6 item 7).
**Current state.** `TrainingRun::spiritBurstRoster()` (`app/Models/TrainingRun.php:1255`) derives a
read-only roster from turn-event `SpiritBurstPayload` rows; `TeamPanelBuilder::teamSection()`
(`app/Services/Career/TeamPanelBuilder.php:442-452`) ships it. No stored roster, no write path.
**Option A — add roster storage (table or stored payload).** Consequence: a Trainer can record the
18-20 named members; a migration and ADR.
**Option B — keep the derived read-out.** Consequence: the roster stays a display of recorded burst
states, not an editable list; no schema.
**Recommendation.** B — the roster is a display fact, not a decision input, and no walk showed it driving
a choice.
**Lands in.** nothing, or a new ADR if A.
**Blocks.** nothing — filed as KNOWN-ISSUES.

---

#### A-0.11 — Is the Mood Effect one sourced figure to render, or a per-tier lookup table?

**Source.** Walk-4 §7 item 3 documents the Mood Effect panel at "±20% training and ±4% running"
(§1.7; `docs/UMAMUSUME_REFERENCE.md` §1.1.6). The mood renders as a tier pill only
(`resources/js/components/MoodPill.vue`).
**Current state.** The figure is a single sourced reading in the reference corpus, not in any `config/`
table or code constant; no per-tier percentage table exists.
**Option A — transcribe the one sourced figure (±20% training / ±4% running) into `config('uma')` and
render it beside the mood.** Consequence: the cockpit shows the two sourced numbers; a small, sourced,
read-only addition, no schema.
**Option B — build a per-tier percentage lookup.** Consequence: a fuller surface, but the per-tier figures
are not sourced anywhere this repo reads, so it would be invented or held.
**Recommendation.** A — the source confirms one figure, not a ladder.
**Lands in.** `config/uma.php`, `CockpitController`, `CareerStatePanel.vue` (or a mood cell).
**Blocks.** nothing — filed as KNOWN-ISSUES until ruled.

---

#### B-0.1 — Scenario-scoped mandatory races: filter the seeded set, or seed per scenario? (R4-06)

**Source.** Walk-4 F-05 documents a Unity Cup career listing URA Finals names as mandatory (§1.6).
**Current state.** `CockpitController::obligationSlots()` (`app/Http/Controllers/Career/CockpitController.php:667-675`)
and `TeamPanelBuilder::careerGoalSections()` (`app/Services/Career/TeamPanelBuilder.php:86-92`) filter
`forScenario()->where('is_mandatory', true)`; the scenario scoping exists but does not remove seeded
rows the matrix should not show.
**Option A — filter the seeded mandatory set by scenario.** Consequence: a Unity Cup run's obligation list
carries no URA Finals row; no schema change.
**Option B — a new seed migration adding per-scenario mandatory rows.** Consequence: correct per-scenario
obligations, but a seed/schema change and an ADR first.
**Recommendation.** A — the scoping column already exists; filtering is the no-schema fix.
**Lands in.** `CockpitController.php:568-584`, `RaceDecisionController.php:40-44`.
**Blocks.** B-3.1.

---

#### B-0.2 — Cleared goals: add completed-race recording, or a goal-state column? (R4-07)

**Source.** Walk-4 F-06 documents "Goals Achieved 0 of 8" derived from nothing (§1.6). The round-4
register recategorised `KI-34`.
**Current state.** `TraineeGoal.php:20-27` (`app/Models/TraineeGoal.php`) states progress is derived from
`race_entries`; `TeamPanelBuilder::objectiveMet()` (`app/Services/Career/TeamPanelBuilder.php:321-359`)
reads completed entries. The gap is an un-recordable completed race, not a missing goal column.
**Option A — add completed-race recording (write path).** Consequence: a completed entry with a placement
feeds the existing derivation; no new column.
**Option B — add a goal-state column.** Consequence: a stored goal state, but it creates a second owner
for a fact `race_entries` already answers, and a migration.
**Recommendation.** A — matches the model's own design (`TraineeGoal.php:20-27`).
**Lands in.** a race-write path (the race decision screen or `StoreRaceEntryRequest`).
**Blocks.** B-3.2.

---

#### B-0.3 — Pending turn + date: a "pending" state, or a plain-form date field? (R4-08)

**Source.** Walk-4 F-07 documents a mid-career snapshot forcing a fabricated outcome; the round-4
register names the two shapes.
**Current state.** `StoreTurnEntryRequest` (`app/Http/Requests/StoreTurnEntryRequest.php:74-86`) requires
stats and `turn`; `choice`/`outcome` are required only when `stage` is set (`:142-143`), so the plain
form already accepts a no-outcome row. There is no date input, and `TurnChoice`
(`app/Enums/TurnChoice.php`) has no pending case.
**Option A — add a "pending"/undecided turn state (new `TurnChoice` value or enum).** Consequence: the
guided rail can record an undecided turn; a shape change needing the ruling.
**Option B — add a date field to the snapshot door (`StoreSnapshotRequest`).** Consequence: a snapshot
names year/month directly instead of deriving it from a turn number; no outcome invented.
**Recommendation.** B — the date field answers the snapshot's real need with no vocabulary change.
**Lands in.** `StoreSnapshotRequest`, the snapshot surface.
**Blocks.** B-3.3.

---

#### B-0.4 — Scheduled race: a stored "undecided" status, or a derived line? (R4-10)

**Source.** Walk-4 E-09 documents a race being decided offering only `Enter Race` (§1.6).
**Current state.** `RaceEntryStatus` (`app/Enums/RaceEntryStatus.php:12-19`) is
`NotOffered`/`Skipped`/`Entered`/`Completed`; the only write stores `Entered`.
**Option A — add an `Undecided`/`Scheduled` status value.** Consequence: a stored-vocabulary change with
owner approval, and a new state every reader must handle.
**Option B — derive a "scheduled, not yet run" line on the decision screen.** Consequence: the surface
states the fact without touching the enum or its readers.
**Recommendation.** B — derived, no vocabulary change, no new state to leak into stored data.
**Lands in.** `RaceDecision.vue` (a line), no enum change.
**Blocks.** B-3.4.

---

#### B-0.5 — Team stat grades: add the column, or keep the named absence? (R4-12)

**Source.** Walk-4 U-02 documents team stat grades A/A/A/S/A having no input (§1.8).
**Current state.** `TeamPanelBuilder::teamSection()` hard-codes `grade => null` for every stat
(`app/Services/Career/TeamPanelBuilder.php:501-506`); `UnityCupPanel.vue:210-225` prints the absence.
**Option A — add a `team_stat_grades` column (or table).** Consequence: the run records the five letters;
a migration, ADR, and digest line.
**Option B — keep the named absence.** Consequence: the panel keeps stating the gap honestly; no schema.
**Recommendation.** A — the grades are source-cited and load-bearing (they explain the Guts Level 5 in
§2.6).
**Lands in.** a new ADR, a migration, `TeamPanelBuilder`, `UnityCupEditor.vue`.
**Blocks.** B-3.5.

---

#### B-0.6 — Current Spirit gauge: add the column, or keep the named absence? (R4-14)

**Source.** Walk-4 U-04 documents the Current Spirit gauge having no input (§1.8).
**Current state.** `UnityCupPanel.vue:231-241` mounts `ResourceMeter` with `value: null, max: null` and
the absence sentence; no column or payload reader.
**Option A — add a `current_spirit` column.** Consequence: the gauge reads a recorded value; a migration,
ADR, and digest line.
**Option B — keep the named absence.** Consequence: the gauge stays empty with its reason; no schema.
**Recommendation.** A — it is the one reading that would let burst readiness be computed.
**Lands in.** a new ADR, a migration, `UnityCupPanel.vue`, a writer.
**Blocks.** B-3.6.

---

#### B-0.7 — Skill levels: add the column/table, or keep acquisition-only? (R4-16)

**Source.** Walk-4 U-06 documents skill levels (unique Lvl 3, hint levels) having no field (§1.3, §2.1).
**Current state.** `SkillsPlannerController` carries acquisition state only
(`app/Http/Controllers/Career/SkillsPlannerController.php:216-228`); no level column.
**Option A — add a `skill_levels` table or per-run level column.** Consequence: the planner records a
level per acquired skill; a migration, ADR, and digest line.
**Option B — keep acquisition-only.** Consequence: levels stay unrecorded and the screen states it; no
schema.
**Recommendation.** A — the source's hint-discount ladder (§2.1) is keyed by level, so levels are the
missing input, not a nicety.
**Lands in.** a new ADR, a migration, `SkillsPlannerController`, `SkillsPlanner.vue`.
**Blocks.** B-3.7.

---

#### B-0.8 — Borrowed-card owner: add the identity column, or "will not add"? (R4-18)

**Source.** Walk-4 U-08 documents the friend card's owner account being unrepresentable beyond `Rented`
(§1.4).
**Current state.** `deck_slots.ownership` (migration `2026_10_09_120000_add_ownership_to_deck_slots_table.php:24`)
records `OWNED`/`RENTED`/nothing; no account or identity column.
**Option A — add an owner-identity column.** Consequence: the borrowed card names whose account it came
from; a migration and ADR, and a field a loopback tool has no second account to fill.
**Option B — "will not add".** Consequence: the `Rented` flag stays the whole record; the absence is a
ruling, not an oversight.
**Recommendation.** B — a single-Trainer, loopback-only tool has no other account identity to record.
**Lands in.** nothing (a "will not add" note), or a new ADR if A.
**Blocks.** B-3.8.

---

## remaining-work-rulings-owed.md

> **Consolidated 2026-10-26 (Round 21, owner-ordered).** Source: `docs/proposals/remaining-work-rulings-owed.md` (305 lines), <!-- census: quoted-original -->
> reproduced verbatim below with headings demoted one level. The original was deleted from the tree
> after every non-blank line was found here (217/217); the fold, the pointers repointed
> and the disposition of the originals are recorded in `docs/research-scratch/INDEX.md`
> section `### Round 21`. Read the sibling sections in these masters rather than looking for the
> old paths: the group-ab and remaining-work plans are in `PROCESS-PLANS.md`, the two rulings-owed
> briefs in `PLANS-AND-BRIEFS.md`, the three audits in `AUDIT-AND-VERIFICATION.md`, and the two root skill-registry files in
> `GOVERNANCE.md`.

## Remaining work: the eight rulings owed

Recorded 2026-10-24 at `499e0105`. This document presents every ruling the remaining
roadmap depends on, in dependency order, so the owner can rule in one sitting. It decides
nothing: each entry names the options, the evidence, a recommendation, and the observation
that would overturn the recommendation.

Sources of record: `AGENTS.md` §2 (precedence), `DESIGN.md`, `docs/adr/0029` (Proposed),
`SCREEN_SPEC.md` §8, `KNOWN-ISSUES.md` KI-115..KI-130, and this pass's three
investigations (§R1, §R5b, §R6 below).

A ruling is recorded as a dated note beside the KI or document it governs. No row here
waives a gate: a ruling that changes a design decision is still a schema or DESIGN.md
change with its own travel (migration, ADR, digest, PRD citation per §11).

---

### R4 — Accept ADR-0029 (per-run measurement columns)?

**Blocks.** Slices 1.1, 1.3, 1.6, 1.7, 1.8 directly; 1.2 chains on 1.1. Five code slices.

**Source.** `docs/adr/0029-per-run-measurement-columns.md` (drafted at `9c232066`,
`Status: Proposed`; contract `decision: proposed`); KIs KI-116..KI-120, KI-124..KI-126.

**Current state.** The ADR authorises eight per-run storages and builds none:
`deck_slots.card_level` (numerator only), `deck_slots.reached_effects`, `turn_entries`
banner + card avatars, `training_runs.team_member_count`, `team_roster`,
`team_roster_member_skill`, `training_runs.team_stat_grades`, `training_runs.current_spirit`,
`skill_levels`.

**Evidence.** Two rows need a separate answer before they can be migrated, and the ADR
says so itself: `A-0.13` and `A-0.14` are marked `needs-PRD-citation`
(`docs/adr/0029-per-run-measurement-columns.md:43-44`, decision D6 at `:59`, and `:88`),
because `PRD.md` has no story for a stored roster or guest skills. `A-0.5` collides with
US-12's acceptance sentence (`PRD.md:103`, restated at `:53` and `:431`) and is R7.

**Option A — accept as drafted.** Consequence: slices 1.1, 1.2, 1.3, 1.6, 1.7, 1.8 open
once their PRD citation is settled; 1.4/1.5 wait on R8. Cost: five to six slices, one
migration each, digest + PRD citation per §11.

**Option B — accept the six rows that have a PRD anchor.** Consequence: 1.1, 1.2, 1.3,
1.6, 1.7, 1.8 open; `A-0.13`/`A-0.14` stay `blocked: PRD`. Cost: same minus the two
tables, and the 20/20 counter and guest skills remain absent.

**Option C — decline.** Consequence: all eight stay `blocked: ADR`, filed as KIs. The
audit's `needs-schema` rows close as "not stored, by ruling". Cost: none; the source's
facts remain unrecordable.

**Recommendation.** A, with R7 and R8 answered in the same sitting. Those two narrow
specific rows; they do not change the ADR's shape.

**What would settle it.** An owner statement that per-run measurement facts are out of
product scope (C), or that a stored team roster is not wanted (B).

---

### R7 — US-12: narrow its acceptance sentence, or refuse A-0.5?

**Blocks.** Slice 1.1 (`A-0.5`), and through it 1.2.

**Source.** `PRD.md:103` (US-12 acceptance: a run records which six cards it was equipped
with), `PRD.md:53` and `:431` ("no collection, no levels, no limit breaks, no Unique Perk
state"), `docs/adr/0029-per-run-measurement-columns.md:41` (`A-0.5` row, cited to
`FR-C-6, US-12 (travel note)`).

**Current state.** `deck_slots` stores `support_card_id`, `slot_position` and `ownership`;
it has no level. A run can say which card, never what the card was at.

**Option A — dated travel note on US-12.** The sentence's "stored anywhere" narrows to
"account-level collection", and the per-run snapshot is authorised by ADR-0029. Consequence:
1.1 stores `card_level` (numerator only, per ADR-0029 D1) and 1.2 stores reached effects.
Cost: one PRD note plus the ADR.

**Option B — refuse A-0.5.** US-12 stands as written; ADR-0014's collection ban is read as
covering the per-run copy too. Consequence: 1.1 and 1.2 close as declined; the deck analysis
keeps catalog maxima for every run. Cost: none.

**Option C — store the level without citing US-12.** Consequence: §11 is violated, because
every schema change carries a PRD citation. Not viable as written; listed so the refusal is
explicit.

**Recommendation.** A. The per-run reading ("what this run's equipped card is at") is what
the audit asked for, and it creates no collection entity that ADR-0014 declined.

**What would settle it.** An owner reading of US-12 as deliberate about the run, not the
account; or an ADR-0014 erratum extending the collection veto to per-run copies.

---

### R8 — A-0.13 / A-0.14: write the PRD story, or refuse?

**Blocks.** Slices 1.4 (stored roster, 20/20 counter) and 1.5 (guest skills).

**Source.** `docs/adr/0029-per-run-measurement-columns.md:43-44,59,88`; `PRD.md` §6
non-goals; KI-125, KI-126.

**Current state.** No scenario data stores a team roster; the 20/20 counter in the client
counts a roster the tool does not hold (`TeamPanelBuilder` derives six deck cards, not
eighteen members). Guest skill lists are neither stored nor derivable.

**Option A — one PRD story for the team roster.** Covers both rows: the roster and its
per-member skills. Consequence: 1.4 and 1.5 open; the counter and the roster editor ship.
Cost: one PRD story, two migrations, two ADR rows already drafted.

**Option B — refuse both.** The counter keeps its named absence and roster skills stay
unrecorded. Consequence: KIs close as declined. Cost: none.

**Option C — roster only, no skills.** Consequence: 1.4 ships, 1.5 stays blocked. Cost:
one PRD story, one migration; guest skills remain absent.

**Recommendation.** A if KI-130's dead-registration ruling composes the team panels; B
otherwise. A roster editor with no composed surface is storage without a screen.

**What would settle it.** The KI-130 composition ruling (compose or retire the five
registrations). It is the same surface.

---

### R3 — The Scenario-region focusable ruling: amend, restore, or retire?

**Blocks.** Slice 2.6; closes the last two reds in `tests/browser/unity-cup-panel.spec.ts`
(`:134`, `:229`).

**Source.** `e5768c98` commit body (2026-10-11 ruling: "no disclosure and no focus stop
anywhere in the Scenario region"; lifting needs "the owner's written overrule");
`DESIGN.md:768-777` (§5(a), amended 2026-10-17, owner instruction of the same day);
`2b82f043` (implemented §5(a) at `resources/js/components/scenario/ScenarioPanel.vue:243`);
`tests/Feature/UnityCupSurfacesTest.php:284` (executable form scans only the two Unity Cup
panel files).

**Current state.** The Scenario region renders 13 focusable summaries and one `<details>`,
all from `AbsenceValue` at `ScenarioPanel.vue:243` and the scenario panels that compose it.
The spec still asserts zero for the whole region; `UnityCupSurfacesTest` is green because
it never scans the wrapper.

**Option A — amend the ruling.** Add one sentence to `DESIGN.md` §5(a): the disclosure
channel is permitted in the Scenario region. Consequence: spec narrows to the two panels in
the same commit. Cost: one DESIGN.md amendment (owner), one spec line each, no component
change.

**Option B — restore the 2026-10-11 ruling.** `AbsenceValue` gains a static mode and every
in-region caller migrates (`ScenarioPanel.vue:243`, `TrackblazerRoutePlanner.vue:185`,
`UraPreFinalReview.vue:106,136,161`). Consequence: `AbsenceChannelTest`'s ratchet and the
2026-10-17 amendment both need a dated exception. Cost: one new prop, five call sites, one
DESIGN.md note, one test's expectations.

**Option C — retire the ruling.** Spec narrows to the panels; a dated note says the
region-wide guard is retired. Consequence: same end state as A without the DESIGN.md
sentence. Cost: one spec change, one note; leaves §5(a)'s scope unstated for this region.

**Recommendation.** A. It is the only option that makes the written rule and the tree agree
without deleting either.

**What would settle it.** An owner statement that the 2026-10-17 amendment was meant only
for new surfaces, which makes B mandatory.

---

### R1 — S2b payload shape: does a ceilings-only save store a target?

**Blocks.** Slice 2.4; also covers R4-31's symptom.

**Source.** `app/Http/Requests/StoreBuildTargetRequest.php:71-82` (all-or-nothing
`$presence`), `:160-170` (`isEmptyTarget()`), `:186-217` (`payload()`),
`resources/js/pages/Career/BuildTarget.vue:126,175`.

**Evidence (this pass, code-proven).** `isEmptyTarget()` checks only `purpose`, `distance`,
`surface`, `style` and the five target values; ceilings and `skill_priorities` are ignored.
A save carrying ceilings plus priorities therefore validates (ceilings have their own
rules) and returns `payload() === null`: the priorities are never written. The form echoes
whatever the stored target holds (`BuildTarget.vue:126`) and always posts the key
(`:175`), and the request requires it present (`:81`). So the observed loss is **silent**,
not a 422: the first save stores no target, and the next save posts an empty list. R4-31
and R4-03 are one mechanism.

**Option A — one partial payload (S2b B1).** `payload()` returns a partial object when
ceilings or priorities were entered; Preflight renders `Partial` instead of `Missing`; the
cockpit renders what is present. Consequence: 2.4 opens and R4-31 closes with it. Cost:
three controllers/services, three feature cases, one browser case.

**Option B — two draft keys.** `build_target` stays all-or-nothing; `caps_and_priorities`
becomes independent. Consequence: same user outcome, two shapes to read. Cost: every
downstream consumer learns which key carries what.

**Option C — copy-only refusal.** `Save target` refuses without saying why; Preflight's
`Missing` is correct. Consequence: no code change; entered ceilings still discarded on run
creation. Cost: none, and the loss remains.

**Recommendation.** A.

**What would settle it.** An owner statement that a target without a purpose is genuinely
not a target (C), which would make the loss correct rather than a defect.

---

### R2 — S11: one copy sentence naming where Team Rank is recorded?

**Blocks.** Slice 2.5. One sentence.

**Source.** R4-13 (`docs/audits/rice-shower-unity-cup-round4-reconciliation.md`);
`resources/js/components/career/UnityCupEditor.vue` help text; `TrainingRun` records rank
through a turn's team-race event.

**Current state.** The editor offers no rank input and does not say where a rank comes
from, so a Trainer who reads the client's rank has no path from this panel.

**Option A — copy-only.** One sentence: "Team Rank is recorded through a turn's team-race
event, not through this editor." Consequence: 2.5 ships with one assertion. Cost: one line,
one test.

**Option B — copy plus a shortcut.** A link to the Events screen or the turn form, with the
rank field pre-scoped. Consequence: a navigation change and a new control. Cost: one link,
one browser case, and a decision about which surface owns rank entry.

**Option C — leave it.** The KI stays open.

**Recommendation.** A. The rank already has an owner; the defect is that the editor is
silent about it.

**What would settle it.** An owner preference for entering rank from the editor itself,
which would make B insufficient and require a real input.

---

### R5b — `.card + .card`: fix the spec measurement, exempt grids, or accept?

**Blocks.** Slice 3.1 (one pre-existing red in `career-deck-select.spec.ts`).

**Source.** `resources/css/app.css:678-681` (`.card + .card { margin-top: 14px }`, ported
from `shell.css`); `tests/browser/career-deck-select.spec.ts:23-36` (counts children whose
`getBoundingClientRect().top` equals the first child's).

**Evidence (this pass).** The deck grid computes three columns at desktop. The margin rule
applies to every sibling `.card` inside the grid, so children 2..n sit 14px lower than the
first even though they share a row; the spec's equal-top test then reports one per row.
The rule is a stacked-card house rule, not a grid rule.

**Option A — fix the spec.** Count row membership by left offset or by the computed
`gridTemplateColumns` tracks. Consequence: 3.1 closes with a spec-only diff. Cost: one
assertion.

**Option B — exempt grids from the rule.** Scope the margin to non-grid parents. Consequence:
the visual gap between stacked cards is unchanged and grids no longer carry a phantom
margin. Cost: a CSS selector change; the stylesheet is a design surface, so this is Tier C
and needs the same ruling.

**Option C — accept as a KI.** Record the assertion as a known red.

**Recommendation.** A. The rule is intentional and documented as a stacked-card rule; the
measurement is what is wrong.

**What would settle it.** A visual complaint about the extra 14px inside grids, which would
make B the honest fix.

---

### R6 — Inertia DevTools: disable for the browser role, or mitigate?

**Blocks.** Slice 3.3.

**Source.** `vendor/inertiajs/inertia-laravel/config/inertia.php:170-184`;
`vendor/inertiajs/inertia-laravel/src/DevTools/DevTools.php:16-25`;
`storage/inertia-devtools/` (exists).

**Evidence (this pass).** The provider ships inside **`inertiajs/inertia-laravel` 3.5.1**
(auto-discovered via `extra.laravel.providers`), not a separate package. `config/inertia.php`
is **not published**, but the package config merges, and the gate is
`inertia.devtools.enabled`, read from **`INERTIA_DEVTOOLS_ENABLED`**; when unset it falls
back to `app()->environment('local')`. Storage path and TTL are config keys
(`inertia.devtools.path`, `ttl`, `prune_interval`).

**Option A — env-gated disable.** Set `INERTIA_DEVTOOLS_ENABLED=false` in the browser
harness environment. Consequence: 3.3 closes with no code. Cost: one env line in the
harness (and `.env.example` documentation).

**Option B — publish the config and set the flag.** `php artisan vendor:publish` plus
`devtools.enabled => false` for the harness. Consequence: same behaviour, a tracked config.
Cost: one config file; more surface than A.

**Option C — mitigate on recurrence.** Keep recording and delete `storage/inertia-devtools`
when it grows. Consequence: the `/up` probe can still time out during a long browser run.
Cost: an operational note only.

**Recommendation.** A. The package already reads an env var; nothing needs publishing.

**What would settle it.** A need to inspect recorded entries while debugging the browser
role, which would make B (config with an override) preferable to a hard env switch.

---

### Ruling record

| # | Question | Recommendation | Recorded? |
|---|---|---|---|
| R4 | Accept ADR-0029? | A: accept as drafted | no |
| R7 | Narrow US-12 or refuse A-0.5? | A: dated travel note | no |
| R8 | PRD story for the roster, or refuse? | A if KI-130 composes; else B | no |
| R3 | Amend, restore, or retire the region rule? | A: amend §5(a) | no |
| R1 | One partial payload, two keys, or refuse? | A: one partial payload | no |
| R2 | Copy-only, copy plus shortcut, or leave? | A: copy-only | no |
| R5b | Fix the spec, exempt grids, or accept? | A: fix the spec | no |
| R6 | Disable by env, publish config, or mitigate? | A: env gate | no |

Each ruling is recorded as a dated note beside its governing KI or document; this table is
then updated with the date, not rewritten.
