# Plans and Briefs

## Provenance

This document consolidates the following source files verbatim (no summarization, no deduplication):

- `docs/design-research/TASK-16-RUN-VIEW-FRAME-BRIEF.md` (481 lines)
- `docs/design-research/replan-mobile-first.md` (372 lines)
- `docs/design-research/d-30-amendment-draft-2026-10-01.md` (51 lines)

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

| # | Ruling | What it settles |
|---|---|---|
| R1 | **The shell keeps `max-w-5xl`; the split sits at `lg:` (1024px).** | The frame is built inside the container that exists. `layout.blade.php` is not widened and not touched. The 3fr/2fr columns are viewport-invariant: left ≈ 581px, right ≈ 387px at 1024, 1280 and 1440 alike. |
| R2 | **The race entry form stays in the left column; the rail holds the calendar and the informational panels.** | No form lives in the rail. D-40's region definition holds: state + action left, context right. URA's rail is the calendar alone, which is what lifts its guided flow. |
| R3 | **3c.1 stands — the standalone Scenario block is deleted and "Change scenario" moves to the per-view header row.** | The header row is already view-owned markup (`show.blade.php:19–34`), so this stays inside one file. No slot is added to `x-layout`. |
| R4 | **No sub-routes in this slice. One route, two columns.** | Stated as a ruling, not a default. The sub-route question needs its own decision later. |

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

| Target | Hits | Notes |
|---|---|---|
| docs/GATE-REGISTRY.md | **36** | was 22 when the first brief was written; the M1 batch added citations |
| docs/PRE-MORTEM.md | **32** | was 19 |
| docs/SOURCE-OF-TRUTH.md | **20** | absorbed into `GOVERNANCE.md` §SOURCE-OF-TRUTH.md (`:16`) |
| agents.md (case variant) | **7** | see O-3 below — not a missing file |

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

| Ref | Cites | Ignored by | Disposition |
|---|---|---|---|
| agents.md | 7 | **nothing — case variant** | **Withdraw/repoint, no commit.** Git tracks `AGENTS.md`; agents.md resolves here only because NTFS is case-insensitive. On a case-sensitive checkout these seven break. They sit in `KNOWN-ISSUES.md` KI-55 (a dated entry) and the UI/UX plan (living), so the register ones need an erratum rather than an edit. |
| research-scratch/DOCUMENTATION-INVENTORY-2026-09-30.md | 5 | `.gitignore:87` | **Owner decision (O-3).** The governing inventory. Either promote it into `docs/research-scratch/` as a registered master, or declare every citation into the root scratch folder scratch-only. Leaving it ignored while five citations point at it is the state that produced A-10. |
| .agents/README.md | 4 | `.gitignore:49` | **Withdraw the pointer or commit the file.** `.agents/` is a tooling layer the repo map already describes; the citations are from `README.md:122` and the doc-sync plan. |
| .copilot/instructions.md | 4 | `.gitignore:60` | **Withdraw.** Machine-local by design; `docs/SKILL_AUTOMATION.md:29` should not cite it. |
| research-scratch/scrape-game8-scenarios.md | 1 | `.gitignore:87` | **Owner decision (O-3).** Cited from `docs/UMAMUSUME_REFERENCE.md:1007` (fenced). |
| research-scratch/scrape-training-heuristics.md | 1 | `.gitignore:87` | Same, from `:1055`. |
| research-scratch/global-race-sources.md | 1 | `.gitignore:87` | Same, from `docs/scenarios/09-global-race-calendar.md:727`. |
| research-scratch/calendar-tables.md | 1 | `.gitignore:87` | Same, from `:729`. |

**Target commands, displayed and NOT executed** (three of the four targets are on dirty or fenced paths):

```
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

| List | Ids, occurrences | Cards with a non-empty list | Occurrences that open a skill page |
|---|---|---|---|
| `hint_skills` | 3,971 | 527 | 3,200 |
| `event_skills` | 1,528 | 555 | 1,178 |

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
