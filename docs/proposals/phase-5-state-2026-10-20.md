# Phase 5 state against the Trainer Desk 2.0 remaining-implementation plan — 2026-10-20

This document records what Phase 5 of `Trainer Desk 2.0 — Remaining implementation.txt` actually
holds in the tree, slice by slice. It is written against the tree, not against the plan's wording:
Rule 25 of this arc — *the plan is descriptive, not prescriptive; the tree wins on every
disagreement* — governs every line below. Where the plan's claim and the tree disagree, the tree
is quoted and the disagreement is named.

**Tree state at writing.** `master` = `ce89b2be`, four commits ahead of `origin/master`
(`95896ec1`). The working tree carries only the peer main agent's Career edits
(`app/Http/Controllers/Career/ResultController.php`, `resources/js/pages/Career/Result.vue`,
`resources/js/pages/Career/TrainingDetail.vue`, `tests/browser/career-result.spec.ts`,
`tests/browser/career-training-detail.spec.ts`) plus the operator's untracked plan file. Nothing in
this document was measured against a peer-dirty file, and no peer-dirty file was touched.

**Attribution.** Rule 1 of this arc: *attribution from the transcript, not `git log` metadata*. The
side-agent plan names `a39a3fe5`, `c61ef815`, `0742ece3` and `d01288d2` as the peer's work. This
session's transcript shows the first three were landed by the main agent in this same arc, as
slices 5.1, 5.2 and 5.3 respectively, and that `d01288d2` was already on `master` when the arc
began. Slice 5.4 landed as `ce89b2be` after the side-agent plan was written, so the plan's view of
5.4 is one commit stale. The facts below are the facts; the attribution labels are the
transcript's.

---

## Slice 5.1 — Race database: filters, sort, `Pagination` unification

**The plan's claims.** Sticky filter bar; sortable headers; "Showing A–B of N" via `Pagination`;
drop the all-N/A Gate column; unify the two `Pagination` components and delete
`components/Pagination.vue`.

**Measured.**

- `resources/js/pages/Database/Races.vue:16` — `import Pagination from '../../components/ui/Pagination.vue';`
  — the canonical primitive, not the duplicate.
- `Races.vue:179-187` — the pager is bound to the stored range:
  `:page="props.slots.current_page"`, `:pages="props.slots.last_page"`,
  `:from="props.slots.from ?? 0"`, `:to="props.slots.to ?? 0"`, `:total="props.slots.total"`,
  `noun="slots"`, `@navigate="goToPage"`. The canonical `Pagination` renders
  "Showing X to Y of Z noun" from exactly these, so the range summary is present.
- `app/Http/Controllers/DatabaseController.php` — no sort whitelist exists for the races area, and
  the method's own docblock refuses a filter facet. The Gate column is derived from three booleans
  on `race_catalog_slots`; the plan's `SELECT COUNT(*) FROM races WHERE gate IS NOT NULL` names a
  table and a column that do not exist.

**Verdict: already-satisfied (range summary), needs-re-scope (sort), dropped by ruling (filter,
Gate column, `Pagination` deletion).**

- *Range summary* — landed in `a39a3fe5`; nothing left to do. No commit.
- *Sortable headers* — a controller change (the 1c precedent). The plan's own instruction is
  *stop, report*. Owed to the operator, not to this agent.
- *Sticky filter bar* — the filter is deliberately refused by the controller's own comment, and
  `main`'s container makes a page-level sticky bar a no-op. Not attempted.
- *Gate column* — the column carries data. Dropping it would be the plan's own stop condition.
  Not attempted.
- *`Pagination` deletion* — `components/Pagination.vue` still has live consumers, and deleting a
  module with live importers breaks the tree. The blast-radius clause applies: it is not deleted.

---

## Slice 5.2 — Skills search + detail

**The plan's claims.** Index sticky filter bar; "Page X of Y"; footnote; dash for missing SP;
detail condition in wrapped mono labelled "Raw engine data"; effect chips named in words where a
label exists; one provenance disclosure.

**Measured** — `resources/js/components/skills/SkillCatalog.vue` and
`resources/js/pages/Skills/Show.vue`:

- `SkillCatalog.vue:11` — `import Pagination from '../ui/Pagination.vue';` — the canonical pager,
  bound at `:224`.
- `SkillCatalog.vue:12,210` — `ProvenanceBadge` imported and rendered.
- `SkillCatalog.vue:191` — `<span v-if="skill.name_ja !== null && skill.name_ja !== skill.name" lang="ja" …>` —
  the Japanese name carries its language.
- No `sticky` in the component.
- The index is a card list, not a table — which is the correct host for a per-skill row that links
  to two pages.
- The detail's raw block is **one** `<details>` inside `section[aria-labelledby="mechanics"]`, its
  summary naming the source's own expressions and codes, landed in `c61ef815` with
  `tests/browser/skills.spec.ts` red at `HEAD` before it and green after.
