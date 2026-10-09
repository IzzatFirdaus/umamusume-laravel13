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
  (`docs/adr/0021-sourced-character-artwork.md` for the mirror, `DESIGN.md` §4.7 for the slot rules,
  `PRD.md` OQ-6 for what is still open), so the plan itself is the only piece with no master. It was
  fully executed: the progress table records all ten tasks, Task 9 as blocked with no placeable surface
  and Task 4, 5, 6 and 8 as superseded in part by the Inertia port. The original was tracked, so its
  deletion after this fold is recoverable from git history.

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
  `DESIGN.md`, `docs/design-research/CONSTRAINTS.md`, `tests/Feature/RunViewFrameTest.php`,
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

`docs/design-research/CONSTRAINTS.md` D-40 currently ends with:

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

**DRAFT, for the owner's pen. `docs/design-research/CONSTRAINTS.md` was not edited by this pass; the paste-ready text is §2 below.**
Written 2026-10-01 against HEAD `4e17997`, read-only. This one untracked file was created and no other file was touched.

#### 1. Method note

**Read.** `docs/design-research/CONSTRAINTS.md` §5 (D-30 at `:138`, plus D-31 through D-37 and the D-40 and D-230 amendment paragraphs for voice); `docs/adr/0012-card-detail-fields-and-images.md` (`:7`, `:73`, `:142-145`, `:157-159`, `:243-244`); `docs/adr/0008-character-card-catalog-layer.md` (`:236`, `:439`); `KNOWN-ISSUES.md` (status block `:10-17`, KI-33 `:1516-1573`, KI-35 `:1625-1644`); `docs/design-research/skills-section-phase-b2-2026-10-01.md` (§4.7 `:554-583`, §4.8 `:585-620`, §9.5 `:1089-1109`, §6 items 12-13 at `:898-905`, §10 at `:1136`); `docs/design-research/skills-mechanics-audit-verification-2026-10-01.md` §3.3 (`:82-96`); root `DESIGN.md` §4.2 (`:226-268`, page section 2 at `:246-247`); `docs/design-research/DESIGN.md` (section list only, to keep the two `DESIGN.md` files apart); `PRD.md` (A-5 at `:46`); `app/Models/Umamusume.php` (`:31-40`, `:49`), `app/Models/CharacterCard.php` (`:35-36`, `:40`, `:66-67`), `app/Services/DataPipeline/Parsers/GametoraCharacterParser.php` (`:34-45`, `:83`), `app/Services/DataPipeline/Parsers/GametoraCharacterCardParser.php` (`:92-93`); migrations `2026_09_30_073029_...:41-42` and `2026_09_27_090000_...:16-20`.

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
the census's 505 dead links (22 `docs/GATE-REGISTRY.md` + 19 `docs/PRE-MORTEM.md`) wait on it.

**What is actually missing.** Both paths are dead on a fresh clone: `AGENTS.md:178` ranks
`docs/GATE-REGISTRY.md` second of five authorities, `AGENTS.md:5` names `docs/PRE-MORTEM.md` as the risk
record, `AGENTS.md:94` says "see GATE-REGISTRY C-5", and `README.md:18` maps `docs/PRE-MORTEM.md`. Neither
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

Enumerated with a read-only script (`.scratch-uma/untracked-citations.py`). "UNTRACKED" means the census finds
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
```text

**What is not at risk:** none of the four `research-scratch/*.md` bodies is cited as an instruction a reader
follows; three are provenance citations inside a dated record or a fenced file.

---

## Owner gate O-2 resolved 2026-10-03: repoint, not restore

The owner chose the repoint path: the citations naming `docs/GATE-REGISTRY.md` and `docs/PRE-MORTEM.md` are updated to point at `docs/research-scratch/GOVERNANCE.md` and its absorbed-source sections. The consolidation's absorption stands; the two files are not restored. Two consequences carried forward: `AGENTS.md:178`'s precedence chain no longer names a standalone #2 authority, and any future reader who follows a `GATE-REGISTRY.md` citation arrives inside `GOVERNANCE.md` rather than at a dedicated file.

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

Measured by the audit, not by me: after 1,910 rows landed in `.scratch-uma/skills-c5.sqlite`,
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
`.scratch-uma/ki38-probe.php`): exactly one row, `rarity 3`, `global_release_date 2026-06-18`,
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

Still open on KI-55, and small: the inventory file names `docs/GATE-REGISTRY.md` or `docs/PRE-MORTEM.md` as
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
conformance and the UX laws named in `docs/proposals/frontend-development-plan.md`
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
```text

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
```text

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
```text

Wire the slot in the existing header block of both, alongside the title/rarity:

```blade
<x-support-thumb
    :support-id="$card->support_id"
    size-class="size-12"
    :name="$card->displayName()"
    :route-args="['card' => $card->support_id]"
/>
```text

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

- Modify: `docs/proposals/design-2.0.md` — change log row (the prose is already in §42 + §45a, land a dated row).
- Modify: `docs/proposals/screen-spec-2.0.md` — change log row for §34 footnote + §35.
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
| 9      | Run Create / Legacy Select — `size-10`                                                                        | `[x]` **not implemented**   | —                      | **Blocked, no slice committed.** No placeable surface: the view's picker is a native `<select>` (an `<img>` cannot render inside `<option>`) plus a client-rendered combobox listbox, and on `trainer-desk-2.0` both the view and `trainee-combobox.ts` are deleted by the port. Recorded as unplaceable in `PRD.md` OQ-6, `SCREEN_SPEC.md` §7-16 and `design-2.0` §45a.   |
| 10     | Cross-doc change-log rows + gate run-down                                                                     | `[x]`                       | see below              | `DESIGN.md` §3 count, §4.7 status and geometry, `SCREEN_SPEC.md` §7-16 superseded-in-part, `PRD.md` OQ-6 narrowed, `design-2.0` §45a shipped-state note; full gate block in the Slice Log                                                                                                                                                                                  |

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
```text

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
   them with zero call sites remaining, which is `docs/proposals/frontend-development-plan.md` §5.1
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
   unplaceable in `PRD.md` OQ-6, `SCREEN_SPEC.md` §7-16 and `design-2.0` §45a.

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

Slice row: `docs/proposals/frontend-development-plan.md` line 195 (E4, SCREEN-016, depends on E1,
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
- `Twinkle Star Climax` exclusion: `screen-spec-2.0.md` line 138 conflict row 31;
  `docs/UMAMUSUME_REFERENCE.md` §7 conflict row 31; the named-absence `title` cites
  the conflict row.
- Purchase validation rules and held-copies cap: `app/Http/Requests/StoreShopPurchaseRequest.php`
  docblock + body.
- Trust vocabulary: `design-2.0.md` §49 (`Confirmed / Calculated / Estimated / Unknown`)
  and `ADR-0020` §2.
- 44px / `min-h-11` target rule + WCAG 2.4.11: `frontend-development-plan.md` §12.

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
`docs/Trainer-Docs-2.0-E-Prompts.md` §"PROMPT E6". Three of its premises do not hold on
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