- The effect chips name the human label where one exists: `effect.name ?? '[Unverified] effect N'`
  — the same shape in `SkillCatalog.vue:250` and in the support-card catalogue.

**Verdict: already-satisfied.** The plan's remaining asks were either present before the arc
(`ProvenanceBadge`, `lang="ja"`, the canonical pager) or landed by `c61ef815` (the raw-engine
disclosure and the named effects). No commit.

---

## Slice 5.3 — Support cards

**The plan's claims.** Index `DataTable` (Card, Rarity, Type, Friendship %, Training effectiveness
%, Availability); one chip with `aria-label="SSR, 3 star"`; remove the mono stat string; detail
keeps effect chips + one provenance disclosure.

**Measured** — `resources/js/components/support/SupportCardCatalog.vue` and
`resources/js/pages/SupportCards/Show.vue`:

- `SupportCardCatalog.vue:12,234` — `RarityChip` imported and rendered with the rarity's own label
  and star run.
- `SupportCardCatalog.vue:13` — `ProvenanceBadge` imported.
- `SupportCardCatalog.vue:11,256` — the canonical `ui/Pagination.vue`.
- `SupportCardCatalog.vue:250` — effects named in words, with the source's own id when the
  dictionary has no row.
- No `DataTable` and no `<table>` in the component: the card is the right host for a row that
  carries a portrait, a rarity, two type words and a provenance badge.
- The card detail's provenance is one disclosure whose summary names the state, landed in
  `0742ece3`.

**Verdict: already-satisfied except the index `DataTable` claim, which is a preference and is
dropped by ruling** (see the 5.5 ruling on `DataTable` migration below).

*Residual, not owed by this agent:* the rarity chip's accessible name is the rarity's own label
("SSR") and the star run beside it is not part of that name, so the plan's
`aria-label="SSR, 3 star"` tuple is not what the tree names. The chip's own docblock states the
contract it holds — the glyph run is the readout, `role="img"` + `aria-label` turns the glyphs into
a name — and `tests/browser/catalog.spec.ts:29` pins that as a named glyph run. Changing the name
to a tuple is a copy decision with a spec behind it, not a gap this agent closes.

---

## Slice 5.4 — Trainees catalog (carries Decision 9)

**The plan's claims.** Portrait rule (Decision 9 — Option A: both index and detail show the
trainee's active form, or the first if none is active); collapse costume forms under each trainee
as `<details>`; debut date once; group the detail's aptitudes as Surface / Distance / Style;
collapse empty runs and aliases to one-line rows; source details in one disclosure; `lang="ja"` on
Japanese strings.

**Measured.**

- `components/catalog/TraineeCatalog.vue:200` — `<details v-if="trainee.cards.length > 0">` with
  a `<summary>` at `:205` — the costume forms are already collapsed, landed in `d01288d2`.
- `components/AptitudeGrid.vue:2-3` — the letters are grouped in the export's own order: surface,
  the four distances, the four running styles, and each group carries its own heading — landed in
  `d01288d2` with `tests/browser/catalog-detail.spec.ts` asserting the three `h3` headings.
- `app/Models/Umamusume.php` — `defaultForm()` returns the card flagged `is_debut_form` per A-6,
  or her first form when no card carries the flag. `CatalogController`'s index and detail both
  read it, so the two screens cannot disagree about which card her frame is. Landed in
  `ce89b2be`, with `tests/Feature/CatalogDetailArtworkSlotTest.php` pinning that the detail and the
  index name the same card.
- `Catalog/Show.vue` — the Japanese name carries `lang="ja"` in both places it appears.
- `DESIGN.md:541` and `SCREEN_SPEC.md:787` both name the status dl's **JP debut** and **Global
  debut** rows as the screen's fields, while the "Release date" row above is built by the
  controller's `releaseDate()` helper from the *same two columns*
  (`global_debut_date ?? jp_debut_date`).

**Verdict: satisfied, with two sub-claims left to the owner and one sweep verdict corrected.**

- *Portrait rule* — satisfied. Note the correction: the side-agent plan's sweep recorded
  Decision 9 as "already satisfied — do not change portrait resolution". The tree shows the
  detail's default was `cards->first()`, which was right only because the card scope happens to
  order `is_debut_form` first — the rule was an accident of the ordering rather than a statement
  of it. `ce89b2be` states the rule in one place and pins it with a test.
- *Costume forms collapsed, aptitudes grouped* — already satisfied by `d01288d2`.
- *Debut date once* — needs-re-scope, and it is a copy decision. The "Release date" row and the
  two debut rows read the same two stored columns in two shapes. Collapsing them means choosing
  which shape survives, and `DESIGN.md:541` and `SCREEN_SPEC.md:787` both name the split rows as
  the screen's fields. The owner decides; this agent does not delete a stored fact.
- *Empty runs and aliases as one-line rows* — needs-re-scope. The runs section carries the page's
  primary action ("New career"), and the aliases heading is one of the eight the binding-order case
  pins.
- *Source details in one disclosure* — **the sweep verdict is corrected here.** The side-agent
  sweep recorded this as "dropped by D-33", reasoning that `FormDetail.vue`'s per-form source and
  `Show.vue`'s fetch history are different kinds of information. D-33 itself
  (`docs/research-scratch/DESIGN-CORPUS.md:1886`) says *"Every engine-sourced fact shows
  provenance"* — it requires the provenance to be shown; it does not forbid a disclosure, and the
  roster workstream's own note (`docs/research-scratch/CATALOG-ROSTER-WORKSTREAM.md:3106`) is
  "add no second character-level provenance block". `ce89b2be` did not collapse the two blocks:
  `FormDetail.vue:40` still prints the form's own source inline, and what moved is
  `Catalog/Show.vue`'s *own* trainee-level fetch history into one disclosure whose summary names
  the state. Both kinds of provenance are still shown, each where it belongs.

---

## Slice 5.5 — Scenarios / Shop / Sparks / Events / Hub

**The plan's claims.** Scenarios → two `DataTable`s ("Stat caps by scenario", "Panels by
scenario") with ✓/— cells; Shop/Sparks/Events → `DataTable` per dataset with left-aligned text,
right-aligned numbers, server tags as chips; Hub → `Card` per dataset.

**Measured.**

- `resources/js/pages/Database/Scenarios.vue` — one `<section>` per scenario, each carrying its
  own caps table and panels list. The composition matrix is `config/scenarios.php`'s, and D-240
  (`DESIGN-CORPUS.md:2408`, gate G-33) makes scenario composition *data the layout reads, not
  branching the layout contains*. A table whose rows are all scenarios and whose columns are
  their per-scenario stat caps is a cross-scenario matrix, and that is a `config/scenarios.php`
  change, not a page change.
- `resources/js/pages/Database/Index.vue` — a plain `<nav aria-label="Database areas">` over a
  `<ul>`. The page's own docblock refuses per-area record counts: *"No area counts on this page …
  a figure here would be one no area corroborates on arrival."* The `Card` primitive has **0**
  consumers.
- `resources/js/pages/Database/Events.vue`, `ShopItems.vue`, `Sparks.vue` — hand-rolled row lists
  whose rows open a `ReferenceDrawer`; `tests/browser/database.spec.ts:135` pins the Events
  drawer's open-and-return-focus behaviour.
- `resources/js/components/ui/DataTable.vue` has **1** consumer
  (`resources/js/pages/Career/TraineeSelect.vue`); `resources/js/components/ui/Card.vue` has **0**.

**Verdict: needs-re-scope, dropped by ruling. No commit.**

The migration was built and then withdrawn, because the measurement showed the affordances it
would have cost:

| Target | Affordance the shared primitive would drop |
|---|---|
| Scenarios' caps table | `<th scope="row">` row headers, and the per-scenario `<caption>` |
| Scenarios' panels list | the visible word `off` — the page's own comment states the word stays in the text so the state never rides on colour alone; a `✓`/`—` pair is glyph-only |
| Sparks' star-roll odds | `min-w-[28rem]` + `<th scope="row">` — the shared region scrolls only if the table is wider than it, and `w-full` wraps instead |
| Shop / Sparks / Events row lists | the whole-row drawer trigger, the per-row `<dl>`, and the chip row's presence beside the name |
| Hub | the plain nav the `database.spec.ts` hub case addresses by role |

A migration that loses a spec-pinned affordance is a preference, not a fix. The plan's own
ruling says the same. Nothing was landed from this slice, and the working tree was restored to
`ce89b2be` for these five files.

*If the operator wants a specific comparison view (a column-wise comparison of Sparks is the
example the plan names), it is a targeted change with a proposal first, not a migration.*

---

## What remains for this agent after Phase 5

- **5.1's sort whitelist** — a controller change, owed to the operator. The plan's own instruction
  is *stop, report*.
- **5.4's debut-date shape** — a copy decision between two documented field lists, owed to the
  operator.
- **`components/Pagination.vue`** — still imported by live consumers. It is not deleted; the
  unification is partial by ruling and the remainder is a small ticket, not this arc.
- **5.3's rarity-chip tuple name** — a copy decision with a spec behind it.
- **5.5's comparison views** — available as a proposal-first targeted change if the operator asks.

Phase 6.2 (Review) closes on the same measurement: `83e1ad62` landed the suggestion sort, the
verdict-gated resolve and the button name, and the card list is the correct host for a per-row
verdict form, so the `DataTable` claim is dropped by the same ruling. No work; recorded in the
ledger.
